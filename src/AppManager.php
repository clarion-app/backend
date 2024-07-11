<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Composer;
use ClarionApp\Backend\Models\ComposerPackage;
use ClarionApp\Backend\Models\AppPackage;
use ClarionApp\Backend\Models\NpmPackage;
use ClarionApp\Backend\Events\InstallNPMPackageEvent;
use ClarionApp\Backend\Events\UninstallNPMPackageEvent;

class AppManager
{
    public function appInstall($package)
    {
        [$org, $name] = explode('/', $package);
        $url = "https://store.clarion.app/$package";
        $packageData = json_decode(file_get_contents($url));
        Log::info(print_r($packageData, 1));

        $app = AppPackage::where('organization', $org)->where('name', $name)->first();
        if(!$app)
        {
            $app = AppPackage::create([
                'organization' => $org,
                'name' => $name,
                'description' => $packageData->description,
                'title' => $packageData->title,
                'installed' => false
            ]);
        }

        if($app->installed)
        {
            return "App already installed";
        }

        foreach($packageData->composerPackages as $composerPackage)
        {
            Log::info("Installing $composerPackage");
            $this->composerInstall($composerPackage, $app->id);
        }

        foreach($packageData->npmPackages as $npmPackage)
        {
            Log::info("Installing $npmPackage");
            $this->npmInstall($npmPackage, $app->id);
        }
    }

    public function npmInstall($package, $app_id = "0")
    {
        event(new InstallNPMPackageEvent('{ "package": $package }'));
        $this->updateNpmPackageTable($package, $app_id);
    }

    public function npmUninstall($package, $app_id = "0")
    {
        event(new UninstallNPMPackageEvent('{ "package": $package }'));
        $this->updateNpmPackageTable($package, $app_id);
    }

    public function updateNpmPackageTable($package, $app_id)
    {
        [$org, $name] = explode('/', $package);
        $npmPackage = NpmPackage::where('organization', $org)->where('name', $name)->first();
        $installed = $app_id != "0" ? true : false;
        if ($npmPackage)
        {
            $npmPackage->update(['installed' => $installed]);
        }
        else
        {
            NpmPackage::create([
                'organization' => $org,
                'name' => $name,
                'installed' => $installed,
                'app_id' => $app_id
            ]);
        }
    }

    public function composerInstall($package, $app_id = "0")
    {
        $path = base_path();
        chdir($path);
        $output = shell_exec("cd $path; /usr/local/bin/composer require $package");
        $output .= shell_exec("cd $path; php artisan migrate");
        $output .= shell_exec("cd $path; php artisan queue:restart");
        $this->updateComposerPackageTable($package, $app_id);
        return $output;
    }

    public function composerUninstall($package, $app_id = "0")
    {
        $path = base_path();
        chdir($path);
        $output = shell_exec("cd $path; /usr/local/bin/composer remove $package");
        $output .= shell_exec("cd $path; php artisan queue:restart");
        $this->updateComposerPackageTable($package, $app_id);
        return $output;
    }

    public function updateComposerPackageTable($package, $app_id = null)
    {
        [$org, $name] = explode('/', $package);
        $composerPackage = ComposerPackage::where('organization', $org)->where('name', $name)->first();
        $path = base_path('vendor/'.$package.'/composer.json');
        $installed = file_exists($path);
        if ($composerPackage)
        {
            $composerPackage->update(['installed' => $installed]);
        }
        else
        {
            ComposerPackage::create([
                'organization' => $org,
                'name' => $name,
                'installed' => $installed,
                'app_id' => $app_id
            ]);
        }
    }
}
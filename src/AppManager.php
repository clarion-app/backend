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

    public function appUninstall($package)
    {
        [$org, $name] = explode('/', $package);
        $app = AppPackage::where('organization', $org)->where('name', $name)->first();
        if(!$app)
        {
            return "App not installed";
        }

        if(!$app->installed)
        {
            return "App already uninstalled";
        }

        $npmPackages = NpmPackage::where('app_id', $app->id)->get();
        foreach($npmPackages as $npmPackage)
        {
            Log::info("Uninstalling $npmPackage->organization/$npmPackage->name");
            $this->npmUninstall($npmPackage->organization.'/'.$npmPackage->name);
        }

        $composerPackages = ComposerPackage::where('app_id', $app->id)->get();
        foreach($composerPackages as $composerPackage)
        {
            Log::info("Uninstalling $composerPackage->organization/$composerPackage->name");
            $this->composerUninstall($composerPackage->organization.'/'.$composerPackage->name);
        }

        $app->update(['installed' => false]);
    }

    public function npmInstall($package, $app_id = "0")
    {
        event(new InstallNPMPackageEvent($package));
        $this->updateNpmPackageTable($package, $app_id);
    }

    public function npmUninstall($package)
    {
        event(new UninstallNPMPackageEvent($package));
        [$org, $name] = explode('/', $package);
        $npmPackage = NpmPackage::where('organization', $org)->where('name', $name)->first();
        $npmPackage->delete();
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
        $composer = app(Composer::class);
        $composer->run(['require', $package]);
        //$output = shell_exec("cd $path; /usr/local/bin/composer require $package");
        $output = shell_exec("cd $path; php artisan migrate");
        $output .= shell_exec("cd $path; php artisan queue:restart");
        Log::info($output);
        $this->updateComposerPackageTable($package, $app_id);
        return $output;
    }

    public function composerUninstall($package, $app_id = "0")
    {
        $path = base_path();
        chdir($path);
        $composer = app(Composer::class);
        $composer->run(['remove', $package]);
        //$output = shell_exec("cd $path; /usr/local/bin/composer remove $package");
        $output = shell_exec("cd $path; php artisan queue:restart");
        $composerPackage = ComposerPackage::where('organization', $org)->where('name', $name)->first();
        $composerPackage->delete();
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
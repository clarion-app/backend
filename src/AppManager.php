<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Composer;
use ClarionApp\Backend\Models\ComposerPackage;
use ClarionApp\Backend\Models\AppPackage;

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
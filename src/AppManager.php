<?php
namespace ClarionApp\Backend;

use Illuminate\Support\Facades\Log;
use ClarionApp\Backend\Composer;
use ClarionApp\Backend\Models\ComposerPackage;
use ClarionApp\Backend\Models\AppPackage;
use ClarionApp\Backend\Models\NpmPackage;
use ClarionApp\Backend\Events\InstallNPMPackageEvent;
use ClarionApp\Backend\Events\UninstallNPMPackageEvent;
use Symfony\Component\Process\Process;
use GuzzleHttp\Client as GuzzleClient;

class AppManager
{
    private function validateComposerPackageName(string $package): void
    {
        if (!preg_match('/^[a-z0-9]([_.\-]?[a-z0-9]+)*\/[a-z0-9]([_.\-]?[a-z0-9]+)*$/', $package)) {
            throw new \InvalidArgumentException("Invalid composer package name: $package");
        }
    }

    public function appInstall($package)
    {
        [$org, $name] = explode('/', $package);
        $url = config("clarion.store_url")."/api/organizations/$org/packages/$name";
        Log::info($url);
        $httpClient = app(GuzzleClient::class);
        $response = $httpClient->get($url);
        $packageData = json_decode($response->getBody()->getContents());
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

        foreach($packageData->npm_packages as $npmPackage)
        {
            Log::info("Installing ".print_r($npmPackage, 1));
            $this->npmInstall($npmPackage->name, $app->id);
        }


        foreach($packageData->composer_packages as $composerPackage)
        {
            Log::info("Installing ".print_r($composerPackage, 1));
            $this->composerInstall($composerPackage->name, $app->id);
        }

        $app->update(['installed' => true]);
        return $packageData->npm_packages;
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

        $npmPackageList = [];
        $npmPackages = NpmPackage::where('app_id', $app->id)->get();
        foreach($npmPackages as $npmPackage)
        {
            Log::info("Uninstalling $npmPackage->organization/$npmPackage->name");
            $this->npmUninstall($npmPackage->organization.'/'.$npmPackage->name);
            array_push($npmPackageList, $npmPackage->organization.'/'.$npmPackage->name);
        }

        $composerPackages = ComposerPackage::where('app_id', $app->id)->get();
        foreach($composerPackages as $composerPackage)
        {
            Log::info("Uninstalling $composerPackage->organization/$composerPackage->name");
            $this->composerUninstall($composerPackage->organization.'/'.$composerPackage->name);
        }

        $app->update(['installed' => false]);
        return $npmPackageList;
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
        $this->validateComposerPackageName($package);

        $path = base_path();
        chdir($path);
        $composer = app(Composer::class);
        $composer->run(['require', $package]);

        $process = new Process(['/usr/local/bin/composer', 'require', $package]);
        $process->setWorkingDirectory($path);
        $process->setTimeout(60);
        $process->run();
        $output = $process->getOutput();

        $migrate = new Process(['php', 'artisan', 'migrate']);
        $migrate->setWorkingDirectory($path);
        $migrate->setTimeout(60);
        $migrate->run();
        $output .= $migrate->getOutput();

        $restart = new Process(['php', 'artisan', 'queue:restart']);
        $restart->setWorkingDirectory($path);
        $restart->setTimeout(60);
        $restart->run();
        $output .= $restart->getOutput();

        Log::info($output);
        $this->updateComposerPackageTable($package, $app_id);
        return $output;
    }

    public function composerUninstall($package)
    {
        $this->validateComposerPackageName($package);

        Log::info("Uninstalling composer package $package");
        $path = base_path();
        chdir($path);
        $composer = app(Composer::class);
        $composer->run(['remove', $package]);

        $process = new Process(['/usr/local/bin/composer', 'remove', $package]);
        $process->setWorkingDirectory($path);
        $process->setTimeout(60);
        $process->run();
        $output = $process->getOutput();

        $restart = new Process(['php', 'artisan', 'queue:restart']);
        $restart->setWorkingDirectory($path);
        $restart->setTimeout(60);
        $restart->run();
        $output .= $restart->getOutput();

        [$org, $name] = explode('/', $package);
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

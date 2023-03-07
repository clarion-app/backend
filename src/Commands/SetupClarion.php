<?php

namespace ClarionApp\ClarionSetup\Commands;

use Illuminate\Console\Command;
use Artisan;

class SetupClarion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clarion:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if(!file_exists(config_path('docker-php-client.php')))
        {
            Artisan::call('vendor:publish', [
                '--provider' => 'MetaverseSystems\DockerPhpClient\DockerPhpClientProvider',
                '--force' => true,
                '--tag' => 'config'
            ]);
        }

        if(!file_exists(config_path('multichain.php')))
        {
            Artisan::call('vendor:publish', [
                '--provider' => 'MetaverseSystems\MultiChain\MultiChainProvider',
                '--force' => true,
                '--tag' => 'config'
            ]);
        }

        Artisan::call('clarion:setup-db');
    }
}

<?php

namespace ClarionApp\ClarionSetup\Commands;

use Illuminate\Console\Command;
use Artisan;
use MetaverseSystems\DockerPhpClient\Facades\DockerClient;

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
        $srv_dir = "";

        $containers = DockerClient::containers();
        foreach($containers as $container)
        {
            foreach($container->Mounts as $mount)
            {
                if($mount->Destination == "/srv") $srv_dir = $mount->Source;
            }
        }

        Artisan::call('clarion:setup-node-id');
        Artisan::call('clarion:setup-db', ["srv"=>$srv_dir ]);
        Artisan::call('clarion:setup-multichain');
    }
}

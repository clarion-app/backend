<?php

namespace ClarionApp\Backend\Commands;

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
        Artisan::call('clarion:setup-node-id');
        Artisan::call('clarion:setup-db');
        Artisan::call('clarion:setup-multichain', ["chain-name"=>"clarion-chain"]);
    }
}

<?php

namespace ClarionApp\Backend\Commands;

use Illuminate\Console\Command;
use ClarionApp\Backend\ApiManager;

class TestAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test';

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
        $results = ApiManager::getOperations();
        print json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

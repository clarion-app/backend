<?php

namespace ClarionApp\ClarionSetup\Commands;

use Illuminate\Console\Command;

class SetupNodeID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clarion:setup-node-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configures CLARION_NODE_ID in .env';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $env = file_get_contents(base_path(".env"));
        $lines = explode("\n", $env);

        foreach($lines as $k=>$line)
        {
            try {
              list($key, $value) = explode("=", $line);
            } catch (\ErrorException $e) {
                continue;
            }

            if(stripos($key, "CLARION_NODE_ID") !== false) unset($lines[$k]);
        }

        array_push($lines, "CLARION_NODE_ID=".((string) \Str::uuid()));

        $env = implode("\n", $lines);
        file_put_contents(base_path(".env"), $env);
    }
}

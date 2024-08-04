<?php

namespace ClarionApp\Backend\Commands;

use Illuminate\Console\Command;
use ClarionApp\Backend\UPnPScanner;

class ClarionScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clarion:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for other Clarion nodes on the network';

    public function handle()
    {
        $scanner = new UPnPScanner();
        $devices = $scanner->discoverDevices();
        
        foreach ($devices as $device)
        {
            $description = file_get_contents($device['Location']);
            $xml = simplexml_load_string($description);
            if($xml->device->modelName != 'Clarion')
            {
                continue;
            }
            print_r($xml);
        }
    }
}
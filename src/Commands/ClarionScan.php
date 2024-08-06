<?php

namespace ClarionApp\Backend\Commands;

use Illuminate\Console\Command;
use ClarionApp\Backend\UPnPScanner;
use stdClass;

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
        
        $connectionManagers = [];

        foreach ($devices as $device)
        {
            print_r($device);

            $description = file_get_contents($device['Location']);
            $xml = simplexml_load_string($description);

            $found_connection_manager = false;
            foreach($xml->device->serviceList->service as $service)
            {
                print_r($service);
                if($service->serviceType == 'urn:schemas-upnp-org:service:ConnectionManager:1')
                {
                    if($service->serviceId == 'urn:upnp-org:serviceId:ClarionConnectionManager')
                    {
                        $connectionManagers[] = (string)$service->controlURL;
                    }
                }
            }
        }

        $this->info('Found ' . count($connectionManagers) . ' Clarion nodes on the network');
        foreach($connectionManagers as $controlURL)
        {
            $this->info('Requesting access from ' . $controlURL);
            $this->requestAccess($controlURL);
        }
    }

    public function requestAccess($url)
    {
        $body = new stdClass;
        $body->action = 'join';
        $body->arguments = [
            'node_id' => config('clarion.node_id'),
            'backend_url' => config('app.url'),
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post($url, [
            'json' => $body,
        ]);

    }
}
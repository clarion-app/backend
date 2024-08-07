<?php

namespace ClarionApp\Backend\Commands;

use Illuminate\Console\Command;
use ClarionApp\Backend\UPnPScanner;
use stdClass;
use ClarionApp\Backend\Models\LocalNode;
use ClarionApp\Backend\BlockchainManager;

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
            $description = file_get_contents($device['Location']);
            $xml = simplexml_load_string($description);

            if($xml->device->modelName != 'Clarion')
            {
                continue;
            }
            print_r($xml);

            $id = explode(":", $xml->device->UDN)[1];
            $name = $xml->device->friendlyName;
            $backend_url = $xml->device->presentationURL.":8000";

            $node = LocalNode::find($id);
            if(!$node)
            {
                $node = new LocalNode;
                $node->node_id = $id;
                $node->name = $name;
                $node->backend_url = $backend_url;
                $node->save();
            }

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
        $data = json_decode(file_get_contents($url));

        $manager = new BlockchainManager();
        $wallet_address = $manager->join($data->url);

        $hostname = gethostname();

        $body = new stdClass;
        $body->id = config('clarion.node_id');
        $body->name = $hostname;
        $body->wallet_address = $wallet_address;

        $client = new \GuzzleHttp\Client();
        $response = $client->post($url."/join", [
            'json' => $body,
        ]);

    }
}
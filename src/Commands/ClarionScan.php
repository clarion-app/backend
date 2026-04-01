<?php

namespace ClarionApp\Backend\Commands;

use Illuminate\Console\Command;
use ClarionApp\Backend\UPnPScanner;
use stdClass;
use ClarionApp\Backend\Models\LocalNode;
use ClarionApp\Backend\BlockchainManager;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

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
        $scanner->discoverAndUpsertNodes();

        // Re-discover to find connection managers (need raw XML data)
        $devices = $scanner->discoverDevices();
        $connectionManagers = [];
        $httpClient = app(GuzzleClient::class);

        foreach ($devices as $device)
        {
            try {
                $response = $httpClient->get($device['Location']);
                $description = $response->getBody()->getContents();
            } catch (RequestException $e) {
                continue;
            }
            $xml = simplexml_load_string($description);

            if($xml === false || $xml->device->modelName != 'Clarion')
            {
                continue;
            }

            if (isset($xml->device->serviceList->service)) {
                foreach($xml->device->serviceList->service as $service)
                {
                    if($service->serviceType == 'urn:schemas-upnp-org:service:ConnectionManager:1')
                    {
                        if($service->serviceId == 'urn:upnp-org:serviceId:ClarionConnectionManager')
                        {
                            $connectionManagers[] = (string)$service->controlURL;
                        }
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
        $httpClient = app(GuzzleClient::class);
        $response = $httpClient->get($url);
        $data = json_decode($response->getBody()->getContents());

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
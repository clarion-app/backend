<?php

namespace ClarionApp\Backend;

use ClarionApp\Backend\Models\LocalNode;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class UPnPScanner
{
    private $serviceType;

    public function __construct($serviceType = "ssdp:all")
    {
        $this->serviceType = $serviceType;
    }

    public function discoverDevices()
    {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $msg = "M-SEARCH * HTTP/1.1\r\n" .
               "HOST: 239.255.255.250:1900\r\n" .
               "MAN: \"ssdp:discover\"\r\n" .
               "MX: 2\r\n" .
               "ST: {$this->serviceType}\r\n" .
               "USER-AGENT: PHP UPnP Client\r\n" .
               "\r\n";

        $len = strlen($msg);
        $from = '';
        $port = 0;

        socket_set_option($sock, IPPROTO_IP, IP_MULTICAST_TTL, 2);
        socket_sendto($sock, $msg, $len, 0, '239.255.255.250', 1900);

        socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec'=>5, 'usec'=>0]);

        $response = [];
        while (true) {
            $buf = null;
            @socket_recvfrom($sock, $buf, 2048, 0, $from, $port);
            if (null === $buf) break;
            $response[] = $this->parseResponse($buf);
        }

        socket_close($sock);

        return $response;
    }

    private function parseResponse($response)
    {
        $lines = explode("\r\n", $response);
        $parsedResponse = [];
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $parsedResponse[trim($key)] = trim($value);
            }
        }

        return $parsedResponse;
    }

    /**
     * Discover Clarion nodes via UPnP, fetch descriptions, and upsert LocalNode records.
     *
     * @return LocalNode[] Array of discovered/updated LocalNode models
     */
    public function discoverAndUpsertNodes(): array
    {
        $devices = $this->discoverDevices();
        $nodes = [];
        $httpClient = app(GuzzleClient::class);

        foreach ($devices as $device) {
            try {
                $response = $httpClient->get($device['Location']);
                $description = $response->getBody()->getContents();
            } catch (RequestException $e) {
                continue;
            }

            $xml = simplexml_load_string($description);
            if ($xml === false || $xml->device->modelName != 'Clarion') {
                continue;
            }

            $id = explode(":", (string) $xml->device->UDN)[1];
            $name = (string) $xml->device->friendlyName;
            $backend_url = (string) $xml->device->presentationURL . ":8000";

            $node = LocalNode::where('node_id', $id)->first();
            if (!$node) {
                $node = new LocalNode;
                $node->node_id = $id;
                $node->name = $name;
                $node->backend_url = $backend_url;
                $node->save();
            }

            $nodes[] = $node;
        }

        return $nodes;
    }
}

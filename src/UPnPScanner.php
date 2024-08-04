<?php

namespace ClarionApp\Backend;

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
}

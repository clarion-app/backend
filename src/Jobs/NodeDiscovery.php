<?php

namespace ClarionApp\Backend\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ClarionApp\Backend\UPnPScanner;
use ClarionApp\Backend\Models\LocalNode;

class NodeDiscovery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $local_node_id = config('clarion.node_id');

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
        }
    }
}

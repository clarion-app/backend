<?php

namespace ClarionApp\Backend\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;

class UninstallNPMPackageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $package;

    public function __construct($package)
    {
        $this->package = $package;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('clarion-apps'),
        ];
    }
}
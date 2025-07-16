<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class GateControl implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $gate;
    public $action;

    public function __construct($gate, $action)
    {
        $this->gate = $gate;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return new Channel('gate-control');
    }
}

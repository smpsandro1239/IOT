<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GateControl implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The gate identifier.
     *
     * @var string
     */
    public $gate;

    /**
     * The gate action (true = open, false = close).
     *
     * @var bool
     */
    public $action;

    /**
     * Create a new event instance.
     *
     * @param string $gate
     * @param bool $action
     * @return void
     */
    public function __construct($gate, $action)
    {
        $this->gate = $gate;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('gate-control');
    }
}

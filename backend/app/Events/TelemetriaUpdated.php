<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TelemetriaUpdated implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets;

  public $mac;
  public $direcao;
  public $datahora;
  public $status;

  public function __construct($mac, $direcao, $datahora, $status)
  {
    $this->mac = $mac;
    $this->direcao = $direcao;
    $this->datahora = $datahora;
    $this->status = $status;
  }

  public function broadcastOn()
  {
    return new Channel('telemetria');
  }
}

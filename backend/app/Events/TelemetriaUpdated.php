<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\MacsAutorizados;

class TelemetriaUpdated implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * The MAC address.
   *
   * @var string
   */
  public $mac;

  /**
   * The direction.
   *
   * @var string
   */
  public $direcao;

  /**
   * The timestamp.
   *
   * @var string
   */
  public $datahora;

  /**
   * The status.
   *
   * @var string
   */
  public $status;

  /**
   * The vehicle plate.
   *
   * @var string|null
   */
  public $placa;

  /**
   * The signal strength (0-100).
   *
   * @var int
   */
  public $signal_strength;

  /**
   * The distance in meters.
   *
   * @var int
   */
  public $distance;

  /**
   * Create a new event instance.
   *
   * @param string $mac
   * @param string $direcao
   * @param string $datahora
   * @param string $status
   * @return void
   */
  public function __construct($mac, $direcao, $datahora, $status)
  {
    $this->mac = $mac;
    $this->direcao = $direcao;
    $this->datahora = $datahora;
    $this->status = $status;

    // Get vehicle plate from MAC
    $macAutorizado = MacsAutorizados::where('mac', $mac)->first();
    $this->placa = $macAutorizado ? $macAutorizado->placa : null;

    // Calculate signal strength based on status (for demo purposes)
    $this->signal_strength = $status === 'AUTORIZADO' ? rand(70, 95) : rand(30, 60);

    // Calculate distance (for demo purposes)
    $this->distance = $status === 'AUTORIZADO' ? rand(10, 50) : rand(100, 300);
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    return new Channel('telemetria');
  }
}

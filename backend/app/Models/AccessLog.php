<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_lora_id',
        'barrier_id', // Adicionado
        'timestamp_event',
        'direction_detected',
        'base_station_id',
        'sensor_reports',
        'authorization_status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timestamp_event' => 'datetime',
        'sensor_reports' => 'array',
        'authorization_status' => 'boolean',
    ];

    /**
     * Get the vehicle associated with the access log.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_lora_id', 'lora_id');
    }

    /**
     * Get the barrier where the access log occurred.
     */
    public function barrier(): BelongsTo
    {
        return $this->belongsTo(Barrier::class);
    }
}

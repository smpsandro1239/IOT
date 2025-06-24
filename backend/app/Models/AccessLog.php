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
        'sensor_reports' => 'array', // Cast JSON to array
        'authorization_status' => 'boolean',
    ];

    /**
     * Get the vehicle that owns the access log.
     * Note: This assumes 'vehicle_lora_id' in this table maps to 'lora_id' in 'vehicles' table.
     * This is not a true foreign key relationship in the DB schema by default with this setup,
     * but Eloquent can still relate them.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_lora_id', 'lora_id');
    }
}

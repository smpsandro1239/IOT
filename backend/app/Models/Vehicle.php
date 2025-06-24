<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lora_id',
        'name',
        'is_authorized',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_authorized' => 'boolean',
    ];

    /**
     * Get the access logs for the vehicle.
     */
    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class, 'vehicle_lora_id', 'lora_id');
    }
}

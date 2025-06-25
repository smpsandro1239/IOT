<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VehiclePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'permissible_type',
        'permissible_id',
        'expires_at', // Opcional
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the vehicle that owns the permission.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the parent permissible model (company, site, or barrier).
     */
    public function permissible(): MorphTo
    {
        return $this->morphTo();
    }
}

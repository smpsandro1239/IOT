<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'base_station_mac_address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the site that owns the barrier.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get all of the vehicle permissions for the barrier.
     */
    public function vehiclePermissions(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(VehiclePermission::class, 'permissible');
    }

    // Helper para acessar a company através do site
    public function company()
    {
        return $this->site->company; // Assume que 'site' e 'site.company' estão carregados ou serão
    }
}

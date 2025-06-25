<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the site.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the barriers for the site.
     */
    public function barriers(): HasMany
    {
        return $this->hasMany(Barrier::class);
    }

    /**
     * Get all of the vehicle permissions for the site.
     */
    public function vehiclePermissions(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(VehiclePermission::class, 'permissible');
    }
}

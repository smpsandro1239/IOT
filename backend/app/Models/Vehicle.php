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
        // 'is_authorized', // Removido
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'is_authorized' => 'boolean', // Removido
    ];

    /**
     * Get the access logs for the vehicle.
     */
    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class, 'vehicle_lora_id', 'lora_id');
    }

    /**
     * Get all of the vehicle's permissions.
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VehiclePermission::class);
    }

    // Helper para verificar permissão para uma entidade específica
    public function hasPermissionFor($permissible): bool
    {
        if (!$permissible instanceof Model) {
            return false;
        }
        return $this->permissions()
                    ->where('permissible_type', $permissible->getMorphClass())
                    ->where('permissible_id', $permissible->getKey())
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->exists();
    }

    /**
     * Get only the company permissions for the vehicle.
     */
    public function companyPermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->permissions()->where('permissible_type', Company::class);
    }

    /**
     * Get only the site permissions for the vehicle.
     */
    public function sitePermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->permissions()->where('permissible_type', Site::class);
    }

    /**
     * Get only the barrier permissions for the vehicle.
     */
    public function barrierPermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->permissions()->where('permissible_type', Barrier::class);
    }
}

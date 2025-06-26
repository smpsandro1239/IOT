<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Importar HasApiTokens
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Adicionar HasApiTokens aqui

    // company_id will be added via migration and should be in fillable if managed through User model directly

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id', // Make company_id fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the company that the user might belong to (e.g., for a CompanyAdmin).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Assign a role to the user.
     *
     * @param string|Role $role
     * @return void
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching($role);
    }

    /**
     * Remove a role from the user.
     *
     * @param string|Role $role
     * @return void
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->detach($role);
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string|array $role Name of the role, or array of role names.
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return $this->roles->pluck('name')->intersect($role)->isNotEmpty();
        }
        return $this->roles->contains('name', $role);
    }

    /**
     * Check if the user has a specific permission, directly or through a role.
     * This method iterates through user's roles and checks if any role has the permission.
     *
     * @param string|Permission $permission Name of the permission or Permission object.
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        $permissionName = is_string($permission) ? $permission : ($permission instanceof Permission ? $permission->name : null);

        if (!$permissionName) {
            return false; // Or throw an exception for invalid argument
        }

        // Eager load roles and their permissions if not already loaded, to optimize.
        // However, direct check is often fine for a single user instance.
        // $this->loadMissing('roles.permissions');


        foreach ($this->roles as $role) {
            if ($role->hasPermissionTo($permissionName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the user is a super-admin.
     * Assumes 'super-admin' is the name of the role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * The permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Grant the given permission to a role.
     *
     * @param Permission $permission
     * @return void
     */
    public function grantPermissionTo(Permission $permission)
    {
        $this->permissions()->syncWithoutDetaching($permission);
    }

    /**
     * Revoke the given permission from a role.
     *
     * @param Permission $permission
     * @return void
     */
    public function revokePermissionTo(Permission $permission)
    {
        $this->permissions()->detach($permission);
    }

    /**
     * Check if the role has a specific permission.
     *
     * @param string|Permission $permission Name of the permission or Permission object
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }

        if ($permission instanceof Permission) {
            return $this->permissions->contains('id', $permission->id);
        }

        return false;
    }
}

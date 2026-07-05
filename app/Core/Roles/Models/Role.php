<?php

namespace App\Core\Roles\Models;

use App\Core\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Core\Roles — a named set of permissions (RBAC).
 *
 * Kept dependency-free on purpose: if a product later needs
 * spatie/laravel-permission, swap the internals here without touching
 * consumers ($user->hasRole() / hasPermission() stay stable).
 */
class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')->withTimestamps();
    }

    /** Grant one or more permissions by model or slug. */
    public function givePermissionTo(Permission|string ...$permissions): static
    {
        foreach ($permissions as $permission) {
            $model = is_string($permission)
                ? Permission::where('slug', $permission)->firstOrFail()
                : $permission;

            $this->permissions()->syncWithoutDetaching($model);
        }

        return $this;
    }
}

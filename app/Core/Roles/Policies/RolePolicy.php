<?php

namespace App\Core\Roles\Policies;

use App\Core\Roles\Models\Role;
use App\Core\Users\Models\User;

/**
 * Core\Roles — sample policy showing the Core convention:
 * policies live next to their module, authorization decisions delegate
 * to permission slugs so the RBAC backend stays swappable.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        // The "operator" role is seeded and must never be deleted from the UI.
        return $role->slug !== 'operator' && $user->hasPermission('roles.manage');
    }
}

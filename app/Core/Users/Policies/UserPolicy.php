<?php

namespace App\Core\Users\Policies;

use App\Core\Users\Models\User;

/**
 * Core\Users — authorization for user administration.
 *
 * Checks go through the "users.manage" permission (not a hard-coded
 * role): the seeded "admin" role owns that permission, so in practice
 * only admins manage users — but products can grant it to other roles
 * without code changes.
 *
 * Pro version hooks: per-tenant scoping, "manager can edit own team",
 * read-only auditor role (split users.view from users.manage).
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function delete(User $user, User $model): bool
    {
        return ! $user->is($model) && $user->hasPermission('users.manage');
    }
}

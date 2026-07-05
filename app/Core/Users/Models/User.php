<?php

namespace App\Core\Users\Models;

use App\Core\Roles\Models\Role;
use App\Core\Support\Traits\RecordsActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Core\Users — canonical user model for every Penova product.
 *
 * App\Models\User extends this class so the framework-facing binding
 * (config/auth.php, factories, policies) keeps the native Laravel name
 * while all shared behaviour lives here in Core.
 *
 * Modules must type-hint this class (or App\Models\User), never define
 * their own user models.
 */
class User extends Authenticatable
{
    // RecordsActivity: created/updated/deleted user accounts show up in
    // the Core\Logs audit trail automatically (e.g. "users.created").
    use HasFactory, Notifiable, RecordsActivity;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Roles assigned to the user (Core\Roles RBAC). */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    /** Check a role by slug, e.g. $user->hasRole('admin'). */
    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    /** Check a permission by slug across all assigned roles. */
    public function hasPermission(string $slug): bool
    {
        return $this->roles->some(
            fn (Role $role) => $role->permissions->contains('slug', $slug)
        );
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}

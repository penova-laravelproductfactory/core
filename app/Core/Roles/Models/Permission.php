<?php

namespace App\Core\Roles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Core\Roles — a single grantable ability, e.g. "users.manage".
 *
 * Convention for slugs: "<area>.<action>" — Core areas use their module
 * name (users.manage, settings.manage, logs.view); product Modules
 * register their own (booking.manage) via their seeder/provider,
 * never by editing Core.
 */
class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')->withTimestamps();
    }
}

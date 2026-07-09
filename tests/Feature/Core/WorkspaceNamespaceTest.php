<?php

use App\Core\PenovaCoreServiceProvider;
use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use App\Core\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * RFC-002 / D-024 — the three promises the admin→Workspace/Operator split
 * makes. Each defends a promise, not an implementation detail (16).
 */

test('the admin→operator role-slug migration preserves grants and assignments', function () {
    // Simulate a pre-D-024 database: an "admin" role with a permission grant
    // and a user assignment (the migration ran empty during RefreshDatabase).
    $role = Role::create(['slug' => 'admin', 'name' => 'Administrator', 'description' => 'legacy']);
    $permission = Permission::firstOrCreate(['slug' => 'users.manage'], ['name' => 'Manage Users']);
    $role->permissions()->syncWithoutDetaching($permission);

    $user = User::factory()->create();
    $user->roles()->syncWithoutDetaching($role);

    // Run the rename migration in isolation.
    (require database_path('migrations/2026_07_09_000001_rename_admin_role_slug_to_operator.php'))->up();

    $role->refresh();
    expect($role->slug)->toBe('operator');
    expect($role->permissions()->where('slug', 'users.manage')->exists())->toBeTrue();
    expect($user->fresh()->roles()->where('slug', 'operator')->exists())->toBeTrue();
});

test('Workspace and Module routes resolve from the configured Workspace prefix', function () {
    $prefix = config('penova.workspace.prefix');

    expect($prefix)->not->toBeEmpty();
    // Config-driven, not a hardcoded "/admin": both a Core and a Module route
    // live under whatever prefix is configured.
    expect(route('penova.users.index', absolute: false))->toStartWith("/{$prefix}/");
    expect(route('store.products.index', absolute: false))->toStartWith("/{$prefix}/");
});

test('a legacy PENOVA_ADMIN_* env resolves via the fallback and is flagged deprecated', function () {
    // New key unset, legacy key present.
    foreach (['PENOVA_WORKSPACE_PREFIX'] as $unset) {
        putenv($unset);
        unset($_ENV[$unset], $_SERVER[$unset]);
    }
    $_ENV['PENOVA_ADMIN_PREFIX'] = 'legacy-admin';
    $_SERVER['PENOVA_ADMIN_PREFIX'] = 'legacy-admin';
    putenv('PENOVA_ADMIN_PREFIX=legacy-admin');

    try {
        // Fallback: config resolves the legacy value (new key first, legacy second).
        $config = require config_path('penova.php');
        expect($config['workspace']['prefix'])->toBe('legacy-admin');

        // Deprecation: the legacy env is flagged, naming the retired var.
        $deprecation = null;
        set_error_handler(function (int $errno, string $message) use (&$deprecation) {
            $deprecation = $message;

            return true;
        }, E_USER_DEPRECATED);

        try {
            PenovaCoreServiceProvider::warnOnLegacyAdminEnv();
        } finally {
            restore_error_handler();
        }

        expect($deprecation)->toContain('PENOVA_ADMIN_PREFIX');
        expect($deprecation)->toContain('deprecated');
    } finally {
        putenv('PENOVA_ADMIN_PREFIX');
        unset($_ENV['PENOVA_ADMIN_PREFIX'], $_SERVER['PENOVA_ADMIN_PREFIX']);
    }
});

<?php

namespace App\Modules\Store\Database\Seeders;

use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Modules\Store — seeds the module's permissions and grants them to
 * the Core admin role (Lite keeps it simple: every admin gets full
 * Store access). Called by DatabaseSeeder after PenovaCoreSeeder.
 * Idempotent.
 */
class StorePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'View Store', 'slug' => 'store.view'],
            ['name' => 'Manage Store', 'slug' => 'store.manage'],
        ])->map(fn (array $permission) => Permission::firstOrCreate(
            ['slug' => $permission['slug']],
            $permission,
        ));

        Role::where('slug', 'admin')->first()
            ?->permissions()->syncWithoutDetaching($permissions->pluck('id'));
    }
}

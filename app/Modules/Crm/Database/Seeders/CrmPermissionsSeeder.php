<?php

namespace App\Modules\Crm\Database\Seeders;

use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Modules\Crm — seeds the module's permissions and grants them to the
 * Core admin role. Called by DatabaseSeeder after PenovaCoreSeeder.
 * Idempotent.
 *
 *   crm.leads.view   — see the leads list + the dashboard widget data
 *   crm.leads.manage — create leads
 */
class CrmPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'View Leads', 'slug' => 'crm.leads.view'],
            ['name' => 'Manage Leads', 'slug' => 'crm.leads.manage'],
        ])->map(fn (array $permission) => Permission::firstOrCreate(
            ['slug' => $permission['slug']],
            $permission,
        ));

        Role::where('slug', 'admin')->first()
            ?->permissions()->syncWithoutDetaching($permissions->pluck('id'));
    }
}

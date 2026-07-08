<?php

namespace App\Modules\Store\Database\Seeders;

use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use App\Modules\Store\StoreServiceProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Modules\Store — seeds the module's permissions and grants them to the
 * Core admin role (kept simple for now: every admin gets full Store
 * access). Called by DatabaseSeeder after PenovaCoreSeeder. Idempotent.
 *
 * The set of slugs is NOT declared here — it is read from the Store
 * Manifest, the single declaration of what the module contributes
 * (D-023). This seeder only *registers* (persists) those declared
 * permissions; the human labels below are presentation, not declaration.
 */
class StorePermissionsSeeder extends Seeder
{
    /** Display labels for known slugs; unknown slugs get a generated label. */
    private const LABELS = [
        'store.view' => 'View Store',
        'store.manage' => 'Manage Store',
    ];

    public function run(): void
    {
        $permissions = collect(StoreServiceProvider::manifest()->permissionSlugs())
            ->map(fn (string $slug) => Permission::firstOrCreate(
                ['slug' => $slug],
                ['slug' => $slug, 'name' => self::LABELS[$slug] ?? Str::of($slug)->replace('.', ' ')->headline()],
            ));

        Role::where('slug', 'admin')->first()
            ?->permissions()->syncWithoutDetaching($permissions->pluck('id'));
    }
}

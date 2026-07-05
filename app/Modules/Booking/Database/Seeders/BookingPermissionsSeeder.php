<?php

namespace App\Modules\Booking\Database\Seeders;

use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Modules\Booking — seeds the module's permissions and grants them to
 * the Core admin role. Called by DatabaseSeeder after PenovaCoreSeeder
 * (so the admin role already exists). Idempotent.
 *
 *   booking.view   — see the bookings list + the dashboard widget data
 *   booking.manage — create / edit bookings
 */
class BookingPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'View Bookings', 'slug' => 'booking.view'],
            ['name' => 'Manage Bookings', 'slug' => 'booking.manage'],
        ])->map(fn (array $permission) => Permission::firstOrCreate(
            ['slug' => $permission['slug']],
            $permission,
        ));

        Role::where('slug', 'admin')->first()
            ?->permissions()->syncWithoutDetaching($permissions->pluck('id'));
    }
}

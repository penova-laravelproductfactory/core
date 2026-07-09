<?php

namespace Database\Seeders;

use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the Core baseline every product starts from:
 * permissions, the Operator role, and one Operator account.
 *
 * The seed Operator credentials come from config('penova.operator.email/password')
 * (env: PENOVA_OPERATOR_EMAIL / PENOVA_OPERATOR_PASSWORD). The defaults are a
 * dev/test convenience only — override or rotate them anywhere real.
 *
 * Product Modules seed their OWN permissions in their own seeders
 * (e.g. StorePermissionsSeeder adds "store.manage") — they never edit
 * this file.
 */
class PenovaCoreSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'Manage Users', 'slug' => 'users.manage'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage'],
            ['name' => 'View Activity Logs', 'slug' => 'logs.view'],
        ])->map(fn (array $permission) => Permission::firstOrCreate(
            ['slug' => $permission['slug']],
            $permission,
        ));

        $operator = Role::firstOrCreate(
            ['slug' => 'operator'],
            ['name' => 'Operator', 'description' => 'Full access to the Workspace.'],
        );

        // syncWithoutDetaching: re-running the Core seeder alone must
        // never strip module permissions (booking.view, …) that module
        // seeders granted to the Operator role.
        $operator->permissions()->syncWithoutDetaching($permissions->pluck('id'));

        // Plain member role with no Core permissions — products attach
        // their own module permissions (booking.view, ...) to it.
        Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User', 'description' => 'Regular account without panel management access.'],
        );

        $email = config('penova.operator.email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Operator',
                'password' => Hash::make(config('penova.operator.password')),
            ],
        );

        $user->roles()->syncWithoutDetaching($operator);

        $this->command?->info("Admin account ready: {$email} (role: admin — dev credentials, rotate in production).");
    }
}

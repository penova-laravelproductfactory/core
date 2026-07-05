<?php

namespace Database\Seeders;

use App\Core\Roles\Models\Permission;
use App\Core\Roles\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the Core baseline every product starts from:
 * permissions, the admin role, and one admin account.
 *
 * The admin credentials come from config('penova.admin.email/password')
 * (env: PENOVA_ADMIN_EMAIL / PENOVA_ADMIN_PASSWORD). The defaults are a
 * dev/test convenience only — override or rotate them anywhere real.
 *
 * Product Modules seed their OWN permissions in their own seeders
 * (e.g. BookingSeeder adds "booking.manage") — they never edit this file.
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

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Full access to the panel.'],
        );

        $admin->permissions()->sync($permissions->pluck('id'));

        // Plain member role with no Core permissions — products attach
        // their own module permissions (booking.view, ...) to it.
        Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User', 'description' => 'Regular account without panel management access.'],
        );

        $email = config('penova.admin.email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make(config('penova.admin.password')),
            ],
        );

        $user->roles()->syncWithoutDetaching($admin);

        $this->command?->info("Admin account ready: {$email} (role: admin — dev credentials, rotate in production).");
    }
}

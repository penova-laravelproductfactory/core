<?php

/*
|--------------------------------------------------------------------------
| Core Lite — the "admin experience" system test
|--------------------------------------------------------------------------
| This is the end-to-end contract of Penova Core Lite and must ALWAYS be
| green: a fresh database is migrated and seeded, the seeded admin logs
| in, sees the dashboard, opens Users, creates a user, sees them in the
| list (with the audit log written), then logs out and is locked back
| out of the panel. If a change breaks any step of this flow, Core Lite
| is not releasable.
*/

use Database\Seeders\PenovaCoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('the full admin experience works end to end', function () {
    // 1) Fresh database: RefreshDatabase migrated it; seed the Core baseline.
    $this->seed(PenovaCoreSeeder::class);

    // 2) Log in with the seeded admin credentials (config-driven).
    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ])->assertRedirect(route('penova.dashboard'));

    $this->assertAuthenticated();

    // 3) The dashboard renders its Inertia page, with the panel
    //    composition props shared by HandleInertiaRequests: the sidebar
    //    menu (Core items first — lowest order — plus module items) and
    //    the dashboard widget descriptors (Core + modules, order-sorted).
    $this->get(route('penova.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Dashboard/Index')
            ->where('menu.0.key', 'dashboard')
            ->has('menu.0.href')
            ->where('dashboardWidgets.0.key', 'core-stats'));

    // 4) Users index is reachable (permission middleware + policy allow admin).
    $this->get(route('penova.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Core/Users/Index'));

    // 5) Create a new user from the panel.
    $this->post(route('penova.users.store'), [
        'name' => 'Jane Example',
        'email' => 'jane@example.com',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
        'roles' => [],
    ])->assertRedirect(route('penova.users.index'));

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);

    // RecordsActivity on the User model wrote the audit entry.
    $this->assertDatabaseHas('activity_logs', ['action' => 'users.created']);

    // 6) The new user shows up in the (searchable) list.
    $this->get(route('penova.users.index', ['search' => 'jane']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Users/Index')
            ->where('users.data.0.email', 'jane@example.com'));

    // 7) Logout ends the session and the panel is guarded again.
    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
    $this->get(route('penova.dashboard'))->assertRedirect(route('login'));
});

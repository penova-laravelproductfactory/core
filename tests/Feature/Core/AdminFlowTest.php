<?php

/*
|--------------------------------------------------------------------------
| Core — the "Workspace experience" system test
|--------------------------------------------------------------------------
| This is the end-to-end contract of Penova Core and must ALWAYS be
| green: a fresh database is migrated and seeded, the seeded Operator logs
| in, sees the Workspace, opens Users, creates a user, sees them in the
| list (with the audit log written), then logs out and is locked back
| out of the Workspace. If a change breaks any step of this flow, Core
| is not releasable.
*/

use Database\Seeders\PenovaCoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('the full Workspace experience works end to end', function () {
    // 1) Fresh database: RefreshDatabase migrated it; seed the Core baseline.
    $this->seed(PenovaCoreSeeder::class);

    // 2) Log in with the seeded admin credentials (config-driven).
    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ])->assertRedirect(route('penova.workspace'));

    $this->assertAuthenticated();

    // 3) The workspace renders its Inertia page, with the panel
    //    composition props shared by HandleInertiaRequests: the sidebar
    //    menu (Core items first — lowest order — plus module items) and
    //    the dashboard widget descriptors (Core + modules, order-sorted).
    $this->get(route('penova.workspace'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Workspace/Index')
            ->where('menu', fn ($menu) => collect($menu)
                ->contains(fn ($item) => $item['key'] === 'workspace' && filled($item['href'] ?? null)))
            // Core widgets omit 'area'; the provider normalises it to 'core'.
            ->where('dashboardWidgets', fn ($widgets) => collect($widgets)
                ->contains(fn ($widget) => $widget['key'] === 'core-stats' && ($widget['area'] ?? null) === 'core'))
            ->has('widgetAreas.core'));

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
            ->where('users.data', fn ($users) => collect($users)->contains('email', 'jane@example.com')));

    // 7) Logout ends the session and the panel is guarded again.
    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
    $this->get(route('penova.workspace'))->assertRedirect(route('login'));
});

test('module menu items and widgets are permission-filtered', function () {
    // Core baseline only — the admin has Core permissions but none of
    // the module ones (those come from the module seeders).
    $this->seed(PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ]);

    // Without store.view: no sidebar item, no dashboard widget, 403.
    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu', fn ($menu) => ! collect($menu)->contains('key', 'store'))
            ->where('dashboardWidgets', fn ($widgets) => ! collect($widgets)->contains('key', 'store-active-products')));

    $this->get('/workspace/store/products')->assertForbidden();

    // Grant the module permissions the product-composition way.
    $this->seed(\App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class);

    // Feature tests reuse one app instance, so the session guard still
    // holds the pre-seeding user model (with stale cached relations).
    // Real requests are fresh processes — simulate that.
    $this->app['auth']->forgetGuards();

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu', fn ($menu) => collect($menu)->contains('key', 'store'))
            ->where('dashboardWidgets', fn ($widgets) => collect($widgets)->contains('key', 'store-active-products')));

    $this->get('/workspace/store/products')->assertOk();
});

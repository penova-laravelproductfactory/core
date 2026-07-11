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

    // 2) Log in with the seeded Operator credentials (config-driven).
    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ])->assertRedirect(route('penova.workspace'));

    $this->assertAuthenticated();

    // 3) The workspace renders its Inertia page, with the panel
    //    composition props shared by HandleInertiaRequests: the sidebar
    //    menu (Core items first — lowest order — plus module items) and
    //    the widget descriptors (Core + modules, order-sorted).
    $this->get(route('penova.workspace'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Core/Workspace/Index')
            ->where('menu', fn ($menu) => collect($menu)
                ->contains(fn ($item) => $item['key'] === 'workspace' && filled($item['href'] ?? null)))
            // Core widgets omit 'area'; the provider normalises it to 'core'.
            ->where('widgets', fn ($widgets) => collect($widgets)
                ->contains(fn ($widget) => $widget['key'] === 'core-stats' && ($widget['area'] ?? null) === 'core'))
            ->has('widgetAreas.core'));

    // 4) Users index is reachable (permission middleware + policy allow the Operator).
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

test('Core ships with no business module enabled by default (D-026)', function () {
    // Core is complete on its own: the modules default is empty, so the
    // Workspace composition carries only Core contributions — no module menu
    // items or widgets. (The Store-enabled half is in the Store lane —
    // Feature/Store/ModuleCompositionTest.)
    expect(config('penova.modules'))->toBe([]);

    $this->seed(PenovaCoreSeeder::class);
    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ]);

    $this->get(route('penova.workspace'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu', fn ($menu) => ! collect($menu)->contains('key', 'store'))
            ->where('widgets', fn ($widgets) => ! collect($widgets)->contains('key', 'store-active-products')));
});

test('login returns the guest to the originally-requested Workspace URL (generic intended redirect)', function () {
    $this->seed(PenovaCoreSeeder::class);

    // A guest deep-linking into the Workspace is bounced to login; Laravel
    // stores the intended URL.
    $this->get(route('penova.users.index'))->assertRedirect(route('login'));

    // After login the framework-generic intended() redirect returns them
    // there — Core auth carries no module/checkout-specific handling (D-026).
    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ])->assertRedirect(route('penova.users.index'));
});

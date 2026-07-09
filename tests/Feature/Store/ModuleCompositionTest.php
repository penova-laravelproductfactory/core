<?php

use App\Core\Support\ManifestRegistry;
use Database\Seeders\PenovaCoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The Store-enabled half of the module-composition promises (D-026 §6c).
 * These run in the Store lane (StoreTestCase enables the Store module), so
 * they prove that with a module explicitly enabled, Core's registry,
 * Workspace view-model, route prefix, and permission filtering all surface
 * the module's contributions. The Core-empty half — no module listed,
 * registered, or routed by default — lives in the Feature/Core lane.
 */

uses(RefreshDatabase::class);

test('the registry exposes an enabled module manifest', function () {
    $registry = app(ManifestRegistry::class);

    expect($registry->has('store'))->toBeTrue();

    $store = $registry->get('store');
    expect($store['key'])->toBe('store');
    expect($store['name'])->not->toBeEmpty();
    expect($store['description'])->not->toBeEmpty();
    expect($store['version'])->not->toBeEmpty();
});

test('the platform view-model lists an enabled module manifest', function () {
    $this->seed(PenovaCoreSeeder::class);
    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ]);

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('platform.modules', fn ($modules) => collect($modules)->contains('key', 'store')));
});

test('an enabled module route resolves from the configured Workspace prefix', function () {
    $prefix = config('penova.workspace.prefix');

    expect($prefix)->not->toBeEmpty();
    expect(route('store.products.index', absolute: false))->toStartWith("/{$prefix}/");
});

test('an enabled module menu and widgets are permission-filtered', function () {
    $this->seed(PenovaCoreSeeder::class);
    $this->post('/login', [
        'email' => config('penova.operator.email'),
        'password' => config('penova.operator.password'),
    ]);

    // Without store.view: the module's sidebar item and widget are filtered
    // out, and its route 403s (the route exists — Store is enabled — but the
    // permission is missing).
    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu', fn ($menu) => ! collect($menu)->contains('key', 'store'))
            ->where('widgets', fn ($widgets) => ! collect($widgets)->contains('key', 'store-active-products')));

    $this->get('/workspace/store/products')->assertForbidden();

    // Grant the module permissions the application-composition way.
    $this->seed(\App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class);

    // Feature tests reuse one app instance, so the session guard still holds
    // the pre-seeding user model with stale cached relations — real requests
    // are fresh processes; simulate that.
    $this->app['auth']->forgetGuards();

    $this->get(route('penova.workspace'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu', fn ($menu) => collect($menu)->contains('key', 'store'))
            ->where('widgets', fn ($widgets) => collect($widgets)->contains('key', 'store-active-products')));

    $this->get('/workspace/store/products')->assertOk();
});

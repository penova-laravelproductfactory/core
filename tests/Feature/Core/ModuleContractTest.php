<?php

use App\Core\Support\Manifest;
use App\Core\Support\ManifestRegistry;
use App\Core\Support\PenovaModule;

/**
 * The Core↔Module seam (T2): a conforming Module's Manifest contributions
 * compose into the Platform. Defends the promise by presence (key /
 * contains), never by index, order, or carrier internals (D-023; 16).
 */
class FakeContractModule implements PenovaModule
{
    public static function manifest(): Manifest
    {
        return Manifest::for(
            key: 'fake',
            name: 'Fake Module',
            description: 'A conforming module used to defend the contract.',
            version: '1.2.3',
        )
            ->menu([['key' => 'fake', 'label' => 'Fake', 'route' => 'fake.index', 'icon' => 'squares', 'order' => 700, 'permission' => 'fake.view']])
            ->widgets([['key' => 'fake-widget', 'type' => 'card', 'title' => 'Fake', 'component' => 'Modules/Fake/Widgets/Fake', 'cols' => 1, 'order' => 700, 'area' => 'fake', 'permission' => 'fake.view']])
            ->permissions(['fake.view', 'fake.manage']);
    }
}

beforeEach(function () {
    config(['penova.modules' => [FakeContractModule::class]]);
    app()->forgetInstance(ManifestRegistry::class);
    app()->forgetInstance('penova.menu');
    app()->forgetInstance('penova.widgets');
    app()->forgetInstance('penova.permissions');
});

test('a conforming module is registered by its manifest identity', function () {
    $registry = app(ManifestRegistry::class);

    expect($registry->has('fake'))->toBeTrue();
    expect($registry->get('fake')['name'])->toBe('Fake Module');
});

test('a conforming module contributes its menu, widgets and permissions to the panel', function () {
    // Presence, not position: the promise is that the contribution appears,
    // not where it sorts or how the descriptor is carried.
    expect(collect(app('penova.menu'))->pluck('key'))->toContain('fake');
    expect(collect(app('penova.widgets'))->pluck('key'))->toContain('fake-widget');
    expect(app('penova.permissions'))->toContain('fake.view')->toContain('fake.manage');
});

test('a permissioned contribution keeps its permission so Core can filter it', function () {
    // The author-facing promise: a declared 'permission' survives collection,
    // so HandleInertiaRequests can drop it for users who lack it. We defend
    // that the key is preserved, not the filtering mechanics (covered by the
    // request-level tests).
    $item = collect(app('penova.menu'))->firstWhere('key', 'fake');

    expect($item['permission'])->toBe('fake.view');
});

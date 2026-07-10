<?php

use App\Core\Support\Manifest;

/**
 * The Manifest value object — the carrier for a Module's single
 * declaration. These defend its declared shape and its declaration-like
 * (immutable) nature, not any storage internal (D-023 guardrail 1).
 */
test('a manifest declares identity and exposes it', function () {
    $manifest = Manifest::for('store', 'Store', 'A store.', '0.1.0');

    expect($manifest->key())->toBe('store');
    expect($manifest->identity())->toBe([
        'key' => 'store',
        'name' => 'Store',
        'description' => 'A store.',
        'version' => '0.1.0',
    ]);
});

test('a manifest requires every identity field', function () {
    expect(fn () => Manifest::for('', 'Store', 'x', '0.1.0'))->toThrow(InvalidArgumentException::class);
    expect(fn () => Manifest::for('store', '', 'x', '0.1.0'))->toThrow(InvalidArgumentException::class);
    expect(fn () => Manifest::for('store', 'Store', '', '0.1.0'))->toThrow(InvalidArgumentException::class);
    expect(fn () => Manifest::for('store', 'Store', 'x', ''))->toThrow(InvalidArgumentException::class);
});

test('a manifest declares menu, widget and permission sections', function () {
    $manifest = Manifest::for('store', 'Store', 'x', '0.1.0')
        ->menu([['key' => 'store']])
        ->widgets([['key' => 'store-widget']])
        ->permissions(['store.view']);

    expect($manifest->menuItems())->toBe([['key' => 'store']]);
    expect($manifest->widgetDescriptors())->toBe([['key' => 'store-widget']]);
    expect($manifest->permissionSlugs())->toBe(['store.view']);
});

test('unused sections default to empty', function () {
    $manifest = Manifest::for('store', 'Store', 'x', '0.1.0');

    expect($manifest->menuItems())->toBe([]);
    expect($manifest->widgetDescriptors())->toBe([]);
    expect($manifest->permissionSlugs())->toBe([]);
});

test('a manifest is declaration-like: declaring a section does not mutate the original', function () {
    $base = Manifest::for('store', 'Store', 'x', '0.1.0');
    $withMenu = $base->menu([['key' => 'store']]);

    expect($base->menuItems())->toBe([]);                       // original untouched
    expect($withMenu->menuItems())->toBe([['key' => 'store']]); // new instance carries it
    expect($withMenu)->not->toBe($base);
});

test('a manifest serialises to its named sections', function () {
    $manifest = Manifest::for('store', 'Store', 'x', '0.1.0')->permissions(['store.view']);

    expect(array_keys($manifest->toArray()))->toBe(['identity', 'menu', 'widgets', 'permissions', 'frontend']);
});

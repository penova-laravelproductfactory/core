<?php

use App\Core\Support\FrontendRegistry;

/**
 * Combined P2/P3 — the EXPERIMENTAL module-frontend registry generator
 * (RFC-006 / D-028): module-owned source coordinate, deterministic output, and
 * the full loud named-failure taxonomy. The registry is a git-ignored build
 * artifact — these tests exercise the generator directly.
 */

function storeModule(array $overrides = []): array
{
    return array_replace([
        'key' => 'store',
        'source' => '@/Modules/Store',
        'widgets' => [['key' => 'store-active-products', 'area' => 'store']],
        'frontend' => [
            'widgets' => [['key' => 'store-active-products', 'entry' => 'Widgets/ActiveProductsCard']],
            'pages' => [['name' => 'Modules/Store/Products/Index', 'entry' => 'Pages/Products/Index']],
        ],
    ], $overrides);
}

test('build maps contributions to specifiers from each Module OWN coordinate', function () {
    $map = FrontendRegistry::build([storeModule()]);

    expect($map)->toBe([
        'widgets' => ['store-active-products' => '@/Modules/Store/Widgets/ActiveProductsCard.vue'],
        'pages' => ['Modules/Store/Products/Index' => '@/Modules/Store/Pages/Products/Index.vue'],
    ]);
});

test('build is deterministic — identical inputs render byte-for-byte identical, sorted output', function () {
    $modules = [storeModule()];
    expect(FrontendRegistry::render(FrontendRegistry::build($modules)))
        ->toBe(FrontendRegistry::render(FrontendRegistry::build($modules)));
});

test('a fully paired module with a passing resolver builds cleanly', function () {
    $map = FrontendRegistry::build([storeModule()], fn (string $s) => true);
    expect($map['widgets'])->toHaveKey('store-active-products');
    expect($map['pages'])->toHaveKey('Modules/Store/Products/Index');
});

// --- Named failure taxonomy: one negative per category (all loud) ---

test('duplicate contribution — widget key across Modules', function () {
    FrontendRegistry::build([
        storeModule(),
        storeModule(['key' => 'other', 'source' => '@/Modules/Other', 'frontend' => [
            'widgets' => [['key' => 'store-active-products', 'entry' => 'Widgets/Dup']],
            'pages' => [],
        ], 'widgets' => [['key' => 'store-active-products', 'area' => 'x']]]),
    ]);
})->throws(InvalidArgumentException::class, 'duplicate contribution — widget key [store-active-products]');

test('duplicate contribution — page name across Modules', function () {
    FrontendRegistry::build([
        storeModule(),
        storeModule(['key' => 'other', 'source' => '@/Modules/Other', 'widgets' => [], 'frontend' => [
            'widgets' => [],
            'pages' => [['name' => 'Modules/Store/Products/Index', 'entry' => 'Pages/X']],
        ]]),
    ]);
})->throws(InvalidArgumentException::class, 'duplicate contribution — page name [Modules/Store/Products/Index]');

test('duplicate contribution — a page name in the reserved Core namespace', function () {
    FrontendRegistry::build([storeModule(['widgets' => [], 'frontend' => [
        'widgets' => [],
        'pages' => [['name' => 'Core/Users/Index', 'entry' => 'Pages/X']],
    ]])]);
})->throws(InvalidArgumentException::class, 'collides with the reserved Core namespace');

test('missing frontend entry — a backend widget with no frontend contribution', function () {
    FrontendRegistry::build([storeModule(['frontend' => ['widgets' => [], 'pages' => []]])]);
})->throws(InvalidArgumentException::class, 'missing frontend entry — backend widget [store-active-products]');

test('orphan frontend widget — a frontend widget with no backend declaration', function () {
    FrontendRegistry::build([storeModule(['widgets' => [], 'frontend' => [
        'widgets' => [['key' => 'ghost', 'entry' => 'Widgets/Ghost']],
        'pages' => [],
    ]])]);
})->throws(InvalidArgumentException::class, 'orphan frontend widget — frontend widget [ghost]');

test('unknown target area — a backend widget declares an empty area', function () {
    FrontendRegistry::build([storeModule(['widgets' => [['key' => 'store-active-products', 'area' => '']]])]);
})->throws(InvalidArgumentException::class, 'unknown target area — backend widget [store-active-products]');

test('unresolved entry — an entry that does not resolve through its coordinate', function () {
    FrontendRegistry::build([storeModule()], fn (string $s) => false);
})->throws(InvalidArgumentException::class, 'unresolved entry');

// --- Provenance ---

test('a hand-edited artifact fails the integrity check (provenance)', function () {
    $artifact = FrontendRegistry::render(FrontendRegistry::build([storeModule()]));
    expect(FrontendRegistry::verifyIntegrity($artifact))->toBeTrue();
    expect(FrontendRegistry::verifyIntegrity(str_replace('ActiveProductsCard', 'Hacked', $artifact)))->toBeFalse();
});

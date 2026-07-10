<?php

use App\Core\Support\FrontendRegistry;

/**
 * P4 — module frontend LIFECYCLE (RFC-006 / D-028). One command
 * (`php artisan penova:frontend-registry`) regenerates the git-ignored registry,
 * and every add / enable / disable / update / remove of a module produces the
 * expected registry state after that rebuild. Generic: fake module inputs, no
 * real Module.
 */

function lcModule(string $key, array $pages, array $widgets = []): array
{
    return [
        'key' => $key,
        'source' => "@/Modules/{$key}",
        'widgets' => $widgets,
        'frontend' => ['widgets' => [], 'pages' => $pages],
    ];
}

test('remove / disable — an empty module set yields an empty registry', function () {
    expect(FrontendRegistry::build([]))->toBe(['widgets' => [], 'pages' => []]);
});

test('add / enable — a newly enabled module contributes its entries after rebuild', function () {
    $map = FrontendRegistry::build([lcModule('alpha', [['name' => 'Alpha/Index', 'entry' => 'Pages/Index']])]);

    expect($map['pages'])->toBe(['Alpha/Index' => '@/Modules/alpha/Pages/Index.vue']);
});

test('update — changing a module’s contributions replaces its registry entries after rebuild', function () {
    $before = FrontendRegistry::build([lcModule('alpha', [['name' => 'Alpha/Index', 'entry' => 'Pages/Index']])]);
    $after = FrontendRegistry::build([lcModule('alpha', [['name' => 'Alpha/List', 'entry' => 'Pages/List']])]);

    expect($before['pages'])->toHaveKey('Alpha/Index');
    expect($after['pages'])->not->toHaveKey('Alpha/Index');
    expect($after['pages'])->toHaveKey('Alpha/List');
});

test('disable one of two — removing a module drops only its entries', function () {
    $both = FrontendRegistry::build([
        lcModule('alpha', [['name' => 'Alpha/Index', 'entry' => 'Pages/Index']]),
        lcModule('beta', [['name' => 'Beta/Index', 'entry' => 'Pages/Index']]),
    ]);
    $one = FrontendRegistry::build([lcModule('alpha', [['name' => 'Alpha/Index', 'entry' => 'Pages/Index']])]);

    expect($both['pages'])->toHaveKeys(['Alpha/Index', 'Beta/Index']);
    expect($one['pages'])->toHaveKey('Alpha/Index');
    expect($one['pages'])->not->toHaveKey('Beta/Index');
});

test('one documented command regenerates the registry and runs for every frontend build', function () {
    $scripts = json_decode((string) file_get_contents(base_path('package.json')), true)['scripts'];

    expect($scripts['frontend-registry'])->toContain('penova:frontend-registry');
    expect($scripts['build'])->toStartWith('php artisan penova:frontend-registry');
    expect($scripts['dev'])->toStartWith('php artisan penova:frontend-registry');
});

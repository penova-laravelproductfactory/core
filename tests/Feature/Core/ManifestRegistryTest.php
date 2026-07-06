<?php

use App\Core\Support\ManifestRegistry;

test('registry exposes installed module manifests', function () {
    $registry = app(ManifestRegistry::class);

    expect($registry->has('store'))->toBeTrue();

    $store = $registry->get('store');
    expect($store['key'])->toBe('store');
    expect($store['name'])->not->toBeEmpty();
    expect($store['description'])->not->toBeEmpty();
    expect($store['version'])->not->toBeEmpty();
});

test('registry is empty when no modules are installed', function () {
    config(['penova.modules' => []]);
    app()->forgetInstance(ManifestRegistry::class);

    $registry = app(ManifestRegistry::class);

    expect($registry->isEmpty())->toBeTrue();
    expect($registry->all())->toBe([]);
    expect($registry->get('store'))->toBeNull();
});

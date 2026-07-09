<?php

use App\Core\Support\ManifestRegistry;

test('the registry is empty by default (D-026 — Core enables no module)', function () {
    // Core enables no business module by default, so the registry resolves
    // empty. (The Store-enabled half lives in the Store lane —
    // Feature/Store/ModuleCompositionTest.)
    $registry = app(ManifestRegistry::class);

    expect($registry->isEmpty())->toBeTrue();
    expect($registry->all())->toBe([]);
    expect($registry->get('store'))->toBeNull();
});

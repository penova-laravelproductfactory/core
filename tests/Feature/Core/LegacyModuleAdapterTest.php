<?php

use App\Core\Support\LegacyModuleManifest;
use App\Core\Support\ManifestRegistry;

/**
 * The one-way deprecation bridge: a Module still on the pre-D-023
 * scattered hooks is adapted into a single Manifest, and a deprecation is
 * signalled. Defends that legacy Modules keep working for one cycle
 * (D-023 §4; guardrail 3).
 */
class FakeLegacyModule implements LegacyModuleManifest
{
    public static function menu(): array
    {
        return [['key' => 'legacy', 'label' => 'Legacy', 'route' => 'legacy.index', 'icon' => 'squares', 'order' => 800]];
    }

    public static function widgets(): array
    {
        return [['key' => 'legacy-widget', 'type' => 'card', 'title' => 'Legacy', 'component' => 'Modules/Legacy/Widgets/Legacy', 'cols' => 1, 'order' => 800]];
    }

    public static function permissions(): array
    {
        return ['legacy.view'];
    }

    public static function manifest(): array
    {
        return ['key' => 'legacy', 'name' => 'Legacy Module', 'description' => 'A legacy module.', 'version' => '0.9.0'];
    }
}

test('a legacy module is adapted one-way into a manifest, with a deprecation signal', function () {
    config(['penova.modules' => [FakeLegacyModule::class]]);
    app()->forgetInstance(ManifestRegistry::class);

    // Capture (and swallow) the expected deprecation so it does not surface
    // as a failure, and so we can assert it fired.
    $deprecation = null;
    set_error_handler(function (int $errno, string $message) use (&$deprecation) {
        $deprecation = $message;

        return true;
    }, E_USER_DEPRECATED);

    try {
        $registry = app(ManifestRegistry::class);
    } finally {
        restore_error_handler();
    }

    // The legacy hooks are adapted into the one resolved manifest set.
    expect($registry->has('legacy'))->toBeTrue();
    expect($registry->get('legacy')['name'])->toBe('Legacy Module');
    expect(collect($registry->menuItems())->pluck('key'))->toContain('legacy');
    expect(collect($registry->widgetDescriptors())->pluck('key'))->toContain('legacy-widget');
    expect($registry->permissionSlugs())->toContain('legacy.view');

    // And the deprecation was signalled, naming the offending provider.
    expect($deprecation)->toContain('deprecated');
    expect($deprecation)->toContain(FakeLegacyModule::class);
});

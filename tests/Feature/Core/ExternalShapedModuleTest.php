<?php

use App\Core\Support\DeclaresFrontendPackage;
use App\Core\Support\DeclaresFrontendSource;
use App\Core\Support\FrontendPackageCheck;
use App\Core\Support\FrontendRegistry;
use App\Core\Support\Manifest;
use App\Core\Support\ManifestRegistry;
use App\Core\Support\PenovaModule;
use Illuminate\Support\ServiceProvider;

/**
 * P4 — EXTERNAL-SHAPED fixture (RFC-006 / D-028). A fake Module whose provider
 * owns a frontend coordinate DISTINCT from the in-repo `@/Modules/{key}` default
 * and declares a separate frontend package pairing. It is package-shaped but
 * LOCAL: no publishing, no Composer/npm package, no file moves. It proves the
 * SAME generic generator/resolver path — the one Store uses — resolves a
 * provider-owned coordinate and enforces pairing/peers, with Core naming no
 * Module. Store-free by design (it is not the in-repo reference Module).
 */

/** A package-shaped, out-of-tree Module — coordinate and package owned by the provider. */
class AcmeCatalogServiceProvider extends ServiceProvider implements DeclaresFrontendPackage, DeclaresFrontendSource, PenovaModule
{
    public static function manifest(): Manifest
    {
        return Manifest::for('acme-catalog', 'Acme Catalog', 'External-shaped reference module.', '1.0.0')
            ->widgets([
                ['key' => 'catalog-featured', 'type' => 'card', 'title' => 'Featured', 'cols' => 1, 'order' => 100, 'area' => 'catalog'],
            ])
            ->frontend([
                'widgets' => [['key' => 'catalog-featured', 'entry' => 'Widgets/FeaturedCard']],
                'pages' => [['name' => 'Acme/Catalog/Index', 'entry' => 'Pages/Catalog/Index']],
            ]);
    }

    // Provider-owned coordinate — a package-style root, NOT the @/Modules/{key} default.
    public static function frontendSource(): string
    {
        return '@acme-catalog';
    }

    public static function frontendPackage(): array
    {
        return [
            'name' => '@acme/catalog-frontend',
            'version' => '^1.0',
            'peers' => ['vue' => '^3.5', '@inertiajs/vue3' => '^2.0'],
        ];
    }
}

beforeEach(function () {
    config()->set('penova.modules', [AcmeCatalogServiceProvider::class]);
    $this->registry = new ManifestRegistry();
});

test('Core reads the provider-owned coordinate generically — distinct from the in-repo default', function () {
    $modules = $this->registry->frontendModules();

    expect($modules)->toHaveCount(1);
    expect($modules[0]['source'])->toBe('@acme-catalog');
    expect($modules[0]['source'])->not->toBe('@/Modules/acme-catalog'); // the default it overrides
});

test('widget and page contributions resolve through the SAME generic generator/resolver path', function () {
    $map = FrontendRegistry::build(
        $this->registry->frontendModules(),
        fn (string $specifier): bool => str_starts_with($specifier, '@acme-catalog/'), // the package's own resolver
    );

    expect($map['widgets'])->toBe(['catalog-featured' => '@acme-catalog/Widgets/FeaturedCard.vue']);
    expect($map['pages'])->toBe(['Acme/Catalog/Index' => '@acme-catalog/Pages/Catalog/Index.vue']);
});

test('the declared package pairing surfaces through the provider boundary and passes when matched', function () {
    $packages = $this->registry->frontendPackages();

    expect($packages)->toBe([[
        'key' => 'acme-catalog',
        'package' => ['name' => '@acme/catalog-frontend', 'version' => '^1.0', 'peers' => ['vue' => '^3.5', '@inertiajs/vue3' => '^2.0']],
    ]]);

    // Passes (no throw) — a matched package with compatible peers.
    FrontendPackageCheck::verify(
        $packages,
        ['@acme/catalog-frontend' => '1.4.0'],
        ['vue' => '3.5.13', '@inertiajs/vue3' => '2.0.11'],
    );

    expect(true)->toBeTrue();
});

test('a mismatched installed package for the external-shaped Module fails loudly', function () {
    FrontendPackageCheck::verify(
        $this->registry->frontendPackages(),
        [], // the paired package is not installed
        ['vue' => '3.5.13', '@inertiajs/vue3' => '2.0.11'],
    );
})->throws(InvalidArgumentException::class, 'module frontend package mismatch');

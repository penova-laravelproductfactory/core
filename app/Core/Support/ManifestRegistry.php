<?php

namespace App\Core\Support;

/**
 * Core\Support — the installed Modules' Manifest registry.
 *
 * The single source of truth for what every installed Module contributes.
 * It resolves config('penova.modules') exactly ONCE: each provider
 * implementing the {@see PenovaModule} contract yields its {@see Manifest};
 * a provider still on the deprecated {@see LegacyModuleManifest} hooks is
 * adapted into a Manifest (one-way) with an E_USER_DEPRECATED signal;
 * providers implementing neither contribute nothing.
 *
 * Everything the Platform composes from Modules — the readable identity
 * manifests (Workspace + future tooling), the sidebar menu items, the
 * widget descriptors, and the declared permission slugs — derives
 * from this one resolved set, so the module list is never iterated in
 * parallel (D-023). Registered as a singleton by PenovaCoreServiceProvider.
 */
final class ManifestRegistry
{
    /** @var list<Manifest> */
    private array $manifests;

    /**
     * Module key → its OWN frontend coordinate root (EXPERIMENTAL — RFC-006 /
     * D-028). Read from the provider boundary, not the Manifest: it is module-owned
     * build/install metadata, never a Manifest contribution.
     *
     * @var array<string, string>
     */
    private array $frontendSources = [];

    /**
     * Module key → its declared frontend package pairing (EXPERIMENTAL — RFC-006 /
     * D-028), for Modules whose provider implements {@see DeclaresFrontendPackage}.
     * Also read from the provider boundary, never the Manifest.
     *
     * @var array<string, array{name: string, version: string, peers?: array<string, string>}>
     */
    private array $frontendPackages = [];

    public function __construct()
    {
        $manifests = [];

        foreach (config('penova.modules', []) as $provider) {
            $manifest = $this->resolve($provider);

            if ($manifest === null) {
                continue;
            }

            $manifests[] = $manifest;
            $this->frontendSources[$manifest->key()] = $this->frontendSource($provider, $manifest->key());

            if (is_subclass_of($provider, DeclaresFrontendPackage::class)) {
                $this->frontendPackages[$manifest->key()] = $provider::frontendPackage();
            }
        }

        $this->manifests = $manifests;
    }

    /**
     * Resolve a provider class-string to its Manifest, or null if it
     * implements no Module contract. Legacy providers are adapted one-way
     * and flagged deprecated.
     */
    private function resolve(string $provider): ?Manifest
    {
        if (is_subclass_of($provider, PenovaModule::class)) {
            return $provider::manifest();
        }

        if (is_subclass_of($provider, LegacyModuleManifest::class)) {
            trigger_error(sprintf(
                'Module [%s] uses the deprecated scattered-hook contract; implement %s and return a single manifest(): %s. The legacy contract is removed at the next MAJOR.',
                $provider,
                PenovaModule::class,
                Manifest::class,
            ), E_USER_DEPRECATED);

            $identity = $provider::manifest();

            return Manifest::for(
                $identity['key'],
                $identity['name'],
                $identity['description'],
                $identity['version'],
            )
                ->menu($provider::menu())
                ->widgets($provider::widgets())
                ->permissions($provider::permissions());
        }

        return null;
    }

    /**
     * A Module's OWN frontend coordinate root (EXPERIMENTAL — RFC-006 / D-028) —
     * module-owned build metadata read from the PROVIDER, never the Manifest. A
     * provider MAY declare it by implementing {@see DeclaresFrontendSource};
     * otherwise the in-repo default `@/Modules/{key}` is derived from the key.
     * Core names no specific Module: it reads the provider generically.
     */
    private function frontendSource(string $provider, string $key): string
    {
        if (is_subclass_of($provider, DeclaresFrontendSource::class)) {
            return $provider::frontendSource();
        }

        return "@/Modules/{$key}";
    }

    /**
     * The installed Modules' identity manifests — the public JSON shape the
     * Workspace and future tooling read.
     *
     * @return list<array{key: string, name: string, description: string, version: string}>
     */
    public function all(): array
    {
        return array_map(fn (Manifest $manifest) => $manifest->identity(), $this->manifests);
    }

    /** @return array{key: string, name: string, description: string, version: string}|null */
    public function get(string $key): ?array
    {
        return collect($this->all())->firstWhere('key', $key);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function isEmpty(): bool
    {
        return $this->manifests === [];
    }

    /**
     * The sidebar items every installed Module contributes (Core adds its own).
     *
     * @return list<array<string, mixed>>
     */
    public function menuItems(): array
    {
        return collect($this->manifests)->flatMap(fn (Manifest $manifest) => $manifest->menuItems())->all();
    }

    /**
     * The widget descriptors every installed Module contributes.
     *
     * @return list<array<string, mixed>>
     */
    public function widgetDescriptors(): array
    {
        return collect($this->manifests)->flatMap(fn (Manifest $manifest) => $manifest->widgetDescriptors())->all();
    }

    /**
     * The permission slugs every installed Module declares (deduped).
     *
     * @return list<string>
     */
    public function permissionSlugs(): array
    {
        return collect($this->manifests)
            ->flatMap(fn (Manifest $manifest) => $manifest->permissionSlugs())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Each installed Module's generic frontend-registry input (RFC-006 / D-028):
     * its key, its OWN coordinate root (from the provider boundary, not the
     * Manifest), its backend widget descriptors (for the widget join + area
     * checks), and its EXPERIMENTAL frontend contributions. Iterates the resolved
     * manifests; names no specific Module.
     *
     * @return list<array{key: string, source: string, widgets: list<array<string, mixed>>, frontend: array{widgets: list<array{key: string, entry: string}>, pages: list<array{name: string, entry: string}>}}>
     */
    public function frontendModules(): array
    {
        return array_map(fn (Manifest $manifest) => [
            'key' => $manifest->key(),
            'source' => $this->frontendSources[$manifest->key()] ?? "@/Modules/{$manifest->key()}",
            'widgets' => $manifest->widgetDescriptors(),
            'frontend' => $manifest->frontendContributions(),
        ], $this->manifests);
    }

    /**
     * The declared frontend PACKAGE pairings of the installed Modules that have one
     * (EXPERIMENTAL — RFC-006 / D-028), for the pairing + peer checks. Read from
     * the provider boundary; Modules that ship their frontend in-repo declare none
     * and are absent here. Names no specific Module.
     *
     * @return list<array{key: string, package: array{name: string, version: string, peers?: array<string, string>}}>
     */
    public function frontendPackages(): array
    {
        $out = [];

        foreach ($this->frontendPackages as $key => $package) {
            $out[] = ['key' => $key, 'package' => $package];
        }

        return $out;
    }
}

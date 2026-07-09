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

    public function __construct()
    {
        $this->manifests = collect(config('penova.modules', []))
            ->map(fn (string $provider) => $this->resolve($provider))
            ->filter()
            ->values()
            ->all();
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
}

<?php

namespace App\Core\Support;

/**
 * Core\Support — the installed modules' manifest registry.
 *
 * Reads config('penova.modules') and collects the manifest() of every
 * provider implementing the PenovaModule contract. A single source of
 * truth for "what modules are installed", consumed by the Workspace and
 * (later) the Marketplace, CLI and Module Manager. Registered as a
 * singleton by PenovaCoreServiceProvider.
 */
final class ManifestRegistry
{
    /** @var list<array{key: string, name: string, description: string, version: string}> */
    private array $manifests;

    public function __construct()
    {
        $this->manifests = collect(config('penova.modules', []))
            ->filter(fn (string $provider) => is_subclass_of($provider, PenovaModule::class))
            ->map(fn (string $provider) => $provider::manifest())
            ->values()
            ->all();
    }

    /** @return list<array{key: string, name: string, description: string, version: string}> */
    public function all(): array
    {
        return $this->manifests;
    }

    public function get(string $key): ?array
    {
        return collect($this->manifests)->firstWhere('key', $key);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function isEmpty(): bool
    {
        return $this->manifests === [];
    }
}

<?php

namespace App\Core\Support\Commands;

use App\Core\Support\FrontendPackageCheck;
use App\Core\Support\FrontendRegistry;
use App\Core\Support\ManifestRegistry;
use Illuminate\Console\Command;

/**
 * EXPERIMENTAL (RFC-006 / D-028) — regenerate the module-frontend registry.
 *
 * The registry is a deterministic, git-ignored BUILD ARTIFACT derived from the
 * enabled Modules' Manifest `frontend` sections. It is generated, never
 * hand-authored, and never committed; every frontend build (and the relevant
 * test entry points) regenerate it first, and no consumer may use an absent,
 * hand-edited, or stale registry — `--check` enforces that in CI.
 */
class GenerateFrontendRegistryCommand extends Command
{
    protected $signature = 'penova:frontend-registry {--check : Verify the artifact exists, is untampered and up to date; write nothing and exit non-zero otherwise}';

    protected $description = 'Generate the experimental module-frontend registry (RFC-006 / D-028) from enabled Modules';

    public function handle(ManifestRegistry $registry): int
    {
        $path = self::path();

        // Loud, before-runtime pairing + peer checks (RFC-006 / D-028). A no-op for
        // in-repo Modules, which ship their frontend in the same tree and declare no
        // package; it fires only for a Module paired with a separate frontend package.
        $packages = $registry->frontendPackages();
        FrontendPackageCheck::verify(
            $packages,
            self::installedPackages(array_map(fn (array $p) => $p['package']['name'], $packages)),
            self::coreFrontendVersions(),
        );

        $fresh = FrontendRegistry::render(FrontendRegistry::build($registry->frontendModules(), self::inRepoResolver()));

        if ($this->option('check')) {
            if (! is_file($path)) {
                $this->error('Frontend registry missing — run `php artisan penova:frontend-registry` before building.');

                return self::FAILURE;
            }

            $current = (string) file_get_contents($path);

            if (! FrontendRegistry::verifyIntegrity($current)) {
                $this->error('Frontend registry has been hand-edited (integrity checksum mismatch) — regenerate; do not edit.');

                return self::FAILURE;
            }

            if ($current !== $fresh) {
                $this->error('Frontend registry is stale — the enabled Modules changed. Regenerate with `php artisan penova:frontend-registry`.');

                return self::FAILURE;
            }

            $this->info('Frontend registry is present, untampered and up to date.');

            return self::SUCCESS;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $fresh);
        $this->info('Frontend registry generated: '.$path);

        return self::SUCCESS;
    }

    /** The git-ignored artifact path (a build output, never source-controlled). */
    public static function path(): string
    {
        return resource_path('js/generated/module-frontend-registry.js');
    }

    /**
     * Resolves an in-repo `@/…` specifier against the frontend source tree — the
     * unresolved-entry check for the seam slice. An external package supplies its
     * own resolver through module-owned metadata (relocation slice), without
     * changing the generator.
     */
    private static function inRepoResolver(): callable
    {
        $root = resource_path('js');

        return fn (string $specifier): bool => str_starts_with($specifier, '@/')
            && is_file($root.'/'.substr($specifier, 2));
    }

    /**
     * Core's OWN declared frontend versions (dependencies + devDependencies from
     * package.json) — the peers a paired Module is checked against.
     *
     * @return array<string, string>
     */
    private static function coreFrontendVersions(): array
    {
        $json = json_decode((string) file_get_contents(base_path('package.json')), true) ?: [];

        return array_merge($json['devDependencies'] ?? [], $json['dependencies'] ?? []);
    }

    /**
     * The installed versions of the given frontend package names (read from
     * node_modules) — what a declared pairing is matched against.
     *
     * @param  list<string>  $names
     * @return array<string, string>
     */
    private static function installedPackages(array $names): array
    {
        $out = [];

        foreach ($names as $name) {
            $path = base_path('node_modules/'.$name.'/package.json');
            if (is_file($path)) {
                $json = json_decode((string) file_get_contents($path), true) ?: [];
                if (isset($json['version']) && is_string($json['version'])) {
                    $out[$name] = $json['version'];
                }
            }
        }

        return $out;
    }
}


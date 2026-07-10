<?php

namespace App\Core\Support;

use InvalidArgumentException;

/**
 * EXPERIMENTAL (RFC-006 / D-028) — module frontend PACKAGE PAIRING and PEER
 * compatibility checks. Both are LOUD at generation/build/boot, before runtime.
 *
 * A Module MAY pair its backend with a named frontend package (via
 * {@see DeclaresFrontendPackage}); an in-repo Module that ships its frontend in
 * the same tree declares none and is unaffected. When a pairing IS declared:
 *
 *   module frontend package mismatch — the paired package is absent from the
 *       installed frontend packages, or its installed MAJOR version differs from
 *       the declared one. Backend and frontend halves ship as a matched pair; a
 *       half-upgraded pair fails before it can mis-render.
 *
 *   module frontend peer incompatible — a framework peer the Module's frontend
 *       requires (vue, @inertiajs/vue3, …) is absent from, or its MAJOR differs
 *       from, Core's OWN declared frontend version. Core owns the frontend
 *       runtime; a Module built against an incompatible major fails loudly.
 *
 * Compatibility is judged by MAJOR version — an intentionally coarse, loud rule
 * for an experimental, not-yet-SemVer-frozen seam, not a full range solver. Core
 * names no Module: it iterates opaque provider declarations.
 */
final class FrontendPackageCheck
{
    /**
     * @param  iterable<array{key: string, package: array{name: string, version: string, peers?: array<string, string>}}>  $modules  only Modules that declared a package pairing
     * @param  array<string, string>  $installed  installed frontend package name → version
     * @param  array<string, string>  $core       Core's declared frontend dependency name → version
     */
    public static function verify(iterable $modules, array $installed, array $core): void
    {
        foreach ($modules as $module) {
            $key = $module['key'];
            $package = $module['package'];
            $name = $package['name'];
            $wanted = $package['version'];

            $installedVersion = $installed[$name] ?? null;
            if ($installedVersion === null) {
                throw new InvalidArgumentException("Frontend registry: module frontend package mismatch — module [{$key}] pairs with [{$name}@{$wanted}], but no such frontend package is installed.");
            }
            if (self::major($installedVersion) !== self::major($wanted)) {
                throw new InvalidArgumentException("Frontend registry: module frontend package mismatch — module [{$key}] pairs with [{$name}@{$wanted}], but [{$name}@{$installedVersion}] is installed (major version differs).");
            }

            foreach ($package['peers'] ?? [] as $peer => $constraint) {
                $coreVersion = $core[$peer] ?? null;
                if ($coreVersion === null) {
                    throw new InvalidArgumentException("Frontend registry: module frontend peer incompatible — module [{$key}] requires peer [{$peer} {$constraint}], but Core declares no [{$peer}].");
                }
                if (self::major($coreVersion) !== self::major($constraint)) {
                    throw new InvalidArgumentException("Frontend registry: module frontend peer incompatible — module [{$key}] requires peer [{$peer} {$constraint}], but Core provides [{$peer} {$coreVersion}] (major version differs).");
                }
            }
        }
    }

    /**
     * The MAJOR version token of a version or npm range — the leading integer
     * after any range operators (^ ~ >= <= > < =), whitespace and a `v` prefix.
     * `^3.5.0` → `3`, `~2.0` → `2`, `>=6.0.11` → `6`.
     */
    public static function major(string $version): string
    {
        $normalized = ltrim(trim($version), '^~<>= vV');
        $head = explode('.', $normalized)[0];

        return $head === '' ? $normalized : $head;
    }
}

<?php

namespace App\Core\Support;

/**
 * DEPRECATED — the pre-D-023 scattered-hook Module contract.
 *
 * Before the Manifest unification (RFC-001 / D-023), a Module contributed
 * through four separate static hooks instead of one Manifest. This interface
 * preserves that contract for ONE MAJOR line so existing Modules keep working
 * while they migrate: Core adapts these hooks into a {@see Manifest} — one-way
 * only, never the reverse — and emits an E_USER_DEPRECATED signal (see
 * {@see ManifestRegistry}).
 *
 * Migration: implement {@see PenovaModule} instead and return a single
 * Manifest from manifest(). This interface is removed at the next MAJOR.
 *
 * @deprecated Since the Manifest unification (D-023). Implement PenovaModule.
 */
interface LegacyModuleManifest
{
    /** @return list<array<string, mixed>> */
    public static function menu(): array;

    /** @return list<array<string, mixed>> */
    public static function widgets(): array;

    /** @return list<string> */
    public static function permissions(): array;

    /** @return array{key: string, name: string, description: string, version: string} */
    public static function manifest(): array;
}

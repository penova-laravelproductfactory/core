<?php

namespace App\Core\Support;

/**
 * The Penova module contract.
 *
 * A product Module's service provider implements this interface to
 * contribute to the Platform. A Module declares everything it contributes
 * through a SINGLE Manifest — its one coherent declaration — not through
 * scattered static hooks (D-005; ../../../strategy/06-glossary.md;
 * "small contracts" in ../../../strategy/13-architecture-principles.md). The
 * Manifest's shape is the governed public contract settled by RFC-001 / D-023.
 *
 * Core discovers Modules only through config('penova.modules') class-strings
 * and reads the Manifest of each provider implementing this interface (the
 * is_subclass_of check in {@see ManifestRegistry}). A provider that implements
 * neither this contract nor the deprecated {@see LegacyModuleManifest} still
 * boots, but contributes nothing to the Platform.
 *
 * The Manifest's sections (identity, menu, widgets, permissions) and their
 * item shapes are documented in app/Modules/README.md.
 *
 * @see Manifest
 */
interface PenovaModule
{
    /**
     * The Module's single Manifest — its complete declaration of what it
     * contributes to the Platform.
     */
    public static function manifest(): Manifest;
}

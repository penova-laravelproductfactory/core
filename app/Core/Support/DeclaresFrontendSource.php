<?php

namespace App\Core\Support;

/**
 * An OPTIONAL provider-level capability (EXPERIMENTAL — RFC-006 / D-028).
 *
 * A Module's service provider MAY implement this to declare its OWN frontend
 * coordinate root — the import-specifier prefix its Manifest `entry` tokens
 * resolve against (an in-repo alias like `@/Modules/<Name>` today, a package
 * specifier once the Module is physically relocated).
 *
 * This is module-owned BUILD/INSTALL metadata, deliberately NOT a Manifest
 * section: the Manifest is the governed public contribution contract (RFC-001 /
 * D-023), and the frontend `frontend` descriptor is its only D-028 addition. The
 * source coordinate is where the Module's frontend lives, an implementation fact
 * Core must not assume and must not freeze into the serialized Manifest. Core's
 * registry generator reads it here, generically, through the provider boundary —
 * naming no specific Module (13). A provider that omits it gets the in-repo
 * default `@/Modules/{key}` derived from the Module key.
 *
 * @see PenovaModule   the required contribution contract
 * @see ManifestRegistry::frontendModules()   the generic consumer
 */
interface DeclaresFrontendSource
{
    /**
     * The Module's own frontend coordinate root — an import-specifier prefix,
     * never a filesystem path Core walks. Registry output, not Core knowledge.
     */
    public static function frontendSource(): string;
}

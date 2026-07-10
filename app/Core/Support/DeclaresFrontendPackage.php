<?php

namespace App\Core\Support;

/**
 * An OPTIONAL provider-level capability (EXPERIMENTAL — RFC-006 / D-028).
 *
 * A Module whose frontend ships as a SEPARATE package (once physically
 * relocated) MAY declare the pairing so Core can fail loudly, before runtime,
 * when the two halves do not match:
 *
 *   [name, version] — the frontend package this Module's backend is PAIRED with.
 *       Core checks the installed frontend package's identity and MAJOR version
 *       against this; a missing or major-mismatched package is a loud
 *       "module frontend package mismatch" (see {@see FrontendPackageCheck}).
 *
 *   [peers] — the framework peers (vue, @inertiajs/vue3, …) the Module's
 *       frontend needs. Core checks each against its OWN declared frontend
 *       version; an incompatible major is a loud "module frontend peer
 *       incompatible".
 *
 * This is module-owned build/install metadata, deliberately NOT a Manifest
 * section — like {@see DeclaresFrontendSource}, it is where the Module's
 * frontend lives and what it needs, not what the Module contributes. An in-repo
 * Module that ships its frontend in the same tree declares NO package and is
 * unaffected; the pairing matters only once a frontend is a separate unit. Core
 * reads this generically through the provider boundary, naming no Module (13).
 *
 * @see DeclaresFrontendSource   the coordinate-root companion capability
 * @see FrontendPackageCheck     the generic consumer
 */
interface DeclaresFrontendPackage
{
    /**
     * The Module's frontend package pairing and peer requirements.
     *
     * @return array{name: string, version: string, peers?: array<string, string>}
     */
    public static function frontendPackage(): array;
}

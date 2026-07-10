<?php

namespace App\Core\Support;

use InvalidArgumentException;

/**
 * EXPERIMENTAL (RFC-006 / D-028) — the module-frontend registry generator.
 *
 * Builds a DETERMINISTIC map from the enabled Modules' typed `frontend`
 * contributions (P0's Manifest section) to import specifiers, and renders it as a
 * generated JS module. The rendered artifact is an internal build artifact:
 * generated, git-ignored, never hand-authored, never committed (P1).
 *
 * Core stays Module-AGNOSTIC: it iterates opaque enabled Modules and resolves each
 * `entry` token through that Module's OWN coordinate (its declared source root;
 * in-repo default `@/Modules/{key}`) — it names no specific Module. The specifier
 * is registry OUTPUT, never a Manifest interpretation; the concrete source/key
 * appears ONLY in the generated, git-ignored artifact, never in Core source. A
 * future external package supplies its own coordinate through module-owned
 * metadata without changing this generator or resolver.
 *
 * Named failure categories raised here — all LOUD at generation/build/boot:
 *   duplicate contribution — a widget key or page name repeated across Modules,
 *       or a page name in the reserved Core namespace;
 *   missing frontend entry — an enabled backend widget has no frontend entry;
 *   orphan frontend widget — a frontend widget entry has no backend widget;
 *   unknown target area — a backend widget declares an empty/invalid area;
 *   unresolved entry — an entry does not resolve through its Module coordinate.
 * The ONLY fail-soft case is a registered contribution that later cannot load or
 * throws while rendering — handled at render (WidgetRenderer), never here.
 */
final class FrontendRegistry
{
    /**
     * @param  iterable<array{key: string, source: string, widgets: list<array{key: string, area: mixed}>, frontend: array{widgets?: list<array{key: string, entry: string}>, pages?: list<array{name: string, entry: string}>}}>  $modules
     * @param  null|callable(string): bool  $resolver  maps a specifier → does it resolve? (unresolved-entry check)
     * @return array{widgets: array<string, string>, pages: array<string, string>}
     */
    public static function build(iterable $modules, ?callable $resolver = null): array
    {
        $widgetMap = [];
        $pageMap = [];
        $widgetOwner = [];
        $pageOwner = [];

        foreach ($modules as $module) {
            $key = $module['key'];
            $source = $module['source'];

            $backendKeys = array_map(fn (array $w) => $w['key'], $module['widgets'] ?? []);
            $frontendWidgets = $module['frontend']['widgets'] ?? [];
            $frontendPages = $module['frontend']['pages'] ?? [];
            $frontendKeys = array_map(fn (array $w) => $w['key'], $frontendWidgets);

            // unknown target area — a backend widget's area must be a non-empty string.
            foreach ($module['widgets'] ?? [] as $backendWidget) {
                $area = $backendWidget['area'] ?? null;
                if (! is_string($area) || trim($area) === '') {
                    throw new InvalidArgumentException("Frontend registry: unknown target area — backend widget [{$backendWidget['key']}] in module [{$key}] declares an empty or invalid area.");
                }
            }

            // Widget join — a bijection between this Module's enabled backend widget
            // declarations and its frontend.widgets contributions, by global key.
            foreach ($backendKeys as $backendKey) {
                if (! in_array($backendKey, $frontendKeys, true)) {
                    throw new InvalidArgumentException("Frontend registry: missing frontend entry — backend widget [{$backendKey}] in module [{$key}] has no frontend contribution.");
                }
            }
            foreach ($frontendKeys as $frontendKey) {
                if (! in_array($frontendKey, $backendKeys, true)) {
                    throw new InvalidArgumentException("Frontend registry: orphan frontend widget — frontend widget [{$frontendKey}] in module [{$key}] has no backend widget declaration.");
                }
            }

            foreach ($frontendWidgets as $widget) {
                if (isset($widgetOwner[$widget['key']])) {
                    throw new InvalidArgumentException("Frontend registry: duplicate contribution — widget key [{$widget['key']}] declared by modules [{$widgetOwner[$widget['key']]}] and [{$key}].");
                }
                $widgetOwner[$widget['key']] = $key;
                $widgetMap[$widget['key']] = self::specifier($source, $widget['entry']);
            }

            foreach ($frontendPages as $page) {
                if (str_starts_with($page['name'], 'Core/')) {
                    throw new InvalidArgumentException("Frontend registry: duplicate contribution — page name [{$page['name']}] in module [{$key}] collides with the reserved Core namespace.");
                }
                if (isset($pageOwner[$page['name']])) {
                    throw new InvalidArgumentException("Frontend registry: duplicate contribution — page name [{$page['name']}] declared by modules [{$pageOwner[$page['name']]}] and [{$key}].");
                }
                $pageOwner[$page['name']] = $key;
                $pageMap[$page['name']] = self::specifier($source, $page['entry']);
            }
        }

        // Deterministic ordering: sort by id so identical inputs → identical output.
        ksort($widgetMap);
        ksort($pageMap);
        $map = ['widgets' => $widgetMap, 'pages' => $pageMap];

        // unresolved entry — every specifier must resolve through its Module
        // coordinate. Render never infers a filesystem location from a token.
        if ($resolver !== null) {
            foreach ($map as $section) {
                foreach ($section as $id => $specifier) {
                    if (! $resolver($specifier)) {
                        throw new InvalidArgumentException("Frontend registry: unresolved entry — [{$specifier}] for [{$id}] does not resolve through its module coordinate.");
                    }
                }
            }
        }

        return $map;
    }

    /**
     * The import specifier — REGISTRY OUTPUT, built from the Module's own declared
     * coordinate root (source) + its logical entry token. Never a Manifest field.
     */
    private static function specifier(string $source, string $entry): string
    {
        return "{$source}/{$entry}.vue";
    }

    /**
     * Render the deterministic generated artifact — a JS module exporting the
     * two maps, prefixed by an integrity checksum over the body so a hand-edit is
     * detected (verifyIntegrity). No timestamps or environment data, so identical
     * inputs render byte-for-byte identically.
     */
    public static function render(array $map): string
    {
        $body = self::body($map);
        $checksum = hash('sha256', $body);

        return "/* @generated by `php artisan penova:frontend-registry` — do not edit. checksum:{$checksum} */\n".$body;
    }

    private static function body(array $map): string
    {
        $lines = ['// EXPERIMENTAL module-frontend registry (RFC-006 / D-028). Regenerate; never commit.'];

        foreach (['widgets', 'pages'] as $section) {
            $entries = [];
            foreach ($map[$section] as $id => $specifier) {
                $entries[] = '    '.json_encode($id, JSON_UNESCAPED_SLASHES).': () => import('.json_encode($specifier, JSON_UNESCAPED_SLASHES).'),';
            }
            $lines[] = 'export const module'.ucfirst($section).' = {'.($entries === [] ? '' : "\n".implode("\n", $entries)."\n").'};';
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * True when the artifact's embedded checksum matches its body — i.e. it has
     * not been hand-edited. A false result is the "hand-edited registry fails
     * loudly" protection; regeneration replaces it.
     */
    public static function verifyIntegrity(string $artifact): bool
    {
        if (preg_match('#^/\* @generated .* checksum:([a-f0-9]{64}) \*/\n(.*)$#s', $artifact, $matches) !== 1) {
            return false;
        }

        return hash('sha256', $matches[2]) === $matches[1];
    }
}

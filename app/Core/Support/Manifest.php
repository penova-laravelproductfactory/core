<?php

namespace App\Core\Support;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * A Module's Manifest — its single declaration of what it contributes to
 * the Platform (D-005; ../../../strategy/06-glossary.md). One Module, one
 * Manifest. This is the governed public contract a Module author writes
 * against; its named sections and their shapes are the contract, not this
 * carrier class (RFC-001 / D-023 §3.2).
 *
 * Sections:
 *   identity    — key, name, description, version
 *   menu        — sidebar item descriptors
 *   widgets     — widget descriptors
 *   permissions — the permission slugs the Module declares
 *   frontend    — typed page/widget frontend entries (EXPERIMENTAL — RFC-006 /
 *                 D-028; the frontend-seam contract, may change or be withdrawn
 *                 without a MAJOR, not yet SemVer-frozen)
 *
 * Future contribution categories (policies, settings, logs) are added here
 * as further named sections when Governance accepts them — never as new
 * top-level provider hooks (D-023).
 *
 * The Manifest is declaration-like: it is built once, fluently, and then
 * read. Each fluent method returns a NEW instance, so a built Manifest is
 * effectively immutable — a declaration, not an open mutable object.
 *
 * Item shapes (menu / widget descriptors) are documented in
 * app/Modules/README.md and mirror the shapes Core itself ships; they are
 * validated by their consumers, not narrowed here.
 */
final class Manifest implements Arrayable, JsonSerializable
{
    /**
     * @param  list<array<string, mixed>>  $menu
     * @param  list<array<string, mixed>>  $widgets
     * @param  list<string>  $permissions
     * @param  array{widgets: list<array{key: string, entry: string}>, pages: list<array{name: string, entry: string}>}  $frontend
     */
    private function __construct(
        private readonly string $key,
        private readonly string $name,
        private readonly string $description,
        private readonly string $version,
        private readonly array $menu = [],
        private readonly array $widgets = [],
        private readonly array $permissions = [],
        private readonly array $frontend = ['widgets' => [], 'pages' => []],
    ) {
    }

    /**
     * Begin a Manifest declaration with the Module's identity. Every field
     * is required — a Module must be able to say what it is.
     */
    public static function for(string $key, string $name, string $description, string $version): self
    {
        foreach (['key' => $key, 'name' => $name, 'description' => $description, 'version' => $version] as $field => $value) {
            if (trim($value) === '') {
                throw new InvalidArgumentException("Manifest identity field [{$field}] must not be empty.");
            }
        }

        return new self($key, $name, $description, $version);
    }

    /** Declare the sidebar items this Module contributes. */
    public function menu(array $items): self
    {
        return new self($this->key, $this->name, $this->description, $this->version, array_values($items), $this->widgets, $this->permissions, $this->frontend);
    }

    /** Declare the widget descriptors this Module contributes. */
    public function widgets(array $descriptors): self
    {
        return new self($this->key, $this->name, $this->description, $this->version, $this->menu, array_values($descriptors), $this->permissions, $this->frontend);
    }

    /** Declare the permission slugs this Module introduces. */
    public function permissions(array $slugs): self
    {
        return new self($this->key, $this->name, $this->description, $this->version, $this->menu, $this->widgets, array_values($slugs), $this->frontend);
    }

    /**
     * Declare the Module's typed frontend entries (EXPERIMENTAL — RFC-006 /
     * D-028). Each entry maps a contribution to a NAMED entry token (never a
     * path or glob); Core resolves the token generically, naming no Module:
     *
     *   ->frontend([
     *       'widgets' => [
     *           ['key' => 'store-active-products', 'entry' => 'widgets/ActiveProductsCard'],
     *       ],
     *       'pages' => [
     *           ['name' => 'Store/Products/Index', 'entry' => 'pages/Products/Index'],
     *       ],
     *   ])
     *
     * A frontend widget entry owns frontend RESOLUTION only — {key, entry}. The
     * widget's area, title, ordering and authorization stay the backend widget
     * descriptor's; the two join by the globally-unique widget `key`, so widget
     * truth has ONE authority (RFC-006 / D-028 review). The `entry` token's
     * coordinate ROOT — where the Module's frontend physically lives — is NOT a
     * Manifest field: it is module-owned build metadata the provider declares via
     * {@see DeclaresFrontendSource}, so this public contract never freezes an
     * install/path coordinate. Validated here for
     * structure (malformed descriptor, duplicate contribution, malformed entry
     * token). Registration-time categories — unresolved entry, incompatible
     * peer/Core version, cross-Module uniqueness, and the backend↔frontend widget
     * join (every renderable backend widget has exactly one frontend entry; no
     * orphan frontend entry) — are the registry generator's, which alone holds the
     * Module's export map and the cross-Module view.
     */
    public function frontend(array $sections): self
    {
        $validated = self::validateFrontend($sections);

        return new self($this->key, $this->name, $this->description, $this->version, $this->menu, $this->widgets, $this->permissions, $validated);
    }

    public function key(): string
    {
        return $this->key;
    }

    /** @return array{key: string, name: string, description: string, version: string} */
    public function identity(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function menuItems(): array
    {
        return $this->menu;
    }

    /** @return list<array<string, mixed>> */
    public function widgetDescriptors(): array
    {
        return $this->widgets;
    }

    /** @return list<string> */
    public function permissionSlugs(): array
    {
        return $this->permissions;
    }

    /**
     * The Module's typed frontend entries (EXPERIMENTAL — RFC-006 / D-028).
     *
     * @return array{widgets: list<array{key: string, entry: string}>, pages: list<array{name: string, entry: string}>}
     */
    public function frontendContributions(): array
    {
        return $this->frontend;
    }

    /** @return array{identity: array, menu: list, widgets: list, permissions: list, frontend: array} */
    public function toArray(): array
    {
        return [
            'identity' => $this->identity(),
            'menu' => $this->menu,
            'widgets' => $this->widgets,
            'permissions' => $this->permissions,
            'frontend' => $this->frontend,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Structural validation of the frontend section (RFC-006 / D-028, § 6a.3).
     * Named failure categories raised HERE: MALFORMED DESCRIPTOR (shape / type /
     * entry-token) and DUPLICATE CONTRIBUTION (a key/name repeated within this
     * Manifest). The registration-time categories — UNRESOLVED ENTRY,
     * INCOMPATIBLE PEER/CORE VERSION, global uniqueness across Modules, and the
     * backend↔frontend widget JOIN (exactly one frontend entry per renderable
     * backend widget; no orphan) — belong to the registry generator (P1), which
     * alone holds the cross-Module view and each Module's export map. A frontend
     * widget entry is {key, entry}; area/title/order/authorization stay the
     * backend widget descriptor's.
     *
     * @return array{widgets: list<array{key: string, entry: string}>, pages: list<array{name: string, entry: string}>}
     */
    private static function validateFrontend(array $sections): array
    {
        foreach (array_keys($sections) as $section) {
            if (! in_array($section, ['widgets', 'pages'], true)) {
                throw new InvalidArgumentException("Manifest frontend: malformed descriptor — unknown section [{$section}]; allowed sections are widgets, pages.");
            }
        }

        return [
            'widgets' => self::validateFrontendEntries($sections['widgets'] ?? [], 'widgets', ['key', 'entry'], 'key'),
            'pages' => self::validateFrontendEntries($sections['pages'] ?? [], 'pages', ['name', 'entry'], 'name'),
        ];
    }

    /**
     * @param  list<string>  $fields  the required, non-empty string fields
     * @param  string  $idField  the field that must be unique within the section
     * @return list<array<string, string>>
     */
    private static function validateFrontendEntries(mixed $entries, string $section, array $fields, string $idField): array
    {
        if (! is_array($entries) || ! array_is_list($entries)) {
            throw new InvalidArgumentException("Manifest frontend: malformed descriptor — [{$section}] must be a list of entries.");
        }

        $seen = [];
        $out = [];

        foreach ($entries as $i => $entry) {
            if (! is_array($entry)) {
                throw new InvalidArgumentException("Manifest frontend: malformed descriptor — [{$section}][{$i}] must be an array.");
            }

            $clean = [];
            foreach ($fields as $field) {
                $value = $entry[$field] ?? null;
                if (! is_string($value) || trim($value) === '') {
                    throw new InvalidArgumentException("Manifest frontend: malformed descriptor — [{$section}][{$i}] field [{$field}] must be a non-empty string.");
                }
                $clean[$field] = $value;
            }

            if (! self::isValidEntryToken($clean['entry'])) {
                throw new InvalidArgumentException("Manifest frontend: malformed descriptor — invalid entry token [{$clean['entry']}] in [{$section}][{$i}] (allowed: letters, digits, '_', '-' and '/'; no leading/trailing slash and no '..').");
            }

            if (isset($seen[$clean[$idField]])) {
                throw new InvalidArgumentException("Manifest frontend: duplicate contribution — [{$section}] {$idField} [{$clean[$idField]}] declared twice.");
            }
            $seen[$clean[$idField]] = true;

            $out[] = $clean;
        }

        return $out;
    }

    /**
     * An entry token is a normalized, module-internal name — never a filesystem
     * path. The charset forbids '..' (there is no '.'), leading/trailing slashes,
     * and empty segments, so a token cannot express path traversal.
     */
    private static function isValidEntryToken(string $token): bool
    {
        return preg_match('#^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$#', $token) === 1;
    }
}

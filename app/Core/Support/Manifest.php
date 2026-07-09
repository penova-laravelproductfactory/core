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
     */
    private function __construct(
        private readonly string $key,
        private readonly string $name,
        private readonly string $description,
        private readonly string $version,
        private readonly array $menu = [],
        private readonly array $widgets = [],
        private readonly array $permissions = [],
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
        return new self($this->key, $this->name, $this->description, $this->version, array_values($items), $this->widgets, $this->permissions);
    }

    /** Declare the widget descriptors this Module contributes. */
    public function widgets(array $descriptors): self
    {
        return new self($this->key, $this->name, $this->description, $this->version, $this->menu, array_values($descriptors), $this->permissions);
    }

    /** Declare the permission slugs this Module introduces. */
    public function permissions(array $slugs): self
    {
        return new self($this->key, $this->name, $this->description, $this->version, $this->menu, $this->widgets, array_values($slugs));
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

    /** @return array{identity: array, menu: list, widgets: list, permissions: list} */
    public function toArray(): array
    {
        return [
            'identity' => $this->identity(),
            'menu' => $this->menu,
            'widgets' => $this->widgets,
            'permissions' => $this->permissions,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

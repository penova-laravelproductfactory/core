# White Label / Branding — Design

**Date:** 2026-07-06
**Status:** Approved (design)
**Scope:** Penova Core Lite — v0.1 branding surface

## Goal

Let a panel owner set the brand **name**, **logo**, **primary color**, and
**footer text** from Core Settings, and have those values drive the
`AdminLayout` shell and the public `Welcome` page. No new table — reuse the
existing key-value `SettingsManager`. If nothing is configured, the panel keeps
working on Penova defaults.

## Reconciliation with the existing code

The original request was written against `PENOVA_CORE_STRUCTURE.md`; the real
code differs in ways this design accounts for:

- **`SettingsManager` does not read config defaults.** `get($key, $default)`
  only returns a default the caller passes. The "fall back to config" behavior
  is implemented explicitly at the read sites, not inside the manager.
- **The update controller is generic.** `UpdateSettingsController` validates
  `settings` is an array and loops `set($key, $value)`. There is no
  `SettingController@update` with per-field rules. We keep the generic loop and
  add dot-notation validation for `settings.branding.*`.
- **`Setting.value` is JSON-cast.** So the whole branding group is stored under
  a single key `branding` holding an object — not four dotted keys. This also
  matches the requested Vue shape (`settings.branding?.name`).
- **Components:** `TextInput` (with a built-in `:error` prop), `Button`,
  `Card`, `PageHeader` exist. `InputError` and `TextArea` do **not**. We use
  `TextInput`'s `:error` prop for errors and add a small `TextArea.vue`
  mirroring `TextInput` for `footer_text`.
- **`AdminLayout`** hard-codes "Penova Core" and has no logo or footer.
  **`Welcome`** hard-codes its title and uses the bundled `/penova-logo.png`.

## Decisions

- **primary_color is store-only in v0.1** — persisted and exposed via shared
  props and editable in the form, but it does **not** re-theme the app. The
  Tailwind 4 `@theme` `bg-brand` tokens stay static. Applying the color
  dynamically (wiring a CSS variable to `--color-brand`) is deferred.
- **Logo is a URL** (validated `url`), not a file upload.
- **Storage:** one JSON setting, key `branding`, group `branding`, value
  `{ name, logo_url, primary_color, footer_text }`.
- **Defaults live in `config/penova.php`** and are merged in at read time.
- **The Settings form binds to raw DB values** (empty → placeholder). Saving an
  empty form does **not** write config defaults as DB overrides; defaults only
  appear at the display layer (shared props / page fallbacks).

## Data model

No migration. `Setting`:

| key        | group      | value (JSON)                                              |
|------------|------------|----------------------------------------------------------|
| `branding` | `branding` | `{ name, logo_url, primary_color, footer_text }` |

## Config

Add to `config/penova.php`:

```php
'branding' => [
    'name'          => env('PENOVA_BRAND_NAME', 'Penova Core Lite'),
    'logo_url'      => env('PENOVA_BRAND_LOGO', null),
    'primary_color' => env('PENOVA_BRAND_PRIMARY_COLOR', '#01696f'),
    'footer_text'   => env('PENOVA_BRAND_FOOTER', 'Powered by Penova'),
],
```

## Backend

### `ShowSettingsController` — unchanged
Still renders `Core/Settings/Index` with `settings => $settings->all()`. When
`branding` is set, the collection includes it as an object; when unset, the Vue
form falls back to empty fields via `?? {}`.

### `UpdateSettingsController` — add branding validation
Keep the generic loop; add rules:

```php
$request->validate([
    'settings'                        => ['required', 'array'],
    'settings.branding.name'          => ['nullable', 'string', 'max:100'],
    'settings.branding.logo_url'      => ['nullable', 'url', 'max:255'],
    'settings.branding.primary_color' => ['nullable', 'string', 'max:20'],
    'settings.branding.footer_text'   => ['nullable', 'string', 'max:255'],
]);

foreach ($request->input('settings') as $key => $value) {
    $settings->set($key, $value);
}
```

Note: the loop iterates `$request->input('settings')`, **not**
`$validated['settings']` — adding nested `settings.branding.*` rules makes
Laravel's `validated()` return only the ruled keys, which would silently drop
generic settings (`site_name`, `contact_email`). We validate for the rules but
persist the full submitted `settings` map, preserving the existing generic
behavior. `set('branding', {object})` persists the JSON object; `set()` already
flushes the settings cache.

### `HandleInertiaRequests` — new shared `branding` prop
Resolve `SettingsManager`, merge config defaults under DB values so every page
(including guest `Welcome`) always receives complete values:

```php
$branding = array_merge(
    config('penova.branding'),
    app(SettingsManager::class)->get('branding', []),
);
// share: 'branding' => $branding
```

DB values win over config defaults; missing DB keys fall back to config.

## Frontend

### New component: `Core/Components/TextArea.vue`
Mirror `TextInput`: `label`, `error`, `rows` props, `defineModel`, same input
styling, inline `:error` paragraph.

### `Core/Pages/Settings/Index.vue`
- Extend `useForm` with a `branding` object sourced from
  `props.settings.branding ?? {}` (each field `?? ''`).
- Add a second `<Card title="White Label / Branding">` with a short Persian
  description and a 2-column grid:
  - Brand name → `TextInput` (`:error="form.errors['branding.name']"`)
  - Logo URL → `TextInput` (`form.errors['branding.logo_url']`)
  - Primary color (hex) → `TextInput` (`form.errors['branding.primary_color']`)
  - Footer text → `TextArea`, full width (`form.errors['branding.footer_text']`)
- The existing submit button posts the same `form.put('/admin/settings')`; the
  payload just carries `branding` too.

### `Core/Layouts/AdminLayout.vue`
- `const branding = computed(() => page.props.branding ?? {})`.
- Sidebar brand block: show `<img :src="branding.logo_url">` when present;
  brand text = `branding.name || 'Penova Core'`. Keep the small subtitle.
- Add a slim footer at the bottom of the main column showing
  `branding.footer_text` when present.

### `Core/Pages/Welcome.vue`
- Read `usePage().props.branding`.
- Logo `src = branding.logo_url || '/penova-logo.png'`.
- Hero `<h1>` = `branding.name || 'Penova Core Lite'`.
- Footer line uses `branding.footer_text` (fallback to current text).
- Leave the rest of the Penova marketing copy untouched.

## Default behavior

- **No DB settings:** shared props = config defaults → panel shows
  "Penova Core Lite", Welcome shows the bundled logo. Nothing breaks.
- **After save:** `set()` flushes the cache → the next request's shared props
  reflect new values → `AdminLayout` and `Welcome` update immediately.

## Testing (Pest, feature)

New `tests/Feature/Core/Settings/BrandingTest.php` (match existing Feature test
conventions; create a user with `settings.manage`):

1. Owner can open Settings (`GET /admin/settings` → 200, Inertia component).
2. Submitting valid branding persists it: `PUT /admin/settings` with a
   `settings.branding` payload → DB `branding` row holds the object.
3. Invalid `logo_url` (non-URL) fails validation → `branding.logo_url` error,
   nothing persisted.
4. Shared props expose merged branding: with no DB row, `branding.name` equals
   the config default; after saving, it equals the saved value.

## Out of scope (v0.1)

- Dynamic re-theming from `primary_color`.
- Logo file upload / storage.
- Per-locale branding (single set of values for now).

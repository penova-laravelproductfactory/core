# app/Modules — Product Modules

Business-specific products (Store, CMS, …) live here, one folder
per module. **Core never imports from this namespace** — modules build on
Core, not the other way around. If code is reusable across two modules,
it belongs in `app/Core`.

A working reference module ships in this repo: `Store`
(real CRUD + widgets + permissions).

Start a new module with the scaffolder — it creates the whole anatomy
below (provider implementing the contract, routes.php, backend and
frontend folders) from `stubs/penova/module/`:

```
php artisan penova:module Reports
```

## Anatomy of a module

```
app/Modules/Store/
  StoreServiceProvider.php     ← the module's single entry point
  routes.php                   ← module routes (invokable controllers)
  Controllers/                 ← ONE invokable class per route action
  Models/
  Requests/
  Policies/
  Database/
    Migrations/                ← loadMigrationsFrom() in the provider
    Seeders/                   ← StorePermissionsSeeder (see Permissions)
```

Frontend lives in `resources/js/Modules/Store/`:

```
resources/js/Modules/Store/
  Pages/       ← Inertia pages: Inertia::render('Modules/Store/Products/Index')
  Widgets/     ← widget components, one per declared widget
  Components/  ← module-private components (e.g. a shared form)
```

> **⚠ EXPERIMENTAL — the module-frontend seam (RFC-006 / D-028).** The frontend
> half of the Core↔module seam is now a **declared contract, but an experimental
> one**: it may change or be withdrawn **without a MAJOR bump**, and is not yet
> SemVer-frozen. It graduates to a stable, SemVer-governed contract only once a
> **second independent Module** exercises it (the graduation trigger); until then,
> treat it as provisional. Everything below is under that stance.

A module no longer relies on an automatic path glob. Instead it **declares** its
frontend contributions, and a **generated, git-ignored registry** resolves them:

1. **Declare the entries** in the Manifest's experimental `frontend` section —
   typed `entry` tokens (never paths or globs), joined to widgets by `key` and to
   pages by their Inertia `name`:

   ```php
   ->frontend([
       'widgets' => [
           ['key' => 'store-active-products', 'entry' => 'Widgets/ActiveProductsCard'],
       ],
       'pages' => [
           ['name' => 'Modules/Store/Products/Index', 'entry' => 'Pages/Products/Index'],
       ],
   ])
   ```

   Every enabled backend widget needs exactly one frontend entry with the same
   `key` (no missing entry, no orphan); page `name`s must be globally unique and
   must not start with `Core/`. The `entry` token is module-internal — no `..`, no
   leading/trailing slash.

2. **Own the coordinate.** Where the frontend physically lives is *module* build
   metadata, not a Manifest field. `@/Modules/{key}` is only the **optional default
   convention** — not a guaranteed Core coordinate (D-AUDIT-008); the governed
   surfaces are the Manifest `frontend` descriptor and
   `App\Core\Support\DeclaresFrontendSource`. An in-repo module gets that default; a
   module whose directory differs from its key (or that ships its frontend
   elsewhere) declares its own root:

   ```php
   public static function frontendSource(): string
   {
       return '@/Modules/Store';
   }
   ```

3. **Regenerate the registry** — one command, run automatically by every frontend
   build (`npm run build` / `npm run dev` invoke it first):

   ```
   php artisan penova:frontend-registry
   ```

   The output (`resources/js/generated/…`) is a build artifact: git-ignored,
   never hand-edited, regenerated on any add / enable / disable / update / remove
   of a module. `--check` fails a build whose registry is missing, tampered, or
   stale. Malformed descriptors, a broken widget join, duplicate contributions,
   an unknown widget area, or an unresolvable entry all fail **loudly** at
   generation — never silently at runtime.

**Externally-packaged modules (forward-looking).** A module whose frontend ships
as its own package may also implement `App\Core\Support\DeclaresFrontendPackage`
to declare the backend↔frontend package pairing and its framework peers; Core
then fails loudly, before runtime, on a *module frontend package mismatch* or an
incompatible *peer*. In-repo modules ship their frontend in the same tree and
declare none.

> **Still internal, even so.** The shared Inertia prop shapes (`menu`, `widgets`,
> `widgetAreas`) and the Core layout/components a module imports (e.g.
> `WorkspaceLayout`) remain **Core internals that may change between releases**.
> The governed public contract is the **Manifest** — its section shapes (D-023),
> now including the experimental `frontend` descriptor — *not* the props or Core
> component names derived from them.

## Wiring a module in

Add its provider to `config/penova.php` — the **only** place a module
touches shared configuration:

```php
'modules' => [
    App\Modules\Store\StoreServiceProvider::class,
],
```

`PenovaCoreServiceProvider` registers it — Core stays free of any
compile-time reference to the module.

## The module contract (`App\Core\Support\PenovaModule`)

Every module's service provider extends Laravel's `ServiceProvider`
**and implements `PenovaModule`**. A module declares everything it
contributes — identity, menu, widgets, permissions — through **one
Manifest**, its single coherent declaration (D-005; *Manifest* in the
Glossary). Core reads the Manifest only from providers implementing the
interface — a provider without it still boots, but contributes nothing to
the panel. Sections a module does not use are simply omitted.

Routes and migrations are **not** Manifest contributions — they are
ordinary provider mechanics, wired in `boot()` (see *Routes* below).

```php
use App\Core\Support\Manifest;
use App\Core\Support\PenovaModule;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider implements PenovaModule
{
    public function boot(): void { /* load routes, migrations */ }

    public static function manifest(): Manifest
    {
        return Manifest::for(
            key: 'store',
            name: 'Store',
            description: 'Products, orders and checkout.',
            version: '0.1.0',
        )
            ->menu([ /* sidebar items — see below */ ])
            ->widgets([ /* widgets — see below */ ])
            ->permissions([ /* permission slugs — see below */ ]);
    }
}
```

The Manifest is built once, fluently, then read — a declaration, not a
mutable object. Each section's item shape is documented below.

### The `menu` section — sidebar items

An array of items; Core merges them with its own (orders 10–60) and sorts
by `order`. Use `order >= 100` for module items.

```php
->menu([[
    'key'   => 'store',          // unique across the panel
    'label' => 'فروشگاه',
    'route' => 'store.products.index', // route NAME — Core resolves the URL
    'icon'  => 'bag',            // icon key; the map lives in WorkspaceLayout.vue
                                 // (home|users|shield|cog|clock|bell|calendar|bag|clipboard|sparkles|squares)
    'order' => 100,
    'permission' => 'store.view', // optional; hides the item from users
                                    // without the permission — keep it in
                                    // sync with the route's middleware
]])
```

### The `widgets` section — widgets

> **v1: the widget grid is dormant (D-AUDIT-007).** The `widgets` descriptor is a
> valid Manifest section you may declare, but Core v1 does **not** render a
> user-visible widget grid or dashboard — the widget pipeline is internal and
> dormant. Declaring widgets is safe and forward-looking; do not rely on a live
> grid until Penova declares a Workspace-widget contract through a dedicated RFC.

Widget **descriptors** carry a widget's placement; when a widget grid is enabled it
positions them sorted by `order`. Core's own widgets use orders 10–30 (and 900 for
the Modules card), so modules land in the middle with `order >= 100`.

```php
->widgets([[
    'key'       => 'store-active-products', // joins to the frontend entry (below)
    'type'      => 'card',            // 'card' | 'list'
    'title'     => 'محصولات فعال',    // arrives as widget.title in Vue
    'cols'      => 1,                 // 1 | 2 | 'full' (whole row, any grid width)
    'order'     => 100,
    'area'      => 'store',           // widget area (see below)
    'permission' => 'store.view',     // optional; widget is dropped for users
                                      // without it (match the data endpoint's
                                      // middleware so it never 403s)
]])
```

The widget descriptor carries the widget's **placement** (area, title, order,
permission) — its single authority. The Vue component it renders is declared
separately, in the experimental `frontend` section, and joined by the shared
`key` (see *Anatomy of a module* above). There is no `component` path field.

**Areas.** A widget grid, when enabled, groups one headed section per `area`, so a
module's widgets stay visually grouped. Recommended: give your module its
own area named after it (`'area' => 'store'`) and reuse it on every
widget the module ships. Omitting `area` drops the widget into the
default `core` group. Section headings come from
`config('penova.widgets.areas')` — add your key there for a proper label;
unknown keys fall back to a label formatted from the key itself
(`store-extras` → "Store Extras").

**Widget data.** Descriptors are layout-only. A widget component receives
its descriptor as the `widget` prop and owns its data: read the shared /
page Inertia props, or fetch a small module JSON endpoint on mount (see
`ActiveProductsCard.vue` + `ActiveProductsCountController`).

### The `permissions` section — declared permission slugs

The flat list of permission slugs the module introduces:

```php
->permissions(['store.view', 'store.manage'])
```

This section is the module's **single declaration** of the permissions it
introduces (D-023): Core collects it into the `penova.permissions` binding,
and the module's seeder *reads* it to create the permissions — so the slug
set lives in one place, not several. Keep the route middleware's
`permission:` guards in sync with what you declare here.

## Permissions

Guard module routes with the permission middleware, split by intent —
`*.view` for read-only pages (index, widget data endpoints), `*.manage`
for create/edit actions:

```php
Route::middleware('permission:store.view')->group(...);
Route::middleware('permission:store.manage')->group(...);
```

Seed the permissions from the module's own seeder
(`app/Modules/<Name>/Database/Seeders/<Name>PermissionsSeeder.php`,
`firstOrCreate` + `syncWithoutDetaching` onto the Operator role) and
register it in `database/seeders/DatabaseSeeder.php` after
`PenovaCoreSeeder`. Put the same slug in the `permission` field of the
module's menu items and widget descriptors so the sidebar and widgets
never show what the routes would 403.

## Routes: invokable controllers only

Every route action — Core and Modules alike — is **one invokable
controller class**. Naming convention: `{Verb}{Subject}Controller`, verbs
from this set:

| Verb    | Action                       | Example                    |
|---------|------------------------------|----------------------------|
| List    | index page                   | `ListProductsController`   |
| Show    | single page / form display   | `ShowProductController`    |
| Create  | "new X" form page            | `CreateProductController`  |
| Store   | persist a new record         | `StoreProductController`   |
| Edit    | "edit X" form page           | `EditProductController`    |
| Update  | apply edits                  | `UpdateProductController`  |
| Delete  | destroy                      | `DeleteProductController`  |

(`{Subject}{Verb}Controller` — `ProductIndexController`,
`OrderShowController` — is an accepted equivalent; pick one style per
module and stay consistent.) Widget data endpoints follow the same rule:
`ActiveProductsCountController`.

Module routes reuse the panel middleware + URI prefix from config, but own
their route-name prefix (never `penova.*`). Keep `routes.php` plain and
apply the group in the provider:

```php
public function boot(): void
{
    if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
        Route::middleware(config('penova.workspace.middleware'))
            ->prefix(config('penova.workspace.prefix'))
            ->group(__DIR__.'/routes.php');
    }
}
```

Register static paths (e.g. `/store/products/active-count`) **before**
parameterised ones (`/store/products/{product}`) so they are never
captured by route-model binding.

## Future sections (not implemented yet)

The Manifest grows by adding a **section**, never another top-level hook
(D-023). Planned:

- `policies` — modules announcing their model → policy map so Core
  registers Gates for them.
- `settings` — modules registering their runtime settings (key, default,
  label) into the Core Settings page.
- `logs` — declaring module activity-log actions for nicer rendering in
  the Core audit trail.

Do not pre-declare these; they will be added to the Manifest as optional
sections when Core supports them.

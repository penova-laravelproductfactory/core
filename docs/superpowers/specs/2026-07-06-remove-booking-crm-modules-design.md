# Remove Booking & Crm Modules — Design

**Date:** 2026-07-06
**Status:** Approved (design)
**Scope:** Penova Core Lite — strip the two demo business modules

## Goal

Remove every trace of the `Booking` and `Crm` demo modules so Core Lite ships
only the shared Core plus the one remaining reference module, `Store`. After
this, the panel shows Core sections only (Dashboard, Users, Roles, Settings,
Logs, Notifications) plus Store; no Booking/Crm menu items, widgets, routes,
migrations, seeders, or tests remain.

## Decisions

- **Store stays.** Only `Booking` and `Crm` are removed. `Store` is the
  surviving reference implementation of the module contract.
- **Preserve first, then delete.** Tag the current commit
  `pre-module-removal` before deleting anything (git history keeps the code;
  the tag is a convenient bookmark).
- **Re-point the contract test, don't delete it.** `AdminFlowTest`'s second
  test ("module menu items and widgets are permission-filtered") is entirely
  Booking-based. Rewrite it against `Store` (which follows the identical
  `PenovaModule` contract) so permission-filtered composition stays covered.
- **Migrations are deleted outright** (no down migration). Core Lite is
  early-stage; tests use `RefreshDatabase` and migrate fresh from the
  remaining migrations.
- **Illustrative doc-comment examples stay.** References to Booking/Lead in
  code docblocks (`User.php`, `ListNotificationsController.php`,
  `PenovaModule.php`) and ProPitch's marketing copy are non-functional
  examples; they don't break and are left as-is. Prose docs that describe
  Booking/Crm as *shipped* modules get a light factual update.

## Why this is mostly deletion, not surgery

The architecture is data-driven, so removing the modules is largely removing
folders:

- **Inertia resolver** (`resources/js/app.js`) resolves pages via
  `import.meta.glob('./Modules/*/Pages/**/*.vue')` — deleting the module
  folders makes the glob stop matching them. No edit.
- **Sidebar** (`AdminLayout.vue`) renders from the shared `menu` prop, built
  from each module's `menu()` hook. **Dashboard** (`Dashboard/Index.vue`)
  renders from the shared `dashboardWidgets` prop, built from each module's
  `widgets()` hook. Remove the providers → their menu items and widgets
  disappear. No edit.
- **Provider registration:** `bootstrap/providers.php` lists only
  `AppServiceProvider` + `PenovaCoreServiceProvider`. Modules are wired
  **only** through `config('penova.modules')`, which `PenovaCoreServiceProvider`
  boots. Removing the two config entries fully unregisters them.

## Removals (delete entirely)

Backend:
- `app/Modules/Booking/` (provider, controllers, models, requests, routes.php, seeders)
- `app/Modules/Crm/` (same shape)

Frontend:
- `resources/js/Modules/Booking/` (Pages, Components, Widgets)
- `resources/js/Modules/Crm/` (Pages, Widgets)

Migrations:
- `database/migrations/2026_07_05_000000_create_bookings_table.php`
- `database/migrations/2026_07_05_000001_create_leads_table.php`

## Surgical edits (shared wiring)

### `config/penova.php`
- `modules` array: remove `App\Modules\Booking\BookingServiceProvider::class`
  and `App\Modules\Crm\CrmServiceProvider::class` (and their inline comments).
  Keep `App\Modules\Store\StoreServiceProvider::class`.
- `widgets.areas`: remove the `'booking' => 'رزروها'` and `'crm' => 'CRM'`
  keys. Keep `'core'` and `'store'`.
- **In-file comment examples:** the `modules` docblock example
  (`'modules' => [ App\Modules\Booking..., App\Modules\Crm... ]`) and the
  `widgets` area comment (`"booking-extras" → "Booking Extras"`) reference
  Booking/Crm illustratively. Update both to use Store (e.g.
  `App\Modules\Store\StoreServiceProvider::class` in the example, and a
  `"store-extras" → "Store Extras"` area example) so no Booking/Crm string
  survives anywhere in this file.

### `database/seeders/DatabaseSeeder.php`
- Remove the `BookingPermissionsSeeder::class` and `CrmPermissionsSeeder::class`
  lines from the `$this->call([...])`. Keep `PenovaCoreSeeder` and
  `StorePermissionsSeeder`.

### `tests/Feature/Core/AdminFlowTest.php`
- Rewrite the second test to exercise **Store** instead of Booking:
  - Seed `PenovaCoreSeeder` only → admin lacks `store.view`.
  - Assert `menu` has no item with `key === 'store'`, `dashboardWidgets` has
    no `key === 'store-active-products'`, and `GET /admin/store/products`
    returns 403.
  - Seed `\App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class`,
    then `$this->app['auth']->forgetGuards();`.
  - Assert the `store` menu item and `store-active-products` widget now
    appear and `GET /admin/store/products` returns 200.
- Leave the first test ("full admin experience") unchanged — it asserts only
  Core menu/widget index 0, unaffected by module removal.

### Prose docs (light factual update)
- `docs/architecture.md` and `app/Modules/README.md`: where they list
  Booking/Crm as example/shipped modules, update to reflect that Core Lite
  ships Core + Store only (use Store as the module example). Keep it minimal —
  no rewrite.

## Verification

1. `composer dump-autoload` — drop the removed PSR-4 classes from the autoload map.
2. `php artisan config:clear && php artisan route:clear && php artisan view:clear`.
3. `php artisan test` — all green. Expected suite: `AuthTest`, `AdminFlowTest`
   (2nd test now Store-based), `BrandingTest`, Store tests (`OrderFlowTest`,
   `AccountOrderHistoryTest`), `ExampleTest`.
4. `npm run build` — compiles clean (module folders gone; glob adjusts).
5. Final grep `Booking|Bookings|Crm|CRM|Lead` across the repo — only the
   illustrative doc-comment/marketing examples remain (no `app/Modules/Booking`,
   `app/Modules/Crm`, `resources/js/Modules/{Booking,Crm}`, config entries,
   seeder calls, or migrations).

## Post-condition

- Admin panel: Dashboard, Users, Roles, Settings, Logs, Notifications, plus
  Store (Products + Orders). No Booking/Crm anywhere.
- Config `modules` array holds only Store; `widgets.areas` holds `core` +
  `store`.
- Tests green; the module-composition contract is still covered (via Store).

## Out of scope

- Removing or altering the `Store` module.
- Removing the module infrastructure (`PenovaModule`, `MakePenovaModuleCommand`,
  the DataTable/widget contracts) — that shared Core stays.
- Rewriting docs beyond the factual Booking/Crm corrections.

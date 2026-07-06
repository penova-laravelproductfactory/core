# Remove Booking & Crm Modules Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove every trace of the `Booking` and `Crm` demo modules, leaving Core + the `Store` module, with the module-composition contract still tested (via Store).

**Architecture:** The panel is data-driven — the Inertia resolver is glob-based, and the sidebar/dashboard render from provider-contributed shared props. So removal is mostly deleting the two module folders plus unwiring them from `config('penova.modules')`, the widget-area map, and `DatabaseSeeder`. The Booking-based composition test is re-pointed to Store rather than deleted.

**Tech Stack:** Laravel 12, Inertia 2, Vue 3, Tailwind 4, Pest.

## Global Constraints

- Only `Booking` and `Crm` are removed. `Store` stays untouched.
- Preserve first: tag `pre-module-removal` on the pre-deletion commit before deleting anything.
- Migrations for bookings/leads are deleted outright (no down migration).
- Illustrative doc-comment/marketing examples of Booking/Lead in Core files (`User.php`, `ListNotificationsController.php`, `PenovaModule.php`, `ProPitch.vue`) are left as-is. `config/penova.php`'s own comment examples ARE updated (to Store) so no Booking/Crm string survives in that file.
- After removal: `php artisan test` green; `npm run build` clean; final `grep -rIn "Booking\|Bookings\|Crm\|CRM\|Lead"` returns only the whitelisted illustrative examples.
- Commits: Conventional Commit subjects, NO AI attribution / Co-Authored-By trailers.

## File Structure

| File | Responsibility | Change |
|------|----------------|--------|
| `tests/Feature/Core/AdminFlowTest.php` | Re-point composition test to Store | Modify |
| `config/penova.php` | Drop Booking/Crm from modules + area map + comment examples | Modify |
| `database/seeders/DatabaseSeeder.php` | Drop Booking/Crm seeder calls | Modify |
| `app/Modules/Booking/**`, `app/Modules/Crm/**` | Booking/Crm backend | Delete |
| `database/migrations/*_create_bookings_table.php`, `*_create_leads_table.php` | Booking/Crm tables | Delete |
| `resources/js/Modules/Booking/**`, `resources/js/Modules/Crm/**` | Booking/Crm frontend | Delete |
| `docs/architecture.md`, `app/Modules/README.md` | Prose that lists Booking/Crm as shipped | Modify |

---

## Task 1: Preserve, re-point the contract test, and remove Booking/Crm backend

**Files:**
- Modify: `tests/Feature/Core/AdminFlowTest.php`
- Modify: `config/penova.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Delete: `app/Modules/Booking/`, `app/Modules/Crm/`, the two migrations

**Interfaces:**
- Consumes (from the surviving Store module): menu key `store`, dashboard widget key `store-active-products`, route `store.products.index` at `/admin/store/products` (guarded by `permission:store.view`), and `App\Modules\Store\Database\Seeders\StorePermissionsSeeder`.
- Produces: a bootable app with `config('penova.modules')` = `[StoreServiceProvider::class]` and a green test suite.

- [ ] **Step 1: Tag the pre-removal commit**

Run:
```bash
git tag pre-module-removal
git tag --list pre-module-removal
```
Expected: prints `pre-module-removal`. (This marks the current commit — which still contains both modules — for easy recovery.)

- [ ] **Step 2: Re-point the composition test to Store (write it while Store still present)**

In `tests/Feature/Core/AdminFlowTest.php`, replace the **entire** second test — the `test('module menu items and widgets are permission-filtered', function () { ... });` block — with:

```php
test('module menu items and widgets are permission-filtered', function () {
    // Core baseline only — the admin has Core permissions but none of
    // the module ones (those come from the module seeders).
    $this->seed(PenovaCoreSeeder::class);

    $this->post('/login', [
        'email' => config('penova.admin.email'),
        'password' => config('penova.admin.password'),
    ]);

    // Without store.view: no sidebar item, no dashboard widget, 403.
    $this->get(route('penova.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu', fn ($menu) => ! collect($menu)->contains('key', 'store'))
            ->where('dashboardWidgets', fn ($widgets) => ! collect($widgets)->contains('key', 'store-active-products')));

    $this->get('/admin/store/products')->assertForbidden();

    // Grant the module permissions the product-composition way.
    $this->seed(\App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class);

    // Feature tests reuse one app instance, so the session guard still
    // holds the pre-seeding user model (with stale cached relations).
    // Real requests are fresh processes — simulate that.
    $this->app['auth']->forgetGuards();

    $this->get(route('penova.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('menu', fn ($menu) => collect($menu)->contains('key', 'store'))
            ->where('dashboardWidgets', fn ($widgets) => collect($widgets)->contains('key', 'store-active-products')));

    $this->get('/admin/store/products')->assertOk();
});
```

Leave the first test ("the full admin experience works end to end") unchanged.

- [ ] **Step 3: Verify the re-pointed test passes (Booking/Crm still present)**

Run: `php artisan test --filter="permission-filtered"`
Expected: PASS. (Confirms the Store-based assertions are correct before we delete anything.)

- [ ] **Step 4: Unwire Booking/Crm from config**

In `config/penova.php`:

(a) Replace the `modules` array block:

```php
    'modules' => [
        // Demo business module: bookings CRUD + "bookings today" widget.
        App\Modules\Booking\BookingServiceProvider::class,

        // Light CRM module: leads + "leads today" widget (architecture
        // stress test — third module through the same contract).
        App\Modules\Crm\CrmServiceProvider::class,

        // Store module: product management (physical/virtual/downloadable)
        // + "active products" widget. Orders/cart come later.
        App\Modules\Store\StoreServiceProvider::class,
    ],
```

with:

```php
    'modules' => [
        // Store module: product management (physical/virtual/downloadable)
        // + "active products" widget. Orders/cart come later.
        App\Modules\Store\StoreServiceProvider::class,
    ],
```

(b) Replace the `widgets.areas` map:

```php
    'widgets' => [
        'areas' => [
            'core' => 'عمومی',
            'booking' => 'رزروها',
            'crm' => 'CRM',
            'store' => 'فروشگاه',
        ],
    ],
```

with:

```php
    'widgets' => [
        'areas' => [
            'core' => 'عمومی',
            'store' => 'فروشگاه',
        ],
    ],
```

(c) Update the two in-file comment examples so no Booking/Crm string remains:

- In the `Dashboard Widgets` docblock, change the fallback example
  `("booking-extras" → "Booking Extras")` to `("store-extras" → "Store Extras")`.
- In the `Product Modules` docblock, change the example list:

```php
    | 'modules' => [
    |     App\Modules\Booking\BookingServiceProvider::class,
    |     App\Modules\Crm\CrmServiceProvider::class,
    | ],
```

to:

```php
    | 'modules' => [
    |     App\Modules\Store\StoreServiceProvider::class,
    | ],
```

- [ ] **Step 5: Unwire Booking/Crm from DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php`, replace the `$this->call([...])`:

```php
        $this->call([
            PenovaCoreSeeder::class,

            // Module permission seeders (product-level composition — the
            // same place modules get wired in as config/penova.php).
            \App\Modules\Booking\Database\Seeders\BookingPermissionsSeeder::class,
            \App\Modules\Crm\Database\Seeders\CrmPermissionsSeeder::class,
            \App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class,
        ]);
```

with:

```php
        $this->call([
            PenovaCoreSeeder::class,

            // Module permission seeders (product-level composition — the
            // same place modules get wired in as config/penova.php).
            \App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class,
        ]);
```

- [ ] **Step 6: Delete the module backends and migrations**

Run:
```bash
git rm -r app/Modules/Booking app/Modules/Crm
git rm database/migrations/2026_07_05_000000_create_bookings_table.php
git rm database/migrations/2026_07_05_000001_create_leads_table.php
```
Expected: git stages the deletions (prints `rm '...'` lines).

- [ ] **Step 7: Refresh autoload + caches, then run the full suite**

Run:
```bash
composer dump-autoload
php artisan config:clear && php artisan route:clear && php artisan view:clear
php artisan test
```
Expected: `php artisan test` all green. The suite should be `AuthTest`, `AdminFlowTest` (both tests, 2nd now Store-based), `BrandingTest`, `OrderFlowTest`, `AccountOrderHistoryTest`, `ExampleTest`. No "Class ...Booking... not found" errors. If any surface, a reference to a deleted class remains — find it (`grep -rIn "App\\\\Modules\\\\Booking\|App\\\\Modules\\\\Crm" app config database tests`) and remove it.

- [ ] **Step 8: Commit**

```bash
git add config/penova.php database/seeders/DatabaseSeeder.php tests/Feature/Core/AdminFlowTest.php
git commit -m "refactor(core): remove Booking & Crm module backends"
```
(The `git rm` deletions from Step 6 are already staged and included in this commit.)

---

## Task 2: Remove Booking/Crm frontend

**Files:**
- Delete: `resources/js/Modules/Booking/`, `resources/js/Modules/Crm/`

**Interfaces:**
- Consumes: nothing new. The Inertia resolver (`resources/js/app.js`) globs `./Modules/*/Pages/**/*.vue`, so removing these folders simply drops them from the glob.

- [ ] **Step 1: Delete the module frontends**

Run:
```bash
git rm -r resources/js/Modules/Booking resources/js/Modules/Crm
```
Expected: git stages the deletions.

- [ ] **Step 2: Build to verify the frontend still compiles**

Run: `npm run build`
Expected: `✓ built` with no errors. No `Booking-*`/`Crm-*`/`LeadsTodayCard`/`BookingsTodayCard` chunks are emitted (nothing references them).

- [ ] **Step 3: Commit**

```bash
git commit -m "refactor(core): remove Booking & Crm module frontends"
```

---

## Task 3: Prose docs + final trace sweep

**Files:**
- Modify: `docs/architecture.md`, `app/Modules/README.md`

**Interfaces:**
- Consumes: nothing. Documentation only.

- [ ] **Step 1: Update the prose docs**

Read `docs/architecture.md` and `app/Modules/README.md`. Wherever they present Booking or Crm as **shipped/example modules** (e.g. "app/Modules/Booking", "the Booking module", "Crm/Leads"), update the wording so Core Lite is described as shipping **Core + Store** only, using Store as the module example. Keep edits minimal and factual — do not rewrite the documents or touch unrelated sections. If a file mentions Booking/Crm only as a generic "a module could…" illustration that is still accurate, leave it.

- [ ] **Step 2: Final trace sweep**

Run:
```bash
grep -rIn "Booking\|Bookings\|Crm\|CRM\|Lead" \
  app config database resources routes tests \
  --include="*.php" --include="*.vue" --include="*.js"
```
Expected: the ONLY remaining hits are the whitelisted illustrative code-comment / marketing examples:
- `app/Models/User.php` (docblock: "a Booking module adding a 'bookings' relation")
- `app/Core/Notifications/Controllers/ListNotificationsController.php` (docblock: `new BookingConfirmed($booking)`)
- `app/Core/Support/PenovaModule.php` (docblock: `Modules/Booking/Widgets/LatestBookings` example)
- `app/Core/Support/Commands/MakePenovaModuleCommand.php` (generator example text, if any)
- `resources/js/Core/Widgets/ProPitch.vue` (Persian marketing copy listing رزرو/CRM as future Pro modules)

There must be **no** hits under `app/Modules/Booking`, `app/Modules/Crm`, `resources/js/Modules/Booking`, `resources/js/Modules/Crm`, `config/penova.php`, `database/seeders/DatabaseSeeder.php`, `database/migrations/*`, or `tests/**`. If any non-whitelisted hit remains, remove it and re-run.

- [ ] **Step 3: Verify suite + build once more**

Run:
```bash
php artisan test
npm run build
```
Expected: tests green, build clean.

- [ ] **Step 4: Commit**

```bash
git add docs/architecture.md app/Modules/README.md
git commit -m "docs(core): drop Booking & Crm from architecture docs"
```

---

## Self-review notes

- **Spec coverage:** tag (T1.S1), delete backends + migrations (T1.S6), config modules + areas + comments (T1.S4), DatabaseSeeder (T1.S5), re-point contract test to Store (T1.S2-3), delete frontends (T2), docs (T3.S1), verification `dump-autoload`/`clear`/`test`/`build`/final-grep (T1.S7, T2.S2, T3.S2-3). All spec sections mapped.
- **Atomicity:** backend deletion, config unwire, and seeder unwire are one task so the app stays bootable and the suite stays green at the task boundary.
- **Naming consistency:** Store contract identifiers used in the test — menu key `store`, widget key `store-active-products`, route `/admin/store/products`, seeder `StorePermissionsSeeder` — match `StoreServiceProvider`/`routes.php`/`StorePermissionsSeeder` verbatim.

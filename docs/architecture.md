# Penova Core — Architecture

Penova Core is a **Laravel product factory core**: the shared foundation
that products (Store, CMS, …) are built on, so the 90%-repeated
parts — auth, users, RBAC, settings, notifications, audit logs, UI
components, data tables — are solved exactly once.

**Stack:** Laravel 12 · Vue 3 + Inertia.js 2 · Tailwind CSS 4 · MySQL

## The pattern: modular monolith

One deployable Laravel app, two layers:

```
app/
  Core/          ← the factory: shared, product-agnostic modules
  Modules/       ← the products: business-specific modules (empty in v0.1)
```

### The three rules

1. **Core never imports from `app/Modules`.** Core knows product modules
   only as opaque provider class-strings listed in `config/penova.php`.
2. **Modules build on Core.** They freely use Core models, services,
   middleware, Vue components and layouts.
3. **Reusable code is Core code.** The moment two modules need the same
   thing, it moves into `app/Core`.

These are enforced by convention and comments (see
`PenovaCoreServiceProvider`), not tooling — add an architecture test
(e.g. pest-plugin-arch) when the first real module lands.

## Backend layout

```
app/
  Core/
    PenovaCoreServiceProvider.php   ← registers everything Core
    Auth/          Controllers/, Requests/, routes.php        (login, logout, reset, opt-in register)
    Users/         Controllers/, Models/User, Requests/, Policies/, routes.php
    Roles/         Models/{Role,Permission}, Controllers/, Middleware/, Policies/, routes.php
    Settings/      Models/Setting, Services/SettingsManager, Controllers/, routes.php
    Notifications/ Controllers/, routes.php                   (native database channel)
    Logs/          Models/ActivityLog, Services/ActivityLogger, Controllers/, routes.php
    DataTable/     DataTableBuilder.php                       (server-side search/sort/paginate)
    Support/       Traits/RecordsActivity.php                 (cross-cutting helpers)
  Modules/         (see app/Modules/README.md for module anatomy)
  Http/            app-level glue only (base Controller, HandleInertiaRequests, DashboardController)
config/penova.php  admin prefix, registration toggle, datatable defaults, modules list
routes/penova.php  composes each Core module's own routes.php
```

Each Core module is **self-contained**: its controllers, models,
requests, policies and its own `routes.php` live together.
`routes/penova.php` only composes them — auth routes on the plain `web`
group, everything else under `/{admin-prefix}` + `auth` middleware with
route names `penova.*`.

Key seams modules program against:

- `App\Models\User` — thin subclass of `Core\Users\Models\User`; keeps
  native Laravel bindings working while behaviour lives in Core.
- `permission:<slug>` middleware + `$user->hasPermission()` — RBAC is
  intentionally package-free; swap in spatie/laravel-permission inside
  `Core\Roles` later without touching consumers.
- `SettingsManager` singleton — runtime (DB) settings, cached; deploy
  config stays in `config/penova.php`.
- `ActivityLogger` / `RecordsActivity` trait — one-line audit logging
  for any model.
- `DataTableBuilder` — pairs with `DataTable.vue` over a query-string
  contract (`?search&sort&direction&per_page&page`).

## Frontend layout

```
resources/js/
  app.js               ← Inertia entry; resolves pages from BOTH roots below
  Core/
    Layouts/           AdminLayout.vue (sidebar, bell, toasts), GuestLayout.vue
    Components/        Button, TextInput, Modal, Toast, Pagination, DataTable
    Pages/             Auth/, Dashboard/, Users/, Roles/, Settings/, Logs/, Notifications/
  Modules/             (one folder per product module, mirrors app/Modules)
```

Page resolution is convention-only: `Inertia::render('Core/Users/Index')`
→ `Core/Pages/Users/Index.vue`; `Inertia::render('Modules/Store/Products/Index')`
→ `Modules/Store/Pages/Products/Index.vue`. New modules need zero frontend
registration.

Shared Inertia props (`HandleInertiaRequests`): `app.name`, `auth.user`
(+ role slugs), `flash.success/error` (rendered by `Toast.vue`), and
`unreadNotifications` for the layout bell.

## Database

Core migrations (beyond the Laravel defaults): `roles`, `permissions`,
`role_user`, `permission_role`, `settings`, `activity_logs`,
`notifications`. `PenovaCoreSeeder` seeds the four Core permissions
(`users.manage`, `roles.manage`, `settings.manage`, `logs.view`), the
`admin` role, and an `admin@example.com / password` account (change it).

Module migrations live inside the module
(`app/Modules/<Name>/Database/Migrations`, loaded by its provider).

## Adding a product module (the point of all this)

1. `app/Modules/<Name>/` — provider, `routes.php`, controllers, models,
   migrations, seeder for its permissions.
2. `resources/js/Modules/<Name>/Pages/…` — Vue pages using Core layouts
   and components.
3. Register the provider in `config/penova.php` → `modules`.

`app/Modules/Store` is the reference implementation of this pattern —
see `app/Modules/README.md` for its anatomy. Nothing in `app/Core`
changes when a module is added.

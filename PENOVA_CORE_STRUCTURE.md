# Penova Core – Architecture Overview

Penova Core is a **Laravel product factory core**: a shared foundation on which multiple products (Booking, CRM, CMS, …) will be built, so that the parts repeated in ~90% of projects — authentication, user management, roles & permissions, settings, notifications, audit logs, UI components and data tables — are solved exactly once. The stack is **Laravel 12** (backend), **Vue 3 + Inertia.js 2** (frontend), **Tailwind CSS 4** (UI) and **MySQL** (database). The architecture is a **modular monolith**: one deployable application split into a product-agnostic `app/Core` layer and a business-specific `app/Modules` layer, where Core never depends on Modules and Modules build on top of Core. Version 0.1 ships the complete Core skeleton with working auth, panel CRUD screens and a seeded admin account; `app/Modules` is intentionally empty.

## Directory Structure

```text
app/
  Core/
    PenovaCoreServiceProvider.php      ← central provider (routes, config, policies, middleware, module boot)
    Auth/
      Controllers/                     AuthenticatedSessionController, PasswordResetLinkController,
                                       NewPasswordController, RegisteredUserController
      Requests/LoginRequest.php
      routes.php
    Users/
      Controllers/UserController.php
      Models/User.php                  ← canonical user model
      Requests/                        StoreUserRequest, UpdateUserRequest
      Policies/UserPolicy.php
      routes.php
    Roles/
      Models/                          Role, Permission
      Controllers/RoleController.php
      Middleware/EnsureUserHasPermission.php   ← the "permission:" route middleware
      Policies/RolePolicy.php
      routes.php
    Settings/
      Models/Setting.php
      Services/SettingsManager.php     ← cached key-value store (singleton)
      Controllers/SettingController.php
      routes.php
    Notifications/
      Controllers/NotificationController.php   ← feed over Laravel's native database channel
      routes.php
    Logs/
      Models/ActivityLog.php
      Services/ActivityLogger.php
      Controllers/ActivityLogController.php
      routes.php
    DataTable/
      DataTableBuilder.php             ← server-side search / sort / paginate for any Eloquent query
    Support/
      Traits/RecordsActivity.php       ← drop-in automatic audit logging for models
  Modules/
    README.md                          ← module anatomy & wiring guide (no modules yet)
  Http/
    Controllers/                       Controller (base, +AuthorizesRequests), DashboardController
    Middleware/HandleInertiaRequests.php
  Models/
    User.php                           ← thin subclass of Core\Users\Models\User (framework binding)

config/
  penova.php                           ← all Penova configuration

routes/
  web.php                              ← product-facing public routes ("/" redirects to panel)
  penova.php                           ← composes each Core module's own routes.php

resources/
  views/app.blade.php                  ← single Inertia root template
  js/
    app.js                             ← Inertia entry; resolves Core AND Modules pages
    Core/
      Layouts/                         AdminLayout.vue, GuestLayout.vue
      Components/                      Button, TextInput, Modal, Toast, Pagination, DataTable
      Pages/
        Auth/                          Login, ForgotPassword, ResetPassword, Register
        Dashboard/Index.vue
        Users/                         Index, Create, Edit
        Roles/Index.vue
        Settings/Index.vue
        Logs/Index.vue
        Notifications/Index.vue
    Modules/                           (empty; one folder per future product module)
  css/app.css                          ← Tailwind 4 entry (@import "tailwindcss")

database/
  migrations/                          2026_07_04_… create_roles_tables, create_settings_table,
                                       create_activity_logs_table, create_notifications_table
  seeders/                             PenovaCoreSeeder (permissions, admin role, admin user), DatabaseSeeder

tests/
  Feature/Core/AuthTest.php            ← smoke tests: login page, authentication, guest redirect

docs/
  architecture.md                      ← longer-form architecture notes
```

## Core Modules

### Auth
- Path: `app/Core/Auth`
- Key classes: `AuthenticatedSessionController`, `PasswordResetLinkController`, `NewPasswordController`, `RegisteredUserController`, `LoginRequest` (validation + per-email/IP rate limiting)
- Responsibility: session login/logout and the full password-reset flow. Self-registration exists but is **opt-in** per product via `PENOVA_REGISTRATION=true` — its routes are only registered when the flag is on.
- Routes: own `Auth/routes.php`, loaded by `routes/penova.php` on the plain `web` group (guest-facing, no admin prefix). Route names: `login`, `logout`, `password.*`, `register`.
- Frontend: `resources/js/Core/Pages/Auth/{Login,ForgotPassword,ResetPassword,Register}.vue` on `GuestLayout`.

### Users
- Path: `app/Core/Users`
- Key classes: `Models/User` (canonical user: roles relation, `hasRole()`, `hasPermission()`), `UserController` (resource CRUD), `StoreUserRequest` / `UpdateUserRequest`, `UserPolicy`
- Responsibility: user administration for the panel. `App\Models\User` remains as a thin subclass so native Laravel bindings (auth config, factories, packages) keep working while behaviour lives in Core.
- Routes: `Users/routes.php` → `Route::resource('users')` under `/admin`, names `penova.users.*`, guarded by `permission:users.manage`.
- Frontend: `Users/{Index,Create,Edit}.vue`; Index is the reference implementation of the DataTable pattern.

### Roles
- Path: `app/Core/Roles`
- Key classes: `Models/Role`, `Models/Permission`, `RoleController`, `EnsureUserHasPermission` middleware (aliased as `permission`), `RolePolicy`
- Responsibility: package-free RBAC. Permission slugs follow `<area>.<action>` (`users.manage`, `logs.view`, …); modules seed their own slugs. The RBAC internals are deliberately swappable (e.g. for spatie/laravel-permission) without touching consumers, since all callers go through `hasRole()` / `hasPermission()` / the `permission:` middleware.
- Routes: `Roles/routes.php` → `penova.roles.*` under `permission:roles.manage`.
- Frontend: `Roles/Index.vue` — list plus modal-based create/edit with permission checkboxes.

### Settings
- Path: `app/Core/Settings`
- Key classes: `Models/Setting` (grouped key-value rows, JSON-cast values), `Services/SettingsManager` (cached, registered as a singleton), `SettingController`
- Responsibility: runtime, admin-editable settings. Two deliberate layers: `config/penova.php` = deploy-time developer config (in git); `SettingsManager` = runtime admin config (in DB, cache-backed).
- Routes: `Settings/routes.php` → `penova.settings.{index,update}` under `permission:settings.manage`.
- Frontend: `Settings/Index.vue` (generic key-value form: site name, contact email).

### Notifications
- Path: `app/Core/Notifications`
- Key classes: `NotificationController` only — the module intentionally rides Laravel's **native database notification channel** (`Notifiable` on the user + standard `notifications` table) instead of a custom model.
- Responsibility: the shared notification surface: full-page feed, mark-one/mark-all-as-read, and the unread badge shared with every page. Any module sends notifications the plain Laravel way (`$user->notify(...)`) and they show up here.
- Routes: `Notifications/routes.php` → `penova.notifications.*` (no extra permission; every authenticated user has a feed).
- Frontend: `Notifications/Index.vue` + the bell with unread counter in `AdminLayout.vue`.

### Logs
- Path: `app/Core/Logs`
- Key classes: `Models/ActivityLog` (immutable rows, morphs to any subject), `Services/ActivityLogger` (single write entry point, honours `penova.logs.enabled`), `ActivityLogController`
- Responsibility: audit trail — who did what to which record. Automatic logging is available to any model (Core or Module) via the `RecordsActivity` trait in `app/Core/Support/Traits`, which records created/updated/deleted with the changed attributes.
- Routes: `Logs/routes.php` → `penova.logs.index` under `permission:logs.view`.
- Frontend: `Logs/Index.vue` (read-only DataTable).

### UI
- Path: `resources/js/Core` (frontend-only module — no backend folder)
- Key pieces: `Layouts/AdminLayout.vue` (sidebar, topbar, notification bell, logout, flash toasts), `Layouts/GuestLayout.vue`, and shared components `Button`, `TextInput`, `Modal`, `Toast`, `Pagination`
- Responsibility: the design system of the panel. Every page — Core or future Module — composes these instead of styling its own; `Toast.vue` renders the `flash.success/error` props shared by the Inertia middleware automatically.

### DataTable
- Path: `app/Core/DataTable` (backend) + `resources/js/Core/Components/DataTable.vue` (frontend)
- Key classes: `DataTableBuilder` — fluent wrapper (`for() → searchable() → sortable() → paginate()`) applying a shared query-string contract (`?search&sort&direction&per_page&page`) to any Eloquent query. Sortable columns are **whitelisted** (never raw client input) and `per_page` is capped by config.
- Responsibility: solve server-side tables once. The Vue half renders columns from a prop array, debounces search, toggles sort and emits Inertia partial visits; cells are customizable via `#cell-<key>` slots and a `#actions` slot.

## Service Provider & Config

**`App\Core\PenovaCoreServiceProvider`** — `app/Core/PenovaCoreServiceProvider.php`, registered in `bootstrap/providers.php`. It:

- merges `config/penova.php` (a no-op while Core lives in-app; kept so a future package extraction works unchanged)
- registers Core singletons (`SettingsManager`)
- **boots product modules** from `config('penova.modules')` — Core iterates opaque provider class-strings and never imports anything from `app/Modules` (rule #1 of the architecture, documented in the class docblock)
- aliases the `permission` route middleware
- registers policies (`User → UserPolicy`, `Role → RolePolicy`)
- loads `routes/penova.php` under the `web` middleware group
- registers the `penova::` view namespace (future-package safety)

Inertia's shared props live in `App\Http\Middleware\HandleInertiaRequests` (appended to the `web` group in `bootstrap/app.php`): `app.name`, `auth.user` (id, name, email, role slugs), `flash.success/error`, `unreadNotifications`.

**`config/penova.php`** — main keys:

- `name` — product display name (`PENOVA_NAME`)
- `admin.prefix`, `admin.middleware` — panel URI prefix (default `admin`) and middleware stack (`web`, `auth`)
- `auth.registration` — self-registration toggle (`PENOVA_REGISTRATION`, default off)
- `datatable.per_page`, `datatable.max_per_page` — table defaults (15 / 100)
- `logs.enabled`, `logs.retention_days` — audit logging switch and retention window
- `modules` — array of product-module service providers (empty in v0.1)

## Frontend Structure

`resources/js` mirrors the backend split: everything shared lives under `Core/`, product pages will live under `Modules/<Name>/`.

- **Entry point** `app.js` creates the Inertia app and resolves pages from **two glob roots**: `./Core/Pages/**/*.vue` and `./Modules/*/Pages/**/*.vue`.
- **Route → page wiring** is pure convention: a controller returns `Inertia::render('Core/Users/Index')` and the resolver maps it to `resources/js/Core/Pages/Users/Index.vue`; `Inertia::render('Modules/Booking/Calendar')` maps to `resources/js/Modules/Booking/Pages/Calendar.vue`. New modules therefore need **zero frontend registration**.
- **Layouts**: `AdminLayout.vue` (sidebar nav for Dashboard/Users/Roles/Settings/Logs, notification bell with unread badge, user menu, logout, `<Toast/>`) and `GuestLayout.vue` (centered auth card).
- **Shared components**: `Button` (primary/secondary/danger), `TextInput` (label + error, works with `useForm`), `Modal`, `Toast` (auto-dismissing flash), `Pagination` (Laravel paginator links), `DataTable` (see above).
- **Vite**: `@` aliases to `resources/js`; Tailwind 4 runs via `@tailwindcss/vite` with sources scanned from Blade/JS/Vue files. Single root Blade template `resources/views/app.blade.php`.

Verified working: migrations + seeder run on MySQL, all 27 routes register, `npm run build` compiles (pages are code-split), the test suite passes (5 tests), and `/login` renders a live Inertia page.

## Known Gaps / TODOs

- **No product module exists yet.** `app/Modules` holds only a README; the module contract (provider in `penova.modules`, own routes/migrations/pages) is documented but unproven until a first module (e.g. Booking) is built against it.
- **Skeleton-depth pages.** All panel screens are minimal reference implementations: Settings edits only two hard-coded keys with no grouping UI; the Dashboard is a placeholder card; the Logs page has no detail/diff view or filtering by user/subject.
- **DataTable is basic.** Global search + single-column sort + pagination only — no per-column filters, date ranges, column visibility, bulk actions or exports.
- **No Ziggy / named routes in JS.** `AdminLayout.vue` builds panel URLs by string convention from route names; adding Ziggy (or an equivalent) is the clean fix before URLs get complex.
- **Navigation is static.** The sidebar is a hard-coded array, not permission-filtered and not yet extensible by modules (the plan: move a nav registry into shared Inertia props when the first module lands).
- **Auth is deliberately simple.** No email verification, 2FA, session management UI or "remember me" polish; registration is off by default. Password-reset emails require a configured mailer.
- **Logs retention is config-only.** `penova.logs.retention_days` exists but no scheduled prune command consumes it yet.
- **RBAC has no management UI for permissions themselves** — permissions are seeded, only role↔permission assignment is editable in the panel (intentional: permission slugs are code-owned).
- **Test coverage is smoke-level.** Only `AuthTest` (3 tests) plus the reworked example test; Users/Roles/Settings/Logs modules have no feature tests yet.
- **Architecture rules are convention-only.** "Core never imports Modules" is enforced by docblocks and review, not tooling — add an architecture test (e.g. Pest arch plugin) once modules exist.
- **Default credentials.** `PenovaCoreSeeder` creates `admin@example.com / password` — must be changed in any non-local environment.

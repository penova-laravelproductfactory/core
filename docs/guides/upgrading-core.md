# Upgrading Core

Migration guidance for breaking changes between MAJOR versions of Penova Core.
Each section is a task: what changed, and the path forward.

---

## Admin namespace retired: Workspace / Operator (RFC-002 / D-024)

**Renamed and split.** The authenticated environment is the **Workspace** and the
seeded person is the **Operator** — "admin" is retired for both (D-004 / D-006):

- The Workspace URL prefix is `/workspace` (was `/admin`), from
  `penova.workspace.prefix` (`PENOVA_WORKSPACE_PREFIX`).
- The seed account is `operator@example.com` with the **`operator`** role slug (was
  `admin@example.com` / `admin`), from `penova.operator.*` (`PENOVA_OPERATOR_EMAIL`
  / `PENOVA_OPERATOR_PASSWORD`).
- The former single `penova.admin.*` key (which conflated routing and seed
  credentials) is split into `penova.workspace.*` and `penova.operator.*`.

- **Impact.** Links and scripts hitting `/admin` no longer resolve; the seed login
  account and role slug changed; `.env` files using `PENOVA_ADMIN_*` still work for
  now but are deprecated.
- **Why MAJOR.** A public config namespace, a URL, and a persisted role slug
  changed — breaking, so it lands on a MAJOR with this note and a one-cycle
  fallback.
- **Recovery path.**
  1. Prefer the new env vars: `PENOVA_WORKSPACE_PREFIX`, `PENOVA_OPERATOR_EMAIL`,
     `PENOVA_OPERATOR_PASSWORD`. The legacy `PENOVA_ADMIN_PREFIX` /
     `PENOVA_ADMIN_EMAIL` / `PENOVA_ADMIN_PASSWORD` are honoured for one cycle (with
     a deprecation notice) and removed at the next MAJOR.
  2. Update any hardcoded `/admin` links to `/workspace` (or your configured
     prefix).
  3. No manual data migration is required — the `operator` role slug is migrated in
     place, preserving existing permission grants and user assignments.

---

## Store is no longer enabled by default (RFC-004 / D-026)

**Default changed.** Core enables no business module by default — the
`config/penova.php` `modules` array now ships **empty**, Core's `DatabaseSeeder`
seeds only Core, and Core's login/welcome pages carry no Store-specific content.
Core is complete on its own; commerce is a Module you opt into.

- **Impact.** An application that relied on Store being present by default must
  now **explicitly enable the Store module** and include its seeding/composition
  step. A fresh install no longer has Store's routes, menu, widgets, or
  permissions until Store is enabled.
- **Why MAJOR.** The configuration *format* is unchanged, but the shipped
  *default behaviour* changes in a way an upgrader can observe — so it lands in a
  MAJOR release with this migration note.
- **Recovery path.** Re-enable Store explicitly:
  1. Add its provider to `config/penova.php`:
     ```php
     'modules' => [
         App\Modules\Store\StoreServiceProvider::class,
     ],
     ```
  2. Compose its seeding at the application layer — add
     `App\Modules\Store\Database\Seeders\StorePermissionsSeeder` to your
     application's seeding (Core no longer names it).
  3. Run the module's seeding path (e.g. `php artisan db:seed`).

  No code was removed — Store still ships in the repository — so recovery is a
  configuration/composition change only.

---

## Core is locale-neutral, English by default (RFC-005 / D-027)

**Default changed.** Core no longer ships Persian as its built-in language and
identity. The panel is now internationalized: user-facing text lives in
per-locale catalogs (`lang/en`, `lang/fa`), the base and fallback language is
**English**, and the default install renders **English, left-to-right, with
Latin numerals**. Persian is a first-party, opt-in **supported locale** — never
the default.

- **Impact.** A deployment that relied on Core rendering Persian out of the box
  will, after upgrading, render English until Persian is selected. Text
  direction follows the active locale (LTR by default, RTL under Persian), and
  the Persian font (Yekan Bakh) plus Persian numerals apply only under the
  Persian locale.
- **Why MAJOR.** No API, route, config key, or identifier changed, but the
  shipped *default language and direction* change in a way every user observes —
  so it lands in a MAJOR release with this migration note.
- **Recovery path.** To keep the Persian experience, select Persian as the
  application locale:
  ```dotenv
  # .env
  APP_LOCALE=fa
  ```
  That restores Persian copy, right-to-left layout, the Persian font, and
  Persian numerals. The English base remains the fallback: any catalog key not
  translated in `lang/fa` falls back to its English value rather than showing a
  raw key.

**Out of scope (unchanged by this release).** Core internationalizes *language*,
not *regional behavior*. Jalali calendars, locale-aware number/currency
formatting, and other regional conventions are not part of Core; the only locale
metadata Core standardizes is text direction. Persian-Indic number formatting a
Module performs (e.g. `toLocaleString('fa-IR')` in Module pages) is the Module's
own concern and is untouched.

**No identifiers moved.** Route names, permission slugs, menu keys, config keys,
and every other contract identifier are locale-invariant — only the human-facing
label a user reads is translated. Menu labels Core owns are resolved per-locale;
labels a Module provides remain the Module's own literal text.

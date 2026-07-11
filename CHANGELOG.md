# Changelog

Developer-facing changes to Penova Core — the strategy-backed changes that affect
how you install, configure, or upgrade Core. For step-by-step migrations see
[docs/guides/upgrading-core.md](docs/guides/upgrading-core.md); if a fresh install
misbehaves see [docs/guides/troubleshooting-core.md](docs/guides/troubleshooting-core.md).

Penova Core uses semantic versioning: breaking changes to the public contract land
only on a MAJOR, always with a documented migration path.

## [Unreleased] — toward the first tagged release

The following strategy-backed changes are in the current Core codebase.

### Admin namespace retired — Workspace / Operator (D-024)
- **What changed.** The authenticated environment is the **Workspace** at the
  `/workspace` URL prefix (`penova.workspace.*` routes), and the seeded role/person
  is the **Operator** (`operator` role slug, `operator@example.com` seed account).
  The former single `penova.admin.*` config key is split into `penova.workspace.*`
  (routing) and `penova.operator.*` (seed credentials).
- **Why.** "admin" is retired for both the environment and the person (D-004 /
  D-006). Breaking → MAJOR.
- **What you must do.** Prefer `PENOVA_WORKSPACE_PREFIX` and `PENOVA_OPERATOR_*`
  env vars; the legacy `PENOVA_ADMIN_PREFIX` / `PENOVA_ADMIN_*` are honoured for one
  cycle (with a deprecation notice) and removed next MAJOR. The `operator` role
  slug is migrated in place — grants and assignments are preserved. See
  [docs/guides/upgrading-core.md](docs/guides/upgrading-core.md).

### Store decoupled from Core (D-026)
- **What changed.** Core boots complete with **no business module enabled**; the
  `config/penova.php` `modules` array ships empty and `DatabaseSeeder` seeds only
  Core. Store remains in-repo as a disabled-by-default reference module.
- **Why.** Business capability is a Module, never Core (D-003 / D-007). Breaking to
  the shipped default → MAJOR.
- **What you must do.** If you relied on Store being present, enable it explicitly
  and compose its seeding at the app layer. See
  [docs/guides/upgrading-core.md](docs/guides/upgrading-core.md).

### Core is locale-neutral, English by default (D-027)
- **What changed.** Core is internationalized: English is the base and fallback
  locale, a fresh install renders English / LTR / Latin numerals, and Persian is a
  first-party **opt-in** supported locale (`APP_LOCALE=fa`). Regional formatting
  (numbers, calendar, currency) stays out of Core.
- **Why.** Core is global by design; regional behavior is a Module concern (D-007).
  The Persian-only → English-default flip is breaking → MAJOR.
- **What you must do.** To keep the Persian experience, set `APP_LOCALE=fa`. See
  [docs/guides/upgrading-core.md](docs/guides/upgrading-core.md).

### Experimental module-frontend seam (RFC-006 / D-028)
- **What changed.** A Module contributes Workspace pages and widgets through a new,
  **experimental** Manifest `frontend` section resolved via a generated registry
  (`php artisan penova:frontend-registry`, run automatically by `npm run build` /
  `npm run dev`). The old auto-glob / component-path convention is retired.
- **Why.** A deliberate, typed seam replaces accidental internals (D-028, and the
  explicit-over-magic principle).
- **Status.** **Experimental** — conspicuously labelled; it may change or be
  withdrawn without a MAJOR until a second independent Module graduates it. Not yet
  under the stability promise. Primary seam doc: `app/Modules/README.md`.
- **What you must do.** Nothing for existing apps. Module authors: declare frontend
  entries in the Manifest and build via the npm scripts (a bare `vite build` fails
  without the generated registry).

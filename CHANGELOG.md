# Changelog

Developer-facing changes to Penova Core — the strategy-backed changes that affect
how you install, configure, or upgrade Core. For step-by-step migrations see
[docs/guides/upgrading-core.md](docs/guides/upgrading-core.md); if a fresh install
misbehaves see [docs/guides/troubleshooting-core.md](docs/guides/troubleshooting-core.md).

Penova Core uses semantic versioning: breaking changes to the public contract land
only on a MAJOR, always with a documented migration path.

## [1.0.0-rc.1] — 2026-07-12

First release candidate toward Penova Core 1.0.0. Nothing here is frozen yet — the
surfaces below are the ones **expected to become stable at 1.0.0**, stated as a
candidate so builders can plan. SemVer stability guarantees begin at the 1.0.0
release, not at this release candidate.

### Candidate public contract for 1.0.0 (intended-stable, not yet frozen)
- **Intended-stable** (to be SemVer-guaranteed once 1.0.0 ships): the Manifest
  sections (`identity`, `menu`, `widgets`, `permissions`), the configuration format,
  and the Workspace routes/prefix (`penova.workspace.*`) with the Operator role.
- **Experimental** (may change or be withdrawn without a MAJOR): the module-frontend
  seam — the Manifest `frontend` section and its coordinate (RFC-006 / D-028;
  D-AUDIT-008).
- **Internal / not a contract**: the concrete Resource shape (D-AUDIT-009) and the
  Workspace widget pipeline (D-AUDIT-007).

The following strategy-backed changes ship in this release candidate.

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

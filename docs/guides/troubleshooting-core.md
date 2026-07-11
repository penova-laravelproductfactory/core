# Troubleshooting Core

First-run and install issues on a fresh Core-only install (no business module), and
the quickest fix for each. For version-to-version migrations, see
[upgrading-core.md](upgrading-core.md).

## "Invalid credentials" on first login

The seeded account is the **Operator**, not "admin":

| | |
|---|---|
| Email | `operator@example.com` |
| Password | `password` |
| Role | `operator` |

The old `admin@example.com` account no longer exists (D-024). Override the seed
credentials with `PENOVA_OPERATOR_EMAIL` / `PENOVA_OPERATOR_PASSWORD` before
seeding, and change them in any real environment.

## `/admin` shows nothing / 404

The Workspace lives under the **`/workspace`** prefix, not `/admin` (D-024). Visit
`/workspace` (or your configured `penova.workspace.prefix`). The legacy
`PENOVA_ADMIN_PREFIX` env var is honoured for one cycle if you must keep `/admin`
temporarily, but the canonical prefix is `workspace`.

## Front-end build fails: missing `generated/module-frontend-registry.js`

The module-frontend registry is a git-ignored build artifact and is absent on a
fresh checkout, so a bare `vite build` fails to import it. Build through the npm
scripts, which regenerate it first:

```bash
npm run build     # or: npm run dev
```

Both run `php artisan penova:frontend-registry` before Vite; you can also
regenerate it manually with that command. See `app/Modules/README.md`.

## Database errors on `migrate` / `db:seed`

`.env.example` ships with **SQLite** (`DB_CONNECTION=sqlite`), so `migrate --seed`
works with zero database setup. If you want MySQL instead:

1. In `.env`, set `DB_CONNECTION=mysql` and the `DB_HOST` / `DB_PORT` /
   `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` values.
2. Create the database first — Laravel does not create it for you.
3. Re-run `php artisan penova:install` (or `php artisan migrate --seed`).

If you switched connections after already migrating on SQLite, start clean with
`php artisan penova:install --fresh` (drops and re-runs everything).

# Penova Core — Getting Started

Penova Core lives in a plain Laravel application repository (it is not a
Composer package yet): clone it, then install.

## Setup

```bash
git clone https://github.com/penova-laravelproductfactory/core.git && cd core
composer install
npm install
cp .env.example .env
php artisan key:generate

# point .env at your MySQL database, then either:
php artisan penova:install          # = migrate + seed (add --fresh to start over)
# or the underlying commands yourself:
php artisan migrate --seed

npm run build       # or keep Vite running via the dev script below
composer run dev    # serves the app + queue + logs + Vite
```

> **Front-end build.** `npm run build`, `npm run dev`, and `composer run dev` all
> regenerate the module-frontend registry (`php artisan penova:frontend-registry`)
> before Vite runs. Build the front-end through these scripts — a bare `vite build`
> fails because the generated registry is git-ignored and absent on a fresh
> checkout (see `app/Modules/README.md`).

## Tests

```bash
php artisan test
```

`tests/Feature/Core/AdminFlowTest.php` is the **release gate** for Core:
it walks the whole admin experience (fresh DB → seed → login →
Workspace → create a user → see it listed + audit-logged → logout) and
must always be green.

## Default operator account

`PenovaCoreSeeder` (called from `DatabaseSeeder`) creates:

| | |
|---|---|
| Email | `operator@example.com` |
| Password | `password` |
| Role | `operator` (holds all four Core permissions: `users.manage`, `roles.manage`, `settings.manage`, `logs.view`) |

> **Dev/test only.** These credentials exist so a fresh checkout is usable
> immediately. In any real environment, change the email/password right
> after the first login (or seed real credentials via environment-specific
> seeders) — never ship the defaults.

The defaults can be overridden per environment before seeding:

```env
PENOVA_OPERATOR_EMAIL=owner@yourproduct.com
PENOVA_OPERATOR_PASSWORD=a-strong-generated-secret
```

The seeder reads `config('penova.operator.email')` / `config('penova.operator.password')`,
so no code changes are needed to seed real credentials. The legacy
`PENOVA_ADMIN_EMAIL` / `PENOVA_ADMIN_PASSWORD` variables are still honoured as a
one-cycle fallback and will be removed at the next MAJOR — prefer the
`PENOVA_OPERATOR_*` names.

## Auth flow

- Guests hitting any Workspace URL (the `/workspace` prefix by default, from `penova.workspace.prefix`) are redirected to `/login`.
- Successful login redirects to the intended URL, falling back to the
  Workspace (`penova.workspace`).
- Logout invalidates the session and returns to `/login`.
- Password reset: `/forgot-password` emails a link (requires a configured
  mailer; `MAIL_MAILER=log` writes it to the log in dev) →
  `/reset-password/{token}` sets the new password → back to `/login` with
  a status message.

## Self-registration (off by default)

Core ships with public registration **disabled**. To enable it for a
product, set:

```env
PENOVA_REGISTRATION=true
```

This registers the `/register` routes and shows the "Register" link on the
login page. The toggle is evaluated when routes load — if you cache routes
in production (`php artisan route:cache`), rebuild the cache after
changing it.

## If something goes wrong

First-run problems (wrong login, wrong prefix, a build that fails on the missing
front-end registry, or a database mis-config) and their fixes are collected in
[guides/troubleshooting-core.md](guides/troubleshooting-core.md).

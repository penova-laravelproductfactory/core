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

## Tests

```bash
php artisan test
```

`tests/Feature/Core/AdminFlowTest.php` is the **release gate** for Core:
it walks the whole admin experience (fresh DB → seed → login →
Workspace → create a user → see it listed + audit-logged → logout) and
must always be green.

## Default admin account

`PenovaCoreSeeder` (called from `DatabaseSeeder`) creates:

| | |
|---|---|
| Email | `admin@example.com` |
| Password | `password` |
| Role | `admin` (holds all four Core permissions: `users.manage`, `roles.manage`, `settings.manage`, `logs.view`) |

> **Dev/test only.** These credentials exist so a fresh checkout is usable
> immediately. In any real environment, change the email/password right
> after the first login (or seed real credentials via environment-specific
> seeders) — never ship the defaults.

The defaults can be overridden per environment before seeding:

```env
PENOVA_ADMIN_EMAIL=owner@yourproduct.com
PENOVA_ADMIN_PASSWORD=a-strong-generated-secret
```

The seeder reads `config('penova.admin.email')` / `config('penova.admin.password')`,
so no code changes are needed to seed real credentials.

## Auth flow

- Guests hitting any `/admin` URL are redirected to `/login`.
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

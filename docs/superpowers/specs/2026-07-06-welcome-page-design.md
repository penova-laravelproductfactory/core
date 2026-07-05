# Welcome Page — Design Spec

**Scope:** Core (product-facing root route)
**Date:** 2026-07-06
**Status:** Approved for implementation
**Stack:** Laravel 12 · Inertia 2 · Vue 3 · Tailwind 4 (Persian / RTL, font Yekan Bakh)

## Goal

Replace the bare `/ → /admin` redirect with a clean, Laravel-style Welcome page that
introduces **Penova Core Lite** as a "Laravel Product Factory Starter". Bilingual
(English headings + Persian sub-copy), on-brand (warm palette), with a clear path into
the admin panel. Everyone sees it (guests and authenticated users); the primary CTA
adapts. This is also the first page to adopt the new Penova brand palette as global
design tokens.

## Context (verified against the codebase)

- `/` currently redirects: `routes/web.php` → `redirect()->route('penova.dashboard')`,
  and `tests/Feature/ExampleTest.php` asserts `$this->get('/')->assertRedirect('/admin')`.
  **This test must be updated** as part of the work.
- Controllers live in `app/Http/Controllers/` (`Controller`, `DashboardController`).
  There is no `app/Core/Http/Controllers` namespace — the spec's original path is wrong.
- Inertia resolver (`resources/js/app.js`) maps `Core/<X>` → `resources/js/Core/Pages/<X>.vue`.
  So the render string is **`Core/Welcome`** (NOT `Core/Pages/Welcome`, which would
  resolve to `Core/Pages/Pages/Welcome.vue`).
- Design system (`resources/css/app.css`, Tailwind 4 `@theme`) currently defines only
  `--font-sans: Yekan Bakh`, `--color-brand: #ff4431`, `--color-brand-hover: #e63b2a`
  over Tailwind's default `slate`. There are **no** `surface`/`btn` classes — the spec's
  `from-surface-2`, `btn btn-primary` are from another system and must not be used.
- `<html dir="rtl">`; logical utilities (`ms/me/start/end`, `text-start`) follow it.
- `auth.user` is globally shared by `HandleInertiaRequests` (null for guests).
- GitHub repo: `https://github.com/penova-laravelproductfactory/core`. No docs site exists.

## Decisions

- **Audience:** everyone sees the Welcome page; nobody is auto-redirected. The primary
  CTA adapts via `auth.user` — guest → `route('login')`; authenticated → `/admin`.
- **Palette:** add the full Penova palette to `@theme` as **global tokens** (additive;
  existing slate-based pages are unaffected). Warm neutrals are a new `sand-50…900` ramp
  (named `sand` to avoid colliding with Tailwind's built-in `stone`).
- **Controller:** `app/Http/Controllers/WelcomeController.php`, single-action `__invoke()`,
  `Inertia::render('Core/Welcome')`, no props (page reads shared `auth.user`/`app.name`).
- **Route name:** `welcome` (product-facing, stays in `routes/web.php`).
- **Layout:** the page is **self-contained** (own full-screen shell + `<Head>`), not
  `GuestLayout` (a max-w-md centered card, too small for a landing page).
- **Font:** repo's existing Yekan Bakh (`font-sans`), not the palette artifact's Vazirmatn.

## Design-system change — `@theme` token additions

Add to `resources/css/app.css` `@theme` block:

```css
/* Secondary accent — blue */
--color-accent:        #2596be;   /* secondary links, checkmarks, GitHub ↗ */
--color-accent-hover:  #1f80a3;
--color-accent-light:  #7cc3dd;   /* links on dark surfaces */

/* Warm neutral scale (sand): off-white → near-black */
--color-sand-50:  #fbfaf9;  /* page background         */
--color-sand-100: #f1ede8;  /* subtle fill, chips, code */
--color-sand-200: #efeae4;  /* soft dividers           */
--color-sand-300: #e7e2dc;  /* card borders            */
--color-sand-400: #d6d0c9;  /* strong borders          */
--color-sand-500: #a9a29b;  /* faint text              */
--color-sand-600: #78716c;  /* muted text              */
--color-sand-700: #57534e;  /* body text               */
--color-sand-800: #44403c;  /* body strong             */
--color-sand-900: #1c1917;  /* headings, dark surfaces  */
```

And update the existing hover to the canonical palette value:
`--color-brand-hover: #e63b2a;` → `--color-brand-hover: #e63a29;` (imperceptible; one
source of truth). `--color-brand: #ff4431` already matches.

## Page structure — `resources/js/Core/Pages/Welcome.vue`

Full-height `bg-sand-50` canvas, centered column `max-w-5xl`, generous vertical rhythm.
Brand red used sparingly (primary CTA + small accents); everything else sand + accent.
`<Head title="Penova Core Lite" />`.

### 1. Hero
- Small brand mark (`bg-brand` rounded square with white "P").
- Title: **Penova Core Lite** (`text-sand-900`, extrabold) + tagline **Laravel Product
  Factory Starter** (`text-sand-600`).
- Subtitle (English + Persian), from the spec copy:
  - EN: "A production-ready core for your Laravel products — with auth, users, roles,
    settings, notifications and a clean admin panel, ready to host your Store, SMS and
    Payment modules."
  - FA: «یک هستهٔ آماده برای محصولات لاراولی شما؛ با احراز هویت، مدیریت کاربران و
    نقش‌ها، تنظیمات، نوتیفیکیشن‌ها و یک پنل ادمین تمیز که آمادهٔ نصب ماژول فروشگاه،
    پیامک و پرداخت است.»
- CTAs:
  - **Primary** «ورود به پنل مدیریت» — `bg-brand hover:bg-brand-hover text-white`.
    `href = auth.user ? '/admin' : '/login'` (literal paths — the repo has no Ziggy;
    Vue pages use literal URLs, e.g. StorefrontLayout's `/store/checkout`, `/login`).
  - **Secondary (outline)** «مشاهدهٔ مستندات» — white bg, `border border-brand text-brand`,
    external link to the GitHub repo, `target="_blank" rel="noopener"`.
- **Terminal snippet** (under CTAs): dark `bg-sand-900` rounded card, mono font,
  `text-accent` `$` prompt + `text-sand-300` command: `php artisan penova:install`.

### 2. What you get (4 feature cards)
Heading: "What you get with Penova Core Lite" / «Core Lite چه چیزهایی برایت آماده کرده
است؟». Responsive grid (2×2 desktop, 1-col mobile). Cards: `bg-white border border-sand-300
rounded-xl`, a small `text-accent` check/marker, EN title (`text-sand-900`), FA line
(`text-sand-600`). Content (verbatim from spec):

1. **Authentication & Accounts** — "Full login, registration and password reset flow,
   ready to drop into any product panel." / «جریان کامل ورود، ثبت‌نام و بازیابی رمز
   عبور، آمادهٔ استفاده در هر پنل محصول.»
2. **Users & Roles** — "Admin screens to manage users, roles and permissions without any
   extra packages." / «مدیریت کاربران، نقش‌ها و permissionها بدون نیاز به پکیج خارجی.»
3. **Settings & Notifications** — "Runtime settings and a shared notification feed, so
   every module can reuse the same surface." / «تنظیمات runtime و یک feed مشترک
   نوتیفیکیشن که همهٔ ماژول‌ها می‌توانند روی آن سوار شوند.»
4. **Admin UI & DataTable** — "A clean admin layout, reusable components and a
   server-side DataTable pattern for any CRUD page." / «یک AdminLayout تمیز،
   کامپوننت‌های تکرارپذیر و الگوی DataTable سمت سرور برای هر صفحهٔ CRUD.»

### 3. Modules (coming / installable)
Heading: "Plug-in modules when you're ready" / «وقتی آمادهٔ محصول شدی، ماژول‌ها را اضافه
کن». Intro (EN + FA from spec). Three cards, each with a small `bg-sand-100 text-sand-600`
«به‌زودی» badge (introduction only, not installable yet):

1. **Store Module** — "Turn Core Lite into a real store: products, cart, checkout and
   orders." / «Core Lite را به یک فروشگاه واقعی تبدیل می‌کند: محصولات، سبد خرید،
   checkout و سفارش‌ها.»
2. **SMS Module** — "Send order and OTP SMS using multiple Iranian providers from one
   unified module." / «ارسال پیامک سفارش‌ها و OTP با چندین پروایدر ایرانی از طریق یک
   ماژول یکپارچه.»
3. **Payment Module** — "Connect your store to Iranian payment gateways through a single,
   extensible integration layer." / «اتصال فروشگاه به درگاه‌های پرداخت ایران از طریق یک
   لایهٔ یکپارچه و قابل‌گسترش.»

### 4. Footer
Small `text-sand-500` line: "Penova Core Lite · Laravel Product Factory Starter". Two
`text-accent hover:text-accent-hover` links: **GitHub** (repo URL) and **Documentation**
(repo URL for now), both `target="_blank" rel="noopener"`. No version string (no source
of truth; YAGNI).

## Behavior summary

| Visitor | Primary CTA target (literal href) |
|---|---|
| Guest | `/login` |
| Authenticated | `/admin` |

Docs / GitHub links always open the GitHub repo in a new tab.

## Testing

Update `tests/Feature/ExampleTest.php`: the root-URL test asserts `/` returns **200** and
renders the `Core/Welcome` Inertia component (replacing the `assertRedirect('/admin')`).
Rendering fidelity is covered by `npm run build`.

## Out of scope

Authenticated-vs-guest content differences beyond the CTA target; a real docs site;
module installation flow; version string; any change to existing admin/slate pages.

# Welcome Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the `/ → /admin` redirect with a clean, bilingual Penova Core Lite welcome page, and seed the new brand palette as global Tailwind `@theme` tokens.

**Architecture:** A single-action `WelcomeController` renders the `Core/Welcome` Inertia page at `/` for everyone (guests and authenticated users). The self-contained Vue page reads the shared `auth.user` prop to adapt its primary CTA. The warm Penova palette (`accent*`, `sand-50…900`) is added to `resources/css/app.css` `@theme` — additive, leaving existing slate-based pages untouched.

**Tech Stack:** Laravel 12, Inertia 2, Vue 3, Tailwind 4. Tests: Pest/PHPUnit feature test. Persian/RTL, font Yekan Bakh.

## Global Constraints

- **No AI attribution** in any commit message or file (repo policy).
- **Persian/RTL** page; English headings/sub-copy carry `dir="ltr"`. Font is the repo's **Yekan Bakh** (`font-sans`), NOT Vazirmatn.
- **Use repo design tokens only:** `brand` / `brand-hover`, and the new `accent*` / `sand-*`. Do NOT use `slate-*` on this page, and do NOT use non-existent `surface`/`btn` classes.
- **Literal hrefs in Vue** (the app has no Ziggy): guest CTA `/login`, signed-in CTA `/admin`.
- **Inertia render string is exactly `Core/Welcome`** (resolver injects `Pages/`).
- **Everyone sees the page; nobody is redirected.** Primary CTA target adapts to `auth.user`.
- **Palette is additive to `@theme`** — must not restyle existing admin pages.
- GitHub/Docs links → `https://github.com/penova-laravelproductfactory/core`, `target="_blank" rel="noopener"`.

## File Structure

- Create: `app/Http/Controllers/WelcomeController.php` — single-action, renders `Core/Welcome`.
- Modify: `routes/web.php` — `/` now points to `WelcomeController` (name `welcome`).
- Modify: `tests/Feature/ExampleTest.php` — root URL renders Welcome (was: redirects).
- Modify: `resources/css/app.css` — add `accent*` + `sand-*` tokens; update `brand-hover`.
- Create: `resources/js/Core/Pages/Welcome.vue` — the self-contained landing page.

---

### Task 1: Route, controller, and root-URL test

**Files:**
- Create: `app/Http/Controllers/WelcomeController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/ExampleTest.php`

**Interfaces:**
- Consumes: Inertia (`Inertia::render`), the shared `auth.user` prop (client-side; server passes no props).
- Produces: `GET /` (route name `welcome`) rendering Inertia component **`Core/Welcome`** with 200. Task 3 supplies the matching `.vue` file; this task's test asserts only the component name (server-side Inertia testing does not render Vue), so it passes independently.

- [ ] **Step 1: Write the failing test**

Replace the entire contents of `tests/Feature/ExampleTest.php` with:

```php
<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL renders the Penova Core Lite welcome page (shown to
     * everyone; the page itself adapts its CTA to auth state).
     */
    public function test_the_root_url_renders_the_welcome_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Core/Welcome'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/ExampleTest.php`
Expected: FAIL — `/` still redirects (302 to `/admin`), so `assertOk()` fails.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/WelcomeController.php`:

```php
<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Product-facing landing page at "/" — introduces Penova Core Lite.
 *
 * Shown to everyone (guests and authenticated users); nobody is
 * redirected. The page reads the shared auth.user prop to adapt its
 * primary CTA (login vs. panel), so this controller needs no props.
 */
class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Core/Welcome');
    }
}
```

- [ ] **Step 4: Point the root route at the controller**

In `routes/web.php`, add the import below the existing `use Illuminate\Support\Facades\Route;` line:

```php
use App\Http\Controllers\WelcomeController;
```

Then replace the root route line:

```php
Route::get('/', fn () => redirect()->route('penova.dashboard'));
```

with:

```php
Route::get('/', WelcomeController::class)->name('welcome');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/ExampleTest.php`
Expected: PASS (1 passing).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/WelcomeController.php routes/web.php tests/Feature/ExampleTest.php
git commit -m "feat(core): welcome page route + controller"
```

---

### Task 2: Brand palette tokens

**Files:**
- Modify: `resources/css/app.css`

**Interfaces:**
- Produces (for Task 3): Tailwind utilities generated from new `@theme` tokens —
  `accent` / `accent-hover` / `accent-light`, and `sand-50 … sand-900`
  (e.g. `bg-sand-50`, `border-sand-300`, `text-sand-900`, `text-accent`).
- Consumes: nothing.

- [ ] **Step 1: Add the tokens**

In `resources/css/app.css`, replace this `@theme` block:

```css
@theme {
    --font-sans: 'Yekan Bakh', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';
    --font-yekan: 'Yekan Bakh', ui-sans-serif, system-ui, sans-serif;

    --color-brand: #ff4431;
    --color-brand-hover: #e63b2a;
}
```

with:

```css
@theme {
    --font-sans: 'Yekan Bakh', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';
    --font-yekan: 'Yekan Bakh', ui-sans-serif, system-ui, sans-serif;

    --color-brand: #ff4431;
    --color-brand-hover: #e63a29;

    /* Secondary accent — blue */
    --color-accent: #2596be;
    --color-accent-hover: #1f80a3;
    --color-accent-light: #7cc3dd;

    /* Warm neutral scale (sand): off-white → near-black. Named "sand" to
       avoid colliding with Tailwind's built-in "stone". */
    --color-sand-50: #fbfaf9;
    --color-sand-100: #f1ede8;
    --color-sand-200: #efeae4;
    --color-sand-300: #e7e2dc;
    --color-sand-400: #d6d0c9;
    --color-sand-500: #a9a29b;
    --color-sand-600: #78716c;
    --color-sand-700: #57534e;
    --color-sand-800: #44403c;
    --color-sand-900: #1c1917;
}
```

(Note: the only change to existing tokens is `--color-brand-hover` `#e63b2a → #e63a29`.)

- [ ] **Step 2: Verify the build compiles**

Run: `npm run build`
Expected: build succeeds, no CSS errors.

- [ ] **Step 3: Commit**

```bash
git add resources/css/app.css
git commit -m "feat(core): add Penova brand palette tokens (accent + sand)"
```

---

### Task 3: Welcome page component

**Files:**
- Create: `resources/js/Core/Pages/Welcome.vue`

**Interfaces:**
- Consumes: shared Inertia props `auth.user` (adaptive CTA) — from `HandleInertiaRequests`; the `accent`/`sand`/`brand` tokens from Task 2; rendered by Task 1's controller as component `Core/Welcome`.
- Produces: the landing page (no exports).

- [ ] **Step 1: Create the page**

Create `resources/js/Core/Pages/Welcome.vue`:

```vue
<script setup>
/**
 * Core — public landing page at "/". Introduces Penova Core Lite as a
 * "Laravel Product Factory Starter". Shown to everyone; the primary CTA
 * adapts to auth state (panel when signed in, login otherwise). Uses the
 * Penova brand palette (@theme: brand / accent / sand). Self-contained
 * (own full-screen shell, not GuestLayout).
 */
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth?.user);

// Guest → login; signed-in → straight to the panel (no login round-trip).
// Literal paths: the app has no Ziggy in JS.
const panelHref = computed(() => (user.value ? '/admin' : '/login'));

const repoUrl = 'https://github.com/penova-laravelproductfactory/core';

const features = [
    {
        title: 'Authentication & Accounts',
        fa: 'جریان کامل ورود، ثبت‌نام و بازیابی رمز عبور، آمادهٔ استفاده در هر پنل محصول.',
        en: 'Full login, registration and password reset flow, ready to drop into any product panel.',
    },
    {
        title: 'Users & Roles',
        fa: 'مدیریت کاربران، نقش‌ها و permissionها بدون نیاز به پکیج خارجی.',
        en: 'Admin screens to manage users, roles and permissions without any extra packages.',
    },
    {
        title: 'Settings & Notifications',
        fa: 'تنظیمات runtime و یک feed مشترک نوتیفیکیشن که همهٔ ماژول‌ها می‌توانند روی آن سوار شوند.',
        en: 'Runtime settings and a shared notification feed, so every module can reuse the same surface.',
    },
    {
        title: 'Admin UI & DataTable',
        fa: 'یک AdminLayout تمیز، کامپوننت‌های تکرارپذیر و الگوی DataTable سمت سرور برای هر صفحهٔ CRUD.',
        en: 'A clean admin layout, reusable components and a server-side DataTable pattern for any CRUD page.',
    },
];

const modules = [
    {
        title: 'Store Module',
        fa: 'Core Lite را به یک فروشگاه واقعی تبدیل می‌کند: محصولات، سبد خرید، checkout و سفارش‌ها.',
        en: 'Turn Core Lite into a real store: products, cart, checkout and orders.',
    },
    {
        title: 'SMS Module',
        fa: 'ارسال پیامک سفارش‌ها و OTP با چندین پروایدر ایرانی از طریق یک ماژول یکپارچه.',
        en: 'Send order and OTP SMS using multiple Iranian providers from one unified module.',
    },
    {
        title: 'Payment Module',
        fa: 'اتصال فروشگاه به درگاه‌های پرداخت ایران از طریق یک لایهٔ یکپارچه و قابل‌گسترش.',
        en: 'Connect your store to Iranian payment gateways through a single, extensible integration layer.',
    },
];
</script>

<template>
    <Head title="Penova Core Lite" />

    <div class="min-h-screen bg-sand-50 text-sand-700">
        <div class="mx-auto max-w-5xl px-6 py-16 sm:py-24">

            <!-- Hero -->
            <section class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-brand text-xl font-extrabold text-white">
                    P
                </div>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-sand-900 sm:text-5xl">
                    Penova Core Lite
                </h1>
                <p class="mt-2 text-lg font-medium text-sand-600">Laravel Product Factory Starter</p>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-sand-700">
                    یک هستهٔ آماده برای محصولات لاراولی شما؛ با احراز هویت، مدیریت کاربران و نقش‌ها،
                    تنظیمات، نوتیفیکیشن‌ها و یک پنل ادمین تمیز که آمادهٔ نصب ماژول فروشگاه، پیامک و
                    پرداخت است.
                </p>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-sand-500" dir="ltr">
                    A production-ready core for your Laravel products — auth, users, roles, settings,
                    notifications and a clean admin panel, ready to host your Store, SMS and Payment modules.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        :href="panelHref"
                        class="rounded-lg bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-hover"
                    >
                        ورود به پنل مدیریت
                    </Link>
                    <a
                        :href="repoUrl"
                        target="_blank"
                        rel="noopener"
                        class="rounded-lg border border-brand px-5 py-2.5 text-sm font-bold text-brand hover:bg-brand/5"
                    >
                        مشاهدهٔ مستندات
                    </a>
                </div>

                <!-- Install snippet -->
                <div class="mx-auto mt-8 max-w-md rounded-lg bg-sand-900 px-4 py-3 text-start font-mono text-sm" dir="ltr">
                    <span class="text-accent">$</span>
                    <span class="text-sand-300"> php artisan penova:install</span>
                </div>
            </section>

            <!-- What you get -->
            <section class="mt-20">
                <h2 class="text-center text-2xl font-bold text-sand-900" dir="ltr">What you get with Penova Core Lite</h2>
                <p class="mt-1 text-center text-sm text-sand-500">Core Lite چه چیزهایی برایت آماده کرده است؟</p>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="feature in features"
                        :key="feature.title"
                        class="rounded-xl border border-sand-300 bg-white p-5"
                    >
                        <h3 class="flex items-center gap-2 text-base font-bold text-sand-900" dir="ltr">
                            <span class="text-accent">✓</span>
                            {{ feature.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-sand-600">{{ feature.fa }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-sand-500" dir="ltr">{{ feature.en }}</p>
                    </article>
                </div>
            </section>

            <!-- Modules -->
            <section class="mt-20">
                <h2 class="text-center text-2xl font-bold text-sand-900" dir="ltr">Plug-in modules when you're ready</h2>
                <p class="mt-1 text-center text-sm text-sand-500">وقتی آمادهٔ محصول شدی، ماژول‌ها را اضافه کن</p>

                <p class="mx-auto mt-4 max-w-2xl text-center text-sm leading-relaxed text-sand-600">
                    Penova Core Lite به‌صورت یک هستهٔ رایگان می‌آید. هر زمان به محصول واقعی نیاز داشتی،
                    ماژول‌هایی مثل فروشگاه، پیامک و پرداخت را روی همین هسته اضافه می‌کنی، بدون این‌که
                    دوباره همه‌چیز را بنویسی.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <article
                        v-for="mod in modules"
                        :key="mod.title"
                        class="rounded-xl border border-sand-300 bg-white p-5"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-sand-900" dir="ltr">{{ mod.title }}</h3>
                            <span class="rounded bg-sand-100 px-2 py-0.5 text-xs font-medium text-sand-600">به‌زودی</span>
                        </div>
                        <p class="mt-2 text-sm leading-relaxed text-sand-600">{{ mod.fa }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-sand-500" dir="ltr">{{ mod.en }}</p>
                    </article>
                </div>
            </section>

            <!-- Footer -->
            <footer class="mt-20 border-t border-sand-200 pt-6 text-center">
                <p class="text-xs text-sand-500">Penova Core Lite · Laravel Product Factory Starter</p>
                <div class="mt-2 flex items-center justify-center gap-4 text-sm">
                    <a :href="repoUrl" target="_blank" rel="noopener" class="text-accent hover:text-accent-hover">GitHub ↗</a>
                    <a :href="repoUrl" target="_blank" rel="noopener" class="text-accent hover:text-accent-hover">Documentation ↗</a>
                </div>
            </footer>

        </div>
    </div>
</template>
```

- [ ] **Step 2: Verify the build compiles**

Run: `npm run build`
Expected: build succeeds; `Core/Welcome` chunk emitted, no Vue compile errors.

- [ ] **Step 3: Run the root-URL test to confirm end-to-end wiring**

Run: `php artisan test tests/Feature/ExampleTest.php`
Expected: PASS (the component the controller names now has a real page).

- [ ] **Step 4: Commit**

```bash
git add resources/js/Core/Pages/Welcome.vue
git commit -m "feat(core): welcome page (hero, features, modules, footer)"
```

---

## Self-Review

**Spec coverage:**
- Route/controller at `/`, render `Core/Welcome`, name `welcome` → Task 1. ✓
- Everyone sees page; CTA adapts (`/login` vs `/admin`) → Task 3 `panelHref`. ✓
- ExampleTest flips redirect → renders Welcome → Task 1 Step 1. ✓
- Palette as global `@theme` tokens (`accent*`, `sand-*`) + `brand-hover` update → Task 2. ✓
- Hero (title, tagline, EN+FA subtitle, primary/outline CTAs, install snippet) → Task 3. ✓
- 4 feature cards + 3 module cards with «به‌زودی» badge + footer with GitHub/Docs → Task 3. ✓
- Self-contained layout, Yekan Bakh, RTL with `dir="ltr"` on English/numbers → Task 3. ✓
- Verbatim copy from spec → Task 3 arrays/markup. ✓

**Placeholder scan:** none — full code/commands in every step.

**Type consistency:** render string `Core/Welcome` identical in Task 1 controller and Task 1 test and Task 3 filename (`resources/js/Core/Pages/Welcome.vue`). Token names used in Task 3 (`bg-sand-50`, `text-sand-900`, `border-sand-300`, `bg-sand-100`, `text-accent`, `text-accent-hover`, `bg-brand`, `hover:bg-brand-hover`, `border-brand`) all defined in Task 2. `panelHref`/`user`/`repoUrl`/`features`/`modules` are self-consistent within Task 3; loop variable is `mod` (not the reserved-ish `module`).

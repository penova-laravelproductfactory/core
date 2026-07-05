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

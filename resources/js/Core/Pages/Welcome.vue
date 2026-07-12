<script setup>
/**
 * Core — public landing page at "/". Introduces Penova Core as a "Laravel
 * Product Factory". Shown to everyone; the primary CTA adapts to auth state
 * (Workspace when signed in, login otherwise). Uses the Penova brand palette
 * (@theme: brand / accent / sand). Self-contained (own full-screen shell).
 *
 * Locale-neutral (RFC-005 / D-027 / D-AUDIT-006): all copy renders in the active
 * locale via useI18n — English base, Persian when APP_LOCALE=fa — instead of the
 * former Persian-hardcoded layout. Page direction follows <html dir> from the
 * locale; only command snippets and proper nouns stay LTR.
 */
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

const { t } = useI18n();

const user = computed(() => usePage().props.auth?.user);

const branding = computed(() => usePage().props.branding ?? {});
const logoUrl = computed(() => branding.value.logo_url || '/penova-logo.png');
const brandName = computed(() => branding.value.name || 'Penova Core');
const footerText = computed(
    () => branding.value.footer_text || 'Penova Core · Laravel Product Factory',
);

// Guest → login; signed-in → straight to the Workspace (no login round-trip),
// built from the configured Workspace prefix (shared prop).
const ws = useWorkspacePath();
const panelHref = computed(() => (user.value ? ws() : '/login'));

const repoUrl = 'https://github.com/penovaio/core';

// Keys into the ui.welcome catalog; copy itself lives in lang/{en,fa}/ui.php.
const features = ['auth', 'users', 'settings', 'ui'];
// Illustrative capability categories a Module can add — not specific or regional
// product names (Core names no Module; D-026 / D-007).
const modules = ['commerce', 'messaging', 'payments'];
</script>

<template>
    <Head title="Penova Core" />

    <div class="min-h-screen bg-sand-50 text-sand-700">
        <div class="mx-auto max-w-5xl px-6 py-16 sm:py-24">

            <!-- Hero -->
            <section class="text-center">
                <img
                    :src="logoUrl"
                    :alt="brandName"
                    width="80"
                    height="80"
                    class="mx-auto h-20 w-20"
                />

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-sand-900 sm:text-5xl">
                    {{ brandName }}
                </h1>
                <p class="mt-2 text-lg font-medium text-sand-600" dir="ltr">Laravel Product Factory</p>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-sand-700">
                    {{ t('welcome.hero_intro') }}
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        :href="panelHref"
                        class="rounded-lg bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-hover"
                    >
                        {{ t('welcome.cta_workspace') }}
                    </Link>
                    <a
                        :href="repoUrl"
                        target="_blank"
                        rel="noopener"
                        class="rounded-lg border border-brand px-5 py-2.5 text-sm font-bold text-brand hover:bg-brand/5"
                    >
                        {{ t('welcome.cta_docs') }}
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
                <h2 class="text-center text-2xl font-bold text-sand-900">{{ t('welcome.features_heading') }}</h2>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="key in features"
                        :key="key"
                        class="rounded-xl border border-sand-300 bg-white p-5"
                    >
                        <h3 class="flex items-center gap-2 text-base font-bold text-sand-900">
                            <span class="text-accent">✓</span>
                            {{ t(`welcome.features.${key}.title`) }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-sand-600">{{ t(`welcome.features.${key}.desc`) }}</p>
                    </article>
                </div>
            </section>

            <!-- Modules -->
            <section class="mt-20">
                <h2 class="text-center text-2xl font-bold text-sand-900">{{ t('welcome.modules_heading') }}</h2>

                <p class="mx-auto mt-4 max-w-2xl text-center text-sm leading-relaxed text-sand-600">
                    {{ t('welcome.modules_intro') }}
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <article
                        v-for="key in modules"
                        :key="key"
                        class="rounded-xl border border-sand-300 bg-white p-5"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-sand-900">{{ t(`welcome.modules.${key}.title`) }}</h3>
                            <span class="rounded bg-sand-100 px-2 py-0.5 text-xs font-medium text-sand-600">{{ t('welcome.coming_soon') }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-relaxed text-sand-600">{{ t(`welcome.modules.${key}.desc`) }}</p>
                    </article>
                </div>
            </section>

            <!-- Footer -->
            <footer class="mt-20 border-t border-sand-200 pt-6 text-center">
                <p class="text-xs text-sand-600">{{ footerText }}</p>
                <div class="mt-2 flex items-center justify-center gap-4 text-sm">
                    <a :href="repoUrl" target="_blank" rel="noopener" class="text-accent-hover hover:text-accent" dir="ltr">GitHub ↗</a>
                    <a :href="repoUrl" target="_blank" rel="noopener" class="text-accent-hover hover:text-accent">{{ t('welcome.footer_docs') }} ↗</a>
                </div>
            </footer>

        </div>
    </div>
</template>

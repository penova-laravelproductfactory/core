<script setup>
/**
 * Core\UI — centered-card layout for guest pages (login, register,
 * password reset).
 *
 * Structure: full-height slate canvas → white card (brand title at the
 * top, page form in the default slot) → small Penova footer.
 */
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Core/composables/i18n';

defineProps({
    // Optional document title, e.g. <GuestLayout title="Log in">.
    title: String,
});

const appName = computed(() => usePage().props.app.name);
const { t } = useI18n();
</script>

<template>
    <Head v-if="title" :title="title" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-100 px-4">
        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow">
            <div class="mb-6 text-center">
                <div class="text-2xl font-semibold text-slate-900">{{ appName }}</div>
                <div class="mt-1 text-sm text-slate-500">{{ t('shell.tagline') }}</div>
            </div>

            <slot />
        </div>

        <footer class="mt-6 text-xs text-slate-400">
            © {{ appName }} – {{ t('shell.guest_footer') }}
        </footer>
    </div>
</template>

<script setup>
/**
 * Core\UI — Workspace branding reminder. Surfaces above the fold whenever
 * the panel is still shipping Penova's own default branding, so nobody
 * accidentally ships a client product looking like the vendor's demo.
 * Dismissal is a client-only preference (localStorage), not server state.
 */
import { Link } from '@inertiajs/vue3';
import { SwatchIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useDismiss } from '@/Core/composables/useDismiss';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

defineProps({ brandingConfigured: { type: Boolean, required: true } });

const { dismissed, dismiss } = useDismiss('penova.dismiss.branding');
const ws = useWorkspacePath();
const { t } = useI18n();
</script>

<template>
    <section
        v-if="!brandingConfigured && !dismissed"
        class="relative overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6"
    >
        <button
            type="button"
            class="absolute inset-e-3 top-3 rounded-lg p-1 text-amber-400 transition-colors hover:bg-amber-100 hover:text-amber-600"
            :aria-label="t('common.close')"
            @click="dismiss"
        >
            <XMarkIcon class="size-4" />
        </button>

        <div class="flex items-start gap-3 pe-8">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <SwatchIcon class="size-5" />
            </span>
            <div>
                <p class="font-bold text-amber-900">{{ t('home.branding_reminder_title') }}</p>
                <p class="mt-1 text-sm text-amber-800">{{ t('home.branding_reminder_body') }}</p>
                <Link
                    :href="ws('/settings')"
                    class="mt-3 inline-block rounded-lg bg-brand px-4 py-2 text-sm font-bold text-white transition-colors hover:bg-brand-hover"
                >
                    {{ t('home.configure_branding') }}
                </Link>
            </div>
        </div>
    </section>
</template>

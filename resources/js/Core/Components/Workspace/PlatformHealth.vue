<script setup>
/**
 * Core\UI — Workspace platform health. Reads PlatformHealth::check() (Task
 * 2): five cheap subsystem probes reported Ready/Warning only. A compact
 * list, not a chart — a single degraded row should read at a glance, not
 * demand analysis.
 */
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { useI18n } from '@/Core/composables/i18n';

defineProps({ health: { type: Array, required: true } });

const { t } = useI18n();
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ t('home.health_heading') }}</h2>

        <ul class="mt-2 divide-y divide-slate-100">
            <li v-for="h in health" :key="h.key" class="flex items-center justify-between gap-3 py-2.5">
                <span class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <component
                        :is="h.status === 'ready' ? CheckCircleIcon : ExclamationTriangleIcon"
                        class="size-4 shrink-0"
                        :class="h.status === 'ready' ? 'text-emerald-500' : 'text-amber-500'"
                    />
                    {{ h.label }}
                </span>
                <span class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">{{ h.detail }}</span>
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-semibold"
                        :class="h.status === 'ready' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                    >{{ h.status === 'ready' ? t('status.ready') : t('status.warning') }}</span>
                </span>
            </li>
        </ul>
    </section>
</template>

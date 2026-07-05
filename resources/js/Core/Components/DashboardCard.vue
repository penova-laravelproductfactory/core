<script setup>
/**
 * Core\UI — base card for dashboard widgets: title + optional icon
 * badge + value, with loading/error states handled once for every
 * widget. The 90% case is props-only:
 *
 *   <DashboardCard title="…" icon="users" :value="count"
 *                  :loading="loading" :error="error">زیرنویس</DashboardCard>
 *
 * No trend logic here (by design, for now): widgets needing richer
 * visuals override the slots instead of forking the card —
 *   #icon  → replaces the whole badge (e.g. trend-colored)
 *   #value → replaces the value block (e.g. colored count + delta line)
 *   default→ small muted footer line (subtitle / hint)
 * Future trend support lands HERE as props, without rewriting widgets.
 */
import { computed } from 'vue';
import {
    BellIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    ClockIcon,
    Squares2X2Icon,
    UsersIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    title: { type: String, required: true },
    // Icon key — same string convention as menu/widget descriptors
    // (calendar|users|bell|clock|chart|squares). Unknown keys fall back
    // to the squares icon; the #icon slot overrides the badge entirely.
    icon: { type: String, default: null },
    value: { type: [Number, String], default: null },
    loading: { type: Boolean, default: false },
    error: { type: String, default: null },
});

const icons = {
    bell: BellIcon,
    calendar: CalendarDaysIcon,
    chart: ChartBarIcon,
    clock: ClockIcon,
    squares: Squares2X2Icon,
    users: UsersIcon,
};

const iconComponent = computed(() => icons[props.icon] ?? Squares2X2Icon);
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between">
            <div class="text-sm font-medium text-slate-500">{{ title }}</div>

            <slot name="icon">
                <span
                    v-if="icon"
                    class="inline-flex size-8 items-center justify-center rounded-full bg-slate-100 text-slate-500"
                >
                    <component :is="iconComponent" class="size-5" />
                </span>
            </slot>
        </div>

        <div v-if="loading" class="mt-2 text-3xl font-semibold text-slate-300">…</div>
        <div v-else-if="error" class="mt-2 text-sm leading-relaxed text-slate-400">{{ error }}</div>
        <div v-else class="mt-2">
            <slot name="value">
                <div class="text-3xl font-semibold text-slate-900">{{ value }}</div>
            </slot>
        </div>

        <div v-if="$slots.default" class="mt-1 text-xs text-slate-400">
            <slot />
        </div>
    </div>
</template>

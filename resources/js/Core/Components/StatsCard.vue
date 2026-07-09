<script setup>
/**
 * Core\UI — small numeric stat tile for the widget grid.
 * Modules reuse it for their own counters (bookings today, open
 * deals, …) instead of building bespoke tiles.
 */
defineProps({
    title: { type: String, required: true },
    value: { type: [Number, String], required: true },
    subtitle: String,
});
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="text-sm font-medium text-slate-500">{{ title }}</div>
        <div class="mt-2 text-3xl font-semibold text-slate-900">{{ value }}</div>
        <div v-if="subtitle" class="mt-1 text-xs text-slate-400">{{ subtitle }}</div>
    </div>
</template>

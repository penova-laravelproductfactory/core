<script setup>
/**
 * Core\UI — standard page heading used by every panel page:
 * title + optional subtitle on the left, an #actions slot for
 * buttons on the right. Keeps page tops consistent across Core
 * and future Module pages.
 */
defineProps({
    title: { type: String, required: true },
    subtitle: String,
});
</script>

<template>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-slate-900">{{ title }}</h1>
            <p v-if="subtitle" class="text-sm text-slate-500">{{ subtitle }}</p>
        </div>

        <div v-if="$slots.actions" class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>

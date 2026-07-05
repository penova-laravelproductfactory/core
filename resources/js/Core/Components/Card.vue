<script setup>
/**
 * Core\UI — basic content container: rounded, light shadow, padding.
 * Optional #title slot (or title prop) above the default slot.
 */
defineProps({
    title: String,
});
</script>

<template>
    <div class="rounded-lg bg-white p-6 shadow-sm">
        <div v-if="title || $slots.title" class="mb-4 text-lg font-semibold text-slate-900">
            <slot name="title">{{ title }}</slot>
        </div>

        <slot />
    </div>
</template>

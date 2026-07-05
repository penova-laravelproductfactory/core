<script setup>
/**
 * Core\UI — minimal dialog. Controlled via v-model:show.
 */
defineProps({ show: Boolean, title: String });
const emit = defineEmits(['close']);
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-slate-900/50" @click="emit('close')" />

            <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h2 v-if="title" class="mb-4 text-lg font-semibold text-slate-900">{{ title }}</h2>
                <slot />
            </div>
        </div>
    </Teleport>
</template>

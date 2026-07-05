<script setup>
/**
 * Core\UI — renders Laravel paginator links (the "links" array that
 * LengthAwarePaginator serializes for Inertia).
 */
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, required: true },
});
</script>

<template>
    <nav class="flex flex-wrap gap-1">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                preserve-state
                class="rounded-md px-3 py-1.5 text-sm"
                :class="link.active ? 'bg-brand text-white' : 'text-slate-700 hover:bg-slate-200'"
                v-html="link.label"
            />
            <span v-else class="px-3 py-1.5 text-sm text-slate-400" v-html="link.label" />
        </template>
    </nav>
</template>

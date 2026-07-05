<script setup>
/**
 * Core widget — the user's latest notifications
 * (key "core-recent-notifications"). Full feed: /admin/notifications.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Card from '@/Core/Components/Card.vue';

defineProps({
    widget: Object,
});

const items = computed(() => usePage().props.recentNotifications ?? []);
</script>

<template>
    <Card :title="widget.title">
        <ul v-if="items.length" class="divide-y divide-slate-100">
            <li v-for="item in items" :key="item.id" class="flex items-center justify-between gap-4 py-2.5">
                <span class="text-sm leading-relaxed text-slate-700">{{ item.label }}</span>
                <span class="shrink-0 text-xs text-slate-400">{{ item.time }}</span>
            </li>
        </ul>
        <p v-else class="text-sm leading-relaxed text-slate-400">اعلان جدیدی ندارید.</p>
    </Card>
</template>

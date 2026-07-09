<script setup>
/**
 * Core widget — latest audit-log entries (key "core-recent-activity").
 * The full filterable trail lives at /admin/logs; this is the teaser.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Card from '@/Core/Components/Card.vue';
import { useI18n } from '@/Core/composables/i18n';

defineProps({
    widget: Object,
});

const { t } = useI18n();

const items = computed(() => usePage().props.recentActivity ?? []);
</script>

<template>
    <Card :title="widget.title">
        <ul v-if="items.length" class="divide-y divide-slate-100">
            <li v-for="item in items" :key="item.id" class="flex items-center justify-between gap-4 py-2.5">
                <span class="text-sm leading-relaxed text-slate-700">{{ item.label }}</span>
                <span class="shrink-0 text-xs text-slate-400">{{ item.time }}</span>
            </li>
        </ul>
        <p v-else class="text-sm leading-relaxed text-slate-400">{{ t('widgets.no_activity') }}</p>
    </Card>
</template>

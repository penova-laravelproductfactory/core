<script setup>
/**
 * Core widget — live user/role counters. Registered by
 * PenovaCoreServiceProvider (CORE_WIDGETS, key "core-stats"); data comes
 * from the Workspace page's `stats` prop.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import StatsCard from '@/Core/Components/StatsCard.vue';
import { useI18n } from '@/Core/composables/i18n';

defineProps({
    widget: Object,
});

const { t } = useI18n();

const stats = computed(() => usePage().props.stats ?? {});
</script>

<template>
    <!-- Tiles sit side by side on tablets (full-width widget there) and
         stack at lg+, where the widget occupies one 3-col-grid cell. -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-1">
        <StatsCard :title="t('widgets.users_title')" :value="stats.users_count ?? 0" :subtitle="t('widgets.users_subtitle')" />
        <StatsCard :title="t('widgets.roles_title')" :value="stats.roles_count ?? 0" :subtitle="t('widgets.roles_subtitle')" />
    </div>
</template>

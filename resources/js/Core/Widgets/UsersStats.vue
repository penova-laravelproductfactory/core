<script setup>
/**
 * Core widget — live user/role counters. Registered by
 * PenovaCoreServiceProvider (CORE_WIDGETS, key "core-stats"); data comes
 * from the dashboard page props (DashboardController).
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import StatsCard from '@/Core/Components/StatsCard.vue';

defineProps({
    widget: Object,
});

const stats = computed(() => usePage().props.stats ?? {});
</script>

<template>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <StatsCard title="کاربران" :value="stats.users_count ?? 0" subtitle="حساب‌های ثبت‌شده در پنل" />
        <StatsCard title="نقش‌ها" :value="stats.roles_count ?? 0" subtitle="نقش‌های تعریف‌شده" />
    </div>
</template>

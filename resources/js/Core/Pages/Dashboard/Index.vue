<script setup>
/**
 * Core dashboard — a widget grid, nothing else.
 *
 * The grid is composed from the shared Inertia prop `dashboardWidgets`:
 * descriptor arrays that Core (CORE_WIDGETS in PenovaCoreServiceProvider)
 * and every module's static widgets() hook contribute, merged and
 * order-sorted. DashboardWidget resolves each descriptor's `component`
 * to a Vue file under Core/Widgets or Modules/<Name>/Widgets.
 *
 * Widget DATA is deliberately not part of the descriptor: Lite widgets
 * read the page props below (provided by DashboardController) or the
 * shared props. With no modules registered, only Core's own widgets
 * (stats, activity, notifications, Pro pitch) render.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DashboardWidget from '@/Core/Components/DashboardWidget.vue';

// Consumed by Core widgets via usePage().props — declared here so the
// page's data contract with DashboardController stays visible.
defineProps({
    stats: Object,
    recentActivity: Array,
    recentNotifications: Array,
});

const widgets = computed(() => usePage().props.dashboardWidgets ?? []);
</script>

<template>
    <AdminLayout title="داشبورد">
        <PageHeader title="داشبورد" subtitle="نمای کلی Penova Core Lite" />

        <div class="grid gap-6 lg:grid-cols-2">
            <DashboardWidget
                v-for="widget in widgets"
                :key="widget.key"
                :widget="widget"
                :class="widget.cols === 2 ? 'lg:col-span-2' : ''"
            />
        </div>
    </AdminLayout>
</template>

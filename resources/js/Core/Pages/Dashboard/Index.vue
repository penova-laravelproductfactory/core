<script setup>
/**
 * Core dashboard — a widget grid grouped into headed area sections.
 *
 * Composed from two shared Inertia props:
 *   dashboardWidgets — descriptor arrays that Core (CORE_WIDGETS in
 *     PenovaCoreServiceProvider) and every module's static widgets()
 *     hook contribute, merged, order-sorted, and normalised so 'area'
 *     is always present ('core' by default).
 *   widgetAreas — area key → heading map (config penova.widgets.areas);
 *     keys missing from the map get a label formatted from the key.
 *
 * Sections appear in the order their first widget occurs in the sorted
 * list; inside a section widgets sort by 'order' again (defensive).
 * DashboardWidget resolves each descriptor's `component` to a Vue file
 * under Core/Widgets or Modules/<Name>/Widgets.
 *
 * Widget DATA is deliberately not part of the descriptor: Lite widgets
 * read the page props below (provided by DashboardController) or the
 * shared props, or fetch their own endpoint.
 */
import { computed } from 'vue';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DashboardWidget from '@/Core/Components/DashboardWidget.vue';

const props = defineProps({
    // Shared panel-composition props (HandleInertiaRequests).
    dashboardWidgets: Array,
    widgetAreas: Object, // area → heading label map

    // Page data consumed by Core widgets via usePage().props — declared
    // so the contract with DashboardController stays visible.
    stats: Object,
    recentActivity: Array,
    recentNotifications: Array,
});

// Group widgets by area, keeping each group order-sorted. Key insertion
// order follows the globally sorted list, so the section sequence is
// "wherever each area's first widget lands".
const widgetsByArea = computed(() => {
    const groups = {};

    for (const widget of props.dashboardWidgets ?? []) {
        const area = widget.area || 'core';
        (groups[area] ??= []).push(widget);
    }

    Object.values(groups).forEach((group) => {
        group.sort((a, b) => (a.order ?? 0) - (b.order ?? 0));
    });

    return groups;
});

// Heading for an area: the configured label, or a readable fallback
// built from the key itself ("booking-extras" → "Booking Extras").
function areaLabel(area) {
    if (props.widgetAreas?.[area]) {
        return props.widgetAreas[area];
    }

    return area
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

// Grid density per area: 'core' packs three columns, everything else
// keeps the default two. Presentation-only, so it lives here.
const gridClass = (area) => (area === 'core' ? 'lg:grid-cols-3' : 'lg:grid-cols-2');

// Descriptor cols → span. 'full' spans the whole row whatever the
// area's column count; 2 spans two cells; 1 (default) spans one.
const spanClass = (widget) =>
    widget.cols === 'full' ? 'lg:col-span-full' : widget.cols === 2 ? 'lg:col-span-2' : '';
</script>

<template>
    <AdminLayout title="داشبورد">
        <PageHeader title="داشبورد" subtitle="نمای کلی Penova Core Lite" />

        <div class="space-y-8">
            <section v-for="(widgets, area) in widgetsByArea" :key="area">
                <h2 class="mb-4 text-lg font-semibold text-slate-800">{{ areaLabel(area) }}</h2>

                <div class="grid gap-6" :class="gridClass(area)">
                    <DashboardWidget
                        v-for="widget in widgets"
                        :key="widget.key"
                        :widget="widget"
                        :class="spanClass(widget)"
                    />
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

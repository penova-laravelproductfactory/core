<script setup>
/**
 * Modules\Booking — dashboard widget: how many bookings start today.
 * Registered by BookingServiceProvider::widgets() (key
 * "booking-today-count"); resolved by Core's DashboardWidget from the
 * descriptor's component path "Modules/Booking/Widgets/BookingsTodayCard".
 *
 * Owns its data: fetches the module's JSON endpoint
 * (GET /admin/bookings/today-count → { count }) on mount instead of
 * leaning on dashboard props, so Core stays module-agnostic. "Today" is
 * computed server-side in the app timezone.
 */
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';

// The descriptor is optional: the dashboard passes it, but the widget
// stays self-sufficient (per the widget contract) if rendered bare.
const props = defineProps({
    widget: Object,
});

const title = computed(() => props.widget?.title ?? 'رزروهای امروز');

const count = ref(null);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/admin/bookings/today-count');
        count.value = data.count;
    } catch {
        error.value = 'دریافت آمار رزروها ممکن نشد.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <!-- Same tile anatomy as Core's StatsCard, with loading/error states. -->
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="text-sm font-medium text-slate-500">{{ title }}</div>

        <div v-if="loading" class="mt-2 text-3xl font-semibold text-slate-300">…</div>
        <div v-else-if="error" class="mt-2 text-sm leading-relaxed text-slate-400">{{ error }}</div>
        <div v-else class="mt-2 text-3xl font-semibold text-slate-900">{{ count }}</div>

        <div class="mt-1 text-xs text-slate-400">رزروهایی که امروز شروع می‌شوند</div>
    </div>
</template>

<script setup>
/**
 * Modules\Booking — dashboard widget: today's bookings with a trend
 * indicator vs yesterday. Registered by BookingServiceProvider::widgets()
 * (key "booking-today-count", area "booking").
 *
 * Built on Core's DashboardCard; the base card carries no trend logic,
 * so this widget injects its trend visuals through the #icon and #value
 * slots (the documented extension path).
 *
 * Data: GET /admin/bookings/today-count → { count, yesterday_count }.
 * yesterday_count is optional — without it the trend line hides and the
 * widget renders neutral.
 */
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import DashboardCard from '@/Core/Components/DashboardCard.vue';
import {
    ArrowTrendingDownIcon,
    ArrowTrendingUpIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    widget: Object, // the descriptor; optional so the widget renders bare
});

const title = computed(() => props.widget?.title ?? 'رزروهای امروز');

const count = ref(null);
const previous = ref(null); // yesterday's count; null → no trend shown
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/admin/bookings/today-count');
        count.value = data.count ?? 0;
        previous.value = data.yesterday_count ?? null;
    } catch {
        error.value = 'دریافت آمار رزروها ممکن نشد.';
    } finally {
        loading.value = false;
    }
});

const delta = computed(() => {
    if (count.value == null || previous.value == null) return null;
    return count.value - previous.value;
});

// 'up' | 'down' | 'neutral' — neutral covers delta 0 and missing data.
const trendDirection = computed(() => {
    if (delta.value == null || delta.value === 0) return 'neutral';
    return delta.value > 0 ? 'up' : 'down';
});

// Per-trend styling. Palette: green = growth, orange = drop (soft, not
// alarming — fewer bookings is a signal, not an error), slate = neutral.
const countClasses = computed(() => ({
    up: 'text-green-700',
    down: 'text-orange-700',
    neutral: 'text-slate-900',
})[trendDirection.value]);

const badgeClasses = computed(() => ({
    up: 'bg-green-100 text-green-600',
    down: 'bg-orange-100 text-orange-600',
    neutral: 'bg-slate-100 text-slate-500',
})[trendDirection.value]);

const badgeIcon = computed(() => ({
    up: ArrowTrendingUpIcon,
    down: ArrowTrendingDownIcon,
    neutral: CalendarDaysIcon,
})[trendDirection.value]);
</script>

<template>
    <DashboardCard :title="title" :loading="loading" :error="error">
        <template #icon>
            <span class="inline-flex size-8 items-center justify-center rounded-full" :class="badgeClasses">
                <component :is="badgeIcon" class="size-5" />
            </span>
        </template>

        <template #value>
            <div class="text-3xl font-semibold" :class="countClasses">{{ count }}</div>

            <div v-if="delta !== null" class="mt-1 text-xs" :class="delta === 0 ? 'text-slate-400' : countClasses">
                <span v-if="delta > 0">{{ delta }}+ نسبت به دیروز</span>
                <span v-else-if="delta < 0">{{ Math.abs(delta) }} کمتر از دیروز</span>
                <span v-else>هم‌اندازهٔ دیروز</span>
            </div>
        </template>

        رزروهایی که امروز شروع می‌شوند
    </DashboardCard>
</template>

<script setup>
/**
 * Modules\Booking — dashboard widget: today's bookings with a trend
 * indicator vs yesterday. Registered by BookingServiceProvider::widgets()
 * (key "booking-today-count", area "booking").
 *
 * Data: fetches GET /admin/bookings/today-count on mount; the endpoint
 * returns { count, yesterday_count }. yesterday_count is treated as
 * optional — without it the widget simply hides the trend line and
 * renders neutral, so older/simpler backends keep working.
 */
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import {
    ArrowTrendingDownIcon,
    ArrowTrendingUpIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

// The descriptor is optional: the dashboard passes it, but the widget
// stays self-sufficient (per the widget contract) if rendered bare.
const props = defineProps({
    widget: Object,
});

const title = computed(() => props.widget?.title ?? 'رزروهای امروز');

const count = ref(null);
const previous = ref(null); // yesterday's count; null → no trend shown
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/admin/bookings/today-count');
        count.value = data.count;
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
const cardClasses = computed(() => ({
    up: 'border-green-200',
    down: 'border-orange-200',
    neutral: 'border-slate-200',
})[trendDirection.value]);

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
    <div class="rounded-lg border bg-white p-6" :class="cardClasses">
        <div class="flex items-center justify-between">
            <div class="text-sm font-medium text-slate-500">{{ title }}</div>

            <span class="inline-flex size-8 items-center justify-center rounded-full" :class="badgeClasses">
                <component :is="badgeIcon" class="size-5" />
            </span>
        </div>

        <div v-if="loading" class="mt-2 text-3xl font-semibold text-slate-300">…</div>
        <div v-else-if="error" class="mt-2 text-sm leading-relaxed text-slate-400">{{ error }}</div>
        <template v-else>
            <div class="mt-2 text-3xl font-semibold" :class="countClasses">{{ count }}</div>

            <div v-if="delta !== null" class="mt-1 text-xs" :class="delta === 0 ? 'text-slate-400' : countClasses">
                <span v-if="delta > 0">{{ delta }}+ نسبت به دیروز</span>
                <span v-else-if="delta < 0">{{ Math.abs(delta) }} کمتر از دیروز</span>
                <span v-else>هم‌اندازهٔ دیروز</span>
            </div>
        </template>

        <div class="mt-1 text-xs text-slate-400">رزروهایی که امروز شروع می‌شوند</div>
    </div>
</template>

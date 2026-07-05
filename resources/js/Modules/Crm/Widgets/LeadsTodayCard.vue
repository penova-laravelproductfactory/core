<script setup>
/**
 * Modules\Crm — dashboard widget: how many leads were created today.
 * Registered by CrmServiceProvider::widgets() (key
 * "crm-leads-today-count", area "crm").
 *
 * Fetches GET /admin/leads/today-count on mount; the response is
 * always { count: number }. No trend in this first version — see
 * BookingsTodayCard for the trend-enabled pattern.
 */
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    widget: Object, // the descriptor; optional so the widget renders bare
});

const title = computed(() => props.widget?.title ?? 'سرنخ‌های امروز');

const count = ref(null);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/admin/leads/today-count');
        count.value = data.count;
    } catch {
        error.value = 'دریافت آمار سرنخ‌ها ممکن نشد.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="text-sm font-medium text-slate-500">{{ title }}</div>

        <div v-if="loading" class="mt-2 text-3xl font-semibold text-slate-300">…</div>
        <div v-else-if="error" class="mt-2 text-sm leading-relaxed text-slate-400">{{ error }}</div>
        <div v-else class="mt-2 text-3xl font-semibold text-slate-900">{{ count }}</div>

        <div class="mt-1 text-xs text-slate-400">سرنخ‌های ثبت‌شده در امروز</div>
    </div>
</template>

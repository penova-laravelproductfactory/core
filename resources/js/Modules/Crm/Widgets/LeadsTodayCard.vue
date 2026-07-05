<script setup>
/**
 * Modules\Crm — dashboard widget: how many leads were created today.
 * Registered by CrmServiceProvider::widgets() (key
 * "crm-leads-today-count", area "crm").
 *
 * The props-only DashboardCard case: fetch GET /admin/leads/today-count
 * ({ count }) on mount, hand title/value/loading/error to the base card.
 */
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import DashboardCard from '@/Core/Components/DashboardCard.vue';

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
        count.value = data.count ?? 0;
    } catch {
        error.value = 'دریافت آمار سرنخ‌ها ممکن نشد.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <DashboardCard :title="title" icon="users" :value="count" :loading="loading" :error="error">
        سرنخ‌های ثبت‌شده در امروز
    </DashboardCard>
</template>

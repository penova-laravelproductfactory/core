<script setup>
/**
 * Modules\Store — dashboard widget: how many products are active.
 * Registered by StoreServiceProvider::widgets() (key
 * "store-active-products", area "store").
 *
 * The props-only DashboardCard case: fetch
 * GET /admin/store/products/active-count ({ count }) on mount and hand
 * title/value/loading/error to the base card.
 */
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import DashboardCard from '@/Core/Components/DashboardCard.vue';

const props = defineProps({
    widget: Object, // the descriptor; optional so the widget renders bare
});

const title = computed(() => props.widget?.title ?? 'محصولات فعال');

const count = ref(null);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/admin/store/products/active-count');
        count.value = data.count ?? 0;
    } catch {
        error.value = 'دریافت آمار محصولات ممکن نشد.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <DashboardCard :title="title" icon="bag" :value="count" :loading="loading" :error="error">
        محصولات فعالِ قابل نمایش در فروشگاه
    </DashboardCard>
</template>

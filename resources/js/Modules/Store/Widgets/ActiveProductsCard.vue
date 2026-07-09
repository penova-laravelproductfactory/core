<script setup>
/**
 * Modules\Store — widget: how many products are active.
 * Registered by StoreServiceProvider::widgets() (key
 * "store-active-products", area "store").
 *
 * The props-only WidgetCard case: fetch
 * GET <workspace-prefix>/store/products/active-count ({ count }) on mount and hand
 * title/value/loading/error to the base card.
 */
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import WidgetCard from '@/Core/Components/WidgetCard.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';

const props = defineProps({
    widget: Object, // the descriptor; optional so the widget renders bare
});

const ws = useWorkspacePath();

const title = computed(() => props.widget?.title ?? 'محصولات فعال');

const count = ref(null);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get(ws('/store/products/active-count'));
        count.value = data.count ?? 0;
    } catch {
        error.value = 'دریافت آمار محصولات ممکن نشد.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <WidgetCard :title="title" icon="bag" :value="count" :loading="loading" :error="error">
        محصولات فعالِ قابل نمایش در فروشگاه
    </WidgetCard>
</template>

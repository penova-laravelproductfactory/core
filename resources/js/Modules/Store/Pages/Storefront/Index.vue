<script setup>
/**
 * Modules\Store — the minimal public storefront: active products with
 * add-to-cart. Exists so checkout has a real entry point.
 */
import { router } from '@inertiajs/vue3';
import StorefrontLayout from '@/Modules/Store/Components/StorefrontLayout.vue';
import Pagination from '@/Core/Components/Pagination.vue';

defineProps({
    products: Object, // paginator of active products
    cartCount: Number,
});

const typeLabels = {
    physical: 'فیزیکی',
    virtual: 'مجازی',
    downloadable: 'دانلودی',
};

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');

function addToCart(product) {
    router.post('/store/cart/add', { product_id: product.id }, { preserveScroll: true });
}
</script>

<template>
    <StorefrontLayout title="فروشگاه" :cart-count="cartCount">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">محصولات</h1>

        <div v-if="products.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="product in products.data"
                :key="product.id"
                class="flex flex-col rounded-lg border border-slate-200 bg-white p-4"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="font-semibold text-slate-900">{{ product.name }}</div>
                    <span class="shrink-0 rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                        {{ typeLabels[product.type] ?? product.type }}
                    </span>
                </div>

                <p v-if="product.description" class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-500">
                    {{ product.description }}
                </p>

                <div class="mt-auto flex items-center justify-between pt-4">
                    <div class="font-semibold text-slate-900">{{ formatPrice(product.price) }} تومان</div>
                    <button
                        class="rounded-md bg-brand px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-hover"
                        @click="addToCart(product)"
                    >
                        افزودن به سبد
                    </button>
                </div>
            </div>
        </div>

        <p v-else class="rounded-lg border border-dashed border-slate-300 p-8 text-center text-slate-400">
            فعلاً محصولی برای فروش موجود نیست.
        </p>

        <div class="mt-6">
            <Pagination :links="products.links" />
        </div>
    </StorefrontLayout>
</template>

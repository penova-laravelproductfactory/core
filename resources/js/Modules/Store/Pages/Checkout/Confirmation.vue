<script setup>
/**
 * Modules\Store — guest order confirmation: the number the customer
 * keeps, plus a compact recap.
 */
import StorefrontLayout from '@/Modules/Store/Components/StorefrontLayout.vue';

defineProps({
    order: Object, // { number, customer_name, total, created_at, items[] }
});

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');
</script>

<template>
    <StorefrontLayout title="سفارش ثبت شد">
        <div class="mx-auto max-w-lg rounded-lg border border-green-200 bg-white p-6 text-center">
            <div class="text-4xl">✅</div>
            <h1 class="mt-3 text-xl font-bold text-slate-900">سفارش شما ثبت شد</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                {{ order.customer_name }} عزیز، سفارش شما دریافت شد و به‌زودی برای
                هماهنگی پرداخت و ارسال با شما تماس می‌گیریم.
            </p>

            <div class="mt-4 rounded-md bg-slate-50 p-3">
                <div class="text-xs text-slate-400">شمارهٔ پیگیری سفارش</div>
                <div class="mt-1 font-mono text-lg font-bold text-slate-900" dir="ltr">{{ order.number }}</div>
            </div>

            <ul class="mt-4 divide-y divide-slate-100 text-start">
                <li v-for="item in order.items" :key="item.product_name" class="flex items-center justify-between py-2 text-sm">
                    <span class="text-slate-700">{{ item.product_name }} × {{ item.quantity }}</span>
                    <span class="text-slate-500">{{ formatPrice(item.subtotal) }}</span>
                </li>
            </ul>

            <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                <span class="text-sm font-semibold text-slate-700">جمع کل</span>
                <span class="font-bold text-slate-900">{{ formatPrice(order.total) }} تومان</span>
            </div>
        </div>
    </StorefrontLayout>
</template>

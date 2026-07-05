<script setup>
/**
 * Modules\Store — customer order detail (read-only, v0.1). Summary
 * header, item snapshots, delivery/info. No actions: cancel/reorder need
 * backend pipelines that don't exist yet. Item names/prices are the
 * placement-time snapshot — the order reads as it was bought.
 */
import StorefrontLayout from '@/Modules/Store/Components/StorefrontLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    order: Object,
});

const statusLabels = {
    pending: 'در انتظار',
    confirmed: 'تأییدشده',
    completed: 'تکمیل‌شده',
    cancelled: 'لغوشده',
};

const statusClasses = {
    pending: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-sky-100 text-sky-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');
</script>

<template>
    <StorefrontLayout title="جزئیات سفارش">
        <Link href="/store/account/orders" class="text-sm text-brand hover:underline">→ بازگشت به سفارش‌ها</Link>

        <!-- Summary -->
        <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="font-mono text-lg font-bold text-slate-900" dir="ltr">{{ order.number }}</div>
                    <div class="mt-1 text-xs text-slate-400" dir="ltr">{{ order.created_at }}</div>
                </div>
                <div class="flex gap-2">
                    <span class="rounded px-2 py-0.5 text-xs font-medium" :class="statusClasses[order.status] ?? 'bg-slate-100 text-slate-600'">
                        {{ statusLabels[order.status] ?? order.status }}
                    </span>
                    <span class="rounded px-2 py-0.5 text-xs font-medium" :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'">
                        {{ order.payment_status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">اقلام سفارش</h2>
            <ul class="divide-y divide-slate-100">
                <li v-for="(item, i) in order.items" :key="i" class="flex items-center justify-between py-2 text-sm">
                    <span class="text-slate-700">{{ item.product_name }} × {{ item.quantity }}</span>
                    <span class="text-slate-500">{{ formatPrice(item.subtotal) }} تومان</span>
                </li>
            </ul>
            <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3">
                <span class="text-sm font-semibold text-slate-700">جمع کل</span>
                <span class="font-bold text-slate-900">{{ formatPrice(order.total) }} تومان</span>
            </div>
        </div>

        <!-- Delivery & info -->
        <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4 text-sm">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">اطلاعات ارسال</h2>
            <dl class="space-y-2 text-slate-600">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">گیرنده</dt>
                    <dd class="text-start">{{ order.customer_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">ایمیل</dt>
                    <dd dir="ltr">{{ order.customer_email }}</dd>
                </div>
                <div v-if="order.customer_phone" class="flex justify-between gap-4">
                    <dt class="text-slate-400">تلفن</dt>
                    <dd dir="ltr">{{ order.customer_phone }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-400">نشانی</dt>
                    <dd class="text-start">{{ order.shipping_address }}</dd>
                </div>
                <div v-if="order.notes" class="flex justify-between gap-4">
                    <dt class="text-slate-400">یادداشت</dt>
                    <dd class="text-start">{{ order.notes }}</dd>
                </div>
            </dl>
        </div>
    </StorefrontLayout>
</template>

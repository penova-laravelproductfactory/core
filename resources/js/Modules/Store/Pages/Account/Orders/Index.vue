<script setup>
/**
 * Modules\Store — customer order history overview. Reverse-chronological
 * cards; recognition set only (number, date, status, payment, total,
 * item count) + a details CTA. Badge positions are fixed (status start,
 * payment end) so colour + position scan at a glance. Composes the
 * storefront chrome — customers never see AdminLayout.
 */
import StorefrontLayout from '@/Modules/Store/Components/StorefrontLayout.vue';
import Pagination from '@/Core/Components/Pagination.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    orders: Object, // Laravel paginator: { data, links, meta }
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
    <StorefrontLayout title="سفارش‌های من">
        <h1 class="mb-6 text-xl font-bold text-slate-900">سفارش‌های من</h1>

        <div v-if="orders.data.length" class="space-y-4">
            <article
                v-for="order in orders.data"
                :key="order.number"
                class="rounded-lg border border-slate-200 bg-white p-4"
            >
                <!-- Fixed badge positions: status at start, payment at end. -->
                <div class="flex items-start justify-between">
                    <span
                        class="rounded px-2 py-0.5 text-xs font-medium"
                        :class="statusClasses[order.status] ?? 'bg-slate-100 text-slate-600'"
                    >
                        {{ statusLabels[order.status] ?? order.status }}
                    </span>
                    <span
                        class="rounded px-2 py-0.5 text-xs font-medium"
                        :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                    >
                        {{ order.payment_status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' }}
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="font-mono text-sm font-bold text-slate-900" dir="ltr">{{ order.number }}</div>
                        <div class="mt-1 text-xs text-slate-400" dir="ltr">{{ order.created_at }}</div>
                    </div>
                    <div class="text-sm text-slate-600">{{ order.items_count }} قلم</div>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="text-sm text-slate-700">
                        جمع کل: <span class="font-bold text-slate-900">{{ formatPrice(order.total) }} تومان</span>
                    </span>
                    <Link
                        :href="`/store/account/orders/${order.number}`"
                        class="rounded-md bg-brand px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-hover"
                    >
                        مشاهدهٔ جزئیات
                    </Link>
                </div>
            </article>

            <Pagination :links="orders.links" />
        </div>

        <div v-else class="rounded-lg border border-slate-200 bg-white p-8 text-center">
            <p class="text-slate-500">هنوز سفارشی ثبت نکرده‌اید.</p>
            <Link href="/store" class="mt-3 inline-block text-brand hover:underline">رفتن به فروشگاه</Link>
        </div>
    </StorefrontLayout>
</template>

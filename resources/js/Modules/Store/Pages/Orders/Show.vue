<script setup>
/**
 * Modules\Store — admin order detail: customer block, items snapshot
 * table, totals, and ONLY the two lifecycle actions (status select +
 * payment toggle). Customer data / items / totals are read-only here —
 * an order is a historical record, not editable state.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import Button from '@/Core/Components/Button.vue';

const props = defineProps({
    order: Object,
    statuses: Array, // OrderStatus::values()
});

const statusLabels = {
    pending: 'در انتظار',
    confirmed: 'تأییدشده',
    completed: 'تکمیل‌شده',
    cancelled: 'لغوشده',
};

const form = useForm({
    status: props.order.status,
});

const paymentForm = useForm({
    payment_status: props.order.payment_status,
});

const formatPrice = (price) => Number(price ?? 0).toLocaleString('fa-IR');

function saveStatus() {
    form.put(`/admin/store/orders/${props.order.id}`, { preserveScroll: true });
}

function togglePayment() {
    paymentForm.payment_status = props.order.payment_status === 'paid' ? 'unpaid' : 'paid';
    paymentForm.put(`/admin/store/orders/${props.order.id}`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout :title="`سفارش ${order.number}`">
        <PageHeader title="جزئیات سفارش" :subtitle="order.number">
            <template #actions>
                <Button variant="secondary" href="/admin/store/orders">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Customer + lifecycle -->
            <div class="space-y-6">
                <Card title="مشخصات مشتری">
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-slate-400">نام</dt>
                            <dd class="text-slate-900">{{ order.customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">ایمیل</dt>
                            <dd class="text-slate-900" dir="ltr">{{ order.customer_email }}</dd>
                        </div>
                        <div v-if="order.customer_phone">
                            <dt class="text-slate-400">تلفن</dt>
                            <dd class="text-slate-900" dir="ltr">{{ order.customer_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">آدرس ارسال</dt>
                            <dd class="leading-relaxed text-slate-900">{{ order.shipping_address }}</dd>
                        </div>
                        <div v-if="order.notes">
                            <dt class="text-slate-400">توضیحات مشتری</dt>
                            <dd class="leading-relaxed text-slate-700">{{ order.notes }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400">تاریخ ثبت</dt>
                            <dd class="text-slate-900" dir="ltr">{{ order.created_at }}</dd>
                        </div>
                    </dl>
                </Card>

                <Card title="وضعیت سفارش">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <select
                                v-model="form.status"
                                class="block w-full rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand"
                            >
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ statusLabels[status] ?? status }}
                                </option>
                            </select>
                            <Button :disabled="form.processing || form.status === order.status" @click="saveStatus">
                                ذخیره
                            </Button>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                            <div class="text-sm">
                                <div class="text-slate-400">وضعیت پرداخت</div>
                                <div class="mt-0.5 font-medium" :class="order.payment_status === 'paid' ? 'text-green-700' : 'text-slate-700'">
                                    {{ order.payment_status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' }}
                                </div>
                            </div>

                            <Button
                                :variant="order.payment_status === 'paid' ? 'secondary' : 'primary'"
                                :disabled="paymentForm.processing"
                                @click="togglePayment"
                            >
                                {{ order.payment_status === 'paid' ? 'برگردان به پرداخت‌نشده' : 'علامت‌گذاری: پرداخت شد' }}
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Items snapshot -->
            <div class="lg:col-span-2">
                <Card title="اقلام سفارش">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-start font-medium">محصول</th>
                                    <th class="px-3 py-2 text-start font-medium">قیمت واحد</th>
                                    <th class="px-3 py-2 text-start font-medium">تعداد</th>
                                    <th class="px-3 py-2 text-start font-medium">جمع</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-900">
                                <tr v-for="item in order.items" :key="item.id">
                                    <td class="px-3 py-2.5">{{ item.product_name }}</td>
                                    <td class="px-3 py-2.5">{{ formatPrice(item.price) }}</td>
                                    <td class="px-3 py-2.5">{{ item.quantity }}</td>
                                    <td class="px-3 py-2.5">{{ formatPrice(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200">
                                    <td colspan="3" class="px-3 py-3 text-sm font-semibold text-slate-700">جمع کل</td>
                                    <td class="px-3 py-3 font-bold text-slate-900">{{ formatPrice(order.total) }} تومان</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="mt-3 text-xs leading-relaxed text-slate-400">
                        نام و قیمت اقلام، عکسِ لحظهٔ ثبت سفارش‌اند و با تغییر بعدی محصول
                        عوض نمی‌شوند.
                    </p>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>

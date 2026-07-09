<script setup>
/**
 * Modules\Store — admin orders list on Core's DataTable: search
 * (number / customer name / email), lifecycle filters, sortable
 * number/total/created_at. Row number links to the detail page.
 */
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DataTable from '@/Core/Components/DataTable.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';

const props = defineProps({
    orders: Object, // Laravel LengthAwarePaginator JSON
    filters: Object, // { search, status, payment_status }
});

const ws = useWorkspacePath();

const columns = [
    { key: 'number', label: 'شمارهٔ سفارش', sortable: true },
    { key: 'user', label: 'کاربر' },
    { key: 'customer', label: 'گیرنده (لحظهٔ سفارش)' },
    { key: 'status', label: 'وضعیت' },
    { key: 'payment_status', label: 'پرداخت' },
    { key: 'total', label: 'مبلغ', sortable: true },
    { key: 'created_at', label: 'تاریخ ثبت', sortable: true },
];

const filterStatus = ref(props.filters?.status ?? '');
const filterPayment = ref(props.filters?.payment_status ?? '');

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
    <WorkspaceLayout title="فروشگاه — سفارش‌ها">
        <PageHeader title="سفارش‌ها" subtitle="سفارش‌های ثبت‌شده در فروشگاه" />

        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select
                v-model="filterStatus"
                class="rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand"
            >
                <option value="">همهٔ وضعیت‌ها</option>
                <option value="pending">در انتظار</option>
                <option value="confirmed">تأییدشده</option>
                <option value="completed">تکمیل‌شده</option>
                <option value="cancelled">لغوشده</option>
            </select>

            <select
                v-model="filterPayment"
                class="rounded-md border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand"
            >
                <option value="">وضعیت پرداخت (همه)</option>
                <option value="paid">پرداخت‌شده</option>
                <option value="unpaid">پرداخت‌نشده</option>
            </select>
        </div>

        <DataTable
            :paginator="orders"
            :columns="columns"
            :params="{ status: filterStatus, payment_status: filterPayment }"
            search-placeholder="جستجو در شمارهٔ سفارش، نام یا ایمیل مشتری…"
        >
            <template #cell-number="{ row }">
                <Link :href="ws(`/store/orders/${row.id}`)" class="font-mono font-medium text-brand hover:underline" dir="ltr">
                    {{ row.number }}
                </Link>
            </template>

            <!-- Owning account: quick pivot to the Core user record — the
                 fast path for "show me everything about this customer". -->
            <template #cell-user="{ row }">
                <Link
                    v-if="row.user_id"
                    :href="ws(`/users/${row.user_id}/edit`)"
                    class="text-brand hover:underline"
                >
                    {{ row.user_name }}
                </Link>
                <span v-else class="text-slate-400">—</span>
            </template>

            <template #cell-customer="{ row }">
                <div class="text-slate-900">{{ row.customer_name }}</div>
                <div class="text-xs text-slate-400" dir="ltr">{{ row.customer_email }}</div>
            </template>

            <template #cell-status="{ row }">
                <span class="rounded px-2 py-0.5 text-xs font-medium" :class="statusClasses[row.status] ?? 'bg-slate-100 text-slate-600'">
                    {{ statusLabels[row.status] ?? row.status }}
                </span>
            </template>

            <template #cell-payment_status="{ row }">
                <span
                    class="rounded px-2 py-0.5 text-xs font-medium"
                    :class="row.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                >
                    {{ row.payment_status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' }}
                </span>
            </template>

            <template #cell-total="{ row }">
                {{ formatPrice(row.total) }}
            </template>

            <template #cell-created_at="{ row }">
                <span dir="ltr" class="text-slate-500">{{ row.created_at_human }}</span>
            </template>

            <template #actions="{ row }">
                <Link :href="ws(`/store/orders/${row.id}`)" class="text-brand hover:underline">مشاهده</Link>
            </template>
        </DataTable>
    </WorkspaceLayout>
</template>

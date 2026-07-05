<script setup>
/**
 * Modules\Booking — bookings list, rendered by BookingIndexController
 * via Inertia::render('Modules/Booking/Index').
 *
 * `bookings` is a plain Laravel paginator (data + links); the backend
 * list has no search/sort yet, so this is a simple table + Pagination
 * rather than Core's DataTable (whose search box would silently do
 * nothing here). Swap to DataTable when the controller moves to
 * DataTableBuilder.
 */
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Button from '@/Core/Components/Button.vue';
import Pagination from '@/Core/Components/Pagination.vue';

defineProps({
    bookings: Object, // Laravel LengthAwarePaginator JSON
});

// ISO datetime → "YYYY-MM-DD HH:mm" (plain for now; the Persian
// datepicker/formatter module replaces this later).
const formatDateTime = (iso) => (iso ?? '').slice(0, 16).replace('T', ' ');

const statusLabels = {
    pending: 'در انتظار',
    confirmed: 'تأییدشده',
    cancelled: 'لغوشده',
};

const statusClasses = {
    pending: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};
</script>

<template>
    <AdminLayout title="رزروها">
        <PageHeader title="رزروها" subtitle="مدیریت رزروهای ثبت‌شده">
            <template #actions>
                <Button href="/admin/bookings/create">رزرو جدید</Button>
            </template>
        </PageHeader>

        <div class="space-y-4">
            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold">شناسه</th>
                            <th class="px-4 py-3 text-start font-semibold">نام مشتری</th>
                            <th class="px-4 py-3 text-start font-semibold">زمان شروع</th>
                            <th class="px-4 py-3 text-start font-semibold">وضعیت</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-900">
                        <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-500">{{ booking.id }}</td>
                            <td class="px-4 py-3">{{ booking.customer_name }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ formatDateTime(booking.starts_at) }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="statusClasses[booking.status] ?? 'bg-slate-100 text-slate-600'"
                                >
                                    {{ statusLabels[booking.status] ?? booking.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <Link :href="`/admin/bookings/${booking.id}/edit`" class="text-brand hover:underline">
                                    ویرایش
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="bookings.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                هنوز رزروی ثبت نشده است.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="bookings.links" />
        </div>
    </AdminLayout>
</template>

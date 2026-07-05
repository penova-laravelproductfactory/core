<script setup>
/**
 * Modules\Crm — leads list, rendered by LeadIndexController via
 * Inertia::render('Modules/Crm/Leads/Index'). Same simple-table
 * pattern as the Booking module.
 */
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Button from '@/Core/Components/Button.vue';
import Pagination from '@/Core/Components/Pagination.vue';

defineProps({
    leads: Object, // Laravel LengthAwarePaginator JSON
});

const statusLabels = {
    new: 'جدید',
    contacted: 'تماس‌گرفته‌شده',
    qualified: 'واجد شرایط',
};

const statusClasses = {
    new: 'bg-sky-100 text-sky-700',
    contacted: 'bg-amber-100 text-amber-700',
    qualified: 'bg-green-100 text-green-700',
};

const formatDateTime = (iso) => (iso ?? '').slice(0, 16).replace('T', ' ');
</script>

<template>
    <AdminLayout title="سرنخ‌ها">
        <PageHeader title="سرنخ‌ها" subtitle="مدیریت سرنخ‌های فروش">
            <template #actions>
                <Button href="/admin/leads/create">سرنخ جدید</Button>
            </template>
        </PageHeader>

        <div class="space-y-4">
            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-start font-semibold">نام</th>
                            <th class="px-4 py-3 text-start font-semibold">ایمیل</th>
                            <th class="px-4 py-3 text-start font-semibold">وضعیت</th>
                            <th class="px-4 py-3 text-start font-semibold">تاریخ ثبت</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-900">
                        <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ lead.name }}</td>
                            <td class="px-4 py-3" dir="ltr">{{ lead.email ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="statusClasses[lead.status] ?? 'bg-slate-100 text-slate-600'"
                                >
                                    {{ statusLabels[lead.status] ?? lead.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3" dir="ltr">{{ formatDateTime(lead.created_at) }}</td>
                        </tr>

                        <tr v-if="leads.data.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                هنوز سرنخی ثبت نشده است.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="leads.links" />
        </div>
    </AdminLayout>
</template>

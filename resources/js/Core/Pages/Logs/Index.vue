<script setup>
/**
 * Core\Logs — read-only audit trail.
 */
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DataTable from '@/Core/Components/DataTable.vue';

defineProps({ logs: Object });

const columns = [
    { key: 'created_at', label: 'زمان', sortable: true },
    { key: 'user', label: 'کاربر' },
    { key: 'action', label: 'عملیات', sortable: true },
    { key: 'subject_type', label: 'موضوع' },
];
</script>

<template>
    <WorkspaceLayout title="گزارش فعالیت‌ها">
        <PageHeader title="گزارش فعالیت‌ها" subtitle="چه کسی، چه کاری، چه زمانی" />

        <DataTable :paginator="logs" :columns="columns">
            <template #cell-user="{ row }">
                {{ row.user?.name ?? 'سیستم' }}
            </template>
            <template #cell-subject_type="{ row }">
                <span v-if="row.subject_type" dir="ltr">{{ row.subject_type }} #{{ row.subject_id }}</span>
            </template>
        </DataTable>
    </WorkspaceLayout>
</template>

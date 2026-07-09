<script setup>
/**
 * Core\Logs — read-only audit trail.
 */
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DataTable from '@/Core/Components/DataTable.vue';
import { useI18n } from '@/Core/composables/i18n';

defineProps({ logs: Object });

const { t } = useI18n();

const columns = [
    { key: 'created_at', label: t('logs.col_time'), sortable: true },
    { key: 'user', label: t('logs.col_user') },
    { key: 'action', label: t('logs.col_action'), sortable: true },
    { key: 'subject_type', label: t('logs.col_subject') },
];
</script>

<template>
    <WorkspaceLayout :title="t('logs.title')">
        <PageHeader :title="t('logs.title')" :subtitle="t('logs.subtitle')" />

        <DataTable :paginator="logs" :columns="columns">
            <template #cell-user="{ row }">
                {{ row.user?.name ?? t('logs.system') }}
            </template>
            <template #cell-subject_type="{ row }">
                <span v-if="row.subject_type" dir="ltr">{{ row.subject_type }} #{{ row.subject_id }}</span>
            </template>
        </DataTable>
    </WorkspaceLayout>
</template>

<script setup>
/**
 * Core\Users — user list, the reference implementation of the
 * DataTable pattern every future module table copies.
 */
import { Link, router } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DataTable from '@/Core/Components/DataTable.vue';
import Button from '@/Core/Components/Button.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

defineProps({ users: Object });

const ws = useWorkspacePath();
const { t } = useI18n();

const columns = [
    { key: 'name', label: t('common.name'), sortable: true },
    { key: 'email', label: t('common.email'), sortable: true },
    { key: 'roles', label: t('common.role') },
    { key: 'created_at', label: t('users.col_created'), sortable: true },
];

function destroy(user) {
    if (confirm(t('users.confirm_delete', { name: user.name }))) {
        router.delete(ws(`/users/${user.id}`), { preserveScroll: true });
    }
}
</script>

<template>
    <WorkspaceLayout :title="t('users.title')">
        <PageHeader :title="t('users.title')" :subtitle="t('users.subtitle')">
            <template #actions>
                <Button :href="ws('/users/create')">{{ t('users.new') }}</Button>
            </template>
        </PageHeader>

        <DataTable :paginator="users" :columns="columns">
            <template #cell-roles="{ row }">
                <span
                    v-for="role in row.roles"
                    :key="role.id"
                    class="me-1 rounded bg-brand/10 px-2 py-0.5 text-xs font-medium text-brand"
                >
                    {{ role.name }}
                </span>
            </template>

            <template #actions="{ row }">
                <Link :href="ws(`/users/${row.id}/edit`)" class="me-3 text-brand hover:underline">{{ t('common.edit') }}</Link>
                <button class="text-red-600 hover:underline" @click="destroy(row)">{{ t('common.delete') }}</button>
            </template>
        </DataTable>
    </WorkspaceLayout>
</template>

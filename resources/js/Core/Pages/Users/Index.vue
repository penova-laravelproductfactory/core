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

defineProps({ users: Object });

const ws = useWorkspacePath();

const columns = [
    { key: 'name', label: 'نام', sortable: true },
    { key: 'email', label: 'ایمیل', sortable: true },
    { key: 'roles', label: 'نقش' },
    { key: 'created_at', label: 'تاریخ ایجاد', sortable: true },
];

function destroy(user) {
    if (confirm(`کاربر «${user.name}» حذف شود؟`)) {
        router.delete(ws(`/users/${user.id}`), { preserveScroll: true });
    }
}
</script>

<template>
    <WorkspaceLayout title="کاربران">
        <PageHeader title="کاربران" subtitle="مدیریت حساب‌های کاربری میزکار">
            <template #actions>
                <Button :href="ws('/users/create')">کاربر جدید</Button>
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
                <Link :href="ws(`/users/${row.id}/edit`)" class="me-3 text-brand hover:underline">ویرایش</Link>
                <button class="text-red-600 hover:underline" @click="destroy(row)">حذف</button>
            </template>
        </DataTable>
    </WorkspaceLayout>
</template>

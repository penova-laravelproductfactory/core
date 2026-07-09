<script setup>
/**
 * Core\Roles — role list with inline create/edit in a modal
 * (roles are few; a separate page per role is not worth it).
 */
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import DataTable from '@/Core/Components/DataTable.vue';
import Modal from '@/Core/Components/Modal.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

const props = defineProps({ roles: Object, permissions: Array });

const ws = useWorkspacePath();
const { t } = useI18n();

const columns = [
    { key: 'name', label: t('common.name'), sortable: true },
    { key: 'slug', label: t('roles.col_slug') },
    { key: 'users_count', label: t('roles.col_users_count') },
];

const showModal = ref(false);
const editing = ref(null);

const form = useForm({ name: '', slug: '', permissions: [] });

function openCreate() {
    editing.value = null;
    form.defaults({ name: '', slug: '', permissions: [] });
    form.reset();
    showModal.value = true;
}

function openEdit(role) {
    editing.value = role;
    form.defaults({ name: role.name, slug: role.slug, permissions: role.permissions?.map((p) => p.id) ?? [] });
    form.reset();
    showModal.value = true;
}

function submit() {
    const options = { preserveScroll: true, onSuccess: () => (showModal.value = false) };

    editing.value
        ? form.put(ws(`/roles/${editing.value.id}`), options)
        : form.post(ws('/roles'), options);
}
</script>

<template>
    <WorkspaceLayout :title="t('roles.title')">
        <PageHeader :title="t('roles.title')" :subtitle="t('roles.subtitle')">
            <template #actions>
                <Button @click="openCreate">{{ t('roles.new') }}</Button>
            </template>
        </PageHeader>

        <DataTable :paginator="props.roles" :columns="columns">
            <template #actions="{ row }">
                <button class="text-brand hover:underline" @click="openEdit(row)">{{ t('common.edit') }}</button>
            </template>
        </DataTable>

        <Modal :show="showModal" :title="editing ? t('roles.edit_title') : t('roles.new')" @close="showModal = false">
            <form class="space-y-4" @submit.prevent="submit">
                <TextInput v-model="form.name" :label="t('common.name')" required :error="form.errors.name" />
                <TextInput v-if="!editing" v-model="form.slug" :label="t('roles.col_slug')" required :error="form.errors.slug" />

                <fieldset>
                    <legend class="mb-1 text-sm font-medium text-slate-700">{{ t('roles.permissions_legend') }}</legend>
                    <label
                        v-for="permission in props.permissions"
                        :key="permission.id"
                        class="flex items-center gap-2 text-sm"
                    >
                        <input
                            v-model="form.permissions"
                            type="checkbox"
                            :value="permission.id"
                            class="rounded border-slate-300"
                        />
                        {{ permission.name }}
                        <code class="text-xs text-slate-400" dir="ltr">{{ permission.slug }}</code>
                    </label>
                </fieldset>

                <div class="flex justify-end gap-2">
                    <Button variant="secondary" @click="showModal = false">{{ t('common.cancel') }}</Button>
                    <Button type="submit" :disabled="form.processing">{{ t('common.save') }}</Button>
                </div>
            </form>
        </Modal>
    </WorkspaceLayout>
</template>

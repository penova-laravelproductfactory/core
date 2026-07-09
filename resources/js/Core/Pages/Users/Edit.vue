<script setup>
import { useForm } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

const ws = useWorkspacePath();
const { t } = useI18n();

const props = defineProps({ user: Object, roles: Array });

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles.map((role) => role.id),
});
</script>

<template>
    <WorkspaceLayout :title="t('users.edit_document_title', { name: user.name })">
        <PageHeader :title="t('users.edit_title')" :subtitle="user.email">
            <template #actions>
                <Button variant="secondary" :href="ws('/users')">{{ t('common.back_to_list') }}</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <form class="space-y-4" @submit.prevent="form.put(ws(`/users/${user.id}`))">
                <TextInput v-model="form.name" :label="t('common.name')" required :error="form.errors.name" />
                <TextInput v-model="form.email" :label="t('common.email')" type="email" required :error="form.errors.email" />
                <TextInput v-model="form.password" :label="t('users.password_new')" type="password" :error="form.errors.password" />
                <TextInput v-model="form.password_confirmation" :label="t('users.password_new_confirm')" type="password" />

                <fieldset>
                    <legend class="mb-1 text-sm font-medium text-slate-700">{{ t('users.roles_legend') }}</legend>
                    <label v-for="role in props.roles" :key="role.id" class="flex items-center gap-2 text-sm">
                        <input v-model="form.roles" type="checkbox" :value="role.id" class="rounded border-slate-300" />
                        {{ role.name }}
                    </label>
                </fieldset>

                <Button type="submit" :disabled="form.processing">{{ t('users.save_changes') }}</Button>
            </form>
        </Card>
    </WorkspaceLayout>
</template>

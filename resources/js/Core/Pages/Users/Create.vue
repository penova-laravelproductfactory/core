<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';

const ws = useWorkspacePath();

const props = defineProps({ roles: Array });

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
});
</script>

<template>
    <AdminLayout title="کاربر جدید">
        <PageHeader title="کاربر جدید">
            <template #actions>
                <Button variant="secondary" :href="ws('/users')">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <form class="space-y-4" @submit.prevent="form.post(ws('/users'))">
                <TextInput v-model="form.name" label="نام" required :error="form.errors.name" />
                <TextInput v-model="form.email" label="ایمیل" type="email" required :error="form.errors.email" />
                <TextInput v-model="form.password" label="رمز عبور" type="password" required :error="form.errors.password" />
                <TextInput v-model="form.password_confirmation" label="تکرار رمز عبور" type="password" required />

                <fieldset>
                    <legend class="mb-1 text-sm font-medium text-slate-700">نقش‌ها</legend>
                    <label v-for="role in props.roles" :key="role.id" class="flex items-center gap-2 text-sm">
                        <input v-model="form.roles" type="checkbox" :value="role.id" class="rounded border-slate-300" />
                        {{ role.name }}
                    </label>
                </fieldset>

                <Button type="submit" :disabled="form.processing">ایجاد کاربر</Button>
            </form>
        </Card>
    </AdminLayout>
</template>

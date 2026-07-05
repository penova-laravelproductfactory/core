<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

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
    <AdminLayout :title="`ویرایش کاربر — ${user.name}`">
        <PageHeader title="ویرایش کاربر" :subtitle="user.email">
            <template #actions>
                <Button variant="secondary" href="/admin/users">بازگشت به فهرست</Button>
            </template>
        </PageHeader>

        <Card class="max-w-lg">
            <form class="space-y-4" @submit.prevent="form.put(`/admin/users/${user.id}`)">
                <TextInput v-model="form.name" label="نام" required :error="form.errors.name" />
                <TextInput v-model="form.email" label="ایمیل" type="email" required :error="form.errors.email" />
                <TextInput v-model="form.password" label="رمز عبور جدید (برای حفظ رمز فعلی خالی بگذارید)" type="password" :error="form.errors.password" />
                <TextInput v-model="form.password_confirmation" label="تکرار رمز عبور جدید" type="password" />

                <fieldset>
                    <legend class="mb-1 text-sm font-medium text-slate-700">نقش‌ها</legend>
                    <label v-for="role in props.roles" :key="role.id" class="flex items-center gap-2 text-sm">
                        <input v-model="form.roles" type="checkbox" :value="role.id" class="rounded border-slate-300" />
                        {{ role.name }}
                    </label>
                </fieldset>

                <Button type="submit" :disabled="form.processing">ذخیره تغییرات</Button>
            </form>
        </Card>
    </AdminLayout>
</template>

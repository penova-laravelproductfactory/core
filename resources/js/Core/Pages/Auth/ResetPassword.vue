<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

const props = defineProps({ email: String, token: String });

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <GuestLayout title="تنظیم رمز عبور جدید">
        <h1 class="mb-4 text-center text-lg font-semibold text-slate-800">تنظیم رمز عبور جدید</h1>

        <form class="space-y-4" @submit.prevent="form.post('/reset-password')">
            <TextInput v-model="form.email" label="ایمیل" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" label="رمز عبور جدید" type="password" required :error="form.errors.password" />
            <TextInput v-model="form.password_confirmation" label="تکرار رمز عبور جدید" type="password" required />

            <Button type="submit" :disabled="form.processing">ثبت رمز عبور</Button>
        </form>
    </GuestLayout>
</template>

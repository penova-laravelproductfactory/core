<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <GuestLayout title="ساخت حساب کاربری">
        <h1 class="mb-2 text-center text-lg font-semibold text-slate-800">ساخت حساب کاربری</h1>

        <p class="mb-4 text-sm text-slate-600">
            اگر مدیر سیستم هستید و نیاز به حساب جدید دارید، این فرم را تکمیل کنید.
        </p>

        <form class="space-y-4" @submit.prevent="form.post('/register')">
            <TextInput v-model="form.name" label="نام" required :error="form.errors.name" />
            <TextInput v-model="form.email" label="ایمیل" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" label="رمز عبور" type="password" required :error="form.errors.password" />
            <TextInput v-model="form.password_confirmation" label="تکرار رمز عبور" type="password" required />

            <Button type="submit" :disabled="form.processing" class="w-full">ساخت حساب</Button>

            <div class="text-center text-sm">
                <Link href="/login" class="text-brand hover:underline">قبلاً حساب ساخته‌اید؟ وارد شوید</Link>
            </div>
        </form>
    </GuestLayout>
</template>

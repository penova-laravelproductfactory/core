<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

defineProps({
    canRegister: Boolean,
    // e.g. "Your password has been reset." after the reset flow.
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <GuestLayout title="ورود به پنل مدیریت">
        <h1 class="mb-4 text-center text-lg font-semibold text-slate-800">ورود به پنل مدیریت</h1>

        <p v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="submit">
            <TextInput v-model="form.email" label="ایمیل" type="email" autocomplete="username" required :error="form.errors.email" />
            <TextInput v-model="form.password" label="رمز عبور" type="password" autocomplete="current-password" required :error="form.errors.password" />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input v-model="form.remember" type="checkbox" class="rounded border-slate-300" />
                من را به خاطر بسپار
            </label>

            <Button type="submit" :disabled="form.processing" class="w-full">ورود</Button>

            <div class="flex justify-between text-sm">
                <Link href="/forgot-password" class="text-brand hover:underline">رمز عبور را فراموش کرده‌اید؟</Link>
                <Link v-if="canRegister" href="/register" class="text-brand hover:underline">ساخت حساب کاربری</Link>
            </div>
        </form>
    </GuestLayout>
</template>

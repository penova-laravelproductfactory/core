<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

defineProps({ status: String });

const form = useForm({ email: '' });
</script>

<template>
    <GuestLayout title="بازیابی رمز عبور">
        <h1 class="mb-2 text-center text-lg font-semibold text-slate-800">بازیابی رمز عبور</h1>

        <p class="mb-4 text-sm text-slate-600">
            ایمیل حساب خود را وارد کنید تا لینک بازیابی رمز عبور برایتان ارسال شود.
        </p>

        <p v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="form.post('/forgot-password')">
            <TextInput v-model="form.email" label="ایمیل" type="email" required :error="form.errors.email" />
            <Button type="submit" :disabled="form.processing">ارسال لینک بازیابی</Button>
        </form>
    </GuestLayout>
</template>

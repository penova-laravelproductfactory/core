<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';
import { useI18n } from '@/Core/composables/i18n';

defineProps({
    canRegister: Boolean,
    // e.g. "Your password has been reset." after the reset flow.
    status: String,
});

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <GuestLayout :title="t('auth.login_document_title')">
        <h1 class="mb-4 text-center text-lg font-semibold text-slate-800">{{ t('auth.sign_in') }}</h1>

        <p v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="submit">
            <TextInput v-model="form.email" :label="t('common.email')" type="email" autocomplete="username" required :error="form.errors.email" />
            <TextInput v-model="form.password" :label="t('auth.password')" type="password" autocomplete="current-password" required :error="form.errors.password" />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input v-model="form.remember" type="checkbox" class="rounded border-slate-300" />
                {{ t('auth.remember_me') }}
            </label>

            <Button type="submit" :disabled="form.processing" class="w-full">{{ t('auth.sign_in') }}</Button>

            <div class="flex justify-between text-sm">
                <Link href="/forgot-password" class="text-brand hover:underline">{{ t('auth.forgot_link') }}</Link>
                <Link v-if="canRegister" href="/register" class="text-brand hover:underline">{{ t('auth.register') }}</Link>
            </div>
        </form>
    </GuestLayout>
</template>

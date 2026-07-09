<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';
import { useI18n } from '@/Core/composables/i18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <GuestLayout :title="t('auth.register')">
        <h1 class="mb-2 text-center text-lg font-semibold text-slate-800">{{ t('auth.register') }}</h1>

        <p class="mb-4 text-sm text-slate-600">
            {{ t('auth.register_help') }}
        </p>

        <form class="space-y-4" @submit.prevent="form.post('/register')">
            <TextInput v-model="form.name" :label="t('common.name')" required :error="form.errors.name" />
            <TextInput v-model="form.email" :label="t('common.email')" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" :label="t('auth.password')" type="password" required :error="form.errors.password" />
            <TextInput v-model="form.password_confirmation" :label="t('auth.password_confirm')" type="password" required />

            <Button type="submit" :disabled="form.processing" class="w-full">{{ t('auth.register_submit') }}</Button>

            <div class="text-center text-sm">
                <Link href="/login" class="text-brand hover:underline">{{ t('auth.have_account') }}</Link>
            </div>
        </form>
    </GuestLayout>
</template>

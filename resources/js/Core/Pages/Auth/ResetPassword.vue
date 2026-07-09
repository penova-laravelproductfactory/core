<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';
import { useI18n } from '@/Core/composables/i18n';

const props = defineProps({ email: String, token: String });

const { t } = useI18n();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <GuestLayout :title="t('auth.reset_title')">
        <h1 class="mb-4 text-center text-lg font-semibold text-slate-800">{{ t('auth.reset_title') }}</h1>

        <form class="space-y-4" @submit.prevent="form.post('/reset-password')">
            <TextInput v-model="form.email" :label="t('common.email')" type="email" required :error="form.errors.email" />
            <TextInput v-model="form.password" :label="t('auth.password_new')" type="password" required :error="form.errors.password" />
            <TextInput v-model="form.password_confirmation" :label="t('auth.password_new_confirm')" type="password" required />

            <Button type="submit" :disabled="form.processing">{{ t('auth.reset_submit') }}</Button>
        </form>
    </GuestLayout>
</template>

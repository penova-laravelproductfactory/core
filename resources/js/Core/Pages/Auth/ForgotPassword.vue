<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Core/Layouts/GuestLayout.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';
import { useI18n } from '@/Core/composables/i18n';

defineProps({ status: String });

const { t } = useI18n();

const form = useForm({ email: '' });
</script>

<template>
    <GuestLayout :title="t('auth.forgot_title')">
        <h1 class="mb-2 text-center text-lg font-semibold text-slate-800">{{ t('auth.forgot_title') }}</h1>

        <p class="mb-4 text-sm text-slate-600">
            {{ t('auth.forgot_help') }}
        </p>

        <p v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</p>

        <form class="space-y-4" @submit.prevent="form.post('/forgot-password')">
            <TextInput v-model="form.email" :label="t('common.email')" type="email" required :error="form.errors.email" />
            <Button type="submit" :disabled="form.processing">{{ t('auth.forgot_submit') }}</Button>
        </form>
    </GuestLayout>
</template>

<script setup>
/**
 * Core\Settings — generic key-value editor. Products replace or extend
 * this page with structured groups (mail, branding, ...) as needed.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import Button from '@/Core/Components/Button.vue';

const props = defineProps({ settings: Object });

const form = useForm({
    settings: {
        site_name: props.settings.site_name ?? '',
        contact_email: props.settings.contact_email ?? '',
        ...props.settings,
    },
});
</script>

<template>
    <AdminLayout title="تنظیمات">
        <PageHeader title="تنظیمات" subtitle="پیکربندی سایت، قابل ویرایش توسط مدیران" />

        <Card class="max-w-lg">
            <form class="space-y-4" @submit.prevent="form.put('/admin/settings')">
                <TextInput v-model="form.settings.site_name" label="نام سایت" />
                <TextInput v-model="form.settings.contact_email" label="ایمیل تماس" type="email" />

                <Button type="submit" :disabled="form.processing">ذخیره تنظیمات</Button>
            </form>
        </Card>
    </AdminLayout>
</template>

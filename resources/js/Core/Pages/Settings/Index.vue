<script setup>
/**
 * Core\Settings — generic key-value editor plus the White Label / Branding
 * group. Branding binds to raw DB values (empty when unset); config defaults
 * only surface at the display layer via the shared `branding` prop.
 */
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import TextArea from '@/Core/Components/TextArea.vue';
import Button from '@/Core/Components/Button.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';

const ws = useWorkspacePath();

const props = defineProps({ settings: Object });

const branding = props.settings.branding ?? {};

const form = useForm({
    settings: {
        ...props.settings,
        site_name: props.settings.site_name ?? '',
        contact_email: props.settings.contact_email ?? '',
        branding: {
            name: branding.name ?? '',
            logo_url: branding.logo_url ?? '',
            primary_color: branding.primary_color ?? '',
            footer_text: branding.footer_text ?? '',
        },
    },
});
</script>

<template>
    <AdminLayout title="تنظیمات">
        <PageHeader title="تنظیمات" subtitle="پیکربندی سایت، قابل ویرایش توسط مدیران" />

        <form class="max-w-3xl space-y-6" @submit.prevent="form.put(ws('/settings'))">
            <Card>
                <div class="space-y-4">
                    <TextInput v-model="form.settings.site_name" label="نام سایت" />
                    <TextInput v-model="form.settings.contact_email" label="ایمیل تماس" type="email" />
                </div>
            </Card>

            <Card title="White Label / Branding">
                <p class="mb-4 text-sm text-slate-500">
                    نام برند و برندینگ Core را برای میزکار و صفحهٔ خوش‌آمد تنظیم کنید.
                </p>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <TextInput
                        v-model="form.settings.branding.name"
                        label="Brand name"
                        :error="form.errors['settings.branding.name']"
                    />
                    <TextInput
                        v-model="form.settings.branding.logo_url"
                        label="Logo URL"
                        :error="form.errors['settings.branding.logo_url']"
                    />
                    <TextInput
                        v-model="form.settings.branding.primary_color"
                        label="Primary color (hex)"
                        :error="form.errors['settings.branding.primary_color']"
                    />
                    <div class="md:col-span-2">
                        <TextArea
                            v-model="form.settings.branding.footer_text"
                            label="Footer text"
                            :rows="2"
                            :error="form.errors['settings.branding.footer_text']"
                        />
                    </div>
                </div>
            </Card>

            <Button type="submit" :disabled="form.processing">ذخیره تنظیمات</Button>
        </form>
    </AdminLayout>
</template>

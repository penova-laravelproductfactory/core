<script setup>
/**
 * Core\Settings — generic key-value editor plus the White Label / Branding
 * group. Branding binds to raw DB values (empty when unset); config defaults
 * only surface at the display layer via the shared `branding` prop.
 */
import { useForm } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Core/Layouts/WorkspaceLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import Card from '@/Core/Components/Card.vue';
import TextInput from '@/Core/Components/TextInput.vue';
import TextArea from '@/Core/Components/TextArea.vue';
import Button from '@/Core/Components/Button.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

const ws = useWorkspacePath();
const { t } = useI18n();

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
    <WorkspaceLayout :title="t('settings.title')">
        <PageHeader :title="t('settings.title')" :subtitle="t('settings.subtitle')" />

        <form class="max-w-3xl space-y-6" @submit.prevent="form.put(ws('/settings'))">
            <Card>
                <div class="space-y-4">
                    <TextInput v-model="form.settings.site_name" :label="t('settings.site_name')" />
                    <TextInput v-model="form.settings.contact_email" :label="t('settings.contact_email')" type="email" />
                </div>
            </Card>

            <Card :title="t('settings.branding_card')">
                <p class="mb-4 text-sm text-slate-500">
                    {{ t('settings.branding_help') }}
                </p>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <TextInput
                        v-model="form.settings.branding.name"
                        :label="t('settings.brand_name')"
                        :error="form.errors['settings.branding.name']"
                    />
                    <TextInput
                        v-model="form.settings.branding.logo_url"
                        :label="t('settings.logo_url')"
                        :error="form.errors['settings.branding.logo_url']"
                    />
                    <TextInput
                        v-model="form.settings.branding.primary_color"
                        :label="t('settings.primary_color')"
                        :error="form.errors['settings.branding.primary_color']"
                    />
                    <div class="md:col-span-2">
                        <TextArea
                            v-model="form.settings.branding.footer_text"
                            :label="t('settings.footer_text')"
                            :rows="2"
                            :error="form.errors['settings.branding.footer_text']"
                        />
                    </div>
                </div>
            </Card>

            <Button type="submit" :disabled="form.processing">{{ t('settings.save') }}</Button>
        </form>
    </WorkspaceLayout>
</template>

<script setup>
/**
 * Core\UI — Workspace platform overview. Three counts (users, roles,
 * unread notifications) rendered deliberately small and muted: this is
 * context for orientation, not a metrics dashboard competing with
 * Installed Modules or Platform Health for attention.
 */
import { computed } from 'vue';
import { useI18n } from '@/Core/composables/i18n';

const props = defineProps({ overview: { type: Object, required: true } });

const { t } = useI18n();

const items = computed(() => [
    { key: 'users', label: t('home.overview_users'), value: props.overview.users },
    { key: 'roles', label: t('home.overview_roles'), value: props.overview.roles },
    { key: 'unread', label: t('home.overview_unread'), value: props.overview.unread },
]);
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ t('home.overview_heading') }}</p>

        <dl class="mt-3 flex items-start gap-6">
            <div
                v-for="(item, i) in items"
                :key="item.key"
                class="min-w-0"
                :class="i > 0 ? 'border-s border-slate-100 ps-6' : ''"
            >
                <dt class="text-xs text-slate-400">{{ item.label }}</dt>
                <dd class="mt-0.5 text-base font-semibold text-slate-500">{{ item.value }}</dd>
            </div>
        </dl>
    </section>
</template>

<script setup>
/**
 * Core\UI — Workspace platform snapshot. Three counts (users, roles,
 * unread notifications) rendered deliberately small and muted: this is
 * context for orientation, not a metrics dashboard competing with
 * Installed Modules or Platform Health for attention.
 */
import { computed } from 'vue';

const props = defineProps({ snapshot: { type: Object, required: true } });

const items = computed(() => [
    { key: 'users', label: 'Users', value: props.snapshot.users },
    { key: 'roles', label: 'Roles', value: props.snapshot.roles },
    { key: 'unread', label: 'Unread Notifications', value: props.snapshot.unread },
]);
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Snapshot</p>

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

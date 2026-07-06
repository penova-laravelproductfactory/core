<script setup>
/**
 * Core Workspace — the post-install Control Center at /admin. Composed of
 * focused section components driven by the single `platform` prop. Navigation
 * stays in AdminLayout's shared `menu` prop, not here.
 *
 * Task 5 ships the top of the page (hero, setup progress, quick actions);
 * Task 6 adds installed modules, snapshot, health and What's New below.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import WorkspaceHero from '@/Core/Components/Workspace/WorkspaceHero.vue';
import SetupProgress from '@/Core/Components/Workspace/SetupProgress.vue';
import QuickActions from '@/Core/Components/Workspace/QuickActions.vue';

const platform = computed(() => usePage().props.platform);
</script>

<template>
    <AdminLayout title="میزکار">
        <PageHeader title="میزکار" subtitle="مرکز کنترل پلتفرم Penova" />

        <div class="space-y-8">
            <WorkspaceHero :platform="platform" />
            <SetupProgress :onboarding="platform.onboarding" />
            <QuickActions :links="platform.links" />
        </div>
    </AdminLayout>
</template>

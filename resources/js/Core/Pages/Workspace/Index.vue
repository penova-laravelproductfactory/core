<script setup>
/**
 * Core Workspace — the post-install Control Center at /admin. Composed of
 * focused section components driven by the single `platform` prop. Navigation
 * stays in AdminLayout's shared `menu` prop, not here.
 *
 * Task 5 ships the top of the page (hero, setup progress, get-started actions);
 * Task 6 adds installed modules, overview, health and What's New below.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Core/Layouts/AdminLayout.vue';
import PageHeader from '@/Core/Components/PageHeader.vue';
import WorkspaceHero from '@/Core/Components/Workspace/WorkspaceHero.vue';
import SetupProgress from '@/Core/Components/Workspace/SetupProgress.vue';
import GetStartedActions from '@/Core/Components/Workspace/GetStartedActions.vue';
import InstalledModules from '@/Core/Components/Workspace/InstalledModules.vue';
import PlatformOverview from '@/Core/Components/Workspace/PlatformOverview.vue';
import PlatformHealth from '@/Core/Components/Workspace/PlatformHealth.vue';
import BrandingReminder from '@/Core/Components/Workspace/BrandingReminder.vue';
import WhatsNew from '@/Core/Components/Workspace/WhatsNew.vue';

const platform = computed(() => usePage().props.platform);
</script>

<template>
    <AdminLayout title="میزکار">
        <PageHeader title="میزکار" subtitle="مرکز کنترل پلتفرم Penova" />

        <div class="space-y-8">
            <WorkspaceHero :platform="platform" />
            <BrandingReminder :branding-configured="platform.brandingConfigured" />
            <SetupProgress :onboarding="platform.onboarding" />
            <GetStartedActions :links="platform.links" />
            <InstalledModules :modules="platform.modules" />

            <div class="grid items-start gap-6 lg:grid-cols-2">
                <PlatformOverview :overview="platform.overview" />
                <PlatformHealth :health="platform.health" />
            </div>

            <WhatsNew :whats-new="platform.whatsNew" />
        </div>
    </AdminLayout>
</template>

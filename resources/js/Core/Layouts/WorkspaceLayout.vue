<script setup>
/**
 * Core\UI — the shared Workspace shell.
 *
 * Structure (RTL: <html dir="rtl"> flips flex automatically, so the
 * sidebar — first in the DOM — sits on the RIGHT):
 *   dark sidebar (brand + iconed nav) | white topbar / slate content
 *
 * The sidebar is data-driven: items come from the shared Inertia prop
 * `menu` (Core items + every module's menu() hook, merged and
 * order-sorted by PenovaCoreServiceProvider, hrefs resolved by
 * HandleInertiaRequests). Modules never fork this file — they add a
 * menu entry from their service provider.
 *
 * Page anatomy (Core pages and Module pages alike):
 *
 *   <WorkspaceLayout title="کاربران">
 *       <PageHeader title="کاربران"> … </PageHeader>
 *       …content…
 *   </WorkspaceLayout>
 */
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BellIcon,
    CalendarIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    Cog6ToothIcon,
    HomeIcon,
    ShieldCheckIcon,
    ShoppingBagIcon,
    SparklesIcon,
    Squares2X2Icon,
    UsersIcon,
} from '@heroicons/vue/24/outline';
import Toast from '@/Core/Components/Toast.vue';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

defineProps({
    // Optional document title; pages may also render <Head> themselves.
    title: String,
});

const page = usePage();
const ws = useWorkspacePath();
const { t } = useI18n();

const user = computed(() => page.props.auth.user);
const unread = computed(() => page.props.unreadNotifications);
const branding = computed(() => page.props.branding ?? {});

const userMenuOpen = ref(false);

// Sidebar items — the shared prop described in the docblock above.
const navigation = computed(() => page.props.menu ?? []);

// Menu descriptors carry a string icon key (backend stays Vue-agnostic);
// this map turns it into a Heroicon. Extend it when a module needs a new
// key; unknown keys fall back to a neutral squares icon.
const icons = {
    home: HomeIcon,
    users: UsersIcon,
    shield: ShieldCheckIcon,
    cog: Cog6ToothIcon,
    clock: ClockIcon,
    bell: BellIcon,
    calendar: CalendarIcon,
    bag: ShoppingBagIcon,
    clipboard: ClipboardDocumentListIcon,
    sparkles: SparklesIcon,
    squares: Squares2X2Icon,
};

const iconFor = (item) => icons[item.icon] ?? Squares2X2Icon;

// Active when the current URL is the item itself or nested under it.
// The workspace href is the panel root, so it needs an exact match.
const isActive = (item) => {
    const path = page.url.split('?')[0];

    return item.key === 'workspace' ? path === item.href : path.startsWith(item.href);
};
</script>

<template>
    <Head v-if="title" :title="title" />

    <div class="flex min-h-screen bg-slate-50">
        <!-- Sidebar (renders on the right in RTL) -->
        <aside class="flex w-64 flex-col bg-slate-900 text-slate-100">
            <div class="flex h-16 items-center gap-2 px-6">
                <img
                    v-if="branding.logo_url"
                    :src="branding.logo_url"
                    alt=""
                    class="h-8 w-8 shrink-0 rounded"
                />
                <div class="min-w-0">
                    <div class="truncate text-lg font-bold tracking-wide">
                        {{ branding.name || 'Penova Core' }}
                    </div>
                    <div class="text-xs text-slate-400">{{ t('shell.workspace_subtitle') }}</div>
                </div>
            </div>

            <nav class="flex-1 space-y-2 px-3 py-5">
                <Link
                    v-for="item in navigation"
                    :key="item.key"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-md border-s-4 px-3 py-2.5 text-sm font-medium transition-colors"
                    :class="isActive(item)
                        ? 'border-brand bg-slate-800 text-white'
                        : 'border-transparent text-slate-300 hover:border-brand/40 hover:bg-slate-800/60 hover:text-white'"
                >
                    <component :is="iconFor(item)" class="size-5 shrink-0" />
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <!-- Main column -->
        <div class="flex flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6">
                <!-- Optional topbar title; most pages use <PageHeader> instead. -->
                <div class="text-sm font-medium text-slate-500">
                    <slot name="title" />
                </div>

                <div class="flex items-center gap-5">
                    <!-- Notification bell -->
                    <Link :href="ws('/notifications')" class="relative text-slate-500 hover:text-slate-700">
                        <BellIcon class="size-6" />
                        <span
                            v-if="unread > 0"
                            class="absolute -inset-e-2 -top-2 rounded-full bg-brand px-1.5 text-xs font-bold text-white"
                        >
                            {{ unread }}
                        </span>
                    </Link>

                    <!-- User menu -->
                    <div class="relative">
                        <button
                            class="flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            {{ user?.name }}
                            <span class="text-xs text-slate-400">▾</span>
                        </button>

                        <div
                            v-if="userMenuOpen"
                            class="absolute inset-e-0 z-20 mt-2 w-40 rounded-md border border-slate-100 bg-white py-1 shadow-lg"
                            @click="userMenuOpen = false"
                        >
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="block w-full px-4 py-2 text-start text-sm text-slate-700 hover:bg-slate-50"
                            >
                                {{ t('common.logout') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6">
                <slot />
            </main>

            <footer
                v-if="branding.footer_text"
                class="border-t border-slate-200 bg-white px-6 py-3 text-center text-xs text-slate-400"
            >
                {{ branding.footer_text }}
            </footer>
        </div>

        <Toast />
    </div>
</template>

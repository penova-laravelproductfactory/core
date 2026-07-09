<script setup>
/**
 * Core\UI — Workspace get-started actions. Five forward-facing shortcuts to the
 * moves that actually get a product shipped (scaffold, brand, ship users,
 * read docs). Not a nav duplicate: WorkspaceLayout's sidebar covers the panel,
 * this covers "what to do next" for someone who just installed Core.
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    PuzzlePieceIcon,
    SwatchIcon,
    RectangleStackIcon,
    UserGroupIcon,
    BookOpenIcon,
    ArrowTopRightOnSquareIcon,
    ChevronLeftIcon,
} from '@heroicons/vue/24/outline';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

const props = defineProps({ links: { type: Object, required: true } });

const ws = useWorkspacePath();
const { t } = useI18n();

const cards = computed(() => [
    {
        key: 'first-module',
        icon: PuzzlePieceIcon,
        title: t('home.gs_module_title'),
        description: t('home.gs_module_desc'),
        href: props.links.documentation,
    },
    {
        key: 'branding',
        icon: SwatchIcon,
        title: t('home.configure_branding'),
        description: t('home.gs_branding_desc'),
        href: ws('/settings'),
    },
    {
        key: 'first-resource',
        icon: RectangleStackIcon,
        title: t('home.gs_resource_title'),
        description: t('home.gs_resource_desc'),
        href: props.links.documentation,
    },
    {
        key: 'users-roles',
        icon: UserGroupIcon,
        title: t('home.gs_users_title'),
        description: t('home.gs_users_desc'),
        href: ws('/users'),
    },
    {
        key: 'documentation',
        icon: BookOpenIcon,
        title: t('home.gs_docs_title'),
        description: t('home.gs_docs_desc'),
        href: props.links.documentation,
    },
].map((card) => ({ ...card, internal: card.href.startsWith('/') })));
</script>

<template>
    <section>
        <h2 class="text-lg font-bold text-slate-900">{{ t('home.get_started') }}</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <component
                :is="card.internal ? Link : 'a'"
                v-for="card in cards"
                :key="card.key"
                :href="card.href"
                v-bind="card.internal ? {} : { target: '_blank', rel: 'noopener' }"
                class="group relative flex flex-col rounded-2xl border border-slate-200 bg-white p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-brand/30 hover:shadow-lg hover:shadow-slate-200/60"
            >
                <div class="flex items-start justify-between">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <component :is="card.icon" class="size-5" />
                    </span>
                    <component
                        :is="card.internal ? ChevronLeftIcon : ArrowTopRightOnSquareIcon"
                        class="size-4 text-slate-300 transition-colors group-hover:text-brand"
                    />
                </div>

                <h3 class="mt-4 text-sm font-bold text-slate-900">{{ card.title }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ card.description }}</p>
            </component>
        </div>
    </section>
</template>

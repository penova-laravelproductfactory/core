<script setup>
/**
 * Core\UI — the one button. Variants cover the whole panel; Modules
 * use this instead of styling their own.
 *
 * With `href` it renders as an Inertia <Link> styled identically,
 * so "button that navigates" needs no wrapper:
 *   <Button href="/admin/users/create">کاربر جدید</Button>
 *
 * Brand color comes from the --color-brand token in resources/css/app.css.
 */
import { Link } from '@inertiajs/vue3';

defineProps({
    variant: { type: String, default: 'primary' }, // primary | secondary | danger | link
    type: { type: String, default: 'button' },
    disabled: Boolean,
    href: String,
});

const variants = {
    primary: 'bg-brand text-white shadow-sm hover:bg-brand-hover',
    secondary: 'bg-white text-brand shadow-sm ring-1 ring-inset ring-brand hover:bg-brand/5',
    danger: 'bg-red-600 text-white shadow-sm hover:bg-red-500',
    // Text-only action: brand colored, no background.
    link: 'text-brand hover:underline',
};

const base = 'inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
</script>

<template>
    <Link v-if="href" :href="href" :class="[base, variants[variant]]">
        <slot />
    </Link>

    <button v-else :type="type" :disabled="disabled" :class="[base, variants[variant]]">
        <slot />
    </button>
</template>

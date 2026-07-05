<script setup>
/**
 * Modules\Store — minimal public (guest) layout for the storefront and
 * checkout pages. Deliberately NOT AdminLayout: no sidebar, no auth
 * chrome — just a brand bar with the cart shortcut. Products replace
 * this with a themed storefront later.
 */
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: String,
    cartCount: { type: Number, default: 0 },
});

const appName = computed(() => usePage().props.app.name);

const user = computed(() => usePage().props.auth?.user);

// Extensible account menu — future entries (پروفایل، آدرس‌ها) slot in
// here without touching the template.
const accountLinks = [
    { label: 'سفارش‌های من', href: '/store/account/orders' },
];
</script>

<template>
    <Head v-if="title" :title="title" />

    <div class="flex min-h-screen flex-col bg-slate-100">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-4xl items-center justify-between px-4">
                <Link href="/store" class="text-lg font-bold text-slate-900">
                    فروشگاه {{ appName }}
                </Link>

                <nav class="flex items-center gap-4 text-sm">
                    <template v-if="user">
                        <Link
                            v-for="link in accountLinks"
                            :key="link.href"
                            :href="link.href"
                            class="font-medium text-slate-600 hover:text-slate-900"
                        >
                            {{ link.label }}
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="text-slate-400 hover:text-slate-700"
                        >
                            خروج
                        </Link>
                    </template>

                    <Link
                        href="/store/checkout"
                        class="rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white hover:bg-brand-hover"
                        :class="{ 'pointer-events-none opacity-40': cartCount === 0 }"
                    >
                        تسویه حساب ({{ cartCount }})
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-8">
            <slot />
        </main>

        <footer class="py-6 text-center text-xs text-slate-400">
            © {{ appName }}
        </footer>
    </div>
</template>

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
</script>

<template>
    <Head v-if="title" :title="title" />

    <div class="flex min-h-screen flex-col bg-slate-100">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-4xl items-center justify-between px-4">
                <Link href="/store" class="text-lg font-bold text-slate-900">
                    فروشگاه {{ appName }}
                </Link>

                <Link
                    href="/store/checkout"
                    class="rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white hover:bg-brand-hover"
                    :class="{ 'pointer-events-none opacity-40': cartCount === 0 }"
                >
                    تسویه حساب ({{ cartCount }})
                </Link>
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

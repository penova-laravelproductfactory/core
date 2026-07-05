<script setup>
/**
 * Core\UI — renders flash messages shared by HandleInertiaRequests
 * ("flash.success" / "flash.error") as auto-dismissing toasts.
 * Rendered once by AdminLayout; pages just redirect back()->with(...).
 */
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);

const flash = computed(() => page.props.flash ?? {});
const message = computed(() => flash.value.success ?? flash.value.error);
const isError = computed(() => Boolean(flash.value.error));

watch(message, (value) => {
    if (!value) return;
    visible.value = true;
    setTimeout(() => (visible.value = false), 4000);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200"
        enter-from-class="translate-y-2 opacity-0"
        leave-active-class="transition duration-200"
        leave-to-class="opacity-0"
    >
        <div
            v-if="visible && message"
            class="fixed bottom-6 inset-e-6 z-50 rounded-md px-4 py-3 text-sm font-medium text-white shadow-lg"
            :class="isError ? 'bg-red-600' : 'bg-green-600'"
        >
            {{ message }}
        </div>
    </Transition>
</template>

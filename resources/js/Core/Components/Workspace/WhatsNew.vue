<script setup>
/**
 * Core\UI — Workspace What's New. Reads config('penova.changelog')[0]
 * (Task 4) — null-safe, since a fresh install may ship no changelog yet.
 * Dismissal is per-version so the next release re-surfaces automatically.
 */
import { computed, ref } from 'vue';
import { SparklesIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ whatsNew: { type: Object, default: null } });

const key = computed(() => (props.whatsNew ? `penova.dismiss.whatsnew.${props.whatsNew.version}` : null));
const dismissed = ref(key.value ? localStorage.getItem(key.value) === '1' : false);

const dismiss = () => {
    if (!key.value) return;
    localStorage.setItem(key.value, '1');
    dismissed.value = true;
};
</script>

<template>
    <section
        v-if="whatsNew && !dismissed"
        class="relative rounded-2xl border border-slate-200 bg-white p-5 sm:p-6"
    >
        <button
            type="button"
            class="absolute inset-e-3 top-3 rounded-lg p-1 text-slate-300 transition-colors hover:bg-slate-100 hover:text-slate-500"
            aria-label="بستن"
            @click="dismiss"
        >
            <XMarkIcon class="size-4" />
        </button>

        <div class="flex items-start gap-3 pe-8">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                <SparklesIcon class="size-5" />
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-900">What's New in v{{ whatsNew.version }}</h2>
                    <span class="text-xs text-slate-400">{{ whatsNew.date }}</span>
                </div>
                <ul class="mt-2 space-y-1">
                    <li
                        v-for="highlight in whatsNew.highlights"
                        :key="highlight"
                        class="flex items-start gap-2 text-sm text-slate-600"
                    >
                        <span class="mt-2 size-1 shrink-0 rounded-full bg-slate-300" aria-hidden="true" />
                        {{ highlight }}
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>

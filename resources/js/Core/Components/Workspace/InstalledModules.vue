<script setup>
/**
 * Core\UI — Workspace installed modules. Renders whatever ManifestRegistry
 * found in config('penova.modules') (Task 1/4) — no module names are
 * hardcoded here. Empty install (Core with no business module yet)
 * gets an inviting empty state instead of a blank grid.
 */
import { PuzzlePieceIcon } from '@heroicons/vue/24/outline';

defineProps({ modules: { type: Array, required: true } });
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-slate-900">Installed Modules</h2>
            <span v-if="modules.length" class="text-sm font-semibold text-slate-500">{{ modules.length }}</span>
        </div>

        <div v-if="modules.length" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="m in modules"
                :key="m.key"
                class="rounded-xl border border-slate-200 p-5 transition-colors hover:border-brand/30"
            >
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                        <PuzzlePieceIcon class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="truncate font-bold text-slate-900">{{ m.name }}</h3>
                            <span class="shrink-0 rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">v{{ m.version }}</span>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-sm leading-relaxed text-slate-500">{{ m.description }}</p>

                <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                    <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true" />
                    Ready
                </span>
            </article>
        </div>

        <div v-else class="mt-5 flex flex-col items-center rounded-xl border border-dashed border-slate-300 p-10 text-center">
            <span class="flex size-12 items-center justify-center rounded-full bg-slate-50 text-slate-300">
                <PuzzlePieceIcon class="size-6" />
            </span>
            <p class="mt-4 text-base font-bold text-slate-900">Your platform is ready.</p>
            <p class="mt-1 max-w-sm text-sm text-slate-500">Install your first business module to turn Penova Core into a real product.</p>
        </div>
    </section>
</template>

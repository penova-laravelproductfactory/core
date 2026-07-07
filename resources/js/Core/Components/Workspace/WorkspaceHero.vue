<script setup>
/**
 * Core\UI — Workspace hero. First thing an admin sees after install: the
 * product's name, current version, and the three outbound links that keep
 * them oriented (docs / source / release notes). Status only — no actions
 * live here, that's SetupProgress and GetStartedActions below.
 */
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';

defineProps({ platform: { type: Object, required: true } });
</script>

<template>
    <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-8 sm:p-10">
        <!-- Signature: a soft brand glow anchored to the hero's leading edge (RTL: visually top-left). -->
        <div
            class="pointer-events-none absolute -top-24 -start-24 size-72 rounded-full bg-brand/10 blur-3xl"
            aria-hidden="true"
        />

        <div class="relative">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    Welcome to Penova Core
                </h1>
                <span class="rounded-full bg-brand/10 px-3 py-1 text-xs font-bold tracking-wide text-brand">
                    v{{ platform.version }}
                </span>
            </div>

            <p class="mt-3 max-w-2xl text-base leading-relaxed text-slate-600">
                Start your next Laravel product in minutes instead of weeks.
            </p>

            <div class="mt-7 flex flex-wrap gap-2 text-sm font-semibold">
                <a
                    :href="platform.links.documentation"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-slate-700 transition-colors hover:bg-slate-50 hover:text-brand"
                >
                    Documentation
                    <ArrowTopRightOnSquareIcon class="size-4 text-slate-400" />
                </a>
                <a
                    :href="platform.links.github"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-slate-700 transition-colors hover:bg-slate-50 hover:text-brand"
                >
                    GitHub
                    <ArrowTopRightOnSquareIcon class="size-4 text-slate-400" />
                </a>
                <a
                    :href="platform.links.release_notes"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-slate-700 transition-colors hover:bg-slate-50 hover:text-brand"
                >
                    Release Notes
                    <ArrowTopRightOnSquareIcon class="size-4 text-slate-400" />
                </a>
            </div>
        </div>
    </section>
</template>

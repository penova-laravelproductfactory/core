<script setup>
/**
 * Core\UI — Workspace setup progress. Turns the onboarding view-model
 * (Task 4) into a checklist an admin can act on directly: incomplete
 * steps carry their own CTA, done steps just confirm. "Keep building"
 * guidance below has no done/undone state — it's always-on forward
 * suggestions once the checklist is clear.
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CheckIcon, ChevronLeftIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ onboarding: { type: Object, required: true } });

const done = computed(() => props.onboarding.steps.filter((s) => s.done).length);
const total = computed(() => props.onboarding.steps.length);
const percent = computed(() => Math.round((done.value / total.value) * 100));
const isInternal = (href) => href?.startsWith('/');
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-slate-900">راه‌اندازی پلتفرم</h2>
            <span class="text-sm font-semibold text-slate-500">{{ done }}/{{ total }}</span>
        </div>

        <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
            <div
                class="h-full rounded-full bg-brand transition-all duration-500"
                :style="{ width: percent + '%' }"
            />
        </div>

        <ul class="mt-6 space-y-1">
            <li
                v-for="step in onboarding.steps"
                :key="step.key"
                class="flex flex-wrap items-center justify-between gap-3 rounded-lg px-3 py-2.5"
                :class="step.done ? '' : 'hover:bg-slate-50'"
            >
                <span class="flex items-center gap-3 text-sm" :class="step.done ? 'text-slate-500' : 'font-medium text-slate-800'">
                    <span
                        class="flex size-5 shrink-0 items-center justify-center rounded-full"
                        :class="step.done ? 'bg-emerald-500 text-white' : 'ring-2 ring-slate-200'"
                    >
                        <CheckIcon v-if="step.done" class="size-3.5" />
                    </span>
                    {{ step.label }}
                </span>
                <template v-if="!step.done && step.cta">
                    <Link
                        v-if="isInternal(step.cta.href)"
                        :href="step.cta.href"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-brand hover:text-brand-hover"
                    >
                        {{ step.cta.label }}
                        <ChevronLeftIcon class="size-4" />
                    </Link>
                    <a
                        v-else
                        :href="step.cta.href"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-brand hover:text-brand-hover"
                    >
                        {{ step.cta.label }}
                        <ArrowTopRightOnSquareIcon class="size-4" />
                    </a>
                </template>
            </li>
        </ul>

        <div class="mt-6 border-t border-slate-100 pt-5">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Keep building</p>
            <ul class="space-y-1">
                <li
                    v-for="g in onboarding.guidance"
                    :key="g.key"
                    class="flex items-start justify-between gap-4 rounded-lg px-3 py-2.5 hover:bg-slate-50"
                >
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ g.label }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ g.description }}</p>
                    </div>
                    <a
                        :href="g.cta.href"
                        target="_blank"
                        rel="noopener"
                        class="mt-0.5 inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-brand hover:text-brand-hover"
                    >
                        {{ g.cta.label }}
                        <ArrowTopRightOnSquareIcon class="size-4" />
                    </a>
                </li>
            </ul>
        </div>
    </section>
</template>

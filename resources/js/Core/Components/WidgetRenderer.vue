<script setup>
/**
 * Core\UI — renders one widget from its descriptor.
 *
 * Resolution (RFC-006 / D-028):
 *   Core widgets   → their own `component` ("Core/Widgets/X") against Core's glob.
 *   Module widgets → ONLY through the generated module-frontend registry, keyed by
 *     the widget's globally-unique `key`. Core keeps NO module-directory glob and
 *     no `component` path for Module widgets; the registry (a git-ignored build
 *     artifact — registry output) is the only resolver, and Core names no Module.
 *     The declared contract is the Manifest `frontend` section.
 *
 * A validly-registered widget whose component later fails to load fails SOFT
 * (visible placeholder). Registration errors are caught at generation, never here.
 */
import { computed, defineAsyncComponent } from 'vue';
import { useI18n } from '@/Core/composables/i18n';
import { moduleWidgets } from '@/generated/module-frontend-registry.js';

const props = defineProps({
    widget: { type: Object, required: true },
});

const { t } = useI18n();

const coreWidgets = import.meta.glob('../Widgets/**/*.vue');

const loader = computed(() => {
    const { key, component } = props.widget;

    // Module widget: resolved by key from the registry (registry output).
    if (moduleWidgets[key]) {
        return moduleWidgets[key];
    }

    // Core widget: its own "Core/Widgets/X" component against Core's own glob.
    if (typeof component === 'string' && component.startsWith('Core/')) {
        return coreWidgets[`../${component.replace(/^Core\//, '')}.vue`];
    }

    return null;
});

const resolved = computed(() => (loader.value ? defineAsyncComponent(loader.value) : null));
</script>

<template>
    <component :is="resolved" v-if="resolved" :widget="widget" />

    <!-- A validly-registered widget whose component fails to load: fail soft and
         visibly, so a runtime miss never blanks the widget grid. -->
    <div v-else class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-400">
        {{ t('render.widget_missing_before') }}<span dir="ltr">{{ widget.key }}</span>{{ t('render.widget_missing_after') }}
    </div>
</template>

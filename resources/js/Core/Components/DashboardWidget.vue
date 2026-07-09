<script setup>
/**
 * Core\UI — renders one dashboard widget from its descriptor (the shape
 * documented in app/Core/Support/PenovaModule.php).
 *
 * `component` in the descriptor is a path-like name resolved against the
 * two widget roots Vite knows about at build time:
 *   Core/Widgets/X          → resources/js/Core/Widgets/X.vue
 *   Modules/<Name>/Widgets/X → resources/js/Modules/<Name>/Widgets/X.vue
 *
 * Every widget component receives the full descriptor as the `widget`
 * prop; widgets pull their data from the shared/page Inertia props.
 *
 * This path-resolution convention is a Core internal, not a declared public
 * contract — it may change between releases (see app/Modules/README.md,
 * "Frontend seam stability"). The declared contract is the Manifest (D-023).
 */
import { computed, defineAsyncComponent } from 'vue';

const props = defineProps({
    widget: { type: Object, required: true },
});

const coreWidgets = import.meta.glob('../Widgets/**/*.vue');
const moduleWidgets = import.meta.glob('../../Modules/*/Widgets/**/*.vue');

const loader = computed(() => {
    const name = props.widget.component;

    return name.startsWith('Modules/')
        ? moduleWidgets[`../../${name}.vue`]
        : coreWidgets[`../${name.replace(/^Core\//, '')}.vue`];
});

const resolved = computed(() => (loader.value ? defineAsyncComponent(loader.value) : null));
</script>

<template>
    <component :is="resolved" v-if="resolved" :widget="widget" />

    <!-- A registered descriptor whose .vue file is missing: fail soft and
         visibly, so a typo in `component` never blanks the dashboard. -->
    <div v-else class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-400">
        ویجت «<span dir="ltr">{{ widget.component }}</span>» پیدا نشد.
    </div>
</template>

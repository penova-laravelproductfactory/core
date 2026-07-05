import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

/**
 * Penova Core — Inertia entry point.
 *
 * Page components are resolved from two roots:
 *   Core/...            → resources/js/Core/Pages/...   (shared panel)
 *   Modules/<Name>/...  → resources/js/Modules/<Name>/Pages/... (products)
 *
 * A controller rendering Inertia::render('Modules/Booking/Calendar')
 * therefore needs no frontend registration — drop the .vue file in the
 * module's Pages folder and it resolves.
 */
const corePages = import.meta.glob('./Core/Pages/**/*.vue');
const modulePages = import.meta.glob('./Modules/*/Pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} — Penova` : 'Penova'),

    resolve: (name) => {
        if (name.startsWith('Modules/')) {
            const [, module, ...rest] = name.split('/');
            return modulePages[`./Modules/${module}/Pages/${rest.join('/')}.vue`]();
        }

        // "Core/Users/Index" → ./Core/Pages/Users/Index.vue
        return corePages[`./Core/Pages/${name.replace(/^Core\//, '')}.vue`]();
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});

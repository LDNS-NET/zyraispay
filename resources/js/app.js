import '../css/app.css';
import './bootstrap';
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import VueApexCharts from "vue3-apexcharts";
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';




createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(VueApexCharts);
        app.use(Toast, {
            position: 'top-right',
            timeout: 3000,
            closeOnClick: true,
        });

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

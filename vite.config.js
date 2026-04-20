import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/charts/dashboard.js',
                'resources/js/charts/reports.js',
                'resources/js/incident-form.js',
                'resources/js/ceet-vue/main.js',
            ],
            refresh: [
                'resources/views/**',
                'routes/**',
                'app/Http/Controllers/**',
                'app/Http/Requests/**',
            ],
        }),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
        watch: {
            ignored: [
                '**/node_modules/**',
                '**/vendor/**',
                '**/storage/**',
                '**/.git/**',
            ],
            usePolling: true,
            interval: 300,
        },
    },
});

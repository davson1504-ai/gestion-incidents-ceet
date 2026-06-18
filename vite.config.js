import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pages/admin-dashboard.css',
                'resources/css/pages/supervisor-dashboard.css',
                'resources/css/pages/login.css',
                'resources/js/app.js',
                'resources/js/pages/admin-dashboard.js',
                'resources/js/pages/supervisor-dashboard.js',
                'resources/css/pages/operator-dashboard.css',
                'resources/js/pages/operator-dashboard.js',
                'resources/css/pages/incidents-index.css',
                'resources/js/pages/incidents-index.js',
                'resources/css/pages/incidents-mine.css',
                'resources/js/pages/incidents-mine.js',
                'resources/css/pages/incidents-en-cours.css',
                'resources/js/pages/incidents-en-cours.js',
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

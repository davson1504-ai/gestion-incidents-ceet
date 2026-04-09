import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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

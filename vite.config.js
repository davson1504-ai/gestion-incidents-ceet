import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                // ── CSS global (layout + composants + login) ──────────────
                'resources/css/app.css',
                'resources/css/pages/login.css',

                // ── CSS pages dashboards ──────────────────────────────────
                'resources/css/pages/dashboard-admin.css',
                'resources/css/pages/dashboard-supervisor.css',
                'resources/css/pages/dashboard-operator.css',

                // ── CSS pages incidents ───────────────────────────────────
                'resources/css/pages/incidents-index.css',
                'resources/css/pages/incidents-create.css',
                'resources/css/pages/incidents-edit.css',
                'resources/css/pages/incidents-show.css',
                'resources/css/pages/incidents-en-cours.css',
                'resources/css/pages/incidents-mine.css',

                // ── CSS pages autres ──────────────────────────────────────
                'resources/css/pages/reports.css',
                'resources/css/pages/users.css',
                'resources/css/pages/profile.css',
                'resources/css/pages/catalogues.css',
                'resources/css/pages/historique.css',
                'resources/css/pages/system-status.css',

                // ── JS global ─────────────────────────────────────────────
                'resources/js/app.js',

                // ── JS pages dashboards ───────────────────────────────────
                'resources/js/pages/admin-dashboard.js',
                'resources/js/pages/supervisor-dashboard.js',
                'resources/js/pages/operator-dashboard.js',

                // ── JS pages incidents ────────────────────────────────────
                'resources/js/pages/incidents-index.js',
                'resources/js/pages/incidents-show.js',
                'resources/js/pages/incidents-create.js',
                'resources/js/pages/incidents-mine.js',
                'resources/js/pages/incidents-en-cours.js',

                // ── JS pages autres ───────────────────────────────────────
                'resources/js/pages/reports.js',
                'resources/js/pages/users.js',
                'resources/js/pages/profile.js',
                'resources/js/pages/catalogues.js',
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

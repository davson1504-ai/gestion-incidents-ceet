/**
 * CEET — Dashboard Administrateur
 * Fichier cible : resources/js/pages/admin-dashboard.js
 */

const parseChartData = () => {
    const node = document.getElementById('ceet-admin-chart-data');

    if (!node) {
        return [];
    }

    try {
        const data = JSON.parse(node.textContent || '[]');
        return Array.isArray(data) ? data : [];
    } catch (error) {
        console.warn('[CEET] Données chart invalides', error);
        return [];
    }
};

const enhanceChart = (root) => {
    const chart = root.querySelector('[data-ceet-chart]');
    const bars = root.querySelector('[data-chart-bars]');

    if (!chart || !bars) {
        return;
    }

    const data = parseChartData();

    if (!data.length) {
        bars.innerHTML = '<div class="ceet-admin-empty-state">Aucune donnée disponible.</div>';
        return;
    }

    bars.innerHTML = data.map((point, index) => {
        const total = Number(point.total || 0);
        const height = Math.max(0, Math.min(100, Number(point.height || 0)));
        const label = String(point.label || 'N/A');
        const tone = index % 2 === 0 ? 'is-primary' : 'is-secondary';

        return `
            <div class="ceet-admin-chart-bar-item ${tone}" title="${total} incident(s)" aria-label="${label}: ${total} incident(s)">
                <div class="ceet-admin-chart-track">
                    <span style="height: ${height}%"></span>
                </div>
                <small>${label}</small>
            </div>
        `;
    }).join('');

    chart.setAttribute('role', 'img');
};

const initFilterPanel = (root) => {
    const toggle = root.querySelector('[data-filter-toggle]');
    const panel = root.querySelector('[data-filter-panel]');

    if (!toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = !panel.hasAttribute('hidden');

        if (isOpen) {
            panel.setAttribute('hidden', '');
            panel.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            return;
        }

        panel.removeAttribute('hidden');
        panel.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');

        const firstInput = panel.querySelector('input');
        firstInput?.focus({ preventScroll: true });
    });
};

const initClickableRows = (root) => {
    root.querySelectorAll('tr[data-row-url]').forEach((row) => {
        row.addEventListener('click', (event) => {
            const target = event.target;

            if (target.closest('a, button, input, select, textarea')) {
                return;
            }

            const url = row.getAttribute('data-row-url');
            if (url && url !== '#') {
                window.location.href = url;
            }
        });
    });
};

const bootAdminDashboard = () => {
    const root = document.querySelector('[data-admin-dashboard]');

    if (!root) {
        return;
    }

    document.body.classList.add('ceet-admin-dashboard-shell');

    enhanceChart(root);
    initFilterPanel(root);
    initClickableRows(root);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAdminDashboard, { once: true });
} else {
    bootAdminDashboard();
}

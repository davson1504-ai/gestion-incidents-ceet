import './bootstrap';
import { initNavigation } from './core/navigation';
import { initNotifications } from './core/notifications';
import { initAjaxForms } from './core/ajax-forms';
import { initToasts } from './core/toasts';
import { initHelpHints } from './core/help-hint';
const boot = () => {
    window.CEET = window.CEET || {};
    window.CEET.appReady = true;
    initNavigation();
    initNotifications();
    initAjaxForms();
    initToasts();
};
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}

initHelpHints();

// CEET - bouton "Vider anciens" dans le panneau notifications
(() => {
    if (window.__CEET_CLEAR_OLD_NOTIFICATIONS__) return;
    window.__CEET_CLEAR_OLD_NOTIFICATIONS__ = true;

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const ensureButton = () => {
        document.querySelectorAll('.ceet-global-notification-header').forEach((header) => {
            if (header.querySelector('[data-ceet-notification-clear-old]')) return;

            const readAll = header.querySelector('[data-ceet-notification-read-all]');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ceet-global-notification-clear-old';
            btn.dataset.ceetNotificationClearOld = '';
            btn.textContent = 'Vider anciens';

            if (readAll) readAll.insertAdjacentElement('afterend', btn);
            else header.appendChild(btn);
        });
    };

    document.addEventListener('click', async (event) => {
        const btn = event.target.closest('[data-ceet-notification-clear-old]');
        if (!btn) return;

        event.preventDefault();
        event.stopPropagation();

        if (!window.confirm('Supprimer les notifications déjà lues et celles de plus de 30 jours ?')) return;

        const oldText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Vidage...';

        try {
            const response = await fetch('/notifications/clear-old', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const payload = await response.json().catch(() => ({}));
            btn.textContent = payload.deleted > 0 ? 'Vidés' : 'Aucun ancien';

            window.setTimeout(() => window.location.reload(), 650);
        } catch (error) {
            console.error('[CEET] Suppression anciennes notifications impossible:', error);
            btn.textContent = 'Erreur';
            window.setTimeout(() => {
                btn.textContent = oldText;
                btn.disabled = false;
            }, 1400);
        }
    }, true);

    const boot = () => {
        ensureButton();

        const observer = new MutationObserver(() => {
            window.requestAnimationFrame(ensureButton);
        });

        observer.observe(document.body, { childList: true, subtree: true });
        document.addEventListener('ceet:navigated', ensureButton);
        document.addEventListener('ceet:page-ready', ensureButton);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();

// CEET - détails interventions/rapports sur la page incident
(() => {
    if (window.__CEET_INTERVENTION_DETAIL_TOGGLE__) {
        return;
    }

    window.__CEET_INTERVENTION_DETAIL_TOGGLE__ = true;

    const syncButtonState = (button, row) => {
        const isOpen = !row.hidden;
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        button.textContent = isOpen ? 'Masquer' : 'Consulter';
    };

    const prepareButtons = (root = document) => {
        root.querySelectorAll('[data-intervention-detail]').forEach((button) => {
            const targetId = button.getAttribute('data-intervention-detail');
            const row = targetId ? document.getElementById(targetId) : null;

            button.type = 'button';
            button.setAttribute('aria-controls', targetId || '');
            button.setAttribute('aria-expanded', row && !row.hidden ? 'true' : 'false');

            if (!button.textContent.trim() || button.textContent.trim() === 'Masquer') {
                button.textContent = row && !row.hidden ? 'Masquer' : 'Consulter';
            }
        });
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-intervention-detail]');

        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const targetId = button.getAttribute('data-intervention-detail');
        const row = targetId ? document.getElementById(targetId) : null;

        if (!row) {
            console.warn('[CEET] Ligne détail introuvable:', targetId);
            return;
        }

        row.hidden = !row.hidden;
        row.classList.toggle('is-open', !row.hidden);
        syncButtonState(button, row);
    }, true);

    const boot = () => prepareButtons(document);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }

    document.addEventListener('ceet:navigated', () => prepareButtons(document));
    document.addEventListener('ceet:page-ready', () => prepareButtons(document));

    window.CEET_INTERVENTION_DETAIL_TARGETS = () => Array.from(document.querySelectorAll('[data-intervention-detail]')).map((button) => ({
        button: button.textContent.trim(),
        target: button.getAttribute('data-intervention-detail'),
        exists: !!document.getElementById(button.getAttribute('data-intervention-detail')),
    }));
})();

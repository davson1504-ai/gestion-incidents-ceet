/**
 * CEET — catalogues.js
 * Interactions de la page catalogues :
 *  - Recherche en temps réel sur les cartes catalogue
 *  - Menu déroulant "Nouveau catalogue"
 *  - Toggle détails dans les tableaux (statuts, types...)
 */

const boot = () => {

    // ── Recherche filtre cartes catalogue ──────────────────
    const searchInput = document.querySelector('[data-catalogue-search]');
    const cards       = Array.from(document.querySelectorAll('[data-catalogue-card]'));
    const emptyMsg    = document.querySelector('[data-catalogue-empty]');

    if (searchInput && cards.length) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            let visible = 0;

            cards.forEach((card) => {
                const keywords = card.dataset.catalogueKeywords?.toLowerCase() ?? '';
                const match    = !term || keywords.includes(term);
                card.hidden    = !match;
                if (match) visible++;
            });

            if (emptyMsg) emptyMsg.hidden = visible > 0;
        });
    }

    // ── Menu déroulant "Nouveau catalogue" ─────────────────
    const createMenu   = document.querySelector('[data-catalogue-create-menu]');
    const createToggle = document.querySelector('[data-catalogue-create-toggle]');
    const createPanel  = document.querySelector('[data-catalogue-create-panel]');

    if (createMenu && createToggle && createPanel) {
        createToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !createPanel.hidden;
            createPanel.hidden = isOpen;
            createToggle.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', (e) => {
            if (!createMenu.contains(e.target)) {
                createPanel.hidden = true;
                createToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                createPanel.hidden = true;
                createToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ── Toggle détails logs historique (dans statuts/types) ─
    document.querySelectorAll('[data-history-detail-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.historyDetailToggle;
            const row = document.getElementById(targetId);
            if (!row) return;
            const wasHidden = row.hidden;
            row.hidden = !wasHidden;
            btn.textContent = wasHidden ? 'Masquer' : btn.dataset.originalText || 'Afficher';
            if (!btn.dataset.originalText) btn.dataset.originalText = 'Afficher';
        });
    });

    // ── Toggle détails systeme log ─────────────────────────
    document.querySelectorAll('[data-system-log-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.systemLogToggle;
            const row = document.getElementById(targetId);
            if (!row) return;
            row.hidden = !row.hidden;
            btn.textContent = row.hidden ? 'Détails' : 'Masquer';
        });
    });

    // ── Toggle détails intervention (show page) ────────────
    document.querySelectorAll('[data-intervention-detail]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.interventionDetail;
            const row = document.getElementById(targetId);
            if (!row) return;
            row.hidden = !row.hidden;
            btn.textContent = row.hidden ? 'Consulter' : 'Masquer';
        });
    });

};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

/* === CEET CATALOGUES INDEX INTERACTIONS === */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('[data-catalogue-search]');
    const cards = Array.from(document.querySelectorAll('[data-catalogue-card]'));
    const emptyState = document.querySelector('[data-catalogue-empty]');

    if (searchInput && cards.length) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            cards.forEach((card) => {
                const text = (card.dataset.catalogueKeywords || card.textContent || '').toLowerCase();
                const visible = !query || text.includes(query);

                card.hidden = !visible;

                if (visible) {
                    visibleCount++;
                }
            });

            if (emptyState) {
                emptyState.hidden = visibleCount > 0;
            }
        });
    }

    const menu = document.querySelector('[data-catalogue-create-menu]');
    const toggle = document.querySelector('[data-catalogue-create-toggle]');
    const panel = document.querySelector('[data-catalogue-create-panel]');

    if (menu && toggle && panel) {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            const opened = panel.hidden;
            panel.hidden = !opened;
            toggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
        });

        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target)) {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
});
/* === END CEET CATALOGUES INDEX INTERACTIONS === */

/* === CEET CATALOGUES EXCEL IMPORT START === */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-catalogues-import-modal]');
    const openButtons = document.querySelectorAll('[data-catalogues-import-open]');
    const closeButtons = document.querySelectorAll('[data-catalogues-import-close]');

    if (!modal || !openButtons.length) return;

    const openModal = () => {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        window.setTimeout(() => modal.querySelector('input[type="file"]')?.focus(), 50);
    };

    const closeModal = () => {
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    openButtons.forEach((button) => button.addEventListener('click', openModal));
    closeButtons.forEach((button) => button.addEventListener('click', closeModal));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });
});
/* === CEET CATALOGUES EXCEL IMPORT END === */

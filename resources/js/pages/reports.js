/**
 * CEET — reports.js
 * Interactions de la page rapports :
 *  - Soumission du formulaire de filtres via navigation normale (pas AJAX)
 *  - Toggle détails des lignes du tableau récapitulatif
 *  - Protection des boutons d'export (évite double-clic)
 */

const boot = () => {

    // ── Protection double-clic sur les exports ─────────────
    document.querySelectorAll(
        '.ceet-reports-admin-export-box button[type="submit"]'
    ).forEach((btn) => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            if (!form) return;

            // Validation simple : date ou mois requis
            const input = form.querySelector('input[type="month"], input[type="date"]');
            if (input && !input.value) {
                input.focus();
                return;
            }

            btn.disabled = true;
            btn.dataset.originalText = btn.textContent.trim();
            btn.textContent = 'Génération...';

            // Ré-activer après 8s (timeout de sécurité)
            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = btn.dataset.originalText || btn.textContent;
            }, 8000);
        });
    });

    // ── Highlight ligne tableau au survol ──────────────────
    // Géré en CSS, rien à faire ici.

    // ── Auto-submit filtres au changement de période ───────
    const periodSelect = document.querySelector(
        '.ceet-reports-admin-filter-form select[name="period"]'
    );
    if (periodSelect) {
        periodSelect.addEventListener('change', () => {
            periodSelect.closest('form')?.submit();
        });
    }

};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

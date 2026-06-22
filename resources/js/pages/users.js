/**
 * CEET — users.js
 * Interactions de la page gestion utilisateurs :
 *  - Confirmation de suppression
 *  - Highlight de ligne active
 *  - Auto-submit filtre statut
 */

const boot = () => {

    // ── Confirmation suppression utilisateur ───────────────
    // La confirmation est déjà dans les vues via onsubmit="return confirm(...)"
    // On ajoute ici une version JS propre sans attribut inline

    document.querySelectorAll(
        '.ceet-users-admin-action-btn.is-danger[type="submit"]'
    ).forEach((btn) => {
        const form = btn.closest('form');
        if (!form || form.getAttribute('onsubmit')) return;

        form.addEventListener('submit', (e) => {
            const userName = form.closest('tr')
                ?.querySelector('.ceet-users-admin-identity strong')
                ?.textContent?.trim() ?? 'cet utilisateur';

            if (!confirm(`Supprimer ou désactiver ${userName} ?\n\nCette action est irréversible.`)) {
                e.preventDefault();
            }
        });
    });

    // ── Auto-submit filtres sur changement de sélect ───────
    ['[name="role"]', '[name="is_active"]'].forEach((selector) => {
        document.querySelector(
            `.ceet-users-admin-filters ${selector}`
        )?.addEventListener('change', function () {
            this.closest('form')?.submit();
        });
    });

    // ── Highlight ligne au focus clavier ───────────────────
    document.querySelectorAll('.ceet-users-admin-table tbody tr').forEach((row) => {
        row.addEventListener('focusin',  () => row.classList.add('is-focused'));
        row.addEventListener('focusout', () => row.classList.remove('is-focused'));
    });

};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

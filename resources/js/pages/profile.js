/**
 * CEET — profile.js
 * Interactions de la page profil :
 *  - Synchronisation prénom/nom → champ name caché
 *  - Feedback succès après enregistrement
 *  - Indicateur force mot de passe
 */

const boot = () => {

    // ── Sync prénom + nom → champ name caché ───────────────
    const firstNameInput = document.querySelector('input[name="first_name"]');
    const lastNameInput  = document.querySelector('input[name="last_name"]');
    const fullNameInput  = document.querySelector('input[data-full-name]');

    const syncFullName = () => {
        if (!fullNameInput) return;
        const first = firstNameInput?.value?.trim() ?? '';
        const last  = lastNameInput?.value?.trim()  ?? '';
        fullNameInput.value = [first, last].filter(Boolean).join(' ') || fullNameInput.value;
    };

    firstNameInput?.addEventListener('input', syncFullName);
    lastNameInput?.addEventListener('input',  syncFullName);

    // ── Indicateur force mot de passe ──────────────────────
    const passwordInput = document.querySelector(
        '.ceet-profile-password-form input[name="password"]'
    );

    if (passwordInput) {
        // Crée une barre de force
        const bar = document.createElement('div');
        bar.style.cssText = `
            height: 4px;
            border-radius: 999px;
            background: var(--ceet-border);
            overflow: hidden;
            margin-top: 4px;
        `;
        const fill = document.createElement('span');
        fill.style.cssText = `
            display: block;
            height: 100%;
            width: 0%;
            border-radius: inherit;
            transition: width 200ms ease, background-color 200ms ease;
        `;
        bar.appendChild(fill);
        passwordInput.closest('label')?.appendChild(bar);

        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let strength = 0;
            if (val.length >= 8)  strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            const colors = ['#ba1a1a', '#f59e0b', '#3b82f6', '#2f6b45'];
            const widths  = ['25%', '50%', '75%', '100%'];

            fill.style.width           = val.length ? widths[strength - 1] || '10%' : '0%';
            fill.style.backgroundColor = val.length ? colors[strength - 1] || colors[0] : 'transparent';
        });
    }

    // ── Toast succès si session success ────────────────────
    if (typeof window.CEET?.toast === 'function') {
        const successAlert = document.querySelector('.ceet-alert-success');
        if (successAlert) {
            const msg = successAlert.querySelector('.ceet-alert-content')?.textContent?.trim();
            if (msg) window.CEET.toast(msg, 'success');
        }
    }

};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

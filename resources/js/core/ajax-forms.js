const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const wantsJson = (form) => form.matches('[data-ceet-ajax-form]') && !form.matches('[data-ceet-native-form]');

const shouldSkip = (form) => {
    const action = form.getAttribute('action') || '';

    return form.matches('[data-ceet-native-form]')
        || action.includes('/logout')
        || action.includes('export')
        || action.includes('download')
        || form.getAttribute('enctype') === 'multipart/form-data';
};

const setBusy = (form, busy) => {
    form.toggleAttribute('aria-busy', busy);
    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = busy;
    });
};

export const initAjaxForms = () => {
    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || shouldSkip(form) || !wantsJson(form)) {
            return;
        }

        event.preventDefault();
        setBusy(form, true);

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Le formulaire contient des erreurs.');
            }

            window.CEET?.toast?.('success', payload.message || 'Action effectuée avec succès.');

            if (payload.redirect) {
                window.location.assign(payload.redirect);
                return;
            }

            form.dispatchEvent(new CustomEvent('ceet:ajax-success', { detail: payload, bubbles: true }));
        } catch (error) {
            window.CEET?.toast?.('danger', error.message || 'Action impossible.');
            form.dispatchEvent(new CustomEvent('ceet:ajax-error', { detail: error, bubbles: true }));
        } finally {
            setBusy(form, false);
        }
    });
};

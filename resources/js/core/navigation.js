const nativeTargets = [
    '[download]',
    'a[href^="mailto:"]',
    'a[href^="tel:"]',
    'a[href*="format=pdf"]',
    'a[href*="format=excel"]',
    'a[href*="format=csv"]',
    'a[href$=".pdf"]',
    'a[href$=".xlsx"]',
    'a[href$=".csv"]',
];

const isNativeNavigation = (element) => {
    if (!element) {
        return true;
    }

    if (element.closest('form[data-ceet-native-form]')) {
        return true;
    }

    return nativeTargets.some((selector) => element.matches?.(selector) || element.closest?.(selector));
};

const initSidebar = () => {
    const toggles = document.querySelectorAll('[data-ceet-sidebar-toggle]');

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('ceet-sidebar-open');
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.body.classList.remove('ceet-sidebar-open');
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const isInsideSidebar = Boolean(target.closest('.ceet-sidebar'));
        const isToggle = Boolean(target.closest('[data-ceet-sidebar-toggle]'));

        if (!isInsideSidebar && !isToggle) {
            document.body.classList.remove('ceet-sidebar-open');
        }
    });
};

const initConfirmForms = () => {
    const modal = document.querySelector('[data-ceet-confirm-modal]');
    const messageNode = modal?.querySelector('[data-ceet-confirm-message]');
    const acceptButton = modal?.querySelector('[data-ceet-confirm-accept]');
    const cancelButtons = modal?.querySelectorAll('[data-ceet-confirm-cancel]') || [];

    let pendingForm = null;

    const close = () => {
        pendingForm = null;
        modal?.setAttribute('hidden', 'hidden');
    };

    cancelButtons.forEach((button) => button.addEventListener('click', close));

    acceptButton?.addEventListener('click', () => {
        if (!pendingForm) {
            close();
            return;
        }

        const form = pendingForm;
        close();
        form.dataset.ceetConfirmed = '1';
        form.requestSubmit();
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const confirmation = form.dataset.confirm;

        if (!confirmation || form.dataset.ceetConfirmed === '1') {
            return;
        }

        event.preventDefault();

        if (!modal) {
            if (window.confirm(confirmation)) {
                form.dataset.ceetConfirmed = '1';
                form.requestSubmit();
            }
            return;
        }

        pendingForm = form;
        if (messageNode) {
            messageNode.textContent = confirmation;
        }
        modal.removeAttribute('hidden');
        acceptButton?.focus();
    });
};

export const initNavigation = () => {
    initSidebar();
    initConfirmForms();

    document.addEventListener('click', (event) => {
        const link = event.target instanceof Element ? event.target.closest('a[data-ceet-link]') : null;

        if (!link || isNativeNavigation(link)) {
            return;
        }

        document.body.classList.remove('ceet-sidebar-open');
    });
};

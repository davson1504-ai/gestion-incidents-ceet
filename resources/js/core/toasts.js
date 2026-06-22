const icons = {
    success: 'check_circle',
    danger: 'error',
    error: 'error',
    warning: 'warning',
    info: 'info',
};

const labels = {
    success: 'Succès',
    danger: 'Erreur',
    error: 'Erreur',
    warning: 'Attention',
    info: 'Information',
};

const ensureStack = () => {
    let stack = document.querySelector('[data-ceet-toast-stack]');

    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'ceet-toast-stack';
        stack.dataset.ceetToastStack = '1';
        document.body.appendChild(stack);
    }

    return stack;
};

const createToast = (type, message) => {
    const toast = document.createElement('div');
    const resolvedType = type || 'info';

    toast.className = `ceet-toast ceet-toast-${resolvedType}`;
    toast.setAttribute('role', resolvedType === 'danger' || resolvedType === 'error' ? 'alert' : 'status');
    toast.innerHTML = `
        <span class="material-symbols-outlined" aria-hidden="true">${icons[resolvedType] || icons.info}</span>
        <span>
            <strong>${labels[resolvedType] || labels.info}</strong>
            <small></small>
        </span>
    `;

    toast.querySelector('small').textContent = message || 'Action effectuée.';
    return toast;
};

export const initToasts = () => {
    window.CEET = window.CEET || {};

    window.CEET.toast = (type = 'info', message = '') => {
        const stack = ensureStack();
        const toast = createToast(type, message);
        stack.appendChild(toast);

        window.setTimeout(() => {
            toast.remove();
        }, 5200);
    };
};

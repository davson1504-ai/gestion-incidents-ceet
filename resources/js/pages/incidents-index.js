(() => {
    const page = document.querySelector('[data-incidents-index]');

    if (!page) {
        return;
    }

    const body = document.body;
    const sidebar = page.querySelector('[data-incidents-sidebar]');
    const toggleButton = page.querySelector('[data-incidents-sidebar-toggle]');
    const overlay = page.querySelector('[data-incidents-overlay]');
    const searchInput = page.querySelector('.ceet-incidents-top-search input');
    const toast = page.querySelector('[data-incidents-toast]');
    const toastClose = page.querySelector('[data-incidents-toast-close]');

    const openSidebar = () => {
        body.classList.add('ceet-incidents-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        body.classList.remove('ceet-incidents-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'false');
    };

    toggleButton?.addEventListener('click', () => {
        if (body.classList.contains('ceet-incidents-sidebar-open')) {
            closeSidebar();
            return;
        }

        openSidebar();
    });

    overlay?.addEventListener('click', closeSidebar);

    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();

            if (toast) {
                toast.hidden = true;
            }
        }

        const isSearchShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

        if (isSearchShortcut && searchInput) {
            event.preventDefault();
            searchInput.focus();
        }
    });

    toastClose?.addEventListener('click', () => {
        if (toast) {
            toast.hidden = true;
        }
    });
})();

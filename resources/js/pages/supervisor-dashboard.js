(() => {
    const page = document.querySelector('[data-supervisor-dashboard]');

    if (!page) {
        return;
    }

    const body = document.body;
    const sidebar = page.querySelector('[data-supervisor-sidebar]');
    const toggleButton = page.querySelector('[data-supervisor-sidebar-toggle]');
    const overlay = page.querySelector('[data-supervisor-overlay]');
    const searchForm = page.querySelector('.ceet-supervisor-search');
    const searchInput = searchForm?.querySelector('input');
    const filterButton = page.querySelector('[data-supervisor-filter]');

    const openSidebar = () => {
        body.classList.add('ceet-supervisor-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        body.classList.remove('ceet-supervisor-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'false');
    };

    toggleButton?.addEventListener('click', () => {
        if (body.classList.contains('ceet-supervisor-sidebar-open')) {
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

    searchInput?.addEventListener('focus', () => {
        searchForm?.classList.add('is-focused');
    });

    searchInput?.addEventListener('blur', () => {
        searchForm?.classList.remove('is-focused');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }

        const isSearchShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

        if (isSearchShortcut && searchInput) {
            event.preventDefault();
            searchInput.focus();
        }
    });

    filterButton?.addEventListener('click', () => {
        searchInput?.focus();
    });
})();
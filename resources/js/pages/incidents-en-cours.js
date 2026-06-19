(() => {
    const page = document.querySelector('[data-current-page]');

    if (!page) {
        return;
    }

    const body = document.body;
    const sidebar = page.querySelector('[data-current-sidebar]');
    const toggleButton = page.querySelector('[data-current-sidebar-toggle]');
    const overlay = page.querySelector('[data-current-overlay]');
    const searchInput = page.querySelector('.ceet-current-search input');
    const filterToggle = page.querySelector('[data-current-filter-toggle]');
    const filterPanel = page.querySelector('[data-current-filter-panel]');
    const viewLinks = page.querySelectorAll('[data-current-view]');

    const openSidebar = () => {
        body.classList.add('ceet-current-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        body.classList.remove('ceet-current-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'false');
    };

    toggleButton?.addEventListener('click', () => {
        if (body.classList.contains('ceet-current-sidebar-open')) {
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

    filterToggle?.addEventListener('click', () => {
        if (!filterPanel) {
            return;
        }

        filterPanel.hidden = !filterPanel.hidden;
    });

    viewLinks.forEach((link) => {
        link.addEventListener('click', () => {
            viewLinks.forEach((item) => item.classList.remove('is-active'));
            link.classList.add('is-active');
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();

            if (filterPanel) {
                filterPanel.hidden = true;
            }
        }

        const isSearchShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

        if (isSearchShortcut && searchInput) {
            event.preventDefault();
            searchInput.focus();
        }
    });
})();

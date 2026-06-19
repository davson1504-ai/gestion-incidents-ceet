(() => {
    const page = document.querySelector('[data-mine-page]');

    if (!page) {
        return;
    }

    const body = document.body;
    const sidebar = page.querySelector('[data-mine-sidebar]');
    const toggleButton = page.querySelector('[data-mine-sidebar-toggle]');
    const overlay = page.querySelector('[data-mine-overlay]');
    const searchInput = page.querySelector('.ceet-mine-search input');
    const filterToggle = page.querySelector('[data-mine-filter-toggle]');
    const filterPanel = page.querySelector('[data-mine-filter-panel]');

    const openSidebar = () => {
        body.classList.add('ceet-mine-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        body.classList.remove('ceet-mine-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'false');
    };

    toggleButton?.addEventListener('click', () => {
        if (body.classList.contains('ceet-mine-sidebar-open')) {
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

    page.querySelectorAll('[data-mine-row]').forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('a') || event.target.closest('button')) {
                return;
            }

            row.classList.add('is-selected');

            window.setTimeout(() => {
                row.classList.remove('is-selected');
            }, 280);
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

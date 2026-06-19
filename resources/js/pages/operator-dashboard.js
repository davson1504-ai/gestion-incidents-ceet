
(() => {
    const page = document.querySelector('[data-operator-dashboard]');

    if (!page) {
        return;
    }

    const body = document.body;
    const sidebar = page.querySelector('[data-operator-sidebar]');
    const toggleButton = page.querySelector('[data-operator-sidebar-toggle]');
    const overlay = page.querySelector('[data-operator-overlay]');
    const searchInput = page.querySelector('.ceet-operator-search input');

    const openSidebar = () => {
        body.classList.add('ceet-operator-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        body.classList.remove('ceet-operator-sidebar-open');
        toggleButton?.setAttribute('aria-expanded', 'false');
    };

    toggleButton?.addEventListener('click', () => {
        if (body.classList.contains('ceet-operator-sidebar-open')) {
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
        }

        const isSearchShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

        if (isSearchShortcut && searchInput) {
            event.preventDefault();
            searchInput.focus();
        }
    });

    // Operator dashboard notifications dropdown
    const notificationRoot = page.querySelector('[data-operator-notifications]');

    if (notificationRoot) {
        const toggle = notificationRoot.querySelector('[data-notifications-toggle]');
        const panel = notificationRoot.querySelector('[data-notification-panel]');
        const list = notificationRoot.querySelector('[data-notification-list]');
        const badge = notificationRoot.querySelector('[data-notification-badge]');
        const summary = notificationRoot.querySelector('[data-notification-summary]');
        const readAllButton = notificationRoot.querySelector('[data-notifications-read-all]');
        const notificationsUrl = notificationRoot.dataset.notificationsUrl || '/notifications';
        const readAllUrl = notificationRoot.dataset.readAllUrl || '/notifications/read-all';
        const readUrlTemplate = notificationRoot.dataset.readUrlTemplate || '/notifications/__ID__/read';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const formatDate = (value) => {
            if (!value) {
                return '';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return '';
            }

            return date.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        const updateUnreadCount = (count) => {
            const unread = Number(count || 0);

            if (badge) {
                badge.textContent = unread > 99 ? '99+' : String(unread);
                badge.hidden = unread < 1;
            }

            if (summary) {
                summary.textContent = `${unread} non lue(s)`;
            }
        };

        const renderNotifications = (payload) => {
            const notifications = Array.isArray(payload?.notifications) ? payload.notifications : [];
            updateUnreadCount(payload?.unread_count || 0);

            if (!list) {
                return;
            }

            if (!notifications.length) {
                list.innerHTML = '<div class="ceet-operator-notification-empty">Aucune notification disponible.</div>';
                return;
            }

            list.innerHTML = notifications.map((notification) => {
                const data = notification.data || {};
                const title = data.title || 'Notification';
                const message = data.message || data.incident_title || 'Nouvelle information disponible.';
                const code = data.incident_code ? `<span>${escapeHtml(data.incident_code)}</span>` : '';
                const date = formatDate(notification.created_at);
                const href = data.incident_url || '#';
                const unreadClass = notification.read_at ? '' : ' is-unread';

                return `
                    <a href="${escapeHtml(href)}" class="ceet-operator-notification-item${unreadClass}" data-notification-id="${escapeHtml(notification.id)}">
                        <span class="ceet-operator-notification-status"></span>
                        <span class="ceet-operator-notification-content">
                            <strong>${escapeHtml(title)}</strong>
                            <small>${escapeHtml(message)}</small>
                            <em>${code}${date ? `<span>${escapeHtml(date)}</span>` : ''}</em>
                        </span>
                    </a>
                `;
            }).join('');
        };

        const loadNotifications = async () => {
            if (list) {
                list.innerHTML = '<div class="ceet-operator-notification-empty">Chargement des notifications...</div>';
            }

            try {
                const response = await fetch(notificationsUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Impossible de charger les notifications.');
                }

                renderNotifications(await response.json());
            } catch (error) {
                if (list) {
                    list.innerHTML = '<div class="ceet-operator-notification-empty is-error">Notifications indisponibles.</div>';
                }
            }
        };

        const openPanel = () => {
            panel.hidden = false;
            toggle?.setAttribute('aria-expanded', 'true');
            loadNotifications();
        };

        const closePanel = () => {
            panel.hidden = true;
            toggle?.setAttribute('aria-expanded', 'false');
        };

        toggle?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (panel.hidden) {
                openPanel();
                return;
            }

            closePanel();
        });

        document.addEventListener('click', (event) => {
            if (!notificationRoot.contains(event.target)) {
                closePanel();
            }
        });

        list?.addEventListener('click', async (event) => {
            const item = event.target.closest('[data-notification-id]');

            if (!item) {
                return;
            }

            const notificationId = item.dataset.notificationId;
            const href = item.getAttribute('href');

            if (href === '#') {
                event.preventDefault();
            }

            if (notificationId && item.classList.contains('is-unread')) {
                try {
                    const response = await fetch(readUrlTemplate.replace('__ID__', encodeURIComponent(notificationId)), {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                    });

                    if (response.ok) {
                        const payload = await response.json();
                        item.classList.remove('is-unread');
                        updateUnreadCount(payload?.unread_count || 0);
                    }
                } catch (error) {
                    // Navigation remains available even if marking as read fails.
                }
            }
        });

        readAllButton?.addEventListener('click', async () => {
            try {
                const response = await fetch(readAllUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });

                if (response.ok) {
                    updateUnreadCount(0);
                    notificationRoot.querySelectorAll('.ceet-operator-notification-item.is-unread').forEach((item) => {
                        item.classList.remove('is-unread');
                    });
                }
            } catch (error) {
                // Keep current UI state.
            }
        });
    }

})();

const POLL_INTERVAL_MS = 60_000;

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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

const buildPanel = (trigger, index) => {
    const wrapper = document.createElement('span');
    wrapper.className = 'ceet-global-notification-root';

    trigger.parentNode.insertBefore(wrapper, trigger);
    wrapper.appendChild(trigger);

    const panelId = `ceet-notification-panel-${index + 1}`;
    const panel = document.createElement('section');
    panel.id = panelId;
    panel.className = 'ceet-global-notification-panel';
    panel.hidden = true;
    panel.setAttribute('aria-label', 'Centre de notifications');
    panel.innerHTML = `
        <header class="ceet-global-notification-header">
            <div>
                <strong>Notifications</strong>
                <span data-ceet-notification-summary>Chargement...</span>
            </div>
            <button type="button" data-ceet-notification-read-all>Tout marquer comme lu</button>
        </header>
        <div class="ceet-global-notification-list" data-ceet-notification-list></div>
        <footer class="ceet-global-notification-footer">
            <small>Centre de notifications CEET</small>
        </footer>
    `;
    wrapper.appendChild(panel);

    trigger.setAttribute('aria-controls', panelId);
    trigger.setAttribute('aria-expanded', 'false');

    return {
        wrapper,
        trigger,
        panel,
        list: panel.querySelector('[data-ceet-notification-list]'),
        summary: panel.querySelector('[data-ceet-notification-summary]'),
        readAll: panel.querySelector('[data-ceet-notification-read-all]'),
        badge: trigger.querySelector('[data-ceet-notification-count]'),
    };
};

export const initNotifications = () => {
    const triggers = Array.from(document.querySelectorAll('[data-ceet-notification-trigger]'));

    if (!triggers.length) {
        return;
    }

    const first = triggers[0];
    const state = {
        loaded: false,
        loading: false,
        error: '',
        unread: Number(first.querySelector('[data-ceet-notification-count]')?.textContent || 0),
        notifications: [],
        notificationsUrl: first.dataset.notificationsUrl || '/notifications',
        countUrl: first.dataset.notificationsCountUrl || '/notifications/count',
        readAllUrl: first.dataset.notificationsReadAllUrl || '/notifications/read-all',
        readUrlTemplate: first.dataset.notificationsReadUrlTemplate || '/notifications/__ID__/read',
        roots: triggers.map(buildPanel),
    };

    const updateBadges = () => {
        state.roots.forEach(({ badge, trigger }) => {
            if (badge) {
                badge.textContent = state.unread > 99 ? '99+' : String(state.unread);
                badge.hidden = state.unread < 1;
            }

            trigger.setAttribute(
                'aria-label',
                state.unread > 0 ? `Notifications (${state.unread} non lue(s))` : 'Notifications'
            );
        });
    };

    const render = () => {
        updateBadges();

        state.roots.forEach(({ list, summary, readAll }) => {
            if (summary) {
                summary.textContent = `${state.unread} non lue(s)`;
            }

            if (readAll) {
                readAll.disabled = state.loading || state.unread < 1;
            }

            if (!list) {
                return;
            }

            if (state.loading && !state.loaded) {
                list.innerHTML = '<div class="ceet-global-notification-empty">Chargement des notifications...</div>';
                return;
            }

            if (state.error) {
                list.innerHTML = `<div class="ceet-global-notification-error">${escapeHtml(state.error)}</div>`;
                return;
            }

            if (!state.notifications.length) {
                list.innerHTML = '<div class="ceet-global-notification-empty">Aucune notification pour le moment.</div>';
                return;
            }

            list.innerHTML = state.notifications.map((notification) => {
                const data = notification.data || {};
                const title = notification.title || data.title || 'Notification';
                const message = notification.message || data.message || data.incident_title || 'Nouvelle information disponible.';
                const code = data.incident_code || '';
                const date = formatDate(notification.created_at);
                const href = notification.url || data.incident_url || (data.incident_id ? `/incidents/${data.incident_id}` : '#');
                const unreadClass = notification.read_at ? '' : ' is-unread';

                return `
                    <a href="${escapeHtml(href)}" class="ceet-global-notification-item${unreadClass}" data-ceet-notification-id="${escapeHtml(notification.id)}">
                        <span class="ceet-global-notification-status" aria-hidden="true"></span>
                        <span class="ceet-global-notification-content">
                            <strong>${escapeHtml(title)}</strong>
                            <small>${escapeHtml(message)}</small>
                            <em>${code ? `<span>${escapeHtml(code)}</span>` : ''}${date ? `<span>${escapeHtml(date)}</span>` : ''}</em>
                        </span>
                    </a>
                `;
            }).join('');
        });
    };

    const fetchJson = async (url, options = {}) => {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
        });

        if (!response.ok) {
            throw new Error('Le centre de notifications est temporairement indisponible.');
        }

        return response.json();
    };

    const load = async (force = false) => {
        if (state.loading || (state.loaded && !force)) {
            render();
            return;
        }

        state.loading = true;
        state.error = '';
        render();

        try {
            const payload = await fetchJson(state.notificationsUrl);
            state.unread = Number(payload.unread_count || 0);
            state.notifications = Array.isArray(payload.notifications) ? payload.notifications : [];
            state.loaded = true;
        } catch (error) {
            state.error = error instanceof Error
                ? error.message
                : 'Impossible de charger les notifications.';
        } finally {
            state.loading = false;
            render();
        }
    };

    const refreshCount = async () => {
        try {
            const payload = await fetchJson(state.countUrl);
            state.unread = Number(payload.unread_count || 0);
            updateBadges();
        } catch (error) {
            // Le prochain cycle réessaiera sans interrompre l'interface.
        }
    };

    const markOne = async (id) => {
        if (!id) {
            return false;
        }

        try {
            const payload = await fetchJson(
                state.readUrlTemplate.replace('__ID__', encodeURIComponent(id)),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                }
            );

            state.unread = Number(payload.unread_count ?? Math.max(0, state.unread - 1));
            state.notifications = state.notifications.map((notification) => (
                notification.id === id
                    ? { ...notification, read_at: notification.read_at || new Date().toISOString() }
                    : notification
            ));
            render();

            return true;
        } catch (error) {
            state.error = 'Impossible de marquer cette notification comme lue.';
            render();

            return false;
        }
    };

    const markAll = async () => {
        try {
            const payload = await fetchJson(state.readAllUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });

            state.unread = Number(payload.unread_count || 0);
            state.notifications = state.notifications.map((notification) => ({
                ...notification,
                read_at: notification.read_at || new Date().toISOString(),
            }));
            state.error = '';
            render();
        } catch (error) {
            state.error = 'Impossible de marquer les notifications comme lues.';
            render();
        }
    };

    const closeAll = (except = null) => {
        state.roots.forEach(({ panel, trigger }) => {
            if (panel === except) {
                return;
            }

            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        });
    };

    state.roots.forEach(({ trigger, panel, list, readAll }) => {
        trigger.addEventListener('click', async () => {
            const willOpen = panel.hidden;
            closeAll(panel);
            panel.hidden = !willOpen;
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

            if (willOpen) {
                await load();
            }
        });

        readAll?.addEventListener('click', markAll);

        list?.addEventListener('click', async (event) => {
            const item = event.target instanceof Element
                ? event.target.closest('[data-ceet-notification-id]')
                : null;

            if (!item) {
                return;
            }

            event.preventDefault();
            const href = item.getAttribute('href') || '#';
            await markOne(item.dataset.ceetNotificationId);

            if (href !== '#') {
                window.location.assign(href);
            }
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (target instanceof Element && !target.closest('.ceet-global-notification-root')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    load(true);

    window.setInterval(() => {
        const hasOpenPanel = state.roots.some(({ panel }) => !panel.hidden);

        if (hasOpenPanel) {
            load(true);
        } else {
            refreshCount();
        }
    }, POLL_INTERVAL_MS);
};

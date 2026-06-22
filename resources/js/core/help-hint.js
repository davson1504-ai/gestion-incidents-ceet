function cleanText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim();
}

function normalize(value) {
    return cleanText(value)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function escapeHtml(value) {
    return String(value || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function titleFor(element) {
    const explicit = cleanText(element.dataset.helpTitle);

    if (explicit) {
        return explicit;
    }

    const node = element.querySelector([
        'h1',
        'h2',
        'h3',
        'h4',
        '.ceet-card-title',
        '.stat-label',
        '.kpi-label',
        '.metric-label',
        '.ceet-reports-admin-kpi-head',
        '.ceet-current-stat-label',
        'strong',
    ].join(','));

    return cleanText(node?.textContent) || 'Aide';
}

function helpFor(element, title) {
    const explicit = cleanText(element.dataset.help);

    if (explicit) {
        return explicit;
    }

    const text = normalize(`${title} ${cleanText(element.textContent)}`);

    if (text.includes('total en cours')) return "Nombre total d’incidents actuellement ouverts ou suivis en temps réel.";
    if (text.includes('priorite critique')) return "Nombre d’incidents critiques nécessitant une attention prioritaire.";
    if (text.includes('moyenne resolution') || text.includes('duree moyenne')) return "Temps moyen observé pour le traitement ou le rétablissement.";
    if (text.includes('equipes mobilisees')) return "Nombre d’équipes ou opérateurs mobilisés pour le traitement.";
    if (text.includes('total incidents')) return "Nombre total d’incidents enregistrés sur la période suivie.";
    if (text.includes('taux de resolution') || text.includes('taux resolution')) return "Pourcentage d’incidents clôturés par rapport au total.";
    if (text.includes('table') || text.includes('code') && text.includes('statut')) return "Tableau de suivi des données opérationnelles.";
    if (text.includes('utilisateurs')) return "Informations de synthèse sur les comptes utilisateurs.";
    if (text.includes('catalogue')) return "Catalogue de référence utilisé pour standardiser les données.";
    if (text.includes('rapport') || text.includes('distribution') || text.includes('repartition')) return "Bloc d’analyse ou de statistiques.";
    if (text.includes('vue du site')) return "Synthèse du site ou de la zone concernée.";

    return "Ce bloc donne une information utile pour comprendre ou utiliser cette partie de l’application.";
}

function isExcluded(element) {
    if (!element) {
        return true;
    }

    if (element.closest([
        '.ceet-sidebar',
        '.ceet-topbar',
        '.topbar',
        '.navbar',
        'nav',
        'header',
        '.ceet-current-sidebar',
        '.ceet-current-topbar',
        '.ceet-current-search',
        '.ceet-reports-admin-sidebar',
        '.ceet-reports-admin-topbar',
        '.ceet-reports-admin-search',
        '.ceet-users-admin-sidebar',
        '.ceet-users-admin-topbar',
        '.ceet-users-admin-search',
        '.ceet-catalogues-sidebar',
        '.ceet-catalogues-topbar',
        '.ceet-catalogues-search',
        '.ceet-help-trigger',
        '.ceet-help-panel',
    ].join(','))) {
        return true;
    }

    if (element.matches('button, a, input, select, textarea, label, th, td')) {
        return true;
    }

    if (element.matches([
        '.ceet-current-filter-panel',
        '.ceet-current-alert',
        '.ceet-current-icon-btn',
        '.ceet-current-square-btn',
        '.ceet-current-row-actions',
        '.ceet-reports-admin-filter-panel',
        '.ceet-reports-filter-panel',
        '.ceet-reports-admin-alert',
        '.ceet-reports-admin-field',
        '.ceet-users-admin-filter-panel',
        '.ceet-users-admin-alert',
        '.ceet-users-admin-action-btn',
        '.ceet-users-admin-row-actions',
    ].join(','))) {
        return true;
    }

    const text = normalize(element.textContent);

    return (
        text.includes('rechercher un incident') ||
        text.includes('filtres avances') ||
        text.includes('periode du rapport') ||
        text.includes('synchronisation scada') ||
        text.includes('derniere mise a jour') ||
        text.includes('synchronise') ||
        (text.includes('appliquer') && text.includes('reinitialiser')) ||
        (text.includes('filtrer') && element.querySelector('button, a, input, select'))
    );
}

function hasTopRightControl(element) {
    const rect = element.getBoundingClientRect();

    if (!rect.width || !rect.height) {
        return false;
    }

    return Array.from(element.querySelectorAll([
        'button',
        'a',
        'svg',
        'i',
        '.bi',
        '.material-symbols-outlined',
        '.btn',
        '.dropdown',
        '.dropdown-toggle',
        '[class*="icon"]',
        '[class*="Icon"]',
    ].join(','))).some((node) => {
        if (node.classList.contains('ceet-help-trigger') || node.closest('.ceet-help-panel')) {
            return false;
        }

        const nodeRect = node.getBoundingClientRect();

        if (!nodeRect.width || !nodeRect.height) {
            return false;
        }

        const centerX = nodeRect.left + nodeRect.width / 2;
        const centerY = nodeRect.top + nodeRect.height / 2;

        return (
            centerX > rect.right - Math.min(120, rect.width * 0.35) &&
            centerY < rect.top + Math.min(70, Math.max(45, rect.height * 0.35))
        );
    });
}

const AUTO_SELECTORS = [
    '[data-help]',
    '.ceet-helpable',

    '.ceet-current-stat-card',
    '.ceet-current-table-panel',
    '.ceet-current-info-card',

    '.ceet-reports-admin-kpi-card',
    '.ceet-reports-admin-panel',
    '.ceet-reports-admin-export-box',

    '.ceet-users-admin-stats > *',
    '.ceet-users-admin-table-card',

    '.ceet-catalogue-card',
    '.ceet-catalogues-footer-panel',
    '.ceet-catalogue-metric-card',
    '.ceet-catalogue-table-card',
    '.ceet-catalog-table-card',
    '.ceet-catalog-summary-card',

    '.ceet-admin-kpi-card',
    '.ceet-admin-panel',
    '.ceet-dashboard-kpi',
    '.ceet-dashboard-kpi-card',
    '.ceet-dashboard-panel',
    '.ceet-dashboard-table-card',
    '.ceet-kpi-card',
    '.ceet-supervisor-kpi-card',
    '.ceet-supervisor-panel',
    '.ceet-supervisor-chart-panel',
    '.ceet-supervisor-exposed-panel',
    '.ceet-incident-kpi-card',
    '.ceet-incidents-kpi-card',
    '.incident-stat-card',
    '.stat-card',
    '.ceet-incidents-summary-card',
    '.ceet-incidents-table-card',
    '.ceet-incident-list-card',
    '.incidents-table-card',
    '.content-card',
    '.form-card',
    '.table-card',
    '.users-stat-card',
    '.users-table-card',
];

function candidates(root = document) {
    const found = new Set();

    AUTO_SELECTORS.forEach((selector) => {
        root.querySelectorAll(selector).forEach((element) => found.add(element));
    });

    return Array.from(found).filter((element) => {
        if (isExcluded(element)) {
            return false;
        }

        const rect = element.getBoundingClientRect();

        if (rect.width < 100 || rect.height < 44) {
            return false;
        }

        return true;
    });
}

function mount(element) {
    if (element.dataset.ceetHelpReady === 'true') {
        return;
    }

    element.classList.add('ceet-helpable');
    element.classList.remove('ceet-help-offset-right', 'ceet-help-offset-right-lg');

    element.querySelectorAll(':scope > .ceet-help-trigger, :scope > .ceet-help-panel').forEach((node) => node.remove());

    if (hasTopRightControl(element)) {
        element.classList.add('ceet-help-offset-right');
    }

    const title = titleFor(element);
    const help = helpFor(element, title);

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'ceet-help-trigger';
    trigger.textContent = '!';
    trigger.setAttribute('aria-label', `Aide : ${title}`);
    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
    });

    const panel = document.createElement('div');
    panel.className = 'ceet-help-panel';
    panel.setAttribute('role', 'tooltip');
    panel.innerHTML = `
        <p class="ceet-help-title">${escapeHtml(title)}</p>
        <p class="ceet-help-text">${escapeHtml(help)}</p>
    `;

    // Important : ajout direct dans la carte, jamais dans document.body.
    // Le rond défile donc avec le rectangle.
    element.appendChild(trigger);
    element.appendChild(panel);
    element.dataset.ceetHelpReady = 'true';
}

function cleanupFloatingNodes() {
    document.querySelectorAll('.ceet-help-trigger, .ceet-help-panel').forEach((node) => {
        if (!node.parentElement?.classList.contains('ceet-helpable')) {
            node.remove();
        }
    });
}

function scan(root = document) {
    candidates(root).forEach(mount);
    cleanupFloatingNodes();
}

export function initHelpHints() {
    if (window.CEET_HELP_HINTS_READY) {
        scan(document);
        return;
    }

    window.CEET_HELP_HINTS_READY = true;

    const boot = () => {
        scan(document);

        const observer = new MutationObserver(() => {
            window.requestAnimationFrame(() => scan(document));
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });

        window.CEET_HELPABLE_COUNT = () => document.querySelectorAll('.ceet-help-trigger').length;
        window.CEET_HELPABLE_TARGETS = () => Array.from(document.querySelectorAll('.ceet-helpable')).map((node) => ({
            tag: node.tagName,
            className: node.className,
            text: cleanText(node.textContent).slice(0, 90),
        }));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
}

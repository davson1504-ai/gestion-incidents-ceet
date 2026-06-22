import './bootstrap';
import { initNavigation } from './core/navigation';
import { initNotifications } from './core/notifications';
import { initAjaxForms } from './core/ajax-forms';
import { initToasts } from './core/toasts';
import { initHelpHints } from './core/help-hint';
const boot = () => {
    window.CEET = window.CEET || {};
    window.CEET.appReady = true;
    initNavigation();
    initNotifications();
    initAjaxForms();
    initToasts();
};
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}

initHelpHints();

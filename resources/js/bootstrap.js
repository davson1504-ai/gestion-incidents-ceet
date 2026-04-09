/**
 * Bootstrap JS est chargé via CDN dans resources/views/layouts/app.blade.php.
 */

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Laravel Echo + Reverb (WebSocket temps réel)
 *
 * ✅ CORRECTION LENTEUR: On initialise Echo uniquement si les variables
 * d'environnement Reverb sont définies, et avec un timeout court.
 * Sinon la page attend 30s+ que la connexion WebSocket échoue.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey    = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost   = import.meta.env.VITE_REVERB_HOST ?? 'localhost';
const reverbPort   = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

// On ne tente la connexion que si la clé est configurée
if (reverbKey && reverbKey !== 'null' && reverbKey !== '') {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbPort,
            forceTLS: reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
            // ✅ Timeouts courts : si Reverb n'est pas démarré, on abandonne vite
            activityTimeout: 5000,
            pongTimeout: 2000,
            disableStats: true,
        });
        window.EchoReady = true;
    } catch (e) {
        console.warn('[Echo] Connexion Reverb impossible:', e.message);
        window.Echo = null;
        window.EchoReady = false;
    }
} else {
    // Reverb non configuré → pas de WebSocket, pas de blocage
    window.Echo = null;
    window.EchoReady = false;
    console.info('[Echo] Reverb non configuré — temps réel désactivé.');
}
import 'bootstrap';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST ?? 'localhost';
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

if (reverbKey && reverbKey !== 'null') {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbPort,
            forceTLS: reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
            activityTimeout: 5000,
            pongTimeout: 2000,
            disableStats: true,
        });
        window.EchoReady = true;
    } catch (error) {
        console.warn('[Echo] Reverb init failed:', error?.message ?? error);
        window.Echo = null;
        window.EchoReady = false;
    }
} else {
    window.Echo = null;
    window.EchoReady = false;
}

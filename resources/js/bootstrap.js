import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo setup (using Pusher-compatible client)
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Use native WebSocket server if available via Reverb or Pusher
window.Pusher = Pusher;

const broadcastDriver = import.meta.env.VITE_BROADCAST_DRIVER || 'reverb';

if (broadcastDriver === 'pusher') {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
        wsPort: Number(import.meta.env.VITE_PUSHER_PORT || 6001),
        wssPort: Number(import.meta.env.VITE_PUSHER_PORT || 6001),
        forceTLS: String(import.meta.env.VITE_PUSHER_FORCE_TLS || '').toLowerCase() === 'true',
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
            }
        }
    });
} else {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY || 'local',
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
            }
        }
    });
}

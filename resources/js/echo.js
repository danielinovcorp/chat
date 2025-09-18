import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher; // agora o conector pusher encontra o client

const appKey = import.meta.env.VITE_REVERB_APP_KEY;
const host   = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
const scheme = (import.meta.env.VITE_REVERB_SCHEME ?? (location.protocol === 'https:' ? 'https' : 'http'));
const port   = Number(import.meta.env.VITE_REVERB_PORT ?? (scheme === 'https' ? 443 : 80));
const forceTLS = scheme === 'https';

window.Echo = new Echo({
  broadcaster: 'pusher',          // <- usa o conector pusher
  key: appKey,
  wsHost: host,
  wsPort: port,
  wssPort: port,
  forceTLS,
  enabledTransports: ['ws', 'wss'],
  cluster: 'mt1',              // não é usado com Reverb
  disableStats: true,              // limpa pings do Pusher

  authEndpoint: '/broadcasting/auth',
  auth: {
    withCredentials: true,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    },
  },
});

if (import.meta.env.DEV) {
  console.debug('[echo] init via Pusher→Reverb', { host, port, forceTLS });
}

// >>> avisa o front que o Echo está pronto
document.dispatchEvent(new CustomEvent('echo:ready'));
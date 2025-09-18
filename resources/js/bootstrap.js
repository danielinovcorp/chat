import axios from 'axios';
window.axios = axios;

// Requisições AJAX padrão do Laravel
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF: lê do <meta name="csrf-token" ...> gerado pelo Blade
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrf) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
} else if (import.meta.env.DEV) {
  console.warn('[bootstrap] meta[name="csrf-token"] não encontrado — verifique o layout.');
}

// Antes de cada request, anexa X-Socket-Id (para o broadcast ignorar o próprio cliente)
window.axios.interceptors.request.use((config) => {
  try {
    const socketId = window?.Echo?.socketId?.();
    if (socketId) config.headers['X-Socket-Id'] = socketId;
  } catch {}
  return config;
});

// Echo (WebSockets via Reverb)
import './echo';

// Logs em dev
if (import.meta.env.DEV) {
  console.debug('[bootstrap] carregado. APP_URL:', import.meta.env.VITE_APP_URL || window.location.origin);
}

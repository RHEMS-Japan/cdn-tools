// Bootstrap 5
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

// Vue 3
import { createApp } from 'vue';
import PurgeApp from './components/PurgeApp.vue';

// Axios
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Bootstrap JS (components)
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/dropdown';

// Check if we're on the purge page (has #app element)
const appEl = document.getElementById('app');
if (appEl) {
  const service = document.getElementById('service')?.value || '';
  const account = document.getElementById('account')?.value || '';
  const defaultsInput = document.getElementById('defaults')?.value || '';
  const defaults = defaultsInput ? defaultsInput.split(',').map((v, i) => ({ text: v, value: v })) : [];

  const app = createApp(PurgeApp, {
    service: service,
    account: account,
    defaults: defaults,
  });

  app.mount('#app');
}

// Queue update button
document.getElementById('update-queue-btn')?.addEventListener('click', function() {
  const service = document.getElementById('service')?.value || '';
  const account = document.getElementById('account')?.value || '';
  const data = new FormData();
  data.append('service', service);
  data.append('account', account);
  axios.post('/ajax/update', data)
    .then(() => location.reload())
    .catch(error => console.error('Update failed:', error));
});
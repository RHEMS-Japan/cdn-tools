// Bootstrap 5
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

// Vue 3
import { createApp } from 'vue';
import PurgeModal from './components/purge-modal.vue';

// Axios
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Bootstrap JS (components)
import 'bootstrap/js/dist/modal';
import 'bootstrap/js/dist/dropdown';

// Create Vue app
const app = createApp({});
app.component('purge-modal', PurgeModal);
app.mount('#app');
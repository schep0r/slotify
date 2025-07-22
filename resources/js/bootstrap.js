import axios from 'axios';
import { createApp } from 'vue';
import router from './router';
import { createPinia } from 'pinia'
import App from './App.vue';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const pinia = createPinia()
const app = createApp(App);

app.use(router);
app.use(pinia)
app.mount('#app');

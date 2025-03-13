import { createApp } from 'vue';
import router from './router';
import { createPinia } from 'pinia';
import App from './App.vue';
import axios from 'axios';

// Fix API base URL
axios.defaults.baseURL = '/api'; 
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

axios.interceptors.response.use(response => response, error => {
    if (error.response?.status === 401) {
        localStorage.removeItem('auth_token');
        router.push('/login');
    }
    return Promise.reject(error);
});

const app = createApp(App);
app.use(router);
app.use(createPinia());
app.mount('#app');

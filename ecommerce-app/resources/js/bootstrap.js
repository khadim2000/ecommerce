import axios from 'axios';
window.axios = axios;

// Base URL dynamique
//window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://localhost:8000';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    window.axios.defaults.withCredentials = true;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Intercepteur 419
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            console.warn('CSRF token expired, refreshing...');
            axios.get('/api/csrf-token', { withCredentials: true })
                .then(response => {
                    if (response.data.csrf_token) {
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token;
                        const metaTag = document.head.querySelector('meta[name="csrf-token"]');
                        if (metaTag) metaTag.setAttribute('content', response.data.csrf_token);
                        return axios.request(error.config);
                    }
                })
                .catch(() => window.location.reload());
        }
        return Promise.reject(error);
    }
);

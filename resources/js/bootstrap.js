import axios from 'axios';

window.axios = axios;

// Required headers
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Always enforce HTTPS API endpoint
window.axios.defaults.baseURL =
    import.meta.env.VITE_API_URL ||
    window.location.origin.replace(/^http:/, 'https:');

// Ensure CSRF token is included if present
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
else {
    console.error(
        'CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token',
    );
}

// Enable sending of cookies with CORS requests
window.axios.defaults.withCredentials = true;

// Optional: Set a default timeout for requests (in milliseconds)
window.axios.defaults.timeout = 10000; // 10 seconds

// Optional: Add a response interceptor to handle global responses
window.axios.interceptors.response.use(
    response => response,
    error => {
        // Handle global errors (e.g., logging, notifications)
        console.error('Axios error:', error);
        return Promise.reject(error);
    }
);
import axios from 'axios';

export const apiClient = axios.create({
    baseURL: 'http://localhost:80',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
    withXSRFToken: true,
});
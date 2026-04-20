import axios from 'axios';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
    withCredentials: true,
});

export const unwrapPayload = (response) => {
    const payload = response?.data;

    if (payload && Object.prototype.hasOwnProperty.call(payload, 'success')) {
        return payload.data;
    }

    return payload;
};

export default api;

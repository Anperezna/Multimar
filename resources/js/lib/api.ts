import axios from 'axios';

const configuredBaseUrl = import.meta.env.VITE_API_BASE_URL?.trim();

const api = axios.create({
  baseURL: configuredBaseUrl || '/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
});

export default api;
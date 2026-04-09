import axios from 'axios';

const configuredBaseUrl = import.meta.env.VITE_API_BASE_URL?.trim();
const normalizedBaseUrl = configuredBaseUrl
  ? configuredBaseUrl.replace(/\/+$/, '').endsWith('/api')
    ? configuredBaseUrl.replace(/\/+$/, '')
    : `${configuredBaseUrl.replace(/\/+$/, '')}/api`
  : '/api';

const normalizeBaseUrl = (rawUrl?: string): string => {
  if (!rawUrl) {
    return '/api';
  }

  const noTrailingSlash = rawUrl.replace(/\/+$/, '');

  if (noTrailingSlash.endsWith('/api')) {
    return noTrailingSlash;
  }

  return `${noTrailingSlash}/api`;
};

const api = axios.create({
  baseURL: normalizedBaseUrl,
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

export default api;
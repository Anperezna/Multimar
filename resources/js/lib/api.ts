import axios from 'axios';

const configuredBaseUrl = import.meta.env.VITE_API_BASE_URL?.trim();

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
  baseURL: normalizeBaseUrl(configuredBaseUrl),
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
});

export default api;
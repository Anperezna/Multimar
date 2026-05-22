import axios from 'axios';

// 1. Configuración de la URL base
const configuredBaseUrl = import.meta.env.VITE_API_BASE_URL?.trim();

const normalizeBaseUrl = (rawUrl?: string): string => {
  if (!rawUrl) return '/api';
  const noTrailingSlash = rawUrl.replace(/\/+$/, '');
  return noTrailingSlash.endsWith('/api') ? noTrailingSlash : `${noTrailingSlash}/api`;
};

const normalizedBaseUrl = normalizeBaseUrl(configuredBaseUrl);

// 2. Obtención del token CSRF desde el DOM
const csrfTokenElement = document.head.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfTokenElement ? (csrfTokenElement as HTMLMetaElement).content : '';

if (!csrfToken) {
  console.error('CSRF token no encontrado. Verifica que <meta name="csrf-token"> esté en tu app.blade.php');
}

// 3. Función para obtener el token CSRF actual
const getCurrentCsrfToken = (): string => {
  const tokenElement = document.head.querySelector('meta[name="csrf-token"]');
  return tokenElement ? (tokenElement as HTMLMetaElement).content : csrfToken;
};

// 4. Función para crear una instancia con configuración base
const createApiInstance = (timeout?: number) => {
  const instance = axios.create({
    baseURL: normalizedBaseUrl,
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': getCurrentCsrfToken()
    },
    timeout: timeout || 30000 // 30 segundos por defecto
  });

  // Interceptor para inyectar token de autenticación y refrescar CSRF
  instance.interceptors.request.use((config) => {
    const authToken = localStorage.getItem('auth_token');
    if (authToken) {
      config.headers.Authorization = `Bearer ${authToken}`;
    }

    // Refrescar CSRF dinámicamente
    config.headers['X-CSRF-TOKEN'] = getCurrentCsrfToken();

    return config;
  });

  // Interceptor para manejar errores de autenticación
  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        console.error('Error 401:', error.config?.url);
        
        // No redirigir en peticiones del chatbot, solo rechazar el error
        if (error.config?.url?.includes('/chatbot/')) {
          return Promise.reject(error);
        }
        
        // Para otras peticiones, redirigir a login
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
      }
      return Promise.reject(error);
    }
  );

  return instance;
};

// 5. Instancias de API
const api = createApiInstance(30000); // 30 segundos para peticiones normales
export const chatbotApi = createApiInstance(310000); // 310 segundos para el chatbot

// 6. Configuración global de axios para el resto de la app
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

export { createApiInstance };
export default api;
import { createRouter, createWebHistory } from 'vue-router';
import api from '@/lib/api';

import Login from '../pages/Login.vue';
import Welcome from '../pages/Welcome.vue';
import Home from '@/pages/Home.vue';
import Clientes from '@/pages/Clientes.vue';
import EditarPerfil from '@/pages/EditarPerfil.vue';
import EditarContrasena from '@/pages/EditarContrasena.vue';
import Accesibilidad from '@/pages/Accesibilidad.vue';
import Incoterm from '@/pages/Incoterm.vue';
import SolicitudOferta from '@/pages/SolicitudOferta.vue';
import Ofertas from '@/pages/Ofertas.vue';
import DetalleEnvio from '@/pages/DetalleEnvio.vue';


const routes = [
    {
        path: '/',
        component: Welcome,
    },
    {
        path: '/login',
        component: Login,
    },
    {
        path: '/home',
        component: Home
    },
    {
        path: '/clientes',
        component: Clientes
    },
    {
        path: '/ajustes',
        component: EditarPerfil
    },
    {
        path: '/ajustes/contrasena',
        component: EditarContrasena
    },
    {
        path: '/ajustes/accesibilidad',
        component: Accesibilidad
    },
    {
        path: '/incoterm',
        component: Incoterm
    },
    {
        path: '/solicitudOferta',
        component: SolicitudOferta
    },
    {
        path: '/ofertas',
        component: Ofertas,
    },
    {
        path: '/ofertas/:id',
        name: 'detalle-oferta',
        component: DetalleEnvio
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const publicPaths = ['/', '/login'];
let syncingUserRole = false;

const syncUserRoleInBackground = () => {
    if (syncingUserRole) {
        return;
    }

    syncingUserRole = true;

    api.get('/user')
        .then(({ data }) => {
            const resolvedRole =
                typeof data?.rol === 'string'
                    ? data.rol
                    : data?.rol?.rol || '';
            const resolvedName =
                [data?.nombre ?? data?.nom, data?.apellidos ?? data?.cognoms]
                    .filter(Boolean)
                    .join(' ')
                    .trim() ||
                data?.name ||
                data?.correo ||
                data?.correu ||
                '';

            if (resolvedRole) {
                localStorage.setItem('user_rol', resolvedRole);
            }

            if (resolvedName) {
                localStorage.setItem('user_name', resolvedName);
            }

            if (resolvedRole || resolvedName) {
                window.dispatchEvent(new Event('auth-updated'));
            }
        })
        .catch(() => {
            // Do not interrupt navigation if background sync fails.
        })
        .finally(() => {
            syncingUserRole = false;
        });
};

router.beforeEach((to) => {
    if (publicPaths.includes(to.path)) {
        return true;
    }

    const token = localStorage.getItem('auth_token');

    if (!token) {
        return '/login';
    }

    if (!localStorage.getItem('user_rol')) {
        syncUserRoleInBackground();
    }

    return true;
});

export default router;

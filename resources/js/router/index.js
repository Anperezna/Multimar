import { createRouter, createWebHistory } from 'vue-router';

import Login from '../pages/Login.vue';
import Welcome from '../pages/Welcome.vue';
import Home from '@/pages/Home.vue';
import EditarPerfil from '@/pages/EditarPerfil.vue';
import EditarContrasena from '@/pages/EditarContrasena.vue';
import Accesibilidad from '@/pages/Accesibilidad.vue';

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
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

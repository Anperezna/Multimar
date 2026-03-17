import { createRouter, createWebHistory } from 'vue-router';

import Login from '../pages/Login.vue';
import Welcome from '../pages/Welcome.vue';
import Home from '@/pages/Home.vue';
import SolicitudOferta from '@/pages/SolicitudOferta.vue';
import Incoterm from '@/pages/Incoterm.vue';

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
        path: '/solicitudOferta',
        component: SolicitudOferta
    },
    {
        path: '/incoterm',
        component: Incoterm
    }

];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

import { createRouter, createWebHistory } from 'vue-router';

import Login from '../pages/Login.vue';
import Welcome from '../pages/Welcome.vue';
import Home from '@/pages/Home.vue';
import Clientes from '@/pages/Clientes.vue';
import EditarPerfil from '@/pages/EditarPerfil.vue';
import EditarContrasena from '@/pages/EditarContrasena.vue';
import Accesibilidad from '@/pages/Accesibilidad.vue';
import Incoterm from '@/pages/Incoterm.vue';
import SolicitudOferta from '@/pages/SolicitudOferta.vue';
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
        path: '/ofertas/:id',
        name: 'detalle-oferta',
        component: DetalleEnvio
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

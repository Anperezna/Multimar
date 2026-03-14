import { createRouter, createWebHistory } from 'vue-router';

import Login from '../pages/Login.vue';
import Welcome from '../pages/Welcome.vue';
import Home from '@/pages/Home.vue';

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
        path: '/main',
        component: Home
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

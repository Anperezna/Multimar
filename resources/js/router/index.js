import { createRouter, createWebHistory } from 'vue-router';

import Login from '../pages/Login.vue';
import Welcome from '../pages/Welcome.vue';

const routes = [
    {
        path: '/',
        component: Welcome,
    },
    {
        path: '/login',
        component: Login,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

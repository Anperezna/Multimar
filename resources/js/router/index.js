import { createRouter, createWebHistory } from "vue-router";

import Welcome from "../pages/Welcome.vue";
import Login from "../pages/Login.vue";


const routes = [
    {
        path: "/",
        component: Welcome
    },
    {
        path: "/login",
        component: Login
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
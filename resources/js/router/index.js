import { createRouter, createWebHistory } from 'vue-router';
import GamePage from "../views/GamePage.vue";
import GamesPage from "../views/GamesPage.vue";
import LoginPage from "../views/LoginPage.vue";
import { useAuthStore } from '../stores/authStore.js';

const routes = [
    {
        path: '/',
        redirect: '/games'
    },
    {
        path: '/login',
        name: 'Login',
        component: LoginPage,
        meta: { requiresGuest: true }
    },
    {
        path: '/games',
        name: 'Games',
        component: GamesPage,
        meta: { requiresAuth: true }
    },
    {
        path: '/games/:gameId',
        name: 'Game',
        component: GamePage,
        props: true,
        meta: { requiresAuth: true }
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guards
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    // Check if route requires authentication
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
        return;
    }

    // Check if route requires guest (not authenticated)
    if (to.meta.requiresGuest && authStore.isAuthenticated) {
        next('/games');
        return;
    }

    next();
});

export default router;

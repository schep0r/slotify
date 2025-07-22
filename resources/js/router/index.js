import { createRouter, createWebHistory } from 'vue-router';
import GameLayout from "../components/Layout/GameLayout.vue";

const routes = [
    {
        path: '/',
        component: GameLayout,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

import { createRouter, createWebHistory } from 'vue-router';
import GamePage from "../views/GamePage.vue";
import GamesPage from "../views/GamesPage.vue";

const routes = [
    {
        path: '/games',
        component: GamesPage,
    },
    {
        path: '/games/:gameId',
        component: GamePage,
        props: true,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

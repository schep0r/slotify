<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();
const props = defineProps({
    game: {
        type: Object,
        required: true,
        validator: (value) => {
            // Basic validation to ensure we have at least an id and name
            return ['id', 'name'].every(prop => prop in value);
        }
    }
});

const navigateToGame = () => {
    router.push(`/games/${props.game.id}`);
};
</script>

<template>
    <div class="bg-gray-700 rounded-xl shadow-lg overflow-hidden transform transition-transform duration-300 hover:scale-105 hover:shadow-xl flex flex-col">
        <!-- Use a placeholder image if imageUrl is not available -->
        <img
            :src="game.imageUrl || `https://placehold.co/150x100/${Math.floor(Math.random()*999999).toString(16)}/FFFFFF?text=${game.name}`"
            :alt="game.name"
            class="w-full h-40 object-cover border-b-2 border-gray-600"
        >
        <div class="p-5 flex flex-col flex-grow">
            <h3 class="text-2xl font-bold text-yellow-300 mb-2">{{ game.name }}</h3>
            <div class="flex justify-between items-center mb-3">
                <p class="text-sm text-gray-300">{{ game.type || 'Unknown Type' }}</p>
                <span v-if="game.status" class="text-xs px-2 py-1 rounded-full"
                      :class="game.status === 'active' ? 'bg-green-600' : 'bg-gray-600'">
                    {{ game.status }}
                </span>
            </div>
            <div class="text-gray-400 text-base flex-grow">
                <!-- Show slug if description is not available -->
                <p v-if="game.description">{{ game.description }}</p>
                <p v-else-if="game.slug">{{ game.slug }}</p>
                <div v-else class="space-y-2">
                    <p v-if="game.provider" class="text-sm">Provider: {{ game.provider }}</p>
                    <p v-if="game.min_bet && game.max_bet" class="text-sm">Bet range: {{ game.min_bet }} - {{ game.max_bet }}</p>
                    <p v-if="game.rtp" class="text-sm">RTP: {{ game.rtp }}%</p>
                    <p v-if="game.reels && game.rows" class="text-sm">Grid: {{ game.reels }}x{{ game.rows }}</p>
                    <p v-if="game.paylines" class="text-sm">Paylines: {{ game.paylines }}</p>
                </div>
            </div>
            <button
                @click="navigateToGame"
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full transition-colors duration-200 shadow-md">
                Грати зараз
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Add any specific styles for GameCard.vue here if needed */
</style>

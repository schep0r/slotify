<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useGameStore } from '../stores/gameStore.js';
import { useAuthStore } from '../stores/authStore.js';
import BalanceDisplay from '../components/UI/BalanceDisplay.vue';
import BetControls from '../components/UI/BetControls.vue';
import SpinButton from '../components/UI/SpinButton.vue';
import SlotMachine from '../components/SlotMachine/SlotMachine.vue';
import PayoutTable from '../components/SlotMachine/PayoutTable.vue';

const props = defineProps({
    gameId: {
        type: String,
        required: false
    }
});

const route = useRoute();
const gameStore = useGameStore();
const authStore = useAuthStore();

const handleSpin = async () => {
    await gameStore.spin();
};

const handleBetChange = (newBet) => {
    gameStore.setBetAmount(newBet);
};

onMounted(() => {
    // Use the gameId from props or from route params
    const gameId = props.gameId || route.params.gameId;

    // If we have a gameId, we could use it to fetch specific game configuration
    // For now, just log it and call the existing fetchConfiguration method
    if (gameId) {
        console.log(`Loading game with ID: ${gameId}`);
    }

    gameStore.fetchConfiguration(gameId);
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-purple-900 to-indigo-900 text-white flex flex-col items-center justify-center p-4 font-inter">
        <h1 class="text-5xl font-extrabold mb-8 text-yellow-400 drop-shadow-lg animate-pulse">
            СЛОТ МАШИНА
        </h1>

        <div class="bg-gray-800 bg-opacity-70 rounded-3xl p-8 shadow-2xl border-4 border-yellow-500 flex flex-col items-center space-y-8 max-w-4xl w-full">
            <!-- UI/BalanceDisplay.vue -->
            <BalanceDisplay :balance="authStore.userBalance" :freeSpins="gameStore.freeSpins" />

            <!-- SlotMachine/SlotMachine.vue -->
            <SlotMachine :visibleSymbols="gameStore.visibleSymbols" :winningLines="gameStore.winningLines" />

            <!-- Message display -->
            <div class="h-10 text-center text-lg font-semibold text-yellow-300">
                {{ gameStore.message }}
            </div>

            <div class="flex flex-col md:flex-row items-center justify-around w-full space-y-6 md:space-y-0 md:space-x-8">
                <!-- UI/BetControls.vue -->
                <BetControls :betAmount="gameStore.betAmount" @bet-change="handleBetChange" />

                <!-- SpinButton.vue -->
                <SpinButton @spin="handleSpin" :isLoading="gameStore.isLoading" />
            </div>

            <!-- SlotMachine/PayoutTable.vue -->
            <PayoutTable />
        </div>
    </div>
</template>

<style scoped>
/* Add any specific styles for App.vue here if needed */
</style>

<script setup>
import { useGameStore } from './../../stores/gameStore'; // Import the Pinia store

const gameStore = useGameStore();
</script>

<template>
    <div class="bg-gray-700 rounded-xl p-6 shadow-inner w-full max-w-md">
        <h3 class="text-2xl font-bold text-center mb-4 text-yellow-300">Таблиця Виплат</h3>
        <div class="space-y-2">
            <div v-for="(item, index) in gameStore.payouts" :key="index" class="flex justify-between items-center bg-gray-600 p-3 rounded-md text-lg">
                <span class="font-semibold">{{ item.combo }}</span>
                <span class="text-green-400 font-bold">{{ item.payout }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any specific styles for PayoutTable.vue here if needed */
</style>

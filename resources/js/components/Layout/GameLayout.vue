<script setup>
import { ref, computed } from 'vue';
import BalanceDisplay from './../UI/BalanceDisplay.vue';
import BetControls from './../UI/BetControls.vue';
import SpinButton from './../UI/SpinButton.vue';
import SlotMachine from './../SlotMachine/SlotMachine.vue';
import PayoutTable from './../SlotMachine/PayoutTable.vue';

const balance = ref(1000);
const betAmount = ref(10);
const freeSpins = ref(0);
const visibleSymbols = ref([
    ['🍒', '🍋', '🍊', '🍊', '🍊'],
    ['🍋', '🍒', '🍊', '🍊', '🍊'],
    ['🍋', '🍒', '🍊', '🍊', '🍊'],
    ['🍊', '🍋', '🍒', '🍊', '🍊'],
    ['🍊', '🍋', '🍒', '🍊', '🍊'],
]);
const winningLines = ref([]);
const totalPayout = ref(0);
const isJackpot = ref(false);
const multiplier = ref(1);
const isLoading = ref(false);
const message = ref('');

// Define available symbols and their weights for simulation
const allSymbols = ['🍒', '🍋', '🍊', '🔔', '💎', '⭐', '7️⃣'];
const symbolWeights = {
    '🍒': 0.2, '🍋': 0.2, '🍊': 0.15, '🔔': 0.15, '💎': 0.1, '⭐': 0.1, '7️⃣': 0.1
};

// Helper to get a random symbol based on weights
const getRandomSymbol = () => {
    let rand = Math.random();
    let cumulativeWeight = 0;
    for (const symbol of allSymbols) {
        cumulativeWeight += symbolWeights[symbol];
        if (rand < cumulativeWeight) {
            return symbol;
        }
    }
    return allSymbols[0]; // Fallback
};

// Mock backend spin function
const simulateSpin = () => {
    // Simulate reel positions (not directly used for display, but good for backend logic)
    const reelPositions = Array(3).fill(0).map(() => Math.floor(Math.random() * 100)); // Example positions

    // Simulate visible symbols (3 reels, 3 rows each)
    const newVisibleSymbols = Array(5).fill(0).map(() =>
        Array(5).fill(0).map(() => getRandomSymbol())
    );

    // Simulate winning lines and payout
    let simulatedWinningLines = [];
    let simulatedTotalPayout = 0;
    let simulatedIsJackpot = false;
    let simulatedMultiplier = 1;
    let simulatedFreeSpinsAwarded = 0;

    // Simple winning logic: check for 3 identical symbols in the middle row
    if (newVisibleSymbols[0][1] === newVisibleSymbols[1][1] && newVisibleSymbols[1][1] === newVisibleSymbols[2][1]) {
        simulatedWinningLines.push(1); // Indicate the middle row (index 1) as a win
        const winningSymbol = newVisibleSymbols[0][1];
        switch (winningSymbol) {
            case '🍒': simulatedTotalPayout = 50; break;
            case '🍋': simulatedTotalPayout = 75; break;
            case '🍊': simulatedTotalPayout = 100; break;
            case '🔔': simulatedTotalPayout = 150; break;
            case '💎': simulatedTotalPayout = 200; break;
            case '⭐': simulatedTotalPayout = 300; break;
            case '7️⃣':
                simulatedTotalPayout = 1000;
                simulatedIsJackpot = true;
                simulatedFreeSpinsAwarded = 5; // Award free spins for jackpot
                simulatedMultiplier = 2; // Example multiplier for jackpot
                break;
            default: simulatedTotalPayout = 0; break;
        }
    }

    // Apply multiplier if any
    simulatedTotalPayout *= simulatedMultiplier;

    const newBalance = balance.value - betAmount.value + simulatedTotalPayout;

    return {
        reelPositions,
        visibleSymbols: newVisibleSymbols,
        winningLines: simulatedWinningLines,
        totalPayout: simulatedTotalPayout,
        newBalance,
        isJackpot: simulatedIsJackpot,
        multiplier: simulatedMultiplier,
        freeSpinsAwarded: simulatedFreeSpinsAwarded
    };
};

const handleSpin = async () => {
    if (isLoading.value || (balance.value < betAmount.value && freeSpins.value === 0)) {
        message.value = "Недостатньо коштів або вже обертається.";
        return;
    }

    isLoading.value = true;
    message.value = '';
    winningLines.value = [];
    totalPayout.value = 0;
    isJackpot.value = false;
    multiplier.value = 1;

    let currentBalance = balance.value;
    let currentFreeSpins = freeSpins.value;

    if (currentFreeSpins > 0) {
        currentFreeSpins -= 1;
        freeSpins.value = currentFreeSpins;
        message.value = `Використано безкоштовний спін! Залишилось: ${currentFreeSpins}`;
    } else {
        currentBalance -= betAmount.value;
        balance.value = currentBalance;
    }

    // Simulate network delay
    await new Promise(resolve => setTimeout(resolve, 1500));

    const result = simulateSpin();

    visibleSymbols.value = result.visibleSymbols;
    winningLines.value = result.winningLines;
    totalPayout.value = result.totalPayout;
    isJackpot.value = result.isJackpot;
    multiplier.value = result.multiplier;
    freeSpins.value += result.freeSpinsAwarded;
    balance.value = result.newBalance;

    if (result.totalPayout > 0) {
        message.value = `Ви виграли ${result.totalPayout}! ${result.isJackpot ? 'ДЖЕКПОТ!' : ''} ${result.multiplier > 1 ? `Множник: x${result.multiplier}` : ''} ${result.freeSpinsAwarded > 0 ? `Надано ${result.freeSpinsAwarded} безкоштовних обертань!` : ''}`;
    } else {
        message.value = 'Спробуйте ще раз!';
    }

    isLoading.value = false;
};

const handleBetChange = (newBet) => {
    betAmount.value = newBet;
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-purple-900 to-indigo-900 text-white flex flex-col items-center justify-center p-4 font-inter">
        <h1 class="text-5xl font-extrabold mb-8 text-yellow-400 drop-shadow-lg animate-pulse">
            СЛОТ МАШИНА
        </h1>

        <div class="bg-gray-800 bg-opacity-70 rounded-3xl p-8 shadow-2xl border-4 border-yellow-500 flex flex-col items-center space-y-8 max-w-4xl w-full">
            <!-- UI/BalanceDisplay.vue -->
            <BalanceDisplay :balance="balance" :freeSpins="freeSpins" />

            <!-- SlotMachine/SlotMachine.vue -->
            <SlotMachine :visibleSymbols="visibleSymbols" :winningLines="winningLines" />

            <!-- Message display -->
            <div class="h-10 text-center text-lg font-semibold text-yellow-300">
                {{ message }}
            </div>

            <div class="flex flex-col md:flex-row items-center justify-around w-full space-y-6 md:space-y-0 md:space-x-8">
                <!-- UI/BetControls.vue -->
                <BetControls :betAmount="betAmount" @bet-change="handleBetChange" />

                <!-- SpinButton.vue -->
                <SpinButton @spin="handleSpin" :isLoading="isLoading" />
            </div>

            <!-- SlotMachine/PayoutTable.vue -->
            <PayoutTable />
        </div>
    </div>
</template>

<style scoped>
/* Add any specific styles for App.vue here if needed */
</style>

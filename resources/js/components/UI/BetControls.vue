<script setup>
const props = defineProps({
    betAmount: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['betChange']);

const bets = [5, 10, 20, 50, 100];

const selectBet = (bet) => {
    emit('betChange', bet);
};
</script>

<template>
    <div class="flex flex-col items-center space-y-4 bg-gray-700 rounded-xl p-6 shadow-inner">
        <h3 class="text-xl font-bold text-yellow-300">Ваша Ставка: ${{ betAmount }}</h3>
        <div class="flex space-x-2">
            <button
                v-for="bet in bets"
                :key="bet"
                @click="selectBet(bet)"
                :class="`px-6 py-3 rounded-full font-bold text-lg transition-all duration-200
                    ${betAmount === bet ? 'bg-yellow-500 text-gray-900 shadow-lg' : 'bg-gray-600 text-white hover:bg-gray-500'}`"
            >
                ${{ bet }}
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Add any specific styles for BetControls.vue here if needed */
</style>

<script setup>
import SlotSymbol from './SlotSymbol.vue';

const props = defineProps({
    symbols: {
        type: Array,
        required: true
    },
    isWinning: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div :class="`flex flex-col space-y-2 p-2 bg-gray-700 rounded-xl transition-all duration-300 ${isWinning ? 'ring-4 ring-green-400 scale-105' : ''}`">
        <SlotSymbol
            v-for="(symbol, index) in symbols"
            :key="index"
            :symbol="symbol"
        />
    </div>
</template>

<style scoped>
/* Add any specific styles for Reel.vue here if needed */
</style>

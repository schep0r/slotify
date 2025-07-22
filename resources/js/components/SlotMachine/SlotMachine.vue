<script setup>
import Reel from './SlotReel.vue';

const props = defineProps({
    visibleSymbols: {
        type: Array,
        required: true
    },
    winningLines: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <div class="flex space-x-4 bg-gray-900 rounded-2xl p-6 shadow-inner border-2 border-gray-700">
        <Reel
            v-for="(reelSymbols, index) in visibleSymbols"
            :key="index"
            :symbols="reelSymbols"
            :isWinning="winningLines.includes(index)"
        />
    </div>
</template>

<style scoped>
/* Add any specific styles for SlotMachine.vue here if needed */
</style>

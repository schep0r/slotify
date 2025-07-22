<script setup>
const props = defineProps({
    balance: {
        type: Number,
        required: true
    },
    freeSpins: {
        type: Number,
        required: true
    }
});
</script>

<template>
    <div class="flex flex-col md:flex-row justify-center items-center space-y-4 md:space-y-0 md:space-x-8 bg-gray-700 rounded-xl p-6 shadow-inner w-full max-w-md">
        <div class="text-2xl font-bold text-yellow-300">
            Баланс: <span class="text-white">${{ balance }}</span>
        </div>
        <div class="text-2xl font-bold text-blue-300">
            Безкоштовні обертання: <span class="text-white">{{ freeSpins }}</span>
        </div>
    </div>
</template>

<style scoped>
/* Add any specific styles for BalanceDisplay.vue here if needed */
</style>

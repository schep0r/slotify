<script setup>
const props = defineProps({
    symbol: {
        type: String,
        required: true
    }
});
</script>

<template>
    <div class="w-24 h-24 bg-gray-600 rounded-lg flex items-center justify-center text-6xl font-bold shadow-md transform transition-transform duration-300 hover:scale-110">
        {{ symbol }}
    </div>
</template>

<style scoped>
/* Add any specific styles for Symbol.vue here if needed */
</style>

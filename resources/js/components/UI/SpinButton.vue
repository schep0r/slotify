<script setup>
const props = defineProps({
    isLoading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['spin']);

const triggerSpin = () => {
    emit('spin');
};
</script>

<template>
    <button
        @click="triggerSpin"
        :disabled="isLoading"
        :class="`
            px-12 py-6 rounded-full text-3xl font-extrabold uppercase tracking-wide
            bg-gradient-to-r from-green-500 to-green-700 text-white
            shadow-xl hover:from-green-600 hover:to-green-800
            transform transition-all duration-300
            ${isLoading ? 'opacity-50 cursor-not-allowed animate-pulse' : 'hover:scale-105 active:scale-95'}
            focus:outline-none focus:ring-4 focus:ring-green-300 focus:ring-opacity-75
        `"
    >
        {{ isLoading ? 'Обертається...' : 'Обертати!' }}
    </button>
</template>

<style scoped>
/* Add any specific styles for SpinButton.vue here if needed */
</style>

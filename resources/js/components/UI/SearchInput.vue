<script setup>
const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const updateSearch = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <div class="relative w-full md:w-1/2">
        <input
            type="text"
            :value="modelValue"
            @input="updateSearch"
            placeholder="Пошук ігор..."
            class="w-full px-5 py-3 pr-10 rounded-full bg-gray-700 text-white placeholder-gray-400 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
        >
        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </div>
</template>

<style scoped>
/* Add any specific styles for SearchInput.vue here if needed */
</style>

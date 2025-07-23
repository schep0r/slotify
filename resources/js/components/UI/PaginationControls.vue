<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: {
        type: Number,
        required: true
    },
    totalPages: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['pageChange']);

// Generate an array of page numbers to display
const pagesToShow = computed(() => {
    const pages = [];
    const maxPages = 5; // Max number of page buttons to show
    let startPage = Math.max(1, props.currentPage - Math.floor(maxPages / 2));
    let endPage = Math.min(props.totalPages, startPage + maxPages - 1);

    if (endPage - startPage + 1 < maxPages) {
        startPage = Math.max(1, endPage - maxPages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }
    return pages;
});

const changePage = (page) => {
    if (page !== props.currentPage) {
        emit('pageChange', page);
    }
};
</script>

<template>
    <div v-if="totalPages > 1" class="flex justify-center items-center space-x-2 mt-8">
        <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
            Попередня
        </button>

        <button
            v-for="page in pagesToShow"
            :key="page"
            @click="changePage(page)"
            :class="[
                'px-4 py-2 rounded-lg font-semibold transition-colors duration-200',
                currentPage === page ? 'bg-yellow-500 text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600'
            ]"
        >
            {{ page }}
        </button>

        <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
            Наступна
        </button>
    </div>
</template>

<style scoped>
/* Add any specific styles for PaginationControls.vue here if needed */
</style>

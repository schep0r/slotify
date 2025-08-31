<script setup>
import { onMounted } from 'vue';
import { useGamesStore } from '../stores/gamesStore';
import GameCard from '../components/Game/GameCard.vue';
import PaginationControls from '../components/UI/PaginationControls.vue';
import SearchInput from '../components/UI/SearchInput.vue';
import FilterSelect from '../components/UI/FilterSelect.vue';

const gamesStore = useGamesStore();

// Fetch all games from the simulated backend when the component is mounted
onMounted(() => {
    gamesStore.fetchAllGamesFromBackend();
});

// Handlers for UI interactions, dispatching actions to the store
const handleSearch = (query) => {
    gamesStore.setSearchQuery(query);
};

const handleFilter = (category) => {
    gamesStore.setFilterCategory(category);
};

const handlePageChange = (page) => {
    gamesStore.setCurrentPage(page);
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-900 to-indigo-900 text-white p-6 font-inter">
        <h1 class="text-5xl font-extrabold text-center mb-10 text-yellow-400 drop-shadow-lg">
            Усі Ігри
        </h1>

        <div class="max-w-6xl mx-auto bg-gray-800 bg-opacity-70 rounded-3xl p-8 shadow-2xl border-4 border-blue-500">
            <!-- Search and Filter Controls -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 space-y-4 md:space-y-0 md:space-x-4">
                <SearchInput :modelValue="gamesStore.searchQuery" @update:modelValue="handleSearch" />
                <FilterSelect
                    :modelValue="gamesStore.filterCategory"
                    :options="gamesStore.availableCategories"
                    @update:modelValue="handleFilter"
                />
            </div>

            <!-- Loading / Error / No Games Message -->
            <div v-if="gamesStore.isLoading" class="text-center text-xl text-blue-300 py-10">
                Завантаження ігор...
            </div>
            <div v-else-if="gamesStore.error" class="text-center text-xl text-red-500 py-10">
                {{ gamesStore.error }}
            </div>
            <div v-else-if="gamesStore.games.length === 0" class="text-center text-xl text-gray-400 py-10">
                Ігор не знайдено за вашими критеріями.
            </div>

            <!-- Games Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <GameCard v-for="game in gamesStore.games" :key="game.id" :game="game" />
            </div>

            <!-- Pagination Controls -->
            <PaginationControls
                :currentPage="gamesStore.currentPage"
                :totalPages="gamesStore.totalPages"
                @page-change="handlePageChange"
            />
        </div>
    </div>
</template>

<style scoped>
/* Scoped styles for GamesPage.vue can be added here if needed */
</style>

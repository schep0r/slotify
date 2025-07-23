import { defineStore } from 'pinia';
import { getGames } from '../api';

export const useGamesStore = defineStore('games', {
    state: () => ({
        allGames: [], // All games loaded initially (simulated backend data)
        games: [],    // Games currently displayed on the page (after filtering/pagination)
        isLoading: false,
        error: null,
        searchQuery: '',
        filterCategory: 'All', // Default filter
        currentPage: 1,
        perPage: 6, // Number of games per page
        totalGames: 0, // Total games after search/filter, before pagination
        availableCategories: ['All', 'Slots', 'Table Games', 'Live Casino', 'Jackpot'], // Categories from backend
    }),
    getters: {
        // Calculate total pages based on filtered games
        totalPages: (state) => Math.ceil(state.totalGames / state.perPage),
    },
    actions: {
        /**
         * Fetches all games from the backend using the API.
         */
        async fetchAllGamesFromBackend() {
            this.isLoading = true;
            this.error = null;
            try {
                // Call the API to get games
                const response = await getGames();
                this.allGames = response.data;

                // Process categories from the fetched games
                const categories = new Set(['All']);
                this.allGames.forEach(game => {
                    if (game.type) {
                        categories.add(game.type);
                    }
                });
                this.availableCategories = Array.from(categories);

                this.filterAndPaginateGames(); // Initial filter and paginate
            } catch (err) {
                this.error = 'Не вдалося завантажити ігри. Спробуйте ще раз.';
                console.error('Error fetching games:', err);
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Filters games based on search query and category, then applies pagination.
         * This method is called internally whenever search, filter, or page changes.
         */
        filterAndPaginateGames() {
            let filtered = this.allGames;

            // Apply category filter
            if (this.filterCategory !== 'All') {
                filtered = filtered.filter(game => game.type === this.filterCategory);
            }

            // Apply search query filter
            if (this.searchQuery) {
                const lowerCaseQuery = this.searchQuery.toLowerCase();
                filtered = filtered.filter(game =>
                    game.name.toLowerCase().includes(lowerCaseQuery) ||
                    (game.slug && game.slug.toLowerCase().includes(lowerCaseQuery))
                );
            }

            this.totalGames = filtered.length; // Update total games after filtering

            // Apply pagination
            const startIndex = (this.currentPage - 1) * this.perPage;
            const endIndex = startIndex + this.perPage;
            this.games = filtered.slice(startIndex, endIndex);
        },

        /**
         * Sets the search query and resets to the first page.
         * @param {string} query - The search string.
         */
        setSearchQuery(query) {
            this.searchQuery = query;
            this.currentPage = 1; // Reset to first page on new search
            this.filterAndPaginateGames();
        },

        /**
         * Sets the filter category and resets to the first page.
         * @param {string} category - The category to filter by.
         */
        setFilterCategory(category) {
            this.filterCategory = category;
            this.currentPage = 1; // Reset to first page on new filter
            this.filterAndPaginateGames();
        },

        /**
         * Sets the current page number.
         * @param {number} page - The page number to navigate to.
         */
        setCurrentPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.filterAndPaginateGames();
            }
        },
    },
});

import { defineStore } from 'pinia';
import { getGameSettings as apiGameSettings, playGame as apiPlayGame } from '../api/index.js'
import { useAuthStore } from "./authStore.js";


export const useGameStore = defineStore('game', {
    state: () => ({
        betAmount: 10,
        freeSpins: 0,
        visibleSymbols: [],
        symbolsAssociative: {
            'CHERRY': '🍒',
            'LEMON': '🍋',
            'ORANGE': '🍊',
            'GRAPES': '🍇',
            'PLUM': '🍑',
            'WATERMELON': '🍉',
            'WILD': '🐾',
            'SCATTER': '✨',
            'BAR': '💰',
            'SEVEN': '7️⃣'
        },
        winningLines: [],
        totalPayout: 0,
        isJackpot: false,
        isLoading: false,
        message: '',
        // Configuration data from backend
        payouts: [] // Will also be fetched from backend
    }),
    actions: {
        async fetchConfiguration(gameId) {
            this.isLoading = true;
            try {
                const mapToEmoji = (val) => {
                    if (typeof val === 'string') {
                        // If it is a known key, map to emoji; otherwise, try splitting by spaces to map sequences like "CHERRY CHERRY CHERRY"
                        if (this.symbolsAssociative[val]) return this.symbolsAssociative[val];
                        // Split by whitespace, map tokens, and join back with a single space
                        return val
                            .trim()
                            .split(/\s+/)
                            .map(token => this.symbolsAssociative[token] || token)
                            .join(' ');
                    }
                    if (Array.isArray(val)) {
                        return val.map(mapToEmoji);
                    }
                    return val;
                };

                // Fetch settings from backend
                const { data } = await apiGameSettings(gameId); // default to 1 if not provided

                // Backend returns: { success: true, payouts: [{ combo: '🍒 🍒 🍒', payout: 50 }, ...] }
                const payouts = Array.isArray(data?.payouts) ? data.payouts : [];
                const visibleSymbols = Array.isArray(data?.visible_symbols) ? data.visible_symbols : [];

                // Map backend payout field name to the one used by the store/spin logic (value)
                this.payouts = payouts.map(p => ({
                    combo: mapToEmoji(p.combo),
                    payout: p.payout ?? 0,
                }));

                // Map backend textual identifiers to symbol emojis using symbolsAssociative
                this.visibleSymbols = mapToEmoji(visibleSymbols);

                this.message = 'Конфігурація завантажена!';

            } catch (error) {
                console.error('Помилка завантаження конфігурації:', error);
                this.message = error?.message || 'Помилка завантаження конфігурації.';
            } finally {
                this.isLoading = false;
            }
        },

        setBetAmount(amount) {
            this.betAmount = amount;
        },

        async spin(gameId) {
            const authStore = useAuthStore();

            if (this.isLoading || (authStore.userBalance < this.betAmount && this.freeSpins === 0)) {

                this.message = "Недостатньо коштів або вже обертається.";
                return;
            }
            const mapToEmoji = (val) => {
                if (typeof val === 'string') {
                    // If it is a known key, map to emoji; otherwise, try splitting by spaces to map sequences like "CHERRY CHERRY CHERRY"
                    if (this.symbolsAssociative[val]) return this.symbolsAssociative[val];
                    // Split by whitespace, map tokens, and join back with a single space
                    return val
                        .trim()
                        .split(/\s+/)
                        .map(token => this.symbolsAssociative[token] || token)
                        .join(' ');
                }
                if (Array.isArray(val)) {
                    return val.map(mapToEmoji);
                }
                return val;
            };

            this.isLoading = true;
            this.message = '';
            this.winningLines = [];
            this.totalPayout = 0;
            this.isJackpot = false;

            let currentBalance = authStore.userBalance;
            let currentFreeSpins = this.freeSpins;

            if (currentFreeSpins > 0) {
                currentFreeSpins -= 1;
                this.freeSpins = currentFreeSpins;
                this.message = `Використано безкоштовний спін! Залишилось: ${currentFreeSpins}`;
            } else {
                currentBalance -= this.betAmount;

                authStore.updateBalance(currentBalance)
            }

            try {
                const {data} = await apiPlayGame(gameId, this.betAmount)

                authStore.updateBalance(data.result.newBalance)

                this.visibleSymbols = mapToEmoji(data.result.gameData.visibleSymbols);
                // this.winningLines = result.winningLines;
                // this.totalPayout = result.totalPayout;
                // this.isJackpot = result.isJackpot;
                // this.multiplier = result.multiplier;
                // this.freeSpins += result.freeSpinsAwarded;

                if (data.result.winAmount > 0) {
                    this.message = `Ви виграли ${data.result.winAmount}! `;
                } else {
                    this.message = 'Спробуйте ще раз!';
                }

                this.isLoading = false;
            } catch (error) {
                console.log(error);

                this.isLoading = false;

                this.message = error.message
            }
        }
    }
});

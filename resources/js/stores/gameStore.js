import { defineStore } from 'pinia';

export const useGameStore = defineStore('game', {
    state: () => ({
        balance: 1000,
        betAmount: 10,
        freeSpins: 0,
        visibleSymbols: [
            ['🍒', '🍋', '🍊'],
            ['🍋', '🍒', '🍊'],
            ['🍊', '🍋', '🍒']
        ],
        winningLines: [],
        totalPayout: 0,
        isJackpot: false,
        multiplier: 1,
        isLoading: false,
        message: '',
        // Configuration data from backend
        allSymbols: [],
        symbolWeights: {},
        payouts: [] // Will also be fetched from backend
    }),
    actions: {
        async fetchConfiguration() {
            this.isLoading = true;
            try {
                // Simulate API call to backend for configuration
                const response = await new Promise(resolve => setTimeout(() => {
                    resolve({
                        symbols: ['🍒', '🍋', '🍊', '🔔', '💎', '⭐', '7️⃣'],
                        weights: {
                            '🍒': 0.2, '🍋': 0.2, '🍊': 0.15, '🔔': 0.15, '💎': 0.1, '⭐': 0.1, '7️⃣': 0.1
                        },
                        payouts: [
                            { combo: '7️⃣ 7️⃣ 7️⃣', payout: '1000x (ДЖЕКПОТ!)', value: 1000 },
                            { combo: '⭐ ⭐ ⭐', payout: '300x', value: 300 },
                            { combo: '💎 💎 💎', payout: '200x', value: 200 },
                            { combo: '🔔 🔔 🔔', payout: '150x', value: 150 },
                            { combo: '🍊 🍊 🍊', payout: '100x', value: 100 },
                            { combo: '🍋 🍋 🍋', payout: '75x', value: 75 },
                            { combo: '🍒 🍒 🍒', payout: '50x', value: 50 },
                        ]
                    });
                }, 500)); // Simulate network delay

                this.allSymbols = response.symbols;
                this.symbolWeights = response.weights;
                this.payouts = response.payouts;
                this.message = 'Конфігурація завантажена!';

            } catch (error) {
                console.error('Помилка завантаження конфігурації:', error);
                this.message = 'Помилка завантаження конфігурації.';
            } finally {
                this.isLoading = false;
            }
        },

        setBetAmount(amount) {
            this.betAmount = amount;
        },

        getRandomSymbol() {
            let rand = Math.random();
            let cumulativeWeight = 0;
            for (const symbol of this.allSymbols) {
                cumulativeWeight += this.symbolWeights[symbol];
                if (rand < cumulativeWeight) {
                    return symbol;
                }
            }
            return this.allSymbols[0]; // Fallback
        },

        async spin() {
            if (this.isLoading || (this.balance < this.betAmount && this.freeSpins === 0)) {
                this.message = "Недостатньо коштів або вже обертається.";
                return;
            }

            this.isLoading = true;
            this.message = '';
            this.winningLines = [];
            this.totalPayout = 0;
            this.isJackpot = false;
            this.multiplier = 1;

            let currentBalance = this.balance;
            let currentFreeSpins = this.freeSpins;

            if (currentFreeSpins > 0) {
                currentFreeSpins -= 1;
                this.freeSpins = currentFreeSpins;
                this.message = `Використано безкоштовний спін! Залишилось: ${currentFreeSpins}`;
            } else {
                currentBalance -= this.betAmount;
                this.balance = currentBalance;
            }

            // Simulate network delay for spin
            await new Promise(resolve => setTimeout(resolve, 1500));

            // --- Mock Backend Spin Logic ---
            const reelPositions = Array(3).fill(0).map(() => Math.floor(Math.random() * 100));
            const newVisibleSymbols = Array(3).fill(0).map(() =>
                Array(3).fill(0).map(() => this.getRandomSymbol())
            );

            let simulatedWinningLines = [];
            let simulatedTotalPayout = 0;
            let simulatedIsJackpot = false;
            let simulatedMultiplier = 1;
            let simulatedFreeSpinsAwarded = 0;

            // Simple winning logic: check for 3 identical symbols in the middle row
            if (newVisibleSymbols[0][1] === newVisibleSymbols[1][1] && newVisibleSymbols[1][1] === newVisibleSymbols[2][1]) {
                simulatedWinningLines.push(1); // Indicate the middle row (index 1) as a win
                const winningSymbol = newVisibleSymbols[0][1];
                const winningPayout = this.payouts.find(p => p.combo.includes(winningSymbol) && p.combo.split(' ').length === 3);

                if (winningPayout) {
                    simulatedTotalPayout = winningPayout.value;
                    if (winningSymbol === '7️⃣') { // Assuming '7️⃣' is the jackpot symbol
                        simulatedIsJackpot = true;
                        simulatedFreeSpinsAwarded = 5;
                        simulatedMultiplier = 2;
                    }
                }
            }

            simulatedTotalPayout *= simulatedMultiplier;
            const newBalance = this.balance - this.betAmount + simulatedTotalPayout; // Recalculate based on current state

            const result = {
                reelPositions,
                visibleSymbols: newVisibleSymbols,
                winningLines: simulatedWinningLines,
                totalPayout: simulatedTotalPayout,
                newBalance,
                isJackpot: simulatedIsJackpot,
                multiplier: simulatedMultiplier,
                freeSpinsAwarded: simulatedFreeSpinsAwarded
            };
            // --- End Mock Backend Spin Logic ---


            this.visibleSymbols = result.visibleSymbols;
            this.winningLines = result.winningLines;
            this.totalPayout = result.totalPayout;
            this.isJackpot = result.isJackpot;
            this.multiplier = result.multiplier;
            this.freeSpins += result.freeSpinsAwarded;
            this.balance = result.newBalance;

            if (result.totalPayout > 0) {
                this.message = `Ви виграли ${result.totalPayout}! ${result.isJackpot ? 'ДЖЕКПОТ!' : ''} ${result.multiplier > 1 ? `Множник: x${result.multiplier}` : ''} ${result.freeSpinsAwarded > 0 ? `Надано ${result.freeSpinsAwarded} безкоштовних обертань!` : ''}`;
            } else {
                this.message = 'Спробуйте ще раз!';
            }

            this.isLoading = false;
        }
    }
});

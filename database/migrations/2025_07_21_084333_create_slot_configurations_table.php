<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slot_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');

            // Basic slot configuration
            $table->string('name');
            $table->string('theme')->default('classic');
            $table->integer('reels')->default(5);
            $table->integer('rows')->default(3);
            $table->integer('paylines')->default(25);

            // RTP and volatility settings
            $table->decimal('rtp_percentage', 5, 2)->default(96.00); // 96.00%
            $table->enum('volatility', ['low', 'medium', 'high'])->default('medium');

            // Betting configuration
            $table->decimal('min_bet', 10, 2)->default(0.01);
            $table->decimal('max_bet', 10, 2)->default(100.00);
            $table->decimal('bet_increment', 10, 2)->default(0.01);
            $table->json('bet_levels')->nullable(); // [0.01, 0.05, 0.10, 0.25, 0.50, 1.00, etc.]

            // Jackpot configuration
            $table->boolean('has_progressive_jackpot')->default(false);
            $table->decimal('jackpot_seed', 12, 2)->default(0.00);
            $table->decimal('jackpot_contribution_rate', 5, 4)->default(0.0100); // 1%

            // Symbol configuration
            $table->json('symbols'); // Symbol definitions with values and frequencies
            $table->json('wild_symbols')->nullable();
            $table->json('scatter_symbols')->nullable();
            $table->json('bonus_symbols')->nullable();

            // Paytable and winning combinations
            $table->json('paytable'); // Winning combinations and payouts
            $table->json('special_features')->nullable(); // Free spins, multipliers, etc.

            // Game mechanics
            $table->boolean('has_free_spins')->default(false);
            $table->integer('free_spins_trigger_count')->default(3);
            $table->integer('free_spins_award')->default(10);
            $table->decimal('free_spins_multiplier', 5, 2)->default(1.00);

            $table->boolean('has_bonus_game')->default(false);
            $table->json('bonus_game_config')->nullable();

            // Auto play settings
            $table->boolean('auto_play_enabled')->default(true);
            $table->integer('max_auto_spins')->default(100);

            // Game state and statistics
            $table->boolean('is_active')->default(true);
            $table->decimal('current_jackpot', 12, 2)->default(0.00);
            $table->bigInteger('total_spins')->default(0);
            $table->decimal('total_wagered', 15, 2)->default(0.00);
            $table->decimal('total_paid_out', 15, 2)->default(0.00);

            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional configuration options

            $table->timestamps();

            // Indexes
            $table->index(['game_id', 'is_active']);
            $table->index('rtp_percentage');
            $table->index('volatility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_configurations');
    }
};

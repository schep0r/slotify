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
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->uuid('round_id')->unique(); // External round identifier for transactions
            $table->foreignId('game_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Denormalized for faster queries
            $table->string('game_id'); // Which specific slot game

            // Financial data
            $table->decimal('bet_amount', 10, 4); // Support micro-bets
            $table->decimal('win_amount', 10, 4)->default(0);
            $table->decimal('net_result', 10, 4); // win_amount - bet_amount
            $table->decimal('balance_before', 10, 4); // Balance before this round
            $table->decimal('balance_after', 10, 4); // Balance after this round

            // Game specific data
            $table->json('reel_result'); // Slot reels outcome [["A","B","C"], ["D","E","F"], ...]
            $table->json('paylines_won')->nullable(); // Which paylines won
            $table->json('multipliers')->nullable(); // Any multipliers applied
            $table->json('bonus_features')->nullable(); // Free spins, bonus games, etc.
            $table->integer('lines_played'); // Number of paylines played
            $table->decimal('bet_per_line', 10, 4); // Bet amount per line

            // Technical data
            $table->string('rng_seed')->nullable(); // RNG seed for this round (for verification)
            $table->decimal('rtp_contribution', 5, 4)->nullable(); // This round's RTP contribution
            $table->boolean('is_bonus_round')->default(false);
            $table->string('bonus_type')->nullable(); // 'free_spins', 'pick_bonus', etc.
            $table->integer('free_spins_remaining')->nullable(); // If in free spins mode

            // Audit and compliance
            $table->string('transaction_ref')->nullable(); // Reference to wallet transaction
            $table->ipAddress('ip_address');
            $table->string('user_agent')->nullable();
            $table->enum('round_status', ['pending', 'completed', 'cancelled', 'disputed'])->default('completed');
            $table->timestamp('completed_at')->nullable();
            $table->string('completion_hash')->nullable(); // Hash for round integrity verification

            // Additional metadata
            $table->json('extra_data')->nullable(); // For future game features
            $table->timestamps();

            // Indexes for performance
            $table->index(['game_session_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['game_id', 'created_at']);
            $table->index(['round_status', 'created_at']);
            $table->index('bet_amount');
            $table->index('win_amount');
            $table->index(['is_bonus_round', 'bonus_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};

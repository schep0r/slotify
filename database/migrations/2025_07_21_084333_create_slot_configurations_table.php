<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('slot_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');

            $table->string('version')->default('1.0');
            $table->json('paytable'); // Symbol combinations and payouts
            $table->json('reel_strips'); // Symbol arrangements on each reel
            $table->json('symbol_weights'); // Probability weights for symbols
            $table->json('bonus_triggers')->nullable(); // Bonus round trigger conditions
            $table->json('free_spins_config')->nullable();
            $table->json('multipliers')->nullable();
            $table->json('wilds_config')->nullable();
            $table->json('scatters_config')->nullable();
            $table->json('jackpot_config')->nullable();
            $table->json('rtp_config'); // RTP configuration by bet level
            $table->json('volatility_config'); // Volatility parameters
            $table->json('special_features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['game_id', 'is_active']);
            $table->unique(['game_id', 'version']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('slot_configurations');
    }
};

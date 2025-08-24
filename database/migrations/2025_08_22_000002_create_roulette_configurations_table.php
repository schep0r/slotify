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
        Schema::create('roulette_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            
            $table->enum('wheel_type', ['european', 'american'])->default('european');
            $table->decimal('min_bet', 8, 2)->default(1.00);
            $table->decimal('max_bet', 8, 2)->default(1000.00);
            $table->json('table_limits'); // Limits for different bet types
            $table->json('special_rules')->nullable(); // En prison, La partage, etc.
            $table->decimal('rtp_percentage', 5, 2)->default(97.30); // European: 97.30%, American: 94.74%
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            $table->index(['game_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roulette_configurations');
    }
};
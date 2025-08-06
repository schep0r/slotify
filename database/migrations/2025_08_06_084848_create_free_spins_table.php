<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('free_spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount')->default(0);
            $table->integer('used_amount')->default(0);
            $table->string('source')->default('bonus'); // bonus, promotion, achievement, etc.
            $table->decimal('bet_value', 8, 2)->nullable(); // Fixed bet value for free spins
            $table->string('game_restriction')->nullable(); // Specific slot game or null for any
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional data like multipliers, etc.
            $table->timestamps();

            $table->index(['user_id', 'is_active', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('free_spins');
    }
};

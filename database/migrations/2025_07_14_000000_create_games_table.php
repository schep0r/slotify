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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider');
            $table->string('status')->nullable(false);
            $table->string('type')->nullable(false);
            $table->decimal('min_bet', 8, 2);
            $table->decimal('max_bet', 8, 2);
            $table->integer('reels')->default(5);
            $table->integer('rows')->default(3);
            $table->integer('paylines')->default(25);
            $table->decimal('rtp', 5, 2)->default(95.00);
            $table->json('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};

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
        Schema::create('free_spin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('free_spin_id')->constrained()->onDelete('cascade');
            $table->string('game_id');
            $table->decimal('bet_amount', 8, 2);
            $table->decimal('win_amount', 8, 2)->default(0);
            $table->json('spin_result'); // Store the actual spin results
            $table->timestamp('played_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('free_spin_transactions');
    }
};

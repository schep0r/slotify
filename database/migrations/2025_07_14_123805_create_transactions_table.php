<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_session_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['bet', 'win', 'deposit', 'withdrawal', 'bonus', 'refund', 'adjustment']);
            $table->decimal('amount', 15, 2); // Positive for credits, negative for debits
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->json('spin_result')->nullable();
            $table->string('reference_id')->nullable()->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index(['game_session_id']);
            $table->index(['reference_id']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};

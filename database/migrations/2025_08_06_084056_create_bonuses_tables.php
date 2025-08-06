<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bonus_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['free_spins', 'bonus_coins', 'multiplier', 'no_deposit', 'deposit_match', 'cashback']);
            $table->json('config'); // Store bonus-specific configuration
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bonus_type_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'active', 'used', 'expired', 'cancelled'])->default('pending');
            $table->integer('amount'); // Coins, spins, or percentage
            $table->integer('used_amount')->default(0);
            $table->decimal('wagering_requirement', 8, 2)->default(0);
            $table->decimal('wagered_amount', 8, 2)->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable(); // Additional bonus data
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['expires_at']);
        });

        Schema::create('bonus_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bonus_type_id')->constrained()->onDelete('cascade');
            $table->timestamp('claimed_at');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'bonus_type_id', 'claimed_at']);
        });

        Schema::create('bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_bonus_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit', 'wager']);
            $table->integer('amount');
            $table->string('description');
            $table->json('game_data')->nullable(); // Slot game info, spin results, etc.
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_transactions');
        Schema::dropIfExists('bonus_claims');
        Schema::dropIfExists('user_bonuses');
        Schema::dropIfExists('bonus_types');
    }
};

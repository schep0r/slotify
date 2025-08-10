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
        Schema::create('game_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->text('value');
            $table->enum('data_type', ['integer', 'string', 'boolean', 'json', 'decimal']);
            $table->string('description')->nullable();
            $table->boolean('is_configurable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'key']);
            $table->index('game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_configurations');
    }
};

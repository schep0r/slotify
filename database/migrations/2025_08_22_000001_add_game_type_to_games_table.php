<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // Update the type column to use enum values
            $table->string('type')->default('slot')->change();
        });

        // Update existing games to have 'slot' type if they don't have one
        DB::table('games')->whereNull('type')->orWhere('type', '')->update(['type' => 'slot']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible since we're changing the meaning of the type column
        // In a real scenario, you might want to create a backup of the data first
    }
};
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->integer('level')->default(1);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('bonus_balance', 15, 2)->default(0);
            $table->string('vip_status')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->index(['status', 'verified_at']);
            $table->index('vip_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_verified_at_index');
            $table->dropIndex('users_vip_status_index');

            $table->dropColumn('status');
            $table->dropColumn('level');
            $table->dropColumn('balance');
            $table->dropColumn('bonus_balance');
            $table->dropColumn('vip_status');
            $table->dropColumn('verified_at');
        });
    }
};

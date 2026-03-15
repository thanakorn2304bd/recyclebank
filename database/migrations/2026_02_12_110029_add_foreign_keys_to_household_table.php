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
        Schema::table('household', function (Blueprint $table) {
            $table->foreign('community_id', 'fk_household_community')
                ->references('community_id')
                ->on('community')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreign('created_by', 'fk_household_created_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('household', function (Blueprint $table) {
            $table->dropForeign('fk_household_community');
            $table->dropForeign('fk_household_created_by');
        });
    }
};

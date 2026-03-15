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
        Schema::table('transaction', function (Blueprint $table) {
            $table->foreign('household_id', 'fk_transaction_household')
                ->references('household_id')
                ->on('household')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreign('recorded_by', 'fk_transaction_recorded_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->dropForeign('fk_transaction_household');
            $table->dropForeign('fk_transaction_recorded_by');
        });
    }
};

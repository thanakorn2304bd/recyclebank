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
        Schema::table('user_account', function (Blueprint $table) {
            $table->foreign('household_id', 'fk_user_account_household')
                ->references('household_id')
                ->on('household')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('staff_id', 'fk_user_account_staff')
                ->references('staff_id')
                ->on('staff')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_account', function (Blueprint $table) {
            $table->dropForeign('fk_user_account_household');
            $table->dropForeign('fk_user_account_staff');
        });
    }
};

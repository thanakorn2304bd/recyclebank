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
        Schema::table('log_activity', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_log_activity_user')
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
        Schema::table('log_activity', function (Blueprint $table) {
            $table->dropForeign('fk_log_activity_user');
        });
    }
};

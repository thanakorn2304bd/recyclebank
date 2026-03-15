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
        Schema::create('user_account', function (Blueprint $table) {
            $table->integer('user_id')->autoIncrement();
            $table->string('username', 50)->unique('uq_user_account_username');
            $table->string('password');
            $table->enum('role', ['member', 'staff', 'admin']);
            $table->integer('household_id')->nullable();
            $table->integer('staff_id')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_active');

            $table->index('household_id', 'idx_user_account_household_id');
            $table->index('staff_id', 'idx_user_account_staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_account');
    }
};

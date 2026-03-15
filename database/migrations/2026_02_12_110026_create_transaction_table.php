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
        Schema::create('transaction', function (Blueprint $table) {
            $table->integer('transaction_id')->autoIncrement();
            $table->integer('household_id');
            $table->date('transaction_date');
            $table->enum('transaction_type', ['deposit', 'withdraw']);
            $table->decimal('total_weight', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->integer('recorded_by');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('household_id', 'idx_transaction_household_id');
            $table->index('recorded_by', 'idx_transaction_recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};

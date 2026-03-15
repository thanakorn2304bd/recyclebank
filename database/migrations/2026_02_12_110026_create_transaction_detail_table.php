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
        Schema::create('transaction_detail', function (Blueprint $table) {
            $table->integer('detail_id')->autoIncrement();
            $table->integer('transaction_id');
            $table->integer('material_id');
            $table->decimal('weight', 10, 2);
            $table->decimal('price_per_unit', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('transaction_id', 'idx_transaction_detail_transaction_id');
            $table->index('material_id', 'idx_transaction_detail_material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_detail');
    }
};

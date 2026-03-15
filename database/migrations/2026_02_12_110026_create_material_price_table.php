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
        Schema::create('material_price', function (Blueprint $table) {
            $table->integer('price_id')->autoIncrement();
            $table->integer('material_id');
            $table->decimal('price', 10, 2);
            $table->date('effective_date');
            $table->date('expired_date')->nullable();
            $table->integer('created_by');
            $table->dateTime('created_at');

            $table->index('material_id', 'idx_material_price_material_id');
            $table->index('created_by', 'idx_material_price_created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_price');
    }
};

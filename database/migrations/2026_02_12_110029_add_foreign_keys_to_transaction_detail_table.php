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
        Schema::table('transaction_detail', function (Blueprint $table) {
            $table->foreign('material_id', 'fk_transaction_detail_material')
                ->references('material_id')
                ->on('material')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreign('transaction_id', 'fk_transaction_detail_transaction')
                ->references('transaction_id')
                ->on('transaction')
                ->restrictOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_detail', function (Blueprint $table) {
            $table->dropForeign('fk_transaction_detail_material');
            $table->dropForeign('fk_transaction_detail_transaction');
        });
    }
};

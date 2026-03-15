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
        Schema::table('material_price', function (Blueprint $table) {
            $table->foreign('created_by', 'fk_material_price_created_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreign('material_id', 'fk_material_price_material')
                ->references('material_id')
                ->on('material')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_price', function (Blueprint $table) {
            $table->dropForeign('fk_material_price_created_by');
            $table->dropForeign('fk_material_price_material');
        });
    }
};

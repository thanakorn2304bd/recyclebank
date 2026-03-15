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
        Schema::table('material', function (Blueprint $table) {
            $table->foreign('category_id', 'fk_material_category')
                ->references('category_id')
                ->on('material_category')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material', function (Blueprint $table) {
            $table->dropForeign('fk_material_category');
        });
    }
};

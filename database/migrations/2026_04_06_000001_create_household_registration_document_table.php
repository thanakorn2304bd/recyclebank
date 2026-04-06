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
        Schema::create('household_registration_document', function (Blueprint $table) {
            $table->integer('registration_document_id')->autoIncrement();
            $table->integer('household_id');
            $table->enum('document_type', ['household_copy', 'national_id_copy']);
            $table->string('original_name', 255);
            $table->string('stored_path', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index('household_id', 'idx_household_registration_document_household_id');
            $table->unique(['household_id', 'document_type'], 'uq_household_registration_document_household_type');

            $table->foreign('household_id', 'fk_household_registration_document_household')
                ->references('household_id')
                ->on('household')
                ->restrictOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_registration_document');
    }
};

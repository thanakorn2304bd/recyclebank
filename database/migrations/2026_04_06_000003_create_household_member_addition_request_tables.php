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
        Schema::create('household_member_addition_request', function (Blueprint $table) {
            $table->integer('member_addition_request_id')->autoIncrement();
            $table->integer('household_id');
            $table->integer('requested_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('household_id', 'idx_hmar_household_id');
            $table->index('requested_by', 'idx_hmar_requested_by');
            $table->index('reviewed_by', 'idx_hmar_reviewed_by');

            $table->foreign('household_id', 'fk_hmar_household')
                ->references('household_id')
                ->on('household')
                ->restrictOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('requested_by', 'fk_hmar_requested_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('reviewed_by', 'fk_hmar_reviewed_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('household_member_addition_request_member', function (Blueprint $table) {
            $table->integer('member_addition_request_member_id')->autoIncrement();
            $table->integer('member_addition_request_id');
            $table->string('full_name', 100);
            $table->string('id_card', 13);
            $table->string('id_card_last4', 4)->nullable();
            $table->string('id_card_hash', 64)->nullable();
            $table->string('relation', 50);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('member_addition_request_id', 'idx_hmarm_request_id');
            $table->index('id_card_last4', 'idx_hmarm_id_card_last4');
            $table->index('id_card_hash', 'idx_hmarm_id_card_hash');

            $table->foreign('member_addition_request_id', 'fk_hmarm_request')
                ->references('member_addition_request_id')
                ->on('household_member_addition_request')
                ->restrictOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('household_member_addition_request_document', function (Blueprint $table) {
            $table->integer('member_addition_request_document_id')->autoIncrement();
            $table->integer('member_addition_request_id');
            $table->enum('document_type', ['household_copy', 'national_id_copy']);
            $table->unsignedInteger('member_position');
            $table->string('member_full_name', 100)->nullable();
            $table->string('member_relation', 50)->nullable();
            $table->char('member_id_card_last4', 4)->nullable();
            $table->string('original_name', 255);
            $table->string('stored_path', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('member_addition_request_id', 'idx_hmard_request_id');
            $table->unique(
                ['member_addition_request_id', 'document_type', 'member_position'],
                'uq_hmard_request_type_member'
            );

            $table->foreign('member_addition_request_id', 'fk_hmard_request')
                ->references('member_addition_request_id')
                ->on('household_member_addition_request')
                ->restrictOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_member_addition_request_document');
        Schema::dropIfExists('household_member_addition_request_member');
        Schema::dropIfExists('household_member_addition_request');
    }
};

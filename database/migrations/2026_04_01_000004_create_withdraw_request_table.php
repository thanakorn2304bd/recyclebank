<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdraw_request', function (Blueprint $table) {
            $table->integer('withdraw_request_id')->autoIncrement();
            $table->string('request_no', 30)->unique('uq_withdraw_request_no');
            $table->integer('household_id');
            $table->integer('requested_by')->nullable();
            $table->date('requested_for_date');
            $table->decimal('requested_amount', 10, 2);
            $table->text('request_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->integer('approved_transaction_id')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('household_id', 'idx_withdraw_request_household_id');
            $table->index('requested_by', 'idx_withdraw_request_requested_by');
            $table->index('requested_for_date', 'idx_withdraw_request_requested_for_date');
            $table->index('status', 'idx_withdraw_request_status');
            $table->index('reviewed_by', 'idx_withdraw_request_reviewed_by');
            $table->index('approved_transaction_id', 'idx_withdraw_request_approved_transaction_id');
        });

        Schema::table('withdraw_request', function (Blueprint $table) {
            $table->foreign('household_id', 'fk_withdraw_request_household_id')
                ->references('household_id')
                ->on('household')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreign('requested_by', 'fk_withdraw_request_requested_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('reviewed_by', 'fk_withdraw_request_reviewed_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('approved_transaction_id', 'fk_withdraw_request_approved_transaction_id')
                ->references('transaction_id')
                ->on('transaction')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('withdraw_request', function (Blueprint $table) {
            $table->dropForeign('fk_withdraw_request_household_id');
            $table->dropForeign('fk_withdraw_request_requested_by');
            $table->dropForeign('fk_withdraw_request_reviewed_by');
            $table->dropForeign('fk_withdraw_request_approved_transaction_id');
        });

        Schema::dropIfExists('withdraw_request');
    }
};

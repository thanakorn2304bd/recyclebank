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
        Schema::table('household', function (Blueprint $table) {
            $table->integer('reviewed_by')->nullable()->after('created_by');
            $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');

            $table->index('reviewed_by', 'idx_household_reviewed_by');
        });

        Schema::table('household', function (Blueprint $table) {
            $table->foreign('reviewed_by', 'fk_household_reviewed_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('log_activity', function (Blueprint $table) {
            $table->string('entity_type', 100)->nullable()->after('module');
            $table->string('entity_id', 50)->nullable()->after('entity_type');
            $table->string('ip_address', 45)->nullable()->after('entity_id');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->json('before_values')->nullable()->after('user_agent');
            $table->json('after_values')->nullable()->after('before_values');
            $table->json('metadata')->nullable()->after('after_values');

            $table->index(['entity_type', 'entity_id'], 'idx_log_activity_entity');
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->boolean('is_reversal')->default(false)->after('recorded_by');
            $table->integer('reversal_of_transaction_id')->nullable()->after('is_reversal');
            $table->integer('reversed_by')->nullable()->after('reversal_of_transaction_id');
            $table->dateTime('reversed_at')->nullable()->after('reversed_by');
            $table->text('reversal_reason')->nullable()->after('reversed_at');

            $table->index('is_reversal', 'idx_transaction_is_reversal');
            $table->index('reversal_of_transaction_id', 'idx_transaction_reversal_of');
            $table->index('reversed_by', 'idx_transaction_reversed_by');
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->foreign('reversal_of_transaction_id', 'fk_transaction_reversal_of')
                ->references('transaction_id')
                ->on('transaction')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreign('reversed_by', 'fk_transaction_reversed_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->dropForeign('fk_transaction_reversal_of');
            $table->dropForeign('fk_transaction_reversed_by');
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->dropIndex('idx_transaction_is_reversal');
            $table->dropIndex('idx_transaction_reversal_of');
            $table->dropIndex('idx_transaction_reversed_by');
            $table->dropColumn([
                'is_reversal',
                'reversal_of_transaction_id',
                'reversed_by',
                'reversed_at',
                'reversal_reason',
            ]);
        });

        Schema::table('log_activity', function (Blueprint $table) {
            $table->dropIndex('idx_log_activity_entity');
            $table->dropColumn([
                'entity_type',
                'entity_id',
                'ip_address',
                'user_agent',
                'before_values',
                'after_values',
                'metadata',
            ]);
        });

        Schema::table('household', function (Blueprint $table) {
            $table->dropForeign('fk_household_reviewed_by');
        });

        Schema::table('household', function (Blueprint $table) {
            $table->dropIndex('idx_household_reviewed_by');
            $table->dropColumn([
                'reviewed_by',
                'reviewed_at',
                'review_notes',
            ]);
        });
    }
};

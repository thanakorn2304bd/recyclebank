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
        Schema::create('household', function (Blueprint $table) {
            $table->integer('household_id')->autoIncrement();
            $table->char('account_no', 10)->unique('uq_household_account_no');
            $table->string('house_no', 20);
            $table->string('village_no', 10)->nullable();
            $table->char('community_id', 2);
            $table->string('phone', 20)->nullable();
            $table->string('contact_person', 100);
            $table->date('register_date');
            $table->enum('active_status', ['pending', 'active', 'inactive']);
            $table->integer('accumulated_months');
            $table->decimal('total_balance', 10, 2);
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('community_id', 'idx_household_community_id');
            $table->index('created_by', 'idx_household_created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household');
    }
};

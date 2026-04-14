<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_registration_document', function (Blueprint $table) {
            $table->mediumBinary('content')->nullable()->after('stored_path');
        });

        Schema::table('household_member_addition_request_document', function (Blueprint $table) {
            $table->mediumBinary('content')->nullable()->after('stored_path');
        });
    }

    public function down(): void
    {
        Schema::table('household_registration_document', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('household_member_addition_request_document', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};

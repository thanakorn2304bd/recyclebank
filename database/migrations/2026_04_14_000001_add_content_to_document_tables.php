<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE household_registration_document ADD COLUMN content MEDIUMBLOB NULL AFTER stored_path');
        DB::statement('ALTER TABLE household_member_addition_request_document ADD COLUMN content MEDIUMBLOB NULL AFTER stored_path');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE household_registration_document DROP COLUMN content');
        DB::statement('ALTER TABLE household_member_addition_request_document DROP COLUMN content');
    }
};

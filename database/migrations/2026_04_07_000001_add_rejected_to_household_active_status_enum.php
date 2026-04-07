<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE household MODIFY active_status ENUM('pending', 'active', 'inactive', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE household MODIFY active_status ENUM('pending', 'active', 'inactive') NOT NULL DEFAULT 'pending'");
    }
};

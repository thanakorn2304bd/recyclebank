<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_account', function (Blueprint $table) {
            $table->dateTime('password_changed_at')->nullable()->after('password');
            $table->boolean('force_password_reset')->default(false)->after('last_login');
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('force_password_reset');
            $table->dateTime('locked_until')->nullable()->after('failed_login_attempts');
        });

        DB::table('user_account')
            ->whereNull('password_changed_at')
            ->update([
                'password_changed_at' => DB::raw('created_at'),
                'force_password_reset' => false,
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('user_account', function (Blueprint $table) {
            $table->dropColumn([
                'password_changed_at',
                'force_password_reset',
                'failed_login_attempts',
                'locked_until',
            ]);
        });
    }
};

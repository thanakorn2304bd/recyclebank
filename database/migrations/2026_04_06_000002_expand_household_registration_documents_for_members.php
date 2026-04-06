<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('household_registration_document')) {
            return;
        }

        Schema::table('household_registration_document', function (Blueprint $table) {
            if (! Schema::hasColumn('household_registration_document', 'member_position')) {
                $table->unsignedInteger('member_position')->default(0)->after('document_type');
            }

            if (! Schema::hasColumn('household_registration_document', 'member_full_name')) {
                $table->string('member_full_name', 100)->nullable()->after('member_position');
            }

            if (! Schema::hasColumn('household_registration_document', 'member_relation')) {
                $table->string('member_relation', 50)->nullable()->after('member_full_name');
            }

            if (! Schema::hasColumn('household_registration_document', 'member_id_card_last4')) {
                $table->char('member_id_card_last4', 4)->nullable()->after('member_relation');
            }
        });

        DB::table('household_registration_document')
            ->where('document_type', 'national_id_copy')
            ->where('member_position', 0)
            ->update(['member_position' => 1]);

        $legacyUniqueName = 'uq_household_registration_document_household_type';
        $expandedUniqueName = 'uq_household_registration_document_household_type_member';

        if ($this->indexExists($legacyUniqueName)) {
            Schema::table('household_registration_document', function (Blueprint $table) use ($legacyUniqueName) {
                $table->dropUnique($legacyUniqueName);
            });
        }

        if (! $this->indexExists($expandedUniqueName)) {
            Schema::table('household_registration_document', function (Blueprint $table) use ($expandedUniqueName) {
                $table->unique(['household_id', 'document_type', 'member_position'], $expandedUniqueName);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('household_registration_document')) {
            return;
        }

        $expandedUniqueName = 'uq_household_registration_document_household_type_member';
        $legacyUniqueName = 'uq_household_registration_document_household_type';

        if ($this->indexExists($expandedUniqueName)) {
            Schema::table('household_registration_document', function (Blueprint $table) use ($expandedUniqueName) {
                $table->dropUnique($expandedUniqueName);
            });
        }

        if (! $this->indexExists($legacyUniqueName)) {
            Schema::table('household_registration_document', function (Blueprint $table) use ($legacyUniqueName) {
                $table->unique(['household_id', 'document_type'], $legacyUniqueName);
            });
        }

        Schema::table('household_registration_document', function (Blueprint $table) {
            if (Schema::hasColumn('household_registration_document', 'member_id_card_last4')) {
                $table->dropColumn('member_id_card_last4');
            }

            if (Schema::hasColumn('household_registration_document', 'member_relation')) {
                $table->dropColumn('member_relation');
            }

            if (Schema::hasColumn('household_registration_document', 'member_full_name')) {
                $table->dropColumn('member_full_name');
            }

            if (Schema::hasColumn('household_registration_document', 'member_position')) {
                $table->dropColumn('member_position');
            }
        });
    }

    private function indexExists(string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'household_registration_document')
            ->where('index_name', $indexName)
            ->exists();
    }
};

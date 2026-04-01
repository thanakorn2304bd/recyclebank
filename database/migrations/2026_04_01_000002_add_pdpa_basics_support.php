<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member', function (Blueprint $table) {
            $table->string('id_card_last4', 4)->nullable()->after('id_card');
            $table->string('id_card_hash', 64)->nullable()->after('id_card_last4');

            $table->index('id_card_last4', 'idx_member_id_card_last4');
            $table->index('id_card_hash', 'idx_member_id_card_hash');
        });

        DB::table('member')
            ->select(['member_id', 'id_card'])
            ->orderBy('member_id')
            ->get()
            ->each(function ($member) {
                $idCard = preg_replace('/\D+/', '', (string) $member->id_card) ?? '';

                DB::table('member')
                    ->where('member_id', $member->member_id)
                    ->update([
                        'id_card_last4' => $idCard !== '' ? substr($idCard, -4) : null,
                        'id_card_hash' => $idCard !== '' ? hash('sha256', $idCard) : null,
                    ]);
            });

        Schema::create('privacy_notice_version', function (Blueprint $table) {
            $table->integer('privacy_notice_version_id')->autoIncrement();
            $table->string('version_code', 20)->unique('uq_privacy_notice_version_code');
            $table->string('title', 150);
            $table->text('summary');
            $table->longText('content');
            $table->dateTime('effective_at');
            $table->boolean('is_active')->default(true);
            $table->integer('published_by')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('effective_at', 'idx_privacy_notice_effective_at');
            $table->index('published_by', 'idx_privacy_notice_published_by');
        });

        Schema::create('privacy_consent', function (Blueprint $table) {
            $table->integer('privacy_consent_id')->autoIncrement();
            $table->integer('user_id')->nullable();
            $table->integer('household_id')->nullable();
            $table->integer('privacy_notice_version_id');
            $table->string('consent_type', 50);
            $table->dateTime('consented_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('consent_notes')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();

            $table->index('user_id', 'idx_privacy_consent_user_id');
            $table->index('household_id', 'idx_privacy_consent_household_id');
            $table->index('privacy_notice_version_id', 'idx_privacy_consent_notice_id');
        });

        Schema::create('data_subject_request', function (Blueprint $table) {
            $table->integer('data_subject_request_id')->autoIncrement();
            $table->string('request_no', 30)->unique('uq_data_subject_request_no');
            $table->integer('household_id')->nullable();
            $table->string('requester_name', 100);
            $table->string('requester_contact', 150)->nullable();
            $table->enum('request_type', ['access', 'correction', 'deletion', 'restriction', 'objection']);
            $table->enum('status', ['submitted', 'in_review', 'completed', 'rejected'])->default('submitted');
            $table->dateTime('submitted_at');
            $table->date('due_at')->nullable();
            $table->integer('assigned_to')->nullable();
            $table->text('request_details');
            $table->text('resolution_notes')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('household_id', 'idx_data_subject_request_household_id');
            $table->index('assigned_to', 'idx_data_subject_request_assigned_to');
            $table->index('status', 'idx_data_subject_request_status');
        });

        Schema::create('security_incident', function (Blueprint $table) {
            $table->integer('security_incident_id')->autoIncrement();
            $table->string('incident_no', 30)->unique('uq_security_incident_no');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'contained', 'reported', 'closed'])->default('open');
            $table->integer('reported_by')->nullable();
            $table->integer('assigned_to')->nullable();
            $table->dateTime('occurred_at')->nullable();
            $table->dateTime('detected_at');
            $table->string('summary', 255);
            $table->string('affected_scope', 255)->nullable();
            $table->integer('affected_record_count')->nullable();
            $table->boolean('notification_required')->default(false);
            $table->dateTime('authority_notified_at')->nullable();
            $table->dateTime('subject_notified_at')->nullable();
            $table->text('impact_details')->nullable();
            $table->text('containment_actions')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->index('reported_by', 'idx_security_incident_reported_by');
            $table->index('assigned_to', 'idx_security_incident_assigned_to');
            $table->index('status', 'idx_security_incident_status');
        });

        Schema::table('privacy_notice_version', function (Blueprint $table) {
            $table->foreign('published_by', 'fk_privacy_notice_published_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('privacy_consent', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_privacy_consent_user_id')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('household_id', 'fk_privacy_consent_household_id')
                ->references('household_id')
                ->on('household')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('privacy_notice_version_id', 'fk_privacy_consent_notice_id')
                ->references('privacy_notice_version_id')
                ->on('privacy_notice_version')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('data_subject_request', function (Blueprint $table) {
            $table->foreign('household_id', 'fk_data_subject_request_household_id')
                ->references('household_id')
                ->on('household')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('assigned_to', 'fk_data_subject_request_assigned_to')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('security_incident', function (Blueprint $table) {
            $table->foreign('reported_by', 'fk_security_incident_reported_by')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();

            $table->foreign('assigned_to', 'fk_security_incident_assigned_to')
                ->references('user_id')
                ->on('user_account')
                ->restrictOnUpdate()
                ->nullOnDelete();
        });

        DB::table('privacy_notice_version')->insert([
            'version_code' => '1.0',
            'title' => 'ประกาศคุ้มครองข้อมูลส่วนบุคคลสำหรับผู้สมัครสมาชิกครัวเรือน',
            'summary' => 'ระบบจะเก็บข้อมูลครัวเรือน รายชื่อสมาชิก เลขบัตรประชาชน และข้อมูลติดต่อเท่าที่จำเป็น เพื่อใช้สมัครสมาชิก ตรวจสอบสิทธิ์ อนุมัติบัญชี ทำธุรกรรม และตรวจสอบย้อนหลังตามหน้าที่ของหน่วยงาน',
            'content' => implode("\n\n", [
                '1. ระบบเก็บข้อมูลเท่าที่จำเป็นต่อการสมัครสมาชิก การอนุมัติครัวเรือน การทำธุรกรรม และการจัดทำรายงานที่เกี่ยวข้อง',
                '2. ข้อมูลส่วนบุคคลที่เก็บอาจรวมถึงชื่อผู้ติดต่อ เบอร์โทร รายชื่อสมาชิกในครัวเรือน และเลขบัตรประชาชนของสมาชิก',
                '3. เจ้าหน้าที่ที่ได้รับมอบหมายเท่านั้นจึงจะเข้าถึงข้อมูลส่วนบุคคลตามหน้าที่ และระบบมีการบันทึกประวัติการใช้งานเพื่อตรวจสอบย้อนหลัง',
                '4. เจ้าของข้อมูลสามารถยื่นคำขอเกี่ยวกับข้อมูลส่วนบุคคล เช่น ขอเข้าถึง ขอแก้ไข หรือขอลบข้อมูล ผ่านเจ้าหน้าที่ผู้ดูแลระบบ',
                '5. หากเกิดเหตุผิดปกติหรือเหตุข้อมูลรั่วไหล ระบบจะบันทึกเหตุการณ์และติดตามการแจ้งหน่วยงานหรือเจ้าของข้อมูลตามความเหมาะสม',
            ]),
            'effective_at' => now(),
            'is_active' => true,
            'published_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('security_incident', function (Blueprint $table) {
            $table->dropForeign('fk_security_incident_reported_by');
            $table->dropForeign('fk_security_incident_assigned_to');
        });

        Schema::table('data_subject_request', function (Blueprint $table) {
            $table->dropForeign('fk_data_subject_request_household_id');
            $table->dropForeign('fk_data_subject_request_assigned_to');
        });

        Schema::table('privacy_consent', function (Blueprint $table) {
            $table->dropForeign('fk_privacy_consent_user_id');
            $table->dropForeign('fk_privacy_consent_household_id');
            $table->dropForeign('fk_privacy_consent_notice_id');
        });

        Schema::table('privacy_notice_version', function (Blueprint $table) {
            $table->dropForeign('fk_privacy_notice_published_by');
        });

        Schema::dropIfExists('security_incident');
        Schema::dropIfExists('data_subject_request');
        Schema::dropIfExists('privacy_consent');
        Schema::dropIfExists('privacy_notice_version');

        Schema::table('member', function (Blueprint $table) {
            $table->dropIndex('idx_member_id_card_last4');
            $table->dropIndex('idx_member_id_card_hash');
            $table->dropColumn([
                'id_card_last4',
                'id_card_hash',
            ]);
        });
    }
};

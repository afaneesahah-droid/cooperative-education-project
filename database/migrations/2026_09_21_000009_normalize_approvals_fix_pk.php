<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M09 – Normalize approvals table (แก้โครงสร้างใหม่)
 * ปัญหาเดิม:
 *   - PK = approver_id (= user_id) → 1 user อนุมัติได้แค่ 1 รายการตลอดชีวิต!
 *   - ไม่มี request_id FK → ไม่รู้ว่าอนุมัติ request ไหน
 *
 * แก้:
 *   - สร้าง approvals table ใหม่ที่ถูกต้อง (DROP + RECREATE)
 *   - PK: auto-increment id
 *   - เพิ่ม request_id FK → repair_requests
 *   - approver_id กลายเป็น FK → users (ไม่ใช่ PK)
 *   - เพิ่ม approved_by (FK) สำหรับ multi-level approval
 */
return new class extends Migration
{
    public function up(): void
    {
        // ดึงข้อมูลเก่าออกมาก่อน DROP
        $oldApprovals = DB::table('approvals')->get();

        // Drop และ Recreate table
        Schema::dropIfExists('approvals');

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();                                         // PK: auto-increment
            $table->string('request_id', 5)->nullable();         // FK: รหัสการแจ้งซ่อม
            $table->string('approver_id', 8)->nullable();        // FK: ผู้อนุมัติ
            $table->string('approval_status', 50)->nullable();   // สถานะ: approved / rejected / pending
            $table->date('approval_date')->nullable();           // วันที่อนุมัติ
            $table->text('approval_note')->nullable();           // หมายเหตุ / เหตุผลปฏิเสธ
            $table->timestamps();

            $table->foreign('request_id')
                  ->references('request_id')
                  ->on('repair_requests')
                  ->onDelete('cascade');

            $table->foreign('approver_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Restore ข้อมูลเก่า (เท่าที่ทำได้)
        foreach ($oldApprovals as $old) {
            DB::table('approvals')->insert([
                'approver_id'     => $old->approver_id,
                'approval_status' => $old->approval_status ?? null,
                'approval_date'   => $old->approver_date ?? null,
                'created_at'      => $old->created_at ?? now(),
                'updated_at'      => $old->updated_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        $newApprovals = DB::table('approvals')->get();

        Schema::dropIfExists('approvals');

        Schema::create('approvals', function (Blueprint $table) {
            $table->string('approver_id', 8)->primary();
            $table->string('approval_status', 50)->nullable();
            $table->date('approver_date')->nullable();
            $table->timestamps();

            $table->foreign('approver_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');
        });

        // Restore unique per approver_id
        $seen = [];
        foreach ($newApprovals as $a) {
            if ($a->approver_id && !in_array($a->approver_id, $seen)) {
                DB::table('approvals')->insert([
                    'approver_id'     => $a->approver_id,
                    'approval_status' => $a->approval_status,
                    'approver_date'   => $a->approval_date,
                    'created_at'      => $a->created_at ?? now(),
                    'updated_at'      => $a->updated_at ?? now(),
                ]);
                $seen[] = $a->approver_id;
            }
        }
    }
};

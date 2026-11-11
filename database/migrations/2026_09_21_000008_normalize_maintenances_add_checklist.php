<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M08 – Normalize maintenances table
 * 3NF Fix: ย้าย checklist booleans จาก repair_requests มาไว้ที่ maintenances
 *   - checked_power, checked_cleaning, checked_ping
 *   - repair_hw, repair_sw, repair_net
 * เหตุผล: ค่าเหล่านี้เป็น "ผลการตรวจสอบ" ที่ช่างทำ ไม่ใช่ property ของ request
 *
 * นอกจากนี้ maintenances ต้องอ้างอิงตรงไปที่ request_id
 * (ไม่ผ่าน repair_detail_id → repair_details → request)
 * เพื่อให้ query ง่ายขึ้นและลด transitive dependency
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: เพิ่ม checklist columns ใน maintenances
        Schema::table('maintenances', function (Blueprint $table) {
            $table->boolean('checked_power')->default(false)->after('repair_action');
            $table->boolean('checked_cleaning')->default(false)->after('checked_power');
            $table->boolean('checked_ping')->default(false)->after('checked_cleaning');
            $table->boolean('repair_hw')->default(false)->after('checked_ping');
            $table->boolean('repair_sw')->default(false)->after('repair_hw');
            $table->boolean('repair_net')->default(false)->after('repair_sw');
        });

        // Step 2: เพิ่ม request_id FK โดยตรง (shortcut ผ่าน repair_details)
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('request_id', 5)->nullable()->after('repair_detail_id');
            $table->foreign('request_id')
                  ->references('request_id')
                  ->on('repair_requests')
                  ->onDelete('cascade');
        });

        // Step 3: Migrate request_id จาก repair_details → maintenances
        DB::statement("
            UPDATE maintenances m
            JOIN repair_details rd ON m.repair_detail_id = rd.repair_detail_id
            SET m.request_id = rd.approval_id
            WHERE m.request_id IS NULL
        ");

        // Step 4: Migrate checklist booleans จาก repair_requests → maintenances
        // (ใช้ค่าจาก repair_requests ของ request นั้นๆ)
        DB::statement("
            UPDATE maintenances m
            JOIN repair_requests rr ON m.request_id = rr.request_id
            SET
                m.checked_power   = rr.checked_power,
                m.checked_cleaning = rr.checked_cleaning,
                m.checked_ping    = rr.checked_ping,
                m.repair_hw       = rr.repair_hw,
                m.repair_sw       = rr.repair_sw,
                m.repair_net      = rr.repair_net
            WHERE m.request_id IS NOT NULL
        ");

        // Step 5: ลบ checklist columns ออกจาก repair_requests (ย้ายไป maintenances แล้ว)
        Schema::table('repair_requests', function (Blueprint $table) {
            $columns = Schema::getColumnListing('repair_requests');
            $toDrop = array_filter(
                ['checked_power', 'checked_cleaning', 'checked_ping', 'repair_hw', 'repair_sw', 'repair_net'],
                fn($col) => in_array($col, $columns)
            );
            if (!empty($toDrop)) {
                $table->dropColumn(array_values($toDrop));
            }
        });
    }

    public function down(): void
    {
        // Restore checklist columns to repair_requests
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->boolean('checked_power')->default(false);
            $table->boolean('checked_cleaning')->default(false);
            $table->boolean('checked_ping')->default(false);
            $table->boolean('repair_hw')->default(false);
            $table->boolean('repair_sw')->default(false);
            $table->boolean('repair_net')->default(false);
        });

        // Restore from maintenances (latest per request)
        DB::statement("
            UPDATE repair_requests rr
            JOIN (
                SELECT m.request_id, m.checked_power, m.checked_cleaning, m.checked_ping,
                       m.repair_hw, m.repair_sw, m.repair_net
                FROM maintenances m
                WHERE (m.request_id, m.created_at) IN (
                    SELECT request_id, MAX(created_at) FROM maintenances GROUP BY request_id
                )
            ) latest ON rr.request_id = latest.request_id
            SET
                rr.checked_power   = latest.checked_power,
                rr.checked_cleaning = latest.checked_cleaning,
                rr.checked_ping    = latest.checked_ping,
                rr.repair_hw       = latest.repair_hw,
                rr.repair_sw       = latest.repair_sw,
                rr.repair_net      = latest.repair_net
        ");

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn([
                'request_id',
                'checked_power', 'checked_cleaning', 'checked_ping',
                'repair_hw', 'repair_sw', 'repair_net',
            ]);
        });
    }
};

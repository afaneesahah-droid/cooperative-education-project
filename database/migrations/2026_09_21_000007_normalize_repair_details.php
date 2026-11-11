<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M07 – Normalize repair_details table
 * 2NF Fix: ลบ `building_id`, `room_number` ออก
 * เหตุผล: ข้อมูลเหล่านี้ซ้ำซ้อนกับ repair_requests → equipment → room
 *         repair_details ควรเก็บแค่ timeline การซ่อมและสถานะ ไม่ใช่ตำแหน่ง
 *
 * ลบ `repair_start_date` และ `completion_date` ออกด้วย
 * เพราะวันที่จริงควรอยู่ใน maintenances (บันทึกการปฏิบัติจริง)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_details', function (Blueprint $table) {
            $columns = Schema::getColumnListing('repair_details');

            $toDrop = array_filter(
                ['room_number', 'repair_start_date', 'completion_date'],
                fn($col) => in_array($col, $columns)
            );

            if (in_array('building_id', $columns)) {
                try { $table->dropForeign(['building_id']); } catch (\Exception $e) {}
                $toDrop[] = 'building_id';
            }

            if (!empty($toDrop)) {
                $table->dropColumn(array_values($toDrop));
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_details', function (Blueprint $table) {
            $table->string('building_id', 5)->nullable();
            $table->string('room_number', 3)->nullable();
            $table->date('repair_start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->foreign('building_id')->references('building_id')->on('buildings')->onDelete('set null');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M06 – Normalize repair_requests table (ใหญ่ที่สุด)
 * Fix:
 *   1NF: ลบ `location` string composite (→ ใช้ equipment.room_id แทน)
 *   2NF: ลบ `building` string, `room_number` string, `equipment_number` string (redundant กับ equipment)
 *   3NF: ลบ `assigned_to` (→ ใช้ assignments table แทน)
 *        ลบ `request_date` (= created_at)
 *        ย้าย checklist booleans → maintenances table
 *        ลบ `request_description` (ซ้ำกับ `description`)
 *
 * NOTE: checked_power/cleaning/ping, repair_hw/sw/net ถูก migrate ไป maintenances
 *       แต่เพื่อ backward compat จะ keep ไว้ก่อน แล้วค่อย migrate ใน M08
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Migrate assigned_to → assignments table
        // ดึง records ที่มี assigned_to แต่ยังไม่มีใน assignments
        $requests = DB::table('repair_requests')
            ->whereNotNull('assigned_to')
            ->get();

        foreach ($requests as $req) {
            $exists = DB::table('assignments')
                ->where('request_id', $req->request_id)
                ->where('user_id', $req->assigned_to)
                ->exists();

            if (!$exists) {
                // หา assignment_id ถัดไปที่ไม่ชน
                $lastId = DB::table('assignments')->where('assignment_id', 'like', 'A%')->max('assignment_id');
                $num = $lastId ? ((int)substr($lastId, 1) + 1) : 1;
                $nextId = 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
                while (DB::table('assignments')->where('assignment_id', $nextId)->exists()) {
                    $num++;
                    $nextId = 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
                }

                DB::table('assignments')->insert([
                    'assignment_id'    => $nextId,
                    'request_id'       => $req->request_id,
                    'user_id'          => $req->assigned_to,
                    'assign_date'      => $req->accepted_at ?? $req->created_at,
                    'task_description' => null,
                    'task_priority'    => $req->priority,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // Step 2: ลบ redundant columns
        Schema::table('repair_requests', function (Blueprint $table) {
            $columns = Schema::getColumnListing('repair_requests');

            // ลบ string location/building/room_number (redundant กับ equipment.room_id)
            $toDrop = array_filter(
                ['location', 'building', 'room_number', 'equipment_number', 'request_description', 'request_date', 'assigned_to'],
                fn($col) => in_array($col, $columns)
            );

            if (!empty($toDrop)) {
                // ต้อง drop FK ก่อน
                try { $table->dropForeign(['assigned_to']); } catch (\Exception $e) {}

                $table->dropColumn(array_values($toDrop));
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->string('location', 255)->nullable();
            $table->string('building', 80)->nullable();
            $table->string('room_number', 3)->nullable();
            $table->string('equipment_number', 2)->nullable();
            $table->string('request_description', 255)->nullable();
            $table->date('request_date')->nullable();
            $table->string('assigned_to', 8)->nullable();
            $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('set null');
        });

        // Restore assigned_to from assignments table (latest assignment per request)
        DB::statement("
            UPDATE repair_requests rr
            JOIN (
                SELECT request_id, user_id
                FROM assignments
                WHERE (request_id, created_at) IN (
                    SELECT request_id, MAX(created_at) FROM assignments GROUP BY request_id
                )
            ) a ON rr.request_id = a.request_id
            SET rr.assigned_to = a.user_id
        ");
    }
};

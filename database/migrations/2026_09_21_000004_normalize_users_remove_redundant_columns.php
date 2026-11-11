<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M04 – Normalize users table
 * 2NF / Redundancy Fix:
 *   - ลบ `name` column (computed: prefix+first_name+last_name → accessor ใน Model แล้ว)
 *   - ลบ `phone_number` (ซ้ำกับ `phone`)
 *   - ลบ `position` string (ซ้ำกับ `position_id` FK)
 *   - ลบ `department` string (ซ้ำกับ `department_id` FK)
 *
 * NOTE: users.name ถูก computed ใน User::getNameAttribute() accessor แล้ว
 *       จึงปลอดภัยที่จะลบ column จริงออก
 */
return new class extends Migration
{
    public function up(): void
    {
        // ก่อนลบ — ตรวจว่า position_id และ department_id มีข้อมูลครบ
        // migrate: ถ้า position_id ว่าง ให้จับคู่จาก position string
        $posMap = DB::table('positions')->pluck('position_id', 'position_name');
        $deptMap = DB::table('departments')->pluck('department_id', 'department_name');

        $users = DB::table('users')
            ->where(function ($q) {
                $q->whereNull('position_id')->whereNotNull('position');
            })
            ->orWhere(function ($q) {
                $q->whereNull('department_id')->whereNotNull('department');
            })
            ->get();

        foreach ($users as $user) {
            $updates = [];
            if (empty($user->position_id) && !empty($user->position)) {
                $updates['position_id'] = $posMap[$user->position] ?? null;
            }
            if (empty($user->department_id) && !empty($user->department)) {
                $updates['department_id'] = $deptMap[$user->department] ?? null;
            }
            if (!empty($updates)) {
                DB::table('users')->where('user_id', $user->user_id)->update($updates);
            }
        }

        // ลบ redundant columns
        Schema::table('users', function (Blueprint $table) {
            // ตรวจว่าคอลัมน์มีอยู่จริงก่อนลบ (safe drop)
            $columns = Schema::getColumnListing('users');

            $toDrop = array_filter(
                ['phone_number', 'position', 'department'],
                fn($col) => in_array($col, $columns)
            );

            if (!empty($toDrop)) {
                $table->dropColumn(array_values($toDrop));
            }
        });

        // ลบ name column แยก (เพราะ MySQL อาจมีปัญหากับ multi-drop + accessor)
        if (Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 30)->nullable()->after('user_id');
            $table->string('phone_number', 10)->nullable()->after('phone');
            $table->string('position', 255)->nullable()->after('position_id');
            $table->string('department', 255)->nullable()->after('department_id');
        });

        // Restore name from first_name + last_name
        DB::statement("UPDATE users SET name = TRIM(CONCAT(COALESCE(prefix,''), ' ', COALESCE(first_name,''), ' ', COALESCE(last_name,'')))");

        // Restore position/department strings from FK
        DB::statement("
            UPDATE users u
            JOIN positions p ON u.position_id = p.position_id
            SET u.position = p.position_name
            WHERE u.position IS NULL
        ");
        DB::statement("
            UPDATE users u
            JOIN departments d ON u.department_id = d.department_id
            SET u.department = d.department_name
            WHERE u.department IS NULL
        ");
    }
};

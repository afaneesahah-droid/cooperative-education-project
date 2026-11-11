<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M05 – Normalize repair_equipments table
 * 2NF / Redundancy Fix:
 *   - รวม `equipment_name` + `name` → เหลือแค่ `name` (ลบ equipment_name)
 *   - รวม `type` + `category` → เหลือแค่ `category` (ลบ type)
 *   - เพิ่ม `asset_kind_id` FK → asset_kinds (แทน category string)
 *   - เปลี่ยน `responsible_person` string → `responsible_person_id` FK → users
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Step 1: Migrate equipment_name → name (ถ้า name ว่าง) ---
        DB::statement("
            UPDATE repair_equipments
            SET name = equipment_name
            WHERE (name IS NULL OR name = '') AND equipment_name IS NOT NULL AND equipment_name != ''
        ");

        // --- Step 2: Migrate type → category (ถ้า category ว่าง) ---
        DB::statement("
            UPDATE repair_equipments
            SET category = type
            WHERE (category IS NULL OR category = '') AND type IS NOT NULL AND type != ''
        ");

        // --- Step 3: เพิ่ม asset_kind_id FK ---
        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_kind_id')->nullable()->after('category');
            $table->foreign('asset_kind_id')
                  ->references('id')
                  ->on('asset_kinds')
                  ->onDelete('set null');
        });

        // --- Step 4: Migrate category string → asset_kind_id ---
        $kindMap = DB::table('asset_kinds')->pluck('id', 'name');
        $devices = DB::table('repair_equipments')->whereNotNull('category')->get();
        foreach ($devices as $device) {
            $kindId = $kindMap[$device->category] ?? null;
            if ($kindId) {
                DB::table('repair_equipments')
                  ->where('equipment_id', $device->equipment_id)
                  ->update(['asset_kind_id' => $kindId]);
            }
        }

        // --- Step 5: เพิ่ม responsible_person_id FK ---
        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->string('responsible_person_id', 8)->nullable()->after('responsible_person');
            $table->foreign('responsible_person_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // --- Step 6: Migrate responsible_person string → responsible_person_id ---
        // Try matching by name (first_name + last_name) or user_id directly
        $usersAll = DB::table('users')->get();
        $nameToId = [];
        foreach ($usersAll as $u) {
            $fullName = trim(($u->prefix ? $u->prefix . ' ' : '') . ($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
            if ($fullName) $nameToId[$fullName] = $u->user_id;
            // Also try user_id directly
            $nameToId[$u->user_id] = $u->user_id;
        }

        $devices = DB::table('repair_equipments')->whereNotNull('responsible_person')->get();
        foreach ($devices as $device) {
            $userId = $nameToId[$device->responsible_person] ?? null;
            if ($userId) {
                DB::table('repair_equipments')
                  ->where('equipment_id', $device->equipment_id)
                  ->update(['responsible_person_id' => $userId]);
            }
        }

        // --- Step 7: ลบ redundant columns ---
        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->dropColumn(['equipment_name', 'type', 'responsible_person']);
        });
    }

    public function down(): void
    {
        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->string('equipment_name', 40)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('responsible_person', 255)->nullable();
        });

        // Restore from normalized columns
        DB::statement("UPDATE repair_equipments SET equipment_name = name WHERE equipment_name IS NULL");
        DB::statement("UPDATE repair_equipments SET type = category WHERE type IS NULL");
        DB::statement("
            UPDATE repair_equipments re
            JOIN users u ON re.responsible_person_id = u.user_id
            SET re.responsible_person = TRIM(CONCAT(COALESCE(u.prefix,''), ' ', COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')))
            WHERE re.responsible_person IS NULL
        ");

        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->dropForeign(['asset_kind_id']);
            $table->dropColumn('asset_kind_id');
            $table->dropForeign(['responsible_person_id']);
            $table->dropColumn('responsible_person_id');
        });
    }
};

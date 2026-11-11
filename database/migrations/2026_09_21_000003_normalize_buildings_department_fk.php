<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M03 – Normalize buildings table
 * 3NF Fix:
 *   - `department_name` (string) → `department_id` FK → departments
 *   - ลบ `room_number` ออก (room_number ควรอยู่ในตาราง rooms เท่านั้น)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: เพิ่ม department_id FK column
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('department_id', 2)->nullable()->after('department_name');
            $table->foreign('department_id')
                  ->references('department_id')
                  ->on('departments')
                  ->onDelete('set null');
        });

        // Step 2: Migrate data — จับคู่ department_name string → department_id
        if (Schema::hasTable('departments')) {
            $deptMap = DB::table('departments')->pluck('department_id', 'department_name');
            $buildings = DB::table('buildings')->whereNotNull('department_name')->get();
            foreach ($buildings as $building) {
                $deptId = $deptMap[$building->department_name] ?? null;
                if ($deptId) {
                    DB::table('buildings')
                      ->where('building_id', $building->building_id)
                      ->update(['department_id' => $deptId]);
                }
            }
        }

        // Step 3: ลบ department_name string และ room_number ออก
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn(['department_name', 'room_number']);
        });
    }

    public function down(): void
    {
        // Restore removed columns
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('department_name', 50)->nullable();
            $table->string('room_number', 5)->nullable();
        });

        // Restore department_name from FK
        if (Schema::hasTable('departments')) {
            $buildings = DB::table('buildings')->whereNotNull('department_id')->get();
            foreach ($buildings as $building) {
                $dept = DB::table('departments')->where('department_id', $building->department_id)->first();
                if ($dept) {
                    DB::table('buildings')
                      ->where('building_id', $building->building_id)
                      ->update(['department_name' => $dept->department_name]);
                }
            }
        }

        Schema::table('buildings', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M01 – Normalize departments table
 * 3NF Fix: เชื่อม `group` string → `department_group_id` FK → department_groups
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: เพิ่มคอลัมน์ FK ใหม่ (nullable ก่อน)
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('department_group_id')->nullable()->after('group');
            $table->foreign('department_group_id')
                  ->references('id')
                  ->on('department_groups')
                  ->onDelete('set null');
        });

        // Step 2: Migrate data — จับคู่ group string (group_code หรือ group_name) → department_group_id
        if (Schema::hasTable('department_groups')) {
            $groups = DB::table('department_groups')->get();
            $groupMap = [];
            foreach ($groups as $g) {
                $groupMap[$g->group_code] = $g->id;
                $groupMap[$g->group_name] = $g->id;
            }

            $departments = DB::table('departments')->whereNotNull('group')->get();
            foreach ($departments as $dept) {
                $groupId = $groupMap[$dept->group] ?? null;
                if ($groupId) {
                    DB::table('departments')
                      ->where('department_id', $dept->department_id)
                      ->update(['department_group_id' => $groupId]);
                }
            }
        }

        // Step 3: ลบคอลัมน์เก่า (group string) ออก
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('group', 50)->nullable()->after('department_name');
        });

        // Restore group names from FK
        if (Schema::hasTable('department_groups')) {
            $departments = DB::table('departments')->whereNotNull('department_group_id')->get();
            foreach ($departments as $dept) {
                $group = DB::table('department_groups')->where('id', $dept->department_group_id)->first();
                if ($group) {
                    DB::table('departments')
                      ->where('department_id', $dept->department_id)
                      ->update(['group' => $group->group_code]);
                }
            }
        }

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['department_group_id']);
            $table->dropColumn('department_group_id');
        });
    }
};

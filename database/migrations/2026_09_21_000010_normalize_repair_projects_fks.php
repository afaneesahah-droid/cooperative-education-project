<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M10 – Normalize repair_projects table
 * 3NF Fix:
 *   - `department` (string) → `department_id` FK (departments) AND `department_group_id` FK (department_groups)
 *   - `responsible_person` (string) → `responsible_person_id` FK → users
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: เพิ่ม FK columns ใหม่
        Schema::table('repair_projects', function (Blueprint $table) {
            $table->string('department_id', 2)->nullable()->after('department');
            $table->unsignedBigInteger('department_group_id')->nullable()->after('department_id');
            $table->string('responsible_person_id', 8)->nullable()->after('responsible_person');

            $table->foreign('department_id')
                  ->references('department_id')
                  ->on('departments')
                  ->onDelete('set null');

            if (Schema::hasTable('department_groups')) {
                $table->foreign('department_group_id')
                      ->references('id')
                      ->on('department_groups')
                      ->onDelete('set null');
            }

            $table->foreign('responsible_person_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null');
        });

        // Step 2: Migrate department string → department_id / department_group_id
        $deptMap = DB::table('departments')->pluck('department_id', 'department_name');

        $groupMap = [];
        if (Schema::hasTable('department_groups')) {
            $groups = DB::table('department_groups')->get();
            foreach ($groups as $g) {
                $groupMap[$g->group_code] = $g->id;
                $groupMap[$g->group_name] = $g->id;
            }
        }

        $projects = DB::table('repair_projects')->whereNotNull('department')->get();
        foreach ($projects as $proj) {
            $dept = $proj->department;
            $deptId = $deptMap[$dept] ?? null;
            $groupId = $groupMap[$dept] ?? null;

            if (!$groupId && !$deptId) {
                if (str_contains($dept, 'ทรัพยากร')) $groupId = 1;
                elseif (str_contains($dept, 'แผน') || str_contains($dept, 'นโยบาย')) $groupId = 2;
                elseif (str_contains($dept, 'นักเรียน') || str_contains($dept, 'นักศึกษา')) $groupId = 3;
                elseif (str_contains($dept, 'วิชาการ')) $groupId = 4;
            }

            DB::table('repair_projects')
              ->where('id', $proj->id)
              ->update([
                  'department_id' => $deptId,
                  'department_group_id' => $groupId,
              ]);
        }

        // Step 3: Migrate responsible_person string → responsible_person_id
        $usersAll = DB::table('users')->get();
        $nameToId = [];
        foreach ($usersAll as $u) {
            $fullName = trim(($u->prefix ?? '') . ' ' . ($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
            if ($fullName) $nameToId[trim($fullName)] = $u->user_id;
            $nameToId[$u->user_id] = $u->user_id;
        }

        $projects = DB::table('repair_projects')->whereNotNull('responsible_person')->get();
        foreach ($projects as $proj) {
            $userId = $nameToId[$proj->responsible_person] ?? null;
            if ($userId) {
                DB::table('repair_projects')
                  ->where('id', $proj->id)
                  ->update(['responsible_person_id' => $userId]);
            }
        }

        // Step 4: ลบ string columns เก่า
        Schema::table('repair_projects', function (Blueprint $table) {
            $table->dropColumn(['department', 'responsible_person']);
        });
    }

    public function down(): void
    {
        Schema::table('repair_projects', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('responsible_person')->nullable();
        });

        // Restore strings from FKs
        DB::statement("
            UPDATE repair_projects rp
            JOIN departments d ON rp.department_id = d.department_id
            SET rp.department = d.department_name
            WHERE rp.department IS NULL
        ");
        if (Schema::hasTable('department_groups')) {
            DB::statement("
                UPDATE repair_projects rp
                JOIN department_groups dg ON rp.department_group_id = dg.id
                SET rp.department = dg.group_name
                WHERE rp.department IS NULL
            ");
        }
        DB::statement("
            UPDATE repair_projects rp
            JOIN users u ON rp.responsible_person_id = u.user_id
            SET rp.responsible_person = TRIM(CONCAT(COALESCE(u.prefix,''), ' ', COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')))
            WHERE rp.responsible_person IS NULL
        ");

        Schema::table('repair_projects', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            if (Schema::hasColumn('repair_projects', 'department_group_id')) {
                try { $table->dropForeign(['department_group_id']); } catch (\Exception $e) {}
                $table->dropColumn('department_group_id');
            }
            $table->dropForeign(['responsible_person_id']);
            $table->dropColumn('responsible_person_id');
        });
    }
};

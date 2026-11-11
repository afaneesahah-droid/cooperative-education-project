<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * M11 – Normalize department_budgets table
 * 3NF Fix:
 *   - `department` (string) → `department_group_id` FK (department_groups) AND/OR `department_id` FK (departments)
 *   - อัปเดต unique constraint ให้ใช้ FK แทน department string
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: เพิ่ม department_group_id และ department_id FK
        Schema::table('department_budgets', function (Blueprint $table) {
            $table->unsignedBigInteger('department_group_id')->nullable()->after('department');
            $table->string('department_id', 2)->nullable()->after('department_group_id');

            if (Schema::hasTable('department_groups')) {
                $table->foreign('department_group_id')
                      ->references('id')
                      ->on('department_groups')
                      ->onDelete('cascade');
            }

            $table->foreign('department_id')
                  ->references('department_id')
                  ->on('departments')
                  ->onDelete('cascade');
        });

        // Step 2: Migrate department string → department_group_id / department_id
        $deptMap = DB::table('departments')->pluck('department_id', 'department_name');

        $groupMap = [];
        if (Schema::hasTable('department_groups')) {
            $groups = DB::table('department_groups')->get();
            foreach ($groups as $g) {
                $groupMap[$g->group_code] = $g->id;
                $groupMap[$g->group_name] = $g->id;
            }
        }

        $budgets = DB::table('department_budgets')->whereNotNull('department')->get();
        foreach ($budgets as $budget) {
            $dept = $budget->department;
            $groupId = $groupMap[$dept] ?? null;
            $deptId  = $deptMap[$dept] ?? null;

            if (!$groupId && !$deptId) {
                if (str_contains($dept, 'ทรัพยากร')) $groupId = 1;
                elseif (str_contains($dept, 'แผน') || str_contains($dept, 'นโยบาย')) $groupId = 2;
                elseif (str_contains($dept, 'นักเรียน') || str_contains($dept, 'นักศึกษา')) $groupId = 3;
                elseif (str_contains($dept, 'วิชาการ')) $groupId = 4;
            }

            DB::table('department_budgets')
              ->where('id', $budget->id)
              ->update([
                  'department_group_id' => $groupId,
                  'department_id'       => $deptId,
              ]);
        }

        // Step 3: ลบ old unique constraint และ department string column
        Schema::table('department_budgets', function (Blueprint $table) {
            try {
                $table->dropUnique('dept_year_budget_unique');
            } catch (\Exception $e) {}

            $table->dropColumn('department');
        });

        // Step 4: เพิ่ม unique constraint ใหม่
        Schema::table('department_budgets', function (Blueprint $table) {
            $table->unique(['department_group_id', 'department_id', 'fiscal_year', 'budget_type'], 'dept_fk_year_budget_unique');
        });
    }

    public function down(): void
    {
        // Drop new unique + restore old column
        Schema::table('department_budgets', function (Blueprint $table) {
            try {
                $table->dropUnique('dept_fk_year_budget_unique');
            } catch (\Exception $e) {}

            $table->string('department')->nullable()->after('id');
        });

        // Restore department string from FKs
        if (Schema::hasTable('department_groups')) {
            DB::statement("
                UPDATE department_budgets db
                JOIN department_groups dg ON db.department_group_id = dg.id
                SET db.department = dg.group_name
                WHERE db.department IS NULL
            ");
        }
        DB::statement("
            UPDATE department_budgets db
            JOIN departments d ON db.department_id = d.department_id
            SET db.department = d.department_name
            WHERE db.department IS NULL
        ");

        // Re-add original unique constraint
        Schema::table('department_budgets', function (Blueprint $table) {
            $table->unique(['department', 'fiscal_year', 'budget_type'], 'dept_year_budget_unique');
            if (Schema::hasColumn('department_budgets', 'department_group_id')) {
                try { $table->dropForeign(['department_group_id']); } catch (\Exception $e) {}
                $table->dropColumn('department_group_id');
            }
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DepartmentBudget extends Model
{
    use HasFactory;

    protected $table = 'department_budgets';

    protected $fillable = [
        'department',          // intercepted by mutator -> department_group_id / department_id (M11 compat)
        'department_group_id', // FK → department_groups (M11)
        'department_id',       // FK → departments (M11)
        'fiscal_year',
        'budget_type',
        'allocated_amount',
        'description',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function departmentGroup()
    {
        return $this->belongsTo(DepartmentGroup::class, 'department_group_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    // ─── Accessors & Mutators (Backward Compatibility) ──────────────────────

    public function getDepartmentAttribute()
    {
        return optional($this->departmentGroup)->group_name
            ?? optional($this->getRelationValue('department'))->department_name
            ?? ($this->attributes['department'] ?? '');
    }

    public function setDepartmentAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['department_group_id'] = null;
            $this->attributes['department_id'] = null;
            return;
        }

        // Try department_groups first
        $group = DepartmentGroup::where('group_name', $value)
            ->orWhere('group_code', $value)
            ->first();

        if (!$group) {
            // Fuzzy match for 4 groups
            if (str_contains($value, 'ทรัพยากร')) $group = DepartmentGroup::find(1);
            elseif (str_contains($value, 'แผน') || str_contains($value, 'นโยบาย')) $group = DepartmentGroup::find(2);
            elseif (str_contains($value, 'นักเรียน') || str_contains($value, 'นักศึกษา')) $group = DepartmentGroup::find(3);
            elseif (str_contains($value, 'วิชาการ')) $group = DepartmentGroup::find(4);
        }

        if ($group) {
            $this->attributes['department_group_id'] = $group->id;
            return;
        }

        // Try departments
        $dept = Department::where('department_name', $value)
            ->orWhere('department_id', $value)
            ->first();
        if ($dept) {
            $this->attributes['department_id'] = $dept->department_id;
        }
    }

    /**
     * Standard budget types used across college
     */
    public static function getDefaultBudgetTypes()
    {
        return [
            'งบประมาณกลาง' => [
                'name' => 'งบประมาณกลาง',
                'icon' => 'fa-building-columns',
                'color' => 'blue',
            ],
            'งบพัฒนาสถานศึกษา' => [
                'name' => 'งบพัฒนาสถานศึกษา',
                'icon' => 'fa-graduation-cap',
                'color' => 'emerald',
            ],
            'งบเงินรายได้' => [
                'name' => 'งบเงินรายได้',
                'icon' => 'fa-sack-dollar',
                'color' => 'amber',
            ],
            'งบอุดหนุน' => [
                'name' => 'งบอุดหนุน',
                'icon' => 'fa-hand-holding-dollar',
                'color' => 'purple',
            ],
        ];
    }

    /**
     * Standard departments list
     */
    public static function getDefaultDepartments()
    {
        if (Schema::hasTable('department_groups') || Schema::hasTable('departments_group')) {
            try {
                $groups = DepartmentGroup::orderBy('id')->pluck('group_name')->toArray();
                if (!empty($groups)) {
                    return $groups;
                }
            } catch (\Exception $e) {}
        }

        return [
            'ฝ่ายบริหารทรัพยากร',
            'ฝ่ายนโยบายและแผน',
            'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า',
            'ฝ่ายวิชาการ',
        ];
    }

    /**
     * Calculate comprehensive statistics for all departments for a given fiscal year
     */
    public static function getStatsForYear($fiscalYear)
    {
        $departments = self::getDefaultDepartments();
        $budgetTypes = self::getDefaultBudgetTypes();

        // Fetch existing allocations for this fiscal year with relationships
        $allAllocations = self::with(['departmentGroup', 'department'])
            ->where('fiscal_year', $fiscalYear)
            ->get();

        // Fetch all repair projects for this fiscal year with relationships
        $projects = RepairProject::with(['department', 'departmentGroup'])
            ->where('fiscal_year', $fiscalYear)
            ->get();

        $stats = [];
        $overallTotalAllocated = 0;
        $overallTotalInProjects = 0;
        $overallTotalUsed = 0;

        foreach ($departments as $dept) {
            // Match allocations for this dept (by group_name, department_name, or fuzzy)
            $deptAllocations = $allAllocations->filter(function ($item) use ($dept) {
                $itemDept = $item->department;
                return $itemDept === $dept
                    || (str_contains($dept, 'ทรัพยากร') && str_contains($itemDept, 'ทรัพยากร'))
                    || (str_contains($dept, 'แผน') && str_contains($itemDept, 'แผน'))
                    || (str_contains($dept, 'นักศึกษา') && str_contains($itemDept, 'นักศึกษา'))
                    || (str_contains($dept, 'วิชาการ') && str_contains($itemDept, 'วิชาการ'));
            })->keyBy('budget_type');

            // Match projects for this dept
            $deptProjects = $projects->filter(function ($proj) use ($dept) {
                $projDept = $proj->department;
                return $projDept === $dept
                    || (str_contains($dept, 'ทรัพยากร') && str_contains($projDept, 'ทรัพยากร'))
                    || (str_contains($dept, 'แผน') && str_contains($projDept, 'แผน'))
                    || (str_contains($dept, 'นักศึกษา') && str_contains($projDept, 'นักศึกษา'))
                    || (str_contains($dept, 'วิชาการ') && str_contains($projDept, 'วิชาการ'));
            });

            $totalRecord = $deptAllocations->get('งบประมาณรวม');
            if ($totalRecord) {
                $deptTotalAllocated = (float)$totalRecord->allocated_amount;
            } else {
                $deptTotalAllocated = (float)$deptAllocations->sum('allocated_amount');
            }

            $typeBreakdown = [];
            foreach ($budgetTypes as $typeKey => $meta) {
                $allocated = 0.0;
                if ($deptAllocations->has($typeKey)) {
                    $allocated = (float)$deptAllocations[$typeKey]->allocated_amount;
                }

                $matchingProjects = $deptProjects->where('budget_source', $typeKey);
                $inProjects = (float)$matchingProjects->sum('budget');
                $used = (float)$matchingProjects->sum('used_budget');
                $remaining = max(0, $allocated - $inProjects);
                $percent = $allocated > 0 ? min(100, round(($inProjects / $allocated) * 100)) : 0;

                $typeBreakdown[$typeKey] = [
                    'name' => $typeKey,
                    'icon' => $meta['icon'],
                    'color' => $meta['color'],
                    'allocated' => $allocated,
                    'in_projects' => $inProjects,
                    'used' => $used,
                    'remaining' => $remaining,
                    'percent' => $percent,
                    'project_count' => $matchingProjects->count(),
                ];
            }

            $deptTotalInProjects = (float)$deptProjects->sum('budget');
            $deptTotalUsed = (float)$deptProjects->sum('used_budget');
            $deptRemaining = max(0, $deptTotalAllocated - $deptTotalInProjects);
            $deptPercent = $deptTotalAllocated > 0 ? min(100, round(($deptTotalInProjects / $deptTotalAllocated) * 100)) : 0;

            $overallTotalAllocated += $deptTotalAllocated;
            $overallTotalInProjects += $deptTotalInProjects;
            $overallTotalUsed += $deptTotalUsed;

            $stats[$dept] = [
                'department' => $dept,
                'fiscal_year' => $fiscalYear,
                'total_allocated' => $deptTotalAllocated,
                'total_in_projects' => $deptTotalInProjects,
                'total_used' => $deptTotalUsed,
                'remaining' => $deptRemaining,
                'percent' => $deptPercent,
                'types' => $typeBreakdown,
                'project_count' => $deptProjects->count(),
            ];
        }

        $overallRemaining = max(0, $overallTotalAllocated - $overallTotalInProjects);
        $overallPercent = $overallTotalAllocated > 0 ? min(100, round(($overallTotalInProjects / $overallTotalAllocated) * 100)) : 0;

        return [
            'fiscal_year' => $fiscalYear,
            'departments' => $stats,
            'summary' => [
                'total_allocated' => $overallTotalAllocated,
                'total_in_projects' => $overallTotalInProjects,
                'total_used' => $overallTotalUsed,
                'remaining' => $overallRemaining,
                'percent' => $overallPercent,
            ]
        ];
    }
}

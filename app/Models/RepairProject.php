<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairProject extends Model
{
    use HasFactory;

    protected $table = 'repair_projects';

    protected $fillable = [
        'project_code',
        'name',
        'department',             // intercepted by mutator -> department_id / department_group_id (M10 compat)
        'department_id',          // FK → departments (M10)
        'department_group_id',    // FK → department_groups (M10)
        'responsible_person',     // intercepted by mutator -> responsible_person_id (M10 compat)
        'responsible_person_id',  // FK → users (M10)
        'budget',
        'used_budget',
        'fiscal_year',
        'budget_source',
        'description',
        'status',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function departmentGroup()
    {
        return $this->belongsTo(DepartmentGroup::class, 'department_group_id', 'id');
    }

    public function responsiblePerson()
    {
        return $this->belongsTo(User::class, 'responsible_person_id', 'user_id');
    }

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class, 'repair_project_id');
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
            $this->attributes['department_id'] = null;
            $this->attributes['department_group_id'] = null;
            return;
        }

        // Check if matching department_groups
        $group = DepartmentGroup::where('group_name', $value)
            ->orWhere('group_code', $value)
            ->first();

        if (!$group) {
            if (str_contains($value, 'ทรัพยากร')) $group = DepartmentGroup::find(1);
            elseif (str_contains($value, 'แผน') || str_contains($value, 'นโยบาย')) $group = DepartmentGroup::find(2);
            elseif (str_contains($value, 'นักเรียน') || str_contains($value, 'นักศึกษา')) $group = DepartmentGroup::find(3);
            elseif (str_contains($value, 'วิชาการ')) $group = DepartmentGroup::find(4);
        }

        if ($group) {
            $this->attributes['department_group_id'] = $group->id;
            return;
        }

        // Check if matching departments
        if (Department::where('department_id', $value)->exists()) {
            $this->attributes['department_id'] = $value;
            return;
        }

        $dept = Department::where('department_name', $value)->first();
        if ($dept) {
            $this->attributes['department_id'] = $dept->department_id;
        }
    }

    public function getResponsiblePersonAttribute()
    {
        $raw = $this->attributes['responsible_person'] ?? null;
        if (!empty($raw)) {
            $user = User::where('user_id', $raw)->first();
            if ($user) {
                $fullName = trim(($user->prefix ? $user->prefix . ' ' : '') . $user->first_name . ' ' . ($user->last_name ?? ''));
                return $fullName ?: $user->name;
            }
            return $raw;
        }

        $user = $this->getRelationValue('responsiblePerson');
        if ($user) {
            $fullName = trim(($user->prefix ? $user->prefix . ' ' : '') . $user->first_name . ' ' . ($user->last_name ?? ''));
            return $fullName ?: $user->name;
        }

        return null;
    }

    public function setResponsiblePersonAttribute($value)
    {
        $this->attributes['responsible_person'] = $value;
    }
}
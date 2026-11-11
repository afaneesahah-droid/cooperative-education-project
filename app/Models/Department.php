<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';
    protected $primaryKey = 'department_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'department_id',
        'department_name',
        'group',               // intercepted by mutator -> department_group_id (M01 compat)
        'department_group_id', // FK → department_groups (M01)
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function departmentGroup()
    {
        return $this->belongsTo(DepartmentGroup::class, 'department_group_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'department_id', 'department_id');
    }

    public function buildings()
    {
        return $this->hasMany(Building::class, 'department_id', 'department_id');
    }

    // ─── Accessors & Mutators (Backward Compatibility) ──────────────────────

    public function getGroupAttribute()
    {
        $raw = $this->attributes['group'] ?? null;
        if (!empty($raw)) {
            return $raw;
        }
        return optional($this->departmentGroup)->group_code ?? optional($this->departmentGroup)->group_name;
    }

    public function setGroupAttribute($value)
    {
        $this->attributes['group'] = $value;
        if (is_numeric($value)) {
            $this->attributes['department_group_id'] = $value;
        } elseif (is_string($value)) {
            $group = DepartmentGroup::where('group_code', $value)
                ->orWhere('group_name', $value)
                ->first();
            if ($group) {
                $this->attributes['department_group_id'] = $group->id;
            }
        }
    }
}

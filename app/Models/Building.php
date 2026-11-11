<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $primaryKey = 'building_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'building_id',
        'building_name',
        'department_name', // intercepted by mutator -> department_id (M03 compat)
        'department_id',
        'floors',
        'description',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'building_id', 'building_id');
    }

    // ─── Backward Compatibility ─────────────────────────────────────────────

    public function getDepartmentNameAttribute()
    {
        return optional($this->department)->department_name;
    }

    public function setDepartmentNameAttribute($value)
    {
        if (is_numeric($value)) {
            $this->attributes['department_id'] = $value;
        } elseif (is_string($value)) {
            $dept = Department::where('department_name', $value)->first();
            if ($dept) {
                $this->attributes['department_id'] = $dept->department_id;
            }
        }
    }
}

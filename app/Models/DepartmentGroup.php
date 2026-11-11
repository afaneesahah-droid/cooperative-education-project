<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DepartmentGroup extends Model
{
    protected $table = 'department_groups';

    protected $fillable = [
        'group_code',
        'group_name',
        'description',
    ];

    public function getTable()
    {
        if (Schema::hasTable('department_groups')) {
            return 'department_groups';
        }
        return 'departments_group';
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'department_group_id', 'id');
    }

    public function budgets()
    {
        return $this->hasMany(DepartmentBudget::class, 'department_group_id', 'id');
    }

    public function repairProjects()
    {
        return $this->hasMany(RepairProject::class, 'department_group_id', 'id');
    }
}

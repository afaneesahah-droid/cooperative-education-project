<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';
    protected $primaryKey = 'assignment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'assignment_id',
        'request_id',
        'user_id',
        'assign_date',
        'task_description',
        'task_priority',
    ];

    protected function casts(): array
    {
        return [
            'assign_date' => 'date',
        ];
    }

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class, 'request_id', 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

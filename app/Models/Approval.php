<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'approvals';

    protected $fillable = [
        'request_id',
        'approver_id',
        'approval_status',
        'approval_date',
        'approval_note',
    ];

    protected function casts(): array
    {
        return [
            'approval_date' => 'date',
        ];
    }

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class, 'request_id', 'request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id', 'user_id');
    }
}

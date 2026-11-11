<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairDetail extends Model
{
    use HasFactory;

    protected $table = 'repair_details';
    protected $primaryKey = 'repair_detail_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'repair_detail_id',
        'request_id',
        'description',
        'request_description',
        'preferred_date',
        'contact_number',
        'note',
    ];

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class, 'request_id', 'request_id');
    }
}

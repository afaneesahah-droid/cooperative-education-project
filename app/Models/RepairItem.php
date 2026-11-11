<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairItem extends Model
{
    use HasFactory;

    protected $table = 'repair_items';
    protected $primaryKey = 'item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'item_id',
        'request_id',
        'equipment_id',
        'equipment_name',
        'equipment_number',
        'device_type',
        'building_id',
        'room_number',
        'location',
    ];

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class, 'request_id', 'request_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Device::class, 'equipment_id', 'equipment_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }
}

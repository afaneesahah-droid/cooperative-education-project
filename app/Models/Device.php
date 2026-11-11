<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'repair_equipments';
    protected $primaryKey = 'equipment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $with = ['building', 'room'];

    protected $fillable = [
        'equipment_id',
        'equipment_name',        // intercepted by mutator -> name (M05 compat)
        'image',
        'device_code',
        'name',
        'serial',
        'type',                  // intercepted by mutator -> category (M05 compat)
        'spec',
        'status',
        'brand',
        'model',
        'category',
        'asset_kind_id',         // FK → asset_kinds (M05)
        'building_id',
        'floor',
        'room_id',
        'responsible_person',    // intercepted by mutator -> responsible_person_id (M05 compat)
        'responsible_person_id', // FK → users (M05)
        'supplier',
        'price',
        'warranty',
        'purchase_date',
        'version',
        'start_date',
        'installation_date',
        'license_type',
        'expiration_date'
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_code');
    }

    public function assetKind()
    {
        return $this->belongsTo(AssetKind::class, 'asset_kind_id', 'id');
    }

    public function deviceType()
    {
        return $this->belongsTo(AssetKind::class, 'asset_kind_id', 'id');
    }

    public function responsiblePerson()
    {
        return $this->belongsTo(User::class, 'responsible_person_id', 'user_id');
    }

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class, 'equipment_id', 'equipment_id');
    }

    // ─── Accessors & Mutators (Backward Compatibility) ──────────────────────

    public function getIdAttribute()
    {
        return $this->equipment_id;
    }

    /**
     * equipment_name compatibility accessor / mutator (M05: merged into name)
     */
    

    

    /**
     * type compatibility accessor / mutator (M05: merged into category)
     */
    

    

    /**
     * responsible_person compatibility accessor / mutator
     */
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

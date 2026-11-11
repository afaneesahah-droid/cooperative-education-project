<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';
    protected $primaryKey = 'position_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'position_id',
        'position_name',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class, 'position_id', 'position_id');
    }

    // ─── Backward Compatibility ─────────────────────────────────────────────

    public function getRoleAttribute()
    {
        return $this->attributes['role'] ?? null;
    }

    public function setRoleAttribute($value)
    {
        // No-op for backward compatibility after M02 dropped role column
    }
}

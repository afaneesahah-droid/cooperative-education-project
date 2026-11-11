<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'priority'
    ];
}

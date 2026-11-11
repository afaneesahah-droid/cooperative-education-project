<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceType extends Model
{
    protected $fillable = [
        'category',
        'name',
        'prefix',
        'asset_code',
        'brand',
        'model',
        'details',
        'purchase_date'
    ];
}

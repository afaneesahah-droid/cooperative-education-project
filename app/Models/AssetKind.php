<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetKind extends Model
{
    protected $table = 'asset_kinds';

    protected $fillable = [
        'category_code',
        'name',
        'description',
    ];
}

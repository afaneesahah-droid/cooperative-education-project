<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetSource extends Model
{
    use HasFactory;

    protected $table = 'budget_sources';

    protected $fillable = [
        'source_key',
        'source_name',
        'total_allocated',
        'fiscal_year',
    ];

    /**
     * Default budget sources with initial ceilings
     */
    public static function getDefaultSources()
    {
        return [
            'งบประมาณกลาง' => [
                'name' => 'งบประมาณกลาง',
                'allocated' => 2000000.00,
            ],
            'งบพัฒนาสถานศึกษา' => [
                'name' => 'งบพัฒนาสถานศึกษา',
                'allocated' => 1000000.00,
            ],
            'งบเงินรายได้' => [
                'name' => 'งบเงินรายได้',
                'allocated' => 500000.00,
            ],
            'งบอุดหนุน' => [
                'name' => 'งบอุดหนุน',
                'allocated' => 500000.00,
            ],
        ];
    }
}

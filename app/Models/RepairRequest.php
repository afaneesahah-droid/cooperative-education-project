<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairRequest extends Model
{
    use HasFactory;

    protected $primaryKey = 'request_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $with = ['device', 'user', 'latestMaintenance', 'item', 'latestAssignment'];

    protected $fillable = [
        'request_id',
        'request_code',
        'equipment_id',
        'user_id',
        'type',
        'description',
        'image',
        'budget',
        'priority',
        'status',
        'note',
        'budget_approved',
        'rating',
        'eval_comment',
        'repair_project_id',
        'repair_details',
        'repair_evaluation',
        'repair_result',
        'unrepairable_reason',
        'technician_recommendation',
    ];

    /**
     * Auto-sync assigned_to via assignments table on save (backward compatibility)
     */
    protected static function booted()
    {
        static::saved(function ($request) {
            if (!empty($request->assigned_to_temp)) {
                $exists = Assignment::where('request_id', $request->request_id)
                    ->where('user_id', $request->assigned_to_temp)
                    ->exists();

                if (!$exists) {
                    $lastId = Assignment::where('assignment_id', 'like', 'A%')->max('assignment_id');
                    $num = $lastId ? ((int)substr($lastId, 1) + 1) : 1;
                    $nextId = 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
                    while (Assignment::where('assignment_id', $nextId)->exists()) {
                        $num++;
                        $nextId = 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
                    }

                    Assignment::create([
                        'assignment_id'    => $nextId,
                        'request_id'       => $request->request_id,
                        'user_id'          => $request->assigned_to_temp,
                        'assign_date'      => now(),
                        'task_priority'    => $request->priority ?? 'normal',
                    ]);
                }
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'equipment_id', 'equipment_id');
    }

    public function repairProject()
    {
        return $this->belongsTo(RepairProject::class, 'repair_project_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'request_id', 'request_id');
    }

    public function latestAssignment()
    {
        return $this->hasOne(Assignment::class, 'request_id', 'request_id')->latestOfMany('assignment_id');
    }

    public function assignee()
    {
        return $this->hasOneThrough(
            User::class,
            Assignment::class,
            'request_id', // Foreign key on assignments table
            'user_id',    // Foreign key on users table
            'request_id', // Local key on repair_requests table
            'user_id'     // Local key on assignments table
        );
    }

    public function item()
    {
        return $this->hasOne(RepairItem::class, 'request_id', 'request_id');
    }

    public function items()
    {
        return $this->hasMany(RepairItem::class, 'request_id', 'request_id');
    }

    public function detail()
    {
        return $this->hasOne(RepairDetail::class, 'request_id', 'request_id');
    }

    public function details()
    {
        return $this->hasMany(RepairDetail::class, 'request_id', 'request_id');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'request_id', 'request_id');
    }

    public function latestMaintenance()
    {
        return $this->hasOne(Maintenance::class, 'request_id', 'request_id')->latestOfMany('maintenance_id');
    }

    public function approvals()
    {
        return null;
    }

    // ─── Accessors & Mutators (Backward Compatibility) ──────────────────────

    public function getIdAttribute()
    {
        return $this->request_id;
    }

    /**
     * Backward compat for assigned_to
     */
    public function getAssignedToAttribute()
    {
        return optional($this->latestAssignment)->user_id ?? ($this->attributes['assigned_to'] ?? null);
    }

    public function setAssignedToAttribute($value)
    {
        $this->assigned_to_temp = $value;
    }

    /**
     * Backward compat for location / building / room_number / equipment_number
     */
    public function getLocationAttribute()
    {
        if ($this->item && !empty($this->item->location)) {
            return $this->item->location;
        }
        return optional($this->device)->room
            ? optional($this->device->room)->room_name
            : (optional($this->device)->building ? optional($this->device->building)->building_name : '-');
    }

    public function getBuildingAttribute()
    {
        if ($this->item && !empty($this->item->building_id)) {
            return $this->item->building_id;
        }
        return optional($this->device)->building ? optional($this->device->building)->building_name : '';
    }

    public function getRoomNumberAttribute()
    {
        if ($this->item && !empty($this->item->room_number)) {
            return $this->item->room_number;
        }
        return optional($this->device)->room_id ?? '';
    }

    public function getEquipmentNumberAttribute()
    {
        if ($this->item && !empty($this->item->equipment_number)) {
            return $this->item->equipment_number;
        }
        return optional($this->device)->device_code ? substr($this->device->device_code, -2) : '';
    }

    /**
     * ชนิดทรัพย์สิน อิงตามตาราง asset_kinds ของระบบจริง
     * ลำดับ: ชนิดของครุภัณฑ์ที่ผูกไว้ -> ชนิดที่บันทึกตอนแจ้งซ่อม (repair_items / repair_requests.type)
     */
    public function getAssetKindAttribute()
    {
        static $kindNames = null;
        if ($kindNames === null) {
            try {
                $kindNames = AssetKind::pluck('name')->map(fn($n) => trim($n))->filter()->values()->all();
            } catch (\Throwable $e) {
                $kindNames = [];
            }
        }

        $candidates = array_values(array_filter(array_map(fn($v) => trim((string) $v), [
            optional($this->device)->assetKind?->name,
            optional($this->device)->deviceType?->name,
            optional($this->device)->type,
            optional($this->device)->name,
            optional($this->item)->device_type,
            optional($this->item)->equipment_name,
            $this->attributes['type'] ?? null,
        ])));

        foreach ($candidates as $c) {
            if ($c === 'OS ระบบปฏิบัติการ') {
                $c = 'OS ระบบปฎิบัติการ';
            }
            foreach ($kindNames as $kn) {
                if (mb_strtolower($c) === mb_strtolower($kn)) {
                    return $kn;
                }
            }
        }

        return '-';
    }

    /**
     * ประเภททรัพย์สิน (Category: ฮาร์ดแวร์, ซอฟต์แวร์, ระบบเครือข่าย, IOT)
     */
    public function getAssetCategoryAttribute()
    {
        // 1. ตรวจสอบจาก repair_equipments (category)
        if ($this->device && !empty($this->device->category)) {
            $cat = trim($this->device->category);
            if (in_array($cat, ['ฮาร์ดแวร์', 'ซอฟต์แวร์', 'ระบบเครือข่าย', 'IOT'])) {
                return $cat;
            }
        }

        // 2. แมปจากชนิดทรัพย์สิน (asset_kind) ผ่านตาราง asset_kinds
        static $kindToCategory = null;
        if ($kindToCategory === null) {
            try {
                $kindToCategory = AssetKind::pluck('category_code', 'name')->map(fn($v) => trim($v))->toArray();
            } catch (\Throwable $e) {
                $kindToCategory = [];
            }
        }

        $kind = $this->asset_kind;
        if ($kind && $kind !== '-' && isset($kindToCategory[$kind]) && !empty($kindToCategory[$kind])) {
            return $kindToCategory[$kind];
        }

        // 3. ตรวจสอบจาก attributes type หรือ repair_items device_type
        $candidates = [
            $this->attributes['type'] ?? '',
            optional($this->item)->device_type ?? '',
        ];
        foreach ($candidates as $c) {
            $c = trim((string)$c);
            if (in_array($c, ['ฮาร์ดแวร์', 'hardware', 'คอมพิวเตอร์'])) return 'ฮาร์ดแวร์';
            if (in_array($c, ['ซอฟต์แวร์', 'software'])) return 'ซอฟต์แวร์';
            if (in_array($c, ['ระบบเครือข่าย', 'network'])) return 'ระบบเครือข่าย';
            if (in_array($c, ['IOT', 'iot'])) return 'IOT';
            if (isset($kindToCategory[$c]) && !empty($kindToCategory[$c])) return $kindToCategory[$c];
        }

        // 4. วิเคราะห์จากข้อความอาการ/ชื่ออุปกรณ์
        $text = mb_strtolower(($this->description ?? '') . ' ' . (optional($this->item)->equipment_name ?? ''), 'UTF-8');
        if (str_contains($text, 'network') || str_contains($text, 'lan') || str_contains($text, 'wifi') || str_contains($text, 'เครือข่าย') || str_contains($text, 'สัญญาณ') || str_contains($text, 'สายแลน')) {
            return 'ระบบเครือข่าย';
        }
        if (str_contains($text, 'software') || str_contains($text, 'windows') || str_contains($text, 'office') || str_contains($text, 'โปรแกรม') || str_contains($text, 'ซอฟต์แวร์') || str_contains($text, 'ระบบปฏิบัติการ') || str_contains($text, 'ระบบปฎิบัติการ')) {
            return 'ซอฟต์แวร์';
        }

        return 'ฮาร์ดแวร์';
    }

    public function getRequestDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d') : null;
    }

    public function getRequestDescriptionAttribute()
    {
        return $this->description;
    }

    public function setRequestDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value;
    }

    /**
     * Backward compat for maintenances columns
     */
    public function getStartDateAttribute()
    {
        return optional($this->latestMaintenance)->start_date ?? '';
    }

    public function getAcceptedAtAttribute()
    {
        return optional($this->latestMaintenance)->accepted_at;
    }

    public function getInspectionResultAttribute()
    {
        return optional($this->latestMaintenance)->inspection_result ?? '';
    }

    public function getRepairEvaluationAttribute()
    {
        return !empty($this->attributes['repair_evaluation']) ? $this->attributes['repair_evaluation'] : (optional($this->latestMaintenance)->repair_evaluation ?? '');
    }

    public function getRepairDetailsAttribute()
    {
        return !empty($this->attributes['repair_details']) ? $this->attributes['repair_details'] : (optional($this->latestMaintenance)->repair_details ?? '');
    }

    public function getRepairResultAttribute()
    {
        return !empty($this->attributes['repair_result']) ? $this->attributes['repair_result'] : (optional($this->latestMaintenance)->repair_result ?? '');
    }

    public function getUnrepairableReasonAttribute()
    {
        return !empty($this->attributes['unrepairable_reason']) ? $this->attributes['unrepairable_reason'] : (optional($this->latestMaintenance)->unrepairable_reason ?? '');
    }

    public function getTechnicianRecommendationAttribute()
    {
        return '';
    }

    /**
     * Checklist booleans (retrieved from latest maintenance)
     */
    public function getCheckedPowerAttribute()
    {
        return (bool) (optional($this->latestMaintenance)->checked_power ?? false);
    }

    public function getCheckedCleaningAttribute()
    {
        return (bool) (optional($this->latestMaintenance)->checked_cleaning ?? false);
    }

    public function getCheckedPingAttribute()
    {
        return (bool) (optional($this->latestMaintenance)->checked_ping ?? false);
    }

    public function getRepairHwAttribute()
    {
        return (bool) (optional($this->latestMaintenance)->repair_hw ?? false);
    }

    public function getRepairSwAttribute()
    {
        return (bool) (optional($this->latestMaintenance)->repair_sw ?? false);
    }

    public function getRepairNetAttribute()
    {
        return (bool) (optional($this->latestMaintenance)->repair_net ?? false);
    }

    /**
     * Accessor for clean symptom.
     */
    public function getCleanSymptomAttribute()
    {
        if (empty($this->description)) return '-';

        // 1. Remove parenthesized metadata (e.g. วันที่สะดวกให้เข้าซ่อม, เบอร์โทรติดต่อ, รหัสอุปกรณ์)
        $clean = preg_replace('/\([^)]*\)/u', '', $this->description);

        // 2. Cut off at extra sections/labels
        $cutPos = mb_strlen($clean);
        foreach ([
            'ส่วนเวลาที่เสนอราคาค่าซ่อม:',
            'วันที่เสนอราคาค่าซ่อม:',
            'วันที่สะดวกให้เข้าซ่อม:',
            'ช่วงเวลาที่สะดวกให้เข้าซ่อม:',
            'ช่วงเวลาที่สะดวก:',
            'เวลาที่สะดวก:',
            'เบอร์โทรติดต่อผู้แจ้ง:',
            'เบอร์โทรติดต่อ:',
            'วันที่แจ้งซ่อม:',
            'เวลาแจ้งซ่อม:'
        ] as $label) {
            $pos = mb_strpos($clean, $label);
            if ($pos !== false && $pos < $cutPos) {
                $cutPos = $pos;
            }
        }
        $clean = trim(mb_substr($clean, 0, $cutPos));

        // 3. Remove any remaining convenient time/date patterns anywhere in the string
        $clean = preg_replace('/(ช่วงเวลาที่สะดวก(ให้เข้าซ่อม)?|วันที่สะดวก(ให้เข้าซ่อม)?|เวลาที่สะดวก)[^\r\n]*/u', '', $clean);

        // 4. Remove repeated prefixes like "อาการที่พบ:", "อาการเสีย:", "อาการชำรุด:"
        $clean = preg_replace('/^(\s*(อาการที่พบ|อาการเสีย|อาการชำรุด)\s*[:：]\s*)+/u', '', $clean);

        // 5. Normalize whitespace and newlines
        $clean = preg_replace('/[\r\n\t]+/', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        if ($clean === '' || $clean === '-') {
            return '-';
        }

        return $clean;
    }

    /**
     * Accessor for display device code.
     */
    public function getDisplayDeviceCodeAttribute()
    {
        if ($this->device && $this->device->device_code) {
            return $this->device->device_code;
        }
        if (preg_match('/\(อุปกรณ์ที่พิมพ์เอง:\s*([^)]+)\)/u', $this->description, $matches)) {
            return trim($matches[1]);
        }
        return '-';
    }

    public function getIssueDescriptionAttribute()
    {
        return $this->description;
    }

    public function getProblemDescriptionAttribute()
    {
        return $this->description;
    }

    public function getRecommendationsAttribute()
    {
        return $this->technician_recommendation;
    }

    public function getProjectNameAttribute()
    {
        return optional($this->repairProject)->name ?? '-';
    }

    public function getBudgetSourceAttribute()
    {
        return optional($this->repairProject)->budget_source ?? '-';
    }

    public function getTitleAttribute()
    {
        return $this->type ?? $this->request_description ?? '-';
    }

    /**
     * Convert number to Thai Baht text representation
     */
    public static function thaiBahtText($amount)
    {
        if (is_null($amount) || (float)$amount == 0) {
            return 'ศูนย์บาทถ้วน';
        }
        $amount = number_format((float)$amount, 2, '.', '');
        list($baht, $satang) = explode('.', $amount);
        
        $thaiNums = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $thaiUnits = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];
        
        $convert = function ($str) use ($thaiNums, $thaiUnits) {
            $len = strlen($str);
            $res = '';
            for ($i = 0; $i < $len; $i++) {
                $digit = (int)$str[$i];
                $pos = $len - $i - 1;
                if ($digit !== 0) {
                    if ($pos === 0 && $digit === 1 && $len > 1 && (int)$str[$i - 1] !== 0) {
                        $res .= 'เอ็ด';
                    } elseif ($pos === 1 && $digit === 1) {
                        $res .= '';
                    } elseif ($pos === 1 && $digit === 2) {
                        $res .= 'ยี่';
                    } else {
                        $res .= $thaiNums[$digit];
                    }
                    $res .= $thaiUnits[$pos];
                }
            }
            return $res;
        };
        
        $result = '';
        if ((int)$baht > 0) {
            if (strlen($baht) > 6) {
                $million = substr($baht, 0, -6);
                $rest = substr($baht, -6);
                $result .= $convert($million) . 'ล้าน' . $convert($rest) . 'บาท';
            } else {
                $result .= $convert($baht) . 'บาท';
            }
        }
        
        if ((int)$satang === 0) {
            $result .= 'ถ้วน';
        } else {
            $result .= $convert($satang) . 'สตางค์';
        }
        return $result;
    }
}

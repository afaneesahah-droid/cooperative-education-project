<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'prefix',
        'first_name',
        'last_name',
        'first_name_en',
        'last_name_en',
        'citizen_id',
        'email',
        'phone_number',
        'gender',
        'password',
        'role',
        'status',
        'department',
        'department_id',
        'profile_photo',
        'plain_password',
    ];

    /**
     * Auto-sync name on save.
     */
    protected static function booted()
    {
        static::saving(function ($user) {
            if (empty($user->attributes['name'])) {
                $computed = trim(($user->prefix ? $user->prefix . ' ' : '') . ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                if (!empty($computed)) {
                    $user->attributes['name'] = $computed;
                }
            }
        });
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Disable remember_token column access since column has been dropped from DB.
     */
    public function getRememberTokenName()
    {
        return '';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function positionRelation()
    {
        return null;
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class, 'user_id', 'user_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'user_id', 'user_id');
    }

    // ─── Status helpers ─────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // ─── Accessors & Mutators (Backward Compatibility) ──────────────────────

    /**
     * Accessor for id (compat).
     */
    public function getIdAttribute()
    {
        return $this->user_id;
    }

    /**
     * Accessor for username (compat).
     */
    public function getUsernameAttribute()
    {
        return $this->user_id;
    }

    /**
     * Computed full name from prefix + first_name + last_name, with fallback to raw name.
     */
    public function getNameAttribute()
    {
        $computed = trim(($this->prefix ? $this->prefix . ' ' : '') . ($this->attributes['first_name'] ?? '') . ' ' . ($this->attributes['last_name'] ?? ''));
        if (!empty($computed)) {
            return $computed;
        }
        return $this->attributes['name'] ?? '';
    }

    public function setNameAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['name'] = $value;
        } elseif (empty($this->attributes['name'])) {
            $computed = trim(($this->prefix ? $this->prefix . ' ' : '') . ($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
            if (!empty($computed)) {
                $this->attributes['name'] = $computed;
            }
        }
    }

    public function getFirstNameAttribute()
    {
        if (!empty($this->attributes['first_name'])) {
            return $this->attributes['first_name'];
        }
        if (!empty($this->attributes['name'])) {
            $name = trim($this->attributes['name']);
            $prefix = $this->attributes['prefix'] ?? null;
            if ($prefix && str_starts_with($name, $prefix)) {
                $name = trim(substr($name, strlen($prefix)));
            }
            $parts = preg_split('/\s+/', $name);
            return $parts[0] ?? '';
        }
        return '';
    }

    public function getLastNameAttribute()
    {
        if (!empty($this->attributes['last_name'])) {
            return $this->attributes['last_name'];
        }
        if (!empty($this->attributes['name'])) {
            $name = trim($this->attributes['name']);
            $prefix = $this->attributes['prefix'] ?? null;
            if ($prefix && str_starts_with($name, $prefix)) {
                $name = trim(substr($name, strlen($prefix)));
            }
            $parts = preg_split('/\s+/', $name);
            if (count($parts) > 1) {
                return implode(' ', array_slice($parts, 1));
            }
        }
        return '';
    }

    /**
     * Accessor for card_ID (compat -> citizen_id).
     */
    public function getCardIdAttribute()
    {
        return $this->citizen_id;
    }

    public function setCardIdAttribute($value)
    {
        $this->attributes['citizen_id'] = $value;
    }

    /**
     * Accessor for phone (compat -> phone_number).
     */
    public function getPhoneAttribute()
    {
        return $this->attributes['phone_number'] ?? null;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone_number'] = substr(str_replace('-', '', $value ?? ''), 0, 10);
    }

    /**
     * Accessor for phone_number.
     */
    public function getPhoneNumberAttribute()
    {
        return $this->attributes['phone_number'] ?? null;
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = substr(str_replace('-', '', $value ?? ''), 0, 10);
    }

    /**
     * Accessor for department name string (backward compat).
     * อ่านจาก relationship หรือ attribute department
     */
    public function getDepartmentNameAttribute()
    {
        return optional($this->departmentRelation)->department_name ?? ($this->attributes['department'] ?? null);
    }

    /**
     * Accessor for position (dynamically derived from role).
     */
    public function getPositionAttribute()
    {
        $role = $this->role ?? '';
        if (str_contains($role, 'admin')) return 'ผู้ดูแลระบบ';
        if (str_contains($role, 'executive')) return 'ผู้บริหาร';
        if (str_contains($role, 'head')) return 'หัวหน้างาน';
        if (str_contains($role, 'technician')) return 'เจ้าหน้าที่ซ่อมบำรุง';
        if (str_contains($role, 'teacher')) return 'ครู';
        return 'บุคลากร';
    }

    public function getPositionNameAttribute()
    {
        return $this->position;
    }

    public function setPositionAttribute($value)
    {
        // Column dropped, no-op to prevent SQL errors
    }

    public function getPositionIdAttribute()
    {
        return null;
    }

    public function setPositionIdAttribute($value)
    {
        // Column dropped, no-op
    }

    public function getPlainPasswordAttribute()
    {
        return null;
    }

    public function setPlainPasswordAttribute($value)
    {
        // Column dropped, no-op
    }

    public function getSuspendReasonAttribute()
    {
        return null;
    }

    public function setSuspendReasonAttribute($value)
    {
        // Column dropped, no-op
    }

    public function getDepartmentAttribute()
    {
        return $this->department_name;
    }

    public function setDepartmentAttribute($value)
    {
        if (is_numeric($value)) {
            $this->attributes['department_id'] = $value;
        } elseif (is_string($value)) {
            $this->attributes['department'] = $value;
            $dept = Department::where('department_name', $value)->first();
            if ($dept) {
                $this->attributes['department_id'] = $dept->department_id;
            }
        }
    }

    public function getSectionNameAttribute()
    {
        $resourceList = ["งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"];
        $planningList = ["งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา"];
        $studentList = ["งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"];
        $academicList = ["งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า"];

        $dept     = $this->department_name;
        $position = $this->position_name;

        if (in_array($position, $resourceList) || in_array($position, $planningList) || in_array($position, $studentList) || in_array($position, $academicList)) {
            return $position;
        }

        return $dept ?: $position;
    }

    public function getDepartmentGroupNameAttribute()
    {
        $resourceList = ["งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"];
        $planningList = ["งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา"];
        $studentList = ["งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"];
        $academicList = ["งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า"];

        $section = $this->section_name;

        if (in_array($section, $resourceList)) return 'ฝ่ายบริหารทรัพยากร';
        if (in_array($section, $planningList)) return 'ฝ่ายนโยบายและแผน';
        if (in_array($section, $studentList)) return 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า';
        if (in_array($section, $academicList)) return 'ฝ่ายวิชาการ';

        return '';
    }
}

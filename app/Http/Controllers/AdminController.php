<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * ตั้งค่าหน้า แผงควบคุมผู้ดูแลระบบ
     */
    public function index(Request $request)
    {
        $users        = User::orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")->orderBy('created_at', 'desc')->get();
        $pendingCount = User::where('status', 'pending')->count();
        $dbPositions  = \Illuminate\Support\Facades\Schema::hasTable('positions') ? \App\Models\Position::all() : collect([]);
        $dbDepartments = \App\Models\Department::all();
        $dbBuildings  = \App\Models\Building::with('rooms')->orderBy('building_id')->get();
        $dbDeviceTypes = \App\Models\DeviceType::orderBy('id')->get();
        $dbRepairTypes = \Illuminate\Support\Facades\Schema::hasTable('repair_types') ? \App\Models\RepairType::orderBy('id')->get() : collect([]);
        $dbPersonnelTypes = \App\Models\PersonnelType::orderBy('id')->get();
        $dbAssetKinds = \App\Models\AssetKind::orderBy('id')->get();
        $dbDepartmentGroups = \App\Models\DepartmentGroup::orderBy('id')->get();
        $dbBudgetSources = \Illuminate\Support\Facades\Schema::hasTable('budget_sources')
            ? \App\Models\BudgetSource::all()->keyBy('source_key')
            : collect([]);
        $selectedFiscalYear = $request->get('fiscal_year', (string)(date('Y') + 543));
        $availableFiscalYears = \Illuminate\Support\Facades\Schema::hasTable('department_budgets')
            ? \App\Models\DepartmentBudget::distinct()->pluck('fiscal_year')
                ->merge(\App\Models\RepairProject::distinct()->pluck('fiscal_year'))
                ->filter()->unique()->sortDesc()->values()
            : \App\Models\RepairProject::distinct()->pluck('fiscal_year')->filter()->unique()->sortDesc()->values();

        $currentYearStr = (string)(date('Y') + 543);
        if (!$availableFiscalYears->contains($currentYearStr)) {
            $availableFiscalYears->prepend($currentYearStr);
        }
        $availableFiscalYears = $availableFiscalYears->unique()->values();

        // โครงการซ่อมบำรุงกรองตามปีงบประมาณที่เลือก (งบประมาณตั้งปีต่อปีและใช้ได้เฉพาะของปีนั้นๆ)
        $projectsQuery = \App\Models\RepairProject::with(['repairRequests', 'repairRequests.user'])->orderBy('created_at', 'desc');
        if ($selectedFiscalYear !== 'all') {
            $projectsQuery->where('fiscal_year', $selectedFiscalYear);
        }
        $dbRepairProjects = $projectsQuery->get();

        $statsYear = ($selectedFiscalYear === 'all') ? $currentYearStr : $selectedFiscalYear;
        $departmentBudgetStats = \Illuminate\Support\Facades\Schema::hasTable('department_budgets')
            ? \App\Models\DepartmentBudget::getStatsForYear($statsYear)
            : [
                'fiscal_year' => $statsYear,
                'departments' => [],
                'summary' => ['total_allocated' => 0, 'total_in_projects' => 0, 'total_used' => 0, 'remaining' => 0, 'percent' => 0]
            ];

        $activeTab = $request->get('tab', 'tab-users');
        if (session('active_tab') === 'tab-profile' || (isset($errors) && $errors->any())) {
            $activeTab = 'tab-profile';
        }

        $logoUrl = file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('public/images/logo.png');
        $authPhoto = Auth::user() ? Auth::user()->profile_photo : null;
        $profilePhotoUrl = null;
        if ($authPhoto) {
            $profilePhotoUrl = asset('uploads/profiles/' . $authPhoto);
        }

        $resourceList = ["งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"];
        $planningList = ["งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา"];
        $studentList = ["งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"];
        $academicList = ["งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า"];

        $formattedUsers = $users->map(function($user) use ($resourceList, $planningList, $studentList, $academicList) {
            $roleMap = [
                'admin' => 'Admin(ผู้ดูแลระบบ)',
                'executive' => 'ผู้บริหาร',
                'head' => 'หัวหน้างาน',
                'technician' => 'ช่างซ่อมบำรุง',
                'teacher' => 'แจ้งซ่อม'
            ];
            
            $roles = array_map('trim', explode(',', $user->role ?? ''));
            $translatedRoles = [];
            foreach ($roles as $r) {
                if (isset($roleMap[$r])) {
                    $translatedRoles[] = $roleMap[$r];
                }
            }
            $roleThai = empty($translatedRoles) ? 'ผู้ใช้ทั่วไป' : implode(', ', $translatedRoles);

            $deptGroup = '';
            $dept = $user->department;
            $position = $user->position;

            if (in_array($position, $resourceList) || in_array($position, $planningList) || in_array($position, $studentList) || in_array($position, $academicList)) {
                $dept = $position;
                if (in_array('admin', $roles)) $position = 'ผู้ดูแลระบบ';
                elseif (in_array('executive', $roles)) $position = 'ผู้บริหาร';
                elseif (in_array('head', $roles)) $position = 'หัวหน้างาน';
                elseif (in_array('technician', $roles)) $position = 'เจ้าหน้าที่ซ่อมบำรุง';
                elseif (in_array('teacher', $roles)) $position = 'ครู';
            }

            if (empty($position) || $position === 'ผู้ใช้ทั่วไป') {
                if (in_array('admin', $roles)) $position = 'ผู้ดูแลระบบ';
                elseif (in_array('executive', $roles)) $position = 'ผู้บริหาร';
                elseif (in_array('head', $roles)) $position = 'หัวหน้างาน';
                elseif (in_array('technician', $roles)) $position = 'เจ้าหน้าที่ซ่อมบำรุง';
                elseif (in_array('teacher', $roles)) $position = 'ครู';
            }
            if (in_array($dept, $resourceList)) $deptGroup = 'resource';
            elseif (in_array($dept, $planningList)) $deptGroup = 'planning';
            elseif (in_array($dept, $studentList)) $deptGroup = 'student';
            elseif (in_array($dept, $academicList)) $deptGroup = 'academic';

            return [
                'id' => $user->user_id,
                'db_id' => $user->user_id,
                'prefix' => $user->prefix ?: '',
                'first_name' => $user->first_name ?: ($user->name ?: ''),
                'last_name' => $user->last_name ?: '',
                'first_name_en' => $user->first_name_en ?: '',
                'last_name_en' => $user->last_name_en ?: '',
                'citizen_id' => $user->citizen_id ?: '',
                'username' => $user->user_id,
                'name' => $user->name ?: trim(($user->prefix ? $user->prefix . ' ' : '') . ($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'email' => $user->email,
                'phone' => $user->phone ?: '',
                'gender' => $user->gender ?: '',
                'position' => $position ?: '',
                'department_group' => $deptGroup ?: '',
                'department' => $dept ?: '',
                'role' => $user->role ?: 'teacher',
                'roleThai' => $roleThai,
                'status' => $user->status,
                'suspend_reason' => $user->suspend_reason ?: '',
                'profile_photo' => $user->profile_photo ?: '',
                'plain_password' => $user->plain_password ?: '',
            ];
        });

        return response()->view('dashboards.admin', compact(
            'dbRepairProjects',
            'users',
            'pendingCount',
            'dbPositions',
            'dbDepartments',
            'dbBuildings',
            'dbDeviceTypes',
            'dbRepairTypes',
            'dbPersonnelTypes',
            'dbAssetKinds',
            'dbDepartmentGroups',
            'dbBudgetSources',
            'activeTab',
            'logoUrl',
            'profilePhotoUrl',
            'formattedUsers',
            'selectedFiscalYear',
            'availableFiscalYears',
            'departmentBudgetStats'
        ))->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }
    /**
     * Ã Â¸Â­Ã Â¸â„¢Ã Â¸Â¸Ã Â¸Â¡Ã Â¸Â±Ã Â¸â€¢Ã Â¸Â´Ã Â¸ÂÃ Â¸Â²Ã Â¸Â£Ã Â¸ÂªÃ Â¸Â¡Ã Â¸Â±Ã Â¸â€žÃ Â¸Â£Ã Â¸ÂªÃ Â¸Â¡Ã Â¸Â²Ã Â¸Å Ã Â¸Â´Ã Â¸Â
     */
    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'approved']);

        return back()->with('success', "Ã Â¸Â­Ã Â¸â„¢Ã Â¸Â¸Ã Â¸Â¡Ã Â¸Â±Ã Â¸â€¢Ã Â¸Â´Ã Â¸Å¡Ã Â¸Â±Ã Â¸ÂÃ Â¸Å Ã Â¸ÂµÃ Â¸â€šÃ Â¸Â­Ã Â¸â€¡ {$user->first_name} {$user->last_name} Ã Â¹â‚¬Ã Â¸Â£Ã Â¸ÂµÃ Â¸Â¢Ã Â¸Å¡Ã Â¸Â£Ã Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§");
    }

    /**
     * Ã Â¸â€ºÃ Â¸ÂÃ Â¸Â´Ã Â¹â‚¬Ã Â¸ÂªÃ Â¸ËœÃ Â¸ÂÃ Â¸Â²Ã Â¸Â£Ã Â¸ÂªÃ Â¸Â¡Ã Â¸Â±Ã Â¸â€žÃ Â¸Â£Ã Â¸ÂªÃ Â¸Â¡Ã Â¸Â²Ã Â¸Å Ã Â¸Â´Ã Â¸Â
     */
    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);

        return back()->with('error', "Ã Â¸â€ºÃ Â¸ÂÃ Â¸Â´Ã Â¹â‚¬Ã Â¸ÂªÃ Â¸ËœÃ Â¸Å¡Ã Â¸Â±Ã Â¸ÂÃ Â¸Å Ã Â¸ÂµÃ Â¸â€šÃ Â¸Â­Ã Â¸â€¡ {$user->first_name} {$user->last_name} Ã Â¹â‚¬Ã Â¸Â£Ã Â¸ÂµÃ Â¸Â¢Ã Â¸Å¡Ã Â¸Â£Ã Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¹ÂÃ Â¸Â¥Ã Â¹â€°Ã Â¸Â§");
    }

    /**
     * Ã Â¹â‚¬Ã Â¸Å¾Ã Â¸Â´Ã Â¹Ë†Ã Â¸Â¡Ã Â¸Å“Ã Â¸Â¹Ã Â¹â€°Ã Â¹Æ’Ã Â¸Å Ã Â¹â€°Ã Â¹Æ’Ã Â¸Â«Ã Â¸Â¡Ã Â¹Ë†
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'prefix' => 'nullable|string|max:30',
            'first_name' => 'required|string|max:30',
            'last_name' => 'required|string|max:30',
            'first_name_en' => 'required|string|max:30',
            'last_name_en' => 'required|string|max:30',
            'username' => 'required|string|max:8|unique:users,user_id',
            'email' => 'required|email|max:50|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'citizen_id' => 'nullable|string|max:13',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'role' => 'required|string|max:50',
            'password' => 'required|string|min:6',
            'gender' => 'nullable|string|max:50',
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'username.unique' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว',
            'username.max' => 'ชื่อผู้ใช้ต้องไม่เกิน 8 ตัวอักษร',
        ]);

        $citizenId = preg_replace('/\D/', '', $request->citizen_id ?? '');
        if (!empty($citizenId)) {
            if (User::whereRaw("REPLACE(REPLACE(citizen_id, '-', ''), ' ', '') = ?", [$citizenId])->exists()) {
                return response()->json([
                    'success' => false,
                    'field'   => 'citizen_id',
                    'message' => 'มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว',
                    'errors'  => ['citizen_id' => ['มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว']]
                ], 422);
            }
        }

        $cleanPhone = substr(preg_replace('/\D/', '', $request->phone ?? ''), 0, 10);
        if (!empty($cleanPhone)) {
            if (User::whereRaw("REPLACE(REPLACE(phone_number, '-', ''), ' ', '') = ?", [$cleanPhone])->exists()) {
                return response()->json([
                    'success' => false,
                    'field'   => 'phone',
                    'message' => 'เบอร์โทรศัพท์ซ้ำ',
                    'errors'  => ['phone' => ['เบอร์โทรศัพท์ซ้ำ']]
                ], 422);
            }
        }

        $user = User::create([
            'user_id' => $request->username,
            'name' => ($request->prefix ? $request->prefix . ' ' : '') . $request->first_name . ' ' . $request->last_name,
            'prefix' => $request->prefix,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'first_name_en' => $request->first_name_en,
            'last_name_en' => $request->last_name_en,
            'email' => $request->email,
            'phone_number' => substr(str_replace('-', '', $request->phone ?? ''), 0, 10),
            'citizen_id' => $request->citizen_id,
            'department' => $request->department,
            'role' => $request->role,
            'gender' => $request->gender,
            'password' => bcrypt($request->password),
            'plain_password' => $request->password,
            'status' => 'approved',
        ]);
        
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = 'user_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            if (!is_dir(public_path('uploads/profiles'))) {
                mkdir(public_path('uploads/profiles'), 0755, true);
            }
            $file->move(public_path('uploads/profiles'), $filename);
            $user->profile_photo = $filename;
            $user->save();
        }

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Ã Â¹ÂÃ Â¸ÂÃ Â¹â€°Ã Â¹â€žÃ Â¸â€šÃ Â¸ÂªÃ Â¸Â´Ã Â¸â€”Ã Â¸ËœÃ Â¸Â´Ã Â¹Å’Ã Â¸Å“Ã Â¸Â¹Ã Â¹â€°Ã Â¹Æ’Ã Â¸Å Ã Â¹â€°Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::where('user_id', $id)->first() ?? User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Ã Â¹â€žÃ Â¸Â¡Ã Â¹Ë†Ã Â¸Å¾Ã Â¸Å¡Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥Ã Â¸Å“Ã Â¸Â¹Ã Â¹â€°Ã Â¹Æ’Ã Â¸Å Ã Â¹â€°Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢Ã Â¸â€”Ã Â¸ÂµÃ Â¹Ë†Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Â¸'], 404);
        }

        $request->validate([
            'prefix'        => 'nullable|string|max:30',
            'first_name'    => 'nullable|string|max:30',
            'last_name'     => 'nullable|string|max:30',
            'first_name_en' => 'nullable|string|max:30',
            'last_name_en'  => 'nullable|string|max:30',
            'username'      => 'nullable|string|max:8|unique:users,user_id,' . $user->user_id . ',user_id',
            'email'         => 'nullable|email|max:50|unique:users,email,' . $user->user_id . ',user_id',
            'phone'         => 'nullable|string|max:20',
            'citizen_id'    => 'nullable|string|max:13',
            'position'      => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'role'          => 'nullable|string|max:50',
            'password'      => 'nullable|string|min:6',
            'gender'        => 'nullable|string|max:50',
        ]);

        $citizenId = preg_replace('/\D/', '', $request->citizen_id ?? '');
        if (!empty($citizenId)) {
            if (User::whereRaw("REPLACE(REPLACE(citizen_id, '-', ''), ' ', '') = ?", [$citizenId])->where('user_id', '!=', $user->user_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'field'   => 'citizen_id',
                    'message' => 'มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว',
                    'errors'  => ['citizen_id' => ['มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว']]
                ], 422);
            }
        }

        $cleanPhone = substr(preg_replace('/\D/', '', $request->phone ?? ''), 0, 10);
        if (!empty($cleanPhone)) {
            if (User::whereRaw("REPLACE(REPLACE(phone_number, '-', ''), ' ', '') = ?", [$cleanPhone])->where('user_id', '!=', $user->user_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'field'   => 'phone',
                    'message' => 'เบอร์โทรศัพท์ซ้ำ',
                    'errors'  => ['phone' => ['เบอร์โทรศัพท์ซ้ำ']]
                ], 422);
            }
        }

        $oldId = $user->user_id;

        // รักษาข้อมูลเดิมไว้เสมอ: อัปเดตเฉพาะค่าใหม่ที่มีการส่งมาและไม่ว่างเท่านั้น
        if ($request->filled('prefix')) {
            $user->prefix = $request->prefix;
        }
        if ($request->filled('first_name')) {
            $user->first_name = $request->first_name;
        }
        if ($request->filled('last_name')) {
            $user->last_name = $request->last_name;
        }
        if ($request->filled('first_name_en')) {
            $user->first_name_en = $request->first_name_en;
        }
        if ($request->filled('last_name_en')) {
            $user->last_name_en = $request->last_name_en;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('phone')) {
            $cleanPhone = substr(str_replace('-', '', $request->phone), 0, 10);
            if (!empty($cleanPhone)) {
                $user->phone_number = $cleanPhone;
            }
        }
        if ($request->filled('department')) {
            $user->department = $request->department;
        }
        if ($request->filled('role')) {
            $user->role = $request->role;
        }
        if ($request->filled('gender')) {
            $user->gender = $request->gender;
        }
        if ($request->filled('citizen_id')) {
            $user->citizen_id = $request->citizen_id;
        }
        if ($request->filled('position')) {
            $user->position = $request->position;
        }

        if ($request->filled('first_name') || $request->filled('last_name') || $request->filled('prefix')) {
            $computedName = trim(($user->prefix ? $user->prefix . ' ' : '') . ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            if (!empty($computedName)) {
                $user->name = $computedName;
            }
        }
        
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
            $user->plain_password = $request->password;
        }
        
        if ($oldId !== $request->username) {
            try {
                \Illuminate\Support\Facades\DB::table('users')->where('user_id', $oldId)->update([
                    'user_id' => $request->username
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'errors' => ['username' => ['Ã Â¹â€žÃ Â¸Â¡Ã Â¹Ë†Ã Â¸ÂªÃ Â¸Â²Ã Â¸Â¡Ã Â¸Â²Ã Â¸Â£Ã Â¸â€“Ã Â¹â‚¬Ã Â¸â€ºÃ Â¸Â¥Ã Â¸ÂµÃ Â¹Ë†Ã Â¸Â¢Ã Â¸â„¢Ã Â¸Å Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸Å“Ã Â¸Â¹Ã Â¹â€°Ã Â¹Æ’Ã Â¸Å Ã Â¹â€°Ã Â¹â€žÃ Â¸â€Ã Â¹â€°Ã Â¹â‚¬Ã Â¸â„¢Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸â€¡Ã Â¸Ë†Ã Â¸Â²Ã Â¸ÂÃ Â¸Â¡Ã Â¸ÂµÃ Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥Ã Â¸Â­Ã Â¸Â·Ã Â¹Ë†Ã Â¸â„¢Ã Â¸Å“Ã Â¸Â¹Ã Â¸ÂÃ Â¸ÂÃ Â¸Â±Ã Â¸Å¡Ã Â¸Å¡Ã Â¸Â±Ã Â¸ÂÃ Â¸Å Ã Â¸ÂµÃ Â¸â„¢Ã Â¸ÂµÃ Â¹â€°Ã Â¹Æ’Ã Â¸â„¢Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Å¡']]], 422);
            }
        }
        
        $user->user_id = $request->username;
        $user->exists = true;
        
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = 'user_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            if (!is_dir(public_path('uploads/profiles'))) {
                mkdir(public_path('uploads/profiles'), 0755, true);
            }
            $file->move(public_path('uploads/profiles'), $filename);
            $user->profile_photo = $filename;
        }
        
        $user->save();

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * ตรวจสอบความซ้ำซ้อนของเลขบัตรประชาชนและเบอร์โทรศัพท์
     */
    public function checkDuplicateUser(Request $request)
    {
        $type = $request->input('type'); // 'citizen_id' or 'phone'
        $value = trim($request->input('value', ''));
        $excludeUserId = $request->input('exclude_user_id');

        if ($type === 'citizen_id') {
            $cleanVal = preg_replace('/\D/', '', $value);
            if (empty($cleanVal) || strlen($cleanVal) !== 13) {
                return response()->json(['exists' => false]);
            }
            $query = User::whereRaw("REPLACE(REPLACE(citizen_id, '-', ''), ' ', '') = ?", [$cleanVal]);
            if (!empty($excludeUserId)) {
                $query->where('user_id', '!=', $excludeUserId);
            }
            $exists = $query->exists();
            return response()->json([
                'exists' => $exists,
                'field' => 'citizen_id',
                'message' => $exists ? 'มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว' : ''
            ]);
        }

        if ($type === 'phone') {
            $cleanPhone = substr(preg_replace('/\D/', '', $value), 0, 10);
            if (empty($cleanPhone) || strlen($cleanPhone) !== 10) {
                return response()->json(['exists' => false]);
            }
            $query = User::whereRaw("REPLACE(REPLACE(phone_number, '-', ''), ' ', '') = ?", [$cleanPhone]);
            if (!empty($excludeUserId)) {
                $query->where('user_id', '!=', $excludeUserId);
            }
            $exists = $query->exists();
            return response()->json([
                'exists' => $exists,
                'field' => 'phone',
                'message' => $exists ? 'เบอร์โทรศัพท์ซ้ำ' : ''
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Ã Â¹â‚¬Ã Â¸â€ºÃ Â¸Â¥Ã Â¸ÂµÃ Â¹Ë†Ã Â¸Â¢Ã Â¸â„¢Ã Â¸ÂªÃ Â¸â€“Ã Â¸Â²Ã Â¸â„¢Ã Â¸Â°Ã Â¹Æ’Ã Â¸Å Ã Â¹â€°Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢/Ã Â¸Â£Ã Â¸Â°Ã Â¸â€¡Ã Â¸Â±Ã Â¸Å¡Ã Â¸ÂÃ Â¸Â²Ã Â¸Â£Ã Â¹Æ’Ã Â¸Å Ã Â¹â€°Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢
     */
    public function changeStatus(Request $request, $id)
    {
        $user = User::where('user_id', $id)->first() ?? User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Ã Â¹â€žÃ Â¸Â¡Ã Â¹Ë†Ã Â¸Å¾Ã Â¸Å¡Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥Ã Â¸Å“Ã Â¸Â¹Ã Â¹â€°Ã Â¹Æ’Ã Â¸Å Ã Â¹â€°Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢Ã Â¸â€”Ã Â¸ÂµÃ Â¹Ë†Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Â¸'], 404);
        }
        $status = $request->input('status');
        $suspendReason = $status === 'suspended' ? $request->input('suspend_reason') : null;
        $user->update([
            'status' => $status,
            'suspend_reason' => $suspendReason
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Ã Â¹â‚¬Ã Â¸Å¾Ã Â¸Â´Ã Â¹Ë†Ã Â¸Â¡Ã Â¸â€ºÃ Â¸Â£Ã Â¸Â°Ã Â¹â‚¬Ã Â¸Â Ã Â¸â€”Ã Â¸Å¡Ã Â¸Â¸Ã Â¸â€žÃ Â¸Â¥Ã Â¸Â²Ã Â¸ÂÃ Â¸Â£Ã Â¸Â¥Ã Â¸â€¡Ã Â¸ÂÃ Â¸Â²Ã Â¸â„¢Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥
     */
    public function storePosition(Request $request)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('positions')) {
            return response()->json(['success' => false, 'message' => 'ระบบไม่มีการใช้งานตารางตำแหน่งแล้ว'], 400);
        }

        $request->validate([
            'name' => 'required|string|max:50|unique:positions,position_name',
            'role' => 'required|string|max:50',
        ]);

        $nextId = 'P' . (\App\Models\Position::count() + 1);

        $position = \App\Models\Position::create([
            'position_id' => $nextId,
            'position_name' => $request->name,
            'role' => $request->role,
        ]);

        return response()->json(['success' => true, 'position' => $position]);
    }

    /**
     * Ã Â¹â‚¬Ã Â¸Å¾Ã Â¸Â´Ã Â¹Ë†Ã Â¸Â¡Ã Â¹ÂÃ Â¸Å“Ã Â¸â„¢Ã Â¸Â/Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢Ã Â¸Â¥Ã Â¸â€¡Ã Â¸ÂÃ Â¸Â²Ã Â¸â„¢Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥
     */
    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:departments,department_name',
            'group' => 'required|string|max:50',
        ]);

        $nextId = 'D' . (\App\Models\Department::count() + 1);

        $department = \App\Models\Department::create([
            'department_id' => $nextId,
            'department_name' => $request->name,
            'group' => $request->group,
        ]);

        return response()->json(['success' => true, 'department' => $department]);
    }

    public function updateDepartment(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'group' => 'required|string|max:50',
        ]);

        $department = \App\Models\Department::where('department_id', $id)->orWhere('id', $id)->firstOrFail();
        $department->update([
            'department_name' => $request->name,
            'group' => $request->group,
        ]);

        return response()->json(['success' => true, 'department' => $department]);
    }

    public function destroyDepartment($id)
    {
        $department = \App\Models\Department::where('department_id', $id)->orWhere('id', $id)->first();
        if ($department) {
            $department->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    // --- Department Groups (Ã Â¸ÂªÃ Â¸Â±Ã Â¸â€¡Ã Â¸ÂÃ Â¸Â±Ã Â¸â€/Ã Â¸ÂÃ Â¹Ë†Ã Â¸Â²Ã Â¸Â¢Ã Â¸â€¡Ã Â¸Â²Ã Â¸â„¢) ---
    public function storeDepartmentGroup(Request $request)
    {
        $request->validate([
            'group_code' => 'required|string|max:50|unique:department_groups,group_code',
            'group_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = \App\Models\DepartmentGroup::create([
            'group_code' => $request->group_code,
            'group_name' => $request->group_name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'data' => $group]);
    }

    public function updateDepartmentGroup(Request $request, $id)
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = \App\Models\DepartmentGroup::where('group_code', $id)->orWhere('id', $id)->firstOrFail();
        $group->update([
            'group_name' => $request->group_name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'data' => $group]);
    }

    public function destroyDepartmentGroup($id)
    {
        $group = \App\Models\DepartmentGroup::where('group_code', $id)->orWhere('id', $id)->first();
        if ($group) {
            $group->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    /**
     * Ã Â¸â€Ã Â¸Â¶Ã Â¸â€¡Ã Â¸Â£Ã Â¸Â²Ã Â¸Â¢Ã Â¸Å Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¸â€”Ã Â¸Â±Ã Â¹â€°Ã Â¸â€¡Ã Â¸Â«Ã Â¸Â¡Ã Â¸â€ (JSON)
     */
    public function indexBuildings()
    {
        $buildings = \App\Models\Building::orderBy('building_id')->get();
        return response()->json($buildings);
    }

    /**
     * Ã Â¹â‚¬Ã Â¸Å¾Ã Â¸Â´Ã Â¹Ë†Ã Â¸Â¡Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¹Æ’Ã Â¸Â«Ã Â¸Â¡Ã Â¹Ë†Ã Â¸Â¥Ã Â¸â€¡Ã Â¸ÂÃ Â¸Â²Ã Â¸â„¢Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥
     */
    public function storeBuilding(Request $request)
    {
        $request->validate([
            'building_id' => 'required|string|max:50|unique:buildings,building_id',
            'name'        => 'required|string|max:255|unique:buildings,building_name',
            'floors'      => 'required|integer|min:1',
            'rooms'       => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ], [
            'building_id.unique' => 'Ã Â¸Â¡Ã Â¸ÂµÃ Â¸Â£Ã Â¸Â«Ã Â¸Â±Ã Â¸ÂªÃ Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¸ÂµÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¸Â¹Ã Â¹Ë†Ã Â¹Æ’Ã Â¸â„¢Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Å¡Ã Â¸â„¢Ã Â¸ÂµÃ Â¹â€°Ã Â¹Â Ã Â¸Â¥Ã Â¹â€°Ã Â¸Â§',
            'name.unique'        => 'Ã Â¸Â¡Ã Â¸ÂµÃ Â¸Å Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¸ÂµÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¸Â¹Ã Â¹Ë†Ã Â¹Æ’Ã Â¸â„¢Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Å¡Ã Â¸â„¢Ã Â¸ÂµÃ Â¹â€°Ã Â¹Â Ã Â¸Â¥Ã Â¹â€°Ã Â¸Â§',
            'building_id.required' => 'Ã Â¸Â Ã Â¸Â£Ã Â¸Â¸Ã Â¸â€œÃ Â¸Â²Ã Â¸Â Ã Â¸Â£Ã Â¸Â­Ã Â¸Â Ã Â¸Â£Ã Â¸Â«Ã Â¸Â±Ã Â¸ÂªÃ Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£',
            'name.required'        => 'Ã Â¸Â Ã Â¸Â£Ã Â¸Â¸Ã Â¸â€œÃ Â¸Â²Ã Â¸Â Ã Â¸Â£Ã Â¸Â­Ã Â¸Â Ã Â¸Å Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£',
        ]);

        $building = \App\Models\Building::create([
            'building_id'   => $request->building_id,
            'building_name' => $request->name,
            'floors'        => $request->floors,
            'room_number'   => (string)($request->rooms ?? 0),
            'description'   => $request->description,
        ]);

        return response()->json(['success' => true, 'building' => $building]);
    }

    public function updateBuilding(Request $request, $id)
    {
        $request->validate([
            'building_id' => 'required|string|max:50|unique:buildings,building_id,' . $id . ',building_id',
            'name'        => 'required|string|max:255|unique:buildings,building_name,' . $id . ',building_id',
            'floors'      => 'required|integer|min:1',
            'rooms'       => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ], [
            'building_id.unique' => 'Ã Â¸Â¡Ã Â¸ÂµÃ Â¸Â£Ã Â¸Â«Ã Â¸Â±Ã Â¸ÂªÃ Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¸ÂµÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¸Â¹Ã Â¹Ë†Ã Â¹Æ’Ã Â¸â„¢Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Å¡Ã Â¸â„¢Ã Â¸ÂµÃ Â¹â€°Ã Â¹Â Ã Â¸Â¥Ã Â¹â€°Ã Â¸Â§',
            'name.unique'        => 'Ã Â¸Â¡Ã Â¸ÂµÃ Â¸Å Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¸ÂµÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¸Â¹Ã Â¹Ë†Ã Â¹Æ’Ã Â¸â„¢Ã Â¸Â£Ã Â¸Â°Ã Â¸Å¡Ã Â¸Å¡Ã Â¸â„¢Ã Â¸ÂµÃ Â¹â€°Ã Â¹Â Ã Â¸Â¥Ã Â¹â€°Ã Â¸Â§',
            'building_id.required' => 'Ã Â¸Â Ã Â¸Â£Ã Â¸Â¸Ã Â¸â€œÃ Â¸Â²Ã Â¸Â Ã Â¸Â£Ã Â¸Â­Ã Â¸Â Ã Â¸Â£Ã Â¸Â«Ã Â¸Â±Ã Â¸ÂªÃ Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£',
            'name.required'        => 'Ã Â¸Â Ã Â¸Â£Ã Â¸Â¸Ã Â¸â€œÃ Â¸Â²Ã Â¸Â Ã Â¸Â£Ã Â¸Â­Ã Â¸Â Ã Â¸Å Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£',
        ]);

        $building = \App\Models\Building::where('building_id', $id)->firstOrFail();
        
        $building->update([
            'building_id'   => $request->building_id,
            'building_name' => $request->name,
            'floors'        => $request->floors,
            'room_number'   => (string)($request->rooms ?? 0),
            'description'   => $request->description,
        ]);

        return response()->json(['success' => true, 'building' => $building]);
    }

    /**
     * Ã Â¸Â¥Ã Â¸Å¡Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¸Â­Ã Â¸Â­Ã Â¸Â Ã Â¸Ë†Ã Â¸Â²Ã Â¸Â Ã Â¸Â Ã Â¸Â²Ã Â¸â„¢Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥
     */
    public function destroyBuilding($id)
    {
        try {
            $building = \App\Models\Building::findOrFail($id);
            $building->delete();
            return response()->json(['success' => true]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Check for foreign key constraint violation (SQLSTATE 23000)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'foreign key constraint')) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Ã Â¹â€žÃ Â¸Â¡Ã Â¹Ë†Ã Â¸ÂªÃ Â¸Â²Ã Â¸Â¡Ã Â¸Â²Ã Â¸Â£Ã Â¸â€“Ã Â¸Â¥Ã Â¸Å¡Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¹â€žÃ Â¸â€ Ã Â¹â€° Ã Â¹â‚¬Ã Â¸â„¢Ã Â¸Â·Ã Â¹Ë†Ã Â¸Â­Ã Â¸â€¡Ã Â¸Ë†Ã Â¸Â²Ã Â¸Â Ã Â¸Â¡Ã Â¸ÂµÃ Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥Ã Â¸Â Ã Â¸Â²Ã Â¸Â£Ã Â¹Â Ã Â¸Ë†Ã Â¹â€°Ã Â¸â€¡Ã Â¸â€¹Ã Â¹Ë†Ã Â¸Â­Ã Â¸Â¡Ã Â¸â€”Ã Â¸ÂµÃ Â¹Ë†Ã Â¸Å“Ã Â¸Â¹Ã Â¸Â Ã Â¸Â Ã Â¸Â±Ã Â¸Å¡Ã Â¸Â­Ã Â¸Â²Ã Â¸â€žÃ Â¸Â²Ã Â¸Â£Ã Â¸â„¢Ã Â¸ÂµÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¢Ã Â¸Â¹Ã Â¹Ë†'
                ]);
            }
            return response()->json([
                'success' => false, 
                'message' => 'Ã Â¹â‚¬Ã Â¸Â Ã Â¸Â´Ã Â¸â€ Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Å“Ã Â¸Â´Ã Â¸â€ Ã Â¸Å¾Ã Â¸Â¥Ã Â¸Â²Ã Â¸â€ Ã Â¹Æ’Ã Â¸â„¢Ã Â¸Â Ã Â¸Â²Ã Â¸Â£Ã Â¸Â¥Ã Â¸Å¡Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥Ã Â¸â€”Ã Â¸ÂµÃ Â¹Ë†Ã Â¸Â Ã Â¸Â²Ã Â¸â„¢Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Â¡Ã Â¸Â¹Ã Â¸Â¥'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Ã Â¹â‚¬Ã Â¸ÂÃ Â¸Â´Ã Â¸â€Ã Â¸â€šÃ Â¹â€°Ã Â¸Â­Ã Â¸Å“Ã Â¸Â´Ã Â¸â€Ã Â¸Å¾Ã Â¸Â¥Ã Â¸Â²Ã Â¸â€: ' . $e->getMessage()
            ]);
        }
    }

    public function storeRepairProject(Request $request)
    {
        $request->validate([
            'project_code' => 'required|string|unique:repair_projects,project_code',
            'name' => 'required|string',
            'department' => 'required|string',
            'responsible_person' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'budget_source' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $fiscalYear = $request->input('fiscal_year');
        if (empty($fiscalYear)) {
            if (preg_match('/-(\d{4})-/', $request->project_code, $matches)) {
                $fiscalYear = $matches[1];
            } else {
                $fiscalYear = (string)(date('Y') + 543);
            }
        }

        $dept = $request->department;
        $budgetSourceKey = $request->input('budget_source', 'งบประมาณกลาง') ?: 'งบประมาณกลาง';
        $budgetAmount = (float)$request->budget;

        // ตรวจสอบวงเงินจัดสรรของฝ่ายตามปีงบประมาณ
        if (\Illuminate\Support\Facades\Schema::hasTable('department_budgets')) {
            $totalBudgetRecord = \App\Models\DepartmentBudget::where('department', $dept)
                ->where('fiscal_year', $fiscalYear)
                ->where('budget_type', 'งบประมาณรวม')
                ->first();

            $allocatedCeiling = 0;
            if ($totalBudgetRecord) {
                $allocatedCeiling = (float)$totalBudgetRecord->allocated_amount;
            } else {
                $allocatedCeiling = (float)\App\Models\DepartmentBudget::where('department', $dept)
                    ->where('fiscal_year', $fiscalYear)
                    ->sum('allocated_amount');
            }

            // หากฝ่ายยังไม่ได้ตั้งงบประมาณในปีงบประมาณนี้ (งบประมาณตั้งปีต่อปีและใช้ได้เฉพาะของปีนั้นๆ)
            if ($allocatedCeiling <= 0) {
                return response()->json([
                    'message' => 'ไม่สามารถบันทึกโครงการได้ เนื่องจาก' . $dept . ' ยังไม่มีการตั้งงบประมาณสำหรับปีงบประมาณ ' . $fiscalYear . ' (งบประมาณเป็น ฿0.00) โดยงบประมาณตั้งปีต่อปีและใช้ได้เฉพาะของปีนั้นๆ เท่านั้น กรุณากำหนดงบประมาณประจำปี ' . $fiscalYear . ' ก่อนสร้างโครงการ',
                    'errors' => ['budget' => ['ฝ่าย ' . $dept . ' ยังไม่มีการตั้งงบประมาณประจำปี ' . $fiscalYear . ' (งบประมาณตั้งปีต่อปีและใช้ได้เฉพาะของปีนั้นๆ)']]
                ], 422);
            }

            $alreadyAllocated = (float)\App\Models\RepairProject::where('department', $dept)
                ->where('fiscal_year', $fiscalYear)
                ->sum('budget');
            $available = max(0, $allocatedCeiling - $alreadyAllocated);

            if ($budgetAmount > $available) {
                return response()->json([
                    'message' => 'งบประมาณโครงการเกินวงเงินคงเหลือของ' . $dept . ' ประจำปี ' . $fiscalYear . ' (คงเหลือให้จัดสรรได้อีก ' . number_format($available, 2) . ' บาท จากวงเงินจัดสรรปี ' . $fiscalYear . ' จำนวน ' . number_format($allocatedCeiling, 2) . ' บาท)',
                    'errors' => ['budget' => ['งบประมาณเกินวงเงินคงเหลือของ' . $dept . ' ประจำปี ' . $fiscalYear . ' (คงเหลือให้จัดสรรได้อีก ' . number_format($available, 2) . ' บาท)']]
                ], 422);
            }
        } elseif (\Illuminate\Support\Facades\Schema::hasTable('budget_sources')) {
            $budgetSource = \App\Models\BudgetSource::where('source_key', $budgetSourceKey)->first();
            $defaultCeilings = [
                'งบประมาณกลาง' => 2000000.00,
                'งบพัฒนาสถานศึกษา' => 1000000.00,
                'งบเงินรายได้' => 500000.00,
                'งบอุดหนุน' => 500000.00,
            ];
            $ceiling = $budgetSource ? (float)$budgetSource->total_allocated : ($defaultCeilings[$budgetSourceKey] ?? 0);
            if ($ceiling > 0) {
                $alreadyAllocated = (float)\App\Models\RepairProject::where('budget_source', $budgetSourceKey)->sum('budget');
                $available = max(0, $ceiling - $alreadyAllocated);
                if ($budgetAmount > $available) {
                    return response()->json([
                        'message' => 'งบประมาณเกินวงเงินคงเหลือของหมวด ' . $budgetSourceKey . ' (เหลือให้จัดสรรได้อีก ' . number_format($available, 2) . ' บาท)',
                        'errors' => ['budget' => ['งบประมาณเกินวงเงินคงเหลือของหมวด ' . $budgetSourceKey . ' (เหลือให้จัดสรรได้อีก ' . number_format($available, 2) . ' บาท)']]
                    ], 422);
                }
            }
        }

        $project = \App\Models\RepairProject::create([
            'project_code' => $request->project_code,
            'name' => $request->name,
            'department' => $request->department,
            'responsible_person' => $request->responsible_person,
            'budget' => $request->budget,
            'used_budget' => 0,
            'fiscal_year' => $fiscalYear,
            'budget_source' => $budgetSourceKey,
            'description' => $request->input('description'),
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'project' => $project]);
    }

    public function updateDepartmentBudgets(Request $request)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('department_budgets')) {
            return response()->json(['success' => false, 'message' => 'ตาราง department_budgets ยังไม่ได้ติดตั้ง'], 500);
        }

        $fiscalYear = $request->input('fiscal_year', (string)(date('Y') + 543));

        // ตรวจสอบว่าเป็นรูปแบบตารางกรอกงบของทั้ง 4 ฝ่าย
        if ($request->has('dept_budgets') && is_array($request->input('dept_budgets'))) {
            $deptBudgets = $request->input('dept_budgets');

            foreach ($deptBudgets as $deptName => $amount) {
                $cleanedAmount = (float)str_replace(',', '', (string)$amount);
                \App\Models\DepartmentBudget::updateOrCreate(
                    [
                        'department' => $deptName,
                        'fiscal_year' => $fiscalYear,
                        'budget_type' => 'งบประมาณรวม',
                    ],
                    [
                        'allocated_amount' => $cleanedAmount,
                    ]
                );

                // ลบเรคอร์ดประเภทงบย่อยเดิม (ถ้ามี) เพื่อป้องกันยอดซ้ำซ้อน
                \App\Models\DepartmentBudget::where('department', $deptName)
                    ->where('fiscal_year', $fiscalYear)
                    ->where('budget_type', '!=', 'งบประมาณรวม')
                    ->delete();
            }

            $stats = \App\Models\DepartmentBudget::getStatsForYear($fiscalYear);

            return response()->json([
                'success' => true,
                'message' => 'บันทึกการจัดสรรงบประมาณของทุกฝ่าย ประจำปีงบประมาณ ' . $fiscalYear . ' เรียบร้อยแล้ว',
                'stats' => $stats,
            ]);
        }

        $department = $request->input('department');
        $budgets = $request->input('budgets', []);

        if ($department && !empty($budgets)) {
            foreach ($budgets as $typeKey => $amount) {
                $cleanedAmount = (float)str_replace(',', '', (string)$amount);
                \App\Models\DepartmentBudget::updateOrCreate(
                    [
                        'department' => $department,
                        'fiscal_year' => $fiscalYear,
                        'budget_type' => $typeKey,
                    ],
                    [
                        'allocated_amount' => $cleanedAmount,
                    ]
                );
            }
        }

        $stats = \App\Models\DepartmentBudget::getStatsForYear($fiscalYear);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกการจัดสรรงบประมาณสำเร็จ',
            'stats' => $stats,
        ]);
    }

    public function getDepartmentBudgetStats(Request $request)
    {
        $fiscalYear = $request->input('fiscal_year', (string)(date('Y') + 543));
        $stats = \Illuminate\Support\Facades\Schema::hasTable('department_budgets')
            ? \App\Models\DepartmentBudget::getStatsForYear($fiscalYear)
            : null;

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function updateBudgetSources(Request $request)
    {
        $request->validate([
            'sources' => 'required|array',
        ]);

        if (!\Illuminate\Support\Facades\Schema::hasTable('budget_sources')) {
            return response()->json(['success' => false, 'message' => 'ตาราง budget_sources ยังไม่ได้ติดตั้ง'], 500);
        }

        $sources = $request->input('sources', []);
        foreach ($sources as $key => $val) {
            if (is_array($val) && isset($val['source_key'])) {
                $sourceKey = $val['source_key'];
                $amount = (float)str_replace(',', '', (string)($val['total_allocated'] ?? 0));
                $fiscal = $val['fiscal_year'] ?? (string)(date('Y') + 543);
            } else {
                $sourceKey = $key;
                $amount = (float)str_replace(',', '', (string)$val);
                $fiscal = $request->input('fiscal_year', (string)(date('Y') + 543));
            }
            \App\Models\BudgetSource::updateOrCreate(
                ['source_key' => $sourceKey],
                [
                    'source_name' => $sourceKey,
                    'total_allocated' => $amount,
                    'fiscal_year' => $fiscal,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'บันทึกวงเงินงบประมาณสำเร็จ',
            'sources' => \App\Models\BudgetSource::all()->keyBy('source_key')
        ]);
    }
}

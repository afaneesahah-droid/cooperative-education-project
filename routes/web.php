<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\RepairRequestController;
use App\Http\Controllers\BasicDataController;
use App\Models\Device;
use App\Models\RepairRequest;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public & Auth Routes
|--------------------------------------------------------------------------
*/

Route::get('/show-tables', function() {
    $tables = DB::select('SHOW TABLES');
    return response()->json($tables);
});

Route::get('/', [AuthController::class, 'showLogin'])->name('login.show');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Forgot Password Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtp'])->name('password.otp.send');
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.otp.verify');
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.reset.submit');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
if (!function_exists('checkRoleAndRedirect')) {
    function checkRoleAndRedirect($requiredRole) {
        $user = Auth::user();
        if (!$user) return redirect('/login');
        $roles = array_map('trim', explode(',', mb_strtolower($user->role ?? '', 'UTF-8')));
        
        if (in_array('admin', $roles) || in_array('ผู้ดูแลระบบ', $roles)) return null;
        
        // For teacher, allow everyone (it's the base role for standard users)
        if ($requiredRole === 'teacher') return null;
        
        $allowed = false;
        
        switch (strtolower($requiredRole)) {
            case 'head':
                $allowed = in_array('head', $roles) || in_array('หัวหน้างาน', $roles);
                break;
            case 'technician':
                $allowed = in_array('technician', $roles) || in_array('ช่าง', $roles);
                break;
            case 'executive':
                $allowed = in_array('executive', $roles) || in_array('ผู้บริหาร', $roles);
                break;
            case 'admin':
                $allowed = in_array('admin', $roles) || in_array('ผู้ดูแลระบบ', $roles);
                break;
            default:
                $allowed = in_array(strtolower($requiredRole), $roles);
        }
        
        if (!$allowed) {
            return redirect('/select-role')->with('error', 'ระบบตรวจพบการเปลี่ยนบัญชี จึงสลับกลับมายังหน้าหลักของคุณ');
        }
        return null;
    }
}

    
    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Role Selection
    Route::get('/select-role', function () {
        $user = Auth::user();
        $roles = array_map('trim', explode(',', mb_strtolower($user->role ?? '', 'UTF-8')));
        
        $hasSpecialRole = count(array_intersect(['admin', 'executive', 'head', 'technician', 'ผู้ดูแลระบบ', 'ผู้บริหาร', 'หัวหน้างาน', 'ช่าง'], $roles)) > 0;
        
        if (!$hasSpecialRole) {
            return redirect('/dashboard/teacher');
        }
        
        // Re-read roles for view rendering
        $roles = array_map('trim', explode(',', $user->role ?? ''));
        return view('auth.select-role', compact('roles'));
    })->name('role.select');

    Route::post('/select-role', function (\Illuminate\Http\Request $request) {
        $request->validate(['system_role' => 'required|string']);
        $role = $request->input('system_role');
        return redirect('/dashboard/' . $role);
    })->name('role.process');
    
    // Profile
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Repair Requests API
    Route::get('/repair-requests', function () {
        return redirect('/dashboard/teacher?tab=status');
    });
    Route::post('/repair-requests', [RepairRequestController::class, 'store'])->name('repair.store');
    Route::put('/repair-requests/{id}', [RepairRequestController::class, 'update'])->name('repair.update');
    Route::post('/repair-requests/{id}/finish', [RepairRequestController::class, 'finish'])->name('repair.finish');
    Route::post('/repair-requests/{id}/assign', [RepairRequestController::class, 'assign'])->name('repair.assign');
    Route::post('/repair-requests/{id}/accept', [RepairRequestController::class, 'accept'])->name('repair.accept');
    Route::post('/repair-requests/rate', [RepairRequestController::class, 'rate'])->name('repair.rate');
    Route::post('/repair-requests/{id}/approve-budget', [RepairRequestController::class, 'approveBudget'])->name('repair.approve_budget');
    Route::post('/repair-requests/{id}/request-budget', [RepairRequestController::class, 'requestBudget'])->name('repair.request_budget');
    Route::post('/repair-requests/{id}/acknowledge-parts', [RepairRequestController::class, 'acknowledgeParts'])->name('repair.acknowledge_parts');
    Route::post('/budget/deduct', [RepairRequestController::class, 'deductProjectBudget'])->name('budget.deduct');
    Route::get('/repair-requests/{id}/print-cost', [RepairRequestController::class, 'printCost'])->name('repair.print_cost');

    // Dashboards
    Route::get('/dashboard/teacher', function () {
        if ($redirect = checkRoleAndRedirect('teacher')) return $redirect;
        $user = Auth::user();
        $devices = Device::with(['building', 'room'])->get();
        $requests = RepairRequest::where('user_id', $user->user_id)
            ->where(function($q) {
                $q->whereNull('type')->orWhere('type', '!=', 'เบิกงบประมาณ');
            })
            ->with(['device.assetKind', 'assignee', 'repairProject', 'user.departmentRelation', 'item', 'latestMaintenance'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ประวัติการเบิกเฉพาะของหน้าแจ้งซ่อม
        $teacherRequisitions = RepairRequest::where(function($q) {
                $q->where('type', 'เบิกงบประมาณ')
                  ->orWhereNotNull('repair_project_id');
            })
            ->where(function($q) {
                $q->where('note', 'หน้าแจ้งซ่อม')
                  ->orWhere('note', 'teacher')
                  ->orWhereHas('user', function($uq) {
                      $uq->where('role', 'like', '%teacher%')
                         ->orWhere('role', 'like', '%ครู%');
                  });
            })
            ->with(['device.assetKind', 'assignee', 'repairProject', 'user', 'item', 'latestMaintenance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboards.teacher', compact('user', 'devices', 'requests', 'teacherRequisitions'));
    })->name('dashboard.teacher');

    Route::get('/migrate-run-temp', function() {
    \Artisan::call('migrate', ['--force' => true]);
    return 'Migration completed: ' . \Artisan::output();
});

Route::get('/dashboard/technician', function () {
        if ($redirect = checkRoleAndRedirect('technician')) return $redirect;
        $user = Auth::user();
        $devices = Device::with(['building', 'room'])->get();
        $requests = RepairRequest::where(function($q) {
                $q->whereNull('type')->orWhere('type', '!=', 'เบิกงบประมาณ');
            })
            ->with(['device.assetKind', 'user.departmentRelation', 'assignee', 'repairProject', 'item', 'latestMaintenance'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ประวัติการเบิก / บันทึกค่าซ่อมจริงของฝ่ายเจ้าหน้าที่ซ่อมบำรุง
        $technicianRequisitions = RepairRequest::where(function($q) {
                $q->where('type', 'เบิกงบประมาณ')
                  ->orWhereNotNull('repair_project_id')
                  ->orWhere('budget', '>', 0);
            })
            ->where(function($q) {
                $q->whereHas('assignments')
                  ->orWhereHas('maintenances')
                  ->orWhere('note', 'like', '%บันทึกค่าซ่อมแล้ว%')
                  ->orWhere('note', 'like', '%เจ้าหน้าที่%')
                  ->orWhereHas('user', function($uq) {
                      $uq->where('role', 'like', '%technician%')
                         ->orWhere('role', 'like', '%ช่าง%');
                  });
            })
            ->where(function($q) {
                $q->where('note', 'not like', '%หน้าแจ้งซ่อม%')
                  ->orWhereNull('note');
            })
            ->with(['device.assetKind', 'user.departmentRelation', 'assignee', 'repairProject', 'item', 'latestMaintenance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboards.technician', compact('user', 'devices', 'requests', 'technicianRequisitions'));
    })->name('dashboard.technician');

    Route::get('/dashboard/head', function () {
        if ($redirect = checkRoleAndRedirect('head')) return $redirect;
        $technicians = User::where('role', 'like', '%technician%')
                           ->orWhere('role', 'like', '%ช่าง%')
                           ->get();
        return view('dashboards.head', compact('technicians'));
    })->name('dashboard.head');

    Route::get('/dashboard/executive', function () {
        if ($redirect = checkRoleAndRedirect('executive')) return $redirect;
        $user = Auth::user();
        return view('dashboards.executive', compact('user'));
    })->name('dashboard.executive');

    Route::get('/dashboard/admin', [AdminController::class, 'index'])->name('dashboard.admin');

    // Admin: Device/Asset Management
    Route::get('/admin/devices', [DeviceController::class, 'index'])->name('admin.devices.index');
    Route::get('/admin/devices/{id}', [DeviceController::class, 'show'])->name('admin.devices.show');
    Route::post('/admin/devices', [DeviceController::class, 'store'])->name('admin.devices.store');
    Route::match(['post', 'put'], '/admin/devices/{id}', [DeviceController::class, 'update'])->name('admin.devices.update');
    Route::post('/admin/devices/{id}/status', [DeviceController::class, 'changeStatus'])->name('admin.devices.status');
    Route::delete('/admin/devices/{id}', [DeviceController::class, 'destroy'])->name('admin.devices.destroy');

    // Admin: Basic Data Management
    Route::post('/admin/device-types', [BasicDataController::class, 'storeDeviceType'])->name('admin.device-types.store');
    Route::match(['post', 'put'], '/admin/device-types/{id}', [BasicDataController::class, 'updateDeviceType'])->name('admin.device-types.update');
    Route::delete('/admin/device-types/{id}', [BasicDataController::class, 'destroyDeviceType'])->name('admin.device-types.destroy');

    Route::post('/admin/repair-types', [BasicDataController::class, 'storeRepairType'])->name('admin.repair-types.store');
    Route::delete('/admin/repair-types/{id}', [BasicDataController::class, 'destroyRepairType'])->name('admin.repair-types.destroy');

    Route::post('/admin/personnel-types', [BasicDataController::class, 'storePersonnelType'])->name('admin.personnel-types.store');
    Route::match(['post', 'put'], '/admin/personnel-types/{id}', [BasicDataController::class, 'updatePersonnelType'])->name('admin.personnel-types.update');
    Route::delete('/admin/personnel-types/{id}', [BasicDataController::class, 'destroyPersonnelType'])->name('admin.personnel-types.destroy');

    Route::post('/admin/asset-kinds', [BasicDataController::class, 'storeAssetKind'])->name('admin.asset-kinds.store');
    Route::match(['post', 'put'], '/admin/asset-kinds/{id}', [BasicDataController::class, 'updateAssetKind'])->name('admin.asset-kinds.update');
    Route::delete('/admin/asset-kinds/{id}', [BasicDataController::class, 'destroyAssetKind'])->name('admin.asset-kinds.destroy');
    
    Route::post('/admin/asset-kinds-by-name/rename', [BasicDataController::class, 'renameAssetKindByName'])->name('admin.asset-kinds.rename-by-name');
    Route::delete('/admin/asset-kinds-by-name', [BasicDataController::class, 'destroyAssetKindByName'])->name('admin.asset-kinds.destroy-by-name');
    
    Route::post('/admin/asset-categories', [BasicDataController::class, 'storeAssetCategory'])->name('admin.asset-categories.store');
    Route::post('/admin/asset-categories/rename', [BasicDataController::class, 'renameAssetCategory'])->name('admin.asset-categories.rename');
    Route::delete('/admin/asset-categories', [BasicDataController::class, 'destroyAssetCategory'])->name('admin.asset-categories.destroy');

    // Admin: Rooms
    Route::post('/admin/rooms', [BasicDataController::class, 'storeRoom'])->name('admin.rooms.store');
    Route::put('/admin/rooms', [BasicDataController::class, 'updateRoom'])->name('admin.rooms.update');
    Route::delete('/admin/rooms', [BasicDataController::class, 'destroyRoom'])->name('admin.rooms.destroy');

    // Admin: Users Management
    Route::post('/admin/users/{id}/approve', [AdminController::class, 'approveUser'])->name('admin.users.approve');
    Route::post('/admin/users/{id}/reject',  [AdminController::class, 'rejectUser'])->name('admin.users.reject');
    Route::post('/admin/users/store', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/admin/users/check-duplicate', [AdminController::class, 'checkDuplicateUser'])->name('admin.users.checkDuplicate');
    Route::match(['post', 'put'], '/admin/users/{id}/update', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{id}/status', [AdminController::class, 'changeStatus'])->name('admin.users.status');

    // Admin: Positions, Departments & Groups
    Route::post('/admin/positions', [AdminController::class, 'storePosition'])->name('admin.positions.store');
    
    Route::post('/admin/departments', [AdminController::class, 'storeDepartment'])->name('admin.departments.store');
    Route::match(['post', 'put'], '/admin/departments/{id}', [AdminController::class, 'updateDepartment'])->name('admin.departments.update');
    Route::delete('/admin/departments/{id}', [AdminController::class, 'destroyDepartment'])->name('admin.departments.destroy');

    Route::post('/admin/department-groups', [AdminController::class, 'storeDepartmentGroup'])->name('admin.department-groups.store');
    Route::match(['post', 'put'], '/admin/department-groups/{id}', [AdminController::class, 'updateDepartmentGroup'])->name('admin.department-groups.update');
    Route::delete('/admin/department-groups/{id}', [AdminController::class, 'destroyDepartmentGroup'])->name('admin.department-groups.destroy');

    // Admin: Buildings
    Route::get('/admin/buildings', [AdminController::class, 'indexBuildings'])->name('admin.buildings.index');
    Route::post('/admin/buildings', [AdminController::class, 'storeBuilding'])->name('admin.buildings.store');
    Route::post('/admin/buildings/{id}/update', [AdminController::class, 'updateBuilding'])->name('admin.buildings.update');
    Route::post('/admin/buildings/{id}/delete', [AdminController::class, 'destroyBuilding'])->name('admin.buildings.destroy');
    Route::post('/admin/repair-projects', [AdminController::class, 'storeRepairProject'])->name('admin.repair-projects.store');
    Route::post('/admin/budget-sources', [AdminController::class, 'updateBudgetSources'])->name('admin.budget-sources.update');
    Route::post('/admin/department-budgets', [AdminController::class, 'updateDepartmentBudgets'])->name('admin.department-budgets.update');
    Route::get('/admin/department-budgets/stats', [AdminController::class, 'getDepartmentBudgetStats'])->name('admin.department-budgets.stats');
});

/*
|--------------------------------------------------------------------------
| System & Development Utilities
|--------------------------------------------------------------------------
*/

Route::get('/clear-cache', function() {
    try {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        return "à¸¥à¹‰à¸²à¸‡à¹à¸„à¸Šà¸—à¸±à¹‰à¸‡à¸«à¸¡à¸”à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§!";
    } catch (\Exception $e) {
        return "à¹€à¸à¸´à¸”à¸‚à¹‰à¸­à¸œà¸´à¸”à¸žà¸¥à¸²à¸”à¹ƒà¸™à¸à¸²à¸£à¸¥à¹‰à¸²à¸‡à¹à¸„à¸Š: " . $e->getMessage();
    }
});

Route::get('/show-log', function() {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return '<pre>à¹„à¸¡à¹ˆà¸žà¸šà¹„à¸Ÿà¸¥à¹Œ log</pre>';
    }
    $lines = file($logFile);
    $last100 = array_slice($lines, -100);
    return '<pre style="font-size:12px;background:#1e1e1e;color:#d4d4d4;padding:20px;white-space:pre-wrap;">'
        . htmlspecialchars(implode('', $last100))
        . '</pre>';
});

Route::get('/reset-all-passwords', function() {
    try {
        $users = [
            '40660001' => 'Tec1234',
            '40660002' => 'Tech1234',
            '40660003' => 'Head1234',
            '40660004' => 'Exe1234',
            'admin' => 'Admin1234'
        ];

        $updated = [];
        foreach ($users as $username => $password) {
            $user = User::where('user_id', $username)->first();
            if ($user) {
                $user->password = Hash::make($password);
                $user->status = 'approved';
                $user->save();
                $updated[] = "$username";
            }
        }
        return "à¸­à¸±à¸›à¹€à¸”à¸•à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¸—à¸¸à¸à¸šà¸±à¸à¸Šà¸µà¹€à¸£à¸´à¹ˆà¸¡à¸•à¹‰à¸™à¸•à¸²à¸¡ Seeder à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§! à¸šà¸±à¸à¸Šà¸µà¸—à¸µà¹ˆà¸­à¸±à¸›à¹€à¸”à¸•: " . implode(', ', $updated);
    } catch (\Exception $e) {
        return "à¹€à¸à¸´à¸”à¸‚à¹‰à¸­à¸œà¸´à¸”à¸žà¸¥à¸²à¸”: " . $e->getMessage();
    }
});

Route::get('/seed-db', function() {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return "à¸£à¸±à¸™à¸‚à¹‰à¸­à¸¡à¸¹à¸¥ Seeder à¸šà¸™à¹€à¸‹à¸´à¸£à¹Œà¸Ÿà¹€à¸§à¸­à¸£à¹Œà¸ªà¸³à¹€à¸£à¹‡à¸ˆ!";
    } catch (\Exception $e) {
        return "à¹€à¸à¸´à¸”à¸‚à¹‰à¸­à¸œà¸´à¸”à¸žà¸¥à¸²à¸”à¹ƒà¸™à¸à¸²à¸£à¸£à¸±à¸™ Seeder: " . $e->getMessage();
    }
});

Route::get('/migrate-db', function() {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return "à¸£à¸±à¸™ Migration à¸šà¸™à¹€à¸‹à¸´à¸£à¹Œà¸Ÿà¹€à¸§à¸­à¸£à¹Œà¸ªà¸³à¹€à¸£à¹‡à¸ˆ! à¸ªà¸£à¹‰à¸²à¸‡à¸•à¸²à¸£à¸²à¸‡à¹ƒà¸«à¸¡à¹ˆà¸—à¸±à¹‰à¸‡à¸«à¸¡à¸”à¹à¸¥à¹‰à¸§.";
    } catch (\Exception $e) {
        return "à¹€à¸à¸´à¸”à¸‚à¹‰à¸­à¸œà¸´à¸”à¸žà¸¥à¸²à¸”à¹ƒà¸™à¸à¸²à¸£à¸£à¸±à¸™ Migration: " . $e->getMessage();
    }
});

Route::get('/patch-db-and-seed', function () {
    try {
        $db = DB::connection();
        $columns = $db->select("SHOW COLUMNS FROM users");
        $colNames = array_map(fn($c) => $c->Field, $columns);

        if (!in_array('prefix', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `prefix` varchar(30) NULL AFTER `name`");
        }
        if (!in_array('first_name', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `first_name` varchar(30) NULL AFTER `prefix`");
        }
        if (!in_array('last_name', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `last_name` varchar(30) NULL AFTER `first_name`");
        }
        if (!in_array('gender', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `gender` varchar(10) NULL AFTER `last_name`");
        }
        if (!in_array('phone_number', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `phone_number` varchar(10) NULL");
        }
        if (!in_array('phone', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `phone` varchar(20) NULL");
        }
        if (!in_array('position_id', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `position_id` varchar(2) NULL");
        }
        if (!in_array('department_id', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `department_id` varchar(2) NULL");
        }
        if (!in_array('department', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `department` varchar(255) NULL");
        }
        if (!in_array('position', $colNames)) {
            $db->statement("ALTER TABLE users ADD COLUMN `position` varchar(255) NULL");
        }

        if (Schema::hasTable('buildings')) {
            $db->statement("ALTER TABLE buildings MODIFY COLUMN `building_name` varchar(255) NOT NULL");
        }

        Artisan::call('db:seed', ['--force' => true]);
        return "âœ… Patched columns and seeded data successfully!";
    } catch (\Exception $e) {
        return "âŒ Error: " . $e->getMessage();
    }
});

Route::get('/migrate-fresh-db-v6', function () {
    try {
        Artisan::call('migrate:fresh', [
            '--seed'  => true,
            '--force' => true,
        ]);
        return "âœ… migrate:fresh --seed à¸ªà¸³à¹€à¸£à¹‡à¸ˆ! à¸•à¸²à¸£à¸²à¸‡à¹à¸¥à¸°à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¸—à¸±à¹‰à¸‡à¸«à¸¡à¸”à¸–à¸¹à¸à¸ªà¸£à¹‰à¸²à¸‡à¹ƒà¸«à¸¡à¹ˆà¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§ (v6)";
    } catch (\Exception $e) {
        return "âŒ à¹€à¸à¸´à¸”à¸‚à¹‰à¸­à¸œà¸´à¸”à¸žà¸¥à¸²à¸”: " . $e->getMessage();
    }
});

Route::get('/migrate-fresh-db-v5', function() {
    $maxRetries = 10;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        try {
            Schema::disableForeignKeyConstraints();
            
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $array = (array) $table;
                $tableName = reset($array);
                Schema::drop($tableName);
            }
            
            Schema::enableForeignKeyConstraints();

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            
            return "à¸¥à¹‰à¸²à¸‡à¸•à¸²à¸£à¸²à¸‡à¹€à¸à¹ˆà¸² à¸£à¸±à¸™à¹‚à¸„à¸£à¸‡à¸ªà¸£à¹‰à¸²à¸‡à¹ƒà¸«à¸¡à¹ˆ à¹à¸¥à¸°à¸¥à¸‡à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¸ˆà¸³à¸¥à¸­à¸‡à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§ (v5)!";
        } catch (\Exception $e) {
            $retryCount++;
            Log::error("Migration attempt $retryCount failed: " . $e->getMessage());
            if ($retryCount >= $maxRetries) {
                return "à¹€à¸à¸´à¸”à¸‚à¹‰à¸­à¸œà¸´à¸”à¸žà¸¥à¸²à¸” (à¸¥à¸­à¸‡à¹ƒà¸«à¸¡à¹ˆà¸„à¸£à¸š $maxRetries à¸„à¸£à¸±à¹‰à¸‡à¹à¸¥à¹‰à¸§): " . $e->getMessage();
            }
            sleep(1);
        }
    }
});

Route::get('/migrate-fresh-db', function() {
    $maxRetries = 5;
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        try {
            Schema::disableForeignKeyConstraints();
            
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $array = (array) $table;
                $tableName = reset($array);
                Schema::drop($tableName);
            }
            
            Schema::enableForeignKeyConstraints();

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            
            return "à¸¥à¹‰à¸²à¸‡à¸•à¸²à¸£à¸²à¸‡à¹€à¸à¹ˆà¸² à¸£à¸±à¸™à¹‚à¸„à¸£à¸‡à¸ªà¸£à¹‰à¸²à¸‡à¹ƒà¸«à¸¡à¹ˆ à¹à¸¥à¸°à¸¥à¸‡à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¸ˆà¸³à¸¥à¸­à¸‡à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§!";
        } catch (\Exception $e) {
            $retryCount++;
            Log::error("Migration attempt $retryCount failed: " . $e->getMessage());
            if ($retryCount >= $maxRetries) {
                return "à¹€à¸à¸´à¸”à¸‚à¹‰à¸­à¸œà¸´à¸”à¸žà¸¥à¸²à¸” (à¸¥à¸­à¸‡à¹ƒà¸«à¸¡à¹ˆà¸„à¸£à¸š $maxRetries à¸„à¸£à¸±à¹‰à¸‡à¹à¸¥à¹‰à¸§): " . $e->getMessage();
            }
            sleep(1);
        }
    }
});



Route::get('/test-js', function() {
    $requests = \App\Models\RepairRequest::with(['device', 'device.room'])->orderBy('id', 'desc')->take(10)->get();
    $output = "Latest 10 Requests:<br>";
    foreach ($requests as $req) {
        $output .= "Req: {$req->request_code}, Location: {$req->location}<br>";
        if ($req->device) {
            $output .= "  Device: {$req->device->equipment_id} / {$req->device->name}, Room ID field: {$req->device->room_id}<br>";
            if ($req->device->room) {
                $output .= "  Room: {$req->device->room->room_code} / {$req->device->room->room_name}<br>";
            } else {
                $output .= "  Room: NULL<br>";
            }
        } else {
            $output .= "  Device: NULL<br>";
        }
        $output .= "<hr>";
    }
    return $output;
});

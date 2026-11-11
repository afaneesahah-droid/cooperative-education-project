<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'like', '%วิรัต%')->orWhere('first_name', 'like', '%วิรัต%')->first();
if ($user) {
    echo "User ID: " . $user->id . "\n";
    echo "User Name: " . $user->name . " " . $user->first_name . " " . $user->last_name . "\n";
    echo "User Role: " . $user->role . "\n";
    
    $assignedRequests = \App\Models\RepairRequest::where('assigned_to', $user->id)->count();
    echo "Assigned Requests (by ID): " . $assignedRequests . "\n";
    
    $assignedByName = \App\Models\RepairRequest::where('assigned_to', 'like', '%วิรัต%')->count();
    echo "Assigned Requests (by Name): " . $assignedByName . "\n";
    
    $allTechnicians = \App\Models\User::where('role', 'like', '%technician%')->orWhere('role', 'like', '%ช่าง%')->get(['id', 'name', 'first_name', 'last_name', 'role']);
    echo "All Technicians:\n";
    foreach ($allTechnicians as $t) {
        echo " - " . $t->id . ": " . $t->name . " (" . $t->first_name . " " . $t->last_name . ") - " . $t->role . "\n";
    }
} else {
    echo "User not found\n";
}

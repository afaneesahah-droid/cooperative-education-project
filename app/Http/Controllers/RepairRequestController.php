<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\RepairRequest;
use App\Models\RepairItem;
use App\Models\RepairDetail;
use App\Models\Maintenance;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepairRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'device_id' => 'nullable|string',
            'building' => 'nullable|string',
            'floor' => 'nullable|string',
            'room' => 'nullable|string',
            'type' => 'required|string',
            'priority' => 'required|string',
            'description' => 'required|string',
            'budget' => 'nullable|numeric|min:0',
            'preferred_date' => 'nullable|date',
            'device_code' => 'nullable|string',
            'repair_project_id' => 'nullable|exists:repair_projects,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        if ($validator->fails()) {
            $errMsg = 'ข้อมูลไม่ครบถ้วนหรือไม่ถูกต้อง: ' . implode(', ', $validator->errors()->all());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }
            return redirect('/dashboard/teacher?tab=status')->with('error', $errMsg);
        }

        try {
            // จัดการโครงการและงบประมาณ (ถ้ามีการเลือกโครงการ)
            $repairProjectId = null;
            $budgetAmount = $request->filled('budget') ? (float)$request->budget : 0;
            $budgetApproved = false;

            if ($request->filled('repair_project_id')) {
                $project = \App\Models\RepairProject::find($request->repair_project_id);
                if ($project) {
                    $repairProjectId = $project->id;
                    if ($budgetAmount > 0) {
                        $remaining = (float)($project->budget - $project->used_budget);
                        if ($budgetAmount > $remaining) {
                            $errMsg = 'งบประมาณคงเหลือในโครงการ ' . $project->name . ' ไม่เพียงพอ (คงเหลือ ' . number_format($remaining, 2) . ' บาท)';
                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json(['success' => false, 'message' => $errMsg], 422);
                            }
                            return redirect()->back()->with('error', $errMsg);
                        }
                        // ตัดงบประมาณของโครงการทันที
                        $project->increment('used_budget', $budgetAmount);
                        $budgetApproved = true;
                    }
                }
            }

            // Generate Request ID: REQ01, REQ02, REQ03...
            $lastRequest = RepairRequest::orderByRaw('CAST(SUBSTRING(request_id, 4) AS UNSIGNED) DESC')->first();
            $nextNum = $lastRequest ? ((int)substr($lastRequest->request_id, 3)) + 1 : 1;
            $requestId = 'REQ' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
            while (RepairRequest::where('request_id', $requestId)->exists()) {
                $nextNum++;
                $requestId = 'REQ' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
            }

            // Generate Request Code
            $year = date('Y') + 543;
            $count = RepairRequest::whereYear('created_at', date('Y'))->count() + 1;
            $requestCode = 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            while (RepairRequest::where('request_code', $requestCode)->exists()) {
                $count++;
                $requestCode = 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $description = $request->description;
            $device = null;
            if ($request->filled('device_id')) {
                $device = Device::where('equipment_id', $request->device_id)->orWhere('device_code', $request->device_id)->first();
            }
            $equipmentId = $device ? $device->equipment_id : null;
            
            // ตรวจสอบเฉพาะอุปกรณ์ที่ไม่สามารถแจ้งซ่อมได้ (เช่น อุปกรณ์ที่ชำรุดจนซ่อมไม่ได้ / แทงจำหน่าย / ปลดระวาง)
            if ($device) {
                $isUnrepairable = in_array($device->status, ['ไม่สามารถซ่อมได้', 'ชำรุดซ่อมไม่ได้', 'แทงจำหน่าย', 'จำหน่าย']);
                
                // ตรวจสอบประวัติล่าสุดว่าประเมินเป็นไม่สามารถซ่อมได้หรือไม่
                if (!$isUnrepairable && $equipmentId) {
                    $lastUnrepair = RepairRequest::where('equipment_id', $equipmentId)
                        ->where('status', 'ไม่สามารถซ่อมได้')
                        ->latest('created_at')
                        ->first();
                    if ($lastUnrepair) {
                        $subsequentCompleted = RepairRequest::where('equipment_id', $equipmentId)
                            ->where('created_at', '>', $lastUnrepair->created_at)
                            ->whereIn('status', ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์'])
                            ->exists();
                        if (!$subsequentCompleted) {
                            $isUnrepairable = true;
                        }
                    }
                }

                if ($isUnrepairable) {
                    $errMsg = 'ไม่สามารถแจ้งซ่อมได้ เนื่องจากอุปกรณ์นี้ได้รับการประเมินว่าไม่สามารถซ่อมแซมได้ (ปลดระวาง/แทงจำหน่าย)';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 422);
                    }
                    return redirect('/dashboard/teacher?tab=status')->with('error', $errMsg);
                }
            }
            
            if ($request->filled('preferred_date')) {
                $formattedDate = date('d/m/Y', strtotime($request->preferred_date));
                $description .= "\n(วันที่สะดวกให้เข้าซ่อม: " . $formattedDate . ")";
            }
            if ($request->filled('device_code')) {
                $description .= "\n(รหัสอุปกรณ์: " . $request->device_code . ")";
            }
            if ($request->filled('contact_number')) {
                $description .= "\n(เบอร์โทรติดต่อ: " . $request->contact_number . ")";
            }

            if ($device) {
                // ดึงข้อมูลอัตโนมัติจากความสัมพันธ์ของ Device
                $bName = $device->building ? $device->building->building_name : '-';
                $fName = $device->floor ?: '-';
                $rName = '-';
                if ($device->room) {
                    $rName = $device->room->room_code ?: $device->room->room_name ?: '-';
                }
                
                $location = 'อาคาร ' . $bName . ' ชั้น ' . $fName . ' ห้อง ' . $rName;
                $saveBuilding = $device->building_id;
                $saveRoom = mb_substr($rName, 0, 3); // Max 3 chars based on schema
            } else {
                // ใช้ข้อมูลที่พิมพ์เองหรือตัวเลือกในกรณีที่ไม่มีในฐานข้อมูล
                if ($request->filled('device_id')) {
                    $devText = trim($request->device_id);
                    if (str_starts_with($devText, 'ไม่มีรหัส')) {
                        $description = "(ข้อมูลทรัพย์สิน: " . $devText . ")\n" . $description;
                    } else {
                        $description = "(อุปกรณ์/ข้อมูลที่ระบุ: " . $devText . ")\n" . $description;
                    }
                }
                
                $building = \App\Models\Building::find($request->building);
                $buildingName = $building ? $building->building_name : $request->building;
                $roomText = $request->room;
                if ($roomText === 'undefined' || empty($roomText) || $roomText === 'ไม่ระบุ') {
                    $roomText = '-';
                }
                
                $location = 'อาคาร ' . ($buildingName ?: '-') . ' ชั้น ' . ($request->floor ?: '-') . ' ห้อง ' . $roomText;
                $saveBuilding = $request->building;
                $saveRoom = mb_substr($roomText, 0, 3); // Max 3 chars based on schema
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'repair_' . $requestId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $uploadDir = public_path('uploads/repairs');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $file->move($uploadDir, $filename);
                $imagePath = 'uploads/repairs/' . $filename;
            }

            $repairRequest = RepairRequest::create([
                'request_id' => $requestId,
                'request_code' => $requestCode,
                'equipment_id' => $equipmentId,
                'user_id' => Auth::user() ? Auth::user()->user_id : Auth::id(),
                'type' => $request->type,
                'description' => $description,
                'image' => $imagePath,
                'budget' => $budgetAmount,
                'repair_project_id' => $repairProjectId,
                'budget_approved' => $budgetApproved,
                'priority' => $request->priority,
                'status' => 'รอมอบหมาย',
            ]);

            // Update Device Status
            if ($device) {
                $device->update(['status' => 'ชำรุด/ส่งซ่อมบำรุง']);
            }

            // Create RepairItem record
            try {
                $lastItem = RepairItem::orderByRaw('CAST(SUBSTRING(item_id, 4) AS UNSIGNED) DESC')->first();
                $nextItemNum = $lastItem ? ((int)substr($lastItem->item_id, 3)) + 1 : 1;
                $itemId = 'ITM' . str_pad($nextItemNum, 2, '0', STR_PAD_LEFT);
                while (RepairItem::where('item_id', $itemId)->exists()) {
                    $nextItemNum++;
                    $itemId = 'ITM' . str_pad($nextItemNum, 2, '0', STR_PAD_LEFT);
                }

                RepairItem::create([
                    'item_id' => $itemId,
                    'request_id' => $requestId,
                    'equipment_id' => $equipmentId,
                    'equipment_name' => $device ? $device->device_name : ($request->device_id ?? null),
                    'equipment_number' => $request->device_code ? substr($request->device_code, -2) : ($request->device_id ? substr($request->device_id, -2) : null),
                    'device_type' => $device ? $device->device_type : $request->type,
                    'building_id' => $saveBuilding,
                    'room_number' => $saveRoom,
                    'location' => $location,
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create repair_item: ' . $e->getMessage());
            }

            // Create RepairDetail record
            try {
                $lastDetail = RepairDetail::orderByRaw('CAST(SUBSTRING(repair_detail_id, 4) AS UNSIGNED) DESC')->first();
                $nextDetailNum = $lastDetail ? ((int)substr($lastDetail->repair_detail_id, 3)) + 1 : 1;
                $detailId = 'DTL' . str_pad($nextDetailNum, 2, '0', STR_PAD_LEFT);
                while (RepairDetail::where('repair_detail_id', $detailId)->exists()) {
                    $nextDetailNum++;
                    $detailId = 'DTL' . str_pad($nextDetailNum, 2, '0', STR_PAD_LEFT);
                }

                RepairDetail::create([
                    'repair_detail_id' => $detailId,
                    'request_id' => $requestId,
                    'description' => $description,
                    'request_description' => substr($request->description, 0, 255),
                    'preferred_date' => $request->preferred_date ? date('Y-m-d', strtotime($request->preferred_date)) : null,
                    'contact_number' => $request->contact_number ?? null,
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create repair_detail: ' . $e->getMessage());
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'บันทึกการซ่อมสำเร็จ ส่งมอบให้หัวหน้างานเรียบร้อย รหัสอ้างอิง: ' . $requestCode,
                    'request_code' => $requestCode,
                    'redirect' => url('/dashboard/teacher?tab=status')
                ]);
            }

            return redirect('/dashboard/teacher?tab=status')->with('success', 'บันทึกการซ่อมสำเร็จ ส่งมอบให้หัวหน้างานเรียบร้อย รหัสอ้างอิง: ' . $requestCode);
        } catch (\Throwable $e) {
            \Log::error('Exception in store repair request: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()
                ], 500);
            }
            return redirect('/dashboard/teacher?tab=status')->with('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $repairRequest = RepairRequest::findOrFail($id);
        
        $request->validate([
            'status' => 'required|string',
            'note' => 'nullable|string',
            'budget' => 'nullable|numeric',
            'inspection_result' => 'nullable|string',
            'repair_evaluation' => 'nullable|string',
            'repair_details' => 'nullable|string',
            'repair_result' => 'nullable|string',
            'unrepairable_reason' => 'nullable|string',
            'technician_recommendation' => 'nullable|string',
        ]);

        $repairRequest->update([
            'status' => $request->status ?? $repairRequest->status,
            'note' => $request->filled('note') ? $request->note : $repairRequest->note,
            'budget' => $request->budget ?? $repairRequest->budget,
        ]);

        // Dual-write to maintenances table
        try {
            $maintenance = Maintenance::where('request_id', $repairRequest->request_id)->first();
            if (!$maintenance) {
                $lastMnt = Maintenance::orderByRaw('CAST(SUBSTRING(maintenance_id, 4) AS UNSIGNED) DESC')->first();
                $nextMntNum = $lastMnt ? ((int)substr($lastMnt->maintenance_id, 3)) + 1 : 1;
                $mntId = 'MNT' . str_pad($nextMntNum, 2, '0', STR_PAD_LEFT);
                while (Maintenance::where('maintenance_id', $mntId)->exists()) {
                    $nextMntNum++;
                    $mntId = 'MNT' . str_pad($nextMntNum, 2, '0', STR_PAD_LEFT);
                }

                Maintenance::create([
                    'maintenance_id' => $mntId,
                    'request_id' => $repairRequest->request_id,
                    'user_id' => Auth::user() ? Auth::user()->user_id : $repairRequest->assigned_to,
                    'start_date' => $repairRequest->start_date ? date('Y-m-d', strtotime($repairRequest->start_date)) : null,
                    'accepted_at' => $repairRequest->accepted_at,
                    'inspection_result' => $request->inspection_result,
                    'repair_evaluation' => $request->repair_evaluation,
                    'repair_details' => $request->repair_details,
                    'repair_result' => $request->repair_result,
                    'unrepairable_reason' => $request->unrepairable_reason,
                    'checked_power' => $request->has('checked_power'),
                    'checked_cleaning' => $request->has('checked_cleaning'),
                    'checked_ping' => $request->has('checked_ping'),
                    'repair_hw' => $request->has('repair_hw'),
                    'repair_sw' => $request->has('repair_sw'),
                    'repair_net' => $request->has('repair_net'),
                ]);
            } else {
                $maintenance->update([
                    'inspection_result' => $request->inspection_result ?? $maintenance->inspection_result,
                    'repair_evaluation' => $request->repair_evaluation ?? $maintenance->repair_evaluation,
                    'repair_details' => $request->repair_details ?? $maintenance->repair_details,
                    'repair_result' => $request->repair_result ?? $maintenance->repair_result,
                    'unrepairable_reason' => $request->unrepairable_reason ?? $maintenance->unrepairable_reason,
                    'checked_power' => $request->has('checked_power'),
                    'checked_cleaning' => $request->has('checked_cleaning'),
                    'checked_ping' => $request->has('checked_ping'),
                    'repair_hw' => $request->has('repair_hw'),
                    'repair_sw' => $request->has('repair_sw'),
                    'repair_net' => $request->has('repair_net'),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to sync maintenance: ' . $e->getMessage());
        }

        // If completed or unfixable, release the device
        if (in_array($request->status, ['เสร็จสิ้น', 'ไม่สามารถซ่อมได้']) && $repairRequest->equipment_id) {
            $device = Device::find($repairRequest->equipment_id);
            if ($device) {
                $device->update(['status' => 'สามารถยืมใช้งานได้']);
            }
        }

        // If completed or unrepairable, redirect to technician dashboard history tab
        if ($request->status === 'เสร็จสิ้น') {
            return redirect()->route('dashboard.technician', ['tab' => 'history'])->with('success', 'บันทึกสถานะ "เสร็จสิ้น" สำเร็จ รายการงานซ่อมถูกย้ายไปอยู่ใน "ดูประวัติงานซ่อมย้อนหลัง" เรียบร้อยแล้ว');
        }

        return redirect()->back()->with('success', 'บันทึกการแก้ไขงานซ่อมสำเร็จ');
    }

    public function finish(Request $request, $id)
    {
        $repairRequest = RepairRequest::findOrFail($id);
        
        $request->validate([
            'note' => 'nullable|string',
        ]);

        $repairRequest->update([
            'status' => 'เสร็จสิ้น',
            'note' => $request->note,
        ]);

        // If completed, redirect to technician dashboard history tab
        return redirect()->route('dashboard.technician', ['tab' => 'history'])->with('success', 'บันทึกสถานะ "เสร็จสิ้น" สำเร็จ รายการงานซ่อมถูกย้ายไปอยู่ใน "ดูประวัติงานซ่อมย้อนหลัง" เรียบร้อยแล้ว');
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,user_id',
            'priority'    => 'required|string',
        ]);

        $repairRequest = RepairRequest::findOrFail($id);
        $repairRequest->update([
            'priority' => $request->priority,
            'status'   => 'รอรับงาน',
        ]);

        try {
            $existing = Assignment::where('request_id', $repairRequest->request_id)->first();
            if ($existing) {
                $existing->update([
                    'user_id'       => $request->assigned_to,
                    'assign_date'   => now(),
                    'task_priority' => $request->priority ?? 'normal',
                ]);
            } else {
                $maxNum = Assignment::selectRaw("MAX(CAST(SUBSTRING(assignment_id, 2) AS UNSIGNED)) as max_num")->value('max_num') ?: 0;
                $num = $maxNum + 1;
                $nextId = 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
                while (Assignment::where('assignment_id', $nextId)->exists()) {
                    $num++;
                    $nextId = 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
                }

                Assignment::create([
                    'assignment_id' => $nextId,
                    'request_id'    => $repairRequest->request_id,
                    'user_id'       => $request->assigned_to,
                    'assign_date'   => now(),
                    'task_priority' => $request->priority ?? 'normal',
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to create assignment on assign: ' . $e->getMessage());
        }

        return redirect()->route('dashboard.head', ['tab' => 'pending'])->with('success', 'มอบหมายงานให้เจ้าหน้าที่สำเร็จแล้ว');
    }

    public function accept(Request $request, $id)
    {
        $repairRequest = RepairRequest::findOrFail($id);

        // Only the assigned technician can accept
        if ($repairRequest->assigned_to !== Auth::id()) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์รับงานนี้');
        }

        $now = now();
        $repairRequest->update([
            'status' => 'กำลังซ่อม',
        ]);

        try {
            $maintenance = Maintenance::where('request_id', $repairRequest->request_id)->first();
            if ($maintenance) {
                $maintenance->update([
                    'accepted_at' => $now,
                    'start_date'  => $repairRequest->start_date ? date('Y-m-d', strtotime($repairRequest->start_date)) : now()->toDateString(),
                ]);
            } else {
                $lastMnt = Maintenance::orderByRaw('CAST(SUBSTRING(maintenance_id, 4) AS UNSIGNED) DESC')->first();
                $nextMntNum = $lastMnt ? ((int)substr($lastMnt->maintenance_id, 3)) + 1 : 1;
                $mntId = 'MNT' . str_pad($nextMntNum, 2, '0', STR_PAD_LEFT);
                while (Maintenance::where('maintenance_id', $mntId)->exists()) {
                    $nextMntNum++;
                    $mntId = 'MNT' . str_pad($nextMntNum, 2, '0', STR_PAD_LEFT);
                }

                Maintenance::create([
                    'maintenance_id' => $mntId,
                    'request_id'     => $repairRequest->request_id,
                    'user_id'        => Auth::user() ? Auth::user()->user_id : $repairRequest->assigned_to,
                    'start_date'     => $repairRequest->start_date ? date('Y-m-d', strtotime($repairRequest->start_date)) : now()->toDateString(),
                    'accepted_at'    => $now,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to update maintenance on accept: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'รับงานสำเร็จ เริ่มดำเนินการซ่อมได้เลย');
    }

    public function approveBudget(Request $request, $id)
    {
        $repairRequest = RepairRequest::findOrFail($id);
        $wasAlreadyApproved = $repairRequest->budget_approved;

        $repairRequest->update([
            'budget_approved' => true,
            'status' => 'กำลังซ่อม'
        ]);

        // หักยอดงบประมาณที่ใช้ไปจากโครงการที่เชื่อมอยู่ (ถ้ายังไม่เคยตัดยอดมาก่อน)
        if ($repairRequest->repair_project_id && !$wasAlreadyApproved) {
            $project = \App\Models\RepairProject::find($repairRequest->repair_project_id);
            if ($project) {
                $project->increment('used_budget', $repairRequest->budget ?? 0);
            }
        }

        return redirect()->back()->with('success', 'ลงนามอนุมัติจัดสรรงบประมาณสำเร็จแล้ว ใบงานถูกส่งกลับไปที่ช่างเพื่อดำเนินงานซ่อมต่อ');
    }

    public function requestBudget(Request $request, $id)
    {
        $repairRequest = RepairRequest::findOrFail($id);

        $updateData = [
            'status' => 'รออนุมัติงบประมาณ',
        ];

        // บันทึก project_id ถ้ามีการส่งมา
        if ($request->filled('project_id')) {
            $updateData['repair_project_id'] = $request->input('project_id');
        }

        // อัปเดตงบประมาณถ้ามีการส่งมา
        if ($request->filled('budget_amount_total') && $request->input('budget_amount_total') > 0) {
            $updateData['budget'] = $request->input('budget_amount_total');
        }

        $repairRequest->update($updateData);

        return redirect()->back()->with('success', 'ส่งเรื่องขออนุมัติเบิกจ่ายงบประมาณไปยัง ผอ. เรียบร้อยแล้ว');
    }

    public function rate(Request $request)
    {
        $request->validate([
            'request_id'   => 'required|exists:repair_requests,request_id',
            'rating'       => 'required|integer|min:1|max:5',
            'eval_comment' => 'nullable|string',
        ]);

        $repairRequest = RepairRequest::findOrFail($request->request_id);

        if ($repairRequest->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ประเมินใบงานนี้')->with('active_tab', 'tab-status');
        }

        if ($repairRequest->status !== 'เสร็จสิ้น') {
            return redirect()->back()->with('error', 'ไม่สามารถประเมินใบงานที่ยังซ่อมไม่เสร็จสิ้นได้')->with('active_tab', 'tab-status');
        }

        $repairRequest->update([
            'rating'       => $request->rating,
            'eval_comment' => $request->eval_comment,
        ]);

        return redirect()->back()->with('success', 'บันทึกการประเมินความพึงพอใจสำเร็จ ขอบคุณครับ')->with('active_tab', 'tab-status');
    }
    public function acknowledgeParts(Request $request, $id)
    {
        $repairRequest = RepairRequest::findOrFail($id);
        
        // Only the requester can acknowledge
        if ($repairRequest->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ดำเนินการในใบงานนี้');
        }

        $repairRequest->update([
            'status' => 'กำลังซ่อม'
        ]);

        return redirect()->back()->with('success', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ อัปเดตสถานะเป็น กำลังซ่อม แล้ว');
    }

    public function deductProjectBudget(Request $request)
    {
        $request->validate([
            'project_id'          => 'required|exists:repair_projects,id',
            'amount'              => 'required|numeric|min:0.01',
            'reason'              => 'nullable|string',
            'repair_request_code' => 'nullable|string',
        ]);

        $project = \App\Models\RepairProject::findOrFail($request->project_id);
        $amount = (float) $request->amount;

        $project->increment('used_budget', $amount);
        $project->refresh();

        // ตรวจสอบและอัปเดตใบแจ้งซ่อมต้นทาง (หากมีการเชื่อมโยงจากการบันทึกค่าซ่อม)
        $user = \Illuminate\Support\Facades\Auth::user();
        $linkedReq = null;
        $linkedCode = trim($request->input('repair_request_code', ''));
        if (!empty($linkedCode) && $linkedCode !== '—') {
            $linkedReq = RepairRequest::where('request_code', $linkedCode)
                ->orWhere('request_id', $linkedCode)
                ->first();
        }

        // กรณีไม่ได้ส่ง code มาตรงๆ ลองตรวจสอบจากข้อความเหตุผล หรือจากใบแจ้งซ่อมที่รออะไหล่ของผู้ใช้นี้
        if (!$linkedReq && $user) {
            $reason = trim($request->input('reason', ''));
            if (preg_match('/(REQ[-_]?\d{4}[-_]?\d{4}|REQ\d+)/i', $reason, $m)) {
                $codeCandidate = $m[1];
                $linkedReq = RepairRequest::where('request_code', $codeCandidate)
                    ->orWhere('request_id', $codeCandidate)
                    ->first();
            }

            if (!$linkedReq) {
                // ค้นหาใบแจ้งซ่อมล่าสุดของผู้ใช้นี้ที่สถานะเป็น 'รอเปลี่ยนอะไหล่' หรือ 'รออนุมัติงบประมาณ'
                $pendingJobs = RepairRequest::where('user_id', $user->user_id)
                    ->whereIn('status', ['รอเปลี่ยนอะไหล่', 'รออนุมัติงบประมาณ', 'รออะไหล่'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($pendingJobs->count() === 1) {
                    $linkedReq = $pendingJobs->first();
                }
            }
        }

        $createdRequest = null;
        $reason = trim($request->input('reason', ''));
        if ($linkedReq) {
            // อัปเดตใบแจ้งซ่อมจริงให้เป็น 'กำลังซ่อม' อย่างต่อเนื่อง ไม่สร้างเรคคอร์ดขยะซ้ำซ้อน
            $sourceTag = !empty($sourcePage) ? "[$sourcePage] " : '';
            $updateData = [
                'budget'            => $amount,
                'repair_project_id' => $project->id,
                'budget_approved'   => true,
                'status'            => 'กำลังซ่อม', // ย้ายจาก 'รอเปลี่ยนอะไหล่' เป็น 'กำลังซ่อม' หลังบันทึกค่าซ่อมเสร็จสิ้น
                'note'              => ($linkedReq->note ? $linkedReq->note . ' | ' : '') . $sourceTag . 'บันทึกค่าซ่อมแล้ว (' . number_format($amount, 2) . ' บาท)',
            ];
            if (!empty($reason)) {
                $updateData['repair_details'] = $reason;
            }
            $linkedReq->update($updateData);

            // ซิงค์ไปยังตาราง maintenances ด้วยเพื่อความสมบูรณ์ของประวัติ
            try {
                if (!empty($reason)) {
                    $mnt = Maintenance::where('request_id', $linkedReq->request_id)->first();
                    if ($mnt) {
                        $mnt->update(['repair_details' => $reason]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Maintenance sync in disburse: ' . $e->getMessage());
            }

            $linkedReq->refresh();
            $createdRequest = $linkedReq;
        } elseif ($user) {
            // กรณีเป็นการเบิกงบประมาณทั่วไปที่ไม่ได้ผูกกับใบแจ้งซ่อม สร้างใบเบิกประเภท 'เบิกงบประมาณ'
            $lastRequest = RepairRequest::orderByRaw('CAST(SUBSTRING(request_id, 4) AS UNSIGNED) DESC')->first();
            $nextNum = $lastRequest ? ((int)substr($lastRequest->request_id, 3)) + 1 : 1;
            $requestId = 'REQ' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
            while (RepairRequest::where('request_id', $requestId)->exists()) {
                $nextNum++;
                $requestId = 'REQ' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
            }

            $year = date('Y') + 543;
            $count = RepairRequest::whereYear('created_at', date('Y'))->count() + 1;
            $requestCode = 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            while (RepairRequest::where('request_code', $requestCode)->exists()) {
                $count++;
                $requestCode = 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $finalReason = $reason ?: ('เบิกค่าซ่อมแซม/วัสดุอุปกรณ์ โครงการ: ' . $project->name);

            $sourcePage = trim($request->input('source_page', ''));
            if (empty($sourcePage)) {
                $role = mb_strtolower($user->role ?? '', 'UTF-8');
                if (str_contains($role, 'technician') || str_contains($role, 'ช่าง')) {
                    $sourcePage = 'หน้าเจ้าหน้าที่ซ่อมบำรุง';
                } else {
                    $sourcePage = 'หน้าแจ้งซ่อม';
                }
            }

            $createdRequest = RepairRequest::create([
                'request_id' => $requestId,
                'request_code' => $requestCode,
                'user_id' => $user->user_id,
                'type' => 'เบิกงบประมาณ',
                'description' => $finalReason,
                'repair_details' => $finalReason,
                'budget' => $amount,
                'repair_project_id' => $project->id,
                'status' => 'อนุมัติแล้ว',
                'budget_approved' => true,
                'priority' => 'ปกติ',
                'note' => $sourcePage,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'บันทึกค่าซ่อมและตัดยอดงบประมาณสำเร็จ อัปเดตสถานะเป็น กำลังซ่อม แล้ว',
            'requisition' => $createdRequest,
            'linked_job' => $linkedReq ? [
                'request_id'   => $linkedReq->request_id,
                'request_code' => $linkedReq->request_code,
                'status'       => $linkedReq->status,
            ] : null,
            'project' => [
                'id'          => $project->id,
                'name'        => $project->name,
                'budget'      => (float)$project->budget,
                'used_budget' => (float)$project->used_budget,
                'remaining'   => (float)($project->budget - $project->used_budget),
            ]
        ]);
    }

    /**
     * Display printable repair cost record (ใบบันทึกค่าซ่อม)
     */
    public function printCost(Request $request, $id)
    {
        $repairRequest = RepairRequest::where('request_id', $id)
            ->orWhere('request_code', $id)
            ->with(['user', 'device', 'repairProject', 'assignee', 'latestAssignment.technician', 'items'])
            ->first();

        if (!$repairRequest) {
            // Construct a temporary model if record is accessed via code or dynamic parameters
            $repairRequest = new RepairRequest([
                'request_id'   => $id,
                'request_code' => $request->query('code', $id),
                'description'  => $request->query('reason', ''),
                'repair_details' => $request->query('items') ?: $request->query('reason', ''),
                'budget'       => (float)$request->query('amount', 0),
                'status'       => 'อนุมัติแล้ว',
                'budget_approved' => true,
            ]);
            if ($request->filled('project_name')) {
                $repairRequest->project_name = $request->query('project_name');
            }
            if ($request->filled('budget_source')) {
                $repairRequest->budget_source = $request->query('budget_source');
            }
            if ($request->filled('user_name')) {
                $tempUser = new \App\Models\User(['name' => $request->query('user_name')]);
                $repairRequest->setRelation('user', $tempUser);
            }
        } else {
            if ($request->filled('amount') && ((float)$repairRequest->budget <= 0)) {
                $repairRequest->budget = (float)$request->query('amount');
            }
            if ($request->filled('project_name') && empty($repairRequest->project_name) && !$repairRequest->repair_project_id) {
                $repairRequest->project_name = $request->query('project_name');
            }
            if ($request->filled('budget_source') && empty($repairRequest->budget_source)) {
                $repairRequest->budget_source = $request->query('budget_source');
            }
            if ($request->filled('items')) {
                $repairRequest->repair_details = $request->query('items');
            } elseif ($request->filled('reason') && (str_contains($request->query('reason'), '|') || preg_match('/@\s*฿/u', $request->query('reason'))) && (empty($repairRequest->repair_details) || !str_contains($repairRequest->repair_details, '|'))) {
                $repairRequest->repair_details = $request->query('reason');
            }
            if ($request->filled('items_json')) {
                $repairRequest->items_json = $request->query('items_json');
            }
        }

        return view('dashboards.print_cost', compact('repairRequest'));
    }
}


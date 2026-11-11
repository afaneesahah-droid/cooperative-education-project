<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    /**
     * ดึงข้อมูลทรัพย์สินทั้งหมด (สำหรับตาราง)
     */
    public function index(Request $request)
    {
        $query = Device::query();
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('device_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('equipment_name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial', 'like', "%{$search}%")
                  ->orWhere('responsible_person', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        if ($request->has('kind') && $request->kind !== 'all') {
            $query->where('type', $request->kind);
        }

        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where(function($q) {
                    $q->where('status', '!=', 'suspended')->orWhereNull('status');
                });
            } else if ($request->status === 'suspended') {
                $query->where('status', 'suspended');
            }
        }

        $devices = $query->orderBy('created_at', 'desc')->get();
        return response()->json($devices);
    }

    /**
     * บันทึกข้อมูลทรัพย์สินใหม่
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_code' => 'nullable|string|max:50',
            'serial' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        // Auto-generate equipment_id (E0001)
        $latestDevice = Device::orderBy('equipment_id', 'desc')->first();
        if ($latestDevice && preg_match('/^E(\d{4})$/', $latestDevice->equipment_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            $equipmentId = 'E' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $equipmentId = 'E0001';
        }

        // จัดการรหัสครุภัณฑ์ (ถ้าไม่ได้ระบุหรือเลือกสร้างอัตโนมัติ ให้ใช้ AST-E0001)
        $deviceCode = trim($request->device_code ?? '');
        if (empty($deviceCode) || $deviceCode === 'AUTO' || str_contains($deviceCode, 'อัตโนมัติ')) {
            $deviceCode = 'AST-' . $equipmentId;
        }

        // จัดการหมายเลขเครื่อง (ถ้าไม่ได้ระบุ ให้เป็น N/A)
        $serial = trim($request->serial ?? '');
        if (empty($serial)) {
            $serial = 'N/A';
        }

        // จัดการอัปโหลดรูปภาพอุปกรณ์
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'dev_' . $equipmentId . '_' . time() . '.' . $file->getClientOriginalExtension();
            $uploadDir = public_path('uploads/devices');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file->move($uploadDir, $filename);
            $imagePath = 'uploads/devices/' . $filename;
        }

        $device = Device::create([
            'equipment_id' => $equipmentId,
            'image' => $imagePath,
            'device_code' => $deviceCode,
            'serial' => $serial,
            'category' => $request->category,
            'type' => $request->type, // ชนิด
            'equipment_name' => $request->type, // Fallback name
            'name' => $request->type, // Fallback name
            'brand' => $request->brand,
            'model' => $request->model,
            'spec' => $request->specs,
            'supplier' => $request->supplier,
            'purchase_date' => $request->purchase_date,
            'price' => $request->price,
            'warranty' => $request->warranty,
            'building_id' => $request->building_id,
            'floor' => $request->floor,
            'room_id' => $request->room_id,
            'responsible_person' => $request->responsible_person,
            'version' => $request->version,
            'start_date' => $request->start_date,
            'installation_date' => $request->installation_date,
            'license_type' => $request->license_type,
            'expiration_date' => $request->expiration_date,
            'status' => $request->status ?? 'พร้อมใช้งาน',
        ]);

        return response()->json(['success' => true, 'device' => $device]);
    }

    /**
     * อัปเดตข้อมูลทรัพย์สิน
     */
    public function show($id)
    {
        $device = Device::where('equipment_id', $id)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลทรัพย์สิน'], 404);
        }
        return response()->json(['success' => true, 'data' => $device]);
    }

    public function update(Request $request, $id)
    {
        $device = Device::where('equipment_id', $id)->firstOrFail();

        $request->validate([
            'device_code' => 'nullable|string|max:50',
            'serial' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $pick = function ($keys) use ($request, $device) {
            if (!is_array($keys)) {
                $keys = [$keys];
            }
            foreach ($keys as $k) {
                if ($request->has($k)) {
                    $val = $request->input($k);
                    if ($val !== null && trim((string)$val) !== '') {
                        return is_string($val) ? trim($val) : $val;
                    }
                }
            }
            $primaryKey = $keys[0];
            return $device->$primaryKey ?? null;
        };

        $deviceCode = trim($request->device_code ?? '');
        if (empty($deviceCode) || $deviceCode === 'AUTO' || str_contains($deviceCode, 'อัตโนมัติ')) {
            $deviceCode = $device->device_code ?: ('AST-' . $device->equipment_id);
        }

        $serial = trim($request->serial ?? '');
        if (empty($serial)) {
            $serial = $device->serial ?: 'N/A';
        }

        $type = $pick(['type', 'name']) ?: $device->type;

        // จัดการอัปโหลดหรือลบรูปภาพอุปกรณ์
        $imagePath = $device->image;
        if ($request->hasFile('image')) {
            if ($device->image && file_exists(public_path($device->image))) {
                @unlink(public_path($device->image));
            }
            $file = $request->file('image');
            $filename = 'dev_' . $device->equipment_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $uploadDir = public_path('uploads/devices');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file->move($uploadDir, $filename);
            $imagePath = 'uploads/devices/' . $filename;
        } elseif ($request->has('remove_image') && $request->remove_image == '1') {
            if ($device->image && file_exists(public_path($device->image))) {
                @unlink(public_path($device->image));
            }
            $imagePath = null;
        }

        $device->update([
            'image' => $imagePath,
            'device_code' => $deviceCode,
            'serial' => $serial,
            'category' => $pick(['category']) ?: $device->category,
            'type' => $type,
            'equipment_name' => $type ?: ($device->equipment_name ?? $device->type),
            'name' => $type ?: ($device->name ?? $device->type),
            'brand' => $pick(['brand']) ?: $device->brand,
            'model' => $pick(['model']) ?: $device->model,
            'spec' => $pick(['specs', 'spec']),
            'supplier' => $pick(['supplier']),
            'purchase_date' => $pick(['purchase_date']),
            'price' => $pick(['price']),
            'warranty' => $pick(['warranty']),
            'building_id' => $pick(['building_id']),
            'floor' => $pick(['floor']),
            'room_id' => $pick(['room_id']),
            'responsible_person' => $pick(['responsible_person']),
            'version' => $pick(['version']),
            'start_date' => $pick(['start_date']),
            'installation_date' => $pick(['installation_date']),
            'license_type' => $pick(['license_type']),
            'expiration_date' => $pick(['expiration_date']),
            'status' => $pick(['status']) ?: ($device->status ?: 'พร้อมใช้งาน'),
        ]);

        return response()->json(['success' => true, 'device' => $device]);
    }

    
    /**
     * เปลี่ยนสถานะทรัพย์สิน
     */
    public function changeStatus(Request $request, $id)
    {
        $device = Device::where('equipment_id', $id)->firstOrFail();
        
        $request->validate([
            'status' => 'required|string|max:100',
        ]);
        
        $device->update([
            'status' => $request->status
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * ลบข้อมูลทรัพย์สิน
     */
    public function destroy($id)
    {
        $device = Device::where('equipment_id', $id)->firstOrFail();
        $device->delete();

        return response()->json(['success' => true]);
    }
}

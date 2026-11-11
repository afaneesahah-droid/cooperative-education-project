<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceType;
use App\Models\RepairType;
use App\Models\PersonnelType;
use App\Models\AssetKind;

class BasicDataController extends Controller
{
    // --- Device Types ---
    public function storeDeviceType(Request $request)
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:50',
            'asset_code' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'purchase_date' => 'nullable|date',
        ]);

        $deviceType = DeviceType::create($validated);
        return response()->json(['success' => true, 'data' => $deviceType]);
    }

    public function updateDeviceType(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:50',
            'asset_code' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'details' => 'nullable|string',
            'purchase_date' => 'nullable|date',
        ]);

        $deviceType = DeviceType::findOrFail($id);
        $deviceType->update($validated);
        return response()->json(['success' => true, 'data' => $deviceType]);
    }

    public function destroyDeviceType($id)
    {
        $deviceType = DeviceType::find($id);
        if ($deviceType) {
            $deviceType->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    // --- Repair Types ---
    public function storeRepairType(Request $request)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('repair_types')) {
            return response()->json(['success' => false, 'message' => 'Table repair_types removed'], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|max:50',
        ]);

        $repairType = RepairType::create($validated);
        return response()->json(['success' => true, 'data' => $repairType]);
    }

    public function destroyRepairType($id)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('repair_types')) {
            return response()->json(['success' => true]);
        }

        $repairType = RepairType::find($id);
        if ($repairType) {
            $repairType->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    // --- Personnel Types ---
    public function storePersonnelType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (\App\Models\PersonnelType::where('name', $validated['name'])->exists()) {
            return response()->json(['success' => false, 'message' => 'มีประเภทบุคลากรนี้อยู่ในระบบแล้ว'], 422);
        }

        $personnelType = PersonnelType::create($validated);
        return response()->json(['success' => true, 'data' => $personnelType]);
    }

    public function updatePersonnelType(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (\App\Models\PersonnelType::where('name', $validated['name'])->where('id', '!=', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'มีประเภทบุคลากรนี้อยู่ในระบบแล้ว'], 422);
        }

        $personnelType = PersonnelType::findOrFail($id);
        $personnelType->update($validated);
        return response()->json(['success' => true, 'data' => $personnelType]);
    }

    public function destroyPersonnelType($id)
    {
        $personnelType = PersonnelType::find($id);
        if ($personnelType) {
            $personnelType->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    // --- Asset Categories (ประเภททรัพย์สิน) ---
    public function storeAssetCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $name = $validated['name'];

        // ตรวจสอบว่ามี category_code นี้อยู่แล้วหรือไม่
        $exists = \App\Models\AssetKind::where('category_code', $name)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'มีประเภททรัพย์สินนี้อยู่แล้ว'], 422);
        }

        // บันทึกลงฐานข้อมูล asset_kinds เพื่อให้ปรากฏทั้งประเภทและชนิดทรัพย์สินใน phpMyAdmin
        $kind = \App\Models\AssetKind::create([
            'category_code' => $name,
            'name'          => $name,
        ]);

        return response()->json(['success' => true, 'data' => $kind]);
    }

    // --- Asset Kinds ---
    public function storeAssetKind(Request $request)
    {
        $validated = $request->validate([
            'category_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kind = AssetKind::create($validated);

        // ซิงค์บันทึกลงตาราง device_types ด้วยเพื่อรองรับทุกฟังก์ชันในระบบ
        \App\Models\DeviceType::firstOrCreate(
            ['name' => $validated['name']],
            ['category' => $validated['category_code']]
        );

        return response()->json(['success' => true, 'data' => $kind]);
    }

    public function updateAssetKind(Request $request, $id)
    {
        $validated = $request->validate([
            'category_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kind = AssetKind::findOrFail($id);
        $kind->update($validated);
        return response()->json(['success' => true, 'data' => $kind]);
    }

    public function destroyAssetKind($id)
    {
        $kind = AssetKind::find($id);
        if ($kind) {
            $kind->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    public function renameAssetCategory(Request $request)
    {
        $validated = $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:50',
        ]);

        $oldName = $validated['old_name'];
        $newName = $validated['new_name'];

        // Update in AssetKinds
        \App\Models\AssetKind::where('category_code', $oldName)->update(['category_code' => $newName]);
        
        // Update in DeviceTypes
        \App\Models\DeviceType::where('category', $oldName)->update(['category' => $newName]);

        return response()->json(['success' => true]);
    }

    public function destroyAssetCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $name = $validated['name'];

        // Delete from AssetKinds
        \App\Models\AssetKind::where('category_code', $name)->delete();
        
        // Delete from DeviceTypes
        \App\Models\DeviceType::where('category', $name)->delete();

        return response()->json(['success' => true]);
    }

    public function renameAssetKindByName(Request $request)
    {
        $validated = $request->validate([
            'category_code' => 'required|string',
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:255',
        ]);

        $catCode = $validated['category_code'];
        $oldName = $validated['old_name'];
        $newName = $validated['new_name'];

        \App\Models\AssetKind::where('category_code', $catCode)
            ->where('name', $oldName)
            ->update(['name' => $newName]);

        \App\Models\DeviceType::where('category', $catCode)
            ->where('name', $oldName)
            ->update(['name' => $newName]);

        return response()->json(['success' => true]);
    }

    public function destroyAssetKindByName(Request $request)
    {
        $validated = $request->validate([
            'category_code' => 'required|string',
            'name' => 'required|string',
        ]);

        $catCode = $validated['category_code'];
        $name = $validated['name'];

        \App\Models\AssetKind::where('category_code', $catCode)
            ->where('name', $name)
            ->delete();

        \App\Models\DeviceType::where('category', $catCode)
            ->where('name', $name)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|string|max:5',
            'room_code' => 'required|string|max:50',
            'room_name' => 'required|string|max:255',
            'floor' => 'required|integer',
        ]);

        $room = \App\Models\Room::create($validated);
        return response()->json(['success' => true, 'data' => $room]);
    }

    public function updateRoom(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|string|max:5',
            'old_room_code' => 'required|string|max:50',
            'room_code' => 'required|string|max:50',
            'room_name' => 'required|string|max:255',
            'floor' => 'required|integer',
        ]);

        $room = \App\Models\Room::where('building_id', $validated['building_id'])
                                ->where('room_code', $validated['old_room_code'])
                                ->first();

        if ($room) {
            $room->update([
                'room_code' => $validated['room_code'],
                'room_name' => $validated['room_name'],
                'floor' => $validated['floor']
            ]);
            return response()->json(['success' => true, 'data' => $room]);
        }
        return response()->json(['success' => false, 'message' => 'Room not found'], 404);
    }

    public function destroyRoom(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|string|max:5',
            'room_code' => 'required|string|max:50',
        ]);

        $room = \App\Models\Room::where('building_id', $validated['building_id'])
                                ->where('room_code', $validated['room_code'])
                                ->first();
        if ($room) {
            $room->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Room not found'], 404);
    }
}

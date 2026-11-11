<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * อัปเดตข้อมูลส่วนตัวของผู้ใช้
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // ตรวจจับไฟล์อัปโหลดล้มเหลว หรือใหญ่เกินลิมิต php.ini (2MB บนเซิร์ฟเวอร์นักศึกษา)
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['profile_photo']['error'];
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                $errorMsg = 'อัปโหลดไฟล์ไม่สำเร็จ (รหัสความผิดพลาด: ' . $errorCode . ')';
                if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
                    $errorMsg = 'ไฟล์รูปภาพของคุณมีขนาดใหญ่เกินกว่าที่เซิร์ฟเวอร์กำหนด';
                }
                return back()->withErrors(['profile_photo' => $errorMsg])
                    ->withInput()
                    ->with('active_tab', 'tab-profile');
            }
        }

        if (!$request->filled('email')) {
            $request->merge(['email' => $user->email]);
        }

        $request->validate([
            'prefix'                => 'nullable|string|max:30',
            'first_name'            => 'nullable|string|max:255',
            'last_name'             => 'nullable|string|max:255',
            'first_name_en'         => 'nullable|string|max:255',
            'last_name_en'          => 'nullable|string|max:255',
            'email'                 => 'required|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'phone'                 => 'nullable|string|regex:/^[0-9]{3}-?[0-9]{3}-?[0-9]{4}$/',
            'gender'                => 'nullable|string|in:ชาย,หญิง',
            'department'            => 'nullable|string|max:255',
            'position'              => 'nullable|string|max:255',
            'profile_photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'current_password'      => 'nullable|string',
            'new_password'          => 'nullable|string|min:8|confirmed',
        ], [
            'email.required'         => 'กรุณากรอกอีเมล',
            'email.email'            => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique'           => 'อีเมลนี้ถูกใช้งานแล้ว',
            'phone.regex'            => 'เบอร์โทรศัพท์ต้องอยู่ในรูปแบบ 088-253-1483 หรือตัวเลข 10 หลัก',
            'gender.in'              => 'เพศที่เลือกไม่ถูกต้อง',
            'profile_photo.image'    => 'ไฟล์ต้องเป็นรูปภาพเท่านั้น',
            'profile_photo.mimes'    => 'รองรับเฉพาะไฟล์ jpg, jpeg, png, webp',
            'new_password.min'       => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร',
            'new_password.confirmed' => 'รหัสผ่านใหม่ไม่ตรงกัน',
        ]);

        // ตรวจสอบรหัสผ่านปัจจุบัน ถ้าต้องการเปลี่ยน
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'])
                    ->withInput()
                    ->with('active_tab', 'tab-profile');
            }
            $user->password = Hash::make($request->new_password);
            $user->plain_password = $request->new_password;
        }

        // จัดการ upload รูปโปรไฟล์
        if ($request->hasFile('profile_photo')) {
            // ลบรูปเก่า (ถ้ามี)
            if ($user->profile_photo && file_exists(public_path('uploads/profiles/' . $user->profile_photo))) {
                @unlink(public_path('uploads/profiles/' . $user->profile_photo));
            }

            $file     = $request->file('profile_photo');
            $filename = 'user_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!is_dir(public_path('uploads/profiles'))) {
                mkdir(public_path('uploads/profiles'), 0755, true);
            }

            $file->move(public_path('uploads/profiles'), $filename);
            $user->profile_photo = $filename;
        }

        // เก็บข้อมูลเก่าไว้เสมอ: อัปเดตเฉพาะฟิลด์ที่มีการส่งค่าใหม่ที่ไม่ว่างมาเท่านั้น
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('phone')) {
            $cleanPhone = substr(str_replace('-', '', $request->phone), 0, 10);
            if (!empty($cleanPhone)) {
                $user->phone_number = $cleanPhone;
            }
        }
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
        if ($request->filled('citizen_id')) {
            $user->citizen_id = $request->citizen_id;
        }
        if ($request->filled('gender')) {
            $user->gender = $request->gender;
        }
        if ($request->filled('department')) {
            $user->department = $request->department;
        }
        if ($request->filled('position')) {
            $user->position = $request->position;
        }

        // คำนวณชื่อเต็มใหม่เฉพาะเมื่อมีการแก้ไขชื่อ นามสกุล หรือคำนำหน้าอย่างแท้จริงเท่านั้น
        if ($request->filled('first_name') || $request->filled('last_name') || $request->filled('prefix')) {
            $computedName = trim(($user->prefix ? $user->prefix . ' ' : '') . ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            if (!empty($computedName)) {
                $user->name = $computedName;
            }
        }

        $user->save();

        return back()
            ->with('profile_success', 'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว')
            ->with('active_tab', 'tab-profile');
    }
}

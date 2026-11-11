<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }


    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'กรุณากรอกชื่อผู้ใช้ (Username)',
            'password.required' => 'กรุณากรอกรหัสผ่าน (Password)',
        ]);

        $loginInput = $request->input('username');
        $loginField = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_id';

        $credentials = [
            $loginField => $loginInput,
            'password' => $request->password,
        ];

        // ตรวจสอบให้แน่ใจว่าค่าถูกส่งผ่านระเบียบวิธีของ Eloquent แบบปกติ
        $user = \App\Models\User::where($loginField, '=', $loginInput)->first();

        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['username' => ['ไม่พบบัญชีของคุณ']]
                ], 422);
            }
            return back()->withErrors([
                'username' => 'ไม่พบบัญชีของคุณ',
            ])->withInput();
        }

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $authUser = Auth::user();

            // ตรวจสถานะบัญชี
            if ($authUser->isPending()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => ['status' => ['บัญชีของคุณยังรอการอนุมัติจากแอดมิน กรุณารอการแจ้งเตือน']]
                    ], 422);
                }
                return back()->withErrors([
                    'status' => 'บัญชีของคุณยังรอการอนุมัติจากแอดมิน กรุณารอการแจ้งเตือน',
                ])->withInput();
            }

            if ($authUser->isSuspended()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => ['status' => ['บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ']]
                    ], 422);
                }
                return back()->withErrors([
                    'status' => 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
                ])->withInput();
            }

            if ($authUser->isRejected()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => ['status' => ['บัญชีของคุณถูกระงับ']]
                    ], 422);
                }
                return back()->withErrors([
                    'status' => 'บัญชีของคุณถูกระงับ',
                ])->withInput();
            }

            $roles = array_map('trim', explode(',', $authUser->role ?? ''));
            $hasSpecialRole = count(array_intersect(['admin', 'executive', 'head', 'technician'], $roles)) > 0;
            
            if ($hasSpecialRole) {
                return redirect()->route('role.select');
            }
            
            return redirect()->intended('/dashboard/teacher');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['password' => ['รหัสผ่านไม่ถูกต้อง']]
            ], 422);
        }

        return back()->withErrors([
            'password' => 'รหัสผ่านไม่ถูกต้อง',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'กรุณากรอกอีเมลในรูปแบบที่ถูกต้อง',
        ]);

        $email = $request->input('email');
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบบัญชีที่ลงทะเบียนไว้'
            ], 404);
        }

        if ($user->status === 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'บัญชีของคุณยังอยู่ระหว่างรอการอนุมัติ ไม่สามารถกู้คืนรหัสผ่านได้'
            ], 400);
        }

        if ($user->status === 'rejected') {
            return response()->json([
                'status' => 'error',
                'message' => 'บัญชีของคุณถูกระงับ'
            ], 400);
        }

        // Generate 6-digit OTP
        $otp = sprintf('%06d', rand(0, 999999));
        $expiresAt = now()->addMinutes(10);

        // Store in Session
        session([
            'reset_email' => $email,
            'reset_otp' => $otp,
            'reset_otp_expires_at' => $expiresAt,
            'reset_otp_verified' => false,
        ]);

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::send('emails.otp', ['otp' => $otp], function($message) use ($email) {
                $message->to($email)
                        ->subject('รหัสความปลอดภัย (OTP) สำหรับกู้คืนรหัสผ่าน - YTC Repair System');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ส่งอีเมล OTP ล้มเหลว: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการส่งอีเมล กรุณาลองใหม่อีกครั้ง'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'ส่งรหัส OTP ไปยังอีเมลของท่านเรียบร้อยแล้ว'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'กรุณากรอกรหัส OTP',
            'otp.size' => 'รหัส OTP ต้องมีความยาว 6 หลัก',
        ]);

        $otp = $request->input('otp');
        $sessionOtp = session('reset_otp');
        $sessionEmail = session('reset_email');
        $expiresAt = session('reset_otp_expires_at');

        if (!$sessionOtp || !$sessionEmail || !$expiresAt) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลการขอรหัส OTP หรือหมดเวลาทำรายการ กรุณากดส่งรหัสใหม่อีกครั้ง'
            ], 400);
        }

        if (now()->greaterThan($expiresAt)) {
            return response()->json([
                'status' => 'error',
                'message' => 'รหัส OTP หมดอายุแล้ว กรุณากดส่งรหัสใหม่อีกครั้ง'
            ], 400);
        }

        if ($otp !== $sessionOtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'รหัส OTP ไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง'
            ], 400);
        }

        // Set verification flag
        session(['reset_otp_verified' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'ยืนยันรหัส OTP สำเร็จ'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ], [
            'password.required' => 'กรุณากรอกรหัสผ่านใหม่',
            'password.min' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'การยืนยันรหัสผ่านไม่ตรงกัน',
            'password.regex' => 'รหัสผ่านใหม่ต้องประกอบด้วยตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลขอย่างน้อย 1 ตัว',
        ]);

        $sessionEmail = session('reset_email');
        $verified = session('reset_otp_verified');

        if (!$sessionEmail || !$verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถดำเนินการได้ เนื่องจากยังไม่ผ่านขั้นตอนการยืนยัน OTP'
            ], 403);
        }

        $user = \App\Models\User::where('email', $sessionEmail)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่พบบัญชีผู้ใช้งานที่ต้องการรีเซ็ต'
            ], 404);
        }

        // Update password
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        // Clear session
        session()->forget(['reset_email', 'reset_otp', 'reset_otp_expires_at', 'reset_otp_verified']);

        return response()->json([
            'status' => 'success',
            'message' => 'เปลี่ยนรหัสผ่านสำเร็จแล้ว'
        ]);
    }
}

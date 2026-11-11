<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #DBEAFE;
            padding: 16px;
            overflow-x: hidden;
        }

        /* ==================== WRAPPER ==================== */
        .login-wrapper {
            display: flex;
            width: 900px;
            max-width: 100%;
            min-height: 560px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(109,40,217,0.18);
        }

        /* ==================== LEFT PANEL ==================== */
        .left-panel {
            flex: 0 0 42%;
            background: linear-gradient(160deg, #1E3A8A 0%, #2563EB 45%, #60A5FA 75%, #DBEAFE 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 36px;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -90px;
            left: -90px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -110px;
            right: -90px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .logo-wrap {
            position: relative;
            z-index: 1;
            margin-bottom: 18px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(4px);
        }

        .logo-wrap img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2));
        }

        .system-title {
            position: relative;
            z-index: 1;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            text-align: center;
            line-height: 1.65;
            margin-bottom: 0;
        }

        .system-title strong {
            display: block;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .welcome-box {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.14);
            border-radius: 14px;
            padding: 16px 24px;
            text-align: center;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .welcome-box p {
            color: #fff;
            font-size: 0.83rem;
            font-weight: 500;
            line-height: 1.75;
        }

        /* ==================== RIGHT PANEL ==================== */
        .right-panel {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 36px 48px 36px;
        }

        /* แถบภาษา TH | EN */
        .lang-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            margin-bottom: 30px;
        }

        .lang-bar a {
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s;
        }

        .lang-bar a.active  { color: #7c3aed; }
        .lang-bar a.inactive{ color: #bbb; font-weight: 500; }
        .lang-bar a:hover   { color: #5b21b6; }
        .lang-bar span      { color: #ddd; font-size: 0.9rem; }

        /* หัวเรื่อง */
        .form-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .form-subtitle {
            font-size: 0.82rem;
            color: #9ca3af;
            font-weight: 400;
            margin-bottom: 28px;
        }

        /* Error Banner */
        .error-banner {
            background: #DBEAFE;
            border-left: 4px solid #e11d48;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #be123c;
        }

        /* Field Label */
        .field-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        /* Input Group */
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.88rem;
            pointer-events: none;
        }

        .input-group input {
            width: 100%;
            padding: 13px 14px 13px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Prompt', sans-serif;
            font-size: 0.88rem;
            color: #374151;
            background: #f9fafb;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        .input-group input:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
        }

        .input-group input.input-error {
            border-color: #ef4444 !important;
            background: #fff5f5;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.15);
        }

        .input-group.has-error {
            margin-bottom: 6px;
        }

        .field-error-msg {
            display: none;
            color: #ef4444;
            font-size: 0.78rem;
            margin-top: 4px;
            margin-bottom: 12px;
            padding-left: 2px;
        }

        .input-group input::placeholder { color: #c4c9d4; }

        /* Toggle password */
        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 0.88rem;
            padding: 0;
            transition: color 0.2s;
        }

        .toggle-pass:hover { color: #6b7280; }

        /* ปุ่ม Login */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Prompt', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 18px rgba(124,58,237,0.35);
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124,58,237,0.45);
        }

        .btn-login:active { transform: translateY(0); }

        /* ลิงก์ด้านล่าง */
        .bottom-links {
            text-align: center;
            margin-top: 22px;
        }

        .bottom-links p {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .bottom-links p a {
            color: #7c3aed;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .bottom-links p a:hover {
            color: #5b21b6;
            text-decoration: underline;
        }

        .back-home-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-home-link:hover { color: #6b7280; }

        /* ==================== SUCCESS OVERLAY ==================== */
        #success-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,10,40,0.75);
            backdrop-filter: blur(6px);
            z-index: 50;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        #success-overlay.show { display: flex; }

        .success-card {
            background: #DBEAFE;
            border-radius: 28px;
            padding: 40px 36px;
            max-width: 360px;
            width: 100%;
            text-align: center;
            border: 3px solid #10b981;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .success-icon {
            display: inline-flex;
            background: #DBEAFE;
            color: #10b981;
            padding: 20px;
            border-radius: 50%;
            margin-bottom: 16px;
        }

        .success-icon i { font-size: 3rem; }

        .success-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .success-card p {
            font-size: 0.82rem;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top-color: #7c3aed;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 860px) {
            .login-wrapper {
                flex-direction: column;
                border-radius: 20px;
                min-height: unset;
                width: 100%;
                max-width: 480px;
            }

            .left-panel {
                flex: none;
                padding: 32px 20px;
            }

            .right-panel {
                padding: 32px 24px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .login-wrapper {
                border-radius: 16px;
            }

            .left-panel {
                padding: 24px 16px;
            }

            .logo-wrap {
                width: 100px;
                height: 100px;
                margin-bottom: 12px;
            }

            .logo-wrap img {
                width: 75px;
                height: 75px;
            }

            .system-title {
                font-size: 0.85rem;
            }

            .system-title strong {
                font-size: 0.95rem;
            }

            .right-panel {
                padding: 24px 18px;
            }

            .form-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">

        <!-- ===== LEFT PANEL ===== -->
        <div class="left-panel">
            <div class="logo-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="YTC Logo">
            </div>

            <div class="system-title">
                <strong>ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</strong>
            </div>
        </div>

        <!-- ===== RIGHT PANEL ===== -->
        <div class="right-panel">

            <!-- หัวเรื่อง -->
            <h1 class="form-title">เข้าสู่ระบบ</h1>

            @error('status')
            <div class="error-banner">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $message }}</span>
            </div>
            @enderror

            <!-- ฟอร์มล็อกอิน -->
            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <!-- รหัสผู้ใช้ -->
                <label class="field-label" for="username-input">ชื่อผู้ใช้ (Username) <span style="color: #ef4444;">*</span></label>
                <div class="input-group @error('username') has-error @enderror">
                    <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                    <input
                        type="text"
                        id="username-input"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        placeholder="รหัสผู้ใช้"
                        autocomplete="username"
                        class="@error('username') input-error @enderror"
                    >
                </div>
                <small id="username-error-msg" class="field-error-msg" style="@error('username') display: block; @enderror">
                    @error('username')
                        <i class="fa-solid fa-circle-exclamation" style="margin-right: 4px;"></i>{{ $message }}
                    @enderror
                </small>

                <!-- รหัสผ่าน -->
                <label class="field-label" for="password-input">รหัสผ่าน (Password) <span style="color: #ef4444;">*</span></label>
                <div class="input-group @error('password') has-error @enderror">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input
                        type="password"
                        id="password-input"
                        name="password"
                        value="{{ old('password') }}"
                        required
                        placeholder="รหัสผ่าน"
                        autocomplete="current-password"
                        oninput="this.value = this.value.replace(/[\u0e00-\u0e7f]/g, '')"
                        class="@error('password') input-error @enderror"
                    >
                    <button type="button" class="toggle-pass" onclick="togglePasswordVisibility()" aria-label="แสดง/ซ่อนรหัสผ่าน">
                        <i class="fa-solid fa-eye" id="password-toggle-icon"></i>
                    </button>
                </div>
                <small id="password-error-msg" class="field-error-msg" style="@error('password') display: block; @enderror">
                    @error('password')
                        <i class="fa-solid fa-circle-exclamation" style="margin-right: 4px;"></i>{{ $message }}
                    @enderror
                </small>

                <!-- จดจำรหัสผ่าน และ ลืมรหัสผ่าน -->
                <div class="flex items-center justify-between w-full mb-4" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember-me" name="remember" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer mr-2">
                        <label for="remember-me" class="text-xs font-bold text-slate-500 cursor-pointer select-none">จดจำรหัสผ่าน</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-red-600 hover:text-red-700 transition hover:underline">ลืมรหัสผ่าน</a>
                </div>

                <!-- ปุ่มล็อกอิน -->
                <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
            </form>


        </div>
    </div>

    <!-- Success Overlay -->
    <div id="success-overlay">
        <div class="success-card">
            <div class="success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3>ยืนยันสิทธิ์เข้าสู่ระบบสำเร็จ!</h3>
            <p id="success-status-text">กำลังเตรียมการนำเข้าข้อมูลระบบแจ้งซ่อม...</p>
            <div class="spinner"></div>
        </div>
    </div>

    <script>
        // แสดง/ซ่อน รหัสผ่าน
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password-input');
            const icon = document.getElementById('password-toggle-icon');
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.className = "fa-solid fa-eye-slash";
            } else {
                passwordInput.type = "password";
                icon.className = "fa-solid fa-eye";
            }
        }



        function showExternalRegistration() {
            alert("ระบบแจ้งซ่อม: กำลังเปิดหน้าต่างสำหรับบุคคลภายนอกเพื่อยื่นคำขอซ่อมบำรุงวิทยาลัยเทคนิคยะลา");
        }

        // ระบบจดจำรหัสผ่านและกรอกข้อมูลอัตโนมัติ
        window.addEventListener('DOMContentLoaded', () => {
            const rememberMeCheckbox = document.getElementById('remember-me');
            const usernameInput = document.getElementById('username-input');
            const passwordInput = document.getElementById('password-input');

            // โหลดข้อมูลที่จำไว้ถ้ามี
            if (localStorage.getItem('remember_me') === 'true') {
                rememberMeCheckbox.checked = true;
                usernameInput.value = localStorage.getItem('saved_username') || '';
                passwordInput.value = localStorage.getItem('saved_password') || '';
            }

             // ตรวจสอบข้อมูลก่อนส่งฟอร์ม
             const form = document.querySelector('form');
             const passwordErrMsg = document.getElementById('password-error-msg');
             const usernameErrMsg = document.getElementById('username-error-msg');

             form.addEventListener('submit', (e) => {
                 const usernameVal = usernameInput.value.trim();
                 const passwordVal = passwordInput.value.trim();

                 // Clear errors
                 usernameInput.classList.remove('input-error');
                 usernameInput.closest('.input-group').classList.remove('has-error');
                 if (usernameErrMsg) usernameErrMsg.style.display = 'none';
                 
                 passwordInput.classList.remove('input-error');
                 passwordInput.closest('.input-group').classList.remove('has-error');
                 if (passwordErrMsg) passwordErrMsg.style.display = 'none';

                 let errorMsg = '';
                 let focusTarget = null;

                 if (usernameVal === '') {
                     usernameInput.classList.add('input-error');
                     usernameInput.closest('.input-group').classList.add('has-error');
                     if (usernameErrMsg) {
                         usernameErrMsg.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="margin-right: 4px;"></i>กรุณากรอกชื่อผู้ใช้ (Username)';
                         usernameErrMsg.style.display = 'block';
                     }
                     errorMsg = 'กรุณากรอกชื่อผู้ใช้ (Username)';
                     focusTarget = usernameInput;
                 } else if (passwordVal === '') {
                     passwordInput.classList.add('input-error');
                     passwordInput.closest('.input-group').classList.add('has-error');
                     if (passwordErrMsg) {
                         passwordErrMsg.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="margin-right: 4px;"></i>กรุณากรอกรหัสผ่าน (Password)';
                         passwordErrMsg.style.display = 'block';
                     }
                     errorMsg = 'กรุณากรอกรหัสผ่าน (Password)';
                     focusTarget = passwordInput;
                 }

                 if (errorMsg) {
                     e.preventDefault();
                     if (focusTarget) {
                         focusTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                         focusTarget.focus();
                     }
                     return;
                 }

                 // ผ่านเงื่อนไข — จัดการ localStorage
                 if (rememberMeCheckbox.checked) {
                     localStorage.setItem('remember_me', 'true');
                     localStorage.setItem('saved_username', usernameInput.value);
                     localStorage.setItem('saved_password', passwordInput.value);
                 } else {
                     localStorage.removeItem('remember_me');
                     localStorage.removeItem('saved_username');
                     localStorage.removeItem('saved_password');
                 }
             });
 
             // ลบกรอบแดงทันทีที่ผู้ใช้เริ่มพิมพ์รหัสผ่าน
             passwordInput.addEventListener('input', () => {
                 if (passwordInput.value.trim() !== '') {
                     passwordInput.classList.remove('input-error');
                     passwordInput.closest('.input-group').classList.remove('has-error');
                     passwordErrMsg.style.display = 'none';
                 }
             });

             // ลบกรอบแดงทันทีที่ผู้ใช้เริ่มพิมพ์รหัสผู้ใช้
             usernameInput.addEventListener('input', () => {
                 if (usernameInput.value.trim() !== '') {
                     usernameInput.classList.remove('input-error');
                     usernameInput.closest('.input-group').classList.remove('has-error');
                     if (usernameErrMsg) {
                         usernameErrMsg.style.display = 'none';
                     }
                 }
             });
        });
    </script>
</body>

</html>
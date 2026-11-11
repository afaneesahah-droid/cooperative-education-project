<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบกู้คืนรหัสผ่าน - วิทยาลัยเทคนิคยะลา</title>
    <!-- นำเข้า Tailwind CSS และ FontAwesome / Lucide สำหรับไอคอนที่สวยงาม -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #DBEAFE;
        }

        /* เอฟเฟกต์การสลับหน้าแบบ Fade-in Smooth */
        .step-view {
            animation: fadeIn 0.4s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* โครงสร้างหลัก Login Wrapper */
        .login-wrapper {
            display: flex;
            width: 1000px; 
            max-width: 95vw;
            min-height: 650px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(30, 58, 138, 0.25);
        }

        /* ส่วนคอลัมน์ด้านซ้ายสำหรับแบรนดิ้ง */
        .left-panel {
            flex: 0 0 42%; 
            background: linear-gradient(165deg, #1e3a8a 0%, #1d4ed8 40%, #2563eb 70%, #3b82f6 100%);
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
            top: -70px;
            left: -70px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -90px;
            right: -70px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        /* ส่วนคอลัมน์ขวาสำหรับฟอร์ม */
        .right-panel {
            flex: 1;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 48px; 
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                min-height: auto;
            }
            .left-panel {
                flex: none;
                padding: 36px 20px;
            }
            .right-panel {
                padding: 32px 24px;
            }
        }

        /* ปรับระดับขนาดตัวหนังสือให้สอดคล้องกับหน้าแรก (Login Page) */
        .right-panel h1, .right-panel h2 {
            font-size: 1.75rem !important;
            font-weight: 700 !important;
            color: #111827 !important;
        }

        .right-panel p {
            font-size: 0.82rem !important;
            color: #9ca3af !important;
            font-weight: 400 !important;
        }

        .right-panel label {
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #374151 !important;
        }

        .right-panel input:not(.otp-input) {
            font-size: 0.88rem !important;
            padding-top: 13px !important;
            padding-bottom: 13px !important;
        }

        .right-panel button[type="submit"], .right-panel button.btn-login {
            font-size: 1rem !important;
            font-weight: 700 !important;
            padding-top: 14px !important;
            padding-bottom: 14px !important;
        }

        .right-panel a {
            font-size: 0.82rem !important;
            font-weight: 600 !important;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <div class="login-wrapper bg-white">

        <!-- ===== คอลัมน์ซ้าย: ข้อมูลสถาบันและตราสัญลักษณ์ ===== -->
        <div class="left-panel text-white text-center">
            <div class="relative z-10 mb-6 w-36 h-36 bg-white/10 rounded-full flex items-center justify-center border-4 border-white/20 backdrop-blur-md shadow-inner transition-transform duration-500 hover:scale-105">
                <img src="{{ asset('images/logo.png') }}" alt="YTC Logo" class="w-28 h-28 object-contain">
            </div>

            <div class="relative z-10 space-y-2">
                <h2 class="text-lg sm:text-xl font-extrabold tracking-wide text-white leading-relaxed">
                    ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา
                </h2>
                <div class="h-1 w-12 bg-yellow-400 mx-auto rounded-full mt-2"></div>
            </div>
        </div>

        <!-- ===== คอลัมน์ขวา: พื้นที่ขั้นตอนและแบบฟอร์มกู้คืนรหัสผ่าน ===== -->
        <div class="right-panel">



            <div id="form-step-1" class="step-view space-y-5 max-w-md mx-auto w-full my-auto">
                <div class="text-center">
                    <div class="inline-flex p-3 bg-blue-50 text-blue-600 rounded-full mb-3">
                        <i data-lucide="key-round" class="w-8 h-8"></i>
                    </div>
                    <h1 class="text-3xl font-extrabold text-slate-800">ลืมรหัสผ่าน</h1>
                </div>

                <form id="email-form" onsubmit="handleEmailSubmit(event)" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">อีเมลที่ลงทะเบียน <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </span>
                            <input type="email" id="email" required placeholder="กรุณากรอกอีเมลของคุณ" oninput="clearEmailError()"
                                class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base font-medium bg-slate-50 transition-all" />
                        </div>
                        <p id="email-error" class="text-xs text-red-500 mt-2 hidden font-semibold flex items-center gap-1.5 animate-in fade-in slide-in-from-top-1 duration-200" style="color: #ef4444;">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-500" style="color: #ef4444;"></i>
                            <span id="email-error-text" class="text-red-500" style="color: #ef4444;">ไม่พบบัญชีที่ลงทะเบียนไว้</span>
                        </p>
                    </div>

                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-800 hover:to-blue-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2 text-base">
                        ส่งลิงก์รหัส OTP <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                </form>

                <div class="text-center pt-3">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-500 hover:text-blue-600 transition-all flex items-center justify-center gap-1">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i> ย้อนกลับไปหน้าเข้าสู่ระบบ
                    </a>
                </div>
            </div>

            <div id="form-step-2" class="step-view hidden space-y-5 max-w-md mx-auto w-full my-auto">
                <div class="text-center">
                    <div class="inline-flex p-3 bg-amber-50 text-amber-600 rounded-full mb-3">
                        <i data-lucide="shield-alert" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-800">ยืนยันรหัส OTP</h2>
                    <p class="text-slate-500 mt-2 text-sm leading-relaxed max-w-md mx-auto">
                        ระบบได้ส่งรหัสความป OTP จำนวน 6 หลัก ไปยังอีเมล <span id="display-target-email" class="font-semibold text-slate-700 block mt-1 underline">your-email@example.com</span> แล้ว
                    </p>
                </div>



                <form id="otp-form" onsubmit="handleOtpSubmit(event)" class="space-y-5">
                    <!-- กล่องกรอก OTP แยกช่อง -->
                    <div class="flex justify-center gap-2 max-w-xs mx-auto" id="otp-container">
                        <input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="1" class="otp-input w-11 h-11 text-center text-lg font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" required />
                        <input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="1" class="otp-input w-11 h-11 text-center text-lg font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" required />
                        <input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="1" class="otp-input w-11 h-11 text-center text-lg font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" required />
                        <input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="1" class="otp-input w-11 h-11 text-center text-lg font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" required />
                        <input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="1" class="otp-input w-11 h-11 text-center text-lg font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" required />
                        <input type="text" pattern="[0-9]*" inputmode="numeric" maxlength="1" class="otp-input w-11 h-11 text-center text-lg font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" required />
                    </div>

                    <!-- แจ้งเตือนด้านล่าง OTP -->
                    <div id="otp-msg" class="hidden text-center text-sm font-normal px-4 py-2.5 rounded-xl transition-all"></div>

                    <div class="text-center text-sm">
                        <p class="text-slate-500">
                            ยังไม่ได้รับรหัสยืนยัน? 
                            <button type="button" id="btn-resend" onclick="handleResendOtp()" class="text-blue-600 hover:text-blue-800 font-bold transition-all focus:outline-none">
                                ส่งรหัสอีกครั้ง
                            </button>
                            <span id="countdown-text" class="text-slate-400 font-medium"> (ใน <span id="timer">59</span> วินาที)</span>
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="goToStep(1)"
                            class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3.5 px-4 rounded-xl transition-all text-sm">
                            ย้อนกลับ
                        </button>
                        <button type="submit" 
                            class="w-2/3 bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-800 hover:to-blue-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2 text-sm sm:text-base">
                            ยืนยัน <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div id="form-step-3" class="step-view hidden space-y-5 max-w-md mx-auto w-full my-auto">
                <div class="text-center">
                    <div class="inline-flex p-3 bg-blue-50 text-blue-600 rounded-full mb-3">
                        <i data-lucide="lock" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-800">กำหนดรหัสผ่านใหม่</h2>
                    <p class="text-slate-500 text-sm mt-2 leading-relaxed max-w-md mx-auto">
                        สร้างรหัสผ่านใหม่ที่คาดเดายากเพื่อความปลอดภัยสูงสุดของข้อมูลระบบ
                    </p>
                </div>

                <form id="password-form" onsubmit="handlePasswordSubmit(event)" class="space-y-4">
                    <!-- ช่องกรอกรหัสใหม่ -->
                    <div>
                        <label for="new-password" class="block text-sm font-semibold text-slate-700 mb-1">รหัสผ่านใหม่ <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </span>
                            <input type="password" id="new-password" required placeholder="อย่างน้อย 6 ตัวอักษรขึ้นไป" onkeyup="checkPasswordStrength()"
                                class="w-full pl-10 pr-10 py-3.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base bg-slate-50 transition-all" />
                            <button type="button" onclick="togglePasswordVisibility('new-password')" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none">
                                <i data-lucide="eye" class="w-4 h-4" id="toggle-icon-new"></i>
                            </button>
                    </div>


                    <!-- ยืนยันรหัสอีกครั้ง -->
                    <div>
                        <label for="confirm-password" class="block text-sm font-semibold text-slate-700 mb-1">ยืนยันรหัสผ่านใหม่อีกครั้ง <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <i data-lucide="check-square" class="w-4 h-4"></i>
                            </span>
                            <input type="password" id="confirm-password" required placeholder="กรอกรหัสผ่านซ้ำอีกครั้ง"
                                class="w-full pl-10 pr-10 py-3.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-base bg-slate-50 transition-all" />
                            <button type="button" onclick="togglePasswordVisibility('confirm-password')" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none">
                                <i data-lucide="eye" class="w-4 h-4" id="toggle-icon-confirm"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-800 hover:to-blue-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2 text-base">
                        บันทึก <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>

            <div id="form-step-4" class="step-view hidden space-y-5 text-center my-auto max-w-md mx-auto w-full">
                <div class="inline-flex p-4 bg-emerald-50 text-emerald-500 rounded-full animate-bounce">
                    <i data-lucide="party-popper" class="w-10 h-10"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-800">กู้คืนรหัสผ่านเรียบร้อย!</h2>
                    <p class="text-slate-500 mt-2 text-sm leading-relaxed max-w-md mx-auto">
                        รหัสผ่านใหม่สำหรับวิทยาลัยของคุณถูกเปิดใช้งานเสร็จสมบูรณ์แล้ว ระบบได้บันทึกและทำการตรวจสอบสิทธิ์ความปลอดภัยให้บัญชีของคุณเรียบร้อย
                    </p>
                </div>



                <a href="{{ route('login') }}"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2 text-base">
                    <i data-lucide="log-in" class="w-5 h-5"></i> ไปหน้าเข้าสู่ระบบเพื่อใช้งาน
                </a>
            </div>

            <!-- ส่วนท้ายกระดาษ -->
            <div class="mt-6 pt-4 border-t border-slate-100 text-center text-[10px] text-slate-400 flex flex-col sm:flex-row justify-between items-center gap-2">
            </div>

        </div>
    </div>

    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full"></div>

    <script>
        // เริ่มต้นเรนเดอร์ไอคอนทั้งหมด
        lucide.createIcons();

        // ตัวแปรและข้อมูลสำหรับจำลองสถานะของระบบ
        let currentStep = 1;
        let timerSeconds = 60;
        let countdownInterval;
        let enteredEmail = "";

        function goToStep(step) {
            document.querySelectorAll('.step-view').forEach(view => {
                view.classList.add('hidden');
            });
            const targetView = document.getElementById(`form-step-${step}`);
            if (targetView) {
                targetView.classList.remove('hidden');
            }
            currentStep = step;

        }

        // จัดการกับการโฟกัสของช่อง OTP อัตโนมัติ (Auto-tab และ Backspace หวนกลับ)
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            // ดักสิทธิ์การป้อนเฉพาะตัวเลขเท่านั้น
            input.addEventListener('keypress', (e) => {
                if (isNaN(String.fromCharCode(e.which))) e.preventDefault();
            });

            input.addEventListener('input', (e) => {
                // รีเซ็ตขอบสีแดงและข้อความเมื่อผู้ใช้เริ่มพิมพ์ใหม่
                if (typeof hideOtpMsg === 'function') hideOtpMsg();
                if (e.target.value.length >= 1) {
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0) {
                    if (index > 0) {
                        otpInputs[index - 1].focus();
                    }
                }
            });
        });

        // ฟังก์ชันระบบแจ้งเตือน Toast สวยงามไร้การใช้ alert() ดิบๆ
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            let bgColor = 'bg-emerald-500';
            let icon = 'check-circle';
            if (type === 'error') {
                bgColor = 'bg-rose-500';
                icon = 'alert-triangle';
            } else if (type === 'info') {
                bgColor = 'bg-blue-600';
                icon = 'info';
            }

            toast.className = `flex items-center p-4 text-white rounded-xl shadow-xl border border-white/10 transition-all duration-300 transform translate-y-3 opacity-0 ${bgColor}`;
            toast.innerHTML = `
                <i data-lucide="${icon}" class="w-5 h-5 mr-3 shrink-0"></i>
                <span class="text-xs font-semibold leading-relaxed">${message}</span>
            `;

            container.appendChild(toast);
            lucide.createIcons();

            setTimeout(() => {
                toast.classList.remove('translate-y-3', 'opacity-0');
            }, 50);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-3');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 4000);
        }

        // ระบบควบคุมสไลด์แล?        // จัดการส่งแบบฟอร์มอีเมล (ขั้นตอนที่ 1)
        function handleEmailSubmit(event) {
            event.preventDefault();
            const emailInput = document.getElementById('email');
            
            // ล้างสถานะข้อผิดพลาดก่อนเริ่มส่งใหม่
            clearEmailError();
            
            enteredEmail = emailInput.value;

            if (!enteredEmail.includes('@')) {
                showToast("กรุณากรอกอีเมลในรูปแบบที่ถูกต้องเพื่อดำเนินการต่อครับ", "error");
                return;
            }

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalBtnContent = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="fa fa-spinner fa-spin mr-2"></i> กำลังส่ง...`;

            fetch('{{ route("password.otp.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: enteredEmail })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                
                if (res.status === 200) {
                    document.getElementById('display-target-email').innerText = enteredEmail;
                    goToStep(2);
                    startTimer();
                } else if (res.status === 404 || res.status === 400) {
                    // แสดงสีขอบแดงและข้อความแจ้งเตือนใต้ช่องกรอกอีเมล
                    emailInput.classList.remove('border-slate-200', 'focus:ring-blue-500');
                    emailInput.classList.add('border-red-500', 'focus:ring-red-500');
                    
                    const emailError = document.getElementById('email-error');
                    const emailErrorText = document.getElementById('email-error-text');
                    if (emailError && emailErrorText) {
                        emailErrorText.innerText = res.body.message || "ไม่พบบัญชีที่ลงทะเบียนไว้";
                        emailError.classList.remove('hidden');
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                } else {
                    showToast(res.body.message || "เกิดข้อผิดพลาดในการตรวจสอบอีเมล", "error");
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                showToast("ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
                console.error(error);
            });
        }

        function clearEmailError() {
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            if (emailInput) {
                emailInput.classList.remove('border-red-500', 'focus:ring-red-500');
                emailInput.classList.add('border-slate-200', 'focus:ring-blue-500');
            }
            if (emailError) {
                emailError.classList.add('hidden');
            }
        }

        // เริ่มระนาบเวลานับถอยหลังในการส่ง OTP (60 วินาที)
        function startTimer() {
            clearInterval(countdownInterval);
            timerSeconds = 60;
            const timerSpan = document.getElementById('timer');
            const countdownText = document.getElementById('countdown-text');
            const btnResend = document.getElementById('btn-resend');

            btnResend.disabled = true;
            btnResend.classList.add('opacity-40', 'cursor-not-allowed');
            countdownText.classList.remove('hidden');

            countdownInterval = setInterval(() => {
                timerSeconds--;
                timerSpan.innerText = timerSeconds;

                if (timerSeconds <= 0) {
                    clearInterval(countdownInterval);
                    btnResend.disabled = false;
                    btnResend.classList.remove('opacity-40', 'cursor-not-allowed');
                    countdownText.classList.add('hidden');
                }
            }, 1000);
        }

        // ฟังก์ชันเมื่อกดขอรับรหัสผ่านใหม่อีกครั้ง
        function handleResendOtp() {
            const btnResend = document.getElementById('btn-resend');
            const originalContent = btnResend.innerHTML;
            btnResend.disabled = true;
            btnResend.innerHTML = `<i class="fa fa-spinner fa-spin mr-1"></i> กำลังส่ง...`;

            fetch('{{ route("password.otp.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: enteredEmail })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                btnResend.disabled = false;
                btnResend.innerHTML = originalContent;
                if (res.status === 200) {
                    startTimer();
                } else {
                    showToast(res.body.message || "เกิดข้อผิดพลาดในการส่ง OTP", "error");
                }
            })
            .catch(error => {
                btnResend.disabled = false;
                btnResend.innerHTML = originalContent;
                showToast("ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
                console.error(error);
            });
        }

        // helper: แสดงข้อความแจ้งเตือนด้านล่าง OTP (ไม่มีกรอบ/พื้นหลัง)
        function showOtpMsg(message, type) {
            const el = document.getElementById('otp-msg');
            if (!el) return;
            el.textContent = message;
            el.className = 'text-center text-sm font-normal py-1.5 transition-all ';
            if (type === 'error') {
                el.className += 'text-red-500';
                // ทำให้ขอบ input OTP เป็นสีแดง
                document.querySelectorAll('.otp-input').forEach(inp => {
                    inp.classList.remove('border-slate-200', 'focus:ring-blue-500');
                    inp.classList.add('border-red-400', 'focus:ring-red-400');
                });
            } else if (type === 'success') {
                el.className += 'text-green-600';
                resetOtpBorders();
            } else {
                el.className += 'text-blue-600';
                resetOtpBorders();
            }
            el.classList.remove('hidden');
        }

        function resetOtpBorders() {
            document.querySelectorAll('.otp-input').forEach(inp => {
                inp.classList.remove('border-red-400', 'focus:ring-red-400');
                inp.classList.add('border-slate-200', 'focus:ring-blue-500');
            });
        }

        function hideOtpMsg() {
            const el = document.getElementById('otp-msg');
            if (el) el.classList.add('hidden');
            resetOtpBorders();
        }

        // ยืนยันรหัส OTP (ขั้นตอนที่ 2)
        function handleOtpSubmit(event) {
            event.preventDefault();
            
            let otpCode = "";
            otpInputs.forEach(input => {
                otpCode += input.value;
            });

            if (otpCode.length < 6) {
                showOtpMsg("กรุณาระบุรหัส OTP ให้ครบทั้ง 6 หลัก", "error");
                return;
            }

            hideOtpMsg();

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="fa fa-spinner fa-spin mr-2"></i> กำลังตรวจสอบ...`;

            fetch('{{ route("password.otp.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ otp: otpCode })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                if (res.status === 200) {
                    showOtpMsg(res.body.message || "ยืนยันสำเร็จ ✓", "success");
                    setTimeout(() => goToStep(3), 800);
                } else {
                    showOtpMsg(res.body.message || "รหัส OTP ไม่ถูกต้องหรือหมดอายุ", "error");
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                showOtpMsg("ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
                console.error(error);
            });
        }

        // ซ่อนและแสดงข้อความรหัสผ่าน
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const iconId = inputId === 'new-password' ? 'toggle-icon-new' : 'toggle-icon-confirm';
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        // ฟังก์ชันประเมินระดับรหัสผ่าน (Password Strength Real-time Checker)
        function checkPasswordStrength() {
            const password = document.getElementById('new-password').value;
            const label = document.getElementById('strength-label');
            
            const bars = [
                document.getElementById('strength-bar-1'),
                document.getElementById('strength-bar-2'),
                document.getElementById('strength-bar-3'),
                document.getElementById('strength-bar-4')
            ];

            // ล้างสีก่อนประเมิน
            bars.forEach(bar => {
                bar.className = 'h-full bg-slate-200 transition-all duration-300';
            });

            if (password.length === 0) {
                label.innerText = 'สั้นเกินไป';
                label.className = 'text-slate-400 font-semibold';
                return;
            }

            let score = 0;
            if (password.length >= 6) score++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            // แสดงเกจแถบความปลอดภัยตามคะแนนที่ตรวจจับได้
            if (score === 1) {
                label.innerText = 'ไม่ปลอดภัย (สั้นเกินไป)';
                label.className = 'text-red-500 font-semibold';
                bars[0].className = 'h-full bg-red-500 transition-all duration-300';
            } else if (score === 2) {
                label.innerText = 'ระดับปานกลาง';
                label.className = 'text-orange-500 font-semibold';
                bars[0].className = 'h-full bg-orange-500 transition-all duration-300';
                bars[1].className = 'h-full bg-orange-500 transition-all duration-300';
            } else if (score === 3) {
                label.innerText = 'ปลอดภัยค่อนข้างสูง';
                label.className = 'text-blue-500 font-semibold';
                bars[0].className = 'h-full bg-blue-500 transition-all duration-300';
                bars[1].className = 'h-full bg-blue-500 transition-all duration-300';
                bars[2].className = 'h-full bg-blue-500 transition-all duration-300';
            } else if (score >= 4) {
                label.innerText = 'ปลอดภัยสูงสุดดีเยี่ยม!';
                label.className = 'text-green-500 font-semibold';
                bars[0].className = 'h-full bg-green-500 transition-all duration-300';
                bars[1].className = 'h-full bg-green-500 transition-all duration-300';
                bars[2].className = 'h-full bg-green-500 transition-all duration-300';
                bars[3].className = 'h-full bg-green-500 transition-all duration-300';
            }
        }

        // จัดการตรวจสอบและบันทึกรหัสผ่านใหม่ (ขั้นตอนที่ 3)
        function handlePasswordSubmit(event) {
            event.preventDefault();
            const password = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (password.length < 6) {
                showToast("รหัสผ่านใหม่ต้องมีความยาวไม่น้อยกว่า 6 ตัวอักษรขึ้นไปครับ", "error");
                return;
            }

            if (password !== confirmPassword) {
                showToast("รหัสผ่านใหม่ทั้งสองช่องระบุไม่ตรงกัน โปรดตรวจสอบอีกครั้ง", "error");
                return;
            }

            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
            if (!regex.test(password)) {
                showToast("รหัสผ่านใหม่ต้องประกอบด้วยตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลขอย่างน้อย 1 ตัว", "error");
                return;
            }

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="fa fa-spinner fa-spin mr-2"></i> กำลังบันทึก...`;

            fetch('{{ route("password.reset.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    password: password,
                    password_confirmation: confirmPassword
                })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                if (res.status === 200) {
                    showToast(res.body.message || "ดำเนินการตั้งค่ารหัสผ่านวิทยาลัยของคุณเสร็จสิ้นเรียบร้อยแล้ว", "success");
                    goToStep(4);
                } else {
                    showToast(res.body.message || "เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน", "error");
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                showToast("ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง", "error");
                console.error(error);
            });
        }

        function resetFlow() {
            document.getElementById('email').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
            otpInputs.forEach(input => input.value = '');
            checkPasswordStrength();
            goToStep(1);
            showToast("ระบบสแตนด์บายเพื่อรอจำลองการทำงานอีกครั้ง", "info");
        }

        function showDemoInfo() {
            showToast("ระบบนี้เป็นเวอร์ชันต้นแบบ (UI Prototype) ที่ผสานดีไซน์และฟังก์ชันเสร็จสมบูรณ์เรียบร้อยแล้ว", "info");
        }
    </script>
</body>

</html>
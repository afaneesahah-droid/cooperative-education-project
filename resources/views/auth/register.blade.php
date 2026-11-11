<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</title>
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
            padding: 20px 0;
        }

        /* ==================== WRAPPER ==================== */
        .register-wrapper {
            display: flex;
            width: 1000px;
            max-width: 95vw;
            min-height: 650px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(109,40,217,0.18);
        }

        /* ==================== LEFT PANEL ==================== */
        .left-panel {
            flex: 0 0 35%;
            background: linear-gradient(160deg, #1E3A8A 0%, #2563EB 45%, #60A5FA 75%, #DBEAFE 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 30px;
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
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            text-align: center;
            line-height: 1.65;
            margin-bottom: 0;
        }

        .system-title strong {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
        }

        /* ==================== RIGHT PANEL ==================== */
        .right-panel {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 48px;
        }

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
            margin-bottom: 24px;
        }

        /* Error Banner */
        .error-banner {
            background: #FEE2E2;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #b91c1c;
        }

        /* Success Banner */
        .success-banner {
            background: #D1FAE5;
            border-left: 4px solid #10b981;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #065f46;
        }

        /* Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .form-field-full {
            grid-column: span 2;
        }

        /* Field Label */
        .field-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        /* Input Group */
        .input-group {
            position: relative;
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

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Prompt', sans-serif;
            font-size: 0.88rem;
            color: #374151;
            background: #f9fafb;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            appearance: none;
        }

        .input-group select {
            padding-right: 30px;
        }

        .input-group::after {
            /* Dropdown indicator for select */
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            display: none;
        }

        .input-group.select-group::after {
            display: block;
        }

        .input-group input:focus, .input-group select:focus {
            border-color: #2563EB;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .field-error-msg {
            display: none;
            color: #ef4444;
            font-size: 0.78rem;
            margin-top: 4px;
            padding-left: 2px;
        }

        .input-group input.input-error,
        .input-group select.input-error {
            border-color: #ef4444 !important;
            background: #fff5f5;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.15);
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

        /* ปุ่ม Register */
        .btn-register {
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
            box-shadow: 0 4px 18px rgba(37,99,235,0.3);
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37,99,235,0.4);
        }

        .btn-register:active { transform: translateY(0); }

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
            color: #2563EB;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .bottom-links p a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .register-wrapper {
                flex-direction: column;
                border-radius: 16px;
                min-height: unset;
            }

            .left-panel { flex: none; padding: 36px 20px; }
            .right-panel { padding: 28px 20px; }
            .form-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .form-field-full {
                grid-column: span 1;
            }
        }
    </style>
</head>

<body>
    <div class="register-wrapper">

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
            <h1 class="form-title">สมัครสมาชิก</h1>

            <!-- Error Banner -->
            @if($errors->any())
            <div class="error-banner">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- ฟอร์มสมัครสมาชิก -->
            <form method="POST" action="{{ route('register') }}" id="register-form" novalidate>
                @csrf

                <div class="form-grid">
                    <!-- ชื่อจริง -->
                    <div>
                        <label class="field-label" for="first-name-input">ชื่อ <span style="color: #ef4444;">*</span></label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fa-solid fa-id-card"></i></span>
                            <input 
                                type="text" 
                                id="first-name-input" 
                                name="first_name" 
                                value="{{ old('first_name') }}" 
                                required 
                                maxlength="30"
                                oninput="this.value = this.value.replace(/[^a-zA-Z\u0e00-\u0e7f\s]/g, ''); clearFieldError('first-name-input')"
                                placeholder="ชื่อ"
                            >
                        </div>
                        <small id="first-name-input-error" class="field-error-msg">กรุณากรอกชื่อ</small>
                    </div>

                    <!-- นามสกุล -->
                    <div>
                        <label class="field-label" for="last-name-input">นามสกุล <span style="color: #ef4444;">*</span></label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fa-solid fa-id-card"></i></span>
                            <input 
                                type="text" 
                                id="last-name-input" 
                                name="last_name" 
                                value="{{ old('last_name') }}" 
                                required 
                                maxlength="30"
                                oninput="this.value = this.value.replace(/[^a-zA-Z\u0e00-\u0e7f\s]/g, ''); clearFieldError('last-name-input')"
                                placeholder="นามสกุล"
                            >
                        </div>
                        <small id="last-name-input-error" class="field-error-msg">กรุณากรอกนามสกุล</small>
                    </div>

                    <!-- คำนำหน้า -->
                    <div>
                        <label class="field-label" for="prefix-input">คำนำหน้า <span style="color: #ef4444;">*</span></label>
                        <div class="input-group select-group">
                            <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                            <select id="prefix-input" name="prefix" required onchange="clearFieldError('prefix-input')">
                                <option value="">เลือกคำนำหน้า</option>
                                <option value="นาย" {{ old('prefix') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                <option value="นาง" {{ old('prefix') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                <option value="นางสาว" {{ old('prefix') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                            </select>
                        </div>
                        <small id="prefix-input-error" class="field-error-msg">กรุณาเลือกคำนำหน้า</small>
                    </div>

                    <!-- ชื่อผู้ใช้ -->
                    <div>
                        <label class="field-label" for="username-input">รหัสผู้ใช้ / User ID <span style="color: #ef4444;">*</span></label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                            <input 
                                type="text" 
                                id="username-input" 
                                name="username" 
                                value="{{ old('username') }}" 
                                required 
                                maxlength="8"
                                oninput="this.value = this.value.replace(/[^0-9]/g, ''); autoFillFromUsername(); clearFieldError('username-input')"
                                placeholder="รหัสผู้ใช้ 8 หลัก"
                            >
                        </div>
                        <small id="username-input-error" class="field-error-msg">กรุณากรอกรหัสผู้ใช้</small>
                    </div>

                    <!-- อีเมล -->
                    <div>
                        <label class="field-label" for="email-input">อีเมล <span style="color: #ef4444;">*</span></label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" id="email-input" name="email" value="{{ old('email') }}" required placeholder="อีเมลแอดเดรส" oninput="clearFieldError('email-input')">
                        </div>
                        <small id="email-input-error" class="field-error-msg">กรุณากรอกอีเมล</small>
                    </div>

                    <!-- เบอร์โทรศัพท์ -->
                    <div>
                        <label class="field-label" for="phone-input">เบอร์โทรศัพท์ <span style="color: #ef4444;">*</span></label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fa-solid fa-phone"></i></span>
                            <input 
                                type="text" 
                                id="phone-input" 
                                name="phone" 
                                value="{{ old('phone') }}" 
                                required 
                                pattern="[0-9]{10}" 
                                maxlength="10" 
                                oninput="this.value = this.value.replace(/[^0-9]/g, ''); clearFieldError('phone-input')"
                                placeholder="เบอร์โทรศัพท์ 10 หลัก (0-9)"
                            >
                        </div>
                        <small id="phone-input-error" class="field-error-msg">กรุณากรอกเบอร์โทรศัพท์</small>
                    </div>

                    <!-- แก้ไขจุดนี้: เพิ่มช่องให้เลือกฝ่ายหลักเพิ่มขึ้นมา -->
                    <div>
                        <label class="field-label" for="department-group-input">ฝ่าย <span style="color: #ef4444;">*</span></label>
                        <div class="input-group select-group">
                            <span class="input-icon"><i class="fa-solid fa-layer-group"></i></span>
                            <select id="department-group-input" name="department_group" onchange="updateSubDepartments(); clearFieldError('department-group-input')" required>
                                <option value="">-- กรุณาเลือกฝ่าย --</option>
                                <option value="resource" {{ old('department_group') == 'resource' ? 'selected' : '' }}>ฝ่ายบริหารทรัพยากร</option>
                                <option value="planning" {{ old('department_group') == 'planning' ? 'selected' : '' }}>ฝ่ายแผนงานและความร่วมมือ</option>
                                <option value="student" {{ old('department_group') == 'student' ? 'selected' : '' }}>ฝ่ายพัฒนากิจการนักเรียน นักศึกษา</option>
                                <option value="academic" {{ old('department_group') == 'academic' ? 'selected' : '' }}>ฝ่ายวิชาการ</option>
                            </select>
                        </div>
                        <small id="department-group-input-error" class="field-error-msg">กรุณาเลือกฝ่าย</small>
                    </div>

                    <!-- แก้ไขจุดนี้: ฟิลด์แผนกวิชา / ฝ่าย เดิม ถูกเปลี่ยนเป็นแบบเด้ง Dynamic ตามฝ่ายหลัก -->
                    <div>
                        <label class="field-label" for="department-input">งาน/แผนกวิชา <span style="color: #ef4444;">*</span></label>
                        <div class="input-group select-group">
                            <span class="input-icon"><i class="fa-solid fa-building"></i></span>
                            <select id="department-input" name="department" required disabled onchange="clearFieldError('department-input')">
                                <option value="">-- กรุณาเลือกแผนกวิชา --</option>
                            </select>
                        </div>
                        <small id="department-input-error" class="field-error-msg">กรุณาเลือกงาน/แผนกวิชา</small>
                    </div>

                    <!-- สิทธิ์การใช้งาน -->
                    <div>
                        <label class="field-label" for="role-input">สิทธิ์การใช้งาน <span style="color: #ef4444;">*</span></label>
                        <div class="input-group select-group">
                            <span class="input-icon"><i class="fa-solid fa-shield-halved"></i></span>
                            <select id="role-input" name="role" required onchange="clearFieldError('role-input')">
                                <option value="">เลือกสิทธิ์การใช้งาน</option>
                                <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>ครู / บุคลากร</option>
                                <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>ช่างเทคนิค</option>
                                <option value="head" {{ old('role') == 'head' ? 'selected' : '' }}>หัวหน้าฝ่าย</option>
                                <option value="executive" {{ old('role') == 'executive' ? 'selected' : '' }}>ผู้บริหาร</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ (Admin)</option>
                            </select>
                        </div>
                        <small id="role-input-error" class="field-error-msg">กรุณาเลือกสิทธิ์การใช้งาน</small>
                    </div>


                    <!-- รหัสผ่าน -->
                    <div>
                        <label class="field-label" for="password-input">รหัสผ่าน <span style="color: #ef4444;">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password-input" name="password" oninput="validatePasswordComplexity(); clearFieldError('password-input')" required autocomplete="new-password" placeholder="อย่างน้อย6ตัว(มี A-Z,a-zและตัวเลข)" style="padding-left: 14px;">
                            <button type="button" class="toggle-pass" onclick="togglePasswordVisibility('password-input', 'toggle-icon-1')" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                <i class="fa-solid fa-eye" id="toggle-icon-1"></i>
                            </button>
                        </div>
                        <small id="password-input-error" class="field-error-msg">กรุณากรอกรหัสผ่าน</small>
                    </div>

                    <!-- ยืนยันรหัสผ่าน -->
                    <div id="password-confirm-group">
                        <label class="field-label" for="password-confirm-input">ยืนยันรหัสผ่าน <span style="color: #ef4444;">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password-confirm-input" name="password_confirmation" required disabled autocomplete="new-password" placeholder="กรุณากรอกรหัสผ่านหลักให้ถูกต้องก่อน" style="padding-left: 14px;" oninput="clearFieldError('password-confirm-input')">
                            <button type="button" class="toggle-pass" onclick="togglePasswordVisibility('password-confirm-input', 'toggle-icon-2')" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                <i class="fa-solid fa-eye" id="toggle-icon-2"></i>
                            </button>
                        </div>
                        <small id="password-confirm-input-error" class="field-error-msg">กรุณายืนยันรหัสผ่าน</small>
                    </div>
                </div>

                <!-- ปุ่มสมัครสมาชิก -->
                <button type="submit" class="btn-register">สมัครสมาชิก</button>
            </form>

            <div class="bottom-links">
                <p>มีบัญชีผู้ใช้อยู่แล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ</a></p>
            </div>

        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.className = "fa-solid fa-eye-slash";
            } else {
                passwordInput.type = "password";
                icon.className = "fa-solid fa-eye";
            }
        }

        // เพิ่มชุดข้อมูลงาน/แผนกวิชาของวิทยาลัยเทคนิคยะลาเข้าในสคริปต์[cite: 1]
        const subDepartmentData = {
            resource: [
                "งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", 
                "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"
            ],
            planning: [
                "งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", 
                "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", 
                "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพประจำอำเภอยะหา"
            ],
            student: [
                "งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", 
                "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"
            ],
            academic: [
                "งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", 
                "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", 
                "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", 
                "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", 
                "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า",
                "แผนกวิชาอิเล็กทรอนิกส์", "แผนกวิชาเทคโนโลยีคอมพิวเตอร์", "แมคคาทรอนิกส์และหุ่นยนต์"
            ]
        };

        // เพิ่มฟังก์ชันดึงค่าตัวเลือกย่อยของแต่ละฝ่ายให้ทำงานประสานกัน[cite: 1]
        function updateSubDepartments(savedValue = null) {
            const mainDeptSelect = document.getElementById("department-group-input");
            const subDeptSelect = document.getElementById("department-input");
            const selectedMain = mainDeptSelect.value;

            subDeptSelect.innerHTML = "";

            if (selectedMain === "") {
                subDeptSelect.disabled = true;
                const defaultOpt = document.createElement("option");
                defaultOpt.value = "";
                defaultOpt.text = "-- กรุณาเลือกฝ่ายก่อน --";
                subDeptSelect.appendChild(defaultOpt);
            } else {
                subDeptSelect.disabled = false;
                
                const defaultOpt = document.createElement("option");
                defaultOpt.value = "";
                defaultOpt.text = "เลือกงาน / แผนกวิชา";
                subDeptSelect.appendChild(defaultOpt);

                subDepartmentData[selectedMain].forEach(function(item) {
                    const opt = document.createElement("option");
                    opt.value = item;
                    opt.text = item;
                    if(savedValue && savedValue === item) {
                        opt.selected = true;
                    }
                    subDeptSelect.appendChild(opt);
                });
            }
        }

        // ฟังก์ชันกำหนดสิทธิ์ ฝ่าย และแผนกวิชาอัตโนมัติจากรหัสผู้ใช้งาน 8 หลัก
        function autoFillFromUsername() {
            const usernameInput = document.getElementById("username-input");
            const username = usernameInput.value.trim();
            if (username.length === 8) {
                const roleCode = username.substring(0, 2);
                const groupCode = username.substring(2, 5);
                const deptCode = username.substring(5, 8);

                // 1. ตรวจสอบและเลือกสิทธิ์การใช้งาน (Role)
                const roleMap = {
                    "10": "executive",
                    "20": "head",
                    "30": "teacher",
                    "40": "technician",
                    "90": "admin"
                };
                const roleVal = roleMap[roleCode];
                if (roleVal) {
                    const roleSelect = document.getElementById("role-input");
                    roleSelect.value = roleVal;
                    clearFieldError("role-input");
                }

                // 2. ตรวจสอบและเลือกฝ่าย (Department Group)
                const groupMap = {
                    "100": "resource",
                    "200": "academic",
                    "300": "planning",
                    "400": "student"
                };
                const groupVal = groupMap[groupCode];
                if (groupVal) {
                    const groupSelect = document.getElementById("department-group-input");
                    groupSelect.value = groupVal;
                    clearFieldError("department-group-input");
                    
                    // เรียกให้แสดงงาน/แผนกย่อยตามฝ่ายหลักที่เลือก
                    updateSubDepartments();
                }

                // 3. ตรวจสอบและเลือกงาน/แผนกวิชา (Sub-department)
                const deptMap = {
                    "001": "แผนกวิชาเครื่องกล",
                    "002": "แผนกวิชาช่างกลโรงงาน",
                    "003": "แผนกวิชาเทคนิคการเชื่อมและการผลิต",
                    "004": "แผนกวิชาสถาปัตยกรรม",
                    "005": "แผนกวิชาช่างก่อสร้างและโยธา",
                    "006": "แผนกวิชาอิเล็กทรอนิกส์",
                    "007": "แผนกวิชาสามัญสัมพันธ์",
                    "008": "แผนกวิชาคอมพิวเตอร์ธุรกิจ",
                    "009": "แผนกวิชาเทคโนโลยีคอมพิวเตอร์",
                    "010": "แมคคาทรอนิกส์และหุ่นยนต์",
                    "011": "แผนกวิชาช่างไฟฟ้ากำลัง",
                    "500": "ศูนย์ฝึกอบรมวิชาชีพประจำอำเภอยะหา"
                };
                const deptVal = deptMap[deptCode];
                if (deptVal) {
                    const deptSelect = document.getElementById("department-input");
                    // ตรวจสอบว่ามีตัวเลือกนี้อยู่ในกล่องหรือไม่ ถ้าไม่มีให้สร้างตัวเลือกเข้าไปใหม่
                    let optionExists = false;
                    for (let i = 0; i < deptSelect.options.length; i++) {
                        if (deptSelect.options[i].value === deptVal) {
                            optionExists = true;
                            break;
                        }
                    }
                    if (!optionExists) {
                        const newOpt = document.createElement("option");
                        newOpt.value = deptVal;
                        newOpt.text = deptVal;
                        deptSelect.appendChild(newOpt);
                    }
                    deptSelect.value = deptVal;
                    clearFieldError("department-input");
                }
            }
        }

        // ประมวลผลค่าเก่าที่ระบบบันทึกไว้เมื่อเปิดหน้าเว็บ
        document.addEventListener("DOMContentLoaded", function() {
            const oldGroup = "{{ old('department_group') }}";
            const oldDept = "{{ old('department') }}";
            
            if(oldGroup) {
                document.getElementById("department-group-input").value = oldGroup;
                updateSubDepartments(oldDept);
            }
            validatePasswordComplexity();
        });

        // ฟังก์ชันตรวจสอบความยากง่ายของรหัสผ่านตามเงื่อนไข (A-Z, a-z, 0-9, 6 ตัวขึ้นไป)
        function validatePasswordComplexity() {
            const passwordInput = document.getElementById("password-input");
            const confirmInput = document.getElementById("password-confirm-input");
            const val = passwordInput.value;
            // regex: อย่างน้อย A-Z 1 ตัว, a-z 1 ตัว, ตัวเลข 1 ตัว, ความยาว >= 6
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/;

            if (val.length === 0) {
                passwordInput.setCustomValidity("");
                confirmInput.disabled = true;
                confirmInput.value = "";
                confirmInput.placeholder = "ยืนยันรหัสผ่าน";
            } else {
                if (regex.test(val)) {
                    passwordInput.setCustomValidity("");
                    confirmInput.disabled = false;
                    confirmInput.placeholder = "กรอกรหัสผ่านอีกครั้ง";
                } else {
                    passwordInput.setCustomValidity("รหัสผ่านต้องมีตัวอักษรพิมพ์ใหญ่(อย่างน้อย1ตัว),ตัวพิมพ์เล็ก(อย่างน้อย1ตัว),(ตัวเลขอย่างน้อย1ตัว และมีความยาวไม่ต่ำกว่า6ตัวอักษร");
                    confirmInput.disabled = true;
                    confirmInput.value = "";
                    confirmInput.placeholder = "กรุณากรอกรหัสผ่านหลักให้ถูกต้องก่อน";
                }
            }
        }

        // คืนสถานะค่า disabled ก่อนส่งฟอร์มเพื่อส่งข้อมูลไปยังฝั่งเซิร์ฟเวอร์หลังบ้านได้ถูกต้อง
        document.getElementById("register-form").addEventListener("submit", function(e) {
            // รายการช่องที่ต้องตรวจสอบ: [id ของ input, id ของ small error]
            const requiredFields = [
                { id: "first-name-input",        errId: "first-name-input-error" },
                { id: "last-name-input",         errId: "last-name-input-error" },
                { id: "prefix-input",            errId: "prefix-input-error" },
                { id: "username-input",          errId: "username-input-error" },
                { id: "email-input",             errId: "email-input-error" },
                { id: "phone-input",             errId: "phone-input-error" },
                { id: "department-group-input",  errId: "department-group-input-error" },
                { id: "department-input",        errId: "department-input-error" },
                { id: "role-input",              errId: "role-input-error" },
                { id: "password-input",          errId: "password-input-error" },
                { id: "password-confirm-input",  errId: "password-confirm-input-error" },
            ];

            // ซ่อนข้อความเตือนทั้งหมดก่อน
            requiredFields.forEach(function(f) {
                const errEl = document.getElementById(f.errId);
                if (errEl) errEl.style.display = "none";
            });

            // ตรวจหาช่องแรกที่ยังว่าง แล้วแสดงข้อความเตือน + กรอบแดงช่องนั้น
            let firstEmpty = null;
            for (let i = 0; i < requiredFields.length; i++) {
                const el = document.getElementById(requiredFields[i].id);
                if (!el || el.disabled) continue;
                if (!el.value || el.value.trim() === "") {
                    const errEl = document.getElementById(requiredFields[i].errId);
                    if (errEl) errEl.style.display = "block";
                    el.classList.add("input-error");
                    if (!firstEmpty) firstEmpty = el;
                    break; // เตือนทีละช่อง
                }
            }

            if (firstEmpty) {
                e.preventDefault();
                firstEmpty.scrollIntoView({ behavior: "smooth", block: "center" });
                firstEmpty.focus();
                return;
            }

            // ปลดล็อก disabled fields ก่อนส่ง
            document.getElementById("department-group-input").disabled = false;
            document.getElementById("department-input").disabled = false;
            document.getElementById("password-confirm-input").disabled = false;
        });

        // ฟังก์ชันลบข้อความเตือน + กรอบแดง เมื่อผู้ใช้เริ่มกรอกช่องนั้น
        function clearFieldError(inputId) {
            const errEl = document.getElementById(inputId + "-error");
            if (errEl) errEl.style.display = "none";
            const inputEl = document.getElementById(inputId);
            if (inputEl) inputEl.classList.remove("input-error");
        }
    </script>
</body>

</html>
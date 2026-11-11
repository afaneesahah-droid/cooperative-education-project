<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกระบบ - ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</title>
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

        /* ==================== RIGHT PANEL ==================== */
        .right-panel {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 36px 48px 36px;
            align-items: center;
        }

        .role-card {
            width: 100%;
            max-width: 420px;
        }
        
        .role-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #334155;
            text-align: center;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .role-title i { font-size: 1.5rem; color: #1E3A8A; }
        
        .role-option {
            display: flex;
            align-items: center;
            padding: 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: #f9fafb;
        }
        .role-option:hover { background: #f3f4f6; border-color: #d1d5db; }
        .role-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #2563EB;
            cursor: pointer;
        }
        .role-option span {
            margin-left: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            color: #475569;
        }
        .role-option.selected {
            background: #EFF6FF;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .role-option.selected span { font-weight: 600; color: #1E3A8A; }
        
        .btn-confirm-role {
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
            margin-top: 12px;
            box-shadow: 0 4px 18px rgba(37, 99, 235, 0.35);
            letter-spacing: 0.5px;
        }
        .btn-confirm-role:hover { 
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.45); 
        }
        .btn-confirm-role:active { transform: translateY(0); }
        
        .role-footer {
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 24px;
            line-height: 1.5;
        }

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
            <div class="role-card">
                <div class="role-title">
                    <i class="fa-solid fa-user-check"></i>
                    โปรดเลือกระบบที่ต้องการใช้งาน
                </div>
                
                <form action="{{ route('role.process') }}" method="POST" id="role-form">
                    @csrf
                    <div class="role-options">
                        @php $firstRoleSet = false; @endphp
                        
                        @if(isset($roles) && in_array('admin', $roles))
                        <label class="role-option {{ !$firstRoleSet ? 'selected' : '' }}">
                            <input type="radio" name="system_role" value="admin" {{ !$firstRoleSet ? 'checked' : '' }}>
                            <span>ผู้ดูแลระบบ (Admin)</span>
                        </label>
                        @php $firstRoleSet = true; @endphp
                        @endif

                        @if(isset($roles) && in_array('executive', $roles))
                        <label class="role-option {{ !$firstRoleSet ? 'selected' : '' }}">
                            <input type="radio" name="system_role" value="executive" {{ !$firstRoleSet ? 'checked' : '' }}>
                            <span>ผู้บริหาร</span>
                        </label>
                        @php $firstRoleSet = true; @endphp
                        @endif

                        @if(isset($roles) && in_array('head', $roles))
                        <label class="role-option {{ !$firstRoleSet ? 'selected' : '' }}">
                            <input type="radio" name="system_role" value="head" {{ !$firstRoleSet ? 'checked' : '' }}>
                            <span>หัวหน้างาน</span>
                        </label>
                        @php $firstRoleSet = true; @endphp
                        @endif

                        @if(isset($roles) && in_array('technician', $roles))
                        <label class="role-option {{ !$firstRoleSet ? 'selected' : '' }}">
                            <input type="radio" name="system_role" value="technician" {{ !$firstRoleSet ? 'checked' : '' }}>
                            <span>เจ้าหน้าที่ซ่อมบำรุง</span>
                        </label>
                        @php $firstRoleSet = true; @endphp
                        @endif

                        <label class="role-option {{ !$firstRoleSet ? 'selected' : '' }}">
                            <input type="radio" name="system_role" value="teacher" {{ !$firstRoleSet ? 'checked' : '' }}>
                            <span>แจ้งซ่อม</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-confirm-role" id="btn-confirm-role">เข้าสู่ระบบ</button>
                </form>
                
                <div class="role-footer">
                    © 2026 ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ<br>วิทยาลัยเทคนิคยะลา
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
             const roleOptions = document.querySelectorAll('.role-option');
             roleOptions.forEach(option => {
                 option.addEventListener('click', function() {
                     roleOptions.forEach(opt => opt.classList.remove('selected'));
                     this.classList.add('selected');
                     const radio = this.querySelector('input[type="radio"]');
                     radio.checked = true;
                 });
             });
             
             document.getElementById('role-form').addEventListener('submit', function(e) {
                 const btn = document.getElementById('btn-confirm-role');
                 btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังเข้าสู่ระบบ...';
                 btn.disabled = true;
             });
        });
    </script>
</body>

</html>

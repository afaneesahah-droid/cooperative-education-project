<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ส่วนงานผู้ดูแลระบบ (Admin Command Terminal) - วิทยาลัยเทคนิคยะลา</title>

    <!-- Tailwind CSS & FontAwesome & Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&family=Fira+Code:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <style>
        html, body { height: 100%; height: 100dvh; margin: 0; padding: 0; overflow: hidden; }
        * { font-family: 'Prompt', sans-serif; }
        .font-mono { font-family: 'Fira Code', monospace; }
        .premium-glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05); }
        .premium-white-card { background: #FFFFFF; border: 1px solid #F1F5F9; box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #F8FAFC; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 8px; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased h-screen h-[100dvh] max-h-screen max-h-[100dvh] w-full max-w-full flex flex-col font-semibold selection:bg-blue-500 selection:text-white overflow-hidden">

    <!-- 1. Header Bar (ความสูงคงที่ ด้านบนสุดเต็มความกว้าง) -->
    @include('dashboards.admin.header')

    <!-- 2. Main Layout Container (เต็มความสูงหน้าจอส่วนที่เหลือ ยืดหยุ่นไม่ล้นจอ) -->
    <div class="flex-1 min-h-0 flex flex-row w-full overflow-hidden relative">
        
        <!-- Sidebar Navigation (ฝั่งซ้ายเต็มความสูง) -->
        @include('dashboards.admin.sidebar')

        <!-- Main Content Area (พื้นที่เนื้อหาหลักฝั่งขวา เลื่อนแนวตั้งได้เฉพาะส่วนเนื้อหา scrollbar สวยงาม) -->
        <main class="flex-1 min-w-0 min-h-0 h-full overflow-y-auto custom-scrollbar bg-slate-100 p-3 sm:p-4 lg:p-6">
            @include('dashboards.admin.tabs.users')
            @include('dashboards.admin.tabs.assets')
            @include('dashboards.admin.tabs.personnel')
            @include('dashboards.admin.tabs.repair_projects')
            @include('dashboards.admin.tabs.basic_data')
        </main>
    </div>

    <!-- Modals & Scripts (Must be at bottom of body) -->
    @include('dashboards.admin.modals')
    @include('dashboards.admin.scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('admin-profile-btn');
            var modal = document.getElementById('modal-admin-profile');
            
            if (btn && modal) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.style.display = 'flex';
                    modal.classList.remove('hidden');
                });
            }

            @if(session('profile_success'))
                Swal.fire({
                    scrollbarPadding: false,
                    icon: 'success',
                    title: 'บันทึกโปรไฟล์เรียบร้อย',
                    text: '{{ session('profile_success') }}',
                    showConfirmButton: false,
                    timer: 2000,
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        title: 'text-lg font-bold text-slate-800'
                    }
                });
            @endif

            @if($errors->any() && session('active_tab') == 'tab-profile')
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.remove('hidden');
                }
                Swal.fire({
                    scrollbarPadding: false,
                    icon: 'error',
                    title: 'ข้อมูลไม่ถูกต้อง',
                    html: '<ul class="text-left text-sm text-red-500 list-disc list-inside mb-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                    confirmButtonText: 'ตกลง',
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        title: 'text-lg font-bold text-slate-800',
                        confirmButton: 'px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold'
                    }
                });
            @endif
        });
    </script>
</body>
</html>

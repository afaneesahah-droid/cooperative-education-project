<!-- ส่วนหัวหน้าจอสำหรับผู้บริหาร (Executive Header) -->
<header class="h-16 sm:h-20 text-white sticky top-0 z-40 flex items-center justify-between px-3 sm:px-6 shadow-md shrink-0 w-full max-w-full" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <div class="flex items-center space-x-2.5 sm:space-x-3.5 min-w-0 mr-2">
        <!-- ปุ่ม Hamburger สำหรับ Mobile & Tablet (< 1024px) -->
        <button type="button" onclick="toggleExecutiveMobileSidebar()" class="lg:hidden w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 active:bg-white/30 flex items-center justify-center text-white transition focus:outline-none shrink-0" aria-label="เปิดเมนู">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <div class="w-9 h-9 sm:w-12 sm:h-12 flex items-center justify-center bg-white rounded-full p-0.5 shrink-0 shadow-sm">
            <img src="{{ asset('images/logo.png') }}" alt="YTC Logo" class="w-full h-full object-contain drop-shadow-sm">
        </div>
        <div class="min-w-0">
            <h1 class="text-xs sm:text-base md:text-lg lg:text-xl font-black text-white tracking-tight leading-tight truncate">ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</h1>
        </div>
    </div>
    
    <div class="relative">
        <div id="exec-profile-btn" onclick="toggleProfileDropdown()" class="flex items-center bg-blue-900/30 hover:bg-blue-900/50 transition-colors rounded-full pr-1 pl-4 py-1 cursor-pointer">
            <div class="hidden sm:flex flex-col text-right mr-3">
                <span class="text-sm font-black text-white leading-tight" id="header-user-name">{{ $user->name }}</span>
                <span class="text-xs text-blue-200 font-bold" id="header-user-position"><i class="fa-solid fa-user-shield"></i> {{ $user->position ?: 'ผู้บริหาร' }}</span>
            </div>
            <div class="w-10 h-10 bg-white text-slate-700 rounded-full flex items-center justify-center font-black overflow-hidden flex-shrink-0 shadow-sm">
                @if($user->profile_photo)
                    <img src="{{ asset('uploads/profiles/' . $user->profile_photo) }}" alt="Profile Photo" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($user->name ?? 'EX', 0, 2)) }}
                @endif
            </div>
        </div>

        <!-- Dropdown Menu -->
        <div id="exec-profile-dropdown" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-50">
            <div class="p-2 space-y-1">
                <a href="javascript:void(0)" onclick="goToProfile()" class="flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition group">
                    <i class="fa-solid fa-user text-base text-slate-500 group-hover:text-slate-700 transition-colors"></i>
                    ดูโปรไฟล์
                </a>
                <hr class="border-slate-100 my-1">
                <a href="javascript:void(0)" onclick="confirmLogout()" class="flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 hover:text-red-600 rounded-xl transition group">
                    <i class="fa-solid fa-right-from-bracket text-base text-red-400 group-hover:text-red-500 transition-colors"></i>
                    ออกจากระบบ
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('exec-profile-dropdown');
        if (dropdown) {
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                setTimeout(() => {
                    dropdown.classList.remove('opacity-0', 'scale-95');
                    dropdown.classList.add('opacity-100', 'scale-100');
                }, 10);
            } else {
                dropdown.classList.remove('opacity-100', 'scale-100');
                dropdown.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
            }
        }
    }

    function goToProfile() {
        toggleProfileDropdown();
        const modal = document.getElementById('modal-executive-profile');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
        if (typeof openProfileModal === 'function') {
            openProfileModal();
        }
    }

    function confirmLogout() {
        const dropdown = document.getElementById('exec-profile-dropdown');
        if (dropdown && !dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('opacity-100', 'scale-100');
            dropdown.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 200);
        }
        const modal = document.getElementById('logout-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
    }

    function closeLogoutModal() {
        const modal = document.getElementById('logout-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('click', function(event) {
        const btn = document.getElementById('exec-profile-btn');
        const dropdown = document.getElementById('exec-profile-dropdown');
        if (btn && dropdown && !btn.contains(event.target) && !dropdown.contains(event.target)) {
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('opacity-100', 'scale-100');
                dropdown.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
            }
        }
    });
</script>

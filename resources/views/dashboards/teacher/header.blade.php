<header class="h-16 sm:h-20 text-white sticky top-0 z-40 flex items-center justify-between px-3 sm:px-6 shadow-md shrink-0 w-full max-w-full" style="background: linear-gradient(90deg, #1E40AF 0%, #2563EB 50%, #3B82F6 100%);">
    <div class="flex items-center space-x-2.5 sm:space-x-3 min-w-0 mr-2">
        <div class="w-9 h-9 sm:w-12 sm:h-12 flex items-center justify-center bg-white rounded-full p-0.5 shrink-0 shadow-sm">
            <img src="{{ asset('images/logo.png') }}" alt="YTC Logo" class="w-full h-full object-contain drop-shadow-sm">
        </div>
        <div class="min-w-0">
            <h2 class="text-xs sm:text-base md:text-lg lg:text-xl font-black text-white leading-tight truncate">ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</h2>
        </div>
    </div>
    
    <div class="relative">
        <div id="teacher-profile-btn" onclick="toggleProfileDropdown(event)" class="flex items-center bg-blue-900/30 hover:bg-blue-900/50 transition-colors rounded-full pr-1 pl-4 py-1 cursor-pointer select-none">
            <div class="hidden sm:flex flex-col text-right mr-3">
                <span class="text-sm font-black text-white leading-tight">{{ $user->name ?? 'ผู้ใช้งาน' }}</span>
                <span class="text-xs text-blue-200 font-bold"><i class="fa-solid fa-shield-halved"></i> {{ $user->role ?? 'User' }}</span>
            </div>
            <div class="w-10 h-10 bg-white text-slate-700 rounded-full flex items-center justify-center font-black overflow-hidden flex-shrink-0 shadow-sm">
                @if(isset($user->profile_photo) && $user->profile_photo)
                    <img src="{{ asset('uploads/profiles/' . $user->profile_photo) }}" alt="Profile Photo" class="w-full h-full object-cover">
                @else
                    <i class="fa-regular fa-user text-lg"></i>
                @endif
            </div>
        </div>

        <!-- Profile Dropdown Menu -->
        <div id="teacher-profile-dropdown" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-50">
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
    function toggleProfileDropdown(e) {
        if(e) e.stopPropagation();
        const dropdown = document.getElementById('teacher-profile-dropdown');
        if (!dropdown) return;
        
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            setTimeout(() => {
                dropdown.classList.remove('opacity-0', 'scale-95');
                dropdown.classList.add('opacity-100', 'scale-100');
            }, 10);
        } else {
            closeDropdownMenu();
        }
    }

    function closeDropdownMenu() {
        const dropdown = document.getElementById('teacher-profile-dropdown');
        if (dropdown && !dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('opacity-100', 'scale-100');
            dropdown.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 200);
        }
    }

    function goToProfile() {
        closeDropdownMenu();
        const modal = document.getElementById('modal-teacher-profile');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
        if (typeof openProfileModal === 'function') {
            openProfileModal();
        }
    }

    function confirmLogout() {
        closeDropdownMenu();
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
        const btn = document.getElementById('teacher-profile-btn');
        const dropdown = document.getElementById('teacher-profile-dropdown');
        if (btn && dropdown && !btn.contains(event.target) && !dropdown.contains(event.target)) {
            closeDropdownMenu();
        }
    });
</script>
<header class="h-16 sm:h-20 text-white sticky top-0 z-40 flex items-center justify-between px-3 sm:px-6 shadow-md shrink-0 w-full" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <!-- ปุ่มเปิดเมนู Mobile และแบรนดิ้ง -->
    <div class="flex items-center space-x-2 sm:space-x-3.5 min-w-0 flex-1 mr-2">
        <!-- Hamburger Menu Button for Mobile & Tablet -->
        <button type="button" id="head-mobile-menu-btn" onclick="toggleHeadMobileSidebar()" class="lg:hidden p-2 rounded-xl text-white hover:bg-white/15 active:bg-white/20 transition-all focus:outline-none shrink-0" aria-label="เปิดเมนูนำทาง">
            <i class="fa-solid fa-bars text-lg sm:text-xl"></i>
        </button>

        <div class="w-9 h-9 sm:w-12 sm:h-12 flex items-center justify-center bg-white rounded-full p-0.5 shrink-0 shadow-sm">
            <img src="{{ asset('images/logo.png') }}" alt="YTC Logo" class="w-full h-full object-contain drop-shadow-sm">
        </div>
        <div class="min-w-0 flex-1">            
            <h1 class="text-xs sm:text-base md:text-lg lg:text-xl font-black text-white leading-tight truncate">ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</h1>
        </div>
    </div>

    <!-- ส่วนโปรไฟล์ด้านขวา -->
    <div class="relative shrink-0">
        <div id="head-profile-btn" onclick="toggleProfileDropdown(event)" class="flex items-center bg-blue-900/30 hover:bg-blue-900/50 transition-colors rounded-full pr-1 pl-2 sm:pl-4 py-1 cursor-pointer select-none">
            <div class="hidden sm:flex flex-col text-right mr-3">
                <span class="text-xs sm:text-sm font-black text-white leading-tight" id="header-user-name">{{ $user->name }}</span>
                <span class="text-[10px] sm:text-xs text-blue-200 font-bold" id="header-user-position"><i class="fa-solid fa-user-shield"></i> {{ $user->position ?: 'หัวหน้าศูนย์ข้อมูล' }}</span>
            </div>
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white text-slate-700 rounded-full flex items-center justify-center font-black overflow-hidden flex-shrink-0 shadow-sm">
                @if($user->profile_photo)
                    <img src="{{ asset('uploads/profiles/' . $user->profile_photo) }}" alt="Profile Photo" class="w-full h-full object-cover">
                @else
                    <i class="fa-regular fa-user text-base sm:text-lg"></i>
                @endif
            </div>
        </div>

        <!-- Dropdown Menu -->
        <div id="head-profile-dropdown" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-50">
            <div class="p-2 space-y-1">
                <a href="javascript:void(0)" onclick="goToProfile(event)" class="flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition group">
                    <i class="fa-solid fa-user text-base text-slate-500 group-hover:text-slate-700 transition-colors"></i>
                    ดูโปรไฟล์
                </a>
                <hr class="border-slate-100 my-1">
                <a href="javascript:void(0)" onclick="confirmLogout(event)" class="flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 hover:text-red-600 rounded-xl transition group">
                    <i class="fa-solid fa-right-from-bracket text-base text-red-400 group-hover:text-red-500 transition-colors"></i>
                    ออกจากระบบ
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleProfileDropdown(e) {
        if (e) e.stopPropagation();
        const dropdown = document.getElementById('head-profile-dropdown');
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
        const dropdown = document.getElementById('head-profile-dropdown');
        if (dropdown && !dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('opacity-100', 'scale-100');
            dropdown.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                dropdown.classList.add('hidden');
            }, 200);
        }
    }

    function goToProfile(e) {
        if (e) e.stopPropagation();
        closeDropdownMenu();
        const modal = document.getElementById('modal-head-profile');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
        if (typeof window.openProfileModal === 'function') {
            window.openProfileModal();
        }
    }

    function closeProfileModal() {
        const modal = document.getElementById('modal-head-profile');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function confirmLogout(e) {
        if (e) e.stopPropagation();
        closeDropdownMenu();
        const modal = document.getElementById('logout-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    }

    function closeLogoutModal() {
        const modal = document.getElementById('logout-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    window.toggleProfileDropdown = toggleProfileDropdown;
    window.closeDropdownMenu = closeDropdownMenu;
    window.goToProfile = goToProfile;
    window.closeProfileModal = closeProfileModal;
    window.confirmLogout = confirmLogout;
    window.closeLogoutModal = closeLogoutModal;

    document.addEventListener('click', function(event) {
        const btn = document.getElementById('head-profile-btn');
        const dropdown = document.getElementById('head-profile-dropdown');
        if (btn && dropdown && !btn.contains(event.target) && !dropdown.contains(event.target)) {
            closeDropdownMenu();
        }
    });
</script>

<!-- Header Bar (ความสูงพอดี ไม่ล้นจอ รองรับทุกอุปกรณ์) -->
<header id="admin-main-header" class="h-14 sm:h-16 text-white sticky top-0 z-40 flex items-center justify-between px-3 sm:px-6 shadow-md shrink-0 w-full max-w-full" style="background: linear-gradient(90deg, #1E40AF 0%, #2563EB 50%, #3B82F6 100%);">
    <div class="flex items-center space-x-2.5 sm:space-x-3 min-w-0 mr-2">
        <!-- ปุ่ม Hamburger สำหรับ Mobile & Tablet (< 1024px) -->
        <button type="button" onclick="toggleAdminMobileSidebar()" class="lg:hidden w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/15 hover:bg-white/25 active:bg-white/30 flex items-center justify-center text-white transition focus:outline-none shrink-0 cursor-pointer" aria-label="เปิดเมนู">
            <i class="fa-solid fa-bars text-base sm:text-lg"></i>
        </button>

        <img src="{{ $logoUrl }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="โลโก้วิทยาลัยเทคนิคยะลา"
            class="h-8 w-8 sm:h-10 sm:w-10 object-contain rounded-xl shrink-0 bg-white p-0.5 shadow-sm">
        <div class="min-w-0">
            <h1 class="text-xs sm:text-sm md:text-base lg:text-lg font-black text-white tracking-normal leading-tight truncate">ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</h1>
        </div>
    </div>
    
    <!-- ผู้ควบคุมระบบ (ตรงตามรูปภาพใหม่ที่มี Dropdown) -->
    <div class="relative flex items-center shrink-0">
        <button id="profile-dropdown-btn" onclick="toggleProfileDropdown()" class="flex items-center space-x-2.5 sm:space-x-3 cursor-pointer group focus:outline-none bg-blue-700/20 hover:bg-blue-700/40 rounded-full pl-3 sm:pl-4 pr-1.5 py-1 sm:py-1.5 transition">
            <div class="hidden sm:flex flex-col text-right">
                <span class="text-xs sm:text-sm font-black text-white leading-tight">{{ Auth::user()->name ?: 'แอดมิน ระบบ' }}</span>
                <span class="text-[10px] text-blue-200 font-bold block"><i class="fa-solid fa-shield-halved text-blue-200"></i> {{ Auth::user()->position ?: 'ผู้ดูแลระบบ' }}</span>
            </div>
            <div class="w-8 h-8 sm:w-9 sm:h-9 bg-white text-slate-700 rounded-full flex items-center justify-center font-bold text-xs sm:text-sm shadow-sm shrink-0 border border-slate-200 group-hover:border-blue-300 transition overflow-hidden">
                @if(isset($profilePhotoUrl) && $profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\'fa-regular fa-user\'></i>';">
                @else
                    <i class="fa-regular fa-user"></i>
                @endif
            </div>
        </button>

        <!-- Dropdown Menu -->
        <div id="profile-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden transform origin-top-right transition-all duration-200 z-50">
            <div class="p-2 space-y-1">
                <button onclick="toggleProfileDropdown(); openAdminProfileModal();" class="w-full flex items-center space-x-3 px-4 py-2.5 hover:bg-slate-50 text-slate-700 rounded-xl transition text-sm font-bold text-left">
                    <i class="fa-solid fa-user text-slate-500 w-4 text-center"></i>
                    <span>ดูโปรไฟล์</span>
                </button>
                <div class="h-px bg-slate-100 my-1 mx-2"></div>
                <button onclick="toggleProfileDropdown(); showLogoutModal();" class="w-full flex items-center space-x-3 px-4 py-2.5 hover:bg-red-50 text-red-500 rounded-xl transition text-sm font-bold text-left">
                    <i class="fa-solid fa-right-from-bracket text-red-400 w-4 text-center"></i>
                    <span>ออกจากระบบ</span>
                </button>
            </div>
        </div>
    </div>
</header>
<script>
    console.log('Header loaded');
    
    function toggleProfileDropdown() {
        const menu = document.getElementById('profile-dropdown-menu');
        menu.classList.toggle('hidden');
    }

    // ปิด dropdown เมื่อคลิกที่อื่น
    window.addEventListener('click', function(e) {
        const btn = document.getElementById('profile-dropdown-btn');
        const menu = document.getElementById('profile-dropdown-menu');
        if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>

<!-- 1. เมนู Navigation ด้านซ้าย (Sidebar) สำหรับ Desktop (>= 1024px) -->
<aside class="hidden lg:flex lg:w-72 shrink-0 flex-col text-white h-full shadow-xl border-r border-slate-700/10 z-20" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <nav class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto scrollbar-hide" aria-label="Tabs" id="main-nav-tabs">
                
        <a href="javascript:void(0)" onclick="switchTab('tab-report', this)" class="desktop-tab tab-link active border-l-4 border-white text-blue-900 bg-white shadow-md font-bold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-screwdriver-wrench text-lg"></i> บันทึกข้อมูลการแจ้งซ่อม
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tab-assigned', this)" class="desktop-tab tab-link border-l-4 border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-xl">
            <span class="flex items-center gap-3">
                <i class="fa-solid fa-list-check text-lg"></i> งานที่ได้รับมอบหมาย
            </span>
            @if(isset($pendingAccept) && $pendingAccept->count() > 0)
                <span class="bg-amber-400 text-slate-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm animate-pulse">{{ $pendingAccept->count() }}</span>
            @endif
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tab-history', this)" class="desktop-tab tab-link border-l-4 border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-clock-rotate-left text-lg text-blue-200"></i> ดูประวัติงานซ่อมย้อนหลัง
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tab-requisition', this)" class="desktop-tab tab-link border-l-4 border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-file-invoice-dollar text-lg"></i> บันทึกค่าซ่อม
        </a>
    </nav>
</aside>

<!-- 2. Mobile & Tablet Off-Canvas Drawer (< 1024px) -->
<div id="technician-mobile-backdrop" onclick="closeTechnicianMobileSidebar()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

<aside id="technician-mobile-sidebar" class="fixed top-0 bottom-0 left-0 w-72 max-w-[85vw] text-white z-50 shadow-2xl flex flex-col -translate-x-full transition-transform duration-300 ease-out lg:hidden" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <!-- Header ของ Drawer -->
    <div class="p-4 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-white leading-none">เมนูหลัก</h3>
                <span class="text-[10px] text-blue-200">เจ้าหน้าที่ซ่อมบำรุง</span>
            </div>
        </div>
        <button type="button" onclick="closeTechnicianMobileSidebar()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition focus:outline-none" aria-label="ปิดเมนู">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>

    <!-- รายการเมนูสำหรับ Mobile & Tablet -->
    <nav class="flex-1 py-4 px-3 space-y-2 overflow-y-auto" aria-label="Mobile Tabs">
        <a href="javascript:void(0)" onclick="handleMobileTechnicianTab('tab-report', this)" data-tab="tab-report" class="mobile-technician-tab border-l-4 border-white text-blue-900 bg-white shadow-md font-bold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-screwdriver-wrench text-lg"></i> บันทึกข้อมูลการแจ้งซ่อม
        </a>

        <a href="javascript:void(0)" onclick="handleMobileTechnicianTab('tab-assigned', this)" data-tab="tab-assigned" class="mobile-technician-tab border-l-4 border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-xl">
            <span class="flex items-center gap-3">
                <i class="fa-solid fa-list-check text-lg"></i> งานที่ได้รับมอบหมาย
            </span>
            @if(isset($pendingAccept) && $pendingAccept->count() > 0)
                <span class="bg-amber-400 text-slate-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm">{{ $pendingAccept->count() }}</span>
            @endif
        </a>

        <a href="javascript:void(0)" onclick="handleMobileTechnicianTab('tab-history', this)" data-tab="tab-history" class="mobile-technician-tab border-l-4 border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-clock-rotate-left text-lg"></i> ดูประวัติงานซ่อมย้อนหลัง
        </a>

        <a href="javascript:void(0)" onclick="handleMobileTechnicianTab('tab-requisition', this)" data-tab="tab-requisition" class="mobile-technician-tab border-l-4 border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-file-invoice-dollar text-lg"></i> บันทึกค่าซ่อม
        </a>
    </nav>
</aside>

<script>
    function toggleTechnicianMobileSidebar() {
        const sidebar = document.getElementById('technician-mobile-sidebar');
        const backdrop = document.getElementById('technician-mobile-backdrop');
        if (sidebar && backdrop) {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                backdrop.classList.remove('hidden');
                setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
                sidebar.classList.remove('-translate-x-full');
            } else {
                closeTechnicianMobileSidebar();
            }
        }
    }

    function closeTechnicianMobileSidebar() {
        const sidebar = document.getElementById('technician-mobile-sidebar');
        const backdrop = document.getElementById('technician-mobile-backdrop');
        if (sidebar && backdrop) {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
    }

    function handleMobileTechnicianTab(tabId, el) {
        const desktopTab = document.querySelector(`.desktop-tab[onclick*="${tabId}"]`);
        if (typeof switchTab === 'function') {
            switchTab(tabId, desktopTab || el);
        }
        
        document.querySelectorAll('.mobile-technician-tab').forEach(t => {
            t.classList.remove('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold');
            t.classList.add('border-transparent', 'text-blue-100/80', 'font-semibold');
        });
        if (el) {
            el.classList.remove('border-transparent', 'text-blue-100/80', 'font-semibold');
            el.classList.add('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold');
        }

        closeTechnicianMobileSidebar();
    }
</script>

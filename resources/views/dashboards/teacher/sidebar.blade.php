<!-- Sidebar สำหรับ Desktop -->
<aside class="hidden lg:flex lg:w-72 shrink-0 flex-col text-white h-full shadow-xl border-r border-slate-700/10" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <nav class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto scrollbar-hide" aria-label="Tabs" id="main-nav-tabs">
        <a href="javascript:void(0)" onclick="switchTab('tab-report', this)" class="desktop-tab tab-link border-l-4 border-white text-blue-900 bg-white shadow-md font-bold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg">
            <i class="fa-solid fa-screwdriver-wrench text-lg"></i> แจ้งซ่อม
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tab-status', this)" class="desktop-tab tab-link border-l-4 border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-lg">
            <span class="flex items-center gap-3"><i class="fa-solid fa-bars-progress text-lg"></i> ติดตามสถานะ</span>
            @if(isset($unratedCount) && $unratedCount > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-black bg-red-500 text-white rounded-full animate-bounce">{{ $unratedCount }}</span>
            @endif
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tab-history', this)" class="desktop-tab tab-link border-l-4 border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg">
            <i class="fa-solid fa-clock-rotate-left text-lg"></i> ดูประวัติงานซ่อมย้อนหลัง
        </a>
    

        <a href="javascript:void(0)" onclick="switchTab('tab-requisition', this)" class="desktop-tab tab-link border-l-4 border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg">
            <i class="fa-solid fa-file-invoice-dollar text-lg"></i> บันทึกค่าซ่อม
        </a>
    </nav>
</aside>

<!-- Bottom Navigation Bar สำหรับ Mobile & Tablet (< 1024px) -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-40 px-2 sm:px-4 py-2 flex justify-around shadow-[0_-4px_20px_rgba(0,0,0,0.06)] pb-[max(0.5rem,env(safe-area-inset-bottom))]">
    <button type="button" onclick="switchTab('tab-report', this)" class="desktop-tab flex flex-col items-center justify-center text-blue-600 font-bold text-[11px] sm:text-xs py-1 px-2 sm:px-3 rounded-xl transition">
        <i class="fa-solid fa-screwdriver-wrench text-base sm:text-lg mb-0.5"></i>
        <span>แจ้งซ่อม</span>
    </button>
    <button type="button" onclick="switchTab('tab-status', this)" class="desktop-tab relative flex flex-col items-center justify-center text-slate-500 font-semibold text-[11px] sm:text-xs py-1 px-2 sm:px-3 rounded-xl transition">
        <i class="fa-solid fa-bars-progress text-base sm:text-lg mb-0.5"></i>
        <span>ติดตามสถานะ</span>
        @if(isset($unratedCount) && $unratedCount > 0)
            <span class="absolute top-0 right-2 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
            <span class="absolute top-0 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
        @endif
    </button>
    <button type="button" onclick="switchTab('tab-history', this)" class="desktop-tab flex flex-col items-center justify-center text-slate-500 font-semibold text-[11px] sm:text-xs py-1 px-2 sm:px-3 rounded-xl transition">
        <i class="fa-solid fa-clock-rotate-left text-base sm:text-lg mb-0.5"></i>
        <span>ดูประวัติ</span>
    </button>

    <button type="button" onclick="switchTab('tab-requisition', this)" class="desktop-tab flex flex-col items-center justify-center text-slate-500 font-semibold text-[11px] sm:text-xs py-1 px-2 sm:px-3 rounded-xl transition">
        <i class="fa-solid fa-file-invoice-dollar text-base sm:text-lg mb-0.5"></i>
        <span>บันทึกค่าซ่อม</span>
    </button>
</div>
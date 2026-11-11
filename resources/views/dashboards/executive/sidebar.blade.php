@php
    $currentTab = request('tab', 'summary');
    if (str_starts_with($currentTab, 'tab-')) {
        $currentTab = substr($currentTab, 4);
    }
    $isSummary = ($currentTab !== 'history');
@endphp
<!-- 1. เมนู Navigation ด้านซ้าย (Sidebar) สำหรับ Desktop (>= 1024px) -->
<aside class="hidden lg:flex lg:w-72 shrink-0 flex-col text-white h-full shadow-xl border-r border-slate-700/10 z-20" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <nav class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto scrollbar-hide" aria-label="Tabs" id="main-nav-tabs">
        <a href="javascript:void(0)" onclick="switchTab('tab-summary', this)" class="desktop-tab tab-link border-l-4 {{ $isSummary ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-chart-line text-lg"></i> ข้อมูลสรุปการซ่อม
        </a>

        <a href="javascript:void(0)" onclick="switchTab('tab-history', this)" class="desktop-tab tab-link border-l-4 {{ !$isSummary ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-xl">
            <span class="flex items-center gap-3"><i class="fa-solid fa-clock-rotate-left text-lg"></i> ดูประวัติงานซ่อมย้อนหลัง</span>
            @if(isset($requests) && $requests->count() > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-black bg-white/20 text-white rounded-full">{{ $requests->count() }}</span>
            @endif
        </a>
    </nav>
</aside>

<!-- 2. Mobile & Tablet Off-Canvas Drawer (< 1024px) -->
<div id="executive-mobile-backdrop" onclick="closeExecutiveMobileSidebar()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

<aside id="executive-mobile-sidebar" class="fixed top-0 bottom-0 left-0 w-72 max-w-[85vw] text-white z-50 shadow-2xl flex flex-col -translate-x-full transition-transform duration-300 ease-out lg:hidden" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <!-- Header ของ Drawer -->
    <div class="p-4 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-white leading-none">เมนูหลัก</h3>
                <span class="text-[10px] text-blue-200">ส่วนผู้บริหาร</span>
            </div>
        </div>
        <button type="button" onclick="closeExecutiveMobileSidebar()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition focus:outline-none" aria-label="ปิดเมนู">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>

    <!-- รายการเมนูสำหรับ Mobile & Tablet -->
    <nav class="flex-1 py-4 px-3 space-y-2 overflow-y-auto" aria-label="Mobile Tabs">
        <a href="javascript:void(0)" onclick="handleMobileExecutiveTab('tab-summary', this)" data-tab="tab-summary" class="mobile-executive-tab border-l-4 {{ $isSummary ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-chart-line text-lg"></i> ข้อมูลสรุปการซ่อม
        </a>

        <a href="javascript:void(0)" onclick="handleMobileExecutiveTab('tab-history', this)" data-tab="tab-history" class="mobile-executive-tab border-l-4 {{ !$isSummary ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-xl">
            <span class="flex items-center gap-3"><i class="fa-solid fa-clock-rotate-left text-lg"></i> ดูประวัติงานซ่อมย้อนหลัง</span>
            @if(isset($requests) && $requests->count() > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-black bg-white/20 text-white rounded-full">{{ $requests->count() }}</span>
            @endif
        </a>
    </nav>
</aside>

<script>
    function toggleExecutiveMobileSidebar() {
        const sidebar = document.getElementById('executive-mobile-sidebar');
        const backdrop = document.getElementById('executive-mobile-backdrop');
        if (sidebar && backdrop) {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                backdrop.classList.remove('hidden');
                setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
                sidebar.classList.remove('-translate-x-full');
            } else {
                closeExecutiveMobileSidebar();
            }
        }
    }

    function closeExecutiveMobileSidebar() {
        const sidebar = document.getElementById('executive-mobile-sidebar');
        const backdrop = document.getElementById('executive-mobile-backdrop');
        if (sidebar && backdrop) {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
    }

    function handleMobileExecutiveTab(tabId, el) {
        if (typeof switchTab === 'function') {
            switchTab(tabId);
        }
        closeExecutiveMobileSidebar();
    }
</script>

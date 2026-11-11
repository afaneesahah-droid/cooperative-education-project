<!-- 1. เมนู Navigation ด้านซ้าย (Sidebar) สำหรับ Desktop & Notebook -->
<aside class="hidden lg:flex lg:w-64 xl:w-72 shrink-0 flex-col text-white h-full shadow-xl border-r border-slate-700/10" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <nav class="flex-1 py-4 lg:py-5 px-3 lg:px-4 space-y-1 lg:space-y-1.5 overflow-y-auto scrollbar-hide" aria-label="Tabs" id="main-nav-tabs">
        
        <!-- จัดการสิทธิ์ผู้ใช้งาน -->
        <a href="{{ route('dashboard.admin') }}?tab=tab-users" id="nav-tab-users-desktop" onclick="switchAdminTab('tab-users', this); history.pushState(null, '', '?tab=tab-users'); return false;" class="desktop-tab tab-link border-l-4 {{ $activeTab === 'tab-users' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-lg">
            <span class="flex items-center gap-3">
                <i class="fa-solid fa-user-shield text-lg"></i> จัดการสิทธิ์ผู้ใช้งาน
            </span>
            @if(isset($pendingCount) && $pendingCount > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-black bg-red-500 text-white rounded-full animate-pulse">{{ $pendingCount }}</span>
            @endif
        </a>

        <!-- จัดการข้อมูลพื้นฐาน (Accordion Dropdown) -->
        <div class="space-y-1">
            <button type="button" onclick="toggleBasicDataMenu()" id="nav-tab-basic-data-desktop" class="w-full desktop-tab tab-link border-l-4 {{ $activeTab === 'tab-basic-data' ? 'border-white text-white bg-white/15 font-bold' : 'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-lg cursor-pointer">
                <span class="flex items-center gap-3">
                    <i class="fa-solid fa-sliders text-lg"></i> จัดการข้อมูลพื้นฐาน
                </span>
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" id="basic-data-chevron"></i>
            </button>

            <!-- เมนูย่อยข้อมูลพื้นฐาน -->
            <div id="basic-data-sub-menu" class="pl-4 space-y-1" style="display: {{ $activeTab === 'tab-basic-data' ? 'block' : 'none' }};">
                <a href="{{ route('dashboard.admin') }}?tab=tab-basic-data&sub=device-types" onclick="handleSubMenuClick('device-types', this); return false;" class="sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="device-types">
                    <i class="fa-solid fa-boxes-stacked text-xs"></i> ประเภททรัพย์สิน
                </a>
                <a href="{{ route('dashboard.admin') }}?tab=tab-basic-data&sub=personnel-types" onclick="handleSubMenuClick('personnel-types', this); return false;" class="sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="personnel-types">
                    <i class="fa-solid fa-address-book text-xs"></i> ประเภทบุคลากร
                </a>
                <a href="{{ route('dashboard.admin') }}?tab=tab-basic-data&sub=buildings" onclick="handleSubMenuClick('buildings', this); return false;" class="sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="buildings">
                    <i class="fa-solid fa-building text-xs"></i> อาคาร
                </a>
                <a href="{{ route('dashboard.admin') }}?tab=tab-basic-data&sub=departments" onclick="handleSubMenuClick('departments', this); return false;" class="sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="departments">
                    <i class="fa-solid fa-sitemap text-xs"></i> หน่วยงาน
                </a>
            </div>
        </div>

        <!-- จัดการข้อมูลทรัพย์สิน -->
        <a href="{{ route('dashboard.admin') }}?tab=tab-assets" id="nav-tab-assets-desktop" onclick="switchAdminTab('tab-assets', this); history.pushState(null, '', '?tab=tab-assets'); return false;" class="desktop-tab tab-link border-l-4 {{ $activeTab === 'tab-assets' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg">
            <i class="fa-solid fa-boxes-stacked text-lg"></i> จัดการข้อมูลทรัพย์สิน
        </a>

        <!-- จัดการข้อมูลบุคลากร -->
        <a href="{{ route('dashboard.admin') }}?tab=tab-personnel" id="nav-tab-personnel-desktop" onclick="switchAdminTab('tab-personnel', this); history.pushState(null, '', '?tab=tab-personnel'); return false;" class="desktop-tab tab-link border-l-4 {{ $activeTab === 'tab-personnel' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg">
            <i class="fa-solid fa-users-gear text-lg"></i> จัดการข้อมูลบุคลากร
        </a>

        <!-- จัดการข้อมูลโครงการซ่อมครุภัณฑ์ -->
        <a href="{{ route('dashboard.admin') }}?tab=tab-repair-projects" id="nav-tab-repair-projects-desktop" onclick="switchAdminTab('tab-repair-projects', this); history.pushState(null, '', '?tab=tab-repair-projects'); return false;" class="desktop-tab tab-link border-l-4 {{ $activeTab === 'tab-repair-projects' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg">
            <i class="fa-solid fa-screwdriver-wrench text-lg"></i> จัดการข้อมูลโครงการซ่อมครุภัณฑ์
        </a>
    </nav>
</aside>

<!-- 2. Mobile & Tablet Off-Canvas Drawer (< 1024px) -->
<div id="admin-mobile-backdrop" onclick="closeAdminMobileSidebar()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

<aside id="admin-mobile-sidebar" class="fixed top-0 bottom-0 left-0 w-72 max-w-[85vw] text-white z-50 shadow-2xl flex flex-col -translate-x-full transition-transform duration-300 ease-out lg:hidden" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);">
    <!-- Header ของ Drawer -->
    <div class="p-4 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-white leading-none">เมนูหลัก</h3>
                <span class="text-[10px] text-blue-200">ผู้ดูแลระบบ (Admin)</span>
            </div>
        </div>
        <button type="button" onclick="closeAdminMobileSidebar()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition focus:outline-none" aria-label="ปิดเมนู">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>

    <!-- รายการเมนูสำหรับ Mobile & Tablet -->
    <nav class="flex-1 py-4 px-3 space-y-1.5 overflow-y-auto" aria-label="Mobile Tabs">
        <!-- จัดการสิทธิ์ผู้ใช้งาน -->
        <a href="javascript:void(0)" onclick="handleMobileAdminTab('tab-users', this)" class="mobile-admin-tab border-l-4 {{ $activeTab === 'tab-users' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-xl">
            <span class="flex items-center gap-3">
                <i class="fa-solid fa-user-shield text-lg"></i> จัดการสิทธิ์ผู้ใช้งาน
            </span>
            @if(isset($pendingCount) && $pendingCount > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-black bg-red-500 text-white rounded-full">{{ $pendingCount }}</span>
            @endif
        </a>

        <!-- จัดการข้อมูลพื้นฐาน (Accordion Dropdown) -->
        <div class="space-y-1">
            <button type="button" id="nav-tab-basic-data-mobile" onclick="toggleMobileBasicDataMenu()" class="w-full mobile-admin-tab border-l-4 {{ $activeTab === 'tab-basic-data' ? 'border-white text-white bg-white/15 font-bold' : 'border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center justify-between rounded-xl cursor-pointer">
                <span class="flex items-center gap-3">
                    <i class="fa-solid fa-sliders text-lg"></i> จัดการข้อมูลพื้นฐาน
                </span>
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" id="mobile-basic-data-chevron"></i>
            </button>

            <!-- เมนูย่อยข้อมูลพื้นฐานสำหรับ Mobile -->
            <div id="mobile-basic-data-sub-menu" class="pl-4 space-y-1" style="display: {{ $activeTab === 'tab-basic-data' ? 'block' : 'none' }};">
                <a href="javascript:void(0)" onclick="handleMobileAdminSubMenu('device-types', this)" class="mobile-sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="device-types">
                    <i class="fa-solid fa-boxes-stacked text-xs"></i> ประเภททรัพย์สิน
                </a>
                <a href="javascript:void(0)" onclick="handleMobileAdminSubMenu('personnel-types', this)" class="mobile-sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="personnel-types">
                    <i class="fa-solid fa-address-book text-xs"></i> ประเภทบุคลากร
                </a>
                <a href="javascript:void(0)" onclick="handleMobileAdminSubMenu('buildings', this)" class="mobile-sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="buildings">
                    <i class="fa-solid fa-building text-xs"></i> อาคาร
                </a>
                <a href="javascript:void(0)" onclick="handleMobileAdminSubMenu('departments', this)" class="mobile-sub-menu-item text-blue-100/80 hover:text-white hover:bg-white/10 py-2.5 px-4 text-xs font-semibold rounded-lg flex items-center gap-2.5 transition" data-sub="departments">
                    <i class="fa-solid fa-sitemap text-xs"></i> หน่วยงาน
                </a>
            </div>
        </div>

        <!-- จัดการข้อมูลทรัพย์สิน -->
        <a href="javascript:void(0)" onclick="handleMobileAdminTab('tab-assets', this)" class="mobile-admin-tab border-l-4 {{ $activeTab === 'tab-assets' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-boxes-stacked text-lg"></i> จัดการข้อมูลทรัพย์สิน
        </a>

        <!-- จัดการข้อมูลบุคลากร -->
        <a href="javascript:void(0)" onclick="handleMobileAdminTab('tab-personnel', this)" class="mobile-admin-tab border-l-4 {{ $activeTab === 'tab-personnel' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-users-gear text-lg"></i> จัดการข้อมูลบุคลากร
        </a>

        <!-- จัดการข้อมูลโครงการซ่อมครุภัณฑ์ -->
        <a href="javascript:void(0)" onclick="handleMobileAdminTab('tab-repair-projects', this)" class="mobile-admin-tab border-l-4 {{ $activeTab === 'tab-repair-projects' ? 'border-white text-blue-900 bg-white shadow-md font-bold' : 'border-transparent text-blue-100/80 hover:bg-white/10 hover:text-white font-semibold' }} whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-xl">
            <i class="fa-solid fa-screwdriver-wrench text-lg"></i> จัดการข้อมูลโครงการซ่อมครุภัณฑ์
        </a>
    </nav>
</aside>

<!-- 3. JavaScript จัดการแท็บย่อยข้อมูลพื้นฐาน -->
<script>
    window.currentBasicSubTab = window.currentBasicSubTab || 'device-types';

    function toggleBasicDataMenu() {
        if (typeof playBeep === 'function') playBeep();
        const subMenu = document.getElementById('basic-data-sub-menu');
        const chevron = document.getElementById('basic-data-chevron');
        const desktopBtn = document.getElementById('nav-tab-basic-data-desktop');
        const isActive = desktopBtn && desktopBtn.classList.contains('bg-white');

        if (!subMenu) return;

        if (subMenu.style.display === 'none' || subMenu.style.display === '') {
            subMenu.style.display = 'block';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            subMenu.style.display = 'none';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }

        if (typeof switchAdminTab === 'function' && !isActive) {
            switchAdminTab('tab-basic-data', desktopBtn);
        }
    }

    function handleSubMenuClick(subKey, el) {
        history.pushState(null, '', '?tab=tab-basic-data&sub=' + subKey);

        document.querySelectorAll('.tab-content').forEach(tab => {
            if (tab.id === 'tab-basic-data') {
                tab.style.display = 'block';
            } else {
                tab.style.display = 'none';
            }
        });

        window.switchBasicDataSubTab(subKey);

        document.querySelectorAll('.sub-menu-item').forEach(item => {
            item.classList.remove('bg-white', 'text-blue-900', 'font-bold', 'shadow-sm');
            item.classList.add('text-blue-100/80', 'font-semibold');
        });
        if (el) {
            el.classList.remove('text-blue-100/80');
            el.classList.add('bg-white', 'text-blue-900', 'font-bold', 'shadow-sm');
        }

        document.querySelectorAll('.desktop-tab, .mobile-admin-tab').forEach(tab => {
            tab.classList.remove('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold', 'text-white', 'bg-white/15');
            tab.classList.add('border-transparent', 'text-blue-100/70', 'font-semibold');
        });
        const mainTab = document.getElementById('nav-tab-basic-data-desktop');
        const mobileTab = document.getElementById('nav-tab-basic-data-mobile');
        if (mainTab) {
            mainTab.classList.remove('border-transparent', 'text-blue-100/70', 'font-semibold');
            mainTab.classList.add('border-white', 'text-white', 'bg-white/15', 'font-bold');
        }
        if (mobileTab) {
            mobileTab.classList.remove('border-transparent', 'text-blue-100/70', 'font-semibold');
            mobileTab.classList.add('border-white', 'text-white', 'bg-white/15', 'font-bold');
        }
    }

    window.switchBasicDataSubTab = function(subtabKey) {
        window.currentBasicSubTab = subtabKey;
        
        document.querySelectorAll('.basic-subtab-container').forEach(el => {
            el.style.display = 'none';
        });

        const targetContainer = document.getElementById('subtab-container-' + subtabKey);
        if (targetContainer) {
            targetContainer.style.display = 'block';
            if (subtabKey === 'device-types' && typeof window.updateKindTableByCategory === 'function') {
                const select = document.getElementById('manage-category-select');
                const currentCat = select ? select.value : 'all';
                window.updateKindTableByCategory(currentCat);
            }
            if (subtabKey === 'departments' && typeof window.updateMainSubDeptTableByGroup === 'function') {
                const select = document.getElementById('main-dept-group-select');
                const currentGroup = select ? select.value : 'all';
                window.updateMainSubDeptTableByGroup(currentGroup);
            }
        }

        const titleText = document.getElementById('basic-data-title-text');
        const activeLink = document.querySelector('.sub-menu-item[data-sub="' + subtabKey + '"]');
        if (titleText && activeLink) {
            titleText.textContent = activeLink.textContent.trim();
        }

        const headerButtons = document.getElementById('basic-header-buttons-container');
        if (headerButtons) {
            headerButtons.style.display = 'flex';
            if (subtabKey === 'departments') {
                headerButtons.innerHTML = '<button type="button" onclick="openAddDeptGroupModal(); if(typeof playBeep===\'function\') playBeep();" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer"><i class="fa-solid fa-plus"></i> <span>เพิ่มฝ่าย</span></button><button type="button" onclick="openNewSubDeptModal(); if(typeof playBeep===\'function\') playBeep();" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer"><i class="fa-solid fa-plus"></i> <span>งาน/แผนก</span></button>';
            } else if (subtabKey === 'device-types') {
                headerButtons.innerHTML = '<button type="button" onclick="openAddAssetCategoryModal()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer"><i class="fa-solid fa-plus"></i> <span>เพิ่มประเภททรัพย์สิน</span></button><button type="button" onclick="openAddAssetKindModal()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer"><i class="fa-solid fa-plus"></i> <span>เพิ่มชนิดทรัพย์สิน</span></button>';
            } else {
                headerButtons.innerHTML = '<button id="btn-add-basic" type="button" onclick="window.handleBasicHeaderAdd();" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer"><i class="fa-solid fa-plus"></i><span id="btn-add-basic-text">เพิ่มข้อมูล</span></button>';
                const btnAdd = document.getElementById('btn-add-basic');
                if (btnAdd) btnAdd.style.display = (subtabKey === 'device-types') ? 'none' : 'flex';
                const btnAddText = document.getElementById('btn-add-basic-text');
                if (btnAddText) {
                    if (subtabKey === 'buildings') btnAddText.textContent = 'เพิ่ม';
                    else if (subtabKey === 'personnel-types') btnAddText.textContent = 'เพิ่ม';
                    else btnAddText.textContent = 'เพิ่มข้อมูล';
                }
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'tab-basic-data') {
            const subKey = urlParams.get('sub') || 'device-types';
            const activeLink = document.querySelector('.sub-menu-item[data-sub="' + subKey + '"]');
            handleSubMenuClick(subKey, activeLink);
        }
    });

    window.handleBasicHeaderManage = function() {
        if (typeof window.openManageBasicModal === 'function') {
            window.openManageBasicModal();
        } else {
            const modal = document.getElementById('modal-manage-basic');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.remove('hidden');
            }
        }
    };
    
    window.handleBasicHeaderAdd = function() {
        if (window.currentBasicSubTab === 'device-types') {
            if (typeof window.openAssetItemModal === 'function') window.openAssetItemModal();
        } else if (window.currentBasicSubTab === 'buildings') {
            if (typeof window.openBuildingModal === 'function') window.openBuildingModal();
        } else if (window.currentBasicSubTab === 'personnel-types') {
            if (typeof window.openNewPositionModal === 'function') window.openNewPositionModal();
        } else {
            alert('ไม่พบฟังก์ชันที่รองรับการเพิ่มข้อมูลในหน้านี้');
        }
    };
    function filterBasicDataItems() {
        const searchText = document.getElementById('basic-search-input')?.value.toLowerCase() || '';
        document.querySelectorAll('#basic-data-table-body tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(searchText) ? '' : 'none';
        });
    }
    function editDeviceType(id) { alert('แก้ไขหมวดหมู่ครุภัณฑ์ ID: ' + id); }
    function deleteDeviceType(id) { if(confirm('ต้องการลบหมวดหมู่ครุภัณฑ์นี้หรือไม่?')) alert('ลบข้อมูล ID: ' + id); }
    function deletePersonnelType(id) { if(confirm('ต้องการลบประเภทบุคลากรนี้หรือไม่?')) alert('ลบข้อมูล ID: ' + id); }
    function editBuilding(id) { alert('แก้ไขอาคาร ID: ' + id); }
    function deleteBuilding(id) { if(confirm('ต้องการลบอาคารนี้หรือไม่?')) alert('ลบข้อมูล ID: ' + id); }
    function deleteSubDepartment(group, name) { if(confirm('ต้องการลบแผนก ' + name + '?')) alert('ลบข้อมูล'); }

    /* Admin Mobile Sidebar Handlers */
    function toggleAdminMobileSidebar() {
        const sidebar = document.getElementById('admin-mobile-sidebar');
        const backdrop = document.getElementById('admin-mobile-backdrop');
        if (sidebar && backdrop) {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                backdrop.classList.remove('hidden');
                setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
                sidebar.classList.remove('-translate-x-full');
            } else {
                closeAdminMobileSidebar();
            }
        }
    }

    function closeAdminMobileSidebar() {
        const sidebar = document.getElementById('admin-mobile-sidebar');
        const backdrop = document.getElementById('admin-mobile-backdrop');
        if (sidebar && backdrop) {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
    }

    function toggleMobileBasicDataMenu() {
        const subMenu = document.getElementById('mobile-basic-data-sub-menu');
        const chevron = document.getElementById('mobile-basic-data-chevron');
        if (!subMenu) return;
        if (subMenu.style.display === 'none' || subMenu.style.display === '') {
            subMenu.style.display = 'block';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            subMenu.style.display = 'none';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    function handleMobileAdminTab(tabId, el) {
        history.pushState(null, '', '?tab=' + tabId);
        const desktopTab = document.querySelector(`.desktop-tab[onclick*="${tabId}"]`);
        if (typeof switchAdminTab === 'function') {
            switchAdminTab(tabId, desktopTab || el);
        }
        
        document.querySelectorAll('.mobile-admin-tab').forEach(t => {
            t.classList.remove('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold', 'text-white', 'bg-white/15');
            t.classList.add('border-transparent', 'text-blue-100/80', 'font-semibold');
        });
        if (el) {
            el.classList.remove('border-transparent', 'text-blue-100/80', 'font-semibold');
            el.classList.add('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold');
        }
        closeAdminMobileSidebar();
    }

    function handleMobileAdminSubMenu(subKey, el) {
        const desktopItem = document.querySelector(`.sub-menu-item[data-sub="${subKey}"]`);
        if (typeof handleSubMenuClick === 'function') {
            handleSubMenuClick(subKey, desktopItem || el);
        }
        document.querySelectorAll('.mobile-sub-menu-item').forEach(item => {
            item.classList.remove('bg-white', 'text-blue-900', 'font-bold', 'shadow-sm');
            item.classList.add('text-blue-100/80', 'font-semibold');
        });
        if (el) {
            el.classList.remove('text-blue-100/80');
            el.classList.add('bg-white', 'text-blue-900', 'font-bold', 'shadow-sm');
        }
        closeAdminMobileSidebar();
    }
</script>





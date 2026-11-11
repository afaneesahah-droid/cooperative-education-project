<!-- TAB 2: บอร์ดจัดการข้อมูลทรัพย์สินและอาคารสถานที่ (Asset & Infrastructure Management) -->
<div id="tab-assets" class="tab-content {{ $activeTab === 'tab-assets' ? 'block' : 'hidden' }} space-y-4 sm:space-y-5" style="display: {{ $activeTab === 'tab-assets' ? 'block' : 'none' }};">
    <div class="premium-white-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl space-y-4 sm:space-y-5">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 sm:pb-4">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 flex items-center">
                    <i class="fa-solid fa-boxes-stacked text-blue-600 mr-2"></i> จัดการข้อมูลทรัพย์สิน
                </h3>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="openAssetItemModal(); playBeep(;" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-2 transition shadow-md shadow-blue-200 cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>เพิ่ม</span>
                </button>
            </div>
        </div> 

        <!-- Filters & Search -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="sm:col-span-2 lg:col-span-6 relative text-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="device-search" onkeyup="renderDevicesList()" placeholder="ค้นหารหัส, ชื่ออุปกรณ์, อาคารสถานที่..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 font-bold transition">
            </div>

            <!-- Category Filter -->
            <div class="sm:col-span-1 lg:col-span-3 text-xs">
                <select id="device-category-filter" onchange="handleAssetCategoryFilterChange()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none focus:border-blue-500 font-bold cursor-pointer">
                    <option value="all">กรุณาเลือกประเภททรัพย์สิน</option>
                    @if(isset($dbAssetKinds) && count($dbAssetKinds) > 0)
                        @foreach(collect($dbAssetKinds)->pluck('category_code')->unique() as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    @else
                        <option value="ซอฟแวร์">ซอฟแวร์</option>
                        <option value="ไอโอที">ไอโอที</option>
                        <option value="ฮาร์ดแวร์">ฮาร์ดแวร์</option>
                    @endif
                </select>
            </div>

            <!-- Subcategory Filter -->
            <div class="sm:col-span-1 lg:col-span-3 text-xs">
                <select id="device-kind-filter" onchange="renderDevicesList()" disabled class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none focus:border-blue-500 font-bold cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="all">กรุณาเลือกชนิดทรัพย์สิน</option>
                </select>
            </div>
        </div>

        <!-- Table Container (Horizontal scroll on mobile/tablet/notebook without squeezing buttons) -->
        <div id="device-crud-container" class="overflow-x-auto custom-scrollbar rounded-xl border border-slate-100 w-full min-w-0">
            <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold min-w-[960px]">
                <thead id="devices-table-header">
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-extrabold uppercase whitespace-nowrap text-xs">
                        <th class="p-3 sm:p-3.5 text-center w-14">ลำดับ</th>
                        <th class="p-3 sm:p-3.5 min-w-[140px]">รหัสครุภัณฑ์</th>
                        <th class="p-3 sm:p-3.5 min-w-[120px]">หมวดหมู่ทรัพย์สิน</th>
                        <th class="p-3 sm:p-3.5 min-w-[120px]">ชนิดทรัพย์สิน</th>
                        <th class="p-3 sm:p-3.5 min-w-[110px]">อาคาร</th>
                        <th class="p-3 sm:p-3.5 min-w-[150px]">บุคลากรที่รับผิดชอบ</th>
                        <th class="p-3 sm:p-3.5 text-center min-w-[170px] whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="devices-table-body" class="divide-y divide-slate-100 text-slate-700">
                    <!-- โหลดประมวลผลผ่าน JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

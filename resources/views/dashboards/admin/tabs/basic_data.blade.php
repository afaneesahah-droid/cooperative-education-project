<!-- TAB 4: บอร์ดจัดการข้อมูลพื้นฐาน (Basic System Configuration & Lookup Data) -->
<div id="tab-basic-data" class="tab-content space-y-4 sm:space-y-5" style="display: {{ $activeTab === 'tab-basic-data' ? 'block' : 'none' }};">
    <div class="premium-white-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl space-y-4 sm:space-y-5 bg-white border border-slate-100 shadow-sm">
        
        <!-- Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 sm:pb-4">
            <div>
                <h3 id="basic-data-title-heading" class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-blue-600"></i>
                    <span id="basic-data-title-text">ประเภททรัพย์สิน</span>
                </h3>
            </div>
            <div class="flex items-center gap-2" id="basic-header-buttons-container">

                <button id="btn-add-basic" type="button" onclick="window.handleBasicHeaderAdd();" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span id="btn-add-basic-text">เพิ่ม</span>
                </button>
            </div>
        </div>

        @include('dashboards.admin.tabs.basic-data.device-types')
        @include('dashboards.admin.tabs.basic-data.personnel-types')
        @include('dashboards.admin.tabs.basic-data.buildings')
        @include('dashboards.admin.tabs.basic-data.departments')

    </div>
</div>

@include('dashboards.admin.tabs.basic-data.scripts')

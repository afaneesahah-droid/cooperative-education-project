<!-- TAB 3: บอร์ดจัดการข้อมูลบุคลากร โครงสร้างองค์กร และประเภททรัพย์สิน -->
<div id="tab-personnel" class="tab-content {{ $activeTab === 'tab-personnel' ? 'block' : 'hidden' }} space-y-4 sm:space-y-5" style="display: {{ $activeTab === 'tab-personnel' ? 'block' : 'none' }};">
    <div class="premium-white-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl space-y-4 sm:space-y-5">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 sm:pb-4">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 flex items-center">
                    <i class="fa-solid fa-users-gear text-blue-600 mr-2"></i> จัดการข้อมูลบุคลากร
                </h3>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- ปุ่มฝั่งบุคลากร -->
                <button onclick="prepareCreateUserForm(); playBeep();" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>เพิ่ม</span>
                </button>

            </div>
        </div>



        <!-- Container 0: Personnel List Table -->
        <div id="personnel-list-container" class="space-y-4">
            <div class="relative w-full max-w-md text-xs mb-2">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="personnel-search-input" onkeyup="renderPersonnelList()" placeholder="ค้นหาชื่อ, นามสกุล, ตำแหน่ง, ฝ่าย หรือแผนกวิชา..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 font-bold transition">
            </div>

            <!-- แท็บย่อยสลับดูข้อมูลบุคลากร -->
            <div class="flex border-b border-slate-200 text-xs md:text-sm font-bold gap-4 mb-2 pb-2">
                <button id="personnel-subtab-active-btn" type="button" onclick="switchPersonnelSubTab('active')" class="py-2.5 border-b-2 border-blue-600 text-blue-600 flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-users"></i> บุคลากรปัจจุบัน <span id="personnel-count-active-badge" class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full text-[10px]">0</span>
                </button>
                <button id="personnel-subtab-trash-btn" type="button" onclick="switchPersonnelSubTab('trash')" class="py-2.5 border-b-2 border-transparent text-slate-400 hover:text-red-500 flex items-center gap-2 transition-all cursor-pointer">
                    บัญชีที่ถูกระงับ <span id="personnel-count-trash-badge" class="bg-red-50 text-red-500 px-2 py-0.5 rounded-full text-[10px]">0</span>
                </button>
            </div>

            <div class="overflow-x-auto custom-scrollbar rounded-xl border border-slate-100 w-full min-w-0">
                <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold min-w-[850px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-extrabold uppercase">
                            <th class="p-4 text-center w-14">ลำดับ</th>
                            <th class="p-4">ชื่อ</th>
                            <th class="p-4">นามสกุล</th>
                            <th class="p-4">ประเภทบุคลากร</th>
                            <th class="p-4">อีเมล</th>
                            <th class="p-4 text-center w-[240px]">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="personnel-list-table-body" class="divide-y divide-slate-100 text-slate-700">
                        @if(isset($formattedUsers) && is_countable($formattedUsers) && count($formattedUsers) > 0)
                            @php
                                $deptGroups = [
                                    'resource' => 'ฝ่ายบริหารทรัพยากร',
                                    'planning' => 'ฝ่ายแผนงานและความร่วมมือ',
                                    'student' => 'ฝ่ายพัฒนากิจการนักเรียน นักศึกษา',
                                    'academic' => 'ฝ่ายวิชาการ'
                                ];
                            @endphp
                            @foreach($formattedUsers as $index => $u)
                                <tr class="hover:bg-slate-50 transition border-b border-slate-100 personnel-row" data-status="{{ $u['status'] === 'suspended' ? 'trash' : 'active' }}" data-user-id="{{ $u['id'] }}" data-user="{{ rawurlencode(json_encode($u, JSON_UNESCAPED_UNICODE)) }}">
                                    <td class="p-4 text-center">{{ $index + 1 }}</td>
                                    <td class="p-4 font-bold text-slate-800 personnel-fname">{{ $u['first_name'] ?: '-' }}</td>
                                    <td class="p-4 font-bold text-slate-800 personnel-lname">{{ $u['last_name'] ?: '-' }}</td>
                                    <td class="p-4 text-slate-600 personnel-type">{{ $u['position'] ?: '-' }}</td>
                                    <td class="p-4 text-slate-600 font-mono personnel-email">{{ $u['email'] ?: '-' }}</td>
                                    <td class="p-4 text-center action-cell">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" onclick="viewPersonnel(this);" class="flex items-center gap-1 px-2.5 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-blue-200 whitespace-nowrap" title="ดูข้อมูล">
                                                <i class="fa-solid fa-eye"></i> ดูข้อมูล
                                            </button>
                                            <button type="button" onclick="editUser('{{ $u['id'] }}', 'personnel');" class="flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-amber-200 whitespace-nowrap" title="แก้ไขข้อมูลบุคลากร">
                                                <i class="fa-solid fa-pen"></i> แก้ไข
                                            </button>
                                            @if($u['status'] === 'suspended')
                                                <button type="button" onclick="restoreUser('{{ $u['id'] }}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-green-200 whitespace-nowrap" title="ยกเลิกระงับบัญชี">
                                                    <i class="fa-solid fa-rotate-left"></i> ยกเลิกระงับ
                                                </button>
                                            @else
                                                <button type="button" onclick="softDeleteUser('{{ $u['id'] }}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-red-200 whitespace-nowrap" title="ระงับการใช้งาน">
                                                    <i class="fa-solid fa-ban"></i> ระงับ
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr id="personnel-empty-row">
                                <td colspan="9" class="p-8 text-center text-slate-500 font-semibold">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-users-slash text-4xl mb-3 text-slate-300"></i>
                                        <p>ไม่พบข้อมูลบุคลากรในระบบ</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Container 1: Departments & Sub-departments Table -->
        <div id="department-crud-container" class="hidden overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-extrabold uppercase">
                        <th class="p-3 w-12">#</th>
                        <th class="p-3">สังกัด / ฝ่ายงาน</th>
                        <th class="p-3">งาน / แผนกวิชา</th>
                        <th class="p-3 text-right">การควบคุม</th>
                    </tr>
                </thead>
                <tbody id="departments-table-body" class="divide-y divide-slate-100 text-slate-700">
                    <!-- โหลดประมวลผลผ่าน JS -->
                </tbody>
            </table>
        </div>

        <!-- Container 2: Personnel Types Table -->
        <div id="personnel-types-container" class="hidden overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-extrabold uppercase">
                        <th class="p-4 w-16">ลำดับ</th>
                        <th class="p-4">ประเภทบุคลากร</th>
                        <th class="p-4 text-center">ระดับสิทธิ์</th>
                        <th class="p-4 text-right">การควบคุม</th>
                    </tr>
                </thead>
                <tbody id="personnel-types-table-body" class="divide-y divide-slate-100 text-slate-700">
                    <!-- โหลดประมวลผลผ่าน JS -->
                </tbody>
            </table>
        </div>

        <!-- Container 3: Asset Categories Table (เพิ่มในหน้าเดียวกัน) -->
        <div id="asset-categories-container" class="hidden overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-extrabold uppercase">
                        <th class="p-4 w-16 text-center">#</th>
                        <th class="p-4">รหัส / ชื่อประเภททรัพย์สิน</th>
                        <th class="p-4">รายละเอียดหมวดหมู่</th>
                        <th class="p-4 text-center">จำนวนรายการ</th>
                        <th class="p-4 text-right">การควบคุม</th>
                    </tr>
                </thead>
                <tbody id="asset-categories-table-body" class="divide-y divide-slate-100 text-slate-700">
                    <!-- โหลดประมวลผลผ่าน JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>
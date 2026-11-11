<div id="tab-repair-projects" class="tab-content {{ $activeTab === 'tab-repair-projects' ? 'block' : 'hidden' }} space-y-4 sm:space-y-5" style="display: {{ $activeTab === 'tab-repair-projects' ? 'block' : 'none' }};">
    <div class="premium-white-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl space-y-4 sm:space-y-6 bg-white shadow-sm border border-slate-100">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 sm:pb-4">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 flex items-center">
                    <i class="fa-solid fa-screwdriver-wrench text-blue-600 mr-2"></i> รายการโครงการซ่อมบำรุง
                </h3>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- ตัวกรองปีงบประมาณ -->
                <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 text-xs shadow-xs">
                    <span class="text-slate-500 font-bold"><i class="fa-regular fa-calendar-days text-blue-600 mr-1"></i>ปีงบประมาณ:</span>
                    <select id="repair-project-fiscal-year-filter" onchange="changeBudgetFiscalYear(this.value)" class="bg-transparent font-extrabold text-slate-800 outline-none cursor-pointer">
                        <option value="all" {{ ($selectedFiscalYear ?? '') === 'all' ? 'selected' : '' }}>-- แสดงทุกปี --</option>
                        @if(isset($availableFiscalYears))
                            @foreach($availableFiscalYears as $fy)
                            <option value="{{ $fy }}" {{ ($selectedFiscalYear ?? '') == $fy ? 'selected' : '' }}>ปี {{ $fy }}</option>
                            @endforeach
                        @else
                            <option value="{{ date('Y') + 543 }}">ปี {{ date('Y') + 543 }}</option>
                        @endif
                    </select>
                </div>

                <!-- ปุ่มกำหนดงบประมาณของแต่ละฝ่าย -->
                <button type="button" onclick="openManageDeptBudgetsModal()" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-2 transition shadow-md shadow-indigo-200 whitespace-nowrap cursor-pointer">
                    <i class="fa-solid fa-coins text-amber-300"></i>
                    <span>จัดการงบประมาณ</span>
                </button>

                <!-- ปุ่มเพิ่มโครงการ -->
                <button type="button" onclick="openAddRepairProjectModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-2 transition shadow-md shadow-blue-200 whitespace-nowrap cursor-pointer">
                    <i class="fa-solid fa-plus"></i>
                    <span>เพิ่ม</span>
                </button>
            </div>
        </div>



        <!-- Filters & Search for Projects Table -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center pt-2">
            <!-- Search -->
            <div class="md:col-span-5 relative text-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="search-repair-project" placeholder="ค้นหาตามรหัส, ชื่อโครงการ, ฝ่าย, หรือประเภทงบ..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 font-bold transition">
            </div>

            <!-- Filter Department Group -->
            <div class="md:col-span-4 relative text-xs">
                <select id="filter-dept-select" onchange="filterProjectsByDept(this.value)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none focus:border-blue-500 font-bold transition appearance-none cursor-pointer">
                    <option value="">-- แสดงทุกฝ่าย --</option>
                    <option value="ฝ่ายบริหารทรัพยากร">ฝ่ายบริหารทรัพยากร</option>
                    <option value="ฝ่ายนโยบายและแผน">ฝ่ายนโยบายและแผน</option>
                    <option value="ฝ่ายพัฒนานักศึกษาและศิษย์เก่า">ฝ่ายพัฒนานักศึกษาและศิษย์เก่า</option>
                    <option value="ฝ่ายวิชาการ">ฝ่ายวิชาการ</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto custom-scrollbar rounded-xl border border-slate-100 w-full min-w-0">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead class="bg-slate-50/50">
                    <tr class="border-b border-slate-100">
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider text-center">ลำดับ</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider">รหัสโครงการ</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider">ชื่อโครงการ</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider text-center">ปีงบประมาณ</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider">หน่วยงานรับผิดชอบ (ฝ่าย)</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider text-right">งบประมาณ (บาท)</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider text-right">ใช้ไป (บาท)</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider text-right">คงเหลือ (บาท)</th>
                        <th class="px-4 py-3 text-[11px] font-black text-slate-500 uppercase tracking-wider text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
@forelse ($dbRepairProjects as $index => $project)
    @php
        $projRequests = $project->repairRequests->map(function($req) {
            $userObj = $req->user;
            $name = 'ผู้แจ้งซ่อม';
            if ($userObj) {
                $name = $userObj->first_name ? trim($userObj->first_name . ' ' . ($userObj->last_name ?? '')) : ($userObj->name ?? $userObj->username);
            }
            return [
                'request_code' => $req->request_code ?? $req->request_id,
                'reporter' => $name,
                'date' => $req->created_at ? $req->created_at->format('d/m/Y') : ($req->request_date ?? '-'),
                'status' => $req->status ?? '-',
                'budget' => (float)($req->budget ?? 0),
                'desc' => $req->description ?? '-',
            ];
        });
        $projData = [
            'id' => $project->id,
            'code' => $project->project_code,
            'name' => $project->name,
            'dept' => $project->department,
            'owner' => $project->responsible_person ?? '-',
            'fiscal' => $project->fiscal_year ?? '-',
            'budget' => (float)$project->budget,
            'budget_source' => $project->budget_source ?? 'งบประมาณกลาง',
            'description' => $project->description ?? '-',
            'used' => (float)$project->used_budget,
            'remaining' => (float)($project->budget - $project->used_budget),
            'requests' => $projRequests,
        ];
    @endphp
    <tr class="repair-project-row hover:bg-slate-50/80 transition-colors" data-search="{{ mb_strtolower($project->project_code . ' ' . $project->name . ' ' . $project->department . ' ' . ($project->fiscal_year ?? '') . ' ' . ($project->budget_source ?? 'งบประมาณกลาง')) }}" data-dept="{{ $project->department }}">
        <td class="px-4 py-4 text-sm font-bold text-slate-700 text-center">{{ $index + 1 }}</td>
        <td class="px-4 py-4 text-sm font-bold text-slate-700 whitespace-nowrap">{{ $project->project_code }}</td>
        <td class="px-4 py-4">
            <p class="text-sm font-bold text-slate-800 line-clamp-2">{{ $project->name }}</p>
            <div class="mt-1 flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100/80">
                    <i class="fa-solid fa-coins text-[10px] text-indigo-500"></i> {{ $project->budget_source ?? 'งบประมาณกลาง' }}
                </span>
            </div>
        </td>
        <td class="px-4 py-4 text-sm font-bold text-slate-700 text-center">{{ $project->fiscal_year ?? '-' }}</td>
        <td class="px-4 py-4 text-xs font-bold text-slate-600 whitespace-nowrap">{{ $project->department }}</td>
        <td class="px-4 py-4 text-sm font-bold text-slate-700 text-right">{{ number_format($project->budget, 2) }}</td>
        <td class="px-4 py-4 text-sm font-bold text-slate-700 text-right text-amber-600">{{ number_format($project->used_budget, 2) }}</td>
        <td class="px-4 py-4 text-sm font-black text-blue-600 text-right">{{ number_format($project->budget - $project->used_budget, 2) }}</td>
        
        <td class="px-4 py-4 text-center">
            <button type="button" onclick='openViewRepairProjectModal(@json($projData))' class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-bold transition shadow-sm shadow-blue-200 cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-eye"></i> ดูรายละเอียด
            </button>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="9" class="px-4 py-8 text-center text-slate-500 text-sm font-medium">ไม่มีข้อมูลโครงการซ่อมบำรุง</td>
    </tr>
    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function changeBudgetFiscalYear(year) {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', 'tab-repair-projects');
    url.searchParams.set('fiscal_year', year);
    window.location.href = url.toString();
}

function filterProjectsByDept(dept) {
    const rows = document.querySelectorAll('.repair-project-row');
    rows.forEach(row => {
        const rowDept = row.getAttribute('data-dept') || '';
        if (!dept || rowDept === dept) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-repair-project');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.repair-project-row');
            rows.forEach(row => {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});
</script>
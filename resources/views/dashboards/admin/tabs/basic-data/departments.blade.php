        <!-- 4. SUB-TAB: หน่วยงาน -->
        <div id="subtab-container-departments" class="basic-subtab-container space-y-6" style="display: none;">
            
            <!-- Section: ฝ่ายงาน -->
            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-800">ฝ่ายงาน</label>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[240px]">
                        <select id="main-dept-group-select" onchange="updateMainSubDeptTableByGroup(this.value)"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl focus:outline-none focus:border-blue-500 cursor-pointer shadow-xs appearance-none pr-10">
                            <option value="all" selected>-- ทุกฝ่ายงาน --</option>
                            @if(isset($dbDepartmentGroups) && count($dbDepartmentGroups) > 0)
                                @foreach($dbDepartmentGroups as $dg)
                                    <option value="{{ $dg->group_code ?: $dg->group_name }}">{{ $dg->group_name }}</option>
                                @endforeach
                            @else
                                <option value="resource">ฝ่ายบริหารทรัพยากร</option>
                                <option value="planning">ฝ่ายแผนงานและความร่วมมือ</option>
                                <option value="student">ฝ่ายพัฒนากิจการนักเรียน นักศึกษา</option>
                                <option value="academic">ฝ่ายวิชาการ</option>
                            @endif
                        </select>
                        <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    <button type="button" onclick="editDeptGroup()"
                        class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold cursor-pointer">
                        <i class="fa-solid fa-pen"></i> แก้ไข
                    </button>
                </div>
            </div>

            <!-- Section: งาน / แผนกวิชา -->
            <div class="space-y-3 pt-2">
                <h4 class="text-sm font-bold text-slate-800">งาน / แผนกวิชา</h4>

                <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-xs">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm font-semibold">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-500 font-bold uppercase">
                                <th class="p-3.5 pl-4 w-16 text-center">ลำดับ</th>
                                <th class="p-3.5">งาน/แผนกวิชา</th>
                                <th class="p-3.5 pr-4 text-right">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="basic-departments-table-body" class="divide-y divide-slate-100 text-slate-700">
                            <tr>
                                <td colspan="3" class="p-8 text-center text-slate-400 font-medium">
                                    กรุณาฝ่าย เพื่อดูข้อมูลงาน / แผนกวิชา
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer count -->
                <p id="main-subdept-footer-count" class="text-center text-xs text-slate-400 font-medium pt-2">
                    แสดง 0 รายการ
                </p>
            </div>

        </div>

    </div>

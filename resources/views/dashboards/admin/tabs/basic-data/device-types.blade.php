        <!-- 1. SUB-TAB: ประเภททรัพย์สิน -->
        <div id="subtab-container-device-types" class="basic-subtab-container space-y-5" style="display: block;">
            
            <!-- Top Controls Row -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                
                <!-- Search Box -->
                <div class="w-full flex-1">
                    <div class="relative w-full h-[42px]">
                        <input type="text" id="manage-kinds-search" placeholder="ค้นหาชนิดทรัพย์สิน..." onkeyup="filterKindsTable()"
                            class="w-full h-full px-4 pl-10 bg-white border border-slate-200 text-slate-800 text-sm font-medium rounded-xl focus:outline-none focus:border-blue-500 transition shadow-xs placeholder:text-slate-400">
                        <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    </div>
                </div>

                <!-- Section: ประเภททรัพย์สิน -->
                <div class="w-full flex-1">
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <select id="manage-category-select" onchange="updateKindTableByCategory(this.value)"
                                class="w-full h-[42px] px-4 bg-white border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl focus:outline-none focus:border-blue-500 cursor-pointer shadow-xs appearance-none pr-10">
                                <option value="all">-- เลือกประเภททรัพย์สิน --</option>
                            </select>
                            <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <button type="button" onclick="editAssetCategory()"
                            class="px-3 h-[42px] bg-amber-500 hover:bg-amber-600 text-white text-[11px] rounded-xl flex items-center gap-1.5 transition font-bold cursor-pointer shadow-xs">
                            <i class="fa-solid fa-pen"></i> แก้ไข
                        </button>
                    </div>
                </div>

            </div>

                <!-- Section: ชนิดทรัพย์สิน -->
                <div id="manage-kinds-section" class="space-y-3 pt-2">
                    <h4 class="text-sm font-bold text-slate-800">ชนิดทรัพย์สิน</h4>

                    <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-xs">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm font-semibold">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-500 font-bold uppercase">
                                    <th class="p-3.5 pl-4 w-24 text-center">ลำดับ</th>
                                    <th class="p-3.5 pl-4">ชื่อชนิดทรัพย์สิน</th>
                                    <th class="p-3.5 pr-4 text-right">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="manage-kinds-tbody" class="divide-y divide-slate-100 text-slate-700">
                                <!-- โหลดผ่าน JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer count -->
                    <p id="manage-kinds-footer-count" class="text-center text-xs text-slate-400 font-medium pt-2">
                        แสดง 1 - 2 จาก 2 รายการ
                    </p>
                </div>
        </div>

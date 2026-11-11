




<!-- 3. SUB-TAB: อาคารสถานที่ -->
        <div id="subtab-container-buildings" class="basic-subtab-container space-y-5" style="display: none;">
            <!-- ส่วนค้นหา -->
            <div class="flex items-center justify-between">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-search"></i>
                    </div>
                    <input type="text" id="search-building-input" onkeyup="searchBuildings()" placeholder="ค้นหารหัส หรือชื่ออาคาร..."
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl focus:outline-none focus:border-blue-500 shadow-sm placeholder:text-slate-400 transition">
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-black font-extrabold uppercase">
                            <th class="p-4 w-16 text-center">ลำดับ</th>
                            <th class="p-4">รหัสอาคาร</th>
                            <th class="p-4">ชื่ออาคาร</th>
                            <th class="p-4 text-center">จำนวนชั้น</th>
                            <th class="p-4 text-center">จำนวนห้อง</th>
                            <th class="p-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="buildings-table-body" class="divide-y divide-slate-100 text-slate-700">
                        @if(isset($dbBuildings) && count($dbBuildings) > 0)
                            @foreach($dbBuildings as $index => $b)
                            <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                                <td class="p-4 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                                <td class="p-4 font-mono font-bold text-blue-600">{{ $b->building_id }}</td>
                                <td class="p-4 font-bold text-slate-800">{{ $b->building_name }}</td>
                                <td class="p-4 text-center font-bold text-slate-600">{{ $b->floors }}</td>
                                <td class="p-4 text-center font-bold text-slate-600" id="room-count-{{ $b->building_id }}">{{ $b->rooms ? $b->rooms->count() : 0 }}</td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="editBuilding('{{ $b->building_id }}')" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold cursor-pointer"><i class="fa-solid fa-pen"></i> แก้ไข</button>
                                        <button type="button" onclick="viewBuildingDetails('{{ $b->building_id }}')" class="px-2.5 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-[10px] rounded-lg transition font-bold cursor-pointer"><i class="fa-solid fa-door-open"></i> จัดการห้อง</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500 font-semibold">
                                    ไม่มีข้อมูลอาคารในระบบ
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

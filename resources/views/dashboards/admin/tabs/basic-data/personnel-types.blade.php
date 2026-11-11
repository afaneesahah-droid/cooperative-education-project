        <!-- 2. SUB-TAB: ประเภทบุคลากร -->
        <div id="subtab-container-personnel-types" class="basic-subtab-container space-y-5" style="display: none;">
            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-extrabold uppercase">
                            <th class="p-4 w-20 text-center">ลำดับ</th>
                            <th class="p-4">ประเภทบุคลากร</th>
                            <th class="p-4 w-32 text-center">การควบคุม</th>
                        </tr>
                    </thead>
                    <tbody id="basic-personnel-types-table-body" class="divide-y divide-slate-100 text-slate-700">
                        @if(isset($dbPersonnelTypes) && count($dbPersonnelTypes) > 0)
                            @foreach($dbPersonnelTypes as $index => $pt)
                            <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                                <td class="p-4 text-center text-slate-500 font-mono">{{ $index + 1 }}</td>
                                <td class="p-4 font-bold text-slate-800">{{ $pt->name }}</td>
                                <td class="p-4 text-center">
                                    <button type="button" onclick="editPersonnelType({{ $pt->id }})" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold"><i class="fa-solid fa-pen"></i> แก้ไข</button>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="p-8 text-center text-slate-500 font-semibold">
                                    ไม่มีข้อมูลประเภทบุคลากรในระบบ
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

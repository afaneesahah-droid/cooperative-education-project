<!-- TAB: บัญชีที่ถูกระงับ -->
<div id="tab-suspended-users" class="tab-content main-admin-tab space-y-5" style="display: {{ $activeTab === 'tab-suspended-users' ? 'block' : 'none' }};">
    <div class="premium-white-card p-6 rounded-3xl space-y-5 bg-white border border-slate-100 shadow-sm">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-base sm:text-lg font-black text-red-600 flex items-center">
                <i class="fa-solid fa-user-slash mr-2"></i> บัญชีที่ถูกระงับ
            </h3>
            <p class="text-xs text-slate-500 mt-1">ข้อมูลบัญชีผู้ใช้งานและบุคลากรที่ถูกระงับสิทธิ์การใช้งานในระบบ</p>
        </div> 

        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="relative w-full sm:max-w-md text-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="suspended-user-search" onkeyup="renderSuspendedUsersList()" placeholder="ค้นหาบัญชีที่ถูกระงับ..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-red-500 font-bold transition">
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold">
                <thead>
                    <tr class="bg-red-50 border-b border-red-100 text-red-600 font-extrabold uppercase">
                        <th class="p-4 text-center w-14">ลำดับ</th>
                        <th class="p-4">ชื่อผู้ใช้</th>
                        <th class="p-4">คำนำหน้า</th>
                        <th class="p-4">ชื่อ</th>
                        <th class="p-4">นามสกุล</th>
                        <th class="p-4">อีเมล</th>
                        <th class="p-4">สิทธิ์ผู้ใช้งาน</th>
                        <th class="p-4">สถานะบัญชี</th>
                        <th class="p-4 text-center w-[160px]">การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="suspended-users-table-body" class="divide-y divide-slate-100 text-slate-700">
                    @php $hasSuspended = false; @endphp
                    @if(isset($formattedUsers) && is_countable($formattedUsers) && count($formattedUsers) > 0)
                        @foreach($formattedUsers as $index => $u)
                        @if($u['status'] === 'suspended')
                        @php $hasSuspended = true; @endphp
                        <tr class="hover:bg-red-50/50 transition border-b border-slate-100 suspended-user-row" data-user-id="{{ $u['id'] }}">
                            <td class="p-4 text-center font-bold text-slate-500 font-mono suspended-user-index"></td>
                            <td class="p-4 font-mono text-slate-700">{{ $u['username'] }}</td>
                            <td class="p-4 text-slate-700">{{ $u['prefix'] }}</td>
                            <td class="p-4 text-slate-700">{{ $u['first_name'] }}</td>
                            <td class="p-4 text-slate-700">{{ $u['last_name'] }}</td>
                            <td class="p-4 font-mono">{{ $u['email'] }}</td>
                            <td class="p-4 text-blue-600/80">{{ $u['roleThai'] }}</td>
                            <td class="p-4">
                                <span class="bg-red-50 text-red-700 border border-red-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold cursor-help" title="เหตุผล: {{ $u['suspend_reason'] ?: 'ไม่ได้ระบุเหตุผล' }}">
                                    <i class="fa-solid fa-ban text-[8px] mr-1"></i>ถูกระงับ
                                </span>
                            </td>
                            <td class="p-4 text-center action-cell">
                                <button type="button" onclick="restoreUser('{{ $u['id'] }}');" class="flex items-center mx-auto gap-1 px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-green-200 whitespace-nowrap" title="ยกเลิกระงับบัญชี">
                                    <i class="fa-solid fa-rotate-left"></i> ยกเลิกระงับ
                                </button>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    @endif
                    @if(!$hasSuspended)
                        <tr id="suspended-empty-row">
                            <td colspan="9" class="p-8 text-center text-slate-500 font-semibold">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-user-check text-4xl mb-3 text-slate-300"></i>
                                    <p>ไม่มีบัญชีที่ถูกระงับในระบบ</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function renderSuspendedUsersList() {
        const searchText = document.getElementById('suspended-user-search')?.value.toLowerCase() || '';
        const rows = document.querySelectorAll('#suspended-users-table-body tr.suspended-user-row');
        let counter = 1;
        let visibleCount = 0;

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const matchesSearch = rowText.includes(searchText);

            if (matchesSearch) {
                row.style.display = '';
                const indexCell = row.querySelector('.suspended-user-index');
                if (indexCell) {
                    indexCell.innerText = counter++;
                }
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        const emptyRow = document.getElementById('suspended-empty-row');
        if (emptyRow) {
            if (visibleCount === 0 && rows.length > 0) {
                emptyRow.style.display = '';
                emptyRow.innerHTML = '<td colspan="9" class="p-8 text-center text-slate-500 font-semibold"><div class="flex flex-col items-center justify-center"><i class="fa-solid fa-search text-3xl mb-3 text-slate-300"></i><p>ไม่พบข้อมูลที่ค้นหา</p></div></td>';
            } else if (visibleCount > 0) {
                emptyRow.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('suspended-users-table-body')) {
            renderSuspendedUsersList();
        }
    });
</script>

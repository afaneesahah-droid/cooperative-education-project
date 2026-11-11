<!-- TAB 1: บอร์ดจัดการบัญชีผู้ใช้งานและสิทธิ์เข้าทำงานระบบ (RBAC Settings) -->
<div id="tab-users" class="tab-content main-admin-tab space-y-5" style="display: {{ $activeTab === 'tab-users' ? 'block' : 'none' }};">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm font-semibold px-5 py-3 rounded-2xl">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 text-sm font-semibold px-5 py-3 rounded-2xl">
            <i class="fa-solid fa-circle-xmark text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    @php $pendingUsers = isset($users) ? $users->where('status', 'pending') : collect(); @endphp
    @if($pendingUsers->count() > 0)
    <div class="premium-white-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl space-y-4 border-l-4 border-amber-400 bg-white shadow-sm">
        <div class="flex items-center gap-3">
            <span class="flex items-center justify-center w-9 h-9 bg-amber-100 rounded-xl">
                <i class="fa-solid fa-user-clock text-amber-500 text-base"></i>
            </span>
            <div>
                <h3 class="text-base font-black text-slate-900">รอการอนุมัติ</h3>
                <p class="text-xs text-slate-500">{{ $pendingUsers->count() }} บัญชีรอการตรวจสอบ</p>
            </div>
        </div>
        <div class="overflow-x-auto custom-scrollbar rounded-xl border border-amber-100 w-full min-w-0">
            <table class="w-full text-left text-xs md:text-sm font-semibold border-collapse min-w-[850px]">
                <thead>
                    <tr class="bg-amber-50 border-b border-amber-100 text-slate-500 font-extrabold uppercase">
                        <th class="p-3">ชื่อ</th>
                        <th class="p-3">นามสกุล</th>
                        <th class="p-3">รหัสผู้ใช้</th>
                        <th class="p-3">อีเมล</th>
                        <th class="p-3">เบอร์โทร</th>
                        <th class="p-3">แผนก</th>
                        <th class="p-3">วันที่สมัคร</th>
                        <th class="p-3 text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-50">
                    @foreach($pendingUsers as $pu)
                    <tr class="hover:bg-amber-50/50 transition">
                        <td class="p-3 text-slate-700">{{ $pu->first_name }}</td>
                        <td class="p-3 text-slate-700">{{ $pu->last_name }}</td>
                        <td class="p-3 text-slate-600">{{ $pu->username }}</td>
                        <td class="p-3 text-slate-600">{{ $pu->email }}</td>
                        <td class="p-3 text-slate-600">{{ $pu->phone ?? '-' }}</td>
                        <td class="p-3 text-slate-600">{{ $pu->department ?? '-' }}</td>
                        <td class="p-3 text-slate-500">{{ $pu->created_at ? $pu->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="p-3">
                            <div class="flex items-center justify-center gap-2">
                                <form method="POST" action="{{ route('admin.users.approve', $pu->id) }}" onsubmit="return confirm('อนุมัติบัญชีของ {{ $pu->first_name }} {{ $pu->last_name }}?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-check"></i> อนุมัติ
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.reject', $pu->id) }}" onsubmit="return confirm('ปฏิเสธบัญชีของ {{ $pu->first_name }} {{ $pu->last_name }}?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-lg transition shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i> ปฏิเสธ
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="premium-white-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl space-y-4 sm:space-y-5 bg-white border border-slate-100 shadow-sm">
        <div class="border-b border-slate-100 pb-3 sm:pb-4">
            <h3 class="text-base sm:text-lg font-black text-slate-900 flex items-center">
                <i class="fa-solid fa-user-shield text-blue-600 mr-2"></i> จัดการสิทธิ์ผู้ใช้งาน
            </h3>
        </div> 

        <!-- แถวค้นหา -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="relative w-full sm:max-w-md text-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="user-search" onkeyup="renderUsersList()" placeholder="ค้นหาชื่อ, อีเมล, สิทธิ์ หรือแผนก..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 font-bold transition">
            </div>
        </div>

        <!-- แท็บตัวกรองบัญชี -->
        <div class="flex items-center gap-4 border-b border-slate-100 overflow-x-auto custom-scrollbar w-full">
            <a onclick="switchUserViewMode('active')" id="user-tab-view-active" class="px-5 py-3 text-sm font-extrabold border-b-2 border-blue-600 text-blue-600 transition cursor-pointer flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-users"></i> บัญชีปกติ
            </a>
            <a onclick="switchUserViewMode('suspended')" id="user-tab-view-suspended" class="px-5 py-3 text-sm font-extrabold border-b-2 border-transparent text-slate-400 hover:text-red-500 transition cursor-pointer flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-ban"></i> บัญชีที่ถูกระงับ
            </a>
        </div>

        <!-- ตารางบัญชีผู้ใช้ระบบ -->
        <div class="overflow-x-auto custom-scrollbar rounded-xl border border-slate-100 w-full min-w-0">
            <table class="w-full text-left border-collapse text-xs md:text-sm font-semibold min-w-[950px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-extrabold uppercase">
                        <th class="p-4 text-center w-14">ลำดับ</th>
                        <th class="p-4">ชื่อผู้ใช้</th>
                        <th class="p-4">คำนำหน้า</th>
                        <th class="p-4">ชื่อ</th>
                        <th class="p-4">นามสกุล</th>
                        <th class="p-4">อีเมล</th>
                        <th class="p-4">สิทธิ์ผู้ใช้งาน</th>
                        <th class="p-4">สถานะบัญชี</th>
                        <th class="p-4 text-center w-[160px]" id="table-header-action">แก้ไขข้อมูล</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="divide-y divide-slate-100 text-slate-700">
                    @if(isset($formattedUsers) && is_countable($formattedUsers) && count($formattedUsers) > 0)
                        @foreach($formattedUsers as $index => $u)
                        @php
                            $rowStatus = $u['status'] === 'suspended' ? 'trash' : 'active';
                        @endphp
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100 user-row" data-status="{{ $rowStatus }}" data-user-id="{{ $u['id'] }}" data-user="{{ rawurlencode(json_encode($u, JSON_UNESCAPED_UNICODE)) }}">
                            <td class="p-4 text-center font-bold text-slate-500 font-mono user-index">{{ $index + 1 }}</td>
                            <td class="p-4 font-mono text-slate-700 user-uname">{{ $u['username'] }}</td>
                            <td class="p-4 text-slate-700 user-prefix">{{ $u['prefix'] }}</td>
                            <td class="p-4 text-slate-700 user-fname">{{ $u['first_name'] }}</td>
                            <td class="p-4 text-slate-700 user-lname">{{ $u['last_name'] }}</td>
                            <td class="p-4 font-mono user-email">{{ $u['email'] }}</td>
                            <td class="p-4 text-blue-600/80 user-role">{{ $u['roleThai'] }}</td>
                            <td class="p-4 status-cell">
                                @if($u['status'] === 'suspended')
                                    <span class="bg-red-50 text-red-700 border border-red-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold cursor-help" title="เหตุผล: {{ $u['suspend_reason'] ?: 'ไม่ได้ระบุเหตุผล' }}">
                                        <i class="fa-solid fa-ban text-[8px] mr-1"></i>ถูกระงับ
                                    </span>
                                @else
                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold">
                                        <i class="fa-solid fa-circle text-[8px] mr-1"></i>ใช้งานปกติ
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center action-cell">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="editUser('{{ $u['id'] }}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer whitespace-nowrap shadow-sm shadow-amber-200" title="แก้ไขสิทธิ์ผู้ใช้งาน">
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
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500 font-semibold">
                                ไม่มีข้อมูลผู้ใช้งานในระบบ
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal เพิ่มผู้ใช้ / แก้ไขผู้ใช้งาน -->
<div id="user-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white max-w-4xl w-full rounded-3xl border border-slate-100 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="px-6 md:px-8 py-5 md:py-6 flex items-center justify-between border-b border-slate-100">
            <h4 id="modal-user-title" class="font-black text-lg md:text-xl text-slate-900 flex items-center gap-2">
                เพิ่มบุคลากรใหม่
            </h4>
            <button type="button" onclick="closeUserModal()" class="w-8 h-8 flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 md:p-8 space-y-8 max-h-[80vh] overflow-y-auto custom-scrollbar">
        <form id="user-form" onsubmit="handleUserSubmit(event)" class="space-y-8" novalidate>
            <input type="hidden" id="modal-user-id" value="">

            <!-- ข้อมูลส่วนตัว -->
            <div>
                <h4 class="text-sm font-black text-slate-800 mb-4">ข้อมูลส่วนตัว</h4>
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- อัปโหลดรูปโปรไฟล์ -->
                    <div class="w-full md:w-64 shrink-0 flex flex-col items-center justify-center p-6 border border-slate-200 border-dashed bg-slate-50/50 rounded-3xl h-64 relative overflow-hidden group hover:border-blue-400 transition cursor-pointer">
                        <label for="modal-profile-pic" class="absolute inset-0 w-full h-full cursor-pointer z-10 flex flex-col items-center justify-center text-center">
                            <div id="modal-profile-placeholder-text" class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-file-arrow-up text-3xl mb-3 text-blue-400 group-hover:text-blue-500 transition"></i>
                                <span class="font-bold text-slate-900 mb-1 group-hover:text-blue-600 transition">อัปโหลดรูปโปรไฟล์</span>
                                <span class="text-[10px] font-semibold text-slate-900 mt-1">ขนาดไฟล์ไม่เกิน 2 MB</span>
                                <span class="text-[10px] font-semibold text-slate-900">รองรับไฟล์ JPG, PNG</span>
                            </div>
                            <input type="file" id="modal-profile-pic" class="hidden" accept="image/jpeg, image/png" onchange="previewUserModalImage(this)">
                        </label>
                        <!-- Preview image -->
                        <img id="modal-profile-preview" src="" class="hidden absolute inset-0 w-full h-full object-cover rounded-3xl z-30 pointer-events-none">
                    </div>

                    <!-- ฟอร์มข้อมูลส่วนตัว -->
                    <div class="flex-1 space-y-6">
                        <div class="space-y-4">
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">คำนำหน้า</label>
                            <select id="modal-prefix" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 text-slate-800 font-bold text-sm min-h-[46px] invalid:text-slate-600 invalid:font-medium appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:10px_10px] bg-no-repeat bg-[position:right_15px_center]" onchange="this.classList.remove('border-red-500'); document.getElementById('modal-prefix-error').classList.add('hidden');">
                                <option value="" disabled selected class="text-slate-300">เลือกคำนำหน้า</option>
                                <option value="นาย" class="text-slate-700">นาย</option>
                                <option value="นาง" class="text-slate-700">นาง</option>
                                <option value="นางสาว" class="text-slate-700">นางสาว</option>
                            </select>
                            <p id="modal-prefix-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกคำนำหน้า</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">ชื่อ (ภาษาไทย)</label>
                                <input type="text" id="modal-firstname" required placeholder="กรอกชื่อ" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="this.value = this.value.replace(/[^ก-๙\s]/g, ''); this.classList.remove('border-red-500'); document.getElementById('modal-firstname-error').classList.add('hidden');">
                                <p id="modal-firstname-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกชื่อ (ภาษาไทย)</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">นามสกุล (ภาษาไทย)</label>
                                <input type="text" id="modal-lastname" required placeholder="กรอกนามสกุล" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="this.value = this.value.replace(/[^ก-๙\s]/g, ''); this.classList.remove('border-red-500'); document.getElementById('modal-lastname-error').classList.add('hidden');">
                                <p id="modal-lastname-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกนามสกุล (ภาษาไทย)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">ชื่อ (ภาษาอังกฤษ)</label>
                                <input type="text" id="modal-firstname-en" required placeholder="First Name" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); this.classList.remove('border-red-500'); document.getElementById('modal-firstname-en-error').classList.add('hidden');">
                                <p id="modal-firstname-en-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกชื่อ (ภาษาอังกฤษ)</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">นามสกุล (ภาษาอังกฤษ)</label>
                                <input type="text" id="modal-lastname-en" required placeholder="Last Name" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); this.classList.remove('border-red-500'); document.getElementById('modal-lastname-en-error').classList.add('hidden');">
                                <p id="modal-lastname-en-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกนามสกุล (ภาษาอังกฤษ)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">เลขบัตรประชาชน</label>
                                <input type="text" id="modal-citizen-id" required maxlength="13" placeholder="เลขบัตรประชาชน 13 หลัก" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.classList.remove('border-red-500'); const cie = document.getElementById('modal-citizen-id-error'); if(cie){ cie.classList.add('hidden'); cie.innerHTML = '<i class=\'fa-solid fa-circle-exclamation mr-1\'></i>กรุณากรอกเลขบัตรประชาชน 13 หลัก'; } if(this.value.length === 13) validateCitizenIdRealtime();" onblur="if(this.value.length === 13) validateCitizenIdRealtime();">
                                <p id="modal-citizen-id-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเลขบัตรประชาชน 13 หลัก</p>
                            </div>
                        </div>

                        <!-- ข้อมูลการทำงาน -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <h5 class="font-black text-slate-800 mb-3 text-sm">ข้อมูลการทำงาน</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">ประเภทบุคลากร</label>
                                <select id="modal-role" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 text-slate-800 font-bold text-sm min-h-[46px] invalid:text-slate-600 invalid:font-medium appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:10px_10px] bg-no-repeat bg-[position:right_15px_center]" onchange="document.getElementById('modal-role-error').classList.add('hidden'); this.classList.remove('border-red-500');">
                                    <option value="" disabled selected class="text-slate-300">เลือกประเภทบุคลากร</option>
                                    @if(isset($dbPersonnelTypes))
                                        @foreach($dbPersonnelTypes as $pt)
                                            <option value="{{ $pt->id }}" class="text-slate-700">{{ $pt->name }}</option>
                                        @endforeach
                                    @endif

                                </select>
                                <p id="modal-role-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกประเภทบุคลากร</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">ฝ่าย</label>
                                <select id="modal-department-group" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 text-slate-800 font-bold text-sm min-h-[46px] invalid:text-slate-600 invalid:font-medium appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:10px_10px] bg-no-repeat bg-[position:right_15px_center]">
                                    <option value="" disabled selected class="text-slate-300">เลือกฝ่าย</option>
                                    @if(isset($dbDepartmentGroups))
                                        @foreach($dbDepartmentGroups as $dg)
                                            <option value="{{ $dg->group_code }}" class="text-slate-700">{{ $dg->group_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">แผนก/งาน</label>
                                <select id="modal-department" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 text-slate-800 font-bold text-sm min-h-[46px] invalid:text-slate-600 invalid:font-medium appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E')] bg-[length:10px_10px] bg-no-repeat bg-[position:right_15px_center]">
                                    <option value="" disabled selected class="text-slate-300">เลือกแผนก/งาน</option>
                                    @if(isset($dbDepartments))
                                        @foreach($dbDepartments as $d)
                                            <option value="{{ $d->department_id }}" data-group="{{ $d->group }}" class="text-slate-700">{{ $d->department_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="relative">
                                <label class="block text-slate-700 text-xs font-bold mb-2">สิทธิ์การใช้งาน</label>
                                <div id="role-select-container" class="relative">
                                    <button type="button" id="role-select-button" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 text-slate-300 font-bold text-sm min-h-[46px] flex justify-between items-center text-left">
                                        <span id="role-select-text" class="truncate mr-2">เลือกสิทธิ์การใช้งาน</span>
                                        <i class="fa-solid fa-chevron-down text-slate-400"></i>
                                    </button>
                                    <div id="role-select-dropdown" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg hidden max-h-96 overflow-y-auto custom-scrollbar">
                                        <div class="p-2 space-y-1">
                                            <label class="flex items-center px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                <input type="checkbox" name="roles[]" value="admin" class="role-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                                <span class="ml-3 font-medium text-slate-700">Admin(ผู้ดูแลระบบ)</span>
                                            </label>
                                            <label class="flex items-center px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                <input type="checkbox" name="roles[]" value="executive" class="role-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                                <span class="ml-3 font-medium text-slate-700">ผู้บริหาร</span>
                                            </label>
                                            <label class="flex items-center px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                <input type="checkbox" name="roles[]" value="head" class="role-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                                <span class="ml-3 font-medium text-slate-700">หัวหน้างาน</span>
                                            </label>
                                            <label class="flex items-center px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                <input type="checkbox" name="roles[]" value="technician" class="role-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                                <span class="ml-3 font-medium text-slate-700">ช่างซ่อมบำรุง</span>
                                            </label>
                                            <label class="flex items-center px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                                <input type="checkbox" name="roles[]" value="teacher" class="role-checkbox w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                                <span class="ml-3 font-medium text-slate-700">แจ้งซ่อม</span>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="hidden" id="modal-system-role" required>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- บัญชีผู้ใช้งาน -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <h5 class="font-black text-slate-800 mb-3 text-sm">บัญชีผู้ใช้งาน</h5>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">ชื่อผู้ใช้ (Username)</label>
                                <input type="text" id="modal-username" required placeholder="ชื่อผู้ใช้งานระบบ" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-2">รหัสผ่าน</label>
                                <div class="relative">
                                    <input type="password" id="modal-password" placeholder="รหัสผ่าน (อย่างน้อย 6 ตัวอักษร)" class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" onfocus="document.getElementById('password-requirements').classList.remove('hidden');" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, ''); checkPasswordRequirements(this.value); document.getElementById('modal-password-error').classList.add('hidden');">
                                    <button type="button" onclick="toggleAdminPasswordVisibility('modal-password', 'modal-password-eye')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <i class="fa-solid fa-eye" id="modal-password-eye"></i>
                                    </button>
                                </div>
                                <div class="mt-3 bg-slate-50 p-3 rounded-xl border border-slate-100 hidden" id="password-requirements">
                                    <p class="text-[11px] font-bold text-slate-700 mb-2"><i class="fa-solid fa-shield-halved text-blue-500 mr-1"></i> เงื่อนไขรหัสผ่าน:</p>
                                    <ul class="list-none space-y-1.5 text-[10px]">
                                        <li id="req-length" class="flex items-center gap-1.5 text-slate-400 font-semibold transition-colors duration-200"><i class="fa-solid fa-circle text-[5px]"></i> ความยาวอย่างน้อย 6 ตัวอักษร</li>
                                        <li id="req-lower" class="flex items-center gap-1.5 text-slate-400 font-semibold transition-colors duration-200"><i class="fa-solid fa-circle text-[5px]"></i> ตัวอักษรพิมพ์เล็ก (a-z) อย่างน้อย 1 ตัว</li>
                                        <li id="req-upper" class="flex items-center gap-1.5 text-slate-400 font-semibold transition-colors duration-200"><i class="fa-solid fa-circle text-[5px]"></i> ตัวอักษรพิมพ์ใหญ่ (A-Z) อย่างน้อย 1 ตัว</li>
                                        <li id="req-number" class="flex items-center gap-1.5 text-slate-400 font-semibold transition-colors duration-200"><i class="fa-solid fa-circle text-[5px]"></i> ตัวเลข (0-9) อย่างน้อย 1 ตัว</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        </div>
                        <p id="modal-password-error" class="text-red-500 text-[10px] hidden mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i>รหัสผ่านยังไม่ตรงตามเงื่อนไขที่กำหนด</p>
                    </div>
                </div>
            </div>
            </div>
            
            <hr class="border-slate-100 mt-6">

            <!-- ข้อมูลติดต่อ -->
            <div class="pt-6">
                <h5 class="text-sm font-black text-slate-800 mb-3">ข้อมูลติดต่อ</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-2">เบอร์โทรศัพท์</label>
                        <input type="tel" id="modal-phone" required placeholder="กรอกเบอร์โทรศัพท์" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="let v = this.value.replace(/\D/g, '').substring(0, 10); this.value = v.length > 7 ? v.substring(0,3) + '-' + v.substring(3,7) + '-' + v.substring(7) : v.length > 3 ? v.substring(0,3) + '-' + v.substring(3) : v; this.classList.remove('border-red-500'); const pe = document.getElementById('modal-phone-error'); if(pe){ pe.classList.add('hidden'); pe.innerHTML = '<i class=\'fa-solid fa-circle-exclamation mr-1\'></i>กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก'; } if(v.length === 10) validatePhoneRealtime();" onblur="let v = this.value.replace(/\D/g, '').substring(0, 10); if(v.length === 10) validatePhoneRealtime();" maxlength="12">
                        <p id="modal-phone-error" class="text-red-500 text-[10px] hidden mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก</p>
                    </div>
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-2">อีเมล</label>
                        <input type="email" id="modal-email" required placeholder="กรอกอีเมล" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 placeholder:text-slate-600 placeholder:font-medium text-slate-800 font-bold text-sm min-h-[46px]" oninput="this.classList.remove('border-red-500'); document.getElementById('modal-email-error')?.classList.add('hidden');">
                        <p id="modal-email-error" class="text-red-500 text-[10px] hidden mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i>รูปแบบอีเมลไม่ถูกต้อง</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-100 mt-6">
                <button type="button" onclick="closeUserModal()" class="px-7 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl font-bold transition cursor-pointer">ยกเลิก</button>
                <button type="submit" id="modal-submit-btn" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-extrabold transition shadow-md shadow-blue-200 cursor-pointer flex items-center justify-center gap-2"><span>บันทึก</span></button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- JavaScript ควบคุมระบบจัดการผู้ใช้งานทั้งหมด -->
<script>
    function checkPasswordRequirements(password) {
        const reqLength = document.getElementById('req-length');
        const reqLower = document.getElementById('req-lower');
        const reqUpper = document.getElementById('req-upper');
        const reqNumber = document.getElementById('req-number');

        if (!reqLength) return;

        const setValid = (el, isValid) => {
            if (isValid) {
                el.classList.remove('text-slate-400');
                el.classList.add('text-emerald-600');
                el.querySelector('i').classList.replace('fa-circle', 'fa-circle-check');
                el.querySelector('i').classList.remove('text-[5px]');
            } else {
                el.classList.remove('text-emerald-600');
                el.classList.add('text-slate-400');
                el.querySelector('i').classList.replace('fa-circle-check', 'fa-circle');
                el.querySelector('i').classList.add('text-[5px]');
            }
        };

        setValid(reqLength, password.length >= 6);
        setValid(reqLower, /[a-z]/.test(password));
        setValid(reqUpper, /[A-Z]/.test(password));
        setValid(reqNumber, /[0-9]/.test(password));
    }

    // ฟังก์ชันตรวจสอบความซ้ำซ้อนของเลขบัตรประชาชน และเบอร์โทรศัพท์
    async function checkDuplicateField(type, value, excludeUserId = '') {
        if (!value) return false;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const baseUrl = window.BASE_URL || "{{ url('') }}";
            const res = await fetch(baseUrl + '/admin/users/check-duplicate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    type: type,
                    value: value,
                    exclude_user_id: excludeUserId
                })
            });
            const data = await res.json();
            return !!data.exists;
        } catch (err) {
            console.error('Check duplicate error:', err);
            return false;
        }
    }

    function showCitizenIdDuplicateWarning() {
        const modal = document.getElementById('user-modal');
        if (modal) modal.style.display = 'flex';

        const submitBtn = document.getElementById('modal-submit-btn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>บันทึก</span>';
        }

        const input = document.getElementById('modal-citizen-id');
        const err = document.getElementById('modal-citizen-id-error');
        if (input) {
            input.classList.add('border-red-500');
        }
        if (err) {
            err.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว';
            err.classList.remove('hidden');
        }
        Swal.fire({
            scrollbarPadding: false,
            icon: 'warning',
            title: 'มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว',
            text: 'หมายเลขบัตรประชาชนนี้มีข้อมูลในระบบแล้ว กรุณาตรวจสอบอีกครั้ง',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#2563eb',
            heightAuto: false,
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-slate-100',
                title: 'text-lg font-bold text-slate-800'
            }
        }).then(() => {
            const m = document.getElementById('user-modal');
            if (m) m.style.display = 'flex';
            if (input) {
                setTimeout(() => {
                    input.focus();
                    input.select();
                }, 100);
            }
        });
    }

    function showPhoneDuplicateWarning() {
        const modal = document.getElementById('user-modal');
        if (modal) modal.style.display = 'flex';

        const submitBtn = document.getElementById('modal-submit-btn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>บันทึก</span>';
        }

        const input = document.getElementById('modal-phone');
        const err = document.getElementById('modal-phone-error');
        if (input) {
            input.classList.add('border-red-500');
        }
        if (err) {
            err.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>เบอร์โทรศัพท์ซ้ำ';
            err.classList.remove('hidden');
        }
        Swal.fire({
            scrollbarPadding: false,
            icon: 'warning',
            title: 'เบอร์โทรศัพท์ซ้ำ',
            text: 'เบอร์โทรศัพท์นี้มีอยู่ในระบบแล้ว กรุณาตรวจสอบอีกครั้ง',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#2563eb',
            heightAuto: false,
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-slate-100',
                title: 'text-lg font-bold text-slate-800'
            }
        }).then(() => {
            const m = document.getElementById('user-modal');
            if (m) m.style.display = 'flex';
            if (input) {
                setTimeout(() => {
                    input.focus();
                    input.select();
                }, 100);
            }
        });
    }

    async function validateCitizenIdRealtime() {
        const input = document.getElementById('modal-citizen-id');
        const err = document.getElementById('modal-citizen-id-error');
        if (!input || input.disabled) return;
        const val = input.value.replace(/\D/g, '');
        const userId = document.getElementById('modal-user-id')?.value || '';
        if (val.length === 13) {
            const isDup = await checkDuplicateField('citizen_id', val, userId);
            if (isDup) {
                input.classList.add('border-red-500');
                if (err) {
                    err.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว';
                    err.classList.remove('hidden');
                }
            } else {
                input.classList.remove('border-red-500');
                if (err) err.classList.add('hidden');
            }
        } else {
            if (err && (err.innerText.includes('มีการลงทะเบียนด้วยเลขบัตรประชาชนนี้แล้ว') || err.innerText.includes('หมายเลขบัตรประชาชนซ้ำ'))) {
                err.classList.add('hidden');
                err.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเลขบัตรประชาชน 13 หลัก';
            }
        }
    }

    async function validatePhoneRealtime() {
        const input = document.getElementById('modal-phone');
        const err = document.getElementById('modal-phone-error');
        if (!input || input.disabled) return;
        const cleanPhone = (input.value || '').replace(/\D/g, '').substring(0, 10);
        const userId = document.getElementById('modal-user-id')?.value || '';
        if (cleanPhone.length === 10) {
            const isDup = await checkDuplicateField('phone', cleanPhone, userId);
            if (isDup) {
                input.classList.add('border-red-500');
                if (err) {
                    err.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>เบอร์โทรศัพท์ซ้ำ';
                    err.classList.remove('hidden');
                }
            } else {
                input.classList.remove('border-red-500');
                if (err) err.classList.add('hidden');
            }
        } else {
            if (err && err.innerText.includes('เบอร์โทรศัพท์ซ้ำ')) {
                err.classList.add('hidden');
                err.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก';
            }
        }
    }

    // 1. ฟังก์ชันเปิด Modal เพิ่มผู้ใช้งาน
    function prepareCreateUserForm() {
        document.getElementById('modal-user-title').innerHTML = 'เพิ่มบุคลากรใหม่';
        if (document.getElementById('modal-user-breadcrumb')) {
            document.getElementById('modal-user-breadcrumb').innerHTML = 'จัดการข้อมูลบุคลากร <i class="fa-solid fa-chevron-right text-[10px] mx-1"></i> เพิ่มบุคลากรใหม่';
        }
        document.getElementById('modal-user-id').value = '';
        document.getElementById('user-form').reset();
        
        const roleError = document.getElementById('modal-role-error');
        if (roleError) roleError.classList.add('hidden');
        const roleEl = document.getElementById('modal-role');
        if (roleEl) roleEl.classList.remove('border-red-500');

        // Reset system role multi-select
        const roleSelectText = document.getElementById('role-select-text');
        const roleCheckboxes = document.querySelectorAll('.role-checkbox');
        if (roleSelectText) {
            roleSelectText.innerText = 'เลือกสิทธิ์การใช้งาน';
            const btn = document.getElementById('role-select-button');
            if (btn) {
                btn.classList.replace('text-slate-700', 'text-slate-300');
                btn.classList.add('font-medium');
                btn.disabled = false;
                btn.classList.remove('bg-slate-50', 'cursor-not-allowed', 'opacity-70');
                btn.classList.add('bg-white');
            }
        }
        roleCheckboxes.forEach(cb => cb.checked = false);
        const modalSystemRole = document.getElementById('modal-system-role');
        if (modalSystemRole) modalSystemRole.value = '';
        
        if (document.getElementById('modal-citizen-id')) {
            document.getElementById('modal-citizen-id').value = '';
            document.getElementById('modal-citizen-id').classList.remove('border-red-500');
        }
        if (document.getElementById('modal-citizen-id-error')) {
            document.getElementById('modal-citizen-id-error').classList.add('hidden');
            document.getElementById('modal-citizen-id-error').innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเลขบัตรประชาชน 13 หลัก';
        }
        if (document.getElementById('modal-phone')) {
            document.getElementById('modal-phone').value = '';
            document.getElementById('modal-phone').classList.remove('border-red-500');
        }
        if (document.getElementById('modal-phone-error')) {
            document.getElementById('modal-phone-error').classList.add('hidden');
            document.getElementById('modal-phone-error').innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก';
        }
        if (document.getElementById('modal-username')) document.getElementById('modal-username').value = '';
        if (document.getElementById('modal-password')) {
            document.getElementById('modal-password').value = '';
            if (typeof checkPasswordRequirements === 'function') checkPasswordRequirements('');
        }
        if (document.getElementById('password-requirements')) document.getElementById('password-requirements').classList.add('hidden');
        if (document.getElementById('modal-password-req')) document.getElementById('modal-password-req').classList.remove('hidden');
        if (document.getElementById('modal-password-error')) document.getElementById('modal-password-error').classList.add('hidden');
        
        // Reset department options visibility
        const departmentSelect = document.getElementById('modal-department');
        if (departmentSelect) {
            departmentSelect.disabled = false;
            departmentSelect.querySelectorAll('option:not([value=""])').forEach(opt => {
                opt.style.display = '';
                opt.hidden = false;
                opt.disabled = false;
            });
        }
        const fieldsToToggle = [
            'modal-prefix', 'modal-firstname', 'modal-lastname', 
            'modal-firstname-en', 'modal-lastname-en', 'modal-role',
            'modal-department-group', 'modal-department', 
            'modal-phone', 'modal-email', 'modal-profile-pic',
            'modal-citizen-id', 'modal-username', 'modal-password'
        ];
        fieldsToToggle.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = false;
                el.readOnly = false;
                el.classList.remove('bg-slate-50', 'opacity-70', 'cursor-not-allowed', 'text-slate-500');
            }
        });
        const picLabel = document.querySelector('label[for="modal-profile-pic"]');
        if (picLabel) {
            picLabel.classList.remove('opacity-70', 'pointer-events-none', 'bg-slate-50', 'opacity-0');
            picLabel.classList.add('opacity-100');
        }
        
        const previewImg = document.getElementById('modal-profile-preview');
        if (previewImg) {
            previewImg.src = '';
            previewImg.classList.add('hidden');
        }
        const placeholder = document.getElementById('modal-profile-placeholder-text');
        if (placeholder) placeholder.classList.remove('hidden');

        document.getElementById('user-modal').style.display = 'flex';
    }

    // 2. ฟังก์ชันเปิด Modal แก้ไขผู้ใช้งาน
    function editUser(id, context = 'users') {
        if (context === 'personnel') {
            document.getElementById('modal-user-title').innerHTML = 'แก้ไขข้อมูลบุคลากร';
            if (document.getElementById('modal-user-breadcrumb')) {
                document.getElementById('modal-user-breadcrumb').innerHTML = 'จัดการข้อมูลบุคลากร <i class="fa-solid fa-chevron-right text-[10px] mx-1"></i> แก้ไขข้อมูลบุคลากร';
            }
        } else {
            document.getElementById('modal-user-title').innerHTML = 'แก้ไขสิทธิ์ผู้ใช้งาน';
            if (document.getElementById('modal-user-breadcrumb')) {
                document.getElementById('modal-user-breadcrumb').innerHTML = 'จัดการสิทธิ์ผู้ใช้งาน <i class="fa-solid fa-chevron-right text-[10px] mx-1"></i> แก้ไขสิทธิ์ผู้ใช้งาน';
            }
        }
        document.getElementById('modal-user-id').value = id;
        
        const roleError = document.getElementById('modal-role-error');
        if (roleError) roleError.classList.add('hidden');
        const roleEl = document.getElementById('modal-role');
        if (roleEl) roleEl.classList.remove('border-red-500');

        if (document.getElementById('modal-citizen-id')) {
            document.getElementById('modal-citizen-id').classList.remove('border-red-500');
        }
        if (document.getElementById('modal-citizen-id-error')) {
            document.getElementById('modal-citizen-id-error').classList.add('hidden');
            document.getElementById('modal-citizen-id-error').innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเลขบัตรประชาชน 13 หลัก';
        }
        if (document.getElementById('modal-phone')) {
            document.getElementById('modal-phone').classList.remove('border-red-500');
        }
        if (document.getElementById('modal-phone-error')) {
            document.getElementById('modal-phone-error').classList.add('hidden');
            document.getElementById('modal-phone-error').innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก';
        }
        
        const row = document.querySelector(`tr.user-row[data-user-id="${id}"]`);
        if (!row) return;

        const userDataBase64 = row.getAttribute('data-user');
        let userData = {};
        try {
            userData = JSON.parse(decodeURIComponent(userDataBase64));
        } catch (e) {
            console.error('Failed to parse user data', e);
            return;
        }
        
        // Populate actual user data
        if (document.getElementById('modal-prefix')) document.getElementById('modal-prefix').value = userData.prefix || '';
        if (document.getElementById('modal-email')) document.getElementById('modal-email').value = userData.email || '';
        if (document.getElementById('modal-firstname')) document.getElementById('modal-firstname').value = userData.first_name || '';
        if (document.getElementById('modal-lastname')) document.getElementById('modal-lastname').value = userData.last_name || '';
        if (document.getElementById('modal-firstname-en')) document.getElementById('modal-firstname-en').value = userData.first_name_en || '';
        if (document.getElementById('modal-lastname-en')) document.getElementById('modal-lastname-en').value = userData.last_name_en || '';
        if (document.getElementById('modal-phone')) document.getElementById('modal-phone').value = userData.phone || '';
        if (document.getElementById('modal-citizen-id')) {
            const czInput = document.getElementById('modal-citizen-id');
            czInput.value = userData.citizen_id || '';
            czInput.disabled = true;
            czInput.classList.add('bg-slate-50', 'opacity-70', 'cursor-not-allowed');
        }
        if (document.getElementById('modal-username')) {
            const unInput = document.getElementById('modal-username');
            unInput.value = userData.user_id || userData.username || '';
            unInput.disabled = true;
            unInput.classList.add('bg-slate-50', 'opacity-70', 'cursor-not-allowed');
        }
        if (document.getElementById('modal-password')) {
            if ((userData.username && userData.username.toLowerCase() === 'admin') || 
                (userData.user_id && userData.user_id.toLowerCase() === 'admin')) {
                document.getElementById('modal-password').value = '';
                document.getElementById('modal-password').type = 'text';
                
                const eyeIcon = document.getElementById('modal-password-eye');
                if(eyeIcon) {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            } else if (userData.plain_password) {
                document.getElementById('modal-password').value = userData.plain_password;
                document.getElementById('modal-password').type = 'text';
                
                const eyeIcon = document.getElementById('modal-password-eye');
                if(eyeIcon) {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            } else {
                document.getElementById('modal-password').value = '';
                document.getElementById('modal-password').type = 'text';
                
                const eyeIcon = document.getElementById('modal-password-eye');
                if(eyeIcon) {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            }
        }
        
        if (document.getElementById('modal-password-req')) document.getElementById('modal-password-req').classList.add('hidden');
        if (document.getElementById('modal-password-error')) document.getElementById('modal-password-error').classList.add('hidden');
        
        // Smart select for role (Personnel Type)
        const roleSelect = document.getElementById('modal-role');
        if (roleSelect) {
            roleSelect.value = userData.role;
            if (!roleSelect.value) {
                // Try to find an option whose text matches the user's position or roleThai
                let matched = false;
                Array.from(roleSelect.options).forEach(opt => {
                    if (opt.value === "") return;
                    const optText = opt.text.trim();
                    if (optText === (userData.position || '').trim() || 
                        optText === (userData.roleThai || '').trim()) {
                        roleSelect.value = opt.value;
                        matched = true;
                    }
                });
                
                // If still not found, try manual fallback mapping
                if (!matched) {
                    if (userData.role === 'admin') roleSelect.value = "5";
                    else if (userData.role === 'head') roleSelect.value = "3";
                    else if (userData.role === 'teacher') roleSelect.value = "1";
                    else if (userData.role === 'staff') roleSelect.value = "6";
                    else roleSelect.value = "";
                }
            }
        }
        
        // Smart select for Department Group
        const deptGroupSelect = document.getElementById('modal-department-group');
        if (deptGroupSelect) {
            deptGroupSelect.value = userData.department_group || '';
        }

        // Smart select for Department
        const departmentSelect = document.getElementById('modal-department');
        if (departmentSelect) {
            departmentSelect.disabled = false;
            // First try setting by exact value if it's already an ID
            departmentSelect.value = userData.department || '';
            
            // If it failed to select (meaning userData.department is likely a string name), try finding by text
            if (!departmentSelect.value && userData.department) {
                Array.from(departmentSelect.options).forEach(opt => {
                    if (opt.text.trim() === userData.department.trim()) {
                        departmentSelect.value = opt.value;
                    }
                });
            }
            
            // Show only options that match the selected group
            const currentGroup = deptGroupSelect ? deptGroupSelect.value : '';
            Array.from(departmentSelect.options).forEach(opt => {
                if (opt.value === "") return;
                if (currentGroup && opt.getAttribute('data-group') === currentGroup) {
                    opt.style.display = 'block';
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    opt.style.display = 'none';
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
        }
        
        // Smart select for System Role (Multi-select)
        const systemRoleHidden = document.getElementById('modal-system-role');
        const roleCheckboxes = document.querySelectorAll('.role-checkbox');
        const roleSelectText = document.getElementById('role-select-text');
        
        if (systemRoleHidden && roleCheckboxes.length > 0) {
            let roles = [];
            if (userData.role) {
                roles = userData.role.split(',').map(r => r.trim());
            }
            systemRoleHidden.value = roles.join(',');
            
            let selectedTexts = [];
            roleCheckboxes.forEach(cb => {
                cb.checked = roles.includes(cb.value);
                if (cb.checked) {
                    selectedTexts.push(cb.nextElementSibling.innerText);
                }
            });
            
            if (selectedTexts.length > 0) {
                roleSelectText.innerText = selectedTexts.join(', ');
                const btn = document.getElementById('role-select-button');
                if (btn) {
                    btn.classList.replace('text-slate-300', 'text-slate-700');
                    btn.classList.remove('font-medium');
                }
            } else {
                roleSelectText.innerText = 'เลือกสิทธิ์การใช้งาน';
                const btn = document.getElementById('role-select-button');
                if (btn) {
                    btn.classList.replace('text-slate-700', 'text-slate-300');
                    btn.classList.add('font-medium');
                }
            }
        }
        
        const btn = document.getElementById('role-select-button');
        if (btn) {
            if (context === 'personnel') {
                btn.disabled = true;
                btn.classList.remove('bg-white');
                btn.classList.add('bg-slate-50', 'cursor-not-allowed', 'opacity-70');
            } else {
                btn.disabled = false;
                btn.classList.remove('bg-slate-50', 'cursor-not-allowed', 'opacity-70');
                btn.classList.add('bg-white');
            }
        }

        const fieldsToToggle = [
            'modal-prefix', 'modal-firstname', 'modal-lastname', 
            'modal-firstname-en', 'modal-lastname-en', 'modal-role',
            'modal-department-group', 'modal-department', 
            'modal-phone', 'modal-email', 'modal-profile-pic',
            'modal-password'
        ];
        
        fieldsToToggle.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (context === 'users') {
                    el.disabled = true;
                    el.classList.add('bg-slate-50', 'opacity-70', 'cursor-not-allowed');
                } else {
                    el.disabled = false;
                    el.classList.remove('bg-slate-50', 'opacity-70', 'cursor-not-allowed');
                }
            }
        });
        
        const picLabel = document.querySelector('label[for="modal-profile-pic"]');
        if (picLabel) {
            if (context === 'users') {
                picLabel.classList.add('opacity-70', 'pointer-events-none', 'bg-slate-50');
            } else {
                picLabel.classList.remove('opacity-70', 'pointer-events-none', 'bg-slate-50', 'opacity-0');
                picLabel.classList.add('opacity-100');
            }
        }
        
        const previewImg2 = document.getElementById('modal-profile-preview');
        const placeholder2 = document.getElementById('modal-profile-placeholder-text');
        if (previewImg2) {
            if (userData.profile_photo) {
                const photoUrl = '{{ asset("uploads/profiles") }}/' + userData.profile_photo;
                previewImg2.src = photoUrl;
                previewImg2.classList.remove('hidden');
                if (placeholder2) placeholder2.classList.add('hidden');
                if (picLabel && context !== 'users') {
                    picLabel.classList.add('opacity-0');
                    picLabel.classList.remove('opacity-100');
                }
            } else {
                previewImg2.src = '';
                previewImg2.classList.add('hidden');
                if (placeholder2) placeholder2.classList.remove('hidden');
                if (picLabel && context !== 'users') {
                    picLabel.classList.remove('opacity-0');
                    picLabel.classList.add('opacity-100');
                }
            }
        }
        

        document.getElementById('user-modal').style.display = 'flex';
    }

    // 3. ปิด Modal
    function closeUserModal() {
        document.getElementById('user-modal').style.display = 'none';
    }

    // 4. Submit Form
    async function handleUserSubmit(e) {
        e.preventDefault();
        const userId = document.getElementById('modal-user-id').value;
        const isEdit = !!userId;
        
        const row = document.querySelector(`tr.user-row[data-user-id="${userId}"]`) || document.querySelector(`tr.personnel-row[data-user-id="${userId}"]`);
        
        let userData = {};
        if (row) {
            // ดึงข้อมูลเดิมมาอัปเดต
            try {
                userData = JSON.parse(decodeURIComponent(row.getAttribute('data-user')));
            } catch (error) {}
        }
            
        // รับค่าจากฟอร์ม
        const prefix = document.getElementById('modal-prefix')?.value || '';
        const email = document.getElementById('modal-email')?.value || '';
        const firstName = document.getElementById('modal-firstname')?.value || '';
        const lastName = document.getElementById('modal-lastname')?.value || '';
        const firstNameEn = document.getElementById('modal-firstname-en')?.value || '';
        const lastNameEn = document.getElementById('modal-lastname-en')?.value || '';
        const phone = (document.getElementById('modal-phone')?.value || '').replace(/\D/g, '');
        const citizenId = (document.getElementById('modal-citizen-id')?.value || '').replace(/\D/g, '');
        const isCitizenDisabled = document.getElementById('modal-citizen-id')?.disabled;
            
        // Hide all errors first
        document.getElementById('modal-prefix-error')?.classList.add('hidden');
        document.getElementById('modal-firstname-error')?.classList.add('hidden');
        document.getElementById('modal-lastname-error')?.classList.add('hidden');
        document.getElementById('modal-firstname-en-error')?.classList.add('hidden');
        document.getElementById('modal-lastname-en-error')?.classList.add('hidden');
        document.getElementById('modal-prefix')?.classList.remove('border-red-500');
        document.getElementById('modal-firstname')?.classList.remove('border-red-500');
        document.getElementById('modal-lastname')?.classList.remove('border-red-500');
        document.getElementById('modal-firstname-en')?.classList.remove('border-red-500');
        document.getElementById('modal-lastname-en')?.classList.remove('border-red-500');
        if(document.getElementById('modal-phone')) document.getElementById('modal-phone').classList.remove('border-red-500');
        if(document.getElementById('modal-phone-error')) document.getElementById('modal-phone-error').classList.add('hidden');
        if(document.getElementById('modal-citizen-id')) document.getElementById('modal-citizen-id').classList.remove('border-red-500');
        if(document.getElementById('modal-citizen-id-error')) document.getElementById('modal-citizen-id-error').classList.add('hidden');
            
        // Custom Name Validation (One by one)
        if (!prefix) {
            document.getElementById('modal-prefix-error')?.classList.remove('hidden');
            document.getElementById('modal-prefix')?.classList.add('border-red-500');
            document.getElementById('modal-prefix')?.focus();
            return;
        } else if (!firstName) {
            document.getElementById('modal-firstname-error')?.classList.remove('hidden');
            document.getElementById('modal-firstname')?.classList.add('border-red-500');
            document.getElementById('modal-firstname')?.focus();
            return;
        } else if (!lastName) {
            document.getElementById('modal-lastname-error')?.classList.remove('hidden');
            document.getElementById('modal-lastname')?.classList.add('border-red-500');
            document.getElementById('modal-lastname')?.focus();
            return;
        } else if (!firstNameEn) {
            document.getElementById('modal-firstname-en-error')?.classList.remove('hidden');
            document.getElementById('modal-firstname-en')?.classList.add('border-red-500');
            document.getElementById('modal-firstname-en')?.focus();
            return;
        } else if (!lastNameEn) {
            document.getElementById('modal-lastname-en-error')?.classList.remove('hidden');
            document.getElementById('modal-lastname-en')?.classList.add('border-red-500');
            document.getElementById('modal-lastname-en')?.focus();
            return;
        }

        // 1. ตรวจสอบเลขบัตรประชาชนก่อน (เตือนทีละช่อง)
        if (!isEdit && !citizenId) {
            const cErr = document.getElementById('modal-citizen-id-error');
            if(cErr) {
                cErr.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเลขบัตรประชาชน 13 หลัก';
                cErr.classList.remove('hidden');
            }
            document.getElementById('modal-citizen-id')?.classList.add('border-red-500');
            document.getElementById('modal-citizen-id')?.focus();
            return;
        }
        if (citizenId && citizenId.length !== 13) {
            const cErr = document.getElementById('modal-citizen-id-error');
            if(cErr) {
                cErr.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเลขบัตรประชาชน 13 หลัก';
                cErr.classList.remove('hidden');
            }
            document.getElementById('modal-citizen-id')?.classList.add('border-red-500');
            document.getElementById('modal-citizen-id')?.focus();
            return;
        }

        // ตรวจสอบเลขบัตรประชาชนซ้ำกับระบบ
        if (!isCitizenDisabled && citizenId) {
            const isCitizenDup = await checkDuplicateField('citizen_id', citizenId, isEdit ? (userData.user_id || userId) : '');
            if (isCitizenDup) {
                showCitizenIdDuplicateWarning();
                return; // หยุดการทำงานทันที (เตือนทีละช่อง - ยังไม่เตือนเบอร์โทร)
            }
        }

        // 2. ตรวจสอบประเภทบุคลากร
        const roleEl = document.getElementById('modal-role');
        if (!roleEl || !roleEl.value) {
            document.getElementById('modal-role-error')?.classList.remove('hidden');
            roleEl?.classList.add('border-red-500');
            roleEl?.focus();
            return;
        }

        // 3. ตรวจสอบเบอร์โทรศัพท์ (เตือนทีละช่อง - หลังจากเลขบัตรประชาชนผ่านแล้ว)
        if (phone.length !== 10) {
            const phoneError = document.getElementById('modal-phone-error');
            if (phoneError) {
                phoneError.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกเบอร์โทรศัพท์ให้ครบ 10 หลัก';
                phoneError.classList.remove('hidden');
            }
            document.getElementById('modal-phone')?.classList.add('border-red-500');
            document.getElementById('modal-phone')?.focus();
            return;
        }

        // ตรวจสอบเบอร์โทรศัพท์ซ้ำกับระบบ
        const isPhoneDup = await checkDuplicateField('phone', phone, isEdit ? (userData.user_id || userId) : '');
        if (isPhoneDup) {
            showPhoneDuplicateWarning();
            return; // หยุดการทำงานทันที (เตือนเบอร์โทรศัพท์ซ้ำ)
        }

        // 4. ตรวจสอบอีเมล
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            const modal = document.getElementById('user-modal');
            if (modal) modal.style.display = 'flex';
            const emailInput = document.getElementById('modal-email');
            const emailError = document.getElementById('modal-email-error');
            if (emailInput) {
                emailInput.classList.add('border-red-500');
            }
            if (emailError) {
                emailError.classList.remove('hidden');
            }
            Swal.fire({
                scrollbarPadding: false,
                icon: 'warning',
                title: 'กรุณากรอกอีเมลที่ถูกต้อง',
                text: 'รูปแบบอีเมลไม่ถูกต้อง เช่น example@gmail.com',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#2563eb',
                heightAuto: false,
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    title: 'text-lg font-bold text-slate-800'
                }
            }).then(() => {
                const m = document.getElementById('user-modal');
                if (m) m.style.display = 'flex';
                if (emailInput) {
                    setTimeout(() => {
                        emailInput.focus();
                        emailInput.select();
                    }, 100);
                }
            });
            return;
        }

        const roleElVal = roleEl.value;
        const positionThai = roleEl.options[roleEl.selectedIndex]?.text || userData.position || '';
        
        const systemRoleEl = document.getElementById('modal-system-role');
        const newRole = systemRoleEl ? systemRoleEl.value : userData.role;
        
        let newRoleThai = userData.roleThai || '';
        const roleSelectText = document.getElementById('role-select-text');
        if (roleSelectText && roleSelectText.innerText !== 'เลือกสิทธิ์การใช้งาน') {
            newRoleThai = roleSelectText.innerText;
        }
        
        const deptGroupEl = document.getElementById('modal-department-group');
        const deptGroup = deptGroupEl?.value || userData.department_group || '';
        
        const deptEl = document.getElementById('modal-department');
        const dept = deptEl?.value || userData.department || '';
        const deptName = deptEl?.options[deptEl.selectedIndex]?.text || userData.department || '';
        
        const usernameInput = document.getElementById('modal-username')?.value || '';
        const passwordInput = document.getElementById('modal-password')?.value || '';
        const passwordConfirm = document.getElementById('modal-password-confirm')?.value || '';
        
        if (!usernameInput) {
            const modal = document.getElementById('user-modal');
            if (modal) modal.style.display = 'flex';
            const usernameInputEl = document.getElementById('modal-username');
            if (usernameInputEl) usernameInputEl.classList.add('border-red-500');
            Swal.fire({
                scrollbarPadding: false,
                icon: 'warning',
                title: 'กรุณากรอกชื่อผู้ใช้',
                heightAuto: false,
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    title: 'text-lg font-bold text-slate-800'
                }
            }).then(() => {
                const m = document.getElementById('user-modal');
                if (m) m.style.display = 'flex';
                if (usernameInputEl) {
                    setTimeout(() => {
                        usernameInputEl.focus();
                        usernameInputEl.select();
                    }, 100);
                }
            });
            return;
        }
        
        // บังคับกรอก password เฉพาะเมื่อเพิ่มใหม่ หรือถ้ากรอกมาแล้วต้องตรวจสอบรูปแบบ
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/;
        if (!isEdit) {
            if (!passwordInput || !passwordRegex.test(passwordInput)) {
                document.getElementById('modal-password-error')?.classList.remove('hidden');
                document.getElementById('modal-password')?.focus();
                return;
            }
        } else if (passwordInput) {
            if (!passwordRegex.test(passwordInput)) {
                document.getElementById('modal-password-error')?.classList.remove('hidden');
                document.getElementById('modal-password')?.focus();
                return;
            }
        }
        
        document.getElementById('modal-password-error')?.classList.add('hidden');
        
        // เตรียมข้อมูลส่งไป Backend
        const formData = new FormData();
        formData.append('citizen_id', citizenId);
        formData.append('username', usernameInput || userData.username || email.split('@')[0].substring(0, 8));
        if (!isEdit) {
            formData.append('password', passwordInput);
        } else if (passwordInput) {
            formData.append('password', passwordInput);
        }
        formData.append('prefix', prefix);
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('first_name_en', firstNameEn);
        formData.append('last_name_en', lastNameEn);
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('position', positionThai);
        formData.append('department', deptName);
        formData.append('role', newRole || 'teacher');
        formData.append('gender', userData.gender || '');
        
        const fileInput = document.getElementById('modal-profile-pic');
        if (fileInput && fileInput.files[0]) {
            formData.append('profile_photo', fileInput.files[0]);
        }
        
        const submitBtn = document.getElementById('modal-submit-btn');
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'บันทึก';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>กำลังบันทึก...';
        }
        
        const baseUrl = window.BASE_URL || "{{ url('') }}";
        const targetUrl = isEdit ? baseUrl + `/admin/users/${userData.db_id || userId}/update` : baseUrl + '/admin/users/store';
        
        try {
            const res = await fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData
            });
            const data = await res.json();
            
            if (data.success) {
                closeUserModal();
                if (!isEdit) {
                    Swal.fire({
                        scrollbarPadding: false,
                        icon: 'success',
                        title: 'เพิ่มข้อมูลเรียบร้อย',
                        showConfirmButton: false,
                        timer: 1500,
                        heightAuto: false,
                        customClass: {
                            popup: 'rounded-2xl shadow-xl border border-slate-100',
                            title: 'text-lg font-bold text-slate-800'
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }

                // อัปเดตข้อมูลเก็บไว้ใน attribute
                userData.email = email;
                userData.prefix = prefix;
                userData.first_name = firstName;
                userData.last_name = lastName;
                userData.first_name_en = firstNameEn;
                userData.last_name_en = lastNameEn;
                userData.phone = phone;
                userData.role = newRole;
                userData.roleThai = newRoleThai;
                userData.position = positionThai;
                userData.department_group = deptGroup;
                userData.department = deptName;
                userData.citizen_id = citizenId;
                userData.user_id = usernameInput || userData.user_id;
                
                const passInput = document.getElementById('modal-password');
                if (passInput && passInput.value) {
                    userData.plain_password = passInput.value;
                }
                
                const newDataStr = encodeURIComponent(JSON.stringify(userData));
                
                // อัปเดตข้อมูลบนตาราง (tab-users)
                const uRow = document.querySelector(`tr.user-row[data-user-id="${userId}"]`);
                if (uRow) {
                    const fnameCell = uRow.querySelector('.user-fname');
                    const lnameCell = uRow.querySelector('.user-lname');
                    const emailCell = uRow.querySelector('.user-email');
                    const roleCell = uRow.querySelector('.user-role');
                    if (fnameCell) fnameCell.innerText = firstName;
                    if (lnameCell) lnameCell.innerText = lastName;
                    if (emailCell) emailCell.innerText = email;
                    if (roleCell) roleCell.innerText = newRoleThai;
                    uRow.setAttribute('data-user', newDataStr);
                }
                
                // อัปเดตข้อมูลบนตาราง (tab-personnel)
                const pRow = document.querySelector(`tr.personnel-row[data-user-id="${userId}"]`);
                if (pRow) {
                    const pCells = pRow.querySelectorAll('td');
                    if (pCells.length >= 6) {
                        pCells[1].innerText = firstName;
                        pCells[2].innerText = lastName;
                        pCells[3].innerText = positionThai || '-';
                        pCells[4].innerText = email;
                    }
                    pRow.setAttribute('data-user', newDataStr);
                }
                
                Swal.fire({
                    scrollbarPadding: false,
                    icon: 'success',
                    title: 'บันทึกข้อมูลเรียบร้อย',
                    showConfirmButton: false,
                    timer: 1500,
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        title: 'text-lg font-bold text-slate-800'
                    }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                const modal = document.getElementById('user-modal');
                if (modal) modal.style.display = 'flex';

                if (data.field === 'citizen_id' || (data.errors && data.errors.citizen_id)) {
                    showCitizenIdDuplicateWarning();
                    return;
                }
                if (data.field === 'phone' || (data.errors && data.errors.phone)) {
                    showPhoneDuplicateWarning();
                    return;
                }
                if (data.field === 'username' || (data.errors && data.errors.username)) {
                    const usernameInput = document.getElementById('modal-username');
                    if (usernameInput) usernameInput.classList.add('border-red-500');
                    const userMsg = data.errors?.username ? data.errors.username[0] : (data.message || 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว');
                    Swal.fire({
                        scrollbarPadding: false,
                        icon: 'warning',
                        title: 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว',
                        text: userMsg,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#2563eb',
                        heightAuto: false,
                        customClass: {
                            popup: 'rounded-2xl shadow-xl border border-slate-100',
                            title: 'text-lg font-bold text-slate-800'
                        }
                    }).then(() => {
                        const m2 = document.getElementById('user-modal');
                        if (m2) m2.style.display = 'flex';
                        if (usernameInput) setTimeout(() => { usernameInput.focus(); usernameInput.select(); }, 100);
                    });
                    return;
                }
                if (data.field === 'email' || (data.errors && data.errors.email)) {
                    const emailInput = document.getElementById('modal-email');
                    if (emailInput) emailInput.classList.add('border-red-500');
                    const emailMsg = data.errors?.email ? data.errors.email[0] : (data.message || 'รูปแบบอีเมลไม่ถูกต้อง');
                    Swal.fire({
                        scrollbarPadding: false,
                        icon: 'warning',
                        title: 'ข้อมูลอีเมลไม่ถูกต้อง',
                        text: emailMsg,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#2563eb',
                        heightAuto: false,
                        customClass: {
                            popup: 'rounded-2xl shadow-xl border border-slate-100',
                            title: 'text-lg font-bold text-slate-800'
                        }
                    }).then(() => {
                        const m2 = document.getElementById('user-modal');
                        if (m2) m2.style.display = 'flex';
                        if (emailInput) setTimeout(() => emailInput.focus(), 100);
                    });
                    return;
                }
                const errorMsg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                Swal.fire({
                    scrollbarPadding: false,
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาดในการตรวจสอบข้อมูล',
                    text: errorMsg,
                    showConfirmButton: true,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#2563eb',
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        title: 'text-lg font-bold text-slate-800'
                    }
                }).then(() => {
                    const m = document.getElementById('user-modal');
                    if (m) m.style.display = 'flex';
                });
            }
        } catch (error) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
            const modal = document.getElementById('user-modal');
            if (modal) modal.style.display = 'flex';

            console.error(error);
            Swal.fire({
                scrollbarPadding: false,
                icon: 'error',
                title: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                showConfirmButton: true,
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#2563eb',
                heightAuto: false,
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    title: 'text-lg font-bold text-slate-800'
                }
            }).then(() => {
                const m = document.getElementById('user-modal');
                if (m) m.style.display = 'flex';
            });
        }
    }

    // 5. ฟังก์ชันระงับผู้ใช้งาน (Soft Delete) และ คืนค่า (Restore)
    window.openSuspendUserModal = function(id) {
        setupSuspendUserForm();
        const modal = document.getElementById('modal-suspend-user');
        const idInput = document.getElementById('suspend-modal-user-id');
        const reasonInput = document.getElementById('suspend-reason-input');
        const errorMsg = document.getElementById('suspend-error-message');
        if (modal) {
            if (idInput) idInput.value = id;
            if (reasonInput) reasonInput.value = '';
            if (errorMsg) errorMsg.classList.add('hidden');
            modal.classList.remove('hidden');
            if (reasonInput) {
                setTimeout(() => reasonInput.focus(), 60);
            }
        }
    };
    function openSuspendUserModal(id) { window.openSuspendUserModal(id); }

    window.closeSuspendUserModal = function() {
        const modal = document.getElementById('modal-suspend-user');
        if (modal) modal.classList.add('hidden');
        const errorMsg = document.getElementById('suspend-error-message');
        if (errorMsg) errorMsg.classList.add('hidden');
    };
    function closeSuspendUserModal() { window.closeSuspendUserModal(); }

    window.softDeleteUser = function(id) {
        window.openSuspendUserModal(id);
    };
    function softDeleteUser(id) { window.softDeleteUser(id); }

    window.restoreUser = function(id) {
        const modal = document.getElementById('modal-restore-user');
        const idInput = document.getElementById('restore-modal-user-id');
        if (modal && idInput) {
            idInput.value = id;
            modal.classList.remove('hidden');
        }
    };
    function restoreUser(id) { window.restoreUser(id); }

    window.closeRestoreUserModal = function() {
        const modal = document.getElementById('modal-restore-user');
        if (modal) modal.classList.add('hidden');
    };
    function closeRestoreUserModal() { window.closeRestoreUserModal(); }

    window.confirmRestoreUser = function() {
        const id = document.getElementById('restore-modal-user-id')?.value;
        window.closeRestoreUserModal();
        if (id) window.changeUserStatus(id, 'approved', null);
    };
    function confirmRestoreUser() { window.confirmRestoreUser(); }

    function setupSuspendUserForm() {
        const suspendForm = document.getElementById('suspend-user-form');
        if (suspendForm && !suspendForm.dataset.listenerAttached) {
            suspendForm.dataset.listenerAttached = 'true';
            suspendForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const id = document.getElementById('suspend-modal-user-id')?.value;
                const reasonInput = document.getElementById('suspend-reason-input');
                const reason = reasonInput ? reasonInput.value.trim() : '';
                const errorMsg = document.getElementById('suspend-error-message');

                if (!reason) {
                    if (errorMsg) errorMsg.classList.remove('hidden');
                    if (reasonInput) reasonInput.focus();
                    return;
                }

                window.closeSuspendUserModal();
                if (id) {
                    window.changeUserStatus(id, 'suspended', reason);
                }
            });
        }

        const reasonInput = document.getElementById('suspend-reason-input');
        if (reasonInput && !reasonInput.dataset.listenerAttached) {
            reasonInput.dataset.listenerAttached = 'true';
            reasonInput.addEventListener('input', function() {
                const errorMsg = document.getElementById('suspend-error-message');
                if (errorMsg && this.value.trim()) {
                    errorMsg.classList.add('hidden');
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupSuspendUserForm);
    } else {
        setupSuspendUserForm();
    }

    window.changeUserStatus = function(id, status, reason = null) {
        const baseUrl = window.BASE_URL || '';
        fetch(baseUrl + `/admin/users/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ status: status, suspend_reason: reason })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const userRow = document.querySelector(`tr.user-row[data-user-id="${id}"]`);
                if (userRow) {
                    const statusCell = userRow.querySelector('.status-cell');
                    const reasonCell = userRow.querySelector('.reason-cell');
                    const actionCell = userRow.querySelector('.action-cell');
                    
                    if (status === 'suspended') {
                        userRow.setAttribute('data-status', 'trash');
                        if (statusCell) {
                            const safeReason = (reason || 'ไม่ได้ระบุเหตุผล').replace(/"/g, '&quot;');
                            statusCell.innerHTML = `<span class="bg-red-50 text-red-700 border border-red-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold cursor-help" title="เหตุผล: ${safeReason}">
                                <i class="fa-solid fa-ban text-[8px] mr-1"></i>ถูกระงับ
                            </span>`;
                        }
                        if (reasonCell) reasonCell.innerText = reason || '-';
                        if (actionCell) {
                            actionCell.innerHTML = `<div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="editUser('${id}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer whitespace-nowrap shadow-sm shadow-amber-200" title="แก้ไขสิทธิ์ผู้ใช้งาน">
                                    <i class="fa-solid fa-pen"></i> แก้ไข
                                </button>
                                <button type="button" onclick="restoreUser('${id}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-green-200 whitespace-nowrap" title="ยกเลิกระงับบัญชี">
                                    <i class="fa-solid fa-rotate-left"></i> ยกเลิกระงับ
                                </button>
                            </div>`;
                        }
                    } else {
                        userRow.setAttribute('data-status', 'active');
                        if (statusCell) {
                            statusCell.innerHTML = `<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold">
                                <i class="fa-solid fa-circle text-[8px] mr-1"></i>ใช้งานปกติ
                            </span>`;
                        }
                        if (reasonCell) reasonCell.innerText = '-';
                        if (actionCell) {
                            actionCell.innerHTML = `<div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="editUser('${id}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer whitespace-nowrap shadow-sm shadow-amber-200" title="แก้ไขสิทธิ์ผู้ใช้งาน">
                                    <i class="fa-solid fa-pen"></i> แก้ไข
                                </button>
                                <button type="button" onclick="softDeleteUser('${id}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-red-200 whitespace-nowrap" title="ระงับการใช้งาน">
                                    <i class="fa-solid fa-ban"></i> ระงับ
                                </button>
                            </div>`;
                        }
                    }
                }

                const personnelRow = document.querySelector(`tr.personnel-row[data-user-id="${id}"]`);
                if (personnelRow) {
                    const actionCell = personnelRow.querySelector('.action-cell');
                    if (status === 'suspended') {
                        personnelRow.setAttribute('data-status', 'trash');
                        if (actionCell) {
                            actionCell.innerHTML = `<div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="viewPersonnel(this);" class="flex items-center gap-1 px-2.5 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-blue-200 whitespace-nowrap" title="ดูข้อมูล">
                                    <i class="fa-solid fa-eye"></i> ดูข้อมูล
                                </button>
                                <button type="button" onclick="editUser('${id}', 'personnel');" class="flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-amber-200 whitespace-nowrap" title="แก้ไขข้อมูลบุคลากร">
                                    <i class="fa-solid fa-pen"></i> แก้ไข
                                </button>
                                <button type="button" onclick="restoreUser('${id}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-green-200 whitespace-nowrap" title="ยกเลิกระงับบัญชี">
                                    <i class="fa-solid fa-rotate-left"></i> ยกเลิกระงับ
                                </button>
                            </div>`;
                        }
                    } else {
                        personnelRow.setAttribute('data-status', 'active');
                        if (actionCell) {
                            actionCell.innerHTML = `<div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="viewPersonnel(this);" class="flex items-center gap-1 px-2.5 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-blue-200 whitespace-nowrap" title="ดูข้อมูล">
                                    <i class="fa-solid fa-eye"></i> ดูข้อมูล
                                </button>
                                <button type="button" onclick="editUser('${id}', 'personnel');" class="flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-amber-200 whitespace-nowrap" title="แก้ไขข้อมูลบุคลากร">
                                    <i class="fa-solid fa-pen"></i> แก้ไข
                                </button>
                                <button type="button" onclick="softDeleteUser('${id}');" class="flex items-center gap-1 px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[11px] rounded-lg transition font-bold cursor-pointer shadow-sm shadow-red-200 whitespace-nowrap" title="ระงับการใช้งาน">
                                    <i class="fa-solid fa-ban"></i> ระงับ
                                </button>
                            </div>`;
                        }
                    }
                }

                Swal.fire({
                    scrollbarPadding: false,
                    icon: 'success',
                    title: 'เปลี่ยนสถานะสำเร็จ',
                    showConfirmButton: false,
                    timer: 1500,
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        title: 'text-lg font-bold text-slate-800'
                    }
                }).then(() => {
                    window.location.reload();
                });

                if (typeof renderUsersList === 'function') renderUsersList();
                if (typeof window.renderPersonnelList === 'function') window.renderPersonnelList();
            } else {
                Swal.fire({
                    scrollbarPadding: false,
                    icon: 'error',
                    title: data.message || 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะ',
                    showConfirmButton: false,
                    timer: 2000,
                    heightAuto: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        title: 'text-lg font-bold text-slate-800'
                    }
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                scrollbarPadding: false,
                icon: 'error',
                title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                showConfirmButton: false,
                timer: 2000,
                heightAuto: false,
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    title: 'text-lg font-bold text-slate-800'
                }
            });
        });
    }

    // 7. ฟังก์ชันค้นหาในตาราง
    window.userCurrentViewMode = 'active';
    window.switchUserViewMode = function (mode) {
        window.userCurrentViewMode = mode;
        const activeTab = document.getElementById('user-tab-view-active');
        const suspendedTab = document.getElementById('user-tab-view-suspended');

        if (activeTab && suspendedTab) {
            if (mode === 'active') {
                activeTab.className = 'px-5 py-3 text-sm font-extrabold border-b-2 border-blue-600 text-blue-600 transition cursor-pointer flex items-center gap-2 whitespace-nowrap';
                suspendedTab.className = 'px-5 py-3 text-sm font-extrabold border-b-2 border-transparent text-slate-400 hover:text-red-500 transition cursor-pointer flex items-center gap-2 whitespace-nowrap';
            } else {
                suspendedTab.className = 'px-5 py-3 text-sm font-extrabold border-b-2 border-red-500 text-red-500 transition cursor-pointer flex items-center gap-2 whitespace-nowrap';
                activeTab.className = 'px-5 py-3 text-sm font-extrabold border-b-2 border-transparent text-slate-400 hover:text-blue-600 transition cursor-pointer flex items-center gap-2 whitespace-nowrap';
            }
        }
        renderUsersList();
    };

    function renderUsersList() {
        const searchText = document.getElementById('user-search')?.value.toLowerCase() || '';
        const rows = document.querySelectorAll('#users-table-body tr.user-row');
        let counter = 1;

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const matchesSearch = rowText.includes(searchText);
            const status = row.getAttribute('data-status');
            const matchesTab = (window.userCurrentViewMode === 'suspended') ? (status === 'trash') : (status !== 'trash');

            if (matchesSearch && matchesTab) {
                row.style.display = '';
                const indexCell = row.querySelector('.user-index');
                if (indexCell) {
                    indexCell.innerText = counter++;
                }
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Call render once on load to initialize badges
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('users-table-body')) {
            renderUsersList();
        }
    });

    // 8. ฟังก์ชัน Beep เสียงกดปุ่ม (Fallback กัน Error)
    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            osc.connect(ctx.destination);
            osc.frequency.value = 800;
            osc.start();
            osc.stop(ctx.currentTime + 0.05);
        } catch (e) {}
    }

    // 9. กรองแผนก/งานตามฝ่ายที่เลือก
    document.getElementById('modal-department-group')?.addEventListener('change', function() {
        const selectedGroup = this.value;
        const departmentSelect = document.getElementById('modal-department');
        const options = departmentSelect.querySelectorAll('option:not([value=""])');
        
        departmentSelect.value = ''; // Reset selection
        departmentSelect.disabled = false; // Always enabled
        
        options.forEach(opt => {
            const optGroup = opt.getAttribute('data-group');
            if (!selectedGroup || optGroup === selectedGroup) {
                opt.style.display = '';
                opt.hidden = false;
                opt.disabled = false;
            } else {
                opt.style.display = 'none';
                opt.hidden = true;
                opt.disabled = true;
            }
        });
    });

    // 10. Multi-select Dropdown Logic for System Role
    const roleSelectButton = document.getElementById('role-select-button');
    const roleSelectDropdown = document.getElementById('role-select-dropdown');
    const roleCheckboxes = document.querySelectorAll('.role-checkbox');
    const roleSelectText = document.getElementById('role-select-text');
    const modalSystemRole = document.getElementById('modal-system-role');

    if (roleSelectButton) {
        roleSelectButton.addEventListener('click', () => {
            roleSelectDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!document.getElementById('role-select-container').contains(e.target)) {
                roleSelectDropdown.classList.add('hidden');
            }
        });

        // Handle checkbox changes
        roleCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                let selectedValues = [];
                let selectedTexts = [];
                roleCheckboxes.forEach(box => {
                    if (box.checked) {
                        selectedValues.push(box.value);
                        selectedTexts.push(box.nextElementSibling.innerText);
                    }
                });

                modalSystemRole.value = selectedValues.join(',');
                if (selectedTexts.length > 0) {
                    roleSelectText.innerText = selectedTexts.join(', ');
                    roleSelectButton.classList.replace('text-slate-300', 'text-slate-700');
                    roleSelectButton.classList.remove('font-medium');
                } else {
                    roleSelectText.innerText = 'เลือกสิทธิ์การใช้งาน';
                    roleSelectButton.classList.replace('text-slate-700', 'text-slate-300');
                    roleSelectButton.classList.add('font-medium');
                }
            });
        });
    }
    window.previewUserModalImage = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('modal-profile-preview');
                const placeholder = document.getElementById('modal-profile-placeholder-text');
                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    };
</script>   

@php
    $resList = ["งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"];
    $planList = ["งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา"];
    $studList = ["งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"];
    $acadList = ["งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า"];

    $allDeptsLookup = \Illuminate\Support\Facades\Schema::hasTable('departments') 
        ? \App\Models\Department::all() 
        : collect([]);
    $deptsByName = $allDeptsLookup->keyBy('department_name');
    $deptsById = $allDeptsLookup->keyBy('department_id');

    $groupCodeMap = [
        'resource' => 'ฝ่ายบริหารทรัพยากร',
        'planning' => 'ฝ่ายนโยบายและแผน',
        'student' => 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า',
        'academic' => 'ฝ่ายวิชาการ',
    ];

    if (isset($dbDepartmentGroups)) {
        foreach ($dbDepartmentGroups as $dg) {
            $groupCodeMap[$dg->id] = $dg->group_name;
            if (!empty($dg->group_code)) {
                $groupCodeMap[$dg->group_code] = $dg->group_name;
            }
        }
    }

    $resolveUserGroup = function($u) use ($resList, $planList, $studList, $acadList, $deptsByName, $deptsById, $groupCodeMap) {
        $d = trim((string)($u->department ?? ''));
        if (in_array($d, $resList)) return 'ฝ่ายบริหารทรัพยากร';
        if (in_array($d, $planList)) return 'ฝ่ายนโยบายและแผน';
        if (in_array($d, $studList)) return 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า';
        if (in_array($d, $acadList)) return 'ฝ่ายวิชาการ';

        if (in_array($d, ['ฝ่ายบริหารทรัพยากร', 'ฝ่ายนโยบายและแผน', 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า', 'ฝ่ายวิชาการ'])) {
            return $d;
        }

        $dObj = $deptsByName->get($d) ?? $deptsById->get($u->department_id ?? null);
        if ($dObj && !empty($dObj->group)) {
            $grp = trim((string)$dObj->group);
            if (isset($groupCodeMap[$grp])) return $groupCodeMap[$grp];
            return $grp;
        }
        return '';
    };
@endphp

<!-- Modal เพิ่มประเภทบุคลากรใหม่ -->
<div id="modal-add-position"
    class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div
        class="bg-white max-w-md w-full rounded-3xl border border-slate-100 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="bg-blue-600 px-6 py-4 flex items-center justify-between text-white">
            <h4 class="font-bold text-base flex items-center gap-2">
                👥 เพิ่มประเภทบุคลากรใหม่
            </h4>
            <button type="button" onclick="closeNewPositionModal()" class="text-white/80 hover:text-white transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
                  <!-- Body -->
          <div class="p-6 space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                      <label class="block text-slate-700 mb-1.5 font-bold text-xs">รหัสโครงการ <span class="text-red-500">*</span></label>
                      <input type="text" id="broken-repair-project-code" placeholder="เช่น PM-2569-0001"
                          class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block p-3 outline-none transition">
                  </div>
                  <div>
                      <label class="block text-slate-700 mb-1.5 font-bold text-xs">ชื่อโครงการ <span class="text-red-500">*</span></label>
                      <input type="text" id="broken-repair-project-name" placeholder="กรอกชื่อโครงการ"
                          class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block p-3 outline-none transition">
                  </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                      <label class="block text-slate-700 mb-1.5 font-bold text-xs">ฝ่าย (หน่วยงานรับผิดชอบ) <span class="text-red-500">*</span></label>
                      <select id="broken-repair-project-department" onchange="updateBrokenProjectOwnersByDepartment()" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block p-3 outline-none transition cursor-pointer">
                          <option value="">-- เลือกฝ่าย --</option>
                          @foreach($dbDepartmentGroups as $group)
                          <option value="{{ $group->group_name }}">{{ $group->group_name }}</option>
                          @endforeach
                      </select>
                  </div>
                  <div>
                      <label class="block text-slate-700 mb-1.5 font-bold text-xs">ผู้รับผิดชอบ <span class="text-red-500">*</span></label>
                      <select id="broken-repair-project-owner" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block p-3 outline-none transition cursor-pointer">
                          <option value="">-- โปรดเลือกฝ่ายก่อน --</option>
                          @if(isset($users) && count($users) > 0)
                              @php
                                  $allPersonnel = $users->filter(function($u) { return $u->status !== 'rejected'; })->sortBy(function($u) {
                                      return trim(($u->prefix ? $u->prefix . ' ' : '') . $u->first_name . ' ' . ($u->last_name ?? '')) ?: $u->name;
                                  });
                              @endphp
                              @foreach($allPersonnel as $user)
                                  @php
                                      $displayName = trim(($user->prefix ? $user->prefix . ' ' : '') . $user->first_name . ' ' . ($user->last_name ?? ''));
                                      if (empty($displayName)) $displayName = $user->name ?: ($user->username ?? $user->user_id);
                                      $userDeptGroup = $resolveUserGroup($user);
                                  @endphp
                                  <option value="{{ $displayName }}" data-department="{{ $userDeptGroup }}">{{ $displayName }}</option>
                              @endforeach
                          @endif
                      </select>
                  </div>
              </div>

              <div>
                  <label class="block text-slate-700 mb-1.5 font-bold text-xs">งบประมาณ (บาท) <span class="text-red-500">*</span></label>
                  <input type="number" id="broken-repair-project-budget" placeholder="0.00" step="0.01" min="0"
                      class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 block p-3 outline-none transition">
              </div>
          </div>
          <!-- Footer -->
            <div class="flex items-center justify-between pt-2">
                <p id="bd-rooms-footer" class="text-xs text-slate-500 font-medium">แสดง 1 - 4 จาก 4 รายการ</p>
                <div class="flex items-center gap-1">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 transition cursor-not-allowed">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-800 text-white font-bold transition">
                        1
                    </button>
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 transition cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ป๊อปอัปเพิ่มห้อง (Add Room Modal) -->
<div id="modal-add-room" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl shadow-2xl p-6 md:p-8 space-y-6 text-sm font-semibold relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Close button -->
        <button type="button" onclick="closeAddRoomModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition cursor-pointer">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <!-- Header -->
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-plus"></i>
            </div>
            <h4 class="text-xl font-black text-slate-900">
                เพิ่มห้อง
            </h4>
        </div>

        <form id="new-room-form" onsubmit="saveNewRoom(event)" class="space-y-6">
            <div>
                <label for="room-form-code" class="block text-slate-700 font-bold mb-1.5 text-base">รหัสห้อง <span class="text-red-500">*</span></label>
                <input type="text" id="room-form-code" required placeholder="กรอกรหัสห้อง"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 text-base">
            </div>
            <div>
                <label for="room-form-name" class="block text-slate-700 font-bold mb-1.5 text-base">ชื่อห้อง <span class="text-red-500">*</span></label>
                <input type="text" id="room-form-name" required placeholder="กรอกชื่อห้อง"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 text-base">
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="closeAddRoomModal()"
                    class="px-6 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-2xl font-bold transition cursor-pointer shadow-sm text-base">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold transition cursor-pointer shadow-md shadow-blue-200 text-base">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ป๊อปอัปแก้ไขห้อง (Edit Room Modal) -->
<div id="modal-edit-room" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-3xl shadow-2xl p-6 md:p-8 space-y-6 text-sm font-semibold relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Close button -->
        <button type="button" onclick="closeEditRoomModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition cursor-pointer">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <!-- Header -->
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-pen"></i>
            </div>
            <h4 class="text-xl font-black text-slate-900">
                แก้ไขห้อง
            </h4>
        </div>

        <form id="edit-room-form" onsubmit="updateRoom(event)" class="space-y-6">
            <input type="hidden" id="edit-room-id">
            <div>
                <label for="edit-room-form-code" class="block text-slate-700 font-bold mb-1.5 text-base">รหัสห้อง <span class="text-red-500">*</span></label>
                <input type="text" id="edit-room-form-code" required placeholder="กรอกรหัสห้อง"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm text-base cursor-not-allowed" readonly>
            </div>
            <div>
                <label for="edit-room-form-name" class="block text-slate-700 font-bold mb-1.5 text-base">ชื่อห้อง <span class="text-red-500">*</span></label>
                <input type="text" id="edit-room-form-name" required placeholder="กรอกชื่อห้อง"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 text-base">
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="closeEditRoomModal()"
                    class="px-6 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-2xl font-bold transition cursor-pointer shadow-sm text-base">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold transition cursor-pointer shadow-md shadow-blue-200 text-base">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ป๊อปอัปยืนยันการทำรายการต่างๆ (System Custom Modal) -->
<div id="admin-custom-modal"
    class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div
        class="bg-white rounded-3xl w-full max-w-md overflow-hidden transition-all duration-300 transform scale-100 border border-blue-500/20 shadow-2xl">
        <div class="bg-gradient-to-r from-blue-900 to-blue-600 text-white p-6 border-b-2 border-slate-100">
            <h3 class="text-base font-black mt-1" id="custom-modal-title">ยืนยันการตั้งค่าระบบ</h3>
        </div>

        <div class="p-6 space-y-4">
            <p id="custom-modal-text" class="text-xs text-slate-600 leading-relaxed font-bold">
                ท่านแน่ใจหรือไม่ที่จะทำการบันทึกและอัปเดตสิทธิ์ผู้เข้าใช้ระบบดังกล่าว?</p>
        </div>

        <div class="bg-slate-50 p-4 border-t border-slate-100 flex justify-end space-x-2 shrink-0 text-xs">
            <button onclick="closeAdminCustomModal(); playBeep();"
                class="px-4 py-2.5 bg-slate-200 text-slate-600 rounded-xl font-bold border border-slate-300 hover:bg-slate-300 transition">ยกเลิก</button>
            <button onclick="executeModalConfirmAction();"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl shadow-md transition"
                id="custom-modal-confirm-btn">
                บันทึก
            </button>
        </div>
    </div>
</div>

<!-- แถบแจ้งเตือนความสำเร็จ (Root Toast Notification System) -->
<div id="admin-toast"
    class="hidden fixed bottom-6 right-6 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl items-center space-x-3 transition-all duration-300 transform translate-y-0 z-50 border-l-4 border-blue-500">
    <span class="text-blue-500 text-xl" id="toast-icon-box"><i class="fa-solid fa-circle-check"></i></span>
    <div>
        <h4 class="font-extrabold text-xs text-white" id="toast-title-box">ดำเนินการสำเร็จ</h4>
        <p id="toast-text-box" class="text-[10px] text-slate-400">ระบบทำงานเรียบร้อย</p>
    </div>
</div>

<!-- Hidden Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- ป๊อปอัปยืนยันการออกจากระบบ (Logout Modal) -->
<div id="logout-modal"
    class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div
        class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl border border-slate-200 transition-all duration-300 transform scale-100 p-8 text-center relative">
        <div class="w-20 h-20 bg-blue-50 rounded-3xl mx-auto flex items-center justify-center mb-6">
            <i class="fa-solid fa-right-from-bracket text-4xl text-blue-600"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-900 mb-2">ออกจากระบบ?</h3>
        <p class="text-xs text-slate-500 font-bold mb-8">คุณต้องการออกจากระบบหรือไม่?</p>
        <div class="flex space-x-3">
            <button onclick="closeLogoutModal();"
                class="flex-1 py-3.5 bg-slate-50 text-slate-700 rounded-xl font-black hover:bg-slate-100 transition border border-slate-200 text-sm">
                ยกเลิก
            </button>
            <button onclick="confirmLogout();"
                class="flex-1 py-3.5 bg-blue-600 text-white rounded-xl font-black hover:bg-blue-700 shadow-md shadow-blue-200 transition flex items-center justify-center space-x-2 text-sm">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>ออกจากระบบ</span>
            </button>
        </div>
    </div>
</div>

<!-- ป๊อปอัปเพิ่มข้อมูล (Add Model Data Modal) -->
<div id="modal-add-asset-item"
    class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white max-w-3xl w-full rounded-3xl border border-slate-100 shadow-2xl p-6 md:p-8 space-y-5 text-sm font-semibold relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Close button -->
        <button type="button" onclick="closeAssetItemModal()"
            class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <!-- Header -->
        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <h4 class="text-xl font-black text-slate-900">
                เพิ่มข้อมูลทรัพย์สิน
            </h4>
        </div>

        <!-- Quick Category Tabs -->
        <div class="flex items-center space-x-2 pt-2 pb-2 overflow-x-auto scrollbar-hide">
            <button type="button" onclick="selectAssetCategoryTab('ฮาร์ดแวร์', this)" class="asset-cat-tab shrink-0 px-4 py-2 text-xs sm:text-sm font-bold rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                <i class="fa-solid fa-computer mr-1"></i> ฮาร์ดแวร์
            </button>
            <button type="button" onclick="selectAssetCategoryTab('ซอฟแวร์', this)" class="asset-cat-tab shrink-0 px-4 py-2 text-xs sm:text-sm font-bold rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                <i class="fa-solid fa-compact-disc mr-1"></i> ซอฟต์แวร์
            </button>
            <button type="button" onclick="selectAssetCategoryTab('ระบบเครือข่าย', this)" class="asset-cat-tab shrink-0 px-4 py-2 text-xs sm:text-sm font-bold rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                <i class="fa-solid fa-network-wired mr-1"></i> ระบบเครือข่าย
            </button>
        </div>

        <form id="form-hardware" novalidate
            onsubmit="if(typeof createHardwareItem === 'function') createHardwareItem(event); else closeAssetItemModal(); return false;"
            class="space-y-5">
            
            <div class="space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar pr-2 pb-2">
                
                <!-- หมวดที่ 1 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-blue-600 mb-3 flex items-center gap-2"><i class="fa-solid fa-boxes-stacked"></i> หมวดที่ 1 : ข้อมูลทรัพย์สิน</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <!-- รูปภาพอุปกรณ์ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">รูปภาพอุปกรณ์ (ไม่บังคับ)</label>
                            <div class="flex flex-col sm:flex-row items-center gap-4 p-3.5 sm:p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                                <div class="relative w-28 h-28 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 hover:border-blue-400 flex flex-col items-center justify-center overflow-hidden shrink-0 transition group cursor-pointer" onclick="document.getElementById('hw-form-image').click()">
                                    <img id="hw-form-image-preview" src="" alt="พรีวิวรูปอุปกรณ์" class="hidden w-full h-full object-contain p-1">
                                    <div id="hw-form-image-placeholder" class="flex flex-col items-center justify-center p-2 text-center text-slate-400 group-hover:text-blue-500 transition">
                                        <i class="fa-solid fa-camera text-2xl mb-1 text-slate-300 group-hover:text-blue-500 transition"></i>
                                        <span class="text-[11px] font-bold">เพิ่มรูปภาพ</span>
                                    </div>
                                    <div id="hw-form-image-overlay" class="hidden absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <i class="fa-solid fa-pen text-xs"></i> เปลี่ยนรูป
                                    </div>
                                </div>
                                <div class="flex-1 text-center sm:text-left flex flex-col justify-center">
                                    <p class="text-xs text-slate-700 font-bold mb-0.5">อัปโหลดรูปภาพอุปกรณ์ หรือครุภัณฑ์</p>
                                    <p class="text-[11px] text-slate-400 font-semibold mb-2.5">รองรับไฟล์ JPG, JPEG, PNG, WEBP (ขนาดไม่เกิน 5 MB)</p>
                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                        <button type="button" onclick="document.getElementById('hw-form-image').click()" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <i class="fa-solid fa-arrow-up-from-bracket text-xs"></i> เลือกรูปภาพ
                                        </button>
                                        <button type="button" id="hw-form-image-remove-btn" onclick="removeAssetImage('hw')" class="hidden px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <i class="fa-solid fa-trash-can text-xs"></i> ลบรูป
                                        </button>
                                    </div>
                                    <input type="file" id="hw-form-image" accept="image/jpeg,image/png,image/webp,image/jpg" onchange="previewAssetImage('hw', this)" class="hidden">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ประเภททรัพย์สิน <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="hw-form-category" onchange="handleAiFormCategoryChange(); document.getElementById('hw-form-category-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                        <option value="" disabled selected>-- เลือกประเภททรัพย์สิน --</option>
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
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="hw-form-category-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกประเภททรัพย์สิน</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ชนิดทรัพย์สิน <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="hw-form-kind" disabled onchange="handleAiFormKindChange(); document.getElementById('hw-form-kind-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- กรุณาเลือกประเภททรัพย์สินก่อน --</option>
                                        @if(isset($dbDeviceTypes) && count($dbDeviceTypes) > 0)
                                            @foreach($dbDeviceTypes as $type)
                                                <option value="{{ $type->id ?? $type }}">{{ $type->name ?? $type }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="hw-form-kind-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกชนิดทรัพย์สิน</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">ยี่ห้อ <span class="text-red-500">*</span></label>
                                <input type="text" id="hw-form-brand" placeholder="เช่น HP, Dell, Apple, Lenovo" oninput="document.getElementById('hw-form-brand-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="hw-form-brand-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกยี่ห้อ</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">รุ่น <span class="text-red-500">*</span></label>
                                <input type="text" id="hw-form-model" placeholder="เช่น ProBook 440, OptiPlex 3090" oninput="document.getElementById('hw-form-model-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="hw-form-model-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกรุ่น</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <div class="flex items-center justify-between mb-1.5 gap-2">
                                    <label class="block text-slate-700 font-bold text-xs sm:text-sm whitespace-nowrap">รหัสครุภัณฑ์ <span id="hw-code-required-star" class="text-red-500">*</span></label>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium cursor-pointer select-none hover:text-blue-600 transition-colors whitespace-nowrap shrink-0">
                                        <input type="checkbox" id="hw-form-no-code" onchange="toggleNoCode('hw')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <span>ไม่มีรหัส (สร้างอัตโนมัติ)</span>
                                    </label>
                                </div>
                                <input type="text" id="hw-form-code" placeholder="เช่น INV-001" oninput="document.getElementById('hw-form-code-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 transition-colors">
                                <p id="hw-form-code-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกรหัสครุภัณฑ์</p>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1.5 gap-2">
                                    <label class="block text-slate-700 font-bold text-xs sm:text-sm whitespace-nowrap">หมายเลขเครื่อง (Serial) <span id="hw-serial-required-star" class="text-red-500">*</span></label>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium cursor-pointer select-none hover:text-blue-600 transition-colors whitespace-nowrap shrink-0">
                                        <input type="checkbox" id="hw-form-no-serial" onchange="toggleNoSerial('hw')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <span>ไม่มีเลขเครื่อง (N/A)</span>
                                    </label>
                                </div>
                                <input type="text" id="hw-form-serial" placeholder="เช่น SN-123456" oninput="document.getElementById('hw-form-serial-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 transition-colors">
                                <p id="hw-form-serial-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกหมายเลขเครื่อง</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold">สเปก / คุณลักษณะเฉพาะ <span class="text-red-500">*</span></label>
                            <textarea id="hw-form-specs" rows="3" placeholder="กรอกรายละเอียดสเปก หรือคุณลักษณะเฉพาะของทรัพย์สิน..." oninput="document.getElementById('hw-form-specs-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 resize-none"></textarea>
                            <p id="hw-form-specs-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกสเปก / คุณลักษณะเฉพาะ</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ผู้จัดจำหน่าย <span class="text-red-500">*</span></label>
                                <input type="text" id="hw-form-supplier" placeholder="ชื่อบริษัท หรือ ร้านค้า" oninput="document.getElementById('hw-form-supplier-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="hw-form-supplier-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกผู้จัดจำหน่าย</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">วันที่จัดซื้อ <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-calendar text-slate-400"></i>
                                    </div>
                                    <input type="text" id="hw-form-purchase-date" placeholder="เลือกวันที่..." onchange="document.getElementById('hw-form-purchase-date-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                </div>
                                <p id="hw-form-purchase-date-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาระบุวันที่จัดซื้อ</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ราคาทรัพย์สิน (บาท) <span class="text-red-500">*</span></label>
                                <input type="text" id="hw-form-price" placeholder="0.00" oninput="document.getElementById('hw-form-price-error').classList.add('hidden'); this.classList.remove('border-red-500'); formatCurrency(this);"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="hw-form-price-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกราคาทรัพย์สิน</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ระยะเวลารับประกัน <span class="text-red-500">*</span></label>
                                <input type="text" id="hw-form-warranty" placeholder="เช่น 1 ปี, 6 เดือน" oninput="document.getElementById('hw-form-warranty-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="hw-form-warranty-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกระยะเวลารับประกัน</p>
                            </div>
                        </div>
                    </div>
                </div>

                

                  <!-- หมวดที่ 2 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-blue-600 mb-3 flex items-center gap-2"><i class="fa-solid fa-building"></i> หมวดที่ 2 : ข้อมูลอาคาร</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">อาคาร <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="hw-form-building" onchange="handleAiFormBuildingChange(); document.getElementById('hw-form-building-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                        <option value="" disabled selected>-- เลือกอาคาร --</option>
                                        @if(isset($dbBuildings) && count($dbBuildings) > 0)
                                            @foreach($dbBuildings as $building)
                                                <option value="{{ $building->building_id }}">{{ $building->building_name }}</option>
                                            @endforeach
                                        @else
                                            <option value="B01">อาคารเทคโนโลยีสารสนเทศ</option>
                                            <option value="B02">อาคารช่างยนต์</option>
                                            <option value="B03">อาคารอำนวยการ</option>
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="hw-form-building-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกอาคาร</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ชั้น <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="hw-form-floor" onchange="handleAiFormFloorChange(); document.getElementById('hw-form-floor-error').classList.add('hidden'); this.classList.remove('border-red-500');" disabled
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- เลือกชั้น --</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="hw-form-floor-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกชั้น</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ห้อง <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="hw-form-room" onchange="document.getElementById('hw-form-room-error').classList.add('hidden'); this.classList.remove('border-red-500');" disabled
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- เลือกห้อง --</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="hw-form-room-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกห้อง</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-slate-700 mb-1.5 font-bold">บุคลากรที่รับผิดชอบ <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="hw-form-responsible" onchange="document.getElementById('hw-form-responsible-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                    <option value="" disabled selected>-- เลือกบุคลากร --</option>
                                    @if(isset($users) && count($users) > 0)
                                        @foreach($users as $user)
                                            <option value="{{ trim(($user->prefix ? $user->prefix . ' ' : '') . $user->first_name . ' ' . $user->last_name) }}">{{ $user->prefix }} {{ $user->first_name }} {{ $user->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <p id="hw-form-responsible-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกบุคลากรที่รับผิดชอบ</p>
                        </div>
                    </div>
                </div>

                <!-- หมวดที่ 3 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-amber-500 mb-3 flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> หมวดที่ 3 : สถานะ</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold">สถานะ <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="hw-form-status" onchange="document.getElementById('hw-form-status-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                    <option value="พร้อมใช้งาน" selected>พร้อมใช้งาน</option>
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <p id="hw-form-status-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกสถานะ</p>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4">
                <button type="button" onclick="closeAssetItemModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-2xl font-bold transition cursor-pointer shadow-sm">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
<form id="form-software" novalidate
            onsubmit="if(typeof createSoftwareItem === 'function') createSoftwareItem(event); else closeAssetItemModal(); return false;"
            class="space-y-5">
            
            <div class="space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar pr-2 pb-2">
                
                <!-- หมวดที่ 1 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-blue-600 mb-3 flex items-center gap-2"><i class="fa-solid fa-boxes-stacked"></i> หมวดที่ 1 : ข้อมูลทรัพย์สิน</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <!-- รูปภาพอุปกรณ์ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">รูปภาพอุปกรณ์ (ไม่บังคับ)</label>
                            <div class="flex flex-col sm:flex-row items-center gap-4 p-3.5 sm:p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                                <div class="relative w-28 h-28 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 hover:border-blue-400 flex flex-col items-center justify-center overflow-hidden shrink-0 transition group cursor-pointer" onclick="document.getElementById('sw-form-image').click()">
                                    <img id="sw-form-image-preview" src="" alt="พรีวิวรูปอุปกรณ์" class="hidden w-full h-full object-contain p-1">
                                    <div id="sw-form-image-placeholder" class="flex flex-col items-center justify-center p-2 text-center text-slate-400 group-hover:text-blue-500 transition">
                                        <i class="fa-solid fa-camera text-2xl mb-1 text-slate-300 group-hover:text-blue-500 transition"></i>
                                        <span class="text-[11px] font-bold">เพิ่มรูปภาพ</span>
                                    </div>
                                    <div id="sw-form-image-overlay" class="hidden absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <i class="fa-solid fa-pen text-xs"></i> เปลี่ยนรูป
                                    </div>
                                </div>
                                <div class="flex-1 text-center sm:text-left flex flex-col justify-center">
                                    <p class="text-xs text-slate-700 font-bold mb-0.5">อัปโหลดรูปภาพอุปกรณ์ หรือครุภัณฑ์</p>
                                    <p class="text-[11px] text-slate-400 font-semibold mb-2.5">รองรับไฟล์ JPG, JPEG, PNG, WEBP (ขนาดไม่เกิน 5 MB)</p>
                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                        <button type="button" onclick="document.getElementById('sw-form-image').click()" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <i class="fa-solid fa-arrow-up-from-bracket text-xs"></i> เลือกรูปภาพ
                                        </button>
                                        <button type="button" id="sw-form-image-remove-btn" onclick="removeAssetImage('sw')" class="hidden px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <i class="fa-solid fa-trash-can text-xs"></i> ลบรูป
                                        </button>
                                    </div>
                                    <input type="file" id="sw-form-image" accept="image/jpeg,image/png,image/webp,image/jpg" onchange="previewAssetImage('sw', this)" class="hidden">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ประเภททรัพย์สิน <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="sw-form-category" onchange="handleAiFormCategoryChange(); document.getElementById('sw-form-category-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                        <option value="" disabled selected>-- เลือกประเภททรัพย์สิน --</option>
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
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="sw-form-category-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกประเภททรัพย์สิน</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ชนิดทรัพย์สิน <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="sw-form-kind" disabled onchange="handleAiFormKindChange(); document.getElementById('sw-form-kind-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- กรุณาเลือกประเภททรัพย์สินก่อน --</option>
                                        @if(isset($dbDeviceTypes) && count($dbDeviceTypes) > 0)
                                            @foreach($dbDeviceTypes as $type)
                                                <option value="{{ $type->id ?? $type }}">{{ $type->name ?? $type }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="sw-form-kind-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกชนิดทรัพย์สิน</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5 gap-2">
                                <label class="block text-slate-700 font-bold text-xs sm:text-sm whitespace-nowrap">รหัสครุภัณฑ์ <span id="sw-code-required-star" class="text-red-500">*</span></label>
                                <label class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium cursor-pointer select-none hover:text-blue-600 transition-colors whitespace-nowrap shrink-0">
                                    <input type="checkbox" id="sw-form-no-code" onchange="toggleNoCode('sw')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    <span>ไม่มีรหัส (สร้างอัตโนมัติ)</span>
                                </label>
                            </div>
                            <input type="text" id="sw-form-code" placeholder="เช่น SW-001 หรือ AST-001" oninput="document.getElementById('sw-form-code-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 transition-colors">
                            <p id="sw-form-code-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกรหัสครุภัณฑ์</p>
                        </div>
                        <input type="hidden" id="sw-form-serial" value="N/A">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">ผู้พัฒนา / บริษัทผู้ผลิต <span class="text-red-500">*</span></label>
                                <input type="text" id="sw-form-brand" placeholder="เช่น Microsoft, Adobe, Google" oninput="document.getElementById('sw-form-brand-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="sw-form-brand-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกผู้พัฒนา / บริษัทผู้ผลิต</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">ชื่อโปรแกรม / รุ่นเวอร์ชัน <span class="text-red-500">*</span></label>
                                <input type="text" id="sw-form-model" placeholder="เช่น Windows 11, Office 2021" oninput="document.getElementById('sw-form-model-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="sw-form-model-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกชื่อโปรแกรม / รุ่นเวอร์ชัน</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold">ประเภทลิขสิทธิ์ / รายละเอียดการติดตั้ง <span class="text-red-500">*</span></label>
                            <textarea id="sw-form-specs" rows="3" placeholder="เช่น Volume License สำหรับ 50 เครื่อง, บัญชีองค์กร (Campus), ลิขสิทธิ์ตลอดชีพ หรือรายปี..." oninput="document.getElementById('sw-form-specs-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 resize-none"></textarea>
                            <p id="sw-form-specs-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกรายละเอียดลิขสิทธิ์</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ผู้จัดจำหน่าย <span class="text-red-500">*</span></label>
                                <input type="text" id="sw-form-supplier" placeholder="ชื่อบริษัท หรือ ร้านค้า" oninput="document.getElementById('sw-form-supplier-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="sw-form-supplier-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกผู้จัดจำหน่าย</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">วันที่จัดซื้อ <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-calendar text-slate-400"></i>
                                    </div>
                                    <input type="text" id="sw-form-purchase-date" placeholder="เลือกวันที่..." onchange="document.getElementById('sw-form-purchase-date-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                </div>
                                <p id="sw-form-purchase-date-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาระบุวันที่จัดซื้อ</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ราคาทรัพย์สิน (บาท) <span class="text-red-500">*</span></label>
                                <input type="text" id="sw-form-price" placeholder="0.00" oninput="document.getElementById('sw-form-price-error').classList.add('hidden'); this.classList.remove('border-red-500'); formatCurrency(this);"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="sw-form-price-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกราคาทรัพย์สิน</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ระยะเวลารับประกัน <span class="text-red-500">*</span></label>
                                <input type="text" id="sw-form-warranty" placeholder="เช่น 1 ปี, 6 เดือน" oninput="document.getElementById('sw-form-warranty-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="sw-form-warranty-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกระยะเวลารับประกัน</p>
                            </div>
                        </div>
                    </div>
                </div>

                

                  <!-- หมวดที่ 2 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-blue-600 mb-3 flex items-center gap-2"><i class="fa-solid fa-building"></i> หมวดที่ 2 : ข้อมูลอาคาร</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">อาคาร <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="sw-form-building" onchange="handleAiFormBuildingChange(); document.getElementById('sw-form-building-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                        <option value="" disabled selected>-- เลือกอาคาร --</option>
                                        @if(isset($dbBuildings) && count($dbBuildings) > 0)
                                            @foreach($dbBuildings as $building)
                                                <option value="{{ $building->building_id }}">{{ $building->building_name }}</option>
                                            @endforeach
                                        @else
                                            <option value="B01">อาคารเทคโนโลยีสารสนเทศ</option>
                                            <option value="B02">อาคารช่างยนต์</option>
                                            <option value="B03">อาคารอำนวยการ</option>
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="sw-form-building-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกอาคาร</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ชั้น <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="sw-form-floor" onchange="handleAiFormFloorChange(); document.getElementById('sw-form-floor-error').classList.add('hidden'); this.classList.remove('border-red-500');" disabled
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- เลือกชั้น --</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="sw-form-floor-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกชั้น</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ห้อง <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="sw-form-room" onchange="document.getElementById('sw-form-room-error').classList.add('hidden'); this.classList.remove('border-red-500');" disabled
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- เลือกห้อง --</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="sw-form-room-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกห้อง</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-slate-700 mb-1.5 font-bold">บุคลากรที่รับผิดชอบ <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="sw-form-responsible" onchange="document.getElementById('sw-form-responsible-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                    <option value="" disabled selected>-- เลือกบุคลากร --</option>
                                    @if(isset($users) && count($users) > 0)
                                        @foreach($users as $user)
                                            <option value="{{ trim(($user->prefix ? $user->prefix . ' ' : '') . $user->first_name . ' ' . $user->last_name) }}">{{ $user->prefix }} {{ $user->first_name }} {{ $user->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <p id="sw-form-responsible-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกบุคลากรที่รับผิดชอบ</p>
                        </div>
                    </div>
                </div>

                <!-- หมวดที่ 3 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-amber-500 mb-3 flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> หมวดที่ 3 : สถานะ</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold">สถานะ <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="sw-form-status" onchange="document.getElementById('sw-form-status-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                    <option value="พร้อมใช้งาน" selected>พร้อมใช้งาน</option>
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <p id="sw-form-status-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกสถานะ</p>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4">
                <button type="button" onclick="closeAssetItemModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-2xl font-bold transition cursor-pointer shadow-sm">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
<form id="form-network" novalidate
            onsubmit="if(typeof createNetworkItem === 'function') createNetworkItem(event); else closeAssetItemModal(); return false;"
            class="space-y-5">
            
            <div class="space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar pr-2 pb-2">
                
                <!-- หมวดที่ 1 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-blue-600 mb-3 flex items-center gap-2"><i class="fa-solid fa-boxes-stacked"></i> หมวดที่ 1 : ข้อมูลทรัพย์สิน</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <!-- รูปภาพอุปกรณ์ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">รูปภาพอุปกรณ์ (ไม่บังคับ)</label>
                            <div class="flex flex-col sm:flex-row items-center gap-4 p-3.5 sm:p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                                <div class="relative w-28 h-28 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 hover:border-blue-400 flex flex-col items-center justify-center overflow-hidden shrink-0 transition group cursor-pointer" onclick="document.getElementById('nw-form-image').click()">
                                    <img id="nw-form-image-preview" src="" alt="พรีวิวรูปอุปกรณ์" class="hidden w-full h-full object-contain p-1">
                                    <div id="nw-form-image-placeholder" class="flex flex-col items-center justify-center p-2 text-center text-slate-400 group-hover:text-blue-500 transition">
                                        <i class="fa-solid fa-camera text-2xl mb-1 text-slate-300 group-hover:text-blue-500 transition"></i>
                                        <span class="text-[11px] font-bold">เพิ่มรูปภาพ</span>
                                    </div>
                                    <div id="nw-form-image-overlay" class="hidden absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <i class="fa-solid fa-pen text-xs"></i> เปลี่ยนรูป
                                    </div>
                                </div>
                                <div class="flex-1 text-center sm:text-left flex flex-col justify-center">
                                    <p class="text-xs text-slate-700 font-bold mb-0.5">อัปโหลดรูปภาพอุปกรณ์ หรือครุภัณฑ์</p>
                                    <p class="text-[11px] text-slate-400 font-semibold mb-2.5">รองรับไฟล์ JPG, JPEG, PNG, WEBP (ขนาดไม่เกิน 5 MB)</p>
                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                        <button type="button" onclick="document.getElementById('nw-form-image').click()" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <i class="fa-solid fa-arrow-up-from-bracket text-xs"></i> เลือกรูปภาพ
                                        </button>
                                        <button type="button" id="nw-form-image-remove-btn" onclick="removeAssetImage('nw')" class="hidden px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                            <i class="fa-solid fa-trash-can text-xs"></i> ลบรูป
                                        </button>
                                    </div>
                                    <input type="file" id="nw-form-image" accept="image/jpeg,image/png,image/webp,image/jpg" onchange="previewAssetImage('nw', this)" class="hidden">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ประเภททรัพย์สิน <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="nw-form-category" onchange="handleAiFormCategoryChange(); document.getElementById('nw-form-category-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                        <option value="" disabled selected>-- เลือกประเภททรัพย์สิน --</option>
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
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="nw-form-category-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกประเภททรัพย์สิน</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ชนิดทรัพย์สิน <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="nw-form-kind" disabled onchange="handleAiFormKindChange(); document.getElementById('nw-form-kind-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- กรุณาเลือกประเภททรัพย์สินก่อน --</option>
                                        @if(isset($dbDeviceTypes) && count($dbDeviceTypes) > 0)
                                            @foreach($dbDeviceTypes as $type)
                                                <option value="{{ $type->id ?? $type }}">{{ $type->name ?? $type }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="nw-form-kind-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกชนิดทรัพย์สิน</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">ยี่ห้อ / ผู้ผลิต <span class="text-red-500">*</span></label>
                                <input type="text" id="nw-form-brand" placeholder="เช่น Cisco, TP-Link, Ruijie" oninput="document.getElementById('nw-form-brand-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="nw-form-brand-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกยี่ห้อ / ผู้ผลิต</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">รุ่น / จุดเชื่อมต่อ <span class="text-red-500">*</span></label>
                                <input type="text" id="nw-form-model" placeholder="เช่น Catalyst 2960, UniFi AP, สายแลน" oninput="document.getElementById('nw-form-model-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="nw-form-model-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกรุ่น / จุดเชื่อมต่อ</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <div class="flex items-center justify-between mb-1.5 gap-2">
                                    <label class="block text-slate-700 font-bold text-xs sm:text-sm whitespace-nowrap">รหัสอุปกรณ์ / จุดเชื่อมต่อ <span id="nw-code-required-star" class="text-red-500">*</span></label>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium cursor-pointer select-none hover:text-blue-600 transition-colors whitespace-nowrap shrink-0">
                                        <input type="checkbox" id="nw-form-no-code" onchange="toggleNoCode('nw')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <span>ไม่มีรหัส (สร้างอัตโนมัติ)</span>
                                    </label>
                                </div>
                                <input type="text" id="nw-form-code" placeholder="เช่น NW-001" oninput="document.getElementById('nw-form-code-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 transition-colors">
                                <p id="nw-form-code-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกรหัสอุปกรณ์ / รหัสจุดเชื่อมต่อ</p>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1.5 gap-2">
                                    <label class="block text-slate-700 font-bold text-xs sm:text-sm whitespace-nowrap">MAC Address / Serial <span id="nw-serial-required-star" class="text-red-500">*</span></label>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium cursor-pointer select-none hover:text-blue-600 transition-colors whitespace-nowrap shrink-0">
                                        <input type="checkbox" id="nw-form-no-serial" onchange="toggleNoSerial('nw')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <span>ไม่มี MAC / เลขเครื่อง (N/A)</span>
                                    </label>
                                </div>
                                <input type="text" id="nw-form-serial" placeholder="เช่น AA:BB:CC หรือ SN-123" oninput="document.getElementById('nw-form-serial-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 transition-colors">
                                <p id="nw-form-serial-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอก MAC Address หรือหมายเลขเครื่อง</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold">IP Address / พอร์ต / รายละเอียดจุดติดตั้ง <span class="text-red-500">*</span></label>
                            <textarea id="nw-form-specs" rows="3" placeholder="เช่น IP: 192.168.1.1, พอร์ต LAN 1-24, ความเร็ว 1Gbps, เชื่อมต่อกับโต๊ะอาจารย์ ห้อง 501..." oninput="document.getElementById('nw-form-specs-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400 resize-none"></textarea>
                            <p id="nw-form-specs-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกรายละเอียดจุดติดตั้ง</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ผู้จัดจำหน่าย <span class="text-red-500">*</span></label>
                                <input type="text" id="nw-form-supplier" placeholder="ชื่อบริษัท หรือ ร้านค้า" oninput="document.getElementById('nw-form-supplier-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="nw-form-supplier-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกผู้จัดจำหน่าย</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">วันที่จัดซื้อ <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-calendar text-slate-400"></i>
                                    </div>
                                    <input type="text" id="nw-form-purchase-date" placeholder="เลือกวันที่..." onchange="document.getElementById('nw-form-purchase-date-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                </div>
                                <p id="nw-form-purchase-date-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาระบุวันที่จัดซื้อ</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ราคาทรัพย์สิน (บาท) <span class="text-red-500">*</span></label>
                                <input type="text" id="nw-form-price" placeholder="0.00" oninput="document.getElementById('nw-form-price-error').classList.add('hidden'); this.classList.remove('border-red-500'); formatCurrency(this);"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="nw-form-price-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกราคาทรัพย์สิน</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ระยะเวลารับประกัน <span class="text-red-500">*</span></label>
                                <input type="text" id="nw-form-warranty" placeholder="เช่น 1 ปี, 6 เดือน" oninput="document.getElementById('nw-form-warranty-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 font-bold shadow-sm placeholder:text-slate-400">
                                <p id="nw-form-warranty-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกระยะเวลารับประกัน</p>
                            </div>
                        </div>
                    </div>
                </div>

                

                  <!-- หมวดที่ 2 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-blue-600 mb-3 flex items-center gap-2"><i class="fa-solid fa-building"></i> หมวดที่ 2 : ข้อมูลอาคาร</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">อาคาร <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="nw-form-building" onchange="handleAiFormBuildingChange(); document.getElementById('nw-form-building-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                        <option value="" disabled selected>-- เลือกอาคาร --</option>
                                        @if(isset($dbBuildings) && count($dbBuildings) > 0)
                                            @foreach($dbBuildings as $building)
                                                <option value="{{ $building->building_id }}">{{ $building->building_name }}</option>
                                            @endforeach
                                        @else
                                            <option value="B01">อาคารเทคโนโลยีสารสนเทศ</option>
                                            <option value="B02">อาคารช่างยนต์</option>
                                            <option value="B03">อาคารอำนวยการ</option>
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="nw-form-building-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกอาคาร</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ชั้น <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="nw-form-floor" onchange="handleAiFormFloorChange(); document.getElementById('nw-form-floor-error').classList.add('hidden'); this.classList.remove('border-red-500');" disabled
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- เลือกชั้น --</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="nw-form-floor-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกชั้น</p>
                            </div>
                            <div>
                                <label class="block text-slate-700 mb-1.5 font-bold">ห้อง <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="nw-form-room" onchange="document.getElementById('nw-form-room-error').classList.add('hidden'); this.classList.remove('border-red-500');" disabled
                                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="" disabled selected>-- เลือกห้อง --</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p id="nw-form-room-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกห้อง</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-slate-700 mb-1.5 font-bold">บุคลากรที่รับผิดชอบ <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="nw-form-responsible" onchange="document.getElementById('nw-form-responsible-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                    <option value="" disabled selected>-- เลือกบุคลากร --</option>
                                    @if(isset($users) && count($users) > 0)
                                        @foreach($users as $user)
                                            <option value="{{ trim(($user->prefix ? $user->prefix . ' ' : '') . $user->first_name . ' ' . $user->last_name) }}">{{ $user->prefix }} {{ $user->first_name }} {{ $user->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <p id="nw-form-responsible-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกบุคลากรที่รับผิดชอบ</p>
                        </div>
                    </div>
                </div>

                <!-- หมวดที่ 3 -->
                <div>
                    <h5 class="text-sm sm:text-base font-bold text-amber-500 mb-3 flex items-center gap-2"><i class="fa-solid fa-circle-info"></i> หมวดที่ 3 : สถานะ</h5>
                    <div class="space-y-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100">
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold">สถานะ <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="nw-form-status" onchange="document.getElementById('nw-form-status-error').classList.add('hidden'); this.classList.remove('border-red-500');"
                                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer appearance-none shadow-sm font-bold">
                                    <option value="พร้อมใช้งาน" selected>พร้อมใช้งาน</option>
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            <p id="nw-form-status-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณาเลือกสถานะ</p>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4">
                <button type="button" onclick="closeAssetItemModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-2xl font-bold transition cursor-pointer shadow-sm">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div></div><!-- Modal ????????????????????????? --><div id="modal-manage-departments" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center z-50 p-4" style="display: none;"><div class="bg-white w-full max-w-4xl rounded-3xl p-6 md:p-8 shadow-2xl space-y-6 border border-slate-100 relative max-h-[90vh] overflow-y-auto custom-scrollbar"><!-- Header --><div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4"><h3 id="modal-manage-dept-title" class="text-lg md:text-xl font-black text-slate-900 flex items-center gap-2"
                <i class="fa-solid fa-sliders text-blue-600"></i> จัดการหน่วยงาน / แผนกวิชา
            </h3>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="openAddDeptGroupModal()"
                    class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-plus"></i> เพิ่มฝ่าย
                </button>
                <button type="button" onclick="openNewSubDeptModal()"
                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-plus"></i> เพิ่มงาน/แผนกวิชา
                </button>
                <button type="button" onclick="closeManageDepartmentsModal()"
                    class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl transition cursor-pointer ml-1">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Section: สังกัด / ฝ่ายงาน -->
        <div class="space-y-2">
            <label class="block text-sm font-bold text-slate-800">สังกัด / ฝ่ายงาน</label>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[240px]">
                    <select id="manage-dept-group-select" onchange="updateSubDeptTableByGroup(this.value)"
                        class="w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl focus:outline-none focus:border-blue-500 cursor-pointer shadow-xs appearance-none pr-10">
                        <option value="all">-- ทุกสังกัด / ฝ่ายงาน --</option>
                        <option value="resource">ฝ่ายบริหารทรัพยากร</option>
                        <option value="planning">ฝ่ายแผนงานและความร่วมมือ</option>
                        <option value="student">ฝ่ายพัฒนากิจการนักเรียน นักศึกษา</option>
                        <option value="academic">ฝ่ายวิชาการ</option>
                    </select>
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <button type="button" onclick="editDeptGroup()"
                    class="px-4 py-2.5 border border-amber-200 bg-white text-amber-600 hover:bg-amber-50 text-xs font-bold rounded-xl flex items-center gap-1.5 transition cursor-pointer shadow-xs">
                    <i class="fa-solid fa-pen text-amber-500"></i> แก้ไข
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
                            <th class="p-3.5">งาน / แผนกวิชา</th>
                            <th class="p-3.5 pr-4 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="manage-subdept-tbody" class="divide-y divide-slate-100 text-slate-700">
                        <!-- โหลดผ่าน JS -->
                    </tbody>
                </table>
            </div>

            <!-- Footer count -->
            <p id="manage-subdept-footer-count" class="text-center text-xs text-slate-400 font-medium pt-2">
                แสดง 1 - 0 จาก 0 รายการ
            </p>
        </div>

    </div>
</div>



<!-- Modal แก้ไขสังกัด/ฝ่ายงาน -->
<div id="modal-edit-dept-group" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    แก้ไขสังกัด / ฝ่ายงาน
                </h3>
            </div>
            <button type="button" onclick="closeEditDeptGroupModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form onsubmit="submitEditDeptGroupForm(event)" class="space-y-4 text-xs font-semibold">
            <input type="hidden" id="edit-dept-group-old-name">
            <div>
                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">ชื่อสังกัด / ฝ่ายงาน <span class="text-red-500">*</span></label>
                <input type="text" id="edit-dept-group-name-input" required placeholder="กรอกชื่อสังกัด / ฝ่ายงาน"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs">
            </div>
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditDeptGroupModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-2xl transition cursor-pointer">ยกเลิก</button>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl transition shadow-md shadow-blue-200 cursor-pointer">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal แก้ไขงาน/แผนกวิชา -->
<div id="modal-edit-sub-dept" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    แก้ไขงาน / แผนกวิชา
                </h3>
            </div>
            <button type="button" onclick="closeEditSubDeptModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form onsubmit="submitEditSubDeptForm(event)" class="space-y-4 text-xs font-semibold">
            <input type="hidden" id="edit-subdept-group-key">
            <input type="hidden" id="edit-subdept-old-name">
            <div>
                <label class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">งาน / แผนกวิชา <span class="text-red-500">*</span></label>
                <input type="text" id="edit-subdept-name-input" required placeholder="กรุณากรอก งาน / แผนกวิชา"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs">
            </div>
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditSubDeptModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-2xl transition cursor-pointer">ยกเลิก</button>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl transition shadow-md shadow-blue-200 cursor-pointer">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal เพิ่มชนิดทรัพย์สิน (Image 1 Popup) -->
<div id="modal-add-asset-kind"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4"
    style="display: none;">
    <div
        class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    เพิ่มชนิดทรัพย์สิน
                </h3>
            </div>
            <button type="button" onclick="closeAddAssetKindModal()"
                class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form onsubmit="submitAddAssetKindForm(event)" class="space-y-4">
            <!-- Field 1: ประเภททรัพย์สิน -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ประเภททรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="add-kind-category-select" onchange="if(typeof clearAddAssetKindError==='function') clearAddAssetKindError('category')"
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 cursor-pointer shadow-xs appearance-none pr-10">
                        <option value="" disabled selected>เลือกประเภททรัพย์สิน</option>
                    </select>
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <p id="add-kind-category-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาเลือกประเภททรัพย์สิน</p>
            </div>

            <!-- Field 2: ชื่อชนิดทรัพย์สิน -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ชื่อชนิดทรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <input type="text" id="add-kind-name-input" placeholder="กรอกชื่อชนิดทรัพย์สิน" oninput="if(typeof clearAddAssetKindError==='function') clearAddAssetKindError('name')"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300">
                <p id="add-kind-name-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณากรอกชื่อชนิดทรัพย์สิน</p>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeAddAssetKindModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Modal เพิ่มประเภททรัพย์สิน -->
<div id="modal-add-asset-category" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    เพิ่มประเภททรัพย์สิน
                </h3>
            </div>
            <button type="button" onclick="closeAddAssetCategoryModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form onsubmit="submitAddAssetCategoryForm(event)" class="space-y-4">
            <!-- Field 1: ชื่อประเภททรัพย์สิน -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ชื่อประเภททรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <input type="text" id="add-category-name-input" placeholder="กรอกชื่อประเภททรัพย์สิน"
                    oninput="if(typeof clearAddCategoryError === 'function') clearAddCategoryError()"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="add-category-name-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณากรอกประเภททรัพย์สิน</p>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeAddAssetCategoryModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Modal แก้ไขประเภททรัพย์สิน (Edit Asset Category Modal) -->
<div id="modal-edit-asset-category" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    แก้ไขประเภททรัพย์สิน
                </h3>
            </div>
            <button type="button" onclick="closeEditAssetCategoryModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="edit-asset-category-form" onsubmit="submitEditAssetCategoryForm(event)" class="space-y-4 text-xs font-semibold">
            <input type="hidden" id="edit-category-old-name">
            <div>
                <label for="edit-category-name-input" class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">ชื่อประเภททรัพย์สิน <span class="text-red-500">*</span></label>
                <input type="text" id="edit-category-name-input" placeholder="กรอกชื่อประเภททรัพย์สิน"
                    oninput="if(typeof clearEditCategoryError === 'function') clearEditCategoryError()"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs transition-colors">
                <p id="edit-category-name-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณากรอกประเภททรัพย์สิน</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditAssetCategoryModal()"
                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-2xl transition cursor-pointer">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl transition shadow-md shadow-blue-200 cursor-pointer">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal แก้ไขชนิดทรัพย์สิน (Edit Asset Kind Modal) -->
<div id="modal-edit-asset-kind" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    แก้ไขชนิดทรัพย์สิน
                </h3>
            </div>
            <button type="button" onclick="closeEditAssetKindModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="edit-asset-kind-form" onsubmit="submitEditAssetKindForm(event)" class="space-y-4 text-xs font-semibold">
            <input type="hidden" id="edit-kind-old-name">
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ประเภททรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="edit-kind-category-select" required
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 appearance-none pr-10 shadow-xs cursor-pointer">
                        <option value="" disabled class="text-slate-300">เลือกประเภททรัพย์สิน</option>
                    </select>
                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-sm"></i>
                    </div>
                </div>
            </div>
            <div>
                <label for="edit-kind-name-input" class="block text-slate-700 mb-1.5 font-bold text-xs sm:text-sm">ชื่อชนิดทรัพย์สิน <span class="text-red-500">*</span></label>
                <input type="text" id="edit-kind-name-input" required placeholder="กรอกชื่อชนิดทรัพย์สิน"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditAssetKindModal()"
                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-2xl transition cursor-pointer">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl transition shadow-md shadow-blue-200 cursor-pointer">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal เพิ่มประเภทบุคลากร -->
<div id="modal-add-personnel-type" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    เพิ่มประเภทบุคลากร
                </h3>
            </div>
            <button type="button" onclick="closeNewPositionModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="new-personnel-type-form" onsubmit="submitAddPersonnelTypeForm(event)" class="space-y-4 text-left">
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ประเภทบุคลากร <span class="text-red-500">*</span>
                </label>
                <input type="text" id="personnel-type-name-input" oninput="document.getElementById('personnel-type-name-error').classList.add('hidden'); this.classList.remove('border-red-500');" placeholder="เช่น บุคลากรทั่วไป"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300">
                <p id="personnel-type-name-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกประเภทบุคลากร</p>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeNewPositionModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal แก้ไขประเภทบุคลากร -->
<div id="modal-edit-personnel-type" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-pen text-amber-500"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    แก้ไขประเภทบุคลากร
                </h3>
            </div>
            <button type="button" onclick="closeEditPersonnelTypeModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="edit-personnel-type-form" onsubmit="submitEditPersonnelTypeForm(event)" class="space-y-4 text-left">
            <input type="hidden" id="edit-personnel-type-id-input" value="">
            
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ประเภทบุคลากร <span class="text-red-500">*</span>
                </label>
                <input type="text" id="edit-personnel-type-name-input" required placeholder="เช่น บุคลากรทั่วไป"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-amber-500 shadow-xs placeholder:text-slate-300">
                <p id="edit-personnel-type-name-error" class="text-red-500 text-[10px] mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i>กรุณากรอกประเภทบุคลากร</p>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeEditPersonnelTypeModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
<!-- ป๊อปอัปเพิ่มข้อมูลอุปกรณ์ (Device Type) -->
<div id="modal-add-device-type" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-emerald-600 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-layer-group text-white text-sm"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    เพิ่มข้อมูลทรัพย์สิน / ครุภัณฑ์
                </h3>
            </div>
            <button type="button" onclick="closeDeviceTypeModal()" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="new-device-type-form" onsubmit="submitAddDeviceTypeForm(event)" class="p-6 space-y-4" novalidate>
            <!-- Field 1: ประเภททรัพย์สิน -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ประเภททรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="device-type-category-select" required
                        onchange="updateDeviceTypeKindDropdown(this.value); if(typeof clearDeviceTypeError==='function') clearDeviceTypeError('category')"
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-emerald-500 shadow-xs cursor-pointer appearance-none transition-colors">
                        <option value="" disabled selected>-- เลือกประเภททรัพย์สิน --</option>
                    </select>
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <p id="device-type-category-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาเลือกประเภททรัพย์สิน</p>
            </div>

            <!-- Field 2: ชนิดทรัพย์สิน -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ชนิดทรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="device-type-kind-select" required
                        onchange="if(typeof clearDeviceTypeError==='function') clearDeviceTypeError('kind')"
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-400 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-emerald-500 shadow-xs cursor-pointer appearance-none transition-colors">
                        <option value="" disabled selected>-- กรุณาเลือกประเภททรัพย์สินก่อน --</option>
                    </select>
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <p id="device-type-kind-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาเลือกชนิดทรัพย์สิน</p>
            </div>

            <!-- Field 3+4: ยี่ห้อ + รุ่น (2 columns) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                        ยี่ห้อ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="device-type-brand-input" required
                        placeholder="เช่น HP, Dell, Microsoft..."
                        oninput="if(typeof clearDeviceTypeError==='function') clearDeviceTypeError('brand')"
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-emerald-500 shadow-xs placeholder:text-slate-300 transition-colors">
                    <p id="device-type-brand-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณากรอกยี่ห้อ</p>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                        รุ่น <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="device-type-model-input" required
                        placeholder="เช่น ProDesk 400, 2024..."
                        oninput="if(typeof clearDeviceTypeError==='function') clearDeviceTypeError('model')"
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-emerald-500 shadow-xs placeholder:text-slate-300 transition-colors">
                    <p id="device-type-model-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณากรอกรุ่น</p>
                </div>
            </div>

            <!-- Field 5: รายละเอียดเพิ่มเติม -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    รายละเอียดเพิ่มเติม
                </label>
                <textarea id="device-type-desc-input" rows="3"
                    placeholder="ระบุสเปก, สิทธิ์การใช้งาน หรือคำอธิบายเพิ่มเติม..."
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-emerald-500 shadow-xs placeholder:text-slate-300 resize-none"></textarea>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" onclick="closeDeviceTypeModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ป๊อปอัปแก้ไขข้อมูลอุปกรณ์ (Edit Device Type) -->
<div id="modal-edit-device-type" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-amber-500 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-pen text-white text-sm"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    แก้ไขข้อมูลทรัพย์สิน / ครุภัณฑ์
                </h3>
            </div>
            <button type="button" onclick="closeEditDeviceTypeModal()" class="text-slate-400 hover:text-slate-600 transition p-1 rounded-lg cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="edit-device-type-form" onsubmit="submitEditDeviceTypeForm(event)" class="p-6 space-y-4" novalidate>
            <input type="hidden" id="edit-device-type-id" value="">

            <!-- Field 1: ประเภททรัพย์สิน -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ประเภททรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="edit-device-type-category-select" required disabled
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none shadow-xs cursor-not-allowed appearance-none transition-colors">
                        <option value="" disabled selected>-- เลือกประเภททรัพย์สิน --</option>
                    </select>
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <p id="edit-device-type-category-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาเลือกประเภททรัพย์สิน</p>
            </div>

            <!-- Field 2: ชนิดทรัพย์สิน -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ชนิดทรัพย์สิน <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="edit-device-type-kind-select" required disabled
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none shadow-xs cursor-not-allowed appearance-none transition-colors">
                        <option value="" disabled selected>-- กรุณาเลือกประเภททรัพย์สินก่อน --</option>
                    </select>
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <p id="edit-device-type-kind-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาเลือกชนิดทรัพย์สิน</p>
            </div>

            <!-- Field 3+4: ยี่ห้อ + รุ่น (2 columns) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                        ยี่ห้อ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="edit-device-type-brand-input" required
                        placeholder="เช่น HP, Dell, Microsoft..."
                        oninput="if(typeof clearEditDeviceTypeError==='function') clearEditDeviceTypeError('brand')"
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-amber-500 shadow-xs placeholder:text-slate-300 transition-colors">
                    <p id="edit-device-type-brand-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณากรอกยี่ห้อ</p>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                        รุ่น <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="edit-device-type-model-input" required
                        placeholder="เช่น ProDesk 400, 2024..."
                        oninput="if(typeof clearEditDeviceTypeError==='function') clearEditDeviceTypeError('model')"
                        class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-amber-500 shadow-xs placeholder:text-slate-300 transition-colors">
                    <p id="edit-device-type-model-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณากรอกรุ่น</p>
                </div>
            </div>

            <!-- Field 5: รายละเอียดเพิ่มเติม -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    รายละเอียดเพิ่มเติม
                </label>
                <textarea id="edit-device-type-desc-input" rows="3"
                    placeholder="ระบุสเปก, สิทธิ์การใช้งาน หรือคำอธิบายเพิ่มเติม..."
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-amber-500 shadow-xs placeholder:text-slate-300 resize-none"></textarea>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" onclick="closeEditDeviceTypeModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Modal ดูข้อมูลบุคลากร -->
<div id="modal-view-personnel" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white max-w-4xl w-full rounded-3xl border border-slate-100 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="px-6 md:px-8 py-5 md:py-6 flex items-center justify-between border-b border-slate-100">
            <h4 class="font-black text-lg md:text-xl text-slate-900 flex items-center gap-2">
                ข้อมูลบุคลากร
            </h4>
            <button type="button" onclick="closeViewPersonnelModal()" class="w-8 h-8 flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 md:p-8 space-y-8 max-h-[80vh] overflow-y-auto custom-scrollbar">
            
            <!-- ข้อมูลส่วนตัว -->
            <div>
                <h5 class="text-sm font-black text-slate-800 mb-4">ข้อมูลส่วนตัว</h5>
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Profile Picture (Left) -->
                    <div class="w-full md:w-64 shrink-0 flex flex-col items-center justify-center p-6 border border-slate-200 border-dashed bg-slate-50/50 rounded-3xl h-64 relative overflow-hidden">
                        <!-- Actual photo when available -->
                        <img id="view-personnel-profile-img" src="" class="hidden absolute inset-0 w-full h-full object-cover rounded-3xl">
                        <!-- Default icon when no photo -->
                        <div id="view-personnel-profile-icon" class="w-16 h-16 mb-3 rounded-full text-slate-300 flex items-center justify-center text-4xl shadow-sm">
                            <i class="fa-regular fa-image"></i>
                        </div>
                        <span id="view-personnel-profile-label" class="text-sm font-bold text-slate-600">รูปโปรไฟล์</span>
                        <span class="text-[10px] font-semibold text-slate-400 mt-1" id="view-personnel-no-photo">ไม่มีรูปภาพ</span>
                    </div>
                    
                    <!-- Info Grid (Right) -->
                    <div class="flex-1 space-y-6">
                        <!-- ส่วนข้อมูลส่วนตัว -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- คำนำหน้า (full width) -->
                        <div class="sm:col-span-2">
                            <label class="block text-slate-700 text-xs font-bold mb-2">คำนำหน้า</label>
                            <div id="view-personnel-prefix" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>

                        <!-- ชื่อ (ภาษาไทย) -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">ชื่อ (ภาษาไทย)</label>
                            <div id="view-personnel-fname" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        <!-- นามสกุล (ภาษาไทย) -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">นามสกุล (ภาษาไทย)</label>
                            <div id="view-personnel-lname" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        
                        <!-- ชื่อ (ภาษาอังกฤษ) -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">ชื่อ (ภาษาอังกฤษ)</label>
                            <div id="view-personnel-fname-en" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        <!-- นามสกุล (ภาษาอังกฤษ) -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">นามสกุล (ภาษาอังกฤษ)</label>
                            <div id="view-personnel-lname-en" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>

                        <!-- เลขบัตรประชาชน (full width) -->
                        <div class="sm:col-span-2">
                            <label class="block text-slate-700 text-xs font-bold mb-2">เลขบัตรประชาชน</label>
                            <div id="view-personnel-citizen-id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        </div>
                        
                        <!-- ส่วนข้อมูลการทำงาน -->
                        <div class="pt-2 border-t border-slate-100">
                            <h5 class="font-black text-slate-800 mb-3 text-sm">ข้อมูลการทำงาน</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- ประเภทบุคลากร -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">ประเภทบุคลากร</label>
                            <div id="view-personnel-type" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        <!-- ฝ่าย -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">ฝ่าย</label>
                            <div id="view-personnel-dept-group" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        
                        <!-- แผนก/งาน -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">แผนก/งาน</label>
                            <div id="view-personnel-dept" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        <!-- สิทธิ์การใช้งาน -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">สิทธิ์การใช้งาน</label>
                            <div id="view-personnel-role" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                        </div>
                        </div>
                        </div>

                        <!-- ส่วนบัญชีผู้ใช้ -->
                        <div class="pt-2 border-t border-slate-100">
                            <h5 class="font-black text-slate-800 mb-3 text-sm">บัญชีผู้ใช้งาน</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- ชื่อผู้ใช้ -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">ชื่อผู้ใช้ (Username)</label>
                            <div id="view-personnel-username" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center font-mono"></div>
                        </div>
                        <!-- รหัสผ่าน -->
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-2">รหัสผ่าน (Password)</label>
                            <div class="relative w-full">
                                <input type="password" id="view-personnel-password" readonly class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center font-mono focus:outline-none cursor-default">
                                <button type="button" onclick="toggleAdminPasswordVisibility('view-personnel-password', 'view-personnel-password-eye')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <i class="fa-solid fa-eye-slash" id="view-personnel-password-eye"></i>
                                </button>
                            </div>
                        </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="border-slate-100 mt-2">
            
            <!-- ข้อมูลติดต่อ -->
            <div class="pt-2">
                <h5 class="text-sm font-black text-slate-800 mb-3">ข้อมูลติดต่อ</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- เบอร์โทรศัพท์ -->
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-2">เบอร์โทรศัพท์</label>
                        <div id="view-personnel-phone" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center"></div>
                    </div>
                    <!-- อีเมล -->
                    <div>
                        <label class="block text-slate-700 text-xs font-bold mb-2">อีเมล</label>
                        <div id="view-personnel-email" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-2xl font-bold text-sm min-h-[46px] flex items-center font-mono"></div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="px-6 md:px-8 py-5 border-t border-slate-100 flex items-center justify-end gap-3">
            <button type="button" onclick="closeViewPersonnelModal()" class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-2xl transition shadow-sm cursor-pointer">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<!-- Modal แก้ไขสิทธิ์ผู้ใช้งาน (Profile) -->
<div id="modal-admin-profile" onclick="if(event.target === this) closeAdminProfileModal()" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-3 sm:p-4 overflow-y-auto" style="display: none;">
    <div class="bg-white max-w-3xl sm:max-w-4xl w-full rounded-2xl sm:rounded-3xl border border-slate-100 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[88vh] my-auto">
        <!-- Header -->
        <div class="px-5 sm:px-6 py-3.5 sm:py-4 flex items-center justify-between border-b border-slate-100 bg-white shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold shadow-sm">
                    <i class="fa-solid fa-user-circle"></i>
                </div>
                <h4 class="font-black text-base sm:text-lg text-slate-800 tracking-tight">
                    โปรไฟล์
                </h4>
            </div>
            <button type="button" onclick="closeAdminProfileModal()" class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 sm:p-6 overflow-y-auto flex-1 custom-scrollbar">
            <form id="admin-profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="prefix" value="{{ Auth::user()->prefix ?? '' }}">
                <input type="hidden" name="first_name" value="{{ Auth::user()->first_name ?? '' }}">
                <input type="hidden" name="last_name" value="{{ Auth::user()->last_name ?? '' }}">
                <input type="hidden" name="first_name_en" value="{{ Auth::user()->first_name_en ?? '' }}">
                <input type="hidden" name="last_name_en" value="{{ Auth::user()->last_name_en ?? '' }}">
                <input type="hidden" name="citizen_id" value="{{ Auth::user()->citizen_id ?? '' }}">
                <input type="hidden" name="gender" value="{{ Auth::user()->gender ?? '' }}">
                <input type="hidden" name="department" value="{{ Auth::user()->department ?? '' }}">
                <input type="hidden" name="position" value="{{ Auth::user()->position ?? '' }}">
                
                <!-- ข้อมูลส่วนตัว -->
                <div>
                    <h5 class="font-black text-slate-800 mb-3 text-xs sm:text-sm flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-blue-500 text-xs"></i>
                        ข้อมูลส่วนตัว
                    </h5>
                    <div class="flex flex-col md:flex-row gap-5">
                        <!-- Left Column (Image Upload) -->
                        <div class="w-full md:w-52 shrink-0 flex flex-col items-center md:items-start">
                            <div class="w-40 h-40 sm:w-44 sm:h-44 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden relative group bg-slate-50 flex items-center justify-center shadow-inner cursor-not-allowed">
                                <input type="file" name="profile_photo" id="profile-image-upload" onchange="previewAdminProfileImage(this)" class="absolute inset-0 w-full h-full opacity-0 cursor-not-allowed z-10" accept="image/jpeg,image/png,image/webp" disabled>
                                
                                <!-- Default Upload Content -->
                                <div id="profile-image-placeholder" class="flex flex-col items-center w-full h-full justify-center p-3 text-center @if(isset($profilePhotoUrl) && $profilePhotoUrl) hidden @endif">
                                    <div class="w-10 h-10 bg-slate-200 text-slate-400 rounded-xl flex items-center justify-center mb-2">
                                        <i class="fa-solid fa-user text-base"></i>
                                    </div>
                                    <h6 class="font-bold text-slate-700 text-xs mb-0.5">อัปโหลดรูปโปรไฟล์</h6>
                                    <p class="text-[10px] text-slate-400">ขนาดไฟล์ไม่เกิน 2 MB</p>
                                    <p class="text-[10px] text-slate-400">รองรับไฟล์ JPG, PNG</p>
                                </div>
                                
                                <!-- Preview Image Content -->
                                <div id="profile-image-preview" class="absolute inset-0 @if(!isset($profilePhotoUrl) || !$profilePhotoUrl) hidden @endif">
                                    <img src="{{ $profilePhotoUrl ?? '' }}" id="profile-image-img" class="w-full h-full object-cover">
                                    <!-- Overlay on hover (Disabled) -->
                                    <div class="absolute inset-0 bg-slate-900/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-0">
                                        <span class="text-white font-bold text-xs bg-black/50 backdrop-blur-sm px-2.5 py-1 rounded-lg"><i class="fa-solid fa-lock mr-1"></i>ไม่สามารถแก้ไขได้</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column (Form Fields) -->
                        <div class="flex-1 space-y-3.5">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-1.5">คำนำหน้า</label>
                                <div class="relative">
                                    <select name="prefix" id="profile-prefix" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm appearance-none cursor-not-allowed opacity-80" disabled>
                                        <option value="นาย" {{ (Auth::user()->prefix ?? '') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                        <option value="นาง" {{ (Auth::user()->prefix ?? '') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                        <option value="นางสาว" {{ (Auth::user()->prefix ?? '') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Names (TH) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">ชื่อ (ภาษาไทย)</label>
                                    <input type="text" name="first_name" id="profile-fname-th" value="{{ Auth::user()->first_name ?? '' }}" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80" readonly>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">นามสกุล (ภาษาไทย)</label>
                                    <input type="text" name="last_name" id="profile-lname-th" value="{{ Auth::user()->last_name ?? '' }}" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80" readonly>
                                </div>
                            </div>
                            
                            <!-- Names (EN) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">ชื่อ (ภาษาอังกฤษ)</label>
                                    <input type="text" name="first_name_en" id="profile-fname-en" value="{{ Auth::user()->first_name_en ?? '' }}" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80" readonly>
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">นามสกุล (ภาษาอังกฤษ)</label>
                                    <input type="text" name="last_name_en" id="profile-lname-en" value="{{ Auth::user()->last_name_en ?? '' }}" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80" readonly>
                                </div>
                            </div>
                            
                            <!-- Citizen ID -->
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-1.5">เลขบัตรประชาชน</label>
                                <input type="text" name="citizen_id" id="profile-citizen-id" value="{{ Auth::user()->citizen_id ?? '' }}" placeholder="เลขบัตรประชาชน 13 หลัก" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลการทำงาน -->
                @php
                    $userPos = Auth::user()->position;
                    $userDept = Auth::user()->department;
                    
                    $ptName = \Illuminate\Support\Facades\DB::table('personnel_types')
                        ->where('id', $userPos)
                        ->orWhere('name', $userPos)
                        ->value('name');
                    $displayPosition = $ptName ?: ($userPos ?: '-');

                    $deptRow = \Illuminate\Support\Facades\DB::table('departments')
                        ->where('department_id', $userDept)
                        ->orWhere('department_name', $userDept)
                        ->first();
                        
                    $displayDept = $deptRow ? $deptRow->department_name : ($userDept ?: '-');
                    
                    // Map group accurately based on the system's predefined lists
                    $displayGroup = '-';
                    
                    $resourceList = ["งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"];
                    $planningList = ["งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา"];
                    $studentList = ["งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"];
                    $academicList = ["งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า"];
                    
                    if (in_array($displayDept, $resourceList)) $displayGroup = 'ฝ่ายบริหารทรัพยากร';
                    elseif (in_array($displayDept, $planningList)) $displayGroup = 'ฝ่ายนโยบายและแผน';
                    elseif (in_array($displayDept, $studentList)) $displayGroup = 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า';
                    elseif (in_array($displayDept, $academicList)) $displayGroup = 'ฝ่ายวิชาการ';
                    else {
                        if ($deptRow && $deptRow->group) {
                            $dgName = \Illuminate\Support\Facades\DB::table('department_groups')
                                ->where('group_code', $deptRow->group)
                                ->orWhere('group_name', $deptRow->group)
                                ->value('group_name');
                            $displayGroup = $dgName ?: $deptRow->group;
                        }
                        if ($displayGroup === '-' || empty($displayGroup)) {
                            $displayGroup = Auth::user()->department_group ?: '-';
                        }
                    }

                    $roles = explode(',', Auth::user()->role ?? '');
                    $roleNames = [];
                    foreach($roles as $r) {
                        $r = trim($r);
                        if($r == 'admin') $roleNames[] = 'Admin(ผู้ดูแลระบบ)';
                        else if($r == 'teacher') $roleNames[] = 'แจ้งซ่อม';
                        else if($r == 'technician') $roleNames[] = 'ช่างซ่อมบำรุง';
                        else if($r == 'head') $roleNames[] = 'หัวหน้างาน';
                        else if($r == 'executive') $roleNames[] = 'ผู้บริหาร';
                        else if($r) $roleNames[] = $r;
                    }
                    $roleDisplay = !empty($roleNames) ? implode(', ', $roleNames) : '-';
                @endphp
                <div class="pt-4 border-t border-slate-100">
                    <h5 class="font-black text-slate-800 mb-3 text-xs sm:text-sm flex items-center gap-2">
                        <i class="fa-solid fa-briefcase text-blue-500 text-xs"></i>
                        ข้อมูลการทำงาน
                    </h5>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-slate-600 mb-1 font-bold text-xs">ประเภทบุคลากร</label>
                            <div class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs sm:text-sm min-h-[42px] flex items-center cursor-not-allowed">
                                {{ $displayPosition }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-slate-600 mb-1 font-bold text-xs">ฝ่าย</label>
                            <div class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs sm:text-sm min-h-[42px] flex items-center cursor-not-allowed">
                                {{ $displayGroup }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-slate-600 mb-1 font-bold text-xs">แผนก/งาน</label>
                            <div class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs sm:text-sm min-h-[42px] flex items-center cursor-not-allowed">
                                {{ $displayDept }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-slate-600 mb-1 font-bold text-xs">สิทธิ์การใช้งาน</label>
                            <div class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs sm:text-sm min-h-[42px] flex items-center cursor-not-allowed">
                                {{ $roleDisplay }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลติดต่อ -->
                <div class="pt-4 border-t border-slate-100">
                    <h5 class="font-black text-slate-800 mb-3 text-xs sm:text-sm flex items-center gap-2">
                        <i class="fa-solid fa-address-book text-blue-500 text-xs"></i>
                        ข้อมูลติดต่อ
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="profile-phone" value="{{ Auth::user()->phone ?? '' }}" placeholder="เช่น 084-567-8901" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">อีเมล</label>
                            <input type="email" name="email" id="profile-email" value="{{ Auth::user()->email ?? '' }}" placeholder="เช่น imron@ytc.ac.th" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- ความปลอดภัยและรหัสผ่าน -->
                <div class="pt-4 border-t border-slate-100">
                    <h5 class="font-black text-slate-800 mb-3 text-xs sm:text-sm flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-blue-500 text-xs"></i>
                        ความปลอดภัยและรหัสผ่าน
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">ชื่อผู้ใช้ (Username)</label>
                            <input type="text" id="profile-username" value="{{ Auth::user()->user_id ?? '' }}" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80" disabled>
                        </div>
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">รหัสผ่านปัจจุบัน</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="profile-current-password" placeholder="กรอกรหัสผ่านเพื่อยืนยันการเปลี่ยนแปลงรหัสใหม่" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                                <button type="button" onclick="toggleAdminPasswordVisibility('profile-current-password', 'profile-toggle-icon-current')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <i class="fa-solid fa-eye text-xs" id="profile-toggle-icon-current"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Passwords -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mt-3">
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">รหัสผ่านใหม่</label>
                            <input type="password" name="new_password" id="profile-password" placeholder="กรุณากรอกรหัสผ่านใหม่ (หากต้องการเปลี่ยน)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="new_password_confirmation" id="profile-password-confirm" placeholder="กรุณากรอกรหัสผ่านใหม่อีกครั้ง" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="px-5 sm:px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
            <button type="button" onclick="closeAdminProfileModal()" class="px-5 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 rounded-xl font-bold text-xs sm:text-sm transition cursor-pointer">
                ยกเลิก
            </button>
            <button type="submit" form="admin-profile-form" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl transition shadow-md shadow-blue-200 hover:shadow-lg cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-check text-xs"></i>
                บันทึก
            </button>
        </div>
    </div>
</div>


<!-- Modal ดูรายละเอียดทรัพย์สิน -->
<div id="modal-view-asset" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white w-full max-w-2xl sm:max-w-3xl rounded-3xl shadow-2xl overflow-hidden border border-slate-100 my-auto animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="px-6 py-5 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-white">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-lg sm:text-xl shadow-md shadow-blue-500/20 shrink-0">
                    <i class="fa-solid fa-desktop"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-black text-slate-800 tracking-tight">
                        รายละเอียดทรัพย์สิน
                    </h3>
                </div>
            </div>
            <button type="button" onclick="closeViewAssetModal()" class="w-9 h-9 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="p-5 sm:p-6 space-y-6 max-h-[72vh] overflow-y-auto custom-scrollbar text-sm" id="view-asset-content">
            <div class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 text-blue-500"></i>
                <br>
                <span class="font-bold text-slate-600">กำลังโหลดข้อมูล...</span>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-white flex items-center justify-end">
            <button type="button" onclick="closeViewAssetModal()" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm rounded-xl transition flex items-center justify-center gap-2 shadow-md shadow-blue-200 cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i> ปิด
            </button>
        </div>
    </div>
</div>

<!-- Modal กำหนดงบประมาณประจำปีของแต่ละฝ่าย (ตารางรวม 4 ฝ่าย) -->
<div id="modal-manage-department-budgets" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[60] flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white max-w-5xl w-full rounded-3xl shadow-2xl animate-in fade-in zoom-in-95 duration-200 border border-slate-100 overflow-hidden my-6">
        <!-- Header -->
        <div class="px-7 py-5 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-blue-50/70 via-white to-indigo-50/40">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-base sm:text-lg text-slate-800 tracking-tight">
                        กำหนดงบประมาณประจำปีของแต่ละฝ่าย
                    </h4>
                </div>
            </div>
            <button type="button" onclick="closeManageDeptBudgetsModal()" class="w-9 h-9 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="form-manage-dept-budgets" onsubmit="saveDepartmentBudgetsTable(event)" class="p-6 sm:p-7 space-y-5">
            <!-- แถบเลือกปีงบประมาณ และสรุปภาพรวม (อยู่ในแถวเดียวกัน) -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-2.5 shrink-0">
                    <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-blue-600 flex items-center justify-center text-sm shadow-xs shrink-0">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <div class="flex items-center gap-2 relative">
                        <label class="font-bold text-xs text-slate-700 whitespace-nowrap">ปีงบประมาณ:</label>
                        
                        <!-- Calendar-style Year Picker Button & Popup -->
                        <div class="relative">
                            <button type="button" id="dept-budget-fy-btn" onclick="toggleDeptBudgetYearPicker(event)" 
                                class="flex items-center gap-2 bg-white border border-blue-500 hover:border-blue-600 font-black text-slate-800 text-sm px-3.5 py-1.5 rounded-full shadow-xs transition hover:bg-blue-50/40 cursor-pointer outline-none focus:ring-2 focus:ring-blue-500/20">
                                <span id="dept-budget-fy-display">{{ $selectedFiscalYear ?? (date('Y') + 543) }}</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-500 transition-transform duration-200" id="dept-budget-fy-chevron"></i>
                            </button>
                            <input type="hidden" id="table-dept-budget-fiscal-year" value="{{ $selectedFiscalYear ?? (date('Y') + 543) }}">
                            
                            <!-- Calendar Year Picker Popup -->
                            <div id="dept-budget-year-picker-popup" class="hidden absolute top-full left-0 mt-2 w-64 bg-white border border-slate-200 shadow-2xl rounded-2xl z-[70] p-3 animate-in fade-in zoom-in-95 duration-150">
                                <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-100">
                                    <button type="button" onclick="changeDeptBudgetYearDecade(-9, event)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-600 transition cursor-pointer">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <span id="dept-budget-year-range" class="font-extrabold text-xs text-slate-700 tracking-wide">2569 - 2577</span>
                                    <button type="button" onclick="changeDeptBudgetYearDecade(9, event)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-600 transition cursor-pointer">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>
                                </div>
                                <div id="dept-budget-year-grid" class="grid grid-cols-3 gap-1.5 text-center">
                                    <!-- 3x3 grid of years -->
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <button type="button" onclick="selectDeptBudgetTodayYear(event)" class="text-blue-600 font-bold hover:underline cursor-pointer">
                                        ปีปัจจุบัน ({{ date('Y') + 543 }})
                                    </button>
                                    <span class="text-slate-400">เลือกปีงบประมาณ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- สรุปตัวเลขรวมด้านบน (อยู่ในแถวเดียวกันเสมอ ไม่แตกบรรทัด) -->
                <div class="flex items-center gap-2 sm:gap-2.5 text-xs flex-nowrap shrink-0 overflow-x-auto">
                    <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 shadow-xs flex items-center whitespace-nowrap">
                        <span class="text-slate-400 font-medium">งบประมาณทั้งหมด:</span>
                        <span id="modal-summary-total-allocated" class="font-extrabold text-blue-600 ml-1">฿0.00</span>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 shadow-xs flex items-center whitespace-nowrap">
                        <span class="text-slate-400 font-medium">ใช้ไปแล้ว:</span>
                        <span id="modal-summary-total-used" class="font-extrabold text-amber-600 ml-1">฿0.00</span>
                    </div>
                    <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 shadow-xs flex items-center whitespace-nowrap">
                        <span class="text-slate-400 font-medium">คงเหลือรวม:</span>
                        <span id="modal-summary-total-remaining" class="font-extrabold text-emerald-600 ml-1">฿0.00</span>
                    </div>
                </div>
            </div>

            <!-- ตารางกรอกงบประมาณของทั้ง 4 ฝ่าย -->
            <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-black text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4 text-center w-12">#</th>
                            <th class="py-3 px-4">ฝ่าย (หน่วยงานรับผิดชอบ)</th>
                            <th class="py-3 px-4 text-right w-52 sm:w-60">งบประมาณทั้งหมด (บาท)</th>
                            <th class="py-3 px-4 text-right w-36 sm:w-44">ใช้ไปแล้ว (โครงการ)</th>
                            <th class="py-3 px-4 text-right w-36 sm:w-44">งบคงเหลือ (บาท)</th>
                        </tr>
                    </thead>
                    <tbody id="dept-budgets-table-tbody" class="divide-y divide-slate-100 text-sm">
                        @php
                            $allDepts = [
                                ['name' => 'ฝ่ายบริหารทรัพยากร', 'icon' => 'fa-building-columns', 'color' => 'blue'],
                                ['name' => 'ฝ่ายนโยบายและแผน', 'icon' => 'fa-clipboard-list', 'color' => 'indigo'],
                                ['name' => 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า', 'icon' => 'fa-graduation-cap', 'color' => 'amber'],
                                ['name' => 'ฝ่ายวิชาการ', 'icon' => 'fa-book-open', 'color' => 'emerald'],
                            ];
                        @endphp
                        @foreach($allDepts as $idx => $d)
                        <tr class="hover:bg-slate-50/60 transition dept-budget-row" data-dept="{{ $d['name'] }}">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-400 text-xs">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-sm shrink-0">
                                        <i class="fa-solid {{ $d['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-800 text-sm">{{ $d['name'] }}</div>
                                        <div class="text-[11px] text-slate-400 font-medium" id="dept-row-subtext-{{ $idx }}">โครงการซ่อม 0 รายการ</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-xs pointer-events-none">฿</span>
                                    <input type="text" id="dept-table-input-{{ $idx }}" data-dept="{{ $d['name'] }}"
                                        value="0.00"
                                        oninput="formatCurrency(this); recalculateDeptTable();"
                                        class="dept-table-input w-full pl-7 pr-3 py-2 bg-white border border-slate-200 text-slate-800 text-sm font-black rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 text-right outline-none transition shadow-xs">
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span id="dept-table-used-{{ $idx }}" class="font-extrabold text-slate-700 text-sm block">฿0.00</span>
                                <span id="dept-table-percent-{{ $idx }}" class="text-[10px] text-slate-400 font-semibold">0%</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span id="dept-table-remaining-{{ $idx }}" class="font-black text-emerald-600 text-sm block">฿0.00</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-blue-50/60 border-t-2 border-blue-200/80 font-black text-xs sm:text-sm">
                        <tr>
                            <td colspan="2" class="py-3.5 px-4 text-slate-800">
                                <i class="fa-solid fa-calculator text-blue-600 mr-1.5"></i> รวมงบประมาณทุกฝ่าย:
                            </td>
                            <td class="py-3.5 px-4 text-right text-blue-700 font-black" id="table-footer-total-allocated">฿0.00</td>
                            <td class="py-3.5 px-4 text-right text-amber-700 font-black" id="table-footer-total-used">฿0.00</td>
                            <td class="py-3.5 px-4 text-right text-emerald-700 font-black" id="table-footer-total-remaining">฿0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-2 flex items-center justify-end gap-2.5 border-t border-slate-100">
                <button type="button" onclick="closeManageDeptBudgetsModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-xl transition cursor-pointer">
                    ยกเลิก
                </button>
                <button type="submit" id="btn-save-dept-budgets" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl transition shadow-md shadow-blue-200 cursor-pointer">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal เพิ่มโครงการ -->
<div id="modal-add-repair-project" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-[60] flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white max-w-2xl w-full rounded-3xl shadow-2xl animate-in fade-in zoom-in-95 duration-200 border border-slate-100 overflow-hidden my-6">
        <!-- Header -->
        <div class="px-7 py-5 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50/60 via-white to-blue-50/30">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-lg shadow-md shadow-blue-500/20">
                    <i class="fa-solid fa-folder-plus"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-base sm:text-lg text-slate-800 tracking-tight">
                        เพิ่มโครงการซ่อมครุภัณฑ์
                    </h4>
                </div>
            </div>
            <button type="button" onclick="closeAddRepairProjectModal()" class="w-9 h-9 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-7 space-y-6 max-h-[75vh] overflow-y-auto">
            <!-- ส่วนที่ 1: ข้อมูลโครงการ -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                    <span class="w-2 h-4 bg-blue-600 rounded-full"></span>
                    <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider">ข้อมูลโครงการ</h5>
                </div>

                <!-- รหัสโครงการ & ปีการศึกษา -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">
                            รหัสโครงการ <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                                <i class="fa-solid fa-hashtag"></i>
                            </span>
                            <input type="text" id="new-repair-project-code" oninput="clearError('new-repair-project-code')" placeholder="เช่น PM-2569-0001"
                                class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 block outline-none transition shadow-xs font-medium placeholder:text-slate-400">
                        </div>
                        <p id="err-new-repair-project-code" class="text-rose-500 text-xs mt-1.5 hidden font-medium">กรุณากรอกรหัสโครงการ</p>
                    </div>

                    <div class="relative">
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">
                            ปีการศึกษา <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-sm pointer-events-none">
                                <i class="fa-regular fa-calendar-days"></i>
                            </span>
                            <input type="text" id="new-repair-project-fiscal-year" readonly oninput="clearError('new-repair-project-fiscal-year')" placeholder="เลือกปีการศึกษา"
                                class="w-full pl-10 pr-9 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 block outline-none transition cursor-pointer shadow-xs font-medium placeholder:text-slate-400"
                                onclick="toggleYearPicker(event)">
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                                <i class="fa-solid fa-chevron-down"></i>
                            </span>
                        </div>
                        
                        <!-- Calendar-like Popup -->
                        <div id="year-picker-popup" class="hidden absolute top-full left-0 mt-2 w-full max-w-[280px] bg-white border border-slate-200 shadow-xl rounded-2xl z-50 p-3.5 transform origin-top transition-all duration-200">
                            <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-100">
                                <button type="button" id="year-picker-prev" onclick="changeYearDecade(-9, event)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-600 transition"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                                <span id="year-picker-range" class="font-bold text-xs text-slate-700">2569 - 2577</span>
                                <button type="button" onclick="changeYearDecade(9, event)" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-600 transition"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                            </div>
                            <div id="year-picker-grid" class="grid grid-cols-3 gap-1.5">
                                <!-- JS will populate years here -->
                            </div>
                        </div>
                        
                        <p id="err-new-repair-project-fiscal-year" class="text-rose-500 text-xs mt-1.5 hidden font-medium">กรุณาเลือกปีการศึกษา</p>
                    </div>
                </div>

                <!-- ชื่อโครงการ (เต็มแถว ให้ดูง่าย ไม่เบียด) -->
                <div>
                    <label class="block text-slate-700 mb-1.5 font-bold text-xs">
                        ชื่อโครงการ <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-sm pointer-events-none">
                            <i class="fa-regular fa-folder-open"></i>
                        </span>
                        <input type="text" id="new-repair-project-name" oninput="clearError('new-repair-project-name')" placeholder="กรอกชื่อโครงการ เช่น โครงการซ่อมบำรุงและปรับปรุงประสิทธิภาพครุภัณฑ์..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 block outline-none transition shadow-xs font-medium placeholder:text-slate-400">
                    </div>
                    <p id="err-new-repair-project-name" class="text-rose-500 text-xs mt-1.5 hidden font-medium">กรุณากรอกชื่อโครงการ</p>
                </div>

                <!-- ฝ่าย & ผู้รับผิดชอบ -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">
                            ฝ่าย (หน่วยงานรับผิดชอบ) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-sm pointer-events-none">
                                <i class="fa-regular fa-building"></i>
                            </span>
                            <select id="new-repair-project-department" onchange="clearError('new-repair-project-department'); updateBudgetSourceHelper(); updateProjectOwnersByDepartment();" class="w-full pl-10 pr-9 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 block outline-none transition cursor-pointer appearance-none shadow-xs font-medium">
                                <option value="">-- เลือกฝ่าย --</option>
                                @foreach($dbDepartmentGroups as $group)
                                <option value="{{ $group->group_name }}">{{ $group->group_name }}</option>
                                @endforeach
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                                <i class="fa-solid fa-chevron-down"></i>
                            </span>
                        </div>
                        <p id="err-new-repair-project-department" class="text-rose-500 text-xs mt-1.5 hidden font-medium">กรุณาเลือกฝ่าย</p>
                    </div>

                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">
                            ผู้รับผิดชอบ <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-sm pointer-events-none">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <select id="new-repair-project-owner" onchange="clearError('new-repair-project-owner')" class="w-full pl-10 pr-9 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 block outline-none transition cursor-pointer appearance-none shadow-xs font-medium">
                                <option value="">-- โปรดเลือกฝ่ายก่อน --</option>
                                @if(isset($users) && count($users) > 0)
                                    @php
                                        $allPersonnel = $users->filter(function($u) { return $u->status !== 'rejected'; })->sortBy(function($u) {
                                            return trim(($u->prefix ? $u->prefix . ' ' : '') . $u->first_name . ' ' . ($u->last_name ?? '')) ?: $u->name;
                                        });
                                    @endphp
                                    @foreach($allPersonnel as $user)
                                        @php
                                            $displayName = trim(($user->prefix ? $user->prefix . ' ' : '') . $user->first_name . ' ' . ($user->last_name ?? ''));
                                            if (empty($displayName)) $displayName = $user->name ?: ($user->username ?? $user->user_id);
                                            $userDeptGroup = $resolveUserGroup($user);
                                        @endphp
                                        <option value="{{ $displayName }}" data-department="{{ $userDeptGroup }}">{{ $displayName }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                                <i class="fa-solid fa-chevron-down"></i>
                            </span>
                        </div>
                        <p id="err-new-repair-project-owner" class="text-rose-500 text-xs mt-1.5 hidden font-medium">กรุณาเลือกผู้รับผิดชอบ</p>
                    </div>
                </div>
            </div>

            <!-- ส่วนที่ 2: งบประมาณและรายละเอียด -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-1 border-b border-slate-100 pt-1">
                    <span class="w-2 h-4 bg-emerald-500 rounded-full"></span>
                    <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider">งบประมาณและรายละเอียดเพิ่มเติม</h5>
                </div>

                <!-- แหล่งที่มางบ & วงเงินงบประมาณ -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">
                            แหล่งที่มาของงบประมาณ <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-sm pointer-events-none">
                                <i class="fa-solid fa-wallet"></i>
                            </span>
                            <select id="new-repair-project-budget-source" onchange="clearError('new-repair-project-budget-source'); updateBudgetSourceHelper(this.value);" class="w-full pl-10 pr-9 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 block outline-none transition cursor-pointer appearance-none shadow-xs font-medium">
                                <option value="งบประมาณกลาง" selected>งบประมาณกลาง</option>
                                <option value="งบเงินรายได้">งบเงินรายได้</option>
                                <option value="งบอุดหนุน">งบอุดหนุน</option>
                                <option value="งบพัฒนาสถานศึกษา">งบพัฒนาสถานศึกษา</option>
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                                <i class="fa-solid fa-chevron-down"></i>
                            </span>
                        </div>
                        <p id="err-new-repair-project-budget-source" class="text-rose-500 text-xs mt-1.5 hidden font-medium">กรุณาเลือกแหล่งที่มาของงบประมาณ</p>
                    </div>

                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">
                            งบประมาณ (บาท) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm pointer-events-none">฿</span>
                            <input type="text" id="new-repair-project-budget" oninput="clearError('new-repair-project-budget'); formatCurrency(this);" placeholder="0.00"
                                class="w-full pl-8 pr-4 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 block outline-none transition shadow-xs">
                        </div>
                        <p id="err-new-repair-project-budget" class="text-rose-500 text-xs mt-1.5 hidden font-medium">กรุณากรอกงบประมาณ</p>
                    </div>
                </div>

                <!-- หมายเหตุ / รายละเอียดเพิ่มเติม (เต็มแถว สบายตา ไม่โดนตัดข้อความ) -->
                <div>
                    <label class="block text-slate-700 mb-1.5 font-bold text-xs flex items-center justify-between">
                        <span>หมายเหตุ / รายละเอียดเพิ่มเติม</span>
                        <span class="text-slate-400 font-normal text-xs">(ไม่บังคับ)</span>
                    </label>
                    <textarea id="new-repair-project-description" rows="2" placeholder="ระบุวัตถุประสงค์ หรือรายละเอียดโครงการเพิ่มเติม (ถ้ามี)..."
                        class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 text-slate-800 text-sm rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition font-medium placeholder:text-slate-400 resize-none shadow-xs"></textarea>
                </div>
            </div>

            <!-- Script for custom Year Picker -->
            <script>
                let currentStartYear = 2569; 

                function renderYearPicker() {
                    const grid = document.getElementById('year-picker-grid');
                    const range = document.getElementById('year-picker-range');
                    const btnPrev = document.getElementById('year-picker-prev');
                    if (!grid || !range) return;
                    
                    grid.innerHTML = '';
                    range.innerText = `${currentStartYear} - ${currentStartYear + 8}`;

                    // Ensure previous button is always enabled
                    if (btnPrev) {
                        btnPrev.classList.remove('opacity-30', 'cursor-not-allowed');
                        btnPrev.classList.add('hover:bg-slate-100');
                    }

                    for (let i = 0; i < 9; i++) {
                        const year = currentStartYear + i;
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'py-2 rounded-xl text-xs font-bold hover:bg-blue-50 hover:text-blue-600 transition text-slate-700 border border-transparent hover:border-blue-200';
                        btn.innerText = year;
                        btn.onclick = (e) => {
                            e.stopPropagation();
                            document.getElementById('new-repair-project-fiscal-year').value = year;
                            document.getElementById('year-picker-popup').classList.add('hidden');
                            clearError('new-repair-project-fiscal-year');
                        };
                        grid.appendChild(btn);
                    }
                }

                function toggleYearPicker(e) {
                    e.stopPropagation();
                    const popup = document.getElementById('year-picker-popup');
                    if (!popup) return;
                    popup.classList.toggle('hidden');
                    if (!popup.classList.contains('hidden')) {
                        renderYearPicker();
                    }
                }

                function changeYearDecade(amount, e) {
                    e.stopPropagation();
                    currentStartYear += amount;
                    renderYearPicker();
                }

                // Close popup when clicking outside
                document.addEventListener('click', function(event) {
                    const popup = document.getElementById('year-picker-popup');
                    const input = document.getElementById('new-repair-project-fiscal-year');
                    if (popup && input && !popup.contains(event.target) && event.target !== input) {
                        popup.classList.add('hidden');
                    }
                });
            </script>
        </div>
        
        <!-- Footer -->
        <div class="px-7 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 rounded-b-3xl">
            <button type="button" onclick="closeAddRepairProjectModal()"
                class="px-5 py-2.5 text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-100 font-bold text-xs sm:text-sm rounded-xl transition cursor-pointer shadow-xs">
                ยกเลิก
            </button>
            <button type="button" onclick="saveNewRepairProject()"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-md shadow-blue-500/20 cursor-pointer">
                บันทึก
            </button>
        </div>
    </div>
</div>

<!-- Modal กำหนดเพดานวงเงินงบประมาณ (Budget Ceiling Management) -->
<div id="modal-manage-budget-sources" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center z-[70] p-4 hidden" style="display: none;">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="px-6 sm:px-7 py-5 bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-800 text-white flex items-center justify-between shadow-xs shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/10 text-white flex items-center justify-center text-lg shadow-inner shrink-0 backdrop-blur-sm border border-white/20">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black tracking-tight">กำหนดวงเงินงบประมาณ (Budget Ceiling)</h3>
                    <p class="text-xs text-blue-100/80 font-medium">ระบุวงเงินงบประมาณรวมแต่ละแหล่งเงิน เพื่อใช้ตัดยอดในการสร้างโครงการใหม่</p>
                </div>
            </div>
            <button type="button" onclick="closeManageBudgetSourcesModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="form-manage-budget-sources" onsubmit="saveBudgetSources(event)" class="p-6 sm:p-7 overflow-y-auto space-y-5 flex-1 bg-slate-50/50">
            
            <!-- Tip Banner -->
            <div class="p-3.5 bg-blue-50/80 rounded-2xl border border-blue-100 flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xs shrink-0 mt-0.5 font-black">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
                <div class="text-xs text-slate-700 leading-relaxed font-medium">
                    <strong class="text-blue-900 font-bold block mb-0.5">หลักการตัดยอดงบประมาณ:</strong>
                    วงเงินที่ระบุนี้ คือ <strong>"งบประมาณรวมทั้งหมดที่ได้รับจัดสรรมา"</strong> ในแต่ละแหล่งเงิน เมื่อมีการเพิ่มโครงการใหม่ ยอดงบโครงการจะถูกนำไปหักออกจากยอดนี้ หากตั้งโครงการเกินวงเงินคงเหลือ ระบบจะแจ้งเตือนและไม่อนุญาตให้บันทึก
                </div>
            </div>

            <!-- ปีงบประมาณ -->
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-700 block">ปีงบประมาณ</span>
                        <span class="text-[11px] text-slate-400">สำหรับรอบการจัดสรรงบประมาณ</span>
                    </div>
                </div>
                <input type="text" id="manage-bs-fiscal-year" value="2569" class="w-24 text-center px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm font-bold rounded-xl focus:bg-white focus:border-blue-500 outline-none">
            </div>

            <!-- Sources List -->
            <div class="space-y-3.5">
                @php
                    $sourcesConfig = [
                        [
                            'key' => 'งบประมาณกลาง',
                            'name' => 'งบประมาณกลาง',
                            'icon' => 'fa-building-columns',
                            'color' => 'blue',
                            'badge' => 'bg-blue-100 text-blue-800',
                            'border' => 'border-blue-200',
                            'default' => 2000000.00
                        ],
                        [
                            'key' => 'งบพัฒนาสถานศึกษา',
                            'name' => 'งบพัฒนาสถานศึกษา',
                            'icon' => 'fa-graduation-cap',
                            'color' => 'emerald',
                            'badge' => 'bg-emerald-100 text-emerald-800',
                            'border' => 'border-emerald-200',
                            'default' => 1000000.00
                        ],
                        [
                            'key' => 'งบเงินรายได้',
                            'name' => 'งบเงินรายได้',
                            'icon' => 'fa-sack-dollar',
                            'color' => 'amber',
                            'badge' => 'bg-amber-100 text-amber-800',
                            'border' => 'border-amber-200',
                            'default' => 500000.00
                        ],
                        [
                            'key' => 'งบอุดหนุน',
                            'name' => 'งบอุดหนุน',
                            'icon' => 'fa-hand-holding-dollar',
                            'color' => 'purple',
                            'badge' => 'bg-purple-100 text-purple-800',
                            'border' => 'border-purple-200',
                            'default' => 500000.00
                        ],
                    ];
                @endphp

                @foreach($sourcesConfig as $idx => $s)
                    @php
                        $curAllocated = $s['default'];
                        if (isset($dbBudgetSources) && $dbBudgetSources->has($s['key'])) {
                            $curAllocated = (float)$dbBudgetSources[$s['key']]->total_allocated;
                        }
                        $matchingProjects = isset($dbRepairProjects) ? $dbRepairProjects->filter(function($p) use ($s) {
                            return trim($p->budget_source ?? 'งบประมาณกลาง') === $s['key'];
                        }) : collect();
                        $inProjectsVal = (float)$matchingProjects->sum('budget');
                        if ($inProjectsVal > $curAllocated && (!isset($dbBudgetSources) || !$dbBudgetSources->has($s['key']))) {
                            $curAllocated = $inProjectsVal;
                        }
                        $remainingVal = max(0, $curAllocated - $inProjectsVal);
                    @endphp
                    <div class="bg-white rounded-2xl p-4 sm:p-4.5 border border-slate-200/90 shadow-2xs hover:border-blue-300 transition space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-{{ $s['color'] }}-50 text-{{ $s['color'] }}-600 flex items-center justify-center text-sm shrink-0">
                                    <i class="fa-solid {{ $s['icon'] }}"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800">{{ $s['name'] }}</h4>
                                    <span class="text-[11px] text-slate-400">โครงการในระบบ: {{ $matchingProjects->count() }} โครงการ</span>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $s['badge'] }}">
                                ตั้งโครงการไปแล้ว ฿{{ number_format($inProjectsVal, 2) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100 items-center">
                            <div>
                                <label class="block text-slate-600 font-bold text-xs mb-1">
                                    เพดานงบประมาณที่ได้รับจัดสรร (บาท) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-xs pointer-events-none">฿</span>
                                    <input type="text" 
                                           id="manage-bs-input-{{ $idx }}" 
                                           data-key="{{ $s['key'] }}"
                                           data-inprojects="{{ $inProjectsVal }}"
                                           value="{{ number_format($curAllocated, 2) }}" 
                                           oninput="formatCurrency(this); recalcManageBudgetSource({{ $idx }});"
                                           class="w-full pl-7 pr-3 py-2 bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm font-black rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition">
                                </div>
                            </div>

                            <div class="bg-slate-50/80 rounded-xl p-2.5 border border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-slate-500">คงเหลือสร้างโครงการได้:</span>
                                <strong id="manage-bs-remaining-{{ $idx }}" class="text-xs sm:text-sm font-black text-emerald-600">
                                    ฿{{ number_format($remainingVal, 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="closeManageBudgetSourcesModal()"
                    class="px-5 py-2.5 text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-100 font-bold text-xs sm:text-sm rounded-xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-md shadow-blue-500/20 cursor-pointer">
                    บันทึกวงเงินงบประมาณ
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Building Modal -->
<div id="modal-add-building" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    เพิ่มอาคาร
                </h3>
            </div>
            <button type="button" onclick="closeBuildingModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form onsubmit="submitAddBuildingForm(event)" class="space-y-4">
            <!-- Field 1: รหัสอาคาร -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    รหัสอาคาร <span class="text-red-500">*</span>
                </label>
                <input type="text" id="building-code-input" placeholder="กรุณาระบุรหัสอาคาร"
                    oninput="if(typeof clearAddBuildingError === 'function') clearAddBuildingError('code')"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="building-code-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุรหัสอาคาร</p>
            </div>

            <!-- Field 2: ชื่ออาคาร -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ชื่ออาคาร <span class="text-red-500">*</span>
                </label>
                <input type="text" id="building-name-input" placeholder="กรุณาระบุชื่ออาคาร"
                    oninput="if(typeof clearAddBuildingError === 'function') clearAddBuildingError('name')"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="building-name-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุชื่ออาคาร</p>
            </div>

            <!-- Field 3: จำนวนชั้น -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    จำนวนชั้น <span class="text-red-500">*</span>
                </label>
                <input type="number" id="building-floors-input" placeholder="กรุณาระบุจำนวนชั้น" min="1"
                    oninput="if(typeof clearAddBuildingError === 'function') clearAddBuildingError('floors')"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="building-floors-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุจำนวนชั้น</p>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeBuildingModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Dept Group Modal -->
<div id="modal-add-dept-group" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    เพิ่มฝ่าย
                </h3>
            </div>
            <button type="button" onclick="closeAddDeptGroupModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form onsubmit="submitAddDeptGroupForm(event)" class="space-y-4">
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ชื่อฝ่าย <span class="text-red-500">*</span>
                </label>
                <input type="text" id="add-dept-group-name-input" placeholder="กรุณาระบุชื่อฝ่าย"
                    oninput="if(typeof clearAddDeptGroupError === 'function') clearAddDeptGroupError()"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="new-dept-group-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุชื่อฝ่าย</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeAddDeptGroupModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Sub Dept Modal -->
<div id="modal-add-sub-dept" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">
                    งาน/แผนก
                </h3>
            </div>
            <button type="button" onclick="closeNewSubDeptModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form onsubmit="saveNewSubDept(event)" class="space-y-4">
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    เลือกฝ่ายหลัก <span class="text-red-500">*</span>
                </label>
                <select id="new-sub-dept-group"
                    onchange="if(typeof clearAddSubDeptError === 'function') clearAddSubDeptError()"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs cursor-pointer appearance-none pr-10">
                    <option value="" disabled selected>-- กรุณาเลือกฝ่ายหลัก --</option>
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
                <p id="add-sub-dept-main-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาเลือกฝ่ายหลัก</p>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">
                    ชื่อแผนก <span class="text-red-500">*</span>
                </label>
                <input type="text" id="new-sub-dept-name" placeholder="กรุณาระบุชื่อแผนก"
                    oninput="if(typeof clearAddSubDeptError === 'function') clearAddSubDeptError()"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="new-sub-dept-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุชื่อแผนก</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeNewSubDeptModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    @php
        $modalBudgetStats = [];
        $defaultAllocations = [
            'งบประมาณกลาง' => 2000000.00,
            'งบพัฒนาสถานศึกษา' => 1000000.00,
            'งบเงินรายได้' => 500000.00,
            'งบอุดหนุน' => 500000.00,
        ];
        $sourceDefs = [
            'งบประมาณกลาง' => ['icon' => 'fa-building-columns', 'color' => 'blue'],
            'งบพัฒนาสถานศึกษา' => ['icon' => 'fa-graduation-cap', 'color' => 'emerald'],
            'งบเงินรายได้' => ['icon' => 'fa-sack-dollar', 'color' => 'amber'],
            'งบอุดหนุน' => ['icon' => 'fa-hand-holding-dollar', 'color' => 'purple'],
        ];
        foreach ($sourceDefs as $k => $def) {
            $matching = isset($dbRepairProjects) ? $dbRepairProjects->filter(function($p) use ($k) {
                return trim($p->budget_source ?? 'งบประมาณกลาง') === $k;
            }) : collect();
            
            $allocatedCeiling = $defaultAllocations[$k] ?? 0.00;
            if (isset($dbBudgetSources) && $dbBudgetSources->has($k)) {
                $allocatedCeiling = (float)$dbBudgetSources[$k]->total_allocated;
            }
            
            $inProjects = (float)$matching->sum('budget');
            $used = (float)$matching->sum('used_budget');
            if ($inProjects > $allocatedCeiling && (!isset($dbBudgetSources) || !$dbBudgetSources->has($k))) {
                $allocatedCeiling = $inProjects;
            }
            
            $remainingForProjects = max(0, $allocatedCeiling - $inProjects);
            $percentAllocated = $allocatedCeiling > 0 ? min(100, round(($inProjects / $allocatedCeiling) * 100)) : 0;

            $modalBudgetStats[$k] = [
                'name' => $k,
                'count' => $matching->count(),
                'total_allocated' => $allocatedCeiling,
                'in_projects' => $inProjects,
                'total_used' => $used,
                'remaining_for_projects' => $remainingForProjects,
                'percent' => $percentAllocated,
                'icon' => $def['icon'],
                'color' => $def['color'],
            ];
        }
    @endphp

    window.budgetSourceStats = @json($modalBudgetStats ?? []);
    window.departmentBudgetStats = @json($departmentBudgetStats ?? []);

    function updateBudgetSourceHelper(sourceKey) {
        // Live card removed
    }

    function checkProjectBudgetLimit() {
        // Live card removed
    }

    function openManageDeptBudgetsModal() {
        if(typeof playBeep==='function') playBeep();
        const modal = document.getElementById('modal-manage-department-budgets');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        const currentTabFY = document.getElementById('repair-project-fiscal-year-filter')?.value;
        const modalFYInput = document.getElementById('table-dept-budget-fiscal-year');
        if (currentTabFY && modalFYInput) {
            if (currentTabFY !== 'all') {
                modalFYInput.value = currentTabFY;
                const disp = document.getElementById('dept-budget-fy-display');
                if (disp) disp.innerText = currentTabFY;
            }
        }

        loadDeptBudgetsTableData();
    }

    function closeManageDeptBudgetsModal() {
        if(typeof playBeep==='function') playBeep();
        const modal = document.getElementById('modal-manage-department-budgets');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        const popup = document.getElementById('dept-budget-year-picker-popup');
        if (popup) popup.classList.add('hidden');
    }

    let currentDeptBudgetStartYear = 2569;

    function renderDeptBudgetYearPicker() {
        const grid = document.getElementById('dept-budget-year-grid');
        const range = document.getElementById('dept-budget-year-range');
        const currentYearVal = document.getElementById('table-dept-budget-fiscal-year')?.value || '2569';
        if (!grid || !range) return;

        grid.innerHTML = '';
        range.innerText = `${currentDeptBudgetStartYear} - ${currentDeptBudgetStartYear + 8}`;

        for (let i = 0; i < 9; i++) {
            const year = currentDeptBudgetStartYear + i;
            const btn = document.createElement('button');
            btn.type = 'button';
            const isSelected = String(year) === String(currentYearVal);

            if (isSelected) {
                btn.className = 'py-2 rounded-xl text-xs font-black bg-blue-600 text-white shadow-xs transition';
            } else {
                btn.className = 'py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 border border-transparent hover:border-blue-200 transition cursor-pointer';
            }

            btn.innerText = year;
            btn.onclick = (e) => {
                e.stopPropagation();
                selectDeptBudgetYear(year);
            };
            grid.appendChild(btn);
        }
    }

    function toggleDeptBudgetYearPicker(e) {
        if (e) e.stopPropagation();
        const popup = document.getElementById('dept-budget-year-picker-popup');
        const chev = document.getElementById('dept-budget-fy-chevron');
        if (!popup) return;

        const isHidden = popup.classList.contains('hidden');
        if (isHidden) {
            const currentVal = parseInt(document.getElementById('table-dept-budget-fiscal-year')?.value) || 2569;
            currentDeptBudgetStartYear = Math.floor((currentVal - 2560) / 9) * 9 + 2560;
            if (currentDeptBudgetStartYear < 2550) currentDeptBudgetStartYear = 2560;

            renderDeptBudgetYearPicker();
            popup.classList.remove('hidden');
            if (chev) chev.classList.add('rotate-180');
        } else {
            popup.classList.add('hidden');
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function changeDeptBudgetYearDecade(amount, e) {
        if (e) e.stopPropagation();
        currentDeptBudgetStartYear += amount;
        renderDeptBudgetYearPicker();
    }

    function selectDeptBudgetYear(year) {
        const fyInput = document.getElementById('table-dept-budget-fiscal-year');
        const fyDisplay = document.getElementById('dept-budget-fy-display');
        const popup = document.getElementById('dept-budget-year-picker-popup');
        const chev = document.getElementById('dept-budget-fy-chevron');

        if (fyInput) fyInput.value = year;
        if (fyDisplay) fyDisplay.innerText = year;
        if (popup) popup.classList.add('hidden');
        if (chev) chev.classList.remove('rotate-180');

        changeDeptBudgetTableFiscalYear(year);
    }

    function selectDeptBudgetTodayYear(e) {
        if (e) e.stopPropagation();
        const thisYear = {{ date('Y') + 543 }};
        currentDeptBudgetStartYear = Math.floor((thisYear - 2560) / 9) * 9 + 2560;
        selectDeptBudgetYear(thisYear);
    }

    document.addEventListener('click', function(e) {
        const popup = document.getElementById('dept-budget-year-picker-popup');
        const btn = document.getElementById('dept-budget-fy-btn');
        if (popup && btn && !popup.contains(e.target) && !btn.contains(e.target)) {
            popup.classList.add('hidden');
            const chev = document.getElementById('dept-budget-fy-chevron');
            if (chev) chev.classList.remove('rotate-180');
        }
    });

    function changeDeptBudgetTableFiscalYear(fy) {
        const fyInput = document.getElementById('table-dept-budget-fiscal-year');
        const fyDisplay = document.getElementById('dept-budget-fy-display');
        if (fyInput && fy) fyInput.value = fy;
        if (fyDisplay && fy) fyDisplay.innerText = fy;
        loadDeptBudgetsTableData(fy);
    }

    function loadDeptBudgetsTableData(targetFY) {
        const fy = targetFY || document.getElementById('table-dept-budget-fiscal-year')?.value || '{{ $selectedFiscalYear ?? (date("Y") + 543) }}';

        if (window.departmentBudgetStats && String(window.departmentBudgetStats.fiscal_year) === String(fy)) {
            renderDeptTableFromStats(window.departmentBudgetStats);
            return;
        }

        fetch(`{{ route("admin.department-budgets.stats") }}?fiscal_year=${encodeURIComponent(fy)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.stats) {
                window.departmentBudgetStats = data.stats;
                renderDeptTableFromStats(data.stats);
            } else {
                recalculateDeptTable();
            }
        })
        .catch(err => {
            console.error('Failed to load dept budget stats:', err);
            recalculateDeptTable();
        });
    }

    function renderDeptTableFromStats(stats) {
        const depts = [
            'ฝ่ายบริหารทรัพยากร',
            'ฝ่ายนโยบายและแผน',
            'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า',
            'ฝ่ายวิชาการ'
        ];

        depts.forEach((deptName, idx) => {
            const input = document.getElementById('dept-table-input-' + idx);
            const usedEl = document.getElementById('dept-table-used-' + idx);
            const remainingEl = document.getElementById('dept-table-remaining-' + idx);
            const subtextEl = document.getElementById('dept-row-subtext-' + idx);
            const percentEl = document.getElementById('dept-table-percent-' + idx);

            let allocated = 0;
            let inProjects = 0;
            let projectCount = 0;

            if (stats && stats.departments && stats.departments[deptName]) {
                const d = stats.departments[deptName];
                allocated = d.total_allocated || 0;
                inProjects = d.total_in_projects || 0;
                projectCount = d.project_count || 0;
            }

            if (input) {
                input.value = Number(allocated).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                input.dataset.inProjects = inProjects;
            }
            if (usedEl) {
                usedEl.innerText = '฿' + Number(inProjects).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            if (subtextEl) {
                subtextEl.innerText = `โครงการซ่อม ${projectCount} รายการ`;
            }

            const remaining = Math.max(0, allocated - inProjects);
            if (remainingEl) {
                remainingEl.innerText = '฿' + Number(remaining).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                remainingEl.className = remaining > 0 ? 'font-black text-emerald-600 text-sm block' : 'font-black text-slate-400 text-sm block';
            }
            if (percentEl) {
                const pct = allocated > 0 ? Math.min(100, Math.round((inProjects / allocated) * 100)) : 0;
                percentEl.innerText = `ใช้ไป ${pct}%`;
            }
        });

        recalculateDeptTable();
    }

    function recalculateDeptTable() {
        const depts = [
            'ฝ่ายบริหารทรัพยากร',
            'ฝ่ายนโยบายและแผน',
            'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า',
            'ฝ่ายวิชาการ'
        ];

        let totalAllocated = 0;
        let totalUsed = 0;

        depts.forEach((deptName, idx) => {
            const input = document.getElementById('dept-table-input-' + idx);
            const remainingEl = document.getElementById('dept-table-remaining-' + idx);
            const percentEl = document.getElementById('dept-table-percent-' + idx);

            const allocated = parseFloat(input ? input.value.replace(/,/g, '') : 0) || 0;
            const inProjects = parseFloat(input?.dataset?.inProjects || 0) || 0;
            const remaining = allocated - inProjects;

            totalAllocated += allocated;
            totalUsed += inProjects;

            if (remainingEl) {
                if (remaining < 0) {
                    remainingEl.className = 'font-black text-rose-600 text-sm block';
                    remainingEl.innerText = '-฿' + Number(Math.abs(remaining)).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else if (remaining > 0) {
                    remainingEl.className = 'font-black text-emerald-600 text-sm block';
                    remainingEl.innerText = '฿' + Number(remaining).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else {
                    remainingEl.className = 'font-black text-slate-400 text-sm block';
                    remainingEl.innerText = '฿0.00';
                }
            }

            if (percentEl) {
                const pct = allocated > 0 ? Math.min(100, Math.round((inProjects / allocated) * 100)) : 0;
                percentEl.innerText = `ใช้ไป ${pct}%`;
            }
        });

        const totalRemaining = Math.max(0, totalAllocated - totalUsed);

        // Update footer
        const footAllocated = document.getElementById('table-footer-total-allocated');
        const footUsed = document.getElementById('table-footer-total-used');
        const footRemaining = document.getElementById('table-footer-total-remaining');

        if (footAllocated) footAllocated.innerText = '฿' + Number(totalAllocated).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (footUsed) footUsed.innerText = '฿' + Number(totalUsed).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (footRemaining) footRemaining.innerText = '฿' + Number(totalRemaining).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Update top summary cards in modal
        const sumAllocated = document.getElementById('modal-summary-total-allocated');
        const sumUsed = document.getElementById('modal-summary-total-used');
        const sumRemaining = document.getElementById('modal-summary-total-remaining');

        if (sumAllocated) sumAllocated.innerText = '฿' + Number(totalAllocated).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (sumUsed) sumUsed.innerText = '฿' + Number(totalUsed).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (sumRemaining) sumRemaining.innerText = '฿' + Number(totalRemaining).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function saveDepartmentBudgetsTable(e) {
        if (e) e.preventDefault();
        if(typeof playBeep==='function') playBeep();

        const fy = document.getElementById('table-dept-budget-fiscal-year')?.value || '{{ $selectedFiscalYear ?? (date("Y") + 543) }}';
        const depts = [
            'ฝ่ายบริหารทรัพยากร',
            'ฝ่ายนโยบายและแผน',
            'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า',
            'ฝ่ายวิชาการ'
        ];

        const deptBudgets = {};
        depts.forEach((deptName, idx) => {
            const input = document.getElementById('dept-table-input-' + idx);
            deptBudgets[deptName] = parseFloat(input ? input.value.replace(/,/g, '') : 0) || 0;
        });

        Swal.fire({
            title: 'กำลังบันทึกงบประมาณ...',
            text: 'กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch('{{ route("admin.department-budgets.update") }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                fiscal_year: fy,
                dept_budgets: deptBudgets
            })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                window.departmentBudgetStats = data.stats;
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกสำเร็จ',
                    text: data.message || 'บันทึกการจัดสรรงบประมาณเรียบร้อยแล้ว',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    closeManageDeptBudgetsModal();
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', 'tab-repair-projects');
                    url.searchParams.set('fiscal_year', fy);
                    window.location.href = url.toString();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล',
                    confirmButtonText: 'ตกลง'
                });
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: err.message || 'เกิดข้อผิดพลาดที่ไม่รู้จัก',
                confirmButtonText: 'ตกลง'
            });
            console.error('Error:', err);
        });
    }

    let allRepairProjectOwnersCache = null;
    let allBrokenRepairProjectOwnersCache = null;

    function initRepairProjectOwners() {
        if (!allRepairProjectOwnersCache) {
            const ownerSelect = document.getElementById('new-repair-project-owner');
            if (ownerSelect) {
                allRepairProjectOwnersCache = [];
                const opts = ownerSelect.querySelectorAll('option');
                opts.forEach(opt => {
                    if (opt.value) {
                        allRepairProjectOwnersCache.push({
                            value: opt.value,
                            text: opt.textContent.trim(),
                            department: opt.getAttribute('data-department') || ''
                        });
                    }
                });
            }
        }

        if (!allBrokenRepairProjectOwnersCache) {
            const brokenOwnerSelect = document.getElementById('broken-repair-project-owner');
            if (brokenOwnerSelect) {
                allBrokenRepairProjectOwnersCache = [];
                const opts = brokenOwnerSelect.querySelectorAll('option');
                opts.forEach(opt => {
                    if (opt.value) {
                        allBrokenRepairProjectOwnersCache.push({
                            value: opt.value,
                            text: opt.textContent.trim(),
                            department: opt.getAttribute('data-department') || ''
                        });
                    }
                });
            }
        }
    }

    function updateProjectOwnersByDepartment(preferredOwner = null) {
        initRepairProjectOwners();
        const deptSelect = document.getElementById('new-repair-project-department');
        const ownerSelect = document.getElementById('new-repair-project-owner');
        if (!deptSelect || !ownerSelect || !allRepairProjectOwnersCache) return;

        const selectedDept = deptSelect.value.trim();
        const currentVal = preferredOwner !== null ? preferredOwner : ownerSelect.value;

        ownerSelect.innerHTML = '';

        if (!selectedDept) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '-- โปรดเลือกฝ่ายก่อน --';
            ownerSelect.appendChild(opt);
            ownerSelect.value = '';
            return;
        }

        const matching = allRepairProjectOwnersCache.filter(item => item.department === selectedDept);

        if (matching.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = `-- ไม่พบบุคลากรใน${selectedDept} --`;
            ownerSelect.appendChild(opt);
            ownerSelect.value = '';
            return;
        }

        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = `-- เลือกผู้รับผิดชอบ (${matching.length} ท่าน) --`;
        ownerSelect.appendChild(defaultOpt);

        let hasSelected = false;
        matching.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.text;
            opt.setAttribute('data-department', item.department);
            if (item.value === currentVal) {
                opt.selected = true;
                hasSelected = true;
            }
            ownerSelect.appendChild(opt);
        });

        if (!hasSelected) {
            ownerSelect.value = '';
        }
    }

    function updateBrokenProjectOwnersByDepartment(preferredOwner = null) {
        initRepairProjectOwners();
        const deptSelect = document.getElementById('broken-repair-project-department');
        const ownerSelect = document.getElementById('broken-repair-project-owner');
        if (!deptSelect || !ownerSelect || !allBrokenRepairProjectOwnersCache) return;

        const selectedDept = deptSelect.value.trim();
        const currentVal = preferredOwner !== null ? preferredOwner : ownerSelect.value;

        ownerSelect.innerHTML = '';

        if (!selectedDept) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '-- โปรดเลือกฝ่ายก่อน --';
            ownerSelect.appendChild(opt);
            ownerSelect.value = '';
            return;
        }

        const matching = allBrokenRepairProjectOwnersCache.filter(item => item.department === selectedDept);

        if (matching.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = `-- ไม่พบบุคลากรใน${selectedDept} --`;
            ownerSelect.appendChild(opt);
            ownerSelect.value = '';
            return;
        }

        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = `-- เลือกผู้รับผิดชอบ (${matching.length} ท่าน) --`;
        ownerSelect.appendChild(defaultOpt);

        let hasSelected = false;
        matching.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.text;
            opt.setAttribute('data-department', item.department);
            if (item.value === currentVal) {
                opt.selected = true;
                hasSelected = true;
            }
            ownerSelect.appendChild(opt);
        });

        if (!hasSelected) {
            ownerSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initRepairProjectOwners();
        updateProjectOwnersByDepartment();
        updateBrokenProjectOwnersByDepartment();
    });

    function openAddRepairProjectModal() {
        if(typeof playBeep==='function') playBeep();
        const modal = document.getElementById('modal-add-repair-project');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        // กำหนดปีงบประมาณเริ่มต้นตามปีที่เลือกอยู่บนหน้าเว็บ (งบประมาณใช้เฉพาะปีต่อปี)
        const currentTabFY = document.getElementById('repair-project-fiscal-year-filter')?.value;
        const fyInput = document.getElementById('new-repair-project-fiscal-year');
        if (fyInput) {
            if (currentTabFY && currentTabFY !== 'all') {
                fyInput.value = currentTabFY;
            } else if (!fyInput.value) {
                fyInput.value = '{{ $selectedFiscalYear ?? (date("Y") + 543) }}';
            }
            clearError('new-repair-project-fiscal-year');
        }

        const bSource = document.getElementById('new-repair-project-budget-source');
        updateBudgetSourceHelper(bSource ? bSource.value : 'งบประมาณกลาง');

        // กรองรายชื่อผู้รับผิดชอบตามฝ่ายที่เลือก
        updateProjectOwnersByDepartment();
    }
    
          function clearError(id) {
          document.getElementById(id).classList.remove('border-red-500');
          document.getElementById('err-' + id).classList.add('hidden');
      }

      function closeAddRepairProjectModal() {
        if(typeof playBeep==='function') playBeep();
        const modal = document.getElementById('modal-add-repair-project');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        document.getElementById('new-repair-project-code').value = '';
        document.getElementById('new-repair-project-name').value = '';
        document.getElementById('new-repair-project-department').value = '';
        document.getElementById('new-repair-project-owner').value = '';
        updateProjectOwnersByDepartment();
        const bSource = document.getElementById('new-repair-project-budget-source');
        if (bSource) bSource.value = 'งบประมาณกลาง';
        document.getElementById('new-repair-project-budget').value = '';
        document.getElementById('new-repair-project-fiscal-year').value = '';
        const bDesc = document.getElementById('new-repair-project-description');
        if (bDesc) bDesc.value = '';
    }
    
      function formatCurrency(input) {
          // ลบทุกอย่างที่ไม่ใช่ตัวเลขและจุด
          let val = input.value.replace(/[^0-9.]/g, '');
          
          // จัดการจุดทศนิยมให้มีได้แค่ 1 ตัว
          let parts = val.split('.');
          if (parts.length > 2) {
              parts = [parts[0], parts.slice(1).join('')];
          }
          
          // จุดทศนิยม ให้เป็น 2หลักเท่านั้น (จำกัดตอนพิมพ์)
          if (parts[1] !== undefined) {
              parts[1] = parts[1].substring(0, 2);
          }
          
          // ใส่ลูกน้ำสำหรับหลักพัน
          if (parts[0]) {
              parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
          }
          
          input.value = parts.join('.');
      }

      // บังคับให้เป็น 2 หลักเสมอเมื่อคลิกออก (onblur) สำหรับทุก input ที่ใช้ formatCurrency
      document.addEventListener('blur', function(e) {
          if (e.target && e.target.tagName === 'INPUT' && e.target.hasAttribute('oninput') && e.target.getAttribute('oninput').includes('formatCurrency(this)')) {
              let val = e.target.value.replace(/,/g, '');
              let num = parseFloat(val);
              if (!isNaN(num)) {
                  e.target.value = num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
              } else if (e.target.value !== '') {
                  e.target.value = '0.00';
              }
              
              // trigger oninput manually to update any recalculations
              if (e.target.value !== '') {
                  let event = new Event('input', { bubbles: true });
                  // We temporarily remove formatCurrency from oninput to prevent it from re-stripping '.00' formatting if it conflicts, but our formatCurrency handles commas fine.
                  // Actually, recalculations usually read the value directly. Let's just call recalculate functions if they exist in the oninput string.
                  if (e.target.getAttribute('oninput').includes('recalculateDeptTable')) {
                      if (typeof recalculateDeptTable === 'function') recalculateDeptTable();
                  }
                  if (e.target.getAttribute('oninput').includes('recalcManageBudgetSource')) {
                      // We can just dispatch event
                      e.target.dispatchEvent(event);
                  }
              }
          }
      }, true);

      function saveNewRepairProject() {
          if(typeof playBeep==='function') playBeep();
          const code = document.getElementById('new-repair-project-code').value;
          const name = document.getElementById('new-repair-project-name').value;
          const dept = document.getElementById('new-repair-project-department').value;
          const owner = document.getElementById('new-repair-project-owner').value;
          const budget_source = document.getElementById('new-repair-project-budget-source') ? document.getElementById('new-repair-project-budget-source').value : 'งบประมาณกลาง';
          let budget = document.getElementById('new-repair-project-budget').value;
          budget = budget.replace(/,/g, ''); // นำคอมม่าออกก่อนตรวจสอบและบันทึก
          const fiscal_year = document.getElementById('new-repair-project-fiscal-year').value;
          const description = document.getElementById('new-repair-project-description') ? document.getElementById('new-repair-project-description').value : '';

          // Clear all previous errors first
          clearError('new-repair-project-code');
          clearError('new-repair-project-name');
          clearError('new-repair-project-department');
          clearError('new-repair-project-owner');
          clearError('new-repair-project-budget-source');
          clearError('new-repair-project-budget');
          clearError('new-repair-project-fiscal-year');

          // Reset the code error text in case it was changed to "Duplicate" earlier
          document.getElementById('err-new-repair-project-code').textContent = 'กรุณากรอกรหัสโครงการ';

          let hasError = false;
          const validateField = (val, id) => {
              if (hasError) return;
              if (!val.trim()) {
                  document.getElementById(id).classList.add('border-red-500');
                  document.getElementById('err-' + id).classList.remove('hidden');
                  document.getElementById(id).focus();
                  hasError = true;
              }
          };

          validateField(code, 'new-repair-project-code');
          validateField(name, 'new-repair-project-name');
          validateField(dept, 'new-repair-project-department');
          validateField(owner, 'new-repair-project-owner');
          validateField(budget_source, 'new-repair-project-budget-source');
          validateField(budget, 'new-repair-project-budget');
          validateField(fiscal_year, 'new-repair-project-fiscal-year');

          if(hasError) {
              return;
          }

          Swal.fire({
              title: 'กำลังบันทึกข้อมูล...',
              text: 'กรุณารอสักครู่',
              allowOutsideClick: false,
              didOpen: () => {
                  Swal.showLoading();
              }
          });

          fetch(window.BASE_URL + '/admin/repair-projects', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                  project_code: code,
                  name: name,
                  department: dept,
                  responsible_person: owner,
                  budget: budget,
                  fiscal_year: fiscal_year,
                  budget_source: budget_source,
                  description: description
              })
          })
          .then(async response => {
              const data = await response.json();
              if(!response.ok) {
                  if(response.status === 422) {
                      if (data.errors && data.errors.project_code) {
                          // Found duplicate code error
                          const codeInput = document.getElementById('new-repair-project-code');
                          const errCode = document.getElementById('err-new-repair-project-code');
                          codeInput.classList.add('border-red-500');
                          errCode.textContent = 'มีรหัสโครงการนี้อยู่ในระบบแล้ว';
                          errCode.classList.remove('hidden');
                          codeInput.focus();
                          throw new Error('DUPLICATE_CODE');
                      }
                      let errors = Object.values(data.errors).flat().join('\n');
                      throw new Error(errors);
                  }
                  throw new Error(data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
              }
              return data;
          })
          .then(data => {
              if(data.success) {
                  Swal.fire({
                      icon: 'success',
                      title: 'สำเร็จ',
                      text: 'เพิ่มโครงการใหม่เรียบร้อยแล้ว',
                      showConfirmButton: false,
                      timer: 1500
                  }).then(() => {
                      closeAddRepairProjectModal();
                      window.location.reload();
                  });
              } else {
                  Swal.fire({
                      icon: 'error',
                      title: 'เกิดข้อผิดพลาด',
                      text: data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล',
                      confirmButtonText: 'ตกลง'
                  });
              }
          })
          .catch(error => {
              if (error.message === 'DUPLICATE_CODE') {
                  Swal.close(); // Just close the loading popup
                  return;
              }
              Swal.fire({
                  icon: 'error',
                  title: 'เกิดข้อผิดพลาด',
                  text: error.message || 'เกิดข้อผิดพลาดที่ไม่รู้จัก',
                  confirmButtonText: 'ตกลง'
              });
              console.error('Error:', error);
          });
      }



</script>

<!-- Edit Building Modal -->
<div id="modal-edit-building" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800">แก้ไขอาคาร</h3>
            </div>
            <button type="button" onclick="closeEditBuildingModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="submitEditBuildingForm(event)" class="space-y-4">
            <input type="hidden" id="edit-building-old-code">
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">รหัสอาคาร <span class="text-red-500">*</span></label>
                <input type="text" id="edit-building-code-input" placeholder="กรุณาระบุรหัสอาคาร"
                    oninput="if(typeof clearEditBuildingError === 'function') clearEditBuildingError('code')"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="edit-building-code-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุรหัสอาคาร</p>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">ชื่ออาคาร <span class="text-red-500">*</span></label>
                <input type="text" id="edit-building-name-input" placeholder="กรุณาระบุชื่ออาคาร"
                    oninput="if(typeof clearEditBuildingError === 'function') clearEditBuildingError('name')"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="edit-building-name-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุชื่ออาคาร</p>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">จำนวนชั้น <span class="text-red-500">*</span></label>
                <input type="number" id="edit-building-floors-input" placeholder="กรุณาระบุจำนวนชั้น" min="1"
                    oninput="if(typeof clearEditBuildingError === 'function') clearEditBuildingError('floors')"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="edit-building-floors-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุจำนวนชั้น</p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeEditBuildingModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Building Details Modal -->
<div id="modal-building-details" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[70] p-4" style="display: none;">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200 overflow-hidden max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h3 id="building-details-title" class="text-base sm:text-lg font-bold text-slate-800">รายละเอียดอาคาร</h3>
                    <p id="building-details-subtitle" class="text-xs text-slate-500 font-semibold mt-0.5"></p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-building-details').style.display='none'" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Filter & Add Row -->
        <div class="flex items-center justify-between gap-3 px-6 pt-4 pb-2 flex-shrink-0">
            <select id="building-details-floor-select" onchange="updateBuildingRoomsTable()"
                class="px-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl focus:outline-none focus:border-blue-500 cursor-pointer">
            </select>
            <button type="button" onclick="openAddRoomModal()"
                class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl flex items-center gap-1.5 transition shadow-sm cursor-pointer">
                <i class="fa-solid fa-plus"></i> <span>เพิ่มห้อง</span>
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-y-auto flex-1 px-6 pb-6">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold text-xs uppercase tracking-wide">
                        <th class="p-3 text-center w-10">ลำดับ</th>
                        <th class="p-3 text-left">รหัสห้อง</th>
                        <th class="p-3 text-left">ชื่อห้อง</th>
                        <th class="p-3 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="building-rooms-table-body">
                    <tr><td colspan="4" class="p-6 text-center text-slate-400 font-bold">ไม่มีข้อมูลห้องในชั้นนี้</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Manage Room Modal (Add/Edit) -->
<div id="modal-manage-room" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-[80] p-4" style="display: none;">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 md:p-7 shadow-2xl space-y-5 border border-slate-100 relative animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center text-base font-bold shadow-xs">
                    <i class="fa-solid fa-door-open text-blue-600"></i>
                </div>
                <h3 id="manage-room-title" class="text-base sm:text-lg font-bold text-slate-800">เพิ่มห้อง</h3>
            </div>
            <button type="button" onclick="closeManageRoomModal()" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="submitManageRoomForm(event)" class="space-y-4">
            <input type="hidden" id="manage-room-mode" value="add">
            <input type="hidden" id="manage-room-old-code" value="">
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">รหัสห้อง <span class="text-red-500">*</span></label>
                <input type="text" id="manage-room-code-input" placeholder="กรุณาระบุรหัสห้อง"
                    oninput="this.classList.remove('border-red-500', 'focus:border-red-500'); this.classList.add('border-slate-200', 'focus:border-blue-500'); document.getElementById('manage-room-code-error')?.classList.add('hidden');"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="manage-room-code-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุรหัสห้อง</p>
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1.5">ชื่อห้อง <span class="text-red-500">*</span></label>
                <input type="text" id="manage-room-name-input" placeholder="กรุณาระบุชื่อห้อง"
                    oninput="this.classList.remove('border-red-500', 'focus:border-red-500'); this.classList.add('border-slate-200', 'focus:border-blue-500'); document.getElementById('manage-room-name-error')?.classList.add('hidden');"
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-blue-500 shadow-xs placeholder:text-slate-300 transition-colors">
                <p id="manage-room-name-error" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุชื่อห้อง</p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closeManageRoomModal()"
                    class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-blue-200">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal ดูรายละเอียดโครงการซ่อมบำรุง -->
<div id="modal-view-repair-project" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[70] flex items-center justify-center p-4">
    <div class="bg-white max-w-2xl w-full rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 border border-slate-100 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between text-white shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-lg font-bold shadow-inner">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div>
                    <h4 class="font-black text-base" id="vp-name">รายละเอียดโครงการซ่อมบำรุง</h4>
                    <p class="text-blue-100 text-xs font-semibold mt-0.5" id="vp-code">-</p>
                </div>
            </div>
            <button type="button" onclick="closeViewRepairProjectModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto space-y-5">
            <!-- Project Meta Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px]">ปีงบประมาณ</span>
                    <span id="vp-fiscal" class="font-bold text-slate-700 mt-1 block">-</span>
                </div>
                <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100/80">
                    <span class="text-indigo-500 font-semibold block text-[10px]">แหล่งงบประมาณ</span>
                    <span id="vp-budget-source" class="font-bold text-indigo-700 mt-1 block truncate">งบประมาณกลาง</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px]">หน่วยงานรับผิดชอบ</span>
                    <span id="vp-dept" class="font-bold text-slate-700 mt-1 block line-clamp-1">-</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 font-semibold block text-[10px]">ผู้รับผิดชอบ</span>
                    <span id="vp-owner" class="font-bold text-slate-700 mt-1 block line-clamp-1">-</span>
                </div>
                <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                    <span class="text-blue-500 font-semibold block text-[10px]">งบประมาณรวม</span>
                    <span id="vp-budget" class="font-black text-blue-700 mt-1 block">-</span>
                </div>
            </div>

            <!-- Project Description / Note -->
            <div id="vp-desc-container" class="p-3 bg-slate-50/70 rounded-xl border border-slate-100 text-xs">
                <span class="text-slate-400 font-semibold block text-[10px]">หมายเหตุ / รายละเอียดเพิ่มเติม</span>
                <p id="vp-desc" class="font-medium text-slate-700 mt-0.5 whitespace-pre-line">-</p>
            </div>

            <!-- Financial Status Bar -->
            <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 grid grid-cols-2 gap-4 text-center">
                <div class="border-r border-slate-200 pr-4">
                    <span class="text-slate-500 text-xs font-bold block">ใช้ไปแล้ว (บาท)</span>
                    <span id="vp-used" class="text-xl font-black text-amber-600 mt-1 block">-</span>
                </div>
                <div class="pl-4">
                    <span class="text-slate-500 text-xs font-bold block">คงเหลือสุทธิ (บาท)</span>
                    <span id="vp-remaining" class="text-xl font-black text-emerald-600 mt-1 block">-</span>
                </div>
            </div>

            <!-- History of Deductions / Repair Requests -->
            <div class="space-y-3">
                <h5 class="text-xs font-black text-slate-700 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-clock-rotate-left text-blue-500"></i> ประวัติการตัดยอดงบประมาณ / ใบแจ้งซ่อมที่เกี่ยวข้อง
                    </span>
                    <span id="vp-count" class="text-[11px] font-semibold text-slate-400">0 รายการ</span>
                </h5>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-[10px] uppercase font-black text-slate-500 tracking-wider">
                            <tr>
                                <th class="px-3 py-2.5">รหัสแจ้งซ่อม</th>
                                <th class="px-3 py-2.5">ผู้แจ้งซ่อม</th>
                                <th class="px-3 py-2.5">วันที่</th>
                                <th class="px-3 py-2.5">สถานะ</th>
                                <th class="px-3 py-2.5 text-right">งบที่ตัด (บาท)</th>
                            </tr>
                        </thead>
                        <tbody id="vp-requests-tbody" class="divide-y divide-slate-100 bg-white font-medium text-slate-700">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0">
            <button type="button" onclick="closeViewRepairProjectModal()" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                ปิด
            </button>
        </div>
    </div>
</div>

<!-- ป๊อปอัประงับบัญชี (Suspend User Modal) -->
<div id="modal-suspend-user"
    onclick="if(event.target === this) closeSuspendUserModal()"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4 hidden"
    style="display: none;">
    <div class="bg-white max-w-md w-full rounded-3xl border border-slate-100 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h4 class="font-black text-slate-800 text-lg flex items-center gap-2">
                <i class="fa-solid fa-user-lock text-red-500"></i> ระงับการใช้งานบัญชี
            </h4>
            <button type="button" onclick="closeSuspendUserModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="suspend-user-form" class="p-6 space-y-4">
            <input type="hidden" id="suspend-modal-user-id" value="">
            
            <div>
                <label class="block text-xs sm:text-sm text-slate-700 mb-1.5">
                    เหตุผลการระงับ <span class="text-red-500">*</span>
                </label>
                <textarea id="suspend-reason-input" rows="4" placeholder="ระบุเหตุผลที่ต้องการระงับบัญชี..."
                    class="w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-semibold rounded-2xl focus:outline-none focus:border-red-500 shadow-xs placeholder:text-slate-300 resize-none"></textarea>
                <p id="suspend-error-message" class="text-red-500 text-xs mt-1.5 hidden">กรุณาระบุเหตุผลการระงับ</p>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-center gap-3 pt-3">
                <button type="button" onclick="closeSuspendUserModal()"
                    class="w-full py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="w-full py-3 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-red-200">
                    ยืนยัน
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ป๊อปอัปคืนค่าบัญชี (Restore User Modal) -->
<div id="modal-restore-user"
    onclick="if(event.target === this) closeRestoreUserModal()"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[70] flex items-center justify-center p-4 hidden"
    style="display: none;">
    <div class="bg-white max-w-md w-full rounded-3xl border border-slate-100 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h4 class="font-black text-slate-800 text-lg flex items-center gap-2">
                <i class="fa-solid fa-rotate-left text-emerald-500"></i> คืนค่าการใช้งานบัญชี
            </h4>
            <button type="button" onclick="closeRestoreUserModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <input type="hidden" id="restore-modal-user-id" value="">
            <p class="text-sm font-semibold text-slate-600">คุณต้องการคืนค่าบัญชีนี้ให้กลับมาใช้งานได้ตามปกติใช่หรือไม่?</p>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-center gap-3 pt-3">
                <button type="button" onclick="closeRestoreUserModal()"
                    class="w-full py-3 bg-slate-50 hover:bg-slate-100 text-slate-700 text-sm font-bold rounded-2xl transition cursor-pointer shadow-xs">
                    ยกเลิก
                </button>
                <button type="button" onclick="confirmRestoreUser()"
                    class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-2xl transition cursor-pointer shadow-md shadow-emerald-200">
                    คืนค่า
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openViewRepairProjectModal(p) {
    if (!p) return;
    document.getElementById('vp-name').innerText = p.name || '-';
    document.getElementById('vp-code').innerText = 'รหัสโครงการ: ' + (p.code || '-');
    document.getElementById('vp-fiscal').innerText = p.fiscal || '-';
    document.getElementById('vp-budget-source').innerText = p.budget_source || 'งบประมาณกลาง';
    document.getElementById('vp-desc').innerText = p.description || '-';
    document.getElementById('vp-dept').innerText = p.dept || '-';
    document.getElementById('vp-owner').innerText = p.owner || '-';
    document.getElementById('vp-budget').innerText = (parseFloat(p.budget) || 0).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';
    document.getElementById('vp-used').innerText = (parseFloat(p.used) || 0).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';
    document.getElementById('vp-remaining').innerText = (parseFloat(p.remaining) || 0).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';

    const tbody = document.getElementById('vp-requests-tbody');
    tbody.innerHTML = '';
    const reqs = p.requests || [];
    document.getElementById('vp-count').innerText = reqs.length + ' รายการ';

    if (reqs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-400 font-medium">ยังไม่มีรายการแจ้งซ่อมที่ตัดงบประมาณจากโครงการนี้</td></tr>';
    } else {
        reqs.forEach((r, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition';
            tr.innerHTML = `
                <td class="px-3 py-2.5 font-bold text-blue-600 font-mono">${r.request_code}</td>
                <td class="px-3 py-2.5 font-bold text-slate-800">${r.reporter}</td>
                <td class="px-3 py-2.5 text-slate-500">${r.date}</td>
                <td class="px-3 py-2.5"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600">${r.status}</span></td>
                <td class="px-3 py-2.5 text-right font-black text-amber-600">${(parseFloat(r.budget) || 0).toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    const modal = document.getElementById('modal-view-repair-project');
    if (modal) modal.classList.remove('hidden');
}

function closeViewRepairProjectModal() {
    const modal = document.getElementById('modal-view-repair-project');
    if (modal) modal.classList.add('hidden');
}
</script>
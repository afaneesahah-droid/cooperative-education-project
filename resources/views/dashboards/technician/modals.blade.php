<!-- Hidden Logout Form (POST + CSRF) -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- Logout Confirmation Modal -->
<div id="logout-modal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl border border-slate-100 text-center">
        <div class="inline-flex bg-blue-50 text-blue-600 p-4 rounded-2xl mb-4">
            <i class="fa-solid fa-right-from-bracket text-3xl"></i>
        </div>
        <h3 class="text-xl font-black text-slate-900">ออกจากระบบ?</h3>
        <p class="text-sm text-slate-500 mt-2 font-medium">คุณต้องการออกจากระบบหรือไม่?</p>
        <div class="flex gap-3 mt-6">
            <button onclick="closeLogoutModal()"
                class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-2xl transition cursor-pointer">
                ยกเลิก
            </button>
            <button onclick="document.getElementById('logout-form').submit()"
                class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-2xl transition shadow-md shadow-blue-200 cursor-pointer">
                <i class="fa-solid fa-right-from-bracket mr-1"></i> ออกจากระบบ
            </button>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div id="modal-technician-profile" onclick="if(event.target === this) closeProfileModal()"
    class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
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
            <button type="button" onclick="closeProfileModal()" class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-5 sm:p-6 overflow-y-auto flex-1 custom-scrollbar">
            {{-- Alert: Success --}}
            @if(session('profile_success'))
                <div class="mb-4 flex items-center gap-3 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-3 rounded-2xl text-sm font-bold">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <span>{{ session('profile_success') }}</span>
                </div>
            @endif

            {{-- Alert: Error --}}
            @if($errors->any())
                <div class="mb-4 flex items-center gap-3 bg-red-50 text-red-700 border border-red-200 px-4 py-3 rounded-2xl text-sm font-bold">
                    <i class="fa-solid fa-circle-xmark text-lg"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form id="technician-profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="prefix" value="{{ $user->prefix ?? '' }}">
                <input type="hidden" name="first_name" value="{{ $user->first_name ?? '' }}">
                <input type="hidden" name="last_name" value="{{ $user->last_name ?? '' }}">
                <input type="hidden" name="first_name_en" value="{{ $user->first_name_en ?? '' }}">
                <input type="hidden" name="last_name_en" value="{{ $user->last_name_en ?? '' }}">
                <input type="hidden" name="citizen_id" value="{{ $user->citizen_id ?? '' }}">
                <input type="hidden" name="gender" value="{{ $user->gender ?? '' }}">
                <input type="hidden" name="department" value="{{ $user->department ?? '' }}">
                <input type="hidden" name="position" value="{{ $user->position ?? '' }}">
                
                <!-- ข้อมูลส่วนตัว -->
                <div>
                    <h5 class="font-black text-slate-800 mb-3 text-xs sm:text-sm flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-blue-500 text-xs"></i>
                        ข้อมูลส่วนตัว
                    </h5>
                    <div class="flex flex-col md:flex-row gap-5">
                        <!-- Left Column (Image Upload) -->
                        <div class="w-full md:w-52 shrink-0 flex flex-col items-center md:items-start">
                            <div class="w-40 h-40 sm:w-44 sm:h-44 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden relative group bg-slate-50 flex items-center justify-center shadow-inner cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                                <input type="file" name="profile_photo" id="profile_photo_input" onchange="previewPhoto(event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" accept="image/jpeg,image/png,image/webp">
                                
                                <!-- Default Upload Content -->
                                <div id="avatar-placeholder" class="flex flex-col items-center w-full h-full justify-center p-3 text-center @if(isset($user->profile_photo) && $user->profile_photo) hidden @endif">
                                    <div class="w-10 h-10 bg-slate-200 text-slate-400 rounded-xl flex items-center justify-center mb-2 group-hover:bg-blue-100 group-hover:text-blue-500 transition">
                                        <i class="fa-solid fa-user text-base"></i>
                                    </div>
                                    <h6 class="font-bold text-slate-700 text-xs mb-0.5 group-hover:text-blue-600 transition">อัปโหลดรูปโปรไฟล์</h6>
                                    <p class="text-[10px] text-slate-400">ขนาดไฟล์ไม่เกิน 2 MB</p>
                                    <p class="text-[10px] text-slate-400">รองรับไฟล์ JPG, PNG</p>
                                </div>
                                
                                <!-- Preview Image Content -->
                                <div id="avatar-preview-container" class="absolute inset-0 @if(!isset($user->profile_photo) || !$user->profile_photo) hidden @endif">
                                    <img id="avatar-preview" src="{{ (isset($user->profile_photo) && $user->profile_photo) ? asset('uploads/profiles/' . $user->profile_photo) : '' }}" class="w-full h-full object-cover">
                                    <!-- Overlay on hover -->
                                    <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition z-10 pointer-events-none">
                                        <i class="fa-solid fa-camera text-xl mb-1"></i>
                                        <span class="text-xs font-bold">เปลี่ยนรูป</span>
                                    </div>
                                </div>
                            </div>
                            @error('profile_photo')
                                <p class="text-red-500 text-[10px] mt-1.5 font-bold text-center"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Right Column (Form Fields) -->
                        <div class="flex-1 space-y-3.5">
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-1.5">คำนำหน้า <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm appearance-none cursor-not-allowed opacity-80">
                                        <option value="" {{ ($user->prefix ?? '') === '' ? 'selected' : '' }}>-- เลือกคำนำหน้า --</option>
                                        <option value="นาย" {{ ($user->prefix ?? '') == 'นาย' ? 'selected' : '' }}>นาย</option>
                                        <option value="นาง" {{ ($user->prefix ?? '') == 'นาง' ? 'selected' : '' }}>นาง</option>
                                        <option value="นางสาว" {{ ($user->prefix ?? '') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Names (TH) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">ชื่อ (ภาษาไทย) <span class="text-red-500">*</span></label>
                                    <input type="text" value="{{ $user->first_name ?? explode(' ', $user->name, 2)[0] }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80">
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">นามสกุล (ภาษาไทย) <span class="text-red-500">*</span></label>
                                    <input type="text" value="{{ $user->last_name ?? (explode(' ', $user->name, 2)[1] ?? '') }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80">
                                </div>
                            </div>
                            
                            <!-- Names (EN) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">ชื่อ (ภาษาอังกฤษ)</label>
                                    <input type="text" value="{{ $user->first_name_en ?? '' }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80">
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-xs font-bold mb-1.5">นามสกุล (ภาษาอังกฤษ)</label>
                                    <input type="text" value="{{ $user->last_name_en ?? '' }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80">
                                </div>
                            </div>
                            
                            <!-- Citizen ID -->
                            <div>
                                <label class="block text-slate-700 text-xs font-bold mb-1.5">เลขบัตรประชาชน <span class="text-red-500">*</span></label>
                                <input type="text" value="{{ $user->citizen_id ?? '' }}" placeholder="เลขบัตรประชาชน 13 หลัก" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-800 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลการทำงาน -->
                @php
                    $userPos = $user->position ?? (Auth::user()?->position ?? null);
                    $userDept = $user->department ?? (Auth::user()?->department ?? null);
                    
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
                            $displayGroup = Auth::user()?->department_group ?: ($user->department_group ?? '-');
                        }
                    }

                    $roles = explode(',', Auth::user()?->role ?? ($user->role ?? ''));
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
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">เบอร์โทรศัพท์ <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" id="phone_input_modal" maxlength="12" oninput="formatPhoneNumber(this)" placeholder="เช่น 084-567-8901" value="{{ $user->phone ?? '' }}" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">อีเมล <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ $user->email ?? '' }}" placeholder="เช่น user@ytc.ac.th" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
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
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">ชื่อผู้ใช้ (Username) <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $user->username ?? '' }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-xs sm:text-sm cursor-not-allowed opacity-80">
                        </div>
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">รหัสผ่านเดิม</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="profile-current-password" placeholder="กรอกรหัสผ่านเดิม (ถ้าต้องการเปลี่ยน)" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                                <button type="button" onclick="togglePasswordVisibility('profile-current-password', 'profile-toggle-icon-current')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <i class="fa-solid fa-eye text-xs" id="profile-toggle-icon-current"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Passwords -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mt-3">
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">รหัสผ่านใหม่</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="profile-password" placeholder="รหัสผ่านใหม่ (ถ้าไม่เปลี่ยนให้เว้นว่าง)" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                                <button type="button" onclick="togglePasswordVisibility('profile-password', 'profile-toggle-icon-new')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <i class="fa-solid fa-eye text-xs" id="profile-toggle-icon-new"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-slate-700 text-xs font-bold mb-1.5">ยืนยันรหัสผ่านใหม่</label>
                            <div class="relative">
                                <input type="password" name="new_password_confirmation" id="profile-password-confirm" placeholder="ยืนยันรหัสผ่านใหม่" class="w-full pl-3.5 pr-10 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-semibold text-xs sm:text-sm">
                                <button type="button" onclick="togglePasswordVisibility('profile-password-confirm', 'profile-toggle-icon-confirm')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <i class="fa-solid fa-eye text-xs" id="profile-toggle-icon-confirm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="px-5 sm:px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
            <button type="button" onclick="closeProfileModal()" class="px-5 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 rounded-xl font-bold text-xs sm:text-sm transition cursor-pointer">
                ยกเลิก
            </button>
            <button type="submit" form="technician-profile-form" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl transition shadow-md shadow-blue-200 hover:shadow-lg cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-check text-xs"></i>
                บันทึก
            </button>
        </div>
    </div>
</div>
<script>
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('avatar-preview');
            let container = document.getElementById('avatar-preview-container');
            let placeholder = document.getElementById('avatar-placeholder');
            if (img) img.src = e.target.result;
            if (container) container.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<!-- Create Budget Request Modal -->
<div id="create-budget-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-white max-w-5xl w-full rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5 relative overflow-hidden shrink-0">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
            <div class="relative z-10 flex justify-between items-center">
                <h2 class="text-xl font-black text-white flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-indigo-100"></i> บันทึกค่าซ่อม
                </h2>
                <button type="button" onclick="closeCreateBudgetModal()" class="text-white hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Body -->
        <div class="overflow-y-auto">
            <form id="budget-request-form" action="" method="POST" class="w-full">
                @csrf
                @method('POST')
                <input type="hidden" id="budget_repair_request_id" name="repair_request_id" value="">
                <input type="hidden" id="budget_amount_total_hidden" name="budget_amount_total" value="0">
                <div class="p-6 space-y-5">
                    <!-- Row 1: โครงการ etc. -->
                    @php
                        $availableProjects = \App\Models\RepairProject::all();
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4">
                        <!-- ชื่อโครงการ -->
                        <div class="lg:col-span-2">
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs">ชื่อโครงการ <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="budget_project_id" name="project_id" onchange="handleProjectChange(this)" class="w-full px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl focus:outline-none focus:border-blue-500 transition font-semibold text-sm shadow-sm appearance-none">
                                    <option value="">-- เลือกโครงการ --</option>
                                    @foreach($availableProjects as $p)
                                        <option value="{{ $p->id }}" 
                                                data-code="{{ $p->project_code }}" 
                                                data-dept="{{ $p->department }}"
                                                data-fiscal="{{ $p->fiscal_year }}"
                                                data-budget="{{ $p->budget }}"
                                                data-used="{{ $p->used_budget ?? 0 }}">
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <!-- รหัสโครงการ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs">รหัสโครงการ</label>
                            <input type="text" id="budget_project_code" name="project_code" readonly placeholder="-" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-sm shadow-sm cursor-not-allowed">
                        </div>
                        <!-- ปีงบประมาณ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs">ปีงบประมาณ</label>
                            <input type="text" id="budget_fiscal_year" name="fiscal_year" readonly placeholder="-" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-sm shadow-sm cursor-not-allowed">
                        </div>
                        <!-- หน่วยงานที่รับผิดชอบ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs">หน่วยงานที่รับผิดชอบ</label>
                            <input type="text" id="budget_department" name="department" readonly placeholder="-" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-sm shadow-sm cursor-not-allowed">
                        </div>
                        <!-- งบประมาณ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs">งบประมาณ (บาท)</label>
                            <input type="text" id="budget_amount" name="budget" readonly placeholder="0.00" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-sm shadow-sm text-right cursor-not-allowed">
                        </div>
                        <!-- คงเหลือ -->
                        <div>
                            <label class="block text-slate-700 mb-1.5 font-bold text-xs">คงเหลือ (บาท)</label>
                            <input type="text" id="budget_remaining" name="remaining" readonly placeholder="0.00" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none transition font-semibold text-sm shadow-sm text-right cursor-not-allowed">
                        </div>
                    </div>
                    
                    <script>
                        function handleProjectChange(select) {
                            const selected = select.options[select.selectedIndex];
                            const codeInput = document.getElementById("budget_project_code");
                            const deptInput = document.getElementById("budget_department");
                            const amountInput = document.getElementById("budget_amount");
                            const remainingInput = document.getElementById("budget_remaining");
                            const fiscalInput = document.getElementById("budget_fiscal_year");
                            
                            if (selected && selected.value !== "") {
                                codeInput.value = selected.getAttribute("data-code") || "-";
                                deptInput.value = selected.getAttribute("data-dept") || "-";
                                fiscalInput.value = selected.getAttribute("data-fiscal") || "-";
                                
                                const budgetRaw = parseFloat(selected.getAttribute("data-budget") || 0);
                                const usedRaw = parseFloat(selected.getAttribute("data-used") || 0);
                                const remaining = budgetRaw - usedRaw;
                                
                                amountInput.value = budgetRaw.toLocaleString("th-TH", {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                remainingInput.value = remaining.toLocaleString("th-TH", {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            } else {
                                codeInput.value = "";
                                deptInput.value = "";
                                fiscalInput.value = "";
                                amountInput.value = "";
                                remainingInput.value = "";
                            }
                        }
                    </script>
                </div>

                <div class="px-6 pb-6 space-y-5">
                    <!-- Row 2: รายการที่ขอเบิก -->
                    <div>
                        <h5 class="font-bold text-slate-800 mb-3 text-sm">รายการที่ขอเบิก</h5>
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse whitespace-nowrap min-w-[800px]">
                                    <thead>
                                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-extrabold text-slate-600">
                                            <th class="px-4 py-3 text-center w-16">ลำดับ</th>
                                            <th class="px-4 py-3">รายการ</th>
                                            <th class="px-4 py-3">รายละเอียด</th>
                                            <th class="px-4 py-3 text-center w-24">จำนวน</th>
                                            <th class="px-4 py-3 text-center w-24">หน่วย</th>
                                            <th class="px-4 py-3 text-right w-32">ราคาต่อหน่วย</th>
                                            <th class="px-4 py-3 text-right w-32">รวมจำนวน</th>
                                            <th class="px-4 py-3 text-center w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="budget-items-tbody" class="text-xs font-semibold text-slate-700 divide-y divide-slate-50">
                                        <tr id="budget-empty-row" style="display: table-row;">
                                            <td colspan="8" class="py-8 text-center text-slate-400 font-medium">ยังไม่มีรายการ กรุณากดเพิ่มรายการ</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-4 py-4 flex justify-between items-center bg-white border-t border-slate-100">
                                <button type="button" onclick="addBudgetItem()" class="flex items-center gap-1.5 px-4 py-2 bg-white text-blue-600 border border-blue-100 hover:bg-blue-50 hover:border-blue-200 rounded-xl text-xs font-bold transition shadow-sm">
                                    <i class="fa-solid fa-plus"></i> เพิ่มรายการ
                                </button>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="font-extrabold text-slate-700">รวมเป็นเงินทั้งสิ้น</span>
                                    <span id="budget-grand-total" class="font-black text-blue-900 text-lg">0.00 บาท</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: เหตุผล -->
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">เหตุผลในการขอเบิก <span class="text-red-500">*</span></label>
                        <textarea rows="2" placeholder="ระบุเหตุผลในการขอเบิกงบประมาณ..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 transition font-semibold text-sm shadow-sm resize-none text-slate-700"></textarea>
                    </div>

                    <!-- Row 4: เอกสารประกอบ -->
                    <div>
                        <label class="block text-slate-700 mb-1.5 font-bold text-xs">เอกสารประกอบ</label>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <label for="budget_file_input" class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 rounded-xl text-xs font-bold transition cursor-pointer w-fit">
                                <i class="fa-solid fa-paperclip"></i> แนบไฟล์
                                <input type="file" id="budget_file_input" multiple class="hidden" onchange="handleBudgetFiles(this)">
                            </label>
                            <div id="budget-file-list" class="flex flex-wrap items-center gap-2">
                            </div>
                            <span id="budget-no-file-text" class="text-xs text-slate-400 block">ยังไม่มีไฟล์แนบ</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-2">
                        <button type="button" onclick="submitBudgetRequest()" class="px-8 py-3 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-extrabold text-sm rounded-xl transition shadow-md shadow-indigo-200">
                            บันทึก
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

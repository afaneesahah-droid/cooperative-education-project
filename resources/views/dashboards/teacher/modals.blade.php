
<!-- Detail Modal -->
<div id="detail-modal" onclick="closeDetailModal(event)" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4">
    <div id="modal-box" onclick="event.stopPropagation()" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-6 py-4 flex justify-between items-center text-white">
            <div>
                <h2 class="text-white text-xl font-black mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-blue-200"></i>รายละเอียดการแจ้งซ่อม
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <span id="modal-code" class="bg-blue-800/50 text-blue-100 px-2 py-0.5 rounded text-sm font-bold tracking-wider"></span>
                    <span class="text-blue-100 text-[11px] font-medium"><i class="fa-regular fa-calendar mr-1"></i> วันที่แจ้ง: <span id="modal-created"></span></span>
                    <span id="modal-repaired-badge" class="text-emerald-200 text-[11px] font-medium hidden"><i class="fa-regular fa-calendar-check mr-1"></i> วันที่ซ่อม: <span id="modal-repaired"></span></span>
                </div>
            </div>
            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto scrollbar-hide">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <p id="modal-reporter-name" class="font-black text-slate-800 text-base"></p>
                        <p class="text-xs font-bold text-slate-400">ผู้แจ้งซ่อม</p>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- รายละเอียดปัญหาที่แจ้ง -->
            <div id="modal-description-section" class="bg-amber-50/70 rounded-2xl p-4 border border-amber-100">
                <p class="text-[10px] font-extrabold text-amber-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-note-sticky text-amber-500"></i>รายละเอียดอาการชำรุดที่แจ้ง
                </p>
                <div id="modal-description" class="text-sm text-slate-700 font-medium leading-relaxed whitespace-pre-line"></div>
            </div>

            <!-- รูปภาพประกอบที่แนบ -->
            <div id="modal-image-section" class="bg-slate-50 rounded-2xl p-4 border border-slate-200 hidden">
                <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-image text-blue-500"></i>รูปภาพประกอบที่แนบ
                </p>
                <div class="relative rounded-xl overflow-hidden bg-slate-200/80 border border-slate-200 max-h-72 flex items-center justify-center">
                    <a id="modal-image-link" href="#" target="_blank" class="block w-full h-full text-center group cursor-pointer">
                        <img id="modal-image-preview" src="" alt="รูปภาพประกอบ" class="w-full max-h-72 object-contain rounded-xl transition group-hover:opacity-95">
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5 pointer-events-none">
                            <i class="fa-solid fa-up-right-and-down-left-from-center"></i> คลิกเพื่อดูภาพขนาดเต็ม
                        </div>
                    </a>
                </div>
            </div>

            <div id="modal-inspection-section" class="bg-blue-50 rounded-2xl p-4 border border-blue-100 hidden">
                <p class="text-[10px] font-extrabold text-blue-500 uppercase tracking-wider mb-2"><i class="fa-solid fa-wrench mr-1"></i>รายละเอียดการตรวจสอบ (ช่าง)</p>
                <p id="modal-inspection-result" class="text-sm text-slate-700 font-medium leading-relaxed"></p>
            </div>

            <div id="modal-repair-details-section" class="bg-indigo-50 rounded-2xl p-4 border border-indigo-100 hidden">
                <p class="text-[10px] font-extrabold text-indigo-500 uppercase tracking-wider mb-2"><i class="fa-solid fa-screwdriver-wrench mr-1"></i>รายละเอียดการซ่อม / อะไหล่ที่ต้องซื้อ</p>
                <p id="modal-repair-details" class="text-sm text-slate-700 font-medium leading-relaxed"></p>
            </div>

            <div id="modal-repair-result-section" class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100 hidden">
                <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-circle-check"></i>ผลการซ่อมบำรุง</p>
                <p id="modal-repair-result" class="text-sm text-slate-800 font-bold leading-relaxed"></p>
            </div>
        </div>
    </div>
</div>

<!-- Evaluation Modal -->
<div id="eval-modal" onclick="closeEvalModal(event)" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4 transition-opacity duration-300 opacity-0">
    <div id="eval-modal-box" onclick="event.stopPropagation()" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-6 py-4 flex justify-between items-center text-white">
            <div>
                <h2 class="text-white text-lg md:text-xl font-black mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-star text-amber-300"></i> ประเมินความพึงพอใจ
                </h2>
                <div class="flex items-center gap-2">
                    <span class="text-blue-100/80 text-xs font-medium">ใบงาน:</span>
                    <span id="eval-modal-code" class="bg-blue-800/60 text-blue-100 px-2.5 py-0.5 rounded text-xs font-bold font-mono tracking-wider">-</span>
                </div>
            </div>
            <button type="button" onclick="closeEvalModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Body -->
        <form action="{{ route('repair.rate') }}" method="POST" id="eval-form" onsubmit="return validateRatingForm(this)">
            @csrf
            <input type="hidden" name="request_id" id="eval-modal-request-id">
            
            <div class="p-6 space-y-5">
                <!-- เกณฑ์โดยรวม Card -->
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-5 text-center flex flex-col items-center">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-blue-100/80 text-blue-700 text-xs font-bold mb-2">
                        <i class="fa-solid fa-award text-blue-600"></i> เกณฑ์โดยรวม
                    </div>
                    <h4 class="text-base font-black text-slate-800 mb-1">ระดับความพึงพอใจในภาพรวม</h4>
                    <p class="text-xs text-slate-400 font-normal mb-2">โปรดเลือกระดับความพึงพอใจต่อผลการให้บริการและการซ่อมบำรุง</p>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-[11px] font-bold mb-3 shadow-xs">
                        <i class="fa-solid fa-star text-amber-500"></i> สรุปผลคะแนนแบบทศนิยม (1.0 - 5.0 คะแนน)
                    </div>
                    
                    <!-- Star Rating System -->
                    <div class="star-rating py-2.5 px-5 bg-white rounded-xl border border-slate-200 shadow-sm inline-flex items-center justify-center" id="modal-star-rating" style="justify-content: center; gap: 0.65rem;">
                        <input type="radio" id="r_rating_5" name="rating" value="5" required onchange="updateRatingText(5)">
                        <label for="r_rating_5" title="5 ดาว - พอใจมากที่สุด (5.0 คะแนน)" class="text-3xl hover:scale-125 transition-transform"><i class="fa-solid fa-star"></i></label>
                        
                        <input type="radio" id="r_rating_4" name="rating" value="4" onchange="updateRatingText(4)">
                        <label for="r_rating_4" title="4 ดาว - พอใจมาก (4.0 คะแนน)" class="text-3xl hover:scale-125 transition-transform"><i class="fa-solid fa-star"></i></label>
                        
                        <input type="radio" id="r_rating_3" name="rating" value="3" onchange="updateRatingText(3)">
                        <label for="r_rating_3" title="3 ดาว - ปานกลาง (3.0 คะแนน)" class="text-3xl hover:scale-125 transition-transform"><i class="fa-solid fa-star"></i></label>
                        
                        <input type="radio" id="r_rating_2" name="rating" value="2" onchange="updateRatingText(2)">
                        <label for="r_rating_2" title="2 ดาว - น้อย (2.0 คะแนน)" class="text-3xl hover:scale-125 transition-transform"><i class="fa-solid fa-star"></i></label>
                        
                        <input type="radio" id="r_rating_1" name="rating" value="1" onchange="updateRatingText(1)">
                        <label for="r_rating_1" title="1 ดาว - น้อยที่สุด (1.0 คะแนน)" class="text-3xl hover:scale-125 transition-transform"><i class="fa-solid fa-star"></i></label>
                    </div>

                    <!-- Description of Selected Rating -->
                    <div id="rating-description" class="mt-3 text-xs md:text-sm font-bold text-slate-400 min-h-[1.5rem] transition-all flex items-center justify-center">
                        คลิกที่รูปดาวเพื่อเลือกระดับคะแนน (1.0 - 5.0 คะแนน)
                    </div>
                </div>

                <!-- ข้อเสนอแนะเพิ่มเติม -->
                <div>
                    <label class="flex items-center gap-1.5 text-slate-700 font-bold text-xs mb-2">
                        <i class="fa-regular fa-comment-dots text-blue-600"></i> ข้อเสนอแนะเพิ่มเติม (ถ้ามี)
                    </label>
                    <textarea name="eval_comment" rows="3" placeholder="ระบุข้อเสนอแนะเพิ่มเติมเพื่อพัฒนาการให้บริการ..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 text-slate-700 text-xs md:text-sm font-medium transition-all resize-none placeholder:text-slate-400"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 pb-6 pt-1 flex gap-3 bg-white">
                <button type="button" onclick="closeEvalModal()" class="w-1/3 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-sm transition-colors text-center">
                    ยกเลิก
                </button>
                <button type="submit" class="w-2/3 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-bold text-sm shadow-md shadow-blue-500/25 hover:shadow-lg hover:shadow-blue-500/35 hover:-translate-y-0.5 transition-all flex justify-center items-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i> <span>ส่งแบบประเมิน</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Logout Form & Modal -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
<div id="logout-modal" onclick="closeLogoutModal(event)" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div onclick="event.stopPropagation()" class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl border border-slate-100 text-center">
        <div class="inline-flex bg-blue-50 text-blue-600 p-4 rounded-2xl mb-4">
            <i class="fa-solid fa-right-from-bracket text-3xl"></i>
        </div>
        <h3 class="text-xl font-black text-slate-900">ออกจากระบบ?</h3>
        <p class="text-sm text-slate-500 mt-2 font-medium">คุณต้องการออกจากระบบหรือไม่?</p>
        <div class="flex gap-3 mt-6">
            <button type="button" onclick="closeLogoutModal()" class="flex-1 px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-2xl transition">ยกเลิก</button>
            <button type="button" onclick="document.getElementById('logout-form').submit()" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-2xl transition shadow-md shadow-blue-200">
                <i class="fa-solid fa-right-from-bracket mr-1"></i> ออกจากระบบ
            </button>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div id="modal-teacher-profile" onclick="if(event.target === this) closeProfileModal()"
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

            <form id="teacher-profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
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
                                <input type="file" name="profile_photo" id="teacher_profile_photo_input" onchange="previewPhoto(event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" accept="image/jpeg,image/png,image/webp">
                                
                                <!-- Default Upload Content -->
                                <div id="avatar-placeholder" class="flex flex-col items-center w-full h-full justify-center p-3 text-center @if(isset($user->profile_photo) && $user->profile_photo) hidden @endif">
                                    <div class="w-10 h-10 bg-slate-200 text-slate-400 rounded-xl flex items-center justify-center mb-2 group-hover:bg-blue-100 group-hover:text-blue-500 transition">
                                        <i class="fa-solid fa-user text-base"></i>
                                    </div>
                                    <h6 class="font-bold text-slate-700 text-xs mb-0.5 group-hover:text-blue-600 transition">อัปโหลดรูปโปรไฟล์</h6>
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
                            $displayGroup = Auth::user()->department_group ?: ($user->department_group ?? '-');
                        }
                    }

                    $roles = explode(',', Auth::user()->role ?? ($user->role ?? ''));
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
            <button type="submit" form="teacher-profile-form" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-xl transition shadow-md shadow-blue-200 hover:shadow-lg cursor-pointer flex items-center gap-1.5">
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


<!-- Budget Requisition Form is now directly embedded into tab-requisition in teacher.blade.php (No popup modal needed) -->
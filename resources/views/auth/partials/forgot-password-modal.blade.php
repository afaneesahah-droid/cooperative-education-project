<!-- Forgot Password Modal (Exactly like Add User modal in Manage Permissions page) -->
<div id="forgot-password-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white max-w-2xl w-full rounded-3xl border border-slate-100 shadow-2xl p-6 md:p-8 space-y-5 text-sm font-semibold relative animate-in fade-in zoom-in-95 duration-200">
        <!-- Close Button -->
        <button type="button" onclick="closeForgotPasswordModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h4 class="text-base sm:text-lg font-black text-slate-900 flex items-center">
            <i class="fa-solid fa-key text-blue-600 mr-2"></i> คำขอกู้คืนรหัสผ่านผู้ใช้งาน
        </h4>

        <div id="forgot-password-form-container">
            <form id="forgot-password-form" onsubmit="submitForgotPasswordForm(event)" class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- ชื่อจริง -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">ชื่อ <span class="text-red-500">*</span></label>
                        <input type="text" id="forgot-first-name" required placeholder="ชื่อ" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold">
                    </div>

                    <!-- นามสกุล -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">นามสกุล <span class="text-red-500">*</span></label>
                        <input type="text" id="forgot-last-name" required placeholder="นามสกุล" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- รหัสผู้ใช้ / Username -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">รหัสผู้ใช้ / Username <span class="text-red-500">*</span></label>
                        <input type="text" id="forgot-username" required placeholder="รหัสผู้ใช้ (สำหรับล็อกอิน)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold">
                    </div>

                    <!-- อีเมล -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">อีเมล <span class="text-red-500">*</span></label>
                        <input type="email" id="forgot-email" required placeholder="เช่น somchai@ytc.ac.th" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-blue-600 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- เบอร์โทรศัพท์ -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">เบอร์โทรศัพท์ <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            id="forgot-phone" 
                            required 
                            pattern="[0-9]{10}" 
                            maxlength="10" 
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            placeholder="เบอร์โทรศัพท์ 10 หลัก (0-9)"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold"
                        >
                    </div>

                    <!-- ตำแหน่ง -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">ตำแหน่ง <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select id="forgot-position" onchange="handleForgotPositionChange()" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold cursor-pointer appearance-none">
                                <option value="">-- กรุณาเลือกตำแหน่ง --</option>
                                <option value="ครู">ครู</option>
                                <option value="เจ้าหน้าที่ซ่อมบำรุง">เจ้าหน้าที่ซ่อมบำรุง</option>
                                <option value="หัวหน้างาน">หัวหน้างาน</option>
                                <option value="ผู้บริหาร">ผู้บริหาร</option>
                                <option value="ผู้ดูแลระบบ">ผู้ดูแลระบบ</option>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- ฝ่าย -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">ฝ่าย <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select id="forgot-dept-group" onchange="updateForgotSubDepartments()" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold cursor-pointer appearance-none">
                                <option value="">-- กรุณาเลือกฝ่าย --</option>
                                <option value="resource">ฝ่ายบริหารทรัพยากร</option>
                                <option value="planning">ฝ่ายแผนงานและความร่วมมือ</option>
                                <option value="student">ฝ่ายพัฒนากิจการนักเรียน นักศึกษา</option>
                                <option value="academic">ฝ่ายวิชาการ</option>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- งาน/แผนกวิชา -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">งาน/แผนกวิชา <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select id="forgot-dept" required disabled class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-700 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold cursor-pointer appearance-none">
                                <option value="">-- กรุณาเลือกฝ่ายก่อน --</option>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- สิทธิ์การใช้งาน (ล็อกตามตำแหน่งแบบออโต้) -->
                <div>
                    <label class="block text-slate-700 mb-1 font-bold">สิทธิ์การใช้งาน (ล็อกออโต้) <span class="text-red-500">*</span></label>
                    <select id="forgot-role" disabled required class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 text-slate-500 rounded-xl font-bold cursor-not-allowed appearance-none">
                        <option value="teacher">ครูผู้ใช้งาน (Teacher)</option>
                        <option value="technician">ช่างซ่อมบำรุง (Technician)</option>
                        <option value="head">หัวหน้างานศูนย์ข้อมูล (Head)</option>
                        <option value="executive">ผู้บริหาร (Executive)</option>
                        <option value="admin">ผู้ดูแลระบบ (Admin)</option>
                    </select>
                    <input type="hidden" id="forgot-role-hidden" value="teacher">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- รหัสผ่านใหม่ -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">รหัสผ่านใหม่ <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="forgot-password" oninput="validateForgotConfirmPassword()" required placeholder="อย่างน้อย 6 ตัว (มี A-Z, a-z และตัวเลข)" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold">
                            <button type="button" onclick="toggleForgotPassVisibility('forgot-password', 'forgot-toggle-icon-1')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="fa-solid fa-eye" id="forgot-toggle-icon-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ยืนยันรหัสผ่านใหม่ -->
                    <div>
                        <label class="block text-slate-700 mb-1 font-bold">ยืนยันรหัสผ่านใหม่ <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="forgot-password-confirm" oninput="validateForgotConfirmPassword()" required placeholder="ยืนยันรหัสผ่านอีกครั้ง" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:bg-white transition font-bold">
                            <button type="button" onclick="toggleForgotPassVisibility('forgot-password-confirm', 'forgot-toggle-icon-2')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="fa-solid fa-eye" id="forgot-toggle-icon-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="flex-grow py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black tracking-wide shadow-md shadow-blue-200 transition">ส่งคำขอรีเซ็ตรหัสผ่าน</button>
                    <button type="button" onclick="closeForgotPasswordModal()" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition font-bold">ยกเลิก</button>
                </div>
            </form>
        </div>

        <!-- Success Container -->
        <div id="forgot-password-success-container" class="hidden text-center py-8 space-y-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 text-green-600 border border-green-200 text-2xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h5 class="text-base font-black text-slate-900">ส่งคำขอกู้คืนสำเร็จ!</h5>
            <p class="text-xs text-slate-500 font-semibold leading-relaxed px-4">
                ระบบได้จดบันทึกคำขอรีเซ็ตรหัสผ่านใหม่ของคุณเรียบร้อยแล้ว เจ้าหน้าที่แผนกศูนย์ข้อมูลสารสนเทศ (ศูนย์ข้อมูลตึก 6 ชั้น 2) จะดำเนินการตรวจสอบความถูกต้องและอนุมัติให้คุณในระบบภายใน 24 ชั่วโมง
            </p>
            <div class="pt-4 flex justify-center">
                <button type="button" onclick="closeForgotPasswordModal()" class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition text-sm">
                    ตกลง
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const forgotSubDepartmentData = {
        resource: [
            "งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", 
            "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"
        ],
        planning: [
            "งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", 
            "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", 
            "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา"
        ],
        student: [
            "งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", 
            "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"
        ],
        academic: [
            "งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", 
            "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", 
            "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", 
            "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", 
            "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า"
        ]
    };

    function updateForgotSubDepartments() {
        const mainDeptSelect = document.getElementById("forgot-dept-group");
        const subDeptSelect = document.getElementById("forgot-dept");
        const selectedMain = mainDeptSelect.value;

        subDeptSelect.innerHTML = "";

        if (selectedMain === "") {
            subDeptSelect.disabled = true;
            const defaultOpt = document.createElement("option");
            defaultOpt.value = "";
            defaultOpt.text = "-- กรุณาเลือกฝ่ายก่อน --";
            subDeptSelect.appendChild(defaultOpt);
        } else {
            subDeptSelect.disabled = false;
            
            const defaultOpt = document.createElement("option");
            defaultOpt.value = "";
            defaultOpt.text = "เลือกงาน / แผนกวิชา";
            subDeptSelect.appendChild(defaultOpt);

            forgotSubDepartmentData[selectedMain].forEach(function(item) {
                const opt = document.createElement("option");
                opt.value = item;
                opt.text = item;
                subDeptSelect.appendChild(opt);
            });
        }
    }

    function handleForgotPositionChange() {
        const positionSelect = document.getElementById("forgot-position");
        const roleSelect = document.getElementById("forgot-role");
        const roleHidden = document.getElementById("forgot-role-hidden");
        const selectedPos = positionSelect.value;

        let role = "";
        if (selectedPos === "ครู") {
            role = "teacher";
        } else if (selectedPos === "เจ้าหน้าที่ซ่อมบำรุง") {
            role = "technician";
        } else if (selectedPos === "หัวหน้างาน") {
            role = "head";
        } else if (selectedPos === "ผู้บริหาร") {
            role = "executive";
        } else if (selectedPos === "ผู้ดูแลระบบ") {
            role = "admin";
        }

        roleSelect.value = role;
        roleHidden.value = role;
    }

    function toggleForgotPassVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.className = "fa-solid fa-eye-slash";
        } else {
            input.type = "password";
            icon.className = "fa-solid fa-eye";
        }
    }

    function validateForgotConfirmPassword() {
        const pass = document.getElementById('forgot-password');
        const confirm = document.getElementById('forgot-password-confirm');
        if (pass.value !== confirm.value) {
            confirm.setCustomValidity("รหัสผ่านไม่ตรงกัน");
        } else {
            confirm.setCustomValidity("");
        }
    }

    // ควบคุม Modal ลืมรหัสผ่าน
    function openForgotPasswordModal() {
        document.getElementById('forgot-first-name').value = '';
        document.getElementById('forgot-last-name').value = '';
        document.getElementById('forgot-username').value = '';
        document.getElementById('forgot-email').value = '';
        document.getElementById('forgot-phone').value = '';
        document.getElementById('forgot-position').value = '';
        document.getElementById('forgot-dept-group').value = '';
        document.getElementById('forgot-dept').value = '';
        document.getElementById('forgot-dept').disabled = true;
        document.getElementById('forgot-password').value = '';
        document.getElementById('forgot-password-confirm').value = '';
        
        document.getElementById('forgot-password-form-container').classList.remove('hidden');
        document.getElementById('forgot-password-success-container').classList.add('hidden');
        
        const modal = document.getElementById('forgot-password-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeForgotPasswordModal() {
        const modal = document.getElementById('forgot-password-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function submitForgotPasswordForm(event) {
        event.preventDefault();
        document.getElementById('forgot-password-form-container').classList.add('hidden');
        document.getElementById('forgot-password-success-container').classList.remove('hidden');
    }
</script>

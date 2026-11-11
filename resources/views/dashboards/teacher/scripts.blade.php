<script>
    // Live Clock Logic
    (function() {
        function tickClock() {
            const clock = document.getElementById('live-clock');
            if (!clock) return;
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            const month = months[now.getMonth()];
            const year = now.getFullYear() + 543;
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            clock.textContent = `วันที่แจ้ง: ${day} ${month} ${year} เวลา ${hours}:${minutes} น.`;
        }
        tickClock();
        setInterval(tickClock, 1000);
    })();

    // Image Upload & Preview Handling
    function handleRepairImageSelect(input) {
        const file = input.files && input.files[0];
        const placeholder = document.getElementById('repair-image-placeholder');
        const previewContainer = document.getElementById('repair-image-preview-container');
        const previewImg = document.getElementById('repair-image-preview');
        const nameEl = document.getElementById('repair-image-name');
        const sizeEl = document.getElementById('repair-image-size');

        if (!file) {
            removeRepairImage();
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ประเภทไฟล์ไม่ถูกต้อง',
                    text: 'กรุณาเลือกไฟล์รูปภาพที่เป็น JPG, PNG หรือ WEBP เท่านั้น',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#3B82F6'
                });
            } else {
                alert('กรุณาเลือกไฟล์รูปภาพที่เป็น JPG, PNG หรือ WEBP เท่านั้น');
            }
            input.value = '';
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ขนาดไฟล์เกินกำหนด',
                    text: 'ไฟล์รูปภาพต้องมีขนาดไม่เกิน 5 MB',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#3B82F6'
                });
            } else {
                alert('ไฟล์รูปภาพต้องมีขนาดไม่เกิน 5 MB');
            }
            input.value = '';
            return;
        }

        // Format file size
        const sizeFormatted = file.size > 1024 * 1024 
            ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
            : (file.size / 1024).toFixed(1) + ' KB';

        if (nameEl) nameEl.textContent = file.name;
        if (sizeEl) sizeEl.textContent = sizeFormatted;

        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewImg) previewImg.src = e.target.result;
            if (placeholder) placeholder.classList.add('hidden');
            if (previewContainer) previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    function removeRepairImage(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const input = document.getElementById('repair-image-input');
        const placeholder = document.getElementById('repair-image-placeholder');
        const previewContainer = document.getElementById('repair-image-preview-container');
        const previewImg = document.getElementById('repair-image-preview');

        if (input) input.value = '';
        if (previewImg) previewImg.src = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dropzone = document.getElementById('repair-image-dropzone');
        const input = document.getElementById('repair-image-input');
        if (dropzone && input) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-blue-500', 'bg-blue-50/40');
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-blue-500', 'bg-blue-50/40');
                }, false);
            });
            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    input.files = files;
                    handleRepairImageSelect(input);
                }
            }, false);
        }
    });

    // Form Submission Logic
    function prepareSubmit(form) {
        try {
            // Collect checked symptoms
            const symptoms = [];
            const checkboxes = form.querySelectorAll('input[name="symptoms[]"]:checked');
            checkboxes.forEach((cb) => {
                symptoms.push(cb.value);
            });

            const detailsEl = document.getElementById('additional-details');
            const details = detailsEl ? detailsEl.value : '';
            let finalDesc = '';
            if (symptoms.length > 0) {
                finalDesc += 'อาการที่พบ: ' + symptoms.join(', ');
            }
            if (details.trim() !== '') {
                finalDesc += (finalDesc !== '' ? '\n' : '') + 'รายละเอียดเพิ่มเติม: ' + details;
            }
            if (finalDesc === '') {
                finalDesc = '-';
            }
            
            const descInput = document.getElementById('final-description');
            if (descInput) {
                descInput.value = finalDesc;
            }

            // Determine device type
            let deviceType = 'ทั่วไป';
            const kindFilter = document.getElementById('kind-filter');
            const categoryFilter = document.getElementById('category-filter');
            const deviceSelect = document.getElementById('device-select-input');
            
            let foundDevice = null;
            if (deviceSelect && deviceSelect.value && window.teacherDevicesData) {
                const val = deviceSelect.value.trim().toLowerCase();
                foundDevice = window.teacherDevicesData.find(x => {
                    const c = (x.device_code || '').trim().toLowerCase();
                    const id = (x.equipment_id || '').trim().toLowerCase();
                    return c === val || id === val;
                });
            }
            
            if (foundDevice) {
                deviceType = foundDevice.type || foundDevice.name || foundDevice.category || deviceType;
            } else if (kindFilter && kindFilter.value) {
                deviceType = kindFilter.value;
            } else if (categoryFilter && categoryFilter.value) {
                deviceType = categoryFilter.value;
            }
            
            const typeInput = document.getElementById('type-input');
            if (typeInput) {
                typeInput.value = deviceType;
            }

            // Validate project budget
            const projSelect = document.getElementById('teacher-repair-project-select');
            const bInput = document.getElementById('teacher-repair-budget-input');
            if (projSelect && projSelect.value !== '') {
                const enteredB = parseFloat(bInput?.value) || 0;
                const selOpt = projSelect.options[projSelect.selectedIndex];
                const maxRem = parseFloat(selOpt?.getAttribute('data-remaining') || 0);

                if (enteredB <= 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'กรุณาระบุงบประมาณที่ต้องการใช้',
                            text: 'ท่านได้เลือกโครงการซ่อมบำรุงแล้ว กรุณาระบุจำนวนเงินงบประมาณที่ต้องการใช้',
                            icon: 'warning',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#4F46E5',
                            customClass: { popup: 'rounded-[2rem]' }
                        });
                    } else {
                        alert('กรุณาระบุงบประมาณที่ต้องการใช้');
                    }
                    if (bInput) bInput.focus();
                    return false;
                }

                if (enteredB > maxRem) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'งบประมาณคงเหลือไม่เพียงพอ',
                            html: `ยอดงบประมาณที่ระบุ (<b>${enteredB.toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท</b>)<br>เกินกว่างบประมาณคงเหลือในโครงการ (<b>${maxRem.toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท</b>)`,
                            icon: 'error',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#EF4444',
                            customClass: { popup: 'rounded-[2rem]' }
                        });
                    } else {
                        alert('งบประมาณคงเหลือไม่เพียงพอ');
                    }
                    if (bInput) bInput.focus();
                    return false;
                }
            }
            
            // Show loading spinner
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'กำลังส่งข้อมูลแจ้งซ่อม...',
                    text: 'ระบบกำลังบันทึกข้อมูล กรุณารอสักครู่',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            // Collect form data
            const formData = new FormData(form);

            fetch(form.action || "{{ route('repair.store') }}", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(async (response) => {
                const data = await response.json().catch(() => null);
                if (response.ok && data && data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'แจ้งซ่อมสำเร็จ!',
                            text: data.message || 'บันทึกคำขอแจ้งซ่อมเรียบร้อยแล้ว',
                            icon: 'success',
                            heightAuto: false,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#3B82F6',
                            customClass: {
                                popup: 'rounded-[2rem]',
                                title: 'text-2xl font-black text-slate-800',
                                confirmButton: 'px-8 py-3 rounded-xl font-bold text-lg'
                            }
                        }).then(() => {
                            window.location.href = data.redirect || "{{ url('/dashboard/teacher?tab=status') }}";
                        });
                    } else {
                        alert(data.message || 'บันทึกคำขอแจ้งซ่อมเรียบร้อยแล้ว');
                        window.location.href = data.redirect || "{{ url('/dashboard/teacher?tab=status') }}";
                    }
                } else {
                    const errMsg = (data && data.message) ? data.message : 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'ไม่สามารถแจ้งซ่อมได้',
                            text: errMsg,
                            icon: 'error',
                            heightAuto: false,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#EF4444',
                            customClass: {
                                popup: 'rounded-[2rem]',
                                title: 'text-2xl font-black text-slate-800',
                                confirmButton: 'px-8 py-3 rounded-xl font-bold text-lg'
                            }
                        });
                    } else {
                        alert(errMsg);
                    }
                }
            })
            .catch((error) => {
                console.error('Fetch error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'การเชื่อมต่อขัดข้อง',
                        text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                        icon: 'error',
                        heightAuto: false,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#EF4444',
                        customClass: {
                            popup: 'rounded-[2rem]',
                            title: 'text-2xl font-black text-slate-800',
                            confirmButton: 'px-8 py-3 rounded-xl font-bold text-lg'
                        }
                    });
                } else {
                    alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
                }
            });

        } catch (err) {
            console.error('prepareSubmit caught error:', err);
        }

        return false; // Prevent full page browser submission!
    }

    document.addEventListener('DOMContentLoaded', function() {
        const reportForm = document.querySelector('form[action*="repair-requests"]');
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                prepareSubmit(this);
            });
        }
    });

    // Project & Budget selection logic for teacher
    function handleTeacherProjectChange(select) {
        const selected = select.options[select.selectedIndex];
        const infoCard = document.getElementById('teacher-project-info-card');
        const budgetInput = document.getElementById('teacher-repair-budget-input');
        const hintSpan = document.getElementById('teacher-budget-max-hint');

        if (selected && selected.value !== '') {
            const code = selected.getAttribute('data-code') || '-';
            const dept = selected.getAttribute('data-dept') || '-';
            const source = selected.getAttribute('data-source') || 'งบประมาณกลาง';
            const budget = parseFloat(selected.getAttribute('data-budget') || 0);
            const remaining = parseFloat(selected.getAttribute('data-remaining') || 0);

            document.getElementById('teacher-info-code').innerText = code;
            const sourceElem = document.getElementById('teacher-info-source');
            if (sourceElem) sourceElem.innerText = source;
            document.getElementById('teacher-info-dept').innerText = dept;
            document.getElementById('teacher-info-budget').innerText = budget.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';
            document.getElementById('teacher-info-remaining').innerText = remaining.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';

            if (hintSpan) {
                hintSpan.innerText = `(งบที่ใช้ได้สูงสุด ${remaining.toLocaleString('th-TH', {minimumFractionDigits: 2})} บ.)`;
                hintSpan.classList.remove('hidden');
            }

            if (budgetInput) {
                budgetInput.disabled = false;
                budgetInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
                budgetInput.classList.add('bg-white');
                budgetInput.focus();
            }

            if (infoCard) infoCard.classList.remove('hidden');
            calculateTeacherRemaining();
        } else {
            if (infoCard) infoCard.classList.add('hidden');
            if (hintSpan) hintSpan.classList.add('hidden');
            if (budgetInput) {
                budgetInput.value = '';
                budgetInput.disabled = true;
                budgetInput.classList.add('bg-slate-100', 'cursor-not-allowed');
                budgetInput.classList.remove('bg-white');
            }
        }
    }

    function calculateTeacherRemaining() {
        const select = document.getElementById('teacher-repair-project-select');
        const budgetInput = document.getElementById('teacher-repair-budget-input');
        if (!select || !budgetInput) return;

        const selected = select.options[select.selectedIndex];
        if (!selected || !selected.value) return;

        const remaining = parseFloat(selected.getAttribute('data-remaining') || 0);
        const entered = parseFloat(budgetInput.value) || 0;
        const remainingAfter = remaining - entered;

        const calcPreview = document.getElementById('teacher-calc-preview');
        const calcError = document.getElementById('teacher-calc-error');
        const remainingAfterSpan = document.getElementById('teacher-calc-remaining-after');

        if (entered > remaining) {
            if (calcError) calcError.classList.remove('hidden');
            if (calcPreview) calcPreview.classList.add('hidden');
            budgetInput.classList.add('border-red-500', 'text-red-600');
            budgetInput.classList.remove('border-slate-200');
        } else {
            if (calcError) calcError.classList.add('hidden');
            if (calcPreview) calcPreview.classList.remove('hidden');
            if (remainingAfterSpan) {
                remainingAfterSpan.innerText = remainingAfter.toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท';
            }
            budgetInput.classList.remove('border-red-500', 'text-red-600');
            budgetInput.classList.add('border-slate-200');
        }
    }

    function openCreateBudgetModal() {
        const modal = document.getElementById('create-budget-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            const box = document.getElementById('create-budget-modal-box');
            if (box) {
                box.classList.remove('opacity-0', 'scale-95');
                box.classList.add('opacity-100', 'scale-100');
            }
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeCreateBudgetModal() {
        const modal = document.getElementById('create-budget-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Tab Switching System (รองรับ Desktop & Mobile พร้อมจำแท็บเมื่อรีเฟรช)
    function switchTab(tabId, element) {
        // 1. ซ่อน Tab Content ทั้งหมด และแสดงเฉพาะ Tab ที่เลือก
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(el => {
            el.classList.add('hidden');
            el.classList.remove('block');
        });

        const target = document.getElementById(tabId);
        if (target) {
            target.classList.remove('hidden');
            target.classList.add('block');
        }

        // 2. ปรับ Style ของปุ่ม เมนู Sidebar/Bottom nav
        const allTabs = document.querySelectorAll('.desktop-tab');
        allTabs.forEach(el => {
            if (el.tagName === 'A') {
                el.className = "desktop-tab tab-link border-l-4 border-transparent text-blue-100/70 hover:bg-white/10 hover:text-white font-semibold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg";
            } else if (el.tagName === 'BUTTON') {
                el.classList.remove('text-blue-600', 'font-bold');
                el.classList.add('text-slate-500', 'font-semibold');
            }
        });

        if (!element) {
            element = document.querySelector('aside a.desktop-tab[onclick*="' + tabId + '"]') || document.querySelector('.desktop-tab[onclick*="' + tabId + '"]');
        }

        if (element) {
            if (element.tagName === 'A') {
                element.className = "desktop-tab tab-link border-l-4 border-white text-blue-900 bg-white shadow-md font-bold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg";
                const mobBtn = document.querySelector('nav button.desktop-tab[onclick*="' + tabId + '"]');
                if (mobBtn) {
                    mobBtn.classList.remove('text-slate-500', 'font-semibold');
                    mobBtn.classList.add('text-blue-600', 'font-bold');
                }
            } else if (element.tagName === 'BUTTON') {
                element.classList.remove('text-slate-500', 'font-semibold');
                element.classList.add('text-blue-600', 'font-bold');
                const deskLink = document.querySelector('aside a.desktop-tab[onclick*="' + tabId + '"]');
                if (deskLink) {
                    deskLink.className = "desktop-tab tab-link border-l-4 border-white text-blue-900 bg-white shadow-md font-bold whitespace-nowrap py-3 px-4 text-sm transition-all flex items-center gap-3 rounded-lg";
                }
            }
        }

        // 3. บันทึกแท็บที่เลือกเพื่อให้อยู่หน้าเดิมเวลารีเฟรช
        try {
            const url = new URL(window.location);
            const shortName = tabId.replace(/^tab-/, '');
            url.searchParams.set('tab', shortName);
            window.history.replaceState(null, '', url);
            sessionStorage.setItem('active_teacher_tab', tabId);
        } catch (e) {
            console.error('Error saving tab state:', e);
        }

        // Trigger History Dashboard update/resize when tab is opened
        if (tabId === 'tab-history') {
            setTimeout(() => {
                if (typeof window.onHistoryTabOpened === 'function') {
                    window.onHistoryTabOpened();
                }
            }, 60);
        }
    }

    // กู้คืนแท็บที่เปิดค้างไว้เมื่อรีเฟรชหน้า
    document.addEventListener('DOMContentLoaded', function() {
        @if(!session('active_tab') && !$errors->hasAny(['current_password','name','email','new_password']))
        try {
            const urlParams = new URLSearchParams(window.location.search);
            let tabParam = urlParams.get('tab');
            let targetTab = null;

            if (tabParam) {
                targetTab = tabParam.startsWith('tab-') ? tabParam : ('tab-' + tabParam);
            } else if (window.location.hash) {
                targetTab = window.location.hash.replace('#', '');
            } else {
                targetTab = sessionStorage.getItem('active_teacher_tab');
            }

            if (targetTab && document.getElementById(targetTab)) {
                const el = document.querySelector('aside a.desktop-tab[onclick*="' + targetTab + '"]') || document.querySelector('.desktop-tab[onclick*="' + targetTab + '"]');
                switchTab(targetTab, el);
            }
        } catch (e) {
            console.error('Error restoring tab:', e);
        }
        @endif
    });

    // Modal Control Functions
    function confirmLogout() {
        const modal = document.getElementById('logout-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeLogoutModal(e) {
        if (!e || e.target === document.getElementById('logout-modal')) {
            const modal = document.getElementById('logout-modal');
            if (modal) modal.classList.add('hidden');
        }
    }

    window.openProfileModal = function() {
        const modal = document.getElementById('modal-teacher-profile');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
    };

    window.closeProfileModal = function(e) {
        if (!e || e.target === document.getElementById('modal-teacher-profile')) {
            const modal = document.getElementById('modal-teacher-profile');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        }
    };

    function openDetailModal(data) {
        const modal = document.getElementById('detail-modal');
        const box = document.getElementById('modal-box');
        if (!modal || !box) return;

        document.getElementById('modal-code').textContent = data.code || '-';
        let deviceEl = document.getElementById('modal-device');
        if (deviceEl) deviceEl.textContent = data.device || '-';
        let deviceCodeEl = document.getElementById('modal-device-code');
        if (deviceCodeEl) deviceCodeEl.textContent = data.device_code || '-';
        
        // New layout mapping
        let reporterEl = document.getElementById('modal-reporter-name');
        if (reporterEl) reporterEl.textContent = data.reporter_name || '-';
        let catEl = document.getElementById('modal-asset-category'); if(catEl) catEl.textContent = data.asset_category || '-';
        let kindEl = document.getElementById('modal-asset-kind'); if(kindEl) kindEl.textContent = data.asset_kind || '-';
        let bldEl = document.getElementById('modal-building'); if(bldEl) bldEl.textContent = data.building || '-';
        let floorEl = document.getElementById('modal-floor'); if(floorEl) floorEl.textContent = data.floor || '-';
        let roomEl = document.getElementById('modal-room'); if(roomEl) roomEl.textContent = data.room || '-';
        let decodedDesc = data.description || '-';
        if (data.description && data.description !== '-') {
            try {
                decodedDesc = decodeURIComponent(escape(window.atob(data.description)));
            } catch (e) {
                decodedDesc = data.description;
            }
        }
        let descEl = document.getElementById('modal-description');
        if(descEl) descEl.innerHTML = decodedDesc;
        
        let inspectionSection = document.getElementById('modal-inspection-section');
        let inspectionResult = document.getElementById('modal-inspection-result');
        if (inspectionSection && inspectionResult) {
            let decodedInsp = data.inspection_result || '';
            if (decodedInsp) {
                try {
                    decodedInsp = decodeURIComponent(escape(window.atob(decodedInsp)));
                } catch (e) {
                    // Do nothing
                }
            }
            if (decodedInsp && decodedInsp.trim() !== '') {
                inspectionResult.innerHTML = decodedInsp;
                inspectionSection.classList.remove('hidden');
            } else {
                inspectionSection.classList.add('hidden');
            }
        }
        
        let repairSection = document.getElementById('modal-repair-details-section');
        let repairDetails = document.getElementById('modal-repair-details');
        if (repairSection && repairDetails) {
            let decodedRepair = data.repair_details || '';
            if (decodedRepair) {
                try {
                    decodedRepair = decodeURIComponent(escape(window.atob(decodedRepair)));
                } catch (e) {
                    // Do nothing
                }
            }
            if (decodedRepair && decodedRepair.trim() !== '') {
                repairDetails.innerHTML = decodedRepair;
                repairSection.classList.remove('hidden');
                if (inspectionSection && !inspectionSection.classList.contains('hidden')) {
                    inspectionSection.classList.add('mb-3');
                }
            } else {
                repairSection.classList.add('hidden');
                if (inspectionSection) {
                    inspectionSection.classList.remove('mb-3');
                }
            }
        }

        let assigneeEl = document.getElementById('modal-assignee');
        if (assigneeEl) assigneeEl.textContent = data.assignee || 'ยังไม่มอบหมาย';
        document.getElementById('modal-created').textContent = data.created_at || '-';

        // Repair date & result
        let repairedDateEl = document.getElementById('modal-repaired');
        let repairedBadge = document.getElementById('modal-repaired-badge');
        if (repairedDateEl && repairedBadge) {
            if (data.repair_date && data.repair_date !== '-') {
                repairedDateEl.textContent = data.repair_date;
                repairedBadge.classList.remove('hidden');
            } else {
                repairedBadge.classList.add('hidden');
            }
        }

        let repairResultSec = document.getElementById('modal-repair-result-section');
        let repairResultText = document.getElementById('modal-repair-result');
        if (repairResultSec && repairResultText) {
            let decodedOutcome = data.repair_result || '';
            if (decodedOutcome) {
                try {
                    decodedOutcome = decodeURIComponent(escape(window.atob(decodedOutcome)));
                } catch (e) {
                    // Do nothing
                }
            }
            if (decodedOutcome && decodedOutcome.trim() !== '') {
                repairResultText.innerHTML = decodedOutcome;
                repairResultSec.classList.remove('hidden');
            } else {
                repairResultSec.classList.add('hidden');
            }
        }

        const prioEl = document.getElementById('modal-priority');
        if (prioEl) {
            prioEl.innerHTML = data.priority === 'ด่วนที่สุด' 
                ? '<span class="text-red-600 font-black"><i class="fa-solid fa-triangle-exclamation mr-1"></i> ด่วนที่สุด</span>' 
                : '<span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check mr-1"></i> ระดับปกติ</span>';
        }

        // Image modal preview
        const imgSection = document.getElementById('modal-image-section');
        const imgPreview = document.getElementById('modal-image-preview');
        const imgLink = document.getElementById('modal-image-link');
        if (imgSection && imgPreview) {
            if (data.image && data.image.trim() !== '') {
                imgPreview.src = data.image;
                if (imgLink) imgLink.href = data.image;
                imgSection.classList.remove('hidden');
            } else {
                imgPreview.src = '';
                if (imgLink) imgLink.href = '#';
                imgSection.classList.add('hidden');
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeDetailModal(e) {
        if (e && e.target !== document.getElementById('detail-modal')) return;
        const modal = document.getElementById('detail-modal');
        const box = document.getElementById('modal-box');
        if (!modal || !box) return;

        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }

    function openEvalModal(id, code, deviceName) {
        const modal = document.getElementById('eval-modal');
        const box = document.getElementById('eval-modal-box');
        if (!modal) return;
        
        const reqInput = document.getElementById('eval-modal-request-id');
        if (reqInput) reqInput.value = id;
        
        const codeSpan = document.getElementById('eval-modal-code');
        if (codeSpan) codeSpan.textContent = deviceName ? `${code}: ${deviceName}` : code;
        
        // Reset rating selection
        const radios = modal.querySelectorAll('input[type="radio"][name="rating"]');
        radios.forEach(r => r.checked = false);
        
        const ratingDesc = document.getElementById('rating-description');
        if (ratingDesc) {
            ratingDesc.className = 'mt-4 text-xs md:text-sm font-bold text-slate-400 min-h-[1.5rem] transition-all';
            ratingDesc.textContent = 'คลิกที่รูปดาวเพื่อเลือกระดับคะแนน (1 - 5 ดาว)';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            if (box) {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }
        });
        document.body.style.overflow = 'hidden';
    }

    function updateRatingText(val) {
        const textEl = document.getElementById('rating-description');
        if (!textEl) return;
        const labels = {
            5: { text: '⭐ คะแนน: 5.0 / 5.0 — พอใจมากที่สุด (ยอดเยี่ยม)', color: 'text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl shadow-xs' },
            4: { text: '⭐ คะแนน: 4.0 / 5.0 — พอใจมาก (ดีมาก)', color: 'text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl shadow-xs' },
            3: { text: '⭐ คะแนน: 3.0 / 5.0 — ปานกลาง', color: 'text-amber-600 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-xl shadow-xs' },
            2: { text: '⭐ คะแนน: 2.0 / 5.0 — น้อย', color: 'text-orange-600 bg-orange-50 border border-orange-200 px-3 py-1.5 rounded-xl shadow-xs' },
            1: { text: '⭐ คะแนน: 1.0 / 5.0 — น้อยที่สุด (ควรปรับปรุง)', color: 'text-rose-600 bg-rose-50 border border-rose-200 px-3 py-1.5 rounded-xl shadow-xs' }
        };
        if (labels[val]) {
            textEl.className = `mt-3 text-xs md:text-sm font-black min-h-[1.5rem] transition-all flex items-center justify-center ${labels[val].color}`;
            textEl.textContent = labels[val].text;
        }
    }

    function validateRatingForm(form) {
        const rating = form.querySelector('input[name="rating"]:checked');
        if (!rating) {
            alert('กรุณาเลือกระดับความพึงพอใจโดยรวม (จำนวนดาว 1-5 ดาว) ก่อนส่งแบบประเมินครับ');
            return false;
        }
        return true;
    }

    function closeEvalModal(e) {
        if (e && e.target !== document.getElementById('eval-modal')) return;
        const modal = document.getElementById('eval-modal');
        const box = document.getElementById('eval-modal-box');
        if (!modal) return;
        
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        if (box) {
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
        }
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }

    // Phone Formatting Utility
    function formatPhoneNumber(input) {
        let val = input.value.replace(/\D/g, '');
        if (val.length > 10) val = val.substring(0, 10);
        let formatted = '';
        if (val.length > 0) formatted += val.substring(0, 3);
        if (val.length > 3) formatted += '-' + val.substring(3, 6);
        if (val.length > 6) formatted += '-' + val.substring(6, 10);
        input.value = formatted;
    }

    // Avatar Preview Utility
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('avatar-preview');
            if (img) {
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
    }

    function onKindChange() {
        const kindSelect = document.getElementById("kind-filter");
        const categorySelect = document.getElementById("category-filter");
        const typeInput = document.getElementById("type-input");
        
        if (!kindSelect) return;
        const kindVal = kindSelect.value;
        
        // หากเลือกชนิดทรัพย์สิน แต่ประเภททรัพย์สินยังไม่ตรง หรือยังไม่ได้เลือกประเภท ให้ซิงค์ประเภทให้ตรง แล้วกรองชนิดทรัพย์สินให้แสดงเฉพาะประเภทนั้น
        if (kindVal && categorySelect) {
            let matchedCat = "";
            const catMap = (window.teacherCatKindMap && Object.keys(window.teacherCatKindMap).length > 0)
                ? window.teacherCatKindMap
                : {
                    'ฮาร์ดแวร์': ['โน้ตบุ๊ค', 'เมาส์', 'แป้นพิมพ์'],
                    'ระบบเครือข่าย': ['สายแลน', 'wifi', 'อุปกรณ์กระจายสัญญาณ'],
                    'ซอฟต์แวร์': ['OS ซอฟต์แวร์สำนักงาน', 'OS ระบบปฎิบัติการ'],
                    'IOT': ['IOT', 'เซ็นเซอร์']
                };

            for (let cat in catMap) {
                const list = catMap[cat] || [];
                if (list.includes(kindVal) || list.some(k => (k || '').trim() === kindVal.trim() || (k || '').indexOf(kindVal) !== -1 || kindVal.indexOf(k || '') !== -1)) {
                    matchedCat = cat;
                    break;
                }
            }

            if (matchedCat && categorySelect.value !== matchedCat) {
                categorySelect.value = matchedCat;
                updateKindDropdown(kindVal);
            }
        }

        if (typeInput) {
            typeInput.value = kindVal || (categorySelect ? categorySelect.value : "ทั่วไป");
        }

        // เมื่อเปลี่ยนชนิดทรัพย์สิน ให้ล้างรหัสครุภัณฑ์เดิมออก และปรับสถานะล็อก
        const devInput = document.getElementById("device-select-input");
        if (devInput) devInput.value = "";
        const clearBtn = document.getElementById("clear-device-btn");
        if (clearBtn) clearBtn.classList.add("hidden");

        if (typeof updateSymptoms === "function") {
            updateSymptoms();
        }
        if (typeof filterDevices === "function") {
            filterDevices();
        }
        if (typeof syncDeviceSelectState === "function") {
            syncDeviceSelectState();
        }
    }

    function isDeviceSelectEligible() {
        const categorySelect = document.getElementById("category-filter");
        const kindSelect = document.getElementById("kind-filter");
        const catVal = categorySelect ? (categorySelect.value || "").trim() : "";
        const kindVal = kindSelect ? (kindSelect.value || "").trim() : "";
        return catVal !== "" && kindVal !== "";
    }

    function syncDeviceSelectState() {
        const categorySelect = document.getElementById("category-filter");
        const kindSelect = document.getElementById("kind-filter");
        const deviceInput = document.getElementById("device-select-input");
        const toggleBtn = document.getElementById("toggle-device-dropdown-btn");
        const clearBtn = document.getElementById("clear-device-btn");
        const hintEl = document.getElementById("device-lock-hint");
        const dropdown = document.getElementById("device-custom-dropdown");
        const wrapper = document.getElementById("device-combobox-wrapper");
        const labelTitle = document.getElementById("device-select-title");
        const labelSubtitle = document.getElementById("device-select-subtitle");
        const labelIcon = document.getElementById("device-select-icon");
        const inputIcon = document.getElementById("device-input-icon");

        if (!deviceInput) return;

        const catVal = categorySelect ? (categorySelect.value || "").trim() : "";
        const kindVal = kindSelect ? (kindSelect.value || "").trim() : "";
        const isEligible = catVal !== "" && kindVal !== "";

        const isSoftware = catVal.includes('ซอฟ') || catVal.toLowerCase().includes('soft');
        const isNetwork = catVal.includes('เครือข่าย') || catVal.toLowerCase().includes('net');
        const isIot = catVal.includes('IOT') || catVal.toLowerCase().includes('iot') || catVal.includes('ไอโอที');

        // ปรับเปลี่ยนหัวข้อข้อความและไอคอนตามหมวดหมู่ทรัพย์สิน
        if (isSoftware) {
            if (labelTitle) labelTitle.textContent = "ชื่อโปรแกรม / ซอฟต์แวร์";
            if (labelSubtitle) labelSubtitle.textContent = "ไม่บังคับ";
            if (labelIcon) labelIcon.className = "fa-solid fa-compact-disc text-blue-500 text-xs";
            if (inputIcon) inputIcon.className = "fa-solid fa-compact-disc text-sm";
        } else if (isNetwork) {
            if (labelTitle) labelTitle.textContent = "จุดเชื่อมต่อ / เครือข่าย";
            if (labelSubtitle) labelSubtitle.textContent = "ไม่บังคับ";
            if (labelIcon) labelIcon.className = "fa-solid fa-network-wired text-blue-500 text-xs";
            if (inputIcon) inputIcon.className = "fa-solid fa-network-wired text-sm";
        } else if (isIot) {
            if (labelTitle) labelTitle.textContent = "อุปกรณ์ IoT / จุดติดตั้ง";
            if (labelSubtitle) labelSubtitle.textContent = "ไม่บังคับ";
            if (labelIcon) labelIcon.className = "fa-solid fa-microchip text-blue-500 text-xs";
            if (inputIcon) inputIcon.className = "fa-solid fa-microchip text-sm";
        } else {
            if (labelTitle) labelTitle.textContent = "รหัสครุภัณฑ์ / อุปกรณ์";
            if (labelSubtitle) labelSubtitle.textContent = "ไม่บังคับ";
            if (labelIcon) labelIcon.className = "fa-solid fa-barcode text-blue-500 text-xs";
            if (inputIcon) inputIcon.className = "fa-solid fa-barcode text-sm";
        }

        if (isEligible) {
            deviceInput.disabled = false;
            deviceInput.readOnly = false;
            
            if (isSoftware) {
                deviceInput.placeholder = "เลือกจากรายการ หรือพิมพ์ชื่อโปรแกรม...";
            } else if (isNetwork) {
                deviceInput.placeholder = "เลือกจากรายการ หรือระบุจุดเชื่อมต่อ...";
            } else if (isIot) {
                deviceInput.placeholder = "เลือกจากรายการ หรือระบุอุปกรณ์ IoT...";
            } else {
                deviceInput.placeholder = "พิมพ์หรือเลือกรหัสครุภัณฑ์ (ถ้ามี)...";
            }
            
            deviceInput.className = "w-full pl-11 pr-20 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-700 font-bold text-sm transition-all shadow-sm hover:border-slate-300 placeholder:text-slate-400 placeholder:font-normal cursor-pointer";
            
            if (wrapper) {
                wrapper.classList.remove("opacity-75");
            }

            if (toggleBtn) {
                toggleBtn.disabled = false;
                toggleBtn.className = "text-slate-400 hover:text-blue-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer";
                toggleBtn.title = isSoftware ? "เปิด/ปิด รายการซอฟต์แวร์" : (isNetwork ? "เปิด/ปิด รายการเครือข่าย" : "เปิด/ปิด รายการครุภัณฑ์");
            }

            if (hintEl) {
                hintEl.classList.add("hidden");
                hintEl.innerHTML = "";
            }
        } else {
            deviceInput.disabled = true;
            deviceInput.value = "";
            deviceInput.placeholder = "เลือกประเภทและชนิดทรัพย์สินก่อน...";
            deviceInput.className = "w-full pl-11 pr-20 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-400 font-normal text-sm transition-all shadow-none cursor-not-allowed placeholder:text-slate-400 select-none";
            
            if (wrapper) {
                wrapper.classList.add("opacity-75");
            }

            if (toggleBtn) {
                toggleBtn.disabled = true;
                toggleBtn.className = "text-slate-300 p-1 rounded-lg transition cursor-not-allowed opacity-40 pointer-events-none";
                toggleBtn.title = "กรุณาเลือกประเภทและชนิดทรัพย์สินก่อน";
            }

            if (clearBtn) {
                clearBtn.classList.add("hidden");
            }

            if (dropdown) {
                dropdown.classList.add("hidden");
            }

            if (hintEl) {
                hintEl.classList.add("hidden");
                hintEl.innerHTML = "";
            }
        }
    }

    function handleDeviceWrapperClick(e) {
        if (!isDeviceSelectEligible()) {
            const categorySelect = document.getElementById("category-filter");
            const kindSelect = document.getElementById("kind-filter");
            const catVal = categorySelect ? (categorySelect.value || "").trim() : "";
            const kindVal = kindSelect ? (kindSelect.value || "").trim() : "";

            if (!catVal && categorySelect) {
                categorySelect.focus();
                categorySelect.classList.add("ring-4", "ring-amber-400/30", "border-amber-400");
                setTimeout(() => categorySelect.classList.remove("ring-4", "ring-amber-400/30", "border-amber-400"), 1200);
            } else if (!kindVal && kindSelect) {
                kindSelect.focus();
                kindSelect.classList.add("ring-4", "ring-amber-400/30", "border-amber-400");
                setTimeout(() => kindSelect.classList.remove("ring-4", "ring-amber-400/30", "border-amber-400"), 1200);
            }
        }
    }

    function updateKindDropdown(targetKindToSelect) {
        try {
            const categorySelect = document.getElementById("category-filter");
            const categoryFilter = categorySelect ? (categorySelect.value || "").trim() : "";
            const kindSelect = document.getElementById("kind-filter");
            if (!kindSelect) return;
            
            const prevSelectedKind = targetKindToSelect || kindSelect.value;
            kindSelect.innerHTML = '';
            
            // Map fallback ตามฐานข้อมูลจริงของระบบ
            const fallbackCatKindMap = {
                'ฮาร์ดแวร์': ['โน้ตบุ๊ค', 'เมาส์', 'แป้นพิมพ์'],
                'ระบบเครือข่าย': ['สายแลน', 'wifi', 'อุปกรณ์กระจายสัญญาณ'],
                'ซอฟต์แวร์': ['OS ซอฟต์แวร์สำนักงาน', 'OS ระบบปฎิบัติการ'],
                'IOT': ['IOT', 'เซ็นเซอร์']
            };

            const catKindMap = (window.teacherCatKindMap && Object.keys(window.teacherCatKindMap).length > 0)
                ? window.teacherCatKindMap
                : fallbackCatKindMap;

            kindSelect.disabled = false;
            kindSelect.className = "w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-700 font-bold text-sm transition-all appearance-none shadow-sm hover:border-slate-300 cursor-pointer";

            const defaultOpt = document.createElement("option");
            defaultOpt.value = "";

            if (!categoryFilter) {
                // ยังไม่ได้เลือกประเภททรัพย์สิน
                defaultOpt.textContent = "-- กรุณาเลือกประเภททรัพย์สินก่อน --";
                kindSelect.appendChild(defaultOpt);
                kindSelect.value = "";
                
                const typeInput = document.getElementById("type-input");
                if (typeInput) typeInput.value = "ทั่วไป";

                // ล้างรหัสครุภัณฑ์ และปรับสถานะล็อก
                const devInput = document.getElementById("device-select-input");
                if (devInput) devInput.value = "";
                const clearBtn = document.getElementById("clear-device-btn");
                if (clearBtn) clearBtn.classList.add("hidden");
                if (typeof syncDeviceSelectState === "function") {
                    syncDeviceSelectState();
                }
                return;
            }

            // เมื่อเลือกประเภททรัพย์สินแล้ว (เช่น ฮาร์ดแวร์) -> ให้มีแค่ชนิดของประเภททรัพย์สินนั้นเท่านั้น
            defaultOpt.textContent = "-- เลือกชนิดทรัพย์สิน --";
            kindSelect.appendChild(defaultOpt);

            let kinds = [];
            // 1. ค้นหาจาก window.teacherCatKindMap
            if (catKindMap[categoryFilter] && Array.isArray(catKindMap[categoryFilter])) {
                kinds = catKindMap[categoryFilter];
            } else {
                for (let catKey in catKindMap) {
                    if (catKey.trim() === categoryFilter || catKey.includes(categoryFilter) || categoryFilter.includes(catKey)) {
                        kinds = catKindMap[catKey] || [];
                        break;
                    }
                }
            }

            // 2. ค้นหาจาก fallbackCatKindMap ตามข้อมูลจริงของระบบ
            if (!kinds || kinds.length === 0) {
                for (let catKey in fallbackCatKindMap) {
                    if (catKey.trim() === categoryFilter || catKey.includes(categoryFilter) || categoryFilter.includes(catKey)) {
                        kinds = fallbackCatKindMap[catKey] || [];
                        break;
                    }
                }
            }

            let isSelected = false;
            if (kinds && kinds.length > 0) {
                kinds.forEach(function(kind) {
                    if (!kind || kind === 'น') return; // ข้ามข้อมูลทดสอบ
                    const opt = document.createElement("option");
                    opt.value = kind;
                    opt.textContent = kind;
                    if (kind === prevSelectedKind) {
                        opt.selected = true;
                        isSelected = true;
                    }
                    kindSelect.appendChild(opt);
                });
            } else {
                const emptyOpt = document.createElement("option");
                emptyOpt.value = "";
                emptyOpt.textContent = "ไม่มีรายการชนิดทรัพย์สิน";
                kindSelect.appendChild(emptyOpt);
            }

            if (!isSelected && !targetKindToSelect) {
                kindSelect.value = "";
            }

            const typeInput = document.getElementById("type-input");
            if (typeInput) {
                typeInput.value = kindSelect.value || categoryFilter || "ทั่วไป";
            }

            // เมื่อเปลี่ยนประเภททรัพย์สิน ให้ล้างรหัสครุภัณฑ์ และปรับสถานะล็อก
            const devInput = document.getElementById("device-select-input");
            if (devInput) devInput.value = "";
            const clearBtn = document.getElementById("clear-device-btn");
            if (clearBtn) clearBtn.classList.add("hidden");
            if (typeof syncDeviceSelectState === "function") {
                syncDeviceSelectState();
            }
        } catch (e) {
            console.error("Error in updateKindDropdown: " + e.message);
        }
    }

    // ==========================================
    function getDeviceIconClass(deviceName, deviceType) {
        const name = ((deviceName || '') + ' ' + (deviceType || '')).toLowerCase();
        if (name.includes('notebook') || name.includes('โน้ตบุ๊ค') || name.includes('laptop')) return 'fa-laptop';
        if (name.includes('mouse') || name.includes('เมาส์')) return 'fa-computer-mouse';
        if (name.includes('keyboard') || name.includes('คีย์บอร์ด') || name.includes('แป้นพิมพ์')) return 'fa-keyboard';
        if (name.includes('printer') || name.includes('ปริ้นเตอร์') || name.includes('เครื่องพิมพ์')) return 'fa-print';
        if (name.includes('server') || name.includes('เซิร์ฟเวอร์')) return 'fa-server';
        if (name.includes('wifi') || name.includes('ไวไฟ') || name.includes('access point') || name.includes('ap')) return 'fa-wifi';
        if (name.includes('router') || name.includes('switch') || name.includes('สวิตช์') || name.includes('แลน')) return 'fa-network-wired';
        if (name.includes('display') || name.includes('monitor') || name.includes('จอ')) return 'fa-display';
        if (name.includes('ซอฟต์แวร์') || name.includes('windows') || name.includes('office') || name.includes('lic')) return 'fa-compact-disc';
        if (name.includes('iot') || name.includes('เซ็นเซอร์') || name.includes('esp32') || name.includes('arduino') || name.includes('rpi')) return 'fa-microchip';
        return 'fa-desktop';
    }

    function isValidDeviceCode(code) {
        if (!code) return false;
        const clean = String(code).trim();
        return clean !== '' && clean !== '-';
    }

    function openDeviceDropdown() {
        if (!isDeviceSelectEligible()) return;
        const dropdown = document.getElementById('device-custom-dropdown');
        const chevron = document.getElementById('device-dropdown-chevron');
        if (!dropdown) return;
        renderDeviceDropdown();
        dropdown.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
    }

    function closeDeviceDropdown() {
        const dropdown = document.getElementById('device-custom-dropdown');
        const chevron = document.getElementById('device-dropdown-chevron');
        if (!dropdown) return;
        dropdown.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }

    function toggleDeviceDropdown() {
        if (!isDeviceSelectEligible()) return;
        const dropdown = document.getElementById('device-custom-dropdown');
        if (!dropdown) return;
        if (dropdown.classList.contains('hidden')) {
            openDeviceDropdown();
            const input = document.getElementById('device-select-input');
            if (input) input.focus();
        } else {
            closeDeviceDropdown();
        }
    }

    function selectDevice(code) {
        // ตรวจสอบเฉพาะอุปกรณ์ที่ไม่สามารถแจ้งซ่อมได้จริง ๆ
        if (window.teacherDevicesData) {
            const target = (code || '').trim().toLowerCase();
            const found = window.teacherDevicesData.find(d => {
                const c = (d.device_code || '').trim().toLowerCase();
                const id = (d.equipment_id || '').trim().toLowerCase();
                return c === target || id === target;
            });

            if (found && ['ไม่สามารถซ่อมได้', 'ชำรุดซ่อมไม่ได้', 'แทงจำหน่าย', 'จำหน่าย'].includes((found.status || '').trim())) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'ไม่สามารถแจ้งซ่อมได้',
                        text: 'อุปกรณ์นี้ได้รับการประเมินว่าไม่สามารถซ่อมแซมได้ (ปลดระวาง/แทงจำหน่าย)',
                        icon: 'error',
                        heightAuto: false,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#EF4444',
                        customClass: {
                            popup: 'rounded-[2rem]',
                            title: 'text-2xl font-black text-slate-800',
                            confirmButton: 'px-8 py-3 rounded-xl font-bold text-lg'
                        }
                    });
                } else {
                    alert('อุปกรณ์นี้ได้รับการประเมินว่าไม่สามารถซ่อมแซมได้ (ปลดระวาง/แทงจำหน่าย)');
                }
                return;
            }
        }

        const input = document.getElementById('device-select-input');
        if (input) {
            input.value = code;
        }
        const clearBtn = document.getElementById('clear-device-btn');
        if (clearBtn) clearBtn.classList.remove('hidden');
        
        closeDeviceDropdown();
        if (typeof updateDeviceCode === 'function') {
            updateDeviceCode();
        }
    }

    function handleDeviceInput(query) {
        if (!isDeviceSelectEligible()) {
            const input = document.getElementById('device-select-input');
            if (input) input.value = '';
            return;
        }
        const clearBtn = document.getElementById('clear-device-btn');
        if (clearBtn) {
            if (query && query.trim() !== '') {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }
        openDeviceDropdown();
        renderDeviceDropdown(query);
        if (typeof updateDeviceCode === 'function') {
            updateDeviceCode();
        }
    }

    function clearDeviceInput() {
        const input = document.getElementById("device-select-input");
        const clearBtn = document.getElementById("clear-device-btn");
        if (input) {
            input.value = "";
            if (clearBtn) clearBtn.classList.add("hidden");
            input.focus();
        }
        renderDeviceDropdown('');
        if (typeof updateDeviceCode === 'function') {
            updateDeviceCode();
        }
    }

    function filterDevices() {
        renderDeviceDropdown();
    }


    function renderDeviceDropdown(searchQuery) {
        const listEl = document.getElementById('device-dropdown-list');
        const countEl = document.getElementById('device-dropdown-count');
        if (!listEl) return;

        const input = document.getElementById('device-select-input');
        const query = (typeof searchQuery === 'string' ? searchQuery : (input ? input.value : '')).trim().toLowerCase();
        
        const categoryFilter = document.getElementById("category-filter") ? document.getElementById("category-filter").value.trim() : "";
        const kindFilter = document.getElementById("kind-filter") ? document.getElementById("kind-filter").value.trim() : "";
        
        let devices = Array.isArray(window.teacherDevicesData) ? [...window.teacherDevicesData] : [];
        
        // กรองอุปกรณ์จากฐานข้อมูล
        let filtered = devices.filter(d => {
            const code = ((d.device_code && d.device_code !== '-') ? d.device_code : (d.equipment_id || '')).trim();
            if (!isValidDeviceCode(code)) return false;

            const cat = (d.category || '').trim();
            const kind = (d.type || d.name || '').trim();

            // Match Category
            if (categoryFilter !== '') {
                let matchCat = cat === categoryFilter || cat.includes(categoryFilter) || categoryFilter.includes(cat);
                if (!matchCat) {
                    const allowedKinds = (window.teacherCatKindMap && window.teacherCatKindMap[categoryFilter]) || [];
                    for (let k of allowedKinds) {
                        if (k === kind || k.includes(kind) || kind.includes(k)) {
                            matchCat = true;
                            break;
                        }
                    }
                }
                if (!matchCat) return false;
            }

            // Match Kind
            if (kindFilter !== '') {
                const normKind = kind.toLowerCase();
                const normFilter = kindFilter.toLowerCase();
                const match = normKind === normFilter || 
                              normKind.includes(normFilter) || 
                              normFilter.includes(normKind) ||
                              (d.name && d.name.toLowerCase().includes(normFilter));
                if (!match) return false;
            }

            return true;
        });


        // Match Search Query
        if (query !== '') {
            filtered = filtered.filter(d => {
                const code = ((d.device_code && d.device_code !== '-') ? d.device_code : (d.equipment_id || '')).trim().toLowerCase();
                const name = (d.name || d.type || '').toLowerCase();
                const kind = (d.type || '').toLowerCase();
                const cat = (d.category || '').toLowerCase();
                const serial = (d.serial || '').toLowerCase();
                const brand = (d.brand || '').toLowerCase();
                const model = (d.model || '').toLowerCase();

                return code.includes(query) || 
                       name.includes(query) || 
                       kind.includes(query) || 
                       cat.includes(query) ||
                       brand.includes(query) ||
                       model.includes(query) ||
                       serial.includes(query);
            });
        }

        // ลบรายการซ้ำตามรหัสครุภัณฑ์
        const seen = new Set();
        const uniqueList = [];
        for (let d of filtered) {
            const code = ((d.device_code && d.device_code !== '-') ? d.device_code : (d.equipment_id || '')).trim();
            if (!seen.has(code.toLowerCase())) {
                seen.add(code.toLowerCase());
                uniqueList.push(d);
            }
        }

        if (countEl) {
            countEl.textContent = `${uniqueList.length} รายการ`;
        }

        const isSoftware = categoryFilter.includes('ซอฟ') || categoryFilter.toLowerCase().includes('soft');
        const isNetwork = categoryFilter.includes('เครือข่าย') || categoryFilter.toLowerCase().includes('net');
        const isIot = categoryFilter.includes('IOT') || categoryFilter.toLowerCase().includes('iot') || categoryFilter.includes('ไอโอที');

        let html = '';

        // กรณีผู้ใช้พิมพ์เอง และไม่ตรงกับในลิสต์ ให้มีปุ่มกดใช้รหัสที่พิมพ์
        const exactMatch = uniqueList.some(d => (((d.device_code && d.device_code !== '-') ? d.device_code : d.equipment_id) || '').trim().toLowerCase() === query);
        if (query !== '' && !exactMatch) {
            const safeQuery = query.replace(/"/g, '&quot;').replace(/'/g, "\\'");
            html += `
                <div onclick="selectDevice('${safeQuery}')" class="px-3.5 py-2.5 bg-blue-50/70 hover:bg-blue-100/70 cursor-pointer transition flex items-center justify-between text-xs text-slate-700 border-b border-blue-100">
                    <div class="flex items-center gap-2 truncate">
                        <i class="fa-solid fa-barcode text-blue-500 text-xs shrink-0"></i>
                        <span class="truncate">ใช้รหัสที่พิมพ์: <strong class="text-blue-700 font-bold">"${safeQuery}"</strong></span>
                    </div>
                    <span class="text-[11px] text-blue-700 font-bold bg-white px-2 py-0.5 rounded-full shadow-xs shrink-0 ml-2">ระบุเอง</span>
                </div>
            `;
        }

        uniqueList.forEach(device => {
            const code = ((device.device_code && device.device_code !== '-') ? device.device_code : (device.equipment_id || '')).trim();
            if (!isValidDeviceCode(code)) return;

            const safeCode = code.replace(/"/g, '&quot;').replace(/'/g, "\\'");
            const isSelected = (input && input.value.trim().toLowerCase() === code.toLowerCase());
            const room = (device.room_id || '').trim();
            const tagInfo = room ? `ห้อง ${room}` : '';

            const isUnrep = ['ไม่สามารถซ่อมได้', 'ชำรุดซ่อมไม่ได้', 'แทงจำหน่าย', 'จำหน่าย'].includes((device.status || '').trim());
            const unrepBadge = isUnrep ? '<span class="text-[10px] bg-rose-50 text-rose-600 border border-rose-200 px-2 py-0.5 rounded-full font-bold">ไม่สามารถซ่อมได้</span>' : '';

            html += `
                <div onclick="selectDevice('${safeCode}')" 
                     class="px-3.5 py-2.5 hover:bg-blue-50/80 cursor-pointer transition flex items-center justify-between gap-3 text-xs border-b border-slate-50 last:border-b-0 ${isSelected ? 'bg-blue-50 font-bold' : ''} ${isUnrep ? 'opacity-70 bg-rose-50/30' : ''}">
                    <div class="min-w-0 flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg ${isUnrep ? 'bg-rose-100 text-rose-600' : 'bg-blue-100/70 text-blue-600'} flex items-center justify-center shrink-0 text-xs">
                            <i class="fa-solid ${isUnrep ? 'fa-ban' : 'fa-barcode'}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-800 text-sm tracking-tight truncate">${code}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        ${unrepBadge}
                        ${tagInfo ? `<span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-semibold">${tagInfo}</span>` : ''}
                        ${isSelected ? '<i class="fa-solid fa-check text-blue-600 text-xs ml-1"></i>' : ''}
                    </div>
                </div>
            `;
        });

        if (!html) {
            html = `
                <div class="p-6 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-box-open text-2xl mb-2 text-slate-300 block"></i>
                    ไม่พบรหัสครุภัณฑ์ที่ค้นหา
                </div>
            `;
        }

        listEl.innerHTML = html;
    }

    // Outside click listener to close device dropdown
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('device-combobox-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeDeviceDropdown();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeviceDropdown();
        }
    });

    function updateDeviceCode() {
        const select = document.getElementById("device-select-input");
        const display = document.getElementById("device-code-display");
        const clearBtn = document.getElementById("clear-device-btn");
        if (!select) return;

        const val = (select.value || "").trim();
        if (clearBtn) {
            if (val !== "") {
                clearBtn.classList.remove("hidden");
            } else {
                clearBtn.classList.add("hidden");
            }
        }
        
        if (!val || !window.teacherDevicesData) return;

        const lowerVal = val.toLowerCase();
        // ค้นหาข้อมูลอุปกรณ์จากฐานข้อมูลจริง
        const device = window.teacherDevicesData.find(d => {
            const code = (d.device_code || "").trim().toLowerCase();
            const id = (d.equipment_id || "").trim().toLowerCase();
            return code === lowerVal || id === lowerVal;
        });

        if (!device) return; // หากเป็นรหัสที่คีย์เองที่ไม่มีในฐานข้อมูล

        if (display && device.serial) {
            display.value = device.serial;
        }

        // ประเภททรัพย์สินและชนิดทรัพย์สินไม่ต้องเลือกใหม่:
        // คงค่าเดิมที่ผู้ใช้เลือกไว้ ห้ามรีเซ็ตหรือบังคับเลือกใหม่
        const categorySelect = document.getElementById("category-filter");
        const kindSelect = document.getElementById("kind-filter");

        // เฉพาะกรณีที่ผู้ใช้ยังไม่ได้เลือกประเภททรัพย์สินไว้เลย จึงจะเติมค่าให้
        if (categorySelect && !categorySelect.value && device.category) {
            for (let opt of categorySelect.options) {
                if (opt.value === device.category || opt.value.indexOf(device.category) !== -1 || device.category.indexOf(opt.value) !== -1) {
                    categorySelect.value = opt.value;
                    break;
                }
            }
            if (typeof updateKindDropdown === "function") {
                updateKindDropdown(device.type || device.name);
            }
        } else if (kindSelect && !kindSelect.value && (device.type || device.name)) {
            const kindVal = device.type || device.name;
            for (let opt of kindSelect.options) {
                if (opt.value === kindVal || opt.text === kindVal || opt.value.indexOf(kindVal) !== -1) {
                    kindSelect.value = opt.value;
                    break;
                }
            }
        }

        // ปรับค่า hidden type โดยให้ความสำคัญกับสิ่งที่ผู้ใช้เลือก
        const typeInput = document.getElementById("type-input");
        if (typeInput) {
            typeInput.value = (kindSelect && kindSelect.value) || (categorySelect && categorySelect.value) || device.type || device.name || device.category || "ทั่วไป";
        }

        // ดึงข้อมูลสถานที่ (อาคาร ชั้น ห้อง) อัตโนมัติตามข้อมูลจริง
        const buildingId = device.building_id || "";
        const buildingSelect = document.getElementById("building-select");
        if (buildingSelect && buildingId) {
            for (let opt of buildingSelect.options) {
                if (opt.value == buildingId || opt.text.indexOf(buildingId) !== -1) {
                    buildingSelect.value = opt.value;
                    break;
                }
            }
        }

        // ดึงข้อมูลชั้นอัตโนมัติ (Floor)
        if (typeof updateFloors === "function") {
            updateFloors();
        }
        const floorSelect = document.getElementById("floor-select");
        const floorVal = device.floor;
        if (floorSelect && floorVal !== undefined && floorVal !== null && floorVal !== '') {
            floorSelect.disabled = false;
            for (let opt of floorSelect.options) {
                if (opt.value == floorVal || opt.text == "ชั้น " + floorVal || opt.text == floorVal) {
                    floorSelect.value = opt.value;
                    break;
                }
            }
        }

        // ดึงข้อมูลห้องอัตโนมัติ (Room)
        if (typeof updateRooms === "function") {
            updateRooms();
        }
        const roomSelect = document.getElementById("room-select");
        const roomCode = String(device.room_id || "").trim();
        if (roomSelect && roomCode) {
            roomSelect.disabled = false;
            for (let opt of roomSelect.options) {
                const optRoomCode = opt.getAttribute("data-room-code") || "";
                const optRoomId = opt.getAttribute("data-room-id") || "";
                if (opt.value == roomCode || optRoomCode == roomCode || optRoomId == roomCode || opt.text.startsWith(roomCode) || opt.text.indexOf(roomCode) !== -1) {
                    roomSelect.value = opt.value;
                    break;
                }
            }
        }
    }

    function updateSymptoms() {
        const catSelect = document.getElementById('category-filter');
        const kindSelect = document.getElementById('kind-filter');
        const container = document.getElementById('symptoms-container');
        if (!container) return;
        
        const category = catSelect ? (catSelect.value || '').trim() : '';
        const kind = kindSelect ? (kindSelect.value || '').trim() : '';

        const symptomsMap = {
            'ฮาร์ดแวร์': {
                'default': [
                    'เปิดเครื่องไม่ติด / ไฟไม่เข้า',
                    'เปิดติดแล้วดับ / เครื่องดับเอง',
                    'เปิดติดแต่ภาพไม่ขึ้น / หน้าจอดำ',
                    'หน้าจอเป็นเส้น / จอกระพริบ',
                    'หน้าจอแตก / ชำรุดเสียหาย',
                    'อุปกรณ์แตกหัก / พอร์ตเชื่อมต่อชำรุดเสียหาย',
                    'แป้นพิมพ์ / เมาส์ / ทัชแพด ไม่ทำงาน',
                    'มีเสียงดังผิดปกติ (พัดลม / อุปกรณ์ภายใน)',
                    'เครื่องร้อนจัดผิดปกติ / พัดลมไม่หมุน',
                    'แบตเตอรี่เสื่อม / ชาร์จไฟไม่เข้า',
                    'อุปกรณ์ต่อพ่วงตรวจไม่พบ (USB / HDMI)',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'โน้ตบุ๊ค': [
                    'เปิดเครื่องไม่ติด / ไฟไม่เข้าเครื่อง',
                    'เปิดติดแล้วดับ / เครื่องดับเอง',
                    'หน้าจอดำ / ภาพไม่ขึ้น',
                    'หน้าจอเป็นเส้น / จอกระพริบ',
                    'หน้าจอแตก / ชำรุดเสียหาย',
                    'แป้นพิมพ์ / ทัชแพด ใช้งานไม่ได้',
                    'แบตเตอรี่เสื่อม / เสียบสายชาร์จไม่เข้า',
                    'เครื่องร้อนจัด / พัดลมเสียงดังผิดปกติ',
                    'ตัวเครื่องแตกหัก / บานพับชำรุด',
                    'พอร์ตเชื่อมต่อชำรุด (USB / Type-C / HDMI)',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'เมาส์': [
                    'คลิกไม่ติด / คลิกยาก',
                    'ดับเบิ้ลคลิกเองผิดปกติ (คลิกเบิ้ล)',
                    'เลื่อนเคอร์เซอร์ไม่ได้ / เมาส์กระตุก',
                    'สายเมาส์ชำรุด / สายขาด',
                    'คอมพิวเตอร์ตรวจไม่พบเมาส์ (USB Unrecognized)',
                    'ไฟเซ็นเซอร์เมาส์ไม่ติด',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'แป้นพิมพ์': [
                    'ปุ่มกดไม่ติด / บางปุ่มใช้งานไม่ได้',
                    'ปุ่มกดค้าง / ตัวอักษรพิมพ์เบิ้ลเอง',
                    'แป้นพิมพ์ไม่ตอบสนองทั้งหมด',
                    'สายแป้นพิมพ์ชำรุด / สายขาด',
                    'คอมพิวเตอร์ตรวจไม่พบแป้นพิมพ์ (USB Unrecognized)',
                    'ปุ่มหลุดหาย / ขาตั้งแป้นพิมพ์หัก',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ]
            },
            'ซอฟต์แวร์': {
                'default': [
                    'เครื่องทำงานช้าผิดปกติ / โปรแกรมค้างบ่อย (Not Responding)',
                    'โปรแกรมเปิดไม่ขึ้น / เปิดแล้วเด้งปิดเอง',
                    'ระบบปฏิบัติการไม่ยอมบูต / ค้างหน้าโลโก้ (Boot Loop)',
                    'หน้าจอฟ้า (Blue Screen / BSOD)',
                    'ติดไวรัส / มัลแวร์ / มีโฆษณาแปลกปลอมรบกวน',
                    'เข้าสู่ระบบไม่ได้ / ลืมรหัสผ่าน / บัญชีถูกล็อค',
                    'เปิดไฟล์เอกสารไม่ได้ / ไฟล์เสียหาย (Corrupted File)',
                    'แจ้งเตือนข้อผิดพลาด (Error Message) / ไดรเวอร์มีปัญหา',
                    'ต้องการติดตั้งโปรแกรมใหม่ / ขอลงโปรแกรมเพิ่ม',
                    'ต้องการลงระบบปฏิบัติการใหม่ (ลง Windows ใหม่ / อัปเดตระบบ)',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'OS ระบบปฎิบัติการ': [
                    'ระบบปฏิบัติการไม่ยอมบูต / ค้างหน้าโลโก้ (Boot Loop)',
                    'หน้าจอฟ้า (Blue Screen / BSOD)',
                    'เครื่องทำงานช้าผิดปกติ / CPU or Disk 100%',
                    'เครื่องค้างบ่อย / รีสตาร์ทเอง',
                    'ติดไวรัส / มัลแวร์ระบบ',
                    'เข้าสู่ระบบไม่ได้ / ลืมรหัสผ่าน',
                    'ต้องการลง Windows ใหม่ / อัปเดตระบบปฏิบัติการ',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'OS ซอฟต์แวร์สำนักงาน': [
                    'โปรแกรมเปิดไม่ขึ้น / เปิดแล้วเด้งปิดทันที',
                    'โปรแกรมค้าง / ไม่ตอบสนอง (Not Responding)',
                    'แจ้งเตือน License หมดอายุ / บัญชีใช้งานไม่ได้',
                    'เปิดไฟล์เอกสารไม่ได้ / ไฟล์เสียหาย',
                    'ฟอนต์เพี้ยน / พิมพ์ภาษาไทยไม่ได้',
                    'ต้องการติดตั้งหรืออัปเดตโปรแกรมสำนักงาน',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ]
            },
            'ระบบเครือข่าย': {
                'default': [
                    'เชื่อมต่ออินเทอร์เน็ตไม่ได้ (No Internet / หลุดการเชื่อมต่อ)',
                    'สัญญาณ Wi-Fi หลุดบ่อย / เชื่อมต่อไม่เสถียร',
                    'มองไม่เห็นชื่อสัญญาณ Wi-Fi (SSID)',
                    'อินเทอร์เน็ตช้าผิดปกติ / ค่าปิง (Ping) สูง',
                    'เข้าเว็บไซต์บางเว็บไม่ได้ / ปัญหาเกี่ยวกับ DNS',
                    'ไม่ได้รับ IP Address (ขึ้น Limited Access / เครื่องหมายตกใจ)',
                    'สายแลนชำรุด / หัวล็อคหัก / ไฟพอร์ตแลน (LAN) ไม่ติด',
                    'เชื่อมต่อเครื่องพิมพ์เครือข่าย (Network Printer) ไม่ได้',
                    'ไม่สามารถแชร์ไฟล์หรือเข้าโฟลเดอร์เครือข่ายได้',
                    'อุปกรณ์เครือข่าย (Router / Switch / AP) ไฟไม่ติดหรือไม่ทำงาน',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'สายแลน': [
                    'หัวล็อค RJ-45 หัก / หัวสายหลวม',
                    'สายแลนขาด / เปลือกสายฉีกขาด',
                    'เสียบสายแล้วไฟพอร์ตแลนไม่ติด',
                    'ต่อสายแลนแล้วอินเทอร์เน็ตไม่วิ่ง / หลุดบ่อย',
                    'ความเร็วช้าผิดปกติ (เชื่อมต่อได้เพียง 10 Mbps)',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'wifi': [
                    'มองไม่เห็นชื่อสัญญาณ Wi-Fi (SSID)',
                    'เชื่อมต่อ Wi-Fi ไม่ได้ / รหัสผ่านไม่ผ่าน',
                    'สัญญาณ Wi-Fi อ่อนมาก / หลุดบ่อย',
                    'เชื่อมต่อ Wi-Fi ติดแต่ใช้งานอินเทอร์เน็ตไม่ได้ (No Internet)',
                    'ความเร็ว Wi-Fi ช้าผิดปกติ',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ],
                'อุปกรณ์กระจายสัญญาณ': [
                    'อุปกรณ์ไม่จ่ายไฟ / ไฟสถานะ Power ไม่ติด',
                    'พอร์ตเชื่อมต่อชำรุด / ไฟพอร์ตแลนไม่ติด',
                    'สัญญาณไม่กระจาย / ไม่แจกจ่าย IP Address',
                    'อุปกรณ์ร้อนจัด / ค้างบ่อย ต้องรีสตาร์ทตลอด',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ]
            },
            'IOT': {
                'default': [
                    'เซ็นเซอร์ไม่ทำงาน / ไม่อ่านค่า',
                    'อุปกรณ์ไม่ส่งข้อมูล / ค่าไม่อัปเดต',
                    'อุปกรณ์ขาดการเชื่อมต่อ (Offline)',
                    'ไฟแสดงสถานะไม่ติด / ไม่จ่ายไฟ',
                    'อุปกรณ์ชำรุดเสียหายทางกายภาพ',
                    'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
                ]
            },
            'default': [
                'เปิดเครื่องไม่ติด / ไฟไม่เข้า',
                'เครื่องค้าง / ทำงานช้าผิดปกติ',
                'หน้าจอดำ / ภาพไม่ขึ้น',
                'เชื่อมต่ออินเทอร์เน็ตไม่ได้',
                'โปรแกรมเปิดไม่ขึ้น / มีข้อผิดพลาด',
                'อุปกรณ์ชำรุดเสียหาย',
                'อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)'
            ]
        };

        let matchedCatKey = 'default';
        if (category.includes('ฮาร์ด') || category.toLowerCase().includes('hard')) {
            matchedCatKey = 'ฮาร์ดแวร์';
        } else if (category.includes('ซอฟ') || category.toLowerCase().includes('soft')) {
            matchedCatKey = 'ซอฟต์แวร์';
        } else if (category.includes('เครือข่าย') || category.toLowerCase().includes('net')) {
            matchedCatKey = 'ระบบเครือข่าย';
        } else if (category.includes('IOT') || category.toLowerCase().includes('iot') || category.includes('ไอโอที')) {
            matchedCatKey = 'IOT';
        }

        let symptomsList = [];
        if (matchedCatKey !== 'default' && symptomsMap[matchedCatKey]) {
            const catGroup = symptomsMap[matchedCatKey];
            let matchedKindKey = 'default';
            if (kind) {
                for (let k in catGroup) {
                    if (k !== 'default' && (k === kind || kind.includes(k) || k.includes(kind))) {
                        matchedKindKey = k;
                        break;
                    }
                }
            }
            symptomsList = catGroup[matchedKindKey] || catGroup['default'] || [];
        } else {
            symptomsList = symptomsMap['default'];
        }

        container.innerHTML = '';

        symptomsList.forEach(function(symptom) {
            const label = document.createElement('label');
            label.className = 'flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = 'symptoms[]';
            input.value = symptom;
            input.className = 'w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer';

            if (symptom.includes('อื่นๆ') || symptom.includes('อื่น ๆ')) {
                input.onchange = function() {
                    const extra = document.getElementById('additional-details');
                    if (extra) extra.focus();
                };
            }

            const span = document.createElement('span');
            span.className = 'text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none';
            span.textContent = symptom;

            label.appendChild(input);
            label.appendChild(span);
            container.appendChild(label);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const catFilter = document.getElementById('category-filter');
        const kindFilter = document.getElementById('kind-filter');
        if (catFilter) {
            catFilter.addEventListener('change', function() {
                updateKindDropdown();
                filterDevices();
                updateSymptoms();
            });
        }
        if (kindFilter) {
            kindFilter.addEventListener('change', function() {
                onKindChange();
                updateSymptoms();
            });
        }
        updateKindDropdown();
        filterDevices();
        updateSymptoms();
    });

    // Overrides for broken updateFloors and updateRooms in teacher.blade.php
    function updateFloors() {
        const buildingSelect = document.getElementById("building-select");
        const floorSelect = document.getElementById("floor-select");
        const roomSelect = document.getElementById("room-select");
        
        if (!buildingSelect || !floorSelect || !roomSelect) return;
        
        const selectedBuildingId = buildingSelect.value;
        
        floorSelect.innerHTML = "";
        roomSelect.innerHTML = "";
        roomSelect.disabled = true;
        
        const defaultRoomOpt = document.createElement("option");
        defaultRoomOpt.value = "";
        defaultRoomOpt.text = "-- กรุณาเลือกชั้นก่อน --";
        roomSelect.appendChild(defaultRoomOpt);
        
        if (!selectedBuildingId) {
            floorSelect.disabled = true;
            const defaultOpt = document.createElement("option");
            defaultOpt.value = "";
            defaultOpt.text = "-- กรุณาเลือกอาคารก่อน --";
            floorSelect.appendChild(defaultOpt);
        } else {
            floorSelect.disabled = false;
            const defaultOpt = document.createElement("option");
            defaultOpt.value = "";
            defaultOpt.text = "เลือกชั้น...";
            floorSelect.appendChild(defaultOpt);
            
            const b = window.teacherBuildingsData.find(x => x.building_id == selectedBuildingId);
            if (b && b.rooms && b.rooms.length > 0) {
                const floors = new Set();
                b.rooms.forEach(room => floors.add(room.floor));
                const sortedFloors = Array.from(floors).sort();
                sortedFloors.forEach(floor => {
                    const opt = document.createElement("option");
                    opt.value = floor;
                    opt.text = "ชั้น " + floor;
                    floorSelect.appendChild(opt);
                });
            } else if (b && b.floors) {
                for (let i = 1; i <= b.floors; i++) {
                    const opt = document.createElement("option");
                    opt.value = i;
                    opt.text = "ชั้น " + i;
                    floorSelect.appendChild(opt);
                }
            } else {
                const selectedOption = buildingSelect.options[buildingSelect.selectedIndex];
                if (selectedOption) {
                    const floors = parseInt(selectedOption.getAttribute("data-floors")) || 0;
                    for (let i = 1; i <= floors; i++) {
                        const opt = document.createElement("option");
                        opt.value = i;
                        opt.text = "ชั้น " + i;
                        floorSelect.appendChild(opt);
                    }
                }
            }
        }
    }

    function updateRooms() {
        const buildingSelect = document.getElementById("building-select");
        const floorSelect = document.getElementById("floor-select");
        const roomSelect = document.getElementById("room-select");
        if (!buildingSelect || !floorSelect || !roomSelect) return;
        
        const selectedBuildingId = buildingSelect.value;
        const selectedFloor = floorSelect.value;
        
        roomSelect.innerHTML = "";
        
        if (!selectedFloor) {
            roomSelect.disabled = true;
            const defaultOpt = document.createElement("option");
            defaultOpt.value = "";
            defaultOpt.text = "-- กรุณาเลือกชั้นก่อน --";
            roomSelect.appendChild(defaultOpt);
        } else {
            roomSelect.disabled = false;
            const defaultOpt = document.createElement("option");
            defaultOpt.value = "";
            defaultOpt.text = "เลือกห้อง...";
            roomSelect.appendChild(defaultOpt);
            
            const b = (window.teacherBuildingsData || []).find(x => x.building_id == selectedBuildingId);
            if (b && b.rooms) {
                const floorRooms = b.rooms.filter(r => r.floor == selectedFloor);
                floorRooms.forEach(room => {
                    const opt = document.createElement("option");
                    const code = room.room_code || room.room_number || '';
                    const name = room.room_name || room.name || '';
                    const displayText = code && name ? `${code} (${name})` : (code || name || room.id);
                    opt.value = code || displayText; 
                    opt.text = displayText;
                    opt.setAttribute("data-room-code", code);
                    opt.setAttribute("data-room-id", room.id);
                    roomSelect.appendChild(opt);
                });
            }
        }
    }

</script>

<script>
<!-- Budget Request JS from technician -->
let budgetItemCount = 0;

function addBudgetItem() {
    const tbody = document.getElementById('budget-items-tbody');
    const emptyRow = document.getElementById('budget-empty-row');
    if (emptyRow) {
        emptyRow.style.display = 'none';
    }

    budgetItemCount++;
    
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50 transition budget-item-row';
    tr.innerHTML = `
        <td class="py-2 px-4">
            <input type="text" placeholder="ระบุรายละเอียดอะไหล่..." class="w-full bg-transparent border-0 border-b border-gray-200 focus:border-blue-500 focus:ring-0 px-2 py-1 text-sm font-medium">
        </td>
        <td class="py-2 px-4 text-center">
            <input type="number" min="1" value="1" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" class="item-qty w-16 bg-transparent border-0 border-b border-gray-200 focus:border-blue-500 focus:ring-0 text-center font-bold">
        </td>
        <td class="py-2 px-4 text-center">
            <input type="text" placeholder="หน่วย" value="ชุด" class="w-16 bg-transparent border-0 border-b border-gray-200 focus:border-blue-500 focus:ring-0 text-center text-sm font-medium">
        </td>
        <td class="py-2 px-4 text-right">
            <input type="number" min="0" value="0" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" class="item-price w-24 bg-transparent border-0 border-b border-gray-200 focus:border-blue-500 focus:ring-0 text-right font-bold">
        </td>
        <td class="py-2 px-4 text-right font-black text-gray-800 item-total-text">0.00</td>
        <td class="py-2 px-3 text-center">
            <button type="button" onclick="removeBudgetItem(this)" class="text-red-400 hover:text-red-600 transition-colors bg-red-50 hover:bg-red-100 w-8 h-8 rounded-full flex items-center justify-center"><i class="fa-solid fa-trash-can"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    updateBudgetItemIndices();
}

function removeBudgetItem(btn) {
    const tr = btn.closest('tr');
    tr.remove();
    updateBudgetItemIndices();
    calculateBudgetGrandTotal();
}

function updateBudgetItemIndices() {
    const rows = document.querySelectorAll('.budget-item-row');
    if (rows.length === 0) {
        const emptyRow = document.getElementById('budget-empty-row');
        if (emptyRow) emptyRow.style.display = 'table-row';
    }
}

function calculateRowTotal(input) {
    const tr = input.closest('tr');
    const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
    const price = parseFloat(tr.querySelector('.item-price').value) || 0;
    const total = qty * price;
    
    tr.querySelector('.item-total-text').innerText = total.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    calculateBudgetGrandTotal();
}

// Function to convert numbers to Thai words
function ThaiBaht(Number) {
    let t = "";
    if (Number === 0) return "ศูนย์บาทถ้วน";
    Number = Number.toFixed(2);
    let strNumber = Number.toString();
    let numParts = strNumber.split('.');
    
    let baht = parseInt(numParts[0]);
    let satang = parseInt(numParts[1]);
    
    let text = "";
    if (baht > 0) text += getThaiText(baht) + "บาท";
    if (satang > 0) text += getThaiText(satang) + "สตางค์";
    else text += "ถ้วน";
    
    return text;
}

function getThaiText(num) {
    const numbers = ["ศูนย์", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
    const units = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
    
    let str = num.toString();
    let len = str.length;
    let result = "";
    
    for (let i = 0; i < len; i++) {
        let n = parseInt(str.charAt(i));
        if (n !== 0) {
            if (i === (len - 1) && n === 1 && len > 1) {
                result += "เอ็ด";
            } else if (i === (len - 2) && n === 2) {
                result += "ยี่";
            } else if (i === (len - 2) && n === 1) {
                // omit 1 in 10s place (สิบ)
            } else {
                result += numbers[n];
            }
            result += units[len - 1 - i];
        }
    }
    return result;
}

function calculateBudgetGrandTotal() {
    const rows = document.querySelectorAll('.budget-item-row');
    let grandTotal = 0;
    
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        grandTotal += (qty * price);
    });
    
    const gtElem = document.getElementById('budget-grand-total');
    if (gtElem) {
        gtElem.innerText = grandTotal.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท';
    }
    
    const txtElem = document.getElementById('budget-text-amount');
    if (txtElem) {
        txtElem.innerText = "( " + ThaiBaht(grandTotal) + " )";
    }
}

let budgetFiles = [];

function handleBudgetFiles(input) {
    const newFiles = Array.from(input.files);
    budgetFiles = budgetFiles.concat(newFiles);
    
    input.value = '';
    
    renderBudgetFileList();
}

function removeBudgetFile(index) {
    budgetFiles.splice(index, 1);
    renderBudgetFileList();
}

function renderBudgetFileList() {
    const listContainer = document.getElementById('budget-file-list');
    const noFileText = document.getElementById('budget-no-file-text');
    if (!listContainer) return;
    
    const existingItems = listContainer.querySelectorAll('.budget-file-item');
    existingItems.forEach(item => item.remove());
    
    if (budgetFiles.length === 0) {
        if (noFileText) noFileText.style.display = 'block';
    } else {
        if (noFileText) noFileText.style.display = 'none';
        
        budgetFiles.forEach((file, index) => {
            let fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            if (file.size < 1024 * 1024) {
                fileSize = (file.size / 1024).toFixed(2) + ' KB';
            }
            
            const fileDiv = document.createElement('div');
            fileDiv.className = 'budget-file-item flex items-center gap-2 text-sm bg-white border border-slate-200 px-3 py-2 rounded-lg w-fit';
            fileDiv.innerHTML = `
                <i class="fa-regular fa-file text-slate-400"></i>
                <span class="font-medium text-slate-600 truncate max-w-[200px] sm:max-w-xs" title="${file.name}">${file.name}</span>
                <span class="text-slate-400 text-xs ml-1">(${fileSize})</span>
                <button type="button" onclick="removeBudgetFile(${index})" class="text-slate-400 hover:text-red-500 transition-colors ml-2">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            listContainer.appendChild(fileDiv);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const createBudgetModalEl = document.getElementById('create-budget-modal');
    if (createBudgetModalEl) {
        createBudgetModalEl.addEventListener('click', function(e) {
            if (e.target === this) closeCreateBudgetModal();
        });
    }
});

// ==========================================
// Repair History Dashboard Analytics Logic
// ==========================================
let historyBarChartInstance = null;
let historyDonutChartInstance = null;
let currentHistoryTimeMode = 'all'; // 'all' | 'day' | 'month' | 'year'
let currentHistoryFilter = 'all';   // 'all' | 'repairable' | 'unrepairable_parts' | 'hardware' | 'software' | 'network_other'
let currentDonutView = 'outcome';   // 'outcome' | 'category'
let currentBarView = 'category';    // 'category' | 'timeline' | 'status'
let currentTeacherHistoryViewMode = 'card'; // 'card', 'table', 'chart'
let currentTeacherHistoryView = 'card';
let currentTeacherListDisplayMode = 'card';

function setTeacherHistoryViewMode(mode) {
    currentTeacherHistoryViewMode = mode;
    currentTeacherHistoryView = mode;
    if (mode === 'table' || mode === 'card') {
        currentTeacherListDisplayMode = mode;
    }

    const statCards = document.getElementById('teacher-history-stat-cards');
    const chartView = document.getElementById('teacher-history-chart-view');
    const tableView = document.getElementById('teacher-history-table-view');
    const tableContainer = document.getElementById('teacher-history-table-container');
    const cardContainer = document.getElementById('teacher-history-card-container');

    const btnHeaderCard = document.getElementById('btn-view-card');
    const btnHeaderTable = document.getElementById('btn-view-table');
    const btnHeaderChart = document.getElementById('btn-view-chart-toggle');

    const btnListCard = document.getElementById('btn-list-card');
    const btnListTable = document.getElementById('btn-list-table');

    const activeHeaderClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-white text-blue-700 shadow-sm cursor-pointer';
    const inactiveHeaderClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-white/80 hover:text-white hover:bg-white/10 cursor-pointer';

    const activeSubBtnClass = 'px-2.5 py-1 rounded-lg transition-all bg-white text-blue-600 shadow-xs flex items-center gap-1 font-bold cursor-pointer';
    const inactiveSubBtnClass = 'px-2.5 py-1 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center gap-1 font-semibold cursor-pointer';

    if (mode === 'chart') {
        // กราฟ: ให้แสดงเฉพาะกราฟเท่านั้น (ซ่อนการ์ดสถิติ 5 ใบ และซ่อนส่วนรายการทั้งหมด)
        if (statCards) statCards.classList.add('hidden');
        if (tableView) tableView.classList.add('hidden');
        if (chartView) chartView.classList.remove('hidden');

        // Header buttons highlight
        if (btnHeaderChart) btnHeaderChart.className = activeHeaderClass;
        if (btnHeaderCard) btnHeaderCard.className = inactiveHeaderClass;
        if (btnHeaderTable) btnHeaderTable.className = inactiveHeaderClass;

        // Render & Resize charts
        if (!historyBarChartInstance || !historyDonutChartInstance) {
            applyHistoryFilter();
        }
        setTimeout(() => {
            if (historyBarChartInstance) historyBarChartInstance.resize();
            if (historyDonutChartInstance) historyDonutChartInstance.resize();
            if (chartView) chartView.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 60);

    } else if (mode === 'table') {
        // ตาราง: แสดงการ์ดสถิติ และแสดงตารางรายการ ซ่อนกราฟ
        if (statCards) statCards.classList.remove('hidden');
        if (chartView) chartView.classList.add('hidden');
        if (tableView) tableView.classList.remove('hidden');
        if (tableContainer) tableContainer.classList.remove('hidden');
        if (cardContainer) cardContainer.classList.add('hidden');

        // Header buttons highlight
        if (btnHeaderTable) btnHeaderTable.className = activeHeaderClass;
        if (btnHeaderCard) btnHeaderCard.className = inactiveHeaderClass;
        if (btnHeaderChart) btnHeaderChart.className = inactiveHeaderClass;

        // Sub switcher highlight
        if (btnListTable) btnListTable.className = activeSubBtnClass;
        if (btnListCard) btnListCard.className = inactiveSubBtnClass;

    } else { // default 'card'
        // การ์ด: แสดงการ์ดสถิติ และแสดงการ์ดรายการ ซ่อนกราฟ
        if (statCards) statCards.classList.remove('hidden');
        if (chartView) chartView.classList.add('hidden');
        if (tableView) tableView.classList.remove('hidden');
        if (cardContainer) cardContainer.classList.remove('hidden');
        if (tableContainer) tableContainer.classList.add('hidden');

        // Header buttons highlight
        if (btnHeaderCard) btnHeaderCard.className = activeHeaderClass;
        if (btnHeaderTable) btnHeaderTable.className = inactiveHeaderClass;
        if (btnHeaderChart) btnHeaderChart.className = inactiveHeaderClass;

        // Sub switcher highlight
        if (btnListCard) btnListCard.className = activeSubBtnClass;
        if (btnListTable) btnListTable.className = inactiveSubBtnClass;
    }
}

// Backward compatibility wrappers
function setTeacherListDisplayMode(mode) {
    setTeacherHistoryViewMode(mode);
}

function toggleTeacherHistoryChart() {
    if (currentTeacherHistoryViewMode === 'chart') {
        setTeacherHistoryViewMode('card');
    } else {
        setTeacherHistoryViewMode('chart');
    }
}

function setTeacherHistoryView(mode) {
    if (mode === 'chart') {
        setTeacherHistoryViewMode('chart');
    } else if (mode === 'table') {
        setTeacherHistoryViewMode('table');
    } else {
        setTeacherHistoryViewMode('card');
    }
}

function syncHistorySearch(val) {
    const input1 = document.getElementById('history-search-input');
    const input2 = document.getElementById('history-search-input-secondary');
    if (input1 && input1.value !== val) input1.value = val;
    if (input2 && input2.value !== val) input2.value = val;
    applyHistoryFilter();
}

function clearHistorySearch() {
    const input1 = document.getElementById('history-search-input');
    const input2 = document.getElementById('history-search-input-secondary');
    if (input1) input1.value = '';
    if (input2) input2.value = '';
    applyHistoryFilter();
}

function setHistoryTimeMode(mode) {
    currentHistoryTimeMode = mode;
    
    // Mode toggle styling
    const modes = ['all', 'day', 'month', 'year'];
    modes.forEach(m => {
        const btn = document.getElementById(`btn-mode-${m}`);
        if (!btn) return;
        if (m === mode) {
            btn.className = 'history-mode-btn px-3 py-1.5 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold';
        } else {
            btn.className = 'history-mode-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-semibold';
        }
    });

    // Toggle date selector controls
    const ctrlDay = document.getElementById('control-day');
    const ctrlMonth = document.getElementById('control-month');
    const ctrlYear = document.getElementById('control-year');

    if (ctrlDay) ctrlDay.classList.toggle('hidden', mode !== 'day');
    if (ctrlMonth) ctrlMonth.classList.toggle('hidden', mode !== 'month');
    if (ctrlYear) ctrlYear.classList.toggle('hidden', mode !== 'year');

    applyHistoryFilter();
}

function setHistoryFilter(filterKey) {
    currentHistoryFilter = filterKey;
    
    const filterKeys = ['all', 'repairable', 'inprogress', 'unrepairable_parts', 'unrepairable', 'hardware', 'software', 'network_other'];
    filterKeys.forEach(k => {
        const chip = document.getElementById(`chip-${k}`);
        if (chip) {
            if (k === filterKey) {
                let colorClass = 'bg-blue-600 text-white shadow-blue-200';
                if (k === 'repairable') colorClass = 'bg-emerald-600 text-white shadow-emerald-200';
                else if (k === 'inprogress') colorClass = 'bg-teal-600 text-white shadow-teal-200';
                else if (k === 'unrepairable_parts') colorClass = 'bg-purple-600 text-white shadow-purple-200';
                else if (k === 'unrepairable') colorClass = 'bg-rose-600 text-white shadow-rose-200';
                else if (k === 'hardware') colorClass = 'bg-indigo-600 text-white shadow-indigo-200';
                else if (k === 'software') colorClass = 'bg-purple-600 text-white shadow-purple-200';
                else if (k === 'network_other') colorClass = 'bg-teal-600 text-white shadow-teal-200';
                chip.className = `history-chip active px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${colorClass} shadow-sm`;
            } else {
                chip.className = `history-chip px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 border border-transparent`;
            }
        }
    });

    // Update active border on top stat cards
    const statCards = document.querySelectorAll('.stat-history-card');
    statCards.forEach(c => {
        c.classList.remove('ring-2', 'ring-blue-500', 'shadow-md');
    });
    const activeStatCard = document.getElementById(`stat-card-${filterKey}`);
    if (activeStatCard) {
        activeStatCard.classList.add('ring-2', 'ring-blue-500', 'shadow-md');
    }

    applyHistoryFilter();
}

function setDonutView(viewKey) {
    currentDonutView = viewKey;
    
    const btnOutcome = document.getElementById('btn-donut-outcome');
    const btnCategory = document.getElementById('btn-donut-category');
    const legendOutcome = document.getElementById('donut-legend-outcome');
    const legendCategory = document.getElementById('donut-legend-category');
    const chartTitle = document.getElementById('donut-chart-title');

    if (viewKey === 'outcome') {
        if (btnOutcome) btnOutcome.className = 'px-2.5 py-1 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold';
        if (btnCategory) btnCategory.className = 'px-2.5 py-1 rounded-lg transition-all text-slate-500 hover:text-slate-800 font-semibold';
        if (legendOutcome) legendOutcome.classList.remove('hidden');
        if (legendCategory) legendCategory.classList.add('hidden');
        if (chartTitle) chartTitle.textContent = 'สัดส่วนผลการซ่อม';
    } else {
        if (btnOutcome) btnOutcome.className = 'px-2.5 py-1 rounded-lg transition-all text-slate-500 hover:text-slate-800 font-semibold';
        if (btnCategory) btnCategory.className = 'px-2.5 py-1 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold';
        if (legendOutcome) legendOutcome.classList.add('hidden');
        if (legendCategory) legendCategory.classList.remove('hidden');
        if (chartTitle) chartTitle.textContent = 'สัดส่วนประเภทการซ่อม';
    }

    applyHistoryFilter();
}

function setBarView(viewKey) {
    currentBarView = viewKey;
    
    const btnCat = document.getElementById('btn-bar-cat');
    const btnTime = document.getElementById('btn-bar-time');
    const btnStat = document.getElementById('btn-bar-stat');

    const activeClass = 'px-2.5 py-1 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold flex items-center gap-1';
    const inactiveClass = 'px-2.5 py-1 rounded-lg transition-all text-slate-500 hover:text-slate-800 font-semibold flex items-center gap-1';

    if (btnCat) btnCat.className = (viewKey === 'category') ? activeClass : inactiveClass;
    if (btnTime) btnTime.className = (viewKey === 'timeline') ? activeClass : inactiveClass;
    if (btnStat) btnStat.className = (viewKey === 'status') ? activeClass : inactiveClass;

    applyHistoryFilter();
}

function resetHistoryFilter() {
    currentHistoryTimeMode = 'all';
    currentHistoryFilter = 'all';
    
    const searchInput = document.getElementById('history-search-input');
    if (searchInput) searchInput.value = '';
    
    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.classList.add('hidden');

    const dateInput = document.getElementById('history-filter-date');
    if (dateInput && dateInput._flatpickr) {
        dateInput._flatpickr.setDate(new Date());
    }

    setHistoryTimeMode('all');
    setHistoryFilter('all');
    setBarView('category');
}

function applyHistoryFilter() {
    const allData = window.historyJobsData || [];
    const dateEl = document.getElementById('history-filter-date');
    const selectedDate = dateEl?.getAttribute('data-date-ymd') || dateEl?.value;

    // For month mode: read month select (1-12) and dedicated month-year select
    const selectedMonth = parseInt(document.getElementById('history-filter-month')?.value) || (new Date().getMonth() + 1);
    const monthYear     = parseInt(document.getElementById('history-filter-month-year')?.value) || new Date().getFullYear();
    // For year mode: read year select
    const yearModeYear  = parseInt(document.getElementById('history-filter-year')?.value) || new Date().getFullYear();

    // selectedYear depends on mode
    const selectedYear = (currentHistoryTimeMode === 'month') ? monthYear : yearModeYear;
    const searchKeyword = document.getElementById('history-search-input')?.value.trim().toLowerCase() || '';

    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.classList.toggle('hidden', searchKeyword === '');

    // 1. Filter dataset by selected time mode
    const timeFiltered = allData.filter(item => {
        if (currentHistoryTimeMode === 'day') {
            return item.date_ymd === selectedDate;
        } else if (currentHistoryTimeMode === 'month') {
            return item.year === selectedYear && item.month === selectedMonth;
        } else if (currentHistoryTimeMode === 'year') {
            return item.year === selectedYear;
        }
        return true; // 'all'
    });

    // 2. Calculate KPI counts from timeFiltered
    const totalCount = timeFiltered.length;
    const repairableCount = timeFiltered.filter(i => i.reparability === 'repairable').length;
    const inprogressCount = timeFiltered.filter(i => ['กำลังดำเนินการ', 'กำลังซ่อม', 'รอรับงาน'].includes(i.status)).length;
    const partsCount = timeFiltered.filter(i => ['รออะไหล่', 'รอเปลี่ยนอะไหล่', 'รออนุมัติงบประมาณ', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ'].includes(i.status) || (i.budget > 0 && i.reparability !== 'repairable')).length;
    const unrepairableOnlyCount = timeFiltered.filter(i => i.status === 'ไม่สามารถซ่อมได้' || i.status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้').length;
    const unrepairableCount = timeFiltered.filter(i => i.reparability === 'unrepairable_parts').length;
    const hwCount = timeFiltered.filter(i => i.category === 'hardware').length;
    const swCount = timeFiltered.filter(i => i.category === 'software').length;
    const netCount = timeFiltered.filter(i => i.category === 'network_other').length;

    // 3. Update Stat Cards DOM
    const elTotal = document.getElementById('stat-total-count');
    const elRepairable = document.getElementById('stat-repairable-count');
    const elInprogress = document.getElementById('stat-inprogress-count');
    const elParts = document.getElementById('stat-parts-count');
    const elUnrepairable = document.getElementById('stat-unrepairable-count');
    const elUnrepairableOnly = document.getElementById('stat-unrepairable-only-count');
    const elHw = document.getElementById('stat-hw-count');
    const elSw = document.getElementById('stat-sw-count');
    const elNet = document.getElementById('stat-net-count');

    if (elTotal) elTotal.textContent = totalCount;
    if (elRepairable) elRepairable.textContent = repairableCount;
    if (elInprogress) elInprogress.textContent = inprogressCount;
    if (elParts) elParts.textContent = partsCount;
    if (elUnrepairable) elUnrepairable.textContent = (partsCount > 0 ? partsCount : unrepairableCount);
    if (elUnrepairableOnly) elUnrepairableOnly.textContent = (unrepairableOnlyCount > 0 ? unrepairableOnlyCount : unrepairableCount);
    if (elHw) elHw.textContent = hwCount;
    if (elSw) elSw.textContent = swCount;
    if (elNet) elNet.textContent = netCount;

    const repPct = totalCount > 0 ? ((repairableCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const unrepPct = totalCount > 0 ? ((unrepairableCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const hwPct = totalCount > 0 ? ((hwCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const swPct = totalCount > 0 ? ((swCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const netPct = totalCount > 0 ? ((netCount / totalCount) * 100).toFixed(1) + '%' : '0%';

    const elRepPct = document.getElementById('stat-repairable-pct');
    const elUnrepPct = document.getElementById('stat-unrepairable-pct');
    const elHwPct = document.getElementById('stat-hw-pct');
    const elSwPct = document.getElementById('stat-sw-pct');
    const elNetPct = document.getElementById('stat-net-pct');
    if (elRepPct) elRepPct.textContent = repPct;
    if (elUnrepPct) elUnrepPct.textContent = unrepPct;
    if (elHwPct) elHwPct.textContent = hwPct;
    if (elSwPct) elSwPct.textContent = swPct;
    if (elNetPct) elNetPct.textContent = netPct;

    // Donut bottom numbers
    const dRep = document.getElementById('donut-repairable-count');
    const dUnrep = document.getElementById('donut-unrepairable-count');
    const dHw = document.getElementById('donut-hw-count');
    const dSw = document.getElementById('donut-sw-count');
    const dNet = document.getElementById('donut-net-count');
    if (dRep) dRep.textContent = repairableCount + ' รายการ';
    if (dUnrep) dUnrep.textContent = unrepairableCount + ' รายการ';
    if (dHw) dHw.textContent = hwCount + ' รายการ';
    if (dSw) dSw.textContent = swCount + ' รายการ';
    if (dNet) dNet.textContent = netCount + ' รายการ';

    // Filter chips counts
    const bAll = document.getElementById('badge-count-all');
    const bRep = document.getElementById('badge-count-repairable');
    const bProg = document.getElementById('badge-count-inprogress');
    const bParts = document.getElementById('badge-count-parts');
    const bUnrep = document.getElementById('badge-count-unrepairable');
    const bHw = document.getElementById('badge-count-hw');
    const bSw = document.getElementById('badge-count-sw');
    const bNet = document.getElementById('badge-count-net');
    if (bAll) bAll.textContent = totalCount;
    if (bRep) bRep.textContent = repairableCount;
    if (bProg) bProg.textContent = inprogressCount;
    if (bParts) bParts.textContent = (partsCount > 0 ? partsCount : unrepairableCount);
    if (bUnrep) bUnrep.textContent = (unrepairableOnlyCount > 0 ? unrepairableOnlyCount : unrepairableCount);
    if (bHw) bHw.textContent = hwCount;
    if (bSw) bSw.textContent = swCount;
    if (bNet) bNet.textContent = netCount;

    // Period Label
    const periodLabel = document.getElementById('stat-period-label');
    const barTitle = document.getElementById('chart-bar-title');
    const barSub = document.getElementById('chart-bar-subtitle');

    if (currentHistoryTimeMode === 'all') {
        if (periodLabel) periodLabel.textContent = 'ข้อมูลทั้งหมดในระบบ';
        if (barTitle) barTitle.textContent = 'สถิติเปรียบเทียบผลการซ่อมและหมวดหมู่ (ทั้งหมด)';
        if (barSub) barSub.textContent = 'สรุปภาพรวมแยกตามเดือนตลอดทั้งระบบ';
    } else if (currentHistoryTimeMode === 'day') {
        let dateDisplay = selectedDate || 'วันนี้';
        if (selectedDate) {
            const parts = selectedDate.split('-');
            if (parts.length === 3) {
                const thYear = parseInt(parts[0]) + 543;
                dateDisplay = `${parts[2]}/${parts[1]}/${thYear}`;
            }
        }
        if (periodLabel) periodLabel.textContent = `ประจำวันที่ ${dateDisplay}`;
        if (barTitle) barTitle.textContent = `สถิติประจำวันที่ ${dateDisplay}`;
        if (barSub) barSub.textContent = 'เปรียบเทียบสถิติแต่ละประเภทในวันนี้';
    } else if (currentHistoryTimeMode === 'month') {
        const monthNames = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const mName = monthNames[selectedMonth] || '';
        const thYear = selectedYear + 543;
        if (periodLabel) periodLabel.textContent = `ประจำเดือน ${mName} ${thYear}`;
        if (barTitle) barTitle.textContent = `สถิติรายสัปดาห์ เดือน ${mName} ${thYear}`;
        if (barSub) barSub.textContent = 'เปรียบเทียบงานซ่อมแต่ละช่วงสัปดาห์ในเดือน';
    } else if (currentHistoryTimeMode === 'year') {
        const thYear = selectedYear + 543;
        if (periodLabel) periodLabel.textContent = `ประจำปี พ.ศ. ${thYear}`;
        if (barTitle) barTitle.textContent = `สถิติรายเดือน ประจำปี พ.ศ. ${thYear}`;
        if (barSub) barSub.textContent = 'เปรียบเทียบงานซ่อมรายเดือน (ม.ค. - ธ.ค.)';
    }

    // 4. Render Bar Chart (Redesigned with Real Data & Multiple View Modes)
    const barCanvas = document.getElementById('historyBarChart');
    if (barCanvas && typeof Chart !== 'undefined') {
        let barLabels = [];
        let datasets = [];
        const legendContainer = document.getElementById('bar-legend-container');

        // Calculate Category Breakdown (Real Data: Hardware, Software, Network)
        // Calculate Asset Kind Breakdown (STRICT REAL DATA: Only real kinds from asset_kinds master table)
        const kindStats = {};
        let totalKindRep = 0;
        let totalKindUnrep = 0;

        const validMasterKinds = (window.masterAssetKinds && Array.isArray(window.masterAssetKinds) && window.masterAssetKinds.length > 0)
            ? window.masterAssetKinds
            : ['โน้ตบุ๊ค', 'สายแลน', 'OS ซอฟต์แวร์สำนักงาน', 'เมาส์', 'แป้นพิมพ์', 'wifi', 'IOT', 'เซ็นเซอร์', 'OS ระบบปฎิบัติการ', 'อุปกรณ์กระจายสัญญาณ', 'การ์จจอ'];

        function normalizeKind(name) {
            if (!name || name === '-' || name === 'อุปกรณ์ทั่วไป') return null;
            let s = String(name).trim();
            if (s === 'OS ระบบปฏิบัติการ') s = 'OS ระบบปฎิบัติการ';
            const found = validMasterKinds.find(k => k.toLowerCase() === s.toLowerCase());
            return found || null;
        }

        // Status Counts
        let stDone = 0, stParts = 0, stProgress = 0, stPending = 0;

        timeFiltered.forEach(item => {
            const k = normalizeKind(item.asset_kind);
            // STRICT FILTER: Only master kinds that actually exist in the real system
            if (k) {
                if (!kindStats[k]) {
                    kindStats[k] = { rep: 0, unrep: 0, total: 0 };
                }
                kindStats[k].total++;
                if (item.reparability === 'repairable') {
                    kindStats[k].rep++;
                    totalKindRep++;
                } else {
                    kindStats[k].unrep++;
                    totalKindUnrep++;
                }
            }

            // Status grouping
            const s = (item.status || '').trim();
            if (s.includes('เสร็จ') || s.includes('สำเร็จ')) {
                stDone++;
            } else if (s.includes('อะไหล่') || s.includes('งบ') || s.includes('ไม่สามารถ')) {
                stParts++;
            } else if (s.includes('กำลัง') || s.includes('ตรวจ')) {
                stProgress++;
            } else {
                stPending++;
            }
        });

        // Sort kinds by total desc
        const sortedKinds = Object.keys(kindStats).sort((a, b) => kindStats[b].total - kindStats[a].total);

        // Fallback default list if no items
        if (sortedKinds.length === 0) {
            ['โน้ตบุ๊ค', 'เมาส์', 'OS ระบบปฎิบัติการ', 'แป้นพิมพ์', 'OS ซอฟต์แวร์สำนักงาน'].forEach(k => {
                sortedKinds.push(k);
                kindStats[k] = { rep: 0, unrep: 0, total: 0 };
            });
        }

        // Dynamic Chart Views
        if (currentBarView === 'category' || currentBarView === 'kind') {
            if (legendContainer) {
                legendContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 
                        ซ่อมได้ <span class="font-bold ml-0.5">${totalKindRep}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100 text-rose-800">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> 
                        สั่งอะไหล่/ซ่อมไม่ได้ <span class="font-bold ml-0.5">${totalKindUnrep}</span>
                    </span>
                `;
            }

            barLabels = sortedKinds;
            datasets = [
                {
                    label: 'สามารถซ่อมได้',
                    data: sortedKinds.map(k => kindStats[k].rep),
                    backgroundColor: '#10B981',
                    hoverBackgroundColor: '#059669',
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: sortedKinds.length > 5 ? 0.6 : 0.45,
                    categoryPercentage: 0.6
                },
                {
                    label: 'สั่งอะไหล่/ซ่อมไม่ได้',
                    data: sortedKinds.map(k => kindStats[k].unrep),
                    backgroundColor: '#F43F5E',
                    hoverBackgroundColor: '#E11D48',
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: sortedKinds.length > 5 ? 0.6 : 0.45,
                    categoryPercentage: 0.6
                }
            ];

            if (barTitle) barTitle.textContent = 'สถิติเปรียบเทียบผลการซ่อมตามชนิดทรัพย์สิน';
            if (barSub) barSub.textContent = '';

        } else if (currentBarView === 'timeline') {
            if (legendContainer) {
                legendContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 
                        ซ่อมได้ <span class="font-bold ml-0.5">${repairableCount}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100 text-rose-800">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> 
                        สั่งอะไหล่ <span class="font-bold ml-0.5">${unrepairableCount}</span>
                    </span>
                `;
            }

            if (currentHistoryTimeMode === 'day') {
                barLabels = ['เช้า (08:00 - 12:00)', 'บ่าย (12:00 - 16:00)', 'เย็น (16:00 - 20:00)'];
                let tRep = [0, 0, 0], tUnrep = [0, 0, 0];
                timeFiltered.forEach(item => {
                    let hour = 10;
                    if (item.created_at && item.created_at.includes(' ')) {
                        hour = parseInt(item.created_at.split(' ')[1].split(':')[0]) || 10;
                    }
                    let idx = hour < 12 ? 0 : (hour < 16 ? 1 : 2);
                    if (item.reparability === 'repairable') tRep[idx]++;
                    else tUnrep[idx]++;
                });
                datasets = [
                    { label: 'สามารถซ่อมได้', data: tRep, backgroundColor: '#10B981', borderRadius: 6, barPercentage: 0.5, categoryPercentage: 0.6 },
                    { label: 'สั่งอะไหล่/ซ่อมไม่ได้', data: tUnrep, backgroundColor: '#F43F5E', borderRadius: 6, barPercentage: 0.5, categoryPercentage: 0.6 }
                ];
            } else if (currentHistoryTimeMode === 'month') {
                barLabels = ['สัปดาห์ 1 (1-7)', 'สัปดาห์ 2 (8-14)', 'สัปดาห์ 3 (15-21)', 'สัปดาห์ 4 (22-สิ้นเดือน)'];
                let wRep = [0, 0, 0, 0], wUnrep = [0, 0, 0, 0];
                timeFiltered.forEach(item => {
                    let wIdx = item.day <= 7 ? 0 : (item.day <= 14 ? 1 : (item.day <= 21 ? 2 : 3));
                    if (item.reparability === 'repairable') wRep[wIdx]++;
                    else wUnrep[wIdx]++;
                });
                datasets = [
                    { label: 'สามารถซ่อมได้', data: wRep, backgroundColor: '#10B981', borderRadius: 6, barPercentage: 0.5, categoryPercentage: 0.6 },
                    { label: 'สั่งอะไหล่/ซ่อมไม่ได้', data: wUnrep, backgroundColor: '#F43F5E', borderRadius: 6, barPercentage: 0.5, categoryPercentage: 0.6 }
                ];
            } else {
                barLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                let mRep = new Array(12).fill(0), mUnrep = new Array(12).fill(0);
                timeFiltered.forEach(item => {
                    const mIdx = item.month - 1;
                    if (mIdx >= 0 && mIdx < 12) {
                        if (item.reparability === 'repairable') mRep[mIdx]++;
                        else mUnrep[mIdx]++;
                    }
                });
                datasets = [
                    { label: 'สามารถซ่อมได้', data: mRep, backgroundColor: '#10B981', borderRadius: 6, barPercentage: 0.55, categoryPercentage: 0.7 },
                    { label: 'สั่งอะไหล่/ซ่อมไม่ได้', data: mUnrep, backgroundColor: '#F43F5E', borderRadius: 6, barPercentage: 0.55, categoryPercentage: 0.7 }
                ];
            }

            if (barTitle) barTitle.textContent = 'แนวโน้มผลการซ่อมตามช่วงเวลา';
            if (barSub) barSub.textContent = 'เปรียบเทียบงานซ่อมได้และสั่งอะไหล่ในแต่ละช่วงเวลา';

        } else if (currentBarView === 'status') {
            if (legendContainer) {
                legendContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-600 font-semibold bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> เสร็จสิ้น (${stDone})
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-600 font-semibold bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> รออะไหล่ (${stParts})
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-600 font-semibold bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> กำลังซ่อม (${stProgress})
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-600 font-semibold bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> รอมอบหมาย (${stPending})
                    </span>
                `;
            }

            barLabels = ['เสร็จสิ้น', 'รออะไหล่/งบประมาณ', 'กำลังดำเนินการซ่อม', 'รอมอบหมาย/รอรับเรื่อง'];
            datasets = [
                {
                    label: 'จำนวนงาน',
                    data: [stDone, stParts, stProgress, stPending],
                    backgroundColor: ['#10B981', '#F59E0B', '#3B82F6', '#94A3B8'],
                    hoverBackgroundColor: ['#059669', '#D97706', '#2563EB', '#64748B'],
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: 0.45,
                    categoryPercentage: 0.6
                }
            ];

            if (barTitle) barTitle.textContent = 'สถิติเปรียบเทียบตามสถานะงานซ่อมจริง';
            if (barSub) barSub.textContent = 'แสดงการกระจายตัวของสถานะงานซ่อมทั้งหมดตามข้อมูลจริงในระบบ';
        }

        const barData = {
            labels: barLabels,
            datasets: datasets
        };

        const barOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleFont: { family: 'Prompt', size: 12, weight: 'bold' },
                    bodyFont: { family: 'Prompt', size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || context.label || '';
                            const val = context.parsed.y !== undefined ? context.parsed.y : context.parsed;
                            return ` ${label}: ${val} รายการ`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Prompt', size: 11, weight: '600' }, color: '#475569' }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: { family: 'Prompt', size: 11 },
                        color: '#94a3b8',
                        stepSize: 1
                    },
                    grid: { color: 'rgba(226, 232, 240, 0.6)' }
                }
            }
        };

        if (historyBarChartInstance) {
            historyBarChartInstance.data = barData;
            historyBarChartInstance.options = barOptions;
            historyBarChartInstance.update();
        } else {
            historyBarChartInstance = new Chart(barCanvas.getContext('2d'), {
                type: 'bar',
                data: barData,
                options: barOptions
            });
        }
    }

    // 5. Render Donut Chart (Switches between 'outcome' and 'category')
    const donutCanvas = document.getElementById('historyDonutChart');
    if (donutCanvas && typeof Chart !== 'undefined') {
        let donutLabels = [];
        let donutData = [];
        let donutColors = [];

        if (currentDonutView === 'outcome') {
            donutLabels = ['สามารถซ่อมได้', 'ซ่อมไม่ได้/สั่งอะไหล่'];
            donutData = [repairableCount, unrepairableCount];
            donutColors = ['#10B981', '#F43F5E'];
        } else {
            donutLabels = ['ฮาร์ดแวร์', 'ซอฟต์แวร์', 'เครือข่าย/อื่นๆ'];
            donutData = [hwCount, swCount, netCount];
            donutColors = ['#4F46E5', '#9333EA', '#0D9488'];
        }

        const totalActive = donutData.reduce((a, b) => a + b, 0);

        if (totalActive === 0) {
            donutLabels = ['ยังไม่มีข้อมูล'];
            donutData = [1];
            donutColors = ['#E2E8F0'];
        }

        const donutConfig = {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutData,
                    backgroundColor: donutColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (totalActive === 0) return ' ยังไม่มีข้อมูล';
                                const val = context.raw || 0;
                                const pct = ((val / totalActive) * 100).toFixed(1);
                                return ` ${context.label}: ${val} รายการ (${pct}%)`;
                            }
                        },
                        titleFont: { family: 'Prompt' },
                        bodyFont: { family: 'Prompt' }
                    }
                }
            }
        };

        if (historyDonutChartInstance) {
            historyDonutChartInstance.data = donutConfig.data;
            historyDonutChartInstance.options = donutConfig.options;
            historyDonutChartInstance.update();
        } else {
            historyDonutChartInstance = new Chart(donutCanvas.getContext('2d'), donutConfig);
        }
    }

    // 6. Filter history job cards in DOM
    let visibleCount = 0;
    const cards = document.querySelectorAll('.history-job-card');
    cards.forEach(card => {
        const cCategory = card.getAttribute('data-category');
        const cReparability = card.getAttribute('data-reparability');
        const cDate = card.getAttribute('data-date');
        const cYear = parseInt(card.getAttribute('data-year'));
        const cMonth = parseInt(card.getAttribute('data-month'));
        const cKeyword = card.getAttribute('data-keyword') || '';

        // Match time
        let matchTime = true;
        if (currentHistoryTimeMode === 'day') {
            matchTime = (cDate === selectedDate);
        } else if (currentHistoryTimeMode === 'month') {
            matchTime = (cYear === selectedYear && cMonth === selectedMonth);
        } else if (currentHistoryTimeMode === 'year') {
            matchTime = (cYear === selectedYear);
        }

        // Match filter
        let matchFilter = true;
        const cStatus = card.getAttribute('data-status') || '';
        if (currentHistoryFilter === 'all') {
            matchFilter = true;
        } else if (currentHistoryFilter === 'repairable') {
            matchFilter = (cReparability === 'repairable');
        } else if (currentHistoryFilter === 'inprogress') {
            matchFilter = ['กำลังดำเนินการ', 'กำลังซ่อม', 'รอรับงาน'].includes(cStatus);
        } else if (currentHistoryFilter === 'unrepairable_parts') {
            matchFilter = (cReparability === 'unrepairable_parts' || ['รออะไหล่', 'รอเปลี่ยนอะไหล่', 'รออนุมัติงบประมาณ', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ'].includes(cStatus));
        } else if (currentHistoryFilter === 'unrepairable') {
            matchFilter = (cStatus === 'ไม่สามารถซ่อมได้' || cStatus === 'ไม่อนุมัติงบ/ซ่อมไม่ได้' || cReparability === 'unrepairable_parts');
        } else if (currentHistoryFilter === 'hardware' || currentHistoryFilter === 'software' || currentHistoryFilter === 'network_other') {
            matchFilter = (cCategory === currentHistoryFilter);
        }

        // Match search
        let matchSearch = (searchKeyword === '' || cKeyword.includes(searchKeyword));

        if (matchTime && matchFilter && matchSearch) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    // Filter Table rows
    const rows = document.querySelectorAll('.history-row');
    let visibleRows = 0;
    rows.forEach(row => {
        const cCategory = row.getAttribute('data-category');
        const cReparability = row.getAttribute('data-reparability');
        const cDate = row.getAttribute('data-date');
        const cYear = parseInt(row.getAttribute('data-year'));
        const cMonth = parseInt(row.getAttribute('data-month'));
        const cKeyword = row.getAttribute('data-keyword') || '';

        // Match time
        let matchTime = true;
        if (currentHistoryTimeMode === 'day') {
            matchTime = (cDate === selectedDate);
        } else if (currentHistoryTimeMode === 'month') {
            matchTime = (cYear === selectedYear && cMonth === selectedMonth);
        } else if (currentHistoryTimeMode === 'year') {
            matchTime = (cYear === selectedYear);
        }

        // Match filter
        let matchFilter = true;
        const rStatus = row.getAttribute('data-status') || '';
        if (currentHistoryFilter === 'all') {
            matchFilter = true;
        } else if (currentHistoryFilter === 'repairable') {
            matchFilter = (cReparability === 'repairable');
        } else if (currentHistoryFilter === 'inprogress') {
            matchFilter = ['กำลังดำเนินการ', 'กำลังซ่อม', 'รอรับงาน'].includes(rStatus);
        } else if (currentHistoryFilter === 'unrepairable_parts') {
            matchFilter = (cReparability === 'unrepairable_parts' || ['รออะไหล่', 'รอเปลี่ยนอะไหล่', 'รออนุมัติงบประมาณ', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ'].includes(rStatus));
        } else if (currentHistoryFilter === 'unrepairable') {
            matchFilter = (rStatus === 'ไม่สามารถซ่อมได้' || rStatus === 'ไม่อนุมัติงบ/ซ่อมไม่ได้' || cReparability === 'unrepairable_parts');
        } else if (currentHistoryFilter === 'hardware' || currentHistoryFilter === 'software' || currentHistoryFilter === 'network_other') {
            matchFilter = (cCategory === currentHistoryFilter);
        }

        // Match search
        let matchSearch = (searchKeyword === '' || cKeyword.includes(searchKeyword));

        if (matchTime && matchFilter && matchSearch) {
            row.classList.remove('hidden');
            visibleRows++;
        } else {
            row.classList.add('hidden');
        }
    });

    const emptyRowEl = document.getElementById('history-empty-row');
    if (emptyRowEl) {
        if (visibleRows === 0 && rows.length > 0) {
            emptyRowEl.classList.remove('hidden');
        } else {
            emptyRowEl.classList.add('hidden');
        }
    }

    const emptyFilterEl = document.getElementById('history-empty-filter');
    if (emptyFilterEl) {
        if (visibleCount === 0 && cards.length > 0) {
            emptyFilterEl.classList.remove('hidden');
        } else {
            emptyFilterEl.classList.add('hidden');
        }
    }
}

window.onHistoryTabOpened = function() {
    if (typeof setTeacherHistoryViewMode === 'function') {
        setTeacherHistoryViewMode(currentTeacherHistoryViewMode || 'card');
    }
    if (!historyBarChartInstance || !historyDonutChartInstance) {
        applyHistoryFilter();
    } else {
        setTimeout(() => {
            if (historyBarChartInstance) historyBarChartInstance.resize();
            if (historyDonutChartInstance) historyDonutChartInstance.resize();
        }, 60);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Thai Flatpickr for History Date Picker (วัน/เดือน/ปี พ.ศ. ตามหลักประเทศไทย)
    if (typeof flatpickr !== 'undefined') {
        const historyDateInput = document.getElementById('history-filter-date');
        if (historyDateInput) {
            flatpickr(historyDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                altInputClass: 'w-36 text-xs bg-slate-50 border border-slate-200 rounded-xl pl-3 pr-8 py-2 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs',
                locale: 'th',
                defaultDate: historyDateInput.getAttribute('data-date-ymd') || historyDateInput.value || 'today',
                formatDate: (date, format, locale) => {
                    if (format === 'Y-m-d') {
                        const d = date.getDate().toString().padStart(2, '0');
                        const m = (date.getMonth() + 1).toString().padStart(2, '0');
                        const y = date.getFullYear();
                        return `${y}-${m}-${d}`;
                    }
                    const d = date.getDate().toString().padStart(2, '0');
                    const m = (date.getMonth() + 1).toString().padStart(2, '0');
                    const y = date.getFullYear() + 543;
                    return `${d}/${m}/${y}`;
                },
                onOpen: function(selectedDates, dateStr, instance) {
                    instance.thaiYearInterval = setInterval(() => {
                        if (instance.currentYearElement && instance.currentYearElement.value === instance.currentYear.toString()) {
                            instance.currentYearElement.value = instance.currentYear + 543;
                        }
                    }, 50);
                },
                onClose: function(selectedDates, dateStr, instance) {
                    if (instance.thaiYearInterval) {
                        clearInterval(instance.thaiYearInterval);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates && selectedDates.length > 0) {
                        const d = selectedDates[0];
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        historyDateInput.setAttribute('data-date-ymd', `${y}-${m}-${day}`);
                    }
                    if (typeof applyHistoryFilter === 'function') {
                        applyHistoryFilter();
                    }
                }
            });
        }
    }

    if (window.historyJobsData) {
        applyHistoryFilter();
    }

    if (typeof syncDeviceSelectState === 'function') {
        syncDeviceSelectState();
    }
});
</script>
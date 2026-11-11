<script>
function switchInnerTab(tabName) {
    // ซ่อนเนื้อหาตารางทั้งหมด
    document.querySelectorAll('.inner-tbody').forEach(tbody => {
        tbody.classList.add('hidden');
    });
    // แสดงตารางที่เลือก
    const activeTbody = document.getElementById('tbody-' + tabName);
    if (activeTbody) {
        activeTbody.classList.remove('hidden');
    }

    // ล้าง style แท็บที่ใช้งานอยู่ทั้งหมด
    document.querySelectorAll('.inner-tab').forEach(tab => {
        tab.classList.remove('active', 'border-blue-600', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-slate-500');
    });
    
    // ตั้งค่า style แท็บที่เลือกให้เป็น active
    const activeTab = document.getElementById('inner-tab-' + tabName);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-slate-500');
        activeTab.classList.add('active', 'border-blue-600', 'text-blue-600');
    }
}

// ฟังก์ชันสลับแท็บรายการขอเบิก
function switchReqTab(tabName) {
    // ซ่อนเนื้อหาตารางทั้งหมด
    document.querySelectorAll('.req-tbody').forEach(tbody => {
        tbody.classList.add('hidden');
    });
    // แสดงตารางที่เลือก
    const activeTbody = document.getElementById('req-tbody-' + tabName);
    if (activeTbody) {
        activeTbody.classList.remove('hidden');
    }

    // รีเซ็ตแท็บ
    document.querySelectorAll('.req-tab').forEach(tab => {
        tab.classList.remove('active', 'border-blue-600', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-slate-500');
    });
    
    // ตั้งค่าแท็บที่เลือก
    const activeTab = document.getElementById('req-tab-' + tabName);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-slate-500');
        activeTab.classList.add('active', 'border-blue-600', 'text-blue-600');
    }
}
// ฟังก์ชันสำหรับกรองข้อมูลเมื่อคลิกการ์ดในแท็บขอเบิก
function filterRequisitionHead(status, element) {
    if (element) {
        // Remove active class from all stat cards in the requisition tab
        const tabRequisition = document.getElementById('tab-requisition');
        if (tabRequisition) {
            const cards = tabRequisition.querySelectorAll('.stat-tab-card');
            cards.forEach(card => {
                card.classList.remove('active-stat');
            });
            // Add active class to clicked card
            element.classList.add('active-stat');
            
            // กรองข้อมูลในตาราง (สมมติว่าแถวมีคลาส .req-row และเก็บสถานะไว้ใน data-status หรือข้อความในแถว)
            const rows = tabRequisition.querySelectorAll('tbody tr:not(.empty-row)'); // ยกเว้นแถวที่บอกว่าไม่มีข้อมูล
            let hasVisibleRows = false;
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                // ถ้าสถานะเป็น 'ทั้งหมด' หรือตรงกับสถานะในแถว
                if (!status || status === 'ทั้งหมด' || text.includes(status.toLowerCase())) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // แสดง/ซ่อนแถว "ไม่มีข้อมูล"
            const emptyRow = tabRequisition.querySelector('.empty-row');
            if (emptyRow) {
                emptyRow.style.display = hasVisibleRows ? 'none' : '';
            }
        }
    }
}

// ทำการคลิก "ทั้งหมด" ในแท็บเบิกอะไหล่เป็นค่าเริ่มต้นเมื่อโหลดหน้า
document.addEventListener('DOMContentLoaded', function() {
    const tabRequisition = document.getElementById('tab-requisition');
    if (tabRequisition) {
        const cards = tabRequisition.querySelectorAll('.stat-tab-card');
        if (cards.length > 0) {
            const defaultCard = cards[cards.length - 1]; // เลือกการ์ด "ทั้งหมด" (ใบสุดท้าย)
            filterRequisitionHead('ทั้งหมด', defaultCard);
        }
    }
});

// ตัวแปรและฟังก์ชันแบ่งหน้า (Pagination) สำหรับแท็บมอบหมายงาน
let headAssignCurrentPage = 1;
const headAssignPageSize = 7;
let currentHeadAssignStatus = 'ทั้งหมด';

function isAssignRowMatch(row, status) {
    const rowStatus = row.getAttribute('data-status');
    if (status === 'ทั้งหมด') return true;
    if (status === 'มอบหมายแล้ว' && rowStatus !== 'รอมอบหมาย') return true;
    if ((status === 'กำลังซ่อม' || status === 'กำลังดำเนินการ') && (rowStatus === 'กำลังซ่อม' || rowStatus === 'กำลังดำเนินการ' || rowStatus === 'รอรับงาน')) return true;
    if (status === 'รอเปลี่ยนอะไหล่' && (rowStatus === 'รอเปลี่ยนอะไหล่' || rowStatus === 'รออะไหล่' || rowStatus === 'รอจัดซื้ออะไหล่' || rowStatus === 'รออนุมัติงบประมาณ' || rowStatus === 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ')) return true;
    if (status === rowStatus || (status === 'รอรับมอบหมาย' && rowStatus === 'รอมอบหมาย')) return true;
    return false;
}

function renderHeadAssignPaginationAndItems() {
    const tabPending = document.getElementById('tab-pending');
    if (!tabPending) return;

    const rows = tabPending.querySelectorAll('tbody tr.assign-row');
    const matchingRows = [];
    rows.forEach(row => {
        if (isAssignRowMatch(row, currentHeadAssignStatus)) {
            matchingRows.push(row);
        }
    });

    const totalMatching = matchingRows.length;
    const totalPages = Math.ceil(totalMatching / headAssignPageSize) || 1;

    if (headAssignCurrentPage > totalPages) headAssignCurrentPage = totalPages;
    if (headAssignCurrentPage < 1) headAssignCurrentPage = 1;

    const startIndex = (headAssignCurrentPage - 1) * headAssignPageSize;
    const endIndex = startIndex + headAssignPageSize;

    // แสดง/ซ่อนแถวตามหน้าปัจจุบัน
    rows.forEach(row => {
        const matchIdx = matchingRows.indexOf(row);
        if (matchIdx !== -1 && matchIdx >= startIndex && matchIdx < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // แสดง/ซ่อนแถว "ไม่มีข้อมูล"
    const emptyRow = tabPending.querySelector('.empty-row-assign');
    if (emptyRow) {
        emptyRow.style.display = totalMatching === 0 ? '' : 'none';
    }

    // เรนเดอร์คอนโทรลเลอร์ Pagination
    const infoEl = document.getElementById('head-assign-pagination-info');
    const btnsEl = document.getElementById('head-assign-pagination-buttons');
    const containerEl = document.getElementById('head-assign-pagination-container');

    if (!containerEl) return;

    if (totalMatching === 0) {
        if (infoEl) infoEl.innerHTML = '<span class="text-slate-400">ไม่พบรายการที่ตรงกับเงื่อนไข</span>';
        if (btnsEl) btnsEl.innerHTML = '';
        return;
    }

    const startDisplay = startIndex + 1;
    const endDisplay = Math.min(endIndex, totalMatching);

    if (infoEl) {
        infoEl.innerHTML = `แสดง <span class="font-bold text-slate-700">${startDisplay}</span> ถึง <span class="font-bold text-slate-700">${endDisplay}</span> จากทั้งหมด <span class="font-bold text-slate-700">${totalMatching}</span> รายการ`;
    }

    if (btnsEl) {
        let btnHtml = '';

        // ปุ่มไปซ้าย / ก่อนหน้า
        const isPrevDisabled = headAssignCurrentPage === 1;
        btnHtml += `<button type="button" onclick="goToHeadAssignPage(${headAssignCurrentPage - 1})" ${isPrevDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold transition flex items-center gap-1.5 ${isPrevDisabled ? 'bg-slate-50 text-slate-300 cursor-not-allowed border-slate-100' : 'bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 hover:border-blue-200 cursor-pointer shadow-xs'}">
            <i class="fa-solid fa-chevron-left text-[10px]"></i>
            <span>ก่อนหน้า</span>
        </button>`;

        // ปุ่มหมายเลขหน้า
        let pagesToShow = [];
        if (totalPages <= 7) {
            for (let p = 1; p <= totalPages; p++) pagesToShow.push(p);
        } else {
            pagesToShow.push(1);
            if (headAssignCurrentPage > 3) pagesToShow.push('...');
            const startP = Math.max(2, headAssignCurrentPage - 1);
            const endP = Math.min(totalPages - 1, headAssignCurrentPage + 1);
            for (let p = startP; p <= endP; p++) {
                if (!pagesToShow.includes(p)) pagesToShow.push(p);
            }
            if (headAssignCurrentPage < totalPages - 2) pagesToShow.push('...');
            if (!pagesToShow.includes(totalPages)) pagesToShow.push(totalPages);
        }

        pagesToShow.forEach(p => {
            if (p === '...') {
                btnHtml += `<span class="px-2 py-1 text-slate-400 font-bold">...</span>`;
            } else if (p === headAssignCurrentPage) {
                btnHtml += `<button type="button" class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-xs shadow-md shadow-blue-200 cursor-default">${p}</button>`;
            } else {
                btnHtml += `<button type="button" onclick="goToHeadAssignPage(${p})" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 text-slate-600 hover:text-blue-600 border border-slate-200 font-bold text-xs transition cursor-pointer shadow-xs">${p}</button>`;
            }
        });

        // ปุ่มหน้าถัดไป (ไปขวา)
        const isNextDisabled = headAssignCurrentPage === totalPages;
        btnHtml += `<button type="button" onclick="goToHeadAssignPage(${headAssignCurrentPage + 1})" ${isNextDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold transition flex items-center gap-1.5 ${isNextDisabled ? 'bg-slate-50 text-slate-300 cursor-not-allowed border-slate-100' : 'bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 hover:border-blue-200 cursor-pointer shadow-xs'}">
            <span>หน้าถัดไป</span>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>`;

        btnsEl.innerHTML = btnHtml;
    }
}

function goToHeadAssignPage(page) {
    headAssignCurrentPage = page;
    renderHeadAssignPaginationAndItems();
}

// ฟังก์ชันสำหรับกรองข้อมูลเมื่อคลิกการ์ดในแท็บมอบหมายงาน
function filterAssignHead(status, element) {
    if (element) {
        // Remove active class from all stat cards in the assign tab
        const tabPending = document.getElementById('tab-pending');
        if (tabPending) {
            const cards = tabPending.querySelectorAll('.stat-assign-card');
            cards.forEach(card => {
                card.classList.remove('ring-2', 'ring-blue-500', 'border-blue-400', 'shadow-md');
                card.classList.add('border-slate-100');
            });
            // Add active class to clicked card
            element.classList.add('ring-2', 'ring-blue-500', 'border-blue-400', 'shadow-md');
            element.classList.remove('border-slate-100');
            
            currentHeadAssignStatus = status;
            headAssignCurrentPage = 1;
            renderHeadAssignPaginationAndItems();
        }
    }
}

function filterRequisitionHead(status, btn) {
    const tabRequisition = document.getElementById('tab-requisition');
    if (tabRequisition) {
        // อัปเดตสไตล์ของปุ่ม (ลบ shadow ออกจากปุ่มอื่น และเพิ่มให้ปุ่มที่เลือก)
        const cards = tabRequisition.querySelectorAll('.stat-tab-card');
        cards.forEach(card => {
            card.classList.remove('ring-4', 'ring-opacity-50', 'scale-105', 'shadow-md');
            card.classList.add('shadow-sm');
        });
        
        if (btn) {
            btn.classList.remove('shadow-sm');
            btn.classList.add('shadow-md', 'scale-105');
            
            // เพิ่มสี ring ตามสถานะ
            if (status === 'รอพิจารณา') btn.classList.add('ring-4', 'ring-blue-100');
            else if (status === 'อนุมัติแล้ว') btn.classList.add('ring-4', 'ring-emerald-100');
            else if (status === 'ไม่อนุมัติ') btn.classList.add('ring-4', 'ring-red-100');
            else if (status === 'ส่งกลับแก้ไข') btn.classList.add('ring-4', 'ring-slate-200');
            else if (status === 'ทั้งหมด') btn.classList.add('ring-4', 'ring-indigo-100');
        }

        // กรองแถวในตาราง
        const rows = tabRequisition.querySelectorAll('tbody tr:not(.empty-row)');
        let hasVisibleRows = false;
        
        if (rows.length > 0) {
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (status === 'ทั้งหมด' || status === rowStatus) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // แสดง/ซ่อนแถว "ไม่มีข้อมูล"
            const emptyRow = tabRequisition.querySelector('.empty-row');
            if (emptyRow) {
                emptyRow.style.display = hasVisibleRows ? 'none' : '';
            }
        }
    }
}

// ทำการคลิก "ทั้งหมด" เป็นค่าเริ่มต้นเมื่อโหลดหน้า
document.addEventListener('DOMContentLoaded', function() {
    const tabPending = document.getElementById('tab-pending');
    if (tabPending) {
        const cards = tabPending.querySelectorAll('.stat-assign-card');
        if (cards.length > 0) {
            const defaultCard = cards[0]; // เลือกการ์ด "ทั้งหมด" (ใบแรก)
            filterAssignHead('ทั้งหมด', defaultCard);
        }
    }
    
    const tabRequisition = document.getElementById('tab-requisition');
    if (tabRequisition) {
        const cards = tabRequisition.querySelectorAll('.stat-tab-card');
        if (cards.length > 0) {
            const defaultCard = cards[cards.length - 1]; // เลือกการ์ด "ทั้งหมด" (ใบสุดท้าย)
            filterRequisitionHead('ทั้งหมด', defaultCard);
        }
    }
});

function formatPhoneNumber(input) {
    let val = input.value.replace(/\D/g, '');
    if (val.length > 10) val = val.substring(0, 10);
    let formatted = '';
    if (val.length > 0) formatted += val.substring(0, 3);
    if (val.length > 3) formatted += '-' + val.substring(3, 6);
    if (val.length > 6) formatted += '-' + val.substring(6, 10);
    input.value = formatted;
}

document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone_input');
    if (phoneInput) {
        formatPhoneNumber(phoneInput);
    }
});

window.openProfileModal = function() {
    const modal = document.getElementById('modal-head-profile');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
};

window.closeProfileModal = function() {
    const modal = document.getElementById('modal-head-profile');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
};

window.confirmLogout = function() {
    const dropdown = document.getElementById('head-profile-dropdown');
    if (dropdown && !dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('opacity-100', 'scale-100');
        dropdown.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 200);
    }
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
};

window.closeLogoutModal = function() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
};

@if(session('profile_success') || session('success') || session('error') || $errors->any())
document.addEventListener('DOMContentLoaded', () => {
    // If the error or success is from the profile form
    @if(session('profile_success') || $errors->hasAny(['first_name','last_name','email','phone','position','department','current_password','new_password','profile_photo']))
    if (typeof openProfileModal === 'function') {
        openProfileModal();
    }
    @endif
});
@endif

// ฟังก์ชันสลับเปลี่ยนหน้าจอแท็บ
function switchTab(tabId, element) {
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.remove('block');
        el.classList.add('hidden');
    });
    const target = document.getElementById(tabId);
    if (target) {
        target.classList.remove('hidden');
        target.classList.add('block');
    }
    
    // For sidebar tabs
    document.querySelectorAll('.desktop-tab').forEach(el => {
        el.classList.remove('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold');
        el.classList.add('border-transparent', 'text-blue-100/70', 'hover:bg-white/10', 'hover:text-white', 'font-semibold');
    });
    if (!element) {
        element = document.querySelector(`.desktop-tab[onclick*="${tabId}"]`);
    }
    if(element && element.classList.contains('desktop-tab')) {
        element.classList.remove('border-transparent', 'text-blue-100/70', 'hover:bg-white/10', 'hover:text-white', 'font-semibold');
        element.classList.add('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold');
    }

    // บันทึกแท็บที่เลือกเพื่อให้อยู่หน้าเดิมเวลารีเฟรช
    try {
        const url = new URL(window.location);
        const shortName = tabId.replace(/^tab-/, '');
        url.searchParams.set('tab', shortName);
        window.history.replaceState(null, '', url);
        sessionStorage.setItem('active_head_tab', tabId);
    } catch (e) {
        console.error('Error saving tab state:', e);
    }

    if (tabId === 'tab-pending') {
        setTimeout(function() {
            if (typeof renderHeadAssignPaginationAndItems === 'function') renderHeadAssignPaginationAndItems();
        }, 50);
    }

    if (tabId === 'tab-history') {
        setTimeout(function() {
            if (typeof applyHistoryFilter === 'function') applyHistoryFilter();
        }, 50);
    }
}

// ฟังก์ชันสำหรับกระโดดไปหน้าประวัติซ่อมพร้อมฟิลเตอร์ (จากแท็บสรุปงานซ่อม)
function jumpToHistory(status) {
    const sidebarHistoryBtn = document.querySelector('.desktop-tab[onclick*="tab-history"]');
    switchTab('tab-history', sidebarHistoryBtn);
    if (typeof setHeadHistoryView === 'function') {
        setHeadHistoryView('table');
    }
    if (status) {
        if (typeof setHistoryFilter === 'function') {
            if (status === 'เสร็จสิ้น') {
                setHistoryFilter('repairable');
            } else if (status === 'ไม่สามารถซ่อมได้') {
                setHistoryFilter('unrepairable_parts');
            } else {
                setHistoryFilter('all');
                const searchInput = document.getElementById('history-search-input');
                if (searchInput) searchInput.value = status;
                if (typeof applyHistoryFilter === 'function') applyHistoryFilter();
            }
        }
    } else {
        if (typeof setHistoryFilter === 'function') setHistoryFilter('all');
        const searchInput = document.getElementById('history-search-input');
        if (searchInput) searchInput.value = '';
        if (typeof applyHistoryFilter === 'function') applyHistoryFilter();
    }
}

// เปิด-ปิด Modal มอบหมายงานช่าง
function openAssignModal(btn) {
    const id = btn.getAttribute('data-id');
    const code = btn.getAttribute('data-code');
    const deviceName = btn.getAttribute('data-device');
    const assetKind = btn.getAttribute('data-kind') || '-';
    const description = btn.getAttribute('data-desc');
    const location = btn.getAttribute('data-loc');
    const userName = btn.getAttribute('data-user');
    const priority = btn.getAttribute('data-priority');
    const reqDate = btn.getAttribute('data-date');
    const assignee = btn.getAttribute('data-assignee-id');
    const form = document.getElementById('assign-job-form');
    if (form) form.action = `{{ url('/repair-requests') }}/${id}/assign`;
    
    // เติมข้อมูลลงในการ์ดสรุป
    const elCode = document.getElementById('assign-modal-req-code');
    if (elCode) elCode.innerText = `#${code}`;
    
    const elDevice = document.getElementById('assign-modal-device');
    if (elDevice) elDevice.innerText = assetKind;
    
    const elUser = document.getElementById('assign-modal-user');
    if (elUser) elUser.innerText = userName;
    
    const elDate = document.getElementById('assign-modal-date');
    if (elDate) elDate.innerText = reqDate;
    
    const descEl = document.getElementById('assign-modal-desc');
    if (descEl) descEl.innerText = description || '-';
    
    const image = btn.getAttribute('data-image') || '';
    const imgContainer = document.getElementById('assign-modal-image-container');
    const imgLink = document.getElementById('assign-modal-image-link');
    if (imgContainer && imgLink) {
        if (image && image.trim() !== '') {
            imgLink.href = image;
            imgContainer.classList.remove('hidden');
        } else {
            imgLink.href = '#';
            imgContainer.classList.add('hidden');
        }
    }
    
    // Toggle Form / Details
    const formFields = document.getElementById('assign-form-fields');
    const submitBtn = document.getElementById('assign-submit-btn');
    const detailsView = document.getElementById('assigned-details');
    const modalTitle = document.getElementById('assign-modal-title');
    
    if (assignee && assignee !== '') {
        // Already assigned, show details
        if (modalTitle) modalTitle.innerText = 'รายละเอียดการมอบหมายงาน';
        if (formFields) formFields.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'none';
        if (detailsView) {
            detailsView.style.display = 'block';
            document.getElementById('assigned-tech-name').innerText = btn.getAttribute('data-assignee-name') || '-';
            document.getElementById('assigned-time').innerText = btn.getAttribute('data-assigned-at') || '-';
        }
    } else {
        // Not assigned, show assign form
        if (modalTitle) modalTitle.innerText = 'มอบหมายงานซ่อม';
        if (formFields) formFields.style.display = 'block';
        if (submitBtn) submitBtn.style.display = 'flex';
        if (detailsView) detailsView.style.display = 'none';
    }
    
    // Update priority radio buttons
    const radios = document.querySelectorAll('#assign-job-form input[name="priority"]');
    radios.forEach(radio => {
        radio.checked = (radio.value === priority);
    });
    
    // Update technician dropdown if already assigned
    const techSelect = document.getElementById('assign-technician');
    if (techSelect) {
        techSelect.value = assignee || "";
        updateTechWorkloadNotice(techSelect);
    }

    const modal = document.getElementById('assign-job-modal');
    if (modal) {
        modal.classList.remove('hidden');
        const box = modal.querySelector('.bg-white'); // the modal container
        if (box) {
            // trigger reflow
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }
    }
}

function enableEditAssignment() {
    const formFields = document.getElementById('assign-form-fields');
    const submitBtn = document.getElementById('assign-submit-btn');
    const detailsView = document.getElementById('assigned-details');
    const modalTitle = document.getElementById('assign-modal-title');
    if (modalTitle) modalTitle.innerText = 'เปลี่ยนช่างผู้รับผิดชอบ';
    if (formFields) formFields.style.display = 'block';
    if (submitBtn) submitBtn.style.display = 'flex';
    if (detailsView) detailsView.style.display = 'none';
}

function openDetailsModal(btn) {
    try {
        const status = btn.getAttribute('data-status');
    // if not completed or unrepairable, just open the assign modal so they can view info
    if (status !== 'เสร็จสิ้น' && status !== 'ไม่สามารถซ่อมได้') {
        openHeadReqDetailModal(btn);
        return;
    }

    const code = btn.getAttribute('data-code');
    const deviceName = btn.getAttribute('data-device');
    const assetKind = btn.getAttribute('data-kind') || '-';
    const assignee = btn.getAttribute('data-assignee-name');
    const evaluation = btn.getAttribute('data-repair-evaluation') || '-';
    const repairDetails = btn.getAttribute('data-repair-details') || '-';
    const reason = btn.getAttribute('data-unrepairable-reason') || '-';
    const recommend = btn.getAttribute('data-technician-recommendation') || '-';
    
    const elCode = document.getElementById('detail-req-code');
    if (elCode) elCode.innerText = `#${code}`;
    
    const elDevice = document.getElementById('detail-device');
    if (elDevice) elDevice.innerText = assetKind;
    
    const elStatus = document.getElementById('detail-status');
    if (elStatus) {
        elStatus.innerText = status;
        elStatus.className = `text-sm font-bold ${status === 'เสร็จสิ้น' ? 'text-emerald-600' : 'text-red-600'}`;
    }
    
    const elAssignee = document.getElementById('detail-assignee');
    if (elAssignee) elAssignee.innerText = assignee || '-';

    document.getElementById('detail-eval').innerText = evaluation;
    document.getElementById('detail-repair').innerText = repairDetails;
    document.getElementById('detail-recommend').innerText = recommend;

    const unrepairContainer = document.getElementById('unrepairable-container');
    if (status === 'ไม่สามารถซ่อมได้') {
        unrepairContainer.classList.remove('hidden');
        document.getElementById('detail-unrepair-reason').innerText = reason;
    } else {
        unrepairContainer.classList.add('hidden');
    }

    const modal = document.getElementById('details-job-modal');
    if (modal) {
        modal.classList.remove('hidden');
        const box = modal.querySelector('.bg-white');
        if (box) {
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }
    } else {
        alert("Error: modal 'details-job-modal' not found.");
    }
    } catch(e) {
        alert("JS Error: " + e.message);
        console.error(e);
    }
}

function closeDetailsModal() {
    const modal = document.getElementById('details-job-modal');
    if (modal) {
        const box = modal.querySelector('.bg-white');
        if (box) {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200); // 200ms duration for fade-out
    }
}


document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr for start date and due date
    if (typeof flatpickr !== 'undefined') {
        const fpConfig = {
            dateFormat: "Y-m-d", // รูปแบบที่ส่งไป server
            altInput: true,
            altFormat: "d/m/Y", // รูปแบบที่แสดง (จะถูก override ด้วย formatDate)
            locale: "th",
            minDate: "today",
            formatDate: (date, format, locale) => {
                if (format === "Y-m-d") {
                    const d = date.getDate().toString().padStart(2, '0');
                    const m = (date.getMonth() + 1).toString().padStart(2, '0');
                    const y = date.getFullYear();
                    return `${y}-${m}-${d}`;
                }
                // คืนค่าวันที่แสดงผลเป็น ปี พ.ศ. สำหรับ altFormat
                const d = date.getDate().toString().padStart(2, '0');
                const m = (date.getMonth() + 1).toString().padStart(2, '0');
                const y = date.getFullYear() + 543;
                return `${d}/${m}/${y}`;
            },
            onOpen: function(selectedDates, dateStr, instance) {
                // Force Thai year display aggressively to prevent Flatpickr from reverting it
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
            }
        };
        flatpickr("#assign-start-date", fpConfig);
        
        if (document.getElementById('date_range_picker')) {
            flatpickr("#date_range_picker", Object.assign({}, fpConfig, {
                mode: "range",
                minDate: null 
            }));
        }

        // Date Pickers for Summary and Assign Tabs
        flatpickr(".single_date_picker", Object.assign({}, fpConfig, {
            mode: "single",
            minDate: null // Allow past dates for filtering
        }));

        // History Filter Date (วัน/เดือน/ปี พ.ศ. ตามหลักประเทศไทย)
        const historyDateInput = document.getElementById('history-filter-date');
        if (historyDateInput) {
            flatpickr(historyDateInput, Object.assign({}, fpConfig, {
                minDate: null,
                altInputClass: 'w-36 text-xs bg-slate-50 border border-slate-200 rounded-xl pl-3 pr-8 py-2 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs',
                defaultDate: historyDateInput.getAttribute('data-date-ymd') || historyDateInput.value || 'today',
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
            }));
        }
    }
});

function validateAssignForm(e) {
    const select = document.getElementById('assigned_to_select');
    const errorMsg = document.getElementById('assign_error_msg');
    
    if (!select.value) {
        e.preventDefault();
        errorMsg.classList.remove('hidden');
        select.classList.remove('border-slate-200', 'focus:border-blue-500', 'focus:ring-blue-500/10');
        select.classList.add('border-red-500', 'ring-4', 'ring-red-500/10');
        return false;
    }
    return true;
}

function hideAssignError() {
    const select = document.getElementById('assigned_to_select');
    const errorMsg = document.getElementById('assign_error_msg');
    
    if(errorMsg && select) {
        errorMsg.classList.add('hidden');
        select.classList.remove('border-red-500', 'ring-4', 'ring-red-500/10');
        select.classList.add('border-slate-200', 'focus:border-blue-500', 'focus:ring-blue-500/10');
    }
}

// ปิด Modal
function closeAssignModal() {
    const modal = document.getElementById('assign-job-modal');
    if (modal) {
        const box = modal.querySelector('.bg-white');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        if (box) {
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            const hintEl = document.getElementById('tech-workload-hint');
            if (hintEl) {
                hintEl.className = 'hidden';
                hintEl.innerHTML = '';
            }
        }, 300);
    }
}

// แสดงข้อมูลสรุปภาระงานของเจ้าหน้าที่ซ่อมบำรุงที่เลือก
function updateTechWorkloadNotice(selectEl) {
    const hintEl = document.getElementById('tech-workload-hint');
    if (!hintEl) return;
    
    if (!selectEl || !selectEl.value) {
        hintEl.className = 'hidden';
        hintEl.innerHTML = '';
        return;
    }
    
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    if (!selectedOption) return;
    
    const count = parseInt(selectedOption.getAttribute('data-count') || '0', 10);
    const techName = selectedOption.getAttribute('data-name') || selectedOption.text;
    
    hintEl.classList.remove('hidden');
    if (count === 0) {
        hintEl.className = 'mt-2.5 text-xs rounded-xl p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2.5 font-medium animate-in fade-in duration-200 shadow-xs';
        hintEl.innerHTML = `<div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-check"></i></div><div><strong>${techName}</strong> ไม่มีงานค้างในระบบ พร้อมรับงานใหม่ทันที</div>`;
    } else if (count >= 5) {
        hintEl.className = 'mt-2.5 text-xs rounded-xl p-3 bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2.5 font-medium animate-in fade-in duration-200 shadow-xs';
        hintEl.innerHTML = `<div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-triangle-exclamation"></i></div><div><strong>${techName}</strong> มีงานค้างอยู่ <strong>${count}</strong> งาน (ค่อนข้างหนาแน่น)</div>`;
    } else {
        hintEl.className = 'mt-2.5 text-xs rounded-xl p-3 bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-2.5 font-medium animate-in fade-in duration-200 shadow-xs';
        hintEl.innerHTML = `<div class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-clock"></i></div><div><strong>${techName}</strong> กำลังปฏิบัติงานอยู่ <strong>${count}</strong> งาน</div>`;
    }
}

// โหลดกราฟแสดงผลจำลองเชิงปริมาณ (แท็บสรุปงานซ่อม - ครบทั้ง 5 กราฟ)
document.addEventListener('DOMContentLoaded', () => {
    // Chart 1: Status (Doughnut)
    @if($requests->count() > 0)
    const el1 = document.getElementById('chartSummaryStatus');
    if (el1) {
        @php
            $stRorMop = $requests->whereIn('status', ['รอมอบหมาย', 'รอดำเนินการ'])->count();
            $stKamlang = $requests->whereIn('status', ['กำลังซ่อม', 'กำลังดำเนินการ', 'รอรับงาน'])->count();
            $stRorAlai = $requests->whereIn('status', ['รอเปลี่ยนอะไหล่', 'รออะไหล่', 'รอจัดซื้ออะไหล่'])->count();
            $stMaiSamart = $requests->whereIn('status', ['ไม่สามารถซ่อมได้', 'ยกเลิก'])->count();
            $stSetSin = $requests->whereIn('status', ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์'])->count();
            $stTotal = $requests->count() > 0 ? $requests->count() : 1;
        @endphp
        const data1 = [{{ $stRorMop }}, {{ $stKamlang }}, {{ $stRorAlai }}, {{ $stMaiSamart }}, {{ $stSetSin }}];
        const colors1 = ['#f97316', '#6366f1', '#a855f7', '#ef4444', '#10b981'];
        const rawLabels1 = ['รอมอบหมาย', 'กำลังซ่อม', 'รอเปลี่ยนอะไหล่', 'ไม่สามารถซ่อมได้', 'เสร็จสิ้น'];
        const total1 = {{ $stTotal }};
        const labels1 = rawLabels1.map((name, idx) => `${name} ${data1[idx]} (${((data1[idx]*100)/total1).toFixed(2)}%)`);
        
        new Chart(el1, {
            type: 'doughnut',
            data: {
                labels: labels1,
                datasets: [{
                    data: data1,
                    backgroundColor: colors1,
                    borderWidth: 0,
                    cutout: '72%',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: -10 } },
                plugins: {
                    legend: { 
                        position: 'right', 
                        labels: { boxWidth: 8, usePointStyle: true, pointStyle: 'circle', font: { family: 'Prompt', size: 9, weight: 'bold' }, color: '#475569', padding: 8 } 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.raw} รายการ`;
                            }
                        }
                    }
                }
            }
        });
    }
    @endif

    // Chart 2: งานซ่อมแยกตามชนิดทรัพย์สิน (Bar)
    const el2 = document.getElementById('chartSummaryAssetKind');
    let chartSummaryKindInstance = null;
    if (el2) {
        chartSummaryKindInstance = new Chart(el2, {
            type: 'bar',
            data: {
                labels: {!! json_encode($deviceTypeStats->keys()) !!},
                datasets: [{
                    label: 'จำนวนรายการ',
                    data: {!! json_encode($deviceTypeStats->values()) !!},
                    backgroundColor: '#f43f5e',
                    borderRadius: 4,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f8fafc' },
                        border: { display: false },
                        ticks: { font: { family: 'Prompt', size: 10 }, color: '#94a3b8', stepSize: 2, precision: 0 } 
                    },
                    x: { 
                        grid: { display: false },
                        border: { display: false },
                        ticks: { font: { family: 'Prompt', size: 9, weight: 'bold' }, color: '#64748b' } 
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Dynamic Filter for Chart 2 by Asset Category
    window.filterSummaryKindsByCategory = function(selectedCategory) {
        if (!chartSummaryKindInstance || !window.summaryKindsRawData) return;
        let filtered = window.summaryKindsRawData;
        if (selectedCategory && selectedCategory !== 'all') {
            filtered = filtered.filter(item => item.category === selectedCategory);
        }
        const counts = {};
        filtered.forEach(item => {
            counts[item.kind] = (counts[item.kind] || 0) + 1;
        });
        const sorted = Object.keys(counts).sort((a, b) => counts[b] - counts[a]).slice(0, 5);
        chartSummaryKindInstance.data.labels = sorted;
        chartSummaryKindInstance.data.datasets[0].data = sorted.map(k => counts[k]);
        chartSummaryKindInstance.update();
    };

    // Chart 3: งานซ่อมแยกตามประเภททรัพย์สิน (Pie)
    const el3 = document.getElementById('chartSummaryCategory');
    if (el3) {
        const catColorMap = {
            'ฮาร์ดแวร์': '#6366f1',
            'ซอฟต์แวร์': '#ec4899',
            'ระบบเครือข่าย': '#0ea5e9',
            'IOT': '#f59e0b'
        };
        const catLabels = {!! json_encode($deviceCategoryStats->keys()) !!};
        const catColors = catLabels.map(l => catColorMap[l] || '#10b981');

        new Chart(el3, {
            type: 'pie',
            data: {
                labels: catLabels,
                datasets: [{
                    data: {!! json_encode($deviceCategoryStats->values()) !!},
                    backgroundColor: catColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'right', 
                        labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle', font: { family: 'Prompt', size: 10, weight: 'bold' }, color: '#475569', padding: 10 } 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const val = context.parsed || 0;
                                const dataset = context.dataset.data;
                                const total = dataset.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${label}: ${val} รายการ (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Chart 4: ช่างที่รับงานซ่อมสูงสุด (Horizontal Bar)
    const el4 = document.getElementById('chartSummaryTechnician');
    if (el4) {
        new Chart(el4, {
            type: 'bar',
            data: {
                labels: {!! json_encode($technicianStats->keys()) !!},
                datasets: [{
                    label: 'จำนวนงานที่รับ',
                    data: {!! json_encode($technicianStats->values()) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    maxBarThickness: 24
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { 
                        beginAtZero: true, 
                        grid: { color: '#f8fafc' },
                        border: { display: false },
                        ticks: { font: { family: 'Prompt', size: 10 }, color: '#94a3b8', stepSize: 1, precision: 0 } 
                    },
                    y: { 
                        grid: { display: false },
                        border: { display: false },
                        ticks: { font: { family: 'Prompt', size: 10, weight: 'bold' }, color: '#475569' } 
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Chart 5: ฝ่ายที่แจ้งซ่อมสูงสุด (Line)
    const el5 = document.getElementById('chartSummaryDepartment');
    if (el5) {
        const rawDeptKeys = {!! json_encode($departmentStats->keys()) !!};
        const deptLabels = rawDeptKeys.map(d => {
            if (d.includes('เทคโนโลยีสารสนเทศ')) return ['แผนกวิชาเทคโนโลยี', 'สารสนเทศและการสื่อสาร'];
            if (d.includes('ศูนย์ข้อมูล')) return ['งานศูนย์ข้อมูล', 'สารสนเทศ'];
            return d;
        });

        new Chart(el5, {
            type: 'line',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'จำนวนรายการแจ้งซ่อม',
                    data: {!! json_encode($departmentStats->values()) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    fill: false,
                    tension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f8fafc' },
                        border: { display: false },
                        ticks: { font: { family: 'Prompt', size: 10 }, color: '#94a3b8', stepSize: 5, precision: 0 } 
                    },
                    x: { 
                        grid: { display: false },
                        border: { display: false },
                        ticks: { 
                            font: { family: 'Prompt', size: 9, weight: 'bold' }, 
                            color: '#64748b',
                            autoSkip: false,
                            maxRotation: 0
                        } 
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                const idx = items[0].dataIndex;
                                return rawDeptKeys[idx] || '';
                            }
                        }
                    }
                }
            }
        });
    }
});

// ส่งออกเอกสารเป็น PDF
function exportHistoryPDF() {
    const rows = document.querySelectorAll('#history-data-table tbody tr.history-row');
    const visibleRows = [];
    rows.forEach(r => {
        if (r.style.display !== 'none') visibleRows.push(r);
    });

    if (visibleRows.length === 0) {
        alert('ไม่พบข้อมูลรายการแจ้งซ่อมสำหรับพิมพ์');
        return;
    }

    const now = new Date();
    const dateFormatted = now.toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });

    let tableRowsHtml = '';
    visibleRows.forEach((r, idx) => {
        const cells = r.querySelectorAll('td');
        if (cells.length >= 9) {
            const code = cells[0].textContent.trim();
            const date = cells[1].textContent.trim().replace(/\s+/g, ' ');
            const devName = cells[2].querySelector('div:first-child')?.textContent.trim() || cells[2].textContent.trim();
            const devCode = cells[2].querySelector('div:last-child')?.textContent.trim() || '-';
            const symptom = cells[3].getAttribute('title') || cells[3].textContent.trim();
            const location = cells[4].getAttribute('title') || cells[4].textContent.trim();
            const tech = cells[5].textContent.trim();
            const priority = cells[6].textContent.trim();
            const cost = cells[7].textContent.trim();
            const status = cells[8].textContent.trim();

            tableRowsHtml += `
                <tr style="border-bottom: 1px solid #e2e8f0; font-size: 10px; background-color: ${idx % 2 === 0 ? '#ffffff' : '#f8fafc'};">
                    <td style="padding: 6px 8px; font-weight: bold; color: #2563eb; white-space: nowrap;">${code}</td>
                    <td style="padding: 6px 8px; white-space: nowrap;">${date}</td>
                    <td style="padding: 6px 8px;"><strong>${devName}</strong><br><span style="font-size: 8px; color: #64748b;">${devCode}</span></td>
                    <td style="padding: 6px 8px;">${symptom}</td>
                    <td style="padding: 6px 8px;">${location}</td>
                    <td style="padding: 6px 8px; white-space: nowrap;">${tech}</td>
                    <td style="padding: 6px 8px; text-align: center; white-space: nowrap;">${priority}</td>
                    <td style="padding: 6px 8px; text-align: right; font-weight: bold; white-space: nowrap;">${cost}</td>
                    <td style="padding: 6px 8px; text-align: center; font-weight: bold; white-space: nowrap;">${status}</td>
                </tr>
            `;
        }
    });

    const printContainer = document.createElement('div');
    printContainer.style.padding = '15px';
    printContainer.style.fontFamily = "'Prompt', sans-serif";
    printContainer.style.color = '#1e293b';

    printContainer.innerHTML = `
        <div style="text-align: center; margin-bottom: 15px; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">
            <h2 style="font-size: 16px; font-weight: 800; color: #1e3a8a; margin: 0 0 4px 0;">ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา</h2>
            <h3 style="font-size: 13px; font-weight: bold; color: #334155; margin: 0 0 4px 0;">รายงานประวัติงานซ่อมย้อนหลัง</h3>
            <div style="font-size: 9px; color: #64748b; display: flex; justify-content: space-between; margin-top: 6px;">
                <span>จำนวนข้อมูลในรายงาน: <strong>${visibleRows.length}</strong> รายการ</span>
                <span>พิมพ์เมื่อ: ${dateFormatted} น.</span>
            </div>
        </div>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #2563eb; color: #ffffff; font-size: 9px; font-weight: bold;">
                    <th style="padding: 6px 8px;">รหัสแจ้งซ่อม</th>
                    <th style="padding: 6px 8px;">วันที่แจ้ง</th>
                    <th style="padding: 6px 8px;">อุปกรณ์</th>
                    <th style="padding: 6px 8px;">อาการ/ปัญหา</th>
                    <th style="padding: 6px 8px;">สถานที่</th>
                    <th style="padding: 6px 8px;">ช่างผู้รับผิดชอบ</th>
                    <th style="padding: 6px 8px; text-align: center;">ความเร่งด่วน</th>
                    <th style="padding: 6px 8px; text-align: right;">ค่าใช้จ่าย</th>
                    <th style="padding: 6px 8px; text-align: center;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                ${tableRowsHtml}
            </tbody>
        </table>
        <div style="margin-top: 15px; text-align: right; font-size: 9px; color: #64748b;">
            ลงชื่อ ................................................................ ผู้จัดทำรายงาน
        </div>
    `;

    if (typeof html2pdf !== 'undefined') {
        const opt = {
            margin: [8, 8, 8, 8],
            filename: `repair_history_report_${now.toISOString().slice(0, 10)}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(printContainer).save();
    } else {
        // Fallback: Browser print window
        const printWin = window.open('', '_blank');
        if (!printWin) {
            alert('กรุณาอนุญาต Pop-up บนเบราว์เซอร์เพื่อพิมพ์รายงาน');
            return;
        }
        printWin.document.write('<!DOCTYPE html><html><head><title>รายงานประวัติงานซ่อม</title>');
        printWin.document.write('<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600;700&display=swap" rel="stylesheet">');
        printWin.document.write('<style>body { font-family: "Prompt", sans-serif; margin: 20px; } @page { size: landscape; margin: 10mm; }</style>');
        printWin.document.write('</head><body>');
        printWin.document.write(printContainer.innerHTML);
        printWin.document.write('</body></html>');
        printWin.document.close();
        printWin.focus();
        setTimeout(() => { printWin.print(); }, 500);
    }
}

// ฟังก์ชันสำหรับส่งออก Excel
function exportHistoryExcel() {
    if (typeof XLSX === 'undefined') {
        alert('กำลังโหลดโมดูล Excel กรุณาลองใหม่อีกครั้ง');
        return;
    }

    const rows = document.querySelectorAll('#history-data-table tbody tr.history-row');
    const excelData = [];

    // Header row
    excelData.push([
        'รหัสการแจ้งซ่อม',
        'วันที่แจ้งซ่อม',
        'รายการอุปกรณ์',
        'รหัสอุปกรณ์/ครุภัณฑ์',
        'อาการเสีย/ปัญหา',
        'สถานที่',
        'เจ้าหน้าที่ซ่อมบำรุง',
        'ความเร่งด่วน',
        'ค่าใช้จ่าย (บาท)',
        'สถานะ'
    ]);

    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 9) {
                const code = cells[0].textContent.trim();
                const date = cells[1].textContent.trim().replace(/\s+/g, ' ');
                const devName = cells[2].querySelector('div:first-child')?.textContent.trim() || cells[2].textContent.trim();
                const devCode = cells[2].querySelector('div:last-child')?.textContent.trim() || '-';
                const symptom = cells[3].getAttribute('title') || cells[3].textContent.trim();
                const location = cells[4].getAttribute('title') || cells[4].textContent.trim();
                const tech = cells[5].textContent.trim();
                const priority = cells[6].textContent.trim();
                const cost = cells[7].textContent.trim().replace('฿', '').replace(/,/g, '');
                const status = cells[8].textContent.trim();

                excelData.push([
                    code,
                    date,
                    devName,
                    devCode,
                    symptom,
                    location,
                    tech,
                    priority,
                    parseFloat(cost) || 0,
                    status
                ]);
            }
        }
    });

    if (excelData.length <= 1) {
        alert('ไม่พบข้อมูลรายการแจ้งซ่อมสำหรับส่งออก Excel');
        return;
    }

    const ws = XLSX.utils.aoa_to_sheet(excelData);
    ws['!cols'] = [
        { wch: 16 }, // รหัส
        { wch: 18 }, // วันที่
        { wch: 22 }, // อุปกรณ์
        { wch: 18 }, // รหัสครุภัณฑ์
        { wch: 35 }, // อาการ
        { wch: 20 }, // สถานที่
        { wch: 22 }, // ช่าง
        { wch: 12 }, // ความเร่งด่วน
        { wch: 15 }, // ค่าใช้จ่าย
        { wch: 18 }  // สถานะ
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "ประวัติงานซ่อม");

    const today = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, `รายงานประวัติการแจ้งซ่อม_${today}.xlsx`);
}

// รายละเอียดงานซ่อม Modal
function openHistoryDetail(data) {
    const modal = document.getElementById('details-job-modal');
    if (!modal) return;
    
    document.getElementById('detail-req-code').textContent = data.code || '-';
    document.getElementById('detail-device').textContent = data.device || '-';
    
    const statusEl = document.getElementById('detail-status');
    if (statusEl) {
        statusEl.textContent = data.status || '-';
        statusEl.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full inline-block ' + 
            (data.status === 'เสร็จสิ้น' ? 'bg-emerald-50 text-emerald-600' : 
             (data.status === 'กำลังดำเนินการ' ? 'bg-blue-50 text-blue-600' : 
              (data.status === 'ไม่สามารถซ่อมได้' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600')));
    }
    
    document.getElementById('detail-assignee').textContent = data.assignee || 'ยังไม่ระบุ';
    document.getElementById('detail-eval').textContent = data.eval || '-';
    document.getElementById('detail-repair').textContent = data.repair || '-';
    
    const unrepairBox = document.getElementById('unrepairable-container');
    const unrepairReason = document.getElementById('detail-unrepair-reason');
    if (unrepairBox && unrepairReason) {
        if (data.status === 'ไม่สามารถซ่อมได้' || (data.unrepair_reason && data.unrepair_reason !== '-')) {
            unrepairReason.textContent = data.unrepair_reason || 'ไม่สามารถซ่อมได้';
            unrepairBox.classList.remove('hidden');
        } else {
            unrepairBox.classList.add('hidden');
        }
    }
    
    document.getElementById('detail-recommend').textContent = data.recommend || '-';

    // Satisfaction & Feedback Section
    const satContainer = document.getElementById('detail-satisfaction-container');
    if (satContainer) {
        const rating = parseInt(data.rating || 0, 10);
        if (rating > 0) {
            satContainer.classList.remove('hidden');
            const scoreEl = document.getElementById('detail-sat-score');
            if (scoreEl) scoreEl.textContent = rating.toFixed(1) + ' / 5.0';

            const starsEl = document.getElementById('detail-sat-stars');
            if (starsEl) {
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    if (rating >= i) {
                        starsHtml += '<i class="fa-solid fa-star text-amber-400"></i>';
                    } else {
                        starsHtml += '<i class="fa-regular fa-star text-slate-300"></i>';
                    }
                }
                starsEl.innerHTML = starsHtml;
            }

            const badgeEl = document.getElementById('detail-sat-badge');
            if (badgeEl) {
                if (rating === 5) {
                    badgeEl.textContent = 'ดีเยี่ยม (5/5)';
                    badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
                } else if (rating === 4) {
                    badgeEl.textContent = 'ดีมาก (4/5)';
                    badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200';
                } else if (rating === 3) {
                    badgeEl.textContent = 'ปานกลาง (3/5)';
                    badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200';
                } else {
                    badgeEl.textContent = 'ควรปรับปรุง (' + rating + '/5)';
                    badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200';
                }
            }

            const commentEl = document.getElementById('detail-sat-comment');
            if (commentEl) {
                if (data.eval_comment && data.eval_comment.trim() !== '' && data.eval_comment !== '-') {
                    commentEl.textContent = '“' + data.eval_comment.trim() + '”';
                    commentEl.className = 'text-xs text-slate-700 bg-white/90 p-3 rounded-xl border border-amber-100/80 font-medium italic';
                } else {
                    commentEl.textContent = 'ไม่มีข้อเสนอแนะเพิ่มเติม';
                    commentEl.className = 'text-xs text-slate-400 bg-white/90 p-3 rounded-xl border border-amber-100/80 font-medium italic';
                }
            }
        } else {
            satContainer.classList.add('hidden');
        }
    }

    // Image in detail modal
    const imgContainer = document.getElementById('detail-image-container');
    const imgPreview = document.getElementById('detail-image-preview');
    const imgLink = document.getElementById('detail-image-link');
    if (imgContainer && imgPreview) {
        if (data.image && data.image.trim() !== '') {
            imgPreview.src = data.image;
            if (imgLink) imgLink.href = data.image;
            imgContainer.classList.remove('hidden');
        } else {
            imgPreview.src = '';
            if (imgLink) imgLink.href = '#';
            imgContainer.classList.add('hidden');
        }
    }
    
    modal.classList.remove('hidden');
}

function closeDetailsModal() {
    const modal = document.getElementById('details-job-modal');
    if (modal) modal.classList.add('hidden');
}

// ==========================================
// Standardized History Filtering & Chart System
// ==========================================
let currentHistoryTimeMode = 'all'; // 'all', 'day', 'month', 'year'
let currentHistoryFilter = 'all';   // 'all', 'repairable', 'unrepairable_parts', 'hardware', 'software', 'network_other'
let currentDonutView = 'outcome';   // 'outcome', 'category'

let historyBarChartInstance = null;
let historyDonutChartInstance = null;
let currentBarView = 'category'; // 'category' | 'timeline' | 'status'
let currentHeadHistoryView = 'chart'; // 'chart', 'table'
let headHistoryCurrentPage = 1;
const headHistoryPageSize = 5;
let lastHeadHistorySearchKeyword = '';

function setHeadHistoryView(mode) {
    currentHeadHistoryView = mode;
    const chartView = document.getElementById('head-history-chart-view');
    const tableView = document.getElementById('head-history-table-view');

    if (chartView && tableView) {
        if (mode === 'table') {
            chartView.classList.add('hidden');
            tableView.classList.remove('hidden');
        } else { // default 'chart'
            chartView.classList.remove('hidden');
            tableView.classList.add('hidden');
        }
    }

    // Update header buttons
    const modes = ['chart', 'table'];
    modes.forEach(m => {
        const btn = document.getElementById(`btn-view-${m}`);
        if (btn) {
            if (m === mode) {
                btn.className = 'flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-white text-blue-600 shadow-sm cursor-pointer';
            } else {
                btn.className = 'flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-white hover:bg-white/15 cursor-pointer';
            }
        }
    });

    // Resize charts if chart section is visible
    if (mode === 'chart') {
        setTimeout(() => {
            if (historyBarChartInstance) historyBarChartInstance.resize();
            if (historyDonutChartInstance) historyDonutChartInstance.resize();
        }, 60);
    }
}

function setHistoryTimeMode(mode) {
    currentHistoryTimeMode = mode;

    // Update Mode Toggle Buttons
    document.querySelectorAll('.history-mode-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
        btn.classList.add('text-slate-600', 'font-semibold');
    });
    const activeBtn = document.getElementById(`btn-mode-${mode}`);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-600', 'font-semibold');
        activeBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
    }

    // Toggle Date Picker Controls
    const dayCtrl = document.getElementById('control-day');
    const monthCtrl = document.getElementById('control-month');
    const yearCtrl = document.getElementById('control-year');

    if (dayCtrl) dayCtrl.classList.toggle('hidden', mode !== 'day');
    if (monthCtrl) monthCtrl.classList.toggle('hidden', mode !== 'month');
    if (yearCtrl) yearCtrl.classList.toggle('hidden', mode !== 'year');

    applyHistoryFilter();
}

function setHistoryFilter(filterKey, element) {
    currentHistoryFilter = filterKey;

    // Update 6 Big Rectangular Cards (stat-history-card)
    const filterCardKeys = ['all', 'repairable', 'unrepairable_parts', 'hardware', 'software', 'network_other'];
    const defaultBorders = {
        'all': 'border-slate-200/80',
        'repairable': 'border-slate-200/80',
        'unrepairable_parts': 'border-rose-200/80',
        'hardware': 'border-indigo-200/80',
        'software': 'border-purple-200/80',
        'network_other': 'border-teal-200/80'
    };

    filterCardKeys.forEach(k => {
        const card = document.getElementById(`chip-${k}`);
        if (!card) return;
        const titleSpan = card.querySelector('.card-title');
        if (k === filterKey) {
            card.classList.add('active-stat', 'border-blue-500', 'ring-2', 'ring-blue-500', 'shadow-md');
            card.classList.remove('border-slate-200/80', 'border-rose-200/80', 'border-indigo-200/80', 'border-purple-200/80', 'border-teal-200/80', 'shadow-sm');
            if (titleSpan) {
                titleSpan.classList.remove('font-bold', 'text-slate-600', 'text-slate-500');
                titleSpan.classList.add('font-black', 'text-blue-700');
            }
        } else {
            card.classList.remove('active-stat', 'border-blue-500', 'ring-2', 'ring-blue-500', 'shadow-md');
            card.classList.add(defaultBorders[k] || 'border-slate-200/80', 'shadow-sm');
            if (titleSpan) {
                titleSpan.classList.remove('font-black', 'text-blue-700');
                titleSpan.classList.add('font-bold', 'text-slate-600');
            }
        }
    });

    // Sync Donut chart view if applicable
    if (filterKey === 'repairable' || filterKey === 'unrepairable_parts') {
        if (typeof setDonutView === 'function') setDonutView('outcome');
    } else if (['hardware', 'software', 'network_other'].includes(filterKey)) {
        if (typeof setDonutView === 'function') setDonutView('category');
    }

    headHistoryCurrentPage = 1;
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
        if (btnOutcome) {
            btnOutcome.classList.add('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
            btnOutcome.classList.remove('text-slate-500');
        }
        if (btnCategory) {
            btnCategory.classList.remove('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
            btnCategory.classList.add('text-slate-500');
        }
        if (legendOutcome) legendOutcome.classList.remove('hidden');
        if (legendCategory) legendCategory.classList.add('hidden');
        if (chartTitle) chartTitle.textContent = 'สัดส่วนผลการซ่อม';
    } else {
        if (btnCategory) {
            btnCategory.classList.add('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
            btnCategory.classList.remove('text-slate-500');
        }
        if (btnOutcome) {
            btnOutcome.classList.remove('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
            btnOutcome.classList.add('text-slate-500');
        }
        if (legendOutcome) legendOutcome.classList.add('hidden');
        if (legendCategory) legendCategory.classList.remove('hidden');
        if (chartTitle) chartTitle.textContent = 'สัดส่วนหมวดหมู่อุปกรณ์';
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
    headHistoryCurrentPage = 1;
    setHistoryTimeMode('all');
    setHistoryFilter('all');
    setBarView('category');
    clearHistorySearch();
}

function clearHistorySearch() {
    headHistoryCurrentPage = 1;
    const searchInput = document.getElementById('history-search-input');
    const clearBtn = document.getElementById('btn-clear-search');
    if (searchInput) {
        searchInput.value = '';
    }
    if (clearBtn) {
        clearBtn.classList.add('hidden');
    }
    applyHistoryFilter();
}

function applyHighlight(element, query) {
    // Basic highlight preservation
}

function applyHistoryFilter() {
    const jobs = window.historyJobsData || [];

    const searchInput = document.getElementById('history-search-input');
    const clearBtn = document.getElementById('btn-clear-search');
    const searchKeyword = searchInput ? searchInput.value.trim().toLowerCase() : '';

    if (clearBtn) {
        clearBtn.classList.toggle('hidden', searchKeyword === '');
    }

    // 1. Time-based filtering
    let selectedDate = '';
    let selectedMonth = 0;
    let selectedYear = 0;

    const dateInput = document.getElementById('history-filter-date');
    if (dateInput) selectedDate = dateInput.getAttribute('data-date-ymd') || dateInput.value;

    const monthInput = document.getElementById('history-filter-month');
    if (monthInput) selectedMonth = parseInt(monthInput.value);

    if (currentHistoryTimeMode === 'month') {
        const monthYearInput = document.getElementById('history-filter-month-year');
        if (monthYearInput) selectedYear = parseInt(monthYearInput.value);
    } else if (currentHistoryTimeMode === 'year') {
        const yearInput = document.getElementById('history-filter-year');
        if (yearInput) selectedYear = parseInt(yearInput.value);
    }

    const timeFiltered = jobs.filter(item => {
        if (currentHistoryTimeMode === 'day') {
            return item.date_ymd === selectedDate;
        } else if (currentHistoryTimeMode === 'month') {
            return item.year === selectedYear && item.month === selectedMonth;
        } else if (currentHistoryTimeMode === 'year') {
            return item.year === selectedYear;
        }
        return true;
    });

    // 2. Count statistics for current time period
    let totalCount = timeFiltered.length;
    let repairableCount = 0;
    let unrepairableCount = 0;
    let hwCount = 0;
    let swCount = 0;
    let netCount = 0;

    timeFiltered.forEach(item => {
        if (item.reparability === 'repairable') repairableCount++;
        if (item.reparability === 'unrepairable_parts') unrepairableCount++;
        if (item.category === 'hardware') hwCount++;
        if (item.category === 'software') swCount++;
        if (item.category === 'network_other') netCount++;
    });

    // 3. Update KPI Stat Cards & Badges
    const elTotal = document.getElementById('stat-total-count');
    if (elTotal) elTotal.textContent = totalCount;

    const elRepCount = document.getElementById('stat-repairable-count');
    const elRepPct = document.getElementById('stat-repairable-pct');
    if (elRepCount) elRepCount.textContent = repairableCount;
    if (elRepPct) elRepPct.textContent = totalCount > 0 ? `${Math.round((repairableCount / totalCount) * 100)}%` : '0%';

    const elUnrepCount = document.getElementById('stat-unrepairable-count');
    const elUnrepPct = document.getElementById('stat-unrepairable-pct');
    if (elUnrepCount) elUnrepCount.textContent = unrepairableCount;
    if (elUnrepPct) elUnrepPct.textContent = totalCount > 0 ? `${Math.round((unrepairableCount / totalCount) * 100)}%` : '0%';

    const elHwCount = document.getElementById('stat-hw-count');
    const elHwPct = document.getElementById('stat-hw-pct');
    if (elHwCount) elHwCount.textContent = hwCount;
    if (elHwPct) elHwPct.textContent = totalCount > 0 ? `${Math.round((hwCount / totalCount) * 100)}%` : '0%';

    const elSwCount = document.getElementById('stat-sw-count');
    const elSwPct = document.getElementById('stat-sw-pct');
    if (elSwCount) elSwCount.textContent = swCount;
    if (elSwPct) elSwPct.textContent = totalCount > 0 ? `${Math.round((swCount / totalCount) * 100)}%` : '0%';

    const elNetCount = document.getElementById('stat-net-count');
    const elNetPct = document.getElementById('stat-net-pct');
    if (elNetCount) elNetCount.textContent = netCount;
    if (elNetPct) elNetPct.textContent = totalCount > 0 ? `${Math.round((netCount / totalCount) * 100)}%` : '0%';

    // Update Chip Badges
    const badgeAll = document.getElementById('badge-count-all');
    if (badgeAll) badgeAll.textContent = totalCount;
    const badgeRep = document.getElementById('badge-count-repairable');
    if (badgeRep) badgeRep.textContent = repairableCount;
    const badgeUnrep = document.getElementById('badge-count-unrepairable');
    if (badgeUnrep) badgeUnrep.textContent = unrepairableCount;
    const badgeHw = document.getElementById('badge-count-hw');
    if (badgeHw) badgeHw.textContent = hwCount;
    const badgeSw = document.getElementById('badge-count-sw');
    if (badgeSw) badgeSw.textContent = swCount;
    const badgeNet = document.getElementById('badge-count-net');
    if (badgeNet) badgeNet.textContent = netCount;

    // Donut Legend counts
    const donutRep = document.getElementById('donut-repairable-count');
    if (donutRep) donutRep.textContent = `${repairableCount} รายการ`;
    const donutUnrep = document.getElementById('donut-unrepairable-count');
    if (donutUnrep) donutUnrep.textContent = `${unrepairableCount} รายการ`;
    const donutHw = document.getElementById('donut-hw-count');
    if (donutHw) donutHw.textContent = `${hwCount} รายการ`;
    const donutSw = document.getElementById('donut-sw-count');
    if (donutSw) donutSw.textContent = `${swCount} รายการ`;
    const donutNet = document.getElementById('donut-net-count');
    if (donutNet) donutNet.textContent = `${netCount} รายการ`;

    // Period label in card 1
    const periodLabel = document.getElementById('stat-period-label');
    if (periodLabel) {
        if (currentHistoryTimeMode === 'all') periodLabel.textContent = 'ข้อมูลทั้งหมดในระบบ';
        else if (currentHistoryTimeMode === 'day') periodLabel.textContent = `ประจำวันที่ ${selectedDate}`;
        else if (currentHistoryTimeMode === 'month') periodLabel.textContent = `ประจำเดือน ${selectedMonth}/${selectedYear + 543}`;
        else if (currentHistoryTimeMode === 'year') periodLabel.textContent = `ประจำปี พ.ศ. ${selectedYear + 543}`;
    }

    // 4. Render Bar Chart (Redesigned with Real Data & Multiple View Modes)
    const barCanvas = document.getElementById('historyBarChart');
    if (barCanvas && typeof Chart !== 'undefined') {
        let barLabels = [];
        let datasets = [];
        const legendContainer = document.getElementById('bar-legend-container');
        const barTitle = document.getElementById('chart-bar-title');
        const barSub = document.getElementById('chart-bar-subtitle');

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
                    { label: 'สามารถซ่อมได้', data: wRep, backgroundColor: '#10B981', borderRadius: 6, barPercentage: 0.55, categoryPercentage: 0.6 },
                    { label: 'สั่งอะไหล่/ซ่อมไม่ได้', data: wUnrep, backgroundColor: '#F43F5E', borderRadius: 6, barPercentage: 0.55, categoryPercentage: 0.6 }
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

    // 5. Render Donut Chart
    const donutCanvas = document.getElementById('historyDonutChart');
    if (donutCanvas && typeof Chart !== 'undefined') {
        let donutLabels = [];
        let donutData = [];
        let donutColors = [];
        let totalActive = 0;

        if (currentDonutView === 'outcome') {
            donutLabels = ['สามารถซ่อมได้', 'ซ่อมไม่ได้/สั่งอะไหล่'];
            donutData = [repairableCount, unrepairableCount];
            donutColors = ['#10B981', '#F43F5E'];
            totalActive = repairableCount + unrepairableCount;
        } else {
            donutLabels = ['ฮาร์ดแวร์', 'ซอฟต์แวร์', 'เครือข่าย & อื่นๆ'];
            donutData = [hwCount, swCount, netCount];
            donutColors = ['#6366F1', '#A855F7', '#14B8A6'];
            totalActive = hwCount + swCount + netCount;
        }

        if (totalActive === 0) {
            donutData = [1];
            donutLabels = ['ไม่มีข้อมูล'];
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

    // 6. Filter history rows in table with pagination
    if (searchKeyword !== lastHeadHistorySearchKeyword) {
        headHistoryCurrentPage = 1;
        lastHeadHistorySearchKeyword = searchKeyword;
    }

    let matchingRows = [];
    const rows = document.querySelectorAll('#history-data-table tbody tr.history-row');
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

        // Match category / reparability chip
        let matchFilter = true;
        if (currentHistoryFilter === 'repairable') matchFilter = (cReparability === 'repairable');
        else if (currentHistoryFilter === 'unrepairable_parts') matchFilter = (cReparability === 'unrepairable_parts');
        else if (currentHistoryFilter === 'hardware') matchFilter = (cCategory === 'hardware');
        else if (currentHistoryFilter === 'software') matchFilter = (cCategory === 'software');
        else if (currentHistoryFilter === 'network_other') matchFilter = (cCategory === 'network_other');

        // Match search keyword
        let matchSearch = true;
        if (searchKeyword !== '') {
            matchSearch = cKeyword.includes(searchKeyword);
        }

        if (matchTime && matchFilter && matchSearch) {
            matchingRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const totalMatching = matchingRows.length;
    const totalPages = Math.ceil(totalMatching / headHistoryPageSize) || 1;

    if (headHistoryCurrentPage > totalPages) headHistoryCurrentPage = totalPages;
    if (headHistoryCurrentPage < 1) headHistoryCurrentPage = 1;

    const startIndex = (headHistoryCurrentPage - 1) * headHistoryPageSize;
    const endIndex = startIndex + headHistoryPageSize;

    matchingRows.forEach((row, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Empty state row
    const emptyRow = document.getElementById('history-empty-row');
    if (emptyRow) {
        emptyRow.classList.toggle('hidden', totalMatching > 0);
    }

    // Render pagination
    renderHeadHistoryPagination(totalMatching, totalPages, startIndex, endIndex);
}

function renderHeadHistoryPagination(totalMatching, totalPages, startIndex, endIndex) {
    const infoEl = document.getElementById('head-history-pagination-info');
    const btnsEl = document.getElementById('head-history-pagination-buttons');
    const containerEl = document.getElementById('head-history-pagination-container');

    if (!containerEl) return;

    if (totalMatching === 0) {
        if (infoEl) infoEl.innerHTML = '<span class="text-slate-400">ไม่พบรายการที่ตรงกับเงื่อนไข</span>';
        if (btnsEl) btnsEl.innerHTML = '';
        return;
    }

    const startDisplay = startIndex + 1;
    const endDisplay = Math.min(endIndex, totalMatching);

    if (infoEl) {
        infoEl.innerHTML = `แสดง <span class="font-bold text-slate-700">${startDisplay}</span> ถึง <span class="font-bold text-slate-700">${endDisplay}</span> จากทั้งหมด <span class="font-bold text-slate-700">${totalMatching}</span> รายการ`;
    }

    if (btnsEl) {
        let btnHtml = '';

        // ปุ่มไปซ้าย / ก่อนหน้า
        const isPrevDisabled = headHistoryCurrentPage === 1;
        btnHtml += `<button type="button" onclick="goToHeadHistoryPage(${headHistoryCurrentPage - 1})" ${isPrevDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold transition flex items-center gap-1.5 ${isPrevDisabled ? 'bg-slate-50 text-slate-300 cursor-not-allowed border-slate-100' : 'bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 hover:border-blue-200 cursor-pointer shadow-xs'}">
            <i class="fa-solid fa-chevron-left text-[10px]"></i>
            <span>ก่อนหน้า</span>
        </button>`;

        // ปุ่มหมายเลขหน้า
        let pagesToShow = [];
        if (totalPages <= 7) {
            for (let p = 1; p <= totalPages; p++) pagesToShow.push(p);
        } else {
            pagesToShow.push(1);
            if (headHistoryCurrentPage > 3) pagesToShow.push('...');
            const startP = Math.max(2, headHistoryCurrentPage - 1);
            const endP = Math.min(totalPages - 1, headHistoryCurrentPage + 1);
            for (let p = startP; p <= endP; p++) {
                if (!pagesToShow.includes(p)) pagesToShow.push(p);
            }
            if (headHistoryCurrentPage < totalPages - 2) pagesToShow.push('...');
            if (!pagesToShow.includes(totalPages)) pagesToShow.push(totalPages);
        }

        pagesToShow.forEach(p => {
            if (p === '...') {
                btnHtml += `<span class="px-2 py-1 text-slate-400 font-bold">...</span>`;
            } else if (p === headHistoryCurrentPage) {
                btnHtml += `<button type="button" class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-xs shadow-md shadow-blue-200 cursor-default">${p}</button>`;
            } else {
                btnHtml += `<button type="button" onclick="goToHeadHistoryPage(${p})" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 text-slate-600 hover:text-blue-600 border border-slate-200 font-bold text-xs transition cursor-pointer shadow-xs">${p}</button>`;
            }
        });

        // ปุ่มหน้าถัดไป (ไปขวา)
        const isNextDisabled = headHistoryCurrentPage === totalPages;
        btnHtml += `<button type="button" onclick="goToHeadHistoryPage(${headHistoryCurrentPage + 1})" ${isNextDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold transition flex items-center gap-1.5 ${isNextDisabled ? 'bg-slate-50 text-slate-300 cursor-not-allowed border-slate-100' : 'bg-white hover:bg-blue-50 text-slate-600 hover:text-blue-600 hover:border-blue-200 cursor-pointer shadow-xs'}">
            <span>หน้าถัดไป</span>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>`;

        btnsEl.innerHTML = btnHtml;
    }
}

function goToHeadHistoryPage(page) {
    headHistoryCurrentPage = page;
    applyHistoryFilter();
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        let tabParam = urlParams.get('tab');
        let targetTab = null;

        if (tabParam) {
            targetTab = tabParam.startsWith('tab-') ? tabParam : ('tab-' + tabParam);
        } else if (window.location.hash) {
            targetTab = window.location.hash.replace('#', '');
        } else {
            targetTab = 'tab-summary';
        }

        if (targetTab && document.getElementById(targetTab)) {
            const tabLink = document.querySelector(`a[onclick*="switchTab('${targetTab}'"]`) || document.querySelector(`.desktop-tab[onclick*="${targetTab}"]`);
            switchTab(targetTab, tabLink);
        }
    } catch (e) {
        console.error('Error restoring head tab:', e);
    }

    if (typeof applyHistoryFilter === 'function') {
        applyHistoryFilter();
    }
});

function openHeadReqDetailModal(btn) {
    const modal = document.getElementById('req-full-detail-modal');
    if (!modal) return;
    
    // Extract data
    const status = btn.getAttribute('data-status') || '-';
    const code = btn.getAttribute('data-code') || '-';
    const deviceName = btn.getAttribute('data-device') || '-';
    let deviceDisplayName = deviceName;
    let deviceIdStr = 'อุปกรณ์ทั่วไป';
    if(deviceName.includes('(#')) {
        const parts = deviceName.split('(#');
        deviceDisplayName = parts[0].trim();
        deviceIdStr = '#' + parts[1].replace(')', '');
    }
    
    const reporter = btn.getAttribute('data-user') || '-';
    const location = btn.getAttribute('data-loc') || '-';
    const type = btn.getAttribute('data-type') || '-';
    const priority = btn.getAttribute('data-priority') || '-';
    const assignee = btn.getAttribute('data-assignee-name') || '-';
    const desc = btn.getAttribute('data-desc') || '-';
    const created = btn.getAttribute('data-date') || '-';
    
    // Populate
    document.getElementById('hrm-code').innerText = code;
    document.getElementById('hrm-device-name').innerText = deviceDisplayName;
    document.getElementById('hrm-device-id').innerText = deviceIdStr;
    
    const statusEl = document.getElementById('hrm-status');
    statusEl.innerText = status;
    statusEl.className = 'text-xs font-extrabold px-3 py-1 rounded-full border whitespace-nowrap flex-shrink-0 ' + 
        (status === 'รอรับงาน' ? 'bg-amber-50 text-amber-600 border-amber-200' : 
         (status === 'กำลังดำเนินการ' || status === 'กำลังซ่อม' ? 'bg-indigo-50 text-indigo-600 border-indigo-200' : 
          (status === 'รอเปลี่ยนอะไหล่' || status === 'รออะไหล่' ? 'bg-purple-50 text-purple-600 border-purple-200' : 'bg-slate-50 text-slate-600 border-slate-200')));
          
    document.getElementById('hrm-reporter').innerText = reporter;
    document.getElementById('hrm-location').innerText = location;
    document.getElementById('hrm-type').innerText = type;
    
    const priorityEl = document.getElementById('hrm-priority');
    priorityEl.innerText = priority === 'ปกติ' ? 'ระดับปกติ' : priority;
    if (priority === 'ด่วน' || priority === 'ด่วนมาก') {
        priorityEl.className = 'text-sm font-bold text-red-600';
    } else {
        priorityEl.className = 'text-sm font-bold text-emerald-600';
    }
    
    document.getElementById('hrm-assignee').innerText = assignee;
    document.getElementById('hrm-description').innerText = desc;
    document.getElementById('hrm-created').innerText = created;

    // Attached Image in full detail modal
    const image = btn.getAttribute('data-image') || '';
    const hrmImgContainer = document.getElementById('hrm-image-container');
    const hrmImgPreview = document.getElementById('hrm-image-preview');
    const hrmImgLink = document.getElementById('hrm-image-link');
    if (hrmImgContainer && hrmImgPreview) {
        if (image && image.trim() !== '') {
            hrmImgPreview.src = image;
            if (hrmImgLink) hrmImgLink.href = image;
            hrmImgContainer.classList.remove('hidden');
        } else {
            hrmImgPreview.src = '';
            if (hrmImgLink) hrmImgLink.href = '#';
            hrmImgContainer.classList.add('hidden');
        }
    }

    // Satisfaction in full detail modal
    const rating = parseInt(btn.getAttribute('data-rating') || 0, 10);
    const evalComment = btn.getAttribute('data-eval-comment') || '';
    const hrmSatContainer = document.getElementById('hrm-satisfaction-container');
    if (hrmSatContainer) {
        if (rating > 0) {
            hrmSatContainer.classList.remove('hidden');
            const hrmSatScore = document.getElementById('hrm-sat-score');
            if (hrmSatScore) hrmSatScore.textContent = rating.toFixed(1) + ' / 5.0';

            const hrmSatStars = document.getElementById('hrm-sat-stars');
            if (hrmSatStars) {
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    starsHtml += rating >= i ? '<i class="fa-solid fa-star text-amber-400"></i>' : '<i class="fa-regular fa-star text-slate-300"></i>';
                }
                hrmSatStars.innerHTML = starsHtml;
            }

            const hrmSatBadge = document.getElementById('hrm-sat-badge');
            if (hrmSatBadge) {
                hrmSatBadge.textContent = rating >= 4.5 ? 'ดีเยี่ยม' : (rating >= 3.5 ? 'ดีมาก' : (rating >= 2.5 ? 'ปานกลาง' : 'ควรปรับปรุง'));
            }

            const hrmSatComment = document.getElementById('hrm-sat-comment');
            if (hrmSatComment) {
                hrmSatComment.textContent = evalComment.trim() ? '“' + evalComment.trim() + '”' : 'ไม่มีข้อเสนอแนะเพิ่มเติม';
            }
        } else {
            hrmSatContainer.classList.add('hidden');
        }
    }
    
    // Check if reassignment is allowed
    const reassignBtn = document.getElementById('hrm-reassign-btn');
    if (reassignBtn) {
        if (status === 'รอรับงาน' || status === 'กำลังดำเนินการ' || status === 'กำลังซ่อม' || status === 'รอเปลี่ยนอะไหล่' || status === 'รออะไหล่') {
            reassignBtn.classList.remove('hidden');
            reassignBtn.onclick = function() {
                closeHeadReqDetailModal();
                setTimeout(() => { openAssignModal(btn); }, 300);
            };
        } else {
            reassignBtn.classList.add('hidden');
        }
    }

    modal.classList.remove('hidden');
    const box = document.getElementById('req-full-detail-box');
    if (box) {
        void modal.offsetWidth;
        box.classList.remove('scale-95');
        box.classList.remove('opacity-0');
        box.classList.add('scale-100');
        box.classList.add('opacity-100');
    }
}

function closeHeadReqDetailModal(event) {
    if (event && event.target.id !== 'req-full-detail-modal' && event.target.tagName !== 'BUTTON') {
        return; 
    }
    const modal = document.getElementById('req-full-detail-modal');
    if (!modal) return;
    const box = document.getElementById('req-full-detail-box');
    if (box) {
        box.classList.remove('scale-100');
        box.classList.remove('opacity-100');
        box.classList.add('scale-95');
        box.classList.add('opacity-0');
    }
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// ==========================================
// Supervisor Technician Feedback & Satisfaction
// ==========================================
let currentHeadFeedbackTechId = 'all';
let currentHeadFeedbackTextMode = 'all'; // 'all', 'has_text'

function selectHeadFeedbackTech(techId, btnEl) {
    currentHeadFeedbackTechId = techId;

    // 1. Update active styling on tech buttons
    document.querySelectorAll('.tech-feedback-selector-btn').forEach(b => {
        b.classList.remove('active-tech-selector', 'border-blue-500', 'bg-blue-50/40');
        b.classList.add('border-slate-200/80', 'bg-white');
    });
    if (btnEl) {
        btnEl.classList.remove('border-slate-200/80', 'bg-white');
        btnEl.classList.add('active-tech-selector', 'border-blue-500', 'bg-blue-50/40');
    }

    // 2. Update header display
    const nameDisplay = document.getElementById('selected-tech-name-display');
    const badgeDisplay = document.getElementById('selected-tech-badge-display');
    const scoreDisplay = document.getElementById('selected-tech-score-display');

    if (techId === 'all') {
        if (nameDisplay) nameDisplay.textContent = 'ช่างทั้งหมด (รวมทุกคน)';
        const overallScore = document.querySelector('.tech-feedback-selector-btn[data-tech-id="all"] .text-slate-800')?.textContent || '0.00';
        if (scoreDisplay) scoreDisplay.textContent = overallScore + ' / 5.0';
    } else {
        const techName = btnEl?.getAttribute('data-tech-name') || btnEl?.querySelector('h4')?.textContent || 'ช่างที่เลือก';
        const techScore = btnEl?.querySelector('.text-slate-800')?.textContent || '-';
        if (nameDisplay) nameDisplay.textContent = techName;
        if (scoreDisplay && techScore !== '-') scoreDisplay.textContent = techScore + ' / 5.0';
    }

    // 3. Filter the feedback feed
    applyHeadFeedbackFiltering();
}

function filterHeadFeedbackTextTab(mode) {
    currentHeadFeedbackTextMode = mode;

    const btnAll = document.getElementById('btn-feedback-tab-all');
    const btnText = document.getElementById('btn-feedback-tab-text');

    if (mode === 'all') {
        btnAll?.classList.add('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
        btnAll?.classList.remove('text-slate-500');
        btnText?.classList.remove('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
        btnText?.classList.add('text-slate-500');
    } else {
        btnText?.classList.add('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
        btnText?.classList.remove('text-slate-500');
        btnAll?.classList.remove('bg-white', 'text-blue-600', 'shadow-sm', 'font-bold');
        btnAll?.classList.add('text-slate-500');
    }

    applyHeadFeedbackFiltering();
}

function applyHeadFeedbackFiltering() {
    const cards = document.querySelectorAll('.feedback-feed-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const cardTechId = card.getAttribute('data-tech-id') || '';
        const cardHasText = card.getAttribute('data-has-text') === '1';

        let matchTech = (currentHeadFeedbackTechId === 'all') || (cardTechId == currentHeadFeedbackTechId);
        let matchText = (currentHeadFeedbackTextMode === 'all') || cardHasText;

        if (matchTech && matchText) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    const emptyState = document.getElementById('feedback-feed-empty-state');
    if (emptyState) {
        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    }

    const badgeDisplay = document.getElementById('selected-tech-badge-display');
    if (badgeDisplay) {
        badgeDisplay.textContent = visibleCount + ' งานที่ประเมินแล้ว';
    }
}

function viewTechFeedbackInHistory(techId, techName) {
    if (typeof switchTab === 'function') {
        const historyLink = document.querySelector('[onclick*="tab-history"]');
        switchTab('tab-history', historyLink);
    }
    
    if (typeof setHeadHistoryView === 'function') {
        setHeadHistoryView('chart');
    }

    setTimeout(() => {
        const feedbackContainer = document.getElementById('head-tech-satisfaction-section');
        if (feedbackContainer) {
            feedbackContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        const techCardBtn = document.querySelector(`.tech-feedback-selector-btn[data-tech-id="${techId}"]`);
        if (techCardBtn) {
            selectHeadFeedbackTech(techId, techCardBtn);
        }
    }, 200);
}
</script>
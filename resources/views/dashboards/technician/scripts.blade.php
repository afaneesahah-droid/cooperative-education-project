<script>
    // =========================================================================
    // Itemized Requisition & Budget Helper Functions (Unified & Polished)
    // =========================================================================

    // 1. Switch View: Form vs History
    function switchRequisitionView(view) {
        const formSec = document.getElementById('req-section-form');
        const historySec = document.getElementById('req-section-history');
        const btnForm = document.getElementById('btn-req-tab-form');
        const btnHistory = document.getElementById('btn-req-tab-history');

        if (view === 'form') {
            if (formSec) formSec.classList.remove('hidden');
            if (historySec) historySec.classList.add('hidden');
            
            if (btnForm) {
                btnForm.className = 'flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-black transition-all bg-white text-blue-700 shadow-sm cursor-pointer flex items-center justify-center gap-2';
            }
            if (btnHistory) {
                btnHistory.className = 'flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-bold transition-all text-white/80 hover:text-white hover:bg-white/10 cursor-pointer flex items-center justify-center gap-2';
            }
        } else {
            if (formSec) formSec.classList.add('hidden');
            if (historySec) historySec.classList.remove('hidden');

            if (btnForm) {
                btnForm.className = 'flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-bold transition-all text-white/80 hover:text-white hover:bg-white/10 cursor-pointer flex items-center justify-center gap-2';
            }
            if (btnHistory) {
                btnHistory.className = 'flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-black transition-all bg-white text-blue-700 shadow-sm cursor-pointer flex items-center justify-center gap-2';
            }
        }
    }

    // 2. Project Selection Changed
    function handleProjectChange(select) {
        const badge = document.getElementById('budget_project_source_badge');
        const dispBudget = document.getElementById('display_budget_amount');
        const dispUsed = document.getElementById('display_budget_used');
        const dispRem = document.getElementById('display_budget_remaining');

        const setField = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        if (!select || !select.value) {
            if (badge) {
                badge.innerText = 'ยังไม่ได้เลือกโครงการ';
                badge.className = 'px-3 py-1 rounded-xl text-xs font-bold bg-slate-200/80 text-slate-600 border border-slate-200';
            }
            if (dispBudget) dispBudget.innerText = '฿0.00';
            if (dispUsed) dispUsed.innerText = '฿0.00';
            if (dispRem) dispRem.innerText = '฿0.00';

            setField('budget_project_source', '');
            setField('budget_project_code', '');
            setField('budget_department', '');
            setField('budget_fiscal_year', '');
            setField('budget_amount', 0);
            setField('budget_used', 0);
            setField('budget_remaining', 0);

            updateGrandTotal();
            return;
        }

        const opt = select.options[select.selectedIndex];
        const source = opt.getAttribute('data-source') || 'งบประมาณกลาง';
        const code = opt.getAttribute('data-code') || '';
        const dept = opt.getAttribute('data-dept') || 'ส่วนกลาง';
        const fiscal = opt.getAttribute('data-fiscal') || '';
        const budget = parseFloat(opt.getAttribute('data-budget') || 0);
        const used = parseFloat(opt.getAttribute('data-used') || 0);
        const remaining = parseFloat(opt.getAttribute('data-remaining') || (budget - used));

        if (badge) {
            badge.innerText = source;
            badge.className = 'px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-100/80 shadow-2xs';
        }
        if (dispBudget) dispBudget.innerText = '฿' + budget.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (dispUsed) dispUsed.innerText = '฿' + used.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (dispRem) dispRem.innerText = '฿' + remaining.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        setField('budget_project_source', source);
        setField('budget_project_code', code);
        setField('budget_department', dept);
        setField('budget_fiscal_year', fiscal);
        setField('budget_amount', budget);
        setField('budget_used', used);
        setField('budget_remaining', remaining);

        updateGrandTotal();
    }

    // 3. Add Item Row
    function addBudgetItem(desc = '', qty = 1, unit = 'รายการ', price = '') {
        const tbody = document.getElementById('budget-items-tbody');
        if (!tbody) return;

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/80 transition budget-item-row';
        tr.innerHTML = `
            <td class="py-2.5 px-4">
                <input type="text" value="${desc.replace(/"/g, '&quot;')}" placeholder="เช่น ค่าเปลี่ยนจอภาพ / ซ่อมเมนบอร์ด / อะไหล่ RAM..." 
                       class="w-full bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition hover:border-slate-300">
            </td>
            <td class="py-2.5 px-4 text-center">
                <input type="number" min="1" value="${qty}" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" 
                       class="item-qty w-20 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-2 py-2 text-xs sm:text-sm text-center font-bold focus:outline-none transition hover:border-slate-300">
            </td>
            <td class="py-2.5 px-4 text-center">
                <input type="text" placeholder="หน่วย" value="${unit.replace(/"/g, '&quot;')}" 
                       class="w-20 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-2 py-2 text-xs sm:text-sm text-center font-medium focus:outline-none transition hover:border-slate-300">
            </td>
            <td class="py-2.5 px-4 text-right">
                <input type="number" min="0" step="0.01" value="${price}" placeholder="0.00" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" 
                       class="item-price w-28 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-3 py-2 text-xs sm:text-sm text-right font-bold focus:outline-none transition hover:border-slate-300">
            </td>
            <td class="py-2.5 px-4 text-right font-black text-slate-800 text-sm item-total-text font-mono">
                0.00
            </td>
            <td class="py-2.5 px-3 text-center">
                <button type="button" onclick="removeBudgetItem(this)" class="text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors w-8 h-8 rounded-xl flex items-center justify-center mx-auto cursor-pointer" title="ลบรายการนี้">
                    <i class="fa-solid fa-trash-can text-sm"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        // Calculate if values given
        const priceInput = tr.querySelector('.item-price');
        if (priceInput) calculateRowTotal(priceInput);
    }

    // 4. Remove Item Row
    function removeBudgetItem(btn) {
        const tbody = document.getElementById('budget-items-tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('.budget-item-row');
        if (rows.length <= 1) {
            const firstRow = rows[0];
            const textInput = firstRow.querySelector('input[type="text"]');
            if (textInput) textInput.value = '';
            const qtyInput = firstRow.querySelector('.item-qty');
            if (qtyInput) qtyInput.value = '1';
            const priceInput = firstRow.querySelector('.item-price');
            if (priceInput) priceInput.value = '';
            const totalCell = firstRow.querySelector('.item-total-text');
            if (totalCell) totalCell.innerText = '0.00';
            updateGrandTotal();
            return;
        }

        const row = btn.closest('tr');
        if (row) {
            row.remove();
            updateGrandTotal();
        }
    }

    // 5. Calculate Row Total
    function calculateRowTotal(input) {
        const row = input.closest('tr');
        if (!row) return;

        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const rowTotal = Math.max(0, qty * price);

        const totalCell = row.querySelector('.item-total-text');
        if (totalCell) {
            totalCell.innerText = rowTotal.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        updateGrandTotal();
    }

    // 6. Live Thai Baht Text Converter
    function bahtText(num) {
        if (isNaN(num) || num === 0) return 'ศูนย์บาทถ้วน';
        num = parseFloat(num).toFixed(2);
        const parts = num.split('.');
        const integerPart = parts[0];
        const decimalPart = parts[1];

        const numbers = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        const units = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

        function convert(str) {
            let res = '';
            const len = str.length;
            for (let i = 0; i < len; i++) {
                const digit = parseInt(str.charAt(i));
                const pos = len - i - 1;
                if (digit !== 0) {
                    if (pos === 0 && digit === 1 && len > 1 && parseInt(str.charAt(i - 1)) !== 0) {
                        res += 'เอ็ด';
                    } else if (pos === 1 && digit === 1) {
                        res += '';
                    } else if (pos === 1 && digit === 2) {
                        res += 'ยี่';
                    } else {
                        res += numbers[digit];
                    }
                    res += units[pos];
                }
            }
            return res;
        }

        let result = '';
        if (parseInt(integerPart) > 0) {
            if (integerPart.length > 6) {
                const millionPart = integerPart.slice(0, -6);
                const restPart = integerPart.slice(-6);
                result += convert(millionPart) + 'ล้าน' + convert(restPart) + 'บาท';
            } else {
                result += convert(integerPart) + 'บาท';
            }
        }

        if (parseInt(decimalPart) === 0) {
            result += 'ถ้วน';
        } else {
            result += convert(decimalPart) + 'สตางค์';
        }
        return result;
    }

    // 7. Update Grand Total & Validate
    function updateGrandTotal() {
        let total = 0;
        const rows = document.querySelectorAll('.budget-item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            total += (qty * price);
        });

        const totalElem = document.getElementById('budget-grand-total');
        if (totalElem) {
            totalElem.innerText = total.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' บาท';
        }

        const textElem = document.getElementById('budget-text-amount');
        if (textElem) {
            textElem.innerText = total > 0 ? `( ${bahtText(total)} )` : '( ศูนย์บาทถ้วน )';
        }

        const warning = document.getElementById('budget-exceed-warning');
        const warningText = document.getElementById('budget-exceed-text');
        const remainingEl = document.getElementById('budget_remaining');
        const remaining = parseFloat(remainingEl?.value) || 0;

        if (total > 0 && remainingEl && remainingEl.value !== '' && total > remaining) {
            if (warning) warning.classList.remove('hidden');
            if (warningText) {
                warningText.innerText = `ยอดเงินที่ขอเบิก (฿${total.toLocaleString('th-TH', {minimumFractionDigits: 2})}) เกินกว่างบประมาณคงเหลือของโครงการ (คงเหลือ ฿${remaining.toLocaleString('th-TH', {minimumFractionDigits: 2})})`;
            }
        } else {
            if (warning) warning.classList.add('hidden');
        }
    }

    // 8. Handle Uploaded Files UI
    function handleBudgetFiles(input) {
        const list = document.getElementById('budget-file-list');
        if (!list) return;
        list.innerHTML = '';
        if (!input.files || input.files.length === 0) return;

        Array.from(input.files).forEach(f => {
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 shadow-2xs';
            badge.innerHTML = `<i class="fa-solid fa-paperclip text-blue-500"></i> ${f.name} <span class="text-slate-400 text-[10px]">(${(f.size/1024).toFixed(1)} KB)</span>`;
            list.appendChild(badge);
        });
    }

    // 9. Reset Form
    function resetBudgetForm() {
        const form = document.getElementById('inline-budget-form');
        if (form) form.reset();

        const tbody = document.getElementById('budget-items-tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr class="hover:bg-slate-50/80 transition budget-item-row">
                    <td class="py-2.5 px-4">
                        <input type="text" placeholder="เช่น ค่าเปลี่ยนจอภาพ / ซ่อมเมนบอร์ด / อะไหล่ RAM..." 
                               class="w-full bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition hover:border-slate-300">
                    </td>
                    <td class="py-2.5 px-4 text-center">
                        <input type="number" min="1" value="1" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" 
                               class="item-qty w-20 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-2 py-2 text-xs sm:text-sm text-center font-bold focus:outline-none transition hover:border-slate-300">
                    </td>
                    <td class="py-2.5 px-4 text-center">
                        <input type="text" placeholder="หน่วย" value="รายการ" 
                               class="w-20 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-2 py-2 text-xs sm:text-sm text-center font-medium focus:outline-none transition hover:border-slate-300">
                    </td>
                    <td class="py-2.5 px-4 text-right">
                        <input type="number" min="0" step="0.01" placeholder="0.00" onchange="calculateRowTotal(this)" onkeyup="calculateRowTotal(this)" 
                               class="item-price w-28 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl px-3 py-2 text-xs sm:text-sm text-right font-bold focus:outline-none transition hover:border-slate-300">
                    </td>
                    <td class="py-2.5 px-4 text-right font-black text-slate-800 text-sm item-total-text font-mono">
                        0.00
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <button type="button" onclick="removeBudgetItem(this)" class="text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors w-8 h-8 rounded-xl flex items-center justify-center mx-auto cursor-pointer" title="ลบรายการนี้">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </td>
                </tr>
            `;
        }

        const fileList = document.getElementById('budget-file-list');
        if (fileList) fileList.innerHTML = '';

        const warning = document.getElementById('budget-exceed-warning');
        if (warning) warning.classList.add('hidden');

        const totalElem = document.getElementById('budget-grand-total');
        if (totalElem) totalElem.innerText = '0.00 บาท';
        const textElem = document.getElementById('budget-text-amount');
        if (textElem) textElem.innerText = '( ศูนย์บาทถ้วน )';

        const projectSelect = document.getElementById('budget_project_id');
        if (projectSelect) {
            projectSelect.value = '';
            handleProjectChange(projectSelect);
        }
    }

    // 10. Submit Budget Request
    function submitBudgetRequest(form) {
        const select = document.getElementById('budget_project_id');
        if (!select || !select.value) {
            Swal.fire({
                title: 'กรุณาเลือกโครงการ',
                text: 'โปรดระบุโครงการที่ต้องการตัดยอดงบประมาณ',
                icon: 'warning',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#2563EB',
                customClass: { popup: 'rounded-3xl' }
            });
            return;
        }

        let totalAmount = 0;
        let itemSummaries = [];
        let itemsData = [];
        const rows = document.querySelectorAll('.budget-item-row');

        rows.forEach((row, idx) => {
            const desc = row.querySelector('input[type="text"]')?.value?.trim() || '';
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const unit = row.querySelectorAll('input[type="text"]')[1]?.value?.trim() || 'รายการ';
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            const lineTotal = qty * price;

            if (lineTotal > 0 || desc) {
                totalAmount += lineTotal;
                itemSummaries.push(`${desc || 'รายการที่ ' + (idx + 1)} (${qty} ${unit} @ ฿${price.toLocaleString('th-TH', {minimumFractionDigits: 2})})`);
                itemsData.push({
                    name: desc || ('รายการที่ ' + (idx + 1)),
                    qty: qty || 1,
                    unit: unit || 'รายการ',
                    price: price,
                    total: lineTotal
                });
            }
        });

        if (totalAmount <= 0) {
            Swal.fire({
                title: 'ไม่พบยอดเงินที่ต้องการเบิก',
                text: 'กรุณาระบุรายละเอียด จำนวน และราคาต่อหน่วยให้ถูกต้อง',
                icon: 'warning',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#2563EB',
                customClass: { popup: 'rounded-3xl' }
            });
            return;
        }

        const opt = select.options[select.selectedIndex];
        const projectName = opt.getAttribute('data-name') || opt.textContent.trim();
        const budgetRemaining = parseFloat(opt.getAttribute('data-remaining') || 0);

        if (totalAmount > budgetRemaining) {
            Swal.fire({
                title: 'งบประมาณคงเหลือไม่เพียงพอ',
                html: `ยอดที่ขอเบิก: <b class="text-red-600 font-mono">฿${totalAmount.toLocaleString('th-TH', {minimumFractionDigits: 2})}</b><br>
                       งบคงเหลือที่ใช้ได้: <b class="text-slate-800 font-mono">฿${budgetRemaining.toLocaleString('th-TH', {minimumFractionDigits: 2})}</b><br>
                       <span class="text-xs text-slate-500 mt-2 block">กรุณาปรับลดจำนวนเงิน หรือตรวจสอบโครงการที่เลือก</span>`,
                icon: 'error',
                confirmButtonText: 'รับทราบ',
                confirmButtonColor: '#EF4444',
                customClass: { popup: 'rounded-3xl' }
            });
            return;
        }

        const reasonInput = document.getElementById('budget_request_reason');
        const reasonText = reasonInput ? reasonInput.value.trim() : '';
        if (!reasonText) {
            Swal.fire({
                title: 'กรุณาระบุเหตุผลความจำเป็น',
                text: 'โปรดระบุเหตุผลในการขอเบิกงบประมาณ',
                icon: 'warning',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#2563EB',
                customClass: { popup: 'rounded-3xl' }
            });
            return;
        }

        const combinedReason = (itemSummaries.join(', ') + ' | ' + reasonText).trim();

        Swal.fire({
            title: 'ยืนยันการขอเบิกงบประมาณ?',
            html: `
                <div class="text-left text-xs bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2.5 font-medium">
                    <div class="pb-2 border-b border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">โครงการ</span>
                        <p class="font-bold text-slate-800 text-sm">${projectName}</p>
                    </div>
                    <div class="space-y-1">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">รายการที่ขอเบิก (${itemSummaries.length} รายการ)</span>
                        <div class="max-h-24 overflow-y-auto space-y-1 text-slate-600 pr-1">
                            ${itemSummaries.map(s => `<p class="truncate">• ${s}</p>`).join('')}
                        </div>
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-b border-slate-200">
                        <span class="text-slate-500 font-bold">ยอดเงินที่ขอเบิกรวม:</span>
                        <span class="font-black text-blue-600 font-mono text-base">฿${totalAmount.toLocaleString('th-TH', {minimumFractionDigits: 2})}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 italic text-right">
                        ${bahtText(totalAmount)}
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-emerald-700 font-bold">คงเหลือหลังตัดยอด:</span>
                        <span class="font-black text-emerald-600 font-mono">฿${(budgetRemaining - totalAmount).toLocaleString('th-TH', {minimumFractionDigits: 2})}</span>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันบันทึก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#2563EB',
            cancelButtonColor: '#64748B',
            customClass: { popup: 'rounded-3xl' }
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'กำลังบันทึกและตัดยอดงบประมาณ...',
                text: 'ระบบกำลังตัดยอดงบประมาณโครงการอัตโนมัติ กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const rjiCodeEl = document.getElementById('rji-job-code');
            const linkedJobCode = (rjiCodeEl && rjiCodeEl.textContent && rjiCodeEl.textContent.trim() !== '—')
                ? rjiCodeEl.textContent.trim()
                : null;

            fetch("{{ route('budget.deduct') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    project_id: select.value,
                    amount: totalAmount,
                    reason: combinedReason,
                    items_json: JSON.stringify(itemsData),
                    source_page: 'หน้าเจ้าหน้าที่ซ่อมบำรุง',
                    repair_request_code: linkedJobCode
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    opt.setAttribute('data-used', data.project.used_budget);
                    opt.setAttribute('data-remaining', data.project.remaining);

                    Swal.fire({
                        title: 'ตัดยอดงบประมาณสำเร็จ!',
                        html: `บันทึกคำขอเบิกและตัดยอดงบประมาณโครงการเรียบร้อยแล้ว<br>
                               <div class="mt-3 p-3 bg-slate-50 rounded-xl text-left text-xs font-semibold text-slate-600 space-y-1 border border-slate-100">
                                   <p>• ยอดที่ตัดงบ: <b class="text-blue-600 font-bold">฿${totalAmount.toLocaleString('th-TH', {minimumFractionDigits: 2})}</b></p>
                                   <p>• ใช้ไปแล้วรวม: <b class="text-amber-600 font-bold">฿${parseFloat(data.project.used_budget).toLocaleString('th-TH', {minimumFractionDigits: 2})}</b></p>
                                   <p>• คงเหลือสุทธิ: <b class="text-emerald-600 font-bold">฿${parseFloat(data.project.remaining).toLocaleString('th-TH', {minimumFractionDigits: 2})}</b></p>
                               </div>`,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: '🖨️ พิมพ์ใบบันทึกค่าซ่อม',
                        cancelButtonText: 'ไปที่ประวัติการเบิก',
                        confirmButtonColor: '#2563EB',
                        cancelButtonColor: '#64748B',
                        customClass: { popup: 'rounded-3xl' }
                    }).then((result) => {
                        const newReqId = (data.requisition && data.requisition.request_id) ? data.requisition.request_id : (data.requisition && data.requisition.request_code ? data.requisition.request_code : '');
                        if (result.isConfirmed && newReqId) {
                            printRepairCost(newReqId, {
                                code: (data.requisition && data.requisition.request_code) || linkedJobCode,
                                project_name: projectName,
                                budget_source: opt.getAttribute('data-source') || 'งบประมาณกลาง',
                                raw_amount: totalAmount,
                                reason: combinedReason,
                                items: itemSummaries.join(', '),
                                items_json: JSON.stringify(itemsData)
                            });
                        }
                        resetBudgetForm();
                        window.location.href = window.location.pathname + '?tab=requisition&view=history';
                    });
                } else {
                    Swal.fire({
                        title: 'ไม่สามารถตัดยอดได้',
                        text: data.message || 'เกิดข้อผิดพลาดในการบันทึก',
                        icon: 'error',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#EF4444',
                        customClass: { popup: 'rounded-3xl' }
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์เพื่อตัดยอดงบประมาณได้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#EF4444',
                    customClass: { popup: 'rounded-3xl' }
                });
            });
        });
    }

    // 11. Requisition History Filter Functions
    function filterRequisitions() {
        const searchInput = document.getElementById('requisition-search-input');
        const term = (searchInput ? searchInput.value : '').toLowerCase().trim();

        const rows = document.querySelectorAll('.requisition-row');
        let matchedCount = 0;
        rows.forEach(row => {
            const code = (row.getAttribute('data-code') || '').toLowerCase();
            const project = (row.getAttribute('data-project') || '').toLowerCase();
            const source = (row.getAttribute('data-source') || '').toLowerCase();
            const textContent = row.textContent.toLowerCase();

            const matchesSearch = !term || code.includes(term) || project.includes(term) || source.includes(term) || textContent.includes(term);

            if (matchesSearch) {
                row.style.display = '';
                matchedCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResultRow = document.getElementById('req-no-results-row');
        if (noResultRow) {
            noResultRow.classList.toggle('hidden', matchedCount > 0 || rows.length === 0);
        }
    }

    function resetRequisitionFilters() {
        const searchInput = document.getElementById('requisition-search-input');
        if (searchInput) searchInput.value = '';
        filterRequisitions();
    }

    let currentRequisitionData = null;

    function openViewRequisitionModalFromBtn(btn) {
        if (!btn) return;
        const req = {
            id: btn.getAttribute('data-id'),
            code: btn.getAttribute('data-code'),
            date: btn.getAttribute('data-date'),
            project_name: btn.getAttribute('data-project'),
            budget_source: btn.getAttribute('data-source'),
            amount: btn.getAttribute('data-amount'),
            raw_amount: btn.getAttribute('data-raw-amount'),
            user_name: btn.getAttribute('data-user'),
            assignee_name: btn.getAttribute('data-assignee'),
            status: btn.getAttribute('data-status'),
            status_class: btn.getAttribute('data-status-class'),
            items: btn.getAttribute('data-items'),
            reason: btn.getAttribute('data-reason'),
            raw_desc: btn.getAttribute('data-raw-desc')
        };
        openViewRequisitionModal(req);
    }

    function openViewRequisitionModal(req) {
        if (!req) return;
        currentRequisitionData = req;
        const card = document.getElementById('inline-req-detail-card');
        if (!card) return;

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = val || '-';
        };

        setVal('view-req-code', req.code);
        setVal('view-req-amount', req.amount || '฿0.00');
        setVal('view-req-date', req.date);
        
        const statusEl = document.getElementById('view-req-status');
        if (statusEl) {
            statusEl.innerText = req.status || '-';
            statusEl.className = 'px-3 py-1 rounded-full text-xs font-bold inline-block border ' + (req.status_class || 'bg-slate-100 text-slate-700');
        }

        setVal('view-req-project', (req.project_name ? req.project_name : '-') + (req.project_code ? ' (' + req.project_code + ')' : ''));
        setVal('view-req-source', req.budget_source);
        setVal('view-req-device', req.device);
        setVal('view-req-location', req.location);
        
        let fullDetail = '';
        if (req.items && req.items !== '-') {
            fullDetail += 'รายการที่ขอเบิก:\n' + req.items;
        }
        if (req.reason && req.reason !== '-') {
            fullDetail += (fullDetail ? '\n\nเหตุผลความจำเป็น:\n' : '') + req.reason;
        }
        setVal('view-req-reason', fullDetail || (req.raw_desc || 'ไม่มีรายละเอียดเพิ่มเติม'));

        card.classList.remove('hidden');
        card.style.display = 'flex';
        document.body.classList.add('overflow-hidden');
    }

    function closeViewRequisitionModal() {
        const card = document.getElementById('inline-req-detail-card');
        if (card) {
            card.classList.add('hidden');
            card.style.display = 'none';
        }
        document.body.classList.remove('overflow-hidden');
    }

    function printCurrentRequisition() {
        if (!currentRequisitionData) return;
        const reqId = currentRequisitionData.id || currentRequisitionData.code;
        printRepairCost(reqId, currentRequisitionData);
    }

    function printRepairCost(id, dataOrCode) {
        let baseUrl = "{{ url('/repair-requests') }}/" + encodeURIComponent(id) + "/print-cost";
        const params = new URLSearchParams();
        params.set('auto_print', '1');

        if (dataOrCode && typeof dataOrCode === 'object') {
            if (dataOrCode.code) params.set('code', dataOrCode.code);
            if (dataOrCode.project_name) params.set('project_name', dataOrCode.project_name);
            if (dataOrCode.budget_source) params.set('budget_source', dataOrCode.budget_source);
            if (dataOrCode.raw_amount) params.set('amount', dataOrCode.raw_amount);
            if (dataOrCode.items_json) params.set('items_json', dataOrCode.items_json);
            if (dataOrCode.items) params.set('items', dataOrCode.items);
            if (dataOrCode.raw_desc) {
                params.set('reason', dataOrCode.raw_desc);
            } else if (dataOrCode.reason) {
                params.set('reason', dataOrCode.reason);
            }
            if (dataOrCode.user_name) params.set('user_name', dataOrCode.user_name);
        } else if (typeof dataOrCode === 'string') {
            params.set('code', dataOrCode);
        }

        const fullUrl = baseUrl + '?' + params.toString();
        window.open(fullUrl, '_blank');
    }

    function previewCurrentFormPrint() {
        const select = document.getElementById('budget_project_id');
        const opt = select && select.selectedIndex > 0 ? select.options[select.selectedIndex] : null;
        const projectName = opt ? (opt.getAttribute('data-name') || opt.textContent.trim()) : 'งบประมาณกลาง';
        const budgetSource = opt ? (opt.getAttribute('data-source') || 'งบประมาณกลาง') : 'งบประมาณกลาง';
        
        let totalAmount = 0;
        let itemSummaries = [];
        let itemsData = [];
        document.querySelectorAll('.budget-item-row').forEach((row, idx) => {
            const desc = row.querySelector('input[type="text"]')?.value?.trim() || '';
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const unit = row.querySelectorAll('input[type="text"]')[1]?.value?.trim() || 'รายการ';
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            const lineTotal = qty * price;
            if (lineTotal > 0 || desc) {
                totalAmount += lineTotal;
                itemSummaries.push(`${desc || 'รายการที่ ' + (idx + 1)} (${qty} ${unit} @ ฿${price.toLocaleString('th-TH', {minimumFractionDigits: 2})})`);
                itemsData.push({
                    name: desc || ('รายการที่ ' + (idx + 1)),
                    qty: qty || 1,
                    unit: unit || 'รายการ',
                    price: price,
                    total: lineTotal
                });
            }
        });
        
        const reasonInput = document.getElementById('budget_request_reason');
        const reasonText = reasonInput ? reasonInput.value.trim() : '';
        const combined = (itemSummaries.join(', ') + (reasonText ? ' | ' + reasonText : '')).trim();

        const params = new URLSearchParams();
        params.set('code', 'REQ-' + (new Date().getFullYear() + 543) + '-PREVIEW');
        params.set('project_name', projectName);
        params.set('budget_source', budgetSource);
        params.set('amount', totalAmount);
        params.set('reason', combined);
        params.set('items', itemSummaries.join(', '));
        params.set('items_json', JSON.stringify(itemsData));
        params.set('user_name', '{{ Auth::user()->name ?? "" }}');
        params.set('auto_print', '0');

        const previewUrl = "{{ url('/repair-requests') }}/PREVIEW/print-cost?" + params.toString();
        window.open(previewUrl, '_blank', 'width=1000,height=900,scrollbars=yes,status=no,toolbar=no');
    }

    function goToRepairRecord(data) {
        if (typeof switchTab === 'function') {
            switchTab('tab-requisition', null);
        }
        setTimeout(function() {
            switchRequisitionView('form');
        }, 50);

        setTimeout(function() {
            const infoCard = document.getElementById('repair-job-info-card');
            if (infoCard) {
                const codeEl = document.getElementById('rji-job-code');
                const deviceEl = document.getElementById('rji-device');
                const locationEl = document.getElementById('rji-location');
                const detailEl = document.getElementById('rji-detail');
                const detailRow = document.getElementById('rji-detail-row');

                if (codeEl) codeEl.textContent = data.job_code || '—';
                if (deviceEl) deviceEl.textContent = data.device || '—';
                if (locationEl) locationEl.textContent = data.location || '—';

                const detailText = (data.repair_details && data.repair_details.trim())
                    ? data.repair_details.trim()
                    : (data.description && data.description.trim() ? data.description.trim() : '');
                if (detailText && detailEl && detailRow) {
                    detailEl.textContent = detailText;
                    detailRow.classList.remove('hidden');
                } else if (detailRow) {
                    detailRow.classList.add('hidden');
                }

                infoCard.classList.remove('hidden');
            }

            const tbody = document.getElementById('budget-items-tbody');
            if (tbody) {
                const firstRow = tbody.querySelector('.budget-item-row');
                if (firstRow) {
                    const descInput = firstRow.querySelector('input[type="text"]');
                    if (descInput && !descInput.value) {
                        const detail = (data.repair_details && data.repair_details.trim())
                            ? data.repair_details.trim()
                            : (data.description && data.description.trim() ? data.description.trim() : '');
                        if (detail) {
                            descInput.value = detail;
                        }
                    }
                }
            }

            const formSection = document.getElementById('req-section-form');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 150);
    }

    function closeRepairJobInfoCard() {
        const card = document.getElementById('repair-job-info-card');
        if (card) card.classList.add('hidden');
    }

    function openCreateBudgetModal(repairRequestId) {
        if (typeof switchTab === 'function') {
            switchTab('tab-requisition', null);
        }
        setTimeout(() => {
            switchRequisitionView('form');
        }, 50);
    }

    function closeCreateBudgetModal() {
        // Modal replaced with unified inline form
    }

    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        const reqView = params.get('view');
        if (tab === 'requisition' || tab === 'tab-requisition') {
            if (typeof switchTab === 'function') {
                switchTab('tab-requisition', null);
            }
            if (reqView === 'history') {
                switchRequisitionView('history');
            } else {
                switchRequisitionView('form');
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

function confirmLogout() {
    const modal = document.getElementById('logout-modal');
    if (modal) modal.classList.remove('hidden');
}

function closeLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) modal.classList.add('hidden');
}

const logoutModalEl = document.getElementById('logout-modal');
if (logoutModalEl) {
    logoutModalEl.addEventListener('click', function(e) {
        if (e.target === this) closeLogoutModal();
    });
}

window.openProfileModal = function() {
    const modal = document.getElementById('modal-technician-profile');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
};

window.closeProfileModal = function() {
    const modal = document.getElementById('modal-technician-profile');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
};

const profileModal = document.getElementById('modal-technician-profile');
if (profileModal) {
    profileModal.addEventListener('click', function(e) {
        if (e.target === this) closeProfileModal();
    });
}

// Tab Switching Logic (พร้อมจำแท็บเมื่อรีเฟรช)
function switchTab(tabId, element) {
    try {
        console.log('switchTab called:', tabId, element);
        
        if (!element) {
            element = document.querySelector('aside a.desktop-tab[onclick*="' + tabId + '"]') || document.querySelector('.desktop-tab[onclick*="' + tabId + '"]') || document.querySelector('.mobile-tab[onclick*="' + tabId + '"]');
        }

        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.remove('block');
            el.classList.add('hidden');
        });
        
        const target = document.getElementById(tabId);
        if (target) {
            target.classList.remove('hidden');
            target.classList.add('block');

            if (tabId === 'tab-history') {
                setTimeout(() => {
                    if (typeof setTechnicianHistoryView === 'function') {
                        setTechnicianHistoryView(currentTechnicianHistoryView || 'chart');
                    }
                }, 60);
            }
        } else {
            console.warn('Target tab content not found:', tabId);
        }
        
        // For sidebar tabs (Desktop)
        document.querySelectorAll('.desktop-tab').forEach(el => {
            el.className = el.className.replace(/\bborder-white\b/g, 'border-transparent')
                                       .replace(/\btext-blue-900\b/g, 'text-blue-100/70')
                                       .replace(/\bbg-white\b/g, '')
                                       .replace(/\bshadow-md\b/g, '')
                                       .replace(/\bfont-bold\b/g, 'font-semibold')
                                       .replace(/\bhover:bg-white\/10\b/g, '')
                                       .replace(/\bhover:text-white\b/g, '') + ' hover:bg-white/10 hover:text-white';
        });
        
        // For mobile tabs (Mobile)
        document.querySelectorAll('.mobile-tab').forEach(el => {
            el.className = el.className.replace(/\btext-blue-600\b/g, 'text-slate-400')
                                       .replace(/\bfont-bold\b/g, 'font-semibold')
                                       .replace(/\bhover:text-blue-500\b/g, '') + ' hover:text-blue-500';
        });

        if(element) {
            if(element.classList.contains('desktop-tab')) {
                element.className = element.className.replace(/\bborder-transparent\b/g, 'border-white')
                                           .replace(/\btext-blue-100\/70\b/g, 'text-blue-900')
                                           .replace(/\bhover:bg-white\/10\b/g, '')
                                           .replace(/\bhover:text-white\b/g, '')
                                           .replace(/\bfont-semibold\b/g, 'font-bold') + ' bg-white shadow-md';
                
                // Also sync mobile tab
                try {
                    const mobileEquivalent = document.querySelector('.mobile-tab[onclick*="' + tabId + '"]');
                    if(mobileEquivalent) {
                        mobileEquivalent.className = mobileEquivalent.className.replace(/\btext-slate-400\b/g, 'text-blue-600')
                                                                       .replace(/\bfont-semibold\b/g, 'font-bold')
                                                                       .replace(/\bhover:text-blue-500\b/g, '');
                    }
                } catch(e) { console.error('Error syncing mobile tab', e); }
            } else if (element.classList.contains('mobile-tab')) {
                element.className = element.className.replace(/\btext-slate-400\b/g, 'text-blue-600')
                                           .replace(/\bfont-semibold\b/g, 'font-bold')
                                           .replace(/\bhover:text-blue-500\b/g, '');
                
                // Also sync desktop tab
                try {
                    const desktopEquivalent = document.querySelector('.desktop-tab[onclick*="' + tabId + '"]');
                    if(desktopEquivalent) {
                        desktopEquivalent.className = desktopEquivalent.className.replace(/\bborder-transparent\b/g, 'border-white')
                                                                       .replace(/\btext-blue-100\/70\b/g, 'text-blue-900')
                                                                       .replace(/\bhover:bg-white\/10\b/g, '')
                                                                       .replace(/\bhover:text-white\b/g, '')
                                                                       .replace(/\bfont-semibold\b/g, 'font-bold') + ' bg-white shadow-md';
                    }
                } catch(e) { console.error('Error syncing desktop tab', e); }
            }
        }

        // บันทึกแท็บที่เลือกเพื่อให้อยู่หน้าเดิมเวลารีเฟรช
        try {
            const url = new URL(window.location);
            const shortName = tabId.replace(/^tab-/, '');
            url.searchParams.set('tab', shortName);
            window.history.replaceState(null, '', url);
            sessionStorage.setItem('active_technician_tab', tabId);
        } catch (e) {
            console.error('Error saving tab state:', e);
        }

        if (tabId === 'tab-history') {
            setTimeout(function() {
                if (typeof applyHistoryFilter === 'function') applyHistoryFilter();
            }, 50);
        }
    } catch (err) {
        console.error('Error in switchTab:', err);
    }
}

// กู้คืนแท็บที่เปิดค้างไว้เมื่อรีเฟรชหน้า
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
            targetTab = sessionStorage.getItem('active_technician_tab');
        }

        if (targetTab && document.getElementById(targetTab)) {
            const el = document.querySelector('aside a.desktop-tab[onclick*="' + targetTab + '"]') || document.querySelector('.desktop-tab[onclick*="' + targetTab + '"]') || document.querySelector('.mobile-tab[onclick*="' + targetTab + '"]');
            switchTab(targetTab, el);
        }
    } catch (e) {
        console.error('Error restoring tab:', e);
    }
});

// Toggle Password Visibility
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
}

// Preview profile photo upload
function previewPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const wrapper = document.getElementById('avatar-wrapper');
        const initials = document.getElementById('avatar-initials');
        if (initials) initials.remove();

        let img = document.getElementById('avatar-preview');
        if (!img) {
            img = document.createElement('img');
            img.id = 'avatar-preview';
            img.className = 'w-full h-full object-cover';
            img.alt = 'Profile Photo';
            if (wrapper) wrapper.appendChild(img);
        }
        if (img) img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Device Selection - Code updates
function updateDeviceCode() {
        const select = document.getElementById("device-select-input");
        const display = document.getElementById("device-code-display");
        if (!select || !display) return;
        
        let selectedOption = null;
        if (select.tagName && select.tagName.toLowerCase() === "select") {
            selectedOption = select.options[select.selectedIndex];
        } else {
            const list = document.getElementById("device-list");
            if (list) {
                for (let opt of list.options) {
                    if (opt.value === select.value) {
                        selectedOption = opt;
                        break;
                    }
                }
            }
        }
    
    if (selectedOption && selectedOption.value !== "") {
        display.value = selectedOption.getAttribute("data-code") || "";
    } else {
        display.value = "";
    }
}

// Dynamic Building -> Floors
const floorData = {
    "อาคาร 1": 3,
    "อาคาร 2": 5,
    "อาคาร 3": 4,
    "อาคาร 4": 6,
    "อาคาร 5": 3,
    "อาคารเนกประสงค์": 6,
    "แฟลต 1": 3
};

function updateFloors() {
    const buildingSelect = document.getElementById("building-select");
    const floorSelect = document.getElementById("floor-select");
    const roomSelect = document.getElementById("room-select");
    if (!buildingSelect || !floorSelect || !roomSelect) return;
    const selectedBuilding = buildingSelect.value;

    floorSelect.innerHTML = "";
    roomSelect.innerHTML = "";

    // Reset rooms select
    roomSelect.disabled = true;
    const defaultRoomOpt = document.createElement("option");
    defaultRoomOpt.value = "";
    defaultRoomOpt.text = "-- กรุณาเลือกชั้นก่อน --";
    roomSelect.appendChild(defaultRoomOpt);

    if (selectedBuilding === "") {
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

        const floorsCount = floorData[selectedBuilding];
        if (floorsCount) {
            for (let i = 1; i <= floorsCount; i++) {
                const opt = document.createElement("option");
                opt.value = "ชั้น " + i;
                opt.text = "ชั้น " + i;
                floorSelect.appendChild(opt);
            }
        }
    }
}

// Dynamic Floors -> Rooms
function updateRooms() {
    const floorSelect = document.getElementById("floor-select");
    const roomSelect = document.getElementById("room-select");
    if (!floorSelect || !roomSelect) return;
    const selectedFloor = floorSelect.value;

    roomSelect.innerHTML = "";

    if (selectedFloor === "") {
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

        const floorNumStr = selectedFloor.replace("ชั้น ", "");
        const floorNum = parseInt(floorNumStr);

        if (!isNaN(floorNum)) {
            for (let i = 1; i <= 5; i++) {
                const roomNum = floorNum * 100 + i;
                const opt = document.createElement("option");
                opt.value = "ห้อง " + roomNum;
                opt.text = "ห้อง " + roomNum;
                roomSelect.appendChild(opt);
            }
        }
    }
}

// Helper for device icon in JS
function getJsDeviceIcon(deviceName) {
    if (!deviceName) return 'fa-desktop';
    const name = deviceName.toLowerCase();
    if (name.includes('notebook') || name.includes('โน้ตบุ๊ก') || name.includes('โน๊ตบุ๊ค') || name.includes('laptop')) return 'fa-laptop';
    if (name.includes('mouse') || name.includes('เมาส์')) return 'fa-computer-mouse';
    if (name.includes('keyboard') || name.includes('คีย์บอร์ด') || name.includes('แป้นพิมพ์')) return 'fa-keyboard';
    if (name.includes('printer') || name.includes('เครื่องพิมพ์') || name.includes('ปริ้นเตอร์')) return 'fa-print';
    if (name.includes('server') || name.includes('เซิร์ฟเวอร์')) return 'fa-server';
    if (name.includes('network') || name.includes('router') || name.includes('switch') || name.includes('เครือข่าย')) return 'fa-network-wired';
    if (name.includes('monitor') || name.includes('จอ') || name.includes('โปรเจคเตอร์')) return 'fa-display';
    return 'fa-desktop';
}

// History Modal logics
function openHistoryModal(data) {
    const modal = document.getElementById('history-modal');
    const box = document.getElementById('history-modal-box');
    if (!modal || !box) return;

    document.getElementById('hm-code').textContent = data.code;
    document.getElementById('hm-title').textContent = data.title;
    document.getElementById('hm-device-name').textContent = data.device;
    document.getElementById('hm-location').textContent = data.location;
    let cleanDesc = (data.description || '').replace(/(ช่วงเวลาที่สะดวก(ให้เข้าซ่อม)?|วันที่สะดวก(ให้เข้าซ่อม)?|เวลาที่สะดวก)[^\n\r]*/gi, '').trim();
    document.getElementById('hm-description').textContent = cleanDesc;
    document.getElementById('hm-assignee').textContent = data.assignee;
    const repEl = document.getElementById('hm-reporter');
    if (repEl) repEl.textContent = data.reporter || 'ไม่ระบุ';
    document.getElementById('hm-created').textContent = data.created_at;
    document.getElementById('hm-finished').textContent = data.finished_at;
    document.getElementById('hm-note').textContent = data.note;

    const iconEl = document.getElementById('hm-device-icon');
    if (iconEl) {
        iconEl.className = 'fa-solid mr-1 text-slate-400 ' + getJsDeviceIcon(data.device);
    }
    const prioEl = document.getElementById('hm-priority');
    if (prioEl) {
        if (data.priority === 'ด่วนที่สุด') {
            prioEl.textContent = '🚨 ด่วนที่สุด';
            prioEl.className = 'text-sm font-black text-blue-600';
        } else {
            prioEl.textContent = '🟢 ระดับปกติ';
            prioEl.className = 'text-sm font-bold text-emerald-600';
        }
    }

    // Status
    const statusEl = document.getElementById('hm-status');
    if (statusEl) {
        statusEl.textContent = data.status;
        statusEl.className = 'text-xs font-extrabold px-3 py-1 rounded-full border whitespace-nowrap flex-shrink-0 ' + data.statusClass;
    }

    // Show Note Box only if there is a note
    const noteBox = document.getElementById('hm-note-box');
    if (noteBox) {
        if (data.note && data.note !== '-' && data.note !== '') {
            noteBox.classList.remove('hidden');
        } else {
            noteBox.classList.add('hidden');
        }
    }

    // Show Rating Box only if rated
    const ratingBox = document.getElementById('hm-rating-box');
    const starsEl = document.getElementById('hm-stars');
    const scoreEl = document.getElementById('hm-rating-score');
    const badgeEl = document.getElementById('hm-rating-badge');
    if (ratingBox) {
        const ratingVal = Number(data.rating || 0);
        if (ratingVal > 0) {
            ratingBox.classList.remove('hidden');
            if (starsEl) {
                starsEl.innerHTML = Array.from({length: 5}, (_, i) =>
                    `<i class="fa-solid fa-star ${i < ratingVal ? 'text-amber-400' : 'text-slate-200'}"></i>`
                ).join('');
            }
            if (scoreEl) scoreEl.textContent = ratingVal.toFixed(1) + ' / 5.0 คะแนน';
            if (badgeEl) badgeEl.textContent = data.rating_text || (ratingVal === 5 ? 'ดีมาก' : (ratingVal === 4 ? 'ดี' : (ratingVal === 3 ? 'ปานกลาง' : (ratingVal === 2 ? 'พอใช้' : 'ปรับปรุง'))));
        } else {
            ratingBox.classList.add('hidden');
        }
    }

    // Show Feedback Box only if there is a feedback comment
    const feedbackBox = document.getElementById('hm-feedback-box');
    const feedbackEl = document.getElementById('hm-feedback');
    if (feedbackBox) {
        if (data.eval_comment && data.eval_comment.trim() !== '' && data.eval_comment !== '-') {
            feedbackBox.classList.remove('hidden');
            if (feedbackEl) feedbackEl.textContent = `"${data.eval_comment}"`;
        } else {
            feedbackBox.classList.add('hidden');
        }
    }

    // Image in history modal
    const hmImgContainer = document.getElementById('hm-image-container');
    const hmImgPreview = document.getElementById('hm-image-preview');
    const hmImgLink = document.getElementById('hm-image-link');
    if (hmImgContainer && hmImgPreview) {
        if (data.image && data.image.trim() !== '') {
            hmImgPreview.src = data.image;
            if (hmImgLink) hmImgLink.href = data.image;
            hmImgContainer.classList.remove('hidden');
        } else {
            hmImgPreview.src = '';
            if (hmImgLink) hmImgLink.href = '#';
            hmImgContainer.classList.add('hidden');
        }
    }

    // Show Modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        box.classList.remove('scale-95', 'opacity-0');
        box.classList.add('scale-100', 'opacity-100');
    });
}


function closeHistoryModal(event) {
    if (event && event.target !== document.getElementById('history-modal')) return;
    const modal = document.getElementById('history-modal');
    const box = document.getElementById('history-modal-box');
    if (!modal || !box) return;

    box.classList.remove('scale-100', 'opacity-100');
    box.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}


// Direct Form Logics
const assignedJobsData = @json($assignedRepairs->keyBy('id'));

function updateFormFields(jobId) {
    const job = assignedJobsData[jobId];
    const formSection = document.getElementById('repair-form-fields');
    const form = document.getElementById('direct-repair-update-form');
    
    if (!job || !formSection || !form) {
        if (formSection) formSection.classList.add('hidden');
        return;
    }
    
    formSection.classList.remove('hidden');
    form.action = `{{ url('/repair-requests') }}/${job.id}`;
    
    document.getElementById('form-job-code').textContent = job.request_code;
    document.getElementById('form-job-device').textContent = (job.device ? job.device.name : 'อุปกรณ์ไม่ระบุ') + ' (' + (job.device ? job.device.device_code : '-') + ')';
    document.getElementById('form-job-location').textContent = job.location;
    document.getElementById('form-job-reporter').textContent = job.user ? job.user.name : 'ครู/บุคลากร';
    document.getElementById('form-job-desc').textContent = job.description;
    
    document.getElementById('direct-status').value = job.status;
    document.getElementById('direct-budget').value = job.budget || 0;
    document.getElementById('direct-note').value = job.note || '';
    
    document.getElementById('direct-checked-power').checked = !!job.checked_power;
    document.getElementById('direct-checked-cleaning').checked = !!job.checked_cleaning;
    document.getElementById('direct-checked-ping').checked = !!job.checked_ping;
    
    document.getElementById('direct-repair-hw').checked = !!job.repair_hw;
    document.getElementById('direct-repair-sw').checked = !!job.repair_sw;
    document.getElementById('direct-repair-net').checked = !!job.repair_net;
}

function selectJobToRecord(jobId) {
    const reportTab = document.querySelector('[onclick="switchTab(\'tab-report\', this)"]');
    if (reportTab) {
        switchTab('tab-report', reportTab);
    }
    const selectEl = document.getElementById('job-select-form');
    if (selectEl) {
        selectEl.value = jobId;
        updateFormFields(jobId);
    }
}

function syncRepairType() {
    if (document.getElementById('hidden_repair_hw')) document.getElementById('hidden_repair_hw').disabled = !document.getElementById('repair_hw_radio').checked;
    if (document.getElementById('hidden_repair_sw')) document.getElementById('hidden_repair_sw').disabled = !document.getElementById('repair_sw_radio').checked;
    if (document.getElementById('hidden_repair_net')) document.getElementById('hidden_repair_net').disabled = !document.getElementById('repair_net_radio').checked;
}

function submitBudgetRequest() {
    const projectId = document.getElementById('budget_project_id')?.value;
    if (!projectId) {
        Swal.fire({
            title: 'กรุณาเลือกโครงการ',
            text: 'กรุณาเลือกโครงการก่อนบันทึก',
            icon: 'warning',
            confirmButtonColor: '#4F46E5',
            customClass: { popup: 'rounded-[2rem]' }
        });
        return;
    }

    // อัปเดต hidden input สำหรับ project_id
    const projectSelect = document.getElementById('budget_project_id');
    if (projectSelect) {
        projectSelect.name = 'project_id';
    }

    // คำนวณยอดรวมและใส่ใน hidden input
    const grandTotalText = document.getElementById('budget-grand-total')?.innerText || '0';
    const totalNum = parseFloat(grandTotalText.replace(/[^0-9.]/g, '')) || 0;
    const totalHidden = document.getElementById('budget_amount_total_hidden');
    if (totalHidden) totalHidden.value = totalNum;

    const form = document.getElementById('budget-request-form');
    if (!form || !form.action || form.action.includes('void')) {
        Swal.fire({
            title: 'ข้อผิดพลาด',
            text: 'กรุณาเปิด modal ผ่านปุ่มขอเบิกงบประมาณจากใบงาน',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }

    Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    form.submit();
}

// STEP-BY-STEP REPAIR MODAL LOGIC (1. Inspection -> 2. Evaluation -> Action)
let currentEvaluation = 'can_repair';
let currentActionStatus = 'completed';

function setEvaluation(type) {
    currentEvaluation = type;
    const btnCan = document.getElementById('eval-btn-can');
    const btnCannot = document.getElementById('eval-btn-cannot');
    const secCan = document.getElementById('section-can-repair');
    const secCannot = document.getElementById('section-cannot-repair');
    const evalInput = document.getElementById('update-eval-input');
    const statusInput = document.getElementById('update-status-input');
    const submitBtn = document.getElementById('btn-submit-repair');

    const unselectedBtnClass = 'eval-choice-btn p-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-600 hover:border-slate-300 flex items-center gap-3 transition-all cursor-pointer text-left';

    if (type === 'can_repair') {
        if (btnCan) btnCan.className = 'eval-choice-btn p-3 rounded-2xl border-2 border-emerald-500 bg-emerald-50/80 text-emerald-800 shadow-xs flex items-center gap-3 transition-all cursor-pointer text-left ring-2 ring-emerald-400/20';
        if (btnCannot) btnCannot.className = unselectedBtnClass;
        if (secCan) secCan.classList.remove('hidden');
        if (secCannot) secCannot.classList.add('hidden');
        if (evalInput) evalInput.value = 'สามารถซ่อมได้';

        setActionStatus(currentActionStatus);
    } else {
        if (btnCan) btnCan.className = unselectedBtnClass;
        if (btnCannot) btnCannot.className = 'eval-choice-btn p-3 rounded-2xl border-2 border-rose-500 bg-rose-50/80 text-rose-800 shadow-xs flex items-center gap-3 transition-all cursor-pointer text-left ring-2 ring-rose-400/20';
        if (secCan) secCan.classList.add('hidden');
        if (secCannot) secCannot.classList.remove('hidden');
        if (evalInput) evalInput.value = 'ไม่สามารถซ่อมได้';
        if (statusInput) statusInput.value = 'ไม่สามารถซ่อมได้';

        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> <span>บันทึก</span>';
            submitBtn.className = 'px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer';
        }
    }
}

let currentEditingJob = null;

function setActionStatus(action) {
    if (action === 'completed') {
        const needsCost = currentEditingJob && (currentEditingJob.status === 'รอเปลี่ยนอะไหล่' || currentEditingJob.status === 'รออะไหล่' || currentEditingJob.needs_cost);
        const hasCost = currentEditingJob && (currentEditingJob.has_cost || (currentEditingJob.budget && parseFloat(currentEditingJob.budget) > 0));
        if (needsCost && !hasCost) {
            Swal.fire({
                title: 'ยังไม่สามารถเลือกสถานะนี้ได้',
                text: 'รายการนี้อยู่ในสถานะรอเปลี่ยนอะไหล่ ต้องให้ผู้แจ้งซ่อมบันทึกค่าซ่อมให้เรียบร้อยก่อน จึงจะสามารถปิดงานซ่อมได้',
                icon: 'warning',
                confirmButtonText: 'รับทราบ',
                confirmButtonColor: '#2563EB',
                customClass: { popup: 'rounded-3xl' }
            });
            return;
        }
    }

    currentActionStatus = action;
    const btnDone = document.getElementById('action-btn-completed');
    const btnWait = document.getElementById('action-btn-wait_parts');
    const statusInput = document.getElementById('update-status-input');
    const submitBtn = document.getElementById('btn-submit-repair');
    const detailsLabel = document.getElementById('repair-details-label');

    const unselectedBtnClass = 'action-choice-btn p-2.5 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:border-slate-300 flex items-center justify-center gap-2 text-xs font-bold transition cursor-pointer';

    if (action === 'completed') {
        if (btnDone) btnDone.className = 'action-choice-btn p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 text-emerald-800 flex items-center justify-center gap-2 text-xs font-bold transition cursor-pointer ring-2 ring-emerald-400/20';
        if (btnWait) btnWait.className = unselectedBtnClass;
        if (statusInput) statusInput.value = 'เสร็จสิ้น';
        if (detailsLabel) detailsLabel.textContent = 'รายละเอียดการซ่อมแซม (ถ้ามี)';
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> <span>บันทึก</span>';
            submitBtn.className = 'px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer';
        }
    } else {
        if (btnDone) btnDone.className = unselectedBtnClass;
        if (btnWait) btnWait.className = 'action-choice-btn p-2.5 rounded-xl border-2 border-amber-500 bg-amber-50 text-amber-800 flex items-center justify-center gap-2 text-xs font-bold transition cursor-pointer ring-2 ring-amber-400/20';
        if (statusInput) statusInput.value = 'รอเปลี่ยนอะไหล่';
        if (detailsLabel) detailsLabel.textContent = 'ระบุอะไหล่ที่ต้องใช้ หรือรายละเอียด (ถ้ามี)';
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> <span>บันทึก</span>';
            submitBtn.className = 'px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer';
        }
    }
}

function openUpdateModal(btnOrData) {
    try {
        let data = {};
        if (btnOrData instanceof HTMLElement || (btnOrData && btnOrData.dataset)) {
            const rawJob = (typeof btnOrData.getAttribute === 'function' ? btnOrData.getAttribute('data-job') : null) || (btnOrData.dataset ? btnOrData.dataset.job : null);
            if (rawJob) {
                try {
                    data = JSON.parse(rawJob);
                } catch (e) {
                    console.error('Error parsing data-job JSON:', e);
                    data = btnOrData.dataset || {};
                }
            } else {
                data = btnOrData.dataset || {};
            }
        } else if (typeof btnOrData === 'string') {
            try {
                data = JSON.parse(btnOrData);
            } catch (e) {
                console.error('Error parsing JSON string:', e);
                data = {};
            }
        } else if (typeof btnOrData === 'object' && btnOrData !== null) {
            data = btnOrData;
        }

        currentEditingJob = data;

        const modal = document.getElementById('update-modal');
        const form = document.getElementById('update-repair-form');
        if (!modal) {
            console.error('Modal #update-modal not found');
            return;
        }
        if (!form) {
            console.error('Form #update-repair-form not found');
            return;
        }

        const jobId = data.id || '';
        form.action = "{{ url('/repair-requests') }}/" + jobId;

        const codeEl = document.getElementById('update-modal-code');
        if (codeEl) codeEl.textContent = data.code || ('REQ-' + jobId);

        const deviceEl = document.getElementById('update-modal-device');
        if (deviceEl) deviceEl.textContent = data.device || 'อุปกรณ์ไม่ระบุชื่อ';

        const locEl = document.getElementById('update-modal-location');
        if (locEl) {
            let loc = (data.location || '-').replace(/อาคาร อาคาร/g, 'อาคาร').replace(/undefined/g, 'ไม่ระบุ');
            locEl.textContent = loc;
        }

        const descEl = document.getElementById('update-modal-description');
        if (descEl) descEl.textContent = data.description || '-';

        // Attached image in update modal
        const imgContainer = document.getElementById('update-modal-image-container');
        const imgLink = document.getElementById('update-modal-image-link');
        if (imgContainer && imgLink) {
            if (data.image && data.image.trim() !== '') {
                imgLink.href = data.image;
                imgContainer.classList.remove('hidden');
            } else {
                imgLink.href = '#';
                imgContainer.classList.add('hidden');
            }
        }

        // Set textarea input values
        const inspInput = document.getElementById('inspection_result');
        if (inspInput) {
            inspInput.value = data.inspection_result || '';
        }

        const detailsInput = document.getElementById('repair_details');
        if (detailsInput) {
            detailsInput.value = data.repair_details || '';
        }

        const reasonInput = document.getElementById('unrepairable_reason');
        if (reasonInput) {
            reasonInput.value = data.unrepairable_reason || '';
        }

        clearFieldWarnings(form);

        // Determine evaluation state
        if (data.status === 'ไม่สามารถซ่อมได้' || data.repair_evaluation === 'ไม่สามารถซ่อมได้') {
            setEvaluation('cannot_repair');
        } else {
            setEvaluation('can_repair');
            if (data.status === 'รอเปลี่ยนอะไหล่' || data.status === 'รออะไหล่') {
                setActionStatus('wait_parts');
            } else {
                setActionStatus('completed');
            }
        }

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    } catch (err) {
        console.error('Error in openUpdateModal:', err);
        const fallbackModal = document.getElementById('update-modal');
        if (fallbackModal) {
            fallbackModal.style.display = 'flex';
            fallbackModal.classList.remove('hidden');
            fallbackModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }
}

function closeUpdateModal(event) {
    if (event && event.target && event.target.id !== 'update-modal') return;
    const modal = document.getElementById('update-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        clearFieldWarnings(document.getElementById('update-repair-form'));
    }
}

function showFieldWarning(elementId, message) {
    const el = document.getElementById(elementId);
    if (!el) return;

    el.classList.add('has-field-warning', '!border-red-500', '!bg-red-50/40', '!ring-2', '!ring-red-200');

    const existing = el.parentNode ? el.parentNode.querySelector('.field-warning-box') : null;
    if (existing) existing.remove();

    const box = document.createElement('div');
    box.className = 'field-warning-box mt-1.5 p-2.5 rounded-xl bg-red-50 border border-red-200 text-xs font-bold text-red-600 flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-200';
    box.innerHTML = `<i class="fa-solid fa-circle-exclamation text-red-500 text-sm shrink-0"></i> <span>${message}</span>`;
    if (el.parentNode) el.parentNode.appendChild(box);

    el.focus();

    const onInput = function() {
        el.classList.remove('has-field-warning', '!border-red-500', '!bg-red-50/40', '!ring-2', '!ring-red-200');
        if (box && box.parentNode) box.remove();
        el.removeEventListener('input', onInput);
    };
    el.addEventListener('input', onInput);
}

function clearFieldWarnings(form) {
    if (!form) return;
    try {
        form.querySelectorAll('.field-warning-box').forEach(b => b.remove());
        form.querySelectorAll('.has-field-warning, textarea, input').forEach(el => {
            el.classList.remove('has-field-warning', '!border-red-500', '!bg-red-50/40', '!ring-2', '!ring-red-200');
        });
    } catch (e) {
        console.error('Error in clearFieldWarnings:', e);
    }
}

function handleSimpleUpdateSubmit(event, form) {
    event.preventDefault();

    clearFieldWarnings(form);

    const inspection = (document.getElementById('inspection_result')?.value || '').trim();
    const details = (document.getElementById('repair_details')?.value || '').trim();
    const reason = (document.getElementById('unrepairable_reason')?.value || '').trim();
    const evalVal = document.getElementById('update-eval-input')?.value || 'สามารถซ่อมได้';

    let hasError = false;

    if (!inspection) {
        showFieldWarning('inspection_result', 'กรุณากรอกผลการตรวจสอบ');
        hasError = true;
    }

    if (evalVal === 'ไม่สามารถซ่อมได้' && !reason) {
        showFieldWarning('unrepairable_reason', 'กรุณากรอกสาเหตุที่ไม่สามารถซ่อมได้');
        hasError = true;
    }

    if (hasError) {
        return false;
    }

    // Set note field for unified note tracking
    const noteInput = document.getElementById('update-note-input');
    if (noteInput) {
        if (evalVal === 'ไม่สามารถซ่อมได้') {
            noteInput.value = reason || inspection;
        } else {
            noteInput.value = details || inspection;
        }
    }

    Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        allowOutsideClick: false,
        heightAuto: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    form.submit();
    return true;
}

// Backward compatibility stubs
function selectRepairStatus(statusKey) {
    if (statusKey === 'cannot_repair') {
        setEvaluation('cannot_repair');
    } else {
        setEvaluation('can_repair');
        setActionStatus(statusKey === 'wait_parts' ? 'wait_parts' : 'completed');
    }
}
function toggleRepairForms() {}
function validateUpdateForm(event, form) {
    return handleSimpleUpdateSubmit(event, form);
}

// Modern Finish Repair Modal Logics
let currentFinishJobId = null;

function openFinishRepairModal(btnOrJobId, note, code, device) {
    let jobId = '';
    let currentNote = '';
    let requestCode = '';
    let deviceName = '';

    if (btnOrJobId instanceof HTMLElement || (btnOrJobId && btnOrJobId.dataset)) {
        jobId = btnOrJobId.dataset.jobId || btnOrJobId.getAttribute('data-job-id') || '';
        currentNote = btnOrJobId.dataset.jobNote || btnOrJobId.getAttribute('data-job-note') || '';
        requestCode = btnOrJobId.dataset.jobCode || btnOrJobId.getAttribute('data-job-code') || '';
        deviceName = btnOrJobId.dataset.jobDevice || btnOrJobId.getAttribute('data-job-device') || '';
    } else {
        jobId = btnOrJobId;
        currentNote = note || '';
        requestCode = code || ('REQ-' + jobId);
        deviceName = device || '';
    }

    currentFinishJobId = jobId;

    const modal = document.getElementById('finish-repair-modal');
    if (!modal) return;

    const idInput = document.getElementById('finish-modal-job-id');
    if (idInput) idInput.value = jobId;

    const codeEl = document.getElementById('finish-modal-code');
    if (codeEl) codeEl.textContent = requestCode || ('REQ-' + jobId);

    const deviceEl = document.getElementById('finish-modal-device');
    if (deviceEl) deviceEl.textContent = deviceName || '';

    const input = document.getElementById('finish-repair-note-input');
    if (input) {
        input.value = currentNote || '';
        input.classList.remove('!border-red-500', '!bg-red-50/40', '!ring-2', '!ring-red-200');
    }

    const warning = document.getElementById('finish-repair-warning');
    if (warning) warning.classList.add('hidden');

    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        if (input) input.focus();
    }, 100);
}

function closeFinishRepairModal(event) {
    if (event && event.target && event.target.id !== 'finish-repair-modal') return;
    const modal = document.getElementById('finish-repair-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

function insertFinishQuickText(text) {
    const input = document.getElementById('finish-repair-note-input');
    if (!input) return;
    if (input.value.trim() === '') {
        input.value = text;
    } else {
        input.value = input.value.trim() + ' ' + text;
    }
    input.classList.remove('!border-red-500', '!bg-red-50/40', '!ring-2', '!ring-red-200');
    const warning = document.getElementById('finish-repair-warning');
    if (warning) warning.classList.add('hidden');
    input.focus();
}

function handleFinishRepairSubmit(event) {
    event.preventDefault();
    const jobId = document.getElementById('finish-modal-job-id')?.value || currentFinishJobId;
    const input = document.getElementById('finish-repair-note-input');
    const note = (input?.value || '').trim();
    const warning = document.getElementById('finish-repair-warning');

    if (!note) {
        if (input) {
            input.classList.add('!border-red-500', '!bg-red-50/40', '!ring-2', '!ring-red-200');
            input.focus();
        }
        if (warning) warning.classList.remove('hidden');
        return false;
    }

    const form = document.getElementById('finish-repair-form-' + jobId);
    const hiddenNote = document.getElementById('finish-repair-note-' + jobId);

    if (hiddenNote) hiddenNote.value = note;

    if (!form) {
        Swal.fire({
            title: 'ข้อผิดพลาด',
            text: 'ไม่พบแบบฟอร์มบันทึกปิดงานสำหรับใบงานนี้',
            icon: 'error',
            confirmButtonColor: '#10B981'
        });
        return false;
    }

    Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    closeFinishRepairModal();
    form.submit();
    return true;
}

// Backward compatibility stubs
function selectRepairStatus(statusKey) {
    if (statusKey === 'cannot_repair') {
        setEvaluation('cannot_repair');
    } else {
        setEvaluation('can_repair');
        setActionStatus(statusKey === 'wait_parts' ? 'wait_parts' : 'completed');
    }
}
function toggleRepairForms() {}
function validateUpdateForm(event, form) {
    return handleSimpleUpdateSubmit(event, form);
}

function promptFinishRepair(jobId, currentNote) {
    openFinishRepairModal(jobId, currentNote);
}

document.addEventListener('DOMContentLoaded', function() {
    const inspectionInput = document.getElementById('inspection_result');
    const repairDetailsInput = document.getElementById('repair_details');
    
    if (inspectionInput && repairDetailsInput) {
        let lastMirroredValue = "";

        // Mirror text as user types if repair details is empty or untouched
        inspectionInput.addEventListener('input', function() {
            if (repairDetailsInput.value === '' || repairDetailsInput.value === lastMirroredValue) {
                repairDetailsInput.value = this.value;
                lastMirroredValue = this.value;
            }
        });
        
        // Reset mirrored value tracker when modal opens (can hook into the open button, 
        // but for safety, the condition above handles fresh/empty forms perfectly)
    }
});

// ==================== ประวัติการซ่อมย้อนหลัง (History Dashboard & Filter) ====================
let currentHistoryTimeMode = 'all';
let currentHistoryFilter = 'all';
let currentDonutView = 'category';
let currentKindCategoryView = 'all';
let historyBarChartInstance = null;
let historyDonutChartInstance = null;
let currentTechnicianHistoryView = 'chart'; // 'chart', 'table'

function setTechnicianHistoryView(mode) {
    currentTechnicianHistoryView = mode;
    const chartView = document.getElementById('technician-history-chart-view');
    const tableView = document.getElementById('technician-history-table-view');

    if (chartView && tableView) {
        if (mode === 'table') {
            chartView.classList.add('hidden');
            tableView.classList.remove('hidden');
        } else { // default 'chart'
            chartView.classList.remove('hidden');
            tableView.classList.add('hidden');
        }
    }

    // Update Header buttons
    const modes = ['chart', 'table'];
    modes.forEach(m => {
        const btnHeader = document.getElementById(`btn-view-${m}`);

        if (btnHeader) {
            if (m === mode) {
                btnHeader.className = 'flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-white text-blue-600 shadow-sm cursor-pointer';
            } else {
                btnHeader.className = 'flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-white hover:bg-white/15 cursor-pointer';
            }
        }
    });

    // Resize charts if visible
    if (mode === 'chart') {
        setTimeout(() => {
            if (historyBarChartInstance) historyBarChartInstance.resize();
            if (historyDonutChartInstance) historyDonutChartInstance.resize();
        }, 60);
    }
}

function openTechnicianHistoryWithMode(mode, element) {
    switchTab('tab-history', element);
    setTechnicianHistoryView(mode);
}

function setHistoryTimeMode(mode) {
    currentHistoryTimeMode = mode;
    
    // Update Mode button active styles
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

function setKindCategoryView(cat) {
    currentKindCategoryView = cat;
    
    const catViews = ['all', 'hardware', 'software', 'network'];
    catViews.forEach(c => {
        const btn = document.getElementById(c === 'all' ? 'btn-kind-cat-all' : (c === 'hardware' ? 'btn-kind-cat-hw' : (c === 'software' ? 'btn-kind-cat-sw' : 'btn-kind-cat-net')));
        if (!btn) return;
        if (c === cat) {
            btn.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-blue-600 shadow-xs';
        } else {
            btn.className = 'px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800';
        }
    });

    applyHistoryFilter();
}

function setHistoryFilter(filterKey, element) {
    currentHistoryFilter = filterKey;
    
    // Update active-stat on stat-history-card (top chart cards, if present)
    const histCards = document.querySelectorAll('.stat-history-card');
    histCards.forEach(c => {
        c.classList.remove('active-stat');
        const titleSpan = c.querySelector('span:first-child');
        if (titleSpan) {
            titleSpan.className = 'text-[11px] font-extrabold text-slate-500 uppercase tracking-wider';
        }
    });

    let activeTopCard = null;
    const cardIndexMap = { 'all': 0, 'hardware': 1, 'software': 2, 'network_other': 3 };
    if (cardIndexMap[filterKey] !== undefined && histCards[cardIndexMap[filterKey]]) {
        activeTopCard = histCards[cardIndexMap[filterKey]];
    }
    if (activeTopCard) {
        activeTopCard.classList.add('active-stat');
        const titleSpan = activeTopCard.querySelector('span:first-child');
        if (titleSpan) {
            titleSpan.className = 'text-[11px] font-black text-blue-700 uppercase tracking-wider';
        }
    }
    
    // Update 6 Big Rectangular Cards (stat-history-filter-card)
    const filterCardKeys = ['all', 'hardware', 'software', 'network_other', 'repairable', 'unrepairable_parts'];
    filterCardKeys.forEach(k => {
        const card = document.getElementById(`chip-${k}`);
        if (!card) return;
        const titleSpan = card.querySelector('.card-title');
        if (k === filterKey) {
            card.classList.add('active-stat', 'border-blue-500');
            card.classList.remove('border-slate-200/80', 'border-blue-200');
            if (titleSpan) {
                titleSpan.classList.remove('text-slate-700', 'text-slate-500', 'font-bold');
                titleSpan.classList.add('text-blue-700', 'font-black');
            }
        } else {
            card.classList.remove('active-stat', 'border-blue-500', 'border-blue-200');
            card.classList.add('border-slate-200/80');
            if (titleSpan) {
                titleSpan.classList.remove('text-blue-700', 'font-black');
                titleSpan.classList.add('text-slate-700', 'font-bold');
            }
        }
    });

    // Sync category chart view if matching
    if (['all', 'hardware', 'software'].includes(filterKey)) {
        if (typeof currentKindCategoryView !== 'undefined') {
            currentKindCategoryView = filterKey;
            const catViews = ['all', 'hardware', 'software', 'network'];
            catViews.forEach(c => {
                const btn = document.getElementById(c === 'all' ? 'btn-kind-cat-all' : (c === 'hardware' ? 'btn-kind-cat-hw' : (c === 'software' ? 'btn-kind-cat-sw' : 'btn-kind-cat-net')));
                if (btn) {
                    btn.className = (c === filterKey) 
                        ? 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-blue-600 shadow-xs' 
                        : 'px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800';
                }
            });
        }
    } else if (filterKey === 'network_other') {
        if (typeof currentKindCategoryView !== 'undefined') {
            currentKindCategoryView = 'network';
            const catViews = ['all', 'hardware', 'software', 'network'];
            catViews.forEach(c => {
                const btn = document.getElementById(c === 'all' ? 'btn-kind-cat-all' : (c === 'hardware' ? 'btn-kind-cat-hw' : (c === 'software' ? 'btn-kind-cat-sw' : 'btn-kind-cat-net')));
                if (btn) {
                    btn.className = (c === 'network') 
                        ? 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-blue-600 shadow-xs' 
                        : 'px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800';
                }
            });
        }
    } else if (filterKey === 'repairable' || filterKey === 'unrepairable_parts') {
        if (typeof setDonutView === 'function') {
            setDonutView('outcome');
        }
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
    const chartSub = document.getElementById('donut-chart-sub');

    if (viewKey === 'outcome') {
        if (btnOutcome) btnOutcome.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-blue-600 shadow-xs';
        if (btnCategory) btnCategory.className = 'px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800';
        if (legendOutcome) legendOutcome.classList.remove('hidden');
        if (legendCategory) legendCategory.classList.add('hidden');
        if (chartTitle) chartTitle.textContent = 'สัดส่วนผลการซ่อม';
        if (chartSub) chartSub.textContent = 'เปรียบเทียบงานที่ซ่อมได้ vs ซ่อมไม่ได้';
    } else {
        if (btnOutcome) btnOutcome.className = 'px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800';
        if (btnCategory) btnCategory.className = 'px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-blue-600 shadow-xs';
        if (legendOutcome) legendOutcome.classList.add('hidden');
        if (legendCategory) legendCategory.classList.remove('hidden');
        if (chartTitle) chartTitle.textContent = 'สัดส่วนตามประเภททรัพย์สิน';
        if (chartSub) chartSub.textContent = '';
    }

    applyHistoryFilter();
}

function toggleHistoryAnalytics() {
    const container = document.getElementById('history-analytics-container');
    const icon = document.getElementById('icon-toggle-analytics');
    const text = document.getElementById('text-toggle-analytics');
    if (!container) return;
    
    const isHidden = container.classList.contains('hidden');
    if (isHidden) {
        container.classList.remove('hidden');
        if (icon) icon.classList.add('rotate-180');
        if (text) text.textContent = 'ซ่อนกราฟสถิติ';
        setTimeout(() => {
            if (historyBarChartInstance) historyBarChartInstance.resize();
            if (historyDonutChartInstance) historyDonutChartInstance.resize();
        }, 60);
    } else {
        container.classList.add('hidden');
        if (icon) icon.classList.remove('rotate-180');
        if (text) text.textContent = 'กราฟสถิติ & ช่วงเวลา';
    }
}

function resetHistoryFilter() {
    currentHistoryTimeMode = 'all';
    currentHistoryFilter = 'all';
    
    const searchInput = document.getElementById('history-search-input');
    if (searchInput) searchInput.value = '';
    
    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.classList.add('hidden');

    setHistoryTimeMode('all');
    setHistoryFilter('all');
}

function clearHistorySearch() {
    const searchInput = document.getElementById('history-search-input');
    if (searchInput) searchInput.value = '';
    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.classList.add('hidden');
    applyHistoryFilter();
}

function applyHistoryFilter() {
    const allData = window.historyJobsData || [];
    const dateEl = document.getElementById('history-filter-date');
    const selectedDate = dateEl?.getAttribute('data-date-ymd') || dateEl?.value;

    const selectedMonth = parseInt(document.getElementById('history-filter-month')?.value) || (new Date().getMonth() + 1);
    const monthYear     = parseInt(document.getElementById('history-filter-month-year')?.value) || new Date().getFullYear();
    const yearModeYear  = parseInt(document.getElementById('history-filter-year')?.value) || new Date().getFullYear();

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
    const unrepairableCount = timeFiltered.filter(i => i.reparability === 'unrepairable_parts').length;
    
    // Asset Categories (ประเภททรัพย์สิน)
    const hwCount = timeFiltered.filter(i => i.category === 'hardware').length;
    const swCount = timeFiltered.filter(i => i.category === 'software').length;
    const netCount = timeFiltered.filter(i => i.category === 'network_other').length;

    // Asset Kinds (ชนิดทรัพย์สิน) - 1. หมวดฮาร์ดแวร์ (ตามฐานข้อมูลจริง)
    const notebookCount = timeFiltered.filter(i => i.device_kind_key === 'notebook').length;
    const keyboardCount = timeFiltered.filter(i => i.device_kind_key === 'keyboard').length;
    const mouseCount = timeFiltered.filter(i => i.device_kind_key === 'mouse').length;

    // Asset Kinds (ชนิดทรัพย์สิน) - 2. หมวดซอฟต์แวร์ (ตามฐานข้อมูลจริง)
    const osCount = timeFiltered.filter(i => i.device_kind_key === 'os').length;
    const officeSwCount = timeFiltered.filter(i => i.device_kind_key === 'office_sw').length;

    // Asset Kinds (ชนิดทรัพย์สิน) - 3. หมวดระบบเครือข่าย (ตามฐานข้อมูลจริง)
    const lanCount = timeFiltered.filter(i => i.device_kind_key === 'lan').length;
    const accessPointCount = timeFiltered.filter(i => i.device_kind_key === 'access_point').length;
    const wifiCount = timeFiltered.filter(i => i.device_kind_key === 'wifi').length;

    // Percentages
    const repPct = totalCount > 0 ? ((repairableCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const unrepPct = totalCount > 0 ? ((unrepairableCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const hwPct = totalCount > 0 ? ((hwCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const swPct = totalCount > 0 ? ((swCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const netPct = totalCount > 0 ? ((netCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const notebookPct = totalCount > 0 ? ((notebookCount / totalCount) * 100).toFixed(1) + '%' : '0%';
    const osPct = totalCount > 0 ? ((osCount / totalCount) * 100).toFixed(1) + '%' : '0%';

    // 3. Update the 4 Core Summary Cards DOM
    const elTotal = document.getElementById('stat-total-count');
    const elHw = document.getElementById('stat-hw-count');
    const elHwPct = document.getElementById('stat-hw-pct');
    const elSw = document.getElementById('stat-sw-count');
    const elSwPct = document.getElementById('stat-sw-pct');
    const elNet = document.getElementById('stat-net-count');
    const elNetPct = document.getElementById('stat-net-pct');

    if (elTotal) elTotal.textContent = totalCount;
    if (elHw) elHw.textContent = hwCount;
    if (elHwPct) elHwPct.textContent = hwPct;
    if (elSw) elSw.textContent = swCount;
    if (elSwPct) elSwPct.textContent = swPct;
    if (elNet) elNet.textContent = netCount;
    if (elNetPct) elNetPct.textContent = netPct;

    // Donut numbers
    const dHw = document.getElementById('donut-hw-count');
    const dHwPct = document.getElementById('donut-hw-pct');
    const dSw = document.getElementById('donut-sw-count');
    const dSwPct = document.getElementById('donut-sw-pct');
    const dNet = document.getElementById('donut-net-count');
    const dNetPct = document.getElementById('donut-net-pct');
    if (dHw) dHw.textContent = hwCount + ' รายการ';
    if (dHwPct) dHwPct.textContent = hwPct;
    if (dSw) dSw.textContent = swCount + ' รายการ';
    if (dSwPct) dSwPct.textContent = swPct;
    if (dNet) dNet.textContent = netCount + ' รายการ';
    if (dNetPct) dNetPct.textContent = netPct;

    const dRep = document.getElementById('donut-repairable-count');
    const dRepPct = document.getElementById('donut-repairable-pct');
    const dUnrep = document.getElementById('donut-unrepairable-count');
    const dUnrepPct = document.getElementById('donut-unrepairable-pct');
    if (dRep) dRep.textContent = repairableCount + ' รายการ';
    if (dRepPct) dRepPct.textContent = repPct;
    if (dUnrep) dUnrep.textContent = unrepairableCount + ' รายการ';
    if (dUnrepPct) dUnrepPct.textContent = unrepPct;

    // Filter chip badges
    const bAll = document.getElementById('badge-count-all');
    const bHw = document.getElementById('badge-count-hw');
    const bSw = document.getElementById('badge-count-sw');
    const bNet = document.getElementById('badge-count-net');
    const bNotebook = document.getElementById('badge-count-notebook');
    const bOS = document.getElementById('badge-count-os');
    const bRep = document.getElementById('badge-count-repairable');
    const bUnrep = document.getElementById('badge-count-unrepairable');

    if (bAll) bAll.textContent = totalCount;
    if (bHw) bHw.textContent = hwCount;
    if (bSw) bSw.textContent = swCount;
    if (bNet) bNet.textContent = netCount;
    if (bNotebook) bNotebook.textContent = notebookCount;
    if (bOS) bOS.textContent = osCount;
    if (bRep) bRep.textContent = repairableCount;
    if (bUnrep) bUnrep.textContent = unrepairableCount;

    // Period Label & Card 1 Subtitle
    const periodLabel = document.getElementById('stat-period-label');
    const cardTotalSub = document.getElementById('stat-card-total-sub');

    if (currentHistoryTimeMode === 'all') {
        if (periodLabel) periodLabel.textContent = 'ข้อมูลงานซ่อมที่ได้รับมอบหมายจริง';
        if (cardTotalSub) cardTotalSub.textContent = 'ภาพรวมงานที่ได้รับมอบหมาย';
    } else if (currentHistoryTimeMode === 'day') {
        let dateDisplay = selectedDate || 'วันนี้';
        if (selectedDate) {
            const parts = selectedDate.split('-');
            if (parts.length === 3) {
                const thYear = parseInt(parts[0]) + 543;
                dateDisplay = `${parts[2]}/${parts[1]}/${thYear}`;
            }
        }
        if (periodLabel) periodLabel.textContent = `งานที่ได้รับมอบหมาย ประจำวันที่ ${dateDisplay}`;
        if (cardTotalSub) cardTotalSub.textContent = `รายวัน (${dateDisplay})`;
    } else if (currentHistoryTimeMode === 'month') {
        const monthNames = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const mName = monthNames[selectedMonth] || '';
        const thYear = selectedYear + 543;
        if (periodLabel) periodLabel.textContent = `งานที่ได้รับมอบหมาย ประจำเดือน ${mName} พ.ศ. ${thYear}`;
        if (cardTotalSub) cardTotalSub.textContent = `รายเดือน (${mName})`;
    } else if (currentHistoryTimeMode === 'year') {
        const thYear = selectedYear + 543;
        if (periodLabel) periodLabel.textContent = `งานที่ได้รับมอบหมาย ประจำปี พ.ศ. ${thYear}`;
        if (cardTotalSub) cardTotalSub.textContent = `รายปี (พ.ศ. ${thYear})`;
    }

    // Dynamic Satisfaction Assessment & Requester Feedback updates
    const ratedItems = timeFiltered.filter(i => (Number(i.rating) || 0) > 0);
    const satRatedCount = ratedItems.length;
    const satAvg = satRatedCount > 0 ? (ratedItems.reduce((acc, curr) => acc + Number(curr.rating), 0) / satRatedCount) : 0;
    const satAvgScoreEl = document.getElementById('sat-avg-score');
    const satAvgPctEl = document.getElementById('sat-avg-pct');
    const satStarsEl = document.getElementById('sat-avg-stars');
    const satTierBadgeEl = document.getElementById('sat-tier-badge');
    const satRatedCountBadgeEl = document.getElementById('sat-rated-count-badge');
    const satRatedTextEl = document.getElementById('sat-rated-text');

    if (satAvgScoreEl) satAvgScoreEl.textContent = satAvg.toFixed(1);
    if (satAvgPctEl) satAvgPctEl.textContent = ((satAvg / 5) * 100).toFixed(1) + '%';
    if (satRatedCountBadgeEl) satRatedCountBadgeEl.textContent = satRatedCount + ' รายการประเมิน';
    if (satRatedTextEl) {
        const pct = totalCount > 0 ? Math.round((satRatedCount / totalCount) * 100) : 0;
        satRatedTextEl.textContent = `ผู้ใช้งานประเมินแล้ว ${satRatedCount} จาก ${totalCount} งาน (${pct}%)`;
    }

    if (satStarsEl) {
        let starsHtml = '';
        for (let s = 1; s <= 5; s++) {
            if (s <= Math.floor(satAvg)) {
                starsHtml += '<i class="fa-solid fa-star"></i>';
            } else if (s - 0.5 <= satAvg) {
                starsHtml += '<i class="fa-solid fa-star-half-stroke"></i>';
            } else {
                starsHtml += '<i class="fa-regular fa-star text-slate-200"></i>';
            }
        }
        satStarsEl.innerHTML = starsHtml;
    }

    if (satTierBadgeEl) {
        let tierLabel = 'ยังไม่มีการประเมิน';
        let tierColor = 'bg-slate-100 text-slate-600 border-slate-200';
        if (satAvg >= 4.5) {
            tierLabel = 'ดีมากที่สุด (ยอดเยี่ยม)';
            tierColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        } else if (satAvg >= 3.5) {
            tierLabel = 'ดีมาก';
            tierColor = 'bg-teal-50 text-teal-700 border-teal-200';
        } else if (satAvg >= 2.5) {
            tierLabel = 'ปานกลาง';
            tierColor = 'bg-amber-50 text-amber-700 border-amber-200';
        } else if (satAvg >= 1.5) {
            tierLabel = 'พอใช้';
            tierColor = 'bg-orange-50 text-orange-700 border-orange-200';
        } else if (satAvg > 0) {
            tierLabel = 'ต้องปรับปรุง';
            tierColor = 'bg-rose-50 text-rose-700 border-rose-200';
        }
        satTierBadgeEl.className = `text-xs font-bold px-3 py-1.5 rounded-xl border ${tierColor} flex items-center gap-1.5 shadow-xs`;
        satTierBadgeEl.innerHTML = `<i class="fa-solid fa-award"></i> <span>${tierLabel}</span>`;
    }

    // Star Breakdown
    const s5 = ratedItems.filter(i => Number(i.rating) === 5).length;
    const s4 = ratedItems.filter(i => Number(i.rating) === 4).length;
    const s3 = ratedItems.filter(i => Number(i.rating) === 3).length;
    const s2 = ratedItems.filter(i => Number(i.rating) === 2).length;
    const s1 = ratedItems.filter(i => Number(i.rating) === 1).length;

    const setStarBar = (num, count) => {
        const barEl = document.getElementById(`sat-bar-${num}`);
        const countEl = document.getElementById(`sat-count-${num}`);
        const pct = satRatedCount > 0 ? Math.round((count / satRatedCount) * 100) : 0;
        if (barEl) barEl.style.width = pct + '%';
        if (countEl) countEl.textContent = count + ' งาน';
    };
    setStarBar(5, s5);
    setStarBar(4, s4);
    setStarBar(3, s3);
    setStarBar(2, s2);
    setStarBar(1, s1);

window.currentFeedbackTab = 'all';
function filterFeedbackTab(mode) {
    window.currentFeedbackTab = mode;
    const btnAll = document.getElementById('fb-btn-all');
    const btnHasText = document.getElementById('fb-btn-has-text');
    
    if (btnAll && btnHasText) {
        if (mode === 'all') {
            btnAll.className = 'px-2.5 py-1 rounded-md font-bold transition-all bg-white text-blue-600 shadow-xs cursor-pointer';
            btnHasText.className = 'px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
        } else {
            btnHasText.className = 'px-2.5 py-1 rounded-md font-bold transition-all bg-white text-blue-600 shadow-xs cursor-pointer';
            btnAll.className = 'px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
        }
    }

    const items = document.querySelectorAll('#sat-feedback-list .feedback-item');
    let visibleCount = 0;
    items.forEach(el => {
        const hasComm = el.getAttribute('data-has-comment') === '1';
        if (mode === 'all' || hasComm) {
            el.style.display = '';
            visibleCount++;
        } else {
            el.style.display = 'none';
        }
    });

    let emptyNotice = document.getElementById('fb-tab-empty-notice');
    if (visibleCount === 0) {
        if (!emptyNotice) {
            emptyNotice = document.createElement('div');
            emptyNotice.id = 'fb-tab-empty-notice';
            emptyNotice.className = 'p-6 text-center text-slate-400 bg-white rounded-xl border border-dashed border-slate-200';
            emptyNotice.innerHTML = `
                <i class="fa-regular fa-comment-dots text-3xl text-slate-300 mb-2"></i>
                <p class="text-xs font-bold text-slate-500">ไม่มีรายการที่มีข้อความในช่วงเวลานี้</p>
            `;
            const list = document.getElementById('sat-feedback-list');
            if (list) list.appendChild(emptyNotice);
        } else {
            emptyNotice.style.display = '';
        }
    } else if (emptyNotice) {
        emptyNotice.style.display = 'none';
    }
}

    // Feedback Feed List
    const feedbackListEl = document.getElementById('sat-feedback-list');
    const feedbackCountEl = document.getElementById('sat-feedback-count');
    const hasTextCountEl = document.getElementById('sat-has-text-count');
    
    // Only jobs with rating > 0 are evaluations!
    const feedbackItems = timeFiltered.filter(i => (Number(i.rating) || 0) > 0);
    const hasTextCount = feedbackItems.filter(i => (i.eval_comment || '').trim() !== '').length;

    if (feedbackCountEl) feedbackCountEl.textContent = feedbackItems.length;
    if (hasTextCountEl) hasTextCountEl.textContent = hasTextCount;

    if (feedbackListEl && currentHistoryTimeMode !== 'all') {
        if (feedbackItems.length === 0) {
            feedbackListEl.innerHTML = `
                <div class="p-8 text-center text-slate-400 bg-white rounded-xl border border-dashed border-slate-200">
                    <i class="fa-regular fa-comment-dots text-3xl text-slate-300 mb-2"></i>
                    <p class="text-xs font-bold text-slate-500">ไม่มีการประเมินความพึงพอใจในช่วงเวลาที่เลือก</p>
                </div>
            `;
        } else {
            const tiers = {1: 'ปรับปรุง', 2: 'พอใช้', 3: 'ปานกลาง', 4: 'ดี', 5: 'ดีมาก'};
            const tierColors = {
                1: 'text-red-700 bg-red-50 border-red-200',
                2: 'text-orange-700 bg-orange-50 border-orange-200',
                3: 'text-amber-700 bg-amber-50 border-amber-200',
                4: 'text-emerald-700 bg-emerald-50 border-emerald-200',
                5: 'text-emerald-800 bg-emerald-100 border-emerald-300'
            };
            feedbackListEl.innerHTML = feedbackItems.map(fb => {
                const rScore = Number(fb.rating) || 0;
                const rTier = tiers[rScore] || '-';
                const rBg = tierColors[rScore] || 'text-slate-700 bg-slate-50 border-slate-200';
                const cText = (fb.eval_comment || '').trim();
                const hasComment = cText !== '';
                let starIcons = '';
                for (let s = 1; s <= 5; s++) {
                    starIcons += `<i class="fa-solid fa-star ${s <= rScore ? 'text-amber-400' : 'text-slate-200'}"></i>`;
                }

                const commentHtml = hasComment ? `
                    <div class="bg-gradient-to-r from-blue-50/60 to-indigo-50/30 rounded-xl p-3 text-xs text-slate-800 leading-relaxed font-normal border border-blue-100/80 flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-blue-100/80 text-blue-600 flex items-center justify-center shrink-0 mt-0.5 text-[10px]">
                            <i class="fa-solid fa-quote-left"></i>
                        </div>
                        <p class="text-xs text-slate-700 font-medium leading-relaxed">“${cText}”</p>
                    </div>
                ` : `
                    <div class="bg-slate-50/80 rounded-xl px-3 py-2 text-xs text-slate-400 italic flex items-center gap-2 border border-slate-100">
                        <i class="fa-regular fa-comment text-slate-300 text-xs"></i>
                        <span>ผู้รับบริการประเมินความพึงพอใจระดับ ${rScore}.0 ดาว (ไม่ได้ระบุข้อความเพิ่มเติม)</span>
                    </div>
                `;

                const commentTag = hasComment ? `
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 flex items-center gap-1">
                        <i class="fa-solid fa-quote-left text-[8px]"></i> มีข้อเสนอแนะ
                    </span>
                ` : '';

                const isHidden = (window.currentFeedbackTab === 'has-text' && !hasComment) ? 'display: none;' : '';

                return `
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-2xs hover:border-blue-300 transition-all space-y-2.5 feedback-item" data-has-comment="${hasComment ? '1' : '0'}" style="${isHidden}">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border ${rBg}">
                                    <div class="flex text-amber-500 text-[10px]">${starIcons}</div>
                                    <span class="text-xs font-black">${rScore}.0</span>
                                    <span class="text-[10px] font-bold text-slate-600">(${rTier})</span>
                                </div>
                                ${commentTag}
                            </div>
                            <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1 shrink-0">
                                <i class="fa-regular fa-clock text-[10px]"></i>
                                <span>${fb.formatted_date || '-'}</span>
                            </div>
                        </div>

                        ${commentHtml}

                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 text-[10px] shrink-0">${fb.code}</span>
                                <span class="truncate max-w-[180px] text-slate-500 font-medium text-[11px]">
                                    <i class="fa-solid fa-desktop text-slate-400 text-[10px] mr-1"></i>${fb.device_name || 'อุปกรณ์'}
                                </span>
                            </div>
                            <button type="button" class="text-[10px] font-bold text-blue-500 hover:text-blue-700 hover:underline flex items-center gap-1 cursor-pointer shrink-0"
                                    onclick="openHistoryModal({
                                        code: '${fb.code}',
                                        title: '${(fb.device_name || 'อุปกรณ์').replace(/'/g, "\\'")}',
                                        device: '${(fb.device_name || '-').replace(/'/g, "\\'")}',
                                        kind: '${(fb.kind || 'ไม่ระบุ').replace(/'/g, "\\'")}',
                                        location: '${(fb.location || '-').replace(/'/g, "\\'")}',
                                        type: '${fb.type || ''}',
                                        priority: '${fb.priority || ''}',
                                        description: '${(fb.description || '').replace(/'/g, "\\'")}',
                                        reporter: '${(fb.user_name || 'ไม่ระบุ').replace(/'/g, "\\'")}',
                                        assignee: 'นาย วิรัตน์ ยินดีดี',
                                        status: '${fb.status || ''}',
                                        statusClass: '',
                                        created_at: '${fb.created_at || '-'}',
                                        finished_at: '${fb.formatted_date || '-'}',
                                        note: '${(fb.note || '-').replace(/'/g, "\\'")}',
                                        rating: ${rScore},
                                        rating_text: '${rTier}',
                                        eval_comment: '${cText.replace(/'/g, "\\'")}'
                                    })">
                                ดูรายละเอียด <i class="fa-solid fa-chevron-right text-[8px]"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }
    }

    // 4. Render Donut Chart (Mode: Category vs Outcome)
    const donutCanvas = document.getElementById('historyDonutChart');
    if (donutCanvas && typeof Chart !== 'undefined') {
        let donutLabels, donutData, donutColors, centerPct, centerSub;

        if (currentDonutView === 'outcome') {
            donutLabels = ['สามารถซ่อมได้', 'ซ่อมไม่ได้'];
            donutData = [repairableCount, unrepairableCount];
            donutColors = ['#10B981', '#F43F5E'];
            centerPct = repPct;
            centerSub = 'ซ่อมสำเร็จ';
        } else {
            donutLabels = ['ฮาร์ดแวร์', 'ซอฟต์แวร์', 'เครือข่าย'];
            donutData = [hwCount, swCount, netCount];
            donutColors = ['#6366F1', '#A855F7', '#0D9488'];
            if (hwCount >= swCount && hwCount > 0) {
                centerPct = hwPct;
                centerSub = 'ฮาร์ดแวร์';
            } else if (swCount > 0) {
                centerPct = swPct;
                centerSub = 'ซอฟต์แวร์';
            } else {
                centerPct = '0%';
                centerSub = 'ประเภททรัพย์สิน';
            }
        }

        const totalDonutVal = donutData.reduce((a, b) => a + b, 0);

        const donutConfig = {
            type: 'doughnut',
            data: {
                labels: totalDonutVal === 0 ? ['ไม่มีข้อมูล'] : donutLabels,
                datasets: [{
                    data: totalDonutVal === 0 ? [1] : donutData,
                    backgroundColor: totalDonutVal === 0 ? ['#E2E8F0'] : donutColors,
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: totalDonutVal > 0,
                        callbacks: {
                            label: function(context) {
                                const val = context.raw || 0;
                                const pct = totalDonutVal > 0 ? ((val / totalDonutVal) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${val} รายการ (${pct}%)`;
                            }
                        },
                        titleFont: { family: 'Prompt', size: 12 },
                        bodyFont: { family: 'Prompt', size: 12 }
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

        // Center Percentage
        const elCenterPct = document.getElementById('donut-center-pct');
        const elCenterSub = document.getElementById('donut-center-sub');
        if (elCenterPct) elCenterPct.textContent = centerPct;
        if (elCenterSub) elCenterSub.textContent = centerSub;
    }

    // 5. Render Asset Kind Column Bar Chart (Grouped by Category View)
    const barCanvas = document.getElementById('historyBarChart');
    if (barCanvas && typeof Chart !== 'undefined') {
        let barLabels = [];
        let barDataValues = [];
        let barColors = [];
        let barFilterKeys = [];

        if (currentKindCategoryView === 'hardware') {
            barLabels = ['โน้ตบุ๊ค', 'แป้นพิมพ์', 'เมาส์'];
            barDataValues = [notebookCount, keyboardCount, mouseCount];
            barColors = ['#4F46E5', '#6366F1', '#818CF8'];
            barFilterKeys = ['notebook', 'keyboard', 'mouse'];
        } else if (currentKindCategoryView === 'software') {
            barLabels = ['OS ระบบปฎิบัติการ', 'OS ซอฟต์แวร์สำนักงาน'];
            barDataValues = [osCount, officeSwCount];
            barColors = ['#9333EA', '#A855F7'];
            barFilterKeys = ['os', 'office_sw'];
        } else if (currentKindCategoryView === 'network') {
            barLabels = ['สายแลน', 'อุปกรณ์กระจายสัญญาณ', 'wifi'];
            barDataValues = [lanCount, accessPointCount, wifiCount];
            barColors = ['#0D9488', '#14B8A6', '#2DD4BF'];
            barFilterKeys = ['lan', 'access_point', 'wifi'];
        } else {
            // 'all' - แสดงชนิดทรัพย์สินทั้งหมดที่มีจริงในระบบ แยกตามหมวด
            barLabels = [
                ['โน้ตบุ๊ค', '(HW)'],
                ['OS ปฎิบัติการ', '(SW)'],
                ['แป้นพิมพ์', '(HW)'],
                ['เมาส์', '(HW)'],
                ['สายแลน', '(Net)'],
                ['กระจายสัญญาณ', '(Net)'],
                ['wifi', '(Net)'],
                ['OS สำนักงาน', '(SW)']
            ];
            barDataValues = [
                notebookCount,
                osCount,
                keyboardCount,
                mouseCount,
                lanCount,
                accessPointCount,
                wifiCount,
                officeSwCount
            ];
            barColors = [
                '#4F46E5',
                '#9333EA',
                '#6366F1',
                '#818CF8',
                '#0D9488',
                '#14B8A6',
                '#2DD4BF',
                '#A855F7'
            ];
            barFilterKeys = [
                'notebook',
                'os',
                'keyboard',
                'mouse',
                'lan',
                'access_point',
                'wifi',
                'office_sw'
            ];
        }

        const barData = {
            labels: barLabels,
            datasets: [{
                label: 'จำนวนงาน',
                data: barDataValues,
                backgroundColor: barColors,
                borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 },
                barThickness: currentKindCategoryView === 'all' ? 18 : 34,
                maxBarThickness: 38
            }]
        };

        const barConfig = {
            type: 'bar',
            data: barData,
            options: {
                indexAxis: 'x',
                responsive: true,
                maintainAspectRatio: false,
                onClick: (event, elements) => {
                    if (elements && elements.length > 0) {
                        const index = elements[0].index;
                        if (barFilterKeys[index]) {
                            setHistoryFilter(barFilterKeys[index]);
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        titleFont: { family: 'Prompt', size: 12 },
                        bodyFont: { family: 'Prompt', size: 12 },
                        callbacks: {
                            label: function(context) {
                                const val = context.raw || 0;
                                const totalInView = barDataValues.reduce((a, b) => a + b, 0);
                                const pct = totalInView > 0 ? ((val / totalInView) * 100).toFixed(1) : 0;
                                return ` จำนวน: ${val} รายการ (${pct}%) - คลิกเพื่อกรอง`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Prompt', size: 11, weight: '600' },
                            color: '#334155',
                            maxRotation: 0,
                            autoSkip: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                            font: { family: 'Prompt', size: 10 },
                            color: '#64748B'
                        },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    }
                }
            }
        };

        if (historyBarChartInstance) {
            historyBarChartInstance.data = barData;
            historyBarChartInstance.options = barConfig.options;
            historyBarChartInstance.update();
        } else {
            historyBarChartInstance = new Chart(barCanvas.getContext('2d'), barConfig);
        }
    }

    // 6. Filter history rows in table
    let visibleCount = 0;
    const rows = document.querySelectorAll('#history-data-table tbody tr.history-row');
    rows.forEach(row => {
        const cCategory = row.getAttribute('data-category');
        const cReparability = row.getAttribute('data-reparability');
        const cKind = row.getAttribute('data-kind');
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

        // Match category / reparability / device kind chip
        let matchFilter = true;
        if (currentHistoryFilter === 'repairable') matchFilter = (cReparability === 'repairable');
        else if (currentHistoryFilter === 'unrepairable_parts') matchFilter = (cReparability === 'unrepairable_parts');
        else if (currentHistoryFilter === 'hardware') matchFilter = (cCategory === 'hardware');
        else if (currentHistoryFilter === 'software') matchFilter = (cCategory === 'software');
        else if (currentHistoryFilter === 'network_other') matchFilter = (cCategory === 'network_other');
        else if (currentHistoryFilter === 'notebook') matchFilter = (cKind === 'notebook');
        else if (currentHistoryFilter === 'keyboard') matchFilter = (cKind === 'keyboard');
        else if (currentHistoryFilter === 'mouse') matchFilter = (cKind === 'mouse');
        else if (currentHistoryFilter === 'os') matchFilter = (cKind === 'os');
        else if (currentHistoryFilter === 'office_sw') matchFilter = (cKind === 'office_sw');
        else if (currentHistoryFilter === 'lan') matchFilter = (cKind === 'lan');
        else if (currentHistoryFilter === 'access_point') matchFilter = (cKind === 'access_point');
        else if (currentHistoryFilter === 'wifi') matchFilter = (cKind === 'wifi');

        // Match search keyword
        let matchSearch = true;
        if (searchKeyword !== '') {
            matchSearch = cKeyword.includes(searchKeyword);
        }

        if (matchTime && matchFilter && matchSearch) {
            row.style.display = '';
            visibleCount++;
            if (typeof applyHighlight === 'function') {
                applyHighlight(row, searchKeyword);
            }
        } else {
            row.style.display = 'none';
        }
    });

    // Empty state row
    const emptyRow = document.getElementById('history-empty-row');
    if (emptyRow) {
        emptyRow.classList.toggle('hidden', visibleCount > 0);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Thai Flatpickr for History Date Picker (วัน/เดือน/ปี พ.ศ. ตามหลักประเทศไทย)
    if (typeof flatpickr !== 'undefined') {
        const historyDateInput = document.getElementById('history-filter-date');
        if (historyDateInput) {
            flatpickr(historyDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                altInputClass: 'w-36 text-xs bg-slate-50 border border-slate-200 rounded-xl pl-3 pr-8 py-1.5 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs',
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

    if (typeof applyHistoryFilter === 'function') {
        applyHistoryFilter();
    }
    if (typeof setTechnicianHistoryView === 'function') {
        setTechnicianHistoryView('chart');
    }
});
</script>

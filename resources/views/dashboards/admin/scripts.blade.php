<script>
    window.onerror = function (msg, url, lineNo, columnNo, error) {
        alert("JS Error: " + msg + "\nLine: " + lineNo + "\nCol: " + columnNo);
        return false;
    };
    // Base URL สำหรับ API calls - ใช้ Laravel url() เพื่อให้ทำงานได้ทั้ง local และ server
    window.BASE_URL = "{{ url('') }}";
</script>
<script>
    window.toggleAdminPasswordVisibility = function (inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    };

    window.currentEditAssetId = null;

    window.closeAssetItemModal = function () {
        const modal = document.getElementById('modal-add-asset-item');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        window.currentEditAssetId = null;

        // Reset title
        const titleEls = document.querySelectorAll('#modal-add-asset-item h4');
        if (titleEls.length > 0) titleEls[0].innerHTML = 'เพิ่มข้อมูลทรัพย์สิน';

        // Reset button texts
        document.querySelectorAll('#modal-add-asset-item button[type="submit"]').forEach(b => {
            b.innerHTML = 'บันทึก';
        });
    };

    const gBuildingsData = @json($dbBuildings ?? []);

    window.handleAiFormBuildingChange = function () {
        ['hw-form-', 'sw-form-', 'nw-form-'].forEach(prefix => {
            const bSelect = document.getElementById(prefix + 'building');
            const fSelect = document.getElementById(prefix + 'floor');
            const rSelect = document.getElementById(prefix + 'room');

            if (fSelect && bSelect) {
                const currentFloor = fSelect.value;

                fSelect.innerHTML = '<option value="" disabled selected>-- เลือกชั้น --</option>';
                const buildingCode = bSelect.value;
                if (buildingCode) {
                    const b = gBuildingsData.find(x => x.building_id == buildingCode);
                    if (b && b.floors) {
                        for (let i = 1; i <= b.floors; i++) {
                            fSelect.innerHTML += `<option value="${i}">ชั้น ${i}</option>`;
                        }
                        fSelect.disabled = false;
                    } else {
                        fSelect.disabled = true;
                    }
                } else {
                    fSelect.disabled = true;
                }

                if (currentFloor) fSelect.value = currentFloor;

                if (!fSelect.value && rSelect) {
                    rSelect.innerHTML = '<option value="" disabled selected>-- เลือกห้อง --</option>';
                    rSelect.disabled = true;
                }
            }
        });
    };

    window.handleAiFormFloorChange = function () {
        ['hw-form-', 'sw-form-', 'nw-form-'].forEach(prefix => {
            const bSelect = document.getElementById(prefix + 'building');
            const fSelect = document.getElementById(prefix + 'floor');
            const rSelect = document.getElementById(prefix + 'room');

            if (rSelect && bSelect && fSelect) {
                const currentRoom = rSelect.value;

                rSelect.innerHTML = '<option value="" disabled selected>-- เลือกห้อง --</option>';
                const buildingCode = bSelect.value;
                const floorNo = fSelect.value;
                if (buildingCode && floorNo) {
                    const b = gBuildingsData.find(x => x.building_id == buildingCode);
                    if (b && b.rooms) {
                        const rooms = b.rooms.filter(r => r.floor == floorNo);
                        rooms.forEach(r => {
                            rSelect.innerHTML += `<option value="${r.room_code}">${r.room_name}</option>`;
                        });
                        rSelect.disabled = false;
                    } else {
                        rSelect.disabled = true;
                    }
                } else {
                    rSelect.disabled = true;
                }

                if (currentRoom) rSelect.value = currentRoom;
            }
        });
    };

    window.validateAdminPasswordComplexity = function () {
        // Mock to prevent errors
    };

    window.switchAdminTab = function (tabId, btn) {
        // Hide all tabs
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => {
            tab.style.display = 'none';
            tab.classList.add('hidden');
            tab.classList.remove('block');
        });

        // Show target tab
        const target = document.getElementById(tabId);
        if (target) {
            target.style.display = 'block';
            target.classList.remove('hidden');
            target.classList.add('block');
        }

        // Update button styles for desktop and mobile
        const navLinks = document.querySelectorAll('.tab-link');
        navLinks.forEach(link => {
            link.classList.remove('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold', 'text-white', 'bg-white/15');
            link.classList.add('border-transparent', 'text-blue-100/70', 'hover:bg-white/10', 'hover:text-white', 'font-semibold');
        });

        if (btn) {
            if (tabId !== 'tab-basic-data') {
                document.querySelectorAll('.sub-menu-item, .mobile-sub-menu-item').forEach(item => {
                    item.classList.remove('bg-white', 'text-blue-900', 'font-bold', 'shadow-sm');
                    item.classList.add('text-blue-100/80', 'font-semibold');
                });
            }
            btn.classList.remove('border-transparent', 'text-blue-100/70', 'hover:bg-white/10', 'hover:text-white', 'font-semibold');
            btn.classList.add('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold');

            // Also update the corresponding mobile/desktop button if clicked from one
            const id = btn.id;
            let otherId = '';
            if (id.includes('-desktop')) {
                otherId = id.replace('-desktop', '-mobile');
            } else if (id.includes('-mobile')) {
                otherId = id.replace('-mobile', '-desktop');
            }

            if (otherId) {
                const otherBtn = document.getElementById(otherId);
                if (otherBtn) {
                    otherBtn.classList.remove('border-transparent', 'text-blue-100/70', 'hover:bg-white/10', 'hover:text-white', 'font-semibold');
                    otherBtn.classList.add('border-white', 'text-blue-900', 'bg-white', 'shadow-md', 'font-bold');
                }
            }
        }

        // trigger specific functions if tab requires it
        if (tabId === 'tab-assets' && typeof renderDevicesList === 'function') {
            renderDevicesList();
        }
    };

    window.updateAssetStatusDropdowns = function (isEdit, currentStatus = 'พร้อมใช้งาน') {
        const prefixes = ['hw', 'sw', 'nw'];
        prefixes.forEach(prefix => {
            const select = document.getElementById(prefix + '-form-status');
            if (select) {
                select.innerHTML = '';
                if (!isEdit) {
                    // For Add Modal: Only 1 status option "พร้อมใช้งาน"
                    const opt = document.createElement('option');
                    opt.value = 'พร้อมใช้งาน';
                    opt.textContent = 'พร้อมใช้งาน';
                    opt.selected = true;
                    select.appendChild(opt);
                } else {
                    // For Edit Modal: 3 status options "พร้อมใช้งาน", "กำลังซ่อม", "รออะไหล่"
                    const statuses = [
                        { val: 'พร้อมใช้งาน', text: 'พร้อมใช้งาน' },
                        { val: 'กำลังซ่อม', text: 'กำลังซ่อม' },
                        { val: 'รออะไหล่', text: 'รออะไหล่' }
                    ];

                    let normStatus = currentStatus;
                    if (!normStatus || normStatus === 'active' || normStatus === 'ปกติ / พร้อมใช้งาน' || normStatus === 'ใช้งานได้ปกติ' || normStatus === 'ใช้งานได้') {
                        normStatus = 'พร้อมใช้งาน';
                    }

                    statuses.forEach(st => {
                        const opt = document.createElement('option');
                        opt.value = st.val;
                        opt.textContent = st.text;
                        if (normStatus === st.val) opt.selected = true;
                        select.appendChild(opt);
                    });
                }
            }
        });
    };

    window.openAssetItemModal = function (isEdit = false) {
        if (!isEdit) {
            window.currentEditAssetId = null;
            // Reset title
            const titleEls = document.querySelectorAll('#modal-add-asset-item h4');
            if (titleEls.length > 0) titleEls[0].innerHTML = 'เพิ่มข้อมูลทรัพย์สิน';
            // Reset button texts
            document.querySelectorAll('#modal-add-asset-item button[type="submit"]').forEach(b => {
                b.innerHTML = 'บันทึก';
            });
        }

        const modal = document.getElementById('modal-add-asset-item');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        // Reset forms
        const formHw = document.getElementById('form-hardware');
        const formSw = document.getElementById('form-software');
        const formNw = document.getElementById('form-network');
        if (formHw) formHw.reset();
        if (formSw) formSw.reset();
        if (formNw) formNw.reset();

        ['form-hardware', 'form-software', 'form-network'].forEach(fId => {
            const form = document.getElementById(fId);
            if (form) {
                form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500', 'focus:border-red-500'));
                form.querySelectorAll('[id$="-error"]').forEach(el => el.classList.add('hidden'));
            }
        });

        // Reset no-code and no-serial checkboxes & inputs
        ['hw', 'sw', 'nw'].forEach(p => {
            const noCode = document.getElementById(p + '-form-no-code');
            const noSerial = document.getElementById(p + '-form-no-serial');
            if (noCode && noCode.checked) {
                noCode.checked = false;
                if (typeof window.toggleNoCode === 'function') window.toggleNoCode(p);
            }
            if (noSerial && noSerial.checked) {
                noSerial.checked = false;
                if (typeof window.toggleNoSerial === 'function') window.toggleNoSerial(p);
            }
        });

        if (typeof window.clearAiFormAutoFill === 'function') window.clearAiFormAutoFill();

        if (typeof window.updateAssetStatusDropdowns === 'function') {
            window.updateAssetStatusDropdowns(isEdit);
        }

        if (!isEdit) {
            window.currentAssetImageRemoved = false;
            ['hw', 'sw', 'nw'].forEach(p => {
                if (typeof window.removeAssetImage === 'function') window.removeAssetImage(p);
            });
            window.currentAssetImageRemoved = false;

            // Call selectAssetCategoryTab with default 'ฮาร์ดแวร์' to reset view
            const firstTab = document.querySelector('.asset-cat-tab');
            if (typeof window.selectAssetCategoryTab === 'function') {
                window.selectAssetCategoryTab('ฮาร์ดแวร์', firstTab);
            }
        }
    };
    window.selectAssetCategoryTab = function (categoryName, btn) {
        // Update button styles
        const tabs = document.querySelectorAll('.asset-cat-tab');
        tabs.forEach(tab => {
            tab.classList.remove('bg-blue-600', 'text-white');
            tab.classList.add('bg-slate-100', 'text-slate-600');
        });
        if (btn) {
            btn.classList.remove('bg-slate-100', 'text-slate-600');
            btn.classList.add('bg-blue-600', 'text-white');
        }

        // Hide all forms
        const formHw = document.getElementById('form-hardware');
        const formSw = document.getElementById('form-software');
        const formNw = document.getElementById('form-network');

        if (formHw) formHw.classList.add('hidden');
        if (formSw) formSw.classList.add('hidden');
        if (formNw) formNw.classList.add('hidden');

        let targetCatId = null;
        let searchStr = '';

        // Show specific form
        if (categoryName.includes('ฮาร์ดแวร์') && formHw) {
            formHw.classList.remove('hidden');
            targetCatId = 'hw-form-category';
            searchStr = 'ฮาร์ดแวร์';
        } else if ((categoryName.includes('ซอฟต์แวร์') || categoryName.includes('ซอฟแวร์')) && formSw) {
            formSw.classList.remove('hidden');
            targetCatId = 'sw-form-category';
            searchStr = 'ซอฟต์แวร์';
        } else if (categoryName.includes('เครือข่าย') && formNw) {
            formNw.classList.remove('hidden');
            targetCatId = 'nw-form-category';
            searchStr = 'เครือข่าย';
        }

        // Auto select and lock category
        ['hw-form-category', 'sw-form-category', 'nw-form-category'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = true;
                el.classList.add('bg-slate-50', 'text-slate-500', 'cursor-not-allowed');
            }
        });

        if (targetCatId) {
            const catSelect = document.getElementById(targetCatId);
            if (catSelect) {
                let found = false;
                for (let i = 0; i < catSelect.options.length; i++) {
                    const txt = catSelect.options[i].text;
                    const val = catSelect.options[i].value;
                    if (txt.includes(searchStr) || val.includes(searchStr) || (searchStr === 'ซอฟต์แวร์' && (txt.includes('ซอฟ') || val.includes('ซอฟ')))) {
                        catSelect.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
                
                if (!found && searchStr) {
                    const displayStr = searchStr === 'เครือข่าย' ? 'ระบบเครือข่าย' : searchStr;
                    const newOpt = new Option(displayStr, displayStr);
                    catSelect.add(newOpt);
                    catSelect.value = displayStr;
                }

                catSelect.disabled = true;
                catSelect.classList.add('bg-slate-50', 'text-slate-500', 'cursor-not-allowed');
                if (typeof window.handleAiFormCategoryChange === 'function') {
                    window.handleAiFormCategoryChange();
                }
            }
        }
    };
    window.handleAiFormCategoryChange = function () {
        let prefix = 'ai';
        const formHw = document.getElementById('form-hardware');
        const formSw = document.getElementById('form-software');
        const formNw = document.getElementById('form-network');

        if (formHw && !formHw.classList.contains('hidden')) prefix = 'hw';
        else if (formSw && !formSw.classList.contains('hidden')) prefix = 'sw';
        else if (formNw && !formNw.classList.contains('hidden')) prefix = 'nw';

        const catSelect = document.getElementById(prefix + '-form-category');
        const kindSelect = document.getElementById(prefix + '-form-kind');
        if (!catSelect || !kindSelect) return;
        if (window.currentEditAssetId) return;

        // Reset kind & auto-fill fields
        kindSelect.innerHTML = '<option value="" disabled selected>-- เลือกชนิดทรัพย์สิน --</option>';
        if (typeof window.clearAiFormAutoFill === 'function') window.clearAiFormAutoFill();

        if (catSelect.value) {
            kindSelect.disabled = false;
            kindSelect.classList.remove('disabled:bg-slate-50', 'disabled:text-slate-400');

            const catVal = catSelect.value;
            let kinds = [];
            if (typeof window.assetKindsData !== 'undefined' && window.assetKindsData[catVal]) {
                kinds = window.assetKindsData[catVal];
            } else if (typeof window.assetKindsData !== 'undefined') {
                const selectedText = catSelect.options[catSelect.selectedIndex].text;
                if (window.assetKindsData[selectedText]) {
                    kinds = window.assetKindsData[selectedText];
                }
            }

            kinds.forEach(kind => {
                const opt = document.createElement('option');
                opt.value = kind;
                opt.text = kind;
                kindSelect.appendChild(opt);
            });
        } else {
            kindSelect.disabled = true;
            kindSelect.classList.add('disabled:bg-slate-50', 'disabled:text-slate-400');
        }
    };
    window.clearAiFormAutoFill = function () {
        if (window.currentEditAssetId) return;
        let prefix = 'ai';
        const formHw = document.getElementById('form-hardware');
        const formSw = document.getElementById('form-software');
        const formNw = document.getElementById('form-network');

        if (formHw && !formHw.classList.contains('hidden')) prefix = 'hw';
        else if (formSw && !formSw.classList.contains('hidden')) prefix = 'sw';
        else if (formNw && !formNw.classList.contains('hidden')) prefix = 'nw';

        const brandSelect = document.getElementById(prefix + '-form-brand');
        const modelSelect = document.getElementById(prefix + '-form-model');
        if (brandSelect) {
            if (brandSelect.tagName === 'SELECT') {
                brandSelect.innerHTML = '<option value="" disabled selected>-- เลือกรุ่น --</option>';
            } else {
                brandSelect.value = '';
            }
        }
        if (modelSelect) {
            if (modelSelect.tagName === 'SELECT') {
                modelSelect.innerHTML = '<option value="" disabled selected>-- เลือกรุ่น --</option>';
            } else {
                modelSelect.value = '';
            }
        }
    };
    window.handleAiFormKindChange = function () {
        let prefix = 'ai';
        const formHw = document.getElementById('form-hardware');
        const formSw = document.getElementById('form-software');
        const formNw = document.getElementById('form-network');

        if (formHw && !formHw.classList.contains('hidden')) prefix = 'hw';
        else if (formSw && !formSw.classList.contains('hidden')) prefix = 'sw';
        else if (formNw && !formNw.classList.contains('hidden')) prefix = 'nw';

        const kindSelect = document.getElementById(prefix + '-form-kind');
        const brandSelect = document.getElementById(prefix + '-form-brand');
        const modelSelect = document.getElementById(prefix + '-form-model');
        if (!kindSelect) return;

        const kindVal = kindSelect.value;
        const typeData = (typeof window.assetDeviceTypesData !== 'undefined') ? window.assetDeviceTypesData[kindVal] : null;

        if (typeData && !window.currentEditAssetId) {
            if (brandSelect) {
                if (brandSelect.tagName === 'SELECT') {
                    brandSelect.innerHTML = '';
                    const opt = document.createElement('option');
                    opt.value = typeData.brand || '';
                    opt.text = typeData.brand || '';
                    opt.selected = true;
                    brandSelect.appendChild(opt);

                    const emptyOpt = document.createElement('option');
                    emptyOpt.value = '';
                    emptyOpt.text = '-- เลือกรุ่น --';
                    brandSelect.insertBefore(emptyOpt, brandSelect.firstChild);
                } else if (typeData.brand && !brandSelect.value) {
                    brandSelect.value = typeData.brand;
                }
            }
            if (modelSelect) {
                if (modelSelect.tagName === 'SELECT') {
                    modelSelect.innerHTML = '';
                    const opt = document.createElement('option');
                    opt.value = typeData.model || '';
                    opt.text = typeData.model || '';
                    opt.selected = true;
                    modelSelect.appendChild(opt);

                    const emptyOpt = document.createElement('option');
                    emptyOpt.value = '';
                    emptyOpt.text = '-- เลือกรุ่น --';
                    modelSelect.insertBefore(emptyOpt, modelSelect.firstChild);
                } else if (typeData.model && !modelSelect.value) {
                    modelSelect.value = typeData.model;
                }
            }
        }
    };
    window.handleAssetCategoryFilterChange = function () {
        const catSelect = document.getElementById('device-category-filter');
        const kindSelect = document.getElementById('device-kind-filter');
        if (!catSelect || !kindSelect) return;

        kindSelect.innerHTML = '<option value="all">-- เลือกชนิดทรัพย์สิน --</option>';
        if (catSelect.value && catSelect.value !== 'all') {
            kindSelect.disabled = false;

            // Link with Basic Data if available
            let kinds = [];
            if (typeof assetKindsData !== 'undefined' && assetKindsData[catSelect.value]) {
                kinds = assetKindsData[catSelect.value];
            } else {
                // Fallback mock data
                if (catSelect.value === 'ฮาร์ดแวร์') {
                    kinds = ['คอมพิวเตอร์ตั้งโต๊ะ (PC)', 'โน้ตบุ๊ก (Notebook)', 'เครื่องพิมพ์ (Printer)', 'โปรเจคเตอร์ (Projector)'];
                } else if (catSelect.value === 'ซอฟต์แวร์') {
                    kinds = ['ระบบปฏิบัติการ (OS)', 'โปรแกรมสำนักงาน', 'โปรแกรมเฉพาะทาง'];
                } else if (catSelect.value === 'ไอโอที') {
                    kinds = ['สวิตช์ (Switch)', 'เราเตอร์ (Router)', 'จุดกระจายสัญญาณ (Access Point)'];
                }
            }

            kinds.forEach(kind => {
                const opt = document.createElement('option');
                opt.value = kind;
                opt.text = kind;
                kindSelect.appendChild(opt);
            });
        } else {
            kindSelect.disabled = true;
        }
        
        if (typeof window.renderDevicesList === 'function') {
            window.renderDevicesList();
        }
    };

    window.resetAssetFilters = function () {
        const search = document.getElementById('device-search');
        const catSelect = document.getElementById('device-category-filter');
        const kindSelect = document.getElementById('device-kind-filter');

        if (search) search.value = '';
        if (catSelect) catSelect.value = 'all';
        if (kindSelect) {
            kindSelect.value = 'all';
            kindSelect.disabled = true;
            kindSelect.innerHTML = '<option value="all">-- เลือกชนิดทรัพย์สิน --</option>';
        }
        renderDevicesList();
    };

    let assetCurrentViewMode = 'active';

    window.switchViewMode = function (mode) {
        assetCurrentViewMode = mode;
        const activeTab = document.getElementById('tab-view-active');
        const suspendedTab = document.getElementById('tab-view-suspended');

        if (activeTab && suspendedTab) {
            if (mode === 'active') {
                activeTab.className = 'px-4 py-2 text-xs font-extrabold border-b-2 border-blue-600 text-blue-600 transition';
                suspendedTab.className = 'px-4 py-2 text-xs font-extrabold border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5';
            } else {
                suspendedTab.className = 'px-4 py-2 text-xs font-extrabold border-b-2 border-red-500 text-red-500 transition flex items-center gap-1.5';
                activeTab.className = 'px-4 py-2 text-xs font-extrabold border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition';
            }
        }
        renderDevicesList();
    };

    window.renderDevicesList = function () {
        const search = document.getElementById('device-search')?.value || '';
        const category = document.getElementById('device-category-filter')?.value || 'all';
        const kind = document.getElementById('device-kind-filter')?.value || 'all';

        const tbody = document.getElementById('devices-table-body');
        if (!tbody) return;

        // ลบเงื่อนไขบังคับเลือกประเภทและชนิดทรัพย์สินออก

        const url = new URL(window.BASE_URL + '/admin/devices', window.location.origin);
        if (search) url.searchParams.append('search', search);
        if (category !== 'all') url.searchParams.append('category', category);
        if (kind !== 'all') url.searchParams.append('kind', kind);
        url.searchParams.append('status', assetCurrentViewMode);

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('devices-table-body');
                tbody.innerHTML = '';

                let suspendedCount = 0;

                if (data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-slate-500 font-bold">ไม่พบข้อมูลทรัพย์สิน</td></tr>`;
                } else {
                    data.forEach((device, index) => {
                        let badgeClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                        let statusText = device.status || 'พร้อมใช้งาน';

                        if (device.status === 'กำลังซ่อม') {
                            badgeClass = 'bg-amber-50 text-amber-600 border-amber-200';
                            statusText = 'กำลังซ่อม';
                        } else if (device.status === 'รออะไหล่') {
                            badgeClass = 'bg-orange-50 text-orange-600 border-orange-200';
                            statusText = 'รออะไหล่';
                        } else if (device.status === 'suspended') {
                            badgeClass = 'bg-red-50 text-red-600 border-red-200';
                            statusText = 'ระงับการใช้งาน';
                        } else {
                            badgeClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                            statusText = 'พร้อมใช้งาน';
                        }                        let buildingName = device.building_id || '-';
                        if (device.building_id && typeof gBuildingsData !== 'undefined') {
                            const b = gBuildingsData.find(x => x.building_id == device.building_id);
                            if (b && b.building_name) {
                                buildingName = b.building_name;
                            }
                        }

                        const row = document.createElement('tr');
                        row.className = 'hover:bg-slate-50 transition border-b border-slate-100';
                        row.innerHTML = `
                        <td class="p-3 sm:p-3.5 text-center font-mono font-bold text-slate-500">${index + 1}</td>
                        <td class="p-3 sm:p-3.5">
                            <div class="font-bold text-slate-900">${device.device_code || '-'}</div>
                        </td>
                        <td class="p-3 sm:p-3.5 whitespace-nowrap"><span class="px-2.5 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-600 inline-block">${device.category || '-'}</span></td>
                        <td class="p-3 sm:p-3.5 whitespace-nowrap"><span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold inline-block">${device.type || '-'}</span></td>
                        <td class="p-3 sm:p-3.5 whitespace-nowrap">${buildingName}</td>
                        <td class="p-3 sm:p-3.5 whitespace-nowrap">${device.responsible_person || device.personnel_name || '-'}</td>

                        <td class="p-3 sm:p-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5 whitespace-nowrap shrink-0">
                                <button type="button" onclick="viewAssetItem('${device.equipment_id}')" class="px-2.5 sm:px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-sm shadow-blue-200 whitespace-nowrap shrink-0">
                                    <i class="fa-solid fa-eye text-xs"></i> <span>ดูข้อมูล</span>
                                </button>
                                <button type="button" onclick="editAssetItem('${device.equipment_id}')" class="px-2.5 sm:px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-sm shadow-amber-200 whitespace-nowrap shrink-0">
                                    <i class="fa-solid fa-pen text-xs"></i> <span>แก้ไข</span>
                                </button>
                            </div>
                        </td>
                    `;
                        tbody.appendChild(row);
                    });
                }

                // Optional: update suspended count badge by making a separate API call if needed
            });
    };

    window.currentAssetImageRemoved = false;

    window.previewAssetImage = function (prefix, input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 5 * 1024 * 1024) {
                alert('ขนาดไฟล์รูปภาพเกิน 5 MB กรุณาเลือกไฟล์ใหม่');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById(prefix + '-form-image-preview');
                const placeholder = document.getElementById(prefix + '-form-image-placeholder');
                const removeBtn = document.getElementById(prefix + '-form-image-remove-btn');
                const overlay = document.getElementById(prefix + '-form-image-overlay');
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                if (placeholder) placeholder.classList.add('hidden');
                if (removeBtn) removeBtn.classList.remove('hidden');
                if (overlay) overlay.classList.remove('hidden');
                window.currentAssetImageRemoved = false;
            };
            reader.readAsDataURL(file);
        }
    };

    window.removeAssetImage = function (prefix) {
        const input = document.getElementById(prefix + '-form-image');
        const preview = document.getElementById(prefix + '-form-image-preview');
        const placeholder = document.getElementById(prefix + '-form-image-placeholder');
        const removeBtn = document.getElementById(prefix + '-form-image-remove-btn');
        const overlay = document.getElementById(prefix + '-form-image-overlay');
        if (input) input.value = '';
        if (preview) {
            preview.src = '';
            preview.classList.add('hidden');
        }
        if (placeholder) placeholder.classList.remove('hidden');
        if (removeBtn) removeBtn.classList.add('hidden');
        if (overlay) overlay.classList.add('hidden');
        window.currentAssetImageRemoved = true;
    };

    function genericSubmitAsset(data, formId) {
        let url = window.BASE_URL + '/admin/devices';
        let method = 'POST';

        const formData = new FormData();
        for (const key in data) {
            if (data[key] instanceof File) {
                formData.append(key, data[key]);
            } else if (data[key] !== null && data[key] !== undefined) {
                formData.append(key, data[key]);
            }
        }

        if (window.currentAssetImageRemoved) {
            formData.append('remove_image', '1');
        }

        if (window.currentEditAssetId) {
            url = window.BASE_URL + '/admin/devices/' + window.currentEditAssetId;
            formData.append('_method', 'PUT');
            method = 'POST';
        }

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    if (typeof playSuccessSound === 'function') playSuccessSound();
                    closeAssetItemModal();
                    document.getElementById(formId).reset();
                    ['hw', 'sw', 'nw'].forEach(p => {
                        if (typeof window.removeAssetImage === 'function') window.removeAssetImage(p);
                    });
                    renderDevicesList();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (res.message || 'ไม่สามารถบันทึกข้อมูลได้'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            });
    }

    window.toggleNoCode = function (prefix) {
        const chk = document.getElementById(prefix + '-form-no-code');
        const input = document.getElementById(prefix + '-form-code');
        const star = document.getElementById(prefix + '-code-required-star');
        const err = document.getElementById(prefix + '-form-code-error');
        if (!chk || !input) return;

        if (chk.checked) {
            input.dataset.oldVal = input.value;
            input.value = '';
            input.placeholder = '[ระบบสร้างรหัสให้อัตโนมัติ]';
            input.readOnly = true;
            input.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
            input.classList.remove('bg-white', 'text-slate-800', 'border-red-500');
            if (star) star.classList.add('hidden');
            if (err) err.classList.add('hidden');
        } else {
            const defPlaceholder = prefix === 'sw' ? 'เช่น SW-001' : (prefix === 'nw' ? 'เช่น NW-001' : 'เช่น INV-001');
            input.placeholder = defPlaceholder;
            input.readOnly = false;
            input.value = input.dataset.oldVal || '';
            input.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
            input.classList.add('bg-white', 'text-slate-800');
            if (star) star.classList.remove('hidden');
        }
    };

    window.toggleNoSerial = function (prefix) {
        const chk = document.getElementById(prefix + '-form-no-serial');
        const input = document.getElementById(prefix + '-form-serial');
        const star = document.getElementById(prefix + '-serial-required-star');
        const err = document.getElementById(prefix + '-form-serial-error');
        if (!chk || !input) return;

        if (chk.checked) {
            input.dataset.oldVal = input.value;
            input.value = 'N/A';
            input.readOnly = true;
            input.classList.add('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
            input.classList.remove('bg-white', 'text-slate-800', 'border-red-500');
            if (star) star.classList.add('hidden');
            if (err) err.classList.add('hidden');
        } else {
            const defSerial = prefix === 'sw' ? 'เช่น XXXXX-XXXXX หรือ Free' : (prefix === 'nw' ? 'เช่น AA:BB:CC หรือ SN-123' : 'เช่น SN-123456');
            input.placeholder = defSerial;
            input.readOnly = false;
            input.value = (input.dataset.oldVal && input.dataset.oldVal !== 'N/A') ? input.dataset.oldVal : '';
            input.classList.remove('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
            input.classList.add('bg-white', 'text-slate-800');
            if (star) star.classList.remove('hidden');
        }
    };

    function validateAssetForm(prefix) {
        const formId = prefix === 'hw' ? 'form-hardware' : (prefix === 'sw' ? 'form-software' : 'form-network');
        const formContainer = document.getElementById(formId);

        if (formContainer) {
            formContainer.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500', 'focus:border-red-500'));
            formContainer.querySelectorAll('[id$="-error"]').forEach(el => el.classList.add('hidden'));
        }

        let fieldSuffixes = [];
        if (prefix === 'hw') {
            fieldSuffixes = ['kind', 'brand', 'model', 'code', 'serial', 'specs', 'supplier', 'purchase-date', 'price', 'warranty', 'building', 'floor', 'room', 'responsible', 'status'];
        } else if (prefix === 'sw') {
            fieldSuffixes = ['kind', 'brand', 'model', 'code', 'specs', 'supplier', 'purchase-date', 'price', 'warranty', 'building', 'floor', 'room', 'responsible', 'status'];
        } else if (prefix === 'nw') {
            fieldSuffixes = ['kind', 'brand', 'model', 'code', 'serial', 'specs', 'supplier', 'purchase-date', 'price', 'warranty', 'building', 'floor', 'room', 'responsible', 'status'];
        }

        for (let i = 0; i < fieldSuffixes.length; i++) {
            const suffix = fieldSuffixes[i];

            // ข้ามตรวจสอบถ้าเลือกไม่มีรหัสครุภัณฑ์ หรือไม่มีหมายเลขเครื่อง
            if (suffix === 'code' && document.getElementById(prefix + '-form-no-code')?.checked) {
                continue;
            }
            if (suffix === 'serial' && document.getElementById(prefix + '-form-no-serial')?.checked) {
                continue;
            }

            const fieldId = prefix + '-form-' + suffix;
            const errorId = fieldId + '-error';

            const fieldEl = document.getElementById(fieldId);
            const errorEl = document.getElementById(errorId);

            if (fieldEl) {
                const val = fieldEl.value ? fieldEl.value.trim() : '';
                if (!val) {
                    fieldEl.classList.remove('border-slate-200', 'focus:border-blue-500');
                    fieldEl.classList.add('border-red-500', 'focus:border-red-500');
                    if (errorEl) {
                        errorEl.classList.remove('hidden');
                    }

                    fieldEl.focus();
                    fieldEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            }
        }

        return true;
    }

    window.parseThaiDate = function (dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('/');
        if (parts.length === 3) {
            const y = parseInt(parts[2]) - 543;
            return `${y}-${parts[1]}-${parts[0]}`;
        }
        return dateStr;
    };

    window.createHardwareItem = function (e) {
        if (e) e.preventDefault();
        if (!validateAssetForm('hw')) return false;

        const noCode = document.getElementById('hw-form-no-code')?.checked;
        const noSerial = document.getElementById('hw-form-no-serial')?.checked;

        let data = {
            category: 'ฮาร์ดแวร์',
            type: document.getElementById('hw-form-kind')?.value || '',
            brand: document.getElementById('hw-form-brand')?.value || '',
            model: document.getElementById('hw-form-model')?.value || '',
            device_code: noCode ? 'AUTO' : (document.getElementById('hw-form-code')?.value || ''),
            serial: noSerial ? 'N/A' : (document.getElementById('hw-form-serial')?.value || ''),
            specs: document.getElementById('hw-form-specs')?.value || document.getElementById('hw-form-details')?.value || '',
            supplier: document.getElementById('hw-form-supplier')?.value || '',
            purchase_date: window.parseThaiDate(document.getElementById('hw-form-purchase-date')?.value) || (document.getElementById('hw-form-purchase-date')?.value || ''),
            price: document.getElementById('hw-form-price')?.value?.replace(/,/g, '') || '',
            warranty: document.getElementById('hw-form-warranty')?.value || '',
            building_id: document.getElementById('hw-form-building')?.value || '',
            floor: document.getElementById('hw-form-floor')?.value || '',
            room_id: document.getElementById('hw-form-room')?.value || '',
            responsible_person: document.getElementById('hw-form-responsible')?.value || '',
            status: document.getElementById('hw-form-status')?.value || 'พร้อมใช้งาน'
        };

        const hwImgInput = document.getElementById('hw-form-image');
        if (hwImgInput && hwImgInput.files && hwImgInput.files[0]) {
            data.image = hwImgInput.files[0];
        }

        genericSubmitAsset(data, 'form-hardware');
    };

    window.createSoftwareItem = function (e) {
        if (e) e.preventDefault();
        if (!validateAssetForm('sw')) return false;

        const noCode = document.getElementById('sw-form-no-code')?.checked;
        const noSerial = document.getElementById('sw-form-no-serial')?.checked;

        let data = {
            category: 'ซอฟต์แวร์',
            type: document.getElementById('sw-form-kind')?.value || '',
            brand: document.getElementById('sw-form-brand')?.value || '',
            model: document.getElementById('sw-form-model')?.value || '',
            version: document.getElementById('sw-form-version')?.value || '',
            device_code: noCode ? 'AUTO' : (document.getElementById('sw-form-code')?.value || 'AUTO'),
            serial: noSerial ? 'N/A' : (document.getElementById('sw-form-serial')?.value || 'N/A'),
            supplier: document.getElementById('sw-form-supplier')?.value || '',
            purchase_date: window.parseThaiDate(document.getElementById('sw-form-purchase-date')?.value) || (document.getElementById('sw-form-purchase-date')?.value || ''),
            price: document.getElementById('sw-form-price')?.value?.replace(/,/g, '') || '',
            warranty: document.getElementById('sw-form-warranty')?.value || '',
            start_date: document.getElementById('sw-form-start-date')?.value || '',
            installation_date: document.getElementById('sw-form-installation-date')?.value || '',
            expiration_date: document.getElementById('sw-form-expiration-date')?.value || '',
            specs: document.getElementById('sw-form-specs')?.value || document.getElementById('sw-form-details')?.value || '',
            building_id: document.getElementById('sw-form-building')?.value || '',
            floor: document.getElementById('sw-form-floor')?.value || '',
            room_id: document.getElementById('sw-form-room')?.value || '',
            responsible_person: document.getElementById('sw-form-responsible')?.value || '',
            status: document.getElementById('sw-form-status')?.value || 'พร้อมใช้งาน'
        };

        const swImgInput = document.getElementById('sw-form-image');
        if (swImgInput && swImgInput.files && swImgInput.files[0]) {
            data.image = swImgInput.files[0];
        }

        genericSubmitAsset(data, 'form-software');
    };

    window.createNetworkItem = function (e) {
        if (e) e.preventDefault();
        if (!validateAssetForm('nw')) return false;

        const noCode = document.getElementById('nw-form-no-code')?.checked;
        const noSerial = document.getElementById('nw-form-no-serial')?.checked;

        let data = {
            category: 'ระบบเครือข่าย',
            type: document.getElementById('nw-form-kind')?.value || '',
            brand: document.getElementById('nw-form-brand')?.value || '',
            model: document.getElementById('nw-form-model')?.value || '',
            device_code: noCode ? 'AUTO' : (document.getElementById('nw-form-code')?.value || ''),
            serial: noSerial ? 'N/A' : (document.getElementById('nw-form-serial')?.value || ''),
            specs: document.getElementById('nw-form-specs')?.value || document.getElementById('nw-form-details')?.value || '',
            supplier: document.getElementById('nw-form-supplier')?.value || '',
            purchase_date: window.parseThaiDate(document.getElementById('nw-form-purchase-date')?.value) || (document.getElementById('nw-form-purchase-date')?.value || ''),
            price: document.getElementById('nw-form-price')?.value?.replace(/,/g, '') || '',
            warranty: document.getElementById('nw-form-warranty')?.value || '',
            building_id: document.getElementById('nw-form-building')?.value || '',
            floor: document.getElementById('nw-form-floor')?.value || '',
            room_id: document.getElementById('nw-form-room')?.value || '',
            responsible_person: document.getElementById('nw-form-responsible')?.value || '',
            status: document.getElementById('nw-form-status')?.value || 'พร้อมใช้งาน'
        };

        const nwImgInput = document.getElementById('nw-form-image');
        if (nwImgInput && nwImgInput.files && nwImgInput.files[0]) {
            data.image = nwImgInput.files[0];
        }

        genericSubmitAsset(data, 'form-network');
    };

    window.closeViewAssetModal = function () {
        const m = document.getElementById('modal-view-asset');
        if (m) {
            m.style.display = 'none';
            m.classList.add('hidden');
        }
    };

    window.viewAssetItem = function (id) {
        const m = document.getElementById('modal-view-asset');
        const content = document.getElementById('view-asset-content');
        if (!m || !content) return;

        m.style.display = 'flex';
        m.classList.remove('hidden');
        content.innerHTML = '<div class="text-center py-12 text-slate-400"><i class="fa-solid fa-spinner fa-spin text-3xl mb-3 text-blue-500"></i><br><span class="font-bold text-slate-600">กำลังโหลดข้อมูล...</span></div>';

        fetch(window.BASE_URL + '/admin/devices/' + id + '?t=' + new Date().getTime())
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const d = res.data;

                    // Helpers
                    const fDate = (dateStr) => {
                        if (!dateStr) return '-';
                        const dt = new Date(dateStr);
                        if (isNaN(dt)) return dateStr;
                        return dt.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
                    };

                    let buildingName = d.building_id || '-';
                    if (d.building && d.building.building_name) {
                        buildingName = d.building.building_name;
                    } else if (d.building_id && typeof gBuildingsData !== 'undefined') {
                        const b = gBuildingsData.find(x => x.building_id == d.building_id);
                        if (b && b.building_name) buildingName = b.building_name;
                    }

                    let floorDisplay = '-';
                    const floorVal = d.floor || (d.room && d.room.floor);
                    if (floorVal !== undefined && floorVal !== null && String(floorVal).trim() !== '') {
                        const strFloor = String(floorVal).trim();
                        floorDisplay = strFloor.startsWith('ชั้น') ? strFloor : 'ชั้น ' + strFloor;
                    }

                    let roomDisplay = '-';
                    const roomVal = d.room_id || (d.room && (d.room.room_code || d.room.room_name));
                    if (roomVal !== undefined && roomVal !== null && String(roomVal).trim() !== '') {
                        const strRoom = String(roomVal).trim();
                        const baseRoom = strRoom.startsWith('ห้อง') ? strRoom : 'ห้อง ' + strRoom;
                        if (d.room && d.room.room_name && d.room.room_name !== strRoom) {
                            roomDisplay = `${baseRoom} (${d.room.room_name})`;
                        } else {
                            roomDisplay = baseRoom;
                        }
                    }

                    // Default image fallback matching user design
                    let assetImg = window.BASE_URL + '/images/device_default.jpg';
                    if (d.image) {
                        assetImg = d.image.startsWith('http') ? d.image : (window.BASE_URL + '/' + d.image.replace(/^\//, ''));
                    }

                    // Status Badge
                    let statusBadge = '';
                    if (d.status === 'กำลังซ่อม') {
                        statusBadge = '<span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-50 text-amber-600 border border-amber-200/80 rounded-full text-xs font-extrabold shadow-xs"><i class="fa-solid fa-wrench text-amber-500"></i> กำลังซ่อม</span>';
                    } else if (d.status === 'รออะไหล่') {
                        statusBadge = '<span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-orange-50 text-orange-600 border border-orange-200/80 rounded-full text-xs font-extrabold shadow-xs"><i class="fa-solid fa-clock text-orange-500"></i> รออะไหล่</span>';
                    } else if (d.status === 'suspended') {
                        statusBadge = '<span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-red-50 text-red-600 border border-red-200/80 rounded-full text-xs font-extrabold shadow-xs"><i class="fa-solid fa-ban text-red-500"></i> ระงับการใช้งาน</span>';
                    } else {
                        statusBadge = '<span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-200/80 rounded-full text-xs font-extrabold shadow-xs"><i class="fa-solid fa-circle-check text-emerald-500"></i> พร้อมใช้งาน</span>';
                    }

                    const isSw = (d.category?.includes('ซอฟ') || d.category?.toLowerCase().includes('soft'));
                    const isNw = (d.category?.includes('เครือข่าย') || d.category?.toLowerCase().includes('net'));
                    const catIcon = isSw ? 'fa-compact-disc' : (isNw ? 'fa-network-wired' : 'fa-desktop');
                    const brandModel = [d.brand, d.model].filter(Boolean).join(' ');

                    let bLabel = 'ยี่ห้อ';
                    let mLabel = 'รุ่น';
                    let sLabel = 'Serial Number';
                    if (isSw) {
                        bLabel = 'ผู้พัฒนา / บริษัทผู้ผลิต';
                        mLabel = 'ชื่อโปรแกรม / เวอร์ชัน';
                        sLabel = 'รหัสลิขสิทธิ์ / Product Key';
                    } else if (isNw) {
                        bLabel = 'ยี่ห้อ / ผู้ผลิต';
                        mLabel = 'รุ่น / ชื่ออุปกรณ์ / SSID';
                        sLabel = 'MAC Address / Serial';
                    }

                    const html = `
                        <!-- Hero Box (Top Card) -->
                        <div class="bg-gradient-to-br from-blue-50/60 via-sky-50/30 to-indigo-50/40 rounded-3xl p-5 sm:p-6 border border-blue-100/80 flex flex-col sm:flex-row items-center sm:items-start justify-between gap-5 relative overflow-hidden">
                            <div class="flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-5 w-full sm:w-auto">
                                <!-- Asset Image Box -->
                                <div class="w-24 h-24 sm:w-28 sm:h-28 bg-white rounded-2xl p-2 shadow-sm border border-blue-100/80 flex items-center justify-center overflow-hidden shrink-0">
                                    <img src="${assetImg}" alt="${d.name || d.equipment_name || 'อุปกรณ์'}" class="w-full h-full object-contain">
                                </div>
                                <!-- Info -->
                                <div class="text-center sm:text-left">
                                    <!-- Badge: ประเภท / ชนิดทรัพย์สิน -->
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100/80 text-blue-600 text-xs font-bold rounded-lg mb-2">
                                        <i class="fa-solid ${catIcon} text-xs"></i>
                                        <span>${d.type || d.category || 'อุปกรณ์'}</span>
                                    </div>
                                    <!-- Big Title: Brand Model (เช่น Microsoft Window11) หรือชื่ออุปกรณ์ -->
                                    <h4 class="text-2xl font-black text-slate-800 tracking-tight mb-1">
                                        ${brandModel || d.name || d.equipment_name || d.type || '-'}
                                    </h4>
                                    ${brandModel && (d.type || d.name) && (d.type !== brandModel && d.name !== brandModel) ? `
                                        <div class="text-xs font-bold text-slate-500 mb-2">${d.name || d.type}</div>
                                    ` : ''}
                                    <!-- Meta tags row -->
                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-sm text-slate-600 font-semibold">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-tag text-slate-400"></i> ${d.category || '-'}
                                        </span>
                                        <span class="text-slate-300">|</span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-barcode text-slate-500"></i> ${d.device_code || '-'}
                                        </span>
                                        ${brandModel ? `
                                            <span class="text-slate-300">|</span>
                                            <span class="inline-flex items-center gap-1.5 text-blue-600 font-bold">
                                                <i class="fa-solid fa-cube text-blue-500"></i> ${brandModel}
                                            </span>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status & Print Action (Right Side) -->
                            <div class="flex flex-col items-center sm:items-end justify-between self-stretch shrink-0 pt-1">
                                <div>
                                    ${statusBadge}
                                </div>
                                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition cursor-pointer hover:underline mt-4 sm:mt-0">
                                    <i class="fa-solid fa-print"></i> พิมพ์เอกสาร
                                </button>
                            </div>
                        </div>

                        <!-- Data Sections -->
                        <div class="space-y-6 pt-1">
                            
                            <!-- Section 1 : ข้อมูลทั่วไป -->
                            <div>
                                <div class="flex items-center gap-3 mb-3.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs shrink-0 shadow-sm">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </div>
                                    <h5 class="text-base font-extrabold text-slate-800 whitespace-nowrap">ข้อมูลทั่วไป</h5>
                                    <div class="flex-1 h-[1px] bg-slate-200/80"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <!-- การแสดง ยี่ห้อ/รุ่น รวมกัน (เช่น Microsoft Window11) -->
                                    ${brandModel ? `
                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50/50 border border-blue-200/80 rounded-2xl p-4 flex items-center gap-3.5 sm:col-span-2 shadow-xs">
                                        <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-base shrink-0 shadow-sm shadow-blue-500/20">
                                            <i class="fa-solid ${isSw ? 'fa-compact-disc' : (isNw ? 'fa-network-wired' : 'fa-laptop')}"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-bold text-blue-600 mb-0.5">${isSw ? 'ผู้ผลิตและชื่อโปรแกรม (Brand & Model)' : 'ยี่ห้อและรุ่น (Brand & Model)'}</p>
                                            <p class="text-base font-black text-slate-900">${brandModel}</p>
                                        </div>
                                    </div>
                                    ` : ''}

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-layer-group"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">ประเภท</p>
                                            <p class="text-sm font-bold text-slate-800">${d.category || '-'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-cube"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">ชนิดทรัพย์สิน</p>
                                            <p class="text-sm font-bold text-slate-800">${d.type || '-'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-tag"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">${bLabel}</p>
                                            <p class="text-sm font-bold text-slate-800">${d.brand || '-'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-hashtag"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">${mLabel}</p>
                                            <p class="text-sm font-bold text-slate-800">${d.model || '-'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-barcode"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">${sLabel}</p>
                                            <p class="text-sm font-bold text-slate-800">${d.serial || '-'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 2 : สถานที่ตั้ง / การติดตั้ง -->
                            <div>
                                <div class="flex items-center gap-3 mb-3.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs shrink-0 shadow-sm">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                    <h5 class="text-base font-extrabold text-slate-800 whitespace-nowrap">สถานที่ตั้ง / การติดตั้ง</h5>
                                    <div class="flex-1 h-[1px] bg-slate-200/80"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-3.5">
                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-building"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">อาคาร</p>
                                            <p class="text-sm font-bold text-slate-800">${buildingName}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-layer-group"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">ชั้น</p>
                                            <p class="text-sm font-bold text-slate-800">${floorDisplay}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-door-open"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">ห้อง</p>
                                            <p class="text-sm font-bold text-slate-800">${roomDisplay}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-user-check"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">บุคลากรที่รับผิดชอบ</p>
                                            <p class="text-sm font-bold text-slate-800">${d.responsible_person || '- ไม่ระบุ -'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-calendar-day"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">วันที่ติดตั้ง</p>
                                            <p class="text-sm font-bold text-slate-800">${d.installation_date ? fDate(d.installation_date) : '-'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3 : สเปค / รายละเอียดเพิ่มเติม -->
                            <div>
                                <div class="flex items-center gap-3 mb-3.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs shrink-0 shadow-sm">
                                        <i class="fa-solid fa-gear"></i>
                                    </div>
                                    <h5 class="text-base font-extrabold text-slate-800 whitespace-nowrap">${(() => {
                                        if (isSw) return 'รายละเอียดลิขสิทธิ์ / การติดตั้ง';
                                        if (isNw) return 'IP Address / พอร์ต / จุดเชื่อมต่อ';
                                        return 'สเปค / รายละเอียดเพิ่มเติม';
                                    })()}</h5>
                                    <div class="flex-1 h-[1px] bg-slate-200/80"></div>
                                </div>
                                <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-800 whitespace-pre-line">${d.spec || d.specs || d.name || '-'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4 : ข้อมูลการจัดซื้อและการรับประกัน -->
                            <div>
                                <div class="flex items-center gap-3 mb-3.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs shrink-0 shadow-sm">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <h5 class="text-base font-extrabold text-slate-800 whitespace-nowrap">ข้อมูลการจัดซื้อและการรับประกัน</h5>
                                    <div class="flex-1 h-[1px] bg-slate-200/80"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-shop"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">ผู้จัดจำหน่าย</p>
                                            <p class="text-sm font-bold text-slate-800">${d.supplier || '-'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-calendar-check"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">วันที่จัดซื้อ</p>
                                            <p class="text-sm font-bold text-slate-800">${d.purchase_date ? fDate(d.purchase_date) : '-'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-baht-sign"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">ราคาทรัพย์สิน</p>
                                            <p class="text-sm font-bold text-slate-800">${d.price ? (Number(d.price).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' บาท') : '-'}</p>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-full bg-blue-100/80 text-blue-600 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-semibold text-slate-400 mb-0.5">ระยะเวลารับประกัน</p>
                                            <p class="text-sm font-bold text-slate-800">${d.warranty || '-'}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    `;
                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<div class="text-center py-12 text-red-500"><i class="fa-solid fa-file-circle-xmark text-5xl mb-3 opacity-50"></i><br><span class="font-bold text-lg">ไม่พบข้อมูลทรัพย์สินนี้</span></div>';
                }
            })
            .catch(err => {
                content.innerHTML = '<div class="text-center py-12 text-red-500"><i class="fa-solid fa-triangle-exclamation text-5xl mb-3 opacity-50"></i><br><span class="font-bold text-lg">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</span></div>';
            });
    };
    window.editAssetItem = function (id) {
        window.currentEditAssetId = id;

        // Change Title
        const titleEls = document.querySelectorAll('#modal-add-asset-item h4');
        if (titleEls.length > 0) titleEls[0].innerHTML = 'แก้ไขข้อมูลทรัพย์สิน';

        // Change Button texts
        document.querySelectorAll('#modal-add-asset-item button[type="submit"]').forEach(b => {
            b.innerHTML = 'บันทึก';
        });

        // Fetch Data
        fetch(window.BASE_URL + '/admin/devices/' + id + '?t=' + new Date().getTime())
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const d = res.data;

                    // Open Modal and preserve currentEditAssetId
                    window.openAssetItemModal(true);
                    window.currentEditAssetId = id;

                    if (typeof window.updateAssetStatusDropdowns === 'function') {
                        window.updateAssetStatusDropdowns(true, d.status || 'พร้อมใช้งาน');
                    }

                    // Robust category detection (handles 'ซอฟต์แวร์', 'ซอฟแวร์', 'ระบบเครือข่าย', 'เครือข่าย', etc.)
                    const catLower = (d.category || '').toLowerCase();
                    let tabName = 'ฮาร์ดแวร์';
                    let prefix = 'hw';
                    if (catLower.includes('ซอฟ') || catLower.includes('soft')) {
                        tabName = 'ซอฟต์แวร์';
                        prefix = 'sw';
                    } else if (catLower.includes('เครือข่าย') || catLower.includes('net')) {
                        tabName = 'ระบบเครือข่าย';
                        prefix = 'nw';
                    } else {
                        tabName = 'ฮาร์ดแวร์';
                        prefix = 'hw';
                    }

                    // Select active category tab button
                    const tabs = document.querySelectorAll('.asset-cat-tab');
                    let targetBtn = null;
                    tabs.forEach(t => {
                        const txt = t.innerText;
                        if (prefix === 'sw' && (txt.includes('ซอฟ') || txt.includes('Soft'))) targetBtn = t;
                        else if (prefix === 'nw' && (txt.includes('เครือข่าย') || txt.includes('Net'))) targetBtn = t;
                        else if (prefix === 'hw' && (txt.includes('ฮาร์ดแวร์') || txt.includes('Hard'))) targetBtn = t;
                    });
                    tabs.forEach(tab => {
                        tab.classList.remove('bg-blue-600', 'text-white');
                        tab.classList.add('bg-slate-100', 'text-slate-600');
                    });
                    if (targetBtn) {
                        targetBtn.classList.remove('bg-slate-100', 'text-slate-600');
                        targetBtn.classList.add('bg-blue-600', 'text-white');
                    }

                    // Show active form, hide others
                    const formHw = document.getElementById('form-hardware');
                    const formSw = document.getElementById('form-software');
                    const formNw = document.getElementById('form-network');
                    if (formHw) formHw.classList.add('hidden');
                    if (formSw) formSw.classList.add('hidden');
                    if (formNw) formNw.classList.add('hidden');

                    const activeForm = prefix === 'hw' ? formHw : (prefix === 'sw' ? formSw : formNw);
                    if (activeForm) activeForm.classList.remove('hidden');

                    const setVal = (idSuffix, val) => {
                        const el = document.getElementById(prefix + '-form-' + idSuffix);
                        if (el && val !== null && val !== undefined) el.value = val;
                    };

                    // Category dropdown
                    const catSelect = document.getElementById(prefix + '-form-category');
                    if (catSelect) {
                        let catFound = false;
                        for (let i = 0; i < catSelect.options.length; i++) {
                            const opt = catSelect.options[i];
                            if (opt.value === d.category || opt.text === d.category ||
                               (catLower.includes('ซอฟ') && (opt.value.includes('ซอฟ') || opt.text.includes('ซอฟ'))) ||
                               (catLower.includes('เครือข่าย') && (opt.value.includes('เครือข่าย') || opt.text.includes('เครือข่าย'))) ||
                               (catLower.includes('ฮาร์ด') && (opt.value.includes('ฮาร์ด') || opt.text.includes('ฮาร์ด')))) {
                                catSelect.selectedIndex = i;
                                catFound = true;
                                break;
                            }
                        }
                        if (!catFound && d.category) {
                            const newOpt = new Option(d.category, d.category, true, true);
                            catSelect.add(newOpt);
                            catSelect.value = d.category;
                        }
                        catSelect.disabled = true;
                        catSelect.classList.add('bg-slate-50', 'text-slate-500', 'cursor-not-allowed');
                    }

                    // Kind dropdown
                    const kindSelect = document.getElementById(prefix + '-form-kind');
                    if (kindSelect) {
                        kindSelect.innerHTML = '<option value="" disabled>-- เลือกชนิดทรัพย์สิน --</option>';
                        const catKey = catSelect ? catSelect.value : d.category;
                        let kinds = [];
                        if (typeof window.assetKindsData !== 'undefined' && window.assetKindsData[catKey]) {
                            kinds = window.assetKindsData[catKey];
                        }
                        kinds.forEach(kind => {
                            const opt = document.createElement('option');
                            opt.value = kind;
                            opt.text = kind;
                            kindSelect.appendChild(opt);
                        });

                        let typeFound = false;
                        if (d.type) {
                            for (let i = 0; i < kindSelect.options.length; i++) {
                                if (kindSelect.options[i].value === d.type || kindSelect.options[i].text === d.type) {
                                    kindSelect.selectedIndex = i;
                                    typeFound = true;
                                    break;
                                }
                            }
                            if (!typeFound) {
                                const newOpt = document.createElement('option');
                                newOpt.value = d.type;
                                newOpt.text = d.type;
                                newOpt.selected = true;
                                kindSelect.appendChild(newOpt);
                                kindSelect.value = d.type;
                            }
                        }
                        kindSelect.disabled = false;
                        kindSelect.classList.remove('disabled:bg-slate-50', 'disabled:text-slate-400');
                    }

                    // Brand and Model
                    setVal('brand', d.brand || '');
                    setVal('model', d.model || '');

                    // Device code
                    const codeChk = document.getElementById(prefix + '-form-no-code');
                    if (codeChk) {
                        if (!d.device_code || d.device_code === 'AUTO' || d.device_code.includes('อัตโนมัติ')) {
                            codeChk.checked = true;
                            if (typeof window.toggleNoCode === 'function') window.toggleNoCode(prefix);
                        } else {
                            codeChk.checked = false;
                            if (typeof window.toggleNoCode === 'function') window.toggleNoCode(prefix);
                            setVal('code', d.device_code);
                        }
                    } else {
                        setVal('code', d.device_code);
                    }

                    // Serial
                    const serialChk = document.getElementById(prefix + '-form-no-serial');
                    if (serialChk) {
                        if (!d.serial || d.serial === 'N/A') {
                            serialChk.checked = true;
                            if (typeof window.toggleNoSerial === 'function') window.toggleNoSerial(prefix);
                        } else {
                            serialChk.checked = false;
                            if (typeof window.toggleNoSerial === 'function') window.toggleNoSerial(prefix);
                            setVal('serial', d.serial);
                        }
                    } else {
                        setVal('serial', d.serial || 'N/A');
                    }

                    // Specs / Details
                    const specValue = d.spec || d.specs || '';
                    setVal('details', specValue);
                    setVal('specs', specValue);

                    // Supplier, Purchase Date, Price, Warranty
                    setVal('supplier', d.supplier || '');

                    const pDateEl = document.getElementById(prefix + '-form-purchase-date');
                    if (pDateEl) {
                        if (pDateEl._flatpickr) {
                            pDateEl._flatpickr.setDate(d.purchase_date || '');
                        } else {
                            pDateEl.value = d.purchase_date || '';
                        }
                    }

                    setVal('price', d.price || '');
                    setVal('warranty', d.warranty || '');

                    // Building, Floor, Room
                    setVal('building', d.building_id || '');
                    if (typeof window.handleAiFormBuildingChange === 'function') {
                        window.handleAiFormBuildingChange();
                    }

                    const fSelect = document.getElementById(prefix + '-form-floor');
                    if (fSelect && d.floor) {
                        let floorFound = false;
                        for (let opt of fSelect.options) {
                            if (opt.value == d.floor) {
                                fSelect.value = opt.value;
                                floorFound = true;
                                break;
                            }
                        }
                        if (!floorFound) {
                            const opt = document.createElement('option');
                            opt.value = d.floor;
                            opt.text = 'ชั้น ' + d.floor;
                            opt.selected = true;
                            fSelect.appendChild(opt);
                            fSelect.value = d.floor;
                        }
                        fSelect.disabled = false;
                    }
                    if (typeof window.handleAiFormFloorChange === 'function') {
                        window.handleAiFormFloorChange();
                    }

                    const rSelect = document.getElementById(prefix + '-form-room');
                    if (rSelect && d.room_id) {
                        let roomFound = false;
                        for (let opt of rSelect.options) {
                            if (opt.value == d.room_id || opt.text == d.room_id) {
                                rSelect.value = opt.value;
                                roomFound = true;
                                break;
                            }
                        }
                        if (!roomFound) {
                            const opt = document.createElement('option');
                            opt.value = d.room_id;
                            opt.text = d.room_id;
                            opt.selected = true;
                            rSelect.appendChild(opt);
                            rSelect.value = d.room_id;
                        }
                        rSelect.disabled = false;
                    }

                    // Responsible Person
                    const respSelect = document.getElementById(prefix + '-form-responsible');
                    if (respSelect && (d.responsible_person || d.personnel_name)) {
                        const respVal = d.responsible_person || d.personnel_name;
                        let respFound = false;
                        for (let opt of respSelect.options) {
                            if (opt.value == respVal || opt.text == respVal || (opt.text && opt.text.includes(respVal))) {
                                respSelect.value = opt.value;
                                respFound = true;
                                break;
                            }
                        }
                        if (!respFound) {
                            const opt = document.createElement('option');
                            opt.value = respVal;
                            opt.text = respVal;
                            opt.selected = true;
                            respSelect.appendChild(opt);
                            respSelect.value = respVal;
                        }
                    }

                    // Status
                    setVal('status', d.status || 'พร้อมใช้งาน');

                    // Image preview in edit mode
                    ['hw', 'sw', 'nw'].forEach(p => {
                        const imgEl = document.getElementById(p + '-form-image-preview');
                        const phEl = document.getElementById(p + '-form-image-placeholder');
                        const rmBtn = document.getElementById(p + '-form-image-remove-btn');
                        const overlay = document.getElementById(p + '-form-image-overlay');
                        const fileInput = document.getElementById(p + '-form-image');
                        if (fileInput) fileInput.value = '';

                        if (d.image) {
                            const fullImgUrl = d.image.startsWith('http') ? d.image : (window.BASE_URL + '/' + d.image.replace(/^\//, ''));
                            if (imgEl) {
                                imgEl.src = fullImgUrl;
                                imgEl.classList.remove('hidden');
                            }
                            if (phEl) phEl.classList.add('hidden');
                            if (rmBtn) rmBtn.classList.remove('hidden');
                            if (overlay) overlay.classList.remove('hidden');
                        } else {
                            if (imgEl) {
                                imgEl.src = '';
                                imgEl.classList.add('hidden');
                            }
                            if (phEl) phEl.classList.remove('hidden');
                            if (rmBtn) rmBtn.classList.add('hidden');
                            if (overlay) overlay.classList.add('hidden');
                        }
                    });
                    window.currentAssetImageRemoved = false;

                    // Software Specific Fields
                    if (prefix === 'sw') {
                        setVal('version', d.version || '');
                        setVal('start-date', d.start_date || '');
                        setVal('installation-date', d.installation_date || '');
                        setVal('expiration-date', d.expiration_date || '');
                    }
                } else {
                    alert('ไม่พบข้อมูลทรัพย์สิน');
                    window.currentEditAssetId = null;
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                window.currentEditAssetId = null;
            });
    };

    window.changeAssetStatus = function (id, status) {
        fetch(window.BASE_URL + `/admin/devices/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: status })
        })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    renderDevicesList();
                }
            });
    };


    window.currentPersonnelSubTab = window.currentPersonnelSubTab || 'active';

    window.switchPersonnelSubTab = function (tab) {
        window.currentPersonnelSubTab = tab;
        const activeBtn = document.getElementById('personnel-subtab-active-btn');
        const trashBtn = document.getElementById('personnel-subtab-trash-btn');

        if (tab === 'active') {
            activeBtn.className = 'py-2.5 border-b-2 border-blue-600 text-blue-600 flex items-center gap-2 transition-all cursor-pointer';
            trashBtn.className = 'py-2.5 border-b-2 border-transparent text-slate-400 hover:text-red-500 flex items-center gap-2 transition-all cursor-pointer';
        } else {
            trashBtn.className = 'py-2.5 border-b-2 border-red-500 text-red-500 flex items-center gap-2 transition-all cursor-pointer';
            activeBtn.className = 'py-2.5 border-b-2 border-transparent text-slate-400 hover:text-blue-600 flex items-center gap-2 transition-all cursor-pointer';
        }
        renderPersonnelList();
    };

    window.renderPersonnelList = function () {
        const query = document.getElementById('personnel-search-input')?.value.toLowerCase() || '';
        const rows = document.querySelectorAll('.personnel-row');
        let visibleCount = 0;
        let activeCount = 0;
        let trashCount = 0;

        const deptGroups = {
            'resource': 'ฝ่ายบริหารทรัพยากร',
            'planning': 'ฝ่ายนโยบายและแผน',
            'student': 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า',
            'academic': 'ฝ่ายวิชาการ'
        };

        const currentTab = window.currentPersonnelSubTab || 'active';

        rows.forEach(row => {
            const status = row.getAttribute('data-status') || 'active';

            if (status === 'active') activeCount++;
            if (status === 'trash') trashCount++;

            let u = {};
            try {
                const userDataStr = row.getAttribute('data-user');
                if (userDataStr) u = JSON.parse(decodeURIComponent(userDataStr));
            } catch (e) { }

            const fname = (u.first_name || '').toLowerCase();
            const lname = (u.last_name || '').toLowerCase();
            const type = '-';
            const deptGroup = (deptGroups[u.department_group] || u.department_group || '').toLowerCase();
            const dept = (u.department || '').toLowerCase();

            const searchMatch = fname.includes(query) || lname.includes(query) || type.includes(query) || deptGroup.includes(query) || dept.includes(query);
            const tabMatch = status === currentTab;

            if (searchMatch && tabMatch) {
                row.style.display = '';
                visibleCount++;

                const seqCell = row.querySelector('td:first-child');
                if (seqCell) seqCell.innerText = visibleCount;
            } else {
                row.style.display = 'none';
            }
        });

        const activeBadge = document.getElementById('personnel-count-active-badge');
        if (activeBadge) activeBadge.innerText = activeCount;

        const trashBadge = document.getElementById('personnel-count-trash-badge');
        if (trashBadge) trashBadge.innerText = trashCount;

        const emptyRow = document.getElementById('personnel-empty-row');
        if (emptyRow) {
            emptyRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    };

    // Initialize personnel list on page load
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('personnel-list-table-body')) {
            if (typeof window.renderPersonnelList === 'function') {
                window.renderPersonnelList();
            }
        }
    });

    window.viewPersonnel = function (btn) {
        const tr = btn.closest('tr');
        if (!tr) return;

        const userDataStr = tr.getAttribute('data-user');
        if (!userDataStr) return;

        try {
            const u = JSON.parse(decodeURIComponent(userDataStr));
            const deptGroups = {
                'resource': 'ฝ่ายบริหารทรัพยากร',
                'planning': 'ฝ่ายนโยบายและแผน',
                'student': 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า',
                'academic': 'ฝ่ายวิชาการ'
            };

            const roleLabels = {
                'teacher': 'ครู / อาจารย์ (Teacher)',
                'technician': 'ช่างเทคนิค (Technician)',
                'head': 'หัวหน้าส่วนงาน (Head)',
                'executive': 'ผู้บริหาร (Executive)',
                'admin': 'ผู้ดูแลระบบ (Admin)'
            };

            const setText = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val || '-';
            };

            // à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¸ªà¹ˆà¸§à¸™à¸•à¸±à¸§
            setText('view-personnel-prefix', u.prefix);
            setText('view-personnel-fname', u.first_name);
            setText('view-personnel-lname', u.last_name);
            setText('view-personnel-fname-en', u.first_name_en);
            setText('view-personnel-lname-en', u.last_name_en);
            setText('view-personnel-citizen-id', u.citizen_id);

            // à¸‡à¸²à¸™ / à¸ªà¸´à¸—à¸˜à¸´à¹Œ
            setText('view-personnel-type', u.position);
            setText('view-personnel-dept-group', deptGroups[u.department_group] || u.department_group);
            setText('view-personnel-dept', u.department);

            // à¸ªà¸´à¸—à¸˜à¸´à¹Œ: à¸­à¸²à¸ˆà¹€à¸›à¹‡à¸™ string à¹€à¸”à¸µà¸¢à¸§à¸«à¸£à¸·à¸­ array
            const roleRaw = u.roles || u.role || '';
            const roleArr = Array.isArray(roleRaw) ? roleRaw : roleRaw.toString().split(',').map(r => r.trim()).filter(Boolean);
            const roleText = roleArr.map(r => roleLabels[r] || r).join(', ');
            setText('view-personnel-role', roleText);

            // à¸Šà¸·à¹ˆà¸­à¸œà¸¹à¹‰à¹ƒà¸Šà¹‰
            const pId = u.id || u.user_id;
            setText('view-personnel-username', pId);

            // à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™ (à¸ˆà¸³à¸¥à¸­à¸‡à¹à¸ªà¸”à¸‡à¸œà¸¥à¹€à¸‰à¸žà¸²à¸° admin)
            const passEl = document.getElementById('view-personnel-password');
            const passEye = document.getElementById('view-personnel-password-eye');
            if (passEl) {
                passEl.type = 'text';
                if (passEye) {
                    passEye.classList.remove('fa-eye');
                    passEye.classList.add('fa-eye-slash');
                }
                if (u.plain_password) {
                    passEl.value = u.plain_password;
                } else if ((pId && pId.toLowerCase() === 'admin') || (u.username && u.username.toLowerCase() === 'admin')) {
                    passEl.value = 'Admin1234';
                } else {
                    passEl.value = '-';
                }
            }

            // à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¸•à¸´à¸”à¸•à¹ˆà¸­
            setText('view-personnel-phone', u.phone);
            setText('view-personnel-email', u.email);

            // à¸£à¸¹à¸›à¹‚à¸›à¸£à¹„à¸Ÿà¸¥à¹Œ
            const imgEl = document.getElementById('view-personnel-profile-img');
            const iconEl = document.getElementById('view-personnel-profile-icon');
            const labelEl = document.getElementById('view-personnel-profile-label');
            if (imgEl && iconEl && labelEl) {
                if (u.profile_photo) {
                    imgEl.src = window.BASE_URL + '/uploads/profiles/' + u.profile_photo;
                    imgEl.classList.remove('hidden');
                    iconEl.classList.add('hidden');
                    labelEl.classList.add('hidden');
                } else {
                    imgEl.src = '';
                    imgEl.classList.add('hidden');
                    iconEl.classList.remove('hidden');
                    labelEl.classList.remove('hidden');
                }
            }

            const modal = document.getElementById('modal-view-personnel');
            if (modal) {
                modal.classList.remove('hidden');
            }
        } catch (e) {
            console.error('Error parsing user data', e);
        }
    };

    window.closeViewPersonnelModal = function () {
        const modal = document.getElementById('modal-view-personnel');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    // Load data on start
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('tab-assets')) {
            renderDevicesList();
        }
    });

    window.openAdminCustomModal = function (title, message, confirmCallback, confirmText = 'ยืนยัน') {
        const modal = document.getElementById('modal-admin-custom-confirm');
        const titleEl = document.getElementById('admin-custom-modal-title');
        const messageEl = document.getElementById('admin-custom-modal-message');
        const confirmBtn = document.getElementById('admin-custom-modal-confirm-btn');

        if (!modal) return;

        if (titleEl) titleEl.innerText = title;
        if (messageEl) messageEl.innerText = message;
        if (confirmBtn) confirmBtn.innerText = confirmText;

        // Remove old event listeners by cloning the button
        if (confirmBtn) {
            const newBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

            newBtn.addEventListener('click', function () {
                window.closeAdminCustomModal();
                if (typeof confirmCallback === 'function') {
                    confirmCallback();
                }
            });
        }

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    };

    window.closeAdminCustomModal = function () {
        const modal = document.getElementById('modal-admin-custom-confirm');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.previewAdminProfileImage = function (input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const previewImg = document.getElementById('profile-image-img');
                const previewContainer = document.getElementById('profile-image-preview');
                const placeholder = document.getElementById('profile-image-placeholder');

                if (previewImg && previewContainer && placeholder) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    };

    // Initialize datepickers
    if (typeof flatpickr !== 'undefined') {
        const fpConfig = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            defaultDate: 'today',
            locale: 'th',
            allowInput: true,
            formatDate: (date, format, locale) => {
                const d = date.getDate().toString().padStart(2, '0');
                const m = (date.getMonth() + 1).toString().padStart(2, '0');
                const y = date.getFullYear() + 543;
                return d + '/' + m + '/' + y;
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
            }
        };
        flatpickr('#hw-form-purchase-date, #sw-form-purchase-date, #nw-form-purchase-date', fpConfig);
    }
    
    function openAdminProfileModal() {
        const modal = document.getElementById('modal-admin-profile');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
    }
    
    function closeAdminProfileModal() {
        const modal = document.getElementById('modal-admin-profile');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    }

    window.showLogoutModal = function () {
        const modal = document.getElementById('logout-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
    };
    
    window.closeLogoutModal = function () {
        const modal = document.getElementById('logout-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.confirmLogout = function () {
        const form = document.getElementById('logout-form');
        if (form) form.submit();
    };

    // ==========================================
    // ระบบระงับบัญชี (Suspend) และ คืนค่า (Restore)
    // ==========================================
    window.setupSuspendUserForm = function () {
        const suspendForm = document.getElementById('suspend-user-form');
        if (suspendForm && !suspendForm.dataset.listenerAttached) {
            suspendForm.dataset.listenerAttached = 'true';
            suspendForm.addEventListener('submit', function (e) {
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
            reasonInput.addEventListener('input', function () {
                const errorMsg = document.getElementById('suspend-error-message');
                if (errorMsg && this.value.trim()) {
                    errorMsg.classList.add('hidden');
                }
            });
        }
    };

    window.openSuspendUserModal = function (id) {
        window.setupSuspendUserForm();
        const modal = document.getElementById('modal-suspend-user');
        const idInput = document.getElementById('suspend-modal-user-id');
        const reasonInput = document.getElementById('suspend-reason-input');
        const errorMsg = document.getElementById('suspend-error-message');

        if (modal) {
            if (idInput) idInput.value = id;
            if (reasonInput) reasonInput.value = '';
            if (errorMsg) errorMsg.classList.add('hidden');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            if (reasonInput) {
                setTimeout(() => reasonInput.focus(), 60);
            }
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ระงับการใช้งานบัญชี',
                text: 'กรุณาระบุเหตุผลการระงับบัญชีผู้ใช้:',
                input: 'textarea',
                inputPlaceholder: 'ระบุเหตุผลที่ต้องการระงับบัญชี...',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl p-6 font-semibold',
                    title: 'text-lg font-bold text-slate-800'
                },
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'กรุณาระบุเหตุผลการระงับ';
                    }
                }
            }).then((res) => {
                if (res.isConfirmed && res.value) {
                    window.changeUserStatus(id, 'suspended', res.value.trim());
                }
            });
        } else {
            const reason = prompt('กรุณาระบุเหตุผลการระงับบัญชีผู้ใช้:');
            if (reason && reason.trim()) {
                window.changeUserStatus(id, 'suspended', reason.trim());
            }
        }
    };
    window.softDeleteUser = function (id) {
        window.openSuspendUserModal(id);
    };

    window.closeSuspendUserModal = function () {
        const modal = document.getElementById('modal-suspend-user');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
        const errorMsg = document.getElementById('suspend-error-message');
        if (errorMsg) errorMsg.classList.add('hidden');
    };

    window.restoreUser = function (id) {
        const modal = document.getElementById('modal-restore-user');
        const idInput = document.getElementById('restore-modal-user-id');
        if (modal && idInput) {
            idInput.value = id;
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยกเลิกระงับการใช้งานบัญชี',
                text: 'คุณต้องการยกเลิกระงับบัญชีนี้ให้กลับมาใช้งานได้ตามปกติใช่หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยกเลิกระงับ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl p-6 font-semibold',
                    title: 'text-lg font-bold text-slate-800'
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    window.changeUserStatus(id, 'approved', null);
                }
            });
        } else {
            if (confirm('คุณต้องการคืนค่าบัญชีนี้ให้กลับมาใช้งานได้ตามปกติใช่หรือไม่?')) {
                window.changeUserStatus(id, 'approved', null);
            }
        }
    };

    window.closeRestoreUserModal = function () {
        const modal = document.getElementById('modal-restore-user');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    };

    window.confirmRestoreUser = function () {
        const id = document.getElementById('restore-modal-user-id')?.value;
        window.closeRestoreUserModal();
        if (id) window.changeUserStatus(id, 'approved', null);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.setupSuspendUserForm);
    } else {
        window.setupSuspendUserForm();
    }
</script>



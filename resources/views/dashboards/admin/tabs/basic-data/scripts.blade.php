<!-- JavaScript รองรับการทำงานของ Sub-tab ทั้งหมด -->
<script>
    let currentBasicSubTab = 'device-types';

    window.showAdminToast = function(title, text, icon = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                scrollbarPadding: false,
                icon: icon,
                title: title,
                text: text,
                showConfirmButton: false,
                timer: 1500,
                heightAuto: false,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100 p-6',
                    title: 'text-lg font-bold text-slate-800',
                    htmlContainer: 'text-sm font-semibold text-slate-600 mt-2'
                }
            });
        } else {
            alert(title + (text ? ": " + text : ""));
        }
    };
    // ข้อมูลงาน/แผนกวิชาจากฐานข้อมูลจริง
    const subDepartmentData = {
        resource: [],
        planning: [],
        student: [],
        academic: []
    };
    @if(isset($dbDepartments) && count($dbDepartments) > 0)
        @foreach($dbDepartments as $d)
            @php
                $grpKey = $d->group ?: ($d->getAttributes()['group'] ?? null);
                if (!$grpKey && !empty($d->department_group_id) && isset($dbDepartmentGroups)) {
                    $matchDg = $dbDepartmentGroups->firstWhere('id', $d->department_group_id);
                    $grpKey = $matchDg ? ($matchDg->group_code ?: $matchDg->group_name) : null;
                }
                $grpKey = $grpKey ?: 'resource';
            @endphp
            if (!subDepartmentData[{!! json_encode($grpKey) !!}]) subDepartmentData[{!! json_encode($grpKey) !!}] = [];
            if (!subDepartmentData[{!! json_encode($grpKey) !!}].includes({!! json_encode($d->department_name) !!})) {
                subDepartmentData[{!! json_encode($grpKey) !!}].push({!! json_encode($d->department_name) !!});
            }
        @endforeach
    @endif

    // 2. ฟังก์ชันจัดการปุ่ม Header ด้านบน และ Modal เพิ่มชนิด/ประเภททรัพย์สิน
        window.assetKindsData = {};
        @if(isset($dbAssetKinds) && count($dbAssetKinds) > 0)
            @foreach($dbAssetKinds as $ak)
                if (!window.assetKindsData[{!! json_encode($ak->category_code) !!}]) window.assetKindsData[{!! json_encode($ak->category_code) !!}] = [];
                if ({!! json_encode($ak->category_code) !!} !== {!! json_encode($ak->name) !!}) {
                    if (!window.assetKindsData[{!! json_encode($ak->category_code) !!}].includes({!! json_encode($ak->name) !!})) {
                        window.assetKindsData[{!! json_encode($ak->category_code) !!}].push({!! json_encode($ak->name) !!});
                    }
                }
            @endforeach
        @endif

        @if(isset($dbDeviceTypes) && count($dbDeviceTypes) > 0)
            @foreach($dbDeviceTypes as $dt)
                @if($dt->category)
                    if (!window.assetKindsData[{!! json_encode($dt->category) !!}]) window.assetKindsData[{!! json_encode($dt->category) !!}] = [];
                    if (!window.assetKindsData[{!! json_encode($dt->category) !!}].includes({!! json_encode($dt->name) !!})) {
                        window.assetKindsData[{!! json_encode($dt->category) !!}].push({!! json_encode($dt->name) !!});
                    }
                @endif
            @endforeach
        @endif
        
        @php
            $adminDbDevices = \App\Models\Device::select('category', 'name')->whereNotNull('category')->distinct()->get();
        @endphp
        @foreach($adminDbDevices as $dev)
            @if($dev->category && $dev->name)
                if (!window.assetKindsData[{!! json_encode($dev->category) !!}]) window.assetKindsData[{!! json_encode($dev->category) !!}] = [];
                if (!window.assetKindsData[{!! json_encode($dev->category) !!}].includes({!! json_encode($dev->name) !!})) {
                    window.assetKindsData[{!! json_encode($dev->category) !!}].push({!! json_encode($dev->name) !!});
                }
            @endif
        @endforeach

        // ข้อมูล device types แบบ keyed-by-kind สำหรับ auto-fill ยี่ห้อ/รุ่น
        window.assetDeviceTypesData = {};
        @if(isset($dbDeviceTypes) && count($dbDeviceTypes) > 0)
            @foreach($dbDeviceTypes as $dt)
                window.assetDeviceTypesData[{!! json_encode($dt->name) !!}] = {
                    brand: {!! json_encode($dt->brand ?? '') !!},
                    model: {!! json_encode($dt->model ?? '') !!},
                    details: {!! json_encode($dt->details ?? '') !!},
                    category: {!! json_encode($dt->category ?? '') !!}
                };
            @endforeach
        @endif

        // Populate Category Selectors dynamically on load
        document.addEventListener('DOMContentLoaded', function() {
            const categories = Object.keys(assetKindsData);
            
            const manageCat = document.getElementById('manage-category-select');
            const addKindCat = document.getElementById('add-kind-category-select');
            const basicCatFilter = document.getElementById('basic-category-filter');
            
            if (manageCat) {
                manageCat.innerHTML = '<option value="all">-- เลือกประเภททรัพย์สิน --</option>';
            }
            if (addKindCat) {
                addKindCat.innerHTML = '<option value="" disabled selected>เลือกประเภททรัพย์สิน</option>';
            }
            if (basicCatFilter) {
                basicCatFilter.innerHTML = '<option value="all">-- ทุกประเภททรัพย์สิน --</option>';
            }

            categories.forEach(cat => {
                if (manageCat) {
                    const opt = document.createElement('option');
                    opt.value = cat;
                    opt.textContent = cat;
                    manageCat.appendChild(opt);
                }
                if (addKindCat) {
                    const opt = document.createElement('option');
                    opt.value = cat;
                    opt.textContent = cat;
                    addKindCat.appendChild(opt);
                }
                if (basicCatFilter) {
                    const opt = document.createElement('option');
                    opt.value = cat;
                    opt.textContent = cat;
                    basicCatFilter.appendChild(opt);
                }
            });
            
            // Trigger initial render for "all" or current selected
            if (typeof updateKindTableByCategory === 'function') {
                const initVal = manageCat ? manageCat.value : 'all';
                updateKindTableByCategory(initVal);
            }

            if (typeof updateMainSubDeptTableByGroup === 'function') {
                const mainSel = document.getElementById('main-dept-group-select');
                updateMainSubDeptTableByGroup(mainSel ? mainSel.value : 'all');
            }
        });

    window.filterKindsTable = function() {
        const select = document.getElementById('manage-category-select');
        const catKey = select ? select.value : 'all';
        updateKindTableByCategory(catKey);
    };

    window.updateKindTableByCategory = function(catKey) {
        const tbody = document.getElementById('manage-kinds-tbody');
        const footerCount = document.getElementById('manage-kinds-footer-count');
        const section = document.getElementById('manage-kinds-section');
        if (!tbody) return;

        const searchInput = document.getElementById('manage-kinds-search');
        const searchKeyword = searchInput ? searchInput.value.toLowerCase() : '';

        let list = [];
        if (!catKey || catKey === 'all') {
            if (section) section.style.display = 'block';
            for (const category in assetKindsData) {
                assetKindsData[category].forEach(item => {
                    if (item.toLowerCase().includes(searchKeyword) || category.toLowerCase().includes(searchKeyword)) {
                        list.push({ name: item, catKey: category });
                    }
                });
            }
        } else {
            if (section) section.style.display = 'block';
            (assetKindsData[catKey] || []).forEach(item => {
                if (item.toLowerCase().includes(searchKeyword)) {
                    list.push({ name: item, catKey: catKey });
                }
            });
        }

        if (list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="p-8 text-center text-slate-400 font-medium">
                        ${searchKeyword ? 'ไม่พบข้อมูลที่ค้นหา' : 'ยังไม่มีข้อมูลชนิดทรัพย์สิน (คลิกปุ่ม "+ เพิ่มชนิดทรัพย์สิน" ด้านบนเพื่อเพิ่มรายการใหม่)'}
                    </td>
                </tr>
            `;
            if (footerCount) footerCount.textContent = 'แสดง 0 รายการ';
            return;
        }

        let html = '';
        list.forEach((itemObj, index) => {
            const item = itemObj.name;
            const itemCatKey = itemObj.catKey;
            html += `
                <tr class="hover:bg-slate-50/80 transition border-b border-slate-100">
                    <td class="p-3.5 pl-4 text-center font-bold text-slate-500">${index + 1}</td>
                    <td class="p-3.5 pl-4 font-bold text-slate-800">
                        ${item}
                    </td>
                    <td class="p-3.5 pr-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" onclick="editAssetKind('${item}', '${itemCatKey}')" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold cursor-pointer">
                                <i class="fa-solid fa-pen"></i> แก้ไข
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        if (footerCount) {
            footerCount.textContent = `แสดง 1 - ${list.length} จาก ${list.length} รายการ`;
        }
    };

    window.openManageBasicModal = function(actionType = 'manage') {
        const modal = document.getElementById('modal-manage-basic');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
        const select = document.getElementById('manage-category-select');
        const currentCat = select ? select.value : 'hardware';
        window.updateKindTableByCategory(currentCat);
    };

    window.handleBasicHeaderManage = function() {
        if (window.currentBasicSubTab === 'departments') {
            if (typeof openManageDepartmentsModal === 'function') openManageDepartmentsModal();
        } else {
            window.openManageBasicModal('manage');
        }
    };

    window.handleBasicHeaderAdd = function() {
        if (window.currentBasicSubTab === 'departments') {
            if (typeof openManageDepartmentsModal === 'function') openManageDepartmentsModal();
        } else if (window.currentBasicSubTab === 'buildings') {
            if (typeof window.openBuildingModal === 'function') window.openBuildingModal();
        } else if (window.currentBasicSubTab === 'personnel-types') {
            if (typeof openNewPositionModal === 'function') openNewPositionModal();
        } else {
            if (typeof window.openDeviceTypeModal === 'function') {
                window.openDeviceTypeModal();
            }
        }
    };

    window.closeManageBasicModal = function() {
        const modal = document.getElementById('modal-manage-basic');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    // Modal เพิ่มชนิดทรัพย์สิน (Image 1)
    window.openAddAssetKindModal = function() {
        const modal = document.getElementById('modal-add-asset-kind');
        if (modal) {
            const select = document.getElementById('manage-category-select');
            const addKindSelect = document.getElementById('add-kind-category-select');
            if (select && addKindSelect && select.value && select.value !== 'all') {
                addKindSelect.value = select.value;
            } else if (addKindSelect) {
                addKindSelect.selectedIndex = 0;
            }
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            if(typeof window.clearAddAssetKindError === 'function') window.clearAddAssetKindError();
        }
    };

    window.closeAddAssetKindModal = function() {
        const modal = document.getElementById('modal-add-asset-kind');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.clearAddAssetKindError = function(field) {
        if (!field) {
            ['category', 'name'].forEach(f => window.clearAddAssetKindError(f));
            return;
        }
        const selectId = field === 'category' ? 'add-kind-category-select' : 'add-kind-name-input';
        const input = document.getElementById(selectId);
        const errorText = document.getElementById(`add-kind-${field}-error`);
        if (input) {
            input.classList.remove('border-red-500', 'focus:border-red-500');
            if (field === 'category') {
                input.classList.add('border-slate-200', 'focus:border-blue-500');
            } else {
                input.classList.add('border-slate-200', 'focus:border-blue-500');
            }
        }
        if (errorText) errorText.classList.add('hidden');
    };

    window.submitAddAssetKindForm = function(event) {
        if (event) event.preventDefault();
        const catSelect = document.getElementById('add-kind-category-select');
        const nameInput = document.getElementById('add-kind-name-input');

        const cat = catSelect ? catSelect.value : '';
        const name = nameInput ? nameInput.value.trim() : '';

        window.clearAddAssetKindError();

        if (!cat) {
            if (catSelect) {
                catSelect.classList.remove('border-slate-200', 'focus:border-blue-500');
                catSelect.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('add-kind-category-error');
            if (err) err.classList.remove('hidden');
            return false;
        }

        if (!name) {
            if (nameInput) {
                nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                nameInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('add-kind-name-error');
            if (err) {
                err.innerText = 'กรุณากรอกชื่อชนิดทรัพย์สิน';
                err.classList.remove('hidden');
            }
            return false;
        }

        const catKey = catSelect.value;
        const kindName = nameInput.value.trim();

        if (assetKindsData[catKey] && assetKindsData[catKey].includes(kindName)) {
            if (nameInput) {
                nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                nameInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('add-kind-name-error');
            if (err) {
                err.innerText = 'มีชนิดทรัพย์สินนี้อยู่ในระบบแล้ว';
                err.classList.remove('hidden');
            }
            return false;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route("admin.asset-kinds.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                category_code: catKey,
                name: kindName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!assetKindsData[catKey]) assetKindsData[catKey] = [];
                assetKindsData[catKey].push(kindName);

                window.updateKindTableByCategory(catKey);

                const manageSelect = document.getElementById('manage-category-select');
                if (manageSelect) manageSelect.value = catKey;

                window.closeAddAssetKindModal();
                nameInput.value = '';
            } else {
                alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });

        return false;
    };

    // Modal เพิ่มประเภททรัพย์สิน
    window.openAddAssetCategoryModal = function() {
        const modal = document.getElementById('modal-add-asset-category');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            
            // clear form and errors
            const nameInput = document.getElementById('add-category-name-input');
            if (nameInput) {
                nameInput.value = '';
                if(typeof clearAddCategoryError === 'function') clearAddCategoryError();
            }
        }
    };
    
    window.clearAddCategoryError = function() {
        const nameInput = document.getElementById('add-category-name-input');
        const errorText = document.getElementById('add-category-name-error');
        if (nameInput) {
            nameInput.classList.remove('border-red-500', 'focus:border-red-500');
            nameInput.classList.add('border-slate-200', 'focus:border-blue-500');
        }
        if (errorText) errorText.classList.add('hidden');
    };

    window.closeAddAssetCategoryModal = function() {
        const modal = document.getElementById('modal-add-asset-category');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.submitAddAssetCategoryForm = function(event) {
        if (event) event.preventDefault();
        const nameInput = document.getElementById('add-category-name-input');
        const errorText = document.getElementById('add-category-name-error');
        
        if (!nameInput || !nameInput.value.trim()) {
            if (nameInput) {
                nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                nameInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            if (errorText) {
                errorText.innerText = 'กรุณากรอกชื่อประเภททรัพย์สิน';
                errorText.classList.remove('hidden');
            }
            return false;
        }

        const name = nameInput.value.trim();
        const catKey = name;

        if (assetKindsData[catKey] !== undefined) {
            if (nameInput) {
                nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                nameInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            if (errorText) {
                errorText.innerText = 'มีประเภททรัพย์สินนี้อยู่ในระบบแล้ว';
                errorText.classList.remove('hidden');
            }
            return false;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route("admin.asset-categories.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: name
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('manage-category-select');
                const catFilter = document.getElementById('basic-category-filter');
                const addKindSelect = document.getElementById('add-kind-category-select');

                if (select) {
                    const opt = document.createElement('option');
                    opt.value = catKey;
                    opt.textContent = name;
                    select.appendChild(opt);
                    select.value = catKey;
                }
                if (catFilter) {
                    const optFilter = document.createElement('option');
                    optFilter.value = catKey;
                    optFilter.textContent = name;
                    catFilter.appendChild(optFilter);
                }
                if (addKindSelect) {
                    const optKind = document.createElement('option');
                    optKind.value = catKey;
                    optKind.textContent = name;
                    addKindSelect.appendChild(optKind);
                }

                assetKindsData[catKey] = [];
                window.updateKindTableByCategory(catKey);
                window.closeAddAssetCategoryModal();
                nameInput.value = '';
            } else {
                alert(data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });

        return false;
    };

    window.editAssetCategory = function() {
        const select = document.getElementById('manage-category-select');
        if (!select || select.selectedIndex === -1) return;
        const currentOption = select.options[select.selectedIndex];
        const oldName = currentOption.text;

        const oldNameInput = document.getElementById('edit-category-old-name');
        if (oldNameInput) oldNameInput.value = oldName;

        const nameInput = document.getElementById('edit-category-name-input');
        if (nameInput) {
            nameInput.value = oldName;
            if(typeof clearEditCategoryError === 'function') clearEditCategoryError();
        }

        const modal = document.getElementById('modal-edit-asset-category');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            if (nameInput) setTimeout(() => nameInput.focus(), 100);
        }
    };

    window.clearEditCategoryError = function() {
        const nameInput = document.getElementById('edit-category-name-input');
        const errorText = document.getElementById('edit-category-name-error');
        if (nameInput) {
            nameInput.classList.remove('border-red-500', 'focus:border-red-500');
            nameInput.classList.add('border-slate-200', 'focus:border-blue-500');
        }
        if (errorText) errorText.classList.add('hidden');
    };

    window.closeEditAssetCategoryModal = function() {
        const modal = document.getElementById('modal-edit-asset-category');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.submitEditAssetCategoryForm = function(event) {
        if (event) event.preventDefault();
        const input = document.getElementById('edit-category-name-input');
        const errorText = document.getElementById('edit-category-name-error');
        const newName = input ? input.value.trim() : '';

        if (!newName) {
            if (input) {
                input.classList.remove('border-slate-200', 'focus:border-blue-500');
                input.classList.add('border-red-500', 'focus:border-red-500');
            }
            if (errorText) errorText.classList.remove('hidden');
            return false;
        }

        const select = document.getElementById('manage-category-select');
        if (select && select.selectedIndex !== -1) {
            const currentOption = select.options[select.selectedIndex];
            const oldName = currentOption.value; // Our value is the actual name now

            if (oldName === newName) {
                window.closeEditAssetCategoryModal();
                return false;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('{{ route("admin.asset-categories.rename") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    old_name: oldName,
                    new_name: newName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update assetKindsData dictionary key
                    assetKindsData[newName] = assetKindsData[oldName];
                    delete assetKindsData[oldName];

                    // Helper to update dropdowns
                    const updateDropdown = (id) => {
                        const dd = document.getElementById(id);
                        if (dd) {
                            for (let i = 0; i < dd.options.length; i++) {
                                if (dd.options[i].value === oldName) {
                                    dd.options[i].value = newName;
                                    dd.options[i].text = newName;
                                    break;
                                }
                            }
                        }
                    };

                    updateDropdown('manage-category-select');
                    updateDropdown('basic-category-filter');
                    updateDropdown('add-kind-category-select');
                    updateDropdown('device-type-category-select');

                    // Update rows data-category attribute
                    const rows = document.querySelectorAll('#basic-data-table-body tr.device-type-row');
                    rows.forEach(row => {
                        if (row.getAttribute('data-category') === oldName) {
                            row.setAttribute('data-category', newName);
                        }
                    });

                    window.updateKindTableByCategory(newName);
                    window.closeEditAssetCategoryModal();
                    
                    if (typeof showAdminToast === 'function') {
                        showAdminToast("สำเร็จ", `เปลี่ยนชื่อประเภททรัพย์สินเป็น "${newName}" เรียบร้อยแล้ว`);
                    }
                } else {
                    alert('เกิดข้อผิดพลาดในการเปลี่ยนชื่อ');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            });
        }
        return false;
    };

    window.deleteAssetCategory = function() {
        const select = document.getElementById('manage-category-select');
        if (!select || select.options.length <= 1) {
            if (typeof showAdminToast === 'function') {
                showAdminToast("คำเตือน", "ไม่สามารถลบประเภททรัพย์สินสุดท้ายได้", "warning");
            } else {
                alert('ไม่สามารถลบประเภททรัพย์สินสุดท้ายได้');
            }
            return;
        }
        const currentOption = select.options[select.selectedIndex];
        const catName = currentOption.text;
        const catKey = currentOption.value;

        const confirmCallback = () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('{{ route("admin.asset-categories.destroy") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    name: catKey
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    delete assetKindsData[catKey];
                    
                    const removeDropdownOption = (id) => {
                        const dd = document.getElementById(id);
                        if (dd) {
                            for (let i = 0; i < dd.options.length; i++) {
                                if (dd.options[i].value === catKey) {
                                    dd.remove(i);
                                    break;
                                }
                            }
                        }
                    };

                    removeDropdownOption('manage-category-select');
                    removeDropdownOption('basic-category-filter');
                    removeDropdownOption('add-kind-category-select');
                    removeDropdownOption('device-type-category-select');

                    // Delete table rows associated with this category
                    const rows = document.querySelectorAll('#basic-data-table-body tr.device-type-row');
                    rows.forEach(row => {
                        if (row.getAttribute('data-category') === catKey) {
                            row.remove();
                        }
                    });

                    // Update filters
                    if (typeof filterBasicDataItems === 'function') {
                        filterBasicDataItems();
                    }

                    if (select.options.length > 1) {
                        select.selectedIndex = 1;
                        window.updateKindTableByCategory(select.value);
                    } else {
                        window.updateKindTableByCategory('');
                    }

                    if (typeof showAdminToast === 'function') {
                        showAdminToast("สำเร็จ", `ลบประเภททรัพย์สิน "${catName}" เรียบร้อยแล้ว`);
                    }
                } else {
                    alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            });
        };

        if (typeof openAdminCustomModal === 'function') {
            openAdminCustomModal("ยืนยันการลบ", `คุณต้องการลบประเภททรัพย์สิน "${catName}" ใช่หรือไม่?`, confirmCallback);
        } else if (confirm(`คุณต้องการลบประเภททรัพย์สิน "${catName}" ใช่หรือไม่?`)) {
            confirmCallback();
        }
    };

    window.editAssetKind = function(oldName, catKey) {
        let hiddenCat = document.getElementById('edit-kind-cat-key');
        if (!hiddenCat) {
            hiddenCat = document.createElement('input');
            hiddenCat.type = 'hidden';
            hiddenCat.id = 'edit-kind-cat-key';
            const modal = document.getElementById('modal-edit-asset-kind');
            if(modal) modal.appendChild(hiddenCat);
        }
        if (hiddenCat) hiddenCat.value = catKey;

        const select = document.getElementById('edit-kind-category-select');
        if (select) {
            select.innerHTML = '<option value="" disabled class="text-slate-300">เลือกประเภททรัพย์สิน</option>';
            for (const category in assetKindsData) {
                const opt = document.createElement('option');
                opt.value = category;
                opt.textContent = category;
                if (category === catKey) opt.selected = true;
                select.appendChild(opt);
            }
            select.disabled = true;
            select.classList.add('bg-slate-50', 'cursor-not-allowed', 'opacity-70');
        }

        const oldNameInput = document.getElementById('edit-kind-old-name');
        if (oldNameInput) oldNameInput.value = oldName;

        const nameInput = document.getElementById('edit-kind-name-input');
        if (nameInput) nameInput.value = oldName;

        const modal = document.getElementById('modal-edit-asset-kind');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            if (nameInput) setTimeout(() => nameInput.focus(), 100);
        }
    };

    window.closeEditAssetKindModal = function() {
        const modal = document.getElementById('modal-edit-asset-kind');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.submitEditAssetKindForm = function(event) {
        if (event) event.preventDefault();
        const oldName = document.getElementById('edit-kind-old-name') ? document.getElementById('edit-kind-old-name').value : '';
        const input = document.getElementById('edit-kind-name-input');
        const newName = input ? input.value.trim() : '';

        if (!newName || oldName === newName) return false;

        const hiddenCat = document.getElementById('edit-kind-cat-key');
        const catKey = hiddenCat ? hiddenCat.value : '';
        if (!catKey) return false;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route("admin.asset-kinds.rename-by-name") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                category_code: catKey,
                old_name: oldName,
                new_name: newName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof assetKindsData !== 'undefined' && assetKindsData[catKey]) {
                    const idx = assetKindsData[catKey].indexOf(oldName);
                    if (idx !== -1) assetKindsData[catKey][idx] = newName;
                }

                if (typeof window.updateKindTableByCategory === 'function') {
                    window.updateKindTableByCategory(catKey);
                }

                // Update basic-type-filter if currently active
                const mainCatFilter = document.getElementById('basic-category-filter');
                if (mainCatFilter && mainCatFilter.value === catKey) {
                    if (typeof window.updateBasicTypeFilterDropdown === 'function') {
                        window.updateBasicTypeFilterDropdown(catKey);
                        if (typeof filterBasicDataItems === 'function') filterBasicDataItems();
                    }
                }

                // Also update the main table's data-kind attributes
                const rows = document.querySelectorAll('#basic-data-table-body tr.device-type-row');
                rows.forEach(row => {
                    if (row.getAttribute('data-category') === catKey && row.getAttribute('data-kind') === oldName) {
                        row.setAttribute('data-kind', newName);
                    }
                });

                window.closeEditAssetKindModal();
                if (typeof showAdminToast === 'function') {
                    showAdminToast("สำเร็จ", `เปลี่ยนชื่อชนิดทรัพย์สินเป็น "${newName}" เรียบร้อยแล้ว`);
                }
            } else {
                alert('เกิดข้อผิดพลาดในการแก้ไขข้อมูล');
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });

        return false;
    };

    window.deleteAssetKind = function(name) {
        if (confirm(`คุณต้องการลบชนิดทรัพย์สิน "${name}" ใช่หรือไม่?`)) {
            const select = document.getElementById('manage-category-select');
            const catKey = select ? select.value : '';
            if (!catKey) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('{{ route("admin.asset-kinds.destroy-by-name") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    category_code: catKey,
                    name: name
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (assetKindsData[catKey]) {
                        assetKindsData[catKey] = assetKindsData[catKey].filter(k => k !== name);
                    }
                    window.updateKindTableByCategory(catKey);

                    // Update basic-type-filter if currently active
                    const mainCatFilter = document.getElementById('basic-category-filter');
                    if (mainCatFilter && mainCatFilter.value === catKey) {
                        if (typeof window.updateBasicTypeFilterDropdown === 'function') {
                            window.updateBasicTypeFilterDropdown(catKey);
                            if (typeof filterBasicDataItems === 'function') filterBasicDataItems();
                        }
                    }

                    // Delete table rows associated with this kind
                    const rows = document.querySelectorAll('#basic-data-table-body tr.device-type-row');
                    rows.forEach(row => {
                        if (row.getAttribute('data-category') === catKey && row.getAttribute('data-kind') === name) {
                            row.remove();
                        }
                    });

                    if (typeof showAdminToast === 'function') {
                        showAdminToast("สำเร็จ", `ลบชนิดทรัพย์สิน "${name}" เรียบร้อยแล้ว`);
                    } else {
                        alert(`ลบชนิดทรัพย์สิน "${name}" เรียบร้อยแล้ว`);
                    }
                } else {
                    alert('เกิดข้อผิดพลาดในการลบข้อมูล');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            });
        }
    };

    window.openManageDepartmentsModal = function() {
        const modal = document.getElementById('modal-manage-departments');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            window.updateSubDeptTableByGroup('all');
        }
    };

    window.closeManageDepartmentsModal = function() {
        const modal = document.getElementById('modal-manage-departments');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.updateMainSubDeptTableByGroup = function(groupKey) {
        const tbody = document.getElementById('basic-departments-table-body');
        const footerCount = document.getElementById('main-subdept-footer-count');
        if (!tbody) return;

        tbody.innerHTML = '';
        const groupSel = document.getElementById('main-dept-group-select');
        const selectedKey = (typeof groupKey !== 'undefined' && groupKey !== null) ? groupKey : (groupSel ? groupSel.value : '');

        if (!selectedKey || selectedKey === 'none') {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="p-8 text-center text-slate-400 font-medium">
                        กรุณาเลือกสังกัด / ฝ่ายงาน เพื่อดูข้อมูลงาน / แผนกวิชา
                    </td>
                </tr>
            `;
            if (footerCount) footerCount.textContent = 'แสดง 0 รายการ';
            return;
        }

        let list = [];
        const groupMapNames = {
            'resource': 'ฝ่ายบริหารทรัพยากร',
            'planning': 'ฝ่ายแผนงานและความร่วมมือ',
            'student': 'ฝ่ายพัฒนากิจการนักเรียน นักศึกษา',
            'academic': 'ฝ่ายวิชาการ'
        };
        groupMapNames['ฝ่ายนโยบายและแผน'] = 'ฝ่ายนโยบายและแผน';
        groupMapNames['ฝ่ายพัฒนานักศึกษาและศิษย์เก่า'] = 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า';

        @if(isset($dbDepartmentGroups) && count($dbDepartmentGroups) > 0)
            @foreach($dbDepartmentGroups as $dg)
                @if($dg->group_code)
                    groupMapNames[{!! json_encode($dg->group_code) !!}] = {!! json_encode($dg->group_name) !!};
                @endif
                groupMapNames[{!! json_encode((string)$dg->id) !!}] = {!! json_encode($dg->group_name) !!};
                groupMapNames[{!! json_encode($dg->group_name) !!}] = {!! json_encode($dg->group_name) !!};
            @endforeach
        @endif

        if (typeof subDepartmentData !== 'undefined' && subDepartmentData) {
            if (selectedKey === 'all') {
                Object.keys(subDepartmentData).forEach(g => {
                    (subDepartmentData[g] || []).forEach(deptName => {
                        list.push({ group: g, groupName: groupMapNames[g] || g, name: deptName });
                    });
                });
            } else {
                let matchedKeys = [selectedKey];
                for (const [k, v] of Object.entries(groupMapNames)) {
                    if (k === selectedKey || v === selectedKey) {
                        if (!matchedKeys.includes(k)) matchedKeys.push(k);
                        if (!matchedKeys.includes(v)) matchedKeys.push(v);
                    }
                }

                let foundNames = new Set();
                matchedKeys.forEach(k => {
                    if (subDepartmentData[k]) {
                        subDepartmentData[k].forEach(deptName => {
                            if (!foundNames.has(deptName)) {
                                foundNames.add(deptName);
                                list.push({ group: k, groupName: groupMapNames[k] || groupMapNames[selectedKey] || selectedKey, name: deptName });
                            }
                        });
                    }
                });
            }
        }

        if (list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="p-6 text-center text-slate-400 font-medium">
                        ไม่พบข้อมูลงาน / แผนกวิชาในฝ่ายนี้
                    </td>
                </tr>
            `;
            if (footerCount) footerCount.textContent = 'แสดง 0 รายการ';
            return;
        }

        list.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition border-b border-slate-100';
            tr.innerHTML = `
                <td class="p-3.5 pl-4 w-16 text-center font-mono text-slate-500">${index + 1}</td>
                <td class="p-3.5 font-bold text-slate-800">${item.name}</td>
                <td class="p-3.5 pr-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <button type="button" onclick="editSubDeptFromManage('${item.group}', '${item.name}')" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold cursor-pointer">
                            <i class="fa-solid fa-pen"></i> แก้ไข
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (footerCount) {
            footerCount.textContent = `แสดง 1 - ${list.length} จาก ${list.length} รายการ`;
        }
    };
    window.updateSubDeptTableByGroup = window.updateMainSubDeptTableByGroup;

    window.openAddDeptGroupModal = function() {
        const modal = document.getElementById('modal-add-dept-group');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    };

    window.closeAddDeptGroupModal = function() {
        const modal = document.getElementById('modal-add-dept-group');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.submitAddDeptGroupForm = function(event) {
        if (event) event.preventDefault();
        const nameInput = document.getElementById('new-dept-group-name') || document.getElementById('add-dept-group-name-input');
        const name = nameInput ? nameInput.value.trim() : '';
        const errorMsg = document.getElementById('new-dept-group-error');
        
        if (!name) {
            if (nameInput) nameInput.classList.add('border-red-500');
            if (errorMsg) errorMsg.classList.remove('hidden');
            return false;
        }

        let isDuplicateGroup = false;
        const mainSelectOptions = document.getElementById('main-dept-group-select')?.options || [];
        for (let i = 0; i < mainSelectOptions.length; i++) {
            if (mainSelectOptions[i].text.trim() === name) {
                isDuplicateGroup = true;
                break;
            }
        }
        if (isDuplicateGroup) {
            if (nameInput) nameInput.classList.add('border-red-500');
            if (errorMsg) {
                errorMsg.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>มีชื่อฝ่ายนี้อยู่ในระบบแล้ว';
                errorMsg.classList.remove('hidden');
            }
            return false;
        }

        const groupKey = 'group_' + Date.now();

        // เพิ่มใน dropdown ของ main tab
        const mainSelect = document.getElementById('main-dept-group-select');
        if (mainSelect) {
            const opt = document.createElement('option');
            opt.value = groupKey;
            opt.textContent = name;
            mainSelect.appendChild(opt);
        }

        // เพิ่มใน dropdown ของ modal จัดการ
        const manageSelect = document.getElementById('manage-dept-group-select');
        if (manageSelect) {
            const opt2 = document.createElement('option');
            opt2.value = groupKey;
            opt2.textContent = name;
            manageSelect.appendChild(opt2);
            manageSelect.value = groupKey;
        }

        // เพิ่มใน dropdown ของ modal เพิ่มงาน/แผนกวิชา
        const subDeptGroupSelect = document.getElementById('new-sub-dept-group');
        if (subDeptGroupSelect) {
            const opt3 = document.createElement('option');
            opt3.value = groupKey;
            opt3.textContent = name;
            subDeptGroupSelect.appendChild(opt3);
        }

        if (typeof subDepartmentData !== 'undefined') {
            subDepartmentData[groupKey] = [];
        }

        window.closeAddDeptGroupModal();
        if (nameInput) nameInput.value = '';
        if (typeof window.updateSubDeptTableByGroup === 'function') window.updateSubDeptTableByGroup(groupKey);

        if (typeof showAdminToast === 'function') {
            showAdminToast("สำเร็จ", `เพิ่มสังกัด/ฝ่ายงาน "${name}" เรียบร้อยแล้ว`);
        }
        return false;
    };

    // Modal เพิ่มงาน/แผนกวิชา
    window.openNewSubDeptModal = function() {
        const modal = document.getElementById('modal-add-sub-dept');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');

            // sync ค่า dropdown ฝ่ายงาน จาก main select
            const mainSel = document.getElementById('main-dept-group-select');
            const subDeptGroupSel = document.getElementById('new-sub-dept-group');
            if (mainSel && subDeptGroupSel && mainSel.value) {
                subDeptGroupSel.value = mainSel.value;
            }

            const nameInput = document.getElementById('new-sub-dept-name');
            if (nameInput) {
                nameInput.value = '';
                setTimeout(() => nameInput.focus(), 100);
            }
        }
    };

    window.closeNewSubDeptModal = function() {
        const modal = document.getElementById('modal-add-sub-dept');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        const nameInput = document.getElementById('new-sub-dept-name');
        if (nameInput) nameInput.value = '';
    };

    window.clearAddSubDeptError = function() {
        const groupSelect = document.getElementById('new-sub-dept-group');
        const mainError = document.getElementById('add-sub-dept-main-error');
        if (groupSelect) groupSelect.classList.remove('border-red-500');
        if (mainError) mainError.classList.add('hidden');

        const nameInput = document.getElementById('new-sub-dept-name');
        const nameError = document.getElementById('new-sub-dept-error');
        if (nameInput) nameInput.classList.remove('border-red-500');
        if (nameError) nameError.classList.add('hidden');
    };

    window.saveNewSubDept = function(event) {
        if (event) event.preventDefault();
        const groupSelect = document.getElementById('new-sub-dept-group');
        const nameInput = document.getElementById('new-sub-dept-name');

        const groupKey = groupSelect ? groupSelect.value : '';
        const deptName = nameInput ? nameInput.value.trim() : '';

        if (!groupKey) {
            const errSelect = document.getElementById('new-sub-dept-group');
            const errMainMsg = document.getElementById('add-sub-dept-main-error');
            if (errSelect) errSelect.classList.add('border-red-500');
            if (errMainMsg) errMainMsg.classList.remove('hidden');
            return false;
        }

        if (!deptName) {
            const errInput = document.getElementById('new-sub-dept-name');
            const errMsg = document.getElementById('new-sub-dept-error');
            if (errInput) errInput.classList.add('border-red-500');
            if (errMsg) {
                errMsg.innerHTML = 'กรุณาระบุชื่อแผนก';
                errMsg.classList.remove('hidden');
            }
            return false;
        }

        let isDuplicateSubDept = false;
        if (typeof subDepartmentData !== 'undefined') {
            for (const key in subDepartmentData) {
                if (subDepartmentData[key].includes(deptName)) {
                    isDuplicateSubDept = true;
                    break;
                }
            }
        }
        if (isDuplicateSubDept) {
            const errInput = document.getElementById('new-sub-dept-name');
            const errMsg = document.getElementById('new-sub-dept-error');
            if (errInput) errInput.classList.add('border-red-500');
            if (errMsg) {
                errMsg.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>มีงาน/แผนก นี้อยู่ในระบบแล้ว';
                errMsg.classList.remove('hidden');
            }
            return false;
        }

        // บันทึกลง subDepartmentData
        if (typeof subDepartmentData !== 'undefined') {
            if (!subDepartmentData[groupKey]) subDepartmentData[groupKey] = [];
            subDepartmentData[groupKey].push(deptName);
        }

        // อัปเดตตารางใน main tab
        if (typeof window.updateMainSubDeptTableByGroup === 'function') {
            const mainSel = document.getElementById('main-dept-group-select');
            window.updateMainSubDeptTableByGroup(mainSel ? mainSel.value : groupKey);
        }

        window.closeNewSubDeptModal();
        if (typeof showAdminToast === 'function') {
            showAdminToast('สำเร็จ', `เพิ่ม "${deptName}" เรียบร้อยแล้ว`);
        }
        return false;
    };

    window.editDeptGroup = function() {
        const select = document.getElementById('main-dept-group-select') || document.getElementById('manage-dept-group-select');
        if (!select || !select.value) {
            return;
        }
        const currentOption = select.options[select.selectedIndex];
        const oldName = currentOption ? currentOption.text : '';
        if (!oldName || oldName.includes('--')) {
            return;
        }

        const oldInput = document.getElementById('edit-dept-group-old-name');
        if (oldInput) oldInput.value = oldName;

        const nameInput = document.getElementById('edit-dept-group-name-input');
        if (nameInput) {
            nameInput.value = oldName;
            nameInput.classList.remove('border-red-500');
            nameInput.addEventListener('input', function() {
                this.classList.remove('border-red-500');
                const err = document.getElementById('edit-dept-group-error');
                if (err) err.classList.add('hidden');
            }, { once: false });
        }

        const modal = document.getElementById('modal-edit-dept-group');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            if (nameInput) setTimeout(() => nameInput.focus(), 100);
        }
    };

    window.closeEditDeptGroupModal = function() {
        const modal = document.getElementById('modal-edit-dept-group');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.submitEditDeptGroupForm = function(event) {
        if (event) event.preventDefault();
        const input = document.getElementById('edit-dept-group-name-input');
        const newName = input ? input.value.trim() : '';
        if (!newName) return false;

        let isDuplicateGroupEdit = false;
        const mainSelectOptionsEdit = document.getElementById('main-dept-group-select')?.options || [];
        const oldNameStr = document.getElementById('edit-dept-group-old-name') ? document.getElementById('edit-dept-group-old-name').value : '';
        for (let i = 0; i < mainSelectOptionsEdit.length; i++) {
            if (mainSelectOptionsEdit[i].text.trim() === newName && newName !== oldNameStr.trim()) {
                isDuplicateGroupEdit = true;
                break;
            }
        }
        if (isDuplicateGroupEdit) {
            if (input) input.classList.add('border-red-500');
            let errorMsg = document.getElementById('edit-dept-group-error');
            if (!errorMsg) {
                errorMsg = document.createElement('p');
                errorMsg.id = 'edit-dept-group-error';
                errorMsg.className = 'text-red-500 text-[10px] mt-1 hidden';
                if (input) input.parentNode.appendChild(errorMsg);
            }
            if (errorMsg) {
                errorMsg.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>มีชื่อฝ่ายนี้อยู่ในระบบแล้ว';
                errorMsg.classList.remove('hidden');
            }
            return false;
        }

        const selectMain = document.getElementById('main-dept-group-select');
        if (selectMain && selectMain.selectedIndex >= 0) {
            const currentOpt = selectMain.options[selectMain.selectedIndex];
            if (currentOpt && currentOpt.value) currentOpt.text = newName;
        }

        const selectManage = document.getElementById('manage-dept-group-select');
        if (selectManage && selectManage.selectedIndex >= 0) {
            const currentOpt = selectManage.options[selectManage.selectedIndex];
            if (currentOpt && currentOpt.value) currentOpt.text = newName;
        }

        if (typeof window.updateMainSubDeptTableByGroup === 'function') {
            const sel = document.getElementById('main-dept-group-select');
            window.updateMainSubDeptTableByGroup(sel ? sel.value : '');
        }

        window.closeEditDeptGroupModal();
        if (typeof showAdminToast === 'function') {
            showAdminToast("สำเร็จ", `เปลี่ยนชื่อสังกัด/ฝ่ายงานเป็น "${newName}" เรียบร้อยแล้ว`);
        }
        return false;
    };

    window.deleteDeptGroup = function() {
        const select = document.getElementById('manage-dept-group-select');
        if (!select || select.selectedIndex <= 0 || select.value === 'all') {
            if (typeof showAdminToast === 'function') {
                showAdminToast("คำเตือน", "กรุณาเลือกสังกัด/ฝ่ายงานที่ต้องการลบ", "warning");
            }
            return;
        }
        const currentOption = select.options[select.selectedIndex];
        const groupKey = select.value;
        const catName = currentOption.text;

        const confirmCallback = () => {
            if (typeof subDepartmentData !== 'undefined') {
                delete subDepartmentData[groupKey];
            }
            select.remove(select.selectedIndex);
            select.value = 'all';
            window.updateSubDeptTableByGroup('all');
            if (typeof showAdminToast === 'function') {
                showAdminToast("สำเร็จ", `ลบสังกัด/ฝ่ายงาน "${catName}" เรียบร้อยแล้ว`);
            }
        };

        if (typeof openAdminCustomModal === 'function') {
            openAdminCustomModal("ยืนยันการลบสังกัด/ฝ่ายงาน", `คุณต้องการลบสังกัด/ฝ่ายงาน "${catName}" ใช่หรือไม่?`, confirmCallback);
        } else if (confirm(`คุณต้องการลบสังกัด/ฝ่ายงาน "${catName}" ใช่หรือไม่?`)) {
            confirmCallback();
        }
    };

    window.editSubDeptFromManage = function(group, oldName) {
        const groupInput = document.getElementById('edit-subdept-group-key');
        if (groupInput) groupInput.value = group;

        const oldNameInput = document.getElementById('edit-subdept-old-name');
        if (oldNameInput) oldNameInput.value = oldName;

        const input = document.getElementById('edit-subdept-name-input');
        if (input) {
            input.value = oldName;
            input.classList.remove('border-red-500');
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
                const err = document.getElementById('edit-subdept-error');
                if (err) err.classList.add('hidden');
            }, { once: false });
        }

        const modal = document.getElementById('modal-edit-sub-dept');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            if (input) setTimeout(() => input.focus(), 100);
        }
    };

    window.closeEditSubDeptModal = function() {
        const modal = document.getElementById('modal-edit-sub-dept');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.submitEditSubDeptForm = function(event) {
        if (event) event.preventDefault();
        const group = document.getElementById('edit-subdept-group-key') ? document.getElementById('edit-subdept-group-key').value : '';
        const oldName = document.getElementById('edit-subdept-old-name') ? document.getElementById('edit-subdept-old-name').value : '';
        const input = document.getElementById('edit-subdept-name-input');
        const newName = input ? input.value.trim() : '';

        if (!newName) return false;

        let isDuplicateSubDeptEdit = false;
        if (typeof subDepartmentData !== 'undefined') {
            for (const key in subDepartmentData) {
                if (subDepartmentData[key].includes(newName) && newName !== oldName) {
                    isDuplicateSubDeptEdit = true;
                    break;
                }
            }
        }
        if (isDuplicateSubDeptEdit) {
            if (input) input.classList.add('border-red-500');
            let errorMsg = document.getElementById('edit-subdept-error');
            if (!errorMsg) {
                errorMsg = document.createElement('p');
                errorMsg.id = 'edit-subdept-error';
                errorMsg.className = 'text-red-500 text-[10px] mt-1 hidden';
                if (input) input.parentNode.appendChild(errorMsg);
            }
            if (errorMsg) {
                errorMsg.innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i>มีงาน/แผนก นี้อยู่ในระบบแล้ว';
                errorMsg.classList.remove('hidden');
            }
            return false;
        }

        if (typeof subDepartmentData !== 'undefined' && subDepartmentData[group]) {
            const idx = subDepartmentData[group].indexOf(oldName);
            if (idx !== -1) {
                subDepartmentData[group][idx] = newName;
            }
        }

        const select = document.getElementById('manage-dept-group-select');
        window.updateSubDeptTableByGroup(select ? select.value : 'all');
        if (typeof renderDepartmentsList === 'function') renderDepartmentsList();

        window.closeEditSubDeptModal();
        if (typeof playAuditSuccessChime === 'function') playAuditSuccessChime();
        if (typeof showAdminToast === 'function') {
            showAdminToast("สำเร็จ", `แก้ไขเป็น "${newName}" เรียบร้อยแล้ว`);
        }
        return false;
    };

    window.handleCategoryFilterChange = function(catKey) {
        window.updateBasicTypeFilterDropdown(catKey);
        filterBasicDataItems();
    };

    window.updateBasicTypeFilterDropdown = function(catKey) {
        const typeSelect = document.getElementById('basic-type-filter');
        if (!typeSelect) return;
        typeSelect.innerHTML = '<option value="all">-- เลือกชนิดทรัพย์สิน --</option>';
        if (catKey === 'all') return;
        const kindsList = (typeof assetKindsData !== 'undefined' && assetKindsData[catKey]) ? assetKindsData[catKey] : [];
        kindsList.forEach(kind => {
            const opt = document.createElement('option');
            opt.value = kind;
            opt.textContent = kind;
            typeSelect.appendChild(opt);
        });
    };

    // Modal เพิ่มประเภททรัพย์สิน
    window.updateDeviceTypeKindDropdown = function(catKey) {
        const kindSelect = document.getElementById('device-type-kind-select');
        if (!kindSelect) return;
        kindSelect.innerHTML = '';
        const kindsList = (typeof assetKindsData !== 'undefined' && assetKindsData[catKey]) ? assetKindsData[catKey] : [];
        if (!catKey || kindsList.length === 0) {
            kindSelect.innerHTML = '<option value="">-- กรุณาเลือกหรือเพิ่มชนิดทรัพย์สินในจัดการก่อน --</option>';
            kindSelect.className = kindSelect.className.replace('text-slate-800', 'text-slate-400');
            return;
        }
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '-- เลือกชนิดทรัพย์สิน --';
        kindSelect.appendChild(placeholder);
        kindsList.forEach(kind => {
            const opt = document.createElement('option');
            opt.value = kind;
            opt.textContent = kind;
            kindSelect.appendChild(opt);
        });
        kindSelect.classList.remove('text-slate-400');
        kindSelect.classList.add('text-slate-800');
    };

    window.openDeviceTypeModal = function() {
        const modal = document.getElementById('modal-add-device-type');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            // Sync categories from manage modal
            const catSelect = document.getElementById('device-type-category-select');
            const manageCatSelect = document.getElementById('manage-category-select');
            if (catSelect && manageCatSelect) {
                while (catSelect.options.length > 1) {
                    catSelect.remove(1);
                }
                for (let i = 1; i < manageCatSelect.options.length; i++) {
                    const opt = manageCatSelect.options[i];
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.text = opt.text;
                    catSelect.appendChild(newOpt);
                }
                catSelect.value = '';
            }
            window.updateDeviceTypeKindDropdown('');
            // Reset text fields
            const brandInput = document.getElementById('device-type-brand-input');
            if (brandInput) brandInput.value = '';
            const modelInput = document.getElementById('device-type-model-input');
            if (modelInput) modelInput.value = '';
            const descInput = document.getElementById('device-type-desc-input');
            if (descInput) descInput.value = '';
        }
    };

    window.closeDeviceTypeModal = function() {
        const modal = document.getElementById('modal-add-device-type');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        const form = document.getElementById('new-device-type-form');
        if (form) form.reset();
        
        ['category', 'kind', 'brand', 'model'].forEach(field => clearDeviceTypeError(field));
    };

    window.clearDeviceTypeError = function(field) {
        const inputMap = {
            'category': 'device-type-category-select',
            'kind': 'device-type-kind-select',
            'brand': 'device-type-brand-input',
            'model': 'device-type-model-input'
        };
        const id = inputMap[field];
        const input = document.getElementById(id);
        const err = document.getElementById(id.replace('-select', '-error').replace('-input', '-error'));
        
        if (input) {
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-slate-200', 'focus:border-emerald-500');
        }
        if (err) err.classList.add('hidden');
    };

    window.submitAddDeviceTypeForm = function(event) {
        if (event) event.preventDefault();
        const catSelect = document.getElementById('device-type-category-select');
        const kindSelect = document.getElementById('device-type-kind-select');
        const brandInput = document.getElementById('device-type-brand-input');
        const modelInput = document.getElementById('device-type-model-input');
        const descInput = document.getElementById('device-type-desc-input');

        const cat = catSelect ? catSelect.value : '';
        const kind = kindSelect ? kindSelect.value : '';
        const brand = brandInput ? brandInput.value.trim() : '';
        const model = modelInput ? modelInput.value.trim() : '';
        const desc = descInput ? descInput.value.trim() : '';

        ['category', 'kind', 'brand', 'model'].forEach(field => clearDeviceTypeError(field));

        if (!cat) {
            if (catSelect) {
                catSelect.classList.remove('border-slate-200', 'focus:border-emerald-500');
                catSelect.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('device-type-category-error');
            if (err) err.classList.remove('hidden');
            return false;
        }

        if (!kind) {
            if (kindSelect) {
                kindSelect.classList.remove('border-slate-200', 'focus:border-emerald-500');
                kindSelect.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('device-type-kind-error');
            if (err) err.classList.remove('hidden');
            return false;
        }

        if (!brand) {
            if (brandInput) {
                brandInput.classList.remove('border-slate-200', 'focus:border-emerald-500');
                brandInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('device-type-brand-error');
            if (err) err.classList.remove('hidden');
            return false;
        }

        if (!model) {
            if (modelInput) {
                modelInput.classList.remove('border-slate-200', 'focus:border-emerald-500');
                modelInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('device-type-model-error');
            if (err) err.classList.remove('hidden');
            return false;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route("admin.device-types.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                category: cat,
                name: kind,
                brand: brand,
                model: model,
                details: desc
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dt = data.data;
                const tbody = document.getElementById('basic-data-table-body');
                if (tbody) {
                    const newIndex = tbody.querySelectorAll('tr.device-type-row').length + 1;
                    const tr = document.createElement('tr');
                    tr.id = `device-type-row-${dt.id}`;
                    tr.className = 'device-type-row hover:bg-slate-50/80 transition border-b border-slate-100';
                    tr.setAttribute('data-category', cat);
                    tr.setAttribute('data-kind', kind);
                    tr.innerHTML = `
                        <td class="p-4 text-center text-slate-500 font-mono">${newIndex}</td>
                        <td class="p-4 font-bold text-slate-800">${brand}</td>
                        <td class="p-4 text-slate-700 font-medium">${model}</td>
                        <td class="p-4 text-slate-600 max-w-xs truncate">${desc || '-'}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="editDeviceType(${dt.id})" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold"><i class="fa-solid fa-pen"></i> แก้ไข</button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                    
                    if (typeof filterBasicDataItems === 'function') {
                        filterBasicDataItems();
                    }
                }

                window.closeDeviceTypeModal();
                if (typeof showAdminToast === 'function') {
                    showAdminToast('สำเร็จ', `เพิ่มประเภททรัพย์สิน "${brand} ${model}" เรียบร้อยแล้ว`);
                }
            } else {
                alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });
        
        return false;
    };

    // Modal เพิ่มอาคารใหม่
    window.openBuildingModal = function() {
        const modal = document.getElementById('modal-add-building');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            const codeInput = document.getElementById('building-code-input');
            if (codeInput) {
                codeInput.value = '';
                setTimeout(() => codeInput.focus(), 100);
            }
            if(typeof window.clearAddBuildingError === 'function') window.clearAddBuildingError();
        }
    };

    window.closeBuildingModal = function() {
        const modal = document.getElementById('modal-add-building');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        const form = document.getElementById('new-building-form');
        if (form) form.reset();
    };

    window.clearAddBuildingError = function(field) {
        if (!field) {
            ['code', 'name', 'floors'].forEach(f => window.clearAddBuildingError(f));
            return;
        }
        const input = document.getElementById(`building-${field}-input`);
        const errorText = document.getElementById(`building-${field}-error`);
        if (input) {
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-slate-200', 'focus:border-blue-500');
        }
        if (errorText) {
            errorText.classList.add('hidden');
            if (field === 'code') errorText.textContent = 'กรุณากรอกรหัสอาคาร';
            if (field === 'name') errorText.textContent = 'กรุณากรอกชื่ออาคาร';
            if (field === 'floors') errorText.textContent = 'กรุณากรอกจำนวนชั้น';
        }
    };

    window.checkAddBuildingDuplicate = function(field) {
        const codeInput = document.getElementById('building-code-input');
        const nameInput = document.getElementById('building-name-input');
        const codeErr = document.getElementById('building-code-error');
        const nameErr = document.getElementById('building-name-error');

        const codeVal = codeInput ? codeInput.value.trim() : '';
        const nameVal = nameInput ? nameInput.value.trim() : '';

        const buildings = window.buildingRoomsData || {};

        if (field === 'code' || !field) {
            if (codeVal !== '') {
                const codeExists = Object.keys(buildings).some(bId => bId.trim().toLowerCase() === codeVal.toLowerCase());
                if (codeExists) {
                    if (codeInput) {
                        codeInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                        codeInput.classList.add('border-red-500', 'focus:border-red-500');
                    }
                    if (codeErr) {
                        codeErr.textContent = 'มีรหัสอาคารนี้อยู่ในระบบนี้แล้ว';
                        codeErr.classList.remove('hidden');
                    }
                    return true;
                }
            }
        }

        if (field === 'name' || !field) {
            if (nameVal !== '') {
                const nameExists = Object.values(buildings).some(b => b && b.name && b.name.trim().toLowerCase() === nameVal.toLowerCase());
                if (nameExists) {
                    if (nameInput) {
                        nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                        nameInput.classList.add('border-red-500', 'focus:border-red-500');
                    }
                    if (nameErr) {
                        nameErr.textContent = 'มีชื่ออาคารนี้อยู่ในระบบนี้แล้ว';
                        nameErr.classList.remove('hidden');
                    }
                    return true;
                }
            }
        }

        return false;
    };

    window.submitAddBuildingForm = function(event) {
        if (event) event.preventDefault();
        const codeInput = document.getElementById('building-code-input');
        const nameInput = document.getElementById('building-name-input');
        const floorsInput = document.getElementById('building-floors-input');

        const code = codeInput ? codeInput.value.trim() : '';
        const name = nameInput ? nameInput.value.trim() : '';
        const floorsStr = floorsInput ? floorsInput.value.trim() : '';
        const floors = floorsInput ? parseInt(floorsInput.value.trim()) || 0 : 0;

        window.clearAddBuildingError();

        // 1. ตรวจสอบรหัสอาคารก่อน
        if (!code) {
            if (codeInput) {
                codeInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                codeInput.classList.add('border-red-500', 'focus:border-red-500');
                codeInput.focus();
            }
            const err = document.getElementById('building-code-error');
            if (err) {
                err.textContent = 'กรุณากรอกรหัสอาคาร';
                err.classList.remove('hidden');
            }
            return false;
        }

        if (window.checkAddBuildingDuplicate && window.checkAddBuildingDuplicate('code')) {
            if (codeInput) codeInput.focus();
            return false;
        }

        // 2. เมื่อรหัสอาคารผ่าน จึงตรวจสอบชื่ออาคาร
        if (!name) {
            if (nameInput) {
                nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                nameInput.classList.add('border-red-500', 'focus:border-red-500');
                nameInput.focus();
            }
            const err = document.getElementById('building-name-error');
            if (err) {
                err.textContent = 'กรุณากรอกชื่ออาคาร';
                err.classList.remove('hidden');
            }
            return false;
        }

        if (window.checkAddBuildingDuplicate && window.checkAddBuildingDuplicate('name')) {
            if (nameInput) nameInput.focus();
            return false;
        }

        // 3. เมื่อชื่ออาคารผ่าน จึงตรวจสอบจำนวนชั้น
        if (!floorsStr || floors < 1) {
            if (floorsInput) {
                floorsInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                floorsInput.classList.add('border-red-500', 'focus:border-red-500');
                floorsInput.focus();
            }
            const err = document.getElementById('building-floors-error');
            if (err) {
                err.textContent = 'กรุณากรอกจำนวนชั้น';
                err.classList.remove('hidden');
            }
            return false;
        }

        if (window.checkAddBuildingDuplicate && window.checkAddBuildingDuplicate()) {
            return false;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route("admin.buildings.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                building_id: code,
                name: name,
                floors: floors,
                rooms: 0,
                description: ''
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                if (typeof buildingRoomsData === 'undefined') {
                    window.buildingRoomsData = {};
                }
                if (!buildingRoomsData[code]) {
                    buildingRoomsData[code] = { name: name, floors: floors, rooms: [] };
                }

                window.closeBuildingModal();
                if (typeof showAdminToast === 'function') {
                    showAdminToast("สำเร็จ", `เพิ่มอาคาร "${name}" (${code}) เรียบร้อยแล้ว`);
                }
                
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                if (body.errors) {
                    if (body.errors.building_id) {
                        if (codeInput) {
                            codeInput.classList.remove('border-slate-200', 'focus:border-emerald-500');
                            codeInput.classList.add('border-red-500', 'focus:border-red-500');
                        }
                        const err = document.getElementById('building-code-error');
                        if (err) {
                            err.textContent = body.errors.building_id[0] || 'มีรหัสอาคารนี้อยู่ในระบบนี้แล้ว';
                            err.classList.remove('hidden');
                        }
                    }
                    if (body.errors.name) {
                        if (nameInput) {
                            nameInput.classList.remove('border-slate-200', 'focus:border-emerald-500');
                            nameInput.classList.add('border-red-500', 'focus:border-red-500');
                        }
                        const err = document.getElementById('building-name-error');
                        if (err) {
                            err.textContent = body.errors.name[0] || 'มีชื่ออาคารนี้อยู่ในระบบนี้แล้ว';
                            err.classList.remove('hidden');
                        }
                    }
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (body.message || 'ไม่สามารถเพิ่มอาคารได้'));
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });

        return false;
    };

    // Modal เพิ่มประเภทบุคลากร
    window.openNewPositionModal = function() {
        const modal = document.getElementById('modal-add-personnel-type');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            const nameInput = document.getElementById('personnel-type-name-input');
            if (nameInput) {
                nameInput.value = '';
                setTimeout(() => nameInput.focus(), 100);
            }
        }
    };

    window.closeNewPositionModal = function() {
        const modal = document.getElementById('modal-add-personnel-type');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        const form = document.getElementById('new-personnel-type-form');
        if (form) form.reset();
        
        const errName = document.getElementById('personnel-type-name-error');
        if (errName) errName.classList.add('hidden');
        const nameInput = document.getElementById('personnel-type-name-input');
        if (nameInput) nameInput.classList.remove('border-red-500');
    };

    window.submitAddPersonnelTypeForm = function(event) {
        if (event) event.preventDefault();
        const nameInput = document.getElementById('personnel-type-name-input');

        const name = nameInput ? nameInput.value.trim() : '';

        if (!name) {
            const errName = document.getElementById('personnel-type-name-error');
            if (errName) errName.classList.remove('hidden');
            if (nameInput) nameInput.classList.add('border-red-500');
            return false;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch('{{ route("admin.personnel-types.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: name
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('basic-personnel-types-table-body');
                if (tbody) {
                    const newIndex = tbody.children.length + 1;
                    const newId = data.data ? data.data.id : newIndex;
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition border-b border-slate-100';
                    tr.innerHTML = `
                        <td class="p-4 text-center text-slate-500 font-mono">${newIndex}</td>
                        <td class="p-4 font-bold text-slate-800">${name}</td>
                        <td class="p-4 text-center">
                            <button type="button" onclick="editPersonnelType(${newId})" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold"><i class="fa-solid fa-pen"></i> แก้ไข</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                }
                window.closeNewPositionModal();
                if (typeof showAdminToast === 'function') {
                    showAdminToast("สำเร็จ", `เพิ่มข้อมูลประเภทบุคลากร "${name}" เรียบร้อยแล้ว`);
                }
            } else {
                const errName = document.getElementById('personnel-type-name-error');
                if (errName) {
                    errName.innerHTML = `<i class="fa-solid fa-circle-exclamation mr-1"></i>${data.message || 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล'}`;
                    errName.classList.remove('hidden');
                }
                const nameInput = document.getElementById('personnel-type-name-input');
                if (nameInput) nameInput.classList.add('border-red-500');
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
        });
        
        return false;
    };


    // ตัวแปรเก็บข้อมูลห้องของอาคาร
    window.buildingRoomsData = {
        @if(isset($dbBuildings) && count($dbBuildings) > 0)
            @foreach($dbBuildings as $b)
                '{{ $b->building_id }}': {
                    name: '{{ $b->building_name }}',
                    floors: {{ $b->floors }},
                    rooms: [
                        @if($b->rooms)
                            @foreach($b->rooms as $room)
                                {code: '{{ $room->room_code }}', name: '{{ $room->room_name }}', floor: {{ $room->floor }}},
                            @endforeach
                        @endif
                    ]
                },
            @endforeach
        @endif
    };
    
    let currentBuildingDetailsId = null;

    window.viewBuildingDetails = function(buildingId) {
        currentBuildingDetailsId = buildingId;
        const bData = buildingRoomsData[buildingId] || { name: 'ไม่พบข้อมูล', floors: 1, rooms: [] };
        
        const title = document.getElementById('building-details-title');
        const subtitle = document.getElementById('building-details-subtitle');
        const floorSelect = document.getElementById('building-details-floor-select');
        
        if (title) title.textContent = `รายละเอียดอาคาร: ${bData.name}`;
        if (subtitle) subtitle.textContent = `รหัสอาคาร: ${buildingId}`;
        
        if (floorSelect) {
            floorSelect.innerHTML = '';
            const allOpt = document.createElement('option');
            allOpt.value = 'all';
            allOpt.textContent = 'ทุกชั้น';
            floorSelect.appendChild(allOpt);
            
            for (let i = 1; i <= bData.floors; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = `ชั้น ${i}`;
                floorSelect.appendChild(opt);
            }
        }
        
        window.updateBuildingRoomsTable();
        
        const modal = document.getElementById('modal-building-details');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
    };

    window.closeBuildingDetailsModal = function() {
        const modal = document.getElementById('modal-building-details');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        currentBuildingDetailsId = null;
    };

    window.updateBuildingRoomsTable = function() {
        if (!currentBuildingDetailsId) return;
        
        const bData = buildingRoomsData[currentBuildingDetailsId];
        const floorSelect = document.getElementById('building-details-floor-select');
        const selectedFloor = floorSelect ? floorSelect.value : 'all';
        
        let filteredRooms = bData.rooms || [];
        if (selectedFloor !== 'all') {
            filteredRooms = filteredRooms.filter(r => r.floor == selectedFloor);
        }
        
        const tbody = document.getElementById('building-rooms-table-body');
        if (tbody) {
            tbody.innerHTML = '';
            if (filteredRooms.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="p-6 text-center text-slate-400 font-bold">ไม่พบข้อมูลห้องในชั้นนี้</td></tr>`;
            } else {
                filteredRooms.forEach((room, index) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition border-b border-slate-100';
                    tr.innerHTML = `
                        <td class="p-3 text-center text-slate-500 font-mono">${index + 1}</td>
                        <td class="p-3 font-bold text-blue-600">${room.code}</td>
                        <td class="p-3 font-semibold text-slate-700">${room.name}</td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="editRoom('${room.code}', '${room.name}')" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold"><i class="fa-solid fa-pen"></i> แก้ไข</button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }
        
        const countLabel = document.getElementById('building-rooms-footer-count');
        if (countLabel) {
            countLabel.textContent = `แสดง ${filteredRooms.length} ห้อง`;
        }
    };

    window.openAddRoomModal = function() {
        const modal = document.getElementById('modal-manage-room');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            document.getElementById('manage-room-title').innerHTML = 'เพิ่มห้องใหม่';
            const icon = modal.querySelector('.fa-door-open, .fa-pen-to-square');
            if (icon) icon.className = 'fa-solid fa-door-open text-blue-600';
            document.getElementById('manage-room-mode').value = 'add';
            document.getElementById('manage-room-code-input').value = '';
            document.getElementById('manage-room-name-input').value = '';
            
            // Reset errors
            const errCode = document.getElementById('manage-room-code-error');
            if (errCode) errCode.classList.add('hidden');
            document.getElementById('manage-room-code-input').classList.remove('border-red-500');
            
            const errName = document.getElementById('manage-room-name-error');
            if (errName) errName.classList.add('hidden');
            document.getElementById('manage-room-name-input').classList.remove('border-red-500');
        }
    };

    window.editRoom = function(code, name) {
        const modal = document.getElementById('modal-manage-room');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            document.getElementById('manage-room-title').innerHTML = 'แก้ไขห้อง';
            const icon = modal.querySelector('.fa-door-open, .fa-pen-to-square');
            if (icon) icon.className = 'fa-solid fa-pen-to-square text-blue-600';
            document.getElementById('manage-room-mode').value = 'edit';
            document.getElementById('manage-room-old-code').value = code;
            document.getElementById('manage-room-code-input').value = code;
            document.getElementById('manage-room-name-input').value = name;
            
            // Reset errors
            const errCode = document.getElementById('manage-room-code-error');
            if (errCode) errCode.classList.add('hidden');
            document.getElementById('manage-room-code-input').classList.remove('border-red-500');
            
            const errName = document.getElementById('manage-room-name-error');
            if (errName) errName.classList.add('hidden');
            document.getElementById('manage-room-name-input').classList.remove('border-red-500');
        }
    };

    window.closeManageRoomModal = function() {
        const modal = document.getElementById('modal-manage-room');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };

    window.submitManageRoomForm = function(event) {
        if (event) event.preventDefault();
        
        const mode = document.getElementById('manage-room-mode').value;
        const oldCode = document.getElementById('manage-room-old-code').value;
        const code = document.getElementById('manage-room-code-input').value.trim();
        const name = document.getElementById('manage-room-name-input').value.trim();
        
        if (!code) {
            const errCode = document.getElementById('manage-room-code-error');
            if (errCode) errCode.classList.remove('hidden');
            const codeEl = document.getElementById('manage-room-code-input');
            if (codeEl) {
                codeEl.classList.remove('border-slate-200', 'focus:border-blue-500');
                codeEl.classList.add('border-red-500', 'focus:border-red-500');
                codeEl.focus();
            }
            return false;
        }
        
        if (!name) {
            const errName = document.getElementById('manage-room-name-error');
            if (errName) errName.classList.remove('hidden');
            const nameEl = document.getElementById('manage-room-name-input');
            if (nameEl) {
                nameEl.classList.remove('border-slate-200', 'focus:border-blue-500');
                nameEl.classList.add('border-red-500', 'focus:border-red-500');
                nameEl.focus();
            }
            return false;
        }
        
        const floorSelect = document.getElementById('building-details-floor-select');
        let floor = floorSelect ? floorSelect.value : '1';
        if (floor === 'all') floor = 1; 
        
        if (currentBuildingDetailsId && buildingRoomsData[currentBuildingDetailsId]) {
            const bData = buildingRoomsData[currentBuildingDetailsId];
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (mode === 'add') {
                fetch('{{ route("admin.rooms.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        building_id: currentBuildingDetailsId,
                        room_code: code,
                        room_name: name,
                        floor: parseInt(floor)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bData.rooms.push({ code: code, name: name, floor: parseInt(floor) });
                        window.updateBuildingRoomsTable();
                        const countTd = document.getElementById('room-count-' + currentBuildingDetailsId);
                        if (countTd) countTd.textContent = bData.rooms.length;
                        
                        if (typeof showAdminToast === 'function') showAdminToast("สำเร็จ", `เพิ่มห้อง "${code}" เรียบร้อยแล้ว`);
                        window.closeManageRoomModal();
                    } else {
                        alert(data.message || 'เกิดข้อผิดพลาดในการเพิ่มห้อง');
                    }
                }).catch(err => {
                    console.error(err);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                });
                
            } else if (mode === 'edit') {
                fetch('{{ route("admin.rooms.update") }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        building_id: currentBuildingDetailsId,
                        old_room_code: oldCode,
                        room_code: code,
                        room_name: name,
                        floor: parseInt(floor)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const roomIndex = bData.rooms.findIndex(r => r.code === oldCode);
                        if (roomIndex !== -1) {
                            bData.rooms[roomIndex].code = code;
                            bData.rooms[roomIndex].name = name;
                            bData.rooms[roomIndex].floor = parseInt(floor);
                        }
                        window.updateBuildingRoomsTable();
                        if (typeof showAdminToast === 'function') showAdminToast("สำเร็จ", `แก้ไขห้อง "${code}" เรียบร้อยแล้ว`);
                        window.closeManageRoomModal();
                    } else {
                        alert(data.message || 'เกิดข้อผิดพลาดในการแก้ไขห้อง');
                    }
                }).catch(err => {
                    console.error(err);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                });
            }
        }
        
        return false;
    };

    window.deleteRoom = function(code) {
        if (confirm(`ยืนยันลบห้องรหัส ${code} ใช่หรือไม่?`)) {
            if (currentBuildingDetailsId && buildingRoomsData[currentBuildingDetailsId]) {
                const bData = buildingRoomsData[currentBuildingDetailsId];
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('{{ route("admin.rooms.destroy") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        building_id: currentBuildingDetailsId,
                        room_code: code
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bData.rooms = bData.rooms.filter(r => r.code !== code);
                        window.updateBuildingRoomsTable();
                        const countTd = document.getElementById('room-count-' + currentBuildingDetailsId);
                        if (countTd) countTd.textContent = bData.rooms.length;
                        
                        if (typeof showAdminToast === 'function') showAdminToast("สำเร็จ", `ลบห้อง "${code}" เรียบร้อยแล้ว`);
                    } else {
                        alert(data.message || 'เกิดข้อผิดพลาดในการลบห้อง');
                    }
                }).catch(err => {
                    console.error(err);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                });
            }
        }
    };


    // 3. ฟังก์ชันระบบ ค้นหา / กรอง ข้อมูลในตาราง
    function filterBasicDataItems() {
        const searchText = document.getElementById('basic-search-input')?.value.toLowerCase() || '';
        const catFilter = document.getElementById('basic-category-filter')?.value || 'all';
        const typeFilter = document.getElementById('basic-type-filter')?.value || 'all';

        const rows = document.querySelectorAll('#basic-data-table-body tr.device-type-row');
        let hasVisibleRows = false;
        let visibleIndex = 1;

        rows.forEach(row => {
            const rowCat = row.getAttribute('data-category');
            const rowKind = row.getAttribute('data-kind');
            const rowText = row.innerText.toLowerCase();
            const matchesSearch = rowText.includes(searchText);
            
            const matchesCat = (rowCat === catFilter);
            const matchesKind = (rowKind === typeFilter);
            
            if (catFilter === 'all' || typeFilter === 'all') {
                row.style.display = 'none';
            } else if (matchesSearch && matchesCat && matchesKind) {
                row.style.display = '';
                hasVisibleRows = true;
                
                // อัปเดตลำดับตัวเลข
                const indexCell = row.querySelector('td:first-child');
                if (indexCell) {
                    indexCell.textContent = visibleIndex++;
                }
            } else {
                row.style.display = 'none';
            }
        });

        const emptyRow = document.getElementById('device-type-empty-row');
        if (catFilter === 'all' || typeFilter === 'all') {
            if (emptyRow) {
                emptyRow.style.display = '';
                emptyRow.innerHTML = '<td colspan="5" class="p-8 text-center text-slate-400 font-medium">กรุณาเลือก "ประเภททรัพย์สิน" และ "ชนิดทรัพย์สิน" เพื่อดูข้อมูล</td>';
            }
        } else if (!hasVisibleRows) {
            if (emptyRow) {
                emptyRow.style.display = '';
                emptyRow.innerHTML = '<td colspan="5" class="p-8 text-center text-slate-400 font-medium">ไม่พบข้อมูลที่ตรงกับเงื่อนไขการค้นหา</td>';
            }
        } else {
            if (emptyRow) {
                emptyRow.style.display = 'none';
            }
        }
    }

    window.updateEditDeviceTypeKindDropdown = function(catKey) {
        const kindSelect = document.getElementById('edit-device-type-kind-select');
        if (!kindSelect) return;
        kindSelect.innerHTML = '';
        
        const categories = {};
        if (typeof assetKindsData !== 'undefined') {
            for (const catName in assetKindsData) {
                categories[catName] = assetKindsData[catName];
            }
        }
        
        const kindsList = categories[catKey] || [];
        if (!catKey || kindsList.length === 0) {
            kindSelect.innerHTML = '<option value="">-- กรุณาเลือกหรือเพิ่มชนิดทรัพย์สินในจัดการก่อน --</option>';
            kindSelect.className = kindSelect.className.replace('text-slate-800', 'text-slate-400');
            return;
        }
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '-- เลือกชนิดทรัพย์สิน --';
        kindSelect.appendChild(placeholder);
        kindsList.forEach(kind => {
            const opt = document.createElement('option');
            opt.value = kind;
            opt.textContent = kind;
            kindSelect.appendChild(opt);
        });
        kindSelect.classList.remove('text-slate-400');
        kindSelect.classList.add('text-slate-800');
    };

    window.editDeviceType = function(id) {
        const row = document.getElementById(`device-type-row-${id}`);
        if (!row) {
            if (typeof showAdminToast === 'function') showAdminToast('ข้อผิดพลาด', 'ไม่พบข้อมูลรายการนี้', 'error');
            return;
        }
        
        const cat = row.getAttribute('data-category');
        const kind = row.getAttribute('data-kind');
        const brand = row.children[1].innerText;
        const model = row.children[2].innerText;
        const desc = row.children[3].innerText !== '-' ? row.children[3].innerText : '';
        
        document.getElementById('edit-device-type-id').value = id;
        
        // Sync categories
        const catSelect = document.getElementById('edit-device-type-category-select');
        const manageCatSelect = document.getElementById('manage-category-select');
        if (catSelect && manageCatSelect) {
            while (catSelect.options.length > 1) {
                catSelect.remove(1);
            }
            for (let i = 1; i < manageCatSelect.options.length; i++) {
                const opt = manageCatSelect.options[i];
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.text = opt.text;
                catSelect.appendChild(newOpt);
            }
        }
        
        if (catSelect) {
            catSelect.value = cat;
            window.updateEditDeviceTypeKindDropdown(cat);
            setTimeout(() => {
                const kindSelect = document.getElementById('edit-device-type-kind-select');
                if (kindSelect) kindSelect.value = kind;
            }, 50);
        }
        
        document.getElementById('edit-device-type-brand-input').value = brand !== '-' ? brand : '';
        document.getElementById('edit-device-type-model-input').value = model !== '-' ? model : '';
        document.getElementById('edit-device-type-desc-input').value = desc;
        
        const modal = document.getElementById('modal-edit-device-type');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }
    };
    
    window.closeEditDeviceTypeModal = function() {
        const modal = document.getElementById('modal-edit-device-type');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        const form = document.getElementById('edit-device-type-form');
        if (form) form.reset();
        
        ['category', 'kind', 'brand', 'model'].forEach(field => clearEditDeviceTypeError(field));
    };

    window.clearEditDeviceTypeError = function(field) {
        const inputMap = {
            'category': 'edit-device-type-category-select',
            'kind': 'edit-device-type-kind-select',
            'brand': 'edit-device-type-brand-input',
            'model': 'edit-device-type-model-input'
        };
        const id = inputMap[field];
        if (!id) return;
        const input = document.getElementById(id);
        const err = document.getElementById(id.replace('-select', '-error').replace('-input', '-error'));
        
        if (input) {
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-slate-200', 'focus:border-amber-500');
        }
        if (err) err.classList.add('hidden');
    };

    window.submitEditDeviceTypeForm = function(event) {
        if (event) event.preventDefault();
        
        const id = document.getElementById('edit-device-type-id').value;
        const catSelect = document.getElementById('edit-device-type-category-select');
        const kindSelect = document.getElementById('edit-device-type-kind-select');
        const brandInput = document.getElementById('edit-device-type-brand-input');
        const modelInput = document.getElementById('edit-device-type-model-input');
        const descInput = document.getElementById('edit-device-type-desc-input');

        const cat = catSelect ? catSelect.value : '';
        const kind = kindSelect ? kindSelect.value : '';
        const brand = brandInput ? brandInput.value.trim() : '';
        const model = modelInput ? modelInput.value.trim() : '';
        const desc = descInput ? descInput.value.trim() : '';

        ['category', 'kind', 'brand', 'model'].forEach(field => clearEditDeviceTypeError(field));

        let hasError = false;
        
        if (!cat) {
            if (catSelect) {
                catSelect.classList.remove('border-slate-200', 'focus:border-amber-500');
                catSelect.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('edit-device-type-category-error');
            if (err) err.classList.remove('hidden');
            hasError = true;
        }

        if (!kind) {
            if (kindSelect) {
                kindSelect.classList.remove('border-slate-200', 'focus:border-amber-500');
                kindSelect.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('edit-device-type-kind-error');
            if (err) err.classList.remove('hidden');
            hasError = true;
        }

        if (!brand) {
            if (brandInput) {
                brandInput.classList.remove('border-slate-200', 'focus:border-amber-500');
                brandInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('edit-device-type-brand-error');
            if (err) err.classList.remove('hidden');
            hasError = true;
        }

        if (!model) {
            if (modelInput) {
                modelInput.classList.remove('border-slate-200', 'focus:border-amber-500');
                modelInput.classList.add('border-red-500', 'focus:border-red-500');
            }
            const err = document.getElementById('edit-device-type-model-error');
            if (err) err.classList.remove('hidden');
            hasError = true;
        }
        
        if (hasError) return false;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const url = '{{ route("admin.device-types.update", ":id") }}'.replace(':id', id);
        
        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                category: cat,
                name: kind,
                brand: brand,
                model: model,
                details: desc
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`device-type-row-${id}`);
                if (row) {
                    row.setAttribute('data-category', cat);
                    row.setAttribute('data-kind', kind);
                    row.children[1].innerText = brand || '-';
                    row.children[2].innerText = model || '-';
                    row.children[3].innerText = desc || '-';
                }
                
                if (typeof filterBasicDataItems === 'function') {
                    filterBasicDataItems();
                }

                window.closeEditDeviceTypeModal();
                if (typeof showAdminToast === 'function') {
                    showAdminToast('สำเร็จ', `แก้ไขข้อมูลทรัพย์สิน "${brand} ${model}" เรียบร้อยแล้ว`);
                }
            } else {
                alert('เกิดข้อผิดพลาดในการแก้ไขข้อมูล: ' + (data.message || ''));
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });
        
        return false;
    };
    
    window.deleteDeviceType = function(id) {
        if(confirm('ยืนยันลบรายการนี้?')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = '{{ route("admin.device-types.destroy", ":id") }}'.replace(':id', id);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from DOM
                    const tbody = document.getElementById('basic-data-table-body');
                    const rows = tbody.querySelectorAll('tr.device-type-row');
                    rows.forEach(row => {
                        const btn = row.querySelector(`button[onclick="deleteDeviceType('${id}')"]`) || row.querySelector(`button[onclick="deleteDeviceType(${id})"]`);
                        if (btn) {
                            row.remove();
                        }
                    });
                    
                    if (typeof filterBasicDataItems === 'function') {
                        filterBasicDataItems();
                    }
                    if (typeof showAdminToast === 'function') {
                        showAdminToast("สำเร็จ", `ลบประเภททรัพย์สินเรียบร้อยแล้ว`);
                    } else {
                        alert('ลบข้อมูลเรียบร้อยแล้ว');
                    }
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบข้อมูลได้'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            });
        }
    }
    
    function deletePersonnelType(id) {
        if(confirm('ยืนยันลบประเภทบุคลากรนี้?')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = '{{ route("admin.personnel-types.destroy", ":id") }}'.replace(':id', id);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from DOM
                    const tbody = document.getElementById('basic-personnel-types-table-body');
                    if (tbody) {
                        const rows = tbody.querySelectorAll('tr');
                        rows.forEach(row => {
                            const btn = row.querySelector(`button[onclick*="deletePersonnelType('${id}')"]`) || row.querySelector(`button[onclick*="deletePersonnelType(${id})"]`);
                            if (btn) {
                                row.remove();
                            }
                        });
                        
                        // Update sequence numbers
                        const remainingRows = tbody.querySelectorAll('tr');
                        let count = 1;
                        remainingRows.forEach(row => {
                            const firstTd = row.querySelector('td:first-child');
                            if (firstTd && !firstTd.hasAttribute('colspan')) {
                                firstTd.textContent = count++;
                            }
                        });
                        
                        if (remainingRows.length === 0) {
                             tbody.innerHTML = '<tr><td colspan="3" class="p-8 text-center text-slate-500 font-semibold">ไม่มีข้อมูลประเภทบุคลากรในระบบ</td></tr>';
                        }
                    }
                    
                    if (typeof showAdminToast === 'function') {
                        showAdminToast("สำเร็จ", `ลบประเภทบุคลากรเรียบร้อยแล้ว`);
                    } else {
                        alert('ลบข้อมูลเรียบร้อยแล้ว');
                    }
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบข้อมูลได้'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            });
        }
    }
    window.editBuilding = function(id) {
        const bData = buildingRoomsData[id];
        if (!bData) {
            if (typeof showAdminToast === 'function') showAdminToast("คำเตือน", "ไม่พบข้อมูลอาคารนี้", "warning");
            return;
        }

        const modal = document.getElementById('modal-edit-building');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');

            const oldCodeInput = document.getElementById('edit-building-old-code');
            const codeInput = document.getElementById('edit-building-code-input');
            const nameInput = document.getElementById('edit-building-name-input');
            const floorsInput = document.getElementById('edit-building-floors-input');

            if (oldCodeInput) oldCodeInput.value = id;
            if (codeInput) codeInput.value = id;
            if (nameInput) nameInput.value = bData.name;
            if (floorsInput) floorsInput.value = bData.floors;
        }
    };

    window.closeEditBuildingModal = function() {
        const modal = document.getElementById('modal-edit-building');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        if (window.clearEditBuildingError) window.clearEditBuildingError();
    };

    window.clearEditBuildingError = function(field) {
        if (!field) {
            ['code', 'name', 'floors'].forEach(f => window.clearEditBuildingError(f));
            return;
        }
        const input = document.getElementById(`edit-building-${field}-input`);
        const errorText = document.getElementById(`edit-building-${field}-error`);
        if (input) {
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-slate-200', 'focus:border-blue-500');
        }
        if (errorText) {
            errorText.classList.add('hidden');
            if (field === 'code') errorText.textContent = 'กรุณากรอกรหัสอาคาร';
            if (field === 'name') errorText.textContent = 'กรุณากรอกชื่ออาคาร';
            if (field === 'floors') errorText.textContent = 'กรุณากรอกจำนวนชั้น';
        }
    };

    window.checkEditBuildingDuplicate = function(field) {
        const oldCode = document.getElementById('edit-building-old-code')?.value || '';
        const codeInput = document.getElementById('edit-building-code-input');
        const nameInput = document.getElementById('edit-building-name-input');
        const codeErr = document.getElementById('edit-building-code-error');
        const nameErr = document.getElementById('edit-building-name-error');

        const codeVal = codeInput ? codeInput.value.trim() : '';
        const nameVal = nameInput ? nameInput.value.trim() : '';

        const buildings = window.buildingRoomsData || {};

        if (field === 'code' || !field) {
            if (codeVal !== '') {
                const codeExists = Object.keys(buildings).some(bId => bId !== oldCode && bId.trim().toLowerCase() === codeVal.toLowerCase());
                if (codeExists) {
                    if (codeInput) {
                        codeInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                        codeInput.classList.add('border-red-500', 'focus:border-red-500');
                    }
                    if (codeErr) {
                        codeErr.textContent = 'มีรหัสอาคารนี้อยู่ในระบบนี้แล้ว';
                        codeErr.classList.remove('hidden');
                    }
                    return true;
                }
            }
        }

        if (field === 'name' || !field) {
            if (nameVal !== '') {
                const nameExists = Object.entries(buildings).some(([bId, b]) => bId !== oldCode && b && b.name && b.name.trim().toLowerCase() === nameVal.toLowerCase());
                if (nameExists) {
                    if (nameInput) {
                        nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                        nameInput.classList.add('border-red-500', 'focus:border-red-500');
                    }
                    if (nameErr) {
                        nameErr.textContent = 'มีชื่ออาคารนี้อยู่ในระบบนี้แล้ว';
                        nameErr.classList.remove('hidden');
                    }
                    return true;
                }
            }
        }

        return false;
    };

    window.submitEditBuildingForm = function(event) {
        if (event) event.preventDefault();
        const oldCode = document.getElementById('edit-building-old-code').value;
        const codeInput = document.getElementById('edit-building-code-input');
        const nameInput = document.getElementById('edit-building-name-input');
        const floorsInput = document.getElementById('edit-building-floors-input');

        const code = codeInput ? codeInput.value.trim() : '';
        const name = nameInput ? nameInput.value.trim() : '';
        const floors = floorsInput ? parseInt(floorsInput.value) || 0 : 0;

        if (window.clearEditBuildingError) window.clearEditBuildingError();

        // 1. ตรวจสอบรหัสอาคารก่อน
        if (!code) {
            if (codeInput) {
                codeInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                codeInput.classList.add('border-red-500', 'focus:border-red-500');
                codeInput.focus();
            }
            const err = document.getElementById('edit-building-code-error');
            if (err) {
                err.textContent = 'กรุณากรอกรหัสอาคาร';
                err.classList.remove('hidden');
            }
            return false;
        }

        if (window.checkEditBuildingDuplicate && window.checkEditBuildingDuplicate('code')) {
            if (codeInput) codeInput.focus();
            return false;
        }

        // 2. เมื่อรหัสอาคารผ่าน จึงตรวจสอบชื่ออาคาร
        if (!name) {
            if (nameInput) {
                nameInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                nameInput.classList.add('border-red-500', 'focus:border-red-500');
                nameInput.focus();
            }
            const err = document.getElementById('edit-building-name-error');
            if (err) {
                err.textContent = 'กรุณากรอกชื่ออาคาร';
                err.classList.remove('hidden');
            }
            return false;
        }

        if (window.checkEditBuildingDuplicate && window.checkEditBuildingDuplicate('name')) {
            if (nameInput) nameInput.focus();
            return false;
        }

        // 3. เมื่อชื่ออาคารผ่าน จึงตรวจสอบจำนวนชั้น
        const floorsStr = floorsInput ? floorsInput.value.trim() : '';
        if (!floorsStr || floors < 1) {
            if (floorsInput) {
                floorsInput.classList.remove('border-slate-200', 'focus:border-blue-500');
                floorsInput.classList.add('border-red-500', 'focus:border-red-500');
                floorsInput.focus();
            }
            const err = document.getElementById('edit-building-floors-error');
            if (err) {
                err.textContent = 'กรุณากรอกจำนวนชั้น';
                err.classList.remove('hidden');
            }
            return false;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const url = '{{ route("admin.buildings.update", ":id") }}'.replace(':id', oldCode);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                building_id: code,
                name: name,
                floors: floors,
                rooms: 0,
                description: ''
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                if (buildingRoomsData[oldCode]) {
                    const roomData = buildingRoomsData[oldCode];
                    roomData.name = name;
                    roomData.floors = floors;
                    if (code !== oldCode) {
                        buildingRoomsData[code] = roomData;
                        delete buildingRoomsData[oldCode];
                    }
                }

                const tbody = document.getElementById('buildings-table-body');
                if (tbody) {
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const codeCell = row.querySelector('td:nth-child(2)');
                        if (codeCell && codeCell.textContent.trim() === oldCode) {
                            codeCell.textContent = code;
                            const nameCell = row.querySelector('td:nth-child(3)');
                            if (nameCell) nameCell.textContent = name;
                            const floorsCell = row.querySelector('td:nth-child(4)');
                            if (floorsCell) floorsCell.textContent = floors;
                            
                            const countCell = row.querySelector('td:nth-child(5)');
                            if (countCell) countCell.id = 'room-count-' + code;

                            const actionsCell = row.querySelector('td:last-child');
                            if (actionsCell) {
                                actionsCell.innerHTML = `
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="editBuilding('${code}')" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] rounded-lg transition font-bold cursor-pointer"><i class="fa-solid fa-pen"></i> แก้ไข</button>
                                        <button type="button" onclick="viewBuildingDetails('${code}')" class="px-2.5 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-[10px] rounded-lg transition font-bold cursor-pointer"><i class="fa-solid fa-door-open"></i> จัดการห้อง</button>
                                    </div>
                                `;
                            }
                        }
                    });
                }

                if (typeof showAdminToast === 'function') {
                    showAdminToast("สำเร็จ", `แก้ไขข้อมูลอาคาร "${name}" (${code}) เรียบร้อยแล้ว`);
                }
                window.closeEditBuildingModal();
            } else {
                if (body.errors) {
                    if (body.errors.building_id) {
                        if (codeInput) {
                            codeInput.classList.remove('border-slate-200', 'focus:border-amber-500');
                            codeInput.classList.add('border-red-500', 'focus:border-red-500');
                        }
                        const err = document.getElementById('edit-building-code-error');
                        if (err) {
                            err.textContent = body.errors.building_id[0] || 'มีรหัสอาคารนี้อยู่ในระบบนี้แล้ว';
                            err.classList.remove('hidden');
                        }
                    }
                    if (body.errors.name) {
                        if (nameInput) {
                            nameInput.classList.remove('border-slate-200', 'focus:border-amber-500');
                            nameInput.classList.add('border-red-500', 'focus:border-red-500');
                        }
                        const err = document.getElementById('edit-building-name-error');
                        if (err) {
                            err.textContent = body.errors.name[0] || 'มีชื่ออาคารนี้อยู่ในระบบนี้แล้ว';
                            err.classList.remove('hidden');
                        }
                    }
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (body.message || 'ไม่สามารถแก้ไขอาคารได้'));
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });
        
        return false;
    };
    // ฟังก์ชันสำหรับ Modal แก้ไขประเภทบุคลากร
    window.editPersonnelType = function(id) {
        const modal = document.getElementById('modal-edit-personnel-type');
        if (!modal) return;
        
        // ค้นหาแถวในตารางเพื่อดึงข้อมูลเดิม (ในสถานการณ์จริงควรดึงจาก API หรือฐานข้อมูล)
        const tbody = document.getElementById('basic-personnel-types-table-body');
        let oldName = '';
        let oldDesc = '';
        
        if (tbody) {
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const btn = row.querySelector(`button[onclick*="editPersonnelType(${id})"]`);
                if (btn || (typeof id === 'string' && row.querySelector(`button[onclick*="editPersonnelType('${id}')"]`))) {
                    oldName = row.querySelector('td:nth-child(2)')?.textContent.trim() || '';
                }
            });
        }
        
        document.getElementById('edit-personnel-type-id-input').value = id;
        document.getElementById('edit-personnel-type-name-input').value = oldName;
        
        modal.style.display = 'flex';
        modal.classList.remove('hidden');

        const nameInput = document.getElementById('edit-personnel-type-name-input');
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                const errName = document.getElementById('edit-personnel-type-name-error');
                if (errName) errName.classList.add('hidden');
                this.classList.remove('border-red-500');
            }, { once: false }); // Will add multiple, but it's safe enough or we can define it outside
        }
    };

    window.closeEditPersonnelTypeModal = function() {
        const modal = document.getElementById('modal-edit-personnel-type');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
        const errName = document.getElementById('edit-personnel-type-name-error');
        if (errName) errName.classList.add('hidden');
        const nameInput = document.getElementById('edit-personnel-type-name-input');
        if (nameInput) nameInput.classList.remove('border-red-500');
    };

    window.submitEditPersonnelTypeForm = function(event) {
        if (event) event.preventDefault();
        
        const id = document.getElementById('edit-personnel-type-id-input').value;
        const nameInput = document.getElementById('edit-personnel-type-name-input');
        const newName = nameInput ? nameInput.value.trim() : '';
        
        if (!newName) {
            const errName = document.getElementById('edit-personnel-type-name-error');
            if (errName) errName.classList.remove('hidden');
            if (nameInput) nameInput.classList.add('border-red-500');
            return false;
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const updateUrl = '{{ route("admin.personnel-types.update", ["id" => ":id"]) }}'.replace(':id', id);

        fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: newName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('basic-personnel-types-table-body');
                if (tbody) {
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const btn = row.querySelector(`button[onclick*="editPersonnelType(${id})"]`) || row.querySelector(`button[onclick*="editPersonnelType('${id}')"]`);
                        if (btn) {
                            const nameCell = row.querySelector('td:nth-child(2)');
                            if (nameCell) nameCell.textContent = newName;
                        }
                    });
                }
                window.closeEditPersonnelTypeModal();
                if (typeof showAdminToast === 'function') {
                    showAdminToast("สำเร็จ", `แก้ไขประเภทบุคลากร "${newName}" เรียบร้อยแล้ว`);
                }
            } else {
                const errName = document.getElementById('edit-personnel-type-name-error');
                if (errName) {
                    errName.innerHTML = `<i class="fa-solid fa-circle-exclamation mr-1"></i>${data.message || 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล'}`;
                    errName.classList.remove('hidden');
                }
                const nameInput = document.getElementById('edit-personnel-type-name-input');
                if (nameInput) nameInput.classList.add('border-red-500');
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
        });
        
        return false;
    };
    function deleteBuilding(id) { if(confirm('ยืนยันลบอาคารนี้?')) alert('ลบเรียบร้อย ID: ' + id); }
    function deleteSubDepartment(group, name) { if(confirm('ยืนยันลบหน่วยงาน ' + name + '?')) alert('ลบเรียบร้อย'); }

    window.searchBuildings = function() {
        const input = document.getElementById("search-building-input");
        if (!input) return;
        
        const filter = input.value.toUpperCase();
        const tbody = document.getElementById("buildings-table-body");
        if (!tbody) return;
        
        const trs = tbody.getElementsByTagName("tr");

        let hasVisibleRow = false;
        
        for (let i = 0; i < trs.length; i++) {
            // Check if this row is the "No data" row
            if (trs[i].children.length === 1) continue; 
            
            const tdCode = trs[i].getElementsByTagName("td")[1];
            const tdName = trs[i].getElementsByTagName("td")[2];
            
            if (tdCode || tdName) {
                const codeValue = tdCode ? tdCode.textContent || tdCode.innerText : "";
                const nameValue = tdName ? tdName.textContent || tdName.innerText : "";
                
                if (codeValue.toUpperCase().indexOf(filter) > -1 || nameValue.toUpperCase().indexOf(filter) > -1) {
                    trs[i].style.display = "";
                    hasVisibleRow = true;
                } else {
                    trs[i].style.display = "none";
                }
            }
        }

        // Handle "No results found" row
        let noResultsRow = document.getElementById("buildings-no-results-row");
        
        if (!hasVisibleRow && trs.length > 0 && filter !== "") {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = "buildings-no-results-row";
                noResultsRow.innerHTML = `
                    <td colspan="6" class="p-8 text-center text-slate-500 font-semibold">
                        ไม่พบข้อมูลอาคารที่ค้นหา
                    </td>
                `;
                tbody.appendChild(noResultsRow);
            } else {
                noResultsRow.style.display = "";
            }
        } else if (noResultsRow) {
            noResultsRow.style.display = "none";
        }
    };
</script>

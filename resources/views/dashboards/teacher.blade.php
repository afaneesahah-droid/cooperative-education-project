@php
    $unfixableBudgetJobs = $requests->where('user_id', $user->user_id ?? $user->id)
        ->filter(fn($job) => $job->budget > 0 && ($job->status === 'รออนุมัติงบประมาณ' || $job->status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้' || ($job->status === 'กำลังดำเนินการ' && $job->budget_approved)));
    $teacherJobs = $requests->where('user_id', $user->user_id ?? $user->id);
    $unratedCount = $teacherJobs->where('status', 'เสร็จสิ้น')->where('rating', 0)->count();
    $ratedTeacherJobs = $teacherJobs->filter(fn($job) => (int)($job->rating ?? 0) > 0);
    $totalRatedCount = $ratedTeacherJobs->count();
    $overallAvgRating = $totalRatedCount > 0 ? (float)$ratedTeacherJobs->avg('rating') : 0.0;
    $overallAvgFormatted = number_format($overallAvgRating, 2);
    $overallAvgOneDec = number_format($overallAvgRating, 1);
    $overallPercent = $totalRatedCount > 0 ? number_format(($overallAvgRating / 5) * 100, 1) : '0.0';

    if (!function_exists('getRatingLabel')) {
        function getRatingLabel($ratingVal) {
            $val = (float)$ratingVal;
            if ($val >= 4.5) return 'พอใจมากที่สุด (ยอดเยี่ยม)';
            if ($val >= 3.5) return 'พอใจมาก (ดีมาก)';
            if ($val >= 2.5) return 'ปานกลาง';
            if ($val >= 1.5) return 'น้อย';
            if ($val > 0) return 'น้อยที่สุด (ควรปรับปรุง)';
            return 'ยังไม่มีการประเมิน';
        }
    }
    $getRatingLabel = function($val) {
        return getRatingLabel($val);
    };
    
    $thai_months = ['Jan'=>'ม.ค.', 'Feb'=>'ก.พ.', 'Mar'=>'มี.ค.', 'Apr'=>'เม.ย.', 'May'=>'พ.ค.', 'Jun'=>'มิ.ย.', 'Jul'=>'ก.ค.', 'Aug'=>'ส.ค.', 'Sep'=>'ก.ย.', 'Oct'=>'ต.ค.', 'Nov'=>'พ.ย.', 'Dec'=>'ธ.ค.'];
    $year = date('Y') + 543;
    $count = \App\Models\RepairRequest::whereYear('created_at', date('Y'))->count() + 1;
    $nextRequestCode = 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    if (!function_exists('getDeviceIcon')) {
        function getDeviceIcon($deviceName) {
            $name = mb_strtolower($deviceName);
            if (str_contains($name, 'notebook') || str_contains($name, '????????') || str_contains($name, '????????') || str_contains($name, 'laptop')) {
                return 'fa-laptop';
            } elseif (str_contains($name, 'mouse') || str_contains($name, '?????')) {
                return 'fa-computer-mouse';
            } elseif (str_contains($name, 'keyboard') || str_contains($name, '?????????') || str_contains($name, '?????????')) {
                return 'fa-keyboard';
            } elseif (str_contains($name, 'printer') || str_contains($name, '????????????') || str_contains($name, '??????????')) {
                return 'fa-print';
            } elseif (str_contains($name, 'server') || str_contains($name, '???????????')) {
                return 'fa-server';
            } elseif (str_contains($name, 'network') || str_contains($name, 'router') || str_contains($name, 'switch') || str_contains($name, '?????????')) {
                return 'fa-network-wired';
            } elseif (str_contains($name, 'monitor') || str_contains($name, '??') || str_contains($name, '???????????')) {
                return 'fa-display';
            } else {
                return 'fa-desktop';
            }
        }
    }

@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่วนแจ้งซ่อม - วิทยาลัยเทคนิคยะลา</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Flatpickr (Thai Datepicker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #DBEAFE; }
        .flatpickr-calendar {
            font-family: 'Prompt', sans-serif !important;
            border-radius: 1rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e2e8f0 !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #2563eb !important;
            border-color: #2563eb !important;
        }
        .blueprint-clean-bg {
            background-image: linear-gradient(to right, rgba(37, 99, 235, 0.007) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(37, 99, 235, 0.007) 1px, transparent 1px);
            background-size: 20px 20px;
        }
        @keyframes glow-blue {
            0%, 100% { box-shadow: 0 0 5px rgba(37, 99, 235, 0.2); border-color: rgba(37, 99, 235, 0.4); }
            50% { box-shadow: 0 0 15px rgba(37, 99, 235, 0.5); border-color: rgba(37, 99, 235, 1); }
        }
        .urgent-pulse { animation: glow-blue 2s infinite ease-in-out; }
        .white-card { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -10px rgba(148, 163, 184, 0.15); }

        /* Star Rating System */
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 0.35rem; }
        .star-rating input { display: none; }
        .star-rating label { color: #e2e8f0; cursor: pointer; font-size: 1.75rem; transition: color 0.2s; }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #fbbf24; /* amber-400 */
            text-shadow: 0 0 10px rgba(251, 191, 36, 0.4);
        }

    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased h-screen w-full max-w-full flex flex-col font-semibold selection:bg-blue-500 selection:text-white overflow-hidden">

    @include('dashboards.teacher.header')

    <div class="flex-1 flex flex-row w-full h-full overflow-hidden relative">
        @include('dashboards.teacher.sidebar')

        <main class="flex-1 min-w-0 h-full overflow-y-auto bg-slate-100 p-3 sm:p-5 md:p-8 pb-24 lg:pb-8">
            <section class="w-full max-w-7xl mx-auto">


            <!-- Tab 1: แจ้งซ่อม (Unified Single-Page UI) -->
            <div id="tab-report" class="tab-content block space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                
                <form action="{{ route('repair.store') }}" method="POST" enctype="multipart/form-data" class="w-full" onsubmit="return prepareSubmit(this)">
                    @csrf
                    
                    @if(session('success'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: 'แจ้งซ่อมสำเร็จ!',
                                    icon: 'success',
                                    heightAuto: false,
                                    confirmButtonText: 'ตกลง',
                                    confirmButtonColor: '#3B82F6',
                                    customClass: {
                                        popup: 'rounded-[2rem]',
                                        title: 'text-2xl font-black text-slate-800',
                                        confirmButton: 'px-8 py-3 rounded-xl font-bold text-lg'
                                    }
                                });
                            });
                        </script>
                    @endif

                    @if(session('error'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: 'ไม่สามารถแจ้งซ่อมได้',
                                    text: '{{ session('error') }}',
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
                            });
                        </script>
                    @endif

                    <!-- Unified Premium Container -->
                    <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden">
                        
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 px-6 py-5 relative overflow-hidden">
                            <!-- Background Pattern -->
                            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-[14px] bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg border border-white/30 shadow-inner">
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-black text-white tracking-wide">แบบฟอร์มแจ้งซ่อม</h2>
                                        <div class="mt-1 inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-lg border border-white/30 text-white shadow-sm">
                                            <i class="fa-solid fa-qrcode text-blue-200"></i>
                                            <span class="text-sm font-semibold tracking-wide">รหัสการแจ้งซ่อม:</span>
                                            <span class="text-sm font-mono font-bold tracking-wider">{{ $nextRequestCode }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="px-4 py-2 bg-white/10 backdrop-blur-sm rounded-xl border border-white/10 text-white flex items-center gap-2 text-sm">
                                        <i class="fa-regular fa-calendar"></i> 
                                        <span id="live-clock" class="font-bold">วันที่แจ้ง: {{ (int)date('d') }} {{ $thai_months[date('M')] }} {{ date('Y') + 543 }} เวลา {{ date('H:i') }} น.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Body (Single Page Layout) -->
                        <div class="p-4 sm:p-6 md:p-8">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-x-10 gap-y-8">
                                
                                <!-- ข้อมูลผู้แจ้ง (Meta Info) - Rebalanced Horizontal Row -->
                                <div class="lg:col-span-12 flex flex-col md:flex-row items-center justify-between gap-6 bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                                    <div class="flex items-center gap-5 w-full md:w-auto">
                                        <div class="shrink-0">
                                            @if($user->profile_photo)
                                                <img src="{{ url('uploads/profiles/' . $user->profile_photo) }}" alt="Profile" class="w-16 h-16 rounded-full object-cover shadow-sm border-2 border-white">
                                            @else
                                                <div class="w-16 h-16 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center text-blue-600 font-black text-2xl shadow-sm">
                                                    {{ mb_substr($user->first_name ?? $user->username, 0, 1, 'UTF-8') }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">ผู้แจ้งซ่อม</p>
                                            <p class="font-black text-slate-800 text-lg mb-1">{{ $user->first_name ? $user->first_name . ' ' . $user->last_name : $user->username }}</p>
                                            
                                            @if($user->department_group_name || $user->section_name)
                                                <div>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white text-slate-600 text-xs font-bold border border-slate-200 shadow-sm">
                                                        <i class="fa-solid fa-building-user text-blue-500"></i>
                                                        {{ trim(($user->department_group_name ? $user->department_group_name . ' ' : '') . $user->section_name) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                                        <div class="flex-shrink-0 w-full md:w-auto bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                                                <i class="fa-solid fa-phone"></i>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">เบอร์โทรศัพท์ติดต่อ</p>
                                                <input type="text" name="contact_number" value="{{ $user->phone ?? $user->phone_number ?? '' }}" placeholder="ระบุเบอร์โทร..." class="text-sm px-0 py-1 bg-transparent border-0 border-b-2 border-slate-200 focus:outline-none focus:border-blue-500 w-full max-w-[200px] font-bold text-slate-700 transition-all placeholder:text-slate-300">
                                            </div>
                                        </div>

                                        <div class="flex-shrink-0 w-full md:w-auto bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-center gap-2 min-w-[220px]">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider pl-1">ระดับความเร่งด่วน</p>
                                            <div class="flex items-center gap-2">
                                                <label class="cursor-pointer group relative m-0 w-1/2">
                                                    <input type="radio" name="priority" value="ปกติ" class="peer sr-only" checked>
                                                    <div class="px-3 py-1.5 rounded-lg border-2 border-slate-100 bg-slate-50 text-slate-500 font-bold text-xs flex items-center justify-center gap-1.5 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-600 transition-all hover:border-slate-200 shadow-sm">
                                                        <i class="fa-solid fa-check-circle"></i> ปกติ
                                                    </div>
                                                </label>
                                                <label class="cursor-pointer group relative m-0 w-1/2">
                                                    <input type="radio" name="priority" value="เร่งด่วน" class="peer sr-only">
                                                    <div class="px-3 py-1.5 rounded-lg border-2 border-slate-100 bg-slate-50 text-slate-500 font-bold text-xs flex items-center justify-center gap-1.5 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-600 transition-all hover:border-slate-200 shadow-sm">
                                                        <i class="fa-solid fa-bolt"></i> เร่งด่วน
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ข้อมูลอุปกรณ์ (Equipment) -->
                                <div class="lg:col-span-12">
                                    <h3 class="text-sm font-black text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                                        <i class="fa-solid fa-desktop text-blue-500"></i> ส่วนที่ 1: ข้อมูลอุปกรณ์
                                    </h3>
                                    @php
                                        $dbDevices = \App\Models\Device::with(['building', 'room'])->whereNotNull('equipment_id')->get();
                                        $dbBuildings = \App\Models\Building::with('rooms')->orderBy('building_id')->get();
                                        
                                        // ใช้ข้อมูลฐาน AssetKind (ข้อมูลที่จัดการโดยแอดมินในส่วนข้อมูลพื้นฐาน)
                                        $assetKinds = \App\Models\AssetKind::all();
                                        
                                        $catKindMap = [];
                                        
                                        foreach($assetKinds as $ak) {
                                            if ($ak->category_code) {
                                                $catCode = trim($ak->category_code);
                                                if ($catCode === 'น' || empty($catCode)) continue;
                                                if (!isset($catKindMap[$catCode])) {
                                                    $catKindMap[$catCode] = [];
                                                }
                                                if ($ak->name) {
                                                    $name = trim($ak->name);
                                                    if ($name !== 'น' && $name !== $catCode && !in_array($name, $catKindMap[$catCode])) {
                                                        $catKindMap[$catCode][] = $name;
                                                    }
                                                }
                                            }
                                        }

                                        // เสริมข้อมูลให้ตรงกับฐานข้อมูลจริงของระบบ
                                        if (empty($catKindMap['ฮาร์ดแวร์'])) {
                                            $catKindMap['ฮาร์ดแวร์'] = ['โน้ตบุ๊ค', 'เมาส์', 'แป้นพิมพ์'];
                                        }
                                        if (empty($catKindMap['ระบบเครือข่าย'])) {
                                            $catKindMap['ระบบเครือข่าย'] = ['สายแลน', 'wifi', 'อุปกรณ์กระจายสัญญาณ'];
                                        }
                                        if (empty($catKindMap['ซอฟต์แวร์'])) {
                                            $catKindMap['ซอฟต์แวร์'] = ['OS ซอฟต์แวร์สำนักงาน', 'OS ระบบปฎิบัติการ'];
                                        }
                                        if (empty($catKindMap['IOT'])) {
                                            $catKindMap['IOT'] = ['IOT', 'เซ็นเซอร์'];
                                        }
                                        
                                        $categories = array_keys($catKindMap);
                                        $allKinds = [];
                                        foreach($catKindMap as $kinds) {
                                            foreach($kinds as $k) {
                                                if(!in_array($k, $allKinds)) $allKinds[] = $k;
                                            }
                                        }

                                        // รวบรวมครุภัณฑ์เริ่มต้นให้ครบทุกหมวดหมู่และทุกชนิดทรัพย์สิน
                                        $existingCodes = [];
                                        $allDevicesList = [];
                                        foreach($dbDevices as $d) {
                                            $effCode = trim(($d->device_code && $d->device_code !== '-') ? $d->device_code : ($d->equipment_id ?? ''));
                                            if ($effCode) {
                                                $existingCodes[strtolower($effCode)] = true;
                                            }
                                            $allDevicesList[] = [
                                                'equipment_id' => $d->equipment_id,
                                                'device_code' => $effCode ?: $d->equipment_id,
                                                'name' => $d->name ?: ($d->equipment_name ?: $d->type),
                                                'type' => $d->type ?: ($d->name ?: $d->equipment_name),
                                                'category' => $d->category ?: 'ฮาร์ดแวร์',
                                                'brand' => $d->brand ?? '',
                                                'model' => $d->model ?? '',
                                                'serial' => ($d->serial && $d->serial !== '-') ? $d->serial : '',
                                                'building_id' => $d->building_id ?? '',
                                                'floor' => $d->floor ?? '',
                                                'room_id' => $d->room_id ?? ''
                                            ];
                                        }
                                    @endphp
                                    <script>
                                        window.teacherCatKindMap = {!! json_encode($catKindMap, JSON_UNESCAPED_UNICODE) !!};
                                        window.teacherAllKinds = {!! json_encode($allKinds, JSON_UNESCAPED_UNICODE) !!};
                                        window.teacherBuildingsData = {!! json_encode($dbBuildings, JSON_UNESCAPED_UNICODE) !!};
                                        window.teacherDevicesData = {!! json_encode($allDevicesList, JSON_UNESCAPED_UNICODE) !!};

                                    </script>

                                    <!-- ส่วนที่ 1: ข้อมูลทรัพย์สิน / อุปกรณ์ -->
                                    <div class="space-y-4 mb-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <!-- ประเภททรัพย์สิน -->
                                            <div>
                                                <label class="text-slate-700 font-bold mb-2 text-xs flex items-center justify-between">
                                                    <span class="flex items-center gap-1.5">
                                                        <i class="fa-solid fa-layer-group text-blue-500 text-xs"></i>
                                                        <span>ประเภททรัพย์สิน</span>
                                                        <span class="text-red-500 text-xs">*</span>
                                                    </span>
                                                </label>
                                                <div class="relative">
                                                    <select id="category-filter" onchange="updateKindDropdown(); if(typeof filterDevices==='function') filterDevices(); if(typeof updateSymptoms==='function') updateSymptoms();" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-700 font-bold text-sm transition-all appearance-none shadow-sm hover:border-slate-300 cursor-pointer">
                                                        <option value="">กรุณาเลือกประเภททรัพย์สิน</option>
                                                        @foreach($categories as $cat)
                                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                                        @endforeach
                                                    </select>
                                                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                                                </div>
                                            </div>

                                            <!-- ชนิดทรัพย์สิน -->
                                            <div>
                                                <label class="text-slate-700 font-bold mb-2 text-xs flex items-center justify-between">
                                                    <span class="flex items-center gap-1.5">
                                                        <i class="fa-solid fa-shapes text-blue-500 text-xs"></i>
                                                        <span>ชนิดทรัพย์สิน</span>
                                                        <span class="text-red-500 text-xs">*</span>
                                                    </span>
                                                </label>
                                                <div class="relative">
                                                    <select id="kind-filter" onchange="onKindChange()" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-700 font-bold text-sm transition-all appearance-none shadow-sm hover:border-slate-300 cursor-pointer">
                                                        <option value="">-- กรุณาเลือกประเภทก่อน --</option>
                                                    </select>
                                                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- รหัสครุภัณฑ์ / อุปกรณ์ / โปรแกรม (ย้ายมาไว้ด้านล่างของประเภทและชนิดทรัพย์สิน) -->
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label id="device-select-label" class="text-slate-700 font-bold text-xs flex items-center gap-1.5 truncate">
                                                    <i id="device-select-icon" class="fa-solid fa-barcode text-blue-500 text-xs"></i>
                                                    <span id="device-select-title" class="truncate">รหัสครุภัณฑ์ / อุปกรณ์</span>
                                                </label>
                                                <span id="device-select-subtitle" class="text-[10px] text-slate-400 font-semibold bg-slate-100 px-2 py-0.5 rounded-full shrink-0">ไม่บังคับ</span>
                                            </div>
                                            <div class="relative group opacity-75" id="device-combobox-wrapper" onclick="handleDeviceWrapperClick(event)">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-blue-500 transition-colors pointer-events-none z-10">
                                                    <i id="device-input-icon" class="fa-solid fa-barcode text-sm"></i>
                                                </span>
                                                <input type="text" 
                                                       name="device_id" 
                                                       id="device-select-input" 
                                                       placeholder="เลือกประเภทและชนิดทรัพย์สินก่อน..." 
                                                       autocomplete="off"
                                                       disabled
                                                       onfocus="openDeviceDropdown()"
                                                       onclick="openDeviceDropdown()"
                                                       oninput="handleDeviceInput(this.value)"
                                                       class="w-full pl-11 pr-20 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-400 font-normal text-sm transition-all shadow-none cursor-not-allowed placeholder:text-slate-400 select-none">
                                                
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 z-10">
                                                    <button type="button" 
                                                            id="clear-device-btn" 
                                                            onclick="clearDeviceInput()" 
                                                            class="text-slate-300 hover:text-red-500 text-xs hidden p-1 cursor-pointer transition-colors" 
                                                            title="ล้างข้อมูล">
                                                        <i class="fa-solid fa-circle-xmark text-sm"></i>
                                                    </button>
                                                    <button type="button" 
                                                            id="toggle-device-dropdown-btn" 
                                                            onclick="toggleDeviceDropdown()" 
                                                            disabled
                                                            class="text-slate-300 p-1 rounded-lg transition cursor-not-allowed opacity-40 pointer-events-none" 
                                                            title="กรุณาเลือกประเภทและชนิดทรัพย์สินก่อน">
                                                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" id="device-dropdown-chevron"></i>
                                                    </button>
                                                </div>

                                                <!-- Custom Clean Dropdown Menu -->
                                                <div id="device-custom-dropdown" 
                                                     class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden z-50 py-1 transition-all duration-150">
                                                    <div id="device-dropdown-list" class="max-h-56 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                                                        <!-- Dynamic items rendered via JS -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="type" id="type-input" value="ทั่วไป">
                                    </div>
                                </div>

                                <!-- ส่วนที่ 2: สถานที่ -->
                                <div class="lg:col-span-12">
                                    <h3 class="text-sm font-black text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                                        <i class="fa-solid fa-location-dot text-emerald-500"></i> ส่วนที่ 2: สถานที่
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                        <!-- อาคาร -->
                                        <div>
                                            <label class="text-slate-700 font-bold mb-2 text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-building text-emerald-500 text-xs"></i>
                                                <span>อาคาร</span>
                                                <span class="text-red-500 text-xs">*</span>
                                            </label>
                                            <div class="relative">
                                                <select name="building" id="building-select" onchange="updateFloors()" class="w-full pl-4 pr-8 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-slate-700 font-bold text-sm transition-all appearance-none cursor-pointer hover:border-slate-300 shadow-sm">
                                                    <option value="">เลือกอาคาร...</option>
                                                    @foreach($dbBuildings as $b)
                                                        <option value="{{ $b->building_id }}">{{ $b->building_name }}</option>
                                                    @endforeach
                                                </select>
                                                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                                            </div>
                                        </div>

                                        <!-- ชั้น -->
                                        <div>
                                            <label class="text-slate-700 font-bold mb-2 text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-stairs text-emerald-500 text-xs"></i>
                                                <span>ชั้น</span>
                                                <span class="text-red-500 text-xs">*</span>
                                            </label>
                                            <div class="relative">
                                                <select name="floor" id="floor-select" onchange="updateRooms()" class="w-full pl-4 pr-8 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-slate-700 font-bold text-sm transition-all appearance-none cursor-pointer disabled:opacity-60 shadow-sm">
                                                    <option value="">เลือกชั้น...</option>
                                                </select>
                                                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                                            </div>
                                        </div>

                                        <!-- ห้อง -->
                                        <div>
                                            <label class="text-slate-700 font-bold mb-2 text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-door-open text-emerald-500 text-xs"></i>
                                                <span>ห้อง</span>
                                                <span class="text-red-500 text-xs">*</span>
                                            </label>
                                            <div class="relative">
                                                <select name="room" id="room-select" class="w-full pl-4 pr-8 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-slate-700 font-bold text-sm transition-all appearance-none cursor-pointer disabled:opacity-60 shadow-sm">
                                                    <option value="">เลือกห้อง...</option>
                                                </select>
                                                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                                            </div>
                                        </div>
                                        <input type="hidden" name="preferred_date" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <!-- ส่วนที่ 3: รายละเอียดปัญหา -->
                                <div class="lg:col-span-12">
                                    <h3 class="text-sm font-black text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                                        <i class="fa-solid fa-circle-exclamation text-amber-500"></i> ส่วนที่ 3: รายละเอียดปัญหา
                                    </h3>
                                    <div>
                                        <!-- อาการเสีย Checkboxes -->
                                        <div class="flex items-center justify-between mb-2.5">
                                            <label class="text-slate-700 font-bold text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-list-check text-amber-500 text-xs"></i>
                                                <span>อาการเสียที่พบ</span>
                                            </label>
                                            <span class="text-[11px] text-slate-400 font-medium">สามารถเลือกได้มากกว่า 1 อาการ</span>
                                        </div>
                                        <div id="symptoms-container" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-5">
                                            <!-- อาการจะถูกดึงมาแสดงด้วย Javascript ตามประเภทและชนิดทรัพย์สิน -->
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="เปิดเครื่องไม่ติด / ไฟไม่เข้า" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">เปิดเครื่องไม่ติด / ไฟไม่เข้า</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="เปิดติดแล้วดับ / เครื่องดับเอง" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">เปิดติดแล้วดับ / เครื่องดับเอง</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="เปิดติดแต่ภาพไม่ขึ้น / หน้าจอดำ" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">เปิดติดแต่ภาพไม่ขึ้น / หน้าจอดำ</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="หน้าจอเป็นเส้น / จอกระพริบ" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">หน้าจอเป็นเส้น / จอกระพริบ</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="หน้าจอแตก / ชำรุดเสียหาย" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">หน้าจอแตก / ชำรุดเสียหาย</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="อุปกรณ์แตกหัก / พอร์ตเชื่อมต่อชำรุดเสียหาย" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">อุปกรณ์แตกหัก / พอร์ตเชื่อมต่อชำรุดเสียหาย</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="แป้นพิมพ์ / เมาส์ / ทัชแพด ไม่ทำงาน" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">แป้นพิมพ์ / เมาส์ / ทัชแพด ไม่ทำงาน</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="มีเสียงดังผิดปกติ (พัดลม / อุปกรณ์ภายใน)" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">มีเสียงดังผิดปกติ (พัดลม / อุปกรณ์ภายใน)</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm">
                                                <input type="checkbox" name="symptoms[]" value="เครื่องร้อนจัดผิดปกติ / พัดลมไม่หมุน" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">เครื่องร้อนจัดผิดปกติ / พัดลมไม่หมุน</span>
                                            </label>
                                            <label class="flex items-start gap-3 p-3.5 border border-slate-200/90 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition-all group bg-white shadow-sm sm:col-span-2">
                                                <input type="checkbox" name="symptoms[]" value="อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)" class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 shrink-0 cursor-pointer" onchange="document.getElementById('additional-details').focus()">
                                                <span class="text-xs sm:text-sm font-medium text-slate-700 leading-snug group-hover:text-slate-900 select-none">อื่นๆ (ระบุในรายละเอียดเพิ่มเติม)</span>
                                            </label>
                                        </div>

                                        <!-- รายละเอียดเพิ่มเติม -->
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-slate-700 font-bold text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-pen-to-square text-amber-500 text-xs"></i>
                                                <span>รายละเอียดเพิ่มเติม</span>
                                            </label>
                                            <span class="text-[11px] text-slate-400 font-medium">ระบุเพิ่มเติมหากมี</span>
                                        </div>
                                        <textarea id="additional-details" name="additional_details" rows="3" placeholder="ระบุรายละเอียดอาการเสียเพิ่มเติม เช่น ใช้งานไม่ได้ตั้งแต่ช่วงเช้า, มีกลิ่นไหม้, หรือแจ้งข้อความ error..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all text-sm font-medium text-slate-700 placeholder:text-slate-400 shadow-sm hover:border-slate-300 resize-none"></textarea>
                                        
                                        <!-- แนบรูปภาพประกอบปัญหา / ความเสียหาย -->
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="text-slate-700 font-bold text-xs flex items-center gap-1.5">
                                                    <i class="fa-solid fa-camera text-blue-500 text-xs"></i>
                                                    <span>แนบรูปภาพประกอบปัญหา / ความเสียหาย</span>
                                                </label>
                                                <span class="text-[11px] text-slate-400 font-medium bg-slate-100 px-2.5 py-0.5 rounded-full">ไม่บังคับ (สูงสุด 5MB)</span>
                                            </div>

                                            <div id="repair-image-dropzone" class="relative border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-2xl p-4 sm:p-5 text-center bg-slate-50/70 hover:bg-blue-50/20 transition-all cursor-pointer group">
                                                <input type="file" 
                                                       id="repair-image-input" 
                                                       name="image" 
                                                       accept="image/png, image/jpeg, image/jpg, image/webp" 
                                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                       onchange="handleRepairImageSelect(this)">
                                                
                                                <!-- Placeholder State -->
                                                <div id="repair-image-placeholder" class="flex flex-col items-center justify-center py-2 sm:py-3">
                                                    <div class="w-12 h-12 mb-2.5 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                                        <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                                                    </div>
                                                    <p class="text-sm font-bold text-slate-700 mb-0.5 group-hover:text-blue-600 transition-colors">
                                                        คลิกเพื่อเลือกรูปภาพ หรือ ลากไฟล์รูปภาพมาวางที่นี่
                                                    </p>
                                                    <p class="text-xs text-slate-400">
                                                        รองรับไฟล์ JPG, PNG, WEBP หรือถ่ายรูปจากกล้อง (ขนาดไม่เกิน 5 MB)
                                                    </p>
                                                </div>

                                                <!-- Preview State (Hidden by default) -->
                                                <div id="repair-image-preview-container" class="hidden relative flex flex-col sm:flex-row items-center gap-4 bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm text-left">
                                                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 shadow-inner">
                                                        <img id="repair-image-preview" src="" alt="ตัวอย่างภาพที่แนบ" class="w-full h-full object-cover">
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[11px] font-bold border border-blue-100">
                                                                <i class="fa-solid fa-image"></i> รูปภาพที่เลือก
                                                            </span>
                                                        </div>
                                                        <p id="repair-image-name" class="text-sm font-bold text-slate-800 truncate mb-1">filename.jpg</p>
                                                        <p id="repair-image-size" class="text-xs text-slate-400 mb-2">0 KB</p>
                                                        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-bold">
                                                            <i class="fa-solid fa-circle-check"></i> แนบไฟล์เรียบร้อยแล้ว
                                                        </span>
                                                    </div>
                                                    <div class="z-20 w-full sm:w-auto flex justify-end">
                                                        <button type="button" 
                                                                onclick="removeRepairImage(event)" 
                                                                class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs flex items-center justify-center gap-1.5 transition-all border border-red-200 cursor-pointer shadow-sm">
                                                            <i class="fa-solid fa-trash-can"></i> ลบรูป
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Hidden Description Field to send to backend -->
                                        <input type="hidden" name="description" id="final-description" required>
                                    </div>
                                </div>

                                <!-- Submit Section -->
                                <div class="lg:col-span-12 pt-6 mt-2 border-t border-slate-100 flex justify-end items-center">
                                    <button type="submit" class="w-full md:w-auto px-10 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black rounded-xl shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center cursor-pointer">
                                        <span>บันทึก</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Tab 2: ติดตามสถานะ -->
            <div id="tab-status" class="tab-content hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                <!-- Unified Premium Container -->
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden">
                    
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 px-6 py-5 relative overflow-hidden">
                        <!-- Background Pattern -->
                        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[14px] bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg border border-white/30 shadow-inner">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-white tracking-wide">ติดตามสถานะการซ่อม</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-6 md:p-8 bg-slate-50/30">
                        <div id="teacher-tracking-list" class="space-y-6 max-h-[700px] overflow-y-auto pr-2 custom-scrollbar">
                        @php
                            $activeJobs = $requests->reject(function($job) {
                                if (($job->type ?? '') === 'เบิกงบประมาณ') {
                                    return true;
                                }
                                if ($job->status === 'เสร็จสิ้น' && $job->rating == 0) {
                                    return false;
                                }
                                return str_starts_with($job->status, 'ซ่อมเสร็จ') || $job->status === 'เสร็จสิ้น' || str_starts_with($job->status, 'ยกเลิก') || $job->status === 'ไม่สามารถซ่อมได้';
                            });
                        @endphp
                    @if($activeJobs->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 bg-slate-50/50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-5xl text-slate-200 shadow-sm mb-6">
                            <i class="fa-solid fa-clipboard-check text-emerald-400"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-700">ไม่มีใบแจ้งซ่อมที่รอการดำเนินการ</h3>
                        <p class="text-slate-500 mt-2 font-medium">คุณสามารถส่งแจ้งซ่อมใหม่ได้ที่นี่ "แจ้งซ่อม"</p>
                    </div>
                    @else
                        @foreach($activeJobs as $job)
                        @php
                            // Stepper Logic
                            $isBudgetFlow = $job->budget > 0 || in_array($job->status, ['รออนุมัติงบประมาณ', 'ไม่อนุมัติงบ/ซ่อมไม่ได้', 'รอเปลี่ยนอะไหล่', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ']);
                            
                            $steps = [];
                            if ($isBudgetFlow) {
                                $steps = [
                                    ['label' => 'รับเรื่อง', 'icon' => 'fa-clipboard-list', 'status' => 'รอดำเนินการ', 'active' => false, 'done' => false],
                                    ['label' => 'มอบหมาย', 'icon' => 'fa-user-tag', 'status' => 'มอบหมายช่าง', 'active' => false, 'done' => false],
                                    ['label' => 'ตรวจสอบ', 'icon' => 'fa-gears', 'status' => 'กำลังดำเนินการ', 'active' => false, 'done' => false],
                                    ['label' => 'รอเปลี่ยนอะไหล่', 'icon' => 'fa-box-open', 'status' => 'รอเปลี่ยนอะไหล่', 'active' => false, 'done' => false],
                                    ['label' => 'กำลังซ่อม', 'icon' => 'fa-screwdriver-wrench', 'status' => 'กำลังซ่อม', 'active' => false, 'done' => false],
                                    ['label' => 'เสร็จสิ้น', 'icon' => 'fa-check-double', 'status' => 'เสร็จสิ้น', 'active' => false, 'done' => false],
                                ];
                            } else {
                                $steps = [
                                    ['label' => 'รับเรื่อง', 'icon' => 'fa-clipboard-list', 'status' => 'รอดำเนินการ', 'active' => false, 'done' => false],
                                    ['label' => 'มอบหมาย', 'icon' => 'fa-user-tag', 'status' => 'มอบหมายช่าง', 'active' => false, 'done' => false],
                                    ['label' => 'ตรวจสอบ', 'icon' => 'fa-gears', 'status' => 'กำลังดำเนินการ', 'active' => false, 'done' => false],
                                    ['label' => 'กำลังซ่อม', 'icon' => 'fa-screwdriver-wrench', 'status' => 'กำลังซ่อม', 'active' => false, 'done' => false],
                                    ['label' => 'เสร็จสิ้น', 'icon' => 'fa-check-double', 'status' => 'เสร็จสิ้น', 'active' => false, 'done' => false],
                                ];
                            }

                            // Determine Current Step Index based on Status
                            $currentStatus = $job->status;
                            $currentIndex = 0;
                            
                            if (in_array($currentStatus, ['รอมอบหมาย', 'รอดำเนินการ'])) {
                                $currentIndex = 0;
                            } elseif (in_array($currentStatus, ['มอบหมายช่าง', 'รอรับงาน'])) {
                                $currentIndex = 1;
                            } elseif (in_array($currentStatus, ['กำลังดำเนินการ'])) {
                                $currentIndex = 2;
                            } elseif (in_array($currentStatus, ['รออนุมัติงบประมาณ', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ', 'รอเปลี่ยนอะไหล่']) && $isBudgetFlow) {
                                $currentIndex = 3;
                            } elseif ($currentStatus === 'ไม่อนุมัติงบ/ซ่อมไม่ได้') {
                                $currentIndex = 3; // Stuck at budget
                                $steps[3]['label'] = 'ไม่อนุมัติงบ/ซ่อมไม่ได้';
                                $steps[3]['icon'] = 'fa-ban';
                            } elseif (in_array($currentStatus, ['กำลังซ่อม', 'รออะไหล่'])) {
                                $currentIndex = $isBudgetFlow ? 4 : 3;
                            } elseif ($currentStatus === 'เสร็จสิ้น') {
                                $currentIndex = count($steps) - 1;
                            }

                            // Calculate Badge and Icon UI
                            $statusText = $currentStatus;
                            $statusIcon = "fa-clock-rotate-left";
                            $statusBadge = "bg-slate-100 text-slate-600 border-slate-200"; 
                            $iconBg = "bg-slate-50 text-slate-500 border-slate-200";

                            if (in_array($currentStatus, ['รอมอบหมาย', 'รอดำเนินการ'])) {
                                $statusBadge = "bg-blue-50 text-blue-700 border-blue-200";
                                $iconBg = "bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 border-blue-200 shadow-sm shadow-blue-100";
                            } elseif (in_array($currentStatus, ['มอบหมายช่าง', 'รอรับงาน'])) {
                                $statusIcon = "fa-user-tag";
                                $statusBadge = "bg-indigo-50 text-indigo-700 border-indigo-200";
                                $iconBg = "bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 border-indigo-200 shadow-sm shadow-indigo-100";
                            } elseif (in_array($currentStatus, ['กำลังดำเนินการ'])) {
                                $statusIcon = "fa-gears";
                                $statusBadge = "bg-purple-50 text-purple-700 border-purple-200";
                                $iconBg = "bg-gradient-to-br from-purple-50 to-purple-100 text-purple-600 border-purple-200 shadow-sm shadow-purple-100";
                            } elseif (in_array($currentStatus, ['กำลังซ่อม', 'รออะไหล่'])) {
                                $statusIcon = "fa-screwdriver-wrench";
                                $statusBadge = "bg-cyan-50 text-cyan-700 border-cyan-200";
                                $iconBg = "bg-gradient-to-br from-cyan-50 to-cyan-100 text-cyan-600 border-cyan-200 shadow-sm shadow-cyan-100";
                            } elseif ($currentStatus === 'เสร็จสิ้น') {
                                $statusIcon = "fa-circle-check";
                                $statusBadge = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                $iconBg = "bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 border-emerald-200 shadow-sm shadow-emerald-100";
                            } elseif (in_array($currentStatus, ['รออนุมัติงบประมาณ', 'รอเปลี่ยนอะไหล่'])) {
                                $statusText = "รายละเอียดอะไหล่ที่ต้องเปลี่ยน";
                                $statusIcon = "fa-file-invoice-dollar";
                                $statusBadge = "bg-amber-50 text-amber-700 border-amber-200";
                                $iconBg = "bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 border-amber-200 shadow-sm shadow-amber-100";
                            } else {
                                $statusIcon = "fa-ban";
                                $statusBadge = "bg-rose-50 text-rose-700 border-rose-200";
                                $iconBg = "bg-gradient-to-br from-rose-50 to-rose-100 text-rose-600 border-rose-200 shadow-sm shadow-rose-100";
                            }
                        @endphp
                        
                        <div onclick="openDetailModal({
                                        code: '{{ $job->request_code }}',
                                        device: '{{ addslashes($job->device->name ?? 'อุปกรณ์ถูกลบ') }}',
                                        device_code: '{{ $job->display_device_code }}',
                                        location: '{{ addslashes($job->location) }}',
                                        type: '{{ addslashes($job->type) }}',
                                        reporter_name: '{{ addslashes($job->user->name ?? 'ไม่ระบุ') }}',
                                        asset_category: '{{ addslashes($job->device->category ?? 'ไม่ระบุ') }}',
                                        asset_kind: '{{ addslashes($job->type ?? 'ไม่ระบุ') }}',
                                        building: '{{ addslashes($job->device->building->building_name ?? $job->device->building->name ?? 'ไม่ระบุ') }}',
                                        floor: '{{ addslashes($job->device->floor ?? 'ไม่ระบุ') }}',
                                        room: '{{ addslashes($job->device->room->room_name ?? $job->device->room->room_number ?? 'ไม่ระบุ') }}',
                                        priority: '{{ $job->priority }}',
                                        status: '{{ $job->status }}',
                                        statusText: `{!! strip_tags($statusText) !!}`,
                                        description: '{{ base64_encode(nl2br(trim(str_replace('อาการเสีย:', '', e($job->description))))) }}',
                                        inspection_result: '{{ base64_encode(nl2br(e($job->inspection_result ?? ''))) }}',
                                        repair_details: '{{ base64_encode(nl2br(e($job->repair_details ?? $job->note ?? ''))) }}',
                                        assignee: '{{ addslashes($job->assignee->name ?? 'รอแอดมินมอบหมาย') }}',
                                        image: '{{ $job->image ? asset($job->image) : "" }}',
                                        created_at: '{{ $job->created_at->setTimezone('Asia/Bangkok')->format('d/m/Y H:i') }} น.',
                                        updated_at: '{{ $job->updated_at->setTimezone('Asia/Bangkok')->format('d/m/Y H:i') }} น.'
                                    })" class="group relative bg-white border border-slate-100/60 rounded-[2rem] p-6 sm:p-8 hover:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.1)] transition-all duration-500 overflow-hidden cursor-pointer">
                            <!-- Background Accent -->
                            <div class="absolute -right-20 -top-20 w-64 h-64 bg-gradient-to-br from-slate-50 to-slate-100 rounded-full blur-3xl opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>

                            <!-- Header -->
                            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl border {{ $iconBg }} shrink-0 relative overflow-hidden transition-transform duration-300 group-hover:scale-105">
                                        <!-- Shimmer effect -->
                                        <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/40 to-transparent group-hover:animate-[shimmer_1.5s_infinite]"></div>
                                        <i class="fa-solid {{ getDeviceIcon($job->device->name ?? '') }} relative z-10"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ $job->device->name ?? 'อุปกรณ์อื่นๆ' }}</h3>
                                        <div class="flex items-center gap-2 mt-1 font-mono">
                                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-100 shadow-sm">รหัสแจ้งซ่อม: {{ $job->request_code }}</span>
                                            <span class="text-slate-300">|</span> 
                                            <span class="text-xs font-semibold text-slate-500"><i class="fa-solid fa-qrcode mr-1 text-slate-400"></i>รหัสครุภัณฑ์: {{ $job->display_device_code }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1.5 font-sans">
                                            <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100 shadow-sm">ประเภท: {{ $job->device->category ?? 'ไม่ระบุ' }}</span>
                                            <span class="text-[10px] font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100 shadow-sm">ชนิด: {{ $job->device->deviceType->name ?? $job->type ?? 'ไม่ระบุ' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:items-end gap-2 shrink-0">
                                    <span class="px-3.5 py-1.5 rounded-xl text-[11px] font-black border flex items-center gap-1.5 shadow-sm {{ $statusBadge }}">
                                        <i class="fa-solid {{ $statusIcon }}"></i> {!! $statusText !!}
                                    </span>
                                    @if($job->priority === 'ด่วนที่สุด' && $job->status !== 'เสร็จสิ้น')
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200 animate-pulse shadow-sm">
                                            <i class="fa-solid fa-bolt"></i> ด่วนที่สุด
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Content Details (Glassmorphism inspired) -->
                            <div class="relative bg-gradient-to-br from-slate-50/80 to-white/50 rounded-[1.5rem] p-5 mb-8 border border-slate-100 shadow-sm backdrop-blur-sm">
                                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-blue-400 to-indigo-500 rounded-l-[1.5rem]"></div>
                                @php
                                    $shortDesc = $job->clean_symptom ?: 'ไม่ระบุอาการ';
                                @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 pl-3 mb-5">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-user-gear"></i></div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ช่างผู้รับผิดชอบ</span>
                                            <span class="text-xs font-bold text-slate-700 mt-0.5">{{ $job->assignee->name ?? 'รอเจ้าหน้าที่รับเรื่อง' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-regular fa-calendar-days"></i></div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">วันที่แจ้งซ่อม</span>
                                            <span class="text-xs font-bold text-slate-700 mt-0.5">{{ $job->created_at->setTimezone('Asia/Bangkok')->format('d/m/Y H:i') }} น.</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-location-dot"></i></div>
                                        <div class="flex flex-col">
                                            @php
                                                $displayLoc = str_replace('undefined', 'ไม่ระบุ', $job->location);
                                                if (str_contains($displayLoc, 'ห้อง -')) {
                                                    if ($job->device && $job->device->room) {
                                                        $realRoom = $job->device->room->room_code ?: $job->device->room->room_name;
                                                        if ($realRoom) {
                                                            $displayLoc = str_replace('ห้อง -', 'ห้อง ' . $realRoom, $displayLoc);
                                                        } else {
                                                            $displayLoc = str_replace(' ห้อง -', '', $displayLoc);
                                                        }
                                                    } else {
                                                        $displayLoc = str_replace(' ห้อง -', '', $displayLoc);
                                                    }
                                                }
                                            @endphp
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">อาคาร/ชั้น/ห้อง</span>
                                            <span class="text-xs font-bold text-slate-700 mt-0.5">{{ $displayLoc }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-sm text-slate-700 font-bold leading-relaxed pl-3 border-t border-slate-100/80 pt-5">
                                    <span class="text-slate-500 font-medium">อาการที่พบ:</span> {{ \Illuminate\Support\Str::limit($shortDesc, 80) }}
                                </div>
                                
                                @if($job->status === 'รอเปลี่ยนอะไหล่')
                                <div class="mt-4 bg-blue-50/80 border border-blue-200/60 rounded-xl p-3.5 flex gap-3.5 items-start shadow-sm" onclick="event.stopPropagation()">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-bold text-blue-800 text-[11px] uppercase tracking-wider block mb-0.5">รอดำเนินการซ่อม</span>
                                        <p class="text-blue-700/90 text-xs font-medium leading-relaxed">{{ $job->repair_details ?? 'ช่างกำลังรอดำเนินการซ่อม กรุณาบันทึกรายการค่าซ่อมเพื่อส่งข้อมูลให้เจ้าหน้าที่ซ่อมบำรุง' }}</p>
                                        @php
                                            $repairJobData = json_encode([
                                                'job_code'       => $job->request_code ?? '',
                                                'device'         => ($job->device->name ?? 'อุปกรณ์ไม่ระบุ'),
                                                'location'       => $job->location ?? '',
                                                'description'    => $job->description ?? '',
                                                'repair_details' => $job->repair_details ?? '',
                                            ], JSON_UNESCAPED_UNICODE);
                                        @endphp
                                        <button type="button" onclick="goToRepairRecord({{ $repairJobData }})" class="mt-3 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-file-pen"></i>
                                            บันทึกค่าซ่อม
                                        </button>
                                    </div>
                                </div>
                                @elseif($job->status === 'กำลังดำเนินการ' && $job->repair_evaluation === 'can_repair' && !empty($job->note))
                                <div class="mt-4 bg-amber-50/80 border border-amber-200/60 rounded-xl p-3.5 flex gap-3.5 items-start shadow-sm">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-bold text-amber-800 text-[11px] uppercase tracking-wider block mb-0.5">ข้อความจากช่างซ่อม</span>
                                        <p class="text-amber-700/90 text-xs font-medium leading-relaxed">{{ $job->note }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Sleek Stepper Timeline -->
                            <div class="relative px-2 sm:px-10 mb-8 mt-2">
                                <div class="flex items-center justify-between relative z-10 w-full">
                                    @foreach($steps as $index => $step)
                                        @php
                                            $isCompleted = $index < $currentIndex;
                                            $isActive = $index === $currentIndex;
                                            $isRejected = ($currentStatus === 'ไม่อนุมัติงบ/ซ่อมไม่ได้' && $isActive);
                                            
                                            $stepClass = "text-slate-400 bg-slate-50 border-slate-200";
                                            $lineClass = "bg-slate-100";
                                            
                                            if ($isCompleted) {
                                                $stepClass = "text-emerald-500 bg-emerald-50 border-emerald-500";
                                                $lineClass = "bg-gradient-to-r from-emerald-400 to-emerald-500";
                                            } elseif ($isActive) {
                                                if ($isRejected) {
                                                    $stepClass = "text-rose-600 bg-rose-50 border-rose-500 ring-4 ring-rose-50 shadow-md shadow-rose-200/50";
                                                } elseif ($step['label'] === 'รอเปลี่ยนอะไหล่') {
                                                    $stepClass = "text-amber-600 bg-amber-50 border-amber-500 ring-4 ring-amber-50 shadow-md shadow-amber-200/50";
                                                } elseif ($step['label'] === 'เสร็จสิ้น') {
                                                    $stepClass = "text-emerald-600 bg-emerald-50 border-emerald-500 ring-4 ring-emerald-50 shadow-md shadow-emerald-200/50";
                                                } else {
                                                    $stepClass = "text-blue-600 bg-blue-50 border-blue-500 ring-4 ring-blue-50 shadow-md shadow-blue-200/50";
                                                }
                                            }
                                        @endphp
                                        
                                        <!-- Step {{ $index + 1 }} -->
                                        <div class="flex flex-col items-center group/step cursor-default relative w-10">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base border-2 {{ $stepClass }} transition-all duration-300 z-10">
                                                <i class="fa-solid {{ $step['icon'] }}"></i>
                                            </div>
                                            <span class="absolute top-12 text-[10px] font-bold text-slate-600 tracking-wide whitespace-nowrap" style="left: 50%; transform: translateX(-50%);">{{ $step['label'] }}</span>
                                        </div>
                                        
                                        @if ($index < count($steps) - 1)
                                            <!-- Line {{ $index + 1 }} -->
                                            <div class="flex-1 h-1.5 {{ $lineClass }} -mx-2 transition-all duration-300 z-0 rounded-full"></div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <!-- Actions -->
                            <div class="relative pt-6 border-t border-slate-100 flex justify-end items-center gap-3">
                                @if($job->status === 'กำลังดำเนินการ' && $job->repair_evaluation === 'can_repair')
                                    <form action="{{ route('repair.update', $job->id) }}" method="POST" class="inline-block" onclick="event.stopPropagation();">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="รออะไหล่">
                                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 px-5 py-2.5 rounded-xl transition-all shadow-md shadow-blue-500/25 hover:shadow-lg hover:-translate-y-0.5">
                                            <i class="fa-solid fa-check-double"></i> รับทราบ
                                        </button>
                                    </form>
                                @endif
                                
                                @if($job->status === 'เสร็จสิ้น' && $job->rating == 0)
                                    <button
                                        data-job-id="{{ $job->id }}"
                                        data-request-code="{{ $job->request_code }}"
                                        data-device-name="{{ $job->device->name ?? 'อุปกรณ์ถูกลบ' }}"
                                        onclick="event.stopPropagation(); openEvalModal(this.getAttribute('data-job-id'), this.getAttribute('data-request-code'), this.getAttribute('data-device-name'))"
                                        class="inline-flex items-center gap-2 text-xs font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 px-5 py-2.5 rounded-xl transition-all duration-300 shadow-md shadow-amber-500/25 hover:shadow-lg hover:shadow-amber-500/40 hover:-translate-y-0.5">
                                        <i class="fa-solid fa-star"></i> ประเมินความพึงพอใจ
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @endif
                                        </div>
                    </div>
                </div>
            </div>


            <!-- Tab 5: ประวัติการซ่อมย้อนหลัง -->
            <div id="tab-history" class="tab-content hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                <!-- Unified Premium Container -->
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden flex flex-col">
                    
                    <!-- Header with Tactical Blue Gradient (Matching Technician Assigned Design) -->
                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 px-6 py-5 relative overflow-hidden">
                        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-xl border border-white/30 shadow-inner">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-xl font-black text-white tracking-wide">ดูประวัติงานซ่อมย้อนหลัง</h2>
                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-500/50 text-white text-[10px] font-extrabold border border-white/20">ตารางสรุปงาน</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full md:w-auto">
                                <div class="relative flex-1 md:w-72">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-100/70"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" id="history-search-input" onkeyup="syncHistorySearch(this.value)" placeholder="ค้นหารหัสใบงาน, ชื่ออุปกรณ์, อาการ, ช่าง..." class="w-full pl-11 pr-10 py-2 bg-white/15 border border-white/25 rounded-full text-xs sm:text-sm font-semibold text-white placeholder-blue-100/70 focus:outline-none focus:bg-white/25 focus:border-white/50 transition-all shadow-inner backdrop-blur-sm">
                                    <button type="button" onclick="clearHistorySearch()" id="btn-clear-search" class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 text-white/70 hover:text-white text-xs cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="flex items-center bg-white/15 p-1 rounded-xl border border-white/25 backdrop-blur-sm shrink-0">
                                    <button type="button" id="btn-view-card" onclick="setTeacherHistoryViewMode('card')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-white text-blue-700 shadow-sm cursor-pointer" title="แสดงแบบการ์ด">
                                        <i class="fa-solid fa-grip"></i>
                                        <span>การ์ด</span>
                                    </button>
                                    <button type="button" id="btn-view-table" onclick="setTeacherHistoryViewMode('table')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-white/80 hover:text-white hover:bg-white/10 cursor-pointer" title="แสดงแบบตาราง">
                                        <i class="fa-solid fa-table-list"></i>
                                        <span>ตาราง</span>
                                    </button>
                                    <button type="button" id="btn-view-chart-toggle" onclick="setTeacherHistoryViewMode('chart')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-white/80 hover:text-white hover:bg-white/10 cursor-pointer" title="ดูกราฟสถิติ">
                                        <i class="fa-solid fa-chart-pie"></i>
                                        <span>กราฟ</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-6 md:p-8 bg-slate-50/30">
                        @php
                            // Filter history jobs: include completed repairs, unrepairable/budget/spare part jobs, and cancelled requests
                            $historyJobs = $requests->filter(function($job) {
                                if (($job->type ?? '') === 'เบิกงบประมาณ') {
                                    return false;
                                }
                                $status = $job->status ?? '';
                                $budget = (float)($job->budget ?? 0);
                                $unrepairReason = $job->unrepairable_reason ?? optional($job->latestMaintenance)->unrepairable_reason ?? '';
                                $maintenanceResult = optional($job->latestMaintenance)->repair_result ?? '';
                                $hasProject = !empty($job->repair_project_id);

                                if (str_starts_with($status, 'ซ่อมเสร็จ') || $status === 'เสร็จสิ้น' || str_starts_with($status, 'ซ่อมสำเร็จ')) {
                                    return true;
                                }
                                if ($status === 'ไม่สามารถซ่อมได้' || 
                                    $status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้' || 
                                    in_array($status, ['รอเปลี่ยนอะไหล่', 'รออะไหล่', 'รอจัดซื้ออะไหล่', 'รออนุมัติงบประมาณ', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ']) ||
                                    $maintenanceResult === 'ไม่สามารถซ่อมได้' ||
                                    $maintenanceResult === 'รอเปลี่ยนอะไหล่' ||
                                    $budget > 0 ||
                                    $hasProject ||
                                    !empty($unrepairReason)) {
                                    return true;
                                }
                                if (str_starts_with($status, 'ยกเลิก')) {
                                    return true;
                                }
                                return false;
                            });

                            // Classify Reparability
                            $classifyReparability = function($job) {
                                $status = is_object($job) ? ($job->status ?? '') : (string)$job;
                                $unrepairReason = is_object($job) ? ($job->unrepairable_reason ?? optional($job->latestMaintenance)->unrepairable_reason ?? '') : '';
                                $maintenanceResult = is_object($job) ? (optional($job->latestMaintenance)->repair_result ?? '') : '';
                                $budget = is_object($job) ? (float)($job->budget ?? 0) : 0;
                                $hasProject = is_object($job) && !empty($job->repair_project_id);

                                // ถ้าซ่อมเสร็จสิ้น/สำเร็จ ถือว่าสามารถซ่อมได้เสมอ
                                if (in_array($status, ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์']) || str_starts_with($status, 'ซ่อมเสร็จ') || in_array($maintenanceResult, ['ซ่อมสำเร็จ', 'ซ่อมได้'])) {
                                    return 'repairable';
                                }

                                // 1. ตรวจสอบกลุ่มที่ต้องสั่งอะไหล่ / มีการเบิกงบ / ซ่อมไม่ได้
                                if ($status === 'ไม่สามารถซ่อมได้' || 
                                    $status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้' || 
                                    in_array($status, ['รอเปลี่ยนอะไหล่', 'รออะไหล่', 'รอจัดซื้ออะไหล่', 'รออนุมัติงบประมาณ', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ']) ||
                                    $maintenanceResult === 'ไม่สามารถซ่อมได้' ||
                                    $maintenanceResult === 'รอเปลี่ยนอะไหล่' ||
                                    !empty($unrepairReason) ||
                                    $budget > 0 ||
                                    $hasProject) {
                                    return 'unrepairable_parts';
                                }

                                // 2. งานที่ยกเลิก
                                if (str_starts_with($status, 'ยกเลิก')) {
                                    return 'cancelled';
                                }

                                // 3. สามารถซ่อมได้
                                return 'repairable';
                            };

                            // Classify Category
                            $classifyCategory = function($job) {
                                if (!is_object($job)) return 'hardware';

                                $devCat = $job->device->category ?? $job->item->device_type ?? '';
                                if (in_array($devCat, ['ฮาร์ดแวร์', 'hardware', 'คอมพิวเตอร์'])) return 'hardware';
                                if (in_array($devCat, ['ซอฟต์แวร์', 'software'])) return 'software';
                                if (in_array($devCat, ['ระบบเครือข่าย', 'network', 'ไอโอที', 'iot'])) return 'network_other';

                                $category = $job->device->category ?? $job->item->device_type ?? '';
                                $typeName = $job->device->deviceType->name ?? $job->device->assetKind->name ?? $job->item->equipment_name ?? $job->type ?? '';
                                $deviceName = $job->device->name ?? $job->item->equipment_name ?? '';
                                $desc = $job->clean_symptom ?? $job->description ?? '';

                                $haystack = mb_strtolower($category . ' ' . $typeName . ' ' . $deviceName . ' ' . $desc, 'UTF-8');
                                if (str_contains($haystack, 'network') || str_contains($haystack, 'lan') || str_contains($haystack, 'wifi') || str_contains($haystack, 'switch') || str_contains($haystack, 'router') || str_contains($haystack, 'เครือข่าย') || str_contains($haystack, 'อินเทอร์เน็ต') || str_contains($haystack, 'สายแลน') || str_contains($haystack, 'สัญญาณ')) {
                                    return 'network_other';
                                }
                                if (str_contains($haystack, 'software') || str_contains($haystack, 'โปรแกรม') || str_contains($haystack, 'windows') || str_contains($haystack, 'office') || str_contains($haystack, 'ไวรัส') || str_contains($haystack, 'driver') || str_contains($haystack, 'ไดรเวอร์') || str_contains($haystack, 'จอฟ้า') || str_contains($haystack, 'ลง windows') || str_contains($haystack, 'ซอฟต์แวร์') || str_contains($haystack, 'ระบบปฏิบัติการ') || str_contains($haystack, 'ระบบปฎิบัติการ')) {
                                    return 'software';
                                }
                                return 'hardware';
                            };

                            $historyDataList = [];
                            $availableYears = [];
                            foreach($historyJobs as $job) {
                                $catKey = $classifyCategory($job);
                                $repKey = $classifyReparability($job);
                                $dateObj = \Carbon\Carbon::parse($job->created_at);
                                $yearGreg = (int)$dateObj->format('Y');
                                $thaiYear = $yearGreg + 543;
                                
                                if (!in_array($yearGreg, $availableYears)) {
                                    $availableYears[] = $yearGreg;
                                }

                                $historyDataList[] = [
                                    'id' => $job->id,
                                    'code' => $job->request_code,
                                    'category' => $catKey,
                                    'reparability' => $repKey,
                                    'budget' => (float)($job->budget ?? 0),
                                    'device_name' => $job->device->name ?? $job->item->equipment_name ?? 'อุปกรณ์ไอที',
                                    'asset_kind' => $job->asset_kind,
                                    'created_at' => $job->created_at ? $job->created_at->format('Y-m-d H:i:s') : '',
                                    'date_ymd' => $dateObj->format('Y-m-d'),
                                    'year' => $yearGreg,
                                    'thai_year' => $thaiYear,
                                    'month' => (int)$dateObj->format('n'),
                                    'day' => (int)$dateObj->format('j'),
                                    'status' => $job->status,
                                    'rating' => (int)($job->rating ?? 0),
                                ];
                            }
                            
                            $currentGregYear = (int)date('Y');
                            if (!in_array($currentGregYear, $availableYears)) {
                                $availableYears[] = $currentGregYear;
                            }
                            rsort($availableYears);

                            $thaiMonths = [
                                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                            ];
                        @endphp

                        <!-- Stat Summary Cards (Interactive Filter Matching Technician Assigned Style) -->
                        <div id="teacher-history-stat-cards" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                            <!-- 1. งานทั้งหมด -->
                            <div onclick="setHistoryFilter('all')" id="stat-card-all" class="stat-history-card active-stat bg-white rounded-2xl p-4 border border-blue-200 shadow-sm flex flex-col justify-between border-l-4 border-l-blue-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-black text-blue-700 uppercase tracking-wider">งานทั้งหมด</span>
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-list-check"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="stat-total-count" class="text-2xl sm:text-3xl font-black text-blue-600 tracking-tight">{{ $historyJobs->count() }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>
                            
                            <!-- 2. เสร็จสิ้น -->
                            <div onclick="setHistoryFilter('repairable')" id="stat-card-repairable" class="stat-history-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-emerald-500 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">เสร็จสิ้น</span>
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-circle-check"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="stat-repairable-count" class="text-2xl sm:text-3xl font-black text-emerald-600 tracking-tight">0</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 3. ซ่อมไม่ได้ -->
                            <div onclick="setHistoryFilter('unrepairable')" id="stat-card-unrepairable" class="stat-history-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-rose-500 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">ซ่อมไม่ได้</span>
                                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-circle-xmark"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="stat-unrepairable-only-count" class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight">0</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Optional Graph & Period Filters View (Toggled via [ กราฟ ]) -->
                        <div id="teacher-history-chart-view" class="hidden space-y-6 rounded-3xl bg-white p-5 sm:p-6 border border-slate-200/80 shadow-sm transition-all duration-300">
                            <!-- Filter Control Bar -->
                            <div class="bg-slate-50/70 rounded-2xl p-4 sm:p-5 border border-slate-200/80 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-blue-500/20">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-base">
                                            สรุปสถิติการแจ้งซ่อมและผลการซ่อมบำรุง
                                        </h3>
                                    </div>
                                </div>

                                <!-- Period Toggle Buttons & Date Controls -->
                                <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                                    <!-- Mode Selector Buttons -->
                                    <div class="inline-flex p-1 bg-white rounded-xl border border-slate-200 text-xs font-bold text-slate-600 shadow-xs">
                                        <button type="button" onclick="setHistoryTimeMode('all')" id="btn-mode-all" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold">ทั้งหมด</button>
                                        <button type="button" onclick="setHistoryTimeMode('day')" id="btn-mode-day" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-semibold">รายวัน</button>
                                        <button type="button" onclick="setHistoryTimeMode('month')" id="btn-mode-month" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-semibold">รายเดือน</button>
                                        <button type="button" onclick="setHistoryTimeMode('year')" id="btn-mode-year" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-semibold">รายปี</button>
                                    </div>

                                    <!-- Dynamic Date Selectors -->
                                    <div id="history-date-controls" class="flex items-center gap-2">
                                        <!-- Day Picker -->
                                        <div id="control-day" class="hidden flex items-center gap-1.5">
                                            <div class="relative flex items-center">
                                                <input type="text" id="history-filter-date" value="{{ date('d/m/') . (date('Y') + 543) }}" data-date-ymd="{{ date('Y-m-d') }}" onchange="applyHistoryFilter()" placeholder="วว/ดด/ปปปป" class="w-36 text-xs bg-white border border-slate-200 rounded-xl pl-3 pr-8 py-2 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs">
                                                <span class="absolute right-2.5 pointer-events-none text-slate-400 text-xs">
                                                    <i class="fa-regular fa-calendar-days"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Month Picker (Thai) -->
                                        <div id="control-month" class="hidden flex items-center gap-1.5">
                                            <select id="history-filter-month" onchange="applyHistoryFilter()"
                                                    class="text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs">
                                                <option value="1"  {{ (int)date('n') === 1  ? 'selected' : '' }}>มกราคม</option>
                                                <option value="2"  {{ (int)date('n') === 2  ? 'selected' : '' }}>กุมภาพันธ์</option>
                                                <option value="3"  {{ (int)date('n') === 3  ? 'selected' : '' }}>มีนาคม</option>
                                                <option value="4"  {{ (int)date('n') === 4  ? 'selected' : '' }}>เมษายน</option>
                                                <option value="5"  {{ (int)date('n') === 5  ? 'selected' : '' }}>พฤษภาคม</option>
                                                <option value="6"  {{ (int)date('n') === 6  ? 'selected' : '' }}>มิถุนายน</option>
                                                <option value="7"  {{ (int)date('n') === 7  ? 'selected' : '' }}>กรกฎาคม</option>
                                                <option value="8"  {{ (int)date('n') === 8  ? 'selected' : '' }}>สิงหาคม</option>
                                                <option value="9"  {{ (int)date('n') === 9  ? 'selected' : '' }}>กันยายน</option>
                                                <option value="10" {{ (int)date('n') === 10 ? 'selected' : '' }}>ตุลาคม</option>
                                                <option value="11" {{ (int)date('n') === 11 ? 'selected' : '' }}>พฤศจิกายน</option>
                                                <option value="12" {{ (int)date('n') === 12 ? 'selected' : '' }}>ธันวาคม</option>
                                            </select>
                                            @php $curY = (int)date('Y'); @endphp
                                            <select id="history-filter-month-year" onchange="applyHistoryFilter()"
                                                    class="text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs">
                                                @for($y = $curY + 5; $y >= 2015; $y--)
                                                    <option value="{{ $y }}" {{ $curY === $y ? 'selected' : '' }}>พ.ศ. {{ $y + 543 }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <!-- Year Picker -->
                                        <div id="control-year" class="hidden flex items-center gap-1.5">
                                            <select id="history-filter-year" onchange="applyHistoryFilter()" class="text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-xs">
                                                @php $currentYear = (int)date('Y'); @endphp
                                                @for($y = $currentYear + 5; $y >= 2015; $y--)
                                                    <option value="{{ $y }}" {{ $currentYear === $y ? 'selected' : '' }}>พ.ศ. {{ $y + 543 }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Reset Button -->
                                    <button type="button" onclick="resetHistoryFilter()" title="รีเซ็ตตัวกรอง" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-white rounded-xl border border-transparent hover:border-slate-200 transition-colors shadow-xs">
                                        <i class="fa-solid fa-rotate-right text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Charts Container (2 Columns) -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                                <!-- Bar Chart (7 Cols) -->
                                <div class="lg:col-span-7 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                                    <!-- Header with Title & Mode Switcher -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                        <div>
                                            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                <i class="fa-solid fa-chart-column text-blue-600"></i>
                                                <span id="chart-bar-title">สถิติเปรียบเทียบผลการซ่อมตามชนิดทรัพย์สิน</span>
                                            </h4>
                                        </div>
                                        <!-- View Mode Switcher -->
                                        <div class="inline-flex p-0.5 bg-slate-100 rounded-xl border border-slate-200 text-[10px] font-bold self-start sm:self-auto">
                                            <button type="button" onclick="setBarView('category')" id="btn-bar-cat" class="px-2.5 py-1 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold flex items-center gap-1">
                                                <i class="fa-solid fa-boxes-stacked text-[10px]"></i> ชนิดทรัพย์สิน
                                            </button>
                                            <button type="button" onclick="setBarView('timeline')" id="btn-bar-time" class="px-2.5 py-1 rounded-lg transition-all text-slate-500 hover:text-slate-800 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-calendar-days text-[10px]"></i> รายเดือน
                                            </button>
                                            <button type="button" onclick="setBarView('status')" id="btn-bar-stat" class="px-2.5 py-1 rounded-lg transition-all text-slate-500 hover:text-slate-800 font-semibold flex items-center gap-1">
                                                <i class="fa-solid fa-list-check text-[10px]"></i> สถานะงาน
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Dynamic Legend -->
                                    <div id="bar-legend-container" class="flex flex-wrap items-center justify-end gap-2 text-[11px] font-semibold text-slate-600 mb-2">
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 text-emerald-800">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 
                                            ซ่อมได้ <span id="bar-legend-rep-count" class="font-bold ml-0.5">0</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100 text-rose-800">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> 
                                            สั่งอะไหล่/ซ่อมไม่ได้ <span id="bar-legend-unrep-count" class="font-bold ml-0.5">0</span>
                                        </span>
                                    </div>

                                    <!-- Canvas -->
                                    <div class="relative h-[260px] w-full">
                                        <canvas id="historyBarChart"></canvas>
                                    </div>
                                </div>

                                <!-- Doughnut Chart (5 Cols) with View Switcher -->
                                <div class="lg:col-span-5 bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div>
                                            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                <i class="fa-solid fa-chart-pie text-purple-500"></i>
                                                <span id="donut-chart-title">สัดส่วนผลการซ่อม</span>
                                            </h4>
                                        </div>
                                        <!-- Donut Switcher Pills -->
                                        <div class="inline-flex p-0.5 bg-slate-100 rounded-xl border border-slate-200 text-[10px] font-bold">
                                            <button type="button" onclick="setDonutView('outcome')" id="btn-donut-outcome" class="px-2.5 py-1 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold">ผลการซ่อม</button>
                                            <button type="button" onclick="setDonutView('category')" id="btn-donut-category" class="px-2.5 py-1 rounded-lg transition-all text-slate-500 hover:text-slate-800">ประเภท</button>
                                        </div>
                                    </div>
                                    <div class="relative h-[200px] w-full flex items-center justify-center">
                                        <canvas id="historyDonutChart"></canvas>
                                    </div>
                                    <!-- Legend / Stats Bar underneath -->
                                    <div id="donut-legend-outcome" class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 text-center">
                                        <div class="p-2 rounded-xl bg-emerald-50/70 border border-emerald-200/60">
                                            <span class="text-[10px] font-bold text-emerald-700 block"><i class="fa-solid fa-circle-check mr-1"></i> สามารถซ่อมได้</span>
                                            <span id="donut-repairable-count" class="text-xs font-black text-emerald-900">0 รายการ</span>
                                        </div>
                                        <div class="p-2 rounded-xl bg-rose-50/70 border border-rose-200/60">
                                            <span class="text-[10px] font-bold text-rose-700 block"><i class="fa-solid fa-triangle-exclamation mr-1"></i> ซ่อมไม่ได้/สั่งอะไหล่</span>
                                            <span id="donut-unrepairable-count" class="text-xs font-black text-rose-900">0 รายการ</span>
                                        </div>
                                    </div>
                                    <div id="donut-legend-category" class="hidden grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 text-center">
                                        <div class="p-1.5 rounded-xl bg-indigo-50/60 border border-indigo-100/50">
                                            <span class="text-[10px] font-bold text-indigo-700 block">ฮาร์ดแวร์</span>
                                            <span id="donut-hw-count" class="text-xs font-black text-indigo-900">0 รายการ</span>
                                        </div>
                                        <div class="p-1.5 rounded-xl bg-purple-50/60 border border-purple-100/50">
                                            <span class="text-[10px] font-bold text-purple-700 block">ซอฟต์แวร์</span>
                                            <span id="donut-sw-count" class="text-xs font-black text-purple-900">0 รายการ</span>
                                        </div>
                                        <div class="p-1.5 rounded-xl bg-teal-50/60 border border-teal-100/50">
                                            <span class="text-[10px] font-bold text-teal-700 block">เครือข่าย</span>
                                            <span id="donut-net-count" class="text-xs font-black text-teal-900">0 รายการ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End #teacher-history-chart-view -->

                        <!-- 2. ส่วนแสดงผลแบบตารางรายการ (Table View Container) -->
                        <div id="teacher-history-table-view" class="space-y-4">


                            <!-- 1. ส่วนแสดงผลแบบตาราง (Table View Container - Hidden by default) -->
                            <div id="teacher-history-table-container" class="hidden overflow-x-auto w-full max-w-full text-xs border border-slate-200/80 rounded-2xl bg-white shadow-sm custom-scrollbar" style="-webkit-overflow-scrolling: touch;">
                                <table class="w-full text-left border-collapse" id="history-data-table">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[11px] tracking-wider whitespace-nowrap">
                                            <th class="px-4 py-3.5">รหัสการแจ้งซ่อม</th>
                                            <th class="px-4 py-3.5 whitespace-nowrap">วันที่แจ้ง</th>
                                            <th class="px-4 py-3.5">รายการอุปกรณ์</th>
                                            <th class="px-4 py-3.5">อาการที่พบ</th>
                                            <th class="px-4 py-3.5">สถานที่</th>
                                            <th class="px-4 py-3.5">ช่างผู้รับผิดชอบ</th>
                                            <th class="px-4 py-3.5 text-center whitespace-nowrap">สถานะ</th>
                                            <th class="px-4 py-3.5 text-center whitespace-nowrap">ความพึงพอใจ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium text-[11px]">
                                        @if($historyJobs->isEmpty())
                                            <tr>
                                                <td colspan="8" class="p-10 text-center text-slate-400">
                                                    <div class="text-4xl text-slate-300 mb-3"><i class="fa-solid fa-history"></i></div>
                                                    <div class="font-bold text-sm">ไม่มีประวัติงานซ่อมในระบบขณะนี้</div>
                                                </td>
                                            </tr>
                                        @else
                                            @foreach($historyJobs as $job)
                                                @php
                                                    $categoryKey = $classifyCategory($job);
                                                    $reparabilityKey = $classifyReparability($job);
                                                    $dateObj = \Carbon\Carbon::parse($job->created_at);
                                                    
                                                    $statusIcon = "fa-circle-check";
                                                    $iconBg = "bg-emerald-50 text-emerald-500 border-emerald-200 shadow-sm shadow-emerald-100";
                                                    $statusBadge = "bg-emerald-50 text-emerald-600 border-emerald-200";
                                                    $statusText = $job->status;
                                                    
                                                    if ($reparabilityKey === 'unrepairable_parts') {
                                                        $statusIcon = "fa-triangle-exclamation";
                                                        $iconBg = "bg-rose-50 text-rose-500 border-rose-200 shadow-sm shadow-rose-100";
                                                        $statusBadge = "bg-rose-50 text-rose-600 border-rose-200";
                                                    } elseif ($reparabilityKey === 'cancelled') {
                                                        $statusIcon = "fa-ban";
                                                        $iconBg = "bg-slate-50 text-slate-500 border-slate-200 shadow-sm shadow-slate-100";
                                                        $statusBadge = "bg-slate-50 text-slate-600 border-slate-200";
                                                    }

                                                    $repairOutcome = $job->repair_result ?: ($job->repair_details ?: ($job->inspection_result ?: ($job->unrepairable_reason ?: $job->note)));
                                                    if (empty($repairOutcome)) {
                                                        if ($reparabilityKey === 'repairable') {
                                                            $repairOutcome = 'ดำเนินการซ่อมแซมและแก้ไขปัญหาเรียบร้อยแล้ว อุปกรณ์พร้อมใช้งานตามปกติ';
                                                        } elseif ($reparabilityKey === 'unrepairable_parts') {
                                                            if ($job->status === 'ไม่สามารถซ่อมได้' || $job->status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้') {
                                                                $repairOutcome = $job->unrepairable_reason ?: 'ไม่สามารถซ่อมแซมได้';
                                                            } else {
                                                                $repairOutcome = $job->unrepairable_reason ?: 'อยู่ระหว่างดำเนินการจัดหาอะไหล่และซ่อมแซม';
                                                            }
                                                        } elseif ($reparabilityKey === 'cancelled') {
                                                            $repairOutcome = 'ยกเลิกคำขอแจ้งซ่อม';
                                                        } else {
                                                            $repairOutcome = 'ดำเนินการเรียบร้อยแล้ว';
                                                        }
                                                    }

                                                    $shortDesc = $job->clean_symptom ?: 'ไม่ระบุอาการ';

                                                    $displayLoc = str_replace('undefined', 'ไม่ระบุ', $job->location ?? '');
                                                    if (str_contains($displayLoc, 'ห้อง -') && $job->device && $job->device->room) {
                                                        $realRoom = $job->device->room->room_code ?: $job->device->room->room_name;
                                                        if ($realRoom) {
                                                            $displayLoc = str_replace('ห้อง -', 'ห้อง ' . $realRoom, $displayLoc);
                                                        }
                                                    }

                                                    $searchKeywords = mb_strtolower(
                                                        $job->request_code . ' ' . 
                                                        ($job->device->name ?? '') . ' ' . 
                                                        ($job->display_device_code ?? '') . ' ' . 
                                                        ($job->device->category ?? '') . ' ' . 
                                                        ($job->device->deviceType->name ?? $job->type ?? '') . ' ' . 
                                                        ($job->description ?? '') . ' ' . 
                                                        ($job->repair_details ?? '') . ' ' . 
                                                        ($job->unrepairable_reason ?? '') . ' ' . 
                                                        ($job->assignee ? $job->assignee->first_name . ' ' . $job->assignee->last_name : '') . ' ' . 
                                                        $displayLoc . ' ' .
                                                        ($job->status ?? '') . ' ' .
                                                        ($categoryKey === 'hardware' ? 'ฮาร์ดแวร์ hardware' : ($categoryKey === 'software' ? 'ซอฟต์แวร์ software' : 'เครือข่าย network')) . ' ' .
                                                        ($reparabilityKey === 'repairable' ? 'สามารถซ่อมได้ ซ่อมเสร็จ' : ($reparabilityKey === 'unrepairable_parts' ? 'ซ่อมไม่ได้ ต้องสั่งอะไหล่ อะไหล่' : 'ยกเลิก')),
                                                        'UTF-8'
                                                    );
                                                @endphp
                                                <tr class="hover:bg-blue-50/50 transition history-row cursor-pointer"
                                                    data-category="{{ $categoryKey }}"
                                                    data-reparability="{{ $reparabilityKey }}"
                                                    data-date="{{ $dateObj->format('Y-m-d') }}"
                                                    data-year="{{ $dateObj->format('Y') }}"
                                                    data-month="{{ (int)$dateObj->format('n') }}"
                                                    data-day="{{ (int)$dateObj->format('j') }}"
                                                    data-keyword="{{ $searchKeywords }}"
                                                    onclick="openDetailModal({
                                                        code: '{{ $job->request_code }}',
                                                        device: '{{ $job->device->name ?? 'ไม่ระบุ' }}',
                                                        device_code: '{{ $job->display_device_code }}',
                                                        location: '{{ $job->device->building->building_name ?? '-' }} {{ $job->device->room->room_code ?? '-' }}',
                                                        type: '{{ $job->device->deviceType->name ?? '-' }}',
                                                        reporter_name: '{{ addslashes($job->user->name ?? 'ไม่ระบุ') }}',
                                                        asset_category: '{{ addslashes($job->device->category ?? 'ไม่ระบุ') }}',
                                                        asset_kind: '{{ addslashes($job->device->deviceType->name ?? $job->type ?? 'ไม่ระบุ') }}',
                                                        building: '{{ addslashes($job->device->building->building_name ?? $job->device->building->name ?? 'ไม่ระบุ') }}',
                                                        floor: '{{ addslashes($job->device->floor ?? 'ไม่ระบุ') }}',
                                                        room: '{{ addslashes($job->device->room->room_name ?? $job->device->room->room_code ?? 'ไม่ระบุ') }}',
                                                        priority: '{{ $job->priority }}',
                                                        description: '{{ base64_encode(nl2br(trim(str_replace('อาการเสีย:', '', e($job->description))))) }}',
                                                        inspection_result: '{{ base64_encode(nl2br(e($job->inspection_result ?? ''))) }}',
                                                        repair_details: '{{ base64_encode(nl2br(e($job->repair_details ?? $job->note ?? $job->unrepairable_reason ?? ''))) }}',
                                                        repair_result: '{{ base64_encode(nl2br(e($repairOutcome))) }}',
                                                        repair_date: '{{ \Carbon\Carbon::parse($job->updated_at ?? $job->created_at)->addYears(543)->format('d/m/Y H:i') }} น.',
                                                        assignee: '{{ $job->assignee->first_name ?? '-' }} {{ $job->assignee->last_name ?? '' }}',
                                                        image: '{{ $job->image ? asset($job->image) : "" }}',
                                                        status: '{{ $job->status }}',
                                                        statusText: '{!! $statusText !!}',
                                                        created_at: '{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') }} น.'
                                                    })">
                                                    <td class="px-4 py-3.5 font-bold text-blue-600 whitespace-nowrap">
                                                        {{ $job->request_code }}
                                                    </td>
                                                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-500">
                                                        <div class="flex items-center gap-1.5 text-slate-500">
                                                            <i class="fa-regular fa-calendar-plus text-slate-400"></i>
                                                            <span>{{ $job->created_at ? \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3.5">
                                                        <div class="font-bold text-slate-800">{{ $job->device->name ?? 'อุปกรณ์ไอที' }}</div>
                                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                            @if(!empty($job->display_device_code))
                                                                <span class="text-[10px] text-slate-500 font-mono bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/60">
                                                                    {{ $job->display_device_code }}
                                                                </span>
                                                            @endif
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $categoryKey === 'hardware' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : ($categoryKey === 'software' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-teal-50 text-teal-700 border border-teal-100') }}">
                                                                {{ $categoryKey === 'hardware' ? 'ฮาร์ดแวร์' : ($categoryKey === 'software' ? 'ซอฟต์แวร์' : 'เครือข่าย') }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3.5 text-slate-600 max-w-[170px] truncate" title="{{ $shortDesc }}">
                                                        {{ Str::limit($shortDesc, 35) }}
                                                    </td>
                                                    <td class="px-4 py-3.5 text-slate-500 max-w-[140px] truncate" title="{{ $displayLoc }}">
                                                        {{ $displayLoc ?: '-' }}
                                                    </td>
                                                    <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">
                                                        @if($job->assignee)
                                                            <span class="inline-flex items-center gap-1 text-slate-700 font-semibold">
                                                                <i class="fa-solid fa-user-gear text-slate-400 text-xs"></i>
                                                                {{ $job->assignee->first_name }} {{ $job->assignee->last_name }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400 italic">ยังไม่มอบหมาย</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                                            <i class="fa-solid {{ $statusIcon }} mr-1"></i>
                                                            {{ $statusText }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                                        @if($job->rating > 0)
                                                            <div class="inline-flex items-center gap-1 text-amber-500 font-bold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">
                                                                <i class="fa-solid fa-star text-[10px]"></i>
                                                                <span class="text-amber-800 text-[11px]">{{ $job->rating }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-slate-300 text-[10px]">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr id="history-empty-row" class="hidden">
                                                <td colspan="8" class="p-10 text-center text-slate-400">
                                                    <div class="text-4xl text-slate-300 mb-3"><i class="fa-solid fa-filter-circle-xmark"></i></div>
                                                    <div class="font-bold text-sm">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <!-- 2. ส่วนแสดงผลแบบการ์ด (Card View Container - Default View) -->
                            <div id="teacher-history-card-container" class="space-y-4">
                                <div id="teacher-history-list" class="space-y-4">
                        
                        <!-- History Jobs Rendered Here -->
                        @if($historyJobs->isEmpty())
                            <div class="flex flex-col items-center justify-center py-20 bg-slate-50/50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-5xl text-slate-200 shadow-sm mb-6">
                                    <i class="fa-solid fa-clipboard-check text-emerald-400"></i>
                                </div>
                                <h3 class="text-xl font-black text-slate-700">ไม่มีประวัติการแจ้งซ่อม</h3>
                                <p class="text-slate-500 mt-2 font-medium">คุณยังไม่มีประวัติการซ่อมที่เสร็จสิ้นหรือแจ้งซ่อมในระบบ</p>
                            </div>
                        @else
                            @foreach($historyJobs as $job)
                            @php
                                $categoryKey = $classifyCategory($job);
                                $reparabilityKey = $classifyReparability($job);
                                $dateObj = \Carbon\Carbon::parse($job->created_at);
                                
                                $statusIcon = "fa-screwdriver-wrench";
                                $iconBg = "bg-teal-50 text-teal-600 border-teal-200";
                                $statusBadge = "bg-teal-50 text-teal-700 border-teal-200";
                                $cardBorder = "border-l-4 border-l-teal-500";
                                $statusText = $job->status;
                                
                                if ($job->status === 'รอรับงาน') {
                                    $statusIcon = "fa-hand-pointer";
                                    $iconBg = "bg-amber-50 text-amber-600 border-amber-200";
                                    $statusBadge = "bg-amber-50 text-amber-700 border-amber-200";
                                    $cardBorder = "border-l-4 border-l-amber-500";
                                } elseif ($job->status === 'เสร็จสิ้น' || $job->status === 'ซ่อมเสร็จสมบูรณ์' || str_starts_with($job->status, 'ซ่อมเสร็จ') || $reparabilityKey === 'repairable') {
                                    $statusIcon = "fa-circle-check";
                                    $iconBg = "bg-emerald-50 text-emerald-600 border-emerald-200";
                                    $statusBadge = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                    $cardBorder = "border-l-4 border-l-emerald-500";
                                } elseif ($job->status === 'ไม่สามารถซ่อมได้' || $job->status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้') {
                                    $statusIcon = "fa-circle-xmark";
                                    $iconBg = "bg-rose-50 text-rose-600 border-rose-200";
                                    $statusBadge = "bg-rose-50 text-rose-700 border-rose-200";
                                    $cardBorder = "border-l-4 border-l-rose-500";
                                } elseif (str_starts_with($job->status, 'ยกเลิก')) {
                                    $statusIcon = "fa-ban";
                                    $iconBg = "bg-slate-100 text-slate-500 border-slate-200";
                                    $statusBadge = "bg-slate-100 text-slate-600 border-slate-200";
                                    $cardBorder = "border-l-4 border-l-slate-400";
                                } elseif ($job->status === 'รออนุมัติงบประมาณ') {
                                    $statusIcon = "fa-hourglass-half";
                                    $iconBg = "bg-amber-50 text-amber-600 border-amber-200";
                                    $statusBadge = "bg-amber-50 text-amber-700 border-amber-200 animate-pulse";
                                    $cardBorder = "border-l-4 border-l-amber-500";
                                } elseif ($job->status === 'รออะไหล่' || $job->status === 'รอเปลี่ยนอะไหล่' || $reparabilityKey === 'unrepairable_parts') {
                                    $statusIcon = "fa-boxes-stacked";
                                    $iconBg = "bg-purple-50 text-purple-600 border-purple-200";
                                    $statusBadge = "bg-purple-50 text-purple-700 border-purple-200";
                                    $cardBorder = "border-l-4 border-l-purple-500";
                                } elseif ($job->status === 'กำลังดำเนินการ' || $job->status === 'กำลังซ่อม') {
                                    $statusIcon = "fa-screwdriver-wrench";
                                    $iconBg = "bg-teal-50 text-teal-600 border-teal-200";
                                    $statusBadge = "bg-teal-50 text-teal-700 border-teal-200";
                                    $cardBorder = "border-l-4 border-l-teal-500";
                                }

                                $repairOutcome = $job->repair_result ?: ($job->repair_details ?: ($job->inspection_result ?: ($job->unrepairable_reason ?: $job->note)));
                                if (empty($repairOutcome)) {
                                    if ($reparabilityKey === 'repairable') {
                                        $repairOutcome = 'ดำเนินการซ่อมแซมและแก้ไขปัญหาเรียบร้อยแล้ว อุปกรณ์พร้อมใช้งานตามปกติ';
                                    } elseif ($reparabilityKey === 'unrepairable_parts') {
                                        if ($job->status === 'ไม่สามารถซ่อมได้' || $job->status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้') {
                                            $repairOutcome = $job->unrepairable_reason ?: 'ไม่สามารถซ่อมแซมได้';
                                        } else {
                                            $repairOutcome = $job->unrepairable_reason ?: 'อยู่ระหว่างดำเนินการจัดหาอะไหล่และซ่อมแซม';
                                        }
                                    } elseif ($reparabilityKey === 'cancelled') {
                                        $repairOutcome = 'ยกเลิกคำขอแจ้งซ่อม';
                                    } else {
                                        $repairOutcome = 'ดำเนินการเรียบร้อยแล้ว';
                                    }
                                }

                                $displayLoc = str_replace(['อาคาร อาคาร', 'undefined'], ['อาคาร', 'ไม่ระบุ'], $job->location);
                                if (str_contains($displayLoc, 'ห้อง -')) {
                                    if ($job->device && $job->device->room) {
                                        $realRoom = $job->device->room->room_code ?: $job->device->room->room_name;
                                        if ($realRoom) {
                                            $displayLoc = str_replace('ห้อง -', 'ห้อง ' . $realRoom, $displayLoc);
                                        } else {
                                            $displayLoc = str_replace(' ห้อง -', '', $displayLoc);
                                        }
                                    } else {
                                        $displayLoc = str_replace(' ห้อง -', '', $displayLoc);
                                    }
                                }
                                if (empty(trim($displayLoc)) || $displayLoc === '-') {
                                    $displayLoc = 'ไม่ระบุสถานที่';
                                }

                                $searchKeywords = mb_strtolower(
                                    $job->request_code . ' ' . 
                                    ($job->device->name ?? '') . ' ' . 
                                    ($job->display_device_code ?? '') . ' ' . 
                                    ($job->device->category ?? '') . ' ' . 
                                    ($job->device->deviceType->name ?? $job->type ?? '') . ' ' . 
                                    ($job->description ?? '') . ' ' . 
                                    ($job->repair_details ?? '') . ' ' . 
                                    ($job->unrepairable_reason ?? '') . ' ' . 
                                    ($job->user->name ?? '') . ' ' . 
                                    ($job->assignee ? $job->assignee->first_name . ' ' . $job->assignee->last_name : '') . ' ' . 
                                    ($job->status ?? '') . ' ' .
                                    ($categoryKey === 'hardware' ? 'ฮาร์ดแวร์ hardware' : ($categoryKey === 'software' ? 'ซอฟต์แวร์ software' : 'เครือข่าย network')) . ' ' .
                                    ($reparabilityKey === 'repairable' ? 'สามารถซ่อมได้ ซ่อมเสร็จ' : ($reparabilityKey === 'unrepairable_parts' ? 'ซ่อมไม่ได้ ต้องสั่งอะไหล่ อะไหล่' : 'ยกเลิก')),
                                    'UTF-8'
                                );
                            @endphp
                            <div onclick="openDetailModal({
                                            code: '{{ $job->request_code }}',
                                            device: '{{ $job->device->name ?? 'ไม่ระบุ' }}',
                                            device_code: '{{ $job->display_device_code }}',
                                            location: '{{ $job->device->building->building_name ?? '-' }} {{ $job->device->room->room_code ?? '-' }}',
                                            type: '{{ $job->device->deviceType->name ?? '-' }}',
                                            reporter_name: '{{ addslashes($job->user->name ?? 'ไม่ระบุ') }}',
                                            asset_category: '{{ addslashes($job->device->category ?? 'ไม่ระบุ') }}',
                                            asset_kind: '{{ addslashes($job->device->deviceType->name ?? $job->type ?? 'ไม่ระบุ') }}',
                                            building: '{{ addslashes($job->device->building->building_name ?? $job->device->building->name ?? 'ไม่ระบุ') }}',
                                            floor: '{{ addslashes($job->device->floor ?? 'ไม่ระบุ') }}',
                                            room: '{{ addslashes($job->device->room->room_name ?? $job->device->room->room_code ?? 'ไม่ระบุ') }}',
                                            priority: '{{ $job->priority }}',
                                            description: '{{ base64_encode(nl2br(trim(str_replace('อาการเสีย:', '', e($job->description))))) }}',
                                            inspection_result: '{{ base64_encode(nl2br(e($job->inspection_result ?? ''))) }}',
                                            repair_details: '{{ base64_encode(nl2br(e($job->repair_details ?? $job->note ?? $job->unrepairable_reason ?? ''))) }}',
                                            repair_result: '{{ base64_encode(nl2br(e($repairOutcome))) }}',
                                            repair_date: '{{ \Carbon\Carbon::parse($job->updated_at ?? $job->created_at)->addYears(543)->format('d/m/Y H:i') }} น.',
                                            assignee: '{{ $job->assignee->first_name ?? '-' }} {{ $job->assignee->last_name ?? '' }}',
                                            image: '{{ $job->image ? asset($job->image) : "" }}',
                                            status: '{{ $job->status }}',
                                            statusText: '{!! $statusText !!}',
                                            created_at: '{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') }} น.'
                                        })"
                                 class="history-job-card bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/70 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col gap-4 relative group overflow-hidden {{ $cardBorder }} cursor-pointer"
                                 data-category="{{ $categoryKey }}"
                                 data-reparability="{{ $reparabilityKey }}"
                                 data-status="{{ $job->status }}"
                                 data-date="{{ $dateObj->format('Y-m-d') }}"
                                 data-year="{{ $dateObj->format('Y') }}"
                                 data-month="{{ (int)$dateObj->format('n') }}"
                                 data-day="{{ (int)$dateObj->format('j') }}"
                                 data-keyword="{{ $searchKeywords }}">

                                <!-- Top Row: Icon, Device, REQ, Status Badge -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg border {{ $iconBg }} shrink-0 shadow-xs">
                                            <i class="fa-solid {{ getDeviceIcon($job->device->name ?? '') }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-[11px] font-mono font-black text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                                    REQ: {{ $job->request_code }}
                                                </span>
                                                @if($job->device && $job->display_device_code)
                                                    <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                                                        รหัส: {{ $job->display_device_code }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-mono font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                                                        รหัส: -
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="text-base sm:text-lg font-black text-slate-800 truncate mt-1">
                                                {{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center gap-1.5 shrink-0">
                                        <span class="px-3 py-1 rounded-full text-xs font-black border {{ $statusBadge }} flex items-center gap-1.5 shadow-xs">
                                            <i class="fa-solid {{ $statusIcon }} text-[10px]"></i>
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Middle Grid: 4 Info Columns -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-slate-50/70 p-3.5 rounded-2xl border border-slate-100">
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                            <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i>สถานที่ซ่อม
                                        </span>
                                        <p class="font-bold text-slate-700 truncate" title="{{ $displayLoc }}">
                                            {{ $displayLoc }}
                                        </p>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                            <i class="fa-solid fa-user text-indigo-500 mr-1"></i>ผู้แจ้งซ่อม
                                        </span>
                                        <p class="font-bold text-slate-700 truncate">
                                            {{ $job->user->name ?? 'ไม่ระบุ' }}
                                        </p>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                            <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1"></i>อาการที่พบ
                                        </span>
                                        <p class="font-bold text-slate-700 truncate" title="{{ $job->clean_symptom }}">
                                            {{ $job->clean_symptom ?: '-' }}
                                        </p>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                            <i class="fa-regular fa-clock text-slate-400 mr-1"></i>วันที่แจ้งซ่อม
                                        </span>
                                        <p class="font-bold text-slate-700">
                                            {{ \Carbon\Carbon::parse($job->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') }} น.
                                        </p>
                                    </div>
                                </div>

                                <!-- Bottom Row: Priority, Rating & View Details -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-slate-500">ระดับความเร่งด่วน:</span>
                                        <span class="text-xs font-black px-2.5 py-1 rounded-full {{ $job->priority === 'ด่วนที่สุด' ? 'bg-red-50 text-red-600 border border-red-200' : ($job->priority === 'ด่วน' ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200') }}">
                                            <i class="fa-solid fa-circle text-[6px] mr-1"></i>{{ $job->priority ?: 'ปกติ' }}
                                        </span>

                                        @if(str_starts_with($job->status, 'ซ่อมเสร็จ') || $job->status === 'เสร็จสิ้น')
                                            @if($job->rating > 0)
                                                <span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full flex items-center gap-1 shadow-xs">
                                                    <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                                    {{ number_format($job->rating, 1) }} ({{ getRatingLabel($job->rating) }})
                                                </span>
                                            @else
                                                <button type="button" onclick="event.stopPropagation(); openEvalModal({{ $job->id }}, '{{ $job->request_code }}', '{{ addslashes($job->device->name ?? '') }}')" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-full text-xs font-bold transition-all shadow-xs flex items-center gap-1 cursor-pointer">
                                                    <i class="fa-regular fa-star text-[10px]"></i> ประเมินความพึงพอใจ
                                                </button>
                                            @endif
                                        @elseif($reparabilityKey === 'unrepairable_parts' || $job->status === 'รออะไหล่' || $job->status === 'รอเปลี่ยนอะไหล่')
                                            <span class="text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 px-2.5 py-1 rounded-full flex items-center gap-1">
                                                <i class="fa-solid fa-boxes-stacked text-[10px]"></i> รออะไหล่/สั่งซื้อ
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 justify-end">
                                        <button type="button" 
                                            onclick="event.stopPropagation(); openDetailModal({
                                                code: '{{ $job->request_code }}',
                                                device: '{{ $job->device->name ?? 'ไม่ระบุ' }}',
                                                device_code: '{{ $job->display_device_code }}',
                                                location: '{{ $job->device->building->building_name ?? '-' }} {{ $job->device->room->room_code ?? '-' }}',
                                                type: '{{ $job->device->deviceType->name ?? '-' }}',
                                                reporter_name: '{{ addslashes($job->user->name ?? 'ไม่ระบุ') }}',
                                                asset_category: '{{ addslashes($job->device->category ?? 'ไม่ระบุ') }}',
                                                asset_kind: '{{ addslashes($job->device->deviceType->name ?? $job->type ?? 'ไม่ระบุ') }}',
                                                building: '{{ addslashes($job->device->building->building_name ?? $job->device->building->name ?? 'ไม่ระบุ') }}',
                                                floor: '{{ addslashes($job->device->floor ?? 'ไม่ระบุ') }}',
                                                room: '{{ addslashes($job->device->room->room_name ?? $job->device->room->room_code ?? 'ไม่ระบุ') }}',
                                                priority: '{{ $job->priority }}',
                                                description: '{{ base64_encode(nl2br(trim(str_replace('อาการเสีย:', '', e($job->description))))) }}',
                                                inspection_result: '{{ base64_encode(nl2br(e($job->inspection_result ?? ''))) }}',
                                                repair_details: '{{ base64_encode(nl2br(e($job->repair_details ?? $job->note ?? $job->unrepairable_reason ?? ''))) }}',
                                                repair_result: '{{ base64_encode(nl2br(e($repairOutcome))) }}',
                                                repair_date: '{{ \Carbon\Carbon::parse($job->updated_at ?? $job->created_at)->addYears(543)->format('d/m/Y H:i') }} น.',
                                                assignee: '{{ $job->assignee->first_name ?? '-' }} {{ $job->assignee->last_name ?? '' }}',
                                                image: '{{ $job->image ? asset($job->image) : "" }}',
                                                status: '{{ $job->status }}',
                                                statusText: '{!! $statusText !!}',
                                                created_at: '{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') }} น.'
                                            })"
                                            class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center justify-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-circle-info text-slate-400"></i>
                                            ดูรายละเอียด
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Filter Empty State -->
                        <div id="history-empty-filter" class="hidden flex flex-col items-center justify-center py-16 bg-white/70 rounded-[2rem] border-2 border-dashed border-slate-200">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-2xl text-slate-400 mb-3 shadow-inner">
                                <i class="fa-solid fa-filter-circle-xmark"></i>
                            </div>
                            <h4 class="text-base font-bold text-slate-700">ไม่พบข้อมูลตามตัวกรองที่เลือก</h4>
                            <p class="text-xs text-slate-500 mt-1">ลองเปลี่ยนช่วงเวลา ประเภทการซ่อม หรือคำค้นหาใหม่อีกครั้ง</p>
                            <button type="button" onclick="resetHistoryFilter()" class="mt-4 px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs font-bold rounded-xl border border-blue-200 transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-rotate-left"></i> ล้างตัวกรองทั้งหมด
                            </button>
                        </div>

                                </div><!-- End #teacher-history-list -->
                            </div><!-- End #teacher-history-card-container -->
                        </div><!-- End #teacher-history-table-view -->
                    </div>
                </div>

                <script>
                    window.historyJobsData = @json($historyDataList);
                    window.masterAssetKinds = @json(\App\Models\AssetKind::pluck('name')->map(fn($n) => trim($n))->filter()->values()->all());
                </script>
            </div>

            <!-- Tab 6: บันทึกค่าซ่อม & การเบิกจ่ายงบประมาณ (Requisition & Budget Tracking) -->
            @php
                if (isset($teacherRequisitions)) {
                    $requisitionList = $teacherRequisitions;
                } else {
                    $requisitionList = $requests->filter(function($r) {
                        $isReq = ($r->type ?? '') === 'เบิกงบประมาณ' || !empty($r->repair_project_id);
                        if (!$isReq) return false;
                        $note = trim($r->note ?? '');
                        if ($note === 'หน้าแจ้งซ่อม' || $note === 'teacher') return true;
                        if ($note === 'หน้าเจ้าหน้าที่ซ่อมบำรุง' || $note === 'technician') return false;
                        $role = mb_strtolower($r->user->role ?? '', 'UTF-8');
                        return str_contains($role, 'teacher') || str_contains($role, 'ครู') || empty($role);
                    });
                }
                $totalBudgetRequested = (float)$requisitionList->sum('budget');
                $pendingRequisitions = $requisitionList->filter(function($r) {
                    $s = mb_strtolower(trim($r->status ?? ''), 'UTF-8');
                    return in_array($s, ['รอมอบหมาย', 'รอดำเนินการ', 'กำลังดำเนินการ', 'กำลังซ่อม']) || !$r->budget_approved;
                });
                $approvedRequisitions = $requisitionList->filter(function($r) {
                    $s = mb_strtolower(trim($r->status ?? ''), 'UTF-8');
                    return in_array($s, ['อนุมัติแล้ว', 'เสร็จสิ้น']) || $r->budget_approved;
                });
                $uniqueProjectsCount = $requisitionList->pluck('repair_project_id')->filter()->unique()->count();
                $allAvailableProjects = isset($repairProjects) ? $repairProjects : \App\Models\RepairProject::all();
                $availableFiscalYears = $allAvailableProjects->pluck('fiscal_year')->filter()->unique()->sortDesc();
                $userRequestsForLink = (isset($teacherJobs) ? $teacherJobs : $requests)->take(50);
            @endphp

            <div id="tab-requisition" class="tab-content hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
                <!-- Unified Single Container (Clean, Modern, Comfortable, Easy to Use) -->
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden">
                    
                    <!-- Single Elegant Header -->
                    <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-blue-600 px-6 sm:px-8 py-5 text-white relative overflow-hidden">
                        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                        <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-xl border border-white/30 shadow-inner">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-white tracking-wide">บันทึกค่าซ่อม & เบิกงบประมาณ</h2>
                                </div>
                            </div>

                            <!-- Sleek Switcher: [ ✍️ บันทึกค่าซ่อมใหม่ ] [ 📋 ประวัติการเบิก (X) ] -->
                            <div class="flex items-center bg-black/25 backdrop-blur-md p-1.5 rounded-2xl border border-white/15 shrink-0 w-full sm:w-auto shadow-inner">
                                <button type="button" id="btn-req-tab-form" onclick="switchRequisitionView('form')" 
                                        class="flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-black transition-all bg-white text-blue-700 shadow-sm cursor-pointer flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>บันทึกค่าซ่อมใหม่</span>
                                </button>
                                <button type="button" id="btn-req-tab-history" onclick="switchRequisitionView('history')" 
                                        class="flex-1 sm:flex-none px-4 py-2 rounded-xl text-xs font-bold transition-all text-white/80 hover:text-white hover:bg-white/10 cursor-pointer flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    <span>ประวัติการเบิก ({{ $requisitionList->count() }})</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Container Body -->
                    <div class="p-6 sm:p-8">

                        <!-- ========================================== -->
                        <!-- VIEW 1: แบบฟอร์มบันทึกค่าซ่อม (Exact Match & Ultra-Polished) -->
                        <!-- ========================================== -->
                        <div id="req-section-form" class="space-y-6">

                            <!-- ====== Linked Repair Request Info Card (hidden by default, shown by goToRepairRecord) ====== -->
                            <div id="repair-job-info-card" class="hidden">
                                <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 border border-blue-200/80 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                                    <!-- Subtle pattern -->
                                    <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 2px 2px, #2563eb 1px, transparent 0); background-size: 16px 16px;"></div>
                                    <div class="relative z-10">
                                        <!-- Header row -->
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-xl bg-blue-500 text-white flex items-center justify-center text-xs shadow-sm">
                                                    <i class="fa-solid fa-link"></i>
                                                </div>
                                                <span class="text-xs font-black text-blue-800 uppercase tracking-wider">ข้อมูลการแจ้งซ่อมที่เชื่อมโยง</span>
                                            </div>
                                            <button type="button" onclick="closeRepairJobInfoCard()" class="w-6 h-6 rounded-lg hover:bg-blue-200/60 text-blue-400 hover:text-blue-600 flex items-center justify-center transition text-xs cursor-pointer">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>

                                        <!-- Info grid -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <div class="bg-white/70 rounded-xl p-3 border border-blue-100/80">
                                                <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block mb-0.5">รหัสแจ้งซ่อม</span>
                                                <span id="rji-job-code" class="text-sm font-black text-blue-700 font-mono">—</span>
                                            </div>
                                            <div class="bg-white/70 rounded-xl p-3 border border-blue-100/80">
                                                <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block mb-0.5">อุปกรณ์</span>
                                                <span id="rji-device" class="text-sm font-bold text-slate-700 truncate block">—</span>
                                            </div>
                                            <div class="bg-white/70 rounded-xl p-3 border border-blue-100/80 sm:col-span-2">
                                                <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block mb-0.5">สถานที่</span>
                                                <span id="rji-location" class="text-sm font-bold text-slate-700">—</span>
                                            </div>
                                        </div>

                                        <!-- Description row (shows if available) -->
                                        <div id="rji-detail-row" class="hidden mt-3 bg-white/70 rounded-xl p-3 border border-blue-100/80">
                                            <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block mb-0.5">รายละเอียดจากช่าง</span>
                                            <p id="rji-detail" class="text-xs font-medium text-slate-700 leading-relaxed"></p>
                                        </div>

                                        <p class="mt-3 text-[10px] text-blue-400 font-medium italic flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            ข้อมูลด้านบนถูกดึงจากใบแจ้งซ่อมและกรอกในฟอร์มอัตโนมัติ
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- ====== END Linked Repair Request Info Card ====== -->

                            <form id="inline-budget-form" action="javascript:void(0)" method="POST" onsubmit="event.preventDefault(); submitBudgetRequest(this);" class="space-y-6">
                                
                                <!-- เชื่อมโยงกับใบแจ้งซ่อมที่รออะไหล่ / บันทึกค่าซ่อม -->
                                @php
                                    $pendingJobsForCost = $requests->filter(function($j) {
                                        return in_array($j->status, ['รอเปลี่ยนอะไหล่', 'รออนุมัติงบประมาณ', 'รออะไหล่']) && ($j->type ?? '') !== 'เบิกงบประมาณ';
                                    });
                                @endphp
                                <div class="space-y-2.5 p-4 bg-gradient-to-r from-blue-50/80 via-indigo-50/40 to-blue-50/80 border border-blue-200/80 rounded-2xl shadow-2xs">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs shadow-xs">
                                                <i class="fa-solid fa-link"></i>
                                            </span>
                                            <label for="link_repair_job_select" class="text-xs sm:text-sm font-black text-blue-900 tracking-tight">
                                                เชื่อมโยงกับใบแจ้งซ่อมที่รออะไหล่
                                            </label>
                                        </div>
                                        <span class="text-[11px] font-bold text-blue-700 bg-white/90 border border-blue-200 px-2.5 py-0.5 rounded-full shadow-2xs">
                                            สถานะจะเปลี่ยนเป็น "กำลังซ่อม" อัตโนมัติ
                                        </span>
                                    </div>
                                    <div class="relative mt-1">
                                        <select id="link_repair_job_select" onchange="handleJobSelectChange(this)"
                                                class="w-full px-4 py-2.5 bg-white border border-blue-200 text-slate-800 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition font-bold text-xs sm:text-sm shadow-2xs cursor-pointer hover:border-blue-300">
                                            <option value="">-- ไม่เชื่อมโยง (เป็นการเบิกงบประมาณทั่วไป) --</option>
                                            @foreach($pendingJobsForCost as $pj)
                                                <option value="{{ $pj->request_code }}"
                                                        data-code="{{ $pj->request_code }}"
                                                        data-device="{{ $pj->device->name ?? 'อุปกรณ์อื่นๆ' }}"
                                                        data-location="{{ $pj->location ?? 'ไม่ระบุ' }}"
                                                        data-desc="{{ $pj->repair_details ?: ($pj->clean_symptom ?: $pj->description) }}">
                                                    {{ $pj->request_code }} | {{ $pj->device->name ?? 'อุปกรณ์อื่นๆ' }} ({{ $pj->clean_symptom ?: 'รอเปลี่ยนอะไหล่' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                                    </div>
                                    <input type="hidden" id="selected_repair_request_code" name="repair_request_code" value="">
                                </div>
                                
                                <!-- 1. เลือกโครงการที่ต้องการเบิกงบประมาณ -->
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-sm shadow-blue-500/25 shrink-0">
                                            1
                                        </span>
                                        <label for="budget_project_id" class="text-sm font-black text-slate-800 tracking-tight">
                                            เลือกโครงการที่ต้องการเบิกงบประมาณ <span class="text-red-500">*</span>
                                        </label>
                                    </div>
                                    
                                    <div class="relative">
                                        <select id="budget_project_id" name="project_id" onchange="handleProjectChange(this)" 
                                                class="w-full px-4 py-3.5 bg-slate-50/90 hover:bg-white focus:bg-white border border-slate-200 text-slate-800 rounded-2xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-bold text-sm shadow-2xs appearance-none cursor-pointer hover:border-slate-300">
                                            <option value="">-- กรุณาเลือกโครงการ --</option>
                                            @foreach($allAvailableProjects as $p)
                                                @php
                                                    $rem = (float)($p->budget - ($p->used_budget ?? 0));
                                                    $deptLabel = $p->department ? "[$p->department]" : "";
                                                    $yearLabel = $p->fiscal_year ? "ปี $p->fiscal_year" : "";
                                                @endphp
                                                <option value="{{ $p->id }}" 
                                                        data-code="{{ $p->project_code }}" 
                                                        data-name="{{ $p->name }}"
                                                        data-dept="{{ $p->department ?? 'ส่วนกลาง' }}"
                                                        data-fiscal="{{ $p->fiscal_year ?? date('Y')+543 }}"
                                                        data-source="{{ $p->budget_source ?? 'งบประมาณกลาง' }}"
                                                        data-budget="{{ $p->budget }}"
                                                        data-used="{{ $p->used_budget ?? 0 }}"
                                                        data-remaining="{{ $rem }}">
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                                    </div>

                                    <!-- แถบสรุปสถานะงบประมาณโครงการ -->
                                    <div id="project-budget-banner" class="p-4 sm:p-4.5 rounded-2xl bg-gradient-to-r from-slate-50 via-slate-50 to-blue-50/40 border border-slate-200/80 flex flex-wrap items-center justify-between gap-4 shadow-2xs transition-all">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-xs font-bold text-slate-500">แหล่งงบประมาณ:</span>
                                            <span id="budget_project_source_badge" class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-200/80 text-slate-600 border border-slate-200">
                                                ยังไม่ได้เลือกโครงการ
                                            </span>
                                            <input type="hidden" id="budget_project_source" name="budget_source">
                                            <input type="hidden" id="budget_project_code" name="project_code">
                                            <input type="hidden" id="budget_department" name="department">
                                            <input type="hidden" id="budget_fiscal_year" name="fiscal_year">
                                        </div>

                                        <div class="flex items-center gap-5 sm:gap-6 font-mono text-right">
                                            <div>
                                                <span class="text-slate-400 block text-[10px] font-sans font-bold uppercase">งบทั้งหมด</span>
                                                <span id="display_budget_amount" class="font-bold text-slate-700 text-xs sm:text-sm">฿0.00</span>
                                                <input type="hidden" id="budget_amount">
                                            </div>
                                            <div>
                                                <span class="text-slate-400 block text-[10px] font-sans font-bold uppercase">ใช้ไปแล้ว</span>
                                                <span id="display_budget_used" class="font-bold text-amber-600 text-xs sm:text-sm">฿0.00</span>
                                                <input type="hidden" id="budget_used">
                                            </div>
                                            <div class="pl-4 border-l border-slate-200/80">
                                                <span class="text-emerald-600 block text-[10px] font-sans font-bold uppercase">คงเหลือที่ใช้ได้</span>
                                                <span id="display_budget_remaining" class="font-black text-emerald-600 text-sm sm:text-base">฿0.00</span>
                                                <input type="hidden" id="budget_remaining">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. รายการค่าใช้จ่าย / ค่าอะไหล่ที่ขอเบิก -->
                                <div class="space-y-3 pt-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-sm shadow-blue-500/25 shrink-0">
                                                2
                                            </span>
                                            <h4 class="text-sm font-black text-slate-800 tracking-tight">
                                                รายการค่าใช้จ่าย / ค่าอะไหล่ที่ขอเบิก
                                            </h4>
                                        </div>
                                        <button type="button" onclick="addBudgetItem()" 
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 font-bold text-xs rounded-xl transition-all duration-200 shadow-2xs active:scale-95 cursor-pointer">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                            <span>เพิ่มรายการอีก</span>
                                        </button>
                                    </div>

                                    <!-- Modern Itemized Table -->
                                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                                        <div class="overflow-x-auto w-full max-w-full" style="-webkit-overflow-scrolling: touch;">
                                            <table class="w-full text-left border-collapse whitespace-nowrap min-w-[650px]">
                                                <thead>
                                                    <tr class="bg-slate-50/90 border-b border-slate-200 text-xs font-bold text-slate-600">
                                                        <th class="px-4 py-3">รายละเอียด / อะไหล่ / ค่าซ่อม</th>
                                                        <th class="px-4 py-3 text-center w-28">จำนวน</th>
                                                        <th class="px-4 py-3 text-center w-28">หน่วย</th>
                                                        <th class="px-4 py-3 text-right w-36">ราคา/หน่วย (บาท)</th>
                                                        <th class="px-4 py-3 text-right w-36">รวมเงิน (บาท)</th>
                                                        <th class="px-3 py-3 text-center w-12"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="budget-items-tbody" class="text-xs text-slate-700 divide-y divide-slate-100">
                                                    <!-- Default Row 1 -->
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
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Summary Bar -->
                                        <div class="px-6 py-4 bg-slate-50/80 flex flex-col sm:flex-row justify-between items-center gap-3 border-t border-slate-200">
                                            <div class="text-slate-500 text-xs sm:text-sm font-semibold italic" id="budget-text-amount">
                                                ( ศูนย์บาทถ้วน )
                                            </div>
                                            <div class="flex items-baseline gap-2.5">
                                                <span class="text-xs font-bold text-slate-600">ยอดเงินที่ขอเบิกรวม:</span>
                                                <span id="budget-grand-total" class="font-black text-blue-700 text-xl sm:text-2xl font-mono">0.00 บาท</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Over-budget Warning -->
                                    <div id="budget-exceed-warning" class="hidden p-3.5 bg-red-50 border border-red-200 rounded-2xl text-xs font-bold text-red-700 flex items-center gap-2">
                                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-sm"></i>
                                        <span id="budget-exceed-text">ยอดเงินที่ขอเบิกเกินกว่างบประมาณคงเหลือของโครงการ</span>
                                    </div>
                                </div>

                                <!-- 3. เหตุผลความจำเป็นในการขอเบิก & แนบเอกสารประกอบ -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                                    <!-- เหตุผลความจำเป็น -->
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-sm shadow-blue-500/25 shrink-0">
                                                3
                                            </span>
                                            <label for="budget_request_reason" class="text-sm font-black text-slate-800 tracking-tight">
                                                เหตุผลความจำเป็นในการขอเบิก <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                        <textarea id="budget_request_reason" rows="3" 
                                                  class="w-full px-4 py-3 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition font-medium text-xs sm:text-sm shadow-2xs resize-none text-slate-800 hover:border-slate-300"></textarea>
                                    </div>

                                    <!-- แนบเอกสารประกอบ -->
                                    <div class="space-y-2">
                                        <label class="text-sm font-black text-slate-800 tracking-tight block pt-0.5">
                                            แนบเอกสารประกอบ
                                        </label>
                                        <div class="p-4 bg-slate-50/80 border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-blue-50/30 rounded-2xl flex flex-col items-center justify-center gap-2 transition cursor-pointer group" onclick="document.getElementById('budget_file_input').click()">
                                            <div class="w-10 h-10 rounded-2xl bg-blue-50 group-hover:bg-blue-600 text-blue-600 group-hover:text-white transition-all flex items-center justify-center text-base">
                                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                            </div>
                                            <div class="text-center">
                                                <span class="text-xs font-bold text-blue-600 hover:underline">คลิกเพื่อเลือกไฟล์</span>
                                                <span class="text-xs text-slate-400 block mt-0.5">รองรับไฟล์รูปภาพ, PDF หรือเอกสาร</span>
                                            </div>
                                            <input type="file" id="budget_file_input" multiple class="hidden" onchange="handleBudgetFiles(this)">
                                            <div id="budget-file-list" class="flex flex-wrap items-center gap-1.5 mt-1.5"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row justify-end items-center gap-4 pt-6 border-t border-slate-100">

                                    <div class="flex items-center gap-3 w-full sm:w-auto">
                                        <button type="button" onclick="resetBudgetForm()" class="flex-1 sm:flex-none px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-xl transition cursor-pointer">
                                            ล้างข้อมูล
                                        </button>
                                        <button type="submit" class="flex-1 sm:flex-none px-7 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-xs sm:text-sm rounded-xl transition shadow-md shadow-blue-500/20 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                            <i class="fa-solid fa-floppy-disk text-sm"></i>
                                            <span>บันทึก</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>


<!-- ========================================== -->
                        <!-- VIEW 2: ประวัติการเบิกค่าซ่อม (History View) -->
                        <!-- ========================================== -->
                        <div id="req-section-history" class="space-y-6 hidden">

                            <!-- Controls: Search & Status Filter -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pb-2 border-b border-slate-100">
                                <div class="relative w-full sm:w-80">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" id="requisition-search-input" oninput="filterRequisitions()" 
                                           placeholder="ค้นหาเลขที่เบิก, โครงการ, รายการ..." 
                                           class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:outline-none focus:border-blue-500">
                                </div>


                            </div>

                            <!-- History Table -->
                            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                                <div class="overflow-x-auto w-full max-w-full" style="-webkit-overflow-scrolling: touch;">
                                    <table class="w-full text-left border-collapse whitespace-nowrap min-w-[950px]">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600">
                                                <th class="px-5 py-3">เลขที่เบิก</th>
                                                <th class="px-5 py-3">วันที่</th>
                                                <th class="px-5 py-3">โครงการ</th>
                                                <th class="px-5 py-3">แหล่งงบ</th>
                                                <th class="px-5 py-3">รายการ</th>
                                                <th class="px-5 py-3">เหตุผล</th>
                                                <th class="px-5 py-3 text-right">ยอดเงิน (บาท)</th>
                                                <th class="px-5 py-3 text-center">รายละเอียด</th>
                                            </tr>
                                        </thead>
                                        <tbody id="requisition-table-body" class="text-xs divide-y divide-slate-100 text-slate-700">
                                            @forelse($requisitionList as $req)
                                                @php
                                                    $isApproved = in_array(mb_strtolower(trim($req->status ?? ''), 'UTF-8'), ['อนุมัติแล้ว', 'เสร็จสิ้น']) || $req->budget_approved;
                                                    $isPending = in_array(mb_strtolower(trim($req->status ?? ''), 'UTF-8'), ['รอมอบหมาย', 'รอดำเนินการ', 'กำลังดำเนินการ', 'กำลังซ่อม']) || !$req->budget_approved;
                                                    $source = $req->budget_source ?? ($req->repairProject->budget_source ?? 'งบประมาณกลาง');
                                                    $projName = $req->repairProject ? $req->repairProject->name : ($req->project_name ?? 'โครงการซ่อมแซมทั่วไป');
                                                    $projCode = $req->repairProject ? $req->repairProject->project_code : '';
                                                    $rawDesc = $req->problem_description ?? $req->details ?? $req->description ?? $req->title ?? '-';
                                                    
                                                    // Clean symptom / reason helper (เอาเฉพาะอาการที่พบเท่านั้น)
                                                    $cleanHelper = function($str) {
                                                        if (empty($str) || $str === '-') return '-';
                                                        $res = $str;
                                                        $res = preg_replace('/\s*\([^)]*(วันที่สะดวก|ช่วงเวลา|เวลาที่สะดวก|เบอร์โทร|โทรศัพท์|รหัสอุปกรณ์|อุปกรณ์ที่พิมพ์เอง)[^)]*\)/u', '', $res);
                                                        $res = preg_replace('/\s*\(\s*(วันที่|เบอร์|โทร|tel|phone)[^)]*\)/iu', '', $res);
                                                        foreach ([
                                                            'ส่วนเวลาที่เสนอราคาค่าซ่อม:', 'วันที่เสนอราคาค่าซ่อม:', 'วันที่สะดวกให้เข้าซ่อม:',
                                                            'ช่วงเวลาที่สะดวกให้เข้าซ่อม:', 'ช่วงเวลาที่สะดวก:', 'เวลาที่สะดวก:',
                                                            'เบอร์โทรติดต่อผู้แจ้ง:', 'เบอร์โทรติดต่อ:', 'เบอร์โทร:', 'วันที่แจ้งซ่อม:', 'เวลาแจ้งซ่อม:'
                                                        ] as $lbl) {
                                                            $pos = mb_strpos($res, $lbl);
                                                            if ($pos !== false) $res = mb_substr($res, 0, $pos);
                                                        }
                                                        $res = preg_replace('/^\s*(อาการที่พบ|อาการเสีย|อาการชำรุด)\s*[:：]\s*/u', '', $res);
                                                        $res = preg_replace('/[\r\n\t]+/', ' ', $res);
                                                        $res = preg_replace('/\s+/', ' ', $res);
                                                        return trim($res) ?: '-';
                                                    };

                                                    $itemsText = '-';
                                                    $reasonText = '-';
                                                    if ($rawDesc && $rawDesc !== '-') {
                                                        if (str_contains($rawDesc, ' | ')) {
                                                            $parts = explode(' | ', $rawDesc, 2);
                                                            $itemsText = trim($parts[0]) ?: '-';
                                                            $reasonText = $cleanHelper($parts[1] ?? '');
                                                        } elseif (str_contains($rawDesc, '|')) {
                                                            $parts = explode('|', $rawDesc, 2);
                                                            $itemsText = trim($parts[0]) ?: '-';
                                                            $reasonText = $cleanHelper($parts[1] ?? '');
                                                        } else {
                                                            // Check if $rawDesc has items format with prices (e.g. '@ ฿' or '(@ ฿')
                                                            if (preg_match('/@\s*฿|\(\s*\d+\s*[^\s@()]+\s*@/u', $rawDesc)) {
                                                                $itemsText = trim($rawDesc);
                                                                $reasonText = '-';
                                                            } else {
                                                                // It is the repair problem description / symptom, NOT purchase items!
                                                                $itemsText = (float)($req->budget ?? 0) > 0 ? 'ค่าอะไหล่ / ค่าใช้จ่ายในการซ่อม' : '-';
                                                                $reasonText = $cleanHelper($rawDesc);
                                                            }
                                                        }
                                                    }
                                                    $displayReason = ($reasonText !== '-' ? $reasonText : $cleanHelper($rawDesc));
                                                    $hasActualItems = preg_match('/@\s*฿|\(\s*\d+\s*[^\s@()]+\s*@/u', $itemsText);
                                                    
                                                    if ($isApproved) {
                                                        $statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                                        $statusDot = 'bg-emerald-500';
                                                        $statusText = 'อนุมัติแล้ว';
                                                    } elseif ($isPending) {
                                                        $statusBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                                                        $statusDot = 'bg-amber-500';
                                                        $statusText = 'รอดำเนินการ';
                                                    } else {
                                                        $statusBadge = 'bg-slate-100 text-slate-600 border-slate-200';
                                                        $statusDot = 'bg-slate-400';
                                                        $statusText = $req->status;
                                                    }
                                                @endphp
                                                <tr class="requisition-row hover:bg-slate-50/80 transition"
                                                    data-code="{{ strtolower($req->request_code ?? '') }}"
                                                    data-project="{{ strtolower($projName) }}"
                                                    data-source="{{ strtolower($source) }}"
                                                    data-status="{{ $isApproved ? 'approved' : ($isPending ? 'pending' : 'cancelled') }}">
                                                    
                                                    <!-- 1. เลขที่เบิก -->
                                                    <td class="px-5 py-3.5">
                                                        <span class="font-mono font-bold text-blue-600 block text-xs">{{ $req->request_code }}</span>
                                                    </td>

                                                    <!-- 2. วันที่ -->
                                                    <td class="px-5 py-3.5 text-slate-500 text-xs">
                                                        {{ $req->created_at ? $req->created_at->format('d/m/Y H:i น.') : '-' }}
                                                    </td>

                                                    <!-- 3. โครงการ -->
                                                    <td class="px-5 py-3.5">
                                                        <div class="flex flex-col gap-0.5">
                                                            <span class="font-bold text-slate-800 truncate max-w-[200px]" title="{{ $projName }}">{{ $projName }}</span>
                                                            @if($projCode)
                                                                <span class="text-[10px] font-mono text-slate-400">({{ $projCode }})</span>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <!-- 4. แหล่งงบ -->
                                                    <td class="px-5 py-3.5">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                            {{ $source }}
                                                        </span>
                                                    </td>

                                                    <!-- 5. รายการ -->
                                                    <td class="px-5 py-3.5">
                                                        <p class="truncate max-w-[200px] text-slate-700 font-medium" title="{{ $itemsText }}">{{ $itemsText }}</p>
                                                    </td>

                                                    <!-- 6. เหตุผล -->
                                                    <td class="px-5 py-3.5">
                                                        <p class="truncate max-w-[200px] text-slate-500 font-medium" title="{{ $reasonText }}">{{ $reasonText }}</p>
                                                    </td>

                                                    <!-- 7. ยอดเงิน -->
                                                    <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 text-sm">
                                                        ฿{{ number_format((float)($req->budget ?? 0), 2) }}
                                                    </td>

                                                    <!-- 8. รายละเอียด & สั่งพิมพ์ -->
                                                    <td class="px-5 py-3.5 text-center">
                                                        <div class="flex items-center justify-center gap-1.5">
                                                            <button type="button" 
                                                                    onclick="openViewRequisitionModalFromBtn(this)"
                                                                    data-id="{{ $req->request_id ?? $req->request_code }}"
                                                                    data-code="{{ $req->request_code }}"
                                                                    data-date="{{ $req->created_at ? $req->created_at->format('d/m/Y H:i น.') : '-' }}"
                                                                    data-project="{{ $projName . ($projCode ? ' (' . $projCode . ')' : '') }}"
                                                                    data-source="{{ $source }}"
                                                                    data-amount="฿{{ number_format((float)($req->budget ?? 0), 2) }}"
                                                                    data-raw-amount="{{ (float)($req->budget ?? 0) }}"
                                                                    data-user="{{ optional($req->user)->name ?? '' }}"
                                                                    data-assignee="{{ optional($req->assignee)->name ?? '' }}"
                                                                    data-status="{{ $statusText }}"
                                                                    data-status-class="{{ $statusBadge }}"
                                                                    data-items="{{ e($hasActualItems ? $itemsText : '') }}"
                                                                    data-reason="{{ e($reasonText !== '-' ? $reasonText : '') }}"
                                                                    data-raw-desc="{{ e($rawDesc) }}"
                                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                                                <i class="fa-regular fa-eye"></i>
                                                                <span>ดู</span>
                                                            </button>
                                                            @php
                                                                $printUrl = url('/repair-requests/' . ($req->request_id ?: $req->request_code) . '/print-cost') . '?' . http_build_query([
                                                                    'auto_print' => '1',
                                                                    'code' => $req->request_code,
                                                                    'project_name' => $projName,
                                                                    'budget_source' => $source,
                                                                    'amount' => (float)($req->budget ?? 0),
                                                                    'items' => $hasActualItems ? $itemsText : '',
                                                                    'reason' => $displayReason,
                                                                    'user_name' => optional($req->user)->name ?? ''
                                                                ]);
                                                            @endphp
                                                            <a href="{{ $printUrl }}"
                                                               target="_blank"
                                                               rel="noopener noreferrer"
                                                               title="พิมพ์ใบบันทึกค่าซ่อม"
                                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-xs font-bold rounded-xl border border-blue-200 hover:border-blue-600 transition cursor-pointer">
                                                                <i class="fa-solid fa-print"></i>
                                                                <span>พิมพ์</span>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-6 py-12 text-center">
                                                        <div class="max-w-md mx-auto space-y-2 text-center">
                                                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mx-auto">
                                                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                                            </div>
                                                            <h4 class="text-sm font-bold text-slate-700">ยังไม่มีประวัติการเบิกค่าซ่อม</h4>
                                                            <button type="button" onclick="switchRequisitionView('form')" class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition">
                                                                + บันทึกค่าซ่อมใหม่
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse

                                            <tr id="req-no-results-row" class="hidden">
                                                <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                                                    <p class="font-bold text-slate-600 text-xs">ไม่พบรายการที่ตรงกับการค้นหา</p>
                                                    <button type="button" onclick="resetRequisitionFilters()" class="text-xs text-blue-600 underline mt-1 font-bold">ล้างการค้นหา</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Requisition Details Modal Overlay (Opens front and center) -->
                            <div id="inline-req-detail-card" onclick="if (event.target === this) closeViewRequisitionModal()" class="hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                                <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-sm">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-base font-black text-slate-800">รายละเอียดใบเบิกค่าซ่อม</h4>
                                                <span id="view-req-code" class="text-xs font-mono font-bold text-indigo-600">REQ-xxxx-xxxx</span>
                                            </div>
                                        </div>
                                        <button type="button" onclick="closeViewRequisitionModal()" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition cursor-pointer">
                                            <i class="fa-solid fa-xmark text-sm"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                                            <span class="text-slate-400 font-bold block mb-1">ยอดเงินที่ขอเบิก</span>
                                            <h3 id="view-req-amount" class="text-2xl font-black text-blue-700 font-mono">฿0.00</h3>
                                            <span class="text-[11px] text-slate-400 mt-1 block">วันที่ขอเบิก: <strong id="view-req-date" class="text-slate-700 font-semibold">-</strong></span>
                                        </div>

                                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-2">
                                            <span class="text-slate-400 font-bold block">ข้อมูลโครงการ</span>
                                            <div>
                                                <span class="text-slate-400 text-[11px] block">ชื่อโครงการ:</span>
                                                <strong id="view-req-project" class="text-slate-800 font-bold text-xs block truncate">-</strong>
                                            </div>
                                            <div>
                                                <span class="text-slate-400 text-[11px] block">แหล่งงบประมาณ:</span>
                                                <strong id="view-req-source" class="text-indigo-700 font-bold text-xs block">-</strong>
                                            </div>
                                        </div>

                                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 shadow-2xs space-y-2">
                                            <span class="text-slate-400 font-bold block">สถานะ</span>
                                            <div>
                                                <span id="view-req-status" class="px-3 py-1 rounded-full text-xs font-bold inline-block border bg-white text-slate-700 shadow-2xs">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 text-xs shadow-2xs">
                                        <span class="text-slate-400 font-bold block mb-1">รายละเอียด / เหตุผลความจำเป็นในการเบิก:</span>
                                        <p id="view-req-reason" class="text-slate-700 font-medium leading-relaxed bg-white p-3.5 rounded-xl border border-slate-200 whitespace-pre-wrap">-</p>
                                    </div>

                                    <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                                        <button type="button" onclick="printCurrentRequisition()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                                            <i class="fa-solid fa-print"></i> <span>พิมพ์เอกสาร</span>
                                        </button>
                                        <button type="button" onclick="closeViewRequisitionModal()" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                                            ปิด
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
</section>

        <script>
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
    
    // Initialize dropdown state
    if (typeof updateKindDropdown === 'function') {
        updateKindDropdown();
        filterDevices();
    }
});

            // ดูข้อมูลงาน/แผนกวิชาของวิทยาลัยเทคนิคยะลา แยกตามหน่วยงานภายใน
            const subDepartmentData = {
                "ฝ่ายบริหารทรัพยากร": [
                    "งานบริหารงานทั่วไป", "งานบุคลากร", "งานการเงิน", "งานบัญชี", 
                    "งานพัสดุ", "งานอาคารสถานที่", "งานทะเบียน", "งานประชาสัมพันธ์"
                ],
                "ฝ่ายยุทธศาสตร์และแผนงาน": [
                    "งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", 
                    "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", 
                    "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพประจำอำเภอยะหา"
                ],
                "ฝ่ายแผนงานและความร่วมมือ": [
                    "งานวางแผนและงบประมาณ", "งานศูนย์ข้อมูลสารสนเทศ", "งานความร่วมมือ", 
                    "งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ", "งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์", 
                    "งานประกันคุณภาพและมาตรฐานการศึกษา", "ศูนย์ฝึกอบรมวิชาชีพประจำอำเภอยะหา"
                ],
                "ฝ่ายพัฒนากิจการนักเรียน นักศึกษา": [
                    "งานกิจกรรมนักเรียนนักศึกษา", "งานครูที่ปรึกษา", "งานปกครอง", 
                    "งานแนะแนวอาชีพและการจัดหางาน", "งานสวัสดิการนักเรียน นักศึกษา", "งานโครงการพิเศษและบริการชุมชน"
                ],
                "ฝ่ายวิชาการ": [
                    "งานพัฒนาหลักสูตรการเรียนการสอน", "งานวัดผลและประเมินผล", "งานวิทยบริการและห้องสมุด", 
                    "งานสื่อการเรียนการสอน", "แผนกวิชาเครื่องกล", "แผนกวิชาช่างกลโรงงาน", 
                    "แผนกวิชาเทคนิคการเชื่อมและการผลิต", "แผนกวิชาช่างไฟฟ้ากำลัง", "แผนกวิชาช่างก่อสร้างและโยธา", 
                    "แผนกวิชาสถาปัตยกรรม", "แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร", "แผนกวิชาคอมพิวเตอร์ธุรกิจ", 
                    "แผนกวิชาสามัญสัมพันธ์", "แผนกวิชาช่างเทคนิคพื้นฐาน", "ภาควิชาเทคโนโลยียานยนต์", "ภาควิชาเทคโนโลยีไฟฟ้า",
                    "แผนกวิชาอิเล็กทรอนิกส์", "แผนกวิชาเทคโนโลยีคอมพิวเตอร์", "แมคคาทรอนิกส์และหุ่นยนต์"
                ]
            };

            function updateSubDepartments(savedValue = null) {
                const mainDeptSelect = document.getElementById("department-group-input");
                const subDeptSelect = document.getElementById("department-input");
                const selectedMain = mainDeptSelect.value;

                subDeptSelect.innerHTML = "";

                if (selectedMain === "") {
                    subDeptSelect.disabled = true;
                    const defaultOpt = document.createElement("option");
                    defaultOpt.value = "";
                    defaultOpt.text = "-- ไม่มีข้อมูลแผนก --";
                    subDeptSelect.appendChild(defaultOpt);
                } else {
                    const defaultOpt = document.createElement("option");
                    defaultOpt.value = "";
                    defaultOpt.text = "เลือกแผนก";
                    subDeptSelect.appendChild(defaultOpt);

                    if (subDepartmentData[selectedMain]) {
                        subDepartmentData[selectedMain].forEach(function(item) {
                            const opt = document.createElement("option");
                            opt.value = item;
                            opt.text = item;
                            if (savedValue && savedValue === item) {
                                opt.selected = true;
                            }
                            subDeptSelect.appendChild(opt);
                        });
                    }
                }
                // ล็อคไม่ให้แก้ไข
                subDeptSelect.disabled = true;
            }

            document.addEventListener("DOMContentLoaded", function() {
                const currentDept = "{{ old('department', $user->department) }}";
                const currentPos = "{{ old('position', $user->position) }}";
                if (currentDept) {
                    document.getElementById("department-group-input").value = currentDept;
                    updateSubDepartments(currentPos);
                }
                
                // Update device code display on load
                updateDeviceCode();
            });

            // Evaluation modal functions are managed in teacher/scripts.blade.php

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
                        wrapper.appendChild(img);
                    }
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            // switchTab in scripts.blade.php

            function switchTabAndHighlight(tabId) {
                const link = document.querySelector('[onclick*="' + tabId + '"]');
                if (link) switchTab(tabId, link);
            }



            function togglePwd(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon  = document.getElementById(iconId);
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }

            // ฟ้า session ก่อนหน้าอยู่ที่ tab-profile ให้เบิกโดยอัตโนมัติ
            @if(session('active_tab') === 'tab-profile' || $errors->hasAny(['current_password','name','email','new_password']))
            document.addEventListener('DOMContentLoaded', () => {
                const profileTab = document.querySelector('[onclick="switchTab(\'tab-profile\', this)"]');
                if (profileTab) switchTab('tab-profile', profileTab);
            });
            @endif

            @if(session('active_tab') === 'tab-status')
            document.addEventListener('DOMContentLoaded', () => {
                const statusTab = document.querySelector('[onclick*="tab-status"]');
                if (statusTab) switchTab('tab-status', statusTab);
            });
            @endif

            

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    closeDetailModal();
                    closeHistoryModal();
                    closeEvalModal();
                }
            });

            function openHistoryModal(data) {
                const modal = document.getElementById('history-modal');
                const box = document.getElementById('history-modal-box');

                document.getElementById('hm-code').textContent = data.code;
                document.getElementById('hm-title').textContent = data.title;
                document.getElementById('hm-device-name').textContent = data.device;
                document.getElementById('hm-location').textContent = data.location;
                document.getElementById('hm-type').textContent = data.type;
                let cleanDesc = (data.description || '').replace(/(ช่วงเวลาที่สะดวก(ให้เข้าซ่อม)?|วันที่สะดวก(ให้เข้าซ่อม)?|เวลาที่สะดวก)[^\n\r]*/gi, '').trim();
                document.getElementById('hm-description').textContent = cleanDesc;
                document.getElementById('hm-assignee').textContent = data.assignee;
                document.getElementById('hm-created').textContent = data.created_at;
                document.getElementById('hm-finished').textContent = data.finished_at;
                document.getElementById('hm-note').textContent = data.note;

                // Priority
                const prioEl = document.getElementById('hm-priority');
                if (data.priority === 'ด่วนที่สุด') {
                    prioEl.textContent = '�??� ด่วนที่สุด';
                    prioEl.className = 'text-sm font-black text-blue-600';
                } else {
                    prioEl.textContent = '�??� ระดับปกติ';
                    prioEl.className = 'text-sm font-bold text-emerald-600';
                }

                // Status
                const statusEl = document.getElementById('hm-status');
                statusEl.textContent = data.status;
                statusEl.className = 'text-xs font-extrabold px-3 py-1 rounded-full border whitespace-nowrap flex-shrink-0 ' + data.statusClass;

                // Stars
                const starsEl = document.getElementById('hm-stars');
                const ratingBox = document.getElementById('hm-rating-box');
                if (data.rating > 0) {
                    ratingBox.classList.remove('hidden');
                    starsEl.innerHTML = Array.from({length: 5}, (_, i) =>
                        `<i class="fa-solid fa-star text-lg ${i < data.rating ? 'text-amber-400' : 'text-slate-200'}"></i>`
                    ).join('');
                } else {
                    ratingBox.classList.add('hidden');
                }

                // Show
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
                box.classList.remove('scale-100', 'opacity-100');
                box.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.remove('flex');
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 200);
            }
        </script>
    </main>

    <!-- History Detail Modal -->
    <div id="history-modal" onclick="closeHistoryModal(event)" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div id="history-modal-box" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-4 flex justify-between items-center">
                <div>
                    <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest">ประวัติการซ่อมย้อนหลัง</p>
                    <h3 id="hm-code" class="text-white font-black text-lg mt-0.5"></h3>
                </div>
                <button onclick="closeHistoryModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <!-- Title + Status -->
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p id="hm-title" class="font-black text-slate-800 text-base leading-snug"></p>
                        <p id="hm-device" class="text-xs text-slate-500 font-semibold mt-0.5"><i class="fa-solid fa-desktop mr-1 text-slate-400" id="hm-device-icon"></i><span id="hm-device-name"></span></p>
                    </div>
                    <span id="hm-status" class="text-xs font-extrabold px-3 py-1 rounded-full border whitespace-nowrap flex-shrink-0"></span>
                </div>

                <hr class="border-slate-100">

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-location-dot text-red-400 mr-1"></i>สถานที่</p>
                        <p id="hm-location" class="text-sm font-bold text-slate-700"></p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-tag text-slate-400 mr-1"></i>ประเภท</p>
                        <p id="hm-type" class="text-sm font-bold text-slate-700"></p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-fire text-orange-400 mr-1"></i>ความเร่งด่วน</p>
                        <p id="hm-priority" class="text-sm font-bold"></p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-user-wrench text-slate-400 mr-1"></i>ช่างผู้ซ่อม</p>
                        <p id="hm-assignee" class="text-sm font-bold text-slate-700"></p>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                    <p class="text-[10px] font-extrabold text-amber-500 uppercase tracking-wider mb-2"><i class="fa-solid fa-note-sticky mr-1"></i>อาการชำรุดที่แจ้ง</p>
                    <p id="hm-description" class="text-sm text-slate-700 font-medium leading-relaxed"></p>
                </div>

                <!-- Rating -->
                <div id="hm-rating-box" class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                    <p class="text-[10px] font-extrabold text-amber-500 uppercase tracking-wider mb-2"><i class="fa-solid fa-star mr-1"></i>ผลการประเมินความพึงพอใจ</p>
                    <div id="hm-stars" class="flex gap-1"></div>
                </div>

                <!-- Note -->
                <div id="hm-note-box" class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2"><i class="fa-solid fa-comment-dots mr-1"></i>หมายเหตุ / บันทึกปิดงาน</p>
                    <p id="hm-note" class="text-sm text-slate-600 font-medium"></p>
                </div>

                <!-- Timestamps -->
                <div class="flex gap-3">
                    <div class="flex-1 bg-slate-50 rounded-xl px-3 py-2 border border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase"><i class="fa-regular fa-calendar mr-1"></i>วันที่เช่น</p>
                        <p id="hm-created" class="text-xs font-black text-slate-700 mt-0.5"></p>
                    </div>
                    <div class="flex-1 bg-slate-50 rounded-xl px-3 py-2 border border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase"><i class="fa-solid fa-flag-checkered mr-1"></i>เครื่องมือ</p>
                        <p id="hm-finished" class="text-xs font-black text-slate-700 mt-0.5"></p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeHistoryModal()" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-sm transition">
                    <i class="fa-solid fa-xmark mr-1"></i> ปิด
                </button>
            </div>
        </div>
    </div>

            
            
            
            </main>
    </div>

    @include('dashboards.teacher.modals')
    @include('dashboards.teacher.scripts')

<script>
    // =========================================================================
    // Itemized Requisition & Budget Helper Functions (Exact Matching & Polished)
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
            // If only one row, just clear it rather than removing
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

        // Grand Total Number
        const totalElem = document.getElementById('budget-grand-total');
        if (totalElem) {
            totalElem.innerText = total.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' บาท';
        }

        // Baht Text
        const textElem = document.getElementById('budget-text-amount');
        if (textElem) {
            textElem.innerText = total > 0 ? `( ${bahtText(total)} )` : '( ศูนย์บาทถ้วน )';
        }

        // Budget Exceed Warning
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

        // Calculate grand total and items list
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

        // Combine items & reason
        const combinedReason = (itemSummaries.join(', ') + ' | ' + reasonText).trim();

        // SweetAlert Confirmation
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

            const selectedJobCodeInput = document.getElementById('selected_repair_request_code');
            const rjiCodeEl = document.getElementById('rji-job-code');
            const linkedJobCode = (selectedJobCodeInput && selectedJobCodeInput.value.trim())
                ? selectedJobCodeInput.value.trim()
                : ((rjiCodeEl && rjiCodeEl.textContent && rjiCodeEl.textContent.trim() !== '—')
                    ? rjiCodeEl.textContent.trim()
                    : null);

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
                    source_page: 'หน้าแจ้งซ่อม',
                    repair_request_code: linkedJobCode
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    opt.setAttribute('data-used', data.project.used_budget);
                    opt.setAttribute('data-remaining', data.project.remaining);

                    const isJobLinked = !!data.linked_job;
                    Swal.fire({
                        title: isJobLinked ? 'บันทึกค่าซ่อมสำเร็จ!' : 'ตัดยอดงบประมาณสำเร็จ!',
                        html: `${isJobLinked ? 'บันทึกค่าซ่อมและอัปเดตสถานะใบแจ้งซ่อมเป็น <b class="text-blue-600 font-bold">"กำลังซ่อม"</b> เรียบร้อยแล้ว' : 'บันทึกคำขอเบิกและตัดยอดงบประมาณโครงการเรียบร้อยแล้ว'}<br>
                               <div class="mt-3 p-3 bg-slate-50 rounded-xl text-left text-xs font-semibold text-slate-600 space-y-1 border border-slate-100">
                                   <p>• ยอดค่าซ่อม/ตัดงบ: <b class="text-blue-600 font-bold">฿${totalAmount.toLocaleString('th-TH', {minimumFractionDigits: 2})}</b></p>
                                   <p>• ใช้ไปแล้วรวม: <b class="text-amber-600 font-bold">฿${parseFloat(data.project.used_budget).toLocaleString('th-TH', {minimumFractionDigits: 2})}</b></p>
                                   <p>• คงเหลือสุทธิ: <b class="text-emerald-600 font-bold">฿${parseFloat(data.project.remaining).toLocaleString('th-TH', {minimumFractionDigits: 2})}</b></p>
                               </div>`,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: '🖨️ พิมพ์ใบบันทึกค่าซ่อม',
                        cancelButtonText: isJobLinked ? 'ติดตามสถานะการซ่อม' : 'ไปที่ประวัติการเบิก',
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
                        if (isJobLinked) {
                            window.location.href = window.location.pathname + '?tab=status';
                        } else {
                            window.location.href = window.location.pathname + '?tab=requisition&view=history';
                        }
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
    let currentRequisitionStatusFilter = 'all';

    function setRequisitionStatusFilter(status, btn) {
        currentRequisitionStatusFilter = status;
        document.querySelectorAll('.req-status-btn').forEach(el => {
            el.className = 'req-status-btn px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:text-slate-900 cursor-pointer';
        });
        if (btn) {
            btn.className = 'req-status-btn active px-3.5 py-1.5 rounded-lg text-xs font-black bg-white text-blue-700 shadow-2xs cursor-pointer';
        }
        filterRequisitions();
    }

    function filterRequisitions() {
        const searchInput = document.getElementById('requisition-search-input');
        const term = (searchInput ? searchInput.value : '').toLowerCase().trim();

        const rows = document.querySelectorAll('.requisition-row');
        rows.forEach(row => {
            const code = (row.getAttribute('data-code') || '').toLowerCase();
            const project = (row.getAttribute('data-project') || '').toLowerCase();
            const desc = (row.getAttribute('data-desc') || '').toLowerCase();
            const status = (row.getAttribute('data-status') || '').toLowerCase();

            const matchesSearch = !term || code.includes(term) || project.includes(term) || desc.includes(term);
            const matchesStatus = currentRequisitionStatusFilter === 'all' || status === currentRequisitionStatusFilter.toLowerCase();

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
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

    // Print current requisition being viewed in detail card
    function printCurrentRequisition() {
        if (!currentRequisitionData) return;
        const reqId = currentRequisitionData.id || currentRequisitionData.code;
        printRepairCost(reqId, currentRequisitionData);
    }

    // Open dedicated printable A4 repair cost document
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

    // Preview / Print current form values before saving
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

    // Default view on page load: always default to 'form' for ease of use unless ?view=history is in URL
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const reqView = params.get('view');
        if (reqView === 'history') {
            switchRequisitionView('history');
        } else {
            switchRequisitionView('form');
        }
    });

    // Navigate to repair record tab and pre-fill with job data
    function goToRepairRecord(data) {
        // 1. Switch to tab-requisition and show form view
        switchTab('tab-requisition', null);
        setTimeout(function() {
            switchRequisitionView('form');
        }, 50);

        setTimeout(function() {
            // 2. Show and populate the info card
            const infoCard = document.getElementById('repair-job-info-card');
            if (infoCard) {
                // Fill in each field
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

            // Sync with select and hidden code input
            const jobSelect = document.getElementById('link_repair_job_select');
            if (jobSelect && data.job_code) {
                jobSelect.value = data.job_code;
            }
            const hiddenCode = document.getElementById('selected_repair_request_code');
            if (hiddenCode) {
                hiddenCode.value = data.job_code || '';
            }

            // 4. Pre-fill first item row description
            const tbody = document.getElementById('budget-items-tbody');
            if (tbody) {
                const firstRow = tbody.querySelector('.budget-item-row');
                if (firstRow) {
                    const descInput = firstRow.querySelector('input[type="text"]');
                    if (descInput && !descInput.value) {
                        const detail = (data.repair_details && data.repair_details.trim())
                            ? data.repair_details.trim()
                            : (data.description && data.description.trim()
                                ? data.description.trim()
                                : '');
                        if (detail) {
                            descInput.value = detail;
                        }
                    }
                }
            }

            // 5. Scroll info card into view smoothly
            const formSection = document.getElementById('req-section-form');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 150);
    }

    function handleJobSelectChange(select) {
        const val = select.value;
        const hiddenCode = document.getElementById('selected_repair_request_code');
        if (hiddenCode) hiddenCode.value = val;

        const opt = select.options[select.selectedIndex];
        if (!val || !opt) {
            closeRepairJobInfoCard(false);
            return;
        }

        const data = {
            job_code: opt.getAttribute('data-code') || val,
            device: opt.getAttribute('data-device') || '',
            location: opt.getAttribute('data-location') || '',
            repair_details: opt.getAttribute('data-desc') || '',
            description: opt.getAttribute('data-desc') || ''
        };

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

            if (data.repair_details && detailEl && detailRow) {
                detailEl.textContent = data.repair_details;
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
                if (descInput && !descInput.value && data.repair_details) {
                    descInput.value = data.repair_details;
                }
            }
        }
    }

    function closeRepairJobInfoCard(resetSelect = true) {
        const card = document.getElementById('repair-job-info-card');
        if (card) card.classList.add('hidden');
        const hiddenCode = document.getElementById('selected_repair_request_code');
        if (hiddenCode) hiddenCode.value = '';
        if (resetSelect) {
            const jobSelect = document.getElementById('link_repair_job_select');
            if (jobSelect) jobSelect.value = '';
        }
        const codeEl = document.getElementById('rji-job-code');
        if (codeEl) codeEl.textContent = '—';
    }
</script>


</body>
</html>

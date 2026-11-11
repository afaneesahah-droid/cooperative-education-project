@php
    $pendingAccept  = $requests->where('assigned_to', $user->id)->where('status', 'รอรับงาน');
    $assignedRepairs = $requests->where('assigned_to', $user->id)->where('status', 'กำลังดำเนินการ');
    $myWorkLogs = $requests->where('assigned_to', $user->id)->filter(function($j) {
        return !in_array($j->status, ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์', 'ซ่อมเสร็จ', 'ยกเลิก'])
            && !str_starts_with($j->status, 'ซ่อมเสร็จ')
            && !str_starts_with($j->status, 'ซ่อมสำเร็จ')
            && !str_starts_with($j->status, 'ยกเลิก');
    });
    $assignedTasks = $requests->where('assigned_to', $user->id)->filter(function($j) {
        return !in_array($j->status, ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์', 'ซ่อมเสร็จ', 'ยกเลิก'])
            && !str_starts_with($j->status, 'ซ่อมเสร็จ')
            && !str_starts_with($j->status, 'ซ่อมสำเร็จ')
            && !str_starts_with($j->status, 'ยกเลิก');
    });
    $activeAssignedTasks = $assignedTasks->whereNotIn('status', ['ไม่สามารถซ่อมได้', 'ไม่อนุมัติงบ/ซ่อมไม่ได้']);

    $year = date('Y') + 543;
    $count = \App\Models\RepairRequest::whereYear('created_at', date('Y'))->count() + 1;
    $nextRequestCode = 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    if (!function_exists('getDeviceIcon')) {
        function getDeviceIcon($deviceName) {
            $name = mb_strtolower($deviceName);
            if (str_contains($name, 'notebook') || str_contains($name, 'โน้ตบุ๊ก') || str_contains($name, 'โน๊ตบุ๊ค') || str_contains($name, 'laptop')) {
                return 'fa-laptop';
            } elseif (str_contains($name, 'mouse') || str_contains($name, 'เมาส์')) {
                return 'fa-computer-mouse';
            } elseif (str_contains($name, 'keyboard') || str_contains($name, 'คีย์บอร์ด') || str_contains($name, 'แป้นพิมพ์')) {
                return 'fa-keyboard';
            } elseif (str_contains($name, 'printer') || str_contains($name, 'เครื่องพิมพ์') || str_contains($name, 'ปริ้นเตอร์')) {
                return 'fa-print';
            } elseif (str_contains($name, 'server') || str_contains($name, 'เซิร์ฟเวอร์')) {
                return 'fa-server';
            } elseif (str_contains($name, 'network') || str_contains($name, 'router') || str_contains($name, 'switch') || str_contains($name, 'เครือข่าย')) {
                return 'fa-network-wired';
            } elseif (str_contains($name, 'monitor') || str_contains($name, 'จอ') || str_contains($name, 'โปรเจคเตอร์')) {
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
    <title>ส่วนปฏิบัติงานเจ้าหน้าที่ช่างบำรุง (Tactical Console) - วิทยาลัยเทคนิคยะลา</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
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
        @keyframes gear-rotation { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .spinning-gear { animation: gear-rotation 4s linear infinite; }
        @keyframes border-pulse-red { 0%, 100% { border-color: rgba(239, 68, 68, 0.3); box-shadow: 0 0 4px rgba(239, 68, 68, 0.1); } 50% { border-color: rgba(37, 99, 235, 1); box-shadow: 0 0 15px rgba(220, 38, 38, 0.3); } }
        .emergency-card { animation: border-pulse-red 2s infinite ease-in-out; border-width: 2px; }
        .grid-blueprint-light { background-image: linear-gradient(to right, rgba(220, 38, 38, 0.015) 1px, transparent 1px), linear-gradient(to bottom, rgba(220, 38, 38, 0.015) 1px, transparent 1px); background-size: 25px 25px; }
        .white-panel { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02); }

        /* Tab styles */
        .tab-nav-btn {
            position: relative;
            padding: 0.75rem 1.25rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
            white-space: nowrap;
            cursor: pointer;
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tab-nav-btn:hover {
            color: #dc2626;
            background-color: #fff1f2;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .tab-nav-btn.active {
            color: #dc2626;
            border-bottom-color: #dc2626;
            font-weight: 800;
        }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Stat card clickable */
        .stat-tab-card, .stat-assign-card, .stat-history-card, .stat-history-filter-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .stat-tab-card:hover, .stat-assign-card:hover, .stat-history-card:hover, .stat-history-filter-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(0,0,0,0.08);
        }
        .stat-tab-card.active-stat, .stat-assign-card.active-stat, .stat-history-card.active-stat, .stat-history-filter-card.active-stat {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased h-screen w-full max-w-full flex flex-col font-semibold selection:bg-blue-500 selection:text-white overflow-hidden">

    @include('dashboards.technician.header')

    <div class="flex-1 flex flex-row w-full h-full overflow-hidden relative">
        @include('dashboards.technician.sidebar')

        <main class="flex-1 min-w-0 h-full overflow-y-auto bg-slate-100 p-3 sm:p-5 md:p-8">
            <section class="w-full mx-auto space-y-6">
            

            
<!-- Tab 2: ติดตามสถานะ -->
            
<!-- Tab 1: บันทึกข้อมูลการปฏิบัติงานซ่อม -->
            <div id="tab-report" class="tab-content block space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="bg-white rounded-3xl shadow-[0_4px_25px_-5px_rgba(0,0,0,0.06)] border border-slate-100 overflow-hidden flex flex-col">
                    <!-- Compact Modern Header -->
                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between relative overflow-hidden">
                        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                        <div class="flex items-center gap-3 relative z-10">
                            <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-base border border-white/25 shadow-inner">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-white tracking-wide">บันทึกข้อมูลการแจ้งซ่อม</h2>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-white/20 text-white text-xs font-black border border-white/25 backdrop-blur-sm relative z-10">
                            {{ $myWorkLogs->count() }} รายการ
                        </span>
                    </div>

                    <div class="p-4 sm:p-6 bg-slate-50/40 space-y-3">
                    @if($myWorkLogs->isEmpty())
                        <div class="text-center py-14 text-slate-400 border border-slate-200/60 rounded-2xl bg-white shadow-xs">
                            <div class="text-4xl text-slate-300 mb-2"><i class="fa-solid fa-clipboard-check text-emerald-500"></i></div>
                            <h3 class="text-sm font-bold text-slate-700">ยังไม่มีบันทึกการปฏิบัติงานซ่อม</h3>
                            <p class="text-xs text-slate-400 mt-1">เมื่อคุณมีงานซ่อมที่ได้รับมอบหมาย รายการงานซ่อมจะมาปรากฏที่นี่</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($myWorkLogs as $job)
                                @php
                                    $statusIcon = "fa-spinner";
                                    $iconBg = "bg-teal-50 text-teal-600 border-teal-200";
                                    $statusBadge = "bg-teal-50 text-teal-700 border-teal-200";
                                    $statusText = $job->status;
                                    
                                    if ($job->status === 'เสร็จสิ้น' || $job->status === 'ซ่อมเสร็จสมบูรณ์') {
                                        $statusIcon = "fa-circle-check";
                                        $iconBg = "bg-emerald-50 text-emerald-600 border-emerald-200";
                                        $statusBadge = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                    } elseif (str_starts_with($job->status, 'ยกเลิก')) {
                                        $statusIcon = "fa-ban";
                                        $iconBg = "bg-slate-100 text-slate-500 border-slate-200";
                                        $statusBadge = "bg-slate-100 text-slate-600 border-slate-200";
                                    } elseif ($job->status === 'รออนุมัติงบประมาณ') {
                                        $statusIcon = "fa-hourglass-half";
                                        $iconBg = "bg-amber-50 text-amber-600 border-amber-200";
                                        $statusBadge = "bg-amber-50 text-amber-700 border-amber-200 animate-pulse";
                                    } elseif ($job->status === 'ยืนยันการซื้ออะไหล่สำเร็จ') {
                                        $statusIcon = "fa-check-double";
                                        $iconBg = "bg-indigo-50 text-indigo-600 border-indigo-200";
                                        $statusBadge = "bg-indigo-50 text-indigo-700 border-indigo-200";
                                    } elseif ($job->status === 'รอรับงาน') {
                                        $statusIcon = "fa-hand-pointer";
                                        $iconBg = "bg-amber-50 text-amber-600 border-amber-200";
                                        $statusBadge = "bg-amber-50 text-amber-700 border-amber-200";
                                    } else {
                                        $statusIcon = "fa-screwdriver-wrench";
                                        $iconBg = "bg-blue-50 text-blue-600 border-blue-200";
                                        $statusBadge = "bg-blue-50 text-blue-700 border-blue-200";
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
                                @endphp
                                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col gap-3.5 relative overflow-hidden group">
                                    <!-- Top Row: Icon, Device Name, REQ, Status, Timestamp -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base border {{ $iconBg }} shrink-0 shadow-2xs">
                                                <i class="fa-solid {{ getDeviceIcon($job->device->name ?? '') }}"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-[10px] font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">REQ: {{ $job->request_code }}</span>
                                                    @if($job->device && $job->device->device_code)
                                                        <span class="text-[10px] font-mono font-medium text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">รหัส: {{ $job->device->device_code }}</span>
                                                    @endif
                                                </div>
                                                <h3 class="text-sm sm:text-base font-black text-slate-800 truncate mt-0.5">{{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}</h3>
                                            </div>
                                        </div>
                                        <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center gap-1 shrink-0">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $statusBadge }} flex items-center gap-1.5 shadow-2xs">
                                                <i class="fa-solid {{ $statusIcon }} text-[10px]"></i> {{ $statusText }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-medium">
                                                <i class="fa-regular fa-clock mr-1"></i>{{ $job->updated_at ? \Carbon\Carbon::parse($job->updated_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Middle Row: Compact 3-Column Info (Symptoms, Location, Reporter) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                                <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1"></i>อาการที่พบ
                                            </span>
                                            <p class="font-bold text-slate-700 truncate" title="{{ $job->clean_symptom }}">{{ $job->clean_symptom ?: '-' }}</p>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                                <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i>สถานที่ตั้ง
                                            </span>
                                            <p class="font-medium text-slate-600 truncate" title="{{ $displayLoc }}">{{ $displayLoc }}</p>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                                                <i class="fa-solid fa-user text-indigo-500 mr-1"></i>ผู้แจ้งซ่อม
                                            </span>
                                            <p class="font-medium text-slate-600 truncate">{{ $job->user->name ?? 'ไม่ระบุ' }}</p>
                                        </div>
                                    </div>

                                    <!-- Bottom Row: Technician Note & Action Buttons -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-1">
                                        <div class="text-xs text-slate-500 min-w-0 flex items-center gap-1.5">
                                            @if($job->note)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[11px] font-medium border border-indigo-100 max-w-full truncate">
                                                    <i class="fa-solid fa-clipboard-list shrink-0 text-indigo-500"></i>
                                                    <span class="truncate italic">"{{ $job->note }}"</span>
                                                </span>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">
                                                    <i class="fa-regular fa-comment-dots mr-1"></i>ยังไม่มีบันทึกการทำงาน
                                                </span>
                                            @endif
                                        </div>

                                        @php
                                            // ตรวจสอบว่าต้องบันทึกค่าซ่อมหรือไม่:
                                            // หากสถานะเป็น 'รอเปลี่ยนอะไหล่' หรือ 'รออะไหล่' จะแสดงปุ่ม "ซ่อมเสร็จ" ก็ต่อเมื่อผู้แจ้งซ่อมบันทึกค่าซ่อมเสร็จแล้วเท่านั้น
                                            $hasCostRecorded = ($job->budget > 0) || ($job->budget_approved);
                                            $needsCostRecording = in_array($job->status, ['รอเปลี่ยนอะไหล่', 'รออะไหล่']);
                                            $canShowFinish = ($job->status === 'กำลังซ่อม') || ($needsCostRecording && $hasCostRecorded);

                                            $jobPayload = [
                                                'id' => $job->id,
                                                'code' => $job->request_code,
                                                'device' => $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ',
                                                'location' => $displayLoc,
                                                'reporter' => $job->user->name ?? 'ครู/บุคลากร',
                                                'description' => $job->clean_symptom ?: ($job->description ?: '-'),
                                                'status' => $job->status,
                                                'inspection_result' => $job->inspection_result ?? '',
                                                'repair_evaluation' => $job->repair_evaluation ?? '',
                                                'repair_details' => $job->repair_details ?? '',
                                                'unrepairable_reason' => $job->unrepairable_reason ?? '',
                                                'note' => $job->note ?? '',
                                                'budget' => (float)($job->budget ?? 0),
                                                'has_cost' => $hasCostRecorded,
                                                'needs_cost' => $needsCostRecording,
                                                'image' => $job->image ? asset($job->image) : '',
                                            ];
                                        @endphp

                                        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap justify-end shrink-0">
                                            @if($canShowFinish)
                                                <form action="{{ route('repair.finish', $job->id) }}" method="POST" class="inline" onclick="event.stopPropagation();" id="finish-repair-form-{{ $job->id }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="เสร็จสิ้น">
                                                    <input type="hidden" name="note" id="finish-repair-note-{{ $job->id }}" value="">
                                                    <button type="button" 
                                                        data-job-id="{{ $job->id }}"
                                                        data-job-code="{{ $job->request_code }}"
                                                        data-job-device="{{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}"
                                                        data-job-note="{{ $job->note }}"
                                                        onclick="openFinishRepairModal(this)" 
                                                        class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                                        <i class="fa-solid fa-check"></i> ซ่อมเสร็จ
                                                    </button>
                                                </form>
                                            @elseif($needsCostRecording)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-700 border border-amber-200/80 rounded-xl text-xs font-bold shadow-2xs select-none">
                                                    <i class="fa-solid fa-hourglass-half text-amber-500 animate-pulse"></i> รอผู้แจ้งซ่อมบันทึกค่าซ่อม
                                                </span>
                                            @endif

                                            @if(in_array($job->status, ['กำลังดำเนินการ', 'รอเปลี่ยนอะไหล่', 'กำลังซ่อม']) && !$job->budget_approved)
                                                <button type="button" 
                                                    onclick="goToRepairRecord({
                                                        job_code: '{{ $job->request_code }}',
                                                        device: '{{ addslashes($job->device ? ($job->device->name ?? $job->device->device_name ?? $job->type) : ($job->type ?? 'อุปกรณ์')) }}',
                                                        location: '{{ addslashes($displayLoc ?? '') }}',
                                                        repair_details: '{{ addslashes($job->repair_details ?? '') }}',
                                                        description: '{{ addslashes($job->clean_symptom ?: ($job->description ?: '')) }}'
                                                    })"
                                                    class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                                    <i class="fa-solid fa-file-invoice-dollar"></i> ขอเบิกงบ
                                                </button>
                                            @endif

                                            <button type="button" 
                                                data-job="{{ json_encode($jobPayload, JSON_UNESCAPED_UNICODE) }}"
                                                onclick="openUpdateModal(this)"
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer select-none">
                                                <i class="fa-solid fa-pen-to-square pointer-events-none"></i>
                                                <span class="pointer-events-none">บันทึกข้อมูลการซ่อม</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    </div>
                </div>
            </div>

            <!-- Tab: งานที่ได้รับมอบหมาย -->
            <div id="tab-assigned" class="tab-content hidden space-y-5 animate-in fade-in slide-in-from-bottom-4 duration-500">
                


                {{-- กล่องรายการงานที่ได้รับมอบหมาย --}}
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden flex flex-col">
                    <!-- Card Header with Tactical Blue Gradient -->
                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 px-6 py-5 relative overflow-hidden">
                        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-xl border border-white/30 shadow-inner">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-xl font-black text-white tracking-wide">งานที่ได้รับมอบหมาย</h2>
                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-500/50 text-white text-[10px] font-extrabold border border-white/20">ตารางสรุปงาน</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full md:w-auto">
                                <div class="relative flex-1 md:w-72">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-100/70"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="text" id="search-assigned" onkeyup="filterAssigned()" placeholder="ค้นหารหัสใบงาน, ชนิดทรัพย์สิน, อาการ, ผู้แจ้ง..." class="w-full pl-11 pr-4 py-2 bg-white/15 border border-white/25 rounded-full text-xs sm:text-sm font-semibold text-white placeholder-blue-100/70 focus:outline-none focus:bg-white/25 focus:border-white/50 transition-all shadow-inner backdrop-blur-sm">
                                </div>
                                <div class="flex items-center bg-white/15 p-1 rounded-xl border border-white/25 backdrop-blur-sm shrink-0">
                                    <button type="button" id="btn-view-card" onclick="setAssignedViewMode('card')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-white text-blue-700 shadow-sm cursor-pointer" title="แสดงแบบการ์ด">
                                        <i class="fa-solid fa-grip"></i>
                                        <span>การ์ด</span>
                                    </button>
                                    <button type="button" id="btn-view-table" onclick="setAssignedViewMode('table')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-white/80 hover:text-white hover:bg-white/10 cursor-pointer" title="แสดงแบบตาราง">
                                        <i class="fa-solid fa-table-list"></i>
                                        <span>ตาราง</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 md:p-8 bg-slate-50/40 space-y-6">
                        <!-- Stat Summary Cards (Interactive Filter) -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                            <!-- 1. งานทั้งหมด -->
                            <div onclick="filterAssignedJobs('งานทั้งหมด', this)" class="stat-assign-card active-stat bg-white rounded-2xl p-4 border border-blue-200 shadow-sm flex flex-col justify-between border-l-4 border-l-blue-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-black text-blue-700 uppercase tracking-wider">งานทั้งหมด</span>
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-list-check"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-2xl sm:text-3xl font-black text-blue-600 tracking-tight">{{ $assignedTasks->count() }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>
                            
                            <!-- 2. รอรับงาน -->
                            <div onclick="filterAssignedJobs('รอรับงาน', this)" class="stat-assign-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-amber-500 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">รอรับงาน</span>
                                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-hand-pointer"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-2xl sm:text-3xl font-black text-amber-600 tracking-tight">{{ $assignedTasks->where('status', 'รอรับงาน')->count() }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 3. กำลังซ่อม -->
                            <div onclick="filterAssignedJobs('กำลังซ่อม', this)" class="stat-assign-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-teal-500 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">กำลังซ่อม</span>
                                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-2xl sm:text-3xl font-black text-teal-600 tracking-tight">{{ $assignedTasks->whereIn('status', ['กำลังดำเนินการ', 'กำลังซ่อม', 'รออนุมัติงบประมาณ'])->count() }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 4. รออะไหล่ -->
                            <div onclick="filterAssignedJobs('รออะไหล่', this)" class="stat-assign-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-purple-500 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">รออะไหล่</span>
                                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-boxes-stacked"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-2xl sm:text-3xl font-black text-purple-600 tracking-tight">{{ $assignedTasks->whereIn('status', ['รออะไหล่', 'รอเปลี่ยนอะไหล่'])->count() }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>
                            <!-- 5. ไม่สามารถซ่อมได้ -->
                            <div onclick="filterAssignedJobs('ไม่สามารถซ่อมได้', this)" class="stat-assign-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-rose-500 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">ซ่อมไม่ได้</span>
                                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-circle-xmark"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight">{{ $assignedTasks->where('status', 'ไม่สามารถซ่อมได้')->count() }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>
                        </div>

                        {{-- ข้อความเมื่อไม่มีงาน --}}
                        @if($assignedTasks->isEmpty())
                            <div class="text-center py-10 text-slate-400 border border-slate-100 rounded-3xl bg-white shadow-xs">
                                <div class="w-14 h-14 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 text-2xl shadow-sm">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                                <h3 class="text-base font-black text-slate-700">ยังไม่มีงานที่ได้รับมอบหมาย</h3>
                                <p class="text-xs text-slate-400 mt-1">เมื่อหัวหน้างานมอบหมายงานซ่อมให้คุณ รายการจะมาปรากฏที่นี่</p>
                            </div>
                        @else
                            <!-- 1. Card View Container -->
                            <div class="space-y-4" id="assigned-jobs-card-view">
                                @foreach($assignedTasks as $job)
                                    @php
                                        $isClosed = in_array($job->status, ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์', 'ไม่สามารถซ่อมได้', 'ยกเลิก']);
                                        $statusIcon = "fa-spinner";
                                        $iconBg = "bg-blue-50 text-blue-600 border-blue-200";
                                        $statusBadge = "bg-blue-50 text-blue-700 border-blue-200";
                                        $cardBorder = "border-l-4 border-l-blue-500";
                                        $statusText = $job->status;
                                        
                                        if ($job->status === 'รอรับงาน') {
                                            $statusIcon = "fa-hand-pointer";
                                            $iconBg = "bg-amber-50 text-amber-600 border-amber-200";
                                            $statusBadge = "bg-amber-50 text-amber-700 border-amber-200";
                                            $cardBorder = "border-l-4 border-l-amber-500";
                                        } elseif ($job->status === 'เสร็จสิ้น' || $job->status === 'ซ่อมเสร็จสมบูรณ์') {
                                            $statusIcon = "fa-circle-check";
                                            $iconBg = "bg-emerald-50 text-emerald-600 border-emerald-200";
                                            $statusBadge = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                            $cardBorder = "border-l-4 border-l-emerald-500";
                                        } elseif ($job->status === 'ไม่สามารถซ่อมได้') {
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
                                        } elseif ($job->status === 'รออะไหล่' || $job->status === 'รอเปลี่ยนอะไหล่') {
                                            $statusIcon = "fa-boxes-stacked";
                                            $iconBg = "bg-purple-50 text-purple-600 border-purple-200";
                                            $statusBadge = "bg-purple-50 text-purple-700 border-purple-200";
                                            $cardBorder = "border-l-4 border-l-purple-500";
                                        } elseif ($job->status === 'ยืนยันการซื้ออะไหล่สำเร็จ') {
                                            $statusIcon = "fa-check-double";
                                            $iconBg = "bg-indigo-50 text-indigo-600 border-indigo-200";
                                            $statusBadge = "bg-indigo-50 text-indigo-700 border-indigo-200";
                                            $cardBorder = "border-l-4 border-l-indigo-500";
                                        } else {
                                            $statusIcon = "fa-screwdriver-wrench";
                                            $iconBg = "bg-teal-50 text-teal-600 border-teal-200";
                                            $statusBadge = "bg-teal-50 text-teal-700 border-teal-200";
                                            $cardBorder = "border-l-4 border-l-teal-500";
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
                                    @endphp

                                    <div class="assigned-job-card bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/70 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col gap-4 relative group overflow-hidden {{ $cardBorder }}" 
                                         data-status="{{ $job->status }}">
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
                                                        @if($job->device && $job->device->device_code)
                                                            <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                                                                รหัส: {{ $job->device->device_code }}
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

                                        <!-- Middle Grid: ผู้แจ้งซ่อม & อาการที่พบ -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-slate-50/70 p-3.5 rounded-2xl border border-slate-100">
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
                                        </div>

                                        <!-- Bottom Row: Actions -->
                                        <div class="flex items-center justify-end gap-2 pt-1">

                                            <button type="button" 
                                                onclick="openHistoryModal({
                                                    code: '{{ $job->request_code }}',
                                                    title: '{{ addslashes($job->device->name ?? "อุปกรณ์ถูกลบ") }}',
                                                    device: '{{ addslashes($job->device->name ?? "-") }} ({{ $job->device?->device_code ?? "-" }})',
                                                    location: '{{ addslashes($displayLoc) }}',
                                                    type: '{{ $job->type }}',
                                                    priority: '{{ $job->priority }}',
                                                    description: '{{ addslashes(str_replace(array("\r", "\n"), " ", $job->clean_symptom)) }}',
                                                    reporter: '{{ addslashes($job->user->name ?? "ไม่ระบุ") }}',
                                                    assignee: '{{ addslashes($job->assignee->name ?? "ยังไม่ระบุ") }}',
                                                    status: '{{ $job->status }}',
                                                    statusClass: '{{ $statusBadge }}',
                                                    created_at: '{{ \Carbon\Carbon::parse($job->created_at)->setTimezone("Asia/Bangkok")->addYears(543)->format("d/m/Y H:i") }} น.',
                                                    finished_at: '{{ $job->updated_at && in_array($job->status, ["เสร็จสิ้น", "ซ่อมเสร็จสมบูรณ์"]) ? \Carbon\Carbon::parse($job->updated_at)->setTimezone("Asia/Bangkok")->addYears(543)->format("d/m/Y H:i") . " น." : "-" }}',
                                                    note: '{{ addslashes(str_replace(array("\r", "\n"), " ", $job->note ?? "-")) }}',
                                                    rating: {{ (int)($job->rating ?? 0) }},
                                                    rating_text: '{{ [1 => "ปรับปรุง", 2 => "พอใช้", 3 => "ปานกลาง", 4 => "ดี", 5 => "ดีมาก"][(int)($job->rating ?? 0)] ?? "-" }}',
                                                    eval_comment: '{{ addslashes(str_replace(array("\r", "\n"), " ", !empty($job->eval_comment) ? $job->eval_comment : (!empty($job->note) && !str_contains($job->note, "บันทึกค่าซ่อม") ? $job->note : ""))) }}',
                                                    image: '{{ $job->image ? asset($job->image) : "" }}'
                                                })"
                                                class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center justify-center gap-1.5 cursor-pointer">
                                                <i class="fa-solid fa-circle-info text-slate-400"></i>
                                                ดูรายละเอียด
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- 2. Table View Container -->
                            <div class="overflow-x-auto w-full max-w-full text-xs border border-slate-200/80 rounded-2xl bg-white shadow-sm hidden" id="assigned-jobs-table-view" style="-webkit-overflow-scrolling: touch;">
                                <table class="w-full text-left border-collapse" id="assigned-jobs-data-table">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-extrabold text-[11px] uppercase tracking-wider">
                                            <th class="px-4 py-3.5">รหัสแจ้งซ่อม</th>
                                            <th class="px-4 py-3.5">ชนิดทรัพย์สิน</th>
                                            <th class="px-4 py-3.5">อาการที่พบ</th>
                                            <th class="px-4 py-3.5">ผู้แจ้งซ่อม</th>
                                            <th class="px-4 py-3.5 text-center">สถานะ</th>
                                            <th class="px-4 py-3.5 text-center">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                        @foreach($assignedTasks as $job)
                                            @php
                                                $isClosed = in_array($job->status, ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์', 'ไม่สามารถซ่อมได้', 'ยกเลิก']);
                                                $statusIcon = "fa-spinner";
                                                $iconBg = "bg-blue-50 text-blue-600 border-blue-200";
                                                $statusBadge = "bg-blue-50 text-blue-700 border-blue-200";
                                                $statusText = $job->status;
                                                
                                                if ($job->status === 'รอรับงาน') {
                                                    $statusIcon = "fa-hand-pointer";
                                                    $iconBg = "bg-amber-50 text-amber-600 border-amber-200";
                                                    $statusBadge = "bg-amber-50 text-amber-700 border-amber-200";
                                                } elseif ($job->status === 'เสร็จสิ้น' || $job->status === 'ซ่อมเสร็จสมบูรณ์') {
                                                    $statusIcon = "fa-circle-check";
                                                    $iconBg = "bg-emerald-50 text-emerald-600 border-emerald-200";
                                                    $statusBadge = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                                } elseif ($job->status === 'ไม่สามารถซ่อมได้') {
                                                    $statusIcon = "fa-circle-xmark";
                                                    $iconBg = "bg-rose-50 text-rose-600 border-rose-200";
                                                    $statusBadge = "bg-rose-50 text-rose-700 border-rose-200";
                                                } elseif (str_starts_with($job->status, 'ยกเลิก')) {
                                                    $statusIcon = "fa-ban";
                                                    $iconBg = "bg-slate-100 text-slate-500 border-slate-200";
                                                    $statusBadge = "bg-slate-100 text-slate-600 border-slate-200";
                                                } elseif ($job->status === 'รออนุมัติงบประมาณ') {
                                                    $statusIcon = "fa-hourglass-half";
                                                    $iconBg = "bg-amber-50 text-amber-600 border-amber-200";
                                                    $statusBadge = "bg-amber-50 text-amber-700 border-amber-200 animate-pulse";
                                                } elseif ($job->status === 'รออะไหล่' || $job->status === 'รอเปลี่ยนอะไหล่') {
                                                    $statusIcon = "fa-boxes-stacked";
                                                    $iconBg = "bg-purple-50 text-purple-600 border-purple-200";
                                                    $statusBadge = "bg-purple-50 text-purple-700 border-purple-200";
                                                } elseif ($job->status === 'ยืนยันการซื้ออะไหล่สำเร็จ') {
                                                    $statusIcon = "fa-check-double";
                                                    $iconBg = "bg-indigo-50 text-indigo-600 border-indigo-200";
                                                    $statusBadge = "bg-indigo-50 text-indigo-700 border-indigo-200";
                                                } else {
                                                    $statusIcon = "fa-screwdriver-wrench";
                                                    $iconBg = "bg-teal-50 text-teal-600 border-teal-200";
                                                    $statusBadge = "bg-teal-50 text-teal-700 border-teal-200";
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
                                            @endphp
                                            <tr class="assigned-job-row hover:bg-slate-50/70 transition-colors" 
                                                data-status="{{ $job->status }}">
                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                    <div class="flex flex-col gap-0.5 font-mono">
                                                        <span class="text-xs font-black text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100 w-fit">
                                                            {{ $job->request_code }}
                                                        </span>
                                                        @if($job->device && $job->device->device_code)
                                                            <span class="text-[10px] font-bold text-slate-400">
                                                                #{{ $job->device->device_code }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm border {{ $iconBg }} shrink-0 shadow-xs">
                                                            <i class="fa-solid {{ getDeviceIcon($job->device->name ?? '') }}"></i>
                                                        </div>
                                                        <div class="font-black text-slate-800 text-xs truncate max-w-[200px]" title="{{ $job->asset_kind ?? 'ไม่ระบุ' }}">
                                                            {{ $job->asset_kind ?? 'ไม่ระบุ' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5 max-w-[280px]">
                                                    <div class="font-semibold text-slate-700 text-xs line-clamp-2" title="{{ $job->clean_symptom }}">
                                                        {{ $job->clean_symptom ?: 'ไม่ระบุอาการ' }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                    <div class="flex items-center gap-1.5 text-slate-700 font-bold text-xs">
                                                        <i class="fa-regular fa-user text-indigo-500 text-[11px]"></i>
                                                        <span>{{ $job->user->name ?? 'ไม่ระบุ' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black border inline-flex items-center gap-1.5 shadow-xs {{ $statusBadge }}">
                                                        <i class="fa-solid {{ $statusIcon }} text-[9px]"></i>
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                                    <button type="button" 
                                                        onclick="openHistoryModal({
                                                            code: '{{ $job->request_code }}',
                                                            title: '{{ addslashes($job->device->name ?? 'อุปกรณ์ถูกลบ') }}',
                                                            device: '{{ addslashes($job->device->name ?? '-') }} ({{ $job->device?->device_code ?? '-' }})',
                                                            location: '{{ addslashes($displayLoc) }}',
                                                            type: '{{ $job->type }}',
                                                            priority: '{{ $job->priority }}',
                                                            description: '{{ addslashes(str_replace(array("\r", "\n"), ' ', $job->clean_symptom)) }}',
                                                            reporter: '{{ addslashes($job->user->name ?? 'ไม่ระบุ') }}',
                                                            assignee: '{{ addslashes($job->assignee->name ?? 'ยังไม่ระบุ') }}',
                                                            status: '{{ $job->status }}',
                                                            statusClass: '{{ $statusBadge }}',
                                                            created_at: '{{ \Carbon\Carbon::parse($job->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') }} น.',
                                                            finished_at: '{{ $job->updated_at && in_array($job->status, ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์']) ? \Carbon\Carbon::parse($job->updated_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}',
                                                            note: '{{ addslashes(str_replace(array("\r", "\n"), ' ', $job->note ?? '-')) }}',
                                                            rating: {{ (int)($job->rating ?? 0) }},
                                                            rating_text: '{{ [1 => "ปรับปรุง", 2 => "พอใช้", 3 => "ปานกลาง", 4 => "ดี", 5 => "ดีมาก"][(int)($job->rating ?? 0)] ?? "-" }}',
                                                            eval_comment: '{{ addslashes(str_replace(array("\r", "\n"), ' ', !empty($job->eval_comment) ? $job->eval_comment : (!empty($job->note) && !str_contains($job->note, 'บันทึกค่าซ่อม') ? $job->note : ''))) }}',
                                                            image: '{{ $job->image ? asset($job->image) : "" }}'
                                                        })"
                                                        class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer mx-auto">
                                                        <i class="fa-solid fa-circle-info text-slate-400"></i>
                                                        ดูรายละเอียด
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Controls (เมื่อมีข้อมูลเพิ่มไม่ต้องเลื่อนลง ให้ทำเป็นหน้าถัดไป) -->
                            <div id="assigned-pagination-container" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100 text-xs mt-3">
                                <div class="text-slate-500 font-semibold" id="assigned-pagination-info"></div>
                                <div class="flex items-center gap-1.5" id="assigned-pagination-buttons"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div id="tab-history" class="tab-content hidden space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
                
                @php
                    $myHistory = $requests->filter(function($j) use ($user) {
                        $techId = $j->assigned_to ?? optional($j->assignee)->user_id ?? optional($j->assignee)->id;
                        return $techId == ($user->user_id ?? $user->id);
                    })->filter(function($j) {
                        return in_array($j->status, ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์', 'ไม่สามารถซ่อมได้', 'ไม่อนุมัติงบ/ซ่อมไม่ได้', 'ยกเลิก']);
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

                    // Classify Category - อิงตามฐานข้อมูลจริง repair_equipments และ asset_kinds
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

                    // Classify Device Kind (ชนิดทรัพย์สิน) - ตรงตามตาราง asset_kinds และ repair_equipments ในระบบจริง
                    $classifyDeviceKind = function($job) {
                        return $job->asset_kind ?? 'ไม่ระบุ';
                    };

                    $classifyDeviceKindKey = function($kindName) {
                        switch($kindName) {
                            case 'โน้ตบุ๊ค': return 'notebook';
                            case 'OS ระบบปฎิบัติการ':
                            case 'OS ระบบปฏิบัติการ': return 'os';
                            case 'OS ซอฟต์แวร์สำนักงาน': return 'office_sw';
                            case 'แป้นพิมพ์': return 'keyboard';
                            case 'เมาส์': return 'mouse';
                            case 'สายแลน': return 'lan';
                            case 'อุปกรณ์กระจายสัญญาณ': return 'access_point';
                            case 'wifi': return 'wifi';
                            default: return 'other';
                        }
                    };

                    $historyDataList = [];
                    $availableYears = [];
                    foreach($myHistory as $job) {
                        $catKey = $classifyCategory($job);
                        $repKey = $classifyReparability($job);
                        $kindName = $classifyDeviceKind($job);
                        $kindKey = $classifyDeviceKindKey($kindName);
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
                            'category_name' => $catKey === 'hardware' ? 'ฮาร์ดแวร์' : ($catKey === 'software' ? 'ซอฟต์แวร์' : 'เครือข่าย'),
                            'device_kind' => $kindName,
                            'device_kind_key' => $kindKey,
                            'reparability' => $repKey,
                            'budget' => (float)($job->budget ?? 0),
                            'device_name' => $job->device->name ?? $job->item->equipment_name ?? $job->type ?? 'อุปกรณ์ไอที',
                            'created_at' => $job->created_at ? $job->created_at->format('Y-m-d H:i:s') : '',
                            'date_ymd' => $dateObj->format('Y-m-d'),
                            'year' => $yearGreg,
                            'thai_year' => $thaiYear,
                            'month' => (int)$dateObj->format('n'),
                            'day' => (int)$dateObj->format('j'),
                            'status' => $job->status,
                            'rating' => (int)($job->rating ?? 0),
                            'eval_comment' => $job->eval_comment ?? (!empty($job->note) && !str_contains($job->note, 'บันทึกค่าซ่อม') ? $job->note : ''),
                            'user_name' => $job->user->name ?? 'ผู้แจ้งซ่อม',
                            'dept_name' => $job->user->department->name ?? optional($job->user->department)->department_name ?? 'แผนกวิชา',
                            'formatted_date' => $job->updated_at ? \Carbon\Carbon::parse($job->updated_at)->addYears(543)->format('d/m/Y H:i') . ' น.' : '',
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

                <script>
                    window.historyJobsData = @json($historyDataList);
                </script>

                <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden flex flex-col">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 px-6 py-5 relative overflow-hidden">
                        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[14px] bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg border border-white/30 shadow-inner">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-white tracking-wide">ดูประวัติงานซ่อมย้อนหลัง</h2>
                                </div>
                            </div>

                            <!-- View Mode Switcher in Header -->
                            <div class="inline-flex p-1 bg-white/20 backdrop-blur-md rounded-2xl border border-white/30 shadow-inner">
                                <button type="button" onclick="setTechnicianHistoryView('chart')" id="btn-view-chart" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-white text-blue-600 shadow-sm cursor-pointer">
                                    <i class="fa-solid fa-chart-pie"></i> ดูแบบกราฟ
                                </button>
                                <button type="button" onclick="setTechnicianHistoryView('table')" id="btn-view-table" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-white hover:bg-white/15 cursor-pointer">
                                    <i class="fa-solid fa-table-list"></i> ดูเป็นตาราง
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 bg-slate-50/30 space-y-6">

                        <!-- 5 Core Repair Statistics Summary Cards Calculation -->
                        @php
                            $histTotal = $myHistory->count();
                            $histRep = $myHistory->filter(fn($j) => str_starts_with($j->status, 'ซ่อมเสร็จ') || $j->status === 'เสร็จสิ้น' || str_starts_with($j->status, 'ซ่อมสำเร็จ'))->count();
                            $histUnrep = $myHistory->filter(fn($j) => $j->status === 'ไม่สามารถซ่อมได้' || $j->status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้' || in_array($j->status, ['รอเปลี่ยนอะไหล่', 'รออะไหล่', 'รออนุมัติงบประมาณ']) || $j->budget > 0 || !empty($j->unrepairable_reason))->count();
                            $histHw = $myHistory->filter(fn($j) => $classifyCategory($j) === 'hardware')->count();
                            $histSw = $myHistory->filter(fn($j) => $classifyCategory($j) === 'software')->count();
                            $histNet = $myHistory->filter(fn($j) => $classifyCategory($j) === 'network_other')->count();

                            // Real Hardware Kinds (โน้ตบุ๊ค, แป้นพิมพ์, เมาส์)
                            $pctRep = $histTotal > 0 ? number_format(($histRep / $histTotal) * 100, 1) : '0';
                            $pctUnrep = $histTotal > 0 ? number_format(($histUnrep / $histTotal) * 100, 1) : '0';
                            $pctHw = $histTotal > 0 ? number_format(($histHw / $histTotal) * 100, 1) : '0';
                            $pctSw = $histTotal > 0 ? number_format(($histSw / $histTotal) * 100, 1) : '0';
                            $pctNet = $histTotal > 0 ? number_format(($histNet / $histTotal) * 100, 1) : '0';

                        @endphp

                        <!-- 6 Core Filter Cards (สถิติและตัวกรองประวัติงานซ่อมย้อนหลัง เหมือนแบบรูปภาพ) -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                            <!-- 1. ทั้งหมด -->
                            <div onclick="setHistoryFilter('all', this)" id="chip-all" class="history-chip stat-history-filter-card active-stat bg-white rounded-2xl p-4 border border-blue-500 shadow-sm flex flex-col justify-between border-l-4 border-l-blue-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="card-title text-[11px] font-black text-blue-700 uppercase tracking-wider">ทั้งหมด</span>
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-list-ul"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="badge-count-all" class="text-2xl sm:text-3xl font-black text-blue-600 tracking-tight">{{ $histTotal }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 2. ฮาร์ดแวร์ -->
                            <div onclick="setHistoryFilter('hardware', this)" id="chip-hardware" class="history-chip stat-history-filter-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-blue-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="card-title text-[11px] font-bold text-slate-700 uppercase tracking-wider">ฮาร์ดแวร์</span>
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-laptop"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="badge-count-hw" class="text-2xl sm:text-3xl font-black text-blue-600 tracking-tight">{{ $histHw }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 3. ซอฟต์แวร์ -->
                            <div onclick="setHistoryFilter('software', this)" id="chip-software" class="history-chip stat-history-filter-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-purple-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="card-title text-[11px] font-bold text-slate-700 uppercase tracking-wider">ซอฟต์แวร์</span>
                                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-compact-disc"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="badge-count-sw" class="text-2xl sm:text-3xl font-black text-purple-600 tracking-tight">{{ $histSw }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 4. เครือข่าย -->
                            <div onclick="setHistoryFilter('network_other', this)" id="chip-network_other" class="history-chip stat-history-filter-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-teal-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="card-title text-[11px] font-bold text-slate-700 uppercase tracking-wider">เครือข่าย</span>
                                    <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-sitemap"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="badge-count-net" class="text-2xl sm:text-3xl font-black text-teal-600 tracking-tight">{{ $histNet }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 5. ซ่อมได้ -->
                            <div onclick="setHistoryFilter('repairable', this)" id="chip-repairable" class="history-chip stat-history-filter-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-emerald-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="card-title text-[11px] font-bold text-slate-700 uppercase tracking-wider">ซ่อมได้</span>
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-circle-check"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="badge-count-repairable" class="text-2xl sm:text-3xl font-black text-emerald-600 tracking-tight">{{ $histRep }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>

                            <!-- 6. ซ่อมไม่ได้ -->
                            <div onclick="setHistoryFilter('unrepairable_parts', this)" id="chip-unrepairable_parts" class="history-chip stat-history-filter-card bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between border-l-4 border-l-rose-600 hover:shadow-md transition-all cursor-pointer">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="card-title text-[11px] font-bold text-slate-700 uppercase tracking-wider">ซ่อมไม่ได้</span>
                                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs shadow-xs"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span id="badge-count-unrepairable" class="text-2xl sm:text-3xl font-black text-rose-600 tracking-tight">{{ $histUnrep }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Compatibility Spans for dynamic JS time filter updates -->
                        <div class="hidden">
                            <span id="stat-total-count">{{ $histTotal }}</span>
                            <span id="stat-hw-count">{{ $histHw }}</span>
                            <span id="stat-hw-pct">{{ $pctHw }}%</span>
                            <span id="stat-sw-count">{{ $histSw }}</span>
                            <span id="stat-sw-pct">{{ $pctSw }}%</span>
                            <span id="stat-net-count">{{ $histNet }}</span>
                            <span id="stat-net-pct">{{ $pctNet }}%</span>
                            <span id="stat-card-total-sub"></span>
                        </div>

                        <!-- 1. ส่วนแสดงผลแบบกราฟสถิติ (Chart View Container) -->
                        <div id="technician-history-chart-view" class="space-y-6">

                        <!-- Visual Analytics Section (Always Visible & Easiest to Read) -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
                            <!-- Header with Period Selector & Info -->
                            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold shadow-sm">
                                        <i class="fa-solid fa-chart-pie"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm">สถิติการซ่อมตามประเภทและชนิดทรัพย์สิน</h4>
                                    </div>
                                </div>

                                <!-- Time Filter Controls (แต่ละวัน เดือน ปี) -->
                                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                                    <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">
                                        <button type="button" onclick="setHistoryTimeMode('all')" id="btn-mode-all" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold">ทั้งหมด</button>
                                        <button type="button" onclick="setHistoryTimeMode('day')" id="btn-mode-day" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-semibold">รายวัน</button>
                                        <button type="button" onclick="setHistoryTimeMode('month')" id="btn-mode-month" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-semibold">รายเดือน</button>
                                        <button type="button" onclick="setHistoryTimeMode('year')" id="btn-mode-year" class="history-mode-btn px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-semibold">รายปี</button>
                                    </div>

                                    <div id="history-date-controls" class="flex items-center gap-2">
                                        <div id="control-day" class="hidden flex items-center gap-1.5">
                                            <div class="relative flex items-center">
                                                <input type="text" id="history-filter-date" value="{{ date('d/m/') . (date('Y') + 543) }}" data-date-ymd="{{ date('Y-m-d') }}" onchange="applyHistoryFilter()" placeholder="วว/ดด/ปปปป" class="w-36 text-xs bg-slate-50 border border-slate-200 rounded-xl pl-3 pr-8 py-1.5 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs">
                                                <span class="absolute right-2.5 pointer-events-none text-slate-400 text-xs">
                                                    <i class="fa-regular fa-calendar-days"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div id="control-month" class="hidden flex items-center gap-1.5">
                                            <select id="history-filter-month" onchange="applyHistoryFilter()"
                                                    class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
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
                                                    class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                                @for($y = $curY + 5; $y >= 2015; $y--)
                                                    <option value="{{ $y }}" {{ $curY === $y ? 'selected' : '' }}>พ.ศ. {{ $y + 543 }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <div id="control-year" class="hidden flex items-center gap-1.5">
                                            <select id="history-filter-year" onchange="applyHistoryFilter()" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                @php $currentYear = (int)date('Y'); @endphp
                                                @for($y = $currentYear + 5; $y >= 2015; $y--)
                                                    <option value="{{ $y }}" {{ $currentYear === $y ? 'selected' : '' }}>พ.ศ. {{ $y + 543 }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <button type="button" onclick="resetHistoryFilter()" title="รีเซ็ตตัวกรอง" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl border border-transparent hover:border-blue-100 transition-colors">
                                        <i class="fa-solid fa-rotate-right text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- 2 Clean & Intuitive Charts (Categories Donut & Asset Kind Bar) -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-stretch">
                                <!-- Chart 1: สัดส่วนประเภททรัพย์สิน (Asset Categories Donut) -->
                                <div class="bg-slate-50/60 rounded-xl p-4 border border-slate-200/70 flex flex-col justify-between">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <h5 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-layer-group text-blue-500"></i>
                                                <span id="donut-chart-title">สัดส่วนตามประเภททรัพย์สิน</span>
                                            </h5>
                                            <p id="donut-chart-sub" class="text-[11px] text-slate-400"></p>
                                        </div>
                                        <div class="flex items-center gap-1 p-0.5 bg-slate-200/70 rounded-lg border border-slate-200">
                                            <button type="button" onclick="setDonutView('category')" id="btn-donut-category" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-blue-600 shadow-xs">ประเภททรัพย์สิน</button>
                                            <button type="button" onclick="setDonutView('outcome')" id="btn-donut-outcome" class="px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800">ผลการซ่อม</button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center my-auto py-2">
                                        <!-- Donut Canvas with Center Percentage -->
                                        <div class="sm:col-span-5 relative h-[145px] flex items-center justify-center">
                                            <canvas id="historyDonutChart"></canvas>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                                <span id="donut-center-pct" class="text-xl font-black text-slate-800">50.0%</span>
                                                <span id="donut-center-sub" class="text-[10px] text-slate-400 font-bold">ฮาร์ดแวร์</span>
                                            </div>
                                        </div>

                                        <!-- Side Legend Cards for Category Mode -->
                                        <div id="donut-legend-category" class="sm:col-span-7 flex flex-col justify-center gap-1.5">
                                            <div onclick="setHistoryFilter('hardware')" class="flex items-center justify-between p-2 rounded-xl bg-white border border-indigo-200 hover:border-indigo-400 cursor-pointer shadow-xs transition-all">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shrink-0"></span>
                                                    <span class="text-xs font-bold text-slate-700">ฮาร์ดแวร์</span>
                                                </div>
                                                <div class="text-right">
                                                    <span id="donut-hw-count" class="text-xs font-black text-indigo-600 block">{{ $histHw }} รายการ</span>
                                                    <span id="donut-hw-pct" class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded">{{ $pctHw }}%</span>
                                                </div>
                                            </div>

                                            <div onclick="setHistoryFilter('software')" class="flex items-center justify-between p-2 rounded-xl bg-white border border-purple-200 hover:border-purple-400 cursor-pointer shadow-xs transition-all">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shrink-0"></span>
                                                    <span class="text-xs font-bold text-slate-700">ซอฟต์แวร์</span>
                                                </div>
                                                <div class="text-right">
                                                    <span id="donut-sw-count" class="text-xs font-black text-purple-600 block">{{ $histSw }} รายการ</span>
                                                    <span id="donut-sw-pct" class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded">{{ $pctSw }}%</span>
                                                </div>
                                            </div>

                                            <div onclick="setHistoryFilter('network_other')" class="flex items-center justify-between p-2 rounded-xl bg-white border border-teal-200 hover:border-teal-400 cursor-pointer shadow-xs transition-all">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-teal-600 shrink-0"></span>
                                                    <span class="text-xs font-bold text-slate-700">เครือข่าย</span>
                                                </div>
                                                <div class="text-right">
                                                    <span id="donut-net-count" class="text-xs font-black text-teal-600 block">0 รายการ</span>
                                                    <span id="donut-net-pct" class="text-[10px] font-bold text-teal-700 bg-teal-50 px-1.5 py-0.2 rounded">0.0%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Side Legend Cards for Outcome Mode (Hidden by default) -->
                                        <div id="donut-legend-outcome" class="hidden sm:col-span-7 flex flex-col justify-center gap-2">
                                            <div onclick="setHistoryFilter('repairable')" class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-emerald-200 hover:border-emerald-400 cursor-pointer shadow-sm transition-all">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                                                    <div>
                                                        <span class="text-xs font-bold text-slate-700 block">สามารถซ่อมได้</span>
                                                        <span class="text-[10px] text-slate-400">ใช้งานได้ปกติ</span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span id="donut-repairable-count" class="text-xs font-black text-emerald-600 block">{{ $histRep }} รายการ</span>
                                                    <span id="donut-repairable-pct" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.2 rounded">{{ $pctRep }}%</span>
                                                </div>
                                            </div>

                                            <div onclick="setHistoryFilter('unrepairable_parts')" class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-rose-200 hover:border-rose-400 cursor-pointer shadow-sm transition-all">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                                                    <div>
                                                        <span class="text-xs font-bold text-slate-700 block">ซ่อมไม่ได้</span>
                                                        <span class="text-[10px] text-slate-400"></span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span id="donut-unrepairable-count" class="text-xs font-black text-rose-600 block">{{ $histUnrep }} รายการ</span>
                                                    <span id="donut-unrepairable-pct" class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded">{{ $pctUnrep }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chart 2: สถิติตามชนิดทรัพย์สิน (Asset Kind Column Bar) -->
                                <div class="bg-slate-50/60 rounded-xl p-4 border border-slate-200/70 flex flex-col justify-between">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                        <div>
                                            <h5 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                                <i class="fa-solid fa-chart-column text-teal-600"></i>
                                                <span id="chart-bar-title">สถิติการซ่อมตามชนิดทรัพย์สิน</span>
                                            </h5>
                                        </div>
                                        <!-- Category Switcher Tabs for Device Kinds -->
                                        <div class="flex items-center gap-1 p-0.5 bg-slate-200/70 rounded-lg border border-slate-200 shrink-0">
                                            <button type="button" onclick="setKindCategoryView('all')" id="btn-kind-cat-all" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-blue-600 shadow-xs">ทั้งหมด</button>
                                            <button type="button" onclick="setKindCategoryView('hardware')" id="btn-kind-cat-hw" class="px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800">💻 ฮาร์ดแวร์</button>
                                            <button type="button" onclick="setKindCategoryView('software')" id="btn-kind-cat-sw" class="px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800">💿 ซอฟต์แวร์</button>
                                            <button type="button" onclick="setKindCategoryView('network')" id="btn-kind-cat-net" class="px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-500 hover:text-slate-800">🌐 เครือข่าย</button>
                                        </div>
                                    </div>

                                    <!-- Column Bar Canvas -->
                                    <div class="relative h-[210px] w-full my-auto">
                                        <canvas id="historyBarChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. ส่วนแสดงการประเมินความพึงพอใจและข้อเสนอแนะของผู้แจ้งซ่อม (Satisfaction Assessment & Requester Feedback) -->
                        @php
                            $ratedHistory = $myHistory->filter(fn($j) => (int)($j->rating ?? 0) > 0);
                            $totalRatedCount = $ratedHistory->count();
                            $avgRating = $totalRatedCount > 0 ? (float)$ratedHistory->avg('rating') : 0.0;
                            $avgFormatted = number_format($avgRating, 1);
                            $avgPercent = $totalRatedCount > 0 ? number_format(($avgRating / 5) * 100, 1) : '0.0';
                            
                            $star5 = $ratedHistory->filter(fn($j) => (int)$j->rating === 5)->count();
                            $star4 = $ratedHistory->filter(fn($j) => (int)$j->rating === 4)->count();
                            $star3 = $ratedHistory->filter(fn($j) => (int)$j->rating === 3)->count();
                            $star2 = $ratedHistory->filter(fn($j) => (int)$j->rating === 2)->count();
                            $star1 = $ratedHistory->filter(fn($j) => (int)$j->rating === 1)->count();

                            $star5Pct = $totalRatedCount > 0 ? round(($star5 / $totalRatedCount) * 100) : 0;
                            $star4Pct = $totalRatedCount > 0 ? round(($star4 / $totalRatedCount) * 100) : 0;
                            $star3Pct = $totalRatedCount > 0 ? round(($star3 / $totalRatedCount) * 100) : 0;
                            $star2Pct = $totalRatedCount > 0 ? round(($star2 / $totalRatedCount) * 100) : 0;
                            $star1Pct = $totalRatedCount > 0 ? round(($star1 / $totalRatedCount) * 100) : 0;

                            $ratingTierLabel = 'ยังไม่มีการประเมิน';
                            $ratingTierColor = 'bg-slate-100 text-slate-600 border-slate-200';
                            if ($avgRating >= 4.5) {
                                $ratingTierLabel = 'ดีมากที่สุด (ยอดเยี่ยม)';
                                $ratingTierColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            } elseif ($avgRating >= 3.5) {
                                $ratingTierLabel = 'ดีมาก';
                                $ratingTierColor = 'bg-teal-50 text-teal-700 border-teal-200';
                            } elseif ($avgRating >= 2.5) {
                                $ratingTierLabel = 'ปานกลาง';
                                $ratingTierColor = 'bg-amber-50 text-amber-700 border-amber-200';
                            } elseif ($avgRating >= 1.5) {
                                $ratingTierLabel = 'พอใช้';
                                $ratingTierColor = 'bg-orange-50 text-orange-700 border-orange-200';
                            } elseif ($avgRating > 0) {
                                $ratingTierLabel = 'ต้องปรับปรุง';
                                $ratingTierColor = 'bg-rose-50 text-rose-700 border-rose-200';
                            }

                            $feedbackHistory = $myHistory->filter(function($j) {
                                return (int)($j->rating ?? 0) > 0;
                            })->sortByDesc('updated_at');
                        @endphp

                        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm space-y-5">
                            <!-- Header -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-amber-400 to-yellow-400 text-white flex items-center justify-center text-lg font-black shadow-md shadow-amber-500/20">
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm sm:text-base flex items-center gap-2">
                                            การประเมินความพึงพอใจและข้อเสนอแนะของผู้แจ้งซ่อม
                                            <span id="sat-rated-count-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ $totalRatedCount }} รายการประเมิน
                                            </span>
                                        </h4>
                                        <p class="text-xs text-slate-400 font-medium">สรุปคะแนนประเมินการบริการงานซ่อมบำรุงและข้อคิดเห็น/ข้อเสนอแนะจากผู้ใช้บริการ</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span id="sat-tier-badge" class="text-xs font-bold px-3 py-1.5 rounded-xl border {{ $ratingTierColor }} flex items-center gap-1.5 shadow-xs">
                                        <i class="fa-solid fa-award"></i> <span>{{ $ratingTierLabel }}</span>
                                    </span>
                                </div>
                            </div>

                            <!-- 2 Columns: Score Overview (Left, 5 cols) & Feedback Cards (Right, 7 cols) -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

                                <!-- Left Column: คะแนนความพึงพอใจเฉลี่ย & สัดส่วนจำนวนดาว (5 Cols) -->
                                <div class="lg:col-span-5 bg-gradient-to-b from-amber-50/40 via-white to-slate-50/50 rounded-2xl p-5 border border-amber-100/80 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-4">
                                            <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                                <i class="fa-solid fa-chart-simple text-amber-500"></i> สรุปผลการประเมินภาพรวม
                                            </h5>
                                            <span class="text-[11px] font-bold text-slate-400">เต็ม 5.0 คะแนน</span>
                                        </div>

                                        <!-- Score Banner -->
                                        <div class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-amber-200/60 shadow-xs mb-4">
                                            <div class="text-center min-w-[80px]">
                                                <div id="sat-avg-score" class="text-4xl font-black text-amber-600 tracking-tight leading-none">
                                                    {{ $avgFormatted }}
                                                </div>
                                                <div class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">คะแนนเฉลี่ย</div>
                                            </div>

                                            <div class="flex-1 border-l border-slate-100 pl-4 space-y-1">
                                                <div id="sat-avg-stars" class="flex items-center gap-1 text-amber-400 text-sm">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= floor($avgRating))
                                                            <i class="fa-solid fa-star"></i>
                                                        @elseif($i - 0.5 <= $avgRating)
                                                            <i class="fa-solid fa-star-half-stroke"></i>
                                                        @else
                                                            <i class="fa-regular fa-star text-slate-200"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <p class="text-xs font-bold text-slate-700">
                                                    ความพึงพอใจเฉลี่ย <span id="sat-avg-pct">{{ $avgPercent }}%</span>
                                                </p>
                                                <p id="sat-rated-text" class="text-[11px] text-slate-400 font-medium">
                                                    ผู้ใช้งานประเมินแล้ว {{ $totalRatedCount }} จาก {{ $histTotal }} งาน ({{ $histTotal > 0 ? round(($totalRatedCount / $histTotal) * 100) : 0 }}%)
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Star Breakdown (5, 4, 3, 2, 1) -->
                                        <div class="space-y-2 text-xs">
                                            <!-- 5 Stars -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 flex items-center gap-1 text-[11px] font-bold text-slate-600 shrink-0">
                                                    <span>5</span> <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                                </div>
                                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div id="sat-bar-5" class="bg-gradient-to-r from-amber-400 to-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $star5Pct }}%"></div>
                                                </div>
                                                <span id="sat-count-5" class="w-12 text-right text-[11px] font-bold text-slate-600 shrink-0">{{ $star5 }} งาน</span>
                                            </div>

                                            <!-- 4 Stars -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 flex items-center gap-1 text-[11px] font-bold text-slate-600 shrink-0">
                                                    <span>4</span> <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                                </div>
                                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div id="sat-bar-4" class="bg-gradient-to-r from-amber-300 to-amber-400 h-full rounded-full transition-all duration-500" style="width: {{ $star4Pct }}%"></div>
                                                </div>
                                                <span id="sat-count-4" class="w-12 text-right text-[11px] font-bold text-slate-600 shrink-0">{{ $star4 }} งาน</span>
                                            </div>

                                            <!-- 3 Stars -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 flex items-center gap-1 text-[11px] font-bold text-slate-600 shrink-0">
                                                    <span>3</span> <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                                </div>
                                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div id="sat-bar-3" class="bg-gradient-to-r from-yellow-300 to-amber-300 h-full rounded-full transition-all duration-500" style="width: {{ $star3Pct }}%"></div>
                                                </div>
                                                <span id="sat-count-3" class="w-12 text-right text-[11px] font-bold text-slate-600 shrink-0">{{ $star3 }} งาน</span>
                                            </div>

                                            <!-- 2 Stars -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 flex items-center gap-1 text-[11px] font-bold text-slate-600 shrink-0">
                                                    <span>2</span> <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                                </div>
                                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div id="sat-bar-2" class="bg-orange-300 h-full rounded-full transition-all duration-500" style="width: {{ $star2Pct }}%"></div>
                                                </div>
                                                <span id="sat-count-2" class="w-12 text-right text-[11px] font-bold text-slate-600 shrink-0">{{ $star2 }} งาน</span>
                                            </div>

                                            <!-- 1 Star -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 flex items-center gap-1 text-[11px] font-bold text-slate-600 shrink-0">
                                                    <span>1</span> <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                                </div>
                                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                    <div id="sat-bar-1" class="bg-rose-400 h-full rounded-full transition-all duration-500" style="width: {{ $star1Pct }}%"></div>
                                                </div>
                                                <span id="sat-count-1" class="w-12 text-right text-[11px] font-bold text-slate-600 shrink-0">{{ $star1 }} งาน</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottom summary note -->
                                    <div class="mt-4 pt-3 border-t border-amber-200/60 flex items-center justify-between text-[11px] text-slate-500">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check text-emerald-500"></i> สรุปผลจากแบบประเมินในระบบ
                                        </span>
                                        <span class="font-bold text-slate-700">อัปเดตเรียลไทม์</span>
                                    </div>
                                </div>

                                <!-- Right Column: ข้อเสนอแนะและความคิดเห็นจากผู้รับบริการ (7 Cols) -->
                                <div class="lg:col-span-7 bg-slate-50/60 rounded-2xl p-5 border border-slate-200/70 flex flex-col justify-between">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3.5 border-b border-slate-200/70 mb-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-blue-100/70 text-blue-600 flex items-center justify-center text-sm shadow-xs">
                                                <i class="fa-solid fa-comments"></i>
                                            </div>
                                            <div>
                                                <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                                    ข้อเสนอแนะและคำติชม
                                                </h5>
                                                <p class="text-[10px] text-slate-400">จากแบบประเมินความพึงพอใจ (ไม่เปิดเผยชื่อผู้ประเมิน)</p>
                                            </div>
                                        </div>

                                        <!-- Filter tabs: ทั้งหมด / เฉพาะที่มีข้อความ -->
                                        @php
                                            $hasCommentCount = $feedbackHistory->filter(fn($x) => !empty(trim($x->eval_comment ?? '')))->count();
                                        @endphp
                                        <div class="flex items-center bg-slate-200/70 p-0.5 rounded-lg text-[11px] self-start sm:self-auto shadow-inner">
                                            <button type="button" id="fb-btn-all" onclick="filterFeedbackTab('all')" 
                                                    class="px-2.5 py-1 rounded-md font-bold transition-all bg-white text-blue-600 shadow-xs cursor-pointer">
                                                ทั้งหมด (<span id="sat-feedback-count">{{ $feedbackHistory->count() }}</span>)
                                            </button>
                                            <button type="button" id="fb-btn-has-text" onclick="filterFeedbackTab('has-text')" 
                                                    class="px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                                                <i class="fa-solid fa-comment-dots text-[10px] mr-1 text-blue-500"></i>มีข้อความ (<span id="sat-has-text-count">{{ $hasCommentCount }}</span>)
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Feed of Comments with custom scrollbar -->
                                    <div id="sat-feedback-list" class="space-y-3 max-h-[380px] overflow-y-auto pr-1.5" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                                        @if($feedbackHistory->isEmpty())
                                            <div class="p-8 text-center text-slate-400 bg-white rounded-xl border border-dashed border-slate-200">
                                                <i class="fa-regular fa-comment-dots text-3xl text-slate-300 mb-2"></i>
                                                <p class="text-xs font-bold text-slate-500">ยังไม่มีการประเมินความพึงพอใจในขณะนี้</p>
                                            </div>
                                        @else
                                            @foreach($feedbackHistory as $fb)
                                                @php
                                                    $rateScore = (int)($fb->rating ?? 0);
                                                    $rateTier = [1 => 'ปรับปรุง', 2 => 'พอใช้', 3 => 'ปานกลาง', 4 => 'ดี', 5 => 'ดีมาก'][$rateScore] ?? '-';
                                                    $rateBg = [
                                                        1 => 'text-red-700 bg-red-50 border-red-200',
                                                        2 => 'text-orange-700 bg-orange-50 border-orange-200',
                                                        3 => 'text-amber-700 bg-amber-50 border-amber-200',
                                                        4 => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                                        5 => 'text-emerald-800 bg-emerald-100 border-emerald-300'
                                                    ][$rateScore] ?? 'text-slate-700 bg-slate-50 border-slate-200';
                                                    $commentText = trim($fb->eval_comment ?? '');
                                                    $hasComment = !empty($commentText);
                                                @endphp
                                                <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-2xs hover:border-blue-300 transition-all space-y-2.5 feedback-item" data-has-comment="{{ $hasComment ? '1' : '0' }}">
                                                    <!-- Top: Rating Stars & Date -->
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <!-- Stars Pill -->
                                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {{ $rateBg }}">
                                                                <div class="flex text-amber-500 text-[10px]">
                                                                    @for($s = 1; $s <= 5; $s++)
                                                                        <i class="fa-solid fa-star {{ $s <= $rateScore ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                                                    @endfor
                                                                </div>
                                                                <span class="text-xs font-black">{{ $rateScore }}.0</span>
                                                                <span class="text-[10px] font-bold text-slate-600">({{ $rateTier }})</span>
                                                            </div>

                                                            @if($hasComment)
                                                                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 flex items-center gap-1">
                                                                    <i class="fa-solid fa-quote-left text-[8px]"></i> มีข้อเสนอแนะ
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <!-- Date -->
                                                        <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1 shrink-0">
                                                            <i class="fa-regular fa-clock text-[10px]"></i>
                                                            <span>{{ $fb->updated_at ? \Carbon\Carbon::parse($fb->updated_at)->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Middle: Comment Bubble or Empty indicator -->
                                                    @if($hasComment)
                                                        <div class="bg-gradient-to-r from-blue-50/60 to-indigo-50/30 rounded-xl p-3 text-xs text-slate-800 leading-relaxed font-normal border border-blue-100/80 flex items-start gap-2.5">
                                                            <div class="w-6 h-6 rounded-lg bg-blue-100/80 text-blue-600 flex items-center justify-center shrink-0 mt-0.5 text-[10px]">
                                                                <i class="fa-solid fa-quote-left"></i>
                                                            </div>
                                                            <p class="text-xs text-slate-700 font-medium leading-relaxed">“{{ $commentText }}”</p>
                                                        </div>
                                                    @else
                                                        <div class="bg-slate-50/80 rounded-xl px-3 py-2 text-xs text-slate-400 italic flex items-center gap-2 border border-slate-100">
                                                            <i class="fa-regular fa-comment text-slate-300 text-xs"></i>
                                                            <span>ผู้รับบริการประเมินความพึงพอใจระดับ {{ $rateScore }}.0 ดาว (ไม่ได้ระบุข้อความเพิ่มเติม)</span>
                                                        </div>
                                                    @endif

                                                    <!-- Bottom: Code, Device, Details Link -->
                                                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span class="font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 text-[10px] shrink-0">
                                                                {{ $fb->request_code }}
                                                            </span>
                                                            <span class="truncate max-w-[180px] text-slate-500 font-medium text-[11px]">
                                                                <i class="fa-solid fa-desktop text-slate-400 text-[10px] mr-1"></i>{{ $fb->device->name ?? $fb->item->equipment_name ?? $fb->type ?? 'อุปกรณ์' }}
                                                            </span>
                                                        </div>
                                                        <button type="button" class="text-[10px] font-bold text-blue-500 hover:text-blue-700 hover:underline flex items-center gap-1 cursor-pointer shrink-0"
                                                                onclick="openHistoryModal({
                                                                    code: '{{ $fb->request_code }}',
                                                                    title: '{{ addslashes($fb->device->name ?? 'อุปกรณ์ถูกลบ') }}',
                                                                    device: '{{ addslashes($fb->device->name ?? '-') }} ({{ $fb->device?->device_code ?? '-' }})',
                                                                    kind: '{{ addslashes($fb->asset_kind ?? 'ไม่ระบุ') }}',
                                                                    location: '{{ addslashes($fb->location ?? '-') }}',
                                                                    type: '{{ $fb->type }}',
                                                                    priority: '{{ $fb->priority }}',
                                                                    description: '{{ addslashes(str_replace(array("\r", "\n"), ' ', $fb->clean_symptom ?? $fb->description ?? '')) }}',
                                                                    reporter: '{{ addslashes($fb->user->name ?? 'ไม่ระบุ') }}',
                                                                    assignee: '{{ addslashes($fb->assignee->name ?? 'ยังไม่ระบุ') }}',
                                                                    status: '{{ $fb->status }}',
                                                                    statusClass: '',
                                                                    created_at: '{{ \Carbon\Carbon::parse($fb->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') }} น.',
                                                                    finished_at: '{{ $fb->updated_at ? \Carbon\Carbon::parse($fb->updated_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}',
                                                                    note: '{{ addslashes(str_replace(array("\r", "\n"), ' ', $fb->note ?? '-')) }}',
                                                                    rating: {{ (int)($fb->rating ?? 0) }},
                                                                    rating_text: '{{ $rateTier }}',
                                                                    eval_comment: '{{ addslashes(str_replace(array("\r", "\n"), ' ', $commentText)) }}',
                                                                    image: '{{ $fb->image ? asset($fb->image) : "" }}'
                                                                })">
                                                            ดูรายละเอียด <i class="fa-solid fa-chevron-right text-[8px]"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- 2. ส่วนแสดงผลแบบตารางรายการ (Table View Container) -->
                        <div id="technician-history-table-view" class="space-y-4">

                        <!-- Live Search Bar & Table Header -->
                        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shadow-xs">
                                    <i class="fa-solid fa-table-list"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-xs sm:text-sm">ตารางประวัติงานซ่อม</h4>
                                </div>
                            </div>

                            <!-- Live Search Box -->
                            <div class="relative w-full sm:w-72 shrink-0">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="history-search-input" onkeyup="applyHistoryFilter()" placeholder="ค้นหารหัส, อุปกรณ์, ผู้แจ้ง..." class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                                <button type="button" onclick="clearHistorySearch()" id="btn-clear-search" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        <!-- รายการประวัติ Table -->
                        <div class="overflow-x-auto w-full max-w-full text-xs border border-slate-200/80 rounded-2xl bg-white shadow-sm" style="-webkit-overflow-scrolling: touch;">
                            <table class="w-full text-left border-collapse" id="history-data-table">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[11px] tracking-wider whitespace-nowrap">
                                        <th class="px-4 py-3.5">รหัสแจ้งซ่อม</th>
                                        <th class="px-4 py-3.5">ผู้แจ้งซ่อม</th>
                                        <th class="px-4 py-3.5">ชนิดทรัพย์สิน</th>
                                        <th class="px-4 py-3.5">อาการเสีย</th>
                                        <th class="px-4 py-3.5">สถานที่ซ่อม</th>
                                        <th class="px-4 py-3.5 whitespace-nowrap">วันที่แจ้ง</th>
                                        <th class="px-4 py-3.5 whitespace-nowrap">วันที่แล้วเสร็จ</th>
                                        <th class="px-4 py-3.5 text-center">งบประมาณ</th>
                                        <th class="px-4 py-3.5 text-center">สถานะ</th>
                                        <th class="px-4 py-3.5 text-center">ความพึงพอใจ</th>
                                        <th class="px-4 py-3.5">ข้อเสนอแนะของผู้แจ้ง</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-[11px]">
                                    @if($myHistory->isEmpty())
                                        <tr>
                                            <td colspan="11" class="p-10 text-center text-slate-400">
                                                <div class="text-4xl text-slate-300 mb-3"><i class="fa-solid fa-history"></i></div>
                                                <div class="font-bold text-sm">ไม่มีประวัติการส่งแจ้งซ่อมของคุณในขณะนี้</div>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($myHistory as $job)
                                            @php
                                                $catKey = $classifyCategory($job);
                                                $repKey = $classifyReparability($job);
                                                $kindName = $classifyDeviceKind($job);
                                                $kindKey = $classifyDeviceKindKey($kindName);
                                                $dateObj = \Carbon\Carbon::parse($job->created_at);

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

                                                $feedbackComment = !empty($job->eval_comment) ? $job->eval_comment : (!empty($job->note) && !str_contains($job->note, 'บันทึกค่าซ่อม') ? $job->note : '');

                                                $searchKeywords = mb_strtolower(
                                                    $job->request_code . ' ' . 
                                                    ($job->user->name ?? '') . ' ' .
                                                    ($job->device->name ?? '') . ' ' . 
                                                    ($job->device?->device_code ?? '') . ' ' . 
                                                    ($job->device?->category ?? '') . ' ' . 
                                                    ($job->device?->deviceType?->name ?? $job->type ?? '') . ' ' . 
                                                    ($job->clean_symptom ?? $job->description ?? '') . ' ' . 
                                                    $displayLoc . ' ' .
                                                    ($job->repair_details ?? '') . ' ' . 
                                                    ($job->unrepairable_reason ?? '') . ' ' . 
                                                    ($job->status ?? '') . ' ' .
                                                    $feedbackComment . ' ' .
                                                    $kindName . ' ' . $kindKey . ' ' .
                                                    ($catKey === 'hardware' ? 'ฮาร์ดแวร์ hardware' : ($catKey === 'software' ? 'ซอฟต์แวร์ software' : 'เครือข่าย network')) . ' ' .
                                                    ($repKey === 'repairable' ? 'สามารถซ่อมได้ ซ่อมเสร็จ' : ($repKey === 'unrepairable_parts' ? 'ซ่อมไม่ได้ ต้องสั่งอะไหล่ อะไหล่' : 'ยกเลิก')),
                                                    'UTF-8'
                                                );
                                            @endphp
                                            <tr class="hover:bg-slate-50/70 transition history-row cursor-pointer"
                                                data-category="{{ $catKey }}"
                                                data-reparability="{{ $repKey }}"
                                                data-kind="{{ $kindKey }}"
                                                data-date="{{ $dateObj->format('Y-m-d') }}"
                                                data-year="{{ $dateObj->format('Y') }}"
                                                data-month="{{ (int)$dateObj->format('n') }}"
                                                data-day="{{ (int)$dateObj->format('j') }}"
                                                data-keyword="{{ $searchKeywords }}"
                                                onclick="openHistoryModal({
                                                code: '{{ $job->request_code }}',
                                                title: '{{ addslashes($job->device->name ?? "อุปกรณ์ถูกลบ") }}',
                                                device: '{{ addslashes($job->device->name ?? "-") }} ({{ $job->device?->device_code ?? "-" }})',
                                                kind: '{{ addslashes($job->asset_kind ?? "ไม่ระบุ") }}',
                                                location: '{{ addslashes($displayLoc) }}',
                                                type: '{{ $job->type }}',
                                                priority: '{{ $job->priority }}',
                                                description: '{{ addslashes(str_replace(array("\r", "\n"), " ", $job->clean_symptom)) }}',
                                                reporter: '{{ addslashes($job->user->name ?? "ไม่ระบุ") }}',
                                                assignee: '{{ addslashes($job->assignee->name ?? "ยังไม่ระบุ") }}',
                                                status: '{{ $job->status }}',
                                                statusClass: '',
                                                created_at: '{{ \Carbon\Carbon::parse($job->created_at)->setTimezone("Asia/Bangkok")->addYears(543)->format("d/m/Y H:i") }} น.',
                                                finished_at: '{{ $job->updated_at && $job->status == "เสร็จสิ้น" ? \Carbon\Carbon::parse($job->updated_at)->setTimezone("Asia/Bangkok")->addYears(543)->format("d/m/Y H:i") . " น." : "-" }}',
                                                note: '{{ addslashes(str_replace(array("\r", "\n"), " ", $job->note ?? "-")) }}',
                                                rating: {{ (int)($job->rating ?? 0) }},
                                                rating_text: '{{ [1 => "ปรับปรุง", 2 => "พอใช้", 3 => "ปานกลาง", 4 => "ดี", 5 => "ดีมาก"][(int)($job->rating ?? 0)] ?? "-" }}',
                                                eval_comment: '{{ addslashes(str_replace(array("\r", "\n"), " ", $feedbackComment)) }}',
                                                image: '{{ $job->image ? asset($job->image) : "" }}'
                                            })">
                                                <td class="px-4 py-3.5 font-bold text-blue-600 whitespace-nowrap">{{ $job->request_code }}</td>
                                                
                                                <td class="px-4 py-3.5 text-slate-700 font-medium whitespace-nowrap">{{ $job->user->name ?? 'ไม่ระบุผู้แจ้ง' }}</td>
                                                
                                                <td class="px-4 py-3.5">
                                                    <div class="font-bold text-slate-800">{{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}</div>
                                                    <div class="flex items-center gap-1 mt-1 flex-wrap">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $catKey === 'hardware' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : ($catKey === 'software' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-teal-50 text-teal-700 border border-teal-100') }}">
                                                            {{ $catKey === 'hardware' ? 'ฮาร์ดแวร์' : ($catKey === 'software' ? 'ซอฟต์แวร์' : 'เครือข่าย') }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-600 font-semibold bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/70">
                                                            {{ $kindName }}
                                                        </span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $job->device?->device_code ?? '-' }}</div>
                                                </td>
                                                
                                                <td class="px-4 py-3.5 text-slate-500 max-w-[140px] truncate" title="{{ $job->clean_symptom }}">{{ Str::limit($job->clean_symptom, 35) }}</td>
                                                
                                                <td class="px-4 py-3.5 text-slate-650 max-w-[120px] truncate" title="{{ $displayLoc }}">{{ $displayLoc }}</td>
                                                
                                                <!-- วันที่แจ้ง -->
                                                <td class="px-4 py-3.5 whitespace-nowrap text-slate-600">
                                                    <div class="flex items-center gap-1.5 text-slate-500">
                                                        <i class="fa-regular fa-calendar-plus text-slate-400"></i>
                                                        <span>{{ $job->created_at ? \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}</span>
                                                    </div>
                                                </td>

                                                <!-- วันที่แล้วเสร็จ -->
                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                    @if($job->updated_at && ($job->status === 'เสร็จสิ้น' || str_starts_with($job->status, 'ซ่อมเสร็จ') || str_starts_with($job->status, 'ซ่อมสำเร็จ')))
                                                        <div class="flex items-center gap-1.5 text-emerald-600 font-semibold">
                                                            <i class="fa-solid fa-check"></i>
                                                            <span>{{ \Carbon\Carbon::parse($job->updated_at)->addYears(543)->format('d/m/Y H:i') }} น.</span>
                                                        </div>
                                                    @elseif($job->status === 'ไม่สามารถซ่อมได้' || $job->status === 'ไม่อนุมัติงบ/ซ่อมไม่ได้')
                                                        <div class="flex items-center gap-1.5 text-rose-500 font-semibold">
                                                            <i class="fa-solid fa-xmark"></i>
                                                            <span>{{ $job->updated_at ? \Carbon\Carbon::parse($job->updated_at)->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}</span>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center gap-1.5 text-slate-400">
                                                            <span>{{ $job->updated_at ? \Carbon\Carbon::parse($job->updated_at)->addYears(543)->format('d/m/Y H:i') . ' น.' : '-' }}</span>
                                                        </div>
                                                    @endif
                                                </td>
                                                
                                                <td class="px-4 py-3.5 text-center font-bold text-blue-650 whitespace-nowrap">฿{{ number_format($job->budget, 0) }}</td>
                                                
                                                <td class="px-4 py-3.5 text-center font-bold whitespace-nowrap">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] {{ $job->status === 'เสร็จสิ้น' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : ($job->status === 'กำลังดำเนินการ' ? 'bg-blue-50 text-blue-600 border border-blue-100' : ($job->status === 'ไม่สามารถซ่อมได้' ? 'bg-red-50 text-red-650 border border-red-100' : 'bg-amber-50 text-amber-600 border border-amber-100')) }}">
                                                        {{ $job->status }}
                                                    </span>
                                                </td>
                                                
                                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                                    @if($job->rating > 0)
                                                        @php
                                                            $rText = [1 => 'ปรับปรุง', 2 => 'พอใช้', 3 => 'ปานกลาง', 4 => 'ดี', 5 => 'ดีมาก'][$job->rating] ?? '-';
                                                            $rColor = [1 => 'text-red-700 bg-red-50 border-red-200', 2 => 'text-orange-700 bg-orange-50 border-orange-200', 3 => 'text-amber-700 bg-amber-50 border-amber-200', 4 => 'text-emerald-700 bg-emerald-50 border-emerald-200', 5 => 'text-emerald-800 bg-emerald-100 border-emerald-300'][$job->rating] ?? 'text-slate-600 bg-slate-50 border-slate-200';
                                                        @endphp
                                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $rColor }} inline-flex items-center gap-1 shadow-xs">
                                                            <i class="fa-solid fa-star text-amber-500 text-[9px]"></i>
                                                            {{ number_format($job->rating, 1) }} ({{ $rText }})
                                                        </span>
                                                    @else
                                                        <span class="text-slate-300 text-[10px]">-</span>
                                                    @endif
                                                </td>

                                                <td class="px-4 py-3.5 text-slate-600 max-w-[220px]">
                                                    @if(!empty($feedbackComment) && $feedbackComment !== '-')
                                                        <div class="flex items-start gap-1.5" title="{{ $feedbackComment }}">
                                                            <i class="fa-solid fa-quote-left text-amber-400 text-[10px] shrink-0 mt-0.5"></i>
                                                            <span class="truncate text-[11px] font-normal text-slate-700 italic">"{{ Str::limit($feedbackComment, 42) }}"</span>
                                                        </div>
                                                    @else
                                                        <span class="text-slate-300 text-[10px] italic">- ไม่มีข้อเสนอแนะ -</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr id="history-empty-row" class="hidden">
                                            <td colspan="11" class="p-10 text-center text-slate-400">
                                                <div class="text-4xl text-slate-300 mb-3"><i class="fa-solid fa-filter-circle-xmark"></i></div>
                                                <div class="font-bold text-sm">ไม่พบข้อมูลประวัติการซ่อมตามเงื่อนไขที่เลือก</div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        </div><!-- End #technician-history-table-view -->
                    </div>
                </div>
            </div>

            <!-- Tab: บันทึกค่าซ่อม & การเบิกจ่ายงบประมาณ (Requisition & Budget Tracking) -->
            @php
                if (isset($technicianRequisitions)) {
                    $requisitionList = $technicianRequisitions;
                } else {
                    $requisitionList = $requests->filter(function($r) {
                        $isReq = ($r->type ?? '') === 'เบิกงบประมาณ' || !empty($r->repair_project_id) || (float)($r->budget ?? 0) > 0;
                        if (!$isReq) return false;
                        $note = trim($r->note ?? '');
                        if (str_contains($note, 'หน้าแจ้งซ่อม')) return false;

                        $hasTech = !empty($r->assigned_to) || !empty($r->assignee) || !empty($r->latestMaintenance);
                        $hasCostNote = str_contains($note, 'บันทึกค่าซ่อมแล้ว') || str_contains($note, 'เจ้าหน้าที่');
                        $role = mb_strtolower($r->user->role ?? '', 'UTF-8');
                        $isTechUser = str_contains($role, 'technician') || str_contains($role, 'ช่าง');

                        return $hasTech || $hasCostNote || $isTechUser;
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
        </main>
    </div>

    <!-- History Detail Modal -->
    <div id="history-modal" onclick="closeHistoryModal(event)" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div id="history-modal-box" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4 flex justify-between items-center text-white">
                <div>
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-widest">รายละเอียดการแจ้งซ่อม</p>
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
                        <p id="hm-device" class="text-xs text-slate-500 font-semibold mt-0.5"><i id="hm-device-icon" class="fa-solid fa-desktop mr-1 text-slate-400"></i><span id="hm-device-name"></span></p>
                    </div>
                    <span id="hm-status" class="text-xs font-extrabold px-3 py-1 rounded-full border whitespace-nowrap flex-shrink-0"></span>
                </div>

                <hr class="border-slate-100">

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-user text-indigo-500 mr-1"></i>ผู้แจ้งซ่อม</p>
                        <p id="hm-reporter" class="text-sm font-bold text-slate-700"></p>
                    </div>
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
                    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 col-span-2">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-user-wrench text-slate-400 mr-1"></i>ช่างผู้ซ่อม</p>
                        <p id="hm-assignee" class="text-sm font-bold text-slate-700"></p>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100">
                    <p class="text-[10px] font-extrabold text-amber-500 uppercase tracking-wider mb-2"><i class="fa-solid fa-note-sticky mr-1"></i>อาการชำรุดที่แจ้ง</p>
                    <p id="hm-description" class="text-sm text-slate-700 font-medium leading-relaxed"></p>
                </div>

                <!-- Attached Image in History Modal -->
                <div id="hm-image-container" class="bg-slate-50 rounded-2xl p-4 border border-slate-200 hidden">
                    <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-image text-blue-500"></i>รูปภาพประกอบที่แนบ
                    </p>
                    <div class="relative rounded-xl overflow-hidden bg-slate-200/80 border border-slate-200 max-h-64 flex items-center justify-center">
                        <a id="hm-image-link" href="#" target="_blank" class="block w-full h-full text-center group cursor-pointer">
                            <img id="hm-image-preview" src="" alt="รูปภาพประกอบ" class="w-full max-h-64 object-contain rounded-xl transition group-hover:opacity-95">
                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5 pointer-events-none">
                                <i class="fa-solid fa-up-right-and-down-left-from-center"></i> คลิกเพื่อดูภาพขนาดเต็ม
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Rating Box -->
                <div id="hm-rating-box" class="bg-amber-50/80 rounded-2xl p-4 border border-amber-200/80 space-y-2 hidden">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-extrabold text-amber-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-star text-amber-500"></i> ผลการประเมินความพึงพอใจ
                        </p>
                        <span id="hm-rating-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div id="hm-stars" class="flex gap-1 text-base text-amber-400"></div>
                        <span id="hm-rating-score" class="text-xs font-black text-amber-900"></span>
                    </div>
                </div>

                <!-- Requester Feedback Box -->
                <div id="hm-feedback-box" class="bg-blue-50/70 rounded-2xl p-4 border border-blue-100 space-y-1.5 hidden">
                    <p class="text-[10px] font-extrabold text-blue-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-regular fa-comment-dots text-blue-500"></i> ข้อเสนอแนะจากผู้แจ้งซ่อม
                    </p>
                    <p id="hm-feedback" class="text-xs text-slate-700 font-medium italic leading-relaxed"></p>
                </div>

                <!-- Note -->
                <div id="hm-note-box" class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2"><i class="fa-solid fa-comment-dots mr-1"></i>หมายเหตุ / บันทึกปิดใบงาน</p>
                    <p id="hm-note" class="text-sm text-slate-600 font-medium"></p>
                </div>

                <!-- Timestamps -->
                <div class="flex gap-3">
                    <div class="flex-1 bg-slate-50 rounded-xl px-3 py-2 border border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase"><i class="fa-regular fa-calendar mr-1"></i>วันที่แจ้ง</p>
                        <p id="hm-created" class="text-xs font-black text-slate-700 mt-0.5"></p>
                    </div>
                    <div class="flex-1 bg-slate-50 rounded-xl px-3 py-2 border border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase"><i class="fa-solid fa-flag-checkered mr-1"></i>เสร็จเมื่อ</p>
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

    <!-- Update Repair Status Modal (Clean, Step-by-Step Inspection & Evaluation) -->
    <div id="update-modal" onclick="closeUpdateModal(event)" style="display: none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-y-auto">
        <div id="update-modal-box" onclick="event.stopPropagation()" class="bg-white max-w-lg w-full rounded-3xl border border-slate-100 shadow-2xl overflow-hidden text-sm font-semibold relative animate-in fade-in zoom-in-95 duration-200 my-auto">
            
            <!-- Clean Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-xs">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black text-slate-800 font-prompt">บันทึกการตรวจเช็คและการซ่อม</h3>
                            <span id="update-modal-code" class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100"></span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeUpdateModal()" class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer" title="ปิด">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body -->
            <form id="update-repair-form" action="" method="POST" class="p-6 space-y-4" novalidate onsubmit="return handleSimpleUpdateSubmit(event, this);">
                @csrf
                @method('PUT')

                <!-- Hidden inputs for backend compatibility -->
                <input type="hidden" name="status" id="update-status-input" value="เสร็จสิ้น">
                <input type="hidden" name="repair_evaluation" id="update-eval-input" value="สามารถซ่อมได้">
                <input type="hidden" name="note" id="update-note-input" value="">

                <!-- Summary Info Card (Clean & Compact) -->
                <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="fa-solid fa-desktop text-blue-600 text-xs shrink-0"></i>
                            <span id="update-modal-device" class="font-black text-slate-800 text-xs truncate"></span>
                        </div>
                        <span id="update-modal-location" class="text-slate-400 font-medium text-[11px] shrink-0"></span>
                    </div>
                    <div class="text-xs text-slate-600 flex items-start gap-1.5 pt-1 border-t border-slate-200/50">
                        <span class="text-slate-400 shrink-0 font-medium">อาการเสีย:</span>
                        <span id="update-modal-description" class="font-bold text-blue-700 truncate"></span>
                    </div>
                    <div id="update-modal-image-container" class="hidden pt-1 border-t border-slate-200/50">
                        <a id="update-modal-image-link" href="#" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 font-bold">
                            <i class="fa-solid fa-image"></i> ดูรูปภาพประกอบที่แนบมา
                        </a>
                    </div>
                </div>

                <!-- Step 1: ผลการตรวจเช็ค (Inspection) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="inspection_result" class="text-xs font-extrabold text-slate-700 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                            <span>ผลการตรวจสอบ</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">ระบุสิ่งที่ช่างตรวจพบ</span>
                    </div>
                    <textarea id="inspection_result" name="inspection_result" rows="2"
                        class="w-full px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-medium text-xs text-slate-800 transition resize-none placeholder:text-slate-400"
                        placeholder="เช่น เปิดเครื่องไม่ติด, สายไฟหลวม, หน้าจอไม่ขึ้นภาพ, อุปกรณ์ชำรุด..."></textarea>
                </div>

                <!-- Step 2: ประเมินการซ่อม (Evaluation: Can / Cannot Repair) -->
                <div class="space-y-2 pt-1 border-t border-slate-100">
                    <label class="block text-xs font-extrabold text-slate-700 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                        <span>ประเมินผลการซ่อม</span>
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Option A: สามารถซ่อมได้ -->
                        <button type="button" onclick="setEvaluation('can_repair')" id="eval-btn-can"
                            class="eval-choice-btn p-3 rounded-2xl border-2 border-emerald-500 bg-emerald-50/80 text-emerald-800 shadow-xs flex items-center gap-3 transition-all cursor-pointer text-left ring-2 ring-emerald-400/20">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-base">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-xs">สามารถซ่อมได้</div>
                                <div class="text-[10px] text-emerald-600 font-normal truncate">ซ่อมได้ทันที / รออะไหล่</div>
                            </div>
                        </button>

                        <!-- Option B: ไม่สามารถซ่อมได้ -->
                        <button type="button" onclick="setEvaluation('cannot_repair')" id="eval-btn-cannot"
                            class="eval-choice-btn p-3 rounded-2xl border-2 border-slate-200 bg-white text-slate-600 hover:border-slate-300 flex items-center gap-3 transition-all cursor-pointer text-left">
                            <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 text-base">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-xs">ไม่สามารถซ่อมได้</div>
                                <div class="text-[10px] text-slate-400 font-normal truncate">ชำรุดหนัก / ไม่คุ้มซ่อม</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 3A: เมื่อสามารถซ่อมได้ (เลือก: ซ่อมเสร็จ หรือ รออะไหล่ + รายละเอียดการซ่อม) -->
                <div id="section-can-repair" class="space-y-3 pt-1 border-t border-slate-100">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-extrabold text-slate-700">
                            สถานะการดำเนินงาน <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <button type="button" onclick="setActionStatus('completed')" id="action-btn-completed"
                                class="action-choice-btn p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50 text-emerald-800 flex items-center justify-center gap-2 text-xs font-bold transition cursor-pointer ring-2 ring-emerald-400/20">
                                <i class="fa-solid fa-check"></i>
                                <span>ซ่อมเสร็จแล้ว (ปิดงาน)</span>
                            </button>
                            <button type="button" onclick="setActionStatus('wait_parts')" id="action-btn-wait_parts"
                                class="action-choice-btn p-2.5 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:border-slate-300 flex items-center justify-center gap-2 text-xs font-bold transition cursor-pointer">
                                <i class="fa-solid fa-boxes-stacked text-amber-500"></i>
                                <span>รอเปลี่ยนอะไหล่</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="repair_details" class="block text-xs font-extrabold text-slate-700">
                            <span id="repair-details-label">รายละเอียดการซ่อม (ถ้ามี)</span>
                        </label>
                        <textarea id="repair_details" name="repair_details" rows="2"
                            class="w-full px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 font-medium text-xs text-slate-800 transition resize-none placeholder:text-slate-400"
                            placeholder="ระบุสิ่งที่ได้ดำเนินการซ่อมแซม หรืออะไหล่ที่ต้องใช้..."></textarea>
                    </div>
                </div>

                <!-- Step 3B: เมื่อไม่สามารถซ่อมได้ (ช่องระบุสาเหตุ) -->
                <div id="section-cannot-repair" class="hidden space-y-3 pt-1 border-t border-slate-100 animate-in fade-in duration-200">
                    <div class="space-y-1.5">
                        <label for="unrepairable_reason" class="block text-xs font-extrabold text-slate-700">
                            สาเหตุที่ไม่สามารถซ่อมได้ <span class="text-red-500">*</span>
                        </label>
                        <textarea id="unrepairable_reason" name="unrepairable_reason" rows="2"
                            class="w-full px-3.5 py-2 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 font-medium text-xs text-slate-800 transition resize-none placeholder:text-slate-400"
                            placeholder="ระบุสาเหตุ เช่น วงจรหลักไหม้เสียหายหนัก ไม่คุ้มค่าซ่อม, อะไหล่เลิกผลิต..."></textarea>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeUpdateModal()"
                        class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        ยกเลิก
                    </button>
                    <button type="submit" id="btn-submit-repair"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>บันทึก</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Finish Repair Modal (บันทึกการซ่อมแซมและปิดงาน) -->
    <div id="finish-repair-modal" onclick="closeFinishRepairModal(event)" style="display: none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 overflow-y-auto">
        <div id="finish-repair-modal-box" onclick="event.stopPropagation()" class="bg-white max-w-md w-full rounded-3xl border border-slate-100 shadow-2xl overflow-hidden text-sm font-semibold relative animate-in fade-in zoom-in-95 duration-200 my-auto">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-lg shadow-2xs">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 font-prompt">บันทึกการซ่อมแซมและปิดงาน</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span id="finish-modal-code" class="font-mono text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100"></span>
                            <span id="finish-modal-device" class="text-xs text-slate-500 font-medium truncate max-w-[180px]"></span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeFinishRepairModal()" class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer" title="ปิด">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body -->
            <form id="finish-repair-active-form" onsubmit="return handleFinishRepairSubmit(event);" class="p-6 space-y-4">
                <input type="hidden" id="finish-modal-job-id" value="">

                <!-- Quick suggestion chips -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500 mr-1"></i>ข้อความด่วนที่ใช้บ่อย
                    </label>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="insertFinishQuickText('เปลี่ยนอุปกรณ์/อะไหล่เรียบร้อยแล้ว')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 text-slate-600 text-xs rounded-lg border border-slate-200 transition font-medium cursor-pointer">
                            + เปลี่ยนอุปกรณ์/อะไหล่
                        </button>
                        <button type="button" onclick="insertFinishQuickText('ทำความสะอาด ตรวจเช็ค และทดสอบการใช้งานแล้วปกติ')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 text-slate-600 text-xs rounded-lg border border-slate-200 transition font-medium cursor-pointer">
                            + ตรวจเช็คและทดสอบปกติ
                        </button>
                        <button type="button" onclick="insertFinishQuickText('แก้ไขการตั้งค่าและอัปเดตระบบเรียบร้อยแล้ว')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 text-slate-600 text-xs rounded-lg border border-slate-200 transition font-medium cursor-pointer">
                            + แก้ไขระบบ/ตั้งค่าแล้ว
                        </button>
                    </div>
                </div>

                <!-- Textarea input -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="finish-repair-note-input" class="text-xs font-extrabold text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-clipboard-check text-emerald-600"></i>
                            <span>ระบุรายละเอียดหรือข้อความบันทึกการซ่อม</span>
                            <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <textarea id="finish-repair-note-input" rows="3"
                        class="w-full px-3.5 py-2.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-2xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-xs text-slate-800 transition resize-none placeholder:text-slate-400"
                        placeholder="กรอกรายละเอียดการทำงาน เช่น เปลี่ยนพาวเวอร์ซัพพลายใหม่ ทดสอบเปิดเครื่องใช้งานได้ตามปกติ..."></textarea>
                    <div id="finish-repair-warning" class="hidden p-2.5 rounded-xl bg-red-50 border border-red-200 text-xs font-bold text-red-600 flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-red-500 text-sm shrink-0"></i>
                        <span>กรุณาระบุรายละเอียดหรือข้อความบันทึกการซ่อม</span>
                    </div>
                </div>

                <!-- Info note -->
                <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 text-[11px] text-emerald-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-emerald-600 shrink-0"></i>
                    <span>เมื่อกดบันทึก ระบบจะปรับสถานะใบงานเป็น <b>"เสร็จสิ้น"</b> และบันทึกประวัติการซ่อมโดยอัตโนมัติ</span>
                </div>

                <!-- Footer buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeFinishRepairModal()"
                        class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        ยกเลิก
                    </button>
                    <button type="submit" id="btn-confirm-finish-repair"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>บันทึกและปิดงาน</span>
                    </button>
                </div>
            </form>
        </div>
    </div>



    @include('dashboards.technician.modals')
    @include('dashboards.technician.scripts')

    <script>
    function applyHighlight(element, keyword) {
        const marks = element.querySelectorAll('mark.search-highlight');
        marks.forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });

        if (!keyword || !keyword.trim()) return;

        const escapedKeyword = keyword.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const regex = new RegExp("(" + escapedKeyword + ")", "gi");
        
        const treeWalker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, {
            acceptNode: function(node) {
                if (node.parentNode.nodeName === 'MARK' || 
                    node.parentNode.nodeName === 'SCRIPT' || 
                    node.parentNode.nodeName === 'STYLE') {
                    return NodeFilter.FILTER_REJECT;
                }
                return regex.test(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });

        const nodesToReplace = [];
        while (treeWalker.nextNode()) {
            nodesToReplace.push(treeWalker.currentNode);
        }

        nodesToReplace.forEach(node => {
            const span = document.createElement('span');
            span.innerHTML = node.nodeValue.replace(regex, '<mark class="search-highlight bg-yellow-300 text-slate-900 rounded-sm">$1</mark>');
            while (span.firstChild) {
                node.parentNode.insertBefore(span.firstChild, node);
            }
            node.parentNode.removeChild(node);
        });
    }

    function setAssignedViewMode(mode) {
        const cardContainer = document.getElementById('assigned-jobs-card-view');
        const tableContainer = document.getElementById('assigned-jobs-table-view');
        const btnCard = document.getElementById('btn-view-card');
        const btnTable = document.getElementById('btn-view-table');

        if (!cardContainer || !tableContainer) return;

        if (mode === 'table') {
            cardContainer.classList.add('hidden');
            tableContainer.classList.remove('hidden');

            if (btnCard && btnTable) {
                btnCard.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-white/80 hover:text-white hover:bg-white/10 cursor-pointer';
                btnTable.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-white text-blue-700 shadow-sm cursor-pointer';
            }
            localStorage.setItem('technician_assigned_view', 'table');
        } else {
            tableContainer.classList.add('hidden');
            cardContainer.classList.remove('hidden');

            if (btnCard && btnTable) {
                btnCard.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-white text-blue-700 shadow-sm cursor-pointer';
                btnTable.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-white/80 hover:text-white hover:bg-white/10 cursor-pointer';
            }
            localStorage.setItem('technician_assigned_view', 'card');
        }
    }

    let currentAssignedStatusFilter = 'งานทั้งหมด';
    let currentAssignedSearchQuery = '';
    let assignedCurrentPage = 1;
    const assignedPageSize = 5;

    function getMatchingAssignedIndices() {
        const rows = document.querySelectorAll('.assigned-job-row');
        const cards = document.querySelectorAll('.assigned-job-card');
        const count = Math.max(rows.length, cards.length);
        const matching = [];

        for (let idx = 0; idx < count; idx++) {
            const item = rows[idx] || cards[idx];
            if (!item) continue;

            const rowStatus = item.getAttribute('data-status') || '';
            let statusMatch = false;
            if (currentAssignedStatusFilter === 'งานทั้งหมด' || currentAssignedStatusFilter === 'ทั้งหมด') {
                statusMatch = true;
            } else if (currentAssignedStatusFilter === 'กำลังดำเนินการ' || currentAssignedStatusFilter === 'กำลังซ่อม') {
                statusMatch = ['กำลังดำเนินการ', 'กำลังซ่อม', 'รออนุมัติงบประมาณ'].includes(rowStatus);
            } else if (currentAssignedStatusFilter === 'รออะไหล่' || currentAssignedStatusFilter === 'รอเปลี่ยนอะไหล่') {
                statusMatch = ['รออะไหล่', 'รอเปลี่ยนอะไหล่'].includes(rowStatus);
            } else if (currentAssignedStatusFilter === 'ไม่สามารถซ่อมได้' || currentAssignedStatusFilter === 'ซ่อมไม่ได้') {
                statusMatch = ['ไม่สามารถซ่อมได้', 'ซ่อมไม่ได้', 'ไม่อนุมัติงบ/ซ่อมไม่ได้'].includes(rowStatus);
            } else if (currentAssignedStatusFilter === 'รอรับงาน') {
                statusMatch = (rowStatus === 'รอรับงาน');
            } else {
                statusMatch = (rowStatus === currentAssignedStatusFilter);
            }

            let searchMatch = true;
            if (currentAssignedSearchQuery !== '') {
                const text = (item.innerText || item.textContent || '').toLowerCase();
                searchMatch = text.includes(currentAssignedSearchQuery);
            }

            if (statusMatch && searchMatch) {
                matching.push(idx);
            }
        }
        return matching;
    }

    function renderAssignedPaginationAndItems() {
        const cards = document.querySelectorAll('.assigned-job-card');
        const rows = document.querySelectorAll('.assigned-job-row');
        const totalItems = Math.max(cards.length, rows.length);
        if (totalItems === 0) return;

        const matching = getMatchingAssignedIndices();
        const totalMatching = matching.length;
        const totalPages = Math.ceil(totalMatching / assignedPageSize) || 1;

        if (assignedCurrentPage > totalPages) assignedCurrentPage = totalPages;
        if (assignedCurrentPage < 1) assignedCurrentPage = 1;

        const startIndex = (assignedCurrentPage - 1) * assignedPageSize;
        const endIndex = startIndex + assignedPageSize;

        // Show/hide rows and cards
        for (let i = 0; i < totalItems; i++) {
            const matchPos = matching.indexOf(i);
            if (matchPos !== -1 && matchPos >= startIndex && matchPos < endIndex) {
                if (rows[i]) rows[i].style.display = '';
                if (cards[i]) cards[i].style.display = '';
                if (currentAssignedSearchQuery !== '') {
                    if (rows[i]) applyHighlight(rows[i], currentAssignedSearchQuery);
                    if (cards[i]) applyHighlight(cards[i], currentAssignedSearchQuery);
                }
            } else {
                if (rows[i]) rows[i].style.display = 'none';
                if (cards[i]) cards[i].style.display = 'none';
            }
        }

        // Render pagination container
        const infoEl = document.getElementById('assigned-pagination-info');
        const btnsEl = document.getElementById('assigned-pagination-buttons');
        const containerEl = document.getElementById('assigned-pagination-container');

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

            // Previous button
            const isPrevDisabled = assignedCurrentPage === 1;
            btnHtml += `<button type="button" onclick="goToAssignedPage(${assignedCurrentPage - 1})" ${isPrevDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold transition flex items-center gap-1 ${isPrevDisabled ? 'bg-slate-50 text-slate-300 cursor-not-allowed border-slate-100' : 'bg-white hover:bg-slate-50 text-slate-600 hover:text-blue-600 hover:border-blue-200 cursor-pointer shadow-xs'}">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                <span class="hidden sm:inline">ก่อนหน้า</span>
            </button>`;

            // Page number buttons
            let pagesToShow = [];
            if (totalPages <= 7) {
                for (let p = 1; p <= totalPages; p++) pagesToShow.push(p);
            } else {
                pagesToShow.push(1);
                if (assignedCurrentPage > 3) pagesToShow.push('...');
                const startP = Math.max(2, assignedCurrentPage - 1);
                const endP = Math.min(totalPages - 1, assignedCurrentPage + 1);
                for (let p = startP; p <= endP; p++) {
                    if (!pagesToShow.includes(p)) pagesToShow.push(p);
                }
                if (assignedCurrentPage < totalPages - 2) pagesToShow.push('...');
                if (!pagesToShow.includes(totalPages)) pagesToShow.push(totalPages);
            }

            pagesToShow.forEach(p => {
                if (p === '...') {
                    btnHtml += `<span class="px-2 py-1 text-slate-400 font-bold">...</span>`;
                } else if (p === assignedCurrentPage) {
                    btnHtml += `<button type="button" class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-xs shadow-md shadow-blue-200 cursor-default">${p}</button>`;
                } else {
                    btnHtml += `<button type="button" onclick="goToAssignedPage(${p})" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 text-slate-600 hover:text-blue-600 border border-slate-200 font-bold text-xs transition cursor-pointer shadow-xs">${p}</button>`;
                }
            });

            // Next button
            const isNextDisabled = assignedCurrentPage === totalPages;
            btnHtml += `<button type="button" onclick="goToAssignedPage(${assignedCurrentPage + 1})" ${isNextDisabled ? 'disabled' : ''} class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold transition flex items-center gap-1 ${isNextDisabled ? 'bg-slate-50 text-slate-300 cursor-not-allowed border-slate-100' : 'bg-white hover:bg-slate-50 text-slate-600 hover:text-blue-600 hover:border-blue-200 cursor-pointer shadow-xs'}">
                <span class="hidden sm:inline">ถัดไป</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>`;

            btnsEl.innerHTML = btnHtml;
        }
    }

    function goToAssignedPage(page) {
        assignedCurrentPage = page;
        renderAssignedPaginationAndItems();
    }

    function filterAssignedJobs(status, element) {
        if (element) {
            const cards = document.querySelectorAll('.stat-assign-card');
            cards.forEach(card => card.classList.remove('active-stat'));
            element.classList.add('active-stat');
            
            currentAssignedStatusFilter = status;
            currentAssignedSearchQuery = '';
            assignedCurrentPage = 1;

            const searchInput = document.getElementById('search-assigned');
            if (searchInput) searchInput.value = '';
            
            const marks = document.querySelectorAll('.assigned-job-card mark.search-highlight, .assigned-job-row mark.search-highlight');
            marks.forEach(mark => {
                const parent = mark.parentNode;
                parent.replaceChild(document.createTextNode(mark.textContent), mark);
                parent.normalize();
            });

            renderAssignedPaginationAndItems();
        }
    }

    function filterAssigned() {
        const input = document.getElementById('search-assigned').value;
        currentAssignedSearchQuery = input.trim().toLowerCase();
        assignedCurrentPage = 1;

        const marks = document.querySelectorAll('.assigned-job-card mark.search-highlight, .assigned-job-row mark.search-highlight');
        marks.forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });

        renderAssignedPaginationAndItems();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('technician_assigned_view') || 'card';
        setAssignedViewMode(savedView);
        renderAssignedPaginationAndItems();
    });

    function filterHistory() {
        let input = document.getElementById('search-history').value;
        let lowerInput = input.toLowerCase();
        let table = document.getElementById('history-data-table');
        let rows = table.getElementsByClassName('history-row');
        for (let i = 0; i < rows.length; i++) {
            let text = rows[i].innerText || rows[i].textContent;
            if (text.toLowerCase().includes(lowerInput)) {
                rows[i].style.display = "";
                applyHighlight(rows[i], input);
            } else {
                rows[i].style.display = "none";
            }
        }
    }

    // ฟังก์ชันสำหรับยืนยันการรับงานด้วย SweetAlert2
    function confirmAccept(event, form) {
        event.preventDefault(); // หยุดการส่งฟอร์มทันที
        
        Swal.fire({
            html: `
                <div class="flex flex-col items-center mt-2 pb-2">
                    <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-5 ring-8 ring-blue-50/50 shadow-inner">
                        <i class="fa-solid fa-toolbox text-3xl drop-shadow-sm"></i>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 mb-2 font-prompt">ยืนยันการรับงานนี้?</h2>

                </div>
            `,
            showCancelButton: true,
            heightAuto: false,
            confirmButtonText: 'รับงาน',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-[2rem] shadow-2xl border border-slate-100 font-prompt p-6',
                confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold px-8 py-3 shadow-md hover:shadow-lg transition-all mx-2',
                cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold px-8 py-3 transition-all mx-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // แสดงสถานะกำลังโหลด
                Swal.fire({
                    title: 'กำลังดำเนินการ...',
                    allowOutsideClick: false,
                    heightAuto: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // ส่งฟอร์ม
                form.submit();
            }
        });
    }
    </script>
</body>
</html>

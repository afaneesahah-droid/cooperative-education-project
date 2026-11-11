@php
    $user = Auth::user();
    
    $dateRange = request('date_range');
    $startDate = request('start_date');
    $endDate = request('end_date');
    
    if ($dateRange) {
        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $startDate = trim($dates[0]);
            $endDate = trim($dates[1]);
        } else {
            $startDate = trim($dates[0]);
            $endDate = trim($dates[0]);
        }
    }
    
    $query = \App\Models\RepairRequest::with(['device.assetKind', 'user.departmentRelation', 'assignee', 'item', 'latestMaintenance', 'repairProject'])
        ->where(function($q) {
            $q->whereNull('type')->orWhere('type', '!=', 'เบิกงบประมาณ');
        })
        ->orderByRaw("
            CASE 
                WHEN status = 'รอมอบหมาย' THEN 1
                WHEN status = 'กำลังซ่อม' OR status = 'กำลังดำเนินการ' THEN 2
                WHEN status = 'รอเปลี่ยนอะไหล่' OR status = 'รออะไหล่' OR status = 'รอจัดซื้ออะไหล่' OR status = 'รออนุมัติงบประมาณ' OR status = 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ' THEN 3
                WHEN status = 'ไม่สามารถซ่อมได้' THEN 4
                WHEN status = 'เสร็จสิ้น' THEN 5
                ELSE 6
            END ASC
        ")
        ->orderBy('created_at', 'desc');
    
    if ($startDate && $endDate) {
        $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    } elseif ($startDate) {
        $query->where('created_at', '>=', $startDate . ' 00:00:00');
    } elseif ($endDate) {
        $query->where('created_at', '<=', $endDate . ' 23:59:59');
    }
    $requests = $query->get();
    
    try {
        $technicians = \App\Models\User::where(function($q) {
            $q->where('role', 'like', '%technician%')
              ->orWhere('role', 'like', '%ช่าง%')
              ->orWhere('role', 'like', '%ซ่อมบำรุง%')
              ->orWhere('department', 'like', '%ช่าง%')
              ->orWhere('department', 'like', '%ซ่อมบำรุง%');
        })->where('status', 'approved')->get();
    } catch (\Throwable $e) {
        $technicians = \App\Models\User::where('role', 'like', '%technician%')
            ->orWhere('role', 'like', '%ช่าง%')
            ->get();
    }

    // คำนวณจำนวนงานที่ต้องทำ (งานที่กำลังทำ/ค้างอยู่) ของช่างแต่ละคน
    $closedStatuses = ['เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์', 'ยกเลิก', 'ไม่สามารถซ่อมได้'];
    $techActiveJobsCount = [];
    
    try {
        $activeRequestsAll = \App\Models\RepairRequest::whereNotIn('status', $closedStatuses)->get();
        foreach ($activeRequestsAll as $ar) {
            $techId = $ar->assigned_to ?? optional($ar->assignee)->user_id ?? optional($ar->assignee)->id;
            if (!empty($techId)) {
                $techActiveJobsCount[$techId] = ($techActiveJobsCount[$techId] ?? 0) + 1;
            }
        }
    } catch (\Throwable $e) {
        foreach ($requests as $ar) {
            if (!in_array($ar->status, $closedStatuses)) {
                $techId = $ar->assigned_to ?? optional($ar->assignee)->user_id ?? optional($ar->assignee)->id;
                if (!empty($techId)) {
                    $techActiveJobsCount[$techId] = ($techActiveJobsCount[$techId] ?? 0) + 1;
                }
            }
        }
    }

    // เรียงลำดับเจ้าหน้าที่ตามจำนวนงานที่ต้องทำจากน้อยไปมาก เพื่อให้เลือกง่าย
    if (isset($technicians) && $technicians->count() > 0) {
        $technicians = $technicians->sortBy(function($t) use ($techActiveJobsCount) {
            return $techActiveJobsCount[$t->id] ?? $techActiveJobsCount[$t->user_id] ?? 0;
        });
    }

    // Stats calculations
    $pendingAssignment = $requests->where('status', 'รอมอบหมาย');
    $activeRepairs = $requests->whereIn('status', ['กำลังดำเนินการ', 'กำลังซ่อม', 'รอรับงาน']);
    $waitingPartsRepairs = $requests->whereIn('status', ['รอเปลี่ยนอะไหล่', 'รออะไหล่', 'รอจัดซื้ออะไหล่', 'รออนุมัติงบประมาณ', 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ']);
    $completedRepairs = $requests->where('status', 'เสร็จสิ้น');
    $unfixableRepairs = $requests->where('status', 'ไม่สามารถซ่อมได้');

    // Time-based calculations
    $todayRequests = $requests->filter(fn($r) => $r->created_at && $r->created_at->isToday());
    $monthRequests = $requests->filter(fn($r) => $r->created_at && $r->created_at->isCurrentMonth());
    $yearRequests = $requests->filter(fn($r) => $r->created_at && $r->created_at->isCurrentYear());

    // Latest Repairs
    $latestRepairs = $requests->take(5);

    // Device Category Stats for Chart (งานซ่อมแยกตามประเภททรัพย์สิน)
    $deviceCategoryStats = $requests->groupBy(function($r) {
        return $r->asset_category ?? 'ฮาร์ดแวร์';
    })->map->count()->sortDesc()->take(5);

    // Device Type Stats for Chart (งานซ่อมแยกตามชนิดทรัพย์สิน - เฉพาะชนิดทรัพย์สินจริงตามระบบ ไม่รวม '-')
    $validKindsList = $requests->map(function($r) {
        $k = $r->asset_kind;
        if (empty($k) || $k === '-' || $k === 'ไม่ระบุ' || $k === 'ไม่ระบุชนิด' || $k === 'อุปกรณ์ทั่วไป') {
            return null;
        }
        return [
            'kind' => $k,
            'category' => $r->asset_category ?? 'ฮาร์ดแวร์'
        ];
    })->filter()->values();

    $deviceTypeStats = $validKindsList->pluck('kind')->countBy()->sortDesc()->take(5);

    // Technician Stats
    $technicianStats = $requests->filter(function($r) {
        return $r->assignee != null;
    })->groupBy(function($r) {
        return $r->assignee->first_name ?? $r->assignee->name ?? 'ไม่ระบุ';
    })->map->count()->sortDesc()->take(5);

    // Department Stats
    $departmentStats = $requests->filter(function($r) {
        return $r->user != null;
    })->groupBy(function($r) {
        $dept = optional($r->user->departmentRelation)->department_name ?? $r->user->department ?? 'ไม่ระบุ';
        return $dept ?: 'ไม่ระบุ';
    })->map->count()->sortDesc()->take(5);

    // Dynamic Status Stats for Doughnut Chart in user specified order
    $preferredStatusOrder = ['รอมอบหมาย', 'กำลังซ่อม', 'รอเปลี่ยนอะไหล่', 'ไม่สามารถซ่อมได้', 'เสร็จสิ้น'];
    $statusStats = collect($preferredStatusOrder)->mapWithKeys(function($st) use ($requests) {
        return [$st => $requests->where('status', $st)->count()];
    })->filter(fn($c) => $c > 0);

    // Technician Satisfaction Stats (ผลการประเมินความพึงพอใจแยกตามช่าง)
    $techSatisfactionStats = collect($technicians)->map(function($tech) use ($requests) {
        $assignedJobs = $requests->filter(function($r) use ($tech) {
            return ($r->assigned_to == $tech->id || 
                    $r->assigned_to == $tech->user_id || 
                    optional($r->assignee)->id == $tech->id || 
                    optional($r->assignee)->user_id == $tech->user_id || 
                    (!empty($tech->name) && $r->assigned_to == $tech->name));
        });
        $ratedJobs = $assignedJobs->filter(function($r) {
            return (int)($r->rating ?? 0) > 0;
        });
        $ratedCount = $ratedJobs->count();
        $avgRating = $ratedCount > 0 ? round($ratedJobs->avg('rating'), 2) : 0;
        
        $breakdown = [
            5 => $ratedJobs->where('rating', 5)->count(),
            4 => $ratedJobs->where('rating', 4)->count(),
            3 => $ratedJobs->where('rating', 3)->count(),
            2 => $ratedJobs->where('rating', 2)->count(),
            1 => $ratedJobs->where('rating', 1)->count(),
        ];
        
        $feedbacks = $ratedJobs->filter(fn($r) => !empty(trim($r->eval_comment ?? '')));
        
        return [
            'tech' => $tech,
            'id' => $tech->id,
            'user_id' => $tech->user_id ?? $tech->id,
            'name' => $tech->name,
            'first_name' => $tech->first_name ?? $tech->name,
            'role' => $tech->role ?? 'ช่างซ่อมบำรุง',
            'avatar' => $tech->profile_photo ?? $tech->avatar ?? null,
            'total_jobs' => $assignedJobs->count(),
            'completed_jobs' => $assignedJobs->where('status', 'เสร็จสิ้น')->count(),
            'rated_count' => $ratedCount,
            'avg_rating' => $avgRating,
            'stars_pct' => $avgRating > 0 ? round(($avgRating / 5) * 100) : 0,
            'breakdown' => $breakdown,
            'feedbacks_count' => $feedbacks->count(),
            'feedbacks' => $feedbacks->sortByDesc('updated_at')->values(),
            'rated_jobs' => $ratedJobs->sortByDesc('updated_at')->values(),
        ];
    })->sortByDesc('avg_rating')->values();

    $overallRatedJobs = $requests->filter(fn($r) => (int)($r->rating ?? 0) > 0);
    $overallRatedCount = $overallRatedJobs->count();
    $overallAvgRating = $overallRatedCount > 0 ? round($overallRatedJobs->avg('rating'), 2) : 0;
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่วนงานหัวหน้าศูนย์ข้อมูล (Head Tactical Center) - วิทยาลัยเทคนิคยะลา</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <!-- FontAwesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SheetJS for Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <!-- html2pdf for PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #DBEAFE;
        }
        /* ลายน้ำตารางกระดานวิศวกรรมสไตล์โมเดิร์น */
        .modern-grid-bg {
            background-image: linear-gradient(to right, rgba(37, 99, 235, 0.009) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(37, 99, 235, 0.009) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .premium-white-card {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.12);
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .scrollbar-hide {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .stat-history-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .stat-history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(0,0,0,0.08);
        }
        .stat-history-card.active-stat {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25) !important;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased h-screen w-full max-w-full flex flex-col font-semibold selection:bg-blue-500 selection:text-white overflow-hidden">

    @include('dashboards.head.header')

    <div class="flex-1 flex flex-row w-full h-full overflow-hidden relative">
        @include('dashboards.head.sidebar')

        <main class="flex-1 min-w-0 h-full overflow-y-auto bg-slate-100 p-3 sm:p-5 md:p-8 space-y-6">

        @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-3.5 rounded-2xl text-sm font-bold max-w-4xl mx-auto">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="flex items-center gap-3 bg-red-50 text-red-700 border border-red-200 px-4 py-3.5 rounded-2xl text-sm font-bold max-w-4xl mx-auto">
                <i class="fa-solid fa-circle-xmark text-lg"></i>
                <span>{{ session('error') ?: ($errors->first() ?: 'เกิดข้อผิดพลาดในการทำรายการ กรุณากรอกข้อมูลให้ครบถ้วน') }}</span>
            </div>
        @endif

        @php
            $currentReqTab = request('tab', 'summary');
            if (str_starts_with($currentReqTab, 'tab-')) {
                $currentReqTab = substr($currentReqTab, 4);
            }
            $isSummaryReq = ($currentReqTab === 'summary');
            $isPendingReq = ($currentReqTab === 'pending');
            $isHistoryReq = ($currentReqTab === 'history');
        @endphp

        <!-- ==================== TAB 1: ข้อมูลสรุปการซ่อม ==================== -->
        <div id="tab-summary" class="tab-content {{ $isSummaryReq ? 'block' : 'hidden' }} animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Unified Premium Container -->
            <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 px-6 py-5 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[14px] bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg border border-white/30 shadow-inner">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-white tracking-wide">สรุปงานซ่อม</h2>
                            </div>
                        </div>
                        <form action="{{ route('dashboard.head') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                            <!-- เราส่งแท็บ summary ให้เปิดอัตโนมัติเมื่อกด submit จะได้ไม่กลับไปหน้าอื่น -->
                            <input type="hidden" name="tab" value="summary">

                            <!-- Date Picker -->
                            <div class="flex items-center gap-2">
                                <div class="relative flex items-center bg-white/10 border border-white/20 rounded-full shadow-sm backdrop-blur-sm px-4 py-2 w-32 hover:bg-white/20 transition-all cursor-pointer">
                                    <span class="text-blue-100 mr-2"><i class="fa-regular fa-calendar"></i></span>
                                    <input type="text" name="start_date" value="{{ request('start_date') }}" placeholder="ตั้งแต่" class="single_date_picker bg-transparent text-white text-[13px] font-extrabold focus:outline-none w-full placeholder-blue-100/70 cursor-pointer text-left" readonly onchange="this.form.submit()">
                                </div>
                                <span class="text-white text-sm font-bold">-</span>
                                <div class="relative flex items-center bg-white/10 border border-white/20 rounded-full shadow-sm backdrop-blur-sm px-4 py-2 w-32 hover:bg-white/20 transition-all cursor-pointer">
                                    <span class="text-blue-100 mr-2"><i class="fa-regular fa-calendar"></i></span>
                                    <input type="text" name="end_date" value="{{ request('end_date') }}" placeholder="สิ้นสุด" class="single_date_picker bg-transparent text-white text-[13px] font-extrabold focus:outline-none w-full placeholder-blue-100/70 cursor-pointer text-left" readonly onchange="this.form.submit()">
                                </div>
                            </div>
                            
                            @if(request('start_date') || request('end_date'))
                            <a href="{{ route('dashboard.head', ['tab' => 'summary']) }}" class="w-9 h-9 flex items-center justify-center rounded-full bg-red-400/20 text-red-100 hover:bg-red-400/40 border border-red-300/30 transition-all shadow-sm" title="ล้างตัวกรอง">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </a>
                            @endif
                        </form>
                    </div>
                </div>
                <!-- Body -->
                <div class="p-8 space-y-6 bg-slate-50/30">
                    <!-- Stats Cards Row -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- แจ้งซ่อมทั้งหมด -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1" onclick="jumpToHistory('')">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-blue-100 text-blue-600 shadow-[0_0_15px_rgba(37,99,235,0.3)]">
                                    <i class="fa-solid fa-clipboard-list text-xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[11px] font-extrabold text-slate-600 mb-0.5 truncate">แจ้งซ่อมทั้งหมด</h3>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-3xl font-black text-blue-700 leading-none">{{ $requests->count() }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- รอดำเนินการ (Pending Assignment) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1" onclick="jumpToHistory('รอดำเนินการ')">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-orange-100 text-orange-500 shadow-[0_0_15px_rgba(249,115,22,0.3)]">
                                    <i class="fa-regular fa-clock text-xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[11px] font-extrabold text-slate-600 mb-0.5 truncate">รอดำเนินการ</h3>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-3xl font-black text-orange-600 leading-none">{{ $pendingAssignment->count() }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- กำลังดำเนินการ (Active) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1" onclick="jumpToHistory('กำลังดำเนินการ')">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-indigo-100 text-indigo-500 shadow-[0_0_15px_rgba(99,102,241,0.3)]">
                                    <i class="fa-solid fa-screwdriver-wrench text-xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[11px] font-extrabold text-slate-600 mb-0.5 truncate">กำลังดำเนินการ</h3>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-3xl font-black text-indigo-700 leading-none">{{ $activeRepairs->count() }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ซ่อมเสร็จแล้ว (Completed) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1" onclick="jumpToHistory('เสร็จสิ้น')">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 bg-green-100 text-green-500 shadow-[0_0_15px_rgba(34,197,94,0.3)]">
                                    <i class="fa-regular fa-circle-check text-xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[11px] font-extrabold text-slate-600 mb-0.5 truncate">ซ่อมเสร็จแล้ว</h3>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-3xl font-black text-green-600 leading-none">{{ $completedRepairs->count() }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold">รายการ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts & List Section (Grid of 5 panels) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <script>
                            window.summaryKindsRawData = {!! json_encode($validKindsList, JSON_UNESCAPED_UNICODE) !!};
                        </script>
                        <!-- Panel 1: งานซ่อมแยกตามชนิดทรัพย์สิน (Bar Chart) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col h-[320px]">
                            <div class="flex justify-between items-center mb-1">
                                <h3 class="text-[11px] font-black text-slate-800">งานซ่อมแยกตามชนิดทรัพย์สิน</h3>
                                <select onchange="if(typeof filterSummaryKindsByCategory === 'function') filterSummaryKindsByCategory(this.value);" class="text-[9px] font-bold text-slate-500 bg-white border border-slate-200 rounded-md px-1.5 py-0.5 outline-none cursor-pointer">
                                    <option value="all">ทั้งหมด</option>
                                    <option value="ฮาร์ดแวร์">ฮาร์ดแวร์</option>
                                    <option value="ซอฟต์แวร์">ซอฟต์แวร์</option>
                                    <option value="ระบบเครือข่าย">ระบบเครือข่าย</option>
                                    <option value="IOT">IOT</option>
                                </select>
                            </div>
                            <p class="text-[9px] text-slate-400 font-bold mb-2">(รายการ)</p>
                            <div class="flex-1 w-full h-full relative">
                                <canvas id="chartSummaryAssetKind" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <!-- Panel 2: งานซ่อมแยกตามประเภททรัพย์สิน (Pie Chart) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col h-[320px]">
                            <div class="flex justify-between items-center mb-1">
                                <h3 class="text-[11px] font-black text-slate-800">งานซ่อมแยกตามประเภททรัพย์สิน</h3>
                                <select class="text-[9px] font-bold text-slate-500 bg-white border border-slate-200 rounded-md px-1.5 py-0.5 outline-none cursor-pointer">
                                    <option>ทั้งหมด</option>
                                </select>
                            </div>
                            <p class="text-[9px] text-slate-400 font-bold mb-2">(รายการ)</p>
                            <div class="flex-1 w-full h-full relative flex items-center justify-center">
                                <canvas id="chartSummaryCategory" class="w-full h-full max-h-[220px]"></canvas>
                            </div>
                        </div>

                        <!-- Panel 3: ช่างที่รับงานซ่อมสูงสุด (Horizontal Bar Chart) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col h-[320px]">
                            <div class="flex justify-between items-center mb-1">
                                <h3 class="text-[11px] font-black text-slate-800">ช่างที่รับงานซ่อมสูงสุด</h3>
                            </div>
                            <p class="text-[9px] text-slate-400 font-bold mb-2">(รายการ)</p>
                            <div class="flex-1 w-full h-full relative">
                                <canvas id="chartSummaryTechnician" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <!-- Panel 4: ฝ่ายที่แจ้งซ่อมสูงสุด (Line Chart) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col h-[320px]">
                            <div class="flex justify-between items-center mb-1">
                                <h3 class="text-[11px] font-black text-slate-800">ฝ่ายที่แจ้งซ่อมสูงสุด</h3>
                            </div>
                            <p class="text-[9px] text-slate-400 font-bold mb-2">(รายการ)</p>
                            <div class="flex-1 w-full h-full relative">
                                <canvas id="chartSummaryDepartment" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <!-- Panel 5: งานซ่อมล่าสุด (Latest Repairs List) -->
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col h-[320px] lg:col-span-2">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-[11px] font-black text-slate-800">งานซ่อมล่าสุด</h3>
                                <a href="javascript:void(0)" onclick="jumpToHistory('')" class="text-[9px] font-bold text-blue-600 hover:underline">ดูทั้งหมด</a>
                            </div>
                            <div class="flex-1 overflow-y-auto pr-1 space-y-3.5 scrollbar-hide">
                                @foreach($latestRepairs as $job)
                                <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2.5 last:border-0 last:pb-0">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <span class="text-[10px] font-bold text-slate-700 shrink-0">{{ $job->request_code }}</span>
                                        <span class="text-[10px] font-medium text-slate-500 truncate" title="{{ $job->issue_description ?? $job->clean_symptom ?? $job->description ?? 'ไม่ระบุอาการ' }}">{{ Str::limit($job->issue_description ?? $job->clean_symptom ?? $job->description ?? 'ไม่ระบุอาการ', 25) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @php
                                            $bg = 'bg-slate-50'; $text = 'text-slate-600'; $border = 'border-slate-100';
                                            $displayStatus = $job->status;
                                            if(in_array($job->status, ['รอมอบหมาย', 'รอรับงาน'])) { $bg = 'bg-orange-50'; $text = 'text-orange-500'; $border = 'border-orange-100'; $displayStatus = 'รอดำเนินการ'; }
                                            elseif(in_array($job->status, ['กำลังดำเนินการ', 'กำลังซ่อม'])) { $bg = 'bg-blue-50'; $text = 'text-blue-500'; $border = 'border-blue-100'; $displayStatus = 'กำลังดำเนินการ'; }
                                            elseif($job->status == 'เสร็จสิ้น') { $bg = 'bg-green-50'; $text = 'text-green-600'; $border = 'border-green-100'; $displayStatus = 'ซ่อมเสร็จแล้ว'; }
                                            elseif(in_array($job->status, ['ไม่สามารถซ่อมได้', 'ยกเลิก'])) { $bg = 'bg-red-50'; $text = 'text-red-500'; $border = 'border-red-100'; $displayStatus = 'ยกเลิก'; }
                                        @endphp
                                        <span class="{{ $bg }} {{ $text }} {{ $border }} border px-1.5 py-0.5 rounded text-[8px] font-bold w-16 text-center">
                                            {{ $displayStatus }}
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-bold w-[50px] text-right">{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                                @endforeach
                                @if($latestRepairs->count() == 0)
                                <div class="text-center py-8 text-slate-400 text-[10px] font-bold">ไม่มีรายการซ่อม</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Section: ผลการประเมินความพึงพอใจแยกตามช่างซ่อมบำรุง (Leaderboard) -->
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm font-bold shadow-xs">
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                        ผลการประเมินความพึงพอใจแยกตามช่างซ่อมบำรุง
                                        <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">จัดอันดับตามคะแนนเฉลี่ย</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-400 font-medium">สถิติคะแนนความพึงพอใจจากผู้รับบริการที่ประเมินงานซ่อมของเจ้าหน้าที่แต่ละท่าน</p>
                                </div>
                            </div>

                            <!-- Overall Score Badge -->
                            <div class="flex items-center gap-2 bg-gradient-to-r from-amber-50 to-orange-50 px-3.5 py-1.5 rounded-xl border border-amber-200/60 shadow-xs self-start sm:self-auto">
                                <div class="flex items-center gap-1 text-amber-500 text-xs">
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <span class="text-xs font-black text-amber-900">คะแนนเฉลี่ยรวม: {{ $overallAvgRating > 0 ? number_format($overallAvgRating, 2) : '0.00' }} / 5.0</span>
                                <span class="text-[10px] text-amber-700/80 font-bold bg-amber-200/50 px-2 py-0.5 rounded-md">ประเมินแล้ว {{ $overallRatedCount }} งาน</span>
                            </div>
                        </div>

                        <!-- Technician Leaderboard Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @forelse($techSatisfactionStats as $index => $stat)
                                @php
                                    $rank = $index + 1;
                                    $rankBadge = match($rank) {
                                        1 => ['bg' => 'bg-amber-500 text-white shadow-amber-200 shadow-md', 'icon' => 'fa-trophy', 'label' => 'อันดับ 1'],
                                        2 => ['bg' => 'bg-slate-400 text-white shadow-slate-200 shadow-md', 'icon' => 'fa-medal', 'label' => 'อันดับ 2'],
                                        3 => ['bg' => 'bg-amber-700 text-white shadow-amber-200 shadow-md', 'icon' => 'fa-award', 'label' => 'อันดับ 3'],
                                        default => ['bg' => 'bg-slate-100 text-slate-600', 'icon' => 'fa-hashtag', 'label' => 'อันดับ ' . $rank],
                                    };
                                    
                                    $tier = match(true) {
                                        $stat['avg_rating'] >= 4.5 => ['label' => 'ดีเยี่ยม (Excellent)', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        $stat['avg_rating'] >= 3.5 => ['label' => 'ดีมาก (Very Good)', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                        $stat['avg_rating'] >= 2.5 => ['label' => 'ปานกลาง (Fair)', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                        $stat['avg_rating'] > 0    => ['label' => 'ควรปรับปรุง', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                        default                    => ['label' => 'ยังไม่มีการประเมิน', 'bg' => 'bg-slate-50 text-slate-500 border-slate-200'],
                                    };
                                @endphp
                                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:shadow-md hover:border-blue-200 transition-all flex flex-col justify-between group relative overflow-hidden">
                                    <!-- Top Rank & Role Header -->
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-black {{ $rankBadge['bg'] }}">
                                                <i class="fa-solid {{ $rankBadge['icon'] }} text-[9px]"></i>
                                                {{ $rankBadge['label'] }}
                                            </span>
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border {{ $tier['bg'] }}">
                                                {{ $tier['label'] }}
                                            </span>
                                        </div>

                                        <!-- Tech Profile & Name -->
                                        <div class="flex items-center gap-3 mb-3.5">
                                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-sm font-black shadow-inner shrink-0 relative overflow-hidden">
                                                @if(!empty($stat['avatar']))
                                                    <img src="{{ asset('uploads/profiles/' . $stat['avatar']) }}" alt="{{ $stat['name'] }}" class="w-full h-full object-cover">
                                                @else
                                                    <span>{{ mb_substr($stat['name'], 0, 1, 'UTF-8') }}</span>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h4 class="font-black text-slate-800 text-xs sm:text-sm truncate group-hover:text-blue-600 transition-colors" title="{{ $stat['name'] }}">{{ $stat['name'] }}</h4>
                                                <p class="text-[10px] text-slate-400 font-semibold truncate">{{ $stat['role'] }}</p>
                                            </div>
                                        </div>

                                        <!-- Large Rating Display -->
                                        <div class="bg-gradient-to-br from-amber-50/60 to-orange-50/40 rounded-xl p-3 border border-amber-100/70 mb-3">
                                            <div class="flex items-baseline justify-between mb-1.5">
                                                <div class="flex items-baseline gap-1">
                                                    <span class="text-2xl font-black text-slate-800 tracking-tight">{{ $stat['avg_rating'] > 0 ? number_format($stat['avg_rating'], 2) : '0.00' }}</span>
                                                    <span class="text-[11px] font-bold text-slate-400">/ 5.0</span>
                                                </div>
                                                <div class="text-[10px] font-bold text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded-md">
                                                    {{ $stat['rated_count'] }} ประเมิน
                                                </div>
                                            </div>

                                            <!-- Star Icons -->
                                            <div class="flex items-center gap-1 text-amber-400 text-xs mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($stat['avg_rating'] >= $i)
                                                        <i class="fa-solid fa-star text-amber-400"></i>
                                                    @elseif($stat['avg_rating'] >= ($i - 0.5))
                                                        <i class="fa-solid fa-star-half-stroke text-amber-400"></i>
                                                    @else
                                                        <i class="fa-regular fa-star text-slate-300"></i>
                                                    @endif
                                                @endfor
                                                <span class="text-[10px] font-bold text-slate-500 ml-1.5">({{ $stat['stars_pct'] }}%)</span>
                                            </div>

                                            <!-- Star Rating Breakdown Bars -->
                                            <div class="space-y-1 pt-1.5 border-t border-amber-100/60 text-[9px] font-bold text-slate-500">
                                                @foreach([5, 4, 3, 2, 1] as $starLevel)
                                                    @php
                                                        $starCount = $stat['breakdown'][$starLevel] ?? 0;
                                                        $barPct = $stat['rated_count'] > 0 ? round(($starCount / $stat['rated_count']) * 100) : 0;
                                                    @endphp
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="w-4 text-right shrink-0">{{ $starLevel }}★</span>
                                                        <div class="flex-1 h-1.5 bg-slate-200/70 rounded-full overflow-hidden">
                                                            <div class="h-full bg-amber-400 rounded-full" style="width: {{ $barPct }}%"></div>
                                                        </div>
                                                        <span class="w-5 text-right text-slate-400 shrink-0">{{ $starCount }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Workload Summary -->
                                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 px-1 mb-3">
                                            <span>งานที่รับผิดชอบ: <strong class="text-slate-800">{{ $stat['total_jobs'] }}</strong> งาน</span>
                                            <span>เสร็จแล้ว: <strong class="text-emerald-600">{{ $stat['completed_jobs'] }}</strong> งาน</span>
                                        </div>
                                    </div>

                                    <!-- Action Button to View Details & Feedback in Tab 3 -->
                                    <button type="button" 
                                            onclick="viewTechFeedbackInHistory('{{ $stat['id'] }}', '{{ addslashes($stat['name']) }}')"
                                            class="w-full mt-1 py-2 px-3 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200 hover:border-blue-200 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                        <i class="fa-solid fa-comments text-xs"></i>
                                        <span>ดูข้อเสนอแนะ & ประวัติงาน</span>
                                        <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
                                    </button>
                                </div>
                            @empty
                                <div class="col-span-full py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-user-xmark text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-bold text-sm">ไม่พบข้อมูลช่างในระบบ</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 2: ตรวจสอบ & มอบหมายงาน ==================== -->
        <div id="tab-pending" class="tab-content {{ $isPendingReq ? 'block' : 'hidden' }} animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Unified Premium Container -->
            <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 px-4 sm:px-6 py-4 sm:py-5 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-[14px] bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-base sm:text-lg border border-white/30 shadow-inner">
                                <i class="fa-solid fa-hourglass-half"></i>
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white tracking-wide">มอบหมายงาน</h2>
                            </div>
                        </div>
                        <form action="{{ route('dashboard.head') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                            <input type="hidden" name="tab" value="pending">

                            <!-- Date Picker -->
                            <div class="flex items-center gap-2">
                                <div class="relative flex items-center bg-white/10 border border-white/20 rounded-full shadow-sm backdrop-blur-sm px-4 py-2 w-32 hover:bg-white/20 transition-all cursor-pointer">
                                    <span class="text-blue-100 mr-2"><i class="fa-regular fa-calendar"></i></span>
                                    <input type="text" name="start_date" value="{{ request('start_date') }}" placeholder="ตั้งแต่" class="single_date_picker bg-transparent text-white text-[13px] font-extrabold focus:outline-none w-full placeholder-blue-100/70 cursor-pointer text-left" readonly onchange="this.form.submit()">
                                </div>
                                <span class="text-white text-sm font-bold">-</span>
                                <div class="relative flex items-center bg-white/10 border border-white/20 rounded-full shadow-sm backdrop-blur-sm px-4 py-2 w-32 hover:bg-white/20 transition-all cursor-pointer">
                                    <span class="text-blue-100 mr-2"><i class="fa-regular fa-calendar"></i></span>
                                    <input type="text" name="end_date" value="{{ request('end_date') }}" placeholder="สิ้นสุด" class="single_date_picker bg-transparent text-white text-[13px] font-extrabold focus:outline-none w-full placeholder-blue-100/70 cursor-pointer text-left" readonly onchange="this.form.submit()">
                                </div>
                            </div>
                            
                            @if(request('start_date') || request('end_date'))
                            <a href="{{ route('dashboard.head') }}?tab=pending" class="w-9 h-9 flex items-center justify-center rounded-full bg-red-400/20 text-red-100 hover:bg-red-400/40 border border-red-300/30 transition-all shadow-sm" title="ล้างตัวกรอง">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </a>
                            @endif
                        </form>
                    </div>
                </div>
                <!-- Body -->
                <div class="p-4 sm:p-6 bg-slate-50/30 space-y-4">

            <!-- Summary Cards Row -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2.5 sm:gap-3 2xl:gap-3.5">
                <!-- งานทั้งหมด -->
                <div class="stat-assign-card active-stat bg-white rounded-2xl p-3 sm:px-3.5 sm:py-3.5 shadow-md border ring-2 ring-blue-500 border-blue-400 cursor-pointer transition-all hover:shadow-lg hover:-translate-y-1 min-h-[88px] sm:min-h-[94px] flex items-center" onclick="filterAssignHead('ทั้งหมด', this)">
                    <div class="flex items-center gap-2.5 sm:gap-3 pointer-events-none w-full min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 2xl:w-12 2xl:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 bg-blue-100 text-blue-600 shadow-[0_0_12px_rgba(37,99,235,0.2)]">
                            <i class="fa-solid fa-clipboard-list text-lg sm:text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-[11px] sm:text-xs font-black text-slate-700 mb-0.5 leading-tight tracking-tight whitespace-normal break-words" title="งานทั้งหมด">งานทั้งหมด</h3>
                            <div class="flex items-baseline gap-1 flex-nowrap whitespace-nowrap">
                                <span class="text-xl sm:text-2xl 2xl:text-3xl font-black text-blue-700 leading-none tracking-tight shrink-0">{{ $requests->count() }}</span>
                                <span class="text-[10px] sm:text-[11px] text-slate-400 font-bold shrink-0">รายการ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- รอมอบหมาย -->
                <div class="stat-assign-card bg-white rounded-2xl p-3 sm:px-3.5 sm:py-3.5 shadow-xs border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1 min-h-[88px] sm:min-h-[94px] flex items-center" onclick="filterAssignHead('รอมอบหมาย', this)">
                    <div class="flex items-center gap-2.5 sm:gap-3 pointer-events-none w-full min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 2xl:w-12 2xl:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 bg-orange-100 text-orange-500 shadow-[0_0_12px_rgba(249,115,22,0.2)]">
                            <i class="fa-regular fa-clock text-lg sm:text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-[11px] sm:text-xs font-black text-slate-700 mb-0.5 leading-tight tracking-tight whitespace-normal break-words" title="รอมอบหมาย">รอมอบหมาย</h3>
                            <div class="flex items-baseline gap-1 flex-nowrap whitespace-nowrap">
                                <span class="text-xl sm:text-2xl 2xl:text-3xl font-black text-orange-600 leading-none tracking-tight shrink-0">{{ $pendingAssignment->count() }}</span>
                                <span class="text-[10px] sm:text-[11px] text-slate-400 font-bold shrink-0">รายการ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- กำลังซ่อม -->
                <div class="stat-assign-card bg-white rounded-2xl p-3 sm:px-3.5 sm:py-3.5 shadow-xs border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1 min-h-[88px] sm:min-h-[94px] flex items-center" onclick="filterAssignHead('กำลังซ่อม', this)">
                    <div class="flex items-center gap-2.5 sm:gap-3 pointer-events-none w-full min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 2xl:w-12 2xl:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 bg-indigo-100 text-indigo-500 shadow-[0_0_12px_rgba(99,102,241,0.2)]">
                            <i class="fa-solid fa-screwdriver-wrench text-lg sm:text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-[11px] sm:text-xs font-black text-slate-700 mb-0.5 leading-tight tracking-tight whitespace-normal break-words" title="กำลังซ่อม">กำลังซ่อม</h3>
                            <div class="flex items-baseline gap-1 flex-nowrap whitespace-nowrap">
                                <span class="text-xl sm:text-2xl 2xl:text-3xl font-black text-indigo-700 leading-none tracking-tight shrink-0">{{ $activeRepairs->count() }}</span>
                                <span class="text-[10px] sm:text-[11px] text-slate-400 font-bold shrink-0">รายการ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- รอเปลี่ยนอะไหล่ -->
                <div class="stat-assign-card bg-white rounded-2xl p-3 sm:px-3.5 sm:py-3.5 shadow-xs border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1 min-h-[88px] sm:min-h-[94px] flex items-center" onclick="filterAssignHead('รอเปลี่ยนอะไหล่', this)">
                    <div class="flex items-center gap-2.5 sm:gap-3 pointer-events-none w-full min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 2xl:w-12 2xl:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 bg-purple-100 text-purple-600 shadow-[0_0_12px_rgba(168,85,247,0.2)]">
                            <i class="fa-solid fa-box-open text-lg sm:text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-[11px] sm:text-xs font-black text-slate-700 mb-0.5 leading-tight tracking-tight whitespace-normal break-words" title="รอเปลี่ยนอะไหล่">รอเปลี่ยนอะไหล่</h3>
                            <div class="flex items-baseline gap-1 flex-nowrap whitespace-nowrap">
                                <span class="text-xl sm:text-2xl 2xl:text-3xl font-black text-purple-600 leading-none tracking-tight shrink-0">{{ $waitingPartsRepairs->count() }}</span>
                                <span class="text-[10px] sm:text-[11px] text-slate-400 font-bold shrink-0">รายการ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ไม่สามารถซ่อมได้ -->
                <div class="stat-assign-card bg-white rounded-2xl p-3 sm:px-3.5 sm:py-3.5 shadow-xs border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1 min-h-[88px] sm:min-h-[94px] flex items-center" onclick="filterAssignHead('ไม่สามารถซ่อมได้', this)">
                    <div class="flex items-center gap-2.5 sm:gap-3 pointer-events-none w-full min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 2xl:w-12 2xl:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 bg-red-100 text-red-500 shadow-[0_0_12px_rgba(239,68,68,0.2)]">
                            <i class="fa-solid fa-ban text-lg sm:text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-[11px] sm:text-xs font-black text-slate-700 mb-0.5 leading-tight tracking-tight whitespace-normal break-words" title="ไม่สามารถซ่อมได้">ไม่สามารถซ่อมได้</h3>
                            <div class="flex items-baseline gap-1 flex-nowrap whitespace-nowrap">
                                <span class="text-xl sm:text-2xl 2xl:text-3xl font-black text-red-600 leading-none tracking-tight shrink-0">{{ $unfixableRepairs->count() }}</span>
                                <span class="text-[10px] sm:text-[11px] text-slate-400 font-bold shrink-0">รายการ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- เสร็จสิ้น -->
                <div class="stat-assign-card bg-white rounded-2xl p-3 sm:px-3.5 sm:py-3.5 shadow-xs border border-slate-100 cursor-pointer transition-all hover:shadow-md hover:-translate-y-1 min-h-[88px] sm:min-h-[94px] flex items-center" onclick="filterAssignHead('เสร็จสิ้น', this)">
                    <div class="flex items-center gap-2.5 sm:gap-3 pointer-events-none w-full min-w-0">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 2xl:w-12 2xl:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-500 shadow-[0_0_12px_rgba(34,197,94,0.2)]">
                            <i class="fa-regular fa-circle-check text-lg sm:text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-[11px] sm:text-xs font-black text-slate-700 mb-0.5 leading-tight tracking-tight whitespace-normal break-words" title="เสร็จสิ้น">เสร็จสิ้น</h3>
                            <div class="flex items-baseline gap-1 flex-nowrap whitespace-nowrap">
                                <span class="text-xl sm:text-2xl 2xl:text-3xl font-black text-emerald-600 leading-none tracking-tight shrink-0">{{ $completedRepairs->count() }}</span>
                                <span class="text-[10px] sm:text-[11px] text-slate-400 font-bold shrink-0">รายการ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-100 overflow-hidden mt-4">
                <div class="overflow-x-auto w-full max-w-full custom-scrollbar" style="-webkit-overflow-scrolling: touch;">
                    <table class="w-full text-left border-collapse whitespace-nowrap" id="assign-jobs-table">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-bold text-slate-700 uppercase tracking-wide">
                                <th class="px-4 py-3">รหัสการแจ้งซ่อม</th>
                                <th class="px-4 py-3">ชนิดทรัพย์สิน</th>
                                <th class="px-4 py-3">อาการ</th>
                                <th class="px-4 py-3">ผู้แจ้ง</th>
                                <th class="px-4 py-3">วันที่แจ้ง</th>
                                <th class="px-4 py-3">สถานะ</th>
                                <th class="px-4 py-3 text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-bold text-slate-500 divide-y divide-slate-50">
                            <tr class="empty-row-assign hidden">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-semibold bg-white">
                                    ไม่พบรายการแจ้งซ่อมที่ตรงกับเงื่อนไข
                                </td>
                            </tr>
                            @foreach($requests as $job)
                            <tr class="hover:bg-slate-50/50 transition-colors bg-white assign-row" data-status="{{ $job->status }}">
                                <td class="px-4 py-2.5">
                                    <span class="font-mono text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100 text-[11px]">{{ $job->request_code }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-bold text-slate-700 max-w-[130px] truncate" title="{{ $job->asset_kind ?? 'ไม่ระบุ' }}">{{ $job->asset_kind ?? 'ไม่ระบุ' }}</td>
                                <td class="px-4 py-2.5 max-w-[150px] truncate text-slate-600" title="{{ $job->clean_symptom ?: ($job->description ?: '-') }}">{{ $job->clean_symptom ?: ($job->description ?: '-') }}</td>
                                <td class="px-4 py-2.5 max-w-[130px] truncate text-slate-600" title="{{ $job->user->name ?? 'ครู/บุคลากร' }}">{{ $job->user->name ?? 'ครู/บุคลากร' }}</td>
                                <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($job->status === 'รอมอบหมาย')
                                        <span class="bg-orange-50 text-orange-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-orange-200">รอมอบหมาย</span>
                                    @elseif($job->status === 'รอรับงาน')
                                        <span class="bg-amber-50 text-amber-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-amber-200">รอรับงาน</span>
                                        @if($job->assignee)
                                            <div class="text-[10px] text-slate-500 font-semibold mt-0.5 flex items-center gap-1" title="ช่าง: {{ $job->assignee->name }}">
                                                <i class="fa-solid fa-user-gear text-blue-500 text-[9px]"></i>
                                                <span class="truncate max-w-[120px]">{{ $job->assignee->name }}</span>
                                            </div>
                                        @endif
                                    @elseif($job->status === 'กำลังดำเนินการ' || $job->status === 'กำลังซ่อม')
                                        <span class="bg-indigo-50 text-indigo-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-indigo-200">กำลังซ่อม</span>
                                        @if($job->assignee)
                                            <div class="text-[10px] text-slate-500 font-semibold mt-0.5 flex items-center gap-1" title="ช่าง: {{ $job->assignee->name }}">
                                                <i class="fa-solid fa-user-gear text-blue-500 text-[9px]"></i>
                                                <span class="truncate max-w-[120px]">{{ $job->assignee->name }}</span>
                                            </div>
                                        @endif
                                    @elseif($job->status === 'รอเปลี่ยนอะไหล่' || $job->status === 'รออะไหล่' || $job->status === 'รอจัดซื้ออะไหล่' || $job->status === 'รออนุมัติงบประมาณ' || $job->status === 'ยืนยันการสั่งซื้ออะไหล่สำเร็จ')
                                        <span class="bg-purple-50 text-purple-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-purple-200">รอเปลี่ยนอะไหล่</span>
                                        @if($job->assignee)
                                            <div class="text-[10px] text-slate-500 font-semibold mt-0.5 flex items-center gap-1" title="ช่าง: {{ $job->assignee->name }}">
                                                <i class="fa-solid fa-user-gear text-blue-500 text-[9px]"></i>
                                                <span class="truncate max-w-[120px]">{{ $job->assignee->name }}</span>
                                            </div>
                                        @endif
                                    @elseif($job->status === 'เสร็จสิ้น')
                                        <span class="bg-emerald-50 text-emerald-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-emerald-200">เสร็จสิ้น</span>
                                        @if((int)($job->rating ?? 0) > 0)
                                            <div class="text-[9px] text-amber-600 font-bold mt-0.5 flex items-center gap-1 justify-center sm:justify-start">
                                                <i class="fa-solid fa-star text-amber-500 text-[8px]"></i>
                                                <span>{{ number_format($job->rating, 1) }}</span>
                                            </div>
                                        @endif
                                    @elseif($job->status === 'ไม่สามารถซ่อมได้')
                                        <span class="bg-rose-50 text-rose-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-rose-200">ไม่สามารถซ่อมได้</span>
                                    @else
                                        <span class="bg-amber-50 text-amber-600 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-amber-200">{{ $job->status }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                    @if($job->status === 'รอมอบหมาย' || ($job->status === 'รอรับงาน' && !$job->assignee))
                                    <button type="button" 
                                            onclick="openAssignModal(this)" 
                                            data-id="{{ $job->id }}"
                                            data-code="{{ $job->request_code }}"
                                            data-kind="{{ $job->asset_kind ?? 'ไม่ระบุ' }}"
                                            data-device="{{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }} (#{{ $job->device->device_code ?? '-' }})"
                                            data-desc="{{ $job->description }}"
                                            data-loc="{{ $job->location }}"
                                            data-type="{{ $job->device->type ?? $job->type ?? '-' }}"
                                            data-user="{{ $job->user->name ?? 'ครู/บุคลากร' }}"
                                            data-priority="{{ $job->priority }}"
                                            data-date="{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') }} น."
                                            data-assignee-id="{{ $job->assigned_to ?? optional($job->assignee)->user_id ?? '' }}"
                                            data-assignee-name="{{ optional($job->assignee)->name ?? 'ยังไม่มอบหมายงาน' }}"
                                            data-status="{{ $job->status }}"
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold py-1.5 px-3 rounded-xl text-xs transition shadow-sm shadow-blue-100 inline-flex items-center gap-1.5 cursor-pointer">
                                        มอบหมายงาน
                                    </button>
                                    @else
                                    <button type="button" 
                                            onclick="openDetailsModal(this)" 
                                            data-id="{{ $job->id }}"
                                            data-code="{{ $job->request_code }}"
                                            data-kind="{{ $job->asset_kind ?? 'ไม่ระบุ' }}"
                                            data-device="{{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }} (#{{ $job->device->device_code ?? '-' }})"
                                            data-desc="{{ $job->description }}"
                                            data-loc="{{ $job->location }}"
                                            data-type="{{ $job->device->type ?? $job->type ?? '-' }}"
                                            data-user="{{ $job->user->name ?? 'ครู/บุคลากร' }}"
                                            data-priority="{{ $job->priority }}"
                                            data-date="{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') }} น."
                                            data-assignee-id="{{ $job->assigned_to ?? optional($job->assignee)->user_id ?? '' }}"
                                            data-assignee-name="{{ optional($job->assignee)->name ?? 'ยังไม่มอบหมายงาน' }}"
                                            data-status="{{ $job->status }}"
                                            data-rating="{{ $job->rating ?? 0 }}"
                                            data-eval-comment="{{ $job->eval_comment ?? '' }}"
                                            data-repair-evaluation="{{ $job->repair_evaluation }}"
                                            data-repair-details="{{ $job->repair_details }}"
                                            data-repair-result="{{ $job->repair_result }}"
                                            data-unrepairable-reason="{{ $job->unrepairable_reason }}"
                                            data-technician-recommendation="{{ $job->technician_recommendation }}"
                                            data-assigned-at="{{ optional($job->latestAssignment)->assign_date ? \Carbon\Carbon::parse(optional($job->latestAssignment)->assign_date)->addYears(543)->format('d/m/Y') : ($job->updated_at ? \Carbon\Carbon::parse($job->updated_at)->addYears(543)->format('d/m/Y H:i น.') : '') }}"
                                            class="px-2.5 py-1 border border-slate-200 text-slate-600 rounded-lg text-[10px] font-bold hover:bg-slate-50 transition flex items-center justify-center gap-1 mx-auto cursor-pointer">
                                        <i class="fa-solid fa-circle-info"></i> ดูรายละเอียด
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @if($requests->count() === 0)
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 font-semibold bg-white">
                                    ไม่มีรายการแจ้งซ่อมเลย
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls (เมื่อมีข้อมูลเพิ่มไม่ต้องเลื่อนลง ให้ทำเป็นหน้าถัดไป) -->
                <div id="head-assign-pagination-container" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 border-t border-slate-100 text-xs bg-slate-50/40">
                    <div class="text-slate-500 font-semibold text-xs" id="head-assign-pagination-info"></div>
                    <div class="flex items-center gap-1.5" id="head-assign-pagination-buttons"></div>
                </div>
            </div>
                </div>
            </div>
        </div>


        <!-- ==================== TAB 3: ประวัติการซ่อมย้อนหลัง ==================== -->
        <div id="tab-history" class="tab-content {{ $isHistoryReq ? 'block' : 'hidden' }} space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
            
            @php
                $myHistory = $requests;

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

                $historyDataList = [];
                $availableYears = [];
                foreach($myHistory as $job) {
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
                        'eval_comment' => $job->eval_comment ?? '',
                        'assignee_name' => $job->assignee->name ?? $job->assignee->first_name ?? $job->assigned_to ?? 'ไม่ระบุ',
                        'assignee_id' => $job->assignee->id ?? $job->assignee->user_id ?? $job->assigned_to ?? '',
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
                window.masterAssetKinds = @json(\App\Models\AssetKind::pluck('name')->map(fn($n) => trim($n))->filter()->values()->all());
            </script>

            <!-- Unified Premium Container -->
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
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- View Mode Switcher -->
                            <div class="inline-flex p-1 bg-white/20 backdrop-blur-md rounded-2xl border border-white/30 shadow-inner">
                                <button type="button" onclick="setHeadHistoryView('chart')" id="btn-view-chart" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-white text-blue-600 shadow-sm cursor-pointer">
                                    <i class="fa-solid fa-chart-pie"></i> ดูแบบกราฟ
                                </button>
                                <button type="button" onclick="setHeadHistoryView('table')" id="btn-view-table" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-white hover:bg-white/15 cursor-pointer">
                                    <i class="fa-solid fa-table-list"></i> ดูเป็นตาราง
                                </button>
                            </div>
                            <button type="button" onclick="exportHistoryPDF()" class="flex items-center gap-1.5 px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 rounded-xl text-xs font-bold text-white transition-all shadow-sm backdrop-blur-sm">
                                <i class="fa-solid fa-file-pdf"></i> พิมพ์ PDF
                            </button>
                            <button type="button" onclick="exportHistoryExcel()" class="flex items-center gap-1.5 px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 rounded-xl text-xs font-bold text-white transition-all shadow-sm backdrop-blur-sm">
                                <i class="fa-solid fa-file-excel"></i> ส่งออก Excel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8 bg-slate-50/30 space-y-6">



                    <!-- 1. ส่วนแสดงผลแบบกราฟสถิติ (Chart View Container) -->
                    <div id="head-history-chart-view" class="space-y-6">
                        <!-- Filter Control Bar -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
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
                                <div class="inline-flex p-1 bg-slate-100/90 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">
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
                                            <input type="text" id="history-filter-date" value="{{ date('d/m/') . (date('Y') + 543) }}" data-date-ymd="{{ date('Y-m-d') }}" onchange="applyHistoryFilter()" placeholder="วว/ดด/ปปปป" class="w-36 text-xs bg-slate-50 border border-slate-200 rounded-xl pl-3 pr-8 py-2 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-xs">
                                            <span class="absolute right-2.5 pointer-events-none text-slate-400 text-xs">
                                                <i class="fa-regular fa-calendar-days"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Month Picker (Thai) -->
                                    <div id="control-month" class="hidden flex items-center gap-1.5">
                                        <select id="history-filter-month" onchange="applyHistoryFilter()"
                                                class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
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
                                                class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                            @for($y = $curY + 5; $y >= 2015; $y--)
                                                <option value="{{ $y }}" {{ $curY === $y ? 'selected' : '' }}>พ.ศ. {{ $y + 543 }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <!-- Year Picker -->
                                    <div id="control-year" class="hidden flex items-center gap-1.5">
                                        <select id="history-filter-year" onchange="applyHistoryFilter()" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @php $currentYear = (int)date('Y'); @endphp
                                            @for($y = $currentYear + 5; $y >= 2015; $y--)
                                                <option value="{{ $y }}" {{ $currentYear === $y ? 'selected' : '' }}>พ.ศ. {{ $y + 543 }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <!-- Reset Button -->
                                <button type="button" onclick="resetHistoryFilter()" title="รีเซ็ตตัวกรอง" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl border border-transparent hover:border-blue-100 transition-colors">
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

                        <!-- Section: การประเมินความพึงพอใจและข้อเสนอแนะแยกตามช่าง -->
                        <div id="head-tech-satisfaction-section" class="mt-6 pt-6 border-t border-slate-100">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm font-bold shadow-xs">
                                        <i class="fa-solid fa-comments"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                            การประเมินความพึงพอใจและข้อเสนอแนะแยกตามช่าง
                                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">ความคิดเห็นจริงจากผู้รับบริการ</span>
                                        </h3>
                                        <p class="text-[11px] text-slate-400 font-medium">เลือกช่างซ่อมบำรุงเพื่อดูคะแนนและข้อเสนอแนะของช่างท่านนั้นๆ</p>
                                    </div>
                                </div>

                                <!-- Filter tabs: ทั้งหมด / มีข้อความข้อเสนอแนะ -->
                                <div class="inline-flex p-0.5 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold self-start sm:self-auto">
                                    <button type="button" onclick="filterHeadFeedbackTextTab('all')" id="btn-feedback-tab-all" class="px-3 py-1.5 rounded-lg transition-all bg-white text-blue-600 shadow-sm font-bold">
                                        ทั้งหมด (<span id="feedback-count-all">{{ $overallRatedCount }}</span>)
                                    </button>
                                    <button type="button" onclick="filterHeadFeedbackTextTab('has_text')" id="btn-feedback-tab-text" class="px-3 py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800">
                                        มีข้อความเสนอแนะ (<span id="feedback-count-text">{{ $overallRatedJobs->filter(fn($r) => !empty(trim($r->eval_comment ?? '')))->count() }}</span>)
                                    </button>
                                </div>
                            </div>

                            <!-- 2 Column Layout: Left = Tech Selector, Right = Feedback Feed -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                                <!-- Left: Technician Selector List (4 Cols) -->
                                <div class="lg:col-span-4 space-y-2.5">
                                    <!-- All Techs Option -->
                                    <button type="button" 
                                            onclick="selectHeadFeedbackTech('all', this)" 
                                            data-tech-id="all" 
                                            class="tech-feedback-selector-btn active-tech-selector w-full text-left p-3.5 rounded-2xl border-2 border-blue-500 bg-blue-50/40 shadow-xs transition-all flex items-center justify-between cursor-pointer group">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm font-black shadow-inner shrink-0">
                                                <i class="fa-solid fa-users"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-black text-slate-800 text-xs truncate group-hover:text-blue-600">ช่างทั้งหมด (รวมทุกคน)</h4>
                                                <p class="text-[10px] text-slate-400 font-bold">คะแนนเฉลี่ยรวม</p>
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="flex items-center gap-1 text-xs font-black text-amber-500 justify-end">
                                                <i class="fa-solid fa-star"></i>
                                                <span class="text-slate-800">{{ $overallAvgRating > 0 ? number_format($overallAvgRating, 2) : '0.00' }}</span>
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-400">{{ $overallRatedCount }} งาน</span>
                                        </div>
                                    </button>

                                    <!-- Individual Tech Cards -->
                                    @foreach($techSatisfactionStats as $tStat)
                                        <button type="button" 
                                                onclick="selectHeadFeedbackTech('{{ $tStat['id'] }}', this)" 
                                                data-tech-id="{{ $tStat['id'] }}" 
                                                data-tech-userid="{{ $tStat['user_id'] }}"
                                                data-tech-name="{{ $tStat['name'] }}"
                                                class="tech-feedback-selector-btn w-full text-left p-3.5 rounded-2xl border-2 border-slate-200/80 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-xs transition-all flex items-center justify-between cursor-pointer group">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-inner shrink-0 overflow-hidden">
                                                    @if(!empty($tStat['avatar']))
                                                        <img src="{{ asset('uploads/profiles/' . $tStat['avatar']) }}" alt="{{ $tStat['name'] }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span>{{ mb_substr($tStat['name'], 0, 1, 'UTF-8') }}</span>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="font-black text-slate-800 text-xs truncate group-hover:text-blue-600" title="{{ $tStat['name'] }}">{{ $tStat['name'] }}</h4>
                                                    <p class="text-[10px] text-slate-400 font-semibold truncate">{{ $tStat['role'] }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="flex items-center gap-1 text-xs font-black text-amber-500 justify-end">
                                                    <i class="fa-solid fa-star"></i>
                                                    <span class="text-slate-800">{{ $tStat['avg_rating'] > 0 ? number_format($tStat['avg_rating'], 2) : '-' }}</span>
                                                </div>
                                                <span class="text-[10px] font-bold text-slate-400">{{ $tStat['rated_count'] }} ประเมิน</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Right: Feedback Feed Stream (8 Cols) -->
                                <div class="lg:col-span-8 bg-slate-50/70 rounded-2xl p-4 sm:p-5 border border-slate-200/70 flex flex-col min-h-[380px]">
                                    <!-- Current Selected Header -->
                                    <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-slate-200/60">
                                        <div class="flex items-center gap-2">
                                            <span id="selected-tech-name-display" class="font-black text-slate-800 text-xs sm:text-sm">ช่างทั้งหมด (รวมทุกคน)</span>
                                            <span id="selected-tech-badge-display" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                                {{ $overallRatedCount }} งานที่ประเมินแล้ว
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-xl border border-amber-200/60">
                                            <i class="fa-solid fa-star text-amber-500"></i>
                                            <span id="selected-tech-score-display">{{ $overallAvgRating > 0 ? number_format($overallAvgRating, 2) : '0.00' }} / 5.0</span>
                                        </div>
                                    </div>

                                    <!-- Feed List Container -->
                                    <div id="tech-feedback-feed-container" class="space-y-3 flex-1 overflow-y-auto max-h-[500px] pr-1 custom-scrollbar">
                                        @php
                                            $allRatedJobsFlat = $requests->filter(fn($r) => (int)($r->rating ?? 0) > 0)->sortByDesc('updated_at');
                                        @endphp
                                        @forelse($allRatedJobsFlat as $rJob)
                                            @php
                                                $jobTechId = $rJob->assigned_to ?? optional($rJob->assignee)->user_id ?? optional($rJob->assignee)->id ?? '';
                                                $jobTechName = $rJob->assignee->name ?? $rJob->assignee->first_name ?? $rJob->assigned_to ?? 'ไม่ระบุช่าง';
                                                $hasText = !empty(trim($rJob->eval_comment ?? ''));
                                                $rRating = (int)($rJob->rating ?? 0);
                                                $rTier = match(true) {
                                                    $rRating === 5 => ['label' => 'ดีเยี่ยม (5/5)', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                                    $rRating === 4 => ['label' => 'ดีมาก (4/5)', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                                    $rRating === 3 => ['label' => 'ปานกลาง (3/5)', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                                    default        => ['label' => 'ควรปรับปรุง (' . $rRating . '/5)', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                                };
                                            @endphp
                                            <div class="feedback-feed-card bg-white rounded-xl p-3.5 sm:p-4 border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all space-y-2.5"
                                                 data-tech-id="{{ $jobTechId }}"
                                                 data-has-text="{{ $hasText ? '1' : '0' }}">
                                                <!-- Card Header -->
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-mono text-blue-600 font-bold text-xs bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100">{{ $rJob->request_code }}</span>
                                                        <span class="text-[11px] font-bold text-slate-700 truncate max-w-[200px]">{{ $rJob->device->name ?? $rJob->item->equipment_name ?? 'อุปกรณ์ไอที' }}</span>
                                                        <span class="text-[10px] text-slate-400 font-medium">| {{ \Carbon\Carbon::parse($rJob->created_at)->addYears(543)->format('d/m/Y') }}</span>
                                                    </div>
                                                    <!-- Stars + Tier -->
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex items-center gap-0.5 text-amber-400 text-xs">
                                                            @for($s = 1; $s <= 5; $s++)
                                                                @if($rRating >= $s)
                                                                    <i class="fa-solid fa-star"></i>
                                                                @else
                                                                    <i class="fa-regular fa-star text-slate-200"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $rTier['bg'] }}">
                                                            {{ $rTier['label'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Feedback Comment Quote (Anonymous requester) -->
                                                <div class="bg-amber-50/40 rounded-xl p-3 border border-amber-100/70">
                                                    @if($hasText)
                                                        <p class="text-xs text-slate-700 italic font-medium leading-relaxed">
                                                            “{{ $rJob->eval_comment }}”
                                                        </p>
                                                    @else
                                                        <p class="text-xs text-slate-400 italic">
                                                            ผู้รับบริการประเมิน {{ $rRating }} ดาว (ไม่มีข้อความเพิ่มเติม)
                                                        </p>
                                                    @endif
                                                </div>

                                                <!-- Card Footer: Assigned Tech & View Detail Button -->
                                                <div class="flex items-center justify-between pt-1 text-[11px]">
                                                    <div class="flex items-center gap-1.5 text-slate-500 font-medium">
                                                        <i class="fa-solid fa-user-gear text-blue-500 text-[10px]"></i>
                                                        <span>ช่างผู้ดูแล: <strong class="text-slate-700">{{ $jobTechName }}</strong></span>
                                                    </div>
                                                    <button type="button" 
                                                            onclick="openHistoryDetail({
                                                                code: '{{ $rJob->request_code }}',
                                                                device: '{{ addslashes($rJob->device->name ?? $rJob->item->equipment_name ?? "อุปกรณ์ไม่ระบุชื่อ") }} (#{{ $rJob->device?->device_code ?? "-" }})',
                                                                status: '{{ $rJob->status }}',
                                                                assignee: '{{ addslashes($jobTechName) }}',
                                                                eval: '{{ addslashes(str_replace(array("\r", "\n"), " ", $rJob->issue_description ?? $rJob->description ?? "-")) }}',
                                                                repair: '{{ addslashes(str_replace(array("\r", "\n"), " ", $rJob->repair_details ?? "-")) }}',
                                                                unrepair_reason: '{{ addslashes(str_replace(array("\r", "\n"), " ", $rJob->unrepairable_reason ?? "-")) }}',
                                                                recommend: '{{ addslashes(str_replace(array("\r", "\n"), " ", $rJob->recommendations ?? $rJob->note ?? "-")) }}',
                                                                rating: '{{ $rRating }}',
                                                                rating_text: '{{ $rRating }} / 5.0',
                                                                eval_comment: '{{ addslashes(str_replace(array("\r", "\n"), " ", $rJob->eval_comment ?? "")) }}',
                                                                image: '{{ $rJob->image ? asset($rJob->image) : "" }}'
                                                            })"
                                                            class="text-blue-600 hover:text-blue-800 font-bold text-[10px] flex items-center gap-1 hover:underline cursor-pointer">
                                                        <span>ดูรายละเอียดงาน</span>
                                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="py-12 text-center text-slate-400">
                                                <i class="fa-regular fa-star text-3xl mb-2 text-slate-300"></i>
                                                <p class="font-bold text-xs">ยังไม่มีข้อมูลการประเมินความพึงพอใจในระบบ</p>
                                            </div>
                                        @endforelse

                                        <!-- Empty State When Filter Yields Nothing -->
                                        <div id="feedback-feed-empty-state" class="hidden py-12 text-center text-slate-400">
                                            <i class="fa-solid fa-comment-slash text-3xl mb-2 text-slate-300"></i>
                                            <p class="font-bold text-xs">ไม่พบข้อมูลการประเมินสำหรับเงื่อนไขที่เลือก</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- end #head-history-chart-view -->

                    <!-- 2. ส่วนแสดงผลแบบตารางรายการ (Table View Container) -->
                    <div id="head-history-table-view" class="space-y-4 hidden">

                        <!-- Table Toolbar (Title & Live Search) -->
                        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shadow-xs">
                                    <i class="fa-solid fa-table-list"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-xs sm:text-sm">ตารางประวัติงานซ่อม</h4>
                                </div>
                            </div>

                            <!-- Live Search Box -->
                            <div class="relative w-full sm:w-72 lg:w-80 shrink-0">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="history-search-input" onkeyup="applyHistoryFilter()" placeholder="ค้นหารหัสแจ้ง, อุปกรณ์, สถานที่, ผู้แจ้ง..." class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                                <button type="button" onclick="clearHistorySearch()" id="btn-clear-search" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                    <!-- รายการประวัติ Table -->
                    <div class="overflow-x-auto w-full max-w-full text-xs border border-slate-200/80 rounded-2xl bg-white shadow-sm custom-scrollbar" style="-webkit-overflow-scrolling: touch;">
                        <table class="w-full text-left border-collapse" id="history-data-table">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider whitespace-nowrap">
                                    <th class="px-4 py-2.5">รหัสการแจ้งซ่อม</th>
                                    <th class="px-4 py-2.5">วันที่แจ้งซ่อม</th>
                                    <th class="px-4 py-2.5">รายการอุปกรณ์</th>
                                    <th class="px-4 py-2.5">อาการ</th>
                                    <th class="px-4 py-2.5">สถานที่</th>
                                    <th class="px-4 py-2.5">เจ้าหน้าที่ซ่อมบำรุง</th>
                                    <th class="px-4 py-2.5 text-center">ความเร่งด่วน</th>
                                    <th class="px-4 py-2.5 text-center">ค่าใช้จ่าย</th>
                                    <th class="px-4 py-2.5 text-center">สถานะ</th>
                                    <th class="px-4 py-2.5 text-center whitespace-nowrap">ความพึงพอใจ</th>
                                    <th class="px-4 py-2.5 whitespace-nowrap">ข้อเสนอแนะ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-[11px]">
                                @if($myHistory->isEmpty())
                                    <tr>
                                        <td colspan="11" class="p-10 text-center text-slate-400">
                                            <div class="text-4xl text-slate-300 mb-3"><i class="fa-solid fa-history"></i></div>
                                            <div class="font-bold text-sm">ไม่มีประวัติงานซ่อมในระบบขณะนี้</div>
                                        </td>
                                    </tr>
                                @else
                                    @foreach($myHistory as $req)
                                        @php
                                            $catKey = $classifyCategory($req);
                                            $repKey = $classifyReparability($req);
                                            $dateObj = \Carbon\Carbon::parse($req->created_at);

                                            $searchKeywords = mb_strtolower(
                                                $req->request_code . ' ' . 
                                                ($req->user->name ?? '') . ' ' .
                                                ($req->device->name ?? '') . ' ' . 
                                                ($req->device?->device_code ?? '') . ' ' . 
                                                ($req->device?->category ?? '') . ' ' . 
                                                ($req->device?->deviceType?->name ?? $req->type ?? '') . ' ' . 
                                                ($req->clean_symptom ?? $req->description ?? '') . ' ' . 
                                                ($req->location ?? '') . ' ' .
                                                ($req->assignee->name ?? '') . ' ' .
                                                ($req->repair_details ?? '') . ' ' . 
                                                ($req->unrepairable_reason ?? '') . ' ' . 
                                                ($req->status ?? '') . ' ' .
                                                ($req->eval_comment ?? '') . ' ' .
                                                ($req->rating ? 'ความพึงพอใจ ' . $req->rating . ' ดาว' : '') . ' ' .
                                                ($catKey === 'hardware' ? 'ฮาร์ดแวร์ hardware' : ($catKey === 'software' ? 'ซอฟต์แวร์ software' : 'เครือข่าย network')) . ' ' .
                                                ($repKey === 'repairable' ? 'สามารถซ่อมได้ ซ่อมเสร็จ' : ($repKey === 'unrepairable_parts' ? 'ซ่อมไม่ได้ ต้องสั่งอะไหล่ อะไหล่' : ($repKey === 'cancelled' ? 'ยกเลิก' : 'รอดำเนินการ กำลังซ่อม รอมอบหมาย'))),
                                                'UTF-8'
                                            );
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 transition history-row cursor-pointer"
                                            data-category="{{ $catKey }}"
                                            data-reparability="{{ $repKey }}"
                                            data-date="{{ $dateObj->format('Y-m-d') }}"
                                            data-year="{{ $dateObj->format('Y') }}"
                                            data-month="{{ (int)$dateObj->format('n') }}"
                                            data-day="{{ (int)$dateObj->format('j') }}"
                                            data-rating="{{ (int)($req->rating ?? 0) }}"
                                            data-tech-id="{{ $req->assigned_to ?? optional($req->assignee)->user_id ?? optional($req->assignee)->id ?? '' }}"
                                            data-keyword="{{ $searchKeywords }}"
                                            onclick="openHistoryDetail({
                                                code: '{{ $req->request_code }}',
                                                device: '{{ addslashes($req->device->name ?? $req->item->equipment_name ?? "อุปกรณ์ไม่ระบุชื่อ") }} (#{{ $req->device?->device_code ?? "-" }})',
                                                status: '{{ $req->status }}',
                                                assignee: '{{ addslashes($req->assignee->name ?? "ยังไม่มอบหมาย") }}',
                                                eval: '{{ addslashes(str_replace(array("\r", "\n"), " ", $req->issue_description ?? $req->description ?? "-")) }}',
                                                repair: '{{ addslashes(str_replace(array("\r", "\n"), " ", $req->repair_details ?? "-")) }}',
                                                unrepair_reason: '{{ addslashes(str_replace(array("\r", "\n"), " ", $req->unrepairable_reason ?? "-")) }}',
                                                recommend: '{{ addslashes(str_replace(array("\r", "\n"), " ", $req->recommendations ?? $req->note ?? "-")) }}',
                                                rating: '{{ (int)($req->rating ?? 0) }}',
                                                rating_text: '{{ (int)($req->rating ?? 0) > 0 ? number_format($req->rating, 1) . " / 5.0" : "-" }}',
                                                eval_comment: '{{ addslashes(str_replace(array("\r", "\n"), " ", $req->eval_comment ?? "")) }}',
                                                image: '{{ $req->image ? asset($req->image) : "" }}'
                                            })">
                                            <td class="py-2.5 px-4">
                                                <span class="font-mono text-blue-600 font-bold">{{ $req->request_code }}</span>
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($req->created_at)->addYears(543)->format('d/m/Y H:i') }} น.
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-800 font-bold">
                                                <div>{{ $req->device->name ?? $req->item->equipment_name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}</div>
                                                <div class="text-[10px] text-slate-400 font-normal">{{ $req->device?->device_code ?? '-' }}</div>
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-500 max-w-[150px] truncate" title="{{ $req->clean_symptom ?: ($req->description ?: '-') }}">
                                                {{ Str::limit($req->clean_symptom ?: ($req->description ?: '-'), 30) }}
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-500 max-w-[130px] truncate" title="{{ $req->location }}">
                                                {{ Str::limit($req->location, 25) }}
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-700 whitespace-nowrap">{{ $req->assignee->name ?? 'ยังไม่มอบหมายงาน' }}</td>
                                            <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                                <span class="px-2 py-0.5 rounded-md font-bold text-[9px] {{ $req->priority === 'ด่วนที่สุด' ? 'bg-red-50 text-red-600' : ($req->priority === 'ด่วน' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                                                    {{ $req->priority }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center text-slate-800 font-bold whitespace-nowrap">฿{{ number_format($req->budget, 0) }}</td>
                                            <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                                @php
                                                    $statusBadgeClass = match($req->status) {
                                                        'เสร็จสิ้น', 'ซ่อมเสร็จสมบูรณ์', 'ซ่อมสำเร็จ' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                        'กำลังดำเนินการ', 'กำลังซ่อม', 'รอรับงาน' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                        'รอมอบหมาย' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                                        'รอเปลี่ยนอะไหล่', 'รออะไหล่', 'รออนุมัติงบประมาณ' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                        'ไม่สามารถซ่อมได้', 'ไม่อนุมัติงบ/ซ่อมไม่ได้' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                                        'ยกเลิก' => 'bg-slate-100 text-slate-500 border border-slate-200',
                                                        default => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                    };
                                                @endphp
                                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] whitespace-nowrap {{ $statusBadgeClass }}">
                                                    {{ $req->status }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                                @if((int)($req->rating ?? 0) > 0)
                                                    <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 font-black text-[10px]">
                                                        <i class="fa-solid fa-star text-amber-500 text-[9px]"></i>
                                                        <span>{{ number_format($req->rating, 1) }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-300 text-[10px] font-normal">-</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-500 max-w-[160px] truncate" title="{{ $req->eval_comment ?? '-' }}">
                                                @if(!empty(trim($req->eval_comment ?? '')))
                                                    <span class="text-slate-700 italic">“{{ Str::limit($req->eval_comment, 24) }}”</span>
                                                @else
                                                    <span class="text-slate-300 text-[10px] font-normal">-</span>
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

                        <!-- Pagination Controls (เมื่อมีข้อมูลเพิ่มไม่ต้องเลื่อนลง ให้ทำเป็นหน้าถัดไป) -->
                        <div id="head-history-pagination-container" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3 border-t border-slate-100 text-xs bg-slate-50/40">
                            <div class="text-slate-500 font-semibold text-xs" id="head-history-pagination-info"></div>
                            <div class="flex items-center gap-1.5" id="head-history-pagination-buttons"></div>
                        </div>
                    </div>
                    </div><!-- end #head-history-table-view -->
                </div>
            </div>
        </div>
    </main>
    </div>

    <!-- Modal มอบหมายงานช่าง -->
    <div id="assign-job-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] w-full max-w-3xl overflow-hidden shadow-2xl border border-white/20 transition-all" id="assign-job-modal-content">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-400 px-6 py-5 relative overflow-hidden">
                <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-lg border border-white/30 shadow-inner">
                            <i class="fa-solid fa-clipboard-user"></i>
                        </div>
                        <h4 class="font-black text-xl text-white tracking-wide" id="assign-modal-title">มอบหมายงานซ่อม</h4>
                    </div>
                    <button type="button" onclick="closeAssignModal()" class="text-white/70 hover:bg-white/20 hover:text-white rounded-full w-8 h-8 flex items-center justify-center transition backdrop-blur-sm">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>
            
            <form id="assign-job-form" method="POST" action="">
                @csrf
                <div class="p-6 md:p-8 space-y-8 bg-slate-50/50">
                    <!-- Summary Card -->
                    <div class="bg-white rounded-2xl p-5 md:p-6 border border-slate-200/60 shadow-sm flex flex-col md:flex-row items-center md:items-start gap-5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-50/50 to-transparent rounded-bl-full -z-0"></div>
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-50 to-blue-100/50 text-blue-600 rounded-2xl flex items-center justify-center shrink-0 border border-blue-100 shadow-inner relative z-10">
                            <i class="fa-solid fa-file-invoice text-2xl"></i>
                        </div>
                        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-5 w-full relative z-10">
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">รหัสการแจ้งซ่อม</p>
                                <p class="text-xs font-bold text-blue-600 bg-blue-50/50 px-2.5 py-1 rounded-lg inline-block border border-blue-100/50 whitespace-nowrap" id="assign-modal-req-code">-</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">ชนิดทรัพย์สิน</p>
                                <p class="text-xs font-normal text-slate-800 py-1" id="assign-modal-device">-</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">ผู้แจ้ง</p>
                                <p class="text-xs font-normal text-slate-800 py-1" id="assign-modal-user">-</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">วันที่แจ้ง</p>
                                <p class="text-xs font-normal text-slate-800 py-1" id="assign-modal-date">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Description & Image in Assign Modal -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-2">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-note-sticky text-amber-500 mr-1"></i>อาการชำรุดที่แจ้ง</p>
                            <p id="assign-modal-desc" class="text-xs text-slate-700 font-medium">-</p>
                        </div>
                        <div id="assign-modal-image-container" class="hidden pt-2 border-t border-slate-200/60">
                            <a id="assign-modal-image-link" href="#" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 font-bold">
                                <i class="fa-solid fa-image"></i> ดูรูปภาพประกอบที่แนบมา
                            </a>
                        </div>
                    </div>
                    
                    <!-- Form Fields -->
                    <div id="assign-form-fields" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="flex items-center justify-between text-slate-700 font-medium text-sm">
                                    <span class="flex items-center gap-2"><i class="fa-solid fa-user-gear text-blue-500"></i> เจ้าหน้าที่ซ่อมบำรุง <span class="text-red-500">*</span></span>
                                    <span class="text-[11px] font-medium text-slate-400"><i class="fa-solid fa-circle-info mr-1"></i>แสดงจำนวนงานในมือ</span>
                                </label>
                                <div class="relative group">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-500 transition-colors"><i class="fa-regular fa-user"></i></span>
                                    <select name="assigned_to" id="assign-technician" onchange="updateTechWorkloadNotice(this)" required class="w-full pl-11 pr-10 py-3.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-700 font-normal text-sm appearance-none shadow-sm hover:border-slate-300 cursor-pointer">
                                        <option value="">เลือกเจ้าหน้าที่ซ่อมบำรุงผู้รับผิดชอบ</option>
                                        @foreach($technicians as $tech)
                                            @php
                                                $jobCount = $techActiveJobsCount[$tech->id] ?? $techActiveJobsCount[$tech->user_id] ?? 0;
                                                $tStatItem = $techSatisfactionStats->firstWhere('id', $tech->id);
                                                $tRating = $tStatItem ? $tStatItem['avg_rating'] : 0;
                                            @endphp
                                            <option value="{{ $tech->id }}" data-count="{{ $jobCount }}" data-name="{{ $tech->name }}">
                                                {{ $tech->name }} (มีงานที่ต้องทำ {{ $jobCount }} งาน{{ $jobCount == 0 ? ' - ว่าง' : '' }}{{ $tRating > 0 ? " | ⭐ {$tRating}" : '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></span>
                                </div>
                                <div id="tech-workload-hint" class="hidden text-xs rounded-xl p-2.5 transition-all font-medium"></div>
                            </div>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-slate-700 font-medium text-sm"><i class="fa-solid fa-calendar-day text-blue-500"></i> วันที่มอบหมายงาน <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 group-focus-within:text-blue-500 transition-colors pointer-events-none"><i class="fa-regular fa-calendar-days"></i></span>
                                    <input type="text" name="start_date" id="assign-start-date" required class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-700 font-normal text-sm uppercase shadow-sm hover:border-slate-300 cursor-pointer" placeholder="วว/ดด/ปปปป">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="priority" value="ปกติ">

                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-slate-700 font-medium text-sm"><i class="fa-solid fa-message text-blue-500"></i> หมายเหตุถึงเจ้าหน้าที่ซ่อมบำรุง</label>
                            <div class="relative group">
                                <textarea name="head_note" id="assign-note" rows="3" class="w-full px-4 py-3.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-slate-700 font-normal text-sm shadow-sm hover:border-slate-300 resize-none placeholder-slate-400" placeholder="ระบุรายละเอียดเพิ่มเติม หรือหมายเหตุสำหรับเจ้าหน้าที่ผู้รับงาน (ถ้ามี)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Details View (Hidden by default) -->
                    <div id="assigned-details" class="hidden mt-6 bg-gradient-to-r from-blue-50 to-indigo-50/50 rounded-2xl p-6 border border-blue-100/60 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-6xl text-blue-500/5 rotate-12"><i class="fa-solid fa-clipboard-check"></i></div>
                        <h5 class="text-sm font-black text-blue-800 mb-4 flex items-center gap-2 relative z-10"><i class="fa-solid fa-clipboard-user text-blue-600"></i> รายละเอียดการมอบหมายงาน</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 relative z-10">
                            <div class="bg-white/60 rounded-xl p-3 border border-white/40">
                                <p class="text-[10px] font-medium text-blue-600 mb-1.5 uppercase tracking-wider">เจ้าหน้าที่ซ่อมบำรุงผู้รับผิดชอบ</p>
                                <p class="text-sm font-normal text-slate-800 flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs shadow-inner"><i class="fa-solid fa-user"></i></span>
                                    <span id="assigned-tech-name">-</span>
                                </p>
                            </div>
                            <div class="bg-white/60 rounded-xl p-3 border border-white/40">
                                <p class="text-[10px] font-medium text-blue-600 mb-1.5 uppercase tracking-wider">เวลาที่มอบหมายงาน</p>
                                <p class="text-sm font-normal text-slate-800 flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs shadow-inner"><i class="fa-regular fa-clock"></i></span>
                                    <span id="assigned-time">-</span>
                                </p>
                            </div>
                        <div class="mt-4 pt-3 border-t border-blue-100/60 flex justify-end">
                            <button type="button" onclick="enableEditAssignment()" class="text-xs bg-white hover:bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-user-pen"></i> เปลี่ยนช่างผู้รับผิดชอบ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

                <div class="p-6 md:px-8 md:py-6 border-t border-slate-100 flex flex-col-reverse sm:flex-row justify-end gap-3 bg-white rounded-b-[2rem]">
                    <button type="button" onclick="closeAssignModal()" class="w-full sm:w-auto px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors">
                        ยกเลิก
                    </button>
                    <button type="submit" id="assign-submit-btn" class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm shadow-[0_0_15px_rgba(37,99,235,0.3)] hover:shadow-[0_0_20px_rgba(37,99,235,0.4)] transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('dashboards.head.modals')
    @include('dashboards.head.scripts')
</body>
</html>
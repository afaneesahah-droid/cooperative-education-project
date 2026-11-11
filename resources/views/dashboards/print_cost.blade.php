<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบบันทึกค่าซ่อม - {{ $repairRequest->request_code ?? 'เอกสาร' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', 'Sarabun', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Top Action Bar (Hidden on Print) */
        .action-bar {
            width: 210mm;
            max-width: 100%;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 10px 20px;
            border-radius: 14px;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        .action-bar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-bar-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .action-bar-desc {
            font-size: 11.5px;
            color: #64748b;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background-color: #1e3a8a;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        .btn-close {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-close:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }

        /* A4 Document Container (Designed to fit on EXACTLY 1 A4 Page) */
        .doc-page {
            width: 210mm;
            min-height: 297mm;
            max-width: 100%;
            background: #ffffff;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.07);
            border-radius: 12px;
            padding: 10mm 15mm 8mm 15mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-sizing: border-box;
        }

        /* Top Header */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
        }

        .header-org-title {
            font-size: 19px;
            font-weight: 800;
            color: #1e3a8a;
            line-height: 1.15;
            letter-spacing: -0.3px;
        }

        .header-org-sub-en {
            font-size: 12.5px;
            font-weight: 700;
            color: #1e3a8a;
            letter-spacing: 0.6px;
            line-height: 1.25;
            margin-top: 1px;
        }

        .header-org-dept {
            font-size: 11px;
            font-weight: 500;
            color: #334155;
            line-height: 1.2;
            margin-top: 1px;
        }

        .header-right {
            display: flex;
            align-items: center;
            border-left: 2px solid #1e3a8a;
            padding-left: 10px;
            height: 42px;
        }

        .header-slogan {
            font-size: 12px;
            font-weight: 700;
            color: #1e3a8a;
            line-height: 1.3;
        }

        /* Title */
        .doc-title-section {
            margin-top: 10px;
            text-align: center;
        }

        .title-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }

        .title-line {
            flex: 1;
            height: 1.5px;
            background-color: #1e3a8a;
        }

        .title-main {
            font-size: 22px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.4px;
            white-space: nowrap;
        }

        .title-sub-1 {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 2px;
        }

        .title-sub-2 {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e3a8a;
            margin-top: 0px;
        }

        /* Ref Box */
        .ref-box {
            margin-top: 10px;
            border: 1.5px solid #93c5fd;
            background-color: #f0f7ff;
            border-radius: 10px;
            padding: 6px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ref-item {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .ref-icon-badge {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            background-color: #1e3a8a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
            flex-shrink: 0;
        }

        .ref-label {
            font-size: 10.5px;
            font-weight: 600;
            color: #64748b;
            line-height: 1.15;
        }

        .ref-val {
            font-size: 14.5px;
            font-weight: 800;
            color: #1e3a8a;
            line-height: 1.2;
            margin-top: 1px;
        }

        .ref-divider {
            width: 1.5px;
            height: 26px;
            background-color: #93c5fd;
            margin: 0 16px;
        }

        /* Section Block */
        .section-block {
            margin-top: 10px;
        }

        .section-header {
            background-color: #1e3a8a;
            color: #ffffff;
            border-radius: 7px 7px 0 0;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            font-weight: 700;
        }

        .section-header i {
            font-size: 13px;
        }

        .table-container {
            border: 1.5px solid #93c5fd;
            border-top: none;
            border-radius: 0 0 9px 9px;
            overflow: hidden;
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        /* Form Details Table (Info Table) */
        .info-table {
            table-layout: fixed;
            width: 100%;
        }

        .info-table td {
            padding: 6px 12px;
            vertical-align: middle;
        }

        .info-label {
            width: 215px !important;
            min-width: 215px !important;
            max-width: 215px !important;
            white-space: nowrap !important;
            overflow: hidden;
            background-color: #f0f7ff;
            color: #1e3a8a;
            font-weight: 700;
            font-size: 12px;
            border-right: 1.5px solid #93c5fd;
            border-bottom: 1.5px solid #e2e8f0;
        }

        .info-value {
            background-color: #ffffff;
            color: #0f172a;
            border-bottom: 1.5px solid #e2e8f0;
            font-weight: 500;
            font-size: 12px;
            word-break: break-word;
        }

        .info-table tr:last-child .info-label,
        .info-table tr:last-child .info-value {
            border-bottom: none;
        }

        /* Expenses Table */
        .expense-table {
            table-layout: fixed;
            width: 100%;
        }

        .expense-table th {
            background-color: #f0f7ff;
            color: #1e3a8a;
            font-weight: 700;
            padding: 6px 10px;
            text-align: center;
            font-size: 12px;
            border-bottom: 1.5px solid #93c5fd;
            border-right: 1.5px solid #93c5fd;
        }

        .expense-table th:last-child {
            border-right: none;
        }

        .expense-table td {
            padding: 6px 12px;
            vertical-align: middle;
            font-size: 12px;
            border-right: 1.5px solid #93c5fd;
            border-bottom: 1.5px solid #93c5fd;
        }

        .expense-table td:last-child {
            border-right: none;
        }

        .expense-row-total td {
            border-bottom: none;
        }

        .total-label-cell {
            background-color: #f0f7ff;
            color: #1e3a8a;
            font-size: 12.5px;
            font-weight: 800;
            text-align: center;
        }

        .total-amount-cell {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 800;
            text-align: center;
        }

        /* Baht Text Box */
        .baht-box {
            margin-top: 8px;
            border: 1.5px solid #93c5fd;
            background-color: #ffffff;
            border-radius: 9px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .baht-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: #1e3a8a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            flex-shrink: 0;
        }

        .baht-label {
            font-size: 10.5px;
            font-weight: 700;
            color: #1e3a8a;
            line-height: 1.15;
            white-space: nowrap;
        }

        .baht-text {
            font-size: 12px;
            font-weight: 600;
            color: #1e3a8a;
            margin-top: 1px;
        }

        .dotted-line {
            color: #94a3b8;
            font-family: monospace;
            letter-spacing: 1px;
            font-size: 11px;
            display: inline-block;
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Footer */
        .doc-footer {
            margin-top: 12px;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            position: relative;
        }

        .footer-left {
            font-size: 9.5px;
            color: #334155;
            font-weight: 500;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px 10px;
            max-width: 68%;
            line-height: 1.35;
            padding-bottom: 2px;
        }

        .footer-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .footer-item i {
            color: #1e3a8a;
            font-size: 10px;
        }

        .footer-sep {
            color: #cbd5e1;
        }

        .footer-right {
            width: 160px;
            height: 55px;
            position: relative;
            flex-shrink: 0;
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
        }

        .footer-building-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Strict Print Settings for 1 Page */
        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm 7mm 5mm 7mm;
            }

            html, body {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                overflow: hidden !important;
            }

            .doc-page {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                height: auto !important;
                max-height: 287mm !important;
                padding: 2mm 5mm !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }

            .no-print {
                display: none !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    @php
        // Prepare display data with safe fallbacks
        $reqCode = $repairRequest->request_code ?? ('REQ-' . (date('Y') + 543) . '-' . str_pad($repairRequest->request_id ?? '1', 4, '0', STR_PAD_LEFT));
        $createdAt = $repairRequest->created_at ? $repairRequest->created_at->format('d/m/Y H:i น.') : (date('d/m/') . (date('Y') + 543) . ' ' . date('H:i น.'));
        
        $userName = optional($repairRequest->user)->name ?? (optional($repairRequest->user)->fullname ?? (Auth::user()->name ?? ''));
        $projectName = optional($repairRequest->repairProject)->name ?? ($repairRequest->project_name ?? 'งบประมาณกลาง');
        $budgetSource = $repairRequest->budget_source ?? (optional($repairRequest->repairProject)->budget_source ?? 'งบประมาณกลาง');
        
        // Format project and budget source
        $projectDisplay = $projectName;
        if (!empty($budgetSource) && $budgetSource !== $projectName) {
            $projectDisplay .= ' (' . $budgetSource . ')';
        }

        // Extract clean symptom / repair detail (เอาเฉพาะอาการที่พบเท่านั้น ตัดวันที่สะดวก, เบอร์โทร, รหัสอุปกรณ์ออก)
        $rawDetail = $repairRequest->problem_description 
            ?? ($repairRequest->description 
            ?? (is_string($repairRequest->details) ? $repairRequest->details : (optional(optional($repairRequest->details)->first())->description ?? '')));
        if (empty($rawDetail) && request()->filled('reason')) {
            $rawDetail = request()->query('reason');
        }
        
        $cleanDetail = $rawDetail;
        if (!empty($cleanDetail) && str_contains($cleanDetail, '|')) {
            $parts = explode('|', $cleanDetail, 2);
            $cleanDetail = trim($parts[1] ?? '');
        }

        // 1. Remove parenthesized metadata (e.g. วันที่สะดวกให้เข้าซ่อม, เบอร์โทรติดต่อ, รหัสอุปกรณ์, etc.)
        $cleanDetail = preg_replace('/\s*\([^)]*(วันที่สะดวก|ช่วงเวลา|เวลาที่สะดวก|เบอร์โทร|โทรศัพท์|รหัสอุปกรณ์|อุปกรณ์ที่พิมพ์เอง)[^)]*\)/u', '', $cleanDetail);
        $cleanDetail = preg_replace('/\s*\(\s*(วันที่|เบอร์|โทร|tel|phone)[^)]*\)/iu', '', $cleanDetail);

        // 2. Remove unparenthesized trailing metadata lines
        $cutPos = mb_strlen($cleanDetail);
        foreach ([
            'ส่วนเวลาที่เสนอราคาค่าซ่อม:',
            'วันที่เสนอราคาค่าซ่อม:',
            'วันที่สะดวกให้เข้าซ่อม:',
            'ช่วงเวลาที่สะดวกให้เข้าซ่อม:',
            'ช่วงเวลาที่สะดวก:',
            'เวลาที่สะดวก:',
            'เบอร์โทรติดต่อผู้แจ้ง:',
            'เบอร์โทรติดต่อ:',
            'เบอร์โทร:',
            'วันที่แจ้งซ่อม:',
            'เวลาแจ้งซ่อม:'
        ] as $label) {
            $pos = mb_strpos($cleanDetail, $label);
            if ($pos !== false && $pos < $cutPos) {
                $cutPos = $pos;
            }
        }
        $cleanDetail = trim(mb_substr($cleanDetail, 0, $cutPos));

        // 3. Remove prefixes like "อาการที่พบ:", "อาการเสีย:", "อาการชำรุด:"
        $cleanDetail = preg_replace('/^\s*(อาการที่พบ|อาการเสีย|อาการชำรุด)\s*[:：]\s*/u', '', $cleanDetail);

        // 4. Normalize whitespace and newlines
        $cleanDetail = preg_replace('/[\r\n\t]+/', ' ', $cleanDetail);
        $cleanDetail = preg_replace('/\s+/', ' ', $cleanDetail);
        $cleanDetail = trim($cleanDetail);

        $repairDetail = $cleanDetail ?: ($repairRequest->clean_symptom ?: '-');
        
        // Assignee / Technician name
        $assigneeName = optional($repairRequest->assignee)->name 
            ?? (optional($repairRequest->latestAssignment)->technician ? optional($repairRequest->latestAssignment->technician)->name : '');
        
        // Approval date
        $approvedDate = '';
        if ($repairRequest->budget_approved || in_array(mb_strtolower(trim($repairRequest->status ?? ''), 'UTF-8'), ['อนุมัติแล้ว', 'เสร็จสิ้น'])) {
            $approvedDate = $repairRequest->updated_at ? $repairRequest->updated_at->format('d/m/Y') : ($repairRequest->created_at ? $repairRequest->created_at->format('d/m/Y') : date('d/m/Y'));
        }

        $totalBudget = (float)($repairRequest->budget ?? 0);
        if ($totalBudget <= 0 && request()->filled('amount')) {
            $totalBudget = (float)request()->query('amount');
        }

        // Parse items if available (รายการค่าใช้จ่าย / ค่าอะไหล่ที่ขอเบิก)
        $itemsList = [];

        // 1. Direct items_json from request query or repairRequest
        $jsonCandidate = request()->query('items_json') ?: ($repairRequest->items_json ?? null);
        if (empty($jsonCandidate)) {
            // Check if repair_details or note starts with JSON
            foreach ([$repairRequest->repair_details ?? '', $repairRequest->note ?? '', $repairRequest->description ?? ''] as $cand) {
                if (is_string($cand) && (str_starts_with(trim($cand), '[') || str_starts_with(trim($cand), '{'))) {
                    $jsonCandidate = trim($cand);
                    break;
                }
            }
        }

        if (!empty($jsonCandidate)) {
            $decoded = json_decode($jsonCandidate, true);
            if (is_array($decoded)) {
                foreach ($decoded as $idx => $it) {
                    $qty = isset($it['qty']) ? (float)$it['qty'] : 1;
                    $price = isset($it['price']) ? (float)$it['price'] : 0;
                    $total = isset($it['total']) ? (float)$it['total'] : ($qty * $price);
                    $itemsList[] = [
                        'index' => count($itemsList) + 1,
                        'name'  => $it['name'] ?? ('รายการที่ ' . ($idx + 1)),
                        'qty'   => $qty,
                        'unit'  => !empty($it['unit']) ? $it['unit'] : 'รายการ',
                        'price' => $price,
                        'total' => $total,
                    ];
                }
            }
        }

        // 2. Parse from items string (e.g. "ค่าเปลี่ยนจอภาพ (1 รายการ @ ฿1,500.00), ซ่อมเมนบอร์ด (1 ชุด @ ฿1,500.00) | ขอเบิกงบซ่อม")
        if (empty($itemsList)) {
            $rawItemsStr = '';
            if (request()->filled('items')) {
                $rawItemsStr = request()->query('items');
            } elseif (!empty($repairRequest->repair_details) && is_string($repairRequest->repair_details)) {
                $rawItemsStr = $repairRequest->repair_details;
            } elseif (request()->filled('reason') && str_contains(request()->query('reason'), '|')) {
                $rawItemsStr = request()->query('reason');
            } elseif (!empty($rawDetail) && is_string($rawDetail) && str_contains($rawDetail, '|')) {
                $rawItemsStr = $rawDetail;
            } elseif (!empty($repairRequest->description) && is_string($repairRequest->description) && str_contains($repairRequest->description, '|')) {
                $rawItemsStr = $repairRequest->description;
            }

            if (!empty($rawItemsStr)) {
                if (str_contains($rawItemsStr, '|')) {
                    $parts = explode('|', $rawItemsStr, 2);
                    $rawItemsStr = trim($parts[0]);
                }

                // Split on commas outside parentheses
                $splitItems = preg_split('/,(?![^(]*\))\s*/u', $rawItemsStr);
                foreach ($splitItems as $rItem) {
                    $itemStr = trim($rItem);
                    if (empty($itemStr) || $itemStr === '-') continue;

                    // Match: Name (qty unit @ ฿price)
                    if (preg_match('/^\s*(.+?)\s*\(\s*([0-9.]+)\s*([^\s@()]+)?\s*@\s*฿?([0-9,.]+)\s*\)\s*$/u', $itemStr, $m)) {
                        $name = trim($m[1]);
                        $qty = (float)$m[2];
                        $unit = !empty($m[3]) ? trim($m[3]) : 'รายการ';
                        $price = (float)str_replace(',', '', $m[4]);
                        $total = $qty * $price;
                    } elseif (preg_match('/^\s*(.+?)\s*@\s*฿?([0-9,.]+)\s*$/u', $itemStr, $m)) {
                        $name = trim($m[1]);
                        $qty = 1;
                        $unit = 'รายการ';
                        $price = (float)str_replace(',', '', $m[2]);
                        $total = $price;
                    } else {
                        // Do not accept if it matches repair symptom or metadata
                        if ($itemStr === $cleanDetail || $itemStr === $repairDetail || $itemStr === ($rawDetail ?? '') 
                            || preg_match('/^(อาการ|เปิดไม่ติด|พัง|ดับ|ชำรุด|จอแตก|เครื่องค้าง|ติดไวรัส)/u', $itemStr)
                            || preg_match('/(วันที่สะดวก|เบอร์โทร|โทรศัพท์)/u', $itemStr)) {
                            continue;
                        }
                        $name = trim($itemStr);
                        $qty = 1;
                        $unit = 'รายการ';
                        $price = ($totalBudget > 0 && count($splitItems) === 1) ? $totalBudget : 0;
                        $total = $price;
                    }

                    $itemsList[] = [
                        'index' => count($itemsList) + 1,
                        'name'  => $name,
                        'qty'   => $qty,
                        'unit'  => $unit,
                        'price' => $price,
                        'total' => $total,
                    ];
                }
            }
        }

        // 3. Check if repairRequest->items relation has actual items
        if (empty($itemsList) && !empty($repairRequest->items) && count($repairRequest->items) > 0) {
            foreach ($repairRequest->items as $it) {
                $customName = $it->item_name ?? ($it->name ?? null);
                if (!empty($customName)) {
                    $price = (float)($it->price ?? $it->total ?? 0);
                    $qty = (float)($it->quantity ?? $it->qty ?? 1);
                    $itemsList[] = [
                        'index' => count($itemsList) + 1,
                        'name'  => $customName,
                        'qty'   => $qty,
                        'unit'  => $it->unit ?? 'รายการ',
                        'price' => $price,
                        'total' => $qty * $price,
                    ];
                }
            }
        }

        // Calculate total budget from items if totalBudget was 0 or multiple items
        $sumItems = array_sum(array_column($itemsList, 'total'));
        if ($sumItems > 0 && ($totalBudget <= 0 || count($itemsList) > 1)) {
            $totalBudget = $sumItems;
        }

        // 4. Fallback if still empty: Single item representing requested expense / parts
        // (Do NOT use symptom/repairDetail 'เปิดไม่ติด' here, this is the purchase/expense items table)
        if (empty($itemsList)) {
            $fallbackName = 'ค่าอะไหล่ / ค่าใช้จ่ายที่ขอเบิก';
            if (!empty($repairRequest->device->name)) {
                $fallbackName = 'ค่าอะไหล่และอุปกรณ์สำหรับ ' . $repairRequest->device->name;
            } elseif (!empty($repairRequest->items->first()->equipment_name)) {
                $fallbackName = 'ค่าอะไหล่และอุปกรณ์สำหรับ ' . $repairRequest->items->first()->equipment_name;
            }

            $itemsList[] = [
                'index' => 1,
                'name'  => $fallbackName,
                'qty'   => 1,
                'unit'  => 'รายการ',
                'price' => $totalBudget,
                'total' => $totalBudget,
            ];
        }

        $bahtText = \App\Models\RepairRequest::thaiBahtText($totalBudget);
    @endphp

    <!-- Action Bar (Hidden when printing) -->
    <div class="action-bar no-print">
        <div class="action-bar-left">
            <i class="fa-solid fa-file-invoice text-blue-800 text-lg"></i>
            <div>
                <div class="action-bar-title">ใบบันทึกค่าซ่อม ({{ $reqCode }})</div>
                <div class="action-bar-desc">จัดรูปแบบพอดีกระดาษ A4 หน้าเดียวเรียบร้อยแล้ว</div>
            </div>
        </div>
        <div class="action-buttons">
            <button type="button" onclick="window.print()" class="btn btn-print">
                <i class="fa-solid fa-print"></i>
                <span>พิมพ์เอกสาร</span>
            </button>
            <button type="button" onclick="window.close(); if(!window.closed) history.back();" class="btn btn-close">
                <i class="fa-solid fa-arrow-left"></i>
                <span>ย้อนกลับ / ปิด</span>
            </button>
        </div>
    </div>

    <!-- Printable A4 Document (Fits 1 Sheet) -->
    <div class="doc-page">
        <div>
            <!-- Top Header -->
            <div class="doc-header">
                <div class="header-left">
                    <img src="{{ asset('images/logo.png') }}" alt="โลโก้วิทยาลัยเทคนิคยะลา" class="header-logo" onerror="this.src='/project2/public/images/logo.png'">
                    <div>
                        <div class="header-org-title">วิทยาลัยเทคนิคยะลา</div>
                        <div class="header-org-sub-en">YALA TECHNICAL COLLEGE</div>
                        <div class="header-org-dept">สำนักงานคณะกรรมการการอาชีวศึกษา กระทรวงศึกษาธิการ</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-slogan">
                        <div>“พัฒนาทักษะ สร้างอาชีพ</div>
                        <div>สู่อนาคตที่ยั่งยืน”</div>
                    </div>
                </div>
            </div>

            <!-- Title -->
            <div class="doc-title-section">
                <div class="title-row">
                    <div class="title-line"></div>
                    <div class="title-main">ใบบันทึกค่าซ่อม</div>
                    <div class="title-line"></div>
                </div>
                <div class="title-sub-1">ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ</div>
                <div class="title-sub-2">วิทยาลัยเทคนิคยะลา</div>
            </div>

            <!-- Ref Box -->
            <div class="ref-box">
                <div class="ref-item">
                    <div class="ref-icon-badge">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <div class="ref-label">เลขที่ใบแจ้งซ่อม</div>
                        <div class="ref-val">{{ $reqCode }}</div>
                    </div>
                </div>
                <div class="ref-divider"></div>
                <div class="ref-item">
                    <div class="ref-icon-badge">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div>
                        <div class="ref-label">วันที่แจ้งซ่อม</div>
                        <div class="ref-val">{{ $createdAt }}</div>
                    </div>
                </div>
            </div>

            <!-- Section 1: ข้อมูลการแจ้งซ่อม -->
            <div class="section-block">
                <div class="section-header">
                    <i class="fa-solid fa-user"></i>
                    <span>ข้อมูลการแจ้งซ่อม</span>
                </div>
                <div class="table-container">
                    <table class="doc-table info-table">
                        <tbody>
                            <tr>
                                <td class="info-label">ชื่อผู้ซื้อ / ผู้แจ้งซ่อม</td>
                                <td class="info-value">
                                    @if(!empty($userName))
                                        {{ $userName }}
                                    @else
                                        <span class="dotted-line">........................................................................................................................</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">ชื่อโครงการ / แหล่งงบประมาณ</td>
                                <td class="info-value">
                                    {{ !empty($projectDisplay) ? $projectDisplay : 'งบประมาณกลาง' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">รายละเอียดการซ่อม / รายการซ่อม</td>
                                <td class="info-value">
                                    @if(!empty($repairDetail))
                                        {{ $repairDetail }}
                                    @else
                                        <span class="dotted-line">........................................................................................................................</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">ผู้ดำเนินการซ่อม / ผู้รับผิดชอบ</td>
                                <td class="info-value">
                                    @if(!empty($assigneeName))
                                        {{ $assigneeName }}
                                    @else
                                        <span class="dotted-line">........................................................................................................................</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">วันที่อนุมัติ</td>
                                <td class="info-value">
                                    @if(!empty($approvedDate))
                                        {{ $approvedDate }}
                                    @else
                                        <span class="dotted-line">........................................................................................................................</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 2: รายการค่าใช้จ่าย / ค่าอะไหล่ที่ขอเบิก -->
            <div class="section-block">
                <div class="section-header">
                    <i class="fa-solid fa-coins"></i>
                    <span>รายการค่าใช้จ่าย / ค่าอะไหล่ที่ขอเบิก</span>
                </div>
                <div class="table-container">
                    <table class="doc-table expense-table">
                        <thead>
                            <tr>
                                <th style="width: 8%; text-align: center;">ลำดับ</th>
                                <th style="width: 44%; text-align: left; padding-left: 12px;">รายละเอียด / อะไหล่ / ค่าซ่อม</th>
                                <th style="width: 10%; text-align: center;">จำนวน</th>
                                <th style="width: 11%; text-align: center;">หน่วย</th>
                                <th style="width: 13%; text-align: right; padding-right: 12px;">ราคา/หน่วย (บาท)</th>
                                <th style="width: 14%; text-align: right; padding-right: 12px;">รวมเงิน (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itemsList as $it)
                                <tr>
                                    <td style="text-align: center; font-weight: 600;">{{ $it['index'] }}</td>
                                    <td style="text-align: left; padding-left: 12px; font-weight: 500;">
                                        @if(!empty($it['name']))
                                            {{ $it['name'] }}
                                        @else
                                            <span class="dotted-line">................................................................................</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center; font-weight: 600;">{{ number_format((float)($it['qty'] ?? 1)) }}</td>
                                    <td style="text-align: center;">{{ $it['unit'] ?? 'รายการ' }}</td>
                                    <td style="text-align: right; padding-right: 12px; font-mono font-weight: 600;">
                                        {{ number_format((float)($it['price'] ?? 0), 2) }}
                                    </td>
                                    <td style="text-align: right; padding-right: 12px; font-weight: 700; color: #1e3a8a; font-size: 12.5px;">
                                        {{ number_format((float)($it['total'] ?? 0), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="expense-row-total">
                                <td colspan="5" class="total-label-cell" style="text-align: right; padding-right: 16px; font-weight: 800;">ยอดเงินที่ขอเบิกรวม</td>
                                <td class="total-amount-cell" style="text-align: right; padding-right: 12px; font-weight: 800;">{{ number_format($totalBudget, 2) }} บาท</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Baht Text -->
                <div class="baht-box">
                    <div class="baht-icon">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <div class="baht-label">จำนวนเงินตัวอักษร</div>
                        <div class="baht-text">
                            @if(!empty($bahtText) && $totalBudget > 0)
                                ( {{ $bahtText }} )
                            @else
                                <span class="dotted-line">................................................................................................................................................................</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="doc-footer">
            <div class="footer-left">
                <span class="footer-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>129 ถ.พิพิธภักดี อ.เมือง จ.ยะลา</span>
                </span>
                <span class="footer-sep">|</span>
                <span class="footer-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>โทรศัพท์ 0-7321-2471 , 0-7321-6769</span>
                </span>
                <span class="footer-sep">|</span>
                <span class="footer-item">
                    <i class="fa-solid fa-fax"></i>
                    <span>โทรสาร. 0-7321-3404</span>
                </span>
            </div>
            <div class="footer-right">
                <img src="{{ asset('images/ytc_building_footer.png') }}" alt="อาคารวิทยาลัยเทคนิคยะลา" class="footer-building-img" onerror="this.src='/project2/public/images/ytc_building_footer.png'">
            </div>
        </div>
    </div>

    @if(request()->query('auto_print') == '1')
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 400);
            });
        </script>
    @endif

</body>
</html>

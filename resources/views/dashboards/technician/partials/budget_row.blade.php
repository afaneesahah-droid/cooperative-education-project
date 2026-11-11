@php
    if ($job->budget_approved) {
        $bClass = "bg-emerald-50 text-emerald-600 border-emerald-200";
        $bText = "อนุมัติแล้ว";
        $bIcon = "fa-check-circle";
    } elseif ($job->status === 'รออนุมัติงบประมาณ') {
        $bClass = "bg-orange-50 text-orange-500 border-orange-200";
        $bText = "รออนุมัติ";
        $bIcon = "fa-clock";
    } elseif ($job->status === 'เสร็จสิ้น') {
        $bClass = "bg-blue-50 text-blue-600 border-blue-200";
        $bText = "ดำเนินการแล้ว";
        $bIcon = "fa-check-circle";
    } else {
        $bClass = "bg-slate-50 text-slate-500 border-slate-200";
        $bText = "รอดำเนินการ";
        $bIcon = "fa-clock";
    }
@endphp
<tr class="hover:bg-slate-50/70 transition">
    <td class="p-4">{{ $job->request_code }}</td>
    <td class="p-4 text-slate-800 font-bold">{{ $job->device->name ?? 'ซ่อมบำรุงทั่วไป' }}</td>
    <td class="p-4 text-slate-500 font-medium text-[11px] max-w-xs truncate">{{ $job->clean_symptom ?: ($job->description ?: '-') }}</td>
    <td class="p-4 text-right font-black text-slate-700">{{ number_format($job->budget, 0) }}</td>
    <td class="p-4 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border {{ $bClass }}">
            <i class="fa-regular {{ $bIcon }}"></i> {{ $bText }}
        </span>
    </td>
    <td class="p-4 text-center text-[11px] text-slate-500 font-medium">{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y') }}</td>
    <td class="p-4 text-center">
        <button type="button" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition flex items-center justify-center mx-auto">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
    </td>
</tr>

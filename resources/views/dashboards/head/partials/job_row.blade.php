<tr class="hover:bg-slate-50/80 transition-colors cursor-pointer"
    data-id="{{ $job->id }}"
    data-code="{{ $job->request_code }}"
    data-device="{{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}"
    data-desc="{{ $job->description }}"
    data-loc="{{ $job->location }}"
    data-user="{{ $job->user->name ?? 'ครู/บุคลากร' }}"
    data-priority="{{ $job->priority }}"
    data-date="{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') }} น."
    data-image="{{ $job->image ? asset($job->image) : '' }}"
    onclick="openAssignModal(this)">
    <td class="p-4">
        <span class="text-blue-600 font-bold text-xs">#{{ $job->request_code }}</span>
    </td>
    <td class="p-4">
        <span class="text-slate-700 font-semibold">{{ $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ' }}</span>
    </td>
    <td class="p-4">
        <span class="text-slate-600 font-medium text-xs">{{ $job->clean_symptom ?: ($job->description ?: '-') }}</span>
    </td>
    <td class="p-4">
        <span class="text-slate-600 font-medium text-xs">{{ $job->location }}</span>
    </td>
    <td class="p-4">
        <span class="text-slate-600 font-medium text-xs">{{ $job->user->name ?? 'บุคลากร' }}</span>
    </td>
    <td class="p-4">
        <span class="text-slate-500 font-medium text-xs">{{ \Carbon\Carbon::parse($job->created_at)->addYears(543)->format('d/m/Y H:i') }} น.</span>
    </td>
    <td class="p-4 text-center">
        <span class="inline-flex items-center justify-center w-full max-w-[100px] gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border
            {{ $job->status === 'รอมอบหมาย' ? 'bg-orange-50 text-orange-600 border-orange-200' : 
               ($job->status === 'กำลังดำเนินการ' || $job->status === 'กำลังซ่อม' ? 'bg-indigo-50 text-indigo-600 border-indigo-200' : 
               ($job->status === 'รอเปลี่ยนอะไหล่' || $job->status === 'รออะไหล่' ? 'bg-purple-50 text-purple-600 border-purple-200' :
               ($job->status === 'ไม่สามารถซ่อมได้' ? 'bg-rose-50 text-rose-600 border-rose-200' :
               ($job->status === 'เสร็จสิ้น' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200')))) }}">
            {{ $job->status }}
        </span>
    </td>
    <td class="p-4 text-center">
        <button type="button" class="px-4 py-1.5 border border-blue-200 text-blue-600 hover:bg-blue-50 rounded-lg text-xs font-bold transition-colors whitespace-nowrap inline-block pointer-events-none">
            {{ $job->status === 'รอมอบหมาย' ? 'มอบหมาย' : 'ดูรายละเอียด' }}
        </button>
    </td>
</tr>

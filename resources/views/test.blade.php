<?php
$job = (object)[
    'id' => 1,
    'request_code' => 'REQ-001',
    'device' => (object)['name' => 'Device "Quote"'],
    'location' => 'Loc',
    'user' => (object)['name' => 'User'],
    'description' => "Desc \n Newline",
    'status' => 'Status',
    'budget' => 0,
    'checked_power' => false,
    'checked_cleaning' => false,
    'checked_ping' => false,
    'repair_hw' => false,
    'repair_sw' => false,
    'repair_net' => false,
    'inspection_result' => 'Insp "Quote"',
    'note' => 'Note "Quote" \''
];
?>
<!DOCTYPE html>
<html>
<body>
<button id="btn1" onclick="openUpdateModal({{ \Illuminate\Support\Js::from([
    'id' => $job->id,
    'code' => $job->request_code,
    'device' => $job->device->name ?? 'อุปกรณ์ไม่ระบุชื่อ',
    'location' => $job->location,
    'reporter' => $job->user->name ?? 'ครู/บุคลากร',
    'description' => $job->description,
    'status' => $job->status,
    'budget' => $job->budget ?? 0,
    'checked_power' => $job->checked_power ? true : false,
    'checked_cleaning' => $job->checked_cleaning ? true : false,
    'checked_ping' => $job->checked_ping ? true : false,
    'repair_hw' => $job->repair_hw ? true : false,
    'repair_sw' => $job->repair_sw ? true : false,
    'repair_net' => $job->repair_net ? true : false,
    'inspection_result' => $job->inspection_result ?? '',
    'note' => $job->note ?? ''
]) }})">Test Button</button>

<script>
function openUpdateModal(data) {
    console.log("Success!", data);
    document.body.innerHTML += "<p id='success'>Success! " + data.note + "</p>";
}
// Try to click the button automatically
try {
    document.getElementById('btn1').click();
} catch (e) {
    document.body.innerHTML += "<p id='error'>Error: " + e.message + "</p>";
}
</script>
</body>
</html>

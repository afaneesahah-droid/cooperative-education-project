<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Device;
use App\Models\RepairRequest;
use App\Models\Position;
use App\Models\Department;
use App\Models\Building;
use App\Models\DeviceType;
use App\Models\RepairType;
use App\Models\PersonnelType;
use App\Models\AssetKind;
use App\Models\DepartmentGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default positions
        $positions = [
            ['position_id' => 'P1', 'position_name' => 'ครู', 'role' => 'teacher'],
            ['position_id' => 'P2', 'position_name' => 'เจ้าหน้าที่ซ่อมบำรุง', 'role' => 'technician'],
            ['position_id' => 'P3', 'position_name' => 'หัวหน้างาน', 'role' => 'head'],
            ['position_id' => 'P4', 'position_name' => 'ผู้บริหาร', 'role' => 'executive'],
            ['position_id' => 'P5', 'position_name' => 'ผู้ดูแลระบบ', 'role' => 'admin'],
        ];
        foreach ($positions as $pos) {
            Position::updateOrCreate(['position_id' => $pos['position_id']], $pos);
        }

        // Seed default departments
        $departments = [
            // resource group
            ['department_id' => 'D1', 'department_name' => 'งานบริหารงานทั่วไป', 'group' => 'resource'],
            ['department_id' => 'D2', 'department_name' => 'งานบุคลากร', 'group' => 'resource'],
            ['department_id' => 'D3', 'department_name' => 'งานการเงิน', 'group' => 'resource'],
            ['department_id' => 'D4', 'department_name' => 'งานบัญชี', 'group' => 'resource'],
            ['department_id' => 'D5', 'department_name' => 'งานพัสดุ', 'group' => 'resource'],
            ['department_id' => 'D6', 'department_name' => 'งานอาคารสถานที่', 'group' => 'resource'],
            ['department_id' => 'D7', 'department_name' => 'งานทะเบียน', 'group' => 'resource'],
            ['department_id' => 'D8', 'department_name' => 'งานประชาสัมพันธ์', 'group' => 'resource'],
            // planning group
            ['department_id' => 'D9', 'department_name' => 'งานวางแผนและงบประมาณ', 'group' => 'planning'],
            ['department_id' => '10', 'department_name' => 'งานศูนย์ข้อมูลสารสนเทศ', 'group' => 'planning'],
            ['department_id' => '11', 'department_name' => 'งานความร่วมมือ', 'group' => 'planning'],
            ['department_id' => '12', 'department_name' => 'งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ', 'group' => 'planning'],
            ['department_id' => '13', 'department_name' => 'งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์', 'group' => 'planning'],
            ['department_id' => '14', 'department_name' => 'งานประกันคุณภาพและมาตรฐานการศึกษา', 'group' => 'planning'],
            ['department_id' => '15', 'department_name' => 'ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา', 'group' => 'planning'],
            // student group
            ['department_id' => '16', 'department_name' => 'งานกิจกรรมนักเรียนนักศึกษา', 'group' => 'student'],
            ['department_id' => '17', 'department_name' => 'งานครูที่ปรึกษา', 'group' => 'student'],
            ['department_id' => '18', 'department_name' => 'งานปกครอง', 'group' => 'student'],
            ['department_id' => '19', 'department_name' => 'งานแนะแนวอาชีพและการจัดหางาน', 'group' => 'student'],
            ['department_id' => '20', 'department_name' => 'งานสวัสดิการนักเรียน นักศึกษา', 'group' => 'student'],
            ['department_id' => '21', 'department_name' => 'งานโครงการพิเศษและบริการชุมชน', 'group' => 'student'],
            // academic group
            ['department_id' => '22', 'department_name' => 'งานพัฒนาหลักสูตรการเรียนการสอน', 'group' => 'academic'],
            ['department_id' => '23', 'department_name' => 'งานวัดผลและประเมินผล', 'group' => 'academic'],
            ['department_id' => '24', 'department_name' => 'งานวิทยบริการและห้องสมุด', 'group' => 'academic'],
            ['department_id' => '25', 'department_name' => 'งานสื่อการเรียนการสอน', 'group' => 'academic'],
            ['department_id' => '26', 'department_name' => 'แผนกวิชาเครื่องกล', 'group' => 'academic'],
            ['department_id' => '27', 'department_name' => 'แผนกวิชาช่างกลโรงงาน', 'group' => 'academic'],
            ['department_id' => '28', 'department_name' => 'แผนกวิชาเทคนิคการเชื่อมและการผลิต', 'group' => 'academic'],
            ['department_id' => '29', 'department_name' => 'แผนกวิชาช่างไฟฟ้ากำลัง', 'group' => 'academic'],
            ['department_id' => '30', 'department_name' => 'แผนกวิชาช่างก่อสร้างและโยธา', 'group' => 'academic'],
            ['department_id' => '31', 'department_name' => 'แผนกวิชาสถาปัตยกรรม', 'group' => 'academic'],
            ['department_id' => '32', 'department_name' => 'แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร', 'group' => 'academic'],
            ['department_id' => '33', 'department_name' => 'แผนกวิชาคอมพิวเตอร์ธุรกิจ', 'group' => 'academic'],
            ['department_id' => '34', 'department_name' => 'แผนกวิชาสามัญสัมพันธ์', 'group' => 'academic'],
            ['department_id' => '35', 'department_name' => 'แผนกวิชาช่างเทคนิคพื้นฐาน', 'group' => 'academic'],
            ['department_id' => '36', 'department_name' => 'ภาควิชาเทคโนโลยียานยนต์', 'group' => 'academic'],
            ['department_id' => '37', 'department_name' => 'ภาควิชาเทคโนโลยีไฟฟ้า', 'group' => 'academic'],
        ];
        foreach ($departments as $dept) {
            Department::updateOrCreate(['department_id' => $dept['department_id']], $dept);
        }

        // Seed buildings (อาคารภายในวิทยาลัย)
        $buildings = [
            ['building_id' => 'B001', 'building_name' => 'อาคารอำนวยการ ๒ ชั้น'],
            ['building_id' => 'B002', 'building_name' => 'หอประชุมรูปโดม ชั้นเดียว'],
            ['building_id' => 'B003', 'building_name' => 'อาคารทะเบียนวัดผล ชั้นเดียว'],
            ['building_id' => 'B004', 'building_name' => 'อาคารบริการศูนย์ Fix it ถาวร ชั้นเดียว'],
            ['building_id' => 'B005', 'building_name' => 'อาคารสวัสดิการพยาบาล ชั้นเดียว'],
            ['building_id' => 'B006', 'building_name' => 'อาคารผลิตผล ๒ ชั้น'],
            ['building_id' => 'B007', 'building_name' => 'อาคารสำนักงานศูนย์บ่มเพาะผู้ประกอบการ ๔ ชั้น'],
            ['building_id' => 'B008', 'building_name' => 'อาคารวิทยบริการ ๔ ชั้น'],
            ['building_id' => 'B009', 'building_name' => 'อาคารพัสดุ ชั้นเดียว'],
            ['building_id' => 'B010', 'building_name' => 'อาคารประชาสัมพันธ์ ชั้นเดียว'],
            ['building_id' => 'B011', 'building_name' => 'อาคารสำนักงานอาคารสถานที่ ชั้นเดียว'],
            ['building_id' => 'B012', 'building_name' => 'อาคารโรงเก็บเครื่องมือและวัสดุงานอาคาร ชั้นเดียว'],
            ['building_id' => 'B013', 'building_name' => 'อาคารเรียนภาควิชาช่างก่อสร้างและโยธา ๒ ชั้น'],
            ['building_id' => 'B014', 'building_name' => 'อาคารเรียนภาควิชาสถาปัตยกรรม ๒ ชั้น'],
            ['building_id' => 'B015', 'building_name' => 'โรงฝึกงานภาควิชาเครื่องกล ชั้นเดียว'],
            ['building_id' => 'B016', 'building_name' => 'โรงฝึกงานภาควิชาช่างกลโรงงาน ชั้นเดียว'],
            ['building_id' => 'B017', 'building_name' => 'โรงฝึกงานภาควิชาเทคนิคการเชื่อมฯ ชั้นเดียว'],
            ['building_id' => 'B018', 'building_name' => 'โรงฝึกงานภาควิชาช่างก่อสร้าง-โยธา ชั้นเดียว'],
            ['building_id' => 'B019', 'building_name' => 'โรงฝึกงานปูนช่างก่อสร้าง ชั้นเดียว'],
            ['building_id' => 'B020', 'building_name' => 'โรงฝึกงานภาควิชาเทคนิคการเชื่อมและการผลิต ชั้นเดียว'],
            ['building_id' => 'B021', 'building_name' => 'อาคารเรียนภาควิชาอุตสาหกรรมยาง ชั้นเดียว'],
            ['building_id' => 'B022', 'building_name' => 'อาคารเรียนคณะเทคโนโลยีบัณฑิต(ป.ตรี) ๓ ชั้น'],
            ['building_id' => 'B023', 'building_name' => 'โรงฝึกงานไม้ช่างก่อสร้าง ชั้นเดียว'],
            ['building_id' => 'B024', 'building_name' => 'อาคารเรียนภาควิชาเทคโนโลยีคอมพิวเตอร์ ๔ ชั้น'],
            ['building_id' => 'B025', 'building_name' => 'อาคารเรียนแผนกวิชาคอมพิวเตอร์ธุรกิจ ๔ ชั้น'],
            ['building_id' => 'B026', 'building_name' => 'อาคารเรียนภาควิชาสามัญสัมพันธ์ ๔ ชั้น'],
            ['building_id' => 'B027', 'building_name' => 'อาคารเรียนภาควิชาช่างอิเล็กทรอนิกส์ ๔ ชั้น'],
            ['building_id' => 'B028', 'building_name' => 'อาคารเรียนภาควิชาเครื่องกล ๔ ชั้น'],
            ['building_id' => 'B029', 'building_name' => 'โรงฝึกงานภาควิชาอุตสาหกรรมยางฯ ชั้นเดียว'],
            ['building_id' => 'B030', 'building_name' => 'อาคารอเนกประสงค์ (โรงยิม) ชั้นเดียว'],
            ['building_id' => 'B031', 'building_name' => 'อาคารเรียนและฝึกงานภาควิชาช่างไฟฟ้า ๔ ชั้น'],
            ['building_id' => 'B032', 'building_name' => 'อาคารเรียนและปฏิบัติการ ๖ ชั้น'],
        ];
        foreach ($buildings as $bld) {
            list($floors, $description) = $this->parseBuildingInfo($bld['building_name']);
            Building::updateOrCreate(['building_id' => $bld['building_id']], [
                'building_name' => $bld['building_name'],
                'floors'        => $floors,
                'description'   => $description,
            ]);
        }

        // 1. สร้างผู้ใช้งานตาม demoAccounts ใน HTML
        $teacher = User::updateOrCreate([
            'user_id' => '40660001'
        ], [
            'name' => 'นางสาวอามีนะห์ ยะลาพิทักษ์',
            'prefix' => 'นางสาว',
            'first_name' => 'อามีนะห์',
            'last_name' => 'ยะลาพิทักษ์',
            'gender' => 'หญิง',
            'email' => 'teacher@ytc.ac.th',
            'phone' => '081-234-5678',
            'phone_number' => '0812345678',
            'department' => 'แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร',
            'position' => 'ครู',
            'position_id' => 'P1',
            'department_id' => '32',
            'password' => Hash::make('Tec1234'),
            'role' => 'teacher',
            'status' => 'approved'
        ]);

        $technician = User::updateOrCreate([
            'user_id' => '40660002'
        ], [
            'name' => 'นายวิรัตน์ ยินดีดี',
            'prefix' => 'นาย',
            'first_name' => 'วิรัตน์',
            'last_name' => 'ยินดีดี',
            'email' => 'tech@ytc.ac.th',
            'gender' => 'ชาย',
            'phone' => '088-253-1483',
            'phone_number' => '0882531483',
            'department' => 'งานศูนย์ข้อมูลสารสนเทศ',
            'position' => 'เจ้าหน้าที่ซ่อมบำรุง',
            'position_id' => 'P2',
            'department_id' => '10',
            'password' => bcrypt('Tech1234'),
            'role' => 'technician',
            'status' => 'approved',
        ]);

        User::updateOrCreate([
            'user_id' => '40660003'
        ], [
            'name' => 'สมมาตร ศรีสวัดิ์',
            'prefix' => 'นาย',
            'first_name' => 'สมมาตร',
            'last_name' => 'ศรีสวัดิ์',
            'email' => 'head@ytc.ac.th',
            'gender' => 'ชาย',
            'phone' => '082-948-2531',
            'phone_number' => '0829482531',
            'department' => 'งานศูนย์ข้อมูลสารสนเทศ',
            'position' => 'หัวหน้างาน',
            'position_id' => 'P3',
            'department_id' => '10',
            'password' => bcrypt('Head1234'),
            'role' => 'head',
            'status' => 'approved',
        ]);

        User::updateOrCreate([
            'user_id' => '40660004'
        ], [
            'name' => 'สมชาย เจริญเลิศ',
            'prefix' => 'นาย',
            'first_name' => 'สมชาย',
            'last_name' => 'เจริญเลิศ',
            'email' => 'executive@ytc.ac.th',
            'gender' => 'ชาย',
            'phone' => '083-456-7890',
            'phone_number' => '0834567890',
            'department' => 'งานศูนย์ข้อมูลสารสนเทศ',
            'position' => 'ผู้บริหาร',
            'position_id' => 'P4',
            'department_id' => '10',
            'password' => bcrypt('Exe1234'),
            'role' => 'executive',
            'status' => 'approved',
        ]);
        
        User::updateOrCreate([
            'user_id' => 'admin'
        ], [
            'name' => 'อิมรอน สือแม',
            'prefix' => 'นาย',
            'first_name' => 'อิมรอน',
            'last_name' => 'สือแม',
            'email' => 'imron@ytc.ac.th',
            'gender' => 'ชาย',
            'phone' => '084-567-8901',
            'phone_number' => '0845678901',
            'department' => 'งานศูนย์ข้อมูลสารสนเทศ',
            'position' => 'ผู้ดูแลระบบ',
            'position_id' => 'P5',
            'department_id' => '10',
            'password' => bcrypt('Admin1234'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        // 2. สร้างข้อมูลอุปกรณ์ (Devices)
        $devices = [
            ['equipment_id' => 'DEV01', 'equipment_name' => 'เครื่องคอมพิวเตอร์ตั้งโต๊ะ HP ProDesk', 'device_code' => 'DEV-001', 'serial' => 'YTC-IT-PC001/69', 'name' => 'เครื่องคอมพิวเตอร์ตั้งโต๊ะ HP ProDesk', 'type' => 'คอมพิวเตอร์ตั้งโต๊ะ (PC)', 'spec' => 'Core i5, RAM 16GB, SSD 512GB (ห้อง 204)', 'status' => 'สามารถยืมใช้งานได้'],
            ['equipment_id' => 'DEV02', 'equipment_name' => 'เครื่องพิมพ์เครือข่าย HP LaserJet Pro', 'device_code' => 'DEV-002', 'serial' => 'YTC-IT-PR002/69', 'name' => 'เครื่องพิมพ์เครือข่าย HP LaserJet Pro', 'type' => 'เครื่องพิมพ์ (Printer)', 'spec' => 'ห้องธุรการ ตึกวิทยบริการ', 'status' => 'สามารถยืมใช้งานได้'],
            ['equipment_id' => 'DEV03', 'equipment_name' => 'เครื่องแม่ข่าย Dell PowerEdge R750', 'device_code' => 'DEV-003', 'serial' => 'YTC-IT-SR003/69', 'name' => 'เครื่องแม่ข่าย Dell PowerEdge R750', 'type' => 'เซิร์ฟเวอร์ (Server)', 'spec' => 'ห้องเซิร์ฟเวอร์หลักวิทยาลัย', 'status' => 'สามารถยืมใช้งานได้'],
            ['equipment_id' => 'DEV04', 'equipment_name' => 'สวิตช์แกนเครือข่าย Cisco Catalyst 2960', 'device_code' => 'DEV-004', 'serial' => 'YTC-IT-SW004/69', 'name' => 'สวิตช์แกนเครือข่าย Cisco Catalyst 2960', 'type' => 'อุปกรณ์เครือข่าย (Router/Switch)', 'spec' => 'ตู้ Rack ชั้น 2 ห้อง 204', 'status' => 'สามารถยืมใช้งานได้'],
            ['equipment_id' => 'DEV05', 'equipment_name' => 'เครื่องสำรองไฟ APC Smart-UPS 1500VA', 'device_code' => 'DEV-005', 'serial' => 'YTC-IT-UP005/69', 'name' => 'เครื่องสำรองไฟ APC Smart-UPS 1500VA', 'type' => 'เครื่องสำรองไฟ (UPS)', 'spec' => 'ตู้ห้องมั่นคงระบบสารสนเทศ', 'status' => 'สามารถยืมใช้งานได้'],
        ];

        foreach ($devices as $d) {
            Device::updateOrCreate(['equipment_id' => $d['equipment_id']], $d);
        }

        // 3. สร้างข้อมูลใบแจ้งซ่อม (Repair Requests)
        if (RepairRequest::count() === 0) {
            RepairRequest::create([
                'request_id' => 'REQ01',
                'request_code' => 'REQ-2569-0001',
                'equipment_id' => 'DEV01',
                'user_id' => $teacher->user_id,
                'location' => 'อาคาร 6 ห้องคอมพิวเตอร์ 204',
                'type' => 'ฮาร์ดแวร์ (Hardware)',
                'description' => 'หน้าจอไม่ส่องสัญญาณภาพ แต่พัดลมเมนบอร์ดหมุนส่งเสียงดังถี่สะดุด',
                'budget' => 1500,
                'status' => 'เสร็จสิ้น',
                'assigned_to' => $technician->user_id,
                'priority' => 'ปกติ',
                'note' => 'ตรวจสอบการเชื่อมขั้วแรมใหม่ และทำการเปลี่ยนพัดลมระบายความร้อนเคสตัวขัดข้อง',
                'budget_approved' => true,
                'rating' => 5,
                'created_at' => now()->subDays(10)
            ]);

            RepairRequest::create([
                'request_id' => 'REQ02',
                'request_code' => 'REQ-2569-0002',
                'equipment_id' => 'DEV04',
                'user_id' => $teacher->user_id,
                'location' => 'ห้องโถงศูนย์ข้อมูล ชั้น 2',
                'type' => 'ระบบเครือข่าย (Network)',
                'description' => 'พอร์ตสวิตช์หลักสายแลนไฟสีส้มกะพริบถี่ผิดปกติ ทำให้เครื่องคอมครูทั้งตึกวิทยบริการหลุดเน็ต',
                'budget' => 3500,
                'status' => 'กำลังดำเนินการ',
                'assigned_to' => $technician->user_id,
                'priority' => 'ด่วนที่สุด',
                'note' => 'ต้องทำการสั่งซื้อพอร์ตการ์ดแปลงไฟและสวิตช์อะไหล่สำหรับชุดแปลงตู้คอนโทรล',
                'budget_approved' => false,
                'rating' => 0,
                'created_at' => now()->subDays(8)
            ]);
        }

        // 4. สร้างข้อมูลประเภททรัพย์สิน (Device Types)
        $deviceTypes = [];
        foreach ($deviceTypes as $dt) {
            DeviceType::updateOrCreate(['name' => $dt['name']], $dt);
        }

        // 5. สร้างข้อมูลประเภทงานซ่อม (Repair Types)
        $repairTypes = [
            ['name' => 'ซ่อมบำรุงฮาร์ดแวร์ (Hardware Repair)', 'description' => 'คอมพิวเตอร์เปิดไม่ติด อะไหล่ชำรุด เปลี่ยนอุปกรณ์ ฮาร์ดดิสก์ แบตเตอรี่ หน้าจอ', 'priority' => 'สูง'],
            ['name' => 'ซอฟต์แวร์และโปรแกรม (Software & OS)', 'description' => 'ติดตั้งโปรแกรมใหม่ ลงระบบปฏิบัติการ Windows/Office แก้ไขไวรัส ปัญหาการใช้งาน', 'priority' => 'ปานกลาง'],
            ['name' => 'ระบบเครือข่ายและอินเทอร์เน็ต (Network & LAN)', 'description' => 'สาย LAN ชำรุด เชื่อมต่อ Wi-Fi ไม่ได้ เข้าใช้อินเทอร์เน็ตหรือระบบเครือข่ายภายในไม่ได้', 'priority' => 'ด่วนที่สุด'],
            ['name' => 'อุปกรณ์ต่อพ่วงและสำนักงาน (Peripherals)', 'description' => 'ซ่อมเครื่องพิมพ์ (Printer) เครื่องสแกนเนอร์ เครื่องฉายโปรเจคเตอร์ เครื่องสำรองไฟ (UPS)', 'priority' => 'ปานกลาง'],
            ['name' => 'บำรุงรักษาเชิงป้องกัน (Preventive Maintenance)', 'description' => 'ตรวจเช็คสภาพเครื่องตามระยะเวลา ทำความสะอาด เป่าฝุ่น จัดการระบบระบายความร้อน', 'priority' => 'ปกติ']
        ];
        foreach ($repairTypes as $rt) {
            RepairType::updateOrCreate(['name' => $rt['name']], $rt);
        }

        // 6. สร้างข้อมูลประเภทบุคลากร (Personnel Types)
        $personnelTypes = [
            ['name' => 'ครู / บุคลากร (Teacher)', 'description' => 'แจ้งซ่อม ยื่นคำขอเบิก ตรวจสอบสถานะ และประเมินความพึงพอใจการบริการ', 'level' => 'Level 1'],
            ['name' => 'ช่างซ่อมบำรุง (Technician)', 'description' => 'จัดการรับงาน ดำเนินการซ่อม อัปเดตประวัติการส่งซ่อม และพิมพ์รายงานใบเสร็จ', 'level' => 'Level 2'],
            ['name' => 'หัวหน้างานศูนย์คอมฯ (Head)', 'description' => 'อนุมัติเบิกพัสดุ มอบหมายช่างซ่อม และตรวจภาพรวมสรุปสถิติทั้งหมด', 'level' => 'Level 3'],
            ['name' => 'ผู้บริหารวิทยาลัย (Executive)', 'description' => 'เรียกดูสรุปภาพรวม รายงานสถิติ รายงานประวัติผลการดำเนินงาน และคำขอเบิกงบ', 'level' => 'Level 2'],
            ['name' => 'ผู้ดูแลระบบ (Admin)', 'description' => 'สิทธิ์ควบคุมเทอร์มินัล ดำเนินงานแก้ไข เพิ่มเติมรายชื่อ สิทธิ์ และคลังครุภัณฑ์', 'level' => 'Level 4']
        ];
        foreach ($personnelTypes as $pt) {
            PersonnelType::updateOrCreate(['name' => $pt['name']], $pt);
        }

        // 7. สร้างข้อมูลหมวดหมู่/ชนิดทรัพย์สิน (Asset Kinds)
        $assetKinds = [];
        foreach ($assetKinds as $ak) {
            AssetKind::updateOrCreate(['category_code' => $ak['category_code'], 'name' => $ak['name']], $ak);
        }

        // 8. สร้างข้อมูลสังกัด/ฝ่ายงาน (Department Groups)
        $departmentGroups = [
            ['group_code' => 'resource', 'group_name' => 'ฝ่ายบริหารทรัพยากร', 'description' => 'ดูแลการเงิน บุคลากร พัสดุ และอาคารสถานที่'],
            ['group_code' => 'planning', 'group_name' => 'ฝ่ายแผนงานและความร่วมมือ', 'description' => 'ดูแลงานศูนย์ข้อมูลสารสนเทศ งานความร่วมมือ และวิจัย'],
            ['group_code' => 'student', 'group_name' => 'ฝ่ายพัฒนากิจการนักเรียน นักศึกษา', 'description' => 'ดูแลกิจกรรม ปกครอง และสวัสดิการนักศึกษา'],
            ['group_code' => 'academic', 'group_name' => 'ฝ่ายวิชาการ', 'description' => 'ดูแลหลักสูตร แผนกวิชา และการเรียนการสอน']
        ];
        foreach ($departmentGroups as $dg) {
            DepartmentGroup::updateOrCreate(['group_code' => $dg['group_code']], $dg);
        }
    }

    /**
     * Helper to parse building name to retrieve floors and description.
     */
    private function parseBuildingInfo(string $rawName): array
    {
        $cleanName = trim(preg_replace([
            '/\s*[0-9๑๒๓๔๕๖๗๘๙]+\s*ชั้น\s*$/u',
            '/\s*ชั้นเดียว\s*$/u',
            '/\s*[0-9๑๒๓๔๕๖๗๘๙]+\s*ชั้น/u',
            '/\s*ชั้นเดียว/u'
        ], '', $rawName));

        $buildingFloorsMap = [
            "อาคารอำนวยการ" => 2,
            "หอประชุมรูปโดม" => 1,
            "อาคารทะเบียนวัดผล" => 1,
            "อาคารบริการศูนย์ Fix it ถาวร" => 1,
            "อาคารสวัสดิการพยาบาลช" => 1,
            "อาคารสวัสดิการพยาบาล" => 1,
            "อาคารผลิตผล" => 2,
            "อาคารสำนักงานศูนย์บ่มเพาะผู้ประกอบการ" => 4,
            "อาคารวิทยบริการ" => 4,
            "อาคารพัสดุ" => 1,
            "อาคารประชาสัมพันธ์" => 1,
            "อาคารสำนักงานอาคารสถานที่" => 1,
            "อาคารโรงเก็บเครื่องมือและวัสดุงานอาคาร" => 1,
            "อาคารอเนกประสงค์ (โรงยิม)" => 1,
            "อาคารเรียนภาควิชาช่างก่อสร้างและโยธา" => 1,
            "อาคารเรียนภาควิชาสถาปัตยกรรม" => 2,
            "อาคารเรียนภาควิชาอุตสาหกรรมยาง" => 1,
            "อาคารเรียนคณะเทคโนโลยีบัณฑิต(ป.ตรี)" => 3,
            "อาคารเรียนภาควิชาเทคโนโลยีคอมพิวเตอร์" => 4,
            "อาคารเรียนแผนกวิชาคอมพิวเตอร์ธุรกิจ" => 4,
            "อาคารเรียนภาควิชาสามัญสัมพันธ์" => 4,
            "อาคารเรียนภาควิชาช่างอิเล็กทรอนิกส์" => 4,
            "อาคารเรียนภาควิชาเครื่องกล" => 4,
            "อาคารเรียนและฝึกงานภาควิชาช่างไฟฟ้า" => 4,
            "อาคารเรียนและปฏิบัติการ" => 6,
            "โรงฝึกงานภาควิชาเครื่องกล" => 1,
            "โรงฝึกงานภาควิชาช่างกลโรงงาน" => 1,
            "โรงฝึกงานภาควิชาเทคนิคการเชื่อมฯ" => 1,
            "โรงฝึกงานภาควิชาช่างก่อสร้าง-โยธา" => 1,
            "โรงฝึกงานปูนช่างก่อสร้าง" => 1,
            "โรงฝึกงานภาควิชาเทคนิคการเชื่อมและการผลิต" => 1,
            "โรงฝึกงานไม้ช่างก่อสร้าง" => 1,
            "โรงฝึกงานภาควิชาอุตสาหกรรมยางฯ" => 1
        ];

        $buildingDescMap = [
            "อาคารอำนวยการ" => "สำนักงานผู้บริหาร งานบุคลากร การเงิน และบริหารงานทั่วไป",
            "หอประชุมรูปโดม" => "หอประชุมจัดกิจกรรม พิธีการ และงานสัมมนา",
            "อาคารทะเบียนวัดผล" => "งานรับสมัครนักศึกษา ออกเอกสารสำคัญ และประมวลผลการเรียน",
            "อาคารบริการศูนย์ Fix it ถาวร" => "ศูนย์บริการซ่อมบำรุงเครื่องมือและอุปกรณ์ให้ชุมชน",
            "อาคารสวัสดิการพยาบาลช" => "ห้องปฐมพยาบาลและบริการสุขภาพเบื้องต้น",
            "อาคารสวัสดิการพยาบาล" => "ห้องปฐมพยาบาลและบริการสุขภาพเบื้องต้น",
            "อาคารผลิตผล" => "พื้นที่จัดแสดงและจำหน่ายผลงานนักศึกษา",
            "อาคารสำนักงานศูนย์บ่มเพาะผู้ประกอบการ" => "ศูนย์ส่งเสริมธุรกิจและที่ปรึกษา Startup นักศึกษา",
            "อาคารวิทยบริการ" => "หอสมุด ศูนย์สืบค้นข้อมูล และห้องคอมพิวเตอร์",
            "อาคารพัสดุ" => "งานจัดซื้อ จัดจ้าง และคลังพัสดุ",
            "อาคารประชาสัมพันธ์" => "จุดต้อนรับและให้บริการข้อมูลข่าวสาร",
            "อาคารสำนักงานอาคารสถานที่" => "งานดูแลอาคาร ซ่อมบำรุง และความปลอดภัย",
            "อาคารโรงเก็บเครื่องมือและวัสดุงานอาคาร" => "คลังเก็บอุปกรณ์เครื่องมือช่างและวัสดุซ่อมบำรุง",
            "อาคารอเนกประสงค์ (โรงยิม)" => "อาคารพลศึกษา แข่งขันกีฬาในร่ม และกิจกรรมสันทนาการ",
            "อาคารเรียนภาควิชาช่างก่อสร้างและโยธา" => "ห้องเรียนทฤษฎีและทดสอบวัสดุก่อสร้าง",
            "อาคารเรียนภาควิชาสถาปัตยกรรม" => "สตูดิโอเขียนแบบและออกแบบสถาปัตยกรรม",
            "อาคารเรียนภาควิชาอุตสาหกรรมยาง" => "ห้องเรียนและปฏิบัติการเคมี-เทคโนโลยยาง",
            "อาคารเรียนคณะเทคโนโลยีบัณฑิต(ป.ตรี)" => "ห้องเรียนและปฏิบัติการระดับปริญญาตรี",
            "อาคารเรียนภาควิชาเทคโนโลยีคอมพิวเตอร์" => "ห้องปฏิบัติการเครือข่ายและฮาร์ดแวร์",
            "อาคารเรียนแผนกวิชาคอมพิวเตอร์ธุรกิจ" => "ห้องปฏิบัติการซอฟต์แวร์และงานสำนักงานดิจิทัล",
            "อาคารเรียนภาควิชาสามัญสัมพันธ์" => "อาคารเรียนวิชาพื้นฐาน (ภาษา วิทยาศาสตร์ คณิตศาสตร์)",
            "อาคารเรียนภาควิชาช่างอิเล็กทรอนิกส์" => "ห้องปฏิบัติการวงจรอิเล็กทรอนิกส์และสื่อสาร",
            "อาคารเรียนภาควิชาเครื่องกล" => "ห้องเรียนทฤษฎียานยนต์และเครื่องยนต์",
            "อาคารเรียนและฝึกงานภาควิชาช่างไฟฟ้า" => "ห้องเรียนและปฏิบัติการติดตั้ง/ควบคุมระบบไฟฟ้า",
            "อาคารเรียนและปฏิบัติการ" => "อาคารเรียนขนาดใหญ่และห้องปฏิบัติการรวม",
            "โรงฝึกงานภาควิชาเครื่องกล" => "โรงฝึกงานปฏิบัติการซ่อมบำรุงเครื่องยนต์",
            "โรงฝึกงานภาควิชาช่างกลโรงงาน" => "โรงฝึกงานเครื่องกลึง มิลลิ่ง และเครื่องมือกล",
            "โรงฝึกงานภาควิชาเทคนิคการเชื่อมฯ" => "โรงฝึกงานปฏิบัติการเชื่อมไฟฟ้า เชื่อมก๊าซ และตัดโลหะ",
            "โรงฝึกงานภาควิชาช่างก่อสร้าง-โยธา" => "โรงฝึกงานสำรวจและโครงสร้างงานก่อสร้าง",
            "โรงฝึกงานปูนช่างก่อสร้าง" => "โรงฝึกงานปฏิบัติการงานปูน ก่ออิฐ และฉาบปูน",
            "โรงฝึกงานภาควิชาเทคนิคการเชื่อมและการผลิต" => "โรงฝึกงานแปรรูปโลหะแผ่นและกระบวนการผลิต",
            "โรงฝึกงานไม้ช่างก่อสร้าง" => "โรงฝึกงานไม้และแปรรูปเฟอร์นิเจอร์",
            "โรงฝึกงานภาควิชาอุตสาหกรรมยางฯ" => "โรงฝึกงานขึ้นรูปและแปรรูปผลิตภัณฑ์ยาง"
        ];

        $floors = 1;
        if (isset($buildingFloorsMap[$cleanName])) {
            $floors = $buildingFloorsMap[$cleanName];
        } else {
            if (mb_strpos($rawName, "๖ ชั้น") !== false || mb_strpos($rawName, "6 ชั้น") !== false || mb_strpos($rawName, "6ชั้น") !== false) {
                $floors = 6;
            } elseif (mb_strpos($rawName, "๔ ชั้น") !== false || mb_strpos($rawName, "4 ชั้น") !== false || mb_strpos($rawName, "4ชั้น") !== false) {
                $floors = 4;
            } elseif (mb_strpos($rawName, "๓ ชั้น") !== false || mb_strpos($rawName, "3 ชั้น") !== false || mb_strpos($rawName, "3ชั้น") !== false) {
                $floors = 3;
            } elseif (mb_strpos($rawName, "๒ ชั้น") !== false || mb_strpos($rawName, "2 ชั้น") !== false || mb_strpos($rawName, "2ชั้น") !== false) {
                $floors = 2;
            } elseif (mb_strpos($rawName, "ชั้นเดียว") !== false || mb_strpos($rawName, "๑ ชั้น") !== false || mb_strpos($rawName, "1 ชั้น") !== false || mb_strpos($rawName, "1ชั้น") !== false) {
                $floors = 1;
            }
        }
        $description = $buildingDescMap[$cleanName] ?? '-';
        return [$floors, $description];
    }
}

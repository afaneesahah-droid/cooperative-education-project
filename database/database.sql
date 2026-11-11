-- =========================================================
-- SQL Dump for Database: S406665005_db
-- Host: student.yru.ac.th
-- Total Tables: 23
-- Generated Date: 2026-09-22 05:06:33
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- Table structure for table `asset_kinds`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `asset_kinds`;
CREATE TABLE `asset_kinds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(255) NOT NULL DEFAULT 'hardware',
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `asset_kinds`
INSERT INTO `asset_kinds` VALUES
('29', 'ฮาร์ดแวร์', 'โน้ตบุ๊ค', NULL, '2026-08-01 17:31:33', '2026-08-01 17:31:33'),
('30', 'ระบบเครือข่าย', 'สายแลน', NULL, '2026-08-01 21:07:23', '2026-08-01 21:07:23'),
('31', 'ซอฟต์แวร์', 'OS ซอฟต์แวร์สำนักงาน', NULL, '2026-08-02 15:58:49', '2026-08-27 02:20:08'),
('32', 'ฮาร์ดแวร์', 'เมาส์', NULL, '2026-08-05 13:54:15', '2026-08-05 13:54:15'),
('33', 'ฮาร์ดแวร์', 'แป้นพิมพ์', NULL, '2026-08-05 13:54:26', '2026-08-05 13:54:26'),
('34', 'ระบบเครือข่าย', 'wifi', NULL, '2026-08-05 20:54:02', '2026-08-19 20:37:59'),
('40', 'IOT', 'IOT', NULL, '2026-08-12 17:47:38', '2026-08-12 17:47:38'),
('41', 'IOT', 'เซ็นเซอร์', NULL, '2026-08-12 17:48:09', '2026-08-12 17:48:09'),
('42', 'ซอฟต์แวร์', 'OS ระบบปฎิบัติการ', NULL, '2026-08-19 20:37:29', '2026-08-27 02:20:08'),
('43', 'ระบบเครือข่าย', 'อุปกรณ์กระจายสัญญาณ', NULL, '2026-08-19 20:38:38', '2026-08-19 20:38:38');

-- ---------------------------------------------------------
-- Table structure for table `assignments`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
  `assignment_id` varchar(5) NOT NULL,
  `request_id` varchar(5) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `assign_date` date DEFAULT NULL,
  `task_description` varchar(255) DEFAULT NULL,
  `task_priority` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `assignments_request_id_foreign` (`request_id`),
  KEY `assignments_user_id_foreign` (`user_id`),
  CONSTRAINT `assignments_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `repair_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `assignments`
INSERT INTO `assignments` VALUES
('A001', 'REQ01', '40660002', '2026-08-25', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.', 'ปกติ', '2026-08-25 17:06:35', '2026-08-25 17:11:55'),
('A002', 'REQ03', '40660002', '2026-08-25', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.', 'ปกติ', '2026-08-25 18:43:48', '2026-08-25 18:44:45'),
('A003', 'REQ17', '40660002', '2026-09-01', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดไม่ติด', 'ปกติ', '2026-09-01 19:45:09', '2026-09-01 19:50:00'),
('A004', 'REQ18', '40660002', '2026-09-01', 'มอบหมายงานซ่อม: อาการที่พบ: ต้องการติดตั้งโปรแกรม', 'ปกติ', '2026-09-01 21:27:45', '2026-09-01 21:31:35'),
('A005', 'REQ19', '40660002', '2026-09-02', 'มอบหมายงานซ่อม: อาการที่พบ: เข้าสู่ระบบไม่ได้', 'ปกติ', '2026-09-02 02:16:54', '2026-09-02 02:23:47'),
('A006', 'REQ21', '40660002', '2026-09-02', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดไฟล์ไม่ได้', 'ปกติ', '2026-09-02 23:07:47', '2026-09-03 03:35:43'),
('A007', 'REQ22', '40660002', '2026-09-03', 'มอบหมายงานซ่อม: อาการที่พบ: เครื่องช้า', 'ปกติ', '2026-09-03 03:36:16', '2026-09-03 03:45:51'),
('A008', 'REQ23', '40660002', '2026-09-03', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดไม่ติด', 'ปกติ', '2026-09-03 13:15:45', '2026-09-14 02:07:40'),
('A009', 'REQ24', '40660002', '2026-09-03', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้', 'ปกติ', '2026-09-03 15:45:48', '2026-09-03 15:58:17'),
('A010', 'REQ27', 'Kachatd', '2026-09-10', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้', 'ปกติ', '2026-09-10 01:12:49', '2026-09-10 04:31:52'),
('A011', 'REQ28', 'Kachatd', '2026-09-15', 'มอบหมายงานซ่อม: อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้', 'ปกติ', '2026-09-15 03:22:12', '2026-09-15 03:27:06'),
('A012', 'REQ29', '40660002', '2026-09-16', 'มอบหมายงานซ่อม: อาการที่พบ: โปรแกรมมีปัญหา/ค้าง', 'ปกติ', '2026-09-16 04:38:42', '2026-09-16 04:48:02');

-- ---------------------------------------------------------
-- Table structure for table `budget_sources`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `budget_sources`;
CREATE TABLE `budget_sources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_key` varchar(255) NOT NULL,
  `source_name` varchar(255) NOT NULL,
  `total_allocated` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fiscal_year` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `budget_sources_source_key_unique` (`source_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `budget_sources`
INSERT INTO `budget_sources` VALUES
('1', 'งบประมาณกลาง', 'งบประมาณกลาง', '2000000.00', '2569', '2026-09-15 04:56:25', '2026-09-15 04:57:31'),
('2', 'งบพัฒนาสถานศึกษา', 'งบพัฒนาสถานศึกษา', '1000000.00', '2569', '2026-09-15 04:56:25', '2026-09-15 04:57:31'),
('3', 'งบเงินรายได้', 'งบเงินรายได้', '500000.00', '2569', '2026-09-15 04:56:25', '2026-09-15 04:57:31'),
('4', 'งบอุดหนุน', 'งบอุดหนุน', '500000.00', '2569', '2026-09-15 04:56:25', '2026-09-15 04:57:31');

-- ---------------------------------------------------------
-- Table structure for table `buildings`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `buildings`;
CREATE TABLE `buildings` (
  `building_id` varchar(5) NOT NULL,
  `room_number` varchar(5) DEFAULT NULL,
  `building_name` varchar(255) NOT NULL,
  `floors` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `department_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `buildings`
INSERT INTO `buildings` VALUES
('A001', '0', 'หอประชุม', '1', NULL, NULL, '2026-08-05 14:15:53', '2026-08-05 14:15:53'),
('B001', '0', 'อาคารอำนวยการ', '2', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:18:41'),
('B003', '0', 'อาคารทะเบียนวัดผล', '1', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:26:30'),
('B007', '0', 'อาคารสำนักงานศูนย์บ่มเพาะผู้ประกอบการ', '4', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:26:49'),
('B008', '0', 'อาคารวิทยบริการ', '4', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:26:55'),
('B009', '0', 'อาคารพัสดุ', '1', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:27:53'),
('B010', '0', 'อาคารประชาสัมพันธ์', '1', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:27:47'),
('B011', '0', 'อาคารสำนักงานอาคารสถานที่', '1', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:27:59'),
('B012', '0', 'อาคารโรงเก็บเครื่องมือและวัสดุงานอาคาร', '1', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:28:06'),
('B013', '0', 'อาคารเรียนภาควิชาช่างก่อสร้างและโยธา', '1', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:28:11'),
('B014', '0', 'อาคารเรียนภาควิชาสถาปัตยกรรม', '2', NULL, NULL, '2026-07-26 15:16:17', '2026-08-01 18:28:18'),
('B021', '0', 'อาคารเรียนภาควิชาอุตสาหกรรมยาง', '1', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:28:36'),
('B024', '0', 'อาคารเรียนภาควิชาเทคโนโลยีคอมพิวเตอร์', '4', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:28:24'),
('B025', '0', 'อาคารเรียนแผนกวิชาคอมพิวเตอร์ธุรกิจ', '4', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:28:29'),
('B026', '0', 'อาคารเรียนภาควิชาสามัญสัมพันธ์', '4', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:28:42'),
('B027', '0', 'อาคารเรียนภาควิชาช่างอิเล็กทรอนิกส์', '4', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:28:47'),
('B028', '0', 'อาคารเรียนภาควิชาเครื่องกล', '4', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:28:53'),
('B031', '0', 'อาคารเรียนและฝึกงานภาควิชาช่างไฟฟ้า', '4', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:29:51'),
('B032', '0', 'อาคารเรียนและปฏิบัติการ', '6', NULL, NULL, '2026-07-26 15:16:18', '2026-08-01 18:29:46');

-- ---------------------------------------------------------
-- Table structure for table `cache`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `cache_locks`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `department_budgets`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `department_budgets`;
CREATE TABLE `department_budgets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department` varchar(255) NOT NULL,
  `fiscal_year` varchar(20) NOT NULL,
  `budget_type` varchar(255) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dept_year_budget_unique` (`department`,`fiscal_year`,`budget_type`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `department_budgets`
INSERT INTO `department_budgets` VALUES
('25', 'ฝ่ายบริหารทรัพยากร', '2569', 'งบประมาณรวม', '4000000.00', NULL, '2026-09-16 02:11:17', '2026-09-16 02:11:17'),
('26', 'ฝ่ายนโยบายและแผน', '2569', 'งบประมาณรวม', '3000000.00', NULL, '2026-09-16 02:11:17', '2026-09-16 02:11:17'),
('27', 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า', '2569', 'งบประมาณรวม', '2100000.00', NULL, '2026-09-16 02:11:17', '2026-09-16 02:11:17'),
('28', 'ฝ่ายวิชาการ', '2569', 'งบประมาณรวม', '3000000.00', NULL, '2026-09-16 02:11:17', '2026-09-16 02:11:17'),
('29', 'ฝ่ายนโยบายและแผน', '2569', 'งบประมาณกลาง', '1500000.00', NULL, '2026-09-16 02:48:45', '2026-09-16 02:48:45'),
('30', 'ฝ่ายนโยบายและแผน', '2569', 'งบพัฒนาสถานศึกษา', '750000.00', NULL, '2026-09-16 02:48:45', '2026-09-16 02:48:45'),
('31', 'ฝ่ายนโยบายและแผน', '2569', 'งบเงินรายได้', '400000.00', NULL, '2026-09-16 02:48:45', '2026-09-16 02:48:45'),
('32', 'ฝ่ายนโยบายและแผน', '2569', 'งบอุดหนุน', '350000.00', NULL, '2026-09-16 02:48:45', '2026-09-16 02:48:45');

-- ---------------------------------------------------------
-- Table structure for table `department_groups`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `department_groups`;
CREATE TABLE `department_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_code` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_groups_group_code_unique` (`group_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `department_groups`
INSERT INTO `department_groups` VALUES
('1', 'resource', 'ฝ่ายบริหารทรัพยากร', NULL, NULL),
('2', 'planning', 'ฝ่ายนโยบายและแผน', NULL, NULL),
('3', 'student', 'ฝ่ายพัฒนานักศึกษาและศิษย์เก่า', NULL, NULL),
('4', 'academic', 'ฝ่ายวิชาการ', NULL, NULL);

-- ---------------------------------------------------------
-- Table structure for table `departments`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `department_id` varchar(2) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `group` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `departments`
INSERT INTO `departments` VALUES
('10', 'งานศูนย์ข้อมูลสารสนเทศ', 'planning', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('11', 'งานความร่วมมือ', 'planning', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('12', 'งานส่งเสริมผลิตผล การค้า และประกอบธุรกิจ', 'planning', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('13', 'งานวิจัย พัฒนา นวัตกรรม และสิ่งประดิษฐ์', 'planning', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('14', 'งานประกันคุณภาพและมาตรฐานการศึกษา', 'planning', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('15', 'ศูนย์ฝึกอบรมวิชาชีพอำเภอยะหา', 'planning', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('16', 'งานกิจกรรมนักเรียนนักศึกษา', 'student', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('17', 'งานครูที่ปรึกษา', 'student', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('18', 'งานปกครอง', 'student', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('19', 'งานแนะแนวอาชีพและการจัดหางาน', 'student', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('20', 'งานสวัสดิการนักเรียน นักศึกษา', 'student', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('21', 'งานโครงการพิเศษและบริการชุมชน', 'student', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('22', 'งานพัฒนาหลักสูตรการเรียนการสอน', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('23', 'งานวัดผลและประเมินผล', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('24', 'งานวิทยบริการและห้องสมุด', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('25', 'งานสื่อการเรียนการสอน', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('26', 'แผนกวิชาเครื่องกล', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('27', 'แผนกวิชาช่างกลโรงงาน', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('28', 'แผนกวิชาเทคนิคการเชื่อมและการผลิต', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('29', 'แผนกวิชาช่างไฟฟ้ากำลัง', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('30', 'แผนกวิชาช่างก่อสร้างและโยธา', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('31', 'แผนกวิชาสถาปัตยกรรม', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('32', 'แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('33', 'แผนกวิชาคอมพิวเตอร์ธุรกิจ', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('34', 'แผนกวิชาสามัญสัมพันธ์', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('35', 'แผนกวิชาช่างเทคนิคพื้นฐาน', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('36', 'ภาควิชาเทคโนโลยียานยนต์', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('37', 'ภาควิชาเทคโนโลยีไฟฟ้า', 'academic', '2026-07-26 15:16:17', '2026-07-26 15:16:17'),
('D1', 'งานบริหารงานทั่วไป', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D2', 'งานบุคลากร', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D3', 'งานการเงิน', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D4', 'งานบัญชี', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D5', 'งานพัสดุ', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D6', 'งานอาคารสถานที่', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D7', 'งานทะเบียน', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D8', 'งานประชาสัมพันธ์', 'resource', '2026-07-26 15:16:16', '2026-07-26 15:16:16'),
('D9', 'งานวางแผนและงบประมาณ', 'planning', '2026-07-26 15:16:16', '2026-07-26 15:16:16');

-- ---------------------------------------------------------
-- Table structure for table `device_types`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `device_types`;
CREATE TABLE `device_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `prefix` varchar(255) DEFAULT NULL,
  `asset_code` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `device_types`
INSERT INTO `device_types` VALUES
('10', 'ฮาร์ดแวร์', 'โน้ตบุ๊ค', NULL, NULL, 'HP', '545', 'เดี่ยวเผะ่อรา', NULL, '2026-08-01 17:33:11', '2026-08-01 17:33:11'),
('38', 'ระบบเครือข่าย', 'สายแลน', NULL, NULL, 'HP', '111', '*', NULL, '2026-08-01 21:24:08', '2026-08-01 21:24:08'),
('77', 'ซอฟต์แวร์', 'OS ซอฟต์แวร์สำนักงาน', NULL, NULL, '12', '45', '*', NULL, '2026-08-02 15:59:06', '2026-08-27 02:20:08'),
('80', 'ระบบเครือข่าย', 'สายแลน', NULL, NULL, '8', '8', '-', NULL, '2026-08-02 16:55:34', '2026-08-02 16:55:34'),
('81', 'ฮาร์ดแวร์', 'โน้ตบุ๊ค', NULL, NULL, 'Apple', 'MacBook Air 15 นิ้ว (ชิป M3)', 'หน้าจอ: 15.3 นิ้ว Liquid Retina Display (2880 x 1864)\nซีพียู (CPU): Apple M3 (CPU 8-core, GPU 10-core)\nแรม (RAM): Unified Memory 8GB / 16GB / 24GB\nความจุ (SSD): 256GB / 512GB / 1TB\nการ์ดจอ (GPU): Integrated Apple M3 GPU (10-core)\nน้ำหนัก: 1.51 กิโลกรัม\nระบบปฏิบัติการ: macOS', NULL, '2026-08-04 20:01:36', '2026-08-04 20:01:36'),
('84', 'IOT', 'เซ็นเซอร์', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-12 17:48:09', '2026-08-12 17:48:09'),
('85', 'ซอฟต์แวร์', 'OS ระบบปฎิบัติการ', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 20:37:29', '2026-08-27 02:20:08'),
('86', 'ระบบเครือข่าย', 'อุปกรณ์กระจายสัญญาณ', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-19 20:38:38', '2026-08-19 20:38:38'),
('87', 'น', 'น', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-21 20:45:02', '2026-08-21 20:45:02');

-- ---------------------------------------------------------
-- Table structure for table `failed_jobs`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `job_batches`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `jobs`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table structure for table `maintenances`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `maintenances`;
CREATE TABLE `maintenances` (
  `maintenance_id` varchar(5) NOT NULL,
  `request_id` varchar(5) NOT NULL,
  `user_id` varchar(8) DEFAULT NULL,
  `start_date` varchar(20) DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `inspection_result` text DEFAULT NULL,
  `repair_evaluation` varchar(255) DEFAULT NULL,
  `repair_details` text DEFAULT NULL,
  `repair_result` varchar(50) DEFAULT NULL,
  `unrepairable_reason` text DEFAULT NULL,
  `checked_power` tinyint(1) NOT NULL DEFAULT 0,
  `checked_cleaning` tinyint(1) NOT NULL DEFAULT 0,
  `checked_ping` tinyint(1) NOT NULL DEFAULT 0,
  `repair_hw` tinyint(1) NOT NULL DEFAULT 0,
  `repair_sw` tinyint(1) NOT NULL DEFAULT 0,
  `repair_net` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`maintenance_id`),
  KEY `maintenances_request_id_foreign` (`request_id`),
  KEY `maintenances_user_id_foreign` (`user_id`),
  CONSTRAINT `maintenances_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `repair_requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `maintenances`
INSERT INTO `maintenances` VALUES
('MNT01', 'REQ17', '40660002', '2026-09-01', '2026-09-01 19:46:31', 'ปุ่ม Power เสีย', 'cannot_repair', NULL, 'ไม่สามารถซ่อมได้', 'ปุ่ม Power เสีย ไม่สามารถซ่อมได้', '0', '0', '0', '0', '0', '0', '2026-09-01 19:45:09', '2026-09-01 19:50:00'),
('MNT02', 'REQ18', '40660002', '2026-09-01', '2026-09-01 21:29:36', 'ไม่มีโปรแกรม PDF', 'can_repair', 'ทำการติดตั้งโปรแกรม PDF', 'เสร็จสิ้น', NULL, '0', '0', '0', '0', '0', '0', '2026-09-01 21:27:45', '2026-09-01 21:31:35'),
('MNT03', 'REQ19', '40660002', '2026-09-02', NULL, 'ลืมรหัสผ่าน แล้วทำซ้ำๆทำให้ระบบล๊อก', 'can_repair', 'ทำการรีเซตรหัสผ่านใหม่', 'เสร็จสิ้น', NULL, '0', '0', '0', '0', '0', '0', '2026-09-02 02:16:54', '2026-09-02 02:23:47'),
('MNT04', 'REQ21', '40660002', '2026-09-02', NULL, 'รัก', 'can_repair', 'ฮาร์ดแวร์', 'เสร็จสิ้น', NULL, '0', '0', '0', '0', '0', '0', '2026-09-02 23:07:47', '2026-09-03 03:35:43'),
('MNT05', 'REQ22', '40660002', '2026-09-03', NULL, 'ติดไวรัส', 'can_repair', 'ติดไวรัส', 'เสร็จสิ้น', NULL, '0', '0', '0', '0', '0', '0', '2026-09-03 03:36:16', '2026-09-03 03:45:51'),
('MNT06', 'REQ23', '40660002', '2026-09-04', NULL, 'จากการตรวจสอบพบว่า สายชาร์จหลวม ทำให้ไม่สามารถเปิดเครื่องได้', 'can_repair', 'ต้องซื้อสายชาร์จใหม่', 'กำลังซ่อม', NULL, '0', '0', '0', '0', '0', '0', '2026-09-03 13:15:45', '2026-09-14 02:07:40'),
('MNT07', 'REQ24', '40660002', '2026-09-03', NULL, 'gkjsk', 'can_repair', 'gkjsk', 'เสร็จสิ้น', NULL, '0', '0', '0', '0', '0', '0', '2026-09-03 15:45:48', '2026-09-03 15:58:17'),
('MNT08', 'REQ27', 'Kachatd', '2026-09-10', NULL, 'เปลี่ยนอุปกรณ์', 'สามารถซ่อมได้', 'เปลี่ยนอุปกรณ์', 'กำลังซ่อม', NULL, '0', '0', '0', '0', '0', '0', '2026-09-10 01:12:49', '2026-09-10 04:31:52'),
('MNT09', 'REQ28', 'Kachatd', '2026-09-15', '2026-09-15 03:25:13', 'fdj', 'สามารถซ่อมได้', 'fdj', 'เสร็จสิ้น', NULL, '0', '0', '0', '0', '0', '0', '2026-09-15 03:22:12', '2026-09-15 03:27:06'),
('MNT10', 'REQ29', '40660002', '2026-09-16', NULL, '1', 'สามารถซ่อมได้', '1', 'รอเปลี่ยนอะไหล่', NULL, '0', '0', '0', '0', '0', '0', '2026-09-16 04:38:42', '2026-09-16 04:48:02');

-- ---------------------------------------------------------
-- Table structure for table `migrations`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations`
INSERT INTO `migrations` VALUES
('1', '0001_01_01_000001_create_cache_table', '1'),
('2', '0001_01_01_000002_create_jobs_table', '1'),
('3', '2026_07_09_000001_create_departments_table', '1'),
('4', '2026_07_09_000002_create_positions_table', '1'),
('5', '2026_07_09_000003_create_users_table', '1'),
('6', '2026_07_09_000004_create_repair_equipments_table', '1'),
('7', '2026_07_09_000005_create_buildings_table', '1'),
('8', '2026_07_09_000006_create_repair_requests_table', '1'),
('9', '2026_07_09_000007_create_repair_items_table', '1'),
('10', '2026_07_09_000008_create_repair_details_table', '1'),
('11', '2026_07_09_000009_create_disbursements_table', '1'),
('12', '2026_07_09_000010_create_assignments_table', '1'),
('13', '2026_07_09_000011_create_maintenances_table', '1'),
('14', '2026_07_09_000012_create_withdrawals_table', '1'),
('15', '2026_07_09_000013_create_approvals_table', '1'),
('16', '2026_07_19_165913_add_role_and_group_to_positions_and_departments', '1'),
('17', '2026_07_21_025243_add_floors_and_description_to_buildings_table', '1'),
('18', '2026_07_24_163741_create_device_types_table', '1'),
('19', '2026_07_24_163751_create_repair_types_table', '1'),
('20', '2026_07_24_163759_create_personnel_types_table', '1'),
('21', '2026_07_27_234737_create_asset_kinds_table', '2'),
('22', '2026_07_27_234757_create_department_groups_table', '2'),
('23', '2026_07_30_052601_create_rooms_table', '3'),
('24', '2026_08_04_202221_add_asset_fields_to_repair_equipments_table', '4'),
('25', '2026_08_11_190110_add_suspend_reason_to_users_table', '5'),
('26', '2026_08_12_141232_add_english_names_to_users_table', '6'),
('27', '2026_08_12_191720_add_software_fields_to_repair_equipments_table', '7'),
('28', '2026_08_12_194001_add_responsible_person_to_repair_equipments_table', '8'),
('29', '2026_08_13_113618_add_citizen_id_to_users_table', '8'),
('30', '2026_08_14_204240_add_plain_password_to_users_table', '9'),
('31', '2026_08_15_183244_add_date_fields_to_repair_equipments_table', '10'),
('32', '2026_08_16_181412_add_missing_asset_fields_to_repair_equipments_table', '11'),
('33', '2026_08_19_215709_create_repair_projects_table', '12'),
('34', '2026_09_03_123840_add_repair_project_id_to_repair_requests_table', '13');

-- ---------------------------------------------------------
-- Table structure for table `personnel_types`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `personnel_types`;
CREATE TABLE `personnel_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `personnel_types`
INSERT INTO `personnel_types` VALUES
('1', 'ผู้บริหาร', '2026-07-27 23:35:48', '2026-08-12 15:56:13'),
('4', 'ครู', '2026-07-27 23:35:48', '2026-08-12 15:56:25'),
('5', 'บุคลากรทางการศึกษา', '2026-07-27 23:35:48', '2026-08-12 15:57:05'),
('6', 'เจ้าหน้าที่', '2026-08-01 18:17:06', '2026-08-01 18:17:24'),
('8', 'เจ้าหน้าทีฝ่ายวิชาการ', '2026-08-13 11:21:23', '2026-08-13 11:21:23'),
('9', 'เจ้าหน้าที่ฝ่ายทรัพยากร', '2026-08-18 08:20:51', '2026-08-18 08:20:51'),
('10', 'ภารโรง', '2026-08-18 08:21:12', '2026-08-18 08:21:12'),
('11', 'เจ้าหน้าที่ฝ่ายธุรการ', '2026-08-18 08:46:43', '2026-08-18 08:46:43');

-- ---------------------------------------------------------
-- Table structure for table `repair_details`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `repair_details`;
CREATE TABLE `repair_details` (
  `repair_detail_id` varchar(5) NOT NULL,
  `request_id` varchar(5) NOT NULL,
  `description` text DEFAULT NULL,
  `request_description` varchar(255) DEFAULT NULL,
  `preferred_date` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`repair_detail_id`),
  KEY `repair_details_request_id_foreign` (`request_id`),
  CONSTRAINT `repair_details_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `repair_requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `repair_details`
INSERT INTO `repair_details` VALUES
('DTL01', 'REQ01', '(อุปกรณ์ที่พิมพ์เอง: A001)\nอาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.\n(วันที่สะดวกให้เข้าซ่อม: 25/08/2026)\n(รหัสอุปกรณ์: SN001)\n(เบอร์โทรติดต่อ: 082-948-2531)', 'อาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.', '25/08/2026', '082-948-2531', NULL, '2026-08-25 17:06:35', '2026-08-25 17:11:55'),
('DTL02', 'REQ03', '(อุปกรณ์ที่พิมพ์เอง: A003)\nอาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.\n(วันที่สะดวกให้เข้าซ่อม: 25/08/2026)\n(รหัสอุปกรณ์: SN003)\n(เบอร์โทรติดต่อ: 082-948-2531)', 'อาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.', '25/08/2026', '082-948-2531', NULL, '2026-08-25 18:43:48', '2026-08-25 18:44:45'),
('DTL03', 'REQ17', 'อาการที่พบ: เปิดไม่ติด\n(วันที่สะดวกให้เข้าซ่อม: 01/09/2026)\n(เบอร์โทรติดต่อ: 0845678901)', 'อาการที่พบ: เปิดไม่ติด', '01/09/2026', '0845678901', NULL, '2026-09-01 19:45:09', '2026-09-01 19:50:00'),
('DTL04', 'REQ18', 'อาการที่พบ: ต้องการติดตั้งโปรแกรม\n(วันที่สะดวกให้เข้าซ่อม: 01/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: ต้องการติดตั้งโปรแกรม', '01/09/2026', '081-234-5678', NULL, '2026-09-01 21:27:45', '2026-09-01 21:31:35'),
('DTL05', 'REQ19', 'อาการที่พบ: เข้าสู่ระบบไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 02/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: เข้าสู่ระบบไม่ได้', '02/09/2026', '081-234-5678', NULL, '2026-09-02 02:16:54', '2026-09-02 02:23:47'),
('DTL06', 'REQ20', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 02/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้', '02/09/2026', '081-234-5678', NULL, '2026-09-02 15:11:33', '2026-09-02 15:11:33'),
('DTL07', 'REQ21', 'อาการที่พบ: เปิดไฟล์ไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 02/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: เปิดไฟล์ไม่ได้', '02/09/2026', '081-234-5678', '-', '2026-09-02 23:07:47', '2026-09-03 03:35:43'),
('DTL08', 'REQ22', 'อาการที่พบ: เครื่องช้า\n(วันที่สะดวกให้เข้าซ่อม: 03/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: เครื่องช้า', '03/09/2026', '081-234-5678', 'แ', '2026-09-03 03:36:16', '2026-09-03 03:45:51'),
('DTL09', 'REQ23', 'อาการที่พบ: เปิดไม่ติด\n(วันที่สะดวกให้เข้าซ่อม: 03/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: เปิดไม่ติด', '03/09/2026', '081-234-5678', NULL, '2026-09-03 13:15:45', '2026-09-14 02:07:40'),
('DTL10', 'REQ24', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 03/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้', '03/09/2026', '081-234-5678', 'efdha', '2026-09-03 15:45:48', '2026-09-03 15:58:17'),
('DTL11', 'REQ25', 'Test repair submission from script', 'Test repair submission from script', NULL, NULL, NULL, '2026-09-08 18:32:14', '2026-09-08 18:32:14'),
('DTL12', 'REQ26', 'อาการที่พบ: เปิดไม่ติด\n(วันที่สะดวกให้เข้าซ่อม: 08/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: เปิดไม่ติด', '08/09/2026', '081-234-5678', NULL, '2026-09-08 18:42:35', '2026-09-08 18:42:35'),
('DTL13', 'REQ27', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 10/09/2026)\n(เบอร์โทรติดต่อ: 083-456-7890)', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้', '10/09/2026', '083-456-7890', 'เปลี่ยนอุปกรณ์', '2026-09-10 01:12:49', '2026-09-10 04:31:52'),
('DTL14', 'REQ28', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 15/09/2026)\n(เบอร์โทรติดต่อ: 083-456-7890)', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้', '15/09/2026', '083-456-7890', 'fdj', '2026-09-15 03:22:12', '2026-09-15 03:27:06'),
('DTL15', 'REQ29', 'อาการที่พบ: โปรแกรมมีปัญหา/ค้าง\n(วันที่สะดวกให้เข้าซ่อม: 16/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', 'อาการที่พบ: โปรแกรมมีปัญหา/ค้าง', '16/09/2026', '081-234-5678', '1', '2026-09-16 04:38:42', '2026-09-16 04:48:02');

-- ---------------------------------------------------------
-- Table structure for table `repair_equipments`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `repair_equipments`;
CREATE TABLE `repair_equipments` (
  `equipment_id` varchar(5) NOT NULL,
  `equipment_name` varchar(40) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `device_code` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `serial` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `spec` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `building_id` varchar(5) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `room_id` varchar(50) DEFAULT NULL,
  `responsible_person` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `license_type` varchar(255) DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `warranty` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  PRIMARY KEY (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `repair_equipments`
INSERT INTO `repair_equipments` VALUES
('E0006', 'แป้นพิมพ์', NULL, 'INV-NB-009', 'แป้นพิมพ์', 'SN88920111A', 'แป้นพิมพ์', 'คีย์บอร์ดแบบมีสาย เชื่อมต่อผ่านพอร์ต USB-A, เลย์เอาต์มาตรฐาน 104 ปุ่ม พร้อมสกรีน ภาษาไทย-อังกฤษ (TH/EN)', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-16 18:25:27', '2026-09-08 18:32:14', 'Dell', 'K120', 'ฮาร์ดแวร์', 'B001', '1', '205', 'นางสาว อาซวานี ยะโกะ', NULL, NULL, NULL, NULL, NULL, 'IT City', '2090.00', '1ปี', '2026-08-17'),
('E0007', 'โน้ตบุ๊ค', NULL, 'INV002', 'โน้ตบุ๊ค', 'SN123456', 'โน้ตบุ๊ค', 'เมาส์ไร้สาย (Wireless Mouse 2.4GHz) เชื่อมต่อผ่าน USB Receiver, ความละเอียด 1000 DPI', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-18 10:23:02', '2026-09-03 03:36:16', 'Logitech', 'M330 Silent Plus', 'ฮาร์ดแวร์', 'B001', '1', '101', 'นาย อิมรอน สือแม', NULL, NULL, NULL, NULL, NULL, 'IT City', '389.88', '6เดือน', '2026-08-18'),
('E0008', 'โน้ตบุ๊ค', NULL, 'INV001', 'โน้ตบุ๊ค', 'SN88920111A', 'โน้ตบุ๊ค', 'CPU Intel Core i5-1235U, RAM 16GB DDR4, SSD 512GB NVMe, จอภาพขนาด 14 นิ้ว FHD', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-19 14:48:35', '2026-09-03 13:15:45', 'Lenovo', 'ExpertBook B1400', 'ฮาร์ดแวร์', 'B001', '1', '101', 'นางสาว อามีนะห์ ยะลาพิทักษ์', NULL, NULL, NULL, NULL, NULL, 'IT City', '39900.00', '3ปี', '2026-07-29'),
('E0009', 'OS ระบบปฎิบัติการ', NULL, '-', 'OS ระบบปฎิบัติการ', '-', 'OS ระบบปฎิบัติการ', 'ระบบปฏิบัติการ Windows 11 Pro แบบ 64-bit สิทธิ์ใช้งานแบบ OEM / FPP รองรับการทำงานเครือข่าย Domain/Active Directory', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-19 15:58:23', '2026-09-02 23:07:47', 'Microsoft', 'Windows 11 Pro', 'ซอฟต์แวร์', 'B001', '1', '205', 'นางสาว อาซวานี ยะโกะ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('E0010', 'เมาส์', NULL, 'Rg', 'เมาส์', 'Gfgshfghry', 'เมาส์', 'Dndjjdjrg', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-20 08:37:37', '2026-09-15 03:22:12', 'Fer', 'Ncghmgdh', 'ฮาร์ดแวร์', 'B001', '1', '205', 'นาย สมชาย เจริญเลิศ', NULL, NULL, NULL, NULL, NULL, 'Safdfd', '12111.00', '1y', '2026-12-12'),
('E0011', 'แป้นพิมพ์', NULL, 'พะ้ำพ', 'แป้นพิมพ์', 'ดำเไพะะำั่', 'แป้นพิมพ์', 'เดำัพ่ำพั่ะ', 'พร้อมใช้งาน', '2026-08-20 08:39:18', '2026-08-20 08:39:18', 'พำ้พเ้', 'กดสยงเ', 'ฮาร์ดแวร์', 'B001', '1', '101', 'นางสาว อามีนะห์ ยะลาพิทักษ์', NULL, NULL, NULL, NULL, NULL, 'ถเำดเำพ', '11111111.00', '1ด', '2026-12-13'),
('E0012', 'เมาส์', NULL, '11', 'เมาส์', 'Aa', 'เมาส์', 'Asqsd', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-20 09:20:18', '2026-08-25 00:58:17', 'Ass', 'Qa', 'ฮาร์ดแวร์', 'B001', '1', '205', 'นาย สมชาย เจริญเลิศ', NULL, NULL, NULL, NULL, NULL, 'Wa', '10000.00', '1 ปี', '2026-12-15'),
('E0013', 'แป้นพิมพ์', NULL, '1', 'แป้นพิมพ์', '1', 'แป้นพิมพ์', '1', 'พร้อมใช้งาน', '2026-08-21 19:23:08', '2026-08-21 19:23:08', '1', '1', 'ฮาร์ดแวร์', 'B001', '1', '101', 'นางสาว อาซวานี ยะโกะ', NULL, NULL, NULL, NULL, NULL, '1', '1.00', '1', '2026-08-25'),
('E0014', 'โน้ตบุ๊ค', NULL, '57df', 'โน้ตบุ๊ค', '5b7d', 'โน้ตบุ๊ค', 'bf75etn5', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-21 20:45:31', '2026-09-10 01:12:49', 'fhd', '754v', 'ฮาร์ดแวร์', 'B001', '1', '101', 'นาย สมชาย เจริญเลิศ', NULL, NULL, NULL, NULL, NULL, '5fbe3', '5.02', '574x', '2026-08-12'),
('E0015', 'โน้ตบุ๊ค', NULL, '1234567', 'โน้ตบุ๊ค', 'SN88920111A', 'โน้ตบุ๊ค', '1', 'ชำรุด/ส่งซ่อมบำรุง', '2026-08-24 14:20:50', '2026-09-02 15:11:33', 'HP', 'ProBook 450', 'ฮาร์ดแวร์', 'B001', '1', '101', 'นางสาว อามีนะห์ ยะลาพิทักษ์', NULL, NULL, NULL, NULL, NULL, 'IT City', '25000.00', '1', '2026-08-18'),
('E0016', 'เมาส์', NULL, 'Q1', 'เมาส์', 'PP1', 'เมาส์', 'เมาส์ดี', 'พร้อมใช้งาน', '2026-08-27 02:49:26', '2026-08-27 02:49:26', 'HP', '1', 'ฮาร์ดแวร์', 'A001', '1', '101', 'นางสาว อามีนะห์ ยะลาพิทักษ์', NULL, NULL, NULL, NULL, NULL, 'IT City', '10000.00', '1 ปี', '2026-08-28');

-- ---------------------------------------------------------
-- Table structure for table `repair_items`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `repair_items`;
CREATE TABLE `repair_items` (
  `item_id` varchar(5) NOT NULL,
  `request_id` varchar(5) NOT NULL,
  `equipment_id` varchar(5) DEFAULT NULL,
  `equipment_name` varchar(100) DEFAULT NULL,
  `equipment_number` varchar(50) DEFAULT NULL,
  `device_type` varchar(100) DEFAULT NULL,
  `building_id` varchar(5) DEFAULT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `repair_items_request_id_foreign` (`request_id`),
  KEY `repair_items_equipment_id_foreign` (`equipment_id`),
  CONSTRAINT `repair_items_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `repair_requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `repair_items`
INSERT INTO `repair_items` VALUES
('ITM01', 'REQ01', NULL, 'คอมพิวเตอร์และอุปกรณ์ (A001)', '01', 'คอมพิวเตอร์', 'A001', 'und', 'A001 1 undefined', '2026-08-25 17:06:35', '2026-08-25 17:11:55'),
('ITM02', 'REQ03', NULL, 'คอมพิวเตอร์และอุปกรณ์ (A003)', '03', 'คอมพิวเตอร์', 'A001', 'und', 'A001 1 undefined', '2026-08-25 18:43:48', '2026-08-25 18:44:45'),
('ITM03', 'REQ17', 'E0007', 'โน้ตบุ๊ค', NULL, 'ฮาร์ดแวร์', 'B001', '-', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง -', '2026-09-01 19:45:09', '2026-09-01 19:50:00'),
('ITM04', 'REQ18', 'E0009', 'OS ระบบปฎิบัติการ', NULL, 'ซอฟต์แวร์', 'B001', '-', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง -', '2026-09-01 21:27:45', '2026-09-01 21:31:35'),
('ITM05', 'REQ19', 'E0009', 'OS ระบบปฎิบัติการ', NULL, 'ซอฟต์แวร์', 'B001', '-', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง -', '2026-09-02 02:16:54', '2026-09-02 02:23:47'),
('ITM06', 'REQ20', 'E0015', 'โน้ตบุ๊ค', NULL, 'ฮาร์ดแวร์', 'B001', '-', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง -', '2026-09-02 15:11:33', '2026-09-02 15:11:33'),
('ITM07', 'REQ21', 'E0009', 'OS ระบบปฎิบัติการ', NULL, 'ซอฟต์แวร์', 'B001', '205', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 205', '2026-09-02 23:07:47', '2026-09-03 03:35:43'),
('ITM08', 'REQ22', 'E0007', 'โน้ตบุ๊ค', NULL, 'ฮาร์ดแวร์', 'B001', '101', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 101', '2026-09-03 03:36:16', '2026-09-03 03:45:51'),
('ITM09', 'REQ23', 'E0008', 'โน้ตบุ๊ค', NULL, 'ฮาร์ดแวร์', 'B001', '101', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 101', '2026-09-03 13:15:45', '2026-09-14 02:07:40'),
('ITM10', 'REQ24', 'E0007', 'โน้ตบุ๊ค', NULL, 'ฮาร์ดแวร์', 'B001', '101', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 101', '2026-09-03 15:45:48', '2026-09-03 15:58:17'),
('ITM11', 'REQ25', 'E0006', 'แป้นพิมพ์', '06', 'ฮาร์ดแวร์', 'B001', '205', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 205', '2026-09-08 18:32:14', '2026-09-08 18:32:14'),
('ITM12', 'REQ26', 'E0007', 'โน้ตบุ๊ค', '02', 'ฮาร์ดแวร์', 'B001', '101', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 101', '2026-09-08 18:42:35', '2026-09-08 18:42:35'),
('ITM13', 'REQ27', 'E0014', 'โน้ตบุ๊ค', 'df', 'ฮาร์ดแวร์', 'B001', '101', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 101', '2026-09-10 01:12:49', '2026-09-10 04:31:52'),
('ITM14', 'REQ28', 'E0010', 'เมาส์', 'Rg', 'ฮาร์ดแวร์', 'B001', '205', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 205', '2026-09-15 03:22:12', '2026-09-15 03:27:06'),
('ITM15', 'REQ29', 'E0009', 'OS ระบบปฎิบัติการ', '-', 'ซอฟต์แวร์', 'B001', '205', 'อาคาร อาคารอำนวยการ ชั้น 1 ห้อง 205', '2026-09-16 04:38:42', '2026-09-16 04:48:02');

-- ---------------------------------------------------------
-- Table structure for table `repair_projects`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `repair_projects`;
CREATE TABLE `repair_projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `responsible_person` varchar(255) NOT NULL,
  `budget` decimal(15,2) NOT NULL,
  `used_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `fiscal_year` varchar(255) DEFAULT NULL,
  `budget_source` varchar(255) DEFAULT 'งบประมาณกลาง',
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `repair_projects_project_code_unique` (`project_code`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `repair_projects`
INSERT INTO `repair_projects` VALUES
('1', '101', 'ซ่อมบำรุง', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '100000.00', '0.00', '2569', 'งบประมาณกลาง', NULL, 'pending', '2026-08-19 22:19:19', '2026-09-16 04:40:44'),
('2', '102', 'ซ่อมบำรุง', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '20000.00', '0.00', '2569', 'งบประมาณกลาง', NULL, 'pending', '2026-08-19 23:01:10', '2026-08-19 23:01:10'),
('3', '103', 'ซ่อมบำรุง', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '200000.00', '0.00', '2568', 'งบประมาณกลาง', NULL, 'pending', '2026-08-20 08:05:19', '2026-08-20 08:05:19'),
('4', '005', 'อาฟานี', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '1000000.00', '0.00', '2569', 'งบประมาณกลาง', NULL, 'pending', '2026-08-21 19:16:46', '2026-08-21 19:16:46'),
('5', '1', 'ซ่อมคอมพิวเตอร์', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '30000.00', '0.00', '2569', 'งบประมาณกลาง', NULL, 'pending', '2026-08-26 15:20:56', '2026-08-26 15:20:56'),
('6', '001', 'ซ่อมอุปกรณ์คอมพิวเตอร์', 'ฝ่ายวิชาการ', 'นาย สมชาย เจริญเลิศ', '100000.00', '0.00', '2569', 'งบประมาณกลาง', NULL, 'pending', '2026-08-27 14:37:28', '2026-08-27 14:37:28'),
('7', '002', 'A', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '100000.00', '0.00', '2569', 'งบประมาณกลาง', NULL, 'pending', '2026-08-27 15:13:47', '2026-08-27 15:13:47'),
('8', '7', 'พ', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '500000.00', '0.00', '2570', 'งบพัฒนาสถานศึกษา', NULL, 'pending', '2026-09-15 04:21:38', '2026-09-15 04:21:38'),
('9', '789', 'ซ่อม', 'ฝ่ายบริหารทรัพยากร', 'นาย สมชาย เจริญเลิศ', '30000.00', '0.00', '2570', 'งบพัฒนาสถานศึกษา', NULL, 'pending', '2026-09-15 19:52:26', '2026-09-15 19:52:26');

-- ---------------------------------------------------------
-- Table structure for table `repair_requests`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `repair_requests`;
CREATE TABLE `repair_requests` (
  `request_id` varchar(5) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `equipment_id` varchar(5) DEFAULT NULL,
  `request_code` varchar(50) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `priority` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `budget_approved` tinyint(1) NOT NULL DEFAULT 0,
  `repair_project_id` bigint(20) unsigned DEFAULT NULL,
  `repair_evaluation` varchar(255) DEFAULT NULL,
  `repair_details` text DEFAULT NULL,
  `repair_result` text DEFAULT NULL,
  `unrepairable_reason` text DEFAULT NULL,
  `technician_recommendation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `repair_requests_user_id_foreign` (`user_id`),
  KEY `repair_requests_equipment_id_foreign` (`equipment_id`),
  CONSTRAINT `repair_requests_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `repair_equipments` (`equipment_id`) ON DELETE SET NULL,
  CONSTRAINT `repair_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `repair_requests`
INSERT INTO `repair_requests` VALUES
('REQ01', '40660003', NULL, 'REQ-2569-0001', '?????', '(อุปกรณ์ที่พิมพ์เอง: A001)\nอาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.\n(วันที่สะดวกให้เข้าซ่อม: 25/08/2026)\n(รหัสอุปกรณ์: SN001)\n(เบอร์โทรติดต่อ: 082-948-2531)', '0.00', 'กำลังซ่อม', 'ปกติ', NULL, '0', NULL, '0', '2026-08-25 17:06:35', '2026-08-25 17:11:55'),
('REQ03', '40660003', NULL, 'REQ-2569-0003', '?????', '(อุปกรณ์ที่พิมพ์เอง: A003)\nอาการที่พบ: เปิดไม่ติด\r\n\r\nช่วงเวลาที่สะดวกให้เข้าซ่อม: 08:30 - 10:30 น.\n(วันที่สะดวกให้เข้าซ่อม: 25/08/2026)\n(รหัสอุปกรณ์: SN003)\n(เบอร์โทรติดต่อ: 082-948-2531)', '0.00', 'กำลังซ่อม', 'ปกติ', NULL, '0', NULL, '0', '2026-08-25 18:43:48', '2026-08-25 18:44:45'),
('REQ17', 'admin', 'E0007', 'REQ-2569-0017', 'โน้ตบุ๊ค', 'อาการที่พบ: เปิดไม่ติด\n(วันที่สะดวกให้เข้าซ่อม: 01/09/2026)\n(เบอร์โทรติดต่อ: 0845678901)', '0.00', 'ไม่สามารถซ่อมได้', 'ปกติ', NULL, '0', NULL, '0', '2026-09-01 19:45:09', '2026-09-01 19:50:00'),
('REQ18', '40660001', 'E0009', 'REQ-2569-0004', 'OS ระบบปฎิบัติการ', 'อาการที่พบ: ต้องการติดตั้งโปรแกรม\n(วันที่สะดวกให้เข้าซ่อม: 01/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'เสร็จสิ้น', 'ปกติ', NULL, '0', NULL, '3', '2026-09-01 21:27:45', '2026-09-01 21:31:35'),
('REQ19', '40660001', 'E0009', 'REQ-2569-0005', 'OS ระบบปฎิบัติการ', 'อาการที่พบ: เข้าสู่ระบบไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 02/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'เสร็จสิ้น', 'ปกติ', NULL, '0', NULL, '0', '2026-09-02 02:16:54', '2026-09-02 02:23:47'),
('REQ20', '40660001', 'E0015', 'REQ-2569-0006', 'โน้ตบุ๊ค', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 02/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'รอมอบหมาย', 'ปกติ', NULL, '0', NULL, '0', '2026-09-02 15:11:33', '2026-09-02 15:11:33'),
('REQ21', '40660001', 'E0009', 'REQ-2569-0007', 'OS ระบบปฎิบัติการ', 'อาการที่พบ: เปิดไฟล์ไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 02/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'เสร็จสิ้น', 'ปกติ', '-', '0', NULL, '0', '2026-09-02 23:07:47', '2026-09-03 03:35:43'),
('REQ22', '40660001', 'E0007', 'REQ-2569-0008', 'โน้ตบุ๊ค', 'อาการที่พบ: เครื่องช้า\n(วันที่สะดวกให้เข้าซ่อม: 03/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'เสร็จสิ้น', 'ปกติ', 'แ', '0', NULL, '0', '2026-09-03 03:36:16', '2026-09-03 03:45:51'),
('REQ23', '40660001', 'E0008', 'REQ-2569-0009', 'โน้ตบุ๊ค', 'อาการที่พบ: เปิดไม่ติด\n(วันที่สะดวกให้เข้าซ่อม: 03/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'กำลังซ่อม', 'ปกติ', NULL, '0', NULL, '0', '2026-09-03 13:15:45', '2026-09-14 02:07:40'),
('REQ24', '40660001', 'E0007', 'REQ-2569-0010', 'โน้ตบุ๊ค', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 03/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'เสร็จสิ้น', 'ปกติ', 'efdha', '0', NULL, '4', '2026-09-03 15:45:48', '2026-09-03 15:58:17'),
('REQ25', '40660001', 'E0006', 'REQ-2569-0011', 'hardware', 'Test repair submission from script', '0.00', 'รอมอบหมาย', 'normal', NULL, '0', NULL, '0', '2026-09-08 18:32:14', '2026-09-08 18:32:14'),
('REQ26', '40660001', 'E0007', 'REQ-2569-0012', 'โน้ตบุ๊ค', 'อาการที่พบ: เปิดไม่ติด\n(วันที่สะดวกให้เข้าซ่อม: 08/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'รอมอบหมาย', 'ปนติ', NULL, '0', NULL, '0', '2026-09-08 18:42:35', '2026-09-08 18:42:35'),
('REQ27', '40660004', 'E0014', 'REQ-2569-0013', 'โน้ตบุ๊ค', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 10/09/2026)\n(เบอร์โทรติดต่อ: 083-456-7890)', '0.00', 'กำลังซ่อม', 'ปกติ', 'เปลี่ยนอุปกรณ์', '0', NULL, '0', '2026-09-10 01:12:49', '2026-09-10 04:31:52'),
('REQ28', '40660004', 'E0010', 'REQ-2569-0014', 'เมาส์', 'อาการที่พบ: เปิดติดแต่ใช้งานไม่ได้\n(วันที่สะดวกให้เข้าซ่อม: 15/09/2026)\n(เบอร์โทรติดต่อ: 083-456-7890)', '0.00', 'เสร็จสิ้น', 'ปกติ', 'fdj', '0', NULL, '0', '2026-09-15 03:22:12', '2026-09-15 03:27:06'),
('REQ29', '40660001', 'E0009', 'REQ-2569-0015', 'OS ระบบปฎิบัติการ', 'อาการที่พบ: โปรแกรมมีปัญหา/ค้าง\n(วันที่สะดวกให้เข้าซ่อม: 16/09/2026)\n(เบอร์โทรติดต่อ: 081-234-5678)', '0.00', 'รอเปลี่ยนอะไหล่', 'ปกติ', '1', '0', NULL, '0', '2026-09-16 04:38:42', '2026-09-16 04:48:02');

-- ---------------------------------------------------------
-- Table structure for table `rooms`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `building_id` varchar(5) NOT NULL,
  `room_code` varchar(50) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `floor` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rooms_building_id_foreign` (`building_id`),
  CONSTRAINT `rooms_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`building_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `rooms`
INSERT INTO `rooms` VALUES
('1', 'B001', '101', 'ห้องปฏิบัติการ', '1', '2026-07-30 05:34:27', '2026-07-30 05:34:27'),
('2', 'B001', '205', 'ห้องเรียน', '1', '2026-07-30 05:34:48', '2026-07-30 05:34:48'),
('4', 'B003', '106', 'คอม', '1', '2026-08-02 16:02:30', '2026-08-02 16:02:30'),
('5', 'A001', '101', 'อันทผาลัม', '1', '2026-08-20 07:46:14', '2026-08-20 07:46:14');

-- ---------------------------------------------------------
-- Table structure for table `users`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` varchar(8) NOT NULL,
  `citizen_id` varchar(13) DEFAULT NULL,
  `name` varchar(30) NOT NULL,
  `prefix` varchar(30) DEFAULT NULL,
  `first_name` varchar(30) DEFAULT NULL,
  `first_name_en` varchar(30) DEFAULT NULL,
  `last_name` varchar(30) DEFAULT NULL,
  `last_name_en` varchar(30) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `phone_number` varchar(10) DEFAULT NULL,
  `department_id` varchar(2) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'approved',
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_citizen_id_unique` (`citizen_id`),
  KEY `users_department_id_foreign` (`department_id`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` VALUES
('40660001', '1969900000001', 'นางสาว อามีนะห์ ยะลาพิทักษ์', 'นางสาว', 'อามีนะห์', 'aminah', 'ยะลาพิทักษ์', 'yalaphitak', 'หญิง', 'aminah@ytc.ac.th', '0812345678', '32', 'แผนกวิชาเทคโนโลยีสารสนเทศและการสื่อสาร', '$2y$12$d2DR6Lr3SGwrWyQ0HOP2Ve4y8f0cNl5vz9qip5q816Subc6sU1zQy', 'teacher', 'approved', 'user_40660001_1786737922.jpeg', '2026-07-26 15:16:18', '2026-09-20 23:14:28'),
('40660002', '1969900000002', 'นาย วิรัตน์ ยินดีดี', 'นาย', 'วิรัตน์', 'wirat', 'ยินดีดี', 'yindidee', 'ชาย', 'wirat@ytc.ac.th', '0882531483', '10', 'งานความร่วมมือ', '$2y$12$k4VZ.9XIKpGv.ocJvVR1ku.LY0u5CYXKWqlZXLnUdqKsfJIwOf28q', 'technician', 'approved', 'user_40660002_1786737938.png', '2026-07-26 15:16:18', '2026-08-30 00:35:42'),
('40660003', '1969900000003', 'นาย สมมาตร ศรีสวัดิ์', 'นาย', 'สมมาตร', 'sommat', 'ศรีสวัดิ์', 'srisawat', 'ชาย', 'sommat@ytc.ac.th', '082-948-25', '10', 'งานศูนย์ข้อมูลสารสนเทศ', '$2y$12$XKNcUy3UqTzqwdJRZ/ILN.PIVR5uLVSym1W6d/G7H2YyKZ39Crwui', 'head,teacher', 'approved', 'user_40660003_1786737886.png', '2026-07-26 15:16:19', '2026-08-14 20:51:10'),
('40660004', '1969900000004', 'นาย สมชาย เจริญเลิศ', 'นาย', 'สมชาย', 'somchai', 'เจริญเลิศ', 'charoenlert', 'ชาย', 'somchai@ytc.ac.th', '083-456-78', '10', 'งานศูนย์ข้อมูลสารสนเทศ', '$2y$12$WWBguhM00sQfpSqIJJWs2eus2Omm8QxpX1I/TfNR6hwDSDBTub0K6', 'executive,teacher', 'approved', 'user_40660004_1786737904.png', '2026-07-26 15:16:19', '2026-08-14 20:51:44'),
('40660005', '1969800313231', 'นางสาว อาซวานี ยะโกะ', 'นางสาว', 'อาซวานี', 'Aswani', 'ยะโกะ', 'Yakoh', NULL, 'wani@gmail.com', '0644649376', NULL, 'งานวิทยบริการและห้องสมุด', '$2y$12$kDGKOEBY/CQ3F2c89UV/OeRY47d4WB12LewE0xrRsmOb4toSIPZWe', 'teacher', 'approved', 'user_40660005_1786739008.jpg', '2026-08-14 20:21:59', '2026-09-10 00:35:06'),
('admin', '1969900000000', 'นาย อิมรอน สือแม', 'นาย', 'อิมรอน', 'Imron', 'สือแม', 'Suamae', 'ชาย', 'imron@ytc.ac.th', '0845678901', '10', 'งานบริหารงานทั่วไป', '$2y$12$FahOZQQ1xLu5fwUK4fEZsOfmpCZtvE.RCA1jwrOIerFJv0jH/S2na', 'admin,teacher', 'approved', 'user_admin_1786738398.jpg', '2026-07-26 15:16:19', '2026-08-23 00:35:15'),
('Boyhasi', '1977777777777', 'นาย บอย หาสี', 'นาย', 'บอย', 'Boy', 'หาสี', 'Hasi', NULL, 'Boyhasi20@gmail.com', '0999999999', NULL, 'งานศูนย์ข้อมูลสารสนเทศ', '$2y$12$7GxsMqJBPDPX7Yp3WH9JCOqPsjsWXBjezI6m4prBwRanemPz1Xv3C', 'technician,teacher', 'approved', NULL, '2026-08-30 16:02:24', '2026-08-30 16:02:24'),
('Kachatd', '1999999999999', 'นาย กาชาด ศิริชัยวัตนา', 'นาย', 'กาชาด', 'Kachatd', 'ศิริชัยวัตนา', 'Sirichaiwattana', NULL, 'Kachatd19@gmail.com', '0666666666', NULL, 'งานศูนย์ข้อมูลสารสนเทศ', '$2y$12$di0P9D4k/MxgmKkc49qFVOvh/EHWK8OaAtXCCfYbkRSrc.ITiEVlm', 'technician,teacher', 'approved', NULL, '2026-08-30 15:59:09', '2026-08-30 15:59:09'),
('Songhao', '1900000000000', 'นาย ทรงเผ่า ช่วยคงมา', 'นาย', 'ทรงเผ่า', 'Songhao', 'ช่วยคงมา', 'Chuaykongma', NULL, 'Songhao199@gmail.com', '0600000000', NULL, 'งานศูนย์ข้อมูลสารสนเทศ', '$2y$12$tul39fmZSpLLVCdxl2Zou.6vksouqluspGnMYyp1rXAubdgw.z5Na', 'head,teacher', 'approved', NULL, '2026-08-30 15:53:41', '2026-08-30 15:53:41'),
('teehah36', '1111111111111', 'นางสาว ฟาตีฮะห์ หวังยี', 'นางสาว', 'ฟาตีฮะห์', 'Fateehah', 'หวังยี', 'Yangyee', NULL, 'Teehah@gmail.com', '0644649376', NULL, 'งานประชาสัมพันธ์', '$2y$12$3R2MPTBptWIB64ppyAvYseT2KL0.WOXYK0IPaKKQtYy2KwoD2uv52', 'teacher', 'approved', 'user_teehah36_1787344706.png', '2026-08-21 20:30:05', '2026-09-20 23:11:33');

SET FOREIGN_KEY_CHECKS = 1;
-- Dump completed successfully.

-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql205.infinityfree.com
-- Generation Time: Sep 09, 2026 at 04:49 AM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42851763_todolist`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_types`
--

CREATE TABLE `accounting_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_types`
--

INSERT INTO `accounting_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'accounting', 'คะแนน', '3.00');

-- --------------------------------------------------------

--
-- Table structure for table `administrative_types`
--

CREATE TABLE `administrative_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrative_types`
--

INSERT INTO `administrative_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'administrative', 'คะแนน', '5.00');

-- --------------------------------------------------------

--
-- Table structure for table `community_types`
--

CREATE TABLE `community_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `community_types`
--

INSERT INTO `community_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'community', 'คะแนน', '7.00');

-- --------------------------------------------------------

--
-- Table structure for table `debt_types`
--

CREATE TABLE `debt_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `debt_types`
--

INSERT INTO `debt_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'โทรติดตามหนี้พร้อมบันทึกการติดตามหนี้', 'คน', '4.00'),
(2, 'ออกไปติดตามหนี้', 'คน', '10.00');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `position` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `username`, `password`, `role`, `position`) VALUES
(1, 'นายอาเดณ ชายกุล', '10001', '123', 'user', 'ผู้จัดการใหญ่'),
(2, 'นายเกียรติพงศ์ กูลดี', '10018', '123', 'user', 'รองผู้จัดการใหญ่'),
(3, 'นายศรัณย์ เวลาดี', '10022', '123', 'user', 'รองผู้จัดการใหญ่'),
(4, 'นายยมนา พยายาม', '30008', '123', 'user', 'ผู้จัดการอาวุโส/ผู้จัดการสาขาเมืองกระบี่-อ่าวลึก'),
(5, 'นางศิริมา สิทธิมนต์', '10003', '123', 'user', 'หัวหน้าฝ่ายการเงิน'),
(6, 'นายนัฐวุฒิ ช่างสนั่น', '10002', '123', 'user', 'หัวหน้าฝ่ายสินเชื่อ'),
(7, 'น.ส.รัชนีกร กูลดี', '10007', '123', 'user', 'หัวหน้าฝ่ายธุรการ'),
(8, 'น.ส.พรทิพย์ ชายกุล', '10012', '123', 'user', 'หัวหน้าฝ่ายบัญชี'),
(9, 'นายเดชสิทธ์ บ่อหนา', '10051', '123', 'user', 'หัวหน้าฝ่ายเทคโนโลยีสารสนเทศ'),
(10, 'นายเสรี ยะนาย', '10025', '123', 'user', 'หัวหน้าฝ่ายสัมพันธ์ชุมชน'),
(11, 'นายวศพล พยายาม', '10043', '123', 'user', 'รักษาการหัวหน้าฝ่ายติดตามหนี้'),
(12, 'นายดำรงค์ บุญเพิ่ม', '20021', '123', 'user', 'ผู้จัดการสาขาแหลมกรวด'),
(13, 'นายอภิสิทธิ์ ขาวเล็ก', '10047', '123', 'user', 'ผู้จัดการสาขาเหนือคลอง'),
(14, 'นายประสงค์ แสล่หมัน', '40054', '123', 'user', 'ผู้จัดการสาขาเกาะลันตา'),
(15, 'นายฮูไซฟี ดำนาดี', '10036', '123', 'user', 'ผู้จัดการสาขาคลองท่อม'),
(16, 'นายสมเกียรติ ทวีกุล', '20048', '123', 'user', 'รักษาการผู้จัดการสาขาคลองหมาก'),
(17, 'นายวิทวัส ชลธี', '10015', '123', 'user', 'เจ้าหน้าที่ฝ่ายติดตามหนี้'),
(18, 'นายอัษฎาวุธ มารถโอสถ', '10061', '123', 'user', 'เจ้าหน้าที่ฝ่ายติดตามหนี้'),
(19, 'น.ส.พรพิมล พยายาม', '10035', '123', 'user', 'เจ้าหน้าที่ฝ่ายเทคโนโลยีสารสนเทศ'),
(20, 'น.ส.นาเดีย ชายกุล', '10072', '123', 'user', 'เจ้าหน้าที่ฝ่ายเทคโนโลยีสารสนเทศ'),
(21, 'น.ส.ไหมสุรีย์ กุลพ่อ', '10034', '123', 'user', 'เจ้าหน้าที่ฝ่ายธุรการ'),
(22, 'น.ส.ศิริยา ชายกุล', '10044', '123', 'user', 'เจ้าหน้าที่ฝ่ายบัญชี'),
(23, 'น.ส.วิยุดา คลองรั้ว', '10050', '123', 'user', 'เจ้าหน้าที่ฝ่ายบัญชี'),
(24, 'น.ส.อัคลีมา ชายกุล', '10058', '123', 'user', 'เลขาผู้จัดการและดูแลฝ่ายบุคคล'),
(25, 'นายก้อหรี โบบทอง', '20020', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(26, 'น.ส.วันณา คลองยวน', '20010', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(27, 'น.ส.อลิษา ชลธี (เยาะ)', '11019', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(28, 'น.ส.เพ็ญพร ชลธี', '11028', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(29, 'น.ส.ไรฮัน อุปมา', '10026', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(30, 'น.ส.อภิญญา ชลธี', '70009', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(31, 'น.ส.เจนจิรา ซื่อตรง', '41030', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(32, 'น.ส.วิภารัตน์ ชายกุล', '11037', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(33, 'น.ส.วิยะดา ขำหิรัญ', '40039', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(34, 'น.ส.ยุพา ตาวัน', '71029', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(35, 'นายปกรณ์ ชายกุล', '40041', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(36, 'น.ส.สุธิดา ผลเงาะ', '51045', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(37, 'น.ส.สโรชา สกุลเล็ก', '51046', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(38, 'นายวิเชียร โต๊ะแหมน', '42053', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(39, 'น.สพัชรี บุญเพิ่ม', '22055', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(40, 'น.ส.ประทุมพร คลองยวน', '30057', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(41, 'น.ส.มารีด้า โบบทอง', '60059', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(42, 'นางชลธิชา หลานสัน', '53062', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(43, 'น.ส.สุกัญญา โต๊ะหลี', '42063', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(44, 'นายสมชาย เจ๊ะละหวัง', '50064', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(45, 'นายธนาวุฒ มัสหรน', '10065', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(46, 'นายชัยวิทย์ แข็งแรง', '31066', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(47, 'นายเจษฎา เจะพงค์', '10067', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(48, 'นายพัชระ ชลธี', '50068', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(49, 'น.ส.สุกัญญา อาแว', '30070', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(50, 'น.ส.ศิริวรรณ จิงู', '31071', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(51, 'น.ส.สาธุพร ปาทาน', '72073', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(52, 'นายนะบีล กาหลง', '10074', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(53, 'นายกิตติศักดิ์ บ้านนบ', '60075', '123', 'user', 'เจ้าหน้าที่ให้บริการสมาชิก'),
(54, 'น.ส.ปราณี พยายาม', NULL, NULL, 'user', 'แม่บ้าน'),
(55, 'admin', 'admin', 'admin', 'admin', '');

-- --------------------------------------------------------

--
-- Table structure for table `finance_types`
--

CREATE TABLE `finance_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_types`
--

INSERT INTO `finance_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'รับฝากหุ้น', 'รายการ', '1.00'),
(2, 'รับฝาก-ถอนเงิน  ', 'รายการ', '1.00'),
(3, 'รับชำระสินเชื่อ', 'รายการ', '1.00'),
(4, 'ออกใบจ่ายเงิน', 'รายการ', '1.00'),
(5, 'ออกใบรับเงินยืม', 'รายการ', '1.00'),
(6, 'ออกใบรับเงินอื่นๆ', 'รายการ', '1.00'),
(7, 'ลงข้อมูลสมาชิกใหม่', 'รายการ', '5.00'),
(8, 'เปิดบัญชีเงินฝาก', 'รายการ', '3.00'),
(9, 'รับสมัครตะกาฟุล', 'คน', '3.00'),
(10, 'รับชำระตะกาฟุล', 'รายการ', '1.00'),
(11, 'ทำเบิกสวัสดิการสมาชิก', 'คน', '2.00'),
(12, 'ทำรายการถอนหุ้น', 'คน', '2.00'),
(13, 'รับสินเชื่อฉุกเฉินพร้อมทำสัญญา', 'สัญญา', '10.00'),
(14, 'รับสินเชื่อสามัญ', 'คำขอ', '10.00'),
(15, 'รับสินเชื่อพิเศษ', 'คำขอ', '15.00'),
(16, 'โทรติดต่อในงานสหกรณ์ (ค่าโทรสหกรณ์ออกให้)', 'คน', '1.00'),
(17, 'โทรติดต่อในงานสหกรณ์ (ค่าโทรออกเอง)', 'คน', '2.00'),
(18, 'ทำสัญญาสินเชื่อสามัญ,พิเศษ', 'สัญญา', '5.00'),
(19, 'ทำเอกสาร/รายงานต่างๆ', 'ฉบับ', '10.00');

-- --------------------------------------------------------

--
-- Table structure for table `hr_types`
--

CREATE TABLE `hr_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr_types`
--

INSERT INTO `hr_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'hr', 'คะแนน', '6.00');

-- --------------------------------------------------------

--
-- Table structure for table `it_types`
--

CREATE TABLE `it_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_types`
--

INSERT INTO `it_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'ทำประกาศต่างๆ', 'รายการ', '20.00'),
(2, 'ออกแบบงานประชาสัมพันธ์สหกรณ์ (วันหยุด,ประกาศ)', 'รายการ', '10.00'),
(3, 'ออกแบบงานประชาสัมพันธ์สหกรณ์ (ประชาสัมพันธ์ สหกรณ์)', 'หน้าละ', '30.00'),
(4, 'โพสต์/โต้ตอบสมาชิกผ่านเฟส/เพจหรือไลน์สหกรณ์', 'รายการ', '1.00');

-- --------------------------------------------------------

--
-- Table structure for table `job_types`
--

CREATE TABLE `job_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_types`
--

INSERT INTO `job_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(1, 'รับปรึกษาสินเชื่อ (แนบรายชื่อ)\r\n', 'คน', '5.00'),
(2, 'โทรติดต่อในงานสหกรณ์ (ค่าโทรสหกรณ์ออกให้)', 'คน', '1.00'),
(3, 'โทรติดต่อในงานสหกรณ์ (ค่าโทรออกเอง)\r\n', 'คน', '2.00'),
(4, 'อังกัตสินค้าในสำนักงาน\r\n', 'รายการ', '2.00'),
(5, 'อังกัตสินค้านอกสถานที่/ตรวจโครงการสินเชื่อ\r\n', 'รายการ', '10.00'),
(6, 'โทรติดตามหนี้พร้อมบันทึกการติดตามหนี้\r\n', 'คน', '4.00'),
(7, 'ทำหนังสือติดตามหนี้/หักปันผล/ทำหนังสือส่ง\r\n', 'ฉบับ', '2.00'),
(24, 'ทำเอกสารโนติส\r\n', 'ฉบับ', '5.00'),
(25, 'ออกไปติดตามหนี้\r\n', 'คน', '10.00'),
(26, 'ออกไปเก็บเงินฝาก/หุ้น/ผ่อน\r\n', 'รายการ', '1.00'),
(27, 'ออกพื้นที่เกี่ยวกับงานสหกรณ์ (คุตบะฮ์)', 'ครั้ง', '20.00'),
(28, 'ออกพื้นที่เกี่ยวกับงานสหกรณ์ (รับเงินฝาก+สินเชื่อ)', 'ครั้ง', '20.00'),
(29, 'ออกพื้นที่เกี่ยวกับงานสหกรณ์ (มอบน้ำ)', 'ครั้ง', '5.00'),
(30, 'ออกพื้นที่เกี่ยวกับงานสหกรณ์ (งานบุญ)\r\n', 'ครั้ง', '5.00'),
(31, 'ทำเอกสาร/รายงานต่างๆ\r\n', 'ฉบับ', '10.00'),
(32, 'รับสมาชิกนอกพื้นที่\r\n', 'คนละ', '5.00'),
(33, 'ลงรายการในทะเบียนต่างๆ\r\n', 'รายการ', '1.00'),
(34, 'นำเอกสารเข้าแฟ้ม\r\n', 'ฉบับ', '1.00'),
(35, 'ทำหนังสือจำนอง\r\n', 'ชุด', '15.00'),
(36, 'ทำหนังสือไถ่ถอน\r\n', 'ชุด', '5.00'),
(37, 'ค้นหาถ่ายเอกสารโฉนด\r\n', 'ฉบับ', '2.00'),
(38, 'กรอกข้อมูลในเอกสาร(ยืนยันยอด)\r\n', 'คนละ', '2.00'),
(39, 'ตรวจนับใบเสร็จหรือใบรับจ่ายอื่นๆ\r\n', 'ใบ', '0.10'),
(40, 'ตรวจสอบใบปะหน้ากับโปรแกรม\r\n', 'วัน', '10.00'),
(41, 'เข้าเล่มใบเสร็จ', 'เล่ม', '5.00'),
(42, 'โทรแจ้งรายการผิดพลาดทางบัญชี/ติดตามใบเสร็จล่าช้า\r\n', 'รายการ', '3.00'),
(43, 'ตรวจสอบข้อมูลทางบัญชีประจำวันในโปรแกรม', 'วันละ', '20.00'),
(44, 'ตรวจเช็คยอดเงินฝากธนาคาร', 'รายการ', '1.00'),
(45, 'ทำงบทดลอง', 'งบละ', '50.00'),
(46, 'ทำงบการเงินประจำปี', 'งบละ', '300.00'),
(47, 'ปรับปรุงแก้ไขเอกสารแบบฟอร์มต่างๆ', 'ฉบับละ', '10.00'),
(48, 'ลงรายการปรับปรุงในสมุดและในคอม', 'รายการ', '2.00'),
(49, 'ทำประกาศต่างๆ', 'รายการ', '20.00'),
(50, 'ออกแบบงานประชาสัมพันธ์สหกรณ์ (วันหยุด,ประกาศ)', 'รายการ', '10.00'),
(51, 'ออกแบบงานประชาสัมพันธ์สหกรณ์ (ประชาสัมพันธ์สหกรณ์)', 'หน้าละ', '30.00'),
(52, 'โพสต์/โต้ตอบสมาชิกผ่านเฟส/เพจหรือไลน์สหกรณ์', 'รายการ', '1.00'),
(53, 'แก้ไขข้อมูลหรือเบอร์โทรสมาชิก', 'รายการ', '2.00'),
(54, 'เชิญสมาชิกเข้ากลุ่มไลน์สหกรณ์', 'รายการ', '2.00'),
(55, 'ทำสรุปรับจ่ายประจำวัน', 'วัน', '2.00'),
(56, 'เข้าประชุมพนักงาน สำนักงานใหญ่', 'ครั้ง', '30.00'),
(57, 'เข้าประชุมพนักงาน online', 'ครั้ง', '10.00'),
(58, 'จ่ายค่าเน็ต ค่าไฟ ค่าน้ำ (scan จ่าย)', 'รายการ', '1.00'),
(59, 'ร่วมกิจกรรมสัมพันธ์ชุมชน', 'ครั้ง', '5.00'),
(60, 'แจ้งยอดค้างสินเชื่อหน้าเคาท์เตอร์', 'คน', '2.00'),
(61, 'กรอกข้อมูลส่งประกันสังคม', 'รายการ', '2.00'),
(62, 'สมัครแอพให้สมาชิกจนแล้วเสร็จพร้อมแนบเลขสมาชิกมาในรายงาน', 'คน', '10.00'),
(63, 'ฝึกพนักงานใหม่', 'วัน', '30.00'),
(64, 'ไปจำนองที่ดิน', 'ชุดละ', '20.00'),
(65, 'อธิบายรายละเอียดจำนองที่ดินและเซ็นต์เอกสาร', 'ครั้งละ', '3.00'),
(66, 'อื่นๆ', '', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `loan_types`
--

CREATE TABLE `loan_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `classifier` varchar(50) NOT NULL,
  `score` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_types`
--

INSERT INTO `loan_types` (`id`, `type_name`, `classifier`, `score`) VALUES
(0, '', '', '0.00'),
(1, 'รับปรึกษาสินเชื่อ (แนบรายชื่อ)', 'คน', '5.00'),
(2, 'รับชำระสินเชื่อ', 'รายการ', '1.00'),
(3, 'อังกัตสินค้าในสำนักงาน', 'รายการ', '2.00'),
(4, 'อังกัตสินค้านอกสถานที่/ตรวจโครงการสินเชื่อ', 'รายการ', '10.00');

-- --------------------------------------------------------

--
-- Table structure for table `summary`
--

CREATE TABLE `summary` (
  `เดือน/ปี` date NOT NULL,
  `ชื่อพนักงาน` varchar(100) NOT NULL,
  `จำนวนรายการงาน` float NOT NULL,
  `จำนวนหน่วยงานรวม` float NOT NULL,
  `คะแนนรวมสะสม` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `task_date` date NOT NULL,
  `recorder_name` varchar(255) NOT NULL,
  `job_type` varchar(500) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT 0.00,
  `score` decimal(10,2) DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `task_date`, `recorder_name`, `job_type`, `quantity`, `score`, `note`, `created_at`) VALUES
(17, '2026-09-07', 'น.ส.นาเดีย ชายกุล', 'รับฝากหุ้น, รับสมัครตะกาฟุล', '6.00', '8.00', '', '2026-09-07 07:21:25'),
(18, '2026-08-20', 'น.ส.นาเดีย ชายกุล', 'รับฝากหุ้น', '1.00', '1.00', '', '2026-09-07 07:21:46'),
(19, '2026-09-07', 'น.ส.ศิริยา ชายกุล', 'รับฝาก-ถอนเงิน', '5.00', '5.00', '', '2026-09-07 07:42:41'),
(23, '2026-09-09', 'น.ส.นาเดีย ชายกุล', 'รับฝากหุ้น, รับฝาก-ถอนเงิน', '4.00', '4.00', '', '2026-09-09 07:44:03'),
(24, '2026-09-09', 'น.ส.นาเดีย ชายกุล', 'รับปรึกษาสินเชื่อ (แนบรายชื่อ), รับชำระสินเชื่อ', '6.00', '18.00', '', '2026-09-09 08:16:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_types`
--
ALTER TABLE `accounting_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `administrative_types`
--
ALTER TABLE `administrative_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `community_types`
--
ALTER TABLE `community_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `debt_types`
--
ALTER TABLE `debt_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `finance_types`
--
ALTER TABLE `finance_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `hr_types`
--
ALTER TABLE `hr_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `it_types`
--
ALTER TABLE `it_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `job_types`
--
ALTER TABLE `job_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `loan_types`
--
ALTER TABLE `loan_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `job_types`
--
ALTER TABLE `job_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

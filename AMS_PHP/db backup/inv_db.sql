-- Inventory Management System Backup
-- Generated on: 2025-09-29 09:40:46
-- Database: inventory_db


-- --------------------------------------------------------
-- Table structure for table `item_tb`
-- --------------------------------------------------------
CREATE TABLE `item_tb` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(100) NOT NULL DEFAULT 'N/A',
  `name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `date_received` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `type_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `type_id` (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `item_tb`
--
INSERT INTO `item_tb` VALUES ('51', 'HIMCLP0011', 'Laptop', 'Lenovo ', 'Lenovo Ideapad 100, 80RK', 'PF0KM4VL', 'CPU: Intel i3-5005U 2.0G\r\nRAM: 8GB RAM DDR4\r\nROM: 256 SSD\r\nOS: Windows 10 Pro 64-bit\r\nDate Issued: October 6, 2016\r\nIssue: Broken hinge', '1', '2016-10-06', '2025-09-11 13:28:29', '2025-09-11 13:38:28', '6');
INSERT INTO `item_tb` VALUES ('52', 'HIMCLP0011', 'Laptop Charger', 'Lenovo ', 'N/A', '8S5A10J40450C0C1SG64F003Y', 'Laptop Charger for Lenovo Ideapad 3', '1', '2016-10-06', '2025-09-11 13:29:19', '2025-09-11 13:38:16', '2');
INSERT INTO `item_tb` VALUES ('83', 'RAM008R01', 'RAM', 'Samsung', 'Samsung', '8SSM30N768G1PF436008R', 'SAMSUNG 8GB DDR4 3200MHZ SODIMM for Sarah Rioveros', '1', '2025-05-05', '2025-09-09 11:39:30', '2025-09-12 07:45:19', '1');
INSERT INTO `item_tb` VALUES ('84', 'SSD358001', 'SSD', 'Apacer', 'AS350', '192507403580', 'APACER AS350 512GB 2.5\" \r\nINTERNAL SSD for Sarah Rioveros', '0', '2025-05-05', '2025-09-09 11:39:30', '2025-09-29 09:24:02', '7');
INSERT INTO `item_tb` VALUES ('85', 'MTR664801', 'Monitor', 'Nvision', 'N190HD V8', 'N190VL658A241116648', 'NVISION 19\" N190HD V8 LED MONITOR for ACCTG PC', '0', '2025-05-30', '2025-09-09 11:39:30', '2025-09-29 10:00:47', '2');
INSERT INTO `item_tb` VALUES ('86', 'MTR377702', 'Monitor', 'Nvision', 'N190HD V8', 'N190VL658A241223777', 'NVISION 19\" N190HD V8 LED MONITOR FOR REF PC', '0', '2025-05-30', '2025-09-09 11:39:30', '2025-09-19 14:24:48', '2');
INSERT INTO `item_tb` VALUES ('87', 'KEY00001', 'Keyboard & Mouse', 'A4TECH', 'KRS-3330', 'N/A', 'A4TECH KRS-3330 KEYBOARD & MOUSE SET', '5', '2025-06-26', '2025-09-09 11:39:30', '2025-09-29 13:17:14', '2');
INSERT INTO `item_tb` VALUES ('88', 'SSD9CUY02', 'SSD', 'ADATA', 'SU650', '2P1029129CUY', 'ADATA SU650 SSD 512 GB FOR Merriam Mangrobang Laptop', '0', '2025-06-26', '2025-09-09 11:39:30', '2025-09-23 15:04:21', '7');
INSERT INTO `item_tb` VALUES ('89', 'RJ450001', 'RJ45', 'RJ45', 'RJ45', 'N/A', 'RJ45 ', '100', '2025-07-14', '2025-09-09 11:39:30', '2025-09-12 08:30:24', '3');
INSERT INTO `item_tb` VALUES ('90', 'CMS000001', 'Cmos Battery', 'Cmos Battery', 'Cmos Battery', 'N/A', 'CMOS BATTERY', '20', '2025-07-14', '2025-09-09 11:39:30', '2025-09-23 10:08:24', '2');
INSERT INTO `item_tb` VALUES ('92', 'PLI00001', 'Pliers Sets', 'N/A', 'N/A', 'N/A', '(Plier, Long Nose, and Cutter)', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:40:00', '5');
INSERT INTO `item_tb` VALUES ('93', 'SCR00002', 'Screwdriver', 'N/A', 'N/A', 'N/A', 'Phillip Screwdriver ', '2', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:40:09', '5');
INSERT INTO `item_tb` VALUES ('94', 'SCR00002', 'Screwdriver', 'N/A', 'N/A', 'N/A', 'Flat Screwdriver', '3', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:40:22', '5');
INSERT INTO `item_tb` VALUES ('95', 'CRI00002', 'Crimping Tool', 'N/A', 'N/A', 'N/A', 'Crimping Tool pass through', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:40:31', '5');
INSERT INTO `item_tb` VALUES ('96', 'LAN00001', 'LAN Tester', 'N/A', 'N/A', 'N/A', 'LAN Tester', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:40:41', '5');
INSERT INTO `item_tb` VALUES ('97', 'HDD00001', 'HDD Dock', 'N/A', 'N/A', 'N/A', 'HDD Docking Station', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:40:47', '5');
INSERT INTO `item_tb` VALUES ('98', 'CRI00002', 'Crimping Tool', 'N/A', 'N/A', 'N/A', 'Ethernet Punch Down Tool', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:40:55', '5');
INSERT INTO `item_tb` VALUES ('99', 'SOL00001', 'Soldering Machine', 'N/A', 'N/A', 'N/A', '2 in 1 Soldering Station (Soldering Iron and Heat Gun)', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:41:52', '5');
INSERT INTO `item_tb` VALUES ('100', 'BRU00001', 'Brush', 'N/A', 'N/A', 'N/A', 'Paint brush', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:43:23', '5');
INSERT INTO `item_tb` VALUES ('101', 'CLE00001', 'Cleaner', 'N/A', 'N/A', 'N/A', 'Wie Out', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:44:01', '5');
INSERT INTO `item_tb` VALUES ('102', 'CON00001', 'Contact Cleaner', 'N/A', 'N/A', 'N/A', 'Contact Cleaner', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:44:07', '5');
INSERT INTO `item_tb` VALUES ('103', 'ADA00001', 'Adapter', 'N/A', 'N/A', 'N/A', 'LAN to USB adapter', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:44:13', '5');
INSERT INTO `item_tb` VALUES ('104', 'CUT00001', 'Cutter', 'N/A', 'N/A', 'N/A', 'Heavy Duty Cutter', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:39:49', '5');
INSERT INTO `item_tb` VALUES ('105', 'HDDORFT09', 'HDD', 'Toshiba', 'Toshiba', 'X7AOPORFT', 'Allan Old 1TB HDD', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 09:12:13', '7');
INSERT INTO `item_tb` VALUES ('106', 'FLA00001', 'Flashdrive', 'Sandisk', 'Sandisk', 'N/A', 'Sandisk 4GB (Red)', '0', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 09:10:07', '7');
INSERT INTO `item_tb` VALUES ('107', 'HDM00001', 'HDMI Splitter', 'HDMI Splitter', 'HDMI Splitter', 'N/A', 'HDMI Splitter', '1', '2025-09-09', '2025-09-09 13:12:50', '2025-09-23 14:39:39', '5');
INSERT INTO `item_tb` VALUES ('108', 'LT0200', 'Laptop', 'Dell', 'Dell Latitude 5410', '1BXSM53', 'CPU: i5-10310U 1.70GHz\r\nRAM: 16GB RAM 2677MT/s\r\nROM: DIGISTOR 512GB\r\nOS: WIN 11-64 BIT PRO', '1', '2024-08-01', '2025-09-09 13:47:05', '2025-09-11 14:41:08', '6');
INSERT INTO `item_tb` VALUES ('109', 'LT0200', 'Laptop Charger', 'Dell', 'DA65PM111', 'N/A', 'Laptop Charger for Dell', '1', '2024-08-01', '2025-09-09 13:48:45', '2025-09-11 14:40:41', '2');
INSERT INTO `item_tb` VALUES ('110', 'LT0149', 'Laptop', 'Dell', 'Dell Latitude 5490', '4191901498', 'CPU: I7 8TH GEN @1.90 GHZ\r\nRAM: 8GB RAM 2400 MHZ\r\nROM: 256 GB SSD\r\nOS: Windows 11 PRO 64 bit\r\nDate Issued: September 2023 to Jerwin Herrera', '1', '2023-09-25', '2025-09-09 13:51:54', '2025-09-09 14:31:08', '6');
INSERT INTO `item_tb` VALUES ('111', 'LT0149', 'Laptop Charger', 'Dell', 'LA65130', 'N/A', 'Laptop Charger for Dell Latitude 5490', '1', '2023-09-01', '2025-09-09 13:53:49', '2025-09-09 14:31:16', '2');
INSERT INTO `item_tb` VALUES ('112', 'LT0144', 'Laptop', 'Lenovo', 'Lenovo Ideapad 3', '', 'CPU: I5 11TH GEN @ 2.40GHZ\r\nRAM: 8GB RAM DDR4 3200 MHZ\r\nROM: 500 GB SSD\r\n0S: Windows 11 Home 64 bit\r\nDate Issued: March 2023 to Yaj Comia', '1', '2023-03-01', '2025-09-09 13:56:27', '2025-09-19 15:02:30', '6');
INSERT INTO `item_tb` VALUES ('113', 'LT0070', 'Laptop Charger', 'Lenovo', 'PA-1450-55L', 'N/A', 'Laptop Charger for Lenovo Ideapad 3', '1', '2016-06-01', '2025-09-09 13:58:38', '2025-09-15 07:41:03', '2');
INSERT INTO `item_tb` VALUES ('114', 'LT0130', 'Laptop', 'Acer', 'Acer Travelmate ', 'NXVLFSP00117047907600', 'CPU: I3 10TH GEN @2.10GHZ\r\nRAM: 4GB RAM DDR4 2667 MHZ \r\nROM: 1TB HDD \r\nDATE ISSUED: AUGUST 2021 on Mei Sales\r\nWith Acer Laptop Charger & Laptop Bag\r\n\r\nUpgrade:\r\nRAM: 8GB RAM DDR4 2667 MHZ \r\nROM: 500GB SSD\r\nDate: September 8, 2025 ', '0', '2025-09-09', '2025-09-09 14:03:41', '2025-09-23 10:06:46', '6');
INSERT INTO `item_tb` VALUES ('115', 'N/A', 'Laptop Charger', 'Acer', 'PA-1700-02', 'N/A', 'Laptop Charger for Acer Travelmate P214 Series', '0', '2025-09-01', '2025-09-09 14:06:29', '2025-09-10 09:40:11', '2');
INSERT INTO `item_tb` VALUES ('116', 'LP0015', 'Laptop', 'Acer', 'Acer Travelmate P214 Series', 'NXVLFSP00T04003A817600', 'CPU: I5 10TH GEN 1.60GHZ\nRAM: 8GB RAM DDR4 2667 MHZ\nROM: 500 GB SSD\nOS: WINDOWS 10 HOME 64 BIT\nDate Issued: NOVEMBER 2020 to Atty Jake\nWith Acer Laptop Charger', '0', '2020-11-01', '2025-09-09 14:11:55', '2025-09-10 10:01:09', '6');
INSERT INTO `item_tb` VALUES ('117', 'N/A', 'Laptop Charger', 'Acer', 'A13-045N2A', 'F2T8341422002703', 'Laptop Charger for ACER TRAVELMATE P214 SERIES', '0', '2020-11-01', '2025-09-09 14:13:41', '2025-09-10 09:38:55', '2');
INSERT INTO `item_tb` VALUES ('120', 'LT0137', 'Laptop', 'HP', 'HP Pavillion 15z-eh100', '5CD1496GVX', 'CPU: AMD RYZEN 5 5500U 2.20GHz\nRAM: 8GB RAM\nROM: 256GB SSD\nOS: WINDOWS 11 HOME 64 BIT\nDate Issued: JANUARY 2022 to REF Manager', '1', '2022-01-03', '2025-09-09 16:00:47', '2025-09-09 16:32:05', '6');
INSERT INTO `item_tb` VALUES ('121', 'N/A', 'Laptop Charger', 'HP', 'HP', 'N/A', 'Laptop Charger or HP Pavillion', '1', '2022-01-03', '2025-09-09 16:02:56', '2025-09-10 13:56:49', '2');
INSERT INTO `item_tb` VALUES ('125', 'LT0076', 'Laptop', 'Acer', 'Acer Aspire ES1433', 'NXGLLSP00164605CE37200', 'CPU: I3 6th Gen 2 GHz \r\nRAM: 12 GB RAM DDR4 2133 MHz\r\nROM: 500 GB SSD\r\nOS: Windows 10 Home 64 bit\r\nLaptop for Cebu branch\r\nUpgrade  by Sir King last \r\n2024\r\nIssue: Defective Battery', '1', '2017-03-01', '2025-09-10 13:50:13', '2025-09-11 16:18:48', '6');
INSERT INTO `item_tb` VALUES ('126', 'LT0076', 'Laptop Charger', 'Acer', 'PA-1700-02', 'N/A', 'Laptop Charger for Acer Aspire ES1-433', '1', '2017-03-01', '2025-09-10 13:56:21', '2025-09-12 08:50:55', '2');
INSERT INTO `item_tb` VALUES ('127', 'CDR091025', 'CD ROM', 'Lenovo', 'DB65', 'N/A', 'Lenovo USB Portable DVD Burner', '1', '2025-09-10', '2025-09-10 15:26:44', '2025-09-23 08:46:35', '7');
INSERT INTO `item_tb` VALUES ('128', 'HDD091025', 'External HDD', 'Transcend', 'Transcend', 'I08691-3376', '1TB External HDD (Small Black)', '1', '2025-09-10', '2025-09-10 17:09:47', '2025-09-23 08:56:30', '7');
INSERT INTO `item_tb` VALUES ('129', 'HDD091025', 'External HDD', 'Seagate', 'Seagate', '2GH4XMVP', '1TB External HDD (Large Black)', '0', '2025-09-10', '2025-09-11 08:33:22', '2025-09-26 13:21:07', '7');
INSERT INTO `item_tb` VALUES ('132', 'MTR625903', 'Monitor', 'Nvision', 'N190HD V8', 'N190HDV8S250606259', 'Nvision N190HD V8 Monitor', '0', '2025-09-11', '2025-09-11 17:26:35', '2025-09-12 07:40:19', '2');
INSERT INTO `item_tb` VALUES ('133', 'MTR693104', 'Monitor', 'Nvision', 'N190HD V8', 'N190VL658A240886931', 'Nvision N190HD V8 Monitor', '0', '2025-09-11', '2025-09-11 17:28:22', '2025-09-12 07:40:00', '2');
INSERT INTO `item_tb` VALUES ('134', 'IPP244801', 'IP IPhone', 'N/A', 'N/A', 'NMD223C002448', 'Black IP IPhone ', '0', '2025-02-02', '2025-09-12 07:53:16', '2025-09-12 07:55:58', '6');
INSERT INTO `item_tb` VALUES ('135', 'IPP244702', 'IP IPhone', 'N/A', 'N/A', 'NMD223C002447', 'Black IP Phone\r\n2 SIP LINE\r\nPOE Enabled (S1EP Only)\r\nHandset/Hands free/Headphone Mode\r\nDesktop Stand\r\nOptional External Power Sup', '0', '2025-02-02', '2025-09-12 07:54:44', '2025-09-12 07:55:33', '6');
INSERT INTO `item_tb` VALUES ('136', 'BMT006303', 'Biometrics', 'NGTECO', 'AS10', 'GPR125120063', 'Deployment of new biometrics for CDO Branch', '0', '2025-08-13', '2025-09-12 08:22:10', '2025-09-15 08:47:53', '6');
INSERT INTO `item_tb` VALUES ('137', 'BMT031802', 'Biometrics', 'NGTECO', 'AS10', 'GPR1251200318', 'Deployment of new biometric for Iloilo Branch', '0', '2025-08-13', '2025-09-12 08:25:22', '2025-09-15 08:48:16', '6');
INSERT INTO `item_tb` VALUES ('138', 'BMT052901', 'Biometrics', 'NGTECO', 'AS10', 'GPR1251200529', 'Deployment of new biometric for Canlubang', '1', '2025-08-13', '2025-09-12 08:27:46', '2025-09-12 08:27:46', '6');
INSERT INTO `item_tb` VALUES ('139', 'PRT867001', 'Printer', 'Canon', 'Canon GM2070', 'KMEN08670', 'Service Unit Printer', '1', '2021-11-08', '2025-09-12 10:42:32', '2025-09-12 10:42:32', '6');
INSERT INTO `item_tb` VALUES ('140', 'PTR234302', 'Printer', 'Epson', 'EPSON ECOTANK M2050', 'XCCW002343', 'Spare Printer', '1', '2025-06-28', '2025-09-12 10:45:59', '2025-09-12 10:45:59', '6');
INSERT INTO `item_tb` VALUES ('142', 'LP6092', 'Laptop', 'Lenovo', 'Lenovo ThinkPad X390', 'PC-1DFVTR', 'CPU: I7 8th Gen @1.90 GHz\r\nRAM: 16 GB DDR4 2400 MHz\r\nROM: 500GB SSD\r\nOS: Windows 10 Pro 64 bit\r\nCharger Code: LPC6092', '0', '2024-10-16', '2025-09-16 09:43:59', '2025-09-16 15:47:33', '6');
INSERT INTO `item_tb` VALUES ('143', 'LAP000011', 'Laptop Charger', 'Lenovo', 'ADLX45YLC2D', 'N/A', 'Laptop Charger for Lenovo ThinkPad X390', '0', '2024-10-16', '2025-09-16 09:57:39', '2025-09-26 13:36:46', '2');
INSERT INTO `item_tb` VALUES ('144', 'SSD708103', 'SSD', 'ADATA', 'SU650', '4P1122017081', 'For sir Moyo', '0', '2025-09-17', '2025-09-19 10:00:56', '2025-09-22 16:34:39', '7');
INSERT INTO `item_tb` VALUES ('145', 'SSD711104', 'SSD', 'ADATA', 'SU650', '4P1122017111', 'For sir Miguel', '0', '2025-09-17', '2025-09-19 10:02:03', '2025-09-22 16:58:30', '7');
INSERT INTO `item_tb` VALUES ('146', 'SSD708005', 'SSD', 'ADATA', 'SU650', '4P1122017080', 'For sir Noy', '0', '2025-09-17', '2025-09-19 10:02:41', '2025-09-23 14:14:38', '7');
INSERT INTO `item_tb` VALUES ('147', 'RAM8G2202', 'RAM', 'ADATA', 'ADATA', 'AD4S32008G22', 'For Sir Moyo', '0', '2025-09-17', '2025-09-19 10:05:02', '2025-09-22 16:35:09', '1');
INSERT INTO `item_tb` VALUES ('151', 'INK90121', 'INK', 'Epson', 'N/A', '014699012', 'For CNC Printer', '1', '2025-09-19', '2025-09-19 10:36:38', '2025-09-26 14:45:17', '2');
INSERT INTO `item_tb` VALUES ('152', 'INK90102', 'INK', 'Epson', 'N/A', '014699010', 'For CNC Printer', '0', '2025-09-19', '2025-09-19 10:38:03', '2025-09-22 09:22:13', '2');
INSERT INTO `item_tb` VALUES ('153', 'LAPJ3FS10', 'Laptop', 'Lenovo', 'Lenovo X380 Yoga', 'MP-1FJ3FS', 'CPU: i7 8th Gen 1.90 GHz\r\nRAM: 16GB RAM DDR4 2400 MHz\r\nROM: 500GB SSD \r\nOS: Windows 10 64 bit \r\nLaptop Charger Code: LAPZ13510', '0', '2025-09-19', '2025-09-19 11:43:11', '2025-09-24 08:57:27', '6');
INSERT INTO `item_tb` VALUES ('154', 'FLA00002', 'Flashdrive', 'Blue Flashdrive', 'Blue Flashdrive', 'N/A', 'Blue Flashdrive', '1', '2025-09-09', '2025-09-23 09:09:45', '2025-09-23 09:09:45', '7');
INSERT INTO `item_tb` VALUES ('155', 'SOL00001', 'Solid Works', 'Solid Works', 'Solid Works 2018', 'N/A', 'Solid Works 2018', '4', '2025-09-09', '2025-09-23 09:14:12', '2025-09-23 09:14:12', '4');
INSERT INTO `item_tb` VALUES ('156', 'SMA00001', 'Smart Phone', 'Samsung', 'Samsung Duos', 'N/A', 'Samsung Duos 4G', '5', '2025-09-09', '2025-09-23 09:15:57', '2025-09-23 09:15:57', '6');
INSERT INTO `item_tb` VALUES ('157', 'PRE00001', 'Precision', 'N/A', 'N/A', 'N/A', 'Set of Precision Screwdriver ', '1', '2025-09-09', '2025-09-23 14:30:37', '2025-09-23 14:30:37', '5');
INSERT INTO `item_tb` VALUES ('159', 'LAPZ13511', 'Laptop Charger', 'Lenovo', 'Lenovo', '11S36200287ZZ8008AZ135', 'Laptop Charger for Lenovo', '0', '2025-09-19', '2025-09-24 08:55:34', '2025-09-26 13:37:01', '2');
INSERT INTO `item_tb` VALUES ('160', 'LTO111', 'Laptop', 'Acer', 'Aspire', 'NXH9KSP00192500FB63400', 'CPU: I3 7TH GEN\nRAM: 4GB RAM\nROM: 1TB HDD\nOS: WINDOWS 10 64 BIT\nLaptop Charger Code: LTO111\n', '0', '2019-08-01', '2019-09-01 00:00:00', '2025-09-24 11:06:50', '6');
INSERT INTO `item_tb` VALUES ('161', 'LTO111', 'Laptop Charger', 'Acer', 'Aspire 3 A315-53 S.', 'N/A', 'Laptop Charger for Aspire 3 A315-53 S.', '0', '2019-09-01', '2019-09-01 00:00:00', '2025-09-24 11:07:05', '2');
INSERT INTO `item_tb` VALUES ('163', 'MIN0QJT1', 'Mini PC', 'HP', 'HP Prodesk', '8CC1400QJT', 'CPU: i5 10th gen 1.60Ghz\r\nRAM: 16GB DDR4 2667 MHz\r\nROM: 500 GB SSD\r\nOS: Windows 10 pro 64bit\r\n', '0', '2025-09-26', '2025-09-29 08:54:26', '2025-09-29 10:01:22', '6');
INSERT INTO `item_tb` VALUES ('164', 'PC00001', 'PC', 'N/A', 'N/A', 'N/A', 'CPU: I3 4TH GEN 3.70GHz\r\nRAM: 10GB RAM \r\nROM: 500 GB SSD\r\nOS: Windows 10 pro 64 bit', '0', '2020-09-29', '2025-09-29 09:18:56', '2025-09-29 09:22:04', '6');
INSERT INTO `item_tb` VALUES ('165', 'MON71225', 'Monitor', 'N/A', 'N/A', 'EGGC51A007122', 'Old Monitor', '0', '2020-09-29', '2025-09-29 09:21:11', '2025-09-29 09:22:29', '2');


-- --------------------------------------------------------
-- Table structure for table `item_type`
-- --------------------------------------------------------
CREATE TABLE `item_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `item_type`
--
INSERT INTO `item_type` VALUES ('1', 'PC Parts', 'CPU, Motherboard, RAM, HDD/SSD, PSU, GPU, Cooling systems, Cases, Cables');
INSERT INTO `item_type` VALUES ('2', 'Peripherals', 'Monitors, Keyboards, Mice, Printers, Scanners, Webcams, External storage');
INSERT INTO `item_type` VALUES ('3', 'Networking', 'Routers, Switches, Modems, Access Points, Network Cables, NICs');
INSERT INTO `item_type` VALUES ('4', 'Software', 'Operating Systems, Productivity Software, Security Software, Specialized Software');
INSERT INTO `item_type` VALUES ('5', 'Tools', 'Screwdrivers, Tool Kits, Cable Organizers, Antistatic Equipment, UPS, Batteries');
INSERT INTO `item_type` VALUES ('6', 'Devices', 'Laptops, Tablets, Smartphones, External Battery Packs');
INSERT INTO `item_type` VALUES ('7', 'Storage', 'External HDD/SSD, NAS, Backup Tapes, Cloud Storage Subscriptions');


-- --------------------------------------------------------
-- Table structure for table `system_settings`
-- --------------------------------------------------------
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `system_settings`
--
INSERT INTO `system_settings` VALUES ('1', 'low_stock_threshold', '5', '2025-09-26 14:44:12', '2025-09-26 14:44:12');
INSERT INTO `system_settings` VALUES ('2', 'auto_archive_days', '365', '2025-09-26 14:44:12', '2025-09-26 14:44:12');
INSERT INTO `system_settings` VALUES ('3', 'default_item_type', '6', '2025-09-26 14:44:12', '2025-09-26 14:44:12');
INSERT INTO `system_settings` VALUES ('4', 'borrow_limit', '5', '2025-09-26 14:44:12', '2025-09-26 14:44:12');
INSERT INTO `system_settings` VALUES ('5', 'require_approval', '0', '2025-09-26 14:44:12', '2025-09-26 14:44:12');


-- --------------------------------------------------------
-- Table structure for table `transaction_tb`
-- --------------------------------------------------------
CREATE TABLE `transaction_tb` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('borrowed','issued','returned') NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_returned` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `item_id` (`item_id`),
  KEY `transaction_tb_ibfk_2` (`user_id`),
  CONSTRAINT `transaction_tb_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item_tb` (`item_id`),
  CONSTRAINT `transaction_tb_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_tb` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `transaction_tb`
--
INSERT INTO `transaction_tb` VALUES ('43', '106', '105', 'returned', '1', '2025-09-10 09:07:03', '2025-09-10 00:00:00', 'Okay');
INSERT INTO `transaction_tb` VALUES ('44', '96', '120', 'returned', '1', '2025-09-10 09:09:17', '2025-09-10 00:00:00', 'Okay');
INSERT INTO `transaction_tb` VALUES ('45', '99', '48', 'returned', '1', '2025-09-10 09:10:00', '2025-09-11 08:40:38', 'Okayyy');
INSERT INTO `transaction_tb` VALUES ('46', '116', '105', 'issued', '1', '2025-09-10 09:38:35', '0000-00-00 00:00:00', 'Reissuance of Old laptop of Atty Jake ');
INSERT INTO `transaction_tb` VALUES ('47', '117', '105', 'issued', '1', '2025-09-10 09:38:55', '0000-00-00 00:00:00', 'Reissuance of Old laptop of Atty Jake ');
INSERT INTO `transaction_tb` VALUES ('48', '114', '46', 'issued', '1', '2025-09-10 09:39:46', '0000-00-00 00:00:00', 'Reissuance of Old laptop of Mei (Koppel-Sales)');
INSERT INTO `transaction_tb` VALUES ('49', '115', '46', 'issued', '1', '2025-09-10 09:40:11', '0000-00-00 00:00:00', 'Reissuance of Old laptop of Mei (Koppel-Sales)');
INSERT INTO `transaction_tb` VALUES ('52', '127', '180', 'returned', '1', '2025-09-10 17:49:16', '2025-09-11 09:54:08', 'Returned Okay');
INSERT INTO `transaction_tb` VALUES ('54', '129', '180', 'returned', '1', '2025-09-11 09:56:03', '2025-09-11 00:00:00', 'Returned Okay');
INSERT INTO `transaction_tb` VALUES ('57', '51', '105', 'returned', '1', '2016-10-16 13:35:31', '2025-09-11 11:13:43', 'Laptop Replacement');
INSERT INTO `transaction_tb` VALUES ('58', '52', '105', 'returned', '1', '2016-10-16 13:35:31', '2025-09-11 11:13:55', 'Laptop Replacement');
INSERT INTO `transaction_tb` VALUES ('59', '127', '180', 'returned', '1', '2025-09-11 14:07:52', '2025-09-11 16:22:34', 'Okayyy');
INSERT INTO `transaction_tb` VALUES ('60', '133', '60', 'issued', '1', '2025-09-11 17:29:14', '0000-00-00 00:00:00', 'Replacement of OLD Monitor');
INSERT INTO `transaction_tb` VALUES ('61', '132', '181', 'issued', '1', '2025-09-11 17:32:33', '0000-00-00 00:00:00', 'Replacement of OLD Monitor');
INSERT INTO `transaction_tb` VALUES ('62', '135', '182', 'issued', '1', '2025-09-03 07:55:33', '0000-00-00 00:00:00', 'Replacement of Old IP Phone');
INSERT INTO `transaction_tb` VALUES ('63', '134', '183', 'issued', '1', '2025-09-04 07:55:58', '0000-00-00 00:00:00', 'Replacement of Old IP Phone');
INSERT INTO `transaction_tb` VALUES ('64', '112', '180', 'returned', '1', '2025-09-12 17:20:40', '2025-09-15 00:00:00', 'OKay');
INSERT INTO `transaction_tb` VALUES ('65', '113', '180', 'returned', '1', '2025-09-12 17:23:23', '2025-09-15 00:00:00', 'OKay');
INSERT INTO `transaction_tb` VALUES ('66', '100', '185', 'returned', '1', '2025-09-15 07:43:16', '2025-09-15 00:00:00', 'ok');
INSERT INTO `transaction_tb` VALUES ('67', '136', '184', 'issued', '1', '2025-08-15 08:48:16', '0000-00-00 00:00:00', 'New Biometrics');
INSERT INTO `transaction_tb` VALUES ('68', '137', '186', 'issued', '1', '2025-08-15 08:48:16', '0000-00-00 00:00:00', 'New Biometrics');
INSERT INTO `transaction_tb` VALUES ('69', '106', '187', 'returned', '1', '2025-09-15 10:08:56', '2025-09-15 00:00:00', 'OKay');
INSERT INTO `transaction_tb` VALUES ('70', '142', '188', 'returned', '1', '2025-06-05 09:46:53', '2025-09-16 00:00:00', 'Resigned');
INSERT INTO `transaction_tb` VALUES ('71', '143', '188', 'returned', '1', '2025-06-05 09:58:27', '2025-09-16 00:00:00', 'Resigned');
INSERT INTO `transaction_tb` VALUES ('72', '142', '189', 'issued', '1', '2025-09-16 15:47:33', '0000-00-00 00:00:00', 'Reissuance of Laptop');
INSERT INTO `transaction_tb` VALUES ('73', '143', '189', 'issued', '1', '2025-09-16 15:48:21', '0000-00-00 00:00:00', 'Reissuance of Laptop');
INSERT INTO `transaction_tb` VALUES ('74', '112', '190', 'returned', '1', '2025-09-17 08:25:18', '2025-09-19 00:00:00', 'Okay');
INSERT INTO `transaction_tb` VALUES ('75', '86', '180', 'borrowed', '1', '2025-09-19 14:24:48', '0000-00-00 00:00:00', 'Okay');
INSERT INTO `transaction_tb` VALUES ('76', '152', '191', 'issued', '1', '2025-09-22 09:22:13', '0000-00-00 00:00:00', 'Ink Refill for Printer');
INSERT INTO `transaction_tb` VALUES ('77', '144', '152', 'issued', '1', '2025-09-22 16:34:39', '0000-00-00 00:00:00', 'Issued');
INSERT INTO `transaction_tb` VALUES ('78', '147', '152', 'issued', '1', '2025-09-22 16:35:09', '0000-00-00 00:00:00', 'Issued');
INSERT INTO `transaction_tb` VALUES ('79', '145', '192', 'issued', '1', '2025-09-22 16:58:30', '0000-00-00 00:00:00', 'Issued');
INSERT INTO `transaction_tb` VALUES ('80', '106', '193', 'issued', '1', '2025-09-23 09:07:24', '0000-00-00 00:00:00', 'For sir JY Filess');
INSERT INTO `transaction_tb` VALUES ('81', '146', '194', 'issued', '1', '2025-09-23 14:14:38', '0000-00-00 00:00:00', 'Issued to Danica ');
INSERT INTO `transaction_tb` VALUES ('82', '88', '48', 'issued', '1', '2025-09-23 15:04:21', '0000-00-00 00:00:00', 'Used for Afuangs Laptop');
INSERT INTO `transaction_tb` VALUES ('83', '153', '195', 'issued', '1', '2025-09-24 08:52:24', '0000-00-00 00:00:00', 'Replacement of Old Laptop');
INSERT INTO `transaction_tb` VALUES ('84', '159', '195', 'issued', '1', '2025-09-24 08:56:01', '0000-00-00 00:00:00', 'Laptop Charger');
INSERT INTO `transaction_tb` VALUES ('85', '160', '195', 'returned', '1', '2019-09-01 00:00:00', '2025-09-24 00:00:00', 'Laptop Replacement');
INSERT INTO `transaction_tb` VALUES ('86', '161', '195', 'returned', '1', '2019-09-01 00:00:00', '2025-09-24 00:00:00', 'Laptop Replacement');
INSERT INTO `transaction_tb` VALUES ('87', '160', '180', 'issued', '1', '2025-09-24 11:06:50', '0000-00-00 00:00:00', 'New Laptop');
INSERT INTO `transaction_tb` VALUES ('88', '161', '180', 'issued', '1', '2025-09-24 11:07:05', '0000-00-00 00:00:00', 'New Laptop');
INSERT INTO `transaction_tb` VALUES ('89', '129', '180', 'borrowed', '1', '2025-09-26 13:21:07', '0000-00-00 00:00:00', 'Borrow');
INSERT INTO `transaction_tb` VALUES ('90', '151', '48', 'returned', '1', '2025-09-26 14:44:56', '2025-09-26 00:00:00', 'Returned\r\n');
INSERT INTO `transaction_tb` VALUES ('91', '164', '197', 'issued', '1', '2025-09-29 09:22:04', '0000-00-00 00:00:00', 'New Employee');
INSERT INTO `transaction_tb` VALUES ('92', '165', '197', 'issued', '1', '2025-09-29 09:22:29', '0000-00-00 00:00:00', 'New Employee');
INSERT INTO `transaction_tb` VALUES ('93', '84', '197', 'issued', '1', '2025-09-29 09:24:02', '0000-00-00 00:00:00', 'Issue for the new pc');
INSERT INTO `transaction_tb` VALUES ('94', '87', '197', 'issued', '1', '2025-09-29 09:30:43', '0000-00-00 00:00:00', 'New Employee');
INSERT INTO `transaction_tb` VALUES ('95', '85', '198', 'issued', '1', '2025-09-29 10:00:47', '0000-00-00 00:00:00', 'For new PC');
INSERT INTO `transaction_tb` VALUES ('96', '163', '198', 'issued', '1', '2025-09-29 10:01:22', '0000-00-00 00:00:00', 'New PC');
INSERT INTO `transaction_tb` VALUES ('97', '87', '198', 'issued', '1', '2025-09-29 13:17:14', '0000-00-00 00:00:00', 'New PC');


-- --------------------------------------------------------
-- Table structure for table `user_tb`
-- --------------------------------------------------------
CREATE TABLE `user_tb` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `position` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `company` varchar(50) NOT NULL,
  `area` varchar(150) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `date_hired` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_resigned` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(70) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `user_tb`
--
INSERT INTO `user_tb` VALUES ('35', 'BEB091890', 'Barbacena, Eleuterio Buen', 'OIC QA/QC', 'N/A', 'QA/QC', 'HEEC', 'Mandaluyong', 'user', '1990-09-18 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('36', 'VDS032091', 'Varanal, Diomedes Separa', 'OIC Testing & Commissioning', 'N/A', 'Testing & Commissioning', 'HEEC', 'Mandaluyong', 'user', '1991-03-20 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('37', 'PDG010196', 'Pasumbal, Danilo Gonzales', 'Sales Manager', 'N/A', 'A/C Sales', 'HEEC', 'Mandaluyong', 'user', '1996-01-01 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('38', 'TTP092997', 'Tano, Teodomiro Pasquil', 'Technician III', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '1997-09-29 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('39', 'MAS041299', 'Miguel, Allan Singca', 'Technician III - Asst. Leadman', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '1999-04-12 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('40', 'DRG011801', 'Dalit, Rolando Galvez', 'Foreman', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2001-01-18 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('41', 'DMB071805', 'Delfin, Macdonald Bibit', 'Testing & Commissioning Engineer', 'N/A', 'Testing & Commissioning', 'HEEC', 'Mandaluyong', 'user', '2005-07-18 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('42', 'ABB110706', 'Antonio, Bob', 'Technician II', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2006-11-07 00:00:00', '2024-04-05', '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('43', 'AFHS073007', 'Antiporta, Francis Harvey Sandigan', 'Department Coordinator', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2007-07-30 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('44', 'BMD042608', 'Bravo, Marvin Dimaano', 'Technician I', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2008-04-26 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('45', 'MGB042608', 'Maga, Gilbert Bautista', 'Service Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2008-04-26 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('46', 'VMC100308', 'Valerio, Marlon Cristobal', 'Service Coordinator', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2008-10-03 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('47', 'UPC111208', 'Uy, Pinky Chua', 'Service Sales Coordinator', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2008-11-12 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('48', 'AMJB112710', 'Afuang, Mark Jason Bendaña', 'Sr. Technical Engineering Supervisor', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2010-11-27 00:00:00', NULL, '2025-09-10 08:34:40', NULL);
INSERT INTO `user_tb` VALUES ('49', 'JJM082611', 'Jamayo, Jerry', 'Maintenance Engineer', 'N/A', 'Service', 'HEEC', 'Iloilo', 'user', '2011-08-26 00:00:00', '2023-09-30', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('50', 'ACA042015', 'Arsolon, Charlie Abecilla', 'Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2015-04-20 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('51', 'BJR051215', 'Begardon, Jose Rellores', 'Foreman', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2015-05-12 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('52', 'MRL100515', 'Manalo, Raymart Lagamia', 'Service Troubleshooting Supervisor', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2015-10-05 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('53', 'FJA122015', 'Fugaban, Jolito Agub', 'EL/ES Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2015-12-20 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('54', 'MJL032816', 'Malonzo, John Robert Libardo', 'Sales Engr.', 'N/A', 'EL Sales', 'HEEC', 'Mandaluyong', 'user', '2016-03-28 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('55', 'SMS042616', 'Sagun, Marc Joseph Socito', 'Service Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2016-04-26 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('56', 'AAM050416', 'Avila, Archie Miguel', 'Foreman', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2016-05-04 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('57', 'ABP0511162', 'Altura, Bernard', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2016-05-11 00:00:00', '2023-02-10', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('58', 'AJC090516', 'Arguelles, Juan Gerwin Capulso', 'Sr. Service Engineering Supervisor', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2016-09-05 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('59', 'EJM092617', 'Escobar, Jonathan Millare', 'Foreman', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2017-09-26 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('60', 'BMB031218', 'Bernardo, Mary Rose Bautista', 'Sr. Accounting Specialist', 'N/A', 'Accounting', 'HEEC', 'Mandaluyong', 'user', '2018-03-12 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('61', 'IRM060118', 'Infante, Rudy Magistrado', 'EL/ES Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2018-06-01 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('62', 'PEF062618', 'Pura, Eugene Floralde', 'Safety Officer', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2018-06-26 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('63', 'RNB090318', 'Rarugal, Nathaniel Baunsit', 'Jr. Sales Engineer', 'N/A', 'EL Sales', 'HEEC', 'Mandaluyong', 'user', '2018-09-03 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('64', 'LPL102318', 'Lee, Paul Rosell', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2018-10-23 00:00:00', '2023-03-27', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('65', 'MJT010719', 'Mendoza, Jonathan Calixto Taña', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2019-01-07 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('66', 'BKV031819', 'Bernardino, Kristian Valencia', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2019-03-18 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('67', 'SRV062619', 'Soria, Rowell Vingno', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2019-06-26 00:00:00', '2024-05-10', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('68', 'SGG070419', 'Sael, Gerrine Galendez', 'Messenger/Collector', 'N/A', 'Accounting', 'HEEC', 'Mandaluyong', 'user', '2019-07-04 00:00:00', '2024-06-30', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('69', 'DGM022620', 'De Ocampo, Gary Marqueses', 'Sales Admin', 'N/A', 'EL Sales', 'HEEC', 'Mandaluyong', 'user', '2020-02-26 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('70', 'DAD022720', 'Delos Reyes, Andre Patrick Dela Cruz', 'Inventory Assistant', 'N/A', 'Accounting', 'HEEC', 'Mandaluyong', 'user', '2020-02-27 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('71', 'KIC112320', 'Kalacas, Idriss Kyle', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2020-11-23 00:00:00', '2023-03-04', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('72', 'PIB121720', 'Pasculado, Jr., Isagani Bermudo', 'EL/ES Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2020-12-17 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('73', 'LJHB091421', 'Lawenko, Jose Hilario Berango', 'Sales Manager', 'N/A', 'EL Sales', 'HEEC', 'Mandaluyong', 'user', '2021-09-14 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('74', 'MAM030122', 'Mallorca, Arjay', 'Operations Manager', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2022-03-01 00:00:00', '2023-08-10', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('75', 'MKC032222', 'Moreto, Kim Harold Cuevas', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2022-03-22 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('76', 'CJE041822', 'Colango, Jemuel Ian', 'Service Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2022-04-18 00:00:00', '2022-12-28', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('77', 'CEC041822', 'Cunanan, Ezequiel Sedgwig', 'Project Engineer / AutoCad Operator', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2022-04-18 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('78', 'PMF041822', 'Paragao, Mark Rolan', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2022-04-18 00:00:00', '2023-02-03', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('79', 'TJZ041822', 'Torres, Joshua', 'Service Technician', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2022-04-18 00:00:00', '2024-01-16', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('80', 'MDT080222', 'Mamalayan, Danica Lyra Tiangco', 'Accounting Assistant', 'N/A', 'Accounting', 'HEEC', 'Mandaluyong', 'user', '2022-08-02 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('81', 'CJD110222', 'Castro, Junith Donio', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2022-11-02 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('82', 'DZR022223', 'De Villa, Zionlee Zicri Raguro', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2023-02-22 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('83', 'YMM032123', 'Yniguez, Marco Vincent', 'Jr. Project Engineer / AutoCad Operator', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2023-03-01 00:00:00', '2023-06-16', '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('84', 'YRV030623', 'Yulas, Ricky Voces', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2023-03-06 00:00:00', NULL, '2025-09-10 08:36:33', NULL);
INSERT INTO `user_tb` VALUES ('85', 'RMC032123', 'Rosales, Miguel Alfonso Constantino', 'Jr. Project Engineer/AutoCAD Operator', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2023-03-21 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('86', 'MDN032723', 'Melendres, Daniel Nocidal', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2023-03-27 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('87', 'DJA061323', 'Dellova, Jesse Lemuel', 'Jr. Testing & Commissioning Engineer', 'N/A', 'Testing & Commissioning', 'HEEC', 'Mandaluyong', 'user', '2023-06-13 00:00:00', '2024-02-14', '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('88', 'NWD071023', 'Natividad, Wilson Dulatre', 'Testing & Commissioning Sup/Project Engr.', 'N/A', 'Testing & Commissioning', 'HEEC', 'Mandaluyong', 'user', '2023-07-10 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('89', 'BVG071823', 'Briones, Vince Joseph Gargoles', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2023-07-18 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('90', 'FMP071823', 'Francisco, Mark Gerald', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2023-07-18 00:00:00', '2024-04-01', '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('91', 'BJB080123', 'Barrio, Jodiel', 'Jr. Project Engineer', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2023-08-01 00:00:00', '2024-04-04', '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('92', 'MLD091323', 'Matavia, Lorenel Destriza', 'EL Installer', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2023-09-13 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('93', 'CGL101623', 'Caramat, Gabrielle Keneth Lim', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2023-10-16 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('94', 'EGB101623', 'Esquivel, Gerick Bayudang', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'Mandaluyong', 'user', '2023-10-16 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('95', 'LJL101623', 'Lunar, John Russell Lavarez', 'Jr. Testing & Commissioning Engineer', 'N/A', 'Testing & Commissioning', 'HEEC', 'Mandaluyong', 'user', '2023-10-16 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('96', 'MVT101623', 'Mina, Vergilio', 'Jr. Testing & Commissioning Engineer', 'N/A', 'Testing & Commissioning', 'HEEC', 'Mandaluyong', 'user', '2023-10-16 00:00:00', '2024-02-24', '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('97', 'BMP111623', 'Bantog, Mark Anthony', 'EL Installer', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2023-11-16 00:00:00', '2024-01-03', '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('98', 'NJA112023', 'Nabora, Jeffrey Altares', 'Service Technician', 'N/A', 'Operations', 'HEEC', 'Mandaluyong', 'user', '2023-11-20 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('99', 'TVT040124', 'Tolentino, Van Dham Tolentino', 'Jr. Testing & Commissioning Engineer', 'N/A', 'Testing & Commissioning', 'HEEC', 'Mandaluyong', 'user', '2024-04-01 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('100', 'CJD041124', 'Cordero, John Carl Victor De Asis', 'Sales Engineer', 'N/A', 'EL Sales', 'HEEC', 'Iloilo', 'user', '2024-04-11 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('101', 'CRR041124', 'Cuyos, Renz Marion Rosell', 'Sales Engineer', 'N/A', 'EL Sales', 'HEEC', 'Cebu', 'user', '2024-04-11 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('102', 'FEA090897', 'Fajardo, Edgar Angeles', 'Operations Manager', 'N/A', 'A/C Operations', 'HI-AIRE', 'Mandaluyong', 'user', '1997-09-08 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('103', 'ONE091604', 'Olazo, Nelson Edang', 'A/C Leadman', 'N/A', 'A/C Operations', 'HI-AIRE', 'Mandaluyong', 'user', '2004-09-16 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('104', 'MFI072805', 'Macaraig, Fortunato Ibon', 'Collector', 'N/A', 'Accounting', 'HI-AIRE', 'Mandaluyong', 'user', '2005-07-28 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('105', 'SRJO110705', 'Sangil, Ronaldo Joseph Olayao', 'Sales Officer', 'N/A', 'A/C Sales', 'HI-AIRE', 'Mandaluyong', 'user', '2005-11-07 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('106', 'MRL010606', 'Martin, Reynaldo Lumugdang', 'A/C Technician', 'N/A', 'A/C Service', 'HI-AIRE', 'Mandaluyong', 'user', '2006-01-06 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('107', 'RWB020208', 'Relevo, Willy Barrios', 'Sr. Project Engineer I', 'N/A', 'A/C Operations', 'HI-AIRE', 'Mandaluyong', 'user', '2008-02-02 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('108', 'DSD060609', 'Deuna, Sandy Deocareza', 'A/C Ductman/Fabricator', 'N/A', 'A/C Operations', 'HI-AIRE', 'Mandaluyong', 'user', '2009-06-06 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('109', 'DMP091779', 'Diangson, Marivic Parilla', 'Corporate Secretary/Purchasing Manager', 'N/A', 'EO/Purchasing', 'HIMC', 'Mandaluyong', 'user', '1979-09-17 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('110', 'HMJ080288', 'Hernandez, Marcelo Jabeguero', 'T & D Helper', 'N/A', 'Traffic', 'HIMC', 'Mandaluyong', 'user', '1988-08-02 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('111', 'PJA020193', 'Pasumbal, Juliet Antonio', 'Executive Secretary/OIC Logistics', 'N/A', 'Executive Office', 'HIMC', 'Mandaluyong', 'user', '1993-02-01 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('112', 'PCS040193', 'Phi, Christina Sy', 'Credit & Collection/Service Contracts & Parts Manager', 'N/A', 'Accounting', 'HIMC', 'Mandaluyong', 'user', '1993-04-01 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('113', 'LJG040112', 'Lape, Josefina Geraldez', 'General Manager', 'N/A', 'HEEC Sales', 'HIMC', 'Mandaluyong', 'user', '1994-05-16 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('114', 'PJM111695', 'PulumbariT, Jonah Mauricio', 'Sales Secretary', 'N/A', 'A/C Sales', 'HIMC', 'Mandaluyong', 'user', '1995-11-16 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('115', 'UAR111696', 'Ubongen, Angel Rapadas', 'Warehouse Checker', 'N/A', 'Warehouse', 'HIMC', 'Taguig', 'user', '1996-11-16 00:00:00', NULL, '2025-09-10 08:39:55', NULL);
INSERT INTO `user_tb` VALUES ('116', 'VNT061697', 'Vecina, Norman Tolidana', 'T & D Helper', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '1997-06-16 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('117', 'BFD092199', 'Bo, Ferdinand Dacay', 'T & D Helper', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '1999-09-21 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('118', 'RMP101299', 'Retuerma, Manuelito Padaon', 'T & D Driver', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '1999-10-12 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('119', 'HADG100104', 'Hernandez, Ariel De Guzman', 'Warehouseman', 'N/A', 'Warehouse', 'HIMC', 'MANDALUYONG', 'user', '2004-10-01 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('120', 'DGMD041105', 'Deles, Garry Melvin Diarota', 'Asst. Sales Manager', 'N/A', 'A/C Sales', 'HIMC', 'MANDALUYONG', 'user', '2005-04-11 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('121', 'FMB041205', 'Francisco, Mel Burce', 'Forklift Operator', 'N/A', 'Warehouse', 'HIMC', 'TAGUIG', 'user', '2005-04-12 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('122', 'RAM051305', 'Revilloza, Aristrothel Meron', 'T & D Driver', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '2005-05-13 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('123', 'AGO112105', 'Adano, Gered Ombrog', 'T & D Driver', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '2005-11-21 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('124', 'LCC111208', 'Labian, Cornelio Cabilin', 'T & D Driver', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '2008-11-12 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('125', 'ALAP041309', 'Fabito, Leslie Ann Andrade', 'Purchasing Assistant', 'N/A', 'Purchasing', 'HIMC', 'MANDALUYONG', 'user', '2009-04-13 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('126', 'AND012910', 'Apostol, Nenita Domdom', 'Credit & Collection Assistant', 'N/A', 'Accounting', 'HIMC', 'MANDALUYONG', 'user', '2010-01-29 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('127', 'DEP021910', 'Cruz, Evangeline', 'Sales Engineer', 'N/A', 'Sales', 'HIMC', 'MANDALUYONG', 'user', '2010-02-19 00:00:00', '2023-09-25', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('128', 'ACI022610', 'Torrejos, Christine Aguzar', 'HR Supervisor', 'N/A', 'HR/Admin', 'HIMC', 'MANDALUYONG', 'user', '2010-02-26 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('129', 'ATC050413', 'Andag, Tracy Caballero', 'Accounting Assistant', 'N/A', 'Accounting', 'HIMC', 'MANDALUYONG', 'user', '2013-05-04 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('130', 'FMG062413', 'Fusilero, Melvin Gustoir', 'Sr. A/C Design & Estimate Engineer', 'N/A', 'Engineering', 'HIMC', 'MANDALUYONG', 'user', '2013-06-24 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('131', 'JCA052114', 'Javier, Cheryllyn Antalan', 'Credit & Collection Assistant', 'N/A', 'Accounting', 'HIMC', 'MANDALUYONG', 'user', '2014-05-21 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('132', 'RDK081116', 'Refuerzo, Didith', 'Accounting Supervisor', 'N/A', 'Finance', 'HIMC', 'MANDALUYONG', 'user', '2016-08-11 00:00:00', '2023-05-03', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('133', 'MRD101117', 'Molato, Rogy Dayo', 'A/C Leadman', 'N/A', 'A/C Operations', 'HIMC', 'MANDALUYONG', 'user', '2017-10-11 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('134', 'PJC021918', 'Pascua, Jimmy Boy', 'AutoCad Operator', 'N/A', 'Engineering', 'HIMC', 'MANDALUYONG', 'user', '2018-02-19 00:00:00', '2023-06-10', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('135', 'BMM060418', 'Baluyot, Mhon Azel Marquez', 'Warehouse Assistant', 'N/A', 'Warehouse', 'HIMC', 'MANDALUYONG', 'user', '2018-06-04 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('136', 'DRR073018', 'Diaz, Rowelyn Requina', 'Accounting Assistant-AP', 'N/A', 'Accounting', 'HIMC', 'MANDALUYONG', 'user', '2018-07-30 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('137', 'BAM090318', 'Bautista, Anne Grace Mabalot', 'HR Assistant', 'N/A', 'HR/Admin', 'HIMC', 'MANDALUYONG', 'user', '2018-09-03 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('138', 'GJT100118', 'Gutierrez, Jamaica Tabile', 'General Accountant', 'N/A', 'Accounting', 'HIMC', 'MANDALUYONG', 'user', '2018-10-01 00:00:00', '2024-05-03', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('139', 'GJC060719', 'Gayon, John Cedrick Coronacion', 'A/C Installer', 'N/A', 'A/C Operations', 'HIMC', 'MANDALUYONG', 'user', '2019-06-07 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('140', 'MRM060719', 'Mahinay, Richard Mancao', 'A/C Installer', 'N/A', 'A/C Operations', 'HIMC', 'MANDALUYONG', 'user', '2019-06-07 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('141', 'FEG062619', 'Laporie, Elyza Leah Fullente', 'Accounting Assistant', 'N/A', 'Accounting', 'HIMC', 'MANDALUYONG', 'user', '2019-06-26 00:00:00', '2024-02-29', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('142', 'FWU070819', 'Fortuno, Whillem Usero', 'Purchasing Assistant', 'N/A', 'Purchasing', 'HIMC', 'MANDALUYONG', 'user', '2019-07-08 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('143', 'DGB0041221', 'Derano, Gerry Berdera', 'Import Assistant', 'N/A', 'Import', 'HIMC', 'MANDALUYONG', 'user', '2021-04-12 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('144', 'MG092021', 'Mainot, Genevieve', 'Office Assistant', 'N/A', 'Finance', 'HIMC', 'MANDALUYONG', 'user', '2021-09-20 00:00:00', '2023-07-07', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('145', 'JJD101821', 'Jocson, John Jordan Dionisio', 'Jr. Project Engineer', 'N/A', 'A/C Operations', 'HIMC', 'MANDALUYONG', 'user', '2021-10-18 00:00:00', '2023-08-17', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('146', 'RJF112521', 'Reyes, Jenica Fabricante', 'Receptionist/Purchasing Assistant', 'N/A', 'Purchasing', 'HIMC', 'MANDALUYONG', 'user', '2021-11-25 00:00:00', '2024-05-30', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('147', 'DJL120921', 'De Alday, Jhonver', 'Jr. Sales Engineer', 'N/A', 'Sales', 'HIMC', 'MANDALUYONG', 'user', '2021-12-09 00:00:00', '2023-03-14', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('148', 'EMT022322', 'Evardo, Marino Torrejas', 'Inventory Assistant', 'N/A', 'Accounting', 'HIMC', 'MANDALUYONG', 'user', '2022-02-23 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('149', 'PFC041822', 'Pitogo, Frederick Cruz', 'T & D Driver', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '2022-04-18 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('150', 'LRC052622', 'Lopez, Ryan Cuevas', 'A/C Installer', 'N/A', 'A/C Operations', 'HIMC', 'MANDALUYONG', 'user', '2022-05-26 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('151', 'VAT052622', 'Villas, Amber Archie', 'AC Installer', 'N/A', 'Operations', 'HIMC', 'MANDALUYONG', 'user', '2022-05-26 00:00:00', '2024-01-08', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('152', 'MJM032123', 'Moyo, John Albert Masayda', 'Jr. Sales Engineer 1', 'N/A', 'A/C Sales', 'HIMC', 'MANDALUYONG', 'user', '2023-03-21 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('153', 'RPC062623', 'Rodriguez, Pauline Joy Catura', 'Office Assistant', 'N/A', 'Import', 'HIMC', 'MANDALUYONG', 'user', '2023-06-26 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('154', 'DKM091923', 'Dela Cruz, Kim', 'AutoCad Operator', 'N/A', 'Engineering', 'HIMC', 'MANDALUYONG', 'user', '2023-09-09 00:00:00', '2024-01-31', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('155', 'ARB091123', 'Arquiza, Ritchie Barreras', 'Logistic Assistant/Driver', 'N/A', 'Traffic', 'HIMC', 'MANDALUYONG', 'user', '2023-09-11 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('156', 'DSD101123', 'De Leon, Sheyna April Dullano', 'Jr. Sales Engineer 1', 'N/A', 'A/C Sales', 'HIMC', 'MANDALUYONG', 'user', '2023-10-11 00:00:00', '2024-05-10', '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('157', 'JAC101123', 'Junio, Abegail Claro', 'Junior QA/QC Engineer', 'N/A', 'EL QA/QC', 'HIMC', 'MANDALUYONG', 'user', '2023-10-11 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('158', 'GLF042224', 'Gonzales, Lovely Ness Fernandez', 'AutoCad Operator', 'N/A', 'Engineering', 'HIMC', 'MANDALUYONG', 'user', '2024-04-22 00:00:00', NULL, '2025-09-10 08:47:13', NULL);
INSERT INTO `user_tb` VALUES ('159', 'GET110380', 'Guanzon, Elizabeth', 'Sales Secretary', 'N/A', 'Sales', 'HIMC', 'MANDALUYONG', 'user', '1980-11-03 00:00:00', '2014-03-25', '2025-09-10 08:56:05', NULL);
INSERT INTO `user_tb` VALUES ('160', 'RFG102912', 'Reyes, Francis Jim', 'Sales Engineer', 'N/A', 'Sales', 'HIMC', 'MANDALUYONG', 'user', '2012-10-29 00:00:00', '2014-06-03', '2025-09-10 08:56:05', NULL);
INSERT INTO `user_tb` VALUES ('161', 'PNE111109', 'Pedracio, Nieves', 'Accounting Supervisor', 'N/A', 'Finance', 'HIMC', 'MANDALUYONG', 'user', '2009-11-11 00:00:00', '2014-06-22', '2025-09-10 08:56:05', NULL);
INSERT INTO `user_tb` VALUES ('162', 'TJA010709', 'Tam, Joseph', 'Messenger/Collector', 'N/A', 'Finance', 'HEEC', 'MANDALUYONG', 'user', '2009-01-07 00:00:00', '2013-07-08', '2025-09-10 08:56:05', NULL);
INSERT INTO `user_tb` VALUES ('163', 'BWC112710', 'Bautista, William', 'Jr. Service Engineer', 'N/A', 'Service', 'HEEC', 'MANDALUYONG', 'user', '2010-11-27 00:00:00', '2013-08-23', '2025-09-10 08:56:05', NULL);
INSERT INTO `user_tb` VALUES ('164', 'BMC061913', 'Bolleno, Madelle May', 'Receptionist/HR Assistant', 'N/A', 'HR', 'HIMC', 'MANDALUYONG', 'user', '2013-06-19 00:00:00', '2013-09-10', '2025-09-10 08:56:05', NULL);
INSERT INTO `user_tb` VALUES ('165', 'VRJC091211', 'Villaruz, Ryan Jozef', 'Sales Engineer', 'sample@koppel.ph', 'Sales', 'HEEC', 'MANDALUYONG', 'user', '2025-09-10 11:06:43', '2013-11-30', '2025-09-10 08:56:05', NULL);
INSERT INTO `user_tb` VALUES ('180', 'IT101424', 'IT Admin', 'IT Admin', 'it@koppel.ph', 'IT', 'Koppel', 'MANDALUYONG', 'admin', '2025-09-12 16:24:03', NULL, '2025-09-10 17:48:47', '$2y$10$Qy8LrF6xQAbJjZQo7WrkDudJQ9H6uFTxC0qk/Yp56cIrB5OeSxGbq');
INSERT INTO `user_tb` VALUES ('181', 'MAV071524', 'Angel Malazzab', 'Purchasing Assistant', 'N/A', 'Purchasing ', 'HIMC', 'MANDALUYONG', 'user', '2024-07-15 00:00:00', NULL, '2025-09-11 17:31:38', NULL);
INSERT INTO `user_tb` VALUES ('182', '25-8230', 'Sander Mike Rondilla', 'Customer Support', 'N/A', 'Service', 'Koppel', 'Mandaluyong', 'user', '2025-09-12 07:50:36', NULL, '2025-09-12 07:47:57', NULL);
INSERT INTO `user_tb` VALUES ('183', '34241', 'Laarni Pacayra', 'Customer Support', 'N/A', 'Service', 'Koppel', 'Mandaluyong', 'user', '2025-09-12 07:50:27', NULL, '2025-09-12 07:49:41', NULL);
INSERT INTO `user_tb` VALUES ('184', 'QJT111323', 'Jane Quinquiño', 'Accounting Assistant', 'N/A', 'Accounting', 'Koppel', 'CDO', 'user', '2023-11-13 00:00:00', NULL, '2025-09-12 08:12:11', NULL);
INSERT INTO `user_tb` VALUES ('185', 'CMR', 'Ralph Capinig', 'Digital Marketing', '-', 'Marketing', 'Koppel', 'Mandaluyong', 'user', '2024-10-14 00:00:00', NULL, '2025-09-15 07:42:44', NULL);
INSERT INTO `user_tb` VALUES ('186', 'AGS102610', 'G Apura', 'Branch Sales Manager', '-', 'Sales', 'Koppel', 'Iloilo', 'user', '2010-10-26 00:00:00', NULL, '2025-09-15 08:45:12', NULL);
INSERT INTO `user_tb` VALUES ('187', 'N/A', 'Alfonso Bleza', 'Corporate Attorney', '-', 'Legal', 'Koppel', 'Mandaluyong', 'user', '2025-09-15 10:07:47', NULL, '2025-09-15 10:07:47', NULL);
INSERT INTO `user_tb` VALUES ('188', 'MRR071523', 'Rovic Macabalo', 'Technical Engineer', '-', 'Refrigeration', 'Koppel', 'Mandaluyong', 'user', '2023-07-15 00:00:00', NULL, '2025-09-16 09:46:18', NULL);
INSERT INTO `user_tb` VALUES ('189', 'TEG091225', 'Earl Garret G. Tekiko', 'Design Engineer', '-', 'Refrigeration', 'Koppel', 'Mandaluyong', 'user', '2025-09-12 00:00:00', NULL, '2025-09-16 15:46:53', NULL);
INSERT INTO `user_tb` VALUES ('190', 'N/A', 'Alvin Domingo', 'VRF Manager', '', 'VRF', 'Koppel', 'Mandaluyong', 'user', '2025-09-17 08:24:43', NULL, '2025-09-17 08:24:43', NULL);
INSERT INTO `user_tb` VALUES ('191', 'N/A', 'Anna Marie Basa', 'Manager', '', 'CNC', 'Koppel', 'Mandaluyong', 'user', '2025-09-22 09:21:35', NULL, '2025-09-22 09:21:35', NULL);
INSERT INTO `user_tb` VALUES ('192', 'N/A', 'Miguel Rosales', 'Design Engineer', '-', 'Elevators', 'HEEC', 'Mandaluyong', 'user', '2025-09-22 16:58:08', NULL, '2025-09-22 16:58:00', NULL);
INSERT INTO `user_tb` VALUES ('193', 'N/A', 'Kimberly Yu', 'IT Director', 'kyu@koppel.ph', 'IT & Purchasing', 'Koppel', 'Mandaluyong', 'user', '2025-09-23 09:07:00', NULL, '2025-09-23 09:07:00', NULL);
INSERT INTO `user_tb` VALUES ('194', 'N/A', 'Danica Villamor', '-', '-', 'HI-AIRE', 'HI-AIRE', 'Mandaluyong', 'user', '2025-09-23 14:14:12', NULL, '2025-09-23 14:14:12', NULL);
INSERT INTO `user_tb` VALUES ('195', 'DJT111109', 'JC Dacuycuy', 'Assistant Manager', '-', 'Sales', 'Koppel', 'Mandaluyong', 'user', '2009-11-11 00:00:00', NULL, '2025-09-24 08:51:44', NULL);
INSERT INTO `user_tb` VALUES ('197', 'GWC-00005', 'Rosemarie Larana', 'Parts Admin', '-', 'Service', 'Koppel', 'Mandaluyong', 'user', '2025-10-15 00:00:00', NULL, '2025-09-29 09:13:41', NULL);
INSERT INTO `user_tb` VALUES ('198', 'CGP102925', 'Gabriel Nikko Calabano', 'Parts Analyst', '-', 'Service', 'Koppel', 'Mandaluyong', 'user', '2025-10-29 00:00:00', NULL, '2025-09-29 09:14:13', NULL);


-- Backup completed successfully

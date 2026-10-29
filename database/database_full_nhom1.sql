-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: helpdesk_thudo
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `helpdesk_thudo`
--

/*!40000 DROP DATABASE IF EXISTS `helpdesk_thudo`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `helpdesk_thudo` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `helpdesk_thudo`;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `default_priority_code` varchar(20) NOT NULL DEFAULT 'MED',
  `sla_hours` decimal(4,1) NOT NULL DEFAULT 4.0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'CAT_SSO','Tài khoản & Xác thực SSO','Cấp lại mật khẩu, mở khóa tài khoản, đồng bộ định danh sinh viên/cán bộ','MED',2.0,1,'2026-10-23 16:01:57'),(2,'CAT_MAIL','Email Trường & Microsoft 365','Kích hoạt Microsoft 365 A3, khôi phục hòm thư @daihocthudo.edu.vn, mở rộng OneDrive 1TB','MED',4.0,1,'2026-10-23 16:01:57'),(3,'CAT_QLDT','Cổng Quản lý Đào tạo (QLĐT)','Sự cố cổng đăng ký tín chỉ, tra cứu lịch thi, phân quyền nhập điểm cho giảng viên','HIGH',2.0,1,'2026-10-23 16:01:57'),(4,'CAT_CANVAS','Hệ thống Học tập (Canvas & Teams)','Lỗi không thấy môn học, sự cố nộp bài tập lớn/tiểu luận, tạo phòng học trực tuyến Teams','HIGH',2.0,1,'2026-10-23 16:01:57'),(5,'CAT_WIFI','Wi-Fi Trường & VPN Từ xa','Xác thực mạng THUDO-UNI, cấp phát IP Ký túc xá, cấu hình VPN truy cập Thư viện số từ xa','MED',4.0,1,'2026-10-23 16:01:57');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_students`
--

DROP TABLE IF EXISTS `class_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_students` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `student_id` int(10) unsigned NOT NULL,
  `enrolled_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_student` (`class_id`,`student_id`),
  KEY `fk_class_students_student` (`student_id`),
  CONSTRAINT `fk_class_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_class_students_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_students`
--

LOCK TABLES `class_students` WRITE;
/*!40000 ALTER TABLE `class_students` DISABLE KEYS */;
INSERT INTO `class_students` VALUES (1,1,13,'2026-10-23 16:01:57'),(2,1,14,'2026-10-23 16:01:57'),(3,1,15,'2026-10-23 16:01:57'),(4,1,16,'2026-10-23 16:01:57'),(5,1,17,'2026-10-23 16:01:57'),(6,1,19,'2026-10-23 16:01:57'),(7,3,18,'2026-10-23 16:01:57');
/*!40000 ALTER TABLE `class_students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cohort` varchar(20) NOT NULL DEFAULT 'D24',
  `faculty` varchar(100) NOT NULL DEFAULT 'Khoa Công nghệ Thông tin',
  `academic_year` varchar(30) NOT NULL DEFAULT '2024-2028',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_classes_code` (`class_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,'D24-CNTT1','Lớp Công nghệ Thông tin D24-1','D24','Khoa Công nghệ Thông tin','2024-2028','2026-10-23 16:01:57'),(2,'D24-CNTT2','Lớp Công nghệ Thông tin D24-2','D24','Khoa Công nghệ Thông tin','2024-2028','2026-10-23 16:01:57'),(3,'D20-CNTT1','Lớp Công nghệ Thông tin D20-1 (Đã tốt nghiệp)','D20','Khoa Công nghệ Thông tin','2020-2024','2026-10-23 16:01:57');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_assignments`
--

DROP TABLE IF EXISTS `course_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_assignments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `course_id` int(10) unsigned NOT NULL,
  `lecturer_id` int(10) unsigned NOT NULL,
  `semester` varchar(20) NOT NULL DEFAULT '2026_1',
  `academic_year` varchar(20) NOT NULL DEFAULT '2026-2027',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_course_class_semester` (`class_id`,`course_id`,`semester`),
  KEY `fk_course_assign_course` (`course_id`),
  KEY `fk_course_assign_lecturer` (`lecturer_id`),
  CONSTRAINT `fk_course_assign_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_course_assign_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_course_assign_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_assignments`
--

LOCK TABLES `course_assignments` WRITE;
/*!40000 ALTER TABLE `course_assignments` DISABLE KEYS */;
INSERT INTO `course_assignments` VALUES (1,1,1,7,'2026_1','2026-2027','2026-10-23 16:01:57'),(2,1,2,8,'2026_1','2026-2027','2026-10-23 16:01:57'),(3,1,3,9,'2026_1','2026-2027','2026-10-23 16:01:57'),(4,1,4,10,'2026_1','2026-2027','2026-10-23 16:01:57'),(5,1,5,11,'2026_1','2026-2027','2026-10-23 16:01:57'),(6,1,6,12,'2026_1','2026-2027','2026-10-23 16:01:57'),(7,2,1,7,'2026_1','2026-2027','2026-10-23 16:01:57');
/*!40000 ALTER TABLE `course_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `credits` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `department` varchar(100) NOT NULL DEFAULT 'Bộ môn Công nghệ Phần mềm',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_courses_code` (`course_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'WEB101','Lập trình Web & Ứng dụng',3,'Bộ môn Công nghệ Phần mềm','2026-10-23 16:01:57'),(2,'NET201','Quản trị Mạng & An toàn Hệ thống',3,'Bộ môn Mạng & Truyền thông','2026-10-23 16:01:57'),(3,'DB102','Hệ Quản trị Cơ sở Dữ liệu',3,'Bộ môn Hệ thống Thông tin','2026-10-23 16:01:57'),(4,'OS101','Hệ điều hành & Mã nguồn mở',3,'Bộ môn Kỹ thuật Máy tính','2026-10-23 16:01:57'),(5,'SE201','Kỹ nghệ & Quy trình Phát triển Phần mềm',3,'Bộ môn Công nghệ Phần mềm','2026-10-23 16:01:57'),(6,'LMS301','Hệ thống E-Learning & Chuyển đổi số',2,'Trung tâm CNTT & Truyền thông','2026-10-23 16:01:57');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_holidays_created_by` (`created_by`),
  KEY `idx_holidays_dates` (`start_date`,`end_date`),
  CONSTRAINT `fk_holidays_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,'Tết Dương Lịch 2026','2026-01-01','2026-01-01',1,1,'2026-10-23 16:01:57'),(2,'Tết Nguyên Đán Bính Ngọ 2026','2026-02-14','2026-02-23',0,1,'2026-10-23 16:01:57'),(3,'Giỗ Tổ Hùng Vương (10/3 Âm lịch)','2026-04-26','2026-04-26',1,1,'2026-10-23 16:01:57'),(4,'Ngày Giải phóng Miền Nam & Quốc tế Lao động','2026-04-30','2026-05-01',1,1,'2026-10-23 16:01:57'),(5,'Quốc khánh nước CHXHCN Việt Nam','2026-09-01','2026-09-03',1,1,'2026-10-23 16:01:57');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(190) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_password_resets_user` (`user_id`),
  KEY `idx_token_lookup` (`token_hash`,`expires_at`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (10,14,'224001729@daihocthudo.edu.vn','2a120118945989e7dcde81c13bf889438f54650086e46c1e5148518ba84dd2f1','2026-10-29 17:46:43','2026-10-29 23:31:43'),(18,15,'224001737@daihocthudo.edu.vn','f4d6118a8305a5fda9c432582e4846b26349f9692818e37b8692f31e3be896f7','2026-10-29 18:58:19','2026-10-30 00:43:19');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `priorities`
--

DROP TABLE IF EXISTS `priorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `priorities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sla_factor` decimal(3,2) NOT NULL DEFAULT 1.00,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_priorities_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `priorities`
--

LOCK TABLES `priorities` WRITE;
/*!40000 ALTER TABLE `priorities` DISABLE KEYS */;
INSERT INTO `priorities` VALUES (1,'URGENT','Khẩn cấp',0.25,1),(2,'HIGH','Cao',0.50,2),(3,'MED','Trung bình',1.00,3),(4,'LOW','Thấp',1.50,4);
/*!40000 ALTER TABLE `priorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_ratings`
--

DROP TABLE IF EXISTS `ticket_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_ratings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_ratings_ticket` (`ticket_id`),
  KEY `fk_ticket_ratings_user` (`user_id`),
  CONSTRAINT `fk_ticket_ratings_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_ratings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_ticket_ratings_score` CHECK (`score` between 1 and 5)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_ratings`
--

LOCK TABLES `ticket_ratings` WRITE;
/*!40000 ALTER TABLE `ticket_ratings` DISABLE KEYS */;
INSERT INTO `ticket_ratings` VALUES (1,5,17,5,'KTV hỗ trợ rất nhanh và nhiệt tình, Wi-Fi phòng KTX mình đã bắt được sóng khỏe.','2026-10-21 11:55:00'),(2,8,13,4,'','2026-10-29 18:21:57'),(3,9,13,3,'','2026-10-29 22:03:03');
/*!40000 ALTER TABLE `ticket_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_replies`
--

DROP TABLE IF EXISTS `ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ticket_replies_user` (`user_id`),
  KEY `idx_ticket_replies` (`ticket_id`,`is_internal`,`created_at`),
  CONSTRAINT `fk_ticket_replies_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_replies_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_replies`
--

LOCK TABLES `ticket_replies` WRITE;
/*!40000 ALTER TABLE `ticket_replies` DISABLE KEYS */;
INSERT INTO `ticket_replies` VALUES (1,3,2,'Chào bạn Linh, KTV đã nhận được ảnh thẻ sinh viên TUD D24 của bạn và đang đối chiếu với CSDL Đào tạo.',NULL,0,'2026-10-23 08:40:00'),(2,3,2,'Ghi chú nội bộ: Đã đối chiếu CCCD khớp hồ sơ tuyển sinh D24. Sẽ cấp pass tạm 123456 và gửi về tranlinh.personal@gmail.com.',NULL,1,'2026-10-23 08:42:00'),(3,4,4,'KTV đã kiểm tra văn bản phê duyệt của P.ĐT và mở quyền nộp điểm cho thầy đến hết 17h00 ngày 23/10.',NULL,0,'2026-10-22 14:25:00'),(4,5,6,'KTV đã giải phóng lease IP tại tầng 3 KTX Nhà B. Bạn quên mạng cũ và kết nối lại theo chuẩn PEAP/MSCHAPv2 nhé.',NULL,0,'2026-10-21 11:30:00'),(5,3,14,'oke ạ',NULL,0,'2026-10-29 08:51:13'),(6,3,2,'KTV gửi ảnh minh chứng đã cấu hình thành công mở khóa trên hệ thống.','uploads/replies/reply_3_test_1793239303.png',0,'2026-10-29 09:01:43'),(7,3,2,'KTV gửi ảnh minh chứng đã cấu hình thành công mở khóa trên hệ thống.','uploads/replies/reply_3_test_1793239324.png',0,'2026-10-29 09:02:04'),(8,3,2,'ddggg',NULL,0,'2026-10-29 09:04:19'),(9,3,14,'ggasaagsfsgffggg',NULL,0,'2026-10-29 09:04:45'),(10,1,5,'đã chỉnh sửa lại ngày',NULL,0,'2026-10-29 09:15:28'),(11,1,1,'đã giải quyết',NULL,0,'2026-10-29 09:17:36'),(12,7,2,'✅ [BIÊN BẢN NGHIỆM THU GIẢI PHÁP KỸ THUẬT]:\nsfasff',NULL,0,'2026-10-29 09:23:10'),(13,3,5,'KTV đã cấu hình xong tài khoản Canvas LMS cho bạn, hệ thống hoạt động bình thường.',NULL,0,'2026-10-29 09:38:02'),(14,3,5,'Xác nhận đóng hoàn tất phiếu yêu cầu hỗ trợ.',NULL,0,'2026-10-29 09:38:02'),(15,2,5,'Test giai phap ky thuat',NULL,0,'2026-10-29 09:41:30'),(16,6,5,'Hoan tat dong phieu',NULL,0,'2026-10-29 09:42:21'),(17,9,2,'Kỹ thuật viên đã xử lý và cung cấp giải pháp khắc phục sự cố.',NULL,0,'2026-10-29 09:44:58'),(18,8,6,'Kỹ thuật viên đã hoàn tất hỗ trợ và đóng phiếu thành công.',NULL,0,'2026-10-29 09:45:12'),(19,7,2,'Đã khắc phục xong sự cố xác thực tài khoản SSO thành công.',NULL,0,'2026-10-29 16:16:44'),(20,7,2,'Đã hoàn tất hỗ trợ và đóng phiếu thành công.',NULL,0,'2026-10-29 16:16:55'),(21,1,5,'Kiểm tra giải quyết phiếu',NULL,0,'2026-10-29 16:49:40'),(22,1,5,'Kiểm tra đóng hoàn tất phiếu',NULL,0,'2026-10-29 16:49:40'),(23,1,4,'KTV đã xử lý xong sự cố',NULL,0,'2026-10-29 17:34:35'),(24,1,4,'Hoàn tất đóng phiếu',NULL,0,'2026-10-29 17:34:35'),(25,2,4,'KTV da xu ly xong loi canvas',NULL,0,'2026-10-29 19:12:14'),(26,1,4,'KTV đã cấu hình lại lịch học trừ các ngày nghỉ lễ.',NULL,0,'2026-10-29 20:50:30'),(27,9,13,'oke kklpm',NULL,0,'2026-10-29 21:37:52'),(28,9,2,'đã giải quyết',NULL,0,'2026-10-29 21:51:57'),(29,9,13,'em thấy được rồi ạ',NULL,0,'2026-10-29 22:01:32'),(30,9,2,'✅ [BIÊN BẢN NGHIỆM THU GIẢI PHÁP KỸ THUẬT]:\nđã giải quyết ,,,ưmlamaff',NULL,0,'2026-10-29 22:02:30'),(31,10,5,'jjsjnsbdndfdsf',NULL,0,'2026-10-29 22:37:18'),(32,10,13,'dạ được rồi ạ',NULL,0,'2026-10-29 22:38:09'),(33,12,13,'jdk',NULL,0,'2026-10-29 23:57:35');
/*!40000 ALTER TABLE `ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` int(10) unsigned NOT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `priority_id` int(10) unsigned NOT NULL,
  `parent_ticket_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `course_id` int(10) unsigned DEFAULT NULL,
  `is_guest_ticket` tinyint(1) NOT NULL DEFAULT 0,
  `contact_email` varchar(190) DEFAULT NULL,
  `lookup_pin` varchar(10) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `system_affected` varchar(50) NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('new','in_progress','waiting_requester','resolved','closed') NOT NULL DEFAULT 'new',
  `closed_reason` enum('completed','auto_closed','duplicate','cancelled') DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sla_started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `triage_due_at` datetime NOT NULL,
  `due_at` datetime NOT NULL,
  `first_response_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `reopen_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ticket_code` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_code` (`ticket_code`),
  KEY `fk_tickets_category` (`category_id`),
  KEY `fk_tickets_priority` (`priority_id`),
  KEY `fk_tickets_parent` (`parent_ticket_id`),
  KEY `fk_tickets_course` (`course_id`),
  KEY `idx_admin_queue` (`status`,`triage_due_at`,`due_at`),
  KEY `idx_agent_work` (`assigned_to`,`status`,`due_at`),
  KEY `idx_user_history` (`requester_id`,`created_at`),
  KEY `idx_guest_lookup` (`ticket_code`,`lookup_pin`),
  KEY `idx_ticket_academic` (`class_id`,`course_id`),
  CONSTRAINT `fk_tickets_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_tickets_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_parent` FOREIGN KEY (`parent_ticket_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_priority` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`),
  CONSTRAINT `fk_tickets_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,1,5,4,1,NULL,1,6,0,NULL,NULL,'[SỰ CỐ DIỆN RỘNG] Hiệu chỉnh lịch học trùng ngày nghỉ lễ trên Canvas LMS','KTV cấu hình sinh lịch học tự động đầu kỳ chưa trừ ngày nghỉ lễ 30/4 - 1/5 và 2/9. Cần chạy script CLI đồng bộ lại toàn trường.','Canvas LMS',NULL,'in_progress','completed','2026-10-23 08:00:00','2026-10-23 08:00:00','2026-10-23 08:30:00','2026-10-23 10:00:00','2026-10-23 08:10:00','2026-10-29 09:17:36','2026-10-29 09:17:48',0,'HD-2026-2910-T0L001'),(2,13,5,4,2,1,1,1,0,NULL,NULL,'Lịch nộp bài tập lớn môn Lập trình Web lớp D24-CNTT1 rơi vào ngày 30/4','Em thấy trên Canvas LMS hạn chốt nộp bài rơi đúng ngày 30/4 là ngày nghỉ lễ toàn quốc. Mong thầy cô hiệu chỉnh lại giúp lớp em.','Canvas LMS',NULL,'resolved',NULL,'2026-10-23 08:15:00','2026-10-23 08:15:00','2026-10-23 08:30:00','2026-10-23 10:00:00','2026-10-23 08:20:00','2026-10-29 09:41:30',NULL,0,'HD-2026-2910-P0T002'),(3,14,2,1,3,NULL,1,NULL,1,'tranlinh.personal@gmail.com','4589','Tài khoản đăng nhập portal bị khóa cứng sau 5 lần nhập sai','Em bị khóa tài khoản do nhập sai 5 lần, đã xác minh thẻ sinh viên.','Portal SSO','uploads/attachments/the_sinh_vien_tran_thuc_linh.svg','closed','completed','2026-10-23 08:30:00','2026-10-23 08:30:00','2026-10-23 09:00:00','2026-10-23 10:30:00','2026-10-23 08:40:00','2026-10-29 09:38:02','2026-10-29 09:05:47',0,'HD-2026-2910-T0L003'),(4,7,4,3,1,NULL,1,1,0,NULL,NULL,'[KHẨN CẤP] Cần mở quyền nhập điểm bổ sung học phần Lập trình Web Lớp D24-CNTT1','Cổng nhập điểm QLĐT đã quá hạn 12h, tôi đã có giấy phê duyệt của Phòng Đào tạo đề nghị mở khóa nhập điểm trong 24 giờ cho lớp D24-CNTT1.','QLĐT',NULL,'closed','','2026-10-22 14:00:00','2026-10-22 14:00:00','2026-10-22 14:15:00','2026-10-22 14:30:00','2026-10-22 14:05:00','2026-10-22 14:25:00','2026-10-29 23:42:44',0,'HD-2026-2910-L0H004'),(5,17,6,5,3,NULL,1,NULL,0,NULL,NULL,'Không xác thực được mạng Wi-Fi THUDO-UNI tại KTX Nhà B','Máy tính của em báo lỗi Can not connect to this network khi gõ tài khoản sinh viên D24.','Wi-Fi',NULL,'closed',NULL,'2026-10-21 09:00:00','2026-10-21 09:00:00','2026-10-21 09:30:00','2026-10-21 13:00:00','2026-10-21 09:15:00','2026-10-21 11:30:00','2026-10-21 12:00:00',0,'HD-2026-2910-W0F005'),(6,13,5,4,2,NULL,NULL,NULL,0,NULL,NULL,'[TEST DISPATCH] Yêu cầu hỗ trợ hệ thống Canvas LMS','Phiếu thử nghiệm cơ chế tự động phân công KTV chuyên trách & kiểm tra tràn tải (Auto-Overflow).','Hệ thống ĐH Thủ Đô',NULL,'resolved','completed','2026-10-29 09:20:18','2026-10-29 09:20:18','2026-10-29 03:50:18','2026-10-29 04:20:18',NULL,'2026-10-29 23:54:44',NULL,0,'HD-2026-2910-K0T006'),(7,13,2,1,3,NULL,NULL,NULL,0,NULL,NULL,'[TEST DISPATCH] Yêu cầu mở khóa tài khoản SSO','Phiếu thử nghiệm cơ chế tự động phân công KTV chuyên trách & kiểm tra tràn tải (Auto-Overflow).','Hệ thống ĐH Thủ Đô',NULL,'closed','completed','2026-10-29 09:20:23','2026-10-29 09:20:23','2026-10-29 03:50:23','2026-10-29 04:20:23',NULL,'2026-10-29 16:16:44','2026-10-29 16:16:55',0,'HD-2026-2910-S0O007'),(8,13,6,5,3,NULL,NULL,NULL,0,NULL,NULL,'[TEST DISPATCH] Hỗ trợ cấu hình Wi-Fi KTX','Phiếu thử nghiệm cơ chế tự động phân công KTV chuyên trách & kiểm tra tràn tải (Auto-Overflow).','Hệ thống ĐH Thủ Đô',NULL,'closed','completed','2026-10-29 09:21:34','2026-10-29 09:21:34','2026-10-29 03:51:34','2026-10-29 05:21:34',NULL,'2026-10-29 09:45:12','2026-10-29 09:45:12',0,'HD-2026-2910-N0E008'),(9,13,2,1,3,NULL,NULL,NULL,0,NULL,NULL,'[TEST DISPATCH] Sự cố phân quyền cổng điểm QLĐT','Phiếu thử nghiệm cơ chế tự động phân công KTV chuyên trách & kiểm tra tràn tải (Auto-Overflow).','Hệ thống ĐH Thủ Đô',NULL,'closed','completed','2026-10-29 09:21:36','2026-10-29 09:21:36','2026-10-29 03:51:36','2026-10-29 04:21:36','2026-10-29 21:51:57','2026-10-29 22:02:30','2026-10-29 22:03:03',0,'HD-2026-2910-Q0L009'),(10,13,5,4,2,NULL,NULL,NULL,0,NULL,NULL,'kkkfnfn','chán almws rồi','Canvas LMS','uploads/attachments/proof_20261029_155721_b6a8c7d9.pdf','closed','completed','2026-10-29 21:57:21','2026-10-29 21:57:21','2026-10-29 22:27:21','2026-10-29 22:57:21','2026-10-29 22:37:18','2026-10-29 22:38:57','2026-10-29 22:38:57',0,'HD-2026-2910-0Z010E'),(12,13,6,5,3,NULL,NULL,NULL,0,NULL,NULL,'Cá mập cắt cáp','ko vào đc','Canvas LMS','uploads/attachments/proof_20261029_163400_28b94243.pdf','in_progress',NULL,'2026-10-29 22:34:00','2026-10-29 22:34:00','2026-10-29 23:04:00','2026-10-30 02:34:00',NULL,'2026-10-29 23:57:06',NULL,1,'HD-2026-2910-0B011X'),(14,14,2,1,2,NULL,NULL,NULL,1,'nganpham30072005@gmail.com',NULL,'[CỨU HỘ KHẨN CẤP] Tài khoản bị khóa mềm 30 phút do gõ sai mật khẩu quá 5 lần - Phạm Thị Thanh Ngân (224001729)','SĐT liên hệ: 0975099854\nChi tiết sự cố: kknneokejfj;fkkfkokor','Portal SSO / Tài khoản','uploads/attachments/guest_1793288904_c97cb6.pdf','new',NULL,'2026-10-29 22:48:24','2026-10-29 22:48:24','0000-00-00 00:00:00','2026-10-29 20:48:24',NULL,NULL,NULL,0,'HD-2026-2910-J0013C');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `personal_email` varchar(190) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('requester','agent','admin') NOT NULL DEFAULT 'requester',
  `user_type` enum('student','lecturer','staff') NOT NULL DEFAULT 'student',
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `class_id` int(10) unsigned DEFAULT NULL,
  `cohort` varchar(20) DEFAULT 'D24',
  `academic_status` enum('studying','graduated','expelled','suspended') NOT NULL DEFAULT 'studying',
  `specialty_category_id` int(10) unsigned DEFAULT NULL,
  `max_capacity` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `active_tickets_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_on_duty` tinyint(1) NOT NULL DEFAULT 1,
  `failed_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `lock_reason` enum('none','brute_force','violation','expelled','tuition_debt','graduated') NOT NULL DEFAULT 'none',
  `is_first_login` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_user_code` (`user_code`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `fk_users_class` (`class_id`),
  KEY `fk_users_specialty_category` (`specialty_category_id`),
  KEY `idx_agent_dispatch` (`role`,`is_active`,`is_on_duty`,`specialty_category_id`,`active_tickets_count`),
  KEY `idx_user_lookup` (`user_code`,`user_type`,`academic_status`),
  KEY `idx_cohort_filter` (`cohort`,`academic_status`),
  CONSTRAINT `fk_users_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_users_specialty_category` FOREIGN KEY (`specialty_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ADM001','Quản Trị Viên Hệ Thống','admin@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','admin','staff','Trung tâm CNTT & Truyền thông','0912345678',NULL,NULL,'studying',NULL,10,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(2,'KTV001','KTV Nguyễn Văn An (Chuyên trách SSO)','ktv1@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','agent','staff','Tổ Dịch vụ Định danh & Xác thực','0911000001',NULL,NULL,'studying',1,15,1,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-29 09:34:54',NULL),(3,'KTV002','KTV Trần Thị Bích (Chuyên trách Mail & M365)','ktv2@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','agent','staff','Tổ Hạ tầng Đám mây & Email','0911000002',NULL,NULL,'studying',2,15,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-29 09:34:54',NULL),(4,'KTV003','KTV Lê Văn Cường (Chuyên trách Cổng QLĐT)','ktv3@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','agent','staff','Tổ Phần mềm & CSDL Đào tạo','0911000003',NULL,NULL,'studying',3,15,1,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-29 09:34:54',NULL),(5,'KTV004','KTV Phạm Hoàng Dũng (Chuyên trách Canvas LMS)','ktv4@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','agent','staff','Tổ E-Learning & Lớp học số','0911000004',NULL,NULL,'studying',4,15,1,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-29 09:34:54',NULL),(6,'KTV005','KTV Vũ Thị Én (Chuyên trách Mạng & Wi-Fi)','ktv5@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','agent','staff','Tổ Hạ tầng Mạng & Viễn thông','0911000005',NULL,NULL,'studying',5,15,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-29 09:34:54',NULL),(7,'GV001','TS. Nguyễn Văn Giảng','nguyenvangiang@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','lecturer','Bộ môn Công nghệ Phần mềm','0988776601',NULL,NULL,'studying',NULL,5,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(8,'GV002','ThS. Trần Thị Hạnh','tranthihanh@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','lecturer','Bộ môn Mạng & Truyền thông','0988776602',NULL,NULL,'studying',NULL,5,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(9,'GV003','TS. Lê Văn Tuấn','levantuan@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','lecturer','Bộ môn Hệ thống Thông tin','0988776603',NULL,NULL,'studying',NULL,5,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(10,'GV004','ThS. Phạm Mai Lan','phammailan@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','lecturer','Bộ môn Kỹ thuật Máy tính','0988776604',NULL,NULL,'studying',NULL,5,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(11,'GV005','TS. Hoàng Quốc Bảo','hoangquocbao@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','lecturer','Bộ môn Công nghệ Phần mềm','0988776605',NULL,NULL,'studying',NULL,5,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(12,'GV006','ThS. Vũ Thị Ngọc','vuthingoc@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','lecturer','Trung tâm CNTT & Truyền thông','0988776606',NULL,NULL,'studying',NULL,5,0,1,0,NULL,1,'none',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(13,'224001736','Phạm Thị Thanh Ngân','224001736@daihocthudo.edu.vn',NULL,'$2y$10$DkBwaeDKCYnlFKdnZwyIwemxsN7ayDsyToF8yzdT2bQ.FXwPIGJjC','requester','student','Khoa CNTT - Trưởng nhóm','0901234501',1,'D24','studying',NULL,5,0,1,0,NULL,1,'',0,'2026-10-23 16:01:57','2026-10-30 01:42:25',NULL),(14,'224001729','Trần Thục Linh','224001729@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','student','Khoa CNTT','0901234502',1,'D24','studying',NULL,5,0,1,0,NULL,1,'none',1,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(15,'224001737','Lê Hà My','224001737@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','student','Khoa CNTT','0901234503',1,'D24','studying',NULL,5,0,1,0,NULL,1,'none',1,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(16,'224001742','Nguyễn Ánh Nhi','224001742@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','student','Khoa CNTT','0901234504',1,'D24','studying',NULL,5,0,1,0,NULL,1,'none',1,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(17,'224001746','Phan Công Thành','224001746@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','student','Khoa CNTT','0901234505',1,'D24','studying',NULL,5,0,1,0,NULL,1,'none',1,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(18,'220001001','Nguyễn Cựu Sinh Viên (D20 Tốt nghiệp)','220001001@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','student','Khoa CNTT','0909999001',3,'D20','graduated',NULL,5,0,1,0,NULL,1,'graduated',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL),(19,'224001999','Hoàng Văn Kỷ Luật (TUD D24 Buộc Thôi Học)','224001999@daihocthudo.edu.vn',NULL,'$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m','requester','student','Khoa CNTT','0999888777',1,'D24','expelled',NULL,5,0,1,0,NULL,0,'expelled',0,'2026-10-23 16:01:57','2026-10-23 16:01:57',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'helpdesk_thudo'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-30  1:52:00

-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA CÔNG NGHỆ THÔNG TIN
-- CHỦ ĐỀ 9: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN (IT SUPPORT ONLINE)
-- TẬP TIN DDL TẠO BẢNG CƠ SỞ DỮ LIỆU: database/schema.sql
-- Hệ quản trị CSDL: MySQL 8.0+ (InnoDB, utf8mb4_unicode_ci)
-- ==============================================================================
CREATE DATABASE IF NOT EXISTS `helpdesk_thudo` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
USE `helpdesk_thudo`;
-- Tắt kiểm tra khóa ngoại tạm thời để tránh xung đột thứ tự DROP
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `ticket_ratings`;
DROP TABLE IF EXISTS `ticket_replies`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `course_assignments`;
DROP TABLE IF EXISTS `class_students`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `holidays`;
DROP TABLE IF EXISTS `priorities`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
-- Giữ kiểm tra khóa ngoại tắt trong toàn bộ quá trình DROP và CREATE TABLE
-- để tránh xung đột thứ tự tạo bảng. Bật lại ở cuối tập tin.
-- ==============================================================================
-- 1. BẢNG DANH MỤC DỊCH VỤ TRỰC TUYẾN (CATEGORIES)
-- ==============================================================================
CREATE TABLE `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL, -- CAT_SSO, CAT_MAIL, CAT_QLDT, CAT_CANVAS, CAT_WIFI
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `default_priority_code` VARCHAR(20) NOT NULL DEFAULT 'MED',
    `sla_hours` DECIMAL(4,1) NOT NULL DEFAULT 4.0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_categories_code` UNIQUE (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 2. BẢNG MỨC ĐỘ ƯU TIÊN (PRIORITIES)
-- ==============================================================================
CREATE TABLE `priorities` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL, -- URGENT, HIGH, MED, LOW
    `name` VARCHAR(50) NOT NULL,
    `sla_factor` DECIMAL(3,2) NOT NULL DEFAULT 1.00,
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT `uq_priorities_code` UNIQUE (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 3. BẢNG LỚP HỌC (CLASSES - 1 LỚP CÓ NHIỀU SINH VIÊN)
-- ==============================================================================
CREATE TABLE `classes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_code` VARCHAR(30) NOT NULL, -- Mã lớp: D24-CNTT1, D24-CNTT2 (Khóa D24 TUD)
    `name` VARCHAR(100) NOT NULL, -- Lớp Công nghệ Thông tin D24-1
    `cohort` VARCHAR(20) NOT NULL DEFAULT 'D24', -- Khóa tuyển sinh: D24 (niên khóa 2024-2028)
    `faculty` VARCHAR(100) NOT NULL DEFAULT 'Khoa Công nghệ Thông tin',
    `academic_year` VARCHAR(30) NOT NULL DEFAULT '2024-2028',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_classes_code` UNIQUE (`class_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 4. BẢNG MÔN HỌC / HỌC PHẦN (COURSES)
-- ==============================================================================
CREATE TABLE `courses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `course_code` VARCHAR(30) NOT NULL, -- WEB101, NET201, DB102, LTI301
    `name` VARCHAR(100) NOT NULL, -- Lập trình Web, Mạng máy tính, Cơ sở dữ liệu
    `credits` TINYINT UNSIGNED NOT NULL DEFAULT 3, -- Số tín chỉ
    `department` VARCHAR(100) NOT NULL DEFAULT 'Bộ môn Công nghệ Phần mềm',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_courses_code` UNIQUE (`course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 5. BẢNG TÀI KHOẢN NGƯỜI DÙNG (USERS)
-- Quản trị vòng đời SV (học, tốt nghiệp, thôi học), GV, KTV và Admin
-- ==============================================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_code` VARCHAR(30) NOT NULL, -- Mã SV (224001736) hoặc Mã GV / Cán bộ
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(190) NOT NULL, -- @daihocthudo.edu.vn
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('requester', 'agent', 'admin') NOT NULL DEFAULT 'requester',
    `user_type` ENUM('student', 'lecturer', 'staff') NOT NULL DEFAULT 'student',
    `department` VARCHAR(100) NULL, -- Khoa CNTT, Phòng QLĐT...
    `phone` VARCHAR(20) NULL,
    
    -- Liên kết lớp sinh hoạt & Quản trị Vòng đời Học tập (Lifecycle Management)
    `class_id` INT UNSIGNED NULL, -- Lớp sinh hoạt chính của sinh viên (VD: D24-CNTT1)
    `cohort` VARCHAR(20) NULL DEFAULT 'D24', -- Khóa tuyển sinh (D24, D23, D20...) để lọc hết khóa
    `academic_status` ENUM('studying', 'graduated', 'expelled', 'suspended') NOT NULL DEFAULT 'studying',
    
    -- Thuộc tính dành cho Kỹ thuật viên (KTV) phục vụ Phân công Chuyên môn & Tràn tải
    `specialty_category_id` INT UNSIGNED NULL, -- 1 đến 5 (CAT_SSO, CAT_MAIL,...)
    `max_capacity` TINYINT UNSIGNED NOT NULL DEFAULT 5, -- Giới hạn phiếu xử lý tối đa
    `active_tickets_count` TINYINT UNSIGNED NOT NULL DEFAULT 0, -- Bộ đếm nguyên tử
    `is_on_duty` TINYINT(1) NOT NULL DEFAULT 1, -- Ca trực: 1 (Đang trực), 0 (Nghỉ ca)
    
    -- Cơ chế Bảo mật, Khóa mềm (Soft Lock) và Khóa cứng (Hard Lock)
    `failed_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` DATETIME NULL, -- Khóa mềm 30 phút sau 5 lần nhập sai
    `is_active` TINYINT(1) NOT NULL DEFAULT 1, -- 1: Bình thường, 0: Khóa cứng
    `lock_reason` ENUM('none', 'brute_force', 'violation', 'expelled', 'tuition_debt', 'graduated') NOT NULL DEFAULT 'none',
    `is_first_login` TINYINT(1) NOT NULL DEFAULT 1, -- Bắt buộc đổi mật khẩu ở lần đầu
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT `uq_users_user_code` UNIQUE (`user_code`),
    CONSTRAINT `uq_users_email` UNIQUE (`email`),
    CONSTRAINT `fk_users_class` FOREIGN KEY (`class_id`) 
        REFERENCES `classes` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_users_specialty_category` FOREIGN KEY (`specialty_category_id`) 
        REFERENCES `categories` (`id`) ON DELETE SET NULL,
    INDEX `idx_agent_dispatch` (`role`, `is_active`, `is_on_duty`, `specialty_category_id`, `active_tickets_count`),
    INDEX `idx_user_lookup` (`user_code`, `user_type`, `academic_status`),
    INDEX `idx_cohort_filter` (`cohort`, `academic_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 6. BẢNG SINH VIÊN THUỘC LỚP (CLASS_STUDENTS - 1 LỚP CÓ NHIỀU SINH VIÊN)
-- ==============================================================================
CREATE TABLE `class_students` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL, -- user_id có user_type = 'student'
    `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_class_student` UNIQUE (`class_id`, `student_id`),
    CONSTRAINT `fk_class_students_class` FOREIGN KEY (`class_id`) 
        REFERENCES `classes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_class_students_student` FOREIGN KEY (`student_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 7. BẢNG PHÂN CÔNG GIẢNG DẠY LỚP HỌC PHẦN (COURSE_ASSIGNMENTS)
-- Cơ chế: Mỗi lớp học phần (1 môn của 1 lớp trong 1 kỳ) CHỈ CÓ DUY NHẤT 1 GIẢNG VIÊN PHỤ TRÁCH
-- Ví dụ: 1 học kỳ sinh viên học 6 môn -> Có 6 giảng viên quản lý danh sách theo 6 lớp học phần
-- ==============================================================================
CREATE TABLE `course_assignments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `lecturer_id` INT UNSIGNED NOT NULL, -- 1 giảng viên duy nhất phụ trách lớp học phần
    `semester` VARCHAR(20) NOT NULL DEFAULT '2026_1', -- Học kỳ I năm học 2026-2027
    `academic_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_course_class_semester` UNIQUE (`class_id`, `course_id`, `semester`),
    CONSTRAINT `fk_course_assign_class` FOREIGN KEY (`class_id`) 
        REFERENCES `classes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_course_assign_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_course_assign_lecturer` FOREIGN KEY (`lecturer_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 8. BẢNG DANH MỤC NGÀY NGHỈ LỄ (HOLIDAYS)
-- ==============================================================================
CREATE TABLE `holidays` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_recurring` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_holidays_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 9. BẢNG KHÔI PHỤC MẬT KHẨU TỰ ĐỘNG QUA EMAIL (PASSWORD_RESETS)
-- ==============================================================================
CREATE TABLE `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `email` VARCHAR(190) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_token_lookup` (`token_hash`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 10. BẢNG PHIẾU YÊU CẦU HỖ TRỢ (TICKETS)
-- ==============================================================================
CREATE TABLE `tickets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `requester_id` INT UNSIGNED NOT NULL,
    `assigned_to` INT UNSIGNED NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `priority_id` INT UNSIGNED NOT NULL,
    `parent_ticket_id` INT UNSIGNED NULL,
    
    -- Liên kết Môn học & Lớp học khi báo sự cố đào tạo LMS/QLĐT
    `class_id` INT UNSIGNED NULL,
    `course_id` INT UNSIGNED NULL,
    
    `is_guest_ticket` TINYINT(1) NOT NULL DEFAULT 0,
    `contact_email` VARCHAR(190) NULL,
    `lookup_pin` VARCHAR(10) NULL,
    
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `system_affected` VARCHAR(50) NOT NULL,
    `attachment_path` VARCHAR(255) NULL,
    
    `status` ENUM('new', 'in_progress', 'waiting_requester', 'resolved', 'closed') NOT NULL DEFAULT 'new',
    `closed_reason` ENUM('completed', 'auto_closed', 'duplicate', 'cancelled') NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sla_started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `triage_due_at` DATETIME NOT NULL,
    `due_at` DATETIME NOT NULL,
    `first_response_at` DATETIME NULL,
    `resolved_at` DATETIME NULL,
    `closed_at` DATETIME NULL,
    `reopen_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    
    `ticket_code` VARCHAR(30) NULL UNIQUE, -- Mã định dạng HD-2026-00001 (sinh bởi Controller/Service)
    
    CONSTRAINT `fk_tickets_requester` FOREIGN KEY (`requester_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tickets_assigned_to` FOREIGN KEY (`assigned_to`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tickets_category` FOREIGN KEY (`category_id`) 
        REFERENCES `categories` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tickets_priority` FOREIGN KEY (`priority_id`) 
        REFERENCES `priorities` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tickets_parent` FOREIGN KEY (`parent_ticket_id`) 
        REFERENCES `tickets` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tickets_class` FOREIGN KEY (`class_id`) 
        REFERENCES `classes` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tickets_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE SET NULL,
        
    INDEX `idx_admin_queue` (`status`, `triage_due_at`, `due_at`),
    INDEX `idx_agent_work` (`assigned_to`, `status`, `due_at`),
    INDEX `idx_user_history` (`requester_id`, `created_at`),
    INDEX `idx_guest_lookup` (`ticket_code`, `lookup_pin`),
    INDEX `idx_ticket_academic` (`class_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 11. BẢNG PHẢN HỒI & TRAO ĐỔI (TICKET_REPLIES)
-- ==============================================================================
CREATE TABLE `ticket_replies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `attachment_path` VARCHAR(255) NULL,
    `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT `fk_ticket_replies_ticket` FOREIGN KEY (`ticket_id`) 
        REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_replies_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_ticket_replies` (`ticket_id`, `is_internal`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ==============================================================================
-- 12. BẢNG ĐÁNH GIÁ CSAT 1-5 SAO (TICKET_RATINGS)
-- ==============================================================================
CREATE TABLE `ticket_ratings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `score` TINYINT UNSIGNED NOT NULL,
    `comment` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT `uq_ticket_ratings_ticket` UNIQUE (`ticket_id`),
    CONSTRAINT `fk_ticket_ratings_ticket` FOREIGN KEY (`ticket_id`) 
        REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_ratings_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `chk_ticket_ratings_score` CHECK (`score` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA CÔNG NGHỆ THÔNG TIN
-- CHỦ ĐỀ 9: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN (IT SUPPORT ONLINE)
-- TẬP TIN DDL TẠO BẢNG CƠ SỞ DỮ LIỆU: database/schema.sql
-- Hệ quản trị CSDL: MySQL 8.0+ (InnoDB, utf8mb4_unicode_ci)
-- ==============================================================================

-- 1. TẠO CSDL VÀ CHỌN CSDL SỬ DỤNG
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
DROP TABLE IF EXISTS `holidays`;
DROP TABLE IF EXISTS `priorities`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 1. BẢNG DANH MỤC DỊCH VỤ TRỰC TUYẾN (CATEGORIES)
-- Tạo trước để bảng users có thể tham chiếu specialty_category_id
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
-- 3. BẢNG TÀI KHOẢN NGƯỜI DÙNG (USERS)
-- Quản trị tập trung Sinh viên, Giảng viên, KTV và Admin
-- ==============================================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_code` VARCHAR(30) NOT NULL, -- Mã SV (VD: 224001736) hoặc Mã GV / Cán bộ
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(190) NOT NULL, -- @daihocthudo.edu.vn
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('requester', 'agent', 'admin') NOT NULL DEFAULT 'requester',
    `user_type` ENUM('student', 'lecturer', 'staff') NOT NULL DEFAULT 'student',
    `department` VARCHAR(100) NULL, -- Khoa CNTT, Phòng QLĐT...
    `phone` VARCHAR(20) NULL,
    
    -- Thuộc tính dành riêng cho Kỹ thuật viên (KTV) phục vụ Phân công & Tràn tải
    `specialty_category_id` INT UNSIGNED NULL, -- KTV phụ trách chính nhóm dịch vụ nào
    `max_capacity` TINYINT UNSIGNED NOT NULL DEFAULT 5, -- Giới hạn phiếu đồng thời tối đa
    `active_tickets_count` TINYINT UNSIGNED NOT NULL DEFAULT 0, -- Bộ đếm nguyên tử chống quá tải
    `is_on_duty` TINYINT(1) NOT NULL DEFAULT 1, -- Ca trực: 1 (Đang trực), 0 (Nghỉ ca)
    
    -- Cơ chế Bảo mật, Khóa mềm (Soft Lock) và Khóa cứng (Hard Lock)
    `failed_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0, -- Số lần nhập sai pass liên tiếp
    `locked_until` DATETIME NULL, -- Thời hạn khóa mềm (30 phút sau 5 lần gõ sai)
    `is_active` TINYINT(1) NOT NULL DEFAULT 1, -- 1: Bình thường, 0: Khóa cứng
    `lock_reason` ENUM('none', 'brute_force', 'violation', 'expelled', 'tuition_debt') NOT NULL DEFAULT 'none',
    `is_first_login` TINYINT(1) NOT NULL DEFAULT 1, -- Bắt buộc đổi mật khẩu ở lần đầu đăng nhập
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT `uq_users_user_code` UNIQUE (`user_code`),
    CONSTRAINT `uq_users_email` UNIQUE (`email`),
    CONSTRAINT `fk_users_specialty_category` FOREIGN KEY (`specialty_category_id`) 
        REFERENCES `categories` (`id`) ON DELETE SET NULL,
    INDEX `idx_agent_dispatch` (`role`, `is_active`, `is_on_duty`, `specialty_category_id`, `active_tickets_count`),
    INDEX `idx_user_lookup` (`user_code`, `user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 4. BẢNG DANH MỤC NGÀY NGHỈ LỄ (HOLIDAYS)
-- Phục vụ thuật toán tính SLA giờ hành chính và tự động dời lịch LMS
-- ==============================================================================
CREATE TABLE `holidays` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL, -- Tết Nguyên Đán, Nghỉ Lễ 30/4 - 1/5, Quốc Khánh 2/9
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_recurring` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_holidays_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 5. BẢNG KHÔI PHỤC MẬT KHẨU TỰ ĐỘNG QUA EMAIL (PASSWORD_RESETS)
-- Hỗ trợ cơ chế Self-Service Password Reset (SSPR) trong 15 phút
-- ==============================================================================
CREATE TABLE `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `email` VARCHAR(190) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL, -- Băm SHA-256 của token ngẫu nhiên
    `expires_at` DATETIME NOT NULL, -- Hết hạn sau 15 phút
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_token_lookup` (`token_hash`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 6. BẢNG PHIẾU YÊU CẦU HỖ TRỢ (TICKETS)
-- Bảng trung tâm của hệ thống Helpdesk
-- ==============================================================================
CREATE TABLE `tickets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `requester_id` INT UNSIGNED NOT NULL, -- Người gửi (SV hoặc GV)
    `assigned_to` INT UNSIGNED NULL, -- KTV tiếp nhận xử lý
    `category_id` INT UNSIGNED NOT NULL, -- Danh mục sự cố
    `priority_id` INT UNSIGNED NOT NULL, -- Mức ưu tiên
    `parent_ticket_id` INT UNSIGNED NULL, -- Dùng gộp phiếu sự cố diện rộng LMS/QLĐT
    
    -- Cơ chế Hỗ trợ Người dùng bị khóa tài khoản (Guest Ticket)
    `is_guest_ticket` TINYINT(1) NOT NULL DEFAULT 0, -- 1: Tạo từ form công khai khi bị khóa
    `contact_email` VARCHAR(190) NULL, -- Email ngoài (Gmail cá nhân) nhận pass tạm
    `lookup_pin` VARCHAR(10) NULL, -- Mã PIN 4 số tra cứu công khai không cần login
    
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `system_affected` VARCHAR(50) NOT NULL, -- SSO, Mail, QLDT, Canvas, Teams, Wi-Fi, VPN
    `attachment_path` VARCHAR(255) NULL, -- Đường dẫn ảnh màn hình lỗi lưu ngoài webroot
    
    `status` ENUM('new', 'in_progress', 'waiting_requester', 'resolved', 'closed') NOT NULL DEFAULT 'new',
    `closed_reason` ENUM('completed', 'auto_closed', 'duplicate', 'cancelled') NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sla_started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `triage_due_at` DATETIME NOT NULL,
    `due_at` DATETIME NOT NULL, -- Hạn chót hoàn thành theo SLA
    `first_response_at` DATETIME NULL, -- Thời điểm phản hồi đầu tiên của KTV
    `resolved_at` DATETIME NULL,
    `closed_at` DATETIME NULL,
    `reopen_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    
    -- Cột sinh mã định dạng HD-2026-00001 tự động bằng MySQL 8
    `ticket_code` VARCHAR(30) GENERATED ALWAYS AS (
        CONCAT('HD-', YEAR(`created_at`), '-', LPAD(`id`, 5, '0'))
    ) STORED UNIQUE,
    
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
        
    INDEX `idx_admin_queue` (`status`, `triage_due_at`, `due_at`),
    INDEX `idx_agent_work` (`assigned_to`, `status`, `due_at`),
    INDEX `idx_user_history` (`requester_id`, `created_at`),
    INDEX `idx_guest_lookup` (`ticket_code`, `lookup_pin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 7. BẢNG PHẢN HỒI & TRAO ĐỔI (TICKET_REPLIES)
-- ==============================================================================
CREATE TABLE `ticket_replies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `attachment_path` VARCHAR(255) NULL,
    `is_internal` TINYINT(1) NOT NULL DEFAULT 0, -- 1: Private Note (chỉ KTV/Admin thấy)
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT `fk_ticket_replies_ticket` FOREIGN KEY (`ticket_id`) 
        REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_replies_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_ticket_replies` (`ticket_id`, `is_internal`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 8. BẢNG ĐÁNH GIÁ CSAT 1-5 SAO (TICKET_RATINGS)
-- ==============================================================================
CREATE TABLE `ticket_ratings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL, -- Người chấm (chủ phiếu)
    `score` TINYINT UNSIGNED NOT NULL, -- 1 đến 5 sao
    `comment` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT `uq_ticket_ratings_ticket` UNIQUE (`ticket_id`),
    CONSTRAINT `fk_ticket_ratings_ticket` FOREIGN KEY (`ticket_id`) 
        REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_ratings_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `chk_ticket_ratings_score` CHECK (`score` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 4: THIẾT KẾ CSDL & TRUY VẤN SQL
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
-- CHỦ ĐỀ 9: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN (IT SUPPORT ONLINE)
-- ------------------------------------------------------------------------------
-- PHÂN HỆ CÁ NHÂN: USER MANAGEMENT & AUTH SECURITY (QUẢN TRỊ NGƯỜI DÙNG & BẢO MẬT)
-- SINH VIÊN PHỤ TRÁCH: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
-- LỚP: TOÁN ỨNG DỤNG D2024 (KHOA: TOÁN-CNTT) | HỆ THỐNG PORTAL: Đại học Thủ Đô Hà Nội
-- Hệ Quản trị CSDL: MySQL 8.0+ / MariaDB 10.4+ (InnoDB, utf8mb4_unicode_ci)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `helpdesk_thudo` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `helpdesk_thudo`;

-- Tắt tạm thời kiểm tra khóa ngoại để thực hiện dọn dẹp cấu trúc cũ nếu có
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `class_students`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `categories`;

-- ==============================================================================
-- 1. BẢNG DANH MỤC DỊCH VỤ TRỰC TUYẾN (CATEGORIES)
-- (Bảng cha liên kết chuyên môn của Kỹ thuật viên)
-- ==============================================================================
CREATE TABLE `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL COMMENT 'Mã nhóm: CAT_SSO, CAT_MAIL, CAT_QLDT, CAT_CANVAS, CAT_WIFI',
    `name` VARCHAR(100) NOT NULL COMMENT 'Tên nhóm dịch vụ CNTT',
    `description` VARCHAR(255) NULL,
    `default_priority_code` VARCHAR(20) NOT NULL DEFAULT 'MED',
    `sla_hours` DECIMAL(4,1) NOT NULL DEFAULT 4.0 COMMENT 'Thời gian xử lý cam kết chuẩn (giờ)',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_categories_code` UNIQUE (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 2. BẢNG LỚP HỌC SINH HOẠT (CLASSES)
-- (Bảng cha liên kết lớp học của Sinh viên: Toán Ứng Dụng D2024)
-- ==============================================================================
CREATE TABLE `classes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_code` VARCHAR(30) NOT NULL COMMENT 'Mã lớp sinh hoạt: TUD-D2024, CNTT-D2024',
    `name` VARCHAR(100) NOT NULL COMMENT 'Tên lớp: Lớp Toán Ứng Dụng D2024',
    `cohort` VARCHAR(20) NOT NULL DEFAULT 'D2024' COMMENT 'Khóa tuyển sinh: D2024, D2020 (để lọc sinh viên ra trường)',
    `faculty` VARCHAR(100) NOT NULL DEFAULT 'Khoa: Toán-CNTT',
    `academic_year` VARCHAR(30) NOT NULL DEFAULT '2024-2028',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_classes_code` UNIQUE (`class_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 3. BẢNG TÀI KHOẢN NGƯỜI DÙNG & VÒNG ĐỜI HỌC THUẬT (USERS)
-- Model: User.php - Phụ trách độc quyền bởi Trần Thục Linh (224001729)
-- Nghiệp vụ: Xác thực Bcrypt, phân quyền RBAC, kiểm soát khóa mềm Brute-force & khóa cứng kỷ luật
-- ==============================================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_code` VARCHAR(30) NOT NULL COMMENT 'Mã định danh duy nhất: Mã SV (224001729), Mã GV (GV001), Mã KTV (KTV001)',
    `name` VARCHAR(100) NOT NULL COMMENT 'Họ và tên người dùng tiếng Việt có dấu',
    `email` VARCHAR(190) NOT NULL COMMENT 'Email định danh trường học: @daihocthudo.edu.vn',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu băm Bcrypt 60 ký tự (mặc định: 123456)',
    `role` ENUM('requester', 'agent', 'admin') NOT NULL DEFAULT 'requester' COMMENT 'Vai trò hệ thống',
    `user_type` ENUM('student', 'lecturer', 'staff') NOT NULL DEFAULT 'student' COMMENT 'Đối tượng người dùng',
    `department` VARCHAR(100) NULL COMMENT 'Khoa/Phòng ban công tác',
    `phone` VARCHAR(20) NULL COMMENT 'Số điện thoại liên hệ',
    
    -- Liên kết Lớp sinh hoạt & Quản trị Vòng đời Học thuật (Academic Lifecycle)
    `class_id` INT UNSIGNED NULL COMMENT 'Khóa ngoại trỏ đến classes(id) nếu là sinh viên',
    `cohort` VARCHAR(20) NULL DEFAULT 'D24' COMMENT 'Khóa tuyển sinh phục vụ thống kê & lọc hết khóa',
    `academic_status` ENUM('studying', 'graduated', 'expelled', 'suspended') NOT NULL DEFAULT 'studying' COMMENT 'Trạng thái học vụ',
    
    -- Thuộc tính phân bổ Chuyên môn & Định mức tải cho Kỹ thuật viên (KTV)
    `specialty_category_id` INT UNSIGNED NULL COMMENT 'Chuyên môn chính của KTV (1 đến 5)',
    `max_capacity` TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT 'Định mức tiếp nhận tối đa cùng lúc',
    `active_tickets_count` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Số phiếu thực tế đang xử lý',
    `is_on_duty` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái ca trực: 1 (Đang trực), 0 (Nghỉ ca)',
    
    -- Cơ chế An toàn Bảo mật, Khóa mềm (Soft Lock) & Khóa cứng (Hard Lock)
    `failed_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Đếm số lần gõ sai mật khẩu liên tiếp',
    `locked_until` DATETIME NULL COMMENT 'Mốc thời gian hết hạn khóa mềm 30 phút sau 5 lần nhập sai',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động bình thường, 0: Khóa cứng tài khoản',
    `lock_reason` ENUM('none', 'brute_force', 'violation', 'expelled', 'tuition_debt', 'graduated') NOT NULL DEFAULT 'none' COMMENT 'Lý do khóa tài khoản',
    `is_first_login` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Cờ bắt buộc đổi mật khẩu ở lần đăng nhập đầu tiên',
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
-- 4. BẢNG KHÔI PHỤC MẬT KHẨU TỰ PHỤC VỤ QUA EMAIL (PASSWORD_RESETS - SSPR)
-- Model: PasswordReset.php - Phụ trách độc quyền bởi Trần Thục Linh (224001729)
-- Nghiệp vụ: Sinh mã token SHA-256 ngẫu nhiên 64 hex, hiệu lực nghiêm ngặt 15 phút
-- ==============================================================================
CREATE TABLE `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'ID người dùng yêu cầu đặt lại mật khẩu',
    `email` VARCHAR(190) NOT NULL COMMENT 'Email trường học tiếp nhận link khôi phục',
    `token_hash` VARCHAR(64) NOT NULL COMMENT 'Mã băm SHA-256 của token xác thực',
    `expires_at` DATETIME NOT NULL COMMENT 'Thời điểm hết hạn (thời lượng hiệu lực 15 phút)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_token_lookup` (`token_hash`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 5. BẢNG PHIẾU HỖ TRỢ (TICKETS - BẢNG LIÊN KẾT NGOẠI)
-- (Được sử dụng trong các câu truy vấn JOIN phục vụ phân tích tải KTV & lịch sử sinh viên)
-- ==============================================================================
CREATE TABLE `tickets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_code` VARCHAR(30) NOT NULL UNIQUE COMMENT 'Mã phiếu chuẩn: HD-2026-00001',
    `requester_id` INT UNSIGNED NOT NULL COMMENT 'Sinh viên / Cán bộ tạo phiếu',
    `assigned_to` INT UNSIGNED NULL COMMENT 'Kỹ thuật viên tiếp nhận xử lý',
    `category_id` INT UNSIGNED NOT NULL COMMENT 'Nhóm dịch vụ sự cố',
    `priority_id` INT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Mức độ ưu tiên',
    `class_id` INT UNSIGNED NULL,
    `is_guest_ticket` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Phiếu mở khóa ngoài dành cho tài khoản bị khóa',
    `contact_email` VARCHAR(190) NULL COMMENT 'Email cá nhân nhận kết quả mở khóa',
    `lookup_pin` VARCHAR(10) NULL COMMENT 'Mã PIN 4 số tra cứu ẩn danh',
    `title` VARCHAR(150) NOT NULL COMMENT 'Tiêu đề sự cố',
    `description` TEXT NOT NULL COMMENT 'Mô tả chi tiết',
    `system_affected` VARCHAR(50) NOT NULL COMMENT 'Hệ thống bị lỗi (SSO, Canvas, Mail...)',
    `status` ENUM('new', 'in_progress', 'waiting_requester', 'resolved', 'closed') NOT NULL DEFAULT 'new',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `due_at` DATETIME NOT NULL COMMENT 'Hạn chót cam kết SLA',
    
    CONSTRAINT `fk_tickets_requester` FOREIGN KEY (`requester_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tickets_assigned_to` FOREIGN KEY (`assigned_to`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tickets_category` FOREIGN KEY (`category_id`) 
        REFERENCES `categories` (`id`) ON DELETE RESTRICT,
        
    INDEX `idx_tickets_agent` (`assigned_to`, `status`),
    INDEX `idx_tickets_requester` (`requester_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bật lại kiểm tra toàn vẹn khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;


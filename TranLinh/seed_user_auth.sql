-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 4: THIẾT KẾ CSDL & TRUY VẤN SQL
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
-- CHỦ ĐỀ 9: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN (IT SUPPORT ONLINE)
-- ------------------------------------------------------------------------------
-- DỮ LIỆU SEED MẪU TIẾNG VIỆT PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
-- SINH VIÊN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
-- LỚP: TOÁN ỨNG DỤNG D2024 | KHOA: TOÁN-CNTT
-- TẬP TIN: Buoi3/seed_user_auth.sql
-- Mật khẩu mặc định toàn bộ tài khoản: 123456
-- Hash Bcrypt chuẩn PHP: $2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m
-- ==============================================================================

USE `helpdesk_thudo`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `password_resets`;
TRUNCATE TABLE `tickets`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `classes`;
TRUNCATE TABLE `categories`;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 1. SEED DANH MỤC DỊCH VỤ TRỰC TUYẾN (CATEGORIES)
-- ==============================================================================
INSERT INTO `categories` (`id`, `code`, `name`, `description`, `default_priority_code`, `sla_hours`, `is_active`) VALUES
(1, 'CAT_SSO', 'Tài khoản & Xác thực SSO', 'Cấp lại mật khẩu, mở khóa tài khoản, đồng bộ định danh sinh viên/cán bộ', 'MED', 2.0, 1),
(2, 'CAT_MAIL', 'Email Trường & Microsoft 365', 'Kích hoạt Microsoft 365 A3, khôi phục hòm thư @daihocthudo.edu.vn, mở rộng OneDrive 1TB', 'MED', 4.0, 1),
(3, 'CAT_QLDT', 'Cổng Quản lý Đào tạo (QLĐT)', 'Sự cố cổng đăng ký tín chỉ, tra cứu lịch thi, phân quyền nhập điểm cho giảng viên', 'HIGH', 2.0, 1),
(4, 'CAT_CANVAS', 'Hệ thống Học tập (Canvas & Teams)', 'Lỗi không thấy môn học, sự cố nộp bài tập lớn/tiểu luận, tạo phòng học trực tuyến Teams', 'HIGH', 2.0, 1),
(5, 'CAT_WIFI', 'Wi-Fi Trường & VPN Từ xa', 'Xác thực mạng THUDO-UNI, cấp phát IP Ký túc xá, cấu hình VPN truy cập Thư viện số từ xa', 'MED', 4.0, 1);

-- ==============================================================================
-- 2. SEED DANH MỤC LỚP HỌC (CLASSES - KHÓA TOÁN ỨNG DỤNG D2024 - KHOA: TOÁN-CNTT)
-- ==============================================================================
INSERT INTO `classes` (`id`, `class_code`, `name`, `cohort`, `faculty`, `academic_year`) VALUES
(1, 'TUD-D2024', 'Lớp Toán Ứng Dụng D2024', 'D2024', 'Khoa: Toán-CNTT', '2024-2028'),
(2, 'CNTT-D2024', 'Lớp Công nghệ Thông tin D2024', 'D2024', 'Khoa: Toán-CNTT', '2024-2028'),
(3, 'TUD-D2020', 'Lớp Toán Ứng Dụng D2020 (Đã tốt nghiệp)', 'D2020', 'Khoa: Toán-CNTT', '2020-2024');

-- ==============================================================================
-- 3. SEED TÀI KHOẢN NGƯỜI DÙNG TIẾNG VIỆT (USERS)
-- ==============================================================================
INSERT INTO `users` (
    `id`, `user_code`, `name`, `email`, `password_hash`, `role`, `user_type`, 
    `department`, `phone`, `class_id`, `cohort`, `academic_status`,
    `specialty_category_id`, `max_capacity`, `active_tickets_count`, 
    `is_on_duty`, `is_active`, `lock_reason`, `is_first_login`, `failed_attempts`, `locked_until`
) VALUES
-- 1. Quản trị viên hệ thống
(1, 'ADM001', 'Quản Trị Viên Hệ Thống', 'admin@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'admin', 'staff', 
 'Trung tâm CNTT & Truyền thông', '0912345678', NULL, NULL, 'studying', NULL, 10, 0, 1, 1, 'none', 0, 0, NULL),

-- 2. Đội ngũ 5 Kỹ thuật viên (KTV) phân theo 5 chuyên môn kỹ thuật
(2, 'KTV001', 'KTV Nguyễn Văn An (Chuyên trách SSO)', 'ktv1@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Dịch vụ Định danh & Xác thực', '0911000001', NULL, NULL, 'studying', 1, 5, 1, 1, 1, 'none', 0, 0, NULL),

(3, 'KTV002', 'KTV Trần Thị Bích (Chuyên trách Mail & M365)', 'ktv2@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Hạ tầng Đám mây & Email', '0911000002', NULL, NULL, 'studying', 2, 5, 0, 1, 1, 'none', 0, 0, NULL),

(4, 'KTV003', 'KTV Lê Văn Cường (Chuyên trách Cổng QLĐT)', 'ktv3@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Phần mềm & CSDL Đào tạo', '0911000003', NULL, NULL, 'studying', 3, 5, 1, 1, 1, 'none', 0, 0, NULL),

(5, 'KTV004', 'KTV Phạm Hoàng Dũng (Chuyên trách Canvas LMS)', 'ktv4@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ E-Learning & Lớp học số', '0911000004', NULL, NULL, 'studying', 4, 5, 2, 1, 1, 'none', 0, 0, NULL),

(6, 'KTV005', 'KTV Vũ Thị Én (Chuyên trách Mạng & Wi-Fi)', 'ktv5@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Hạ tầng Mạng & Viễn thông', '0911000005', NULL, NULL, 'studying', 5, 5, 0, 1, 1, 'none', 0, 0, NULL),

-- 3. Giảng viên trường Đại học Thủ Đô Hà Nội
(7, 'GV001', 'TS. Nguyễn Văn Giảng', 'nguyenvangiang@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'lecturer', 
 'Khoa: Toán-CNTT', '0988776601', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0, 0, NULL),

-- 4. Danh sách Sinh viên Nhóm 1 (Lớp Toán Ứng Dụng D2024 - Khoa: Toán-CNTT)
(13, '224001736', 'Phạm Thị Thanh Ngân', '224001736@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa: Toán-CNTT - Trưởng nhóm', '0901234501', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),

-- Sinh viên trực tiếp thực hiện phân hệ User Management & Auth: TRẦN THỤC LINH (Lớp Toán Ứng Dụng D2024 - Khoa: Toán-CNTT)
(14, '224001729', 'Trần Thục Linh', '224001729@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa: Toán-CNTT', '0901234502', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 2, NULL),

(15, '224001737', 'Lê Hà My', '224001737@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa: Toán-CNTT', '0901234503', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),

(16, '224001742', 'Nguyễn Ánh Nhi', '224001742@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa: Toán-CNTT', '0901234504', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),

(17, '224001746', 'Phan Công Thành', '224001746@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa: Toán-CNTT', '0901234505', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),

-- 5. Sinh viên hết khóa / Đã tốt nghiệp (Khóa D2020 - Admin quản trị lọc ra trường)
(18, '220001001', 'Nguyễn Cựu Sinh Viên (D2020 Tốt nghiệp)', '220001001@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa: Toán-CNTT', '0909999001', 3, 'D2020', 'graduated', NULL, 5, 0, 1, 1, 'graduated', 0, 0, NULL),

-- 6. Sinh viên Khóa D2024 bị Kỷ luật Buộc thôi học (Khóa cứng tài khoản, lock_reason = expelled)
(19, '224001999', 'Hoàng Văn Kỷ Luật (TUD D2024 Buộc Thôi Học)', '224001999@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa: Toán-CNTT', '0999888777', 1, 'D2024', 'expelled', NULL, 5, 0, 1, 0, 'expelled', 0, 5, '2026-10-24 16:00:00');

-- ==============================================================================
-- 4. SEED TOKEN KHÔI PHỤC MẬT KHẨU TỰ PHỤC VỤ QUA EMAIL (PASSWORD_RESETS - SSPR)
-- ==============================================================================
INSERT INTO `password_resets` (`id`, `user_id`, `email`, `token_hash`, `expires_at`, `created_at`) VALUES
-- Token 1: Yêu cầu SSPR còn hiệu lực của SV Trần Thục Linh (hiệu lực 15 phút)
(1, 14, '224001729@daihocthudo.edu.vn', 
 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', 
 DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW()),

-- Token 2: Yêu cầu cũ đã hết hạn (tạo cách đây 30 phút)
(2, 13, '224001736@daihocthudo.edu.vn', 
 '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 
 DATE_SUB(NOW(), INTERVAL 15 MINUTE), DATE_SUB(NOW(), INTERVAL 30 MINUTE));

-- ==============================================================================
-- 5. SEED PHIẾU HỖ TRỢ PHỤC VỤ TRUY VẤN JOIN (TICKETS)
-- ==============================================================================
INSERT INTO `tickets` (
    `id`, `ticket_code`, `requester_id`, `assigned_to`, `category_id`, `priority_id`, `class_id`,
    `is_guest_ticket`, `contact_email`, `lookup_pin`, `title`, `description`, 
    `system_affected`, `status`, `created_at`, `due_at`
) VALUES
(1, 'HD-2026-00001', 1, 5, 4, 1, 1, 0, NULL, NULL, 
 '[SỰ CỐ DIỆN RỘNG] Hiệu chỉnh lịch học trùng ngày nghỉ lễ trên Canvas LMS', 
 'KTV cấu hình sinh lịch học tự động đầu kỳ chưa trừ ngày nghỉ lễ 30/4 - 1/5 và 2/9.', 
 'Canvas LMS', 'in_progress', '2026-10-23 08:00:00', '2026-10-23 10:00:00'),

(2, 'HD-2026-00002', 13, 5, 4, 1, 1, 0, NULL, NULL, 
 'Lịch nộp bài tập lớn môn Lập trình Web lớp Toán Ứng Dụng D2024 rơi vào ngày 30/4', 
 'Hạn chốt nộp bài rơi đúng ngày 30/4 là ngày nghỉ lễ toàn quốc.', 
 'Canvas LMS', 'in_progress', '2026-10-23 08:15:00', '2026-10-23 10:00:00'),

-- Phiếu Guest Ticket báo khóa tài khoản do SV Trần Thục Linh gửi kèm ảnh Thẻ SV
(3, 'HD-2026-00003', 14, 2, 1, 3, 1, 1, 'tranlinh.personal@gmail.com', '4589', 
 'Tài khoản đăng nhập portal bị khóa cứng sau 5 lần nhập sai', 
 'Em gõ nhầm mật khẩu cũ 5 lần liên tiếp thì tài khoản bị khóa cứng. Em gửi ảnh chụp thẻ sinh viên lớp Toán Ứng Dụng D2024 để KTV xác minh mở khóa giúp em.', 
 'SSO', 'in_progress', '2026-10-23 08:30:00', '2026-10-23 10:30:00'),

(4, 'HD-2026-00004', 7, 4, 3, 1, 1, 0, NULL, NULL, 
 '[KHẨN CẤP] Cần mở quyền nhập điểm bổ sung học phần Lập trình Web Lớp Toán Ứng Dụng D2024', 
 'Cổng nhập điểm QLĐT đã quá hạn 12h, tôi đã có giấy phê duyệt của Phòng Đào tạo.', 
 'QLĐT', 'resolved', '2026-10-22 14:00:00', '2026-10-22 14:30:00'),

(5, 'HD-2026-00005', 17, 6, 5, 3, 1, 0, NULL, NULL, 
 'Không xác thực được mạng Wi-Fi THUDO-UNI tại KTX Nhà B', 
 'Máy tính của em báo lỗi Can not connect to this network khi gõ tài khoản sinh viên D2024.', 
 'Wi-Fi', 'closed', '2026-10-21 09:00:00', '2026-10-21 13:00:00');


-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD) - KHOA CÔNG NGHỆ THÔNG TIN
-- CHỦ ĐỀ 9: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN (IT SUPPORT ONLINE)
-- TẬP TIN DỮ LIỆU SEED MẪU: database/seed.sql
-- Mật khẩu mặc định/khởi tạo toàn bộ tài khoản sau import: 123456
-- Hash Bcrypt chuẩn PHP: $2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m
-- ==============================================================================

USE `helpdesk_thudo`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `ticket_ratings`;
TRUNCATE TABLE `ticket_replies`;
TRUNCATE TABLE `tickets`;
TRUNCATE TABLE `password_resets`;
TRUNCATE TABLE `course_assignments`;
TRUNCATE TABLE `class_students`;
TRUNCATE TABLE `courses`;
TRUNCATE TABLE `classes`;
TRUNCATE TABLE `holidays`;
TRUNCATE TABLE `priorities`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;

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
-- 2. SEED MỨC ĐỘ ƯU TIÊN (PRIORITIES)
-- ==============================================================================
INSERT INTO `priorities` (`id`, `code`, `name`, `sla_factor`, `sort_order`) VALUES
(1, 'URGENT', 'Khẩn cấp (Giờ thi / Sự cố diện rộng)', 0.25, 1),
(2, 'HIGH', 'Cao (Đăng ký tín chỉ / Nộp bài tập / Nhập điểm)', 0.50, 2),
(3, 'MED', 'Trung bình (Mật khẩu / Mail / Wi-Fi thông thường)', 1.00, 3),
(4, 'LOW', 'Thấp (Hỏi đáp hướng dẫn / Thắc mắc chung)', 1.50, 4);

-- ==============================================================================
-- 3. SEED DANH MỤC LỚP HỌC (CLASSES - KHÓA TUD D24)
-- 1 Lớp sinh hoạt có nhiều sinh viên
-- ==============================================================================
INSERT INTO `classes` (`id`, `class_code`, `name`, `cohort`, `faculty`, `academic_year`) VALUES
(1, 'D24-CNTT1', 'Lớp Công nghệ Thông tin D24-1', 'D24', 'Khoa Công nghệ Thông tin', '2024-2028'),
(2, 'D24-CNTT2', 'Lớp Công nghệ Thông tin D24-2', 'D24', 'Khoa Công nghệ Thông tin', '2024-2028'),
(3, 'D20-CNTT1', 'Lớp Công nghệ Thông tin D20-1 (Đã tốt nghiệp)', 'D20', 'Khoa Công nghệ Thông tin', '2020-2024');

-- ==============================================================================
-- 4. SEED DANH MỤC 6 MÔN HỌC TRONG 1 HỌC KỲ (COURSES)
-- ==============================================================================
INSERT INTO `courses` (`id`, `course_code`, `name`, `credits`, `department`) VALUES
(1, 'WEB101', 'Lập trình Web & Ứng dụng', 3, 'Bộ môn Công nghệ Phần mềm'),
(2, 'NET201', 'Quản trị Mạng & An toàn Hệ thống', 3, 'Bộ môn Mạng & Truyền thông'),
(3, 'DB102', 'Hệ Quản trị Cơ sở Dữ liệu', 3, 'Bộ môn Hệ thống Thông tin'),
(4, 'OS101', 'Hệ điều hành & Mã nguồn mở', 3, 'Bộ môn Kỹ thuật Máy tính'),
(5, 'SE201', 'Kỹ nghệ & Quy trình Phát triển Phần mềm', 3, 'Bộ môn Công nghệ Phần mềm'),
(6, 'LMS301', 'Hệ thống E-Learning & Chuyển đổi số', 2, 'Trung tâm CNTT & Truyền thông');

-- ==============================================================================
-- 5. SEED DANH MỤC NGÀY NGHỈ LỄ TRƯỜNG HỌC NĂM 2026 (HOLIDAYS)
-- Quản trị viên (Admin user_id = 1) cấu hình lịch nghỉ lễ toàn trường
-- ==============================================================================
INSERT INTO `holidays` (`id`, `name`, `start_date`, `end_date`, `is_recurring`, `created_by`) VALUES
(1, 'Tết Dương Lịch 2026', '2026-01-01', '2026-01-01', 1, 1),
(2, 'Tết Nguyên Đán Bính Ngọ 2026', '2026-02-14', '2026-02-23', 0, 1),
(3, 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)', '2026-04-26', '2026-04-26', 1, 1),
(4, 'Ngày Giải phóng Miền Nam & Quốc tế Lao động', '2026-04-30', '2026-05-01', 1, 1),
(5, 'Quốc khánh nước CHXHCN Việt Nam', '2026-09-01', '2026-09-03', 1, 1);

-- ==============================================================================
-- 6. SEED TÀI KHOẢN NGƯỜI DÙNG (USERS)
-- Mật khẩu toàn bộ tài khoản: 123456
-- Hash Bcrypt: $2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m
-- Sinh viên: Khóa TUD D24 (@daihocthudo.edu.vn)
-- ==============================================================================
INSERT INTO `users` (
    `id`, `user_code`, `name`, `email`, `password_hash`, `role`, `user_type`, 
    `department`, `phone`, `class_id`, `cohort`, `academic_status`,
    `specialty_category_id`, `max_capacity`, `active_tickets_count`, 
    `is_on_duty`, `is_active`, `lock_reason`, `is_first_login`
) VALUES
-- 1. Quản trị viên Trung tâm CNTT
(1, 'ADM001', 'Quản Trị Viên Hệ Thống', 'admin@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'admin', 'staff', 
 'Trung tâm CNTT & Truyền thông', '0912345678', NULL, NULL, 'studying', NULL, 10, 0, 1, 1, 'none', 0),

-- 2. Đội ngũ 5 Kỹ thuật viên (KTV) chuyên trách 5 nhóm dịch vụ
(2, 'KTV001', 'KTV Nguyễn Văn An (Chuyên trách SSO)', 'ktv1@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Dịch vụ Định danh & Xác thực', '0911000001', NULL, NULL, 'studying', 1, 5, 1, 1, 1, 'none', 0),

(3, 'KTV002', 'KTV Trần Thị Bích (Chuyên trách Mail & M365)', 'ktv2@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Hạ tầng Đám mây & Email', '0911000002', NULL, NULL, 'studying', 2, 5, 0, 1, 1, 'none', 0),

(4, 'KTV003', 'KTV Lê Văn Cường (Chuyên trách Cổng QLĐT)', 'ktv3@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Phần mềm & CSDL Đào tạo', '0911000003', NULL, NULL, 'studying', 3, 5, 1, 1, 1, 'none', 0),

(5, 'KTV004', 'KTV Phạm Hoàng Dũng (Chuyên trách Canvas LMS)', 'ktv4@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ E-Learning & Lớp học số', '0911000004', NULL, NULL, 'studying', 4, 5, 1, 1, 1, 'none', 0),

(6, 'KTV005', 'KTV Vũ Thị Én (Chuyên trách Mạng & Wi-Fi)', 'ktv5@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'agent', 'staff', 
 'Tổ Hạ tầng Mạng & Viễn thông', '0911000005', NULL, NULL, 'studying', 5, 5, 0, 1, 1, 'none', 0),

-- 3. 6 Giảng viên phụ trách 6 môn học trong kỳ (1 lớp học phần chỉ có 1 GV phụ trách)
(7, 'GV001', 'TS. Nguyễn Văn Giảng', 'nguyenvangiang@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'lecturer', 
 'Bộ môn Công nghệ Phần mềm', '0988776601', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0),

(8, 'GV002', 'ThS. Trần Thị Hạnh', 'tranthihanh@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'lecturer', 
 'Bộ môn Mạng & Truyền thông', '0988776602', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0),

(9, 'GV003', 'TS. Lê Văn Tuấn', 'levantuan@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'lecturer', 
 'Bộ môn Hệ thống Thông tin', '0988776603', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0),

(10, 'GV004', 'ThS. Phạm Mai Lan', 'phammailan@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'lecturer', 
 'Bộ môn Kỹ thuật Máy tính', '0988776604', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0),

(11, 'GV005', 'TS. Hoàng Quốc Bảo', 'hoangquocbao@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'lecturer', 
 'Bộ môn Công nghệ Phần mềm', '0988776605', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0),

(12, 'GV006', 'ThS. Vũ Thị Ngọc', 'vuthingoc@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'lecturer', 
 'Trung tâm CNTT & Truyền thông', '0988776606', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0),

-- 4. Danh sách 5 Sinh viên Nhóm 1 (Khóa TUD D24 - Lớp D24-CNTT1)
(13, '224001736', 'Phạm Thị Thanh Ngân', '224001736@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa CNTT - Trưởng nhóm', '0901234501', 1, 'D24', 'studying', NULL, 5, 0, 1, 1, 'none', 1),

(14, '224001729', 'Trần Thục Linh', '224001729@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa CNTT', '0901234502', 1, 'D24', 'studying', NULL, 5, 0, 1, 1, 'none', 1),

(15, '224001737', 'Lê Hà My', '224001737@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa CNTT', '0901234503', 1, 'D24', 'studying', NULL, 5, 0, 1, 1, 'none', 1),

(16, '224001742', 'Nguyễn Ánh Nhi', '224001742@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa CNTT', '0901234504', 1, 'D24', 'studying', NULL, 5, 0, 1, 1, 'none', 1),

(17, '224001746', 'Phan Công Thành', '224001746@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa CNTT', '0901234505', 1, 'D24', 'studying', NULL, 5, 0, 1, 1, 'none', 1),

-- 5. Sinh viên hết khóa / Đã tốt nghiệp (Khóa TUD D20 - Để Admin lọc hết khóa)
(18, '220001001', 'Nguyễn Cựu Sinh Viên (D20 Tốt nghiệp)', '220001001@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa CNTT', '0909999001', 3, 'D20', 'graduated', NULL, 5, 0, 1, 1, 'graduated', 0),

-- 6. Sinh viên Khóa TUD D24 bị Khóa cứng do Kỷ luật Buộc thôi học (Expelled)
(19, '224001999', 'Hoàng Văn Kỷ Luật (TUD D24 Buộc Thôi Học)', '224001999@daihocthudo.edu.vn', 
 '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m', 'requester', 'student', 
 'Khoa CNTT', '0999888777', 1, 'D24', 'expelled', NULL, 5, 0, 1, 0, 'expelled', 0);

-- ==============================================================================
-- 7. SEED BẢNG SINH VIÊN THUỘC LỚP (CLASS_STUDENTS: 1 LỚP CÓ NHIỀU SINH VIÊN)
-- ==============================================================================
INSERT INTO `class_students` (`class_id`, `student_id`) VALUES
(1, 13), -- Phạm Thị Thanh Ngân thuộc lớp D24-CNTT1
(1, 14), -- Trần Thục Linh thuộc lớp D24-CNTT1
(1, 15), -- Lê Hà My thuộc lớp D24-CNTT1
(1, 16), -- Nguyễn Ánh Nhi thuộc lớp D24-CNTT1
(1, 17), -- Phan Công Thành thuộc lớp D24-CNTT1
(1, 19), -- Hoàng Văn Kỷ Luật thuộc lớp D24-CNTT1
(3, 18); -- Cựu sinh viên D20 thuộc lớp D20-CNTT1

-- ==============================================================================
-- 8. SEED PHÂN CÔNG GIẢNG DẠY LỚP HỌC PHẦN (COURSE_ASSIGNMENTS)
-- Quy tắc: Mỗi lớp học phần CHỈ CÓ DUY NHẤT 1 GIÁO VIÊN PHỤ TRÁCH.
-- Lớp D24-CNTT1 trong kỳ 2026_1 học 6 môn -> Có 6 giảng viên quản lý 6 lớp học phần tương ứng
-- ==============================================================================
INSERT INTO `course_assignments` (`class_id`, `course_id`, `lecturer_id`, `semester`, `academic_year`) VALUES
-- 1. Môn Lập trình Web & Ứng dụng (WEB101) -> TS. Nguyễn Văn Giảng phụ trách duy nhất
(1, 1, 7, '2026_1', '2026-2027'),

-- 2. Môn Quản trị Mạng & An toàn Hệ thống (NET201) -> ThS. Trần Thị Hạnh phụ trách duy nhất
(1, 2, 8, '2026_1', '2026-2027'),

-- 3. Môn Hệ Quản trị Cơ sở Dữ liệu (DB102) -> TS. Lê Văn Tuấn phụ trách duy nhất
(1, 3, 9, '2026_1', '2026-2027'),

-- 4. Môn Hệ điều hành & Mã nguồn mở (OS101) -> ThS. Phạm Mai Lan phụ trách duy nhất
(1, 4, 10, '2026_1', '2026-2027'),

-- 5. Môn Kỹ nghệ Phần mềm (SE201) -> TS. Hoàng Quốc Bảo phụ trách duy nhất
(1, 5, 11, '2026_1', '2026-2027'),

-- 6. Môn Hệ thống E-Learning & LMS (LMS301) -> ThS. Vũ Thị Ngọc phụ trách duy nhất
(1, 6, 12, '2026_1', '2026-2027'),

-- Lớp D24-CNTT2 cũng học môn Lập trình Web -> Do TS. Nguyễn Văn Giảng phụ trách lớp học phần này
(2, 1, 7, '2026_1', '2026-2027');

-- ==============================================================================
-- 9. SEED CÁC PHIẾU YÊU CẦU MẪU (TICKETS - GẮN LỚP HỌC PHẦN & MÔN HỌC)
-- ==============================================================================
INSERT INTO `tickets` (
    `id`, `ticket_code`, `requester_id`, `assigned_to`, `category_id`, `priority_id`, `parent_ticket_id`,
    `class_id`, `course_id`,
    `is_guest_ticket`, `contact_email`, `lookup_pin`, `title`, `description`, 
    `system_affected`, `status`, `created_at`, `sla_started_at`, `triage_due_at`, `due_at`, 
    `first_response_at`, `resolved_at`, `closed_at`
) VALUES
-- Phiếu 1: Sự cố diện rộng Canvas LMS (Phiếu Chủ Master Ticket)
(1, 'HD-2026-00001', 1, 5, 4, 1, NULL, 
 1, 6,
 0, NULL, NULL, 
 '[SỰ CỐ DIỆN RỘNG] Hiệu chỉnh lịch học trùng ngày nghỉ lễ trên Canvas LMS', 
 'KTV cấu hình sinh lịch học tự động đầu kỳ chưa trừ ngày nghỉ lễ 30/4 - 1/5 và 2/9. Cần chạy script CLI đồng bộ lại toàn trường.', 
 'Canvas LMS', 'in_progress', 
 '2026-10-23 08:00:00', '2026-10-23 08:00:00', '2026-10-23 08:30:00', '2026-10-23 10:00:00', 
 '2026-10-23 08:10:00', NULL, NULL),

-- Phiếu 2: Sinh viên Ngân (TUD D24) báo lỗi nộp bài tập môn Lập trình Web lớp D24-CNTT1
(2, 'HD-2026-00002', 13, 5, 4, 1, 1, 
 1, 1,
 0, NULL, NULL, 
 'Lịch nộp bài tập lớn môn Lập trình Web lớp D24-CNTT1 rơi vào ngày 30/4', 
 'Em thấy trên Canvas LMS hạn chốt nộp bài rơi đúng ngày 30/4 là ngày nghỉ lễ toàn quốc. Mong thầy cô hiệu chỉnh lại giúp lớp em.', 
 'Canvas LMS', 'in_progress', 
 '2026-10-23 08:15:00', '2026-10-23 08:15:00', '2026-10-23 08:30:00', '2026-10-23 10:00:00', 
 '2026-10-23 08:20:00', NULL, NULL),

-- Phiếu 3: Guest Ticket báo khóa tài khoản từ form ngoài của SV Linh (TUD D24)
(3, 'HD-2026-00003', 14, 2, 1, 3, NULL, 
 1, NULL,
 1, 'tranlinh.personal@gmail.com', '4589', 
 'Tài khoản đăng nhập portal bị khóa cứng sau 5 lần nhập sai', 
 'Em gõ nhầm mật khẩu cũ 5 lần liên tiếp thì tài khoản bị khóa cứng. Em gửi ảnh chụp thẻ sinh viên TUD D24 để KTV xác minh mở khóa giúp em.', 
 'SSO', 'in_progress', 
 '2026-10-23 08:30:00', '2026-10-23 08:30:00', '2026-10-23 09:00:00', '2026-10-23 10:30:00', 
 '2026-10-23 08:40:00', NULL, NULL),

-- Phiếu 4: Giảng viên Giảng báo khẩn cấp (Mở quyền nhập điểm môn Lập trình Web lớp D24-CNTT1)
(4, 'HD-2026-00004', 7, 4, 3, 1, NULL, 
 1, 1,
 0, NULL, NULL, 
 '[KHẨN CẤP] Cần mở quyền nhập điểm bổ sung học phần Lập trình Web Lớp D24-CNTT1', 
 'Cổng nhập điểm QLĐT đã quá hạn 12h, tôi đã có giấy phê duyệt của Phòng Đào tạo đề nghị mở khóa nhập điểm trong 24 giờ cho lớp D24-CNTT1.', 
 'QLĐT', 'resolved', 
 '2026-10-22 14:00:00', '2026-10-22 14:00:00', '2026-10-22 14:15:00', '2026-10-22 14:30:00', 
 '2026-10-22 14:05:00', '2026-10-22 14:25:00', NULL),

-- Phiếu 5: Sinh viên Thành (TUD D24) báo lỗi Wi-Fi đã hoàn thành và chấm CSAT 5 sao
(5, 'HD-2026-00005', 17, 6, 5, 3, NULL, 
 1, NULL,
 0, NULL, NULL, 
 'Không xác thực được mạng Wi-Fi THUDO-UNI tại KTX Nhà B', 
 'Máy tính của em báo lỗi Can not connect to this network khi gõ tài khoản sinh viên D24.', 
 'Wi-Fi', 'closed', 
 '2026-10-21 09:00:00', '2026-10-21 09:00:00', '2026-10-21 09:30:00', '2026-10-21 13:00:00', 
 '2026-10-21 09:15:00', '2026-10-21 11:30:00', '2026-10-21 12:00:00');

-- ==============================================================================
-- 10. SEED TRAO ĐỔI & PHẢN HỒI (TICKET_REPLIES)
-- ==============================================================================
INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `is_internal`, `created_at`) VALUES
(1, 3, 2, 'Chào bạn Linh, KTV đã nhận được ảnh thẻ sinh viên TUD D24 của bạn và đang đối chiếu với CSDL Đào tạo.', 0, '2026-10-23 08:40:00'),
(2, 3, 2, 'Ghi chú nội bộ: Đã đối chiếu CCCD khớp hồ sơ tuyển sinh D24. Sẽ cấp pass tạm 123456 và gửi về tranlinh.personal@gmail.com.', 1, '2026-10-23 08:42:00'),
(3, 4, 4, 'KTV đã kiểm tra văn bản phê duyệt của P.ĐT và mở quyền nộp điểm cho thầy đến hết 17h00 ngày 23/10.', 0, '2026-10-22 14:25:00'),
(4, 5, 6, 'KTV đã giải phóng lease IP tại tầng 3 KTX Nhà B. Bạn quên mạng cũ và kết nối lại theo chuẩn PEAP/MSCHAPv2 nhé.', 0, '2026-10-21 11:30:00');

-- ==============================================================================
-- 11. SEED ĐÁNH GIÁ CSAT (TICKET_RATINGS)
-- ==============================================================================
INSERT INTO `ticket_ratings` (`id`, `ticket_id`, `user_id`, `score`, `comment`, `created_at`) VALUES
(1, 5, 17, 5, 'KTV hỗ trợ rất nhanh và nhiệt tình, Wi-Fi phòng KTX mình đã bắt được sóng khỏe.', '2026-10-21 11:55:00');

-- Bật lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;

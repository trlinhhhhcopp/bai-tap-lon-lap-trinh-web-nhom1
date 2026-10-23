-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA CÔNG NGHỆ THÔNG TIN
-- CHỦ ĐỀ 9: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN (IT SUPPORT ONLINE)
-- TẬP TIN DỮ LIỆU SEED MẪU: database/seed.sql
-- Mật khẩu mặc định cho toàn bộ tài khoản thử nghiệm: Thudo@2026!
-- Hash Bcrypt: $2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO
-- ==============================================================================

USE `helpdesk_thudo`;

-- Tắt kiểm tra khóa ngoại để nạp dữ liệu sạch
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `ticket_ratings`;
TRUNCATE TABLE `ticket_replies`;
TRUNCATE TABLE `tickets`;
TRUNCATE TABLE `password_resets`;
TRUNCATE TABLE `holidays`;
TRUNCATE TABLE `priorities`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;

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
-- 2. SEED MỨC ĐỘ ƯU TIÊN (PRIORITIES)
-- ==============================================================================
INSERT INTO `priorities` (`id`, `code`, `name`, `sla_factor`, `sort_order`) VALUES
(1, 'URGENT', 'Khẩn cấp (Giờ thi / Sự cố diện rộng)', 0.25, 1),
(2, 'HIGH', 'Cao (Đăng ký tín chỉ / Nộp bài tập / Nhập điểm)', 0.50, 2),
(3, 'MED', 'Trung bình (Mật khẩu / Mail / Wi-Fi thông thường)', 1.00, 3),
(4, 'LOW', 'Thấp (Hỏi đáp hướng dẫn / Thắc mắc chung)', 1.50, 4);

-- ==============================================================================
-- 3. SEED DANH MỤC NGÀY NGHỈ LỄ TRƯỜNG HỌC NĂM 2026 (HOLIDAYS)
-- Phục vụ thuật toán loại trừ giờ hành chính và tự động dời lịch LMS
-- ==============================================================================
INSERT INTO `holidays` (`id`, `name`, `start_date`, `end_date`, `is_recurring`) VALUES
(1, 'Tết Dương Lịch 2026', '2026-01-01', '2026-01-01', 1),
(2, 'Tết Nguyên Đán Bính Ngọ 2026', '2026-02-14', '2026-02-23', 0),
(3, 'Giỗ Tổ Hùng Vương (10/3 Âm lịch)', '2026-04-26', '2026-04-26', 1),
(4, 'Ngày Giải phóng Miền Nam & Quốc tế Lao động', '2026-04-30', '2026-05-01', 1),
(5, 'Quốc khánh nước CHXHCN Việt Nam', '2026-09-01', '2026-09-03', 1);

-- ==============================================================================
-- 4. SEED TÀI KHOẢN NGƯỜI DÙNG (USERS)
-- Mật khẩu mặc định: Thudo@2026!
-- ==============================================================================
INSERT INTO `users` (
    `id`, `user_code`, `name`, `email`, `password_hash`, `role`, `user_type`, 
    `department`, `phone`, `specialty_category_id`, `max_capacity`, `active_tickets_count`, 
    `is_on_duty`, `is_active`, `lock_reason`, `is_first_login`
) VALUES
-- 1. Quản trị viên Trung tâm CNTT
(1, 'ADM001', 'Quản Trị Viên Hệ Thống', 'admin@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'admin', 'staff', 
 'Trung tâm CNTT & Truyền thông', '0912345678', NULL, 10, 0, 1, 1, 'none', 0),

-- 2. Đội ngũ 5 Kỹ thuật viên (KTV) chuyên trách 5 nhóm sự cố
(2, 'KTV001', 'KTV Nguyễn Văn An (Chuyên trách SSO)', 'ktv1@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'agent', 'staff', 
 'Tổ Dịch vụ Định danh & Xác thực', '0911000001', 1, 5, 1, 1, 1, 'none', 0),

(3, 'KTV002', 'KTV Trần Thị Bích (Chuyên trách Mail & M365)', 'ktv2@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'agent', 'staff', 
 'Tổ Hạ tầng Đám mây & Email', '0911000002', 2, 5, 0, 1, 1, 'none', 0),

(4, 'KTV003', 'KTV Lê Văn Cường (Chuyên trách Cổng QLĐT)', 'ktv3@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'agent', 'staff', 
 'Tổ Phần mềm & CSDL Đào tạo', '0911000003', 3, 5, 1, 1, 1, 'none', 0),

(5, 'KTV004', 'KTV Phạm Hoàng Dũng (Chuyên trách Canvas LMS)', 'ktv4@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'agent', 'staff', 
 'Tổ E-Learning & Lớp học số', '0911000004', 4, 5, 1, 1, 1, 'none', 0),

(6, 'KTV005', 'KTV Vũ Thị Én (Chuyên trách Mạng & Wi-Fi)', 'ktv5@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'agent', 'staff', 
 'Tổ Hạ tầng Mạng & Viễn thông', '0911000005', 5, 5, 0, 1, 1, 'none', 0),

-- 3. Giảng viên Khoa CNTT
(7, 'GV001', 'TS. Nguyễn Văn Giảng', 'gv.cntt@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'requester', 'lecturer', 
 'Khoa Công nghệ Thông tin', '0988776655', NULL, 5, 0, 1, 1, 'none', 0),

-- 4. Danh sách 5 Sinh viên Nhóm 1 (Nhóm thực hiện đề tài)
(8, '224001736', 'Phạm Thị Thanh Ngân', '224001736@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'requester', 'student', 
 'Lớp CNTT K22 - Trưởng nhóm', '0901234501', NULL, 5, 0, 1, 1, 'none', 0),

(9, '224001729', 'Trần Thục Linh', '224001729@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'requester', 'student', 
 'Lớp CNTT K22', '0901234502', NULL, 5, 0, 1, 1, 'none', 0),

(10, '224001737', 'Lê Hà My', '224001736@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'requester', 'student', 
 'Lớp CNTT K22', '0901234503', NULL, 5, 0, 1, 1, 'none', 0),

(11, '224001742', 'Nguyễn Ánh Nhi', '224001742@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'requester', 'student', 
 'Lớp CNTT K22', '0901234504', NULL, 5, 0, 1, 1, 'none', 0),

(12, '224001746', 'Phan Công Thành', '224001746@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'requester', 'student', 
 'Lớp CNTT K22', '0901234505', NULL, 5, 0, 1, 1, 'none', 0),

-- 5. Sinh viên bị Khóa cứng do Kỷ luật Buộc thôi học (Mẫu thử nghiệm Hard Lock)
(13, '224001999', 'Hoàng Văn Kỷ Luật', '224001999@daihocthudo.edu.vn', 
 '$2y$10$70IrTFKCBDhG0GbpZPA6X.rf5WBsKAzyFK0ufZBaEc4gCCTemRNRO', 'requester', 'student', 
 'Khoa Kinh tế', '0999888777', NULL, 5, 0, 1, 0, 'expelled', 0);

-- ==============================================================================
-- 5. SEED CÁC PHIẾU YÊU CẦU MẪU (TICKETS)
-- ==============================================================================
INSERT INTO `tickets` (
    `id`, `requester_id`, `assigned_to`, `category_id`, `priority_id`, `parent_ticket_id`,
    `is_guest_ticket`, `contact_email`, `lookup_pin`, `title`, `description`, 
    `system_affected`, `status`, `created_at`, `sla_started_at`, `triage_due_at`, `due_at`, 
    `first_response_at`, `resolved_at`, `closed_at`
) VALUES
-- Phiếu 1: Sự cố diện rộng (Phiếu Chủ Master Ticket) do lệch lịch nghỉ lễ trên LMS
(1, 1, 5, 4, 1, NULL, 
 0, NULL, NULL, 
 '[SỰ CỐ DIỆN RỘNG] Hiệu chỉnh lịch học trùng ngày nghỉ lễ trên Canvas LMS', 
 'KTV cấu hình sinh lịch học tự động đầu kỳ chưa trừ ngày nghỉ lễ 30/4 - 1/5 và 2/9. Cần chạy script CLI đồng bộ lại toàn trường.', 
 'Canvas LMS', 'in_progress', 
 '2026-10-23 08:00:00', '2026-10-23 08:00:00', '2026-10-23 08:30:00', '2026-10-23 10:00:00', 
 '2026-10-23 08:10:00', NULL, NULL),

-- Phiếu 2: Phiếu con của sinh viên báo trùng lịch nghỉ lễ (gộp vào Phiếu 1)
(2, 8, 5, 4, 1, 1, 
 0, NULL, NULL, 
 'Lịch nộp bài tập lớn môn Lập trình Web rơi vào ngày 30/4', 
 'Em thấy trên Canvas LMS hạn chốt nộp bài rơi đúng ngày 30/4 là ngày nghỉ lễ toàn quốc. Mong thầy cô hiệu chỉnh lại giúp lớp em.', 
 'Canvas LMS', 'in_progress', 
 '2026-10-23 08:15:00', '2026-10-23 08:15:00', '2026-10-23 08:30:00', '2026-10-23 10:00:00', 
 '2026-10-23 08:20:00', NULL, NULL),

-- Phiếu 3: Guest Ticket báo khóa tài khoản từ form ngoài
(3, 9, 2, 1, 3, NULL, 
 1, 'tranlinh.personal@gmail.com', '4589', 
 'Tài khoản đăng nhập portal bị khóa cứng sau 5 lần nhập sai', 
 'Em gõ nhầm mật khẩu cũ 5 lần liên tiếp thì tài khoản bị khóa cứng. Em gửi ảnh chụp thẻ sinh viên để KTV xác minh mở khóa giúp em.', 
 'SSO', 'in_progress', 
 '2026-10-23 08:30:00', '2026-10-23 08:30:00', '2026-10-23 09:00:00', '2026-10-23 10:30:00', 
 '2026-10-23 08:40:00', NULL, NULL),

-- Phiếu 4: Giảng viên báo khẩn cấp (Academic Fast-track)
(4, 7, 4, 3, 1, NULL, 
 0, NULL, NULL, 
 '[KHẨN CẤP] Cần mở quyền nhập điểm bổ sung học phần Lập trình Web Lớp 01', 
 'Cổng nhập điểm QLĐT đã quá hạn 12h, tôi đã có giấy phê duyệt của Phòng Đào tạo đề nghị mở khóa nhập điểm trong 24 giờ.', 
 'QLĐT', 'resolved', 
 '2026-10-22 14:00:00', '2026-10-22 14:00:00', '2026-10-22 14:15:00', '2026-10-22 14:30:00', 
 '2026-10-22 14:05:00', '2026-10-22 14:25:00', NULL),

-- Phiếu 5: Sinh viên báo lỗi Wi-Fi đã hoàn thành và chấm CSAT 5 sao
(5, 12, 6, 5, 3, NULL, 
 0, NULL, NULL, 
 'Không xác thực được mạng Wi-Fi THUDO-UNI tại KTX Nhà B', 
 'Máy tính của em báo lỗi Can not connect to this network khi gõ tài khoản sinh viên.', 
 'Wi-Fi', 'closed', 
 '2026-10-21 09:00:00', '2026-10-21 09:00:00', '2026-10-21 09:30:00', '2026-10-21 13:00:00', 
 '2026-10-21 09:15:00', '2026-10-21 11:30:00', '2026-10-21 12:00:00');

-- ==============================================================================
-- 6. SEED TRAO ĐỔI & PHẢN HỒI (TICKET_REPLIES)
-- ==============================================================================
INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `is_internal`, `created_at`) VALUES
(1, 3, 2, 'Chào bạn Linh, KTV đã nhận được ảnh thẻ sinh viên của bạn và đang đối chiếu với CSDL Đào tạo.', 0, '2026-10-23 08:40:00'),
(2, 3, 2, 'Ghi chú nội bộ: Đã đối chiếu CCCD khớp hồ sơ tuyển sinh. Sẽ cấp pass tạm Thudo@2026! và gửi về tranlinh.personal@gmail.com.', 1, '2026-10-23 08:42:00'),
(3, 4, 4, 'KTV đã kiểm tra văn bản phê duyệt của P.ĐT và mở quyền nộp điểm cho thầy đến hết 17h00 ngày 23/10.', 0, '2026-10-22 14:25:00'),
(4, 5, 6, 'KTV đã giải phóng lease IP tại tầng 3 KTX Nhà B. Bạn quên mạng cũ và kết nối lại theo chuẩn PEAP/MSCHAPv2 nhé.', 0, '2026-10-21 11:30:00');

-- ==============================================================================
-- 7. SEED ĐÁNH GIÁ CSAT (TICKET_RATINGS)
-- ==============================================================================
INSERT INTO `ticket_ratings` (`id`, `ticket_id`, `user_id`, `score`, `comment`, `created_at`) VALUES
(1, 5, 12, 5, 'KTV hỗ trợ rất nhanh và nhiệt tình, Wi-Fi phòng mình đã bắt được sóng khỏe.', '2026-10-21 11:55:00');


-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 4: THIẾT KẾ CSDL & TRUY VẤN SQL
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
-- ------------------------------------------------------------------------------
-- HỆ THỐNG CÂU LỆNH TRUY VẤN SELECT PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
-- SINH VIÊN THỰC HIỆN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
-- LỚP: TOÁN ỨNG DỤNG D2024 (KHOA: TOÁN-CNTT)
-- TẬP TIN: Buoi3/queries_user_auth.sql
-- ==============================================================================

USE `helpdesk_thudo`;

-- ==============================================================================
-- CÂU TRUY VẤN 1: XÁC THỰC ĐĂNG NHẬP & KIỂM TRA TRẠNG THÁI KHÓA TÀI KHOẢN (WHERE)
-- ------------------------------------------------------------------------------
-- Mục đích nghiệp vụ:
--   Khi người dùng nhập Email hoặc Mã SV/GV vào Form đăng nhập, Backend (AuthController)
--   cần tìm đúng tài khoản và kiểm tra trạng thái bảo mật:
--   1. Tài khoản có bị Khóa cứng (is_active = 0, lock_reason = 'expelled') do kỷ luật?
--   2. Tài khoản có đang trong thời gian Khóa mềm 30 phút (locked_until > NOW()) do gõ sai quá 5 lần?
--   3. Lấy mật khẩu băm Bcrypt (password_hash) để kiểm tra password_verify() trong PHP.
-- Kỹ thuật SQL áp dụng:
--   - Mệnh đề WHERE với điều kiện kết hợp OR (email = :id OR user_code = :id).
--   - Mệnh đề CASE WHEN phân loại trạng thái trực quan ngay tại tầng CSDL.
--   - Hàm TIMESTAMPDIFF tính số phút còn lại của khóa mềm.
-- ==============================================================================
SELECT 
    `id`,
    `user_code`,
    `name`,
    `email`,
    `role`,
    `academic_status`,
    `is_active`,
    `lock_reason`,
    `failed_attempts`,
    `is_first_login`,
    CASE 
        WHEN `is_active` = 0 THEN 'BỊ KHÓA CỨNG (Kỷ luật Buộc thôi học / Hết khóa)'
        WHEN `locked_until` > NOW() THEN CONCAT('BỊ KHÓA MỀM (Còn ', TIMESTAMPDIFF(MINUTE, NOW(), `locked_until`), ' phút)')
        ELSE 'HỢP LỆ (Sẵn sàng đăng nhập)'
    END AS `trang_thai_tai_khoan`
FROM `users`
WHERE `email` = '224001729@daihocthudo.edu.vn' OR `user_code` = '224001729';


-- ==============================================================================
-- CÂU TRUY VẤN 2: TRA CỨU HỒ SƠ NGƯỜI DÙNG TOÀN DIỆN KÈM LỚP HỌC & CHUYÊN MÔN (LEFT JOIN)
-- ------------------------------------------------------------------------------
-- Mục đích nghiệp vụ:
--   Hiển thị trang thông tin cá nhân (Profile) hoặc xem chi tiết người dùng tại Admin Center.
--   Cần hiển thị thông tin kèm theo:
--   - Tên lớp sinh hoạt của sinh viên (Lớp Toán Ứng Dụng D2024, Khóa D2024, Khoa: Toán-CNTT).
--   - Tên chuyên môn KTV phụ trách (ví dụ: Tài khoản SSO, LMS Canvas, Email M365).
--   Vì người dùng có thể là Admin (không có lớp và không có chuyên môn), nên bắt buộc dùng
--   LEFT JOIN để không làm biến mất bản ghi người dùng nếu khóa ngoại NULL.
-- Kỹ thuật SQL áp dụng:
--   - LEFT JOIN 2 bảng cha: classes và categories.
--   - Hàm COALESCE(...) để thay thế giá trị NULL thành nhãn tiếng Việt thân thiện.
--   - Mệnh đề ORDER BY và LIMIT/OFFSET phân trang dữ liệu.
-- ==============================================================================
SELECT 
    u.`id`,
    u.`user_code` AS `ma_dinh_danh`,
    u.`name` AS `ho_va_ten`,
    u.`email` AS `email_truong`,
    u.`role` AS `vai_tro`,
    u.`user_type` AS `loai_tai_khoan`,
    COALESCE(c.`class_code`, 'N/A') AS `ma_lop`,
    COALESCE(c.`name`, 'Không thuộc lớp sinh hoạt') AS `ten_lop_sinh_hoat`,
    COALESCE(cat.`name`, 'Không phân bổ chuyên môn') AS `chuyen_mon_ktv`,
    COALESCE(cat.`sla_hours`, 0.0) AS `sla_chuan_gio`
FROM `users` u
LEFT JOIN `classes` c ON c.`id` = u.`class_id`
LEFT JOIN `categories` cat ON cat.`id` = u.`specialty_category_id`
WHERE u.`role` IN ('requester', 'agent')
ORDER BY u.`role` DESC, u.`id` ASC
LIMIT 10 OFFSET 0;


-- ==============================================================================
-- CÂU TRUY VẤN 3: XÁC THỰC TOKEN KHÔI PHỤC MẬT KHẨU SSPR CÒN HIỆU LỰC 15 PHÚT (INNER JOIN & WHERE)
-- ------------------------------------------------------------------------------
-- Mục đích nghiệp vụ:
--   Khi sinh viên bấm vào đường link khôi phục mật khẩu trong email (/reset-password?token=...):
--   1. Backend băm SHA-256 mã token và tìm kiếm trong bảng password_resets.
--   2. Kiểm tra token có đúng và còn trong thời hạn 15 phút (expires_at > NOW()).
--   3. Dùng INNER JOIN với users để xác định chắc chắn chủ sở hữu token, và chặn trường hợp
--      tài khoản bị kỷ luật buộc thôi học (is_active = 0, academic_status = 'expelled') cố tình đổi pass.
-- Kỹ thuật SQL áp dụng:
--   - INNER JOIN giữa password_resets và users.
--   - Mệnh đề WHERE kiểm tra đồng thời thời gian hiệu lực và trạng thái tài khoản.
-- ==============================================================================
SELECT 
    pr.`id` AS `token_id`,
    u.`user_code` AS `ma_sinh_vien`,
    u.`name` AS `ho_ten_sinh_vien`,
    pr.`email` AS `email_nhan_link`,
    pr.`token_hash` AS `ma_token_sha256`,
    pr.`expires_at` AS `thoi_diem_het_han`,
    TIMESTAMPDIFF(MINUTE, NOW(), pr.`expires_at`) AS `so_phut_con_lai`,
    u.`academic_status` AS `trang_thai_hoc_vu`
FROM `password_resets` pr
INNER JOIN `users` u ON u.`id` = pr.`user_id`
WHERE pr.`token_hash` = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'
  AND pr.`expires_at` > NOW()
  AND u.`is_active` = 1
  AND u.`academic_status` != 'expelled';


-- ==============================================================================
-- CÂU TRUY VẤN 4: ĐIỀU PHỐI & CÂN BẰNG TẢI KỸ THUẬT VIÊN (JOIN + GROUP BY + ORDER BY)
-- ------------------------------------------------------------------------------
-- Mục đích nghiệp vụ:
--   Khi hệ thống nhận được phiếu báo sự cố mới, hệ thống kiểm tra năng lực của 5 KTV:
--   1. KTV phải đang trong ca trực (is_on_duty = 1) và tài khoản đang hoạt động (is_active = 1).
--   2. Tính toán số phiếu thực tế KTV đang xử lý dở dang (trạng thái 'new', 'in_progress', 'waiting_requester').
--   3. Tính toán số slot trống còn nhận được (max_capacity - số phiếu đang xử lý).
--   4. Sắp xếp KTV có số lượng phiếu đang xử lý ít nhất lên đầu để tự động cân bằng tải.
-- Kỹ thuật SQL áp dụng:
--   - INNER JOIN users với categories để lấy chuyên môn.
--   - LEFT JOIN tickets với điều kiện lọc phiếu chưa hoàn thành để giữ nguyên KTV chưa có phiếu nào (COUNT = 0).
--   - GROUP BY theo thông tin định danh KTV.
--   - ORDER BY theo số lượng phiếu thực tế tăng dần.
-- ==============================================================================
SELECT 
    u.`id` AS `ktv_id`,
    u.`user_code` AS `ma_ktv`,
    u.`name` AS `ten_ky_thuat_vien`,
    cat.`name` AS `chuyen_mon_chinh`,
    u.`max_capacity` AS `dinh_muc_tai_toi_da`,
    COUNT(t.`id`) AS `phieu_thuc_te_dang_xu_ly`,
    (u.`max_capacity` - COUNT(t.`id`)) AS `so_slot_tiep_nhan_con_lai`
FROM `users` u
INNER JOIN `categories` cat ON cat.`id` = u.`specialty_category_id`
LEFT JOIN `tickets` t ON t.`assigned_to` = u.`id` 
    AND t.`status` IN ('new', 'in_progress', 'waiting_requester')
WHERE u.`role` = 'agent' 
  AND u.`is_active` = 1 
  AND u.`is_on_duty` = 1
GROUP BY u.`id`, u.`user_code`, u.`name`, cat.`name`, u.`max_capacity`
ORDER BY `phieu_thuc_te_dang_xu_ly` ASC, `so_slot_tiep_nhan_con_lai` DESC;


-- ==============================================================================
-- CÂU TRUY VẤN 5: THỐNG KÊ CƠ CẤU SINH VIÊN THEO KHÓA & TRẠNG THÁI HỌC VỤ (GROUP BY & COUNT)
-- ------------------------------------------------------------------------------
-- Mục đích nghiệp vụ:
--   Phục vụ màn hình Dashboard Admin Center quản lý vòng đời tài khoản sinh viên:
--   - Thống kê sinh viên đang theo học (studying) của Khóa tuyển sinh TUD D24.
--   - Thống kê sinh viên đã tốt nghiệp hết khóa (D20 graduated) để thực hiện chuyển trạng thái hàng loạt.
--   - Thống kê sinh viên bị kỷ luật buộc thôi học (expelled) để kiểm soát tài khoản bị khóa cứng.
-- Kỹ thuật SQL áp dụng:
--   - WHERE u.user_type = 'student'.
--   - GROUP BY u.cohort.
--   - Đếm có điều kiện bằng COUNT(CASE WHEN ... THEN 1 END) hoặc SUM(CASE WHEN ... THEN 1 ELSE 0 END).
-- ==============================================================================
SELECT 
    u.`cohort` AS `khoa_tuyen_sinh`,
    COUNT(u.`id`) AS `tong_sinh_vien`,
    SUM(CASE WHEN u.`academic_status` = 'studying' THEN 1 ELSE 0 END) AS `dang_hoc`,
    SUM(CASE WHEN u.`academic_status` = 'graduated' THEN 1 ELSE 0 END) AS `da_tot_nghiep`,
    SUM(CASE WHEN u.`academic_status` = 'expelled' THEN 1 ELSE 0 END) AS `ky_luat_buoc_thoi_hoc`
FROM `users` u
WHERE u.`user_type` = 'student'
GROUP BY u.`cohort`
ORDER BY u.`cohort` DESC;


-- ==============================================================================
-- CÂU TRUY VẤN 6: TRA CỨU LỊCH SỬ PHIẾU HỖ TRỢ VÀ TIẾP NHẬN GUEST TICKET (JOIN & WHERE)
-- ------------------------------------------------------------------------------
-- Mục đích nghiệp vụ:
--   Tra cứu lịch sử các phiếu báo sự cố của sinh viên Trần Thục Linh (Mã SV: 224001729),
--   bao gồm cả phiếu Guest Ticket gửi từ form ngoài khi tài khoản bị khóa cứng do nhập sai 5 lần.
-- Kỹ thuật SQL áp dụng:
--   - INNER JOIN giữa tickets, users (người tạo) và categories.
--   - LEFT JOIN với users (KTV xử lý) để hiển thị tên cán bộ tiếp nhận.
--   - WHERE theo mã sinh viên và trạng thái phiếu.
--   - ORDER BY tickets.created_at DESC (đơn mới nhất hiển thị trước).
-- ==============================================================================
SELECT 
    t.`ticket_code` AS `ma_phieu`,
    req.`name` AS `sinh_vien_gui`,
    cat.`name` AS `dich_vu_yeu_cau`,
    t.`title` AS `tieu_de_su_co`,
    t.`status` AS `trang_thai_xu_ly`,
    COALESCE(ktv.`name`, 'Đang chờ phân công') AS `can_bo_ktv_tiep_nhan`,
    t.`created_at` AS `thoi_gian_gui_phieu`,
    t.`due_at` AS `han_chot_sla`
FROM `tickets` t
INNER JOIN `users` req ON req.`id` = t.`requester_id`
INNER JOIN `categories` cat ON cat.`id` = t.`category_id`
LEFT JOIN `users` ktv ON ktv.`id` = t.`assigned_to`
WHERE req.`user_code` = '224001729' OR t.`is_guest_ticket` = 1
ORDER BY t.`created_at` DESC;


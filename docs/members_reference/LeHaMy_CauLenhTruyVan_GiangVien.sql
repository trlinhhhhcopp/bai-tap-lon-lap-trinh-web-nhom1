-- 1. Lấy danh sách phiếu do Giảng viên tạo (có quyền chọn HIGH/URGENT)
-- Dùng cho Lecturer Portal - xem các sự cố khẩn cấp mình đã báo
SELECT 
    t.ticket_code,
    t.title,
    t.status,
    p.code AS muc_uu_tien,
    p.name AS ten_muc_uu_tien,
    c.name AS nhom_dich_vu,
    t.created_at,
    t.due_at,
    cl.class_code,
    co.course_code,
    co.name AS ten_mon_hoc
FROM tickets t
JOIN users u ON t.requester_id = u.id
JOIN priorities p ON t.priority_id = p.id
JOIN categories c ON t.category_id = c.id
LEFT JOIN classes cl ON t.class_id = cl.id
LEFT JOIN courses co ON t.course_id = co.id
WHERE u.user_type = 'lecturer'
ORDER BY 
    FIELD(p.code, 'URGENT', 'HIGH', 'MED', 'LOW'),
    t.created_at DESC;


-- 2. Lấy thông tin phân công giảng dạy: 1 lớp học phần chỉ có 1 giảng viên phụ trách
-- Dùng để kiểm tra ràng buộc UNIQUE và hỗ trợ Academic Fast-Track
SELECT 
    ca.id,
    cl.class_code,
    cl.name AS ten_lop,
    co.course_code,
    co.name AS ten_mon_hoc,
    u.name AS ten_giang_vien,
    u.email,
    ca.semester,
    ca.academic_year
FROM course_assignments ca
JOIN classes cl ON ca.class_id = cl.id
JOIN courses co ON ca.course_id = co.id
JOIN users u ON ca.lecturer_id = u.id
WHERE ca.semester = '2026_1'
ORDER BY cl.class_code, co.course_code;


-- 3. Lấy các phiếu đang ở trạng thái waiting_requester (SLA bị đóng băng)
-- Dùng cho SlaService - kiểm tra cơ chế tạm dừng đếm SLA
SELECT 
    t.ticket_code,
    t.title,
    t.status,
    t.sla_started_at,
    t.due_at,
    u.name AS ten_giang_vien_hoac_sv,
    c.name AS nhom_dich_vu,
    p.name AS muc_uu_tien,
    TIMESTAMPDIFF(MINUTE, t.sla_started_at, NOW()) AS phut_da_chay_truoc_khi_dong_bang
FROM tickets t
JOIN users u ON t.requester_id = u.id
JOIN categories c ON t.category_id = c.id
JOIN priorities p ON t.priority_id = p.id
WHERE t.status = 'waiting_requester'
ORDER BY t.due_at ASC;


-- 4. Lấy danh sách ngày nghỉ lễ để tính SLA theo giờ hành chính
-- Dùng cho SlaService (loại trừ ban đêm + Thứ 7/CN + ngày lễ)
SELECT 
    id,
    name AS ten_ngay_le,
    start_date,
    end_date,
    is_recurring,
    DATEDIFF(end_date, start_date) + 1 AS so_ngay_nghi
FROM holidays
WHERE YEAR(start_date) = 2026
   OR is_recurring = 1
ORDER BY start_date;


-- 5. Thống kê phiếu của Giảng viên theo mức ưu tiên + trạng thái (phục vụ Auto-Priority & Dashboard)
-- Kết hợp Category → Priority để kiểm tra Auto-Priority Mapping
SELECT 
    u.name AS ten_giang_vien,
    c.code AS ma_dich_vu,
    c.name AS ten_dich_vu,
    c.default_priority_code AS priority_tu_dong,
    p.code AS priority_thuc_te,
    t.status,
    COUNT(*) AS so_luong_phieu
FROM tickets t
JOIN users u ON t.requester_id = u.id
JOIN categories c ON t.category_id = c.id
JOIN priorities p ON t.priority_id = p.id
WHERE u.user_type = 'lecturer'
GROUP BY u.name, c.code, c.name, c.default_priority_code, p.code, t.status
ORDER BY u.name, 
         FIELD(p.code, 'URGENT', 'HIGH', 'MED', 'LOW'),
         t.status;
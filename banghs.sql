-- 1. Lấy danh sách phiếu của 1 sinh viên (kèm tên danh mục dịch vụ + mức ưu tiên + trạng thái)
-- Dùng cho trang "Phiếu của tôi" (Student Portal)
SELECT 
    t.ticket_code,
    t.title,
    c.name AS ten_dich_vu,
    p.name AS muc_uu_tien,
    t.status,
    t.created_at,
    t.due_at
FROM tickets t
JOIN categories c ON t.category_id = c.id
JOIN priorities p ON t.priority_id = p.id
WHERE t.requester_id = 13          -- 224001736 - Phạm Thị Thanh Ngân (thay id sinh viên khác nếu cần)
ORDER BY t.created_at DESC;


-- 2. Lấy chi tiết 1 phiếu + thông tin sinh viên gửi + KTV đang xử lý
-- Dùng cho trang Chi tiết phiếu (Student xem tiến độ)
SELECT 
    t.ticket_code,
    t.title,
    t.description,
    t.status,
    u_sv.name AS ten_sinh_vien,
    u_sv.email AS email_sinh_vien,
    u_ktv.name AS ten_ky_thuat_vien,
    c.name AS nhom_dich_vu,
    p.name AS muc_uu_tien,
    t.created_at,
    t.first_response_at,
    t.resolved_at
FROM tickets t
JOIN users u_sv ON t.requester_id = u_sv.id
LEFT JOIN users u_ktv ON t.assigned_to = u_ktv.id
JOIN categories c ON t.category_id = c.id
JOIN priorities p ON t.priority_id = p.id
WHERE t.ticket_code = 'HD-2026-00002';


-- 3. Lấy điểm đánh giá CSAT (1-5 sao) của sinh viên sau khi phiếu được giải quyết
-- Dùng cho module Nghiệm thu CSAT
SELECT 
    t.ticket_code,
    t.title,
    r.score AS diem_csat,
    r.comment AS nhan_xet,
    r.created_at AS thoi_gian_danh_gia,
    u.name AS ten_sinh_vien
FROM ticket_ratings r
JOIN tickets t ON r.ticket_id = t.id
JOIN users u ON r.user_id = u.id
WHERE r.user_id = 17               -- 224001746 - Phan Công Thành (đã có rating 5 sao trong seed)
ORDER BY r.created_at DESC;


-- 4. Thống kê số lượng phiếu theo trạng thái của 1 sinh viên
-- Dùng cho Dashboard cá nhân (số phiếu mới / đang xử lý / đã giải quyết…)
SELECT 
    t.status,
    COUNT(*) AS so_luong
FROM tickets t
WHERE t.requester_id = 13          -- Sinh viên Ngân
GROUP BY t.status
ORDER BY 
    FIELD(t.status, 'new', 'in_progress', 'waiting_requester', 'resolved', 'closed');


-- 5. Lấy danh sách phiếu đã giải quyết nhưng chưa được sinh viên đánh giá CSAT
-- Dùng để nhắc sinh viên nghiệm thu (CSAT) sau khi KTV đóng phiếu
SELECT 
    t.ticket_code,
    t.title,
    t.resolved_at,
    c.name AS nhom_dich_vu,
    u.name AS ten_sinh_vien
FROM tickets t
JOIN categories c ON t.category_id = c.id
JOIN users u ON t.requester_id = u.id
LEFT JOIN ticket_ratings r ON t.id = r.ticket_id
WHERE t.status IN ('resolved', 'closed')
  AND r.id IS NULL                 -- Chưa có đánh giá
  AND t.requester_id = 13          -- Của sinh viên cụ thể (có thể bỏ WHERE này nếu muốn xem toàn hệ thống)
ORDER BY t.resolved_at DESC;
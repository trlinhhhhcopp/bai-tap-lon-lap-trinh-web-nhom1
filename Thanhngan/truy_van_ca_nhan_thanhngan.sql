-- ==============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
-- TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD) - KHOA CÔNG NGHỆ THÔNG TIN
-- BÁO CÁO CÁ NHÂN: TRƯỞNG NHÓM - PHẠM THỊ THANH NGÂN (MSSV: 224001736)
-- PHÂN HỆ PHỤ TRÁCH: CORE ARCHITECTURE & AGENT WORKSPACE (KỸ THUẬT VIÊN)
-- MODEL ĐỘC QUYỀN: Ticket.php (tickets), Reply.php (ticket_replies)
-- ==============================================================================

USE `helpdesk_thudo`;
SET NAMES utf8mb4;

-- ==============================================================================
-- CÂU TRUY VẤN 1 (SELECT DÙNG NHIỀU JOIN + WHERE + ORDER BY)
-- Nghiệp vụ: Bàn làm việc KTV - Lấy danh sách hàng đợi các phiếu đang xử lý
-- sắp xếp theo hạn SLA (due_at ASC) để ưu tiên xử lý sự cố sắp hết hạn.
-- Phân định rõ Lớp sinh hoạt (classes) và Môn học (courses).
-- ==============================================================================
SELECT 
    t.ticket_code AS `Mã Vé`,
    t.title AS `Tiêu Đề Sự Cố`,
    u_req.name AS `Người Gửi Yêu Cầu`,
    COALESCE(c.class_code, 'Không gắn') AS `Mã Lớp SH`,
    COALESCE(c.name, 'Không gắn') AS `Tên Lớp Sinh Hoạt`,
    COALESCE(crs.name, 'Chung') AS `Môn Học`,
    cat.name AS `Nhóm Dịch Vụ`,
    p.name AS `Mức Độ Ưu Tiên`,
    u_agent.name AS `KTV Tiếp Nhận`,
    t.status AS `Trạng Thái`,
    t.due_at AS `Hạn Chót SLA`
FROM tickets t
JOIN users u_req ON t.requester_id = u_req.id
JOIN categories cat ON t.category_id = cat.id
JOIN priorities p ON t.priority_id = p.id
LEFT JOIN users u_agent ON t.assigned_to = u_agent.id
LEFT JOIN classes c ON t.class_id = c.id
LEFT JOIN courses crs ON t.course_id = crs.id
WHERE t.status IN ('new', 'in_progress', 'waiting_requester')
ORDER BY t.due_at ASC;

-- ==============================================================================
-- CÂU TRUY VẤN 2 (SELECT DÙNG JOIN + WHERE LỌC THEO PHIẾU CỤ THỂ)
-- Nghiệp vụ: Xem chi tiết toàn bộ lịch sử trao đổi của một phiếu hỗ trợ
-- Hiển thị thông tin người phản hồi, phân định phản hồi công khai vs ghi chú nội bộ KTV.
-- ==============================================================================
SELECT 
    t.ticket_code AS `Mã Vé`,
    r.id AS `Mã Phản Hồi`,
    u.name AS `Người Phản Hồi`,
    u.role AS `Vai Trò`,
    CASE 
        WHEN r.is_internal = 1 THEN '🔒 Ghi chú nội bộ (Private Note)'
        ELSE '💬 Phản hồi công khai (Public)'
    END AS `Phân Loại Trao Đổi`,
    r.message AS `Nội Dung Trao Đổi`,
    r.created_at AS `Thời Gian Gửi`
FROM ticket_replies r
JOIN tickets t ON r.ticket_id = t.id
JOIN users u ON r.user_id = u.id
WHERE t.ticket_code = 'HD-2026-00003'
ORDER BY r.created_at ASC;

-- ==============================================================================
-- CÂU TRUY VẤN 3 (SELECT DÙNG JOIN 5 BẢNG: ĐỊNH VỊ LỚP HỌC PHẦN & GIẢNG VIÊN)
-- Nghiệp vụ: Khi sinh viên báo sự cố liên quan đến học tập/LMS, KTV truy vấn để:
-- (1) Xác định Lớp Sinh hoạt (classes) của sinh viên (VD: D24-CNTT1)
-- (2) Xác định Môn học (courses) gặp lỗi (VD: WEB101)
-- (3) Xác định chính xác Lớp Học phần trong kỳ và Giảng viên phụ trách duy nhất!
-- ==============================================================================
SELECT 
    t.ticket_code AS `Mã Sự Cố`,
    t.title AS `Tiêu Đề Phiếu`,
    sv.name AS `Sinh Viên Báo Lỗi`,
    sv.user_code AS `Mã SV D24`,
    c.name AS `Lớp Sinh Hoạt Hành Chính`,
    crs.name AS `Môn Học Bị Lỗi`,
    gv.name AS `Giảng Viên Phụ Trách Lớp Học Phần`,
    gv.email AS `Email GV Liên Hệ Phối Hợp`,
    ca.semester AS `Học Kỳ`,
    ca.academic_year AS `Năm Học`
FROM tickets t
JOIN users sv ON t.requester_id = sv.id
JOIN classes c ON t.class_id = c.id
JOIN courses crs ON t.course_id = crs.id
JOIN course_assignments ca ON ca.class_id = t.class_id AND ca.course_id = t.course_id
JOIN users gv ON ca.lecturer_id = gv.id
WHERE t.id = 2;

-- ==============================================================================
-- CÂU TRUY VẤN 4 (SELECT DÙNG JOIN + GROUP BY + TÍNH TOÁN CSAT TRUNG BÌNH)
-- Nghiệp vụ: Báo cáo Thống kê Hiệu suất Phân hệ Kỹ thuật viên (Agent Performance Report)
-- Đếm tổng số phiếu tiếp nhận, số phiếu giải quyết thành công và điểm đánh giá CSAT.
-- ==============================================================================
SELECT 
    u_agent.user_code AS `Mã KTV`,
    u_agent.name AS `Họ Tên KTV`,
    cat.name AS `Nhóm Chuyên Môn Chính`,
    COUNT(t.id) AS `Tổng Phiếu Đã Tiếp Nhận`,
    SUM(CASE WHEN t.status IN ('resolved', 'closed') THEN 1 ELSE 0 END) AS `Số Phiếu Giải Quyết Xong`,
    SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) AS `Số Phiếu Đang Xử Lý`,
    COALESCE(ROUND(AVG(tr.score), 2), 5.00) AS `Điểm Đánh Giá CSAT Trung Bình (1-5 Sao)`
FROM users u_agent
JOIN categories cat ON u_agent.specialty_category_id = cat.id
LEFT JOIN tickets t ON t.assigned_to = u_agent.id
LEFT JOIN ticket_ratings tr ON tr.ticket_id = t.id
WHERE u_agent.role = 'agent'
GROUP BY u_agent.id, u_agent.user_code, u_agent.name, cat.name
ORDER BY `Tổng Phiếu Đã Tiếp Nhận` DESC;


# bai-tap-lon-lap-trinh-web-nhom1
# Chủ đề 9: HỆ THỐNG TIẾP NHẬN VÀ XỬ LÝ HỖ TRỢ ( HELPDESK) NHÓM 1

## Danh sách thành viên:
1. Phạm Thị Thanh Ngân (224001736) - Trưởng nhóm
2. Trần Thục Linh (224001729) 
3. Lê Hà My (224001736)
4. Nguyễn Ánh Nhi (224001742)
5. Phan Công Thành (224001746) 

## Phân công nhiệm vụ:
1. Phạm Thị Thanh Ngân (224001736) - Trưởng nhóm
2. Trần Thục Linh (224001729) 
3. Lê Hà My (224001736)
4. Nguyễn Ánh Nhi (224001742)
5. Phan Công Thành (224001746) 
<<<<<<< HEAD
...

# I. PHÂN TẦNG PHẠM VI (P0 / P1 / P2) & VAI TRÒ NGƯỜI DÙNG

## 1. Phân tầng phạm vi thực hiện theo 10 buổi học

Để đảm bảo hệ thống chạy ổn định và vừa sức với nhóm 5 sinh viên trong 10 buổi học, toàn bộ chức năng được chia thành 3 tầng ưu tiên:

### Tầng P0 (Bắt buộc — Hoàn thành từ Buổi 4 đến Buổi 7)

- Đăng nhập, đăng xuất (password_hash, password_verify), phân quyền 3 vai trò (requester, agent, admin), bảo vệ CSRF (hash_equals).
- CRUD phiếu hỗ trợ (tickets), danh mục sự cố (categories), mức độ ưu tiên (priorities — tách bảng riêng để CRUD theo đúng đề bài) và tài khoản nhân viên (users).
- Máy trạng thái (State Machine) 4 bước: new → in_progress → resolved → closed (kèm nhánh reopen dành riêng cho Admin) và lưu vết vào bảng lịch sử (ticket_histories).
- Admin phân công nhân viên thủ công có kiểm tra định mức tải tối đa (max_capacity) trong Transaction.
- Gửi phản hồi trao đổi theo phiếu (ticket_replies), khóa phản hồi khi phiếu đã closed.
- Tìm kiếm theo từ khóa/mã phiếu, lọc đa tiêu chí (theo trạng thái, danh mục, mức ưu tiên, kỹ thuật viên) và phân trang (LIMIT / OFFSET).
- Hai Endpoint JSON bắt buộc dùng Fetch API: lấy số lượng phiếu chưa xử lý (GET /api/tickets/open-count) và tải lịch sử phiếu (GET /api/tickets/history?code=...).

### Tầng P1 (Mở rộng cốt lõi — Hoàn thành từ Buổi 7 đến Buổi 8)

- Tính hạn xử lý due_at tự động và hiển thị nhãn cảnh báo SLA kèm chữ rõ ràng: [Đúng hạn], [Sắp quá hạn], [Quá hạn].
- Đính kèm ảnh chụp màn hình lỗi có kiểm soát 5 lớp tại Server và phục vụ ảnh qua Controller có kiểm tra quyền truy cập (GET /tickets/attachment?code=...).
- Đánh giá mức độ hài lòng CSAT từ 1 đến 5 sao (ticket_ratings, ràng buộc UNIQUE(ticket_id)).
- Xuất báo cáo CSV có UTF-8 BOM cho Microsoft Excel và lọc chống tấn công CSV Formula Injection.

### Tầng P2 (Tùy chọn sau Buổi 8 — Chỉ làm khi P0 và P1 đã chạy ổn định, đủ 20 test case và cài được trên máy sạch)

- Ô "Gợi ý hướng dẫn tự khắc phục": khi người dùng gõ tiêu đề sự cố, trình duyệt gọi GET /api/kb/search?q=... (debounce 300ms), server tìm kiếm bằng LIKE / FULLTEXT trong bảng kb_articles và trả về tối đa 3 bài viết hướng dẫn khớp nhất.
- Cảnh báo nhẹ khi người dùng tạo phiếu trùng category_id và trùng phòng (location_id) trong vòng 30 phút gần nhất bằng một câu truy vấn SELECT.

## 2. Phân quyền 3 Vai trò người dùng (RBAC)

| Vai trò          | Giá trị lưu CSDL | Quyền hạn chính                                                                 | Ràng buộc kiểm tra tại Server                                                                 |
|------------------|------------------|----------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------|
| Người yêu cầu    | requester        | Tạo phiếu hỗ trợ, xem danh sách phiếu cá nhân, gửi phản hồi khi phiếu chưa đóng, đánh giá hài lòng CSAT (1–5 sao). | Chỉ xem được phiếu và ảnh đính kèm có requester_id là chính mình; không được tự phân công hoặc tự đổi trạng thái sang resolved/closed. |
| Kỹ thuật viên    | agent            | Xem hàng đợi các phiếu được giao cho mình, lọc theo trạng thái và cảnh báo SLA, gửi phản hồi trao đổi, chuyển trạng thái từ in_progress sang resolved. | Chỉ cập nhật được phiếu có assigned_to khớp với ID của mình; không được đóng phiếu (closed) hoặc sửa danh mục hệ thống. |
| Quản trị viên    | admin            | CRUD danh mục, mức ưu tiên, vị trí phòng, bài viết hướng dẫn và tài khoản nhân viên; phân công phiếu & chốt mức ưu tiên áp dụng (priority_id); đóng/mở lại phiếu; xem Dashboard & xuất CSV. | Kiểm tra role === 'admin' ở Middleware; kiểm tra định mức tải max_capacity của kỹ thuật viên trong Transaction trước khi phân công. |

## 3. Giải pháp nghiệp vụ đơn giản thay thế cho thuật toán AI

- **Xử lý lỗi chưa rõ nguyên nhân:** Trong bảng categories có sẵn danh mục "Chưa rõ nguyên nhân / Sự cố khác". Người dùng không biết lỗi thuộc nhóm nào chỉ cần chọn danh mục này và đính kèm ảnh màn hình lỗi; Admin sẽ xem ảnh và cập nhật lại category_id chính xác khi phân công.
- **Chống khai khống mức Khẩn cấp:** Bảng tickets lưu tách biệt 2 cột: priority_requested_id (mức do người dùng chọn lúc tạo phiếu) và priority_id (mức áp dụng thực tế). Nếu người dùng chọn *Khẩn cấp*, hệ thống lưu nguyên đề xuất đó nhưng khởi tạo priority_id ở mức *Trung bình* chờ Admin duyệt và chốt khi phân công.
=======
>>>>>>> d96f79559bbd3bc302cc154fcbf1bd52b461c577
II. SƠ ĐỒ QUAN HỆ THỰC THỂ (ERD V2) & THIẾT KẾ CSDL (CHUẨN 3NF)
1. Quan hệ giữa 10 bảng dữ liệu trong schema.sql (ERD v2)
users (1) ──── (n) tickets qua requester_id (Một người dùng tạo nhiều phiếu yêu cầu).
users (1) ──── (n) tickets qua assigned_to (Một kỹ thuật viên phụ trách nhiều phiếu).
users (n) ──── (n) categories qua bảng trung gian agent_specialties (user_id, category_id) (Một kỹ thuật viên có thể phụ trách nhiều danh mục chuyên môn).
categories (1) ──── (n) tickets qua category_id (Mỗi phiếu thuộc một danh mục sự cố).
priorities (1) ──── (n) tickets qua priority_requested_id và priority_id (Mức ưu tiên đề xuất và mức ưu tiên áp dụng).
locations (1) ──── (n) tickets qua location_id (Mỗi phiếu gắn với một phòng học/phòng Lab chuẩn hóa).
tickets (1) ──── (n) ticket_replies qua ticket_id (Một phiếu có nhiều phản hồi trao đổi).
tickets (1) ──── (n) ticket_histories qua ticket_id (Một phiếu có nhiều bản ghi lịch sử thay đổi).
tickets (1) ──── (0..1) ticket_ratings qua ticket_id UNIQUE (Mỗi phiếu đã giải quyết có tối đa 1 đánh giá CSAT).
categories (1) ──── (n) kb_articles qua category_id (Mỗi danh mục có nhiều bài viết hướng dẫn tự khắc phục).
2. Đặc tả chi tiết các bảng CSDL (MySQL InnoDB, utf8mb4_unicode_ci)
Bảng 1 — users (Người dùng): id (PK), full_name (VARCHAR(100)), email (VARCHAR(190) UNIQUE), password_hash (VARCHAR(255)), role (ENUM('requester','agent','admin')), academic_role (ENUM('student','lecturer','staff')), department (VARCHAR(100)), max_capacity (TINYINT UNSIGNED DEFAULT 5), is_on_duty (TINYINT(1)), failed_login_attempts, locked_until (DATETIME NULL), is_active (TINYINT(1)), created_at.
Bảng 2 — categories (Danh mục sự cố): id (PK), name (VARCHAR(100) UNIQUE), description (VARCHAR(500)), sla_hours (SMALLINT UNSIGNED DEFAULT 24), is_active (TINYINT(1)), created_at.
Bảng 3 — priorities (Mức độ ưu tiên — CRUD bởi Admin): id (PK), code (VARCHAR(20) UNIQUE), name (VARCHAR(60) UNIQUE), sla_factor (DECIMAL(4,2)), sort_order (TINYINT UNSIGNED), is_active (TINYINT(1)), created_at.
Bảng 4 — locations (Vị trí phòng học / phòng Lab chuẩn hóa): id (PK), building (VARCHAR(50)), room_code (VARCHAR(30) UNIQUE), name (VARCHAR(120)), is_active (TINYINT(1)), created_at.
Bảng 5 — agent_specialties (Chuyên môn kỹ thuật viên): Khóa chính kép (user_id, category_id) tham chiếu users(id) và categories(id).
Bảng 6 — tickets (Phiếu yêu cầu hỗ trợ):
Khóa & Tham chiếu: id (PK), ticket_code (VARCHAR(30) UNIQUE), requester_id (FK), assigned_to (FK NULL), category_id (FK), location_id (FK NULL), priority_requested_id (FK), priority_id (FK), parent_ticket_id (FK tự tham chiếu tickets.id NULL).
Nội dung & Thời gian: asset_note (VARCHAR(100) NULL), title (VARCHAR(150)), description (TEXT), status (ENUM('new','in_progress','resolved','closed')), attachment_path (VARCHAR(255) NULL), attachment_original_name (VARCHAR(255) NULL), due_at (DATETIME), first_response_at (DATETIME NULL), resolved_at (DATETIME NULL), closed_at (DATETIME NULL), created_at, updated_at.
Chỉ mục kết hợp (Composite Indexes): idx_tickets_status_due (status, due_at), idx_tickets_assigned_status (assigned_to, status), idx_tickets_requester_created (requester_id, created_at), idx_tickets_dup_check (category_id, location_id, created_at).
Bảng 7 — ticket_replies (Phản hồi phiếu): id (PK), ticket_id (FK), user_id (FK), message (TEXT), attachment_path (VARCHAR(255) NULL), created_at.
Bảng 8 — ticket_histories (Lịch sử thao tác & kiểm toán): id (PK), ticket_id (FK), changed_by (FK), action_type (ENUM('created','assigned','status_changed','priority_changed','category_changed','reopened')), old_value (VARCHAR(100) NULL), new_value (VARCHAR(100)), note (VARCHAR(500) NULL), created_at.
Bảng 9 — ticket_ratings (Đánh giá hài lòng CSAT): id (PK), ticket_id (FK UNIQUE), requester_id (FK), rating (TINYINT UNSIGNED CHECK 1..5), comment (VARCHAR(500) NULL), created_at.
Bảng 10 — kb_articles (Bài viết hướng dẫn tự khắc phục — P2): id (PK), category_id (FK), title (VARCHAR(200)), keywords (VARCHAR(300)), content (TEXT), is_active (TINYINT(1)), created_at, kèm FULLTEXT INDEX ft_kb_search (title, keywords, content).


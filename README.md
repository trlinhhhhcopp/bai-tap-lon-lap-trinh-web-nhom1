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
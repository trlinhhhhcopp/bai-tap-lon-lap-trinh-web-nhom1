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

## Danh sách 18 Route thống nhất toàn hệ thống
V. BẢNG MÃ LỖI CHỐT CỨNG, QUY TẮC VALIDATION & DANH SÁCH ROUTE
1. Khuôn JSON duy nhất & Bảng mã lỗi HTTP chốt cứng
Mọi endpoint /api/* trả về Content-Type: application/json; charset=utf-8 theo khuôn:
{"ok": true, "data": {...}, "error": null} hoặc {"ok": false, "data": null, "error": {"code": 404, "message": "..."}}.

Mã HTTP
Tình huống áp dụng duy nhất
Hành vi với Route HTML
Hành vi với Route /api/*
401
Chưa đăng nhập
302 Redirect về /login
Trả JSON lỗi code: 401
403
Đã đăng nhập nhưng sai vai trò (requester vào /admin/* hoặc agent sửa phiếu người khác)
Hiện trang lỗi 403 Forbidden
Trả JSON lỗi code: 403
404
Phiếu/ảnh không tồn tại hoặc requester A truy cập phiếu của requester B
Hiện trang lỗi 404 Not Found
Trả JSON lỗi code: 404
405
Sai phương thức HTTP (GET vào route chỉ nhận POST)
Hiện trang lỗi 405
Trả JSON lỗi code: 405
409
Vi phạm quy tắc trạng thái/nghiệp vụ (phản hồi phiếu đã đóng, vượt max_capacity, tranh chấp đồng thời, đánh giá CSAT lần 2)
Hiện thông báo lỗi nghiệp vụ
Trả JSON lỗi code: 409
419
Thiếu hoặc sai _csrf_token trong request POST
Hiện lỗi phiên biểu mẫu hết hạn
Trả JSON lỗi code: 419
422
Dữ liệu form hoặc tệp tải lên không hợp lệ
Quay lại form, giữ $old, báo lỗi cạnh trường
Trả JSON lỗi code: 422
429
Vượt giới hạn tần suất (đăng nhập sai quá 5 lần hoặc spam tạo phiếu)
Hiện thông báo tạm khóa 15 phút
Trả JSON lỗi code: 429
500
Lỗi hệ thống / PDOException
rollBack(), ghi log server, hiện trang lỗi 500
Trả JSON lỗi code: 500


| STT | Method | Đường dẫn | Controller@Action | Vai trò được phép | Tầng |
|---|---|---|---|---|---|
| 1 | GET | `/login` | `AuthController@showLogin` | Khách | P0 |
| 2 | POST | `/login` | `AuthController@login` | Khách | P0 |
| 3 | POST | `/logout` | `AuthController@logout` | Đã đăng nhập | P0 |
| 4 | GET | `/tickets/create` | `TicketController@create` | requester, admin | P0 |
| 5 | POST | `/tickets/create` | `TicketController@store` | requester, admin | P0 |
| 6 | GET | `/tickets/my` | `TicketController@myTickets` | requester | P0 |
| 7 | GET | `/tickets/show?code=...` | `TicketController@show` | Chủ phiếu, agent được giao, admin | P0 |
| 8 | POST | `/tickets/reply` | `TicketController@reply` | Chủ phiếu, agent được giao, admin | P0 |
| 9 | GET | `/tickets/attachment?code=...` | `AttachmentController@download` | Chủ phiếu, agent được giao, admin | P1 |
| 10 | POST | `/tickets/rate` | `TicketController@rate` | requester (chủ phiếu) | P1 |
| 11 | GET | `/agent/queue` | `AgentController@queue` | agent, admin | P0 |
| 12 | POST | `/tickets/status` | `AgentController@updateStatus` | agent (được giao), admin | P0 |
| 13 | GET | `/admin/tickets` | `AdminController@tickets` | admin | P0 |
| 14 | POST | `/admin/tickets/assign` | `AdminController@assign` | admin | P0 |
| 15et=utf-8 theo khuôn:
{"ok": true, "data": {...}, "error": null} hoặc {"ok": false, "data": null, "error": {"code": 404, "message": "..."}}.
 | GET/POST | `/admin/master-data` | `AdminController@masterData` | admin | P0 |
| 16 | GET | `/admin/dashboard` | `AdminController@dashboard` | admin | P0 |
| 17 | GET | `/admin/reports/export-csv` | `AdminController@exportCsv` | admin | P1 |
| 18 | GET | `/api/tickets/open-count`, `/api/tickets/history`, `/api/kb/search` | `ApiController@openCount`, `@history`, `@kbSearch` | Đã đăng nhập (theo quyền) | P0 / P2 |

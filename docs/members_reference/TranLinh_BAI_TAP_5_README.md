# BÀI TẬP VỀ NHÀ 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
### HỌC PHẦN: LẬP TRÌNH WEB · HỌC KỲ I · TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
* **Chủ đề 9:** Hệ thống Helpdesk Tiếp nhận & Xử lý Hỗ trợ CNTT Trực tuyến
* **Sinh viên thực hiện:** Trần Thục Linh
* **Mã sinh viên:** 224001729
* **Lớp:** Toán Ứng Dụng D2024 (Khoa: Toán-CNTT)
* **Phân hệ phụ trách:** User Management & Auth Security (Quản trị Người dùng & Bảo mật)
* **Thực thể CSDL:** Bảng `users` (kết nối `classes`, `categories`, `password_resets`)

---

## 📂 1. CẤU TRÚC TẬP TIN BÀI 5 TRONG THƯ MỤC `TranLinh/`

| Tập tin | Vai trò & Tiêu chí bài học |
|---|---|
| [`db.php`](file:///c:/Users/Admin/Desktop/LapTrinhWebnhom1/bai-tap-lon-lap-trinh-web-nhom1/TranLinh/db.php) | Khởi tạo hàm kết nối `db(): PDO` chuẩn (Slide 3-4), `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, tiện ích Flash Message. |
| [`UserRepository.php`](file:///c:/Users/Admin/Desktop/LapTrinhWebnhom1/bai-tap-lon-lap-trinh-web-nhom1/TranLinh/UserRepository.php) | **Repository function** (Slide 9-10): gom các hàm nghiệp vụ `findActiveUsers`, `countActiveUsers`, `findById`, `create`, `update`, `toggleLock`. |
| [`users.php`](file:///c:/Users/Admin/Desktop/LapTrinhWebnhom1/bai-tap-lon-lap-trinh-web-nhom1/TranLinh/users.php) | Giao diện Quản trị CRUD: Bảng danh sách, Tìm kiếm tương đối `%` (Slide 6), Phân trang an toàn (Slide 11), Khung Modal thêm/sửa. |
| [`user_action.php`](file:///c:/Users/Admin/Desktop/LapTrinhWebnhom1/bai-tap-lon-lap-trinh-web-nhom1/TranLinh/user_action.php) | Tiếp nhận POST, xử lý nghiệp vụ, áp dụng mô hình **POST-Redirect-GET** và **Flash Message** (Slide 2 & 8), khóa tài khoản bằng POST (Slide 15). |
| [`user_detail.php`](file:///c:/Users/Admin/Desktop/LapTrinhWebnhom1/bai-tap-lon-lap-trinh-web-nhom1/TranLinh/user_detail.php) | Màn hình xem chi tiết 1 bản ghi (Detail - Slide 7) kết hợp `LEFT JOIN` và `COALESCE`. |
| [`test_crud_cli.php`](file:///c:/Users/Admin/Desktop/LapTrinhWebnhom1/bai-tap-lon-lap-trinh-web-nhom1/TranLinh/test_crud_cli.php) | Script kiểm thử tự động trên Terminal, tự chạy và chấm điểm 5 test case không cần trình duyệt. |

---

## 🛠️ 2. CÁC TIÊU CHÍ KỸ THUẬT ĐẠT CHUẨN BÀI 5

### A. Kết nối PDO đúng cấu hình (Slide 3 & 4)
* DSN đầy đủ: `mysql:host=localhost;dbname=helpdesk_thudo;charset=utf8mb4`.
* Bật `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` để ném ngoại lệ rõ ràng khi phát triển.
* Thiết lập `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` để nhận mảng kết hợp `['cot' => 'gia_tri']`.
* Tách biệt hàm kết nối dùng chung trong `db.php`, không viết rải rác.

### B. Prepared Statement chống 100% SQL Injection (Slide 5 & 6)
* 100% các câu lệnh truy vấn có tham số người dùng nhập đều dùng `:placeholder` thay vì nối chuỗi SQL.
* Tìm kiếm tương đối dùng toán tử `LIKE :kw` và truyền mảng `['kw' => '%' . $keyword . '%']` trong `execute()` / `bindValue()`.

### C. Luồng CRUD Hoàn chỉnh (Slide 7 & 8)
* **Create:** Form thêm người dùng, validate dữ liệu, tự động băm mật khẩu Bcrypt (`123456`), redirect về trang danh sách kèm Flash message.
* **Read (List & Detail):** Danh sách người dùng phân trang và trang chi tiết hồ sơ cá nhân.
* **Update:** Chỉnh sửa thông tin liên hệ, khoa công tác, lớp sinh hoạt.
* **Delete / Khóa (Slide 15):** Khóa tài khoản an toàn thông qua phương thức **POST** (`is_active = 0`, `lock_reason = 'expelled'`), không xóa cứng làm mất dữ liệu liên kết.

### D. Phân trang an toàn (Slide 11)
* Nhận `page` từ URL, ép kiểu nguyên dương: `$page = max(1, (int)($_GET['page'] ?? 1))`.
* Công thức vị trí bắt đầu: `$offset = ($page - 1) * $perPage`.
* Truy vấn `COUNT(*)` để tính chính xác tổng số trang `$totalPages = ceil($total / $perPage)`.

---

## 🧪 3. BẢNG 5 TEST CASE KIỂM THỬ DỮ LIỆU ĐÚNG / SAI (THEO YÊU CẦU SLIDE 16)

Dưới đây là 5 kịch bản kiểm thử được thiết lập và tự động chạy kiểm định trong file `test_crud_cli.php`:

| STT | Tên Test Case | Mục đích kiểm thử | Dữ liệu đầu vào (Input) | Kết quả mong đợi (Expected Output) | Trạng thái |
|:---:|---|---|---|---|:---:|
| **TC1** | **Thêm người dùng hợp lệ (Create)** | Kiểm tra thêm sinh viên mới và băm mật khẩu Bcrypt mặc định 123456 | Mã SV: `224001799`<br>Tên: `Lê Hoàng Nam`<br>Email: `nam224@daihocthudo.edu.vn`<br>Lớp: `1` (TUD-D2024) | Dữ liệu ghi vào CSDL, mật khẩu được băm Bcrypt 60 ký tự, `password_verify` trả về TRUE. Redirect về danh sách kèm Flash Message xanh lá. | ✅ **PASS** |
| **TC2** | **Chặn trùng lặp Mã định danh (Unique Constraint)** | Kiểm tra việc chặn người dùng nhập trùng Mã SV đã tồn tại | Mã SV: `224001729` *(Đã thuộc về SV Trần Thục Linh)* | Bắt lỗi vi phạm ràng buộc Unique, hệ thống ném ngoại lệ RuntimeException: *"Mã định danh hoặc Email này đã tồn tại!"*, không cho lưu vào CSDL. | ✅ **PASS** |
| **TC3** | **Tìm kiếm tương đối với toán tử LIKE % (Search)** | Kiểm tra chức năng tìm kiếm mờ khi người dùng gõ từ khóa | Từ khóa: `kw = Linh` | Câu lệnh SQL thực thi với `%Linh%`, trả về đúng bản ghi sinh viên `Trần Thục Linh` (Mã SV: `224001729`). | ✅ **PASS** |
| **TC4** | **Phân trang an toàn LIMIT / OFFSET (Pagination)** | Kiểm tra công thức phân trang và tính toán số trang | `$perPage = 3`, gọi Trang 1 và Trang 2 | Trang 1 trả về 3 bản ghi đầu tiên; Trang 2 trả về 3 bản ghi tiếp theo, không có sự trùng lặp ID giữa 2 trang. | ✅ **PASS** |
| **TC5** | **Khóa tài khoản bằng phương thức POST (Soft Lock)** | Kiểm tra việc khóa và mở khóa an toàn không xóa cứng | Gửi POST `action = toggle_lock` với `id = 20` | CSDL cập nhật `is_active = 0`, `lock_reason = 'expelled'`. Bấm lần nữa cập nhật `is_active = 1`. Flash message thông báo trạng thái tương ứng. | ✅ **PASS** |

---

## 🚀 4. HƯỚNG DẪN GIẢNG VIÊN VÀ NHÓM CHẠY THỬ

### Cách 1: Chạy kiểm thử tự động trên Terminal (Không cần bật XAMPP)
Mở Terminal tại thư mục gốc bài tập lớn, gõ lệnh:
```powershell
C:\xampp\php\php.exe TranLinh\test_crud_cli.php
```
*(Chương trình sẽ tự động kiểm tra 5 test case và in ra thông báo PASS 100% trên màn hình).*

### Cách 2: Chạy giao diện Web trực quan
1. Khởi động máy chủ ảo PHP tích hợp:
   ```powershell
   cd TranLinh
   C:\xampp\php\php.exe -S localhost:8000
   ```
2. Mở trình duyệt web truy cập:
   👉 **`http://localhost:8000/users.php`**
3. Thử nghiệm đầy đủ: Tìm kiếm người dùng, bấm số trang để chuyển trang, bấm **"+ Thêm tài khoản mới"**, bấm nút **"👁️ Xem"** chi tiết, và bấm nút **"🔒 Khóa / 🔓 Mở"** tài khoản.


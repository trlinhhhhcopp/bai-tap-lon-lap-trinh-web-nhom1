# 5 Test Case – CRUD Phân công giảng dạy (course_assignments)
**Sinh viên:** Lê Hà My (224001737)  
**Bảng:** `course_assignments`  
**Quy tắc nghiệp vụ:** Mỗi lớp học phần (class_id + course_id + semester) chỉ có **duy nhất 1 giảng viên** phụ trách.

---

## Test Case 1 – Thêm phân công thành công (dữ liệu hợp lệ)

| Trường | Giá trị nhập |
|--------|--------------|
| Lớp học | D24-CNTT2 |
| Môn học | NET201 – Quản trị Mạng & An toàn Hệ thống |
| Giảng viên | ThS. Trần Thị Hạnh (GV002) |
| Học kỳ | 2026_1 |
| Năm học | 2026-2027 |

**Kết quả mong đợi:**  
- Thông báo “Thêm phân công giảng dạy thành công.”  
- Bản ghi mới xuất hiện trong danh sách.

---

## Test Case 2 – Trùng phân công (vi phạm UNIQUE)

| Trường | Giá trị nhập |
|--------|--------------|
| Lớp học | D24-CNTT1 |
| Môn học | WEB101 – Lập trình Web & Ứng dụng |
| Giảng viên | Bất kỳ |
| Học kỳ | 2026_1 |

**Kết quả mong đợi:**  
- Báo lỗi: “Lớp học phần này đã có giảng viên phụ trách. Mỗi lớp học phần chỉ được 1 giảng viên.”  
- Không tạo bản ghi mới.

---

## Test Case 3 – Bỏ trống trường bắt buộc

| Trường | Giá trị nhập |
|--------|--------------|
| Lớp học | (không chọn) |
| Môn học | (không chọn) |
| Giảng viên | (không chọn) |

**Kết quả mong đợi:**  
- Báo lỗi: “Vui lòng chọn Lớp học.”  
- Báo lỗi: “Vui lòng chọn Môn học.”  
- Báo lỗi: “Vui lòng chọn Giảng viên phụ trách.”

---

## Test Case 4 – Tìm kiếm + phân trang

| Hành động | Giá trị |
|-----------|---------|
| Gõ từ khóa tìm kiếm | `WEB101` hoặc `Nguyễn Văn Giảng` |
| Chuyển trang | Nếu có nhiều hơn 8 bản ghi |

**Kết quả mong đợi:**  
- Chỉ hiển thị các dòng khớp từ khóa.  
- Phân trang hoạt động đúng (nếu đủ dữ liệu).

---

## Test Case 5 – Xóa bằng POST

| Hành động | Chi tiết |
|-----------|----------|
| Chọn một phân công | Bấm nút **Xóa** |
| Xác nhận hộp thoại | Đồng ý |

**Kết quả mong đợi:**  
- Request gửi bằng phương thức **POST** (không phải GET).  
- Thông báo “Đã xóa phân công giảng dạy.”  
- Bản ghi biến mất khỏi danh sách.

---

## Checklist tiêu chí bài tập

| Tiêu chí | Đạt |
|----------|-----|
| CRUD chạy đúng | ✅ |
| Không có SQL trong file giao diện | ✅ |
| Xóa bằng POST | ✅ |
| Có dữ liệu mẫu để chạy thử | ✅ (seed.sql) |
| Tìm kiếm hoặc phân trang | ✅ (cả hai) |
| 5 test case nhập đúng/sai | ✅ |
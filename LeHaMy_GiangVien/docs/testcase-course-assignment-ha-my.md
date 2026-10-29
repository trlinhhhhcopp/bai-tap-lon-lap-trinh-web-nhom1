# 5 Test Case – CRUD Phân công giảng dạy (course_assignments)

**Sinh viên:** Lê Hà My  
**Bảng:** `course_assignments`  
**Quy tắc:** Mỗi lớp học phần (class_id + course_id + semester) chỉ có **1 giảng viên** phụ trách.

---

## Test Case 1 – Thêm phân công thành công (dữ liệu hợp lệ)

| Trường | Giá trị |
|--------|---------|
| Lớp học | Chọn 1 lớp có sẵn (vd: D24-CNTT2) |
| Môn học | Chọn 1 môn (vd: NET201) |
| Giảng viên | Chọn 1 giảng viên |
| Học kỳ | 2026_1 |
| Năm học | 2026-2027 |

**Kết quả mong đợi:**  
- Thông báo “Thêm phân công giảng dạy thành công.”  
- Bản ghi mới xuất hiện trong danh sách.

---

## Test Case 2 – Trùng phân công (vi phạm UNIQUE)

| Trường | Giá trị |
|--------|---------|
| Lớp + Môn + Học kỳ | Trùng với 1 phân công đã có |
| Giảng viên | Bất kỳ |

**Kết quả mong đợi:**  
- Báo lỗi: “Lớp học phần này đã có giảng viên phụ trách…”  
- Không tạo bản ghi mới.

---

## Test Case 3 – Bỏ trống trường bắt buộc

| Trường | Giá trị |
|--------|---------|
| Lớp học | -- Chọn lớp -- |
| Môn học | -- Chọn môn học -- |
| Giảng viên | -- Chọn giảng viên -- |

**Kết quả mong đợi:**  
- Báo lỗi: “Vui lòng chọn Lớp học.”  
- Báo lỗi: “Vui lòng chọn Môn học.”  
- Báo lỗi: “Vui lòng chọn Giảng viên phụ trách.”

---

## Test Case 4 – Tìm kiếm + phân trang

| Hành động | Giá trị |
|-----------|---------|
| Ô tìm kiếm | `WEB101` hoặc tên giảng viên |
| Phân trang | Nếu > 8 bản ghi |

**Kết quả mong đợi:**  
- Chỉ hiện dòng khớp từ khóa.  
- Phân trang hoạt động đúng.

---

## Test Case 5 – Xóa bằng POST

| Hành động | Chi tiết |
|-----------|----------|
| Bấm nút **Xóa** | Xác nhận hộp thoại |

**Kết quả mong đợi:**  
- Request gửi bằng **POST** (không phải GET).  
- Thông báo “Đã xóa phân công giảng dạy.”  
- Bản ghi biến mất khỏi danh sách.

---

## Checklist tiêu chí

| Tiêu chí | Đạt |
|----------|-----|
| CRUD chạy đúng | ✅ |
| Không SQL trong view | ✅ |
| Xóa bằng POST | ✅ |
| Có dữ liệu mẫu | ✅ |
| Tìm kiếm + phân trang | ✅ |
| Mọi truy vấn có input dùng prepare | ✅ |
| 5 test case | ✅ |
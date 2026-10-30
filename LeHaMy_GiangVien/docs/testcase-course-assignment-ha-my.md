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

## Test Case 6 – Lọc phân công theo lớp và giảng viên

| Hành động | Giá trị |
|-----------|---------|
| Chọn lớp | Một lớp có phân công |
| Chọn giảng viên | Một giảng viên đang phụ trách lớp |
| Bấm **Lọc** | Kết hợp với từ khóa nếu cần |

**Kết quả mong đợi:**
- Chỉ hiện phân công khớp đồng thời với lớp, giảng viên và từ khóa đã chọn.
- Điều kiện lọc được giữ nguyên khi chuyển trang và khi xuất CSV.
- Giảng viên chỉ xem được các phân công của chính mình.

---

## Test Case 7 – Trang quản lý danh mục lớp và môn

| Hành động | Chi tiết |
|-----------|----------|
| Bấm **Quản Lý Lớp & Môn** | Tại danh sách phân công |
| Thêm lớp hoặc môn hợp lệ | Tại trang quản lý danh mục |

**Kết quả mong đợi:**
- Hiển thị trang riêng với biểu mẫu thêm và danh sách lớp/môn hiện có.
- Sau khi thêm thành công, quay lại trang danh mục và thấy dữ liệu vừa thêm.
- Không thể thêm danh mục khi cơ sở dữ liệu đang khóa hoặc khi không phải Admin.

---

## Test Case 8 – Import hàng loạt danh mục và phân công

| Hành động | Chi tiết |
|-----------|----------|
| Tải mẫu CSV hoặc chuẩn bị Excel `.xlsx` | Dùng tiêu đề cột trong mẫu |
| Import lớp/môn hoặc phân công | Từ menu tương ứng |

**Kết quả mong đợi:**
- Dữ liệu hợp lệ được thêm; dòng thiếu dữ liệu, không tìm thấy mã hoặc bị trùng được báo là bỏ qua.
- Sau khi import có dòng thành công, hiển thị bảng riêng chỉ gồm các dòng vừa import thành công; danh sách tổng tương ứng được thu gọn và có thể mở lại.
- Áp dụng cùng cách hiển thị cho lớp học, môn học và phân công; nếu tệp có hơn 500 dòng thành công, thông báo số dòng đang hiển thị.
- Workbook `.xlsx` có định dạng bảng/viền vẫn đọc được giá trị ô.
- Tệp phân công dùng mã lớp, mã môn, mã giảng viên hoặc email, học kỳ và năm học.

---

## Checklist tiêu chí

| Tiêu chí | Đạt |
|----------|-----|
| CRUD chạy đúng | ✅ |
| Không SQL trong view | ✅ |
| Xóa bằng POST | ✅ |
| Có dữ liệu mẫu | ✅ |
| Tìm kiếm + phân trang | ✅ |
| Lọc theo lớp và giảng viên | ✅ |
| Trang quản lý danh mục riêng | ✅ |
| Import CSV/XLSX danh mục và phân công | ✅ |
| Mọi truy vấn có input dùng prepare | ✅ |
| 8 test case | ✅ |
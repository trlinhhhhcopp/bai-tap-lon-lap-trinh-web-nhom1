# 5 Test Case – CRUD Đánh giá CSAT (ticket_ratings)

**Sinh viên:** Nguyễn Ánh Nhi (224001742)  
**Bảng:** `ticket_ratings`  
**Quy tắc nghiệp vụ:** Mỗi phiếu chỉ được đánh giá **1 lần** (UNIQUE ticket_id), điểm từ 1–5 sao.

---

## Test Case 1 – Thêm đánh giá thành công (dữ liệu hợp lệ)

| Trường | Giá trị nhập |
|--------|--------------|
| Phiếu hỗ trợ | Chọn 1 phiếu đã resolved/closed |
| Sinh viên | Chọn 1 sinh viên (ví dụ Phan Công Thành) |
| Điểm CSAT | 5 |
| Nhận xét | KTV hỗ trợ rất nhanh và nhiệt tình |

**Kết quả mong đợi:**  
- Thông báo “Thêm đánh giá CSAT thành công.”  
- Bản ghi mới xuất hiện trong danh sách.

---

## Test Case 2 – Điểm ngoài khoảng 1-5

| Trường | Giá trị nhập |
|--------|--------------|
| Điểm CSAT | 0 hoặc 6 hoặc 10 |

**Kết quả mong đợi:**  
- Báo lỗi: “Điểm CSAT phải từ 1 đến 5 sao.”  
- Không lưu dữ liệu.

---

## Test Case 3 – Đánh giá trùng phiếu (vi phạm UNIQUE)

| Hành động | Chi tiết |
|-----------|----------|
| Chọn phiếu | Phiếu đã có đánh giá rồi |

**Kết quả mong đợi:**  
- Báo lỗi: “Phiếu này đã được đánh giá rồi.”  
- Không tạo bản ghi mới.

---

## Test Case 4 – Tìm kiếm + Lọc + Phân trang

| Hành động | Giá trị |
|-----------|---------|
| Gõ từ khóa | Mã phiếu / tên sinh viên / nội dung nhận xét |
| Lọc theo điểm | Chọn 5 sao |
| Lọc theo sinh viên | Chọn 1 sinh viên cụ thể |
| Chuyển trang | Khi có nhiều hơn 8 bản ghi |

**Kết quả mong đợi:**  
- Chỉ hiển thị các dòng khớp bộ lọc.  
- Phân trang hoạt động đúng.

---

## Test Case 5 – Khóa toàn bộ + Upload CSV + Xóa

| Hành động | Kết quả mong đợi |
|-----------|------------------|
| Bấm **Khóa toàn bộ** | Không còn nút “Thêm đánh giá” và “Upload CSV”. Hiện nút “Mở khóa”. |
| Bấm **Mở khóa** | Khôi phục lại các nút thêm và upload. |
| Upload file CSV hợp lệ | Import thành công, dữ liệu hiện ra danh sách. |
| Xóa 1 đánh giá | Bản ghi biến mất khỏi danh sách. |

---

## Checklist tiêu chí bài tập

| Tiêu chí | Đạt |
|----------|-----|
| CRUD chạy đúng | ✅ |
| Không có SQL trong file giao diện | ✅ |
| Xóa bằng POST | ✅ |
| Có tìm kiếm + lọc + phân trang | ✅ |
| Có chức năng Khóa toàn bộ | ✅ |
| Có Upload CSV | ✅ |
| 5 test case nhập đúng/sai | ✅ |
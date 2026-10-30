# TEST CASES – PhanCongThanh_Admin

## 1. Mục đích kiểm thử

Kiểm tra các chức năng quản lý ngày nghỉ lễ trong hệ thống, bao gồm thêm, sửa, xóa, tìm kiếm và kiểm tra dữ liệu đầu vào.

## 2. Danh sách test case

| Mã TC | Chức năng | Dữ liệu kiểm thử | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
|---|---|---|---|---|---|
| TC01 | Thêm ngày nghỉ lễ | Tên: Ngày nghỉ kiểm tra; ngày bắt đầu: 2026-11-01; ngày kết thúc: 2026-11-01 | Thêm thành công và hiển thị trong danh sách | Đã thêm thành công, ngày nghỉ lễ xuất hiện trong danh sách | PASS |
| TC02 | Kiểm tra tên bị bỏ trống | Để trống tên ngày nghỉ lễ | Hệ thống từ chối dữ liệu và yêu cầu nhập tên | Hệ thống bắt nhập trường tên ngày nghỉ lễ | PASS |
| TC03 | Kiểm tra ngày không hợp lệ | Ngày bắt đầu lớn hơn ngày kết thúc | Hệ thống từ chối dữ liệu và thông báo lỗi | Hiển thị thông báo “Ngày bắt đầu hoặc ngày kết thúc không hợp lệ.” | PASS |
| TC04 | Tìm kiếm ngày nghỉ lễ | Nhập từ khóa: Tết | Hiển thị các ngày nghỉ lễ phù hợp với từ khóa | Đã tìm kiếm và thấy kết quả phù hợp | PASS |
| TC05 | Sửa ngày nghỉ lễ | Chọn một ngày nghỉ lễ có sẵn và thay đổi thông tin | Thông tin được cập nhật và hiển thị đúng trong danh sách | Đã sửa thành công | PASS |

## 3. Kết luận

Đã kiểm thử 5 trường hợp đối với chức năng quản lý ngày nghỉ lễ. Các chức năng thêm, sửa, tìm kiếm và kiểm tra dữ liệu đầu vào hoạt động đúng theo kết quả đã kiểm thử.

Chức năng xóa đã được kiểm tra riêng và hoạt động thành công.

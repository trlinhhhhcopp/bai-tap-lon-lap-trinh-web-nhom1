# 🛠️ HƯỚNG DẪN ĐỒNG BỘ DATABASE DÀNH CHO CÁC THÀNH VIÊN NHÓM 1

> **Dành cho:** Lê Hà My, Nguyễn Ánh Nhi, Phan Công Thành, Trần Thục Linh  
> **Mục đích:** Khắc phục lỗi `Table doesn't exist` hoặc `Unknown column` sau khi `git pull` code mới về máy.

---

### BƯỚC 1: Kéo Code Mới Nhất Về Máy
Mở Git Bash hoặc Terminal trong thư mục dự án và chạy:
```bash
git pull origin main
```

---

### BƯỚC 2: Nạp Lại Database Chuẩn (Chỉ Mất 10 Giây)
1. Mở trình duyệt, truy cập vào **phpMyAdmin**: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Bấm vào thẻ **Import** (hoặc **Nhập**) trên thanh menu trên cùng.
3. Bấm **Choose File** (Chọn tệp) và tìm đến file:
   ```
   database/database_full_nhom1.sql
   ```
4. Cuộn xuống dưới cùng và bấm nút **Import** (hoặc **Thực hiện / Go**).
5. Khi màn hình hiện thông báo màu xanh `Import has been successfully finished` là hoàn tất 100%!

---

### BƯỚC 3: Mở Web Và Kiểm Tra
Truy cập lại hệ thống tại:
[http://localhost/web01/bai-tap-lon-lap-trinh-web-nhom1/](http://localhost/web01/bai-tap-lon-lap-trinh-web-nhom1/)

- Tất cả tài khoản thành viên nhóm (Thanh Ngân, Thục Linh, Hà My, Ánh Nhi, Công Thành) đều có sẵn để đăng nhập 1-click kiểm thử.
- Đầy đủ các phân hệ: **Sinh viên**, **Giảng viên**, **Kỹ thuật viên**, **Quản trị viên**.

---
*Hệ thống đã tích hợp cơ chế tự thích ứng tự động kết nối cả cổng `3306` lẫn `3307` và các mật khẩu phổ biến (`root`, `1234`, `123456`, `admin`,...).*


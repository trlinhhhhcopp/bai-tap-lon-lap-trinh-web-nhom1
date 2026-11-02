# 🛠️ HƯỚNG DẪN ĐỒNG BỘ DATABASE DÀNH CHO CÁC THÀNH VIÊN NHÓM 1

> **Dành cho các thành viên:** Lê Hà My, Nguyễn Ánh Nhi, Phan Công Thành, Trần Thục Linh, Phạm Thị Thanh Ngân  
> **Mục đích:** Đồng bộ dữ liệu CSDL mới nhất, chuẩn **100% UTF-8 Tiếng Việt có dấu** (khắc phục triệt để lỗi bị biến thành ký tự lạ `Quáº£n Trá»‹...` hoặc thiếu bảng/cột).

---

## ⚡ CÁCH 1: ĐỒNG BỘ TỰ ĐỘNG BẰNG 1-CLICK (KHUYÊN DÙNG - 5 GIÂY XONG)

1. Mở Git Bash hoặc Terminal trong thư mục dự án và kéo code mới về:
   ```bash
   git pull origin main
   ```
2. Mở thư mục dự án trên máy tính, nhấp đúp chuột vào file:
   👉 **`dong_bo_database.bat`**
3. Màn hình hiện thông báo xanh lá cây:
   ```
   🎉 DONG BO DATABASE THANH CONG 100%!
      - Tong so nguoi dung: 32 tai khoan (Day du ca 5 thanh vien nhom)
      - Kiem tra ten Tieng Viet: 'Phạm Thị Thanh Ngân' (Chuan UTF-8 khong loi font)
   ```
4. Mở trình duyệt truy cập web: [http://localhost/web01/bai-tap-lon-lap-trinh-web-nhom1/](http://localhost/web01/bai-tap-lon-lap-trinh-web-nhom1/) là dùng được ngay!

---

## 🌐 CÁCH 2: NẠP QUA PHPMYADMIN (NẾU DÙNG GIAO DIỆN WEB)

Nếu bạn quen dùng phpMyAdmin:
1. Mở trình duyệt, truy cập: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Bấm vào tab **Import** (hoặc **Nhập**) trên thanh menu trên cùng.
3. Bấm **Choose File** (Chọn tệp) và trỏ tới file:
   ```
   database/database_full_nhom1.sql
   ```
4. **LƯU Ý QUAN TRỌNG:** Mục **"Character set of the file"** (Bộ mã ký tự) chọn là **`utf-8`**.
5. Cuộn xuống dưới cùng bấm **Import** (hoặc **Thực hiện / Go**).
6. Khi có thông báo màu xanh `Import has been successfully finished` là hoàn tất!

---

## 👥 DANH SÁCH 5 THÀNH VIÊN NHÓM ĐÃ CÓ SẴN TRONG CSDL:

| STT | Mã Sinh Viên | Họ và Tên | Email Đăng Nhập | Mật Khẩu Chung |
|:---:|:---:|:---|:---|:---:|
| 1 | `224001736` | **Phạm Thị Thanh Ngân** (Trưởng nhóm) | `224001736@daihocthudo.edu.vn` | `123456` |
| 2 | `224001729` | **Trần Thục Linh** | `224001729@daihocthudo.edu.vn` | `123456` |
| 3 | `224001735` | **Lê Hà My** | `224001735@daihocthudo.edu.vn` | `123456` |
| 4 | `224001742` | **Nguyễn Ánh Nhi** | `224001742@daihocthudo.edu.vn` | `123456` |
| 5 | `224001746` | **Phan Công Thành** | `224001746@daihocthudo.edu.vn` | `123456` |

*Ngoài ra, hệ thống có sẵn các tài khoản: Quản trị viên (`admin@daihocthudo.edu.vn`), Kỹ thuật viên (`ktv1@daihocthudo.edu.vn` đến `ktv5`), Giảng viên (`nguyenvangiang@daihocthudo.edu.vn`). Mật khẩu tất cả đều là `123456`.*

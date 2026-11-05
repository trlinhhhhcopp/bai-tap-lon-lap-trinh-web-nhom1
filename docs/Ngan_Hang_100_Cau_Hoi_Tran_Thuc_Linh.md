# NGÂN HÀNG 100 CÂU HỎI TRẮC NGHIỆM ĐÁNH GIÁ CHUYÊN SÂU
## PHÂN HỆ QUẢN TRỊ NGƯỜI DÙNG & XÁC THỰC BẢO MẬT (USER MANAGEMENT & AUTH SECURITY)
### ĐỀ TÀI: HỆ THỐNG HELPDESK TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN - ĐẠI HỌC THỦ ĐÔ HÀ NỘI

> **Sinh viên phụ trách:** Trần Thục Linh  
> **Mã sinh viên:** 224001729  
> **Lớp chuyên ngành:** Toán Ứng Dụng D2024 (Khoa: Toán - CNTT)  
> **Giáo viên hướng dẫn:** Trần Thu Phương  
> **Chuẩn kiến trúc & bảo mật:** Clean MVC Pure PHP 8.2, Bcrypt Hashing, ITIL v4, OWASP Top 10, SSPR 15m Token, Guest Rescue Station 2-stage.  

---

## BẢNG MA TRẬN ĐÁP ÁN 100 CÂU HỎI (QUICK ANSWER KEY)

| Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | `B` | **21** | `C` | **41** | `B` | **61** | `B` | **81** | `A` |
| **2** | `C` | **22** | `B` | **42** | `B` | **62** | `B` | **82** | `B` |
| **3** | `A` | **23** | `B` | **43** | `B` | **63** | `B` | **83** | `B` |
| **4** | `B` | **24** | `A` | **44** | `B` | **64** | `B` | **84** | `A` |
| **5** | `B` | **25** | `B` | **45** | `A` | **65** | `B` | **85** | `B` |
| **6** | `B` | **26** | `B` | **46** | `A` | **66** | `B` | **86** | `B` |
| **7** | `B` | **27** | `B` | **47** | `B` | **67** | `B` | **87** | `B` |
| **8** | `C` | **28** | `B` | **48** | `B` | **68** | `B` | **88** | `B` |
| **9** | `A` | **29** | `A` | **49** | `B` | **69** | `B` | **89** | `B` |
| **10** | `C` | **30** | `B` | **50** | `B` | **70** | `B` | **90** | `A` |
| **11** | `B` | **31** | `B` | **51** | `B` | **71** | `A` | **91** | `B` |
| **12** | `B` | **32** | `B` | **52** | `B` | **72** | `B` | **92** | `B` |
| **13** | `B` | **33** | `C` | **53** | `B` | **73** | `B` | **93** | `B` |
| **14** | `A` | **34** | `B` | **54** | `A` | **74** | `B` | **94** | `B` |
| **15** | `B` | **35** | `B` | **55** | `B` | **75** | `B` | **95** | `A` |
| **16** | `B` | **36** | `B` | **56** | `C` | **76** | `B` | **96** | `B` |
| **17** | `B` | **37** | `A` | **57** | `B` | **77** | `B` | **97** | `B` |
| **18** | `B` | **38** | `B` | **58** | `B` | **78** | `B` | **98** | `B` |
| **19** | `B` | **39** | `B` | **59** | `A` | **79** | `B` | **99** | `B` |
| **20** | `B` | **40** | `B` | **60** | `B` | **80** | `B` | **100** | `B` |

---

## 1. CẤU TRÚC THỰC THỂ USERS, PASSWORD_RESETS & CẤU HÌNH KẾT NỐI PDO CHUẨN MỰC

#### Câu 1. Trong Đề án Helpdesk CNTT Nhóm 1, sinh viên Trần Thục Linh (224001729) chịu trách nhiệm chính ở phân hệ cốt lõi nào?

- **A.** Quản trị Danh mục sự cố và phân công điều phối SLA tự động.
- **B.** Quản trị Người dùng, Cơ chế Xác thực Đăng nhập & An toàn Bảo mật Hệ thống (User Management & Auth Security).
- **C.** Quản lý Lịch trực ca của Kỹ thuật viên và Lịch nghỉ Lễ Tết.
- **D.** Xây dựng Báo cáo thống kê hiệu năng KTV và Đánh giá độ hài lòng CSAT.

> **💡 Căn cứ kỹ thuật & Giải thích:** Trần Thục Linh phụ trách thiết kế và phát triển toàn bộ module Quản trị người dùng (User Management), Xác thực danh tính (Authentication), Phân quyền và Bảo mật phiên làm việc.

#### Câu 2. Bảng cơ sở dữ liệu trung tâm nào do Thục Linh thiết kế lưu trữ danh tính của tất cả Giảng viên, Sinh viên, KTV và Admin?

- **A.** `accounts`
- **B.** `system_members`
- **C.** `users`
- **D.** `profiles`

> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `users` là bảng trung tâm lưu trữ mọi tài khoản người dùng trên hệ thống Helpdesk của Đại học Thủ Đô Hà Nội.

#### Câu 3. Trường `user_code` trong bảng `users` có ý nghĩa nghiệp vụ và đặc tính ràng buộc CSDL như thế nào?

- **A.** Mã định danh duy nhất (Mã sinh viên hoặc Mã giảng viên), có chỉ mục UNIQUE để ngăn trùng lặp tài khoản.
- **B.** Mật khẩu bí mật của người dùng khi truy cập vào hệ thống.
- **C.** Số thứ tự tự tăng của người dùng trong hệ thống.
- **D.** Mã phòng máy mà người dùng đang thực hành.

> **💡 Căn cứ kỹ thuật & Giải thích:** `user_code` lưu Mã sinh viên (ví dụ: 224001729) hoặc Mã giảng viên (GV001), bắt buộc duy nhất thông qua ràng buộc `UNIQUE KEY user_code (user_code)`.

#### Câu 4. Trường `role` trong bảng `users` được thiết kế bằng kiểu dữ liệu nào và bao gồm những giá trị định danh nào?

- **A.** `VARCHAR(50)` với giá trị tùy ý không giới hạn.
- **B.** `ENUM('admin', 'agent', 'requester', 'lecturer')` xác định chính xác 4 vai trò trong hệ thống.
- **C.** `INT` với các giá trị 1, 2, 3, 4.
- **D.** `BOOLEAN` chỉ phân biệt quản trị viên hay không.

> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống phân định rõ 4 vai trò thông qua kiểu ENUM: 'admin' (Quản trị viên), 'agent' (Kỹ thuật viên), 'requester' (Sinh viên), 'lecturer' (Giảng viên).

#### Câu 5. Trường `password_hash` trong bảng `users` được quy định độ dài là bao nhiêu để lưu trữ mã băm an toàn từ thuật toán Bcrypt?

- **A.** `VARCHAR(32)` (đủ cho MD5).
- **B.** `VARCHAR(60)` hoặc `VARCHAR(255)` để chứa trọn vẹn chuỗi băm chuẩn 60 ký tự của Bcrypt.
- **C.** `INT(11)`.
- **D.** `TEXT` dài 65.535 ký tự.

> **💡 Căn cứ kỹ thuật & Giải thích:** Thuật toán Bcrypt luôn tạo ra chuỗi băm cố định dài đúng 60 ký tự (bắt đầu bằng `$2y$10$`), do đó `VARCHAR(60)` hoặc `VARCHAR(255)` là thiết kế chuẩn mực.

#### Câu 6. Khóa ngoại `class_id` trong bảng `users` liên kết với bảng `classes(id)` được cấu hình hành vi `ON DELETE` như thế nào để bảo toàn dữ liệu sinh viên khi lớp học giải thể?

- **A.** `ON DELETE CASCADE` (xóa luôn toàn bộ sinh viên trong lớp).
- **B.** `ON DELETE SET NULL` (đặt trường `class_id` về NULL, giữ nguyên hồ sơ và lịch sử sự cố của sinh viên).
- **C.** `ON DELETE RESTRICT` (ngăn cấm hoàn toàn không cho xóa lớp học).
- **D.** `ON DELETE NO ACTION`.

> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng `ON DELETE SET NULL` giúp giữ lại tài khoản và toàn bộ phiếu hỗ trợ của sinh viên trong quá khứ kể cả khi lớp học của niên khóa đó bị xóa khỏi danh mục.

#### Câu 7. Bảng phụ `password_resets` do Thục Linh thiết kế đóng vai trò gì trong cơ chế tự phục hồi tài khoản?

- **A.** Lưu mật khẩu cũ của người dùng để khôi phục khi quên.
- **B.** Lưu trữ token xác thực tạm thời, địa chỉ email và thời gian hết hạn (expires_at) cho tính năng Quên mật khẩu (SSPR).
- **C.** Lưu danh sách các tài khoản bị Admin cấm đăng nhập.
- **D.** Lưu trữ lịch sử chat giữa sinh viên và KTV.

> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `password_resets` gồm: `id`, `user_id`, `email`, `token`, `created_at`, `expires_at` phục vụ quy trình xác thực token đặt lại mật khẩu.

#### Câu 8. Trong file kết nối CSDL `TranLinh/db.php`, thuộc tính nào của PDO được kích hoạt để tự động bắt lỗi và dừng chương trình khi có lỗi SQL?

- **A.** `PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT`
- **B.** `PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING`
- **C.** `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- **D.** `PDO::ATTR_AUTOCOMMIT => false`

> **💡 Căn cứ kỹ thuật & Giải thích:** `PDO::ERRMODE_EXCEPTION` buộc PDO phải ném ngoại lệ `PDOException` mỗi khi câu truy vấn SQL gặp lỗi cú pháp hoặc vi phạm ràng buộc dữ liệu.

#### Câu 9. Thuộc tính `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` mang lại lợi ích gì cho việc xử lý kết quả truy vấn trong PHP?

- **A.** Chỉ trả về mảng kết hợp với tên cột làm chỉ mục (ví dụ `$row['email']`), tiết kiệm 50% bộ nhớ so với `FETCH_BOTH`.
- **B.** Tự động mã hóa kết quả truy vấn thành file JSON.
- **C.** Chỉ trả về các chỉ số dạng số nguyên 0, 1, 2.
- **D.** Tự động xóa kết quả khỏi bộ nhớ đệm sau 1 giây.

> **💡 Căn cứ kỹ thuật & Giải thích:** `PDO::FETCH_ASSOC` loại bỏ các phần tử trùng lặp dạng số nguyên trong kết quả mảng, giúp code dễ đọc, tường minh và tiết kiệm tài nguyên máy chủ.

#### Câu 10. Chuỗi DSN kết nối PDO trong hệ thống của Thục Linh chỉ định bộ mã ký tự nào để hỗ trợ 100% tiếng Việt có dấu và biểu tượng cảm xúc (emoji)?

- **A.** `charset=latin1`
- **B.** `charset=ascii`
- **C.** `charset=utf8mb4`
- **D.** `charset=utf16`

> **💡 Căn cứ kỹ thuật & Giải thích:** `utf8mb4` là chuẩn mã hóa cao cấp nhất trong MySQL, hỗ trợ đầy đủ tiếng Việt Unicode có dấu và toàn bộ bảng biểu tượng cảm xúc emoji 4-byte.

#### Câu 11. Trường `academic_status` trong bảng `users` được thiết kế kiểu ENUM gồm các trạng thái học tập nào của sinh viên?

- **A.** `'freshman'`, `'senior'`
- **B.** `'active'`, `'graduated'`, `'suspended'`, `'expelled'`
- **C.** `'pass'`, `'fail'`
- **D.** `'online'`, `'offline'`

> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `users` quản lý 4 trạng thái học tập: 'active' (Đang theo học), 'graduated' (Đã tốt nghiệp), 'suspended' (Bảo lưu / Đình chỉ tạm thời), 'expelled' (Buộc thôi học).

#### Câu 12. Trường `is_first_login` (kiểu `TINYINT(1)` mặc định là 1 khi tạo mới) trong bảng `users` phục vụ chính sách an toàn thông tin nào?

- **A.** Xác định tài khoản có quyền truy cập Internet hay không.
- **B.** Bắt buộc người dùng phải đổi mật khẩu khởi tạo mặc định (`123456`) ngay trong lần đăng nhập đầu tiên trước khi được sử dụng hệ thống.
- **C.** Đánh dấu tài khoản vừa nạp tiền vào thẻ sinh viên.
- **D.** Kiểm tra xem người dùng có phải là Admin hay không.

> **💡 Căn cứ kỹ thuật & Giải thích:** Khi `is_first_login = 1`, hệ thống chuyển hướng bắt buộc người dùng vào màn hình đổi mật khẩu, triệt tiêu nguy cơ tài khoản bị chiếm đoạt do dùng mật khẩu mặc định.

#### Câu 13. Trường `failed_attempts` (kiểu `INT` mặc định 0) trong bảng `users` được sử dụng để làm gì?

- **A.** Đếm số lần sinh viên nộp bài tập muộn.
- **B.** Đếm số lần đăng nhập sai mật khẩu liên tiếp để kích hoạt cơ chế khóa tạm thời chống tấn công dò quét Brute-Force.
- **C.** Đếm số lần KTV chuyển giao phiếu hỗ trợ.
- **D.** Đếm số lần Admin chỉnh sửa phân quyền.

> **💡 Căn cứ kỹ thuật & Giải thích:** Mỗi lần người dùng nhập sai mật khẩu, `failed_attempts` tăng thêm 1. Khi đạt ngưỡng cảnh báo (>= 5 lần), tài khoản sẽ bị tạm khóa.

#### Câu 14. Trường `locked_until` (kiểu `DATETIME` NULL) trong bảng `users` có chức năng kỹ thuật gì?

- **A.** Ghi nhận mốc thời gian hết hạn khóa tạm thời; nếu thời gian hiện tại nhỏ hơn `locked_until`, hệ thống lập tức từ chối đăng nhập.
- **B.** Lưu thời điểm tài khoản hết hạn hợp đồng lao động.
- **C.** Ghi nhận thời gian KTV hết ca trực.
- **D.** Lưu thời điểm kết thúc kỳ thi học phần.

> **💡 Căn cứ kỹ thuật & Giải thích:** Nếu `locked_until > NOW()`, người dùng bị khóa đăng nhập tạm thời. Khi thời gian này trôi qua, người dùng có thể thử đăng nhập lại mà không cần Admin can thiệp thủ công.

#### Câu 15. Trường `lock_reason` (kiểu `VARCHAR(255)` NULL) trong bảng `users` được cập nhật khi nào?

- **A.** Khi người dùng tự nguyện khóa tài khoản Facebook cá nhân.
- **B.** Khi Quản trị viên (Admin) hoặc hệ thống tự động khóa tài khoản, lưu lại lý do vi phạm (ví dụ: 'expelled', 'brute_force_lock', 'admin_suspended').
- **C.** Khi KTV từ chối tiếp nhận phiếu hỗ trợ.
- **D.** Khi sinh viên xin nghỉ ốm có phép.

> **💡 Căn cứ kỹ thuật & Giải thích:** `lock_reason` giúp ghi vết kiểm toán minh bạch lý do tại sao tài khoản bị vô hiệu hóa, phục vụ tra cứu khi người dùng khiếu nại.

#### Câu 16. Trong quy chuẩn kết nối CSDL, tại sao Thục Linh đóng gói hàm `db(): PDO` vào một file riêng `db.php` thay vì khởi tạo kết nối rải rác ở nhiều file?

- **A.** Để tăng số lượng dòng code nộp cho giảng viên.
- **B.** Tuân thủ nguyên tắc DRY (Don't Repeat Yourself), quản lý thông tin kết nối tập trung tại một nơi duy nhất và tái sử dụng đối tượng PDO xuyên suốt ứng dụng.
- **C.** Vì PHP không cho phép viết lệnh `new PDO` quá 1 lần trong cả dự án.
- **D.** Để ngăn chặn các thành viên khác trong nhóm xem mật khẩu CSDL.

> **💡 Căn cứ kỹ thuật & Giải thích:** Tập trung hóa hàm kết nối trong `db.php` giúp dễ dàng cấu hình môi trường, bảo trì mã nguồn và áp dụng thiết kế Singleton/Shared Connection hiệu quả.

#### Câu 17. Hàm tiện ích `set_flash($type, $message)` trong `TranLinh/db.php` lưu thông báo phản hồi vào đâu để hiển thị cho người dùng ở trang tiếp theo?

- **A.** Ghi đè trực tiếp vào file mã nguồn PHP.
- **B.** Lưu vào biến phiên làm việc `$_SESSION['flash'] = ['type' => $type, 'message' => $message]`; sau khi hiển thị sẽ tự động xóa (Unset).
- **C.** Gửi tin nhắn SMS đến điện thoại của người dùng.
- **D.** Lưu vĩnh viễn vào bảng `flash_messages` trong CSDL MySQL.

> **💡 Căn cứ kỹ thuật & Giải thích:** Flash Message lưu tạm vào Session và bị hủy ngay sau lần hiển thị đầu tiên, đảm bảo người dùng F5 tải lại trang không bị hiện lại thông báo cũ.

#### Câu 18. Chỉ mục `UNIQUE KEY email (email)` trên bảng `users` bảo đảm điều gì trong hệ thống xác thực của Đại học Thủ Đô Hà Nội?

- **A.** Một tài khoản sinh viên có thể đăng ký bằng 10 email khác nhau.
- **B.** Tuyệt đối không có hai tài khoản nào dùng chung một địa chỉ email, bảo đảm tính định danh duy nhất cho tính năng Quên mật khẩu SSPR.
- **C.** Email bắt buộc phải có đuôi `@gmail.com`.
- **D.** Mọi email gửi đi đều được mã hóa bằng khóa bí mật.

> **💡 Căn cứ kỹ thuật & Giải thích:** Ràng buộc duy nhất trên cột `email` ngăn chặn xung đột tài khoản và bảo đảm token khôi phục mật khẩu gửi đến chính xác chủ sở hữu duy nhất.

#### Câu 19. Trường `avatar_path` (kiểu `VARCHAR(255)` NULL) trong bảng `users` được ứng dụng quản lý như thế nào?

- **A.** Lưu toàn bộ nội dung nhị phân (BLOB) của bức ảnh trực tiếp vào bảng `users`.
- **B.** Chỉ lưu đường dẫn tương đối trỏ tới tập tin ảnh đại diện lưu trong thư mục `uploads/avatars/`, tối ưu kích thước CSDL.
- **C.** Lưu địa chỉ URL của tài khoản Instagram cá nhân.
- **D.** Lưu mã màu HEX của giao diện.

> **💡 Căn cứ kỹ thuật & Giải thích:** Lưu đường dẫn tập tin thay vì dữ liệu nhị phân (BLOB) là giải pháp tối ưu giúp CSDL gọn nhẹ, tốc độ truy vấn nhanh và máy chủ web phục vụ ảnh tĩnh trực tiếp.

#### Câu 20. Trong bảng `users`, trường `is_active` (kiểu `TINYINT(1)` mặc định là 1) đại diện cho cơ chế kiểm soát nào?

- **A.** Kiểm tra xem người dùng hiện có đang online bật trình duyệt hay không.
- **B.** Trạng thái kích hoạt tài khoản: 1 là tài khoản đang hoạt động bình thường, 0 là tài khoản đã bị khóa (Soft Lock).
- **C.** Đánh dấu người dùng đã đóng học phí kỳ này hay chưa.
- **D.** Kiểm tra xem người dùng có đang mượn sách thư viện hay không.

> **💡 Căn cứ kỹ thuật & Giải thích:** `is_active` là cờ mềm (Soft Flag) dùng để vô hiệu hóa quyền đăng nhập của người dùng vi phạm mà không làm mất mát bất kỳ liên kết dữ liệu nào trong quá khứ.

## 2. CƠ CHẾ BĂM MẬT KHẨU BCRYPT, ĐĂNG NHẬP & NGĂN NGỪA TẤN CÔNG BRUTE-FORCE

#### Câu 21. Hàm băm mật khẩu chuẩn nào của PHP được Thục Linh triển khai trong toàn bộ hệ thống để bảo vệ mật khẩu người dùng?

- **A.** `md5($password)`
- **B.** `sha1($password)`
- **C.** `password_hash($password, PASSWORD_BCRYPT)`
- **D.** `base64_encode($password)`

> **💡 Căn cứ kỹ thuật & Giải thích:** `password_hash()` với thuật toán `PASSWORD_BCRYPT` tự động sinh muối ngẫu nhiên (salt) và có chi phí tính toán có thể điều chỉnh (cost factor), chống lại tấn công Rainbow Table và GPU Cracking.

#### Câu 22. Tại sao các thuật toán như MD5 hay SHA-1 tuyệt đối không được sử dụng để lưu mật khẩu trong hệ thống Helpdesk chuẩn ITIL?

- **A.** Vì MD5 và SHA-1 quá chậm khiến người dùng chờ lâu.
- **B.** Vì chúng là hàm băm nhanh, dễ bị bẻ khóa bằng bảng băm tính trước (Rainbow Tables) và các bộ xử lý đồ họa GPU tốc độ cao.
- **C.** Vì MD5 không hỗ trợ ký tự tiếng Việt.
- **D.** Vì MD5 tốn quá nhiều dung lượng đĩa cứng.

> **💡 Căn cứ kỹ thuật & Giải thích:** MD5 và SHA-1 được thiết kế cho việc kiểm tra toàn vẹn tập tin chứ không phải băm mật khẩu; tốc độ tính toán quá nhanh biến chúng thành mục tiêu dễ dàng của các cuộc tấn công Brute-Force hiện đại.

#### Câu 23. Khi người dùng đăng nhập, hàm PHP nào được Thục Linh sử dụng để so sánh mật khẩu dạng văn bản thuần với chuỗi băm Bcrypt trong CSDL?

- **A.** `if ($password == $user['password_hash'])`
- **B.** `password_verify($password, $user['password_hash'])`
- **C.** `strcmp($password, $user['password_hash'])`
- **D.** `hash_equals($password, $user['password_hash'])`

> **💡 Căn cứ kỹ thuật & Giải thích:** `password_verify()` tự động trích xuất chuỗi muối (salt) và chi phí thuật toán (cost) nhúng bên trong chuỗi băm Bcrypt để thực hiện băm và đối sánh một cách an toàn.

#### Câu 24. Chuỗi mã băm Bcrypt được sinh ra từ `password_hash('123456', PASSWORD_BCRYPT)` có đặc điểm nhận dạng trực quan nào?

- **A.** Luôn luôn bắt đầu bằng ký tự tiền tố `$2y$10$` và có tổng độ dài đúng 60 ký tự.
- **B.** Luôn luôn có độ dài 32 ký tự dạng số thập lục phân.
- **C.** Kết thúc bằng dấu `==` giống như mã Base64.
- **D.** Chứa toàn chữ số từ 0 đến 9.

> **💡 Căn cứ kỹ thuật & Giải thích:** Ký tự tiền tố `$2y$` đại diện cho phiên bản Bcrypt trong PHP, `10$` là cost factor (chi phí tính toán 2^10 = 1024 vòng lặp), và tiếp theo là 22 ký tự salt cùng 31 ký tự mã băm.

#### Câu 25. Nếu hai tài khoản sinh viên khác nhau đều đặt mật khẩu giống hệt nhau là `123456`, giá trị `password_hash` lưu trong CSDL của họ sẽ như thế nào?

- **A.** Hoàn toàn giống hệt nhau từng ký tự.
- **B.** Hoàn toàn khác biệt nhau, vì Bcrypt tự động sinh ra một chuỗi muối ngẫu nhiên (salt) độc lập cho mỗi lần băm.
- **C.** Tài khoản tạo sau sẽ bị báo lỗi trùng lặp mật khẩu.
- **D.** Cả hai tài khoản đều bị reset về rỗng.

> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế tự động sinh muối ngẫu nhiên (CSPRNG salt) của `password_hash()` bảo đảm 2 mật khẩu giống hệt nhau vẫn tạo ra hai chuỗi băm hoàn toàn khác biệt, vô hiệu hóa hoàn toàn bảng tra cứu ngược Rainbow Tables.

#### Câu 26. Trong quy trình xử lý đăng nhập tại `login.php`, bước kiểm tra trạng thái kích hoạt tài khoản được thực hiện như thế nào?

- **A.** Chỉ kiểm tra quyền Admin, không quan tâm người dùng thường.
- **B.** Nếu `$user['is_active'] == 0`, hệ thống lập tức chặn đăng nhập và hiển thị thông báo tài khoản đang bị khóa kèm lý do.
- **C.** Tự động đổi mật khẩu của người dùng thành rỗng.
- **D.** Gửi yêu cầu đóng học phí đến ngân hàng.

> **💡 Căn cứ kỹ thuật & Giải thích:** Tài khoản có `is_active = 0` (bị khóa mềm) sẽ bị từ chối cấp phiên làm việc (Session) ngay từ cổng đăng nhập.

#### Câu 27. Khi người dùng nhập sai mật khẩu đăng nhập, câu lệnh SQL nào được kích hoạt để tăng chỉ số cảnh báo dò quét mật khẩu?

- **A.** `UPDATE users SET is_active = 0 WHERE id = :id`
- **B.** `UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = :id`
- **C.** `DELETE FROM users WHERE id = :id`
- **D.** `UPDATE users SET password_hash = '123456' WHERE id = :id`

> **💡 Căn cứ kỹ thuật & Giải thích:** `failed_attempts = failed_attempts + 1` ghi nhận chính xác số lần xác thực thất bại liên tiếp nhằm phát hiện dấu hiệu tấn công tự động.

#### Câu 28. Khi số lần đăng nhập sai `failed_attempts` đạt ngưỡng 5 lần liên tiếp, hệ thống của Thục Linh kích hoạt cơ chế phòng vệ nào?

- **A.** Xóa vĩnh viễn tài khoản người dùng khỏi hệ thống.
- **B.** Tạm thời khóa tài khoản trong 15 phút bằng cách gán `locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)` và lưu lý do 'brute_force_lock'.
- **C.** Tự động gửi tin nhắn SMS cảnh báo cho toàn trường.
- **D.** Chuyển toàn bộ dữ liệu người dùng sang máy chủ khác.

> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế khóa tạm 15 phút (Soft Timeout Lock) làm chậm tối đa tốc độ dò quét tự động của hacker mà không làm gián đoạn vĩnh viễn tài khoản của người dùng hợp lệ vô tình quên mật khẩu.

#### Câu 29. Khi người dùng đăng nhập thành công với mật khẩu chính xác, hệ thống thực hiện câu lệnh SQL nào để làm sạch trạng thái phòng vệ?

- **A.** `UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id`
- **B.** `DELETE FROM users WHERE id = :id`
- **C.** `UPDATE users SET is_active = 0 WHERE id = :id`
- **D.** `UPDATE users SET role = 'admin' WHERE id = :id`

> **💡 Căn cứ kỹ thuật & Giải thích:** Ngay khi xác thực mật khẩu thành công, bộ đếm sai sót được đặt lại về 0 và thời hạn khóa tạm thời được hủy bỏ để người dùng sử dụng dịch vụ trơn tru.

#### Câu 30. Cơ chế ép buộc đổi mật khẩu lần đầu (`is_first_login = 1`) được kiểm tra và xử lý ở vị trí nào trong ứng dụng?

- **A.** Chỉ kiểm tra khi sinh viên bấm vào nút Đăng xuất.
- **B.** Kiểm tra ngay sau khi đăng nhập thành công hoặc trong middleware kiểm tra phiên làm việc; nếu `is_first_login == 1`, chuyển hướng bắt buộc tới `change_password.php`.
- **C.** Gửi email nhắc nhở sau 30 ngày.
- **D.** Tự động sinh mật khẩu mới và in ra máy in.

> **💡 Căn cứ kỹ thuật & Giải thích:** Chính sách bảo mật bắt buộc người dùng nhận tài khoản mới hoặc vừa được Admin reset về mặc định (`123456`) phải đặt mật khẩu riêng tư trước khi có thể truy cập bất kỳ tính năng nào khác.

#### Câu 31. Trong form đổi mật khẩu `change_password.php`, cơ chế nào được Thục Linh lập trình để ngăn chặn người dùng đặt lại mật khẩu giống y hệt mật khẩu cũ?

- **A.** Kiểm tra độ dài chuỗi có lớn hơn 6 ký tự hay không.
- **B.** Dùng `password_verify($newPassword, $currentUser['password_hash'])`; nếu trả về TRUE thì từ chối và báo lỗi 'Mật khẩu mới không được trùng với mật khẩu hiện tại'.
- **C.** Bắt buộc mật khẩu mới phải chứa tên của sinh viên.
- **D.** Cấm người dùng gõ phím 'Enter'.

> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng `password_verify` so sánh mật khẩu mới với chuỗi băm đang lưu giúp nhận diện chính xác trường hợp người dùng nhập lại mật khẩu cũ kể cả khi mật khẩu đó đang là mặc định.

#### Câu 32. Để bảo vệ phiên làm việc chống lại tấn công Session Hijacking và Session Fixation khi người dùng đăng nhập thành công, hàm PHP nào bắt buộc phải được gọi?

- **A.** `session_destroy()`
- **B.** `session_regenerate_id(true)`
- **C.** `session_unset()`
- **D.** `session_encode()`

> **💡 Căn cứ kỹ thuật & Giải thích:** `session_regenerate_id(true)` tạo mã định danh Session ID hoàn toàn mới và hủy bỏ định danh cũ, vô hiệu hóa nguy cơ kẻ gian đánh cắp Session ID trước khi người dùng đăng nhập.

#### Câu 33. Biến siêu toàn cục nào trong PHP lưu trữ thông tin danh tính và vai trò của người dùng đã xác thực thành công trong suốt phiên làm việc?

- **A.** `$_POST`
- **B.** `$_COOKIE`
- **C.** `$_SESSION['user']`
- **D.** `$_ENV`

> **💡 Căn cứ kỹ thuật & Giải thích:** `$_SESSION['user']` lưu các thông tin như `id`, `user_code`, `name`, `role`, `email`, được máy chủ lưu trữ an toàn phía backend mà trình duyệt không thể tùy tiện sửa đổi.

#### Câu 34. Khi người dùng bấm nút 'Đăng xuất' (Logout), quy trình hủy bỏ phiên làm việc chuẩn mực gồm những bước nào?

- **A.** Chỉ cần chuyển hướng người dùng về trang chủ bằng `header('Location: /')`.
- **B.** Xóa dữ liệu session (`$_SESSION = []`), xóa cookie session trên trình duyệt, hủy session trên server (`session_destroy()`) và chuyển hướng về `login.php`.
- **C.** Xóa tài khoản người dùng khỏi CSDL.
- **D.** Tắt máy chủ web Apache.

> **💡 Căn cứ kỹ thuật & Giải thích:** Hủy sạch mảng `$_SESSION`, gọi `session_destroy()` và xóa cookie `session_name()` bảo đảm phiên làm việc bị chấm dứt triệt để trên cả trình duyệt và máy chủ.

#### Câu 35. Hàm phân quyền `checkRole($allowedRoles)` trong hệ thống của Thục Linh hoạt động như thế nào khi một sinh viên cố tình truy cập vào trang quản trị Admin?

- **A.** Tự động nâng cấp quyền sinh viên đó thành Admin.
- **B.** Kiểm tra `$_SESSION['user']['role']`; nếu không nằm trong danh sách `$allowedRoles`, lập tức chặn truy cập, thiết lập Flash message cảnh báo và chuyển hướng về trang chủ.
- **C.** Hiển thị màn hình trắng không báo lỗi.
- **D.** Gửi thông báo mời phụ huynh lên trường.

> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểm tra vai trò bằng hàm phân quyền tập trung ngăn chặn triệt để lỗ hổng Broken Access Control (IDOR & Privilege Escalation) theo chuẩn OWASP Top 10.

#### Câu 36. Để ngăn chặn tấn công giả mạo yêu cầu từ trang khác (CSRF) trong form đăng nhập và đổi mật khẩu, kỹ thuật nào được áp dụng?

- **A.** Kiểm tra địa chỉ IP của người dùng mỗi giây.
- **B.** Sinh một mã ngẫu nhiên bí mật `$_SESSION['csrf_token']`, nhúng vào thẻ `<input type="hidden" name="csrf_token">` và kiểm chứng bằng `hash_equals()` khi submit.
- **C.** Bắt người dùng nhập lại mã sinh viên 3 lần.
- **D.** Chặn tất cả các trình duyệt Chrome.

> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế Synchronizer Token Pattern với hàm so sánh thời gian cố định `hash_equals()` bảo đảm yêu cầu gửi lên thực sự xuất phát từ chính form trên hệ thống của trường.

#### Câu 37. Trong file cấu hình phiên làm việc của PHP, cờ bảo mật nào cho cookie session ngăn không cho mã độc JavaScript (XSS) đánh cắp Session ID?

- **A.** `session.cookie_httponly = 1`
- **B.** `session.cookie_secure = 0`
- **C.** `session.use_trans_sid = 1`
- **D.** `session.auto_start = 1`

> **💡 Căn cứ kỹ thuật & Giải thích:** `HttpOnly` chỉ thị cho trình duyệt cấm mã JavaScript truy cập vào thuộc tính `document.cookie` chứa Session ID, vô hiệu hóa kỹ thuật đánh cắp phiên qua lỗi XSS.

#### Câu 38. Khi người dùng có trạng thái học tập `academic_status = 'expelled'` (Buộc thôi học) đăng nhập, hệ thống sẽ phản hồi ra sao?

- **A.** Cho phép đăng nhập bình thường để xem bảng điểm.
- **B.** Hệ thống chặn đăng nhập với thông báo: 'Tài khoản đã bị chấm dứt do trạng thái học tập Buộc thôi học. Vui lòng liên hệ Phòng Đào tạo'.
- **C.** Chuyển sinh viên sang lớp học khác.
- **D.** Tự động trừ 10 điểm rèn luyện.

> **💡 Căn cứ kỹ thuật & Giải thích:** Đồng bộ trạng thái học tập vào cổng xác thực giúp hệ thống tự động ngắt quyền truy cập tài nguyên CNTT của những cá nhân không còn thuộc diện quản lý của nhà trường.

#### Câu 39. Nếu Quản trị viên muốn mở khóa khẩn cấp cho một tài khoản đang bị hệ thống khóa tạm thời do nhập sai mật khẩu, Admin sẽ thao tác trường nào?

- **A.** Xóa tài khoản đó rồi tạo lại tài khoản mới.
- **B.** Cập nhật `failed_attempts = 0`, `locked_until = NULL`, `is_active = 1` và `lock_reason = NULL` trong trang Quản trị người dùng.
- **C.** Khởi động lại máy chủ MySQL.
- **D.** Chuyển trạng thái học tập của sinh viên thành 'graduated'.

> **💡 Căn cứ kỹ thuật & Giải thích:** Admin có đầy đủ giao diện và công cụ thiết lập lại các chỉ số an toàn về trạng thái kích hoạt bình thường mà không cần can thiệp trực tiếp vào dòng lệnh CSDL.

#### Câu 40. Mật khẩu mặc định khởi tạo ban đầu cho các tài khoản mới được nhập từ danh sách sinh viên là gì theo đặc tả của đề án?

- **A.** `admin123`
- **B.** `123456` (được băm Bcrypt tự động và đánh dấu cờ `is_first_login = 1`)
- **C.** `password`
- **D.** Trùng với Mã sinh viên

> **💡 Căn cứ kỹ thuật & Giải thích:** Mật khẩu khởi tạo thống nhất là `123456` kết hợp bắt buộc đổi mật khẩu lần đầu giúp đơn giản hóa quy trình cấp phát tài khoản đại trà cho sinh viên đầu khóa.

## 3. QUY TRÌNH QUÊN MẬT KHẨU SSPR & TRẠM CỨU HỘ TÀI KHOẢN 2 CẤP ĐỘ

#### Câu 41. Thuật ngữ 'SSPR' trong phân hệ do Thục Linh phụ trách là viết tắt của cụm từ tiếng Anh nào?

- **A.** Single Sign-on Password Recovery
- **B.** Self-Service Password Reset (Tự đặt lại mật khẩu qua email)
- **C.** Student System Protection Rule
- **D.** Security Server Protocol Routing

> **💡 Căn cứ kỹ thuật & Giải thích:** SSPR (Self-Service Password Reset) là tính năng cho phép người dùng tự khôi phục mật khẩu thông qua hộp thư điện tử đã đăng ký mà không cần nhờ KTV hỗ trợ thủ công.

#### Câu 42. Hàm PHP bảo mật cao nào được Thục Linh sử dụng để sinh chuỗi token ngẫu nhiên không thể đoán trước cho tính năng Quên mật khẩu?

- **A.** `rand(1000, 9999)`
- **B.** `bin2hex(random_bytes(32))`
- **C.** `time()`
- **D.** `microtime()`

> **💡 Căn cứ kỹ thuật & Giải thích:** `random_bytes()` sử dụng bộ sinh số ngẫu nhiên an toàn mật mã (CSPRNG) của hệ điều hành, tạo ra chuỗi 64 ký tự hex hoàn toàn không thể đoán trước hoặc suy luận toán học.

#### Câu 43. Thời hạn hiệu lực của một token đặt lại mật khẩu trong bảng `password_resets` được cấu hình là bao lâu?

- **A.** 24 giờ.
- **B.** 15 phút kể từ thời điểm gửi yêu cầu (`NOW() + INTERVAL 15 MINUTE`).
- **C.** Vĩnh viễn không bao giờ hết hạn.
- **D.** 30 giây.

> **💡 Căn cứ kỹ thuật & Giải thích:** Giới hạn thời gian token trong vòng 15 phút là tiêu chuẩn an ninh thông tin quốc tế, giảm thiểu tối đa rủi ro nếu đường link bị lộ trong hộp thư người dùng.

#### Câu 44. Vì môi trường phát triển cục bộ (Localhost XAMPP) không có máy chủ SMTP gửi mail ra ngoài Internet, Thục Linh đã triển khai giải pháp sáng tạo nào?

- **A.** Bỏ qua tính năng quên mật khẩu không kiểm thử.
- **B.** Xây dựng Hộp thư ảo cục bộ (Local Fake Mailbox) ghi nhận toàn bộ email gửi đi vào tệp tin JSON `storage/mailbox.json` và giao diện Web xem thư thử nghiệm.
- **C.** Bắt buộc người dùng gọi điện thoại cho tổng đài Viettel.
- **D.** In email ra máy in để sinh viên đến nhận.

> **💡 Căn cứ kỹ thuật & Giải thích:** `storage/mailbox.json` đóng vai trò một hòm thư mô phỏng chuyên nghiệp, lưu toàn bộ người nhận, tiêu đề, nội dung và link reset token, giúp nghiệm thu tính năng mượt mà 100% không cần kết nối mạng bên ngoài.

#### Câu 45. Cấu trúc bản ghi lưu trong bảng `password_resets` bao gồm những cột dữ liệu nào?

- **A.** `id`, `user_id`, `email`, `token`, `created_at`, `expires_at`
- **B.** `id`, `password`, `retype_password`
- **C.** `id`, `username`, `phone_number`
- **D.** `id`, `otp_code`, `status`

> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `password_resets` lưu liên kết tài khoản (`user_id`, `email`), mã token bảo mật và mốc thời gian hết hạn để hệ thống đối soát khi người dùng bấm link.

#### Câu 46. Khi người dùng nhấn vào liên kết `reset_password.php?token=xyz`, câu lệnh SQL nào được dùng để kiểm tra tính hợp lệ của token?

- **A.** `SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()`
- **B.** `SELECT * FROM users WHERE email = :email`
- **C.** `DELETE FROM password_resets WHERE token = :token`
- **D.** `SELECT * FROM password_resets ORDER BY id DESC`

> **💡 Căn cứ kỹ thuật & Giải thích:** Câu truy vấn kiểm tra đồng thời cả 2 điều kiện: token phải trùng khớp và thời điểm hiện tại `NOW()` phải nhỏ hơn thời hạn hết hạn `expires_at`.

#### Câu 47. Sau khi người dùng nhập mật khẩu mới và xác nhận thành công tại trang `reset_password.php`, hệ thống thực hiện thao tác gì với bảng `password_resets`?

- **A.** Giữ nguyên token để người dùng có thể bấm lại lần sau.
- **B.** Xóa token vừa sử dụng khỏi bảng `password_resets` (hoặc đánh dấu đã dùng) để ngăn chặn tấn công Replay Attack.
- **C.** Đổi token đó thành mật khẩu mới.
- **D.** Xóa toàn bộ CSDL của trường.

> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc bảo mật One-Time Token (Token dùng một lần): Ngay sau khi đặt lại mật khẩu thành công, token phải bị tiêu hủy lập tức để ngăn kẻ khác tái sử dụng liên kết trong lịch sử trình duyệt.

#### Câu 48. Nếu một người dùng liên tục yêu cầu gửi mã quên mật khẩu nhiều lần cho cùng một email trong vòng vài phút, giải pháp xử lý là gì?

- **A.** Gửi 100 email cùng một lúc.
- **B.** Xóa các token cũ chưa hết hạn của người dùng đó trước khi cấp phát một token mới, hoặc giới hạn tần suất (Rate Limiting).
- **C.** Khóa vĩnh viễn email đó.
- **D.** Tự động tắt mạng Internet của người dùng.

> **💡 Căn cứ kỹ thuật & Giải thích:** Hủy bỏ các token cũ của cùng tài khoản trước khi sinh token mới bảo đảm tại một thời điểm chỉ có duy nhất một token có hiệu lực, tránh gây rối loạn cho người dùng.

#### Câu 49. Trong trường hợp sinh viên bị mất cả mật khẩu Helpdesk VÀ mất quyền truy cập vào hộp thư email sinh viên, họ sử dụng tính năng cứu trợ nào?

- **A.** Phải làm đơn thôi học nộp cho nhà trường.
- **B.** Trạm Cứu Hộ Tài Khoản 2 Cấp Độ (Guest Rescue Station) cho phép gửi phiếu cứu trợ khẩn cấp không cần đăng nhập.
- **C.** Nhờ bạn cùng lớp đăng nhập hộ rồi đổi tên.
- **D.** Tự viết mã hack vào CSDL trường.

> **💡 Căn cứ kỹ thuật & Giải thích:** Trạm cứu hộ Guest Rescue Station giải quyết bài toán thực tế: Người dùng mất mật khẩu email trường nên không thể nhận link SSPR, do đó cần quy trình định danh ngoại tuyến (Out-of-band).

#### Câu 50. Tại 'Trạm cứu hộ tài khoản' (`guest_rescue.php`), người dùng không có tài khoản cần cung cấp những thông tin và bằng chứng xác minh nào?

- **A.** Số dư tài khoản ngân hàng và thẻ tín dụng.
- **B.** Mã định danh (Mã SV/GV), Họ tên, Số điện thoại liên hệ, Mô tả sự cố và Hình ảnh giấy tờ tùy thân minh chứng (Thẻ SV, Thẻ CCCD).
- **C.** Bằng tốt nghiệp cấp 3 bản gốc.
- **D.** Mật khẩu Facebook cá nhân.

> **💡 Căn cứ kỹ thuật & Giải thích:** Thông tin định danh kết hợp ảnh thẻ SV/CCCD cho phép đội ngũ kỹ thuật đối soát chéo với hồ sơ lưu trữ chính thức của trường trước khi can thiệp tài khoản.

#### Câu 51. Quy trình xử lý phiếu cứu hộ tại 'Trạm cứu hộ tài khoản' được thiết kế theo mô hình 2 cấp độ (Two-Person Rule / Separation of Duties) như thế nào?

- **A.** Bất kỳ ai cũng có thể tự bấm nút reset mật khẩu của người khác.
- **B.** Cấp 1: KTV trực rà soát hồ sơ, đối chiếu ảnh CCCD & thẩm định hợp lệ; Cấp 2: Chỉ Quản Trị Viên (Admin) có thẩm quyền bấm duyệt cấp lại mật khẩu.
- **C.** KTV cấp 1 đổi mật khẩu xong rồi KTV cấp 2 đổi lại lần nữa.
- **D.** Hệ thống tự động duyệt sau 10 giây không cần con người.

> **💡 Căn cứ kỹ thuật & Giải thích:** Phân tách quyền hạn (Separation of Duties): KTV chỉ có thẩm quyền thẩm định hồ sơ (Verify), còn quyền can thiệp dữ liệu nhạy cảm (Reset Credential) thuộc độc quyền của Quản trị viên (Admin).

#### Câu 52. Tại sao Kỹ thuật viên (KTV / Agent) không được phép trực tiếp bấm nút Đặt lại mật khẩu (Reset Password) cho người dùng trong Trạm cứu hộ?

- **A.** Vì KTV không biết cách bấm chuột.
- **B.** Để ngăn chặn nguy cơ KTV lạm quyền, thông đồng chiếm đoạt tài khoản giảng viên hoặc sinh viên trường; mọi hành động cấp phát lại mật khẩu phải do Admin phê duyệt.
- **C.** Vì CSDL không hỗ trợ tài khoản KTV sửa bảng `users`.
- **D.** Vì KTV chỉ làm việc vào ban đêm.

> **💡 Căn cứ kỹ thuật & Giải thích:** Đây là nguyên tắc vàng trong Quản trị An toàn thông tin (Least Privilege & Dual Control): Giảm thiểu nguy cơ tấn công nội gián (Insider Threat).

#### Câu 53. Khi Quản trị viên (Admin) phê duyệt phiếu cứu trợ của sinh viên, mật khẩu tài khoản sẽ được hệ thống đặt về giá trị nào?

- **A.** Chuỗi ngẫu nhiên 50 ký tự.
- **B.** Mật khẩu mặc định quy chuẩn `123456` (được băm Bcrypt) và kích hoạt lại cờ bắt buộc đổi mật khẩu `is_first_login = 1`.
- **C.** Để trống không cần mật khẩu.
- **D.** Trùng với số điện thoại của Admin.

> **💡 Căn cứ kỹ thuật & Giải thích:** Đặt lại về `123456` kèm cờ `is_first_login = 1` bảo đảm sinh viên sau khi nhận lại quyền truy cập bắt buộc phải thiết lập mật khẩu riêng tư mới.

#### Câu 54. Tập tin đính kèm minh chứng (ảnh Thẻ SV, CCCD) do người dùng gửi lên tại Trạm cứu trợ được lưu trữ an toàn ở thư mục nào?

- **A.** `storage/attachments/` hoặc `uploads/rescues/` với tên tệp được băm ngẫu nhiên bằng `uniqid()` để chống ghi đè và tấn công Path Traversal.
- **B.** Lưu trên màn hình Desktop của Admin.
- **C.** Lưu vào thùng rác Recycle Bin.
- **D.** Lưu trên Google Drive công khai.

> **💡 Căn cứ kỹ thuật & Giải thích:** Đổi tên tệp đính kèm bằng hàm băm ngẫu nhiên `uniqid('rescue_', true)` ngăn chặn tấn công ghi đè tệp nguy hiểm và bảo vệ quyền riêng tư hồ sơ cá nhân.

#### Câu 55. Đoạn mã PHP nào kiểm tra định dạng tập tin ảnh minh chứng tải lên tại Trạm cứu hộ để ngăn chặn tin tặc tải lên mã độc PHP webshell?

- **A.** `if (strlen($_FILES['file']['name']) > 5)`
- **B.** `$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)); if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) { die('File không hợp lệ'); }`
- **C.** `if ($_FILES['file']['size'] == 0)`
- **D.** `if ($_POST['type'] == 'image')`

> **💡 Căn cứ kỹ thuật & Giải thích:** White-listing phần mở rộng tập tin (chỉ cho phép các đuôi tệp an toàn như `jpg`, `png`, `pdf`) loại bỏ triệt để nguy cơ hacker tải tệp `.php`, `.phtml` lên máy chủ để thực thi lệnh trái phép.

#### Câu 56. Sau khi Admin duyệt cấp lại mật khẩu cho sinh viên trong Trạm cứu trợ, trạng thái của phiếu cứu trợ (`tickets.status`) được chuyển sang giá trị nào?

- **A.** `'new'`
- **B.** `'in_progress'`
- **C.** `'resolved'` (Đã giải quyết) kèm thông báo gửi đến số điện thoại/email dự phòng của người dùng.
- **D.** `'waiting_requester'`

> **💡 Căn cứ kỹ thuật & Giải thích:** Chuyển trạng thái sang `resolved` hoàn tất quy trình cứu hộ và lưu vết vào hệ thống để người dùng kiểm tra kết quả.

#### Câu 57. Mã theo dõi phiếu cứu trợ (Ticket Code) được cấp cho khách vãng lai tại Trạm cứu hộ có định dạng như thế nào?

- **A.** Một số ngẫu nhiên 1, 2, 3.
- **B.** Mã định dạng chuẩn có tiền tố nhận diện, ví dụ `#HD-RESCUE-YYYY-XXXX` hoặc `#HD-2026-XXXX`.
- **C.** Tên của sinh viên viết hoa không dấu.
- **D.** Địa chỉ IP của người dùng.

> **💡 Căn cứ kỹ thuật & Giải thích:** Mã phiếu chuẩn hóa giúp khách vãng lai dễ dàng tra cứu tiến độ xử lý yêu cầu cứu trợ của mình trực tiếp trên giao diện tra cứu mở.

#### Câu 58. Nếu một người dùng nhập sai mã xác thực hoặc mã token đặt lại mật khẩu đã quá hạn 15 phút, hệ thống sẽ xử lý ra sao?

- **A.** Cho phép đặt lại mật khẩu bình thường vì trễ một chút không sao.
- **B.** Từ chối thực thi, hiển thị thông báo lỗi 'Đường dẫn đặt lại mật khẩu đã hết hạn hoặc không tồn tại' và gợi ý người dùng gửi lại yêu cầu mới.
- **C.** Xóa vĩnh viễn tài khoản của người dùng.
- **D.** Đổi trình duyệt của người dùng sang Firefox.

> **💡 Căn cứ kỹ thuật & Giải thích:** Nghiêm ngặt từ chối token quá hạn bảo đảm tuân thủ đúng chính sách kiểm soát truy cập và phòng vệ thời gian thực.

#### Câu 59. Trong file `storage/mailbox.json`, mỗi bức thư điện tử ảo được Thục Linh lưu trữ dưới cấu trúc mảng JSON gồm các trường dữ liệu nào?

- **A.** `to`, `subject`, `body`, `sent_at`, `token_link`
- **B.** `ip`, `cookie`, `mac_address`
- **C.** `sender_id`, `receiver_password`
- **D.** `file_content`, `line_count`

> **💡 Căn cứ kỹ thuật & Giải thích:** Lưu đầy đủ người nhận (`to`), tiêu đề (`subject`), nội dung HTML (`body`), thời điểm gửi (`sent_at`) và link reset trực tiếp giúp giảng viên kiểm thử dễ dàng nhấp chuột trực tiếp để test.

#### Câu 60. Tại sao giải pháp 'Trạm Cứu Hộ Tài Khoản 2 Bước' được Hội đồng Giảng viên đánh giá là điểm sáng thực tiễn vượt trội trong đề tài Helpdesk CNTT Nhóm 1?

- **A.** Vì tính năng này sử dụng trí tuệ nhân tạo AI tạo video giả lập.
- **B.** Vì giải quyết trúng 'nỗi đau' thực tế tại các trường đại học: Sinh viên quên mật khẩu nhưng email trường cũng bị khóa, đồng thời bảo đảm an ninh tuyệt đối nhờ cơ chế KTV kiểm tra minh chứng, Admin ra quyết định.
- **C.** Vì viết bằng ngôn ngữ lập trình Python thay vì PHP.
- **D.** Vì không cần kết nối tới bất kỳ CSDL nào.

> **💡 Căn cứ kỹ thuật & Giải thích:** Mô hình đáp ứng hoàn hảo bài toán thực tiễn của trường đại học, vừa thuận tiện cho sinh viên gặp nạn, vừa bảo đảm chặt chẽ quy trình kiểm soát nội bộ chuẩn ITIL v4.

## 4. KIẾN TRÚC REPOSITORY PATTERN, PHƯƠNG THỨC POST AN TOÀN & FLASH MESSAGE

#### Câu 61. Mục đích cốt lõi của việc áp dụng mô hình 'Repository Pattern' trong file `TranLinh/UserRepository.php` là gì?

- **A.** Để viết các câu lệnh SQL trực tiếp vào thẻ HTML `<div>` và `<form>`.
- **B.** Tách biệt hoàn toàn tầng logic truy vấn dữ liệu (Data Access Layer) khỏi tầng giao diện (View) và tầng điều khiển (Controller), tăng khả năng tái sử dụng và kiểm thử tự động.
- **C.** Để hệ thống chạy nhanh hơn 1000 lần trên trình duyệt Chrome.
- **D.** Bắt buộc người dùng phải cài đặt thêm cơ sở dữ liệu MongoDB.

> **💡 Căn cứ kỹ thuật & Giải thích:** Repository Pattern tập trung mọi thao tác với bảng `users` vào một lớp duy nhất, giúp mã nguồn tầng giao diện sạch sẽ, dễ bảo trì và dễ dàng viết script Unit Test độc lập.

#### Câu 62. Trong hàm `UserRepository::findActiveUsers($keyword, $page, $perPage)`, tham số `$page` và `$perPage` được sử dụng để làm gì?

- **A.** Chỉ định số lượng tài khoản cần xóa khỏi hệ thống.
- **B.** Tính toán vị trí bắt đầu `$offset = ($page - 1) * $perPage` phục vụ phân trang an toàn trong câu lệnh SQL `LIMIT :limit OFFSET :offset`.
- **C.** Cấu hình số lần nhập sai mật khẩu tối đa.
- **D.** Đổi màu nền của bảng danh sách người dùng.

> **💡 Căn cứ kỹ thuật & Giải thích:** Công thức `$offset = ($page - 1) * $perPage` kết hợp phân trang bằng LIMIT/OFFSET ngăn máy chủ bị quá tải khi bảng người dùng có hàng chục nghìn sinh viên.

#### Câu 63. Trong hàm `UserRepository::countActiveUsers($keyword)`, câu truy vấn SQL nào được sử dụng để đếm tổng số bản ghi thỏa mãn điều kiện tìm kiếm?

- **A.** `SELECT * FROM users` rồi dùng `count()` trong PHP.
- **B.** `SELECT COUNT(*) FROM users u WHERE u.is_active = 1 AND (u.name LIKE :kw OR u.user_code LIKE :kw OR u.email LIKE :kw)`
- **C.** `SELECT MAX(id) FROM users`
- **D.** `DELETE FROM users WHERE name LIKE :kw`

> **💡 Căn cứ kỹ thuật & Giải thích:** `SELECT COUNT(*)` do chính engine CSDL MySQL tính toán và trả về một con số nguyên duy nhất, tối ưu hiệu năng gấp hàng trăm lần so với việc tải toàn bộ bản ghi về PHP rồi mới đếm.

#### Câu 64. Tại sao trong `UserRepository::findById($id)`, tham số `$id` bắt buộc phải được ép kiểu nguyên `(int)$id` hoặc gắn bằng `bindValue(':id', $id, PDO::PARAM_INT)`?

- **A.** Để chuyển chữ số thành chữ viết.
- **B.** Ngăn chặn tuyệt đối lỗ hổng SQL Injection khi kẻ xấu cố tình truyền chuỗi mã độc qua tham số GET/POST trên URL.
- **C.** Để PHP không báo lỗi font chữ tiếng Việt.
- **D.** Để CSDL tự động tăng giá trị của ID lên 1.

> **💡 Căn cứ kỹ thuật & Giải thích:** Ép kiểu dữ liệu nghiêm ngặt kết hợp tham số hóa câu truy vấn (Parameterized Query) là nguyên lý phòng thủ then chốt tiêu diệt hoàn toàn nguy cơ SQL Injection.

#### Câu 65. Hàm `UserRepository::create($data)` thực hiện băm mật khẩu như thế nào trước khi ghi bản ghi sinh viên mới vào CSDL?

- **A.** Lưu mật khẩu dạng văn bản thuần để Admin dễ nhớ.
- **B.** Kiểm tra nếu mật khẩu chưa băm thì tự động gọi `password_hash($data['password'] ?? '123456', PASSWORD_BCRYPT)` trước khi truyền vào câu lệnh INSERT.
- **C.** Mã hóa bằng thuật toán đối xứng DES.
- **D.** Chuyển mật khẩu thành mã Morse.

> **💡 Căn cứ kỹ thuật & Giải thích:** Tự động băm Bcrypt ngay trong Repository bảo đảm tính toàn vẹn: Không một mật khẩu nào có thể lọt vào CSDL dưới dạng văn bản thô (Plain-text).

#### Câu 66. Mô hình kiến trúc 'POST-Redirect-GET' (PRG Pattern) được Thục Linh triển khai trong `user_action.php` nhằm mục đích gì?

- **A.** Làm chậm quá trình gửi form để người dùng kịp đọc quảng cáo.
- **B.** Ngăn chặn hiện tượng gửi lại biểu mẫu (Duplicate Form Submission) khi người dùng bấm phím F5 hoặc nút Reload lại trang web trên trình duyệt.
- **C.** Bắt buộc người dùng phải gõ lại mật khẩu sau mỗi thao tác.
- **D.** Tự động tắt tab trình duyệt khi hoàn thành tác vụ.

> **💡 Căn cứ kỹ thuật & Giải thích:** Khi xử lý xong dữ liệu POST, server gửi mã chuyển hướng HTTP 302/303 đưa trình duyệt sang yêu cầu GET; nếu người dùng nhấn F5, trình duyệt chỉ tải lại trang GET an toàn mà không resubmit form.

#### Câu 67. Đoạn mã PHP nào sau đây thể hiện chuẩn mực mô hình POST-Redirect-GET sau khi thêm người dùng thành công?

- **A.** `include 'users.php';`
- **B.** `set_flash('success', 'Thêm sinh viên thành công!'); header('Location: users.php'); exit;`
- **C.** `echo 'Thành công!'; while(true){}`
- **D.** `die('Đã thêm');`

> **💡 Căn cứ kỹ thuật & Giải thích:** Thiết lập Flash Message, gọi lệnh `header('Location: ...')` và lập tức dừng thực thi bằng `exit;` là cấu trúc kinh điển của mô hình PRG.

#### Câu 68. Tại sao lệnh `exit;` hoặc `die();` bắt buộc phải đặt ngay sau câu lệnh `header('Location: ...');` trong PHP?

- **A.** Để trình duyệt web không bị treo đơ.
- **B.** Ngăn chặn máy chủ tiếp tục thực thi các dòng mã xử lý bên dưới của script, tránh rò rỉ dữ liệu hoặc thực thi sai lệch trước khi trình duyệt chuyển hướng.
- **C.** Để PHP tự động khởi động lại dịch vụ Apache.
- **D.** Chỉ là một thói quen viết code, không có tác dụng kỹ thuật.

> **💡 Căn cứ kỹ thuật & Giải thích:** `header('Location: ...')` chỉ gửi tiêu đề phản hồi về trình duyệt nhưng không làm dừng mã PHP; nếu thiếu `exit;`, mã phía sau vẫn tiếp tục chạy và có thể gây lỗi logic nghiêm trọng.

#### Câu 69. Trong `TranLinh/user_action.php`, thao tác Khóa/Mở khóa tài khoản (`action = toggle_lock`) bắt buộc phải nhận qua phương thức HTTP nào?

- **A.** Phương thức HTTP GET thông qua liên kết `<a href="user_action.php?action=toggle_lock&id=10">`.
- **B.** Phương thức HTTP POST thông qua thẻ `<form method="POST">` kèm mã CSRF token bảo vệ.
- **C.** Phương thức HTTP HEAD.
- **D.** Phương thức HTTP OPTIONS.

> **💡 Căn cứ kỹ thuật & Giải thích:** Các thao tác làm biến đổi trạng thái hệ thống (State-changing Operations) bắt buộc phải dùng POST để tránh bị tấn công CSRF, bẫy click tàng hình hoặc robot tìm kiếm (Web Crawler) tự kích hoạt khi quét link GET.

#### Câu 70. Nếu một lập trình viên thiếu kinh nghiệm cho phép khóa tài khoản bằng phương thức GET (`user_delete.php?id=15`), hệ thống sẽ đối mặt với nguy cơ an ninh nghiêm trọng nào?

- **A.** Trang web sẽ chạy chậm đi 50%.
- **B.** Tin tặc có thể nhúng thẻ `<img src="user_delete.php?id=15">` trên diễn đàn hoặc email; khi Admin vô tình xem ảnh, tài khoản của sinh viên sẽ lập tức bị khóa trái phép (CSRF).
- **C.** Máy chủ MySQL sẽ tự động khóa bảng `users` vĩnh viễn.
- **D.** Không có nguy cơ nào vì GET hay POST đều giống nhau.

> **💡 Căn cứ kỹ thuật & Giải thích:** Trình duyệt tự động gửi yêu cầu GET khi tải tài nguyên (ảnh, script) kèm cookie của Admin, do đó cho phép thao tác nhạy cảm qua GET sẽ mở toang cánh cửa cho các cuộc tấn công CSRF tàn khốc.

#### Câu 71. Hàm `get_flash()` trong `TranLinh/db.php` làm nhiệm vụ gì khi được gọi trong file View `users.php`?

- **A.** Đọc thông báo từ Session, trả về mảng `['type' => ..., 'message' => ...]` và ngay lập tức `unset($_SESSION['flash'])` để thông báo biến mất sau khi tải lại trang.
- **B.** Tải toàn bộ dữ liệu người dùng vào bộ nhớ RAM.
- **C.** Phát âm thanh thông báo trên loa máy tính.
- **D.** Gửi thông báo đẩy Web Push Notification.

> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế tự xóa (Unset on Read) của Flash Message bảo đảm thông báo chỉ xuất hiện đúng một lần duy nhất cho thao tác vừa hoàn thành.

#### Câu 72. Để bảo vệ giao diện Quản trị người dùng `users.php` chống lại tấn công Cross-Site Scripting (XSS), mọi biến dữ liệu hiển thị ra màn hình đều phải được bọc qua hàm nào?

- **A.** `urlencode()`
- **B.** `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- **C.** `strip_tags()`
- **D.** `addslashes()`

> **💡 Căn cứ kỹ thuật & Giải thích:** `htmlspecialchars()` chuyển đổi các ký tự nguy hiểm như `<`, `>`, `"`, `'`, `&` thành các thực thể HTML an toàn, vô hiệu hóa hoàn toàn mã kịch bản độc hại.

#### Câu 73. Khi người dùng nhấn vào nút 'Sửa hồ sơ' trên giao diện `users.php`, dữ liệu của người dùng được tải vào form thông qua cơ chế nào?

- **A.** Gửi email cho người dùng yêu cầu đọc lại thông tin.
- **B.** Hệ thống gọi `UserRepository::findById($id)` để lấy bản ghi hiện tại từ CSDL, gán vào các thuộc tính `value="..."` của ô nhập liệu trong Modal form.
- **C.** Người dùng phải tự nhập lại toàn bộ thông tin từ đầu.
- **D.** Lấy thông tin ngẫu nhiên từ bảng khác.

> **💡 Căn cứ kỹ thuật & Giải thích:** Tải trước dữ liệu hiện tại (Pre-populating Form Fields) mang lại trải nghiệm mượt mà, giúp người dùng dễ dàng chỉnh sửa mà không cần nhập lại những trường không đổi.

#### Câu 74. Trong file `TranLinh/user_detail.php`, câu lệnh truy vấn nào được dùng để hiển thị chi tiết thông tin cá nhân kèm theo tên lớp học sinh hoạt?

- **A.** `SELECT * FROM users WHERE id = :id`
- **B.** `SELECT u.*, COALESCE(c.name, 'Chưa xếp lớp / Cán bộ') AS class_name FROM users u LEFT JOIN classes c ON u.class_id = c.id WHERE u.id = :id`
- **C.** `SELECT * FROM classes WHERE id = :id`
- **D.** `DELETE FROM users WHERE id = :id`

> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng `LEFT JOIN` kết hợp hàm `COALESCE` bảo đảm những người dùng không thuộc lớp nào (như KTV, Admin, Giảng viên) vẫn hiển thị đầy đủ thông tin mà không bị thiếu bản ghi hay lỗi rỗng.

#### Câu 75. Nếu người dùng cố tình nhập giá trị tham số phân trang âm hoặc không hợp lệ (ví dụ `page=-5` hoặc `page=abc`) trên thanh địa chỉ, hàm xử lý an toàn của Thục Linh là gì?

- **A.** Để nguyên giá trị và cho ứng dụng báo lỗi SQL.
- **B.** `$page = max(1, (int)($_GET['page'] ?? 1));`
- **C.** Xóa toàn bộ CSDL người dùng.
- **D.** Tự động trừ tiền trong thẻ sinh viên.

> **💡 Căn cứ kỹ thuật & Giải thích:** Ép kiểu số nguyên `(int)` kết hợp hàm `max(1, ...)` bảo đảm `$page` luôn luôn là một số nguyên dương từ 1 trở lên, triệt tiêu mọi khả năng phá hoại hoặc gây lỗi cú pháp SQL.

#### Câu 76. Trong hàm `UserRepository::update($id, $data)`, trường hợp người quản trị không nhập mật khẩu mới thì hệ thống sẽ cập nhật ra sao?

- **A.** Mật khẩu của người dùng sẽ bị xóa thành rỗng và không thể đăng nhập.
- **B.** Hệ thống chỉ cập nhật các trường thông tin cá nhân (tên, email, khoa, lớp...) và giữ nguyên vẹn chuỗi `password_hash` hiện tại trong CSDL.
- **C.** Hệ thống tự động đổi mật khẩu thành `123456`.
- **D.** Hệ thống báo lỗi và hủy toàn bộ thao tác cập nhật.

> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc thiết kế form cập nhật thông tin: Mật khẩu là tùy chọn (Optional), chỉ tiến hành băm và cập nhật khi ô nhập mật khẩu mới có chứa dữ liệu.

#### Câu 77. Thuật toán phân trang trên giao diện `users.php` tính tổng số trang hiển thị bằng công thức toán học nào trong PHP?

- **A.** `$totalPages = floor($totalUsers / $perPage);`
- **B.** `$totalPages = ceil($totalUsers / $perPage);`
- **C.** `$totalPages = round($totalUsers / $perPage);`
- **D.** `$totalPages = $totalUsers * $perPage;`

> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `ceil()` làm tròn lên số nguyên gần nhất; ví dụ có 10 người dùng với `$perPage = 3` thì `10 / 3 = 3.33` -> làm tròn lên thành 4 trang (trang cuối có 1 người).

#### Câu 78. Thao tác nào sau đây của Thục Linh bảo đảm rằng các lớp học phần hoặc phiếu hỗ trợ trong quá khứ không bao giờ gặp lỗi 'mất cha' (Orphaned Records)?

- **A.** Luôn luôn dùng lệnh `DELETE FROM users WHERE id = :id`.
- **B.** Thực hiện 'Khóa mềm' (Soft Lock) bằng cách cập nhật `is_active = 0` và lưu lý do `lock_reason`, tuyệt đối không xóa cứng bản ghi khỏi bảng `users`.
- **C.** Đổi ID của người dùng thành một số ngẫu nhiên khác.
- **D.** Xóa toàn bộ các bảng liên quan trước khi xóa người dùng.

> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa mềm bảo tồn 100% tính toàn vẹn tham chiếu (Referential Integrity): Tất cả các phiếu hỗ trợ, phản hồi và đánh giá liên kết với sinh viên vẫn tồn tại nguyên vẹn trong hệ thống báo cáo.

#### Câu 79. Trong bảng danh sách người dùng, trạng thái kích hoạt được hiển thị trực quan bằng các huy hiệu (Badge) màu sắc nào?

- **A.** Màu đen và màu trắng.
- **B.** Màu xanh lá cây (Active - Đang hoạt động) và màu đỏ tươi (Locked - Đang bị khóa kèm nút mở khóa nhanh).
- **C.** Màu vàng và màu xám.
- **D.** Chỉ hiển thị dòng chữ số 0 và 1.

> **💡 Căn cứ kỹ thuật & Giải thích:** Giao diện trực quan với thẻ Badge màu sắc chuẩn UI/UX giúp người quản trị nhanh chóng nhận diện trạng thái các tài khoản chỉ trong 1 giây.

#### Câu 80. Tập tin kịch bản kiểm thử tự động `TranLinh/test_crud_cli.php` được thiết kế để chạy trong môi trường nào?

- **A.** Chỉ chạy được trên máy chủ đắt tiền chạy Linux.
- **B.** Chạy trực tiếp trên giao diện dòng lệnh Terminal / Command Line Interface (CLI) của PHP mà không cần bất kỳ trình duyệt web nào.
- **C.** Bắt buộc phải mở trong phần mềm Photoshop.
- **D.** Chỉ chạy được trên điện thoại thông minh.

> **💡 Căn cứ kỹ thuật & Giải thích:** CLI Unit Testing cho phép kiểm tra tính đúng đắn của toàn bộ logic nghiệp vụ (5 Test Cases) chỉ bằng một câu lệnh duy nhất trên PowerShell/Terminal.

## 5. PHÂN TÍCH CHI TIẾT 5 TEST CASES KIỂM THỬ & CÁC CÂU LỆNH TRUY VẤN SQL CỦA THỤC LINH

#### Câu 81. Trong kịch bản kiểm thử **Test Case 1 (Thêm người dùng hợp lệ - Create)** của `test_crud_cli.php`, dữ liệu mẫu sinh viên mới được nhập là gì?

- **A.** Mã SV: `224001799`, Họ tên: `Lê Hoàng Nam`, Email: `nam224@daihocthudo.edu.vn`, Lớp: `1` (TUD-D2024).
- **B.** Mã SV: `admin`, Họ tên: `Super Admin`, Email: `admin@thudo.edu.vn`.
- **C.** Mã SV: `111111111`, Họ tên: `Người Lạ`, Email: `test@gmail.com`.
- **D.** Dữ liệu để trống toàn bộ các trường.

> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 1 đưa vào thông tin sinh viên mới chuẩn xác để kiểm tra quá trình ghi bản ghi vào CSDL và băm mật khẩu tự động.

#### Câu 82. Tiêu chí kiểm định (Assertion) nào được kiểm tra trong **Test Case 1** để xác nhận mật khẩu được băm an toàn?

- **A.** Kiểm tra độ dài mật khẩu bằng 6 ký tự.
- **B.** Kiểm tra chuỗi băm có đúng 60 ký tự và gọi hàm `password_verify('123456', $newUser['password_hash'])` trả về giá trị `TRUE`.
- **C.** Kiểm tra mật khẩu có chứa chữ viết hoa hay không.
- **D.** Kiểm tra mật khẩu có được gửi vào hòm thư Gmail hay không.

> **💡 Căn cứ kỹ thuật & Giải thích:** Xác minh độ dài 60 ký tự và kiểm chứng qua `password_verify` là bằng chứng không thể chối cãi về việc mật khẩu đã được bảo vệ bằng chuẩn Bcrypt quốc tế.

#### Câu 83. Mục đích kiểm thử của **Test Case 2 (Chặn trùng lặp Mã định danh - Unique Constraint)** là gì?

- **A.** Kiểm tra tốc độ gõ phím của sinh viên.
- **B.** Kiểm tra việc hệ thống chặn người dùng cố tình nhập trùng Mã sinh viên `224001729` (đã thuộc về SV Trần Thục Linh) và ném thông báo lỗi thích hợp.
- **C.** Kiểm tra việc gửi email hàng loạt.
- **D.** Kiểm tra giao diện trên màn hình iPad.

> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 2 kiểm định tính toàn vẹn dữ liệu: Hệ thống phải bắt được lỗi trùng lặp khóa duy nhất (Duplicate entry) và xử lý ngoại lệ duyên dáng, không làm vỡ trang web.

#### Câu 84. Trong **Test Case 2**, câu lệnh kiểm tra sự tồn tại của Mã định danh hoặc Email trước khi INSERT được viết như thế nào?

- **A.** `SELECT COUNT(*) FROM users WHERE user_code = :uc OR email = :em`
- **B.** `DELETE FROM users WHERE user_code = :uc`
- **C.** `SELECT * FROM tickets WHERE requester_id = :uc`
- **D.** `DROP TABLE users`

> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn tiền kiểm tra (Pre-validation check) đếm số lượng bản ghi trùng; nếu kết quả > 0, hệ thống chủ động ném lỗi mà không cần đợi CSDL báo lỗi cấp thấp.

#### Câu 85. Trong **Test Case 3 (Tìm kiếm tương đối với toán tử LIKE %)**, từ khóa tìm kiếm (Keyword) nào được đưa vào thử nghiệm?

- **A.** `kw = 'Python'`
- **B.** `kw = 'Linh'` (để tìm kiếm sinh viên Trần Thục Linh)
- **C.** `kw = '123456'`
- **D.** `kw = 'root'`

> **💡 Căn cứ kỹ thuật & Giải thích:** Truyền từ khóa `'Linh'` nhằm xác nhận hệ thống tìm kiếm mờ đúng bản ghi sinh viên `Trần Thục Linh` (Mã SV: `224001729`) mà không bỏ sót hoặc sai lệch kết quả.

#### Câu 86. Câu lệnh SQL có tham số (Parameterized Query) nào được thực thi trong **Test Case 3** để chống tấn công SQL Injection khi tìm kiếm?

- **A.** `SELECT * FROM users WHERE name = '$kw'`
- **B.** `SELECT * FROM users WHERE (name LIKE :kw OR user_code LIKE :kw OR email LIKE :kw)` với mảng tham số `['kw' => '%' . $keyword . '%']`
- **C.** `SELECT * FROM users WHERE name == :kw`
- **D.** `SELECT * FROM users ORDER BY name LIKE :kw`

> **💡 Căn cứ kỹ thuật & Giải thích:** Gắn dấu `%` vào giá trị tham số trong mảng `execute()` thay vì ghép chuỗi trực tiếp vào câu lệnh SQL là kỹ thuật phòng chống SQL Injection kinh điển.

#### Câu 87. Mục đích kiểm thử của **Test Case 4 (Phân trang an toàn LIMIT / OFFSET)** là gì?

- **A.** Kiểm tra xem hệ thống có tự động dịch trang web sang tiếng Anh không.
- **B.** Kiểm tra tính chính xác của công thức phân trang: Với cấu hình 3 bản ghi/trang (`$perPage = 3`), Trang 1 và Trang 2 phải trả về đúng dữ liệu và không trùng lặp ID bản ghi nào giữa 2 trang.
- **C.** Kiểm tra việc in ấn danh sách người dùng ra giấy A4.
- **D.** Kiểm tra số lượng người dùng đang truy cập đồng thời.

> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 4 bảo đảm thuật toán phân trang hoạt động hoàn hảo: Trang 1 lấy bản ghi 1 đến 3 (OFFSET 0), Trang 2 lấy bản ghi 4 đến 6 (OFFSET 3), hoàn toàn độc lập.

#### Câu 88. Trong **Test Case 4**, công thức toán học nào được dùng để tính giá trị `OFFSET` khi gọi Trang 2 với `$perPage = 3`?

- **A.** `$offset = 2 * 3 = 6;`
- **B.** `$offset = (2 - 1) * 3 = 3;`
- **C.** `$offset = 2 + 3 = 5;`
- **D.** `$offset = 2 / 3 = 0;`

> **💡 Căn cứ kỹ thuật & Giải thích:** Công thức chuẩn: `(Page - 1) * PerPage`. Tại Trang 2 với 3 bản ghi mỗi trang, hệ thống sẽ bỏ qua (offset) đúng 3 bản ghi đầu tiên của Trang 1.

#### Câu 89. Trong **Test Case 5 (Khóa tài khoản bằng phương thức POST - Soft Lock)**, hành động nào được kịch bản thực hiện?

- **A.** Xóa vĩnh viễn tài khoản ID = 20 bằng lệnh `DELETE`.
- **B.** Gửi yêu cầu `toggle_lock` cho tài khoản ID = 20, kiểm tra trạng thái CSDL cập nhật `is_active = 0` và lý do `lock_reason = 'expelled'`; sau đó gọi lại để mở khóa về `is_active = 1`.
- **C.** Gửi tin nhắn đe dọa sinh viên.
- **D.** Chuyển tiền học bổng của sinh viên cho người khác.

> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 5 kiểm chứng cơ chế bật/tắt (Toggle) khóa an toàn 2 chiều, ghi nhận lý do kỷ luật học tập chính xác mà không xóa bản ghi.

#### Câu 90. Câu lệnh SQL tối ưu nào được Thục Linh dùng trong hàm `toggleLock($id)` để đảo trạng thái kích hoạt của tài khoản chỉ trong 1 lần thực thi?

- **A.** `UPDATE users SET is_active = IF(is_active = 1, 0, 1), lock_reason = IF(is_active = 1, 'expelled', NULL) WHERE id = :id`
- **B.** `DELETE FROM users WHERE id = :id`
- **C.** `SELECT * FROM users WHERE id = :id`
- **D.** `INSERT INTO users (is_active) VALUES (0)`

> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng hàm điều kiện `IF()` hoặc biểu thức `CASE WHEN` trong lệnh UPDATE giúp đảo ngược trạng thái nguyên tử (Atomic Update) mà không cần truy vấn đọc trạng thái trước đó.

#### Câu 91. Khi chạy file kiểm thử tự động trên Terminal bằng lệnh `php TranLinh/test_crud_cli.php`, kết quả in ra màn hình khi tất cả các test case đều hợp lệ là gì?

- **A.** Màn hình đen không có phản hồi.
- **B.** In danh sách chi tiết: `[PASS] Test Case 1`, `[PASS] Test Case 2`, `[PASS] Test Case 3`, `[PASS] Test Case 4`, `[PASS] Test Case 5` và tổng kết 100% ĐẠT CHUẨN.
- **C.** Báo lỗi Fatal Error màu đỏ.
- **D.** Tự động tắt cửa sổ Terminal.

> **💡 Căn cứ kỹ thuật & Giải thích:** Script kiểm định tự động in ra báo cáo rõ ràng với nhãn [PASS] màu xanh lá cho từng ca kiểm thử theo đúng yêu cầu chấm điểm thực hành của giảng viên.

#### Câu 92. Trong bảng `users`, câu truy vấn nào sau đây tính tỷ lệ phần trăm tài khoản sinh viên đang hoạt động bình thường (`is_active = 1`)?

- **A.** `SELECT COUNT(*) FROM users`
- **B.** `SELECT ROUND(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS active_rate FROM users WHERE role = 'requester'`
- **C.** `SELECT is_active / 100 FROM users`
- **D.** `SELECT AVG(id) FROM users`

> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng phép tổng gộp có điều kiện `SUM(CASE WHEN ...)` kết hợp chia cho `COUNT(*)` là kỹ thuật tính tỷ lệ thống kê chuẩn xác trong báo cáo quản trị.

#### Câu 93. Để lấy danh sách 10 sinh viên có số lượng phiếu hỗ trợ gửi lên nhiều nhất để KTV chủ động liên hệ hướng dẫn, câu lệnh SQL nào được áp dụng?

- **A.** `SELECT * FROM tickets ORDER BY id DESC LIMIT 10`
- **B.** `SELECT u.id, u.user_code, u.name, COUNT(t.id) AS total_tickets FROM users u JOIN tickets t ON u.id = t.requester_id GROUP BY u.id ORDER BY total_tickets DESC LIMIT 10`
- **C.** `SELECT * FROM users WHERE role = 'requester' LIMIT 10`
- **D.** `DELETE FROM tickets WHERE requester_id IN (SELECT id FROM users)`

> **💡 Căn cứ kỹ thuật & Giải thích:** Kết hợp `JOIN` bảng `tickets`, gom nhóm theo `u.id` và sắp xếp giảm dần theo số lượng phiếu hỗ trợ `COUNT(t.id) DESC` phục vụ công tác phân tích người dùng thường xuyên gặp lỗi.

#### Câu 94. Khi thực hiện truy vấn tìm kiếm người dùng trong hệ thống CSDL lớn, chỉ mục (Index) nào trên bảng `users` giúp tăng tốc độ tìm kiếm mã sinh viên lên mức tối đa O(1)?

- **A.** Chỉ mục trên cột `phone`.
- **B.** Chỉ mục duy nhất B-Tree trên cột `user_code` (`UNIQUE KEY user_code`).
- **C.** Chỉ mục trên cột `password_hash`.
- **D.** Chỉ mục trên cột `created_at`.

> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ mục B-Tree trên khóa duy nhất `user_code` cho phép bộ tối ưu hóa truy vấn (Query Optimizer) tìm kiếm trực tiếp bản ghi với độ phức tạp cực thấp O(log N), phản hồi gần như tức thì.

#### Câu 95. Hàm PHP nào được Thục Linh sử dụng để kiểm tra định dạng email hợp lệ theo chuẩn RFC trước khi thêm vào CSDL?

- **A.** `filter_var($email, FILTER_VALIDATE_EMAIL)`
- **B.** `strlen($email) > 5`
- **C.** `is_numeric($email)`
- **D.** `strpos($email, '@')`

> **💡 Căn cứ kỹ thuật & Giải thích:** `filter_var` với cờ `FILTER_VALIDATE_EMAIL` là bộ lọc tích hợp chuẩn của PHP kiểm tra cấu trúc email đầy đủ và an toàn nhất.

#### Câu 96. Trong kịch bản xác thực đăng nhập, tại sao việc so sánh mật khẩu qua hàm `password_verify()` lại an toàn hơn toán tử so sánh chuỗi `==` về mặt phòng thủ phần cứng?

- **A.** Vì `==` chạy quá chậm.
- **B.** Vì `password_verify()` thực hiện so sánh theo thời gian cố định (Constant-time comparison), ngăn chặn tin tặc đo lường độ trễ mili-giây để dò từng ký tự (Timing Attacks).
- **C.** Vì `==` chỉ so sánh được các số nguyên.
- **D.** Vì `==` bị cấm trong phiên bản PHP 8.2.

> **💡 Căn cứ kỹ thuật & Giải thích:** Tấn công thăm dò thời gian (Timing Attacks) khai thác việc so sánh chuỗi thông thường dừng lại ngay ở ký tự sai đầu tiên; cơ chế Constant-time của Bcrypt loại bỏ hoàn toàn dấu vết này.

#### Câu 97. Khi người dùng yêu cầu đặt lại mật khẩu tại `forgot_password.php`, tại sao hệ thống luôn hiển thị thông báo 'Nếu email tồn tại, chúng tôi đã gửi hướng dẫn' kể cả khi email đó không có trong CSDL?

- **A.** Vì lập trình viên lười viết thông báo lỗi cụ thể.
- **B.** Nguyên tắc phòng chống dò quét tài khoản (Prevent User Enumeration): Không cho phép kẻ tấn công biết email đó đã được đăng ký trên hệ thống của trường hay chưa.
- **C.** Vì hệ thống bị mất kết nối CSDL tạm thời.
- **D.** Để tiết kiệm chi phí băng thông mạng.

> **💡 Căn cứ kỹ thuật & Giải thích:** Thông báo trung tính (Ambiguous Response) là khuyến cáo an ninh cốt lõi của tổ chức OWASP, ngăn chặn tin tặc sử dụng chức năng quên mật khẩu để rà quét danh sách email sinh viên/giảng viên của nhà trường.

#### Câu 98. Trong cấu trúc thư mục của dự án, thư mục chứa tập tin hộp thư ảo `mailbox.json` là gì?

- **A.** `config/`
- **B.** `storage/` (thư mục lưu trữ tệp cục bộ nội bộ của máy chủ backend)
- **C.** `public/`
- **D.** `assets/`

> **💡 Căn cứ kỹ thuật & Giải thích:** `storage/` là nơi quy hoạch lưu trữ các tệp cục bộ nhạy cảm (cache, logs, fake mailbox, uploaded proofs), tách biệt khỏi thư mục tài nguyên tĩnh công khai.

#### Câu 99. Tại sao việc cập nhật trạng thái học tập `academic_status` thành `graduated` (Đã tốt nghiệp) không dẫn tới việc khóa tài khoản ngay lập tức?

- **A.** Vì hệ thống chưa kịp cập nhật.
- **B.** Chính sách nhân văn của trường: Cho phép cựu sinh viên vẫn có thể đăng nhập trong thời gian quy định để tra cứu lại lịch sử hỗ trợ kỹ thuật hoặc xin cấp lại bảng điểm.
- **C.** Vì Admin không có quyền can thiệp vào sinh viên tốt nghiệp.
- **D.** Vì bảng `users` không hỗ trợ cập nhật sau khi tốt nghiệp.

> **💡 Căn cứ kỹ thuật & Giải thích:** Tách biệt trạng thái học tập (`academic_status`) và trạng thái kích hoạt (`is_active`) mang lại độ linh hoạt nghiệp vụ cao cho công tác quản trị đại học hiện đại.

#### Câu 100. Tổng kết lại, đóng góp chuyên môn cốt lõi của sinh viên Trần Thục Linh (224001729) trong Đề án Helpdesk CNTT Nhóm 1 là gì?

- **A.** Chỉ cài đặt phần mềm gõ tiếng Việt Unikey cho nhóm.
- **B.** Thiết kế và xây dựng trọn vẹn Phân hệ Quản trị Người dùng chuẩn Clean MVC, triển khai bảo mật Bcrypt 60 ký tự, quy trình SSPR token 15 phút, Trạm cứu hộ tài khoản 2 cấp độ bảo vệ chống tấn công Brute-Force và đạt 100% điểm kiểm thử với 5 Test Cases.
- **C.** Chỉ chụp ảnh màn hình nộp báo cáo.
- **D.** Không tham gia vào quá trình lập trình hệ thống.

> **💡 Căn cứ kỹ thuật & Giải thích:** Trần Thục Linh hoàn thành xuất sắc 19% khối lượng dự án với các sản phẩm CSDL chuẩn mực, bảo mật hàng đầu theo chuẩn OWASP và kịch bản kiểm thử tự động toàn diện.


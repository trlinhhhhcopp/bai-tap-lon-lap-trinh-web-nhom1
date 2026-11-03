# NGÂN HÀNG 100 CÂU HỎI TRẮC NGHIỆM ĐÁNH GIÁ KIẾN THỨC TOÀN DIỆN
## HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT (ITIL v4 HELPDESK) — ĐH THỦ ĐÔ HÀ NỘI
**Nhóm tác giả:** Nhóm 1 | **Học phần:** Lập trình Web & Ứng dụng (WEB101)  
**Kiến trúc:** Clean MVC Pure PHP 8 + MySQL 8 InnoDB 3NF (14 Bảng)

---

### PHẦN 1: KIẾN TRÚC TỔNG THỂ, CÔNG NGHỆ & AN TOÀN THÔNG TIN (CÂU 1 - 12)

#### Câu 1. Kiến trúc phần mềm cốt lõi được dự án lựa chọn xây dựng là gì?
- A. Microservices dựa trên Docker và Kubernetes.
- B. Clean MVC phân tầng thuần PHP 8 (Pure PHP), không phụ thuộc framework nặng, tổ chức Controller, Model, Service và View rõ ràng.
- C. Monolithic truyền thống nhúng toàn bộ logic SQL trực tiếp trong các tệp HTML.
- D. Single Page Application (SPA) hoàn toàn viết bằng React và Node.js.

#### Câu 2. Trong kiến trúc Clean MVC của dự án, tầng Model (`app/Models/`) chịu trách nhiệm chính về việc gì?
- A. Tiếp nhận yêu cầu HTTP từ trình duyệt và điều hướng trang.
- B. Định dạng giao diện CSS và hiển thị các khối giao diện cho người dùng.
- C. Đại diện cho các thực thể bảng dữ liệu, đóng gói các truy vấn CSDL bằng PDO Prepared Statements, ngăn chặn rò rỉ SQL ra ngoài View.
- D. Gửi email thông báo tự động và quản lý tiến trình nền.

#### Câu 3. Để phòng chống triệt để lỗ hổng SQL Injection (theo tiêu chuẩn OWASP Top 10), toàn bộ hệ thống áp dụng cơ chế nào?
- A. Nối chuỗi trực tiếp `$sql = "SELECT * FROM users WHERE id = " . $_GET['id']`.
- B. Chỉ dùng hàm `addslashes()` lọc chuỗi đầu vào.
- C. Sử dụng PDO Prepared Statements với tham số ràng buộc có tên (Named Parameters `:param`) cho 100% câu lệnh truy vấn.
- D. Sử dụng `strip_tags()` để xóa thẻ HTML trong câu lệnh SQL.

#### Câu 4. Cơ chế chống tấn công Cross-Site Request Forgery (CSRF) trong hệ thống hoạt động như thế nào?
- A. Kiểm tra địa chỉ IP của người dùng qua `$_SERVER['REMOTE_ADDR']`.
- B. Sinh một chuỗi ngẫu nhiên mã hóa `csrf_token` trong `$_SESSION`, nhúng vào các biểu mẫu POST dưới dạng input ẩn và kiểm tra khớp trước khi xử lý.
- C. Yêu cầu nhập mã Captcha hình ảnh ở tất cả các biểu mẫu.
- D. Chặn hoàn toàn tất cả các yêu cầu gửi bằng phương thức HTTP POST.

#### Câu 5. Thuật toán băm mật khẩu được hệ thống áp dụng để bảo vệ thông tin đăng nhập trong bảng `users` là gì?
- A. Thuật toán băm một chiều MD5 không salt (`md5($pass)`).
- B. Thuật toán SHA-1 đơn thuần (`sha1($pass)`).
- C. Thuật toán Bcrypt tiêu chuẩn công nghiệp với salt ngẫu nhiên thông qua hàm `password_hash($pass, PASSWORD_BCRYPT)`.
- D. Mã hóa đối xứng AES-256 có thể giải mã ngược lại mật khẩu gốc.

#### Câu 6. Phiên làm việc (Session) của người dùng sau khi xác thực thành công được lưu giữ tại đâu và quản lý qua lớp nào?
- A. Lưu vào LocalStorage của trình duyệt và gửi qua Header `Authorization`.
- B. Lưu trong mảng toàn cục `$_SESSION['user']` và được bao bọc, quản lý qua lớp tiện ích `App\Core\Session`.
- C. Lưu vào một tệp văn bản `.txt` công khai trong thư mục gốc.
- D. Lưu vào bảng `sessions` trong CSDL với cơ chế Memcached.

#### Câu 7. Để ngăn chặn tấn công tải lên tệp độc hại (Malicious File Upload / Webshell), dịch vụ `UploadService.php` thực hiện các biện pháp nào?
- A. Cho phép tải lên mọi định dạng nhưng giới hạn tên tệp dưới 10 ký tự.
- B. Kiểm tra danh sách trắng định dạng đuôi tệp (Whitelist), kiểm tra MIME type thực tế, giới hạn dung lượng và đổi tên tệp ngẫu nhiên kèm mã băm thời gian.
- C. Chỉ kiểm tra phần mở rộng bằng hàm `substr()` và giữ nguyên tên tệp của người dùng.
- D. Lưu trực tiếp tệp vào CSDL dưới dạng chuỗi Base64.

#### Câu 8. Để ngăn ngừa tấn công CSV Injection khi xuất danh sách phiếu hoặc điểm số ra file CSV, hệ thống áp dụng quy tắc gì?
- A. Xóa bỏ toàn bộ số và dấu chấm trong file CSV.
- B. Thêm dấu nháy đơn `'` phía trước các ô dữ liệu bắt đầu bằng các ký tự công thức nguy hiểm như `=`, `+`, `-`, `@`.
- C. Chỉ xuất file dưới định dạng hình ảnh PNG.
- D. Mã hóa toàn bộ nội dung file CSV bằng mật mã Base64.

#### Câu 9. Cơ chế định tuyến (Routing) trong hệ thống được xử lý như thế nào?
- A. Cấu hình tệp `web.xml` phức tạp của máy chủ Java.
- B. Sử dụng tệp điều hướng trung tâm `index.php` kết hợp tham số truy vấn `action` (Query Parameter) và các bộ định tuyến phân hệ (`student/`, `lecturer/`, `admin/`, `tickets/`).
- C. Bắt buộc phải cài đặt mô-đun `mod_rewrite` của Apache để ánh xạ URL ảo.
- D. Gọi trực tiếp các tệp trong thư mục `vendor/`.

#### Câu 10. Phiên bản PHP tối thiểu để hệ thống hoạt động ổn định và tận dụng các tính năng mới (Named Arguments, Match Expression, Constructor Promotion) là gì?
- A. PHP 5.6.
- B. PHP 7.2.
- C. PHP 8.0 trở lên (khuyến nghị chạy trên PHP 8.2).
- D. PHP 5.3.

#### Câu 11. Bảng mã và tập ký tự được thiết lập thống nhất từ MySQL đến kết nối PHP PDO để bảo đảm tiếng Việt không bị lỗi font là gì?
- A. `latin1_swedish_ci`.
- B. `ascii_general_ci`.
- C. `utf8mb4` với đối chiếu `utf8mb4_unicode_ci`.
- D. `cp1258_vietnamese_ci`.

#### Câu 12. Script nạp CSDL tự động `dong_bo_database.bat` hoạt động theo cơ chế nào?
- A. Bắt buộc người dùng phải cài đặt Python và Composer.
- B. Tự động dò tìm bộ thông dịch `php.exe` và kết nối PDO để nạp `database_full_nhom1.sql`, nếu không thấy sẽ thử `mysql.exe` với mã hóa UTF-8 sạch.
- C. Xóa sạch toàn bộ hệ điều hành Windows trước khi nạp dữ liệu.
- D. Chỉ mở trang web phpMyAdmin và bắt người dùng tự import thủ công.

---

### PHẦN 2: THIẾT KẾ CƠ SỞ DỮ LIỆU 14 BẢNG & CHUẨN HÓA 3NF (CÂU 13 - 27)

#### Câu 13. Tổng số lượng bảng quan hệ trong CSDL `helpdesk_thudo` sau khi tối ưu hóa chuẩn 3NF là bao nhiêu bảng?
- A. 10 bảng.
- B. 12 bảng.
- C. 14 bảng.
- D. 18 bảng.

#### Câu 14. Bảng nào trước đây từng tồn tại nhưng đã được phân tích và loại bỏ hoàn toàn khỏi CSDL vì tính chất dư thừa dữ liệu?
- A. Bảng `ticket_replies`.
- B. Bảng `holidays`.
- C. Bảng `class_students`.
- D. Bảng `password_resets`.

#### Câu 15. Lý do kỹ thuật chính xác khiến bảng `class_students` bị loại bỏ là gì?
- A. Do lớp học không cần quản lý sinh viên.
- B. Do bảng `users` đã có sẵn cột khóa ngoại trực tiếp `class_id` tham chiếu đến `classes(id)` (1 sinh viên chính quy thuộc 1 lớp cố định), việc dùng thêm bảng trung gian là dư thừa và làm chậm truy vấn.
- C. Do CSDL MySQL không hỗ trợ bảng có tên chứa dấu gạch dưới.
- D. Do giảng viên yêu cầu sinh viên phải học tự do không theo lớp.

#### Câu 16. Trong bảng `tickets`, các khóa ngoại chính liên kết đến các bảng nào?
- A. Chỉ liên kết duy nhất tới bảng `users`.
- B. `requester_id -> users(id)`, `assigned_to -> users(id)`, `category_id -> categories(id)`, `priority_id -> priorities(id)`, `class_id -> classes(id)`, `course_id -> courses(id)`.
- C. Chỉ liên kết tới bảng `classes` và `courses`.
- D. Không có khóa ngoại nào, toàn bộ lưu dưới dạng chuỗi tự do.

#### Câu 17. Mối quan hệ giữa bảng `tickets` và bảng `ticket_ratings` được thiết lập theo tỷ lệ nào?
- A. Quan hệ Nhiều &ndash; Nhiều (N-N).
- B. Quan hệ Một &ndash; Nhiều (1-N), một phiếu có thể đánh giá hàng chục lần.
- C. Quan hệ Một &ndash; Một (1-1) tuyệt đối, thông qua ràng buộc `UNIQUE KEY uq_ticket_rating (ticket_id)`.
- D. Quan hệ tự thân (Self-referencing).

#### Câu 18. Mã token đặt lại mật khẩu trong bảng `password_resets` được lưu trữ dưới dạng nào trong CSDL?
- A. Văn bản thuần rõ ràng không mã hóa (Plain text).
- B. Chuỗi băm SHA-256 có độ dài 64 ký tự hex (`token_hash`) nhằm ngăn ngừa lộ token khi DB bị rò rỉ.
- C. Chuỗi Base64 có thể dịch ngược lại.
- D. Mã PIN 4 chữ số.

#### Câu 19. Bảng nào trong hệ thống đóng vai trò cung cấp dữ liệu các ngày nghỉ lễ toàn trường để loại trừ khi tính toán thời hạn SLA?
- A. Bảng `classes`.
- B. Bảng `categories`.
- C. Bảng `holidays`.
- D. Bảng `course_lessons`.

#### Câu 20. Cột điểm số `score` của sinh viên trong bảng `student_assignment_submissions` có kiểu dữ liệu là gì?
- A. `VARCHAR(10)`.
- B. `INT(11)`.
- C. `DECIMAL(4,2)` cho phép lưu điểm số từ 0.00 đến 10.00 chính xác đến 2 chữ số thập phân.
- D. `BOOLEAN`.

#### Câu 21. Khi một bản ghi trong bảng `classes` bị xóa, hành vi toàn vẹn khóa ngoại đối với cột `class_id` trong bảng `users` được định nghĩa là gì?
- A. `ON DELETE CASCADE` (xóa luôn tài khoản người dùng).
- B. `ON DELETE RESTRICT` (báo lỗi không cho xóa lớp).
- C. `ON DELETE SET NULL` (giữ lại tài khoản người dùng và gán lớp về rỗng).
- D. `ON DELETE NO ACTION`.

#### Câu 22. Cột `is_internal` kiểu `TINYINT(1)` trong bảng `ticket_replies` mang ý nghĩa nghiệp vụ gì?
- A. Đánh dấu tin nhắn do robot tự động gửi.
- B. Đánh dấu tin nhắn ghi chú kỹ thuật nội bộ chỉ Kỹ thuật viên (Agent) và Quản trị viên (Admin) thấy, Sinh viên không nhìn thấy.
- C. Đánh dấu tin nhắn đã được gửi qua mạng nội bộ LAN.
- D. Đánh dấu tin nhắn bị xóa tạm thời.

#### Câu 23. Các bảng trong CSDL `helpdesk_thudo` tuân thủ dạng chuẩn mấy (Normal Form)?
- A. Chỉ đạt dạng chuẩn 1 (1NF).
- B. Đạt dạng chuẩn 2 (2NF).
- C. Đạt chuẩn hóa dạng 3 (3NF) &ndash; không có phụ thuộc bắc cầu, mỗi thuộc tính không khóa đều phụ thuộc trực tiếp vào khóa chính.
- D. Chưa chuẩn hóa (Unnormalized).

#### Câu 24. Bảng `course_assignments` thể hiện quy tắc nghiệp vụ cốt lõi nào trong phân công giảng dạy?
- A. Một lớp học phần có thể có nhiều giảng viên cùng tranh chấp quyền nhập điểm.
- B. Mỗi lớp học phần (1 môn của 1 lớp trong 1 kỳ học) chỉ có duy nhất 1 Giảng viên phụ trách thông qua cặp ràng buộc logic.
- C. Sinh viên tự do phân công giảng viên dạy mình.
- D. Giảng viên không cần liên kết với lớp học nào.

#### Câu 25. Bảng `notifications` lưu trữ những loại phân loại thông báo (`type`) nào?
- A. Chỉ lưu thông báo quảng cáo.
- B. Các loại: `ticket` (sự cố), `deadline` (hạn bài tập), `rescue` (cứu hộ tài khoản), `broadcast` (sự cố diện rộng).
- C. Chỉ lưu thông báo nộp học phí.
- D. Chỉ lưu thông báo sinh nhật.

#### Câu 26. Cột `parent_ticket_id` (trỏ ngược về `tickets.id`) trong bảng `tickets` phục vụ mục đích gì?
- A. Dùng để xóa phiếu tự động.
- B. Dùng để gom nhóm các phiếu sự cố con vào một phiếu sự cố cha khi xảy ra sự cố kỹ thuật diện rộng (Major Incident).
- C. Dùng để liên kết ticket với tài khoản ngân hàng.
- D. Không có ý nghĩa gì.

#### Câu 27. Cờ `is_guest_ticket` kiểu `TINYINT(1)` trong bảng `tickets` dùng để phân biệt điều gì?
- A. Phân biệt phiếu của khách tham quan trường.
- B. Đánh dấu phiếu gửi từ Trạm Cứu Hộ Tài Khoản Khách (dành cho người mất quyền đăng nhập, tra cứu bằng mã đơn).
- C. Phân biệt phiếu của sinh viên nước ngoài.
- D. Đánh dấu phiếu gửi từ thiết bị di động.

---

### PHẦN 3: PHÂN QUYỀN RBAC & VAI TRÒ NGƯỜI DÙNG (CÂU 28 - 40)

#### Câu 28. Hệ thống Helpdesk phân chia người dùng thành bao nhiêu vai trò chính (Roles) trong ma trận kiểm soát truy cập RBAC?
- A. 2 vai trò: Khách và Thành viên.
- B. 3 vai trò: Sinh viên, Giáo viên, Giám đốc.
- C. 4 vai trò: `student` (hoặc `requester`), `agent` (KTV), `lecturer` (Giảng viên), `admin` (Quản trị viên).
- D. 6 vai trò khác nhau.

#### Câu 29. Vai trò nào trong hệ thống có thẩm quyền tạo bài học mới, đăng tải tài liệu slide và thiết lập hạn nộp bài tập (`deadline_at`)?
- A. Kỹ thuật viên (`agent`).
- B. Sinh viên (`student`).
- C. Giảng viên phụ trách lớp học phần (`lecturer`).
- D. Trưởng phòng tài vụ.

#### Câu 30. Vai trò nào chịu trách nhiệm xác thực kỹ thuật khách quan trên hệ thống log (`it_verified = 1`) khi sinh viên gặp lỗi mạng/LMS không nộp được bài?
- A. Giảng viên bộ môn.
- B. Kỹ thuật viên CNTT (`agent`).
- C. Lớp trưởng lớp sinh hoạt.
- D. Ban Giám hiệu nhà trường.

#### Câu 31. Khi sự cố kỹ thuật được KTV xác nhận hợp lệ, ai là người duy nhất nắm thẩm quyền học thuật để bấm "Phê duyệt gia hạn hạn nộp bài"?
- A. Quản trị viên hệ thống.
- B. Kỹ thuật viên xử lý ticket.
- C. Giảng viên phụ trách trực tiếp lớp học phần đó (`lecturer`).
- D. Sinh viên tự gia hạn cho mình.

#### Câu 32. Trong quy trình Trạm Cứu Hộ tài khoản khách, vai trò nào có thẩm quyền thực hiện Bước 2: "Phê duyệt mở khóa tài khoản"?
- A. Kỹ thuật viên.
- B. Quản trị viên cấp cao (`admin`).
- C. Giảng viên chủ nhiệm.
- D. Bất kỳ người dùng nào truy cập vào trang.

#### Câu 33. Cột trạng thái học tập `academic_status` của sinh viên trong bảng `users` có thể nhận những giá trị nào?
- A. `online`, `offline`, `away`.
- B. `studying` (đang học), `suspended` (đình chỉ tạm thời), `expelled` (buộc thôi học do kỷ luật), `graduated` (đã tốt nghiệp).
- C. `vip`, `normal`, `gold`.
- D. `pass`, `fail`.

#### Câu 34. Điều gì sẽ xảy ra nếu một sinh viên có trạng thái `academic_status = 'expelled'` cố gắng đăng nhập vào hệ thống?
- A. Đăng nhập bình thường nhưng không được gửi ticket.
- B. Hệ thống lập tức từ chối xác thực, thông báo tài khoản bị kỷ luật buộc thôi học và chặn hoàn toàn tính năng khôi phục mật khẩu SSPR.
- C. Hệ thống tự động chuyển tài khoản sang vai trò Giảng viên.
- D. Đăng nhập được nhưng bị ẩn ảnh đại diện.

#### Câu 35. Trường `is_on_duty` (trạng thái ca trực) của Kỹ thuật viên trong bảng `users` có tác dụng gì đối với bộ máy điều phối tự động?
- A. Để chấm công nhận lương cuối tháng.
- B. Chỉ những KTV đang bật trạng thái trực (`is_on_duty = 1`) và tài khoản đang hoạt động (`is_active = 1`) mới được thuật toán tự động gán vé mới.
- C. Để hiển thị màu áo của KTV trên hệ thống.
- D. Không có tác dụng trong mã nguồn.

#### Câu 36. Nếu một Kỹ thuật viên xin nghỉ ốm đột xuất, thao tác nào giúp họ không bị thuật toán tự động điều phối thêm vé mới vào ca của mình?
- A. Đổi mật khẩu tài khoản.
- B. Tắt trạng thái trực ca (`is_on_duty = 0`) ngay trên thanh tác vụ Dashboard.
- C. Xóa tài khoản của mình khỏi CSDL.
- D. Nhờ sinh viên không gửi ticket nữa.

#### Câu 37. Theo nguyên tắc bảo mật thông tin cá nhân (Privacy Isolation), Sinh viên A có xem được danh sách phiếu hỗ trợ của Sinh viên B không?
- A. Xem được nếu biết mã sinh viên của bạn B.
- B. Xem được toàn bộ vì hệ thống mở công khai.
- C. Tuyệt đối không; câu truy vấn SQL của Sinh viên luôn bị ràng buộc điều kiện cứng `WHERE requester_id = :current_user_id`.
- D. Xem được nếu cùng lớp sinh hoạt.

#### Câu 38. Kỹ thuật viên CNTT (Agent) có quyền sửa đổi điểm số bài tập của sinh viên trong môn Lập trình Web không?
- A. Có quyền sửa nếu sinh viên nhờ hỗ trợ.
- B. Không có quyền; nghiệp vụ chấm điểm và sửa điểm hoàn toàn bị giới hạn bởi middleware kiểm tra vai trò Giảng viên (`LecturerMiddleware`).
- C. Có quyền sửa nếu được Admin cho phép bằng văn bản.
- D. Có quyền sửa trong vòng 24 giờ.

#### Câu 39. Giảng viên X có thể xem danh sách nộp bài và chấm điểm cho lớp học phần do Giảng viên Y phụ trách không?
- A. Có thể xem và chấm tự do bất kỳ môn nào.
- B. Không; Controller đối soát nghiêm ngặt `lecturer_id = current_user_id` trong bảng `course_assignments`, từ chối truy cập nếu không được phân công.
- C. Có thể xem nếu dạy cùng khoa CNTT.
- D. Chỉ được xem danh sách nhưng không được bấm chấm điểm.

#### Câu 40. Quản trị viên (Admin) và Kỹ thuật viên (Agent) có thể xem được nội dung các ghi chú nội bộ (`is_internal = 1`) trong phiếu hỗ trợ không?
- A. Chỉ Admin xem được, KTV không xem được.
- B. Cả Admin và KTV đều xem được để phối hợp nghiệp vụ, trong khi vai trò Sinh viên và Khách bị ẩn hoàn toàn trường này.
- C. Sinh viên xem được nếu đã gửi đánh giá CSAT.
- D. Không ai xem được vì tin nhắn bị mã hóa một chiều.

---

### PHẦN 4: VÒNG ĐỜI TICKET, ITIL v4 & ĐIỀU PHỐI TRIAGE (CÂU 41 - 55)

#### Câu 41. Trật tự vòng đời trạng thái chuẩn (Status Lifecycle) của một phiếu hỗ trợ trong hệ thống diễn ra như thế nào?
- A. `closed` &rarr; `resolved` &rarr; `new`.
- B. `new` (Mới tạo) &rarr; `in_progress` (Đang xử lý) &rarr; `waiting_requester` (Chờ người dùng phản hồi) &rarr; `resolved` (Đã giải quyết) &rarr; `closed` (Đóng phiếu).
- C. `pending` &rarr; `approved` &rarr; `rejected`.
- D. `draft` &rarr; `published` &rarr; `archived`.

#### Câu 42. Khi một sinh viên gửi thành công một yêu cầu hỗ trợ mới qua biểu mẫu, trạng thái khởi tạo ban đầu của phiếu luôn là gì?
- A. `in_progress`.
- B. `resolved`.
- C. `new`.
- D. `waiting_requester`.

#### Câu 43. Thuật toán phân công thông minh (Smart Auto-Dispatching) trong `TicketService.php` ưu tiên gán vé cho ai?
- A. Gán ngẫu nhiên cho bất kỳ người dùng nào trong trường.
- B. Gán cho KTV có chuyên môn trùng với danh mục sự cố (`specialty_category_id = category_id`), đang trong ca trực (`is_on_duty = 1`), và có số lượng vé đang xử lý (`active_tickets_count`) ít nhất.
- C. Luôn gán cho Admin cao nhất xử lý.
- D. Gán cho KTV có tuổi đời lớn nhất.

#### Câu 44. Trường hợp không có KTV nào đúng chuyên môn danh mục đang bật ca trực, thuật toán điều phối xử lý ra sao?
- A. Báo lỗi 500 và từ chối lưu phiếu vào CSDL.
- B. Tìm KTV bất kỳ khác đang trong ca trực có tải công việc thấp nhất để gán; nếu không có ai trực sẽ để vé ở trạng thái chờ và gửi thông báo cảnh báo cho Admin.
- C. Tự động xóa phiếu sau 5 phút.
- D. Chuyển ticket về email của Giảng viên.

#### Câu 45. Kỹ thuật viên đang phụ trách phiếu được phép thực hiện thao tác "Chuyển giao phiếu" (Reassign) khi nào?
- A. Bất cứ khi nào KTV cảm thấy lười xử lý mà không cần lý do.
- B. Khi kiểm tra nội dung sự cố và nhận thấy vấn đề thuộc chuyên môn của KTV khác (ví dụ từ Mạng sang Phần mềm đào tạo), kèm theo lý do bàn giao bắt buộc lưu vào nhật ký.
- C. Chỉ khi sinh viên yêu cầu đổi KTV.
- D. Hệ thống không cho phép chuyển giao phiếu.

#### Câu 46. Theo quy tắc nghiệp vụ ITIL, điểm số KPI hoàn thành phiếu và điểm đánh giá sao CSAT sẽ được hệ thống tính cho ai?
- A. KTV đầu tiên nhận vé nhưng đã bấm chuyển giao đi.
- B. Chia đều cho tất cả các KTV trong ca trực.
- C. Tính duy nhất cho Kỹ thuật viên phụ trách xử lý cuối cùng (người đưa trạng thái phiếu về `resolved` và nhận đánh giá CSAT của người dùng).
- D. Luôn tính KPI cho Quản trị viên.

#### Câu 47. Cơ chế "Cảnh báo đùn đẩy trách nhiệm" trong hệ thống sẽ tự động kích hoạt cảnh báo đỏ gửi tới Admin khi nào?
- A. Khi phiếu bị chuyển giao (Reassign) qua lại quá 3 lần giữa các KTV.
- B. Khi phiếu chưa được xem sau 10 phút.
- C. Khi sinh viên chấm 1 sao.
- D. Khi ticket có tệp đính kèm lớn hơn 10MB.

#### Câu 48. Trạng thái `waiting_requester` (Chờ người gửi phản hồi) được KTV sử dụng trong tình huống nào?
- A. Khi KTV đã giải quyết xong sự cố và đi về.
- B. Khi KTV cần người dùng cung cấp thêm thông tin minh chứng, ảnh chụp màn hình lỗi rõ hơn hoặc yêu cầu xác nhận kết quả thử nghiệm.
- C. Khi KTV muốn từ chối xử lý phiếu.
- D. Khi CSDL bị mất kết nối mạng.

#### Câu 49. Trong thời gian phiếu ở trạng thái `waiting_requester`, bộ đếm giờ hạn cam kết SLA sẽ hoạt động thế nào?
- A. Tiếp tục chạy bình thường để ép KTV phải tự đoán lỗi.
- B. Tạm dừng (Pause SLA clock) thời gian cam kết của KTV để bảo đảm tính công bằng, chỉ tính tiếp khi người dùng đã gửi tin nhắn phản hồi.
- C. Tự động trừ điểm KPI của sinh viên.
- D. Đặt lại thời gian SLA về 0 giờ.

#### Câu 50. Để tránh tình trạng một sinh viên bấm gửi liên tục nhiều phiếu cùng một nội dung (Spam/Duplicate tickets), hệ thống áp dụng cơ chế gì?
- A. Giới hạn mỗi sinh viên chỉ được tạo tối đa 1 phiếu trong suốt khóa học.
- B. Kiểm tra truy vấn CSDL: nếu sinh viên đang có phiếu chưa hoàn tất (`status NOT IN ('resolved', 'closed')`) trong cùng một danh mục hoặc buổi học, hệ thống sẽ chặn gửi mới và điều hướng xem lại phiếu cũ.
- C. Trừ tiền tài khoản sinh viên mỗi lần tạo phiếu.
- D. Bắt buộc sinh viên phải chờ 7 ngày mới được tạo phiếu mới.

#### Câu 51. Cấu trúc chuẩn của mã định danh phiếu hỗ trợ (`ticket_code`) trong hệ thống có dạng mẫu nào?
- A. Số tăng dần đơn thuần: `1`, `2`, `3`.
- B. Định dạng tiêu chuẩn ITIL: `HD-YYYY-DDMM-XXXXXX` (Ví dụ: `HD-2026-3110-0G04V1`).
- C. Tên sinh viên viết liền không dấu: `nguyenvana`.
- D. Số chứng minh thư nhân dân của người tạo phiếu.

#### Câu 52. Ai là người có quyền đưa phiếu từ trạng thái `resolved` (Đã giải quyết) sang `closed` (Đóng phiếu hoàn tất)?
- A. Chỉ có Bộ Giáo dục và Đào tạo.
- B. Chính người tạo phiếu (Sinh viên/Giảng viên) sau khi xác nhận đã hài lòng, hoặc hệ thống tự động đóng sau một khoảng thời gian chờ không có khiếu nại.
- C. Bất kỳ ai trên Internet.
- D. Phiếu sau khi `resolved` sẽ không bao giờ chuyển sang `closed`.

#### Câu 53. Khi một phiếu đã được KTV chuyển sang trạng thái `resolved`, người dùng sẽ nhìn thấy khối chức năng nào xuất hiện trên giao diện?
- A. Nút bấm xóa tài khoản.
- B. Khung đánh giá chất lượng dịch vụ CSAT (Chọn từ 1 đến 5 sao và nhập nhận xét góp ý).
- C. Nút bấm thanh toán tiền phí hỗ trợ.
- D. Form đăng ký môn học lại.

#### Câu 54. Nếu người dùng nhận thông báo phiếu đã `resolved` nhưng kiểm tra thực tế vẫn còn lỗi, họ có thể làm gì?
- A. Không thể làm gì khác ngoài việc tạo tài khoản mới.
- B. Gửi tin nhắn phản hồi yêu cầu mở lại phiếu (Reopen ticket), hệ thống tự động chuyển trạng thái phiếu trở lại `in_progress` để KTV tiếp tục hỗ trợ.
- C. Báo công an xử lý.
- D. Hệ thống khóa luôn quyền truy cập của người dùng.

#### Câu 55. Khối chức năng "Trung Tâm Việc Cần Làm Cấp Bách" trên Dashboard của KTV và Admin làm nhiệm vụ gì?
- A. Hiển thị danh sách sinh viên sinh nhật trong ngày.
- B. Tổng hợp và làm nổi bật các sự vụ ưu tiên cao: vé sắp hoặc đã quá hạn SLA, đơn cứu hộ tài khoản chờ xác thực, báo cáo vi phạm cần xử lý, sự cố diện rộng.
- C. Hiển thị thời tiết và tỷ giá vàng.
- D. Hiển thị lịch thi đấu bóng đá trường.

---

### PHẦN 5: THUẬT TOÁN SLA GIỜ HÀNH CHÍNH & LỊCH NGHỈ LỄ (CÂU 56 - 65)

#### Câu 56. Khung giờ làm việc hành chính chuẩn được cấu hình trong `SlaService.php` để tính thời hạn giải quyết bao gồm những khoảng thời gian nào?
- A. 24/24 giờ liên tục không ngừng nghỉ tất cả các ngày.
- B. Ca sáng: 08:00 &ndash; 12:00 (4 tiếng); Ca chiều: 13:30 &ndash; 17:30 (4 tiếng), tổng cộng 8 giờ làm việc/ngày từ Thứ Hai đến Thứ Sáu.
- C. Làm việc từ 07:00 đến 19:00 không nghỉ trưa.
- D. Chỉ làm việc vào các buổi tối từ 18:00 đến 22:00.

#### Câu 57. Khoảng thời gian nghỉ trưa hành chính không tính vào thời hạn cam kết SLA của hệ thống là bao lâu?
- A. Nghỉ từ 11:30 đến 13:00 (1.5 tiếng).
- B. Nghỉ từ 12:00 đến 13:30 (1.5 tiếng).
- C. Không có giờ nghỉ trưa.
- D. Nghỉ từ 12:00 đến 14:00 (2 tiếng).

#### Câu 58. Giả sử sinh viên tạo phiếu vào lúc 11:30 sáng Thứ Sáu với thời gian cam kết SLA là 2.0 giờ làm việc. Hạn chót giải quyết (`due_at`) chính xác sẽ là thời điểm nào?
- A. 13:30 chiều Thứ Sáu.
- B. 15:00 chiều Thứ Sáu (11:30 &ndash; 12:00 hết 0.5h, nghỉ trưa đến 13:30, cộng thêm 1.5h còn lại).
- C. 14:00 chiều Thứ Sáu.
- D. 08:00 sáng Thứ Bảy.

#### Câu 59. Giả sử sinh viên gửi phiếu lúc 16:30 chiều Thứ Sáu với thời hạn cam kết SLA là 2.0 giờ làm việc. Khi nào phiếu này sẽ đến hạn chót (`due_at`)?
- A. 18:30 tối Thứ Sáu.
- B. 09:30 sáng Thứ Bảy.
- C. 09:00 sáng Thứ Hai tuần kế tiếp (16:30 &ndash; 17:30 hết 1.0h, Thứ Bảy và Chủ Nhật nghỉ hoàn toàn, cộng 1.0h vào sáng Thứ Hai từ 08:00 đến 09:00).
- D. 12:00 trưa Thứ Hai.

#### Câu 60. Nếu người dùng gửi yêu cầu hỗ trợ vào lúc 10:00 sáng Chủ Nhật, thời điểm bắt đầu tính giờ cam kết SLA trong thuật toán sẽ là thời điểm nào?
- A. Bắt đầu tính ngay từ 10:00 sáng Chủ Nhật.
- B. Bắt đầu tính từ 08:00 sáng Thứ Hai (ngày làm việc kế tiếp).
- C. Không bao giờ được xử lý.
- D. Bắt đầu tính từ 13:30 chiều Chủ Nhật.

#### Câu 61. Hệ số nhân thời gian cam kết SLA (`sla_factor`) đối với mức độ ưu tiên Khẩn cấp (URGENT) trong bảng `priorities` là bao nhiêu?
- A. 1.0x (giữ nguyên).
- B. 0.5x (rút ngắn một nửa thời gian cam kết để ưu tiên xử lý nhanh).
- C. 2.0x (gấp đôi thời gian).
- D. 0.0x (xử lý tức thì trong 0 giây).

#### Câu 62. Hệ số nhân thời gian cam kết SLA đối với mức độ ưu tiên Thấp (LOW) là bao nhiêu?
- A. 0.5x.
- B. 1.0x.
- C. 1.5x (cho phép thời gian cam kết dài hơn các phiếu thông thường).
- D. 5.0x.

#### Câu 63. Bảng `holidays` trong CSDL can thiệp vào thuật toán tính hạn SLA như thế nào?
- A. Tự động xóa các phiếu tạo trong ngày lễ.
- B. Thuật toán `SlaService` duyệt qua từng ngày: nếu ngày đó trùng vào khoảng `start_date` đến `end_date` của bất kỳ bản ghi ngày lễ nào, toàn bộ 8 giờ làm việc của ngày đó sẽ bị bỏ qua và nhảy sang ngày làm việc tiếp theo.
- C. Ngày lễ vẫn tính giờ làm việc bình thường nhưng tính thêm phụ cấp cho KTV.
- D. Chỉ áp dụng cho ngày nghỉ Tết Nguyên Đán, các ngày lễ khác vẫn tính giờ.

#### Câu 64. Cờ `is_recurring = 1` trong bảng `holidays` được dùng để xử lý tình huống nào?
- A. Ngày lễ chỉ diễn ra một lần duy nhất trong lịch sử trường.
- B. Các kỳ nghỉ lễ định kỳ lặp lại hàng năm theo dương lịch (ví dụ: Ngày Giải phóng 30/4, Quốc tế Lao động 1/5, Quốc khánh 2/9) mà không cần nhập lại mỗi năm.
- C. Đánh dấu ngày KTV được đi du lịch.
- D. Đánh dấu các ngày thi hết môn của sinh viên.

#### Câu 65. Khi một phiếu hỗ trợ đã trôi qua thời điểm `due_at` mà trạng thái vẫn chưa là `resolved` hoặc `closed`, hệ thống sẽ phản ứng như thế nào?
- A. Tự động xóa phiếu để giấu lỗi.
- B. Hiển thị nhãn đỏ cảnh báo "Quá hạn SLA", tính toán số phút/giờ trễ, đẩy lên vị trí cảnh báo khẩn trên Dashboard và trừ điểm hiệu suất ca trực.
- C. Khóa tài khoản của người gửi phiếu.
- D. Đóng phiếu ngay lập tức.

---

### PHẦN 6: QUẢN LÝ HỌC PHẦN, BÀI TẬP & GIA HẠN DEADLINE (CÂU 66 - 77)

#### Câu 66. Quy trình phối hợp 4 bước liên thông giữa Sinh viên &ndash; Kỹ thuật viên &ndash; Giảng viên khi sinh viên gặp sự cố mạng không nộp được bài diễn ra như thế nào?
- A. Sinh viên gửi ticket &rarr; KTV tự sửa điểm &rarr; Giảng viên ký tên &rarr; Hoàn tất.
- B. Bước 1: Sinh viên gửi ticket cứu hộ nộp bài &rarr; Bước 2: KTV kiểm tra log hệ thống và bấm xác nhận lỗi kỹ thuật (`it_verified = 1`) &rarr; Bước 3: Giảng viên phụ trách xem xét và bấm phê duyệt mở lại hạn nộp bài (`deadline_at`) &rarr; Bước 4: Sinh viên nộp bài bổ sung và đánh giá sao CSAT.
- C. Giảng viên mở bài &rarr; Sinh viên nộp bài &rarr; KTV chấm điểm.
- D. Sinh viên tự ý gia hạn bài làm trên trang cá nhân.

#### Câu 67. Tại sao kiến trúc hệ thống không cho phép Kỹ thuật viên (Agent) tự ý đổi hạn nộp bài (`deadline_at`) của buổi học?
- A. Do KTV không biết thao tác máy tính.
- B. Do nguyên tắc phân chia trách nhiệm: KTV chỉ có thẩm quyền xác minh sự cố kỹ thuật khách quan, còn quyết định mở lại hạn nộp bài là quyền học thuật riêng của Giảng viên phụ trách bộ môn.
- C. Do CSDL không cho phép câu lệnh UPDATE trên bảng `course_lessons`.
- D. Do Giảng viên không cho KTV mật khẩu.

#### Câu 68. Điều kiện tiên quyết để Giảng viên nhìn thấy nút bấm "Phê duyệt gia hạn nộp bài" trong chi tiết phiếu hỗ trợ là gì?
- A. Giảng viên phải là Admin cấp cao.
- B. Phiếu phải gắn đúng môn học mà Giảng viên đang phụ trách, và Kỹ thuật viên đã hoàn thành xác thực giám định có lỗi kỹ thuật (`it_verified = 1`).
- C. Sinh viên phải nộp phí phạt nộp muộn.
- D. Phiếu phải được tạo trước 12:00 đêm.

#### Câu 69. Khi Giảng viên phê duyệt gia hạn thành công một yêu cầu cứu hộ nộp bài, trường dữ liệu nào trong CSDL được cập nhật hạn mới?
- A. `tickets.created_at`.
- B. `course_lessons.deadline_at` của buổi học tương ứng được cập nhật sang thời điểm mới trong tương lai.
- C. `users.locked_until`.
- D. `courses.code`.

#### Câu 70. Điểm số bài nộp của sinh viên trong bảng `student_assignment_submissions` được chuẩn hóa theo thang điểm nào?
- A. Thang điểm chữ: A, B, C, D, F.
- B. Thang điểm 100.
- C. Thang điểm 10 chuẩn đại học Việt Nam (từ 0.00 đến 10.00).
- D. Thang điểm 4 của Mỹ.

#### Câu 71. Trường hợp sinh viên đã nộp bài tập nhưng vẫn còn trong hạn nộp và muốn nộp tệp bài làm mới thay thế, hệ thống xử lý như thế nào?
- A. Chặn không cho nộp và báo lỗi vi phạm.
- B. Cho phép nộp đè: cập nhật đường dẫn tệp mới, thời gian nộp mới, đồng thời tự động xóa kết quả điểm số, nhận xét và xếp hạng sao cũ để Giảng viên chấm lại từ đầu.
- C. Tạo thêm một bản ghi mới với ID sinh viên mới.
- D. Tự động cộng thêm 1 điểm thưởng vì tinh thần tự giác.

#### Câu 72. Giới hạn dung lượng tệp tin tối đa khi sinh viên nộp bài tập lớn/tiểu luận trên cổng học phần là bao nhiêu?
- A. 2 MB.
- B. 5 MB.
- C. 25 MB.
- D. Không giới hạn dung lượng.

#### Câu 73. Danh sách các định dạng tệp tin cho phép (Whitelist Extensions) khi sinh viên tải lên bài tập bao gồm:
- A. `.exe`, `.bat`, `.sh`, `.cmd`.
- B. `.zip`, `.rar`, `.7z`, `.pdf`, `.doc`, `.docx`.
- C. Chỉ duy nhất tệp ảnh `.png`.
- D. `.mp3`, `.mp4`, `.avi`.

#### Câu 74. Khi thời gian hiện tại đã vượt quá hạn chót (`NOW() > deadline_at`), biểu mẫu nộp bài tập trên giao diện sinh viên sẽ hiển thị ra sao?
- A. Vẫn cho nộp bình thường không thông báo gì.
- B. Nút nộp bài bị vô hiệu hóa, hiển thị cảnh báo đỏ "Đã hết hạn nộp bài tập" kèm liên kết hướng dẫn gửi phiếu hỗ trợ nếu có lý do sự cố kỹ thuật chính đáng.
- C. Tự động xóa bài làm của sinh viên.
- D. Trừ 5 điểm trực tiếp vào CSDL.

#### Câu 75. Khi sinh viên nộp bài thành công, hệ thống thông báo chuông (Notification) sẽ được gửi đến đối tượng nào?
- A. Toàn bộ sinh viên trong trường.
- B. Giảng viên trực tiếp phụ trách lớp học phần đó để theo dõi số lượng bài nộp.
- C. Nhân viên bảo vệ trường.
- D. Chỉ gửi cho phụ huynh sinh viên.

#### Câu 76. Trong học kỳ hiện tại, mỗi lớp học phần (ví dụ lớp D24-CNTT1 môn Lập trình Web) có thể có tối đa bao nhiêu Giảng viên phụ trách?
- A. Không giới hạn số lượng giảng viên.
- B. Duy nhất 1 Giảng viên phụ trách theo đúng thiết kế nghiệp vụ của bảng `course_assignments`.
- C. Tối thiểu 3 giảng viên.
- D. Luôn luôn là 2 giảng viên cùng dạy.

#### Câu 77. Khi Giảng viên hoàn tất chấm điểm và nhận xét bài nộp, sinh viên sẽ theo dõi kết quả ở đâu?
- A. Phải lên trực tiếp văn phòng khoa để hỏi.
- B. Nhận thông báo chuông tức thời trên thanh công cụ và xem chi tiết điểm số, lời phê sư phạm, số sao đánh giá ngay tại trang chi tiết môn học.
- C. Nhận tin nhắn SMS về số điện thoại cá nhân.
- D. Điểm số chỉ được công bố sau khi tốt nghiệp.

---

### PHẦN 7: BẢO MẬT, XÁC THỰC, SSPR & TRẠM CỨU HỘ KHÁCH (CÂU 78 - 90)

#### Câu 78. Khi tài khoản sinh viên được tạo mới hoặc vừa được Admin reset về mật khẩu mặc định, giá trị mật khẩu ban đầu và cờ trạng thái là gì?
- A. Mật khẩu để trống, `is_first_login = 0`.
- B. Mật khẩu mặc định là `123456`, và cờ `is_first_login = 1`.
- C. Mật khẩu là ngày tháng năm sinh, `is_first_login = 0`.
- D. Mật khẩu là chuỗi 32 ký tự ngẫu nhiên.

#### Câu 79. Khi người dùng đăng nhập thành công nhưng tài khoản đang có cờ `is_first_login = 1`, Middleware hệ thống sẽ có hành vi gì?
- A. Cho phép người dùng truy cập mọi phân hệ bình thường.
- B. Chặn mọi đường dẫn điều hướng khác và bắt buộc chuyển hướng người dùng về trang `index.php?action=first_login` để đổi mật khẩu cá nhân mới.
- C. Tự động đăng xuất người dùng ngay lập tức.
- D. Gửi email cảnh báo tài khoản bị tấn công.

#### Câu 80. Ở màn hình đổi mật khẩu lần đầu (`first_login`), người dùng có được phép nhập lại mật khẩu `123456` không?
- A. Được phép nếu người dùng muốn giữ nguyên.
- B. Tuyệt đối không; hệ thống kiểm tra và chặn cứng nếu `$newPass === '123456'`, yêu cầu đặt mật khẩu mới an toàn hơn.
- C. Được phép nếu nhập đúng 3 lần.
- D. Hệ thống không kiểm tra điều này.

#### Câu 81. Ở cả hai luồng: Đổi mật khẩu lần đầu (`first_login`) và Đặt lại mật khẩu qua email (`reset_password`), hệ thống xử lý thế nào nếu người dùng nhập mật khẩu mới trùng khớp hoàn toàn với mật khẩu cũ hiện tại?
- A. Vẫn chấp nhận đổi bình thường vì Bcrypt sinh salt mới.
- B. Hệ thống sử dụng hàm `password_verify($newPass, $currentPasswordHash)` đối chiếu trực tiếp với CSDL; nếu phát hiện trùng mật khẩu cũ sẽ lập tức từ chối và hiển thị thông báo lỗi yêu cầu chọn mật khẩu khác.
- C. Tự động khóa tài khoản vĩnh viễn.
- D. Hệ thống sẽ cộng thêm số `1` vào sau mật khẩu của người dùng.

#### Câu 82. Trong tính năng khôi phục mật khẩu tự phục vụ qua email (SSPR), mã xác thực OTP gửi về hòm thư người dùng có định dạng như thế nào?
- A. Một đường link dài 200 ký tự hex.
- B. Mã OTP gồm 6 chữ cái in hoa ngẫu nhiên (ví dụ: `GLURJH`, `NZSYZY`), dễ đọc và thuận tiện khi sao chép hoặc gõ trên điện thoại.
- C. Mã số gồm 4 chữ số bí mật.
- D. Mã QR hình ảnh.

#### Câu 83. Thời gian hiệu lực tiêu chuẩn của mã OTP đặt lại mật khẩu trong bảng `password_resets` là bao lâu?
- A. 60 giây.
- B. Đúng 15 phút kể từ thời điểm phát hành (kiểm tra qua điều kiện `expires_at >= NOW()`).
- C. 24 giờ.
- D. Không bao giờ hết hạn.

#### Câu 84. Để bảo đảm an toàn thông tin khi CSDL bị lộ lọt (Data Breach), chuỗi OTP 6 chữ cái được lưu vào cột `token_hash` trong CSDL dưới dạng nào?
- A. Lưu dạng văn bản rõ 6 chữ cái.
- B. Băm một chiều bằng thuật toán SHA-256 (`hash('sha256', $token)`).
- C. Băm bằng MD5.
- D. Lưu dưới dạng mã Morse.

#### Câu 85. Người dùng gửi yêu cầu tại Trạm Cứu Hộ Tài Khoản Khách (`guest_ticket`) cần lưu giữ thông tin gì để tự tra cứu tiến độ phê duyệt?
- A. Bắt buộc phải nhớ cả Tên đăng nhập và Mã PIN 6 số.
- B. Chỉ cần lưu giữ duy nhất Mã đơn cứu hộ (ví dụ: `HD-2026-3110-0G04V1`), hoàn toàn không bị ép buộc phải nhớ thêm mã PIN phức tạp.
- C. Bắt buộc phải mang căn cước công dân đến trường.
- D. Phải để lại dấu vân tay.

#### Câu 86. Cơ chế kiểm duyệt an toàn 2 bước đối với Đơn cứu hộ tài khoản khách gồm những bước nào?
- A. Bước 1: Máy tính tự duyệt &rarr; Bước 2: Sinh viên tự duyệt.
- B. Bước 1: Kỹ thuật viên đối soát hồ sơ, minh chứng và xác thực thông tin hợp lệ (`rescue_verified = 1`) &rarr; Bước 2: Quản trị viên (Admin) xem xét và bấm "Phê Duyệt Mở Khóa Tài Khoản".
- C. Chỉ cần 1 bước duy nhất do KTV tự mở.
- D. Ban Giám hiệu trực tiếp gọi điện thoại phỏng vấn.

#### Câu 87. Sau khi Quản trị viên bấm phê duyệt thành công một hồ sơ cứu hộ tài khoản, trạng thái tài khoản của người dùng sẽ thay đổi như thế nào?
- A. Bị xóa khỏi hệ thống.
- B. Kích hoạt tài khoản (`is_active = 1`), đặt lại mật khẩu về `123456`, xóa cờ khóa, đặt cờ `is_first_login = 1` để người dùng bắt buộc phải đổi mật khẩu ngay khi đăng nhập.
- C. Chuyển thành tài khoản Quản trị viên.
- D. Giữ nguyên không có gì thay đổi.

#### Câu 88. Cơ chế phòng thủ chống tấn công dò quét mật khẩu Brute Force trong hệ thống được cấu hình ra sao?
- A. Khóa vĩnh viễn tài khoản sau 1 lần gõ sai.
- B. Đếm số lần đăng nhập thất bại liên tiếp (`failed_attempts`); khi đạt ngưỡng 5 lần sai sẽ tạm khóa tài khoản trong 15 phút (`locked_until`) và ghi nhận lý do `lock_reason = 'brute_force'`.
- C. Không có cơ chế phòng thủ nào.
- D. Tắt máy chủ web trong 1 tiếng.

#### Câu 89. Nếu một tài khoản bị Admin gắn nhãn khóa do vi phạm kỷ luật học đường (`lock_reason = 'violation'` hoặc `'expelled'`), người đó có thể dùng tính năng SSPR gửi OTP để tự mở khóa tài khoản không?
- A. Có thể tự mở khóa dễ dàng bất cứ lúc nào.
- B. Tuyệt đối không; dịch vụ `AuthService::requestResetToken()` kiểm tra cờ kỷ luật và lập tức ném ngoại lệ từ chối khôi phục mật khẩu.
- C. Có thể mở khóa nếu nhờ bạn bè xác nhận.
- D. Mở khóa được nếu nhập mã OTP sau 12:00 đêm.

#### Câu 90. Các tệp tin ảnh chụp màn hình minh chứng lỗi do người dùng đính kèm vào ticket được lưu trữ ở đâu trên máy chủ?
- A. Lưu lẫn lộn vào thư mục mã nguồn gốc `app/Controllers/`.
- B. Lưu trong thư mục `uploads/tickets/` với tên tệp ngẫu nhiên chống trùng lặp và ghi nhận đường dẫn vào CSDL.
- C. Tải trực tiếp lên Google Drive cá nhân của KTV.
- D. Không lưu trữ tệp tin trên máy chủ.

---

### PHẦN 8: TÍNH NĂNG NÂNG CAO, BÁO CÁO VI PHẠM & VẬN HÀNH THỰC TẾ (CÂU 91 - 100)

#### Câu 91. Khi Kỹ thuật viên phát hiện một sinh viên cố tình gửi nội dung tục tĩu, quấy rối hoặc spam đơn hỗ trợ, KTV có thể thực hiện nghiệp vụ gì?
- A. Tự ý xóa bài tập của sinh viên đó.
- B. Bấm nút "Báo cáo sinh viên vi phạm / Spam" (`is_spam = 1`), nhập lý do lập biên bản kỹ thuật để chuyển hồ sơ lên Quản trị viên xử lý kỷ luật.
- C. Nhắn tin chửi lại sinh viên.
- D. Tắt máy tính đi về.

#### Câu 92. Thẩm quyền quyết định khóa kỷ luật hoặc đình chỉ học tập đối với sinh viên bị báo cáo vi phạm thuộc về ai?
- A. Giảng viên phụ trách môn.
- B. Kỹ thuật viên trực ca.
- C. Quản trị viên (Admin) sau khi xem xét biên bản do KTV đệ trình tại Trung tâm việc khẩn.
- D. Trưởng nhóm sinh viên.

#### Câu 93. Nghiệp vụ "Đánh dấu Sự cố diện rộng" (`is_major_incident = 1`) được sử dụng trong trường hợp nào?
- A. Khi một bóng đèn trong phòng học bị cháy.
- B. Khi xảy ra sự cố kỹ thuật hạ tầng nghiêm trọng ảnh hưởng đến toàn trường (ví dụ đứt cáp quang cổng Wi-Fi chính, hệ thống LMS toàn trường bị gián đoạn diện rộng).
- C. Khi sinh viên quên mang thẻ sinh viên.
- D. Khi một chiếc máy in hết mực.

#### Câu 94. Sau khi Quản trị viên (Admin) phê duyệt thông báo sự cố diện rộng, cảnh báo sẽ được hiển thị như thế nào?
- A. Gửi thư giấy về địa chỉ nhà từng sinh viên.
- B. Xuất hiện dưới dạng banner thông báo đỏ nổi bật (Broadcast Alert) ở đầu trang của tất cả mọi người dùng đăng nhập vào hệ thống.
- C. Chỉ hiển thị trong màn hình của KTV.
- D. Chỉ xuất hiện sau 3 ngày làm việc.

#### Câu 95. Một phiếu hỗ trợ CNTT có thể được sinh viên thực hiện đánh giá mức độ hài lòng (CSAT) tối đa bao nhiêu lần?
- A. Mỗi ngày đánh giá 1 lần.
- B. Tối đa 5 lần tùy theo số lượng KTV tham gia xử lý.
- C. Đúng 1 lần duy nhất, được bảo vệ bằng ràng buộc khóa ngoại duy nhất `UNIQUE KEY (ticket_id)` trong bảng `ticket_ratings`.
- D. Không giới hạn số lần đánh giá.

#### Câu 96. Thang đo đánh giá chất lượng dịch vụ CSAT trong bảng `ticket_ratings` cho phép người dùng chấm trong khoảng giá trị nào?
- A. Từ 1 đến 100 điểm.
- B. Từ 1 sao (Rất không hài lòng) đến 5 sao (Rất hài lòng) kèm theo nhận xét chất lượng.
- C. Chỉ có 2 mức: Thích hoặc Không thích.
- D. Đánh giá bằng chữ: Tốt hoặc Kém.

#### Câu 97. Tính năng Nhập tài khoản hàng loạt (Bulk Import User) dành cho Quản trị viên hỗ trợ nhập dữ liệu từ những định dạng tệp nào?
- A. Chỉ nhận tệp văn bản `.txt`.
- B. Tệp bảng tính Excel (`.xlsx`) và tệp phân tách dấu phẩy (`.csv`).
- C. Tệp tài liệu Word (`.docx`).
- D. Tệp trình chiếu PowerPoint (`.pptx`).

#### Câu 98. Khi Admin tải lên tệp Excel danh sách 50 sinh viên khóa mới, trong đó có 2 sinh viên trùng mã sinh viên đã có trong hệ thống, dịch vụ `SpreadsheetImportService` sẽ phản ứng ra sao?
- A. Hủy bỏ toàn bộ quá trình và báo lỗi trắng trang.
- B. Tạo mới thành công 48 tài khoản hợp lệ, tự động bỏ qua 2 bản ghi trùng lặp và xuất bảng thống kê chi tiết các dòng thành công cùng các dòng bị bỏ qua.
- C. Xóa 2 tài khoản cũ trong CSDL và ghi đè tài khoản mới vào.
- D. Tạo trùng cả 50 sinh viên với mã giống nhau.

#### Câu 99. Tiện ích "Ẩn bảng / Hiện bảng dữ liệu" (Collapsible Data Tables) được tích hợp trên các giao diện quản trị mang lại giá trị thực tiễn gì?
- A. Giúp ẩn giấu dữ liệu khi có đoàn thanh tra kiểm tra.
- B. Giúp người dùng linh hoạt thu gọn các bảng danh sách dài khi không cần xem để tập trung tối đa vào biểu mẫu nhập liệu và tác vụ đang xử lý.
- C. Làm chậm tốc độ hiển thị trang web.
- D. Chỉ là một lỗi giao diện vô tình xuất hiện.

#### Câu 100. Trong môi trường phát triển cục bộ (Localhost) không có kết nối Internet để gửi email SMTP thật, hệ thống kiểm tra và xem trước mã OTP khôi phục mật khẩu thông qua cơ chế nào?
- A. Không có cách nào xem được OTP nên tính năng không thể thử nghiệm.
- B. Hệ thống tự động ghi nhật ký nội dung thư vào tệp lưu trữ nội bộ `storage/mailbox.json` và cung cấp giao diện Hòm thư nội bộ để người kiểm thử xem trước trực quan.
- C. Tự động in mã OTP ra máy in giấy văn phòng.
- D. Phải nạp tiền vào tài khoản để mua gói gửi tin nhắn viễn thông.

---

## BẢNG ĐÁP ÁN TỔNG HỢP (ANSWER KEY) 100 CÂU HỎI

| Câu | Đáp án | Câu | Đáp án | Câu | Đáp án | Câu | Đáp án | Câu | Đáp án |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | B | **21** | C | **41** | B | **61** | B | **81** | B |
| **2** | C | **22** | B | **42** | C | **62** | C | **82** | B |
| **3** | C | **23** | C | **43** | B | **63** | B | **83** | B |
| **4** | B | **24** | B | **44** | B | **64** | B | **84** | B |
| **5** | C | **25** | B | **45** | B | **65** | B | **85** | B |
| **6** | B | **26** | B | **46** | C | **66** | B | **86** | B |
| **7** | B | **27** | B | **47** | A | **67** | B | **87** | B |
| **8** | B | **28** | C | **48** | B | **68** | B | **88** | B |
| **9** | B | **29** | C | **49** | B | **69** | B | **89** | B |
| **10** | C | **30** | B | **50** | B | **70** | C | **90** | B |
| **11** | C | **31** | C | **51** | B | **71** | B | **91** | B |
| **12** | B | **32** | B | **52** | B | **72** | C | **92** | C |
| **13** | C | **33** | B | **53** | B | **73** | B | **93** | B |
| **14** | C | **34** | B | **54** | B | **74** | B | **94** | B |
| **15** | B | **35** | B | **55** | B | **75** | B | **95** | C |
| **16** | B | **36** | B | **56** | B | **76** | B | **96** | B |
| **17** | C | **37** | C | **57** | B | **77** | B | **97** | B |
| **18** | B | **38** | B | **58** | B | **78** | B | **98** | B |
| **19** | C | **39** | B | **59** | C | **79** | B | **99** | B |
| **20** | C | **40** | B | **60** | B | **80** | B | **100** | B |

---

## HƯỚNG DẪN GIẢI THÍCH CHI TIẾT THEO CÁC MÔ-ĐUN TRỌNG TÂM

1. **Về Kiến trúc & An toàn thông tin (Câu 1 &ndash; 12):**
   - Đề tài lựa chọn mô hình **Clean MVC thuần PHP 8** để các bạn sinh viên hiểu sâu sắc về vòng đời của HTTP Request, cách xây dựng phần mềm không phụ thuộc vào framework bên ngoài (`app/Controllers`, `app/Models`, `app/Services`, `app/Core`).
   - Phòng thủ đa tầng OWASP: 100% Prepared Statements (chống SQLi), Token ngẫu nhiên theo session (chống CSRF), Bcrypt cost 10 (chống bẻ khóa mật khẩu), kiểm soát phần mở rộng và MIME type thực tế trong `UploadService` (chống tải file độc hại), xử lý tiền tố công thức khi xuất CSV (chống CSV Injection).

2. **Về Cơ sở dữ liệu 14 bảng chuẩn 3NF (Câu 13 &ndash; 27):**
   - Hệ thống gồm đúng **14 bảng quan hệ InnoDB**.
   - Quyết định loại bỏ bảng `class_students` giúp mô hình đạt chuẩn 3NF tuyệt đối: Sinh viên chính quy trong trường đại học gắn liền với 1 lớp sinh hoạt cố định (`users.class_id`), loại bỏ dư thừa dữ liệu và loại bỏ các câu truy vấn `UNION` phức tạp.
   - Các bảng đều có ràng buộc khóa ngoại rõ ràng: `ON DELETE CASCADE` cho các thực thể phụ thuộc dữ liệu (như `ticket_replies` theo `tickets`), và `ON DELETE SET NULL` cho các thực thể độc lập (như xóa lớp không làm mất người dùng).

3. **Về Phân quyền RBAC & Quản lý vai trò (Câu 28 &ndash; 40):**
   - 4 vai trò rõ rệt:
     - `student` (hoặc `requester`): Sinh viên &ndash; Người gửi yêu cầu, xem môn học, nộp bài, đánh giá CSAT.
     - `lecturer`: Giảng viên &ndash; Quản lý lớp học phần được phân công, chấm điểm thang 10, phê duyệt gia hạn bài tập.
     - `agent`: Kỹ thuật viên &ndash; Trực ca, xử lý sự cố, giám định log kỹ thuật (`it_verified`), báo cáo vi phạm.
     - `admin`: Quản trị viên &ndash; Điều phối cấp cao, duyệt mở khóa tài khoản, phát cảnh báo diện rộng, cấu hình ngày nghỉ lễ, import tài khoản hàng loạt.
   - Đảm bảo tính cô lập dữ liệu (Privacy Isolation): Mỗi sinh viên chỉ truy vấn được phiếu của chính mình.

4. **Về Vòng đời Ticket & Điều phối Triage (Câu 41 &ndash; 55):**
   - Vòng đời: `new` &rarr; `in_progress` &rarr; `waiting_requester` &rarr; `resolved` &rarr; `closed`.
   - Thuật toán `TicketService::createTicketWithAutoDispatch()` ưu tiên 3 điều kiện:
     1. Chuyên môn phù hợp (`specialty_category_id = category_id`).
     2. Đang trong ca trực (`is_on_duty = 1`).
     3. Tải công việc thấp nhất (`active_tickets_count` nhỏ nhất).
   - Cơ chế phát hiện "Đùn đẩy" quá 3 lần chuyển giao giúp ngăn ngừa tình trạng trốn tránh trách nhiệm.

5. **Về Thuật toán SLA Giờ hành chính & Lịch nghỉ lễ (Câu 56 &ndash; 65):**
   - 8 giờ làm việc/ngày: Sáng 08:00 &ndash; 12:00, Chiều 13:30 &ndash; 17:30.
   - Thứ Bảy, Chủ Nhật và các ngày nghỉ lễ trong bảng `holidays` (bao gồm cả các kỳ nghỉ lặp lại hàng năm `is_recurring = 1`) được bỏ qua hoàn toàn.
   - Nhân hệ số SLA Factor: Khẩn cấp `0.5x`, Cao `0.8x`, Bình thường `1.0x`, Thấp `1.5x`.

6. **Về Liên thông Học phần & Gia hạn Deadline (Câu 66 &ndash; 77):**
   - Kỹ thuật viên chỉ có thẩm quyền xác nhận sự cố kỹ thuật khách quan (`it_verified = 1`).
   - Giảng viên phụ trách là người duy nhất quyết định gia hạn deadline nộp bài (`course_lessons.deadline_at`).
   - Thang điểm 10 chuẩn đại học (`DECIMAL 4,2`).
   - Nộp lại bài trước hạn sẽ cập nhật tệp mới và xóa điểm/nhận xét cũ để chấm lại.

7. **Về Bảo mật Mật khẩu & Trạm Cứu Hộ Khách (Câu 78 &ndash; 90):**
   - Mật khẩu mặc định `123456`, cờ `is_first_login = 1` ép đổi mật khẩu ngay lần đầu.
   - Cả 2 luồng `first_login` và `reset_password` (SSPR) đều kiểm tra:
     - Khác `123456`.
     - Khác hoàn toàn mật khẩu cũ hiện tại qua `password_verify($newPass, $currentHash)`.
   - SSPR cấp mã OTP 6 chữ cái in hoa ngẫu nhiên hiệu lực 15 phút, lưu trữ dưới dạng băm SHA-256.
   - Trạm Cứu Hộ Khách chỉ yêu cầu Mã đơn cứu hộ (không cần mã PIN), qua kiểm duyệt 2 bước (KTV xác thực &rarr; Admin mở khóa).

8. **Về Báo cáo vi phạm & Vận hành thực tế (Câu 91 &ndash; 100):**
   - KTV báo cáo sinh viên spam/vi phạm (`is_spam = 1`) &rarr; Admin ra quyết định kỷ luật.
   - Sự cố diện rộng (`is_major_incident = 1`) &rarr; Admin duyệt &rarr; Banner Broadcast đỏ hiển thị cho toàn trường.
   - CSAT Rating: 1 phiếu chỉ được đánh giá 1 lần duy nhất (`UNIQUE KEY ticket_id`).
   - Bulk Import Excel/CSV tự động bỏ qua các bản ghi trùng lặp và mã hóa Bcrypt tài khoản mới.
   - Hòm thư ảo `storage/mailbox.json` phục vụ kiểm thử gửi email trực quan trên môi trường Localhost.

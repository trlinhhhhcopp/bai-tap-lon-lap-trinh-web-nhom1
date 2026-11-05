# BỘ 100 CÂU HỎI TRẮC NGHIỆM CHUYÊN SÂU &ndash; PHẠM THỊ THANH NGÂN (224001736)

> **Trưởng nhóm phụ trách:** Phạm Thị Thanh Ngân (MSV: 224001736 &ndash; Lớp: TUD D2024)
> **Phân hệ chuyên trách:** Kiến Trúc Hệ Thống Cốt Lõi Clean MVC, Bàn Làm Việc KTV (Agent Workspace), Thuật Toán Triage, Model Ticket & Reply
> **Bảng CSDL cốt lõi:** `tickets`, `ticket_replies`
> **Model độc quyền:** `Ticket.php`, `Reply.php`

---

## 📑 MỤC LỤC 5 DẠNG CHUYÊN ĐỀ (100 CÂU HỎI MỚI)

- **[Chuyên đề 1: 1. Kiến Trúc Cốt Lõi Clean MVC Thuần PHP 8, Routing Engine & Front Controller](#chuyen-de-1)** (20 câu)
- **[Chuyên đề 2: 2. Bàn Làm Việc Kỹ Thuật Viên (Agent Workspace) & Quản Lý Hàng Đợi Phiếu](#chuyen-de-2)** (20 câu)
- **[Chuyên đề 3: 3. Thuật Toán Điều Phối Triage Thông Minh, Ngưỡng Quá Tải & Tràn Tải (Auto-Overflow)](#chuyen-de-3)** (20 câu)
- **[Chuyên đề 4: 4. Cơ Chế Đóng Trùng Lặp, Khóa Ngoại parent_ticket_id & Tạm Dừng Đồng Hồ SLA](#chuyen-de-4)** (20 câu)
- **[Chuyên đề 5: 5. Phân Tích Các Câu Lệnh Truy Vấn SQL Chuyên Sâu Của Trưởng Nhóm Thanh Ngân](#chuyen-de-5)** (20 câu)
- **[Bảng Đáp Án Chi Tiết 100 Câu](#bang-dap-an)**

---

## <a id="chuyen-de-1"></a>1. Kiến Trúc Cốt Lõi Clean MVC Thuần PHP 8, Routing Engine & Front Controller

#### Câu 1. Kiến trúc phần mềm cốt lõi được Trưởng nhóm Phạm Thị Thanh Ngân lựa chọn định hình cho toàn bộ dự án Helpdesk là gì?

- **A.** Monolithic truyền thống viết lẫn mã HTML và câu lệnh SQL vào chung một tệp.
- **B.** Clean MVC phân tầng thuần PHP 8 (Pure PHP), không phụ thuộc framework cồng kềnh, tổ chức tường minh Controller, Model, Service và View.
- **C.** Microservices triển khai trên nền tảng đám mây AWS.
- **D.** Single Page Application hoàn toàn bằng Node.js.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống Helpdesk Nhóm 1 được xây dựng theo chuẩn Clean MVC thuần PHP 8, phân tách độc lập giữa xử lý nghiệp vụ, giao tiếp dữ liệu và hiển thị giao diện.

#### Câu 2. Trong kiến trúc MVC của dự án, tầng Model (`app/Models/`) do Thanh Ngân thiết kế đảm nhận vai trò chính nào?

- **A.** Nhận yêu cầu HTTP và vẽ giao diện CSS.
- **B.** Đại diện cho các thực thể quan hệ, bao bọc toàn bộ truy vấn CSDL bằng PDO Prepared Statements (:named_params), tuyệt đối không để rò rỉ SQL ra ngoài View.
- **C.** Gửi tin nhắn SMS tới điện thoại sinh viên.
- **D.** Cấu hình cổng mạng của máy chủ Apache.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tầng Model đại diện cho 14 bảng quan hệ, đóng gói các phương thức CRUD an toàn và bảo vệ dữ liệu trước nguy cơ tấn công tiêm mã.

#### Câu 3. Tệp tin Front Controller `index.php` tại thư mục gốc đóng vai trò gì trong vòng đời xử lý yêu cầu người dùng?

- **A.** Chỉ là tệp chứa thông tin tác giả nhóm.
- **B.** Điểm tiếp nhận tập trung (Single Entry Point): nạp autoloader, kích hoạt session, khởi tạo database và định tuyến tham số `action` tới Controller tương ứng.
- **C.** Tự động tắt máy chủ khi người dùng thoát.
- **D.** Lưu mật khẩu ngân hàng của trường.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mọi yêu cầu truy cập từ trình duyệt đều đi qua `index.php` để được lọc bảo mật, nạp phiên xác thực và phân phối đúng Controller xử lý.

#### Câu 4. Lớp kết nối cơ sở dữ liệu `App\Core\Database` thiết lập cấu hình PDO quan trọng nào để bắt buộc PHP ném ngoại lệ khi có lỗi SQL?

- **A.** `PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT` (Bỏ qua lỗi)
- **B.** `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- **C.** `PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING`
- **D.** `PDO::ATTR_AUTOCOMMIT => false`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chế độ `ERRMODE_EXCEPTION` bảo đảm mọi lỗi cú pháp SQL hoặc vi phạm ràng buộc khóa ngoại đều ném ra ngoại lệ `PDOException`, hỗ trợ bắt lỗi bằng khối `try-catch` an toàn.

#### Câu 5. Chế độ nạp dữ liệu mặc định (`PDO::ATTR_DEFAULT_FETCH_MODE`) được Thanh Ngân cấu hình trong kết nối PDO là gì?

- **A.** `PDO::FETCH_NUM`
- **B.** `PDO::FETCH_ASSOC` (Mảng kết hợp ánh xạ tên cột thành khóa của mảng)
- **C.** `PDO::FETCH_BOTH` (Tốn gấp đôi dung lượng RAM)
- **D.** `PDO::FETCH_OBJ`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `PDO::FETCH_ASSOC` cho phép truy xuất dữ liệu tự nhiên và trực quan dạng `$ticket['ticket_code']`, `$ticket['status']` mà không lãng phí bộ nhớ.

#### Câu 6. Tệp middleware nền tảng `includes/auth.php` cung cấp các hàm trợ giúp (Helper Functions) cốt lõi nào cho hệ thống?

- **A.** `isLoggedIn()`, `currentUser()`, `hasRole($role)`, `requireLogin()`
- **B.** `sendMail()`, `printInvoice()`
- **C.** `formatDate()`, `calculateGPA()`
- **D.** `renderMenu()`, `showBanner()`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bộ hàm trợ giúp xác thực giúp các Controller và View kiểm tra nhanh chóng trạng thái đăng nhập và vai trò người dùng trong 1 dòng lệnh.

#### Câu 7. Để phòng chống tấn công Cross-Site Scripting (XSS) khi xuất dữ liệu ra tệp giao diện View, Thanh Ngân xây dựng hàm trợ giúp nào?

- **A.** `h($string)` hoặc `e($string)` bọc hàm `htmlspecialchars($string, ENT_QUOTES, 'UTF-8')`
- **B.** `strip_tags()`
- **C.** `urlencode()`
- **D.** `base64_encode()`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm rút gọn `h()` bảo đảm mọi chuỗi dữ liệu người dùng nhập (tiêu đề, nhận xét, mô tả) đều được mã hóa an toàn trước khi in ra trình duyệt.

#### Câu 8. Hai Model nghiệp vụ độc quyền do Trưởng nhóm Thanh Ngân trực tiếp thiết kế và quản lý là gì?

- **A.** `CourseLesson.php` và `Rating.php`
- **B.** `Ticket.php` (quản trị thực thể phiếu hỗ trợ) và `Reply.php` (quản trị các phản hồi, trao đổi của phiếu)
- **C.** `Holiday.php` và `Category.php`
- **D.** `PasswordReset.php` và `User.php`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `Ticket.php` và `Reply.php` là trái tim vận hành của hệ thống Helpdesk, chứa toàn bộ trạng thái, vòng đời và lịch sử tương tác của sự cố kỹ thuật.

#### Câu 9. Cơ chế nạp tự động lớp học (Autoloading) trong dự án tuân theo tiêu chuẩn kỹ thuật nào của PHP-FIG?

- **A.** `PSR-0`
- **B.** `PSR-4` (Ánh xạ không gian tên Namespace với cấu trúc thư mục trên ổ cứng)
- **C.** `PSR-7`
- **D.** `PSR-12`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tiêu chuẩn hiện đại PSR-4 giúp loại bỏ hoàn toàn việc phải `require` thủ công hàng trăm tệp lớp học, tăng tính mô-đun và sạch sẽ của mã nguồn.

#### Câu 10. Trong bảng `tickets`, khóa chính của bảng được định nghĩa theo thuộc tính nào?

- **A.** `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`
- **B.** `ticket_code VARCHAR(30) PRIMARY KEY`
- **C.** `created_at DATETIME PRIMARY KEY`
- **D.** `requester_id INT PRIMARY KEY`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa chính số nguyên tự tăng `id` bảo đảm tốc độ liên kết khóa ngoại với các bảng con (`ticket_replies`, `ticket_ratings`) đạt hiệu năng tối đa.

#### Câu 11. Cột `ticket_code` trong bảng `tickets` có ràng buộc toàn vẹn nào?

- **A.** `NULL`
- **B.** `UNIQUE NOT NULL` (Khóa ứng viên duy nhất chống trùng lặp mã vé)
- **C.** `CHECK (ticket_code > 0)`
- **D.** `FOREIGN KEY`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mã vé duy nhất giúp định danh chính xác từng sự cố khi giao tiếp giữa sinh viên, giảng viên và kỹ thuật viên.

#### Câu 12. Mã nguồn dự án sử dụng bộ thư viện hoặc cơ chế nào để giả lập hòm thư điện tử thử nghiệm trên môi trường Localhost?

- **A.** Bắt buộc phải mua dịch vụ Mail Server thật tốn kém.
- **B.** Fake Mailbox nội bộ lưu vào tệp `storage/mailbox.json`, cho phép kiểm tra nội dung thư và token khôi phục mật khẩu ngay tức thì.
- **C.** In email ra giấy máy in.
- **D.** Không cho phép gửi email.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giải pháp Fake Mailbox thông minh: hệ thống tự động ghi nhật ký email ra file JSON giúp nhóm kiểm thử trọn vẹn luồng gửi thư mà không cần cấu hình SMTP ngoài.

#### Câu 13. Tệp `.htaccess` tại thư mục gốc máy chủ Apache đảm nhiệm vai trò bảo mật nào trong kiến trúc dự án?

- **A.** Ngăn duyệt cây thư mục trực tiếp (`Options -Indexes`) và cấm truy cập trực tiếp vào các tệp nhạy cảm (`.env`, `.sql`, `.md`, `storage/`).
- **B.** Tự động tăng tốc độ mạng lên 10 lần.
- **C.** Xóa cookie của trình duyệt.
- **D.** Tắt máy chủ khi có người truy cập.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tấm lá chắn bảo vệ cấp máy chủ web, bảo vệ an toàn các tệp tin cấu hình và dữ liệu sao lưu trước việc dò quét của hacker.

#### Câu 14. Bảng mã ký tự (Character Set) và quy tắc đối chiếu (Collation) được Thanh Ngân thiết lập chuẩn cho toàn bộ CSDL là gì?

- **A.** `latin1` / `latin1_swedish_ci`
- **B.** `utf8mb4` / `utf8mb4_unicode_ci`
- **C.** `ascii` / `ascii_general_ci`
- **D.** `binary`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `utf8mb4` hỗ trợ đầy đủ 4 byte biểu diễn hoàn hảo toàn bộ bảng chữ cái tiếng Việt có dấu và các biểu tượng cảm xúc Emoji hiện đại.

#### Câu 15. Hàm PHP nào trong `app/Core/Controller.php` biến đổi mảng dữ liệu truyền từ Controller thành các biến cục bộ trong file View?

- **A.** `array_merge()`
- **B.** `extract($data)`
- **C.** `json_encode()`
- **D.** `var_dump()`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `extract($data)` biến các phần tử `['tickets' => $t, 'user' => $u]` thành biến `$tickets` và `$user` giúp giao diện View viết mã ngắn gọn, tự nhiên.

#### Câu 16. Trong bảng `tickets`, trường `status` nhận những giá trị ENUM chuẩn nào biểu diễn vòng đời hoàn chỉnh của sự cố?

- **A.** `'start'`, `'end'`
- **B.** `'new'`, `'assigned'`, `'in_progress'`, `'waiting_requester'`, `'resolved'`, `'closed'`
- **C.** `'pass'`, `'fail'`
- **D.** `'true'`, `'false'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Máy trạng thái hữu hạn (State Machine) gồm 6 nấc chuẩn mực của ITIL v4, phản ánh đầy đủ từng bước từ lúc phát sinh đến lúc đóng sự cố.

#### Câu 17. Khi người dùng đăng xuất khỏi hệ thống (`logout.php`), quy trình dọn dẹp phiên làm việc an toàn gồm những thao tác nào?

- **A.** Chỉ đổi URL về trang chủ mà giữ nguyên session.
- **B.** Làm rỗng mảng phiên `$_SESSION = []`, hủy bỏ định danh phiên qua `session_destroy()` và xóa cookie phiên của trình duyệt.
- **C.** Xóa tài khoản người dùng trong CSDL.
- **D.** Khóa tài khoản 30 ngày.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy trình hủy phiên nghiêm ngặt loại trừ hoàn toàn nguy cơ tấn công chiếm đoạt phiên đăng nhập (Session Hijacking).

#### Câu 18. Kỹ thuật POST-Redirect-GET (PRG Pattern) được áp dụng tại các Controller nhằm mục đích gì?

- **A.** Làm cho trang web tải chậm hơn.
- **B.** Ngăn chặn hiện tượng gửi lại biểu mẫu (Duplicate Form Submission) khi người dùng bấm phím F5 hoặc nút Refresh trên trình duyệt.
- **C.** Tự động gửi thêm 1 phiếu mới.
- **D.** Xóa dữ liệu vừa nhập.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sau khi xử lý POST thành công, Controller chuyển hướng `header('Location: ...')` sang request GET, bảo đảm người dùng F5 không bị gửi lặp dữ liệu.

#### Câu 19. Cơ chế Flash Message trong hệ thống lưu trữ thông báo phản hồi người dùng ở đâu trước khi chuyển trang?

- **A.** Lưu trong thẻ nhớ ngoài.
- **B.** Lưu tạm trong `$_SESSION['flash']`, tự động đọc và xóa bỏ (Unset) ngay sau khi hiển thị xong trên View tiếp theo.
- **C.** Lưu vĩnh viễn vào ổ cứng.
- **D.** Ghi vào tệp log của hệ điều hành.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Flash Message hiển thị các thông báo màu xanh lá (Thành công) hoặc đỏ (Thất bại) đúng 1 lần duy nhất, không bị lưu cữu gây nhầm lẫn.

#### Câu 20. Trách nhiệm lãnh đạo của Trưởng nhóm Phạm Thị Thanh Ngân trong Đồ án Helpdesk Nhóm 1 thể hiện qua điểm nào?

- **A.** Chỉ phân công công việc rồi không tham gia lập trình.
- **B.** Thiết kế kiến trúc nền tảng Clean MVC, phân chia ranh giới 5 Model độc quyền cho 5 thành viên, xây dựng Bàn làm việc KTV và liên tục hỗ trợ tích hợp toàn bộ các phân hệ thành một khối thống nhất.
- **C.** Chỉ làm nhiệm vụ nộp bài tập lớn cho cô giáo.
- **D.** Tự làm hết một mình không cho ai tham gia.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thanh Ngân hoàn thành xuất sắc vai trò kiến trúc sư trưởng và điều phối viên, bảo đảm 100% các tiêu chí kỹ thuật và quy chế đào tạo của trường đều đạt điểm tối ưu.

## <a id="chuyen-de-2"></a>2. Bàn Làm Việc Kỹ Thuật Viên (Agent Workspace) & Quản Lý Hàng Đợi Phiếu

#### Câu 21. Giao diện Bàn làm việc Kỹ thuật viên (`agent/index.php`) do Thanh Ngân xây dựng phục vụ những chức năng nghiệp vụ trọng tâm nào?

- **A.** Quản lý điểm thi tốt nghiệp của sinh viên.
- **B.** Tiếp nhận hàng đợi sự cố, theo dõi đồng hồ SLA theo thời gian thực, trao đổi phản hồi với người dùng và chuyển đổi trạng thái giải quyết sự cố.
- **C.** Thu tiền vé xe trong khuôn viên trường.
- **D.** Cài đặt lại hệ điều hành Windows từ xa.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Agent Workspace là trung tâm xử lý kỹ thuật hàng ngày của KTV: trực quan hóa hàng đợi, cảnh báo quá hạn SLA và tương tác hai chiều với người gửi phiếu.

#### Câu 22. Danh sách các phiếu trong hàng đợi của KTV (`agent/partials/ticket_queue.php`) được sắp xếp mặc định theo tiêu chuẩn nào để tối ưu cam kết SLA?

- **A.** Sắp xếp ngẫu nhiên.
- **B.** Sắp xếp theo thời hạn cam kết SLA tăng dần (`due_at ASC`), các phiếu sắp hết hạn cam kết luôn nổi bật lên đầu hàng đợi để KTV ưu tiên xử lý trước.
- **C.** Sắp xếp theo thứ tự bảng chữ cái tên sinh viên.
- **D.** Sắp xếp theo dung lượng tệp đính kèm.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc vận hành ITSM: Hạn SLA gần nhất (`due_at ASC`) bảo đảm KTV không bỏ quên các sự cố sắp hết hạn, nâng cao tỷ lệ tuân thủ SLA toàn trường.

#### Câu 23. Khi xem một phiếu hỗ trợ, KTV có thể gửi hai hình thức phản hồi nào qua form trao đổi?

- **A.** Tin nhắn thoại và Video clip.
- **B.** Phản hồi công khai (Public Reply &ndash; sinh viên đọc được) và Ghi chú nội bộ (Private Note `is_internal = 1` &ndash; chỉ KTV và Admin xem được).
- **C.** Chỉ được gửi tin nhắn công khai.
- **D.** Chỉ được gửi mã độc.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phân định rõ ràng: Ghi chú nội bộ phục vụ bàn giao ca trực hoặc trao đổi kỹ thuật bí mật giữa các KTV mà người dùng ngoài không nhìn thấy.

#### Câu 24. Cột `is_internal` trong bảng `ticket_replies` có kiểu dữ liệu gì và mang ý nghĩa gì?

- **A.** `VARCHAR(100)` lưu tên người gửi.
- **B.** `TINYINT(1)`, nhận giá trị 1 nếu là ghi chú nội bộ giữa các kỹ thuật viên, nhận giá trị 0 nếu là tin nhắn trao đổi công khai với người tạo phiếu.
- **C.** `INT` lưu số tiền tip cho KTV.
- **D.** `DATETIME` lưu giờ đọc tin nhắn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cờ `is_internal` bảo vệ thông tin kỹ thuật nhạy cảm (như mật khẩu tạm, địa chỉ IP switch, log lỗi hệ thống) không bị lộ ra phía sinh viên.

#### Câu 25. Phân quyền RBAC quy định như thế nào về thẩm quyền 'Xóa phiếu hỗ trợ' trong hệ thống?

- **A.** KTV được quyền xóa bất kỳ phiếu nào mình không thích.
- **B.** KTV tuyệt đối KHÔNG CÓ nút xóa phiếu; chỉ Quản trị viên (Admin) mới có thẩm quyền tối cao xóa phiếu trong các trường hợp rác hoặc thử nghiệm.
- **C.** Sinh viên có quyền xóa phiếu của KTV.
- **D.** Ai cũng có quyền xóa phiếu bằng phương thức GET.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chống xóa dấu vết sự cố: KTV chỉ có thẩm quyền tiếp nhận và giải quyết; việc xóa dữ liệu lịch sử do duy nhất Admin kiểm soát.

#### Câu 26. Phân quyền RBAC quy định như thế nào về việc KTV chỉnh sửa tiêu đề hoặc nội dung mô tả ban đầu của người gửi phiếu?

- **A.** KTV được tự do sửa đổi nội dung phản ánh của sinh viên.
- **B.** KTV tuyệt đối KHÔNG ĐƯỢC sửa tiêu đề hoặc mô tả của người gửi nhằm bảo toàn nguyên trạng lời phản ánh ban đầu của sự cố.
- **C.** KTV phải viết lại tiêu đề bằng thơ lục bát.
- **D.** KTV có thể xóa hết mô tả và để trống.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo vệ tính khách quan của dữ liệu khiếu nại: nội dung người học trình bày được giữ nguyên vẹn 100% làm bằng chứng đối soát.

#### Câu 27. Khi một KTV tiếp nhận một phiếu mới (`new`), KTV nhấn nút thao tác nào để chuyển phiếu sang giai đoạn bắt tay vào xử lý?

- **A.** Nút 'Xóa phiếu'.
- **B.** Nút 'Tiếp nhận xử lý' (Chuyển trạng thái phiếu từ `new`/`assigned` sang `in_progress`).
- **C.** Nút 'Đóng phiếu ngay'.
- **D.** Nút 'Chuyển trường'.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuyển sang `in_progress` thông báo cho sinh viên biết KTV đã bắt đầu xử lý sự cố, tạo sự yên tâm cho người học.

#### Câu 28. Khi KTV cần người dùng cung cấp thêm minh chứng (như mã sinh viên hoặc ảnh chụp lỗi), KTV chuyển phiếu sang trạng thái nào?

- **A.** `closed`
- **B.** `waiting_requester` (Chờ người dùng phản hồi)
- **C.** `resolved`
- **D.** `deleted`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `waiting_requester` đưa bóng trách nhiệm về phía người dùng, đồng thời kích hoạt cơ chế tạm dừng đồng hồ tính giờ SLA của KTV.

#### Câu 29. Khi KTV đã khắc phục triệt để lỗi kỹ thuật (ví dụ: cấp lại mật khẩu hoặc thông mạng Wi-Fi), KTV chuyển phiếu sang trạng thái nào?

- **A.** `closed` ngay lập tức mà không cần hỏi người dùng.
- **B.** `resolved` (Đã giải quyết) kèm thông điệp giải pháp chi tiết để mời người dùng vào kiểm tra thực tế và nghiệm thu CSAT.
- **C.** `new` để làm lại từ đầu.
- **D.** `waiting_requester`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn ITIL v4: KTV không được tự ý 'closed' phiếu; KTV chỉ đưa về `resolved`, quyền đóng phiếu thuộc về người dùng sau khi nghiệm thu.

#### Câu 30. Nếu KTV phát hiện sinh viên báo sai sự cố (ví dụ: máy tính sinh viên tự tắt do hết pin nhưng lại báo mạng trường hỏng), KTV có nút xử lý nào?

- **A.** Đánh nhau với sinh viên.
- **B.** Nút 'Hủy báo lỗi (SV báo sai)' &ndash; hệ thống tự động hoàn tất và đóng phiếu với lý do `invalid_report_by_ktv` minh bạch.
- **C.** Khóa thẻ sinh viên của người đó.
- **D.** Tự nhận lỗi về mình.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo vệ uy tín KTV và giảm thiểu lãng phí thời gian xử lý các phản ánh không có cơ sở kỹ thuật.

#### Câu 31. Trong bảng `tickets`, cột `assigned_to` là khóa ngoại tham chiếu đến cột nào trong bảng `users`?

- **A.** `users.email`
- **B.** `users.id` (ID của Kỹ thuật viên phụ trách xử lý)
- **C.** `users.phone`
- **D.** `users.student_code`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa ngoại liên kết KTV chịu trách nhiệm giải quyết sự cố, phục vụ thống kê khối lượng công việc và đánh giá KPI.

#### Câu 32. Khi KTV mở xem chi tiết một phiếu có mã `#HD-2026-2910-T0L001`, thẻ thông tin người gửi (Requester Info Card) hiển thị những dữ liệu gì?

- **A.** Chỉ hiển thị họ tên.
- **B.** Họ tên, Mã sinh viên/giảng viên, Email trường, Số điện thoại, Khoa/Phòng ban và Lớp sinh hoạt hành chính.
- **C.** Số dư tài khoản ngân hàng.
- **D.** Điểm thi THPT Quốc gia.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cung cấp bức tranh toàn cảnh về người yêu cầu giúp KTV dễ dàng liên lạc qua điện thoại hoặc email khi cần phối hợp xử lý gấp.

#### Câu 33. Tính năng 'Bộ lọc bảo mật SV Vi Phạm' trên danh sách phiếu của KTV hoạt động như thế nào?

- **A.** Công khai thông tin sinh viên vi phạm cho toàn bộ mạng xã hội.
- **B.** Chỉ hiển thị nhãn cảnh báo vi phạm đối với KTV và Admin; các sinh viên khác hoàn toàn không thể xem được dữ liệu nhạy cảm này.
- **C.** Xóa thông tin vi phạm ngay lập tức.
- **D.** Bắt sinh viên vi phạm phải nộp tiền phạt.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo mật quyền riêng tư cá nhân và uy tín của người học theo đúng quy định pháp luật về bảo vệ dữ liệu cá nhân.

#### Câu 34. Cột `resolved_at` trong bảng `tickets` được ghi nhận tại thời điểm nào?

- **A.** Khi sinh viên vừa tạo phiếu.
- **B.** Khi KTV chuyển trạng thái phiếu sang `resolved` (Đã giải quyết xong sự cố).
- **C.** Khi sinh viên tốt nghiệp ra trường.
- **D.** Khi máy tính được bán đấu giá.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `resolved_at` chốt mốc thời gian hoàn thành kỹ thuật, làm căn cứ so sánh với `due_at` để đánh giá KTV xử lý 'Đúng hạn' hay 'Quá hạn SLA'.

#### Câu 35. Khi một phiếu đã chuyển sang `resolved` nhưng quá 24h sinh viên không vào nghiệm thu CSAT, thời điểm `closed_at` được ghi nhận ra sao?

- **A.** `closed_at = NULL` mãi mãi.
- **B.** Hệ thống ghi nhận `closed_at = NOW()` tại thời điểm tiến trình Auto-Close tự động đóng phiếu.
- **C.** Lùi thời điểm đóng về 10 năm trước.
- **D.** Ghi nhận bằng ngày sinh nhật của Admin.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ghi nhận mốc kết thúc vòng đời phiếu rõ ràng, khép lại tiến trình mở của ticket trong cơ sở dữ liệu.

#### Câu 36. Trên giao diện Bàn làm việc KTV, tính năng 'Collapsible Table' (Bảng thu gọn/mở rộng) mang lại tiện ích gì?

- **A.** Tự động tắt máy tính của KTV.
- **B.** Cho phép KTV bấm nút để ẩn/hiện các bảng dữ liệu (như bảng phiếu đã đóng, bảng phân công), giúp không gian làm việc gọn gàng và tập trung vào các phiếu đang cần xử lý gấp.
- **C.** Xóa dữ liệu trong bảng.
- **D.** Đổi màu font chữ ngẫu nhiên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tối ưu trải nghiệm người dùng (UX) cho đội ngũ hỗ trợ kỹ thuật, tăng tốc độ xử lý khi phải thao tác liên tục trên màn hình máy tính.

#### Câu 37. Đồng hồ đếm ngược SLA trên giao diện chi tiết phiếu đổi sang màu đỏ cảnh báo khi nào?

- **A.** Khi KTV bắt đầu bấm vào xem phiếu.
- **B.** Khi thời gian thực tế đã vượt quá mốc hạn chót cam kết (`NOW() > due_at`) và phiếu vẫn chưa được giải quyết (Trạng thái Quá Hạn - Overdue SLA).
- **C.** Khi pin máy tính của KTV dưới 20%.
- **D.** Khi ngoài trời có mưa.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cảnh báo thị giác mạnh mẽ thúc đẩy KTV khẩn trương hoàn thành dứt điểm sự cố trước khi bị tính lỗi vi phạm SLA trong báo cáo tháng.

#### Câu 38. Khi KTV chuyển giao một phiếu sang cho KTV khác (`Ticket Transfer`), trường nào trong bảng `tickets` được cập nhật?

- **A.** `requester_id`
- **B.** `assigned_to = :new_agent_id` kèm theo một bản ghi phản hồi nội bộ ghi nhận lý do chuyển giao.
- **C.** `created_at`
- **D.** `lesson_id`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bàn giao trách nhiệm minh bạch, chuyển toàn bộ quyền và nghĩa vụ xử lý sang cho đồng nghiệp có chuyên môn phù hợp hơn.

#### Câu 39. KTV có được phép tự ý sửa điểm đánh giá sao CSAT mà sinh viên đã chấm cho mình không?

- **A.** Được sửa thành 5 sao nếu thấy sinh viên chấm thấp.
- **B.** Tuyệt đối không; điểm CSAT trong bảng `ticket_ratings` là bất biến đối với KTV, bảo đảm tính trung thực và khách quan của việc đánh giá dịch vụ.
- **C.** Chỉ được sửa vào ngày cuối tuần.
- **D.** Được quyền đổi sang điểm chữ A.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc liêm chính dữ liệu trong ITSM: KTV không được can thiệp vào kết quả đánh giá của khách hàng.

#### Câu 40. Để phục vụ việc trao đổi nhanh và gửi kèm giải pháp xử lý sự cố cho sinh viên, form phản hồi của KTV cho phép đính kèm tệp ở định dạng nào?

- **A.** Chỉ cho phép tải lên file nhạc `.mp3`.
- **B.** Các định dạng tài liệu, hình ảnh hướng dẫn và tệp nén: `.png`, `.jpg`, `.pdf`, `.docx`, `.zip`, `.txt`.
- **C.** Các file virus `.bat`, `.vbs`.
- **D.** Không cho đính kèm tệp.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hỗ trợ KTV gửi ảnh chụp màn hình hướng dẫn cấu hình hoặc tệp cài đặt driver khắc phục sự cố nhanh chóng cho người dùng.

## <a id="chuyen-de-3"></a>3. Thuật Toán Điều Phối Triage Thông Minh, Ngưỡng Quá Tải & Tràn Tải (Auto-Overflow)

#### Câu 41. Thuật toán Triage (Phân loại & Điều phối thông minh) trong `TicketService.php` của Thanh Ngân giải quyết bài toán cốt lõi nào?

- **A.** Tính tiền điện cho phòng máy chủ.
- **B.** Tự động phân tích danh mục sự cố, tìm kiếm KTV chuyên trách đang trực ca có tải làm việc thấp nhất để gán phiếu tự động trong 0.1 giây.
- **C.** Xóa các phiếu của sinh viên cá biệt.
- **D.** Tự động tắt máy in của trường.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Loại bỏ hoàn toàn khâu điều phối thủ công bằng con người, rút ngắn thời gian tiếp nhận sự cố từ hàng giờ xuống còn phần trăm giây.

#### Câu 42. Ba điều kiện tiên quyết trong câu lệnh SQL để một KTV được đưa vào danh sách ứng viên tiếp nhận phiếu của thuật toán Triage là gì?

- **A.** `role = 'admin'`, `gender = 'male'`, `age > 30`
- **B.** `role = 'agent'`, `is_active = 1` (Tài khoản đang hoạt động) và `is_on_duty = 1` (Đang trong ca trực được Admin phân công).
- **C.** `role = 'student'`, `score >= 8`
- **D.** `role = 'lecturer'`, `department = 'CNTT'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo đảm người nhận phiếu phải là Kỹ thuật viên, tài khoản không bị khóa và bắt buộc đang ngồi tại vị trí trực ca làm việc.

#### Câu 43. Nguyên lý điều phối 'Least-Loaded' (Ít tải nhất) trong thuật toán Triage hoạt động như thế nào?

- **A.** Gán cho KTV có tuổi nghề trẻ nhất.
- **B.** Đếm số lượng phiếu đang xử lý (`COUNT(status IN ('assigned', 'in_progress'))`) của từng KTV, ưu tiên gán phiếu mới cho KTV có số lượng phiếu đang mở ít nhất.
- **C.** Gán luân phiên theo vòng tròn không cần quan tâm số lượng việc.
- **D.** Gán cho KTV đang đi vắng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cân bằng tải lao động tối ưu (Load Balancing), chống tình trạng người thì làm không hết việc, người thì ngồi chơi xơi nước.

#### Câu 44. Ngưỡng quá tải (Overload Threshold) cho mỗi KTV trong thuật toán điều phối của Thanh Ngân được quy định là bao nhiêu phiếu?

- **A.** 1 phiếu.
- **B.** 5 phiếu đang hoạt động (`active_tickets >= 5`).
- **C.** 50 phiếu.
- **D.** 1.000 phiếu.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ngưỡng 5 phiếu là chuẩn mực khoa học lao động IT: bảo đảm KTV không bị quá tải tâm lý, duy trì chất lượng hỗ trợ người học ở mức cao nhất.

#### Câu 45. Khi một KTV chuyên trách (ví dụ KTV An phụ trách mảng SSO) ĐÃ ĐẠT NGƯỠNG QUÁ TẢI (đang có 5 phiếu dở dang), thuật toán Triage xử lý ra sao?

- **A.** Vẫn cố tình nhồi nhét thêm phiếu cho KTV An đến khi kiệt sức.
- **B.** Kích hoạt cơ chế Tràn Tải Tự Động (Auto-Overflow): tìm kiếm KTV chuyên trách khác hoặc KTV đa năng đang trực ca có tải thấp nhất để gán phiếu.
- **C.** Tự động hủy phiếu của sinh viên.
- **D.** Báo lỗi máy chủ bị quá tải.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế Tràn tải (Overflow Mechanism) bảo đảm hàng đợi không bị tắc nghẽn cục bộ tại một cá nhân kỹ thuật viên.

#### Câu 46. Trong trường hợp KTV chuyên trách mảng Wi-Fi đang nghỉ phép (`is_on_duty = 0`), phiếu báo sự cố Wi-Fi mới sẽ được gán cho ai?

- **A.** Bắt buộc phải chờ đến khi KTV chuyên trách đi làm lại sau 1 tuần.
- **B.** Hệ thống tự động phát hiện KTV chuyên trách đang nghỉ ca và phân bổ phiếu cho KTV dự phòng đang trực ca có tải ít nhất.
- **C.** Gán cho Hiệu trưởng nhà trường.
- **D.** Gán cho lớp trưởng lớp D24.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế dự phòng ca trực bảo đảm sinh viên luôn được hỗ trợ kịp thời kể cả trong các tình huống kỹ thuật viên chính vắng mặt.

#### Câu 47. Nếu TẤT CẢ các KTV trong trường đều đang quá tải hoặc không có ai trong ca trực (ngoài giờ làm việc), phiếu mới sẽ rơi vào trạng thái nào?

- **A.** Bị xóa vĩnh viễn khỏi CSDL.
- **B.** Rơi vào Hàng đợi chờ tiếp nhận tập trung (`status = 'new'`, `assigned_to = NULL`) và hẹn giờ bắt đầu tính SLA sang đầu giờ làm việc tiếp theo.
- **C.** Báo lỗi mạng cho sinh viên.
- **D.** Tự động giải quyết thành công.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo toàn 100% dữ liệu yêu cầu của người dùng, sẵn sàng phân phối ngay khi ca trực tiếp theo bắt đầu làm việc.

#### Câu 48. Hàm PHP nào trong `TicketService.php` thực hiện logic phân công tự động (Auto-Dispatch)?

- **A.** `autoDispatch($ticketId, $categoryId)`
- **B.** `deleteTicket($id)`
- **C.** `sendSmsNotification()`
- **D.** `printBill()`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phương thức `autoDispatch` nhận mã sự cố và danh mục, kết nối CSDL tìm KTV tối ưu và gán `assigned_to` cho bản ghi.

#### Câu 49. Chỉ mục (Composite Index) `idx_agent_dispatch` được tạo trên những cột nào trong bảng `users` để tối ưu thuật toán Triage?

- **A.** `(name, email)`
- **B.** `(role, is_on_duty, is_active)`
- **C.** `(phone, department)`
- **D.** `(created_at, updated_at)`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ mục tổng hợp 3 cột giúp MySQL lọc ngay lập tức tập hợp các KTV đủ điều kiện mà không cần quét toàn bộ bảng dữ liệu người dùng (Full Table Scan).

#### Câu 50. Khi một KTV giải quyết xong một phiếu (chuyển sang `resolved` hoặc `closed`), tải công việc (`active_tickets`) của KTV đó biến động ra sao?

- **A.** Không thay đổi.
- **B.** Tự động giảm đi 1 đơn vị, giải phóng một vị trí tải để thuật toán Triage có thể tiếp tục phân bổ phiếu mới cho KTV này.
- **C.** Tăng lên gấp đôi.
- **D.** Biến thành số âm.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dung lượng xử lý tự động hồi phục theo thời gian thực: KTV giải quyết sự cố càng nhanh thì càng tiếp nhận được các nhiệm vụ mới hiệu quả.

#### Câu 51. Thuật toán điều phối Triage của Thanh Ngân có bảo đảm tính xác định (Deterministic) và công bằng không?

- **A.** Không, phân công hoàn toàn ngẫu nhiên may rủi.
- **B.** Hoàn toàn xác định và công bằng: Dựa trên số liệu tải công việc thực tế, trạng thái ca trực và chuyên môn kỹ thuật, loại bỏ yếu tố cảm tính thiên vị.
- **C.** Chỉ ưu tiên gán cho KTV nữ.
- **D.** Chỉ gán cho KTV ở gần trường nhất.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Minh bạch khoa học: Mọi quyết định điều phối đều dựa trên dữ liệu định lượng chính xác của hệ thống.

#### Câu 52. Khi một phiếu được gán cho KTV thành công qua thuật toán Triage, trạng thái của phiếu chuyển từ `new` sang trạng thái gì?

- **A.** `closed`
- **B.** `assigned` (Đã phân công người xử lý)
- **C.** `resolved`
- **D.** `waiting_requester`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trạng thái `assigned` biểu thị phiếu đã có chủ nhân phụ trách cụ thể, sẵn sàng cho bước bắt tay vào khắc phục.

#### Câu 53. KTV có nhận được thông báo trên màn hình khi thuật toán Triage vừa gán một phiếu mới vào hàng đợi của mình không?

- **A.** Không, phải tự đoán.
- **B.** Có; hệ thống cập nhật bộ đếm huy hiệu trên thanh điều hướng và hiển thị thông báo mới ở đầu hàng đợi.
- **C.** Chỉ nhận được thông báo sau 1 tháng.
- **D.** Hệ thống tự động phát bài hát qua loa máy tính.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cảnh báo tức thời giúp KTV nắm bắt sự việc ngay từ phút đầu tiên, chuẩn bị phương án khắc phục nhanh chóng.

#### Câu 54. Nếu Quản trị viên (Admin) thấy một sự cố đặc biệt phức tạp cần đích thân KTV giỏi nhất xử lý, Admin có quyền can thiệp thủ công không?

- **A.** Không được phép, máy móc quyết định tất cả.
- **B.** Có; Admin có toàn quyền can thiệp chỉ định thủ công (Manual Reassign) bất kỳ phiếu nào sang cho KTV mong muốn.
- **C.** Admin phải viết đơn xin phép KTV.
- **D.** Phải xóa phiếu đi làm lại.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sự kết hợp hoàn hảo giữa tự động hóa thông minh và quyền can thiệp quản trị linh hoạt của con người.

#### Câu 55. Tiêu chí chuyên môn kỹ thuật (`categories`) của KTV được cấu hình tại đâu trong CSDL?

- **A.** Ghi trong tên của KTV.
- **B.** Cấu hình qua trường chuyên môn chính trong hồ sơ người dùng hoặc bảng phân công danh mục KTV.
- **C.** Lưu trong tệp ảnh đại diện.
- **D.** Không lưu ở đâu.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ánh xạ chuyên môn giúp phân loại sự cố: Lỗi mạng giao cho chuyên viên Mạng, Lỗi phần mềm giao cho chuyên viên LMS/SSO.

#### Câu 56. Lợi ích kinh tế và vận hành lớn nhất của thuật toán Triage thông minh trong hệ thống Helpdesk Trường Đại học Thủ Đô Hà Nội là gì?

- **A.** Tăng số lượng KTV phải tuyển dụng.
- **B.** Tối ưu hóa năng suất lao động, giảm $80\%$ thời gian điều phối trung gian và nâng cao mức độ hài lòng CSAT của sinh viên toàn trường.
- **C.** Bắt sinh viên phải trả thêm tiền dịch vụ.
- **D.** Làm cho máy chủ hoạt động nóng hơn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giá trị thực tiễn cốt lõi của công nghệ chuyển đổi số: Tối ưu quy trình, giảm chi phí nhân sự và nâng tầm chất lượng dịch vụ đào tạo.

#### Câu 57. Trong mã nguồn `TicketService.php`, nếu cả 2 KTV chuyên trách đều có cùng số lượng phiếu đang xử lý bằng nhau (ví dụ đều đang có 2 phiếu), thuật toán chọn ai?

- **A.** Báo lỗi xung đột dữ liệu.
- **B.** Chọn KTV có ID nhỏ hơn hoặc KTV có thời điểm tiếp nhận phiếu gần nhất xa hơn (Tie-breaker logic ổn định).
- **C.** Gán phiếu cho cả 2 người cùng lúc.
- **D.** Hủy phiếu đó.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thuật toán Tie-breaker bảo đảm tính nhất quán (Consistency), không bao giờ rơi vào trạng thái treo vô định.

#### Câu 58. Khi KTV đang trực ca nhưng phải rời vị trí đột xuất 10 phút để đi kiểm tra đường dây cáp quang, điều gì xảy ra với các phiếu mới tạo?

- **A.** Phiếu vẫn được xếp hàng đợi theo thứ tự cam kết SLA và KTV sẽ xử lý ngay khi quay lại bàn làm việc.
- **B.** Hệ thống tự động trừ tiền của KTV.
- **C.** Hệ thống tự động đóng trường học.
- **D.** Phiếu bị gửi sang trường đại học khác.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàng đợi tin cậy (Reliable Queue) bảo toàn mọi yêu cầu theo đúng thứ tự ưu tiên của thời gian cam kết SLA.

#### Câu 59. Trong bài báo cáo kỹ thuật, Thanh Ngân mô hình hóa luồng Triage và Vòng đời sự cố bằng biểu đồ chuẩn nào?

- **A.** Sơ đồ mạch điện tử analog.
- **B.** Sơ đồ luồng trạng thái chuẩn ITIL v4 (State Transition Diagram / Flowchart) trực quan rõ ràng.
- **C.** Bản đồ địa lý thành phố Hà Nội.
- **D.** Biểu đồ hình tròn tỷ lệ dân số.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Minh chứng đồ họa học thuật sắc nét trong báo cáo, thể hiện tư duy phân tích hệ thống bài bản của sinh viên CNTT.

#### Câu 60. Tóm lại, phân hệ Bàn làm việc KTV & Thuật toán Triage do Thanh Ngân xây dựng đã đưa Helpdesk Nhóm 1 tiệm cận tiêu chuẩn của phần mềm thương mại nào?

- **A.** Phần mềm gõ văn bản Notepad.
- **B.** Các giải pháp Quản lý Dịch vụ CNTT hàng đầu thế giới như Jira Service Management, Zendesk và ServiceNow.
- **C.** Trò chơi Flappy Bird.
- **D.** Ứng dụng máy tính bỏ túi Calculator.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sản phẩm đạt độ chín muồi cao về công nghệ, kiến trúc mã nguồn và nghiệp vụ vận hành thực tế.

## <a id="chuyen-de-4"></a>4. Cơ Chế Đóng Trùng Lặp, Khóa Ngoại parent_ticket_id & Tạm Dừng Đồng Hồ SLA

#### Câu 61. Cột `parent_ticket_id` trong bảng `tickets` là khóa ngoại đặc biệt có thuộc tính gì trong mô hình CSDL quan hệ?

- **A.** Khóa ngoại trỏ sang cơ sở dữ liệu của Google.
- **B.** Khóa ngoại tự tham chiếu (Self-referencing Foreign Key) trỏ ngược về chính cột `tickets.id` của phiếu sự cố gốc (Phiếu cha).
- **C.** Khóa ngoại trỏ sang bảng điểm học tập.
- **D.** Cột lưu địa chỉ nhà của cha mẹ sinh viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa ngoại tự tham chiếu là giải pháp kinh điển để xây dựng quan hệ phân cấp Cha &ndash; Con (Parent &ndash; Child) giữa các phiếu sự cố trong cùng một bảng dữ liệu.

#### Câu 62. Mục đích nghiệp vụ cốt lõi của việc gom nhóm các phiếu con vào Phiếu Cha thông qua `parent_ticket_id` là gì?

- **A.** Để tự động xóa hết các phiếu con.
- **B.** Xử lý sự cố kỹ thuật diện rộng (Major Incident): Khi hàng chục sinh viên cùng báo 1 lỗi, KTV chỉ cần tập trung xử lý trên 1 phiếu cha duy nhất và đóng các phiếu con dưới dạng trùng lặp.
- **C.** Để tính tiền thưởng gấp đôi cho KTV.
- **D.** Để giấu thông tin sự cố khỏi ban giám hiệu.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tiết kiệm hàng trăm giờ lao động: Thay vì phải trả lời riêng rẽ 50 người, KTV xử lý dứt điểm trên phiếu cha, các phiếu con tự động được cập nhật giải pháp đồng bộ.

#### Câu 63. Trên giao diện xử lý của KTV, chức năng đóng phiếu trùng lặp được kích hoạt thông qua giao diện nào?

- **A.** Nút gửi email cá nhân.
- **B.** Hộp thoại Modal đóng trùng lặp (`closeDuplicateModal`) có ô chọn Phiếu sự cố gốc (`parent_ticket_id`).
- **C.** Gõ lệnh trên Terminal Linux.
- **D.** Bấm phím tắt Ctrl + Alt + Delete.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Modal chuyên dụng hiển thị danh sách các phiếu cha đang xử lý kèm mã vé và tiêu đề sự cố giúp KTV chọn nhanh chóng và chính xác.

#### Câu 64. Điều kiện lọc bắt buộc đối với danh sách 'Phiếu Cha' hiển thị trong Modal đóng trùng lặp là gì?

- **A.** Chỉ hiển thị các phiếu đã đóng từ năm ngoái.
- **B.** Chỉ cho phép chọn các phiếu đang trong trạng thái xử lý thực sự (`status IN ('assigned', 'in_progress')`) và không được chọn chính phiếu đang xét làm cha của chính nó.
- **C.** Hiển thị tất cả mọi dòng trong CSDL.
- **D.** Chỉ hiển thị các phiếu có điểm 5 sao.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn hóa dữ liệu theo ITIL: Phiếu cha bắt buộc phải đang mở để việc gom nhóm có ý nghĩa nghiệp vụ thực tế, đồng thời loại trừ nguy cơ vòng lặp vô tận (Circular Reference `id = parent_ticket_id`).

#### Câu 65. Khi KTV thực hiện đóng một phiếu con dưới dạng trùng lặp, những trường dữ liệu nào trong bảng `tickets` được cập nhật?

- **A.** Chỉ cập nhật mỗi cột `status`.
- **B.** Cập nhật `status = 'closed'`, `closed_reason = 'duplicate'`, `parent_ticket_id = :parent_id` và `closed_at = NOW()`.
- **C.** Xóa trắng toàn bộ thông tin người gửi.
- **D.** Đổi mã sinh viên thành số 0.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ghi nhận đầy đủ vết liên kết: phiếu đã đóng, lý do là trùng lặp, trỏ đến phiếu cha nào và thời điểm đóng chính xác khi nào.

#### Câu 66. Trên giao diện chi tiết phiếu (`tickets/show.php`), khi một sinh viên mở xem một phiếu đã bị gom nhóm trùng lặp, giao diện hiển thị thông báo gì?

- **A.** Thông báo lỗi trang web 404.
- **B.** Khối thông báo màu xanh lam: 'ℹ️ Phiếu này đã được gom nhóm vào sự cố gốc #{parent_ticket_code} &ndash; Bấm để xem tiến độ phiếu cha'.
- **C.** Thông báo sinh viên bị đình chỉ học.
- **D.** Không hiển thị thông tin gì.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính liên thông UX xuất sắc: Sinh viên click vào liên kết là chuyển ngay sang xem tiến độ xử lý của sự cố gốc, nắm bắt được giải pháp của nhà trường.

#### Câu 67. Cơ chế 'Tạm dừng đồng hồ SLA' (Pause SLA Clock) được kích hoạt khi phiếu hỗ trợ rơi vào trạng thái nào?

- **A.** Trạng thái `new`
- **B.** Trạng thái `waiting_requester` (Chờ người dùng phản hồi / bổ sung thông tin)
- **C.** Trạng thái `in_progress`
- **D.** Trạng thái `assigned`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn ITSM quốc tế: Khi bóng trách nhiệm thuộc về phía người dùng (KTV đang đợi ảnh chụp lỗi hoặc thông tin xác minh), đồng hồ cam kết SLA của KTV phải tạm dừng.

#### Câu 68. Tại sao cơ chế Tạm dừng SLA (Pause SLA Clock) khi `waiting_requester` lại là tiêu chí sống còn để đánh giá công bằng hiệu suất của KTV?

- **A.** Vì nếu không tạm dừng, sinh viên đi ngủ hoặc 3 ngày sau mới trả lời tin nhắn sẽ khiến phiếu bị quá hạn (Overdue SLA) oan và KTV bị trừ điểm thi đua một cách bất công.
- **B.** Vì máy chủ sẽ bị nóng nếu đồng hồ chạy liên tục.
- **C.** Để KTV có thêm thời gian đi chơi.
- **D.** Do giới hạn của đồng hồ hệ điều hành.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo vệ công bằng cho người lao động: KTV chỉ chịu trách nhiệm về thời gian họ thực sự làm việc, không phải gánh chịu thời gian trễ do người dùng phản hồi chậm.

#### Câu 69. Trên thanh bên (Sidebar) của giao diện chi tiết phiếu (`tickets/show.php`), huy hiệu SLA hiển thị trạng thái gì khi phiếu ở `waiting_requester`?

- **A.** Huy hiệu đỏ 'QUÁ HẠN SLA'
- **B.** Huy hiệu màu tím nổi bật: '⏸️ ĐỒNG HỒ SLA TẠM DỪNG (PAUSED &ndash; Chờ phản hồi)'
- **C.** Huy hiệu xanh 'ĐÃ HOÀN TẤT'
- **D.** Không hiển thị gì

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Minh bạch trực quan hóa: cả người dùng và kỹ thuật viên đều nhìn thấy rõ ràng đồng hồ cam kết đang ở trạng thái tạm dừng.

#### Câu 70. Trong câu lệnh SQL truy vấn các phiếu bị quá hạn SLA (`isOverdueSLA`), trạng thái `waiting_requester` được xử lý ra sao?

- **A.** Vẫn bị tính là quá hạn bình thường.
- **B.** Bị LOẠI TRỪ hoàn toàn khỏi điều kiện quá hạn (`WHERE status != 'waiting_requester' AND NOW() > due_at`), không phát chuông cảnh báo quá hạn oan.
- **C.** Bị chuyển thành phiếu mới.
- **D.** Bị xóa khỏi CSDL.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bộ lọc backend nhất quán với giao diện frontend: loại trừ triệt để phiếu đang tạm dừng khỏi các thống kê phạt quá hạn.

#### Câu 71. Khi người dùng gửi tin nhắn phản hồi mới vào phiếu đang ở `waiting_requester`, điều gì xảy ra với đồng hồ SLA?

- **A.** Đồng hồ SLA bị đặt lại về 0 giờ.
- **B.** Trạng thái phiếu tự động chuyển về `in_progress` và đồng hồ SLA được kích hoạt chạy tiếp tục phần thời gian còn lại.
- **C.** Đồng hồ SLA dừng vĩnh viễn.
- **D.** Phiếu tự động đóng lại.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trigger chuyển trạng thái tự động: khi người dùng đã trả lời, quả bóng trách nhiệm quay lại với KTV và đồng hồ tính giờ tiếp tục đếm.

#### Câu 72. Nếu KTV đóng một phiếu cha (Major Incident) chuyển sang `resolved`, điều gì xảy ra với các phiếu con đã gom nhóm?

- **A.** Các phiếu con tự động mở lại làm việc từ đầu.
- **B.** Các phiếu con đã đóng với lý do `duplicate` được hoàn tất đồng bộ và sinh viên sở hữu phiếu con có thể vào xem kết quả giải quyết của phiếu cha.
- **C.** Các phiếu con bị xóa sạch.
- **D.** Hệ thống trừ điểm của các sinh viên tạo phiếu con.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giải pháp đồng bộ toàn diện: hoàn tất một sự cố gốc giải quyết triệt để vấn đề cho hàng chục người dùng liên quan.

#### Câu 73. Trong bảng `tickets`, giá trị mặc định của cột `parent_ticket_id` khi tạo phiếu mới là gì?

- **A.** 0
- **B.** `NULL` (phiếu độc lập, không phải phiếu con)
- **C.** 1
- **D.** -1

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giá trị `NULL` biểu thị phiếu là một sự cố độc lập bình thường, chỉ nhận ID khi được gom nhóm vào một phiếu cha cụ thể.

#### Câu 74. Một phiếu sự cố gốc (Phiếu cha) có thể chứa tối đa bao nhiêu phiếu con liên kết vào nó?

- **A.** Chỉ tối đa 1 phiếu con.
- **B.** Không giới hạn số lượng phiếu con (Quan hệ 1 &ndash; N: Một phiếu sự cố gốc có thể có hàng trăm phiếu con trỏ về qua `parent_ticket_id`).
- **C.** Tối đa 5 phiếu con.
- **D.** Bắt buộc đúng 10 phiếu con.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mô hình dữ liệu quan hệ 1-N linh hoạt, đáp ứng hoàn hảo cho các sự cố diện rộng quy mô từ vài chục đến hàng ngàn lượt phản ánh.

#### Câu 75. Hành động gom nhóm phiếu trùng lặp của KTV có bị ghi nhận vào bảng `ticket_replies` không?

- **A.** Không ghi nhận gì.
- **B.** Có; hệ thống tự động sinh một bản ghi phản hồi hệ thống (System Note) thông báo phiếu đã được gom nhóm vào sự cố gốc #{parent_code}.
- **C.** Ghi vào tệp tin trên desktop của KTV.
- **D.** Gửi tin nhắn SMS cho bảo vệ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lịch sử trao đổi (Audit Trail) được cập nhật tự động, giúp bất kỳ ai mở phiếu ra xem đều nắm được lý do và tiến trình đóng phiếu.

#### Câu 76. Khi KTV mở modal gom nhóm, nếu KTV cố tình chọn chính mã phiếu hiện tại làm phiếu cha thì hệ thống xử lý thế nào?

- **A.** Chấp nhận và làm sập vòng lặp CSDL.
- **B.** Hệ thống loại trừ ID của chính phiếu đang xét khỏi danh sách dropdown (`WHERE id != :current_id`), chặn đứng nguy cơ lỗi tham chiếu vòng tròn.
- **C.** Xóa tài khoản của KTV.
- **D.** Tự động đổi tên phiếu.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tầng phòng thủ dữ liệu chặt chẽ ở cả câu truy vấn SQL và giao diện người dùng, ngăn chặn lỗi đệ quy vô hạn.

#### Câu 77. Trong bảng `tickets`, cột `closed_reason` nhận những giá trị chuẩn nào để giải thích nguyên nhân kết thúc phiếu?

- **A.** `'xong'`, `'chua'`
- **B.** `'completed'`, `'duplicate'`, `'invalid_report_by_ktv'`, `'cancelled_by_requester'`, `'auto_closed_timeout'`
- **C.** `'1'`, `'2'`, `'3'`
- **D.** `'red'`, `'green'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng mã lý do đóng phiếu chuẩn hóa giúp báo cáo thống kê cuối kỳ phân tích chính xác tỷ lệ hoàn thành, tỷ lệ báo sai và tỷ lệ trùng lặp.

#### Câu 78. Động cơ SLA tính toán lại mốc `due_at` sau khi phiếu thoát khỏi trạng thái tạm dừng `waiting_requester` bằng cách nào?

- **A.** Cộng thêm đúng số phút mà phiếu đã ở trạng thái tạm dừng vào hạn chót `due_at` ban đầu.
- **B.** Xóa hạn chót cũ và cho KTV thêm 1 tháng.
- **C.** Đặt hạn chót về thời điểm hiện tại.
- **D.** Không thay đổi gì.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo toàn chính xác số phút cam kết ban đầu: thời gian tạm dừng bao nhiêu phút thì hạn chót được nới lùi lại bấy nhiêu phút tương ứng.

#### Câu 79. Khi một sự cố diện rộng xảy ra, sinh viên có cần phải tạo thêm phiếu hỗ trợ nếu thấy trên trang chủ đã có Banner cảnh báo không?

- **A.** Vẫn bắt buộc phải tạo 10 phiếu nữa.
- **B.** Không cần; Banner thông báo nêu rõ đội ngũ kỹ thuật đang xử lý sự cố diện rộng, sinh viên chỉ cần theo dõi cập nhật trên banner mà không phải tạo thêm vé.
- **C.** Phải đến trường nộp đơn giấy.
- **D.** Bắt buộc phải gọi điện thoại.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giảm tải tối đa cho hạ tầng Helpdesk và hàng đợi xử lý sự cố trong các đợt cao điểm kỹ thuật của toàn trường.

#### Câu 80. Sự kết hợp giữa `parent_ticket_id`, Modal đóng trùng lặp và Pause SLA clock do Thanh Ngân xây dựng chứng minh điều gì về trình độ kiến trúc của nhóm?

- **A.** Chỉ là một bài tập làm thêm sơ sài.
- **B.** Thể hiện sự am hiểu sâu sắc về tiêu chuẩn quốc tế Quản trị dịch vụ CNTT (ITIL v4 Service Desk) và năng lực giải quyết các bài toán vận hành phức tạp của thế giới thực.
- **C.** Làm cho hệ thống chạy chậm hơn.
- **D.** Không có giá trị thực tiễn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Được các giảng viên đánh giá là điểm sáng kỹ thuật xuất sắc nhất trong toàn bộ đồ án, khẳng định chất lượng học thuật và tay nghề của Nhóm 1.

## <a id="chuyen-de-5"></a>5. Phân Tích Các Câu Lệnh Truy Vấn SQL Chuyên Sâu Của Trưởng Nhóm Thanh Ngân

#### Câu 81. Trong tệp truy vấn cá nhân của Thanh Ngân (`ThanhNgan_truy_van_ca_nhan.sql`), Câu truy vấn 1 thực hiện phép nối giữa bao nhiêu bảng dữ liệu?

- **A.** Chỉ 1 bảng `tickets` đơn lẻ.
- **B.** Nối 6 bảng dữ liệu: `tickets`, `users` (người gửi), `categories`, `priorities`, `classes` (lớp sinh hoạt), `courses` (môn học) và `users` (KTV tiếp nhận).
- **C.** Nối 20 bảng.
- **D.** Không dùng phép JOIN nào.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn đa bảng chuẩn mực giúp Bàn làm việc KTV hiển thị đầy đủ ngữ cảnh sự cố từ thông tin cá nhân người gửi đến mã lớp và môn học liên quan.

#### Câu 82. Mục đích nghiệp vụ của Câu truy vấn SQL số 1 trong báo cáo cá nhân của Thanh Ngân là gì?

- **A.** Xóa các phiếu đã hoàn thành.
- **B.** Bàn làm việc KTV &ndash; Lấy danh sách hàng đợi các phiếu đang xử lý (`status IN ('new', 'in_progress', 'waiting_requester')`) sắp xếp theo hạn SLA (`due_at ASC`) để ưu tiên giải quyết sự cố sắp hết hạn.
- **C.** Tính điểm thi kết thúc học phần.
- **D.** Đổi mật khẩu của KTV.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn cốt lõi vận hành hàng đợi xử lý sự cố hàng ngày của đội ngũ kỹ thuật viên.

#### Câu 83. Hàm `COALESCE(c.class_code, 'Không gắn')` trong câu truy vấn số 1 của Thanh Ngân xử lý tình huống nào?

- **A.** Chuyển mã lớp thành chữ in hoa.
- **B.** Xử lý khiếu nại không gắn với lớp sinh hoạt cụ thể (`class_id IS NULL`), thay thế giá trị NULL bằng chuỗi 'Không gắn' thân thiện với người dùng.
- **C.** Đếm số lượng sinh viên trong lớp.
- **D.** Xóa lớp sinh hoạt đó.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `COALESCE()` bảo đảm giao diện không bị in ra khoảng trống rỗng hoặc lỗi `null`, nâng cao tính chỉn chu của sản phẩm phần mềm.

#### Câu 84. Trong Câu truy vấn SQL số 2 của Thanh Ngân, cấu trúc câu lệnh nào được dùng để phân loại giữa Phản hồi công khai và Ghi chú nội bộ KTV?

- **A.** `IF ELSE` trong mã PHP.
- **B.** Biểu thức điều kiện trong SQL: `CASE WHEN r.is_internal = 1 THEN '🔒 Ghi chú nội bộ (Private Note)' ELSE '💬 Phản hồi công khai (Public)' END AS Phân Loại Trao Đổi`.
- **C.** `WHERE is_internal = 1`
- **D.** `ORDER BY is_internal`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng `CASE WHEN` trực tiếp trong câu lệnh SQL giúp tầng cơ sở dữ liệu tự động gắn nhãn định dạng dữ liệu trước khi chuyển giao cho tầng View hiển thị.

#### Câu 85. Câu truy vấn SQL số 2 của Thanh Ngân phục vụ màn hình nghiệp vụ nào?

- **A.** Màn hình đăng nhập hệ thống.
- **B.** Xem chi tiết toàn bộ lịch sử trao đổi của một phiếu hỗ trợ theo mã vé (`WHERE t.ticket_code = :code ORDER BY r.created_at ASC`).
- **C.** Màn hình danh sách giảng viên.
- **D.** Màn hình cấu hình ngày nghỉ lễ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trích xuất dòng thời gian tương tác (Timeline) hoàn chỉnh của phiếu, giúp KTV và người dùng theo dõi mạch lạc từng bước trao đổi.

#### Câu 86. Câu truy vấn SQL số 3 trong tệp của Thanh Ngân giải quyết bài toán phức tạp nào khi sinh viên báo lỗi học tập/LMS?

- **A.** Tính tiền học bổng kỳ 1 cho sinh viên.
- **B.** Định vị 3 tầng liên kết: (1) Lớp Sinh hoạt hành chính của sinh viên, (2) Môn học bị lỗi, và (3) Xác định chính xác Lớp Học phần trong kỳ cùng Giảng viên phụ trách duy nhất để liên hệ phối hợp xử lý.
- **C.** Xóa bài tập của cả lớp.
- **D.** Cài đặt phần mềm diệt virus cho sinh viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiến trúc JOIN 5 bảng đỉnh cao: kết nối sinh viên với lớp sinh hoạt, môn học và phân công giảng dạy để tìm ra đúng giảng viên phụ trách giải quyết sự cố.

#### Câu 87. Đoạn mã `JOIN course_assignments ca ON ca.class_id = t.class_id AND ca.course_id = t.course_id` trong truy vấn số 3 của Thanh Ngân thể hiện điều gì?

- **A.** Nối bảng sai cú pháp.
- **B.** Phép nối đa điều kiện (Multi-column Join condition) bảo đảm khớp chính xác cả lớp học và môn học của học kỳ đó.
- **C.** Phép nối chéo vô tận.
- **D.** Lệnh xóa bản ghi phân công.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kỹ thuật JOIN nâng cao trên khóa ngoại tổ hợp, bảo đảm truy xuất đúng lớp học phần mà sinh viên đó đang theo học.

#### Câu 88. Câu truy vấn SQL số 4 của Thanh Ngân phục vụ báo cáo quản trị quan trọng nào?

- **A.** Báo cáo tiền điện của trường.
- **B.** Báo cáo Thống kê Hiệu suất Phân hệ Kỹ thuật viên (Agent Performance Report): Đếm tổng phiếu tiếp nhận, số phiếu giải quyết xong, số phiếu đang xử lý và tính điểm CSAT trung bình.
- **C.** Báo cáo danh sách sinh viên khen thưởng.
- **D.** Báo cáo số lượng bàn ghế hỏng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cung cấp bức tranh toàn diện về năng suất và chất lượng phục vụ của từng kỹ thuật viên cho Ban Giám đốc trung tâm CNTT.

#### Câu 89. Trong truy vấn số 4 của Thanh Ngân, kỹ thuật tính toán nào được dùng để đếm số phiếu đã giải quyết và số phiếu đang xử lý trong cùng một câu lệnh?

- **A.** Chạy 10 câu lệnh SELECT riêng biệt.
- **B.** Kỹ thuật Conditional Aggregation: `SUM(CASE WHEN t.status IN ('resolved', 'closed') THEN 1 ELSE 0 END)` và `SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END)`.
- **C.** Dùng vòng lặp for trong PHP.
- **D.** Đếm thủ công bằng mắt.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kỹ thuật tổng hợp có điều kiện (Conditional Aggregation) là đỉnh cao tối ưu hóa SQL, gom nhiều phép tính thống kê phức tạp vào đúng 1 lượt quét bảng duy nhất.

#### Câu 90. Biểu thức `COALESCE(ROUND(AVG(tr.score), 2), 5.00)` trong truy vấn số 4 của Thanh Ngân có ý nghĩa gì?

- **A.** Làm tròn điểm thi môn Toán.
- **B.** Tính điểm CSAT trung bình làm tròn 2 chữ số thập phân; nếu KTV mới chưa có đánh giá nào (`NULL`) thì mặc định gán điểm khởi đầu là 5.00 sao.
- **C.** Trừ 5 điểm của KTV.
- **D.** Tự động nhân điểm CSAT với 2.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Xử lý dữ liệu khuyết thiếu chuyên nghiệp, bảo đảm báo cáo thống kê luôn có giá trị số hiển thị sạch đẹp, không bị lỗi tính toán.

#### Câu 91. Tại sao các câu truy vấn của Thanh Ngân đều sử dụng mệnh đề `SET NAMES utf8mb4;` ngay ở dòng đầu tệp?

- **A.** Để làm cho tệp dài hơn.
- **B.** Thiết lập kết nối phiên làm việc sử dụng chuẩn mã hóa UTF-8 4-byte, bảo đảm hiển thị tiếng Việt có dấu chuẩn xác 100% khi chạy script.
- **C.** Do MySQL bắt buộc phải viết.
- **D.** Để tăng tốc độ mạng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khắc phục triệt để lỗi hiển thị font tiếng Việt khi nạp câu truy vấn trên các máy chủ cơ sở dữ liệu khác nhau.

#### Câu 92. Trong phương thức `TicketRepository::findById($id)`, cú pháp PDO nào được Thanh Ngân sử dụng để nạp một bản ghi duy nhất?

- **A.** `$stmt->fetchAll()`
- **B.** `$stmt->fetch(PDO::FETCH_ASSOC)`
- **C.** `$stmt->rowCount()`
- **D.** `$stmt->nextRowset()`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `fetch()` chỉ nạp đúng 1 bản ghi duy nhất vào bộ nhớ, tối ưu hóa tài nguyên hơn nhiều so với việc nạp mảng danh sách `fetchAll()`.

#### Câu 93. Để cập nhật giải pháp khắc phục sự cố và chuyển trạng thái phiếu sang `resolved`, câu lệnh SQL Prepared Statement được viết như thế nào?

- **A.** `UPDATE tickets SET status = 'resolved' WHERE 1=1`
- **B.** `UPDATE tickets SET status = 'resolved', resolved_at = NOW(), resolved_by = :agent_id WHERE id = :ticket_id`
- **C.** `INSERT INTO tickets VALUES (...)`
- **D.** `DELETE FROM tickets WHERE id = :ticket_id`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cập nhật đồng bộ: trạng thái resolved, thời điểm giải quyết và danh tính KTV giải quyết sự cố.

#### Câu 94. Khi thực hiện chèn một phản hồi mới vào bảng `ticket_replies`, những tham số bắt buộc nào phải được truyền qua `:named_params`?

- **A.** Chỉ cần truyền nội dung tin nhắn.
- **B.** `:ticket_id`, `:user_id`, `:message`, `:is_internal`, `:attachment_path`
- **C.** Mật khẩu của người dùng.
- **D.** Địa chỉ IP máy chủ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đầy đủ các trường định danh để gắn phản hồi đúng phiếu, đúng tác giả và đúng phân loại công khai/nội bộ.

#### Câu 95. Chỉ mục (Index) nào được Thanh Ngân tạo trong bảng `tickets` để tối ưu hóa việc tra cứu phiếu theo mã code và trạng thái?

- **A.** `idx_ticket_lookup (ticket_code, status)`
- **B.** `idx_title`
- **C.** `idx_description`
- **D.** `idx_created_at`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ mục giúp tăng tốc độ mở xem chi tiết phiếu qua mã code từ vài giây xuống còn dưới 5 mili-giây.

#### Câu 96. Tại sao hệ thống sử dụng mệnh đề `LEFT JOIN users u_agent ON t.assigned_to = u_agent.id` thay vì dùng `INNER JOIN` trong câu truy vấn số 1?

- **A.** Do gõ nhầm lệnh SQL.
- **B.** Vì các phiếu mới tạo (`status = 'new'`) chưa được phân công người xử lý (`assigned_to IS NULL`), nếu dùng INNER JOIN thì các phiếu mới này sẽ bị biến mất khỏi hàng đợi.
- **C.** Để câu lệnh chạy chậm hơn.
- **D.** Vì bảng users không có khóa chính.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiến thức CSDL căn bản và sắc sảo: Dùng LEFT JOIN để không làm rơi rụng các phiếu mới đang chờ KTV tiếp nhận.

#### Câu 97. Khi KTV thực hiện chuyển trạng thái phiếu sang `in_progress`, hệ thống ghi nhận thời điểm bắt đầu xử lý vào cột nào?

- **A.** `created_at`
- **B.** `started_at` (hoặc `updated_at = NOW()`)
- **C.** `due_at`
- **D.** `closed_at`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lưu lại mốc thời gian bắt tay vào việc thực tế của kỹ thuật viên phục vụ phân tích năng suất.

#### Câu 98. Nếu Quản trị viên muốn xem tất cả các phiếu thuộc về một môn học cụ thể (ví dụ môn Lập trình Web `WEB101`), câu lệnh SQL sử dụng mệnh đề lọc nào?

- **A.** `WHERE crs.course_code = 'WEB101'`
- **B.** `WHERE t.title = 'WEB101'`
- **C.** `WHERE t.id = 'WEB101'`
- **D.** `WHERE t.status = 'WEB101'`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lọc chính xác theo mã môn học chuẩn trong bảng danh mục `courses`.

#### Câu 99. Phương thức `TicketRepository::getOverdueCount()` đếm số phiếu quá hạn đang mở bằng câu lệnh SQL nào?

- **A.** `SELECT COUNT(*) FROM tickets WHERE status NOT IN ('resolved', 'closed', 'waiting_requester') AND NOW() > due_at`
- **B.** `SELECT COUNT(*) FROM tickets WHERE status = 'closed'`
- **C.** `SELECT * FROM tickets`
- **D.** `DELETE FROM tickets WHERE NOW() > due_at`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn đếm chính xác số phiếu đang trễ hạn SLA, loại trừ các phiếu đã giải quyết, đã đóng và phiếu đang tạm dừng chờ phản hồi.

#### Câu 100. Tổng kết lại, dấu ấn nổi bật nhất của Trưởng nhóm Phạm Thị Thanh Ngân (224001736) trong Đồ án Helpdesk Nhóm 1 là gì?

- **A.** Chỉ làm nhiệm vụ điểm danh các thành viên trong nhóm.
- **B.** Kiến trúc sư trưởng định hình Clean MVC thuần PHP 8, thiết kế CSDL 14 bảng chuẩn 3NF, phát triển Bàn làm việc KTV và Thuật toán Triage điều phối thông minh chuẩn ITIL v4, đưa Đồ án trở thành một hệ thống phần mềm hoàn chỉnh, an toàn và chuyên nghiệp nhất.
- **C.** Chỉ viết tài liệu báo cáo trang bìa.
- **D.** Không tham gia vào việc viết mã nguồn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phạm Thị Thanh Ngân dẫn dắt nhóm đạt kết quả xuất sắc với 21% đóng góp cốt lõi, làm chủ toàn bộ kiến trúc lõi và xây dựng thành công giải pháp ITSM Helpdesk cho Trường Đại học Thủ Đô Hà Nội.

---

## <a id="bang-dap-an"></a>📊 BẢNG TỔNG HỢP ĐÁP ÁN 100 CÂU HỎI (PHẠM THỊ THANH NGÂN)

| Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | B | **21** | B | **41** | B | **61** | B | **81** | B |
| **2** | B | **22** | B | **42** | B | **62** | B | **82** | B |
| **3** | B | **23** | B | **43** | B | **63** | B | **83** | B |
| **4** | B | **24** | B | **44** | B | **64** | B | **84** | B |
| **5** | B | **25** | B | **45** | B | **65** | B | **85** | B |
| **6** | A | **26** | B | **46** | B | **66** | B | **86** | B |
| **7** | A | **27** | B | **47** | B | **67** | B | **87** | B |
| **8** | B | **28** | B | **48** | A | **68** | A | **88** | B |
| **9** | B | **29** | B | **49** | B | **69** | B | **89** | B |
| **10** | A | **30** | B | **50** | B | **70** | B | **90** | B |
| **11** | B | **31** | B | **51** | B | **71** | B | **91** | B |
| **12** | B | **32** | B | **52** | B | **72** | B | **92** | B |
| **13** | A | **33** | B | **53** | B | **73** | B | **93** | B |
| **14** | B | **34** | B | **54** | B | **74** | B | **94** | B |
| **15** | B | **35** | B | **55** | B | **75** | B | **95** | A |
| **16** | B | **36** | B | **56** | B | **76** | B | **96** | B |
| **17** | B | **37** | B | **57** | B | **77** | B | **97** | B |
| **18** | B | **38** | B | **58** | A | **78** | A | **98** | A |
| **19** | B | **39** | B | **59** | B | **79** | B | **99** | A |
| **20** | B | **40** | B | **60** | B | **80** | B | **100** | B |

---

# BỘ 100 CÂU HỎI TRẮC NGHIỆM CHUYÊN SÂU &ndash; NGUYỄN ÁNH NHI (224001742)

> **Thành viên phụ trách:** Nguyễn Ánh Nhi (MSV: 224001742 &ndash; Lớp: TUD D2024)
> **Phân hệ chuyên trách:** Cổng Sinh Viên, Quản Lý Danh Mục, Mức Độ Ưu Tiên & Nghiệm Thu Đánh Giá CSAT 5 Sao
> **Bảng CSDL cốt lõi:** `ticket_ratings`, `categories`, `priorities`, `student_assignment_submissions`
> **Model phụ trách:** `Rating.php`, `Category.php`, `Priority.php`

---

## 📑 MỤC LỤC 5 DẠNG CHUYÊN ĐỀ (100 CÂU HỎI MỚI)

- **[Chuyên đề 1: 1. Cổng Sinh Viên: Giao diện Nộp Bài, Kiểm soát Deadline & Quản lý Tệp Buổi học](#chuyen-de-1)** (20 câu)
- **[Chuyên đề 2: 2. Cổng Sinh Viên: Tạo Phiếu Hỗ Trợ, Cứu Hộ Buổi Học & Chống Trùng Lặp 3 Lớp](#chuyen-de-2)** (20 câu)
- **[Chuyên đề 3: 3. Nghiệp Vụ Nghiệm Thu CSAT 5 Sao, Đóng Phiếu & Cơ Chế Tự Động Đóng (Auto-Close)](#chuyen-de-3)** (20 câu)
- **[Chuyên đề 4: 4. Quản Lý Danh Mục Dịch Vụ (categories) & Động Cơ Ưu Tiên SLA (priorities)](#chuyen-de-4)** (20 câu)
- **[Chuyên đề 5: 5. Phân Tích 5 Test Cases Chuyên Sâu & Các Câu Lệnh Truy Vấn SQL Của Ánh Nhi](#chuyen-de-5)** (20 câu)
- **[Bảng Đáp Án Chi Tiết 100 Câu](#bang-dap-an)**

---

## <a id="chuyen-de-1"></a>1. Cổng Sinh Viên: Giao diện Nộp Bài, Kiểm soát Deadline & Quản lý Tệp Buổi học

#### Câu 1. Trên giao diện Cổng Sinh viên, hệ thống sử dụng biểu thức PHP nào để kiểm tra một bài tập buổi học đã quá hạn nộp (Deadline) hay chưa?

- **A.** `date('Y-m-d') == $lesson['due_date']`
- **B.** `time() > strtotime($lesson['due_date'])`
- **C.** `count($lesson) > 10`
- **D.** `$_SESSION['user_id'] == $lesson['id']`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống so sánh mốc thời gian Unix hiện tại `time()` với thời điểm kết thúc nộp bài được chuyển đổi qua `strtotime($lesson['due_date'])`. Nếu `time() > strtotime(...)`, buổi học được đánh dấu quá hạn.

#### Câu 2. Khi một bài tập buổi học đã vượt quá hạn chót (Deadline), giao diện Cổng Sinh viên xử lý nút nộp bài như thế nào?

- **A.** Vẫn cho nộp bình thường và tự động cộng 2 điểm khuyến khích.
- **B.** Vô hiệu hóa form nộp bài (disabled), ẩn nút tải tệp lên và hiển thị thông báo hướng dẫn tạo phiếu 'Cứu hộ buổi học'.
- **C.** Xóa vĩnh viễn tài khoản sinh viên khỏi trường.
- **D.** Tự động gửi email cảnh cáo tới phụ huynh sinh viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khi quá hạn deadline, form tải bài bị khóa và hệ thống hiển thị nút chuyển hướng sinh viên tạo vé 'Cứu hộ buổi học' để xin Giảng viên mở lại hạn nếu có lý do chính đáng.

#### Câu 3. Tệp bài tập thực hành do sinh viên tải lên được lưu trữ an toàn tại thư mục nào trên máy chủ?

- **A.** Thư mục `C:\Windows\Temp\`
- **B.** Thư mục `uploads/assignments/` được phân tách ngoài thư mục gốc và có tệp `.htaccess` bảo vệ chống thực thi mã nguồn.
- **C.** Thư mục `public/css/`
- **D.** Lưu trực tiếp dưới dạng chuỗi nhị phân Base64 trong bảng `users`.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tệp nộp bài được phân vùng riêng tại `uploads/assignments/`, tách biệt khỏi mã nguồn thực thi để chống tấn công tải lên mã độc (Web Shell).

#### Câu 4. Quy tắc đặt tên tệp đính kèm bài nộp của sinh viên được Nguyễn Ánh Nhi thiết kế theo cấu trúc chuẩn hóa nào nhằm tránh xung đột tên tệp?

- **A.** `file_{rand()}.txt`
- **B.** `sub_{student_id}_lesson{lesson_id}_{timestamp}.{ext}`
- **C.** `baitap_{ten_sinh_vien}.doc`
- **D.** `document_{id}.pdf`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tiền tố `sub_{student_id}_lesson{lesson_id}_{timestamp}.{ext}` đảm bảo tính duy nhất tuyệt đối, thể hiện rõ sinh viên nào nộp cho bài học nào tại thời điểm chính xác nào.

#### Câu 5. Nếu sinh viên phát hiện bài tập đã nộp bị sai sót nhưng vẫn còn trong thời hạn nộp bài (trước Deadline), sinh viên có quyền làm gì?

- **A.** Không được phép thay đổi trong mọi trường hợp.
- **B.** Được quyền tải lên tệp mới để ghi đè (cập nhật) bài làm, hệ thống tự động xóa tệp cũ và cập nhật lại thời điểm `submitted_at = NOW()`.
- **C.** Phải nộp phạt 50.000 VNĐ tại phòng tài vụ.
- **D.** Phải nhờ Quản trị viên xóa toàn bộ CSDL.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống cho phép nộp đè bài làm trước hạn chót (`NOW() <= due_date`), đảm bảo quyền lợi sửa bài của sinh viên mà không cần can thiệp thủ công.

#### Câu 6. Những định dạng tệp nào sau đây được bộ lọc kiểm tra hợp lệ của Nguyễn Ánh Nhi cho phép sinh viên nộp bài?

- **A.** Chỉ chấp nhận tệp thực thi `.exe`, `.bat`, `.cmd`.
- **B.** Các tệp tài liệu và nén chuẩn học thuật: `.zip`, `.rar`, `.pdf`, `.docx`, `.sql`, `.png`, `.jpg`.
- **C.** Chỉ chấp nhận tệp âm thanh `.mp3`.
- **D.** Chấp nhận tất cả mọi định dạng kể cả `.php`, `.sh`.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bộ lọc đuôi tệp chỉ cho phép các định dạng lưu trữ bài tập, mã nguồn nén hoặc tài liệu nộp chuẩn (`.zip`, `.rar`, `.pdf`, `.docx`, `.sql`), nghiêm cấm các đuôi tệp có khả năng thực thi script máy chủ.

#### Câu 7. Giới hạn dung lượng tối đa cho mỗi tệp bài nộp sinh viên tải lên hệ thống được thiết lập là bao nhiêu?

- **A.** 500 KB.
- **B.** 10 MB (kiểm tra qua `$_FILES['file']['size'] <= 10 * 1024 * 1024`).
- **C.** 10 GB.
- **D.** Không giới hạn dung lượng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống kiểm soát giới hạn 10MB cho mỗi tệp nộp bài nhằm tối ưu băng thông máy chủ trường và ngăn ngừa tấn công làm cạn kiệt ổ cứng (Denial of Service).

#### Câu 8. Bảng CSDL nào lưu trữ trực tiếp trạng thái nộp bài của sinh viên trong phân hệ do Nguyễn Ánh Nhi phụ trách?

- **A.** `student_assignment_submissions`
- **B.** `holidays`
- **C.** `system_settings`
- **D.** `password_resets`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `student_assignment_submissions` ghi nhận thông tin `student_id`, `lesson_id`, `file_path`, `submitted_at`, `score`, `feedback`.

#### Câu 9. Khi sinh viên đã nộp bài thành công nhưng giảng viên chưa chấm điểm, giá trị của cột `score` trong bảng `student_assignment_submissions` là gì?

- **A.** 0.0
- **B.** `NULL`
- **C.** -1.0
- **D.** 10.0

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khi chưa chấm, `score` mang giá trị `NULL` để phân biệt rạch ròi giữa sinh viên 'bị 0 điểm' và sinh viên 'đang chờ giảng viên chấm bài'.

#### Câu 10. Trên giao diện Cổng Sinh viên, trạng thái bài tập hiển thị nhãn màu xanh lá 'Đã nộp đúng hạn' khi thỏa mãn điều kiện nào?

- **A.** `submitted_at IS NOT NULL AND submitted_at <= due_date`
- **B.** `score >= 5.0`
- **C.** `lesson_id == 1`
- **D.** `status == 'closed'`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bài nộp đúng hạn có thời điểm nộp thực tế không vượt quá hạn chót của bài học (`submitted_at <= due_date`).

#### Câu 11. Hàm PHP nào được sử dụng để lấy phần mở rộng (đuôi tệp) nhằm kiểm tra danh sách trắng (whitelist) khi sinh viên tải tệp lên?

- **A.** `strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION))`
- **B.** `strlen($_FILES['file']['name'])`
- **C.** `explode('@', $_FILES['file']['name'])[0]`
- **D.** `substr($_FILES['file']['name'], 0, 3)`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm chuẩn và an toàn nhất là `pathinfo(..., PATHINFO_EXTENSION)` kết hợp `strtolower()` để đưa về chữ thường trước khi so sánh với mảng đuôi cho phép.

#### Câu 12. Để ngăn chặn việc người dùng khác cố tình đoán đường dẫn tệp để tải trộm bài làm của bạn học, hệ thống áp dụng biện pháp nào?

- **A.** Công khai toàn bộ thư mục cho bất kỳ ai có link đều tải được.
- **B.** Đổi tên tệp ngẫu nhiên theo timestamp kết hợp kiểm tra quyền trong session của sinh viên sở hữu trước khi truyền dữ liệu qua `readfile()`.
- **C.** Xóa tệp ngay sau khi nộp.
- **D.** Nén tệp bằng mật khẩu đặt theo số điện thoại.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểm tra xác thực phiên sinh viên trước khi đọc và tải tệp (chống lỗi bảo mật IDOR & Insecure Direct Downloads).

#### Câu 13. Khi sinh viên chưa nộp bài và thời gian còn lại trước Deadline là dưới 24 giờ, giao diện Cổng Sinh viên hiển thị cảnh báo gì?

- **A.** Báo động đỏ và cấm thi.
- **B.** Hộp thông báo nhắc nhở màu vàng cam hiển thị số giờ đếm ngược còn lại để sinh viên khẩn trương hoàn thành.
- **C.** Không hiển thị bất kỳ thông tin gì.
- **D.** Tự động gửi bài làm giả lập lên hệ thống.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giao diện UX thân thiện cung cấp đồng hồ đếm ngược và thanh tiến độ nhắc nhở hạn nộp bài trong 24 giờ cuối.

#### Câu 14. Trường `file_size` trong bảng `student_assignment_submissions` lưu trữ dung lượng tệp theo đơn vị nào?

- **A.** Megabyte (MB)
- **B.** Byte (số nguyên dương UNSIGNED INT)
- **C.** Kilobyte (KB)
- **D.** Gigabyte (GB)

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `file_size` được lưu chính xác theo số byte nguyên bản trả về từ `$_FILES['file']['size']`.

#### Câu 15. Trên giao diện chi tiết môn học của sinh viên, danh sách bài tập buổi học được hiển thị sắp xếp theo tiêu chí nào?

- **A.** Thứ tự ngẫu nhiên mỗi lần F5 tải lại trang.
- **B.** Sắp xếp tuần tự theo số thứ tự buổi học tăng dần (`lesson_number ASC`).
- **C.** Sắp xếp theo thứ tự bảng chữ cái tên giảng viên.
- **D.** Sắp xếp theo dung lượng tệp đề bài giảm dần.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giúp sinh viên theo dõi mạch kiến thức học phần từ Buổi 1, Buổi 2 đến các buổi cuối kỳ một cách khoa học.

#### Câu 16. Khi sinh viên tải tệp đính kèm đề bài do Giảng viên giao xuống máy tính, thuộc tính nào trong thẻ `<a>` giúp trình duyệt tải về thay vì mở xem trực tiếp?

- **A.** `target="_blank"`
- **B.** `download="ten_file_de_bai"`
- **C.** `rel="noopener"`
- **D.** `type="hidden"`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thuộc tính HTML5 `download` kích hoạt hành vi lưu tệp xuống thư mục Downloads của máy tính người dùng.

#### Câu 17. Nếu sinh viên nộp bài sau khi Deadline đã trôi qua 10 phút do được Giảng viên chấp thuận mở lại hạn, hệ thống gắn cờ trạng thái bài nộp là gì?

- **A.** `rejected`
- **B.** `late` (Nộp muộn nhưng được chấp thuận qua phiếu cứu hộ)
- **C.** `failed`
- **D.** `cancelled`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bài nộp sau hạn ban đầu nhưng trong khung giờ gia hạn của phiếu cứu hộ được ghi nhận trạng thái `late` minh bạch để giảng viên cân nhắc điểm thưởng/phạt.

#### Câu 18. Để tránh việc sinh viên gửi form rỗng không có tệp đính kèm khi bấm nút Nộp bài, mã HTML của thẻ input sử dụng thuộc tính nào?

- **A.** `readonly`
- **B.** `required`
- **C.** `disabled`
- **D.** `autocomplete="on"`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thuộc tính `required` phía client kết hợp kiểm tra `!empty($_FILES['file']['name'])` phía server bảo đảm form luôn có dữ liệu tệp hợp lệ.

#### Câu 19. Khi sinh viên nhấn nút 'Xem nhận xét của giảng viên', nội dung hiển thị được trích xuất từ cột nào trong bảng CSDL?

- **A.** `student_assignment_submissions.feedback`
- **B.** `tickets.description`
- **C.** `users.department`
- **D.** `courses.code`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cột `feedback` chứa chi tiết lời phê, phân tích lỗi sai và đánh giá của Giảng viên dành cho bài làm cụ thể của sinh viên.

#### Câu 20. Tại sao tệp bài tập tải lên không được phép giữ nguyên tên gốc của người dùng (ví dụ: `bai tap web.zip`) khi lưu vào đĩa cứng máy chủ?

- **A.** Vì hệ điều hành Linux/Windows sẽ bị hỏng font chữ nếu có dấu cách và ký tự tiếng Việt có dấu, đồng thời các sinh viên trùng tên tệp sẽ bị ghi đè mất bài của nhau.
- **B.** Vì máy chủ chỉ thích đọc tên bằng tiếng Anh.
- **C.** Vì tên tệp gốc làm tăng gấp đôi dung lượng lưu trữ.
- **D.** Vì tệp tiếng Việt sẽ tự động biến thành virus.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn hóa tên tệp giúp loại bỏ lỗi ký tự đặc biệt, dấu cách, chống tấn công Directory Traversal (`../../`) và bảo đảm không có 2 sinh viên nào bị ghi đè tệp của nhau.

## <a id="chuyen-de-2"></a>2. Cổng Sinh Viên: Tạo Phiếu Hỗ Trợ, Cứu Hộ Buổi Học & Chống Trùng Lặp 3 Lớp

#### Câu 21. Khi sinh viên mở form gửi yêu cầu hỗ trợ kỹ thuật (`tickets/create.php`), danh sách các dịch vụ hiển thị tại ô 'Danh mục' được truy vấn từ bảng nào?

- **A.** `priorities`
- **B.** `categories` với điều kiện lọc `is_active = 1`
- **C.** `roles`
- **D.** `classes`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sinh viên chỉ được chọn các danh mục dịch vụ đang được trường kích hoạt hoạt động (`is_active = 1`) như: Tài khoản SSO, Mạng Wi-Fi, LMS, v.v.

#### Câu 22. Trên giao diện tạo phiếu của sinh viên, sinh viên có được phép tự ý chọn đích danh Kỹ thuật viên (KTV) sẽ xử lý phiếu cho mình không?

- **A.** Được tự do chọn bất kỳ ai trong trường.
- **B.** Tuyệt đối không; trường `assigned_to` để trống (NULL), việc phân công hoàn toàn do Thuật toán điều phối Triage thông minh của hệ thống đảm nhận.
- **C.** Chỉ được chọn KTV cùng giới tính.
- **D.** Phải trả thêm phí 20.000 VNĐ để được chọn KTV.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn ITIL v4 quy định người dùng không được tự ý chỉ định kỹ thuật viên nhằm ngăn chặn hiện tượng dồn tải hoặc thiên vị; hệ thống tự động gán theo tải và ca trực.

#### Câu 23. Cơ chế chống gửi trùng lặp 3 lớp (3-Tier Anti-Duplicate Shield) ở phía Cổng sinh viên kiểm tra điều kiện gì ở Lớp 1?

- **A.** Kiểm tra xem sinh viên có đang nợ học phí hay không.
- **B.** Kiểm tra xem sinh viên đó có phiếu cùng danh mục (`category_id`) được tạo trong ngày hoặc đang ở trạng thái mở (`new`, `assigned`, `in_progress`, `waiting_requester`) hay không.
- **C.** Kiểm tra xem sinh viên có đổi ảnh đại diện hay không.
- **D.** Kiểm tra cấu hình RAM của máy tính sinh viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lớp 1 ngăn chặn hành vi gửi liên tiếp nhiều phiếu cùng một vấn đề khi phiếu trước đó vẫn đang trong quá trình được kỹ thuật viên xử lý.

#### Câu 24. Đối với loại phiếu 'Cứu hộ buổi học' (Lesson Rescue Ticket), Lớp 2 của bộ lọc chống trùng lặp kiểm tra trường thông tin nào?

- **A.** `tickets.priority_id`
- **B.** `tickets.lesson_id` &ndash; Chặn sinh viên tạo 2 phiếu cứu hộ cho cùng 1 buổi học cụ thể.
- **C.** `tickets.sla_hours`
- **D.** `tickets.closed_reason`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mỗi bài tập buổi học cụ thể chỉ cho phép sinh viên duy trì 1 yêu cầu cứu hộ hợp lệ; không được spam nhiều phiếu cho cùng một deadline.

#### Câu 25. Khi bộ lọc phát hiện sinh viên đang cố gửi một phiếu trùng lặp (Lớp 3), giao diện Cổng Sinh viên phản ứng như thế nào?

- **A.** Lập tức khóa vĩnh viễn tài khoản sinh viên.
- **B.** Hiển thị hộp thoại cảnh báo (Modal), ngăn chặn hành động gửi và cung cấp nút bấm dẫn trực tiếp tới xem tiến độ phiếu cũ.
- **C.** Tự động xóa sạch dữ liệu bài nộp của sinh viên.
- **D.** Chấp nhận tạo thêm một phiếu mới giống hệt.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống hướng dẫn sinh viên quay lại xem phiếu đang xử lý để tiếp tục trao đổi với KTV qua tính năng phản hồi (ticket_replies), thay vì tạo rác trên hệ thống.

#### Câu 26. Trong bao nhiêu phút đầu tiên kể từ khi tạo phiếu, sinh viên được hệ thống cho phép tự chỉnh sửa nội dung hoặc xóa phiếu nếu KTV chưa tiếp nhận (`status = 'new'`)?

- **A.** 5 phút.
- **B.** 15 phút đầu tiên (`TIMESTAMPDIFF(MINUTE, created_at, NOW()) <= 15`).
- **C.** 24 giờ.
- **D.** Bất cứ lúc nào trong kỳ học.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy chế hệ thống cấp quyền 'Grace Period' 15 phút đầu cho người gửi tự sửa lỗi chính tả hoặc hủy phiếu nếu phát hiện tự khắc phục được sự cố, giảm tải cho KTV.

#### Câu 27. Khi một phiếu đã được KTV chuyển sang trạng thái `in_progress` (Đang xử lý) hoặc đã qua 15 phút đầu, nút 'Sửa' và 'Xóa' của sinh viên sẽ như thế nào?

- **A.** Vẫn bấm được và tự động ghi đè dữ liệu của KTV.
- **B.** Bị ẩn hoàn toàn hoặc vô hiệu hóa; sinh viên chỉ có thể trao đổi qua khung bình luận/phản hồi.
- **C.** Chuyển thành nút gọi điện thoại cho hiệu trưởng.
- **D.** Bắt sinh viên phải trả tiền để mở khóa nút bấm.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đảm bảo tính toàn vẹn dữ liệu điều tra sự cố; khi KTV đã vào cuộc thì lịch sử phiếu không được phép bị người gửi tùy tiện xóa bỏ.

#### Câu 28. Khi sinh viên tạo phiếu hỗ trợ thông thường, mức độ ưu tiên (`priority_id`) được xác định ra sao?

- **A.** Sinh viên được tự ý chọn mức Khẩn cấp (URGENT) cho mọi yêu cầu.
- **B.** Tự động gán theo mức ưu tiên mặc định của danh mục (`categories.default_priority_code`) hoặc mức Bình thường (MED); sinh viên không có quyền chọn URGENT.
- **C.** Hệ thống quay xúc xắc ngẫu nhiên.
- **D.** Luôn luôn là mức Thấp (LOW).

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phân quyền RBAC nghiêm ngặt: Sinh viên không được phép chọn mức Khẩn cấp để tránh việc lạm quyền đẩy phiếu của mình lên đầu hàng đợi SLA một cách tùy tiện.

#### Câu 29. Mã định danh phiếu hỗ trợ (Ticket Code) hiển thị cho sinh viên theo dõi có định dạng cấu trúc chuẩn nào?

- **A.** `1`, `2`, `3`
- **B.** `HD-YYYY-MMDD-XXXXXX` (ví dụ: `HD-2026-2910-T0L001`)
- **C.** `MA_PHIEU_XYZ`
- **D.** `TICKET_RANDOM`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mã phiếu chuẩn hóa gồm tiền tố hệ thống `HD`, năm `YYYY`, ngày tháng `MMDD` và chuỗi hash nhận diện ngẫu nhiên chống đoán mã.

#### Câu 30. Trường dữ liệu nào trong bảng `tickets` liên kết trực tiếp một phiếu hỗ trợ với sinh viên là người tạo phiếu?

- **A.** `assigned_to`
- **B.** `requester_id` (khóa ngoại tham chiếu tới `users.id`)
- **C.** `resolved_by`
- **D.** `parent_ticket_id`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `requester_id` xác định chính xác danh tính người yêu cầu (sinh viên, giảng viên hoặc khách) tạo ra vé hỗ trợ.

#### Câu 31. Khi sinh viên gửi phản hồi mới (Ticket Reply) vào phiếu đang ở trạng thái `waiting_requester`, trạng thái phiếu tự động chuyển thành gì?

- **A.** `closed`
- **B.** `in_progress` (hoặc `assigned`) để KTV tiếp tục làm việc và đồng hồ SLA được kích hoạt chạy tiếp.
- **C.** `rejected`
- **D.** `waiting_requester` mãi mãi.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trigger chuyển trạng thái khi sinh viên đã bổ sung thông tin cần thiết, tự động mở khóa bộ đếm SLA để KTV xử lý dứt điểm.

#### Câu 32. Sinh viên có thể xem lịch sử trao đổi và tiến trình khắc phục sự cố tại trang nào trên Cổng Sinh viên?

- **A.** `agent/index.php`
- **B.** `tickets/show.php?id={ticket_id}`
- **C.** `admin/settings.php`
- **D.** `config/database.php`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trang chi tiết phiếu `tickets/show.php` hiển thị đầy đủ timeline các mốc thời gian, tin nhắn của KTV và form gửi phản hồi cho sinh viên.

#### Câu 33. Khi sinh viên tải tệp minh chứng lỗi (ảnh chụp màn hình, file log) đính kèm khi tạo phiếu, tệp được lưu vào thư mục nào?

- **A.** `uploads/assignments/`
- **B.** `uploads/attachments/`
- **C.** `views/layouts/`
- **D.** `storage/database/`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Minh chứng sự cố của vé hỗ trợ được phân tách riêng biệt trong `uploads/attachments/`.

#### Câu 34. Khi tạo phiếu Cứu hộ Buổi học, sinh viên bắt buộc phải chọn thông tin gì trong form ngoài tiêu đề và mô tả sự cố?

- **A.** Chọn số thẻ căn cước công dân của người yêu.
- **B.** Chọn Học phần và Buổi học cụ thể bị gặp sự cố nộp bài (`lesson_id`).
- **C.** Chọn mật khẩu cấp 2 của KTV.
- **D.** Chọn tài khoản ngân hàng nhận tiền cứu trợ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phiếu cứu hộ bắt buộc phải gắn với `lesson_id` để hệ thống tự động tra cứu ra giảng viên phụ trách bộ môn đó và hạn chót deadline của bài tập.

#### Câu 35. Sinh viên có thể tra cứu nhanh danh sách toàn bộ các phiếu mình đã từng gửi trong kỳ học tại mục nào?

- **A.** Mục 'Phiếu của tôi' (`student/tickets.php` hoặc `tickets/index.php?my=1`).
- **B.** Mục 'Cấu hình máy chủ Apache'.
- **C.** Mục 'Nhập Excel tài khoản'.
- **D.** Mục 'Báo cáo doanh thu nhà trường'.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trang 'Phiếu của tôi' cung cấp bảng danh sách lọc theo trạng thái (Mới, Đang xử lý, Chờ phản hồi, Đã giải quyết, Đã đóng) của riêng sinh viên đó.

#### Câu 36. Khi xem một phiếu đã được giải quyết (`resolved`), sinh viên nhìn thấy thông báo nào kêu gọi nghiệm thu?

- **A.** Thông báo yêu cầu nộp thêm học phí.
- **B.** Khối giao diện nổi bật màu tím: 'KTV đã giải quyết xong sự cố. Mời bạn kiểm tra thực tế và đánh giá chất lượng dịch vụ (CSAT)'.
- **C.** Thông báo cảnh báo vi phạm quy chế.
- **D.** Không hiển thị thông báo gì.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giao diện UX thúc đẩy người dùng tương tác, nhấn mạnh quyền lợi của sinh viên được nghiệm thu và cho điểm KTV trước khi phiếu chính thức đóng lại.

#### Câu 37. Nếu sinh viên kiểm tra thực tế mà thấy sự cố VẪN CHƯA ĐƯỢC KHẮC PHỤC TRIỆT ĐỂ, sinh viên nên làm gì?

- **A.** Đánh giá 5 sao rồi chấp nhận dùng máy tính hỏng.
- **B.** Gửi ngay một phản hồi (reply) mô tả rõ tình trạng lỗi còn tồn tại, phiếu sẽ tự động duy trì tiến trình để KTV tiếp tục hỗ trợ.
- **C.** Bỏ học luôn.
- **D.** Tạo thêm 50 phiếu mới làm nghẽn hệ thống.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Việc gửi phản hồi giúp duy trì luồng trao đổi trong cùng một phiếu, bảo lưu toàn bộ lịch sử trao đổi của hai bên.

#### Câu 38. Thẻ HTML nào được dùng để nhúng liên kết tệp đính kèm mà KTV gửi trong nội dung phản hồi cho sinh viên tải về?

- **A.** `<script src="...">`
- **B.** `<a href="uploads/attachments/..." download class="...">`
- **C.** `<style>`
- **D.** `<form action="...">`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thẻ liên kết chuẩn `<a>` có thuộc tính `download` và biểu tượng kẹp ghim tệp đính kèm trực quan.

#### Câu 39. Cột `first_response_at` trong bảng `tickets` được ghi nhận vào thời điểm nào?

- **A.** Khi sinh viên vừa bấm nút gửi phiếu.
- **B.** Khi KTV gửi câu trả lời/phản hồi đầu tiên tới sinh viên.
- **C.** Khi KTV đánh giá lại sinh viên.
- **D.** Khi sinh viên ra trường.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `first_response_at` đánh dấu thời điểm phản hồi đầu tiên của KTV, dùng để đo lường chỉ số cam kết phản hồi nhanh (First Response Time SLA).

#### Câu 40. Khi sinh viên nhấn nút 'Hủy phiếu' trong 15 phút đầu, trạng thái của phiếu trong CSDL được cập nhật thành gì?

- **A.** `status = 'in_progress'`
- **B.** `status = 'closed'` với `closed_reason = 'cancelled_by_requester'` (hoặc bản ghi bị xóa an toàn tùy cấu hình).
- **C.** `status = 'resolved'`
- **D.** `status = 'escalated'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống đóng phiếu minh bạch với lý do người dùng tự hủy, giải phóng ngay công việc khỏi hàng đợi tiếp nhận của KTV.

## <a id="chuyen-de-3"></a>3. Nghiệp Vụ Nghiệm Thu CSAT 5 Sao, Đóng Phiếu & Cơ Chế Tự Động Đóng (Auto-Close)

#### Câu 41. Chỉ số CSAT trong hệ thống Helpdesk CNTT Nhóm 1 là viết tắt của cụm từ tiếng Anh nào?

- **A.** Computer System Access Technology
- **B.** Customer Satisfaction Score (Điểm số hài lòng của người dùng dịch vụ)
- **C.** Central Security Authorization Token
- **D.** Course Schedule Assignment Term

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** CSAT là tiêu chuẩn quốc tế trong quản trị dịch vụ CNTT (ITSM) dùng để đo lường mức độ thỏa mãn của người học đối với dịch vụ hỗ trợ của nhà trường.

#### Câu 42. Bảng CSDL nào lưu trữ trực tiếp dữ liệu đánh giá chất lượng CSAT trong hệ thống?

- **A.** `ticket_ratings`
- **B.** `course_assignments`
- **C.** `system_logs`
- **D.** `password_resets`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `ticket_ratings` lưu trữ các trường: `id`, `ticket_id`, `user_id`, `score`, `comment`, `created_at`.

#### Câu 43. Ràng buộc toàn vẹn nào trong bảng `ticket_ratings` đảm bảo rằng một phiếu hỗ trợ chỉ được sinh viên đánh giá tối đa một lần duy nhất?

- **A.** `PRIMARY KEY (score)`
- **B.** `UNIQUE KEY uk_ticket (ticket_id)`
- **C.** `INDEX idx_comment (comment)`
- **D.** `FOREIGN KEY (user_id)`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ràng buộc `UNIQUE KEY uk_ticket (ticket_id)` ngăn chặn hoàn toàn việc spam điểm số hoặc gửi trùng nhiều lần đánh giá cho cùng một sự cố.

#### Câu 44. Thang điểm đánh giá CSAT trong bảng `ticket_ratings` nhận các giá trị số nguyên hợp lệ trong khoảng nào?

- **A.** Từ 0 đến 10
- **B.** Từ 1 đến 5 sao (`score BETWEEN 1 AND 5`)
- **C.** Từ -5 đến +5
- **D.** Từ 1 đến 100

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thang đo 5 sao chuẩn quốc tế: 1 sao (Rất không hài lòng), 2 sao (Không hài lòng), 3 sao (Bình thường), 4 sao (Hài lòng), 5 sao (Rất hài lòng).

#### Câu 45. Kiểu dữ liệu của cột `score` trong bảng `ticket_ratings` là kiểu gì để tối ưu hóa bộ nhớ lưu trữ?

- **A.** `BIGINT`
- **B.** `TINYINT UNSIGNED` (chiếm đúng 1 byte nhớ)
- **C.** `VARCHAR(255)`
- **D.** `DOUBLE`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `TINYINT UNSIGNED` lưu trữ các số nguyên không âm từ 0 đến 255, vừa vặn hoàn hảo cho thang điểm 1-5 sao mà không lãng phí tài nguyên RAM/Disk.

#### Câu 46. Khối giao diện đánh giá CSAT được Nguyễn Ánh Nhi thiết kế hiển thị khi phiếu hỗ trợ đang ở trạng thái nào?

- **A.** Trạng thái `new` (Mới tạo).
- **B.** Trạng thái `resolved` (Đã giải quyết, chờ nghiệm thu).
- **C.** Trạng thái `closed` đã có đánh giá rồi.
- **D.** Trạng thái `deleted`.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ khi KTV đã xử lý xong và đưa phiếu về `resolved` thì người dùng mới có căn cứ thực tế để đánh giá chất lượng phục vụ.

#### Câu 47. Trải nghiệm tương tác giao diện CSAT được thiết kế theo phong cách hiện đại của các ứng dụng công nghệ phổ biến nào?

- **A.** Gửi thư bưu điện.
- **B.** Bộ chọn 5 ngôi sao tương tác trực quan (Interactive 5-Star Rating) đổi màu vàng khi rê chuột kèm nhãn cảm xúc.
- **C.** Bắt người dùng nhập lệnh trên Terminal dòng lệnh Linux.
- **D.** Bắt sinh viên đến phòng hội đồng làm bài kiểm tra trắc nghiệm 50 câu.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thiết kế giao diện 5 ngôi sao trực quan theo chuẩn UX hiện đại của Grab/Shopee giúp sinh viên chỉ mất 3 giây để hoàn tất nghiệm thu.

#### Câu 48. Ngay sau khi sinh viên nhấn nút 'Gửi đánh giá CSAT' thành công, hệ thống thực hiện thao tác cập nhật nào đối với phiếu hỗ trợ?

- **A.** Xóa vĩnh viễn phiếu khỏi cơ sở dữ liệu.
- **B.** Chuyển trạng thái phiếu từ `resolved` sang `closed` (Đã đóng hoàn tất) và cập nhật `closed_at = NOW()`.
- **C.** Chuyển phiếu về lại trạng thái `new` cho KTV làm lại từ đầu.
- **D.** Khóa tài khoản KTV.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đánh giá CSAT đồng nghĩa với việc người dùng đã nghiệm thu kết quả, phiếu chính thức kết thúc toàn bộ vòng đời và chuyển sang `closed`.

#### Câu 49. Nếu phiếu đã chuyển sang `resolved` nhưng sinh viên quên không vào đánh giá CSAT, hệ thống áp dụng cơ chế tự động đóng (Auto-Close) sau bao lâu?

- **A.** Sau 5 phút.
- **B.** Sau 24 giờ kể từ mốc thời gian `resolved_at`.
- **C.** Sau 10 năm.
- **D.** Không bao giờ đóng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế Auto-Close sau 24h ngăn chặn việc phiếu bị 'treo' vô hạn trên hệ thống, giúp báo cáo thống kê KPI của phòng CNTT luôn chính xác.

#### Câu 50. Khi một phiếu bị tự động đóng qua cơ chế Auto-Close sau 24h, lý do đóng phiếu (`closed_reason`) được ghi nhận là gì?

- **A.** `spam`
- **B.** `auto_closed_timeout` (hoặc `resolved_timeout_auto_closed`)
- **C.** `duplicate`
- **D.** `rejected`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ghi nhận lý do rõ ràng minh bạch để phân biệt giữa phiếu được sinh viên chủ động nghiệm thu và phiếu tự động đóng theo chính sách quá hạn 24h.

#### Câu 51. Hàm tổng hợp SQL nào được sử dụng để tính điểm đánh giá sao CSAT trung bình của một Kỹ thuật viên?

- **A.** `SUM(score)`
- **B.** `AVG(r.score)` (ví dụ: `SELECT ROUND(AVG(r.score), 1) FROM ticket_ratings r ...`)
- **C.** `MAX(score)`
- **D.** `COUNT(score)`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `AVG()` tính trung bình cộng toàn bộ các lượt chấm điểm sao mà KTV đó nhận được từ sinh viên và giảng viên.

#### Câu 52. Điểm sao CSAT trung bình của mỗi Kỹ thuật viên (ví dụ: ⭐ 4.8 / 5.0) được hiển thị công khai ở những vị trí nào trên hệ thống?

- **A.** Chỉ bí mật lưu trong mã nguồn, không ai xem được.
- **B.** Bàn làm việc KTV (Agent Workspace), Thẻ thông tin người xử lý trên chi tiết phiếu và Bảng thống kê hiệu suất Admin Dashboard.
- **C.** In thành tờ rơi dán ở căng tin trường.
- **D.** Chỉ hiển thị khi có thanh tra bộ GD&ĐT kiểm tra.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tạo môi trường làm việc minh bạch, giúp KTV theo dõi được sự ghi nhận của người dùng và nâng cao tinh thần phục vụ của đội ngũ hỗ trợ.

#### Câu 53. Trường `comment` trong bảng `ticket_ratings` phục vụ mục đích gì?

- **A.** Lưu mật khẩu ngân hàng của sinh viên.
- **B.** Lưu nhận xét, góp ý hoặc lời cảm ơn của người dùng dành cho KTV xử lý phiếu.
- **C.** Lưu mã độc kiểm thử.
- **D.** Lưu địa chỉ MAC của card mạng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cho phép người dùng để lại phản hồi định tính bên cạnh điểm số định lượng (ví dụ: 'KTV hỗ trợ rất nhiệt tình, kết nối mạng ổn định').

#### Câu 54. Độ dài tối đa của nhận xét `comment` trong form đánh giá CSAT được giới hạn là bao nhiêu ký tự để bảo đảm tối ưu giao diện?

- **A.** 10 ký tự.
- **B.** 500 ký tự (kiểm soát qua thuộc tính `maxlength="500"` và kiểm tra `mb_strlen()`).
- **C.** 1 triệu ký tự.
- **D.** Không giới hạn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giới hạn 500 ký tự cô đọng đủ ý và ngăn chặn việc cố tình dán văn bản rác làm tràn vỡ layout hiển thị.

#### Câu 55. Nếu sinh viên chỉ bấm chọn số sao (ví dụ: 5 sao) mà để trống ô nhận xét `comment` thì hệ thống có chấp nhận đánh giá không?

- **A.** Không chấp nhận, bắt buộc phải viết văn 100 từ.
- **B.** Chấp nhận bình thường; trường `comment` là tùy chọn (`nullable`), điểm số sao mới là trường bắt buộc.
- **C.** Trừ 5 điểm rèn luyện của sinh viên.
- **D.** Tự động điền nhận xét chê bai KTV.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tối ưu trải nghiệm: người dùng bận rộn chỉ cần bấm 5 sao là đã có thể gửi ngay đánh giá mà không bị ép buộc phải gõ văn bản.

#### Câu 56. Khi một KTV mới nhận việc và chưa có bất kỳ phiếu nào được chấm điểm CSAT, giao diện Agent Workspace hiển thị điểm CSAT mặc định là gì?

- **A.** ⭐ 0.0 / 5.0 (Kém)
- **B.** ⭐ Chưa có đánh giá (hoặc `-- / 5.0`)
- **C.** ⭐ 5.0 / 5.0
- **D.** Báo lỗi hệ thống 500

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Xử lý khéo léo trường hợp `COUNT(ratings) == 0` để tránh hiển thị 0.0 gây hiểu nhầm là KTV bị đánh giá kém.

#### Câu 57. Thao tác gửi đánh giá CSAT từ giao diện sinh viên sử dụng phương thức HTTP nào để bảo mật dữ liệu?

- **A.** Phương thức `GET` truyền tham số trên URL.
- **B.** Phương thức `POST` có kèm mã chống giả mạo `csrf_token`.
- **C.** Phương thức `DELETE`.
- **D.** Phương thức `OPTIONS`.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tất cả hành động thay đổi trạng thái và ghi nhận dữ liệu trong hệ thống đều bắt buộc dùng HTTP POST kết hợp CSRF token.

#### Câu 58. Nếu một người dùng cố tình gửi request POST đánh giá CSAT cho một phiếu không thuộc sở hữu của mình (`requester_id != $_SESSION['user']['id']`), hệ thống phản ứng ra sao?

- **A.** Vẫn ghi nhận bình thường.
- **B.** Chặn đứng ngay lập tức với mã lỗi `403 Forbidden` (Không có quyền thao tác trên phiếu của người khác).
- **C.** Tự động đổi mật khẩu của người gửi.
- **D.** Chuyển tiếp cho giảng viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc bảo mật phân quyền dữ liệu mức đối tượng (Object-Level Authorization): chỉ chính chủ tạo phiếu mới có quyền nghiệm thu và chấm điểm.

#### Câu 59. Khi Admin xem Bảng xếp hạng CSAT của toàn bộ đội ngũ KTV, hệ thống sắp xếp danh sách theo thứ tự nào?

- **A.** Sắp xếp theo thứ tự bảng chữ cái tên KTV.
- **B.** Sắp xếp theo điểm trung bình CSAT giảm dần (`AVG(score) DESC`), KTV có điểm phục vụ cao nhất xếp trên đầu.
- **C.** Sắp xếp theo số điện thoại.
- **D.** Sắp xếp theo ngày vào trường.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tạo động lực thi đua khen thưởng cuối kỳ cho các KTV có thái độ phục vụ tận tâm và hiệu quả nhất.

#### Câu 60. Lợi ích lớn nhất của việc áp dụng mô hình nghiệm thu CSAT 5 sao trong hệ thống Helpdesk Trường Đại học Thủ Đô Hà Nội là gì?

- **A.** Thu thêm tiền dịch vụ từ sinh viên.
- **B.** Chuyển đổi số công tác đo lường chất lượng hỗ trợ CNTT, lấy người học làm trung tâm và loại bỏ tình trạng KTV tự ý đóng phiếu khi sự cố chưa xong.
- **C.** Làm đẹp giao diện cho sinh viên xem.
- **D.** Giảm tốc độ đường truyền mạng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo vệ quyền lợi tối đa cho người học, bảo đảm mọi sự cố kỹ thuật đều được giải quyết thực chất trước khi đóng hoàn tất.

## <a id="chuyen-de-4"></a>4. Quản Lý Danh Mục Dịch Vụ (categories) & Động Cơ Ưu Tiên SLA (priorities)

#### Câu 61. Bảng `categories` do Nguyễn Ánh Nhi phụ trách thiết kế chứa những trường dữ liệu cốt lõi nào?

- **A.** `id`, `student_name`, `math_score`, `literature_score`
- **B.** `id`, `code`, `name`, `sla_hours`, `default_priority_code`, `is_active`, `created_at`
- **C.** `id`, `ip_address`, `mac_address`
- **D.** `id`, `password_hash`, `token`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `categories` định nghĩa nhóm dịch vụ CNTT, quy định thời gian cam kết gốc `sla_hours` và mức ưu tiên mặc định liên kết với bảng `priorities`.

#### Câu 62. Trong bảng `categories`, cột `sla_hours` mang ý nghĩa nghiệp vụ gì?

- **A.** Số giờ kỹ thuật viên được phép đi ngủ trong ngày.
- **B.** Thời gian cam kết xử lý chuẩn (SLA chuẩn tính theo giờ làm việc hành chính) dành riêng cho nhóm dịch vụ đó.
- **C.** Số tín chỉ của môn học.
- **D.** Thời gian máy chủ tự động tắt nguồn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mỗi danh mục có đặc thù riêng: ví dụ dịch vụ Đăng nhập SSO có `sla_hours = 2.0` giờ, dịch vụ Mạng Wi-Fi có `sla_hours = 4.0` giờ, v.v.

#### Câu 63. Cột `default_priority_code` trong bảng `categories` phục vụ cơ chế tự động hóa nào khi sinh viên tạo phiếu mới?

- **A.** Cơ chế tự động gửi tin nhắn SMS tới phụ huynh.
- **B.** Cơ chế Ánh xạ Mức Ưu Tiên Tự Động (Auto-Priority Mapping): tự động chọn mức ưu tiên tương ứng khi người dùng chọn danh mục dịch vụ.
- **C.** Cơ chế tự động trừ tiền trong thẻ ATM.
- **D.** Cơ chế xóa tài khoản sau 15 ngày.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Auto-Priority Mapping giúp chuẩn hóa mức độ khẩn cấp theo loại sự cố mà không phụ thuộc vào cảm tính chủ quan của người tạo phiếu.

#### Câu 64. Bảng `priorities` định nghĩa các mức độ ưu tiên xử lý sự cố bao gồm những trường thông tin nào?

- **A.** `id`, `title`, `author`, `pages`
- **B.** `id`, `code`, `name`, `color_code`, `sla_factor`, `level`
- **C.** `id`, `server_name`, `port`
- **D.** `id`, `bank_account`, `amount`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Các trường này phục vụ hiển thị nhãn màu sắc (`color_code`), cấp độ ưu tiên (`level`) và hệ số nhân thời gian xử lý (`sla_factor`).

#### Câu 65. Hệ số nhân thời gian cam kết SLA (`sla_factor`) đối với mức độ Khẩn cấp (URGENT) trong bảng `priorities` là bao nhiêu?

- **A.** 1.0x
- **B.** 0.50x (Rút ngắn 50% thời gian cam kết chuẩn của danh mục)
- **C.** 2.0x
- **D.** 5.0x

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mức `URGENT` nhân hệ số 0.50: Nếu danh mục có SLA 2 giờ thì mức khẩn cấp chỉ cho phép xử lý tối đa trong 1 giờ hành chính (60 phút).

#### Câu 66. Hệ số nhân thời gian cam kết SLA (`sla_factor`) đối với mức độ Cao (HIGH) trong bảng `priorities` là bao nhiêu?

- **A.** 0.80x (Rút ngắn 20% thời gian cam kết)
- **B.** 1.0x
- **C.** 1.5x
- **D.** 0.0x

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mức `HIGH` có `sla_factor = 0.80`, ưu tiên giải quyết nhanh hơn các phiếu thông thường.

#### Câu 67. Hệ số nhân thời gian cam kết SLA (`sla_factor`) đối với mức độ Bình thường (MEDIUM) trong bảng `priorities` là bao nhiêu?

- **A.** 0.5x
- **B.** 1.00x (Giữ nguyên chuẩn thời gian danh mục)
- **C.** 3.0x
- **D.** 0.1x

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mức `MEDIUM` là mức chuẩn mực cơ sở (`sla_factor = 1.00`), áp dụng cho phần lớn các yêu cầu kỹ thuật thông thường.

#### Câu 68. Hệ số nhân thời gian cam kết SLA (`sla_factor`) đối với mức độ Thấp (LOW) trong bảng `priorities` là bao nhiêu?

- **A.** 0.5x
- **B.** 1.50x (Tăng thêm 50% thời gian cho phép KTV đối với các yêu cầu không gấp)
- **C.** 1.0x
- **D.** 10.0x

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mức `LOW` cho phép thời gian linh hoạt dài hơn (`sla_factor = 1.50`), tạo điều kiện cho KTV tập trung nguồn lực giải quyết các ca khẩn cấp trước.

#### Câu 69. Công thức tính thời gian cam kết hiệu dụng (`effectiveMinutes`) trong `SlaService.php` kết hợp dữ liệu giữa hai bảng do Ánh Nhi phụ trách là gì?

- **A.** `round(sla_hours + sla_factor)`
- **B.** `round(sla_hours * sla_factor * 60)`
- **C.** `round(sla_hours / sla_factor)`
- **D.** `round(sla_hours * 24)`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thời gian hiệu dụng tính bằng phút: `categorySlaHours * prioritySlaFactor * 60`.

#### Câu 70. Màu sắc đại diện chuẩn (`color_code`) của mức độ ưu tiên Khẩn cấp (URGENT) hiển thị trên giao diện là màu gì?

- **A.** Màu xanh lá cây (`#10b981`)
- **B.** Màu đỏ báo động (`#ef4444` hoặc nhãn `badge-danger`)
- **C.** Màu xám nhạt (`#64748b`)
- **D.** Màu tím mộng mơ (`#a855f7`)

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Màu đỏ giúp KTV lập tức nhận biết sự cố đặc biệt nghiêm trọng ngay trên danh sách hàng đợi.

#### Câu 71. Trong bảng `categories`, khi một danh mục tạm thời ngừng phục vụ (ví dụ: đang bảo trì cổng đăng ký tín chỉ), trường `is_active` nhận giá trị nào?

- **A.** 1
- **B.** 0
- **C.** NULL
- **D.** -1

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khi `is_active = 0`, danh mục bị ẩn khỏi form tạo phiếu của sinh viên mà không làm hỏng khóa ngoại của các phiếu cũ đã tạo trong lịch sử.

#### Câu 72. Ràng buộc khóa chính và khóa duy nhất trong bảng `categories` được thiết lập như thế nào?

- **A.** `PRIMARY KEY (id)` và `UNIQUE KEY uk_code (code)`
- **B.** Không có khóa chính
- **C.** `PRIMARY KEY (name)`
- **D.** `UNIQUE KEY (sla_hours)`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mã danh mục `code` (như `SSO`, `WIFI`, `LMS`, `EMAIL`) là duy nhất để truy vấn trong mã nguồn sạch sẽ.

#### Câu 73. Bảng `priorities` có quy định cột `level` kiểu số nguyên tăng dần phục vụ mục đích gì trong thuật toán sắp xếp hàng đợi Triage?

- **A.** Để tính tiền thưởng cho KTV.
- **B.** Để sắp xếp thứ tự ưu tiên hiển thị: phiếu có `level` cao hơn (URGENT level 4) luôn được đẩy lên trước phiếu có level thấp hơn.
- **C.** Để tính số năm bảo hành máy tính.
- **D.** Không có ý nghĩa gì.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thuật toán Triage sắp xếp: `ORDER BY p.level DESC, t.created_at ASC` bảo đảm phiếu nguy cấp luôn được phục vụ trước tiên.

#### Câu 74. Cột `category_id` trong bảng `tickets` tham chiếu đến cột nào trong bảng `categories`?

- **A.** `categories.code`
- **B.** `categories.id` (Khóa ngoại Foreign Key)
- **C.** `categories.name`
- **D.** `categories.sla_hours`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa ngoại trỏ đến `categories.id` bảo đảm toàn vẹn dữ liệu quan hệ theo chuẩn thiết kế 3NF.

#### Câu 75. Khi một danh mục trong bảng `categories` có `default_priority_code = 'URGENT'`, đối tượng người dùng nào được hưởng luồng tạo phiếu khẩn cấp trực tiếp?

- **A.** Khách vãng lai không có tài khoản.
- **B.** Giảng viên đang trong giờ giảng dạy hoặc các sự cố máy chủ do KTV/Admin khai báo.
- **C.** Mọi sinh viên đang chơi game.
- **D.** Bất kỳ ai gửi phiếu vào lúc nửa đêm.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Được cấu hình cho các nghiệp vụ đặc thù cần xử lý ngay lập tức trong giảng đường.

#### Câu 76. Tại sao không nên lưu tên danh mục (`category_name`) trực tiếp vào từng dòng của bảng `tickets` mà phải tách riêng thành bảng `categories`?

- **A.** Để làm cho cơ sở dữ liệu phức tạp hơn.
- **B.** Tuân thủ chuẩn hóa 3NF: tránh dư thừa dữ liệu, khi trường đổi tên dịch vụ chỉ cần sửa 1 bản ghi duy nhất trong bảng `categories` mà không làm sai lệch hàng triệu phiếu cũ.
- **C.** Để tăng kích thước tệp CSDL.
- **D.** Vì MySQL bắt buộc phải làm như vậy.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn hóa 3NF loại bỏ hiện tượng bất thường khi cập nhật (Update Anomaly) và tiết kiệm không gian lưu trữ.

#### Câu 77. Trong bảng `priorities`, cột `color_code` lưu giá trị theo định dạng mã nào?

- **A.** Mã màu nhị phân
- **B.** Mã màu Hexadecimal (ví dụ `#ef4444`, `#f59e0b`, `#3b82f6`, `#10b981`)
- **C.** Mã RGB thập phân phân cách bằng dấu chấm
- **D.** Tên màu bằng tiếng Hán Việt

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mã màu HEX được dùng trực tiếp trong CSS inline hoặc các thuộc tính SVG hiển thị badge trên giao diện web.

#### Câu 78. Nếu Quản trị viên cập nhật `sla_hours` của danh mục 'Mạng Wi-Fi' từ 4 giờ xuống 3 giờ, các phiếu ĐÃ ĐÓNG trong quá khứ có bị tính lại hạn không?

- **A.** Bị tính lại toàn bộ làm sai lệch lịch sử.
- **B.** Không bị ảnh hưởng; thời điểm `due_at` của mỗi phiếu đã được tính toán và chốt cố định tại thời điểm tạo phiếu.
- **C.** Toàn bộ CSDL bị xóa sạch.
- **D.** Tất cả phiếu cũ tự động chuyển sang quá hạn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thời hạn cam kết `due_at` là dữ liệu tĩnh được tính và lưu cố định tại thời điểm tạo phiếu, bảo toàn lịch sử đánh giá KPI của KTV.

#### Câu 79. Trường `code` trong bảng `priorities` gồm 4 giá trị định danh bất biến chuẩn nào?

- **A.** `MOT`, `HAI`, `BA`, `BON`
- **B.** `URGENT`, `HIGH`, `MED`, `LOW`
- **C.** `MAX`, `PRO`, `PLUS`, `MINI`
- **D.** `RED`, `YELLOW`, `BLUE`, `GREEN`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 4 mã định danh chuẩn theo thông lệ quốc tế của các hệ thống ITSM Jira, Zendesk, ServiceNow.

#### Câu 80. Khi truy vấn danh sách danh mục để đổ dữ liệu vào thẻ `<select>` trên form Cổng Sinh viên, câu lệnh SQL nào là tối ưu nhất?

- **A.** `SELECT * FROM categories`
- **B.** `SELECT id, code, name, default_priority_code FROM categories WHERE is_active = 1 ORDER BY id ASC`
- **C.** `SELECT * FROM tickets`
- **D.** `DELETE FROM categories WHERE is_active = 0`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ chọn các cột cần thiết và lọc `is_active = 1` giúp tiết kiệm bộ nhớ nạp dữ liệu và tăng tốc độ tải trang.

## <a id="chuyen-de-5"></a>5. Phân Tích 5 Test Cases Chuyên Sâu & Các Câu Lệnh Truy Vấn SQL Của Ánh Nhi

#### Câu 81. Trong tài liệu kiểm thử của Nguyễn Ánh Nhi (`NguyenAnhNhi_testcase_rating.md`), Test Case 1 kiểm tra kịch bản nghiệp vụ nào?

- **A.** Thêm đánh giá CSAT thành công với dữ liệu hợp lệ (phiếu đã resolved, điểm 5 sao, có nhận xét tốt).
- **B.** Đánh sập máy chủ bằng SQL Injection.
- **C.** Xóa tài khoản của Trưởng khoa.
- **D.** Đổi mật khẩu người dùng thành rỗng.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 1 xác nhận luồng 'Happy Path': sinh viên chọn 5 sao cho phiếu đã giải quyết, hệ thống ghi nhận thành công và hiển thị bản ghi mới.

#### Câu 82. Test Case 2 của Nguyễn Ánh Nhi kiểm thử trường hợp nhập điểm CSAT bất thường nào?

- **A.** Nhập điểm là 5 sao.
- **B.** Nhập điểm ngoài khoảng 1-5 sao (ví dụ nhập điểm 0, hoặc 6, hoặc 10).
- **C.** Nhập điểm là 3 sao.
- **D.** Nhập nhận xét bằng tiếng Anh.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 2 kiểm tra tầng phòng thủ Validation: hệ thống phải chặn đứng và báo lỗi 'Điểm CSAT phải từ 1 đến 5 sao', không lưu dữ liệu rác.

#### Câu 83. Trong Test Case 3 của Ánh Nhi, khi người dùng cố tình gửi đánh giá CSAT lần thứ 2 cho cùng một phiếu đã có đánh giá rồi, kết quả mong đợi là gì?

- **A.** Ghi đè điểm cũ mà không báo gì.
- **B.** Báo lỗi 'Phiếu này đã được đánh giá rồi' và không tạo bản ghi mới (bảo vệ bởi ràng buộc UNIQUE ticket_id).
- **C.** Cộng dồn điểm thành 10 sao.
- **D.** Xóa phiếu đó khỏi CSDL.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ngăn chặn hoàn toàn việc can thiệp hoặc gian lận điểm số CSAT nhiều lần.

#### Câu 84. Test Case 4 của Nguyễn Ánh Nhi kiểm thử sự kết hợp của những tính năng giao diện nào?

- **A.** Chơi game và nghe nhạc trực tuyến.
- **B.** Tìm kiếm theo từ khóa nhận xét + Bộ lọc theo số sao (1-5) + Lọc theo sinh viên cụ thể + Phân trang khi có hơn 8 bản ghi.
- **C.** Gửi email chúc mừng sinh nhật.
- **D.** In hóa đơn đỏ tài chính.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đảm bảo công cụ quản trị đánh giá CSAT hoạt động mượt mà khi dữ liệu phình to lên hàng ngàn đánh giá.

#### Câu 85. Trong Test Case 5 của Ánh Nhi, chức năng 'Khóa toàn bộ' trên bảng đánh giá CSAT mang lại hiệu ứng gì?

- **A.** Tắt nguồn máy tính của giảng viên.
- **B.** Ẩn nút 'Thêm đánh giá' và 'Upload CSV', chuyển sang hiển thị nút 'Mở khóa' để bảo vệ dữ liệu khi hội đồng chấm thi kiểm tra.
- **C.** Xóa toàn bộ CSDL của trường.
- **D.** Không có bất kỳ thay đổi nào.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính năng Khóa/Mở khóa toàn bộ giúp đóng băng giao diện phục vụ kiểm thử và phúc khảo an toàn.

#### Câu 86. Trong tiêu chí kiểm thử của Ánh Nhi, hành động 'Xóa đánh giá CSAT' bắt buộc phải thực hiện bằng phương thức HTTP nào để đạt tiêu chuẩn an toàn?

- **A.** Phương thức `GET` qua thẻ `<a href="delete.php?id=1">`
- **B.** Phương thức `POST` có xác thực token CSRF và hộp thoại xác nhận JavaScript.
- **C.** Phương thức `TRACE`
- **D.** Phương thức `HEAD`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tiêu chí học thuật chuẩn mực: Nghiêm cấm xóa dữ liệu bằng GET vì dễ bị tấn công CSRF hoặc crawler web vô tình xóa nhầm dữ liệu.

#### Câu 87. Câu lệnh truy vấn SQL số 1 trong tệp `NguyenAnhNhi_CauLenhTruyVan_SinhVien.sql` phục vụ chức năng nào trên Cổng Sinh viên?

- **A.** Xóa danh sách lớp học.
- **B.** Lấy danh sách phiếu của 1 sinh viên (kèm tên dịch vụ, mức ưu tiên, trạng thái, thời gian tạo và hạn SLA) dùng cho trang 'Phiếu của tôi'.
- **C.** Đổi mật khẩu tài khoản Admin.
- **D.** Tính lương cho kỹ thuật viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn kết hợp JOIN giữa 3 bảng `tickets`, `categories`, `priorities` với điều kiện `requester_id = :student_id` sắp xếp theo thời gian mới nhất.

#### Câu 88. Đoạn mã SQL nào trong tệp truy vấn cá nhân của Ánh Nhi dùng để lấy chi tiết 1 phiếu cùng thông tin KTV đang tiếp nhận xử lý?

- **A.** `DELETE FROM tickets WHERE id = 1`
- **B.** `SELECT t.*, u_sv.name AS ten_sv, u_ktv.name AS ten_ktv FROM tickets t JOIN users u_sv ON t.requester_id = u_sv.id LEFT JOIN users u_ktv ON t.assigned_to = u_ktv.id ...`
- **C.** `DROP TABLE tickets`
- **D.** `INSERT INTO tickets VALUES (...)`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dùng `LEFT JOIN users u_ktv` vì ở trạng thái mới tạo (`new`), phiếu chưa có KTV tiếp nhận (`assigned_to IS NULL`), nếu dùng INNER JOIN sẽ làm mất bản ghi.

#### Câu 89. Tại sao trong câu truy vấn lấy chi tiết phiếu của Ánh Nhi, bảng `users` được liên kết tới 2 lần bằng hai bí danh (`u_sv` và `u_ktv`)?

- **A.** Do viết nhầm cú pháp SQL.
- **B.** Vì bảng `tickets` có 2 khóa ngoại cùng trỏ về bảng `users`: `requester_id` (người tạo phiếu) và `assigned_to` (kỹ thuật viên phụ trách).
- **C.** Để làm cho câu lệnh SQL dài hơn.
- **D.** Vì bảng `users` chứa dữ liệu của 2 trường đại học.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kỹ thuật Self-Join / Multiple Aliases trên cùng một bảng dữ liệu người dùng là kiến thức CSDL quan trọng trong bài tập lớn.

#### Câu 90. Câu lệnh truy vấn SQL số 3 của Ánh Nhi dùng để trích xuất thông tin gì phục vụ module Nghiệm thu CSAT?

- **A.** Điểm thi kết thúc học phần môn Toán.
- **B.** Lấy điểm đánh giá CSAT (1-5 sao), nội dung nhận xét và thời gian đánh giá của sinh viên đối với phiếu hỗ trợ.
- **C.** Danh sách các thiết bị Wi-Fi bị cháy.
- **D.** Địa chỉ nhà của sinh viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn JOIN giữa `ticket_ratings`, `tickets` và `users` để hiển thị lịch sử nghiệm thu trên hồ sơ cá nhân của sinh viên.

#### Câu 91. Câu lệnh truy vấn SQL số 4 của Ánh Nhi sử dụng mệnh đề `GROUP BY t.status` kết hợp `FIELD()` nhằm mục đích gì trên Dashboard cá nhân của sinh viên?

- **A.** Tính tổng số tiền phạt sinh viên phải đóng.
- **B.** Thống kê số lượng phiếu theo từng trạng thái và sắp xếp hiển thị theo vòng đời chuẩn: Mới -> Đang xử lý -> Chờ phản hồi -> Đã giải quyết -> Đã đóng.
- **C.** Xóa các phiếu đã quá hạn.
- **D.** Đổi màu giao diện trang web.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `FIELD(t.status, 'new', 'in_progress', 'waiting_requester', 'resolved', 'closed')` bảo đảm trật tự hiển thị khoa học trên biểu đồ Dashboard.

#### Câu 92. Câu lệnh truy vấn SQL số 5 trong tệp của Ánh Nhi sử dụng kỹ thuật nào để tìm ra các phiếu ĐÃ GIẢI QUYẾT NHƯNG CHƯA ĐƯỢC SINH VIÊN ĐÁNH GIÁ CSAT?

- **A.** `CROSS JOIN ticket_ratings`
- **B.** `LEFT JOIN ticket_ratings r ON t.id = r.ticket_id WHERE t.status IN ('resolved', 'closed') AND r.id IS NULL`
- **C.** `INNER JOIN ticket_ratings r ON t.id = r.ticket_id`
- **D.** `WHERE score = 0`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mệnh đề `LEFT JOIN ... WHERE r.id IS NULL` là kỹ thuật chuẩn mực để tìm các bản ghi ở bảng cha chưa từng phát sinh bản ghi liên kết ở bảng con.

#### Câu 93. Khi thực thi truy vấn tìm kiếm đánh giá CSAT theo từ khóa nhận xét, cú pháp PDO Prepared Statement nào được Ánh Nhi áp dụng để ngăn ngừa SQL Injection?

- **A.** `"WHERE comment LIKE '%" . $_GET['kw'] . "%'"` (Nối chuỗi trực tiếp)
- **B.** `"WHERE comment LIKE :kw"` kết hợp ràng buộc tham số `$stmt->bindValue(':kw', '%' . $keyword . '%')`
- **C.** `"WHERE comment = " . $_POST['kw']`
- **D.** `"WHERE 1=1"`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng Named Parameter `:kw` kết hợp truyền ký tự đại diện `%` qua biến dữ liệu là giải pháp bảo mật 100% chống SQL Injection theo khuyến nghị OWASP.

#### Câu 94. Trong bảng danh sách đánh giá CSAT, khi có tổng cộng 25 bản ghi và hệ thống phân trang 8 bản ghi mỗi trang, số trang hiển thị tối đa được tính bằng công thức PHP nào?

- **A.** `round(25 / 8)` (kết quả 3 trang - bị thiếu 1 bản ghi)
- **B.** `ceil(25 / 8)` (hàm trần `ceil()` cho kết quả chính xác là 4 trang)
- **C.** `floor(25 / 8)` (kết quả 3 trang)
- **D.** `25 % 8`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `ceil()` (ceiling) làm tròn lên số nguyên gần nhất, bảo đảm trang thứ 4 chứa nốt 1 bản ghi còn lại mà không bị bỏ sót dữ liệu.

#### Câu 95. Công thức tính giá trị `OFFSET` cho câu lệnh SQL phân trang ở Trang số `$page` (với `$limit = 8`) là gì?

- **A.** `$offset = $page * $limit;`
- **B.** `$offset = ($page - 1) * $limit;`
- **C.** `$offset = $page / $limit;`
- **D.** `$offset = $limit;`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ví dụ Trang 1 có offset = (1-1)*8 = 0; Trang 2 có offset = (2-1)*8 = 8; Trang 3 có offset = 16, hoàn toàn chuẩn xác theo đặc tả của SQL LIMIT/OFFSET.

#### Câu 96. Khi người dùng tải lên tệp CSV chứa danh sách đánh giá CSAT mẫu trong Test Case 5, hàm PHP nào được dùng để đọc từng dòng dữ liệu an toàn?

- **A.** `file_get_contents()`
- **B.** `fgetcsv($handle, 1000, ',')`
- **C.** `readfile()`
- **D.** `scandir()`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `fgetcsv()` là hàm chuyên dụng của PHP để bóc tách các trường dữ liệu được ngăn cách bằng dấu phẩy trong tệp CSV.

#### Câu 97. Để ngăn chặn tấn công CSV Injection (Formula Injection) khi xuất báo cáo đánh giá CSAT ra Excel, hệ thống của Ánh Nhi áp dụng cơ chế lọc nào?

- **A.** Xóa toàn bộ ký tự tiếng Việt.
- **B.** Kiểm tra ký tự đầu tiên của chuỗi; nếu bắt đầu bằng các dấu nguy hiểm `=`, `+`, `-`, `@`, tự động thêm dấu nháy đơn `'` ở đầu chuỗi.
- **C.** Chỉ cho phép xuất file PDF.
- **D.** Cấm tải về nếu có chứa chữ 'A'.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thêm dấu nháy đơn `'` ngăn Excel diễn giải ô dữ liệu thành lệnh thực thi công thức động (Formula Execution), bảo vệ an toàn máy tính người mở file.

#### Câu 98. Khi thực hiện xóa một đánh giá CSAT, thao tác kiểm tra tính hợp lệ của token CSRF trong controller được viết như thế nào?

- **A.** `if ($_POST['token'] == '123456')`
- **B.** `if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die('Token không hợp lệ!'); }`
- **C.** `if (empty($_GET['id']))`
- **D.** `if ($_SERVER['REQUEST_METHOD'] == 'GET')`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `hash_equals()` so sánh chuỗi theo thời gian cố định (Constant-time comparison), chống tấn công dò tìm thời gian (Timing Attacks).

#### Câu 99. Cơ chế 'Khóa toàn bộ' trong Test Case 5 của Ánh Nhi lưu trạng thái khóa ở đâu để duy trì giữa các lần tải trang?

- **A.** Lưu trong thẻ nhớ điện thoại.
- **B.** Lưu trong biến phiên `$_SESSION['csat_locked'] = true` hoặc bảng cấu hình hệ thống `system_settings`.
- **C.** Ghi vào tệp âm thanh.
- **D.** In ra máy in của trường.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lưu vào Session hoặc DB bảo đảm trạng thái được ghi nhớ xuyên suốt phiên làm việc của người dùng.

#### Câu 100. Tổng kết lại, đóng góp chuyên môn cốt lõi của sinh viên Nguyễn Ánh Nhi (224001742) trong Đồ án Helpdesk Nhóm 1 là gì?

- **A.** Chỉ vẽ tranh cổ động cho nhóm.
- **B.** Xây dựng trọn vẹn Cổng Sinh viên nộp/sửa bài tập an toàn, thiết kế cấu trúc 2 bảng cốt lõi `categories` và `priorities`, triển khai quy trình nghiệm thu CSAT 5 sao hiện đại và giải pháp tự động đóng phiếu Auto-Close bảo đảm chuẩn ITIL v4.
- **C.** Chỉ cài đặt máy in cho phòng máy.
- **D.** Không tham gia viết bất kỳ dòng mã nào.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyễn Ánh Nhi hoàn thành xuất sắc 19% khối lượng dự án với các sản phẩm Model sạch sẽ, bảo mật cao và giao diện tương tác trực quan chuẩn mực.

---

## <a id="bang-dap-an"></a>📊 BẢNG TỔNG HỢP ĐÁP ÁN 100 CÂU HỎI (NGUYỄN ÁNH NHI)

| Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | B | **21** | B | **41** | B | **61** | B | **81** | A |
| **2** | B | **22** | B | **42** | A | **62** | B | **82** | B |
| **3** | B | **23** | B | **43** | B | **63** | B | **83** | B |
| **4** | B | **24** | B | **44** | B | **64** | B | **84** | B |
| **5** | B | **25** | B | **45** | B | **65** | B | **85** | B |
| **6** | B | **26** | B | **46** | B | **66** | A | **86** | B |
| **7** | B | **27** | B | **47** | B | **67** | B | **87** | B |
| **8** | A | **28** | B | **48** | B | **68** | B | **88** | B |
| **9** | B | **29** | B | **49** | B | **69** | B | **89** | B |
| **10** | A | **30** | B | **50** | B | **70** | B | **90** | B |
| **11** | A | **31** | B | **51** | B | **71** | B | **91** | B |
| **12** | B | **32** | B | **52** | B | **72** | A | **92** | B |
| **13** | B | **33** | B | **53** | B | **73** | B | **93** | B |
| **14** | B | **34** | B | **54** | B | **74** | B | **94** | B |
| **15** | B | **35** | A | **55** | B | **75** | B | **95** | B |
| **16** | B | **36** | B | **56** | B | **76** | B | **96** | B |
| **17** | B | **37** | B | **57** | B | **77** | B | **97** | B |
| **18** | B | **38** | B | **58** | B | **78** | B | **98** | B |
| **19** | A | **39** | B | **59** | B | **79** | B | **99** | B |
| **20** | A | **40** | B | **60** | B | **80** | B | **100** | B |

---

# BỘ 100 CÂU HỎI TRẮC NGHIỆM CHUYÊN SÂU – LÊ HÀ MY (224001735)

> **Thành viên phụ trách:** Lê Hà My (MSV: 224001735 – Lớp: TUD D2024)
> **Phân hệ chuyên trách:** Cổng Giảng Viên, Quản Lý Học Phần & Chấm Điểm Bài Tập
> **Bảng CSDL cốt lõi:** `courses`, `course_assignments`, `lessons`
> **Model phụ trách:** `Course.php`, `Lesson.php`

---

## 📊 BẢNG MA TRẬN ĐÁP ÁN 100 CÂU HỎI (QUICK ANSWER KEY)

> **Phân bổ đáp án chuẩn:** 25 câu [A] · 25 câu [B] · 25 câu [C] · 25 câu [D] (Được xáo trộn ngẫu nhiên toàn diện)

| Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | `B` | **21** | `B` | **41** | `C` | **61** | `A` | **81** | `C` |
| **2** | `D` | **22** | `C` | **42** | `D` | **62** | `C` | **82** | `D` |
| **3** | `A` | **23** | `A` | **43** | `A` | **63** | `A` | **83** | `A` |
| **4** | `D` | **24** | `A` | **44** | `A` | **64** | `B` | **84** | `B` |
| **5** | `D` | **25** | `D` | **45** | `A` | **65** | `B` | **85** | `B` |
| **6** | `C` | **26** | `D` | **46** | `A` | **66** | `C` | **86** | `D` |
| **7** | `B` | **27** | `C` | **47** | `B` | **67** | `D` | **87** | `C` |
| **8** | `B` | **28** | `A` | **48** | `C` | **68** | `D` | **88** | `D` |
| **9** | `B` | **29** | `A` | **49** | `C` | **69** | `D` | **89** | `A` |
| **10** | `D` | **30** | `A` | **50** | `B` | **70** | `D` | **90** | `C` |
| **11** | `C` | **31** | `C` | **51** | `A` | **71** | `B` | **91** | `A` |
| **12** | `A` | **32** | `A` | **52** | `A` | **72** | `B` | **92** | `A` |
| **13** | `C` | **33** | `B` | **53** | `D` | **73** | `C` | **93** | `A` |
| **14** | `D` | **34** | `C` | **54** | `B` | **74** | `C` | **94** | `C` |
| **15** | `B` | **35** | `C` | **55** | `D` | **75** | `B` | **95** | `B` |
| **16** | `A` | **36** | `C` | **56** | `B` | **76** | `A` | **96** | `D` |
| **17** | `C` | **37** | `D` | **57** | `C` | **77** | `D` | **97** | `C` |
| **18** | `D` | **38** | `B` | **58** | `B` | **78** | `B` | **98** | `C` |
| **19** | `D` | **39** | `D` | **59** | `B` | **79** | `B` | **99** | `D` |
| **20** | `A` | **40** | `D` | **60** | `C` | **80** | `A` | **100** | `B` |

---

## 1. CỔNG GIẢNG VIÊN: KIẾN TRÚC PHÂN CÔNG GIẢNG DẠY, QUYỀN HẠN & NGUYÊN TẮC ITIL 1 LỚP 1 GV

#### Câu 1. Nguyên tắc phân công giảng dạy cốt lõi theo chuẩn ITIL v4 do Lê Hà My thiết lập cho hệ thống là gì?

- **A.** Một lớp học phần có thể có 10 giảng viên cùng đứng tên phụ trách.
- **B.** Mỗi lớp học phần (xác định bởi lớp học, môn học và học kỳ) chỉ có DUY NHẤT 1 GIẢNG VIÊN phụ trách quản lý điểm số và bài tập.
- **C.** Sinh viên tự do phân công giảng viên cho chính mình.
- **D.** Không cần ghi nhận giảng viên trong cơ sở dữ liệu.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc '1 Lớp Học Phần = 1 Giảng Viên Phụ Trách' bảo đảm trách nhiệm giải trình minh bạch, không gây chồng chéo khi chấm điểm hoặc duyệt gia hạn deadline.

#### Câu 2. Ràng buộc CSDL nào trong bảng `course_assignments` trực tiếp hiện thực hóa nguyên tắc '1 Lớp Học Phần = 1 Giảng Viên'?

- **A.** `CHECK (academic_year > 2000)`
- **B.** `INDEX (lecturer_id)`
- **C.** `PRIMARY KEY (id)`
- **D.** `UNIQUE KEY uk_assignment (class_id, course_id, semester)`

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ mục duy nhất tổ hợp `(class_id, course_id, semester)` chặn đứng ngay ở tầng CSDL mọi nỗ lực phân công trùng lặp 2 giảng viên cho cùng 1 lớp môn học trong cùng 1 kỳ.

#### Câu 3. Bảng CSDL `course_assignments` bao gồm những cột thông tin cốt lõi nào?

- **A.** `id`, `class_id`, `course_id`, `lecturer_id`, `semester`, `academic_year`, `created_at`
- **B.** `id`, `email`, `password_hash`
- **C.** `id`, `ticket_code`, `status`
- **D.** `id`, `student_id`, `score`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng đóng vai trò thực thể liên kết 3 chiều giữa Lớp sinh hoạt (`classes`), Môn học (`courses`) và Giảng viên (`users` có `user_type = 'lecturer'`).

#### Câu 4. Cơ chế Middleware nào bảo vệ các trang nghiệp vụ của Cổng Giảng viên (`lecturer/*.php`) khỏi sự truy cập trái phép?

- **A.** Chỉ kiểm tra người dùng đã đăng nhập chưa (`!empty($_SESSION['user'])`).
- **B.** Không cần kiểm tra bảo mật.
- **C.** Kiểm tra địa chỉ IP của quán net.
- **D.** Kiểm tra quyền kép: Người dùng đã đăng nhập, có vai trò `requester` và `user_type === 'lecturer'`; nếu vi phạm sẽ chuyển hướng lỗi 403 Forbidden.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểm tra `user_type === 'lecturer'` bảo đảm sinh viên bình thường dù đã đăng nhập cũng không thể can thiệp vào giao diện chấm điểm và giao bài của thầy cô.

#### Câu 5. Trên Bàn làm việc Giảng viên (`lecturer/index.php`), danh sách các lớp học phần được hiển thị mặc định lọc theo điều kiện học kỳ nào?

- **A.** Tất cả các học kỳ từ khi thành lập trường gom chung vào 1 trang.
- **B.** Chỉ hiển thị các lớp đã bế giảng.
- **C.** Học kỳ của 10 năm trước.
- **D.** Học kỳ hiện tại đang diễn ra (ví dụ: `semester = '2026_1'` tương ứng Học kỳ 1 năm học 2026-2027).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lọc theo học kỳ hiện hành giúp giảng viên tập trung vào các lớp đang giảng dạy, giảm thiểu nhầm lẫn khi thao tác.

#### Câu 6. Đặc quyền ưu tiên đặc biệt nào được cấp cho Giảng viên khi tạo phiếu báo sự cố kỹ thuật trong giờ giảng dạy trên lớp?

- **A.** Được phép tự nâng lương cho bản thân.
- **B.** Được phép xóa tài khoản của sinh viên vi phạm.
- **C.** Được quyền chọn mức ưu tiên Cao (HIGH) hoặc Khẩn cấp (URGENT) để kích hoạt luồng ưu tiên 'Academic Fast-Track' đến thẳng KTV trực ca.
- **D.** Được quyền đóng hệ thống máy chủ trường.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giảng đường xảy ra sự cố máy chiếu, micro hay mạng Wi-Fi giảng dạy là trường hợp khẩn cấp ảnh hưởng trực tiếp đến giờ học của cả lớp, cần được KTV ứng cứu tức thì.

#### Câu 7. Cột `class_id` trong bảng `course_assignments` là khóa ngoại tham chiếu trực tiếp đến bảng nào?

- **A.** `tickets.id`
- **B.** `classes.id` (Bảng quản lý các lớp sinh hoạt của sinh viên)
- **C.** `courses.id`
- **D.** `users.id`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tham chiếu tới bảng `classes` (ví dụ lớp D24-CNTT1, D24-CNTT2, TUD D2024).

#### Câu 8. Cột `course_id` trong bảng `course_assignments` là khóa ngoại tham chiếu trực tiếp đến bảng nào?

- **A.** `holidays.id`
- **B.** `courses.id` (Bảng danh mục các môn học trong chương trình đào tạo)
- **C.** `classes.id`
- **D.** `roles.id`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tham chiếu tới `courses` (chứa mã môn `course_code` như `WEB101`, tên môn 'Lập trình Web', số tín chỉ).

#### Câu 9. Cột `lecturer_id` trong bảng `course_assignments` tham chiếu đến trường nào trong bảng `users` với điều kiện ràng buộc gì?

- **A.** Bất kỳ sinh viên năm nhất nào.
- **B.** `users.id` với điều kiện người dùng phải có `user_type = 'lecturer'`.
- **C.** `users.email`
- **D.** `users.phone`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo đảm chỉ các tài khoản có tư cách Giảng viên mới được gán quyền phụ trách bộ môn.

#### Câu 10. Khi một giảng viên xem danh sách các lớp học phần trên Cổng Giảng viên, câu truy vấn SQL sử dụng điều kiện `WHERE` nào để phân quyền dữ liệu?

- **A.** `WHERE ca.semester IS NULL`
- **B.** `WHERE ca.id = 1`
- **C.** `WHERE 1=1` (Hiện tất cả lớp của mọi giảng viên)
- **D.** `WHERE ca.lecturer_id = :current_user_id` (Chỉ hiển thị các lớp do chính giảng viên đó phụ trách)

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc cô lập dữ liệu: Giảng viên A không được can thiệp hay nhìn thấy bài tập, điểm số của các lớp do Giảng viên B phụ trách.

#### Câu 11. Tại sao hệ thống không gán trực tiếp sinh viên vào từng buổi học mà phải thông qua lớp học phần (`course_assignments`)?

- **A.** Vì sinh viên không thích học.
- **B.** Để làm tốn tài nguyên máy chủ.
- **C.** Chuẩn hóa quan hệ nhiều - nhiều theo 3NF: Sinh viên thuộc Lớp sinh hoạt (`classes`), Lớp sinh hoạt được phân công Môn học (`course_assignments`), từ đó suy ra danh sách sinh viên học môn đó một cách tự nhiên và nhất quán.
- **D.** Do giới hạn của ngôn ngữ PHP.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mô hình dữ liệu quan hệ 3NF giúp quản lý hàng chục ngàn sinh viên chỉ với các mối liên kết khóa ngoại tinh gọn, không dư thừa.

#### Câu 12. Giao diện phân công giảng dạy dành cho Quản trị viên (`admin/course_assignments.php`) do Lê Hà My xây dựng hỗ trợ tính năng xuất dữ liệu nào?

- **A.** Xuất danh sách phân công ra tệp CSV (Excel) kèm bộ lọc lớp, môn, giảng viên hiện tại.
- **B.** Xuất ra đĩa mềm.
- **C.** Xuất ra mã nhị phân.
- **D.** Chỉ cho phép chụp ảnh màn hình.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hỗ trợ Phòng Đào tạo tải về bảng đối soát phân công giảng dạy nhanh chóng chỉ bằng 1 thao tác click.

#### Câu 13. Thao tác xóa một bản ghi phân công giảng dạy (`course_assignments`) có bị hệ thống từ chối trong trường hợp nào?

- **A.** Khi giảng viên đã về hưu.
- **B.** Không bao giờ bị từ chối.
- **C.** Khi lớp học phần đó đã phát sinh dữ liệu các buổi học (`course_lessons`) và bài nộp của sinh viên (`RESTRICT` khóa ngoại).
- **D.** Khi trời mưa.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa ngoại với hành vi `ON DELETE RESTRICT` ngăn ngừa việc vô tình xóa mất toàn bộ lịch sử học tập và điểm số của sinh viên trong kỳ.

#### Câu 14. Cột `academic_year` trong bảng `course_assignments` lưu định dạng chuỗi năm học chuẩn nào?

- **A.** `K2024`
- **B.** `2026`
- **C.** `NAM_NAY`
- **D.** `2026-2027`

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Định dạng chuẩn `YYYY-YYYY` theo quy chế đào tạo tín chỉ của các trường đại học tại Việt Nam.

#### Câu 15. Cột `semester` trong bảng `course_assignments` lưu định dạng chuẩn nào?

- **A.** `HK1`, `HK2`
- **B.** `YYYY_1` hoặc `YYYY_2` (ví dụ `2026_1` cho Học kỳ 1 năm 2026)
- **C.** `1`, `2`, `3`
- **D.** `Học kỳ mùa thu`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cấu trúc `YYYY_N` dễ dàng phục vụ sắp xếp thời gian và truy vấn theo niên giám đào tạo.

#### Câu 16. Mã lớp học phần hiển thị cho sinh viên và giảng viên được ghép tự động theo cấu trúc nào trên tiêu đề trang báo cáo?

- **A.** `{semester}_{course_code}_{class_code}` (ví dụ: `2026_1_30AMA087_TUDD2024_001`)
- **B.** `LOPHOC_123`
- **C.** `TEN_GIANG_VIEN`
- **D.** `ID_AUTO_INCREMENT`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn hóa mã lớp học phần duy nhất giúp phòng đào tạo dễ dàng định danh trong các kỳ thi kết thúc học phần.

#### Câu 17. Khi một giảng viên đăng nhập vào hệ thống, thông tin nào trong mảng `$_SESSION['user']` xác định đây là giảng viên bộ môn?

- **A.** `$_SESSION['user']['role'] === 'superman'`
- **B.** `$_SESSION['user']['department'] === 'School'`
- **C.** `$_SESSION['user']['user_type'] === 'lecturer'`
- **D.** `$_SESSION['user']['is_teacher'] === 1`

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trường `user_type = 'lecturer'` trong bảng `users` được nạp vào session tại thời điểm đăng nhập thành công.

#### Câu 18. Khi Giảng viên nhấn nút 'Xem danh sách lớp phụ trách', Controller nào tiếp nhận và điều hướng yêu cầu?

- **A.** `InstallController`
- **B.** `AdminController`
- **C.** `GuestTicketController`
- **D.** `CourseAssignmentController` (hoặc `LecturerController`)

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiến trúc Clean MVC: Controller chuyên biệt tiếp nhận request, gọi Model `CourseAssignment` truy vấn CSDL và trả về View tương ứng.

#### Câu 19. Khi tạo mới một phân công giảng dạy, trường nào sau đây là BẮT BUỘC phải chọn và không được để trống?

- **A.** Chỉ cần chọn Lớp học, các trường khác để trống.
- **B.** Không có trường nào bắt buộc.
- **C.** Chỉ cần nhập tên giảng viên.
- **D.** Bắt buộc chọn đủ 4 trường: Lớp học (`class_id`), Môn học (`course_id`), Giảng viên phụ trách (`lecturer_id`) và Học kỳ (`semester`).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bốn thông tin then chốt cấu thành một đơn vị lớp học phần hoàn chỉnh; thiếu một trường sẽ không thể xác định đối tượng phục vụ.

#### Câu 20. Quy tắc nghiệp vụ nào ngăn chặn việc một người không có chuyên môn sư phạm (như tài khoản Khách hoặc KTV) được phân công dạy học phần?

- **A.** Hệ thống lọc danh sách giảng viên trong thẻ chọn `<select>` chỉ lấy các user thỏa mãn điều kiện `user_type = 'lecturer' AND status = 'active'`.
- **B.** Bắt buộc phải thi đấu thể thao để chọn người dạy.
- **C.** Nhờ sinh viên bầu chọn bằng phiếu kín.
- **D.** Không có bất kỳ sự kiểm tra nào.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dropdown chỉ liệt kê danh sách nhân sự thuộc ngạch giảng viên đang công tác tại trường, loại trừ tuyệt đối tài khoản kỹ thuật viên hoặc sinh viên.

## 2. QUẢN LÝ BUỔI HỌC: BAN HÀNH ĐỀ BÀI, ĐÍNH KÈM TÀI LIỆU & THIẾT LẬP HẠN CHÓT (DEADLINE)

#### Câu 21. Bảng CSDL `course_lessons` do Lê Hà My phụ trách thiết kế chứa những trường thông tin chính nào?

- **A.** `id`, `mac_address`, `ip_address`
- **B.** `id`, `assignment_id`, `lesson_number`, `title`, `description`, `attachment_path`, `due_date`, `created_at`
- **C.** `id`, `student_id`, `score`
- **D.** `id`, `password_hash`, `token`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `course_lessons` lưu trữ toàn bộ thông tin buổi học của một lớp học phần: số thứ tự buổi, tiêu đề, tệp đề bài đính kèm và thời hạn nộp bài (due_date).

#### Câu 22. Cột `assignment_id` trong bảng `course_lessons` liên kết trực tiếp với bảng nào?

- **A.** `tickets.id`
- **B.** `classes.id`
- **C.** `course_assignments.id` (Khóa ngoại Foreign Key trỏ tới lớp học phần cụ thể)
- **D.** `users.id`

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mỗi buổi học thuộc về một lớp học phần xác định (`course_assignments`), cho phép cùng một môn học nhưng các lớp khác nhau có thể có lịch học và đề bài linh hoạt.

#### Câu 23. Trong bảng `course_lessons`, cột `lesson_number` có kiểu dữ liệu và ý nghĩa gì?

- **A.** `INT UNSIGNED` lưu số thứ tự buổi học (từ Buổi 1, Buổi 2 đến Buổi 15 của học phần).
- **B.** `DATETIME` lưu ngày bắt đầu học.
- **C.** `VARCHAR(50)` lưu tên của phòng học.
- **D.** `DECIMAL(4,2)` lưu điểm số của giảng viên.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `lesson_number` giúp hệ thống và sinh viên sắp xếp các bài tập theo trình tự giảng dạy chuẩn từ đầu kỳ đến cuối kỳ.

#### Câu 24. Khi Giảng viên thiết lập thời hạn nộp bài (`due_date`) cho một buổi học, quy tắc kiểm tra tính hợp lệ (Validation) bắt buộc điều kiện nào?

- **A.** `due_date > NOW()` (Hạn nộp bài phải là một thời điểm trong tương lai so với thời điểm ban hành đề bài).
- **B.** Không cần kiểm tra thời gian.
- **C.** `due_date` phải là một ngày trong quá khứ của 5 năm trước.
- **D.** `due_date` bắt buộc phải rơi vào ngày Chủ Nhật.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ngăn ngừa lỗi logic: Giảng viên không thể vô tình đặt hạn nộp bài về quá khứ khiến sinh viên vừa mở form ra đã bị khóa nộp bài ngay lập tức.

#### Câu 25. Khi Giảng viên đính kèm tệp đề bài chi tiết (file PDF hoặc DOCX) cho buổi học, tệp tài liệu này được lưu trữ tại thư mục nào?

- **A.** `C:\Program Files\`
- **B.** `config/`
- **C.** `views/layouts/`
- **D.** `uploads/lessons/` (hoặc `uploads/assignments/materials/`)

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tài liệu đề bài của giảng viên được phân vùng riêng trong thư mục `uploads/lessons/`, tách biệt với tệp bài làm do sinh viên nộp lên.

#### Câu 26. Để tạo điều kiện cho sinh viên tải đề bài nhanh chóng và trực quan, giao diện buổi học cung cấp thông tin tệp gồm những gì?

- **A.** Bắt sinh viên phải trả tiền để tải đề bài.
- **B.** Chỉ hiển thị dòng chữ 'Đã có đề bài'.
- **C.** Chỉ hiển thị chuỗi mã băm MD5 khó hiểu.
- **D.** Tên tệp gốc thân thiện, định dạng tệp (PDF/DOCX/ZIP), dung lượng tệp (KB/MB) và nút bấm tải xuống nổi bật.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giao diện chuẩn UX sư phạm hiển thị đầy đủ thông tin tệp giúp sinh viên chủ động kiểm tra dung lượng trước khi tải về điện thoại hoặc máy tính.

#### Câu 27. Nếu Giảng viên muốn chỉnh sửa nội dung đề bài hoặc gia hạn thêm thời gian nộp bài cho CẢ LỚP, thao tác được thực hiện ở đâu?

- **A.** Nhắn tin riêng cho từng sinh viên trên Zalo.
- **B.** Giảng viên phải xóa tài khoản và tạo lại từ đầu.
- **C.** Vào giao diện 'Sửa Buổi Học' (`lecturer/lessons/edit.php`), cập nhật lại ngày giờ `due_date` hoặc thay thế tệp đề bài mới.
- **D.** Đổi mật khẩu máy chủ cơ sở dữ liệu.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính năng cập nhật buổi học cho phép Giảng viên linh hoạt điều chỉnh tiến độ học tập cho toàn thể sinh viên trong lớp.

#### Câu 28. Khi Giảng viên cập nhật lùi thời hạn `due_date` thêm 3 ngày cho một buổi học, các sinh viên chưa kịp nộp bài trước đó sẽ nhận được trạng thái gì?

- **A.** Form nộp bài tự động mở khóa trở lại và sinh viên có thể nộp bài bình thường trong khung giờ mới gia hạn.
- **B.** Bị trừ 50% điểm học phần.
- **C.** Tài khoản sinh viên bị chuyển sang trạng thái chờ duyệt.
- **D.** Vẫn bị coi là trễ hạn và không nộp được.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Vì hệ thống so sánh động `time() <= strtotime($lesson['due_date'])`, khi `due_date` được nới rộng thì điều kiện nộp bài tự động thỏa mãn tức thì.

#### Câu 29. Trong bảng `course_lessons`, trường `description` có kiểu dữ liệu là gì trong CSDL MySQL?

- **A.** `TEXT` (cho phép lưu trữ hướng dẫn chi tiết, tiêu chí chấm điểm và yêu cầu đề bài dài hàng ngàn ký tự)
- **B.** `VARCHAR(10)`
- **C.** `BOOLEAN`
- **D.** `INT`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểu dữ liệu `TEXT` đáp ứng đầy đủ cho các đề bài thực hành có nội dung phong phú, danh sách câu hỏi hoặc hướng dẫn từng bước.

#### Câu 30. Tại sao hệ thống áp dụng ràng buộc `ON DELETE RESTRICT` giữa bảng `course_lessons` và bảng `course_assignments`?

- **A.** Để bảo vệ toàn vẹn dữ liệu: Không cho phép xóa lớp học phần khi lớp đó đã có các bài tập buổi học đang được triển khai.
- **B.** Để bắt buộc Giảng viên phải xóa từng chữ một.
- **C.** Không có tác dụng gì.
- **D.** Do lỗi cấu hình CSDL.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc an toàn dữ liệu học thuật: ngăn chặn thao tác nhầm lẫn làm mất toàn bộ bài giảng và lịch sử nộp bài của sinh viên.

#### Câu 31. Khi Giảng viên bấm nút 'Thêm Buổi Học Mới', form tạo buổi học kiểm tra trùng lặp trường nào trong cùng 1 lớp học phần?

- **A.** Kiểm tra trùng tên sinh viên.
- **B.** Kiểm tra địa chỉ nhà của lớp trưởng.
- **C.** Kiểm tra trùng số thứ tự buổi học (`assignment_id, lesson_number`) để tránh việc lớp có 2 buổi học cùng đánh số 'Buổi 1'.
- **D.** Kiểm tra trùng số điện thoại của Giảng viên.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo đảm thứ tự bài học từ Buổi 1 đến Buổi 15 luôn nhất quán, không xảy ra xung đột đánh số thứ tự bài học.

#### Câu 32. Thẻ HTML nào được dùng để cho phép Giảng viên nhập nội dung mô tả đề bài nhiều dòng trên form giao bài?

- **A.** `<textarea name="description" rows="6" class="..."></textarea>`
- **B.** `<input type="password">`
- **C.** `<button>`
- **D.** `<select>`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thẻ `<textarea>` cung cấp không gian rộng rãi để Giảng viên soạn thảo các yêu cầu kỹ thuật của bài thực hành.

#### Câu 33. Để chọn ngày giờ hạn nộp trực quan trên trình duyệt (có lịch chọn ngày và đồng hồ chọn giờ), thẻ input sử dụng thuộc tính `type` nào?

- **A.** `type="checkbox"`
- **B.** `type="datetime-local"`
- **C.** `type="radio"`
- **D.** `type="text"`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `datetime-local` kích hoạt tiện ích chọn ngày giờ tích hợp sẵn của HTML5 (DatePicker), hạn chế tối đa lỗi người dùng gõ sai định dạng ngày tháng.

#### Câu 34. Định dạng chuỗi thời gian nào được lưu vào cột `due_date` trong CSDL MySQL khi form gửi lên?

- **A.** `HH:MM DD-MM-YY`
- **B.** `DD/MM/YYYY`
- **C.** `YYYY-MM-DD HH:MM:SS` (chuẩn DATETIME của MySQL, ví dụ: `2026-11-20 23:59:00`)
- **D.** `TIMESTAMP_MS`

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn định dạng DATETIME quốc tế `Y-m-d H:i:s` giúp việc tính toán chênh lệch thời gian qua các hàm `DATEDIFF()` hoặc `TIMEDIFF()` của SQL diễn ra chính xác.

#### Câu 35. Khi một buổi học được tạo thành công, Giảng viên có thể xem danh sách các buổi học của lớp mình tại trang nào?

- **A.** `admin/settings.php`
- **B.** `agent/ticket_queue.php`
- **C.** `lecturer/lessons.php?assignment_id={id}`
- **D.** `install/setup.sql`

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trang danh sách buổi học hiển thị bảng tổng kết: Thứ tự buổi, Tên đề bài, Tệp đính kèm, Hạn nộp và số lượng sinh viên đã nộp bài.

#### Câu 36. Nếu Giảng viên giao bài tập mà không đính kèm tệp tài liệu (chỉ mô tả đề bài bằng văn bản trong `description`), hệ thống có chấp nhận không?

- **A.** Hệ thống tự động xóa buổi học.
- **B.** Không chấp nhận, bắt buộc phải tải lên tệp 100MB.
- **C.** Chấp nhận bình thường; cột `attachment_path` cho phép giá trị `NULL` dành cho các bài tập ngắn gọn.
- **D.** Trừ lương của Giảng viên.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Linh hoạt trong công tác giảng dạy: Giảng viên có thể giao bài bằng văn bản trực tiếp hoặc đính kèm tệp đề bài chi tiết tùy nhu cầu.

#### Câu 37. Khi Giảng viên xóa một buổi học CHƯA CÓ sinh viên nào nộp bài, tệp đề bài đính kèm trên máy chủ được xử lý thế nào?

- **A.** Tự động đổi tên tệp thành tên của hiệu trưởng.
- **B.** Gửi tệp đó cho công an.
- **C.** Để lại vĩnh viễn trên máy chủ làm rác bộ nhớ.
- **D.** Hệ thống kiểm tra nếu tệp tồn tại qua `file_exists()` và xóa tệp bằng hàm `unlink()` để giải phóng dung lượng đĩa cứng.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy trình dọn dẹp tài nguyên sạch sẽ (Resource Cleanup): xóa dữ liệu trong DB đi kèm xóa tệp rác trên ổ cứng.

#### Câu 38. Cột `created_at` trong bảng `course_lessons` được thiết lập giá trị mặc định là gì?

- **A.** `NULL`
- **B.** `CURRENT_TIMESTAMP`
- **C.** `0000-00-00 00:00:00`
- **D.** `2000-01-01`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tự động ghi nhận thời điểm Giảng viên ban hành buổi học lên hệ thống mà không cần truyền giá trị thủ công trong câu lệnh INSERT.

#### Câu 39. Trên giao diện Bàn làm việc Giảng viên, chỉ số nào cho biết mức độ hoàn thành bài tập của sinh viên trong một buổi học?

- **A.** Tốc độ quạt tản nhiệt của máy tính.
- **B.** Chỉ số nhiệt độ phòng máy chủ.
- **C.** Số lượt like trên Facebook của Giảng viên.
- **D.** Tỷ lệ số lượng bài đã nộp trên tổng số sinh viên của lớp (ví dụ: `28/32 Sinh viên đã nộp bài`).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thống kê trực quan `COUNT(submissions) / COUNT(students)` giúp Giảng viên nắm bắt ngay sĩ số làm bài trước khi buổi học kết thúc.

#### Câu 40. Liên kết giữa bảng `tickets` và bảng `course_lessons` phục vụ tính năng 'Cứu hộ buổi học' được thiết lập qua khóa ngoại nào?

- **A.** `course_lessons.lecturer_id`
- **B.** `tickets.priority_id`
- **C.** `course_lessons.ticket_id`
- **D.** `tickets.lesson_id` trỏ tới `course_lessons.id`

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa ngoại `tickets.lesson_id` là cầu nối kỹ thuật quan trọng nhất để gắn một phiếu báo sự cố với bài tập buổi học cụ thể của sinh viên.

## 3. QUẢN LÝ NỘP BÀI: TẢI TỆP SINH VIÊN AN TOÀN, CHẤM ĐIỂM THANG 10 & NHẬN XÉT (FEEDBACK)

#### Câu 41. Trang xem danh sách nộp bài và chấm điểm của Giảng viên (`lecturer/submissions.php`) lấy dữ liệu từ bảng nào là chủ đạo?

- **A.** `holidays`
- **B.** `ticket_ratings`
- **C.** `student_assignment_submissions` kết hợp JOIN với `users` (lấy họ tên sinh viên) và `course_lessons`
- **D.** `password_resets`

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn phối hợp giúp Giảng viên có cái nhìn toàn diện: Họ tên sinh viên, Mã sinh viên, Tệp bài nộp, Thời gian nộp, Điểm số và Nhận xét.

#### Câu 42. Hành động tải tệp bài làm của sinh viên xuống máy tính của Giảng viên được bảo vệ như thế nào để ngăn chặn tải trộm?

- **A.** Công khai đường dẫn tệp cho toàn bộ Internet tải về.
- **B.** Bắt buộc Giảng viên phải nhập mã OTP 6 số từ ngân hàng.
- **C.** Chỉ cho phép tải vào ban đêm.
- **D.** Truyền qua script PHP xử lý tải tệp: Kiểm tra trong Session xem Giảng viên hiện tại có phải là người phụ trách lớp học phần đó không trước khi xuất luồng dữ liệu tệp.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo mật chống tải tệp trái thẩm quyền: Giảng viên lớp A không thể tải bài của sinh viên lớp B, bảo vệ quyền sở hữu trí tuệ của người học.

#### Câu 43. Thang điểm chấm bài tập của Giảng viên trong hệ thống được thiết lập theo quy chế nào?

- **A.** Thang điểm 10 hệ số thập phân (`score` từ `0.0` đến `10.0`, ví dụ: `8.5`, `9.0`, `10.0`)
- **B.** Thang điểm 4
- **C.** Thang điểm 100
- **D.** Thang điểm chữ `A`, `B`, `C`, `D`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thang điểm 10 chuẩn mực trong hệ thống giáo dục đại học Việt Nam, cho phép lẻ 0.25 hoặc 0.5 điểm.

#### Câu 44. Khi Giảng viên nhập điểm số âm (ví dụ: `-2.0`) hoặc điểm vượt quá 10 (ví dụ: `15.0`), hệ thống xử lý ra sao?

- **A.** Hệ thống kiểm tra Validation chặn lại, báo lỗi 'Điểm số phải nằm trong khoảng từ 0.0 đến 10.0' và không ghi nhận dữ liệu sai.
- **B.** Tự động chia điểm đó cho 2.
- **C.** Vẫn lưu bình thường vào CSDL.
- **D.** Khóa máy tính của Giảng viên.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tầng phòng thủ dữ liệu kiểm tra tính hợp lý của điểm số, ngăn chặn sai sót nhập liệu do gõ nhầm bàn phím.

#### Câu 45. Cột `feedback` trong bảng `student_assignment_submissions` lưu trữ nội dung gì?

- **A.** Lời nhận xét, góp ý, phân tích lỗi sai và hướng dẫn cải thiện bài làm của Giảng viên dành riêng cho sinh viên đó.
- **B.** Số tài khoản ngân hàng của Giảng viên.
- **C.** Mã định danh của router Wi-Fi.
- **D.** Điểm rèn luyện của sinh viên.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Góp ý sư phạm trực tiếp giúp sinh viên hiểu rõ vì sao mình đạt được mức điểm đó và cần khắc phục những lỗi gì trong các buổi thực hành sau.

#### Câu 46. Khi Giảng viên nhấn nút 'Lưu Điểm & Nhận Xét' thành công, hệ thống tự động cập nhật những cột dữ liệu nào trong CSDL?

- **A.** Cập nhật đồng thời `score`, `feedback`, `graded_at = NOW()` và `graded_by = $_SESSION['user']['id']`.
- **B.** Chuyển sinh viên sang lớp khác.
- **C.** Chỉ cập nhật cột `score`.
- **D.** Xóa tệp bài nộp của sinh viên.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ghi nhận đầy đủ vết kiểm toán (Audit Trail): ai là người chấm, thời điểm chấm chính xác khi nào và nội dung đánh giá là gì.

#### Câu 47. Cột `graded_by` trong bảng `student_assignment_submissions` là khóa ngoại tham chiếu đến cột nào trong bảng `users`?

- **A.** `users.email`
- **B.** `users.id` (ID của Giảng viên thực hiện việc chấm bài)
- **C.** `users.phone`
- **D.** `users.student_code`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo đảm xác định chính xác danh tính người chấm điểm, phục vụ công tác đối soát điểm số cuối kỳ.

#### Câu 48. Sau khi Giảng viên lưu điểm thành công, sinh viên có thể xem được điểm số và nhận xét của mình ở đâu?

- **A.** Phải đến trực tiếp phòng Giảng viên xin giấy điểm.
- **B.** Phải đợi đến khi tốt nghiệp mới xem được.
- **C.** Hiển thị công khai tức thì ngay trên trang bài tập Cổng Sinh viên (`student/assignments.php`) khi sinh viên đăng nhập vào tài khoản của mình.
- **D.** Chỉ có phụ huynh sinh viên mới xem được qua SMS.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Số hóa minh bạch: sinh viên nhận được kết quả đánh giá ngay lập tức, tăng tính tương tác hai chiều trong học tập trực tuyến.

#### Câu 49. Giảng viên có được quyền sửa lại điểm số hoặc cập nhật thêm nhận xét sau khi đã chấm bài không?

- **A.** Phải nộp phạt tiền để được sửa điểm.
- **B.** Tuyệt đối không được sửa trong bất kỳ trường hợp nào.
- **C.** Được phép cập nhật lại điểm số và nhận xét bất cứ lúc nào trong thời gian học phần chưa kết thúc, hệ thống sẽ ghi đè điểm mới và cập nhật lại `graded_at`.
- **D.** Chỉ được sửa nếu sinh viên nộp đơn khiếu nại lên Bộ GD&ĐT.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hỗ trợ phúc khảo bài tập: Giảng viên có toàn quyền xem xét lại bài làm và cập nhật điểm số công bằng cho sinh viên.

#### Câu 50. Trên bảng danh sách bài nộp, các sinh viên chưa nộp bài được hiển thị trạng thái như thế nào để Giảng viên tiện theo dõi?

- **A.** Tự động bị gán 0 điểm.
- **B.** Hiển thị nhãn cảnh báo màu đỏ hoặc xám: 'Chưa nộp bài', ô tệp bài làm để trống.
- **C.** Bị xóa tên khỏi danh sách lớp.
- **D.** Hiển thị dấu sao vàng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giúp Giảng viên nắm bắt danh sách sinh viên vắng nộp bài để kịp thời nhắc nhở trong buổi học tiếp theo.

#### Câu 51. Tính năng 'Xuất Bảng Điểm' ra tệp CSV/Excel do Lê Hà My xây dựng hỗ trợ Giảng viên xuất những cột thông tin nào?

- **A.** Xuất đầy đủ: STT, Mã sinh viên, Họ và tên, Lớp sinh hoạt, Thời điểm nộp bài, Điểm số thang 10 và Lời nhận xét.
- **B.** Xuất mật khẩu của sinh viên.
- **C.** Chỉ xuất mỗi cột điểm số.
- **D.** Xuất lịch sử chat của sinh viên.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Định dạng CSV chuẩn mực dễ dàng mở bằng Microsoft Excel hoặc Google Sheets để nộp bảng điểm cho Văn phòng Khoa CNTT.

#### Câu 52. Hàm PHP nào được sử dụng để định dạng điểm số hiển thị đẹp mắt (ví dụ: hiển thị `8.5` thay vì `8.500000`) trên giao diện?

- **A.** `round($score, 1)` hoặc `(float)$score`
- **B.** `strlen($score)`
- **C.** `md5($score)`
- **D.** `explode('.', $score)`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ép kiểu sang float hoặc dùng `round(..., 1)` loại bỏ các số 0 thừa ở phần thập phân, mang lại giao diện tinh gọn, chuyên nghiệp.

#### Câu 53. Khi tải tệp bài làm của sinh viên có đuôi `.zip`, mã header PHP nào được gửi tới trình duyệt để thông báo đây là luồng tải tệp nhị phân?

- **A.** `header('Location: login.php');`
- **B.** `header('HTTP/1.1 404 Not Found');`
- **C.** `header('Content-Type: text/html');`
- **D.** `header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="...");`

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn RFC tải file: `application/octet-stream` kết hợp `Content-Disposition: attachment` chỉ thị trình duyệt lưu tệp xuống đĩa.

#### Câu 54. Để tránh việc Giảng viên tải về phải một tệp đã bị xóa mất trên đĩa cứng máy chủ, hệ thống thực hiện kiểm tra nào trước khi gửi file?

- **A.** Không cần kiểm tra, cứ để trình duyệt báo lỗi trắng trang.
- **B.** `if (!file_exists($filePath)) { die('Tệp không tồn tại hoặc đã bị di dời!'); }`
- **C.** Tải lại trang web 100 lần.
- **D.** Tự động tạo ra một tệp trắng 0 byte.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bắt ngoại lệ tệp không tồn tại một cách êm ái, hiển thị thông báo lỗi rõ ràng cho người dùng thay vì gây crash ứng dụng.

#### Câu 55. Nếu sinh viên nộp bài làm bị muộn so với hạn ban đầu (nộp trong thời gian mở lại hạn), giao diện chấm bài của Giảng viên đánh dấu bằng biểu tượng gì?

- **A.** Biểu tượng cấm thi.
- **B.** Biểu tượng tia chớp đỏ.
- **C.** Không có bất kỳ dấu hiệu nào.
- **D.** Nhãn cảnh báo màu vàng cam: '⚠️ Nộp muộn (Có phiếu cứu hộ được duyệt)' kèm số phút nộp trễ.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cung cấp đầy đủ thông tin bối cảnh cho Giảng viên: sinh viên nộp muộn do lý do chính đáng đã được xác thực, hỗ trợ Giảng viên quyết định trừ điểm hay không.

#### Câu 56. Thao tác gửi dữ liệu chấm điểm từ form của Giảng viên sử dụng phương thức HTTP nào?

- **A.** `TRACE`
- **B.** `POST` có kèm mã xác thực `csrf_token`
- **C.** `GET`
- **D.** `HEAD`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mọi thao tác ghi nhận điểm số trong trường học đều bắt buộc dùng HTTP POST có bảo vệ chống giả mạo request CSRF.

#### Câu 57. Khi Giảng viên muốn chấm bài cho nhiều sinh viên liên tục trong cùng một bảng (chấm điểm hàng loạt), giao diện hỗ trợ cơ chế nào?

- **A.** Tự động cho cả lớp 10 điểm.
- **B.** Bắt Giảng viên phải đến trường chấm điểm bằng bút đỏ.
- **C.** Bảng nhập điểm trực tiếp với các ô `<input name="scores[$id]">` và nút bấm 'Lưu tất cả điểm' chỉ với 1 lần click chuột.
- **D.** Phải mở 100 tab trình duyệt cùng lúc.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tối ưu năng suất giảng dạy: Giảng viên có thể chấm điểm nhanh cho 30-40 sinh viên ngay trên 1 trang duy nhất mà không phải click lưu từng người.

#### Câu 58. Khi xem tệp đề bài dạng PDF mà sinh viên nộp, Giảng viên có thể xem trực tiếp trên trình duyệt (Inline View) bằng thuộc tính nào?

- **A.** `Content-Type: text/plain;`
- **B.** `Content-Disposition: inline;`
- **C.** `Content-Disposition: attachment;`
- **D.** `Content-Length: 0;`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `Content-Disposition: inline` cho phép trình duyệt tận dụng bộ đọc PDF tích hợp sẵn để Giảng viên xem bài chấm ngay trên tab mà không cần tải về máy.

#### Câu 59. Bảng CSDL nào quản lý mối quan hệ giữa sinh viên với lớp sinh hoạt để xác định danh sách sinh viên của lớp học phần?

- **A.** `priorities`
- **B.** `classes` và trường `users.class_id`
- **C.** `holidays`
- **D.** `categories`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cột `users.class_id` trỏ đến `classes.id` xác định chính xác toàn bộ sinh viên đang sinh hoạt trong lớp đó.

#### Câu 60. Sau khi kỳ học kết thúc và Giảng viên đã chốt bảng điểm cuối cùng, Quản trị viên có thể làm gì để bảo vệ tính toàn vẹn của dữ liệu điểm?

- **A.** In điểm ra giấy rồi đốt hết máy chủ.
- **B.** Xóa toàn bộ mã nguồn website.
- **C.** Khóa lớp học phần hoặc chuyển trạng thái học kỳ sang 'Đã hoàn tất', chuyển quyền truy cập của Giảng viên về chế độ Chỉ đọc (Read-only).
- **D.** Xóa tài khoản Giảng viên.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chế độ khóa sổ điểm cuối kỳ theo đúng quy chế đào tạo đại học, ngăn chặn việc tự ý sửa điểm sau khi đã gửi Phòng Đào tạo.

## 4. THẨM QUYỀN THẨM ĐỊNH SỰ CỐ: QUY TRÌNH PHỐI HỢP KTV - GIẢNG VIÊN & MỞ LẠI DEADLINE

#### Câu 61. Quy trình xử lý một phiếu 'Cứu hộ Buổi học' (Lesson Rescue Ticket) diễn ra theo mô hình phối hợp nào trong hệ thống?

- **A.** Mô hình phối hợp 2 tầng: Kỹ thuật viên (KTV) giám định hạ tầng kỹ thuật -> Chuyển tiếp tới Giảng viên phụ trách bộ môn để thẩm định chuyên môn và quyết định gia hạn deadline.
- **B.** Sinh viên tự duyệt cho chính mình.
- **C.** KTV tự ý đổi điểm số của sinh viên mà không hỏi ý kiến Giảng viên.
- **D.** Bắt buộc phải qua phê duyệt của Thủ tướng Chính phủ.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phân định thẩm quyền rõ ràng theo ITIL: KTV chỉ xác thực có lỗi mạng/lỗi hệ thống hay không; còn quyền quyết định cho sinh viên nộp bù hay không thuộc về Giảng viên.

#### Câu 62. Tại tầng 1, Kỹ thuật viên (KTV) thực hiện thao tác gì đối với phiếu Cứu hộ Buổi học của sinh viên?

- **A.** Tắt máy chủ web.
- **B.** Xóa tài khoản của sinh viên.
- **C.** Kiểm tra nhật ký hệ thống (log), nếu đúng có sự cố kỹ thuật thì bấm 'KTV Xác nhận có lỗi' (hoặc nếu sinh viên báo sai thì bấm 'Hủy báo lỗi - SV báo sai').
- **D.** Chấm 10 điểm cho sinh viên.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** KTV đóng vai trò giám định viên kỹ thuật khách quan, bảo đảm sinh viên không lợi dụng lý do kỹ thuật để trốn tránh việc làm bài muộn.

#### Câu 63. Khi KTV bấm 'Hủy báo lỗi (SV báo sai)', phiếu cứu hộ được hệ thống chuyển sang trạng thái nào?

- **A.** `closed` với lý do `invalid_report_by_ktv` (Bác bỏ yêu cầu) và không gửi tiếp sang Giảng viên.
- **B.** `new`
- **C.** `resolved`
- **D.** `in_progress`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chặn đứng ngay các yêu cầu báo khống, tránh làm phiền thời gian làm việc của Giảng viên bộ môn.

#### Câu 64. Khi KTV bấm 'KTV Xác nhận có lỗi', thông báo và nhiệm vụ được hệ thống chuyển tiếp tới ai?

- **A.** Chuyển cho phòng tài vụ.
- **B.** Chuyển tiếp trực tiếp tới Cổng Giảng viên của thầy/cô đang phụ trách lớp học phần đó kèm nhãn 'Chờ Giảng viên duyệt gia hạn'.
- **C.** Chuyển cho tất cả các sinh viên trong trường.
- **D.** Chuyển cho công an giao thông.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giảng viên nhận được thông báo trong hàng đợi duyệt học phần, nắm bắt được sinh viên nào gặp sự cố và kết luận xác thực của KTV.

#### Câu 65. Thẩm quyền độc quyền nào sau đây CHỈ CÓ GIẢNG VIÊN phụ trách lớp học phần mới có quyền thực hiện?

- **A.** Xóa phiếu của toàn hệ thống.
- **B.** Quyết định 'Chấp thuận mở lại Deadline' (gia hạn thời gian nộp bù) hoặc 'Từ chối mở lại Deadline' cho sinh viên.
- **C.** Cài đặt phần mềm diệt virus.
- **D.** Đổi địa chỉ IP máy chủ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy chế đào tạo đại học quy định quyền quyết định hạn nộp bài thuộc về Giảng viên đứng lớp; KTV hoặc Admin không được can thiệp sâu vào chuyên môn học thuật.

#### Câu 66. Giao diện xử lý yêu cầu gia hạn của Giảng viên được thiết kế theo nguyên tắc nào để tránh thao tác nhầm lẫn và xung đột dữ liệu?

- **A.** Không có nút bấm nào.
- **B.** Giao diện bằng giọng nói.
- **C.** Giao diện thao tác một chiều (One-Way Action Interface): Sau khi Giảng viên bấm Chấp thuận hoặc Từ chối, hệ thống tự động khóa nút bấm và ghi nhận kết quả bất biến.
- **D.** Giao diện nhiều chiều cho phép bấm đi bấm lại 100 lần.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ngăn chặn triệt để hiện tượng Giảng viên bấm duyệt rồi lại bấm từ chối nhiều lần gây sai lệch trạng thái nộp bài của sinh viên.

#### Câu 67. Khi Giảng viên bấm nút 'Chấp thuận mở lại Deadline', Giảng viên cần nhập thông tin gì vào hộp thoại?

- **A.** Nhập số tài khoản ngân hàng của mình.
- **B.** Không cần nhập gì.
- **C.** Nhập tên các bạn cùng phòng.
- **D.** Chọn mốc thời gian gia hạn mới (ví dụ: gia hạn thêm 24 giờ kể từ thời điểm duyệt) và lời dặn dò cho sinh viên.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống tự động thiết lập hạn chót cá nhân hóa cho sinh viên đó nộp bù bài tập trong khoảng thời gian gia hạn được cấp phép.

#### Câu 68. Khi Giảng viên bấm nút 'Từ chối mở lại Deadline', lý do từ chối được hệ thống ghi nhận vào đâu?

- **A.** Ghi vào đĩa mềm.
- **B.** Xóa toàn bộ CSDL.
- **C.** Gửi tin nhắn cho Google.
- **D.** Ghi nhận vào cột phản hồi của phiếu và thông báo tới sinh viên lý do bị từ chối (ví dụ: 'Bài tập đã giao trước 2 tuần, sự cố 30 phút cuối không đủ căn cứ gia hạn').

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính sư phạm minh bạch: sinh viên nhận được giải thích rõ ràng từ Giảng viên về quyết định bác bỏ yêu cầu.

#### Câu 69. Sau khi Giảng viên chấp thuận mở lại Deadline cho sinh viên, form nộp bài trên Cổng Sinh viên phản ứng như thế nào?

- **A.** Tiếp tục bị khóa cứng.
- **B.** Tự động nộp bài làm mẫu của Giảng viên.
- **C.** Báo lỗi máy chủ 500.
- **D.** Tự động mở khóa trở lại (Re-enabled), cho phép sinh viên tải tệp bài làm lên trong khoảng thời gian gia hạn.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Luồng dữ liệu khép kín: Giảng viên duyệt gia hạn -> Cổng Sinh viên mở form -> Sinh viên nộp bài bù thành công.

#### Câu 70. Khi Giảng viên cần báo sự cố khẩn cấp về thiết bị giảng đường (như máy chiếu hỏng trong giờ dạy), Giảng viên chọn danh mục nào?

- **A.** Tâm sự tuổi hồng.
- **B.** Hỏi đáp thủ tục ra trường.
- **C.** Đăng ký ký túc xá.
- **D.** Thiết bị phòng máy / Giảng đường (mức ưu tiên tự động kích hoạt HIGH hoặc URGENT).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Danh mục sự cố giảng đường gắn liền với cam kết SLA phản ứng nhanh trong 15 phút của KTV trực ca.

#### Câu 71. Đường dẫn ưu tiên 'Academic Fast-Track' dành riêng cho Giảng viên hoạt động như thế nào trong thuật toán điều phối?

- **A.** Gửi thư mời dự tiệc.
- **B.** Bỏ qua hàng đợi thông thường, đẩy phiếu lên vị trí ưu tiên số 1 của KTV đang trực ca và phát chuông cảnh báo trên màn hình KTV.
- **C.** Tự động tắt máy tính của sinh viên đang học.
- **D.** Không có tác dụng gì.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đảm bảo tiết học trên lớp của Giảng viên không bị gián đoạn quá lâu do sự cố kỹ thuật hạ tầng trường học.

#### Câu 72. Sau khi KTV khắc phục xong sự cố máy chiếu tại phòng học cho Giảng viên, ai là người nghiệm thu chất lượng CSAT?

- **A.** Admin tự chấm điểm cho KTV.
- **B.** Chính Giảng viên đã tạo phiếu đó nghiệm thu thực tế trên máy chiếu và chấm điểm sao CSAT cho KTV.
- **C.** Bảo vệ cổng trường.
- **D.** Sinh viên lớp khác.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy tắc ITIL nhất quán: Người yêu cầu (Giảng viên) là người thụ hưởng dịch vụ và có quyền nghiệm thu chất lượng.

#### Câu 73. Nếu KTV phát hiện một sinh viên cố tình tạo phiếu giả mạo sự cố để xin mở lại hạn nộp bài, KTV sử dụng tính năng nào?

- **A.** Xóa tài khoản của Giảng viên.
- **B.** Đánh nhau với sinh viên.
- **C.** Kích hoạt nút 'Báo cáo vi phạm quy chế' (Student Violation Report), đánh dấu cờ `flagged_as_violation = 1` gửi Admin xem xét kỷ luật.
- **D.** Cho sinh viên 10 điểm.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính năng Báo cáo vi phạm bảo đảm kỷ cương học thuật, ngăn chặn hành vi gian lận công nghệ trong thi cử và nộp bài.

#### Câu 74. Khi một sinh viên bị KTV báo cáo vi phạm quy chế, Giảng viên bộ môn có nắm được thông tin này không?

- **A.** Chỉ Admin mới biết.
- **B.** Không thể biết được.
- **C.** Có; trên giao diện chấm bài của Giảng viên xuất hiện nhãn cảnh báo '⚠️ Sinh viên có hành vi báo cáo sai sự cố kỹ thuật' để Giảng viên lưu ý khi đánh giá điểm chuyên cần.
- **D.** Toàn bộ sinh viên trong trường nhận được tin nhắn.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chia sẻ thông tin nghiệp vụ đa chiều giữa bộ phận kỹ thuật và giảng viên giúp quản lý chất lượng đào tạo chặt chẽ.

#### Câu 75. Nếu sinh viên bị gắn nhãn vi phạm nhưng thực tế bị oan (do mạng trường chập chờn thật), hệ thống cung cấp cơ chế nào?

- **A.** Sinh viên phải hack vào máy chủ trường.
- **B.** Cơ chế Giải oan (Clear Violation Flag): Admin hoặc KTV sau khi kiểm tra lại log có thể bấm 'Gỡ cờ vi phạm' và phục hồi danh dự học tập cho sinh viên.
- **C.** Sinh viên phải chịu oan suốt đời.
- **D.** Phải chuyển sang trường khác học.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo đảm tính công bằng, nhân văn trong môi trường giáo dục: có chế tài kỷ luật nhưng luôn có cơ chế giải oan khi có bằng chứng mới.

#### Câu 76. Thao tác Chấp thuận mở lại Deadline của Giảng viên có làm thay đổi hạn nộp `due_date` gốc của CẢ LỚP không?

- **A.** Không; hạn `due_date` gốc của lớp vẫn giữ nguyên, hệ thống chỉ cấp quyền nộp bài ngoại lệ (Exception Extension) cho RIÊNG sinh viên có phiếu cứu hộ được duyệt.
- **B.** Tự động đổi tên môn học.
- **C.** Làm thay đổi hạn của cả lớp khiến các sinh viên khác phải nộp lại.
- **D.** Xóa hạn nộp của cả lớp.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế cấp quyền nộp bù cá thể hóa, không làm xáo trộn tiến độ học tập và nộp bài đúng hạn của các sinh viên khác trong lớp.

#### Câu 77. Trong bảng `tickets`, cột nào lưu trữ kết quả phê duyệt gia hạn của Giảng viên đối với phiếu cứu hộ buổi học?

- **A.** `mac_address`
- **B.** `password_hash`
- **C.** `sla_factor`
- **D.** `lecturer_approved` kiểu `ENUM('pending', 'approved', 'rejected')`

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trường `lecturer_approved` lưu trữ rõ ràng 3 trạng thái: Đang chờ duyệt, Đã chấp thuận gia hạn, hoặc Đã từ chối.

#### Câu 78. Giảng viên có thể xem danh sách tất cả các yêu cầu cứu hộ buổi học của sinh viên lớp mình tại trang nào?

- **A.** `database/seed.sql`
- **B.** `lecturer/rescue_requests.php` (hoặc tab 'Cứu hộ nộp bài' trên Bàn làm việc Giảng viên)
- **C.** `public/css/style.css`
- **D.** `admin/users.php`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Gom nhóm toàn bộ các đơn xin mở lại hạn nộp của sinh viên vào 1 bảng trực quan để Giảng viên xét duyệt tập trung nhanh chóng.

#### Câu 79. Khi Giảng viên bấm duyệt chấp thuận mở lại Deadline, KTV đang phụ trách phiếu đó nhận được cập nhật gì?

- **A.** Phiếu tự động biến mất.
- **B.** Nhận được thông báo 'Giảng viên đã chấp thuận gia hạn cho sinh viên', KTV có thể yên tâm chuyển phiếu sang trạng thái `resolved` để sinh viên nghiệm thu.
- **C.** KTV bị khóa tài khoản.
- **D.** Bị trừ tiền lương.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hoàn tất quy trình phối hợp liên phòng ban: KTV nắm được kết quả thẩm định của Giảng viên để kết thúc phiếu hỗ trợ theo quy định.

#### Câu 80. Mô hình giải quyết sự cố nộp bài 2 tầng (KTV giám định + Giảng viên quyết định) trong hệ thống Helpdesk Nhóm 1 giải quyết triệt để vấn đề gì trong các trường đại học?

- **A.** Giải quyết dứt điểm tình trạng sinh viên đổ lỗi cho 'nghẽn mạng' để xin nộp trễ bài, đồng thời giảm tải việc Giảng viên phải tự mình đi kiểm tra log kỹ thuật máy chủ.
- **B.** Vấn đề thiếu bãi đỗ xe của trường.
- **C.** Vấn đề thiếu bảng viết phấn trong lớp.
- **D.** Vấn đề tăng giá đồ ăn căng tin.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ứng dụng chuyển đổi số xuất sắc: Kỹ thuật lo việc kỹ thuật, Chuyên môn lo việc chuyên môn, đảm bảo công bằng tuyệt đối cho mọi sinh viên.

## 5. PHÂN TÍCH 7 TEST CASES CHUYÊN SÂU & CÁC CÂU LỆNH TRUY VẤN SQL CỦA LÊ HÀ MY

#### Câu 81. Trong tài liệu kiểm thử của Lê Hà My (`LeHaMy_testcase_course_assignment.md`), Test Case 1 kiểm tra trường hợp nào?

- **A.** Chạy script chiếm quyền root.
- **B.** Tải mã độc lên máy chủ.
- **C.** Thêm phân công giảng dạy thành công với dữ liệu hợp lệ (chọn lớp có sẵn, chọn môn, chọn giảng viên, nhập học kỳ 2026_1).
- **D.** Xóa toàn bộ điểm số của trường.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 1 xác nhận luồng thêm mới phân công hợp lệ hoạt động chuẩn xác, xuất hiện thông báo thành công và bản ghi mới hiển thị trên danh sách.

#### Câu 82. Test Case 2 của Lê Hà My kiểm tra kịch bản vi phạm ràng buộc nào trong CSDL?

- **A.** Vi phạm thời gian đăng nhập quá 5 phút.
- **B.** Vi phạm quy chế phòng cháy chữa cháy.
- **C.** Vi phạm dung lượng ổ cứng.
- **D.** Trùng phân công (vi phạm ràng buộc UNIQUE tổ hợp `class_id + course_id + semester`), hệ thống báo lỗi 'Lớp học phần này đã có giảng viên phụ trách'.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Test Case 2 bảo đảm tính toàn vẹn của nguyên tắc ITIL 1 lớp 1 GV: không thể phân công 2 người cùng quản lý 1 lớp học phần.

#### Câu 83. Trong Test Case 3 của Hà My, khi người dùng bấm Lưu nhưng bỏ trống các trường bắt buộc (Lớp học, Môn học, Giảng viên), hệ thống phản ứng ra sao?

- **A.** Báo lỗi cụ thể: 'Vui lòng chọn Lớp học', 'Vui lòng chọn Môn học', 'Vui lòng chọn Giảng viên phụ trách' và không tạo bản ghi mới.
- **B.** Vẫn lưu vào CSDL các giá trị rỗng.
- **C.** Tự động chọn ngẫu nhiên người dùng bất kỳ.
- **D.** Chuyển hướng về trang chủ.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểm tra tính hoàn thiện của tầng Validation form dữ liệu đầu vào.

#### Câu 84. Test Case 4 của Lê Hà My kiểm tra sự kết hợp của những tính năng nào trên danh sách phân công giảng dạy?

- **A.** Tính năng đổi màu nền trang web.
- **B.** Tìm kiếm theo từ khóa (mã môn `WEB101` hoặc tên giảng viên) kết hợp tính năng phân trang khi danh sách có hơn 8 bản ghi.
- **C.** Tính năng chat bot trả lời tự động.
- **D.** Tính năng phát nhạc nền khi duyệt web.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đảm bảo khả năng tra cứu nhanh chóng và tải trang mượt mà khi nhà trường có hàng trăm phân công giảng dạy mỗi kỳ.

#### Câu 85. Trong Test Case 5 của Hà My, hành động Xóa phân công giảng dạy bắt buộc phải gửi bằng phương thức HTTP nào?

- **A.** Phương thức `PUT` không cần token.
- **B.** Phương thức `POST` có hộp thoại JavaScript xác nhận và kiểm tra token CSRF.
- **C.** Phương thức `GET` qua đường link `<a href="delete.php?id=1">`
- **D.** Phương thức `TRACE`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nghiêm cấm xóa bằng GET để ngăn ngừa việc bot thu thập dữ liệu (Web Crawler) hoặc mã độc vô tình kích hoạt lệnh xóa ngoài ý muốn.

#### Câu 86. Test Case 6 của Lê Hà My kiểm tra khả năng lọc dữ liệu theo những tiêu chí nào?

- **A.** Lọc theo số lượng ổ cắm điện trong phòng.
- **B.** Lọc theo độ dài của tên môn học.
- **C.** Lọc theo năm sinh của Hiệu trưởng.
- **D.** Lọc phân công đồng thời theo Lớp học và Giảng viên, bảo lưu điều kiện lọc khi chuyển trang và khi xuất dữ liệu ra file CSV.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính nhất quán trong trải nghiệm người dùng: bộ lọc được bảo toàn khi người dùng phân trang hoặc bấm nút xuất file báo cáo.

#### Câu 87. Test Case 7 của Lê Hà My kiểm tra tính năng liên kết quản lý danh mục nào từ trang phân công?

- **A.** Quản lý vé xe buýt của sinh viên.
- **B.** Quản lý phòng nghỉ của giáo viên.
- **C.** Chuyển hướng nhanh tới trang 'Quản Lý Lớp & Môn' để thêm lớp sinh hoạt mới hoặc môn học mới khi phòng đào tạo mở thêm mã ngành.
- **D.** Quản lý thực đơn nhà ăn.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính liên thông thuận tiện trong quản trị: Admin có thể bổ sung ngay môn học hoặc lớp học mới mà không phải tìm kiếm qua nhiều menu con.

#### Câu 88. Câu lệnh truy vấn SQL số 1 trong tệp `LeHaMy_CauLenhTruyVan_GiangVien.sql` lấy danh sách phiếu do Giảng viên tạo với mệnh đề sắp xếp đặc thù nào?

- **A.** `ORDER BY p.name`
- **B.** `ORDER BY t.id ASC`
- **C.** `ORDER BY RAND()`
- **D.** `ORDER BY FIELD(p.code, 'URGENT', 'HIGH', 'MED', 'LOW'), t.created_at DESC`

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `FIELD()` trong SQL giúp sắp xếp tùy biến theo thứ tự ưu tiên nghiệp vụ khẩn cấp: URGENT -> HIGH -> MED -> LOW, bảo đảm sự cố nghiêm trọng luôn nổi bật trên đầu.

#### Câu 89. Trong câu truy vấn SQL số 1 của Hà My, điều kiện `WHERE u.user_type = 'lecturer'` đảm bảo điều gì?

- **A.** Chỉ lấy các phiếu do người dùng thuộc ngạch Giảng viên tạo ra trên hệ thống.
- **B.** Lấy tất cả các phiếu của sinh viên toàn trường.
- **C.** Lấy các phiếu đã bị xóa.
- **D.** Lấy các phiếu do robot tự động tạo.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trích xuất riêng danh sách sự cố học thuật do đội ngũ Giảng viên phản ánh để phục vụ báo cáo định kỳ cho Ban Giám hiệu.

#### Câu 90. Câu lệnh truy vấn SQL số 2 của Lê Hà My dùng để kiểm tra thông tin phân công giảng dạy trong học kỳ nào?

- **A.** `ca.semester = '2020_1'`
- **B.** `ca.academic_year = '1999'`
- **C.** `ca.semester = '2026_1'` (Học kỳ 1 năm học 2026-2027)
- **D.** `ca.semester IS NULL`

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khớp đúng với dữ liệu thực tế đang vận hành của lớp học phần Lập trình Web Nhóm 1.

#### Câu 91. Đoạn mã SQL số 2 của Hà My thực hiện phép `JOIN` giữa bảng `course_assignments` với những bảng nào để hiển thị đầy đủ tên lớp, tên môn và tên giảng viên?

- **A.** `JOIN classes cl ON ca.class_id = cl.id JOIN courses co ON ca.course_id = co.id JOIN users u ON ca.lecturer_id = u.id`
- **B.** Chỉ liên kết với bảng `roles`.
- **C.** `CROSS JOIN tickets`
- **D.** Chỉ liên kết với bảng `holidays`.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phép nối 3 bảng chuẩn mực để giải mã các khóa ngoại thành tên gọi văn bản có ý nghĩa đối với người dùng.

#### Câu 92. Câu lệnh truy vấn SQL số 3 của Hà My sử dụng hàm `TIMESTAMPDIFF(MINUTE, t.sla_started_at, NOW())` nhằm mục đích gì?

- **A.** Tính số phút đồng hồ cam kết SLA đã chạy trước khi phiếu rơi vào trạng thái đóng băng `waiting_requester` (Chờ người dùng phản hồi).
- **B.** Tính thời gian bảo hành của máy chiếu.
- **C.** Tính số tuổi của sinh viên.
- **D.** Tính số phút KTV đi muộn.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phục vụ động cơ tính SLA thông minh: khi phiếu tạm dừng, hệ thống phải chốt lại số phút đã trôi qua để khi mở lại chỉ tính tiếp phần thời gian còn lại.

#### Câu 93. Câu lệnh truy vấn SQL số 4 của Hà My truy vấn bảng `holidays` với điều kiện lọc nào để lấy danh sách ngày nghỉ lễ tính SLA?

- **A.** `WHERE YEAR(start_date) = 2026 OR is_recurring = 1 ORDER BY start_date;`
- **B.** `WHERE 1=0`
- **C.** `WHERE id > 100`
- **D.** `WHERE name LIKE '%Tết%'`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lấy toàn bộ các ngày lễ trong năm 2026 và các ngày lễ lặp lại hàng năm (như Quốc khánh 2/9, 30/4 - 1/5) để SlaService trừ khỏi thời gian xử lý của KTV.

#### Câu 94. Biểu thức `DATEDIFF(end_date, start_date) + 1 AS so_ngay_nghi` trong câu truy vấn số 4 của Hà My có ý nghĩa gì?

- **A.** Tính số tiền thưởng ngày lễ.
- **B.** Tính số ngày sinh viên được nghỉ hè.
- **C.** Tính tổng số ngày nghỉ liên tục của đợt nghỉ lễ đó (cộng thêm 1 ngày vì tính cả ngày bắt đầu).
- **D.** Tính số giờ làm thêm của KTV.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Toán tử chuẩn tính số ngày lịch nghỉ lễ trong MySQL: ví dụ nghỉ từ ngày 30/4 đến 1/5 là `(1 - 0) + 1 = 2` ngày nghỉ.

#### Câu 95. Câu lệnh truy vấn SQL số 5 của Hà My sử dụng mệnh đề `GROUP BY` nào để thống kê phiếu của Giảng viên?

- **A.** `GROUP BY t.description`
- **B.** `GROUP BY u.name, c.code, c.name, c.default_priority_code, p.code, t.status`
- **C.** `GROUP BY t.id`
- **D.** `GROUP BY NOW()`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Gom nhóm đa chiều phục vụ lập bảng biểu phân tích sự cố theo Giảng viên, Danh mục dịch vụ, Mức ưu tiên và Trạng thái hoàn thành.

#### Câu 96. Trong bảng `course_assignments`, tại sao không nên dùng `class_name` và `course_name` trực tiếp mà phải dùng `class_id` và `course_id`?

- **A.** Do quy định của mạng máy tính.
- **B.** Để làm cho câu lệnh SQL phức tạp hơn.
- **C.** Vì MySQL không cho phép lưu chữ.
- **D.** Tuân thủ chuẩn hóa 3NF: dùng khóa ngoại dạng số nguyên (INT) giúp tối ưu hóa hiệu năng lập chỉ mục (B-Tree Index), tiết kiệm bộ nhớ và tránh lỗi khi đổi tên môn học.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa ngoại dạng số nguyên luôn có tốc độ tìm kiếm và so khớp nhanh gấp nhiều lần so với so sánh chuỗi văn bản (String Comparison).

#### Câu 97. Khi thực hiện câu lệnh xóa phân công giảng dạy bằng PDO, mã nguồn của Hà My sử dụng phương thức nào để thực thi câu lệnh an toàn?

- **A.** `system("mysql -e 'DROP TABLE'");`
- **B.** `$db->query("DELETE FROM course_assignments WHERE id = " . $_GET['id']);`
- **C.** `$stmt = $db->prepare("DELETE FROM course_assignments WHERE id = :id"); $stmt->execute([':id' => $id]);`
- **D.** `file_get_contents('delete.php');`

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng PDO Prepared Statements với tham số buộc `:id` loại trừ 100% nguy cơ tấn công SQL Injection.

#### Câu 98. Nếu Quản trị viên nhập từ khóa tìm kiếm phân công có chứa ký tự đặc biệt như dấu nháy đơn `'` hoặc dấu `%`, hệ thống của Hà My xử lý thế nào?

- **A.** Xóa toàn bộ bản ghi khớp từ khóa.
- **B.** Khóa tài khoản Quản trị viên.
- **C.** Hệ thống tự động bao bọc trong Prepared Statements và escape an toàn, chỉ tìm kiếm chính xác các bản ghi có chứa ký tự đó mà không bị phá vỡ cấu trúc câu truy vấn.
- **D.** Báo lỗi cú pháp SQL Syntax Error.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Prepared Statements tách rời hoàn toàn giữa lệnh SQL và dữ liệu tham số, vô hiệu hóa mọi nỗ lực chèn ký tự điều khiển độc hại.

#### Câu 99. Trong quy trình chấm điểm bài tập, nếu Giảng viên phát hiện 2 sinh viên nộp 2 tệp bài làm có nội dung giống hệt nhau (sao chép bài), Giảng viên có thể ghi nhận điều này ở đâu?

- **A.** Xóa môn học đó khỏi trường.
- **B.** Bắt 2 sinh viên phải xóa tài khoản.
- **C.** Chỉ có thể ghi nhớ trong đầu.
- **D.** Nhập điểm `score = 0.0` và ghi chú rõ trong ô `feedback`: 'Phát hiện sao chép bài của sinh viên B, vi phạm quy chế học phần'.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ghi nhận lời phê chính thức vào hệ thống giúp sinh viên nhận thức được lỗi sai và có căn cứ minh bạch nếu có khiếu nại điểm số.

#### Câu 100. Tổng kết lại, đóng góp chuyên môn cốt lõi của sinh viên Lê Hà My (224001735) trong Đồ án Helpdesk Nhóm 1 là gì?

- **A.** Không tham gia vào quá trình lập trình hệ thống.
- **B.** Xây dựng trọn vẹn Cổng Giảng viên & Quản lý Phân công Giảng dạy chuẩn ITIL (1 lớp 1 GV), thiết kế CSDL các bảng `course_assignments`, `course_lessons`, `student_assignment_submissions`, xây dựng quy trình giao bài/chấm điểm thang 10 và giải pháp thẩm định mở lại deadline phối hợp KTV.
- **C.** Chỉ làm nhiệm vụ điểm danh sinh viên.
- **D.** Chỉ đi in tài liệu cho nhóm.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lê Hà My hoàn thành xuất sắc 20% khối lượng dự án với các module học thuật vững chắc, phân quyền chặt chẽ và đóng góp to lớn vào sự thành công của BTL Nhóm 1.


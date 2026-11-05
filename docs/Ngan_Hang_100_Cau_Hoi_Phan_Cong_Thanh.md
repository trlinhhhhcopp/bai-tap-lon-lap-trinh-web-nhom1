# BỘ 100 CÂU HỎI TRẮC NGHIỆM CHUYÊN SÂU &ndash; PHAN CÔNG THÀNH (224001746)

> **Thành viên phụ trách:** Phan Công Thành (MSV: 224001746 &ndash; Lớp: TUD D2024)
> **Phân hệ chuyên trách:** Trung Tâm Quản Trị, Quản Lý Ngày Nghỉ Lễ (holidays), Thông Báo Sự Cố Diện Rộng & Nhập Dữ Liệu Excel/CSV
> **Bảng CSDL cốt lõi:** `holidays`, `notifications`
> **Model phụ trách:** `Holiday.php`

---

## 📑 MỤC LỤC 5 DẠNG CHUYÊN ĐỀ (100 CÂU HỎI MỚI)

- **[Chuyên đề 1: 1. Quản Trị Ngày Nghỉ Lễ (holidays) & Tích Hợp Động Cơ Tính SLA Giờ Hành Chính](#chuyen-de-1)** (20 câu)
- **[Chuyên đề 2: 2. Trung Tâm Thông Báo & Phát / Thu Hồi Cảnh Báo Sự Cố Khẩn Cấp Diện Rộng](#chuyen-de-2)** (20 câu)
- **[Chuyên đề 3: 3. Công Cụ Nhập Dữ Liệu Tài Khoản Hàng Loạt Bằng Excel / CSV & Xử Lý Đồng Bộ](#chuyen-de-3)** (20 câu)
- **[Chuyên đề 4: 4. Quản Lý Ca Trực KTV & Phân Quyền Độc Quyền Admin Bật / Tắt Trực Ca](#chuyen-de-4)** (20 câu)
- **[Chuyên đề 5: 5. Phân Tích 5 Test Cases Chuyên Sâu & Các Câu Lệnh Truy Vấn SQL Của Phan Công Thành](#chuyen-de-5)** (20 câu)
- **[Bảng Đáp Án Chi Tiết 100 Câu](#bang-dap-an)**

---

## <a id="chuyen-de-1"></a>1. Quản Trị Ngày Nghỉ Lễ (holidays) & Tích Hợp Động Cơ Tính SLA Giờ Hành Chính

#### Câu 1. Bảng CSDL `holidays` do Phan Công Thành phụ trách thiết kế chứa những trường dữ liệu cốt lõi nào?

- **A.** `id`, `student_name`, `score`
- **B.** `id`, `name`, `start_date`, `end_date`, `is_recurring`, `created_at`
- **C.** `id`, `ip_address`, `mac_address`
- **D.** `id`, `password_hash`, `token`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `holidays` lưu trữ thông tin các ngày nghỉ lễ của trường: tên ngày lễ, ngày bắt đầu, ngày kết thúc và cờ đánh dấu lặp lại hàng năm (`is_recurring`).

#### Câu 2. Cột `is_recurring` trong bảng `holidays` nhận giá trị kiểu gì và mang ý nghĩa nghiệp vụ gì?

- **A.** `VARCHAR(50)` lưu tên của quốc gia.
- **B.** `TINYINT(1)` (hoặc BOOLEAN), nhận giá trị 1 nếu là ngày lễ cố định lặp lại hàng năm theo dương lịch (như Quốc khánh 2/9, 30/4 - 1/5).
- **C.** `INT` lưu số ngày nghỉ.
- **D.** `DATETIME` lưu giờ nghỉ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cờ `is_recurring = 1` giúp thuật toán SLA tự động áp dụng ngày nghỉ cho các năm tiếp theo mà Quản trị viên không cần phải nhập lại bằng tay hàng năm.

#### Câu 3. Trong thuật toán tính hạn cam kết SLA hành chính (`SlaService.php`), những khoảng thời gian nào được tự động LOẠI TRỪ khỏi thời gian đếm giờ của KTV?

- **A.** Chỉ loại trừ ban đêm.
- **B.** Ngoài giờ làm việc (sau 17h30 đến trước 8h00 sáng hôm sau), Thứ Bảy, Chủ Nhật và toàn bộ các ngày lễ cấu hình trong bảng `holidays`.
- **C.** Không loại trừ bất kỳ ngày nào, bắt KTV trực 24/7.
- **D.** Chỉ loại trừ ngày sinh nhật của Giám đốc.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Động cơ SLA thông minh bảo vệ quyền lợi KTV: chỉ tính giờ làm việc thực tế trong khung giờ hành chính (8h00 - 12h00 và 13h30 - 17h30), bỏ qua ngày nghỉ.

#### Câu 4. Khi người dùng gửi phiếu hỗ trợ vào lúc 21h00 tối Thứ Sáu, thuật toán `SlaService::calculateDueAt()` sẽ thiết lập mốc bắt đầu tính giờ (`sla_started_at`) vào thời điểm nào?

- **A.** Bắt đầu tính ngay lập tức từ 21h00 tối Thứ Sáu.
- **B.** Tự động dời mốc bắt đầu sang 8h00 sáng Thứ Hai tuần làm việc tiếp theo (nếu Thứ Hai không phải ngày lễ).
- **C.** Bắt đầu tính từ 0h00 sáng Thứ Bảy.
- **D.** Hủy phiếu đó vì ngoài giờ làm việc.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phiếu gửi ngoài giờ hành chính hoặc cuối tuần tự động được dời sang đầu giờ làm việc của ngày hành chính kế tiếp để bắt đầu tính cam kết SLA.

#### Câu 5. Khi kiểm tra một ngày `$currentDate` có phải ngày nghỉ lễ hay không, hàm `isHoliday()` trong `SlaService.php` thực hiện truy vấn như thế nào?

- **A.** `SELECT * FROM users`
- **B.** So khớp xem `$currentDate` có nằm trong khoảng `[start_date, end_date]` của bảng `holidays` (bao gồm cả điều kiện `YEAR(start_date) = :year` hoặc `is_recurring = 1`).
- **C.** Hỏi ý kiến của sinh viên qua chatbot.
- **D.** Mặc định trả về `true` cho mọi ngày trong tuần.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thuật toán đối soát ngày hiện tại với bảng dữ liệu nghỉ lễ do Admin quản lý để quyết định cộng dồn thêm thời gian cho KTV.

#### Câu 6. Nếu đợt nghỉ lễ Quốc khánh 2/9 kéo dài từ ngày 01/09/2026 đến hết ngày 03/09/2026, giá trị lưu vào cột `start_date` và `end_date` lần lượt là gì?

- **A.** `start_date = '2026-09-03'`, `end_date = '2026-09-01'`
- **B.** `start_date = '2026-09-01'`, `end_date = '2026-09-03'`
- **C.** `start_date = '01/09/2026'`, `end_date = '03/09/2026'`
- **D.** `start_date = '2026'`, `end_date = '2026'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Định dạng chuẩn `YYYY-MM-DD` của kiểu dữ liệu `DATE` trong MySQL bảo đảm việc so sánh chuỗi ngày tháng diễn ra chính xác tuyệt đối.

#### Câu 7. Khi Admin thêm mới một ngày nghỉ lễ, hệ thống thực hiện kiểm tra ràng buộc ngày nào để tránh lỗi logic?

- **A.** Bắt buộc `start_date` phải nhỏ hơn năm 2000.
- **B.** Kiểm tra `start_date <= end_date`; nếu ngày bắt đầu lớn hơn ngày kết thúc, hệ thống từ chối và báo lỗi 'Ngày bắt đầu hoặc ngày kết thúc không hợp lệ'.
- **C.** Bắt buộc ngày kết thúc phải rơi vào Thứ Bảy.
- **D.** Không kiểm tra gì.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy tắc kiểm tra ngày hợp lệ (Validation) ngăn chặn tình huống nhập ngược thời gian gây hỏng thuật toán vòng lặp cộng ngày của SLA.

#### Câu 8. Nếu một sự cố có SLA chuẩn là 2 giờ được gửi lúc 16h30 chiều Thứ Sáu (trước giờ tan làm 1 giờ), hạn chót cam kết `due_at` sẽ rơi vào thời điểm nào?

- **A.** 18h30 tối Thứ Sáu.
- **B.** 9h00 sáng Thứ Hai tuần sau (xử lý 30 phút cuối Thứ Sáu từ 16h30-17h00, và 1h30 phút còn lại từ 8h00-9h30 sáng Thứ Hai).
- **C.** 12h00 trưa Thứ Bảy.
- **D.** 23h59 đêm Chủ Nhật.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thuật toán ngắt quãng: thời gian còn thiếu sau giờ hành chính Thứ Sáu được chuyển tiếp liền mạch sang đầu giờ sáng ngày Thứ Hai.

#### Câu 9. Controller nào trong mã nguồn chịu trách nhiệm tiếp nhận và xử lý các thao tác CRUD ngày nghỉ lễ?

- **A.** `StudentController`
- **B.** `PhanCongThanh_Admin\Controllers\Admin\HolidayController`
- **C.** `GuestTicketController`
- **D.** `RatingController`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Controller chuyên biệt nằm trong phân hệ Admin do Phan Công Thành xây dựng theo chuẩn kiến trúc MVC hướng đối tượng.

#### Câu 10. Để Admin tìm kiếm nhanh một ngày nghỉ lễ theo tên (ví dụ: tìm các đợt nghỉ 'Tết'), câu lệnh SQL sử dụng toán tử nào?

- **A.** `WHERE name = 'Tet'` (So sánh bằng tuyệt đối)
- **B.** `WHERE name LIKE :keyword` với tham số buộc `%Tet%`
- **C.** `WHERE name IS NULL`
- **D.** `WHERE id IN ('Tet')`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tìm kiếm mờ (Fuzzy Search) với toán tử `LIKE %keyword%` giúp Admin tìm thấy cả 'Tết Dương Lịch', 'Tết Nguyên Đán' chỉ với từ khóa 'Tết'.

#### Câu 11. Khi Admin thực hiện xóa một ngày nghỉ lễ, giao diện quản trị gửi yêu cầu bằng phương thức HTTP nào để bảo đảm an toàn?

- **A.** `GET`
- **B.** `POST` có kèm mã xác thực `csrf_token` và hộp thoại xác nhận JavaScript
- **C.** `HEAD`
- **D.** `OPTIONS`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn bảo mật bài tập lớn: Nghiêm cấm xóa dữ liệu bằng GET, luôn dùng HTTP POST có token CSRF bảo vệ.

#### Câu 12. Trong bảng `holidays`, cột `id` được thiết lập thuộc tính gì để tự động tăng định danh?

- **A.** `AUTO_INCREMENT` kết hợp `PRIMARY KEY`
- **B.** `FOREIGN KEY`
- **C.** `DEFAULT NULL`
- **D.** `UNIQUE NOT NULL`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khóa chính tự tăng giúp quản lý các ngày nghỉ lễ độc lập, không bị trùng lặp mã định danh.

#### Câu 13. Khi một ngày nghỉ lễ kéo dài trong đúng 1 ngày duy nhất (ví dụ ngày Nhà giáo Việt Nam 20/11), giá trị của `start_date` và `end_date` như thế nào?

- **A.** `start_date` bằng `end_date` (ví dụ: `2026-11-20`)
- **B.** `end_date` phải để trống NULL
- **C.** `start_date` phải lớn hơn `end_date` 1 ngày
- **D.** Bắt buộc phải nhập 2 ngày khác nhau

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống tính số ngày nghỉ qua công thức `DATEDIFF(end_date, start_date) + 1 = 0 + 1 = 1` ngày nghỉ trọn vẹn.

#### Câu 14. Tại sao các ngày nghỉ lễ cấu hình trong bảng `holidays` lại có độ ưu tiên cao hơn lịch làm việc thông thường?

- **A.** Vì ngày lễ trường đóng cửa, toàn bộ cán bộ và KTV được nghỉ theo quy định của Luật Lao động và Nhà trường.
- **B.** Vì máy tính không hoạt động được vào ngày lễ.
- **C.** Để làm cho CSDL phức tạp hơn.
- **D.** Không có lý do gì.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phù hợp với môi trường thực tế của trường đại học công lập: tôn trọng các ngày nghỉ lễ chính thức của quốc gia và của trường.

#### Câu 15. Khi cập nhật (Sửa) thông tin một ngày nghỉ lễ, câu lệnh SQL PDO Prepared Statement được viết như thế nào?

- **A.** `UPDATE holidays SET name = $_POST['name']`
- **B.** `UPDATE holidays SET name = :name, start_date = :start_date, end_date = :end_date, is_recurring = :is_rec WHERE id = :id`
- **C.** `INSERT INTO holidays VALUES (...)`
- **D.** `DELETE FROM holidays WHERE id = :id`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cập nhật an toàn với tham số có tên `:placeholder`, bảo vệ toàn diện hệ thống trước mọi nguy cơ SQL Injection.

#### Câu 16. Nếu Quản trị viên cố tình bấm nút Lưu nhưng để trống ô Tên ngày nghỉ lễ, hệ thống phản hồi như thế nào?

- **A.** Vẫn lưu vào CSDL với tên rỗng.
- **B.** Chặn lại và hiển thị thông báo lỗi yêu cầu nhập tên ngày nghỉ lễ (Test Case 2).
- **C.** Tự động đặt tên là 'Ngày nghỉ không tên'.
- **D.** Khóa tài khoản Quản trị viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểm tra tính bắt buộc của trường dữ liệu tên ngày lễ, không cho phép tạo dữ liệu rác.

#### Câu 17. Cột `created_at` trong bảng `holidays` lưu trữ thông tin gì?

- **A.** Thời điểm Quản trị viên thực hiện thao tác tạo bản ghi ngày nghỉ lễ vào hệ thống.
- **B.** Ngày thành lập Trường Đại học Thủ Đô Hà Nội.
- **C.** Ngày sinh nhật của KTV.
- **D.** Thời điểm kết thúc học kỳ.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ghi nhận mốc thời gian kiểm toán dữ liệu chuẩn trong CSDL.

#### Câu 18. Danh sách ngày nghỉ lễ trên giao diện Quản trị của Phan Công Thành được sắp xếp mặc định theo trật tự nào?

- **A.** Sắp xếp ngẫu nhiên.
- **B.** Sắp xếp theo ngày bắt đầu giảm dần (`start_date DESC`) hoặc tăng dần theo trình tự thời gian.
- **C.** Sắp xếp theo độ dài của tên ngày lễ.
- **D.** Sắp xếp theo số ký tự trong ID.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giúp Quản trị viên dễ dàng theo dõi các sự kiện nghỉ lễ gần nhất hoặc sắp diễn ra trong năm học.

#### Câu 19. Khi hệ thống kiểm tra ngày nghỉ lễ lặp lại định kỳ hàng năm (`is_recurring = 1`), hàm so sánh ngày xử lý như thế nào?

- **A.** So sánh cả 4 chữ số của năm.
- **B.** Bỏ qua phần năm (`YEAR`), chỉ so sánh tháng và ngày (`DATE_FORMAT(start_date, '%m-%d') <= DATE_FORMAT($current, '%m-%d') ...`).
- **C.** Cộng thêm 100 năm vào ngày lễ.
- **D.** Đổi ngày âm lịch sang dương lịch bằng tay.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kỹ thuật trích xuất tháng và ngày (`%m-%d`) giúp ngày lễ tái hiện chính xác vào cùng ngày đó ở tất cả các năm tiếp theo.

#### Câu 20. Ý nghĩa lớn nhất của việc Phan Công Thành xây dựng phân hệ Quản lý ngày nghỉ lễ `holidays` trong Đồ án Helpdesk Nhóm 1 là gì?

- **A.** Cho phép sinh viên được nghỉ học nhiều hơn.
- **B.** Cung cấp nền tảng dữ liệu chính xác để động cơ SLA giờ hành chính tự động trừ ngày nghỉ, bảo đảm tính công bằng tuyệt đối khi đánh giá hiệu suất (KPI) của đội ngũ Kỹ thuật viên.
- **C.** Làm đẹp giao diện Admin.
- **D.** Tăng dung lượng CSDL.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đây là viên gạch nền móng quan trọng để giải quyết bài toán nghiệp vụ phức tạp nhất trong ITIL: Tính thời gian cam kết dịch vụ theo đúng lịch làm việc hành chính của cơ quan nhà nước.

## <a id="chuyen-de-2"></a>2. Trung Tâm Thông Báo & Phát / Thu Hồi Cảnh Báo Sự Cố Khẩn Cấp Diện Rộng

#### Câu 21. Bảng CSDL `notifications` phục vụ việc hiển thị thông báo khẩn cấp toàn trường gồm những trường dữ liệu nào?

- **A.** `id`, `title`, `message`, `type`, `is_global`, `user_id`, `is_read`, `created_at`
- **B.** `id`, `math_score`, `english_score`
- **C.** `id`, `bus_route`, `bus_fee`
- **D.** `id`, `password_hash`, `token`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `notifications` quản lý các thông báo hệ thống, phân biệt giữa thông báo toàn trường (`is_global = 1`) và thông báo cá nhân (`user_id`).

#### Câu 22. Khi xảy ra sự cố kỹ thuật diện rộng (Major Incident như sập Wi-Fi KTX hoặc cổng đăng ký tín chỉ bảo trì), vai trò nào có thẩm quyền duyệt phát Cảnh báo diện rộng lên toàn bộ hệ thống?

- **A.** Bất kỳ sinh viên năm nhất nào.
- **B.** Quản trị viên (Admin) sau khi KTV kích hoạt đề xuất báo động khẩn cấp.
- **C.** Bảo vệ cổng trường.
- **D.** Người ngoài không có tài khoản.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phân quyền bảo mật: KTV chỉ có nút 'Báo động sự cố diện rộng gửi Admin', chỉ Admin mới có thẩm quyền cao nhất phê duyệt phát thông báo khẩn cấp tới toàn trường.

#### Câu 23. Khối thông báo sự cố diện rộng khi được Admin kích hoạt sẽ hiển thị ở vị trí nào trên giao diện người dùng?

- **A.** Giấu kín trong menu cài đặt của Admin.
- **B.** Thanh Banner màu đỏ/vàng nổi bật ngay ở đầu trang (`top-banner` hoặc `alert-banner`) trên mọi phân hệ (Sinh viên, Giảng viên, KTV).
- **C.** In ra giấy A4 dán ở cột cờ.
- **D.** Chỉ hiển thị khi người dùng bấm F12.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Banner đầu trang giúp sinh viên và giảng viên lập tức nhận biết sự cố đang diễn ra, ngăn chặn việc hàng trăm người cùng tạo phiếu báo trùng lặp.

#### Câu 24. Sau khi sự cố kỹ thuật diện rộng đã được Kỹ thuật viên khắc phục hoàn tất, Admin sử dụng chức năng nào để tắt thanh cảnh báo?

- **A.** Tắt nguồn máy chủ.
- **B.** Bấm nút 'Thu Hồi Cảnh Báo' (Revoke Announcement / Dismiss Global Alert), hệ thống cập nhật `is_active = 0` và ẩn thanh banner tức thì.
- **C.** Xóa tài khoản của KTV.
- **D.** Phải đợi hết kỳ học thông báo mới tự mất.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nút 'Thu hồi cảnh báo' của Admin hoàn tất vòng đời xử lý sự cố diện rộng, trả giao diện hệ thống về trạng thái bình thường.

#### Câu 25. Cột `is_global` trong bảng `notifications` nhận giá trị kiểu gì và mang ý nghĩa gì?

- **A.** `VARCHAR(255)` lưu tên thành phố.
- **B.** `TINYINT(1)`, nhận giá trị 1 nếu thông báo dành cho TOÀN BỘ NGƯỜI DÙNG trong trường, nhận giá trị 0 nếu là thông báo riêng cho cá nhân `user_id`.
- **C.** `INT` lưu số tiền phạt.
- **D.** `DATETIME` lưu giờ phát thanh.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cột cờ `is_global` giúp hệ thống lọc và hiển thị thông báo phát thanh diện rộng mà không cần tạo hàng chục ngàn dòng bản ghi cho từng sinh viên.

#### Câu 26. Khi một sinh viên đăng nhập vào hệ thống, câu truy vấn SQL lấy danh sách thông báo chưa đọc của sinh viên đó kết hợp điều kiện nào?

- **A.** `WHERE 1=0`
- **B.** `WHERE (is_global = 1 OR user_id = :student_id) AND is_read = 0 ORDER BY created_at DESC`
- **C.** `WHERE user_id IS NULL`
- **D.** `WHERE type = 'error'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lấy đồng thời thông báo phát thanh chung của trường và các thông báo riêng (ví dụ: 'KTV đã trả lời phiếu của bạn', 'Giảng viên đã chấm điểm').

#### Câu 27. Trường `type` trong bảng `notifications` thường nhận những giá trị chuẩn nào để quy định màu sắc hiển thị biểu tượng?

- **A.** `'red'`, `'blue'`, `'yellow'`
- **B.** `'info'` (xanh dương), `'warning'` (vàng cam), `'danger'` (đỏ khẩn cấp), `'success'` (xanh lá)
- **C.** `'toan'`, `'ly'`, `'hoa'`
- **D.** `'1'`, `'2'`, `'3'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Các mức cảnh báo chuẩn Bootstrap/Tailwind CSS giúp người dùng trực giác nhận biết mức độ nghiêm trọng của thông tin.

#### Câu 28. Khi KTV bấm 'Báo động sự cố diện rộng lên Admin', hệ thống ghi nhận bản ghi sự cố có trạng thái và tiêu đề tiền tố gì?

- **A.** Tiêu đề có tiền tố `[SỰ CỐ DIỆN RỘNG]` (Major Incident) và gửi thông báo nội bộ tới hàng đợi phê duyệt của Admin.
- **B.** Tự động tắt Wi-Fi trường.
- **C.** Xóa danh sách sinh viên vi phạm.
- **D.** Gửi tin nhắn Messenger cho Admin.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tiền tố `[SỰ CỐ DIỆN RỘNG]` giúp phân biệt sự cố quy mô lớn với các vé hỗ trợ cá nhân thông thường.

#### Câu 29. Trong modal 'Đóng phiếu trùng lặp' của KTV, danh sách các phiếu cha hiển thị để gom nhóm là những phiếu nào?

- **A.** Các phiếu đã đóng từ 5 năm trước.
- **B.** Các phiếu có trạng thái `in_progress` hoặc `assigned` đang diễn ra, đặc biệt ưu tiên các phiếu có nhãn `[SỰ CỐ DIỆN RỘNG]`.
- **C.** Các phiếu của trường khác.
- **D.** Bất kỳ chuỗi văn bản nào do KTV gõ bừa.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn ITIL v4: Chỉ gom nhóm vào phiếu cha đang còn hiệu lực và đang được đội ngũ kỹ thuật xử lý.

#### Câu 30. Khi người dùng nhấn vào nút 'Đã đọc' (Mark as Read) trên một thông báo cá nhân, hệ thống thực hiện thao tác cập nhật nào?

- **A.** Xóa vĩnh viễn dòng thông báo đó khỏi CSDL.
- **B.** Cập nhật `is_read = 1` thông qua request POST/AJAX bảo đảm không làm mất lịch sử thông báo.
- **C.** Đổi mật khẩu người dùng.
- **D.** Khóa tài khoản người dùng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cập nhật cờ `is_read` giúp giảm số lượng trên huy hiệu quả chuông thông báo (Notification Badge) mà vẫn bảo tồn lịch sử.

#### Câu 31. Để thông báo sự cố diện rộng hiển thị hiệu ứng rung lắc nhẹ hoặc nhấp nháy thu hút sự chú ý khi mới xuất hiện, mã CSS sử dụng thuộc tính gì?

- **A.** `display: none;`
- **B.** `animation: pulse 2s infinite;` (hoặc lớp hiệu ứng `animate-pulse` của Tailwind CSS)
- **C.** `opacity: 0;`
- **D.** `font-size: 0px;`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hiệu ứng thị giác nhẹ nhàng giúp người dùng lập tức nhìn thấy thông báo khẩn cấp mà không gây phản cảm.

#### Câu 32. Tại sao hệ thống không gửi tin nhắn SMS tới toàn bộ 15.000 sinh viên khi có sự cố mạng mà lại dùng Cảnh báo Banner trên Web Helpdesk?

- **A.** Vì nhà trường không có sóng điện thoại.
- **B.** Tiết kiệm chi phí viễn thông hàng chục triệu đồng cho trường, đồng thời sinh viên truy cập web để báo lỗi sẽ thấy ngay thông báo tại chỗ.
- **C.** Vì sinh viên không được dùng điện thoại.
- **D.** Do luật cấm gửi SMS.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giải pháp chuyển đổi số thông minh: tiếp cận đúng đối tượng tại đúng điểm chạm dịch vụ (Digital Touchpoint) với chi phí 0 đồng.

#### Câu 33. Khi thu hồi thông báo sự cố diện rộng, Admin có quyền đính kèm thông điệp kết luận gì?

- **A.** Thông báo số tiền thưởng của Admin.
- **B.** Thông điệp hoàn tất khắc phục (ví dụ: 'Hệ thống Cổng thông tin đã khôi phục hoạt động bình thường lúc 10h15. Cảm ơn Thầy/Cô và các bạn sinh viên đã kiên nhẫn').
- **C.** Thông báo nghỉ học cả tuần.
- **D.** Không được viết gì.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính giao tiếp sư phạm văn minh, khép lại sự cố một cách chuyên nghiệp và trấn an tâm lý người dùng.

#### Câu 34. Trong bảng `notifications`, cột `user_id` có thuộc tính gì đối với thông báo toàn trường (`is_global = 1`)?

- **A.** Bắt buộc phải mang giá trị 1.
- **B.** Cho phép giá trị `NULL` (không gắn với một người dùng đơn lẻ cụ thể nào).
- **C.** Bắt buộc phải là số âm.
- **D.** Mang giá trị chuỗi rỗng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `user_id NULL` kết hợp `is_global = 1` biểu thị thông điệp dành cho toàn thể cộng đồng trường học.

#### Câu 35. Giao diện Trung tâm Thông báo dành cho Admin (`admin/notifications.php`) cho phép Admin thực hiện các thao tác nào?

- **A.** Chơi game trực tuyến.
- **B.** Tạo thông báo mới, xem danh sách thông báo đã phát, thu hồi cảnh báo khẩn cấp và lọc theo loại thông báo.
- **C.** Đổi điểm thi của sinh viên.
- **D.** Tải phần mềm crack.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trang điều hành truyền thông nội bộ giúp Admin quản lý toàn diện các kênh phát thanh kỹ thuật của trường.

#### Câu 36. Khi phát cảnh báo khẩn cấp diện rộng, hệ thống có hỗ trợ gửi thông báo tới nhóm vai trò cụ thể (Target Audience) không?

- **A.** Không, bắt buộc ai cũng phải nhận.
- **B.** Có; hỗ trợ cấu hình gửi tới Toàn trường, hoặc Chỉ Giảng viên, hoặc Chỉ Kỹ thuật viên tùy theo tính chất sự cố.
- **C.** Chỉ gửi cho người đã nghỉ hưu.
- **D.** Chỉ gửi cho người có tên bắt đầu bằng chữ A.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phân vùng đối tượng tiếp nhận thông tin giúp tránh làm phiền các bộ phận không liên quan đến sự cố kỹ thuật đó.

#### Câu 37. Thời điểm hết hạn tự động của thông báo diện rộng được lưu trong cột nào nếu có?

- **A.** `expires_at`
- **B.** `password_hash`
- **C.** `user_code`
- **D.** `class_id`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cột `expires_at` cho phép tự động ẩn banner sau một khoảng thời gian nhất định (ví dụ sau 24h) mà Admin không cần vào tắt thủ công.

#### Câu 38. Biểu tượng nào thường được sử dụng cùng thanh Banner sự cố diện rộng để tăng mức độ cảnh báo thị giác?

- **A.** Biểu tượng hình trái tim.
- **B.** Biểu tượng tam giác cảnh báo nguy hiểm `⚠️` hoặc còi báo động `🚨`.
- **C.** Biểu tượng chiếc ly uống nước.
- **D.** Biểu tượng mặt cười.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Biểu tượng trực quan giúp người dùng hiểu ngay đây là thông báo khẩn cấp quan trọng cần đọc ngay.

#### Câu 39. Hành động thu hồi cảnh báo sự cố của Admin được bảo vệ bằng phương thức HTTP nào?

- **A.** `GET`
- **B.** `POST` có kiểm tra token CSRF
- **C.** `TRACE`
- **D.** `CONNECT`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chống tấn công giả mạo yêu cầu (CSRF), bảo đảm chỉ Admin thực sự mới có quyền bật/tắt phát thanh của trường.

#### Câu 40. Khi thu hồi thông báo khẩn cấp, bản ghi trong bảng `notifications` có bị xóa mất khỏi CSDL không?

- **A.** Bị xóa vĩnh viễn không để lại dấu vết.
- **B.** Không bị xóa; hệ thống cập nhật `is_active = 0` và lưu thời điểm thu hồi để phục vụ báo cáo kiểm toán sự cố sau này.
- **C.** Toàn bộ CSDL bị khóa lại.
- **D.** Bản ghi biến thành một môn học mới.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo toàn lịch sử vận hành (Post-Incident Review) để phục vụ công tác rút kinh nghiệm và nâng cấp hạ tầng công nghệ định kỳ.

## <a id="chuyen-de-3"></a>3. Công Cụ Nhập Dữ Liệu Tài Khoản Hàng Loạt Bằng Excel / CSV & Xử Lý Đồng Bộ

#### Câu 41. Công cụ nhập tài khoản hàng loạt (`admin/import_excel.php`) do Phan Công Thành xây dựng hỗ trợ những định dạng tệp nào?

- **A.** Chỉ nhận file ảnh `.png`
- **B.** Tệp bảng tính chuẩn CSV (`.csv`) và bảng tính Microsoft Excel (`.xlsx`, `.xls`)
- **C.** Chỉ nhận file nhạc `.mp3`
- **D.** Chỉ nhận file mã nguồn `.cpp`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hỗ trợ cả CSV và Excel giúp Phòng Đào tạo có thể xuất danh sách sinh viên từ phần mềm quản lý đào tạo của trường nạp thẳng vào hệ thống mà không cần chuyển đổi phức tạp.

#### Câu 42. Lớp Service nào trong mã nguồn chịu trách nhiệm đọc và bóc tách dữ liệu từ tệp bảng tính tải lên?

- **A.** `TicketService`
- **B.** `App\Services\SpreadsheetImportService`
- **C.** `SlaService`
- **D.** `RatingService`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Service chuyên biệt đóng gói logic xử lý file bảng tính, kiểm tra hợp lệ từng dòng và chuẩn bị câu lệnh nạp vào CSDL.

#### Câu 43. Tệp bảng tính mẫu để nhập tài khoản sinh viên hàng loạt bao gồm các cột tiêu đề chuẩn nào?

- **A.** Cột 1: Tên người yêu, Cột 2: Sở thích, Cột 3: Quê quán
- **B.** Mã định danh (`user_code`), Họ và tên (`name`), Email (`email`), Số điện thoại (`phone`), Lớp sinh hoạt (`class_code`), Khoa (`department`)
- **C.** Chỉ cần 1 cột ghi số điện thoại
- **D.** Cột ghi số đo chiều cao và cân nặng

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cấu trúc dữ liệu học viên chuẩn mực để tự động khởi tạo hồ sơ người dùng trong hệ thống.

#### Câu 44. Khi nhập danh sách 500 sinh viên mới từ Excel, mật khẩu khởi tạo mặc định cho các tài khoản là gì và được lưu trữ ra sao?

- **A.** Lưu văn bản rõ `123456` trực tiếp vào CSDL.
- **B.** Mật khẩu mặc định là `123456`, nhưng được băm an toàn bằng thuật toán Bcrypt (`password_hash('123456', PASSWORD_BCRYPT)`) trước khi ghi vào cột `password_hash`.
- **C.** Mỗi sinh viên được cấp một mật khẩu dài 100 chữ số ngẫu nhiên.
- **D.** Để trống không có mật khẩu.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc bảo mật tuyệt đối: 100% mật khẩu trong hệ thống đều được băm Bcrypt, kể cả mật khẩu khởi tạo hàng loạt.

#### Câu 45. Khi tài khoản sinh viên được nhập hàng loạt với mật khẩu mặc định `123456`, cờ bảo mật nào được bật lên giá trị 1?

- **A.** `is_admin = 1`
- **B.** `must_change_password = 1` (Bắt buộc đổi mật khẩu ngay ở lần đăng nhập đầu tiên)
- **C.** `is_locked = 1`
- **D.** `is_agent = 1`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bắt buộc đổi mật khẩu lần đầu ngăn chặn nguy cơ bạn học dùng mật khẩu mặc định để đăng nhập trái phép vào tài khoản của nhau.

#### Câu 46. Trong quá trình đọc tệp Excel/CSV, hệ thống phát hiện một dòng có Mã sinh viên hoặc Email ĐÃ TỒN TẠI trong hệ thống thì xử lý ra sao?

- **A.** Lập tức xóa sạch toàn bộ CSDL.
- **B.** Bỏ qua dòng đó (hoặc cập nhật thông tin mới tùy chọn), ghi nhận vào danh sách cảnh báo trùng lặp và tiếp tục xử lý các dòng hợp lệ còn lại mà không làm dừng toàn bộ quá trình.
- **C.** Báo lỗi crash màn hình xanh.
- **D.** Tự động đổi email sinh viên đó thành số 0.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế chịu lỗi thông minh (Fault-Tolerant Batch Processing): bỏ qua bản ghi lỗi và thông báo chi tiết số dòng bị trùng để Admin xử lý riêng.

#### Câu 47. Thao tác ánh xạ tên lớp sinh hoạt (ví dụ: `D24-CNTT1`) từ tệp Excel vào CSDL được thực hiện như thế nào?

- **A.** Ghi chuỗi văn bản 'D24-CNTT1' vào cột `class_id`.
- **B.** Truy vấn bảng `classes` để tìm `id` tương ứng với `class_code = 'D24-CNTT1'`; nếu chưa có, hệ thống có thể tự động tạo mới lớp học đó để lấy ID gán vào `users.class_id`.
- **C.** Bỏ qua không lưu lớp học.
- **D.** Gán lớp học là số 0.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chuẩn hóa quan hệ dữ liệu: biến chuỗi văn bản trong file Excel thành ID khóa ngoại chuẩn trong bảng `classes`.

#### Câu 48. Để tăng tốc độ nạp hàng ngàn dòng dữ liệu từ Excel vào MySQL và bảo đảm tính toàn vẹn (ACID), kỹ thuật nào được áp dụng?

- **A.** Mở và đóng kết nối CSDL 1000 lần liên tục.
- **B.** Sử dụng Giao dịch CSDL (Database Transaction: `$db->beginTransaction()` ... `$db->commit()`), gom các câu lệnh INSERT vào một khối duy nhất.
- **C.** Chờ 1 giây sau mỗi dòng.
- **D.** Lưu dữ liệu vào tệp Word.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Database Transaction giúp tăng tốc độ chèn hàng ngàn bản ghi từ 30 giây xuống còn dưới 1 giây, đồng thời cho phép `rollBack()` phục hồi nguyên trạng nếu gặp sự cố nghiêm trọng.

#### Câu 49. Sau khi quá trình nhập Excel hoàn tất, giao diện hiển thị bảng tổng kết gồm những thông số nào?

- **A.** Chỉ thông báo 'Xong'.
- **B.** Tổng số dòng đã đọc, Số tài khoản thêm mới thành công, Số dòng bị bỏ qua do trùng lặp hoặc lỗi định dạng, và liên kết tải về danh sách tài khoản mới khởi tạo.
- **C.** Hiển thị mật khẩu của Admin.
- **D.** Hiển thị tốc độ CPU của máy chủ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Báo cáo kết quả nhập dữ liệu chi tiết, minh bạch giúp cán bộ quản trị dễ dàng đối soát với danh sách học viên gốc của nhà trường.

#### Câu 50. Để phòng chống tấn công tải lên mã độc ẩn giấu trong tệp Excel (Malicious File Upload), hệ thống thực hiện những biện pháp kiểm tra nào?

- **A.** Chỉ kiểm tra tên tệp có chữ 'sinhvien' hay không.
- **B.** Kiểm tra phần mở rộng đuôi tệp nằm trong danh sách trắng (`.csv`, `.xlsx`, `.xls`), kiểm tra MIME-Type thực tế của tệp và quét header nhị phân.
- **C.** Không kiểm tra gì, tin tưởng tuyệt đối người dùng.
- **D.** Đổi đuôi tệp thành `.exe`.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tầng phòng thủ nhiều lớp ngăn chặn tin tặc đổi tên tệp PHP độc hại thành `.csv` để tải lên máy chủ.

#### Câu 51. Giới hạn dung lượng tối đa cho tệp Excel/CSV tải lên trong chức năng nhập hàng loạt được cấu hình là bao nhiêu?

- **A.** 10 KB
- **B.** 10 MB (cho phép chứa danh sách tới hơn 50.000 sinh viên)
- **C.** 10 GB
- **D.** 1 Byte

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dung lượng 10MB hoàn toàn đáp ứng cho quy mô sinh viên toàn trường mà không làm cạn kiệt bộ nhớ RAM khi phân tích tệp.

#### Câu 52. Khi đọc tệp CSV có chứa ký tự tiếng Việt có dấu (như 'Nguyễn Văn An'), hàm đọc file cần xử lý bảng mã nào để không bị lỗi font chữ?

- **A.** `ASCII`
- **B.** `UTF-8` (kết hợp xử lý Byte Order Mark - BOM nếu có)
- **C.** `ISO-8859-1`
- **D.** `EBCDIC`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng mã `UTF-8` chuẩn bảo đảm họ tên tiếng Việt có dấu được lưu trữ và hiển thị sắc nét, không bị biến dạng thành các ký tự lạ `???`.

#### Câu 53. Để thuận tiện cho người quản trị lần đầu sử dụng, giao diện trang nhập tài khoản cung cấp tiện ích gì?

- **A.** Nút 'Tải Tệp Mẫu' (Download Sample Template CSV/Excel) có sẵn các cột tiêu đề chuẩn và vài dòng dữ liệu mẫu.
- **B.** Bắt người dùng phải tự đoán tên cột.
- **C.** Gửi tệp mẫu qua bưu điện.
- **D.** Bắt người dùng phải gọi điện hỏi lập trình viên.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tệp mẫu chuẩn giúp cán bộ trường chỉ việc sao chép dữ liệu vào đúng các cột quy định, hạn chế 100% lỗi sai lệch cấu trúc cột.

#### Câu 54. Nếu một dòng trong tệp Excel có định dạng email sai (ví dụ: `nguyenvana@@gmail..com` hoặc không có ký tự `@`), hệ thống xử lý thế nào?

- **A.** Vẫn lưu vào CSDL làm hỏng hệ thống gửi mail.
- **B.** Hàm `filter_var($email, FILTER_VALIDATE_EMAIL)` phát hiện lỗi, đánh dấu dòng đó là không hợp lệ và ghi nhận vào danh sách lỗi kèm số dòng cụ thể.
- **C.** Tự động bịa ra một email mới.
- **D.** Xóa toàn bộ tệp Excel.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bộ lọc `FILTER_VALIDATE_EMAIL` chuẩn của PHP bảo đảm 100% email lưu vào CSDL đều có cú pháp hợp lệ để phục vụ tính năng gửi thư SSPR.

#### Câu 55. Thuộc tính vai trò (`role`) và loại người dùng (`user_type`) được gán mặc định cho các tài khoản sinh viên nhập từ tệp Excel là gì?

- **A.** `role = 'admin'`, `user_type = 'super'`
- **B.** `role = 'requester'`, `user_type = 'student'`
- **C.** `role = 'agent'`, `user_type = 'ktv'`
- **D.** `role = 'guest'`, `user_type = 'none'`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Đúng phân quyền RBAC: người học mang vai trò người yêu cầu (`requester`) và đối tượng cụ thể là sinh viên (`student`).

#### Câu 56. Nếu Quản trị viên tải lên danh sách Giảng viên, cột `user_type` trong tệp bảng tính cần điền giá trị gì để hệ thống phân quyền chính xác?

- **A.** `student`
- **B.** `lecturer` (Hệ thống sẽ cấp quyền truy cập Cổng Giảng viên và phân công giảng dạy)
- **C.** `customer`
- **D.** `visitor`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nhận diện `user_type = 'lecturer'` giúp kích hoạt ngay các quyền hạn học thuật của Giảng viên ngay sau khi nạp tài khoản.

#### Câu 57. Trong quá trình thực thi lệnh chèn dữ liệu hàng loạt, tham số `max_execution_time` trong PHP có thể cần được nâng lên tạm thời qua hàm nào?

- **A.** `sleep()`
- **B.** `set_time_limit(300)` (cho phép script chạy tối đa 300 giây khi tệp dữ liệu lớn)
- **C.** `exit()`
- **D.** `die()`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ngăn chặn lỗi quá thời gian thực thi (PHP Fatal error: Maximum execution time exceeded) khi xử lý các tệp danh sách hàng chục ngàn dòng.

#### Câu 58. Sau khi nhập tài khoản thành công, sinh viên có thể đăng nhập ngay vào hệ thống bằng tài khoản và mật khẩu nào?

- **A.** Đăng nhập bằng số chứng minh thư và mật khẩu để trống.
- **B.** Đăng nhập bằng Email trường cấp (hoặc Mã sinh viên) và mật khẩu mặc định `123456`, sau đó được chuyển hướng đổi mật khẩu mới ngay lập tức.
- **C.** Phải đợi Admin gọi điện kích hoạt.
- **D.** Phải nộp phí kích hoạt 50.000 VNĐ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy trình Onboarding mượt mà: tài khoản sẵn sàng sử dụng tức thì, tự động kích hoạt luồng đổi mật khẩu bảo mật ở lần đầu truy cập.

#### Câu 59. Tại sao không nên để Quản trị viên nhập từng tài khoản một bằng tay qua form đơn lẻ khi đầu năm học có 3.000 tân sinh viên nhập học?

- **A.** Vì Quản trị viên sẽ bị mỏi tay và tốn hàng tuần làm việc, dễ dẫn đến sai sót chính tả khi gõ mã số sinh viên và email.
- **B.** Vì form đơn lẻ chỉ cho phép nhập 10 người mỗi ngày.
- **C.** Vì MySQL cấm nhập từng dòng.
- **D.** Không có lý do gì.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Công cụ Import hàng loạt tự động hóa toàn bộ công tác nạp dữ liệu đầu năm chỉ trong 10 giây, tiết kiệm hàng trăm giờ công lao động cho nhà trường.

#### Câu 60. Tổng kết lại, module Nhập tài khoản Excel/CSV của Phan Công Thành đóng vai trò gì trong kiến trúc vận hành của Đồ án Helpdesk?

- **A.** Chỉ là tính năng phụ trợ không dùng đến.
- **B.** Là huyết mạch khởi tạo dữ liệu người dùng toàn hệ thống, cung cấp nền tảng tài khoản đồng bộ cho cả 3 phân hệ: Sinh viên, Giảng viên và Kỹ thuật viên hoạt động trơn tru.
- **C.** Để tính tiền in ấn.
- **D.** Để sao chép dữ liệu sang máy tính cá nhân.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Không có tài khoản người dùng thì toàn bộ các luồng gửi vé, phân công giảng dạy, chấm điểm hay cứu hộ đều không thể vận hành; đây là tính năng nền tảng sống còn.

## <a id="chuyen-de-4"></a>4. Quản Lý Ca Trực KTV & Phân Quyền Độc Quyền Admin Bật / Tắt Trực Ca

#### Câu 61. Trong bảng `users`, cột nào lưu trữ trạng thái ca trực của một Kỹ thuật viên (KTV)?

- **A.** `is_admin`
- **B.** `is_on_duty` kiểu `TINYINT(1)` (1: Đang trực ca, 0: Đang nghỉ ca)
- **C.** `must_change_password`
- **D.** `score`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cột `is_on_duty` xác định KTV có đang trong ca làm việc sẵn sàng tiếp nhận sự cố phân bổ tự động hay không.

#### Câu 62. Theo quy định phân quyền mới nhất của hệ thống, ai là người DUY NHẤT có quyền bật hoặc tắt trạng thái ca trực (`is_on_duty`) cho KTV?

- **A.** KTV tự do bấm nút bật/tắt tùy thích bất cứ lúc nào.
- **B.** Chỉ Quản trị viên (Admin) mới có quyền phân công và bật/tắt ca trực cho KTV; KTV chỉ có huy hiệu hiển thị xem (Read-only).
- **C.** Sinh viên có quyền tắt ca trực của KTV.
- **D.** Hệ thống tự động bật tắt theo giờ chuông báo thức.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Admin là cấp chỉ huy điều hành nhân sự IT: chỉ Admin mới có thẩm quyền phân công ca trực, tránh tình trạng KTV tự ý tắt trực để trốn việc tiếp nhận phiếu.

#### Câu 63. Trên giao diện Bàn làm việc KTV (`agent/index.php`), huy hiệu ca trực ở góc phải màn hình của KTV hiển thị thông điệp gì khi KTV đang trong ca trực?

- **A.** Nút bấm màu xanh cho phép KTV tự click tắt.
- **B.** Huy hiệu thông tin (Badge tĩnh): '🟢 ĐANG TRỰC CA (Do Admin phân công)', không thể tự click để đổi trạng thái.
- **C.** Huy hiệu màu đen báo hiệu nguy hiểm.
- **D.** Dòng chữ 'Không xác định'.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giao diện Read-only đối với KTV phản ánh tính kỷ luật lao động và sự kiểm soát quản trị của Admin.

#### Câu 64. Khi một KTV được Admin duyệt cho nghỉ phép hoặc hết ca trực (`is_on_duty = 0`), huy hiệu trên màn hình KTV hiển thị là gì?

- **A.** '🔴 Bị sa thải khỏi trường'
- **B.** '⏸️ ĐANG NGHỈ CA (Do Admin phê duyệt)'
- **C.** '🟢 Đang làm việc'
- **D.** Không hiển thị gì

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thông báo rõ ràng trạng thái nghỉ ca đã được phê duyệt chính thức bởi Admin.

#### Câu 65. Nếu một KTV cố tình gửi request POST `action=toggle_duty` lên máy chủ để tự ý bật/tắt ca trực của mình, đoạn code PHP trong `agent/index.php` sẽ xử lý thế nào?

- **A.** Chấp nhận thay đổi bình thường.
- **B.** Kiểm tra `if (!$isAdmin)`, lập tức từ chối và hiển thị thông báo lỗi 'Bạn không có quyền thay đổi trạng thái ca trực. Chỉ Admin mới có quyền này!'.
- **C.** Tự động xóa tài khoản KTV.
- **D.** Chuyển tiếp cho giảng viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo vệ chặt chẽ ở cả tầng Controller xử lý backend: chặn đứng mọi yêu cầu can thiệp trái phép vào dữ liệu ca trực.

#### Câu 66. Giao diện Bảng điều khiển Quản trị viên (`admin/index.php` hoặc `admin/agents.php`) cung cấp công cụ gì để Admin quản lý ca trực của các KTV?

- **A.** Danh sách các KTV kèm nút gạt chuyển đổi trạng thái (Toggle Switch / Modal phân công ca trực) bật/tắt trực ca nhanh chóng.
- **B.** Bắt Admin phải gõ lệnh SQL trong phpMyAdmin.
- **C.** Chỉ cho phép xem chứ không được sửa.
- **D.** Phải gọi điện thoại cho từng KTV.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Admin Dashboard trực quan giúp người quản trị dễ dàng phân bổ quân số trực ca sáng, ca chiều cho từng kỹ thuật viên chỉ với 1 click.

#### Câu 67. Khi có một phiếu sự cố mới được tạo, thuật toán Triage tự động gán phiếu cho ai?

- **A.** Luôn gán cho Admin.
- **B.** Chỉ chọn trong số các KTV đang có `is_on_duty = 1` và có số lượng phiếu đang xử lý ít nhất (thuật toán Least-Loaded).
- **C.** Gán cho KTV đang nghỉ phép.
- **D.** Gán ngẫu nhiên cho một sinh viên trong trường.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc điều phối thông minh: Tuyệt đối không gán việc cho KTV đang nghỉ ca (`is_on_duty = 0`), bảo đảm phiếu luôn có người đang trực xử lý ngay.

#### Câu 68. Trong trường hợp TẤT CẢ KTV chuyên trách dịch vụ đó ĐỀU ĐANG NGHỈ CA (`is_on_duty = 0`), thuật toán Triage xử lý như thế nào?

- **A.** Hủy phiếu của sinh viên.
- **B.** Kích hoạt cơ chế Tràn Tải / Dự Phòng (Auto-Overflow): tìm kiếm các KTV khác đang trực ca có tải thấp nhất, hoặc chuyển về hàng đợi chung để Admin phân công thủ công.
- **C.** Báo lỗi sập CSDL.
- **D.** Để phiếu đó vào thùng rác.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo đảm tính sẵn sàng cao (High Availability): dịch vụ không bị gián đoạn hay tắc nghẽn khi một kỹ thuật viên nghỉ đột xuất.

#### Câu 69. Ngưỡng quá tải tối đa (Overload Threshold) cho mỗi KTV trong thuật toán điều phối của hệ thống được quy định là bao nhiêu phiếu đang mở?

- **A.** 1 phiếu.
- **B.** 5 phiếu đang hoạt động (`active_tickets >= 5`).
- **C.** 100 phiếu.
- **D.** Không giới hạn số lượng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ngưỡng 5 phiếu đang xử lý giúp KTV không bị 'ngập lụt' công việc, tập trung giải quyết dứt điểm từng sự cố đạt chất lượng cao.

#### Câu 70. Chỉ mục (Index) tối ưu nào được tạo trong bảng `users` để hỗ trợ truy vấn tìm kiếm KTV trực ca có tải thấp nhất diễn ra với độ phức tạp O(1)?

- **A.** `idx_agent_dispatch (role, is_on_duty, is_active)`
- **B.** `idx_email`
- **C.** `idx_phone`
- **D.** `idx_name`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ mục tổng hợp đa cột `idx_agent_dispatch` giúp câu lệnh SELECT KTV trực ca chạy tức thì trong vài mili-giây kể cả khi bảng users có hàng chục ngàn dòng.

#### Câu 71. Khi một KTV có đơn xin nghỉ ốm đột xuất, quy trình xử lý ca trực trên hệ thống diễn ra như thế nào?

- **A.** KTV tự ý bỏ về không báo ai.
- **B.** KTV báo cáo Admin -> Admin truy cập hệ thống bấm chuyển trạng thái KTV đó sang nghỉ ca (`is_on_duty = 0`) -> Các phiếu mới không còn điều phối về KTV này nữa.
- **C.** Admin xóa tài khoản của KTV.
- **D.** Tự động trừ lương gấp 10 lần.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy trình quản lý nhân sự chuyên nghiệp, chuẩn mực của cơ quan hành chính nhà nước.

#### Câu 72. Đối với các phiếu đang dở dang (`in_progress`) của KTV vừa được Admin cho nghỉ ca, hệ thống hỗ trợ tính năng gì?

- **A.** Đóng hết phiếu coi như xong.
- **B.** Admin hoặc Trưởng nhóm KTV sử dụng tính năng 'Chuyển giao phiếu' (Ticket Reassign) sang cho một KTV khác đang trực ca tiếp tục xử lý.
- **C.** Bắt sinh viên tạo phiếu mới từ đầu.
- **D.** Xóa sạch lịch sử trao đổi.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảo đảm tính liên tục của dịch vụ (Service Continuity): phiếu được bàn giao êm ái, bảo toàn toàn bộ tin nhắn trao đổi trước đó.

#### Câu 73. Khi Admin thực hiện thao tác bật/tắt ca trực của KTV, thông tin nào được hệ thống lưu vết (Audit Log)?

- **A.** Không lưu gì.
- **B.** Ghi nhận nhật ký: ID Admin thực hiện, ID KTV bị thay đổi, trạng thái mới (`on_duty`/`off_duty`), và mốc thời gian thực hiện.
- **C.** Lưu ảnh chụp màn hình máy tính Admin.
- **D.** Ghi vào tệp nhạc.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Vết kiểm toán giúp Ban Giám hiệu tra cứu minh bạch lịch sử phân công lao động khi có kiểm tra nội bộ.

#### Câu 74. Tại sao việc Admin nắm độc quyền quản lý ca trực lại giúp giải quyết triệt để vấn đề 'Đùn đẩy trách nhiệm' trong phòng CNTT?

- **A.** Vì Admin có quyền phạt tiền.
- **B.** Vì KTV không thể tự ý 'tắt trực' khi thấy sự cố khó hoặc 'bật trực' khi hết sự cố; mọi ca trực đều được lên lịch và phê duyệt chính thức.
- **C.** Vì Admin là người giỏi nhất trường.
- **D.** Không có tác dụng gì.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thiết lập kỷ cương kỷ luật trong vận hành hệ thống hỗ trợ kỹ thuật theo đúng tiêu chuẩn ITIL v4.

#### Câu 75. Trên giao diện Admin, bảng danh sách KTV trực ca hiển thị những cột thông tin trực quan nào?

- **A.** Chỉ hiển thị tên KTV.
- **B.** Mã KTV, Họ tên, Chuyên môn chính (SSO, Wi-Fi, LMS...), Số phiếu đang xử lý, Điểm CSAT trung bình, Trạng thái ca trực và Nút gạt chuyển đổi.
- **C.** Chiều cao và cân nặng của KTV.
- **D.** Danh sách các bạn học cùng cấp 3.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng tổng quan điều hành (Dispatcher Console) cung cấp đầy đủ dữ liệu thời gian thực giúp Admin ra quyết định phân công chính xác.

#### Câu 76. Khi Admin kích hoạt nút 'Bật trực ca' cho một KTV, câu lệnh SQL thực thi là gì?

- **A.** `UPDATE users SET is_on_duty = 1 WHERE id = :agent_id AND role = 'agent'`
- **B.** `DELETE FROM users WHERE id = :agent_id`
- **C.** `INSERT INTO users VALUES (...)`
- **D.** `DROP TABLE users`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cập nhật tức thời trạng thái trực ca của KTV, đưa KTV đó vào danh sách sẵn sàng nhận phiếu của thuật toán Triage.

#### Câu 77. Khi một KTV đang nghỉ ca (`is_on_duty = 0`), KTV đó có thể đăng nhập vào hệ thống để xem lại các phiếu cũ mình đã xử lý không?

- **A.** Bị cấm đăng nhập hoàn toàn.
- **B.** Vẫn đăng nhập bình thường để xem tài liệu và tra cứu lịch sử phiếu; hệ thống chỉ KHÔNG phân bổ thêm phiếu mới tự động mà thôi.
- **C.** Mật khẩu bị tự động đổi.
- **D.** Phải xin phép Admin bằng văn bản giấy.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tách biệt giữa quyền truy cập hệ thống và trạng thái sẵn sàng tiếp nhận sự cố mới.

#### Câu 78. Nếu Quản trị viên muốn đặt lại trạng thái ca trực cho toàn bộ đội ngũ KTV vào đầu ngày làm việc (8h00 sáng), hệ thống hỗ trợ tính năng nào?

- **A.** Nút 'Bắt đầu ca trực toàn đội ngũ' hoặc script CLI đồng bộ ca trực tự động theo thời khóa biểu trường.
- **B.** Bắt Admin phải bấm từng người 100 lần.
- **C.** Xóa toàn bộ KTV cũ và tuyển KTV mới.
- **D.** Chỉ cho phép 1 KTV trực mỗi ngày.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tự động hóa tác vụ định kỳ, tiết kiệm thời gian điều hành đầu giờ mỗi ngày cho Quản trị viên.

#### Câu 79. Chỉ số KPI nào của KTV chịu ảnh hưởng tích cực trực tiếp khi hệ thống điều phối phân bổ phiếu đúng ca trực?

- **A.** Chỉ số nhiệt độ CPU.
- **B.** Thời gian phản hồi đầu tiên (First Response Time) và Tỷ lệ xử lý đúng hạn SLA (SLA Compliance Rate).
- **C.** Số tiền học bổng.
- **D.** Tỷ lệ đi làm muộn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phiếu luôn đến tay người đang ngồi trực ca giúp sự cố được tiếp nhận trong 5-10 phút thay vì nằm chờ hàng tiếng đồng hồ.

#### Câu 80. Tóm lại, sự phối hợp giữa Bảng `holidays`, Động cơ SLA và Quản lý ca trực `is_on_duty` của Phan Công Thành tạo nên điều gì cho Đồ án Helpdesk Nhóm 1?

- **A.** Làm chậm tốc độ đường truyền Internet.
- **B.** Tạo nên Động cơ Quản lý Dịch vụ ITIL thông minh, tự động hóa hoàn toàn việc phân bổ nhân lực và đo lường cam kết dịch vụ theo chuẩn doanh nghiệp thực thụ.
- **C.** Làm cho phần mềm khó sử dụng hơn.
- **D.** Chỉ là bài tập lý thuyết không có tính thực tế.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Minh chứng rõ nét cho năng lực thiết kế phần mềm chuyên nghiệp, giải quyết bài toán thực tế của môi trường giáo dục đại học hiện đại.

## <a id="chuyen-de-5"></a>5. Phân Tích 5 Test Cases Chuyên Sâu & Các Câu Lệnh Truy Vấn SQL Của Phan Công Thành

#### Câu 81. Trong tài liệu kiểm thử của Phan Công Thành (`PhanCongThanh_testcase_holidays.md`), Test Case 1 (TC01) kiểm tra kịch bản nghiệp vụ nào?

- **A.** Thêm ngày nghỉ lễ thành công với dữ liệu hợp lệ (Tên: Ngày nghỉ kiểm tra, ngày bắt đầu: 2026-11-01, ngày kết thúc: 2026-11-01).
- **B.** Tấn công tràn bộ đệm máy chủ.
- **C.** Xóa vĩnh viễn tài khoản Hiệu trưởng.
- **D.** Tắt hệ thống điện toàn trường.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** TC01 xác nhận luồng thêm mới ngày nghỉ hoạt động hoàn hảo, dữ liệu hiển thị đúng trong danh sách bảng `holidays`.

#### Câu 82. Trong Test Case 2 (TC02) của Phan Công Thành, khi người dùng để trống ô 'Tên ngày nghỉ lễ', kết quả thực tế thu được là gì?

- **A.** Hệ thống vẫn lưu vào CSDL với tên rỗng.
- **B.** Hệ thống từ chối dữ liệu và bắt buộc người dùng phải nhập trường tên ngày nghỉ lễ.
- **C.** Báo lỗi sập cơ sở dữ liệu.
- **D.** Tự động thoát ứng dụng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** TC02 kiểm chứng thành công tầng bắt lỗi Validation trường bắt buộc, ngăn chặn dữ liệu rác.

#### Câu 83. Trong Test Case 3 (TC03) của Phan Công Thành, khi người dùng cố tình nhập ngày bắt đầu LỚN HƠN ngày kết thúc (ví dụ: bắt đầu 2026-11-05 nhưng kết thúc 2026-11-01), hệ thống phản ứng ra sao?

- **A.** Vẫn lưu bình thường làm hỏng thuật toán SLA.
- **B.** Hệ thống từ chối dữ liệu và hiển thị thông báo lỗi: 'Ngày bắt đầu hoặc ngày kết thúc không hợp lệ'.
- **C.** Tự động đổi chỗ 2 ngày cho nhau mà không thông báo.
- **D.** Khóa máy tính của người dùng.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** TC03 kiểm thử độ vững chãi của ràng buộc logic thời gian, bảo vệ tính nhất quán của thuật toán tính SLA.

#### Câu 84. Trong Test Case 4 (TC04) của Phan Công Thành, khi người dùng nhập từ khóa tìm kiếm là 'Tết', kết quả mong đợi là gì?

- **A.** Không tìm thấy kết quả nào.
- **B.** Hiển thị tất cả các ngày nghỉ lễ có tên phù hợp chứa từ khóa 'Tết' (ví dụ: Tết Dương lịch, Tết Nguyên Đán).
- **C.** Hiển thị danh sách sinh viên tên Tết.
- **D.** Báo lỗi cú pháp SQL.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** TC04 xác nhận tính năng tìm kiếm tương đối hoạt động chính xác và nhạy bén.

#### Câu 85. Trong Test Case 5 (TC05) của Phan Công Thành, chức năng Sửa ngày nghỉ lễ được kiểm thử như thế nào?

- **A.** Chọn một ngày nghỉ lễ có sẵn, thay đổi thông tin và lưu lại thành công; thông tin mới hiển thị cập nhật chính xác trong danh sách.
- **B.** Xóa ngày lễ đó và nhập lại ngày mới.
- **C.** Chỉ cho phép xem chứ không được sửa.
- **D.** Đổi tên ngày lễ thành số ngẫu nhiên.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** TC05 chứng minh thao tác cập nhật (Update) dữ liệu ngày nghỉ lễ diễn ra mượt mà, đúng chuẩn CRUD.

#### Câu 86. Chức năng Xóa ngày nghỉ lễ được Phan Công Thành kiểm tra riêng biệt và đạt trạng thái gì trong báo cáo?

- **A.** FAILED (Lỗi)
- **B.** PASS (Hoạt động thành công, xóa bản ghi an toàn qua HTTP POST)
- **C.** Chưa kiểm tra
- **D.** Bị bỏ qua

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Xác nhận trọn vẹn chu trình CRUD 4 thao tác: Thêm (C), Đọc/Tìm kiếm (R), Sửa (U), Xóa (D) đều đạt điểm PASS tuyệt đối.

#### Câu 87. Câu lệnh truy vấn SQL số 1 trong tệp `PhanCongThanh_CauLenhTruyVan_Admin.sql` lấy danh sách ngày nghỉ lễ với mệnh đề sắp xếp nào?

- **A.** `ORDER BY RAND()`
- **B.** `ORDER BY start_date DESC;`
- **C.** `ORDER BY id ASC`
- **D.** `ORDER BY name`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn `SELECT id, name, start_date, end_date, is_recurring, created_at FROM holidays ORDER BY start_date DESC;` đưa các ngày lễ mới nhất lên đầu danh sách.

#### Câu 88. Câu lệnh truy vấn SQL số 2 của Phan Công Thành dùng để tìm kiếm ngày nghỉ lễ theo tên có cú pháp chuẩn là gì?

- **A.** `SELECT * FROM holidays WHERE name = 'Tet'`
- **B.** `SELECT id, name, start_date, end_date, is_recurring FROM holidays WHERE name LIKE '%Tet%' ORDER BY start_date DESC;`
- **C.** `DELETE FROM holidays WHERE name = 'Tet'`
- **D.** `SELECT COUNT(*) FROM holidays`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn tìm kiếm mờ sử dụng cặp ký tự đại diện `%` ở hai đầu từ khóa để so khớp mọi vị trí xuất hiện trong chuỗi.

#### Câu 89. Câu lệnh truy vấn SQL số 3 của Phan Công Thành dùng hàm tổng hợp nào để đếm tổng số ngày nghỉ lễ đang có trong hệ thống?

- **A.** `SUM(id)`
- **B.** `SELECT COUNT(*) AS total_holidays FROM holidays;`
- **C.** `AVG(id)`
- **D.** `MAX(start_date)`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `COUNT(*)` đếm nhanh chóng tổng số lượng bản ghi phục vụ hiển thị số liệu tổng quan trên thanh thống kê của Admin.

#### Câu 90. Câu lệnh truy vấn SQL số 4 của Phan Công Thành dùng để xem chi tiết một ngày nghỉ lễ theo ID có cú pháp là gì?

- **A.** `SELECT * FROM holidays WHERE id = 1;`
- **B.** `SELECT * FROM holidays WHERE name = 1;`
- **C.** `UPDATE holidays SET id = 1;`
- **D.** `DROP TABLE holidays;`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truy vấn chi tiết 1 bản ghi bằng khóa chính (`WHERE id = :id`) có tốc độ thực thi tức thì đạt chuẩn tối ưu O(1) nhờ chỉ mục Primary Key.

#### Câu 91. Câu lệnh truy vấn SQL số 5 của Phan Công Thành phục vụ chức năng phân trang danh sách với 10 bản ghi đầu tiên được viết như thế nào?

- **A.** `SELECT * FROM holidays ORDER BY start_date DESC LIMIT 10 OFFSET 0;`
- **B.** `SELECT * FROM holidays WHERE id BETWEEN 1 AND 10;`
- **C.** `SELECT * FROM holidays LIMIT 100;`
- **D.** `SELECT TOP 10 * FROM holidays;`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cú pháp chuẩn của MySQL: `LIMIT 10 OFFSET 0` lấy 10 bản ghi bắt đầu từ vị trí thứ 0, bảo đảm trang web tải nhẹ nhàng và chuyên nghiệp.

#### Câu 92. Để lấy danh sách ngày nghỉ lễ ở Trang số 2 (mỗi trang 10 bản ghi), giá trị `OFFSET` trong câu lệnh SQL số 5 sẽ là bao nhiêu?

- **A.** `OFFSET 0`
- **B.** `OFFSET 10` (Bỏ qua 10 bản ghi của Trang 1)
- **C.** `OFFSET 2`
- **D.** `OFFSET 20`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Công thức phân trang chuẩn: `offset = (page - 1) * perPage = (2 - 1) * 10 = 10`.

#### Câu 93. Trong mã nguồn PHP của `HolidayController.php`, hàm nào được sử dụng để lọc và làm sạch chuỗi đầu vào tránh lỗi XSS trước khi hiển thị ra giao diện?

- **A.** `addslashes()`
- **B.** `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`
- **C.** `strip_tags()`
- **D.** `md5()`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm chuẩn `htmlspecialchars()` mã hóa các ký tự đặc biệt `<, >, ", '` thành thực thể HTML, bảo đảm chống triệt để lỗ hổng XSS (Cross-Site Scripting).

#### Câu 94. Khi thực hiện truy vấn tìm kiếm bằng PDO trong `HolidayController`, việc gán tham số được viết bằng phương thức nào?

- **A.** `$stmt->bindValue(':keyword', '%' . $kw . '%', PDO::PARAM_STR);`
- **B.** `$stmt->query("name = " . $kw);`
- **C.** `eval($kw);`
- **D.** `system($kw);`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Sử dụng `bindValue` với kiểu dữ liệu tường minh `PDO::PARAM_STR` bảo đảm câu truy vấn an toàn và chuẩn mực.

#### Câu 95. Tên cơ sở dữ liệu được chỉ định trong tệp truy vấn SQL cá nhân của Phan Công Thành là gì?

- **A.** `mysql`
- **B.** `helpdesk_thudo` (sử dụng qua lệnh `USE helpdesk_thudo;`)
- **C.** `test_database`
- **D.** `phpmyadmin`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khớp chính xác với tên CSDL chính thức của dự án BTL Nhóm 1 trên máy chủ trường Đại học Thủ Đô Hà Nội.

#### Câu 96. Khi người dùng bấm nút Sửa trên danh sách ngày nghỉ lễ, giao diện điều hướng sang đường dẫn URL nào?

- **A.** `admin/holidays.php?action=edit&id={id}` (hoặc mở modal sửa trực tiếp với ID tương ứng)
- **B.** `admin/delete.php`
- **C.** `logout.php`
- **D.** `index.php?action=login`

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Truyền tham số `id` trên URL để Controller nạp dữ liệu cũ của ngày lễ đó lên form phục vụ thao tác chỉnh sửa.

#### Câu 97. Nếu Quản trị viên nhập ngày bắt đầu và ngày kết thúc hợp lệ nhưng trùng khớp hoàn toàn với một ngày lễ đã có, hệ thống có cảnh báo không?

- **A.** Có; hệ thống kiểm tra và nhắc nhở ngày nghỉ lễ đã tồn tại trong danh mục để tránh trùng lặp ngày nghỉ khi tính SLA.
- **B.** Không, tạo 100 dòng giống nhau.
- **C.** Xóa hết dữ liệu cũ.
- **D.** Khóa quyền truy cập của Admin.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính năng kiểm tra trùng lặp giúp danh mục ngày nghỉ lễ luôn tinh gọn và không bị nhân đôi thời gian trừ giờ của KTV.

#### Câu 98. Khi thực hiện lệnh `DELETE FROM holidays WHERE id = :id`, PDO Statement trả về kết quả số dòng bị tác động qua phương thức nào?

- **A.** `$stmt->fetchAll()`
- **B.** `$stmt->rowCount()` (trả về 1 nếu xóa thành công, trả về 0 nếu ID không tồn tại)
- **C.** `$stmt->errorCode()`
- **D.** `count($stmt)`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `rowCount()` là phương thức chuẩn của PDO để kiểm tra số lượng bản ghi bị ảnh hưởng bởi các câu lệnh INSERT, UPDATE, DELETE.

#### Câu 99. Trong bảng `holidays`, trường `created_at` có kiểu dữ liệu là gì trong CSDL MySQL?

- **A.** `INT`
- **B.** `TIMESTAMP` với thuộc tính `DEFAULT CURRENT_TIMESTAMP`
- **C.** `VARCHAR(50)`
- **D.** `TEXT`

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tự động ghi nhận thời điểm tạo bản ghi theo múi giờ máy chủ một cách chính xác.

#### Câu 100. Tổng kết lại, đóng góp chuyên môn cốt lõi của sinh viên Phan Công Thành (224001746) trong Đồ án Helpdesk Nhóm 1 là gì?

- **A.** Chỉ phụ trách công tác chuẩn bị phòng họp.
- **B.** Xây dựng trọn vẹn Trung tâm Quản trị Admin, thiết kế phân hệ Quản lý ngày nghỉ lễ `holidays` phục vụ thuật toán SLA giờ hành chính, công cụ phát/thu hồi cảnh báo sự cố khẩn cấp diện rộng và giải pháp nhập tài khoản sinh viên hàng loạt bằng Excel/CSV chuẩn 3NF.
- **C.** Chỉ viết tài liệu thuyết trình PowerPoint.
- **D.** Không tham gia vào quá trình lập trình hệ thống.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phan Công Thành hoàn thành xuất sắc 17% khối lượng dự án với các module quản trị nền tảng mạnh mẽ, cung cấp công cụ điều hành then chốt cho đội ngũ quản trị nhà trường.

---

## <a id="bang-dap-an"></a>📊 BẢNG TỔNG HỢP ĐÁP ÁN 100 CÂU HỎI (PHAN CÔNG THÀNH)

| Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | B | **21** | A | **41** | B | **61** | B | **81** | A |
| **2** | B | **22** | B | **42** | B | **62** | B | **82** | B |
| **3** | B | **23** | B | **43** | B | **63** | B | **83** | B |
| **4** | B | **24** | B | **44** | B | **64** | B | **84** | B |
| **5** | B | **25** | B | **45** | B | **65** | B | **85** | A |
| **6** | B | **26** | B | **46** | B | **66** | A | **86** | B |
| **7** | B | **27** | B | **47** | B | **67** | B | **87** | B |
| **8** | B | **28** | A | **48** | B | **68** | B | **88** | B |
| **9** | B | **29** | B | **49** | B | **69** | B | **89** | B |
| **10** | B | **30** | B | **50** | B | **70** | A | **90** | A |
| **11** | B | **31** | B | **51** | B | **71** | B | **91** | A |
| **12** | A | **32** | B | **52** | B | **72** | B | **92** | B |
| **13** | A | **33** | B | **53** | A | **73** | B | **93** | B |
| **14** | A | **34** | B | **54** | B | **74** | B | **94** | A |
| **15** | B | **35** | B | **55** | B | **75** | B | **95** | B |
| **16** | B | **36** | B | **56** | B | **76** | A | **96** | A |
| **17** | A | **37** | A | **57** | B | **77** | B | **97** | A |
| **18** | B | **38** | B | **58** | B | **78** | A | **98** | B |
| **19** | B | **39** | B | **59** | A | **79** | B | **99** | B |
| **20** | B | **40** | B | **60** | B | **80** | B | **100** | B |

---

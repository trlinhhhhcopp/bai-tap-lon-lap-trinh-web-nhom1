# TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
### KHOA CÔNG NGHỆ THÔNG TIN · BÀI TẬP LỚN LẬP TRÌNH WEB (HỌC KỲ I)
---
# 🎓 CHỦ ĐỀ 9: HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
> **Phiên bản:** Chuẩn Đại học Thủ Đô Hà Nội · ITIL v4 Service Desk · Pure Online Services · PHP 8 Clean MVC  
> **Hệ thống Portal:** `https://it-support.daihocthudo.edu.vn`  
> **📄 BẢN BÁO CÁO BÀI TẬP LỚN ĐẦY ĐỦ (PDF):** [**docs/Bao_Cao_BTL_Helpdesk_Nhom1.pdf**](docs/Bao_Cao_BTL_Helpdesk_Nhom1.pdf) *(Bản in A4 màu chuẩn học thuật nộp Giảng viên)*  

---

## 👥 1. DANH SÁCH THÀNH VIÊN NHÓM 1 & PHÂN CÔNG MODEL ĐỘC LẬP (RACI)

> **Cam kết Kiến trúc:** Phân công 5 thành viên phụ trách **5 phân hệ hoàn toàn tách biệt**, mỗi thành viên sở hữu độc quyền các Model và bảng CSDL tương ứng, **tuyệt đối không bị chồng lấn hay phụ thuộc chéo Model**, giúp quá trình phát triển song song mượt mà và bảo vệ đồ án rành mạch trước Giảng viên:

| STT | Họ và Tên | Mã Sinh Viên | Vai trò Nhóm | Phân hệ Phụ trách (MVC & CSDL) | Model & Bảng CSDL Độc quyền | Email Sinh Viên Trường |
|:---:|---|:---:|:---:|---|---|---|
| 1 | **Phạm Thị Thanh Ngân** | **224001736** | **Trưởng nhóm** | **Core Architecture & Ticket Engine**<br>(Nhân MVC, Triage điều phối phiếu, SLA, RBAC) | • `Ticket.php` (`tickets`)<br>• `Reply.php` (`ticket_replies`) | `224001736@daihocthudo.edu.vn` |
| 2 | **Trần Thục Linh** | **224001729** | Thành viên | **User Management & Auth Security**<br>(Xác thực, SSPR Email, Trạm cứu hộ tài khoản khách) | • `User.php` (`users`)<br>• `PasswordReset.php` (`password_resets`) | `224001729@daihocthudo.edu.vn` |
| 3 | **Lê Hà My** | **224001735** | Thành viên | **Academic & Lecturer Portal**<br>(Cổng Giảng viên, Phân công 1 GV/1 Lớp HP, Mở lại Deadline) | • `CourseAssignment.php` (`course_assignments`)<br>• `ClassModel.php`, `Course.php`, `ClassStudent.php` | `224001735@daihocthudo.edu.vn` |
| 4 | **Nguyễn Ánh Nhi** | **224001742** | Thành viên | **Student Portal & CSAT Rating**<br>(Cổng Sinh viên, Đánh giá 5 sao CSAT, Đính kèm bài tập) | • `Rating.php` (`ticket_ratings`)<br>• `Category.php` (`categories`), `Priority.php` (`priorities`) | `224001742@daihocthudo.edu.vn` |
| 5 | **Phan Công Thành** | **224001746** | Thành viên | **Admin Center & Holiday Engine**<br>(Admin Center, Quản lý ngày nghỉ lễ SLA, Script dời lịch LMS) | • `Holiday.php` (`holidays`)<br>• `AdminReportModel.php` (Báo cáo Thống kê) | `224001746@daihocthudo.edu.vn` |

---

## 🌟 2. DANH SÁCH TÍNH NĂNG MỚI ĐÃ CẬP NHẬT & TỐI ƯU HÓA TOÀN DIỆN

Hệ thống đã được tinh chỉnh, nâng cấp và hoàn thiện 100% theo các yêu cầu sư phạm và thực tế vận hành:

### 2.1. Phân quyền vai trò chuẩn xác và kiểm soát thao tác nghiêm ngặt (RBAC)
* **Kỹ thuật viên (KTV / Agent):**
  * **Tuyệt đối KHÔNG có nút sửa phiếu:** KTV không được phép can thiệp hay sửa đổi tiêu đề, mô tả hoặc thông tin do người dùng khai báo (chặn cả ở View và Backend Guard tại `tickets/edit.php`).
  * **Xác nhận sự cố kỹ thuật hợp lệ:** KTV kiểm tra log mạng / hạ tầng & bấm nút *"KTV Xác Nhận Có Lỗi"* để chuyển biên bản sự cố khách quan sang Giảng viên bộ môn xem xét gia hạn.
  * **Hủy báo lỗi (SV Báo Sai):** Nếu kiểm tra không có lỗi hoặc sinh viên báo sai, KTV bấm nút hủy báo lỗi &rarr; Phiếu tự động chuyển sang trạng thái **Hoàn tất & Đóng** (`closed`), loại bỏ hoàn toàn phiếu rác mà Giảng viên không cần xử lý.
  * **Duyệt đơn cứu hộ tài khoản:** KTV xác thực hồ sơ đơn cứu hộ của khách vãng lai trước khi chuyển tiếp lên Admin phê duyệt mở khóa.
  * **Báo cáo Sinh viên vi phạm quy chế:** KTV báo cáo sinh viên spam / nghịch hệ thống &rarr; Phiếu tự động chuyển sang trạng thái `resolved` chuyển sang Admin xem xét kỷ luật và khóa tài khoản. KTV có nút *"Hủy báo cáo vi phạm"* để phục hồi tài khoản nếu xác minh lại thấy hợp lệ.
  * **Nghiệm thu giải pháp:** KTV chỉ có thẩm quyền chuyển trạng thái đến tối đa `resolved` (Đã có giải pháp). Trạng thái `closed` (Đóng hoàn tất) do Sinh viên nghiệm thu đánh giá CSAT hoặc tự động đóng sau 24h.
* **Quản trị viên (Admin):**
  * **Admin KHÔNG có nút sửa phiếu:** Quản trị viên không sửa nội dung phiếu của người dùng.
  * **Admin CHỈ CÓ NÚT XÓA PHIẾU:** Trang bị nút **`🗑️ Xóa`** trên từng dòng danh sách phiếu (`tickets/index.php`) và nút **`🗑️ Xóa phiếu`** trên thanh công cụ chi tiết (`tickets/show.php`), thực thi qua phương thức POST an toàn có xác nhận.
  * **Quyền xem & ghi nhận lịch sử:** Admin không chat phản hồi trực tiếp với người dùng trên phiếu; chỉ theo dõi tiến độ và thực hiện phân công điều chuyển sang KTV khác.
  * **Khóa điều chuyển khi hoàn tất:** Phiếu đã ở trạng thái `resolved` hoặc `closed` sẽ tự động khóa quyền điều chuyển phân công.
  * **Toàn quyền cứu hộ & kỷ luật:** Admin trực tiếp duyệt mở khóa đơn cứu hộ và đặt lại mật khẩu về `123456`, hoặc thực hiện Khóa cứng tài khoản sinh viên vi phạm.
* **Người gửi phiếu (Sinh viên & Giảng viên tạo phiếu):**
  * **Đặc quyền Sửa & Xóa trong 15 phút đầu:** Người tạo phiếu chính chủ có toàn quyền chỉnh sửa nội dung hoặc thu hồi (xóa) phiếu trong vòng **15 phút đầu tiên** kể từ khi gửi (khi phiếu đang ở trạng thái `new` hoặc `waiting_requester`).
  * **Khóa tự động sau 15 phút:** Hết 15 phút, tính năng tự động khóa nhằm đảm bảo tính minh bạch dữ liệu khi KTV đã bắt đầu xử lý.
  * **Nghiệm thu CSAT:** Sinh viên trực tiếp chấm điểm sao (1 - 5 sao) và nhận xét chất lượng hỗ trợ.
* **Giảng viên bộ môn (Lecturer):**
  * **Thẩm quyền học thuật xem xét gia hạn:** Chỉ hiển thị khi KTV đã cấp biên bản xác nhận lỗi kỹ thuật hợp lệ.
  * **Phương án 1 (Chấp thuận):** Giảng viên chọn Deadline gia hạn mới &rarr; Hệ thống tự động chuyển phiếu sang Đã hoàn tất, mở quyền cho SV nộp bài.
  * **Phương án 2 (Từ chối):** Giảng viên từ chối gia hạn kèm lý do sư phạm &rarr; Hệ thống tự động chuyển phiếu sang Đã hoàn tất & Đóng.
  * **Giao diện một chiều:** Sau khi chọn Chấp thuận hoặc Từ chối, form quyết định sẽ tự động biến mất và thay bằng biên bản chính thức, ngăn ngừa thao tác trùng lặp.

### 2.2. Kiểm tra & Cảnh báo phiếu trùng lặp thông minh (Duplicate Incident Detection)
* **Quy tắc nhận diện trùng lặp:** Cùng một người gửi, cùng nhóm dịch vụ sự cố hoặc cùng buổi học, nếu đã gửi trong ngày hoặc phiếu trước đó vẫn đang trong quá trình xử lý (`new`, `in_progress`, `waiting_requester`).
* **Áp dụng toàn diện:** Kiểm tra trên form gửi phiếu Sinh viên, Giảng viên, và Trạm cứu hộ tài khoản khách.
* **Cảnh báo chuẩn HTML:** Render thông báo đẹp mắt kèm mã phiếu cũ, tiêu đề, trạng thái hiện tại và link truy cập nhanh để trao đổi tiếp thay vì tạo thêm phiếu mới gây quá tải hệ thống. Sửa triệt để lỗi in thô thẻ `<strong> < >`.

### 2.3. Hiệu chỉnh chuẩn xác SLA theo Giờ làm việc hành chính & Lịch nghỉ lễ
* **Hạn tiếp nhận cam kết (First Response SLA):** Cố định **15 phút làm việc hành chính** kể từ khi gửi phiếu (tự động bỏ qua Thứ 7, Chủ Nhật và ngày nghỉ lễ; nếu gửi ngoài giờ sẽ tính từ 08:00 ngày làm việc tiếp theo + 15 phút = 08:15:00).
* **Thời gian giải quyết sự cố (Due At):** Giữ nguyên chuẩn hóa theo danh mục dịch vụ và mức độ ưu tiên của phiếu.
* **Khung giờ làm việc KTV:** Kỹ thuật viên chỉ làm việc từ **Thứ 2 đến Thứ 6** (Sáng: 08:00 - 12:00, Chiều: 13:30 - 17:30).
* **Bỏ qua ngày nghỉ:** Tự động loại trừ Thứ Bảy, Chủ Nhật và các ngày nghỉ lễ cấu hình trong CSDL (`holidays`).
* **Phiếu gửi ngoài giờ:** Phiếu gửi sau 17h30 hoặc trong ngày nghỉ sẽ tự động bắt đầu tính hạn SLA từ **08:00 của ngày làm việc tiếp theo**.
* **SLA Trạm cứu hộ:** Tính chuẩn xác **2.0 giờ làm việc hành chính**.

### 2.4. Trạm cứu hộ tài khoản khách (Guest Rescue Station)
* **Xác thực sinh viên:** Kiểm tra mã sinh viên thực tế tồn tại trong CSDL, loại bỏ đơn giả mạo.
* **Quy trình phối hợp 2 cấp:** KTV xác thực thông tin sự cố &rarr; Chuyển tiếp tới Admin duyệt mở khóa và reset mật khẩu về `123456`.
* **Tra cứu an toàn:** Tra cứu tiến độ đơn qua Mã vé cứu hộ + Mã PIN bảo mật.

### 2.5. Tối ưu hóa UI/UX & Bộ lọc Dữ liệu
* **Bỏ pill lọc trùng lặp:** Loại bỏ 4 nút pill ở đầu trang `tickets/index.php` do đã có form chọn trạng thái bên dưới.
* **Bộ lọc bảo mật SV Vi Phạm:** Nút lọc *"SV Vi Phạm (KTV Báo Cáo)"* chỉ hiển thị với Admin và chính sinh viên đó.
* **Nút Ẩn/Hiện bảng dữ liệu (Collapsible Table):** Bổ sung nút toggle bảng dữ liệu kèm icon chuyển đổi trên tất cả các bảng ở mọi vai trò (Sinh viên, Giảng viên, KTV, Admin).
* **Hoàn thiện CSAT:** Sinh viên chấm điểm 1-5 sao mượt mà, tự động cập nhật điểm trung bình CSAT của từng KTV.

### 2.6. Hệ thống Thông báo Thời gian thực Đa vai trò (Notification Center & Banner Đầu Trang)
* **Nút Quả Chuông 🔔 trên Navbar:** Tích hợp đồng bộ trên thanh điều hướng cho tất cả các role, có badge đếm số thông báo chưa đọc và Dropdown panel quản lý thông báo.
* **Banner Thông Báo Nổi Bật Đặt Ở Đầu Trang (Sau Phần Tổng Quan):** Xuất hiện ngay sau phần thống kê tổng quan của từng vai trò (Sinh viên, Giảng viên, KTV, Admin) với đầy đủ thông tin sự cố, nút thao tác nhanh xem chi tiết và nút ẩn/đã đọc.
* **Kịch bản thông báo tự động:**
  * **Sinh viên:** Nhận thông báo khi KTV phản hồi phiếu, phiếu đã giải quyết cần chấm CSAT, hoặc Thầy/Cô giao bài tập / mở lại Deadline.
  * **Giảng viên:** Nhận thông báo khi Phòng Đào tạo phân công lớp học phần mới, hoặc KTV xác nhận lỗi sự cố cần xem xét mở Deadline.
  * **Kỹ thuật viên:** Nhận thông báo khi có phiếu sự cố mới được gán cho mình, người dùng phản hồi chat, hoặc có đơn cứu hộ mới cần xác thực.
  * **Quản trị viên (Admin):** Nhận thông báo khi KTV xác thực đơn cứu hộ cần phê duyệt mở khóa, hoặc KTV báo cáo sinh viên vi phạm quy chế.
* **Tương tác AJAX / Fetch API:** Đánh dấu đã đọc từng thông báo hoặc toàn bộ thông báo tức thì không cần tải lại trang.

---

## 🔑 3. BẢNG TÀI KHOẢN KIỂM THỬ TỪNG VAI TRÒ (TEST ACCOUNTS)

> Mật khẩu mặc định toàn hệ thống: **`123456`**

| Vai trò (Role) | Mã người dùng | Email đăng nhập | Mật khẩu chung | Mô tả quyền hạn & Nhiệm vụ kiểm thử |
|---|---|---|---|---|
| **Quản trị viên (Admin)** | `ADMIN001` | `admin@daihocthudo.edu.vn` | `123456` | Xóa phiếu, duyệt mở khóa cứu hộ, quản lý user & lịch lễ, giám sát tải KTV |
| **Kỹ thuật viên 1 (SSO)** | `KTV001` | `ktv.an@daihocthudo.edu.vn` | `123456` | KTV chuyên trách SSO/Tài khoản: Bàn làm việc KTV, duyệt đơn cứu hộ khách |
| **Kỹ thuật viên 2 (Mạng)** | `KTV002` | `ktv.binh@daihocthudo.edu.vn` | `123456` | KTV chuyên trách Mạng/Wifi: Xác nhận lỗi kỹ thuật, hủy báo lỗi nếu SV báo sai |
| **Giảng viên (Lecturer)** | `GV001` | `nguyenvangiang@daihocthudo.edu.vn` | `123456` | TS. Nguyễn Văn Giảng: Xem lớp phụ trách, Thẩm quyền Chấp thuận / Từ chối gia hạn Deadline |
| **Sinh viên 1 (Student)** | `224001736` | `224001736@daihocthudo.edu.vn` | `123456` | Phạm Thị Thanh Ngân: Gửi phiếu, Sửa/Xóa phiếu trong 15p đầu, Đánh giá CSAT 1-5 sao |
| **Sinh viên 2 (Student)** | `224001729` | `224001729@daihocthudo.edu.vn` | `123456` | Trần Thục Linh: Test nộp bài tập, tra cứu tiến độ xử lý |

---

## 🛡️ 4. MA TRẬN PHÂN QUYỀN TRUY CẬP (RBAC MATRIX)

| Chức năng / Hành động | Khách vãng lai | Sinh viên | Giảng viên | Kỹ thuật viên (KTV) | Quản trị viên (Admin) |
|---|:---:|:---:|:---:|:---:|:---:|
| Gửi đơn cứu hộ tài khoản khách | ✅ | ✅ | ✅ | ❌ | ❌ |
| Tra cứu đơn cứu hộ bằng mã vé + PIN | ✅ | ✅ | ✅ | ✅ | ✅ |
| Gửi phiếu báo sự cố mới | ❌ | ✅ | ✅ (Khẩn cấp) | ❌ | ✅ |
| Chỉnh sửa nội dung phiếu | ❌ | ✅ (Trong 15 phút đầu) | ✅ (Trong 15 phút đầu) | ❌ **(CẤM)** | ❌ **(CẤM)** |
| Xóa phiếu hỗ trợ | ❌ | ✅ (Thu hồi trong 15p đầu) | ✅ (Thu hồi trong 15p đầu) | ❌ **(CẤM)** | ✅ **(TOÀN QUYỀN XÓA)** |
| Xác nhận lỗi kỹ thuật khách quan | ❌ | ❌ | ❌ | ✅ | ❌ |
| Hủy báo lỗi (SV Báo Sai) | ❌ | ❌ | ❌ | ✅ | ❌ |
| Gia hạn hoặc Từ chối mở lại Deadline | ❌ | ❌ | ✅ (Thẩm quyền học thuật) | ❌ | ❌ |
| Báo cáo sinh viên vi phạm / Hủy báo cáo | ❌ | ❌ | ❌ | ✅ | ❌ |
| Khóa cứng tài khoản sinh viên vi phạm | ❌ | ❌ | ❌ | ❌ | ✅ |
| Duyệt mở khóa đơn cứu hộ tài khoản | ❌ | ❌ | ❌ | ✅ (Xác thực) | ✅ (Phê duyệt & Reset pass) |
| Nghiệm thu & Chấm điểm CSAT 1-5 sao | ❌ | ✅ (Phiếu của mình) | ❌ | ❌ | ❌ |
| Quản trị người dùng & Phân vai trò | ❌ | ❌ | ❌ | ❌ | ✅ |
| Cấu hình ngày nghỉ lễ SLA toàn trường | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 🛣️ 5. DANH SÁCH ROUTE MVC CHÍNH THỨC CỦA HỆ THỐNG

| STT | Phương thức | Đường dẫn (URL) | Controller / File | Quyền hạn áp dụng | Ghi chú nghiệp vụ |
|:---:|:---:|---|---|:---:|---|
| 1 | `GET/POST` | `/index.php?action=login` | `AuthController@login` | Khách / Tất cả | Xác thực Bcrypt `password_verify`, session an toàn |
| 2 | `GET` | `/index.php?action=logout` | `AuthController@logout` | Đã đăng nhập | Hủy session, đăng xuất an toàn |
| 3 | `GET/POST` | `/index.php?action=guest_ticket` | `GuestTicketController` | Khách | Gửi đơn cứu hộ tài khoản bị khóa cứng |
| 4 | `GET` | `/index.php?action=track_guest` | `GuestTicketController` | Khách | Tra cứu đơn cứu hộ qua Mã vé + PIN |
| 5 | `GET/POST` | `/tickets/create.php` | `TicketService` | SV, GV, Admin | Tạo phiếu sự cố mới (kiểm tra trùng lặp) |
| 6 | `GET` | `/tickets/index.php` | `TicketRepository` | Đã đăng nhập | Danh sách phiếu có phân trang, bộ lọc nâng cao, nút xóa Admin |
| 7 | `GET/POST` | `/tickets/show.php?id=...` | `TicketService` | Chủ phiếu, KTV, GV, Admin | Chi tiết tiến độ SLA, thẩm quyền KTV/GV, xóa Admin |
| 8 | `GET/POST` | `/tickets/edit.php?id=...` | `TicketRepository` | Chủ phiếu trong 15p đầu | Chỉnh sửa phiếu (chặn tuyệt đối KTV và Admin) |
| 9 | `POST` | `/tickets/delete.php` | `TicketRepository` | Admin hoặc Chủ phiếu trong 15p | Xóa phiếu an toàn bằng POST |
| 10 | `GET` | `/student/index.php` | `CourseController` | Sinh viên | Cổng học phần, theo dõi bài tập, đính kèm minh chứng |
| 11 | `GET/POST` | `/student/ratings.php` | `RatingController` | Sinh viên | Đánh giá sao CSAT và nhận xét chất lượng |
| 12 | `GET` | `/agent/index.php` | `AgentWorkspace` | KTV, Admin | Bàn làm việc KTV, hàng đợi theo `due_at ASC` |
| 13 | `GET/POST` | `/admin/users.php` | `AdminUserController` | Admin | Quản trị tài khoản, khóa cứng, đổi vai trò |
| 14 | `GET/POST` | `/admin/holidays.php` | `HolidayController` | Admin | Quản trị ngày nghỉ lễ toàn trường, hiệu chỉnh SLA |
| 15 | `GET/POST` | `/assignments/index.php` | `CourseAssignmentController` | GV, Admin | Phân công đào tạo (1 GV / 1 lớp HP duy nhất) |

---

## 🗄️ 6. THIẾT KẾ CƠ SỞ DỮ LIỆU (13 BẢNG CHUẨN HÓA 3NF)

1. **`categories`:** 5 nhóm dịch vụ trực tuyến (`CAT_SSO`, `CAT_MAIL`, `CAT_QLDT`, `CAT_CANVAS`, `CAT_WIFI`) kèm hạn SLA chuẩn.
2. **`priorities`:** 4 mức ưu tiên (`URGENT`, `HIGH`, `MED`, `LOW`) kèm hệ số rút ngắn SLA `sla_factor`.
3. **`classes`:** Danh mục lớp sinh hoạt (`D24-CNTT1`, `D24-CNTT2`) khóa D24 ĐH Thủ Đô Hà Nội.
4. **`courses`:** Danh mục 6 môn học (`WEB101`, `NET201`, `DB102`, `OS101`, `SE201`, `LMS301`).
5. **`users`:** Quản trị người dùng; mật khẩu mặc định `123456`; cờ khóa mềm, khóa cứng buộc thôi học; chuyên môn KTV.
6. **`class_students`:** Quan hệ N-N giữa Lớp và Sinh viên (`UNIQUE (class_id, student_id)`).
7. **`course_assignments`:** Phân công giảng dạy; **1 lớp học phần chỉ có 1 giáo viên phụ trách duy nhất** (`UNIQUE (class_id, course_id, semester)`).
8. **`holidays`:** Danh mục ngày nghỉ lễ toàn trường phục vụ loại trừ giờ hành chính tính SLA.
9. **`password_resets`:** Token SHA-256 khôi phục mật khẩu tự động qua email (15 phút).
10. **`tickets`:** Bảng trung tâm lưu trữ phiếu hỗ trợ, mốc SLA, cờ xác nhận KTV, phê duyệt mở hạn của GV.
11. **`ticket_replies`:** Trao đổi hai chiều và ghi chú nội bộ bảo mật (`is_internal = 1`).
12. **`ticket_ratings`:** Đánh giá độ hài lòng CSAT 1–5 sao của sinh viên (quan hệ 1-1 `UNIQUE (ticket_id)`).
13. **`notifications`:** Hệ thống thông báo thời gian thực đa vai trò (SV, GV, KTV, Admin), hỗ trợ Banner nổi bật đầu trang & Quả chuông 🔔.

---

## ⚡ 7. HƯỚNG DẪN CÀI ĐẶT & CHẠY DỰ ÁN

1. **Cấu hình Web Server (XAMPP):**
   * Đặt thư mục dự án vào `C:/xampp/htdocs/web01/bai-tap-lon-lap-trinh-web-nhom1`
   * Khởi động **Apache** và **MySQL** trong XAMPP Control Panel.
2. **Khởi tạo Cơ sở dữ liệu:**
   * Truy cập `http://localhost/phpmyadmin`
   * Tạo cơ sở dữ liệu tên: **`helpdesk_thudo`** (Collation: `utf8mb4_unicode_ci`)
   * Import tập tin DDL tạo bảng: [`database/schema.sql`](database/schema.sql)
   * Import tập tin DML dữ liệu mẫu: [`database/seed.sql`](database/seed.sql)
3. **Truy cập ứng dụng:**
   * Mở trình duyệt truy cập: `http://localhost/web01/bai-tap-lon-lap-trinh-web-nhom1/`
   * Đăng nhập bằng một trong các tài khoản trong bảng kiểm thử ở Mục 3 (Mật khẩu: `123456`).

---

🏆 **KẾT LUẬN & CAM KẾT CHẤT LƯỢNG:**  
Hệ thống Helpdesk CNTT Nhóm 1 đã được hoàn thiện chỉn chu, chuyên nghiệp, vượt qua tất cả các bài kiểm tra thực tế và sẵn sàng bảo vệ xuất sắc trước Hội đồng chấm điểm Bài tập lớn!

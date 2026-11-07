# NGÂN HÀNG 100 CÂU HỎI TRẮC NGHIỆM KIỂM TRA TOÀN DIỆN HỆ THỐNG HELPDESK CNTT (TUD)

> **Phân hệ:** Hệ Thống Hỗ Trợ Kỹ Thuật & Quản Lý Sự Cố Công Nghệ Thông Tin (IT Service Management Helpdesk)
> **Đơn vị:** Nhóm 1 – Trường Đại học Thủ Đô Hà Nội (TUD)
> **Bộ đề kiểm tra mới:** 100 câu hỏi chuyên sâu về Kiến trúc mã nguồn, Cơ sở dữ liệu 3NF, Thuật toán SLA, State Machine, Phân quyền RBAC và Xử lý Ngoại lệ Edge Cases.

---

## 📊 BẢNG MA TRẬN ĐÁP ÁN 100 CÂU HỎI (QUICK ANSWER KEY)

> **Phân bổ đáp án chuẩn:** 25 câu [A] · 25 câu [B] · 25 câu [C] · 25 câu [D] (Được xáo trộn ngẫu nhiên toàn diện)

| Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á | Câu | Đ/Á |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | `C` | **21** | `B` | **41** | `A` | **61** | `C` | **81** | `A` |
| **2** | `C` | **22** | `A` | **42** | `B` | **62** | `A` | **82** | `C` |
| **3** | `C` | **23** | `A` | **43** | `B` | **63** | `C` | **83** | `A` |
| **4** | `A` | **24** | `C` | **44** | `B` | **64** | `D` | **84** | `D` |
| **5** | `B` | **25** | `D` | **45** | `D` | **65** | `D` | **85** | `C` |
| **6** | `C` | **26** | `C` | **46** | `A` | **66** | `A` | **86** | `D` |
| **7** | `A` | **27** | `D` | **47** | `B` | **67** | `D` | **87** | `A` |
| **8** | `A` | **28** | `B` | **48** | `C` | **68** | `A` | **88** | `C` |
| **9** | `B` | **29** | `A` | **49** | `A` | **69** | `B` | **89** | `B` |
| **10** | `C` | **30** | `C` | **50** | `D` | **70** | `D` | **90** | `C` |
| **11** | `D` | **31** | `D` | **51** | `C` | **71** | `C` | **91** | `B` |
| **12** | `D` | **32** | `B` | **52** | `C` | **72** | `A` | **92** | `C` |
| **13** | `D` | **33** | `B` | **53** | `A` | **73** | `D` | **93** | `B` |
| **14** | `A` | **34** | `B` | **54** | `B` | **74** | `C` | **94** | `B` |
| **15** | `B` | **35** | `D` | **55** | `A` | **75** | `B` | **95** | `D` |
| **16** | `C` | **36** | `B` | **56** | `D` | **76** | `D` | **96** | `B` |
| **17** | `B` | **37** | `A` | **57** | `C` | **77** | `D` | **97** | `A` |
| **18** | `D` | **38** | `A` | **58** | `B` | **78** | `C` | **98** | `D` |
| **19** | `B` | **39** | `B` | **59** | `D` | **79** | `A` | **99** | `D` |
| **20** | `D` | **40** | `C` | **60** | `A` | **80** | `A` | **100** | `C` |

---

## 1. KIẾN TRÚC MÃ NGUỒN, CẤU TRÚC THƯ MỤC & LUỒNG ĐIỀU HƯỚNG (VỊ TRÍ & CHỨC NĂNG TỪNG FILE CODE)

#### Câu 1. Trong kiến trúc Luồng 2 (Router MVC thuần OOP), tệp mã nguồn 'index.php' nằm tại thư mục gốc của dự án đảm nhận vị trí và chức năng cốt lõi nào?

- **A.** Chứa toàn bộ giao diện HTML và trực tiếp thực thi hơn 2.500 dòng mã lệnh SQL.
- **B.** Là tệp phụ chỉ dùng để kiểm tra kết nối CSDL MySQL khi bảo trì hệ thống.
- **C.** Đóng vai trò là Front Controller duy nhất (tinh gọn 26 dòng), nạp cấu hình hệ thống từ 'app/bootstrap.php' và ủy quyền toàn bộ điều phối request cho 'App\Core\Router->dispatch()'.
- **D.** Là tệp cấu hình máy chủ Apache để ngăn chặn tấn công DDoS.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'index.php' tại gốc đóng vai trò là Front Controller duy nhất tiếp nhận mọi HTTP request, nạp bootstrap và chuyển giao cho Router điều phối theo mô hình Clean MVC.

#### Câu 2. Tệp mã nguồn 'app/bootstrap.php' nằm ở vị trí nào trong cấu trúc hệ sinh thái và chịu trách nhiệm thực thi các chức năng khởi tạo trọng yếu nào?

- **A.** Nằm trong thư mục public và chỉ dùng để hiển thị thông báo lỗi 404 Not Found.
- **B.** Nằm ở thư mục gốc và chịu trách nhiệm sao lưu định kỳ cơ sở dữ liệu MySQL.
- **C.** Nằm tại 'app/bootstrap.php', chịu trách nhiệm đăng ký PSR-4 Autoloader tự động nạp class, nạp kết nối PDO CSDL, thiết lập class alias tương thích và khai báo đăng ký toàn bộ 48 route endpoints của hệ thống.
- **D.** Nằm trong thư mục views để chứa các thẻ điều hướng chung (navbar, footer).

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'app/bootstrap.php' là trung tâm khởi động hệ thống, tự động nạp thư viện/class chuẩn PSR-4, thiết lập kết nối CSDL và nạp bảng định tuyến 48 routes.

#### Câu 3. Tệp 'app/Core/Router.php' nằm tại vị trí nào trong cấu trúc thư mục và đảm nhận chức năng điều hướng như thế nào?

- **A.** Nằm trong thư mục assets để điều hướng các tệp ảnh CSS/JavaScript.
- **B.** Nằm trong thư mục config để lưu trữ mật khẩu kết nối CSDL.
- **C.** Nằm tại 'app/Core/Router.php', chịu trách nhiệm bóc tách và chuẩn hóa URI (loại bỏ tiền tố thư mục XAMPP), hỗ trợ cả clean URL và query legacy ('?action=...'), thực thi chuỗi Middleware bảo vệ và gọi Controller/Action tương ứng.
- **D.** Nằm trong thư mục storage để ghi lại nhật ký truy cập hàng ngày của sinh viên.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'Router.php' là bộ định tuyến trung tâm bóc tách URI request, xử lý tương thích query legacy (?action=guest_ticket, ?action=forgot_password), chạy Middlewares và kích hoạt Action.

#### Câu 4. Lớp cơ sở 'app/Core/Controller.php' nằm ở đâu và cung cấp các phương thức dùng chung trọng yếu nào cho toàn bộ các Controller con trong hệ thống?

- **A.** Nằm tại 'app/Core/Controller.php', cung cấp các phương thức dùng chung gồm: 'view()' (render giao diện), 'json()' (trả về JSON API), 'redirect()' (chuyển hướng an toàn), 'flash()' (quản lý thông báo phiên) và 'user()' (lấy thông tin người dùng hiện tại).
- **B.** Nằm tại 'public/Controller.php', chỉ dùng để tạo bảng CSDL mới.
- **C.** Nằm tại 'views/Controller.php', chỉ dùng để nhúng các đoạn mã JavaScript.
- **D.** Nằm tại 'storage/Controller.php', dùng để nén tệp tải lên thành file zip.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Base Controller 'app/Core/Controller.php' là lớp trừu tượng nền tảng đóng gói các tiện ích dùng chung chuẩn MVC giúp code gọn gàng, tránh trùng lặp.

#### Câu 5. Các tệp tin Middleware như 'AuthMiddleware.php', 'AdminMiddleware.php', 'AgentMiddleware.php' nằm tại thư mục nào và có chức năng gì?

- **A.** Nằm tại thư mục 'views/middlewares/' để tạo hiệu ứng chuyển động CSS cho các nút bấm.
- **B.** Nằm tại thư mục 'app/Middlewares/', đóng vai trò là tầng lọc trung gian kiểm soát truy cập (RBAC): xác thực trạng thái đăng nhập và kiểm tra nghiêm ngặt quyền hạn người dùng (Role) trước khi cho phép Controller xử lý yêu cầu.
- **C.** Nằm tại thư mục 'config/' để kiểm tra phiên bản PHP máy chủ.
- **D.** Nằm tại thư mục 'storage/' để dọn dẹp các tệp rác quá hạn 30 ngày.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tầng Middleware tại 'app/Middlewares/' thực thi kiểm soát phân quyền (RBAC) độc lập, bảo vệ các route nhạy cảm trước khi request tới được Controller.

#### Câu 6. Tệp mã nguồn 'app/Controllers/GuestTicketController.php' nằm ở vị trí nào và giải quyết bài toán nghiệp vụ gì của hệ thống?

- **A.** Nằm tại 'app/Models/' để tính toán số tiền phạt khi sinh viên trả sách thư viện muộn.
- **B.** Nằm tại 'includes/' để hiển thị danh sách các bài viết tin tức của trường.
- **C.** Nằm tại 'app/Controllers/GuestTicketController.php', điều khiển Trạm cứu hộ tài khoản khách: tiếp nhận ảnh chụp CCCD/thẻ SV từ Webcam hoặc tệp tải lên, sinh mã cứu hộ duy nhất '#HD-...', phối hợp KTV xác minh và hỗ trợ tra cứu tiến độ không cần đăng nhập.
- **D.** Nằm tại 'views/guest/' để in thẻ sinh viên bằng máy in nhiệt.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'GuestTicketController.php' xử lý toàn bộ luồng cứu hộ tài khoản cho người dùng bị khóa/quên mật khẩu, tích hợp module chụp ảnh Webcam trực tiếp.

#### Câu 7. Tệp 'app/Controllers/Admin/AdminDashboardController.php' nằm tại vị trí nào và chịu trách nhiệm quản lý những khối nghiệp vụ nào?

- **A.** Nằm tại 'app/Controllers/Admin/AdminDashboardController.php', điều hành Bảng điều khiển Quản trị: giám sát tải thời gian thực của 5 KTV (ngưỡng 15 phiếu), bật/tắt công tắc trực ca ('is_on_duty'), phát/thu hồi cảnh báo sự cố diện rộng và đếm số phiếu cứu hộ chờ duyệt.
- **B.** Nằm tại 'config/AdminDashboardController.php' để đổi mật khẩu tài khoản root của máy chủ MySQL.
- **C.** Nằm tại 'storage/AdminDashboardController.php' để xuất hóa đơn điện tử cho nhà trường.
- **D.** Nằm tại 'views/admin/' để lưu ảnh đại diện của Ban Giám hiệu.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'AdminDashboardController.php' điều phối trung tâm chỉ huy của Admin, theo dõi tải 5 KTV theo thời gian thực và quản lý cảnh báo khẩn cấp toàn trường.

#### Câu 8. Tệp 'app/Controllers/Agent/AgentDashboardController.php' nằm ở đâu và đảm nhận chức năng nghiệp vụ gì trên Bàn làm việc Kỹ thuật viên ('/agent/workbench')?

- **A.** Nằm tại 'app/Controllers/Agent/AgentDashboardController.php', quản lý hàng đợi phiếu theo ca trực, hỗ trợ trao đổi phản hồi kỹ thuật, mở modal đóng phiếu trùng lặp ('parent_ticket_id') và gửi biên bản đề xuất Giảng viên mở lại hạn nộp bài.
- **B.** Nằm tại 'app/Core/' để xóa toàn bộ dữ liệu phiếu của sinh viên vi phạm.
- **C.** Nằm tại 'views/agent/' để cài đặt hệ điều hành Windows cho phòng máy.
- **D.** Nằm tại 'public/' để cho phép khách bên ngoài xem thông tin nội bộ của các KTV.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'AgentDashboardController.php' là bộ điều khiển phân hệ kỹ thuật viên, quản lý hàng đợi xử lý phiếu, đóng trùng lặp và xác nhận lỗi học thuật.

#### Câu 9. Tệp 'app/Services/TicketService.php' nằm tại vị trí nào và chứa đựng những thuật toán cốt lõi nào của dự án?

- **A.** Nằm tại 'views/' để hiển thị giao diện các biểu mẫu tạo phiếu.
- **B.** Nằm tại 'app/Services/TicketService.php', chứa thuật toán điều phối thông minh Triage (Least-Loaded kết hợp Tie-breaker phân ca), kiểm tra 3 lớp chống gửi trùng lặp trong 24h và tự động phát hiện nguy cơ sự cố diện rộng (Major Incident).
- **C.** Nằm tại 'config/' để kết nối API mạng xã hội Facebook.
- **D.** Nằm tại 'includes/' để phát âm thanh chuông báo khi có phiếu mới.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'TicketService.php' là trái tim nghiệp vụ của hệ thống Helpdesk, chứa thuật toán phân bổ tải lao động tối ưu và kiểm soát chống spam.

#### Câu 10. Tệp 'app/Services/SlaService.php' nằm tại vị trí nào và đảm nhận chức năng gì trong hệ thống Helpdesk?

- **A.** Nằm tại 'storage/' để sao lưu nhật ký hệ thống ra ổ cứng ngoài.
- **B.** Nằm tại 'public/' để hiển thị đồng hồ xem giờ cho sinh viên.
- **C.** Nằm tại 'app/Services/SlaService.php', là động cơ tính toán thời hạn SLA theo giờ hành chính (8h00-11h30, 13h30-17h30), tự động loại trừ giờ nghỉ trưa, ngoài giờ (sau 17h30), Thứ Bảy, Chủ Nhật và các ngày nghỉ lễ cấu hình trong CSDL.
- **D.** Nằm tại 'app/Core/' để khởi động lại máy chủ khi bị quá tải.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'SlaService.php' thực hiện nghiệp vụ tính toán cam kết chất lượng dịch vụ SLA, tuân thủ đúng khung giờ làm việc hành chính học đường.

#### Câu 11. Tệp 'app/Services/UploadService.php' nằm tại vị trí nào và đảm bảo an toàn cho tệp đính kèm và ảnh chụp Webcam như thế nào?

- **A.** Nằm tại 'config/' để nén video bài giảng của giảng viên.
- **B.** Nằm tại 'views/' để hiển thị thanh tiến trình phần trăm tải file.
- **C.** Nằm tại 'storage/' để xóa toàn bộ các tệp ảnh sau khi xem xong.
- **D.** Nằm tại 'app/Services/UploadService.php', kiểm duyệt tệp theo whitelist MIME và phần mở rộng, giới hạn 5MB, khử trùng tên tệp bằng chuỗi băm ngẫu nhiên và giải mã ảnh chụp Webcam Base64 lưu an toàn vào 'storage/uploads/'.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** 'UploadService.php' ngăn chặn mã độc thực thi (RCE) qua tệp tải lên và xử lý an toàn dữ liệu ảnh truyền từ Webcam HTML5.

#### Câu 12. Tệp 'app/Repositories/TicketRepository.php' (hoặc 'TicketRepository.php') nằm tại đâu và phân định vai trò kiến trúc như thế nào so với Model 'app/Models/Ticket.php'?

- **A.** Cả hai file đều là view dùng để tạo bảng hiển thị danh sách phiếu trên trình duyệt.
- **B.** 'TicketRepository' dùng để lưu file tạm, còn 'Ticket.php' dùng để lưu mật khẩu người dùng.
- **C.** 'Ticket.php' thực hiện toàn bộ câu lệnh SQL phức tạp, còn 'TicketRepository' không chứa code PHP nào.
- **D.** 'TicketRepository.php' đóng gói toàn bộ các truy vấn SQL phức tạp (JOIN nhiều bảng, đếm số liệu Dashboard, lọc theo vai trò) bằng PDO Prepared Statements, trong khi Model 'Ticket.php' đóng vai trò là thực thể dữ liệu (Data Entity).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phân tách rõ ràng giữa Model (thực thể dữ liệu) và Repository (truy xuất CSDL nâng cao) theo đúng nguyên lý SOLID và Clean Architecture.

## 2. THIẾT KẾ CSDL CHUYÊN SÂU, KHÓA NGOẠI & TỐI ƯU INDEX

#### Câu 13. Chỉ mục phức hợp (Composite Index) `idx_agent_dispatch (role, is_active, is_on_duty, specialty_category_id, active_tickets_count)` trong bảng `users` được thiết kế nhằm tối ưu hóa câu truy vấn nào?

- **A.** Truy vấn hiển thị danh sách lớp sinh hoạt của sinh viên.
- **B.** Truy vấn lịch sử đăng nhập sai mật khẩu.
- **C.** Truy vấn xuất bảng điểm học phần của Giảng viên.
- **D.** Tối ưu hóa câu truy vấn quét và gán KTV thông minh của thuật toán Triage trong `TicketService::dispatchAgent` mà không phải quét toàn bộ bảng (Table Scan).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Index phức hợp `idx_agent_dispatch` tập hợp đúng 5 thuộc tính mà câu lệnh SQL tìm KTV chuyên trách sử dụng trong mệnh đề `WHERE` và `ORDER BY`, giúp MySQL tìm được KTV trực ca tối ưu chỉ trong O(log N) thời gian.

#### Câu 14. Trong bảng `tickets`, khóa ngoại `parent_ticket_id` liên kết đệ quy về `tickets(id)` được cấu hình quy tắc hành vi `ON DELETE` như thế nào khi bản ghi sự cố cha bị xóa?

- **A.** `ON DELETE SET NULL` (đưa trường `parent_ticket_id` của phiếu con về NULL để bảo toàn dữ liệu lịch sử phiếu con).
- **B.** `ON DELETE RESTRICT` (báo lỗi chặn không cho xóa phiếu cha).
- **C.** `ON DELETE CASCADE` (xóa toàn bộ phiếu con theo).
- **D.** `ON DELETE NO ACTION`.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ràng buộc `FOREIGN KEY (parent_ticket_id) REFERENCES tickets(id) ON DELETE SET NULL` đảm bảo rằng nếu bản ghi phiếu cha bị xóa hoặc dọn dẹp, các phiếu sự cố của sinh viên bên dưới vẫn tồn tại độc lập mà không bị mất dữ liệu.

#### Câu 15. Bảng `categories` có cột `default_priority_code` với các giá trị như `MED`, `HIGH`. Cột này có ý nghĩa gì khi người dùng chọn danh mục trên form tạo vé?

- **A.** Dùng để phân biệt màu sắc của logo trường học.
- **B.** Cung cấp mức độ ưu tiên mặc định cho danh mục đó, giúp hệ thống tự động gán `priority_id` khi người dùng không chủ động chọn mức ưu tiên.
- **C.** Quy định số lượng sinh viên tối đa trong một lớp học.
- **D.** Chỉ dùng để trang trí giao diện quản trị.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `default_priority_code` xác định mức độ quan trọng tự nhiên của nhóm sự cố (ví dụ hệ thống QLĐT hay Canvas LMS mặc định là HIGH), giúp đơn giản hóa biểu mẫu tạo vé của sinh viên.

#### Câu 16. Bảng `ticket_ratings` liên kết với bảng `tickets` qua khóa ngoại `ticket_id` với ràng buộc duy nhất `uq_ticket_rating (ticket_id)`. Ràng buộc này đảm bảo nguyên tắc nghiệp vụ nào?

- **A.** Mỗi KTV chỉ được nhận tối đa 1 phiếu mỗi ngày.
- **B.** Mỗi sinh viên chỉ được tạo 1 phiếu trong suốt khóa học.
- **C.** Mỗi phiếu sự cố chỉ được người dùng thực hiện đánh giá chất lượng CSAT duy nhất 1 lần, chống việc gửi đánh giá lặp lại làm méo mó KPI.
- **D.** Mỗi buổi học chỉ được nộp bài 1 lần duy nhất.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ràng buộc `UNIQUE KEY uq_ticket_rating (ticket_id)` ở tầng CSDL ngăn chặn triệt để tình trạng người dùng bấm gửi nhiều lần hoặc dùng công cụ tự động gửi nhiều lượt vote cho cùng một phiếu.

#### Câu 17. Kiểu dữ liệu của cột `sla_factor` trong bảng `priorities` được thiết kế là `DECIMAL(3,2)`. Ưu điểm vượt trội của `DECIMAL` so với `FLOAT` hoặc `DOUBLE` trong trường hợp này là gì?

- **A.** `DECIMAL` tốn ít dung lượng ổ đĩa hơn kiểu số nguyên.
- **B.** `DECIMAL` lưu trữ số thập phân có độ chính xác tuyệt đối (Fixed-Point Arithmetic), triệt tiêu hoàn toàn sai số làm tròn số thực trong các phép tính toán thời gian cam kết SLA.
- **C.** `DECIMAL` tự động mã hóa chuỗi thành Bcrypt.
- **D.** `DECIMAL` chỉ dùng được trên hệ điều hành Windows.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểu `DECIMAL(3,2)` (ví dụ 0.50, 0.80, 1.50) tránh được các lỗi sai số dấu phẩy động (Floating-Point Precision Issues) vốn có của `FLOAT`/`DOUBLE`, đảm bảo tính toán thời gian SLA chuẩn mực đến từng phút.

#### Câu 18. Bảng `holidays` lưu trữ các ngày nghỉ lễ trường học. Tại sao bảng này không cần nối khóa ngoại (Foreign Key) trực tiếp vào bảng `tickets`?

- **A.** Để tiết kiệm RAM của máy tính người dùng.
- **B.** Do MySQL không cho phép tạo quá 5 khóa ngoại trong một cơ sở dữ liệu.
- **C.** Vì bảng `holidays` được lưu trữ trên một máy chủ đám mây khác.
- **D.** Vì lịch nghỉ lễ áp dụng chung cho toàn trường theo khoảng thời gian thực tế; động cơ `SlaService` truy vấn động theo điều kiện `BETWEEN start_date AND end_date`.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ngày nghỉ lễ là dữ liệu dùng chung theo lịch dương lịch, áp dụng cho mọi phiếu tạo trong hoặc giáp kỳ nghỉ lễ; do đó động cơ SLA dùng phép so sánh khoảng ngày (`WHERE :date BETWEEN start_date AND end_date`) mà không cần FK.

#### Câu 19. Trường `closed_reason` trong bảng `tickets` lưu trữ lý do kết thúc vòng đời của phiếu. Giá trị `'duplicate'` được lưu vào trường này khi nào?

- **A.** Khi sinh viên đạt điểm 10 môn Lập trình Web.
- **B.** Khi KTV hoặc Admin sử dụng tính năng 'Đóng do Trùng lặp sự cố' để gom phiếu con vào phiếu sự cố gốc (Parent Ticket).
- **C.** Khi hệ thống bị mất điện đột ngột.
- **D.** Khi người dùng đổi mật khẩu thành công.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khi xử lý trùng lặp (`action=close_duplicate`), hệ thống cập nhật `status = 'closed'`, `closed_at = NOW()` và ghi nhận `closed_reason = 'duplicate'` để thống kê tỷ lệ phiếu trùng trong báo cáo tổng kết.

#### Câu 20. Trong bảng liên kết nhiều-nhiều `student_classes`, cặp thuộc tính `(student_id, class_id)` được gán ràng buộc nào để đảm bảo tính toàn vẹn dữ liệu?

- **A.** Khóa ngoại tự tham chiếu.
- **B.** Chỉ mục FULLTEXT Index.
- **C.** Thuộc tính tự tăng AUTO_INCREMENT.
- **D.** Khóa chính phức hợp `PRIMARY KEY (student_id, class_id)` chống việc ghi nhận trùng lặp một sinh viên hai lần vào cùng một lớp học.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cặp `PRIMARY KEY (student_id, class_id)` trong bảng trung gian `student_classes` là chuẩn thiết kế 3NF cho quan hệ N-N, đảm bảo một sinh viên không thể có 2 bản ghi trong cùng một lớp.

#### Câu 21. Cột `is_guest_ticket` (kiểu `TINYINT(1)`) trong bảng `tickets` phục vụ mục đích phân loại kỹ thuật nào?

- **A.** Đánh dấu phiếu do robot tự động gửi.
- **B.** Phân biệt rõ ràng giữa phiếu hỗ trợ nội bộ thông thường của người đã đăng nhập với đơn cứu hộ khẩn cấp của khách chưa đăng nhập ngoài cổng.
- **C.** Dùng để kích hoạt chế độ tối (Dark Mode) cho trang web.
- **D.** Đánh dấu phiếu cần gửi tin nhắn SMS tới phụ huynh.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `is_guest_ticket = 1` đánh dấu các đơn xuất phát từ Trạm cứu hộ tài khoản khách (`GuestTicketController`), giúp hệ thống hiển thị quy trình xác thực 2 bước chuyên biệt và áp dụng mẫu tra cứu bằng mã đơn.

#### Câu 22. Khi truy vấn danh sách phiếu tại `tickets/index.php`, tại sao câu lệnh SQL bắt buộc phải sử dụng `LEFT JOIN users u ON t.assigned_to = u.id` thay vì `INNER JOIN`?

- **A.** Để không làm mất các phiếu mới tiếp nhận ở trạng thái `new` chưa được gán KTV nào tiếp nhận (`t.assigned_to IS NULL`).
- **B.** Để bảo vệ mật khẩu của Admin không bị hiển thị.
- **C.** Vì MySQL cấm dùng INNER JOIN với bảng users.
- **D.** Vì INNER JOIN chạy chậm hơn LEFT JOIN 10 lần.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nếu dùng `INNER JOIN`, mọi phiếu mới tạo đang ở trạng thái `new` hoặc chưa phân công KTV (`assigned_to IS NULL`) sẽ bị loại bỏ hoàn toàn khỏi kết quả truy vấn, khiến hàng đợi tiếp nhận bị trống.

#### Câu 23. Cột `is_recurring` trong bảng `holidays` mang giá trị 1 biểu thị điều gì đối với cơ chế kiểm tra lịch nghỉ lễ của `SlaService`?

- **A.** Ngày lễ lặp lại cố định hàng năm theo ngày-tháng (ví dụ 01/01, 30/04, 02/09); động cơ SLA chỉ cần so sánh chuỗi `m-d` với ngày hiện tại mà không phụ thuộc năm.
- **B.** Ngày lễ đó chỉ diễn ra một lần duy nhất rồi tự động xóa khỏi CSDL.
- **C.** Ngày lễ bắt buộc tất cả sinh viên phải đến trường lao động công ích.
- **D.** Ngày lễ tính gấp 3 lần thời gian SLA.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Thuộc tính `is_recurring = 1` đại diện cho các ngày lễ cố định theo dương lịch hàng năm, hàm `SlaService::isHoliday()` trích xuất tháng-ngày (`format('m-d')`) để đối soát tự động cho mọi năm học tiếp theo.

#### Câu 24. Bảng `password_resets` có cột `token_hash VARCHAR(64)`. Tại sao hệ thống lại lưu trữ chuỗi băm 64 ký tự thay vì lưu mã OTP gốc do người dùng nhận được?

- **A.** Để làm cho mã token có hiệu lực vĩnh viễn.
- **B.** Do giao thức HTTP yêu cầu bắt buộc phải lưu 64 ký tự.
- **C.** Sử dụng thuật toán băm SHA-256 (`hash('sha256', $otp)`) để bảo vệ mã OTP, chống rò rỉ mã khôi phục ngay cả khi cơ sở dữ liệu bị lộ lọt (Data Breach).
- **D.** Vì CSDL không hỗ trợ lưu chữ hoa.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Theo chuẩn an toàn OWASP, mã token khôi phục mật khẩu không bao giờ được lưu dạng văn bản thô (Plaintext). Hệ thống chỉ lưu chuỗi băm SHA-256 (`hash('sha256', $token)`), khi người dùng nhập mã sẽ băm lại để so khớp.

## 3. MA TRẬN PHÂN QUYỀN RBAC, MIDDLEWARE & KIỂM SOÁT TRUY CẬP

#### Câu 25. Theo ma trận phân quyền hệ thống Helpdesk, vai trò nào có thẩm quyền độc quyền sử dụng tính năng 'Thu hồi Cảnh báo Khẩn cấp / Sự cố diện rộng' (Revoke Major Incident)?

- **A.** Sinh viên gửi phiếu.
- **B.** Kỹ thuật viên phụ trách hạ tầng.
- **C.** Giảng viên bộ môn.
- **D.** Độc quyền Quản trị viên (Admin) sau khi sự cố kỹ thuật diện rộng đã được khắc phục hoàn toàn.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ có Quản trị viên (Admin) mới có thẩm quyền cao nhất để công bố hoặc thu hồi cảnh báo khẩn cấp diện rộng, đưa cờ `is_major_incident` về 0 và gỡ banner cảnh báo đỏ toàn trường.

#### Câu 26. Khi một Sinh viên gửi request chỉnh sửa nội dung phiếu hỗ trợ tại `tickets/edit.php`, chuỗi điều kiện kiểm tra nghiệp vụ nào bắt buộc phải thỏa mãn đồng thời?

- **A.** Chỉ cần sinh viên đó biết số điện thoại của KTV phụ trách.
- **B.** Phiếu phải có đánh giá CSAT 5 sao.
- **C.** Phải là người tạo phiếu (`requester_id == current_user_id`), trong vòng 15 phút đầu sau khi tạo (`TIMESTAMPDIFF(MINUTE, created_at, NOW()) <= 15`) và phiếu vẫn ở trạng thái `new` hoặc `waiting_requester`.
- **D.** Chỉ được sửa vào ban đêm sau 22:00.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy tắc 15 phút (Golden 15-Minute Window) cho phép người tạo tự sửa sai chính tả hoặc bổ sung ảnh minh chứng chỉ trong 15 phút đầu kể từ lúc tạo, khi KTV chưa tiếp nhận xử lý.

#### Câu 27. Giảng viên khi đăng nhập vào Cổng Học Phần Giảng Viên (`Lecturer Portal`) có thể xem và chấm điểm danh sách bài nộp của những sinh viên nào?

- **A.** Toàn bộ sinh viên trong trường bất kể khoa nào.
- **B.** Chỉ xem được bài nộp của sinh viên cùng quê quán.
- **C.** Chỉ xem được bài nộp của sinh viên có điểm tổng kết trên 8.0.
- **D.** Chỉ xem và chấm điểm được bài nộp của sinh viên thuộc các Lớp học phần do chính Giảng viên đó phụ trách giảng dạy theo phân công đào tạo.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc cô lập dữ liệu học thuật (Academic Privacy Isolation) ràng buộc Giảng viên chỉ được truy xuất dữ liệu lớp học thông qua điều kiện `WHERE c.lecturer_id = :current_user_id`.

#### Câu 28. Trong cơ chế đăng nhập lần đầu, điều kiện nào kích hoạt Middleware chuyển hướng người dùng bắt buộc sang trang `index.php?action=first_login`?

- **A.** Khi người dùng đăng nhập vào ngày Chủ Nhật.
- **B.** Khi người dùng đăng nhập thành công và cờ `users.is_first_login == 1` (tài khoản mới cấp hoặc vừa được Admin reset về `123456`).
- **C.** Khi tài khoản người dùng đã sử dụng quá 3 năm.
- **D.** Khi người dùng đăng nhập từ trình duyệt Google Chrome.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cờ `is_first_login = 1` đánh dấu tài khoản đang sử dụng mật khẩu khởi tạo mặc định `123456`, Middleware sẽ chặn mọi đường dẫn điều hướng khác và ép buộc người dùng phải đổi mật khẩu cá nhân mới.

#### Câu 29. Kỹ thuật viên CNTT (Agent) có được phép tự ý thêm, sửa hoặc xóa các bản ghi ngày lễ trường học trong bảng `holidays` không?

- **A.** Tuyệt đối không; quyền cấu hình lịch nghỉ lễ trường học là độc quyền của Quản trị viên (Admin) tại phân hệ `Admin\HolidayController`.
- **B.** Được phép nếu KTV đó có thâm niên làm việc trên 2 năm.
- **C.** Được phép nếu có sự đồng ý của Giảng viên bộ môn.
- **D.** Được phép vào các ngày cuối tuần.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lịch nghỉ lễ toàn trường ảnh hưởng trực tiếp đến động cơ tính hạn SLA và lịch học của hàng nghìn sinh viên, do đó quyền hạn này được khóa chặt độc quyền cho vai trò Admin.

#### Câu 30. Khi KTV phát hiện sinh viên cố tình gửi phiếu spam, dùng lời lẽ xúc phạm hoặc gian lận cứu hộ, KTV bấm nút 'Báo cáo Vi phạm' thì hệ thống xử lý như thế nào?

- **A.** Trừ 50% tiền học phí của sinh viên.
- **B.** Gửi tin nhắn cảnh cáo tới nhóm Zalo phụ huynh.
- **C.** Cập nhật `is_spam = 1`, `status = 'resolved'`, ghi nhận biên bản vi phạm và đẩy hồ sơ vào 'Trung Tâm Việc Cần Làm Cấp Bách' của Admin để xem xét kỷ luật.
- **D.** Tự động xóa vĩnh viễn sinh viên khỏi CSDL.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tính năng Báo cáo Vi phạm giúp KTV lập biên bản sự việc, kết thúc xử lý kỹ thuật của KTV và chuyển toàn bộ hồ sơ bằng chứng sang bảng điều khiển của Admin để Hội đồng kỷ luật xử lý.

#### Câu 31. Theo quy định chuẩn hóa về phân quyền quản lý nhân sự, Kỹ thuật viên (Agent) có được tự ý bấm chuyển đổi trạng thái ca trực (`is_on_duty`) của mình trên Bàn làm việc không?

- **A.** Được phép tự do bật tắt bất cứ lúc nào tùy thích.
- **B.** Được phép nếu đã hoàn thành đủ 5 phiếu trong ca.
- **C.** Được phép nếu nhờ sinh viên bấm hộ.
- **D.** Tuyệt đối không; KTV chỉ được theo dõi trạng thái ca trực (Read-only); thẩm quyền phân công và điều chuyển trạng thái ca trực `is_on_duty` thuộc độc quyền của Quản trị viên (Admin).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Để đảm bảo kỷ luật ca trực và phân bổ nguồn lực công bằng, KTV không được tự ý tắt ca để né tránh nhận vé; chỉ duy nhất Admin mới có quyền điều chỉnh `is_on_duty` cho KTV trong hệ thống.

#### Câu 32. Nếu một người dùng có tài khoản bị khóa vĩnh viễn với lý do `banned_rescue_abuse` cố tình gửi thông tin tại Trạm cứu hộ tài khoản khách, hệ thống sẽ phản ứng như thế nào?

- **A.** Vẫn tiếp nhận và gửi mã đơn cứu hộ bình thường.
- **B.** Hệ thống lập tức từ chối tiếp nhận, hiển thị thông báo lỗi cấm truy cập do tài khoản đã bị khóa vĩnh viễn vì hành vi nghịch phá trạm cứu hộ.
- **C.** Tự động mở khóa tài khoản cho người dùng sau 5 phút.
- **D.** Gửi email thông báo trúng thưởng điện thoại.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tại `GuestTicketController::submit()`, hệ thống kiểm tra `if ($user['lock_reason'] === 'banned_rescue_abuse' || $user['lock_reason'] === 'spam')` và lập tức từ chối tiếp nhận đơn từ các tài khoản vi phạm nặng.

#### Câu 33. Trong tính năng trao đổi trên phiếu hỗ trợ, khi KTV gửi một phản hồi với tùy chọn 'Ghi chú nội bộ' (`is_internal = 1`), những đối tượng nào có quyền nhìn thấy nội dung này?

- **A.** Bất kỳ ai có đường link đều xem được.
- **B.** Chỉ các Kỹ thuật viên (Agent) và Quản trị viên (Admin); người tạo phiếu (Sinh viên/Giảng viên) hoàn toàn không nhìn thấy.
- **C.** Toàn bộ sinh viên trong trường.
- **D.** Chỉ có Giảng viên bộ môn nhìn thấy.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Ghi chú nội bộ (`is_internal = 1`) là kênh trao đổi nghiệp vụ riêng giữa đội ngũ hỗ trợ kỹ thuật và ban quản trị, câu truy vấn hiển thị tin nhắn phía sinh viên luôn có điều kiện `AND is_internal = 0`.

#### Câu 34. Trong quy trình cứu hộ tài khoản 2 bước bảo mật, thao tác 'Xác thực đơn hợp lệ Bước 1' (`rescue_verified = 1`) thuộc trách nhiệm của vai trò nào?

- **A.** Bảo vệ cổng trường.
- **B.** Kỹ thuật viên trực ca được phân công tiếp nhận đơn cứu hộ sau khi đối soát ảnh thẻ và thông tin cá nhân.
- **C.** Sinh viên tự xác thực cho chính mình.
- **D.** Hệ thống ngân hàng liên kết.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** KTV chuyên trách trực ca kiểm tra đối soát thẻ sinh viên/CCCD với thông tin trên CSDL; nếu hợp lệ, KTV bấm xác thực ghi nhận cờ `rescue_verified = 1` và đẩy lên cho Admin phê duyệt mở khóa.

#### Câu 35. Vai trò nào trong hệ thống có thẩm quyền độc quyền thực hiện chức năng 'Bulk Import Người Dùng' từ tệp bảng tính Excel / CSV?

- **A.** Kỹ thuật viên chuyên trách mạng.
- **B.** Sinh viên lớp trưởng.
- **C.** Giảng viên trợ giảng.
- **D.** Độc quyền Quản trị viên (Admin) tại phân hệ Quản lý Người dùng.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chức năng nạp dữ liệu hàng loạt (Bulk Import) cho phép khởi tạo hàng trăm tài khoản và mã hóa mật khẩu Bcrypt cùng lúc, do đó thẩm quyền này được bảo vệ độc quyền cho tài khoản Admin.

#### Câu 36. Khi Giảng viên đại học gửi một phiếu yêu cầu hỗ trợ sự cố phòng máy hoặc phần mềm giảng dạy, hệ thống áp dụng cơ chế ưu tiên nghiệp vụ nào?

- **A.** Tự động chuyển phiếu sang cho lớp trưởng giải quyết.
- **B.** Tự động nhận diện vai trò Giảng viên và ưu tiên cam kết thời gian xử lý nhanh trong vòng 2 giờ làm việc để không làm gián đoạn giờ giảng dạy trên lớp.
- **C.** Xếp vào hàng đợi bình thường như sinh viên, giải quyết trong 24 giờ.
- **D.** Từ chối tiếp nhận vì giảng viên phải tự sửa phòng máy.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Trong `SlaService.php`, logic nghiệp vụ quy định: nếu người gửi là giảng viên (`userType === 'lecturer'`) với mức ưu tiên Khẩn cấp/Cao, thời gian cam kết SLA tự động đưa về chuẩn 2 giờ (120 phút).

## 4. THUẬT TOÁN ĐIỀU PHỐI TRIAGE & QUẢN LÝ CA TRỰC KTV

#### Câu 37. Thuật toán điều phối thông minh trong `TicketService::dispatchAgent($categoryId)` ưu tiên gán phiếu hỗ trợ mới cho đối tượng KTV nào đầu tiên?

- **A.** KTV có chuyên môn trùng khớp (`specialty_category_id = category_id`), đang trong ca trực do Admin phân công (`is_on_duty = 1`) và chưa vượt ngưỡng kịch tải (`current_active_count < max_capacity`).
- **B.** Gán thẳng cho Admin hệ thống.
- **C.** Gán ngẫu nhiên cho bất kỳ nhân viên nào trong trường.
- **D.** Gán cho KTV có số điện thoại đẹp nhất.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bước 1 của thuật toán Triage luôn tìm KTV chuyên trách nhóm dịch vụ (`specialty_category_id = category_id`) đang bật trực ca (`is_on_duty = 1`, `is_active = 1`) và còn slot xử lý để đảm bảo chất lượng chuyên môn cao nhất.

#### Câu 38. Hạn mức tải tối đa (Max Capacity) mặc định được thiết lập cho mỗi Kỹ thuật viên trong hệ thống là bao nhiêu phiếu đang xử lý đồng thời?

- **A.** 15 phiếu đang hoạt động ở các trạng thái `new`, `in_progress`, `waiting_requester`.
- **B.** 50 phiếu.
- **C.** 5 phiếu.
- **D.** Không giới hạn số lượng.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống cấu hình ngưỡng an toàn `max_capacity = 15` phiếu/KTV. Khi số phiếu active đạt mốc 15, KTV được coi là kịch tải và hệ thống tự động kích hoạt cơ chế tràn tải sang người khác.

#### Câu 39. Khi KTV chuyên trách đang nghỉ ca (`is_on_duty = 0`) hoặc đã đạt ngưỡng kịch tải (15 phiếu), thuật toán Triage sẽ kích hoạt cơ chế nào?

- **A.** Lập tức hủy bỏ phiếu sự cố của sinh viên.
- **B.** Cơ chế Tràn tải / Điều phối dự phòng (Overflow Dispatch): tự động tìm KTV khác đang trực (`is_on_duty = 1`) có số phiếu đang xử lý ít nhất để phân công tạm thời.
- **C.** Bắt sinh viên phải chờ đợi 7 ngày.
- **D.** Tự động tắt máy chủ CSDL.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế Overflow Dispatch quét danh sách KTV đang trực (`is_on_duty = 1`) và sắp xếp theo `current_active_count ASC` để gán cho KTV có tải nhẹ nhất, ngăn ngừa nghẽn hàng đợi.

#### Câu 40. Khi sinh viên tạo đơn cứu hộ tại Trạm cứu hộ tài khoản khách mà KTV Nguyễn Văn An (Chuyên trách SSO) đang nghỉ ca (`is_on_duty = 0`), hệ thống tiếp nhận và phân công đơn như thế nào?

- **A.** Vẫn cố tình gán cho KTV An bất kể đang nghỉ.
- **B.** Báo lỗi hệ thống đóng cửa và yêu cầu sinh viên quay lại vào tuần sau.
- **C.** Thuật toán Triage tự động nhận diện KTV An đang nghỉ ca và điều phối dự phòng sang KTV khác đang trực ca (ví dụ KTV Trần Thị Bích), ghi rõ lý do điều phối trên thông báo.
- **D.** Xóa tài khoản của sinh viên.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Tại `GuestTicketController` và `index.php`, hệ thống gọi `TicketService::dispatchAgent(1)`. Khi KTV chuyên trách có `is_on_duty = 0`, đơn cứu hộ tự động chuyển sang KTV trực ca thay thế có tải thấp nhất.

#### Câu 41. Trong tình huống giả định khẩn cấp khi toàn bộ 5 KTV đều đang nghỉ ca (`is_on_duty = 0`), thuật toán Triage xử lý phiếu sự cố mới như thế nào?

- **A.** Phiếu được lưu an toàn vào hàng đợi ở trạng thái `new` gán cho KTV chuyên trách để sẵn sàng xử lý ngay khi KTV vào ca trực tiếp theo.
- **B.** Gửi tin nhắn phạt tiền sinh viên.
- **C.** Tự động đánh dấu phiếu là đã giải quyết xong.
- **D.** Xóa toàn bộ CSDL của nhà trường.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khi tất cả KTV đều nghỉ, hệ thống kích hoạt cơ chế Fallback Queue: phiếu được lưu an toàn vào hàng đợi của KTV chuyên trách danh mục đó để giao ban xử lý vào ca làm việc kế tiếp.

#### Câu 42. Khi KTV nhận phiếu nhưng phát hiện sự cố thuộc chuyên môn của bộ môn khác (ví dụ từ Mạng sang LMS), thao tác 'Chuyển giao phiếu' (Re-assignment) ghi nhận lịch sử vào bảng nào?

- **A.** Bảng `holidays`.
- **B.** Ghi nhận thông tin chuyển giao kèm lý do bàn giao vào bảng `ticket_replies` để minh bạch lịch sử xử lý và theo dõi trách nhiệm.
- **C.** Bảng `classes`.
- **D.** Bảng `courses`.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mọi thao tác chuyển giao chuyên môn (Re-assign) đều được hệ thống tự động chèn một bản ghi ghi chú vào bảng `ticket_replies` nêu rõ KTV chuyển giao, KTV tiếp nhận và lý do bàn giao.

#### Câu 43. Quy tắc tính điểm KPI hoàn thành nhiệm vụ của Kỹ thuật viên trong hệ thống Helpdesk được quy định như thế nào?

- **A.** KTV nào nhận chuyển giao phiếu nhiều nhất sẽ được điểm cao nhất.
- **B.** Điểm KPI hoàn thành phiếu chỉ được tính cho KTV xử lý cuối cùng &ndash; người trực tiếp đưa phiếu về trạng thái `resolved` và nhận nghiệm thu đánh giá CSAT từ người dùng.
- **C.** Chỉ tính KPI cho Admin.
- **D.** Chia đều điểm cho toàn bộ KTV trong trường.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy tắc KTV xử lý cuối cùng (Final Resolver Credit) ngăn ngừa tình trạng KTV nhận phiếu rồi chuyển tiếp lòng vòng để lấy thành tích; chỉ người thực sự giải quyết triệt để sự cố mới được ghi nhận KPI.

#### Câu 44. Khi một phiếu sự cố bị các KTV chuyển giao liên tiếp vượt quá 3 lần, hệ thống sẽ kích hoạt phản ứng nghiệp vụ nào?

- **A.** Xóa tài khoản của 3 KTV đó.
- **B.** Kích hoạt cảnh báo đỏ 'Đùn đẩy phiếu / Bất thường' trên Dashboard của Admin để lãnh đạo Trung tâm CNTT can thiệp chỉ định KTV chịu trách nhiệm dứt điểm.
- **C.** Gửi phiếu sang trường đại học khác.
- **D.** Tự động đóng phiếu và trừ điểm sinh viên.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ số chuyển giao quá 3 lần (Ticket Ping-pong Alert) là cơ chế kiểm soát chất lượng dịch vụ, giúp Admin phát hiện ngay các sự cố khó hoặc hiện tượng đùn đẩy trách nhiệm để chỉ đạo trực tiếp.

#### Câu 45. Cột `active_tickets_count` trong bảng `users` được cập nhật vào những thời điểm nào trong vòng đời xử lý phiếu?

- **A.** Chỉ cập nhật 1 lần duy nhất vào ngày đầu năm mới.
- **B.** Chỉ cập nhật khi KTV đăng xuất.
- **C.** Không bao giờ thay đổi.
- **D.** Tự động tăng khi có phiếu mới được gán cho KTV và tự động giảm khi phiếu được chuyển sang trạng thái kết thúc (`resolved` hoặc `closed`).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `active_tickets_count` phản ánh số phiếu đang gánh trên vai KTV ở thời gian thực (`status IN ('new', 'in_progress', 'waiting_requester')`), giúp thuật toán cân bằng tải đưa ra quyết định chuẩn xác.

#### Câu 46. Khi KTV mở Modal 'Đóng phiếu do Trùng lặp sự cố', danh sách các phiếu sự cố cha trong dropdown (`parent_ticket_id`) được lọc theo điều kiện bắt buộc nào?

- **A.** Chỉ hiển thị các phiếu ĐANG TRONG QUÁ TRÌNH XỬ LÝ (`status IN ('new', 'in_progress', 'waiting_requester')`), ưu tiên các Sự cố diện rộng (Major Incident) đang diễn ra.
- **B.** Hiển thị các phiếu có điểm CSAT 1 sao.
- **C.** Hiển thị toàn bộ các phiếu đã đóng từ năm trước.
- **D.** Chỉ hiển thị các phiếu do chính sinh viên đó tạo.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dropdown chọn phiếu cha bắt buộc phải lọc `status IN ('new', 'in_progress', 'waiting_requester')` vì theo chuẩn ITIL, chỉ sự cố đang diễn ra mới được làm phiếu gốc gom nhóm các phiếu con mới phát sinh.

#### Câu 47. Tại sao hệ thống cấm chọn một phiếu đã ở trạng thái `resolved` hoặc `closed` làm Phiếu Cha (Parent Ticket) khi gom nhóm sự cố trùng lặp?

- **A.** Để bắt buộc KTV phải tạo thêm tài khoản mới.
- **B.** Vì phiếu đã giải quyết xong trong quá khứ không thể đại diện cho sự cố kỹ thuật đang diễn ra ở hiện tại; gộp vào phiếu đã đóng sẽ làm sinh viên mới không được hỗ trợ.
- **C.** Do hệ điều hành máy chủ cấm chọn phiếu cũ.
- **D.** Vì CSDL MySQL không hỗ trợ số nguyên lớn.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Gom nhóm phiếu con vào một phiếu đã đóng là lỗi logic nghiêm trọng trong ITIL Incident Management: phiếu con sẽ bị 'bỏ rơi' mà không có ai tiếp tục xử lý lỗi kỹ thuật.

#### Câu 48. Khi KTV bấm nút 'Nghiệm Thu Xong' đưa phiếu về trạng thái `resolved`, KTV bắt buộc phải hoàn thành nội dung nào trong Modal nghiệm thu?

- **A.** Điền số thẻ ngân hàng của sinh viên.
- **B.** Điền mã số định danh CCCD của KTV.
- **C.** Ghi rõ nội dung biên bản giải pháp kỹ thuật cụ thể đã áp dụng để khắc phục triệt để sự cố cho người dùng.
- **D.** Tải lên video nhảy múa giải trí.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** KTV bắt buộc phải nhập `solution` (giải pháp kỹ thuật) trước khi hoàn tất phiếu, nội dung này được gửi cho sinh viên kiểm tra và lưu vào kho tri thức Knowledge Base để tái sử dụng.

#### Câu 49. Trong máy trạng thái (State Machine), khi KTV gửi một tin nhắn trao đổi công khai cho sinh viên, trạng thái phiếu sẽ tự động luân chuyển thành trạng thái nào?

- **A.** `waiting_requester` (Chờ người dùng kiểm tra và phản hồi xác nhận).
- **B.** `closed`.
- **C.** `new`.
- **D.** `resolved`.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế State Machine tự động: khi KTV gửi tin nhắn công khai yêu cầu sinh viên cung cấp thêm thông tin hoặc thử lại, phiếu chuyển sang `waiting_requester` để tạm dừng đồng hồ SLA của KTV.

## 5. ĐỘNG CƠ SLA GIỜ HÀNH CHÍNH, CƠ CHẾ PAUSE SLA & LỊCH NGHỈ LỄ

#### Câu 50. Trong `SlaService.php`, khung giờ làm việc buổi chiều của Trung tâm CNTT được định nghĩa chính xác từ mấy giờ đến mấy giờ?

- **A.** 12:00 đến 16:30.
- **B.** 13:00 đến 17:00.
- **C.** 14:00 đến 18:00.
- **D.** 13:30 đến 17:30 (4 tiếng làm việc = 240 phút hành chính).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cấu hình chuẩn trong `SlaService`: Buổi sáng `08:00 - 12:00` (240 phút), Buổi chiều `13:30 - 17:30` (240 phút), tổng một ngày làm việc hành chính là đúng 8 tiếng (480 phút).

#### Câu 51. Khoảng thời gian nghỉ trưa từ `12:00` đến `13:30` (90 phút) được thuật toán `SlaService::addBusinessMinutes()` xử lý như thế nào?

- **A.** Tự động hủy bỏ phiếu hỗ trợ.
- **B.** Vẫn tính giờ làm việc bình thường và phạt KTV nếu không trực trưa.
- **C.** Đóng băng hoàn toàn đồng hồ đếm SLA; số phút công việc còn lại sẽ được cộng tiếp tục bắt đầu từ mốc 13:30 chiều.
- **D.** Tự động cộng thêm 5 tiếng vào hạn SLA.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Động cơ SLA đóng băng trong giờ nghỉ trưa (12:00 - 13:30): nếu phiếu cần thêm thời gian khi chạm mốc 12:00, thuật toán sẽ tự động dời sang 13:30 để tiếp tục tính các phút làm việc còn lại.

#### Câu 52. Nếu người dùng tạo phiếu vào lúc `16:30` chiều Thứ Sáu với thời hạn cam kết là 2.0 giờ làm việc (120 phút), mốc `due_at` chính xác sẽ là thời điểm nào?

- **A.** 12:00 trưa Thứ Bảy.
- **B.** 18:30 tối Thứ Sáu.
- **C.** Thứ Hai tuần kế tiếp lúc 09:00 sáng (Thứ Sáu tính 1h từ 16:30 đến 17:30; đóng băng Thứ 7, CN; sáng Thứ Hai tính tiếp 1h từ 08:00 đến 09:00).
- **D.** 08:00 sáng Thứ Hai.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chiều Thứ Sáu chỉ còn 60 phút làm việc (16:30 - 17:30), 60 phút còn lại được bảo lưu qua 2 ngày nghỉ cuối tuần (Thứ 7 & CN) và được tính vào đầu giờ sáng Thứ Hai (08:00 + 60 phút = 09:00).

#### Câu 53. Khi phiếu hỗ trợ đang ở trạng thái `waiting_requester`, bộ đếm giờ cam kết SLA của KTV hoạt động như thế nào theo chuẩn quy trình ITIL v4?

- **A.** Tự động tạm dừng (Pause SLA clock), đóng băng thời gian cam kết và không tính vi phạm quá hạn cho KTV trong suốt thời gian chờ người dùng phản hồi.
- **B.** Đặt lại hạn SLA về 00:00.
- **C.** Tiếp tục chạy bình thường để ép KTV phải tự mò mẫm sửa lỗi.
- **D.** Trừ 5 điểm KPI của sinh viên mỗi giờ.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Theo chuẩn ITIL v4, khi KTV đang chờ phản hồi xác nhận từ người dùng (`waiting_requester`), SLA phải được Pause để đảm bảo tính công bằng và đánh giá đúng hiệu suất thực tế của đội ngũ kỹ thuật.

#### Câu 54. Trên giao diện danh sách phiếu (`tickets/index.php`) và hàng đợi KTV (`agent/index.php`), phiếu đang ở trạng thái `waiting_requester` sẽ hiển thị huy hiệu SLA nào thay vì còi báo động quá hạn đỏ?

- **A.** QUÁ HẠN KHẨN CẤP.
- **B.** Huy hiệu màu tím `⏸️ TẠM DỪNG SLA (Chờ phản hồi)`.
- **C.** KHÔNG CÓ HẠN.
- **D.** ĐÃ HỦY VÉ.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống hiển thị huy hiệu màu tím `⏸️ TẠM DỪNG SLA (Chờ phản hồi)` và loại trừ trạng thái này khỏi bộ đếm vi phạm quá hạn, giúp giao diện làm việc minh bạch và rõ ràng.

#### Câu 55. Công thức tính tổng thời gian cam kết thực tế (Effective SLA Minutes) trong `SlaService.php` được biểu diễn như thế nào?

- **A.** `effectiveMinutes = round(slaHours * slaFactor * 60)`.
- **B.** `effectiveMinutes = slaHours + priorityId`.
- **C.** `effectiveMinutes = slaHours / 60`.
- **D.** `effectiveMinutes = slaFactor * 100`.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Công thức chuẩn: Lấy số giờ chuẩn của danh mục (`slaHours`) nhân với hệ số điều chỉnh ưu tiên (`slaFactor`) rồi nhân với 60 phút để ra tổng số phút làm việc hành chính cam kết.

#### Câu 56. Đối với danh mục Tài khoản & Xác thực SSO (`sla_hours = 2.0`), nếu phiếu có mức ưu tiên Khẩn cấp (URGENT với `sla_factor = 0.50`), thời gian cam kết giải quyết thực tế là bao nhiêu?

- **A.** 4.0 giờ (240 phút).
- **B.** 30 phút.
- **C.** 2.0 giờ (120 phút).
- **D.** 1.0 giờ (60 phút làm việc hành chính).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Áp dụng công thức: `2.0 giờ * 0.50 * 60 phút = 60 phút (1.0 giờ)`. Mức Khẩn cấp rút ngắn một nửa thời gian cam kết để KTV ưu tiên xử lý tức thời.

#### Câu 57. Đối với danh mục Email Trường & M365 (`sla_hours = 4.0`), nếu phiếu có mức ưu tiên Thấp (LOW với `sla_factor = 1.50`), thời gian cam kết giải quyết thực tế là bao nhiêu?

- **A.** 4.0 giờ (240 phút).
- **B.** 8.0 giờ (480 phút).
- **C.** 6.0 giờ (360 phút làm việc hành chính).
- **D.** 1.5 giờ (90 phút).

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Áp dụng công thức: `4.0 giờ * 1.50 * 60 phút = 360 phút (6.0 giờ)`. Mức Thấp cho phép kéo dài thời gian thêm 50% đối với các yêu cầu không cấp bách.

#### Câu 58. Khi tính toán hạn SLA cho phiếu hỗ trợ được tạo vào ngày Thứ Bảy hoặc Chủ Nhật, thuật toán `SlaService::moveToNextWorkingTime()` xử lý mốc bắt đầu như thế nào?

- **A.** Từ chối tiếp nhận và xóa phiếu.
- **B.** Tự động dời mốc bắt đầu tính giờ làm việc sang đúng 08:00 sáng Thứ Hai của tuần làm việc kế tiếp.
- **C.** Vẫn tính giờ ngay trong ngày Thứ Bảy.
- **D.** Chuyển sang 12:00 trưa Chủ Nhật.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hai ngày cuối tuần hoàn toàn không phải ngày làm việc của KTV (`isWorkingDay() = false`), do đó điểm mốc tính giờ hành chính tự động dịch chuyển đến đầu giờ sáng Thứ Hai (08:00).

#### Câu 59. Giả sử ngày 02/09 rơi vào Thứ Ba và được Admin khai báo trong bảng `holidays`. Một phiếu gửi lúc `17:00` chiều Thứ Hai (01/09) với hạn SLA là 2.0 giờ sẽ đến hạn vào thời điểm nào?

- **A.** 08:00 sáng Thứ Ba 02/09.
- **B.** 12:00 trưa Thứ Ba 02/09.
- **C.** 19:00 tối Thứ Hai 01/09.
- **D.** Sáng Thứ Tư (03/09) lúc 09:00 sáng (vì chiều Thứ Hai tính 30 phút từ 17:00-17:30, toàn bộ ngày Thứ Ba 02/09 nghỉ lễ được loại trừ, sáng Thứ Tư tính tiếp 90 phút từ 08:00-09:30... chính xác là 09:30).

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chiều Thứ Hai chỉ còn 30 phút làm việc (17:00 - 17:30). Cả ngày Thứ Ba nghỉ lễ 02/09 được bỏ qua 100%. Sáng Thứ Tư tính tiếp 90 phút còn lại từ 08:00 đến 09:30 sáng.

#### Câu 60. Hạn chót tiếp nhận ban đầu (Triage Due At) cam kết tối đa cho đội ngũ KTV kể từ khi phiếu được tạo ra trên hệ thống là bao nhiêu phút làm việc hành chính?

- **A.** 15 phút hành chính.
- **B.** 60 phút.
- **C.** 120 phút.
- **D.** 5 phút.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chỉ số Triage SLA cam kết phản hồi/tiếp nhận ban đầu trong vòng 15 phút làm việc hành chính (`addBusinessMinutes($now, 15)`) để người dùng an tâm rằng yêu cầu đã có người tiếp nhận.

#### Câu 61. Mức độ ưu tiên Cao (HIGH) trong bảng `priorities` có hệ số nhân `sla_factor` được cấu hình là bao nhiêu để rút ngắn 20% thời gian cam kết giải quyết?

- **A.** `0.50`.
- **B.** `1.20`.
- **C.** `0.80` (rút ngắn 20% thời gian cam kết).
- **D.** `1.00`.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mức Cao (HIGH) có `sla_factor = 0.80`, giúp rút ngắn 20% thời gian giải quyết đối với các sự cố ảnh hưởng đến công việc quan trọng nhưng chưa phải cấp cứu khẩn cấp.

#### Câu 62. Khi một phiếu hỗ trợ đã trôi qua mốc `due_at` mà trạng thái vẫn chưa đưa về `resolved` hoặc `closed` (và không ở trạng thái `waiting_requester`), hệ thống thể hiện điều gì?

- **A.** Kích hoạt nhãn đỏ cảnh báo nhấp nháy `⏰ QUÁ HẠN SLA`, đếm số phút chậm trễ và tính vào tỷ lệ vi phạm SLA trên báo cáo KPI của KTV.
- **B.** Tự động xóa sạch dữ liệu của KTV.
- **C.** Tự động hoàn tiền học phí cho sinh viên.
- **D.** Khóa cổng đăng nhập của KTV.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Khi `due_at < NOW()` và phiếu chưa hoàn tất (ngoại trừ lúc pause), hệ thống hiển thị cảnh báo đỏ quá hạn trên cả Dashboard Admin, Bàn làm việc KTV và trừ điểm KPI tuân thủ SLA.

## 6. LUỒNG NGHIỆP VỤ TRẠM CỨU HỘ, QUÊN MẬT KHẨU SSPR & ĐỔI MẬT KHẨU

#### Câu 63. Tại sao Trạm cứu hộ tài khoản khách (`GuestTicketController`) chỉ cấp duy nhất Mã đơn cứu hộ mà tuyệt đối không bắt người dùng phải nhớ thêm mã PIN?

- **A.** Do mã PIN tốn nhiều tiền bản quyền phần mềm.
- **B.** Vì CSDL MySQL không lưu được số PIN.
- **C.** Tinh gọn tối đa trải nghiệm người dùng (UX): người dùng đã bị khóa tài khoản và quên mật khẩu thì không nên ép họ phải gánh thêm áp lực ghi nhớ mã PIN phức tạp dễ thất lạc.
- **D.** Để bảo vệ máy chủ không bị quá tải.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy trình tinh gọn chỉ dùng duy nhất Mã đơn cứu hộ chuẩn hóa (ví dụ `#HD-2026-3110-0G04V1`) làm khóa tra cứu kết hợp đối soát thông tin cá nhân, nâng cao tính khả dụng và giảm khiếu nại.

#### Câu 64. Trong quy trình cứu hộ tài khoản 2 bước bảo mật, thao tác nghiệp vụ của Kỹ thuật viên ở Bước 1 là gì?

- **A.** Chuyển tiền hỗ trợ vào tài khoản sinh viên.
- **B.** Xóa tài khoản sinh viên để tạo lại từ đầu.
- **C.** Tự ý đặt lại mật khẩu của sinh viên thành ngày sinh nhật.
- **D.** Kiểm tra đối soát hình ảnh chụp thẻ sinh viên/CCCD đính kèm với dữ liệu hồ sơ trong CSDL trường; nếu hợp lệ thì bấm nút xác thực ghi nhận cờ `rescue_verified = 1`.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** KTV ở Bước 1 chỉ có quyền xác thực tính hợp lệ của hồ sơ minh chứng (`rescue_verified = 1`), không được quyền tự ý mở khóa hoặc đổi mật khẩu trực tiếp nhằm đảm bảo kiểm soát chéo.

#### Câu 65. Khi Admin bấm nút 'Phê duyệt Mở khóa Tài khoản' ở Bước 2 của trạm cứu hộ, hệ thống tự động thực hiện những cập nhật nào vào bảng `users`?

- **A.** Chỉ đổi tên sinh viên thành Admin.
- **B.** Xóa sinh viên khỏi danh sách lớp học.
- **C.** Khóa tài khoản sinh viên thêm 30 ngày.
- **D.** Đặt lại mật khẩu mã hóa Bcrypt về `123456`, mở khóa `is_active = 1`, reset số lần sai `failed_attempts = 0`, xóa `locked_until = NULL`, và bật cờ `is_first_login = 1`.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Admin phê duyệt mở khóa sẽ kích hoạt chuỗi lệnh: gỡ khóa tài khoản (`is_active = 1`), reset số lần nhập sai, cấp mật khẩu tạm mặc định `123456` và kích hoạt `is_first_login = 1` để ép đổi mật khẩu.

#### Câu 66. Hàm `TicketRepository::findDuplicateGuest($userId)` áp dụng cơ chế nghiệp vụ nào để ngăn chặn hành vi spam phá hoại trạm cứu hộ?

- **A.** Kiểm tra nếu người dùng đã có đơn cứu hộ chưa đóng (`status != 'closed'`) hoặc đã gửi đơn trong ngày hôm nay (`DATE(created_at) = CURDATE()`) thì lập tức chặn gửi mới và điều hướng xem lại tiến độ đơn cũ.
- **B.** Tự động khóa máy tính của người gửi.
- **C.** Gửi thông báo cảnh sát mạng.
- **D.** Chặn không cho sinh viên nộp bài tập lớn.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Chống spam trạm cứu hộ: một sinh viên chỉ được phép có 1 đơn cứu hộ đang mở hoặc 1 đơn trong ngày; hệ thống điều hướng trực tiếp sang trang `guest_lookup` kèm mã đơn cũ để theo dõi.

#### Câu 67. Trong tính năng Quên mật khẩu tự phục vụ (SSPR), mã OTP ngẫu nhiên do hàm `PasswordReset::generateAlphabeticOtp(6)` sinh ra có đặc điểm bảo mật và UX nào đặc biệt?

- **A.** Gồm 6 chữ số từ 0 đến 9.
- **B.** Chứa các ký tự đặc biệt tiếng Trung Quốc.
- **C.** Luôn cố định là chuỗi `AAAAAA`.
- **D.** Gồm 6 chữ cái in hoa ngẫu nhiên nhưng đã loại bỏ triệt để hai ký tự `I` và `O` để người dùng không bao giờ bị gõ nhầm với số `1` và số `0`.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm sinh OTP chữ cái thông minh loại bỏ chữ `I` và `O` giúp loại trừ 100% các lỗi nhìn nhầm giữa `I` với `1` và `O` với `0`, giảm tối đa sự bực bội của người dùng khi nhập mã trên điện thoại.

#### Câu 68. Thời hạn hiệu lực an toàn của mã token khôi phục mật khẩu gửi qua email trường cấp được quy định chính xác là bao nhiêu phút?

- **A.** Chính xác 15 phút kể từ thời điểm phát hành qua email (`expires_at = NOW() + INTERVAL 15 MINUTE`).
- **B.** Vĩnh viễn không bao giờ hết hạn.
- **C.** 1 phút.
- **D.** 24 giờ.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Theo chuẩn an ninh OWASP SSPR, thời hạn hiệu lực của OTP/token khôi phục mật khẩu phải đủ ngắn (15 phút) để giảm thiểu cửa sổ tấn công nếu hòm thư của người dùng bị lộ.

#### Câu 69. Khi người dùng yêu cầu khôi phục mật khẩu mới tại `forgot_password`, hệ thống thực hiện thao tác gì với các token cũ của tài khoản đó trong bảng `password_resets`?

- **A.** Giữ nguyên tất cả token cũ để dùng lại.
- **B.** Tự động xóa bỏ toàn bộ các bản ghi token cũ của tài khoản đó (`DELETE FROM password_resets WHERE user_id = :uid`) nhằm ngăn chặn tấn công phát lại (Replay Attack).
- **C.** Chuyển token cũ sang cho bạn cùng lớp.
- **D.** Gửi toàn bộ token cũ lên mạng xã hội.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc One-Active-Token: mỗi tài khoản chỉ được phép tồn tại duy nhất một token khôi phục hợp lệ tại một thời điểm, các token phát hành trước đó đều bị tiêu hủy ngay lập tức.

#### Câu 70. Tiện ích trải nghiệm người dùng (UX) trên màn hình web ngay sau khi người dùng gửi yêu cầu khôi phục mật khẩu thành công hiển thị những tính năng gì?

- **A.** Một trò chơi điện tử để giết thời gian.
- **B.** Bắt buộc người dùng phải điền thêm thông tin gia đình.
- **C.** Danh sách điểm học phần của sinh viên.
- **D.** Đồng hồ đếm ngược 15:00 trực quan thông báo thời hạn hiệu lực của token và nút bấm tiện ích `📬 Mở tab Gmail của bạn ➔` trên trình duyệt.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giao diện hiển thị đồng hồ đếm ngược 15 phút để nhắc nhở người dùng nhanh chóng vào hòm thư, kèm nút bấm một chạm mở tab webmail (Gmail/Outlook) trên trình duyệt giúp trải nghiệm mượt mà.

#### Câu 71. Khi sinh viên thiết lập mật khẩu mới tại màn hình `reset_password`, hệ thống kiểm tra những điều kiện ràng buộc an toàn nào đối với mật khẩu mới?

- **A.** Mật khẩu bắt buộc phải trùng với mã số sinh viên.
- **B.** Chỉ cần mật khẩu có ít nhất 1 chữ số.
- **C.** Độ dài tối thiểu >= 6 ký tự, không được đặt lại về mật khẩu mặc định `123456`, và bắt buộc không được trùng với mật khẩu cũ hiện tại (kiểm tra qua `password_verify()`).
- **D.** Không có bất kỳ ràng buộc nào.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống kiểm tra 3 tầng an ninh: độ dài tối thiểu, chặn mật khẩu khởi tạo phổ biến `123456`, và dùng `password_verify($newPass, $currentHash)` để ngăn người dùng đặt lại đúng mật khẩu cũ.

#### Câu 72. Cột `is_first_login` trong bảng `users` được cập nhật lại thành giá trị gì sau khi người dùng đổi mật khẩu lần đầu thành công?

- **A.** `0` (đánh dấu tài khoản đã được bảo vệ bằng mật khẩu riêng, giải phóng quyền truy cập đầy đủ).
- **B.** `-1`.
- **C.** `1`.
- **D.** `NULL`.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cập nhật `is_first_login = 0` giúp giải phóng tài khoản khỏi trạng thái bắt buộc đổi mật khẩu, người dùng sẽ không bị Middleware chặn lại ở các lần đăng nhập tiếp theo.

#### Câu 73. Trên trang tra cứu đơn cứu hộ `guest_lookup`, nếu người dùng nhập mã của một phiếu nội bộ thông thường (ví dụ phiếu học phần nộp bài) thay vì mã đơn cứu hộ, hệ thống phản ứng ra sao?

- **A.** Hiển thị công khai toàn bộ thông tin phiếu nội bộ cho người ngoài xem.
- **B.** Tự động giải quyết phiếu.
- **C.** Xóa phiếu nội bộ đó ngay lập tức.
- **D.** Nhận diện đây là phiếu nội bộ (`is_guest_ticket = 0`), giấu kín thông tin và hiển thị thông báo yêu cầu người dùng đăng nhập tài khoản trường để xem tiến độ.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế bảo vệ dữ liệu cá nhân: nếu `$ticket && empty($ticket['is_guest_ticket'])`, hệ thống bật cờ `isInternalTicket = true` và yêu cầu đăng nhập, không để lộ nội dung trao đổi nội bộ ra ngoài cổng khách.

#### Câu 74. Cơ chế khóa mềm tài khoản (Soft Lock) tự động kích hoạt khi người dùng gõ sai mật khẩu bao nhiêu lần liên tiếp trên hệ thống?

- **A.** Không bao giờ khóa tài khoản.
- **B.** Sai 20 lần.
- **C.** Gõ sai mật khẩu quá 5 lần liên tiếp sẽ bị khóa tạm thời 30 phút qua trường `locked_until = NOW() + INTERVAL 30 MINUTE`.
- **D.** Sai 1 lần.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế chống dò quét mật khẩu (Anti-Brute Force): trường `failed_attempts` tăng dần sau mỗi lần sai; khi đạt mốc 5 lần, hệ thống gán `locked_until` 30 phút và chặn đăng nhập đến khi hết hạn.

#### Câu 75. Trong môi trường thử nghiệm localhost không có kết nối máy chủ gửi thư SMTP Internet, email reset mật khẩu và thông báo được ghi nhận vào đâu để kiểm thử trực quan?

- **A.** Gửi qua tin nhắn Telegram của Admin.
- **B.** Ghi nhận đầy đủ vào hòm thư ảo `storage/mailbox.json` với giao diện xem hòm thư tích hợp sẵn trên hệ thống.
- **C.** Tự động in ra máy in để bàn.
- **D.** Bị hủy bỏ hoàn toàn không lưu vết.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dịch vụ gửi thư phát hiện môi trường Localhost sẽ tự động tuần tự hóa thư thành JSON và lưu vào `storage/mailbox.json`, cho phép lập trình viên và giáo viên chấm thi kiểm tra nội dung email ngay lập tức.

## 7. QUẢN LÝ HỌC PHẦN, BÀI TẬP BUỔI HỌC & NGHIỆM THU CSAT 5 SAO

#### Câu 76. Tính năng 'Cứu hộ Buổi học' (Lesson Rescue Ticket) liên kết chặt chẽ với bảng nào trong CSDL để chỉ định chính xác bài tập mà sinh viên gặp sự cố nộp bài?

- **A.** Bảng `holidays`.
- **B.** Bảng `classes`.
- **C.** Bảng `password_resets`.
- **D.** Bảng `lessons` thông qua khóa ngoại `tickets.lesson_id` trỏ tới bài tập buổi học cụ thể.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Phiếu cứu hộ buổi học gắn với `tickets.lesson_id`, tự động trích xuất thông tin học phần, buổi học, hạn chót deadline và nội dung đề bài để KTV và Giảng viên đối soát nguyên nhân trễ bài.

#### Câu 77. Khi sinh viên nộp tệp bài tập thực hành lên Cổng Học Phần, tệp đính kèm được hệ thống lưu trữ vào thư mục nào trên máy chủ với quy tắc đặt tên ra sao?

- **A.** Lưu thẳng vào thư mục `C:\Windows\System32`.
- **B.** Lưu tạm trong RAM máy tính.
- **C.** Lưu vào thư mục gốc của ổ cứng D.
- **D.** Lưu vào thư mục `uploads/assignments/` với tiền tố tên tệp chuẩn hóa `sub_{student_id}_lesson{lesson_id}_{timestamp}.ext` nhằm chống trùng lặp và an toàn tải lên.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dịch vụ nộp bài đặt tên file theo cấu trúc `sub_{student_id}_lesson{lesson_id}_{timestamp}.{ext}`, lưu riêng biệt trong `uploads/assignments/` để Giảng viên dễ dàng tải về chấm bài theo từng sinh viên.

#### Câu 78. Giảng viên phụ trách bộ môn thực hiện chấm điểm và ghi nhận nhận xét bài tập của sinh viên tại bảng nào trong CSDL?

- **A.** Bảng `ticket_replies`.
- **B.** Bảng `priorities`.
- **C.** Bảng `assignments` với hai trường thông tin nghiệp vụ chính là `score` (thang điểm 10 hệ số thập phân) và `feedback` (nhận xét đánh giá của giảng viên).
- **D.** Bảng `categories`.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bảng `assignments` lưu trữ toàn bộ trạng thái nộp bài của sinh viên; khi Giảng viên chấm bài, hệ thống cập nhật `score`, `feedback` và thời điểm chấm `graded_at`.

#### Câu 79. Sau khi sinh viên đã nộp bài tập lớn thành công, sinh viên có được phép nộp lại hoặc cập nhật file bài tập mới không?

- **A.** Được phép cập nhật nộp lại file mới miễn là thời điểm nộp lại vẫn còn nằm trước hạn chót Deadline của buổi học đó.
- **B.** Tuyệt đối không được nộp lại trong bất kỳ trường hợp nào.
- **C.** Được nộp lại sau khi kỳ học đã kết thúc 1 năm.
- **D.** Chỉ được nộp lại nếu trả thêm phí chấm bài.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy chế học phần hỗ trợ sinh viên nộp đè bản cập nhật bài tập lớn trước hạn deadline (`NOW() <= due_date`), file cũ được thay thế bằng file mới và hệ thống cập nhật lại thời điểm `submitted_at`.

#### Câu 80. Quy trình đánh giá chất lượng dịch vụ CSAT (Customer Satisfaction) được kích hoạt hiển thị cho sinh viên vào thời điểm nào trong vòng đời phiếu?

- **A.** Khi KTV đã khắc phục xong sự cố và chuyển trạng thái phiếu sang `resolved` (Đã giải quyết), sinh viên nhận thông báo vào nghiệm thu kết quả.
- **B.** Ngay khi sinh viên vừa bấm nút gửi phiếu hỗ trợ mới.
- **C.** Sau khi sinh viên đã tốt nghiệp ra trường.
- **D.** Chỉ kích hoạt vào ngày Nhà giáo Việt Nam 20/11.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** CSAT chỉ xuất hiện khi phiếu ở trạng thái `resolved`: KTV đã giải quyết xong và mời người dùng vào kiểm tra nghiệm thu thực tế, đánh giá từ 1 đến 5 sao trước khi phiếu chính thức đóng hoàn tất.

#### Câu 81. Hệ thống Helpdesk CNTT Nhóm 1 thiết kế trải nghiệm đánh giá chất lượng CSAT theo mô hình hiện đại nào của các ứng dụng công nghệ phổ biến (Grab / Be / Shopee)?

- **A.** Bộ chọn 5 ngôi sao trực quan tương tác (Interactive 5-Star Rating) kèm ô nhập nhận xét góp ý nhanh và các nhãn gợi ý cảm xúc.
- **B.** Bắt sinh viên đến trực tiếp văn phòng trường để làm bài khảo sát trắc nghiệm 50 câu.
- **C.** Gọi điện thoại tự động ghi âm.
- **D.** Gửi thư tay qua đường bưu điện.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Giao diện CSAT được thiết kế chuẩn UX hiện đại với 5 ngôi sao vàng trực quan, sinh viên có thể chấm điểm nhanh 1-5 sao và để lại lời cảm ơn hoặc góp ý chỉ trong 1 thao tác bấm chuột.

#### Câu 82. Điểm đánh giá CSAT trung bình của mỗi Kỹ thuật viên (ví dụ: ⭐ 4.9 / 5.0) được hệ thống tính toán và hiển thị công khai ở những khu vực nào?

- **A.** Chỉ Admin mới xem được trong cơ sở dữ liệu ngầm.
- **B.** In thành bảng vàng treo ở cổng trường.
- **C.** Hiển thị công khai cạnh tên KTV trên Bàn làm việc KTV (Agent Workspace), Admin Dashboard và thẻ thông tin người tiếp nhận trên chi tiết phiếu.
- **D.** Đăng tải lên báo chí truyền thông.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Điểm sao CSAT trung bình được tính bằng `AVG(score)` từ bảng `ticket_ratings` và hiển thị minh bạch cạnh tên KTV, tạo động lực cạnh tranh lành mạnh và nâng cao tinh thần phục vụ người học.

#### Câu 83. Nếu sau khi KTV chuyển phiếu sang trạng thái `resolved` mà sinh viên không vào đánh giá CSAT thì hệ thống xử lý như thế nào sau 24 giờ?

- **A.** Cơ chế tự động đóng (Auto-close): hệ thống tự động chuyển trạng thái phiếu từ `resolved` sang `closed` với lý do hoàn tất mặc định sau 24 giờ để không tồn đọng phiếu.
- **B.** Gửi phiếu quay lại trạng thái `new`.
- **C.** Tự động trừ 1 điểm rèn luyện của sinh viên.
- **D.** Khóa tài khoản KTV phụ trách.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Quy tắc tự động đóng sau 24 giờ (24h Auto-close Rule) là chuẩn mực ITIL v4: nếu người dùng hài lòng và không phản hồi thêm trong 24h, phiếu sẽ tự động đóng để KTV được ghi nhận KPI.

#### Câu 84. Nếu sự cố kỹ thuật của sinh viên vẫn chưa được khắc phục triệt để sau khi KTV bấm `resolved`, sinh viên sử dụng tính năng gì trên giao diện?

- **A.** Đổi mật khẩu tài khoản của KTV.
- **B.** Gửi đơn khiếu nại lên Bộ Giáo dục.
- **C.** Tạo thêm 10 phiếu mới giống hệt nhau.
- **D.** Bấm nút 'Yêu cầu Hỗ trợ lại / Mở lại phiếu' (Reopen Ticket), nhập lý do cụ thể để phiếu quay về trạng thái `in_progress` cho KTV tiếp tục xử lý.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nút 'Yêu cầu tiếp tục hỗ trợ' (Reopen Ticket) kèm Modal nhập lý do mở lại giúp sinh viên phản hồi ngay khi giải pháp của KTV chưa triệt để, bảo đảm quyền lợi tối đa cho người học.

#### Câu 85. Khi phiếu hỗ trợ được sinh viên kích hoạt mở lại (Reopened), trường dữ liệu `reopen_count` trong bảng `tickets` thay đổi như thế nào?

- **A.** Tự động nhân đôi số tiền học phí.
- **B.** Được đặt lại về số 0.
- **C.** Tự động tăng thêm 1 đơn vị (`reopen_count = reopen_count + 1`) và hệ thống hiển thị huy hiệu cảnh báo tím `🔄 MỞ LẠI (X LẦN)` trên danh sách vé.
- **D.** Bị xóa khỏi CSDL.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `reopen_count` đếm số lần sinh viên phải mở lại phiếu, giúp Admin và trưởng bộ môn đo lường chỉ số FCR (First Contact Resolution - Tỷ lệ giải quyết dứt điểm ngay lần đầu) của đội ngũ KTV.

#### Câu 86. Giảng viên có thể xuất bảng điểm và danh sách nộp bài tập của lớp học phần ra định dạng tệp nào để lưu trữ hoặc nộp báo cáo cho Phòng Đào tạo?

- **A.** Tệp Photoshop PSD.
- **B.** Tệp video MP4.
- **C.** Tệp âm thanh MP3.
- **D.** Định dạng tệp CSV chuẩn UTF-8 có chèn ký tự BOM (`\xEF\xBB\xBF`) giúp hiển thị hoàn hảo tiếng Việt có dấu trên Microsoft Excel mà không bị lỗi font.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm xuất CSV chèn chuỗi BOM UTF-8 (`\xEF\xBB\xBF`) ở đầu tệp, giúp phần mềm Microsoft Excel tự động nhận diện bảng mã UTF-8 tiếng Việt chuẩn mực mà người dùng không cần chỉnh tay.

#### Câu 87. Cơ chế 'Chặn sinh viên nộp bài trễ hạn' được thực hiện bằng phép so sánh thời gian nào giữa thời điểm nộp và dữ liệu trong bảng `lessons`?

- **A.** So sánh thời điểm hiện tại `NOW()` với trường thời hạn chót nộp bài `lessons.due_date`; nếu `NOW() > due_date` thì khóa form nộp và hiển thị thông báo đã hết hạn.
- **B.** So sánh ngày sinh của sinh viên với ngày lễ.
- **C.** So sánh mã sinh viên với mã khóa học.
- **D.** So sánh số lượng sinh viên trong lớp với sĩ số tối đa.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kiểm tra deadline: hệ thống so khớp thời gian thực tế `time() > strtotime($lesson['due_date'])`; khi quá hạn, form tải file bị vô hiệu hóa và sinh viên được hướng dẫn tạo phiếu 'Cứu hộ buổi học' nếu có lý do chính đáng.

## 8. AN TOÀN THÔNG TIN, CHỐNG TẤN CÔNG WEB & XỬ LÝ NGOẠI LỆ EDGE CASES

#### Câu 88. Để triệt tiêu hoàn toàn lỗ hổng SQL Injection trên toàn bộ hệ thống, chuẩn mực lập trình nào bắt buộc phải tuân thủ nghiêm ngặt trong mã nguồn PHP PDO?

- **A.** Chỉ sử dụng cơ sở dữ liệu trên tệp Excel.
- **B.** Tắt tính năng thông báo lỗi của máy chủ.
- **C.** Bắt buộc sử dụng Prepared Statements với các tham số giữ chỗ ràng buộc (`$pdo->prepare()` và `$stmt->execute([':param' => $value])`), tuyệt đối không nối chuỗi biến ngoài vào SQL.
- **D.** Ghép trực tiếp biến `$_GET` và `$_POST` vào câu lệnh SQL bằng toán tử dấu chấm `.`. 

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Kỹ thuật Prepared Statements tách rời hoàn toàn mã lệnh SQL và dữ liệu đầu vào của người dùng, CSDL biên dịch cấu trúc trước rồi mới gắn giá trị, khiến kẻ tấn công không thể bẻ gãy cú pháp SQL.

#### Câu 89. Kỹ thuật xử lý dữ liệu nào được áp dụng khi xuất báo cáo ra file CSV nhằm triệt tiêu lỗ hổng tấn công CSV Formula Injection (Excel Macro Injection)?

- **A.** Chuyển toàn bộ nội dung thành hình ảnh.
- **B.** Tự động kiểm tra và chèn thêm dấu nháy đơn `'` hoặc loại bỏ các ký tự khởi đầu công thức nguy hiểm (`=`, `+`, `-`, `@`) ở đầu mỗi ô dữ liệu trước khi ghi ra tệp CSV.
- **C.** Nén tệp bằng mật khẩu 50 ký tự.
- **D.** Đổi đuôi file thành `.exe`.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Lỗ hổng CSV Injection xảy ra khi ô dữ liệu bắt đầu bằng `=`, `+`, `-`, `@` bị Excel hiểu là lệnh thực thi mã độc. Thêm dấu nháy đơn `'` phía trước ép Excel hiểu ô đó chỉ là văn bản thuần túy (Text literal).

#### Câu 90. Để ngăn ngừa lỗ hổng Cross-Site Scripting (XSS) khi hiển thị tiêu đề phiếu, tên người dùng hoặc nội dung trao đổi ra màn hình trình duyệt, hàm PHP nào được sử dụng bắt buộc?

- **A.** `md5()`.
- **B.** `base64_encode()`.
- **C.** `htmlspecialchars($string, ENT_QUOTES, 'UTF-8')`.
- **D.** `strtoupper()`.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hàm `htmlspecialchars()` chuyển đổi các ký tự nguy hiểm của HTML như `<`, `>`, `&`, `"`, `'` thành các thực thể an toàn tương ứng (như `&lt;`, `&gt;`), ngăn ngừa trình duyệt thực thi mã JavaScript độc hại.

#### Câu 91. Khi người dùng tải tệp minh chứng hoặc bài tập lên máy chủ, quy trình thẩm định an toàn tệp tải lên (File Upload Security) kiểm tra những yếu tố nào?

- **A.** Cho phép tải lên mọi loại tệp kể cả file thực thi `.exe`, `.php`, `.sh`.
- **B.** Kiểm tra mã lỗi upload `UPLOAD_ERR_OK`, đối soát đuôi tệp trong Whitelist an toàn (`jpg, png, pdf, zip`), giới hạn dung lượng tệp và đổi tên file bằng hàm băm ngẫu nhiên chống Path Traversal.
- **C.** Tự động gửi tệp lên mạng xã hội.
- **D.** Chỉ kiểm tra tên người nộp bài có đẹp hay không.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Dịch vụ `UploadService` kiểm tra nghiêm ngặt Whitelist định dạng, kiểm tra dung lượng an toàn và tạo tên tệp ngẫu nhiên mới lưu vào thư mục riêng, triệt tiêu hoàn toàn tấn công ghi đè file hoặc thực thi webshell.

#### Câu 92. Cơ chế chống tấn công giả mạo yêu cầu chéo trang (Cross-Site Request Forgery &ndash; CSRF) trong các biểu mẫu thay đổi dữ liệu quan trọng hoạt động ra sao?

- **A.** Bắt người dùng phải quét vân tay trước mỗi lần bấm nút.
- **B.** Tự động chặn tất cả người dùng sau 18:00 tối.
- **C.** Khởi tạo một mã token ngẫu nhiên bí mật lưu trong `$_SESSION['csrf_token']`, nhúng vào thẻ `<input type="hidden" name="csrf_token">` và kiểm tra so khớp token này ở mỗi request POST gửi lên.
- **D.** Chỉ cho phép truy cập web từ máy tính ở thư viện.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Mã CSRF Token bí mật được sinh ngẫu nhiên theo từng phiên làm việc của người dùng, kẻ tấn công từ trang web thứ ba không thể đoán được token này để thực thi các lệnh POST trái phép nhân danh người dùng.

#### Câu 93. Tại sao thuật toán mã hóa mật khẩu của hệ thống sử dụng hàm `password_hash($password, PASSWORD_BCRYPT)` thay vì các hàm băm cũ như `md5()` hay `sha1()`?

- **A.** Bcrypt tự động dịch mật khẩu sang tiếng Anh.
- **B.** Bcrypt tự động tích hợp muối (Salt) ngẫu nhiên cho từng tài khoản và có độ phức tạp tính toán (Work Factor) cao, chống bẻ khóa hiệu quả trước các cuộc tấn công dùng bảng cầu vồng (Rainbow Table).
- **C.** Vì Bcrypt chạy nhanh hơn MD5 gấp 100 lần.
- **D.** Do MD5 không dùng được trên điện thoại.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** MD5 và SHA-1 đã lỗi thời và dễ dàng bị bẻ khóa trong vài giây bằng GPU. Bcrypt là thuật toán băm chậm thích ứng (Adaptive Slow Hash), tích hợp muối tự động, là chuẩn mực bảo mật mật khẩu quốc tế hiện nay.

#### Câu 94. Khi người dùng gửi một biểu mẫu có tệp tải lên vượt quá giới hạn cấu hình `upload_max_filesize` của PHP trong `php.ini`, hệ thống xử lý ngoại lệ như thế nào?

- **A.** Tự động nén tệp lại còn 0 byte.
- **B.** Bắt mã lỗi `UPLOAD_ERR_INI_SIZE` từ mảng `$_FILES['attachment']['error']`, lưu thông báo lỗi thân thiện vào flash message và điều hướng an toàn về form cũ để người dùng chọn tệp nhẹ hơn.
- **C.** Hệ thống bị sập hoàn toàn và hiển thị màn hình trắng (White Screen of Death).
- **D.** Tự động xóa tài khoản của người gửi.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Hệ thống kiểm tra mã lỗi `$_FILES['attachment']['error'] === UPLOAD_ERR_INI_SIZE` và hiển thị cảnh báo thân thiện cho người dùng biết tệp vượt quá dung lượng quy định, bảo vệ máy chủ hoạt động ổn định.

#### Câu 95. Khi xảy ra sự cố đứt cáp quang mạng hoặc lỗi máy chủ Canvas LMS diện rộng toàn trường, tính năng 'Báo Cáo Sự Cố Diện Rộng' (Major Incident) mang lại lợi ích gì cho hệ thống?

- **A.** Xóa toàn bộ bài tập của sinh viên.
- **B.** Tự động gửi tin nhắn đòi nợ.
- **C.** Tự động cho toàn bộ sinh viên nghỉ học 1 tuần.
- **D.** Admin công bố Banner cảnh báo đỏ toàn trường, thông báo cho người dùng biết sự cố đã được tiếp nhận và cho phép gộp hàng loạt phiếu con vào phiếu cha (`parent_ticket_id`), ngăn chặn nguy cơ vỡ tải KTV.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế Major Incident chuẩn ITIL: công bố thông tin tức thời cho toàn trường để sinh viên không hoang mang và không tiếp tục tạo thêm hàng trăm phiếu rác cùng một vấn đề, giúp KTV tập trung sửa lỗi hạ tầng.

#### Câu 96. Khi một Phiếu Sự Cố Cha (Major Incident) được Admin hoặc KTV xử lý xong và chuyển sang trạng thái hoàn tất, các phiếu con liên kết (`parent_ticket_id = ID_Cha`) sẽ được xử lý như thế nào?

- **A.** Chuyển các phiếu con sang trường khác.
- **B.** Hệ thống tự động cập nhật đồng loạt các phiếu sự cố con sang trạng thái hoàn tất kèm thông báo giải pháp kỹ thuật, tiết kiệm tối đa thời gian thao tác thủ công cho đội ngũ hỗ trợ.
- **C.** Xóa sạch toàn bộ các phiếu con khỏi CSDL.
- **D.** Các phiếu con vẫn bị bỏ mặc không ai giải quyết.

> **👉 Đáp án đúng:** [**B**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Cơ chế đóng hàng loạt (Cascade Resolution) theo chuẩn ITIL: đóng phiếu sự cố gốc sẽ tự động kích hoạt cập nhật trạng thái `resolved`/`closed` cho toàn bộ các phiếu con kèm thông báo gửi đến từng sinh viên.

#### Câu 97. Để ngăn ngừa nguy cơ bị chiếm đoạt phiên làm việc (Session Fixation Attack), hàm PHP nào được hệ thống gọi ngay sau khi người dùng xác thực mật khẩu thành công tại `login.php`?

- **A.** `session_regenerate_id(true)` để cấp một định danh phiên làm việc mới hoàn toàn và hủy bỏ ID phiên cũ.
- **B.** `session_unset()`.
- **C.** `session_destroy()`.
- **D.** `session_abort()`.

> **👉 Đáp án đúng:** [**A**]
> **💡 Căn cứ kỹ thuật & Giải thích:** `session_regenerate_id(true)` thay đổi Session ID của người dùng ngay thời điểm chuyển từ trạng thái khách sang trạng thái đã đăng nhập, vô hiệu hóa hoàn toàn mọi nỗ lực cài sẵn ID phiên của kẻ tấn công.

#### Câu 98. Trong trường hợp CSDL MySQL gặp sự cố kết nối hoặc quá tải đột ngột, khối xử lý ngoại lệ `try ... catch (\PDOException $e)` ở các Controller phản ứng ra sao để bảo vệ hệ thống?

- **A.** In toàn bộ tên đăng nhập, mật khẩu CSDL và cấu trúc bảng lỗi ra màn hình để người dùng đọc.
- **B.** Tự động tạo ra một CSDL mới trên Facebook.
- **C.** Tự động tắt nguồn máy tính của người dùng.
- **D.** Bắt trọn vẹn ngoại lệ, ghi log lỗi nội bộ trên máy chủ và hiển thị thông báo lỗi thân thiện, bảo mật thông tin kiến trúc hạ tầng trước kẻ gian.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Nguyên tắc an ninh Error Handling: không bao giờ để lộ thông báo lỗi CSDL (Database Stack Trace) ra giao diện người dùng; hệ thống ghi log bảo mật và trả về giao diện thông báo hệ thống đang bảo trì.

#### Câu 99. Cơ chế 'Bảng dữ liệu có thể thu gọn' (Collapsible Data Tables) với nút bấm 'Ẩn bảng / Hiện bảng' trên giao diện quản trị mang lại giá trị thực tiễn nào cho trải nghiệm người dùng?

- **A.** Làm cho trang web chạy chậm hơn.
- **B.** Dùng để che giấu các sai phạm của KTV.
- **C.** Tự động xóa vĩnh viễn các bản ghi trong bảng khi bấm ẩn.
- **D.** Cho phép người dùng chủ động tùy biến ẩn bớt các khối thông tin phụ hoặc danh sách dài để tập trung vào các tác vụ khẩn cấp, giảm tải thị giác và tối ưu hóa không gian làm việc trên màn hình nhỏ.

> **👉 Đáp án đúng:** [**D**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Collapsible UI nâng cao tính công thái học (Ergonomics): người dùng có thể đóng gọn các bảng dữ liệu lịch sử hoặc thống kê ít dùng để màn hình làm việc thoáng đãng và thao tác nhanh chóng hơn.

#### Câu 100. Khi một sinh viên cố tình sử dụng công cụ tự động (Bot Script) gửi liên tiếp hàng chục request tạo phiếu trong thời gian ngắn, hệ thống chặn đứng hành vi này bằng tầng phòng thủ nào?

- **A.** Tự động gửi tin nhắn báo công an.
- **B.** Tự động đăng ký thêm 5 môn học cho sinh viên.
- **C.** Tầng kiểm soát trùng lặp 3 lớp trong `TicketRepository::findDuplicate()` kết hợp kiểm tra danh mục, bài học và trạng thái phiếu đang mở, ngay lập tức chặn đứng và chuyển hướng xem phiếu cũ.
- **D.** Hệ thống chấp nhận tất cả các phiếu và gửi cho KTV xử lý mệt mỏi.

> **👉 Đáp án đúng:** [**C**]
> **💡 Căn cứ kỹ thuật & Giải thích:** Bộ lọc kiểm soát trùng lặp 3 lớp (Anti-Spam & Duplicate Shield) phát hiện ngay việc người gửi đang có phiếu chưa hoàn tất hoặc đã gửi phiếu cùng mảng trong ngày, từ chối tạo mới và bảo vệ hàng đợi SLA không bị ngập lụt.


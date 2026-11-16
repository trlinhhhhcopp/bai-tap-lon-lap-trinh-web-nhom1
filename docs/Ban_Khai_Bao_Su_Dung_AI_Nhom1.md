# BẢN KHAI BÁO SỬ DỤNG TRÍ TUỆ NHÂN TẠO (AI)

**Bài tập lớn học phần:** Lập trình Web & Ứng dụng (Mã HP: WEB101)  
**Trường:** Đại học Thủ Đô Hà Nội — Khoa Công nghệ Thông tin  
**Nguyên tắc:** AI chỉ là trợ lý học tập. Sinh viên phải tự hiểu, tự giải thích và chịu trách nhiệm về nội dung, dữ liệu, mã nguồn nộp. Không sao chép kết quả AI mà không kiểm chứng hoặc không khai báo. Không đưa dữ liệu nhạy cảm lên công cụ AI khi chưa được phép. Bản khai báo nộp kèm báo cáo và mã nguồn.

---

### I. THÔNG TIN CHUNG

* **Họ và tên sinh viên (hoặc trưởng nhóm):** Phạm Thị Thanh Ngân *(hoặc Trần Thục Linh)*
* **Mã sinh viên:** 224001736  
* **Lớp học phần:** Lập trình Web & Ứng dụng — D24 CNTT1  
* **Thành viên nhóm (Nhóm 1):**
  1. **Phạm Thị Thanh Ngân** (Trưởng nhóm) — MSV: 224001736 (Phân hệ: KTV Workbench, SLA Engine, CSAT)
  2. **Trần Thục Linh** — MSV: 224001729 (Phân hệ: Admin Control Center, Quản trị tài khoản & Phân quyền RBAC)
  3. **Lê Hà My** — MSV: 224001735 (Phân hệ: Cổng Giảng viên, Phân công học phần, Quản lý Buổi học & Deadline)
  4. **Nguyễn Ánh Nhi** — MSV: 224001742 (Phân hệ: Cổng Sinh viên, Trạm cứu hộ nộp bài, Nghiệm thu đánh giá CSAT)
  5. **Phan Công Thành** — MSV: 224001746 (Phân hệ: Quản trị Sự cố diện rộng Major Incident, Quản lý Lịch lễ trừ SLA)
* **Tên đề tài bài tập lớn:**  
  *Xây dựng Hệ thống Tiếp nhận & Xử lý Hỗ trợ CNTT Trực tuyến theo chuẩn ITIL v4 (Helpdesk Online TUD) — Trường Đại học Thủ Đô Hà Nội.*

---

### II. CÔNG CỤ AI ĐÃ SỬ DỤNG

| Công cụ AI (tên, phiên bản) | Mục đích sử dụng cụ thể (gợi ý ý tưởng/giải pháp, viết hoặc sửa mã, kiểm thử, phân tích dữ liệu, hỗ trợ báo cáo...) | Mức độ đóng góp (Ít / TB / Nhiều) |
|---|---|:---:|
| **Google Antigravity / Gemini 2.5 Pro & Claude 3.5 Sonnet** | • Hỗ trợ rà soát mã nguồn (Code Review), tối ưu hóa kiến trúc chuẩn MVC PHP (tách biệt Controller - Model - View).<br>• Gợi ý cấu trúc giải thuật tính toán SLA theo giờ hành chính và loại trừ ngày nghỉ lễ.<br>• Hỗ trợ viết script kiểm thử tự động CLI và vẽ sơ đồ kiến trúc dữ liệu ERD 4 tầng. | **TB** |
| **ChatGPT / GitHub Copilot** | • Gợi ý tra cứu cú pháp CSS Tailwind, cấu trúc thẻ HTML5 ngữ nghĩa và định dạng icon trực quan.<br>• Hỗ trợ kiểm tra nhanh lỗi cú pháp (Syntax error) trong quá trình debug mã nguồn PHP/SQL. | **Ít** |

---

### III. PHÂN ĐỊNH ĐÓNG GÓP CỦA SINH VIÊN VÀ AI THEO TỪNG GIAI ĐOẠN

| Giai đoạn | Phần sinh viên tự làm (đóng góp cốt lõi) | Phần AI hỗ trợ (ghi cụ thể; không có ghi “Không”) |
|---|---|---|
| **1. Bài toán / yêu cầu**<br>*Mục tiêu, phạm vi, ràng buộc* | • Khảo sát thực tế nhu cầu cứu hộ sự cố học tập và cấp lại mật khẩu tại trường ĐH Thủ Đô Hà Nội.<br>• Xác định phạm vi 4 nhóm tác tử (Sinh viên, Giảng viên, KTV, Admin).<br>• Đặt ra yêu cầu nghiệp vụ: Cam kết phản hồi SLA 15 phút, tạm dừng SLA khi chờ người dùng, và trạm cứu hộ không cần đăng nhập. | • Gợi ý cách phân loại các mức độ ưu tiên theo chuẩn ITIL v4 (`URGENT`, `HIGH`, `MED`, `LOW`).<br>• Đóng góp ý tưởng luồng nghiệp vụ 1 trang (Single Page Portal) cho sinh viên nộp bài. |
| **2. Dữ liệu / đầu vào–ra**<br>*Nguồn, quyền dùng, chia tập nếu có* | • Thiết kế toàn bộ cấu trúc 14 bảng CSDL chuẩn 3NF (`users`, `tickets`, `categories`, `course_lessons`, `ticket_ratings`...).<br>• Tự thiết lập các ràng buộc khóa chính (PK), khóa ngoại (FK) và liên kết cascade.<br>• Tự xây dựng bộ dữ liệu mẫu (Seeding) gồm 32 tài khoản thực tế, danh mục dịch vụ và lịch nghỉ lễ năm học. | • Hỗ trợ chuyển đổi nhanh lược đồ bảng sang cú pháp câu lệnh SQL `CREATE TABLE` và `INSERT` dữ liệu mẫu chuẩn UTF-8.<br>• Gợi ý cách đặt tên trường theo chuẩn quốc tế (`snake_case`). |
| **3. Thiết kế giải pháp**<br>*Thuật toán, mô hình hoặc kiến trúc* | • Lựa chọn mô hình kiến trúc MVC hướng đối tượng thuần OOP PHP, không dùng framework bên ngoài.<br>• Thiết kế máy trạng thái (State Machine) quản lý vòng đời vé: `new` $\rightarrow$ `in_progress` $\rightarrow$ `waiting_requester` $\rightarrow$ `resolved` $\rightarrow$ `closed`.<br>• Thiết kế giải thuật phân công tự động cân bằng tải cho KTV. | • Gợi ý cấu trúc bộ định tuyến `Router.php` và cơ chế gắn `Middleware` lọc request.<br>• Gợi ý công thức tính hệ số thời gian SLA: $\text{Effective Minutes} = \text{SLA Hours} \times \text{SLA Factor}$. |
| **4. Cài đặt và tích hợp**<br>*Mã nguồn, mô-đun, thành phần* | • Tự lập trình toàn bộ các Controller và Model của 5 phân hệ thành viên.<br>• Tự cài đặt 5 tầng bảo mật: Chống SQL Injection bằng Prepared Statement, chống CSRF bằng token `hash_equals`, lọc XSS bằng `htmlspecialchars`, phân quyền RBAC và Upload file an toàn.<br>• Tự tích hợp hệ thống thông báo chuông thời gian thực. | • Hỗ trợ chỉnh sửa và tối ưu hóa các đoạn code xử lý chuỗi ngày tháng `DateTime`.<br>• Gợi ý đoạn mã JavaScript xử lý AJAX xem trước file mã nguồn/file ZIP bài nộp (In-App Previewer). |
| **5. Thử nghiệm / kiểm thử**<br>*Train, test, sửa lỗi, an toàn* | • Tự lập bảng và chạy thực tế 25 Test Case kiểm thử chức năng trên trình duyệt.<br>• Trực tiếp kiểm thử các kịch bản biên: Giao bài tập đặt deadline quá khứ, gửi vé trùng lặp spam, nộp bài sau giờ deadline bị gắn nhãn Nộp muộn. | • Hỗ trợ viết script PHP CLI (`test_crud_cli.php`) để chạy tự động kiểm tra cú pháp và logic cơ sở dữ liệu trên Terminal.<br>• Đề xuất kịch bản kiểm thử bảo mật chống vượt quyền IDOR. |
| **6. Phân tích và báo cáo**<br>*Chỉ số, hạn chế, cách tái lập* | • Tự tổng hợp chỉ số kỹ thuật: Tỷ lệ đáp ứng SLA, điểm CSAT trung bình 4.9/5.0 sao.<br>• Phân tích rõ hạn chế của hệ thống: Chưa tích hợp thanh toán lệ phí, chưa đẩy thông báo qua Telegram/Zalo Bot.<br>• Trực tiếp thuyết trình, demo giải thích tường tận từng dòng mã nguồn trước Hội đồng chấm thi. | • Hỗ trợ định dạng bảng biểu, dàn trang báo cáo tài liệu PDF/Markdown chuẩn mực.<br>• Gợi ý kịch bản phân bổ thời gian thuyết trình 5–10 phút đạt chuẩn điểm tối đa. |

---

### IV. KIỂM CHỨNG KẾT QUẢ DO AI TẠO RA

| Nội dung do AI tạo cần kiểm chứng (mã, dữ liệu, thuật toán, mô hình, giao diện, kết quả...) | Sai sót sinh viên đã phát hiện và cách sửa | Cách/nguồn kiểm chứng (chạy lại, đối chiếu số liệu, test, tài liệu, phản biện...) |
|---|---|---|
| **1. Mã nguồn truy vấn CSDL (SQL Query)** | • **Sai sót:** AI ban đầu gợi ý một số câu truy vấn sử dụng nối chuỗi trực tiếp `$sql .= "WHERE name = '$kw'"`, có nguy cơ bị tấn công **SQL Injection**.<br>• **Cách sửa:** Sinh viên phát hiện và sửa lại 100% bằng cơ chế **Prepared Statement** với tham số ẩn danh `:kw` và hàm `bindValue()`. | • Kiểm chứng bằng cách nhập thử các chuỗi payload khai thác: `' OR '1'='1`, `'; DROP TABLE users; --` vào ô tìm kiếm.<br>• Kết quả: Hệ thống lọc an toàn, không xảy ra lỗi bảo mật. |
| **2. Thuật toán tính hạn chót SLA (`SlaService`)** | • **Sai sót:** Thuật toán do AI gợi ý ban đầu chỉ cộng dồn thời gian liên tục 24/7, không đóng băng giờ nghỉ trưa và không loại trừ ngày thứ Bảy, Chủ Nhật.<br>• **Cách sửa:** Sinh viên viết lại hàm `addBusinessMinutes()`, bổ sung khung giờ hành chính (08:00–12:00 & 13:30–17:30) và truy vấn bảng `holidays` để tự động dời sang ca làm việc hợp lệ tiếp theo. | • Kiểm chứng bằng kịch bản test thực tế: Tạo vé sự cố lúc 11:55 trưa (SLA cam kết 30 phút), đối chiếu hạn hoàn thành chính xác là 14:10 chiều (đã trừ trọn vẹn 90 phút nghỉ trưa). |
| **3. Ràng buộc ngày nộp bài tập (`course_lessons`)** | • **Sai sót:** AI ban đầu chỉ tạo trường `deadline_at` mà không kiểm tra ngày có hợp lệ hay không, dẫn đến Giảng viên có thể vô tình nhập ngày trong quá khứ.<br>• **Cách sửa:** Sinh viên tự bổ sung thuộc tính `min="<?= date('Y-m-d\TH:i') ?>"`, hàm JavaScript cảnh báo khi chọn ngày $< \text{hôm nay}$, và xác thực 2 lớp Backend `strtotime($deadline) >= today` chặn tuyệt đối. | • Kiểm thử trực tiếp trên form: Chọn ngày hôm qua $\rightarrow$ Hệ thống lập tức bật cảnh báo và từ chối submit.<br>• Chạy script kiểm thử PHP `test_lesson_delete.php` xác nhận dữ liệu đã được bảo vệ trọn vẹn. |
| **4. Sơ đồ kiến trúc CSDL 4 tầng** | • **Sai sót:** Sơ đồ AI hỗ trợ vẽ ban đầu gộp chung `courses & course_lessons` làm 1 ô (khiến đếm thiếu mất 1 bảng) và ghi nhầm `course_assignments` là gán sinh viên.<br>• **Cách sửa:** Sinh viên đã tách riêng `course_lessons` xuống Tầng 3 Nghiệp vụ và đính chính bảng phân công Giảng viên, đủ trọn vẹn 14 bảng chuẩn 3NF. | • Đối chiếu trực tiếp với câu lệnh `SHOW TABLES` trong phpMyAdmin và sơ đồ quan hệ Foreign Key thực tế của cơ sở dữ liệu. |

---

### V. CAM KẾT

Em cam kết thông tin khai báo là trung thực; em hiểu và có thể giải thích các nội dung, thuật toán, mã nguồn do mình nộp. Nếu khai báo sai hoặc sao chép kết quả AI mà không khai báo, bài tập lớn sẽ bị đánh giá Không đạt.

| | Hà Nội, ngày 16 tháng 11 năm 2026<br><br>**ĐẠI DIỆN NHÓM CAM KẾT**<br>*(Ký và ghi rõ họ tên)*<br><br><br><br>**Phạm Thị Thanh Ngân** |
|:---|:---:|

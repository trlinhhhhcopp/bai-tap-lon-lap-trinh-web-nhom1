<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 4: THIẾT KẾ CSDL & TRUY VẤN SQL
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
 * ------------------------------------------------------------------------------
 * CHƯƠNG TRÌNH CHẠY THỬ NGHIỆM TRUY VẤN CSDL VỚI DỮ LIỆU TIẾNG VIỆT
 * PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
 * SINH VIÊN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
 * LỚP: TOÁN ỨNG DỤNG D2024 (KHOA: TOÁN-CNTT)
 * ------------------------------------------------------------------------------
 * Hướng dẫn chạy:
 *   php run_demo.php
 * hoặc:
 *   C:\xampp\php\php.exe run_demo.php
 * ==============================================================================
 */

header('Content-Type: text/plain; charset=utf-8');

// Thiết lập kết nối PDO SQLite trong bộ nhớ (Zero Configuration - Chạy được ở mọi máy)
try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi khởi tạo CSDL: " . $e->getMessage() . "\n");
}

echo "================================================================================\n";
echo "  TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT\n";
echo "  BÀI 4: THIẾT KẾ CSDL MYSQL & THỰC THI TRUY VẤN SQL VỚI DỮ LIỆU TIẾNG VIỆT\n";
echo "  SINH VIÊN: TRẦN THỤC LINH | MÃ SINH VIÊN: 224001729 | LỚP: TOÁN ỨNG DỤNG D2024\n";
echo "  PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY (QUẢN TRỊ TÀI KHOẢN & BẢO MẬT)\n";
echo "================================================================================\n\n";

// -----------------------------------------------------------------------------
// 1. TẠO CẤU TRÚC BẢNG (SCHEMA DDL)
// -----------------------------------------------------------------------------
echo "[1/3] Đang khởi tạo Schema cấu trúc bảng CSDL phân hệ...\n";

$pdo->exec("
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    description TEXT,
    default_priority_code TEXT NOT NULL DEFAULT 'MED',
    sla_hours NUMERIC NOT NULL DEFAULT 4.0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    cohort TEXT NOT NULL DEFAULT 'D2024',
    faculty TEXT NOT NULL DEFAULT 'Khoa: Toán-CNTT',
    academic_year TEXT NOT NULL DEFAULT '2024-2028',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'requester',
    user_type TEXT NOT NULL DEFAULT 'student',
    department TEXT,
    phone TEXT,
    class_id INTEGER,
    cohort TEXT DEFAULT 'D2024',
    academic_status TEXT NOT NULL DEFAULT 'studying',
    specialty_category_id INTEGER,
    max_capacity INTEGER NOT NULL DEFAULT 5,
    active_tickets_count INTEGER NOT NULL DEFAULT 0,
    is_on_duty INTEGER NOT NULL DEFAULT 1,
    failed_attempts INTEGER NOT NULL DEFAULT 0,
    locked_until DATETIME,
    is_active INTEGER NOT NULL DEFAULT 1,
    lock_reason TEXT NOT NULL DEFAULT 'none',
    is_first_login INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (specialty_category_id) REFERENCES categories(id)
);

CREATE TABLE password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    email TEXT NOT NULL,
    token_hash TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_code TEXT UNIQUE,
    requester_id INTEGER NOT NULL,
    assigned_to INTEGER,
    category_id INTEGER NOT NULL,
    priority_id INTEGER NOT NULL DEFAULT 1,
    is_guest_ticket INTEGER NOT NULL DEFAULT 0,
    contact_email TEXT,
    lookup_pin TEXT,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    system_affected TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'new',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    due_at DATETIME NOT NULL,
    FOREIGN KEY (requester_id) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
");
echo "      -> Cấu trúc bảng (users, password_resets, classes, categories, tickets) đã sẵn sàng.\n\n";

// -----------------------------------------------------------------------------
// 2. NẠP DỮ LIỆU SEED MẪU TIẾNG VIỆT
// -----------------------------------------------------------------------------
echo "[2/3] Đang nạp dữ liệu mẫu tiếng Việt chuẩn Trường Đại học Thủ Đô Hà Nội...\n";

$passHash = '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m'; // Mật khẩu mặc định: 123456

$pdo->exec("
INSERT INTO categories (id, code, name, description, sla_hours) VALUES
(1, 'CAT_SSO', 'Tài khoản & Xác thực SSO', 'Cấp lại mật khẩu, mở khóa tài khoản, định danh sinh viên', 2.0),
(2, 'CAT_MAIL', 'Email Trường & Microsoft 365', 'Kích hoạt Microsoft 365 A3, khôi phục hòm thư @daihocthudo.edu.vn', 4.0),
(3, 'CAT_QLDT', 'Cổng Quản lý Đào tạo (QLĐT)', 'Sự cố cổng đăng ký tín chỉ, tra cứu lịch thi, nhập điểm', 2.0),
(4, 'CAT_CANVAS', 'Hệ thống Học tập (Canvas & Teams)', 'Lỗi không thấy môn học, sự cố nộp bài tập lớn', 2.0),
(5, 'CAT_WIFI', 'Wi-Fi Trường & VPN Từ xa', 'Xác thực mạng THUDO-UNI, cấp phát IP Ký túc xá', 4.0);

INSERT INTO classes (id, class_code, name, cohort, faculty, academic_year) VALUES
(1, 'TUD-D2024', 'Lớp Toán Ứng Dụng D2024', 'D2024', 'Khoa: Toán-CNTT', '2024-2028'),
(2, 'CNTT-D2024', 'Lớp Công nghệ Thông tin D2024', 'D2024', 'Khoa: Toán-CNTT', '2024-2028'),
(3, 'TUD-D2020', 'Lớp Toán Ứng Dụng D2020 (Đã tốt nghiệp)', 'D2020', 'Khoa: Toán-CNTT', '2020-2024');

INSERT INTO users (id, user_code, name, email, password_hash, role, user_type, department, phone, class_id, cohort, academic_status, specialty_category_id, max_capacity, active_tickets_count, is_on_duty, is_active, lock_reason, is_first_login, failed_attempts, locked_until) VALUES
(1, 'ADM001', 'Quản Trị Viên Hệ Thống', 'admin@daihocthudo.edu.vn', '{$passHash}', 'admin', 'staff', 'Trung tâm CNTT & Truyền thông', '0912345678', NULL, NULL, 'studying', NULL, 10, 0, 1, 1, 'none', 0, 0, NULL),
(2, 'KTV001', 'KTV Nguyễn Văn An (Chuyên trách SSO)', 'ktv1@daihocthudo.edu.vn', '{$passHash}', 'agent', 'staff', 'Tổ Dịch vụ Định danh & Xác thực', '0911000001', NULL, NULL, 'studying', 1, 5, 1, 1, 1, 'none', 0, 0, NULL),
(3, 'KTV002', 'KTV Trần Thị Bích (Chuyên trách Mail & M365)', 'ktv2@daihocthudo.edu.vn', '{$passHash}', 'agent', 'staff', 'Tổ Hạ tầng Đám mây & Email', '0911000002', NULL, NULL, 'studying', 2, 5, 0, 1, 1, 'none', 0, 0, NULL),
(4, 'KTV003', 'KTV Lê Văn Cường (Chuyên trách Cổng QLĐT)', 'ktv3@daihocthudo.edu.vn', '{$passHash}', 'agent', 'staff', 'Tổ Phần mềm & CSDL Đào tạo', '0911000003', NULL, NULL, 'studying', 3, 5, 1, 1, 1, 'none', 0, 0, NULL),
(5, 'KTV004', 'KTV Phạm Hoàng Dũng (Chuyên trách Canvas LMS)', 'ktv4@daihocthudo.edu.vn', '{$passHash}', 'agent', 'staff', 'Tổ E-Learning & Lớp học số', '0911000004', NULL, NULL, 'studying', 4, 5, 2, 1, 1, 'none', 0, 0, NULL),
(6, 'KTV005', 'KTV Vũ Thị Én (Chuyên trách Mạng & Wi-Fi)', 'ktv5@daihocthudo.edu.vn', '{$passHash}', 'agent', 'staff', 'Tổ Hạ tầng Mạng & Viễn thông', '0911000005', NULL, NULL, 'studying', 5, 5, 0, 1, 1, 'none', 0, 0, NULL),
(7, 'GV001', 'TS. Nguyễn Văn Giảng', 'nguyenvangiang@daihocthudo.edu.vn', '{$passHash}', 'requester', 'lecturer', 'Khoa: Toán-CNTT', '0988776601', NULL, NULL, 'studying', NULL, 5, 0, 1, 1, 'none', 0, 0, NULL),
(13, '224001736', 'Phạm Thị Thanh Ngân', '224001736@daihocthudo.edu.vn', '{$passHash}', 'requester', 'student', 'Khoa: Toán-CNTT - Trưởng nhóm', '0901234501', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),
(14, '224001729', 'Trần Thục Linh', '224001729@daihocthudo.edu.vn', '{$passHash}', 'requester', 'student', 'Khoa: Toán-CNTT (Toán Ứng Dụng D2024)', '0901234502', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 2, NULL),
(15, '224001737', 'Lê Hà My', '224001737@daihocthudo.edu.vn', '{$passHash}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234503', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),
(16, '224001742', 'Nguyễn Ánh Nhi', '224001742@daihocthudo.edu.vn', '{$passHash}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234504', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),
(17, '224001746', 'Phan Công Thành', '224001746@daihocthudo.edu.vn', '{$passHash}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234505', 1, 'D2024', 'studying', NULL, 5, 0, 1, 1, 'none', 1, 0, NULL),
(18, '220001001', 'Nguyễn Cựu Sinh Viên (D2020 Tốt nghiệp)', '220001001@daihocthudo.edu.vn', '{$passHash}', 'requester', 'student', 'Khoa: Toán-CNTT', '0909999001', 3, 'D2020', 'graduated', NULL, 5, 0, 1, 1, 'graduated', 0, 0, NULL),
(19, '224001999', 'Hoàng Văn Kỷ Luật (TUD D2024 Buộc Thôi Học)', '224001999@daihocthudo.edu.vn', '{$passHash}', 'requester', 'student', 'Khoa: Toán-CNTT', '0999888777', 1, 'D2024', 'expelled', NULL, 5, 0, 1, 0, 'expelled', 0, 5, '2026-10-24 16:00:00');

INSERT INTO password_resets (id, user_id, email, token_hash, expires_at) VALUES
(1, 14, '224001729@daihocthudo.edu.vn', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', datetime('now', '+15 minutes')),
(2, 13, '224001736@daihocthudo.edu.vn', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', datetime('now', '-30 minutes'));

INSERT INTO tickets (id, ticket_code, requester_id, assigned_to, category_id, priority_id, is_guest_ticket, contact_email, lookup_pin, title, description, system_affected, status, created_at, due_at) VALUES
(1, 'HD-2026-00001', 1, 5, 4, 1, 0, NULL, NULL, '[SỰ CỐ DIỆN RỘNG] Hiệu chỉnh lịch học trùng ngày nghỉ lễ trên Canvas LMS', 'KTV cấu hình sinh lịch học tự động đầu kỳ chưa trừ ngày nghỉ lễ 30/4 - 1/5 và 2/9.', 'Canvas LMS', 'in_progress', '2026-10-23 08:00:00', '2026-10-23 10:00:00'),
(2, 'HD-2026-00002', 13, 5, 4, 1, 0, NULL, NULL, 'Lịch nộp bài tập lớn môn Lập trình Web lớp Toán Ứng Dụng D2024 rơi vào ngày 30/4', 'Hạn chốt nộp bài rơi đúng ngày 30/4 là ngày nghỉ lễ toàn quốc.', 'Canvas LMS', 'in_progress', '2026-10-23 08:15:00', '2026-10-23 10:00:00'),
(3, 'HD-2026-00003', 14, 2, 1, 3, 1, 'tranlinh.personal@gmail.com', '4589', 'Tài khoản đăng nhập portal bị khóa cứng sau 5 lần nhập sai', 'Em gõ nhầm mật khẩu cũ 5 lần liên tiếp thì tài khoản bị khóa cứng. Em gửi ảnh chụp thẻ sinh viên lớp Toán Ứng Dụng D2024 để KTV xác minh mở khóa.', 'SSO', 'in_progress', '2026-10-23 08:30:00', '2026-10-23 10:30:00'),
(4, 'HD-2026-00004', 7, 4, 3, 1, 0, NULL, NULL, '[KHẨN CẤP] Cần mở quyền nhập điểm bổ sung học phần Lập trình Web Lớp Toán Ứng Dụng D2024', 'Cổng nhập điểm QLĐT đã quá hạn 12h, tôi đã có giấy phê duyệt của Phòng Đào tạo.', 'QLĐT', 'resolved', '2026-10-22 14:00:00', '2026-10-22 14:30:00'),
(5, 'HD-2026-00005', 17, 6, 5, 3, 0, NULL, NULL, 'Không xác thực được mạng Wi-Fi THUDO-UNI tại KTX Nhà B', 'Máy tính của em báo lỗi Can not connect to this network.', 'Wi-Fi', 'closed', '2026-10-21 09:00:00', '2026-10-21 13:00:00');

");
echo "      -> Đã nạp thành công dữ liệu mẫu cho sinh viên, KTV, giảng viên và yêu cầu SSPR.\n\n";

// -----------------------------------------------------------------------------
// 3. THỰC THI VÀ XUẤT BẢNG KẾT QUẢ CÁC TRUY VẤN
// -----------------------------------------------------------------------------
echo "[3/3] BẮT ĐẦU THỰC THI CÁC CÂU LỆNH TRUY VẤN SQL...\n\n";

function renderBoxTable($stmt, $title, $description = '') {
    echo "================================================================================\n";
    echo ">> $title\n";
    if ($description) {
        echo "   Mục đích: $description\n";
    }
    echo "--------------------------------------------------------------------------------\n";
    
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "(Không tìm thấy dòng dữ liệu nào khớp với điều kiện truy vấn)\n\n";
        return;
    }
    
    $headers = array_keys($rows[0]);
    $lengths = [];
    foreach ($headers as $h) {
        $lengths[$h] = mb_strlen($h, 'UTF-8');
    }
    foreach ($rows as $r) {
        foreach ($headers as $h) {
            $val = (string)($r[$h] ?? 'NULL');
            $len = mb_strlen($val, 'UTF-8');
            if ($len > $lengths[$h]) {
                $lengths[$h] = $len;
            }
        }
    }
    
    // Header
    foreach ($headers as $h) {
        echo str_pad($h, $lengths[$h] + 3);
    }
    echo "\n";
    foreach ($headers as $h) {
        echo str_repeat('-', $lengths[$h] + 2) . " ";
    }
    echo "\n";
    
    // Rows
    foreach ($rows as $r) {
        foreach ($headers as $h) {
            $val = (string)($r[$h] ?? 'NULL');
            $pad = $lengths[$h] + 3 - mb_strlen($val, 'UTF-8');
            echo $val . str_repeat(' ', max(1, $pad));
        }
        echo "\n";
    }
    echo "Tổng cộng: " . count($rows) . " bản ghi được trả về thành công.\n\n";
}

// Câu 1: WHERE
$q1 = "
SELECT 
    id, user_code, name, email, role, academic_status, is_active, lock_reason, failed_attempts, is_first_login,
    CASE 
        WHEN is_active = 0 THEN 'BỊ KHÓA CỨNG (Kỷ luật / Đuổi học)'
        WHEN locked_until > datetime('now', 'localtime') THEN 'BỊ KHÓA MỀM (Quá số lần đăng nhập)'
        ELSE 'HỢP LỆ (Sẵn sàng đăng nhập)'
    END AS trang_thai_tai_khoan
FROM users 
WHERE email = '224001729@daihocthudo.edu.vn' OR user_code = '224001729';
";
renderBoxTable($pdo->query($q1), "CÂU TRUY VẤN 1: Xác thực đăng nhập sinh viên Trần Thục Linh (Mã SV: 224001729)", "Kiểm tra tài khoản, cơ chế khóa mềm Brute-force và khóa cứng kỷ luật bằng WHERE");

// Câu 2: LEFT JOIN
$q2 = "
SELECT 
    u.id,
    u.user_code AS ma_dinh_danh,
    u.name AS ho_va_ten,
    u.email AS email_truong,
    u.role AS vai_tro,
    u.user_type AS loai_tai_khoan,
    COALESCE(c.class_code, 'N/A') AS ma_lop,
    COALESCE(c.name, 'Chưa xếp lớp') AS ten_lop_sinh_hoat,
    COALESCE(cat.name, 'Không chuyên trách') AS chuyen_mon_ktv
FROM users u
LEFT JOIN classes c ON c.id = u.class_id
LEFT JOIN categories cat ON cat.id = u.specialty_category_id
WHERE u.role IN ('requester', 'agent')
ORDER BY u.role DESC, u.id ASC
LIMIT 7;
";
renderBoxTable($pdo->query($q2), "CÂU TRUY VẤN 2: Hồ sơ người dùng kèm thông tin Lớp học & Chuyên môn KTV", "Dùng LEFT JOIN 2 bảng cha (classes, categories) và hàm COALESCE thay thế NULL");

// Câu 3: INNER JOIN & WHERE
$q3 = "
SELECT 
    pr.id AS token_id,
    u.user_code AS ma_sinh_vien,
    u.name AS ho_ten_sinh_vien,
    pr.email AS email_nhan_link,
    pr.token_hash AS ma_token_sha256,
    pr.expires_at AS thoi_diem_het_han,
    u.is_active AS tai_khoan_hoat_dong,
    u.academic_status AS trang_thai_hoc_vu
FROM password_resets pr
INNER JOIN users u ON u.id = pr.user_id
WHERE pr.expires_at > datetime('now') 
  AND u.is_active = 1 
  AND u.academic_status != 'expelled';
";
renderBoxTable($pdo->query($q3), "CÂU TRUY VẤN 3: Xác thực Token khôi phục mật khẩu SSPR còn hiệu lực", "Dùng INNER JOIN kết hợp WHERE kiểm tra hạn 15 phút và chặn tài khoản bị đình chỉ học");

// Câu 4: JOIN + GROUP BY + ORDER BY
$q4 = "
SELECT 
    u.id AS ktv_id,
    u.user_code AS ma_ktv,
    u.name AS ten_ky_thuat_vien,
    cat.name AS chuyen_mon_chinh,
    u.max_capacity AS tai_toi_da,
    COUNT(t.id) AS phieu_thuc_te_dang_xu_ly,
    (u.max_capacity - COUNT(t.id)) AS so_slot_tiep_nhan_con_lai
FROM users u
INNER JOIN categories cat ON cat.id = u.specialty_category_id
LEFT JOIN tickets t ON t.assigned_to = u.id AND t.status IN ('new', 'in_progress', 'waiting_requester')
WHERE u.role = 'agent' 
  AND u.is_active = 1 
  AND u.is_on_duty = 1
GROUP BY u.id, u.user_code, u.name, cat.name, u.max_capacity
ORDER BY phieu_thuc_te_dang_xu_ly ASC, so_slot_tiep_nhan_con_lai DESC;
";
renderBoxTable($pdo->query($q4), "CÂU TRUY VẤN 4: Phân tích Năng lực & Cân bằng tải Kỹ thuật viên tiếp nhận phiếu", "Dùng INNER JOIN, LEFT JOIN, GROUP BY và ORDER BY tìm KTV có ít việc nhất để phân công");

// Câu 5: GROUP BY + COUNT/CASE
$q5 = "
SELECT 
    u.cohort AS khoa_tuyen_sinh,
    COUNT(u.id) AS tong_sinh_vien,
    SUM(CASE WHEN u.academic_status = 'studying' THEN 1 ELSE 0 END) AS dang_hoc,
    SUM(CASE WHEN u.academic_status = 'graduated' THEN 1 ELSE 0 END) AS da_tot_nghiep,
    SUM(CASE WHEN u.academic_status = 'expelled' THEN 1 ELSE 0 END) AS bi_ky_luat_duoi_hoc
FROM users u
WHERE u.user_type = 'student'
GROUP BY u.cohort
ORDER BY u.cohort DESC;
";
renderBoxTable($pdo->query($q5), "CÂU TRUY VẤN 5: Thống kê cơ cấu Sinh viên theo Khóa đào tạo & Trạng thái học vụ", "Dùng GROUP BY và SUM(CASE WHEN ...) quản lý vòng đời tài khoản đầu kỳ và cuối khóa");

// Câu 6: JOIN & WHERE
$q6 = "
SELECT 
    t.ticket_code AS ma_phieu,
    req.name AS sinh_vien_gui,
    cat.name AS dich_vu_yeu_cau,
    t.title AS tieu_de_su_co,
    t.status AS trang_thai_xu_ly,
    COALESCE(ktv.name, 'Đang chờ phân công') AS ktv_tiep_nhan,
    t.created_at AS thoi_gian_gui_phieu
FROM tickets t
INNER JOIN users req ON req.id = t.requester_id
INNER JOIN categories cat ON cat.id = t.category_id
LEFT JOIN users ktv ON ktv.id = t.assigned_to
WHERE req.user_code = '224001729' OR t.is_guest_ticket = 1
ORDER BY t.created_at DESC;
";
renderBoxTable($pdo->query($q6), "CÂU TRUY VẤN 6: Tra cứu lịch sử phiếu hỗ trợ của SV Trần Thục Linh (224001729)", "Dùng INNER JOIN và LEFT JOIN tra cứu lịch sử xử lý và phiếu Guest Ticket mở khóa");

echo "================================================================================\n";
echo "  KẾT THÚC THỰC THI: TOÀN BỘ 6/6 TRUY VẤN ĐỀU CHẠY THÀNH CÔNG 100%!\n";
echo "  CSDL VÀ TRUY VẤN ĐÃ ĐƯỢC KIỂM ĐỊNH ĐẦY ĐỦ VỚI DỮ LIỆU TIẾNG VIỆT CÓ DẤU.\n";
echo "================================================================================\n";


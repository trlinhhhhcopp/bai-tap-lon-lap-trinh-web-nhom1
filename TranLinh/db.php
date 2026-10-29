<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
 * ------------------------------------------------------------------------------
 * PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
 * SINH VIÊN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729 - LỚP: TOÁN ỨNG DỤNG D2024
 * ------------------------------------------------------------------------------
 * TẬP TIN: TranLinh/db.php (Hàm kết nối PDO dùng chung theo Slide 3 & 4)
 * ==============================================================================
 */

// Bắt đầu Session phục vụ Flash Message khi chạy trên Web (Slide 2)
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    @session_start();
}

/**
 * Hàm khởi tạo và tái sử dụng kết nối PDO duy nhất (Singleton Pattern)
 * Tuân thủ nghiêm ngặt Slide 3 & 4:
 * - DSN cần host, database, charset=utf8mb4
 * - Bật PDO::ERRMODE_EXCEPTION để lỗi rõ trong môi trường phát triển
 * - Dùng PDO::FETCH_ASSOC để nhận mảng kết hợp dễ đọc
 * - Có cơ chế Fallback thông minh để chạy được ngay cả khi máy chưa bật MySQL
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // 1. Cấu hình kết nối MySQL chuẩn của Bài tập lớn nhóm 1
    $cfgFile  = __DIR__ . '/../config/database.php';
    $cfg      = file_exists($cfgFile) ? (require $cfgFile) : [];
    $host     = $cfg['host'] ?? '127.0.0.1';
    $port     = $cfg['port'] ?? 3306;
    $dbname   = $cfg['database'] ?? 'helpdesk_thudo';
    $username = $cfg['username'] ?? 'root';
    $password = $cfg['password'] ?? '';
    $charset  = $cfg['charset'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ném Exception khi có lỗi SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mặc định trả về mảng kết hợp
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Dùng Prepared Statement thực thụ của MySQL
    ];

    try {
        // Thử kết nối vào MySQL XAMPP
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // 2. Cơ chế Fallback dự phòng: Sử dụng SQLite CSDL cục bộ nếu MySQL chưa bật
        // Giúp Giảng viên hoặc Sinh viên chạy thử ngay lập tức (Zero-Configuration)
        $sqliteFile = __DIR__ . '/helpdesk_demo.sqlite';
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Tự động khởi tạo schema và seed nếu file SQLite mới tạo
        init_sqlite_if_needed($pdo);
        return $pdo;
    }
}

/**
 * Khởi tạo dữ liệu mẫu cho bộ nhớ SQLite dự phòng nếu MySQL chưa bật
 */
function init_sqlite_if_needed(PDO $pdo): void {
    $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
    if ($check) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS classes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            cohort TEXT NOT NULL DEFAULT 'D2024',
            faculty TEXT NOT NULL DEFAULT 'Khoa: Toán-CNTT'
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            sla_hours NUMERIC NOT NULL DEFAULT 4.0
        );

        CREATE TABLE IF NOT EXISTS users (
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
            locked_until TEXT,
            is_active INTEGER NOT NULL DEFAULT 1,
            lock_reason TEXT NOT NULL DEFAULT 'none',
            is_first_login INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Nạp dữ liệu mẫu lớp học & danh mục
    $pdo->exec("
        INSERT INTO classes (id, class_code, name, cohort) VALUES 
        (1, 'TUD-D2024', 'Lớp Toán Ứng Dụng D2024', 'D2024'),
        (2, 'CNTT-D2024', 'Lớp Công nghệ Thông tin D2024', 'D2024'),
        (3, 'TUD-D2020', 'Lớp Toán Ứng Dụng D2020 (Đã tốt nghiệp)', 'D2020');

        INSERT INTO categories (id, code, name, sla_hours) VALUES 
        (1, 'CAT_SSO', 'Tài khoản & Xác thực SSO', 2.0),
        (2, 'CAT_MAIL', 'Email Trường & Microsoft 365', 4.0),
        (3, 'CAT_QLDT', 'Cổng Quản lý Đào tạo (QLĐT)', 2.0),
        (4, 'CAT_CANVAS', 'Hệ thống Học tập (Canvas & Teams)', 2.0),
        (5, 'CAT_WIFI', 'Wi-Fi Trường & VPN Từ xa', 4.0);
    ");

    // Nạp dữ liệu người dùng ban đầu chuẩn Nhóm 1
    $bcrypt = '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m'; // 123456
    $pdo->exec("
        INSERT INTO users (id, user_code, name, email, password_hash, role, user_type, department, phone, class_id, cohort, academic_status, specialty_category_id, is_active, lock_reason, is_first_login) VALUES 
        (1, 'ADM001', 'Quản Trị Viên Hệ Thống', 'admin@daihocthudo.edu.vn', '{$bcrypt}', 'admin', 'staff', 'Trung tâm CNTT & Truyền thông', '0912345678', NULL, NULL, 'studying', NULL, 1, 'none', 0),
        (2, 'KTV001', 'KTV Nguyễn Văn An (Chuyên trách SSO)', 'ktv1@daihocthudo.edu.vn', '{$bcrypt}', 'agent', 'staff', 'Tổ Định danh & SSO', '0911000001', NULL, NULL, 'studying', 1, 1, 'none', 0),
        (7, 'GV001', 'TS. Nguyễn Văn Giảng', 'nguyenvangiang@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'lecturer', 'Khoa: Toán-CNTT', '0988776601', NULL, NULL, 'studying', NULL, 1, 'none', 0),
        (13, '224001736', 'Phạm Thị Thanh Ngân', '224001736@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT - Trưởng nhóm', '0901234501', 1, 'D2024', 'studying', NULL, 1, 'none', 1),
        (14, '224001729', 'Trần Thục Linh', '224001729@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234502', 1, 'D2024', 'studying', NULL, 1, 'none', 1),
        (15, '224001737', 'Lê Hà My', '224001737@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234503', 1, 'D2024', 'studying', NULL, 1, 'none', 1),
        (16, '224001742', 'Nguyễn Ánh Nhi', '224001742@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234504', 1, 'D2024', 'studying', NULL, 1, 'none', 1),
        (17, '224001746', 'Phan Công Thành', '224001746@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234505', 1, 'D2024', 'studying', NULL, 1, 'none', 1),
        (18, '220001001', 'Nguyễn Cựu Sinh Viên (D2020)', '220001001@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0909999001', 3, 'D2020', 'graduated', NULL, 1, 'graduated', 0),
        (19, '224001999', 'Hoàng Văn Kỷ Luật (Buộc thôi học)', '224001999@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0999888777', 1, 'D2024', 'expelled', NULL, 0, 'expelled', 0);
    ");
}

/**
 * ==============================================================================
 * TIỆN ÍCH FLASH MESSAGE (Theo Slide 2: POST-Redirect-GET và flash message)
 * ==============================================================================
 */

/**
 * Đặt một Flash Message vào Session
 * @param string $type success | danger | warning | info
 * @param string $message Nội dung thông báo
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Lấy ra Flash Message và tự động xóa khỏi Session (Chỉ hiển thị 1 lần duy nhất)
 * @return array|null
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

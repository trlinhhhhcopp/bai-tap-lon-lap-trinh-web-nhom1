<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static bool $isFallback = false;

    public static function isFallback(): bool {
        return self::$isFallback;
    }

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require __DIR__ . '/../../config/database.php';

        $ports     = array_unique([(int)($config['port'] ?? 3306), 3306, 3307]);
        $passwords = array_unique([$config['password'] ?? '', '', 'root', '1234', '123456', 'admin', 'nhi12345']);
        $connected = false;

        foreach ($ports as $port) {
            $dsn = "mysql:host={$config['host']};port={$port};dbname={$config['database']};charset={$config['charset']}";
            foreach ($passwords as $pwd) {
                try {
                    self::$instance = new PDO($dsn, $config['username'], $pwd, $config['options']);
                    self::$isFallback = false;
                    $connected = true;

                    // Đảm bảo các bảng và cột mở rộng luôn tự động tồn tại trên mọi máy tính trong nhóm
                    try {
                        self::$instance->exec("
                            CREATE TABLE IF NOT EXISTS `password_resets` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `user_id` int(11) NOT NULL,
                                `email` varchar(150) NOT NULL,
                                `token` varchar(255) NOT NULL,
                                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                `expires_at` timestamp NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `token` (`token`),
                                KEY `user_id` (`user_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                        ");
                    } catch (\PDOException $ex) {}

                    try { self::$instance->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL;"); } catch (\PDOException $ex) {}
                    try { self::$instance->exec("ALTER TABLE users ADD COLUMN academic_status ENUM('active','graduated','suspended','expelled') NOT NULL DEFAULT 'active';"); } catch (\PDOException $ex) {}
                    try { self::$instance->exec("ALTER TABLE users ADD COLUMN is_first_login TINYINT(1) NOT NULL DEFAULT 0;"); } catch (\PDOException $ex) {}
                    try { self::$instance->exec("ALTER TABLE users ADD COLUMN failed_attempts INT NOT NULL DEFAULT 0;"); } catch (\PDOException $ex) {}
                    try { self::$instance->exec("ALTER TABLE users ADD COLUMN locked_until DATETIME NULL;"); } catch (\PDOException $ex) {}
                    try { self::$instance->exec("ALTER TABLE users ADD COLUMN lock_reason VARCHAR(255) NULL;"); } catch (\PDOException $ex) {}
                    try { self::$instance->exec("ALTER TABLE tickets ADD COLUMN attachment_path VARCHAR(255) NULL;"); } catch (\PDOException $ex) {}
                    try { self::$instance->exec("ALTER TABLE tickets ADD COLUMN is_assigned_transfer TINYINT(1) NOT NULL DEFAULT 0;"); } catch (\PDOException $ex) {}

                    return self::$instance;
                } catch (PDOException $ex) {
                    // Thử mật khẩu / cổng tiếp theo
                }
            }
        }

        try {
            if (!$connected) {
                throw new PDOException("Không thể kết nối MySQL với các mật khẩu mặc định.");
            }
        } catch (PDOException $e) {
            // Đánh dấu đang chạy chế độ dự phòng SQLite
            self::$isFallback = true;

            // Fallback sang CSDL SQLite cục bộ nếu MySQL chưa bật
            $sqlitePath = __DIR__ . '/../../storage/helpdesk_local.sqlite';
            if (!is_dir(dirname($sqlitePath))) {
                mkdir(dirname($sqlitePath), 0777, true);
            }
            self::$instance = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            self::initLocalSqlite(self::$instance);
            return self::$instance;
        }
    }

    private static function initLocalSqlite(PDO $pdo): void {
        $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
        if ($check) {
            try {
                $pdo->exec("ALTER TABLE tickets ADD COLUMN attachment_path TEXT;");
            } catch (\PDOException $e) {}
            try {
                $pdo->exec("ALTER TABLE tickets ADD COLUMN closed_reason TEXT;");
            } catch (\PDOException $e) {}
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN avatar_path TEXT;");
            } catch (\PDOException $e) {}
            return;
        }

        $pdo->exec("
            CREATE TABLE classes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                class_code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                cohort TEXT NOT NULL DEFAULT 'D24',
                faculty TEXT NOT NULL DEFAULT 'Khoa Công nghệ Thông tin'
            );
            CREATE TABLE categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                sla_hours NUMERIC NOT NULL DEFAULT 4.0
            );
            CREATE TABLE priorities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                sla_factor NUMERIC NOT NULL DEFAULT 1.0
            );
            CREATE TABLE courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_code TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                credits INTEGER NOT NULL DEFAULT 3
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
                cohort TEXT DEFAULT 'D24',
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
            CREATE TABLE password_resets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                email TEXT NOT NULL,
                token_hash TEXT NOT NULL,
                expires_at TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_code TEXT UNIQUE,
                requester_id INTEGER NOT NULL,
                assigned_to INTEGER,
                category_id INTEGER NOT NULL,
                priority_id INTEGER NOT NULL DEFAULT 3,
                class_id INTEGER,
                course_id INTEGER,
                parent_ticket_id INTEGER,
                is_guest_ticket INTEGER NOT NULL DEFAULT 0,
                contact_email TEXT,
                lookup_pin TEXT,
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                system_affected TEXT NOT NULL,
                attachment_path TEXT,
                status TEXT NOT NULL DEFAULT 'new',
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                due_at TEXT
            );
            CREATE TABLE ticket_replies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                message TEXT NOT NULL,
                is_internal INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE ticket_ratings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER NOT NULL UNIQUE,
                user_id INTEGER NOT NULL,
                score INTEGER NOT NULL,
                comment TEXT,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $bcrypt = '$2y$10$ZjTmLmtE3Al3TL2y28Pnouxpc4hmVlUi00QI/lqChzmlbFDpa6K8m'; // 123456

        $pdo->exec("
            INSERT INTO classes (id, class_code, name, cohort) VALUES 
            (1, 'D24-CNTT1', 'Lớp Công nghệ Thông tin D24-1', 'D24'),
            (2, 'D24-CNTT2', 'Lớp Công nghệ Thông tin D24-2', 'D24'),
            (3, 'D20-CNTT1', 'Lớp CNTT D20-1 (Đã tốt nghiệp)', 'D20');

            INSERT INTO categories (id, code, name, sla_hours) VALUES 
            (1, 'CAT_SSO', 'Tài khoản & Xác thực SSO', 2.0),
            (2, 'CAT_MAIL', 'Email Trường & Microsoft 365', 4.0),
            (3, 'CAT_QLDT', 'Cổng Quản lý Đào tạo (QLĐT)', 2.0),
            (4, 'CAT_CANVAS', 'Hệ thống Học tập (Canvas & Teams)', 2.0),
            (5, 'CAT_WIFI', 'Wi-Fi Trường & VPN Từ xa', 4.0);

            INSERT INTO priorities (id, code, name, sla_factor) VALUES
            (1, 'URGENT', 'Khẩn cấp', 0.25),
            (2, 'HIGH', 'Cao', 0.50),
            (3, 'MED', 'Trung bình', 1.00),
            (4, 'LOW', 'Thấp', 1.50);

            INSERT INTO courses (id, course_code, name) VALUES 
            (1, 'WEB101', 'Lập trình Web & Ứng dụng'),
            (2, 'NET201', 'Quản trị Mạng & An toàn Hệ thống'),
            (3, 'DB102', 'Hệ Quản trị Cơ sở Dữ liệu');

            INSERT INTO users (id, user_code, name, email, password_hash, role, user_type, department, phone, class_id, cohort, academic_status, specialty_category_id, is_active, lock_reason, is_first_login) VALUES 
            (1, 'ADM001', 'Quản Trị Viên Hệ Thống', 'admin@daihocthudo.edu.vn', '{$bcrypt}', 'admin', 'staff', 'Trung tâm CNTT & Truyền thông', '0912345678', NULL, NULL, 'studying', NULL, 1, 'none', 0),
            (2, 'KTV001', 'KTV Nguyễn Văn An (Chuyên trách SSO)', 'ktv1@daihocthudo.edu.vn', '{$bcrypt}', 'agent', 'staff', 'Tổ Định danh & SSO', '0911000001', NULL, NULL, 'studying', 1, 1, 'none', 0),
            (7, 'GV001', 'TS. Nguyễn Văn Giảng', 'nguyenvangiang@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'lecturer', 'Khoa: Toán-CNTT', '0988776601', NULL, NULL, 'studying', NULL, 1, 'none', 0),
            (13, '224001736', 'Phạm Thị Thanh Ngân', '224001736@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT - Trưởng nhóm', '0901234501', 1, 'D24', 'studying', NULL, 1, 'none', 1),
            (14, '224001729', 'Trần Thục Linh', '224001729@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234502', 1, 'D24', 'studying', NULL, 1, 'none', 0),
            (15, '224001737', 'Lê Hà My', '224001737@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234503', 1, 'D24', 'studying', NULL, 1, 'none', 1),
            (16, '224001742', 'Nguyễn Ánh Nhi', '224001742@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234504', 1, 'D24', 'studying', NULL, 1, 'none', 1),
            (17, '224001746', 'Phan Công Thành', '224001746@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0901234505', 1, 'D24', 'studying', NULL, 1, 'none', 1),
            (18, '220001001', 'Nguyễn Cựu Sinh Viên (D2020)', '220001001@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0909999001', 3, 'D20', 'graduated', NULL, 1, 'graduated', 0),
            (19, '224001999', 'Hoàng Văn Kỷ Luật (Buộc thôi học)', '224001999@daihocthudo.edu.vn', '{$bcrypt}', 'requester', 'student', 'Khoa: Toán-CNTT', '0999888777', 1, 'D24', 'expelled', NULL, 0, 'expelled', 0);

            INSERT INTO tickets (id, ticket_code, requester_id, assigned_to, category_id, priority_id, title, description, system_affected, status, due_at) VALUES 
            (1, 'HD-2026-00001', 14, 2, 1, 3, 'Không đăng nhập được cổng một cửa SSO', 'Em nhập mật khẩu báo lỗi dù đã đổi hôm qua', 'Portal SSO', 'in_progress', '2026-10-28 17:00:00'),
            (2, 'HD-2026-00002', 13, 2, 4, 2, 'Lỗi nộp bài tập lớn Web trên Canvas', 'Em bấm nộp file zip báo lỗi timeout 504', 'Canvas LMS', 'new', '2026-10-27 12:00:00');
        ");
    }
}


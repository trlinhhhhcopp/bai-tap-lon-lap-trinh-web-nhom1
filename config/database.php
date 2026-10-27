<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * CẤU HÌNH KẾT NỐI CƠ SỞ DỮ LIỆU MYSQL PDO
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Hàm db(): PDO dùng chung cho các repository & script (Slide 4)
 */
if (!function_exists('db')) {
    function db(): PDO {
        static $pdo = null;
        if ($pdo !== null) {
            return $pdo;
        }

        if (class_exists('App\Core\Database')) {
            $pdo = \App\Core\Database::getConnection();
            return $pdo;
        }

        $host     = '127.0.0.1';
        $port     = 3306;
        $database = 'helpdesk_thudo';
        $username = 'root';
        $password = '';
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("<strong>Lỗi kết nối CSDL:</strong> " . htmlspecialchars($e->getMessage()));
        }

        return $pdo;
    }
}

/**
 * Helper thiết lập thông báo Flash (Slide 2 & 8)
 */
if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }
}

/**
 * Helper lấy và xóa thông báo Flash
 */
if (!function_exists('get_flash')) {
    function get_flash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

// Trả về mảng cấu hình cho App\Core\Database
return [
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => 3306,
    'database'  => 'helpdesk_thudo',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];

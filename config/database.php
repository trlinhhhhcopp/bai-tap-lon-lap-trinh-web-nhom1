<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * CẤU HÌNH KẾT NỐI CƠ SỞ DỮ LIỆU MYSQL PDO & PSR-4 AUTOLOADER
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Autoloader PSR-4 cho không gian tên App\...
 */
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

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

/**
 * Helper lấy URL gốc của ứng dụng (dùng chung cho mọi view & controller)
 */
if (!function_exists('get_app_base_url')) {
    function get_app_base_url(): string {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: dirname(__DIR__));
        
        if ($docRoot && str_starts_with($appRoot, $docRoot)) {
            $rel = substr($appRoot, strlen($docRoot));
            $base = '/' . trim(str_replace('\\', '/', $rel), '/') . '/';
            return str_replace('//', '/', $base);
        }
        
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $folder = 'bai-tap-lon-lap-trinh-web-nhom1';
        $pos = strpos($script, $folder);
        if ($pos !== false) {
            return substr($script, 0, $pos + strlen($folder)) . '/';
        }
        return '/';
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

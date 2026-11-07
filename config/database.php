<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * CẤU HÌNH KẾT NỐI CƠ SỞ DỮ LIỆU MYSQL PDO & PSR-4 AUTOLOADER
 * ==============================================================================
 */

ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
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
        $passwords = ['', '12345678', 'root'];

        $lastEx = null;
        foreach ($passwords as $pwd) {
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                $pdo = new PDO($dsn, $username, $pwd, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => true,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
                $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;");
                return $pdo;
            } catch (PDOException $e) {
                $lastEx = $e;
            }
        }
        die("<strong>Lỗi kết nối CSDL:</strong> " . htmlspecialchars($lastEx ? $lastEx->getMessage() : 'Không thể kết nối MySQL'));
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

/**
 * Helper xuất nhãn trạng thái chuẩn Pill bo tròn kèm chấm tròn ● (đồng bộ tất cả các role)
 */
if (!function_exists('render_status_badge')) {
    function render_status_badge(string $status, ?string $customLabel = null): string {
        $statusKey = strtolower(trim($status));
        $map = [
            'new' => [
                'label' => 'Chờ tiếp nhận',
                'class' => 'bg-sky-50 text-sky-700 border-sky-300',
                'dot'   => 'text-sky-500'
            ],
            'in_progress' => [
                'label' => 'Đang xử lý',
                'class' => 'bg-amber-50 text-amber-700 border-amber-300',
                'dot'   => 'text-amber-500'
            ],
            'waiting_requester' => [
                'label' => 'Chờ phản hồi',
                'class' => 'bg-purple-50 text-purple-700 border-purple-300',
                'dot'   => 'text-purple-500'
            ],
            'resolved' => [
                'label' => 'Đã giải quyết',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
                'dot'   => 'text-emerald-500'
            ],
            'closed' => [
                'label' => 'Đã đóng',
                'class' => 'bg-slate-100 text-slate-600 border-slate-300',
                'dot'   => 'text-slate-400'
            ],
            'cancelled' => [
                'label' => 'Đã hủy',
                'class' => 'bg-rose-50 text-rose-700 border-rose-300',
                'dot'   => 'text-rose-500'
            ],
        ];

        $cfg = $map[$statusKey] ?? [
            'label' => ucfirst($statusKey),
            'class' => 'bg-slate-50 text-slate-700 border-slate-300',
            'dot'   => 'text-slate-400'
        ];

        $text = $customLabel ?: $cfg['label'];
        return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ' . $cfg['class'] . '"><span class="' . $cfg['dot'] . '">●</span> ' . htmlspecialchars($text) . '</span>';
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
        PDO::ATTR_EMULATE_PREPARES   => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
];

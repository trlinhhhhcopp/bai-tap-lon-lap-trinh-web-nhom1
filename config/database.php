<?php
// ==============================================================================
// BÀI 5: KẾT NỐI CƠ SỞ DỮ LIỆU BẰNG PDO (SLIDE 3 & 4)
// DỰ ÁN: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - ĐH THỦ ĐÔ HÀ NỘI
// ==============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Hàm kết nối CSDL dùng chung (Singleton PDO Instance)
 * Đảm bảo 3 tiêu chí:
 * 1. DSN chứa charset=utf8mb4 hỗ trợ tiếng Việt có dấu
 * 2. PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION (Báo lỗi chi tiết)
 * 3. PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC (Trả về mảng kết hợp)
 */
function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host     = '127.0.0.1';
        $port     = '3306';
        $dbname   = 'helpdesk_thudo';
        $username = 'root';
        $password = ''; // XAMPP mặc định không có mật khẩu

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Dùng native prepared statements của MySQL
            ]);
        } catch (PDOException $e) {
            die("<strong>Lỗi kết nối CSDL:</strong> " . htmlspecialchars($e->getMessage()) . 
                "<br><em>Vui lòng kiểm tra MySQL trong XAMPP đã Bật (Start) và database 'helpdesk_thudo' đã được import.</em>");
        }
    }

    return $pdo;
}

/**
 * Helper thiết lập thông báo Flash (Slide 2 & 8)
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success' hoặc 'error'
        'message' => $message,
    ];
}

/**
 * Helper lấy và xóa thông báo Flash
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}


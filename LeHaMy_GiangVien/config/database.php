<?php
/**
 * Config riêng – Phần Lê Hà My (Giảng viên / course_assignments)
 * Không phụ thuộc config chung của nhóm
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('db')) {
    function db(): PDO
    {
        static $pdo = null;
        if ($pdo !== null) {
            return $pdo;
        }

        $host     = '127.0.0.1';
        $port     = 3306;
        $database = 'helpdesk_thudo';
        $username = 'root';
        $password = '1234';          // Đổi nếu máy bạn có mật khẩu MySQL
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        $passwords = ['', '1234', 'root'];
        $lastException = null;
        foreach ($passwords as $pwd) {
            try {
                $pdo = new PDO($dsn, $username, $pwd, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                return $pdo;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }
        die('<strong>Lỗi kết nối CSDL (LeHaMy):</strong> ' . htmlspecialchars($lastException->getMessage()));

        return $pdo;
    }
}
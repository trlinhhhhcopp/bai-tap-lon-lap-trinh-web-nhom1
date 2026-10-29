<?php
/**
 * Config riêng – Phần Nguyễn Ánh Nhi (ticket_ratings / CSAT)
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
        $password = 'nhi12345';          // Đổi nếu máy bạn có mật khẩu

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<strong>Lỗi kết nối CSDL (AnhNhi):</strong> ' . htmlspecialchars($e->getMessage()));
        }

        return $pdo;
    }
}
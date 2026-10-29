<?php
/**
 * Import CSDL helpdesk_thudo bằng PHP
 * Đặt tại: bai-tap-lon-lap-trinh-web-nhom1/import_db.php
 */

$host = '127.0.0.1';
$db   = 'helpdesk_thudo';
$user = 'root';
$pass = '1234';   // mật khẩu MySQL của bạn
$port = 3306;

/**
 * Chạy file SQL: bỏ comment, tách theo ; rồi exec từng câu
 */
function runSqlFile(PDO $pdo, string $file): void
{
    if (!file_exists($file)) {
        echo "<p style='color:red'>Không tìm thấy: $file</p>";
        return;
    }

    $sql = file_get_contents($file);

    // Bỏ comment dòng (-- ...)
    $sql = preg_replace('/--.*$/m', '', $sql);
    // Bỏ comment khối /* ... */
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Tách theo dấu ;
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $ok = 0;
    foreach ($statements as $stmt) {
        if ($stmt === '') continue;
        try {
            $pdo->exec($stmt);
            $ok++;
        } catch (PDOException $e) {
            // Bỏ qua lỗi "table already exists" / duplicate nếu chạy lại
            if (str_contains($e->getMessage(), 'already exists') ||
                str_contains($e->getMessage(), 'Duplicate')) {
                continue;
            }
            echo "<p style='color:orange'>Cảnh báo: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    echo "<p style='color:green'>Import thành công: " . basename($file) . " ($ok câu lệnh)</p>";
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    echo "<p>Đã tạo / chọn database <b>$db</b></p>";

    runSqlFile($pdo, __DIR__ . '/database/schema.sql');
    runSqlFile($pdo, __DIR__ . '/database/seed.sql');

    echo "<h3>Hoàn tất!</h3>";
    echo "<p><a href='LeHaMy_GiangVien/index.php'>→ Vào trang CRUD Phân công giảng dạy</a></p>";
    echo "<p><a href='LeHaMy_GiangVien/check_data.php'>→ Kiểm tra số liệu</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'><b>Lỗi:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
}
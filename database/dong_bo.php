<?php
/**
 * SCRIPT ĐỒNG BỘ DATABASE TỰ ĐỘNG - CHUẨN UTF-8 TIẾNG VIỆT
 * Nhóm 1 - Bài tập lớn Lập trình Web
 */

ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Asia/Ho_Chi_Minh');

echo "----------------------------------------------------------------------\n";
echo " Dang ket noi toi MySQL va kiem tra cong/mat khau...\n";
echo "----------------------------------------------------------------------\n";

$host = '127.0.0.1';
$ports = [3306, 3307];
$passwords = ['', 'root', '12345678', '1234', '123456', 'admin', 'nhi12345'];

$pdo = null;
$connectedPort = null;
$connectedPwd = null;

foreach ($ports as $port) {
    foreach ($passwords as $pwd) {
        try {
            $testPdo = new PDO("mysql:host={$host};port={$port}", 'root', $pwd, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
            $pdo = $testPdo;
            $connectedPort = $port;
            $connectedPwd = $pwd;
            break 2;
        } catch (\Throwable $e) {}
    }
}

if (!$pdo) {
    echo "❌ LOI: Khong the ket noi toi MySQL tren cong 3306 hoac 3307.\n";
    echo "👉 Vui long kiem tra XAMPP da bat 'MySQL' (nut Start thanh mau xanh) chua!\n";
    exit(1);
}

echo "✅ Ket noi MySQL thanh cong (Port: {$connectedPort}, Password: " . ($connectedPwd === '' ? '[Trong]' : '***') . ")\n";

$sqlFile = __DIR__ . '/database_full_nhom1.sql';
if (!file_exists($sqlFile)) {
    echo "❌ LOI: Khong tim thay tep database/database_full_nhom1.sql!\n";
    exit(1);
}

echo " Dang nap tep CSDL chuan UTF-8 Tieng Viet vao MySQL...\n";

// Sử dụng mysql.exe nếu có
$mysqlExe = null;
$potentialPaths = [
    'C:/xampp/mysql/bin/mysql.exe',
    'C:/New folder/mysql/bin/mysql.exe',
    'D:/xampp/mysql/bin/mysql.exe',
    'E:/xampp/mysql/bin/mysql.exe',
];
foreach ($potentialPaths as $p) {
    if (file_exists($p)) {
        $mysqlExe = $p;
        break;
    }
}

$success = false;

if ($mysqlExe) {
    $pwdArg = $connectedPwd !== '' ? " -p\"{$connectedPwd}\"" : "";
    $cmd = "\"{$mysqlExe}\" --default-character-set=utf8mb4 -h {$host} -P {$connectedPort} -u root{$pwdArg} < \"{$sqlFile}\"";
    system($cmd, $ret);
    if ($ret === 0) {
        $success = true;
    }
}

if (!$success) {
    // Nạp trực tiếp qua PDO
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `helpdesk_thudo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `helpdesk_thudo`;");
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;");

        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        $success = true;
    } catch (\Throwable $e) {
        echo "❌ Loi PDO: " . $e->getMessage() . "\n";
    }
}

if ($success) {
    // Kiểm tra số lượng bản ghi
    $checkPdo = new PDO("mysql:host={$host};port={$connectedPort};dbname=helpdesk_thudo;charset=utf8mb4", 'root', $connectedPwd);
    $userCount = $checkPdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $ticketCount = $checkPdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
    $memberName = $checkPdo->query("SELECT name FROM users WHERE id = 13")->fetchColumn();

    echo "----------------------------------------------------------------------\n";
    echo "🎉 DONG BO DATABASE THANH CONG 100%!\n";
    echo "   - Tong so nguoi dung: {$userCount} tai khoan (Day du ca 5 thanh vien nhom)\n";
    echo "   - Kiem tra ten Tieng Viet: '{$memberName}' (Khong con bi loi phong chu)\n";
    echo "   - Tong so phieu su co: {$ticketCount} phieu (Bao gom day du phieu moi nhat)\n";
    echo "----------------------------------------------------------------------\n";
    echo "Bay gio ban co the mo trinh duyet va truy cap web binh thuong!\n";
} else {
    echo "❌ Co loi xay ra trong qua trinh nap CSDL.\n";
    exit(1);
}

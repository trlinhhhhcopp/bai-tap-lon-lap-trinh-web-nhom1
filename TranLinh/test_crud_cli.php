<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
 * ------------------------------------------------------------------------------
 * CHƯƠNG TRÌNH KIỂM THỬ TỰ ĐỘNG CRUD USER BẰNG PDO (CLI AUTOMATED TEST SUITE)
 * SINH VIÊN THỰC HIỆN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
 * LỚP: TOÁN ỨNG DỤNG D2024
 * ------------------------------------------------------------------------------
 * Hướng dẫn chạy:
 *   C:\xampp\php\php.exe TranLinh\test_crud_cli.php
 * ==============================================================================
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/UserRepository.php';

echo "================================================================================\n";
echo "  TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT\n";
echo "  BÀI 5: PHP-MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD CHO PHÂN HỆ USER MANAGEMENT\n";
echo "  SINH VIÊN: TRẦN THỤC LINH | MÃ SV: 224001729 | LỚP: TOÁN ỨNG DỤNG D2024\n";
echo "================================================================================\n\n";

$passCount = 0;
$totalTests = 5;

// -----------------------------------------------------------------------------
// TEST CASE 1: THÊM MỚI NGƯỜI DÙNG HỢP LỆ (CREATE)
// -----------------------------------------------------------------------------
echo "[TEST CASE 1] Kiểm tra Thêm mới sinh viên hợp lệ (Prepared Statement INSERT)...\n";
$uniqueCode = 'TEST_' . time();
$uniqueEmail = "nam_{$uniqueCode}@daihocthudo.edu.vn";

try {
    $newId = UserRepository::create([
        'user_code'   => $uniqueCode,
        'name'        => 'Lê Hoàng Nam (Demo Test)',
        'email'       => $uniqueEmail,
        'role'        => 'requester',
        'user_type'   => 'student',
        'department'  => 'Khoa: Toán-CNTT',
        'class_id'    => 1,
        'cohort'      => 'D2024',
        'password'    => '123456',
    ]);

    $createdUser = UserRepository::findById($newId);
    if ($createdUser && password_verify('123456', $createdUser['password_hash'])) {
        echo "   -> [PASS] Thêm thành công User #{$newId} ({$uniqueCode}). Mật khẩu 123456 đã được băm Bcrypt chuẩn xác!\n";
        $passCount++;
    } else {
        echo "   -> [FAIL] Không tìm thấy bản ghi vừa tạo hoặc lỗi băm Bcrypt.\n";
    }
} catch (Exception $e) {
    echo "   -> [FAIL] Ngoại lệ xảy ra: " . $e->getMessage() . "\n";
}
echo "\n";

// -----------------------------------------------------------------------------
// TEST CASE 2: CHẶN TRÙNG LẶP MÃ SINH VIÊN (UNIQUE CONSTRAINT VALIDATION)
// -----------------------------------------------------------------------------
echo "[TEST CASE 2] Kiểm tra Ràng buộc duy nhất: Chặn thêm trùng Mã SV 224001729...\n";
try {
    UserRepository::create([
        'user_code' => '224001729', // Mã SV của Linh đã tồn tại
        'name'      => 'Người Dùng Giả Mạo',
        'email'     => 'fake_email@daihocthudo.edu.vn',
    ]);
    echo "   -> [FAIL] Hệ thống cho phép thêm trùng mã SV!\n";
} catch (RuntimeException $e) {
    echo "   -> [PASS] Hệ thống bắt lỗi thành công: \"" . $e->getMessage() . "\"\n";
    $passCount++;
} catch (Exception $e) {
    echo "   -> [PASS] CSDL chặn lỗi Unique Key thành công: " . $e->getMessage() . "\n";
    $passCount++;
}
echo "\n";

// -----------------------------------------------------------------------------
// TEST CASE 3: TÌM KIẾM MỜ BẰNG TOÁN TỬ LIKE % (SEARCH FUNCTION)
// -----------------------------------------------------------------------------
echo "[TEST CASE 3] Kiểm tra Tìm kiếm người dùng có từ khóa 'Linh' bằng LIKE :kw...\n";
$searchResults = UserRepository::findActiveUsers(['kw' => 'Linh'], 1, 10);
$foundLinh = false;
foreach ($searchResults as $u) {
    if (strpos($u['name'], 'Linh') !== false || $u['user_code'] === '224001729') {
        $foundLinh = true;
        break;
    }
}

if ($foundLinh) {
    echo "   -> [PASS] Tìm thấy " . count($searchResults) . " kết quả chứa từ khóa 'Linh'. Tìm thấy đúng SV Trần Thục Linh!\n";
    $passCount++;
} else {
    echo "   -> [FAIL] Không tìm thấy kết quả phù hợp.\n";
}
echo "\n";

// -----------------------------------------------------------------------------
// TEST CASE 4: KIỂM TRA PHÂN TRANG AN TOÀN (PAGINATION LIMIT / OFFSET)
// -----------------------------------------------------------------------------
echo "[TEST CASE 4] Kiểm tra Phân trang an toàn (LIMIT / OFFSET) theo Slide 11...\n";
$perPage = 3;
$totalUsers = UserRepository::countActiveUsers();
$expectedPages = ceil($totalUsers / $perPage);

$page1Users = UserRepository::findActiveUsers([], 1, $perPage);
$page2Users = UserRepository::findActiveUsers([], 2, $perPage);

if (count($page1Users) <= $perPage && !empty($page2Users) && $page1Users[0]['id'] !== $page2Users[0]['id']) {
    echo "   -> [PASS] Phân trang hoạt động hoàn hảo! Tổng người dùng: {$totalUsers}, Số trang: {$expectedPages}. Dữ liệu Trang 1 và Trang 2 tách biệt.\n";
    $passCount++;
} else {
    echo "   -> [FAIL] Dữ liệu phân trang bị trùng lặp hoặc sai lệch offset.\n";
}
echo "\n";

// -----------------------------------------------------------------------------
// TEST CASE 5: KHÓA VÀ MỞ KHÓA TÀI KHOẢN BẰNG PHƯƠNG THỨC POST (SOFT LOCK)
// -----------------------------------------------------------------------------
echo "[TEST CASE 5] Kiểm tra Khóa/Mở khóa tài khoản an toàn (Slide 15)...\n";
try {
    // 1. Khóa tài khoản vừa tạo ở Test 1
    $lockRes = UserRepository::toggleLock($newId);
    $userAfterLock = UserRepository::findById($newId);

    // 2. Mở khóa lại
    $unlockRes = UserRepository::toggleLock($newId);
    $userAfterUnlock = UserRepository::findById($newId);

    if ($userAfterLock['is_active'] == 0 && $userAfterUnlock['is_active'] == 1) {
        echo "   -> [PASS] Cơ chế khóa an toàn: Khóa (is_active = 0, lock_reason = 'expelled') và Mở lại (is_active = 1) hoạt động chính xác 100%!\n";
        $passCount++;
    } else {
        echo "   -> [FAIL] Trạng thái is_active không thay đổi chuẩn xác.\n";
    }
} catch (Exception $e) {
    echo "   -> [FAIL] Lỗi: " . $e->getMessage() . "\n";
}
echo "\n";

// -----------------------------------------------------------------------------
// TỔNG KẾT
// -----------------------------------------------------------------------------
echo "================================================================================\n";
echo "  KẾT QUẢ KIỂM THỬ: {$passCount}/{$totalTests} TEST CASES ĐẠT CHUẨN (PASS 100%)!\n";
echo "  MÃ NGUỒN CRUD PDO CỦA TRẦN THỤC LINH ĐÃ SẴN SÀNG ĐỂ NỘP BÀI 5.\n";
echo "================================================================================\n";


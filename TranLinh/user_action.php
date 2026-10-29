<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Bạn phải đăng nhập với vai trò Quản trị viên (Admin) để truy cập chức năng này!'
    ];
    header('Location: ../index.php');
    exit;
}
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
 * ------------------------------------------------------------------------------
 * PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
 * SINH VIÊN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
 * ------------------------------------------------------------------------------
 * TẬP TIN: TranLinh/user_action.php (Bộ xử lý POST - Áp dụng POST-Redirect-GET & Flash message)
 * Tuân thủ nghiêm ngặt Slide 2, Slide 8 & Slide 15 ("Xóa/khóa bằng POST")
 * ==============================================================================
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/UserRepository.php';

// Chỉ chấp nhận phương thức POST (Bảo vệ an toàn dữ liệu, chống tấn công qua link GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Phương thức yêu cầu không hợp lệ!');
    header('Location: users.php');
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ==============================================================================
        // 1. THÊM MỚI NGƯỜI DÙNG (CREATE)
        // ==============================================================================
        case 'create':
            $userCode   = trim($_POST['user_code'] ?? '');
            $name       = trim($_POST['name'] ?? '');
            $email      = trim($_POST['email'] ?? '');
            $role       = $_POST['role'] ?? 'requester';
            $userType   = $_POST['user_type'] ?? 'student';
            $department = trim($_POST['department'] ?? 'Khoa: Toán-CNTT');
            $phone      = trim($_POST['phone'] ?? '');
            $classId    = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
            $cohort     = trim($_POST['cohort'] ?? 'D2024');

            // Kiểm tra định dạng email trường học
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Địa chỉ email không đúng định dạng!");
            }

            $newId = UserRepository::create([
                'user_code'   => $userCode,
                'name'        => $name,
                'email'       => $email,
                'role'        => $role,
                'user_type'   => $userType,
                'department'  => $department,
                'phone'       => $phone,
                'class_id'    => $classId,
                'cohort'      => $cohort,
                'password'    => '123456', // Khởi tạo mật khẩu mặc định 123456
            ]);

            set_flash('success', "Thêm mới người dùng [{$userCode} - {$name}] thành công! Mật khẩu khởi tạo: 123456.");
            break;

        // ==============================================================================
        // 2. CẬP NHẬT HỒ SƠ NGƯỜI DÙNG (UPDATE)
        // ==============================================================================
        case 'update':
            $id         = (int)($_POST['id'] ?? 0);
            $name       = trim($_POST['name'] ?? '');
            $email      = trim($_POST['email'] ?? '');
            $phone      = trim($_POST['phone'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $classId    = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;

            if ($id <= 0) {
                throw new InvalidArgumentException("ID người dùng không hợp lệ!");
            }

            UserRepository::update($id, [
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone,
                'department' => $department,
                'class_id'   => $classId,
            ]);

            set_flash('success', "Cập nhật hồ sơ tài khoản #{$id} thành công!");
            break;

        // ==============================================================================
        // 3. KHÓA / MỞ KHÓA TÀI KHOẢN BẰNG POST (DELETE / SOFT LOCK - Slide 15)
        // ==============================================================================
        case 'toggle_lock':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException("ID người dùng không hợp lệ!");
            }

            $result = UserRepository::toggleLock($id);
            $type = ($result['is_active'] == 1) ? 'success' : 'warning';
            set_flash($type, "[Tài khoản #{$id}] " . $result['status_text']);
            break;

        default:
            set_flash('danger', 'Hành động không được hỗ trợ!');
            break;
    }

} catch (InvalidArgumentException $e) {
    set_flash('danger', 'Lỗi dữ liệu: ' . $e->getMessage());
} catch (RuntimeException $e) {
    set_flash('danger', 'Lỗi thực thi: ' . $e->getMessage());
} catch (Exception $e) {
    set_flash('danger', 'Lỗi hệ thống: ' . $e->getMessage());
}

// Áp dụng mô hình chuẩn POST-Redirect-GET (Slide 2 & 8)
header('Location: users.php');
exit;


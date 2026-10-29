<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Bắt buộc đăng nhập hệ thống
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

// Kiểm tra quyền hạn: Chỉ Admin hoặc KTV mới được quản lý ngày nghỉ lễ
$role = $_SESSION['user']['role'] ?? '';
if (!in_array($role, ['admin', 'agent'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Bạn không có quyền truy cập vào Phân hệ Quản trị Lịch nghỉ lễ!'];
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/Controllers/Admin/HolidayController.php';

$controllerName = $_GET['controller'] ?? 'holiday';
$action = $_GET['action'] ?? 'index';

if ($controllerName !== 'holiday') {
    http_response_code(404);
    exit('Không tìm thấy chức năng.');
}

$controller = new HolidayController();

$allowedActions = ['index', 'create', 'edit', 'delete'];

if (!in_array($action, $allowedActions, true)) {
    http_response_code(404);
    exit('Không tìm thấy thao tác.');
}

$controller->$action();

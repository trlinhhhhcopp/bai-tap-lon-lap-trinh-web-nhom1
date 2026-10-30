<?php
/**
 * Quản trị ngày nghỉ lễ / SLA Calendar
 * Phân hệ Quản trị viên (Admin)
 */
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

require_once __DIR__ . '/../app/Controllers/Admin/HolidayController.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'index');
$controller = new HolidayController();

match ($action) {
    'create' => $controller->create(),
    'edit'   => $controller->edit(),
    'delete' => $controller->delete(),
    default  => $controller->index(),
};

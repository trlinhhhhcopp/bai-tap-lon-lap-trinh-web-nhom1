<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * FRONT CONTROLLER DUY NHẤT: public/index.php
 * Điểm đón đầu mọi Request trong mô hình kiến trúc MVC
 * ==============================================================================
 */

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hỗ trợ PHP Built-in Server: nếu là file tĩnh có sẵn (css, js, ảnh) thì để server phục vụ trực tiếp
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if ($path !== '/' && file_exists(__DIR__ . $path)) {
        return false;
    }
}

// 1. Bộ nạp tự động PSR-4 cho không gian tên App\...
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\AdminMiddleware;
use App\Controllers\AuthController;
use App\Controllers\GuestTicketController;
use App\Controllers\Student\StudentDashboardController;
use App\Controllers\Admin\AdminUserController;

// 2. Khởi tạo Router và định tuyến
$router = new Router();

// --- ROUTE XÁC THỰC AUTH (TRẦN THỤC LINH PHỤ TRÁCH) ---
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Đổi mật khẩu bắt buộc ở lần đầu đăng nhập
$router->get('/first-login', [AuthController::class, 'showFirstLogin']);
$router->post('/first-login', [AuthController::class, 'changeFirstLogin']);

// Quên mật khẩu SSPR qua Email (15 phút)
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink']);
$router->post('/api/send-reset-email', [AuthController::class, 'apiSendResetEmail']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

// --- ROUTE CỨU HỘ TÀI KHOẢN KHI BỊ KHÓA (GUEST TICKET - TRẦN THỤC LINH) ---
$router->get('/guest/ticket', [GuestTicketController::class, 'showForm']);
$router->post('/guest/ticket', [GuestTicketController::class, 'submit']);
$router->get('/guest/lookup', [GuestTicketController::class, 'showLookup']);

// --- PHÂN HỆ CỔNG SINH VIÊN (STUDENT PORTAL - CÁCH LY THÔNG TIN) ---
$router->get('/student/dashboard', [StudentDashboardController::class, 'index'], [AuthMiddleware::class]);
$router->post('/student/tickets/create', [StudentDashboardController::class, 'createTicket'], [AuthMiddleware::class]);
$router->post('/student/tickets/cancel', [StudentDashboardController::class, 'cancelTicket'], [AuthMiddleware::class]);
$router->post('/student/avatar', [StudentDashboardController::class, 'updateAvatar'], [AuthMiddleware::class]);
$router->get('/tickets/attachment', [StudentDashboardController::class, 'downloadAttachment']);

// --- PHÂN HỆ QUẢN TRỊ VIÊN ADMIN (ADMIN CENTER - TRẦN THỤC LINH PHỤ TRÁCH USER CRUD) ---
$router->get('/admin/users', [AdminUserController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/users/detail', [AdminUserController::class, 'detail'], [AdminMiddleware::class]);
$router->post('/admin/users/create', [AdminUserController::class, 'create'], [AdminMiddleware::class]);
$router->post('/admin/users/update', [AdminUserController::class, 'update'], [AdminMiddleware::class]);
$router->post('/admin/users/toggle-lock', [AdminUserController::class, 'toggleLock'], [AdminMiddleware::class]);

// 3. Phân phối Request
$router->dispatch();


<?php
namespace App;

date_default_timezone_set('Asia/Ho_Chi_Minh');

// 1. PSR-4 Autoloader cho toàn bộ namespace App\...
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

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

// 2. Nạp cấu hình CSDL và hàm phiên làm việc dùng chung
require_once __DIR__ . '/../config/database.php';

// Nạp các Controller thành viên và tạo alias namespace chuẩn mực
require_once __DIR__ . '/Controllers/Lecturer/CourseAssignmentController.php';
require_once __DIR__ . '/Controllers/Lecturer/LecturerLessonController.php';
require_once __DIR__ . '/Controllers/Student/CourseController.php';
require_once __DIR__ . '/Controllers/Student/RatingController.php';

if (!class_exists('App\Controllers\Lecturer\CourseAssignmentController')) {
    class_alias('CourseAssignmentController', 'App\Controllers\Lecturer\CourseAssignmentController');
}
if (!class_exists('App\Controllers\Lecturer\LecturerLessonController')) {
    class_alias('LecturerLessonController', 'App\Controllers\Lecturer\LecturerLessonController');
}
if (!class_exists('App\Controllers\Student\CourseController')) {
    class_alias('CourseController', 'App\Controllers\Student\CourseController');
}
if (!class_exists('App\Controllers\Student\RatingController')) {
    class_alias('RatingController', 'App\Controllers\Student\RatingController');
}

use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\AdminMiddleware;
use App\Controllers\AuthController;
use App\Controllers\GuestTicketController;
use App\Controllers\TicketController;
use App\Controllers\Student\StudentDashboardController;
use App\Controllers\Student\CourseController;
use App\Controllers\Student\RatingController;
use App\Controllers\Agent\AgentDashboardController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminUserController;
use App\Controllers\Admin\HolidayController;
use App\Controllers\Lecturer\CourseAssignmentController;
use App\Controllers\Lecturer\LecturerLessonController;

/**
 * Khởi tạo và đăng ký toàn bộ Route cho hệ thống (Luồng 2 - Router MVC)
 */
function bootstrapRouter(): Router {
    $router = new Router();

    // =========================================================================
    // 1. XÁC THỰC DANH TÍNH & AN TOÀN BẢO MẬT (TRẦN THỤC LINH PHỤ TRÁCH)
    // =========================================================================
    $router->get('/', [AuthController::class, 'showLogin']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/logout', [AuthController::class, 'logout']);
    $router->get('/quick-login', [AuthController::class, 'quickLogin']);

    // Bắt buộc đổi mật khẩu lần đầu
    $router->get('/first-login', [AuthController::class, 'showFirstLogin']);
    $router->post('/first-login', [AuthController::class, 'changeFirstLogin']);

    // Quên mật khẩu SSPR token 15 phút qua Email
    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'sendResetLink']);
    $router->post('/api/send-reset-email', [AuthController::class, 'apiSendResetEmail']);
    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword']);

    // =========================================================================
    // 2. TRẠM CỨU HỘ TÀI KHOẢN KHÁCH (TRẦN THỤC LINH & NHÓM - WEBCAM + UPLOAD)
    // =========================================================================
    $router->get('/guest/ticket', [GuestTicketController::class, 'showForm']);
    $router->post('/guest/ticket', [GuestTicketController::class, 'submit']);
    $router->get('/guest/lookup', [GuestTicketController::class, 'showLookup']);

    // =========================================================================
    // 3. PHÂN HỆ CỔNG SINH VIÊN (NGUYỄN ÁNH NHI & NHÓM - WEBCAM + UPLOAD)
    // =========================================================================
    $router->get('/student/dashboard', [StudentDashboardController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/student/tickets/create', [StudentDashboardController::class, 'createTicket'], [AuthMiddleware::class]);
    $router->post('/student/tickets/cancel', [StudentDashboardController::class, 'cancelTicket'], [AuthMiddleware::class]);
    $router->post('/student/avatar', [StudentDashboardController::class, 'updateAvatar'], [AuthMiddleware::class]);
    $router->get('/tickets/attachment', [StudentDashboardController::class, 'downloadAttachment']);

    // Môn học & Bài tập sinh viên (Nguyễn Ánh Nhi)
    $router->get('/student/courses', [CourseController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/student/courses/detail', [CourseController::class, 'detail'], [AuthMiddleware::class]);
    $router->post('/student/courses/submit', [CourseController::class, 'submitAssignment'], [AuthMiddleware::class]);
    $router->get('/student/courses/delete-submission', [CourseController::class, 'deleteSubmission'], [AuthMiddleware::class]);
    $router->post('/student/courses/delete-submission', [CourseController::class, 'deleteSubmission'], [AuthMiddleware::class]);
    $router->get('/student/courses/lessons', [CourseController::class, 'apiLessons'], [AuthMiddleware::class]);

    // Đánh giá CSAT 5 sao (Nguyễn Ánh Nhi)
    $router->get('/student/ratings', [RatingController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/student/ratings/create', [RatingController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/student/ratings/create', [RatingController::class, 'create'], [AuthMiddleware::class]);
    $router->get('/student/ratings/edit', [RatingController::class, 'edit'], [AuthMiddleware::class]);
    $router->post('/student/ratings/edit', [RatingController::class, 'edit'], [AuthMiddleware::class]);
    $router->get('/student/ratings/delete', [RatingController::class, 'delete'], [AuthMiddleware::class]);
    $router->post('/student/ratings/delete', [RatingController::class, 'delete'], [AuthMiddleware::class]);

    // =========================================================================
    // 4. BÀN LÀM VIỆC KỸ THUẬT VIÊN (PHẠM THỊ THANH NGÂN PHỤ TRÁCH)
    // =========================================================================
    $router->get('/agent/workbench', [AgentDashboardController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/agent/dashboard', [AgentDashboardController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/agent/workbench', [AgentDashboardController::class, 'index'], [AuthMiddleware::class]);

    // =========================================================================
    // 5. QUẢN LÝ PHIẾU HỖ TRỢ ITIL (TOÀN NHÓM PHỐI HỢP)
    // =========================================================================
    $router->get('/tickets/show', [TicketController::class, 'show']);
    $router->post('/tickets/show', [TicketController::class, 'show']);
    $router->get('/tickets/create', [TicketController::class, 'create']);
    $router->post('/tickets/create', [TicketController::class, 'create']);
    $router->get('/tickets', [TicketController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/tickets/list', [TicketController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/tickets/list', [TicketController::class, 'index'], [AuthMiddleware::class]);

    // =========================================================================
    // 6. TRUNG TÂM ĐIỀU HÀNH ADMIN (PHAN CÔNG THÀNH & TRẦN THỤC LINH)
    // =========================================================================
    $router->get('/admin/dashboard', [AdminDashboardController::class, 'index'], [AdminMiddleware::class]);
    $router->get('/admin', [AdminDashboardController::class, 'index'], [AdminMiddleware::class]);

    // Quản lý người dùng (Trần Thục Linh)
    $router->get('/admin/users', [AdminUserController::class, 'index'], [AdminMiddleware::class]);
    $router->get('/admin/users/detail', [AdminUserController::class, 'detail'], [AdminMiddleware::class]);
    $router->post('/admin/users/create', [AdminUserController::class, 'create'], [AdminMiddleware::class]);
    $router->post('/admin/users/update', [AdminUserController::class, 'update'], [AdminMiddleware::class]);
    $router->post('/admin/users/toggle-lock', [AdminUserController::class, 'toggleLock'], [AdminMiddleware::class]);
    $router->post('/admin/users/toggle-duty', [AdminUserController::class, 'toggleDuty'], [AdminMiddleware::class]);

    // Quản lý ngày nghỉ lễ (Phan Công Thành)
    $router->get('/admin/holidays', [HolidayController::class, 'index'], [AdminMiddleware::class]);
    $router->get('/admin/holidays/create', [HolidayController::class, 'create'], [AdminMiddleware::class]);
    $router->post('/admin/holidays/create', [HolidayController::class, 'create'], [AdminMiddleware::class]);
    $router->get('/admin/holidays/edit', [HolidayController::class, 'edit'], [AdminMiddleware::class]);
    $router->post('/admin/holidays/edit', [HolidayController::class, 'edit'], [AdminMiddleware::class]);
    $router->post('/admin/holidays/delete', [HolidayController::class, 'delete'], [AdminMiddleware::class]);

    // =========================================================================
    // 7. CỔNG HỌC PHẦN GIẢNG VIÊN (LÊ HÀ MY PHỤ TRÁCH)
    // =========================================================================
    $router->get('/lecturer/dashboard', [CourseAssignmentController::class, 'index']);
    $router->get('/lecturer/assignments', [CourseAssignmentController::class, 'index']);
    $router->get('/lecturer/assignments/create', [CourseAssignmentController::class, 'create']);
    $router->post('/lecturer/assignments/create', [CourseAssignmentController::class, 'create']);
    $router->get('/lecturer/assignments/edit', [CourseAssignmentController::class, 'edit']);
    $router->post('/lecturer/assignments/edit', [CourseAssignmentController::class, 'edit']);
    $router->get('/lecturer/lessons', [LecturerLessonController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/lecturer/lessons/create', [LecturerLessonController::class, 'create'], [AuthMiddleware::class]);
    $router->post('/lecturer/lessons/update', [LecturerLessonController::class, 'update'], [AuthMiddleware::class]);
    $router->post('/lecturer/lessons/grade', [LecturerLessonController::class, 'grade'], [AuthMiddleware::class]);
    $router->get('/lecturer/lessons/preview', [LecturerLessonController::class, 'preview'], [AuthMiddleware::class]);
    $router->post('/lecturer/lessons/delete', [LecturerLessonController::class, 'delete'], [AuthMiddleware::class]);
    $router->get('/lecturer/lessons/delete', [LecturerLessonController::class, 'delete'], [AuthMiddleware::class]);

    return $router;
}

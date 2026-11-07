<?php
namespace App\Middlewares;

use App\Core\Session;

class AdminMiddleware {
    public function handle(): void {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/');
        $basePath = ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir;

        if (!Session::check()) {
            Session::setFlash('warning', 'Vui lòng đăng nhập!');
            header('Location: ' . $basePath . '/login');
            exit;
        }

        $user = Session::user();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            die("<h2 style='text-align:center;color:red;margin-top:50px;'>403 Forbidden - Bạn không có quyền truy cập khu vực Quản trị viên (Admin)!</h2>");
        }
    }
}

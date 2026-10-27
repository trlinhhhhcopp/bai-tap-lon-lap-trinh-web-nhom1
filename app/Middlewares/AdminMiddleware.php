<?php
namespace App\Middlewares;

use App\Core\Session;

class AdminMiddleware {
    public function handle(): void {
        if (!Session::check()) {
            Session::setFlash('warning', 'Vui lòng đăng nhập!');
            header('Location: /login');
            exit;
        }

        $user = Session::user();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            die("<h2 style='text-align:center;color:red;margin-top:50px;'>403 Forbidden - Bạn không có quyền truy cập khu vực Quản trị viên (Admin)!</h2><p style='text-align:center;'><a href='/'>Quay lại</a></p>");
        }
    }
}


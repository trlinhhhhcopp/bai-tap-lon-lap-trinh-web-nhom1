<?php
namespace App\Middlewares;

use App\Core\Session;

class AuthMiddleware {
    public function handle(): void {
        if (!Session::check()) {
            Session::setFlash('warning', 'Vui lòng đăng nhập để truy cập hệ thống!');
            header('Location: /login');
            exit;
        }

        // Bắt buộc đổi mật khẩu ở lần đăng nhập đầu tiên
        $user = Session::user();
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($user && !empty($user['is_first_login']) && $user['is_first_login'] == 1) {
            if ($uri !== '/first-login' && $uri !== '/logout') {
                Session::setFlash('warning', 'Tài khoản mới: Bạn bắt buộc phải đổi mật khẩu ở lần đầu đăng nhập!');
                header('Location: /first-login');
                exit;
            }
        }
    }
}


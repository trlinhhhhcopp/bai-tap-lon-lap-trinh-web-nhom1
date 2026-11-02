<?php
namespace App\Middlewares;

use App\Core\Session;

class AuthMiddleware {
    public function handle(): void {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/');
        $basePath = ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir;
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($scriptName !== '' && str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        } elseif ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . ltrim($uri, '/');

        if (!Session::check()) {
            Session::setFlash('warning', 'Vui lòng đăng nhập để truy cập hệ thống!');
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Bắt buộc đổi mật khẩu ở lần đăng nhập đầu tiên
        $user = Session::user();
        if ($user && !empty($user['is_first_login']) && $user['is_first_login'] == 1) {
            if ($uri !== '/first-login' && $uri !== '/logout') {
                Session::setFlash('warning', 'Tài khoản mới: Bạn bắt buộc phải đổi mật khẩu ở lần đầu đăng nhập!');
                header('Location: ' . $basePath . '/first-login');
                exit;
            }
        }
    }
}

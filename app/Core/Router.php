<?php
namespace App\Core;

class Router {
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array $handler, array $middlewares = []): void {
        $this->routes[] = [
            'method'      => $method,
            'path'        => $path,
            'controller'  => $handler[0],
            'action'      => $handler[1],
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $fullUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        // Tự động loại bỏ tiền tố script / thư mục dự án khi chạy trên XAMPP hoặc subfolder
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir  = str_replace('\\', '/', dirname($scriptName));

        $uri = $fullUri;
        if (!empty($scriptName) && str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        } elseif (!empty($scriptDir) && $scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        // Chuẩn hóa URI
        $uri = '/' . ltrim($uri, '/');
        $uri = ($uri !== '/' && str_ends_with($uri, '/')) ? rtrim($uri, '/') : $uri;

        // Ánh xạ tương thích các query parameter truyền thống (?action=...)
        if ($uri === '/' || $uri === '' || $uri === '/index.php') {
            $action = $_GET['action'] ?? ($_POST['action'] ?? '');
            if (!empty($action)) {
                $actionMap = [
                    'login'           => '/login',
                    'logout'          => '/logout',
                    'first_login'     => '/first-login',
                    'forgot_password' => '/forgot-password',
                    'reset_password'  => '/reset-password',
                    'guest_ticket'    => '/guest/ticket',
                    'guest_lookup'    => '/guest/lookup',
                    'quick_login'     => '/quick-login',
                    'courses'         => '/student/courses',
                ];
                if (isset($actionMap[$action])) {
                    $uri = $actionMap[$action];
                }
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                // Thực thi Middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    $mw = new $middlewareClass();
                    $mw->handle();
                }

                $controllerClass = $route['controller'];
                $action = $route['action'];

                $controller = new $controllerClass();
                $controller->$action();
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "<div style='max-width:600px;margin:80px auto;padding:30px;background:#fff;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.06);text-align:center;font-family:sans-serif;'>
                <div style='font-size:48px;margin-bottom:12px;'>🔍</div>
                <h2 style='font-size:22px;color:#1e293b;margin-bottom:8px;'>404 - Không tìm thấy trang</h2>
                <p style='font-size:13px;color:#64748b;margin-bottom:24px;'>Đường dẫn yêu cầu <code>{$uri}</code> không tồn tại trên hệ thống Helpdesk.</p>
                <a href='./' style='display:inline-block;padding:10px 20px;background:#0284c7;color:#fff;text-decoration:none;font-weight:700;font-size:13px;border-radius:10px;'>← Quay về Trang Chủ</a>
              </div>";
    }
}

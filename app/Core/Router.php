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
        echo "<h1 style='text-align:center;margin-top:50px;font-family:sans-serif;'>404 - Không tìm thấy trang [{$uri}]</h1>";
        echo "<p style='text-align:center;'><a href='/'>Quay lại trang chủ</a></p>";
    }
}

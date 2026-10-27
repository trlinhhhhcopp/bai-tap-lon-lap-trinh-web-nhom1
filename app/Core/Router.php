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
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        // Chuẩn hóa URI (loại bỏ trailing slash nếu có)
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

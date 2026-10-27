<?php
namespace App\Core;

abstract class Controller {
    /**
     * Render một view giao diện
     * @param string $view Ví dụ: 'auth/login', 'admin/users/index'
     * @param array $data Dữ liệu truyền sang view
     */
    protected function view(string $view, array $data = []): void {
        extract($data);
        $flash = Session::getFlash();
        $currentUser = Session::user();

        $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            die("Lỗi: Không tìm thấy tệp giao diện view [{$viewFile}]!");
        }

        require_once $viewFile;
    }

    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    protected function flash(string $type, string $message): void {
        Session::setFlash($type, $message);
    }

    protected function user(): ?array {
        return Session::user();
    }
}


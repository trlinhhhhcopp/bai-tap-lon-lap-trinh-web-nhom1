<?php
namespace App\Core;

class Controller {
    protected function view(string $viewPath, array $data = []): void {
        if (class_exists(View::class)) {
            View::render($viewPath, $data);
            return;
        }

        extract($data);
        $flash = Session::getFlash();
        $currentUser = Session::user();

        $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $viewPath) . '.php';

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
        if (str_starts_with($url, '/')) {
            $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
            $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..') ?: dirname(dirname(__DIR__)));
            $base = '/';
            if ($docRoot && str_starts_with($appRoot, $docRoot)) {
                $rel = substr($appRoot, strlen($docRoot));
                $base = '/' . trim(str_replace('\\', '/', $rel), '/') . '/';
                $base = str_replace('//', '/', $base);
            }
            if ($base !== '/' && !str_starts_with($url, $base)) {
                $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
                if (str_contains($scriptName, 'public/index.php')) {
                    $url = rtrim($base, '/') . '/public/index.php' . $url;
                } else {
                    $url = rtrim($base, '/') . $url;
                }
            }
        }
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

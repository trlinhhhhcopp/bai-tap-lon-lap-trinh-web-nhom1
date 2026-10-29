<?php
namespace App\Core;

/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * VIEW RENDERER CORE
 * ==============================================================================
 */
class View {
    public static function render(string $viewPath, array $data = []): void {
        extract($data);
        $flash = Session::getFlash();
        $currentUser = Session::user();

        $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("Lỗi: Không tìm thấy tệp giao diện view [{$viewFile}]!");
        }

        require_once $viewFile;
    }
}


<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * FRONT CONTROLLER: public/index.php (LUỒNG 2: ROUTER MVC)
 * ==============================================================================
 */

// Hỗ trợ PHP Built-in Server: nếu là file tĩnh có sẵn (css, js, ảnh) thì để server phục vụ trực tiếp
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if ($path !== '/' && file_exists(__DIR__ . $path)) {
        return false;
    }
}

require_once __DIR__ . '/../app/bootstrap.php';

$router = App\bootstrapRouter();
$router->dispatch();

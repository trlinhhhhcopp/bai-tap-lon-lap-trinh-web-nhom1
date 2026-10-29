<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1 - ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * ------------------------------------------------------------------------------
 * ROOT ROUTER CHO MÁY CHỦ PHP BUILT-IN SERVER
 * Hợp nhất toàn bộ các phân hệ của 5 thành viên và kiến trúc MVC vào 1 luồng duy nhất
 * ==============================================================================
 */

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đồng bộ tên Session duy nhất cho toàn hệ thống để không bao giờ bị lệch phiên
if (session_status() === PHP_SESSION_NONE) {
    session_name('HELPDESK_TUD_SESS');
    @session_start();
}

$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($rawUri, PHP_URL_PATH);
$file = __DIR__ . $uri;

// 1. Phục vụ trực tiếp nếu là tệp tĩnh có thật (ảnh tải lên, css, js, font)
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    // Nếu là file PHP cụ thể của các thành viên (agent/index.php, tickets/index.php, TranLinh/users.php,...)
    if (str_ends_with($file, '.php')) {
        require $file;
        return true;
    }
    // Các tệp tĩnh khác (png, jpg, jpeg, gif, css, js, mwb, sql,...) -> PHP Built-in tự phục vụ
    return false;
}

// 2. Nếu truy cập đường dẫn thư mục có chứa index.php (ví dụ /tickets/, /agent/, /LeHaMy_GiangVien/...)
if ($uri !== '/' && is_dir($file) && file_exists($file . '/index.php')) {
    require $file . '/index.php';
    return true;
}

// 3. Nếu người dùng truy cập trang chủ `/` hoặc `/hub`
if ($uri === '/' || $uri === '/hub') {
    require __DIR__ . '/index.php';
    return true;
}

// 4. Mọi Request nghiệp vụ MVC khác:
// (/login, /logout, /student/dashboard, /forgot-password, /reset-password, /guest/ticket, /guest/lookup,...)
// Chuyển tiếp toàn bộ vào Front Controller duy nhất: public/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/public/index.php';
return true;

<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * CẤU HÌNH HỆ THỐNG CHUNG
 * ==============================================================================
 */

return [
    'app_name'     => 'Helpdesk CNTT - Đại Học Thủ Đô Hà Nội',
    'app_env'      => 'development', // development | production
    'app_url'      => 'http://localhost:8000',
    'timezone'     => 'Asia/Ho_Chi_Minh',
    'session_name' => 'HELPDESK_TUD_SESS',
    'session_lifetime' => 7200, // 2 giờ
    'max_login_attempts' => 5, // Số lần nhập sai trước khi khóa mềm
    'lockout_time' => 30, // Thời gian khóa mềm (phút)
    'sspr_token_lifetime' => 15, // Thời gian sống của token khôi phục pass (phút)
];


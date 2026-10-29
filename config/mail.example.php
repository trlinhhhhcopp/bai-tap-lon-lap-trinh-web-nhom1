<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * MẪU CẤU HÌNH GỬI EMAIL THỰC TẾ QUA MÁY CHỦ GOOGLE GMAIL SMTP
 * (Đổi tên thành mail.php và điền thông tin tài khoản của bạn)
 * ==============================================================================
 */

return [
    // Bật chế độ gửi email thật qua máy chủ Google Gmail
    'enabled'    => true,

    // Máy chủ SMTP của Google
    'host'       => 'smtp.gmail.com',
    'port'       => 465,       // Cổng 465 (SSL bảo mật cao) hoặc 587 (TLS)
    'encryption' => 'ssl',       // 'ssl' hoặc 'tls'

    /**
     * TÀI KHOẢN GMAIL GỬI THƯ (SMTP SENDER):
     * Bạn có thể dùng 1 tài khoản Gmail bất kỳ để làm hòm thư máy chủ gửi.
     * Google yêu cầu sử dụng "Mật khẩu ứng dụng" (App Password 16 chữ số)
     * Cách lấy trong 1 phút: Vào Quản lý Tài khoản Google -> Bảo mật -> Xác minh 2 bước -> Mật khẩu ứng dụng.
     */
    'username'   => 'your_email@gmail.com', // Điền địa chỉ Gmail gửi vào đây
    'password'   => 'xxxx xxxx xxxx xxxx', // Điền Mật khẩu ứng dụng 16 ký tự vào đây

    // Tên và địa chỉ hiển thị trong hòm thư sinh viên khi nhận được thư
    'from_email' => 'your_email@gmail.com',
    'from_name'  => 'Trung tâm CNTT - Trường ĐH Thủ Đô Hà Nội',
    'reply_to'   => 'helpdesk@daihocthudo.edu.vn',
];

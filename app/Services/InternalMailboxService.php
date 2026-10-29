<?php
namespace App\Services;

class InternalMailboxService {
    private static string $storageFile = __DIR__ . '/../../storage/mailbox.json';

    private static function loadAll(): array {
        if (!file_exists(self::$storageFile)) {
            return [];
        }
        $data = @file_get_contents(self::$storageFile);
        if (!$data) return [];
        $json = json_decode($data, true);
        return is_array($json) ? $json : [];
    }

    private static function saveAll(array $all): void {
        $dir = dirname(self::$storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(self::$storageFile, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Gửi một thư điện tử vào Hòm thư nội bộ của người nhận (@daihocthudo.edu.vn)
     */
    public static function send(string $toEmail, string $toName, string $subject, string $bodyHtml, string $otpCode = ''): array {
        $all = self::loadAll();
        $mail = [
            'id'          => 'msg_' . time() . '_' . bin2hex(random_bytes(3)),
            'to_email'    => strtolower(trim($toEmail)),
            'to_name'     => $toName,
            'from_name'   => 'Trung tâm CNTT - Trường ĐH Thủ Đô Hà Nội',
            'from_email'  => 'helpdesk@daihocthudo.edu.vn',
            'subject'     => $subject,
            'body_html'   => $bodyHtml,
            'otp_code'    => strtoupper(trim($otpCode)),
            'sent_at'     => date('Y-m-d H:i:s'),
            'sent_time'   => date('H:i:s d/m/Y'),
            'is_read'     => 0,
        ];

        $key = strtolower(trim($toEmail));
        if (!isset($all[$key])) {
            $all[$key] = [];
        }
        array_unshift($all[$key], $mail);
        self::saveAll($all);

        // Lưu bản sao vào Session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['internal_mailbox'][$key] = $all[$key];
        }

        return $mail;
    }

    /**
     * Lấy toàn bộ thư trong Hòm thư nội bộ của tài khoản
     */
    public static function getMails(string $email): array {
        $all = self::loadAll();
        $key = strtolower(trim($email));
        $mails = $all[$key] ?? ($_SESSION['internal_mailbox'][$key] ?? []);

        // Nếu hòm thư chưa có thư nào, bổ sung thư chào mừng ban đầu
        if (empty($mails)) {
            $welcome = [
                'id'          => 'msg_welcome_' . md5($email),
                'to_email'    => $key,
                'to_name'     => 'Sinh viên / Cán bộ',
                'from_name'   => 'Phòng Quản lý Đào tạo & CNTT',
                'from_email'  => 'daotao@daihocthudo.edu.vn',
                'subject'     => 'Chào mừng bạn đến với Hòm thư điện tử Trường Đại Học Thủ Đô Hà Nội',
                'body_html'   => '<p>Chào bạn,</p><p>Hòm thư điện tử học thuật của bạn đã được kích hoạt thành công trên hệ thống máy chủ Trường Đại Học Thủ Đô Hà Nội. Mọi thông báo lịch học, kết quả điểm thi và hỗ trợ CNTT sẽ được gửi về đây.</p><p>Trân trọng,<br><strong>Phòng QLĐT & Trung tâm CNTT</strong></p>',
                'otp_code'    => '',
                'sent_at'     => date('Y-m-d 08:00:00'),
                'sent_time'   => '08:00:00 ' . date('d/m/Y'),
                'is_read'     => 1,
            ];
            $mails = [$welcome];
        }

        return $mails;
    }
}

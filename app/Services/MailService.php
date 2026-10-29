<?php
namespace App\Services;

use Exception;

class MailService {
    private static string $lastError = '';

    public static function getLastError(): string {
        return self::$lastError;
    }

    /**
     * Gửi email thật qua giao thức SMTP của Google Gmail
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
        self::$lastError = '';
        $configFile = __DIR__ . '/../../config/mail.php';
        if (!file_exists($configFile)) {
            self::$lastError = "File cấu hình config/mail.php không tồn tại!";
            return false;
        }

        $config = require $configFile;
        if (empty($config['enabled'])) {
            self::$lastError = "Chế độ gửi email thật (enabled) đang tắt trong config/mail.php!";
            return false;
        }

        if (empty($config['username']) || empty($config['password']) || $config['username'] === 'your_email@gmail.com') {
            self::$lastError = "Chưa điền tài khoản hoặc mật khẩu ứng dụng Gmail trong config/mail.php!";
            return false;
        }

        $host       = $config['host'] ?? 'smtp.gmail.com';
        $port       = (int)($config['port'] ?? 465);
        $encryption = strtolower($config['encryption'] ?? 'ssl');
        $username   = trim($config['username']);
        $password   = str_replace(' ', '', trim($config['password'])); // Xóa khoảng trắng trong mật khẩu ứng dụng
        $fromEmail  = $config['from_email'] ?? $username;
        $fromName   = $config['from_name'] ?? 'Trung tâm CNTT - Trường ĐH Thủ Đô Hà Nội';

        // Kiểm tra độ dài mật khẩu ứng dụng Google (chuẩn 16 ký tự)
        if (strlen($password) < 16) {
            self::$lastError = "Mật khẩu ứng dụng của Google có độ dài 16 chữ cái. Mật khẩu hiện tại bạn điền chỉ có " . strlen($password) . " chữ cái, hãy kiểm tra lại nhé!";
        }

        $socketHost = ($encryption === 'ssl') ? "ssl://{$host}" : $host;
        $timeout    = 8;

        $socket = @fsockopen($socketHost, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            self::$lastError = "Không thể kết nối đến máy chủ Google ({$socketHost}:{$port}): {$errstr} ({$errno})";
            error_log("MailService Connect Failed: " . self::$lastError);
            return false;
        }

        stream_set_timeout($socket, $timeout);

        $read = function() use ($socket) {
            $data = '';
            while ($str = fgets($socket, 515)) {
                $data .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $data;
        };

        $write = function(string $cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $resp = $read();
        if (substr($resp, 0, 3) !== '220') {
            self::$lastError = "Phản hồi máy chủ ban đầu không hợp lệ: {$resp}";
            fclose($socket);
            return false;
        }

        // Gửi lệnh EHLO
        $write("EHLO " . gethostname());
        $resp = $read();

        // Nếu cổng 587 sử dụng STARTTLS
        if ($encryption === 'tls') {
            $write("STARTTLS");
            $resp = $read();
            if (substr($resp, 0, 3) !== '220') {
                self::$lastError = "STARTTLS không được chấp nhận: {$resp}";
                fclose($socket);
                return false;
            }
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                self::$lastError = "Không thể thiết lập mã hóa TLS an toàn với máy chủ Google!";
                fclose($socket);
                return false;
            }
            $write("EHLO " . gethostname());
            $resp = $read();
        }

        // Xác thực AUTH LOGIN
        $write("AUTH LOGIN");
        $resp = $read();
        if (substr($resp, 0, 3) !== '334') {
            self::$lastError = "AUTH LOGIN bị từ chối: {$resp}";
            fclose($socket);
            return false;
        }

        $write(base64_encode($username));
        $resp = $read();
        if (substr($resp, 0, 3) !== '334') {
            self::$lastError = "Username Gmail bị từ chối: {$resp}";
            fclose($socket);
            return false;
        }

        $write(base64_encode($password));
        $resp = $read();
        if (substr($resp, 0, 3) !== '235') {
            self::$lastError = "Mật khẩu ứng dụng Gmail bị từ chối (Mã {$resp}). Bạn hãy kiểm tra lại đúng 16 chữ cái do Google cấp.";
            fclose($socket);
            error_log("MailService Auth Failed: " . self::$lastError);
            return false;
        }

        // Lệnh MAIL FROM
        $write("MAIL FROM:<{$username}>");
        $resp = $read();
        if (substr($resp, 0, 3) !== '250') {
            self::$lastError = "MAIL FROM bị từ chối: {$resp}";
            fclose($socket);
            return false;
        }

        // Lệnh RCPT TO
        $write("RCPT TO:<{$toEmail}>");
        $resp = $read();
        if (substr($resp, 0, 3) !== '250') {
            self::$lastError = "Địa chỉ người nhận bị từ chối: {$resp}";
            fclose($socket);
            return false;
        }

        // Lệnh DATA
        $write("DATA");
        $resp = $read();
        if (substr($resp, 0, 3) !== '354') {
            self::$lastError = "DATA bị từ chối: {$resp}";
            fclose($socket);
            return false;
        }

        // Soạn Header theo chuẩn MIME RFC 2045/2047 hỗ trợ đầy đủ Tiếng Việt có dấu
        $encodedSubject  = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $encodedToName   = '=?UTF-8?B?' . base64_encode($toName) . '?=';

        $headers = [];
        $headers[] = "From: {$encodedFromName} <{$fromEmail}>";
        $headers[] = "Reply-To: <" . ($config['reply_to'] ?? 'helpdesk@daihocthudo.edu.vn') . ">";
        $headers[] = "To: {$encodedToName} <{$toEmail}>";
        $headers[] = "Subject: {$encodedSubject}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: base64";
        $headers[] = "Date: " . date('r');
        $headers[] = "X-Mailer: Helpdesk-TUD-Mailer/1.0";

        $body = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody));

        $write($body . "\r\n.");
        $resp = $read();

        $write("QUIT");
        fclose($socket);

        return (substr($resp, 0, 3) === '250');
    }
}


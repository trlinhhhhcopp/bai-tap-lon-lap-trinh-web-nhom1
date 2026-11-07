<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Services\AuthService;
use App\Services\MailService;
use Exception;

class AuthController extends Controller {
    public function showLogin(): void {
        if (Session::check()) {
            $user = Session::user();
            if ($user['role'] === 'admin') {
                $this->redirect('/admin/dashboard');
            } elseif ($user['role'] === 'agent') {
                $this->redirect('/agent/workbench');
            } elseif ($user['role'] === 'lecturer' || ($user['user_type'] ?? '') === 'lecturer') {
                $this->redirect('/lecturer/dashboard');
            } else {
                $this->redirect('/student/dashboard');
            }
        }

        $this->view('auth/login', [
            'title' => 'Đăng Nhập Cổng Helpdesk CNTT - ĐH Thủ Đô Hà Nội',
        ]);
    }

    public function quickLogin(): void {
        $code = trim($_GET['code'] ?? '');
        $user = User::findByEmailOrCode($code);
        if ($user) {
            if ((int)$user['is_active'] === 0) {
                $this->flash('danger', 'Tài khoản này đang bị khóa!');
                $this->redirect('/login');
                return;
            }
            Session::set('user', $user);
            $this->flash('success', "Đăng nhập thành công với vai trò: <strong>{$user['name']}</strong> ({$user['user_code']})");
            if ($user['role'] === 'admin') {
                $this->redirect('/admin/dashboard');
            } elseif ($user['role'] === 'agent') {
                $this->redirect('/agent/workbench');
            } elseif ($user['role'] === 'lecturer' || ($user['user_type'] ?? '') === 'lecturer') {
                $this->redirect('/lecturer/dashboard');
            } else {
                $this->redirect('/student/dashboard');
            }
            return;
        }
        $this->flash('danger', 'Không tìm thấy tài khoản để đăng nhập nhanh!');
        $this->redirect('/login');
    }

    public function login(): void {
        $identifier = $_POST['identifier'] ?? '';
        $password   = $_POST['password'] ?? '';

        $result = AuthService::login($identifier, $password);

        if (!$result['success']) {
            $this->flash('danger', $result['message']);
            $this->redirect('/login');
        }

        $user = $result['user'];

        // Nếu là lần đầu đăng nhập, bắt buộc đổi mật khẩu (Slide 2)
        if ($result['is_first_login']) {
            $this->flash('warning', 'Chào mừng bạn! Vui lòng đổi mật khẩu khởi tạo (123456) để kích hoạt tài khoản.');
            $this->redirect('/first-login');
        }

        $this->flash('success', "Xin chào <strong>{$user['name']}</strong>! Đăng nhập thành công.");

        // Điều hướng theo vai trò (RBAC Routing)
        if ($user['role'] === 'admin') {
            $this->redirect('/admin/dashboard');
        } elseif ($user['role'] === 'agent') {
            $this->redirect('/agent/workbench');
        } elseif ($user['role'] === 'lecturer' || ($user['user_type'] ?? '') === 'lecturer') {
            $this->redirect('/lecturer/dashboard');
        } else {
            $this->redirect('/student/dashboard');
        }
    }

    public function logout(): void {
        Session::destroy();
        Session::setFlash('info', 'Bạn đã đăng xuất an toàn khỏi hệ thống!');
        $this->redirect('/login');
    }

    public function showFirstLogin(): void {
        $user = Session::user();
        if (!$user) {
            $this->redirect('/login');
        }

        $this->view('auth/first_login', [
            'user'  => $user,
            'title' => 'Đổi Mật Khẩu Lần Đầu',
        ]);
    }

    public function changeFirstLogin(): void {
        $user = Session::user();
        if (!$user) {
            $this->redirect('/login');
        }

        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        try {
            AuthService::changeFirstLoginPassword($user['id'], $newPass, $confirm);
            $this->flash('success', 'Đổi mật khẩu lần đầu thành công! Tài khoản của bạn đã được kích hoạt.');
            if ($user['role'] === 'admin') {
                $this->redirect('/admin/users');
            } else {
                $this->redirect('/student/dashboard');
            }
        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/first-login');
        }
    }

    public function showForgotPassword(): void {
        $simulatedEmail = $_SESSION['simulated_reset_email'] ?? null;
        if ($simulatedEmail && time() >= ($simulatedEmail['expires_at'] ?? 0)) {
            unset($_SESSION['simulated_reset_email']);
            $simulatedEmail = null;
        }

        $this->view('auth/forgot_password', [
            'title'          => 'Quên Mật Khẩu (Khôi phục qua Email)',
            'simulatedEmail' => $simulatedEmail,
        ]);
    }

    public function sendResetLink(): void {
        $step = $_POST['step'] ?? '';

        // 1. Xử lý bước 2: Xác nhận mã OTP gồm 6 chữ cái in hoa
        if ($step === 'verify_otp') {
            $otpInput = strtoupper(trim($_POST['otp_input'] ?? ''));
            if (empty($otpInput)) {
                $this->flash('danger', 'Vui lòng nhập mã OTP gồm 6 chữ cái xác thực!');
                $this->redirect('/forgot-password?step=enter_otp');
            }

            $record = PasswordReset::verifyToken($otpInput);
            if (!$record) {
                $this->flash('danger', 'Mã OTP không chính xác hoặc đã hết hạn hiệu lực 15 phút. Vui lòng kiểm tra lại!');
                $this->redirect('/forgot-password?step=enter_otp');
            }

            $_SESSION['reset_otp_data'] = [
                'user_id'        => $record['user_id'],
                'user_name'      => $record['name'],
                'user_code'      => $record['user_code'],
                'email'          => $record['email'],
                'verified'       => true,
                'verified_token' => $otpInput,
            ];

            $this->flash('success', 'Xác thực mã OTP thành công! Vui lòng thiết lập mật khẩu mới.');
            $this->redirect('/reset-password?token=' . rawurlencode($otpInput));
        }

        // 2. Xử lý bước 1: Nhận MSV hoặc Email trường và phát sinh mã OTP
        $identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? '');

        if (empty($identifier)) {
            $this->flash('danger', 'Vui lòng nhập Mã Sinh Viên hoặc địa chỉ Email trường cấp của bạn!');
            $this->redirect('/forgot-password');
        }

        // Tự động tìm user theo MSV hoặc email
        $user = User::findByEmailOrCode($identifier);
        if (!$user && !str_contains($identifier, '@')) {
            $user = User::findByEmailOrCode($identifier . '@daihocthudo.edu.vn');
        }

        if (!$user) {
            $this->flash('danger', 'Không tìm thấy tài khoản với thông tin đã nhập! Vui lòng kiểm tra lại MSV hoặc Email.');
            $this->redirect('/forgot-password');
        }

        if (empty($user['is_active']) || ($user['academic_status'] ?? '') === 'expelled') {
            $this->flash('danger', 'Tài khoản này đang bị khóa hoặc ngừng hoạt động. Vui lòng gửi đơn tại Trạm cứu hộ tài khoản!');
            $this->redirect('/forgot-password');
        }

        $email = $user['email'];
        $userName = $user['name'] ?? 'Sinh viên';

        try {
            // Cấp mã OTP dạng chữ cái in hoa (6 ký tự, hiệu lực 15 phút)
            $rawToken = PasswordReset::createToken($user['id'], $email, 15);
            $link = $this->resetPasswordUrl($rawToken);
            $fullLink = $this->resetPasswordUrl($rawToken, true);
            $nowTime = date('H:i:s');
            $expiresAtTimestamp = time() + 900; // 15 phút
            $expireTime = date('H:i:s', $expiresAtTimestamp);

            // Gửi email thật qua máy chủ Google Gmail & lưu mailbox nội bộ
            $emailSubject = "[Helpdesk TUD] Mã OTP khôi phục mật khẩu tài khoản: {$rawToken}";
            $emailHtml = self::buildResetEmailTemplate($userName, $email, $fullLink, $nowTime, $expireTime, $rawToken);
            $sentReal = MailService::send($email, $userName, $emailSubject, $emailHtml);
            \App\Services\InternalMailboxService::send($email, $userName, $emailSubject, $emailHtml, $rawToken);

            // Lưu phiên xác thực OTP
            $_SESSION['reset_otp_data'] = [
                'user_id'      => $user['id'],
                'user_name'    => $userName,
                'user_code'    => $user['user_code'],
                'email'        => $email,
                'target_email' => $email,
                'otp'          => $rawToken,
                'expires_at'   => $expiresAtTimestamp,
                'verified'     => false,
            ];

            // Lưu thông tin hòm thư vào session (backward-compatible)
            $_SESSION['simulated_reset_email'] = [
                'email'        => $email,
                'name'         => $userName,
                'link'         => $link,
                'created_time' => $nowTime,
                'expire_time'  => $expireTime,
                'expires_at'   => $expiresAtTimestamp,
                'is_sent_real' => $sentReal,
            ];

            $this->flash('success', "🚀 Đã gửi mã OTP 6 chữ cái in hoa đến hộp thư: <strong>{$email}</strong>! Vui lòng nhập mã OTP để tiếp tục.");
            $this->redirect('/forgot-password?step=enter_otp');
        } catch (\PDOException $e) {
            $this->flash('danger', 'Hệ thống đang bận xử lý dữ liệu. Vui lòng thử lại sau ít phút hoặc gửi yêu cầu cứu hộ tài khoản!');
            $this->redirect('/forgot-password');
        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    public function showResetPassword(): void {
        $token = $_GET['token'] ?? '';
        $this->view('auth/reset_password', [
            'token' => $token,
            'title' => 'Thiết Lập Mật Khẩu Mới',
        ]);
    }

    public function resetPassword(): void {
        $token   = trim($_POST['token'] ?? ($_SESSION['reset_otp_data']['verified_token'] ?? ''));
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        try {
            AuthService::resetPasswordWithToken($token, $newPass, $confirm);
            unset($_SESSION['simulated_reset_email']);
            unset($_SESSION['reset_otp_data']);
            $this->flash('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.');
            $this->redirect('/login');
        } catch (\PDOException $e) {
            $this->flash('danger', 'Có lỗi khi cập nhật mật khẩu vào hệ thống cơ sở dữ liệu. Vui lòng thử lại hoặc gửi yêu cầu cứu hộ!');
            $this->redirect('/reset-password?token=' . urlencode($token));
        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/reset-password?token=' . urlencode($token));
        }
    }

    public function apiSendResetEmail(): void {
        header('Content-Type: application/json; charset=utf-8');
        $email = trim($_POST['email'] ?? '');
        $token = trim($_POST['token'] ?? '');

        if (empty($email) || empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin email hoặc token']);
            return;
        }

        $user = User::findByEmailOrCode($email);
        $userName = $user['name'] ?? 'Sinh viên';
        $fullLink = $this->resetPasswordUrl($token, true);
        $nowTime = date('H:i:s');
        $expireTime = date('H:i:s', time() + 900);

        $emailSubject = "[Helpdesk TUD] Hướng dẫn đặt lại mật khẩu tài khoản sinh viên";
        $emailHtml = self::buildResetEmailTemplate($userName, $email, $fullLink, $nowTime, $expireTime);

        $sent = MailService::send($email, $userName, $emailSubject, $emailHtml);

        if ($sent) {
            echo json_encode([
                'success' => true,
                'message' => "Đã gửi thành công email đặt lại mật khẩu đến: {$email}",
                'email'   => $email,
            ]);
        } else {
            $err = MailService::getLastError();
            echo json_encode([
                'success' => false,
                'message' => $err ?: "Không thể kết nối máy chủ gửi thư Gmail.",
                'error_detail' => $err,
            ]);
        }
        exit;
    }

    private function resetPasswordUrl(string $token, bool $absolute = false): string {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/');
        $basePath = ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir;
        $path = $basePath . '/reset-password?token=' . rawurlencode($token);
        if (!$absolute) {
            return $path;
        }

        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $port = (int)($_SERVER['SERVER_PORT'] ?? 0);
        $https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
        $scheme = $https ? 'https' : 'http';
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $host = '[' . $host . ']';
        }
        if ($port > 0 && $port !== ($https ? 443 : 80)) {
            $host .= ':' . $port;
        }

        return $scheme . '://' . $host . $path;
    }

    public static function buildResetEmailTemplate(string $name, string $email, string $link, string $nowTime, string $expireTime, ?string $otpCode = null): string {
        $otpSection = '';
        if (!empty($otpCode)) {
            $otpSection = "
            <div style='background: #eef2ff; border: 2px dashed #6366f1; border-radius: 10px; padding: 18px; text-align: center; margin: 20px 0;'>
                <div style='font-size: 12px; font-weight: bold; color: #4338ca; text-transform: uppercase; letter-spacing: 1px;'>Mã Xác Thực OTP Của Bạn (6 Chữ Cái):</div>
                <div style='font-family: monospace; font-size: 32px; font-weight: 900; color: #1e1b4b; letter-spacing: 8px; margin-top: 6px;'>{$otpCode}</div>
                <div style='font-size: 11px; color: #6366f1; margin-top: 4px;'>Nhập trực tiếp mã 6 chữ cái này trên trang Quên mật khẩu hoặc bấm nút bên dưới</div>
            </div>
            ";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Đặt lại mật khẩu</title>
        </head>
        <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background: #f1f5f9; padding: 25px 15px; margin: 0;'>
            <div style='max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;'>
                <div style='background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 25px; text-align: center; border-bottom: 3px solid #0284c7;'>
                    <h2 style='color: #38bdf8; margin: 0 0 6px; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px;'>Trường Đại Học Thủ Đô Hà Nội</h2>
                    <p style='color: #94a3b8; margin: 0; font-size: 13px;'>Hệ Thống Tiếp Nhận & Xử Lý Hỗ Trực Tuyến (Helpdesk Online)</p>
                </div>

                <div style='padding: 30px 25px; color: #1e293b; line-height: 1.6;'>
                    <p style='font-size: 15px; margin-top: 0;'>Xin chào <strong>{$name}</strong>,</p>
                    <p style='font-size: 14px; color: #475569;'>
                        Hệ thống Helpdesk vừa tiếp nhận yêu cầu đặt lại mật khẩu cho tài khoản sinh viên của bạn: 
                        <strong style='color: #0369a1;'>{$email}</strong> vào lúc <strong>{$nowTime}</strong>.
                    </p>
                    {$otpSection}
                    <p style='font-size: 14px; color: #475569;'>
                        Để hoàn tất việc thiết lập mật khẩu mới, bạn có thể nhập mã OTP ở trên hoặc bấm vào nút bên dưới:
                    </p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$link}' target='_blank' style='display: inline-block; padding: 13px 30px; background: #0284c7; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(2,132,199,0.35);'>
                            🔑 ĐẶT LẠI MẬT KHẨU NGAY ➔
                        </a>
                    </div>

                    <div style='background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; font-size: 13px; color: #92400e; margin-bottom: 20px;'>
                        ⏱️ <strong>Lưu ý bảo mật:</strong> Đường link và mã OTP này chỉ có hiệu lực trong vòng <strong>15 phút</strong> (hết hạn lúc <strong>{$expireTime}</strong>). Sau thời gian này, liên kết sẽ tự động bị hủy để đảm bảo an toàn cho tài khoản của bạn.
                    </div>

                    <p style='font-size: 13px; color: #64748b; margin-bottom: 0;'>
                        Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email hoặc liên hệ ngay Trung tâm CNTT trường để được hỗ trợ bảo vệ tài khoản.
                    </p>
                </div>

                <div style='background: #f8fafc; padding: 15px 25px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                    Bài tập lớn môn Lập trình Web - Nhóm 1 | Phân hệ Auth & User Management<br>
                    Trường Đại học Thủ Đô Hà Nội · Khoa Toán - CNTT
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

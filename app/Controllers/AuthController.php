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
                $this->redirect('/admin/users');
            } elseif ($user['role'] === 'agent') {
                $this->redirect('/agent/workbench');
            } else {
                $this->redirect('/student/dashboard');
            }
        }

        $this->view('auth/login', [
            'title' => 'Đăng Nhập Cổng Helpdesk CNTT - ĐH Thủ Đô Hà Nội',
        ]);
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
            $this->redirect('/admin/users');
        } elseif ($user['role'] === 'agent') {
            $this->redirect('/agent/workbench');
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
        $email = trim($_POST['email'] ?? '');

        // 1. Kiểm tra rỗng
        if (empty($email)) {
            $this->flash('danger', 'Vui lòng nhập địa chỉ Email trường cấp của bạn!');
            $this->redirect('/forgot-password');
        }

        // Hỗ trợ sinh viên: nếu lỡ chỉ gõ MSV (VD: 224001729), tự động ghép đuôi @daihocthudo.edu.vn
        if (!str_contains($email, '@')) {
            $email .= '@daihocthudo.edu.vn';
        }

        // 2. Kiểm tra định dạng email và đuôi trường cấp
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@daihocthudo.edu.vn')) {
            $this->flash('danger', 'Vui lòng nhập đúng Email trường cấp có đuôi <strong>@daihocthudo.edu.vn</strong> (Ví dụ: 224001729@daihocthudo.edu.vn)!');
            $this->redirect('/forgot-password');
        }

        try {
            $rawToken = AuthService::requestResetToken($email);
            $user = User::findByEmailOrCode($email);
            $userName = $user['name'] ?? 'Sinh viên';
            $link = "/reset-password?token=" . $rawToken;
            $fullLink = "http://localhost:8080/reset-password?token=" . $rawToken;
            $nowTime = date('H:i:s');
            $expiresAtTimestamp = time() + 900; // 15 phút (900 giây)
            $expireTime = date('H:i:s', $expiresAtTimestamp);

            // Gửi email thật qua máy chủ Google Gmail
            $emailSubject = "[Helpdesk TUD] Hướng dẫn đặt lại mật khẩu tài khoản sinh viên";
            $emailHtml = self::buildResetEmailTemplate($userName, $email, $fullLink, $nowTime, $expireTime);
            $sentReal = MailService::send($email, $userName, $emailSubject, $emailHtml);
            \App\Services\InternalMailboxService::send($email, $userName, $emailSubject, $emailHtml, $rawToken);

            // Lưu thông tin hòm thư vào session
            $_SESSION['simulated_reset_email'] = [
                'email'        => $email,
                'name'         => $userName,
                'link'         => $link,
                'created_time' => $nowTime,
                'expire_time'  => $expireTime,
                'expires_at'   => $expiresAtTimestamp,
                'is_sent_real' => $sentReal,
            ];

            // Thông báo Flash Message kèm đồng hồ đếm ngược 15 phút và link mở tab mới
            $statusText = $sentReal 
                ? "🚀 <strong>Hệ thống đã gửi link đổi mật khẩu thành công đến:</strong> <span style='color:#0369a1; font-weight:700;'>{$email}</span>!
                   <div style='margin-top: 4px; font-size: 13px; color: #334155;'>
                       Vui lòng mở tab <strong>Gmail</strong> của bạn, mở thư gửi từ <em>Trung tâm CNTT</em> và bấm nút <strong>\"🔑 ĐẶT LẠI MẬT KHẨU NGAY\"</strong> trong email để thiết lập mật khẩu mới.
                   </div>"
                : "✉️ <strong>Đã cấp liên kết khôi phục mật khẩu cho email trường cấp:</strong> <span style='color:#0369a1; font-weight:700;'>{$email}</span>!";

            $msg = "
            <div id='resetCountdownBox' data-expires='{$expiresAtTimestamp}' style='display:flex; flex-direction:column; gap:8px;'>
                <div>
                    {$statusText}
                </div>
                <div style='display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-top:2px;'>
                    <span style='display:inline-flex; align-items:center; gap:6px; background:#16a34a; color:#fff; padding:7px 16px; border-radius:6px; font-weight:700; font-size:13px; box-shadow:0 2px 6px rgba(22,163,74,0.3);'>
                        ✓ ĐÃ GỬI LINK VỀ GMAIL!
                    </span>
                    <a href='https://mail.google.com' target='_blank' style='display:inline-flex; align-items:center; gap:6px; background:#0284c7; color:#fff; padding:7px 15px; border-radius:6px; font-weight:700; text-decoration:none; font-size:13px; box-shadow:0 2px 6px rgba(2,132,199,0.3);' title='Mở nhanh tab Gmail để kiểm tra hộp thư'>
                        📬 Mở tab Gmail của bạn ➔
                    </a>
                    <span style='background:#fef3c7; color:#b45309; padding:5px 12px; border-radius:6px; font-weight:700; font-size:12.5px; border:1px solid #fde68a; display:inline-flex; align-items:center; gap:6px;'>
                        ⏱️ Hiệu lực đường link còn: <span id='countdownTimer' style='font-family:monospace; font-size:14px; font-weight:800; color:#b91c1c;'>15:00</span>
                    </span>
                </div>
                <div style='font-size:12px; color:#64748b; margin-top:2px;'>
                    📅 Thời gian cấp: <strong>{$nowTime}</strong> | Đường link hết hiệu lực lúc: <strong>{$expireTime}</strong> (Đúng 15 phút).
                </div>
                <div style='font-size:11.5px; color:#94a3b8; margin-top:1px;'>
                    💡 Lưu ý: Bạn vào Gmail rồi bấm nút trong email mới mở tab đặt lại mật khẩu mới. Nếu không tìm thấy ở Hộp thư đến, bạn kiểm tra thêm mục <strong>Thư rác (Spam)</strong> nhé.
                </div>
            </div>
            <script>
            (function() {
                const box = document.getElementById('resetCountdownBox');
                if (!box) return;
                const expiresAt = parseInt(box.getAttribute('data-expires'), 10);
                const timerElem = document.getElementById('countdownTimer');
                const alertContainer = box.closest('.alert');

                function updateCountdown() {
                    const now = Math.floor(Date.now() / 1000);
                    const remaining = Math.max(0, expiresAt - now);

                    if (remaining <= 0) {
                        if (alertContainer) {
                            alertContainer.innerHTML = '<div style=\"color:#b91c1c; font-weight:700; padding:6px;\">⚠️ Đường link đổi mật khẩu đã hết hạn hiệu lực 15 phút! Flash message sẽ tự động đóng...</div>';
                            setTimeout(function() {
                                alertContainer.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                                alertContainer.style.opacity = '0';
                                alertContainer.style.transform = 'translateY(-10px)';
                                setTimeout(function() { if (alertContainer) alertContainer.remove(); }, 800);
                            }, 3000);
                        }
                        return false;
                    }

                    const m = Math.floor(remaining / 60);
                    const s = remaining % 60;
                    if (timerElem) {
                        timerElem.textContent = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                    }
                    return true;
                }

                if (updateCountdown()) {
                    const timerInterval = setInterval(function() {
                        if (!updateCountdown()) {
                            clearInterval(timerInterval);
                        }
                    }, 1000);
                }
            })();
            </script>
            ";

            $this->flash('success', $msg);
            $this->redirect('/forgot-password');
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
        $token   = $_POST['token'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        try {
            AuthService::resetPasswordWithToken($token, $newPass, $confirm);
            unset($_SESSION['simulated_reset_email']);
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
        $fullLink = "http://localhost:8080/reset-password?token=" . $token;
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

    public static function buildResetEmailTemplate(string $name, string $email, string $link, string $nowTime, string $expireTime): string {
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
                    <p style='color: #94a3b8; margin: 0; font-size: 13px;'>Hệ Thống Tiếp Nhận & Xử Lý Hỗ Trợ CNTT Trực Tuyến (Helpdesk Online)</p>
                </div>

                <div style='padding: 30px 25px; color: #1e293b; line-height: 1.6;'>
                    <p style='font-size: 15px; margin-top: 0;'>Xin chào <strong>{$name}</strong>,</p>
                    <p style='font-size: 14px; color: #475569;'>
                        Hệ thống Helpdesk vừa tiếp nhận yêu cầu đặt lại mật khẩu cho tài khoản sinh viên của bạn: 
                        <strong style='color: #0369a1;'>{$email}</strong> vào lúc <strong>{$nowTime}</strong>.
                    </p>
                    <p style='font-size: 14px; color: #475569;'>
                        Để hoàn tất việc thiết lập mật khẩu mới, bạn vui lòng bấm vào nút bên dưới:
                    </p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$link}' target='_blank' style='display: inline-block; padding: 13px 30px; background: #0284c7; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(2,132,199,0.35);'>
                            🔑 ĐẶT LẠI MẬT KHẨU NGAY ➔
                        </a>
                    </div>

                    <div style='background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; font-size: 13px; color: #92400e; margin-bottom: 20px;'>
                        ⏱️ <strong>Lưu ý bảo mật:</strong> Đường link này chỉ có hiệu lực trong vòng <strong>15 phút</strong> (hết hạn lúc <strong>{$expireTime}</strong>). Sau thời gian này, liên kết sẽ tự động bị hủy để đảm bảo an toàn cho tài khoản của bạn.
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


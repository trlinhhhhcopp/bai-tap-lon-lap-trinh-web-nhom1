<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\AuthService;
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
        $this->view('auth/forgot_password', [
            'title' => 'Quên Mật Khẩu (SSPR - 15 phút)',
        ]);
    }

    public function sendResetLink(): void {
        $email = $_POST['email'] ?? '';
        try {
            $rawToken = AuthService::requestResetToken($email);
            // Giả lập gửi link email
            $link = "/reset-password?token=" . $rawToken;
            $this->flash('success', "Hệ thống đã cấp mã Token thành công (hiệu lực 15 phút)! Đường link khôi phục của bạn là: <a href='{$link}'><strong>Bấm vào đây để đổi mật khẩu</strong></a>");
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
            $this->flash('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.');
            $this->redirect('/login');
        } catch (Exception $e) {
            $this->flash('danger', $e->getMessage());
            $this->redirect('/reset-password?token=' . urlencode($token));
        }
    }
}


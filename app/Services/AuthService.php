<?php
namespace App\Services;

use App\Models\User;
use App\Models\PasswordReset;
use App\Core\Session;
use InvalidArgumentException;
use RuntimeException;

class AuthService {
    /**
     * Xác thực Đăng nhập & Kiểm tra cơ chế Khóa mềm / Khóa cứng
     */
    public static function login(string $identifier, string $password): array {
        $identifier = trim($identifier);
        if (empty($identifier) || empty($password)) {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ Mã đăng nhập và Mật khẩu!'];
        }

        $user = User::findByEmailOrCode($identifier);
        if (!$user) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại trên hệ thống!'];
        }

        // 1. Kiểm tra Khóa cứng (Do kỷ luật buộc thôi học hoặc hết khóa)
        if ($user['is_active'] == 0) {
            $reason = ($user['lock_reason'] === 'expelled') ? 'kỷ luật Buộc thôi học' : 'hết hạn niên khóa';
            return [
                'success' => false, 
                'message' => "Tài khoản của bạn đã bị KHÓA CỨNG do {$reason}. Vui lòng liên hệ Phòng Đào tạo hoặc gửi phiếu Cứu hộ ngoài trang chủ!",
                'is_locked' => true,
            ];
        }

        // 2. Kiểm tra Khóa mềm (Do nhập sai quá 5 lần liên tiếp)
        if (!empty($user['locked_until'])) {
            $lockTime = strtotime($user['locked_until']);
            $now = time();
            if ($lockTime > $now) {
                $remMinutes = ceil(($lockTime - $now) / 60);
                return [
                    'success' => false, 
                    'message' => "Tài khoản tạm thời bị KHÓA MỀM do gõ sai quá 5 lần. Vui lòng thử lại sau {$remMinutes} phút!",
                    'is_soft_locked' => true,
                ];
            }
        }

        // 3. Xác thực mật khẩu Bcrypt
        if (!password_verify($password, $user['password_hash'])) {
            User::recordFailedAttempt($user['id']);
            $remAttempts = max(0, 5 - ($user['failed_attempts'] + 1));
            return [
                'success' => false, 
                'message' => "Mật khẩu không chính xác! Bạn còn {$remAttempts} lần thử trước khi bị khóa tạm thời.",
            ];
        }

        // 4. Đăng nhập thành công: Reset bộ đếm số lần sai
        User::resetFailedAttempts($user['id']);

        // Lưu thông tin người dùng vào Session
        Session::setUser($user);

        return [
            'success'        => true,
            'is_first_login' => ($user['is_first_login'] == 1),
            'user'           => $user,
        ];
    }

    /**
     * Bắt buộc đổi mật khẩu ở lần đăng nhập đầu tiên
     */
    public static function changeFirstLoginPassword(int $userId, string $newPassword, string $confirmPassword): void {
        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException("Mật khẩu mới phải có độ dài tối thiểu từ 6 ký tự trở lên!");
        }
        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException("Mật khẩu xác nhận không khớp!");
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        User::updatePassword($userId, $hash, true);

        // Cập nhật lại session
        $updatedUser = User::findById($userId);
        if ($updatedUser) {
            Session::setUser($updatedUser);
        }
    }

    /**
     * Tự phục vụ cấp lại mật khẩu qua Email (SSPR)
     */
    public static function requestResetToken(string $email): string {
        $email = trim($email);
        $user = User::findByEmailOrCode($email);

        if (!$user) {
            throw new RuntimeException("Không tìm thấy người dùng với email [{$email}]!");
        }

        if ($user['is_active'] == 0 || $user['academic_status'] === 'expelled') {
            throw new RuntimeException("Tài khoản này đã bị khóa kỷ luật, không thể sử dụng tính năng cấp lại mật khẩu!");
        }

        return PasswordReset::createToken($user['id'], $email, 15);
    }

    /**
     * Xác thực Token và Đặt lại mật khẩu mới
     */
    public static function resetPasswordWithToken(string $token, string $newPassword, string $confirmPassword): void {
        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException("Mật khẩu mới phải có tối thiểu 6 ký tự!");
        }
        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException("Mật khẩu xác nhận không trùng khớp!");
        }

        $record = PasswordReset::verifyToken($token);
        if (!$record) {
            throw new RuntimeException("Mã Token khôi phục không hợp lệ hoặc đã hết hạn (quá 15 phút)!");
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        User::updatePassword($record['user_id'], $hash, false);
        PasswordReset::deleteToken($record['id']);
    }
}


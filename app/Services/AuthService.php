<?php
namespace App\Services;

use App\Models\User;
use App\Models\PasswordReset;
use App\Core\Session;
use InvalidArgumentException;
use RuntimeException;

class AuthService {
    /**
     * Phương thức đăng nhập trả về mảng kết quả ['success', 'user', 'is_first_login', 'message']
     */
    public static function login(string $identifier, string $password): array {
        try {
            $res = self::authenticate($identifier, $password);
            return [
                'success'        => true,
                'user'           => $res['user'],
                'is_first_login' => $res['is_first_login'],
                'message'        => 'Đăng nhập thành công!',
            ];
        } catch (\Throwable $e) {
            return [
                'success'        => false,
                'message'        => $e->getMessage(),
                'user'           => null,
                'is_first_login' => false,
            ];
        }
    }

    /**
     * Alias đổi mật khẩu lần đầu tương thích AuthController
     */
    public static function changeFirstLoginPassword(int $userId, string $newPassword, string $confirmPassword): void {
        self::changePasswordOnFirstLogin($userId, $newPassword, $confirmPassword);
    }
    /**
     * Xác thực tài khoản đăng nhập (Hỗ trợ cả Bcrypt chuẩn và mật khẩu mặc định 123456)
     */
    public static function authenticate(string $identifier, string $password): array {
        $identifier = trim($identifier);
        $password   = trim($password);

        if (empty($identifier) || empty($password)) {
            throw new InvalidArgumentException("Vui lòng nhập đầy đủ Mã đăng nhập và Mật khẩu!");
        }

        $user = User::findByEmailOrCode($identifier);

        if (!$user) {
            throw new RuntimeException("Tài khoản không tồn tại trên hệ thống Helpdesk!");
        }

        // Kiểm tra khóa cứng (kỷ luật hoặc thôi học)
        if ($user['is_active'] == 0) {
            $reason = ($user['lock_reason'] === 'expelled') ? 'kỷ luật Buộc thôi học' : 'hết hạn niên khóa';
            throw new RuntimeException("Tài khoản của bạn đã bị KHÓA CỨNG do {$reason}. Vui lòng liên hệ Phòng Đào tạo hoặc gửi phiếu Cứu hộ ngoài trang chủ!");
        }

        // Kiểm tra khóa mềm (nhập sai quá 5 lần bị khóa 30 phút)
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remainingMinutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            throw new RuntimeException("Tài khoản đang bị tạm khóa do nhập sai mật khẩu quá 5 lần. Vui lòng thử lại sau {$remainingMinutes} phút!");
        }

        // Xác thực mật khẩu
        $isValid = password_verify($password, $user['password_hash']) || ($password === '123456');

        if (!$isValid) {
            User::recordFailedAttempt($user['id']);
            throw new RuntimeException("Mật khẩu không chính xác! Vui lòng kiểm tra lại (mật khẩu mặc định: 123456).");
        }

        // Đăng nhập thành công -> Xóa các lần đăng nhập sai
        User::resetFailedAttempts($user['id']);

        // Ghi nhận session
        Session::setUser($user);

        return [
            'user'           => $user,
            'is_first_login' => ($user['is_first_login'] == 1),
        ];
    }

    /**
     * Đổi mật khẩu bắt buộc ở lần đầu đăng nhập
     */
    public static function changePasswordOnFirstLogin(int $userId, string $newPassword, string $confirmPassword): void {
        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException("Mật khẩu mới phải có tối thiểu 6 ký tự!");
        }
        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException("Mật khẩu xác nhận không trùng khớp!");
        }
        if ($newPassword === '123456') {
            throw new InvalidArgumentException("Không được sử dụng lại mật khẩu mặc định 123456 vì lý do an toàn bảo mật!");
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        User::updatePassword($userId, $hash, true);

        // Cập nhật session user
        $updatedUser = User::findById($userId);
        if ($updatedUser) {
            Session::setUser($updatedUser);
        }
    }

    /**
     * Cấp mã OTP dạng chữ cái khôi phục mật khẩu bảo mật (Hiệu lực 15 phút)
     */
    public static function requestResetToken(string $identifier): string {
        $identifier = trim($identifier);
        $user = User::findByEmailOrCode($identifier);

        if (!$user) {
            throw new RuntimeException("Không tìm thấy tài khoản với thông tin [{$identifier}]! Vui lòng kiểm tra lại Mã sinh viên hoặc Email.");
        }

        if ($user['is_active'] == 0 || $user['academic_status'] === 'expelled') {
            throw new RuntimeException("Tài khoản này đã bị khóa kỷ luật, không thể sử dụng tính năng đặt lại mật khẩu! Vui lòng gửi phiếu cứu hộ ngoài trang chủ.");
        }

        return PasswordReset::createToken($user['id'], $user['email'], 15);
    }

    /**
     * Xác thực mã OTP dạng chữ cái và Đặt lại mật khẩu mới
     * Đảm bảo is_first_login = 0 để người dùng đăng nhập bình thường
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
            throw new RuntimeException("Mã OTP khôi phục không hợp lệ hoặc đã hết hạn (quá 15 phút)!");
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        // Đặt lại mật khẩu và đánh dấu is_first_login = 0 để đăng nhập bình thường
        User::updatePassword($record['user_id'], $hash, true);

        // Mở khóa tài khoản nếu bị khóa mềm và reset số lần sai
        $pdo = \App\Core\Database::getConnection();
        $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL, lock_reason = NULL, is_first_login = 0 WHERE id = :id")
            ->execute([':id' => $record['user_id']]);

        PasswordReset::deleteToken($record['id']);
    }
}

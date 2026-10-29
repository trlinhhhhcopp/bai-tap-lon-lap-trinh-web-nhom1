<?php
namespace App\Models;

use App\Core\Database;

class PasswordReset {
    /**
     * Sinh mã OTP ngẫu nhiên gồm các chữ cái in hoa (loại I, O tránh nhầm với 1, 0)
     */
    public static function generateAlphabeticOtp(int $length = 6): string {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $otp = '';
        $max = strlen($letters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $otp .= $letters[random_int(0, $max)];
        }
        return $otp;
    }

    /**
     * Cấp mã OTP dạng chữ cái khôi phục mật khẩu (15 phút)
     */
    public static function createToken(int $userId, string $email, int $lifetimeMinutes = 15): string {
        $pdo = Database::getConnection();

        // Sinh mã OTP dạng chữ cái in hoa (6 ký tự)
        $rawToken = self::generateAlphabeticOtp(6);
        $tokenHash = hash('sha256', strtoupper($rawToken));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$lifetimeMinutes} minutes"));

        // Xóa các token cũ của user này
        $del = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid");
        $del->execute([':uid' => $userId]);

        $ins = $pdo->prepare("INSERT INTO password_resets (user_id, email, token_hash, expires_at) 
                              VALUES (:uid, :email, :hash, :exp)");
        $ins->execute([
            ':uid'   => $userId,
            ':email' => $email,
            ':hash'  => $tokenHash,
            ':exp'   => $expiresAt,
        ]);

        return $rawToken;
    }

    /**
     * Xác thực mã OTP dạng chữ cái (không phân biệt hoa thường)
     */
    public static function verifyToken(string $rawToken): ?array {
        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', strtoupper(trim($rawToken)));
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT pr.*, u.user_code, u.name, u.academic_status, u.is_active
                FROM password_resets pr
                INNER JOIN users u ON u.id = pr.user_id
                WHERE pr.token_hash = :hash 
                  AND pr.expires_at > :now
                  AND u.is_active = 1
                  AND u.academic_status != 'expelled'
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':hash' => $tokenHash,
            ':now'  => $now,
        ]);
        $res = $stmt->fetch();

        return $res ?: null;
    }

    public static function deleteToken(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}

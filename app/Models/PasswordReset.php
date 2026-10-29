<?php
namespace App\Models;

use App\Core\Database;

class PasswordReset {
    public static function createToken(int $userId, string $email, int $lifetimeMinutes = 15): string {
        $pdo = Database::getConnection();

        // Sinh token ngẫu nhiên và băm SHA-256
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$lifetimeMinutes} minutes"));

        // Xóa các token cũ của user này
        $del = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid");
        $del->execute([':uid' => $userId]);

        // Thêm token mới với thời hạn chuẩn ISO tương thích 100% cả MariaDB/MySQL và SQLite
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

    public static function verifyToken(string $rawToken): ?array {
        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', $rawToken);
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


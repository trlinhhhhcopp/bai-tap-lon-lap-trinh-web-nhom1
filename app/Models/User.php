<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT u.*, COALESCE(c.name, 'Chưa xếp lớp') AS class_name 
                               FROM users u 
                               LEFT JOIN classes c ON c.id = u.class_id 
                               WHERE u.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmailOrCode(string $identifier): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users 
                               WHERE email = :id1 OR user_code = :id2 OR personal_email = :id3 
                               LIMIT 1");
        $stmt->execute([':id1' => $identifier, ':id2' => $identifier, ':id3' => $identifier]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByUserCode(string $userCode): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = :code LIMIT 1");
        $stmt->execute([':code' => $userCode]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findActiveUsers(array $filters = [], int $page = 1, int $perPage = 8): array {
        $pdo = Database::getConnection();
        $kw = trim($filters['kw'] ?? '');
        $type = trim($filters['type'] ?? '');
        $classId = (int)($filters['class_id'] ?? 0);

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($kw !== '') {
            $where[] = "(u.user_code LIKE :kw1 OR u.name LIKE :kw2 OR u.email LIKE :kw3)";
            $params[':kw1'] = '%' . $kw . '%';
            $params[':kw2'] = '%' . $kw . '%';
            $params[':kw3'] = '%' . $kw . '%';
        }

        if ($type === 'student') {
            $where[] = "(u.user_type = 'student' OR (u.role = 'requester' AND u.user_type != 'lecturer'))";
        } elseif ($type === 'lecturer') {
            $where[] = "u.user_type = 'lecturer'";
        } elseif ($type === 'agent') {
            $where[] = "u.role = 'agent'";
        } elseif ($type === 'admin') {
            $where[] = "u.role = 'admin'";
        }

        if ($classId > 0) {
            $where[] = "u.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $sql = "SELECT u.*, COALESCE(c.name, 'Chưa xếp lớp') AS class_name
                FROM users u
                LEFT JOIN classes c ON c.id = u.class_id ";

        if (!empty($where)) {
            $sql .= "WHERE " . implode(' AND ', $where) . " ";
        }

        $sql .= "ORDER BY u.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countActiveUsers(array $filters = []): int {
        $pdo = Database::getConnection();
        $kw = trim($filters['kw'] ?? '');
        $type = trim($filters['type'] ?? '');
        $classId = (int)($filters['class_id'] ?? 0);

        $where = [];
        $params = [];

        if ($kw !== '') {
            $where[] = "(u.user_code LIKE :kw1 OR u.name LIKE :kw2 OR u.email LIKE :kw3)";
            $params[':kw1'] = '%' . $kw . '%';
            $params[':kw2'] = '%' . $kw . '%';
            $params[':kw3'] = '%' . $kw . '%';
        }

        if ($type === 'student') {
            $where[] = "(u.user_type = 'student' OR (u.role = 'requester' AND u.user_type != 'lecturer'))";
        } elseif ($type === 'lecturer') {
            $where[] = "u.user_type = 'lecturer'";
        } elseif ($type === 'agent') {
            $where[] = "u.role = 'agent'";
        } elseif ($type === 'admin') {
            $where[] = "u.role = 'admin'";
        }

        if ($classId > 0) {
            $where[] = "u.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $sql = "SELECT COUNT(*) FROM users u ";
        if (!empty($where)) {
            $sql .= "WHERE " . implode(' AND ', $where);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $pdo->query($sql);
        }

        return (int) $stmt->fetchColumn();
    }

    public static function countCountsByType(): array {
        $pdo = Database::getConnection();
        return [
            'all'      => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'student'  => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'student' OR (role = 'requester' AND user_type != 'lecturer')")->fetchColumn(),
            'lecturer' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'lecturer'")->fetchColumn(),
            'agent'    => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'agent'")->fetchColumn(),
        ];
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO users (
                    user_code, name, email, password_hash, role, user_type, 
                    department, phone, class_id, cohort, academic_status, 
                    is_active, lock_reason, is_first_login
                ) VALUES (
                    :user_code, :name, :email, :password_hash, :role, :user_type, 
                    :department, :phone, :class_id, :cohort, :academic_status, 
                    :is_active, 'none', 1
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_code'       => trim($data['user_code']),
            ':name'            => trim($data['name']),
            ':email'           => trim($data['email']),
            ':password_hash'   => $data['password_hash'],
            ':role'            => $data['role'] ?? 'requester',
            ':user_type'       => $data['user_type'] ?? 'student',
            ':department'      => trim($data['department'] ?? 'Khoa: Toán-CNTT'),
            ':phone'           => trim($data['phone'] ?? ''),
            ':class_id'        => !empty($data['class_id']) ? (int)$data['class_id'] : null,
            ':cohort'          => trim($data['cohort'] ?? 'D24'),
            ':academic_status' => $data['academic_status'] ?? 'studying',
            ':is_active'       => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $sql = "UPDATE users SET 
                    name = :name, 
                    email = :email, 
                    phone = :phone, 
                    department = :department, 
                    class_id = :class_id 
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':name'       => trim($data['name']),
            ':email'      => trim($data['email']),
            ':phone'      => trim($data['phone'] ?? ''),
            ':department' => trim($data['department'] ?? ''),
            ':class_id'   => !empty($data['class_id']) ? (int)$data['class_id'] : null,
            ':id'         => $id,
        ]);
    }

    public static function updateAvatar(int $id, string $avatarPath): bool {
        $pdo = Database::getConnection();
        $sql = "UPDATE users SET avatar_path = :avatar WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':avatar' => $avatarPath,
            ':id'     => $id,
        ]);
    }

    public static function updatePassword(int $id, string $newPasswordHash, bool $markFirstLoginDone = true): bool {
        $pdo = Database::getConnection();
        $sql = "UPDATE users SET password_hash = :hash, is_first_login = :first WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':hash'  => $newPasswordHash,
            ':first' => $markFirstLoginDone ? 0 : 1,
            ':id'    => $id,
        ]);
    }

    public static function recordFailedAttempt(int $id, int $maxAttempts = 5, int $lockMinutes = 30): void {
        $pdo = Database::getConnection();
        $user = self::findById($id);
        if (!$user) return;

        $newAttempts = $user['failed_attempts'] + 1;
        if ($newAttempts >= $maxAttempts) {
            $lockUntil = date('Y-m-d H:i:s', strtotime("+{$lockMinutes} minutes"));
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = :att, locked_until = :lock, lock_reason = 'brute_force' WHERE id = :id");
            $stmt->execute([':att' => $newAttempts, ':lock' => $lockUntil, ':id' => $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = :att WHERE id = :id");
            $stmt->execute([':att' => $newAttempts, ':id' => $id]);
        }
    }

    public static function resetFailedAttempts(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function toggleLock(int $id): array {
        $pdo = Database::getConnection();
        $user = self::findById($id);
        if (!$user) {
            throw new \RuntimeException("Không tìm thấy người dùng!");
        }

        $newActive = ($user['is_active'] == 1) ? 0 : 1;
        $lockReason = ($newActive == 0) ? 'expelled' : 'none';

        $stmt = $pdo->prepare("UPDATE users SET is_active = :is_active, lock_reason = :lock_reason WHERE id = :id");
        $stmt->execute([
            ':is_active'   => $newActive,
            ':lock_reason' => $lockReason,
            ':id'          => $id,
        ]);

        return [
            'is_active' => $newActive,
            'message'   => ($newActive == 1) ? 'Đã mở khóa tài khoản thành công' : 'Đã khóa tài khoản thành công',
        ];
    }
}

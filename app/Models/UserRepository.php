<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
 * ------------------------------------------------------------------------------
 * PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
 * SINH VIÊN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
 * ------------------------------------------------------------------------------
 * TẬP TIN: TranLinh/UserRepository.php (Repository Function đơn giản - Slide 9 & 10)
 * ==============================================================================
 */

require_once __DIR__ . '/../../config/database.php';

class UserRepository {

    /**
     * Lấy danh sách người dùng có Tìm kiếm tương đối (%) và Phân trang (Slide 6 & 11)
     * 
     * @param array $filters ['kw' => 'từ khóa']
     * @param int $page Số trang hiện tại (mặc định 1)
     * @param int $perPage Số lượng người dùng trên 1 trang (mặc định 8)
     * @return array Danh sách mảng kết hợp người dùng
     */
    public static function findActiveUsers(array $filters = [], int $page = 1, int $perPage = 8): array {
        $pdo = db();
        $kw = trim($filters['kw'] ?? '');

        // Tính vị trí offset phân trang: (page - 1) * perPage (Slide 11)
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Câu truy vấn Prepared Statement nối bảng classes lấy tên lớp (Slide 5-6)
        $sql = "SELECT 
                    u.id, 
                    u.user_code, 
                    u.name, 
                    u.email, 
                    u.role, 
                    u.user_type, 
                    u.cohort, 
                    u.academic_status, 
                    u.is_active, 
                    u.lock_reason, 
                    u.is_first_login,
                    COALESCE(c.name, 'Chưa xếp lớp') AS class_name
                FROM users u
                LEFT JOIN classes c ON c.id = u.class_id ";

        // Điều kiện tìm kiếm gần đúng với LIKE :kw (Slide 6)
        if ($kw !== '') {
            $sql .= "WHERE u.user_code LIKE :kw OR u.name LIKE :kw OR u.email LIKE :kw ";
        }

        $sql .= "ORDER BY u.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);

        // Ràng buộc tham số an toàn, chống SQL Injection
        if ($kw !== '') {
            $stmt->bindValue(':kw', '%' . $kw . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số người dùng thỏa mãn bộ lọc để tính tổng số trang (Slide 10 & 11)
     * 
     * @param array $filters ['kw' => 'từ khóa']
     * @return int Tổng số bản ghi
     */
    public static function countActiveUsers(array $filters = []): int {
        $pdo = db();
        $kw = trim($filters['kw'] ?? '');

        $sql = "SELECT COUNT(*) FROM users u ";
        if ($kw !== '') {
            $sql .= "WHERE u.user_code LIKE :kw OR u.name LIKE :kw OR u.email LIKE :kw";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':kw' => '%' . $kw . '%']);
        } else {
            $stmt = $pdo->query($sql);
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy chi tiết thông tin 1 người dùng theo ID (Slide 7 & 10)
     * 
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array {
        $pdo = db();
        $sql = "SELECT 
                    u.*,
                    COALESCE(c.class_code, 'N/A') AS class_code,
                    COALESCE(c.name, 'Không thuộc lớp sinh hoạt') AS class_name,
                    COALESCE(cat.name, 'Không phân bổ chuyên môn') AS specialty_name
                FROM users u
                LEFT JOIN classes c ON c.id = u.class_id
                LEFT JOIN categories cat ON cat.id = u.specialty_category_id
                WHERE u.id = :id 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Tra cứu người dùng theo mã định danh (Mã SV / Mã GV / Mã KTV)
     */
    public static function findByUserCode(string $userCode): ?array {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = :user_code LIMIT 1");
        $stmt->execute([':user_code' => trim($userCode)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Thêm mới người dùng (Create - Slide 8)
     * Sử dụng Prepared Statement và băm mật khẩu Bcrypt khởi tạo 123456
     * 
     * @param array $data
     * @return int ID người dùng vừa được tạo (lastInsertId)
     * @throws InvalidArgumentException | RuntimeException
     */
    public static function create(array $data): int {
        $pdo = db();

        // 1. Validate dữ liệu đầu vào bắt buộc
        $userCode = trim($data['user_code'] ?? '');
        $name     = trim($data['name'] ?? '');
        $email    = trim($data['email'] ?? '');
        $role     = $data['role'] ?? 'requester';
        $userType = $data['user_type'] ?? 'student';
        $classId  = !empty($data['class_id']) ? (int)$data['class_id'] : null;
        $cohort   = trim($data['cohort'] ?? 'D2024');

        if (empty($userCode) || empty($name) || empty($email)) {
            throw new InvalidArgumentException("Vui lòng điền đầy đủ Mã người dùng, Họ tên và Email!");
        }

        // 2. Kiểm tra trùng lặp Mã người dùng và Email (Đảm bảo ràng buộc UNIQUE)
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE user_code = :user_code OR email = :email LIMIT 1");
        $checkStmt->execute([':user_code' => $userCode, ':email' => $email]);
        if ($checkStmt->fetch()) {
            throw new RuntimeException("Mã định danh hoặc Email này đã tồn tại trong hệ thống!");
        }

        // 3. Khởi tạo mật khẩu mặc định 123456 băm Bcrypt chuẩn PHP
        $rawPassword = !empty($data['password']) ? $data['password'] : '123456';
        $passwordHash = password_hash($rawPassword, PASSWORD_BCRYPT);

        // 4. Thực thi Prepared Statement INSERT (Slide 8)
        $sql = "INSERT INTO users (
                    user_code, name, email, password_hash, role, user_type, 
                    department, phone, class_id, cohort, academic_status, 
                    is_active, lock_reason, is_first_login
                ) VALUES (
                    :user_code, :name, :email, :password_hash, :role, :user_type, 
                    :department, :phone, :class_id, :cohort, :academic_status, 
                    1, 'none', 1
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_code'       => $userCode,
            ':name'            => $name,
            ':email'           => $email,
            ':password_hash'   => $passwordHash,
            ':role'            => $role,
            ':user_type'       => $userType,
            ':department'      => trim($data['department'] ?? 'Khoa: Toán-CNTT'),
            ':phone'           => trim($data['phone'] ?? ''),
            ':class_id'        => $classId,
            ':cohort'          => $cohort,
            ':academic_status' => 'studying',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin người dùng (Update - Slide 7)
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool {
        $pdo = db();

        $name       = trim($data['name'] ?? '');
        $email      = trim($data['email'] ?? '');
        $phone      = trim($data['phone'] ?? '');
        $department = trim($data['department'] ?? '');
        $classId    = !empty($data['class_id']) ? (int)$data['class_id'] : null;

        if (empty($name) || empty($email)) {
            throw new InvalidArgumentException("Họ tên và Email không được để trống!");
        }

        $sql = "UPDATE users SET 
                    name = :name, 
                    email = :email, 
                    phone = :phone, 
                    department = :department, 
                    class_id = :class_id 
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':name'       => $name,
            ':email'      => $email,
            ':phone'      => $phone,
            ':department' => $department,
            ':class_id'   => $classId,
            ':id'         => $id,
        ]);
    }

    /**
     * Khóa hoặc Mở khóa tài khoản bằng POST (Delete/Lock - Slide 15)
     * Tiêu chí: "Xóa/khóa bằng POST, không xóa cứng làm mất toàn vẹn CSDL"
     * 
     * @param int $id
     * @return array ['is_active' => int, 'status_text' => string]
     */
    public static function toggleLock(int $id): array {
        $pdo = db();
        $user = self::findById($id);
        if (!$user) {
            throw new RuntimeException("Không tìm thấy người dùng!");
        }

        // Đảo ngược trạng thái hoạt động
        $newActive = ($user['is_active'] == 1) ? 0 : 1;
        $lockReason = ($newActive == 0) ? 'expelled' : 'none';

        $stmt = $pdo->prepare("UPDATE users SET is_active = :is_active, lock_reason = :lock_reason WHERE id = :id");
        $stmt->execute([
            ':is_active'   => $newActive,
            ':lock_reason' => $lockReason,
            ':id'          => $id,
        ]);

        return [
            'is_active'   => $newActive,
            'status_text' => ($newActive == 1) ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản thành công',
        ];
    }

    /**
     * Lấy danh mục tất cả lớp học để đưa vào thẻ <select> form
     */
    public static function getClasses(): array {
        $pdo = db();
        return $pdo->query("SELECT id, class_code, name FROM classes ORDER BY class_code ASC")->fetchAll();
    }

    /**
     * Lấy danh mục dịch vụ CNTT
     */
    public static function getCategories(): array {
        $pdo = db();
        return $pdo->query("SELECT id, code, name FROM categories ORDER BY id ASC")->fetchAll();
    }
}


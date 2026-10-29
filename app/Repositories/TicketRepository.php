<?php
// ==============================================================================
// BÀI 5: REPOSITORY FUNCTIONS (SLIDE 9 & 10)
// THỰC THỂ: TICKETS (PHIẾU YÊU CẦU HỖ TRỢ CNTT)
// Trưởng nhóm phụ trách: Phạm Thị Thanh Ngân (224001736 - D24-CNTT1)
// ==============================================================================

require_once __DIR__ . '/../../config/database.php';

class TicketRepository {

    /**
     * Lấy danh sách phiếu kèm tìm kiếm và phân trang an toàn (Slide 6 & 11)
     * Công thức: offset = (page - 1) * perPage
     */
    public static function findAll(
        string $keyword = '',
        int $page = 1,
        int $perPage = 5,
        string $status = '',
        int $categoryId = 0
    ): array {
        $db = db();
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        // 1. Tìm kiếm theo từ khóa (Mã vé hoặc Tiêu đề)
        if (!empty($keyword)) {
            $conditions[] = "(t.ticket_code LIKE :kw OR t.title LIKE :kw OR t.description LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        // 2. Lọc theo trạng thái
        if (!empty($status)) {
            $conditions[] = "t.status = :status";
            $params['status'] = $status;
        }

        // 3. Lọc theo danh mục dịch vụ
        if ($categoryId > 0) {
            $conditions[] = "t.category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT t.*, 
                       u_req.name AS requester_name, u_req.user_code AS requester_code,
                       u_agent.name AS agent_name,
                       c.name AS category_name, c.code AS category_code,
                       p.name AS priority_name, p.code AS priority_code
                FROM tickets t
                INNER JOIN users u_req ON t.requester_id = u_req.id
                LEFT JOIN users u_agent ON t.assigned_to = u_agent.id
                INNER JOIN categories c ON t.category_id = c.id
                INNER JOIN priorities p ON t.priority_id = p.id
                {$whereClause}
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);

        // Bind tham số điều kiện
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }

        // Bind LIMIT và OFFSET phải là kiểu INT (Slide 11)
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số phiếu thỏa mãn điều kiện để tính tổng số trang (Slide 11)
     */
    public static function countAll(string $keyword = '', string $status = '', int $categoryId = 0): int {
        $db = db();
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(ticket_code LIKE :kw OR title LIKE :kw OR description LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }
        if (!empty($status)) {
            $conditions[] = "status = :status";
            $params['status'] = $status;
        }
        if ($categoryId > 0) {
            $conditions[] = "category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT COUNT(*) FROM tickets {$whereClause}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy chi tiết một phiếu theo ID (Slide 7 - Detail)
     */
    public static function findById(int $id): ?array {
        $sql = "SELECT t.*, 
                       u_req.name AS requester_name, u_req.user_code AS requester_code, u_req.email AS requester_email,
                       u_agent.name AS agent_name, u_agent.user_code AS agent_code,
                       c.name AS category_name, c.code AS category_code,
                       p.name AS priority_name, p.code AS priority_code,
                       cl.class_code, cl.name AS class_name,
                       co.course_code, co.name AS course_name
                FROM tickets t
                INNER JOIN users u_req ON t.requester_id = u_req.id
                LEFT JOIN users u_agent ON t.assigned_to = u_agent.id
                INNER JOIN categories c ON t.category_id = c.id
                INNER JOIN priorities p ON t.priority_id = p.id
                LEFT JOIN classes cl ON t.class_id = cl.id
                LEFT JOIN courses co ON t.course_id = co.id
                WHERE t.id = :id";

        $stmt = db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Thêm mới một phiếu hỗ trợ (Slide 8 - Demo INSERT)
     * Sinh mã ticket_code tự động dạng HD-2026-xxxxx
     */
    public static function create(array $data): int {
        $db = db();

        // Tự sinh mã phiếu ngẫu nhiên HD-2026-xxxxx
        $ticketCode = 'HD-2026-' . str_pad((string)mt_rand(100, 99999), 5, '0', STR_PAD_LEFT);

        // Lấy SLA của danh mục để tính hạn deadline tự động
        $catStmt = $db->prepare("SELECT sla_hours FROM categories WHERE id = :id");
        $catStmt->execute(['id' => (int)$data['category_id']]);
        $slaHours = (float)($catStmt->fetchColumn() ?: 4.0);

        // Lấy hệ số của mức ưu tiên
        $prioStmt = $db->prepare("SELECT sla_factor FROM priorities WHERE id = :id");
        $prioStmt->execute(['id' => (int)$data['priority_id']]);
        $slaFactor = (float)($prioStmt->fetchColumn() ?: 1.0);

        // Tính số phút xử lý thực tế
        $effectiveMinutes = (int)round($slaHours * $slaFactor * 60);

        $sql = "INSERT INTO tickets (
                    ticket_code, requester_id, category_id, priority_id, 
                    title, description, system_affected, 
                    status, created_at, sla_started_at, triage_due_at, due_at
                ) VALUES (
                    :ticket_code, :requester_id, :category_id, :priority_id,
                    :title, :description, :system_affected,
                    'new', NOW(), NOW(),
                    DATE_ADD(NOW(), INTERVAL 30 MINUTE),
                    DATE_ADD(NOW(), INTERVAL :eff_min MINUTE)
                )";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'ticket_code'     => $ticketCode,
            'requester_id'    => (int)$data['requester_id'],
            'category_id'     => (int)$data['category_id'],
            'priority_id'     => (int)$data['priority_id'],
            'title'           => trim($data['title']),
            'description'     => trim($data['description']),
            'system_affected' => trim($data['system_affected']),
            'eff_min'         => $effectiveMinutes,
        ]);

        return (int) $db->lastInsertId();
    }

    /**
     * Cập nhật thông tin phiếu (Slide 7 - Edit)
     */
    public static function update(int $id, array $data): bool {
        $fields = [
            'title = :title',
            'description = :description',
            'system_affected = :system_affected',
            'category_id = :category_id',
            'priority_id = :priority_id',
            'assigned_to = :assigned_to',
        ];
        $params = [
            'id'              => $id,
            'title'           => trim($data['title']),
            'description'     => trim($data['description']),
            'system_affected' => trim($data['system_affected']),
            'category_id'     => (int)$data['category_id'],
            'priority_id'     => (int)$data['priority_id'],
            'assigned_to'     => !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null,
        ];

        // Nếu có cập nhật trạng thái
        if (!empty($data['status'])) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
            if ($data['status'] === 'resolved') {
                $fields[] = 'resolved_at = IF(resolved_at IS NULL, NOW(), resolved_at)';
            } elseif ($data['status'] === 'closed') {
                $fields[] = 'closed_at = IF(closed_at IS NULL, NOW(), closed_at)';
            }
        }

        $sql = "UPDATE tickets SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = db()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Cập nhật trạng thái phiếu (Slide 7)
     */
    public static function updateStatus(int $id, string $status, ?string $closedReason = null): bool {
        $fields = ['status = :status'];
        $params = [
            'id'     => $id,
            'status' => $status,
        ];

        if ($status === 'resolved') {
            $fields[] = 'resolved_at = IF(resolved_at IS NULL, NOW(), resolved_at)';
        } elseif ($status === 'closed') {
            $fields[] = 'resolved_at = IF(resolved_at IS NULL, NOW(), resolved_at)';
            $fields[] = 'closed_at = IF(closed_at IS NULL, NOW(), closed_at)';
            $fields[] = 'closed_reason = :closed_reason';
            $params['closed_reason'] = $closedReason ?: 'completed';
        }

        $sql = "UPDATE tickets SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = db()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Hủy/Xóa phiếu (Slide 7 - Delete bằng POST)
     */
    public static function delete(int $id): bool {
        $sql = "DELETE FROM tickets WHERE id = :id";
        $stmt = db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Lấy các tin nhắn phản hồi của phiếu (Slide 7 & 10)
     * - $canViewInternal: Nếu false (Sinh viên), ẩn hoàn toàn các dòng ghi chú nội bộ (is_internal = 1)
     */
    public static function getReplies(int $ticketId, bool $canViewInternal = true): array {
        if (class_exists('App\Models\Reply')) {
            return \App\Models\Reply::getByTicketId($ticketId, $canViewInternal);
        }

        $sql = "SELECT r.*, u.name AS user_name, u.role AS user_role, u.user_code
                FROM ticket_replies r
                JOIN users u ON r.user_id = u.id
                WHERE r.ticket_id = :ticket_id";
        if (!$canViewInternal) {
            $sql .= " AND r.is_internal = 0";
        }
        $sql .= " ORDER BY r.created_at ASC, r.id ASC";

        $stmt = db()->prepare($sql);
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    /**
     * Thêm tin nhắn phản hồi, hỗ trợ đính kèm ảnh và tự động luân chuyển trạng thái vé (State Machine)
     */
    public static function addReply(
        int $ticketId, 
        int $userId, 
        string $message, 
        bool $isInternal = false, 
        ?string $forceStatus = null,
        ?string $attachmentPath = null
    ): bool {
        $db = db();

        // 1. Thêm phản hồi vào bảng ticket_replies
        $sql = "INSERT INTO ticket_replies (ticket_id, user_id, message, attachment_path, is_internal, created_at)
                VALUES (:ticket_id, :user_id, :message, :attachment_path, :is_internal, NOW())";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            'ticket_id'       => $ticketId,
            'user_id'         => $userId,
            'message'         => trim($message),
            'attachment_path' => $attachmentPath,
            'is_internal'     => $isInternal ? 1 : 0,
        ]);

        if (!$ok) {
            return false;
        }

        // 2. Tra cứu vai trò của người gửi & trạng thái hiện tại của vé
        $userStmt = $db->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
        $userStmt->execute(['id' => $userId]);
        $userRole = $userStmt->fetchColumn() ?: 'requester';

        $ticketStmt = $db->prepare("SELECT status, first_response_at FROM tickets WHERE id = :id LIMIT 1");
        $ticketStmt->execute(['id' => $ticketId]);
        $ticket = $ticketStmt->fetch();

        if (!$ticket) {
            return true;
        }

        // 3. Xử lý Logic tự động State Machine
        // Nếu người gửi chọn ép trạng thái cụ thể (ví dụ giải quyết xong -> resolved)
        if (!empty($forceStatus) && in_array($forceStatus, ['new', 'in_progress', 'waiting_requester', 'resolved', 'closed'])) {
            self::updateStatus($ticketId, $forceStatus);
        } else {
            // Logic tự động thông minh:
            if (in_array($userRole, ['agent', 'admin']) && !$isInternal) {
                // KTV phản hồi công khai -> Ghi nhận mốc phản hồi đầu tiên (First Response SLA)
                if (empty($ticket['first_response_at'])) {
                    $db->prepare("UPDATE tickets SET first_response_at = NOW() WHERE id = :id")->execute(['id' => $ticketId]);
                }
                // Nếu vé đang 'new' hoặc 'in_progress' -> Chuyển sang 'waiting_requester' (Chờ người gửi kiểm tra)
                if (in_array($ticket['status'], ['new', 'in_progress'])) {
                    self::updateStatus($ticketId, 'waiting_requester');
                }
            } elseif ($userRole === 'requester') {
                // Người gửi (Sinh viên/Giảng viên) phản hồi lại -> Kích hoạt vé quay lại 'in_progress' để KTV xử lý tiếp
                if (in_array($ticket['status'], ['waiting_requester', 'resolved'])) {
                    self::updateStatus($ticketId, 'in_progress');
                }
            }
        }

        return true;
    }

    // Các hàm tra cứu danh mục phục vụ Select Form
    public static function getCategories(): array {
        return db()->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
    }

    public static function getPriorities(): array {
        return db()->query("SELECT * FROM priorities ORDER BY sort_order ASC")->fetchAll();
    }

    public static function getUsers(string $role = ''): array {
        if (!empty($role)) {
            $stmt = db()->prepare("SELECT id, user_code, name, role, user_type FROM users WHERE role = :role AND is_active = 1 ORDER BY id ASC");
            $stmt->execute(['role' => $role]);
            return $stmt->fetchAll();
        }
        return db()->query("SELECT id, user_code, name, role, user_type FROM users WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
    }
}

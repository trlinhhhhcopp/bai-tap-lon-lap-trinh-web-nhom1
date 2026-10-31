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
        int $categoryId = 0,
        int $requesterId = 0,
        int $assignedTo = 0,
        $hasWarning = false
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
        if ($requesterId > 0) {
            $conditions[] = "t.requester_id = :req_id";
            $params['req_id'] = $requesterId;
        }
        if ($assignedTo > 0) {
            $conditions[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }

                if ($hasWarning) {
            if ($hasWarning === 'sla') {
                $conditions[] = "t.status NOT IN ('resolved', 'closed') AND ((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.triage_due_at IS NOT NULL AND t.triage_due_at < NOW() AND t.assigned_to IS NULL))";
            } elseif ($hasWarning === 'violation') {
                $conditions[] = "t.is_spam = 1";
            } else {
                $conditions[] = "(
                    (t.status NOT IN ('resolved', 'closed')
                    AND (
                        (t.due_at IS NOT NULL AND t.due_at < NOW())
                        OR (t.triage_due_at IS NOT NULL AND t.triage_due_at < NOW() AND t.assigned_to IS NULL)
                        OR (t.due_at IS NOT NULL AND TIMESTAMPDIFF(MINUTE, NOW(), t.due_at) BETWEEN 0 AND 60)
                        OR (p.code IN ('urgent', 'high'))
                        OR (t.title LIKE '%[CỨU HỘ KHẨN CẤP]%')
                        OR (t.reopen_count > 0)
                    ))
                    OR (t.is_spam = 1)
                )";
            }
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
    public static function countAll(
        string $keyword = '',
        string $status = '',
        int $categoryId = 0,
        int $requesterId = 0,
        int $assignedTo = 0,
        $hasWarning = false
    ): int {
        $db = db();
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(t.ticket_code LIKE :kw OR t.title LIKE :kw OR t.description LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }
        if (!empty($status)) {
            $conditions[] = "t.status = :status";
            $params['status'] = $status;
        }
        if ($categoryId > 0) {
            $conditions[] = "t.category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }
        if ($requesterId > 0) {
            $conditions[] = "t.requester_id = :req_id";
            $params['req_id'] = $requesterId;
        }
        if ($assignedTo > 0) {
            $conditions[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }
        if ($hasWarning) {
            if ($hasWarning === 'sla') {
                $conditions[] = "t.status NOT IN ('resolved', 'closed') AND ((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.triage_due_at IS NOT NULL AND t.triage_due_at < NOW() AND t.assigned_to IS NULL))";
            } elseif ($hasWarning === 'violation') {
                $conditions[] = "t.is_spam = 1";
            } else {
                $conditions[] = "(
                    (t.status NOT IN ('resolved', 'closed')
                    AND (
                        (t.due_at IS NOT NULL AND t.due_at < NOW())
                        OR (t.triage_due_at IS NOT NULL AND t.triage_due_at < NOW() AND t.assigned_to IS NULL)
                        OR (t.due_at IS NOT NULL AND TIMESTAMPDIFF(MINUTE, NOW(), t.due_at) BETWEEN 0 AND 60)
                        OR (p.code IN ('urgent', 'high'))
                        OR (t.title LIKE '%[CỨU HỘ KHẨN CẤP]%')
                        OR (t.reopen_count > 0)
                    ))
                    OR (t.is_spam = 1)
                )";
            }
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT COUNT(*) FROM tickets t INNER JOIN priorities p ON t.priority_id = p.id {$whereClause}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Đếm số phiếu đang có cảnh báo khẩn cấp / quá hạn SLA
     */
    /**
     * Đếm số phiếu vi phạm do KTV báo cáo (is_spam = 1) để Admin xem xét
     */
    public static function countViolations(int $requesterId = 0, int $assignedTo = 0, bool $isLecturer = false): int {
        $db = db();
        $conditions = ["t.is_spam = 1"];
        $params = [];
        if ($requesterId > 0 && !$isLecturer) {
            $conditions[] = "t.requester_id = :req_id";
            $params['req_id'] = $requesterId;
        }
        if ($assignedTo > 0) {
            $conditions[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }
        $where = 'WHERE ' . implode(' AND ', $conditions);
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets t {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Đếm số phiếu quá hạn SLA nghiêm trọng
     */
    public static function countSlaOverdue(int $requesterId = 0, int $assignedTo = 0): int {
        $db = db();
        $conditions = [
            "t.status NOT IN ('resolved', 'closed')",
            "((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.triage_due_at IS NOT NULL AND t.triage_due_at < NOW() AND t.assigned_to IS NULL))"
        ];
        $params = [];
        if ($requesterId > 0) {
            $conditions[] = "t.requester_id = :req_id";
            $params['req_id'] = $requesterId;
        }
        if ($assignedTo > 0) {
            $conditions[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }
        $where = 'WHERE ' . implode(' AND ', $conditions);
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets t {$where}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function countWarnings(int $requesterId = 0, int $assignedTo = 0): int {
        $db = db();
        $conditions = [
            "t.status NOT IN ('resolved', 'closed')",
            "(
                (t.due_at IS NOT NULL AND t.due_at < NOW())
                OR (t.triage_due_at IS NOT NULL AND t.triage_due_at < NOW() AND t.assigned_to IS NULL)
                OR (t.due_at IS NOT NULL AND TIMESTAMPDIFF(MINUTE, NOW(), t.due_at) BETWEEN 0 AND 60)
                OR (p.code IN ('urgent', 'high'))
                OR (t.title LIKE '%[CỨU HỘ KHẨN CẤP]%')
                OR (t.reopen_count > 0)
            )"
        ];
        $params = [];
        if ($requesterId > 0) {
            $conditions[] = "t.requester_id = :req_id";
            $params['req_id'] = $requesterId;
        }
        if ($assignedTo > 0) {
            $conditions[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) FROM tickets t INNER JOIN priorities p ON t.priority_id = p.id {$whereClause}";
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
                       COALESCE(cl.class_code, u_cl.class_code, u_req.cohort, 'D24-CNTT1') AS class_code,
                       COALESCE(cl.name, u_cl.name, 'Đại học CNTT K24') AS class_name,
                       co.course_code, co.name AS course_name,
                       cln.lesson_number, cln.title AS lesson_title, cln.deadline_at AS lesson_deadline_at, cln.assignment_requirement AS lesson_requirement
                FROM tickets t
                INNER JOIN users u_req ON t.requester_id = u_req.id
                LEFT JOIN users u_agent ON t.assigned_to = u_agent.id
                INNER JOIN categories c ON t.category_id = c.id
                INNER JOIN priorities p ON t.priority_id = p.id
                LEFT JOIN classes cl ON t.class_id = cl.id
                LEFT JOIN classes u_cl ON u_req.class_id = u_cl.id
                LEFT JOIN courses co ON t.course_id = co.id
                LEFT JOIN course_lessons cln ON t.lesson_id = cln.id
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
        /**
     * Sinh mã phiếu chuẩn hóa theo ngày: HD - YYYY - DDMM - xxxxxx
     * Đuôi 6 ký tự gồm 4 chữ số thứ tự và 2 chữ cái ngẫu nhiên đan xen (Ví dụ: HD-2026-2910-L0H004)
     */
    public static function generateTicketCode(int $seq = 0, ?string $date = null): string {
        $db = db();
        if ($seq <= 0) {
            $maxId = (int)$db->query("SELECT MAX(id) FROM tickets")->fetchColumn();
            $seq = $maxId + 1;
        }
        $ts = $date ? strtotime($date) : time();
        $year = date('Y', $ts);
        $dayMonth = date('dm', $ts); // e.g. 2910

        // 4 chữ số thứ tự (ví dụ: 0004)
        $numStr = str_pad((string)($seq % 10000), 4, '0', STR_PAD_LEFT);
        $digits = str_split($numStr);

        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $c1 = $letters[random_int(0, strlen($letters) - 1)];
        $c2 = $letters[random_int(0, strlen($letters) - 1)];

        // Chọn 2 vị trí ngẫu nhiên trong 6 vị trí (0..5) để đặt 2 chữ cái
        $positions = [0, 1, 2, 3, 4, 5];
        shuffle($positions);
        $pos = array_slice($positions, 0, 2);
        sort($pos);

        $chars = array_fill(0, 6, '');
        $chars[$pos[0]] = $c1;
        $chars[$pos[1]] = $c2;

        $dIdx = 0;
        for ($i = 0; $i < 6; $i++) {
            if ($chars[$i] === '') {
                $chars[$i] = $digits[$dIdx++];
            }
        }

        $suffix = implode('', $chars);
        return "HD-{$year}-{$dayMonth}-{$suffix}";
    }
    public static function create(array $data): int {
        $db = db();

        // Tự sinh mã phiếu bảo mật theo định dạng chuẩn HD-YYYY-DDMM-xxxBxxd
        $ticketCode = !empty($data['ticket_code']) ? $data['ticket_code'] : self::generateTicketCode();

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
        $attachmentPath   = !empty($data['attachment_path']) ? $data['attachment_path'] : null;

        // Cơ chế Smart Auto-Dispatch: Tự động phân công KTV chuyên trách (hoặc tràn tải nếu >= 15 phiếu)
        $assignedTo = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null;
        if (empty($assignedTo)) {
            require_once __DIR__ . '/../Services/TicketService.php';
            try {
                $dispatch = \App\Services\TicketService::dispatchAgent((int)$data['category_id']);
                if (!empty($dispatch['agent_id'])) {
                    $assignedTo = (int)$dispatch['agent_id'];
                }
            } catch (\Throwable $e) {
                $assignedTo = null;
            }
        }

        $courseId = !empty($data['course_id']) ? (int)$data['course_id'] : null;
        $lessonId = !empty($data['lesson_id']) ? (int)$data['lesson_id'] : null;

        $sql = "INSERT INTO tickets (
                    ticket_code, requester_id, assigned_to, category_id, priority_id, 
                    course_id, lesson_id,
                    title, description, system_affected, attachment_path,
                    status, created_at, sla_started_at, triage_due_at, due_at
                ) VALUES (
                    :ticket_code, :requester_id, :assigned_to, :category_id, :priority_id,
                    :course_id, :lesson_id,
                    :title, :description, :system_affected, :attachment_path,
                    'new', NOW(), NOW(),
                    DATE_ADD(NOW(), INTERVAL 30 MINUTE),
                    DATE_ADD(NOW(), INTERVAL :eff_min MINUTE)
                )";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'ticket_code'     => $ticketCode,
            'requester_id'    => (int)$data['requester_id'],
            'assigned_to'     => $assignedTo,
            'category_id'     => (int)$data['category_id'],
            'priority_id'     => (int)$data['priority_id'],
            'course_id'       => $courseId,
            'lesson_id'       => $lessonId,
            'title'           => trim($data['title']),
            'description'     => trim($data['description']),
            'system_affected' => trim($data['system_affected']),
            'attachment_path' => $attachmentPath,
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

    /**
     * Tự động đóng hoàn tất các phiếu đã giải quyết (resolved) quá 24h mà sinh viên chưa đánh giá CSAT
     */
    public static function autoCloseExpiredResolvedTickets(): int {
        try {
            $db = db();
            $sql = "UPDATE tickets 
                    SET status = 'closed', 
                        closed_at = NOW(), 
                        closed_reason = 'auto_closed_24h' 
                    WHERE status = 'resolved' 
                      AND resolved_at IS NOT NULL 
                      AND resolved_at <= NOW() - INTERVAL 24 HOUR";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return (int)$stmt->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }

}

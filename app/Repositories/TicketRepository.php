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
        $hasWarning = false,
        int $lecturerId = 0,
        string $lecturerScope = ''
    ): array {
        $db = db();
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        // 1. Tìm kiếm theo từ khóa (Mã vé hoặc Tiêu đề)
        if (!empty($keyword)) {
            $conditions[] = "(t.ticket_code LIKE :kw1 OR t.title LIKE :kw2 OR t.description LIKE :kw3)";
            $kwVal = '%' . $keyword . '%';
            $params['kw1'] = $kwVal;
            $params['kw2'] = $kwVal;
            $params['kw3'] = $kwVal;
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
        if ($lecturerId > 0) {
            if ($lecturerScope === 'mine') {
                $conditions[] = "t.requester_id = :lid_req";
                $params['lid_req'] = $lecturerId;
            } elseif ($lecturerScope === 'course_incidents') {
                // Chỉ hiển thị các phiếu mà KTV đã xác thực có lỗi kỹ thuật (it_verified = 1) và chưa được GV xử lý
                $conditions[] = "(t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending') AND t.status NOT IN ('closed', 'resolved') AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2)))";
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            } else {
                $conditions[] = "(t.requester_id = :lid_req OR (t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2))))";
                $params['lid_req'] = $lecturerId;
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            }
        } elseif ($requesterId > 0) {
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
            } elseif ($hasWarning === 'guest') {
                $conditions[] = "t.is_guest_ticket = 1";
            } elseif ($hasWarning === 'unrated') {
                $conditions[] = "t.status IN ('resolved', 'closed') AND r.id IS NULL";
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
                       COALESCE(u_req.name, t.contact_email, 'Khách gửi cứu hộ') AS requester_name, 
                       COALESCE(u_req.user_code, 'GUEST') AS requester_code,
                       u_agent.name AS agent_name,
                       COALESCE(c.name, 'Cứu hộ tài khoản') AS category_name, 
                       COALESCE(c.code, 'CAT_SSO') AS category_code,
                       COALESCE(p.name, 'Cao') AS priority_name, 
                       COALESCE(p.code, 'HIGH') AS priority_code,
                       r.id AS rating_id,
                       r.score AS rating_score
                FROM tickets t
                LEFT JOIN users u_req ON t.requester_id = u_req.id
                LEFT JOIN users u_agent ON t.assigned_to = u_agent.id
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN priorities p ON t.priority_id = p.id
                LEFT JOIN ticket_ratings r ON t.id = r.ticket_id
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
        $hasWarning = false,
        int $lecturerId = 0,
        string $lecturerScope = ''
    ): int {
        $db = db();
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(t.ticket_code LIKE :kw1 OR t.title LIKE :kw2 OR t.description LIKE :kw3)";
            $kwVal = '%' . $keyword . '%';
            $params['kw1'] = $kwVal;
            $params['kw2'] = $kwVal;
            $params['kw3'] = $kwVal;
        }
        if (!empty($status)) {
            $conditions[] = "t.status = :status";
            $params['status'] = $status;
        }
        if ($categoryId > 0) {
            $conditions[] = "t.category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }
        if ($lecturerId > 0) {
            if ($lecturerScope === 'mine') {
                $conditions[] = "t.requester_id = :lid_req";
                $params['lid_req'] = $lecturerId;
            } elseif ($lecturerScope === 'course_incidents') {
                // Chỉ hiển thị các phiếu mà KTV đã xác thực có lỗi kỹ thuật (it_verified = 1) và chưa được GV xử lý
                $conditions[] = "(t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending') AND t.status NOT IN ('closed', 'resolved') AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2)))";
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            } else {
                $conditions[] = "(t.requester_id = :lid_req OR (t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2))))";
                $params['lid_req'] = $lecturerId;
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            }
        } elseif ($requesterId > 0) {
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
            } elseif ($hasWarning === 'guest') {
                $conditions[] = "t.is_guest_ticket = 1";
            } elseif ($hasWarning === 'unrated') {
                $conditions[] = "t.status IN ('resolved', 'closed') AND r.id IS NULL";
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
        $sql = "SELECT COUNT(*) FROM tickets t LEFT JOIN priorities p ON t.priority_id = p.id LEFT JOIN ticket_ratings r ON t.id = r.ticket_id {$whereClause}";

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
    public static function countSlaOverdue(int $requesterId = 0, int $assignedTo = 0, int $lecturerId = 0, string $lecturerScope = ''): int {
        $db = db();
        $conditions = [
            "t.status NOT IN ('resolved', 'closed')",
            "((t.due_at IS NOT NULL AND t.due_at < NOW()) OR (t.triage_due_at IS NOT NULL AND t.triage_due_at < NOW() AND t.assigned_to IS NULL))"
        ];
        $params = [];
        if ($lecturerId > 0) {
            if ($lecturerScope === 'mine') {
                $conditions[] = "t.requester_id = :lid_req";
                $params['lid_req'] = $lecturerId;
            } elseif ($lecturerScope === 'course_incidents') {
                $conditions[] = "(t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending') AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2)))";
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            } else {
                $conditions[] = "(t.requester_id = :lid_req OR (t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending') AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2))))";
                $params['lid_req'] = $lecturerId;
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            }
        } elseif ($requesterId > 0) {
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
     * Đếm số phiếu của người dùng đã hoàn tất nhưng chưa đánh giá CSAT (status IN ('resolved', 'closed') AND r.id IS NULL)
     */
    public static function countUnratedTickets(int $userId): int {
        if ($userId <= 0) return 0;
        $db = db();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM tickets t
            LEFT JOIN ticket_ratings r ON t.id = r.ticket_id
            WHERE t.requester_id = :uid
              AND t.status IN ('resolved', 'closed')
              AND r.id IS NULL
        ");
        $stmt->execute([':uid' => $userId]);
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
        if ($lecturerId > 0) {
            if ($lecturerScope === 'mine') {
                $conditions[] = "t.requester_id = :lid_req";
                $params['lid_req'] = $lecturerId;
            } elseif ($lecturerScope === 'course_incidents') {
                // Chỉ hiển thị các phiếu mà KTV đã xác thực có lỗi kỹ thuật (it_verified = 1) và chưa được GV xử lý
                $conditions[] = "(t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending') AND t.status NOT IN ('closed', 'resolved') AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2)))";
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            } else {
                $conditions[] = "(t.requester_id = :lid_req OR (t.lesson_id IS NOT NULL AND t.it_verified = 1 AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid_ca) OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid_ca2))))";
                $params['lid_req'] = $lecturerId;
                $params['lid_ca'] = $lecturerId;
                $params['lid_ca2'] = $lecturerId;
            }
        } elseif ($requesterId > 0) {
            $conditions[] = "t.requester_id = :req_id";
            $params['req_id'] = $requesterId;
        }
        if ($assignedTo > 0) {
            $conditions[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) FROM tickets t LEFT JOIN priorities p ON t.priority_id = p.id {$whereClause}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy chi tiết một phiếu theo ID (Slide 7 - Detail)
     */
    public static function findById(int $id): ?array {
        $sql = "SELECT t.*, 
                       COALESCE(u_req.name, t.contact_email, 'Khách gửi cứu hộ') AS requester_name, 
                       COALESCE(u_req.user_code, 'GUEST') AS requester_code, 
                       COALESCE(u_req.email, t.contact_email, 'Chưa có email') AS requester_email,
                       COALESCE(u_req.is_active, 1) AS requester_is_active,
                       COALESCE(u_req.lock_reason, 'none') AS requester_lock_reason,
                       u_agent.name AS agent_name, u_agent.user_code AS agent_code,
                       COALESCE(c.name, 'Cứu hộ tài khoản') AS category_name, 
                       COALESCE(c.code, 'CAT_SSO') AS category_code,
                       COALESCE(p.name, 'Cao') AS priority_name, 
                       COALESCE(p.code, 'HIGH') AS priority_code,
                       COALESCE(cl.class_code, u_cl.class_code, u_req.cohort, 'D24-CNTT1') AS class_code,
                       COALESCE(cl.name, u_cl.name, 'Đại học CNTT K24') AS class_name,
                       co.course_code, co.name AS course_name,
                       cln.lesson_number, cln.title AS lesson_title, cln.deadline_at AS lesson_deadline_at, cln.assignment_requirement AS lesson_requirement
                FROM tickets t
                LEFT JOIN users u_req ON t.requester_id = u_req.id
                LEFT JOIN users u_agent ON t.assigned_to = u_agent.id
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN priorities p ON t.priority_id = p.id
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

    /**
     * Kiểm tra sự cố trùng lặp của người dùng nội bộ (Sinh viên / Giảng viên)
     * Quy định ITIL: Cùng 1 người gửi 1 dịch vụ sự cố gặp vấn đề nhiều lần trong 1 ngày HOẶC vấn đề đó đang xử lý.
     */
    public static function findDuplicate(int $requesterId, int $categoryId, int $lessonId = 0, int $excludeTicketId = 0): ?array {
        $db = db();
        $sql = "SELECT id, ticket_code, title, status, created_at, category_id, lesson_id
                FROM tickets
                WHERE requester_id = :uid
                  AND id != :exclude_id
                  AND is_guest_ticket = 0
                  AND (
                      -- 1. Cùng dịch vụ sự cố đang trong quá trình xử lý
                      (category_id = :cat_id AND status IN ('new', 'in_progress', 'waiting_requester'))
                      -- 2. Hoặc cùng dịch vụ sự cố đã gửi nhiều lần trong ngày hôm nay
                      OR (category_id = :cat_id2 AND DATE(created_at) = CURDATE())
                      -- 3. Hoặc cùng bài tập buổi học (nếu có) chưa hoàn tất đóng
                      OR (:lesson_id > 0 AND lesson_id = :lesson_id2 AND status NOT IN ('closed'))
                  )
                ORDER BY id DESC LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid'          => $requesterId,
            ':exclude_id'   => $excludeTicketId,
            ':cat_id'       => $categoryId,
            ':cat_id2'      => $categoryId,
            ':lesson_id'    => $lessonId,
            ':lesson_id2'   => $lessonId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Kiểm tra đơn cứu hộ trùng lặp ngoài cổng (Trạm cứu hộ)
     * Quy định: Cùng 1 người gửi đơn cứu hộ nhiều lần trong 1 ngày HOẶC đơn trước đó đang xử lý.
     */
    public static function findDuplicateGuest(int $requesterId, int $excludeTicketId = 0): ?array {
        $db = db();
        $sql = "SELECT id, ticket_code, title, status, created_at, rescue_verified
                FROM tickets
                WHERE requester_id = :uid
                  AND is_guest_ticket = 1
                  AND id != :exclude_id
                  AND (
                      -- Đơn cứu hộ đang xử lý (chưa đóng hoàn tất)
                      status NOT IN ('closed')
                      -- Hoặc đã gửi đơn cứu hộ trong ngày hôm nay
                      OR DATE(created_at) = CURDATE()
                  )
                ORDER BY id DESC LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid'        => $requesterId,
            ':exclude_id' => $excludeTicketId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
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
        if (!empty($assignedTo)) {
            $stmtChkAgent = $db->prepare("SELECT id FROM users WHERE id = :aid AND role = 'agent' LIMIT 1");
            $stmtChkAgent->execute([':aid' => $assignedTo]);
            if (!$stmtChkAgent->fetchColumn()) {
                $assignedTo = null;
            }
        } else {
            $assignedTo = null;
        }

        $requesterId = (int)($data['requester_id'] ?? 0);
        if ($requesterId > 0) {
            $stmtChkReq = $db->prepare("SELECT id FROM users WHERE id = :rid LIMIT 1");
            $stmtChkReq->execute([':rid' => $requesterId]);
            if (!$stmtChkReq->fetchColumn()) {
                $requesterId = (int)($db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1);
            }
        } else {
            $requesterId = (int)($db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1);
        }

        $courseId = !empty($data['course_id']) ? (int)$data['course_id'] : null;
        $lessonId = !empty($data['lesson_id']) ? (int)$data['lesson_id'] : null;

        // Chặn trùng lặp sự cố chuẩn ITIL
        if (empty($data['is_guest_ticket'])) {
            $dup = self::findDuplicate($requesterId, (int)$data['category_id'], (int)($data['lesson_id'] ?? 0));
            if ($dup) {
                throw new \Exception("Phát hiện phiếu sự cố trùng lặp: Đã có phiếu #{$dup['ticket_code']} cùng nhóm dịch vụ gửi trong ngày hoặc đang trong quá trình xử lý!");
            }
        } else {
            $dupGuest = self::findDuplicateGuest($requesterId);
            if ($dupGuest) {
                throw new \Exception("Phát hiện đơn cứu hộ trùng lặp: Đã có đơn cứu hộ #{$dupGuest['ticket_code']} gửi trong ngày hoặc đang trong quá trình xử lý!");
            }
        }

        // Tính toán SLA chuẩn theo giờ hành chính (T2-T6, trừ ngày nghỉ lễ & ngoài giờ)
        require_once __DIR__ . '/../Services/SlaService.php';
        $userType = $data['user_type'] ?? ($_SESSION['user']['user_type'] ?? null);
        if (!$userType && $requesterId > 0) {
            $userType = $db->query("SELECT user_type FROM users WHERE id = {$requesterId} LIMIT 1")->fetchColumn() ?: null;
        }
        $sla = \App\Services\SlaService::calculateSlaDeadlines((int)$data['category_id'], (int)$data['priority_id'], null, $userType);
        $triageDueAt = $sla['triage_due_at'];
        $dueAt       = $sla['due_at'];

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
                    :triage_due_at,
                    :due_at
                )";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'ticket_code'     => $ticketCode,
            'requester_id'    => $requesterId,
            'assigned_to'     => $assignedTo,
            'category_id'     => (int)$data['category_id'],
            'priority_id'     => (int)$data['priority_id'],
            'course_id'       => $courseId,
            'lesson_id'       => $lessonId,
            'title'           => trim($data['title']),
            'description'     => trim($data['description']),
            'system_affected' => trim($data['system_affected']),
            'attachment_path' => $attachmentPath,
            'triage_due_at'   => $triageDueAt,
            'due_at'          => $dueAt,
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
     * Hủy/Xóa phiếu vĩnh viễn (Xóa kèm đánh giá và phản hồi để toàn vẹn CSDL)
     */
    public static function delete(int $id): bool {
        $db = db();
        $db->prepare("DELETE FROM ticket_ratings WHERE ticket_id = :id")->execute(['id' => $id]);
        $db->prepare("DELETE FROM ticket_replies WHERE ticket_id = :id")->execute(['id' => $id]);
        $stmt = $db->prepare("DELETE FROM tickets WHERE id = :id");
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


    /**
     * Đếm số phiếu sự cố học tập CẦN Giảng viên xem xét (KTV đã xác nhận có lỗi & GV chưa xử lý)
     */
    public static function countLecturerCourseIncidents(int $lecturerId): int {
        $db = db();
        $sql = "SELECT COUNT(*) FROM tickets t 
                WHERE t.lesson_id IS NOT NULL 
                  AND t.it_verified = 1
                  AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending')
                  AND t.status NOT IN ('closed', 'resolved')
                  AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid) 
                       OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid2))";
        $stmt = $db->prepare($sql);
        $stmt->execute([':lid' => $lecturerId, ':lid2' => $lecturerId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy danh sách các phiếu sự cố học tập CẦN Giảng viên xem xét (KTV đã xác thực có lỗi kỹ thuật & GV chưa quyết định)
     */
    public static function getPendingCourseIncidentsForLecturer(int $lecturerId): array {
        $db = db();
        $sql = "SELECT t.*, u_req.name AS student_name, u_req.user_code AS student_code,
                       COALESCE(cl.class_code, u_cl.class_code, 'D24-CNTT1') AS class_code,
                       co.name AS course_name, co.course_code,
                       cln.lesson_number, cln.title AS lesson_title, cln.deadline_at AS lesson_deadline_at
                FROM tickets t
                JOIN users u_req ON t.requester_id = u_req.id
                LEFT JOIN classes cl ON t.class_id = cl.id
                LEFT JOIN classes u_cl ON u_req.class_id = u_cl.id
                LEFT JOIN courses co ON t.course_id = co.id
                LEFT JOIN course_lessons cln ON t.lesson_id = cln.id
                WHERE t.lesson_id IS NOT NULL
                  AND t.it_verified = 1
                  AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending')
                  AND t.status NOT IN ('closed', 'resolved')
                  AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid)
                       OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid2))
                ORDER BY t.id DESC
                LIMIT 10";
        $stmt = $db->prepare($sql);
        $stmt->execute([':lid' => $lecturerId, ':lid2' => $lecturerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm số phiếu cứu hộ khẩn cấp (Guest Tickets) đang chờ KTV hoặc Admin xử lý
     */
    public static function countPendingGuestTickets(): int {
        $db = db();
        return (int)$db->query("SELECT COUNT(*) FROM tickets WHERE is_guest_ticket = 1 AND status NOT IN ('closed')")->fetchColumn();
    }

    /**
     * Đếm số phiếu cứu hộ khẩn cấp mà KTV ĐÃ DUYỆT cần Admin quyết định mở khóa
     */
    public static function countPendingGuestTicketsForAdmin(): int {
        $db = db();
        return (int)$db->query("SELECT COUNT(*) FROM tickets WHERE is_guest_ticket = 1 AND rescue_verified = 1 AND status NOT IN ('closed')")->fetchColumn();
    }

    /**
     * Lấy danh sách phiếu cứu hộ khẩn cấp KTV ĐÃ DUYỆT cho Quản trị viên (Admin) xem xét mở khóa
     */
    public static function getPendingGuestTickets(): array {
        $db = db();
        $sql = "SELECT t.*, u_req.name AS student_name, u_req.user_code AS student_code,
                       CASE WHEN (u_req.is_active = 0 OR (u_req.locked_until IS NOT NULL AND u_req.locked_until > NOW()) OR (u_req.lock_reason IS NOT NULL AND u_req.lock_reason != 'none')) THEN 1 ELSE 0 END AS is_locked,
                       u_req.lock_reason,
                       COALESCE(cl.class_code, 'N/A') AS class_code,
                       ag.name AS agent_name, ag.user_code AS agent_code
                FROM tickets t
                LEFT JOIN users u_req ON t.requester_id = u_req.id
                LEFT JOIN classes cl ON u_req.class_id = cl.id
                LEFT JOIN users ag ON t.assigned_to = ag.id
                WHERE t.is_guest_ticket = 1 AND t.rescue_verified = 1 AND t.status NOT IN ('closed')
                ORDER BY t.id DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
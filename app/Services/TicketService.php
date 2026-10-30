<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Ticket;
use App\Models\Reply;
use PDO;
use RuntimeException;

/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD) - KHOA CÔNG NGHỆ THÔNG TIN
 * CHỦ ĐỀ 9: HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * 
 * PHÂN HỆ: CORE ARCHITECTURE & AGENT WORKSPACE
 * THÀNH VIÊN PHỤ TRÁCH ĐỘC QUYỀN: PHẠM THỊ THANH NGÂN (TRƯỞNG NHÓM - MSSV: 224001736)
 * LÕI NGHIỆP VỤ: THUẬT TOÁN PHÂN CÔNG 5 KTV CHUYÊN MÔN & TRÀN TẢI TỰ ĐỘNG NGUYÊN TỬ
 * ==============================================================================
 */
class TicketService {

    /**
     * Thuật toán phân công KTV thông minh (Smart Dispatching & Atomic Overflow)
     * 
     * 1. Bước 1: Tìm KTV chuyên trách tương ứng với category_id đang trực (is_on_duty = 1)
     * 2. Bước 2: Kiểm tra số lượng phiếu đang xử lý của KTV chuyên trách đó (active_tickets_count)
     *    - Nếu < max_capacity (mặc định 15 phiếu): Phân công cho KTV chuyên trách này.
     *    - Nếu >= max_capacity (15 phiếu): KÍCH HOẠT CƠ CHẾ TRÀN TẢI (Overflow Dispatch)
     *      Tìm KTV khác đang trực (is_on_duty = 1) có số phiếu active ít nhất để phân công tạm.
     */
    public static function dispatchAgent(int $categoryId): array {
        $pdo = Database::getConnection();

        // 1. Tìm KTV chuyên trách nhóm dịch vụ này đang trực
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM tickets t 
                    WHERE t.assigned_to = u.id 
                      AND t.status IN ('new', 'in_progress', 'waiting_requester')
                   ) AS current_active_count
            FROM users u
            WHERE u.role = 'agent' 
              AND u.is_active = 1 
              AND u.is_on_duty = 1 
              AND u.specialty_category_id = :cat_id
            LIMIT 1
        ");
        $stmt->execute([':cat_id' => $categoryId]);
        $primaryAgent = $stmt->fetch();

        // Ngưỡng kịch tải cấu hình 15 phiếu
        $maxCap = !empty($primaryAgent['max_capacity']) ? (int)$primaryAgent['max_capacity'] : 15;

        // Nếu có KTV chuyên trách và chưa vượt ngưỡng kịch tải (15 phiếu)
        if ($primaryAgent && (int)$primaryAgent['current_active_count'] < $maxCap) {
            return [
                'agent_id'     => (int)$primaryAgent['id'],
                'agent_name'   => $primaryAgent['name'],
                'agent_code'   => $primaryAgent['user_code'],
                'is_overflow'  => false,
                'reason'       => "Phân công đúng KTV chuyên trách ({$primaryAgent['name']})",
                'active_count' => (int)$primaryAgent['current_active_count'],
            ];
        }

        // 2. KÍCH HOẠT CƠ CHẾ TRÀN TẢI (OVERFLOW DISPATCH)
        // Tìm KTV khác đang trực (is_on_duty = 1) có ít phiếu đang xử lý nhất
        $overflowStmt = $pdo->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM tickets t 
                    WHERE t.assigned_to = u.id 
                      AND t.status IN ('new', 'in_progress', 'waiting_requester')
                   ) AS current_active_count
            FROM users u
            WHERE u.role = 'agent' 
              AND u.is_active = 1 
              AND u.is_on_duty = 1
            ORDER BY current_active_count ASC, u.id ASC
            LIMIT 1
        ");
        $backupAgent = $overflowStmt->fetch();

        if ($backupAgent) {
            $primaryName = $primaryAgent ? $primaryAgent['name'] : 'Chưa có KTV chuyên trách';
            return [
                'agent_id'     => (int)$backupAgent['id'],
                'agent_name'   => $backupAgent['name'],
                'agent_code'   => $backupAgent['user_code'],
                'is_overflow'  => true,
                'reason'       => "Kích hoạt cơ chế TRÀN TẢI tự động do KTV chuyên trách ({$primaryName}) đã đạt ngưỡng tải tối đa. Điều phối sang {$backupAgent['name']}.",
                'active_count' => (int)$backupAgent['current_active_count'],
            ];
        }

        // Trường hợp dự phòng nếu không có KTV nào đang trực (gán mặc định KTV 1)
        return [
            'agent_id'     => 2,
            'agent_name'   => 'KTV Trực Ban Hệ Thống',
            'agent_code'   => 'KTV001',
            'is_overflow'  => false,
            'reason'       => 'Gán KTV trực ban mặc định',
            'active_count' => 0,
        ];
    }

    /**
     * Tính toán hạn chót xử lý SLA tự động
     */
    public static function calculateSla(int $categoryId, int $priorityId): array {
        $pdo = Database::getConnection();

        $catStmt = $pdo->prepare("SELECT sla_hours FROM categories WHERE id = :id");
        $catStmt->execute([':id' => $categoryId]);
        $slaHours = (float)($catStmt->fetchColumn() ?: 4.0);

        $prioStmt = $pdo->prepare("SELECT sla_factor FROM priorities WHERE id = :id");
        $prioStmt->execute([':id' => $priorityId]);
        $slaFactor = (float)($prioStmt->fetchColumn() ?: 1.0);

        $effectiveMinutes = (int)round($slaHours * $slaFactor * 60);

        return [
            'effective_minutes' => $effectiveMinutes,
            'triage_due_at'     => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            'due_at'            => date('Y-m-d H:i:s', strtotime("+{$effectiveMinutes} minutes")),
        ];
    }

    /**
     * Tạo phiếu mới kèm điều phối phân công tự động & ghi chú tràn tải (nếu có)
     */
    public static function createTicketWithAutoDispatch(array $data): array {
        $pdo = Database::getConnection();

        $categoryId = (int)$data['category_id'];
        $priorityId = (int)($data['priority_id'] ?? 0);
        if ($priorityId <= 0) {
            // Tự động xác định mức ưu tiên theo Danh mục (Auto-Priority Mapping chuẩn ITIL PDF)
            $catPrioStmt = $pdo->prepare("SELECT default_priority_code FROM categories WHERE id = :cat_id");
            $catPrioStmt->execute([':cat_id' => $categoryId]);
            $defCode = $catPrioStmt->fetchColumn() ?: 'MED';

            $prioStmt = $pdo->prepare("SELECT id FROM priorities WHERE code = :code");
            $prioStmt->execute([':code' => $defCode]);
            $priorityId = (int)($prioStmt->fetchColumn() ?: 3);
        }

        // 1. Chạy thuật toán phân công KTV
        $dispatch = self::dispatchAgent($categoryId);
        $assignedTo = $dispatch['agent_id'];

        // 2. Tính toán mốc thời gian SLA
        $sla = self::calculateSla($categoryId, $priorityId);

        // 3. Tự sinh mã ticket_code dạng HD-YYYY-DDMM-xxxxxx (chuẩn hóa ITIL mới theo ngày)
        require_once __DIR__ . '/../Repositories/TicketRepository.php';
        $ticketCode = !empty($data['ticket_code']) ? $data['ticket_code'] : \TicketRepository::generateTicketCode();

        // 4. Lưu vào CSDL trong một Transaction
        $pdo->beginTransaction();
        try {
            $sql = "INSERT INTO tickets (
                        ticket_code, requester_id, assigned_to, category_id, priority_id,
                        class_id, course_id, is_guest_ticket, contact_email, lookup_pin,
                        title, description, system_affected, status,
                        created_at, sla_started_at, triage_due_at, due_at
                    ) VALUES (
                        :ticket_code, :requester_id, :assigned_to, :category_id, :priority_id,
                        :class_id, :course_id, :is_guest, :contact_email, :lookup_pin,
                        :title, :description, :system_affected, 'new',
                        NOW(), NOW(), :triage_due, :due_at
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':ticket_code'      => $ticketCode,
                ':requester_id'     => (int)$data['requester_id'],
                ':assigned_to'      => $assignedTo,
                ':category_id'      => $categoryId,
                ':priority_id'      => $priorityId,
                ':class_id'         => !empty($data['class_id']) ? (int)$data['class_id'] : null,
                ':course_id'        => !empty($data['course_id']) ? (int)$data['course_id'] : null,
                ':is_guest'         => !empty($data['is_guest_ticket']) ? 1 : 0,
                ':contact_email'    => $data['contact_email'] ?? null,
                ':lookup_pin'       => $data['lookup_pin'] ?? null,
                ':title'            => trim($data['title']),
                ':description'      => trim($data['description']),
                ':system_affected'  => trim($data['system_affected'] ?? 'Chung'),
                ':triage_due'       => $sla['triage_due_at'],
                ':due_at'           => $sla['due_at'],
            ]);

            $ticketId = (int)$pdo->lastInsertId();

            // Nếu là cơ chế tràn tải -> Tự động thêm 1 dòng ghi chú nội bộ hệ thống để KTV nắm bắt
            if ($dispatch['is_overflow']) {
                $noteSql = "INSERT INTO ticket_replies (ticket_id, user_id, message, is_internal, created_at)
                            VALUES (:ticket_id, :user_id, :message, 1, NOW())";
                $noteStmt = $pdo->prepare($noteSql);
                $noteStmt->execute([
                    ':ticket_id' => $ticketId,
                    ':user_id'   => 1, // Admin / Hệ thống
                    ':message'   => "⚠️ [HỆ THỐNG ĐIỀU PHỐI TỰ ĐỘNG]: " . $dispatch['reason'],
                ]);
            }

            $pdo->commit();

            return [
                'success'        => true,
                'ticket_id'      => $ticketId,
                'ticket_code'    => $ticketCode,
                'assigned_agent' => $dispatch['agent_name'],
                'is_overflow'    => $dispatch['is_overflow'],
                'dispatch_info'  => $dispatch,
            ];
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new RuntimeException("Lỗi tạo phiếu và phân công KTV: " . $e->getMessage());
        }
    }

    /**
     * Lấy hàng đợi phiếu của KTV được sắp xếp theo hạn SLA (due_at ASC)
     * Hỗ trợ 5 nhóm trạng thái (Tất cả, Mới, Đang xử lý, Chờ phản hồi, Đã giải quyết & Đóng)
     */
    public static function getAgentQueue(int $agentId, string $statusFilter = ''): array {
        $pdo = Database::getConnection();

        $sql = "SELECT t.*, 
                       u_req.name AS requester_name, u_req.user_code AS requester_code,
                       c.name AS category_name, c.code AS category_code,
                       p.name AS priority_name, p.code AS priority_code,
                       TIMESTAMPDIFF(MINUTE, NOW(), t.due_at) AS minutes_remaining
                FROM tickets t
                JOIN users u_req ON t.requester_id = u_req.id
                JOIN categories c ON t.category_id = c.id
                JOIN priorities p ON t.priority_id = p.id
                WHERE t.assigned_to = :agent_id";

        $params = [':agent_id' => $agentId];

        if ($statusFilter === 'all') {
            $sql .= " ORDER BY t.id DESC";
        } elseif (in_array($statusFilter, ['resolved', 'closed', 'done', 'resolved_closed'], true)) {
            $sql .= " AND t.status IN ('resolved', 'closed') ORDER BY t.id DESC";
        } elseif (!empty($statusFilter)) {
            $sql .= " AND t.status = :status ORDER BY t.due_at ASC";
            $params[':status'] = $statusFilter;
        } else {
            // Mặc định: Nếu có phiếu đang mở thì ưu tiên hàng đợi mở, nếu đã hoàn tất hết thì hiện toàn bộ
            $hasActive = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE assigned_to = {$agentId} AND status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
            if ($hasActive > 0) {
                $sql .= " AND t.status IN ('new', 'in_progress', 'waiting_requester') ORDER BY t.due_at ASC";
            } else {
                $sql .= " ORDER BY t.id DESC";
            }
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Đếm số lượng phiếu theo 5 mục trạng thái cho bàn làm việc KTV
     */
    public static function getAgentQueueCounts(int $agentId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) AS total_all,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS total_new,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS total_in_progress,
                SUM(CASE WHEN status = 'waiting_requester' THEN 1 ELSE 0 END) AS total_waiting,
                SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) AS total_done,
                SUM(CASE WHEN status IN ('new', 'in_progress', 'waiting_requester') THEN 1 ELSE 0 END) AS total_active
            FROM tickets 
            WHERE assigned_to = :agent_id
        ");
        $stmt->execute([':agent_id' => $agentId]);
        $row = $stmt->fetch();
        return [
            'all'         => (int)($row['total_all'] ?? 0),
            'new'         => (int)($row['total_new'] ?? 0),
            'in_progress' => (int)($row['total_in_progress'] ?? 0),
            'waiting'     => (int)($row['total_waiting'] ?? 0),
            'done'        => (int)($row['total_done'] ?? 0),
            'active'      => (int)($row['total_active'] ?? 0),
        ];
    }

    /**
     * Nghiệm thu phiếu & lập biên bản giải pháp kỹ thuật
     */
    public static function resolveWithSolution(int $ticketId, int $agentId, string $solutionNote): bool {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            // 1. Thêm phản hồi ghi nhận giải pháp công khai
            $replySql = "INSERT INTO ticket_replies (ticket_id, user_id, message, is_internal, created_at)
                         VALUES (:ticket_id, :user_id, :message, 0, NOW())";
            $pdo->prepare($replySql)->execute([
                ':ticket_id' => $ticketId,
                ':user_id'   => $agentId,
                ':message'   => "✅ [BIÊN BẢN NGHIỆM THU GIẢI PHÁP KỸ THUẬT]:\n" . trim($solutionNote),
            ]);

            // 2. Chuyển trạng thái sang resolved
            $ticketSql = "UPDATE tickets 
                          SET status = 'resolved', 
                              resolved_at = NOW() 
                          WHERE id = :id";
            $pdo->prepare($ticketSql)->execute([':id' => $ticketId]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new RuntimeException("Lỗi nghiệm thu giải pháp: " . $e->getMessage());
        }
    }
}


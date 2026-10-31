<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD) - KHOA CÔNG NGHỆ THÔNG TIN
 * CHỦ ĐỀ 9: HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * 
 * PHÂN HỆ: CORE ARCHITECTURE & AGENT WORKSPACE
 * THÀNH VIÊN PHỤ TRÁCH ĐỘC QUYỀN: PHẠM THỊ THANH NGÂN (TRƯỞNG NHÓM - MSSV: 224001736)
 * BẢNG CSDL QUẢN TRỊ: tickets
 * ==============================================================================
 */
class Ticket {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT t.*, 
                       c.name AS category_name, 
                       p.name AS priority_name,
                       COALESCE(req.name, t.contact_email, 'Khách vãng lai') AS requester_name,
                       COALESCE(req.user_code, 'GUEST') AS requester_code,
                       COALESCE(req.email, t.contact_email) AS requester_email,
                       COALESCE(agent.name, 'Đang chờ phân công') AS agent_name,
                       COALESCE(agent.user_code, '') AS agent_code
                FROM tickets t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN priorities p ON p.id = t.priority_id
                LEFT JOIN users req ON req.id = t.requester_id
                LEFT JOIN users agent ON agent.id = t.assigned_to
                WHERE t.id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByRequester(int $userId): array {
        $pdo = Database::getConnection();
        $sql = "SELECT t.*, 
                       c.name AS category_name, 
                       p.name AS priority_name,
                       COALESCE(agent.name, 'Đang chờ phân công') AS agent_name
                FROM tickets t
                JOIN categories c ON c.id = t.category_id
                JOIN priorities p ON p.id = t.priority_id
                LEFT JOIN users agent ON agent.id = t.assigned_to
                WHERE t.requester_id = :uid
                ORDER BY t.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function generateUniqueTicketCode(string $prefix = 'HD', ?string $userName = null): string {
        $pdo = Database::getConnection();
        $year = date('Y');
        $dayMonth = date('dm'); // Ngày (2 số) + Tháng (2 số), ví dụ ngày 29/10 => '2910'
        $charList = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

        $maxId = (int)$pdo->query("SELECT MAX(id) FROM tickets")->fetchColumn();
        $seq = $maxId + 1;
        $numStr = str_pad((string)($seq % 10000), 4, '0', STR_PAD_LEFT);
        $digits = str_split($numStr);

        do {
            $letters = [
                $charList[random_int(0, strlen($charList) - 1)],
                $charList[random_int(0, strlen($charList) - 1)]
            ];

            $positions = [0, 1, 2, 3, 4, 5];
            shuffle($positions);
            $letterPos = array_slice($positions, 0, 2);
            sort($letterPos);

            $suffixArr = [];
            $dIdx = 0;
            $lIdx = 0;
            for ($i = 0; $i < 6; $i++) {
                if (in_array($i, $letterPos, true)) {
                    $suffixArr[] = $letters[$lIdx++];
                } else {
                    $suffixArr[] = $digits[$dIdx++];
                }
            }
            $suffix = implode('', $suffixArr);
            $code = "{$prefix}-{$year}-{$dayMonth}-{$suffix}";

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_code = :code");
            $stmt->execute([':code' => $code]);
            $exists = (int)$stmt->fetchColumn() > 0;
        } while ($exists);

        return $code;
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $userName = $data['requester_name'] ?? null;
        $ticketCode = $data['ticket_code'] ?? self::generateUniqueTicketCode('HD', $userName);

        $requesterId = (int)($data['requester_id'] ?? 0);
        $categoryId  = (int)($data['category_id'] ?? 1);
        $priorityId  = (int)($data['priority_id'] ?? 2);
        $isGuest     = !empty($data['is_guest_ticket']) ? 1 : 0;
        $lessonId    = !empty($data['lesson_id']) ? (int)$data['lesson_id'] : 0;

        // 1. Kiểm tra chống trùng lặp sự cố chuẩn ITIL
        require_once __DIR__ . '/../Repositories/TicketRepository.php';
        if ($isGuest) {
            $dupGuest = \TicketRepository::findDuplicateGuest($requesterId);
            if ($dupGuest) {
                throw new \Exception("Phát hiện đơn cứu hộ trùng lặp: Đã có đơn cứu hộ #{$dupGuest['ticket_code']} gửi trong ngày hoặc đang trong quá trình xử lý!");
            }
        } else {
            $dup = \TicketRepository::findDuplicate($requesterId, $categoryId, $lessonId);
            if ($dup) {
                throw new \Exception("Phát hiện phiếu sự cố trùng lặp: Đã có phiếu #{$dup['ticket_code']} cùng nhóm dịch vụ gửi trong ngày hoặc đang trong quá trình xử lý!");
            }
        }

        // 2. Tính toán SLA chuẩn theo giờ hành chính (T2-T6, trừ ngày nghỉ lễ & ngoài giờ)
        require_once __DIR__ . '/../Services/SlaService.php';
        $sla = \App\Services\SlaService::calculateSlaDeadlines($categoryId, $priorityId);

        $sql = "INSERT INTO tickets (
                    ticket_code, requester_id, assigned_to, category_id, priority_id,
                    class_id, course_id, is_guest_ticket, contact_email, lookup_pin,
                    title, description, system_affected, attachment_path, status,
                    created_at, sla_started_at, triage_due_at, due_at
                ) VALUES (
                    :ticket_code, :requester_id, :assigned_to, :category_id, :priority_id,
                    :class_id, :course_id, :is_guest, :contact_email, :lookup_pin,
                    :title, :description, :system_affected, :attachment_path, 'new',
                    NOW(), :sla_started_at, :triage_due_at, :due_at
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_code'      => $ticketCode,
            ':requester_id'     => $requesterId,
            ':assigned_to'      => $data['assigned_to'] ?? null,
            ':category_id'      => $categoryId,
            ':priority_id'      => $priorityId,
            ':class_id'         => $data['class_id'] ?? null,
            ':course_id'        => $data['course_id'] ?? null,
            ':is_guest'         => $isGuest,
            ':contact_email'    => $data['contact_email'] ?? null,
            ':lookup_pin'       => $data['lookup_pin'] ?? null,
            ':title'            => trim($data['title']),
            ':description'      => trim($data['description']),
            ':system_affected'  => trim($data['system_affected'] ?? 'Chung'),
            ':attachment_path'  => $data['attachment_path'] ?? null,
            ':sla_started_at'   => $sla['sla_started_at'],
            ':triage_due_at'    => $sla['triage_due_at'],
            ':due_at'           => $sla['due_at'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findByTicketCode(string $code): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT t.*, 
                       c.name AS category_name, 
                       p.name AS priority_name,
                       COALESCE(req.name, t.contact_email, 'Khách vãng lai') AS requester_name,
                       COALESCE(agent.name, 'Đang chờ phân công') AS agent_name
                FROM tickets t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN priorities p ON p.id = t.priority_id
                LEFT JOIN users req ON req.id = t.requester_id
                LEFT JOIN users agent ON agent.id = t.assigned_to
                WHERE UPPER(t.ticket_code) = UPPER(:code) LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':code' => trim($code)]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByGuestCodeAndPin(string $code, ?string $pin = null): ?array {
        return self::findByTicketCode($code);
    }

    public static function cancelTicket(int $id): bool {
        $pdo = Database::getConnection();
        $sql = "UPDATE tickets SET status = 'closed', closed_reason = 'cancelled' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Cập nhật trạng thái phiếu hỗ trợ theo Vòng đời 5 bước (State Machine)
     */
    public static function updateStatus(int $id, string $status): bool {
        $pdo = Database::getConnection();
        $validStatuses = ['new', 'in_progress', 'waiting_requester', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        $fields = ["status = :status"];
        $params = [':id' => $id, ':status' => $status];

        if ($status === 'resolved') {
            $fields[] = "resolved_at = NOW()";
        } elseif ($status === 'closed') {
            $fields[] = "closed_at = NOW()";
        }

        $sql = "UPDATE tickets SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Ghi nhận mốc phản hồi đầu tiên của KTV (phục vụ đánh giá SLA First Response)
     */
    public static function recordFirstResponse(int $id): bool {
        $pdo = Database::getConnection();
        $sql = "UPDATE tickets SET first_response_at = NOW() WHERE id = :id AND first_response_at IS NULL";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}

<?php
namespace App\Models;

use App\Core\Database;

class Ticket {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT t.*, 
                       c.name AS category_name, 
                       p.name AS priority_name,
                       req.name AS requester_name,
                       COALESCE(agent.name, 'Đang chờ phân công') AS agent_name
                FROM tickets t
                JOIN categories c ON c.id = t.category_id
                JOIN priorities p ON p.id = t.priority_id
                JOIN users req ON req.id = t.requester_id
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

    public static function generateUniqueTicketCode(string $prefix = 'HD'): string {
        $pdo = Database::getConnection();
        $year = date('Y');
        $dayMonth = date('dm'); // Ngày (2 số) + Tháng (2 số), ví dụ ngày 29/10 => '2910'
        do {
            // Sinh mã gồm tiền tố HD, năm, ngày tháng và 6 số ngẫu nhiên bảo mật (Ví dụ: HD-2026-2910-849216)
            $randomNum = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $code = "{$prefix}-{$year}-{$dayMonth}-{$randomNum}";

            // Kiểm tra trùng lặp trong CSDL để đảm bảo độc nhất 100% không trùng với bất kỳ ai
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_code = :code");
            $stmt->execute([':code' => $code]);
            $exists = (int)$stmt->fetchColumn() > 0;
        } while ($exists);

        return $code;
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $ticketCode = $data['ticket_code'] ?? self::generateUniqueTicketCode('HD');

        $sql = "INSERT INTO tickets (
                    ticket_code, requester_id, assigned_to, category_id, priority_id,
                    class_id, course_id, is_guest_ticket, contact_email, lookup_pin,
                    title, description, system_affected, attachment_path, status, due_at
                ) VALUES (
                    :ticket_code, :requester_id, :assigned_to, :category_id, :priority_id,
                    :class_id, :course_id, :is_guest, :contact_email, :lookup_pin,
                    :title, :description, :system_affected, :attachment_path, 'new', :due_at
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_code'      => $ticketCode,
            ':requester_id'     => $data['requester_id'],
            ':assigned_to'      => $data['assigned_to'] ?? null,
            ':category_id'      => $data['category_id'],
            ':priority_id'      => $data['priority_id'] ?? 3,
            ':class_id'         => $data['class_id'] ?? null,
            ':course_id'        => $data['course_id'] ?? null,
            ':is_guest'         => $data['is_guest_ticket'] ?? 0,
            ':contact_email'    => $data['contact_email'] ?? null,
            ':lookup_pin'       => $data['lookup_pin'] ?? null,
            ':title'            => trim($data['title']),
            ':description'      => trim($data['description']),
            ':system_affected'  => trim($data['system_affected'] ?? 'Chung'),
            ':attachment_path'  => $data['attachment_path'] ?? null,
            ':due_at'           => date('Y-m-d H:i:s', strtotime('+4 hours')),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findByTicketCode(string $code): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT t.*, c.name AS category_name, COALESCE(agent.name, 'Đang chờ phân công') AS agent_name
                FROM tickets t
                JOIN categories c ON c.id = t.category_id
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
}


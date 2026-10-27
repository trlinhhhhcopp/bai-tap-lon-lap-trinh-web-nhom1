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

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $ticketCode = 'HD-' . date('Y') . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO tickets (
                    ticket_code, requester_id, assigned_to, category_id, priority_id,
                    class_id, course_id, is_guest_ticket, contact_email, lookup_pin,
                    title, description, system_affected, status, due_at
                ) VALUES (
                    :ticket_code, :requester_id, :assigned_to, :category_id, :priority_id,
                    :class_id, :course_id, :is_guest, :contact_email, :lookup_pin,
                    :title, :description, :system_affected, 'new', :due_at
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
            ':due_at'           => date('Y-m-d H:i:s', strtotime('+4 hours')),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findByGuestCodeAndPin(string $code, string $pin): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT t.*, c.name AS category_name, COALESCE(agent.name, 'Đang chờ phân công') AS agent_name
                FROM tickets t
                JOIN categories c ON c.id = t.category_id
                LEFT JOIN users agent ON agent.id = t.assigned_to
                WHERE t.ticket_code = :code AND t.lookup_pin = :pin LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':code' => trim($code), ':pin' => trim($pin)]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}


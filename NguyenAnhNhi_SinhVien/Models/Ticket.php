<?php
/**
 * Model Ticket – Bảng tickets
 * Phần của: Nguyễn Ánh Nhi (Student Module)
 */
require_once __DIR__ . '/../config/database.php';

class Ticket
{
    public static function getCategories(): array
    {
        return db()->query("SELECT id, code, name, default_priority_code, sla_hours 
                            FROM categories WHERE is_active = 1 ORDER BY id")->fetchAll();
    }

    public static function getClasses(): array
    {
        return db()->query("SELECT id, class_code, name FROM classes ORDER BY class_code")->fetchAll();
    }

    public static function getCourses(): array
    {
        return db()->query("SELECT id, course_code, name FROM courses ORDER BY course_code")->fetchAll();
    }

    public static function getPriorityIdByCode(string $code): int
    {
        $stmt = db()->prepare("SELECT id FROM priorities WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : 3; // mặc định MED
    }

    public static function create(array $data): int
    {
        // Lấy priority tự động từ category
        $stmt = db()->prepare("SELECT default_priority_code, sla_hours FROM categories WHERE id = :id");
        $stmt->execute(['id' => $data['category_id']]);
        $cat = $stmt->fetch();

        $priorityCode = $cat['default_priority_code'] ?? 'MED';
        $slaHours     = (float)($cat['sla_hours'] ?? 4.0);
        $priorityId   = self::getPriorityIdByCode($priorityCode);

        $now = date('Y-m-d H:i:s');
        $due = date('Y-m-d H:i:s', strtotime("+{$slaHours} hours"));

        $sql = "INSERT INTO tickets 
                (requester_id, category_id, priority_id, class_id, course_id, 
                 title, description, system_affected, status, 
                 created_at, sla_started_at, triage_due_at, due_at)
                VALUES 
                (:requester_id, :category_id, :priority_id, :class_id, :course_id,
                 :title, :description, :system_affected, 'new',
                 :created_at, :sla_started_at, :triage_due_at, :due_at)";

        $stmt = db()->prepare($sql);
        $stmt->execute([
            'requester_id'    => $data['requester_id'],
            'category_id'     => $data['category_id'],
            'priority_id'     => $priorityId,
            'class_id'        => $data['class_id'] ?: null,
            'course_id'       => $data['course_id'] ?: null,
            'title'           => $data['title'],
            'description'     => $data['description'],
            'system_affected' => $data['system_affected'],
            'created_at'      => $now,
            'sla_started_at'  => $now,
            'triage_due_at'   => $due,
            'due_at'          => $due,
        ]);

        $id = (int) db()->lastInsertId();

        // Cập nhật ticket_code
        $code = 'HD-' . date('Y') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
        $stmt = db()->prepare("UPDATE tickets SET ticket_code = :code WHERE id = :id");
        $stmt->execute(['code' => $code, 'id' => $id]);

        return $id;
    }

    public static function findByRequester(int $userId, string $keyword = '', int $page = 1, int $perPage = 10): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $params = ['uid' => $userId];

        $sql = "SELECT t.*, c.name AS category_name, p.name AS priority_name, p.code AS priority_code
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                JOIN priorities p ON t.priority_id = p.id
                WHERE t.requester_id = :uid";

        if ($keyword !== '') {
            $sql .= " AND (t.ticket_code LIKE :kw OR t.title LIKE :kw OR t.description LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByRequester(int $userId, string $keyword = ''): int
    {
        $params = ['uid' => $userId];
        $sql = "SELECT COUNT(*) FROM tickets WHERE requester_id = :uid";

        if ($keyword !== '') {
            $sql .= " AND (ticket_code LIKE :kw OR title LIKE :kw OR description LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function findById(int $id): ?array
    {
        $sql = "SELECT t.*, c.name AS category_name, p.name AS priority_name, p.code AS priority_code,
                       u.name AS requester_name
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                JOIN priorities p ON t.priority_id = p.id
                JOIN users u ON t.requester_id = u.id
                WHERE t.id = :id LIMIT 1";
        $stmt = db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getReplies(int $ticketId): array
    {
        $sql = "SELECT r.*, u.name AS user_name, u.user_type
                FROM ticket_replies r
                JOIN users u ON r.user_id = u.id
                WHERE r.ticket_id = :tid AND r.is_internal = 0
                ORDER BY r.created_at ASC";
        $stmt = db()->prepare($sql);
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll();
    }
}
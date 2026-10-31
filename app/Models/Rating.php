<?php
/**
 * Model Rating – Bảng ticket_ratings
 * Phần của: Nguyễn Ánh Nhi
 */
require_once __DIR__ . '/../../config/database.php';

class Rating
{
    public static function count(string $keyword = '', ?int $score = null, ?int $studentId = null): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM ticket_ratings r
                JOIN tickets t ON r.ticket_id = t.id
                JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (t.ticket_code LIKE :kw1 OR t.title LIKE :kw2 OR u.name LIKE :kw3 OR r.comment LIKE :kw4)";
            $kwVal = '%' . $keyword . '%';
            $params['kw1'] = $kwVal;
            $params['kw2'] = $kwVal;
            $params['kw3'] = $kwVal;
            $params['kw4'] = $kwVal;
        }
        if ($score !== null && $score >= 1 && $score <= 5) {
            $sql .= " AND r.score = :score";
            $params['score'] = $score;
        }
        if ($studentId !== null && $studentId > 0) {
            $sql .= " AND r.user_id = :student_id";
            $params['student_id'] = $studentId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    public static function findAll(string $keyword = '', int $page = 1, int $perPage = 8, ?int $score = null, ?int $studentId = null): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT r.id, r.ticket_id, r.user_id, r.score, r.comment, r.created_at,
                       t.ticket_code, t.title AS ticket_title,
                       u.name AS user_name, u.user_code AS user_code, u.email AS user_email
                FROM ticket_ratings r
                JOIN tickets t ON r.ticket_id = t.id
                JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (t.ticket_code LIKE :kw1 OR t.title LIKE :kw2 OR u.name LIKE :kw3 OR r.comment LIKE :kw4)";
            $kwVal = '%' . $keyword . '%';
            $params['kw1'] = $kwVal;
            $params['kw2'] = $kwVal;
            $params['kw3'] = $kwVal;
            $params['kw4'] = $kwVal;
        }
        if ($score !== null && $score >= 1 && $score <= 5) {
            $sql .= " AND r.score = :score";
            $params['score'] = $score;
        }
        if ($studentId !== null && $studentId > 0) {
            $sql .= " AND r.user_id = :student_id";
            $params['student_id'] = $studentId;
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $sql = "SELECT r.*, t.ticket_code, t.title, u.name AS student_name
                FROM ticket_ratings r
                JOIN tickets t ON r.ticket_id = t.id
                JOIN users u ON r.user_id = u.id
                WHERE r.id = :id LIMIT 1";
        $stmt = db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function existsForTicket(int $ticketId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) AS c FROM ticket_ratings WHERE ticket_id = :ticket_id";
        $params = ['ticket_id' => $ticketId];
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['c'] > 0;
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO ticket_ratings (ticket_id, user_id, score, comment)
                VALUES (:ticket_id, :user_id, :score, :comment)";
        $stmt = db()->prepare($sql);
        $stmt->execute([
            'ticket_id' => $data['ticket_id'],
            'user_id'   => $data['user_id'],
            'score'     => $data['score'],
            'comment'   => $data['comment'] ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE ticket_ratings SET score = :score, comment = :comment WHERE id = :id";
        $stmt = db()->prepare($sql);
        return $stmt->execute([
            'score'   => $data['score'],
            'comment' => $data['comment'] ?: null,
            'id'      => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare("DELETE FROM ticket_ratings WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getAvailableTickets(?int $studentId = null): array
    {
        $sql = "SELECT t.id, t.ticket_code, t.title, u.name AS requester_name
                FROM tickets t
                JOIN users u ON t.requester_id = u.id
                LEFT JOIN ticket_ratings r ON t.id = r.ticket_id
                WHERE t.status IN ('resolved', 'closed') AND r.id IS NULL";
        $params = [];
        if ($studentId !== null && $studentId > 0) {
            $sql .= " AND t.requester_id = :sid";
            $params['sid'] = $studentId;
        }
        $sql .= " ORDER BY t.resolved_at DESC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getAllStudents(): array
    {
        return db()->query("SELECT id, name, email, user_code FROM users
                            WHERE user_type = 'student' AND is_active = 1
                            ORDER BY name")->fetchAll();
    }
}
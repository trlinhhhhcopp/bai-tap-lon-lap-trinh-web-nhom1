<?php
namespace App\Repositories;

use App\Core\Database;
class AgentDashboardRepository
{
    public function findAgent(int $agentId): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT u.*, c.name AS specialty_name, c.code AS specialty_code
             FROM users u
             LEFT JOIN categories c ON u.specialty_category_id = c.id
             WHERE u.id = :id AND u.role = \'agent\''
        );
        $stmt->execute([':id' => $agentId]);

        return $stmt->fetch() ?: null;
    }

    public function getTicketStats(int $agentId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
                    SUM(CASE WHEN status = 'waiting_requester' THEN 1 ELSE 0 END) AS waiting_count,
                    SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) AS resolved_count
             FROM tickets
             WHERE assigned_to = :agent_id"
        );
        $stmt->execute([':agent_id' => $agentId]);
        $row = $stmt->fetch() ?: [];
        $stats = [
            'total' => (int)($row['total'] ?? 0),
            'new' => (int)($row['new_count'] ?? 0),
            'in_progress' => (int)($row['in_progress_count'] ?? 0),
            'waiting_requester' => (int)($row['waiting_count'] ?? 0),
            'resolved' => (int)($row['resolved_count'] ?? 0),
        ];
        $stats['active'] = $stats['new'] + $stats['in_progress'] + $stats['waiting_requester'];

        return $stats;
    }

    public function getRatingStats(int $agentId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) AS total_ratings, AVG(r.score) AS avg_score
             FROM ticket_ratings r
             JOIN tickets t ON r.ticket_id = t.id
             WHERE t.assigned_to = :agent_id'
        );
        $stmt->execute([':agent_id' => $agentId]);
        $stats = $stmt->fetch() ?: [];
        $totalRatings = (int)($stats['total_ratings'] ?? 0);

        return [
            'totalRatings' => $totalRatings,
            'avgScore' => $totalRatings > 0 ? round((float)$stats['avg_score'], 1) : 5.0,
        ];
    }

    public function findActiveAgents(): array
    {
        $stmt = Database::getConnection()->query(
            "SELECT u.*, c.name AS specialty_name,
                    (SELECT COUNT(*) FROM tickets t
                     WHERE t.assigned_to = u.id
                       AND t.status IN ('new', 'in_progress', 'waiting_requester')
                    ) AS active_count
             FROM users u
             LEFT JOIN categories c ON u.specialty_category_id = c.id
             WHERE u.role = 'agent' AND u.is_active = 1
             ORDER BY u.id ASC"
        );

        return $stmt->fetchAll();
    }

    public function updateDuty(int $agentId, int $isOnDuty): ?string
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE users SET is_on_duty = :is_on_duty
             WHERE id = :id AND role = 'agent'"
        );
        $stmt->execute([
            ':is_on_duty' => $isOnDuty,
            ':id' => $agentId,
        ]);

        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :id AND role = 'agent'");
        $stmt->execute([':id' => $agentId]);
        $name = $stmt->fetchColumn();

        return $name === false ? null : (string)$name;
    }
}

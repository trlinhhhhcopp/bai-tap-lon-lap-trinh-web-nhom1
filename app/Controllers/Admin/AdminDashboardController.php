<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Repositories\TicketRepository;
use PDO;

class AdminDashboardController extends Controller {
    public function index(): void {
        $currentUser = Session::user();
        if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
            $this->flash('danger', 'Bạn không có quyền truy cập Trung tâm Quản trị Admin!');
            $this->redirect('/login');
            return;
        }

        $pdo = Database::getConnection();

        // Thống kê toàn hệ thống
        $totalTickets    = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
        $activeTickets   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
        $urgentTickets   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE priority_id IN (1, 2) AND status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
        $resolvedTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('resolved', 'closed')")->fetchColumn();
        $totalUsers      = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalCourses    = (int)$pdo->query("SELECT COUNT(*) FROM course_assignments")->fetchColumn();

        // Danh sách 5 KTV và tải trọng hiện tại
        $agents = $pdo->query("
            SELECT u.id, u.user_code, u.name, c.name AS category_name,
                   COUNT(t.id) AS active_load
            FROM users u
            LEFT JOIN categories c ON u.specialty_category_id = c.id
            LEFT JOIN tickets t ON t.assigned_to = u.id AND t.status IN ('new', 'in_progress', 'waiting_requester')
            WHERE u.role = 'agent'
            GROUP BY u.id, u.user_code, u.name, c.name
            ORDER BY u.id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Danh sách phiếu cần Triage & xử lý gần đây
        $recentTickets = $pdo->query("
            SELECT t.*, c.name AS category_name, p.name AS priority_name, p.code AS priority_code,
                   req.name AS requester_name, req.user_code AS requester_code, ag.name AS agent_name
            FROM tickets t
            JOIN categories c ON t.category_id = c.id
            JOIN priorities p ON t.priority_id = p.id
            JOIN users req ON t.requester_id = req.id
            LEFT JOIN users ag ON t.assigned_to = ag.id
            ORDER BY t.id DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../Repositories/TicketRepository.php';
        $pendingGuestTickets = \TicketRepository::getPendingGuestTickets();
        $pendingGuestCount   = count($pendingGuestTickets);

        // Danh sách sự cố diện rộng do KTV báo cáo
        $majorIncidents = $pdo->query("
            SELECT t.*, c.name AS category_name, p.name AS priority_name,
                   req.name AS requester_name, ag.name AS agent_name, ag.user_code AS agent_code
            FROM tickets t
            JOIN categories c ON t.category_id = c.id
            JOIN priorities p ON t.priority_id = p.id
            JOIN users req ON t.requester_id = req.id
            LEFT JOIN users ag ON t.assigned_to = ag.id
            WHERE t.is_major_incident = 1 AND t.status IN ('new', 'in_progress', 'waiting_requester')
            ORDER BY t.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        $majorIncidentCount = count($majorIncidents);

        $this->view('admin/dashboard', [
            'currentUser'         => $currentUser,
            'totalTickets'        => $totalTickets,
            'activeTickets'       => $activeTickets,
            'urgentTickets'       => $urgentTickets,
            'resolvedTickets'     => $resolvedTickets,
            'totalUsers'          => $totalUsers,
            'totalCourses'        => $totalCourses,
            'agents'              => $agents,
            'recentTickets'       => $recentTickets,
            'pendingGuestTickets' => $pendingGuestTickets,
            'pendingGuestCount'   => $pendingGuestCount,
            'majorIncidents'      => $majorIncidents,
            'majorIncidentCount'  => $majorIncidentCount,
            'title'               => 'Trung Tâm Quản Trị Hệ Thống - Admin Control Center',
        ]);
    }
}

<?php
namespace App\Controllers\Agent;

use App\Core\Controller;
use App\Core\Session;
use App\Repositories\AgentDashboardRepository;
use App\Services\TicketService;
use Exception;

class AgentDashboardController extends Controller
{
    public function index(): void
    {
        $currentUser = Session::user();
        if (!$currentUser || !in_array($currentUser['role'] ?? '', ['agent', 'admin'], true)) {
            $this->flash('danger', 'Bạn không có quyền truy cập vào Bàn làm việc Kỹ thuật viên!');
            $this->redirect('/login');
        }

        $isAgent = $currentUser['role'] === 'agent';
        $isAdmin = $currentUser['role'] === 'admin';
        $agentId = $isAgent
            ? (int)$currentUser['id']
            : (int)($_GET['agent_id'] ?? 2);
        $statusFilter = trim($_GET['status'] ?? '');
        $repository = new AgentDashboardRepository();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handlePost($repository, $agentId, $isAdmin);
        }

        $currentAgent = $repository->findAgent($agentId);
        if (!$currentAgent && $agentId !== 2) {
            $agentId = 2;
            $currentAgent = $repository->findAgent($agentId);
        }
        if (!$currentAgent) {
            $this->flash('danger', 'Không tìm thấy tài khoản kỹ thuật viên để hiển thị.');
            $this->redirect($isAdmin ? '/admin/dashboard' : '/login');
        }

        $stats = $repository->getTicketStats($agentId);
        $ratings = $repository->getRatingStats($agentId);
        $queue = TicketService::getAgentQueue($agentId, $statusFilter);
        $allAgents = $isAdmin ? $repository->findActiveAgents() : [];

        $this->view('agent/index', array_merge(
            [
                'currentUser' => $currentUser,
                'isAgent' => $isAgent,
                'isAdmin' => $isAdmin,
                'agentId' => $agentId,
                'statusFilter' => $statusFilter,
                'currentAgent' => $currentAgent,
                'allAgents' => $allAgents,
                'queue' => $queue,
            ],
            [
                'countAll' => $stats['total'],
                'countNew' => $stats['new'],
                'countInProgress' => $stats['in_progress'],
                'countWaiting' => $stats['waiting_requester'],
                'countActive' => $stats['active'],
                'countResolved' => $stats['resolved'],
            ],
            $ratings
        ));
    }

    private function handlePost(AgentDashboardRepository $repository, int $agentId, bool $isAdmin): void
    {
        $action = $_POST['action'] ?? '';

        if ($action === 'resolve_ticket') {
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $solution = trim($_POST['solution_note'] ?? '');

            if ($ticketId <= 0 || $solution === '') {
                $this->flash('danger', 'Vui lòng nhập nội dung giải pháp kỹ thuật trước khi nghiệm thu!');
                return;
            }

            try {
                TicketService::resolveWithSolution($ticketId, $agentId, $solution);
                $this->flash('success', "Đã lập biên bản giải pháp và nghiệm thu hoàn tất phiếu #{$ticketId} thành công!");
            } catch (Exception $e) {
                $this->flash('danger', $e->getMessage());
            }

            $this->redirect('/agent/workbench?agent_id=' . $agentId);
        }

        if ($action !== 'toggle_duty') {
            return;
        }

        if (!$isAdmin) {
            $this->flash('danger', '⛔ Bạn không có quyền thay đổi trạng thái ca trực! Thẩm quyền này thuộc Quản trị viên.');
            $this->redirect('/agent/workbench?agent_id=' . $agentId);
        }

        $targetAgentId = (int)($_POST['agent_id'] ?? $agentId);
        $newDuty = (int)($_POST['is_on_duty'] ?? 1);
        $dutyReason = trim($_POST['duty_reason'] ?? '');
        if ($targetAgentId <= 0 || !in_array($newDuty, [0, 1], true)) {
            $this->flash('danger', 'Thông tin ca trực không hợp lệ.');
            $this->redirect('/agent/workbench?agent_id=' . $agentId);
        }

        $targetName = $repository->updateDuty($targetAgentId, $newDuty);
        if ($targetName === null) {
            $this->flash('danger', 'Không tìm thấy kỹ thuật viên cần cập nhật ca trực.');
            $this->redirect('/agent/workbench?agent_id=' . $agentId);
        }

        if ($newDuty === 1) {
            $this->flash('success', "✅ Admin đã BẬT trạng thái trực ca cho KTV [{$targetName}]. Hệ thống Triage sẵn sàng tự động điều phối phiếu mới.");
        } else {
            $reasonText = $dutyReason !== '' ? " (Lý do: {$dutyReason})" : '';
            $this->flash('warning', "⏸️ Admin đã chuyển KTV [{$targetName}] sang trạng thái NGHỈ CA{$reasonText}. Vé mới sẽ được điều phối dự phòng.");
        }

        $this->redirect('/agent/workbench?agent_id=' . $targetAgentId);
    }
}

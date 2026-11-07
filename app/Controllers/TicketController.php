<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use TicketRepository;

require_once __DIR__ . '/../Repositories/TicketRepository.php';

class TicketController extends Controller {
    public function index(): void {
        $currentUser = Session::user();
        $userRole = $currentUser['role'];
        $userType = $currentUser['user_type'] ?? 'student';
        $isLecturer = $userType === 'lecturer';

        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && ($_POST['action'] ?? '') === 'admin_delete_ticket'
            && $userRole === 'admin'
        ) {
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            if ($ticketId > 0) {
                TicketRepository::delete($ticketId);
                set_flash('success', "Quản trị viên đã xóa thành công phiếu #{$ticketId} khỏi hệ thống!");
            }
            $this->redirect('/tickets/list');
        }

        $keyword = trim($_GET['kw'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $warningType = trim($_GET['warning'] ?? ($_GET['has_warning'] ?? ''));
        $lecturerScope = trim($_GET['scope'] ?? '');
        if ($status === 'warning') {
            $warningType = 'sla';
            $status = '';
        }

        $isStudentOrLecturer = $userRole === 'requester' || $isLecturer;
        $hasWarning = false;
        if ($warningType === 'sla') {
            $hasWarning = 'sla';
        } elseif ($warningType === 'major' && in_array($userRole, ['agent', 'admin'], true)) {
            $hasWarning = 'major';
        } elseif ($warningType === 'guest') {
            $hasWarning = 'guest';
        } elseif ($warningType === 'violation' && ($userRole === 'admin' || ($userRole === 'requester' && !$isLecturer))) {
            $hasWarning = 'violation';
        } elseif ($warningType === 'unrated' && $isStudentOrLecturer) {
            $hasWarning = 'unrated';
        }

        $lecturerId = $isLecturer ? (int)$currentUser['id'] : 0;
        if ($isLecturer) {
            if ($warningType === 'unrated') {
                $filterReqId = (int)$currentUser['id'];
                $lecturerId = 0;
            } else {
                $filterReqId = 0;
            }
        } elseif ($userRole === 'requester') {
            $filterReqId = (int)$currentUser['id'];
        } else {
            $filterReqId = 0;
        }

        $listAgentId = ($userRole === 'agent' && $warningType !== 'guest') ? (int)$currentUser['id'] : 0;
        $badgeAgentId = $userRole === 'agent' ? (int)$currentUser['id'] : 0;
        $perPage = 5;
        $page = max(1, (int)($_GET['page'] ?? 1));

        $totalTickets = TicketRepository::countAll(
            $keyword,
            $status,
            $categoryId,
            $filterReqId,
            $listAgentId,
            $hasWarning,
            $lecturerId,
            $lecturerScope
        );
        $totalPages = max(1, (int)ceil($totalTickets / $perPage));
        if ($page > $totalPages && $totalTickets > 0) {
            $page = $totalPages;
        }

        $tickets = TicketRepository::findAll(
            $keyword,
            $page,
            $perPage,
            $status,
            $categoryId,
            $filterReqId,
            $listAgentId,
            $hasWarning,
            $lecturerId,
            $lecturerScope
        );
        $categories = TicketRepository::getCategories();
        $slaCount = TicketRepository::countSlaOverdue($filterReqId, $badgeAgentId, $lecturerId, $lecturerScope);
        $lecturerIncidentsCount = $isLecturer
            ? TicketRepository::countLecturerCourseIncidents((int)$currentUser['id'])
            : 0;
        $guestCount = in_array($userRole, ['agent', 'admin'], true)
            ? TicketRepository::countPendingGuestTickets()
            : 0;
        $majorCount = in_array($userRole, ['agent', 'admin'], true)
            ? TicketRepository::countMajorIncidents()
            : 0;
        $canSeeViolations = $userRole === 'admin' || ($userRole === 'requester' && !$isLecturer);
        $violationCount = $canSeeViolations
            ? TicketRepository::countViolations($filterReqId, 0, $isLecturer)
            : 0;
        $unratedCount = $isStudentOrLecturer
            ? TicketRepository::countUnratedTickets((int)$currentUser['id'])
            : 0;
        $flash = get_flash() ?? Session::getFlash();

        $this->view('tickets/index', compact(
            'currentUser',
            'userRole',
            'userType',
            'isLecturer',
            'keyword',
            'status',
            'categoryId',
            'warningType',
            'lecturerScope',
            'isStudentOrLecturer',
            'hasWarning',
            'lecturerId',
            'filterReqId',
            'listAgentId',
            'badgeAgentId',
            'perPage',
            'page',
            'totalTickets',
            'totalPages',
            'tickets',
            'categories',
            'slaCount',
            'lecturerIncidentsCount',
            'guestCount',
            'majorCount',
            'canSeeViolations',
            'violationCount',
            'unratedCount',
            'flash'
        ));
    }

    public function show(): void {
        require __DIR__ . '/../../tickets/show.php';
    }

    public function create(): void {
        require __DIR__ . '/../../tickets/create.php';
    }

    public function edit(): void {
        require __DIR__ . '/../../tickets/edit.php';
    }
}

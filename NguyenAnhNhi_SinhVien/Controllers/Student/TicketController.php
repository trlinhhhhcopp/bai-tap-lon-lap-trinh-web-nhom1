<?php
/**
 * Controller Form báo sự cố + Phiếu của tôi
 * Phần của: Nguyễn Ánh Nhi
 */
require_once __DIR__ . '/../../Models/Ticket.php';

class TicketController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $userId  = $_SESSION['user']['id'];
        $keyword = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));

        $total      = Ticket::countByRequester($userId, $keyword);
        $totalPages = max(1, (int)ceil($total / self::PER_PAGE));
        $page       = min($page, $totalPages);

        $tickets = Ticket::findByRequester($userId, $keyword, $page, self::PER_PAGE);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../views/student/tickets/index.php';
    }

    public function create(): void
    {
        $categories = Ticket::getCategories();
        $classes    = Ticket::getClasses();
        $courses    = Ticket::getCourses();
        $errors     = [];
        $old        = [
            'category_id' => '', 'class_id' => '', 'course_id' => '',
            'title' => '', 'description' => '', 'system_affected' => ''
        ];

        require __DIR__ . '/../../views/student/tickets/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=ticket_create');
            exit;
        }

        $data = [
            'requester_id'    => $_SESSION['user']['id'],
            'category_id'     => (int)($_POST['category_id'] ?? 0),
            'class_id'        => (int)($_POST['class_id'] ?? 0) ?: null,
            'course_id'       => (int)($_POST['course_id'] ?? 0) ?: null,
            'title'           => trim($_POST['title'] ?? ''),
            'description'     => trim($_POST['description'] ?? ''),
            'system_affected' => trim($_POST['system_affected'] ?? ''),
        ];

        $errors = [];
        if ($data['category_id'] <= 0) $errors[] = 'Vui lòng chọn nhóm dịch vụ.';
        if ($data['title'] === '')     $errors[] = 'Vui lòng nhập tiêu đề.';
        if ($data['description'] === '') $errors[] = 'Vui lòng mô tả chi tiết sự cố.';
        if ($data['system_affected'] === '') $errors[] = 'Vui lòng chọn hệ thống bị ảnh hưởng.';

        if (!empty($errors)) {
            $categories = Ticket::getCategories();
            $classes    = Ticket::getClasses();
            $courses    = Ticket::getCourses();
            $old        = $data;
            require __DIR__ . '/../../views/student/tickets/create.php';
            return;
        }

        try {
            $id = Ticket::create($data);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Gửi phiếu báo sự cố thành công!'];
            header('Location: index.php?action=ticket_show&id=' . $id);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
            $categories = Ticket::getCategories();
            $classes    = Ticket::getClasses();
            $courses    = Ticket::getCourses();
            $old        = $data;
            require __DIR__ . '/../../views/student/tickets/create.php';
        }
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $ticket = Ticket::findById($id);

        if (!$ticket || $ticket['requester_id'] != $_SESSION['user']['id']) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy phiếu hoặc bạn không có quyền xem.'];
            header('Location: index.php?action=tickets');
            exit;
        }

        $replies = Ticket::getReplies($id);

        require __DIR__ . '/../../views/student/tickets/show.php';
    }
}
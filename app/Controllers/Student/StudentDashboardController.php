<?php
namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Services\UploadService;
use Exception;
use PDO;

class StudentDashboardController extends Controller {
    public function index(): void {
        $currentUser = $this->user();
        if (!$currentUser) {
            $this->redirect('/login');
            return;
        }

        $userId = (int)$currentUser['id'];
        $pdo = db();

        // 1. Truy vấn chi tiết hồ sơ sinh viên kèm lớp học & học thuật
        $studentStmt = $pdo->prepare("
            SELECT u.*, COALESCE(c.name, 'Chưa phân lớp') AS class_name, COALESCE(c.class_code, 'N/A') AS class_code
            FROM users u
            LEFT JOIN classes c ON u.class_id = c.id
            WHERE u.id = :id
        ");
        $studentStmt->execute([':id' => $userId]);
        $studentProfile = $studentStmt->fetch() ?: $currentUser;

        // 2. Thống kê số lượng phiếu của riêng sinh viên này
        $myTicketsCount  = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId}")->fetchColumn();
        $myActiveCount   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId} AND status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
        $myResolvedCount = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId} AND status IN ('resolved', 'closed')")->fetchColumn();

        // 3. Thống kê học phần & bài tập
        require_once dirname(__DIR__, 2) . '/Models/CourseLesson.php';
        $courseModel = new \CourseLesson();
        $myStudentCourses = $courseModel->getStudentCourses($userId);
        $myCoursesCount = count($myStudentCourses);
        $totalLessonsCount = 0;
        $completedLessonsCount = 0;
        foreach ($myStudentCourses as $mc) {
            $totalLessonsCount += (int)($mc['total_lessons'] ?? 0);
            $completedLessonsCount += (int)($mc['completed_lessons'] ?? 0);
        }

        // 4. Lấy danh sách phiếu của riêng sinh viên này (đầy đủ thông tin chi tiết)
        $myTickets = $pdo->query("
            SELECT t.*, c.name AS category_name, p.name AS priority_name, p.code AS priority_code,
                   ag.name AS agent_name, r.score AS rating_score
            FROM tickets t
            JOIN categories c ON t.category_id = c.id
            JOIN priorities p ON t.priority_id = p.id
            LEFT JOIN users ag ON t.assigned_to = ag.id
            LEFT JOIN ticket_ratings r ON r.ticket_id = t.id
            WHERE t.requester_id = {$userId}
            ORDER BY t.id DESC
        ")->fetchAll();

        // 5. Danh mục sự cố và mức ưu tiên phục vụ form tạo phiếu
        $categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
        $priorities = $pdo->query("SELECT * FROM priorities ORDER BY id ASC")->fetchAll();

        $this->view('student/dashboard', [
            'currentUser'           => $currentUser,
            'studentProfile'        => $studentProfile,
            'myTicketsCount'        => $myTicketsCount,
            'myActiveCount'         => $myActiveCount,
            'myResolvedCount'       => $myResolvedCount,
            'myStudentCourses'      => $myStudentCourses,
            'myCoursesCount'        => $myCoursesCount,
            'totalLessonsCount'     => $totalLessonsCount,
            'completedLessonsCount' => $completedLessonsCount,
            'myTickets'             => $myTickets,
            'categories'            => $categories,
            'priorities'            => $priorities,
            'title'                 => 'Cổng Sinh Viên - Helpdesk Đại Học Thủ Đô Hà Nội',
        ]);
    }

    public function createTicket(): void {
        $user = $this->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $title          = trim($_POST['title'] ?? '');
        $categoryId     = (int)($_POST['category_id'] ?? 1);
        $priorityId     = (int)($_POST['priority_id'] ?? 3);
        $description    = trim($_POST['description'] ?? '');

        if (empty($title) || empty($description)) {
            $this->flash('danger', 'Vui lòng nhập đầy đủ tiêu đề và mô tả sự cố kỹ thuật!');
            $this->redirect('/student/dashboard');
            return;
        }

        try {
            $attachmentPath = null;
            // 1. Kiểm tra ảnh chụp trực tiếp từ webcam HTML5
            $capturedImage = trim($_POST['captured_image'] ?? '');
            if (!empty($capturedImage)) {
                $attachmentPath = UploadService::handleBase64Snapshot($capturedImage);
            } elseif (!empty($_FILES['attachment']['tmp_name'])) {
                // 2. Kiểm tra file tải lên từ thiết bị
                $attachmentPath = UploadService::handleUploadedFile($_FILES['attachment']);
            }

            // Gọi TicketService để phân bổ Triage + tính SLA tự động
            require_once dirname(__DIR__, 2) . '/Services/TicketService.php';
            require_once dirname(__DIR__, 2) . '/Services/SlaService.php';

            $pdo = db();
            $ticketCode = 'TK-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

            // Tính hạn SLA
            $catStmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
            $catStmt->execute([':id' => $categoryId]);
            $category = $catStmt->fetch();
            $slaHours = (float)($category['sla_hours'] ?? 4.0);

            $prioStmt = $pdo->prepare("SELECT * FROM priorities WHERE id = :id");
            $prioStmt->execute([':id' => $priorityId]);
            $priority = $prioStmt->fetch();
            $slaFactor = (float)($priority['sla_factor'] ?? 1.0);

            $effectiveHours = $slaHours * $slaFactor;
            $dueAt = \App\Services\SlaService::calculateDueTime(date('Y-m-d H:i:s'), $effectiveHours);

            // Điều phối Triage gán KTV
            $assignedTo = \App\Services\TicketService::assignAgent($categoryId);

            $insertStmt = $pdo->prepare("
                INSERT INTO tickets (ticket_code, requester_id, category_id, priority_id, assigned_to, title, description, attachment_path, status, due_at, created_at, updated_at)
                VALUES (:ticket_code, :requester_id, :category_id, :priority_id, :assigned_to, :title, :description, :attachment_path, 'new', :due_at, NOW(), NOW())
            ");
            $insertStmt->execute([
                ':ticket_code'     => $ticketCode,
                ':requester_id'    => $user['id'],
                ':category_id'     => $categoryId,
                ':priority_id'     => $priorityId,
                ':assigned_to'     => $assignedTo,
                ':title'           => $title,
                ':description'     => $description,
                ':attachment_path' => $attachmentPath,
                ':due_at'          => $dueAt,
            ]);

            // Cập nhật active_tickets_count cho KTV nếu được gán
            if ($assignedTo) {
                $pdo->exec("UPDATE users SET active_tickets_count = (SELECT COUNT(*) FROM tickets WHERE assigned_to = {$assignedTo} AND status IN ('new', 'in_progress', 'waiting_requester')) WHERE id = {$assignedTo}");
            }

            $this->flash(
                'success',
                "Gửi phiếu báo sự cố thành công! Mã phiếu của bạn: <strong>{$ticketCode}</strong>. " .
                "Kỹ thuật viên đã tiếp nhận yêu cầu và sẽ xử lý trước hạn chót SLA: <strong>{$dueAt}</strong>."
            );
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi khi gửi phiếu hỗ trợ: ' . $e->getMessage());
        }

        $this->redirect('/student/dashboard');
    }

    public function cancelTicket(): void {
        $user = $this->user();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);

        if ($ticketId <= 0) {
            $this->flash('danger', 'Mã phiếu không hợp lệ!');
            $this->redirect('/student/dashboard');
            return;
        }

        $pdo = db();
        $checkStmt = $pdo->prepare("SELECT * FROM tickets WHERE id = :id AND requester_id = :uid");
        $checkStmt->execute([':id' => $ticketId, ':uid' => $user['id']]);
        $ticket = $checkStmt->fetch();

        if (!$ticket) {
            $this->flash('danger', 'Bạn không có quyền hủy phiếu này hoặc phiếu không tồn tại!');
            $this->redirect('/student/dashboard');
            return;
        }

        if (in_array($ticket['status'], ['resolved', 'closed', 'cancelled'])) {
            $this->flash('danger', 'Phiếu này đã hoàn thành hoặc đã đóng, không thể hủy!');
            $this->redirect('/student/dashboard');
            return;
        }

        // Quy tắc ITIL: Chỉ cho phép sinh viên hủy phiếu trong vòng 15 phút (900 giây) kể từ thời điểm gửi
        $createdTs = !empty($ticket['created_at']) ? strtotime($ticket['created_at']) : 0;
        if ($createdTs > 0 && (time() - $createdTs) > 900) {
            $this->flash('danger', 'Quá thời hạn 15 phút! Phiếu chỉ có thể hủy trong vòng 15 phút kể từ thời điểm gửi yêu cầu.');
            $this->redirect('/student/dashboard');
            return;
        }

        $updateStmt = $pdo->prepare("UPDATE tickets SET status = 'cancelled', closed_at = NOW(), updated_at = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $ticketId]);

        // Cập nhật lại tải KTV
        if (!empty($ticket['assigned_to'])) {
            $aid = (int)$ticket['assigned_to'];
            $pdo->exec("UPDATE users SET active_tickets_count = (SELECT COUNT(*) FROM tickets WHERE assigned_to = {$aid} AND status IN ('new', 'in_progress', 'waiting_requester')) WHERE id = {$aid}");
        }

        $this->flash('success', "Đã hủy thành công phiếu hỗ trợ #{$ticket['ticket_code']}!");
        $this->redirect('/student/dashboard');
    }
}

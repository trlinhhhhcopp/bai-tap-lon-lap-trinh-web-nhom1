<?php
namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Models\MasterData;
use App\Services\UploadService;
use Exception;

class StudentDashboardController extends Controller {
    public function index(): void {
        $user = $this->user();
        $fullUser = User::findById($user['id']);
        $myTickets = Ticket::findByRequester($user['id']);
        $categories = MasterData::getCategories();

        $this->view('student/dashboard', [
            'user'       => $fullUser,
            'myTickets'  => $myTickets,
            'categories' => $categories,
            'title'      => 'Cổng Sinh Viên - Bàn Làm Việc Hỗ Trợ Kỹ Thuật',
        ]);
    }

    public function createTicket(): void {
        $user = $this->user();
        $title          = trim($_POST['title'] ?? '');
        $categoryId     = (int)($_POST['category_id'] ?? 1);
        $systemAffected = trim($_POST['system_affected'] ?? 'Chung');
        $description    = trim($_POST['description'] ?? '');

        if (empty($title) || empty($description)) {
            $this->flash('danger', 'Vui lòng nhập đầy đủ tiêu đề và mô tả sự cố!');
            $this->redirect('/student/dashboard');
        }

        try {
            $attachmentPath = null;
            // 1. Kiểm tra ảnh chụp trực tiếp từ webcam/camera
            $capturedImage = trim($_POST['captured_image'] ?? '');
            if (!empty($capturedImage)) {
                $attachmentPath = UploadService::handleBase64Snapshot($capturedImage);
            } elseif (!empty($_FILES['attachment']['tmp_name'])) {
                // 2. Kiểm tra file tải lên từ thiết bị
                $attachmentPath = UploadService::handleUploadedFile($_FILES['attachment']);
            }

            $ticketId = Ticket::create([
                'requester_id'    => $user['id'],
                'requester_name'  => $user['name'] ?? null,
                'category_id'     => $categoryId,
                'priority_id'     => 3, // MED
                'class_id'        => $user['class_id'] ?? null,
                'title'           => $title,
                'description'     => $description,
                'system_affected' => $systemAffected,
                'attachment_path' => $attachmentPath,
            ]);

            $ticket = Ticket::findById($ticketId);
            $category = MasterData::getCategoryById($categoryId);
            $slaHours = $category ? rtrim(rtrim((string)$category['sla_hours'], '0'), '.') : '2';

            $this->flash(
                'success', 
                "Gửi phiếu báo sự cố thành công! Mã phiếu của bạn: <strong>{$ticket['ticket_code']}</strong>. " .
                "Theo quy định hỗ trợ, sự cố này dự kiến sẽ được Kỹ thuật viên tiếp nhận và xử lý trong vòng <strong>{$slaHours} giờ làm việc</strong>. " .
                "Bạn có thể theo dõi tiến độ giải quyết ngay tại bảng bên dưới!"
            );
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/student/dashboard');
    }

    public function updateAvatar(): void {
        $user = $this->user();
        if (!$user) {
            $this->redirect('/login');
        }

        try {
            $avatarPath = null;
            // 1. Kiểm tra ảnh chụp từ webcam/camera
            $capturedImage = trim($_POST['captured_avatar'] ?? '');
            if (!empty($capturedImage)) {
                $avatarPath = UploadService::handleBase64Snapshot($capturedImage);
            } elseif (!empty($_FILES['avatar']['tmp_name'])) {
                // 2. Kiểm tra file tải lên từ thiết bị
                $avatarPath = UploadService::handleUploadedFile($_FILES['avatar']);
            }

            if (!$avatarPath) {
                $this->flash('warning', 'Vui lòng chọn tệp ảnh từ máy tính hoặc chụp ảnh từ Camera trước khi lưu!');
                $this->redirect('/student/dashboard');
            }

            User::updateAvatar((int)$user['id'], $avatarPath);

            // Cập nhật session người dùng hiện tại
            $_SESSION['user']['avatar_path'] = $avatarPath;

            $this->flash('success', 'Cập nhật ảnh đại diện (Avatar) thành công!');
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi cập nhật ảnh đại diện: ' . $e->getMessage());
        }

        $this->redirect('/student/dashboard');
    }

    public function cancelTicket(): void {
        $user = $this->user();
        if (!$user) {
            $this->redirect('/login');
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $ticket = Ticket::findById($ticketId);

        if (!$ticket || $ticket['requester_id'] != $user['id']) {
            $this->flash('danger', 'Không tìm thấy phiếu hỗ trợ hoặc bạn không có quyền thao tác trên phiếu này!');
            $this->redirect('/student/dashboard');
        }

        // 1. Kiểm tra trạng thái: chỉ cho phép hủy khi phiếu còn ở trạng thái "new" (KTV chưa tiếp nhận)
        if ($ticket['status'] !== 'new') {
            $this->flash('danger', 'Không thể hủy phiếu! Kỹ thuật viên đã tiếp nhận xử lý hoặc phiếu đã hoàn tất.');
            $this->redirect('/student/dashboard');
        }

        // 2. Kiểm tra thời gian: chỉ cho phép hủy trong vòng 1 giờ (3600 giây) sau khi nộp
        $createdTime = strtotime($ticket['created_at']);
        $elapsedSeconds = time() - $createdTime;
        if ($elapsedSeconds > 3600) {
            $minutesPassed = round($elapsedSeconds / 60);
            $this->flash('danger', "Đã quá thời hạn hủy phiếu cho phép (đã qua {$minutesPassed} phút, quy định tối đa 60 phút)! Vui lòng liên hệ trực tiếp Kỹ thuật viên phụ trách.");
            $this->redirect('/student/dashboard');
        }

        Ticket::cancelTicket($ticketId);

        $this->flash('success', "Đã hủy phiếu hỗ trợ <strong>{$ticket['ticket_code']}</strong> thành công theo yêu cầu của bạn!");
        $this->redirect('/student/dashboard');
    }

    public function downloadAttachment(): void {
        $file = basename($_GET['file'] ?? '');
        if (empty($file)) {
            http_response_code(404);
            die("Không tìm thấy tệp đính kèm!");
        }

        $filePath = __DIR__ . '/../../../storage/uploads/' . $file;
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("Tệp đính kèm không tồn tại trên hệ thống lưu trữ!");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header("Content-Type: " . ($mime ?: 'application/octet-stream'));
        header("Content-Length: " . filesize($filePath));
        header("Content-Disposition: inline; filename=\"" . $file . "\"");
        readfile($filePath);
        exit;
    }
}


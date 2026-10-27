<?php
namespace App\Controllers\Student;

use App\Core\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Models\MasterData;
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
            $ticketId = Ticket::create([
                'requester_id'    => $user['id'],
                'category_id'     => $categoryId,
                'priority_id'     => 3, // MED
                'class_id'        => $user['class_id'] ?? null,
                'title'           => $title,
                'description'     => $description,
                'system_affected' => $systemAffected,
            ]);

            $ticket = Ticket::findById($ticketId);
            $this->flash('success', "Gửi phiếu báo lỗi thành công! Mã phiếu của bạn: <strong>{$ticket['ticket_code']}</strong>. Kỹ thuật viên sẽ tiếp nhận hỗ trợ trong thời gian sớm nhất.");
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/student/dashboard');
    }
}


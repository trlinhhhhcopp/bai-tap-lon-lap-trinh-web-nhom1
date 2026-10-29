<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Services\UploadService;
use Exception;

class GuestTicketController extends Controller {
    public function showForm(): void {
        $this->view('guest/create_ticket', [
            'title' => 'Form Báo Khóa Tài Khoản & Cứu Hộ Khẩn Cấp (Guest Ticket)',
        ]);
    }

    public function submit(): void {
        $userCode     = trim($_POST['user_code'] ?? '');
        $name         = trim($_POST['name'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $description  = trim($_POST['description'] ?? '');

        if (empty($userCode) || empty($name) || empty($contactEmail)) {
            $this->flash('danger', 'Vui lòng điền đầy đủ Mã SV, Họ tên và Email liên hệ cá nhân!');
            $this->redirect('/guest/ticket');
        }

        try {
            $user = User::findByEmailOrCode($userCode);
            $userId = $user ? $user['id'] : 1; // Gán tạm nếu không có
            $issueTitle = trim($_POST['issue_title'] ?? 'Yêu cầu mở khóa tài khoản');

            $attachmentPath = null;
            $capturedImage = trim($_POST['captured_image'] ?? '');
            if (!empty($capturedImage)) {
                $attachmentPath = UploadService::handleBase64Snapshot($capturedImage);
            } elseif (!empty($_FILES['attachment']['tmp_name'])) {
                $attachmentPath = UploadService::handleUploadedFile($_FILES['attachment']);
            }

            $ticketId = Ticket::create([
                'requester_id'     => $userId,
                'requester_name'   => $name,
                'category_id'      => 1, // CAT_SSO: Hỗ trợ tài khoản
                'priority_id'      => 2, // HIGH
                'is_guest_ticket'  => 1,
                'contact_email'    => $contactEmail,
                'title'            => "[CỨU HỘ] {$issueTitle} - SV {$name} ({$userCode})",
                'description'      => "SĐT: {$phone}. Chi tiết: {$description}",
                'system_affected'  => 'Portal SSO / Tài khoản',
                'attachment_path'  => $attachmentPath,
            ]);

            $ticket = Ticket::findById($ticketId);
            $ticketCode = $ticket['ticket_code'];

            $this->flash('success', "Đã gửi phiếu cứu hộ thành công! <strong>Mã tra cứu duy nhất của bạn: <span style='font-size:16px; color:#0284c7; letter-spacing:0.5px;'>{$ticketCode}</span></strong>. Vấn đề này dự kiến sẽ được Kỹ thuật viên SSO tiếp nhận và xử lý trong vòng <strong>2 giờ làm việc</strong>. Bạn chỉ cần ghi nhớ mã này để tra cứu tiến độ!");
            $this->redirect("/guest/lookup?code={$ticketCode}");
        } catch (\PDOException $e) {
            $this->flash('danger', 'Hệ thống cơ sở dữ liệu đang bận xử lý hoặc gặp sự cố kết nối. Vui lòng thử lại sau ít phút hoặc liên hệ trực tiếp Trung tâm CNTT!');
            $this->redirect('/guest/ticket');
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi gửi phiếu cứu hộ: ' . $e->getMessage());
            $this->redirect('/guest/ticket');
        }
    }

    public function showLookup(): void {
        $code = trim($_GET['code'] ?? '');
        $ticket = null;
        $isInternalTicket = false;

        if (!empty($code)) {
            $ticket = Ticket::findByTicketCode($code);
            if ($ticket && empty($ticket['is_guest_ticket'])) {
                $isInternalTicket = true;
            }
        }

        $this->view('guest/lookup_ticket', [
            'ticket'           => $ticket,
            'isInternalTicket' => $isInternalTicket,
            'code'             => $code,
            'title'            => 'Tra Cứu Tiến Độ Phiếu Cứu Hộ',
        ]);
    }
}


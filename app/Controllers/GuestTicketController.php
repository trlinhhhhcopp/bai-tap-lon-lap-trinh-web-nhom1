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

        if (empty($userCode) || empty($contactEmail)) {
            $this->flash('danger', 'Vui lòng điền đầy đủ Mã SV/CB và Email liên hệ cá nhân!');
            $this->redirect('/guest/ticket');
        }

        try {
            $user = User::findByEmailOrCode($userCode);
            if (!$user) {
                $this->flash('danger', "❌ Không tìm thấy thông tin Sinh viên / Cán bộ có mã '{$userCode}' trong cơ sở dữ liệu nhà trường. Vui lòng kiểm tra lại chính xác Mã SV/CB!");
                $this->redirect('/guest/ticket');
                return;
            }
            if (($user['lock_reason'] ?? '') === 'banned_rescue_abuse' || ($user['lock_reason'] ?? '') === 'spam') {
                $this->flash('danger', '⛔ Tài khoản này đã bị KHÓA VĨNH VIỄN do cố tình nghịch trạm cứu hộ hoặc spam vi phạm nội quy. Hệ thống từ chối tiếp nhận phiếu cứu hộ từ tài khoản này!');
                $this->redirect('/guest/ticket');
                return;
            }
            if (empty($name)) {
                $name = $user['name'];
            }
            $userId = (int)$user['id'];

            // 1. KIỂM TRA ĐƠN CỨU HỘ TRÙNG LẶP (ANTI-DUPLICATE RESCUE TICKET)
            require_once __DIR__ . '/../Repositories/TicketRepository.php';
            $dupRescue = \TicketRepository::findDuplicateGuest($userId);
            if ($dupRescue) {
                $statusLabels = [
                    'new'               => 'Mới tiếp nhận (Chờ KTV xác thực)',
                    'in_progress'       => 'Đang xử lý kỹ thuật',
                    'waiting_requester' => 'Đang chờ phản hồi',
                    'resolved'          => 'KTV đã duyệt (Chờ Admin phê duyệt mở khóa)',
                    'closed'            => 'Đã hoàn tất / Đóng',
                ];
                $stText = $statusLabels[$dupRescue['status']] ?? $dupRescue['status'];
                $sentTime = date('H:i d/m/Y', strtotime($dupRescue['created_at']));
                $existingCode = $dupRescue['ticket_code'];

                $this->flash('warning', "⚠️ <strong>PHÁT HIỆN ĐƠN CỨU HỘ TRÙNG LẶP:</strong> Bạn đã có đơn cứu hộ mã <strong>{$existingCode}</strong> (Gửi lúc {$sentTime}, trạng thái: <strong>{$stText}</strong>). Theo quy định, mỗi sinh viên/cán bộ chỉ gửi 1 đơn cứu hộ trong 1 ngày hoặc khi đơn trước đó đang được xử lý. Bạn có thể theo dõi tiến độ ngay tại đây!");
                $this->redirect("/guest/lookup?code=" . urlencode($existingCode));
                return;
            }

            $issueTitle = trim($_POST['issue_title'] ?? 'Yêu cầu mở khóa tài khoản');

            $attachmentPath = null;
            $capturedImage = trim($_POST['captured_image'] ?? '');
            if (!empty($capturedImage)) {
                $attachmentPath = UploadService::handleBase64Snapshot($capturedImage);
            } elseif (!empty($_FILES['attachment']['tmp_name'])) {
                $attachmentPath = UploadService::handleUploadedFile($_FILES['attachment']);
            }

            // 2. THUẬT TOÁN ĐIỀU PHỐI TRIAGE: TỰ ĐỘNG KIỂM TRA CA TRỰC KTV (is_on_duty)
            require_once __DIR__ . '/../Services/TicketService.php';
            $dispatch = \App\Services\TicketService::dispatchAgent(1); // 1 = CAT_SSO
            $assignedAgentId   = (int)$dispatch['agent_id'];
            $assignedAgentName = $dispatch['agent_name'];

            $ticketId = Ticket::create([
                'requester_id'     => $userId,
                'requester_name'   => $name,
                'assigned_to'      => $assignedAgentId,
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

            // Gửi thông báo đến KTV phụ trách trực tiếp và nhóm kỹ thuật viên trực ca
            try {
                require_once __DIR__ . '/../Services/NotificationService.php';
                \NotificationService::send(
                    $assignedAgentId,
                    "🚑 CÓ ĐƠN CỨU HỘ MỚI #{$ticketCode}",
                    "Sinh viên {$name} ({$userCode}) vừa gửi đơn cứu hộ tài khoản. Vui lòng kiểm tra và xác thực hồ sơ!",
                    "tickets/show.php?id={$ticketId}",
                    'guest_rescue',
                    'agent'
                );
                \NotificationService::sendToRole(
                    'agent',
                    "🚑 ĐƠN CỨU HỘ KHẨN CẤP #{$ticketCode}",
                    "Trạm cứu hộ vừa tiếp nhận đơn của sinh viên {$name} ({$userCode}). Đã phân công cho {$assignedAgentName}!",
                    "tickets/index.php?warning=guest",
                    'guest_rescue',
                    $assignedAgentId
                );
            } catch (\Throwable $e) {}

            $dispatchNotice = !empty($dispatch['is_overflow']) 
                ? " (Hệ thống tự động điều phối sang <strong>{$assignedAgentName}</strong> do KTV chuyên trách đang nghỉ ca/báo ốm)" 
                : "";

            $this->flash('success', "Đã gửi phiếu cứu hộ thành công! <strong>Mã tra cứu duy nhất của bạn: <span style='font-size:16px; color:#0284c7; letter-spacing:0.5px;'>{$ticketCode}</span></strong>. Yêu cầu đã được tự động chuyển đến <strong>{$assignedAgentName}</strong> tiếp nhận xử lý trong vòng <strong>2 giờ làm việc</strong>{$dispatchNotice}.");
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

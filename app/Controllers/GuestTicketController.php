<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ticket;
use App\Models\User;
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
            $pin = str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $user = User::findByEmailOrCode($userCode);
            $userId = $user ? $user['id'] : 1; // Gán tạm nếu không có

            $ticketId = Ticket::create([
                'requester_id'     => $userId,
                'category_id'      => 1, // CAT_SSO: Hỗ trợ tài khoản
                'priority_id'      => 2, // HIGH
                'is_guest_ticket'  => 1,
                'contact_email'    => $contactEmail,
                'lookup_pin'       => $pin,
                'title'            => "[CỨU HỘ TÀI KHOẢN] Yêu cầu mở khóa cho SV {$name} ({$userCode})",
                'description'      => "SĐT: {$phone}. Chi tiết: {$description}",
                'system_affected'  => 'Portal SSO / Tài khoản',
            ]);

            $ticket = Ticket::findById($ticketId);
            $ticketCode = $ticket['ticket_code'];

            $this->flash('success', "Đã gửi phiếu cứu hộ thành công! <strong>Mã phiếu: {$ticketCode}</strong> · <strong>Mã PIN tra cứu: {$pin}</strong>. Vui lòng lưu lại 2 thông tin này để theo dõi tiến độ mở khóa!");
            $this->redirect("/guest/lookup?code={$ticketCode}&pin={$pin}");
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi gửi phiếu: ' . $e->getMessage());
            $this->redirect('/guest/ticket');
        }
    }

    public function showLookup(): void {
        $code = trim($_GET['code'] ?? '');
        $pin  = trim($_GET['pin'] ?? '');
        $ticket = null;

        if (!empty($code) && !empty($pin)) {
            $ticket = Ticket::findByGuestCodeAndPin($code, $pin);
        }

        $this->view('guest/lookup_ticket', [
            'ticket' => $ticket,
            'code'   => $code,
            'pin'    => $pin,
            'title'  => 'Tra Cứu Phiếu Cứu Hộ Tài Khoản Bằng Mã PIN',
        ]);
    }
}


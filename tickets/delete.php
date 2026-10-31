<?php
// ==============================================================================
// BÀI 5: XÓA PHIẾU AN TOÀN BẰNG PHƯƠNG THỨC POST (SLIDE 7)
// Tuyệt đối không xóa bằng GET để tránh tấn công CSRF / click nhầm
// Quyền xóa: Admin HOẶC Người tạo phiếu trong 15 phút đầu (KTV không có quyền)
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', "Phương thức không hợp lệ. Xóa dữ liệu bắt buộc phải dùng POST.");
    header('Location: index.php');
    exit;
}

$currentUser = $_SESSION['user'];
$userRole    = $currentUser['role'];
$id = (int)($_POST['id'] ?? 0);
$ticket = TicketRepository::findById($id);

if (!$ticket) {
    set_flash('error', "Không tìm thấy phiếu cần xóa.");
    header('Location: index.php');
    exit;
}

// Kiểm tra quyền xóa:
// - Admin có toàn quyền xóa
// - Người gửi (requester/lecturer) chỉ được xóa trong 15 phút đầu và phiếu chưa được xử lý
$minutesSinceCreated = (time() - strtotime($ticket['created_at'])) / 60;
$isRequesterOwner = in_array($userRole, ['requester', 'lecturer']) && ((int)$ticket['requester_id'] === (int)$currentUser['id']);
$canRequesterDelete = $isRequesterOwner && ($minutesSinceCreated <= 15) && in_array($ticket['status'], ['new', 'waiting_requester']);

if ($userRole !== 'admin' && !$canRequesterDelete) {
    set_flash('error', "Bạn không có quyền xóa phiếu hỗ trợ này!");
    header('Location: show.php?id=' . $id);
    exit;
}

try {
    TicketRepository::delete($id);
    set_flash('success', "Đã xóa thành công phiếu hỗ trợ {$ticket['ticket_code']}!");
} catch (Exception $e) {
    set_flash('error', "Lỗi khi xóa CSDL: " . $e->getMessage());
}

header('Location: index.php');
exit;

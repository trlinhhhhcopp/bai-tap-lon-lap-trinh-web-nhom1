<?php
// ==============================================================================
// BÀI 5: XÓA PHIẾU AN TOÀN BẰNG PHƯƠNG THỨC POST (SLIDE 7)
// Tuyệt đối không xóa bằng GET để tránh tấn công CSRF / click nhầm
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', "Phương thức không hợp lệ. Xóa dữ liệu bắt buộc phải dùng POST.");
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$ticket = TicketRepository::findById($id);

if (!$ticket) {
    set_flash('error', "Không tìm thấy phiếu cần xóa.");
    header('Location: index.php');
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


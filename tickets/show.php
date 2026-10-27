<?php
// ==============================================================================
// BÀI 5: XEM CHI TIẾT PHIẾU HỖ TRỢ & TRAO ĐỔI (SLIDE 7 - DETAIL)
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

$id = (int)($_GET['id'] ?? 0);
$ticket = TicketRepository::findById($id);

// Xử lý trường hợp không tìm thấy bản ghi (Slide 7)
if (!$ticket) {
    set_flash('error', "Không tìm thấy phiếu hỗ trợ với ID #{$id}.");
    header('Location: index.php');
    exit;
}

// Xử lý thêm tin nhắn trao đổi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $message = trim($_POST['message'] ?? '');
    $userId  = (int)($_POST['user_id'] ?? 2); // Mặc định KTV Nguyễn Văn An nếu không chọn
    $isInternal = isset($_POST['is_internal']) && $_POST['is_internal'] === '1';

    if (!empty($message)) {
        TicketRepository::addReply($id, $userId, $message, $isInternal);
        set_flash('success', "Đã gửi phản hồi thành công!");
        header("Location: show.php?id={$id}");
        exit;
    }
}

// Xử lý cập nhật nhanh trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $newStatus = trim($_POST['status'] ?? '');
    if (in_array($newStatus, ['new', 'in_progress', 'waiting_requester', 'resolved', 'closed'])) {
        TicketRepository::updateStatus($id, $newStatus);
        set_flash('success', "Đã cập nhật trạng thái phiếu sang '{$newStatus}'!");
        header("Location: show.php?id={$id}");
        exit;
    }
}

$replies = TicketRepository::getReplies($id);
$ktvs    = TicketRepository::getUsers('agent');
$flash   = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu <?php echo htmlspecialchars($ticket['ticket_code']); ?> - Helpdesk</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-6">

<div class="max-w-5xl mx-auto space-y-6">

    <!-- HEADER -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono font-bold text-sm text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-200">
                    <?php echo htmlspecialchars($ticket['ticket_code']); ?>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                    <?php echo htmlspecialchars($ticket['category_name']); ?>
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900"><?php echo htmlspecialchars($ticket['title']); ?></h1>
            <p class="text-xs text-slate-500 mt-0.5">Ngày gửi: <?php echo htmlspecialchars($ticket['created_at']); ?> • Hệ thống: <?php echo htmlspecialchars($ticket['system_affected']); ?></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="edit.php?id=<?php echo $ticket['id']; ?>" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition">
                ✏️ Chỉnh sửa phiếu
            </a>
            <a href="index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                ← Danh sách
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    <?php if ($flash): ?>
        <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <!-- NỘI DUNG CHÍNH (2 CỘT) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- CỘT TRÁI: NỘI DUNG SỰ CỐ & KHUNG TRAO ĐỔI (8 CỘT) -->
        <div class="md:col-span-8 space-y-6">
            
            <!-- Mô tả sự cố ban đầu -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <span>📝</span> Mô Tả Chi Tiết Từ Người Gửi:
                </h3>
                <div class="p-4 bg-slate-50 rounded-xl text-xs text-slate-800 leading-relaxed whitespace-pre-line border border-slate-200">
                    <?php echo htmlspecialchars($ticket['description']); ?>
                </div>
            </div>

            <!-- Khung Trao Đổi (Ticket Replies) -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <span>💬</span> Lịch Sử Phản Hồi & Ghi Chú (<?php echo count($replies); ?>):
                </h3>

                <?php if (empty($replies)): ?>
                    <p class="text-xs text-slate-400 italic">Chưa có phản hồi nào cho phiếu này.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($replies as $r): ?>
                            <div class="p-3.5 rounded-xl border text-xs space-y-1.5 <?php echo $r['is_internal'] ? 'bg-amber-50/70 border-amber-200' : 'bg-slate-50 border-slate-200'; ?>">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900">
                                        <?php echo htmlspecialchars($r['user_name']); ?>
                                        <span class="text-slate-400 font-normal">(<?php echo $r['user_role'] === 'agent' ? 'KTV' : 'Người gửi'; ?>)</span>
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <?php if ($r['is_internal']): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900">🔒 Ghi chú nội bộ</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-100 text-indigo-700">💬 Công khai</span>
                                        <?php endif; ?>
                                        <span class="text-slate-400 text-[10px]"><?php echo htmlspecialchars($r['created_at']); ?></span>
                                    </div>
                                </div>
                                <p class="text-slate-700 leading-relaxed"><?php echo htmlspecialchars($r['message']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Form gửi phản hồi mới -->
                <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>" class="pt-3 border-t border-slate-100 space-y-3 text-xs">
                    <input type="hidden" name="action" value="reply" />
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Thêm phản hồi / cập nhật tiến độ:</label>
                        <textarea name="message" rows="3" required placeholder="Nhập nội dung trao đổi hoặc giải pháp xử lý..." 
                                  class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-1.5 cursor-pointer text-slate-700 font-semibold">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                                <span>🔒 Ghi chú nội bộ KTV (Người dùng không thấy)</span>
                            </label>
                        </div>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm">
                            Gửi phản hồi
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- CỘT PHẢI: THÔNG TIN TRẠNG THÁI & SLA (4 CỘT) -->
        <div class="md:col-span-4 space-y-6">

            <!-- Cập nhật trạng thái nhanh -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Trạng Thái Hiện Tại</h3>
                
                <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>" class="space-y-3">
                    <input type="hidden" name="action" value="update_status" />
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="new" <?php echo $ticket['status'] === 'new' ? 'selected' : ''; ?>>Mới tiếp nhận (new)</option>
                        <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>Đang xử lý (in_progress)</option>
                        <option value="waiting_requester" <?php echo $ticket['status'] === 'waiting_requester' ? 'selected' : ''; ?>>Chờ phản hồi (waiting)</option>
                        <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Đã giải quyết (resolved)</option>
                        <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Đã đóng hoàn tất (closed)</option>
                    </select>
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                        Cập nhật trạng thái
                    </button>
                </form>
            </div>

            <!-- Thông tin người liên quan -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Thông Tin Liên Quan</h3>
                
                <div class="space-y-2 border-b border-slate-100 pb-3">
                    <span class="text-slate-400 block text-[11px]">Người gửi yêu cầu:</span>
                    <strong class="text-slate-900 block"><?php echo htmlspecialchars($ticket['requester_name']); ?></strong>
                    <span class="text-slate-500 font-mono block">Mã: <?php echo htmlspecialchars($ticket['requester_code']); ?></span>
                    <span class="text-slate-500 block"><?php echo htmlspecialchars($ticket['requester_email']); ?></span>
                </div>

                <div class="space-y-2 border-b border-slate-100 pb-3">
                    <span class="text-slate-400 block text-[11px]">Kỹ thuật viên phụ trách:</span>
                    <?php if ($ticket['agent_name']): ?>
                        <strong class="text-indigo-700 block"><?php echo htmlspecialchars($ticket['agent_name']); ?></strong>
                    <?php else: ?>
                        <span class="text-amber-600 italic block">Chưa được phân công</span>
                    <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <span class="text-slate-400 block text-[11px]">Mức độ ưu tiên:</span>
                    <strong class="text-rose-600 block"><?php echo htmlspecialchars($ticket['priority_name']); ?></strong>
                </div>
            </div>

            <!-- Thông số SLA (Thời gian) -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-2.5 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                    <span>⏱️</span> Tiến Độ SLA Cam Kết
                </h3>
                
                <div class="p-2.5 bg-slate-50 rounded-xl space-y-1 border border-slate-200">
                    <span class="text-slate-400 text-[10px] block">Hạn Tiếp Nhận (Triage SLA):</span>
                    <strong class="font-mono text-slate-800"><?php echo htmlspecialchars($ticket['triage_due_at']); ?></strong>
                </div>

                <div class="p-2.5 bg-indigo-50/70 rounded-xl space-y-1 border border-indigo-200">
                    <span class="text-indigo-700 text-[10px] block font-bold">Hạn Giải Quyết Xong (Due at):</span>
                    <strong class="font-mono text-indigo-950"><?php echo htmlspecialchars($ticket['due_at']); ?></strong>
                </div>

                <?php if ($ticket['resolved_at']): ?>
                    <div class="p-2.5 bg-emerald-50 rounded-xl space-y-1 border border-emerald-200">
                        <span class="text-emerald-700 text-[10px] block font-bold">Thời điểm giải quyết:</span>
                        <strong class="font-mono text-emerald-950"><?php echo htmlspecialchars($ticket['resolved_at']); ?></strong>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</div>

</body>
</html>


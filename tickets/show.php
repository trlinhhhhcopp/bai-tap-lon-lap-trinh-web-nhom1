<?php
// ==============================================================================
// BÀI 5: XEM CHI TIẾT PHIẾU HỖ TRỢ & HỆ THỐNG PHẢN HỒI HAI CHIỀU (SLIDE 7 & 10)
// THỰC THỂ: TICKETS & TICKET_REPLIES (MODEL TICKET & MODEL REPLY)
// Phân hệ: Core Architecture & Agent Workspace
// Trưởng nhóm phụ trách độc quyền: PHẠM THỊ THANH NGÂN (MSSV: 224001736 - D24-CNTT1)
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

// Chế độ góc nhìn người dùng (Persona Simulator để test phân quyền)
// 'agent': Góc nhìn Kỹ thuật viên / Admin (Thấy tất cả kể cả ghi chú mật 🔒)
// 'student': Góc nhìn Sinh viên / Giảng viên (Chỉ thấy tin công khai, ghi chú mật bị ẩn 100%)
$viewAs = $_GET['view_as'] ?? 'agent';
$canViewInternal = ($viewAs === 'agent');

// Xử lý thêm tin nhắn trao đổi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $message        = trim($_POST['message'] ?? '');
    $userId         = (int)($_POST['user_id'] ?? ($ticket['assigned_to'] ?: 2));
    $isInternal     = isset($_POST['is_internal']) && $_POST['is_internal'] === '1';
    $forceStatus    = !empty($_POST['quick_status']) ? trim($_POST['quick_status']) : null;
    $attachmentPath = null;

    // Xử lý tệp đính kèm (KTV gửi ảnh minh chứng hoặc SV gửi ảnh chụp lỗi)
    if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed, true)) {
            set_flash('error', "Chỉ hỗ trợ tệp đính kèm dạng hình ảnh (JPG, PNG, WEBP, GIF)!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }

        if ($fileSize > 5 * 1024 * 1024) {
            set_flash('error', "Kích thước ảnh vượt quá dung lượng tối đa 5MB!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }

        $uploadDir = __DIR__ . '/../uploads/replies/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeName   = 'reply_' . $id . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $targetFile = $uploadDir . $safeName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $attachmentPath = 'uploads/replies/' . $safeName;
        }
    }

    // Cho phép gửi nếu có nội dung tin nhắn, HOẶC có đính kèm ảnh, HOẶC có chọn chuyển trạng thái
    if (!empty($message) || !empty($attachmentPath) || !empty($forceStatus)) {
        if (empty($message)) {
            if (!empty($attachmentPath)) {
                $message = "Đã gửi kèm 1 tệp hình ảnh minh chứng.";
            } elseif ($forceStatus === 'resolved') {
                $message = "Kỹ thuật viên đã xử lý và cung cấp giải pháp khắc phục sự cố.";
            } elseif ($forceStatus === 'closed') {
                $message = "Kỹ thuật viên đã hoàn tất hỗ trợ và đóng phiếu thành công.";
            } elseif ($forceStatus === 'waiting_requester') {
                $message = "Kỹ thuật viên đang chờ phản hồi xác nhận từ người dùng.";
            } elseif ($forceStatus === 'in_progress') {
                $message = "Kỹ thuật viên đang tiếp tục tiến hành xử lý yêu cầu.";
            } else {
                $message = "Đã cập nhật trạng thái phiếu hỗ trợ.";
            }
        }

        TicketRepository::addReply($id, $userId, $message, $isInternal, $forceStatus, $attachmentPath);
        
        $statusLabels = [
            'new'               => 'Mới tiếp nhận (new)',
            'in_progress'       => 'Đang xử lý (in_progress)',
            'waiting_requester' => 'Chờ người dùng kiểm tra (waiting)',
            'resolved'          => 'Đã có giải pháp (resolved)',
            'closed'            => 'Đã hoàn tất & Đóng phiếu (closed)',
        ];
        
        $notice = "Đã gửi phản hồi thành công!";
        if ($forceStatus && isset($statusLabels[$forceStatus])) {
            $notice .= " Trạng thái phiếu đã được cập nhật sang: <strong>{$statusLabels[$forceStatus]}</strong>.";
        }
        if ($attachmentPath) {
            $notice .= " (Đã tải lên tệp ảnh đính kèm thành công)";
        }
        if ($isInternal) {
            $notice .= " [🔒 Đã lưu làm Ghi chú nội bộ]";
        }
        set_flash('success', $notice);
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    } else {
        set_flash('error', "Vui lòng nhập nội dung trao đổi, chọn ảnh đính kèm hoặc chọn hành động trạng thái!");
    }
}

// Xử lý cập nhật nhanh trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $newStatus = trim($_POST['status'] ?? '');
    if (in_array($newStatus, ['new', 'in_progress', 'waiting_requester', 'resolved', 'closed'])) {
        TicketRepository::updateStatus($id, $newStatus);
        set_flash('success', "Đã cập nhật trạng thái phiếu sang '{$newStatus}'!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// Lấy danh sách phản hồi (có kiểm soát quyền xem ghi chú nội bộ)
$replies = TicketRepository::getReplies($id, $canViewInternal);
$agents  = TicketRepository::getUsers('agent');
$flash   = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu <?php echo htmlspecialchars($ticket['ticket_code']); ?> - Trao Đổi Phản Hồi</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-6">

<div class="max-w-5xl mx-auto space-y-6">

    <!-- BANNER NHIỆM VỤ TRƯỞNG NHÓM -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-4 shadow-sm border border-indigo-900/40 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <span class="p-2 bg-indigo-500/20 text-indigo-300 rounded-xl font-bold text-base">👑</span>
            <div>
                <p class="font-bold text-white tracking-wide">PHÂN HỆ: CORE ARCHITECTURE & AGENT WORKSPACE</p>
                <p class="text-indigo-200/80">Phụ trách độc quyền: <strong class="text-white">Phạm Thị Thanh Ngân</strong> (MSSV: 224001736 · Khóa TUD D24 · Lớp D24-CNTT1)</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 bg-indigo-500/20 text-indigo-300 rounded-lg font-mono font-bold">Model Ticket.php</span>
            <span class="text-slate-500">•</span>
            <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 rounded-lg font-mono font-bold">Model Reply.php</span>
        </div>
    </div>

    <!-- HEADER PHIẾU -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="font-mono font-bold text-sm text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-200">
                    <?php echo htmlspecialchars($ticket['ticket_code']); ?>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                    <?php echo htmlspecialchars($ticket['category_name']); ?>
                </span>
                <?php
                $statusColors = [
                    'new'               => 'bg-blue-50 text-blue-700 border-blue-200',
                    'in_progress'       => 'bg-amber-50 text-amber-700 border-amber-200',
                    'waiting_requester' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'resolved'          => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'closed'            => 'bg-slate-100 text-slate-700 border-slate-300',
                ];
                $statusLabels = [
                    'new'               => 'Mới tiếp nhận',
                    'in_progress'       => 'Đang xử lý',
                    'waiting_requester' => 'Chờ người gửi phản hồi',
                    'resolved'          => 'Đã có giải pháp',
                    'closed'            => 'Đã đóng hoàn tất',
                ];
                $sColor = $statusColors[$ticket['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                $sLabel = $statusLabels[$ticket['status']] ?? $ticket['status'];
                ?>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?php echo $sColor; ?>">
                    ● <?php echo $sLabel; ?>
                </span>
            </div>
            <h1 class="text-xl font-black text-slate-900"><?php echo htmlspecialchars($ticket['title']); ?></h1>
            <p class="text-xs text-slate-500 mt-0.5">Ngày gửi: <?php echo htmlspecialchars($ticket['created_at']); ?> • Hệ thống: <?php echo htmlspecialchars($ticket['system_affected']); ?></p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="edit.php?id=<?php echo $ticket['id']; ?>" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition">
                ✏️ Sửa phiếu
            </a>
            <a href="index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                ← Danh sách
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    <?php if ($flash): ?>
        <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
            <?php echo strip_tags($flash['message'], '<strong><b><em><span>'); ?>
        </div>
    <?php endif; ?>

    <!-- THANH ĐIỀU KHIỂN GÓC NHÌN TEST PHÂN QUYỀN (PERSONA TOGGLE) -->
    <div class="bg-indigo-50/70 border border-indigo-200 rounded-2xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2">
            <span class="font-bold text-indigo-900 flex items-center gap-1">
                <span>🛡️</span> Thử Nghiệm Phân Quyền Xem Tin Nhắn:
            </span>
            <span class="text-indigo-700">Hiện đang ở góc nhìn:</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="show.php?id=<?php echo $id; ?>&view_as=agent" 
               class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1 <?php echo $viewAs === 'agent' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-indigo-700 hover:bg-indigo-100 border border-indigo-200'; ?>">
                <span>👨‍💻</span> KTV / Admin (Xem cả Ghi chú mật 🔒)
            </a>
            <a href="show.php?id=<?php echo $id; ?>&view_as=student" 
               class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1 <?php echo $viewAs === 'student' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-indigo-700 hover:bg-indigo-100 border border-indigo-200'; ?>">
                <span>🎓</span> Sinh Viên (Chỉ xem tin công khai)
            </a>
        </div>
    </div>

    <!-- CẢNH BÁO BẢO MẬT NẾU Ở GÓC NHÌN SINH VIÊN -->
    <?php if ($viewAs === 'student'): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 flex items-center gap-2">
            <span>🔒</span>
            <span><strong>Cơ chế bảo mật cách ly:</strong> Sinh viên chỉ có thể đọc các tin nhắn trao đổi công khai. Mọi ghi chú kỹ thuật nội bộ của KTV đều được hệ thống ẩn hoàn toàn để bảo mật thông tin.</span>
        </div>
    <?php endif; ?>

    <!-- NỘI DUNG CHÍNH (2 CỘT) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- CỘT TRÁI: MÔ TẢ & CHAT TIMELINE (8 CỘT) -->
        <div class="md:col-span-8 space-y-6">
            
            <!-- Mô tả sự cố ban đầu -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📝</span> Mô Tả Sự Cố Ban Đầu:
                    </h3>
                    <span class="text-[11px] text-slate-400 font-mono">Bởi: <?php echo htmlspecialchars($ticket['requester_name']); ?></span>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl text-xs text-slate-800 leading-relaxed whitespace-pre-line border border-slate-200">
                    <?php echo htmlspecialchars($ticket['description']); ?>
                </div>
            </div>

            <!-- Khung Trao Đổi (Ticket Replies) -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span>💬</span> Lịch Sử Phản Hồi & Ghi Chú (<?php echo count($replies); ?> lượt):
                    </h3>
                    <span class="text-[10px] text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded font-mono font-bold">
                        Bảng ticket_replies
                    </span>
                </div>

                <?php if (empty($replies)): ?>
                    <div class="text-center py-8 text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        Chưa có phản hồi nào cho phiếu này. Hãy nhập nội dung bên dưới để bắt đầu trao đổi!
                    </div>
                <?php else: ?>
                    <div class="space-y-3.5">
                        <?php foreach ($replies as $r): ?>
                            <?php 
                            $isAgent = ($r['user_role'] === 'agent' || $r['user_role'] === 'admin');
                            ?>
                            <div class="p-4 rounded-xl border text-xs space-y-2 transition <?php echo $r['is_internal'] ? 'bg-amber-50/80 border-amber-300 shadow-sm' : ($isAgent ? 'bg-indigo-50/40 border-indigo-200' : 'bg-slate-50 border-slate-200'); ?>">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white <?php echo $r['is_internal'] ? 'bg-amber-600' : ($isAgent ? 'bg-indigo-600' : 'bg-emerald-600'); ?>">
                                            <?php echo mb_substr($r['user_name'], 0, 1); ?>
                                        </div>
                                        <span class="font-bold text-slate-900">
                                            <?php echo htmlspecialchars($r['user_name']); ?>
                                        </span>
                                        <span class="text-slate-400 font-normal">
                                            (<?php echo $isAgent ? 'Kỹ thuật viên' : 'Người gửi'; ?>)
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <?php if ($r['is_internal']): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300 flex items-center gap-1">
                                                <span>🔒</span> Ghi chú nội bộ KTV
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700">
                                                💬 Công khai
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-slate-400 text-[10px] font-mono"><?php echo htmlspecialchars($r['created_at']); ?></span>
                                    </div>
                                </div>
                                <div class="text-slate-700 leading-relaxed pl-8">
                                    <?php echo nl2br(htmlspecialchars($r['message'])); ?>
                                </div>

                                <!-- HIỂN THỊ ẢNH ĐÍNH KÈM NẾU CÓ -->
                                <?php if (!empty($r['attachment_path'])): ?>
                                    <div class="mt-2.5 pt-2 border-t border-slate-200/60 pl-8">
                                        <div class="flex items-center gap-1 text-[11px] font-bold text-indigo-700 mb-1.5">
                                            <span>📎</span> Tệp ảnh đính kèm:
                                        </div>
                                        <a href="../<?php echo htmlspecialchars($r['attachment_path']); ?>" target="_blank" class="inline-block group">
                                            <img src="../<?php echo htmlspecialchars($r['attachment_path']); ?>" 
                                                 alt="Ảnh đính kèm" 
                                                 class="max-h-52 max-w-sm rounded-xl border border-slate-300 shadow-sm group-hover:opacity-90 transition object-contain bg-white p-1" />
                                            <span class="block text-[10px] text-slate-400 mt-1">Bấm để phóng to ảnh ↗</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Form gửi phản hồi mới -->
                <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" enctype="multipart/form-data" class="pt-4 border-t border-slate-100 space-y-3.5 text-xs">
                    <input type="hidden" name="action" value="reply" />

                    <!-- Chọn người gửi phản hồi (Mô phỏng 2 chiều) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Người gửi phản hồi:</label>
                            <select name="user_id" id="senderSelect" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <optgroup label="Kỹ thuật viên IT:">
                                    <?php foreach ($agents as $ag): ?>
                                        <option value="<?php echo $ag['id']; ?>" <?php echo ($ticket['assigned_to'] == $ag['id'] || ($viewAs === 'agent' && $ag['id'] == 2)) ? 'selected' : ''; ?>>
                                            KTV: <?php echo htmlspecialchars($ag['name']); ?> (<?php echo htmlspecialchars($ag['user_code']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Sinh viên / Người tạo phiếu:">
                                    <option value="<?php echo $ticket['requester_id']; ?>" <?php echo $viewAs === 'student' ? 'selected' : ''; ?>>
                                        SV: <?php echo htmlspecialchars($ticket['requester_name']); ?> (<?php echo htmlspecialchars($ticket['requester_code']); ?>)
                                    </option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Hành động trạng thái sau phản hồi:</label>
                            <select name="quick_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Tự động thông minh theo State Machine</option>
                                <option value="waiting_requester">Chuyển sang: Chờ người dùng kiểm tra (waiting)</option>
                                <option value="resolved">Chuyển sang: Đã có giải pháp (resolved)</option>
                                <option value="closed">Chuyển sang: Đã hoàn tất & Đóng phiếu (closed)</option>
                                <option value="in_progress">Chuyển sang: Tiếp tục xử lý (in_progress)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nội dung trao đổi / Giải pháp kỹ thuật:</label>
                        <textarea name="message" rows="3" placeholder="Nhập nội dung trao đổi, kết quả xử lý hoặc ghi chú kỹ thuật..." 
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs"></textarea>
                    </div>

                    <!-- ĐÍNH KÈM TỆP ẢNH (SV GỬI ẢNH LỖI, KTV GỬI ẢNH KẾT QUẢ) -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">📎 Đính kèm tệp ảnh (Ảnh chụp màn hình lỗi / Ảnh minh chứng kết quả fix):</label>
                        <input type="file" name="attachment" accept="image/png,image/jpeg,image/webp,image/gif" 
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-300 rounded-xl bg-slate-50 p-1" />
                        <span class="text-[10.5px] text-slate-400 mt-0.5 block">Định dạng hỗ trợ: JPG, PNG, WEBP, GIF (Tối đa 5MB)</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-1.5 cursor-pointer text-slate-700 font-semibold" id="internalBox">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                                <span class="text-amber-800">🔒 Đặt làm Ghi chú nội bộ KTV (Sinh viên không thấy)</span>
                            </label>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                            <span>📤</span> Gửi Phản Hồi Ngay
                        </button>
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded-xl text-[11px] text-slate-500 leading-relaxed border border-slate-200">
                        ⚡ <strong>Quy tắc tự động State Machine:</strong> Khi KTV phản hồi công khai, phiếu tự chuyển sang <em>waiting_requester</em>. Khi Sinh viên phản hồi lại, phiếu tự quay về <em>in_progress</em>.
                    </div>
                </form>
            </div>

        </div>

        <!-- CỘT PHẢI: THÔNG TIN TRẠNG THÁI & SLA (4 CỘT) -->
        <div class="md:col-span-4 space-y-6">

            <!-- Cập nhật trạng thái thủ công -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Cập Nhật Trạng Thái Thủ Công</h3>
                
                <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-3">
                    <input type="hidden" name="action" value="update_status" />
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="new" <?php echo $ticket['status'] === 'new' ? 'selected' : ''; ?>>1. Mới tiếp nhận (new)</option>
                        <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>2. Đang xử lý (in_progress)</option>
                        <option value="waiting_requester" <?php echo $ticket['status'] === 'waiting_requester' ? 'selected' : ''; ?>>3. Chờ phản hồi (waiting)</option>
                        <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>4. Đã giải quyết (resolved)</option>
                        <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>5. Đã đóng hoàn tất (closed)</option>
                    </select>
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                        Cập nhật trạng thái
                    </button>
                </form>
            </div>

            <!-- Thông tin người liên quan -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Thông Tin Liên Quan</h3>
                
                <div class="space-y-1.5 border-b border-slate-100 pb-3">
                    <span class="text-slate-400 block text-[11px]">Người gửi yêu cầu:</span>
                    <strong class="text-slate-900 block"><?php echo htmlspecialchars($ticket['requester_name']); ?></strong>
                    <span class="text-slate-500 font-mono block">Mã SV/CB: <?php echo htmlspecialchars($ticket['requester_code']); ?></span>
                    <span class="text-slate-500 block"><?php echo htmlspecialchars($ticket['requester_email']); ?></span>
                    <?php if (!empty($ticket['class_name'])): ?>
                        <span class="text-indigo-600 block text-[11px]">Lớp: <?php echo htmlspecialchars($ticket['class_name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="space-y-1.5 border-b border-slate-100 pb-3">
                    <span class="text-slate-400 block text-[11px]">Kỹ thuật viên phụ trách:</span>
                    <?php if ($ticket['agent_name']): ?>
                        <strong class="text-indigo-700 block"><?php echo htmlspecialchars($ticket['agent_name']); ?></strong>
                        <span class="text-slate-500 font-mono block">Mã KTV: <?php echo htmlspecialchars($ticket['agent_code']); ?></span>
                    <?php else: ?>
                        <span class="text-amber-600 italic block">Chưa được phân công</span>
                    <?php endif; ?>
                </div>

                <div class="space-y-1">
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

                <?php if ($ticket['first_response_at']): ?>
                    <div class="p-2.5 bg-blue-50 rounded-xl space-y-1 border border-blue-200">
                        <span class="text-blue-700 text-[10px] block font-bold">Mốc phản hồi đầu tiên:</span>
                        <strong class="font-mono text-blue-950"><?php echo htmlspecialchars($ticket['first_response_at']); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if ($ticket['resolved_at']): ?>
                    <div class="p-2.5 bg-emerald-50 rounded-xl space-y-1 border border-emerald-200">
                        <span class="text-emerald-700 text-[10px] block font-bold">Thời điểm giải quyết xong:</span>
                        <strong class="font-mono text-emerald-950"><?php echo htmlspecialchars($ticket['resolved_at']); ?></strong>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</div>

</body>
</html>

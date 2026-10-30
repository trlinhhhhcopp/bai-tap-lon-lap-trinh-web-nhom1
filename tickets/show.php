<?php
// ==============================================================================
// BÀI 5: XEM CHI TIẾT PHIẾU HỖ TRỢ & HỆ THỐNG PHẢN HỒI HAI CHIỀU (SLIDE 7 & 10)
// THỰC THỂ: TICKETS & TICKET_REPLIES (MODEL TICKET & MODEL REPLY)
// Phân hệ: Core Architecture & Agent Workspace
// Trưởng nhóm: PHẠM THỊ THANH NGÂN (MSSV: 224001736 - D24-CNTT1)
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc đăng nhập
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$currentUser = $_SESSION['user'];
$userRole    = $currentUser['role'];
$userType    = $currentUser['user_type'] ?? 'student';

$id = (int)($_GET['id'] ?? 0);
$ticket = TicketRepository::findById($id);

if (!$ticket) {
    set_flash('error', "Không tìm thấy phiếu hỗ trợ với ID #{$id}.");
    header('Location: index.php');
    exit;
}

// Cách ly dữ liệu: Sinh viên & Giảng viên chỉ được xem phiếu của chính mình
if ($userRole === 'requester' && (int)$ticket['requester_id'] !== (int)$currentUser['id']) {
    set_flash('error', "Bạn không có quyền truy cập vào phiếu của tài khoản khác!");
    header('Location: ../index.php');
    exit;
}

// Quyền xem ghi chú nội bộ KTV: Chỉ KTV hoặc Admin mới thấy
$canViewInternal = in_array($userRole, ['agent', 'admin']);
$viewAs = $canViewInternal ? 'agent' : 'student';

// Lấy thông tin đánh giá CSAT của phiếu này (nếu có)
$ratingStmt = db()->prepare("SELECT r.*, u.name as user_name FROM ticket_ratings r LEFT JOIN users u ON r.user_id = u.id WHERE r.ticket_id = :tid LIMIT 1");
$ratingStmt->execute(['tid' => $id]);
$currentRating = $ratingStmt->fetch();

// -----------------------------------------------------------------------------
// 1. XỬ LÝ GỬI ĐÁNH GIÁ CSAT (SINH VIÊN NGHIỆM THU)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rate_ticket') {
    $score = (int)($_POST['score'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');
    $score = max(1, min(5, $score));

    if ($currentRating) {
        $upStmt = db()->prepare("UPDATE ticket_ratings SET score = :score, comment = :comment, created_at = NOW() WHERE ticket_id = :tid");
        $upStmt->execute(['score' => $score, 'comment' => $comment, 'tid' => $id]);
    } else {
        $inStmt = db()->prepare("INSERT INTO ticket_ratings (ticket_id, user_id, score, comment, created_at) VALUES (:tid, :uid, :score, :comment, NOW())");
        $inStmt->execute(['tid' => $id, 'uid' => $currentUser['id'], 'score' => $score, 'comment' => $comment]);
    }

    // Nếu phiếu đang resolved, tự động đóng hoàn tất phiếu
    if ($ticket['status'] === 'resolved') {
        TicketRepository::updateStatus($id, 'closed', 'completed');
    }

    set_flash('success', "Cảm ơn bạn đã nghiệm thu và đánh giá chất lượng dịch vụ: <strong>{$score} ★</strong>!");
    header("Location: show.php?id={$id}&view_as={$viewAs}");
    exit;
}

// -----------------------------------------------------------------------------
// 2. XỬ LÝ KHI SINH VIÊN BẤM "CHƯA XONG (YÊU CẦU MỞ LẠI PHIẾU)"
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reopen_ticket') {
    $reopenNote = trim($_POST['reopen_note'] ?? 'Sự cố vẫn chưa được khắc phục triệt để. Kính nhờ Kỹ thuật viên kiểm tra và hỗ trợ tiếp!');
    TicketRepository::addReply($id, (int)$currentUser['id'], $reopenNote, false, 'in_progress');
    db()->prepare("UPDATE tickets SET reopen_count = reopen_count + 1 WHERE id = :id")->execute(['id' => $id]);
    set_flash('info', "Đã gửi yêu cầu hỗ trợ tiếp! Phiếu đã được kích hoạt lại trạng thái <strong>Đang xử lý</strong>.");
    header("Location: show.php?id={$id}&view_as={$viewAs}");
    exit;
}

// -----------------------------------------------------------------------------
// 3. XỬ LÝ GỬI PHẢN HỒI / GIẢI PHÁP & ĐÍNH KÈM ẢNH
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $message = trim($_POST['message'] ?? '');
    
    if ($userRole === 'requester') {
        $userId = (int)$currentUser['id'];
        $isInternal = false;
        $forceStatus = null;
    } else {
        $userId = (int)($_POST['user_id'] ?? ($ticket['assigned_to'] ?: $currentUser['id']));
        $isInternal = isset($_POST['is_internal']) && $_POST['is_internal'] === '1';
        $forceStatus = !empty($_POST['quick_status']) ? trim($_POST['quick_status']) : null;
    }
    
    $uploadedPaths = [];
    $uploadDir = __DIR__ . '/../uploads/replies/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];

    // Page 6: Hỗ trợ đính kèm tối đa 10 file
    if (!empty($_FILES['attachments']['name']) && is_array($_FILES['attachments']['name'])) {
        $countFiles = count($_FILES['attachments']['name']);
        for ($i = 0; $i < min(10, $countFiles); $i++) {
            if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                $fName = $_FILES['attachments']['name'][$i];
                $fTmp  = $_FILES['attachments']['tmp_name'][$i];
                $fSize = $_FILES['attachments']['size'][$i];
                $ext   = strtolower(pathinfo($fName, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed, true) && $fSize <= 10 * 1024 * 1024) {
                    $safeName = 'reply_' . $id . '_' . time() . '_' . $i . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    if (move_uploaded_file($fTmp, $uploadDir . $safeName)) {
                        $uploadedPaths[] = 'uploads/replies/' . $safeName;
                    }
                }
            }
        }
    } elseif (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fName = $_FILES['attachment']['name'];
        $fTmp  = $_FILES['attachment']['tmp_name'];
        $fSize = $_FILES['attachment']['size'];
        $ext   = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed, true) && $fSize <= 10 * 1024 * 1024) {
            $safeName = 'reply_' . $id . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            if (move_uploaded_file($fTmp, $uploadDir . $safeName)) {
                $uploadedPaths[] = 'uploads/replies/' . $safeName;
            }
        }
    }

    $attachmentPath = null;
    if (!empty($uploadedPaths)) {
        $attachmentPath = json_encode($uploadedPaths, JSON_UNESCAPED_SLASHES);
    }

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
            $notice .= " (Đã tải lên tệp ảnh minh chứng thành công)";
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

// -----------------------------------------------------------------------------
// 4. XỬ LÝ CẬP NHẬT TRẠNG THÁI THỦ CÔNG (KTV / ADMIN)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (in_array($userRole, ['agent', 'admin'])) {
        $newStatus = trim($_POST['status'] ?? '');
        if ($userRole === 'agent' && $newStatus === 'closed') {
            set_flash('error', "Kỹ thuật viên chỉ có thể cập nhật trạng thái đến 'Đã giải quyết' (resolved). Trạng thái 'Đã đóng hoàn tất' (closed) do Sinh viên nghiệm thu/đánh giá CSAT hoặc tự động sau 24h!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }
        if (in_array($newStatus, ['new', 'in_progress', 'waiting_requester', 'resolved', 'closed'])) {
            TicketRepository::updateStatus($id, $newStatus);
            set_flash('success', "Đã cập nhật trạng thái phiếu sang '{$newStatus}'!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }
    }
}

// -----------------------------------------------------------------------------
// 5. XỬ LÝ ĐIỀU PHỐI / PHÂN CÔNG KTV PHỤ TRÁCH (ADMIN / TRIAGE)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_agent') {
    if (in_array($userRole, ['agent', 'admin'])) {
        $newAgentId = (int)($_POST['assigned_to'] ?? 0);
        if ($newAgentId > 0) {
            $agStmt = db()->prepare("SELECT name, user_code FROM users WHERE id = :id AND role = 'agent' LIMIT 1");
            $agStmt->execute(['id' => $newAgentId]);
            $newAgent = $agStmt->fetch();

            if ($newAgent) {
                db()->prepare("UPDATE tickets SET assigned_to = :aid WHERE id = :id")->execute(['aid' => $newAgentId, 'id' => $id]);
                TicketRepository::addReply($id, (int)$currentUser['id'], "Quản trị viên đã phân công phiếu sự cố cho KTV: {$newAgent['name']} ({$newAgent['user_code']}).", true);
                set_flash('success', "Đã phân công thành công cho KTV: <strong>{$newAgent['name']}</strong>!");
                header("Location: show.php?id={$id}&view_as={$viewAs}");
                exit;
            }
        }
    }
}

// -----------------------------------------------------------------------------
// 5.5. XỬ LÝ BÁO CÁO SPAM & KHÓA VĨNH VIỄN TÀI KHOẢN (KTV & ADMIN - PAGE 8)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'report_spam') {
    if (in_array($userRole, ['agent', 'admin'])) {
        $reason = trim($_POST['spam_reason'] ?? 'Gửi tin rác / Phá hoại trạm cứu hộ');
        $lockPermanent = isset($_POST['lock_permanent']) && $_POST['lock_permanent'] === '1';

        $pdo = db();
        $pdo->prepare("UPDATE tickets SET is_spam = 1, spam_reason = :r WHERE id = :id")->execute(['r' => $reason, 'id' => $id]);
        TicketRepository::addReply($id, (int)$currentUser['id'], "🚨 [BÁO CÁO SPAM / NGHỊCH TRẠM CỨU HỘ]: {$reason}", true);

        if ($lockPermanent || $userRole === 'admin') {
            $pdo->prepare("UPDATE users SET is_active = 0, lock_reason = 'banned_rescue_abuse' WHERE id = :uid")
                ->execute(['uid' => $ticket['requester_id']]);
            set_flash('success', "Đã ghi nhận báo cáo spam và KHÓA VĨNH VIỄN tài khoản {$ticket['requester_name']} ({$ticket['requester_code']}) - Chặn trạm cứu hộ!");
        } else {
            set_flash('success', "Đã gửi báo cáo sinh viên spam lên Quản trị viên (Admin) xem xét khóa tài khoản!");
        }

        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// 5.6. XỬ LÝ NGHIỆM THU XONG / HOÀN TẤT KỸ THUẬT (PAGE 8)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_resolve') {
    if (in_array($userRole, ['agent', 'admin'])) {
        TicketRepository::updateStatus($id, 'resolved');
        TicketRepository::addReply($id, (int)$currentUser['id'], "✅ [BIÊN BẢN NGHIỆM THU KỸ THUẬT]: Kỹ thuật viên đã xử lý và nghiệm thu giải pháp hoàn tất. Kính mời người dùng kiểm tra lại và gửi đánh giá CSAT.", false, 'resolved');
        set_flash('success', "Đã nghiệm thu xong giải pháp kỹ thuật! Phiếu đã chuyển sang trạng thái ĐÃ CÓ GIẢI PHÁP (resolved).");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// Lấy danh sách phản hồi
$replies = TicketRepository::getReplies($id, $canViewInternal);
$agents  = TicketRepository::getUsers('agent');
$flash   = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu <?php echo htmlspecialchars($ticket['ticket_code']); ?> - Hệ Thống Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- HEADER PHIẾU (IMAGE 1 BANNER DESIGN) -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <!-- Mã định danh vé -->
                    <span class="font-mono font-bold text-xs text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-200/60">
                        <?php echo htmlspecialchars($ticket['ticket_code']); ?>
                    </span>
                    <!-- Danh mục -->
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/60">
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
                    $dotColors = [
                        'new'               => 'bg-blue-500',
                        'in_progress'       => 'bg-amber-500',
                        'waiting_requester' => 'bg-purple-500',
                        'resolved'          => 'bg-emerald-500',
                        'closed'            => 'bg-slate-500',
                    ];
                    $statusLabels = [
                        'new'               => 'Mới tiếp nhận',
                        'in_progress'       => 'Đang xử lý',
                        'waiting_requester' => 'Chờ người gửi phản hồi',
                        'resolved'          => 'Đã có giải pháp',
                        'closed'            => 'Đã hoàn tất & đóng',
                    ];
                    $sColor = $statusColors[$ticket['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                    $dColor = $dotColors[$ticket['status']] ?? 'bg-slate-400';
                    $sLabel = $statusLabels[$ticket['status']] ?? $ticket['status'];
                    ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $sColor; ?> flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $dColor; ?>"></span>
                        <span><?php echo $sLabel; ?></span>
                    </span>
                    <?php if ($ticket['reopen_count'] > 0): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            🔄 Mở lại: <?php echo $ticket['reopen_count']; ?> lần
                        </span>
                    <?php endif; ?>
                </div>
                
                <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">
                    <?php echo htmlspecialchars($ticket['title']); ?>
                </h1>
                
                <p class="text-xs text-slate-400 font-medium mt-1">
                    Ngày gửi: <?php echo htmlspecialchars($ticket['created_at']); ?> · Ứng dụng: <strong class="text-slate-700 font-semibold"><?php echo htmlspecialchars($ticket['system_affected']); ?></strong>
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <?php if (in_array($userRole, ['agent', 'admin']) && !in_array($ticket['status'], ['resolved', 'closed'])): ?>
                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Xác nhận nghiệm thu xong giải pháp kỹ thuật cho phiếu này?');">
                        <input type="hidden" name="action" value="quick_resolve" />
                        <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-full shadow-sm transition flex items-center gap-1">
                            <span>✅</span> Nghiệm Thu Xong
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (in_array($userRole, ['agent', 'admin'])): ?>
                    <button type="button" onclick="document.getElementById('reportSpamModal').classList.remove('hidden')" 
                            class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-full border border-rose-200 transition flex items-center gap-1">
                        <span>🚨</span> Báo Cáo SV Spam
                    </button>
                    <a href="edit.php?id=<?php echo $ticket['id']; ?>" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full border border-indigo-200 transition">
                        ✏️ Sửa phiếu
                    </a>
                <?php endif; ?>
                <a href="index.php" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-full transition flex items-center gap-1">
                    ← Danh sách phiếu
                </a>
            </div>
        </div>
    </div>

    <!-- BANNER CẢNH BÁO SPAM NẾU CÓ (PAGE 8) -->
    <?php if (!empty($ticket['is_spam'])): ?>
        <div class="p-4 bg-rose-50 border-2 border-rose-300 rounded-3xl flex items-center justify-between flex-wrap gap-3 text-xs text-rose-900 shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🚨</span>
                <div>
                    <strong class="text-rose-950 text-sm block">PHIẾU ĐÃ BỊ BÁO CÁO SPAM / TIN RÁC / NGHỊCH TRẠM CỨU HỘ</strong>
                    <span class="text-rose-700">Lý do: <?php echo htmlspecialchars($ticket['spam_reason'] ?? 'Gửi tin rác không đúng mục đích'); ?></span>
                </div>
            </div>
            <?php if ($userRole === 'admin'): ?>
                <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn KHÓA VĨNH VIỄN tài khoản này và chặn vĩnh viễn trạm cứu hộ?');">
                    <input type="hidden" name="action" value="report_spam" />
                    <input type="hidden" name="lock_permanent" value="1" />
                    <input type="hidden" name="spam_reason" value="Admin xử lý báo cáo: Khóa vĩnh viễn do spam/nghịch trạm cứu hộ" />
                    <button type="submit" class="px-4 py-2 bg-rose-700 hover:bg-rose-800 text-white font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5">
                        <span>🔒</span> Khóa Vĩnh Viễn Tài Khoản Này
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- FLASH MESSAGE -->
    <?php if ($flash): ?>
        <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : ($flash['type'] === 'info' ? 'bg-blue-50 border border-blue-200 text-blue-800' : 'bg-rose-50 border border-rose-200 text-rose-800'); ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <!-- NỘI DUNG CHÍNH (2 CỘT) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- CỘT TRÁI: MÔ TẢ, CSAT, TIMELINE & FORM (8 CỘT) -->
        <div class="md:col-span-8 space-y-6">
            
            <!-- Mô tả sự cố ban đầu -->
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📝</span> Mô Tả Sự Cố Ban Đầu:
                    </h3>
                    <span class="text-[11px] text-slate-400 font-mono">Bởi: <?php echo htmlspecialchars($ticket['requester_name']); ?></span>
                </div>
                <div class="p-4 bg-slate-50 rounded-2xl text-xs text-slate-800 leading-relaxed whitespace-pre-line border border-slate-200">
                    <?php echo htmlspecialchars($ticket['description']); ?>
                </div>

                <!-- HIỂN THỊ FILE MINH CHỨNG SỰ CỐ BAN ĐẦU CỦA SINH VIÊN / GIẢNG VIÊN -->
                <?php if (!empty($ticket['attachment_path'])): ?>
                    <?php
                    $initExt = strtolower(pathinfo($ticket['attachment_path'], PATHINFO_EXTENSION));
                    $isImg = in_array($initExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                    ?>
                    <div class="mt-3 p-4 bg-indigo-50/60 rounded-2xl border border-indigo-200 space-y-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <span class="text-xs font-bold text-indigo-950 flex items-center gap-1.5">
                                <span>📎</span> Tệp / Ảnh Minh Chứng Sự Cố (Do người gửi đính kèm):
                            </span>
                            <a href="../<?php echo htmlspecialchars($ticket['attachment_path']); ?>" target="_blank" 
                               class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                                <span>🔍</span> Xem kích thước gốc ↗
                            </a>
                        </div>
                        <?php if ($isImg): ?>
                            <div class="pt-1">
                                <a href="../<?php echo htmlspecialchars($ticket['attachment_path']); ?>" target="_blank" class="inline-block group">
                                    <img src="../<?php echo htmlspecialchars($ticket['attachment_path']); ?>" 
                                         alt="Minh chứng sự cố" 
                                         class="max-h-72 max-w-full rounded-2xl border border-slate-300 shadow-sm group-hover:opacity-95 transition object-contain bg-white p-1.5" />
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-3 text-xs font-semibold text-slate-700 bg-white p-3 rounded-xl border border-slate-200">
                                <span class="text-2xl">📄</span>
                                <div>
                                    <div class="font-bold text-slate-800"><?php echo basename($ticket['attachment_path']); ?></div>
                                    <a href="../<?php echo htmlspecialchars($ticket['attachment_path']); ?>" download class="text-indigo-600 text-[11px] hover:underline font-bold">
                                        ⬇️ Tải tệp tài liệu minh chứng về máy
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- KHỐI NGHIỆM THU & ĐÁNH GIÁ CSAT (IMAGE 2 DESIGN & CHỈ RIÊNG NGƯỜI TẠO PHIẾU THẤY) -->
            <?php 
            $isRequester = ((int)$currentUser['id'] === (int)$ticket['requester_id']);
            ?>
            <?php if (in_array($ticket['status'], ['resolved', 'closed']) && $isRequester): ?>
                <div id="csat-box" class="bg-amber-50/40 border border-amber-200/90 rounded-3xl p-6 shadow-sm space-y-4">
                    
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-amber-500 text-xl font-bold">🌟</span>
                            <h3 class="text-xs sm:text-sm font-black text-amber-950 uppercase tracking-wide">
                                NGHIỆM THU KẾT QUẢ XỬ LÝ & ĐÁNH GIÁ CSAT (1 - 5 SAO)
                            </h3>
                        </div>
                        <?php if ($currentRating): ?>
                            <button type="button" onclick="document.getElementById('editRatingContainer').classList.toggle('hidden')" 
                                    class="px-3.5 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-950 border border-amber-300 rounded-xl font-bold transition flex items-center gap-1.5 text-xs shadow-2xs">
                                <span>✏️</span> Thay Đổi / Chấm Lại Đánh Giá
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($currentRating): ?>
                        <div class="p-4 bg-white/95 border border-amber-200 rounded-2xl flex items-center justify-between flex-wrap gap-2 text-xs">
                            <div>
                                <span class="font-bold text-slate-800">Đánh giá của bạn:</span>
                                <span class="text-amber-500 font-bold ml-1.5">
                                    <?php echo str_repeat('★', (int)$currentRating['score']); ?>
                                    (<?php echo (int)$currentRating['score']; ?>/5 sao)
                                </span>
                                <?php if (!empty($currentRating['comment'])): ?>
                                    <p class="text-slate-600 mt-1 italic">"<?php echo htmlspecialchars($currentRating['comment']); ?>"</p>
                                <?php endif; ?>
                            </div>
                            <span class="text-[11px] text-slate-400">Đã nghiệm thu lúc <?php echo htmlspecialchars($currentRating['created_at']); ?></span>
                        </div>

                        <!-- KHUNG THAY ĐỔI ĐÁNH GIÁ (MỞ KHI BẤM NÚT SỬA) -->
                        <div id="editRatingContainer" class="hidden p-4 bg-white/95 border border-amber-200 rounded-2xl space-y-3">
                            <div class="font-bold text-amber-950 text-xs flex items-center gap-1.5">
                                <span>✏️</span> Cập nhật lại số sao và lời góp ý dành cho KTV:
                            </div>
                            <form id="csatEditForm" method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-4 text-xs" onsubmit="return handleCsatSubmit(this, event);">
                                <input type="hidden" name="action" value="rate_ticket" />
                                
                                <div>
                                    <label class="block font-semibold text-slate-800 mb-2.5">
                                        Chọn lại mức độ hài lòng với hỗ trợ kỹ thuật:
                                    </label>
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                        <?php 
                                        $scoreOptions = [
                                            5 => '5 ★ Rất hài lòng',
                                            4 => '4 ★ Hài lòng',
                                            3 => '3 ★ Bình thường',
                                            2 => '2 ★ Chưa hài lòng',
                                            1 => '1 ★ Rất thất vọng'
                                        ];
                                        foreach ($scoreOptions as $sVal => $sLabel):
                                            $isChecked = ((int)$currentRating['score'] === $sVal) ? 'checked' : '';
                                        ?>
                                            <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-amber-400 rounded-full cursor-pointer transition shadow-2xs">
                                                <input type="radio" name="score" value="<?php echo $sVal; ?>" <?php echo $isChecked; ?> class="text-amber-500 focus:ring-amber-400 cursor-pointer" />
                                                <span class="font-semibold text-slate-700 text-xs"><?php echo $sLabel; ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div>
                                    <textarea name="comment" rows="2" placeholder="Góp ý hoặc nhận xét của bạn về thái độ và tốc độ hỗ trợ của KTV (tùy chọn)..." 
                                              class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-800 text-xs"><?php echo htmlspecialchars($currentRating['comment'] ?? ''); ?></textarea>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl transition shadow-md shadow-amber-200 flex items-center gap-1.5 text-xs">
                                        <span>⭐</span> Lưu Cập Nhật Đánh Giá CSAT
                                    </button>
                                    <button type="button" onclick="document.getElementById('editRatingContainer').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-2xl transition text-xs">
                                        Đóng lại
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- FORM ĐÁNH GIÁ LẦN ĐẦU -->
                        <form id="csatNewForm" method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-4 text-xs" onsubmit="return handleCsatSubmit(this, event);">
                            <input type="hidden" name="action" value="rate_ticket" />
                            
                            <div>
                                <label class="block font-semibold text-slate-800 mb-2.5">
                                    Mức độ hài lòng với hỗ trợ kỹ thuật:
                                </label>
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-amber-400 rounded-full cursor-pointer transition shadow-2xs">
                                        <input type="radio" name="score" value="5" checked class="text-amber-500 focus:ring-amber-400 cursor-pointer" />
                                        <span class="font-bold text-slate-800 text-xs">5 ★ Rất hài lòng</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-amber-400 rounded-full cursor-pointer transition shadow-2xs">
                                        <input type="radio" name="score" value="4" class="text-amber-500 focus:ring-amber-400 cursor-pointer" />
                                        <span class="font-semibold text-slate-700 text-xs">4 ★ Hài lòng</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-amber-400 rounded-full cursor-pointer transition shadow-2xs">
                                        <input type="radio" name="score" value="3" class="text-amber-500 focus:ring-amber-400 cursor-pointer" />
                                        <span class="font-semibold text-slate-700 text-xs">3 ★ Bình thường</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-amber-400 rounded-full cursor-pointer transition shadow-2xs">
                                        <input type="radio" name="score" value="2" class="text-amber-500 focus:ring-amber-400 cursor-pointer" />
                                        <span class="font-semibold text-slate-700 text-xs">2 ★ Chưa hài lòng</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-amber-400 rounded-full cursor-pointer transition shadow-2xs">
                                        <input type="radio" name="score" value="1" class="text-amber-500 focus:ring-amber-400 cursor-pointer" />
                                        <span class="font-semibold text-slate-700 text-xs">1 ★ Rất thất vọng</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <textarea name="comment" rows="2" placeholder="Góp ý hoặc nhận xét của bạn về thái độ và tốc độ hỗ trợ của KTV (tùy chọn)..." 
                                          class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-800 text-xs"></textarea>
                            </div>

                            <div class="flex items-center justify-start">
                                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl transition shadow-md shadow-amber-200 flex items-center gap-1.5 text-xs">
                                    <span>⭐</span> Xác Nhận Đã Xong & Gửi Đánh Giá CSAT
                                </button>
                                <?php if ($ticket['status'] === 'resolved'): ?>
                                    <button type="button" onclick="document.getElementById('reopenModal').classList.remove('hidden')" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-2xl text-xs transition border border-rose-200 flex items-center gap-1.5 ml-2">
                                        <span>🔄</span> Chưa xong? (Yêu cầu KTV hỗ trợ tiếp)
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

            <!-- Khung Trao Đổi (Ticket Replies) -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span>💬</span> Lịch Sử Phản Hồi & Giải Pháp (<?php echo count($replies); ?> lượt):
                    </h3>
                    <span class="text-[10px] text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg font-mono font-bold">
                        Bảng ticket_replies
                    </span>
                </div>

                <?php if (empty($replies)): ?>
                    <div class="text-center py-8 text-slate-400 text-xs italic bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        Chưa có phản hồi nào cho phiếu này. Hãy nhập nội dung bên dưới để bắt đầu trao đổi!
                    </div>
                <?php else: ?>
                    <div class="space-y-3.5">
                        <?php foreach ($replies as $r): ?>
                            <?php 
                            $isAgent = ($r['user_role'] === 'agent' || $r['user_role'] === 'admin');
                            ?>
                            <div class="p-4 rounded-2xl border text-xs space-y-2 transition <?php echo $r['is_internal'] ? 'bg-amber-50/80 border-amber-300 shadow-sm' : ($isAgent ? 'bg-indigo-50/40 border-indigo-200' : 'bg-slate-50 border-slate-200'); ?>">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white <?php echo $r['is_internal'] ? 'bg-amber-600' : ($isAgent ? 'bg-indigo-600' : 'bg-emerald-600'); ?>">
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
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 border border-amber-300 flex items-center gap-1">
                                                <span>🔒</span> Ghi chú nội bộ KTV
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700">
                                                💬 Công khai
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-slate-400 text-[10px] font-mono"><?php echo htmlspecialchars($r['created_at']); ?></span>
                                    </div>
                                </div>
                                <div class="text-slate-700 leading-relaxed pl-9 whitespace-pre-line">
                                    <?php echo htmlspecialchars($r['message']); ?>
                                </div>

                                <!-- HIỂN THỊ TỆP ĐÍNH KÈM (PAGE 6: HỖ TRỢ TỐI ĐA 10 FILE) -->
                                <?php if (!empty($r['attachment_path'])): ?>
                                    <?php
                                    $rFiles = [];
                                    $decoded = json_decode($r['attachment_path'], true);
                                    if (is_array($decoded)) {
                                        $rFiles = $decoded;
                                    } else {
                                        $rFiles = [$r['attachment_path']];
                                    }
                                    ?>
                                    <div class="mt-2.5 pt-2 border-t border-slate-200/60 pl-9 space-y-2">
                                        <div class="flex items-center gap-1 text-[11px] font-bold text-indigo-700">
                                            <span>📎</span> Tệp minh chứng đính kèm (<?php echo count($rFiles); ?> tệp):
                                        </div>
                                        <div class="flex flex-wrap gap-2.5">
                                            <?php foreach ($rFiles as $fPath): ?>
                                                <?php 
                                                $fExt = strtolower(pathinfo($fPath, PATHINFO_EXTENSION));
                                                $isImg = in_array($fExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                                ?>
                                                <?php if ($isImg): ?>
                                                    <a href="../<?php echo htmlspecialchars($fPath); ?>" target="_blank" class="inline-block group">
                                                        <img src="../<?php echo htmlspecialchars($fPath); ?>" alt="Minh chứng" 
                                                             class="h-28 w-36 object-cover rounded-xl border border-slate-300 shadow-sm group-hover:opacity-90 transition bg-white p-1" />
                                                    </a>
                                                <?php else: ?>
                                                    <a href="../<?php echo htmlspecialchars($fPath); ?>" target="_blank" download 
                                                       class="p-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-semibold text-indigo-700 border border-slate-300 flex items-center gap-1.5 transition">
                                                        <span>📄</span> <?php echo basename($fPath); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Form gửi phản hồi mới -->
                <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" enctype="multipart/form-data" class="pt-5 border-t border-slate-100 space-y-4 text-xs">
                    <input type="hidden" name="action" value="reply" />

                    <?php if (in_array($userRole, ['agent', 'admin'])): ?>
                        <!-- Dành cho Kỹ thuật viên / Quản trị viên: Có đầy đủ công cụ chuyển trạng thái & ghi chú nội bộ -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Người gửi phản hồi:</label>
                                <select name="user_id" id="senderSelect" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <optgroup label="Kỹ thuật viên IT:">
                                        <?php foreach ($agents as $ag): ?>
                                            <option value="<?php echo $ag['id']; ?>" <?php echo ($currentUser['id'] == $ag['id'] || ($ticket['assigned_to'] == $ag['id'])) ? 'selected' : ''; ?>>
                                                KTV: <?php echo htmlspecialchars($ag['name']); ?> (<?php echo htmlspecialchars($ag['user_code']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Hành động trạng thái sau phản hồi:</label>
                                <select name="quick_status" id="quickStatusSelect" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">⚡ Tự động thông minh theo State Machine</option>
                                    <option value="resolved">✅ Chuyển sang: Đã có giải pháp (resolved)</option>
                                    <?php if ($userRole === 'admin'): ?>
                                        <option value="closed">🏁 Chuyển sang: Đã hoàn tất & Đóng phiếu (closed - Admin)</option>
                                    <?php else: ?>
                                        <option value="" disabled class="text-slate-400">🏁 Đã hoàn tất (Chỉ SV đánh giá hoặc sau 24h)</option>
                                    <?php endif; ?>
                                    <option value="waiting_requester">⏳ Chuyển sang: Chờ người dùng kiểm tra (waiting)</option>
                                    <option value="in_progress">⚙️ Chuyển sang: Tiếp tục xử lý (in_progress)</option>
                                </select>
                                <p class="text-[11px] text-indigo-600 mt-1 font-medium flex items-center gap-1">
                                    <span>💡</span> <span>Chọn trạng thái tại đây và bấm <strong>Lưu Phản Hồi</strong> để cập nhật ngay vào CSDL.</span>
                                </p>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Dành cho Sinh viên / Giảng viên: Giao diện gửi tinh gọn -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">Người gửi: <strong class="text-indigo-600"><?php echo htmlspecialchars($currentUser['name']); ?></strong> (<?php echo htmlspecialchars($currentUser['user_code']); ?>)</span>
                            <span class="text-slate-400 text-[11px]">Phản hồi sẽ tự động kích hoạt vé để KTV xử lý</span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nội dung trao đổi / Giải pháp kỹ thuật:</label>
                        <textarea name="message" id="replyMessage" rows="3" placeholder="Nhập nội dung trao đổi, kết quả xử lý hoặc giải pháp kỹ thuật (Nếu để trống khi chọn trạng thái mới, hệ thống sẽ tự sinh thông báo cập nhật chuẩn)..." 
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs"></textarea>
                    </div>

                    <!-- ĐÍNH KÈM TỆP ẢNH (PAGE 6: TỐI ĐA 10 FILE) -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-slate-700 text-xs">📎 Đính kèm tệp minh chứng (Tối đa 10 tệp cùng lúc):</label>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">Tối đa 10 tệp</span>
                        </div>
                        <input type="file" name="attachments[]" id="attachmentInput" multiple accept="image/png,image/jpeg,image/webp,image/gif,application/pdf" 
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-300 rounded-xl bg-white p-1" />
                        <span class="text-[10.5px] text-slate-400 block">Hỗ trợ: JPG, PNG, WEBP, GIF, PDF (Tối đa 10MB/tệp. Có thể giữ Ctrl/Shift để chọn nhiều tệp).</span>
                        
                        <!-- Xem trước thư viện ảnh đính kèm -->
                        <div id="imagePreviewContainer" class="hidden pt-2">
                            <p class="text-[11px] font-bold text-slate-600 mb-1.5">Xem trước danh sách tệp đính kèm (<span id="previewCount">0</span>/10):</p>
                            <div id="previewGallery" class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-1 bg-white rounded-xl border border-slate-200"></div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                        <?php if (in_array($userRole, ['agent', 'admin'])): ?>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1.5 cursor-pointer text-slate-700 font-semibold" id="internalBox">
                                    <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" />
                                    <span class="text-amber-800">🔒 Đặt làm Ghi chú nội bộ KTV (Người dùng không thấy)</span>
                                </label>
                            </div>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <button type="submit" id="btnSubmitReply" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5">
                            <span id="btnIcon">💾</span> <span id="btnText">Lưu Phản Hồi & Cập Nhật Trạng Thái</span>
                        </button>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-2xl text-[11px] text-slate-500 leading-relaxed border border-slate-200">
                        ⚡ <strong>Quy tắc tự động State Machine:</strong> Khi KTV gửi tin nhắn công khai, phiếu tự chuyển sang <em>waiting_requester</em>. Khi Sinh viên/Giảng viên gửi phản hồi lại, phiếu tự quay về <em>in_progress</em>.
                    </div>
                </form>
            </div>

        </div>

        <!-- CỘT PHẢI: THÔNG TIN TRẠNG THÁI & SLA (4 CỘT) -->
        <div class="md:col-span-4 space-y-6">

            <?php if (in_array($userRole, ['agent', 'admin'])): ?>
            <!-- Cập nhật trạng thái thủ công (Chỉ KTV / Admin) -->
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Cập Nhật Trạng Thái Phiếu</h3>
                
                <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>" class="space-y-3">
                    <input type="hidden" name="action" value="update_status" />
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="new" <?php echo $ticket['status'] === 'new' ? 'selected' : ''; ?>>1. Mới tiếp nhận (new)</option>
                        <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>2. Đang xử lý (in_progress)</option>
                        <option value="waiting_requester" <?php echo $ticket['status'] === 'waiting_requester' ? 'selected' : ''; ?>>3. Chờ phản hồi (waiting)</option>
                        <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>4. Đã giải quyết (resolved)</option>
                        <?php if ($userRole === 'admin'): ?>
                          <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>5. Đã đóng hoàn tất (closed - Admin)</option>
                      <?php else: ?>
                          <option value="" disabled class="text-slate-400">5. Đóng hoàn tất (Chỉ SV đánh giá hoặc sau 24h)</option>
                      <?php endif; ?>
                    </select>
                    <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                        Cập nhật trạng thái ngay
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Thông tin người liên quan -->
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Thông Tin Sự Cố</h3>
                
                <div class="space-y-1.5 border-b border-slate-100 pb-3">
                    <span class="text-slate-400 block text-[11px]">Người gửi yêu cầu:</span>
                    <strong class="text-slate-900 block"><?php echo htmlspecialchars($ticket['requester_name']); ?></strong>
                    <span class="text-slate-500 font-mono block">Mã: <?php echo htmlspecialchars($ticket['requester_code']); ?></span>
                    <span class="text-indigo-700 font-bold block text-[11.5px]">Lớp: <?php echo htmlspecialchars($ticket['class_code'] ?? $ticket['class_name'] ?? 'D24-CNTT1'); ?></span>
                    <span class="text-slate-500 block"><?php echo htmlspecialchars($ticket['requester_email']); ?></span>
                </div>

                <div class="space-y-1.5 border-b border-slate-100 pb-3">
                    <span class="text-slate-400 block text-[11px]">Kỹ thuật viên phụ trách:</span>
                    <?php if ($ticket['agent_name']): ?>
                        <strong class="text-indigo-700 block"><?php echo htmlspecialchars($ticket['agent_name']); ?></strong>
                        <span class="text-slate-500 font-mono block">Mã KTV: <?php echo htmlspecialchars($ticket['agent_code']); ?></span>
                    <?php else: ?>
                        <span class="text-amber-600 font-bold italic block">⚠️ Đang chờ phân công...</span>
                    <?php endif; ?>

                    <?php if ($userRole === 'admin'): ?>
                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="mt-2.5 pt-2 border-t border-slate-100 space-y-2">
                            <input type="hidden" name="action" value="assign_agent" />
                            <label class="block text-[11px] font-bold text-slate-700">🎯 Phân công / Điều chuyển KTV:</label>
                            <select name="assigned_to" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Chọn KTV tiếp nhận --</option>
                                <?php foreach ($agents as $ag): ?>
                                    <option value="<?php echo $ag['id']; ?>" <?php echo ((int)$ticket['assigned_to'] === (int)$ag['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ag['name']); ?> (<?php echo htmlspecialchars($ag['user_code']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-[11px] shadow-xs flex items-center justify-center gap-1">
                                <span>💾</span> Lưu Phân Công
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="space-y-1">
                    <span class="text-slate-400 block text-[11px]">Mức độ ưu tiên (Auto-Priority):</span>
                    <?php 
                    $prioBadgeClass = match($ticket['priority_code']) {
                        'URGENT' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'HIGH'   => 'bg-amber-50 text-amber-800 border-amber-200',
                        'MED'    => 'bg-blue-50 text-blue-700 border-blue-200',
                        'LOW'    => 'bg-slate-50 text-slate-700 border-slate-200',
                        default  => 'bg-slate-50 text-slate-700 border-slate-200',
                    };
                    ?>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border <?php echo $prioBadgeClass; ?>">
                        <span>●</span>
                        <span><?php echo htmlspecialchars($ticket['priority_name']); ?></span>
                        <span class="text-[10px] opacity-80">(<?php echo htmlspecialchars($ticket['priority_code']); ?>)</span>
                    </div>
                </div>
            </div>

            <!-- Thông số SLA (Thời gian) -->
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2.5 text-xs">
                <h3 class="font-bold text-slate-700 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                    <span>⏱️</span> Tiến Độ SLA Cam Kết
                </h3>
                
                <div class="p-2.5 bg-slate-50 rounded-xl space-y-1 border border-slate-200">
                    <span class="text-slate-500 text-[10.5px] block font-bold">Thời gian tiếp nhận của KTV:</span>
                    <?php if (!empty($ticket['first_response_at'])): ?>
                        <strong class="font-mono text-emerald-700 block"><?php echo htmlspecialchars($ticket['first_response_at']); ?></strong>
                        <span class="text-[10px] text-emerald-600 block font-semibold">✓ Đã tiếp nhận & phản hồi</span>
                    <?php elseif (!empty($ticket['assigned_at'])): ?>
                        <strong class="font-mono text-indigo-700 block"><?php echo htmlspecialchars($ticket['assigned_at']); ?></strong>
                        <span class="text-[10px] text-indigo-600 block font-semibold">✓ Đã tiếp nhận phân công</span>
                    <?php else: ?>
                        <strong class="font-mono text-amber-700 block"><?php echo htmlspecialchars($ticket['triage_due_at']); ?></strong>
                        <span class="text-[10px] text-amber-600 block">⏳ Hạn tiếp nhận cam kết (Chờ KTV)</span>
                    <?php endif; ?>
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

</main>

<!-- MODAL YÊU CẦU MỞ LẠI PHIẾU (CHO SINH VIÊN KHI SỰ CỐ CHƯA HẾT) -->
<div id="reopenModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                <span>🔄</span> Yêu Cầu Kỹ Thuật Viên Tiếp Tục Hỗ Trợ
            </h3>
            <button type="button" onclick="document.getElementById('reopenModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed">
            Nếu sự cố của bạn vẫn chưa được khắc phục hoặc tài khoản vẫn chưa đăng nhập được, vui lòng nhập lý do cụ thể để Kỹ thuật viên kiểm tra lại.
        </p>
        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-3 text-xs">
            <input type="hidden" name="action" value="reopen_ticket" />
            <textarea name="reopen_note" rows="3" required placeholder="Ví dụ: Em đã thử đăng nhập nhưng hệ thống vẫn báo sai mật khẩu..." 
                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('reopenModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-sm">
                    Gửi Yêu Cầu Mở Lại
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL XÁC NHẬN ĐÁNH GIÁ CSAT (HỎI CHẮC CHẮN TRÁNH BẤM NHẦM SAO) -->
<div id="confirmRatingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-amber-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="text-amber-500 text-lg">🌟</span> Xác Nhận Lưu Đánh Giá CSAT
            </h3>
            <button type="button" onclick="closeConfirmRatingModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none">&times;</button>
        </div>
        
        <div class="space-y-3">
            <p class="text-xs text-slate-700 font-medium leading-relaxed">
                Bạn có chắc chắn muốn lưu mức đánh giá này cho Kỹ thuật viên không?
            </p>

            <div class="p-3.5 bg-amber-50/70 border border-amber-200/80 rounded-2xl space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-600 font-semibold">Điểm đánh giá đã chọn:</span>
                    <span id="modalScoreDisplay" class="font-black text-amber-600 text-sm tracking-wide">★★★★★ (5/5 sao)</span>
                </div>
                <div class="text-[11.5px] text-slate-600 flex items-center justify-between">
                    <span class="text-slate-500">Mức độ hài lòng:</span>
                    <span id="modalScoreDesc" class="font-bold text-slate-800">5 ★ Rất hài lòng</span>
                </div>
                <div id="modalCommentRow" class="text-[11px] text-slate-600 border-t border-amber-200/50 pt-1.5 mt-1 hidden">
                    <span class="text-slate-500 font-medium block mb-0.5">Nội dung góp ý / nhận xét:</span>
                    <p id="modalCommentText" class="italic text-slate-800 bg-white/80 p-2 rounded-xl border border-amber-100"></p>
                </div>
            </div>

            <div class="p-2.5 bg-amber-100/50 rounded-xl border border-amber-200/60 text-[11px] text-amber-900 flex items-start gap-1.5">
                <span class="text-amber-600 text-sm leading-none">⚠️</span>
                <span>Vui lòng kiểm tra kỹ số sao để tránh bấm nhầm. Sau khi xác nhận, điểm đánh giá sẽ được ghi nhận vào hồ sơ hỗ trợ của KTV.</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-2 text-xs">
            <button type="button" onclick="closeConfirmRatingModal()" class="px-4 py-2.5 border border-slate-300 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition">
                ❌ Hủy bỏ (Chọn lại)
            </button>
            <button type="button" id="btnConfirmRatingSubmit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md shadow-amber-200 transition flex items-center gap-1.5">
                <span>✅</span> Đồng Ý & Lưu Đánh Giá
            </button>
        </div>
    </div>
</div>

<!-- MODAL BÁO CÁO SPAM / VI PHẠM (PAGE 8) -->
<div id="reportSpamModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-rose-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-rose-900 flex items-center gap-2">
                <span class="text-rose-600 text-lg">🚨</span> Báo Cáo Sinh Viên Spam / Phá Hoại
            </h3>
            <button type="button" onclick="document.getElementById('reportSpamModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="report_spam" />

            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 leading-relaxed text-[11.5px]">
                Bạn đang báo cáo tài khoản: <strong><?php echo htmlspecialchars($ticket['requester_name']); ?></strong> (Mã: <code><?php echo htmlspecialchars($ticket['requester_code']); ?></code>).
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Chọn lý do vi phạm: <span class="text-rose-500">*</span></label>
                <select name="spam_reason" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 font-semibold text-slate-800">
                    <option value="Gửi tin nhắn spam, tin rác nhiều lần">1. Gửi tin nhắn spam, tin rác nhiều lần</option>
                    <option value="Nghịch trạm cứu hộ, điền thông tin/minh chứng sai sự thật">2. Nghịch trạm cứu hộ, điền thông tin/minh chứng sai sự thật</option>
                    <option value="Gửi phiếu quấy rối, ngôn từ phản cảm">3. Gửi phiếu quấy rối, ngôn từ phản cảm</option>
                    <option value="Lợi dụng hệ thống hỗ trợ để quấy phá">4. Lợi dụng hệ thống hỗ trợ để quấy phá</option>
                </select>
            </div>

            <?php if ($userRole === 'admin'): ?>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-rose-800 text-xs">
                        <input type="checkbox" name="lock_permanent" value="1" checked class="rounded border-slate-300 text-rose-600 focus:ring-rose-500" />
                        <span>🔒 Khóa vĩnh viễn tài khoản & chặn truy cập Trạm cứu hộ ngay</span>
                    </label>
                </div>
            <?php else: ?>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    💡 Báo cáo sẽ được chuyển thẳng đến Quản trị viên (Admin) để khóa vĩnh viễn tài khoản sinh viên này và chặn trạm cứu hộ.
                </p>
            <?php endif; ?>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('reportSpamModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <span>🚨</span> Xác Nhận Báo Cáo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRatingFormToSubmit = null;

const scoreMap = {
    '5': { stars: '★★★★★ (5/5 sao)', desc: '5 ★ Rất hài lòng / Xử lý tuyệt vời' },
    '4': { stars: '★★★★☆ (4/5 sao)', desc: '4 ★ Hài lòng / Đáp ứng yêu cầu' },
    '3': { stars: '★★★☆☆ (3/5 sao)', desc: '3 ★ Bình thường / Đạt mức chấp nhận' },
    '2': { stars: '★★☆☆☆ (2/5 sao)', desc: '2 ★ Chưa hài lòng / Cần cải thiện' },
    '1': { stars: '★☆☆☆☆ (1/5 sao)', desc: '1 ★ Rất thất vọng / Chưa xử lý được' }
};

function handleCsatSubmit(form, event) {
    if (form.dataset.confirmed === 'true') {
        return true;
    }
    if (event) {
        event.preventDefault();
    }
    currentRatingFormToSubmit = form;

    const selectedRadio = form.querySelector('input[name="score"]:checked');
    const scoreVal = selectedRadio ? selectedRadio.value : '5';
    const commentField = form.querySelector('textarea[name="comment"]');
    const commentVal = commentField ? commentField.value.trim() : '';

    const scoreInfo = scoreMap[scoreVal] || { stars: scoreVal + ' ★', desc: scoreVal + ' sao' };

    const displayElem = document.getElementById('modalScoreDisplay');
    const descElem = document.getElementById('modalScoreDesc');
    const commentRow = document.getElementById('modalCommentRow');
    const commentText = document.getElementById('modalCommentText');

    if (displayElem) displayElem.textContent = scoreInfo.stars;
    if (descElem) descElem.textContent = scoreInfo.desc;

    if (commentRow && commentText) {
        if (commentVal) {
            commentText.textContent = '"' + commentVal + '"';
            commentRow.classList.remove('hidden');
        } else {
            commentRow.classList.add('hidden');
        }
    }

    const modal = document.getElementById('confirmRatingModal');
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        if (confirm("Bạn có chắc chắn muốn lưu mức đánh giá " + scoreVal + " sao cho Kỹ thuật viên không?\n(Tránh bấm nhầm số sao)")) {
            form.dataset.confirmed = 'true';
            form.submit();
        }
    }
    return false;
}

function closeConfirmRatingModal() {
    const modal = document.getElementById('confirmRatingModal');
    if (modal) modal.classList.add('hidden');
    currentRatingFormToSubmit = null;
}

document.addEventListener('DOMContentLoaded', function() {
    const btnConfirm = document.getElementById('btnConfirmRatingSubmit');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function() {
            if (currentRatingFormToSubmit) {
                currentRatingFormToSubmit.dataset.confirmed = 'true';
                currentRatingFormToSubmit.submit();
            }
        });
    }

    // Đóng modal khi bấm phím ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmRatingModal();
        }
    });
    // 1. Xem trước danh sách tệp đính kèm (Tối đa 10 tệp - Page 6)
    const attachInput = document.getElementById('attachmentInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewGallery = document.getElementById('previewGallery');
    const previewCount = document.getElementById('previewCount');

    if (attachInput && previewContainer && previewGallery) {
        attachInput.addEventListener('change', function() {
            previewGallery.innerHTML = '';
            const files = Array.from(this.files).slice(0, 10);
            if (files.length === 0) {
                previewContainer.classList.add('hidden');
                return;
            }
            if (this.files.length > 10) {
                alert('Hệ thống chỉ cho phép tải tối đa 10 tệp minh chứng. Đã tự động giữ lại 10 tệp đầu tiên.');
            }
            if (previewCount) previewCount.textContent = files.length;
            previewContainer.classList.remove('hidden');

            files.forEach((file, idx) => {
                const item = document.createElement('div');
                item.className = 'relative group';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        item.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" class="h-20 w-24 object-cover rounded-xl border border-slate-300 shadow-2xs bg-white p-0.5" />
                            <span class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] px-1 rounded font-mono">#${idx+1}</span>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    item.innerHTML = `
                        <div class="h-20 w-28 rounded-xl bg-slate-100 border border-slate-300 p-2 flex flex-col justify-between text-[10px] text-slate-700">
                            <span class="text-base">📄</span>
                            <span class="truncate font-semibold">${file.name}</span>
                            <span class="text-[9px] text-slate-400 font-mono">${(file.size / 1024).toFixed(0)} KB</span>
                        </div>
                    `;
                }
                previewGallery.appendChild(item);
            });
        });
    }

    // 2. Nút bấm động theo trạng thái chọn
    const statusSelect = document.getElementById('quickStatusSelect');
    const btnSubmit = document.getElementById('btnSubmitReply');
    const btnIcon = document.getElementById('btnIcon');
    const btnText = document.getElementById('btnText');
    const messageArea = document.getElementById('replyMessage');

    if (statusSelect && btnSubmit) {
        statusSelect.addEventListener('change', function() {
            const val = this.value;
            if (val === 'resolved') {
                btnSubmit.className = 'px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-200 flex items-center justify-center gap-1.5 ring-2 ring-emerald-400';
                if (btnIcon) btnIcon.textContent = '✅';
                if (btnText) btnText.textContent = 'Lưu Phản Hồi & Chuyển Sang: ĐÃ CÓ GIẢI PHÁP (resolved)';
                if (messageArea && !messageArea.value.trim()) {
                    messageArea.placeholder = "Kỹ thuật viên đã xử lý và cung cấp giải pháp khắc phục sự cố (Nhấn nút Lưu để áp dụng ngay)...";
                }
            } else if (val === 'closed') {
                btnSubmit.className = 'px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition shadow-md shadow-slate-300 flex items-center justify-center gap-1.5 ring-2 ring-slate-400';
                if (btnIcon) btnIcon.textContent = '🏁';
                if (btnText) btnText.textContent = 'Lưu Phản Hồi & ĐÓNG HOÀN TẤT PHIẾU (closed)';
                if (messageArea && !messageArea.value.trim()) {
                    messageArea.placeholder = "Kỹ thuật viên đã hoàn tất hỗ trợ và đóng phiếu thành công...";
                }
            } else if (val === 'waiting_requester') {
                btnSubmit.className = 'px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition shadow-md shadow-purple-200 flex items-center justify-center gap-1.5';
                if (btnIcon) btnIcon.textContent = '⏳';
                if (btnText) btnText.textContent = 'Lưu Phản Hồi & Chuyển: CHỜ NGƯỜI DÙNG KIỂM TRA';
            } else if (val === 'in_progress') {
                btnSubmit.className = 'px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-md shadow-amber-200 flex items-center justify-center gap-1.5';
                if (btnIcon) btnIcon.textContent = '⚙️';
                if (btnText) btnText.textContent = 'Lưu Phản Hồi & TIẾP TỤC XỬ LÝ (in_progress)';
            } else {
                btnSubmit.className = 'px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5';
                if (btnIcon) btnIcon.textContent = '💾';
                if (btnText) btnText.textContent = 'Lưu Phản Hồi & Cập Nhật Trạng Thái';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

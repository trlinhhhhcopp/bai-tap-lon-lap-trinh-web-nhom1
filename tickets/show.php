<?php
// ==============================================================================
// BÀI 5: XEM CHI TIẾT PHIẾU HỖ TRỢ & HỆ THỐNG PHẢN HỒI HAI CHIỀU (SLIDE 7 & 10)
// THỰC THỂ: TICKETS & TICKET_REPLIES (MODEL TICKET & MODEL REPLY)
// Phân hệ: Core Architecture & Agent Workspace
// Trưởng nhóm: PHẠM THỊ THANH NGÂN (MSSV: 224001736 - D24-CNTT1)
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';
require_once __DIR__ . '/../app/Services/NotificationService.php';

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

$isLecturer = ($userType === 'lecturer');
$isCourseLecturer = false;
if ($isLecturer) {
    if (!empty($ticket['course_id'])) {
        $stmtChk = db()->prepare("SELECT COUNT(*) FROM course_assignments WHERE course_id = :cid AND lecturer_id = :lid");
        $stmtChk->execute([':cid' => $ticket['course_id'], ':lid' => $currentUser['id']]);
        if ((int)$stmtChk->fetchColumn() > 0) {
            $isCourseLecturer = true;
        }
    }
    if (!$isCourseLecturer && !empty($ticket['class_id'])) {
        $stmtChk2 = db()->prepare("SELECT COUNT(*) FROM course_assignments WHERE class_id = :cid AND lecturer_id = :lid");
        $stmtChk2->execute([':cid' => $ticket['class_id'], ':lid' => $currentUser['id']]);
        if ((int)$stmtChk2->fetchColumn() > 0) {
            $isCourseLecturer = true;
        }
    }
}

// Cách ly dữ liệu: Sinh viên chỉ xem phiếu của mình; Giảng viên xem phiếu của mình HOẶC môn mình phụ trách
if ($userRole === 'requester' && (int)$ticket['requester_id'] !== (int)$currentUser['id'] && !$isCourseLecturer) {
    set_flash('error', "Bạn không có quyền truy cập vào phiếu của tài khoản khác!");
    header('Location: ../index.php');
    exit;
}

// Cách ly nghiệp vụ KTV: KTV chỉ xem và xử lý phiếu được phân công cho mình (hoặc phiếu mới chưa phân công).
// Tránh trường hợp KTV chuyên môn khác (như Mail/M365) xem hoặc can thiệp vào phiếu của KTV phụ trách (như Canvas LMS).
if ($userRole === 'agent' && !empty($ticket['assigned_to']) && (int)$ticket['assigned_to'] !== (int)$currentUser['id']) {
    $assignedAgentName = db()->query("SELECT name FROM users WHERE id = " . (int)$ticket['assigned_to'])->fetchColumn() ?: "KTV khác";
    set_flash('error', "Phiếu #{$ticket['ticket_code']} đang được phân công chuyên môn cho {$assignedAgentName}. KTV khác không có quyền can thiệp!");
    header('Location: ../agent/index.php');
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
// XỬ LÝ KỊCH BẢN 1: KTV XÁC THỰC LỖI KỸ THUẬT CHO HỌC PHẦN
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ktv_verify_incident') {
    if ($userRole === 'agent') {
        $verifyNote = trim($_POST['verify_note'] ?? 'Kỹ thuật viên đã kiểm tra nhật ký Access Point KTX / Server LMS Canvas: Xác nhận tài khoản sinh viên gặp sự cố gián đoạn mạng ngoài ý muốn trong thời gian nộp bài.');
        db()->prepare("UPDATE tickets SET it_verified = 1, it_verified_at = NOW(), it_verified_note = :note WHERE id = :id")
            ->execute([':note' => $verifyNote, ':id' => $id]);

        $replyContent = "📋 [XÁC NHẬN SỰ CỐ KỸ THUẬT HỢP LỆ CHO HỌC PHẦN]\n" .
                        "Kỹ thuật viên phụ trách đã kiểm tra log hệ thống và cấp biên bản xác thực:\n" .
                        "\"" . $verifyNote . "\"\n" .
                        "👉 Kính chuyển Giảng viên phụ trách môn học xem xét chấp thuận mở lại Deadline / gia hạn nộp bài cho sinh viên.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'in_progress');

        // Tìm Giảng viên phụ trách môn học / lớp học phần này
        $lecturerId = 0;
        if (!empty($ticket['course_id']) && !empty($ticket['class_id'])) {
            $stmtLec = db()->prepare("SELECT lecturer_id FROM course_assignments WHERE course_id = :cid AND class_id = :clid LIMIT 1");
            $stmtLec->execute([':cid' => $ticket['course_id'], ':clid' => $ticket['class_id']]);
            $lecturerId = (int)$stmtLec->fetchColumn();
        }
        if (!$lecturerId && !empty($ticket['course_id'])) {
            $stmtLec = db()->prepare("SELECT lecturer_id FROM course_assignments WHERE course_id = :cid LIMIT 1");
            $stmtLec->execute([':cid' => $ticket['course_id']]);
            $lecturerId = (int)$stmtLec->fetchColumn();
        }
        if (!$lecturerId && !empty($ticket['lesson_id'])) {
            $stmtLec = db()->prepare("SELECT ca.lecturer_id FROM course_lessons cl JOIN course_assignments ca ON ca.course_id = cl.course_id WHERE cl.id = :lid LIMIT 1");
            $stmtLec->execute([':lid' => $ticket['lesson_id']]);
            $lecturerId = (int)$stmtLec->fetchColumn();
        }

        // Gửi thông báo đến Giảng viên phụ trách
        if ($lecturerId > 0) {
            NotificationService::send(
                $lecturerId,
                "🆘 KTV đã xác nhận sự cố nộp bài của sinh viên",
                "KTV vừa kiểm tra và xác nhận sự cố kỹ thuật buổi học trên phiếu #{$ticket['ticket_code']} ({$ticket['requester_name']}). Kính mời Thầy/Cô vào xem xét gia hạn Deadline!",
                "tickets/show.php?id={$id}",
                'deadline_extended',
                'lecturer'
            );
        }

        // Báo cho sinh viên biết KTV đã duyệt và đã chuyển sang GV
        if (!empty($ticket['requester_id'])) {
            NotificationService::send(
                (int)$ticket['requester_id'],
                "📋 KTV đã xác nhận sự cố kỹ thuật buổi học",
                "Kỹ thuật viên đã cấp biên bản xác nhận lỗi cho phiếu #{$ticket['ticket_code']} và chuyển tới Giảng viên bộ môn xem xét gia hạn Deadline.",
                "tickets/show.php?id={$id}",
                'ticket_reply',
                'student'
            );
        }

        set_flash('success', "Đã cấp biên bản xác nhận sự cố kỹ thuật hợp lệ thành công và chuyển thông tin tới Giảng viên bộ môn!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ KỊCH BẢN 1: GIẢNG VIÊN XEM XÉT VÀ MỞ LẠI DEADLINE CHO BUỔI HỌC
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'lecturer_extend_deadline') {
    if ($isCourseLecturer) {
        $newDeadline = trim($_POST['new_deadline'] ?? '');
        $lecturerNote = trim($_POST['lecturer_note'] ?? 'Thầy/Cô đồng ý gia hạn thời gian nộp bài do sự cố mạng đã được KTV xác thực.');
        $lessonId = (int)($ticket['lesson_id'] ?? 0);

        if ($lessonId > 0 && !empty($newDeadline)) {
            $newDeadlineSql = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $newDeadline)));

            // Kiểm tra nghiệp vụ: Hạn Thầy/Cô xét duyệt bắt buộc phải lớn hơn thời điểm hiện tại (> hôm nay)
            if (strtotime($newDeadlineSql) <= time()) {
                set_flash('error', 'Thời hạn mới được gia hạn phải lớn hơn thời điểm hiện tại (hôm nay)!');
                header("Location: show.php?id={$id}&view_as={$viewAs}");
                exit;
            }

            // 1. Cập nhật deadline_at của buổi học trong course_lessons
            db()->prepare("UPDATE course_lessons SET deadline_at = :dl WHERE id = :lid")
                ->execute([':dl' => $newDeadlineSql, ':lid' => $lessonId]);

            // 2. Format hiển thị đẹp
            $formattedDl = date('d/m/Y H:i', strtotime($newDeadlineSql));
            $lessonNum = $ticket['lesson_number'] ?? '';

            // 3. Tự động thêm tin nhắn chính thức từ Giảng viên vào cuộc hội thoại của ticket
            $replyContent = "🎓 [GIẢNG VIÊN BỘ MÔN CHẤP THUẬN GIA HẠN DEADLINE]\n" .
                            "Thầy/Cô đã xem xét biên bản xác nhận kỹ thuật từ KTV và đồng ý mở lại Deadline cho Buổi " . $lessonNum . ".\n" .
                            "⏰ Thời hạn nộp bài mới (Deadline): " . $formattedDl . "\n" .
                            "💬 Lời nhắn của Giảng viên: \"" . $lecturerNote . "\"\n" .
                            "✅ Sinh viên vui lòng truy cập Cổng Học Tập để nộp bài làm bổ sung trước thời hạn trên.";
            TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'closed');

            // 4. Cập nhật quyết định của Giảng viên & tự động hoàn tất phiếu
            db()->prepare("UPDATE tickets SET lecturer_decision = 'approved', lecturer_decision_at = NOW(), lecturer_decision_note = :note, status = 'closed', closed_at = NOW(), closed_reason = 'completed' WHERE id = :id")
                ->execute([':note' => $lecturerNote, ':id' => $id]);

            // Gửi thông báo cho Sinh viên được mở Deadline
            if (!empty($ticket['requester_id'])) {
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "Thầy/Cô đã chấp thuận mở lại Deadline cho bạn!",
                    "Giảng viên phụ trách đã mở lại hạn nộp bài Buổi {$lessonNum} đến {$formattedDl}. Vui lòng vào Cổng Học Tập nộp bài ngay!",
                    "tickets/show.php?id={$id}",
                    'deadline_extended',
                    'student'
                );
            }

            set_flash('success', "Thầy/Cô đã mở lại Deadline thành công cho Buổi {$lessonNum} đến: <strong>{$formattedDl}</strong>! Phiếu tự động chuyển sang trạng thái Đã hoàn tất.");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        } else {
            set_flash('error', "Vui lòng chọn thời hạn Deadline mới hợp lệ!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }
    }
}

// -----------------------------------------------------------------------------
// KTV BÁC BỎ SỰ CỐ KỸ THUẬT (SV BÁO SAI / KHÔNG CÓ LỖI) -> TỰ CHUYỂN HOÀN TẤT
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ktv_reject_incident') {
    if ($userRole === 'agent') {
        $rejectReason = trim($_POST['reject_reason'] ?? 'Kỹ thuật viên đã kiểm tra log hệ thống: Mạng và máy chủ hoạt động bình thường, không ghi nhận sự cố gián đoạn mạng. Sinh viên báo sai sự thật.');
        db()->prepare("UPDATE tickets SET it_verified = 2, it_verified_at = NOW(), it_verified_note = :note, status = 'closed', closed_at = NOW(), closed_reason = 'completed' WHERE id = :id")
            ->execute([':note' => $rejectReason, ':id' => $id]);

        $replyContent = "❌ [KTV BÁC BỎ YÊU CẦU SỰ CỐ KỸ THUẬT - KHÔNG CÓ LỖI]\n" .
                        "Kỹ thuật viên đã kiểm tra nhật ký hệ thống: Xác nhận KHÔNG CÓ sự cố kỹ thuật nào xảy ra.\n" .
                        "Lý do: \"" . $rejectReason . "\"\n" .
                        "👉 Yêu cầu báo lỗi bị bác bỏ. Phiếu tự động chuyển sang Hoàn tất và không chuyển cho Giảng viên.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'closed');

        // Bắn thông báo cho Sinh viên
        if (!empty($ticket['requester_id'])) {
            try {
                require_once __DIR__ . '/../app/Services/NotificationService.php';
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "❌ Kết quả kiểm tra trạm cứu hộ #{$ticket['ticket_code']}",
                    "Kỹ thuật viên đã kiểm tra nhật ký mạng & máy chủ: Không ghi nhận lỗi kỹ thuật khách quan. Yêu cầu cứu hộ buổi học đã được đóng.",
                    "tickets/show.php?id={$id}",
                    'ticket_reply',
                    'student'
                );
            } catch (\Throwable $e) {}
        }

        set_flash('info', "Đã bác bỏ báo lỗi kỹ thuật (Sinh viên báo sai). Phiếu tự động chuyển sang Hoàn tất & Đóng.");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// KTV THU HỒI / HỦY XÁC NHẬN LỖI ĐÃ CẤP
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ktv_reset_verification') {
    if ($userRole === 'agent') {
        db()->prepare("UPDATE tickets SET it_verified = 0, it_verified_at = NULL, it_verified_note = NULL, lecturer_decision = 'pending', lecturer_decision_at = NULL, lecturer_decision_note = NULL, status = 'in_progress', closed_at = NULL, closed_reason = NULL WHERE id = :id")
            ->execute([':id' => $id]);

        $replyContent = "↺ [KTV THU HỒI BIÊN BẢN XÁC THỰC]\n" .
                        "Kỹ thuật viên đã thu hồi biên bản xác nhận lỗi kỹ thuật để tiến hành kiểm tra lại.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'in_progress');

        set_flash('info', "Đã hủy xác nhận lỗi kỹ thuật thành công!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// GIẢNG VIÊN TỪ CHỐI GIA HẠN / HỦY GIA HẠN DEADLINE -> TỰ CHUYỂN HOÀN TẤT
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'lecturer_reject_extension') {
    if ($isCourseLecturer) {
        $rejectNote = trim($_POST['lecturer_reject_note'] ?? 'Thầy/Cô không chấp thuận gia hạn Deadline do thời gian làm bài đã đủ dài theo quy chế đào tạo.');
        
        db()->prepare("UPDATE tickets SET lecturer_decision = 'rejected', lecturer_decision_at = NOW(), lecturer_decision_note = :note, status = 'closed', closed_at = NOW(), closed_reason = 'completed' WHERE id = :id")
            ->execute([':note' => $rejectNote, ':id' => $id]);

        $replyContent = "🚫 [GIẢNG VIÊN BỘ MÔN TỪ CHỐI GIA HẠN DEADLINE]\n" .
                        "Thầy/Cô đã xem xét yêu cầu và quyết định KHÔNG mở lại Deadline cho Buổi " . ($ticket['lesson_number'] ?? '') . ".\n" .
                        "Lý do của Thầy/Cô: \"" . $rejectNote . "\"\n" .
                        "Sinh viên lưu ý nộp bài đúng hạn ở các buổi học tiếp theo.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'closed');

        // Bắn thông báo phản hồi cho sinh viên
        if (!empty($ticket['requester_id'])) {
            try {
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "Thầy/Cô đã từ chối yêu cầu gia hạn Deadline",
                    "Giảng viên bộ môn không chấp thuận mở lại Deadline cho Buổi " . ($ticket['lesson_number'] ?? '') . ". Lý do: {$rejectNote}",
                    "tickets/show.php?id={$id}",
                    'ticket_reply',
                    'student'
                );
            } catch (\Throwable $e) {}
        }

        set_flash('info', "Đã từ chối gia hạn Deadline cho sinh viên. Phiếu tự động chuyển sang Hoàn tất & Đóng.");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ TRẠM CỨU HỘ: KTV XÁC THỰC & CHUYỂN TIẾP CHO ADMIN
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ktv_verify_rescue') {
    if (in_array($userRole, ['agent', 'admin'])) {
        $verifyNote = trim($_POST['rescue_verify_note'] ?? 'Kỹ thuật viên đã kiểm tra thông tin sinh viên/cán bộ: Xác nhận hồ sơ hợp lệ và chuyển tiếp Ban Quản Trị (Admin) xem xét phê duyệt mở khóa tài khoản.');
        db()->prepare("UPDATE tickets SET rescue_verified = 1, rescue_verified_at = NOW(), rescue_verified_note = :note WHERE id = :id")
            ->execute([':note' => $verifyNote, ':id' => $id]);

        $replyContent = "🚑 [KTV ĐÃ XÁC THỰC ĐƠN CỨU HỘ & CHUYỂN ADMIN]\n" .
                        "Kỹ thuật viên đã kiểm tra hồ sơ, đối chiếu thông tin sinh viên/cán bộ hợp lệ.\n" .
                        "Ghi chú: \"" . $verifyNote . "\"\n" .
                        "👉 Kính chuyển Quản Trị Viên (Admin) xem xét phê duyệt mở khóa tài khoản và thiết lập lại mật khẩu.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'in_progress');

        // Gửi thông báo đến Quản trị viên (Admin)
        NotificationService::sendToRole(
            'admin',
            "KTV đã xác thực đơn cứu hộ #{$ticket['ticket_code']}",
            "KTV {$currentUser['name']} đã duyệt xác thực đơn cứu hộ sinh viên. Kính mời Ban Quản Trị phê duyệt mở khóa.",
            "tickets/show.php?id={$id}",
            'guest_rescue'
        );

        set_flash('success', "Đã xác thực đơn cứu hộ và chuyển tiếp Ban Quản Trị (Admin) thành công!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ TRẠM CỨU HỘ: KTV BÁC BỎ ĐƠN CỨU HỘ (SPAM / NGHỊCH TRẠM CỨU HỘ)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ktv_reject_rescue') {
    if (in_array($userRole, ['agent', 'admin'])) {
        $rejectNote = trim($_POST['rescue_reject_note'] ?? 'Kỹ thuật viên đã kiểm tra: Thông tin cung cấp không trùng khớp với hồ sơ hoặc có hành vi spam / nghịch trạm cứu hộ.');
        db()->prepare("UPDATE tickets SET rescue_verified = 2, rescue_verified_at = NOW(), rescue_verified_note = :note, status = 'closed', closed_at = NOW(), closed_reason = 'duplicate' WHERE id = :id")
            ->execute([':note' => $rejectNote, ':id' => $id]);

        $replyContent = "❌ [KTV BÁC BỎ ĐƠN CỨU HỘ]\n" .
                        "Kỹ thuật viên đã kiểm tra và bác bỏ yêu cầu cứu hộ này.\n" .
                        "Lý do: \"" . $rejectNote . "\"\n" .
                        "Đơn cứu hộ đã được đóng.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'closed');

        if (!empty($ticket['requester_id'])) {
            try {
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "KTV đã bác bỏ đơn cứu hộ tài khoản",
                    "Kỹ thuật viên đã kiểm tra và bác bỏ yêu cầu cứu hộ #{$ticket['ticket_code']}. Lý do: {$rejectNote}",
                    "tickets/show.php?id={$id}",
                    'guest_rescue',
                    'student'
                );
            } catch (\Throwable $e) {}
        }

        set_flash('info', "Đã bác bỏ đơn cứu hộ.");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ TRẠM CỨU HỘ: ADMIN DUYỆT MỞ KHÓA TÀI KHOẢN & RESET MẬT KHẨU
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_unlock_account') {
    if ($userRole === 'admin') {
        // KTV phải duyệt trước thì mới báo đến Admin ra quyết định mở khóa
        if ((int)($ticket['rescue_verified'] ?? 0) !== 1) {
            set_flash('error', "Kỹ thuật viên chưa xác thực duyệt đơn cứu hộ này. Ban Quản Trị chỉ xem xét sau khi KTV duyệt!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }

        $targetUserId = (int)$ticket['requester_id'];
        $defaultPassHash = password_hash('123456', PASSWORD_BCRYPT);
        db()->prepare("UPDATE users SET is_active = 1, locked_until = NULL, lock_reason = 'none', failed_attempts = 0, password_hash = :hash, is_first_login = 1 WHERE id = :uid")
            ->execute([':hash' => $defaultPassHash, ':uid' => $targetUserId]);
        db()->prepare("UPDATE tickets SET rescue_verified = 1, status = 'closed', closed_at = NOW(), closed_reason = 'completed' WHERE id = :id")
            ->execute([':id' => $id]);

        $replyContent = "🔓 [ADMIN PHÊ DUYỆT MỞ KHÓA TÀI KHOẢN & ĐẶT LẠI MẬT KHẨU]\n" .
                        "Quản trị viên đã phê duyệt đơn cứu hộ:\n" .
                        "✅ Tài khoản sinh viên/cán bộ đã được MỞ KHÓA thành công.\n" .
                        "🔑 Mật khẩu tạm thời đặt về: 123456 (Hệ thống yêu cầu đổi mật khẩu mới ở lần đăng nhập đầu tiên).\n" .
                        "📧 Hướng dẫn đã được xác nhận gửi về email liên hệ: " . ($ticket['contact_email'] ?? '') . ".";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'closed');

        if (!empty($targetUserId)) {
            try {
                NotificationService::send(
                    $targetUserId,
                    "🎉 Tài khoản của bạn đã được Admin phê duyệt mở khóa",
                    "Ban Quản Trị đã phê duyệt đơn cứu hộ và mở khóa tài khoản. Mật khẩu tạm: 123456. Vui lòng đăng nhập và đổi mật khẩu mới!",
                    "index.php",
                    'guest_rescue',
                    'student'
                );
            } catch (\Throwable $e) {}
        }

        set_flash('success', "Đã mở khóa tài khoản thành công và đặt lại mật khẩu về '123456'!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ TRẠM CỨU HỘ: ADMIN TỪ CHỐI CỨU HỘ & GIỮ NGUYÊN KHÓA TÀI KHOẢN
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_reject_rescue') {
    if ($userRole === 'admin') {
        if ((int)($ticket['rescue_verified'] ?? 0) !== 1) {
            set_flash('error', "Kỹ thuật viên chưa xác thực duyệt đơn cứu hộ này!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }

        $reason = trim($_POST['admin_reject_reason'] ?? 'Ban Quản Trị từ chối mở khóa tài khoản do thông tin xác minh không đủ căn cứ.');
        db()->prepare("UPDATE tickets SET rescue_verified = 2, rescue_verified_at = NOW(), rescue_verified_note = :note, status = 'closed', closed_at = NOW(), closed_reason = 'duplicate' WHERE id = :id")
            ->execute([':note' => $reason, ':id' => $id]);

        $replyContent = "🔒 [ADMIN TỪ CHỐI ĐƠN CỨU HỘ]\n" .
                        "Ban Quản Trị từ chối yêu cầu cứu hộ mở khóa.\n" .
                        "Lý do: \"" . $reason . "\"";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'closed');

        if (!empty($ticket['requester_id'])) {
            try {
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "Ban Quản Trị từ chối yêu cầu cứu hộ mở khóa",
                    "Đơn cứu hộ tài khoản #{$ticket['ticket_code']} bị từ chối. Lý do: {$reason}",
                    "tickets/show.php?id={$id}",
                    'guest_rescue',
                    'student'
                );
            } catch (\Throwable $e) {}
        }

        set_flash('info', "Đã từ chối đơn cứu hộ.");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// ADMIN TRỰC TIẾP KHÓA / MỞ KHÓA TÀI KHOẢN NGƯỜI DÙNG TỪ PHIẾU
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['admin_lock_requester', 'admin_unlock_requester'])) {
    if ($userRole === 'admin') {
        $reqUid = (int)$ticket['requester_id'];
        if ($_POST['action'] === 'admin_lock_requester') {
            db()->prepare("UPDATE users SET is_active = 0, lock_reason = 'admin_locked' WHERE id = :uid")->execute(['uid' => $reqUid]);
            TicketRepository::addReply($id, (int)$currentUser['id'], "🔒 [ADMIN KHÓA TÀI KHOẢN]: Quản trị viên đã khóa tài khoản sinh viên/người dùng này.", false);
            set_flash('success', "Đã khóa tài khoản của người dùng {$ticket['requester_name']} thành công!");
        } else {
            db()->prepare("UPDATE users SET is_active = 1, lock_reason = 'none', failed_attempts = 0 WHERE id = :uid")->execute(['uid' => $reqUid]);
            TicketRepository::addReply($id, (int)$currentUser['id'], "🔓 [ADMIN MỞ KHÓA TÀI KHOẢN]: Quản trị viên đã mở khóa tài khoản cho sinh viên/người dùng này.", false);
            set_flash('success', "Đã mở khóa tài khoản của người dùng {$ticket['requester_name']} thành công!");
        }
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// KTV & ADMIN ĐÓNG PHIẾU VỚI LÝ DO TRÙNG LẶP (DUPLICATE TICKET)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'close_duplicate') {
    if ($userRole === 'agent') {
        $dupNote = trim($_POST['duplicate_note'] ?? 'Đóng phiếu do phát hiện sự cố trùng lặp với phiếu đã gửi trước đó.');
        db()->prepare("UPDATE tickets SET status = 'closed', closed_at = NOW(), closed_reason = 'duplicate' WHERE id = :id")->execute(['id' => $id]);
        TicketRepository::addReply($id, (int)$currentUser['id'], "📑 [ĐÓNG DO TRÙNG LẶP]: {$dupNote}", false, 'closed');
        set_flash('info', "Đã đóng phiếu với lý do Trùng lặp sự cố (duplicate)!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// KTV BÁO CÁO SỰ CỐ DIỆN RỘNG (MAJOR INCIDENT) GỬI BAN QUẢN TRỊ (ADMIN)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ktv_report_major_incident') {
    if ($userRole === 'agent') {
        $sysAffected = trim($_POST['system_affected'] ?? $ticket['system_affected']);
        $majorNote = trim($_POST['major_note'] ?? 'KTV xác định sự cố ảnh hưởng diện rộng đến nhiều người dùng, đề nghị Ban Quản Trị xem xét công bố cảnh báo.');

        $newTitle = $ticket['title'];
        if (!str_contains($newTitle, '[SỰ CỐ DIỆN RỘNG]')) {
            $newTitle = '[SỰ CỐ DIỆN RỘNG] ' . $newTitle;
        }

        // Cập nhật mức độ ưu tiên Khẩn cấp (priority_id = 1) và cờ sự cố diện rộng
        db()->prepare("UPDATE tickets SET priority_id = 1, is_major_incident = 1, major_incident_note = :note, title = :title, system_affected = :sys WHERE id = :id")
            ->execute([':note' => $majorNote, ':title' => $newTitle, ':sys' => $sysAffected, ':id' => $id]);

        $replyMsg = "🔥 [KTV BÁO CÁO SỰ CỐ DIỆN RỘNG LÊN BAN QUẢN TRỊ (ADMIN)]\n" .
                    "KTV {$currentUser['name']} ({$currentUser['user_code']}) đã kích hoạt báo động sự cố diện rộng cấp cao.\n" .
                    "• Hệ thống bị ảnh hưởng: {$sysAffected}\n" .
                    "• Nội dung & Đề xuất KTV: \"{$majorNote}\"\n" .
                    "👉 Đã gửi thông báo khẩn cấp tới Quản Trị Viên (Admin) để tiếp nhận thông tin và chỉ đạo phương án ứng phó toàn trường!";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyMsg, false, 'in_progress');

        // Bắn thông báo khẩn cấp trực tiếp vào chuông thông báo của Admin
        try {
            require_once __DIR__ . '/../app/Services/NotificationService.php';
            NotificationService::sendToRole(
                'admin',
                "🚨 SỰ CỐ DIỆN RỘNG: Phiếu #{$ticket['ticket_code']}",
                "KTV {$currentUser['name']} vừa báo cáo sự cố diện rộng ảnh hưởng '{$sysAffected}'. Đề xuất: {$majorNote}. Kính mời Admin tiếp nhận chỉ đạo!",
                "tickets/show.php?id={$id}",
                'major_incident'
            );
        } catch (\Throwable $e) {}

        set_flash('success', "Đã gửi báo động sự cố diện rộng lên Ban Quản Trị (Admin) thành công! Quản trị viên đã nhận được thông báo để chỉ đạo.");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// ADMIN TIẾP NHẬN BÁO CÁO & PHÁT THÔNG BÁO CẢNH BÁO DIỆN RỘNG TOÀN TRƯỜNG
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_broadcast_major_incident') {
    if ($userRole === 'admin') {
        db()->prepare("UPDATE tickets SET admin_broadcast_status = 'broadcasted' WHERE id = :id")->execute([':id' => $id]);

        $replyContent = "📢 [ADMIN ĐÃ TIẾP NHẬN & PHÁT THÔNG BÁO CẢNH BÁO TOÀN TRƯỜNG]\n" .
                        "Ban Quản Trị đã tiếp nhận thông báo sự cố từ KTV và đã phát cảnh báo diện rộng tới toàn thể Sinh viên & Giảng viên.\n" .
                        "👉 Đội ngũ hạ tầng đang phối hợp xử lý, người dùng được khuyến nghị không gửi thêm phiếu trùng lặp.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, 'in_progress');

        // Phát thông báo cho Sinh viên và Giảng viên
        try {
            require_once __DIR__ . '/../app/Services/NotificationService.php';
            NotificationService::sendToRole('student', "⚠️ THÔNG BÁO SỰ CỐ DIỆN RỘNG: {$ticket['system_affected']}", "Hệ thống {$ticket['system_affected']} đang gặp sự cố diện rộng. Kỹ thuật viên đang khẩn trương khắc phục. Sinh viên không cần tạo thêm phiếu yêu cầu!", "tickets/show.php?id={$id}", 'system');
            NotificationService::sendToRole('lecturer', "⚠️ THÔNG BÁO SỰ CỐ DIỆN RỘNG: {$ticket['system_affected']}", "Hệ thống {$ticket['system_affected']} đang gặp sự cố diện rộng. Ban Quản Trị đang phối hợp xử lý.", "tickets/show.php?id={$id}", 'system');
        } catch (\Throwable $e) {}

        set_flash('success', "Quản trị viên đã phát thông báo cảnh báo sự cố diện rộng thành công tới toàn trường!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// ADMIN THU HỒI CẢNH BÁO KHẨN CẤP / SỰ CỐ DIỆN RỘNG (ĐÃ KHẮC PHỤC XONG)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_revoke_major_incident') {
    if ($userRole === 'admin') {
        $restoreNote = trim($_POST['restore_note'] ?? '');
        $closeTicket = !empty($_POST['close_ticket']) && $_POST['close_ticket'] == '1';
        $newStatus   = $closeTicket ? 'resolved' : $ticket['status'];

        $cleanTitle = trim(preg_replace('/^\[SỰ CỐ DIỆN RỘNG\]\s*/ui', '', $ticket['title']));
        $newTitle   = '[ĐÃ KHẮC PHỤC] ' . $cleanTitle;

        $sql = "UPDATE tickets 
                SET is_major_incident = 0,
                    admin_broadcast_status = 'resolved',
                    priority_id = 3,
                    title = :title";
        $params = [
            ':title' => $newTitle,
            ':id'    => $id,
        ];

        if ($closeTicket) {
            $sql .= ", status = 'resolved', resolved_at = NOW()";
        }
        $sql .= " WHERE id = :id";
        db()->prepare($sql)->execute($params);

        // Ghi log xử lý vào lịch sử phản hồi
        $replyContent = "✅ [ADMIN THU HỒI CẢNH BÁO - SỰ CỐ DIỆN RỘNG ĐÃ KHẮC PHỤC XONG]\n" .
                        "Ban Quản Trị thông báo: Sự cố kỹ thuật diện rộng đối với hệ thống '{$ticket['system_affected']}' đã được khắc phục hoàn tất.\n" .
                        ($restoreNote ? "👉 Ghi chú khôi phục: {$restoreNote}\n" : "") .
                        "Trạng thái cảnh báo diện rộng đã được thu hồi. Hệ thống trở lại hoạt động bình thường.";
        TicketRepository::addReply($id, (int)$currentUser['id'], $replyContent, false, $newStatus);

        // Phát thông báo khôi phục bình thường cho Sinh viên, Giảng viên và KTV
        try {
            require_once __DIR__ . '/../app/Services/NotificationService.php';
            NotificationService::sendToRole(
                'student',
                "✅ HỆ THỐNG ĐÃ HOẠT ĐỘNG BÌNH THƯỜNG: {$ticket['system_affected']}",
                "Sự cố diện rộng hệ thống {$ticket['system_affected']} đã được khắc phục hoàn tất. Các dịch vụ số đã hoạt động bình thường trở lại!",
                "tickets/show.php?id={$id}",
                'system'
            );
            NotificationService::sendToRole(
                'lecturer',
                "✅ HỆ THỐNG ĐÃ HOẠT ĐỘNG BÌNH THƯỜNG: {$ticket['system_affected']}",
                "Sự cố diện rộng hệ thống {$ticket['system_affected']} đã được xử lý xong. Kính mời Thầy/Cô tiếp tục sử dụng hệ thống giảng dạy bình thường.",
                "tickets/show.php?id={$id}",
                'system'
            );
            NotificationService::sendToRole(
                'agent',
                "✅ SỰ CỐ DIỆN RỘNG ĐÃ THU HỒI: {$ticket['ticket_code']}",
                "Admin đã xác nhận sự cố {$ticket['system_affected']} đã được khắc phục và thu hồi cảnh báo diện rộng toàn trường.",
                "tickets/show.php?id={$id}",
                'system'
            );
        } catch (\Throwable $e) {}

        set_flash('success', "Quản trị viên đã thu hồi cảnh báo sự cố khẩn cấp và gửi thông báo hoạt động bình thường tới toàn trường thành công!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// QUẢN TRỊ VIÊN (ADMIN) XÓA PHIẾU HỖ TRỢ
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_delete_ticket') {
    if ($userRole === 'admin') {
        TicketRepository::delete($id);
        set_flash('success', "Quản trị viên đã xóa vĩnh viễn phiếu {$ticket['ticket_code']} khỏi hệ thống!");
        header("Location: index.php");
        exit;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ SINH VIÊN / GIẢNG VIÊN XÓA LẠI PHIẾU TRONG VÒNG 15 PHÚT ĐẦU TIÊN
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'requester_delete_ticket') {
    $createdTime = strtotime($ticket['created_at']);
    $minutes = (time() - $createdTime) / 60;
    $isOwner = in_array($userRole, ['requester', 'lecturer']) && ((int)$ticket['requester_id'] === (int)$currentUser['id']);

    if ($isOwner && $minutes <= 15 && in_array($ticket['status'], ['new', 'waiting_requester'])) {
        TicketRepository::delete($id);
        set_flash('success', "Đã xóa và thu hồi phiếu hỗ trợ {$ticket['ticket_code']} thành công!");
        header("Location: ../index.php");
        exit;
    } else {
        set_flash('error', "Không thể xóa phiếu (Đã quá 15 phút hoặc phiếu đang được xử lý)!");
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

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

    // BẮN THÔNG BÁO CHO KTV PHỤ TRÁCH KHI NGƯỜI DÙNG ĐÁNH GIÁ CSAT
    if (!empty($ticket['assigned_to'])) {
        try {
            $scoreStars = str_repeat('⭐', $score);
            $userRoleLabel = ($userType === 'lecturer') ? "Thầy/Cô" : "Sinh viên";
            NotificationService::send(
                (int)$ticket['assigned_to'],
                "{$scoreStars} Đánh giá CSAT mới từ người dùng",
                "{$userRoleLabel} {$currentUser['name']} vừa đánh giá {$score} sao cho phiếu #{$ticket['ticket_code']}: \"{$comment}\". Cảm ơn bạn đã tận tâm hỗ trợ!",
                "agent/ratings.php?agent_id={$ticket['assigned_to']}",
                'rating',
                'agent'
            );
        } catch (\Throwable $e) {}
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

    // Bắn thông báo cho KTV phụ trách khi người dùng mở lại phiếu
    if (!empty($ticket['assigned_to'])) {
        try {
            NotificationService::send(
                (int)$ticket['assigned_to'],
                "⚠️ Người dùng yêu cầu mở lại phiếu #{$ticket['ticket_code']}",
                "{$currentUser['name']} báo chưa hài lòng và yêu cầu hỗ trợ tiếp: \"{$reopenNote}\".",
                "tickets/show.php?id={$id}",
                'ticket_reply',
                'agent'
            );
        } catch (\Throwable $e) {}
    }

    set_flash('info', "Đã gửi yêu cầu hỗ trợ tiếp! Phiếu đã được kích hoạt lại trạng thái <strong>Đang xử lý</strong>.");
    header("Location: show.php?id={$id}&view_as={$viewAs}");
    exit;
}

// -----------------------------------------------------------------------------
// 3. XỬ LÝ GỬI PHẢN HỒI / GIẢI PHÁP & ĐÍNH KÈM ẢNH
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    // Admin tất cả các phiếu chỉ có quyền xem & phân công sang KTV, không có quyền chat hay phản hồi
    if ($userRole === 'admin') {
        set_flash('error', 'Quản trị viên chỉ có quyền xem và phân công sang KTV, không có quyền gửi phản hồi hay chat trực tiếp trên phiếu!');
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }

    if ($ticket['status'] === 'closed' || !empty($currentRating)) {
        set_flash('error', 'Phiếu đã đóng và hoàn tất đánh giá CSAT. Không thể gửi thêm phản hồi!');
        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
    $message = trim($_POST['message'] ?? '');
    
    // Đăng nhập tài khoản của ai thì lấy trực tiếp ID người đó làm tác giả phản hồi
    $userId = (int)$currentUser['id'];
    if ($userRole === 'requester') {
        $isInternal = false;
        $forceStatus = null;
    } else {
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
        
        // Gửi thông báo đến bên đối thoại
        if (!$isInternal) {
            $shortMsg = mb_strlen($message) > 100 ? mb_substr($message, 0, 100) . '...' : $message;
            $isRequester = ((int)$ticket['requester_id'] === $userId);

            if ($isRequester) {
                // Người tạo phiếu nhắn (SV hoặc GV) -> Gửi thông báo cho KTV phụ trách
                if (!empty($ticket['assigned_to'])) {
                    $senderRole = ($userType === 'lecturer') ? "Thầy/Cô" : "Sinh viên";
                    NotificationService::send(
                        (int)$ticket['assigned_to'],
                        "💬 {$senderRole} vừa phản hồi phiếu #{$ticket['ticket_code']}",
                        "{$currentUser['name']} vừa nhắn: \"{$shortMsg}\"",
                        "tickets/show.php?id={$id}",
                        'ticket_reply',
                        'agent'
                    );
                }
            } else {
                // KTV / Admin / GV bộ môn nhắn -> Gửi thông báo cho người tạo phiếu (SV hoặc GV)
                if (!empty($ticket['requester_id'])) {
                    $reqType = $ticket['requester_type'] ?? '';
                    if (!$reqType) {
                        $reqType = db()->query("SELECT user_type FROM users WHERE id = " . (int)$ticket['requester_id'])->fetchColumn() ?: 'student';
                    }
                    $targetRole = ($reqType === 'lecturer') ? 'lecturer' : 'student';
                    $senderLabel = in_array($userRole, ['agent', 'admin']) ? "Kỹ thuật viên {$currentUser['name']}" : "Thầy/Cô {$currentUser['name']}";
                    NotificationService::send(
                        (int)$ticket['requester_id'],
                        "💬 Phản hồi mới trên phiếu #{$ticket['ticket_code']}",
                        "{$senderLabel} vừa nhắn: \"{$shortMsg}\"",
                        "tickets/show.php?id={$id}",
                        'ticket_reply',
                        $targetRole
                    );
                }
            }
        }
        
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
    if ($userRole === 'agent') {
        $newStatus = trim($_POST['status'] ?? '');
        if ($userRole === 'agent' && $newStatus === 'closed') {
            set_flash('error', "Kỹ thuật viên chỉ có thể cập nhật trạng thái đến 'Đã giải quyết' (resolved). Trạng thái 'Đã đóng hoàn tất' (closed) do Sinh viên nghiệm thu/đánh giá CSAT hoặc tự động sau 24h!");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }
        if (in_array($newStatus, ['new', 'in_progress', 'waiting_requester', 'resolved', 'closed'])) {
            TicketRepository::updateStatus($id, $newStatus);
            $statusLabelsVi = [
                'new'               => 'mới tiếp nhận',
                'in_progress'       => 'đang xử lý',
                'waiting_requester' => 'chờ phản hồi',
                'resolved'          => 'đã giải quyết',
                'closed'            => 'đã hoàn tất & đóng',
            ];
            $statusVi = $statusLabelsVi[$newStatus] ?? $newStatus;

            // Gửi thông báo chuyển trạng thái đến người gửi phiếu (Sinh viên hoặc Giảng viên)
            if (!empty($ticket['requester_id'])) {
                try {
                    require_once __DIR__ . '/../app/Services/NotificationService.php';
                    $reqType = $ticket['requester_type'] ?? '';
                    if (!$reqType) {
                        $reqType = db()->query("SELECT user_type FROM users WHERE id = " . (int)$ticket['requester_id'])->fetchColumn() ?: 'student';
                    }
                    $targetRole = ($reqType === 'lecturer') ? 'lecturer' : 'student';
                    $salutation = ($reqType === 'lecturer') ? "Kính mời Thầy/Cô" : "Mời bạn";

                    if ($newStatus === 'resolved') {
                        NotificationService::send(
                            (int)$ticket['requester_id'],
                            "🎯 Phiếu #{$ticket['ticket_code']} đã có giải pháp xử lý",
                            "Kỹ thuật viên đã chuyển phiếu sang trạng thái ĐÃ GIẢI QUYẾT. {$salutation} vào kiểm tra kết quả và thực hiện đánh giá chất lượng CSAT!",
                            "tickets/show.php?id={$id}",
                            'ticket_reply',
                            $targetRole
                        );
                    } else {
                        NotificationService::send(
                            (int)$ticket['requester_id'],
                            "Cập nhật tiến độ phiếu #{$ticket['ticket_code']}",
                            "Kỹ thuật viên vừa chuyển trạng thái phiếu hỗ trợ của bạn sang: '{$statusVi}'.",
                            "tickets/show.php?id={$id}",
                            'ticket_reply',
                            $targetRole
                        );
                    }
                } catch (\Throwable $e) {}
            }

            set_flash('success', "Đã cập nhật trạng thái phiếu sang {$statusVi}!");
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
        // Phiếu đã hoàn tất & đóng -> Không được điều chuyển phân công
        if (in_array($ticket['status'], ['closed', 'resolved'])) {
            set_flash('error', "Phiếu sự cố đã hoàn tất/đóng nên không thể điều chuyển phân công. Bạn chỉ có quyền xem và ghi lại lịch sử.");
            header("Location: show.php?id={$id}&view_as={$viewAs}");
            exit;
        }
        $newAgentId = (int)($_POST['assigned_to'] ?? 0);
        if ($newAgentId > 0) {
            $agStmt = db()->prepare("SELECT name, user_code FROM users WHERE id = :id AND role = 'agent' LIMIT 1");
            $agStmt->execute(['id' => $newAgentId]);
            $newAgent = $agStmt->fetch();

            if ($newAgent) {
                db()->prepare("UPDATE tickets SET assigned_to = :aid WHERE id = :id")->execute(['aid' => $newAgentId, 'id' => $id]);
                $transferTitle = ($userRole === 'agent') ? "KTV {$currentUser['name']} đã điều chuyển chuyên môn phiếu sự cố" : "Quản trị viên đã phân công phiếu sự cố";
                TicketRepository::addReply($id, (int)$currentUser['id'], "{$transferTitle} cho KTV: {$newAgent['name']} ({$newAgent['user_code']}).", false);

                // Bắn thông báo cho KTV mới nhận phiếu
                try {
                    require_once __DIR__ . '/../app/Services/NotificationService.php';
                    NotificationService::send(
                        $newAgentId,
                        "🎫 Phiếu sự cố mới được điều phối: #{$ticket['ticket_code']}",
                        "Bạn vừa được phân công xử lý phiếu sự cố: '{$ticket['title']}'. Vui lòng tiếp nhận và phản hồi theo SLA.",
                        "tickets/show.php?id={$id}",
                        'ticket_assigned',
                        'agent'
                    );

                    // Bắn thông báo cho Người gửi phiếu (Sinh viên hoặc Giảng viên)
                    if (!empty($ticket['requester_id'])) {
                        $reqType = $ticket['requester_type'] ?? '';
                        if (!$reqType) {
                            $reqType = db()->query("SELECT user_type FROM users WHERE id = " . (int)$ticket['requester_id'])->fetchColumn() ?: 'student';
                        }
                        $targetRole = ($reqType === 'lecturer') ? 'lecturer' : 'student';
                        NotificationService::send(
                            (int)$ticket['requester_id'],
                            "👨‍💻 KTV tiếp nhận phiếu #{$ticket['ticket_code']}",
                            "Phiếu hỗ trợ của bạn đã được phân công cho Kỹ thuật viên {$newAgent['name']} ({$newAgent['user_code']}) phụ trách xử lý.",
                            "tickets/show.php?id={$id}",
                            'ticket_reply',
                            $targetRole
                        );
                    }
                } catch (\Throwable $e) {}

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
        // KTV báo cáo phiếu về trạng thái đã giải quyết (resolved) để sang admin xem xét
        $pdo->prepare("UPDATE tickets SET is_spam = 1, spam_reason = :r, status = 'resolved', resolved_at = NOW() WHERE id = :id")->execute(['r' => $reason, 'id' => $id]);
        TicketRepository::addReply($id, (int)$currentUser['id'], "🚨 [KTV BÁO CÁO VI PHẠM / SPAM - CHUYỂN ADMIN XEM XÉT]: {$reason}", false, 'resolved');

        // Bắn thông báo cho Quản trị viên (Admin) và Sinh viên
        try {
            require_once __DIR__ . '/../app/Services/NotificationService.php';
            NotificationService::sendToRole(
                'admin',
                "🚨 VIỆC CẦN LÀM: KTV báo cáo sinh viên vi phạm",
                "KTV {$currentUser['name']} vừa lập biên bản vi phạm trên phiếu #{$ticket['ticket_code']}. Lý do: {$reason}. Cần Admin thẩm định xử lý kỷ luật!",
                "tickets/show.php?id={$id}",
                'violation_report'
            );
            if (!empty($ticket['requester_id'])) {
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "⚠️ Phiếu #{$ticket['ticket_code']} bị KTV báo cáo vi phạm",
                    "KTV vừa lập biên bản vi phạm đối với phiếu hỗ trợ của bạn (Lý do: {$reason}). Vấn đề đang được chuyển sang Ban Quản Trị xem xét.",
                    "tickets/show.php?id={$id}",
                    'ticket_reply',
                    'student'
                );
            }
        } catch (\Throwable $e) {}

        if ($lockPermanent || $userRole === 'admin') {
            $pdo->prepare("UPDATE users SET is_active = 0, lock_reason = 'banned_rescue_abuse' WHERE id = :uid")
                ->execute(['uid' => $ticket['requester_id']]);
            set_flash('success', "Đã ghi nhận báo cáo spam, chuyển trạng thái ĐÃ GIẢI QUYẾT sang Admin xem xét và KHÓA VĨNH VIỄN tài khoản!");
        } else {
            set_flash('success', "Đã gửi báo cáo vi phạm thành công! Phiếu đã chuyển sang trạng thái ĐÃ GIẢI QUYẾT và gửi sang Admin xem xét.");
        }

        header("Location: show.php?id={$id}&view_as={$viewAs}");
        exit;
    }
}

// -----------------------------------------------------------------------------
// 5.5.1. XỬ LÝ HỦY CẢNH BÁO SPAM / GIẢI OAN CHO SINH VIÊN (KTV & ADMIN)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unmark_spam') {
    if (in_array($userRole, ['agent', 'admin'])) {
        $pdo = db();
        // 1. Hủy bỏ cảnh báo spam của phiếu và đưa phiếu về lại đang xử lý
        $pdo->prepare("UPDATE tickets SET is_spam = 0, spam_reason = NULL, status = 'in_progress' WHERE id = :id")->execute(['id' => $id]);
        
        // 2. Mở khóa lại tài khoản sinh viên nếu bị khóa do spam
        $pdo->prepare("UPDATE users SET is_active = 1, lock_reason = 'none' WHERE id = :uid AND lock_reason IN ('banned_rescue_abuse', 'spam_reported')")
            ->execute(['uid' => $ticket['requester_id']]);

        $cancellerName = ($userRole === 'agent') ? "Kỹ thuật viên ({$currentUser['name']})" : "Quản trị viên (Admin)";
        // 3. Ghi chú lịch sử hệ thống
        TicketRepository::addReply(
            $id, 
            (int)$currentUser['id'], 
            "↺ [HỦY BÁO CÁO VI PHẠM]: {$cancellerName} đã xem xét lại toàn bộ nội dung, xác nhận không có vi phạm và hủy cảnh báo cho sinh viên. Phiếu quay lại tiếp tục xử lý.", 
            false,
            'in_progress'
        );

        // 4. Bắn thông báo giải oan cho Sinh viên
        if (!empty($ticket['requester_id'])) {
            try {
                require_once __DIR__ . '/../app/Services/NotificationService.php';
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "✅ Bạn đã được gỡ bỏ cảnh báo vi phạm",
                    "{$cancellerName} đã xem xét lại nội dung phiếu #{$ticket['ticket_code']}, xác nhận không có vi phạm và hủy báo cáo. Tài khoản của bạn đã được khôi phục bình thường!",
                    "tickets/show.php?id={$id}",
                    'ticket_reply',
                    'student'
                );
            } catch (\Throwable $e) {}
        }

        set_flash('success', "Đã HỦY BỎ BÁO CÁO VI PHẠM thành công! Phiếu đã trở lại trạng thái Đang xử lý và tài khoản sinh viên đã được khôi phục.");
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
        
        // Bắn thông báo mời người dùng (SV hoặc GV) vào đánh giá CSAT
        if (!empty($ticket['requester_id'])) {
            try {
                $reqType = $ticket['requester_type'] ?? '';
                if (!$reqType) {
                    $reqType = db()->query("SELECT user_type FROM users WHERE id = " . (int)$ticket['requester_id'])->fetchColumn() ?: 'student';
                }
                $targetRole = ($reqType === 'lecturer') ? 'lecturer' : 'student';
                $salutation = ($reqType === 'lecturer') ? "Kính mời Thầy/Cô" : "Mời bạn";
                NotificationService::send(
                    (int)$ticket['requester_id'],
                    "✅ KTV đã nghiệm thu giải pháp phiếu #{$ticket['ticket_code']}",
                    "Kỹ thuật viên đã hoàn thành giải pháp kỹ thuật. {$salutation} vào kiểm tra kết quả và thực hiện đánh giá chất lượng CSAT!",
                    "tickets/show.php?id={$id}",
                    'ticket_reply',
                    $targetRole
                );
            } catch (\Throwable $e) {}
        }

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
                <?php if ($userRole === 'agent' && !in_array($ticket['status'], ['resolved', 'closed'])): ?>
                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Xác nhận nghiệm thu xong giải pháp kỹ thuật cho phiếu này?');">
                        <input type="hidden" name="action" value="quick_resolve" />
                        <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-full shadow-sm transition flex items-center gap-1 cursor-pointer">
                            <span>✅</span> Nghiệm Thu Xong
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($userRole === 'agent'): ?>
                    <button type="button" onclick="document.getElementById('reportSpamModal').classList.remove('hidden')" 
                            class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-full border border-rose-200 transition flex items-center gap-1 cursor-pointer">
                        <span>🚨</span> Báo Cáo SV Spam
                    </button>
                <?php endif; ?>

                <?php if ($userRole === 'agent' && !in_array($ticket['status'], ['closed'])): ?>
                    <button type="button" onclick="document.getElementById('closeDuplicateModal').classList.remove('hidden')" 
                            class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-full border border-slate-300 transition flex items-center gap-1 cursor-pointer">
                        <span>📑</span> Đóng Trùng Lặp
                    </button>
                <?php endif; ?>

                <?php if ($userRole === 'agent' && empty($ticket['is_major_incident']) && !in_array($ticket['status'], ['closed', 'resolved'])): ?>
                    <button type="button" onclick="document.getElementById('reportMajorIncidentModal').classList.remove('hidden')" 
                            class="px-3.5 py-1.5 bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white text-xs font-black rounded-full shadow-sm transition flex items-center gap-1 cursor-pointer">
                        <span>🔥</span> Báo Sự Cố Cho Admin
                    </button>
                <?php endif; ?>

                <?php if ($userRole === 'admin' && !empty($ticket['is_major_incident'])): ?>
                    <button type="button" onclick="document.getElementById('revokeMajorIncidentModal').classList.remove('hidden')" 
                            class="px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white text-xs font-black rounded-full shadow-sm transition flex items-center gap-1 cursor-pointer">
                        <span>✅</span> Thu Hồi Cảnh Báo
                    </button>
                <?php endif; ?>

                <?php if ($userRole === 'admin' && !empty($ticket['requester_id'])): ?>
                    <?php $reqIsLocked = ((int)($ticket['requester_is_active'] ?? 1) === 0 || in_array($ticket['requester_lock_reason'] ?? '', ['admin_locked', 'expelled', 'spam_lock'])); ?>
                    <?php if ($reqIsLocked): ?>
                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Mở khóa tài khoản cho sinh viên/người dùng này?');">
                            <input type="hidden" name="action" value="admin_unlock_requester" />
                            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-full shadow-sm transition flex items-center gap-1 cursor-pointer">
                                <span>🔓</span> Mở Khóa SV
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Khóa tài khoản sinh viên này ngay lập tức do vi phạm quy chế?');">
                            <input type="hidden" name="action" value="admin_lock_requester" />
                            <button type="submit" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-full shadow-sm transition flex items-center gap-1 cursor-pointer">
                                <span>🔒</span> Khóa Tài Khoản SV
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php 
                $minutesSinceCreated = (time() - strtotime($ticket['created_at'])) / 60;
                $isRequesterOwner = in_array($userRole, ['requester', 'lecturer']) && ((int)$ticket['requester_id'] === (int)$currentUser['id']);
                $canRequesterModify15m = $isRequesterOwner && ($minutesSinceCreated <= 15) && in_array($ticket['status'], ['new', 'waiting_requester']);
                $minutesLeft15m = max(0, 15 - (int)floor($minutesSinceCreated));
                ?>

                <?php if ($canRequesterModify15m): ?>
                    <a href="edit.php?id=<?php echo $ticket['id']; ?>" class="px-3.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-300 transition flex items-center gap-1 shadow-2xs">
                        <span>✏️</span> Sửa phiếu (Còn <?= $minutesLeft15m ?>p)
                    </a>
                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA và thu hồi phiếu này khỏi hệ thống?');">
                        <input type="hidden" name="action" value="requester_delete_ticket" />
                        <button type="submit" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-full border border-rose-300 transition flex items-center gap-1 shadow-2xs cursor-pointer">
                            <span>🗑️</span> Xóa phiếu
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($userRole === 'admin'): ?>
                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA vĩnh viễn phiếu này khỏi hệ thống? Hành động này không thể hoàn tác.');">
                        <input type="hidden" name="action" value="admin_delete_ticket" />
                        <button type="submit" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-full border border-rose-300 transition flex items-center gap-1 shadow-2xs cursor-pointer" title="Xóa vĩnh viễn phiếu (Quyền Quản Trị)">
                            <span>🗑️</span> Xóa phiếu
                        </button>
                    </form>
                <?php endif; ?>
                <a href="index.php" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-full transition flex items-center gap-1">
                    ← Danh sách phiếu
                </a>
            </div>
        </div>
    </div>

    <!-- BANNER QUYỀN SỬA/XÓA 15 PHÚT DÀNH CHO NGƯỜI GỬI (SV & GIẢNG VIÊN) -->
    <?php if ($canRequesterModify15m): ?>
        <div class="p-4 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-300 rounded-3xl flex items-center justify-between flex-wrap gap-3 text-xs text-amber-950 shadow-xs mb-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⏳</span>
                <div>
                    <strong class="text-sm block">Quyền Chỉnh Sửa & Hủy Phiếu Trong 15 Phút Đầu</strong>
                    <span class="text-slate-600">Bạn có quyền chỉnh sửa lại nội dung hoặc thu hồi (xóa) phiếu này nếu gửi nhầm. Thời gian còn lại: <strong class="text-rose-600 font-mono text-sm"><?= $minutesLeft15m ?> phút</strong>.</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="edit.php?id=<?= $ticket['id'] ?>" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>✏️</span> Sửa Phiếu
                </a>
                <form method="POST" action="show.php?id=<?= $ticket['id'] ?>&view_as=<?= $viewAs ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn XÓA và thu hồi phiếu hỗ trợ này? Hành động này không thể hoàn tác.');">
                    <input type="hidden" name="action" value="requester_delete_ticket" />
                    <button type="submit" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <span>🗑️</span> Xóa Phiếu
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- BANNER SỰ CỐ DIỆN RỘNG (MAJOR INCIDENT) -->
    <?php if (!empty($ticket['is_major_incident'])): ?>
        <div class="p-4 bg-gradient-to-r from-amber-50 via-rose-50 to-orange-50 border-2 border-rose-400 rounded-3xl flex items-center justify-between flex-wrap gap-3 text-xs text-rose-950 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🔥</span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <strong class="text-rose-950 text-sm uppercase font-black">Sự Cố Kỹ Thuật Diện Rộng (Major Incident)</strong>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white animate-pulse">CẤP ĐỘ KHẨN CẤP</span>
                        <?php if (($ticket['admin_broadcast_status'] ?? '') === 'broadcasted'): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white">✓ ĐÃ PHÁT CẢNH BÁO TOÀN TRƯỜNG</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-rose-800 text-[11.5px] mt-0.5">
                        KTV đã báo động sự cố ảnh hưởng quy mô lớn: <strong><?php echo htmlspecialchars($ticket['system_affected']); ?></strong>.
                        <?php if (!empty($ticket['major_incident_note'])): ?>
                            — Đề xuất KTV: "<em><?php echo htmlspecialchars($ticket['major_incident_note']); ?></em>"
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php if ($userRole === 'admin'): ?>
                <div class="flex items-center gap-2 flex-wrap">
                    <?php if (($ticket['admin_broadcast_status'] ?? '') !== 'broadcasted'): ?>
                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Kích hoạt thông báo diện rộng tới toàn thể Sinh viên & Giảng viên trên toàn trường?');">
                            <input type="hidden" name="action" value="admin_broadcast_major_incident" />
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer">
                                <span>📢</span> Phát Cảnh Báo Toàn Trường
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="px-3.5 py-2 bg-emerald-100 text-emerald-800 font-bold rounded-xl text-xs border border-emerald-300">
                            📢 Cảnh báo đang hiển thị toàn trường
                        </span>
                    <?php endif; ?>

                    <button type="button" 
                            onclick="document.getElementById('revokeMajorIncidentModal').classList.remove('hidden')" 
                            class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <span>✅</span> Thu Hồi Cảnh Báo (Đã Khắc Phục Xong)
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif (($ticket['admin_broadcast_status'] ?? '') === 'resolved'): ?>
        <div class="p-4 bg-gradient-to-r from-emerald-50 via-teal-50 to-green-50 border-2 border-emerald-400 rounded-3xl flex items-center justify-between flex-wrap gap-3 text-xs text-emerald-950 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-3xl">✅</span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <strong class="text-emerald-950 text-sm uppercase font-black">Sự Cố Kỹ Thuật Khẩn Cấp Đã Được Khắc Phục Xong</strong>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white">✓ ĐÃ THU HỒI CẢNH BÁO TOÀN TRƯỜNG</span>
                    </div>
                    <p class="text-emerald-800 text-[11.5px] mt-0.5">
                        Ban Quản Trị đã xác nhận hệ thống <strong><?php echo htmlspecialchars($ticket['system_affected']); ?></strong> đã được khắc phục hoàn tất và đưa vào hoạt động bình thường.
                    </p>
                </div>
            </div>
            <div class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-3 py-1.5 rounded-xl border border-emerald-300">
                Hệ thống bình thường
            </div>
        </div>
    <?php endif; ?>

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
            <?php if (in_array($userRole, ['agent', 'admin'])): ?>
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- NÚT KTV & ADMIN HỦY CẢNH BÁO SPAM / GIẢI OAN CHO SINH VIÊN -->
                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Xác nhận HỦY BÁO CÁO VI PHẠM cho sinh viên này?\nPhiếu sẽ quay lại trạng thái Đang xử lý.');">
                        <input type="hidden" name="action" value="unmark_spam" />
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer">
                            <span>↺</span> Hủy Báo Cáo Vi Phạm
                        </button>
                    </form>

                    <?php if ($userRole === 'admin'): ?>
                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn KHÓA VĨNH VIỄN tài khoản này và chặn vĩnh viễn trạm cứu hộ?');">
                            <input type="hidden" name="action" value="report_spam" />
                            <input type="hidden" name="lock_permanent" value="1" />
                            <input type="hidden" name="spam_reason" value="Admin xử lý báo cáo: Khóa vĩnh viễn do spam/nghịch trạm cứu hộ" />
                            <button type="submit" class="px-4 py-2 bg-rose-700 hover:bg-rose-800 text-white font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5">
                                <span>🔒</span> Khóa Vĩnh Viễn
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
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

            <!-- KỊCH BẢN 1: NGỮ CẢNH HỌC PHẦN & BUỔI HỌC ĐÍNH KÈM -->
            <?php if (!empty($ticket['lesson_number']) || !empty($ticket['lesson_title'])): ?>
                <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 border border-indigo-200 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-black bg-indigo-600 text-white flex items-center gap-1 shadow-2xs">
                            <span>🎓</span> SỰ CỐ GẮN LIỀN VỚI BUỔI HỌC (ITIL CONTEXTUAL INCIDENT)
                        </span>
                        <span class="text-[11px] text-purple-700 font-bold font-mono">Buổi <?php echo (int)$ticket['lesson_number']; ?></span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-indigo-950">
                        <div>Môn học: <strong class="text-indigo-900"><?php echo htmlspecialchars($ticket['course_name'] ?? ''); ?></strong> (<?php echo htmlspecialchars($ticket['course_code'] ?? ''); ?>)</div>
                        <div>Lớp học phần: <strong><?php echo htmlspecialchars($ticket['class_code'] ?? ''); ?></strong></div>
                        <div class="sm:col-span-2">Tiêu đề buổi: <strong class="text-indigo-950 font-bold">Buổi <?php echo (int)$ticket['lesson_number']; ?>: <?php echo htmlspecialchars($ticket['lesson_title'] ?? ''); ?></strong></div>
                        <?php if (!empty($ticket['lesson_deadline_at'])): ?>
                            <div>Hạn nộp bài tập (Deadline): <strong class="text-rose-600 font-mono"><?php echo htmlspecialchars($ticket['lesson_deadline_at']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['lesson_requirement'])): ?>
                            <div class="sm:col-span-2 text-slate-600 text-[11.5px] bg-white/70 p-2.5 rounded-xl border border-indigo-100">
                                <span class="font-bold text-indigo-950">Đề bài yêu cầu:</span> <?php echo htmlspecialchars($ticket['lesson_requirement']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 1. BIÊN BẢN XÁC THỰC CỦA KTV -->
                    <?php if ((int)($ticket['it_verified'] ?? 0) === 1): ?>
                        <!-- TRƯỜNG HỢP 1: KTV ĐÃ XÁC NHẬN CÓ LỖI KỸ THUẬT -->
                        <div class="p-3.5 bg-emerald-50 border border-emerald-300 rounded-2xl flex items-start gap-2.5 text-xs text-emerald-900 shadow-2xs">
                            <span class="text-xl shrink-0">✅</span>
                            <div class="space-y-1.5 flex-1">
                                <div class="font-bold flex items-center justify-between flex-wrap gap-1">
                                    <span class="uppercase tracking-wide text-emerald-950 font-black">BIÊN BẢN KỸ THUẬT: KTV ĐÃ KIỂM TRA & XÁC NHẬN SỰ CỐ KỸ THUẬT HỢP LỆ</span>
                                    <span class="font-mono text-[11px] text-emerald-700 font-semibold"><?php echo htmlspecialchars($ticket['it_verified_at'] ?? ''); ?></span>
                                </div>
                                <p class="text-[11.5px] leading-relaxed bg-white/60 p-2.5 rounded-xl border border-emerald-200">
                                    "<?php echo htmlspecialchars($ticket['it_verified_note'] ?: 'Đã kiểm tra log hệ thống, xác nhận tài khoản sinh viên gặp sự cố ngắt kết nối mạng khách quan trong giờ nộp bài.'); ?>"
                                </p>
                                <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
                                    <span class="text-[10.5px] text-emerald-700 italic">✓ Căn cứ kỹ thuật chính thức đã được gửi tới Giảng viên bộ môn xem xét mở lại Deadline.</span>
                                    <?php if ($userRole === 'agent'): ?>
                                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" onsubmit="return confirm('Bạn có chắc chắn muốn thu hồi biên bản xác thực sự cố này?');">
                                            <input type="hidden" name="action" value="ktv_reset_verification" />
                                            <button type="submit" class="px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold rounded-lg text-[10.5px] transition flex items-center gap-1 border border-amber-300 cursor-pointer">
                                                <span>↺</span> Thu Hồi Xác Nhận
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php elseif ((int)($ticket['it_verified'] ?? 0) === 2): ?>
                        <!-- TRƯỜNG HỢP 2: KTV ĐÃ HỦY BÁO LỖI / BÁC BỎ (SV BÁO SAI) -->
                        <div class="p-3.5 bg-rose-50 border border-rose-300 rounded-2xl flex items-start gap-2.5 text-xs text-rose-900 shadow-2xs">
                            <span class="text-xl shrink-0">❌</span>
                            <div class="space-y-1.5 flex-1">
                                <div class="font-bold flex items-center justify-between flex-wrap gap-1">
                                    <span class="uppercase tracking-wide text-rose-950 font-black">BIÊN BẢN KỸ THUẬT: KTV ĐÃ BÁC BỎ — KHÔNG CÓ LỖI (SV BÁO SAI SỰ THẬT)</span>
                                    <span class="font-mono text-[11px] text-rose-700 font-semibold"><?php echo htmlspecialchars($ticket['it_verified_at'] ?? ''); ?></span>
                                </div>
                                <p class="text-[11.5px] leading-relaxed bg-white/60 p-2.5 rounded-xl border border-rose-200">
                                    "<?php echo htmlspecialchars($ticket['it_verified_note'] ?: 'Kỹ thuật viên đã kiểm tra log hệ thống: Mạng và máy chủ hoạt động bình thường, không ghi nhận sự cố gián đoạn mạng. Sinh viên báo sai sự thật.'); ?>"
                                </p>
                                <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
                                    <span class="text-[10.5px] text-rose-700 italic font-semibold">👉 Phiếu này đã được KTV bác bỏ và KHÔNG hiển thị trên danh sách của Giảng viên.</span>
                                    <?php if ($userRole === 'agent'): ?>
                                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>">
                                            <input type="hidden" name="action" value="ktv_reset_verification" />
                                            <button type="submit" class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-lg text-[10.5px] transition flex items-center gap-1 cursor-pointer">
                                                <span>↺</span> Kiểm Tra Lại Sự Cố
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($userRole === 'agent'): ?>
                        <!-- TRƯỜNG HỢP 3: CHƯA KIỂM TRA (DÀNH CHO KTV KIỂM TRA & XÁC THỰC HOẶC HỦY BÁO LỖI) -->
                        <div class="p-3.5 bg-indigo-50 border border-indigo-200 rounded-2xl space-y-2.5 text-xs shadow-2xs">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <span class="font-bold text-indigo-900 flex items-center gap-1.5">
                                    <span>🔍</span> Thao Tác Kỹ Thuật Viên: Kiểm Tra Nhật Ký Mạng / Máy Chủ
                                </span>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="document.getElementById('ktvVerifyForm').classList.toggle('hidden'); document.getElementById('ktvRejectForm').classList.add('hidden');" 
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer flex items-center gap-1">
                                        <span>⚡</span> KTV Xác Nhận Có Lỗi
                                    </button>
                                    <button type="button" onclick="document.getElementById('ktvRejectForm').classList.toggle('hidden'); document.getElementById('ktvVerifyForm').classList.add('hidden');" 
                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer flex items-center gap-1">
                                        <span>❌</span> Hủy Báo Lỗi (SV Báo Sai)
                                    </button>
                                </div>
                            </div>

                            <!-- FORM KTV XÁC NHẬN CÓ LỖI KỸ THUẬT HỢP LỆ -->
                            <form id="ktvVerifyForm" method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="hidden pt-2 border-t border-indigo-200 space-y-2">
                                <input type="hidden" name="action" value="ktv_verify_incident" />
                                <label class="block font-bold text-slate-700 text-[11px]">Nội dung biên bản xác thực có lỗi kỹ thuật (Gửi Thầy/Cô bộ môn):</label>
                                <textarea name="verify_note" rows="2" class="w-full p-2.5 bg-white border border-emerald-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">Kỹ thuật viên đã kiểm tra nhật ký Access Point Wi-Fi KTX / Server LMS Canvas: Xác nhận tài khoản sinh viên gặp sự cố gián đoạn mạng ngoài ý muốn trong thời gian nộp bài từ 23:45 - 23:59. Đề nghị Giảng viên phụ trách xem xét mở lại Deadline cho sinh viên.</textarea>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1 shadow-sm cursor-pointer">
                                        <span>✓</span> Cấp Biên Bản & Chuyển Giảng Viên Mở Deadline
                                    </button>
                                </div>
                            </form>

                            <!-- FORM KTV BÁC BỎ SỰ CỐ / HỦY BÁO LỖI VÌ SV BÁO SAI -->
                            <form id="ktvRejectForm" method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="hidden pt-2 border-t border-rose-200 space-y-2">
                                <input type="hidden" name="action" value="ktv_reject_incident" />
                                <label class="block font-bold text-rose-800 text-[11px]">Lý do bác bỏ / Hủy báo lỗi (SV nói sai / Log mạng bình thường):</label>
                                <textarea name="reject_reason" rows="2" class="w-full p-2.5 bg-white border border-rose-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500">Kỹ thuật viên đã kiểm tra log hệ thống mạng KTX và server Canvas: Kết nối hoàn toàn thông suốt, không có sự cố mạng. Sinh viên báo sai sự thật hoặc nộp trễ ngoài thời gian quy định.</textarea>
                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-rose-600 italic">Phiếu sẽ tự đóng và không hiển thị cho Giảng viên xem xét.</span>
                                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1 shadow-sm cursor-pointer">
                                        <span>❌</span> Xác Nhận Hủy Báo Lỗi (Không Có Lỗi)
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php elseif ($userRole === 'admin'): ?>
                        <!-- CHẾ ĐỘ GIÁM SÁT DÀNH CHO ADMIN: THEO DÕI TIẾN ĐỘ KTV -->
                        <div class="p-3.5 bg-slate-100 border border-slate-200 rounded-2xl flex items-center justify-between gap-2 text-xs text-slate-700">
                            <span class="flex items-center gap-2">
                                <span class="text-lg">👁️</span>
                                <span>Tiến độ kỹ thuật: Đang chờ <strong>Kỹ thuật viên phụ trách</strong> kiểm tra log mạng và server để cấp biên bản. Quản trị viên theo dõi ở chế độ giám sát và điều phối phân công.</span>
                            </span>
                        </div>
                    <?php endif; ?>

                    <!-- 2. KHỐI THẨM QUYỀN HỌC THUẬT CỦA GIẢNG VIÊN -->
                    <?php if ($isCourseLecturer || $userRole === 'admin'): ?>
                        <?php if (($ticket['lecturer_decision'] ?? '') === 'approved'): ?>
                            <!-- GIẢNG VIÊN ĐÃ CHẤP THUẬN -> BIẾN MẤT FORM, HIỆN KẾT QUẢ ĐÃ DUYỆT -->
                            <div class="p-4 bg-emerald-50 border-2 border-emerald-300 rounded-2xl space-y-2 text-xs text-emerald-950 shadow-2xs">
                                <div class="font-bold flex items-center justify-between flex-wrap gap-2 border-b border-emerald-200 pb-2">
                                    <span class="flex items-center gap-1.5 font-black text-sm text-emerald-900">
                                        <span>🎓</span> KẾT QUẢ XỬ LÝ HỌC VỤ: GIẢNG VIÊN ĐÃ CHẤP THUẬN GIA HẠN DEADLINE
                                    </span>
                                    <span class="font-mono text-[11px] text-emerald-700 font-semibold"><?php echo htmlspecialchars($ticket['lecturer_decision_at'] ?? ''); ?></span>
                                </div>
                                <p class="text-[12px] leading-relaxed">
                                    ✅ <strong>Thầy/Cô đã chấp thuận mở lại Deadline cho Buổi <?php echo (int)$ticket['lesson_number']; ?>:</strong>
                                    Hạn nộp mới: <strong class="font-mono text-emerald-800"><?php echo htmlspecialchars($ticket['lesson_deadline_at'] ?? ''); ?></strong>
                                </p>
                                <?php if (!empty($ticket['lecturer_decision_note'])): ?>
                                    <p class="text-[11.5px] italic text-emerald-800 bg-white/70 p-2.5 rounded-xl border border-emerald-200">
                                        "<?php echo htmlspecialchars($ticket['lecturer_decision_note']); ?>"
                                    </p>
                                <?php endif; ?>
                                <span class="text-[10.5px] text-emerald-700 block italic">Phiếu sự cố đã được giải quyết và tự động hoàn tất & đóng.</span>
                            </div>

                        <?php elseif (($ticket['lecturer_decision'] ?? '') === 'rejected'): ?>
                            <!-- GIẢNG VIÊN ĐÃ TỪ CHỐI -> BIẾN MẤT FORM, HIỆN KẾT QUẢ TỪ CHỐI -->
                            <div class="p-4 bg-rose-50 border-2 border-rose-300 rounded-2xl space-y-2 text-xs text-rose-950 shadow-2xs">
                                <div class="font-bold flex items-center justify-between flex-wrap gap-2 border-b border-rose-200 pb-2">
                                    <span class="flex items-center gap-1.5 font-black text-sm text-rose-900">
                                        <span>🎓</span> KẾT QUẢ XỬ LÝ HỌC VỤ: GIẢNG VIÊN ĐÃ TỪ CHỐI GIA HẠN DEADLINE
                                    </span>
                                    <span class="font-mono text-[11px] text-rose-700 font-semibold"><?php echo htmlspecialchars($ticket['lecturer_decision_at'] ?? ''); ?></span>
                                </div>
                                <p class="text-[12px] leading-relaxed">
                                    🚫 <strong>Thầy/Cô đã từ chối yêu cầu gia hạn Deadline cho Buổi <?php echo (int)$ticket['lesson_number']; ?>:</strong>
                                    Hạn nộp bài ban đầu được giữ nguyên theo quy chế đào tạo.
                                </p>
                                <?php if (!empty($ticket['lecturer_decision_note'])): ?>
                                    <p class="text-[11.5px] italic text-rose-800 bg-white/70 p-2.5 rounded-xl border border-rose-200">
                                        "<?php echo htmlspecialchars($ticket['lecturer_decision_note']); ?>"
                                    </p>
                                <?php endif; ?>
                                <span class="text-[10.5px] text-rose-700 block italic">Yêu cầu gia hạn đã bị từ chối và phiếu đã tự động hoàn tất & đóng.</span>
                            </div>

                        <?php elseif ((int)($ticket['it_verified'] ?? 0) === 1): ?>
                            <?php if ($userRole === 'admin'): ?>
                                <!-- CHẾ ĐỘ GIÁM SÁT DÀNH CHO ADMIN: XEM TIẾN ĐỘ THẨM QUYỀN HỌC THUẬT -->
                                <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-2xl flex items-center justify-between gap-2 text-xs text-purple-900 shadow-2xs">
                                    <span class="flex items-center gap-2">
                                        <span class="text-lg">🎓</span>
                                        <span>Thẩm quyền học thuật: Kỹ thuật viên đã cấp biên bản xác thực lỗi kỹ thuật. Đang chờ <strong>Giảng viên phụ trách môn học</strong> xem xét mở lại Deadline cho sinh viên.</span>
                                    </span>
                                </div>
                            <?php elseif ($isCourseLecturer): ?>
                                <!-- KTV ĐÃ XÁC NHẬN & GV CHƯA QUYẾT ĐỊNH: HIỆN 2 LỰA CHỌN DÀNH CHO GIẢNG VIÊN -->
                                <div class="p-4 bg-gradient-to-r from-purple-100/90 via-indigo-100/90 to-purple-50 border-2 border-purple-300 rounded-2xl space-y-3 text-xs">
                                    <div class="flex items-center justify-between flex-wrap gap-2 border-b border-purple-200 pb-2">
                                        <span class="font-black text-purple-950 flex items-center gap-1.5 text-sm">
                                            <span>🎓</span> THẨM QUYỀN HỌC THUẬT: GIẢNG VIÊN XEM XÉT GIA HẠN DEADLINE
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-700 text-white">
                                            Buổi <?php echo (int)$ticket['lesson_number']; ?>
                                        </span>
                                    </div>
                                    <p class="text-purple-900 leading-relaxed text-[11.5px]">
                                        ✅ <strong>Kỹ thuật viên đã kiểm tra và xác nhận sự cố kỹ thuật là có thật.</strong> Thầy/Cô có toàn quyền sư phạm: chấp thuận mở lại hạn nộp bài (Deadline) hoặc từ chối gia hạn nếu lý do chưa hợp lý.
                                    </p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <!-- CỘT 1: CHẤP THUẬN & MỞ LẠI DEADLINE -->
                                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-2.5 bg-white/90 p-3.5 rounded-xl border border-purple-200 flex flex-col justify-between">
                                            <input type="hidden" name="action" value="lecturer_extend_deadline" />
                                            <div class="space-y-2">
                                                <span class="font-bold text-emerald-800 flex items-center gap-1 text-xs">
                                                    <span>⚡</span> Phương Án 1: Chấp Thuận Mở Lại Hạn Nộp
                                                </span>
                                                <div>
                                                    <label class="block font-bold text-slate-700 text-[11px] mb-1">Thời hạn mới (Deadline gia hạn):</label>
                                                    <?php $suggestedDeadline = date('Y-m-d\TH:i', strtotime('+24 hours')); ?>
                                                    <input type="datetime-local" name="new_deadline" value="<?php echo $suggestedDeadline; ?>" min="<?php echo date('Y-m-d\TH:i'); ?>" required 
                                                           class="w-full px-2.5 py-1.5 bg-slate-50 border border-purple-300 rounded-lg font-mono text-purple-900 font-bold text-xs focus:ring-2 focus:ring-purple-500" />
                                                </div>
                                                <div>
                                                    <label class="block font-bold text-slate-700 text-[11px] mb-1">Lời nhắn của Thầy/Cô:</label>
                                                    <input type="text" name="lecturer_note" value="Thầy/Cô chấp thuận mở lại Deadline do sự cố kỹ thuật đã được KTV xác thực. Em hãy tranh thủ nộp bài bổ sung nhé!" 
                                                           class="w-full px-2.5 py-1.5 bg-slate-50 border border-purple-300 rounded-lg text-slate-800 text-xs focus:ring-2 focus:ring-purple-500" />
                                                </div>
                                            </div>
                                            <button type="submit" class="w-full mt-2 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center justify-center gap-1.5 cursor-pointer">
                                                <span>⚡</span> Chấp Thuận & Mở Lại Deadline
                                            </button>
                                        </form>

                                        <!-- CỘT 2: TỪ CHỐI GIA HẠN / HỦY GIA HẠN -->
                                        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-2.5 bg-white/90 p-3.5 rounded-xl border border-rose-200 flex flex-col justify-between">
                                            <input type="hidden" name="action" value="lecturer_reject_extension" />
                                            <div class="space-y-2">
                                                <span class="font-bold text-rose-800 flex items-center gap-1 text-xs">
                                                    <span>🚫</span> Phương Án 2: Hủy / Từ Chối Gia Hạn
                                                </span>
                                                <p class="text-[11px] text-slate-600">
                                                    Nếu Thầy/Cô xét thấy sự cố xảy ra quá lâu sau deadline, hoặc sinh viên đã có đủ thời gian làm bài từ trước:
                                                </p>
                                                <div>
                                                    <label class="block font-bold text-rose-800 text-[11px] mb-1">Lý do Thầy/Cô từ chối gia hạn:</label>
                                                    <textarea name="lecturer_reject_note" rows="2" class="w-full p-2 bg-slate-50 border border-rose-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500" placeholder="Nhập lý do gửi sinh viên...">Thầy/Cô không chấp thuận mở lại Deadline do thời gian làm bài tập đã kéo dài 7 ngày, sự cố mạng KTX chỉ diễn ra ngắn và sinh viên cần chủ động hoàn thành sớm hơn.</textarea>
                                                </div>
                                            </div>
                                            <button type="submit" onclick="return confirm('Thầy/Cô có chắc chắn muốn TỪ CHỐI gia hạn Deadline cho sinh viên này?');" class="w-full mt-2 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center justify-center gap-1.5 cursor-pointer">
                                                <span>🚫</span> Xác Nhận Từ Chối Mở Deadline
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php elseif ((int)($ticket['it_verified'] ?? 0) === 2): ?>
                            <!-- KTV ĐÃ BÁC BỎ LỖI: THÔNG BÁO CHO GV BIẾT -->
                            <div class="p-3.5 bg-slate-100 border border-slate-300 rounded-2xl flex items-center justify-between gap-2 text-xs text-slate-700">
                                <span class="flex items-center gap-2">
                                    <span class="text-lg">ℹ️</span>
                                    <span>Kỹ thuật viên đã kiểm tra và <strong>bác bỏ</strong> sự cố kỹ thuật này (xác nhận sinh viên báo sai). Thầy/Cô không cần xử lý mở lại hạn nộp.</span>
                                </span>
                            </div>
                        <?php elseif ($isCourseLecturer): ?>
                            <!-- CHỜ KTV KIỂM TRA DÀNH CHO GIẢNG VIÊN -->
                            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between gap-2 text-xs text-amber-900">
                                <span class="flex items-center gap-2">
                                    <span class="text-lg">⏳</span>
                                    <span>Đang chờ Kỹ thuật viên kiểm tra log hệ thống mạng & server LMS. Thầy/Cô chỉ cần ra quyết định khi KTV xác nhận có lỗi kỹ thuật khách quan.</span>
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 3. THÔNG BÁO CHO SINH VIÊN -->
                    <?php if ($userRole === 'requester' && !$isLecturer): ?>
                        <?php if (($ticket['lecturer_decision'] ?? '') === 'approved'): ?>
                            <!-- THẦY/CÔ ĐÃ CHẤP THUẬN -> HIỆN NÚT NỘP BÀI -->
                            <div class="p-3.5 bg-emerald-50 border border-emerald-300 rounded-2xl flex items-center justify-between flex-wrap gap-2 text-xs text-emerald-900 shadow-2xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-2xl">🎉</span>
                                    <div>
                                        <strong class="text-emerald-950 font-bold block">Thầy/Cô đã chấp thuận gia hạn Deadline cho Buổi này!</strong>
                                        <div class="text-[11px] text-emerald-700 mt-0.5">Hạn nộp hiện tại: <span class="font-mono font-bold"><?php echo htmlspecialchars($ticket['lesson_deadline_at'] ?? ''); ?></span></div>
                                    </div>
                                </div>
                                <a href="../student/courses.php?view_course_id=<?php echo (int)($ticket['course_id'] ?? 1); ?>" 
                                   class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-2xs flex items-center gap-1 cursor-pointer">
                                    <span>📤</span> Vào Cổng Học Tập Nộp Bài Ngay →
                                </a>
                            </div>

                        <?php elseif (($ticket['lecturer_decision'] ?? '') === 'rejected'): ?>
                            <!-- THẦY/CÔ ĐÃ TỪ CHỐI -> THÔNG BÁO RÕ RÀNG, KHÔNG CÓ NÚT NỘP -->
                            <div class="p-3.5 bg-rose-50 border border-rose-300 rounded-2xl flex items-center justify-between flex-wrap gap-2 text-xs text-rose-900 shadow-2xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-2xl">🚫</span>
                                    <div>
                                        <strong class="text-rose-950 font-bold block">Thầy/Cô bộ môn đã từ chối yêu cầu gia hạn Deadline cho Buổi này.</strong>
                                        <?php if (!empty($ticket['lecturer_decision_note'])): ?>
                                            <div class="text-[11px] text-rose-800 mt-0.5">Lý do: "<em><?php echo htmlspecialchars($ticket['lecturer_decision_note']); ?></em>"</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-rose-200 text-rose-800">
                                    Đã Đóng
                                </span>
                            </div>

                        <?php elseif ((int)($ticket['it_verified'] ?? 0) === 1): ?>
                            <!-- KTV MỚI XÁC NHẬN, THẦY CÔ CHƯA DUYỆT -> CHỜ THẦY/CÔ, CHƯA HIỆN NÚT NỘP -->
                            <div class="p-3.5 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between flex-wrap gap-2 text-xs text-indigo-900 shadow-2xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-2xl">⏳</span>
                                    <div>
                                        <strong class="text-indigo-950 font-bold block">Kỹ thuật viên đã kiểm tra và xác nhận sự cố kỹ thuật hợp lệ!</strong>
                                        <div class="text-[11px] text-indigo-700 mt-0.5">Biên bản đã được chuyển đến Thầy/Cô bộ môn xem xét gia hạn. Vui lòng chờ phản hồi chính thức từ Thầy/Cô.</div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-indigo-100 text-indigo-700">
                                    Đang chờ Thầy/Cô xét duyệt
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- THÔNG TIN PHIẾU CỨU HỘ KHẨN CẤP (GUEST RESCUE TICKET) -->
            <?php if (!empty($ticket['is_guest_ticket'])): ?>
                <?php
                // Lấy thông tin tài khoản đích trong hệ thống
                $guestUserStmt = db()->prepare("
                    SELECT id, user_code, name, email, lock_reason, failed_attempts, is_active, locked_until,
                           CASE WHEN (is_active = 0 OR (locked_until IS NOT NULL AND locked_until > NOW()) OR (lock_reason IS NOT NULL AND lock_reason != 'none')) THEN 1 ELSE 0 END AS is_locked
                    FROM users 
                    WHERE id = :uid
                ");
                $guestUserStmt->execute([':uid' => (int)$ticket['requester_id']]);
                $guestUser = $guestUserStmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 border-2 border-rose-300 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2 border-b border-rose-200 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">🚑</span>
                            <div>
                                <h3 class="text-sm font-black text-rose-950 flex items-center gap-2">
                                    <span>PHIẾU CỨU HỘ KHẨN CẤP (GUEST RESCUE TICKET)</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-600 text-white">
                                        Trạm Cứu Hộ Ngoài Cổng
                                    </span>
                                </h3>
                                <p class="text-[11.5px] text-rose-800 mt-0.5">
                                    Sinh viên bị khóa tài khoản hoặc quên mật khẩu gửi yêu cầu từ Trạm cứu hộ trực tuyến.
                                </p>
                            </div>
                        </div>
                        <?php if ($guestUser && !empty($guestUser['is_locked'])): ?>
                            <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-600 text-white flex items-center gap-1 shadow-2xs">
                                🔒 Tài Khoản Đang Bị Khóa
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                                🔓 Tài Khoản Bình Thường
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-white/80 p-3 rounded-2xl border border-rose-200">
                        <div>
                            <span class="text-slate-400 block text-[11px] font-semibold">Mã SV/CB cần cứu hộ:</span>
                            <strong class="font-mono text-indigo-700 text-sm"><?php echo htmlspecialchars($ticket['requester_code'] ?? $guestUser['user_code'] ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px] font-semibold">Họ và tên:</span>
                            <strong class="text-slate-800 text-sm"><?php echo htmlspecialchars($ticket['requester_name'] ?? $guestUser['name'] ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px] font-semibold">Email cá nhân nhận kết quả:</span>
                            <strong class="text-rose-700 font-mono text-xs"><?php echo htmlspecialchars($ticket['contact_email'] ?? 'Chưa cung cấp'); ?></strong>
                        </div>
                    </div>

                    <!-- THAO TÁC KTV TIẾP NHẬN: XÁC MINH & CHUYỂN ADMIN DUYỆT -->
                    <?php if ($userRole === 'agent'): ?>
                        <?php if ((int)($ticket['rescue_verified'] ?? 0) === 0 && $ticket['status'] !== 'closed'): ?>
                            <div class="p-3 bg-white/90 rounded-2xl border border-amber-300 space-y-2 text-xs">
                                <span class="font-bold text-amber-900 flex items-center gap-1">
                                    <span>🔍</span> Thao Tác KTV Trực Ca: Kiểm tra thẻ SV / CMND & Chuyển tiếp Admin
                                </span>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline">
                                        <input type="hidden" name="action" value="ktv_verify_rescue" />
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer flex items-center gap-1">
                                            <span>✓</span> KTV Xác Thực Hợp Lệ & Chuyển Admin Mở Khóa
                                        </button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('ktvRejectRescueForm').classList.toggle('hidden')" 
                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer flex items-center gap-1">
                                        <span>❌</span> Bác Bỏ (Spam / Nghịch Trạm Cứu Hộ)
                                    </button>
                                </div>
                                <form id="ktvRejectRescueForm" method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="hidden pt-2 border-t border-rose-200 space-y-2">
                                    <input type="hidden" name="action" value="ktv_reject_rescue" />
                                    <label class="block font-bold text-rose-800 text-[11px]">Lý do KTV bác bỏ đơn cứu hộ:</label>
                                    <input type="text" name="rescue_reject_note" value="Thông tin không trùng khớp với hồ sơ nhà trường hoặc có dấu hiệu nghịch trạm cứu hộ." 
                                           class="w-full px-3 py-1.5 bg-white border border-rose-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500" />
                                    <button type="submit" class="px-3 py-1.5 bg-rose-700 hover:bg-rose-800 text-white font-bold rounded-xl text-xs transition">
                                        Xác Nhận Đóng Đơn Cứu Hộ
                                    </button>
                                </form>
                            </div>
                        <?php elseif ((int)($ticket['rescue_verified'] ?? 0) === 1): ?>
                            <div class="p-3 bg-emerald-50 border border-emerald-300 rounded-2xl flex items-center justify-between text-xs text-emerald-900">
                                <span class="flex items-center gap-2 font-bold">
                                    <span>✅</span> KTV Đã Xác Thực Danh Tính & Đang Chuyển Ban Quản Trị (Admin) Phê Duyệt Mở Khóa
                                </span>
                                <span class="font-mono text-[11px] text-emerald-700"><?php echo htmlspecialchars($ticket['rescue_verified_at'] ?? ''); ?></span>
                            </div>
                        <?php elseif ((int)($ticket['rescue_verified'] ?? 0) === 2): ?>
                            <div class="p-3 bg-rose-50 border border-rose-300 rounded-2xl flex items-center justify-between text-xs text-rose-900">
                                <span class="flex items-center gap-2 font-bold">
                                    <span>❌</span> Đơn cứu hộ đã bị bác bỏ do vi phạm quy chế hoặc không đủ căn cứ.
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- THẨM QUYỀN ADMIN: DUYỆT MỞ KHÓA & RESET PASS (CHỈ KHI KTV ĐÃ DUYỆT) -->
                    <?php if ($userRole === 'admin' && $ticket['status'] !== 'closed'): ?>
                        <?php if ((int)($ticket['rescue_verified'] ?? 0) === 1): ?>
                            <div class="p-3.5 bg-purple-50 border-2 border-purple-300 rounded-2xl space-y-2 text-xs">
                                <span class="font-black text-purple-950 flex items-center gap-1.5 text-xs">
                                    <span>⚡</span> Thẩm Quyền Admin: Phê Duyệt Cứu Hộ & Mở Khóa Tài Khoản (KTV Đã Duyệt)
                                </span>
                                <p class="text-[11.5px] text-purple-900">
                                    Kỹ thuật viên đã kiểm tra xác minh hồ sơ/thẻ SV hợp lệ. Admin có thẩm quyền đặt lại mật khẩu về mặc định (123456) và mở khóa tài khoản ngay lập tức:
                                </p>
                                <div class="flex items-center gap-2 flex-wrap pt-1">
                                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn MỞ KHÓA tài khoản và đặt mật khẩu về 123456?');">
                                        <input type="hidden" name="action" value="admin_unlock_account" />
                                        <button type="submit" class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center gap-1.5 cursor-pointer">
                                            <span>🔓</span> Phê Duyệt Mở Khóa & Reset Mật Khẩu (123456)
                                        </button>
                                    </form>
                                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn TỪ CHỐI đơn cứu hộ này?');">
                                        <input type="hidden" name="action" value="admin_reject_rescue" />
                                        <button type="submit" class="px-3 py-2 bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                                            <span>🔒</span> Từ Chối & Giữ Khóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php elseif ((int)($ticket['rescue_verified'] ?? 0) === 2): ?>
                            <div class="p-3 bg-rose-50 border border-rose-300 rounded-2xl flex items-center justify-between text-xs text-rose-900">
                                <span class="flex items-center gap-2 font-bold">
                                    <span>❌</span> Đơn cứu hộ đã bị KTV bác bỏ. Admin chỉ xem lưu trữ.
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="p-3 bg-amber-50 border border-amber-300 rounded-2xl text-xs text-amber-900 flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2">
                                    <span>⏳</span>
                                    <span><strong>Chế độ Admin (Chỉ Xem):</strong> Đơn cứu hộ đang chờ Kỹ thuật viên (KTV) tiếp nhận kiểm tra CMND/Thẻ SV và duyệt xác thực trước. Sau khi KTV duyệt, Admin mới có quyền phê duyệt mở khóa tài khoản.</span>
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
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
            $canShowCsatBox = (!empty($currentRating) || (in_array($ticket['status'], ['resolved', 'closed']) && $isRequester));
            ?>
            <?php if ($canShowCsatBox): ?>
                <div id="csat-box" class="bg-amber-50/40 border border-amber-200/90 rounded-3xl p-6 shadow-sm space-y-4">
                    
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-amber-500 text-xl font-bold">🌟</span>
                            <h3 class="text-xs sm:text-sm font-black text-amber-950 uppercase tracking-wide">
                                NGHIỆM THU KẾT QUẢ XỬ LÝ & ĐÁNH GIÁ CSAT (1 - 5 SAO)
                            </h3>
                        </div>
                        <?php if ($currentRating && $isRequester): ?>
                            <button type="button" onclick="document.getElementById('editRatingContainer').classList.toggle('hidden')" 
                                    class="px-3.5 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-950 border border-amber-300 rounded-xl font-bold transition flex items-center gap-1.5 text-xs shadow-2xs">
                                <span>✏️</span> Thay Đổi / Chấm Lại Đánh Giá
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($currentRating): ?>
                        <div class="p-4 bg-white/95 border border-amber-200 rounded-2xl flex items-center justify-between flex-wrap gap-2 text-xs">
                            <div>
                                <span class="font-bold text-slate-800">
                                    <?php echo $isRequester ? 'Đánh giá của bạn:' : 'Đánh giá từ người gửi (' . htmlspecialchars($currentRating['user_name'] ?? 'Người dùng') . '):'; ?>
                                </span>
                                <span class="text-amber-500 font-bold ml-1.5 font-mono text-sm">
                                    <?php echo str_repeat('★', (int)$currentRating['score']) . str_repeat('☆', 5 - (int)$currentRating['score']); ?>
                                    (<?php echo (int)$currentRating['score']; ?>/5 sao)
                                </span>
                                <?php if (!empty($currentRating['comment'])): ?>
                                    <p class="text-slate-600 mt-1 italic">"<?php echo htmlspecialchars($currentRating['comment']); ?>"</p>
                                <?php else: ?>
                                    <p class="text-slate-400 mt-0.5 text-[11px] italic">(Không có nhận xét thêm)</p>
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

                <?php 
                $isClosedOrRated = ($ticket['status'] === 'closed' || !empty($currentRating));
                ?>
                <?php if ($isClosedOrRated): ?>
                    <div class="pt-5 border-t border-slate-100">
                        <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl text-center space-y-2">
                            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-200 text-slate-600 text-lg">
                                🔒
                            </div>
                            <div class="font-bold text-slate-800 text-xs">Phiếu đã hoàn tất & khóa phản hồi</div>
                            <p class="text-[11px] text-slate-500 max-w-md mx-auto">
                                Người dùng đã đánh giá nghiệm thu dịch vụ. Cuộc hội thoại giữa Kỹ thuật viên và Người yêu cầu đã chính thức kết thúc và được lưu trữ an toàn.
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if ($userRole === 'admin'): ?>
                        <!-- Chế độ Admin: Chỉ xem và phân công KTV, không chat hay gửi phản hồi -->
                        <div class="pt-5 border-t border-slate-100 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center space-y-1">
                            <div class="text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5">
                                <span>🔒</span> Chế độ Quản Trị Viên (Admin): Chỉ Xem & Phân Công KTV
                            </div>
                            <p class="text-[11px] text-slate-500">
                                Admin theo dõi tiến độ và điều phối phân công kỹ thuật viên phụ trách, không tham gia gửi phản hồi hay trao đổi trực tiếp trên phiếu sự cố.
                            </p>
                        </div>
                    <?php else: ?>
                    <!-- Form gửi phản hồi mới cho KTV và Người dùng (SV, GV) -->
                    <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" enctype="multipart/form-data" class="pt-5 border-t border-slate-100 space-y-4 text-xs">
                        <input type="hidden" name="action" value="reply" />

                    <?php if (in_array($userRole, ['agent', 'admin'])): ?>
                        <!-- Dành cho Kỹ thuật viên / Quản trị viên: Có đầy đủ công cụ chuyển trạng thái & ghi chú nội bộ -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
                                <div>
                                    <span class="block text-[10.5px] font-bold text-slate-400 uppercase">Người gửi phản hồi:</span>
                                    <strong class="text-slate-900 font-bold text-xs"><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                                    <span class="text-indigo-600 font-mono text-[11px]">(<?php echo htmlspecialchars($currentUser['user_code']); ?>)</span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                    <?php echo $userRole === 'admin' ? 'Quản Trị Viên' : ($userRole === 'agent' ? 'Kỹ Thuật Viên IT' : 'Người Dùng'); ?>
                                </span>
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
                <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- CỘT PHẢI: THÔNG TIN TRẠNG THÁI & SLA (4 CỘT) -->
        <div class="md:col-span-4 space-y-6">

            <?php if ($userRole === 'agent' && empty($currentRating) && $ticket['status'] !== 'closed'): ?>
            <!-- Cập nhật trạng thái thủ công (Chỉ KTV) -->
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

                    <?php if (in_array($userRole, ['agent', 'admin'])): ?>
                        <?php if (in_array($ticket['status'], ['closed', 'resolved'])): ?>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 text-[11px] text-slate-500 italic flex items-center gap-1.5 bg-slate-50 p-2 rounded-xl">
                                <span>🔒</span> Phiếu đã hoàn tất & đóng. Quyền điều chuyển phân công đã khóa (chỉ xem & lưu lịch sử).
                            </div>
                        <?php else: ?>
                            <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="mt-2.5 pt-2 border-t border-slate-100 space-y-2">
                                <input type="hidden" name="action" value="assign_agent" />
                                <label class="block text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                    <span>🔄</span> Điều Chuyển Chuyên Môn KTV:
                                </label>
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

<!-- MODAL KTV BÁO CÁO SỰ CỐ DIỆN RỘNG (MAJOR INCIDENT) GỬI ADMIN -->
<div id="reportMajorIncidentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-rose-200">
        <div class="flex items-center justify-between pb-3 border-b border-rose-100">
            <h3 class="text-sm font-black text-rose-950 flex items-center gap-2">
                <span class="text-xl">🔥</span> Báo Cáo Sự Cố Diện Rộng Lên Ban Quản Trị (Admin)
            </h3>
            <button type="button" onclick="document.getElementById('reportMajorIncidentModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="ktv_report_major_incident" />

            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-rose-950 leading-relaxed text-[11.5px] space-y-1">
                <strong class="block font-black text-rose-900">⚠️ Lưu ý nghiệp vụ ITIL v4:</strong>
                Chức năng này dùng khi KTV phát hiện sự cố cấp cao ảnh hưởng diện rộng đến toàn trường hoặc toàn bộ sinh viên/giảng viên (ví dụ: sập máy chủ Canvas, lỗi xác thực SSO hàng loạt, nghẽn cổng QLĐT...). Hệ thống sẽ gửi thông báo khẩn cấp trực tiếp tới Quản Trị Viên (Admin) để kích hoạt kênh điều phối!
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Hệ thống / Ứng dụng bị ảnh hưởng: <span class="text-rose-500">*</span></label>
                <input type="text" name="system_affected" value="<?php echo htmlspecialchars($ticket['system_affected']); ?>" required 
                       class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 font-semibold" />
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Mô tả mức độ nghiêm trọng & Khuyến nghị KTV gửi Admin: <span class="text-rose-500">*</span></label>
                <textarea name="major_note" rows="3" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 font-medium" 
                          placeholder="Ví dụ: Đã kiểm tra server log, số lượng người dùng báo lỗi cùng lúc tăng đột biến. Đề nghị Admin phát cảnh báo diện rộng và liên hệ nhà mạng kiểm tra đường truyền..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('reportMajorIncidentModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white font-black rounded-xl transition shadow-md flex items-center gap-1.5 cursor-pointer">
                    <span>🚨</span> Gửi Báo Động Cho Admin Ngay
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

<!-- MODAL ĐÓNG PHIẾU DO TRÙNG LẶP -->
<div id="closeDuplicateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="text-slate-600 text-lg">📑</span> Đóng Phiếu Do Trùng Lặp Sự Cố
            </h3>
            <button type="button" onclick="document.getElementById('closeDuplicateModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="close_duplicate" />

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 leading-relaxed text-[11.5px]">
                Phiếu #<strong><?php echo htmlspecialchars($ticket['ticket_code']); ?></strong> sẽ được chuyển sang trạng thái <strong>Đã hoàn tất / Đóng</strong> với lý do trùng lặp sự cố đã gửi.
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Ghi chú lý do đóng trùng: <span class="text-rose-500">*</span></label>
                <textarea name="duplicate_note" rows="3" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 font-medium">Đóng phiếu do phát hiện sự cố trùng lặp với phiếu đã tiếp nhận trước đó trong hệ thống.</textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('closeDuplicateModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <span>📑</span> Xác Nhận Đóng Trùng Lặp
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ADMIN THU HỒI CẢNH BÁO KHẨN CẤP / SỰ CỐ DIỆN RỘNG (ĐÃ KHẮC PHỤC XONG) -->
<div id="revokeMajorIncidentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-emerald-200">
        <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
            <h3 class="text-sm font-black text-emerald-950 flex items-center gap-2">
                <span class="text-xl">✅</span> Thu Hồi Cảnh Báo Khẩn Cấp (Đã Khắc Phục Xong)
            </h3>
            <button type="button" onclick="document.getElementById('revokeMajorIncidentModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none cursor-pointer">&times;</button>
        </div>

        <form method="POST" action="show.php?id=<?php echo $ticket['id']; ?>&view_as=<?php echo $viewAs; ?>" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="admin_revoke_major_incident" />

            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-950 leading-relaxed text-[11.5px] space-y-1">
                <strong class="block font-black text-emerald-900">📌 Xác nhận thu hồi sự cố diện rộng:</strong>
                <p>Hệ thống: <strong><?php echo htmlspecialchars($ticket['system_affected']); ?></strong> (Phiếu: <code><?php echo htmlspecialchars($ticket['ticket_code']); ?></code>).</p>
                <p class="text-slate-600">Khi Admin xác nhận: Cờ báo động diện rộng sẽ được thu hồi; Mức ưu tiên tự động hạ về Bình thường; Toàn thể Sinh viên & Giảng viên sẽ nhận thông báo hệ thống đã trở lại hoạt động bình thường.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Ghi chú khắc phục & Báo cáo kết quả xử lý: <span class="text-slate-400">(Tùy chọn)</span></label>
                <textarea name="restore_note" rows="3" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 font-medium" 
                          placeholder="Ví dụ: Đội ngũ kỹ thuật đã hoàn tất khắc phục sự cố máy chủ, đường truyền mạng đã hoạt động ổn định 100%..."></textarea>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-1">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 text-xs">
                    <input type="checkbox" name="close_ticket" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                    <span>Đồng thời chuyển trạng thái phiếu sang <strong>Đã giải quyết (Resolved)</strong></span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('revokeMajorIncidentModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-black rounded-xl transition shadow-md flex items-center gap-1.5 cursor-pointer">
                    <span>✅</span> Xác Nhận Thu Hồi & Khắc Phục Xong
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

    // Tự động mở modal thu hồi cảnh báo khẩn cấp nếu có tham số open_revoke=1
    <?php if (!empty($_GET['open_revoke']) && $userRole === 'admin' && !empty($ticket['is_major_incident'])): ?>
    const revokeModal = document.getElementById('revokeMajorIncidentModal');
    if (revokeModal) {
        revokeModal.classList.remove('hidden');
    }
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

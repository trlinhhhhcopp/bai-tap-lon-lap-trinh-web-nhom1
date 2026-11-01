<?php
/**
 * ==============================================================================
 * DỊCH VỤ TRUNG TÂM THÔNG BÁO THỜI GIAN THỰC (NOTIFICATION SERVICE)
 * Hệ Thống Helpdesk CNTT Trực Tuyến - Đại Học Thủ Đô Hà Nội
 * Hỗ trợ thông báo đa vai trò: Sinh viên, Giảng viên, Kỹ thuật viên, Quản trị viên
 * ==============================================================================
 */

require_once __DIR__ . '/../../config/database.php';

class NotificationService
{
    /**
     * Gửi thông báo đến 1 người dùng cụ thể
     */
    public static function send(int $userId, string $title, string $content, ?string $link = null, string $type = 'system', string $role = ''): bool
    {
        if ($userId <= 0) {
            return false;
        }

        try {
            $stmt = db()->prepare("
                INSERT INTO notifications (user_id, role, title, content, link, type, is_read, created_at)
                VALUES (:user_id, :role, :title, :content, :link, :type, 0, NOW())
            ");
            return $stmt->execute([
                ':user_id' => $userId,
                ':role'    => $role ?: 'user',
                ':title'   => $title,
                ':content' => $content,
                ':link'    => $link,
                ':type'    => $type,
            ]);
        } catch (Throwable $e) {
            error_log("[NotificationService::send] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi thông báo tới toàn bộ người dùng của một vai trò (ví dụ: tất cả admin, hoặc tất cả sinh viên lớp)
     * Hỗ trợ role: 'student' (chỉ SV, loại trừ GV), 'lecturer' (chỉ GV), 'agent', 'admin', 'requester'
     */
    public static function sendToRole(string $role, string $title, string $content, ?string $link = null, string $type = 'system', ?int $excludeUserId = null): int
    {
        try {
            $params = [];
            if ($role === 'student') {
                $sql = "SELECT id FROM users WHERE role = 'requester' AND (user_type = 'student' OR user_type IS NULL OR user_type = '') AND is_active = 1";
            } elseif ($role === 'lecturer') {
                $sql = "SELECT id FROM users WHERE user_type = 'lecturer' AND is_active = 1";
            } else {
                $sql = "SELECT id FROM users WHERE role = :role AND is_active = 1";
                $params[':role'] = $role;
            }

            if ($excludeUserId && $excludeUserId > 0) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeUserId;
            }

            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $count = 0;
            foreach ($users as $uid) {
                if (self::send((int)$uid, $title, $content, $link, $type, $role)) {
                    $count++;
                }
            }
            return $count;
        } catch (Throwable $e) {
            error_log("[NotificationService::sendToRole] Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy danh sách thông báo chưa đọc của người dùng
     */
    public static function getUnread(int $userId, int $limit = 10): array
    {
        if ($userId <= 0) return [];
        try {
            $stmt = db()->prepare("
                SELECT * FROM notifications 
                WHERE user_id = :user_id AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Lấy danh sách tất cả thông báo gần đây của người dùng
     */
    public static function getAll(int $userId, int $limit = 20): array
    {
        if ($userId <= 0) return [];
        try {
            $stmt = db()->prepare("
                SELECT * FROM notifications 
                WHERE user_id = :user_id 
                ORDER BY is_read ASC, created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Đếm số lượng thông báo chưa đọc
     */
    public static function countUnread(int $userId): int
    {
        if ($userId <= 0) return 0;
        try {
            $stmt = db()->prepare("
                SELECT COUNT(*) FROM notifications 
                WHERE user_id = :user_id AND is_read = 0
            ");
            $stmt->execute([':user_id' => $userId]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Đánh dấu 1 thông báo đã đọc
     */
    public static function markAsRead(int $id, int $userId): bool
    {
        if ($id <= 0 || $userId <= 0) return false;
        try {
            $stmt = db()->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE id = :id AND user_id = :user_id
            ");
            return $stmt->execute([':id' => $id, ':user_id' => $userId]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Đánh dấu tất cả thông báo của người dùng đã đọc
     */
    public static function markAllAsRead(int $userId): bool
    {
        if ($userId <= 0) return false;
        try {
            $stmt = db()->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = :user_id AND is_read = 0
            ");
            return $stmt->execute([':user_id' => $userId]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Lấy thông báo nổi bật nhất để hiển thị Banner ở đầu trang sau phần tổng quan
     */
    public static function getLatestBanner(int $userId): ?array
    {
        if ($userId <= 0) return null;
        try {
            // Ưu tiên lấy thông báo chưa đọc mới nhất
            $stmt = db()->prepare("
                SELECT * FROM notifications 
                WHERE user_id = :user_id AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([':user_id' => $userId]);
            $banner = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($banner) {
                return $banner;
            }

            // Nếu không có thông báo chưa đọc, lấy thông báo mới nhất trong 24 giờ qua
            $stmt = db()->prepare("
                SELECT * FROM notifications 
                WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([':user_id' => $userId]);
            $recent = $stmt->fetch(PDO::FETCH_ASSOC);
            return $recent ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Tự động khởi tạo thông báo sinh động từ các sự kiện thực tế trong hệ thống
     * nếu người dùng chưa có thông báo nào trong bảng
     */
    public static function ensureUserHasInitialNotifications(array $user): void
    {
        $userId   = (int)($user['id'] ?? 0);
        $role     = $user['role'] ?? '';
        $userType = $user['user_type'] ?? '';

        if ($userId <= 0) return;

        // Nếu người dùng đã có thông báo thì không cần khởi tạo lại (riêng Admin luôn rà soát việc cần làm mới)
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            if ((int)$stmt->fetchColumn() > 0) {
                if ($role === 'admin') {
                    self::checkAndNotifyAdminTasks($userId);
                }
                return;
            }
        } catch (Throwable $e) {
            return;
        }

        // Tự động quét các dữ liệu thực tế tương ứng với vai trò của user
        try {
            if ($role === 'requester' && $userType === 'student') {
                // 1. Sinh viên: Kiểm tra phiếu hỗ trợ đã có phản hồi từ KTV
                $tStmt = db()->prepare("
                    SELECT t.id, t.ticket_code, t.title, t.status 
                    FROM tickets t 
                    WHERE t.requester_id = :user_id 
                    ORDER BY t.id DESC LIMIT 2
                ");
                $tStmt->execute([':user_id' => $userId]);
                $tickets = $tStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tickets as $tk) {
                    if ($tk['status'] === 'resolved' || $tk['status'] === 'closed') {
                        self::send(
                            $userId,
                            "Kỹ thuật viên đã xử lý xong phiếu #{$tk['ticket_code']}",
                            "Sự cố '{$tk['title']}' đã có giải pháp kỹ thuật. Mời bạn vào kiểm tra và nghiệm thu đánh giá chất lượng CSAT!",
                            "tickets/show.php?id={$tk['id']}",
                            'ticket_reply',
                            'student'
                        );
                    } else {
                        self::send(
                            $userId,
                            "Phiếu hỗ trợ #{$tk['ticket_code']} đang được tiếp nhận",
                            "Đội ngũ kỹ thuật viên Helpdesk CNTT đang trong quá trình kiểm tra sự cố '{$tk['title']}'.",
                            "tickets/show.php?id={$tk['id']}",
                            'ticket_reply',
                            'student'
                        );
                    }
                }

                // 2. Bài tập mới từ giảng viên (liên kết đến bài học thực tế)
                $latestLesson = db()->query("
                    SELECT id, lesson_number, title, course_id 
                    FROM course_lessons 
                    ORDER BY id DESC LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);
                if ($latestLesson) {
                    self::send(
                        $userId,
                        "📝 Bài tập thực hành Buổi {$latestLesson['lesson_number']}",
                        "Đã cập nhật đề bài cho Buổi {$latestLesson['lesson_number']}: '{$latestLesson['title']}'. Bạn hãy vào Cổng Học Phần để xem chi tiết và nộp bài!",
                        "student/index.php?action=courses",
                        'new_assignment',
                        'student'
                    );
                }

            } elseif ($userType === 'lecturer') {
                // 1. Giảng viên: Lớp học phần được phân công
                $caStmt = db()->prepare("
                    SELECT ca.id, c.name AS course_name, cl.name AS class_name 
                    FROM course_assignments ca 
                    JOIN courses c ON ca.course_id = c.id 
                    JOIN classes cl ON ca.class_id = cl.id 
                    WHERE ca.lecturer_id = :user_id 
                    LIMIT 2
                ");
                $caStmt->execute([':user_id' => $userId]);
                $assignments = $caStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($assignments as $a) {
                    self::send(
                        $userId,
                        "Phòng Đào tạo phân công giảng dạy lớp {$a['class_name']}",
                        "Bạn đã được phân công phụ trách giảng dạy học phần '{$a['course_name']}' cho lớp học phần {$a['class_name']}.",
                        "assignments/index.php",
                        'class_assigned',
                        'lecturer'
                    );
                }

                // 2. Phiếu sự cố sinh viên cần GV xem xét gia hạn (chỉ gửi khi có phiếu thật được KTV duyệt)
                $verifiedInc = db()->prepare("
                    SELECT t.id, t.ticket_code, t.title 
                    FROM tickets t 
                    WHERE t.lesson_id IS NOT NULL 
                      AND t.it_verified = 1 
                      AND (t.lecturer_decision IS NULL OR t.lecturer_decision = 'pending') 
                      AND t.status NOT IN ('closed', 'resolved') 
                      AND (t.course_id IN (SELECT ca.course_id FROM course_assignments ca WHERE ca.lecturer_id = :lid) 
                           OR t.class_id IN (SELECT ca2.class_id FROM course_assignments ca2 WHERE ca2.lecturer_id = :lid2))
                    ORDER BY t.id DESC LIMIT 1
                ");
                $verifiedInc->execute([':lid' => $userId, ':lid2' => $userId]);
                $vRow = $verifiedInc->fetch(PDO::FETCH_ASSOC);
                if ($vRow) {
                    self::send(
                        $userId,
                        "KTV đã xác nhận sự cố nộp bài #{$vRow['ticket_code']}",
                        "Kỹ thuật viên đã kiểm tra và xác nhận sự cố kỹ thuật '{$vRow['title']}'. Mời Thầy/Cô vào xem xét gia hạn nộp bài.",
                        "tickets/show.php?id={$vRow['id']}",
                        'deadline_extended',
                        'lecturer'
                    );
                }

                // 3. Phiếu hỗ trợ do chính Thầy/Cô tạo gửi Helpdesk CNTT
                $gvTkStmt = db()->prepare("
                    SELECT id, ticket_code, title, status 
                    FROM tickets 
                    WHERE requester_id = :user_id 
                    ORDER BY id DESC LIMIT 2
                ");
                $gvTkStmt->execute([':user_id' => $userId]);
                $gvTickets = $gvTkStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($gvTickets as $gtk) {
                    if (in_array($gtk['status'], ['resolved', 'closed'])) {
                        self::send(
                            $userId,
                            "KTV đã xử lý xong phiếu hỗ trợ #{$gtk['ticket_code']}",
                            "Sự cố '{$gtk['title']}' của Thầy/Cô đã có giải pháp kỹ thuật. Kính mời Thầy/Cô vào kiểm tra kết quả.",
                            "tickets/show.php?id={$gtk['id']}",
                            'ticket_reply',
                            'lecturer'
                        );
                    } else {
                        self::send(
                            $userId,
                            "Phiếu sự cố #{$gtk['ticket_code']} đang được xử lý",
                            "Kỹ thuật viên chuyên trách đang kiểm tra và giải quyết sự cố '{$gtk['title']}' của Thầy/Cô.",
                            "tickets/show.php?id={$gtk['id']}",
                            'ticket_reply',
                            'lecturer'
                        );
                    }
                }

            } elseif ($role === 'agent') {
                // Kỹ thuật viên: Phiếu mới được giao
                $agentTickets = db()->prepare("
                    SELECT id, ticket_code, title 
                    FROM tickets 
                    WHERE assigned_to = :user_id AND status IN ('new', 'in_progress') 
                    ORDER BY id DESC LIMIT 2
                ");
                $agentTickets->execute([':user_id' => $userId]);
                $tList = $agentTickets->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tList as $at) {
                    self::send(
                        $userId,
                        "Phiếu sự cố mới được phân công: #{$at['ticket_code']}",
                        "Bạn vừa được điều phối xử lý phiếu sự cố '{$at['title']}'. Vui lòng kiểm tra và xử lý theo cam kết SLA.",
                        "tickets/show.php?id={$at['id']}",
                        'ticket_assigned',
                        'agent'
                    );
                }

                // Chỉ thông báo cứu hộ nếu thực tế có đơn cứu hộ thật đang chờ xử lý
                $pendingRescue = db()->query("
                    SELECT id, ticket_code, title 
                    FROM tickets 
                    WHERE is_guest_ticket = 1 AND status NOT IN ('closed') 
                    ORDER BY id DESC LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);
                if ($pendingRescue) {
                    self::send(
                        $userId,
                        "🚑 Đơn cứu hộ cần KTV xử lý #{$pendingRescue['ticket_code']}",
                        "Trạm cứu hộ trực tuyến đang có đơn cứu hộ chưa hoàn tất (#{$pendingRescue['ticket_code']}). Vui lòng kiểm tra và xác thực hồ sơ!",
                        "tickets/show.php?id={$pendingRescue['id']}",
                        'guest_rescue',
                        'agent'
                    );
                }

            } elseif ($role === 'admin') {
                // Tự động kiểm tra 4 việc cần làm cấp bách của Admin:
                // Sự cố diện rộng, KTV chuyển đơn cứu hộ, KTV báo cáo SV vi phạm, KTV bị tràn tải
                self::checkAndNotifyAdminTasks($userId);
            }
        } catch (Throwable $e) {
            error_log("[NotificationService::ensureUserHasInitialNotifications] Error: " . $e->getMessage());
        }
    }

    /**
     * Tự động quét và phát hiện các Việc Cần Làm cấp bách của Admin:
     * 1. Sự cố diện rộng (Major Incident / Critical)
     * 2. KTV chuyển phiếu cứu hộ sang (Pending Rescue Approvals)
     * 3. KTV báo cáo sinh viên vi phạm (Pending Violations)
     * 4. KTV bị tràn tải / có phiếu quá hạn SLA (Agent Overflow)
     */
    public static function checkAndNotifyAdminTasks(int $adminId): void
    {
        if ($adminId <= 0) return;
        $pdo = db();

        try {
            // 1. SỰ CỐ DIỆN RỘNG (MAJOR INCIDENT / CRITICAL)
            $stmtMajor = $pdo->query("
                SELECT COUNT(*) FROM tickets 
                WHERE (priority_id = 1 OR title LIKE '%toàn trường%' OR title LIKE '%sập mạng%' OR title LIKE '%diện rộng%') 
                  AND status IN ('new', 'in_progress')
            ");
            $majorCount = (int)($stmtMajor ? $stmtMajor->fetchColumn() : 0);
            if ($majorCount > 0) {
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM notifications 
                    WHERE user_id = :uid AND type = 'major_incident' AND created_at >= DATE_SUB(NOW(), INTERVAL 3 HOUR)
                ");
                $stmtCheck->execute([':uid' => $adminId]);
                if ((int)$stmtCheck->fetchColumn() === 0) {
                    self::send(
                        $adminId,
                        "🔥 VIỆC CẦN LÀM: Phát hiện {$majorCount} sự cố diện rộng / khẩn cấp",
                        "Hệ thống ghi nhận sự cố cấp cao ảnh hưởng diện rộng. Cần Admin kích hoạt kênh điều phối khẩn cấp và đôn đốc KTV xử lý!",
                        "tickets/index.php?kw=toàn+trường",
                        'major_incident',
                        'admin'
                    );
                }
            }

            // 2. KTV CHUYỂN PHIẾU CỨU HỘ SANG (PENDING RESCUE APPROVALS)
            $stmtRescue = $pdo->query("
                SELECT COUNT(*) FROM tickets 
                WHERE rescue_verified = 1 AND status != 'closed'
            ");
            $rescuePendingCount = (int)($stmtRescue ? $stmtRescue->fetchColumn() : 0);
            if ($rescuePendingCount > 0) {
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM notifications 
                    WHERE user_id = :uid AND type = 'guest_rescue' AND is_read = 0
                ");
                $stmtCheck->execute([':uid' => $adminId]);
                if ((int)$stmtCheck->fetchColumn() === 0) {
                    self::send(
                        $adminId,
                        "🚑 VIỆC CẦN LÀM: {$rescuePendingCount} đơn cứu hộ chờ Admin phê duyệt",
                        "Kỹ thuật viên đã kiểm tra và xác thực hồ sơ hợp lệ. Cần Admin xem xét duyệt mở khóa tài khoản và đặt lại mật khẩu cho sinh viên!",
                        "tickets/index.php?warning=guest",
                        'guest_rescue',
                        'admin'
                    );
                }
            }

            // 3. KTV BÁO CÁO SINH VIÊN VI PHẠM (PENDING VIOLATIONS)
            $stmtViolations = $pdo->query("
                SELECT COUNT(*) FROM tickets 
                WHERE is_spam = 1 AND status IN ('new', 'in_progress', 'resolved')
            ");
            $violationCount = (int)($stmtViolations ? $stmtViolations->fetchColumn() : 0);
            if ($violationCount > 0) {
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM notifications 
                    WHERE user_id = :uid AND type = 'violation_report' AND is_read = 0
                ");
                $stmtCheck->execute([':uid' => $adminId]);
                if ((int)$stmtCheck->fetchColumn() === 0) {
                    self::send(
                        $adminId,
                        "🚨 VIỆC CẦN LÀM: {$violationCount} trường hợp SV bị KTV báo cáo vi phạm",
                        "Kỹ thuật viên đã lập biên bản vi phạm quy chế hoặc spam trạm cứu hộ. Cần Admin thẩm định xử lý kỷ luật / khóa tài khoản!",
                        "tickets/index.php?warning=violation",
                        'violation_report',
                        'admin'
                    );
                }
            }

            // 4. KTV BỊ TRÀN TẢI / PHIẾU QUÁ HẠN SLA (AGENT OVERFLOW - ĐẠT HOẶC VƯỢT NGƯỠNG MAX_CAPACITY 15 PHIẾU)
            $stmtOverflow = $pdo->query("
                SELECT u.name, u.max_capacity, COUNT(t.id) as active_tickets 
                FROM tickets t 
                JOIN users u ON t.assigned_to = u.id 
                WHERE t.status IN ('new', 'in_progress') 
                GROUP BY t.assigned_to, u.name, u.max_capacity 
                HAVING active_tickets >= COALESCE(u.max_capacity, 15)
            ");
            $overflowAgents = $stmtOverflow ? $stmtOverflow->fetchAll(PDO::FETCH_ASSOC) : [];
            if (!empty($overflowAgents)) {
                $agentNames = implode(', ', array_map(fn($a) => $a['name'] . " ({$a['active_tickets']}/" . ($a['max_capacity'] ?: 15) . " phiếu)", $overflowAgents));
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM notifications 
                    WHERE user_id = :uid AND type = 'agent_overflow' AND created_at >= DATE_SUB(NOW(), INTERVAL 3 HOUR)
                ");
                $stmtCheck->execute([':uid' => $adminId]);
                if ((int)$stmtCheck->fetchColumn() === 0) {
                    self::send(
                        $adminId,
                        "⚠️ VIỆC CẦN LÀM: Cảnh báo KTV bị tràn tải",
                        "Hệ thống phát hiện KTV đang nhận quá tải: {$agentNames}. Cần Admin điều chuyển bớt phiếu sang KTV khác để đảm bảo SLA!",
                        "tickets/index.php?warning=sla",
                        'agent_overflow',
                        'admin'
                    );
                }
            }
        } catch (Throwable $e) {
            error_log("[NotificationService::checkAndNotifyAdminTasks] Error: " . $e->getMessage());
        }
    }
}


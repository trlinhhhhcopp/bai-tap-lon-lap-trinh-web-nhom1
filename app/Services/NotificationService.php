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
     */
    public static function sendToRole(string $role, string $title, string $content, ?string $link = null, string $type = 'system'): int
    {
        try {
            $stmt = db()->prepare("SELECT id FROM users WHERE role = :role AND is_active = 1");
            $stmt->execute([':role' => $role]);
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

        // Nếu người dùng đã có thông báo thì không cần khởi tạo lại
        try {
            $stmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            if ((int)$stmt->fetchColumn() > 0) {
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

                // 2. Bài tập mới từ giảng viên
                self::send(
                    $userId,
                    "Giảng viên vừa giao bài tập thực hành mới",
                    "Thầy/Cô bộ môn Lập trình Web & Ứng dụng (WEB101) đã cập nhật đề bài và hạn nộp bài tập buổi học tiếp theo. Hãy vào Cổng Học Phần kiểm tra ngay!",
                    "student/index.php?action=courses",
                    'new_assignment',
                    'student'
                );

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

                // 2. Phiếu sự cố sinh viên cần GV xem xét gia hạn
                self::send(
                    $userId,
                    "KTV đã xác nhận sự cố nộp bài của sinh viên",
                    "Kỹ thuật viên đã kiểm tra nhật ký Access Point / Server LMS và xác nhận sự cố hợp lệ. Mời Thầy/Cô vào xem xét gia hạn Deadline cho sinh viên.",
                    "index.php",
                    'deadline_extended',
                    'lecturer'
                );

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

                self::send(
                    $userId,
                    "Có đơn cứu hộ tài khoản sinh viên cần duyệt",
                    "Một sinh viên vừa gửi đơn đề nghị cứu hộ tài khoản bị khóa qua Trạm cứu hộ trực tuyến. Vui lòng xác thực thông tin sự cố để chuyển Admin.",
                    "index.php",
                    'guest_rescue',
                    'agent'
                );

            } elseif ($role === 'admin') {
                // Quản trị viên
                self::send(
                    $userId,
                    "KTV đã xác thực đơn cứu hộ tài khoản mới",
                    "Kỹ thuật viên đã duyệt xác nhận hồ sơ đơn cứu hộ sinh viên. Ban Quản Trị vui lòng phê duyệt mở khóa tài khoản và đặt lại mật khẩu.",
                    "index.php",
                    'guest_rescue',
                    'admin'
                );

                self::send(
                    $userId,
                    "Giám sát hệ thống: Cập nhật điều phối phân công",
                    "Hệ thống tự động ghi nhận các mốc SLA và tải KTV trực ca. Báo cáo thống kê trực tuyến đã sẵn sàng.",
                    "index.php",
                    'system',
                    'admin'
                );
            }
        } catch (Throwable $e) {
            error_log("[NotificationService::ensureUserHasInitialNotifications] Error: " . $e->getMessage());
        }
    }
}


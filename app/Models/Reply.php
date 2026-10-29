<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD) - KHOA CÔNG NGHỆ THÔNG TIN
 * CHỦ ĐỀ 9: HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * 
 * PHÂN HỆ: CORE ARCHITECTURE & AGENT WORKSPACE
 * THÀNH VIÊN PHỤ TRÁCH ĐỘC QUYỀN: PHẠM THỊ THANH NGÂN (TRƯỞNG NHÓM - MSSV: 224001736)
 * BẢNG CSDL QUẢN TRỊ: ticket_replies
 * ==============================================================================
 */
class Reply {
    /**
     * Lấy danh sách phản hồi theo ticket_id
     * - Nếu $canViewInternal = false (Sinh viên/Giảng viên): Tự động lọc chỉ lấy tin nhắn công khai (is_internal = 0)
     * - Nếu $canViewInternal = true (KTV/Admin): Xem được cả trao đổi công khai và ghi chú nội bộ (is_internal = 1)
     */
    public static function getByTicketId(int $ticketId, bool $canViewInternal = false): array {
        $pdo = Database::getConnection();
        $sql = "SELECT r.*, 
                       u.name AS user_name, 
                       u.user_code AS user_code,
                       u.role AS user_role,
                       u.user_type AS user_type
                FROM ticket_replies r
                JOIN users u ON r.user_id = u.id
                WHERE r.ticket_id = :ticket_id";

        if (!$canViewInternal) {
            $sql .= " AND r.is_internal = 0";
        }

        $sql .= " ORDER BY r.created_at ASC, r.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    /**
     * Tạo một phản hồi mới hoặc ghi chú nội bộ KTV
     */
    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO ticket_replies (ticket_id, user_id, message, attachment_path, is_internal, created_at)
                VALUES (:ticket_id, :user_id, :message, :attachment_path, :is_internal, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ticket_id'        => (int)$data['ticket_id'],
            ':user_id'          => (int)$data['user_id'],
            ':message'          => trim($data['message']),
            ':attachment_path'  => $data['attachment_path'] ?? null,
            ':is_internal'      => !empty($data['is_internal']) ? 1 : 0,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Đếm tổng số lượng phản hồi của một phiếu hỗ trợ
     */
    public static function countByTicketId(int $ticketId, bool $canViewInternal = false): int {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM ticket_replies WHERE ticket_id = :ticket_id";
        if (!$canViewInternal) {
            $sql .= " AND is_internal = 0";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Tìm chi tiết một phản hồi theo ID
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $sql = "SELECT r.*, u.name AS user_name, u.role AS user_role 
                FROM ticket_replies r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Xóa một phản hồi (dành cho Admin quản trị hoặc tác giả)
     */
    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM ticket_replies WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}


<?php
/**
 * Front Controller – Student Module
 * Phần của: Nguyễn Ánh Nhi
 */
session_start();
require_once __DIR__ . '/Controllers/Student/RatingController.php';
require_once __DIR__ . '/Controllers/Student/TicketController.php';

$action = $_GET['action'] ?? 'tickets'; // mặc định vào danh sách phiếu

// Giả lập sinh viên đang đăng nhập (để demo)
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id'   => 13,                          // Phạm Thị Thanh Ngân
        'name' => 'Phạm Thị Thanh Ngân',
        'role' => 'requester'
    ];
}

match ($action) {
    // ===== RATING (CSAT) =====
    'ratings'       => (new RatingController())->index(),
    'rating_create' => (new RatingController())->create(),
    'rating_edit'   => (new RatingController())->edit(),
    'rating_delete' => (new RatingController())->delete(),

    // ===== TICKET (Báo sự cố) =====
    'tickets'       => (new TicketController())->index(),
    'ticket_create' => (new TicketController())->create(),
    'ticket_store'  => (new TicketController())->store(),
    'ticket_show'   => (new TicketController())->show(),

    default         => (new TicketController())->index(),
};
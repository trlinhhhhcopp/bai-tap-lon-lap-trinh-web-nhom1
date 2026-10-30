<?php
/**
 * Front Controller – Phân hệ Đánh giá dịch vụ CSAT & Sinh viên
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc đăng nhập
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../app/Controllers/Student/RatingController.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'ratings');

match ($action) {
    // ===== RATING (CSAT) =====
    'ratings', 'index' => (new RatingController())->index(),
    'rating_create', 'create' => (new RatingController())->create(),
    'rating_edit', 'edit'   => (new RatingController())->edit(),
    'rating_delete', 'delete' => (new RatingController())->delete(),

    // ===== ĐIỀU HƯỚNG TẬP TRUNG SANG PHÂN HỆ VÉ DUY NHẤT =====
    'tickets'       => header('Location: ../tickets/index.php') . exit,
    'ticket_create' => header('Location: ../tickets/create.php') . exit,
    'ticket_show'   => header('Location: ../tickets/show.php?id=' . (int)($_GET['id'] ?? 1)) . exit,

    default         => (new RatingController())->index(),
};

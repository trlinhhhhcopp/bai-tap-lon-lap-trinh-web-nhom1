<?php
/**
 * Front Controller – Student Module (CSAT Ratings & Tickets)
 * Phân hệ Sinh viên
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc đăng nhập
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/Controllers/Student/RatingController.php';

$action = $_GET['action'] ?? 'ratings';

match ($action) {
    // ===== RATING (CSAT) =====
    'ratings'       => (new RatingController())->index(),
    'rating_create' => (new RatingController())->create(),
    'rating_edit'   => (new RatingController())->edit(),
    'rating_delete' => (new RatingController())->delete(),

    // ===== ĐIỀU HƯỚNG TẬP TRUNG SANG PHÂN HỆ VÉ DUY NHẤT =====
    'tickets'       => header('Location: ../tickets/index.php') . exit,
    'ticket_create' => header('Location: ../tickets/create.php') . exit,
    'ticket_show'   => header('Location: ../tickets/show.php?id=' . (int)($_GET['id'] ?? 1)) . exit,

    default         => (new RatingController())->index(),
};

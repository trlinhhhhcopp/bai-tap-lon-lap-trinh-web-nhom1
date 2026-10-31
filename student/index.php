<?php
/**
 * Front Controller – Phân hệ Sinh viên (Học phần & Đính kèm bài tập, Đánh giá CSAT)
 * Tuân thủ kiến trúc MVC chuẩn theo Slide Bài 6 (Front Controller + Router)
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
require_once __DIR__ . '/../app/Controllers/Student/CourseController.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'courses');

match ($action) {
    // ===== HỌC PHẦN & ĐÍNH KÈM BÀI TẬP CÁC BUỔI =====
    'courses'                  => (new CourseController())->index(),
    'course_detail'            => (new CourseController())->detail(),
    'course_submit'            => (new CourseController())->submitAssignment(),
    'course_delete_submission' => (new CourseController())->deleteSubmission(),
    'api_lessons'              => (new CourseController())->apiLessons(),

    // ===== RATING (CSAT) =====
    'ratings', 'index'         => (new RatingController())->index(),
    'rating_create', 'create'  => (new RatingController())->create(),
    'rating_edit', 'edit'      => (new RatingController())->edit(),
    'rating_delete', 'delete'  => (new RatingController())->delete(),

    // ===== ĐIỀU HƯỚNG TẬP TRUNG SANG PHÂN HỆ VÉ DUY NHẤT =====
    'tickets'                  => header('Location: ../tickets/index.php') . exit,
    'ticket_create'            => header('Location: ../tickets/create.php') . exit,
    'ticket_show'              => header('Location: ../tickets/show.php?id=' . (int)($_GET['id'] ?? 1)) . exit,

    default                    => (new CourseController())->index(),
};

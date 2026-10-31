<?php
/**
 * Front Controller: Phân hệ Giảng viên - Giao bài tập theo từng buổi học có hạn nộp
 * Tuân thủ kiến trúc MVC (Bài 6-8)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Controllers/Lecturer/LecturerLessonController.php';

$controller = new LecturerLessonController();
$action = $_POST['action'] ?? ($_GET['action'] ?? 'index');

match ($action) {
    'create'  => $controller->create(),
    'update'  => $controller->update(),
    'grade'   => $controller->grade(),
    'preview' => $controller->preview(),
    default   => $controller->index(),
};


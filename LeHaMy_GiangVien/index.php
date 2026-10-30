<?php
/**
 * Front Controller – CRUD course_assignments
 * Phân hệ Giảng viên
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc đăng nhập
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/Controllers/Lecturer/CourseAssignmentController.php';

$action = $_GET['action'] ?? 'index';
$controller = new CourseAssignmentController();

match ($action) {
    'create'      => $controller->create(),
    'edit'        => $controller->edit(),
    'delete'      => $controller->delete(),
    'export_csv'  => $controller->exportCsv(),
    'toggle_lock' => $controller->toggleLock(),
    'manage_catalog' => $controller->manageCatalog(),
    'add_class'   => $controller->addClass(),
    'add_course'  => $controller->addCourse(),
    'import_classes' => $controller->importClasses(),
    'import_courses' => $controller->importCourses(),
    'import_assignments' => $controller->importAssignments(),
    'download_template' => $controller->downloadTemplate(),
    default       => $controller->index(),
};
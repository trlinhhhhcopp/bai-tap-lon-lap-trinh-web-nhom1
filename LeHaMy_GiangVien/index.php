<?php
/**
 * Front Controller – CRUD course_assignments
 * Phần của: Lê Hà My
 */
session_start();
require_once __DIR__ . '/Controllers/Lecturer/CourseAssignmentController.php';

$action = $_GET['action'] ?? 'index';
$controller = new CourseAssignmentController();

match ($action) {
    'create' => $controller->create(),
    'edit'   => $controller->edit(),
    'delete' => $controller->delete(),
    default  => $controller->index(),
};
?>
<?php
/**
 * Front Controller – CRUD ticket_ratings (CSAT)
 * Phần của: Nguyễn Ánh Nhi
 */
session_start();
require_once __DIR__ . '/Controllers/Student/RatingController.php';

$action = $_GET['action'] ?? 'index';
$controller = new RatingController();

match ($action) {
    'create' => $controller->create(),
    'edit'   => $controller->edit(),
    'delete' => $controller->delete(),
    default  => $controller->index(),
};
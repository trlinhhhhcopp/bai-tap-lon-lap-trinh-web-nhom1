<?php

require_once __DIR__ . '/../app/LegacyRoute.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'courses';
$routes = [
    'courses' => '/student/courses',
    'course_detail' => '/student/courses/detail',
    'course_submit' => '/student/courses/submit',
    'course_delete_submission' => '/student/courses/delete-submission',
    'api_lessons' => '/student/courses/lessons',
    'ratings' => '/student/ratings',
    'index' => '/student/ratings',
    'rating_create' => '/student/ratings/create',
    'create' => '/student/ratings/create',
    'rating_edit' => '/student/ratings/edit',
    'edit' => '/student/ratings/edit',
    'rating_delete' => '/student/ratings/delete',
    'delete' => '/student/ratings/delete',
];

if (isset($routes[$action])) {
    dispatchLegacyRoute($routes[$action]);
}

if ($action === 'tickets') {
    header('Location: ../tickets/index.php');
    exit;
}
if ($action === 'ticket_create') {
    header('Location: ../tickets/create.php');
    exit;
}
if ($action === 'ticket_show') {
    header('Location: ../tickets/show.php?id=' . (int)($_GET['id'] ?? 1));
    exit;
}

dispatchLegacyRoute('/student/courses');

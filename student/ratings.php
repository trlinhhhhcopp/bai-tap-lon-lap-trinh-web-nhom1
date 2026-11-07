<?php

require_once __DIR__ . '/../app/LegacyRoute.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'ratings';
$route = match ($action) {
    'create', 'rating_create' => '/student/ratings/create',
    'edit', 'rating_edit' => '/student/ratings/edit',
    'delete', 'rating_delete' => '/student/ratings/delete',
    default => '/student/ratings',
};

dispatchLegacyRoute($route);

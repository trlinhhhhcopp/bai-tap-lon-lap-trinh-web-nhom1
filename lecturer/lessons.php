<?php

require_once __DIR__ . '/../app/LegacyRoute.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'index';
$route = match ($action) {
    'create' => '/lecturer/lessons/create',
    'update' => '/lecturer/lessons/update',
    'grade' => '/lecturer/lessons/grade',
    'preview' => '/lecturer/lessons/preview',
    'delete' => '/lecturer/lessons/delete',
    default => '/lecturer/lessons',
};

dispatchLegacyRoute($route);

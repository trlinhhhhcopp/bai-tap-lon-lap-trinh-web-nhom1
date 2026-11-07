<?php

require_once __DIR__ . '/../app/LegacyRoute.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'index';
$route = match ($action) {
    'create' => '/admin/holidays/create',
    'edit' => '/admin/holidays/edit',
    'delete' => '/admin/holidays/delete',
    default => '/admin/holidays',
};

dispatchLegacyRoute($route);

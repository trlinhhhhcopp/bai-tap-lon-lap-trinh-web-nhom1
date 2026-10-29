
<?php

require_once __DIR__ . '/Controllers/Admin/HolidayController.php';

$controllerName = $_GET['controller'] ?? 'holiday';
$action = $_GET['action'] ?? 'index';

if ($controllerName !== 'holiday') {
    http_response_code(404);
    exit('Không tìm thấy chức năng.');
}

$controller = new HolidayController();

$allowedActions = ['index', 'create', 'edit', 'delete'];

if (!in_array($action, $allowedActions, true)) {
    http_response_code(404);
    exit('Không tìm thấy thao tác.');
}

$controller->$action();

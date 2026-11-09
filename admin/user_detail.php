<?php
/**
 * Lớp chuyển tiếp tương thích (Legacy Adapter) -> Luồng 2 MVC
 * Điều hướng tự động về AdminUserController::detail()
 */
require_once __DIR__ . '/../app/LegacyRoute.php';

dispatchLegacyRoute('/admin/users/detail');

<?php
/**
 * View: Chi tiết Học phần & Danh sách các buổi học
 * Đã hợp nhất vào giao diện 1 trang duy nhất: views/student/courses/index.php
 * File này hoạt động như một lớp chuyển tiếp tương thích ngược an toàn.
 */
require_once __DIR__ . '/../../../config/database.php';
$appBase = get_app_base_url();
$courseId = max(1, (int)($_GET['course_id'] ?? ($_GET['id'] ?? 1)));
header("Location: {$appBase}student/courses?course_id={$courseId}#course-workspace");
exit;

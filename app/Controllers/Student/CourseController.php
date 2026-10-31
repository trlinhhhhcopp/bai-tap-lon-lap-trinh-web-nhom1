<?php
/**
 * Controller: CourseController
 * Phân hệ Sinh viên: Danh mục Học phần & Nộp bài tập các buổi
 * Tuân thủ kiến trúc MVC, CSRF Protection, Server-side Guard & Ownership
 */

require_once __DIR__ . '/../../Models/CourseLesson.php';

class CourseController
{
    private CourseLesson $model;

    public function __construct(?CourseLesson $model = null)
    {
        $this->model = $model ?: new CourseLesson();
    }

    /**
     * Kiểm tra quyền đăng nhập và vai trò Sinh viên (Guard ở Server theo Slide 25-26 Bài 7)
     */
    private function requireStudent(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            header('Location: ../index.php?action=login');
            exit;
        }

        // Sinh viên hoặc tài khoản requester
        $role = $_SESSION['user']['role'] ?? '';
        $userType = $_SESSION['user']['user_type'] ?? '';

        if ($role === 'agent') {
            header('Location: ../agent/index.php');
            exit;
        }

        return (int)$_SESSION['user']['id'];
    }

    /**
     * Xác thực CSRF Token (Slide 28-29 Bài 7)
     */
    private function verifyCsrf(): void
    {
        $sessionToken = $_SESSION['csrf'] ?? '';
        $postToken = $_POST['csrf'] ?? '';

        if (empty($sessionToken) || !hash_equals($sessionToken, $postToken)) {
            http_response_code(403);
            exit('CSRF token không hợp lệ hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
        }
    }

    /**
     * 1. Mục lục danh sách các học phần của sinh viên
     */
    public function index(): void
    {
        $studentId = $this->requireStudent();
        $courses = $this->model->getStudentCourses($studentId);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        // Sinh CSRF token cho phiên nếu chưa có
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        require __DIR__ . '/../../../views/student/courses/index.php';
    }

    /**
     * 2. Chi tiết học phần: Hiện danh sách các buổi học và trạng thái đính kèm bài tập
     */
    public function detail(): void
    {
        $studentId = $this->requireStudent();
        $courseId = max(1, (int)($_GET['course_id'] ?? 1));

        $course = $this->model->getCourseById($courseId);
        if (!$course) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Không tìm thấy học phần được yêu cầu!',
            ];
            header('Location: index.php?action=courses');
            exit;
        }

        $lessons = $this->model->getCourseLessonsWithSubmission($courseId, $studentId);

        // Tính thống kê hoàn thành
        $totalLessons = count($lessons);
        $completedLessons = 0;
        foreach ($lessons as $l) {
            if (!empty($l['is_submitted'])) {
                $completedLessons++;
            }
        }
        $progressPct = $totalLessons > 0 ? (int)round(($completedLessons / $totalLessons) * 100) : 0;

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        require __DIR__ . '/../../../views/student/courses/detail.php';
    }

    /**
     * 3. Xử lý nộp đính kèm bài tập (POST-Redirect-Get / AJAX Fetch)
     */
    public function submitAssignment(): void
    {
        $studentId = $this->requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=courses');
            exit;
        }

        $this->verifyCsrf();

        $lessonId = max(1, (int)($_POST['lesson_id'] ?? 0));
        $courseId = max(1, (int)($_POST['course_id'] ?? 1));
        $notes = trim($_POST['notes'] ?? '');

        $lesson = $this->model->getLessonById($lessonId);
        if (!$lesson) {
            $this->respondError('Không tìm thấy thông tin buổi học.', $courseId);
            return;
        }

        // Kiểm tra file đính kèm
        if (empty($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
            $this->respondError('Vui lòng chọn tệp đính kèm bài tập để hoàn thành buổi học!', $courseId);
            return;
        }

        $file = $_FILES['assignment_file'];
        $maxSize = 25 * 1024 * 1024; // 25 MB
        if ($file['size'] > $maxSize) {
            $this->respondError('Kích thước tệp vượt quá giới hạn 25MB cho phép.', $courseId);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['zip', 'rar', '7z', 'pdf', 'docx', 'doc', 'png', 'jpg', 'jpeg', 'txt', 'sql', 'php', 'html', 'css', 'js'];
        if (!in_array($ext, $allowedExts, true)) {
            $this->respondError('Định dạng tệp không được hỗ trợ. Vui lòng nén file .zip/.rar hoặc tải lên .pdf, .docx, .sql.', $courseId);
            return;
        }

        // Tạo thư mục lưu trữ nếu chưa có
        $uploadDir = __DIR__ . '/../../../uploads/assignments';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeOriginalName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($file['name']));
        $newFileName = 'sub_' . $studentId . '_lesson' . $lessonId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . '/' . $newFileName;
        $dbPath = 'uploads/assignments/' . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->respondError('Lỗi máy chủ khi lưu tệp đính kèm. Vui lòng thử lại.', $courseId);
            return;
        }

        // Cập nhật CSDL
        $success = $this->model->submitAssignment(
            $lessonId,
            $studentId,
            $dbPath,
            $safeOriginalName,
            (int)$file['size'],
            $notes
        );

        if (!$success) {
            $this->respondError('Lỗi khi ghi nhận trạng thái nộp bài trong CSDL.', $courseId);
            return;
        }

        $msg = "Đính kèm bài tập thành công cho Buổi {$lesson['lesson_number']}! Buổi học đã được ghi nhận: ĐÃ HOÀN THÀNH ✅";

        // Nếu là request AJAX Fetch
        if ($this->isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => true,
                'message' => $msg,
                'lesson_number' => $lesson['lesson_number'],
                'file_name' => $safeOriginalName,
                'file_path' => $dbPath,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => $msg,
        ];
        header("Location: index.php?action=course_detail&course_id={$courseId}");
        exit;
    }

    /**
     * 4. Xóa bài nộp
     */
    public function deleteSubmission(): void
    {
        $studentId = $this->requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=courses');
            exit;
        }

        $this->verifyCsrf();

        $submissionId = (int)($_POST['submission_id'] ?? 0);
        $courseId = max(1, (int)($_POST['course_id'] ?? 1));

        $deleted = $this->model->deleteSubmission($submissionId, $studentId);

        if ($deleted) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Đã hủy tệp đính kèm. Buổi học chuyển về trạng thái: CHƯA HOÀN THÀNH.',
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Không thể xóa bài nộp hoặc bạn không có quyền thao tác!',
            ];
        }

        header("Location: index.php?action=course_detail&course_id={$courseId}");
        exit;
    }

    /**
     * 5. Endpoint JSON trả về danh sách buổi học cho Fetch API (Slide 37-39 Bài 8)
     */
    public function apiLessons(): void
    {
        $studentId = $this->requireStudent();
        $courseId = max(1, (int)($_GET['course_id'] ?? 1));

        $lessons = $this->model->getCourseLessonsWithSubmission($courseId, $studentId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'course_id' => $courseId,
            'count' => count($lessons),
            'data' => $lessons,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
    }

    private function respondError(string $message, int $courseId): void
    {
        if ($this->isAjax()) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => $message,
        ];
        header("Location: index.php?action=course_detail&course_id={$courseId}");
        exit;
    }
}


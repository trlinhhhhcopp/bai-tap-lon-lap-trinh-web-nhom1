<?php
/**
 * Controller: CourseController
 * Phân hệ Sinh viên: Danh mục Học phần & Nộp bài tập các buổi
 * Gộp chung thành 1 trang duy nhất (Single Integrated Workspace)
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
            $appBase = get_app_base_url();
            header("Location: {$appBase}login");
            exit;
        }

        $role = $_SESSION['user']['role'] ?? '';
        $userType = $_SESSION['user']['user_type'] ?? '';

        if (
            ($role !== 'requester' && $userType !== 'student')
            || in_array($role, ['admin', 'agent'], true)
            || $userType === 'lecturer'
        ) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập chức năng học phần.');
        }

        return (int)$_SESSION['user']['id'];
    }

    private function requireEnrolledCourse(int $studentId, int $courseId, bool $json = false): void
    {
        if (!$this->model->studentHasCourse($studentId, $courseId)) {
            http_response_code(404);
            if ($json) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'message' => 'Không tìm thấy học phần được yêu cầu.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            exit('Không tìm thấy học phần được yêu cầu.');
        }
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
     * 1. Giao diện tích hợp duy nhất: Học phần & Bài tập của sinh viên
     * Hiển thị danh sách tất cả học phần VÀ toàn bộ các buổi học + nộp bài của môn đang chọn
     */
    public function index(): void
    {
        $studentId = $this->requireStudent();
        $courses = $this->model->getStudentCourses($studentId);

        // Xác định học phần đang chọn
        $requestedCourseId = (int)($_GET['course_id'] ?? ($_GET['id'] ?? 0));
        $selectedCourse = null;
        $selectedCourseId = 0;

        if (!empty($courses)) {
            // Tìm môn học theo yêu cầu
            if ($requestedCourseId > 0) {
                foreach ($courses as $c) {
                    if ((int)$c['course_id'] === $requestedCourseId) {
                        $selectedCourse = $c;
                        $selectedCourseId = $requestedCourseId;
                        break;
                    }
                }
            }

            // Nếu không chọn hoặc không hợp lệ, mặc định chọn môn đầu tiên
            if (!$selectedCourse) {
                $selectedCourse = $courses[0];
                $selectedCourseId = (int)$selectedCourse['course_id'];
            }

            // Lấy thông tin chi tiết môn học
            $detail = $this->model->getCourseById($selectedCourseId);
            if ($detail) {
                $selectedCourse = array_merge($detail, $selectedCourse);
            }
        }

        // Lấy danh sách các buổi học của môn đang chọn
        $lessons = [];
        $totalLessons = 0;
        $completedLessons = 0;
        $progressPct = 0;

        if ($selectedCourseId > 0) {
            $lessons = $this->model->getCourseLessonsWithSubmission($selectedCourseId, $studentId);
            $totalLessons = count($lessons);
            foreach ($lessons as $l) {
                if (!empty($l['is_submitted'])) {
                    $completedLessons++;
                }
            }
            $progressPct = $totalLessons > 0 ? (int)round(($completedLessons / $totalLessons) * 100) : 0;
        }

        // Alias $course để tương thích với các view helper
        $course = $selectedCourse;

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        require __DIR__ . '/../../../views/student/courses/index.php';
    }

    /**
     * 2. Chi tiết học phần (Giữ lại để chuyển tiếp tương thích ngược về 1 trang duy nhất)
     */
    public function detail(): void
    {
        $this->requireStudent();
        $courseId = max(1, (int)($_GET['course_id'] ?? ($_GET['id'] ?? 1)));
        $appBase = get_app_base_url();
        header("Location: {$appBase}student/courses?course_id={$courseId}#course-workspace");
        exit;
    }

    /**
     * 3. Xử lý nộp đính kèm bài tập (POST-Redirect-Get / AJAX Fetch)
     */
    public function submitAssignment(): void
    {
        $studentId = $this->requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $appBase = get_app_base_url();
            header("Location: {$appBase}student/courses");
            exit;
        }

        $this->verifyCsrf();

        $lessonId = max(1, (int)($_POST['lesson_id'] ?? 0));
        $courseId = max(1, (int)($_POST['course_id'] ?? 1));
        $notes = trim($_POST['notes'] ?? '');

        $lesson = $this->model->getLessonById($lessonId);
        if (!$lesson || (int)$lesson['course_id'] !== $courseId || !$this->model->studentHasCourse($studentId, $courseId)) {
            $this->respondError('Không tìm thấy thông tin buổi học.', $courseId, $lessonId);
            return;
        }

        // 1. Kiểm tra thời hạn nộp bài (Deadline Enforcement): Nộp quá hạn không cho nộp bài nữa, yêu cầu báo sự cố
        if (!empty($lesson['deadline_at'])) {
            $now = new \DateTime('now', new \DateTimeZone('Asia/Ho_Chi_Minh'));
            $deadline = new \DateTime($lesson['deadline_at'], new \DateTimeZone('Asia/Ho_Chi_Minh'));
            if ($now > $deadline) {
                $formattedDl = $deadline->format('H:i d/m/Y');
                $this->respondError(
                    "⛔ ĐÃ QUÁ HẠN NỘP BÀI TẬP (Hạn chót: {$formattedDl})! Hệ thống đã khóa tiếp nhận bài nộp tự động. " .
                    "Nếu gặp sự cố kỹ thuật (mạng chậm, lỗi Canvas LMS, mất điện, máy tính hỏng...), bạn vui lòng gửi phiếu 'Báo Lỗi Sự Cố Giờ Học' để Kỹ thuật viên xác thực và Giảng viên xem xét gia hạn nộp bài!",
                    $courseId,
                    $lessonId
                );
                return;
            }
        }

        // Kiểm tra file đính kèm
        if (empty($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
            $this->respondError('Vui lòng chọn tệp đính kèm bài tập để hoàn thành buổi học!', $courseId, $lessonId);
            return;
        }

        $file = $_FILES['assignment_file'];
        $maxSize = 25 * 1024 * 1024; // 25 MB
        if ($file['size'] > $maxSize) {
            $this->respondError('Kích thước tệp vượt quá giới hạn 25MB cho phép.', $courseId, $lessonId);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['zip', 'rar', '7z', 'pdf', 'docx', 'doc', 'png', 'jpg', 'jpeg', 'txt', 'sql', 'php', 'html', 'css', 'js'];
        if (!in_array($ext, $allowedExts, true)) {
            $this->respondError('Định dạng tệp không được hỗ trợ. Vui lòng nén file .zip/.rar hoặc tải lên .pdf, .docx, .sql.', $courseId, $lessonId);
            return;
        }

        // Tạo thư mục lưu trữ nếu chưa có
        $uploadDir = __DIR__ . '/../../../uploads/assignments';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeOriginalName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($file['name']));
        $newFileName = 'sub_' . $studentId . '_lesson' . $lessonId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destPath = $uploadDir . '/' . $newFileName;
        $dbPath = 'uploads/assignments/' . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->respondError('Lỗi máy chủ khi lưu tệp đính kèm. Vui lòng thử lại.', $courseId, $lessonId);
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
            if (is_file($destPath)) {
                unlink($destPath);
            }
            $this->respondError('Lỗi khi ghi nhận trạng thái nộp bài trong CSDL.', $courseId, $lessonId);
            return;
        }

        // Bắn thông báo đến Giảng viên phụ trách môn học (Ghi rõ tên và mã sinh viên nộp bài)
        try {
            require_once __DIR__ . '/../../Services/NotificationService.php';
            $lecturerId = (int)(db()->query("SELECT ca.lecturer_id FROM course_lessons cl JOIN course_assignments ca ON ca.course_id = cl.course_id WHERE cl.id = {$lessonId} LIMIT 1")->fetchColumn() ?: 0);
            if ($lecturerId > 0) {
                $sessionUser = $_SESSION['user'] ?? [];
                $studentName = trim($sessionUser['name'] ?? '');
                $studentCode = trim($sessionUser['user_code'] ?? '');
                if ($studentName === '' || $studentCode === '') {
                    $uStmt = db()->prepare("SELECT name, user_code FROM users WHERE id = :id LIMIT 1");
                    $uStmt->execute([':id' => $studentId]);
                    $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                    if ($uRow) {
                        $studentName = $uRow['name'] ?? 'Sinh viên';
                        $studentCode = $uRow['user_code'] ?? (string)$studentId;
                    }
                }
                $lessonNum   = $lesson['lesson_number'];
                $lessonTitle = $lesson['title'];

                NotificationService::send(
                    $lecturerId,
                    "📥 SV {$studentName} ({$studentCode}) nộp bài Buổi {$lessonNum}",
                    "Sinh viên {$studentName} ({$studentCode}) vừa nộp bài tập Buổi {$lessonNum}: '{$lessonTitle}'. Kính mời Thầy/Cô vào kiểm tra và chấm điểm!",
                    "lecturer/lessons.php?course_id={$courseId}&view_lesson_id={$lessonId}",
                    'assignment_submission',
                    'lecturer'
                );
            }
        } catch (\Throwable $e) {}

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
        $appBase = get_app_base_url();
        header("Location: {$appBase}student/courses?course_id={$courseId}#lesson-card-{$lessonId}");
        exit;
    }

    /**
     * 4. Xóa bài nộp
     */
    public function deleteSubmission(): void
    {
        $studentId = $this->requireStudent();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $appBase = get_app_base_url();
            header("Location: {$appBase}student/courses");
            exit;
        }

        $this->verifyCsrf();

        $submissionId = (int)($_POST['submission_id'] ?? 0);
        $courseId = max(1, (int)($_POST['course_id'] ?? 1));
        $lessonId = (int)($_POST['lesson_id'] ?? 0);

        $this->requireEnrolledCourse($studentId, $courseId);
        if (!$this->model->submissionBelongsToCourse($submissionId, $courseId)) {
            http_response_code(404);
            exit('Không tìm thấy bài nộp trong học phần này.');
        }

        $deleted = $this->model->deleteSubmission($submissionId, $studentId);

        if ($deleted) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Đã hủy tệp đính kèm. Buổi học chuyển về trạng thái: CHƯA HOÀN THÀNH.',
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Không thể hủy bài nộp (do buổi học đã hết hạn nộp hoặc bạn không có quyền thao tác)!',
            ];
        }

        $appBase = get_app_base_url();
        $anchor = $lessonId > 0 ? "#lesson-card-{$lessonId}" : "#course-workspace";
        header("Location: {$appBase}student/courses?course_id={$courseId}{$anchor}");
        exit;
    }

    /**
     * 5. Endpoint JSON trả về danh sách buổi học cho Fetch API (Slide 37-39 Bài 8)
     */
    public function apiLessons(): void
    {
        $studentId = $this->requireStudent();
        $courseId = max(1, (int)($_GET['course_id'] ?? 1));
        $this->requireEnrolledCourse($studentId, $courseId, true);

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

    private function respondError(string $message, int $courseId, int $lessonId = 0): void
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
        $appBase = get_app_base_url();
        $anchor = $lessonId > 0 ? "#lesson-card-{$lessonId}" : "#course-workspace";
        header("Location: {$appBase}student/courses?course_id={$courseId}{$anchor}");
        exit;
    }
}

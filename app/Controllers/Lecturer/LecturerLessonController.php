<?php
/**
 * Controller: LecturerLessonController
 * Phân hệ Giảng viên: Giao bài tập theo từng buổi học & Quản lý thời hạn nộp bài (ITIL Service Design & Classroom SLA)
 * Trách nhiệm: Giảng viên phụ trách môn học giao đề bài, gia hạn hạn nộp và chấm/nghiệm thu bài nộp sinh viên
 */

require_once __DIR__ . '/../../Models/CourseLesson.php';

class LecturerLessonController
{
    private CourseLesson $model;

    public function __construct(?CourseLesson $model = null)
    {
        $this->model = $model ?: new CourseLesson();
    }

    private function requireLecturer(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (empty($user)) {
            header('Location: ../index.php');
            exit;
        }

        $role = $user['role'] ?? '';
        $type = $user['user_type'] ?? '';

        if ($type !== 'lecturer' && $role !== 'admin') {
            set_flash('error', 'Bạn không có quyền truy cập vào cổng quản lý buổi học của Giảng viên!');
            header('Location: ../index.php');
            exit;
        }

        return $user;
    }

    private function verifyCsrf(): void
    {
        $postToken = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf'] ?? '';
        if (empty($postToken) || empty($sessionToken) || !hash_equals($sessionToken, $postToken)) {
            set_flash('error', 'Phiên làm việc đã hết hạn hoặc mã xác thực bảo mật CSRF không hợp lệ!');
            header('Location: lessons.php');
            exit;
        }
    }

    public function index(): void
    {
        $currentUser = $this->requireLecturer();
        $lecturerId  = (int)$currentUser['id'];
        $isAdmin     = ($currentUser['role'] === 'admin');

        $courses = $this->model->getLecturerCourses($lecturerId, $isAdmin);

        // Lấy lớp học phần theo ca_id (course_assignments.id) hoặc fallback theo course_id
        $selectedCaId = (int)($_GET['ca_id'] ?? ($_GET['assignment_id'] ?? 0));
        $selectedCourseId = (int)($_GET['course_id'] ?? 0);

        $selectedCourse = null;
        if ($selectedCaId > 0) {
            foreach ($courses as $c) {
                if ((int)$c['ca_id'] === $selectedCaId) {
                    $selectedCourse = $c;
                    break;
                }
            }
        } elseif ($selectedCourseId > 0) {
            foreach ($courses as $c) {
                if ((int)$c['course_id'] === $selectedCourseId) {
                    $selectedCourse = $c;
                    break;
                }
            }
        }

        if (!$selectedCourse && !empty($courses)) {
            $selectedCourse = $courses[0];
        }

        $selectedCaId     = $selectedCourse ? (int)$selectedCourse['ca_id'] : 0;
        $selectedCourseId = $selectedCourse ? (int)$selectedCourse['course_id'] : 0;
        $selectedClassId  = $selectedCourse ? (int)$selectedCourse['class_id'] : 0;

        $lessons = [];
        if ($selectedCourseId > 0) {
            $lessons = $this->model->getLessonsForLecturer($selectedCourseId, $selectedClassId);
        }

        $viewLessonId = (int)($_GET['view_lesson_id'] ?? 0);
        $lessonSubmissions = [];
        $activeLesson = null;
        if ($viewLessonId > 0) {
            $activeLesson = $this->model->getLessonById($viewLessonId);
            $lessonSubmissions = $this->model->getSubmissionsForLesson($viewLessonId, $selectedClassId);
        }

        // Lấy danh sách sinh viên thuộc các lớp mà Giảng viên này phụ trách bị KTV báo cáo vi phạm
        $violatingStudents = $this->model->getViolatingStudentsForLecturer($lecturerId, $isAdmin);

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        $flash = get_flash();
        require __DIR__ . '/../../../views/lecturer/lessons/index.php';
    }

    public function update(): void
    {
        $currentUser = $this->requireLecturer();
        $this->verifyCsrf();

        $lessonId    = (int)($_POST['lesson_id'] ?? 0);
        $courseId    = (int)($_POST['course_id'] ?? 0);
        $caId        = (int)($_POST['ca_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $requirement = trim($_POST['assignment_requirement'] ?? '');
        $deadlineAt  = trim($_POST['deadline_at'] ?? '');

        $redirectUrl = "lessons.php?" . ($caId > 0 ? "ca_id={$caId}" : "course_id={$courseId}");

        if ($lessonId <= 0 || empty($deadlineAt)) {
            set_flash('error', 'Vui lòng nhập đầy đủ thông tin buổi học và thời hạn nộp bài!');
            header("Location: {$redirectUrl}");
            exit;
        }

        // Định dạng lại datetime nếu từ input datetime-local (Y-m-d\TH:i) sang SQL (Y-m-d H:i:s)
        $formattedDeadline = str_replace('T', ' ', $deadlineAt);
        if (strlen($formattedDeadline) === 16) {
            $formattedDeadline .= ':00';
        }

        $success = $this->model->updateLessonAssignment($lessonId, $requirement, $formattedDeadline, $title ?: null);

        if ($success) {
            set_flash('success', "Đã cập nhật đề bài và gia hạn nộp bài thành công!");
        } else {
            set_flash('error', "Không thể cập nhật buổi học, vui lòng thử lại!");
        }

        header("Location: {$redirectUrl}&view_lesson_id={$lessonId}");
        exit;
    }

    /**
     * Giảng viên chấm điểm và nhận xét bài nộp của sinh viên
     */
    public function grade(): void
    {
        $currentUser = $this->requireLecturer();
        $this->verifyCsrf();

        $submissionId = (int)($_POST['submission_id'] ?? 0);
        $scoreRaw     = trim($_POST['score'] ?? '');
        $feedback     = trim($_POST['feedback'] ?? '');
        $caId         = (int)($_POST['ca_id'] ?? 0);
        $courseId     = (int)($_POST['course_id'] ?? 0);
        $viewLessonId = (int)($_POST['lesson_id'] ?? 0);

        $redirectUrl = "lessons.php?" . ($caId > 0 ? "ca_id={$caId}" : "course_id={$courseId}") . ($viewLessonId > 0 ? "&view_lesson_id={$viewLessonId}" : "");

        if ($submissionId <= 0 || $scoreRaw === '') {
            set_flash('error', 'Vui lòng nhập điểm hợp lệ từ 0.0 đến 10.0 cho sinh viên!');
            header("Location: {$redirectUrl}");
            exit;
        }

        $score = (float)$scoreRaw;
        if ($score < 0 || $score > 10) {
            set_flash('error', 'Điểm chấm phải nằm trong thang điểm từ 0.0 đến 10.0!');
            header("Location: {$redirectUrl}");
            exit;
        }

        $lecturerId = (int)$currentUser['id'];
        $success = $this->model->gradeSubmission($submissionId, $score, $feedback, $lecturerId);

        if ($success) {
            set_flash('success', "Đã lưu điểm (⭐ " . number_format($score, 1) . "/10.0) và lời nhận xét đánh giá cho sinh viên thành công!");
        } else {
            set_flash('error', 'Không thể lưu điểm, vui lòng thử lại!');
        }

        header("Location: {$redirectUrl}");
        exit;
    }

    /**
     * Giảng viên thêm bài tập / buổi học mới cho môn học
     */
    public function create(): void
    {
        $currentUser = $this->requireLecturer();
        $this->verifyCsrf();

        $courseId     = (int)($_POST['course_id'] ?? 0);
        $caId         = (int)($_POST['ca_id'] ?? 0);
        $lessonNumber = (int)($_POST['lesson_number'] ?? 0);
        $title        = trim($_POST['title'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $requirement  = trim($_POST['assignment_requirement'] ?? '');
        $deadlineAt   = trim($_POST['deadline_at'] ?? '');

        $redirectUrl = "lessons.php?" . ($caId > 0 ? "ca_id={$caId}" : "course_id={$courseId}");

        if ($courseId <= 0 || empty($title) || empty($requirement) || empty($deadlineAt)) {
            set_flash('error', 'Vui lòng nhập đầy đủ tiêu đề, yêu cầu bài tập và thời hạn nộp bài!');
            header("Location: {$redirectUrl}");
            exit;
        }

        if ($lessonNumber <= 0) {
            // Tự động tính số thứ tự buổi tiếp theo
            $existing = $this->model->getLessonsForLecturer($courseId);
            $maxNum = 0;
            foreach ($existing as $l) {
                if ((int)$l['lesson_number'] > $maxNum) {
                    $maxNum = (int)$l['lesson_number'];
                }
            }
            $lessonNumber = $maxNum + 1;
        }

        $formattedDeadline = str_replace('T', ' ', $deadlineAt);
        if (strlen($formattedDeadline) === 16) {
            $formattedDeadline .= ':00';
        }

        try {
            $newId = $this->model->createLesson($courseId, $lessonNumber, $title, $description, $requirement, $formattedDeadline);
            if ($newId > 0) {
                set_flash('success', "Đã thêm mới Buổi {$lessonNumber}: {$title} và ban hành đề bài thành công!");
            } else {
                set_flash('error', 'Không thể lưu bài tập mới, vui lòng thử lại!');
            }
        } catch (\Throwable $e) {
            set_flash('error', 'Lỗi: ' . $e->getMessage());
        }

        header("Location: {$redirectUrl}");
        exit;
    }

    /**
     * API Giảng viên xem trực tiếp bài nộp của sinh viên ngay trên app (In-App Previewer)
     */
    public function preview(): void
    {
        $currentUser = $this->requireLecturer();
        header('Content-Type: application/json; charset=utf-8');

        $submissionId = (int)($_GET['submission_id'] ?? 0);
        if ($submissionId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID bài nộp không hợp lệ']);
            exit;
        }

        $stmt = db()->prepare("
            SELECT s.*, 
                   u.name as student_name, u.user_code as student_code, u.email as student_email,
                   cl.class_code, cl.name as class_name,
                   l.title as lesson_title, l.lesson_number
            FROM student_assignment_submissions s
            JOIN users u ON s.student_id = u.id
            JOIN course_lessons l ON s.lesson_id = l.id
            LEFT JOIN classes cl ON u.class_id = cl.id
            WHERE s.id = :id
        ");
        $stmt->execute([':id' => $submissionId]);
        $sub = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$sub) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy dữ liệu bài nộp']);
            exit;
        }

        $fullPath = __DIR__ . '/../../../' . ltrim($sub['file_path'], '/\\');
        $ext = strtolower(pathinfo($sub['file_name'], PATHINFO_EXTENSION));
        $fileInfo = [
            'extension' => $ext,
            'exists'    => file_exists($fullPath),
            'size_kb'   => round($sub['file_size'] / 1024, 1),
            'preview_type' => 'none',
            'content'      => null,
            'zip_files'    => [],
        ];

        if (file_exists($fullPath)) {
            // Text, Code, SQL, MD
            if (in_array($ext, ['php', 'html', 'css', 'js', 'json', 'sql', 'txt', 'md', 'py', 'java', 'xml'], true)) {
                $fileInfo['preview_type'] = 'text';
                $fileInfo['content'] = htmlspecialchars(file_get_contents($fullPath, false, null, 0, 100000));
            } elseif (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $fileInfo['preview_type'] = 'media';
            } elseif ($ext === 'zip' && class_exists('ZipArchive')) {
                $fileInfo['preview_type'] = 'zip';
                $zip = new \ZipArchive();
                if ($zip->open($fullPath) === true) {
                    $count = min(100, $zip->numFiles);
                    for ($i = 0; $i < $count; $i++) {
                        $stat = $zip->statIndex($i);
                        if ($stat) {
                            $fileInfo['zip_files'][] = [
                                'name' => $stat['name'],
                                'size' => round($stat['size'] / 1024, 1) . ' KB',
                                'is_dir' => substr($stat['name'], -1) === '/',
                            ];
                        }
                    }
                    $zip->close();
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => array_merge($sub, ['file_info' => $fileInfo])
        ]);
        exit;
    }
}

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
}

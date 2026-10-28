<?php
/**
 * Controller CRUD Phân công giảng dạy (course_assignments)
 * Phần của: Lê Hà My
 */
require_once __DIR__ . '/../../Models/CourseAssignment.php';

class CourseAssignmentController
{
    private const PER_PAGE = 8;

    public function index(): void
    {
        $keyword = trim($_GET['q'] ?? '');
        $page    = max(1, (int) ($_GET['page'] ?? 1));

        $total      = CourseAssignment::count($keyword);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));
        $page       = min($page, $totalPages);

        $assignments = CourseAssignment::findAll($keyword, $page, self::PER_PAGE);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../views/lecturer/assignments/index.php';
    }

    public function create(): void
    {
        $errors = [];
        $old = [
            'class_id' => '', 'course_id' => '', 'lecturer_id' => '',
            'semester' => '2026_1', 'academic_year' => '2026-2027',
        ];
        $classes   = CourseAssignment::getAllClasses();
        $courses   = CourseAssignment::getAllCourses();
        $lecturers = CourseAssignment::getAllLecturers();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->sanitize($_POST);
            $errors = $this->validate($old);

            if (empty($errors)) {
                try {
                    CourseAssignment::create($old);
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Thêm phân công giảng dạy thành công.'];
                    header('Location: index.php?action=index');
                    exit;
                } catch (PDOException $e) {
                    $errors[] = ($e->getCode() == 23000)
                        ? 'Phân công này đã tồn tại (1 lớp học phần chỉ được 1 giảng viên).'
                        : 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../../views/lecturer/assignments/create.php';
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $assignment = CourseAssignment::findById($id);

        if (!$assignment) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy phân công.'];
            header('Location: index.php?action=index');
            exit;
        }

        $errors = [];
        $old    = $assignment;
        $classes   = CourseAssignment::getAllClasses();
        $courses   = CourseAssignment::getAllCourses();
        $lecturers = CourseAssignment::getAllLecturers();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->sanitize($_POST);
            $errors = $this->validate($old, $id);

            if (empty($errors)) {
                try {
                    CourseAssignment::update($id, $old);
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Cập nhật phân công thành công.'];
                    header('Location: index.php?action=index');
                    exit;
                } catch (PDOException $e) {
                    $errors[] = ($e->getCode() == 23000)
                        ? 'Phân công này đã tồn tại (1 lớp học phần chỉ được 1 giảng viên).'
                        : 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../../views/lecturer/assignments/edit.php';
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed – Chỉ chấp nhận POST');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (!CourseAssignment::findById($id)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy phân công.'];
        } else {
            CourseAssignment::delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Đã xóa phân công giảng dạy.'];
        }
        header('Location: index.php?action=index');
        exit;
    }

    private function sanitize(array $input): array
    {
        return [
            'class_id'      => (int) ($input['class_id'] ?? 0),
            'course_id'     => (int) ($input['course_id'] ?? 0),
            'lecturer_id'   => (int) ($input['lecturer_id'] ?? 0),
            'semester'      => trim($input['semester'] ?? ''),
            'academic_year' => trim($input['academic_year'] ?? ''),
        ];
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        if ($data['class_id'] <= 0)    $errors[] = 'Vui lòng chọn Lớp học.';
        if ($data['course_id'] <= 0)   $errors[] = 'Vui lòng chọn Môn học.';
        if ($data['lecturer_id'] <= 0) $errors[] = 'Vui lòng chọn Giảng viên phụ trách.';
        if ($data['semester'] === '')  $errors[] = 'Học kỳ không được để trống.';
        if ($data['academic_year'] === '') $errors[] = 'Năm học không được để trống.';

        if ($data['class_id'] > 0 && $data['course_id'] > 0 && $data['semester'] !== '') {
            if (CourseAssignment::exists($data['class_id'], $data['course_id'], $data['semester'], $excludeId)) {
                $errors[] = 'Lớp học phần này đã có giảng viên phụ trách. Mỗi lớp học phần chỉ được 1 giảng viên.';
            }
        }
        return $errors;
    }
}
?>
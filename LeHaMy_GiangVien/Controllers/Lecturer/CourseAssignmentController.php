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

        $userRole   = $_SESSION['user']['role'] ?? '';
        $userType   = $_SESSION['user']['user_type'] ?? '';
        $isLecturer = ($userType === 'lecturer' && $userRole !== 'admin');
        $lecturerId = $isLecturer ? (int)$_SESSION['user']['id'] : 0;

        $total      = CourseAssignment::count($keyword, $lecturerId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));
        $page       = min($page, $totalPages);

        $assignments = CourseAssignment::findAll($keyword, $page, self::PER_PAGE, $lecturerId);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../views/lecturer/assignments/index.php';
    }

    public function create(): void {
        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chức năng Phân công giảng dạy chỉ dành cho Quản trị viên (Admin)!'];
            header('Location: index.php?action=index');
            exit;
        }
        if (!empty($_SESSION['assignments_locked'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => '🔒 Cơ sở dữ liệu phân công giảng dạy đang bị KHÓA! Quản trị viên cần mở khóa trước khi thêm phân công.'];
            header('Location: index.php?action=index');
            exit;
        }
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

    public function edit(): void {
        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chức năng Phân công giảng dạy chỉ dành cho Quản trị viên (Admin)!'];
            header('Location: index.php?action=index');
            exit;
        }
        if (!empty($_SESSION['assignments_locked'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => '🔒 Cơ sở dữ liệu phân công giảng dạy đang bị KHÓA! Quản trị viên cần mở khóa trước khi sửa phân công.'];
            header('Location: index.php?action=index');
            exit;
        }
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

    public function delete(): void {
        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chức năng Phân công giảng dạy chỉ dành cho Quản trị viên (Admin)!'];
            header('Location: index.php?action=index');
            exit;
        }
        if (!empty($_SESSION['assignments_locked'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => '🔒 Cơ sở dữ liệu phân công giảng dạy đang bị KHÓA! Quản trị viên cần mở khóa trước khi xóa phân công.'];
            header('Location: index.php?action=index');
            exit;
        }
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
    public function exportCsv(): void
    {
        $userRole   = $_SESSION['user']['role'] ?? '';
        $userType   = $_SESSION['user']['user_type'] ?? '';
        $isLecturer = ($userType === 'lecturer' && $userRole !== 'admin');
        $lecturerId = $isLecturer ? (int)$_SESSION['user']['id'] : 0;

        $rows = CourseAssignment::exportAll($lecturerId);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bang_phan_cong_giang_day_helpdesk_' . date('Ymd_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'STT', 'Mã Lớp Học Phần', 'Tên Lớp', 'Mã Môn Học', 'Tên Môn Học',
            'Số Tín Chỉ', 'Mã Giảng Viên', 'Họ Tên Giảng Viên', 'Email Giảng Viên',
            'Học Kỳ', 'Năm Học', 'Thời Gian Gán'
        ]);

        foreach ($rows as $idx => $r) {
            fputcsv($out, [
                $idx + 1,
                $r['class_code'],
                $r['class_name'],
                $r['course_code'],
                $r['course_name'],
                $r['credits'] ?? 3,
                $r['lecturer_code'] ?? '',
                $r['lecturer_name'],
                $r['lecturer_email'],
                $r['semester'],
                $r['academic_year'],
                $r['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    public function toggleLock(): void
    {
        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chỉ Quản trị viên (Admin) mới có quyền Khóa / Mở khóa CSDL!'];
            header('Location: index.php?action=index');
            exit;
        }

        $_SESSION['assignments_locked'] = !($_SESSION['assignments_locked'] ?? false);
        $isLocked = $_SESSION['assignments_locked'];

        $_SESSION['flash'] = [
            'type' => $isLocked ? 'danger' : 'success',
            'msg'  => $isLocked 
                ? '🔒 Đã KHÓA cơ sở dữ liệu phân công giảng dạy thành công! (Chế độ chỉ đọc/Freeze)' 
                : '🔓 Đã MỞ KHÓA cơ sở dữ liệu phân công giảng dạy! Có thể tiếp tục thêm, sửa, xóa.'
        ];
        header('Location: index.php?action=index');
        exit;
    }

    public function addClass(): void
    {
        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chỉ Admin mới có quyền thêm lớp học!'];
            header('Location: index.php?action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_SESSION['assignments_locked'])) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Cơ sở dữ liệu đang bị KHÓA! Vui lòng mở khóa trước khi thêm lớp.'];
                header('Location: index.php?action=index');
                exit;
            }

            $code       = trim($_POST['class_code'] ?? '');
            $name       = trim($_POST['class_name'] ?? '');
            $cohort     = trim($_POST['cohort'] ?? 'D24');
            $department = trim($_POST['department'] ?? 'Khoa Công Nghệ Thông Tin');

            if (!empty($code) && !empty($name)) {
                try {
                    CourseAssignment::addClass($code, $name, $cohort, $department);
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Đã thêm lớp học mới: {$code} - {$name} thành công!"];
                } catch (\Exception $e) {
                    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Lỗi thêm lớp học: ' . $e->getMessage()];
                }
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Vui lòng nhập đầy đủ Mã lớp và Tên lớp học!'];
            }
        }
        $ref = $_POST['redirect_to'] ?? 'index';
        header('Location: index.php?action=' . $ref);
        exit;
    }

    public function addCourse(): void
    {
        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chỉ Admin mới có quyền thêm môn học!'];
            header('Location: index.php?action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_SESSION['assignments_locked'])) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Cơ sở dữ liệu đang bị KHÓA! Vui lòng mở khóa trước khi thêm môn.'];
                header('Location: index.php?action=index');
                exit;
            }

            $code       = trim($_POST['course_code'] ?? '');
            $name       = trim($_POST['course_name'] ?? '');
            $credits    = max(1, (int)($_POST['credits'] ?? 3));
            $department = trim($_POST['department'] ?? 'Khoa Công Nghệ Thông Tin');

            if (!empty($code) && !empty($name)) {
                try {
                    CourseAssignment::addCourse($code, $name, $credits, $department);
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Đã thêm môn học mới: {$code} - {$name} ({$credits} tín chỉ) thành công!"];
                } catch (\Exception $e) {
                    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Lỗi thêm môn học: ' . $e->getMessage()];
                }
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Vui lòng nhập đầy đủ Mã môn và Tên môn học!'];
            }
        }
        $ref = $_POST['redirect_to'] ?? 'index';
        header('Location: index.php?action=' . $ref);
        exit;
    }
}

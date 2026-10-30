<?php
/**
 * Controller CRUD Phân công giảng dạy (course_assignments)
 * Phần của: Lê Hà My
 */
require_once __DIR__ . '/../../Models/CourseAssignment.php';
require_once __DIR__ . '/../../../app/Services/SpreadsheetImportService.php';

use App\Services\SpreadsheetImportService;

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
        $classId = max(0, (int) ($_GET['class_id'] ?? 0));
        $lecturerFilterId = (!$isLecturer && $userRole === 'admin')
            ? max(0, (int) ($_GET['lecturer_id'] ?? 0))
            : 0;
        $classes = CourseAssignment::getAllClasses();
        $lecturers = $userRole === 'admin' ? CourseAssignment::getAllLecturers() : [];

        $total      = CourseAssignment::count($keyword, $lecturerId, $classId, $lecturerFilterId);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));
        $page       = min($page, $totalPages);

        $assignments = CourseAssignment::findAll(
            $keyword,
            $page,
            self::PER_PAGE,
            $lecturerId,
            $classId,
            $lecturerFilterId
        );

        $flash = $_SESSION['flash'] ?? null;
        $importedList = $flash['imported_list'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../views/lecturer/assignments/index.php';
    }

    public function manageCatalog(): void
    {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chức năng quản lý lớp và môn chỉ dành cho Quản trị viên (Admin)!'];
            header('Location: index.php?action=index');
            exit;
        }

        $classes = CourseAssignment::getAllClasses();
        $courses = CourseAssignment::getAllCourses();
        $isLocked = !empty($_SESSION['assignments_locked']);
        $flash = $_SESSION['flash'] ?? null;
        $importedList = $flash['imported_list'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../views/lecturer/assignments/catalog.php';
    }

    public function downloadTemplate(): void
    {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden');
        }

        $templates = [
            'classes' => ['mau_import_lop.csv', ['Mã lớp', 'Tên lớp', 'Niên khóa', 'Khoa']],
            'courses' => ['mau_import_mon.csv', ['Mã môn', 'Tên môn', 'Số tín chỉ', 'Khoa/Bộ môn']],
            'assignments' => ['mau_import_phan_cong.csv', ['Mã lớp', 'Mã môn', 'Mã giảng viên hoặc email', 'Học kỳ', 'Năm học']],
        ];
        $type = (string) ($_GET['type'] ?? '');
        if (!isset($templates[$type])) {
            http_response_code(404);
            exit('Không tìm thấy mẫu import.');
        }

        [$filename, $header] = $templates[$type];
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $header);
        fclose($output);
        exit;
    }

    public function importClasses(): void
    {
        $this->importCatalog('classes');
    }

    public function importCourses(): void
    {
        $this->importCatalog('courses');
    }

    private function importCatalog(string $type): void
    {
        $redirect = 'index.php?action=manage_catalog';
        if (!$this->canImport($redirect)) {
            return;
        }

        $fields = $type === 'classes'
            ? [
                'class_code' => ['malop', 'mãlớp', 'maclass', 'classcode', 'class'],
                'name' => ['tenlop', 'tênlớp', 'tenclass', 'classname', 'name'],
                'cohort' => ['nienkhoa', 'niênkhóa', 'khoahoc', 'cohort'],
                'department' => ['khoa', 'faculty', 'department'],
            ]
            : [
                'course_code' => ['mamon', 'mãmôn', 'mamonhoc', 'mãmônhọc', 'coursecode'],
                'name' => ['tenmon', 'tênmôn', 'tenmonhoc', 'tênmônhọc', 'coursename', 'name'],
                'credits' => ['tinchi', 'tínchỉ', 'sotinchi', 'sốtínchỉ', 'credits'],
                'department' => ['khoabomon', 'khoabộmôn', 'khoa', 'bomon', 'bộmôn', 'department'],
            ];
        $order = array_keys($fields);
        $required = $type === 'classes' ? ['class_code', 'name'] : ['course_code', 'name'];

        try {
            $records = $this->importRecords($fields, $required, $order);
        } catch (\RuntimeException $exception) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $exception->getMessage()];
            header('Location: ' . $redirect);
            exit;
        }

        $imported = 0;
        $importedRows = [];
        $errors = [];
        foreach ($records as $index => $record) {
            $code = trim((string) ($record[$type === 'classes' ? 'class_code' : 'course_code'] ?? ''));
            $name = trim((string) ($record['name'] ?? ''));
            if ($code === '' || $name === '') {
                $errors[] = 'Dòng ' . ($index + 2) . ': thiếu mã hoặc tên.';
                continue;
            }

            try {
                if ($type === 'classes') {
                    $cohort = trim((string) ($record['cohort'] ?? '')) ?: 'D24';
                    $department = trim((string) ($record['department'] ?? '')) ?: 'Khoa Công Nghệ Thông Tin';
                    CourseAssignment::addClass(
                        $code,
                        $name,
                        $cohort,
                        $department
                    );
                    $importedRows[] = [
                        'class_code' => $code,
                        'name' => $name,
                        'cohort' => $cohort,
                        'department' => $department,
                    ];
                } else {
                    $credits = (int) ($record['credits'] ?? 3);
                    $credits = $credits >= 1 && $credits <= 10 ? $credits : 3;
                    $department = trim((string) ($record['department'] ?? '')) ?: 'Khoa Công Nghệ Thông Tin';
                    CourseAssignment::addCourse(
                        $code,
                        $name,
                        $credits,
                        $department
                    );
                    $importedRows[] = [
                        'course_code' => $code,
                        'name' => $name,
                        'credits' => $credits,
                        'department' => $department,
                    ];
                }
                $imported++;
            } catch (\PDOException $exception) {
                $errors[] = 'Dòng ' . ($index + 2) . ': mã ' . $code . ' đã tồn tại hoặc dữ liệu không hợp lệ.';
            }
        }

        $_SESSION['flash'] = $this->importResult($imported, $errors);
        if ($importedRows !== []) {
            $_SESSION['flash']['imported_list'] = [
                'type' => $type,
                'total' => count($importedRows),
                'rows' => array_slice($importedRows, 0, 500),
            ];
        }
        header('Location: ' . $redirect);
        exit;
    }

    public function importAssignments(): void
    {
        $redirect = 'index.php?action=index';
        if (!$this->canImport($redirect)) {
            return;
        }

        $fields = [
            'class_code' => ['malop', 'mãlớp', 'maclass', 'classcode'],
            'course_code' => ['mamon', 'mãmôn', 'mamonhoc', 'mãmônhọc', 'coursecode'],
            'lecturer_code' => ['magiaovien', 'mãgiáoviên', 'magv', 'mãgv', 'magiangvien', 'mãgiảngviên', 'lecturercode', 'emailgiangvien', 'emailgiảngviên', 'tengiangvien', 'têngiảngviên', 'lecturername'],
            'semester' => ['hocky', 'họckỳ', 'semester'],
            'academic_year' => ['namhoc', 'nămhọc', 'academicyear'],
        ];

        try {
            $records = $this->importRecords($fields, array_keys($fields), array_keys($fields));
        } catch (\RuntimeException $exception) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $exception->getMessage()];
            header('Location: ' . $redirect);
            exit;
        }

        $imported = 0;
        $importedRows = [];
        $errors = [];
        foreach ($records as $index => $record) {
            $rowNumber = $index + 2;
            $classCode = trim((string) ($record['class_code'] ?? ''));
            $courseCode = trim((string) ($record['course_code'] ?? ''));
            $lecturerCode = trim((string) ($record['lecturer_code'] ?? ''));
            $semester = trim((string) ($record['semester'] ?? ''));
            $academicYear = trim((string) ($record['academic_year'] ?? ''));
            $classId = CourseAssignment::findClassIdByCode($classCode);
            $courseId = CourseAssignment::findCourseIdByCode($courseCode);
            $lecturerId = CourseAssignment::findLecturerIdByCode($lecturerCode);

            if ($classId === null || $courseId === null || $lecturerId === null || $semester === '' || $academicYear === '') {
                $errors[] = 'Dòng ' . $rowNumber . ': thiếu dữ liệu hoặc không tìm thấy mã lớp/môn/giảng viên.';
                continue;
            }
            if (CourseAssignment::exists($classId, $courseId, $semester)) {
                $errors[] = 'Dòng ' . $rowNumber . ': lớp, môn và học kỳ đã được phân công.';
                continue;
            }

            try {
                CourseAssignment::create([
                    'class_id' => $classId,
                    'course_id' => $courseId,
                    'lecturer_id' => $lecturerId,
                    'semester' => $semester,
                    'academic_year' => $academicYear,
                ]);
                $imported++;
                $importedRows[] = [
                    'class_code' => $classCode,
                    'course_code' => $courseCode,
                    'lecturer_code' => $lecturerCode,
                    'semester' => $semester,
                    'academic_year' => $academicYear,
                ];
            } catch (\PDOException $exception) {
                $errors[] = 'Dòng ' . $rowNumber . ': không thể lưu phân công (có thể dữ liệu đã tồn tại).';
            }
        }

        $_SESSION['flash'] = $this->importResult($imported, $errors);
        if ($importedRows !== []) {
            $_SESSION['flash']['imported_list'] = [
                'type' => 'assignments',
                'total' => count($importedRows),
                'rows' => array_slice($importedRows, 0, 500),
            ];
        }
        header('Location: ' . $redirect);
        exit;
    }

    private function canImport(string $redirect): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chức năng import chỉ dành cho Quản trị viên (Admin)!'];
            header('Location: index.php?action=index');
            exit;
        }
        if (!empty($_SESSION['assignments_locked'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Cơ sở dữ liệu đang bị khóa. Hãy mở khóa trước khi import.'];
            header('Location: ' . $redirect);
            exit;
        }

        return true;
    }

    private function importRecords(array $fields, array $required, array $columnOrder): array
    {
        if (empty($_FILES['import_file']['name'])) {
            throw new \RuntimeException('Vui lòng chọn tệp .xlsx, .csv hoặc .txt để import.');
        }
        if ($_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Không tải được tệp lên (mã lỗi ' . (int) $_FILES['import_file']['error'] . ').');
        }

        $extension = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
        $rows = SpreadsheetImportService::readFile($_FILES['import_file']['tmp_name'], $extension);
        if ($rows === []) {
            throw new \RuntimeException('Tệp không có dòng dữ liệu.');
        }

        $aliases = [];
        foreach ($fields as $field => $names) {
            $aliases[$field] = array_fill_keys(
                array_map([$this, 'normalizeImportHeader'], array_merge([$field], $names)),
                true
            );
        }

        $headerIndex = null;
        $headerColumns = [];
        foreach (array_slice($rows, 0, 10, true) as $rowIndex => $row) {
            $candidate = [];
            foreach ($row as $columnIndex => $value) {
                $normalized = $this->normalizeImportHeader((string) $value);
                foreach ($aliases as $field => $names) {
                    $matchesHeader = isset($names[$normalized]);
                    if (!$matchesHeader) {
                        foreach ($names as $alias => $_) {
                            if (strlen($alias) >= 5 && str_starts_with($normalized, $alias)) {
                                $matchesHeader = true;
                                break;
                            }
                        }
                    }
                    if ($matchesHeader) {
                        $candidate[$field] = $columnIndex;
                        break;
                    }
                }
            }
            if (count(array_intersect($required, array_keys($candidate))) === count($required)) {
                $headerIndex = $rowIndex;
                $headerColumns = $candidate;
                break;
            }
        }

        $dataRows = $headerIndex === null ? $rows : array_slice($rows, $headerIndex + 1);
        $records = [];
        foreach ($dataRows as $row) {
            $record = [];
            foreach ($columnOrder as $columnIndex => $field) {
                $sourceIndex = $headerIndex === null ? $columnIndex : ($headerColumns[$field] ?? null);
                $record[$field] = $sourceIndex === null ? '' : trim((string) ($row[$sourceIndex] ?? ''));
            }
            if (count(array_filter($record, static function ($value): bool {
                return $value !== '';
            })) > 0) {
                $records[] = $record;
            }
        }

        return $records;
    }

    private function normalizeImportHeader(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        return preg_replace('/[^\p{L}\p{N}]/u', '', $value);
    }

    private function importResult(int $imported, array $errors): array
    {
        $message = 'Đã import thành công ' . $imported . ' dòng.';
        if ($errors !== []) {
            $message .= ' Bỏ qua ' . count($errors) . ' dòng: ' . implode(' ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' ...';
            }
        }

        return ['type' => $errors === [] ? 'success' : 'danger', 'msg' => $message];
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

        $keyword = trim($_GET['q'] ?? '');
        $classId = max(0, (int) ($_GET['class_id'] ?? 0));
        $lecturerFilterId = (!$isLecturer && $userRole === 'admin')
            ? max(0, (int) ($_GET['lecturer_id'] ?? 0))
            : 0;
        $rows = CourseAssignment::exportAll($lecturerId, $keyword, $classId, $lecturerFilterId);

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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chỉ Admin mới có quyền thêm lớp học!'];
            header('Location: index.php?action=manage_catalog');
            exit;
        }

        if (!empty($_SESSION['assignments_locked'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Cơ sở dữ liệu đang bị KHÓA! Vui lòng mở khóa trước khi thêm lớp.'];
            header('Location: index.php?action=manage_catalog');
            exit;
        }

        $code       = trim($_POST['class_code'] ?? '');
        $name       = trim($_POST['class_name'] ?? '');
        $cohort     = trim($_POST['cohort'] ?? 'D24');
        $department = trim($_POST['department'] ?? 'Khoa Công Nghệ Thông Tin');

        if ($code !== '' && $name !== '') {
            try {
                CourseAssignment::addClass($code, $name, $cohort, $department);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => "Đã thêm lớp học mới: {$code} - {$name} thành công!"];
            } catch (\Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Lỗi thêm lớp học: ' . $e->getMessage()];
            }
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Vui lòng nhập đầy đủ Mã lớp và Tên lớp học!'];
        }
        header('Location: index.php?action=manage_catalog');
        exit;
    }

    public function addCourse(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $userRole = $_SESSION['user']['role'] ?? '';
        if ($userRole !== 'admin') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Chỉ Admin mới có quyền thêm môn học!'];
            header('Location: index.php?action=manage_catalog');
            exit;
        }

        if (!empty($_SESSION['assignments_locked'])) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Cơ sở dữ liệu đang bị KHÓA! Vui lòng mở khóa trước khi thêm môn.'];
            header('Location: index.php?action=manage_catalog');
            exit;
        }

        $code       = trim($_POST['course_code'] ?? '');
        $name       = trim($_POST['course_name'] ?? '');
        $credits    = max(1, (int)($_POST['credits'] ?? 3));
        $department = trim($_POST['department'] ?? 'Khoa Công Nghệ Thông Tin');

        if ($code !== '' && $name !== '') {
            try {
                CourseAssignment::addCourse($code, $name, $credits, $department);
                $_SESSION['flash'] = ['type' => 'success', 'msg' => "Đã thêm môn học mới: {$code} - {$name} ({$credits} tín chỉ) thành công!"];
            } catch (\Exception $e) {
                $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Lỗi thêm môn học: ' . $e->getMessage()];
            }
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Vui lòng nhập đầy đủ Mã môn và Tên môn học!'];
        }
        header('Location: index.php?action=manage_catalog');
        exit;
    }
}

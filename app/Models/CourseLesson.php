<?php
/**
 * Model / Repository: CourseLesson
 * Quản lý Học phần, Các buổi học và Đính kèm bài tập của Sinh viên
 * Tuân thủ kiến trúc MVC, PSR-4 và Prepared Statement
 */

require_once __DIR__ . '/../../config/database.php';

class CourseLesson
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: db();
    }

    /**
     * Lấy danh sách các học phần sinh viên đang học kèm tiến độ hoàn thành bài tập từng buổi
     */
    public function getStudentCourses(int $studentId): array
    {
        // 1. Xác định class_id của sinh viên
        $stmtUser = $this->pdo->prepare("SELECT class_id FROM users WHERE id = :sid");
        $stmtUser->execute([':sid' => $studentId]);
        $classId = (int)$stmtUser->fetchColumn();

        // Nếu users.class_id rỗng, tra cứu tiếp trong bảng class_students
        if ($classId <= 0) {
            $stmtCs = $this->pdo->prepare("SELECT class_id FROM class_students WHERE student_id = :sid ORDER BY id DESC LIMIT 1");
            $stmtCs->execute([':sid' => $studentId]);
            $classId = (int)$stmtCs->fetchColumn();
        }

        // Nếu vẫn không có lớp thì gán lớp mặc định 1 (D24-CNTT1) cho môi trường demo
        if ($classId <= 0) {
            $classId = 1;
        }

        // 2. Truy vấn danh sách học phần theo lớp của sinh viên
        $sql = "SELECT 
                    ca.id AS assignment_id,
                    ca.class_id,
                    ca.semester,
                    ca.academic_year,
                    c.id AS course_id,
                    c.course_code,
                    c.name AS course_name,
                    c.credits,
                    c.department,
                    cl.name AS class_name,
                    cl.class_code,
                    COALESCE(u.name, 'Chưa chỉ định') AS lecturer_name,
                    u.email AS lecturer_email
                FROM course_assignments ca
                JOIN courses c ON c.id = ca.course_id
                JOIN classes cl ON cl.id = ca.class_id
                LEFT JOIN users u ON u.id = ca.lecturer_id
                WHERE ca.class_id = :class_id
                ORDER BY c.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':class_id' => $classId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Với mỗi học phần, tính tổng số buổi và số buổi đã hoàn thành (có đính kèm bài tập)
        $lessonStmt = $this->pdo->prepare("SELECT COUNT(*) FROM course_lessons WHERE course_id = :cid");
        $subStmt = $this->pdo->prepare(
            "SELECT COUNT(DISTINCT s.lesson_id)
             FROM student_assignment_submissions s
             JOIN course_lessons l ON l.id = s.lesson_id
             WHERE l.course_id = :cid AND s.student_id = :sid AND s.is_completed = 1 AND s.file_path != ''"
        );

        foreach ($courses as &$c) {
            $cid = (int)$c['course_id'];
            $lessonStmt->execute([':cid' => $cid]);
            $totalLessons = (int)$lessonStmt->fetchColumn();

            $subStmt->execute([':cid' => $cid, ':sid' => $studentId]);
            $completedLessons = (int)$subStmt->fetchColumn();

            $c['total_lessons'] = $totalLessons;
            $c['completed_lessons'] = $completedLessons;
            $c['progress_pct'] = $totalLessons > 0 ? (int)round(($completedLessons / $totalLessons) * 100) : 0;
        }
        unset($c);

        return $courses;
    }

    /**
     * Lấy thông tin chi tiết một học phần
     */
    public function getCourseById(int $courseId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute([':id' => $courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return $course ?: null;
    }

    /**
     * Lấy danh sách các buổi học kèm trạng thái nộp bài của sinh viên
     */
    public function getCourseLessonsWithSubmission(int $courseId, int $studentId): array
    {
        $sql = "SELECT 
                    l.id AS lesson_id,
                    l.course_id,
                    l.lesson_number,
                    l.title,
                    l.description,
                    l.assignment_requirement,
                    l.deadline_at,
                    s.id AS submission_id,
                    s.file_path,
                    s.file_name,
                    s.file_size,
                    s.notes,
                    s.submitted_at,
                    s.is_completed
                FROM course_lessons l
                LEFT JOIN student_assignment_submissions s 
                    ON s.lesson_id = l.id AND s.student_id = :student_id
                WHERE l.course_id = :course_id
                ORDER BY l.lesson_number ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':course_id' => $courseId,
            ':student_id' => $studentId,
        ]);
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));

        foreach ($lessons as &$item) {
            $deadline = new DateTime($item['deadline_at'], new DateTimeZone('Asia/Ho_Chi_Minh'));
            $isSubmitted = !empty($item['file_path']) && (int)$item['is_completed'] === 1;

            $item['is_submitted'] = $isSubmitted;
            $item['is_overdue'] = !$isSubmitted && ($now > $deadline);
            
            if ($isSubmitted && !empty($item['submitted_at'])) {
                $subTime = new DateTime($item['submitted_at'], new DateTimeZone('Asia/Ho_Chi_Minh'));
                $item['is_late'] = $subTime > $deadline;
            } else {
                $item['is_late'] = false;
            }

            // Định dạng thời gian hiển thị thân thiện
            $item['deadline_formatted'] = $deadline->format('d/m/Y H:i');
            $diff = $now->diff($deadline);
            if ($isSubmitted) {
                $item['status_badge'] = $item['is_late'] ? 'Đã nộp muộn' : 'Đã hoàn thành';
                $item['status_color'] = $item['is_late'] ? 'amber' : 'emerald';
            } elseif ($now > $deadline) {
                $item['status_badge'] = 'Quá hạn nộp';
                $item['status_color'] = 'rose';
            } else {
                $days = $diff->days;
                $item['status_badge'] = $days == 0 ? 'Hết hạn hôm nay' : "Còn {$days} ngày";
                $item['status_color'] = $days <= 2 ? 'amber' : 'indigo';
            }
        }
        unset($item);

        return $lessons;
    }

    /**
     * Lấy thông tin một buổi học
     */
    public function getLessonById(int $lessonId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM course_lessons WHERE id = :id");
        $stmt->execute([':id' => $lessonId]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        return $lesson ?: null;
    }

    /**
     * Nộp bài tập đính kèm (hoặc cập nhật file bài tập mới)
     */
    public function submitAssignment(
        int $lessonId,
        int $studentId,
        string $filePath,
        string $fileName,
        int $fileSize,
        string $notes = ''
    ): bool {
        // Kiểm tra xem đã có bản nộp trước đó chưa
        $stmtCheck = $this->pdo->prepare("SELECT id, file_path FROM student_assignment_submissions WHERE lesson_id = :lid AND student_id = :sid");
        $stmtCheck->execute([':lid' => $lessonId, ':sid' => $studentId]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $now = date('Y-m-d H:i:s');

        if ($existing) {
            // Xóa file cũ nếu khác file mới
            if (!empty($existing['file_path']) && $existing['file_path'] !== $filePath && file_exists(__DIR__ . '/../../' . $existing['file_path'])) {
                @unlink(__DIR__ . '/../../' . $existing['file_path']);
            }

            $stmtUpdate = $this->pdo->prepare(
                "UPDATE student_assignment_submissions 
                 SET file_path = :path, file_name = :name, file_size = :size, notes = :notes, submitted_at = :sub_at, is_completed = 1
                 WHERE id = :id"
            );
            return $stmtUpdate->execute([
                ':path' => $filePath,
                ':name' => $fileName,
                ':size' => $fileSize,
                ':notes' => $notes,
                ':sub_at' => $now,
                ':id' => $existing['id'],
            ]);
        }

        $stmtInsert = $this->pdo->prepare(
            "INSERT INTO student_assignment_submissions 
             (lesson_id, student_id, file_path, file_name, file_size, notes, submitted_at, is_completed)
             VALUES (:lid, :sid, :path, :name, :size, :notes, :sub_at, 1)"
        );
        return $stmtInsert->execute([
            ':lid' => $lessonId,
            ':sid' => $studentId,
            ':path' => $filePath,
            ':name' => $fileName,
            ':size' => $fileSize,
            ':notes' => $notes,
            ':sub_at' => $now,
        ]);
    }

    /**
     * Xóa bài nộp (Đảm bảo kiểm tra ownership sinh viên)
     */
    public function deleteSubmission(int $submissionId, int $studentId): bool
    {
        $stmt = $this->pdo->prepare("SELECT id, file_path FROM student_assignment_submissions WHERE id = :id AND student_id = :sid");
        $stmt->execute([':id' => $submissionId, ':sid' => $studentId]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sub) {
            return false;
        }

        if (!empty($sub['file_path']) && file_exists(__DIR__ . '/../../' . $sub['file_path'])) {
            @unlink(__DIR__ . '/../../' . $sub['file_path']);
        }

        $stmtDel = $this->pdo->prepare("DELETE FROM student_assignment_submissions WHERE id = :id AND student_id = :sid");
        return $stmtDel->execute([':id' => $submissionId, ':sid' => $studentId]);
    }
}

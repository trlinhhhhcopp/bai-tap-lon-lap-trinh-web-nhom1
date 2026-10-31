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
                    s.score, s.star_rating,
                    s.feedback,
                    s.graded_at,
                    s.graded_by,
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
     * Lấy danh sách các lớp học phần được phân công cho giảng viên
     */
    public function getLecturerCourses(int $lecturerId, bool $isAdmin = false): array
    {
        $sql = "SELECT ca.id as ca_id, ca.course_id, ca.class_id, ca.semester, ca.academic_year,
                       c.course_code, c.name as course_name, c.credits,
                       cl.class_code, cl.name as class_name,
                       u.name as lecturer_name,
                       (SELECT COUNT(*) FROM course_lessons cln WHERE cln.course_id = c.id) as total_lessons
                FROM course_assignments ca
                JOIN courses c ON ca.course_id = c.id
                JOIN classes cl ON ca.class_id = cl.id
                LEFT JOIN users u ON ca.lecturer_id = u.id ";

        $params = [];
        if (!$isAdmin) {
            $sql .= "WHERE ca.lecturer_id = :lid ";
            $params[':lid'] = $lecturerId;
        }
        $sql .= "ORDER BY ca.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách bài học các buổi của môn học dành cho Giảng viên quản lý
     */
    public function getLessonsForLecturer(int $courseId, int $classId = 0): array
    {
        if ($classId > 0) {
            $sql = "SELECT l.*,
                           (SELECT COUNT(*) FROM student_assignment_submissions s 
                            JOIN users u_sub ON s.student_id = u_sub.id 
                            WHERE s.lesson_id = l.id AND s.is_completed = 1 AND u_sub.class_id = :class_id
                              AND u_sub.is_active = 1
                              AND (u_sub.lock_reason IS NULL OR u_sub.lock_reason = 'none' OR u_sub.lock_reason = '')
                              AND NOT EXISTS (SELECT 1 FROM tickets t_sp WHERE t_sp.requester_id = u_sub.id AND t_sp.is_spam = 1)) AS submission_count
                    FROM course_lessons l
                    WHERE l.course_id = :cid
                    ORDER BY l.lesson_number ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cid' => $courseId, ':class_id' => $classId]);
        } else {
            $sql = "SELECT l.*,
                           (SELECT COUNT(*) FROM student_assignment_submissions s 
                            JOIN users u_sub ON s.student_id = u_sub.id
                            WHERE s.lesson_id = l.id AND s.is_completed = 1
                              AND u_sub.is_active = 1
                              AND (u_sub.lock_reason IS NULL OR u_sub.lock_reason = 'none' OR u_sub.lock_reason = '')
                              AND NOT EXISTS (SELECT 1 FROM tickets t_sp WHERE t_sp.requester_id = u_sub.id AND t_sp.is_spam = 1)) AS submission_count
                    FROM course_lessons l
                    WHERE l.course_id = :cid
                    ORDER BY l.lesson_number ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cid' => $courseId]);
        }
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        foreach ($lessons as &$item) {
            $deadline = !empty($item['deadline_at']) ? new DateTime($item['deadline_at'], new DateTimeZone('Asia/Ho_Chi_Minh')) : null;
            $item['is_overdue'] = $deadline ? ($now > $deadline) : false;
            $item['deadline_formatted'] = $deadline ? $deadline->format('d/m/Y H:i') : 'Chưa đặt hạn';
            $item['deadline_iso'] = $deadline ? $deadline->format('Y-m-d\TH:i') : '';
        }
        unset($item);

        return $lessons;
    }

    /**
     * Giảng viên cập nhật đề bài / yêu cầu và hạn nộp cho buổi học
     */
    /**
     * Lấy danh sách sinh viên thuộc các lớp mà Giảng viên phụ trách bị KTV báo cáo vi phạm
     */
    public function getViolatingStudentsForLecturer(int $lecturerId, bool $isAdmin = false): array
    {
        $sql = "SELECT DISTINCT t.id AS ticket_id, t.ticket_code, t.title AS ticket_title, 
                                t.spam_reason, t.created_at AS reported_at,
                                u.id AS student_id, u.user_code, u.name AS student_name, u.email AS student_email,
                                cl.class_code, cl.name AS class_name
                FROM tickets t
                JOIN users u ON t.requester_id = u.id
                LEFT JOIN classes cl ON u.class_id = cl.id
                WHERE t.is_spam = 1 AND u.user_type = 'student' ";
        
        $params = [];
        if (!$isAdmin) {
            $sql .= "AND u.class_id IN (SELECT class_id FROM course_assignments WHERE lecturer_id = :lid) ";
            $params[':lid'] = $lecturerId;
        }
        $sql .= "ORDER BY t.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateLessonAssignment(int $lessonId, string $requirement, string $deadlineAt, ?string $title = null): bool
    {
        $sql = "UPDATE course_lessons 
                SET assignment_requirement = :req, deadline_at = :deadline" .
                ($title !== null ? ", title = :title" : "") .
                " WHERE id = :id";
        $params = [
            ':req' => $requirement,
            ':deadline' => $deadlineAt,
            ':id' => $lessonId,
        ];
        if ($title !== null) {
            $params[':title'] = $title;
        }
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Lấy danh sách toàn bộ bài tập sinh viên đã nộp cho buổi học này
     */
    public function getSubmissionsForLesson(int $lessonId, int $classId = 0): array
    {
        $whereClass = ($classId > 0) ? " AND u.class_id = :cid" : "";
        $sql = "SELECT s.*, 
                       u.name AS student_name, u.user_code AS student_code, u.email AS student_email,
                       l.title AS lesson_title, l.lesson_number, l.deadline_at,
                       cl.class_code,
                       (SELECT COUNT(*) FROM tickets t_sp WHERE t_sp.requester_id = u.id AND t_sp.is_spam = 1) AS spam_ticket_count,
                       (SELECT t_sp.spam_reason FROM tickets t_sp WHERE t_sp.requester_id = u.id AND t_sp.is_spam = 1 ORDER BY t_sp.id DESC LIMIT 1) AS spam_reason
                FROM student_assignment_submissions s
                JOIN users u ON s.student_id = u.id
                JOIN course_lessons l ON s.lesson_id = l.id
                LEFT JOIN classes cl ON u.class_id = cl.id
                WHERE s.lesson_id = :lid {$whereClass}
                  AND u.is_active = 1
                  AND (u.lock_reason IS NULL OR u.lock_reason = 'none' OR u.lock_reason = '')
                  AND NOT EXISTS (SELECT 1 FROM tickets t_sp WHERE t_sp.requester_id = u.id AND t_sp.is_spam = 1)
                ORDER BY s.submitted_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $params = [':lid' => $lessonId];
        if ($classId > 0) {
            $params[':cid'] = $classId;
        }
        $stmt->execute($params);
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subs as &$sub) {
            $deadline = !empty($sub['deadline_at']) ? new DateTime($sub['deadline_at'], new DateTimeZone('Asia/Ho_Chi_Minh')) : null;
            $subTime  = !empty($sub['submitted_at']) ? new DateTime($sub['submitted_at'], new DateTimeZone('Asia/Ho_Chi_Minh')) : null;
            $sub['is_late'] = ($deadline && $subTime) ? ($subTime > $deadline) : false;
            $sub['submitted_formatted'] = $subTime ? $subTime->format('d/m/Y H:i') : '';
        }
        unset($sub);

        return $subs;
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

    /**
     * Giảng viên chấm điểm và nhận xét bài nộp của sinh viên
     */
    public function gradeSubmission(int $submissionId, float $score, int $starRating, string $feedback, int $lecturerId): bool
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("
            UPDATE student_assignment_submissions 
            SET score = :score, star_rating = :star, feedback = :feedback, graded_at = :graded_at, graded_by = :graded_by 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':score'     => $score,
            ':star'      => max(1, min(5, $starRating)),
            ':feedback'  => $feedback,
            ':graded_at' => $now,
            ':graded_by' => $lecturerId,
            ':id'        => $submissionId,
        ]);
    }

    /**
     * Giảng viên tạo thêm buổi học / bài tập mới cho môn học
     */
    public function createLesson(int $courseId, int $lessonNumber, string $title, string $description, string $requirement, string $deadlineAt): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO course_lessons (course_id, lesson_number, title, description, assignment_requirement, deadline_at)
            VALUES (:course_id, :lesson_number, :title, :description, :requirement, :deadline_at)
        ");
        $stmt->execute([
            ':course_id'       => $courseId,
            ':lesson_number'   => $lessonNumber,
            ':title'           => $title,
            ':description'     => $description,
            ':requirement'     => $requirement,
            ':deadline_at'     => $deadlineAt,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}

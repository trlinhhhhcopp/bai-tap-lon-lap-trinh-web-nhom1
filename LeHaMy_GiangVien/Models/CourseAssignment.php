<?php
/**
 * Model CourseAssignment
 * Bảng: course_assignments
 * Quy tắc: Mỗi lớp học phần chỉ có DUY NHẤT 1 giảng viên phụ trách
 * Phần của: Lê Hà My
 * Tất cả truy vấn đều dùng prepared statement
 */
require_once __DIR__ . '/../config/database.php';

class CourseAssignment
{
    public static function count(
        string $keyword = '',
        int $lecturerId = 0,
        int $classId = 0,
        int $lecturerFilterId = 0
    ): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM course_assignments ca
                JOIN classes cl ON ca.class_id = cl.id
                JOIN courses co ON ca.course_id = co.id
                JOIN users u ON ca.lecturer_id = u.id
                WHERE 1=1";
        $params = [];

        if ($lecturerId > 0) {
            $sql .= " AND ca.lecturer_id = :lid";
            $params['lid'] = $lecturerId;
        }
        if ($classId > 0) {
            $sql .= " AND ca.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        if ($lecturerFilterId > 0) {
            $sql .= " AND ca.lecturer_id = :lecturer_filter_id";
            $params['lecturer_filter_id'] = $lecturerFilterId;
        }
        if ($keyword !== '') {
            $sql .= " AND (cl.class_code LIKE :kw OR cl.name LIKE :kw
                       OR co.course_code LIKE :kw OR co.name LIKE :kw
                       OR u.name LIKE :kw OR ca.semester LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    public static function findAll(
        string $keyword = '',
        int $page = 1,
        int $perPage = 10,
        int $lecturerId = 0,
        int $classId = 0,
        int $lecturerFilterId = 0
    ): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT ca.id, ca.class_id, ca.course_id, ca.lecturer_id,
                       ca.semester, ca.academic_year, ca.created_at,
                       cl.class_code, cl.name AS class_name,
                       co.course_code, co.name AS course_name,
                       u.name AS lecturer_name, u.email AS lecturer_email
                FROM course_assignments ca
                JOIN classes cl ON ca.class_id = cl.id
                JOIN courses co ON ca.course_id = co.id
                JOIN users u ON ca.lecturer_id = u.id
                WHERE 1=1";
        $params = [];

        if ($lecturerId > 0) {
            $sql .= " AND ca.lecturer_id = :lid";
            $params['lid'] = $lecturerId;
        }
        if ($classId > 0) {
            $sql .= " AND ca.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        if ($lecturerFilterId > 0) {
            $sql .= " AND ca.lecturer_id = :lecturer_filter_id";
            $params['lecturer_filter_id'] = $lecturerFilterId;
        }
        if ($keyword !== '') {
            $sql .= " AND (cl.class_code LIKE :kw OR cl.name LIKE :kw
                       OR co.course_code LIKE :kw OR co.name LIKE :kw
                       OR u.name LIKE :kw OR ca.semester LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY ca.semester DESC, cl.class_code, co.course_code
                  LIMIT :limit OFFSET :offset";

        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $sql = "SELECT ca.*,
                       cl.class_code, cl.name AS class_name,
                       co.course_code, co.name AS course_name,
                       u.name AS lecturer_name, u.email AS lecturer_email
                FROM course_assignments ca
                JOIN classes cl ON ca.class_id = cl.id
                JOIN courses co ON ca.course_id = co.id
                JOIN users u ON ca.lecturer_id = u.id
                WHERE ca.id = :id
                LIMIT 1";
        $stmt = db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function exists(int $classId, int $courseId, string $semester, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) AS c FROM course_assignments
                WHERE class_id = :class_id
                  AND course_id = :course_id
                  AND semester = :semester";
        $params = [
            'class_id'  => $classId,
            'course_id' => $courseId,
            'semester'  => $semester,
        ];
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['c'] > 0;
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO course_assignments
                (class_id, course_id, lecturer_id, semester, academic_year)
                VALUES
                (:class_id, :course_id, :lecturer_id, :semester, :academic_year)";
        $stmt = db()->prepare($sql);
        $stmt->execute([
            'class_id'      => $data['class_id'],
            'course_id'     => $data['course_id'],
            'lecturer_id'   => $data['lecturer_id'],
            'semester'      => $data['semester'],
            'academic_year' => $data['academic_year'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE course_assignments SET
                    class_id      = :class_id,
                    course_id     = :course_id,
                    lecturer_id   = :lecturer_id,
                    semester      = :semester,
                    academic_year = :academic_year
                WHERE id = :id";
        $stmt = db()->prepare($sql);
        return $stmt->execute([
            'class_id'      => $data['class_id'],
            'course_id'     => $data['course_id'],
            'lecturer_id'   => $data['lecturer_id'],
            'semester'      => $data['semester'],
            'academic_year' => $data['academic_year'],
            'id'            => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare("DELETE FROM course_assignments WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /** Danh mục – cũng dùng prepare cho đồng bộ */
    public static function getAllClasses(): array
    {
        $stmt = db()->prepare("SELECT id, class_code, name, cohort, faculty AS department FROM classes ORDER BY class_code");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllCourses(): array
    {
        $stmt = db()->prepare("SELECT id, course_code, name, credits, department FROM courses ORDER BY course_code");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllLecturers(): array
    {
        $stmt = db()->prepare(
            "SELECT id, name, email, user_code FROM users
             WHERE user_type = 'lecturer' AND is_active = 1
             ORDER BY name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findClassIdByCode(string $code): ?int
    {
        $stmt = db()->prepare("SELECT id FROM classes WHERE class_code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public static function findCourseIdByCode(string $code): ?int
    {
        $stmt = db()->prepare("SELECT id FROM courses WHERE course_code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public static function findLecturerIdByCode(string $code): ?int
    {
        $stmt = db()->prepare(
            "SELECT id FROM users
             WHERE user_type = 'lecturer' AND (user_code = :code OR email = :email OR name = :name)
             ORDER BY is_active DESC, id LIMIT 2"
        );
        $stmt->execute(['code' => $code, 'email' => $code, 'name' => $code]);
        $matches = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return count($matches) === 1 ? (int) $matches[0] : null;
    }

    public static function exportAll(
        int $lecturerId = 0,
        string $keyword = '',
        int $classId = 0,
        int $lecturerFilterId = 0
    ): array
    {
        $sql = "SELECT ca.id, cl.class_code, cl.name AS class_name,
                       co.course_code, co.name AS course_name, co.credits,
                       u.name AS lecturer_name, u.email AS lecturer_email, u.user_code AS lecturer_code,
                       ca.semester, ca.academic_year, ca.created_at
                FROM course_assignments ca
                JOIN classes cl ON ca.class_id = cl.id
                JOIN courses co ON ca.course_id = co.id
                JOIN users u ON ca.lecturer_id = u.id
                WHERE 1=1";
        $params = [];
        if ($lecturerId > 0) {
            $sql .= " AND ca.lecturer_id = :lid";
            $params['lid'] = $lecturerId;
        }
        if ($classId > 0) {
            $sql .= " AND ca.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        if ($lecturerFilterId > 0) {
            $sql .= " AND ca.lecturer_id = :lecturer_filter_id";
            $params['lecturer_filter_id'] = $lecturerFilterId;
        }
        if ($keyword !== '') {
            $sql .= " AND (cl.class_code LIKE :kw OR cl.name LIKE :kw
                       OR co.course_code LIKE :kw OR co.name LIKE :kw
                       OR u.name LIKE :kw OR ca.semester LIKE :kw)";
            $params['kw'] = '%' . $keyword . '%';
        }
        $sql .= " ORDER BY ca.semester DESC, cl.class_code, co.course_code";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function addClass(string $code, string $name, string $cohort, string $department): int
    {
        $stmt = db()->prepare("INSERT INTO classes (class_code, name, cohort, faculty) VALUES (:c, :n, :co, :d)");
        $stmt->execute(['c' => $code, 'n' => $name, 'co' => $cohort, 'd' => $department]);
        return (int)db()->lastInsertId();
    }

    public static function addCourse(string $code, string $name, int $credits, string $department): int
    {
        $stmt = db()->prepare("INSERT INTO courses (course_code, name, credits, department) VALUES (:c, :n, :cr, :d)");
        $stmt->execute(['c' => $code, 'n' => $name, 'cr' => $credits, 'd' => $department]);
        return (int)db()->lastInsertId();
    }
}

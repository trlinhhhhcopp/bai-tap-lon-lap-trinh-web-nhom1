
<?php
/**
 * Model Holiday
 * Bảng: holidays
 * Phần của: Phan Công Thành (Admin)
 * Chức năng: Danh sách, tìm kiếm, thêm, sửa, xóa ngày nghỉ lễ.
 */
require_once __DIR__ . '/../../config/database.php';

class Holiday
{
    // Đếm số ngày nghỉ lễ, có hỗ trợ tìm kiếm
    public static function count(string $keyword = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM holidays WHERE 1 = 1";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND name LIKE :keyword";
            $params['keyword'] = '%' . $keyword . '%';
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch()['total'];
    }

    // Lấy danh sách ngày nghỉ lễ, hỗ trợ tìm kiếm và phân trang
    public static function findAll(
        string $keyword = '',
        int $page = 1,
        int $perPage = 10
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT id, name, start_date, end_date,
                       is_recurring, created_at
                FROM holidays
                WHERE 1 = 1";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND name LIKE :keyword";
            $params['keyword'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY start_date DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = db()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Tìm một ngày nghỉ lễ theo ID
    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            "SELECT * FROM holidays WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    // Thêm ngày nghỉ lễ
    public static function create(array $data): int
    {
        $sql = "INSERT INTO holidays
                    (name, start_date, end_date, is_recurring)
                VALUES
                    (:name, :start_date, :end_date, :is_recurring)";

        $stmt = db()->prepare($sql);
        $stmt->execute([
            'name'         => trim($data['name']),
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
            'is_recurring' => (int) $data['is_recurring'],
        ]);

        return (int) db()->lastInsertId();
    }

    // Cập nhật ngày nghỉ lễ
    public static function update(int $id, array $data): bool
    {
        $sql = "UPDATE holidays
                SET name = :name,
                    start_date = :start_date,
                    end_date = :end_date,
                    is_recurring = :is_recurring
                WHERE id = :id";

        $stmt = db()->prepare($sql);

        return $stmt->execute([
            'name'         => trim($data['name']),
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
            'is_recurring' => (int) $data['is_recurring'],
            'id'           => $id,
        ]);
    }

    // Xóa ngày nghỉ lễ theo ID
    public static function delete(int $id): bool
    {
        $stmt = db()->prepare(
            "DELETE FROM holidays WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }
}

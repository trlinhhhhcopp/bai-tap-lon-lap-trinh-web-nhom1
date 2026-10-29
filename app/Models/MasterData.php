<?php
namespace App\Models;

use App\Core\Database;

class MasterData {
    public static function getClasses(): array {
        return Database::getConnection()->query("SELECT * FROM classes ORDER BY class_code ASC")->fetchAll();
    }

    public static function getCategories(): array {
        return Database::getConnection()->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
    }

    public static function getCategoryById(int $id): ?array {
        $stmt = Database::getConnection()->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getPriorities(): array {
        return Database::getConnection()->query("SELECT * FROM priorities ORDER BY sort_order ASC")->fetchAll();
    }

    public static function getCourses(): array {
        return Database::getConnection()->query("SELECT * FROM courses ORDER BY course_code ASC")->fetchAll();
    }
}


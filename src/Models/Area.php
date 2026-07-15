<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class Area
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function all(): array
    {
        return self::db()
            ->query('SELECT * FROM areas ORDER BY id ASC')
            ->fetchAll();
    }

    public static function allWithRepoCount(): array
    {
        return self::db()
            ->query(
                'SELECT a.*, COUNT(r.id) AS repo_count
                 FROM areas a
                 LEFT JOIN repositories r ON r.area_id = a.id
                 GROUP BY a.id
                 ORDER BY a.id ASC'
            )
            ->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM areas WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM areas WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, string $slug, string $color): void
    {
        self::db()->prepare(
            'INSERT INTO areas (name, slug, color) VALUES (?, ?, ?)'
        )->execute([$name, $slug, $color]);
    }

    public static function update(int $id, string $name, string $color): void
    {
        self::db()->prepare(
            'UPDATE areas SET name = ?, color = ? WHERE id = ?'
        )->execute([$name, $color, $id]);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('DELETE FROM areas WHERE id = ?')->execute([$id]);
    }
}

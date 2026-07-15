<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class SupportCategory
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function all(): array
    {
        return self::db()
            ->query('SELECT * FROM support_categories ORDER BY name ASC')
            ->fetchAll();
    }

    public static function allWithArticleCount(): array
    {
        return self::db()
            ->query('SELECT c.*,
                            (SELECT COUNT(*) FROM support_articles a WHERE a.category_id = c.id) AS article_count
                     FROM support_categories c
                     ORDER BY c.name ASC')
            ->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM support_categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM support_categories WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, string $slug, string $color): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO support_categories (name, slug, color) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $slug, $color]);
        return (int)self::db()->lastInsertId();
    }

    public static function update(int $id, string $name, string $slug, string $color): void
    {
        self::db()->prepare(
            'UPDATE support_categories SET name = ?, slug = ?, color = ? WHERE id = ?'
        )->execute([$name, $slug, $color, $id]);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('DELETE FROM support_categories WHERE id = ?')->execute([$id]);
    }
}

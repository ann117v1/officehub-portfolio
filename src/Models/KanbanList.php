<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class KanbanList
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function all(): array
    {
        return self::db()
            ->query('SELECT * FROM kanban_lists WHERE is_archived = 0 ORDER BY position_value ASC, id ASC')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM kanban_lists WHERE id = ? AND is_archived = 0 LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, ?string $description, string $color): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO kanban_lists (name, description, color, position_value)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            $description ?: null,
            self::normalizeColor($color),
            self::nextPosition(),
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('UPDATE kanban_lists SET is_archived = 1 WHERE id = ?')->execute([$id]);
    }

    private static function nextPosition(): float
    {
        $max = self::db()->query('SELECT MAX(position_value) FROM kanban_lists WHERE is_archived = 0')->fetchColumn();
        return ((float)($max ?: 0)) + 65536;
    }

    private static function normalizeColor(string $color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#58a6ff';
    }
}

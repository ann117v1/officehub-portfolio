<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class SupportAttachment
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function forArticle(int $articleId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM support_attachments WHERE article_id = ? ORDER BY created_at ASC'
        );
        $stmt->execute([$articleId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM support_attachments WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO support_attachments (article_id, filename, path, mime_type, size, is_image)
             VALUES (:article_id, :filename, :path, :mime_type, :size, :is_image)'
        );
        $stmt->execute([
            ':article_id' => $data['article_id'],
            ':filename'   => $data['filename'],
            ':path'       => $data['path'],
            ':mime_type'  => $data['mime_type'],
            ':size'       => $data['size'],
            ':is_image'   => $data['is_image'] ? 1 : 0,
        ]);
        return (int)self::db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('DELETE FROM support_attachments WHERE id = ?')->execute([$id]);
    }
}

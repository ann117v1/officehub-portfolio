<?php

//Modelo que maneja los comentarios en los pull requests, incluyendo comentarios generales y específicos de líneas de código, así como la creación de nuevos comentarios
namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class PrComment
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function generalForPr(int $prId): array
    {
        $stmt = self::db()->prepare(
            'SELECT c.*, u.username AS author_name
             FROM pr_comments c
             JOIN users u ON u.id = c.author_id
             WHERE c.pr_id = ? AND c.file_path IS NULL
             ORDER BY c.created_at ASC'
        );
        $stmt->execute([$prId]);
        return $stmt->fetchAll();
    }

    public static function inlineForPr(int $prId): array
    {
        $stmt = self::db()->prepare(
            'SELECT c.*, u.username AS author_name
             FROM pr_comments c
             JOIN users u ON u.id = c.author_id
             WHERE c.pr_id = ? AND c.file_path IS NOT NULL
             ORDER BY c.file_path, c.line_number ASC'
        );
        $stmt->execute([$prId]);
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['file_path']][$row['line_number']][] = $row;
        }
        return $map;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO pr_comments (pr_id, author_id, body, file_path, line_number)
             VALUES (:pr_id, :author_id, :body, :file_path, :line_number)'
        );
        $stmt->execute([
            ':pr_id'       => $data['pr_id'],
            ':author_id'   => $data['author_id'],
            ':body'        => $data['body'],
            ':file_path'   => $data['file_path'] ?? null,
            ':line_number' => $data['line_number'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }
}

<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class SupportArticle
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function search(?int $categoryId = null, string $query = ''): array
    {
        $where = [];
        $params = [];

        if ($categoryId !== null) {
            $where[] = 'a.category_id = ?';
            $params[] = $categoryId;
        }

        if ($query !== '') {
            $like = '%' . $query . '%';
            $where[] = '(a.title LIKE ? OR a.body LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderBy = 'a.updated_at DESC';

        if ($query !== '') {
            $orderBy = 'CASE WHEN a.title LIKE ? THEN 0 ELSE 1 END, a.updated_at DESC';
            $params[] = $like;
        }

        $stmt = self::db()->prepare(
            "SELECT a.*, c.name AS category_name, c.slug AS category_slug, c.color AS category_color,
                    u.username AS author_name,
                    (SELECT COUNT(*) FROM support_attachments att WHERE att.article_id = a.id) AS attachment_count,
                    (SELECT GROUP_CONCAT(att.id ORDER BY att.created_at ASC SEPARATOR ',') FROM support_attachments att WHERE att.article_id = a.id) AS attachment_ids,
                    (SELECT GROUP_CONCAT(att.filename ORDER BY att.created_at ASC SEPARATOR '||') FROM support_attachments att WHERE att.article_id = a.id) AS attachment_names
             FROM support_articles a
             JOIN support_categories c ON c.id = a.category_id
             JOIN users u ON u.id = a.author_id
             {$whereClause}
             ORDER BY {$orderBy}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug, c.color AS category_color,
                    u.username AS author_name
             FROM support_articles a
             JOIN support_categories c ON c.id = a.category_id
             JOIN users u ON u.id = a.author_id
             WHERE a.slug = ? LIMIT 1'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM support_articles WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        if ($exceptId !== null) {
            $stmt = self::db()->prepare('SELECT id FROM support_articles WHERE slug = ? AND id <> ? LIMIT 1');
            $stmt->execute([$slug, $exceptId]);
        } else {
            $stmt = self::db()->prepare('SELECT id FROM support_articles WHERE slug = ? LIMIT 1');
            $stmt->execute([$slug]);
        }

        return (bool)$stmt->fetch();
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO support_articles (category_id, title, slug, body, type, author_id)
             VALUES (:category_id, :title, :slug, :body, :type, :author_id)'
        );
        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':title'       => $data['title'],
            ':slug'        => $data['slug'],
            ':body'        => $data['body'],
            ':type'        => $data['type'],
            ':author_id'   => $data['author_id'],
        ]);
        return (int)self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        self::db()->prepare(
            'UPDATE support_articles
             SET category_id = :category_id, title = :title, slug = :slug, body = :body, type = :type
             WHERE id = :id'
        )->execute([
            ':category_id' => $data['category_id'],
            ':title'       => $data['title'],
            ':slug'        => $data['slug'],
            ':body'        => $data['body'],
            ':type'        => $data['type'],
            ':id'          => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('DELETE FROM support_articles WHERE id = ?')->execute([$id]);
    }
}

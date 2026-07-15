<?php

//Modelo que maneja los pull requests, incluyendo creación, listado, visualización de detalles y gestión de estados (abierto, cerrado, fusionado)

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class PullRequest
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function forRepo(int $repoId, string $status = 'open'): array
    {
        $stmt = self::db()->prepare(
            'SELECT pr.*, u.username AS author_name, r.username AS reviewer_name
             FROM pull_requests pr
             JOIN users u ON u.id = pr.author_id
             LEFT JOIN users r ON r.id = pr.reviewer_id
             WHERE pr.repo_id = ? AND pr.status = ?
             ORDER BY pr.created_at DESC'
        );
        $stmt->execute([$repoId, $status]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT pr.*, u.username AS author_name, r.username AS reviewer_name
             FROM pull_requests pr
             JOIN users u ON u.id = pr.author_id
             LEFT JOIN users r ON r.id = pr.reviewer_id
             WHERE pr.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO pull_requests
             (repo_id, title, description, author_id, source_branch, target_branch)
             VALUES (:repo_id, :title, :description, :author_id, :source_branch, :target_branch)'
        );
        $stmt->execute([
            ':repo_id'       => $data['repo_id'],
            ':title'         => $data['title'],
            ':description'   => $data['description'] ?? null,
            ':author_id'     => $data['author_id'],
            ':source_branch' => $data['source_branch'],
            ':target_branch' => $data['target_branch'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function setStatus(int $id, string $status): void
    {
        if ($status === 'merged') {
            self::db()->prepare(
                'UPDATE pull_requests SET status = ?, merged_at = NOW() WHERE id = ?'
            )->execute([$status, $id]);
        } else {
            self::db()->prepare(
                'UPDATE pull_requests SET status = ? WHERE id = ?'
            )->execute([$status, $id]);
        }
    }

    public static function assignReviewer(int $prId, int $reviewerId): void
    {
        self::db()->prepare(
            'UPDATE pull_requests SET reviewer_id = ? WHERE id = ?'
        )->execute([$reviewerId, $prId]);
    }
}

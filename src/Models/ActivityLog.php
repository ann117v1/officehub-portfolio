<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class ActivityLog
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function log(int $userId, string $action, ?int $repoId = null, ?array $metadata = null, ?int $areaId = null): void
    {
        // Si no se pasa areaId explícito, intentar obtenerlo del repo
        if ($areaId === null && $repoId !== null) {
            $stmt = self::db()->prepare('SELECT area_id FROM repositories WHERE id = ? LIMIT 1');
            $stmt->execute([$repoId]);
            $repo = $stmt->fetch();
            $areaId = $repo ? $repo['area_id'] : null;
        }

        self::db()->prepare(
            'INSERT INTO activity_log (user_id, action, repo_id, area_id, metadata)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $userId,
            $action,
            $repoId,
            $areaId,
            $metadata ? json_encode($metadata) : null,
        ]);
    }

    public static function recent(int $limit = 20, ?int $areaId = null, ?int $userId = null): array
    {
        $where = [];
        $params = [];

        if ($areaId !== null) {
            $where[] = 'al.area_id = ?';
            $params[] = $areaId;
        }

        if ($userId !== null) {
            $where[] = "(r.id IS NULL
                         OR r.visibility = 'internal'
                         OR r.owner_id = ?
                         OR EXISTS (
                             SELECT 1
                             FROM repo_permissions rp
                             WHERE rp.repo_id = r.id AND rp.user_id = ?
                         ))";
            $params[] = $userId;
            $params[] = $userId;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT al.*, u.username, r.name AS repo_name
                FROM activity_log al
                JOIN users u ON u.id = al.user_id
                LEFT JOIN repositories r ON r.id = al.repo_id
                {$whereClause}
                ORDER BY al.created_at DESC
                LIMIT " . max(1, (int)$limit);

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function forRepo(int $repoId, int $limit = 30): array
    {
        $stmt = self::db()->prepare(
            'SELECT al.*, u.username
             FROM activity_log al
             JOIN users u ON u.id = al.user_id
             WHERE al.repo_id = ?
             ORDER BY al.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$repoId, $limit]);
        return $stmt->fetchAll();
    }
}

<?php

// Modelo para gestionar los permisos de acceso a los repositorios,
// incluyendo lectura, escritura y administración de permisos para usuarios específicos.
namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class RepoPermission
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function getLevel(int $repoId, int $userId): ?string
    {
        $stmt = self::db()->prepare(
            'SELECT permission FROM repo_permissions
             WHERE repo_id = ? AND user_id = ? LIMIT 1'
        );

        $stmt->execute([$repoId, $userId]);
        $row = $stmt->fetch();

        return $row ? $row['permission'] : null;
    }

    public static function canWrite(int $repoId, int $userId, string $userRole): bool
    {
        if ($userRole === 'viewer') {
            return false;
        }

        $repo = Repository::findById($repoId);

        if ($repo && (int) $repo['owner_id'] === $userId) {
            return true;
        }

        $level = self::getLevel($repoId, $userId);

        if ($repo && $repo['visibility'] === 'private') {
            return $userRole !== 'viewer' && in_array($level, ['write', 'admin'], true);
        }

        if (in_array($userRole, ['admin', 'developer'], true)) {
            return true;
        }

        return in_array($level, ['write', 'admin'], true);
    }

    public static function canAdmin(int $repoId, int $userId, string $userRole): bool
    {
        if ($userRole === 'viewer') {
            return false;
        }

        $repo = Repository::findById($repoId);

        if ($repo && (int) $repo['owner_id'] === $userId) {
            return true;
        }

        $level = self::getLevel($repoId, $userId);

        if ($repo && $repo['visibility'] === 'private') {
            return $userRole !== 'viewer' && $level === 'admin';
        }

        if ($userRole === 'admin') {
            return true;
        }

        return $level === 'admin';
    }

    public static function canRead(int $repoId, int $userId, string $userRole, string $visibility): bool
    {
        if ($visibility === 'internal') {
            return true;
        }

        $repo = Repository::findById($repoId);

        if (!$repo) {
            return false;
        }

        if ((int) $repo['owner_id'] === $userId) {
            return true;
        }

        return self::getLevel($repoId, $userId) !== null;
    }

    public static function forRepo(int $repoId): array
    {
        $stmt = self::db()->prepare(
            'SELECT rp.*, u.username, u.email
             FROM repo_permissions rp
             JOIN users u ON u.id = rp.user_id
             WHERE rp.repo_id = ?
             ORDER BY rp.granted_at DESC'
        );

        $stmt->execute([$repoId]);

        return $stmt->fetchAll();
    }

    public static function set(int $repoId, int $userId, string $permission): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO repo_permissions (repo_id, user_id, permission)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE permission = VALUES(permission)'
        );

        $stmt->execute([$repoId, $userId, $permission]);
    }

    public static function remove(int $repoId, int $userId): void
    {
        self::db()->prepare(
            'DELETE FROM repo_permissions WHERE repo_id = ? AND user_id = ?'
        )->execute([$repoId, $userId]);
    }
}

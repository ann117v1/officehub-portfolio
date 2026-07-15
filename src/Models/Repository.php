<?php

//Manjaría el acceso a los repositorios, incluyendo creación, eliminación y listado de repositorios visibles para el usuario
namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class Repository
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function all(): array
    {
        return self::db()
            ->query('SELECT r.*, u.username AS owner_name
                     FROM repositories r
                     JOIN users u ON u.id = r.owner_id
                     ORDER BY r.created_at DESC')
            ->fetchAll();
    }

    public static function visibleFor(int $userId, string $role, ?int $areaId = null): array
    {
        $where = [
            "(r.visibility = 'internal'
              OR r.owner_id = ?
              OR EXISTS (
                  SELECT 1
                  FROM repo_permissions rp
                  WHERE rp.repo_id = r.id AND rp.user_id = ?
              ))",
        ];
        $params = [$userId, $userId];

        if ($areaId !== null) {
            $where[] = 'r.area_id = ?';
            $params[] = $areaId;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT r.*, u.username AS owner_name
                FROM repositories r
                JOIN users u ON u.id = r.owner_id
                {$whereClause}
                ORDER BY r.created_at DESC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function findByName(string $name): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT r.*, u.username AS owner_name
             FROM repositories r
             JOIN users u ON u.id = r.owner_id
             WHERE r.name = ? LIMIT 1'
        );
        $stmt->execute([$name]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM repositories WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO repositories (name, description, owner_id, visibility, path_on_disk, website_url, area_id)
     VALUES (:name, :description, :owner_id, :visibility, :path_on_disk, :website_url, :area_id)'
        );
        $stmt->execute([
            ':name'         => $data['name'],
            ':description'  => $data['description'] ?? null,
            ':owner_id'     => $data['owner_id'],
            ':visibility'   => $data['visibility'] ?? 'internal',
            ':path_on_disk' => $data['path_on_disk'],
            ':website_url'  => $data['website_url'] ?? null,
            ':area_id'      => $data['area_id'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function updateArea(int $id, ?int $areaId): void
    {
        self::db()->prepare(
            'UPDATE repositories SET area_id = ? WHERE id = ?'
        )->execute([$areaId, $id]);
    }

    public static function updateWebsiteUrl(int $id, ?string $url): void
    {
        self::db()->prepare(
            'UPDATE repositories SET website_url = ? WHERE id = ?'
        )->execute([$url ?: null, $id]);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare(
            'DELETE FROM repositories WHERE id = ?'
        )->execute([$id]);
    }

    public static function updateDefaultBranch(int $id, string $branch): void
    {
        self::db()->prepare(
            'UPDATE repositories SET default_branch = ? WHERE id = ?'
        )->execute([$branch, $id]);
    }

    public static function clearArea(int $areaId): void
    {
        self::db()->prepare(
            'UPDATE repositories SET area_id = NULL WHERE area_id = ?'
        )->execute([$areaId]);
    }

    public static function updateDescription(string $name, string $description): void
{
    $stmt = self::db()->prepare(
        "UPDATE repositories SET description = ? WHERE name = ?"
    );

    $stmt->execute([$description, $name]);
}
}

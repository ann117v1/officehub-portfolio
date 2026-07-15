<?php

//Modelo de usuario para autenticación y gestión de usuarios en la aplicación

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;
use PDOException;

class User
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function findByUsername(string $username): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function findActiveByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM users
             WHERE LOWER(email) = LOWER(?) AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        try {
            return self::db()
                ->query(
                'SELECT id, username, email, display_title, role, repo_access, support_role,
                        notifications_enabled, commit_notifications_enabled,
                        board_email_notifications_enabled, commit_email_notifications_enabled,
                        created_at, is_active
                 FROM users
                 ORDER BY created_at DESC'
                )
                ->fetchAll();
        } catch (PDOException $exception) {
            return self::db()
                ->query(
                    'SELECT id, username, email, role, repo_access, support_role,
                            notifications_enabled, commit_notifications_enabled,
                            created_at, is_active
                     FROM users
                     ORDER BY created_at DESC'
                )
                ->fetchAll();
        }
    }

    public static function create(
        string $username,
        string $email,
        string $displayTitle,
        string $password,
        string $role = 'developer',
        int $repoAccess = 1,
        string $supportRole = 'none',
        int $notificationsEnabled = 1,
        int $commitNotificationsEnabled = 0,
        int $boardEmailNotificationsEnabled = 0,
        int $commitEmailNotificationsEnabled = 0
    ): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = self::db()->prepare(
            'INSERT INTO users
                (username, email, display_title, password_hash, role, repo_access, support_role,
                 notifications_enabled, commit_notifications_enabled,
                 board_email_notifications_enabled, commit_email_notifications_enabled)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $username,
            $email,
            self::normalizeDisplayTitle($displayTitle),
            $hash,
            $role,
            $repoAccess,
            $supportRole,
            $notificationsEnabled,
            $commitNotificationsEnabled,
            $boardEmailNotificationsEnabled,
            $commitEmailNotificationsEnabled,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function toggleActive(int $id): void
    {
        self::db()->prepare(
            'UPDATE users SET is_active = NOT is_active WHERE id = ?'
        )->execute([$id]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function updateRole(int $id, string $role): void
    {
        self::db()->prepare(
            'UPDATE users SET role = ? WHERE id = ?'
        )->execute([$role, $id]);
    }

    public static function updateAccess(
        int $id,
        string $displayTitle,
        int $repoAccess,
        string $supportRole,
        int $notificationsEnabled = 1,
        int $commitNotificationsEnabled = 0,
        int $boardEmailNotificationsEnabled = 0,
        int $commitEmailNotificationsEnabled = 0
    ): void
    {
        self::db()->prepare(
            'UPDATE users
             SET repo_access = ?,
                 display_title = ?,
                 support_role = ?,
                 notifications_enabled = ?,
                 commit_notifications_enabled = ?,
                 board_email_notifications_enabled = ?,
                 commit_email_notifications_enabled = ?
             WHERE id = ?'
        )->execute([
            $repoAccess,
            self::normalizeDisplayTitle($displayTitle),
            $supportRole,
            $notificationsEnabled,
            $commitNotificationsEnabled,
            $boardEmailNotificationsEnabled,
            $commitEmailNotificationsEnabled,
            $id,
        ]);
    }

    public static function updatePermissions(
        int $id,
        string $displayTitle,
        string $role,
        int $repoAccess,
        string $supportRole,
        int $notificationsEnabled = 1,
        int $commitNotificationsEnabled = 0,
        int $boardEmailNotificationsEnabled = 0,
        int $commitEmailNotificationsEnabled = 0
    ): void
    {
        self::db()->prepare(
            'UPDATE users
             SET role = ?,
                 display_title = ?,
                 repo_access = ?,
                 support_role = ?,
                 notifications_enabled = ?,
                 commit_notifications_enabled = ?,
                 board_email_notifications_enabled = ?,
                 commit_email_notifications_enabled = ?
             WHERE id = ?'
        )->execute([
            $role,
            self::normalizeDisplayTitle($displayTitle),
            $repoAccess,
            $supportRole,
            $notificationsEnabled,
            $commitNotificationsEnabled,
            $boardEmailNotificationsEnabled,
            $commitEmailNotificationsEnabled,
            $id,
        ]);
    }

    public static function delete(int $id): void
    {
        self::db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }

    private static function normalizeDisplayTitle(string $displayTitle): ?string
    {
        $displayTitle = trim($displayTitle);

        if ($displayTitle === '') {
            return null;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($displayTitle, 0, 100);
        }

        return substr($displayTitle, 0, 100);
    }
}

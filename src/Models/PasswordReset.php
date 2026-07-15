<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;
use PDOException;
use Throwable;

class PasswordReset
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function hasRecentRequest(int $userId): bool
    {
        $stmt = self::db()->prepare(
            'SELECT 1
             FROM password_reset_tokens
             WHERE user_id = ?
               AND used_at IS NULL
               AND created_at >= DATE_SUB(NOW(), INTERVAL 120 SECOND)
             LIMIT 1'
        );
        $stmt->execute([$userId]);

        return (bool)$stmt->fetchColumn();
    }

    public static function create(int $userId, string $rawToken, ?string $requestedIp): void
    {
        $db = self::db();
        $db->prepare(
            'UPDATE password_reset_tokens
             SET used_at = NOW()
             WHERE user_id = ? AND used_at IS NULL'
        )->execute([$userId]);

        $stmt = $db->prepare(
            'INSERT INTO password_reset_tokens
                (user_id, token_hash, expires_at, requested_ip)
             VALUES
                (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), ?)'
        );
        $stmt->execute([
            $userId,
            self::hash($rawToken),
            $requestedIp,
        ]);
    }

    public static function findValid(string $rawToken): ?array
    {
        if ($rawToken === '') {
            return null;
        }

        $stmt = self::db()->prepare(
            'SELECT prt.id, prt.user_id, prt.expires_at, u.email
             FROM password_reset_tokens prt
             INNER JOIN users u ON u.id = prt.user_id
             WHERE prt.token_hash = ?
               AND prt.used_at IS NULL
               AND prt.expires_at > NOW()
               AND u.is_active = 1
             LIMIT 1'
        );
        $stmt->execute([self::hash($rawToken)]);

        return $stmt->fetch() ?: null;
    }

    public static function invalidatePending(int $userId): void
    {
        self::db()->prepare(
            'UPDATE password_reset_tokens
             SET used_at = NOW()
             WHERE user_id = ? AND used_at IS NULL'
        )->execute([$userId]);
    }

    public static function consumeAndUpdatePassword(string $rawToken, string $password): bool
    {
        $db = self::db();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'SELECT prt.id, prt.user_id
                 FROM password_reset_tokens prt
                 INNER JOIN users u ON u.id = prt.user_id
                 WHERE prt.token_hash = ?
                   AND prt.used_at IS NULL
                   AND prt.expires_at > NOW()
                   AND u.is_active = 1
                 LIMIT 1
                 FOR UPDATE'
            );
            $stmt->execute([self::hash($rawToken)]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $db->rollBack();
                return false;
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            try {
                $db->prepare('UPDATE users SET password_hash = ?, session_version = session_version + 1 WHERE id = ?')
                    ->execute([$passwordHash, (int)$reset['user_id']]);
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '42S22') {
                    throw $exception;
                }

                $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([$passwordHash, (int)$reset['user_id']]);
            }

            $db->prepare(
                'UPDATE password_reset_tokens
                 SET used_at = NOW()
                 WHERE user_id = ? AND used_at IS NULL'
            )->execute([(int)$reset['user_id']]);

            $db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    public static function cleanupExpired(): void
    {
        self::db()->exec(
            'DELETE FROM password_reset_tokens
             WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                OR used_at < DATE_SUB(NOW(), INTERVAL 7 DAY)'
        );
    }

    private static function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}

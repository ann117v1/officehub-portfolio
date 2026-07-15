<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use OfficeHub\Services\MailService;
use PDO;
use Throwable;

class NotificationEmail
{
    private const MAX_ATTEMPTS = 3;

    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function queueForUsers(
        array $userIds,
        string $type,
        string $subject,
        string $body,
        string $link,
        string $dedupeKey
    ): void {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if (empty($userIds)) {
            return;
        }

        $stmt = self::db()->prepare(
            'INSERT IGNORE INTO notification_email_queue
                (user_id, type, subject, body, link, dedupe_key)
             VALUES
                (?, ?, ?, ?, ?, ?)'
        );

        foreach ($userIds as $userId) {
            $stmt->execute([$userId, $type, $subject, $body, $link, $dedupeKey]);

            if ($stmt->rowCount() === 0) {
                self::resetFailedDuplicate($userId, $type, $subject, $body, $link, $dedupeKey);
            }
        }
    }

    private static function resetFailedDuplicate(
        int $userId,
        string $type,
        string $subject,
        string $body,
        string $link,
        string $dedupeKey
    ): void {
        self::db()->prepare(
            "UPDATE notification_email_queue
             SET type = ?,
                 subject = ?,
                 body = ?,
                 link = ?,
                 status = 'pending',
                 attempts = 0,
                 next_attempt_at = CURRENT_TIMESTAMP,
                 last_error = NULL
             WHERE user_id = ?
               AND dedupe_key = ?
               AND status <> 'sent'"
        )->execute([$type, $subject, $body, $link, $userId, $dedupeKey]);
    }

    public static function processPending(int $limit = 5): void
    {
        $limit = max(1, min(20, $limit));

        self::db()->exec(
            "UPDATE notification_email_queue
             SET status = 'failed',
                 next_attempt_at = CURRENT_TIMESTAMP,
                 last_error = 'Envio interrumpido; se reintentara.'
             WHERE status = 'processing'
               AND attempts < " . self::MAX_ATTEMPTS . "
               AND updated_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 MINUTE)"
        );

        $rows = self::db()->query(
            "SELECT q.*, u.email, u.username
             FROM notification_email_queue q
             INNER JOIN users u ON u.id = q.user_id
             WHERE q.status IN ('pending', 'failed')
               AND q.attempts < " . self::MAX_ATTEMPTS . "
               AND q.next_attempt_at <= CURRENT_TIMESTAMP
               AND u.is_active = 1
               AND u.email <> ''
             ORDER BY q.created_at ASC, q.id ASC
             LIMIT {$limit}"
        )->fetchAll();

        foreach ($rows as $row) {
            self::processRow($row);
        }
    }

    private static function processRow(array $row): void
    {
        $id = (int)$row['id'];
        $claimed = self::db()->prepare(
            "UPDATE notification_email_queue
             SET status = 'processing', attempts = attempts + 1
             WHERE id = ? AND status IN ('pending', 'failed')"
        );
        $claimed->execute([$id]);

        if ($claimed->rowCount() !== 1) {
            return;
        }

        try {
            MailService::sendNotification(
                (string)$row['email'],
                (string)$row['username'],
                (string)$row['subject'],
                (string)$row['body'],
                self::absoluteUrl((string)($row['link'] ?? ''))
            );

            self::db()->prepare(
                "UPDATE notification_email_queue
                 SET status = 'sent',
                     sent_at = CURRENT_TIMESTAMP,
                     last_error = NULL
                 WHERE id = ?"
            )->execute([$id]);
        } catch (Throwable $exception) {
            $attempts = (int)$row['attempts'] + 1;
            $retryMinutes = match ($attempts) {
                1 => 2,
                2 => 10,
                default => 30,
            };
            $nextAttempt = date('Y-m-d H:i:s', time() + ($retryMinutes * 60));
            $error = mb_substr(MailService::errorSummary($exception), 0, 1000);

            self::db()->prepare(
                "UPDATE notification_email_queue
                 SET status = 'failed',
                     next_attempt_at = ?,
                     last_error = ?
                 WHERE id = ?"
            )->execute([$nextAttempt, $error, $id]);

            error_log("OfficeHub email notification {$id}: {$error}");
        }
    }

    private static function absoluteUrl(string $link): string
    {
        $config = require BASE_PATH . '/config/app.php';
        $baseUrl = rtrim((string)($config['url'] ?? ''), '/');

        if ($link === '') {
            return $baseUrl;
        }

        if (preg_match('#^https?://#i', $link)) {
            return $link;
        }

        return $baseUrl . '/' . ltrim($link, '/');
    }
}

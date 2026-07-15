<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use OfficeHub\Services\GitService;
use OfficeHub\Services\MailService;
use PDO;
use Throwable;

class Notification
{
    private const COMMIT_SYNC_SECONDS = 8;

    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function unreadFor(int $userId, int $limit = 8): array
    {
        $stmt = self::db()->prepare(
            'SELECT n.*, actor.username AS actor_name
             FROM notifications n
             LEFT JOIN users actor ON actor.id = n.actor_id
             WHERE n.user_id = ? AND n.read_at IS NULL
             ORDER BY n.created_at DESC, n.id DESC
             LIMIT ' . max(1, (int)$limit)
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL'
        );
        $stmt->execute([$userId]);

        return (int)$stmt->fetchColumn();
    }

    public static function markRead(int $id, int $userId): void
    {
        self::db()->prepare(
            'UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?'
        )->execute([$id, $userId]);
    }

    public static function markAllRead(int $userId): void
    {
        self::db()->prepare(
            'UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE user_id = ? AND read_at IS NULL'
        )->execute([$userId]);
    }

    public static function notifyBoardCardCreated(int $cardId, int $listId, ?string $title, array $actor, array $assigneeIds): void
    {
        $list = KanbanList::find($listId);
        $actorName = $actor['username'] ?? 'Alguien';
        $cardTitle = trim((string)$title) !== '' ? trim((string)$title) : 'Tarea sin titulo';
        $listName = $list['name'] ?? 'Tablero';

        if (!empty($assigneeIds)) {
            $userIds = self::filterEnabledUsers($assigneeIds);
            $notificationTitle = 'Nueva tarea asignada';
            $body = "{$actorName} te asigno \"{$cardTitle}\" en {$listName}.";
        } else {
            $userIds = self::enabledUserIds();
            $notificationTitle = 'Nueva tarea en tablero';
            $body = "{$actorName} agrego \"{$cardTitle}\" en {$listName}.";
        }

        $dedupeKey = 'board_card_created:' . $cardId;

        self::createForUsers(
            $userIds,
            'board_card_created',
            $notificationTitle,
            $body,
            '/tablero',
            isset($actor['id']) ? (int)$actor['id'] : null,
            [
                'card_id' => $cardId,
                'list_id' => $listId,
            ],
            $dedupeKey
        );

        try {
            $emailUserIds = !empty($assigneeIds)
                ? self::filterUsersByFlag($assigneeIds, 'board_email_notifications_enabled')
                : self::enabledEmailUserIds('board_email_notifications_enabled');

            NotificationEmail::queueForUsers(
                $emailUserIds,
                'board_card_created',
                $notificationTitle,
                $body,
                '/tablero',
                $dedupeKey
            );
            NotificationEmail::processPending(2);
        } catch (Throwable $exception) {
            error_log('OfficeHub board email notification: ' . self::mailError($exception));
        }
    }

    public static function notifyBoardCardCommented(int $cardId, int $commentId, string $commentBody, array $actor, array $card, ?array $replyToComment = null): void
    {
        $actorId = isset($actor['id']) ? (int)$actor['id'] : 0;
        $actorName = $actor['username'] ?? 'Alguien';
        $cardTitle = trim((string)($card['title'] ?? '')) !== '' ? trim((string)$card['title']) : 'Tarea sin titulo';
        $targetUserIds = [];
        $notificationTitle = 'Nuevo comentario en tarjeta';
        $body = "{$actorName} comento en \"{$cardTitle}\": " . self::shortText($commentBody);

        if ($replyToComment && !empty($replyToComment['user_id'])) {
            $replyUserId = (int)$replyToComment['user_id'];

            if ($replyUserId !== $actorId) {
                $targetUserIds = [$replyUserId];
                $notificationTitle = 'Respuesta en tarjeta';
                $body = "{$actorName} respondio tu comentario en \"{$cardTitle}\": " . self::shortText($commentBody);
            }
        } else {
            $assignees = KanbanCard::assigneesForCards([$cardId]);
            $targetUserIds = array_map(
                'intval',
                array_column($assignees[$cardId] ?? [], 'id')
            );
            $targetUserIds = array_values(array_filter($targetUserIds, fn (int $userId): bool => $userId !== $actorId));
        }

        $targetUserIds = self::filterEnabledUsers($targetUserIds);

        self::createForUsers(
            $targetUserIds,
            $replyToComment ? 'board_card_comment_reply' : 'board_card_comment',
            $notificationTitle,
            $body,
            '/tablero',
            $actorId > 0 ? $actorId : null,
            [
                'card_id' => $cardId,
                'comment_id' => $commentId,
                'reply_to_comment_id' => $replyToComment['id'] ?? null,
            ],
            'board_card_comment:' . $commentId
        );
    }

    public static function syncCommitNotifications(): void
    {
        if (self::shouldRunCommitSync()) {
            foreach (Repository::all() as $repo) {
                try {
                    self::syncRepositoryCommits($repo);
                } catch (Throwable $exception) {
                    continue;
                }
            }
        }

        try {
            NotificationEmail::processPending(2);
        } catch (Throwable $exception) {
            error_log('OfficeHub email queue: ' . $exception->getMessage());
        }
    }

    private static function syncRepositoryCommits(array $repo): void
    {
        $repoId = (int)$repo['id'];
        $git = GitService::fromRepo($repo);

        if ($git->isEmpty()) {
            self::touchRepoCommitState($repoId, null);
            return;
        }

        $branch = trim((string)($repo['default_branch'] ?? '')) ?: $git->defaultBranch();
        $latest = $git->latestCommit($branch);

        if (!$latest && $branch !== 'HEAD') {
            $branch = 'HEAD';
            $latest = $git->latestCommit($branch);
        }

        if (!$latest || empty($latest['hash'])) {
            self::touchRepoCommitState($repoId, null);
            return;
        }

        $state = self::repoCommitState($repoId);
        $latestHash = (string)$latest['hash'];

        if (!$state || empty($state['last_commit_hash'])) {
            self::touchRepoCommitState($repoId, $latestHash);
            return;
        }

        if (hash_equals((string)$state['last_commit_hash'], $latestHash)) {
            self::touchRepoCommitState($repoId, $latestHash);
            return;
        }

        $commits = $git->commitsAfter($branch, (string)$state['last_commit_hash'], 20);
        if (empty($commits)) {
            $commits = [$latest];
        }

        foreach (array_reverse($commits) as $commit) {
            self::notifyCommit($repo, $commit);
        }

        self::touchRepoCommitState($repoId, $latestHash);
    }

    private static function notifyCommit(array $repo, array $commit): void
    {
        $hash = (string)($commit['hash'] ?? '');
        if ($hash === '') {
            return;
        }

        $authorEmail = strtolower(trim((string)($commit['email'] ?? '')));
        $repoName = (string)($repo['name'] ?? 'repositorio');
        $message = trim((string)($commit['message'] ?? 'Commit sin mensaje'));
        $authorName = trim((string)($commit['author'] ?? '')) ?: 'Alguien';
        $shortHash = substr($hash, 0, 7);
        $userIds = self::enabledCommitUserIdsForRepository($repo, $authorEmail);
        $notificationTitle = 'Nuevo commit en ' . $repoName;
        $body = "{$message} ({$shortHash})";
        $emailBody = "{$authorName} realizo un commit en {$repoName}: \"{$message}\" ({$shortHash}).";
        $link = '/repos/' . rawurlencode($repoName) . '/commit/' . rawurlencode($hash);
        $dedupeKey = 'repo_commit:' . (int)$repo['id'] . ':' . $hash;

        self::createForUsers(
            $userIds,
            'repo_commit',
            $notificationTitle,
            $body,
            $link,
            null,
            [
                'repo_id' => (int)$repo['id'],
                'repo_name' => $repoName,
                'commit_hash' => $hash,
                'author' => $commit['author'] ?? null,
                'author_email' => $commit['email'] ?? null,
            ],
            $dedupeKey
        );

        try {
            NotificationEmail::queueForUsers(
                self::enabledCommitEmailUserIdsForRepository($repo, $authorEmail),
                'repo_commit',
                $notificationTitle,
                $emailBody,
                $link,
                $dedupeKey
            );
            NotificationEmail::processPending(2);
        } catch (Throwable $exception) {
            error_log('OfficeHub commit email notification: ' . self::mailError($exception));
        }
    }

    private static function mailError(Throwable $exception): string
    {
        return MailService::errorSummary($exception);
    }

    private static function shortText(string $value, int $limit = 120): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        if ($text === '') {
            return 'Comentario sin texto';
        }

        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit - 3) . '...';
    }

    private static function createForUsers(array $userIds, string $type, string $title, string $body, string $link, ?int $actorId, array $metadata, string $dedupeKey): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if (empty($userIds)) {
            return;
        }

        $stmt = self::db()->prepare(
            'INSERT IGNORE INTO notifications
                (user_id, actor_id, type, title, body, link, metadata, dedupe_key)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $json = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        foreach ($userIds as $userId) {
            $stmt->execute([
                $userId,
                $actorId,
                $type,
                $title,
                $body,
                $link,
                $json ?: null,
                $dedupeKey,
            ]);
        }
    }

    private static function enabledUserIds(): array
    {
        return array_map(
            'intval',
            self::db()
                ->query('SELECT id FROM users WHERE is_active = 1 AND notifications_enabled = 1')
                ->fetchAll(PDO::FETCH_COLUMN)
        );
    }

    private static function enabledCommitUserIdsByEmailExclusion(string $email): array
    {
        $params = [];
        $where = 'WHERE is_active = 1 AND notifications_enabled = 1 AND commit_notifications_enabled = 1';

        if ($email !== '') {
            $where .= ' AND LOWER(email) <> ?';
            $params[] = $email;
        }

        $stmt = self::db()->prepare("SELECT id FROM users {$where}");
        $stmt->execute($params);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function enabledCommitUserIdsForRepository(array $repo, string $email): array
    {
        if (($repo['visibility'] ?? 'internal') !== 'private') {
            return self::enabledCommitUserIdsByEmailExclusion($email);
        }

        $params = [(int)$repo['id'], (int)$repo['owner_id']];
        $where = "WHERE u.is_active = 1
                  AND u.notifications_enabled = 1
                  AND u.commit_notifications_enabled = 1
                  AND (u.id = ? OR rp.user_id IS NOT NULL)";

        if ($email !== '') {
            $where .= ' AND LOWER(u.email) <> ?';
            $params[] = $email;
        }

        $stmt = self::db()->prepare(
            "SELECT DISTINCT u.id
             FROM users u
             LEFT JOIN repo_permissions rp
               ON rp.user_id = u.id AND rp.repo_id = ?
             {$where}"
        );

        // The repository id belongs to the JOIN; the owner id belongs to the WHERE.
        $stmt->execute([$params[0], $params[1], ...array_slice($params, 2)]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function enabledCommitEmailUserIdsForRepository(array $repo, string $email): array
    {
        if (($repo['visibility'] ?? 'internal') !== 'private') {
            return self::enabledEmailUserIds('commit_email_notifications_enabled', $email);
        }

        $params = [(int)$repo['id'], (int)$repo['owner_id']];
        $where = "WHERE u.is_active = 1
                  AND u.commit_email_notifications_enabled = 1
                  AND u.email <> ''
                  AND (u.id = ? OR rp.user_id IS NOT NULL)";

        if ($email !== '') {
            $where .= ' AND LOWER(u.email) <> ?';
            $params[] = $email;
        }

        $stmt = self::db()->prepare(
            "SELECT DISTINCT u.id
             FROM users u
             LEFT JOIN repo_permissions rp
               ON rp.user_id = u.id AND rp.repo_id = ?
             {$where}"
        );
        $stmt->execute([$params[0], $params[1], ...array_slice($params, 2)]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function enabledEmailUserIds(string $flag, string $excludedEmail = ''): array
    {
        $flag = self::emailFlag($flag);
        $params = [];
        $where = "WHERE is_active = 1 AND {$flag} = 1 AND email <> ''";

        if ($excludedEmail !== '') {
            $where .= ' AND LOWER(email) <> ?';
            $params[] = strtolower(trim($excludedEmail));
        }

        $stmt = self::db()->prepare("SELECT id FROM users {$where}");
        $stmt->execute($params);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function filterUsersByFlag(array $userIds, string $flag): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return [];
        }

        $flag = self::emailFlag($flag);
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT id
             FROM users
             WHERE is_active = 1
               AND {$flag} = 1
               AND email <> ''
               AND id IN ({$placeholders})"
        );
        $stmt->execute($userIds);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function emailFlag(string $flag): string
    {
        $allowed = [
            'board_email_notifications_enabled',
            'commit_email_notifications_enabled',
        ];

        if (!in_array($flag, $allowed, true)) {
            throw new \InvalidArgumentException('Preferencia de correo invalida.');
        }

        return $flag;
    }

    private static function filterEnabledUsers(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT id FROM users WHERE is_active = 1 AND notifications_enabled = 1 AND id IN ({$placeholders})"
        );
        $stmt->execute($userIds);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function shouldRunCommitSync(): bool
    {
        $key = 'commit_sync_last_checked';
        $stmt = self::db()->prepare('SELECT state_value, updated_at FROM notification_state WHERE state_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $state = $stmt->fetch();

        if ($state && strtotime((string)$state['updated_at']) > time() - self::COMMIT_SYNC_SECONDS) {
            return false;
        }

        self::db()->prepare(
            'INSERT INTO notification_state (state_key, state_value, updated_at)
             VALUES (?, ?, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE state_value = VALUES(state_value), updated_at = CURRENT_TIMESTAMP'
        )->execute([$key, (string)time()]);

        return true;
    }

    private static function repoCommitState(int $repoId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM notification_commit_state WHERE repo_id = ? LIMIT 1');
        $stmt->execute([$repoId]);
        return $stmt->fetch() ?: null;
    }

    private static function touchRepoCommitState(int $repoId, ?string $hash): void
    {
        self::db()->prepare(
            'INSERT INTO notification_commit_state (repo_id, last_commit_hash, checked_at)
             VALUES (?, ?, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE last_commit_hash = VALUES(last_commit_hash), checked_at = CURRENT_TIMESTAMP'
        )->execute([$repoId, $hash]);
    }
}

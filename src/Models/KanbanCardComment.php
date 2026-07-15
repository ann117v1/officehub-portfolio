<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class KanbanCardComment
{
    private static ?bool $hasReplyColumn = null;

    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function forCard(int $cardId): array
    {
        $stmt = self::db()->prepare(self::commentsSelectSql('c.card_id = ?') . ' ORDER BY c.created_at ASC, c.id ASC');
        $stmt->execute([$cardId]);

        return $stmt->fetchAll();
    }

    public static function forCards(array $cardIds): array
    {
        $cardIds = array_values(array_unique(array_filter(array_map('intval', $cardIds))));

        if (empty($cardIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cardIds), '?'));
        $stmt = self::db()->prepare(self::commentsSelectSql("c.card_id IN ({$placeholders})") . ' ORDER BY c.card_id ASC, c.created_at ASC, c.id ASC');
        $stmt->execute($cardIds);

        $comments = [];
        foreach ($stmt->fetchAll() as $row) {
            $comments[(int)$row['card_id']][] = $row;
        }

        return $comments;
    }

    public static function create(int $cardId, ?int $userId, string $body, bool $isFinal, ?int $replyToCommentId = null): int
    {
        $replyToCommentId = $replyToCommentId !== null && $replyToCommentId > 0 ? $replyToCommentId : null;

        if (self::hasReplyColumn()) {
            $stmt = self::db()->prepare(
                'INSERT INTO kanban_card_comments (card_id, user_id, body, is_final, reply_to_comment_id)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $cardId,
                $userId,
                $body,
                $isFinal ? 1 : 0,
                $replyToCommentId,
            ]);
        } else {
            $stmt = self::db()->prepare(
                'INSERT INTO kanban_card_comments (card_id, user_id, body, is_final)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $cardId,
                $userId,
                $body,
                $isFinal ? 1 : 0,
            ]);
        }

        return (int) self::db()->lastInsertId();
    }

    public static function findForCard(int $commentId, int $cardId): ?array
    {
        if ($commentId <= 0 || $cardId <= 0) {
            return null;
        }

        $stmt = self::db()->prepare(self::commentsSelectSql('c.id = ? AND c.card_id = ?') . ' LIMIT 1');
        $stmt->execute([$commentId, $cardId]);

        return $stmt->fetch() ?: null;
    }

    public static function hasFinalComment(int $cardId): bool
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*)
             FROM kanban_card_comments
             WHERE card_id = ?
               AND is_final = 1
               AND TRIM(body) <> ""'
        );
        $stmt->execute([$cardId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private static function commentsSelectSql(string $where): string
    {
        if (self::hasReplyColumn()) {
            return "SELECT c.*,
                           u.username AS author_name,
                           reply.body AS reply_body,
                           reply.user_id AS reply_user_id,
                           reply_user.username AS reply_author_name
                    FROM kanban_card_comments c
                    LEFT JOIN users u ON u.id = c.user_id
                    LEFT JOIN kanban_card_comments reply ON reply.id = c.reply_to_comment_id
                    LEFT JOIN users reply_user ON reply_user.id = reply.user_id
                    WHERE {$where}";
        }

        return "SELECT c.*,
                       NULL AS reply_to_comment_id,
                       u.username AS author_name,
                       NULL AS reply_body,
                       NULL AS reply_user_id,
                       NULL AS reply_author_name
                FROM kanban_card_comments c
                LEFT JOIN users u ON u.id = c.user_id
                WHERE {$where}";
    }

    private static function hasReplyColumn(): bool
    {
        if (self::$hasReplyColumn !== null) {
            return self::$hasReplyColumn;
        }

        $stmt = self::db()->prepare(
            "SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'kanban_card_comments'
               AND COLUMN_NAME = 'reply_to_comment_id'"
        );
        $stmt->execute();

        self::$hasReplyColumn = (int)$stmt->fetchColumn() > 0;

        return self::$hasReplyColumn;
    }
}

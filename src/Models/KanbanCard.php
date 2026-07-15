<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;
use RuntimeException;

class KanbanCard
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function allVisible(): array
    {
        return self::db()
            ->query(
                'SELECT c.*, u.username AS creator_name
                 FROM kanban_cards c
                 LEFT JOIN users u ON u.id = c.created_by
                 WHERE c.is_archived = 0
                 ORDER BY c.position_value ASC, c.id ASC'
            )
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM kanban_cards WHERE id = ? AND is_archived = 0 LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findAny(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM kanban_cards WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $listId = (int)$data['list_id'];

        $stmt = self::db()->prepare(
            'INSERT INTO kanban_cards
                (list_id, title, description, label_text, label_color, due_date, auto_archive_on_complete,
                 requires_documentation, requires_final_comment,
                 position_value, created_by, updated_by)
             VALUES
                (:list_id, :title, :description, :label_text, :label_color, :due_date, :auto_archive_on_complete,
                 :requires_documentation, :requires_final_comment,
                 :position_value, :created_by, :updated_by)'
        );

        $stmt->execute([
            ':list_id' => $listId,
            ':title' => ($data['title'] ?? '') !== '' ? $data['title'] : null,
            ':description' => $data['description'] ?: null,
            ':label_text' => $data['label_text'] ?: null,
            ':label_color' => self::normalizeColor($data['label_color'] ?? ''),
            ':due_date' => $data['due_date'] ?: null,
            ':auto_archive_on_complete' => !empty($data['auto_archive_on_complete']) ? 1 : 0,
            ':requires_documentation' => !empty($data['requires_documentation']) ? 1 : 0,
            ':requires_final_comment' => !empty($data['requires_final_comment']) ? 1 : 0,
            ':position_value' => self::nextPosition($listId),
            ':created_by' => $data['user_id'] ?? null,
            ':updated_by' => $data['user_id'] ?? null,
        ]);

        $cardId = (int) self::db()->lastInsertId();
        self::syncAssignees($cardId, $data['assignees'] ?? []);

        return $cardId;
    }

    public static function move(int $cardId, int $listId, string $position, ?int $userId = null): ?array
    {
        if (!KanbanList::find($listId)) {
            return null;
        }

        $positionValue = self::normalizePosition($listId, $position);

        $stmt = self::db()->prepare(
            'UPDATE kanban_cards
             SET list_id = ?, position_value = ?, updated_by = ?
             WHERE id = ? AND is_archived = 0'
        );
        $stmt->execute([$listId, $positionValue, $userId, $cardId]);

        return self::find($cardId);
    }

    public static function update(int $cardId, array $data): ?array
    {
        $stmt = self::db()->prepare(
            'UPDATE kanban_cards
             SET title = ?,
                 description = ?,
                 label_text = ?,
                 label_color = ?,
                 due_date = ?,
                 auto_archive_on_complete = ?,
                 requires_documentation = ?,
                 requires_final_comment = ?,
                 updated_by = ?
             WHERE id = ? AND is_archived = 0'
        );

        $stmt->execute([
            ($data['title'] ?? '') !== '' ? $data['title'] : null,
            ($data['description'] ?? '') !== '' ? $data['description'] : null,
            ($data['label_text'] ?? '') !== '' ? $data['label_text'] : null,
            self::normalizeColor($data['label_color'] ?? ''),
            ($data['due_date'] ?? '') !== '' ? $data['due_date'] : null,
            !empty($data['auto_archive_on_complete']) ? 1 : 0,
            !empty($data['requires_documentation']) ? 1 : 0,
            !empty($data['requires_final_comment']) ? 1 : 0,
            $data['user_id'] ?? null,
            $cardId,
        ]);

        self::syncAssignees($cardId, $data['assignees'] ?? []);

        return self::find($cardId);
    }

    public static function setComplete(int $cardId, bool $completed, ?int $userId = null): ?array
    {
        if ($completed) {
            $blockers = self::completionBlockers($cardId);

            if (!empty($blockers)) {
                throw new RuntimeException('No se puede completar la tarjeta. Falta ' . implode(' y ', $blockers) . '.');
            }
        }

        $stmt = self::db()->prepare(
            'UPDATE kanban_cards
             SET is_complete = ?,
                 is_in_progress = 0,
                 completed_at = ?,
                 completed_by = ?,
                 updated_by = ?
             WHERE id = ? AND is_archived = 0'
        );
        $stmt->execute([
            $completed ? 1 : 0,
            $completed ? date('Y-m-d H:i:s') : null,
            $completed ? $userId : null,
            $userId,
            $cardId,
        ]);

        return self::find($cardId);
    }

    public static function setInProgress(int $cardId, bool $inProgress, ?int $userId = null): ?array
    {
        $stmt = self::db()->prepare(
            'UPDATE kanban_cards
             SET is_in_progress = ?,
                 is_complete = 0,
                 completed_at = NULL,
                 completed_by = NULL,
                 updated_by = ?
             WHERE id = ? AND is_archived = 0'
        );
        $stmt->execute([$inProgress ? 1 : 0, $userId, $cardId]);

        return self::find($cardId);
    }

    public static function delete(int $cardId): void
    {
        self::db()
            ->prepare(
                "UPDATE kanban_cards
                 SET is_archived = 1,
                     archived_at = CURRENT_TIMESTAMP,
                     archive_reason = 'deleted'
                 WHERE id = ?"
            )
            ->execute([$cardId]);
    }

    public static function archiveCompleted(int $cardId, ?int $userId = null): bool
{
    $stmt = self::db()->prepare(
        "UPDATE kanban_cards
         SET is_archived = 1,
             archived_at = CURRENT_TIMESTAMP,
             archive_reason = 'completed',
             updated_by = ?
         WHERE id = ?
           AND is_archived = 0
           AND is_complete = 1
           AND completed_at IS NOT NULL"
    );

    $stmt->execute([
        $userId,
        $cardId,
    ]);

    return $stmt->rowCount() > 0;
}

    public static function archiveCompletedOlderThan(int $amount = 7, string $unit = 'DAY'): int
    {
        $unit = strtoupper($unit);
        $allowedUnits = ['MINUTE', 'HOUR', 'DAY'];

        if (!in_array($unit, $allowedUnits, true)) {
            $unit = 'DAY';
        }

        $amount = max(1, min(5256000, $amount));

        $stmt = self::db()->prepare(
            "UPDATE kanban_cards
             SET is_archived = 1,
                 archived_at = CURRENT_TIMESTAMP,
                 archive_reason = 'completed'
             WHERE is_archived = 0
               AND is_complete = 1
               AND auto_archive_on_complete = 1
               AND completed_at IS NOT NULL
               AND completed_at <= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL {$amount} {$unit})"
        );
        $stmt->execute();

        return $stmt->rowCount();
    }

    public static function completionStatus(int $cardId): array
    {
        $card = self::findAny($cardId);

        if (!$card) {
            return [
                'requires_documentation' => false,
                'requires_final_comment' => false,
                'has_documentation' => false,
                'has_final_comment' => false,
                'blockers' => ['tarjeta no encontrada'],
            ];
        }

        $hasDocumentation = KanbanCardAttachment::countForCard($cardId, 'completion') > 0;
        $hasFinalComment = KanbanCardComment::hasFinalComment($cardId);
        $blockers = [];

        if (!empty($card['requires_documentation']) && !$hasDocumentation) {
            $blockers[] = 'adjuntar documentacion';
        }

        if (!empty($card['requires_final_comment']) && !$hasFinalComment) {
            $blockers[] = 'agregar un comentario final';
        }

        return [
            'requires_documentation' => !empty($card['requires_documentation']),
            'requires_final_comment' => !empty($card['requires_final_comment']),
            'has_documentation' => $hasDocumentation,
            'has_final_comment' => $hasFinalComment,
            'blockers' => $blockers,
        ];
    }

    public static function completedHistory(array $filters = [], int $limit = 30, int $offset = 0): array
    {
        [$where, $params] = self::historyConditions($filters);
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $stmt = self::db()->prepare(
            "SELECT c.*,
                    l.name AS list_name,
                    l.color AS list_color,
                    creator.username AS creator_name,
                    completer.username AS completed_by_name
             FROM kanban_cards c
             JOIN kanban_lists l ON l.id = c.list_id
             LEFT JOIN users creator ON creator.id = c.created_by
             LEFT JOIN users completer ON completer.id = c.completed_by
             WHERE {$where}
             ORDER BY c.completed_at DESC, c.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function completedHistoryCount(array $filters = []): int
    {
        [$where, $params] = self::historyConditions($filters);
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM kanban_cards c WHERE {$where}");
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public static function assigneesForCards(array $cardIds): array
    {
        $cardIds = array_values(array_unique(array_filter(array_map('intval', $cardIds))));

        if (empty($cardIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cardIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT a.card_id, u.id, u.username, u.email, u.display_title
             FROM kanban_card_assignees a
             JOIN users u ON u.id = a.user_id
             WHERE a.card_id IN ({$placeholders})
             ORDER BY u.username ASC"
        );
        $stmt->execute($cardIds);

        $assignees = [];
        foreach ($stmt->fetchAll() as $row) {
            $assignees[(int)$row['card_id']][] = $row;
        }

        return $assignees;
    }

    public static function traceSummaryForCards(array $cardIds): array
    {
        $cardIds = array_values(array_unique(array_filter(array_map('intval', $cardIds))));

        if (empty($cardIds)) {
            return [];
        }

        $summary = [];
        foreach ($cardIds as $cardId) {
            $summary[$cardId] = [
                'attachment_count' => 0,
                'work_attachment_count' => 0,
                'completion_attachment_count' => 0,
                'comment_count' => 0,
                'has_final_comment' => false,
            ];
        }

        $placeholders = implode(',', array_fill(0, count($cardIds), '?'));

        $attachmentStmt = self::db()->prepare(
            "SELECT card_id, purpose, COUNT(*) AS total
             FROM kanban_card_attachments
             WHERE card_id IN ({$placeholders})
             GROUP BY card_id, purpose"
        );
        $attachmentStmt->execute($cardIds);

        foreach ($attachmentStmt->fetchAll() as $row) {
            $cardId = (int)$row['card_id'];
            $purpose = (string)($row['purpose'] ?? 'completion');
            $total = (int)$row['total'];

            $summary[$cardId]['attachment_count'] += $total;

            if ($purpose === 'work') {
                $summary[$cardId]['work_attachment_count'] += $total;
            } else {
                $summary[$cardId]['completion_attachment_count'] += $total;
            }
        }

        $commentStmt = self::db()->prepare(
            "SELECT card_id, COUNT(*) AS total, MAX(is_final) AS has_final
             FROM kanban_card_comments
             WHERE card_id IN ({$placeholders})
             GROUP BY card_id"
        );
        $commentStmt->execute($cardIds);

        foreach ($commentStmt->fetchAll() as $row) {
            $cardId = (int)$row['card_id'];
            $summary[$cardId]['comment_count'] = (int)$row['total'];
            $summary[$cardId]['has_final_comment'] = (int)$row['has_final'] === 1;
        }

        return $summary;
    }

    public static function boardVersion(): string
    {
        $db = self::db();

        $lists = $db
            ->query(
                'SELECT id, name, description, color, position_value, is_archived, updated_at
                 FROM kanban_lists
                 ORDER BY id ASC'
            )
            ->fetchAll();

        $cards = $db
            ->query(
                'SELECT id, list_id, title, description, label_text, label_color, due_date,
                        is_complete, is_in_progress, auto_archive_on_complete, requires_documentation, requires_final_comment,
                        completed_at, completed_by, position_value,
                        updated_by, is_archived, archived_at, archive_reason, updated_at
                 FROM kanban_cards
                 ORDER BY id ASC'
            )
            ->fetchAll();

        $assignees = $db
            ->query(
                'SELECT card_id, user_id
                 FROM kanban_card_assignees
                 ORDER BY card_id ASC, user_id ASC'
            )
            ->fetchAll();

        $comments = $db
            ->query(
                'SELECT id, card_id, user_id, body, is_final, created_at
                 FROM kanban_card_comments
                 ORDER BY id ASC'
            )
            ->fetchAll();

        $attachments = $db
            ->query(
                'SELECT id, card_id, uploaded_by, filename, path, mime_type, size, is_image, purpose, created_at
                 FROM kanban_card_attachments
                 ORDER BY id ASC'
            )
            ->fetchAll();

        return hash('sha256', json_encode([
            'lists' => $lists,
            'cards' => $cards,
            'assignees' => $assignees,
            'comments' => $comments,
            'attachments' => $attachments,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function completionBlockers(int $cardId): array
    {
        return self::completionStatus($cardId)['blockers'] ?? [];
    }

    private static function historyConditions(array $filters): array
    {
        $conditions = [
            'c.is_complete = 1',
            'c.is_archived = 1',
            "c.archive_reason = 'completed'",
            'c.completed_at IS NOT NULL',
        ];
        $params = [];

        $query = trim((string)($filters['query'] ?? ''));
        if ($query !== '') {
            $conditions[] = '(c.title LIKE :query_title OR c.description LIKE :query_description)';
            $params[':query_title'] = '%' . $query . '%';
            $params[':query_description'] = '%' . $query . '%';
        }

        $listId = (int)($filters['list_id'] ?? 0);
        if ($listId > 0) {
            $conditions[] = 'c.list_id = :list_id';
            $params[':list_id'] = $listId;
        }

        $dateFrom = trim((string)($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $conditions[] = 'DATE(c.completed_at) >= :date_from';
            $params[':date_from'] = $dateFrom;
        }

        $dateTo = trim((string)($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $conditions[] = 'DATE(c.completed_at) <= :date_to';
            $params[':date_to'] = $dateTo;
        }

        return [implode(' AND ', $conditions), $params];
    }

    private static function syncAssignees(int $cardId, array $userIds): void
    {
        self::db()->prepare('DELETE FROM kanban_card_assignees WHERE card_id = ?')->execute([$cardId]);

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return;
        }

        $stmt = self::db()->prepare('INSERT INTO kanban_card_assignees (card_id, user_id) VALUES (?, ?)');
        foreach ($userIds as $userId) {
            $stmt->execute([$cardId, $userId]);
        }
    }

    private static function nextPosition(int $listId): float
    {
        $stmt = self::db()->prepare('SELECT MAX(position_value) FROM kanban_cards WHERE list_id = ? AND is_archived = 0');
        $stmt->execute([$listId]);
        return ((float)($stmt->fetchColumn() ?: 0)) + 65536;
    }

    private static function normalizePosition(int $listId, string $position): float
    {
        $position = trim($position);

        if ($position !== '' && is_numeric($position)) {
            return (float)$position;
        }

        return self::nextPosition($listId);
    }

    private static function normalizeColor(string $color): ?string
    {
        if ($color === '') {
            return null;
        }

        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#58a6ff';
    }
}

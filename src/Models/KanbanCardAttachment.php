<?php

namespace OfficeHub\Models;

use OfficeHub\Core\Database;
use PDO;

class KanbanCardAttachment
{
    private static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function forCard(int $cardId): array
    {
        $stmt = self::db()->prepare(
            'SELECT a.*, u.username AS uploader_name
             FROM kanban_card_attachments a
             LEFT JOIN users u ON u.id = a.uploaded_by
             WHERE a.card_id = ?
             ORDER BY a.created_at ASC, a.id ASC'
        );
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
        $stmt = self::db()->prepare(
            "SELECT a.*, u.username AS uploader_name
             FROM kanban_card_attachments a
             LEFT JOIN users u ON u.id = a.uploaded_by
             WHERE a.card_id IN ({$placeholders})
             ORDER BY a.card_id ASC, a.created_at ASC, a.id ASC"
        );
        $stmt->execute($cardIds);

        $attachments = [];
        foreach ($stmt->fetchAll() as $row) {
            $attachments[(int)$row['card_id']][] = $row;
        }

        return $attachments;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT *
             FROM kanban_card_attachments
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO kanban_card_attachments
                (card_id, uploaded_by, filename, path, mime_type, size, is_image, purpose)
             VALUES
                (:card_id, :uploaded_by, :filename, :path, :mime_type, :size, :is_image, :purpose)'
        );
        $stmt->execute([
            ':card_id' => $data['card_id'],
            ':uploaded_by' => $data['uploaded_by'],
            ':filename' => $data['filename'],
            ':path' => $data['path'],
            ':mime_type' => $data['mime_type'],
            ':size' => $data['size'],
            ':is_image' => !empty($data['is_image']) ? 1 : 0,
            ':purpose' => self::normalizePurpose((string)($data['purpose'] ?? 'completion')),
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function countForCard(int $cardId, ?string $purpose = null): int
    {
        if ($purpose !== null) {
            $stmt = self::db()->prepare('SELECT COUNT(*) FROM kanban_card_attachments WHERE card_id = ? AND purpose = ?');
            $stmt->execute([$cardId, self::normalizePurpose($purpose)]);

            return (int)$stmt->fetchColumn();
        }

        $stmt = self::db()->prepare('SELECT COUNT(*) FROM kanban_card_attachments WHERE card_id = ?');
        $stmt->execute([$cardId]);

        return (int)$stmt->fetchColumn();
    }

    public static function deleteById(int $id): void
    {
        self::db()
            ->prepare('DELETE FROM kanban_card_attachments WHERE id = ?')
            ->execute([$id]);
    }

    private static function normalizePurpose(string $purpose): string
    {
        return $purpose === 'work' ? 'work' : 'completion';
    }
}

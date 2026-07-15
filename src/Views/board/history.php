<?php
/** @var array $cards */
/** @var array $lists */
/** @var array $filters */
/** @var array $commentsByCard */
/** @var array $attachmentsByCard */
/** @var int $currentUserId */
/** @var string $currentUserName */
/** @var int $page */
/** @var int $totalPages */
/** @var int $total */

$formatDate = static function (?string $date, string $format = 'd/m/Y H:i'): string {
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '';
};

$formatBytes = static function (int $bytes): string {
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $index = 0;
    $size = (float)$bytes;

    while ($size >= 1024 && $index < count($units) - 1) {
        $size /= 1024;
        $index++;
    }

    return rtrim(rtrim(number_format($size, $index === 0 ? 0 : 1), '0'), '.') . ' ' . $units[$index];
};

$shortText = static function (?string $text, int $limit = 150): string {
    $text = trim((string)$text);

    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 1) . '...' : $text;
    }

    return strlen($text) > $limit ? substr($text, 0, $limit - 1) . '...' : $text;
};

$pageUrl = static function (int $targetPage) use ($filters): string {
    $query = [
        'q' => $filters['query'] ?? '',
        'list_id' => (int)($filters['list_id'] ?? 0),
        'date_from' => $filters['date_from'] ?? '',
        'date_to' => $filters['date_to'] ?? '',
        'page' => $targetPage,
    ];

    $query = array_filter($query, static fn ($value): bool => $value !== '' && $value !== 0);
    return base('tablero/historial') . '?' . http_build_query($query);
};

$currentHistoryPath = '/tablero/historial';
if (!empty($_SERVER['QUERY_STRING'])) {
    $currentHistoryPath .= '?' . $_SERVER['QUERY_STRING'];
}

$traceData = [];

foreach ($cards as $card) {
    $cardId = (int)$card['id'];
    $cardComments = $commentsByCard[$cardId] ?? [];
    $cardAttachments = $attachmentsByCard[$cardId] ?? [];
    $archivedAt = (string)($card['archived_at'] ?? '');
    $archivedTs = $archivedAt !== '' ? strtotime($archivedAt) : null;
    $hasFinalComment = false;
    $commentItems = [];

    foreach ($cardComments as $comment) {
        $createdAt = (string)($comment['created_at'] ?? '');
        $createdTs = $createdAt !== '' ? strtotime($createdAt) : null;
        $isFinal = !empty($comment['is_final']);

        if ($isFinal) {
            $hasFinalComment = true;
        }

        $commentItems[] = [
            'id' => (int)$comment['id'],
            'user_id' => (int)($comment['user_id'] ?? 0),
            'author_name' => $comment['author_name'] ?: 'Usuario',
            'body' => (string)($comment['body'] ?? ''),
            'is_final' => $isFinal,
            'is_audit' => $archivedTs !== null && $createdTs !== null && $createdTs >= $archivedTs && !$isFinal,
            'created_at' => $createdAt,
            'created_label' => $formatDate($createdAt),
        ];
    }

    $attachmentItems = [];

    foreach ($cardAttachments as $attachment) {
        $attachmentUrl = base('tablero/cards/' . $cardId . '/attachments/' . (int)$attachment['id']);

        $attachmentItems[] = [
            'id' => (int)$attachment['id'],
            'filename' => $attachment['filename'] ?: 'Adjunto',
            'size' => (int)($attachment['size'] ?? 0),
            'size_label' => $formatBytes((int)($attachment['size'] ?? 0)),
            'is_image' => !empty($attachment['is_image']),
            'purpose' => (string)($attachment['purpose'] ?? 'completion'),
            'mime_type' => (string)($attachment['mime_type'] ?? ''),
            'uploader_name' => $attachment['uploader_name'] ?: 'Usuario',
            'created_at' => (string)($attachment['created_at'] ?? ''),
            'created_label' => $formatDate((string)($attachment['created_at'] ?? '')),
            'preview_url' => $attachmentUrl . '?preview=1',
            'download_url' => $attachmentUrl . '?download=1',
        ];
    }

    $requirements = [];

    if (!empty($card['requires_documentation'])) {
        $completionAttachments = array_filter(
            $attachmentItems,
            fn (array $attachment): bool => ($attachment['purpose'] ?? 'completion') !== 'work'
        );

        $requirements[] = [
            'label' => 'Documentacion obligatoria',
            'complete' => count($completionAttachments) > 0,
        ];
    }

    if (!empty($card['requires_final_comment'])) {
        $requirements[] = [
            'label' => 'Comentario final obligatorio',
            'complete' => $hasFinalComment,
        ];
    }

    $traceData[$cardId] = [
        'id' => $cardId,
        'title' => $card['title'] ?: 'Sin titulo',
        'description' => (string)($card['description'] ?? ''),
        'label_text' => (string)($card['label_text'] ?? ''),
        'label_color' => (string)($card['label_color'] ?? ''),
        'list_name' => (string)($card['list_name'] ?? 'Sin lista'),
        'list_color' => (string)($card['list_color'] ?? '#58a6ff'),
        'completed_at' => (string)($card['completed_at'] ?? ''),
        'completed_label' => $formatDate((string)($card['completed_at'] ?? ''), 'd/m/Y'),
        'completed_by_name' => $card['completed_by_name'] ?: 'Usuario no disponible',
        'archived_at' => $archivedAt,
        'archived_label' => $formatDate($archivedAt, 'd/m/Y'),
        'archive_mode' => !empty($card['auto_archive_on_complete']) ? 'automatico' : 'manual',
        'assignees' => array_map(static fn (array $user): array => [
            'id' => (int)$user['id'],
            'username' => $user['username'] ?? 'Usuario',
        ], $card['assignees'] ?? []),
        'requirements' => $requirements,
        'comments' => $commentItems,
        'attachments' => $attachmentItems,
        'comment_action' => base('tablero/cards/' . $cardId . '/comments'),
        'return_to' => $currentHistoryPath,
    ];
}

$traceJson = json_encode(
    $traceData,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
?>

<style>
    .history-page {
        --history-panel: rgba(255, 255, 255, .92);
        --history-panel-soft: rgba(248, 250, 252, .9);
        --history-border: rgba(148, 163, 184, .36);
        --history-border-soft: rgba(148, 163, 184, .22);
        --history-text: #0f172a;
        --history-muted: #64748b;
        --history-faint: #94a3b8;
        --history-strong: #020617;
        --history-accent: #2563eb;
        --history-green: #059669;
        --history-yellow: #f59e0b;
        max-width: 1240px;
        margin: 0 auto;
        padding: 2px 0 40px;
        color: var(--history-text);
    }

    [data-theme="dark"] .history-page,
    html[data-theme="dark"] .history-page {
        --history-panel: rgba(22, 27, 34, .9);
        --history-panel-soft: rgba(13, 17, 23, .72);
        --history-border: rgba(48, 54, 61, .92);
        --history-border-soft: rgba(48, 54, 61, .62);
        --history-text: #e6edf3;
        --history-muted: #8b949e;
        --history-faint: #6e7681;
        --history-strong: #f0f6fc;
        --history-accent: #58a6ff;
        --history-green: #3fb950;
        --history-yellow: #d29922;
    }

    .history-shell {
        border: 1px solid var(--history-border);
        border-radius: 12px;
        background: var(--history-panel);
        box-shadow: 0 20px 70px rgba(15, 23, 42, .08);
    }

    .history-intro {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .history-intro h1 {
        color: var(--history-strong);
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.01em;
        margin: 0 0 7px;
    }

    .history-intro p {
        max-width: 760px;
        color: var(--history-muted);
        font-size: 12px;
        line-height: 1.6;
        margin: 0;
    }

    .history-back,
    .history-filter-button,
    .history-reset,
    .history-trace-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: transform .16s ease, border-color .16s ease, background .16s ease, box-shadow .16s ease;
    }

    .history-back {
        gap: 7px;
        padding: 0 16px;
        border: 1px solid #020617;
        background: #020617;
        color: #fff;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(2, 6, 23, .16);
    }

    [data-theme="dark"] .history-back,
    html[data-theme="dark"] .history-back {
        border-color: #f8fafc;
        background: #f8fafc;
        color: #020617;
    }

    .history-back:hover,
    .history-filter-button:hover,
    .history-trace-button:hover {
        transform: translateY(-1px);
    }

    .history-filter {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) minmax(180px, 290px) minmax(140px, 1fr) minmax(140px, 1fr);
        gap: 14px;
        align-items: end;
        padding: 18px;
        margin-bottom: 22px;
        background: var(--history-panel-soft);
    }

    .history-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .history-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .history-field label,
    .history-table-label,
    .history-modal-kicker,
    .history-card-label {
        color: var(--history-faint);
        font-size: 10px;
        font-weight: 800;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .history-field input,
    .history-field select {
        width: 100%;
        min-height: 35px;
        border: 1px solid var(--history-border);
        border-radius: 8px;
        background: var(--history-panel);
        color: var(--history-text);
        padding: 0 12px;
        font-size: 12px;
        outline: none;
    }

    .history-field input:focus,
    .history-field select:focus {
        border-color: var(--history-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    .history-filter-button {
        padding: 0 18px;
        border: 1px solid var(--accent-brd);
        background: var(--accent-bg);
        color: #fff;
    }

    .history-reset {
        padding: 0 16px;
        border: 1px solid var(--history-border);
        background: var(--history-panel);
        color: var(--history-text);
    }

    .history-table {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .history-table-head {
        display: grid;
        grid-template-columns: minmax(260px, 1.25fr) minmax(220px, 1fr) minmax(170px, .75fr) minmax(140px, .55fr);
        gap: 18px;
        padding: 0 18px;
    }

    .history-row {
        display: grid;
        grid-template-columns: minmax(260px, 1.25fr) minmax(220px, 1fr) minmax(170px, .75fr) minmax(140px, .55fr);
        gap: 18px;
        align-items: center;
        padding: 18px;
    }

    .history-title-line {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 7px;
    }

    .history-title {
        color: var(--history-strong);
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .history-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 20px;
        padding: 0 8px;
        border: 1px solid var(--history-border-soft);
        border-radius: 999px;
        background: var(--history-panel-soft);
        color: var(--history-muted);
        font-size: 10px;
        font-weight: 700;
    }

    .history-tag-dot,
    .history-list-dot,
    .history-status-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 auto;
        border-radius: 999px;
    }

    .history-description {
        max-width: 540px;
        color: var(--history-muted);
        font-size: 12px;
        line-height: 1.55;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        margin: 0 0 12px;
    }

    .history-trace-button {
        min-width: 118px;
        padding: 0 12px;
        border: 1px solid var(--history-border);
        background: var(--history-panel-soft);
        color: var(--history-strong);
        font-size: 11px;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .history-list-name {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--history-strong);
        font-size: 11px;
        font-weight: 800;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        text-transform: uppercase;
    }

    .history-members {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 9px;
    }

    .history-member {
        padding: 3px 8px;
        border: 1px solid var(--history-border-soft);
        border-radius: 5px;
        background: var(--history-panel-soft);
        color: var(--history-muted);
        font-size: 10px;
        font-weight: 700;
    }

    .history-meta {
        color: var(--history-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .history-meta strong {
        color: var(--history-strong);
        font-weight: 800;
    }

    .history-state {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        border: 1px solid rgba(16, 185, 129, .3);
        border-radius: 999px;
        background: rgba(16, 185, 129, .08);
        color: var(--history-green);
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .history-empty {
        padding: 42px 20px;
        text-align: center;
        color: var(--history-muted);
        font-size: 13px;
    }

    .history-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 20px;
        color: var(--history-muted);
        font-size: 12px;
    }

    .history-pagination a {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        padding: 0 13px;
        border: 1px solid var(--history-border);
        border-radius: 8px;
        background: var(--history-panel);
        color: var(--history-text);
        text-decoration: none;
    }

    .history-modal {
        position: fixed;
        inset: 0;
        z-index: 5000;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 42px 18px;
    }

    .history-modal[hidden] {
        display: none;
    }

    .history-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .42);
        backdrop-filter: blur(6px);
    }

    .history-modal-panel {
        position: relative;
        box-sizing: border-box;
        width: min(840px, calc(100vw - 36px));
        max-height: calc(100vh - 84px);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(226, 232, 240, .95);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 32px 96px -16px rgba(0, 0, 0, .18);
        padding: 26px 28px 30px;
        animation: historyModalIn .18s ease-out;
    }

    [data-theme="dark"] .history-modal-panel,
    html[data-theme="dark"] .history-modal-panel {
        border-color: rgba(48, 54, 61, .85);
        background: #0d1117;
        box-shadow: 0 32px 96px -16px rgba(0, 0, 0, .56);
    }

    @keyframes historyModalIn {
        from {
            opacity: 0;
            transform: translateY(14px) scale(.985);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .history-modal-head {
        flex: 0 0 auto;
        display: flex;
        justify-content: space-between;
        gap: 18px;
        padding-bottom: 20px;
        margin-bottom: 22px;
        border-bottom: 1px solid rgba(226, 232, 240, .85);
    }

    [data-theme="dark"] .history-modal-head,
    html[data-theme="dark"] .history-modal-head {
        border-bottom-color: rgba(48, 54, 61, .72);
    }

    .history-modal-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 9px;
    }

    .history-modal-badge {
        min-height: 20px;
        display: inline-flex;
        align-items: center;
        padding: 0 8px;
        border: 1px solid rgba(203, 213, 225, .85);
        border-radius: 4px;
        background: #f8fafc;
        color: #64748b;
        font-size: 9px;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    [data-theme="dark"] .history-modal-badge,
    html[data-theme="dark"] .history-modal-badge {
        border-color: rgba(48, 54, 61, .85);
        background: #161b22;
        color: #8b949e;
    }

    .history-modal-badge.is-green {
        border-color: rgba(16, 185, 129, .25);
        background: rgba(16, 185, 129, .08);
        color: var(--history-green);
    }

    .history-modal-id {
        color: var(--history-faint);
        font-size: 10px;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
    }

    .history-modal-title {
        color: var(--history-strong);
        font-size: 20px;
        font-weight: 850;
        line-height: 1.2;
        margin: 0 0 7px;
        overflow-wrap: anywhere;
    }

    .history-modal-origin {
        color: var(--history-muted);
        font-size: 11px;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        margin: 0;
    }

    .history-modal-origin strong {
        color: var(--history-strong);
        text-transform: uppercase;
    }

    .history-modal-close {
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        border: 0;
        border-radius: 999px;
        background: #f8fafc;
        color: var(--history-muted);
        cursor: pointer;
        font-size: 22px;
        line-height: 1;
    }

    [data-theme="dark"] .history-modal-close,
    html[data-theme="dark"] .history-modal-close {
        background: #161b22;
    }

    .history-modal-close:hover {
        color: var(--history-strong);
    }

    .history-modal-grid {
        flex: 1 1 auto;
        min-height: 0;
        max-height: calc(76vh - 140px);
        overflow-y: auto;
        overflow-x: hidden;
        display: grid;
        grid-template-columns: minmax(270px, 5fr) minmax(390px, 7fr);
        gap: 22px;
        padding-right: 5px;
    }

    .history-summary-col,
    .history-timeline-col {
        display: flex;
        flex-direction: column;
        gap: 14px;
        min-width: 0;
    }

    .history-detail-card {
        border: 1px solid rgba(203, 213, 225, .8);
        border-radius: 12px;
        background: #f8fafc;
        padding: 15px;
    }

    [data-theme="dark"] .history-detail-card,
    html[data-theme="dark"] .history-detail-card {
        border-color: rgba(48, 54, 61, .8);
        background: rgba(22, 27, 34, .45);
    }

    .history-detail-card.is-strong {
        border-color: rgba(15, 23, 42, .86);
    }

    [data-theme="dark"] .history-detail-card.is-strong,
    html[data-theme="dark"] .history-detail-card.is-strong {
        border-color: rgba(139, 148, 158, .58);
    }

    .history-detail-text {
        margin: 10px 0 0;
        color: var(--history-muted);
        font-size: 12px;
        line-height: 1.65;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .history-read-more {
        display: inline-flex;
        align-items: center;
        margin-top: 9px;
        border: 0;
        background: transparent;
        color: var(--history-accent);
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        padding: 0;
    }

    .history-detail-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 9px;
        color: var(--history-text);
        font-size: 12px;
    }

    .history-divider {
        height: 1px;
        background: var(--history-border);
        margin: 13px 0;
    }

    .history-requirements {
        display: flex;
        flex-direction: column;
        gap: 9px;
        margin-top: 12px;
    }

    .history-requirement {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--history-muted);
        font-size: 12px;
    }

    .history-requirement.is-complete {
        color: var(--history-muted);
        text-decoration: line-through;
        text-decoration-color: rgba(16, 185, 129, .55);
    }

    .history-check {
        width: 13px;
        height: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--history-green);
        font-size: 13px;
        font-weight: 900;
    }

    .history-chat-card {
        min-height: 346px;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 12px;
        background: #fff;
        padding: 15px;
    }

    [data-theme="dark"] .history-chat-card,
    html[data-theme="dark"] .history-chat-card {
        border-color: rgba(48, 54, 61, .85);
        background: #0d1117;
    }

    .history-comments {
        flex: 1;
        max-height: 266px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 13px;
        padding-right: 5px;
        margin-top: 12px;
    }

    .history-comment {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .history-comment.is-own {
        align-items: flex-end;
    }

    .history-comment-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 0 4px 4px;
        color: var(--history-faint);
        font-size: 9px;
        font-weight: 800;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
    }

    .history-comment-bubble {
        max-width: 88%;
        border: 1px solid var(--history-border-soft);
        border-radius: 14px 14px 14px 4px;
        background: var(--history-panel);
        color: var(--history-text);
        padding: 10px 12px;
        font-size: 12px;
        line-height: 1.55;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
    }

    .history-comment.is-own .history-comment-bubble {
        border-color: #020617;
        border-radius: 14px 14px 4px 14px;
        background: #020617;
        color: #fff;
    }

    [data-theme="dark"] .history-comment.is-own .history-comment-bubble,
    html[data-theme="dark"] .history-comment.is-own .history-comment-bubble {
        border-color: #f8fafc;
        background: #f8fafc;
        color: #020617;
    }

    .history-comment-bubble.is-final {
        border-color: rgba(16, 185, 129, .3);
        background: rgba(16, 185, 129, .08);
        color: var(--history-green);
    }

    .history-comment-bubble.is-audit {
        border-style: dashed;
        border-color: #fbbf24;
        background: #fffbeb;
        color: #78350f;
        box-shadow: none;
    }

    .history-comment.is-own .history-comment-bubble.is-audit {
        border-color: #fbbf24;
        background: #fffbeb;
        color: #78350f;
    }

    [data-theme="dark"] .history-comment-bubble.is-audit,
    html[data-theme="dark"] .history-comment-bubble.is-audit,
    [data-theme="dark"] .history-comment.is-own .history-comment-bubble.is-audit,
    html[data-theme="dark"] .history-comment.is-own .history-comment-bubble.is-audit {
        border-color: rgba(251, 191, 36, .72);
        background: rgba(69, 42, 9, .78);
        color: #fde68a;
    }

    .history-audit-label,
    .history-final-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 5px;
        color: #d97706;
        font-size: 8px;
        font-weight: 950;
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    [data-theme="dark"] .history-audit-label,
    html[data-theme="dark"] .history-audit-label {
        color: #fbbf24;
    }

    .history-final-label {
        color: var(--history-green);
    }

    .history-audit-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #f59e0b;
    }

    [data-theme="dark"] .history-audit-dot,
    html[data-theme="dark"] .history-audit-dot {
        background: #fbbf24;
    }

    .history-audit-form {
        display: flex;
        align-items: stretch;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(226, 232, 240, .85);
    }

    [data-theme="dark"] .history-audit-form,
    html[data-theme="dark"] .history-audit-form {
        border-top-color: rgba(48, 54, 61, .72);
    }

    .history-audit-form input {
        flex: 1;
        min-height: 35px;
        border: 1px solid rgba(203, 213, 225, .85);
        border-radius: 8px;
        background: #fff;
        color: var(--history-text);
        padding: 0 12px;
        font-size: 12px;
        outline: none;
    }

    [data-theme="dark"] .history-audit-form input,
    html[data-theme="dark"] .history-audit-form input {
        border-color: rgba(48, 54, 61, .85);
        background: #0d1117;
    }

    .history-audit-form input:focus {
        border-color: var(--history-accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    .history-audit-form button {
        min-height: 35px;
        border: 0;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        background: #aeb4bd;
        color: #fff;
        padding: 0 13px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease, opacity .16s ease;
    }

    [data-theme="dark"] .history-audit-form button,
    html[data-theme="dark"] .history-audit-form button {
        background: #6e7681;
        color: #f0f6fc;
    }

    .history-audit-form.has-text button {
        background: #020617;
        color: #fff;
    }

    [data-theme="dark"] .history-audit-form.has-text button,
    html[data-theme="dark"] .history-audit-form.has-text button {
        background: #f8fafc;
        color: #020617;
    }

    .history-audit-form button:hover {
        transform: translateY(-1px);
    }

    .history-audit-form:not(.has-text) button {
        cursor: default;
    }

    .history-audit-send-icon {
        width: 13px;
        height: 13px;
        flex: 0 0 auto;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .history-audit-status {
        min-height: 16px;
        margin-top: 8px;
        color: var(--history-muted);
        font-size: 11px;
    }

    .history-attachments {
        border: 1px solid rgba(15, 23, 42, .86);
        border-radius: 12px;
        background: #fff;
        padding: 14px;
    }

    [data-theme="dark"] .history-attachments,
    html[data-theme="dark"] .history-attachments {
        border-color: rgba(139, 148, 158, .58);
        background: rgba(22, 27, 34, .45);
    }

    .history-attachment-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-top: 11px;
    }

    .history-attachment-section + .history-attachment-section {
        margin-top: 15px;
        padding-top: 13px;
        border-top: 1px solid rgba(226, 232, 240, .78);
    }

    [data-theme="dark"] .history-attachment-section + .history-attachment-section,
    html[data-theme="dark"] .history-attachment-section + .history-attachment-section {
        border-top-color: rgba(48, 54, 61, .72);
    }

    .history-attachment-section-title {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        color: var(--history-muted);
        font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .history-attachment-section-title small {
        color: var(--history-faint);
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .05em;
        white-space: nowrap;
    }

    .history-attachment {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 9px;
        border: 1px solid var(--history-border-soft);
        border-radius: 9px;
        background: var(--history-panel);
        padding: 8px;
    }

    .history-attachment-icon {
        width: 33px;
        height: 33px;
        flex: 0 0 auto;
        border-radius: 8px;
        background: var(--history-panel-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--history-muted);
        overflow: hidden;
    }

    .history-attachment-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .history-attachment-info {
        min-width: 0;
        flex: 1;
    }

    .history-attachment-name {
        color: var(--history-text);
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .history-attachment-size {
        color: var(--history-faint);
        font-size: 9px;
        margin-top: 3px;
    }

    .history-attachment a {
        flex: 0 0 auto;
        border: 1px solid var(--history-border-soft);
        border-radius: 6px;
        padding: 5px 8px;
        color: var(--history-text);
        background: var(--history-panel-soft);
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    @media (max-width: 980px) {
        .history-filter,
        .history-table-head,
        .history-row,
        .history-modal-grid {
            grid-template-columns: 1fr;
        }

        .history-table-head {
            display: none;
        }

        .history-row {
            gap: 14px;
        }
    }

    @media (max-width: 650px) {
        .history-intro {
            flex-direction: column;
            align-items: stretch;
        }

        .history-back {
            align-self: flex-start;
        }

        .history-modal {
            padding-top: 54px;
        }

        .history-modal-panel {
            max-height: calc(100vh - 76px);
            padding: 20px;
        }

        .history-attachment-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="history-page">
    <header class="history-shell history-intro">
        <div>
            <h1>Historial de tareas completadas</h1>
            <p>
                Consulta las tarjetas archivadas del portal de OfficeHub. Estos registros estan cerrados de manera permanente para fines de auditoria, control de calidad y trazabilidad.
            </p>
        </div>
        <a class="history-back" href="<?= base('tablero') ?>">Volver al tablero</a>
    </header>

    <form class="history-shell history-filter" method="GET" action="<?= base('tablero/historial') ?>">
        <div class="history-field">
            <label for="history-query">Buscar</label>
            <input id="history-query" type="search" name="q" value="<?= e($filters['query'] ?? '') ?>" placeholder="Titulo o descripcion">
        </div>

        <div class="history-field">
            <label for="history-list">Lista</label>
            <select id="history-list" name="list_id">
                <option value="">Todas las listas</option>
                <?php foreach ($lists as $list): ?>
                    <option value="<?= (int)$list['id'] ?>" <?= (int)($filters['list_id'] ?? 0) === (int)$list['id'] ? 'selected' : '' ?>>
                        <?= e($list['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="history-field">
            <label for="history-from">Desde</label>
            <input id="history-from" type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
        </div>

        <div class="history-field">
            <label for="history-to">Hasta</label>
            <input id="history-to" type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
        </div>

        <div class="history-actions">
            <a class="history-reset" href="<?= base('tablero/historial') ?>">Limpiar</a>
            <button class="history-filter-button" type="submit">Filtrar</button>
        </div>
    </form>

    <section class="history-table">
        <div class="history-table-head">
            <span class="history-table-label">Tarea</span>
            <span class="history-table-label">Lista y responsables</span>
            <span class="history-table-label">Finalizacion</span>
            <span class="history-table-label">Estado</span>
        </div>

        <?php if (empty($cards)): ?>
            <div class="history-shell history-empty">No hay tareas completadas que coincidan con los filtros.</div>
        <?php endif; ?>

        <?php foreach ($cards as $card): ?>
            <?php $cardId = (int)$card['id']; ?>
            <article class="history-shell history-row">
                <div>
                    <div class="history-title-line">
                        <div class="history-title"><?= e($card['title'] ?: 'Sin titulo') ?></div>
                        <?php if (!empty($card['label_text'])): ?>
                            <span class="history-tag">
                                <span class="history-tag-dot" style="background:<?= e($card['label_color'] ?: '#58a6ff') ?>;"></span>
                                <?= e($card['label_text']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($card['description'])): ?>
                        <p class="history-description"><?= e($shortText($card['description'], 170)) ?></p>
                    <?php endif; ?>
                    <button class="history-trace-button" type="button" data-history-card="<?= $cardId ?>">Ver trazabilidad</button>
                </div>

                <div>
                    <div class="history-list-name">
                        <span class="history-list-dot" style="background:<?= e($card['list_color'] ?: '#58a6ff') ?>;"></span>
                        <span><?= e($card['list_name']) ?></span>
                    </div>
                    <div class="history-members">
                        <?php if (empty($card['assignees'])): ?>
                            <span class="history-member">Sin asignar</span>
                        <?php else: ?>
                            <?php foreach ($card['assignees'] as $member): ?>
                                <span class="history-member"><?= e($member['username']) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="history-meta">
                    <strong><?= e($card['completed_by_name'] ?: 'Usuario no disponible') ?></strong><br>
                    <?= e($formatDate((string)$card['completed_at'])) ?>
                </div>

                <div class="history-meta">
                    <span class="history-state">Archivada</span>
                    <?php if (!empty($card['archived_at'])): ?>
                        <div style="margin-top:7px;">Archivada <?= e($formatDate((string)$card['archived_at'], 'd/m/Y')) ?></div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <?php if ($totalPages > 1): ?>
        <nav class="history-pagination" aria-label="Paginacion del historial">
            <?php if ($page > 1): ?>
                <a href="<?= e($pageUrl($page - 1)) ?>">Anterior</a>
            <?php endif; ?>
            <span>Pagina <?= $page ?> de <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= e($pageUrl($page + 1)) ?>">Siguiente</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>

<div class="history-modal" id="history-trace-modal" hidden role="dialog" aria-modal="true" aria-labelledby="history-modal-title">
    <div class="history-modal-backdrop" data-history-close></div>
    <section class="history-modal-panel" tabindex="-1">
        <header class="history-modal-head">
            <div>
                <div class="history-modal-badges">
                    <span class="history-modal-badge">Registro de trazabilidad</span>
                    <span class="history-modal-badge is-green" id="history-modal-status">Estado: archivado</span>
                    <span class="history-modal-id" id="history-modal-id">ID: card</span>
                </div>
                <h2 class="history-modal-title" id="history-modal-title">Tarea</h2>
                <p class="history-modal-origin">Origen: <strong id="history-modal-origin">Lista</strong></p>
            </div>
            <button class="history-modal-close" type="button" aria-label="Cerrar" data-history-close>&times;</button>
        </header>

        <div class="history-modal-grid">
            <aside class="history-summary-col">
                <section class="history-detail-card is-strong">
                    <span class="history-card-label">Descripcion de la tarea</span>
                    <p class="history-detail-text" id="history-modal-description">Sin descripcion registrada.</p>
                    <button class="history-read-more" id="history-description-toggle" type="button" hidden>Leer mas</button>
                </section>

                <section class="history-detail-card is-strong">
                    <span class="history-card-label">Detalle de cierre</span>
                    <div class="history-detail-line">
                        <span>Finalizado el</span>
                        <strong id="history-modal-completed">-</strong>
                    </div>
                    <div class="history-divider"></div>
                    <span class="history-card-label">Responsables</span>
                    <div class="history-members" id="history-modal-assignees"></div>
                </section>

                <section class="history-detail-card is-strong">
                    <span class="history-card-label">Requisitos cumplidos</span>
                    <div class="history-requirements" id="history-modal-requirements"></div>
                </section>
            </aside>

            <main class="history-timeline-col">
                <section class="history-chat-card">
                    <span class="history-card-label" id="history-comments-title">Linea de tiempo de comentarios</span>
                    <div class="history-comments" id="history-modal-comments"></div>
                    <form class="history-audit-form" id="history-audit-form" method="POST">
                        <input type="hidden" name="return_to" id="history-return-to" value="<?= e($currentHistoryPath) ?>">
                        <input type="text" name="body" id="history-audit-body" autocomplete="off" placeholder="Escribir una anotacion en esta bitacora historica..." required>
                        <button type="submit" id="history-audit-submit" aria-label="Anotar">
                            <svg class="history-audit-send-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M22 2 11 13"></path>
                                <path d="M22 2 15 22 11 13 2 9 22 2Z"></path>
                            </svg>
                            <span>Anotar</span>
                        </button>
                    </form>
                    <div class="history-audit-status" id="history-audit-status" aria-live="polite"></div>
                </section>

                <section class="history-attachments">
                    <span class="history-card-label">Documentos adjuntos registrados</span>
                    <div class="history-attachment-section">
                        <div class="history-attachment-section-title">
                            <span>Documentacion inicial del proyecto</span>
                            <small>material de trabajo</small>
                        </div>
                        <div class="history-attachment-grid" id="history-modal-work-attachments"></div>
                    </div>
                    <div class="history-attachment-section">
                        <div class="history-attachment-section-title">
                            <span>Documentacion final del proyecto entregada</span>
                            <small>cierre de tarea</small>
                        </div>
                        <div class="history-attachment-grid" id="history-modal-completion-attachments"></div>
                    </div>
                </section>
            </main>
        </div>
    </section>
</div>

<script type="application/json" id="history-trace-data"><?= $traceJson ?: '{}' ?></script>
<script>
(() => {
    const dataNode = document.getElementById('history-trace-data');
    const modal = document.getElementById('history-trace-modal');
    const modalPanel = modal?.querySelector('.history-modal-panel');
    const currentUserId = <?= (int)($currentUserId ?? 0) ?>;
    const currentUserName = <?= json_encode((string)($currentUserName ?? 'Usuario'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const cards = dataNode ? JSON.parse(dataNode.textContent || '{}') : {};

    let activeCard = null;
    let descriptionExpanded = false;

    const titleNode = document.getElementById('history-modal-title');
    const statusNode = document.getElementById('history-modal-status');
    const idNode = document.getElementById('history-modal-id');
    const originNode = document.getElementById('history-modal-origin');
    const descriptionNode = document.getElementById('history-modal-description');
    const descriptionToggle = document.getElementById('history-description-toggle');
    const completedNode = document.getElementById('history-modal-completed');
    const assigneesNode = document.getElementById('history-modal-assignees');
    const requirementsNode = document.getElementById('history-modal-requirements');
    const commentsNode = document.getElementById('history-modal-comments');
    const commentsTitle = document.getElementById('history-comments-title');
    const workAttachmentsNode = document.getElementById('history-modal-work-attachments');
    const completionAttachmentsNode = document.getElementById('history-modal-completion-attachments');
    const auditForm = document.getElementById('history-audit-form');
    const auditBody = document.getElementById('history-audit-body');
    const auditStatus = document.getElementById('history-audit-status');
    const returnTo = document.getElementById('history-return-to');

    function updateAuditButtonState() {
        if (!auditForm || !auditBody) {
            return;
        }

        auditForm.classList.toggle('has-text', auditBody.value.trim() !== '');
    }

    function text(value, fallback = '') {
        value = value === null || value === undefined ? '' : String(value);
        return value.trim() === '' ? fallback : value;
    }

    function shortText(value, limit = 360) {
        value = text(value);
        return value.length > limit ? value.slice(0, limit - 1) + '...' : value;
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }

        const date = new Date(String(value).replace(' ', 'T'));

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        const pad = (number) => String(number).padStart(2, '0');
        return `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }

    function currentDateLabel() {
        const date = new Date();
        const pad = (number) => String(number).padStart(2, '0');
        return `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }

    function parseDate(value) {
        if (!value) {
            return null;
        }

        const timestamp = Date.parse(String(value).replace(' ', 'T'));
        return Number.isNaN(timestamp) ? null : timestamp;
    }

    function formatBytes(bytes) {
        bytes = Number(bytes || 0);

        if (bytes <= 0) {
            return '0 B';
        }

        const units = ['B', 'KB', 'MB', 'GB'];
        let index = 0;
        let size = bytes;

        while (size >= 1024 && index < units.length - 1) {
            size = size / 1024;
            index++;
        }

        return `${index === 0 ? Math.round(size) : size.toFixed(1).replace(/\.0$/, '')} ${units[index]}`;
    }

    function markAuditFlags(card) {
        const archivedAt = parseDate(card.archived_at);

        card.comments = (card.comments || []).map((comment) => {
            const createdAt = parseDate(comment.created_at);
            const isFinal = Number(comment.is_final || 0) === 1 || comment.is_final === true;

            return {
                ...comment,
                user_id: Number(comment.user_id || 0),
                author_name: text(comment.author_name, 'Usuario'),
                body: text(comment.body),
                is_final: isFinal,
                is_audit: comment.is_audit === true || comment.pending === true || (archivedAt !== null && createdAt !== null && createdAt >= archivedAt && !isFinal),
                created_label: text(comment.created_label) || formatDate(comment.created_at),
            };
        });
    }

    function renderPill(container, label) {
        const pill = document.createElement('span');
        pill.className = 'history-member';
        pill.textContent = label;
        container.appendChild(pill);
    }

    function renderDescription(card) {
        const description = text(card.description, 'Sin descripcion registrada.');
        const isLong = description.length > 380;
        descriptionNode.textContent = descriptionExpanded || !isLong ? description : shortText(description, 380);
        descriptionToggle.hidden = !isLong;
        descriptionToggle.textContent = descriptionExpanded ? 'Ver menos' : 'Leer mas';
    }

    function renderRequirements(card) {
        requirementsNode.innerHTML = '';

        if (!card.requirements || card.requirements.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'history-requirement';
            empty.textContent = 'Esta tarjeta no tuvo requisitos especiales de cierre.';
            requirementsNode.appendChild(empty);
            return;
        }

        card.requirements.forEach((requirement) => {
            const item = document.createElement('div');
            item.className = 'history-requirement' + (requirement.complete ? ' is-complete' : '');

            const check = document.createElement('span');
            check.className = 'history-check';
            check.textContent = requirement.complete ? '✓' : '○';

            const label = document.createElement('span');
            label.textContent = requirement.label;

            item.append(check, label);
            requirementsNode.appendChild(item);
        });
    }

    function renderComments(card) {
        markAuditFlags(card);
        commentsNode.innerHTML = '';
        commentsTitle.textContent = `Linea de tiempo de comentarios (${(card.comments || []).length})`;

        if (!card.comments || card.comments.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'history-meta';
            empty.style.textAlign = 'center';
            empty.style.margin = 'auto';
            empty.textContent = 'No se registraron comentarios de avance o cierre.';
            commentsNode.appendChild(empty);
            return;
        }

        card.comments.forEach((comment) => {
            const own = currentUserId > 0 && Number(comment.user_id || 0) === currentUserId;
            const item = document.createElement('div');
            item.className = 'history-comment' + (own ? ' is-own' : '');

            const meta = document.createElement('div');
            meta.className = 'history-comment-meta';
            meta.textContent = `${text(comment.author_name, 'Usuario')} - ${text(comment.created_label) || formatDate(comment.created_at)}`;

            const bubble = document.createElement('div');
            bubble.className = 'history-comment-bubble';

            if (comment.is_audit) {
                bubble.classList.add('is-audit');
                const label = document.createElement('span');
                label.className = 'history-audit-label';
                const dot = document.createElement('span');
                dot.className = 'history-audit-dot';
                label.append(dot, document.createTextNode('Anotacion de auditoria historica'));
                bubble.appendChild(label);
            } else if (comment.is_final) {
                bubble.classList.add('is-final');
                const label = document.createElement('span');
                label.className = 'history-final-label';
                label.textContent = 'Comentario final de cierre';
                bubble.appendChild(label);
            }

            bubble.appendChild(document.createTextNode(text(comment.body)));
            item.append(meta, bubble);
            commentsNode.appendChild(item);
        });

        commentsNode.scrollTop = commentsNode.scrollHeight;
    }

    function renderAttachmentGroup(container, attachments, emptyText) {
        if (!container) {
            return;
        }

        container.innerHTML = '';

        if (!attachments || attachments.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'history-meta';
            empty.style.gridColumn = '1 / -1';
            empty.style.textAlign = 'center';
            empty.style.padding = '16px';
            empty.textContent = emptyText;
            container.appendChild(empty);
            return;
        }

        attachments.forEach((attachment) => {
            const item = document.createElement('div');
            item.className = 'history-attachment';

            const icon = document.createElement('div');
            icon.className = 'history-attachment-icon';

            if (attachment.is_image && attachment.preview_url) {
                const img = document.createElement('img');
                img.src = attachment.preview_url;
                img.alt = attachment.filename;
                icon.appendChild(img);
            } else {
                icon.textContent = 'DOC';
            }

            const info = document.createElement('div');
            info.className = 'history-attachment-info';

            const name = document.createElement('div');
            name.className = 'history-attachment-name';
            name.title = attachment.filename;
            name.textContent = attachment.filename;

            const size = document.createElement('div');
            size.className = 'history-attachment-size';
            size.textContent = attachment.size_label || formatBytes(attachment.size);

            info.append(name, size);

            const view = document.createElement('a');
            view.href = attachment.preview_url || attachment.download_url || '#';
            view.target = '_blank';
            view.rel = 'noopener noreferrer';
            view.textContent = 'Ver';

            item.append(icon, info, view);
            container.appendChild(item);
        });
    }

    function renderAttachments(card) {
        const attachments = card.attachments || [];
        const workAttachments = attachments.filter((attachment) => (attachment.purpose || 'completion') === 'work');
        const completionAttachments = attachments.filter((attachment) => (attachment.purpose || 'completion') !== 'work');

        renderAttachmentGroup(
            workAttachmentsNode,
            workAttachments,
            'No se registro documentacion inicial o de trabajo para esta tarea.'
        );
        renderAttachmentGroup(
            completionAttachmentsNode,
            completionAttachments,
            'No se registro documentacion final entregada para esta tarea.'
        );
    }

    function renderModal(card) {
        activeCard = card;
        descriptionExpanded = false;
        markAuditFlags(card);

        titleNode.textContent = text(card.title, 'Sin titulo');
        statusNode.textContent = `Estado: archivado (${text(card.archive_mode, 'manual')})`;
        idNode.textContent = `ID: card-${card.id}`;
        originNode.textContent = text(card.list_name, 'Sin lista');
        completedNode.textContent = text(card.completed_label, '-');
        auditForm.action = card.comment_action;
        returnTo.value = text(card.return_to, '/tablero/historial');
        auditBody.value = '';
        updateAuditButtonState();
        auditStatus.textContent = '';

        renderDescription(card);
        renderRequirements(card);
        renderComments(card);
        renderAttachments(card);

        assigneesNode.innerHTML = '';
        if (!card.assignees || card.assignees.length === 0) {
            renderPill(assigneesNode, 'Sin asignar');
        } else {
            card.assignees.forEach((user) => renderPill(assigneesNode, user.username || 'Usuario'));
        }
    }

    function openModal(cardId) {
        const card = cards[String(cardId)];
        if (!card || !modal) {
            return;
        }

        renderModal(card);
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        window.setTimeout(() => modalPanel?.focus?.(), 0);
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.style.overflow = '';
        activeCard = null;
    }

    document.querySelectorAll('[data-history-card]').forEach((button) => {
        button.addEventListener('click', () => openModal(button.getAttribute('data-history-card')));
    });

    modal?.querySelectorAll('[data-history-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeModal();
        }
    });

    descriptionToggle?.addEventListener('click', () => {
        if (!activeCard) {
            return;
        }

        descriptionExpanded = !descriptionExpanded;
        renderDescription(activeCard);
    });

    auditBody?.addEventListener('input', updateAuditButtonState);

    auditForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!activeCard || auditForm.dataset.submitting === '1') {
            return;
        }

        const body = auditBody.value.trim();

        if (body === '') {
            return;
        }

        const formData = new FormData(auditForm);
        formData.set('body', body);

        auditForm.dataset.submitting = '1';
        const tempId = `pending-${Date.now()}`;
        const optimisticComment = {
            id: tempId,
            user_id: currentUserId,
            author_name: currentUserName || 'Usuario',
            body,
            is_final: false,
            is_audit: true,
            pending: true,
            created_at: new Date().toISOString(),
            created_label: currentDateLabel(),
        };

        activeCard.comments = [...(activeCard.comments || []), optimisticComment];
        auditBody.value = '';
        updateAuditButtonState();
        renderComments(activeCard);
        auditStatus.textContent = 'Guardando anotacion...';

        try {
            const response = await fetch(auditForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload.message || 'No se pudo guardar la anotacion.');
            }

            if (payload.comment) {
                activeCard.comments = (activeCard.comments || []).map((comment) => {
                    return comment.id === tempId ? payload.comment : comment;
                });
            } else {
                activeCard.comments = payload.comments || activeCard.comments || [];
                activeCard.attachments = payload.attachments || activeCard.attachments || [];
            }

            auditStatus.textContent = 'Anotacion registrada.';
            renderComments(activeCard);
            window.setTimeout(() => {
                if (auditStatus.textContent === 'Anotacion registrada.') {
                    auditStatus.textContent = '';
                }
            }, 1800);
        } catch (error) {
            activeCard.comments = (activeCard.comments || []).filter((comment) => comment.id !== tempId);
            auditBody.value = body;
            updateAuditButtonState();
            renderComments(activeCard);
            auditStatus.textContent = error.message || 'No se pudo guardar la anotacion.';
        } finally {
            auditForm.dataset.submitting = '0';
        }
    });
})();
</script>

<?php

namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Core\Response;
use OfficeHub\Core\Session;
use OfficeHub\Models\KanbanCardAttachment;
use OfficeHub\Models\KanbanCardComment;
use OfficeHub\Models\KanbanCard;
use OfficeHub\Models\KanbanList;
use OfficeHub\Models\Notification;
use OfficeHub\Models\User;
use Throwable;

class BoardController extends Controller
{
    private const COMPLETED_ARCHIVE_AMOUNT = 7;
    private const COMPLETED_ARCHIVE_UNIT = 'DAY';
    private const ALLOWED_ATTACHMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'csv', 'log'];
    private const MAX_ATTACHMENT_SIZE = 10485760;

    public function index(array $params = []): void
    {
        $this->requireAuth();
        $this->archiveCompletedCards();

        $lists = KanbanList::all();
        $cards = KanbanCard::allVisible();
        $assignees = KanbanCard::assigneesForCards(array_column($cards, 'id'));
        $traceSummary = KanbanCard::traceSummaryForCards(array_column($cards, 'id'));
        $cardsByList = [];
        $activeUsers = array_values(array_filter(User::all(), fn (array $user): bool => (int)($user['is_active'] ?? 0) === 1));

        foreach ($lists as $list) {
            $cardsByList[(int)$list['id']] = [];
        }

        foreach ($cards as $card) {
            $listId = (int)$card['list_id'];
            $card['assignees'] = $assignees[(int)$card['id']] ?? [];
            $card['trace_summary'] = $traceSummary[(int)$card['id']] ?? [
                'attachment_count' => 0,
                'work_attachment_count' => 0,
                'completion_attachment_count' => 0,
                'comment_count' => 0,
                'has_final_comment' => false,
            ];

            if (!array_key_exists($listId, $cardsByList)) {
                $cardsByList[$listId] = [];
            }

            $cardsByList[$listId][] = $card;
        }

        $this->view('board/index', [
            'title' => 'Tablero',
            'lists' => $lists,
            'cards' => $cards,
            'cardsByList' => $cardsByList,
            'users' => $activeUsers,
            'currentUserId' => (int)($this->currentUser()['id'] ?? 0),
            'currentUserName' => (string)($this->currentUser()['username'] ?? 'Usuario'),
            'boardVersion' => KanbanCard::boardVersion(),
        ]);
    }

    public function version(array $params = []): void
    {
        $this->requireAuth();
        $this->archiveCompletedCards();

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->json([
            'ok' => true,
            'version' => KanbanCard::boardVersion(),
        ]);
    }

    public function history(array $params = []): void
    {
        $this->requireAuth();
        $this->archiveCompletedCards();

        $filters = [
            'query' => trim((string)$this->request->query('q', '')),
            'list_id' => (int)$this->request->query('list_id', 0),
            'date_from' => $this->validDate((string)$this->request->query('date_from', '')),
            'date_to' => $this->validDate((string)$this->request->query('date_to', '')),
        ];

        $perPage = 30;
        $page = max(1, (int)$this->request->query('page', 1));
        $total = KanbanCard::completedHistoryCount($filters);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $cards = KanbanCard::completedHistory($filters, $perPage, ($page - 1) * $perPage);
        $assignees = KanbanCard::assigneesForCards(array_column($cards, 'id'));
        $commentsByCard = KanbanCardComment::forCards(array_column($cards, 'id'));
        $attachmentsByCard = KanbanCardAttachment::forCards(array_column($cards, 'id'));

        foreach ($cards as &$card) {
            $card['assignees'] = $assignees[(int)$card['id']] ?? [];
        }
        unset($card);

        $this->view('board/history', [
            'title' => 'Historial de tareas',
            'cards' => $cards,
            'lists' => KanbanList::all(),
            'filters' => $filters,
            'commentsByCard' => $commentsByCard,
            'attachmentsByCard' => $attachmentsByCard,
            'currentUserId' => (int)($this->currentUser()['id'] ?? 0),
            'currentUserName' => (string)($this->currentUser()['username'] ?? 'Usuario'),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    private function archiveCompletedCards(): void
    {
        KanbanCard::archiveCompletedOlderThan(
            self::COMPLETED_ARCHIVE_AMOUNT,
            self::COMPLETED_ARCHIVE_UNIT
        );
    }

    public function storeList(array $params = []): void
    {
        $this->requireAuth();

        $name = trim((string)$this->request->input('name', ''));
        $description = trim((string)$this->request->input('description', ''));
        $color = trim((string)$this->request->input('color', '#58a6ff'));

        if ($name === '') {
            Session::flash('error', 'El nombre de la lista es obligatorio.');
            $this->redirect('/tablero');
        }

        KanbanList::create($name, $description, $color);
        Session::flash('success', 'Lista creada.');
        $this->redirect('/tablero');
    }

    public function storeCard(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $listId = (int)$this->request->input('list_id', 0);
        $title = trim((string)$this->request->input('title', ''));

        if ($listId <= 0) {
            Session::flash('error', 'La lista de la tarjeta es obligatoria.');
            $this->redirect('/tablero');
        }

        $assignees = $this->assigneesFromRequest($user, false);
        $cardId = KanbanCard::create([
            'list_id' => $listId,
            'title' => $title,
            'description' => trim((string)$this->request->input('description', '')),
            'label_text' => trim((string)$this->request->input('label_text', '')),
            'label_color' => trim((string)$this->request->input('label_color', '')),
            'due_date' => trim((string)$this->request->input('due_date', '')),
            'auto_archive_on_complete' => (string)$this->request->input('auto_archive_on_complete', '0') === '1',
            'requires_documentation' => (string)$this->request->input('requires_documentation', '0') === '1',
            'requires_final_comment' => (string)$this->request->input('requires_final_comment', '0') === '1',
            'assignees' => $assignees,
            'user_id' => $user['id'] ?? null,
        ]);

        Notification::notifyBoardCardCreated($cardId, $listId, $title, $user, $assignees);

        Session::flash('success', 'Tarjeta creada.');
        $this->redirect('/tablero');
    }

    public function updateCard(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $cardId = (int)($params['id'] ?? 0);

        if ($cardId <= 0) {
            Session::flash('error', 'No se encontro la tarjeta.');
            $this->redirect('/tablero');
        }

        $card = KanbanCard::update($cardId, [
            'title' => trim((string)$this->request->input('title', '')),
            'description' => trim((string)$this->request->input('description', '')),
            'label_text' => trim((string)$this->request->input('label_text', '')),
            'label_color' => trim((string)$this->request->input('label_color', '')),
            'due_date' => trim((string)$this->request->input('due_date', '')),
            'auto_archive_on_complete' => (string)$this->request->input('auto_archive_on_complete', '0') === '1',
            'requires_documentation' => (string)$this->request->input('requires_documentation', '0') === '1',
            'requires_final_comment' => (string)$this->request->input('requires_final_comment', '0') === '1',
            'assignees' => $this->assigneesFromRequest($user, false),
            'user_id' => $user['id'] ?? null,
        ]);

        Session::flash($card ? 'success' : 'error', $card ? 'Tarjeta actualizada.' : 'No se pudo actualizar la tarjeta.');
        $this->redirect('/tablero');
    }

    public function moveCard(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $cardId = (int)$this->request->input('card_id', 0);
        $listId = (int)$this->request->input('list_id', 0);
        $position = trim((string)$this->request->input('position', 'bottom'));

        if ($cardId <= 0 || $listId <= 0) {
            $this->json([
                'ok' => false,
                'message' => 'Faltan datos para mover la tarjeta.',
            ], 422);
        }

        try {
            $card = KanbanCard::move($cardId, $listId, $position, $user['id'] ?? null);

            if (!$card) {
                $this->json([
                    'ok' => false,
                    'message' => 'No se encontro la tarjeta o la lista.',
                ], 404);
            }

            $this->json([
                'ok' => true,
                'list_id' => (int)$card['list_id'],
                'position' => (string)$card['position_value'],
                'version' => KanbanCard::boardVersion(),
            ]);
        } catch (Throwable $exception) {
            $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function completeCard(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $cardId = (int)$this->request->input('card_id', 0);
        $completed = (string)$this->request->input('completed', '0') === '1';

        if ($cardId <= 0) {
            $this->json([
                'ok' => false,
                'message' => 'Falta la tarjeta para actualizar.',
            ], 422);
        }

        try {
            $card = KanbanCard::setComplete($cardId, $completed, $user['id'] ?? null);

            if (!$card) {
                $this->json([
                    'ok' => false,
                    'message' => 'No se encontro la tarjeta.',
                ], 404);
            }

            $this->json([
                'ok' => true,
                'completed' => (bool)$card['is_complete'],
                'in_progress' => (bool)$card['is_in_progress'],
                'version' => KanbanCard::boardVersion(),
            ]);
        } catch (Throwable $exception) {
            $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function progressCard(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $cardId = (int)$this->request->input('card_id', 0);
        $inProgress = (string)$this->request->input('in_progress', '0') === '1';

        if ($cardId <= 0) {
            $this->json([
                'ok' => false,
                'message' => 'Falta la tarjeta para actualizar.',
            ], 422);
        }

        try {
            $card = KanbanCard::setInProgress($cardId, $inProgress, $user['id'] ?? null);

            if (!$card) {
                $this->json([
                    'ok' => false,
                    'message' => 'No se encontro la tarjeta.',
                ], 404);
            }

            $this->json([
                'ok' => true,
                'completed' => (bool)$card['is_complete'],
                'in_progress' => (bool)$card['is_in_progress'],
                'version' => KanbanCard::boardVersion(),
            ]);
        } catch (Throwable $exception) {
            $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function archiveCard(array $params = []): void
{
    $this->requireAuth();

    $user = $this->currentUser();
    $cardId = (int)($params['id'] ?? 0);

    if ($cardId <= 0) {
        Session::flash('error', 'No se encontro la tarjeta.');
        $this->redirect('/tablero');
    }

    $archived = KanbanCard::archiveCompleted(
        $cardId,
        $user['id'] ?? null
    );

    Session::flash(
        $archived ? 'success' : 'error',
        $archived
            ? 'Tarjeta archivada y enviada al historial.'
            : 'Solo se pueden archivar tarjetas completadas.'
    );

    $this->redirect('/tablero');
}

    public function traceCard(array $params = []): void
    {
        $this->requireAuth();

        $cardId = (int)($params['id'] ?? 0);
        $card = $cardId > 0 ? KanbanCard::findAny($cardId) : null;

        if (!$card) {
            $this->json([
                'ok' => false,
                'message' => 'No se encontro la tarjeta.',
            ], 404);
        }

        $this->json($this->cardTracePayload($cardId));
    }

    public function addComment(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $cardId = (int)($params['id'] ?? 0);
        $body = trim((string)$this->request->input('body', ''));
        $isFinal = (string)$this->request->input('is_final', '0') === '1';
        $replyToCommentId = (int)$this->request->input('reply_to_comment_id', 0);
        $returnTo = $this->safeReturnPath((string)$this->request->input('return_to', '/tablero'));
        $card = $cardId > 0 ? KanbanCard::findAny($cardId) : null;

        if ($cardId <= 0 || !$card) {
            if ($this->wantsJson()) {
                $this->json(['ok' => false, 'message' => 'No se encontro la tarjeta.'], 404);
            }

            Session::flash('error', 'No se encontro la tarjeta.');
            $this->redirect($returnTo);
        }

        if ($body === '') {
            if ($this->wantsJson()) {
                $this->json(['ok' => false, 'message' => 'El comentario no puede estar vacio.'], 422);
            }

            Session::flash('error', 'El comentario no puede estar vacio.');
            $this->redirect($returnTo);
        }

        $replyToComment = $replyToCommentId > 0
            ? KanbanCardComment::findForCard($replyToCommentId, $cardId)
            : null;

        if ($replyToCommentId > 0 && !$replyToComment) {
            if ($this->wantsJson()) {
                $this->json(['ok' => false, 'message' => 'No se encontro el comentario a responder.'], 404);
            }

            Session::flash('error', 'No se encontro el comentario a responder.');
            $this->redirect($returnTo);
        }

        $commentId = KanbanCardComment::create(
            $cardId,
            $user['id'] ?? null,
            $body,
            $isFinal,
            $replyToComment ? (int)$replyToComment['id'] : null
        );

        if (empty($card['is_archived'])) {
            Notification::notifyBoardCardCommented($cardId, $commentId, $body, $user, $card, $replyToComment);
        }

        if ($this->wantsJson()) {
            $this->json([
                'ok' => true,
                'comment' => KanbanCardComment::findForCard($commentId, $cardId),
                'completion' => KanbanCard::completionStatus($cardId),
                'version' => KanbanCard::boardVersion(),
                'message' => $isFinal ? 'Comentario final agregado.' : 'Comentario agregado.',
            ]);
        }

        Session::flash('success', $isFinal ? 'Comentario final agregado.' : 'Comentario agregado.');
        $this->redirect($returnTo);
    }

    public function addAttachment(array $params = []): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        $cardId = (int)($params['id'] ?? 0);

        if ($cardId <= 0 || !KanbanCard::find($cardId)) {
            if ($this->wantsJson()) {
                $this->json(['ok' => false, 'message' => 'No se encontro la tarjeta.'], 404);
            }

            Session::flash('error', 'No se encontro la tarjeta.');
            $this->redirect('/tablero');
        }

        $purpose = $this->attachmentPurpose((string)$this->request->input('attachment_purpose', 'completion'));
        $saved = $this->handleCardUploads($cardId, $user['id'] ?? null, $purpose);

        if ($this->wantsJson()) {
            if ($saved <= 0) {
                $this->json(['ok' => false, 'message' => 'No se subio ningun archivo valido.'], 422);
            }

            $payload = $this->cardTracePayload($cardId);
            $label = $purpose === 'work' ? 'documentacion de trabajo' : 'documentacion de cierre';
            $payload['message'] = $saved === 1 ? 'Se agrego ' . $label . '.' : 'Se agregaron adjuntos de ' . $label . '.';
            $this->json($payload);
        }

        Session::flash($saved > 0 ? 'success' : 'error', $saved > 0 ? 'Adjunto agregado.' : 'No se subio ningun archivo valido.');
        $this->redirect('/tablero');
    }

    public function downloadAttachment(array $params = []): never
    {
        $this->requireAuth();

        $cardId = (int)($params['id'] ?? 0);
        $attachment = KanbanCardAttachment::findById((int)($params['attachmentId'] ?? 0));

        if (!$attachment || (int)$attachment['card_id'] !== $cardId) {
            Response::abort(404, 'Adjunto no encontrado.');
        }

        $path = $this->cardAttachmentPath((string)$attachment['path']);

        if (!is_file($path)) {
            Response::abort(404, 'Archivo no encontrado.');
        }

        $mime = $attachment['mime_type'] ?: 'application/octet-stream';
        $forceDownload = (string)($_GET['download'] ?? '0') === '1';
        $preview = (string)($_GET['preview'] ?? '0') === '1';
        $inline = !$forceDownload && (str_starts_with($mime, 'image/') || $mime === 'application/pdf' || str_starts_with($mime, 'text/'));
        $downloadName = str_replace('"', '', basename((string)$attachment['filename']));

        if (!$forceDownload && $preview && $this->isOfficeAttachment($mime, $downloadName)) {
            $previewPath = $this->officePreviewPath($path, (string)$attachment['path'], $downloadName);

            if ($previewPath !== null && is_file($previewPath)) {
                $this->streamAttachmentFile($previewPath, 'application/pdf', pathinfo($downloadName, PATHINFO_FILENAME) . '.pdf', true);
            }

            $this->servePreviewUnavailable($downloadName);
        }

        $this->streamAttachmentFile($path, $mime, $downloadName, $inline);
    }

    public function deleteAttachment(array $params = []): void
    {
        $this->requireAuth();

        $cardId = (int)($params['id'] ?? 0);
        $attachmentId = (int)($params['attachmentId'] ?? 0);
        $attachment = KanbanCardAttachment::findById($attachmentId);

        if (!$attachment || (int)$attachment['card_id'] !== $cardId) {
            if ($this->wantsJson()) {
                $this->json(['ok' => false, 'message' => 'Adjunto no encontrado.'], 404);
            }

            Session::flash('error', 'Adjunto no encontrado.');
            $this->redirect('/tablero');
        }

        $path = $this->cardAttachmentPath((string)$attachment['path']);

        KanbanCardAttachment::deleteById($attachmentId);

        if (is_file($path) && !@unlink($path)) {
            error_log('[OfficeHub] No se pudo eliminar adjunto fisico: ' . $path);
        }

        if ($this->wantsJson()) {
            $payload = $this->cardTracePayload($cardId);
            $payload['message'] = 'Adjunto eliminado.';
            $this->json($payload);
        }

        Session::flash('success', 'Adjunto eliminado.');
        $this->redirect('/tablero');
    }

    public function deleteCard(array $params = []): void
    {
        $this->requireAuth();

        $cardId = (int)($params['id'] ?? 0);

        if ($cardId > 0) {
            KanbanCard::delete($cardId);
            Session::flash('success', 'Tarjeta eliminada.');
        }

        $this->redirect('/tablero');
    }

    private function cardTracePayload(int $cardId): array
    {
        $attachments = array_map(function (array $attachment) use ($cardId): array {
            $url = base('tablero/cards/' . $cardId . '/attachments/' . (int)$attachment['id']);

            return [
                'id' => (int)$attachment['id'],
                'filename' => $attachment['filename'],
                'mime_type' => $attachment['mime_type'],
                'size' => (int)($attachment['size'] ?? 0),
                'is_image' => (int)($attachment['is_image'] ?? 0) === 1,
                'purpose' => $this->attachmentPurpose((string)($attachment['purpose'] ?? 'completion')),
                'created_at' => $attachment['created_at'],
                'uploader_name' => $attachment['uploader_name'] ?? 'Usuario',
                'url' => $url,
                'preview_url' => $url . '?preview=1',
                'download_url' => $url . '?download=1',
                'delete_url' => $url . '/delete',
            ];
        }, KanbanCardAttachment::forCard($cardId));

        return [
            'ok' => true,
            'comments' => KanbanCardComment::forCard($cardId),
            'attachments' => $attachments,
            'completion' => KanbanCard::completionStatus($cardId),
            'version' => KanbanCard::boardVersion(),
        ];
    }

    private function wantsJson(): bool
    {
        $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));

        return $requestedWith === 'xmlhttprequest' || str_contains($accept, 'application/json');
    }

    private function safeReturnPath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//') || str_contains($path, '://')) {
            return '/tablero';
        }

        return $path;
    }

    private function assigneesFromRequest(?array $user, bool $defaultToCurrentUser): array
    {
        $assignees = $this->request->input('assignees', []);
        $withoutAssignee = (string)$this->request->input('without_assignee', '0') === '1';

        if (!is_array($assignees)) {
            $assignees = [$assignees];
        }

        if ($withoutAssignee) {
            return [];
        }

        $assignees = array_values(array_filter(array_map('intval', $assignees)));

        if (empty($assignees) && $defaultToCurrentUser && !empty($user['id'])) {
            return [(int)$user['id']];
        }

        return $assignees;
    }

    private function validDate(string $date): string
    {
        $date = trim($date);
        if ($date === '') {
            return '';
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date ? $date : '';
    }

    private function handleCardUploads(int $cardId, ?int $userId, string $purpose = 'completion'): int
    {
        if (empty($_FILES['attachments']) || !is_array($_FILES['attachments']['name'])) {
            return 0;
        }

        $basePath = $this->cardUploadsBasePath();

        if (!is_dir($basePath)) {
            mkdir($basePath, 0775, true);
        }

        $saved = 0;

        foreach ($_FILES['attachments']['name'] as $index => $name) {
            if (($_FILES['attachments']['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (($_FILES['attachments']['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                continue;
            }

            $size = (int)($_FILES['attachments']['size'][$index] ?? 0);
            $extension = strtolower(pathinfo((string)$name, PATHINFO_EXTENSION));

            if ($size <= 0 || $size > self::MAX_ATTACHMENT_SIZE || !in_array($extension, self::ALLOWED_ATTACHMENT_EXTENSIONS, true)) {
                continue;
            }

            $safeName = $this->sanitizeFilename((string)$name);
            $storedName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeName;
            $target = $basePath . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($_FILES['attachments']['tmp_name'][$index], $target)) {
                continue;
            }

            $mime = mime_content_type($target) ?: ($_FILES['attachments']['type'][$index] ?? 'application/octet-stream');

            KanbanCardAttachment::create([
                'card_id' => $cardId,
                'uploaded_by' => $userId,
                'filename' => (string)$name,
                'path' => $storedName,
                'mime_type' => $mime,
                'size' => $size,
                'is_image' => str_starts_with($mime, 'image/'),
                'purpose' => $purpose,
            ]);

            $saved++;
        }

        return $saved;
    }

    private function attachmentPurpose(string $purpose): string
    {
        return $purpose === 'work' ? 'work' : 'completion';
    }

    private function cardUploadsBasePath(): string
    {
        $config = require BASE_PATH . '/config/app.php';

        if (!empty($config['kanban_uploads_path'])) {
            return rtrim((string)$config['kanban_uploads_path'], "\\/");
        }

        if (!empty($config['support_uploads_path'])) {
            return dirname((string)$config['support_uploads_path']) . DIRECTORY_SEPARATOR . 'kanban-uploads';
        }

        return BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'kanban-uploads';
    }

    private function cardAttachmentPath(string $relativePath): string
    {
        return $this->cardUploadsBasePath() . DIRECTORY_SEPARATOR . basename($relativePath);
    }

    private function isOfficeAttachment(string $mime, string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'], true)
            || in_array($mime, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ], true);
    }

    private function officePreviewPath(string $sourcePath, string $relativePath, string $filename): ?string
    {
        $config = require BASE_PATH . '/config/app.php';

        if (empty($config['office_preview_enabled'])) {
            return null;
        }

        $soffice = $this->sofficeExecutable((string)($config['soffice_path'] ?? ''));

        if ($soffice === null) {
            $this->logOfficePreviewError($filename, 'No se encontro soffice.exe en la ruta configurada.');
            return null;
        }

        $previewDir = $this->cardUploadsBasePath() . DIRECTORY_SEPARATOR . '_previews';

        if (!is_dir($previewDir) && !@mkdir($previewDir, 0775, true) && !is_dir($previewDir)) {
            $this->logOfficePreviewError($filename, 'No se pudo crear la carpeta de previews: ' . $previewDir);
            return null;
        }

        $cacheKey = sha1($relativePath . '|' . filemtime($sourcePath) . '|' . filesize($sourcePath));
        $previewPath = $previewDir . DIRECTORY_SEPARATOR . $cacheKey . '.pdf';

        if (is_file($previewPath)) {
            return $previewPath;
        }

        $workRoot = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'officehub-office-preview';
        $workDir = $workRoot . DIRECTORY_SEPARATOR . $cacheKey;
        $profileDir = $workDir . DIRECTORY_SEPARATOR . 'profile';

        foreach ([$workRoot, $workDir, $profileDir] as $dir) {
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                $this->logOfficePreviewError($filename, 'No se pudo crear la carpeta temporal: ' . $dir);
                return null;
            }
        }

        $localSource = $workDir . DIRECTORY_SEPARATOR . $this->sanitizeFilename($filename);

        if (!is_file($localSource) && !@copy($sourcePath, $localSource)) {
            $this->logOfficePreviewError($filename, 'No se pudo copiar el archivo a temporal: ' . $localSource);
            return null;
        }

        $command = $this->commandArg($soffice)
            . ' --headless --nologo --nofirststartwizard --nodefault --nolockcheck '
            . $this->commandArg('-env:UserInstallation=' . $this->fileUri($profileDir))
            . ' --convert-to pdf --outdir '
            . $this->commandArg($workDir) . ' '
            . $this->commandArg($localSource) . ' 2>&1';

        $output = [];
        $exitCode = 1;
        @exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->logOfficePreviewError($filename, 'LibreOffice fallo con codigo ' . $exitCode . '. Salida: ' . implode(' | ', $output));
            return null;
        }

        $convertedPath = $workDir . DIRECTORY_SEPARATOR . pathinfo($localSource, PATHINFO_FILENAME) . '.pdf';

        if (!is_file($convertedPath)) {
            $matches = glob($workDir . DIRECTORY_SEPARATOR . '*.pdf') ?: [];
            $convertedPath = $matches[0] ?? '';
        }

        if ($convertedPath === '' || !is_file($convertedPath)) {
            $this->logOfficePreviewError($filename, 'LibreOffice termino sin generar PDF. Salida: ' . implode(' | ', $output));
            return null;
        }

        if (!@copy($convertedPath, $previewPath) && !@rename($convertedPath, $previewPath)) {
            $this->logOfficePreviewError($filename, 'No se pudo guardar el PDF convertido en: ' . $previewPath);
            return null;
        }

        return is_file($previewPath) ? $previewPath : null;
    }

    private function commandArg(string $value): string
    {
        return '"' . str_replace('"', '', $value) . '"';
    }

    private function fileUri(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        if (preg_match('/^[A-Za-z]:\//', $normalized) === 1) {
            return 'file:///' . $normalized;
        }

        return 'file://' . $normalized;
    }

    private function logOfficePreviewError(string $filename, string $message): void
    {
        error_log('[OfficeHub] Preview Office: ' . $filename . ' - ' . $message);
    }

    private function sofficeExecutable(string $configuredPath): ?string
    {
        $configuredComPath = '';

        if ($configuredPath !== '' && strtolower(pathinfo($configuredPath, PATHINFO_EXTENSION)) === 'exe') {
            $configuredComPath = substr($configuredPath, 0, -4) . '.com';
        }

        $candidates = array_filter([
            $configuredComPath,
            $configuredPath,
            'C:/Program Files/LibreOffice/program/soffice.com',
            'C:/Program Files/LibreOffice/program/soffice.exe',
            'C:/Program Files (x86)/LibreOffice/program/soffice.com',
            'C:/Program Files (x86)/LibreOffice/program/soffice.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return str_replace('\\', '/', $candidate);
            }
        }

        return null;
    }

    private function streamAttachmentFile(string $path, string $mime, string $downloadName, bool $inline): never
    {
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header(($inline ? 'Content-Disposition: inline; filename="' : 'Content-Disposition: attachment; filename="') . $downloadName . '"');
        readfile($path);
        exit;
    }

    private function servePreviewUnavailable(string $downloadName): never
    {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        echo '<!doctype html><html lang="es"><body style="font-family:Arial,sans-serif;margin:24px;color:#57606a;">'
            . '<strong style="color:#24292f;">Vista previa no disponible.</strong><br>'
            . 'No se pudo convertir el archivo <strong>' . htmlspecialchars($downloadName, ENT_QUOTES, 'UTF-8') . '</strong> a PDF. '
            . 'Verifica que LibreOffice este instalado en el servidor y que Apache tenga permisos para ejecutarlo.'
            . '</body></html>';
        exit;
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $filename);
        return trim($filename ?: 'archivo', '-');
    }
}

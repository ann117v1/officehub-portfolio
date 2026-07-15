<?php

namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Core\Response;
use OfficeHub\Core\Session;
use OfficeHub\Models\SupportArticle;
use OfficeHub\Models\SupportAttachment;
use OfficeHub\Models\SupportCategory;

class SupportController extends Controller
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'gif'];
    private const MAX_FILE_SIZE = 10485760;

    public function index(array $params = []): void
    {
        $this->requireSupportAccess();

        $query = trim((string)$this->request->query('q', ''));

        $this->view('support/index', [
            'title'          => 'Portal de soporte',
            'categories'     => SupportCategory::allWithArticleCount(),
            'activeCategory' => null,
            'articles'       => SupportArticle::search(null, $query),
            'query'          => $query,
        ]);
    }

    public function category(array $params = []): void
    {
        $this->requireSupportAccess();

        $category = SupportCategory::findBySlug($params['slug'] ?? '');

        if (!$category) {
            Response::abort(404, 'Seccion no encontrada.');
        }

        $query = trim((string)$this->request->query('q', ''));

        $this->view('support/index', [
            'title'          => 'Portal de soporte',
            'categories'     => SupportCategory::allWithArticleCount(),
            'activeCategory' => $category,
            'articles'       => SupportArticle::search((int)$category['id'], $query),
            'query'          => $query,
        ]);
    }

    public function show(array $params = []): void
    {
        $this->requireSupportAccess();

        $article = SupportArticle::findBySlug($params['slug'] ?? '');

        if (!$article) {
            Response::abort(404, 'Articulo no encontrado.');
        }

        $attachments = SupportAttachment::forArticle((int)$article['id']);

        $this->view('support/show', [
            'title'       => $article['title'],
            'article'     => $article,
            'attachments' => $attachments,
            'bodyHtml'    => $this->renderArticleBody($article['body'], $attachments),
        ]);
    }

    public function create(array $params = []): void
    {
        $this->requireSupportAdmin();

        $this->view('support/create', [
            'title'      => 'Nuevo articulo',
            'categories' => SupportCategory::all(),
            'error'      => Session::getFlash('error'),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->requireSupportAdmin();

        $title = trim((string)$this->request->input('title', ''));
        $body = trim((string)$this->request->input('body', ''));
        $type = (string)$this->request->input('type', 'info');
        $categoryId = (int)$this->request->input('category_id', 0);
        $user = $this->currentUser();

        if ($title === '' || $body === '' || $categoryId <= 0 || !in_array($type, ['faq', 'tutorial', 'info'], true)) {
            Session::flash('error', 'Completa titulo, seccion, tipo y contenido.');
            $this->redirect('/soporte/nuevo');
        }

        if (!SupportCategory::findById($categoryId)) {
            Session::flash('error', 'La seccion seleccionada no existe.');
            $this->redirect('/soporte/nuevo');
        }

        $articleId = SupportArticle::create([
            'category_id' => $categoryId,
            'title'       => $title,
            'slug'        => $this->uniqueArticleSlug($title),
            'body'        => $body,
            'type'        => $type,
            'author_id'   => $user['id'],
        ]);

        $this->handleUploads($articleId);
        Session::flash('success', 'Articulo creado correctamente.');

        $article = SupportArticle::findById($articleId);
        $this->redirect('/soporte/articulo/' . $article['slug']);
    }

    public function edit(array $params = []): void
    {
        $this->requireSupportAdmin();

        $article = SupportArticle::findBySlug($params['slug'] ?? '');

        if (!$article) {
            Response::abort(404, 'Articulo no encontrado.');
        }

        $this->view('support/edit', [
            'title'       => 'Editar articulo',
            'article'     => $article,
            'categories'  => SupportCategory::all(),
            'attachments' => SupportAttachment::forArticle((int)$article['id']),
            'error'       => Session::getFlash('error'),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->requireSupportAdmin();

        $article = SupportArticle::findBySlug($params['slug'] ?? '');

        if (!$article) {
            Response::abort(404, 'Articulo no encontrado.');
        }

        $title = trim((string)$this->request->input('title', ''));
        $body = trim((string)$this->request->input('body', ''));
        $type = (string)$this->request->input('type', 'info');
        $categoryId = (int)$this->request->input('category_id', 0);

        if ($title === '' || $body === '' || $categoryId <= 0 || !in_array($type, ['faq', 'tutorial', 'info'], true)) {
            Session::flash('error', 'Completa titulo, seccion, tipo y contenido.');
            $this->redirect('/soporte/articulo/' . $article['slug'] . '/editar');
        }

        if (!SupportCategory::findById($categoryId)) {
            Session::flash('error', 'La seccion seleccionada no existe.');
            $this->redirect('/soporte/articulo/' . $article['slug'] . '/editar');
        }

        $this->deleteSelectedAttachments((int)$article['id']);

        $slug = $title === $article['title']
            ? $article['slug']
            : $this->uniqueArticleSlug($title, (int)$article['id']);

        SupportArticle::update((int)$article['id'], [
            'category_id' => $categoryId,
            'title'       => $title,
            'slug'        => $slug,
            'body'        => $body,
            'type'        => $type,
        ]);

        $this->handleUploads((int)$article['id']);
        Session::flash('success', 'Articulo actualizado.');
        $this->redirect('/soporte/articulo/' . $slug);
    }

    public function delete(array $params = []): void
    {
        $this->requireSupportAdmin();

        $article = SupportArticle::findBySlug($params['slug'] ?? '');

        if (!$article) {
            Response::abort(404, 'Articulo no encontrado.');
        }

        foreach (SupportAttachment::forArticle((int)$article['id']) as $attachment) {
            $this->deletePhysicalFile($attachment);
        }

        SupportArticle::delete((int)$article['id']);
        Session::flash('success', 'Articulo eliminado.');
        $this->redirect('/soporte');
    }

    public function download(array $params = []): never
    {
        $this->requireSupportAccess();

        $attachment = SupportAttachment::findById((int)($params['id'] ?? 0));

        if (!$attachment) {
            Response::abort(404, 'Adjunto no encontrado.');
        }

        $path = $this->physicalPath($attachment['path']);

        if (!is_file($path)) {
            Response::abort(404, 'Archivo no encontrado.');
        }

        $mime = $attachment['mime_type'] ?: 'application/octet-stream';
        $disposition = ((int)$attachment['is_image'] === 1) ? 'inline' : 'attachment';
        $downloadName = str_replace('"', '', basename($attachment['filename']));

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header($disposition === 'attachment'
            ? 'Content-Disposition: attachment; filename="' . $downloadName . '"'
            : 'Content-Disposition: inline; filename="' . $downloadName . '"');
        readfile($path);
        exit;
    }

    private function handleUploads(int $articleId): void
    {
        if (empty($_FILES['attachments']) || !is_array($_FILES['attachments']['name'])) {
            return;
        }

        $config = require BASE_PATH . '/config/app.php';
        $basePath = rtrim($config['support_uploads_path'], "\\/");

        if (!is_dir($basePath)) {
            mkdir($basePath, 0775, true);
        }

        foreach ($_FILES['attachments']['name'] as $index => $name) {
            if (($_FILES['attachments']['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (($_FILES['attachments']['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                Session::flash('error', 'Uno de los archivos no pudo subirse.');
                continue;
            }

            $size = (int)($_FILES['attachments']['size'][$index] ?? 0);
            $extension = strtolower(pathinfo((string)$name, PATHINFO_EXTENSION));

            if ($size > self::MAX_FILE_SIZE || !in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                Session::flash('error', 'Se omitio un archivo por tipo no permitido o por superar 10MB.');
                continue;
            }

            $safeName = $this->sanitizeFilename((string)$name);
            $storedName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeName;
            $target = $basePath . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($_FILES['attachments']['tmp_name'][$index], $target)) {
                Session::flash('error', 'No se pudo guardar uno de los archivos.');
                continue;
            }

            $mime = mime_content_type($target) ?: ($_FILES['attachments']['type'][$index] ?? 'application/octet-stream');

            SupportAttachment::create([
                'article_id' => $articleId,
                'filename'   => (string)$name,
                'path'       => $storedName,
                'mime_type'  => $mime,
                'size'       => $size,
                'is_image'   => str_starts_with($mime, 'image/'),
            ]);
        }
    }

    private function deleteSelectedAttachments(int $articleId): void
    {
        $ids = $_POST['delete_attachments'] ?? [];

        if (!is_array($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $attachment = SupportAttachment::findById((int)$id);

            if (!$attachment || (int)$attachment['article_id'] !== $articleId) {
                continue;
            }

            $this->deletePhysicalFile($attachment);
            SupportAttachment::delete((int)$attachment['id']);
        }
    }

    private function deletePhysicalFile(array $attachment): void
    {
        $path = $this->physicalPath($attachment['path']);

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function physicalPath(string $relativePath): string
    {
        $config = require BASE_PATH . '/config/app.php';
        return rtrim($config['support_uploads_path'], "\\/") . DIRECTORY_SEPARATOR . basename($relativePath);
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $filename);
        return trim($filename ?: 'archivo', '-');
    }

    private function uniqueArticleSlug(string $title, ?int $exceptId = null): string
    {
        $base = $this->slugify($title) ?: 'articulo';
        $slug = $base;
        $i = 2;

        while (SupportArticle::slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = strtolower((string)$value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string)$value, '-');
    }

    private function renderArticleBody(string $body, array $attachments): string
    {
        $images = [];

        foreach ($attachments as $attachment) {
            if ((int)$attachment['is_image'] === 1) {
                $images[strtolower($attachment['filename'])] = $attachment;
                $images[(string)$attachment['id']] = $attachment;
            }
        }

        $html = [];
        $renderImage = static function (array $image): string {
            return '<figure style="margin:18px 0;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:10px;">'
                . '<img src="' . base('soporte/adjunto/' . $image['id']) . '" alt="' . e($image['filename']) . '" style="max-width:100%;border-radius:6px;display:block;">'
                . '<figcaption style="font-size:11px;color:var(--text-3);margin-top:8px;">' . e($image['filename']) . '</figcaption>'
                . '</figure>';
        };

        foreach (preg_split('/\R/', $body) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $html[] = '<div style="height:10px;"></div>';
                continue;
            }

            if (preg_match('/^\[imagen:\s*(.+?)\]$/i', $trimmed, $match)) {
                $key = strtolower(trim($match[1]));
                $image = $images[$key] ?? null;

                if ($image) {
                    $html[] = $renderImage($image);
                    continue;
                }
            }

            $plainImage = $images[strtolower($trimmed)] ?? $images[(string)$trimmed] ?? null;

            if ($plainImage) {
                $html[] = $renderImage($plainImage);
                continue;
            }

            if (str_starts_with($trimmed, '## ')) {
                $html[] = '<h2 style="font-size:16px;color:var(--text-1);margin:18px 0 8px;">' . e(substr($trimmed, 3)) . '</h2>';
                continue;
            }

            if (str_starts_with($trimmed, '- ')) {
                $html[] = '<p style="font-size:14px;color:var(--text-2);line-height:1.75;margin:4px 0 4px 16px;">- ' . $this->linkify(e(substr($trimmed, 2))) . '</p>';
                continue;
            }

            $html[] = '<p style="font-size:14px;color:var(--text-2);line-height:1.75;margin:0 0 10px;">' . $this->linkify(e($trimmed)) . '</p>';
        }

        return implode("\n", $html);
    }

    private function linkify(string $escapedText): string
    {
        return preg_replace_callback('/https?:\/\/[^\s<]+/', static function (array $match): string {
            $url = $match[0];
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" style="color:var(--accent);text-decoration:none;">' . $url . '</a>';
        }, $escapedText);
    }
}

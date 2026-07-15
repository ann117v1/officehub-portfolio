<?php
/** @var array $article */
/** @var array $attachments */
/** @var string $bodyHtml */

$typeStyles = [
    'faq'      => ['FAQ', 'var(--accent)', 'var(--bg-hover)'],
    'tutorial' => ['Tutorial', 'var(--green)', 'var(--green-bg)'],
    'info'     => ['Info', 'var(--purple)', 'var(--purple-bg)'],
];
$type = $typeStyles[$article['type']] ?? $typeStyles['info'];

function supportFileSize(int $bytes): string
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    }

    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }

    return $bytes . ' B';
}
?>

<div style="max-width:900px;">
    <nav style="font-size:13px;color:var(--text-2);margin-bottom:18px;">
        <a href="<?= base('soporte') ?>" style="color:var(--text-2);text-decoration:none;">Soporte</a>
        <span style="margin:0 6px;">/</span>
        <a href="<?= base('soporte/categoria/' . e($article['category_slug'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($article['category_name']) ?></a>
        <span style="margin:0 6px;">/</span>
        <span style="color:var(--text-1);"><?= e($article['title']) ?></span>
    </nav>

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:20px;">
        <div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                <span style="font-size:11px;color:<?= $type[1] ?>;background:<?= $type[2] ?>;border:1px solid <?= $type[1] ?>;border-radius:20px;padding:2px 8px;"><?= $type[0] ?></span>
                <span style="font-size:11px;color:var(--text-2);background:var(--bg-hover);border:1px solid var(--border-2);border-radius:20px;padding:2px 8px;">
                    <span style="width:7px;height:7px;border-radius:50%;background:<?= e($article['category_color']) ?>;display:inline-block;margin-right:5px;"></span>
                    <?= e($article['category_name']) ?>
                </span>
            </div>
            <h1 style="font-size:28px;line-height:1.25;color:var(--text-1);font-weight:700;margin-bottom:8px;"><?= e($article['title']) ?></h1>
            <p style="font-size:13px;color:var(--text-2);">
                Publicado el <?= dateFormat($article['created_at'], 'd/m/Y') ?> - actualizado el <?= dateFormat($article['updated_at'], 'd/m/Y') ?> - por <?= e($article['author_name']) ?>
            </p>
        </div>

        <?php if (canManageSupport()): ?>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <a href="<?= base('soporte/articulo/' . e($article['slug']) . '/editar') ?>"
                    style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);font-size:13px;padding:7px 12px;border-radius:6px;text-decoration:none;">
                    Editar
                </a>
                <form method="POST" action="<?= base('soporte/articulo/' . e($article['slug']) . '/eliminar') ?>"
                    onsubmit="return confirm('Seguro que queres eliminar este articulo?');">
                    <button type="submit"
                        style="background:var(--red-bg);border:1px solid var(--red);color:var(--red);font-size:13px;padding:7px 12px;border-radius:6px;cursor:pointer;">
                        Eliminar
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <article style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:24px;">
        <?= $bodyHtml ?>

        <?php if (!empty($attachments)): ?>
            <div style="border-top:1px solid var(--border);margin-top:28px;padding-top:18px;">
                <p style="font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:12px;">Archivos adjuntos - <?= count($attachments) ?></p>
                <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                    <?php foreach ($attachments as $attachment): ?>
                        <a href="<?= base('soporte/adjunto/' . $attachment['id']) ?>"
                            style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:12px;text-decoration:none;display:block;">
                            <div style="font-size:13px;color:var(--text-1);font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($attachment['filename']) ?></div>
                            <div style="font-size:12px;color:var(--text-3);margin-top:5px;"><?= supportFileSize((int)$attachment['size']) ?></div>
                            <div style="font-size:12px;color:var(--accent);margin-top:10px;">Descargar</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </article>
</div>

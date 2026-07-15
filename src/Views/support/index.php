<?php
/** @var array $categories */
/** @var array|null $activeCategory */
/** @var array $articles */
/** @var string $query */

$totalArticles = array_sum(array_map(static fn($category) => (int)$category['article_count'], $categories));
$searchAction = $activeCategory ? base('soporte/categoria/' . $activeCategory['slug']) : base('soporte');
$hasCategories = !empty($categories);
$typeStyles = [
    'faq'      => ['FAQ', 'var(--accent)', 'var(--bg-hover)'],
    'tutorial' => ['Tutorial', 'var(--green)', 'var(--green-bg)'],
    'info'     => ['Info', 'var(--purple)', 'var(--purple-bg)'],
];
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
    <div>
        <h1 style="color:var(--text-1);font-size:22px;font-weight:600;margin-bottom:6px;">
            Portal de soporte
            <span style="color:var(--text-3);font-weight:400;">-&gt;</span>
            <span style="color:var(--accent);">Base de conocimiento</span>
        </h1>
        <p style="font-size:13px;color:var(--text-2);">
            <?= count($articles) ?> articulos visibles
            <?php if ($query !== ''): ?>
                para "<?= e($query) ?>"
            <?php endif; ?>
        </p>
    </div>

    <?php if (canManageSupport() && $hasCategories): ?>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="<?= base('admin/support') ?>"
                style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-2);font-size:13px;font-weight:500;padding:7px 14px;border-radius:6px;text-decoration:none;">
                Secciones
            </a>
            <a href="<?= base('soporte/nuevo') ?>"
                style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;text-decoration:none;">
                + Nuevo articulo
            </a>
        </div>
    <?php elseif (canManageSupport()): ?>
        <a href="<?= base('admin/support') ?>"
            style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;text-decoration:none;">
            Crear primera seccion
        </a>
    <?php endif; ?>
</div>

<?php if ($hasCategories): ?>
    <form method="GET" action="<?= $searchAction ?>" style="display:flex;align-items:center;gap:10px;margin:14px 0 22px;">
        <div style="position:relative;flex:1;max-width:620px;">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:13px;">/</span>
            <input type="search" name="q" value="<?= e($query) ?>" placeholder="Buscar articulos por titulo o contenido..."
                autocomplete="off"
                style="width:100%;background:var(--bg-surface);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:9px 12px 9px 34px;font-size:13px;outline:none;"
                onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
        </div>
        <button type="submit"
            style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);font-size:13px;padding:9px 14px;border-radius:6px;cursor:pointer;">
            Buscar
        </button>
        <?php if ($query !== ''): ?>
            <a href="<?= $searchAction ?>" style="font-size:13px;color:var(--text-2);text-decoration:none;">Limpiar</a>
        <?php endif; ?>
    </form>
<?php endif; ?>

<div style="display:grid;grid-template-columns:220px 1fr;gap:22px;margin-top:22px;">
    <aside style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:14px;height:max-content;">
        <p style="font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">Secciones</p>

        <?php if (!$hasCategories): ?>
            <p style="font-size:13px;color:var(--text-2);line-height:1.5;">Todavia no hay secciones creadas.</p>
        <?php else: ?>
            <a href="<?= base('soporte') ?>"
                style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border-radius:6px;text-decoration:none;color:<?= $activeCategory ? 'var(--text-2)' : 'var(--text-1)' ?>;background:<?= $activeCategory ? 'transparent' : 'var(--bg-hover)' ?>;border-left:2px solid <?= $activeCategory ? 'transparent' : 'var(--accent)' ?>;">
                <span style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:<?= $activeCategory ? '400' : '600' ?>;">
                    <span style="width:7px;height:7px;border-radius:50%;background:var(--text-3);display:inline-block;"></span>
                    Todos los articulos
                </span>
                <span style="font-size:11px;color:var(--text-3);"><?= $totalArticles ?></span>
            </a>

            <div style="display:flex;flex-direction:column;gap:4px;margin-top:6px;">
                <?php foreach ($categories as $category): ?>
                    <?php $isActive = $activeCategory && (int)$activeCategory['id'] === (int)$category['id']; ?>
                    <a href="<?= base('soporte/categoria/' . e($category['slug'])) ?>"
                        style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border-radius:6px;text-decoration:none;color:<?= $isActive ? 'var(--text-1)' : 'var(--text-2)' ?>;background:<?= $isActive ? 'var(--bg-hover)' : 'transparent' ?>;border-left:2px solid <?= $isActive ? 'var(--accent)' : 'transparent' ?>;">
                        <span style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:<?= $isActive ? '600' : '400' ?>;">
                            <span style="width:7px;height:7px;border-radius:50%;background:<?= e($category['color']) ?>;display:inline-block;"></span>
                            <?= e($category['name']) ?>
                        </span>
                        <span style="font-size:11px;color:var(--text-3);"><?= (int)$category['article_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </aside>

    <main>
        <?php if (!$hasCategories): ?>
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:36px;text-align:center;">
                <h2 style="font-size:17px;color:var(--text-1);font-weight:600;margin-bottom:8px;">Crea la primera seccion de la base de conocimiento</h2>
                <p style="font-size:14px;color:var(--text-2);line-height:1.6;margin-bottom:18px;">
                    Las secciones organizan los articulos. Despues de crear una seccion, vas a poder cargar articulos, imagenes y adjuntos dentro de ella.
                </p>
                <?php if (canManageSupport()): ?>
                    <a href="<?= base('admin/support') ?>"
                        style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:8px 16px;border-radius:6px;text-decoration:none;">
                        Ir a gestionar secciones
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <?php if (empty($articles)): ?>
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:36px;text-align:center;">
                <p style="font-size:14px;color:var(--text-2);">No hay articulos para mostrar.</p>
            </div>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
                <?php foreach ($articles as $article): ?>
                    <?php
                    $type = $typeStyles[$article['type']] ?? $typeStyles['info'];
                    $preview = trim(preg_replace('/\s+/', ' ', strip_tags((string)$article['body'])));
                    $preview = strlen($preview) > 220 ? substr($preview, 0, 220) . '...' : $preview;
                    $attachmentIds = $article['attachment_ids'] ? explode(',', $article['attachment_ids']) : [];
                    $attachmentNames = $article['attachment_names'] ? explode('||', $article['attachment_names']) : [];
                    ?>
                    <article style="position:relative;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:16px;min-height:210px;display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
                            <span style="font-size:11px;color:<?= $type[1] ?>;background:<?= $type[2] ?>;border:1px solid <?= $type[1] ?>;border-radius:20px;padding:2px 8px;"><?= $type[0] ?></span>
                            <span style="font-size:11px;color:var(--text-2);background:var(--bg-hover);border:1px solid var(--border-2);border-radius:20px;padding:2px 8px;"><?= e($article['category_name']) ?></span>
                        </div>

                        <a href="<?= base('soporte/articulo/' . e($article['slug'])) ?>"
                            style="font-size:15px;line-height:1.35;color:var(--text-1);font-weight:600;text-decoration:none;">
                            <?= e($article['title']) ?>
                        </a>

                        <p style="font-size:13px;color:var(--text-2);line-height:1.55;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                            <?= e($preview) ?>
                        </p>

                        <?php if (!empty($attachmentNames)): ?>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:auto;">
                                <?php foreach (array_slice($attachmentNames, 0, 3, true) as $i => $filename): ?>
                                    <a href="<?= base('soporte/adjunto/' . e($attachmentIds[$i] ?? '')) ?>"
                                        style="font-size:11px;color:var(--text-2);background:var(--bg-hover);border:1px solid var(--border);border-radius:6px;padding:4px 7px;text-decoration:none;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        Doc <?= e($filename) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;font-size:12px;color:var(--text-3);">
                            <span><?= dateFormat($article['updated_at'], 'd/m/Y') ?> - por <?= e($article['author_name']) ?></span>
                            <?php if (canManageSupport()): ?>
                                <a href="<?= base('soporte/articulo/' . e($article['slug']) . '/editar') ?>" title="Editar"
                                    style="color:var(--text-3);text-decoration:none;">Editar</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

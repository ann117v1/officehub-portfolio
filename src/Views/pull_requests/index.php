<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;">
    <div>
        <nav style="font-size:13px;color:var(--text-2);margin-bottom:4px;">
            <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"
                onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'"><?= e($repo['name']) ?></a>
            <span style="margin:0 6px;">/</span>
            <span style="color:var(--text-1);font-weight:500;">Pull Requests</span>
        </nav>
        <h1 style="color:var(--text-1);font-size:20px;font-weight:600;">Pull Requests</h1>
    </div>
    <?php if (!empty($canCreatePr)): ?>
        <a href="<?= base('repos/' . e($repo['name']) . '/pulls/create') ?>"
            style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;text-decoration:none;">
            + Nuevo PR
        </a>
    <?php endif; ?>
</div>

<!-- Tabs -->
<div style="display:flex;gap:2px;border-bottom:1px solid var(--border);margin-bottom:16px;">
    <?php foreach (['open' => 'Abiertos', 'merged' => 'Mergeados', 'closed' => 'Cerrados'] as $s => $label): ?>
        <a href="<?= base('repos/' . e($repo['name']) . '/pulls') ?>?status=<?= $s ?>"
            style="padding:8px 16px;font-size:13px;text-decoration:none;border-bottom:2px solid <?= $status === $s ? 'var(--accent)' : 'transparent' ?>;color:<?= $status === $s ? 'var(--accent)' : 'var(--text-2)' ?>;font-weight:<?= $status === $s ? '600' : '400' ?>;">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($prs)): ?>
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:48px;text-align:center;">
        <p style="color:var(--text-2);font-size:14px;">
            <?php if ($status === 'open'): ?>
                No hay Pull Requests abiertos.
                <?php if (!empty($canCreatePr)): ?>
                    <a href="<?= base('repos/' . e($repo['name']) . '/pulls/create') ?>"
                        style="color:var(--accent);text-decoration:none;display:block;margin-top:8px;font-size:13px;">
                        Crear el primero
                    </a>
                <?php endif; ?>
            <?php else: ?>
                No hay Pull Requests <?= $status === 'merged' ? 'mergeados' : 'cerrados' ?> todavía.
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
        <?php foreach ($prs as $pr): ?>
            <a href="<?= base('repos/' . e($repo['name']) . '/pulls/' . $pr['id']) ?>"
                style="display:block;padding:14px 16px;border-bottom:1px solid var(--border);text-decoration:none;"
                onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                    <div style="flex:1;min-width:0;">
                        <span style="color:var(--text-1);font-size:14px;font-weight:500;"><?= e($pr['title']) ?></span>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:4px;flex-wrap:wrap;">
                            <span style="color:var(--text-3);font-size:12px;">#<?= $pr['id'] ?></span>
                            <span style="color:var(--text-3);font-size:12px;">·</span>
                            <span style="color:var(--text-2);font-size:12px;"><?= e($pr['author_name']) ?></span>
                            <span style="color:var(--text-3);font-size:12px;">·</span>
                            <code style="background:var(--bg-hover);color:var(--text-2);font-size:11px;padding:1px 6px;border-radius:4px;border:1px solid var(--border-2);"><?= e($pr['source_branch']) ?></code>
                            <span style="color:var(--text-3);font-size:12px;">→</span>
                            <code style="background:var(--bg-hover);color:var(--text-2);font-size:11px;padding:1px 6px;border-radius:4px;border:1px solid var(--border-2);"><?= e($pr['target_branch']) ?></code>
                        </div>
                    </div>
                    <span style="color:var(--text-3);font-size:12px;flex-shrink:0;"><?= dateFormat($pr['created_at'], 'd/m/Y') ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
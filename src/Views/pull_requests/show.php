<?php
$statusStyles = [
    'open'   => 'background:#1a3a1a;color:#3fb950;border:1px solid #2ea043;',
    'merged' => 'background:#2d1f3d;color:#a371f7;border:1px solid #8957e5;',
    'closed' => 'background:var(--bg-hover);color:var(--text-2);border:1px solid var(--border-2);',
];
?>

<div style="margin-bottom:20px;">
    <nav style="font-size:13px;color:var(--text-2);margin-bottom:12px;">
        <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($repo['name']) ?></a>
        <span style="margin:0 6px;">/</span>
        <a href="<?= base('repos/' . e($repo['name']) . '/pulls') ?>" style="color:var(--text-2);text-decoration:none;">Pull Requests</a>
        <span style="margin:0 6px;">/</span>
        <span style="color:var(--text-1);">#<?= $pr['id'] ?></span>
    </nav>

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
        <div>
            <h1 style="color:var(--text-1);font-size:20px;font-weight:600;margin-bottom:8px;"><?= e($pr['title']) ?></h1>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span style="font-size:12px;font-weight:600;padding:2px 10px;border-radius:20px;<?= $statusStyles[$pr['status']] ?>">
                    <?= e($pr['status']) ?>
                </span>
                <span style="font-size:13px;color:var(--text-2);">
                    <?= e($pr['author_name']) ?> quiere mergear
                    <code style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);padding:1px 6px;border-radius:4px;font-size:12px;"><?= e($pr['source_branch']) ?></code>
                    en
                    <code style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);padding:1px 6px;border-radius:4px;font-size:12px;"><?= e($pr['target_branch']) ?></code>
                </span>
                <span style="font-size:12px;color:var(--text-3);">· <?= dateFormat($pr['created_at']) ?></span>
            </div>
        </div>

        <?php if ($pr['status'] === 'open' && !empty($canManagePr)): ?>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/pulls/' . $pr['id'] . '/merge') ?>">
                    <button type="submit" onclick="return confirm('¿Confirmar merge?')"
                        style="background:#2d1f3d;border:1px solid #8957e5;color:#a371f7;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;cursor:pointer;">
                        Mergear PR
                    </button>
                </form>
                <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/pulls/' . $pr['id'] . '/close') ?>">
                    <button type="submit"
                        style="background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-size:13px;padding:7px 16px;border-radius:6px;cursor:pointer;">
                        Cerrar
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($pr['description']): ?>
        <div style="margin-top:12px;background:var(--bg-surface);border:1px solid var(--border);border-radius:6px;padding:14px;font-size:13px;color:var(--text-1);">
            <?= nl2br(e($pr['description'])) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Tabs -->
<div style="display:flex;gap:2px;border-bottom:1px solid var(--border);margin-bottom:16px;">
    <button onclick="showTab('diff')" id="tab-diff"
        style="padding:8px 16px;font-size:13px;background:none;border:none;border-bottom:2px solid var(--accent);color:var(--accent);font-weight:600;cursor:pointer;">
        Cambios (<?= count($diff) ?> archivos)
    </button>
    <button onclick="showTab('comments')" id="tab-comments"
        style="padding:8px 16px;font-size:13px;background:none;border:none;border-bottom:2px solid transparent;color:var(--text-2);cursor:pointer;">
        Comentarios (<?= count($comments) ?>)
    </button>
</div>

<!-- Panel diff -->
<div id="panel-diff">
    <?php if (empty($diff)): ?>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:48px;text-align:center;">
            <p style="color:var(--text-2);font-size:14px;">Sin diferencias entre las ramas.</p>
        </div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($diff as $index => $file):
                $statusStyle = match ($file['status'] ?? 'modified') {
                    'new'     => 'background:#1a3a1a;color:#3fb950;border:1px solid #2ea043;',
                    'deleted' => 'background:var(--red-bg);color:var(--red);border:1px solid var(--red);',
                    default   => 'background:#1f2b4f;color:#58a6ff;border:1px solid #1f6feb;',
                };
                $statusLabel = match ($file['status'] ?? 'modified') {
                    'new'     => 'nuevo',
                    'deleted' => 'eliminado',
                    default   => 'modificado',
                };
            ?>
                <details style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;" <?= $index === 0 ? 'open' : '' ?>>
                    <summary style="list-style:none;cursor:pointer;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg-hover);"
                            onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='var(--bg-hover)'">
                            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                                <span style="font-family:monospace;font-size:13px;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($file['file']) ?></span>
                                <span style="font-size:11px;padding:1px 8px;border-radius:20px;flex-shrink:0;<?= $statusStyle ?>"><?= $statusLabel ?></span>
                                <?php if (!empty($file['is_empty'])): ?>
                                    <span style="font-size:11px;padding:1px 8px;border-radius:20px;background:var(--bg-hover);color:var(--text-2);border:1px solid var(--border-2);">archivo vacío</span>
                                <?php endif; ?>
                            </div>
                            <?php if (empty($file['is_empty'])): ?>
                                <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                                    <span style="font-size:12px;color:#3fb950;font-weight:500;">+<?= (int)($file['additions'] ?? 0) ?></span>
                                    <span style="font-size:12px;color:#f85149;font-weight:500;">-<?= (int)($file['deletions'] ?? 0) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </summary>

                    <?php if (!empty($file['is_empty'])): ?>
                        <div style="padding:16px;font-size:13px;color:var(--text-2);">Este archivo se crea vacío en este Pull Request.</div>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-family:monospace;font-size:12px;">
                                <?php foreach ($file['lines'] as $line): ?>
                                    <?php if ($line['type'] === 'hunk'): ?>
                                        <?php // oculto 
                                        ?>
                                    <?php elseif ($line['type'] === 'add'): ?>
                                        <tr style="background:#1a3a1a;">
                                            <td style="width:40px;text-align:right;padding:2px 10px;color:#3fb950;user-select:none;border-right:1px solid var(--border);"><?= $line['line_no'] ?></td>
                                            <td style="padding:2px 14px;color:#aff5b4;white-space:pre;"><?= htmlspecialchars($line['content']) ?></td>
                                        </tr>
                                    <?php elseif ($line['type'] === 'remove'): ?>
                                        <tr style="background:var(--red-bg);">
                                            <td style="width:40px;text-align:right;padding:2px 10px;color:var(--red);user-select:none;border-right:1px solid var(--border);">&minus;</td>
                                            <td style="padding:2px 14px;color:#ffa198;white-space:pre;"><?= htmlspecialchars($line['content']) ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                                            <td style="width:40px;text-align:right;padding:2px 10px;color:var(--text-3);user-select:none;border-right:1px solid var(--border);"><?= $line['line_no'] ?></td>
                                            <td style="padding:2px 14px;color:var(--text-1);white-space:pre;"><?= htmlspecialchars($line['content']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endif; ?>
                </details>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Panel comentarios -->
<div id="panel-comments" style="display:none;">
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
        <?php if (empty($comments)): ?>
            <p style="color:var(--text-2);font-size:13px;">Sin comentarios todavía.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
                <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:14px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <span style="font-weight:500;font-size:13px;color:var(--text-1);"><?= e($c['author_name']) ?></span>
                        <span style="font-size:12px;color:var(--text-3);"><?= dateFormat($c['created_at']) ?></span>
                    </div>
                    <p style="font-size:13px;color:var(--text-1);"><?= nl2br(e($c['body'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($pr['status'] === 'open'): ?>
        <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/pulls/' . $pr['id'] . '/comment') ?>"
            style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:16px;">
            <label style="display:block;font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:8px;">Agregar comentario</label>
            <textarea name="body" rows="3" required
                style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:8px 12px;font-size:13px;outline:none;resize:vertical;"
                placeholder="Escribí tu comentario..."
                onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'"></textarea>
            <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                <button type="submit"
                    style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;cursor:pointer;">
                    Comentar
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    function showTab(tab) {
        const diff = document.getElementById('panel-diff');
        const comments = document.getElementById('panel-comments');
        const tabDiff = document.getElementById('tab-diff');
        const tabComments = document.getElementById('tab-comments');
        diff.style.display = tab === 'diff' ? 'block' : 'none';
        comments.style.display = tab === 'comments' ? 'block' : 'none';
        tabDiff.style.borderBottomColor = tab === 'diff' ? 'var(--accent)' : 'transparent';
        tabDiff.style.color = tab === 'diff' ? 'var(--accent)' : 'var(--text-2)';
        tabDiff.style.fontWeight = tab === 'diff' ? '600' : '400';
        tabComments.style.borderBottomColor = tab === 'comments' ? 'var(--accent)' : 'transparent';
        tabComments.style.color = tab === 'comments' ? 'var(--accent)' : 'var(--text-2)';
        tabComments.style.fontWeight = tab === 'comments' ? '600' : '400';
    }
</script>
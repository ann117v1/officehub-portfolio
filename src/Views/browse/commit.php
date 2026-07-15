<div style="margin-bottom:20px;">
    <nav style="font-size:13px;color:var(--text-2);margin-bottom:12px;">
        <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($repo['name']) ?></a>
        <span style="margin:0 6px;">/</span>
        <a href="<?= base('repos/' . e($repo['name']) . '/commits') ?>?branch=<?= urlencode($repo['default_branch']) ?>"
            style="color:var(--text-2);text-decoration:none;">Commits</a>
        <span style="margin:0 6px;">/</span>
        <span style="color:var(--text-1);font-family:monospace;"><?= e(shortHash($commit['hash'])) ?></span>
    </nav>

    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;">
        <h1 style="color:var(--text-1);font-size:16px;font-weight:500;margin-bottom:12px;"><?= e($commit['message']) ?></h1>
        <div style="display:flex;flex-wrap:wrap;gap:16px;">
            <span style="font-size:13px;color:var(--text-2);"><?= e($commit['author']) ?></span>
            <span style="font-size:13px;color:var(--text-2);"><?= e($commit['email']) ?></span>
            <span style="font-size:13px;color:var(--text-2);"><?= dateFormat($commit['date']) ?></span>
            <code style="font-size:12px;background:var(--bg-hover);color:var(--text-2);padding:2px 10px;border-radius:6px;border:1px solid var(--border-2);">
                <?= e($commit['hash']) ?>
            </code>
        </div>
    </div>
</div>

<?php if (empty($parsed)): ?>
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:32px;text-align:center;">
        <p style="color:var(--text-2);font-size:14px;">Este commit no tiene cambios de archivos registrados.</p>
    </div>
<?php else: ?>
    <?php foreach ($parsed as $file): ?>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;margin-bottom:12px;overflow:hidden;">
            <div style="padding:8px 14px;border-bottom:1px solid var(--border);background:var(--bg-hover);">
                <span style="font-family:monospace;font-size:13px;color:var(--text-1);"><?= e($file['file']) ?></span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-family:monospace;font-size:12px;">
                    <?php foreach ($file['lines'] as $line): ?>
                        <?php if ($line['type'] === 'hunk'): ?>
                            <?php // oculto 
                            ?>
                        <?php elseif ($line['type'] === 'add'): ?>
                            <tr style="background:var(--green-bg);">
                                <td style="width:40px;text-align:right;padding:2px 10px;color:var(--green);user-select:none;border-right:1px solid var(--border);"><?= $line['line_no'] ?></td>
                                <td style="padding:2px 14px;color:var(--green);white-space:pre;"><?= htmlspecialchars($line['content']) ?></td>
                            </tr>
                        <?php elseif ($line['type'] === 'remove'): ?>
                            <tr style="background:var(--red-bg);">
                                <td style="width:40px;text-align:right;padding:2px 10px;color:var(--red);user-select:none;border-right:1px solid var(--border);">&minus;</td>
                                <td style="padding:2px 14px;color:var(--red);white-space:pre;"><?= htmlspecialchars($line['content']) ?></td>
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
        </div>
    <?php endforeach; ?>
<?php endif; ?>
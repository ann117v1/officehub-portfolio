<nav style="font-size:13px;color:var(--text-2);margin-bottom:16px;">
    <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($repo['name']) ?></a>
    <span style="margin:0 6px;">/</span>
    <?php
    $parts = explode('/', $filePath);
    $accum = '';
    foreach ($parts as $i => $part):
        $accum .= ($i ? '/' : '') . $part;
        $isLast = ($i === count($parts) - 1);
    ?>
        <?php if (!$isLast): ?>
            <a href="<?= base('repos/' . e($repo['name']) . '/tree') ?>?branch=<?= urlencode($branch) ?>&path=<?= urlencode($accum) ?>"
                style="color:var(--text-2);text-decoration:none;"><?= e($part) ?></a>
            <span style="margin:0 6px;">/</span>
        <?php else: ?>
            <span style="color:var(--text-1);font-weight:500;"><?= e($part) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

<div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 14px;border-bottom:1px solid var(--border);background:var(--bg-hover);">
        <span style="font-family:monospace;font-size:13px;color:var(--text-1);"><?= e(basename($filePath)) ?></span>
        <div style="display:flex;align-items:center;gap:16px;">
            <span style="font-size:12px;color:var(--text-3);">rama: <?= e($branch) ?></span>
            <a href="<?= base('repos/' . e($repo['name']) . '/tree') ?>?branch=<?= urlencode($branch) ?>&path=<?= urlencode(dirname($filePath)) ?>"
                style="font-size:12px;color:var(--accent);text-decoration:none;">← Volver a la carpeta</a>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-family:monospace;font-size:12px;">
            <?php
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line):
            ?>
                <tr onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                    <td style="width:48px;text-align:right;padding:2px 12px;color:var(--text-3);user-select:none;border-right:1px solid var(--border);">
                        <?= $i + 1 ?>
                    </td>
                    <td style="padding:2px 16px;color:var(--text-1);white-space:pre;"><?= htmlspecialchars($line) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
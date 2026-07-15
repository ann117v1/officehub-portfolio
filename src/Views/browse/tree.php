<nav style="font-size:13px;color:var(--text-2);margin-bottom:16px;">
    <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"
        onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'"><?= e($repo['name']) ?></a>
    <?php if ($path):
        $parts = explode('/', $path);
        $accum = '';
        foreach ($parts as $i => $part):
            $accum .= ($i ? '/' : '') . $part;
            $isLast = ($i === count($parts) - 1);
    ?>
            <span style="margin:0 6px;">/</span>
            <?php if (!$isLast): ?>
                <a href="<?= base('repos/' . e($repo['name']) . '/tree') ?>?branch=<?= urlencode($branch) ?>&path=<?= urlencode($accum) ?>"
                    style="color:var(--text-2);text-decoration:none;"><?= e($part) ?></a>
            <?php else: ?>
                <span style="color:var(--text-1);font-weight:500;"><?= e($part) ?></span>
            <?php endif; ?>
    <?php endforeach;
    endif; ?>
</nav>

<div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
    <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--border);">
        <select onchange="window.location='<?= base('repos/' . e($repo['name']) . '/tree') ?>?branch='+encodeURIComponent(this.value)+'<?= $path ? '&path=' . urlencode($path) : '' ?>'"
            style="background:var(--bg-base);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:5px 10px;font-size:13px;outline:none;cursor:pointer;">
            <?php foreach ($branches as $b): ?>
                <option value="<?= e($b) ?>" <?= $b === $branch ? 'selected' : '' ?>><?= e($b) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($path): ?>
            <a href="<?= base('repos/' . e($repo['name']) . '/tree') ?>?branch=<?= urlencode($branch) ?>"
                style="font-size:13px;color:var(--text-2);text-decoration:none;"
                onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'">← Raíz</a>
        <?php endif; ?>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <?php if ($path):
            $parentPath = dirname($path);
            $parentUrl  = $parentPath === '.'
                ? base('repos/' . e($repo['name']) . '/tree') . '?branch=' . urlencode($branch)
                : base('repos/' . e($repo['name']) . '/tree') . '?branch=' . urlencode($branch) . '&path=' . urlencode($parentPath);
        ?>
            <tr style="border-bottom:1px solid var(--border);"
                onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                <td style="padding:8px 14px;">
                    <a href="<?= $parentUrl ?>" style="color:var(--text-2);text-decoration:none;font-size:13px;">.. (carpeta anterior)</a>
                </td>
            </tr>
        <?php endif; ?>

        <?php foreach ($tree as $item): ?>
            <tr style="border-bottom:1px solid var(--border);"
                onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                <td style="padding:8px 14px;">
                    <?php if ($item['type'] === 'tree'): ?>
                        <a href="<?= base('repos/' . e($repo['name']) . '/tree') ?>?branch=<?= urlencode($branch) ?>&path=<?= urlencode($item['path']) ?>"
                            style="color:var(--accent);text-decoration:none;font-size:13px;display:flex;align-items:center;gap:8px;">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="#54aeff">
                                <path d="M1.75 1A1.75 1.75 0 0 0 0 2.75v10.5C0 14.216.784 15 1.75 15h12.5A1.75 1.75 0 0 0 16 13.25v-8.5A1.75 1.75 0 0 0 14.25 3H7.5a.25.25 0 0 1-.2-.1l-.9-1.2C6.07 1.26 5.55 1 5 1H1.75Z" />
                            </svg>
                            <?= e($item['name']) ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= base('repos/' . e($repo['name']) . '/blob') ?>?branch=<?= urlencode($branch) ?>&path=<?= urlencode($item['path']) ?>"
                            style="color:var(--text-1);text-decoration:none;font-size:13px;display:flex;align-items:center;gap:8px;">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="color:var(--text-2)">
                                <path d="M2 1.75C2 .784 2.784 0 3.75 0h6.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v9.586A1.75 1.75 0 0 1 13.25 16h-9.5A1.75 1.75 0 0 1 2 14.25Zm1.75-.25a.25.25 0 0 0-.25.25v12.5c0 .138.112.25.25.25h9.5a.25.25 0 0 0 .25-.25V6h-2.75A1.75 1.75 0 0 1 9 4.25V1.5Zm6.75.062V4.25c0 .138.112.25.25.25h2.688Z" />
                            </svg>
                            <?= e($item['name']) ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
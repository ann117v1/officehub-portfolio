<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;">
    <div>
        <nav style="font-size:13px;color:var(--text-2);margin-bottom:4px;">
            <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($repo['name']) ?></a>
            <span style="margin:0 6px;">/</span>
            <span style="color:var(--text-1);font-weight:500;">Commits</span>
        </nav>
        <h1 style="color:var(--text-1);font-size:18px;font-weight:600;">
            Historial de commits
            <span style="font-size:13px;font-weight:400;color:var(--text-2);margin-left:8px;">
                rama: <code style="background:var(--bg-hover);color:var(--text-1);padding:2px 8px;border-radius:4px;font-size:12px;border:1px solid var(--border-2);"><?= e($branch) ?></code>
            </span>
        </h1>
    </div>
    <a href="<?= base('repos/' . e($repo['name'])) ?>"
        style="background:transparent;border:1px solid var(--border-2);color:var(--text-1);font-size:13px;padding:6px 14px;border-radius:6px;text-decoration:none;"
        onmouseover="this.style.borderColor='var(--text-2)'" onmouseout="this.style.borderColor='var(--border-2)'">
        ← Volver al repo
    </a>
</div>

<?php if (empty($commits)): ?>
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:48px;text-align:center;">
        <p style="color:var(--text-2);font-size:14px;">No hay commits en esta rama todavía.</p>
    </div>
<?php else: ?>
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
        <?php foreach ($commits as $commit): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-bottom:1px solid var(--border);"
                onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                <div style="flex:1;min-width:0;">
                    <a href="<?= base('repos/' . e($repo['name']) . '/commit/' . e($commit['hash'])) ?>"
                        style="color:var(--text-1);text-decoration:none;font-size:13px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                        onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-1)'">
                        <?= e($commit['message']) ?>
                    </a>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
                        <span style="font-size:12px;color:var(--text-2);"><?= e($commit['author']) ?></span>
                        <span style="color:var(--text-3);font-size:12px;">·</span>
                        <span style="font-size:12px;color:var(--text-3);"><?= dateFormat($commit['date'], 'd/m/Y H:i') ?></span>
                    </div>
                </div>
                <a href="<?= base('repos/' . e($repo['name']) . '/commit/' . e($commit['hash'])) ?>"
                    style="font-family:monospace;font-size:12px;background:var(--bg-hover);color:var(--text-2);padding:3px 10px;border-radius:6px;text-decoration:none;flex-shrink:0;border:1px solid var(--border-2);"
                    onmouseover="this.style.color='var(--accent)';this.style.borderColor='var(--accent)'" onmouseout="this.style.color='var(--text-2)';this.style.borderColor='var(--border-2)'">
                    <?= e($commit['short_hash']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <p style="font-size:12px;color:var(--text-3);text-align:right;margin-top:8px;">
        Mostrando los últimos <?= count($commits) ?> commits
    </p>
<?php endif; ?>
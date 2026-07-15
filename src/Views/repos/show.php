<?php
/** @var array $repo */
/** @var bool $isEmpty */
/** @var array $branches */
/** @var string $branch */
/** @var array $commits */
/** @var array $tree */
/** @var bool $canAdmin */

$config = require BASE_PATH . '/config/app.php';

$repoHttpUrl = rtrim($config['url'], '/') . '/repos/' . $repo['name'] . '.git';
$cloneCommand = 'git clone ' . $repoHttpUrl;
?>


<!-- Header del repo -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;">
    <div>
        <nav style="font-size:13px;color:var(--text-2);margin-bottom:4px;">
            <a href="<?= base('repos') ?>" style="color:var(--text-2);text-decoration:none;"
                onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'">Repositorios</a>
            <span style="margin:0 6px;">/</span>
            <span style="color:var(--text-1);font-weight:500;"><?= e($repo['name']) ?></span>
        </nav>

        <?php if ($repo['description']): ?>
            <p style="color:var(--text-2);font-size:13px;"><?= e($repo['description']) ?></p>
        <?php endif; ?>

        <?php if ($repo['website_url'] ?? null): ?>
            <?php $domain = parse_url($repo['website_url'], PHP_URL_HOST); ?>
            <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
                <img src="https://www.google.com/s2/favicons?domain=<?= urlencode($domain) ?>&sz=16"
                    width="14" height="14" style="border-radius:2px;flex-shrink:0;"
                    onerror="this.style.display='none'">
                <a href="<?= e($repo['website_url']) ?>" target="_blank" rel="noopener noreferrer"
                    style="font-size:13px;color:var(--accent);text-decoration:none;"
                    onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                    <?= e($domain) ?>
                </a>
                <svg viewBox="0 0 16 16" width="11" height="11" fill="var(--text-3)" style="flex-shrink:0;">
                    <path d="M3.75 2h3.5a.75.75 0 0 1 0 1.5h-3.5a.25.25 0 0 0-.25.25v8.5c0 .138.112.25.25.25h8.5a.25.25 0 0 0 .25-.25v-3.5a.75.75 0 0 1 1.5 0v3.5A1.75 1.75 0 0 1 12.25 14h-8.5A1.75 1.75 0 0 1 2 12.25v-8.5C2 2.784 2.784 2 3.75 2Zm6.854-1h4.146a.25.25 0 0 1 .25.25v4.146a.25.25 0 0 1-.427.177L13.03 4.03 9.28 7.78a.751.751 0 0 1-1.042-.018.751.751 0 0 1-.018-1.042l3.75-3.75-1.543-1.543A.25.25 0 0 1 10.604 1Z" />
                </svg>
            </div>
        <?php endif; ?>
    </div>

    <div style="display:flex;gap:8px;">
        <a href="<?= base('repos/' . e($repo['name']) . '/pulls') ?>"
            style="background:transparent;border:1px solid var(--border-2);color:var(--text-1);font-size:13px;padding:6px 14px;border-radius:6px;text-decoration:none;"
            onmouseover="this.style.borderColor='var(--text-2)'" onmouseout="this.style.borderColor='var(--border-2)'">
            Pull Requests
        </a>

        <?php if ($canAdmin): ?>
            <a href="<?= base('repos/' . e($repo['name']) . '/settings') ?>"
                style="background:transparent;border:1px solid var(--border-2);color:var(--text-1);font-size:13px;padding:6px 14px;border-radius:6px;text-decoration:none;"
                onmouseover="this.style.borderColor='var(--text-2)'" onmouseout="this.style.borderColor='var(--border-2)'">
                Configuración
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($isEmpty): ?>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:32px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <svg viewBox="0 0 16 16" width="18" height="18" fill="var(--text-3)">
                    <path d="M2 2.5A2.5 2.5 0 0 1 4.5 0h8.75a.75.75 0 0 1 .75.75v12.5a.75.75 0 0 1-.75.75h-2.5a.75.75 0 0 1 0-1.5h1.75v-2h-8a1 1 0 0 0-.714 1.7.75.75 0 1 1-1.072 1.05A2.495 2.495 0 0 1 2 11.5Zm10.5-1h-8a1 1 0 0 0-1 1v6.708A2.486 2.486 0 0 1 4.5 9h8Z" />
                </svg>
                <h2 style="color:var(--text-1);font-size:16px;font-weight:500;">Este repositorio está vacío</h2>
            </div>

            <p style="color:var(--text-2);font-size:13px;margin-bottom:20px;line-height:1.6;">
                Todavía no hay código aquí. Seguí los pasos de la derecha para subir tu proyecto.
            </p>

            <div style="background:var(--bg-base);border:1px solid var(--border);border-radius:6px;overflow:hidden;">
                <div style="padding:8px 12px;border-bottom:1px solid var(--border);background:var(--bg-hover);">
                    <span style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:0.05em;">URL del repositorio</span>
                </div>

                <div style="padding:10px 12px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <code id="repo-url" style="font-size:12px;color:var(--green);word-break:break-all;">
                        <?= e($repoHttpUrl) ?>
                    </code>

                    <button onclick="copyUrl()" title="Copiar URL"
                        style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-2);border-radius:4px;padding:4px 10px;font-size:11px;cursor:pointer;flex-shrink:0;"
                        onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'">
                        <span id="copy-label">Copiar</span>
                    </button>
                </div>
            </div>
        </div>

        <div style="position:sticky;top:72px;">
            <?php
            $guideId = 'empty-repo';
            require __DIR__ . '/partials/push_guide.php';
            ?>
        </div>

    </div>

<?php else: ?>

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:24px;align-items:start;">

        <div>

            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;margin-bottom:16px;overflow:hidden;">
                <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--border);">
                    <select onchange="window.location='<?= base('repos/' . e($repo['name'])) ?>?branch='+encodeURIComponent(this.value)"
                        style="background:var(--bg-base);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:5px 10px;font-size:13px;outline:none;cursor:pointer;">
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= e($b) ?>" <?= $b === $branch ? 'selected' : '' ?>><?= e($b) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <a href="<?= base('repos/' . e($repo['name']) . '/commits') ?>?branch=<?= urlencode($branch) ?>"
                        style="font-size:13px;color:var(--text-2);text-decoration:none;"
                        onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-2)'">
                        <?= count($commits) ?>+ commits
                    </a>

                    <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                        <code id="clone-url" style="background:var(--bg-base);border:1px solid var(--border);color:var(--text-2);font-size:12px;padding:4px 12px;border-radius:6px;">
                            <?= e($cloneCommand) ?>
                        </code>

                        <button onclick="copyClone(this)" title="Copiar comando"
                            style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-2);border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer;display:flex;align-items:center;gap:5px;flex-shrink:0;"
                            onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'">
                            <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor">
                                <path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 0 1 0 1.5h-1.5a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-1.5a.75.75 0 0 1 1.5 0v1.5A1.75 1.75 0 0 1 9.25 16h-7.5A1.75 1.75 0 0 1 0 14.25Z" />
                                <path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0 1 14.25 11h-7.5A1.75 1.75 0 0 1 5 9.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z" />
                            </svg>
                            <span id="clone-label">Copiar</span>
                        </button>
                    </div>
                </div>

                <table style="width:100%;border-collapse:collapse;">
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

            <div>
                <p style="font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">Commits recientes</p>
                <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                    <?php foreach ($commits as $commit): ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--border);"
                            onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                            <a href="<?= base('repos/' . e($repo['name']) . '/commit/' . e($commit['hash'])) ?>"
                                style="font-family:monospace;font-size:12px;color:var(--accent);text-decoration:none;flex-shrink:0;width:60px;">
                                <?= e($commit['short_hash']) ?>
                            </a>

                            <span style="flex:1;font-size:13px;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= e($commit['message']) ?>
                            </span>

                            <span style="font-size:12px;color:var(--text-3);flex-shrink:0;">
                                <?= e($commit['author']) ?> · <?= dateFormat($commit['date'], 'd/m/Y') ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <div style="position:sticky;top:72px;">
            <?php
            $guideId = 'repo-changes';
            require __DIR__ . '/partials/push_guide_changes.php';
            ?>
        </div>

    </div>

<?php endif; ?>

<script>
function copyText(text, labelId = null, btn = null) {
    const done = () => {
        if (labelId) {
            const label = document.getElementById(labelId);
            if (label) {
                label.textContent = '✓ Copiado';
                setTimeout(() => label.textContent = 'Copiar', 2000);
            }
        }

        if (btn) {
            const icon = btn.querySelector('svg');

            if (icon) {
                icon.style.fill = 'var(--green)';
            }

            btn.style.borderColor = 'var(--green)';
            btn.style.color = 'var(--green)';

            setTimeout(() => {
                if (icon) {
                    icon.style.fill = 'currentColor';
                }

                btn.style.borderColor = 'var(--border-2)';
                btn.style.color = 'var(--text-2)';
            }, 2000);
        }
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done).catch(() => fallbackCopy(text, done));
    } else {
        fallbackCopy(text, done);
    }
}

function fallbackCopy(text, callback) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';

    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    document.execCommand('copy');
    document.body.removeChild(textarea);

    callback();
}

function copyUrl() {
    const url = document.getElementById('repo-url').textContent.trim();
    copyText(url, 'copy-label');
}

function copyClone(btn) {
    const text = document.getElementById('clone-url').textContent.trim();
    copyText(text, 'clone-label', btn);
}
</script>

<?php
$config = require BASE_PATH . '/config/app.php';

$guideId = $guideId ?? 'push-guide';
$guideIdJs = preg_replace('/[^a-zA-Z0-9_]/', '_', $guideId);
$repoName = $repo['name'] ?? '[nombre]';

$remoteUrl = (isset($repo['name']) && $repo['name'])
    ? rtrim($config['url'], '/') . '/repos/' . $repoName . '.git'
    : rtrim($config['url'], '/') . '/repos/[nombre].git';

$remotePlaceholder = isset($repo['name']) && $repo['name'];
?>

<div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
    <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 16 16" width="15" height="15" fill="var(--accent)">
            <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm6.75-3.25v2.5h2.5a.75.75 0 0 1 0 1.5h-2.5v2.5a.75.75 0 0 1-1.5 0v-2.5h-2.5a.75.75 0 0 1 0-1.5h2.5v-2.5a.75.75 0 0 1 1.5 0Z" />
        </svg>
        <span style="font-size:13px;font-weight:500;color:var(--text-1);">¿Cómo subir tu proyecto?</span>
    </div>

    <div style="display:flex;border-bottom:1px solid var(--border);">
        <button type="button" onclick="showGuide<?= $guideIdJs ?>('nuevo')" id="tab-nuevo-<?= $guideIdJs ?>"
            style="flex:1;padding:8px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;background:none;border:none;border-bottom:2px solid var(--accent);color:var(--accent);cursor:pointer;">
            Proyecto nuevo
        </button>
        <button type="button" onclick="showGuide<?= $guideIdJs ?>('existente')" id="tab-existente-<?= $guideIdJs ?>"
            style="flex:1;padding:8px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;background:none;border:none;border-bottom:2px solid transparent;color:var(--text-2);cursor:pointer;">
            Ya tengo Git
        </button>
    </div>

    <div id="guide-nuevo-<?= $guideIdJs ?>" style="padding:16px;">
        <?php
        $steps_nuevo = [
            [
                'n' => '1',
                'title' => 'Abrí una terminal',
                'cmd' => null,
                'desc' => 'CMD o PowerShell en la carpeta de tu proyecto'
            ],
            ['n' => '2', 'title' => 'Inicializá Git dentro de la ruta de tu proyecto', 'cmd' => 'git init'],
            ['n' => '3', 'title' => 'Cambia el nombre de la rama por defecto', 'cmd' => 'git branch -m master main'],
            ['n' => '4', 'title' => 'Agregá todos los archivos', 'cmd' => 'git add .'],
            ['n' => '5', 'title' => 'Hacé el primer commit', 'cmd' => 'git commit -m "subida inicial proyecto"'],
            ['n' => '6', 'title' => 'Conectá con OfficeHub', 'cmd' => 'git remote add origin ' . $remoteUrl],
            ['n' => '7', 'title' => 'Subí el código', 'cmd' => 'git push -u origin main'],
        ];
        foreach ($steps_nuevo as $step): ?>
            <div style="display:flex;gap:10px;margin-bottom:14px;">
                <div style="width:22px;height:22px;border-radius:50%;background:var(--accent-bg);color:#fff;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                    <?= $step['n'] ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:3px;"><?= $step['title'] ?></p>
                    <?php if ($step['cmd'] ?? null): ?>
                        <code style="display:block;background:var(--bg-base);border:1px solid var(--border);border-radius:4px;padding:5px 8px;font-size:11px;color:var(--green);word-break:break-all;">
                            <?= e($step['cmd']) ?>
                        </code>
                    <?php endif; ?>
                    <?php if ($step['desc'] ?? null): ?>
                        <p style="font-size:11px;color:var(--text-3);margin-top:2px;"><?= $step['desc'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="guide-existente-<?= $guideIdJs ?>" style="padding:16px;display:none;">
        <?php
        $steps_existente = [
            [
                'n' => '1',
                'title' => 'Verificá tu rama actual',
                'cmd' => 'git branch',
                'desc' => 'Asegurate de estar en main o master'
            ],
            ['n' => '2', 'title' => 'Agregá OfficeHub como remote', 'cmd' => 'git remote add officehub ' . $remoteUrl],
            [
                'n' => '3',
                'title' => 'Subí el código',
                'cmd' => 'git push officehub main',
                'desc' => 'Te va a pedir usuario y contraseña de OfficeHub'
            ],
            [
                'n' => '4',
                'title' => 'Para actualizarlo en el futuro',
                'cmd' => 'git push officehub main',
                'desc' => 'Cada vez que quieras subir cambios nuevos'
            ],
        ];
        foreach ($steps_existente as $step): ?>
            <div style="display:flex;gap:10px;margin-bottom:14px;">
                <div style="width:22px;height:22px;border-radius:50%;background:var(--purple-bg);color:var(--purple);font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;border:1px solid var(--purple);">
                    <?= $step['n'] ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:3px;"><?= $step['title'] ?></p>
                    <?php if ($step['cmd'] ?? null): ?>
                        <code style="display:block;background:var(--bg-base);border:1px solid var(--border);border-radius:4px;padding:5px 8px;font-size:11px;color:var(--green);word-break:break-all;">
                            <?= e($step['cmd']) ?>
                        </code>
                    <?php endif; ?>
                    <?php if ($step['desc'] ?? null): ?>
                        <p style="font-size:11px;color:var(--text-3);margin-top:2px;"><?= $step['desc'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="padding:12px 16px;border-top:1px solid var(--border);background:var(--bg-hover);">
        <p style="font-size:11px;color:var(--text-3);line-height:1.5;">
            <?php if ($remotePlaceholder): ?>
                La URL remota ya quedó armada con el nombre exacto de este repo.
            <?php else: ?>
                Reemplazá <code style="color:var(--accent);font-size:11px;">[nombre]</code> por el nombre exacto del repo que creaste.
            <?php endif; ?>
        </p>
    </div>
</div>

<script>
    function showGuide<?= $guideIdJs ?>(tab) {
        document.getElementById('guide-nuevo-<?= $guideIdJs ?>').style.display = tab === 'nuevo' ? 'block' : 'none';
        document.getElementById('guide-existente-<?= $guideIdJs ?>').style.display = tab === 'existente' ? 'block' : 'none';
        document.getElementById('tab-nuevo-<?= $guideIdJs ?>').style.borderBottomColor = tab === 'nuevo' ? 'var(--accent)' : 'transparent';
        document.getElementById('tab-nuevo-<?= $guideIdJs ?>').style.color = tab === 'nuevo' ? 'var(--accent)' : 'var(--text-2)';
        document.getElementById('tab-existente-<?= $guideIdJs ?>').style.borderBottomColor = tab === 'existente' ? 'var(--accent)' : 'transparent';
        document.getElementById('tab-existente-<?= $guideIdJs ?>').style.color = tab === 'existente' ? 'var(--accent)' : 'var(--text-2)';
    }
</script>

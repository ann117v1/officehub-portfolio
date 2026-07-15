<?php
$config = require BASE_PATH . '/config/app.php';

$guideId   = $guideId ?? 'push-changes';
$guideIdJs = preg_replace('/[^a-zA-Z0-9_]/', '_', $guideId);
$repoName  = $repo['name'] ?? '[nombre]';
$remoteUrl = rtrim($config['url'], '/') . '/repos/' . $repoName . '.git';
?>


<div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">

    <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 16 16" width="15" height="15" fill="var(--green)">
            <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0ZM1.5 8a6.5 6.5 0 1 0 13 0 6.5 6.5 0 0 0-13 0Zm4.879-2.773 4.264 2.559a.25.25 0 0 1 0 .428l-4.264 2.559A.25.25 0 0 1 6 10.559V5.442a.25.25 0 0 1 .379-.215Z" />
        </svg>
        <span style="font-size:13px;font-weight:500;color:var(--text-1);">¿Cómo subir nuevos cambios?</span>
    </div>

    <div style="padding:16px;">
        <?php foreach (
            [
                [
                    'n'     => '1',
                    'title' => 'Bajate los últimos cambios primero',
                    'cmd'   => 'git pull origin main',
                    'desc'  => 'Siempre hacé esto antes de empezar a trabajar para no tener conflictos',
                ],
                [
                    'n'     => '2',
                    'title' => 'Hacé tus cambios en el código',
                    'cmd'   => null,
                    'desc'  => 'Editá, agregá o eliminá los archivos que necesites',
                ],
                [
                    'n'     => '3',
                    'title' => 'Verificá qué cambió',
                    'cmd'   => 'git status',
                    'desc'  => 'Muestra los archivos modificados en rojo',
                ],
                [
                    'n'     => '4',
                    'title' => 'Agregá los cambios',
                    'cmd'   => 'git add .',
                    'desc'  => 'El punto agrega todos los archivos modificados',
                ],
                [
                    'n'     => '5',
                    'title' => 'Guardá con un mensaje descriptivo',
                    'cmd'   => 'git commit -m "descripcion del cambio"',
                    'desc'  => 'Ejemplo: "corrijo validacion del formulario de login"',
                ],
                [
                    'n'     => '6',
                    'title' => 'Subí los cambios a OfficeHub',
                    'cmd'   => 'git push origin main',
                    'desc'  => null,
                ],
            ] as $step
        ): ?>
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

    <!-- Nota al pie -->
    <div style="padding:12px 16px;border-top:1px solid var(--border);background:var(--bg-hover);">
        <p style="font-size:11px;color:var(--text-3);line-height:1.5;">
            Para clonar el repo por primera vez:
            <code style="color:var(--accent);font-size:11px;">git clone <?= e($remoteUrl) ?></code>
        </p>
    </div>
</div>


<?php
/** @var array $repo */
/** @var array $permissions */
/** @var array $users */
/** @var array $areas */
/** @var array $collaborators */
?>


<div style="max-width:600px;">
    <nav style="font-size:13px;color:var(--text-2);margin-bottom:8px;">
        <a href="<?= base('repos') ?>" style="color:var(--text-2);text-decoration:none;">Repositorios</a>
        <span style="margin:0 6px;">/</span>
        <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($repo['name']) ?></a>
        <span style="margin:0 6px;">/</span>
        <span style="color:var(--text-1);">Configuración</span>
    </nav>
    <h1 style="color:var(--text-1);font-size:20px;font-weight:600;margin-bottom:20px;">Configuración del repositorio</h1>

    <!-- Info general -->
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:12px;">
        <h2 style="color:var(--text-1);font-size:14px;font-weight:500;margin-bottom:14px;">Información general</h2>
        <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
            <div style="display:flex;gap:8px;">
                <span style="color:var(--text-2);width:120px;flex-shrink:0;">Nombre</span>
                <span style="font-family:monospace;color:var(--text-1);"><?= e($repo['name']) ?></span>
            </div>
            <div style="display:flex;gap:8px;">
                <span style="color:var(--text-2);width:120px;flex-shrink:0;">Visibilidad</span>
                <span style="color:var(--text-1);"><?= e($repo['visibility']) ?></span>
            </div>
            <div style="display:flex;gap:8px;">
                <span style="color:var(--text-2);width:120px;flex-shrink:0;">Rama por defecto</span>
                <code style="font-family:monospace;color:var(--text-1);"><?= e($repo['default_branch']) ?></code>
            </div>
            <div style="display:flex;gap:8px;">
                <span style="color:var(--text-2);width:120px;flex-shrink:0;">Ruta en disco</span>
                <code style="font-family:monospace;font-size:12px;color:var(--text-3);"><?= e($repo['path_on_disk']) ?></code>
            </div>
            <div style="margin-top:14px;">
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <span style="color:var(--text-2);font-size:13px;">Descripción</span>

        <?php if (currentUser()['role'] !== 'viewer'): ?>
            <button onclick="toggleEditDesc()"
                style="background:none;border:none;color:var(--text-2);cursor:pointer;">
                ✏️
            </button>
        <?php endif; ?>
    </div>

    <!-- Vista -->
    <p id="desc-text" style="margin-top:6px;color:var(--text-1);font-size:13px;">
        <?= $repo['description'] ? e($repo['description']) : 'Sin descripción' ?>
    </p>

    <!-- Edición -->
    <form id="desc-form"
        action="<?= base('repos/' . e($repo['name']) . '/update-description') ?>"
        method="POST"
        style="display:none;margin-top:8px;">

        <input type="text" name="description"
            value="<?= e($repo['description'] ?? '') ?>"
            style="width:100%;padding:6px;border:1px solid var(--border-2);border-radius:6px;background:var(--bg-base);color:var(--text-1);font-size:13px;">

        <div style="margin-top:8px;display:flex;gap:8px;">
            <button type="submit"
                style="background:var(--accent);border:none;color:white;padding:6px 12px;border-radius:6px;cursor:pointer;">
                Guardar
            </button>

            <button type="button" onclick="toggleEditDesc()"
                style="background:none;border:1px solid var(--border-2);color:var(--text-2);padding:6px 12px;border-radius:6px;cursor:pointer;">
                Cancelar
            </button>
        </div>
    </form>
</div>
        </div>
    </div>
    

    <!-- URL de producción -->
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:12px;">
        <h2 style="color:var(--text-1);font-size:14px;font-weight:500;margin-bottom:6px;">URL de producción</h2>
        <p style="font-size:13px;color:var(--text-2);margin-bottom:14px;">
            La URL donde está desplegado este proyecto. Se muestra en la página del repositorio.
        </p>
        <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/website') ?>"
            style="display:flex;gap:8px;">
            <input type="url" name="website_url"
                value="<?= e($repo['website_url'] ?? '') ?>"
                placeholder="https://demo.example.test"
                style="flex:1;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 12px;font-size:13px;outline:none;"
                onfocus="this.style.borderColor='var(--accent-brd)'"
                onblur="this.style.borderColor='var(--border-2)'">
            <button type="submit"
                style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;cursor:pointer;">
                Guardar
            </button>
            <?php if ($repo['website_url'] ?? null): ?>
                <button type="submit" name="website_url" value=""
                    style="background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-size:13px;padding:7px 14px;border-radius:6px;cursor:pointer;"
                    onclick="this.form.website_url.value=''">
                    Quitar
                </button>
            <?php endif; ?>
        </form>
        <?php if ($repo['website_url'] ?? null): ?>
            <?php $domain = parse_url($repo['website_url'], PHP_URL_HOST); ?>
            <div style="display:flex;align-items:center;gap:6px;margin-top:10px;">
                <img src="https://www.google.com/s2/favicons?domain=<?= urlencode($domain) ?>&sz=16"
                    width="14" height="14" style="border-radius:2px;"
                    onerror="this.style.display='none'">
                <a href="<?= e($repo['website_url']) ?>" target="_blank"
                    style="font-size:13px;color:var(--accent);text-decoration:none;">
                    <?= e($repo['website_url']) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Mover a otra área (solo admin) -->
    <?php if (hasRole('admin')): ?>
        <?php $areas = \OfficeHub\Models\Area::all(); ?>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:12px;">
            <h2 style="color:var(--text-1);font-size:14px;font-weight:500;margin-bottom:6px;">Área del repositorio</h2>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:14px;">
                Mover este repositorio a otra área. Solo visible para administradores.
            </p>
            <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/move-area') ?>"
                style="display:flex;gap:8px;">
                <select name="area_id"
                    style="flex:1;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
                    <option value="">Sin área asignada</option>
                    <?php foreach ($areas as $area): ?>
                        <option value="<?= $area['id'] ?>"
                            <?= ($repo['area_id'] ?? null) == $area['id'] ? 'selected' : '' ?>>
                            <?= e($area['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit"
                    style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;cursor:pointer;">
                    Mover
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Colaboradores -->
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:12px;">
        <h2 style="color:var(--text-1);font-size:14px;font-weight:500;margin-bottom:14px;">Colaboradores</h2>
        <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/permissions/add') ?>"
            style="display:flex;gap:8px;margin-bottom:16px;">
            <input type="text" name="username" placeholder="Nombre de usuario" required
                style="flex:1;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;"
                onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
            <select name="permission"
                style="background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
                <option value="read">Read</option>
                <option value="write">Write</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit"
                style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;padding:7px 14px;border-radius:6px;cursor:pointer;">
                Agregar
            </button>
        </form>

        <?php if (empty($collaborators ?? [])): ?>
            <p style="font-size:13px;color:var(--text-2);">No hay colaboradores agregados todavía.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;">
                <?php foreach ($collaborators as $collab): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
                        <span style="font-size:13px;color:var(--text-1);"><?= e($collab['username']) ?></span>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:11px;background:var(--bg-hover);color:var(--text-2);border:1px solid var(--border-2);padding:2px 8px;border-radius:20px;"><?= e($collab['permission']) ?></span>
                            <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/permissions/remove') ?>">
                                <input type="hidden" name="user_id" value="<?= $collab['user_id'] ?>">
                                <button type="submit" style="background:none;border:none;color:var(--red);font-size:12px;cursor:pointer;">Quitar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <!-- Zona de peligro -->
    <div style="background:var(--bg-surface);border:1px solid var(--red);border-radius:8px;padding:20px;">
        <h2 style="color:var(--red);font-size:14px;font-weight:500;margin-bottom:8px;">Zona de peligro</h2>
        <p style="font-size:13px;color:var(--text-2);margin-bottom:14px;">
            Eliminar el repositorio borra permanentemente el código y toda la metadata. Esta acción no se puede deshacer.
        </p>
        <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/delete') ?>"
            onsubmit="return confirm('¿Estás seguro? Esta acción es irreversible.')">
            <button type="submit"
                style="background:var(--red-bg);border:1px solid var(--red);color:var(--red);font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;cursor:pointer;">
                Eliminar repositorio
            </button>
        </form>
    </div>
</div>

<script>
function toggleEditDesc() {
    const text = document.getElementById('desc-text');
    const form = document.getElementById('desc-form');

    if (form.style.display === 'none') {
        form.style.display = 'block';
        text.style.display = 'none';
    } else {
        form.style.display = 'none';
        text.style.display = 'block';
    }
}
</script>
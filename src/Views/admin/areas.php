<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="color:var(--text-1);font-size:20px;font-weight:600;">Gestión de áreas</h1>
    <a href="<?= base('admin/users') ?>"
        style="font-size:13px;color:var(--text-2);text-decoration:none;"
        onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'">
        ← Volver a usuarios
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    <!-- Lista de áreas -->
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13px;font-weight:500;color:var(--text-1);">Áreas configuradas</span>
            <span style="font-size:12px;color:var(--text-3);"><?= count($areas) ?> áreas</span>
        </div>

        <?php if (empty($areas)): ?>
            <div style="padding:32px;text-align:center;">
                <p style="font-size:13px;color:var(--text-2);">No hay áreas todavía.</p>
            </div>
        <?php else: ?>
            <?php foreach ($areas as $area): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);"
                    onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">

                    <span style="width:12px;height:12px;border-radius:50%;background:<?= e($area['color']) ?>;flex-shrink:0;"></span>

                    <div style="flex:1;min-width:0;">
                        <span style="font-size:13px;color:var(--text-1);font-weight:500;"><?= e($area['name']) ?></span>
                        <span style="font-size:12px;color:var(--text-3);margin-left:8px;"><?= $area['repo_count'] ?> repos</span>
                    </div>

                    <!-- Editar inline -->
                    <form method="POST" action="<?= base('admin/areas/' . $area['id'] . '/update') ?>"
                        style="display:flex;align-items:center;gap:6px;">
                        <input type="text" name="name" value="<?= e($area['name']) ?>"
                            style="background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:4px 8px;font-size:12px;outline:none;width:130px;"
                            onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
                        <input type="color" name="color" value="<?= e($area['color']) ?>"
                            style="width:28px;height:28px;border:1px solid var(--border-2);border-radius:6px;background:var(--bg-input);cursor:pointer;padding:2px;">
                        <button type="submit"
                            style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-2);font-size:12px;padding:4px 10px;border-radius:6px;cursor:pointer;">
                            Guardar
                        </button>
                    </form>

                    <!-- Eliminar -->
                    <form method="POST" action="<?= base('admin/areas/' . $area['id'] . '/delete') ?>"
                        onsubmit="return confirm('¿Eliminar el área <?= e($area['name']) ?>? Los repos quedarán sin área asignada.')">
                        <button type="submit"
                            style="background:transparent;border:1px solid var(--red);color:var(--red);font-size:12px;padding:4px 10px;border-radius:6px;cursor:pointer;">
                            Eliminar
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Crear nueva área -->
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;">
        <h2 style="color:var(--text-1);font-size:14px;font-weight:500;margin-bottom:16px;">Nueva área</h2>
        <form method="POST" action="<?= base('admin/areas') ?>" style="display:flex;flex-direction:column;gap:12px;">
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Nombre</label>
                <input type="text" name="name" required placeholder="Ej: Base de datos"
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;"
                    onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Color</label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <input type="color" name="color" value="#a371f7"
                        style="width:36px;height:36px;border:1px solid var(--border-2);border-radius:6px;background:var(--bg-input);cursor:pointer;padding:2px;">
                    <span style="font-size:12px;color:var(--text-2);">Elegí el color que represente esta área</span>
                </div>
            </div>
            <button type="submit"
                style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:8px;border-radius:6px;cursor:pointer;">
                Crear área
            </button>
        </form>
    </div>

</div>
<?php
/** @var array $categories */
/** @var string|null $error */
/** @var string|null $success */
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <h1 style="color:var(--text-1);font-size:20px;font-weight:600;margin-bottom:6px;">Secciones de base de conocimiento</h1>
        <p style="font-size:13px;color:var(--text-2);">Primero crea las secciones. Luego los articulos se cargan dentro de una de ellas desde el portal de soporte.</p>
    </div>
    <?php if (hasRole('admin')): ?>
        <div style="display:flex;gap:8px;">
            <a href="<?= base('admin/users') ?>"
                style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-2);font-size:13px;padding:6px 14px;border-radius:6px;text-decoration:none;">
                Usuarios
            </a>
            <a href="<?= base('admin/areas') ?>"
                style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-2);font-size:13px;padding:6px 14px;border-radius:6px;text-decoration:none;">
                Areas
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div style="background:var(--red-bg);border:1px solid var(--red);color:var(--red);border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:13px;"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="background:var(--green-bg);border:1px solid var(--green);color:var(--green);border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:13px;"><?= e($success) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;">
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;height:max-content;">
        <h2 style="color:var(--text-1);font-size:14px;font-weight:500;margin-bottom:16px;">Nueva seccion</h2>
        <form method="POST" action="<?= base('admin/support/categories') ?>" style="display:flex;flex-direction:column;gap:12px;">
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Nombre</label>
                <input type="text" name="name" required
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Color</label>
                <input type="color" name="color" value="#58a6ff"
                    style="width:100%;height:38px;background:var(--bg-input);border:1px solid var(--border-2);border-radius:6px;padding:4px;cursor:pointer;">
            </div>
            <button type="submit"
                style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:8px;border-radius:6px;cursor:pointer;">
                Crear seccion
            </button>
        </form>
    </div>

    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th style="text-align:left;padding:10px 14px;font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.05em;font-weight:500;">Seccion</th>
                    <th style="text-align:left;padding:10px 14px;font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.05em;font-weight:500;">Articulos</th>
                    <th style="padding:10px 14px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="3" style="padding:28px 14px;text-align:center;color:var(--text-2);font-size:13px;">
                            Todavia no hay secciones. Crea la primera para empezar a cargar articulos.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($categories as $category): ?>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 14px;">
                            <form method="POST" action="<?= base('admin/support/categories/' . $category['id'] . '/update') ?>" style="display:flex;align-items:center;gap:8px;">
                                <input type="color" name="color" value="<?= e($category['color']) ?>"
                                    style="width:34px;height:34px;background:var(--bg-input);border:1px solid var(--border-2);border-radius:6px;padding:3px;cursor:pointer;">
                                <input type="text" name="name" value="<?= e($category['name']) ?>"
                                    style="flex:1;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
                                <button type="submit"
                                    style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);font-size:12px;padding:6px 10px;border-radius:6px;cursor:pointer;">
                                    Guardar
                                </button>
                            </form>
                        </td>
                        <td style="padding:10px 14px;color:var(--text-2);"><?= (int)$category['article_count'] ?></td>
                        <td style="padding:10px 14px;text-align:right;">
                            <form method="POST" action="<?= base('admin/support/categories/' . $category['id'] . '/delete') ?>"
                                onsubmit="return confirm('Eliminar esta seccion tambien eliminara sus articulos. Continuar?');">
                                <button type="submit"
                                    style="background:transparent;border:1px solid var(--red);color:var(--red);font-size:12px;padding:6px 10px;border-radius:6px;cursor:pointer;">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

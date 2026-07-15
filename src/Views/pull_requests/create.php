<div style="max-width:640px;">
    <nav style="font-size:13px;color:var(--text-2);margin-bottom:8px;">
        <a href="<?= base('repos/' . e($repo['name'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($repo['name']) ?></a>
        <span style="margin:0 6px;">/</span>
        <a href="<?= base('repos/' . e($repo['name']) . '/pulls') ?>" style="color:var(--text-2);text-decoration:none;">Pull Requests</a>
        <span style="margin:0 6px;">/</span>
        <span style="color:var(--text-1);">Nuevo</span>
    </nav>
    <h1 style="color:var(--text-1);font-size:20px;font-weight:600;margin-bottom:20px;">Abrir Pull Request</h1>

    <?php if ($error ?? null): ?>
        <div class="flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:24px;">
        <form method="POST" action="<?= base('repos/' . e($repo['name']) . '/pulls') ?>">

            <div style="display:flex;align-items:flex-end;gap:12px;margin-bottom:20px;">
                <div style="flex:1;">
                    <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:6px;">Rama origen (con los cambios)</label>
                    <select name="source_branch" required style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
                        <option value="">Seleccioná una rama...</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= e($b) ?>"><?= e($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="color:var(--text-2);font-size:18px;padding-bottom:8px;">→</div>
                <div style="flex:1;">
                    <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:6px;">Rama destino (donde se mergea)</label>
                    <select name="target_branch" required style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
                        <option value="">Seleccioná una rama...</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= e($b) ?>" <?= $b === $repo['default_branch'] ? 'selected' : '' ?>><?= e($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:6px;">Título <span style="color:var(--red);">*</span></label>
                <input type="text" name="title" required
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 12px;font-size:13px;outline:none;"
                    placeholder="Ej: Agregar validación de formulario"
                    onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:6px;">
                    Descripción <span style="color:var(--text-2);font-weight:400;">(opcional)</span>
                </label>
                <textarea name="description" rows="4"
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 12px;font-size:13px;outline:none;resize:vertical;"
                    placeholder="Describí los cambios que incluye este PR..."
                    onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'"></textarea>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit"
                    style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 18px;border-radius:6px;cursor:pointer;">
                    Abrir Pull Request
                </button>
                <a href="<?= base('repos/' . e($repo['name']) . '/pulls') ?>"
                    style="background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-size:13px;padding:7px 18px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
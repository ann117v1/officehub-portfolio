<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

    <!-- Formulario -->
    <div>
        <nav style="font-size:13px;color:var(--text-2);margin-bottom:8px;">
            <a href="<?= base('repos') ?>" style="color:var(--text-2);text-decoration:none;"
                onmouseover="this.style.color='var(--text-1)'" onmouseout="this.style.color='var(--text-2)'">Repositorios</a>
            <span style="margin:0 6px;">/</span>
            <span style="color:var(--text-1);">Nuevo repositorio</span>
        </nav>
        <h1 style="color:var(--text-1);font-size:20px;font-weight:600;margin-bottom:20px;">Crear repositorio</h1>

        <?php if ($error ?? null): ?>
            <div class="flash-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:24px;">
            <form method="POST" action="<?= base('repos') ?>">

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:6px;">
                        Nombre <span style="color:var(--red);">*</span>
                    </label>
                    <input type="text" name="name" required pattern="[a-zA-Z0-9\-_]+"
                        style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 12px;font-size:13px;outline:none;"
                        placeholder="mi-proyecto"
                        onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
                    <p style="font-size:12px;color:var(--text-3);margin-top:4px;">Solo letras, números, guiones y guiones bajos. Sin espacios.</p>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:6px;">
                        Descripción <span style="color:var(--text-2);font-weight:400;">(opcional)</span>
                    </label>
                    <textarea name="description" rows="3"
                        style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 12px;font-size:13px;outline:none;resize:vertical;"
                        placeholder="Breve descripción del proyecto..."
                        onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'"></textarea>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:13px;color:var(--text-1);font-weight:500;margin-bottom:10px;">Visibilidad</label>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <label style="display:flex;align-items:flex-start;gap:10px;background:var(--bg-input);border:1px solid var(--border-2);border-radius:6px;padding:12px;cursor:pointer;">
                            <input type="radio" name="visibility" value="internal" checked style="margin-top:2px;accent-color:var(--accent);">
                            <div>
                                <span style="color:var(--text-1);font-size:13px;font-weight:500;">Internal</span>
                                <p style="color:var(--text-2);font-size:12px;margin-top:2px;">Visible para todos los miembros del equipo</p>
                            </div>
                        </label>
                        <label style="display:flex;align-items:flex-start;gap:10px;background:var(--bg-input);border:1px solid var(--border-2);border-radius:6px;padding:12px;cursor:pointer;">
                            <input type="radio" name="visibility" value="private" style="margin-top:2px;accent-color:var(--accent);">
                            <div>
                                <span style="color:var(--text-1);font-size:13px;font-weight:500;">Private</span>
                                <p style="color:var(--text-2);font-size:12px;margin-top:2px;">Solo vos y los colaboradores que agregues</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit"
                        style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 18px;border-radius:6px;cursor:pointer;">
                        Crear repositorio
                    </button>
                    <a href="<?= base('repos') ?>"
                        style="background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-size:13px;padding:7px 18px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;"
                        onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Card de guía -->
    <div style="position:sticky;top:72px;">
        <?php $guideId = 'create';
        require __DIR__ . '/partials/push_guide.php'; ?>
    </div>

</div>
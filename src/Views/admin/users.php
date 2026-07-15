<?php
/** @var array $users */
/** @var string|null $error */
/** @var string|null $success */

$currentUser = currentUser();
?>

<style>
    .admin-users-layout {
        display: grid;
        grid-template-columns: 300px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .admin-users-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 0;
    }

    .admin-users-list-head {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(220px, 1.4fr) minmax(110px, auto) minmax(150px, auto);
        align-items: center;
        gap: 14px;
        padding: 0 14px 6px;
        font-size: 11px;
        color: var(--text-2);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .admin-user-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 14px;
    }

    .admin-user-card-head {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(220px, 1.4fr) minmax(110px, auto) minmax(150px, auto);
        align-items: center;
        gap: 14px;
        margin-bottom: 12px;
    }

    .admin-users-email {
        color: var(--text-2);
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .admin-users-title {
        margin-top: 3px;
        color: var(--text-3);
        font-size: 11px;
        font-weight: 500;
    }

    .admin-user-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .permission-form {
        display: grid;
        grid-template-columns: minmax(160px, 1fr) minmax(150px, 0.9fr) minmax(96px, auto) minmax(170px, 1fr) minmax(250px, 1.25fr) minmax(90px, auto);
        align-items: center;
        gap: 8px;
        width: 100%;
        border-top: 1px solid var(--border);
        padding-top: 12px;
    }

    .permission-form select {
        width: 100%;
        min-width: 0;
    }

    .permission-title-input {
        width: 100%;
        min-width: 0;
        background: var(--bg-input);
        border: 1px solid var(--border-2);
        color: var(--text-1);
        border-radius: 6px;
        padding: 7px 9px;
        font-size: 12px;
        outline: none;
    }

    .permission-notification-checks {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .permission-notification-groups {
        display: grid;
        gap: 7px;
        min-width: 0;
    }

    .permission-notification-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .permission-notification-label {
        width: 42px;
        flex: 0 0 42px;
        color: var(--text-3);
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .create-notification-group {
        display: grid;
        gap: 7px;
        padding: 10px;
        background: var(--bg-input);
        border: 1px solid var(--border-2);
        border-radius: 6px;
    }

    .permission-admin-note {
        grid-column: 1 / -1;
    }

    @media (max-width: 1180px) {
        .admin-users-layout {
            grid-template-columns: 1fr;
        }

        .admin-users-list-head {
            display: none;
        }

        .admin-user-card-head {
            grid-template-columns: 1fr;
            gap: 7px;
        }

        .admin-user-actions {
            justify-content: flex-start;
        }

        .permission-form {
            grid-template-columns: minmax(150px, 1fr) minmax(170px, 1fr);
        }

        .permission-form button {
            width: max-content;
        }
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="color:var(--text-1);font-size:20px;font-weight:600;">Administracion de usuarios</h1>
    <div style="display:flex;gap:8px;">
        <a href="<?= base('admin/support') ?>"
            style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-2);font-size:13px;padding:6px 14px;border-radius:6px;text-decoration:none;">
            Gestionar soporte
        </a>
        <a href="<?= base('admin/areas') ?>"
            style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-2);font-size:13px;padding:6px 14px;border-radius:6px;text-decoration:none;">
            Gestionar areas
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div style="background:var(--red-bg);border:1px solid var(--red);color:var(--red);border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:13px;">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="background:var(--green-bg);border:1px solid var(--green);color:var(--green);border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:13px;">
        <?= e($success) ?>
    </div>
<?php endif; ?>

<div class="admin-users-layout">
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;height:max-content;">
        <h2 style="color:var(--text-1);font-size:14px;font-weight:500;margin-bottom:16px;">Crear usuario</h2>
        <form method="POST" action="<?= base('admin/users') ?>" style="display:flex;flex-direction:column;gap:12px;" id="create-user-form">
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Usuario</label>
                <input type="text" name="username" required
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Email</label>
                <input type="email" name="email" required
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Cargo visible</label>
                <input type="text" name="display_title" maxlength="100" placeholder="Ej: Desarrollo, Soporte, Infraestructura"
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Contrasena</label>
                <div style="display:flex;align-items:center;background:var(--bg-input);border:1px solid var(--border-2);border-radius:6px;overflow:hidden;">
                    <input type="password" name="password" id="new-password" required minlength="8"
                        style="flex:1;background:transparent;border:none;color:var(--text-1);padding:7px 10px;font-size:13px;outline:none;">
                    <button type="button" onclick="togglePasswordVisibility()"
                        title="Mostrar u ocultar contrasena"
                        style="width:34px;height:32px;background:transparent;border:none;border-left:1px solid var(--border-2);color:var(--text-2);cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg id="password-eye" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Rol repositorios</label>
                <select name="role" class="create-role"
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;cursor:pointer;">
                    <option value="developer">Developer</option>
                    <option value="viewer">Viewer</option>
                    <option value="admin">Admin total</option>
                </select>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2);">
                <input type="checkbox" name="repo_access" value="1" checked class="create-repo-access" style="accent-color:var(--accent);">
                Ver modulo Repositorios
            </label>
            <div class="create-notification-group">
                <span style="font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;">Notificaciones</span>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2);">
                    <input type="checkbox" name="notifications_enabled" value="1" checked style="accent-color:var(--accent);">
                    Campana del portal
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2);">
                    <input type="checkbox" name="commit_notifications_enabled" value="1" style="accent-color:var(--accent);">
                    Commits en el portal
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2);">
                    <input type="checkbox" name="board_email_notifications_enabled" value="1" style="accent-color:var(--accent);">
                    Tarjetas por email
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-2);">
                    <input type="checkbox" name="commit_email_notifications_enabled" value="1" style="accent-color:var(--accent);">
                    Commits por email
                </label>
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:4px;">Rol soporte</label>
                <select name="support_role" class="create-support-role"
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 10px;font-size:13px;outline:none;cursor:pointer;">
                    <option value="none">Sin soporte</option>
                    <option value="support_viewer">Soporte viewer</option>
                    <option value="support_admin">Soporte admin</option>
                </select>
            </div>
            <p class="create-admin-note" style="display:none;font-size:12px;color:var(--text-3);line-height:1.45;">
                Admin total siempre conserva acceso a Repositorios y Soporte.
            </p>
            <button type="submit"
                style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:8px;border-radius:6px;cursor:pointer;">
                Crear usuario
            </button>
        </form>
    </div>

    <div class="admin-users-list">
        <div class="admin-users-list-head">
            <span>Usuario</span>
            <span>Email</span>
            <span>Estado</span>
            <span style="text-align:right;">Acciones</span>
        </div>

        <?php foreach ($users as $u): ?>
            <?php $isSelf = (int)$u['id'] === (int)$currentUser['id']; ?>
            <section class="admin-user-card">
                <div class="admin-user-card-head">
                    <div style="color:var(--text-1);font-weight:600;">
                        <?= e($u['username']) ?>
                        <?php if ($isSelf): ?>
                            <span style="font-size:11px;color:var(--text-3);font-weight:400;">(vos)</span>
                        <?php endif; ?>
                        <div class="admin-users-title"><?= e(($u['display_title'] ?? '') !== '' ? $u['display_title'] : 'Sin cargo visible') ?></div>
                    </div>

                    <div class="admin-users-email"><?= e($u['email']) ?></div>

                    <div>
                        <?php if ($u['is_active']): ?>
                            <span style="background:var(--green-bg);color:var(--green);border:1px solid var(--green);font-size:11px;padding:2px 8px;border-radius:20px;">Activo</span>
                        <?php else: ?>
                            <span style="background:var(--red-bg);color:var(--red);border:1px solid var(--red);font-size:11px;padding:2px 8px;border-radius:20px;">Inactivo</span>
                        <?php endif; ?>
                    </div>

                    <div class="admin-user-actions">
                        <?php if (!$isSelf): ?>
                            <form method="POST" action="<?= base('admin/users/' . $u['id'] . '/toggle') ?>" style="display:inline;">
                                <button type="submit"
                                    style="background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-size:12px;padding:4px 9px;border-radius:6px;cursor:pointer;">
                                    <?= $u['is_active'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                            <form method="POST" action="<?= base('admin/users/' . $u['id'] . '/delete') ?>" style="display:inline;"
                                onsubmit="return confirm('Eliminar este usuario? Si tiene actividad asociada, el sistema lo va a impedir.');">
                                <button type="submit" title="Eliminar usuario"
                                    style="width:30px;height:28px;background:transparent;border:1px solid var(--red);color:var(--red);border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4h8v2" />
                                        <path d="M19 6l-1 14H6L5 6" />
                                        <path d="M10 11v5" />
                                        <path d="M14 11v5" />
                                    </svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" action="<?= base('admin/users/' . $u['id'] . '/access') ?>" class="permission-form">
                    <input type="text" name="display_title" class="permission-title-input" maxlength="100"
                        value="<?= e($u['display_title'] ?? '') ?>"
                        placeholder="Cargo visible">

                    <?php if ($isSelf): ?>
                        <input type="hidden" name="role" value="<?= e($u['role']) ?>">
                    <?php endif; ?>
                    <select name="role" class="permission-role" <?= $isSelf ? 'disabled' : '' ?>
                        style="background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 9px;font-size:12px;outline:none;<?= $isSelf ? 'opacity:0.65;' : '' ?>">
                        <?php foreach (['admin' => 'Admin total', 'developer' => 'Developer', 'viewer' => 'Viewer'] as $roleVal => $roleLabel): ?>
                            <option value="<?= $roleVal ?>" <?= ($u['role'] ?? 'viewer') === $roleVal ? 'selected' : '' ?>><?= $roleLabel ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-2);">
                        <input type="checkbox" name="repo_access" value="1" class="permission-repo-access" <?= (int)($u['repo_access'] ?? 1) === 1 ? 'checked' : '' ?> style="accent-color:var(--accent);">
                        Repos
                    </label>

                    <select name="support_role" class="permission-support-role"
                        style="background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 9px;font-size:12px;outline:none;">
                        <?php foreach (['none' => 'Sin soporte', 'support_viewer' => 'Soporte viewer', 'support_admin' => 'Soporte admin'] as $supportVal => $supportLabel): ?>
                            <option value="<?= $supportVal ?>" <?= ($u['support_role'] ?? 'none') === $supportVal ? 'selected' : '' ?>><?= $supportLabel ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="permission-notification-groups">
                        <div class="permission-notification-row">
                            <span class="permission-notification-label">Portal</span>
                            <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-2);">
                                <input type="checkbox" name="notifications_enabled" value="1" <?= (int)($u['notifications_enabled'] ?? 1) === 1 ? 'checked' : '' ?> style="accent-color:var(--accent);">
                                Campana
                            </label>
                            <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-2);">
                                <input type="checkbox" name="commit_notifications_enabled" value="1" <?= (int)($u['commit_notifications_enabled'] ?? 0) === 1 ? 'checked' : '' ?> style="accent-color:var(--accent);">
                                Commits
                            </label>
                        </div>
                        <div class="permission-notification-row">
                            <span class="permission-notification-label">Email</span>
                            <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-2);">
                                <input type="checkbox" name="board_email_notifications_enabled" value="1" <?= (int)($u['board_email_notifications_enabled'] ?? 0) === 1 ? 'checked' : '' ?> style="accent-color:var(--accent);">
                                Tarjetas
                            </label>
                            <label style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-2);">
                                <input type="checkbox" name="commit_email_notifications_enabled" value="1" <?= (int)($u['commit_email_notifications_enabled'] ?? 0) === 1 ? 'checked' : '' ?> style="accent-color:var(--accent);">
                                Commits
                            </label>
                        </div>
                    </div>

                    <button type="submit"
                        style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);font-size:12px;padding:7px 12px;border-radius:6px;cursor:pointer;">
                        Guardar
                    </button>

                    <span class="permission-admin-note" style="display:none;font-size:11px;color:var(--text-3);width:100%;">
                        Admin total siempre ve todos los modulos. Para limitar accesos, cambia el rol repo a Developer o Viewer.
                    </span>
                </form>
            </section>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('new-password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function syncAdminSelection(form) {
        const role = form.querySelector('.permission-role, .create-role');
        const repo = form.querySelector('.permission-repo-access, .create-repo-access');
        const support = form.querySelector('.permission-support-role, .create-support-role');
        const note = form.querySelector('.permission-admin-note, .create-admin-note');

        if (!role || !repo || !support || !note) {
            return;
        }

        if (role.value === 'admin') {
            repo.checked = true;
            support.value = 'support_admin';
            note.style.display = 'block';
        } else {
            note.style.display = 'none';
        }
    }

    function syncNotificationSelection(form) {
        const notifications = form.querySelector('input[name="notifications_enabled"]');
        const commits = form.querySelector('input[name="commit_notifications_enabled"]');

        if (!notifications || !commits) {
            return;
        }

        commits.disabled = !notifications.checked;

        if (!notifications.checked) {
            commits.checked = false;
        }
    }

    document.querySelectorAll('.permission-form, #create-user-form').forEach(form => {
        syncAdminSelection(form);
        syncNotificationSelection(form);
        form.addEventListener('change', () => {
            syncAdminSelection(form);
            syncNotificationSelection(form);
        });
    });
</script>

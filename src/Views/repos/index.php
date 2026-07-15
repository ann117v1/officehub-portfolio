<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 style="color:var(--text-1);font-size:20px;font-weight:600;">Repositorios</h1>
    <?php $user = currentUser(); ?>
    <?php if (($user['role'] ?? null) !== 'viewer'): ?>
        <a href="<?= base('repos/create') ?>"
            style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;text-decoration:none;">
            + Nuevo repositorio
        </a>
    <?php endif; ?>
</div>

<div style="margin-bottom:16px;">
    <input type="text" id="search" placeholder="Buscar repositorio..."
        style="background:var(--bg-surface);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:7px 14px;font-size:13px;width:280px;outline:none;"
        onkeyup="filterRepos()"
        onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
</div>

<?php if (empty($repos)): ?>
    <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:48px;text-align:center;">
        <p style="color:var(--text-2);font-size:14px;">No hay repositorios todavía.</p>
        <?php if (($user['role'] ?? null) !== 'viewer'): ?>
            <a href="<?= base('repos/create') ?>" style="color:var(--accent);font-size:13px;text-decoration:none;margin-top:8px;display:inline-block;">Crear el primero</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div id="repo-list" style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($repos as $repo): ?>
            <a href="<?= base('repos/' . e($repo['name'])) ?>"
                data-name="<?= e(strtolower($repo['name'])) ?>"
                class="repo-item"
                style="display:block;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:14px 16px;text-decoration:none;"
                onmouseover="this.style.borderColor='var(--accent-brd)'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="color:var(--accent);font-size:14px;font-weight:500;"><?= e($repo['name']) ?></span>
                        <?php if ($repo['visibility'] === 'internal'): ?>
                            <span style="background:var(--green-bg);color:var(--green);border:1px solid var(--green);font-size:11px;padding:1px 8px;border-radius:20px;">internal</span>
                        <?php else: ?>
                            <span style="background:var(--purple-bg);color:var(--purple);border:1px solid var(--purple);font-size:11px;padding:1px 8px;border-radius:20px;">private</span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="font-size:12px;color:var(--text-3);"><?= e($repo['owner_name']) ?></span>
                        <span style="font-size:12px;color:var(--text-3);"><?= dateFormat($repo['created_at'], 'd/m/Y') ?></span>
                    </div>
                </div>
                <?php if ($repo['description']): ?>
                    <p style="color:var(--text-2);font-size:12px;margin-top:4px;"><?= e($repo['description']) ?></p>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    function filterRepos() {
        const query = document.getElementById('search').value.toLowerCase();
        document.querySelectorAll('.repo-item').forEach(item => {
            item.style.display = item.getAttribute('data-name').includes(query) ? 'block' : 'none';
        });
    }
</script>
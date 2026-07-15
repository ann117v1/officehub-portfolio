<?php
$actionColors = [
    'create_repo' => '#3fb950',
    'delete_repo' => '#f85149',
    'open_pr'     => '#58a6ff',
    'merge_pr'    => '#a371f7',
    'close_pr'    => '#8b949e',
];
?>

<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:24px;">
    <div style="flex:1;min-width:0;">
        <h1 style="color:var(--text-1);font-size:20px;font-weight:600;margin-bottom:14px;">
            Dashboard
            <?php
            $activeAreaId = \OfficeHub\Core\Session::get('active_area_id');
            if ($activeAreaId) {
                $currentArea = \OfficeHub\Models\Area::findById((int)$activeAreaId);
                if ($currentArea) {
                    echo '<span style="color:var(--text-3);font-weight:400;font-size:18px;margin-left:4px;">&rarr;</span>';
                    echo '<span style="color:' . e($currentArea['color']) . ';font-weight:500;font-size:18px;margin-left:6px;">' . e($currentArea['name']) . '</span>';
                }
            } else {
                echo '<span style="color:var(--text-3);font-weight:400;font-size:18px;margin-left:4px;">&rarr;</span>';
                echo '<span style="color:var(--text-2);font-weight:400;font-size:18px;margin-left:6px;">Todo</span>';
            }
            ?>
        </h1>

        <?php if (!empty($repos)): ?>
            <div style="position:relative;max-width:420px;">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:13px;">/</span>
                <input type="search" id="dashboard-repo-search" placeholder="Buscar repositorio por nombre..."
                    autocomplete="off"
                    style="width:100%;background:var(--bg-surface);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:9px 12px 9px 34px;font-size:13px;outline:none;"
                    onfocus="this.style.borderColor='var(--accent-brd)'" onblur="this.style.borderColor='var(--border-2)'">
            </div>
        <?php endif; ?>
    </div>

    <a href="<?= base('repos/create') ?>"
        style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:7px 16px;border-radius:6px;text-decoration:none;">
        + Nuevo repositorio
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;">

    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
            <p style="font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.06em;">Repositorios</p>
            <?php if (!empty($repos)): ?>
                <span id="repo-count-label" style="font-size:12px;color:var(--text-3);"><?= count($repos) ?> en esta vista</span>
            <?php endif; ?>
        </div>

        <?php if (empty($repos)): ?>
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:32px;text-align:center;">
                <p style="color:var(--text-2);font-size:14px;">No hay repositorios todavia.</p>
                <a href="<?= base('repos/create') ?>" style="color:var(--accent);font-size:13px;text-decoration:none;margin-top:8px;display:inline-block;">Crear el primero</a>
            </div>
        <?php else: ?>
            <div id="dashboard-repo-list" style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($repos as $repo): ?>
                    <a href="<?= base('repos/' . e($repo['name'])) ?>"
                        class="dashboard-repo-card"
                        data-repo-name="<?= e(strtolower($repo['name'])) ?>"
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
                            <span style="color:var(--text-3);font-size:12px;"><?= e($repo['owner_name']) ?></span>
                        </div>
                        <?php if ($repo['description']): ?>
                            <p style="color:var(--text-2);font-size:12px;margin-top:4px;"><?= e($repo['description']) ?></p>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div id="dashboard-repo-empty" style="display:none;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:28px;text-align:center;">
                <p style="color:var(--text-2);font-size:14px;">No se encontraron repositorios con ese nombre.</p>
            </div>

            <div id="dashboard-repo-pager" style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:10px 12px;">
                <button type="button" id="repo-prev"
                    style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);font-size:12px;padding:6px 12px;border-radius:6px;cursor:pointer;">
                    Anterior
                </button>
                <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                    <span id="repo-page-label" style="font-size:12px;color:var(--text-2);white-space:nowrap;"></span>
                    <div id="repo-page-dots" style="display:flex;align-items:center;gap:5px;"></div>
                </div>
                <button type="button" id="repo-next"
                    style="background:var(--bg-hover);border:1px solid var(--border-2);color:var(--text-1);font-size:12px;padding:6px 12px;border-radius:6px;cursor:pointer;">
                    Siguiente
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <p style="font-size:11px;color:var(--text-2);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:12px;">Actividad reciente</p>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;">
            <?php if (empty($activity)): ?>
                <div style="padding:16px;font-size:13px;color:var(--text-2);">Sin actividad todavia.</div>
            <?php else: ?>
                <?php foreach ($activity as $event):
                    $dot = $actionColors[$event['action']] ?? '#8b949e';
                ?>
                    <div style="padding:10px 14px;border-bottom:1px solid var(--border);">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
                            <span style="width:7px;height:7px;border-radius:50%;background:<?= $dot ?>;flex-shrink:0;display:inline-block;"></span>
                            <span style="font-size:11px;font-family:monospace;background:var(--bg-hover);color:var(--text-2);padding:1px 7px;border-radius:4px;border:1px solid var(--border-2);">
                                <?= e($event['action']) ?>
                            </span>
                            <span style="font-size:11px;color:var(--text-2);"><?= e($event['username']) ?></span>
                        </div>
                        <?php if ($event['repo_name']): ?>
                            <a href="<?= base('repos/' . e($event['repo_name'])) ?>"
                                style="font-size:12px;color:var(--accent);text-decoration:none;display:block;margin-left:15px;">
                                <?= e($event['repo_name']) ?>
                            </a>
                        <?php endif; ?>
                        <span style="font-size:11px;color:var(--text-3);display:block;margin-left:15px;"><?= dateFormat($event['created_at']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php if (!empty($repos)): ?>
    <script>
        (() => {
            const perPage = 5;
            const cards = Array.from(document.querySelectorAll('.dashboard-repo-card'));
            const search = document.getElementById('dashboard-repo-search');
            const empty = document.getElementById('dashboard-repo-empty');
            const pager = document.getElementById('dashboard-repo-pager');
            const prev = document.getElementById('repo-prev');
            const next = document.getElementById('repo-next');
            const label = document.getElementById('repo-page-label');
            const dots = document.getElementById('repo-page-dots');
            const countLabel = document.getElementById('repo-count-label');
            let page = 0;
            let filteredCards = cards;

            function renderDots(totalPages) {
                dots.innerHTML = '';
                for (let i = 0; i < totalPages; i++) {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.setAttribute('aria-label', 'Ir a pagina ' + (i + 1));
                    dot.style.width = '8px';
                    dot.style.height = '8px';
                    dot.style.borderRadius = '50%';
                    dot.style.border = i === page ? '1px solid var(--accent)' : '1px solid var(--border-2)';
                    dot.style.background = i === page ? 'var(--accent)' : 'transparent';
                    dot.style.cursor = 'pointer';
                    dot.style.padding = '0';
                    dot.onclick = () => {
                        page = i;
                        renderPage();
                    };
                    dots.appendChild(dot);
                }
            }

            function renderPage() {
                const totalPages = Math.max(1, Math.ceil(filteredCards.length / perPage));
                if (page > totalPages - 1) {
                    page = totalPages - 1;
                }

                const start = page * perPage;
                const end = start + perPage;

                cards.forEach(card => {
                    card.style.display = 'none';
                });

                filteredCards.forEach((card, index) => {
                    card.style.display = index >= start && index < end ? 'block' : 'none';
                });

                const hasResults = filteredCards.length > 0;
                empty.style.display = hasResults ? 'none' : 'block';
                pager.style.display = hasResults && totalPages > 1 ? 'flex' : 'none';

                if (countLabel) {
                    countLabel.textContent = filteredCards.length + ' en esta vista';
                }

                label.textContent = 'Pagina ' + (page + 1) + ' de ' + totalPages + ' - ' + filteredCards.length + ' repos';
                prev.disabled = page === 0 || !hasResults;
                next.disabled = page === totalPages - 1 || !hasResults;
                prev.style.opacity = prev.disabled ? '0.45' : '1';
                next.style.opacity = next.disabled ? '0.45' : '1';
                prev.style.cursor = prev.disabled ? 'default' : 'pointer';
                next.style.cursor = next.disabled ? 'default' : 'pointer';
                renderDots(totalPages);
            }

            function applySearch() {
                const query = (search?.value || '').trim().toLowerCase();
                filteredCards = query === ''
                    ? cards
                    : cards.filter(card => card.dataset.repoName.includes(query));
                page = 0;
                renderPage();
            }

            prev.onclick = () => {
                if (page > 0) {
                    page--;
                    renderPage();
                }
            };

            next.onclick = () => {
                const totalPages = Math.ceil(filteredCards.length / perPage);
                if (page < totalPages - 1) {
                    page++;
                    renderPage();
                }
            };

            search?.addEventListener('input', applySearch);
            renderPage();
        })();
    </script>
<?php endif; ?>

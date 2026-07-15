<?php
/** @var bool $isConfigured */
/** @var string $selectedBoardId */
/** @var array $boards */
/** @var array|null $board */
/** @var array $lists */
/** @var array $cardsByList */
/** @var array $cards */
/** @var string|null $error */

$labelColors = [
    'green' => '#3fb950',
    'yellow' => '#d29922',
    'orange' => '#f0883e',
    'red' => '#f85149',
    'purple' => '#a371f7',
    'blue' => '#58a6ff',
    'sky' => '#79c0ff',
    'lime' => '#56d364',
    'pink' => '#db61a2',
    'black' => '#484f58',
];

$memberColors = ['#1f6feb', '#238636', '#a371f7', '#db6d28', '#2f81f7', '#d29922', '#bf3989'];
?>

<style>
    .trello-page {
        --trello-list-bg: rgba(22, 27, 34, 0.94);
        --trello-list-border: rgba(139, 148, 158, 0.18);
        --trello-card-bg: #222831;
        --trello-card-border: #39424f;
        --trello-card-hover: #2a323d;
        --trello-card-shadow: rgba(0, 0, 0, 0.26);
        --trello-muted-card: rgba(22, 27, 34, 0.66);
        position: relative;
        left: 50%;
        width: calc(100vw - 48px);
        transform: translateX(-50%);
    }

    [data-theme="light"] .trello-page {
        --trello-list-bg: rgba(226, 232, 240, 0.94);
        --trello-list-border: rgba(9, 105, 218, 0.18);
        --trello-card-bg: #ffffff;
        --trello-card-border: #b8c6d8;
        --trello-card-hover: #edf6ff;
        --trello-card-shadow: rgba(31, 111, 235, 0.12);
        --trello-muted-card: rgba(255, 255, 255, 0.66);
    }

    .trello-board-shell {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-height: calc(100vh - 210px);
        overflow-x: auto;
        overflow-y: hidden;
        padding: 4px 0 18px;
        scroll-snap-type: x proximity;
    }

    .trello-list {
        flex: 0 0 286px;
        max-height: calc(100vh - 220px);
        background: var(--trello-list-bg);
        border: 1px solid var(--trello-list-border);
        border-radius: 10px;
        overflow: hidden;
        scroll-snap-align: start;
        box-shadow: 0 16px 32px var(--trello-card-shadow);
    }

    .trello-list.drop-active {
        border-color: var(--accent-brd);
        box-shadow: 0 0 0 2px rgba(88, 166, 255, 0.18), 0 16px 32px var(--trello-card-shadow);
    }

    .trello-list-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
    }

    .trello-list-title {
        color: var(--text-1);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .trello-cards {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 58px;
        max-height: calc(100vh - 296px);
        overflow-y: auto;
        padding: 10px;
    }

    .trello-card {
        background: var(--trello-card-bg);
        border: 1px solid var(--trello-card-border);
        border-radius: 8px;
        padding: 10px;
        cursor: grab;
        box-shadow: 0 8px 18px var(--trello-card-shadow);
        transition: background 0.15s, border-color 0.15s, transform 0.15s;
    }

    .trello-card:hover {
        background: var(--trello-card-hover);
        border-color: var(--accent-brd);
    }

    .trello-card.is-complete {
        border-color: rgba(63, 185, 80, 0.68);
        box-shadow: 0 0 0 1px rgba(63, 185, 80, 0.22), 0 8px 18px var(--trello-card-shadow);
    }

    .trello-card.dragging {
        opacity: 0.5;
        transform: rotate(1deg);
        cursor: grabbing;
    }

    .trello-card-heading {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
    }

    .trello-card-check {
        flex: 0 0 auto;
        width: 18px;
        height: 18px;
        margin-top: 1px;
        border-radius: 50%;
        border: 1px solid var(--border-2);
        background: var(--bg-hover);
        color: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s;
    }

    .trello-card-check:hover {
        border-color: var(--green);
        transform: scale(1.05);
    }

    .trello-card-check.is-complete {
        background: var(--green);
        border-color: var(--green);
        color: #fff;
    }

    .trello-card-check:disabled {
        opacity: 0.6;
        cursor: wait;
        transform: none;
    }

    .trello-card-title {
        color: var(--text-1);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
        min-width: 0;
    }

    .trello-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 8px;
    }

    .trello-badge-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .trello-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid var(--border-2);
        border-radius: 6px;
        color: var(--text-2);
        font-size: 11px;
        padding: 2px 6px;
        background: var(--bg-hover);
    }

    .trello-due-ok {
        color: var(--green);
        border-color: var(--green);
        background: var(--green-bg);
    }

    .trello-members {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        margin-left: auto;
    }

    .trello-member {
        position: relative;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 1px solid var(--border-2);
        overflow: visible;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
    }

    .trello-member img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        display: block;
    }

    .trello-member::after {
        content: "";
        position: absolute;
        right: -1px;
        bottom: -1px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--green);
        border: 2px solid var(--trello-card-bg);
    }

    .trello-empty {
        color: var(--text-3);
        font-size: 12px;
        padding: 8px;
        background: var(--trello-muted-card);
        border: 1px dashed var(--border-2);
        border-radius: 8px;
    }

    .trello-add-list {
        flex: 0 0 286px;
        color: var(--text-2);
        background: rgba(139, 148, 158, 0.16);
        border: 1px dashed var(--border-2);
        border-radius: 10px;
        padding: 13px 14px;
        font-size: 13px;
        font-weight: 600;
    }

    .trello-status {
        display: none;
        margin-bottom: 12px;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
    }

    .trello-status.ok {
        display: block;
        color: var(--green);
        background: var(--green-bg);
        border: 1px solid var(--green);
    }

    .trello-status.error {
        display: block;
        color: var(--red);
        background: var(--red-bg);
        border: 1px solid var(--red);
    }
</style>

<div class="trello-page">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px;">
        <div>
            <h1 style="color:var(--text-1);font-size:22px;font-weight:700;margin-bottom:6px;">
                <?= e($board['name'] ?? 'Tablero Trello') ?>
            </h1>
            <p style="font-size:13px;color:var(--text-2);line-height:1.5;">
                <?= $isConfigured && !$error ? count($lists) . ' listas - ' . count($cards) . ' tarjetas visibles' : 'Tablero integrado con Trello' ?>
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
            <?php if ($isConfigured && !$error && count($boards) > 1): ?>
                <form method="GET" action="<?= base('trello') ?>">
                    <select name="board" onchange="this.form.submit()"
                        style="background:var(--bg-surface);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:8px 10px;font-size:13px;outline:none;">
                        <?php foreach ($boards as $availableBoard): ?>
                            <option value="<?= e($availableBoard['id']) ?>" <?= $selectedBoardId === $availableBoard['id'] ? 'selected' : '' ?>>
                                <?= e($availableBoard['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>

            <?php if ($board && !empty($board['url'])): ?>
                <a href="<?= e($board['url']) ?>" target="_blank" rel="noopener noreferrer"
                    style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-1);font-size:13px;font-weight:500;padding:8px 14px;border-radius:6px;text-decoration:none;">
                    Abrir en Trello
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$isConfigured): ?>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:22px;max-width:760px;">
            <h2 style="font-size:17px;color:var(--text-1);font-weight:600;margin-bottom:10px;">Configurar credenciales de Trello</h2>
            <p style="font-size:14px;color:var(--text-2);line-height:1.65;margin-bottom:14px;">
                Falta configurar la API key y el token. Crea un archivo local en <code>config/trello.local.php</code> tomando como base <code>config/trello.example.php</code>.
            </p>
            <pre style="background:var(--bg-input);border:1px solid var(--border-2);border-radius:8px;padding:14px;overflow:auto;color:var(--text-1);font-size:12px;"><code>&lt;?php

return [
    'api_key' =&gt; 'TU_API_KEY_DE_TRELLO',
    'token' =&gt; 'TU_TOKEN_DE_TRELLO',
    'board_id' =&gt; 'ID_DEL_TABLERO_A_MOSTRAR',
];</code></pre>
        </div>
    <?php elseif ($error): ?>
        <div style="background:var(--red-bg);border:1px solid var(--red);border-radius:8px;padding:16px;color:var(--red);font-size:13px;margin-bottom:18px;">
            <?= e($error) ?>
        </div>
    <?php else: ?>
        <div id="trello-status" class="trello-status"></div>

        <?php if (empty($lists)): ?>
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:30px;text-align:center;">
                <p style="font-size:14px;color:var(--text-2);">Este tablero no tiene listas abiertas para mostrar.</p>
            </div>
        <?php else: ?>
            <div class="trello-board-shell" id="trello-board-shell">
                <?php foreach ($lists as $list): ?>
                    <?php $listCards = $cardsByList[$list['id']] ?? []; ?>
                    <section class="trello-list" data-list-id="<?= e($list['id']) ?>">
                        <div class="trello-list-header">
                            <h3 class="trello-list-title"><?= e($list['name']) ?></h3>
                            <span class="trello-list-count" style="font-size:11px;color:var(--text-3);"><?= count($listCards) ?></span>
                        </div>

                        <div class="trello-cards" data-list-id="<?= e($list['id']) ?>">
                            <p class="trello-empty" style="<?= empty($listCards) ? '' : 'display:none;' ?>">Sin tarjetas.</p>

                            <?php foreach ($listCards as $card): ?>
                                <?php
                                $badges = is_array($card['badges'] ?? null) ? $card['badges'] : [];
                                $members = is_array($card['members'] ?? null) ? $card['members'] : [];
                                ?>
                                <?php $cardCompleted = !empty($card['dueComplete']); ?>
                                <article class="trello-card <?= $cardCompleted ? 'is-complete' : '' ?>" draggable="true" data-card-id="<?= e($card['id']) ?>" data-list-id="<?= e($list['id']) ?>" data-card-pos="<?= e($card['pos'] ?? '') ?>" data-card-complete="<?= $cardCompleted ? '1' : '0' ?>">
                                    <?php if (!empty($card['labels'])): ?>
                                        <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:8px;">
                                            <?php foreach (array_slice($card['labels'], 0, 5) as $label): ?>
                                                <?php $labelColor = $labelColors[$label['color'] ?? ''] ?? 'var(--border-2)'; ?>
                                                <span title="<?= e($label['name'] ?? '') ?>"
                                                    style="width:38px;height:7px;border-radius:20px;background:<?= e($labelColor) ?>;display:inline-block;"></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="trello-card-heading">
                                        <button type="button"
                                            class="trello-card-check <?= $cardCompleted ? 'is-complete' : '' ?>"
                                            aria-pressed="<?= $cardCompleted ? 'true' : 'false' ?>"
                                            title="<?= $cardCompleted ? 'Desmarcar tarjeta' : 'Marcar tarjeta' ?>">
                                            ✓
                                        </button>
                                        <div class="trello-card-title"><?= e($card['name'] ?? 'Sin titulo') ?></div>
                                    </div>

                                    <div class="trello-card-meta">
                                        <div class="trello-badge-row">
                                            <?php if (!empty($card['due'])): ?>
                                                <span class="trello-badge <?= !empty($card['dueComplete']) ? 'trello-due-ok' : '' ?>">
                                                    <?= !empty($card['dueComplete']) ? '✓' : '○' ?>
                                                    <?= dateFormat($card['due'], 'd/m/Y') ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if (!empty($badges['description'])): ?>
                                                <span class="trello-badge">≡</span>
                                            <?php endif; ?>

                                            <?php if (!empty($badges['checkItems'])): ?>
                                                <span class="trello-badge">
                                                    ☑ <?= (int)($badges['checkItemsChecked'] ?? 0) ?>/<?= (int)$badges['checkItems'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($members)): ?>
                                            <div class="trello-members">
                                                <?php foreach (array_slice($members, 0, 4) as $index => $member): ?>
                                                    <?php
                                                    $memberName = $member['fullName'] ?? $member['username'] ?? 'Miembro';
                                                    $initials = strtoupper((string)($member['initials'] ?? substr((string)$memberName, 0, 2)));
                                                    $memberColor = $memberColors[$index % count($memberColors)];
                                                    ?>
                                                    <span class="trello-member" title="<?= e($memberName) ?>" style="background:<?= e($memberColor) ?>;">
                                                        <?php if (!empty($member['avatarUrl'])): ?>
                                                            <img src="<?= e($member['avatarUrl'] . '/30.png') ?>" alt="<?= e($memberName) ?>">
                                                        <?php else: ?>
                                                            <?= e($initials) ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <a href="<?= e($card['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer"
                                        style="display:inline-block;margin-top:8px;color:var(--text-3);font-size:11px;text-decoration:none;">
                                        Abrir tarjeta
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>

                <div class="trello-add-list">+ Añade otra lista</div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($isConfigured && !$error && !empty($lists)): ?>
    <script>
        (() => {
            const moveUrl = '<?= base('trello/cards/move') ?>';
            const completeUrl = '<?= base('trello/cards/complete') ?>';
            const status = document.getElementById('trello-status');
            let draggedCard = null;
            let sourceContainer = null;
            let sourceNextSibling = null;

            function showStatus(message, type) {
                if (!status) return;
                status.className = 'trello-status ' + type;
                status.textContent = message;
                window.clearTimeout(showStatus.timer);
                showStatus.timer = window.setTimeout(() => {
                    status.className = 'trello-status';
                    status.textContent = '';
                }, 4200);
            }

            function updateCounts() {
                document.querySelectorAll('.trello-list').forEach(list => {
                    const cards = list.querySelectorAll('.trello-card');
                    const count = list.querySelector('.trello-list-count');
                    const empty = list.querySelector('.trello-empty');
                    if (count) count.textContent = cards.length;
                    if (empty) empty.style.display = cards.length === 0 ? 'block' : 'none';
                });
            }

            function getDragAfterElement(container, y) {
                const draggableCards = [...container.querySelectorAll('.trello-card:not(.dragging)')];

                return draggableCards.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;

                    if (offset < 0 && offset > closest.offset) {
                        return {
                            offset,
                            element: child
                        };
                    }

                    return closest;
                }, {
                    offset: Number.NEGATIVE_INFINITY,
                    element: null
                }).element;
            }

            function calculateTrelloPosition(card) {
                const previous = card.previousElementSibling?.classList.contains('trello-card') ? card.previousElementSibling : null;
                const next = card.nextElementSibling?.classList.contains('trello-card') ? card.nextElementSibling : null;
                const previousPos = previous ? Number(previous.dataset.cardPos || 0) : null;
                const nextPos = next ? Number(next.dataset.cardPos || 0) : null;

                if (previousPos !== null && nextPos !== null && nextPos > previousPos) {
                    return String((previousPos + nextPos) / 2);
                }

                if (previousPos === null && nextPos !== null) {
                    return String(Math.max(1, nextPos / 2));
                }

                if (previousPos !== null && nextPos === null) {
                    return String(previousPos + 65536);
                }

                return 'bottom';
            }

            function setCardComplete(card, completed) {
                const check = card.querySelector('.trello-card-check');
                card.dataset.cardComplete = completed ? '1' : '0';
                card.classList.toggle('is-complete', completed);

                if (!check) return;

                check.classList.toggle('is-complete', completed);
                check.setAttribute('aria-pressed', completed ? 'true' : 'false');
                check.title = completed ? 'Desmarcar tarjeta' : 'Marcar tarjeta';
            }

            async function persistMove(card, targetListId, position) {
                const body = new URLSearchParams();
                body.set('card_id', card.dataset.cardId);
                body.set('list_id', targetListId);
                body.set('position', position);

                const response = await fetch(moveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body
                });

                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No se pudo mover la tarjeta.');
                }

                return data;
            }

            async function persistComplete(card, completed) {
                const body = new URLSearchParams();
                body.set('card_id', card.dataset.cardId);
                body.set('completed', completed ? '1' : '0');

                const response = await fetch(completeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body
                });

                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No se pudo marcar la tarjeta.');
                }

                return data;
            }

            document.querySelectorAll('.trello-card').forEach(card => {
                card.addEventListener('dragstart', event => {
                    draggedCard = card;
                    sourceContainer = card.parentElement;
                    sourceNextSibling = card.nextElementSibling;
                    card.classList.add('dragging');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', card.dataset.cardId);
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('dragging');
                    document.querySelectorAll('.trello-list').forEach(list => list.classList.remove('drop-active'));
                    draggedCard = null;
                    sourceContainer = null;
                    sourceNextSibling = null;
                    updateCounts();
                });
            });

            document.querySelectorAll('.trello-card-check').forEach(check => {
                check.addEventListener('pointerdown', event => {
                    event.stopPropagation();
                });

                check.addEventListener('click', async event => {
                    event.preventDefault();
                    event.stopPropagation();

                    const card = check.closest('.trello-card');
                    if (!card || check.disabled) return;

                    const previousCompleted = card.dataset.cardComplete === '1';
                    const nextCompleted = !previousCompleted;

                    check.disabled = true;
                    setCardComplete(card, nextCompleted);

                    try {
                        const data = await persistComplete(card, nextCompleted);
                        setCardComplete(card, !!data.completed);
                        showStatus(data.completed ? 'Tarjeta marcada.' : 'Tarjeta desmarcada.', 'ok');
                    } catch (error) {
                        setCardComplete(card, previousCompleted);
                        showStatus(error.message + ' Si el token es solo lectura, generá uno con scope=read,write.', 'error');
                    } finally {
                        check.disabled = false;
                    }
                });
            });

            document.querySelectorAll('.trello-list').forEach(list => {
                list.addEventListener('dragover', event => {
                    if (!draggedCard) return;
                    event.preventDefault();
                    list.classList.add('drop-active');

                    const targetCards = list.querySelector('.trello-cards');
                    const afterElement = targetCards ? getDragAfterElement(targetCards, event.clientY) : null;

                    if (!targetCards) {
                        return;
                    }

                    if (afterElement) {
                        targetCards.insertBefore(draggedCard, afterElement);
                    } else {
                        targetCards.appendChild(draggedCard);
                    }

                    updateCounts();
                });

                list.addEventListener('dragleave', event => {
                    if (!list.contains(event.relatedTarget)) {
                        list.classList.remove('drop-active');
                    }
                });

                list.addEventListener('drop', async event => {
                    event.preventDefault();
                    list.classList.remove('drop-active');

                    if (!draggedCard) return;

                    const targetCards = list.querySelector('.trello-cards');
                    const targetListId = list.dataset.listId;
                    const previousListId = draggedCard.dataset.listId;
                    const previousPos = draggedCard.dataset.cardPos;
                    const samePosition = previousListId === targetListId && sourceNextSibling === draggedCard.nextElementSibling;

                    if (!targetCards || !targetListId || samePosition) {
                        draggedCard.dataset.listId = targetListId;
                        return;
                    }

                    const position = calculateTrelloPosition(draggedCard);
                    draggedCard.dataset.listId = targetListId;
                    draggedCard.dataset.cardPos = position === 'bottom' ? String(Date.now()) : position;
                    updateCounts();

                    try {
                        const data = await persistMove(draggedCard, targetListId, position);
                        draggedCard.dataset.listId = data.list_id || targetListId;
                        draggedCard.dataset.cardPos = data.position || draggedCard.dataset.cardPos;
                        showStatus('Tarjeta movida en Trello.', 'ok');
                    } catch (error) {
                        draggedCard.dataset.listId = previousListId;
                        draggedCard.dataset.cardPos = previousPos;
                        if (sourceNextSibling && sourceNextSibling.parentElement === sourceContainer) {
                            sourceContainer.insertBefore(draggedCard, sourceNextSibling);
                        } else {
                            sourceContainer.appendChild(draggedCard);
                        }
                        updateCounts();
                        showStatus(error.message + ' Si el token es solo lectura, generá uno con scope=read,write.', 'error');
                    }
                });
            });

            updateCounts();
        })();
    </script>
<?php endif; ?>

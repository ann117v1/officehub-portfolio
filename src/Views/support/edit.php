<?php
/** @var array $article */
/** @var array $categories */
/** @var array $attachments */
/** @var string|null $error */
$supportImageMap = [];

foreach ($attachments as $attachment) {
    if ((int)($attachment['is_image'] ?? 0) !== 1) {
        continue;
    }

    $imageUrl = base('soporte/adjunto/' . (int)$attachment['id']);
    $supportImageMap[strtolower((string)$attachment['filename'])] = $imageUrl;
    $supportImageMap[(string)$attachment['id']] = $imageUrl;
}
?>

<div style="max-width:700px;">
    <nav style="font-size:13px;color:var(--text-2);margin-bottom:18px;">
        <a href="<?= base('soporte') ?>" style="color:var(--text-2);text-decoration:none;">Soporte</a>
        <span style="margin:0 6px;">/</span>
        <a href="<?= base('soporte/articulo/' . e($article['slug'])) ?>" style="color:var(--text-2);text-decoration:none;"><?= e($article['title']) ?></a>
        <span style="margin:0 6px;">/</span>
        <span style="color:var(--text-1);">Editar</span>
    </nav>

    <h1 style="color:var(--text-1);font-size:22px;font-weight:600;margin-bottom:20px;">Editar articulo</h1>

    <?php if ($error): ?>
        <div style="background:var(--red-bg);border:1px solid var(--red);color:var(--red);border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:13px;">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= base('soporte/articulo/' . e($article['slug']) . '/editar') ?>" enctype="multipart/form-data"
        style="display:flex;flex-direction:column;gap:16px;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;padding:20px;">
        <div>
            <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:5px;">Titulo</label>
            <input type="text" name="title" required value="<?= e($article['title']) ?>"
                style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:9px 11px;font-size:14px;outline:none;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 180px;gap:12px;">
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:5px;">Seccion</label>
                <select name="category_id" required
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:9px 11px;font-size:14px;outline:none;">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= (int)$category['id'] === (int)$article['category_id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:5px;">Tipo</label>
                <select name="type"
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:9px 11px;font-size:14px;outline:none;">
                    <?php foreach (['info' => 'Info', 'faq' => 'FAQ', 'tutorial' => 'Tutorial'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $article['type'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:5px;">Contenido</label>
            <div id="support-editor" contenteditable="true"
                style="width:100%;min-height:300px;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-1);border-radius:6px;padding:11px;font-size:14px;line-height:1.55;outline:none;overflow:auto;"></div>
            <textarea name="body" id="support-body" style="display:none;"><?= e($article['body']) ?></textarea>
            <p style="font-size:12px;color:var(--text-3);margin-top:6px;">Podes escribir texto, pegar imagenes con Ctrl+V y usar titulos con "## ". Las imagenes se ven en el editor y se guardan como adjuntos.</p>
        </div>

        <?php if (!empty($attachments)): ?>
            <div>
                <p style="font-size:12px;color:var(--text-2);margin-bottom:4px;">Adjuntos actuales</p>
                <p style="font-size:12px;color:var(--text-3);margin-bottom:8px;">Se conservan al guardar. Solo se eliminan los que marques explicitamente.</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($attachments as $attachment): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:var(--bg-input);border:1px solid var(--border);border-radius:6px;padding:9px 11px;">
                            <span style="font-size:13px;color:var(--text-1);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= e($attachment['filename']) ?>
                                <?php if ((int)$attachment['is_image'] === 1): ?>
                                    <span style="font-size:11px;color:var(--text-3);">[imagen: <?= e($attachment['filename']) ?>]</span>
                                <?php endif; ?>
                            </span>
                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--red);flex-shrink:0;cursor:pointer;">
                                <input type="checkbox" name="delete_attachments[]" value="<?= $attachment['id'] ?>" style="accent-color:var(--red);">
                                Eliminar
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div>
            <label style="display:block;font-size:12px;color:var(--text-2);margin-bottom:5px;">Agregar mas adjuntos</label>
            <input type="file" name="attachments[]" id="support-attachments" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif"
                style="width:100%;background:var(--bg-input);border:1px solid var(--border-2);color:var(--text-2);border-radius:6px;padding:9px 11px;font-size:13px;">
            <p style="font-size:12px;color:var(--text-3);margin-top:6px;">Estos archivos se suman a los adjuntos actuales; no reemplazan lo ya subido.</p>
            <div id="selected-attachments" style="display:none;margin-top:8px;background:var(--bg-input);border:1px solid var(--border);border-radius:6px;padding:9px 11px;">
                <p style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:7px;">Archivos preparados para subir</p>
                <div id="selected-attachments-list" style="display:flex;flex-direction:column;gap:6px;"></div>
            </div>
            <p id="paste-status" style="font-size:12px;color:var(--green);margin-top:6px;display:none;"></p>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <a href="<?= base('soporte/articulo/' . e($article['slug'])) ?>"
                style="background:transparent;border:1px solid var(--border-2);color:var(--text-2);font-size:13px;padding:8px 14px;border-radius:6px;text-decoration:none;">Cancelar</a>
            <button type="submit"
                style="background:var(--accent-bg);border:1px solid var(--accent-brd);color:#fff;font-size:13px;font-weight:500;padding:8px 16px;border-radius:6px;cursor:pointer;">
                Guardar cambios
            </button>
        </div>
    </form>
</div>

<script>
    (function() {
        const body = document.getElementById('support-body');
        const editor = document.getElementById('support-editor');
        const input = document.getElementById('support-attachments');
        const status = document.getElementById('paste-status');
        const selectedBox = document.getElementById('selected-attachments');
        const selectedList = document.getElementById('selected-attachments-list');
        const form = body ? body.closest('form') : null;
        const existingImageMap = <?= json_encode($supportImageMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        if (!body || !editor) {
            return;
        }

        renderEditorFromText(body.value || '');

        if (form) {
            form.addEventListener('submit', event => {
                body.value = serializeEditor();

                if (body.value.trim() === '') {
                    event.preventDefault();
                    editor.focus();
                    if (status) {
                        status.textContent = 'Completa el contenido del articulo.';
                        status.style.display = 'block';
                        status.style.color = 'var(--red)';
                    }
                }
            });
        }

        if (!input || typeof DataTransfer === 'undefined') {
            return;
        }

        const files = new DataTransfer();

        input.addEventListener('change', () => {
            Array.from(input.files).forEach(file => addFile(file));
            input.files = files.files;
            renderSelectedFiles();
        });

        editor.addEventListener('paste', event => {
            const items = event.clipboardData ? Array.from(event.clipboardData.items) : [];
            const imageItems = items.filter(item => item.kind === 'file' && item.type.startsWith('image/'));

            if (imageItems.length === 0) {
                return;
            }

            event.preventDefault();

            imageItems.forEach(item => {
                const blob = item.getAsFile();

                if (!blob) {
                    return;
                }

                const extension = blob.type === 'image/jpeg' ? 'jpg' : (blob.type.split('/')[1] || 'png');
                const filename = 'imagen-' + Date.now() + '-' + Math.random().toString(16).slice(2, 6) + '.' + extension;
                const file = new File([blob], filename, { type: blob.type, lastModified: Date.now() });

                addFile(file);
                insertImageFigure(filename, URL.createObjectURL(file), true);
            });

            input.files = files.files;
            renderSelectedFiles();

            if (status) {
                status.textContent = 'Imagen pegada y agregada como adjunto.';
                status.style.display = 'block';
            }
        });

        function renderEditorFromText(value) {
            editor.innerHTML = '';
            const lines = String(value || '').split(/\r?\n/);

            if (lines.length === 0 || (lines.length === 1 && lines[0] === '')) {
                appendTextLine('');
                return;
            }

            lines.forEach(line => {
                const imageKey = imageMarkerKey(line);
                const imageUrl = imageKey ? existingImageMap[imageKey.toLowerCase()] : null;

                if (imageKey && imageUrl) {
                    editor.appendChild(createImageFigure(imageKey, imageUrl, false));
                    return;
                }

                appendTextLine(line);
            });
        }

        function appendTextLine(text) {
            const paragraph = document.createElement('div');
            paragraph.textContent = text;

            if (text === '') {
                paragraph.appendChild(document.createElement('br'));
            }

            editor.appendChild(paragraph);
        }

        function imageMarkerKey(line) {
            const match = String(line || '').trim().match(/^\[imagen:\s*(.+?)\]$/i);
            return match ? match[1].trim() : '';
        }

        function createImageFigure(filename, src, isNewFile) {
            const figure = document.createElement('figure');
            figure.dataset.marker = '[imagen: ' + filename + ']';
            if (isNewFile) {
                figure.dataset.newFile = filename;
            }
            figure.contentEditable = 'false';
            figure.style.margin = '12px 0';
            figure.style.background = 'var(--bg-surface)';
            figure.style.border = '1px solid var(--border)';
            figure.style.borderRadius = '8px';
            figure.style.padding = '10px';

            const image = document.createElement('img');
            image.src = src;
            image.alt = filename;
            image.style.maxWidth = '100%';
            image.style.borderRadius = '6px';
            image.style.display = 'block';

            const caption = document.createElement('figcaption');
            caption.textContent = filename;
            caption.style.fontSize = '11px';
            caption.style.color = 'var(--text-3)';
            caption.style.marginTop = '8px';

            figure.appendChild(image);
            figure.appendChild(caption);
            return figure;
        }

        function insertImageFigure(filename, src, isNewFile) {
            const figure = createImageFigure(filename, src, isNewFile);
            insertNodeAtCursor(figure);
        }

        function insertNodeAtCursor(node) {
            const selection = window.getSelection();
            let inserted = false;

            if (selection && selection.rangeCount > 0 && editor.contains(selection.anchorNode)) {
                const range = selection.getRangeAt(0);
                range.deleteContents();
                range.insertNode(node);
                inserted = true;

                const spacer = document.createElement('div');
                spacer.appendChild(document.createElement('br'));
                node.after(spacer);

                range.setStart(spacer, 0);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
            }

            if (!inserted) {
                editor.appendChild(node);
                appendTextLine('');
            }

            editor.focus();
        }

        function serializeEditor() {
            const lines = [];

            editor.childNodes.forEach(node => {
                collectEditorLines(node, lines);
            });

            return lines.join('\n').replace(/\n{3,}/g, '\n\n').trim();
        }

        function collectEditorLines(node, lines) {
            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.textContent || '';

                if (text.trim() !== '') {
                    lines.push(...text.split(/\r?\n/));
                }

                return;
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return;
            }

            if (node.matches('figure[data-marker]')) {
                lines.push(node.dataset.marker);
                return;
            }

            if (node.querySelector && node.querySelector('figure[data-marker]')) {
                node.childNodes.forEach(child => collectEditorLines(child, lines));
                return;
            }

            const text = node.innerText || node.textContent || '';

            if (text.trim() !== '') {
                lines.push(...text.split(/\r?\n/));
            }
        }

        function addFile(file) {
            const exists = Array.from(files.files).some(existing => {
                return existing.name === file.name
                    && existing.size === file.size
                    && existing.lastModified === file.lastModified;
            });

            if (!exists) {
                files.items.add(file);
            }
        }

        function removeFile(index) {
            const removedFile = Array.from(files.files)[index] || null;
            const nextFiles = new DataTransfer();

            Array.from(files.files).forEach((file, fileIndex) => {
                if (fileIndex !== index) {
                    nextFiles.items.add(file);
                }
            });

            files.items.clear();
            Array.from(nextFiles.files).forEach(file => files.items.add(file));
            input.files = files.files;

            if (removedFile) {
                editor.querySelectorAll('figure[data-new-file]').forEach(figure => {
                    if (figure.dataset.newFile === removedFile.name) {
                        figure.remove();
                    }
                });
            }

            renderSelectedFiles();
        }

        function renderSelectedFiles() {
            if (!selectedBox || !selectedList) return;

            selectedList.innerHTML = '';
            const selectedFiles = Array.from(files.files);
            selectedBox.style.display = selectedFiles.length ? 'block' : 'none';

            selectedFiles.forEach((file, index) => {
                const row = document.createElement('div');
                row.style.display = 'flex';
                row.style.alignItems = 'center';
                row.style.justifyContent = 'space-between';
                row.style.gap = '10px';

                const name = document.createElement('span');
                name.textContent = file.name;
                name.style.color = 'var(--text-1)';
                name.style.fontSize = '12px';
                name.style.overflow = 'hidden';
                name.style.textOverflow = 'ellipsis';
                name.style.whiteSpace = 'nowrap';

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.textContent = 'Quitar';
                remove.style.background = 'transparent';
                remove.style.border = '1px solid var(--border-2)';
                remove.style.color = 'var(--red)';
                remove.style.borderRadius = '6px';
                remove.style.padding = '3px 7px';
                remove.style.fontSize = '11px';
                remove.style.cursor = 'pointer';
                remove.addEventListener('click', () => removeFile(index));

                row.appendChild(name);
                row.appendChild(remove);
                selectedList.appendChild(row);
            });
        }

    })();
</script>

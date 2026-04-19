const getCookie = (name) => {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\/+^])/g, '\\$1') + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
};

document.addEventListener('DOMContentLoaded', () => {
    const noteIdEl    = document.getElementById('note-id');
    let currentId     = noteIdEl ? noteIdEl.value : null;
    const textarea    = document.getElementById('note-body');
    const saveBtn     = document.getElementById('btn-save');
    const downloadBtn = document.getElementById('btn-download');
    const pinBtn      = document.getElementById('btn-pin');
    const titleEl   = document.getElementById('editor-title');
    const previewEl = document.getElementById('note-preview');
    const baseTitle = currentId ? 'Edit Note' : 'New Note';
    const notekey   = getCookie('noteskey') || '';
    let isDirty      = false;
    let previewDirty = true;

    // ── Toast notifications ────────────────────────────────────────────────────
    const showToast = (message, type = 'success') => {
        const toastEl   = document.getElementById('editor-toast');
        const toastBody = document.getElementById('toast-body');
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(`text-bg-${type}`);
        toastBody.textContent = message;
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
    };

    // ── Load existing note ─────────────────────────────────────────────────────
    if (currentId) {
        fetch(`/note/${currentId}`, {
            headers: { notekey },
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.error) {
                showToast(data.error, 'danger');
                return;
            }
            textarea.value = data.body || '';
            if (parseInt(data.pinned, 10) === 1) {
                pinBtn.setAttribute('aria-pressed', 'true');
                pinBtn.classList.add('active');
            }
        })
        .catch(() => {
            showToast('Failed to load note.', 'danger');
        });
    } else {
        textarea.value = '# ';
        textarea.focus();
        textarea.setSelectionRange(2, 2);
    }

    // ── Dirty state tracking ───────────────────────────────────────────────────
    textarea.addEventListener('input', () => {
        previewDirty = true;
        if (!isDirty) {
            isDirty = true;
            titleEl.textContent = `${baseTitle} *`;
        }
    });

    // ── Copy buttons for code blocks ───────────────────────────────────────────
    const addCopyButtons = (container) => {
        container.querySelectorAll('pre').forEach((pre) => {
            const code = pre.querySelector('code');
            if (!code) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'note-editor__code-wrapper';
            pre.parentNode.insertBefore(wrapper, pre);
            wrapper.appendChild(pre);

            const btn = document.createElement('button');
            btn.className = 'note-editor__copy-btn';
            btn.setAttribute('type', 'button');
            btn.setAttribute('aria-label', 'Copy code');
            btn.innerHTML = '<i class="bi bi-clipboard" aria-hidden="true"></i>';
            wrapper.appendChild(btn);

            btn.addEventListener('click', () => {
                navigator.clipboard.writeText(code.textContent).then(() => {
                    btn.innerHTML = '<i class="bi bi-clipboard-check" aria-hidden="true"></i>';
                    btn.setAttribute('aria-label', 'Copied!');
                    setTimeout(() => {
                        btn.innerHTML = '<i class="bi bi-clipboard" aria-hidden="true"></i>';
                        btn.setAttribute('aria-label', 'Copy code');
                    }, 2000);
                }).catch(() => {
                    btn.innerHTML = '<i class="bi bi-x-circle" aria-hidden="true"></i>';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="bi bi-clipboard" aria-hidden="true"></i>';
                        btn.setAttribute('aria-label', 'Copy code');
                    }, 2000);
                });
            });
        });
    };

    // ── Preview tab ────────────────────────────────────────────────────────────
    const previewTab = document.getElementById('tab-preview');
    if (previewTab) {
        previewTab.addEventListener('shown.bs.tab', () => {
            if (!previewDirty) return;

            const markdown = textarea.value;
            if (markdown.trim() === '') {
                previewEl.innerHTML = '<p class="text-muted fst-italic">Nothing to preview.</p>';
                previewDirty = false;
                return;
            }

            previewEl.innerHTML = marked.parse(markdown);
            addCopyButtons(previewEl);
            previewDirty = false;
        });
    }

    // ── Save ───────────────────────────────────────────────────────────────────
    const saveNote = () => {
        const body   = textarea.value;
        const method = currentId ? 'PATCH' : 'POST';
        const url    = currentId ? `/note/${currentId}` : '/note';

        saveBtn.disabled  = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i><span class="d-none d-lg-inline"> Saving…</span>';

        fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                notekey,
            },
            body: JSON.stringify({ body }),
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.error) {
                showToast(data.error, 'danger');
                return;
            }
            currentId  = data.id;
            isDirty    = false;
            titleEl.textContent = 'Edit Note';
            history.replaceState({}, '', `/note/${currentId}/edit`);
            showToast('Note saved.');
        })
        .catch(() => {
            showToast('Error saving note. Try again.', 'danger');
        })
        .finally(() => {
            saveBtn.disabled  = false;
            saveBtn.innerHTML = '<i class="bi bi-floppy-fill"></i><span class="d-none d-lg-inline"> Save</span>';
        });
    };

    saveBtn.addEventListener('click', saveNote);

    // ── Pin toggle ─────────────────────────────────────────────────────────────
    pinBtn.addEventListener('click', () => {
        if (!currentId) return;

        const isPinned = pinBtn.getAttribute('aria-pressed') === 'true';
        const newValue = isPinned ? 0 : 1;

        pinBtn.disabled = true;

        fetch(`/note/${currentId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                notekey,
            },
            body: JSON.stringify({ pinned: newValue }),
        })
        .then((res) => res.json())
        .then((data) => {
            if (data.error) {
                showToast(data.error, 'danger');
                return;
            }
            pinBtn.setAttribute('aria-pressed', newValue ? 'true' : 'false');
            pinBtn.classList.toggle('active', !!newValue);
            showToast(newValue ? 'Note pinned.' : 'Note unpinned.');
        })
        .catch(() => {
            showToast('Failed to update pin. Try again.', 'danger');
        })
        .finally(() => {
            pinBtn.disabled = false;
        });
    });

    // ── Download ───────────────────────────────────────────────────────────────
    downloadBtn.addEventListener('click', () => {
        const content  = textarea.value;
        const filename = currentId ? `note-${currentId}.md` : 'note.md';
        const blob     = new Blob([content], { type: 'text/markdown' });
        const url      = URL.createObjectURL(blob);
        const a        = document.createElement('a');
        a.href         = url;
        a.download     = filename;
        a.click();
        URL.revokeObjectURL(url);
    });

    // ── Ctrl+S / Cmd+S ────────────────────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveNote();
        }
    });

    // ── Warn on unsaved changes ────────────────────────────────────────────────
    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
        }
    });

    // ── Revisions tab ──────────────────────────────────────────────────────────
    const revisionsTab  = document.getElementById('tab-revisions');
    const revisionsList = document.getElementById('revisions-list');

    const escHtml = (str) => str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const computeDiff = (oldText, newText) => {
        const a = oldText.split('\n');
        const b = newText.split('\n');
        const m = a.length;
        const n = b.length;
        const dp = Array.from({ length: m + 1 }, () => new Uint32Array(n + 1));
        for (let i = 1; i <= m; i++) {
            for (let j = 1; j <= n; j++) {
                dp[i][j] = a[i - 1] === b[j - 1]
                    ? dp[i - 1][j - 1] + 1
                    : Math.max(dp[i - 1][j], dp[i][j - 1]);
            }
        }
        const result = [];
        let i = m;
        let j = n;
        while (i > 0 || j > 0) {
            if (i > 0 && j > 0 && a[i - 1] === b[j - 1]) {
                result.push({ type: 'eq', line: a[i - 1] });
                i--; j--;
            } else if (j > 0 && (i === 0 || dp[i][j - 1] >= dp[i - 1][j])) {
                result.push({ type: 'add', line: b[j - 1] });
                j--;
            } else {
                result.push({ type: 'del', line: a[i - 1] });
                i--;
            }
        }
        return result.reverse();
    };

    const renderDiff = (diff) => diff.map(({ type, line }) => {
        const escaped = escHtml(line);
        if (type === 'del') return `<span class="revision-diff__line revision-diff__line--del">- ${escaped}</span>`;
        if (type === 'add') return `<span class="revision-diff__line revision-diff__line--add">+ ${escaped}</span>`;
        return `<span class="revision-diff__line">  ${escaped}</span>`;
    }).join('');

    if (revisionsTab && revisionsList) {
        revisionsTab.addEventListener('shown.bs.tab', () => {
            revisionsList.innerHTML = '<p class="text-muted fst-italic">Loading revisions&hellip;</p>';

            fetch(`/note/${currentId}/revisions`, {
                headers: { notekey },
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.error) {
                    revisionsList.innerHTML = `<p class="text-danger">${data.error}</p>`;
                    return;
                }
                if (!data.length) {
                    revisionsList.innerHTML = '<p class="text-muted fst-italic">No revisions found.</p>';
                    return;
                }

                const items = data.map((rev) => {
                    const date = new Date(rev.created_at).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' });
                    const title = rev.title || 'Untitled Note';
                    return `<div class="d-flex align-items-center justify-content-between gap-3 py-2 border-bottom">
                        <div>
                            <span class="fw-medium">${title}</span>
                            <span class="text-secondary small ms-2">${date}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-revision-id="${rev.id}" aria-label="View revision from ${date}">View</button>
                    </div>`;
                }).join('');

                revisionsList.innerHTML = items;
            })
            .catch(() => {
                revisionsList.innerHTML = '<p class="text-danger">Failed to load revisions.</p>';
            });
        });

        revisionsList.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-revision-id]');
                    if (!btn) return;

                    const revId = btn.dataset.revisionId;
                    btn.disabled = true;
                    btn.textContent = '…';

                    fetch(`/note/${currentId}/revision/${revId}`, {
                        headers: { notekey },
                    })
                    .then((res) => res.json())
                    .then((rev) => {
                        if (rev.error) {
                            showToast(rev.error, 'danger');
                            return;
                        }
                        const date = new Date(rev.created_at).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' });
                        document.getElementById('revision-modal-date').textContent = date;
                        document.getElementById('revision-modal-body').value = rev.body || '';
                        document.getElementById('btn-restore-revision').dataset.body = rev.body || '';

                        const diffContainer = document.getElementById('revision-modal-diff');
                        if (diffContainer) {
                            const diff = computeDiff(rev.body || '', textarea.value);
                            diffContainer.innerHTML = renderDiff(diff);
                        }

                        bootstrap.Tab.getOrCreateInstance(document.getElementById('revision-tab-text')).show();
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('revision-modal')).show();
                    })
                    .catch(() => {
                        showToast('Failed to load revision.', 'danger');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'View';
                    });
                });

        const restoreBtn = document.getElementById('btn-restore-revision');
        if (restoreBtn) {
            restoreBtn.addEventListener('click', () => {
                textarea.value = restoreBtn.dataset.body;
                isDirty      = true;
                previewDirty = true;
                titleEl.textContent = `${baseTitle} *`;
                bootstrap.Modal.getInstance(document.getElementById('revision-modal')).hide();
                bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-edit')).show();
                showToast('Revision restored. Remember to save.');
            });
        }
    }
});

<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom mb-4 pb-3 d-flex align-items-center justify-content-between gap-3">
        <h2 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
        <a href="/note/new" class="btn btn-primary"><i class="bi bi-plus-circle-fill me-1"></i> New Note</a>
    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 bg-body-tertiary h-100 dashboard-stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="dashboard-stat-card__icon text-primary">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <div class="dashboard-stat-card__value"><?= esc($stats['total_notes']) ?></div>
                        <div class="dashboard-stat-card__label text-secondary">Total Notes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 bg-body-tertiary h-100 dashboard-stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="dashboard-stat-card__icon text-warning">
                        <i class="bi bi-pin-angle-fill"></i>
                    </div>
                    <div>
                        <div class="dashboard-stat-card__value"><?= esc($stats['pinned_notes']) ?></div>
                        <div class="dashboard-stat-card__label text-secondary">Pinned Notes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 bg-body-tertiary h-100 dashboard-stat-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="dashboard-stat-card__icon text-success">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="dashboard-stat-card__value"><?= esc($stats['total_revisions']) ?></div>
                        <div class="dashboard-stat-card__label text-secondary">Total Revisions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content row -->
    <div class="row g-4">

        <!-- Recent notes -->
        <div class="col-lg-8">
            <div class="card border-0 bg-body-tertiary h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Recent Notes</h5>
                    <a href="/" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_notes)): ?>
                    <p class="text-secondary p-4 mb-0">No notes yet. <a href="/note/new">Create your first note.</a></p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 text-secondary fw-normal small">#</th>
                                    <th class="text-secondary fw-normal small">Note ID</th>
                                    <th class="text-secondary fw-normal small">Hash</th>
                                    <th class="text-secondary fw-normal small">Pinned</th>
                                    <th class="text-secondary fw-normal small">Updated</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recent_notes as $note): ?>
                                <tr>
                                    <td class="ps-4 text-secondary small"><?= esc($note['id']) ?></td>
                                    <td><code class="text-info small"><?= esc($note['note_id']) ?></code></td>
                                    <td><code class="text-secondary small"><?= esc(substr($note['hash'], 0, 8)) ?>…</code></td>
                                    <td>
                                        <?php if ($note['pinned']): ?>
                                        <i class="bi bi-pin-fill text-warning" aria-label="Pinned"></i>
                                        <?php else: ?>
                                        <span class="text-body-tertiary">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-secondary small"><?= esc($note['updated_at']) ?></td>
                                    <td class="text-end pe-3">
                                        <a href="/note/<?= esc($note['id']) ?>/edit" class="btn btn-sm btn-outline-secondary" aria-label="Edit note <?= esc($note['id']) ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Side column -->
        <div class="col-lg-4 d-flex flex-column gap-4">

            <!-- Quick Actions -->
            <div class="card border-0 bg-body-tertiary">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Quick Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="/note/new" class="btn btn-outline-primary text-start">
                        <i class="bi bi-plus-circle-fill me-2"></i> New Note
                    </a>
                    <a href="/admin/notes/key" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-key-fill me-2"></i> Set Encryption Key
                    </a>
                    <a href="/admin/export" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-download me-2"></i> Export Notes
                    </a>
                    <a href="/admin/import" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-upload me-2"></i> Import Notes
                    </a>
                    <a href="<?= config('Urls')->logs ?>admin?search=<?= urlencode($_SERVER['HTTP_HOST'] ?? 'unknown') ?>" target="_blank" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-journal-text me-2"></i> Event Log
                    </a>
                    <a href="/debug" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-bug-fill me-2"></i> Debug
                    </a>
                </div>
            </div>

            <!-- System Info -->
            <div class="card border-0 bg-body-tertiary">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0"><i class="bi bi-info-circle-fill me-2 text-info"></i>System Info</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary small">PHP</span>
                            <span class="badge text-bg-secondary font-monospace"><?= esc(phpversion()) ?></span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary small">CodeIgniter</span>
                            <span class="badge text-bg-secondary font-monospace"><?= esc(\CodeIgniter\CodeIgniter::CI_VERSION) ?></span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary small">Hostname</span>
                            <span class="text-body-secondary small font-monospace text-truncate" style="max-width:12rem;" title="<?= esc(gethostname()) ?>"><?= esc(gethostname()) ?></span>
                        </li>
                        <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center gap-3">
                            <span class="text-secondary small">Server Time</span>
                            <span class="text-body-secondary small font-monospace"><?= esc(date('Y-m-d H:i:s')) ?></span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</div>
<?= $this->endSection() ?>
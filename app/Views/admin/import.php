<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom mb-4 pb-3 d-flex align-items-center justify-content-between gap-3">
        <h2 class="mb-0"><i class="bi bi-upload me-2"></i>Import Notes</h2>
        <a href="/admin" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif ?>

    <?php if (isset($imported)): ?>
        <div class="alert <?= $imported > 0 ? 'alert-success' : 'alert-warning' ?> mb-4" role="alert">
            <h5 class="alert-heading mb-1">Import complete</h5>
            <p class="mb-0">
                <strong><?= esc($imported) ?></strong> of <strong><?= esc($total) ?></strong> note<?= $total !== 1 ? 's' : '' ?> imported.
                <?php if ($skipped > 0): ?>
                    <strong><?= esc($skipped) ?></strong> skipped (already exist or invalid).
                <?php endif ?>
            </p>
        </div>

        <?php if (! empty($errors)): ?>
            <div class="card border-0 bg-body-tertiary mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Import Errors</h6>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($errors as $error): ?>
                        <li class="list-group-item bg-transparent text-secondary small"><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>
    <?php endif ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 bg-body-tertiary">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1">Upload JSON File</h5>
                    <p class="text-secondary small mb-4">
                        Upload a <code>.json</code> file containing an array of note objects. Each object must include
                        <code>note_id</code>, <code>title</code>, and <code>body</code>. Records with an existing
                        <code>note_id</code> will be skipped.
                    </p>

                    <form action="/admin/import" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" id="notekey" name="notekey">
                        <div class="mb-4">
                            <label for="import_file" class="form-label">JSON file</label>
                            <input
                                type="file"
                                class="form-control"
                                id="import_file"
                                name="import_file"
                                accept=".json,application/json"
                                required
                            >
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="card border-0 bg-body-tertiary">
                <div class="card-body p-4">
                    <h6 class="card-title mb-3"><i class="bi bi-info-circle me-1"></i> Expected JSON format</h6>
                    <pre class="bg-body rounded p-3 small text-secondary mb-0" style="white-space: pre-wrap; word-break: break-all;">[
  {
    "note_id": "abc123",
    "hash": "optional-hash",
    "title": "Note title",
    "body": "Note body content",
    "pinned": 0,
    "created_at": "2025-01-01 12:00:00",
    "updated_at": "2025-01-01 12:00:00"
  }
]</pre>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

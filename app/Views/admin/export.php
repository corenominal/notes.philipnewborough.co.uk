<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom mb-4 pb-3 d-flex align-items-center justify-content-between gap-3">
        <h2 class="mb-0"><i class="bi bi-download me-2"></i>Export Notes</h2>
        <a href="/admin" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
    </div>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 bg-body-tertiary">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1">Download JSON Export</h5>
                    <p class="text-secondary small mb-4">
                        All <strong><?= esc($note_count) ?></strong> note<?= $note_count !== 1 ? 's' : '' ?> will be decrypted and exported as a
                        <code>.json</code> file. Your encryption key is required to decrypt the notes before export.
                    </p>

                    <form action="/admin/export" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" id="notekey" name="notekey">
                        <button type="submit" class="btn btn-primary" <?= $note_count === 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-download me-1"></i> Export Notes
                        </button>
                        <?php if ($note_count === 0): ?>
                            <p class="text-secondary small mt-3 mb-0">There are no notes to export.</p>
                        <?php endif ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="card border-0 bg-body-tertiary">
                <div class="card-body p-4">
                    <h6 class="card-title mb-3"><i class="bi bi-info-circle me-1"></i> Exported JSON format</h6>
                    <pre class="bg-body rounded p-3 small text-secondary mb-0" style="white-space: pre-wrap; word-break: break-all;">[
  {
    "note_id": "abc123",
    "hash": "sha1-hash",
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

<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="border-bottom border-1 mb-4 pb-4">
                <h2 class="mb-0"><i class="bi bi-key-fill me-2"></i>Notes - Set Encryption Key</h2>
            </div>

            <p class="text-secondary">Enter your encryption key below. The key will be stored in a cookie in your browser and sent with every request to encrypt and decrypt your notes.</p>

            <div id="notes-key-status" class="mb-3"></div>

            <form id="notes-key-form" novalidate>
                <div class="mb-4">
                    <label for="notes-key-input" class="form-label">Encryption Key</label>
                    <input type="password" class="form-control" id="notes-key-input" placeholder="Enter your encryption key" autocomplete="off" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-floppy-fill me-1"></i> Save Key</button>
                    <button type="button" class="btn btn-outline-danger" id="btn-clear-key"><i class="bi bi-trash3-fill me-1"></i> Clear Key</button>
                </div>
            </form>

        </div>
    </div>
</div>
<?= $this->endSection() ?>

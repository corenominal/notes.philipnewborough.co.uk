<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="border-bottom border-1 mb-4 pb-4 d-flex align-items-center justify-content-between gap-3">
                <h2 class="mb-0">Notes</h2>
                <div class="btn-group" role="group" aria-label="Page actions">
                    <button type="button" class="btn btn-outline-primary" id="btn-new-note"><i class="bi bi-plus-circle-fill"></i><span class="d-none d-lg-inline"> New</span></button>
                    <button type="button" class="btn btn-outline-primary" id="btn-refresh"><i class="bi bi-arrow-clockwise"></i><span class="d-none d-lg-inline"> Refresh</span></button>
                </div>
            </div>

            <div class="mb-3">
                <input type="search" id="notes-search" class="form-control" placeholder="Search notes..." aria-label="Search notes">
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Note</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="notes-tbody">
                        <tr><td colspan="2" class="text-center text-muted">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <nav id="notes-pagination" aria-label="Notes pagination"></nav>

        </div>
    </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="modal-delete-note" tabindex="-1" aria-labelledby="modal-delete-note-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-delete-note-label">Delete Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this note? This cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

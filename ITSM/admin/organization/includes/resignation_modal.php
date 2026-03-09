       <!-- Resignation Modal -->
        <?php if (!empty($resign_success)): ?>
            <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
                User marked as resigned successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="modal fade" id="resignModal" tabindex="-1" aria-labelledby="resignModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resignModalLabel">Mark User as Resigned</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" id="resignUserId" name="user_id">
                            <div class="mb-3">
                                <label for="resignDate" class="form-label">Resignation Date *</label>
                                <input type="date" class="form-control" id="resignDate" name="date_resigned" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <p class="text-muted">This will update the user's status to resigned.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Resignation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
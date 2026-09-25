<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

$request_id = (int)($_GET['request_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT
        r.*,
        t.ticket_number
    FROM request_tb r
    LEFT JOIN ticket_tb t
        ON t.ticket_id = r.ticket_id
    WHERE r.request_id = ?
");

$stmt->bind_param("i", $request_id);
$stmt->execute();

$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    die("Request not found.");
}
// Fetch recommendations for this request
$recStmt = $conn->prepare("
    SELECT
        rr.*,
        u.fullname
    FROM request_recommendations rr
    LEFT JOIN user_tb u
        ON rr.recommended_by = u.user_id
    WHERE rr.request_id = ?
    ORDER BY rr.created_at DESC
");

$recStmt->bind_param("i", $request_id);
$recStmt->execute();

$recommendations = $recStmt->get_result();
?>

<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between text-white">
                <h6>
                    Request Details
                </h6>
            <div>
                    <a href="?page=ticket/requests"
                    class="btn btn-secondary btn-sm">
                        Back
                    </a>

                    <a href="?page=ticket/crud/edit_request&id=<?= $request['request_id'] ?>"
                    class="btn btn-primary btn-sm">
                        Edit
                    </a>

                    <a href="?page=ticket/print/print_request&lmr_no=<?= urlencode($request['lmr_no']) ?>"
                    target="_blank"
                    class="btn btn-success btn-sm">
                        Print
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>LMR No</strong></div>
                    <div class="col-md-6"><?= htmlspecialchars($request['lmr_no']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Requestor</strong></div>
                    <div class="col-md-6"><?= htmlspecialchars($request['requestor']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Department</strong></div>
                    <div class="col-md-6"><?= htmlspecialchars($request['department']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Ticket</strong></div>
                    <div class="col-md-6">
                        <a href="?page=ticket/view_ticket&ticket_id=<?= $request['ticket_id'] ?>">
                            <?= htmlspecialchars($request['ticket_number']) ?>
                        </a>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Item</strong></div>
                    <div class="col-md-6"><?= htmlspecialchars($request['item']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Description</strong></div>
                    <div class="col-md-6"><?= nl2br(htmlspecialchars($request['description'])) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Remarks</strong></div>
                    <div class="col-md-6"><?= nl2br(htmlspecialchars($request['remarks'])) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Quantity</strong></div>
                    <div class="col-md-6"><?= $request['quantity'] ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>UoM</strong></div>
                    <div class="col-md-6"><?= htmlspecialchars($request['UoM']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Status</strong></div>
                    <div class="col-md-6">
                        <span class="badge bg-primary">
                            <?= htmlspecialchars($request['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Date Needed</strong></div>
                    <div class="col-md-6"><?= $request['date_needed'] ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Date Created</strong></div>
                    <div class="col-md-6"><?= $request['date_created'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-lightbulb"></i>
                Recommendations
            </div>
            <?php if($recommendations->num_rows): ?>

                    <?php while($rec = $recommendations->fetch_assoc()): ?>

                        <div class="border rounded p-3 mb-3">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>
                                    <div class="fw-bold">
                                        <?= htmlspecialchars($rec['fullname']) ?>
                                    </div>

                                    <small class="text-muted">
                                        <?= date('M d, Y h:i A', strtotime($rec['created_at'])) ?>
                                    </small>
                                </div>

                                <a href="?page=ticket/crud/delete_recommendation&id=<?= $rec['recommendation_id'] ?>&request_id=<?= $request_id ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this recommendation?');">
                                    <i class="fas fa-trash"></i>
                                </a>

                            </div>

                            <div class="mt-2">
                                <?= nl2br(htmlspecialchars($rec['recommendation'])) ?>
                            </div>

                        </div>
                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="text-center p-2 text-align: center;">
                        No recommendations yet.
                    </div>

                <?php endif; ?>
            <div class="card-body">
                <form action="?page=ticket/crud/save_recommendation" method="POST">

                    <input type="hidden"
                        name="request_id"
                        value="<?= $request_id ?>">

                    <div class="mb-2">
                        <textarea
                            class="form-control"
                            name="recommendation"
                            rows="3"
                            placeholder="Enter recommendation..."
                            required></textarea>
                    </div>

                    <button class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Send
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>


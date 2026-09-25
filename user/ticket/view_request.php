<?php
include 'includes/auth.php';
include 'includes/db.php';

$request_id = (int)($_GET['request_id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Fetch request (only allow owner to view)
$stmt = $conn->prepare("
    SELECT
        r.*,
        t.ticket_number
    FROM request_tb r
    LEFT JOIN ticket_tb t
        ON t.ticket_id = r.ticket_id
    WHERE r.request_id = ?
    AND r.user_id = ?
");

$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();

$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    die("Request not found.");
}

// Fetch recommendations
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

    <!-- Request Details -->
    <div class="col-lg-8">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center text-white">
                <h6 class="mb-0">Request Details</h6>

                <div>
                    <a href="?page=ticket/requests"
                       class="btn btn-secondary btn-sm">
                        Back
                    </a>

                    <?php if(strtolower($request['status']) == 'proceed request'): ?>
                        <a href="?page=ticket/includes/print_request&lmr_no=<?= urlencode($request['lmr_no']) ?>"
                           target="_blank"
                           class="btn btn-success btn-sm">
                            <i class="fas fa-print"></i> Print
                        </a>
                    <?php endif; ?>
                </div>

            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-4"><strong>LMR No</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['lmr_no']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Requestor</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['requestor']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Department</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['department']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Ticket</strong></div>
                    <div class="col-md-8">
                        <?php if(!empty($request['ticket_id'])): ?>
                            <a href="?page=ticket/view_ticket&ticket_id=<?= $request['ticket_id'] ?>">
                                <?= htmlspecialchars($request['ticket_number']) ?>
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Item</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['item']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Description</strong></div>
                    <div class="col-md-8"><?= nl2br(htmlspecialchars($request['description'])) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Remarks</strong></div>
                    <div class="col-md-8"><?= nl2br(htmlspecialchars($request['remarks'])) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Quantity</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['quantity']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>UoM</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['UoM']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Status</strong></div>
                    <div class="col-md-8">
                        <span class="badge bg-primary">
                            <?= htmlspecialchars($request['status']) ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Date Needed</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['date_needed']) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Date Created</strong></div>
                    <div class="col-md-8"><?= htmlspecialchars($request['date_created']) ?></div>
                </div>

            </div>

        </div>

    </div>

    <!-- Recommendations -->
    <div class="col-lg-4">

        <div class="card">

            <div class="card-header bg-primary text-white">
                <i class="fas fa-lightbulb"></i>
                IT Recommendations
            </div>

            <div class="card-body">

                <?php if($recommendations && $recommendations->num_rows > 0): ?>

                    <?php while($rec = $recommendations->fetch_assoc()): ?>

                        <div class="border rounded p-3 mb-3">

                            <div class="fw-bold">
                                <?= htmlspecialchars($rec['fullname']) ?>
                            </div>

                            <small class="text-muted">
                                <?= date('M d, Y h:i A', strtotime($rec['created_at'])) ?>
                            </small>

                            <div class="mt-2">
                                <?= nl2br(htmlspecialchars($rec['recommendation'])) ?>
                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="text-center text-muted py-4">
                        No recommendations yet.
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>
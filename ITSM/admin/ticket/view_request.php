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
?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between text-white">
        <h4>
             Request Details
        </h4>
       <div>
            <a href="?page=ticket/requests"
            class="btn btn-secondary">
                Back
            </a>

            <a href="?page=ticket/crud/edit_request&id=<?= $request['request_id'] ?>"
            class="btn btn-primary">
                Edit
            </a>

            <a href="?page=ticket/print/print_request&lmr_no=<?= urlencode($request['lmr_no']) ?>"
            target="_blank"
            class="btn btn-success">
                Print
            </a>
        </div>
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-2"><strong>LMR No</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($request['lmr_no']) ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Requestor</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($request['requestor']) ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Department</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($request['department']) ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Ticket</strong></div>
            <div class="col-md-6">
                <a href="?page=ticket/view_ticket&ticket_id=<?= $request['ticket_id'] ?>">
                    <?= htmlspecialchars($request['ticket_number']) ?>
                </a>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Item</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($request['item']) ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Description</strong></div>
            <div class="col-md-6"><?= nl2br(htmlspecialchars($request['description'])) ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Quantity</strong></div>
            <div class="col-md-6"><?= $request['quantity'] ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>UoM</strong></div>
            <div class="col-md-6"><?= htmlspecialchars($request['UoM']) ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Status</strong></div>
            <div class="col-md-6">
                <span class="badge bg-primary">
                    <?= htmlspecialchars($request['status']) ?>
                </span>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Date Needed</strong></div>
            <div class="col-md-6"><?= $request['date_needed'] ?></div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2"><strong>Date Created</strong></div>
            <div class="col-md-6"><?= $request['date_created'] ?></div>
        </div>
    </div>
</div>
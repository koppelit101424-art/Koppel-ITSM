<?php

include 'includes/auth.php';
include 'includes/db.php';

$request_id = (int)($_GET['request_id'] ?? 0);

if ($request_id <= 0) {
    echo '<div class="alert alert-danger">Invalid request ID.</div>';
    exit;
}


/*
|--------------------------------------------------------------------------
| STEP 1: GET LMR NUMBER FROM SELECTED REQUEST
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT lmr_no
    FROM purch_request_tb
    WHERE request_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();
$selectedRequest = $result->fetch_assoc();

$stmt->close();

if (!$selectedRequest) {
    echo '<div class="alert alert-danger">Request not found.</div>';
    exit;
}

$lmr_no = trim($selectedRequest['lmr_no']);


/*
|--------------------------------------------------------------------------
| STEP 2: GET ALL REQUESTS WITH THE SAME LMR NO
|--------------------------------------------------------------------------
|
| One LMR can contain multiple items.
|
*/

$stmt = $conn->prepare("
    SELECT
        r.request_id,
        r.lmr_no,
        r.user_id,
        r.requestor,
        u.fullname,
        u.company,
        r.department,
        r.item,
        r.description,
        r.quantity,
        r.UoM,
        r.date_needed,
        r.remarks,
        r.status,
        r.priority,
        r.po_no,
        r.date_created,
        r.date_updated,
        r.purchaser_id,
        p.fullname AS purchaser_name
    FROM purch_request_tb r

    LEFT JOIN user_tb u
        ON r.user_id = u.user_id

    LEFT JOIN user_tb p
        ON r.purchaser_id = p.user_id

    WHERE r.lmr_no = ?

    ORDER BY r.request_id ASC
");

$stmt->bind_param("s", $lmr_no);
$stmt->execute();

$result = $stmt->get_result();

$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$stmt->close();

if (empty($requests)) {
    echo '<div class="alert alert-danger">Request not found.</div>';
    exit;
}


/*
|--------------------------------------------------------------------------
| USE FIRST REQUEST FOR GENERAL INFORMATION
|--------------------------------------------------------------------------
*/

$request = $requests[0];


/*
|--------------------------------------------------------------------------
| STEP 3: COLLECT ALL REQUEST IDS UNDER THIS LMR
|--------------------------------------------------------------------------
*/

$requestIds = [];

foreach ($requests as $row) {
    $requestIds[] = (int)$row['request_id'];
}


/*
|--------------------------------------------------------------------------
| STEP 4: GET ALL ATTACHMENTS FOR THIS LMR
|--------------------------------------------------------------------------
|
| Attachments are currently stored against the first request_id.
| However, this query checks ALL request IDs under the same LMR.
|
*/

$attachments = [];

if (!empty($requestIds)) {

    $placeholders = implode(
        ',',
        array_fill(0, count($requestIds), '?')
    );

    $types = str_repeat('i', count($requestIds));

    $sql = "
        SELECT
            attachment_id,
            request_id,
            file_name,
            file_path
        FROM purch_request_attachments
        WHERE request_id IN ($placeholders)
        ORDER BY attachment_id ASC
    ";

    $attachStmt = $conn->prepare($sql);

    $attachStmt->bind_param(
        $types,
        ...$requestIds
    );

    $attachStmt->execute();

    $attachResult = $attachStmt->get_result();

    while ($attachment = $attachResult->fetch_assoc()) {
        $attachments[] = $attachment;
    }

    $attachStmt->close();
}

?>


<style>

.request-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 2px;
}

.request-value {
    font-size: 0.95rem;
    color: #212529;
    margin-bottom: 15px;
    word-break: break-word;
}

.item-card {
    border-left: 3px solid #0d6efd;
    margin-bottom: 8px !important;
}

.item-card .card-body {
    padding: 10px 12px;
}

.item-card .request-label {
    font-size: 0.72rem;
    margin-bottom: 1px;
}

.item-card .request-value {
    font-size: 0.88rem;
    margin-bottom: 8px;
    line-height: 1.3;
}

.item-card .row {
    --bs-gutter-x: 0.75rem;
    --bs-gutter-y: 0;
}
.item-card {
    border-left: 3px solid #0d6efd;
    border-radius: 6px;
    transition: all 0.15s ease-in-out;
}

.item-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.item-card .card-body {
    padding: 12px 14px;
}

.item-card .request-label {
    font-size: 0.70rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 1px;
}

.item-card .request-value {
    font-size: 0.85rem;
    color: #212529;
    margin-bottom: 7px;
    line-height: 1.3;
    word-break: break-word;
}

.item-card .badge {
    font-size: 0.70rem;
}

.attachment-card {
    transition: all 0.15s ease-in-out;
}

.attachment-card:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
}

.activity-empty {
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    text-align: center;
}

</style>


<div class="card">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="card-header d-flex justify-content-between align-items-center text-white">

        <span>
            <i class="fas fa-file-alt me-2"></i>
            Purchasing Request
        </span>

        <a href="?page=ticket/purch_lmr"
           class="btn btn-secondary btn-sm">

            <i class="fas fa-arrow-left me-1"></i>
            Back to Requests

        </a>

    </div>


    <!-- =====================================================
         MAIN BODY
    ====================================================== -->

    <div class="card-body">

        <div class="row g-4">


            <!-- =================================================
                 LEFT CARD
            ================================================== -->

            <div class="col-lg-7">

                <div class="card h-100 shadow-sm">

                    <div class="card-header bg-light">

                        <strong>
                            <i class="fas fa-info-circle me-2"></i>
                            Request Details
                        </strong>

                    </div>


                    <div class="card-body">


                        <!-- =====================================
                             GENERAL INFORMATION
                        ====================================== -->

                        <div class="row">

                            <!-- LMR -->
                            <div class="col-md-6">

                                <div class="request-label">
                                    LMR No.
                                </div>

                                <div class="request-value fw-bold">
                                    <?= htmlspecialchars($lmr_no) ?>
                                </div>

                            </div>


                            <!-- REQUESTOR -->
                            <div class="col-md-6">

                                <div class="request-label">
                                    Requestor
                                </div>

                                <div class="request-value">
                                    <?= htmlspecialchars(
                                        $request['fullname']
                                        ?: $request['requestor']
                                        ?: ''
                                    ) ?>
                                </div>

                            </div>


                            <!-- COMPANY -->
                            <div class="col-md-6">

                                <div class="request-label">
                                    Company
                                </div>

                                <div class="request-value">
                                    <?= htmlspecialchars(
                                        $request['company'] ?? ''
                                    ) ?>
                                </div>

                            </div>


                            <!-- DEPARTMENT -->
                            <div class="col-md-6">

                                <div class="request-label">
                                    Department
                                </div>

                                <div class="request-value">
                                    <?= htmlspecialchars(
                                        $request['department'] ?? ''
                                    ) ?>
                                </div>

                            </div>


                            <!-- PURCHASER -->
                            <div class="col-md-6">

                                <div class="request-label">
                                    Assigned Purchaser
                                </div>

                                <div class="request-value">

                                    <?= htmlspecialchars(
                                        $request['purchaser_name']
                                        ?: 'Unassigned'
                                    ) ?>

                                </div>

                            </div>


                            <!-- STATUS -->
                            <div class="col-md-3">

                                <div class="request-label">
                                    Status
                                </div>

                                <div class="request-value">

                                    <?php
                                    $status =
                                        strtolower(
                                            trim(
                                                $request['status'] ?? ''
                                            )
                                        );
                                    ?>

                                    <span class="badge
                                        <?php
                                        if ($status === 'approved') {
                                            echo 'bg-success';
                                        } elseif ($status === 'rejected') {
                                            echo 'bg-danger';
                                        } elseif ($status === 'pending') {
                                            echo 'bg-warning text-dark';
                                        } else {
                                            echo 'bg-secondary';
                                        }
                                        ?>
                                    ">

                                        <?= htmlspecialchars(
                                            ucfirst($status)
                                        ) ?>

                                    </span>

                                </div>

                            </div>


                            <!-- PRIORITY -->
                            <div class="col-md-3">

                                <div class="request-label">
                                    Priority
                                </div>

                                <div class="request-value">

                                    <?php
                                    $priority =
                                        strtolower(
                                            trim(
                                                $request['priority'] ?? ''
                                            )
                                        );

                                    $priorityClass = 'bg-secondary';

                                    if ($priority === 'urgent') {
                                        $priorityClass = 'bg-danger';
                                    } elseif ($priority === 'high') {
                                        $priorityClass = 'bg-warning text-dark';
                                    } elseif ($priority === 'medium') {
                                        $priorityClass = 'bg-info text-dark';
                                    }
                                    ?>

                                    <span class="badge <?= $priorityClass ?>">

                                        <?= htmlspecialchars(
                                            ucfirst($priority)
                                        ) ?>

                                    </span>

                                </div>

                            </div>


                            <!-- PO NUMBER -->
                            <div class="col-md-6">

                                <div class="request-label">
                                    PO Number
                                </div>

                                <div class="request-value">

                                    <?= !empty($request['po_no'])
                                        ? htmlspecialchars($request['po_no'])
                                        : '<span class="text-muted">Not yet assigned</span>'
                                    ?>

                                </div>

                            </div>


                            <!-- DATE CREATED -->
                            <div class="col-md-6">

                                <div class="request-label">
                                    Date Created
                                </div>

                                <div class="request-value">

                                    <?= htmlspecialchars(
                                        $request['date_created'] ?? ''
                                    ) ?>

                                </div>

                            </div>

                        </div>


                        <hr>


                        <!-- =====================================
                             ITEMS
                        ====================================== -->

                        <!-- Requested Items -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-boxes me-2"></i>
                                    Requested Items
                                    <span class="badge bg-secondary">
                                        <?= count($requests) ?>
                                    </span>
                                </h5>
                            </div>

                            <div class="card-body">

                                <?php if (!empty($requests)): ?>

                                    <div class="row g-3" id="itemsContainer">

                                        <?php foreach ($requests as $index => $item): ?>

                                            <?php
                                            // Show first 4 items by default
                                            $isHidden = $index >= 4;
                                            ?>

                                            <div class="col-md-6 item-wrapper <?= $isHidden ? 'd-none extra-item' : '' ?>">

                                                <div class="card item-card h-100">

                                                    <div class="card-body">

                                                        <!-- Item Header -->
                                                        <div class="d-flex justify-content-between align-items-start mb-2">

                                                            <div class="fw-semibold text-dark">
                                                                <?= htmlspecialchars($item['item']) ?>
                                                            </div>

                                                            <span class="badge bg-light text-secondary">
                                                                #<?= $index + 1 ?>
                                                            </span>

                                                        </div>

                                                        <!-- Quantity / UoM -->
                                                        <div class="row">

                                                            <div class="col-6">
                                                                <div class="request-label">Quantity</div>
                                                                <div class="request-value">
                                                                    <?= htmlspecialchars($item['quantity']) ?>
                                                                </div>
                                                            </div>

                                                            <div class="col-6">
                                                                <div class="request-label">UoM</div>
                                                                <div class="request-value">
                                                                    <?= htmlspecialchars($item['UoM']) ?>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <!-- Description -->
                                                        <?php if (!empty($item['description'])): ?>

                                                            <div class="request-label">
                                                                Description
                                                            </div>

                                                            <div class="request-value">
                                                                <?= nl2br(htmlspecialchars($item['description'])) ?>
                                                            </div>

                                                        <?php endif; ?>

                                                        <!-- Date Needed -->
                                                        <div class="request-label">
                                                            Date Needed
                                                        </div>

                                                        <div class="request-value">
                                                            <?= !empty($item['date_needed'])
                                                                ? date('M d, Y', strtotime($item['date_needed']))
                                                                : '-'
                                                            ?>
                                                        </div>

                                                        <!-- Remarks -->
                                                        <?php if (!empty($item['remarks'])): ?>

                                                            <div class="request-label">
                                                                Remarks
                                                            </div>

                                                            <div class="request-value">
                                                                <?= nl2br(htmlspecialchars($item['remarks'])) ?>
                                                            </div>

                                                        <?php endif; ?>

                                                        <!-- Status / Priority -->
                                                        <div class="d-flex gap-2 flex-wrap">

                                                            <?php
                                                            $status = strtolower(trim($item['status'] ?? ''));
                                                            $priority = strtolower(trim($item['priority'] ?? ''));

                                                            $statusClass = match ($status) {
                                                                'pending' => 'bg-warning text-dark',
                                                                'approved' => 'bg-success',
                                                                'rejected' => 'bg-danger',
                                                                'completed' => 'bg-primary',
                                                                default => 'bg-secondary'
                                                            };

                                                            $priorityClass = match ($priority) {
                                                                'urgent' => 'bg-danger',
                                                                'high' => 'bg-warning text-dark',
                                                                'medium' => 'bg-info text-dark',
                                                                default => 'bg-secondary'
                                                            };
                                                            ?>

                                                            <span class="badge <?= $statusClass ?>">
                                                                <?= htmlspecialchars(ucfirst($status ?: 'N/A')) ?>
                                                            </span>

                                                            <span class="badge <?= $priorityClass ?>">
                                                                <?= htmlspecialchars(ucfirst($priority ?: 'N/A')) ?>
                                                            </span>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>

                                    <?php if (count($requests) > 4): ?>

                                        <div class="text-center mt-3">

                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                id="showAllItemsBtn"
                                            >
                                                <i class="fas fa-chevron-down me-1"></i>
                                                Show All Items
                                            </button>

                                        </div>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <div class="text-muted text-center py-4">
                                        No requested items found.
                                    </div>

                                <?php endif; ?>

                            </div>
                        </div>


                        <!-- =====================================
                             ATTACHMENTS
                        ====================================== -->

                        <hr class="mt-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <h6 class="mb-0 fw-bold">

                                <i class="fas fa-paperclip me-2"></i>

                                Attachments

                            </h6>

                            <span class="badge bg-secondary">

                                <?= count($attachments) ?>

                            </span>

                        </div>


                        <?php if (empty($attachments)): ?>

                            <div class="text-muted small">

                                <i class="fas fa-info-circle me-1"></i>

                                No attachments for this LMR.

                            </div>

                        <?php else: ?>

                            <div class="row">

                                <?php foreach ($attachments as $attachment): ?>

                                    <?php

                                    $fileName =
                                        $attachment['file_name'];

                                    $extension =
                                        strtolower(
                                            pathinfo(
                                                $fileName,
                                                PATHINFO_EXTENSION
                                            )
                                        );

                                    switch ($extension) {

                                        case 'jpg':
                                        case 'jpeg':
                                        case 'png':

                                            $icon =
                                                'fa-file-image';

                                            $iconClass =
                                                'text-success';

                                            break;

                                        case 'pdf':

                                            $icon =
                                                'fa-file-pdf';

                                            $iconClass =
                                                'text-danger';

                                            break;

                                        case 'doc':
                                        case 'docx':

                                            $icon =
                                                'fa-file-word';

                                            $iconClass =
                                                'text-primary';

                                            break;

                                        default:

                                            $icon =
                                                'fa-file';

                                            $iconClass =
                                                'text-secondary';
                                    }

                                    ?>

                                    <div class="col-md-6 mb-2">
                                            <a
                                                href="ticket/preview_purch_attachment.php?attachment_id=<?= (int)$attachment['attachment_id'] ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-decoration-none"
                                            >
                                            <div class="card attachment-card">

                                                <div class="card-body py-2">

                                                    <div class="d-flex align-items-center">

                                                        <i class="
                                                            fas
                                                            <?= $icon ?>
                                                            fa-lg
                                                            <?= $iconClass ?>
                                                            me-3
                                                        "></i>

                                                        <div
                                                            class="flex-grow-1"
                                                            style="min-width:0;"
                                                        >

                                                            <div
                                                                class="fw-semibold text-dark"
                                                                style="word-break:break-word;"
                                                            >

                                                                <?= htmlspecialchars(
                                                                    $fileName
                                                                ) ?>

                                                            </div>

                                                            <small class="text-muted">

                                                                <?= strtoupper(
                                                                    $extension
                                                                ) ?>

                                                                · Click to preview

                                                            </small>

                                                        </div>

                                                        <i class="
                                                            fas
                                                            fa-chevron-right
                                                            text-muted
                                                        "></i>

                                                    </div>

                                                </div>

                                            </div>

                                        </a>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 RIGHT CARD
            ================================================== -->

            <div class="col-lg-5">

                <div class="card h-100 shadow-sm">

                    <div class="card-header bg-light">

                        <strong>

                            <i class="fas fa-history me-2"></i>

                            Activity / Comments

                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="activity-empty">

                            <div>

                                <i class="fas fa-comments fa-2x mb-3"></i>

                                <div class="fw-semibold">
                                    No activity yet
                                </div>

                                <small>
                                    Comments, status updates and activity logs
                                    can be displayed here.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
<script>
$(document).on('click', '#showAllItemsBtn', function () {

    const button = $(this);
    const extraItems = $('.extra-item');

    if (extraItems.first().hasClass('d-none')) {

        // Show all items
        extraItems.removeClass('d-none');

        button.html(`
            <i class="fas fa-chevron-up me-1"></i>
            Show Less
        `);

    } else {

        // Hide items after the first 4
        extraItems.addClass('d-none');

        button.html(`
            <i class="fas fa-chevron-down me-1"></i>
            Show All Items
        `);

        $('html, body').animate({
            scrollTop: $('#itemsContainer').offset().top - 100
        }, 300);
    }
});
</script>

<?php
$conn->close();
?>
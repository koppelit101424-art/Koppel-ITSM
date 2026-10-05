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

if (!$stmt) {
    echo '<div class="alert alert-danger">Failed to prepare request query.</div>';
    exit;
}

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
| FORMAT STATUS
|--------------------------------------------------------------------------
*/

function formatStatus($status)
{
    $status = trim((string)$status);

    if ($status === '') {
        return 'N/A';
    }

    if (
        strtolower($status) === 'n/a' ||
        strtolower($status) === 'na'
    ) {
        return 'N/A';
    }

    $status = strtolower($status);

    $status = ucfirst($status);

    $status = preg_replace(
        '/\bpo\b/i',
        'PO',
        $status
    );

    return $status;
}


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
        r.category_id,
        rc.category_name,
        r.description,
        r.quantity,
        r.UoM,
        r.date_needed,
        r.remarks,
        r.status,
        r.order_status,
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

    LEFT JOIN request_category_tb rc
        ON r.category_id = rc.category_id

    WHERE r.lmr_no = ?

    ORDER BY r.request_id ASC
");

if (!$stmt) {
    echo '<div class="alert alert-danger">Failed to prepare request details query.</div>';
    exit;
}

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
| STEP 3: COLLECT ALL REQUEST IDS UNDER THIS LMR
|--------------------------------------------------------------------------
*/

$requestIds = [];

foreach ($requests as $row) {
    $requestIds[] = (int)$row['request_id'];
}


/*
|--------------------------------------------------------------------------
| GET REQUEST HISTORY / ACTIVITY
|--------------------------------------------------------------------------
|
| NORMAL HISTORY:
|     request_id belongs to one of the requests under this LMR.
|
| BULK HISTORY:
|     request_id IS NULL
|     changes_json contains:
|
|     {
|         "bulk_update": true,
|         "request_ids": [...],
|         "changes": {...}
|     }
|
| Bulk history therefore needs to be included separately.
|
*/

$history = [];

$historyStmt = $conn->prepare("
    SELECT
        h.history_id,
        h.request_id,
        h.bulk_group_id,
        h.changed_by,
        h.comment,
        h.changes_json,
        h.date_created,
        u.fullname
    FROM purch_request_history_tb h

    LEFT JOIN user_tb u
        ON h.changed_by = u.user_id

    WHERE

        (
            h.request_id IN (
                SELECT request_id
                FROM purch_request_tb
                WHERE lmr_no = ?
            )
        )

        OR

        (
            h.request_id IS NULL
            AND h.changes_json IS NOT NULL
            AND JSON_EXTRACT(
                h.changes_json,
                '$.bulk_update'
            ) = true
        )

    ORDER BY
        h.date_created DESC,
        h.history_id DESC
");

if (!$historyStmt) {

    die(
        'History query failed: ' .
        $conn->error
    );
}

$historyStmt->bind_param(
    "s",
    $lmr_no
);

$historyStmt->execute();

$historyResult =
    $historyStmt->get_result();

while (
    $historyRow =
    $historyResult->fetch_assoc()
) {

    $history[] =
        $historyRow;
}

$historyStmt->close();


/*
|--------------------------------------------------------------------------
| USE FIRST REQUEST FOR GENERAL INFORMATION
|--------------------------------------------------------------------------
*/

$request = $requests[0];


/*
|--------------------------------------------------------------------------
| STEP 4: GET ALL ATTACHMENTS FOR THIS LMR
|--------------------------------------------------------------------------
|
| Attachments are stored against request_id.
| Since one LMR can contain multiple requests,
| check all request IDs under the LMR.
|
*/

$attachments = [];

if (!empty($requestIds)) {

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($requestIds),
            '?'
        )
    );

    $types =
        str_repeat(
            'i',
            count($requestIds)
        );

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

    $attachStmt =
        $conn->prepare($sql);

    if ($attachStmt) {

        $attachStmt->bind_param(
            $types,
            ...$requestIds
        );

        $attachStmt->execute();

        $attachResult =
            $attachStmt->get_result();

        while (
            $attachment =
            $attachResult->fetch_assoc()
        ) {

            $attachments[] =
                $attachment;
        }

        $attachStmt->close();
    }
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
    margin-bottom: 8px !important;
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

.item-card .row {
    --bs-gutter-x: 0.75rem;
    --bs-gutter-y: 0;
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

.activity-list {
    max-height: 750px;
    overflow-y: auto;
    padding-right: 5px;
}

.activity-item {
    font-size: 0.88rem;
}

.activity-item .border-start {
    border-width: 3px !important;
}

.activity-item hr {
    border-color: #e9ecef;
}

.activity-item .bg-light {
    background-color: #f8f9fa !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
    color: #fff !important;
}

.bulk-summary {
    background-color: #f8f9fa;
    border-radius: 6px;
    padding: 10px 12px;
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

        <a
            href="?page=ticket/purch_lmr"
            class="btn btn-secondary btn-sm"
        >

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

                                    <?= htmlspecialchars(
                                        $lmr_no
                                    ) ?>

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

                                    $statusClass =
                                        match ($status) {

                                            'pending' =>
                                                'bg-warning text-dark',

                                            'checking requirements' =>
                                                'bg-info text-dark',

                                            'canvassing' =>
                                                'bg-primary',

                                            'negotiation' =>
                                                'bg-purple',

                                            'draft po under discussion' =>
                                                'bg-warning text-dark',

                                            'draft po approved' =>
                                                'bg-success',

                                            'final po approved' =>
                                                'bg-success',

                                            'rejected' =>
                                                'bg-danger',

                                            'closed' =>
                                                'bg-secondary',

                                            default =>
                                                'bg-secondary'
                                        };

                                    ?>

                                    <span class="badge <?= $statusClass ?>">

                                        <?= htmlspecialchars(
                                            formatStatus($status)
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

                                    $priorityClass =
                                        'bg-secondary';

                                    if ($priority === 'urgent') {

                                        $priorityClass =
                                            'bg-danger';

                                    } elseif ($priority === 'high') {

                                        $priorityClass =
                                            'bg-warning text-dark';

                                    } elseif ($priority === 'medium') {

                                        $priorityClass =
                                            'bg-info text-dark';
                                    }

                                    ?>

                                    <span class="badge <?= $priorityClass ?>">

                                        <?= htmlspecialchars(
                                            ucfirst(
                                                $priority ?: 'N/A'
                                            )
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
                                        ? htmlspecialchars(
                                            $request['po_no']
                                        )
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

                                    <div
                                        class="row g-3"
                                        id="itemsContainer"
                                    >

                                        <?php foreach (
                                            $requests
                                            as $index => $item
                                        ): ?>

                                            <?php

                                            $isHidden =
                                                $index >= 4;

                                            ?>


                                            <div
                                                class="col-md-6 item-wrapper <?= $isHidden ? 'd-none extra-item' : '' ?>"
                                            >

                                                <div class="card item-card h-100">

                                                    <div class="card-body">


                                                        <?php

                                                        $status =
                                                            strtolower(
                                                                trim(
                                                                    $item['status'] ?? ''
                                                                )
                                                            );

                                                        $orderStatus =
                                                            strtolower(
                                                                trim(
                                                                    $item['order_status'] ?? ''
                                                                )
                                                            );

                                                        $priority =
                                                            strtolower(
                                                                trim(
                                                                    $item['priority'] ?? ''
                                                                )
                                                            );


                                                        /*
                                                        |--------------------------------------------------------------------------
                                                        | STATUS CLASS
                                                        |--------------------------------------------------------------------------
                                                        */

                                                        $statusClass =
                                                            match ($status) {

                                                                'pending' =>
                                                                    'bg-warning text-dark',

                                                                'checking requirements' =>
                                                                    'bg-info text-dark',

                                                                'canvassing' =>
                                                                    'bg-primary',

                                                                'negotiation' =>
                                                                    'bg-purple',

                                                                'draft po under discussion' =>
                                                                    'bg-warning text-dark',

                                                                'draft po approved' =>
                                                                    'bg-success',

                                                                'final po approved' =>
                                                                    'bg-success',

                                                                'rejected' =>
                                                                    'bg-danger',

                                                                'closed' =>
                                                                    'bg-secondary',

                                                                default =>
                                                                    'bg-secondary'
                                                            };


                                                        /*
                                                        |--------------------------------------------------------------------------
                                                        | ORDER STATUS CLASS
                                                        |--------------------------------------------------------------------------
                                                        */

                                                        $orderStatusClass =
                                                            match ($orderStatus) {

                                                                'order acknowledged' =>
                                                                    'bg-info text-dark',

                                                                'goods delivered' =>
                                                                    'bg-success',

                                                                'goods received' =>
                                                                    'bg-success',

                                                                'payment processing' =>
                                                                    'bg-warning text-dark',

                                                                'payment issued' =>
                                                                    'bg-success',

                                                                'closed' =>
                                                                    'bg-secondary',

                                                                'n/a',
                                                                'na',
                                                                '' =>
                                                                    'bg-secondary',

                                                                default =>
                                                                    'bg-secondary'
                                                            };


                                                        /*
                                                        |--------------------------------------------------------------------------
                                                        | PRIORITY CLASS
                                                        |--------------------------------------------------------------------------
                                                        */

                                                        $priorityClass =
                                                            match ($priority) {

                                                                'urgent' =>
                                                                    'bg-danger',

                                                                'high' =>
                                                                    'bg-warning text-dark',

                                                                'medium' =>
                                                                    'bg-info text-dark',

                                                                default =>
                                                                    'bg-secondary'
                                                            };

                                                        ?>


                                                        <!-- ITEM HEADER -->

                                                        <div class="d-flex justify-content-between align-items-start mb-3">

                                                            <div class="fw-semibold text-dark">

                                                                <?= htmlspecialchars(
                                                                    $item['item']
                                                                ) ?>

                                                            </div>

                                                            <span class="badge bg-light text-secondary">

                                                                #<?= $index + 1 ?>

                                                            </span>

                                                        </div>


                                                        <!-- QUANTITY / UOM -->

                                                        <div class="row g-3 mb-2">

                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    Quantity
                                                                </div>

                                                                <div class="request-value">

                                                                    <?= htmlspecialchars(
                                                                        $item['quantity']
                                                                    ) ?>

                                                                </div>

                                                            </div>


                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    UoM
                                                                </div>

                                                                <div class="request-value">

                                                                    <?= htmlspecialchars(
                                                                        $item['UoM']
                                                                    ) ?>

                                                                </div>

                                                            </div>

                                                        </div>


                                                        <!-- CATEGORY / PO -->

                                                        <div class="row g-3 mb-2">

                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    Category
                                                                </div>

                                                                <div class="request-value">

                                                                    <?= htmlspecialchars(
                                                                        $item['category_name']
                                                                        ?? 'N/A'
                                                                    ) ?>

                                                                </div>

                                                            </div>


                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    PO Number
                                                                </div>

                                                                <div class="request-value">

                                                                    <?php if (
                                                                        !empty(
                                                                            $item['po_no']
                                                                        )
                                                                    ): ?>

                                                                        <?= htmlspecialchars(
                                                                            $item['po_no']
                                                                        ) ?>

                                                                    <?php else: ?>

                                                                        <span class="text-muted">

                                                                            Not yet assigned

                                                                        </span>

                                                                    <?php endif; ?>

                                                                </div>

                                                            </div>

                                                        </div>


                                                        <!-- DATE NEEDED / PRIORITY -->

                                                        <div class="row g-3 mb-2">

                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    Date Needed
                                                                </div>

                                                                <div class="request-value">

                                                                    <?= !empty(
                                                                        $item['date_needed']
                                                                    )
                                                                        ? date(
                                                                            'M d, Y',
                                                                            strtotime(
                                                                                $item['date_needed']
                                                                            )
                                                                        )
                                                                        : '-'
                                                                    ?>

                                                                </div>

                                                            </div>


                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    Priority
                                                                </div>

                                                                <div class="request-value">

                                                                    <span class="badge <?= $priorityClass ?>">

                                                                        <?= htmlspecialchars(
                                                                            ucfirst(
                                                                                $priority ?: 'N/A'
                                                                            )
                                                                        ) ?>

                                                                    </span>

                                                                </div>

                                                            </div>

                                                        </div>


                                                        <!-- STATUS / ORDER STATUS -->

                                                        <div class="row g-3 mb-3">

                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    Status
                                                                </div>

                                                                <div class="request-value">

                                                                    <span class="badge <?= $statusClass ?>">

                                                                        <?= htmlspecialchars(
                                                                            formatStatus(
                                                                                $status ?: 'n/a'
                                                                            )
                                                                        ) ?>

                                                                    </span>

                                                                </div>

                                                            </div>


                                                            <div class="col-6">

                                                                <div class="request-label">
                                                                    Order Status
                                                                </div>

                                                                <div class="request-value">

                                                                    <span class="badge <?= $orderStatusClass ?>">

                                                                        <?= htmlspecialchars(
                                                                            formatStatus(
                                                                                $orderStatus ?: 'n/a'
                                                                            )
                                                                        ) ?>

                                                                    </span>

                                                                </div>

                                                            </div>

                                                        </div>


                                                        <!-- DESCRIPTION / REMARKS -->

                                                        <div class="row g-3 mb-3">

                                                            <div class="col-6">

                                                                <?php if (
                                                                    !empty(
                                                                        $item['description']
                                                                    )
                                                                ): ?>

                                                                    <div class="request-label">
                                                                        Description
                                                                    </div>

                                                                    <div class="request-value mb-3">

                                                                        <?= nl2br(
                                                                            htmlspecialchars(
                                                                                $item['description']
                                                                            )
                                                                        ) ?>

                                                                    </div>

                                                                <?php endif; ?>

                                                            </div>


                                                            <div class="col-6">

                                                                <?php if (
                                                                    !empty(
                                                                        $item['remarks']
                                                                    )
                                                                ): ?>

                                                                    <div class="request-label">
                                                                        Remarks
                                                                    </div>

                                                                    <div class="request-value">

                                                                        <?= nl2br(
                                                                            htmlspecialchars(
                                                                                $item['remarks']
                                                                            )
                                                                        ) ?>

                                                                    </div>

                                                                <?php endif; ?>

                                                            </div>

                                                        </div>


                                                    </div>

                                                </div>

                                            </div>


                                        <?php endforeach; ?>

                                    </div>


                                    <?php if (
                                        count($requests) > 4
                                    ): ?>

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


                            <div class="d-flex align-items-center gap-2">

                                <span class="badge bg-secondary">

                                    <?= count($attachments) ?>

                                </span>


                                <?php if (!empty($attachments)): ?>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-success"
                                        id="downloadAllAttachments"
                                    >

                                        <i class="fas fa-download me-1"></i>

                                        Download All

                                    </button>

                                <?php endif; ?>

                            </div>

                        </div>


                        <?php if (empty($attachments)): ?>

                            <div class="text-muted small">

                                <i class="fas fa-info-circle me-1"></i>

                                No attachments for this LMR.

                            </div>

                        <?php else: ?>

                            <div class="row">

                                <?php foreach (
                                    $attachments
                                    as $attachment
                                ): ?>

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

                                                        <i
                                                            class="
                                                                fas
                                                                <?= $icon ?>
                                                                fa-lg
                                                                <?= $iconClass ?>
                                                                me-3
                                                            "
                                                        ></i>


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


                                                        <i
                                                            class="
                                                                fas
                                                                fa-chevron-right
                                                                text-muted
                                                            "
                                                        ></i>

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

                        <?php if (empty($history)): ?>

                            <div class="activity-empty">

                                <div>

                                    <i class="fas fa-comments fa-2x mb-3"></i>

                                    <div class="fw-semibold">
                                        No activity yet
                                    </div>

                                    <small>
                                        Comments, status updates and activity logs
                                        will appear here.
                                    </small>

                                </div>

                            </div>

                        <?php else: ?>

                            <div class="activity-list">

                                <?php foreach (
                                    $history
                                    as $historyIndex => $activity
                                ): ?>


                                    <?php

                                    /*
                                    |--------------------------------------------------------------------------
                                    | DECODE HISTORY JSON
                                    |--------------------------------------------------------------------------
                                    */

                                    $changes = [];

                                    $isBulkActivity =
                                        false;

                                    $bulkRequestIds = [];


                                    if (
                                        !empty(
                                            $activity['changes_json']
                                        )
                                    ) {

                                        $decodedChanges =
                                            json_decode(
                                                $activity['changes_json'],
                                                true
                                            );


                                        if (
                                            is_array(
                                                $decodedChanges
                                            )
                                        ) {


                                            /*
                                            |--------------------------------------------------------------------------
                                            | BULK HISTORY
                                            |--------------------------------------------------------------------------
                                            */

                                            if (
                                                isset(
                                                    $decodedChanges['bulk_update']
                                                )
                                                &&
                                                $decodedChanges['bulk_update'] === true
                                            ) {

                                                $isBulkActivity =
                                                    true;


                                                $bulkRequestIds =
                                                    $decodedChanges['request_ids']
                                                    ?? [];


                                                /*
                                                |--------------------------------------------------------------------------
                                                | REMOVE DUPLICATE CHANGES
                                                |--------------------------------------------------------------------------
                                                |
                                                | Example:
                                                |
                                                | Request 1:
                                                | pending -> canvassing
                                                |
                                                | Request 2:
                                                | pending -> canvassing
                                                |
                                                | Request 3:
                                                | pending -> canvassing
                                                |
                                                |
                                                | Display:
                                                |
                                                | Status
                                                | Pending -> Canvassing
                                                |
                                                | only once.
                                                |
                                                */

                                                $uniqueChanges = [];


                                                if (
                                                    isset(
                                                        $decodedChanges['changes']
                                                    )
                                                    &&
                                                    is_array(
                                                        $decodedChanges['changes']
                                                    )
                                                ) {


                                                    foreach (
                                                        $decodedChanges['changes']
                                                        as $bulkRequestId =>
                                                        $requestChanges
                                                    ) {


                                                        if (
                                                            !is_array(
                                                                $requestChanges
                                                            )
                                                        ) {
                                                            continue;
                                                        }


                                                        foreach (
                                                            $requestChanges
                                                            as $field =>
                                                            $change
                                                        ) {


                                                            if (
                                                                !is_array(
                                                                    $change
                                                                )
                                                                ||
                                                                !array_key_exists(
                                                                    'old',
                                                                    $change
                                                                )
                                                                ||
                                                                !array_key_exists(
                                                                    'new',
                                                                    $change
                                                                )
                                                            ) {
                                                                continue;
                                                            }


                                                            /*
                                                            |--------------------------------------------------------------------------
                                                            | CREATE UNIQUE SIGNATURE
                                                            |--------------------------------------------------------------------------
                                                            */

                                                            $signature =
                                                                $field .
                                                                '|' .
                                                                (string)(
                                                                    $change['old']
                                                                    ?? ''
                                                                ) .
                                                                '|' .
                                                                (string)(
                                                                    $change['new']
                                                                    ?? ''
                                                                );


                                                            if (
                                                                !isset(
                                                                    $uniqueChanges[
                                                                        $signature
                                                                    ]
                                                                )
                                                            ) {

                                                                $uniqueChanges[
                                                                    $signature
                                                                ] = [

                                                                    'field' =>
                                                                        $field,

                                                                    'old' =>
                                                                        $change['old']
                                                                        ?? '',

                                                                    'new' =>
                                                                        $change['new']
                                                                        ?? ''
                                                                ];
                                                            }

                                                        }

                                                    }

                                                }


                                                $changes =
                                                    array_values(
                                                        $uniqueChanges
                                                    );


                                            } else {


                                                /*
                                                |--------------------------------------------------------------------------
                                                | NORMAL HISTORY
                                                |--------------------------------------------------------------------------
                                                */

                                                $changes =
                                                    $decodedChanges;
                                            }

                                        }

                                    }

                                    ?>


                                    <div class="activity-item mb-4">


                                        <!-- =================================
                                             USER / DATE
                                        ================================== -->

                                        <div class="d-flex align-items-start">

                                            <div
                                                class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                style="width:36px;height:36px;flex-shrink:0;"
                                            >

                                                <i class="fas fa-user"></i>

                                            </div>


                                            <div class="flex-grow-1">

                                                <div class="fw-semibold">

                                                    <?= htmlspecialchars(
                                                        $activity['fullname']
                                                        ?: 'Unknown User'
                                                    ) ?>

                                                </div>


                                                <small class="text-muted">

                                                    <?= !empty(
                                                        $activity['date_created']
                                                    )
                                                        ? date(
                                                            'M d, Y h:i A',
                                                            strtotime(
                                                                $activity['date_created']
                                                            )
                                                        )
                                                        : ''
                                                    ?>

                                                </small>

                                            </div>

                                        </div>


                                        <!-- =================================
                                             BULK UPDATE
                                        ================================== -->

                                        <?php if (
                                            $isBulkActivity
                                        ): ?>


                                            <div class="mt-3">


                                                <!-- BULK SUMMARY -->

                                                <div class="bulk-summary mb-3">

                                                    <div class="d-flex align-items-center">

                                                        <div
                                                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                            style="width:32px;height:32px;"
                                                        >

                                                            <i class="fas fa-layer-group"></i>

                                                        </div>


                                                        <div>

                                                            <div class="fw-semibold">

                                                                Bulk Update

                                                            </div>


                                                            <small class="text-muted">

                                                                <?= count(
                                                                    $bulkRequestIds
                                                                ) ?>

                                                                request(s) updated

                                                            </small>

                                                        </div>

                                                    </div>

                                                </div>


                                                <!-- BULK CHANGES -->

                                                <?php if (
                                                    !empty(
                                                        $changes
                                                    )
                                                ): ?>


                                                    <?php foreach (
                                                        $changes
                                                        as $change
                                                    ): ?>


                                                        <?php

                                                        $field =
                                                            $change['field']
                                                            ?? '';


                                                        $fieldLabel =
                                                            match (
                                                                $field
                                                            ) {

                                                                'status' =>
                                                                    'Status',

                                                                'purchaser' =>
                                                                    'Assigned Purchaser',

                                                                'priority' =>
                                                                    'Priority',

                                                                'category' =>
                                                                    'Category',

                                                                'order_status' =>
                                                                    'Order Status',

                                                                'po_no' =>
                                                                    'PO Number',

                                                                default =>
                                                                    ucwords(
                                                                        str_replace(
                                                                            '_',
                                                                            ' ',
                                                                            $field
                                                                        )
                                                                    )
                                                            };


                                                        $oldValue =
                                                            $change['old']
                                                            ?? '';


                                                        $newValue =
                                                            $change['new']
                                                            ?? '';

                                                        ?>


                                                        <div class="border-start border-3 border-primary ps-3 mb-3">


                                                            <div class="small text-muted mb-1">

                                                                <?= htmlspecialchars(
                                                                    $fieldLabel
                                                                ) ?>

                                                            </div>


                                                            <div>


                                                                <span class="text-muted">

                                                                    <?= htmlspecialchars(
                                                                        $oldValue !== ''
                                                                            ? $oldValue
                                                                            : 'N/A'
                                                                    ) ?>

                                                                </span>


                                                                <i
                                                                    class="fas fa-arrow-right mx-2 text-primary"
                                                                ></i>


                                                                <strong>

                                                                    <?= htmlspecialchars(
                                                                        $newValue !== ''
                                                                            ? $newValue
                                                                            : 'N/A'
                                                                    ) ?>

                                                                </strong>


                                                            </div>


                                                        </div>


                                                    <?php endforeach; ?>


                                                <?php else: ?>


                                                    <div class="text-muted small mb-3">

                                                        No field changes recorded.

                                                    </div>


                                                <?php endif; ?>


                                            </div>


                                        <!-- =================================
                                             NORMAL UPDATE
                                        ================================== -->

                                        <?php elseif (
                                            !empty(
                                                $changes
                                            )
                                        ): ?>


                                            <div class="mt-3">


                                                <?php foreach (
                                                    $changes
                                                    as $field =>
                                                    $change
                                                ): ?>


                                                    <?php

                                                    $fieldLabel =
                                                        match (
                                                            $field
                                                        ) {

                                                            'status' =>
                                                                'Status',

                                                            'purchaser' =>
                                                                'Assigned Purchaser',

                                                            'priority' =>
                                                                'Priority',

                                                            'category' =>
                                                                'Category',

                                                            'order_status' =>
                                                                'Order Status',

                                                            'po_no' =>
                                                                'PO Number',

                                                            default =>
                                                                ucwords(
                                                                    str_replace(
                                                                        '_',
                                                                        ' ',
                                                                        $field
                                                                    )
                                                                )
                                                        };


                                                    $oldValue =
                                                        $change['old']
                                                        ?? '';


                                                    $newValue =
                                                        $change['new']
                                                        ?? '';

                                                    ?>


                                                    <div class="border-start border-3 border-primary ps-3 mb-3">


                                                        <div class="small text-muted mb-1">

                                                            <?= htmlspecialchars(
                                                                $fieldLabel
                                                            ) ?>

                                                        </div>


                                                        <div>


                                                            <span class="text-muted">

                                                                <?= htmlspecialchars(
                                                                    $oldValue
                                                                    ?: 'N/A'
                                                                ) ?>

                                                            </span>


                                                            <i
                                                                class="fas fa-arrow-right mx-2 text-primary"
                                                            ></i>


                                                            <strong>

                                                                <?= htmlspecialchars(
                                                                    $newValue
                                                                    ?: 'N/A'
                                                                ) ?>

                                                            </strong>


                                                        </div>


                                                    </div>


                                                <?php endforeach; ?>


                                            </div>


                                        <?php endif; ?>


                                        <!-- =================================
                                             COMMENT
                                        ================================== -->

                                        <?php if (
                                            !empty(
                                                trim(
                                                    $activity['comment']
                                                    ?? ''
                                                )
                                            )
                                        ): ?>


                                            <div class="mt-2 p-3 bg-light rounded">


                                                <div class="small text-muted mb-1">

                                                    <i class="fas fa-comment me-1"></i>

                                                    Comment

                                                </div>


                                                <div>

                                                    <?= nl2br(
                                                        htmlspecialchars(
                                                            $activity['comment']
                                                        )
                                                    ) ?>

                                                </div>


                                            </div>


                                        <?php endif; ?>


                                    </div>


                                    <?php if (
                                        $historyIndex <
                                        count($history) - 1
                                    ): ?>

                                        <hr>

                                    <?php endif; ?>


                                <?php endforeach; ?>


                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


        </div>

    </div>

</div>


<script>

$(document).on(
    'click',
    '#showAllItemsBtn',
    function () {

        const button =
            $(this);

        const extraItems =
            $('.extra-item');


        if (
            extraItems
                .first()
                .hasClass('d-none')
        ) {


            /*
            |--------------------------------------------------------------------------
            | SHOW ALL ITEMS
            |--------------------------------------------------------------------------
            */

            extraItems.removeClass(
                'd-none'
            );


            button.html(`
                <i class="fas fa-chevron-up me-1"></i>
                Show Less
            `);


        } else {


            /*
            |--------------------------------------------------------------------------
            | HIDE ITEMS AFTER FIRST 4
            |--------------------------------------------------------------------------
            */

            extraItems.addClass(
                'd-none'
            );


            button.html(`
                <i class="fas fa-chevron-down me-1"></i>
                Show All Items
            `);


            $('html, body').animate({

                scrollTop:
                    $('#itemsContainer')
                    .offset()
                    .top - 100

            }, 300);

        }

    }
);

</script>


<script>

document
    .getElementById(
        'downloadAllAttachments'
    )
    ?.addEventListener(
        'click',
        function () {


            const attachmentIds = [

                <?php foreach (
                    $attachments
                    as $attachment
                ): ?>

                    <?= (int)$attachment['attachment_id'] ?>,

                <?php endforeach; ?>

            ];


            if (
                attachmentIds.length === 0
            ) {

                alert(
                    'No attachments found.'
                );

                return;
            }


            const button =
                this;


            /*
            |--------------------------------------------------------------------------
            | PREVENT DOUBLE CLICKING
            |--------------------------------------------------------------------------
            */

            button.disabled =
                true;


            button.innerHTML = `
                <i class="fas fa-spinner fa-spin me-1"></i>
                Downloading...
            `;


            /*
            |--------------------------------------------------------------------------
            | DOWNLOAD EACH FILE SEPARATELY
            |--------------------------------------------------------------------------
            */

            attachmentIds.forEach(
                function (
                    attachmentId,
                    index
                ) {

                    setTimeout(
                        function () {

                            const link =
                                document.createElement(
                                    'a'
                                );


                            link.href =
                                'ticket/download_purch_attachment.php?attachment_id='
                                + attachmentId;


                            link.download =
                                '';


                            document
                                .body
                                .appendChild(
                                    link
                                );


                            link.click();


                            document
                                .body
                                .removeChild(
                                    link
                                );

                        },
                        index * 1200
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | RESTORE BUTTON
            |--------------------------------------------------------------------------
            */

            setTimeout(
                function () {

                    button.disabled =
                        false;


                    button.innerHTML = `
                        <i class="fas fa-download me-1"></i>
                        Download All
                    `;

                },
                attachmentIds.length * 1200 + 1000
            );

        }
    );

</script>


<?php

$conn->close();

?>

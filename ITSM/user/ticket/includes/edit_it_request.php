<?php

include 'includes/auth.php';
include 'includes/db.php';

$created_by = (int)$_SESSION['user_id'];

$request_id = isset($_GET['request_id'])
    ? (int)$_GET['request_id']
    : 0;


// =====================================================
// VALIDATE REQUEST ID
// =====================================================

if ($request_id <= 0) {
    die('Invalid request.');
}


// =====================================================
// GET REQUEST
// =====================================================

$stmt = $conn->prepare("
    SELECT
        request_id,
        lmr_no,
        user_id,
        requestor,
        department,
        item,
        description,
        quantity,
        UoM,
        date_needed,
        remarks,
        status,
        created_by,
        ticket_id
    FROM request_tb
    WHERE request_id = ?
      AND created_by = ?
    LIMIT 1
");

if (!$stmt) {
    die('Database error: ' . $conn->error);
}

$stmt->bind_param(
    "ii",
    $request_id,
    $created_by
);

$stmt->execute();

$result = $stmt->get_result();
$request = $result->fetch_assoc();

$stmt->close();


// =====================================================
// CHECK OWNERSHIP
// =====================================================

if (!$request) {
    die('You are not allowed to edit this request.');
}


// =====================================================
// ONLY PENDING CAN BE EDITED
// =====================================================

if (strcasecmp(trim($request['status']), 'pending') !== 0) {
    die('This request can no longer be edited.');
}


// =====================================================
// GET USER INFORMATION
// =====================================================

$userQuery = $conn->prepare("
    SELECT fullname, department
    FROM user_tb
    WHERE user_id = ?
    LIMIT 1
");

$userQuery->bind_param(
    "i",
    $request['user_id']
);

$userQuery->execute();

$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

$userQuery->close();

?>

<style>
.item-row {
    border-top: 1px dashed #ccc;
    padding-top: 15px;
    margin-top: 15px;
}
</style>

<div class="card">

    <!-- HEADER -->

    <div class="card-header d-flex justify-content-between align-items-center text-white">

        <span>
            Edit IT LMR
        </span>

        <a
            href="?page=ticket/view_request&request_id=<?= (int)$request['request_id'] ?>"
            class="btn btn-secondary btn-sm"
        >
            Back to Request
        </a>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="?page=ticket/includes/update_it_request"
            id="editRequestForm"
        >

            <!-- REQUEST INFORMATION -->

            <div class="row mb-4">

                <!-- LMR NO -->

                <div class="col-md-4">

                    <label class="form-label">
                        LMR No *
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($request['lmr_no']) ?>"
                        readonly
                    >

                    <input
                        type="hidden"
                        name="request_id"
                        value="<?= (int)$request['request_id'] ?>"
                    >

                </div>


                <!-- FULLNAME -->

                <div class="col-md-4">

                    <label class="form-label">
                        Fullname
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($user['fullname'] ?? $request['requestor']) ?>"
                        readonly
                    >

                </div>


                <!-- REFERENCE TICKET -->

                <div class="col-md-4">

                    <label class="form-label">
                        Reference Ticket
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        name="ticket_id"
                        value="<?= htmlspecialchars($request['ticket_id'] ?? '') ?>"
                    readonly>

                </div>


                <!-- DEPARTMENT -->

                <input
                    type="hidden"
                    name="department"
                    value="<?= htmlspecialchars($user['department'] ?? $request['department']) ?>"
                >

                <input
                    type="hidden"
                    name="requestor"
                    value="<?= htmlspecialchars($user['fullname'] ?? $request['requestor']) ?>"
                >

            </div>


            <!-- CREATED BY -->

            <input
                type="hidden"
                name="created_by"
                value="<?= (int)$created_by ?>"
            >


            <!-- STATUS -->

            <div class="mb-4">

                <label class="form-label">
                    Status
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars(ucfirst($request['status'])) ?>"
                    readonly
                >

            </div>


            <!-- ITEM -->

            <h5>Item</h5>

            <div id="itemsContainer">

                <div class="item-row">

                    <div class="row">

                        <!-- ITEM -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Item *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="item"
                                value="<?= htmlspecialchars($request['item']) ?>"
                                required
                            >

                        </div>


                        <!-- DESCRIPTION -->

                        <div class="col-md-3">

                            <label class="form-label">
                                Description *
                            </label>

                            <textarea
                                class="form-control"
                                name="description"
                                rows="1"
                                required
                            ><?= htmlspecialchars($request['description']) ?></textarea>

                        </div>


                        <!-- QUANTITY -->

                        <div class="col-md-2">

                            <label class="form-label">
                                Qty *
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                name="quantity"
                                value="<?= htmlspecialchars($request['quantity']) ?>"
                                step="0.01"
                                min="0.01"
                                required
                            >

                        </div>


                        <!-- UOM -->

                        <div class="col-md-2">

                            <label class="form-label">
                                Unit of Measurement *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="uom"
                                value="<?= htmlspecialchars($request['UoM']) ?>"
                                required
                            >

                        </div>


                        <!-- DATE NEEDED -->

                        <div class="col-md-2">

                            <label class="form-label">
                                Date Needed *
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                name="date_needed"
                                value="<?= htmlspecialchars($request['date_needed']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- REMARKS -->

                    <div class="row mt-2">

                        <div class="col-md-10">

                            <label class="form-label">
                                Remarks
                            </label>

                            <textarea
                                class="form-control"
                                name="remarks"
                                rows="10"
                            ><?= htmlspecialchars($request['remarks'] ?? '') ?></textarea>

                        </div>

                    </div>

                </div>

            </div>


            <!-- BUTTONS -->

            <div class="d-flex justify-content-end mt-4">

                <button
                    type="submit"
                    class="btn btn-primary me-2"
                >
                    <i class="fas fa-save me-1"></i>
                    Save Changes
                </button>

                <a
                    href="#"
                    onclick="window.history.back(); return false;"
                    class="btn btn-secondary"
                >
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

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
// GET LOGGED-IN USER
// Get department from user_tb
// =====================================================

$userStmt = $conn->prepare("
    SELECT
        user_id,
        fullname,
        department
    FROM user_tb
    WHERE user_id = ?
    LIMIT 1
");

if (!$userStmt) {
    die('Database error: ' . $conn->error);
}

$userStmt->bind_param("i", $created_by);
$userStmt->execute();

$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

$userStmt->close();

if (!$user) {
    die('User not found.');
}

$userDepartment = trim($user['department'] ?? '');
$userFullname   = trim($user['fullname'] ?? '');


// =====================================================
// GET REQUEST
// Only the creator can edit the request
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
        created_by
    FROM purch_request_tb
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
// ONLY PENDING REQUESTS CAN BE EDITED
// =====================================================

if (strcasecmp(trim($request['status']), 'pending') !== 0) {
    die('This request can no longer be edited.');
}

?>

<style>

.item-row {
    border-top: 1px dashed #ccc;
    padding-top: 15px;
    margin-top: 15px;
}

</style>


<div class="card">

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="card-header d-flex justify-content-between align-items-center text-white">

        <span>
            Edit Request
        </span>

        <a
            href="?page=ticket/purch_lmr"
            class="btn btn-secondary btn-sm"
        >
            Back to Request
        </a>

    </div>


    <!-- =====================================================
         CARD BODY
    ====================================================== -->

    <div class="card-body">

        <form
            method="POST"
            action="?page=ticket/includes/update_purch_request"
            id="editRequestForm"
        >

            <!-- =================================================
                 REQUEST INFORMATION
            ================================================== -->

            <div class="row mb-4">

                <!-- LMR NO -->

                <div class="col-md-4">

                    <label class="form-label">
                        LMR No *
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($request['lmr_no'] ?? '') ?>"
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
                        value="<?= htmlspecialchars($userFullname) ?>"
                        readonly
                    >

                </div>


                <!-- DEPARTMENT -->

                <div class="col-md-4">

                    <label class="form-label">
                        Department
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($userDepartment) ?>"
                        readonly
                    >

                    <!-- Send department to update file -->
                    <input
                        type="hidden"
                        name="department"
                        value="<?= htmlspecialchars($userDepartment) ?>"
                    >

                </div>

            </div>


            <!-- =================================================
                 STATUS
            ================================================== -->

            <div class="mb-4">

                <label class="form-label">
                    Status
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars(ucfirst($request['status'] ?? '')) ?>"
                    readonly
                >

            </div>


            <!-- =================================================
                 ITEM
            ================================================== -->

            <h5>Item</h5>

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
                            value="<?= htmlspecialchars($request['item'] ?? '') ?>"
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
                        ><?= htmlspecialchars($request['description'] ?? '') ?></textarea>

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
                            value="<?= htmlspecialchars($request['quantity'] ?? '') ?>"
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
                            value="<?= htmlspecialchars($request['UoM'] ?? '') ?>"
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
                            value="<?= htmlspecialchars($request['date_needed'] ?? '') ?>"
                            required
                        >

                    </div>

                </div>


                <!-- =================================================
                     REMARKS
                ================================================== -->

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


            <!-- =================================================
                 BUTTONS
            ================================================== -->

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

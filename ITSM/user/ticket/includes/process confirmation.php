<?php
/*
|--------------------------------------------------------------------------
| GOODS RECEIVED CONFIRMATION
|--------------------------------------------------------------------------
*/

$orderStatus = strtolower(
    trim($request['order_status'] ?? '')
);

if ($orderStatus === 'goods received'):
?>

    <div class="alert alert-success mt-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <strong>
                    <i class="fas fa-box-open me-2"></i>
                    Goods Received
                </strong>

                <div class="small mt-1">
                    Please confirm whether you received the item.
                </div>

            </div>

            <button
                type="button"
                class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#goodsReceivedModal"
            >
                <i class="fas fa-check-circle me-1"></i>
                Confirm Goods Received
            </button>

        </div>

    </div>

<?php endif; ?>

<?php
/*
|--------------------------------------------------------------------------
| GOODS RECEIVED CONFIRMATION MODAL
|--------------------------------------------------------------------------
*/

$orderStatus = strtolower(
    trim($request['order_status'] ?? '')
);

if ($orderStatus === 'goods received'):
?>

<div
    class="modal fade"
    id="goodsReceivedModal"
    tabindex="-1"
    aria-labelledby="goodsReceivedModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <!-- HEADER -->

            <div class="modal-header bg-success text-white">

                <h5
                    class="modal-title"
                    id="goodsReceivedModalLabel"
                >
                    <i class="fas fa-box-open me-2"></i>
                    Confirm Goods Received
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <!-- BODY -->

            <form
                method="POST"
                action=""
            >

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="goods_received_confirmation"
                        value="1"
                    >

                    <input
                        type="hidden"
                        name="request_id"
                        value="<?= (int)$request['request_id'] ?>"
                    >


                    <!-- QUESTION -->

                    <div class="mb-4">

                        <label class="form-label fw-bold">

                            Did you receive the item?

                            <span class="text-danger">*</span>

                        </label>


                        <div class="form-check mb-2">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="goods_received"
                                id="goodsReceivedYes"
                                value="yes"
                                required
                            >

                            <label
                                class="form-check-label"
                                for="goodsReceivedYes"
                            >
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Yes, I received the item
                            </label>

                        </div>


                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="goods_received"
                                id="goodsReceivedNo"
                                value="no"
                                required
                            >

                            <label
                                class="form-check-label"
                                for="goodsReceivedNo"
                            >
                                <i class="fas fa-times-circle text-danger me-1"></i>
                                No, I did not receive the item
                            </label>

                        </div>

                    </div>


                    <!-- COMMENT -->

                    <div class="mb-3">

                        <label
                            for="goodsReceivedComment"
                            class="form-label fw-bold"
                        >

                            Comments

                            <span class="text-danger">*</span>

                        </label>


                        <textarea
                            name="goods_received_comment"
                            id="goodsReceivedComment"
                            class="form-control"
                            rows="5"
                            placeholder="Please enter your comments regarding the received item..."
                            required
                        ></textarea>


                        <div class="form-text">

                            Please describe the condition of the item
                            or explain why you did not receive it.

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="btn btn-success"
                    >

                        <i class="fas fa-save me-1"></i>

                        Submit Confirmation

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>

<?php

/*
|--------------------------------------------------------------------------
| PROCESS GOODS RECEIVED CONFIRMATION
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['goods_received_confirmation'])
) {

    $requestId = (int)(
        $_POST['request_id'] ?? 0
    );


    $goodsReceived = strtolower(
        trim(
            $_POST['goods_received'] ?? ''
        )
    );


    $comment = trim(
        $_POST['goods_received_comment'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | GET LOGGED-IN USER
    |--------------------------------------------------------------------------
    |
    | Change this if your session variable uses
    | a different name.
    |
    */

    $changedBy = (int)(
        $_SESSION['user_id'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($requestId <= 0) {

        $_SESSION['error'] =
            'Invalid request ID.';

    } elseif (!in_array(
        $goodsReceived,
        ['yes', 'no'],
        true
    )) {

        $_SESSION['error'] =
            'Please select Yes or No.';

    } elseif ($comment === '') {

        $_SESSION['error'] =
            'Please enter your comments.';

    } elseif ($changedBy <= 0) {

        $_SESSION['error'] =
            'Unable to identify the logged-in user.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | VERIFY REQUEST EXISTS AND IS GOODS RECEIVED
        |--------------------------------------------------------------------------
        */

        $checkStmt = $conn->prepare("
            SELECT
                request_id,
                order_status
            FROM purch_request_tb
            WHERE request_id = ?
            LIMIT 1
        ");


        if (!$checkStmt) {

            $_SESSION['error'] =
                'Unable to verify the purchasing request.';

            error_log(
                'Goods Received Confirmation: ' .
                'Prepare failed: ' .
                $conn->error
            );

        } else {

            $checkStmt->bind_param(
                "i",
                $requestId
            );


            if (!$checkStmt->execute()) {

                $_SESSION['error'] =
                    'Unable to verify the purchasing request.';

                error_log(
                    'Goods Received Confirmation: ' .
                    'Execute failed: ' .
                    $checkStmt->error
                );

                $checkStmt->close();

            } else {

                $result =
                    $checkStmt->get_result();

                $requestCheck =
                    $result->fetch_assoc();

                $checkStmt->close();


                if (!$requestCheck) {

                    $_SESSION['error'] =
                        'Purchasing request not found.';

                } elseif (
                    strtolower(
                        trim(
                            $requestCheck['order_status'] ?? ''
                        )
                    ) !== 'goods received'
                ) {

                    $_SESSION['error'] =
                        'This request is no longer marked as Goods Received.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | CHANGES JSON
                    |--------------------------------------------------------------------------
                    */

                    $changes = [

                        'goods_received' => [

                            'old' => 'pending confirmation',

                            'new' => $goodsReceived

                        ]

                    ];


                    $changesJson = json_encode(
                        $changes,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                    );


                    if ($changesJson === false) {

                        $_SESSION['error'] =
                            'Unable to prepare confirmation data.';

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | INSERT HISTORY
                        |--------------------------------------------------------------------------
                        */

                        $historyStmt = $conn->prepare("
                            INSERT INTO purch_request_history_tb
                            (
                                request_id,
                                bulk_group_id,
                                changed_by,
                                comment,
                                changes_json,
                                date_created
                            )
                            VALUES
                            (
                                ?,
                                0,
                                ?,
                                ?,
                                ?,
                                NOW()
                            )
                        ");


                        if (!$historyStmt) {

                            $_SESSION['error'] =
                                'Unable to save the confirmation.';

                            error_log(
                                'Goods Received Confirmation: ' .
                                'History prepare failed: ' .
                                $conn->error
                            );

                        } else {

                            $historyStmt->bind_param(
                                "iiss",
                                $requestId,
                                $changedBy,
                                $comment,
                                $changesJson
                            );


                            if ($historyStmt->execute()) {

                                $_SESSION['success'] =
                                    'Goods received confirmation submitted successfully.';

                            } else {

                                $_SESSION['error'] =
                                    'Unable to save the confirmation.';

                                error_log(
                                    'Goods Received Confirmation: ' .
                                    'History insert failed: ' .
                                    $historyStmt->error
                                );
                            }


                            $historyStmt->close();
                        }
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    |
    | Prevent duplicate form submission if the user
    | refreshes the page.
    |
    */

    header(
        'Location: index.php?page=ticket/view_purch_request&request_id=' .
        $requestId
    );

    exit;
}
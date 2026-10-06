<?php

ob_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');


// =====================================================
// JSON RESPONSE
// =====================================================

function returnJson($response, $statusCode = 200)
{
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($statusCode);

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


try {

    // =====================================================
    // LOGIN
    // =====================================================

    if (!isset($_SESSION['user_id'])) {

        returnJson([
            'success' => false,
            'message' => 'Unauthorized.'
        ], 401);
    }

    $userId = (int)$_SESSION['user_id'];


    // =====================================================
    // CHECK USER
    // =====================================================

    $userQuery = $conn->prepare("
        SELECT department
        FROM user_tb
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$userQuery) {

        throw new Exception(
            'User query failed: ' . $conn->error
        );
    }

    $userQuery->bind_param(
        "i",
        $userId
    );

    $userQuery->execute();

    $userResult =
        $userQuery->get_result();

    $user =
        $userResult->fetch_assoc();

    $userQuery->close();


    if (!$user) {

        throw new Exception(
            'User account not found.'
        );
    }


    $department =
        trim($user['department'] ?? '');

    $isPurchasing =
        strcasecmp(
            $department,
            'Purchasing'
        ) === 0;

    $isAdmin =
        isset($_SESSION['user_type']) &&
        strcasecmp(
            trim($_SESSION['user_type']),
            'admin'
        ) === 0;


    if (!$isPurchasing && !$isAdmin) {

        throw new Exception(
            'You are not authorized to perform bulk updates.'
        );
    }


    // =====================================================
    // POST DATA
    // =====================================================

    $requestIds =
        $_POST['request_ids'] ?? [];

    $status =
        strtolower(
            trim($_POST['status'] ?? '')
        );

    $priority =
        strtolower(
            trim($_POST['priority'] ?? '')
        );

    $purchaserId =
        trim(
            $_POST['purchaser_id'] ?? ''
        );

    $categoryId =
        trim(
            $_POST['category_id'] ?? ''
        );

    $orderStatus =
        strtolower(
            trim($_POST['order_status'] ?? '')
        );

    $poNo =
        trim(
            $_POST['po_no'] ?? ''
        );

    $comment =
        trim(
            $_POST['comment'] ?? ''
        );


    // =====================================================
    // COMMENT VALIDATION
    // =====================================================

    if (mb_strlen($comment) > 1000) {

        throw new Exception(
            'Comment cannot exceed 1000 characters.'
        );
    }


    // =====================================================
    // REQUEST IDS
    // =====================================================

    if (
        !is_array($requestIds) ||
        count($requestIds) === 0
    ) {

        throw new Exception(
            'No requests were selected.'
        );
    }


    $requestIds = array_map(
        'intval',
        $requestIds
    );


    $requestIds = array_values(
        array_filter(
            $requestIds,
            function ($id) {
                return $id > 0;
            }
        )
    );


    $requestIds = array_values(
        array_unique($requestIds)
    );


    if (count($requestIds) === 0) {

        throw new Exception(
            'Invalid request IDs.'
        );
    }


    // =====================================================
    // STATUS VALIDATION
    // =====================================================

    $allowedStatuses = [
        'pending',
        'checking requirements',
        'canvassing',
        'negotiation',
        'draft po under discussion',
        'draft po approved',
        'final po approved',
        'rejected',
        'closed'
    ];


    if (
        $status !== '' &&
        !in_array(
            $status,
            $allowedStatuses,
            true
        )
    ) {

        throw new Exception(
            'Invalid status.'
        );
    }


    // =====================================================
    // PRIORITY VALIDATION
    // =====================================================

    $allowedPriorities = [
        'urgent',
        'high',
        'medium'
    ];


    if (
        $priority !== '' &&
        !in_array(
            $priority,
            $allowedPriorities,
            true
        )
    ) {

        throw new Exception(
            'Invalid priority.'
        );
    }


    // =====================================================
    // CATEGORY VALIDATION
    // =====================================================

    if ($categoryId !== '') {

        if (!ctype_digit($categoryId)) {

            throw new Exception(
                'Invalid category.'
            );
        }


        $categoryId = (int)$categoryId;


        $categoryCheck = $conn->prepare("
            SELECT category_id
            FROM request_category_tb
            WHERE category_id = ?
            LIMIT 1
        ");


        if (!$categoryCheck) {

            throw new Exception(
                'Category validation failed: ' .
                $conn->error
            );
        }


        $categoryCheck->bind_param(
            "i",
            $categoryId
        );

        $categoryCheck->execute();

        $categoryResult =
            $categoryCheck->get_result();


        if ($categoryResult->num_rows === 0) {

            $categoryCheck->close();

            throw new Exception(
                'Selected category is invalid.'
            );
        }


        $categoryCheck->close();
    }


    // =====================================================
    // PURCHASER VALIDATION
    // =====================================================

    if ($purchaserId !== '') {

        if (!ctype_digit($purchaserId)) {

            throw new Exception(
                'Invalid purchaser.'
            );
        }


        $purchaserId = (int)$purchaserId;


        if ($purchaserId > 1) {

            $purchaserCheck = $conn->prepare("
                SELECT user_id
                FROM user_tb
                WHERE user_id = ?
                  AND LOWER(TRIM(department)) = 'purchasing'
                LIMIT 1
            ");


            if (!$purchaserCheck) {

                throw new Exception(
                    'Purchaser validation failed: ' .
                    $conn->error
                );
            }


            $purchaserCheck->bind_param(
                "i",
                $purchaserId
            );

            $purchaserCheck->execute();

            $purchaserResult =
                $purchaserCheck->get_result();


            if ($purchaserResult->num_rows === 0) {

                $purchaserCheck->close();

                throw new Exception(
                    'Selected purchaser is not valid.'
                );
            }


            $purchaserCheck->close();
        }
    }


    // =====================================================
    // ORDER STATUS VALIDATION
    // =====================================================

    $allowedOrderStatuses = [
        'n/a',
        'order acknowledged',
        'goods delivered',
        'goods received',
        'payment processing',
        'payment issued',
        'closed'
    ];


    if (
        $orderStatus !== '' &&
        !in_array(
            $orderStatus,
            $allowedOrderStatuses,
            true
        )
    ) {

        throw new Exception(
            'Invalid order status.'
        );
    }


    // =====================================================
    // BULK UPDATE RULES
    // =====================================================

    /*
     * Empty value means:
     *
     *     NO CHANGE
     *
     * Therefore we only modify the fields explicitly
     * selected by the user.
     */


    if ($status === 'final po approved') {

        if ($poNo === '') {

            throw new Exception(
                'PO Number is required when Status is Final PO Approved.'
            );
        }


        if ($orderStatus === '') {

            $orderStatus = 'n/a';
        }
    }


    /*
     * If status is explicitly changed away from
     * Final PO Approved:
     *
     *     PO Number = NULL
     *     Order Status = n/a
     */

    if (
        $status !== '' &&
        $status !== 'final po approved'
    ) {

        $poNo = null;

        $orderStatus = 'n/a';
    }


    // =====================================================
    // NOTHING TO UPDATE
    // =====================================================

    if (
        $status === '' &&
        $priority === '' &&
        $purchaserId === '' &&
        $categoryId === '' &&
        $orderStatus === '' &&
        $poNo === '' &&
        $comment === ''
    ) {

        throw new Exception(
            'No changes or comment were provided.'
        );
    }


    // =====================================================
    // PLACEHOLDERS
    // =====================================================

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($requestIds),
            '?'
        )
    );


    // =====================================================
    // GET CURRENT REQUEST VALUES
    // =====================================================

    $currentRequests = [];


    $selectSql = "
        SELECT
            request_id,
            status,
            purchaser_id,
            priority,
            category_id,
            order_status,
            po_no
        FROM purch_request_tb
        WHERE request_id IN ($placeholders)
    ";


    $selectTypes = '';

    $selectValues = [];


    foreach ($requestIds as $id) {

        $selectTypes .= "i";

        $selectValues[] = $id;
    }


    $selectStmt =
        $conn->prepare($selectSql);


    if (!$selectStmt) {

        throw new Exception(
            'Failed to prepare request lookup: ' .
            $conn->error
        );
    }


    $selectStmt->bind_param(
        $selectTypes,
        ...$selectValues
    );


    $selectStmt->execute();


    $selectResult =
        $selectStmt->get_result();


    while ($row = $selectResult->fetch_assoc()) {

        $currentRequests[
            (int)$row['request_id']
        ] = $row;
    }


    $selectStmt->close();


    if (
        count($currentRequests) !==
        count($requestIds)
    ) {

        throw new Exception(
            'One or more selected requests could not be found.'
        );
    }


    // =====================================================
    // GET PURCHASER NAMES
    // =====================================================

    $purchaserNames = [];


    $purchaserQuery = $conn->query("
        SELECT
            user_id,
            fullname
        FROM user_tb
    ");


    if ($purchaserQuery) {

        while ($row =
            $purchaserQuery->fetch_assoc()
        ) {

            $purchaserNames[
                (int)$row['user_id']
            ] = $row['fullname'];
        }
    }


    // =====================================================
    // GET CATEGORY NAMES
    // =====================================================

    $categoryNames = [];


    $categoryQuery = $conn->query("
        SELECT
            category_id,
            category_name
        FROM request_category_tb
    ");


    if ($categoryQuery) {

        while ($row =
            $categoryQuery->fetch_assoc()
        ) {

            $categoryNames[
                (int)$row['category_id']
            ] = $row['category_name'];
        }
    }


    // =====================================================
    // TRANSACTION
    // =====================================================

    $conn->begin_transaction();


    try {

        // =================================================
        // BUILD UPDATE
        // =================================================

        $fields = [];

        $types = '';

        $values = [];


        // STATUS
        if ($status !== '') {

            $fields[] =
                "status = ?";

            $types .= "s";

            $values[] =
                $status;
        }


        // PRIORITY
        if ($priority !== '') {

            $fields[] =
                "priority = ?";

            $types .= "s";

            $values[] =
                $priority;
        }


        // PURCHASER
        if ($purchaserId !== '') {

            $fields[] =
                "purchaser_id = ?";

            $types .= "i";

            $values[] =
                $purchaserId;
        }


        // CATEGORY
        if ($categoryId !== '') {

            $fields[] =
                "category_id = ?";

            $types .= "i";

            $values[] =
                $categoryId;
        }


        // ORDER STATUS
        if ($orderStatus !== '') {

            $fields[] =
                "order_status = ?";

            $types .= "s";

            $values[] =
                $orderStatus;
        }


        // PO NUMBER
        if ($poNo !== '' && $poNo !== null) {

            $fields[] =
                "po_no = ?";

            $types .= "s";

            $values[] =
                $poNo;
        }


        // CLEAR PO WHEN STATUS CHANGES AWAY
        if (
            $status !== '' &&
            $status !== 'final po approved'
        ) {

            $fields[] =
                "po_no = NULL";
        }


        // =================================================
        // UPDATE REQUEST TABLE
        // =================================================

        $affectedRows = 0;


        if (count($fields) > 0) {

            $updateTypes =
                $types;

            $updateValues =
                $values;


            foreach ($requestIds as $id) {

                $updateTypes .= "i";

                $updateValues[] =
                    $id;
            }


            $sql = "
                UPDATE purch_request_tb
                SET " .
                implode(', ', $fields) .
                "
                WHERE request_id IN ($placeholders)
            ";


            $stmt =
                $conn->prepare($sql);


            if (!$stmt) {

                throw new Exception(
                    'Failed to prepare update: ' .
                    $conn->error
                );
            }


            $stmt->bind_param(
                $updateTypes,
                ...$updateValues
            );


            if (!$stmt->execute()) {

                $error =
                    $stmt->error;

                $stmt->close();

                throw new Exception(
                    'Database update failed: ' .
                    $error
                );
            }


            $affectedRows =
                $stmt->affected_rows;


            $stmt->close();
        }


        // =================================================
        // BUILD INDIVIDUAL HISTORY
        // =================================================

        /*
         * IMPORTANT:
         *
         * Each request gets its OWN changes_json.
         *
         * Example:
         *
         * {
         *     "status": {
         *         "old": "draft po approved",
         *         "new": "negotiation"
         *     },
         *     "category": {
         *         "old": "Facilities and Maintenance",
         *         "new": "Spare Parts VRF"
         *     }
         * }
         */


        $history = null;


        if ($comment !== '') {

            $history = $conn->prepare("
                INSERT INTO purch_request_history_tb
                (
                    request_id,
                    changed_by,
                    comment,
                    changes_json
                )
                VALUES (?, ?, ?, ?)
            ");


            if (!$history) {

                throw new Exception(
                    'History prepare failed: ' .
                    $conn->error
                );
            }
        }


        foreach ($requestIds as $requestId) {

            $old =
                $currentRequests[$requestId];


            $changes = [];


            // =============================================
            // STATUS
            // =============================================

            if ($status !== '') {

                $oldStatus =
                    strtolower(
                        trim(
                            $old['status'] ?? ''
                        )
                    );


                if ($oldStatus !== $status) {

                    $changes['status'] = [

                        'old' =>
                            $old['status'],

                        'new' =>
                            $status
                    ];
                }
            }


            // =============================================
            // PRIORITY
            // =============================================

            if ($priority !== '') {

                $oldPriority =
                    strtolower(
                        trim(
                            $old['priority'] ?? ''
                        )
                    );


                if ($oldPriority !== $priority) {

                    $changes['priority'] = [

                        'old' =>
                            $old['priority'],

                        'new' =>
                            $priority
                    ];
                }
            }


            // =============================================
            // PURCHASER
            // =============================================

            if ($purchaserId !== '') {

                $oldPurchaserId =
                    (int)$old['purchaser_id'];


                if (
                    $oldPurchaserId !==
                    $purchaserId
                ) {

                    $oldPurchaserName =
                        'Unassigned';


                    $newPurchaserName =
                        'Unassigned';


                    if ($oldPurchaserId > 1) {

                        $oldPurchaserName =
                            $purchaserNames[
                                $oldPurchaserId
                            ] ??
                            'Unassigned';
                    }


                    if ($purchaserId > 1) {

                        $newPurchaserName =
                            $purchaserNames[
                                $purchaserId
                            ] ??
                            'Unassigned';
                    }


                    $changes['purchaser'] = [

                        'old' =>
                            $oldPurchaserName,

                        'new' =>
                            $newPurchaserName
                    ];
                }
            }


            // =============================================
            // CATEGORY
            // =============================================

            if ($categoryId !== '') {

                $oldCategoryId =
                    (int)$old['category_id'];


                if (
                    $oldCategoryId !==
                    $categoryId
                ) {

                    $oldCategoryName =
                        'N/A';


                    $newCategoryName =
                        'N/A';


                    if ($oldCategoryId > 0) {

                        $oldCategoryName =
                            $categoryNames[
                                $oldCategoryId
                            ] ??
                            'N/A';
                    }


                    if ($categoryId > 0) {

                        $newCategoryName =
                            $categoryNames[
                                $categoryId
                            ] ??
                            'N/A';
                    }


                    $changes['category'] = [

                        'old' =>
                            $oldCategoryName,

                        'new' =>
                            $newCategoryName
                    ];
                }
            }


            // =============================================
            // ORDER STATUS
            // =============================================

            if ($orderStatus !== '') {

                $oldOrderStatus =
                    strtolower(
                        trim(
                            $old['order_status'] ?? ''
                        )
                    );


                if (
                    $oldOrderStatus !==
                    $orderStatus
                ) {

                    $changes['order_status'] = [

                        'old' =>
                            $old['order_status'],

                        'new' =>
                            $orderStatus
                    ];
                }
            }


            // =============================================
            // PO NUMBER
            // =============================================

            /*
             * If status changed away from Final PO Approved,
             * the PO is cleared even though the user did not
             * directly enter a PO value.
             */

            if (
                $status !== '' &&
                $status !== 'final po approved'
            ) {

                $oldPO =
                    trim(
                        $old['po_no'] ?? ''
                    );


                if ($oldPO !== '') {

                    $changes['po_no'] = [

                        'old' =>
                            $oldPO,

                        'new' =>
                            ''
                    ];
                }

            } elseif ($poNo !== '') {

                $oldPO =
                    trim(
                        $old['po_no'] ?? ''
                    );


                if ($oldPO !== $poNo) {

                    $changes['po_no'] = [

                        'old' =>
                            $oldPO,

                        'new' =>
                            $poNo
                    ];
                }
            }


            // =============================================
            // SAVE HISTORY
            // =============================================

            /*
             * IMPORTANT:
             *
             * If there is ONLY a comment and no actual
             * field changed, changes_json will be:
             *
             * {}
             *
             * This is intentional.
             */


            if (
                $comment !== '' &&
                $history
            ) {

                $changesJson =
                    json_encode(
                        $changes,
                        JSON_UNESCAPED_UNICODE
                    );


                if ($changesJson === false) {

                    throw new Exception(
                        'Failed to encode change history.'
                    );
                }


                $history->bind_param(
                    "iiss",
                    $requestId,
                    $userId,
                    $comment,
                    $changesJson
                );


                if (!$history->execute()) {

                    throw new Exception(
                        'Failed to save history for request ' .
                        $requestId .
                        ': ' .
                        $history->error
                    );
                }
            }
        }


        if ($history) {

            $history->close();
        }


        // =================================================
        // COMMIT
        // =================================================

        $conn->commit();


    } catch (Throwable $e) {

        $conn->rollback();

        throw $e;
    }


    // =====================================================
    // RESPONSE
    // =====================================================

    $message =
        count($requestIds) .
        ' request(s) updated successfully.';


    if (
        $comment !== '' &&
        $affectedRows === 0
    ) {

        $message =
            count($requestIds) .
            ' request(s) comment saved successfully.';

    } elseif ($comment !== '') {

        $message =
            count($requestIds) .
            ' request(s) updated and comment saved successfully.';
    }


    returnJson([

        'success' =>
            true,

        'message' =>
            $message,

        'affected_rows' =>
            $affectedRows,

        'comment_saved' =>
            $comment !== ''

    ]);


} catch (Throwable $e) {

    returnJson([

        'success' =>
            false,

        'message' =>
            $e->getMessage()

    ], 400);
}
?>

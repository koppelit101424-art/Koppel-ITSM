<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/send_purch_request_status_email.php';

header('Content-Type: application/json; charset=utf-8');


$response = [
    'success' => false,
    'message' => ''
];


try {

    // =====================================================
    // LOGIN
    // =====================================================

    if (!isset($_SESSION['user_id'])) {

        throw new Exception(
            'You are not logged in.'
        );
    }


    $current_user_id =
        (int)$_SESSION['user_id'];


    // =====================================================
    // POST DATA
    // =====================================================

    $request_id =
        (int)($_POST['request_id'] ?? 0);


    $status =
        strtolower(
            trim($_POST['status'] ?? '')
        );


    $purchaser_id =
        (int)(
            $_POST['purchaser_id'] ?? 1
        );


    $priority =
        strtolower(
            trim($_POST['priority'] ?? '')
        );


    $category_id =
        (int)(
            $_POST['category_id'] ?? 0
        );


    $order_status =
        strtolower(
            trim($_POST['order_status'] ?? 'n/a')
        );


    $po_no =
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
    // REQUEST ID
    // =====================================================

    if ($request_id <= 0) {

        throw new Exception(
            'Invalid request ID.'
        );
    }


    // =====================================================
    // PRIORITY
    // =====================================================

    $allowed_priorities = [
        'urgent',
        'high',
        'medium'
    ];


    if (!in_array(
        $priority,
        $allowed_priorities,
        true
    )) {

        throw new Exception(
            'Invalid priority.'
        );
    }


    // =====================================================
    // STATUS
    // =====================================================

    $allowed_statuses = [
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


    if (!in_array(
        $status,
        $allowed_statuses,
        true
    )) {

        throw new Exception(
            'Invalid status.'
        );
    }


    // =====================================================
    // ORDER STATUS
    // =====================================================

    $allowed_order_statuses = [
        'n/a',
        'order acknowledged',
        'goods received',
        'payment processing',
        'payment issued',
        'closed'
    ];


    // =====================================================
    // FINAL PO APPROVED
    // =====================================================

    if ($status === 'final po approved') {

        if ($po_no === '') {

            throw new Exception(
                'PO Number is required when Status is Final PO Approved.'
            );
        }


        if (!in_array(
            $order_status,
            $allowed_order_statuses,
            true
        )) {

            throw new Exception(
                'Invalid order status.'
            );
        }

    } else {

        $po_no =
            '';

        $order_status =
            'n/a';
    }


    // =====================================================
    // GET CURRENT USER
    // =====================================================

    $userQuery =
        $conn->prepare("
            SELECT department
            FROM user_tb
            WHERE user_id = ?
            LIMIT 1
        ");


    if (!$userQuery) {

        throw new Exception(
            'User query failed: ' .
            $conn->error
        );
    }


    $userQuery->bind_param(
        "i",
        $current_user_id
    );


    $userQuery->execute();


    $currentUser =
        $userQuery
            ->get_result()
            ->fetch_assoc();


    $userQuery->close();


    if (!$currentUser) {

        throw new Exception(
            'User account not found.'
        );
    }


    $department =
        trim(
            $currentUser['department'] ?? ''
        );


    $sessionUserType =
        trim(
            $_SESSION['user_type'] ?? ''
        );


    $isPurchasing =
        strcasecmp(
            $department,
            'Purchasing'
        ) === 0;


    $isAdmin =
        strcasecmp(
            $sessionUserType,
            'admin'
        ) === 0;


    if (
        !$isPurchasing &&
        !$isAdmin
    ) {

        throw new Exception(
            'You are not authorized to update purchasing requests.'
        );
    }


    // =====================================================
    // GET CURRENT REQUEST
    // =====================================================

    /*
     * IMPORTANT:
     *
     * "user_id" is assumed to be the requestor ID.
     *
     * If your column is requestor_id, change user_id
     * below to requestor_id.
     */

    $check =
        $conn->prepare("
            SELECT
                request_id,
                user_id,
                status,
                purchaser_id,
                priority,
                category_id,
                order_status,
                po_no
            FROM purch_request_tb
            WHERE request_id = ?
            LIMIT 1
        ");


    if (!$check) {

        throw new Exception(
            'Request query failed: ' .
            $conn->error
        );
    }


    $check->bind_param(
        "i",
        $request_id
    );


    $check->execute();


    $currentRequest =
        $check
            ->get_result()
            ->fetch_assoc();


    $check->close();


    if (!$currentRequest) {

        throw new Exception(
            'Request not found.'
        );
    }


    // =====================================================
    // OLD VALUES
    // =====================================================

    $oldPurchaserId =
        (int)$currentRequest['purchaser_id'];


    $oldCategoryId =
        (int)$currentRequest['category_id'];


    $oldStatus =
        strtolower(
            trim(
                $currentRequest['status'] ?? ''
            )
        );


    // =====================================================
    // REQUESTOR
    // =====================================================

    $requestorId =
        (int)(
            $currentRequest['user_id'] ?? 0
        );


    // =====================================================
    // CATEGORY VALIDATION
    // =====================================================

    if ($category_id > 0) {

        $categoryCheck =
            $conn->prepare("
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
            $category_id
        );


        $categoryCheck->execute();


        $categoryResult =
            $categoryCheck->get_result();


        if ($categoryResult->num_rows === 0) {

            $categoryCheck->close();

            throw new Exception(
                'Invalid category.'
            );
        }


        $categoryCheck->close();
    }


    // =====================================================
    // PURCHASER VALIDATION
    // =====================================================

    if ($purchaser_id > 1) {

        $purchaserCheck =
            $conn->prepare("
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
            $purchaser_id
        );


        $purchaserCheck->execute();


        $purchaserResult =
            $purchaserCheck->get_result();


        if ($purchaserResult->num_rows === 0) {

            $purchaserCheck->close();

            throw new Exception(
                'Selected purchaser is not a valid Purchasing user.'
            );
        }


        $purchaserCheck->close();
    }


    // =====================================================
    // OLD PURCHASER NAME
    // =====================================================

    $oldPurchaserName =
        'Unassigned';


    if ($oldPurchaserId > 1) {

        $query =
            $conn->prepare("
                SELECT fullname
                FROM user_tb
                WHERE user_id = ?
                LIMIT 1
            ");


        $query->bind_param(
            "i",
            $oldPurchaserId
        );


        $query->execute();


        $row =
            $query
                ->get_result()
                ->fetch_assoc();


        $query->close();


        if ($row) {

            $oldPurchaserName =
                $row['fullname'];
        }
    }


    // =====================================================
    // NEW PURCHASER NAME
    // =====================================================

    $newPurchaserName =
        'Unassigned';


    if ($purchaser_id > 1) {

        $query =
            $conn->prepare("
                SELECT fullname
                FROM user_tb
                WHERE user_id = ?
                LIMIT 1
            ");


        $query->bind_param(
            "i",
            $purchaser_id
        );


        $query->execute();


        $row =
            $query
                ->get_result()
                ->fetch_assoc();


        $query->close();


        if ($row) {

            $newPurchaserName =
                $row['fullname'];
        }
    }


    // =====================================================
    // OLD CATEGORY NAME
    // =====================================================

    $oldCategoryName =
        'N/A';


    if ($oldCategoryId > 0) {

        $query =
            $conn->prepare("
                SELECT category_name
                FROM request_category_tb
                WHERE category_id = ?
                LIMIT 1
            ");


        $query->bind_param(
            "i",
            $oldCategoryId
        );


        $query->execute();


        $row =
            $query
                ->get_result()
                ->fetch_assoc();


        $query->close();


        if ($row) {

            $oldCategoryName =
                $row['category_name'];
        }
    }


    // =====================================================
    // NEW CATEGORY NAME
    // =====================================================

    $newCategoryName =
        'N/A';


    if ($category_id > 0) {

        $query =
            $conn->prepare("
                SELECT category_name
                FROM request_category_tb
                WHERE category_id = ?
                LIMIT 1
            ");


        $query->bind_param(
            "i",
            $category_id
        );


        $query->execute();


        $row =
            $query
                ->get_result()
                ->fetch_assoc();


        $query->close();


        if ($row) {

            $newCategoryName =
                $row['category_name'];
        }
    }


    // =====================================================
    // BUILD CHANGES
    // =====================================================

    $changes = [];


    // STATUS

    if ($oldStatus !== $status) {

        $changes['status'] = [
            'old' =>
                $currentRequest['status'],

            'new' =>
                $status
        ];
    }


    // PURCHASER

    if (
        $oldPurchaserId !==
        $purchaser_id
    ) {

        $changes['purchaser'] = [
            'old' =>
                $oldPurchaserName,

            'new' =>
                $newPurchaserName
        ];
    }


    // PRIORITY

    if (
        strtolower(
            trim(
                $currentRequest['priority']
            )
        ) !== $priority
    ) {

        $changes['priority'] = [
            'old' =>
                $currentRequest['priority'],

            'new' =>
                $priority
        ];
    }


    // CATEGORY

    if (
        $oldCategoryId !==
        $category_id
    ) {

        $changes['category'] = [
            'old' =>
                $oldCategoryName,

            'new' =>
                $newCategoryName
        ];
    }


    // ORDER STATUS

    if (
        strtolower(
            trim(
                $currentRequest['order_status']
            )
        ) !== $order_status
    ) {

        $changes['order_status'] = [
            'old' =>
                $currentRequest['order_status'],

            'new' =>
                $order_status
        ];
    }


    // PO NUMBER

    if (
        trim(
            $currentRequest['po_no'] ?? ''
        ) !== $po_no
    ) {

        $changes['po_no'] = [
            'old' =>
                $currentRequest['po_no'] ?? '',

            'new' =>
                $po_no
        ];
    }


    // =====================================================
    // NOTHING CHANGED
    // =====================================================

    if (
        empty($changes) &&
        $comment === ''
    ) {

        throw new Exception(
            'No changes or comment were provided.'
        );
    }


    // =====================================================
    // TRANSACTION
    // =====================================================

    $conn->begin_transaction();


    try {

        // -------------------------------------------------
        // UPDATE
        // -------------------------------------------------

        $update =
            $conn->prepare("
                UPDATE purch_request_tb
                SET
                    status = ?,
                    purchaser_id = ?,
                    priority = ?,
                    category_id = ?,
                    order_status = ?,
                    po_no = ?
                WHERE request_id = ?
            ");


        if (!$update) {

            throw new Exception(
                'Update prepare failed: ' .
                $conn->error
            );
        }


        $update->bind_param(
            "sisissi",
            $status,
            $purchaser_id,
            $priority,
            $category_id,
            $order_status,
            $po_no,
            $request_id
        );


        if (!$update->execute()) {

            throw new Exception(
                'Database update failed: ' .
                $update->error
            );
        }


        $update->close();


        // -------------------------------------------------
        // HISTORY
        // -------------------------------------------------

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


        $history =
            $conn->prepare("
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


        $history->bind_param(
            "iiss",
            $request_id,
            $current_user_id,
            $comment,
            $changesJson
        );


        if (!$history->execute()) {

            throw new Exception(
                'Failed to save request history: ' .
                $history->error
            );
        }


        $history->close();


        // -------------------------------------------------
        // COMMIT
        // -------------------------------------------------

        $conn->commit();

    } catch (Throwable $e) {

        $conn->rollback();

        throw $e;
    }


// =====================================================
// EMAIL
//
// IMPORTANT:
// Email is sent AFTER COMMIT.
//
// Status notifications:
//     pending
//     rejected
//
// Order status notifications:
//     goods received
//     closed
//
// SMTP failure will NOT undo the database update.
// =====================================================

$emailSent = false;


// =====================================================
// CURRENT OLD ORDER STATUS
// =====================================================

$oldOrderStatus =
    strtolower(
        trim(
            $currentRequest['order_status'] ?? ''
        )
    );


// =====================================================
// CURRENT NEW ORDER STATUS
// =====================================================

$newOrderStatus =
    strtolower(
        trim(
            $order_status
        )
    );


            // =====================================================
            // NOTIFICATION FLAGS
            // =====================================================

            $shouldSendEmail = false;

            $emailNotificationStatus = '';


            // =====================================================
            // STATUS NOTIFICATIONS
            // =====================================================
            //
            // These come from purch_request_tb.status
            //
            // pending
            // rejected
            // =====================================================

            $statusNotificationStatuses = [
                'pending',
                'rejected'
            ];


            if (
                $oldStatus !== $status &&
                in_array(
                    $status,
                    $statusNotificationStatuses,
                    true
                ) &&
                $requestorId > 0
            ) {

                $shouldSendEmail = true;

                $emailNotificationStatus =
                    $status;
            }


            // =====================================================
            // ORDER STATUS NOTIFICATIONS
            // =====================================================
            //
            // These come from purch_request_tb.order_status
            //
            // goods received
            // closed
            // =====================================================

            $orderStatusNotificationStatuses = [
                'goods received',
                'closed'
            ];


            if (
                $oldOrderStatus !== $newOrderStatus &&
                in_array(
                    $newOrderStatus,
                    $orderStatusNotificationStatuses,
                    true
                ) &&
                $requestorId > 0
            ) {

                $shouldSendEmail = true;

                $emailNotificationStatus =
                    $newOrderStatus;
            }


            // =====================================================
            // SEND EMAIL
            // =====================================================

            if ($shouldSendEmail) {

                $emailSent =
                    sendPurchRequestStatusEmail(
                        $conn,
                        $request_id,
                        $requestorId,
                        $emailNotificationStatus,
                        $changes,
                        $comment,
                        $purchaser_id
                    );
            }



    // =====================================================
// RESPONSE
// =====================================================

$response['success'] =
    true;


$response['message'] =
    'Request updated successfully.';


$response['request_id'] =
    $request_id;


$response['status'] =
    $status;


$response['purchaser_id'] =
    $purchaser_id;


$response['priority'] =
    $priority;


$response['category_id'] =
    $category_id;


$response['category_name'] =
    $newCategoryName;


$response['order_status'] =
    $order_status;


$response['po_no'] =
    $po_no;


$response['purchaser_name'] =
    $newPurchaserName;


$response['changes'] =
    $changes;


$response['email_sent'] =
    $emailSent;


$response['email_notification_status'] =
    $emailNotificationStatus;


} catch (Throwable $e) {

    http_response_code(400);


    $response['success'] =
        false;


    $response['message'] =
        $e->getMessage();
}


echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE
);


$conn->close();
